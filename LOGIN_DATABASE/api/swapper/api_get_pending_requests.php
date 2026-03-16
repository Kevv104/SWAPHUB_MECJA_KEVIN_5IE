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
   $currentUser = $decoded->sub;

   //query per ottenere richieste in attesa
      $query = $connessione->prepare("
    SELECT 
      r.idRichiesta,
      r.UserMittente,
      r.UserDestinatario,
      r.stato,
      r.commento,
      r.dataInvio,
      u.Nome,
      u.Cognome,
      u.Email,
      u.fotoprofilo,
      u.localita
    FROM RichiesteAmicizia r
    JOIN utenti u ON r.UserMittente = u.username
    WHERE r.UserDestinatario = ? 
    AND r.stato = 'inviata'
    ORDER BY r.dataInvio DESC
  ");

  $query->bind_param("s", $currentUser);
   $query->execute();
   $result = $query->get_result();

   $richieste = [];
   while($row = $result->fetch_assoc()) 
   {
      $richieste[] = [
        'idRichiesta' => $row['idRichiesta'],
        'userMittente' => $row['UserMittente'],
        'Nome' => $row['Nome'],
        'Cognome' => $row['Cognome'],
        'Email' => $row['Email'],
        'fotoprofilo' => $row['fotoprofilo'],
        'localita' => $row['localita'],
        'commento' => $row['commento'],
        'dataInvio' => $row['dataInvio'],
        'stato' => $row['stato']
      ];
    }
     echo json_encode([ //restituzione risultato
      "success" => true,
      "richieste" => $richieste,
      "totalRichieste" => count($richieste)
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