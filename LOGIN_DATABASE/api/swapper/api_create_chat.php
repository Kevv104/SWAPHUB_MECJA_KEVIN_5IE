<?php

header('Content-type: application/json');
require_once __DIR__ . '/../../vendor/autoload.php';
require_once '../../jwt.php';
require_once '../../connectdb.php';
use Firebase\JWT\JWT;
use Firebase\JWT\Key;

session_start([
    'cookie_path' => '/login/'
]);

if(!isset($_SESSION['jwt'])) {
  http_response_code(401);
  echo json_encode(["error" => "Non autorizzato"]); 
  exit;
}

try {
  $decoded = JWT::decode($_SESSION['jwt'], new Key(JWT_SECRET, JWT_ALGO));
  $currentUser = $decoded->sub;

  $input = json_decode(file_get_contents('php://input'), true);

  $nomeChat = $input['nome'] ?? ''; 
  $tipoChat = $input['tipo'] ?? 'privata';
  $descrizione = $input['descrizione'] ?? '';
  $partecipanti = $input['partecipanti'] ?? [];

  // Validazione input
  if(empty($nomeChat)) {
    echo json_encode(["success" => false, "error" => "Nome chat obbligatorio"]);
    exit;
  }

  if(empty($partecipanti)) {
    echo json_encode(["success" => false, "error" => "Seleziona almeno un utente"]);
    exit;
  }

  $numPartecipanti = count($partecipanti) + 1;

  // Transazione
  $connessione->begin_transaction();

  // Inserimento chat
  $insertChat = $connessione->prepare("
    INSERT INTO Chat (nome, stato, numPartecipanti, descrizione, tipoChat, idScambio)
    VALUES (?, 'attiva', ?, ?, ?, NULL)
  ");

  $insertChat->bind_param("siss", $nomeChat, $numPartecipanti, $descrizione, $tipoChat);
  $insertChat->execute();

  $idChatCreata = $connessione->insert_id;

  // Inserimento partecipanti
  $insertPartecipante = $connessione->prepare("
    INSERT INTO PartecipaChat (idChat, User)
    VALUES (?, ?)
  ");

  // Aggiungi utente corrente
  $insertPartecipante->bind_param("is", $idChatCreata, $currentUser);
  $insertPartecipante->execute();

  // Aggiungi altri partecipanti
  foreach($partecipanti as $username) {
    $insertPartecipante->bind_param("is", $idChatCreata, $username);
    $insertPartecipante->execute();
  }

  // Conferma transazione
  $connessione->commit();

  echo json_encode([
    "success" => true,
    "idChat" => $idChatCreata,
    "messaggio" => "Chat creata con successo!"
  ]);

  $insertChat->close();
  $insertPartecipante->close();
  $connessione->close();

} catch(Exception $e) {
  $connessione->rollback();
  
  http_response_code(500);
  echo json_encode([
    "success" => false,
    "error" => "Errore nella creazione: " . $e->getMessage()
  ]);
}
?>