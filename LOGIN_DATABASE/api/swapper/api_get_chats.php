<?php

header('Content-Type: application/json');
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

try
{
  $decoded = JWT::decode($_SESSION['jwt'], new Key(JWT_SECRET, JWT_ALGO));
  $currentUser = $decoded->sub; //decodifica jwt

  //query per prendere tutte chat del utente dalla view
  $query = $connessione->prepare("
    SELECT 
      idChat,
      nomeChat,
      tipoChat,
      stato,
      numPartecipanti,
      dataCreazione,
      descrizione,
      totMessaggi,
      ultimoMessaggio,
      dataUltimoMessaggio,
      autoreUltimoMessaggio
    FROM vista_chat_utente 
    WHERE username = ?
    ORDER BY dataUltimoMessaggio DESC
  ");

  $query->bind_param("s", $currentUser);
  $query->execute();
  $result = $query->get_result();

  $chats = [];
  while($row = $result->fetch_assoc()) //costruzione array di chat
  {
    $chats[] = [
      'idChat' => $row['idChat'],
      'nomeChat' => $row['nomeChat'],
      'tipoChat' => $row['tipoChat'],
      'stato' => $row['stato'],
      'numPartecipanti' => $row['numPartecipanti'],
      'dataCreazione' => $row['dataCreazione'],
      'descrizione' => $row['descrizione'],
      'totMessaggi' => $row['totMessaggi'] ?? 0, //se non ha messaggi default zero
      'ultimoMessaggio' => $row['ultimoMessaggio'] ?? 'Nessun messaggio', //se non ce ultimo messaggio, allora 'nessun messaggio'
      'dataUltimoMessaggio' => $row['dataUltimoMessaggio'],
      'autoreUltimoMessaggio' => $row['autoreUltimoMessaggio']
    ];
  }

   echo json_encode([ //restituzione risultato
    "success" => true,
    "chats" => $chats,
    "totalChats" => count($chats)
  ]);

  $query->close();
  $connessione->close();


}catch(Exception $e)
{
  http_response_code(500);
  echo json_encode([
    "success" => false,
    "error" => $e->getMessage()
  ]);
}
?>