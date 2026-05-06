<?php
/**
 * API: Recupera i motivi di segnalazione disponibili
 * 
 * Metodo: GET
 * Permesso: send_report (o view)
 * 
 * Response: { success, motivi[] }
 */

header('Content-Type: application/json; charset=utf-8');

session_start();

require_once __DIR__ . '/../../vendor/autoload.php';
require_once __DIR__ . '/../../jwt.php';
require_once __DIR__ . '/../../connectdb.php';

use Firebase\JWT\JWT;
use Firebase\JWT\Key;

$conn = $connessione;

try {
    // Step 1: Verifica JWT
    if(!isset($_SESSION['jwt'])) {
        throw new Exception('Non autenticato', 401);
    }

    $jwt = $_SESSION['jwt'];
    $decoded = JWT::decode($jwt, new Key(JWT_SECRET, JWT_ALGO));
    if(!$decoded) {
        throw new Exception('JWT non valido', 401);
    }

    $currentUser = $decoded->sub; // Username dell'utente corrente

    // Step 2: Carica motivi ban dalla tabella MotiviBan
    $sql = "SELECT idMotivo, nomeMotivo, descrizione 
            FROM MotiviBan 
            ORDER BY idMotivo ASC";
    
    $stmt = $conn->prepare($sql);
    if(!$stmt) { // Verifica errori di preparazione
        throw new Exception('Errore DB: ' . $conn->error);
    }

    $stmt->execute();
    $result = $stmt->get_result();
    $motivi = $result->fetch_all(MYSQLI_ASSOC); //tutti i motivi di ban come array associativo 
    $stmt->close();

    // Step 3: Restituisci motivi
    http_response_code(200);
    echo json_encode([
        'success' => true,
        'motivi' => $motivi,
        'totalMotivi' => count($motivi)
    ]);

} catch(Exception $e) {
    http_response_code($e->getCode() ?: 400);
    echo json_encode([
        'success' => false,
        'error' => $e->getMessage()
    ]);
}

$conn->close();
