# 🏢 GUIDA MULTI-TENANCY - SENZA SOTTODOMINI

**Versione:** 2.0  
**Data:** Aprile 2026  
**Approccio:** Selezione Città durante Login + Isolamento Dati via Sessione  

---

## 📋 INDICE

1. [Concetti Fondamentali](#-concetti-fondamentali)
2. [Architettura Generale](#-architettura-generale)
3. [Fase 1: Modifiche Database](#-fase-1-modifiche-database)
4. [Fase 2: Configurazione Tenants](#-fase-2-configurazione-tenants)
5. [Fase 3: Classe TenantManager](#-fase-3-classe-tenantmanager)
6. [Fase 4: Modifiche Login](#-fase-4-modifiche-login)
7. [Fase 5: Helper Query Tenant-Safe](#-fase-5-helper-query-tenant-safe)
8. [Fase 6: Modifiche API](#-fase-6-modifiche-api)
9. [Fase 7: Security Checks](#-fase-7-security-checks)
10. [Fase 8: Test & Deployment](#-fase-8-test--deployment)

---

## 🎯 CONCETTI FONDAMENTALI

### Cos'è Multi-Tenancy?

Una sola applicazione serve **molteplici tenant** (città) in **isolamento logico** con **infrastruttura condivisa**.

**SwapHub Example:**
- 1 Database SWAPHUB (condiviso)
- 1 Applicazione PHP (condivisa)
- 3 Tenant: Bergamo, Milano, Torino (logicamente isolati)

### Approccio Scelto: Selezione Durante Login

```
FLUSSO UTENTE:
┌─────────────────────────────────────────────────────┐
│ 1. Utente entra su https://swaphub.it/login/login.php│
│                                                      │
│ 2. Vede dropdown: [ Bergamo | Milano | Torino ]    │
│                                                      │
│ 3. Sceglie "Bergamo" (tenant_id = 1)               │
│                                                      │
│ 4. Inserisce username + password                    │
│                                                      │
│ 5. Backend verifica:                                │
│    - username + password corretti?                  │
│    - utente appartiene a Bergamo? (tenant_id = 1)  │
│                                                      │
│ 6. Se OK:                                           │
│    - $_SESSION['tenant_id'] = 1                     │
│    - JWT token contiene tenant_id=1                │
│    - Redirect a dashboard                           │
│                                                      │
│ 7. Tutte le query successive filtra per tenant_id =1│
└─────────────────────────────────────────────────────┘
```

### Principio Fondamentale

**Ogni query DEVE includere WHERE tenant_id = ?**

```php
// ❌ PERICOLOSO (data leak tra tenant):
SELECT * FROM Prodotto WHERE User = 'gianno'

// ✅ SICURO (isolamento garantito):
$tenant_id = $_SESSION['tenant_id'];  // Sempre dalla sessione!
SELECT * FROM Prodotto WHERE User = 'gianno' AND tenant_id = ?
```

---

## 🏗️ ARCHITETTURA GENERALE

### Tabelle Principali

#### Tabella: Tenant (nuova)
```sql
id | nome_tenant | city     | is_active | created_at
1  | bergamo     | Bergamo  | 1         | 2026-04-17
2  | milano      | Milano   | 1         | 2026-04-17
3  | torino      | Torino   | 1         | 2026-04-17
```

#### Tabelle Esistenti (modificate)
Tutte le tabelle aggiungeranno colonna `tenant_id`:
- `utenti` → user appartiene a quale tenant?
- `Prodotto` → prodotto di quale tenant?
- `Chat`, `Messaggi` → conversazione di quale tenant?
- `Scambio` → scambio di quale tenant?
- **ECCEZIONE:** `Ruolo`, `Permesso` (globali, non filtrate per tenant)

### Flusso Autenticazione

```
User Login Form
    ↓
POST /login/login.php
  - username
  - password
  - tenant_id (dal dropdown)
    ↓
Backend Validation:
  1. Valida tenant_id esiste?
  2. Cerca USER + PASSWORD + tenant_id
    ↓
Success:
  - $_SESSION['tenant_id'] = 1
  - $_SESSION['username'] = 'gianno'
  - JWT token = { username, tenant_id, exp }
    ↓
Redirect to Dashboard
  - Tutte le query: WHERE tenant_id = 1
```

---

## 🔄 FASE 1: MODIFICHE DATABASE

### Step 1.1: Crea Tabella Tenant

```sql
-- Crea tabella TENANT
CREATE TABLE `Tenant` (
  `id` int(11) NOT NULL AUTO_INCREMENT PRIMARY KEY,
  `nome_tenant` varchar(100) NOT NULL UNIQUE,
  `city` varchar(100) NOT NULL,
  `is_active` tinyint(1) NOT NULL DEFAULT 1,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `updated_at` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- Inserisci tenant
INSERT INTO `Tenant` (`nome_tenant`, `city`, `is_active`) VALUES
('bergamo', 'Bergamo', 1),
('milano', 'Milano', 1),
('torino', 'Torino', 1);
```

### Step 1.2: Aggiungi Colonna tenant_id a Tutte le Tabelle

**Esegui per OGNI tabella (eccetto Ruolo, Permesso, RuoloPermesso):**

```sql
-- TABELLE UTENTI & SCAMBI
ALTER TABLE utenti ADD COLUMN tenant_id INT(11) NOT NULL DEFAULT 1 AFTER username;
ALTER TABLE utenti ADD FOREIGN KEY (tenant_id) REFERENCES Tenant(id) ON DELETE CASCADE;

ALTER TABLE Categoria ADD COLUMN tenant_id INT(11) NOT NULL DEFAULT 1 AFTER NomeCategoria;
ALTER TABLE Categoria ADD FOREIGN KEY (tenant_id) REFERENCES Tenant(id) ON DELETE CASCADE;

-- TABELLE CHAT
ALTER TABLE Chat ADD COLUMN tenant_id INT(11) NOT NULL DEFAULT 1 AFTER idChat;
ALTER TABLE Chat ADD FOREIGN KEY (tenant_id) REFERENCES Tenant(id) ON DELETE CASCADE;

ALTER TABLE Messaggi ADD COLUMN tenant_id INT(11) NOT NULL DEFAULT 1 AFTER idMessaggio;
ALTER TABLE Messaggi ADD FOREIGN KEY (tenant_id) REFERENCES Tenant(id) ON DELETE CASCADE;

ALTER TABLE PartecipaChat ADD COLUMN tenant_id INT(11) NOT NULL DEFAULT 1 AFTER idChat;
ALTER TABLE PartecipaChat ADD FOREIGN KEY (tenant_id) REFERENCES Tenant(id) ON DELETE CASCADE;

-- TABELLE PRODOTTI & SCAMBI
ALTER TABLE Prodotto ADD COLUMN tenant_id INT(11) NOT NULL DEFAULT 1 AFTER idProdotto;
ALTER TABLE Prodotto ADD FOREIGN KEY (tenant_id) REFERENCES Tenant(id) ON DELETE CASCADE;

ALTER TABLE Scambio ADD COLUMN tenant_id INT(11) NOT NULL DEFAULT 1 AFTER idScambio;
ALTER TABLE Scambio ADD FOREIGN KEY (tenant_id) REFERENCES Tenant(id) ON DELETE CASCADE;

ALTER TABLE Consegna ADD COLUMN tenant_id INT(11) NOT NULL DEFAULT 1 AFTER idConsegna;
ALTER TABLE Consegna ADD FOREIGN KEY (tenant_id) REFERENCES Tenant(id) ON DELETE CASCADE;

-- TABELLE SOCIAL
ALTER TABLE RichiesteAmicizia ADD COLUMN tenant_id INT(11) NOT NULL DEFAULT 1 AFTER idRichiesta;
ALTER TABLE RichiesteAmicizia ADD FOREIGN KEY (tenant_id) REFERENCES Tenant(id) ON DELETE CASCADE;

ALTER TABLE Recensioni ADD COLUMN tenant_id INT(11) NOT NULL DEFAULT 1 AFTER idRecensione;
ALTER TABLE Recensioni ADD FOREIGN KEY (tenant_id) REFERENCES Tenant(id) ON DELETE CASCADE;

-- TABELLE MODERAZIONE & SUBSCRIPTION
ALTER TABLE Segnalazioni ADD COLUMN tenant_id INT(11) NOT NULL DEFAULT 1 AFTER idSegnalazione;
ALTER TABLE Segnalazioni ADD FOREIGN KEY (tenant_id) REFERENCES Tenant(id) ON DELETE CASCADE;

ALTER TABLE SwapPlus ADD COLUMN tenant_id INT(11) NOT NULL DEFAULT 1 AFTER idAbbonamento;
ALTER TABLE SwapPlus ADD FOREIGN KEY (tenant_id) REFERENCES Tenant(id) ON DELETE CASCADE;
```

### Step 1.3: Aggiungi Indici per Performance

```sql
-- Indici composti per query veloci filtrate per tenant
CREATE INDEX idx_tenant_user ON utenti(tenant_id, username);
CREATE INDEX idx_tenant_prodotto ON Prodotto(tenant_id, User);
CREATE INDEX idx_tenant_chat ON Chat(tenant_id, idChat);
CREATE INDEX idx_tenant_messaggi ON Messaggi(tenant_id, idChat);
CREATE INDEX idx_tenant_scambio ON Scambio(tenant_id, idUtenteMit);
```

---

## ⚙️ FASE 2: CONFIGURAZIONE TENANTS

**Crea file:** `LOGIN_DATABASE/config/tenants_config.php`

```php
<?php
/**
 * CONFIGURAZIONE TENANTS
 * 
 * Definisce tutti i tenant disponibili per la piattaforma
 */

return [
    'tenants' => [
        1 => [
            'id'   => 1,
            'name' => 'bergamo',
            'city' => 'Bergamo',
        ],
        2 => [
            'id'   => 2,
            'name' => 'milano',
            'city' => 'Milano',
        ],
        3 => [
            'id'   => 3,
            'name' => 'torino',
            'city' => 'Torino',
        ],
    ],
];
?>
```

---

## 📱 FASE 3: CLASSE TENANTMANAGER

**Crea file:** `LOGIN_DATABASE/config/TenantManager.php`

```php
<?php
/**
 * CLASSE: TenantManager
 * 
 * Gestisce tenant corrente da sessione PHP
 * Fornisce funzioni helper per accesso dati tenant-aware
 */

class TenantManager {
    
    /**
     * Ritorna tenant_id dalla sessione corrente
     * 
     * @return int|null tenant_id o null se non loggato
     */
    public static function get_current_tenant_id() {
        return $_SESSION['tenant_id'] ?? null;
    }
    
    /**
     * Imposta tenant in sessione (dopo login)
     * 
     * @param int $tenant_id
     */
    public static function set_current_tenant_id($tenant_id) {
        $_SESSION['tenant_id'] = $tenant_id;
    }
    
    /**
     * Ritorna info completa del tenant corrente
     * 
     * @return array|null Tenant data o null
     */
    public static function get_current_tenant() {
        $tenant_id = self::get_current_tenant_id();
        if (!$tenant_id) return null;
        
        $config = require __DIR__ . '/tenants_config.php';
        return $config['tenants'][$tenant_id] ?? null;
    }
    
    /**
     * Ritorna lista di TUTTI i tenant disponibili
     * Usare per dropdown nel login
     * 
     * @return array Array di tenant
     */
    public static function get_all_tenants() {
        $config = require __DIR__ . '/tenants_config.php';
        return $config['tenants'];
    }
    
    /**
     * Valida che un tenant_id è valido
     * 
     * @param int $tenant_id
     * @return bool
     */
    public static function validate_tenant_id($tenant_id) {
        $config = require __DIR__ . '/tenants_config.php';
        return isset($config['tenants'][$tenant_id]);
    }
    
    /**
     * Ottieni tenant_id dal nome del tenant
     * 
     * @param string $name Nome tenant (es: 'bergamo')
     * @return int|null
     */
    public static function get_tenant_id_by_name($name) {
        $config = require __DIR__ . '/tenants_config.php';
        foreach ($config['tenants'] as $id => $tenant) {
            if ($tenant['name'] === $name) {
                return $id;
            }
        }
        return null;
    }
}
?>
```

---

## 🔐 FASE 4: MODIFICHE LOGIN

### Step 4.1: Form Login Aggiornato

**Modifica:** `LOGIN_DATABASE/login.php` (parte HTML)

Aggiungi questo PRIMA del form credenziali:

```html
<!-- FORM LOGIN UPDATO -->
<form method="POST" action="login.php">
    
    <!-- 🆕 DROPDOWN TENANT (NUOVO!) -->
    <div class="form-group">
        <label for="tenant_id"><strong>Seleziona Città:</strong></label>
        <select name="tenant_id" id="tenant_id" required>
            <option value="">-- Scegli la tua città --</option>
            <?php
                // Importa TenantManager
                require_once __DIR__ . '/config/TenantManager.php';
                
                // Ottieni lista tenant
                $tenants = TenantManager::get_all_tenants();
                
                // Genera option
                foreach ($tenants as $id => $tenant) {
                    echo "<option value=\"{$id}\">{$tenant['city']}</option>";
                }
            ?>
        </select>
        <small>Se non trovi la tua città, contatta l'amministratore</small>
    </div>
    
    <!-- Form credenziali (come prima) -->
    <div class="form-group">
        <label for="username"><strong>Username:</strong></label>
        <input type="text" id="username" name="username" required>
    </div>
    
    <div class="form-group">
        <label for="password"><strong>Password:</strong></label>
        <input type="password" id="password" name="password" required>
    </div>
    
    <button type="submit" class="btn-login">Accedi</button>
</form>
```

### Step 4.2: Logica Login Aggiornata

**Modifica:** `LOGIN_DATABASE/login.php` (parte PHP)

Sostituisci la logica di verifica login:

```php
<?php
session_start();

require_once 'config.php';
require_once 'jwt.php';
require_once 'config/TenantManager.php';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    
    $username = $_POST['username'] ?? null;
    $password = $_POST['password'] ?? null;
    $tenant_id = isset($_POST['tenant_id']) ? (int)$_POST['tenant_id'] : null;
    
    // 1️⃣ Validazione Input
    if (!$username || !$password || !$tenant_id) {
        $error = "Tutti i campi sono obbligatori";
    }
    // 2️⃣ Validazione Tenant
    elseif (!TenantManager::validate_tenant_id($tenant_id)) {
        $error = "Tenant non valido";
    }
    // 3️⃣ Query Filtrata per Tenant
    else {
        $stmt = $conn->prepare(
            "SELECT username, password, salt 
             FROM utenti 
             WHERE username = ? AND tenant_id = ? AND isBanned = 0"
        );
        $stmt->execute([$username, $tenant_id]);
        $user = $stmt->fetch(PDO::FETCH_ASSOC);
        
        if ($user && password_verify($password, $user['password'])) {
            
            // 4️⃣ Login Successo: Salva Sessione
            $_SESSION['username'] = $username;
            $_SESSION['tenant_id'] = $tenant_id;
            $_SESSION['logged_in'] = true;
            
            // 5️⃣ Crea JWT Token (incluidi tenant_id)
            $payload = [
                'username' => $username,
                'tenant_id' => $tenant_id,
                'iat' => time(),
                'exp' => time() + 86400 // 24 ore
            ];
            $token = generateJWT($payload);
            $_SESSION['jwt_token'] = $token;
            
            // 6️⃣ Salva preferenza tenant in cookie (opzionale)
            setcookie(
                'last_tenant', 
                $tenant_id, 
                time() + (90 * 24 * 60 * 60), // 90 giorni
                '/'
            );
            
            // 7️⃣ Redirect
            header('Location: mockup_manager.php?azione=dashboard');
            exit;
            
        } else {
            $error = "Credenziali non valide per il tenant selezionato";
        }
    }
    
    // Mostra errore se presente
    if (isset($error)) {
        echo "<div class='alert alert-error'>$error</div>";
    }
}
?>
```

### Step 4.3: Pre-selezionamento Tenant (Opzionale)

Se vuoi pre-selezionare l'ultimo tenant scelto:

```html
<!-- Nel select, modifica option -->
<option value="<?php echo $id; ?>" 
    <?php 
        $last_tenant = $_COOKIE['last_tenant'] ?? null;
        if ($last_tenant == $id) echo 'selected';
    ?>>
    <?php echo $tenant['city']; ?>
</option>
```

---

## 📚 FASE 5: HELPER QUERY TENANT-SAFE

**Crea file:** `LOGIN_DATABASE/database/TenantQuery.php`

Classe helper che aggiunge automaticamente filtro `tenant_id` a tutte le query:

```php
<?php
/**
 * CLASSE: TenantQuery
 * 
 * Wrapper per PDO che aggiunge automaticamente tenant_id
 * a SELECT, UPDATE, DELETE, INSERT
 * 
 * USO:
 *   $tq = new TenantQuery($conn);
 *   $products = $tq->select('Prodotto', ['User = ?'], [$username]);
 */

class TenantQuery {
    
    private $pdo;
    private $tenant_id;
    
    public function __construct($pdo, $tenant_id = null) {
        $this->pdo = $pdo;
        $this->tenant_id = $tenant_id ?? TenantManager::get_current_tenant_id();
    }
    
    /**
     * SELECT query con filtro tenant_id automatico
     */
    public function select($table, $where = [], $params = [], $order = null, $limit = null) {
        // Aggiungi always filtro tenant_id
        $where[] = 'tenant_id = ?';
        $params[] = $this->tenant_id;
        
        // Costruisci query
        $query = "SELECT * FROM $table WHERE " . implode(' AND ', $where);
        
        if ($order) {
            $query .= " ORDER BY $order";
        }
        
        if ($limit) {
            $query .= " LIMIT $limit";
        }
        
        $stmt = $this->pdo->prepare($query);
        $stmt->execute($params);
        
        return $stmt;
    }
    
    /**
     * INSERT con tenant_id automatico
     */
    public function insert($table, $data) {
        $data['tenant_id'] = $this->tenant_id;
        
        $columns = implode(', ', array_keys($data));
        $placeholders = implode(', ', array_fill(0, count($data), '?'));
        
        $query = "INSERT INTO $table ($columns) VALUES ($placeholders)";
        
        $stmt = $this->pdo->prepare($query);
        $stmt->execute(array_values($data));
        
        return $this->pdo->lastInsertId();
    }
    
    /**
     * UPDATE con filtro tenant_id automatico
     */
    public function update($table, $data, $where = [], $params = []) {
        $where[] = 'tenant_id = ?';
        $params[] = $this->tenant_id;
        
        $set_clauses = [];
        foreach (array_keys($data) as $col) {
            $set_clauses[] = "$col = ?";
        }
        
        $query = "UPDATE $table SET " . implode(', ', $set_clauses) 
                . " WHERE " . implode(' AND ', $where);
        
        $stmt = $this->pdo->prepare($query);
        $stmt->execute(array_merge(array_values($data), $params));
        
        return $stmt->rowCount();
    }
    
    /**
     * DELETE con filtro tenant_id automatico
     */
    public function delete($table, $where = [], $params = []) {
        $where[] = 'tenant_id = ?';
        $params[] = $this->tenant_id;
        
        $query = "DELETE FROM $table WHERE " . implode(' AND ', $where);
        
        $stmt = $this->pdo->prepare($query);
        $stmt->execute($params);
        
        return $stmt->rowCount();
    }
    
    /**
     * COUNT con filtro tenant_id
     */
    public function count($table, $where = [], $params = []) {
        $where[] = 'tenant_id = ?';
        $params[] = $this->tenant_id;
        
        $query = "SELECT COUNT(*) as total FROM $table WHERE " 
                . implode(' AND ', $where);
        
        $stmt = $this->pdo->prepare($query);
        $stmt->execute($params);
        
        return $stmt->fetch(PDO::FETCH_ASSOC)['total'];
    }
}
?>
```

**Usa in config.php:**

```php
<?php
// Aggiungi al file config.php
require_once __DIR__ . '/database/TenantQuery.php';

// Helper function globale
function get_tenant_query($pdo = null) {
    global $conn;
    $pdo = $pdo ?? $conn;
    return new TenantQuery($pdo);
}
?>
```

---

## 🔄 FASE 6: MODIFICHE API

### Template di Modifica API

Tutte le API in `api/swapper/` e `api/demo/` devono:

1. **Importare** TenantManager
2. **Validare** che utente appartiene al tenant
3. **Filtrare** query con tenant_id

**PRIMA (pericoloso):**

```php
<?php
// api/swapper/api_get_available_users.php

header('Content-Type: application/json');
require_once '../../config.php';
require_once '../../jwt.php';

$token = $_GET['token'] ?? null;
$user = validaJWT($token);

// ❌ PROBLEMA: Non filtra per tenant
$stmt = $conn->prepare("SELECT username FROM utenti WHERE username != ?");
$stmt->execute([$user]);
$users = $stmt->fetchAll();

echo json_encode(['success' => true, 'users' => $users]);
?>
```

**DOPO (sicuro):**

```php
<?php
// api/swapper/api_get_available_users.php

header('Content-Type: application/json');
require_once '../../config.php';
require_once '../../jwt.php';
require_once '../../config/TenantManager.php';

try {
    // 1️⃣ Valida JWT
    $token = $_GET['token'] ?? null;
    $user = validaJWT($token);
    
    if (!$user) {
        throw new Exception("Token non valido");
    }
    
    // 2️⃣ Ottieni tenant da sessione
    $tenant_id = TenantManager::get_current_tenant_id();
    
    if (!$tenant_id) {
        throw new Exception("Tenant non trovato in sessione");
    }
    
    // 3️⃣ Query FILTRATA per tenant
    $stmt = $conn->prepare(
        "SELECT username FROM utenti 
         WHERE username != ? AND tenant_id = ?"
    );
    $stmt->execute([$user, $tenant_id]);
    $users = $stmt->fetchAll();
    
    // 4️⃣ Risposta
    echo json_encode(['success' => true, 'users' => $users]);
    
} catch (Exception $e) {
    http_response_code(400);
    echo json_encode([
        'success' => false,
        'error' => $e->getMessage()
    ]);
}
?>
```

### Lista API da Modificare

Applica il template sopra a:

```
✅ api/swapper/api_accept_friend_request.php
✅ api/swapper/api_create_chat.php
✅ api/swapper/api_get_available_users.php
✅ api/swapper/api_get_chats.php
✅ api/swapper/api_get_non_friends.php
✅ api/swapper/api_get_pending_requests.php
✅ api/swapper/api_get_swapplus.php
✅ api/swapper/api_send_friend_request.php
✅ api/swapper/api_subscribe_swapplus.php
✅ api/demo/api_demo_NO_TRANSACTION.php
✅ api/demo/api_demo_WITH_TRANSACTION.php
```

---

## 🔐 FASE 7: SECURITY CHECKS

### Middleware Protezione

**Crea file:** `LOGIN_DATABASE/middleware/SessionValidator.php`

```php
<?php
/**
 * MIDDLEWARE: SessionValidator
 * 
 * Chiama all'inizio di pagine protette/API
 * per valida che utente è loggato + tenant è valido
 */

class SessionValidator {
    
    /**
     * Valida sessione + tenant per pagine protette
     * Usa in mockup_manager.php, ecc
     */
    public static function validate_session() {
        require_once __DIR__ . '/../config/TenantManager.php';
        
        // User loggato?
        if (empty($_SESSION['username'])) {
            header('Location: login.php');
            exit;
        }
        
        // Tenant in sessione?
        $tenant_id = TenantManager::get_current_tenant_id();
        if (!$tenant_id) {
            session_destroy();
            header('Location: login.php?error=no_tenant');
            exit;
        }
        
        // Tenant valido?
        if (!TenantManager::validate_tenant_id($tenant_id)) {
            session_destroy();
            header('Location: login.php?error=invalid_tenant');
            exit;
        }
        
        return $tenant_id;
    }
    
    /**
     * Valida JWT da API call
     */
    public static function validate_jwt() {
        $token = $_GET['token'] ?? $_POST['token'] ?? null;
        
        if (!$token) {
            throw new Exception("Token mancante");
        }
        
        if (!validaJWT($token)) {
            throw new Exception("JWT non valido");
        }
        
        return $_SESSION['tenant_id'];
    }
}
?>
```

**Usa in pagine protette:**

```php
<?php
session_start();
require_once 'config.php';
require_once 'middleware/SessionValidator.php';

// ✅ Valida sessione + tenant
$tenant_id = SessionValidator::validate_session();

// Da qui in poi, sei sicuro che tenant_id è valido
?>
```

### Validazioni Essenziali

- ✅ **Sempre** aggiungi `tenant_id` a WHERE clause
- ✅ **Sempre** verifica user appartiene al tenant prima di API
- ✅ **Mai** fidarti di tenant_id dal cliente - leggi da `$_SESSION`
- ✅ **Sempre** usa prepared statements
- ✅ **Valida** che risorse appartengono al tenant prima di modificare

---

## 🧪 FASE 8: TEST & DEPLOYMENT

### Test 1: Isolamento Tenant

```bash
# Scenario: Login come "gianno" in Bergamo, poi prova accesso Milano

# Step 1: Login Bergamo
POST /login/login.php
Data: username=gianno, password=..., tenant_id=1

# Step 2: Vedi prodotti Bergamo
GET /login/api/swapper/api_get_available_users.php?token=TOKEN
Response: ['sara_vintage', 'pietro', 'chiara_style'] (solo Bergamo!)

# Step 3: Logout e Login Milano
POST /login/login.php
Data: username=gianno, password=..., tenant_id=2

# Step 4: Prova acceder Bergamo con token Milano
GET /login/api/swapper/api_get_available_users.php?token=TOKEN
Response: ❌ 401 Unauthorized
(perché tenant_id nel token ≠ $_SESSION['tenant_id'])
```

### Test 2: Cross-Tenant Data Check

```sql
-- Verifica che ogni record ha tenant_id
SELECT COUNT(*) FROM utenti WHERE tenant_id IS NULL;  -- Deve essere 0
SELECT COUNT(*) FROM Prodotto WHERE tenant_id IS NULL;  -- Deve essere 0
```

### Checklist Pre-Deploy

- ✅ Database: Tabella `Tenant` creata
- ✅ Database: `tenant_id` aggiunto a tutte le tabelle
- ✅ Indici: Creati indici composti tenant_id + colonna
- ✅ Config: `tenants_config.php` creato
- ✅ Code: `TenantManager.php` creato
- ✅ Code: `TenantQuery.php` creato
- ✅ Code: `SessionValidator.php` creato
- ✅ Login: Form +dropdown tenant aggiunto
- ✅ Login: Logica login aggiornata con tenant_id
- ✅ API: Tutte API validate + filtrate per tenant
- ✅ Register: Nuovi utenti assegnati al tenant corretto
- ✅ Test: Cross-tenant access bloccato correttamente

---

## 📦 FILE DA CREARE/MODIFICARE

| File | Tipo | Descrizione |
|------|------|-------------|
| `config/tenants_config.php` | 🆕 Nuovo | Configurazione tenant |
| `config/TenantManager.php` | 🆕 Nuovo | Gestione tenant in sessione |
| `database/TenantQuery.php` | 🆕 Nuovo | Helper query tenant-safe |
| `middleware/SessionValidator.php` | 🆕 Nuovo | Middleware protezione |
| `login.php` | 📝 Modifica | Aggiorna form + logica |
| `register.php` | 📝 Modifica | Assegna tenant a nuovo user |
| `config.php` | 📝 Modifica | Importa TenantManager |
| `api/swapper/*.php` | 📝 Modifica | Aggiungi filtri tenant |
| `jwt.php` | 📝 Modifica | JWT include tenant_id |
| `mockup_manager.php` | 📝 Modifica | SessionValidator check |

---

## ✅ RECAP IMPLEMENTAZIONE

### Passo 1️⃣ Database (Esegui SQL)
```sql
-- Crea tabella Tenant
-- Aggiungi tenant_id a tutte le tabelle
-- Crea indici
```

### Passo 2️⃣ File Config
- Crea `config/tenants_config.php`
- Crea `config/TenantManager.php`
- Modifica `config.php` per importare TenantManager

### Passo 3️⃣ Login
- Modifica `login.php`: Aggiunta dropdown tenant
- Modifica logica login: Filtra per tenant_id
- Modifica `jwt.php`: Includi tenant_id nel token

### Passo 4️⃣ Helper Classes
- Crea `database/TenantQuery.php`
- Crea `middleware/SessionValidator.php`

### Passo 5️⃣ API Update
- Importa TenantManager in ogni API
- Aggiungi validazione tenant
- Filtra query con tenant_id

### Passo 6️⃣ Test
- Verifica isolamento tra tenant
- Prova cross-tenant access (deve fallire)

---

## ❓ DOMANDE COMUNI

**Q: Cosa succede se utente dimentica di selezionare una città?**
A: Form non submette (dropdown è `required`). Vedi messaggio di errore.

**Q: Posso ancora usare query normali?**
A: Sì, ma **DEVE includere** `WHERE tenant_id = ?`. Usa classe `TenantQuery` helper.

**Q: Cosa se dimentico il filtro tenant_id?**
A: ⚠️ **DATA LEAK** - utenti di Bergamo vedono dati di Milano!

**Q: Come aggiungo nuovo tenant?**
A:
1. INSERT riga in tabella `Tenant`
2. Aggiorna `config/tenants_config.php`
3. Deploy - automaticamente funziona!

**Q: E se saggio da due browser contemporاneo (due tenant)?**
A: Ogni browser ha la propria sessione. Non c'è problema.

---

**Fine Guida! Buona implementazione 🚀**
