<?php
/**
 * API: Recupera richieste di scambio ricevute dall'utente
 * Metodo: GET
 * Ritorna: Array richieste con dettagli mittente, prodotti offerti, prodotti richiesti (se accettato)
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
    // Assicura che esistano le tabelle
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

    // Recupera richieste ricevute (dove current user è destinatario)
    $query = $connessione->prepare("
        SELECT 
            s.idScambio,
            s.idUtenteMit,
            s.idUtenteDest,
            s.dataInizio,
            s.dataFine,
            s.stato,
            u_mit.Nome AS nomeMittente,
            u_mit.Cognome AS cognomeMittente,
            u_mit.fotoprofilo AS fotoprofiloMittente,
            u_mit.username AS userMittente
        FROM Scambio s
        INNER JOIN utenti u_mit ON s.idUtenteMit = u_mit.username
        WHERE s.idUtenteDest = ?
        ORDER BY s.dataInizio DESC
    ");

    $query->bind_param('s', $currentUser);
    $query->execute();
    $result = $query->get_result();

    $richieste = [];
    while ($row = $result->fetch_assoc()) {
        // Recupera prodotti offerti (lato mittente)
        $qProdMit = $connessione->prepare("
            SELECT sp.idScambioProdotto, p.idProdotto, p.Titolo, p.NomeCategoria, p.Condizioni
            FROM ScambioProdotti sp
            INNER JOIN Prodotto p ON sp.idProdotto = p.idProdotto
            WHERE sp.idScambio = ? AND sp.lato = 'mittente'
        ");
        $qProdMit->bind_param('i', $row['idScambio']);
        $qProdMit->execute();
        $rProdMit = $qProdMit->get_result();
        $prodottiOfferty = $rProdMit->fetch_all(MYSQLI_ASSOC);
        $qProdMit->close();

        // Recupera prodotti richiesti (lato destinatario) se scambio è stato accettato
        $qProdDest = $connessione->prepare("
            SELECT sp.idScambioProdotto, p.idProdotto, p.Titolo, p.NomeCategoria, p.Condizioni
            FROM ScambioProdotti sp
            INNER JOIN Prodotto p ON sp.idProdotto = p.idProdotto
            WHERE sp.idScambio = ? AND sp.lato = 'destinatario'
        ");
        $qProdDest->bind_param('i', $row['idScambio']);
        $qProdDest->execute();
        $rProdDest = $qProdDest->get_result();
        $prodottiRichiesti = $rProdDest->fetch_all(MYSQLI_ASSOC);
        $qProdDest->close();

        $richieste[] = [
            'idScambio' => (int)$row['idScambio'],
            'userMittente' => $row['userMittente'],
            'nomeMittente' => $row['nomeMittente'],
            'cognomeMittente' => $row['cognomeMittente'],
            'fotoprofiloMittente' => $row['fotoprofiloMittente'],
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

?>
