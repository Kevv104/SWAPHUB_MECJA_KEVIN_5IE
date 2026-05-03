<?php

require_once '../../vendor/autoload.php';
require_once '../../connectdb.php';
require_once '../../jwt.php';
require_once '../../sicurezzaRotte.php';

use Firebase\JWT\JWT;
use Firebase\JWT\Key;

header('Content-Type: application/json');

// --- CONFIGURAZIONE AMBIENTE (BYPASS POSTMAN) ---
$isDevelopment = true;

if ($isDevelopment) {
    $username = 'gianno';
} else {
    if (session_status() === PHP_SESSION_NONE) {
        session_start(['cookie_path' => '/login/']);
    }

    if (!isset($_SESSION['jwt'])) {
        http_response_code(401);
        echo json_encode(['success' => false, 'message' => 'Non autorizzato: JWT non trovato']);
        exit;
    }

    try {
        $decoded = JWT::decode($_SESSION['jwt'], new Key(JWT_SECRET, JWT_ALGO));
        $username = $decoded->sub;
    } catch (Exception $e) {
        http_response_code(401);
        echo json_encode(['success' => false, 'message' => 'Token non valido: ' . $e->getMessage()]);
        exit;
    }
}

// GET: Recupera tutti i prodotti dell'utente
if ($_SERVER['REQUEST_METHOD'] === 'GET') {
    try {
        $query = "
            SELECT 
                idProdotto,
                Titolo,
                Descrizione,
                Disponibilità,
                Condizioni,
                dataPubblicazione,
                img,
                NomeCategoria,
                username,
                nomeUtente,
                cognomeUtente
            FROM vista_prodotti
            WHERE username = ?
            ORDER BY dataPubblicazione DESC
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
