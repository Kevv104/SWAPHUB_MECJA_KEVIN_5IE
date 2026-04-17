# 🏢 GUIDA COMPLETA - IMPLEMENTAZIONE MULTI-TENANCY IN SWAPHUB

**Versione:** 1.0  
**Data:** Aprile 2026  
**Strategia:** Schema condiviso con colonna `tenant_id` (Host-based routing)

---

## 📋 INDICE

1. [Concetti Fondamentali](#-concetti-fondamentali)
2. [Strategia Scelta](#-strategia-scelta)
3. [Fase 1: Modifiche Database](#-fase-1-modifiche-database)
4. [Fase 2: Configurazione Tenants](#-fase-2-configurazione-tenants)
5. [Fase 3: Architettura PHP - Tenant Manager](#-fase-3-architettura-php---tenant-manager)
6. [Fase 4: Middleware Isolamento Dati](#-fase-4-middleware-isolamento-dati)
7. [Fase 5: Modifiche API](#-fase-5-modifiche-api)
8. [Fase 6: Gestione Utenti Tenant](#-fase-6-gestione-utenti-tenant)
9. [Fase 7: Security & Validazione](#-fase-7-security--validazione)
10. [Fase 8: Test e Deployment](#-fase-8-test--deployment)

---

## 🎯 CONCETTI FONDAMENTALI

### Cos'è la Multi-Tenancy?

Un'unica applicazione serve **più clienti (tenant)** in **isolamento logico** usando **infrastruttura condivisa**.

### I Tre Approcci Principali:

| Approccio | DB | Schema | Row-Level Filtering | Vantaggi | Svantaggi |
|-----------|----|----|-----|----------|----------|
| **Separate Database** | Uno per tenant | Unico | ✅ Automatico | Massima isolamento | Costo ↑, manutenzione complessa |
| **Separate Schema** | Uno condiviso | Uno per tenant | ✅ Automatico | Buon isolamento | Overhead management schema |
| **Schema Condiviso** ⭐ | Uno condiviso | Unico | ❌ Manuale (WHERE tenant_id) | Cost-effective, scalabile | Richiede attenzione query |

**Tu userai: Schema Condiviso** ← Il più adatto per scale-up futura

---

## 🏗️ STRATEGIA SCELTA

### Identificazione Tenant

```
Tenant ID → Estratto dal DOMAIN NAME (Host)

https://bergamo.swaphub.it/login/...    → tenant_id = 1 ("bergamo")
https://milano.swaphub.it/login/...     → tenant_id = 2 ("milano")
https://torino.swaphub.it/login/...     → tenant_id = 3 ("torino")
```

### Tabella di Configurazione Tenants

Creerai una tabella `Tenant` per mappare:

```
id | nome_tenant | city | subdomain | is_active | created_at
1  | bergamo     | Bergamo | bergamo | 1 | 2026-04-17
2  | milano      | Milano | milano  | 1 | 2026-04-17
3  | torino      | Turin | torino   | 1 | 2026-04-17
```

### Principio Fondamentale: Isolamento Automatico

**Ogni istanza PHP deve conoscere il tenant corrente** e filtrare automaticamente tutte le query per quel tenant.

```php
// PRIMA (senza multi-tenancy - PERICOLOSO):
$stmt = $pdo->prepare("SELECT * FROM Prodotto WHERE User = ?");

// DOPO (con multi-tenancy - SICURO):
$stmt = $pdo->prepare("SELECT * FROM Prodotto WHERE tenant_id = ? AND User = ?");
$stmt->execute([$TENANT_ID, $username]);
```

---

## 🔄 FASE 1: MODIFICHE DATABASE

### Step 1.1: Creare Tabella Tenants

```sql
CREATE TABLE `Tenant` (
  `id` int(11) NOT NULL AUTO_INCREMENT PRIMARY KEY,
  `nome_tenant` varchar(100) NOT NULL UNIQUE,
  `city` varchar(100) NOT NULL,
  `subdomain` varchar(50) NOT NULL UNIQUE,
  `is_active` tinyint(1) NOT NULL DEFAULT 1,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `updated_at` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

INSERT INTO `Tenant` (`nome_tenant`, `city`, `subdomain`, `is_active`) VALUES
('bergamo', 'Bergamo', 'bergamo', 1),
('milano', 'Milano', 'milano', 1),
('torino', 'Torino', 'torino', 1);
```

### Step 1.2: Aggiungere Colonna `tenant_id` a TUTTE le tabelle

Per **ogni tabella** (eccetto Tenant, Ruolo, Permesso), aggiungi:

```sql
-- Per la tabella Categoria
ALTER TABLE Categoria ADD COLUMN tenant_id INT(11) NOT NULL DEFAULT 1 AFTER NomeCategoria;
ALTER TABLE Categoria ADD FOREIGN KEY (tenant_id) REFERENCES Tenant(id) ON DELETE CASCADE;

-- Per la tabella Chat
ALTER TABLE Chat ADD COLUMN tenant_id INT(11) NOT NULL DEFAULT 1 AFTER idChat;
ALTER TABLE Chat ADD FOREIGN KEY (tenant_id) REFERENCES Tenant(id) ON DELETE CASCADE;

-- Per la tabella Messaggi
ALTER TABLE Messaggi ADD COLUMN tenant_id INT(11) NOT NULL DEFAULT 1 AFTER idMessaggio;
ALTER TABLE Messaggi ADD FOREIGN KEY (tenant_id) REFERENCES Tenant(id) ON DELETE CASCADE;

-- Per la tabella PartecipaChat
ALTER TABLE PartecipaChat ADD COLUMN tenant_id INT(11) NOT NULL DEFAULT 1 AFTER idChat;
ALTER TABLE PartecipaChat ADD FOREIGN KEY (tenant_id) REFERENCES Tenant(id) ON DELETE CASCADE;

-- Per la tabella Consegna
ALTER TABLE Consegna ADD COLUMN tenant_id INT(11) NOT NULL DEFAULT 1 AFTER idConsegna;
ALTER TABLE Consegna ADD FOREIGN KEY (tenant_id) REFERENCES Tenant(id) ON DELETE CASCADE;

-- Per la tabella Prodotto
ALTER TABLE Prodotto ADD COLUMN tenant_id INT(11) NOT NULL DEFAULT 1 AFTER idProdotto;
ALTER TABLE Prodotto ADD FOREIGN KEY (tenant_id) REFERENCES Tenant(id) ON DELETE CASCADE;

-- Per la tabella Scambio
ALTER TABLE Scambio ADD COLUMN tenant_id INT(11) NOT NULL DEFAULT 1 AFTER idScambio;
ALTER TABLE Scambio ADD FOREIGN KEY (tenant_id) REFERENCES Tenant(id) ON DELETE CASCADE;

-- Per la tabella RichiesteAmicizia
ALTER TABLE RichiesteAmicizia ADD COLUMN tenant_id INT(11) NOT NULL DEFAULT 1 AFTER idRichiesta;
ALTER TABLE RichiesteAmicizia ADD FOREIGN KEY (tenant_id) REFERENCES Tenant(id) ON DELETE CASCADE;

-- Per la tabella Recensioni
ALTER TABLE Recensioni ADD COLUMN tenant_id INT(11) NOT NULL DEFAULT 1 AFTER idRecensione;
ALTER TABLE Recensioni ADD FOREIGN KEY (tenant_id) REFERENCES Tenant(id) ON DELETE CASCADE;

-- Per la tabella Segnalazioni
ALTER TABLE Segnalazioni ADD COLUMN tenant_id INT(11) NOT NULL DEFAULT 1 AFTER idSegnalazione;
ALTER TABLE Segnalazioni ADD FOREIGN KEY (tenant_id) REFERENCES Tenant(id) ON DELETE CASCADE;

-- Per la tabella SwapPlus
ALTER TABLE SwapPlus ADD COLUMN tenant_id INT(11) NOT NULL DEFAULT 1 AFTER idAbbonamento;
ALTER TABLE SwapPlus ADD FOREIGN KEY (tenant_id) REFERENCES Tenant(id) ON DELETE CASCADE;

-- Per la tabella utenti
ALTER TABLE utenti ADD COLUMN tenant_id INT(11) NOT NULL DEFAULT 1 AFTER username;
ALTER TABLE utenti ADD FOREIGN KEY (tenant_id) REFERENCES Tenant(id) ON DELETE CASCADE;
```

### Step 1.3: Aggiungere Indici per Performance

```sql
-- Indici composti per query rapide filtrate per tenant
ALTER TABLE Prodotto ADD INDEX idx_tenant_user (tenant_id, User);
ALTER TABLE Chat ADD INDEX idx_tenant_id (tenant_id);
ALTER TABLE Messaggi ADD INDEX idx_tenant_chat (tenant_id, idChat);
ALTER TABLE utenti ADD INDEX idx_tenant_username (tenant_id, username);
ALTER TABLE Scambio ADD INDEX idx_tenant_utente (tenant_id, idUtenteMit);
```

---

## ⚙️ FASE 2: CONFIGURAZIONE TENANTS

### Step 2.1: Crea File di Configurazione Tenants

**Percorso:** `LOGIN_DATABASE/config/tenants_config.php`

```php
<?php
/**
 * CONFIGURAZIONE MULTI-TENANCY
 * 
 * Definisce tutti i tenant disponibili e mapping URL → tenant_id
 */

return [
    'tenants' => [
        'bergamo' => [
            'id'       => 1,
            'name'     => 'bergamo',
            'city'     => 'Bergamo',
            'subdomain' => 'bergamo',
        ],
        'milano' => [
            'id'       => 2,
            'name'     => 'milano',
            'city'     => 'Milano',
            'subdomain' => 'milano',
        ],
        'torino' => [
            'id'       => 3,
            'name'     => 'torino',
            'city'     => 'Torino',
            'subdomain' => 'torino',
        ],
    ],
    
    // Mappatura inversa: subdomain → tenant_id
    'subdomain_map' => [
        'bergamo' => 1,
        'milano'  => 2,
        'torino'  => 3,
    ],
];
?>
```

### Step 2.2: Funzione di Estrazione Tenant dal Host

**Aggiungi a:** `LOGIN_DATABASE/config/TenantManager.php` (nuovo file)

```php
<?php
/**
 * CLASSE: TenantManager
 * 
 * Responsabilità:
 * - Estrarre tenant_id dal domain attuale
 * - Validare tenant attivo
 * - Fornire contesto tenant all'applicazione
 */

class TenantManager {
    
    private static $current_tenant_id = null;
    private static $current_tenant_name = null;
    private $config;
    
    public function __construct() {
        $this->config = require __DIR__ . '/tenants_config.php';
    }
    
    /**
     * Estrae il tenant dal domain corrente
     * 
     * Es: bergamo.swaphub.it → restituisce 1
     *     localhost (dev) → restituisce 1 (default)
     */
    public function detect_tenant_from_host() {
        $host = $_SERVER['HTTP_HOST'] ?? 'localhost';
        
        // Estrai il subdomain (prima parte prima del punto)
        $parts = explode('.', $host);
        $subdomain = strtolower($parts[0]);
        
        // Rimuovi porta se presente (localhost:8080 → localhost)
        if (strpos($subdomain, ':') !== false) {
            $subdomain = explode(':', $subdomain)[0];
        }
        
        // Cerca nel mapping
        if (isset($this->config['subdomain_map'][$subdomain])) {
            $tenant_id = $this->config['subdomain_map'][$subdomain];
            $tenant_name = array_key_first(
                array_filter($this->config['tenants'], 
                    fn($t) => $t['id'] == $tenant_id)
            );
            
            self::$current_tenant_id = $tenant_id;
            self::$current_tenant_name = $tenant_name;
            
            return $tenant_id;
        }
        
        // Default a tenant 1 (Bergamo) se non trovato
        self::$current_tenant_id = 1;
        self::$current_tenant_name = 'bergamo';
        return 1;
    }
    
    /**
     * Ritorna tenant_id corrente
     */
    public static function get_current_tenant_id() {
        if (self::$current_tenant_id === null) {
            $manager = new self();
            $manager->detect_tenant_from_host();
        }
        return self::$current_tenant_id;
    }
    
    /**
     * Ritorna nome tenant corrente
     */
    public static function get_current_tenant_name() {
        if (self::$current_tenant_name === null) {
            $manager = new self();
            $manager->detect_tenant_from_host();
        }
        return self::$current_tenant_name;
    }
    
    /**
     * Ritorna array completo tenant corrente
     */
    public static function get_current_tenant() {
        $tenant_id = self::get_current_tenant_id();
        $config = require __DIR__ . '/tenants_config.php';
        
        foreach ($config['tenants'] as $tenant) {
            if ($tenant['id'] == $tenant_id) {
                return $tenant;
            }
        }
        return null;
    }
}
?>
```

### Step 2.3: Inizializzare TenantManager in config.php

**Modifica:** `LOGIN_DATABASE/config.php` (inizio file)

```php
<?php
// MULTI-TENANCY: Inizializza gestore tenant
require_once __DIR__ . '/config/TenantManager.php';

$tenantManager = new TenantManager();
$TENANT_ID = $tenantManager->detect_tenant_from_host();
$TENANT_NAME = TenantManager::get_current_tenant_name();

// Ora tutte le variabili sono disponibili:
// $TENANT_ID = 1 (id del tenant)
// $TENANT_NAME = 'bergamo' (nome tenant)

// ... resto della configurazione
?>
```

---

## 🐘 FASE 3: ARCHITETTURA PHP - TENANT MANAGER

### Step 3.1: Classe Base per Query Tenant-Safe

**Crea:** `LOGIN_DATABASE/database/TenantQuery.php`

```php
<?php
/**
 * CLASSE: TenantQuery
 * 
 * Wrapper per PDO che aggiunge automaticamente filtro tenant_id
 * a tutte le query SELECT, UPDATE, DELETE.
 * 
 * Uso:
 * $query = new TenantQuery($pdo);
 * $query->select('Prodotto', ['User = ?'], [$username]);
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
     * 
     * @param string $table - Nome tabella
     * @param array $where - Array di condizioni WHERE (es: ['User = ?', 'stato = ?'])
     * @param array $params - Array di parametri corrispondenti
     * @param array $order - Ordinamento (opzionale)
     * @param int $limit - Limite risultati (opzionale)
     * 
     * @return PDOStatement
     */
    public function select($table, $where = [], $params = [], $order = null, $limit = null) {
        
        // Aggiungi sempre filtro tenant_id
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
        
        // Effettua query con tutti i parametri (incluso tenant_id)
        $stmt = $this->pdo->prepare($query);
        $stmt->execute($params);
        
        return $stmt;
    }
    
    /**
     * SELECT con colonne specifiche
     */
    public function select_columns($table, $columns, $where = [], $params = []) {
        $where[] = 'tenant_id = ?';
        $params[] = $this->tenant_id;
        
        $col_str = implode(', ', $columns);
        $query = "SELECT $col_str FROM $table WHERE " . implode(' AND ', $where);
        
        $stmt = $this->pdo->prepare($query);
        $stmt->execute($params);
        
        return $stmt;
    }
    
    /**
     * INSERT con tenant_id automatico
     */
    public function insert($table, $data) {
        // Aggiungi tenant_id ai dati
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
        // Aggiungi filtro tenant_id
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
        // Aggiungi filtro tenant_id
        $where[] = 'tenant_id = ?';
        $params[] = $this->tenant_id;
        
        $query = "DELETE FROM $table WHERE " . implode(' AND ', $where);
        
        $stmt = $this->pdo->prepare($query);
        $stmt->execute($params);
        
        return $stmt->rowCount();
    }
    
    /**
     * Conteggio righe con filtro tenant_id
     */
    public function count($table, $where = [], $params = []) {
        $where[] = 'tenant_id = ?';
        $params[] = $this->tenant_id;
        
        $query = "SELECT COUNT(*) as total FROM $table WHERE " . implode(' AND ', $where);
        
        $stmt = $this->pdo->prepare($query);
        $stmt->execute($params);
        
        return $stmt->fetch(PDO::FETCH_ASSOC)['total'];
    }
}
?>
```

### Step 3.2: Helper Globale per TenantQuery

**Modifica:** `LOGIN_DATABASE/connectdb_pdo.php`

```php
<?php
// Aggiungi dopo la connessione PDO
require_once __DIR__ . '/database/TenantQuery.php';

// Funzione globale helper
function get_tenant_query($pdo = null) {
    global $conn; // Connessione PDO globale
    $pdo = $pdo ?? $conn;
    return new TenantQuery($pdo);
}

// Uso nel codice:
// $tq = get_tenant_query();
// $products = $tq->select('Prodotto', ['User = ?'], [$username]);
?>
```

---

## 🔒 FASE 4: MIDDLEWARE ISOLAMENTO DATI

### Step 4.1: Classe di Protezione API

**Crea:** `LOGIN_DATABASE/middleware/TenantProtection.php`

```php
<?php
/**
 * MIDDLEWARE: TenantProtection
 * 
 * Valida che l'utente loggato appartiene al tenant corrente
 * Previene cross-tenant data access
 */

class TenantProtection {
    
    private $pdo;
    private $current_tenant_id;
    
    public function __construct($pdo) {
        $this->pdo = $pdo;
        $this->current_tenant_id = TenantManager::get_current_tenant_id();
    }
    
    /**
     * Valida che un utente appartiene al tenant corrente
     * 
     * IMPORTANTE: Chiama questa all'inizio di ogni API
     * 
     * @param string $username - Username da verificare
     * @return bool - true se valido, false altrimenti
     */
    public function validate_user_in_tenant($username) {
        $stmt = $this->pdo->prepare(
            "SELECT COUNT(*) as count FROM utenti 
             WHERE username = ? AND tenant_id = ?"
        );
        $stmt->execute([$username, $this->current_tenant_id]);
        $result = $stmt->fetch(PDO::FETCH_ASSOC);
        
        return $result['count'] > 0;
    }
    
    /**
     * Valida che una risorsa (prodotto, chat, ecc) appartiene al tenant
     * 
     * @param string $table - Nome tabella
     * @param int $resource_id - ID risorsa
     * @param string $id_column - Nome colonna ID (default: id{Table})
     * @return bool
     */
    public function validate_resource_in_tenant($table, $resource_id, $id_column = null) {
        if ($id_column === null) {
            $id_column = 'id' . ucfirst($table);
        }
        
        $stmt = $this->pdo->prepare(
            "SELECT COUNT(*) as count FROM $table 
             WHERE $id_column = ? AND tenant_id = ?"
        );
        $stmt->execute([$resource_id, $this->current_tenant_id]);
        $result = $stmt->fetch(PDO::FETCH_ASSOC);
        
        return $result['count'] > 0;
    }
    
    /**
     * Risposta errore JSON
     */
    public static function error_response($message, $http_code = 403) {
        http_response_code($http_code);
        return json_encode([
            'success' => false,
            'message' => $message,
            'error_code' => 'TENANT_VIOLATION'
        ]);
    }
}
?>
```

---

## 🔄 FASE 5: MODIFICHE API

### Pattern di Modifica per API

**PRIMA (senza multi-tenancy):**

```php
<?php
// api/swapper/api_get_available_users.php

header('Content-Type: application/json');
require_once '../../config.php';
require_once '../../jwt.php';

$token = $_GET['token'] ?? null;
$user = validaJWT($token);

// ❌ PERICOLOSO: Non filtra per tenant
$stmt = $conn->prepare("SELECT username FROM utenti WHERE username != ?");
$stmt->execute([$user]);
$users = $stmt->fetchAll();

echo json_encode(['success' => true, 'users' => $users]);
?>
```

**DOPO (con multi-tenancy):**

```php
<?php
// api/swapper/api_get_available_users.php

header('Content-Type: application/json');
require_once '../../config.php';
require_once '../../jwt.php';
require_once '../../middleware/TenantProtection.php';

$token = $_GET['token'] ?? null;
$user = validaJWT($token);

// 1️⃣ VALIDAZIONE TENANT
$tenant_protection = new TenantProtection($conn);
$current_tenant = TenantManager::get_current_tenant_id();

// Valida che l'utente loggato è nel tenant corrente
if (!$tenant_protection->validate_user_in_tenant($user)) {
    echo TenantProtection::error_response(
        "Utente non appartiene a questo tenant", 
        403
    );
    exit;
}

// 2️⃣ QUERY CON FILTRO TENANT
$stmt = $conn->prepare(
    "SELECT username FROM utenti 
     WHERE username != ? AND tenant_id = ?"
);
$stmt->execute([$user, $current_tenant]);
$users = $stmt->fetchAll();

echo json_encode(['success' => true, 'users' => $users]);
?>
```

### Step 5.1: Modifica Template API

Applica il pattern sopra a **tutte le API** in `LOGIN_DATABASE/api/`:

**Liste di file da modificare:**

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

## 👥 FASE 6: GESTIONE UTENTI TENANT

### Step 6.1: Modificare Registrazione Utente

**Modifica:** `LOGIN_DATABASE/register.php`

```php
<?php
// Aggiungi al momento della registrazione:
require_once 'config.php';
require_once 'config/TenantManager.php';

// ... codice registrazione ...

$current_tenant = TenantManager::get_current_tenant_id();

// Inserisci utente con tenant_id
$stmt = $conn->prepare(
    "INSERT INTO utenti (username, password, salt, bgcolor, 
                         Nome, Cognome, Email, localita, tenant_id) 
     VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?)"
);

$stmt->execute([
    $username,
    $hashed_password,
    $salt,
    $bgcolor,
    $nome,
    $cognome,
    $email,
    $localita,
    $current_tenant  // ← IMPORTANTE: Aggiungi tenant_id
]);
?>
```

### Step 6.2: Modificare Login

**Modifica:** `LOGIN_DATABASE/login.php`

```php
<?php
// Nel controllo login:
require_once 'config.php';
require_once 'config/TenantManager.php';

$current_tenant = TenantManager::get_current_tenant_id();

$stmt = $conn->prepare(
    "SELECT username, password, salt FROM utenti 
     WHERE username = ? AND tenant_id = ? AND isBanned = 0"
);

$stmt->execute([$username, $current_tenant]);

// ... resto della logica ...
?>
```

---

## 🔐 FASE 7: SECURITY & VALIDAZIONE

### Security Checklist

- ✅ **Sempre** aggiungi `tenant_id` a WHERE clause
- ✅ **Sempre** valida utente appartiene al tenant prima di accesso API
- ✅ **Mai** fidarti di tenant_id dal client - sempre estrai da host/session
- ✅ **Sempre** usa prepared statements (protezione SQL injection)
- ✅ **Valida** che risorse (prodotti, chat) appartengono al tenant prima di modificare

### Step 7.1: Helper Funzione Sicurezza

**Crea:** `LOGIN_DATABASE/security/TenantValidator.php`

```php
<?php
/**
 * HELPER: Validazioni Tenant
 */

class TenantValidator {
    
    private $pdo;
    private $current_tenant;
    
    public function __construct($pdo) {
        $this->pdo = $pdo;
        $this->current_tenant = TenantManager::get_current_tenant_id();
    }
    
    /**
     * Assert: Throw exception se validazione fallisce
     */
    public function assert_user_in_tenant($username) {
        $stmt = $this->pdo->prepare(
            "SELECT COUNT(*) FROM utenti WHERE username = ? AND tenant_id = ?"
        );
        $stmt->execute([$username, $this->current_tenant]);
        
        if ($stmt->fetchColumn() == 0) {
            throw new Exception(
                "User access denied" . 
                " [User: $username - Tenant: {$this->current_tenant}]"
            );
        }
    }
    
    /**
     * Assert: Risorsa appartiene al tenant
     */
    public function assert_resource_in_tenant($table, $id, $id_col) {
        $stmt = $this->pdo->prepare(
            "SELECT COUNT(*) FROM $table WHERE $id_col = ? AND tenant_id = ?"
        );
        $stmt->execute([$id, $this->current_tenant]);
        
        if ($stmt->fetchColumn() == 0) {
            throw new Exception(
                "Resource not found or access denied" .
                " [Table: $table - ID: $id - Tenant: {$this->current_tenant}]"
            );
        }
    }
}
?>
```

### Step 7.2: Uso nei Controller/API

```php
<?php
try {
    $validator = new TenantValidator($conn);
    
    // Validazione all'inizio della API
    $validator->assert_user_in_tenant($logged_user);
    $validator->assert_resource_in_tenant('Prodotto', $product_id, 'idProdotto');
    
    // Se arrive qui, tutto è sicuro
    // ... logica applicazione ...
    
} catch (Exception $e) {
    http_response_code(403);
    echo json_encode([
        'success' => false,
        'error' => 'Access denied'
    ]);
}
?>
```

---

## 🧪 FASE 8: TEST E DEPLOYMENT

### Step 8.1: Test Isolamento Tenant

**Scenario 1: Cross-Tenant Access (DEVE FALLIRE)**

```bash
# 1. Login come "gianno" su bergamo.swaphub.it
POST /login/login.php
username=gianno&password=...
Cookie → JWT token A

# 2. Estrai prodotti di "gianno"
GET /login/api/swapper/api_get_available_users.php?token=TOKEN_A
ResponseA: ['sara_vintage', 'pietro', ...] (solo da Bergamo)

# 3. Prova accedere da milano.swaphub.it con stesso token
POST /login/login.php
username=gianno&password=...
Cookie → JWT token B

GET /login/api/swapper/api_get_available_users.php?token=TOKEN_B
Response B: ❌ 403 Forbidden (utente "gianno" non esiste su milano)
```

### Step 8.2: Checklist Pre-Deployment

- ✅ Tutte le tabelle hanno colonna `tenant_id`
- ✅ File `config/TenantManager.php` creato
- ✅ Classe `TenantQuery` importata in `connectdb_pdo.php`
- ✅ Tutte le API validate con `TenantProtection`
- ✅ Login e Register includono `tenant_id`
- ✅ Test cross-tenant access fallisce correttamente
- ✅ Indici aggiunti per performance

### Step 8.3: Deployment

```bash
# 1. Backup database
mysqldump -u root -p SWAPHUB > backup_before_mt.sql

# 2. Esegui script SQL (Step 1.1, 1.2, 1.3)
mysql -u root -p SWAPHUB < migration_add_tenant_columns.sql

# 3. Deploy codice PHP modificato
# - Carica TenantManager.php
# - Carica TenantQuery.php
# - Carica TenantProtection.php
# - Aggiorna tutti i file API

# 4. Test su staging PRIMA di production
```

---

## 📚 SUMMARY - COSA IMPLEMENTARE

| Fase | File | Azione |
|------|------|--------|
| 1 | SWAPHUBSQL.sql | Aggiungere colonna `tenant_id` a tutte tabelle |
| 2 | config/tenants_config.php | ✨ Nuovo file con mapping tenant |
| 2 | config/TenantManager.php | ✨ Nuovo file per estrazione tenant |
| 3 | database/TenantQuery.php | ✨ Nuovo file per query tenant-safe |
| 4 | middleware/TenantProtection.php | ✨ Nuovo file per protezione dati |
| 5 | api/swapper/*.php | Aggiungere validazioni tenant a tutte API |
| 6 | register.php, login.php | Aggiungere `tenant_id` a INSERT/SELECT |
| 7 | security/TenantValidator.php | ✨ Nuovo file per assert validazioni |

---

## 🎯 NEXT STEPS

1. **Esegui Fase 1** - Modifica database
2. **Esegui Fase 2** - Setup configurazione tenant manager
3. **Test Fase 3** - Verifica TenantManager estrae corretto tenant da host
4. **Implementa Fase 4** - Middleware protezione
5. **Modifica Fase 5** - Aggiorna tutte API
6. **Dev Fase 6-7** - Login/Register e security checks
7. **Test Fase 8** - Validation suite

---

## ❓ DOMANDE COMUNI

**Q: Posso ancora usare query normali?**  
A: Sì, ma DEVE includere `WHERE tenant_id = ?`. Consiglio di usare `TenantQuery` helper.

**Q: Cosa succede se dimentico il filtro tenant_id?**  
A: ⚠️ **DATA LEAK** - utenti di Bergamo vedranno dati di Milano!

**Q: E per il database di staging?**  
A: Usa stesso schema, copia `tenants_config.php`, cambia solo `subdomain_map`.

**Q: Come aggiungo nuovo tenant?**  
A:
1. Aggiungi riga in `Tenant` table
2. Aggiorna `config/tenants_config.php`
3. Configura DNS subdomain.swaphub.it
4. Deploy - automaticamente funziona!

---

**Fine Guida - Buona implementazione! 🚀**
