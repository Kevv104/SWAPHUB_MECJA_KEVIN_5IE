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
    $idRichiesta = $input['idRichiesta'] ?? null;

     if(empty($idRichiesta)) 
    {
       echo json_encode([
         "success" => false,
         "error" => "ID richiesta obbligatorio"
       ]);
       exit;
    } //validazione input

    //verifica esistenza richiesta e destinazione al utente corretto
     $checkQuery = $connessione->prepare("
      SELECT idRichiesta, UserMittente, UserDestinatario, stato 
      FROM RichiesteAmicizia 
      WHERE idRichiesta = ? AND UserDestinatario = ?
    ");

    $checkQuery->bind_param("is", $idRichiesta, $currentUser);
    $checkQuery->execute();
    $result = $checkQuery->get_result();

    
    if($result->num_rows === 0)
    {
       echo json_encode([
         "success" => false,
         "error" => "Richiesta non trovata o non sei il destinatario"
       ]);
       $checkQuery->close();
       exit;
    }//non ci stanno richieste
    $richiesta = $result->fetch_assoc();
    $checkQuery->close();

    //verifica se la richiesta  non è in stato 'inviata'
    if($richiesta['stato'] !== 'inviata') 
    {
       echo json_encode([
         "success" => false,
         "error" => "La richiesta non è in attesa (stato attuale: " . $richiesta['stato'] . ")"
       ]);
       exit;
    }

    //se è inviata allora ne aggiorniamo lo stato in accettata
    $updateQuery = $connessione->prepare("
      UPDATE RichiesteAmicizia 
      SET stato = 'accettata' 
      WHERE idRichiesta = ?
    ");

    $updateQuery->bind_param("i", $idRichiesta);
    $updateQuery->execute();

    echo json_encode([ //restituzione risultato
      "success" => true,
      "message" => "Richiesta di amicizia accettata!",
      "amicizia" => [
        'idRichiesta' => $idRichiesta,
        'userMittente' => $richiesta['UserMittente'],
        'userDestinatario' => $currentUser,
        'stato' => 'accettata'
      ]
    ]);

    $updateQuery->close();
    $connessione->close();


}catch(Exception $e)
{
   http_response_code(500);
    echo json_encode([
        "success" => false,
        "error" => "Errore del server: " . $e->getMessage()
    ]);
}
?>