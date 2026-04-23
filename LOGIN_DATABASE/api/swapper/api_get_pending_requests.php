<?php
header('Content-Type: application/json');
require_once __DIR__ . '/../../vendor/autoload.php';
require_once '../../jwt.php';
require_once '../../connectdb.php';

use Firebase\JWT\JWT;
use Firebase\JWT\Key;

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

try {
    $query = $connessione->prepare("
        SELECT
            ra.idRichiesta,
            ra.UserMittente,
            ra.UserDestinatario,
            um.Nome AS nomeMittente,
            um.Cognome AS cognomeMittente,
            ra.dataInvio,
            ra.stato,
            ra.commento,
            um.fotoprofilo AS fotoMittente,
            um.localita AS localitaMittente
        FROM RichiesteAmicizia ra
        JOIN utenti um ON um.username = ra.UserMittente
        JOIN utenti ud ON ud.username = ra.UserDestinatario
        WHERE ra.UserDestinatario = ?
          AND ra.stato = 'inviata'
          AND um.tenant_id = ?
          AND ud.tenant_id = ?
        ORDER BY ra.dataInvio DESC
    ");

    $query->bind_param("sii", $currentUser, $currentTenantId, $currentTenantId);
    $query->execute();
    $result = $query->get_result();

    $richieste = [];
    while($row = $result->fetch_assoc()) {
        $richieste[] = [
            'idRichiesta'   => $row['idRichiesta'],
            'userMittente'  => $row['UserMittente'],
            'Nome'          => $row['nomeMittente'],
            'Cognome'       => $row['cognomeMittente'],
            'fotoprofilo'   => $row['fotoMittente'] ?? 'uploads/profile/default.png',
            'localita'      => $row['localitaMittente'] ?? '',
            'commento'      => $row['commento'] ?? '',
            'dataInvio'     => $row['dataInvio'],
            'stato'         => $row['stato']
        ];
    }

    echo json_encode([
        "success" => true,
        "currentUser_debug" => $currentUser,
        "tenant_debug" => $currentTenantId,
        "richieste" => $richieste,
        "totalRichieste" => count($richieste)
    ]);

    $query->close();
    $connessione->close();

} catch(Exception $e) {
    http_response_code(500);
    echo json_encode(["success" => false, "error" => "Errore: " . $e->getMessage()]);
}
?>