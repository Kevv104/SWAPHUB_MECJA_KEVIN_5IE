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
} //verifica metodo post 

try
{
  $decoded = JWT::decode($_SESSION['jwt'], new Key(JWT_SECRET, JWT_ALGO));
  $currentUser = $decoded->sub;

  //lettura input json
  $input = json_decode(file_get_contents('php://input'), true);
  $durataGiorni = $input['durataGiorni'] ?? null;

  //validazione input
  $durateConsentite = [30, 90, 365];
  if(!in_array($durataGiorni, $durateConsentite)) {
    echo json_encode([
      "success" => false,
      "error" => "Durata non valida. Scegli: 30, 90 o 365 giorni"
    ]);
    exit;
  }

    //controllo se è gia presente abbonamento attivo
    $checkQuery = $connessione->prepare("
    SELECT * FROM vista_swapplus_utente 
    WHERE username = ? AND statoAbbonamento IN ('attivo', 'in_scadenza')
  ");

  $checkQuery->bind_param("s", $currentUser);
  $checkQuery->execute();
  $result = $checkQuery->get_result();

  if($result->num_rows > 0) 
  {
     $abbonamentoEsistente = $result->fetch_assoc(); //l' abbonmento preso dalla fetch

     echo json_encode([
      "success" => false,
      "error" => "Hai già un abbonamento attivo fino al " . $abbonamentoEsistente['dataFine'],
      "abbonamentoEsistente" => [
        'dataFine' => $abbonamentoEsistente['dataFine'],
        'giorniRimanenti' => $abbonamentoEsistente['giorniRimanenti']
      ]
    ]); //restituzione risultato già esistente

     $checkQuery->close();
     $connessione->close();
     exit;
  }

  $checkQuery->close();

  //calcolo data inizio e fine abbonamento
  $dataInizio = date('Y-m-d'); //  data oggi
  $dataFine = date('Y-m-d', strtotime("+{$durataGiorni} days")); //  data oggi + durata

  //creazione nuovo abbonamento
  $insertQuery = $connessione->prepare("
    INSERT INTO SwapPlus (user, dataInizio, dataFine)
    VALUES (?, ?, ?)
  ");

  $insertQuery->bind_param("sss", $currentUser, $dataInizio, $dataFine);
  $insertQuery->execute();

  $idAbbonamento = $connessione->insert_id; //id autoincrement

   echo json_encode([
    "success" => true,
    "message" => "Abbonamento Swap+ attivato con successo!",
    "abbonamento" => [
      'idAbbonamento' => $idAbbonamento,
      'dataInizio' => $dataInizio,
      'dataFine' => $dataFine,
      'durataGiorni' => $durataGiorni,
      'statoAbbonamento' => 'attivo'
    ]
  ]); //restituzione dati nuovo abbonamento

  $insertQuery->close();
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