<?php
// API: api_send_friend_request.php
// CASO D'USO: send_friend_request (Permesso #5)
// DESCRIZIONE: Invia una richiesta di amicizia a un utente

header('Content-Type: application/json');
require_once __DIR__ . '/../../vendor/autoload.php';
require_once '../../jwt.php';
require_once '../../connectdb.php';

use Firebase\JWT\JWT;
use Firebase\JWT\Key;

/**
 * --- CONFIGURAZIONE AMBIENTE (BYPASS POSTMAN) ---
 */
$isDevelopment = false; 

if ($isDevelopment) {
    $currentUser = 'gianno'; 
    $tenantLookup = $connessione->prepare("SELECT tenant_id FROM utenti WHERE username = ?");
    $tenantLookup->bind_param("s", $currentUser);
    $tenantLookup->execute();
    $tenantResult = $tenantLookup->get_result();
    $tenantRow = $tenantResult->fetch_assoc();
    $tenantLookup->close();

    if (!$tenantRow || empty($tenantRow['tenant_id'])) {
        http_response_code(401);
        echo json_encode(["success" => false, "error" => "Tenant utente non trovato"]);
        exit;
    }

    $currentTenantId = (int)$tenantRow['tenant_id'];
} else {
    session_start(['cookie_path' => '/login/']);
    if(!isset($_SESSION['jwt'])) {
        http_response_code(401);
        echo json_encode(["success" => false, "error" => "Non autorizzato"]); 
        exit;
    }
    try {
        $decoded = JWT::decode($_SESSION['jwt'], new Key(JWT_SECRET, JWT_ALGO));
        $currentUser = $decoded->sub;
        $currentTenantId = isset($decoded->tenant_id) ? (int)$decoded->tenant_id : 0;
        if(!$currentUser || $currentTenantId <= 0) {
            throw new Exception("Token privo di tenant valido");
        }
    } catch (Exception $e) {
        http_response_code(401);
        echo json_encode(["success" => false, "error" => "Token non valido"]);
        exit;
    }
}

// Controllo Metodo
if($_SERVER['REQUEST_METHOD'] !== 'POST') {
    http_response_code(405);
    echo json_encode(["success" => false, "error" => "Metodo non consentito. Usa POST"]);
    exit;
}

try {
    // Leggiamo i dati dal Body JSON di Postman
    $input = json_decode(file_get_contents('php://input'), true);
    $userRicevente = $input['userRicevente'] ?? null; 

    if(empty($userRicevente)) {
        echo json_encode(["success" => false, "error" => "Username destinatario obbligatorio"]);
        exit;
    }

    if($userRicevente === $currentUser) {
        echo json_encode(["success" => false, "error" => "Non puoi inviare una richiesta a te stesso"]);
        exit;
    }

    // 1. Verifica se l'utente destinatario esiste
    $checkUser = $connessione->prepare("SELECT username, tenant_id FROM utenti WHERE username = ?");
    $checkUser->bind_param("s", $userRicevente);
    $checkUser->execute();
    $resultUser = $checkUser->get_result();

    if($resultUser->num_rows === 0) {
        echo json_encode(["success" => false, "error" => "Utente destinatario non trovato"]);
        $checkUser->close();
        exit;
    }

    $userRiceventeData = $resultUser->fetch_assoc();
    if ((int)$userRiceventeData['tenant_id'] !== $currentTenantId) {
        echo json_encode(["success" => false, "error" => "Puoi inviare richieste solo a utenti del tuo tenant"]);
        $checkUser->close();
        exit;
    }

    $checkUser->close();
      
    // 2. Verifica se esiste già una richiesta tra i due (inviata o accettata)
    $checkRichiesta = $connessione->prepare("
        SELECT idRichiesta, stato 
        FROM RichiesteAmicizia 
        WHERE (UserMittente = ? AND UserDestinatario = ?)
           OR (UserMittente = ? AND UserDestinatario = ?)
    ");

    $checkRichiesta->bind_param("ssss", $currentUser, $userRicevente, $userRicevente, $currentUser);
    $checkRichiesta->execute();
    $resultRichiesta = $checkRichiesta->get_result();

    if($resultRichiesta->num_rows > 0) {
        $richiestaEsistente = $resultRichiesta->fetch_assoc();

        if($richiestaEsistente['stato'] === 'inviata') {
            echo json_encode(["success" => false, "error" => "Richiesta già in attesa"]);
        } else if($richiestaEsistente['stato'] === 'accettata') {
            echo json_encode(["success" => false, "error" => "Siete già amici!"]);
        } else {
            echo json_encode(["success" => false, "error" => "Stato richiesta: " . $richiestaEsistente['stato']]);
        }
        $checkRichiesta->close();
        exit;
    }
    $checkRichiesta->close();

    // 3. Creazione richiesta amicizia
    $insertQuery = $connessione->prepare("
        INSERT INTO RichiesteAmicizia (UserMittente, UserDestinatario, stato, commento)
        VALUES (?, ?, 'inviata', '')
    ");

    $insertQuery->bind_param("ss", $currentUser, $userRicevente);
    $insertQuery->execute();

    $idRichiesta = $connessione->insert_id;
    $dataRichiesta = date('Y-m-d H:i:s');

    echo json_encode([
        "success" => true,
        "message" => "Richiesta inviata con successo!",
        "richiesta" => [
            'idRichiesta' => $idRichiesta,
            'userMittente' => $currentUser,
            'userDestinatario' => $userRicevente,
            'tenant_id' => $currentTenantId,
            'stato' => 'inviata',
            'dataRichiesta' => $dataRichiesta
        ]
    ]);

    $insertQuery->close();
    $connessione->close();

} catch (Exception $e) {
    http_response_code(500);
    echo json_encode(["success" => false, "error" => $e->getMessage()]);
}
?>