<?php
// API: api_accept_friend_request.php
// DESCRIZIONE: Accetta una richiesta di amicizia in entrata

header('Content-Type: application/json');
require_once __DIR__ . '/../../vendor/autoload.php';
require_once '../../jwt.php';
require_once '../../connectdb.php';

use Firebase\JWT\JWT;
use Firebase\JWT\Key;

/**
 * --- CONFIGURAZIONE AMBIENTE (BYPASS POSTMAN) ---
 */
$isDevelopment = true; 

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
    if (session_status() === PHP_SESSION_NONE) {
        session_start(['cookie_path' => '/login/']);
    }

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

// Verifica metodo POST
if($_SERVER['REQUEST_METHOD'] !== 'POST') {
    http_response_code(405);
    echo json_encode(["success" => false, "error" => "Metodo non consentito. Usa POST"]);
    exit;
}

try {
    // Lettura input JSON
    $input = json_decode(file_get_contents('php://input'), true);
    $idRichiesta = $input['idRichiesta'] ?? null;

    if(empty($idRichiesta)) {
        echo json_encode([
            "success" => false,
            "error" => "ID richiesta obbligatorio"
        ]);
        exit;
    }

    $checkQuery = $connessione->prepare("
                SELECT ra.idRichiesta, ra.UserMittente, ra.UserDestinatario, ra.stato
                FROM RichiesteAmicizia ra
                JOIN utenti um ON um.username = ra.UserMittente
                JOIN utenti ud ON ud.username = ra.UserDestinatario
                WHERE ra.idRichiesta = ?
                    AND ra.UserDestinatario = ?
                    AND um.tenant_id = ?
                    AND ud.tenant_id = ?
    ");

        $checkQuery->bind_param("isii", $idRichiesta, $currentUser, $currentTenantId, $currentTenantId);
    $checkQuery->execute();
    $result = $checkQuery->get_result();

    if($result->num_rows === 0) {
        echo json_encode([
            "success" => false,
            "error" => "Richiesta non trovata o non sei il destinatario autorizzato"
        ]);
        $checkQuery->close();
        exit;
    }

    $richiesta = $result->fetch_assoc();
    $checkQuery->close();

    // 2. Verifica se la richiesta è ancora in stato 'inviata'
    if($richiesta['stato'] !== 'inviata') {
        echo json_encode([
            "success" => false,
            "error" => "La richiesta non è più in attesa (stato attuale: " . $richiesta['stato'] . ")"
        ]);
        exit;
    }

    // 3. Aggiornamento stato in 'accettata'
    $updateQuery = $connessione->prepare("
        UPDATE RichiesteAmicizia 
        SET stato = 'accettata' 
        WHERE idRichiesta = ?
    ");

    $updateQuery->bind_param("i", $idRichiesta);
    $updateQuery->execute();

    if ($updateQuery->affected_rows > 0) {
        echo json_encode([
            "success" => true,
            "message" => "Richiesta di amicizia accettata con successo!",
            "amicizia" => [
                'idRichiesta' => $idRichiesta,
                'userMittente' => $richiesta['UserMittente'],
                'userDestinatario' => $currentUser,
                'stato' => 'accettata'
            ]
        ]);
    } else {
        throw new Exception("Errore durante l'aggiornamento della richiesta.");
    }

    $updateQuery->close();
    $connessione->close();

} catch(Exception $e) {
    http_response_code(500);
    echo json_encode([
        "success" => false,
        "error" => "Errore del server: " . $e->getMessage()
    ]);
}
?>