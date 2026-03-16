<?php

// API: api_send_friend_request.php
//CASO D'USO: send_friend_request (Permesso #5)
//DESCRIZIONE: Invia una richiesta di amicizia a un utente

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

if($_SERVER['REQUEST_METHOD'] !== 'POST') {
  http_response_code(405);
  echo json_encode(["error" => "Metodo non consentito. Usa POST"]);
  exit;
}

try
{
    $decoded = JWT::decode($_SESSION['jwt'], new Key(JWT_SECRET, JWT_ALGO));
    $currentUser = $decoded->sub;

     $input = json_decode(file_get_contents('php://input'), true);
     $userRicevente = $input['userRicevente'] ?? null; 

     if(empty($userRicevente)) 
     {
       echo json_encode([
      "success" => false,
      "error" => "Username destinatario obbligatorio"
      ]);
       exit;
     }

     if($userRicevente === $currentUser) 
     {
         echo json_encode([
         "success" => false,
         "error" => "Non puoi inviare una richiesta di amicizia a te stesso"
         ]);
         exit;
     }

     $checkUser = $connessione->prepare("SELECT username FROM utenti WHERE username = ?");
     $checkUser->bind_param("s", $userRicevente);
     $checkUser->execute();
     $resultUser = $checkUser->get_result();

     if($resultUser->num_rows === 0) 
     {
         echo json_encode([
         "success" => false,
         "error" => "Utente destinatario non trovato"
         ]);
    $checkUser->close();
    exit;
     }
      $checkUser->close();
      
       $checkRichiesta = $connessione->prepare("
    SELECT idRichiesta, stato 
    FROM RichiesteAmicizia 
    WHERE (UserMittente = ? AND UserDestinatario = ?)
       OR (UserMittente = ? AND UserDestinatario = ?)
  ");

   $checkRichiesta->bind_param("ssss", $currentUser, $userRicevente, $userRicevente, $currentUser);
   $checkRichiesta->execute();
  $resultRichiesta = $checkRichiesta->get_result();

  if($resultRichiesta->num_rows > 0)
  {
   $richiestaEsistente = $resultRichiesta->fetch_assoc();

   if($richiestaEsistente['stato'] === 'inviata') 
   {
    echo json_encode([
        "success" => false,
        "error" => "Esiste già una richiesta di amicizia in attesa con questo utente"
      ]);
      $checkRichiesta->close();
      exit;
   }else if($richiestaEsistente['stato'] === 'accettata')
   {
     echo json_encode([
        "success" => false,
        "error" => "Siete già amici!"
      ]);
   } else 
   {
     echo json_encode([
        "success" => false,
        "error" => "Richiesta già presente con stato: " . $richiestaEsistente['stato']
      ]);
   }
     $checkRichiesta->close();
    $connessione->close();
    exit;
  }
  $checkRichiesta->close();

  //creazione richiesta amicizia
  $insertQuery = $connessione->prepare("
    INSERT INTO RichiesteAmicizia (UserMittente, UserDestinatario, stato, commento)
    VALUES (?, ?, 'inviata', '')
    ");

    $insertQuery->bind_param("ss", $currentUser, $userRicevente);
    $insertQuery->execute();

    $idRichiesta = $connessione->insert_id;
    $dataRichiesta = date('Y-m-d H:i:s'); //data di oggi

    echo json_encode([
    "success" => true,
    "message" => "Richiesta di amicizia inviata con successo!",
    "richiesta" => [
      'idRichiesta' => $idRichiesta,
      'userMittente' => $currentUser,
      'userDestinatario' => $userRicevente,
      'stato' => 'inviata',
      'dataRichiesta' => $dataRichiesta
    ]
  ]); //restituzione risultato

  $insertQuery->close();
  $connessione->close();


} catch (Exception $e) {
    echo json_encode([
        "success" => false,
        "error" => $e->getMessage()
    ]);
}

?>