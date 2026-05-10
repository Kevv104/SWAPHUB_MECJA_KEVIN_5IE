<?php
/**
 * API: Conferma richiesta di scambio dopo controproposta del destinatario
 * Metodo: POST
 * Body JSON: { idScambio }
 * Effetto: Aggiorna stato a 'completato'
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
    $qScambio = $connessione->prepare("\n        SELECT idScambio, idUtenteMit, idUtenteDest, stato\n        FROM Scambio\n        WHERE idScambio = ?\n    ");
    $qScambio->bind_param('i', $idScambio);
    $qScambio->execute();
    $rScambio = $qScambio->get_result()->fetch_assoc();
    $qScambio->close();

    if (!$rScambio) {
        http_response_code(404);
        echo json_encode(['success' => false, 'error' => 'Scambio non trovato']);
        exit;
    }

    if ($rScambio['idUtenteMit'] !== $currentUser) {
        http_response_code(403);
        echo json_encode(['success' => false, 'error' => 'Non sei il mittente di questo scambio']);
        exit;
    }

    if ($rScambio['stato'] !== 'accettato') {
        http_response_code(400);
        echo json_encode(['success' => false, 'error' => 'Puoi confermare solo una controproposta già accettata dal destinatario']);
        exit;
    }

    $connessione->begin_transaction();

    $qUpdate = $connessione->prepare("\n        UPDATE Scambio\n        SET stato = 'completato', dataFine = NOW()\n        WHERE idScambio = ?\n    ");
    $qUpdate->bind_param('i', $idScambio);
    $qUpdate->execute();
    $qUpdate->close();

    $connessione->commit();

    echo json_encode([
        'success' => true,
        'messaggio' => 'Scambio confermato con successo!'
    ]);
} catch (Exception $e) {
    if (isset($connessione)) {
        $connessione->rollback();
    }

    http_response_code(500);
    echo json_encode([
        'success' => false,
        'error' => 'Errore server: ' . $e->getMessage()
    ]);
}
