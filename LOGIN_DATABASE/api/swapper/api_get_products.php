<?php

require_once '../../vendor/autoload.php';
require_once '../../connectdb.php';
require_once '../../jwt.php';
require_once '../../sicurezzaRotte.php';

use Firebase\JWT\JWT;
use Firebase\JWT\Key;

header('Content-Type: application/json');

// --- CONFIGURAZIONE AMBIENTE ---
if (session_status() === PHP_SESSION_NONE) {
    session_start(['cookie_path' => '/login/']);
}

// Bypass per development/Postman: se non c'è JWT e siamo in localhost, usa test user
$isLocalhost = in_array($_SERVER['REMOTE_ADDR'], ['127.0.0.1', '::1']) || 
               (isset($_SERVER['HTTP_HOST']) && strpos($_SERVER['HTTP_HOST'], 'localhost') !== false);

$username = null;

if (isset($_SESSION['jwt'])) {
    try {
        $decoded = JWT::decode($_SESSION['jwt'], new Key(JWT_SECRET, JWT_ALGO));
        $username = $decoded->sub;
    } catch (Exception $e) {
        http_response_code(401);
        echo json_encode(['success' => false, 'message' => 'Token non valido: ' . $e->getMessage()]);
        exit;
    }
} elseif ($isLocalhost) {
    // Bypass solo per localhost/development
    $username = 'gianno';
} else {
    http_response_code(401);
    echo json_encode(['success' => false, 'message' => 'Non autorizzato: JWT non trovato']);
    exit;
}

// GET: Recupera tutti i prodotti dell'utente
if ($_SERVER['REQUEST_METHOD'] === 'GET') {
    try {
        $query = "
            SELECT 
                p.idProdotto,
                p.Titolo,
                p.Descrizione,
                p.Disponibilità,
                p.Condizioni,
                p.dataPubblicazione,
                p.img,
                p.NomeCategoria,
                p.User AS username,
                u.Nome AS nomeUtente,
                u.Cognome AS cognomeUtente
            FROM Prodotto p
            LEFT JOIN utenti u ON p.User = u.username
            WHERE p.User = ?
            ORDER BY p.dataPubblicazione DESC
        ";
        
        $stmt = $connessione->prepare($query);
        if (!$stmt) {
            throw new Exception('Errore prepare: ' . $connessione->error);
        }
        
        $stmt->bind_param("s", $username);
        $stmt->execute();
        $result = $stmt->get_result();
        
        $prodotti = [];
        while ($row = $result->fetch_assoc()) {
            $prodotti[] = $row;
        }
        $stmt->close();
        
        echo json_encode([
            'success' => true,
            'data' => $prodotti,
            'count' => count($prodotti)
        ]);
    } catch (Exception $e) {
        http_response_code(500);
        echo json_encode([
            'success' => false, 
            'message' => 'Errore database: ' . $e->getMessage()
        ]);
    }
    exit;
}

http_response_code(405);
echo json_encode(['success' => false, 'message' => 'Metodo non consentito']);
?>
