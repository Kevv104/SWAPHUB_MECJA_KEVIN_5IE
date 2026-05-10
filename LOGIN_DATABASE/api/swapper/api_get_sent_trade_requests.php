<?php
/**
 * API: Recupera richieste di scambio inviate dall'utente
 * Metodo: GET
 * Ritorna: array richieste con dettagli destinatario, prodotti offerti e prodotti ricevuti
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

try {
    $connessione->query("\n        CREATE TABLE IF NOT EXISTS ScambioProdotti (\n            idScambioProdotto INT(11) PRIMARY KEY AUTO_INCREMENT,\n            idScambio INT(11) NOT NULL,\n            idProdotto INT(11) NOT NULL,\n            lato ENUM('mittente', 'destinatario') NOT NULL,\n            UNIQUE KEY unique_scambio_prodotto (idScambio, idProdotto, lato),\n            FOREIGN KEY (idScambio) REFERENCES Scambio(idScambio) ON DELETE CASCADE,\n            FOREIGN KEY (idProdotto) REFERENCES Prodotto(idProdotto) ON DELETE CASCADE\n        ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci\n    ");

    $query = $connessione->prepare("\n        SELECT \n            s.idScambio,\n            s.idUtenteMit,\n            s.idUtenteDest,\n            s.dataInizio,\n            s.dataFine,\n            s.stato,\n            u_dest.Nome AS nomeDestinatario,\n            u_dest.Cognome AS cognomeDestinatario,\n            u_dest.fotoprofilo AS fotoprofiloDestinatario,\n            u_dest.username AS userDestinatario\n        FROM Scambio s\n        INNER JOIN utenti u_dest ON s.idUtenteDest = u_dest.username\n        WHERE s.idUtenteMit = ?\n        ORDER BY s.dataInizio DESC\n    ");

    $query->bind_param('s', $currentUser);
    $query->execute();
    $result = $query->get_result();

    $richieste = [];
    while ($row = $result->fetch_assoc()) {
        $qProdMit = $connessione->prepare("\n            SELECT sp.idScambioProdotto, p.idProdotto, p.Titolo, p.NomeCategoria, p.Condizioni\n            FROM ScambioProdotti sp\n            INNER JOIN Prodotto p ON sp.idProdotto = p.idProdotto\n            WHERE sp.idScambio = ? AND sp.lato = 'mittente'\n        ");
        $qProdMit->bind_param('i', $row['idScambio']);
        $qProdMit->execute();
        $rProdMit = $qProdMit->get_result();
        $prodottiOfferty = $rProdMit->fetch_all(MYSQLI_ASSOC);
        $qProdMit->close();

        $qProdDest = $connessione->prepare("\n            SELECT sp.idScambioProdotto, p.idProdotto, p.Titolo, p.NomeCategoria, p.Condizioni\n            FROM ScambioProdotti sp\n            INNER JOIN Prodotto p ON sp.idProdotto = p.idProdotto\n            WHERE sp.idScambio = ? AND sp.lato = 'destinatario'\n        ");
        $qProdDest->bind_param('i', $row['idScambio']);
        $qProdDest->execute();
        $rProdDest = $qProdDest->get_result();
        $prodottiRichiesti = $rProdDest->fetch_all(MYSQLI_ASSOC);
        $qProdDest->close();

        $richieste[] = [
            'idScambio' => (int)$row['idScambio'],
            'userDestinatario' => $row['userDestinatario'],
            'nomeDestinatario' => $row['nomeDestinatario'],
            'cognomeDestinatario' => $row['cognomeDestinatario'],
            'fotoprofiloDestinatario' => $row['fotoprofiloDestinatario'],
            'dataInizio' => $row['dataInizio'],
            'dataFine' => $row['dataFine'],
            'stato' => $row['stato'],
            'prodottiOfferty' => $prodottiOfferty,
            'prodottiRichiesti' => $prodottiRichiesti
        ];
    }

    echo json_encode([
        'success' => true,
        'richieste' => $richieste,
        'totalRichieste' => count($richieste)
    ]);
} catch (Exception $e) {
    http_response_code(500);
    echo json_encode([
        'success' => false,
        'error' => 'Errore server: ' . $e->getMessage()
    ]);
}
