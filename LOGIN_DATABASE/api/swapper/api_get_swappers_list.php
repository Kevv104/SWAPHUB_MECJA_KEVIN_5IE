<?php
/**
 * API: Recupera lista di Swapper (per segnalazioni)
 * 
 * Metodo: GET
 * Permesso: send_report
 * Filtra: Solo Swapper, esclude utente corrente
 * 
 * Response: { success, utenti[], totalUtenti }
 * simile a get_users_list.php ma con filtro su ruolo e senza info sensibili
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

    $currentUser = $decoded->sub;

    // Step 2: Carica tutti gli Swapper (esclude utente corrente)
    $sql = "SELECT u.username, u.Nome, u.Cognome, u.fotoprofilo, u.localita 
            FROM utenti u
            INNER JOIN UtenteRuolo ur ON u.username = ur.username
            INNER JOIN Ruolo r ON ur.idRuolo = r.idRuolo
            WHERE r.nomeRuolo = 'Swapper' 
              AND u.username != ?
            ORDER BY u.Nome ASC, u.Cognome ASC";

    
    $stmt = $conn->prepare($sql);
    if(!$stmt) {
        throw new Exception('Errore DB: ' . $conn->error);
    }

    $stmt->bind_param('s', $currentUser);
    $stmt->execute();
    $result = $stmt->get_result();
    $utenti = $result->fetch_all(MYSQLI_ASSOC);
    $stmt->close();

    // Step 3: Restituisci lista
    http_response_code(200);
    echo json_encode([
        'success' => true,
        'utenti' => $utenti,
        'totalUtenti' => count($utenti)
    ]);

} catch(Exception $e) {
    http_response_code($e->getCode() ?: 400);
    echo json_encode([
        'success' => false,
        'error' => $e->getMessage()
    ]);
}

$conn->close();
