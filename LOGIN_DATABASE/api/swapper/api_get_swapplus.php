<?php
/**
 * API: api_get_swapplus.php
 * CASO D'USO: view_own_swapplus (Permesso #9)
 * DESCRIZIONE: Recupera abbonamento Swap+ dell'utente loggato
 * 
 * RICHIEDE: 
 * - Autenticazione JWT
 * - VIEW SQL: vista_swapplus_utente
 * 
 * RESTITUISCE:
 * - success: true/false
 * - hasAbbonamento: true/false
 * - abbonamento: {oggetto con dati} (se presente)
 */

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

   //query sulla view creata su db
   $query = $connessione->prepare(" 
    SELECT * FROM vista_swapplus_utente 
    WHERE username = ?
  ");

  $query->bind_param("s", $currentUser);
  $query->execute();
  $result = $query->get_result();

  //controllo esistenza del abbonamento

  if($row = $result->fetch_assoc()) //nel caso l'utente ha un abbonamento
  {
    echo json_encode([
      "success" => true,
      "hasAbbonamento" => true,
      "abbonamento" => [
        'idAbbonamento' => $row['idAbbonamento'],
        'dataInizio' => $row['dataInizio'],
        'dataFine' => $row['dataFine'],
        'giorniRimanenti' => max(0, $row['giorniRimanenti']),
        'giorniTrascorsi' => $row['giorniTrascorsi'],
        'durataGiorni' => $row['durataGiorni'],
        'statoAbbonamento' => $row['statoAbbonamento']
      ]
    ]);
  }else //l'utente non ha un abbonamento
  {
    echo json_encode([
      "success" => true,
      "hasAbbonamento" => false
    ]);
  }

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