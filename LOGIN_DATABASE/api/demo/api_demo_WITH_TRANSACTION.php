<?php

header('Content-Type: application/json');
require_once __DIR__ . '/../../connectdb_pdo.php';

session_start(['cookie_path' => '/login/']);

if(!isset($_SESSION['jwt'])) {
  http_response_code(401);
  echo json_encode(["error" => "Non autorizzato"]); 
  exit;
}

if($_SERVER['REQUEST_METHOD'] !== 'POST') {
  http_response_code(405);
  echo json_encode(["error" => "Metodo non consentito"]);
  exit;
}

require_once __DIR__ . '/../../vendor/autoload.php';
require_once '../../jwt.php';
use Firebase\JWT\JWT;
use Firebase\JWT\Key;

try {
    
    $decoded = JWT::decode($_SESSION['jwt'], new Key(JWT_SECRET, JWT_ALGO));
    $currentUser = $decoded->sub;
    
    $input = json_decode(file_get_contents('php://input'), true);
    $nomeChat = $input['nome'] ?? 'Test Chat WITH TRANSACTION';
    $partecipanti = $input['partecipanti'] ?? [];
    
    //inizio transazione
    $pdo->beginTransaction();
    
    echo json_encode([
        "step" => 0,
        "message" => "🔒 TRANSAZIONE INIZIATA"
    ]) . "\n";
    flush();
    
    //crea chat
    $stmt = $pdo->prepare("
        INSERT INTO Chat (nome, stato, numPartecipanti, tipoChat, idScambio)
        VALUES (?, 'attiva', ?, 'gruppo', NULL)
    ");
    $numPart = count($partecipanti) + 1;
    $stmt->execute([$nomeChat, $numPart]);
    $idChat = $pdo->lastInsertId();
    
    echo json_encode([
        "step" => 1,
        "message" => "✓ Chat creata (ID: $idChat) - NON ancora definitivo"
    ]) . "\n";
    flush();
    
    //aggiungi utente corrente
    $stmt = $pdo->prepare("
        INSERT INTO PartecipaChat (idChat, User)
        VALUES (?, ?)
    ");
    $stmt->execute([$idChat, $currentUser]);
    
    echo json_encode([
        "step" => 2,
        "message" => "✓ Utente corrente aggiunto ($currentUser) - NON ancora definitivo"
    ]) . "\n";
    flush();
    
    //aggiungi altri partecipanti
    $count = 0;
    foreach($partecipanti as $username) {
        $count++;
        
        $stmt->execute([$idChat, $username]);
        
        echo json_encode([
            "step" => "3.$count",
            "message" => "✓ Partecipante #$count aggiunto ($username) - NON ancora definitivo"
        ]) . "\n";
        flush();
        
        //simula errore dopo il 2° partecipante
        if($count === 2) {
            throw new Exception("💥 ERRORE SIMULATO dopo aver salvato $count partecipanti!");
        }
    }
    
    //conferma transazione
    $pdo->commit();
    
    echo json_encode([
        "success" => true,
        "message" => "✅ COMMIT eseguito - Chat salvata definitivamente",
        "idChat" => $idChat,
        "database_state" => "CONSISTENTE"
    ]);
    
} catch(Exception $e) {
    //annulla transazione
    if($pdo->inTransaction()) {
        $pdo->rollBack();
    }
    
    http_response_code(500);
    echo json_encode([
        "success" => false,
        "error" => $e->getMessage(),
        "action_taken" => "🔄 ROLLBACK eseguito - TUTTE le operazioni annullate",
        "database_state" => "CONSISTENTE (nessuna modifica salvata)"
    ]);
}
?>