<?php
/**
 * API: Invia richiesta di scambio
 * Metodo: POST
 * Body JSON: { partnerUsername, productsOffered[] }
 * Validazioni: 1-2 prodotti, appartengono a utente, sono disponibili
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
$partnerUsername = trim($input['partnerUsername'] ?? '');
$productsOffered = $input['productsOffered'] ?? [];

// Validazioni
if (empty($partnerUsername)) {
    http_response_code(400);
    echo json_encode(['success' => false, 'error' => 'Partner username mancante']);
    exit;
}

if (!is_array($productsOffered) || count($productsOffered) < 1 || count($productsOffered) > 2) {
    http_response_code(400);
    echo json_encode(['success' => false, 'error' => 'Deve esserci minimum 1 e massimo 2 prodotti']);
    exit;
}

// Validare che partner esista e sia un Swapper
if ($partnerUsername === $currentUser) {
    http_response_code(400);
    echo json_encode(['success' => false, 'error' => 'Non puoi scambiare con te stesso']);
    exit;
}

try {
    // Creare tabella ScambioProdotti se non esiste
    $connessione->query("
        CREATE TABLE IF NOT EXISTS ScambioProdotti (
            idScambioProdotto INT(11) PRIMARY KEY AUTO_INCREMENT,
            idScambio INT(11) NOT NULL,
            idProdotto INT(11) NOT NULL,
            lato ENUM('mittente', 'destinatario') NOT NULL,
            UNIQUE KEY unique_scambio_prodotto (idScambio, idProdotto, lato),
            FOREIGN KEY (idScambio) REFERENCES Scambio(idScambio) ON DELETE CASCADE,
            FOREIGN KEY (idProdotto) REFERENCES Prodotto(idProdotto) ON DELETE CASCADE
        ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci
    ");

    // Validare che partner esiste
    $qPartner = $connessione->prepare("SELECT COUNT(*) as cnt FROM utenti WHERE username = ?");
    $qPartner->bind_param('s', $partnerUsername);
    $qPartner->execute();
    $rPartner = $qPartner->get_result()->fetch_assoc();
    $qPartner->close();

    if ($rPartner['cnt'] == 0) {
        http_response_code(400);
        echo json_encode(['success' => false, 'error' => 'Partner non trovato']);
        exit;
    }

    // Validare che tutti i prodotti appartengono all'utente e sono disponibili
    foreach ($productsOffered as $idProd) {
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

    // Creare richiesta di scambio nella tabella Scambio
    // Per ora useremo la prima combinazione 1:1 come placeholder, poi useremo ScambioProdotti per il resto
    $firstProduct = $productsOffered[0];
    
    $qInsert = $connessione->prepare("
        INSERT INTO Scambio (idUtenteMit, idUtenteDest, dataInizio, stato)
        VALUES (?, ?, NOW(), 'proposto')
    ");
    $qInsert->bind_param('ss', $currentUser, $partnerUsername);
    $qInsert->execute();

    $idScambio = $connessione->insert_id;

    if ($idScambio === 0) {
        throw new Exception('Errore nell\'inserimento della richiesta di scambio');
    }

    // Aggiungere tutti i prodotti in ScambioProdotti
    $qAddProd = $connessione->prepare("
        INSERT INTO ScambioProdotti (idScambio, idProdotto, lato)
        VALUES (?, ?, 'mittente')
    ");

    foreach ($productsOffered as $idProd) {
        $qAddProd->bind_param('ii', $idScambio, $idProd);
        $qAddProd->execute();
    }

    $qAddProd->close();

    // Opzionalmente: aggiornare Disponibilità dei prodotti a 'in_scambio' (dipende dalla logica)
    // Per ora lasciamo 'disponibile' fin quando non viene accettato
    /*
    $qUpdateProd = $connessione->prepare("
        UPDATE Prodotto SET Disponibilità = 'in_scambio' WHERE idProdotto = ?
    ");
    foreach ($productsOffered as $idProd) {
        $qUpdateProd->bind_param('i', $idProd);
        $qUpdateProd->execute();
    }
    $qUpdateProd->close();
    */

    // Commit transazione
    $connessione->commit();

    echo json_encode([
        'success' => true,
        'idScambio' => (int)$idScambio,
        'messaggio' => 'Richiesta di scambio inviata con successo!'
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
