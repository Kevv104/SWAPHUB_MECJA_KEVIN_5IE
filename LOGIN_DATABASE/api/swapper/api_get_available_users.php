<?php

header('Content-Type: application/json');
require_once __DIR__ . '/../../vendor/autoload.php';
require_once '../../jwt.php';
require_once '../../connectdb.php';
use Firebase\JWT\JWT;
use Firebase\JWT\Key;

session_start();

if(!isset($_SESSION['jwt'])) {
  http_response_code(401);
  echo json_encode(["error" => "Non autorizzato"]); 
  exit;
}

try {
  $decoded = JWT::decode($_SESSION['jwt'], new Key(JWT_SECRET, JWT_ALGO));
  $currentUser = $decoded->sub;

  $query = $connessione->prepare("
    SELECT 
      username,
      Nome,
      Cognome,
      Email,
      fotoprofilo
    FROM utenti
    WHERE username != ?
    ORDER BY Nome, Cognome
  ");

  $query->bind_param("s", $currentUser);
  $query->execute();
  $result = $query->get_result();

  $utenti = [];
  while($row = $result->fetch_assoc()) {
    $utenti[] = $row;
  }
  
  echo json_encode([
    "success" => true,
    "utenti" => $utenti
  ]);

  $query->close();
  $connessione->close();

} catch(Exception $e) {
  http_response_code(401);
  echo json_encode([
    "error" => $e->getMessage()
  ]);
}
?>