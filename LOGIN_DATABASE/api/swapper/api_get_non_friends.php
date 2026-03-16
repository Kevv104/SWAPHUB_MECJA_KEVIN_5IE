<?php

//API: api_get_non_friends.php
//CASO D'USO: send_friend_request (Permesso #5)
//DESCRIZIONE: Recupera lista utenti con cui NON si è amici

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
   $currentUser = $decoded->sub;

   //query per otenere utenti non amici tra di loro(escludendo se stesso, utenti con cui si è già inviata richiesta, utenti già amici)
   
  $query = $connessione->prepare("
    SELECT 
      u.username,
      u.Nome,
      u.Cognome,
      u.Email,
      u.fotoprofilo,
      u.localita
    FROM utenti u
    WHERE u.username != ?
    AND u.username NOT IN (
      -- Escludi utenti con cui hai già una richiesta in attesa (tu -> loro)
      SELECT UserDestinatario 
      FROM RichiesteAmicizia 
      WHERE UserMittente = ? AND stato = 'inviata'
      
      UNION
      
      -- Escludi utenti che ti hanno mandato richiesta in attesa (loro -> tu)
      SELECT UserMittente 
      FROM RichiesteAmicizia 
      WHERE UserDestinatario = ? AND stato = 'inviata'
      
      UNION
      
      -- Escludi utenti già amici (richiesta accettata in entrambe le direzioni)
      SELECT UserDestinatario 
      FROM RichiesteAmicizia 
      WHERE UserMittente = ? AND stato = 'accettata'
      
      UNION
      
      SELECT UserMittente 
      FROM RichiesteAmicizia 
      WHERE UserDestinatario = ? AND stato = 'accettata'
    )
    ORDER BY u.Nome, u.Cognome
  ");

   $query->bind_param("sssss", $currentUser, $currentUser, $currentUser, $currentUser, $currentUser);
   $query->execute();
   $result = $query->get_result();

   $utenti = [];
    while($row = $result->fetch_assoc()) 
    {
      $utenti[] = [
      'username' => $row['username'],
      'Nome' => $row['Nome'],
      'Cognome' => $row['Cognome'],
      'Email' => $row['Email'],
      'fotoprofilo' => $row['fotoprofilo'],
      'localita' => $row['localita']
    ];
    }

    echo json_encode([ //restituzione risultato
    "success" => true,
    "utenti" => $utenti,
    "totalUtenti" => count($utenti)
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