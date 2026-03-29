<?php
header('Content-Type: application/json');
require_once __DIR__ . '/../../vendor/autoload.php';
require_once '../../jwt.php';
require_once '../../connectdb.php';

use Firebase\JWT\JWT;
use Firebase\JWT\Key;

$isDevelopment = true; 

if ($isDevelopment) {
    $currentUser = 'gianno'; 
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
    } catch (Exception $e) {
        http_response_code(401);
        echo json_encode(["success" => false, "error" => "Token non valido"]);
        exit;
    }
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