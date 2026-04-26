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
        echo json_encode(['success' => false, 'message' => 'Non autorizzato']);
        exit;
    }

    try {
        $decoded = JWT::decode($_SESSION['jwt'], new Key(JWT_SECRET, JWT_ALGO));
        $username = $decoded->sub;
    } catch (Exception $e) {
        http_response_code(401);
        echo json_encode(['success' => false, 'message' => 'Token non valido']);
        exit;
    }
}

// DELETE: Elimina un prodotto
if ($_SERVER['REQUEST_METHOD'] === 'DELETE') {
    parse_str(file_get_contents("php://input"), $_DELETE);
    
    $idProdotto = $_DELETE['idProdotto'] ?? '';
    
    if (empty($idProdotto)) {
        http_response_code(400);
        echo json_encode(['success' => false, 'message' => 'ID prodotto non specificato']);
        exit;
    }
    
    // Verifica che il prodotto appartenga all'utente
    $verificaQuery = "SELECT User, img FROM Prodotto WHERE idProdotto = ?";
    $verificaStmt = $connessione->prepare($verificaQuery);
    $verificaStmt->bind_param("i", $idProdotto);
    $verificaStmt->execute();
    $verificaResult = $verificaStmt->get_result();
    
    if ($verificaResult->num_rows === 0) {
        http_response_code(404);
        echo json_encode(['success' => false, 'message' => 'Prodotto non trovato']);
        exit;
    }
    
    $row = $verificaResult->fetch_assoc();
    if ($row['User'] !== $username) {
        http_response_code(403);
        echo json_encode(['success' => false, 'message' => 'Non autorizzato']);
        exit;
    }
    
    // Elimina immagine locale, evitando placeholder e path esterni
    if (!empty($row['img']) && strpos($row['img'], 'IMG/') === 0) {
        $imgPathFisico = __DIR__ . '/../../' . $row['img'];
        if (file_exists($imgPathFisico) && basename($imgPathFisico) !== 'noimage.jpg') {
            @unlink($imgPathFisico);
        }
    }
    
    $verificaStmt->close();
    
    // Eliminazione
    $query = "DELETE FROM Prodotto WHERE idProdotto = ? AND User = ?";
    
    $stmt = $connessione->prepare($query);
    $stmt->bind_param("is", $idProdotto, $username);
    
    if ($stmt->execute()) {
        echo json_encode([
            'success' => true,
            'message' => 'Prodotto eliminato con successo'
        ]);
    } else {
        http_response_code(500);
        echo json_encode(['success' => false, 'message' => 'Errore eliminazione prodotto']);
    }
    $stmt->close();
    exit;
}

http_response_code(405);
echo json_encode(['success' => false, 'message' => 'Metodo non consentito']);
?>
