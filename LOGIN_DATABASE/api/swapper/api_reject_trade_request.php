<?php
/**
 * API: Rifiuta richiesta di scambio
 * Metodo: POST
 * Body JSON: { idScambio }
 * Effetto: Aggiorna stato a 'annullato'
 */

header('Content-Type: application/json');
require_once __DIR__ . '/../../vendor/autoload.php';
require_once '../../jwt.php';
require_once '../../connectdb.php';

use Firebase\JWT\JWT;
use Firebase\JWT\Key;

if (session_status() === PHP_SESSION_NONE) {
    session_start(['cookie_path' => '/login/']);
}

if (!isset($_SESSION['jwt'])) {
    http_response_code(401);
    echo json_encode(['success' => false, 'error' => 'Non autorizzato']);
    exit;
}

try {
    $decoded = JWT::decode($_SESSION['jwt'], new Key(JWT_SECRET, JWT_ALGO));
    $currentUser = $decoded->sub;
} catch (Exception $e) {
    http_response_code(401);
    echo json_encode(['success' => false, 'error' => 'Token non valido']);
    exit;
}

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    http_response_code(405);
    echo json_encode(['success' => false, 'error' => 'Metodo non consentito']);
    exit;
}

$input = json_decode(file_get_contents('php://input'), true);
$idScambio = $input['idScambio'] ?? null;

if (!$idScambio || !is_numeric($idScambio)) {
    http_response_code(400);
    echo json_encode(['success' => false, 'error' => 'idScambio mancante o non valido']);
    exit;
}

try {
    // Verifica che lo scambio esista e appartenga all'utente
    $qScambio = $connessione->prepare("
        SELECT idScambio, idUtenteMit, idUtenteDest, stato 
        FROM Scambio 
        WHERE idScambio = ?
    ");
    $qScambio->bind_param('i', $idScambio);
    $qScambio->execute();
    $rScambio = $qScambio->get_result()->fetch_assoc();
    $qScambio->close();

    if (!$rScambio) {
        http_response_code(404);
        echo json_encode(['success' => false, 'error' => 'Scambio non trovato']);
        exit;
    }

    // Permetti al destinatario di rifiutare
    if ($rScambio['idUtenteDest'] !== $currentUser) {
        http_response_code(403);
        echo json_encode(['success' => false, 'error' => 'Non puoi rifiutare questo scambio']);
        exit;
    }

    if ($rScambio['stato'] === 'annullato') {
        http_response_code(400);
        echo json_encode(['success' => false, 'error' => 'Questo scambio è già stato rifiutato']);
        exit;
    }

    if ($rScambio['stato'] !== 'proposto') {
        http_response_code(400);
        echo json_encode(['success' => false, 'error' => 'Puoi rifiutare solo richieste in stato "proposto"']);
        exit;
    }

    // Aggiorna stato a 'annullato'
    $qUpdate = $connessione->prepare("
        UPDATE Scambio 
        SET stato = 'annullato' 
        WHERE idScambio = ?
    ");
    $qUpdate->bind_param('i', $idScambio);
    $qUpdate->execute();
    $qUpdate->close();

    echo json_encode([
        'success' => true,
        'messaggio' => 'Scambio rifiutato'
    ]);

} catch (Exception $e) {
    http_response_code(500);
    echo json_encode([
        'success' => false,
        'error' => 'Errore server: ' . $e->getMessage()
    ]);
}

?>
