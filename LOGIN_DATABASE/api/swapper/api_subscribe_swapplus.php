<?php
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

// Verifica metodo POST
if($_SERVER['REQUEST_METHOD'] !== 'POST') {
    http_response_code(405);
    echo json_encode(["success" => false, "error" => "Metodo non consentito. Usa POST"]);
    exit;
}

try {
    // Lettura input JSON dal Body di Postman
    $input = json_decode(file_get_contents('php://input'), true);
    $durataGiorni = $input['durataGiorni'] ?? null;

    // Validazione input
    $durateConsentite = [30, 90, 365];
    if(!in_array($durataGiorni, $durateConsentite)) {
        echo json_encode([
            "success" => false,
            "error" => "Durata non valida. Scegli: 30, 90 o 365 giorni"
        ]);
        exit;
    }

    // Controllo se è già presente abbonamento attivo tramite la VIEW
    $checkQuery = $connessione->prepare("
        SELECT * FROM vista_swapplus_utente 
        WHERE username = ? AND statoAbbonamento IN ('Attivo', 'In Scadenza')
    ");

    $checkQuery->bind_param("s", $currentUser);
    $checkQuery->execute();
    $result = $checkQuery->get_result();

    if($result->num_rows > 0) {
        $abbonamentoEsistente = $result->fetch_assoc();
        echo json_encode([
            "success" => false,
            "error" => "Hai già un abbonamento attivo fino al " . $abbonamentoEsistente['dataFine'],
            "abbonamentoEsistente" => [
                'dataFine' => $abbonamentoEsistente['dataFine'],
                'giorniRimanenti' => $abbonamentoEsistente['giorniRimanenti']
            ]
        ]);
        $checkQuery->close();
        $connessione->close();
        exit;
    }
    $checkQuery->close();

    // Calcolo date
    $dataInizio = date('Y-m-d'); 
    $dataFine = date('Y-m-d', strtotime("+{$durataGiorni} days")); 

    // Creazione nuovo abbonamento
    $insertQuery = $connessione->prepare("
        INSERT INTO SwapPlus (user, dataInizio, dataFine)
        VALUES (?, ?, ?)
    ");

    $insertQuery->bind_param("sss", $currentUser, $dataInizio, $dataFine);
    $insertQuery->execute();

    $idAbbonamento = $connessione->insert_id;

    echo json_encode([
        "success" => true,
        "message" => "Abbonamento Swap+ attivato con successo!",
        "abbonamento" => [
            'idAbbonamento' => $idAbbonamento,
            'user' => $currentUser,
            'dataInizio' => $dataInizio,
            'dataFine' => $dataFine,
            'durataGiorni' => $durataGiorni,
            'statoAbbonamento' => 'Attivo'
        ]
    ]);

    $insertQuery->close();
    $connessione->close();

} catch(Exception $e) {
    http_response_code(500);
    echo json_encode([
        "success" => false,
        "error" => "Errore del server: " . $e->getMessage()
    ]);
}
?>