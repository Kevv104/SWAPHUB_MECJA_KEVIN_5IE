<?php
# API per ottenere le richieste di amicizia in sospeso per l'utente corrente
header('Content-Type: application/json');
require_once __DIR__ . '/../../vendor/autoload.php';
require_once '../../jwt.php';
require_once '../../connectdb.php';

use Firebase\JWT\JWT;
use Firebase\JWT\Key;

/**
 * --- CONFIGURAZIONE AMBIENTE (BYPASS POSTMAN/LOCALHOST) ---
 */
if (session_status() === PHP_SESSION_NONE) {
    session_start(['cookie_path' => '/login/']);
}

// Bypass per development/Postman: se non c'è JWT e siamo in localhost, usa test user
$isLocalhost = in_array($_SERVER['REMOTE_ADDR'], ['127.0.0.1', '::1']) || 
               (isset($_SERVER['HTTP_HOST']) && strpos($_SERVER['HTTP_HOST'], 'localhost') !== false);

$currentUser = null;

if (isset($_SESSION['jwt'])) {
    try {
        $decoded = JWT::decode($_SESSION['jwt'], new Key(JWT_SECRET, JWT_ALGO));
        $currentUser = $decoded->sub;
    } catch (Exception $e) {
        http_response_code(401);
        echo json_encode(["success" => false, "error" => "Token non valido"]);
        exit;
    }
} elseif ($isLocalhost) {
    // Bypass solo per localhost/development
    $currentUser = 'gianno';
} else {
    http_response_code(401);
    echo json_encode(["success" => false, "error" => "Non autorizzato"]);
    exit;
}

try {
    // Usiamo la vista_richieste_amicizia
    // Assicurati che la vista includa i campi: idRichiesta, UserMittente, UserDestinatario, 
    // nomeMittente, cognomeMittente, dataInvio, stato, commento, fotoMittente, localitaMittente
    $query = $connessione->prepare("
        SELECT * FROM vista_richieste_amicizia 
        WHERE UserDestinatario = ? 
        AND stato = 'inviata'
        ORDER BY dataInvio DESC
    ");

    $query->bind_param("s", $currentUser);
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