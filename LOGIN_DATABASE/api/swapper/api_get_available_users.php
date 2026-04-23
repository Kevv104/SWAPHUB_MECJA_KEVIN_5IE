<?php
header('Content-Type: application/json');
require_once __DIR__ . '/../../vendor/autoload.php';
require_once '../../jwt.php';
require_once '../../connectdb.php';

use Firebase\JWT\JWT;
use Firebase\JWT\Key;


$isDevelopment = (
    (isset($_GET['dev_bypass']) && $_GET['dev_bypass'] === '1') ||
    (isset($_SERVER['HTTP_X_DEV_BYPASS']) && $_SERVER['HTTP_X_DEV_BYPASS'] === '1')
);

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
    // Query per prendere tutti gli utenti tranne quello loggato
    $query = $connessione->prepare("
        SELECT 
            username,
            Nome,
            Cognome,
            Email,
            fotoprofilo
        FROM utenti
                WHERE username != ?
                    AND tenant_id = ?
        ORDER BY Nome, Cognome
    ");

        $query->bind_param("si", $currentUser, $currentTenantId);
    $query->execute();
    $result = $query->get_result();

    $utenti = [];
    while($row = $result->fetch_assoc()) {
        $utenti[] = [
            'username'    => $row['username'],
            'Nome'        => $row['Nome'],
            'Cognome'     => $row['Cognome'],
            'Email'       => $row['Email'],
            'fotoprofilo' => $row['fotoprofilo']
        ];
    }
    
    echo json_encode([
        "success"           => true,
        "currentUser_debug" => $currentUser,
        "tenant_debug"      => $currentTenantId,
        "utenti"            => $utenti,
        "totalUtenti"       => count($utenti)
    ]);

    $query->close();
    $connessione->close();

} catch(Exception $e) {
    // In caso di errore nel database o nella query
    http_response_code(500);
    echo json_encode([
        "success" => false,
        "error"   => "Errore del server: " . $e->getMessage()
    ]);
}
?>