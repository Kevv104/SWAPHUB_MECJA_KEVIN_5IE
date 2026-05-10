<?php
/**
 * API: Accetta richiesta di scambio e registra i prodotti che vuoi ricevere
 * Metodo: POST
 * Body JSON: { idScambio, productsToReceive[] }
 * Effetto: Aggiorna stato a 'accettato' e popola ScambioProdotti lato destinatario
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
$productsToReceive = $input['productsToReceive'] ?? [];

if (!$idScambio || !is_numeric($idScambio)) {
    http_response_code(400);
    echo json_encode(['success' => false, 'error' => 'idScambio mancante o non valido']);
    exit;
}

if (!is_array($productsToReceive) || count($productsToReceive) < 1 || count($productsToReceive) > 2) {
    http_response_code(400);
    echo json_encode(['success' => false, 'error' => 'Deve esserci minimum 1 e massimo 2 prodotti']);
    exit;
}

try {
    // Verifica che lo scambio esista e sia in stato 'proposto'
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

    if ($rScambio['idUtenteDest'] !== $currentUser) {
        http_response_code(403);
        echo json_encode(['success' => false, 'error' => 'Non sei il destinatario di questo scambio']);
        exit;
    }

    if ($rScambio['stato'] !== 'proposto') {
        http_response_code(400);
        echo json_encode(['success' => false, 'error' => 'Questo scambio non è più in stato "proposto"']);
        exit;
    }

    // Verifica che tutti i prodotti appartengono all'utente e sono disponibili
    foreach ($productsToReceive as $idProd) {
        $qProd = $connessione->prepare("
            SELECT idProdotto, User, Disponibilità 
            FROM Prodotto 
            WHERE idProdotto = ?
        ");
        $qProd->bind_param('i', $idProd);
        $qProd->execute();
        $rProd = $qProd->get_result()->fetch_assoc();
        $qProd->close();

        if (!$rProd) {
            http_response_code(404);
            echo json_encode(['success' => false, 'error' => "Prodotto {$idProd} non trovato"]);
            exit;
        }

        if ($rProd['User'] !== $currentUser) {
            http_response_code(403);
            echo json_encode(['success' => false, 'error' => "Prodotto {$idProd} non ti appartiene"]);
            exit;
        }

        if ($rProd['Disponibilità'] !== 'disponibile') {
            http_response_code(400);
            echo json_encode(['success' => false, 'error' => "Prodotto {$idProd} non è disponibile"]);
            exit;
        }
    }

    // Inizia transazione
    $connessione->begin_transaction();

    // Aggiorna lo stato dello scambio a 'accettato'
    $qUpdate = $connessione->prepare("
        UPDATE Scambio 
        SET stato = 'accettato' 
        WHERE idScambio = ?
    ");
    $qUpdate->bind_param('i', $idScambio);
    $qUpdate->execute();
    $qUpdate->close();

    // Aggiungi i prodotti ricevuti in ScambioProdotti (lato destinatario)
    $qAddProd = $connessione->prepare("
        INSERT INTO ScambioProdotti (idScambio, idProdotto, lato)
        VALUES (?, ?, 'destinatario')
    ");

    foreach ($productsToReceive as $idProd) {
        $qAddProd->bind_param('ii', $idScambio, $idProd);
        $qAddProd->execute();
    }
    $qAddProd->close();

    // Commit
    $connessione->commit();

    echo json_encode([
        'success' => true,
        'messaggio' => 'Scambio accettato con successo!'
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

?>
