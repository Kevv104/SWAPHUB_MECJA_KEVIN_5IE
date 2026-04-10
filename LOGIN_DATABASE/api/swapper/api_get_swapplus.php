<?php
/**
 * API: api_get_swapplus.php
 * CASO D'USO: view_own_swapplus (Permesso #9)
 * DESCRIZIONE: Recupera abbonamento Swap+ con bypass per test Postman
 */

header('Content-Type: application/json');

// Inclusione file necessari
require_once __DIR__ . '/../../vendor/autoload.php';
require_once '../../jwt.php';
require_once '../../connectdb.php'; // Qui deve essere definita la variabile $connessione

use Firebase\JWT\JWT;
use Firebase\JWT\Key;

/**
 * --- SOLUZIONE B: RILEVAMENTO POSTMAN ---
 * Se la richiesta arriva da Postman, forziamo l'utente 'gianno' per saltare il muro dei cookie.
 * Se arriva dal browser, seguiamo la  logica originale JWT.
 */
$userAgent = $_SERVER['HTTP_USER_AGENT'] ?? '';
$isPostman = (strpos($userAgent, 'Postman') !== false);

if ($isPostman) {
    // Simuliamo l'utente loggato per il test
    $currentUser = 'gianno'; 
} else {
    // --- LOGICA ORIGINALE JWT / SESSIONE ---
    session_start([
        'cookie_path' => '/login/'
    ]);

    if (!isset($_SESSION['jwt'])) {
        http_response_code(401);
        echo json_encode(["success" => false, "error" => "Non autorizzato - Sessione JWT mancante"]); 
        exit;
    }

    try {
        $decoded = JWT::decode($_SESSION['jwt'], new Key(JWT_SECRET, JWT_ALGO));
        $currentUser = $decoded->sub;
    } catch (Exception $e) {
        http_response_code(401);
        echo json_encode(["success" => false, "error" => "JWT non valido: " . $e->getMessage()]);
        exit;
    }
}

/**
 * --- LOGICA DI RECUPERO DATI DAL DATABASE ---
 */
try {
    // Query sulla view 'vista_swapplus_utente' che abbiamo creato
    $query = $connessione->prepare(" 
        SELECT * FROM vista_swapplus_utente 
        WHERE username = ?
    ");

    if (!$query) {
        throw new Exception("Errore nella preparazione della query: " . $connessione->error);
    }

    $query->bind_param("s", $currentUser);
    $query->execute();
    $result = $query->get_result();

    // Controllo esistenza abbonamento
    if ($row = $result->fetch_assoc()) {
        // L'utente ha un abbonamento attivo o scaduto nella vista
        echo json_encode([
            "success" => true,
            "hasAbbonamento" => true,
            "currentUser_debug" => $currentUser, // Ti aiuta a capire chi stai testando
            "abbonamento" => [
                'idAbbonamento' => $row['idAbbonamento'],
                'dataInizio' => $row['dataInizio'],
                'dataFine' => $row['dataFine'],
                'giorniRimanenti' => max(0, $row['giorniRimanenti']),
                'giorniTrascorsi' => $row['giorniTrascorsi'],
                'durataGiorni' => $row['durataGiorni'],
                'statoAbbonamento' => $row['statoAbbonamento']
            ]
        ]);
    } else {
        // L'utente non ha proprio un record in SwapPlus
        echo json_encode([
            "success" => true,
            "hasAbbonamento" => false,
            "currentUser_debug" => $currentUser,
            "message" => "Nessun abbonamento Swap+ trovato per l'utente " . $currentUser
        ]);
    }

    $query->close();
    $connessione->close();

} catch (Exception $e) {
    http_response_code(500);
    echo json_encode([
        "success" => false,
        "error" => "Errore del server: " . $e->getMessage()
    ]);
}
?>