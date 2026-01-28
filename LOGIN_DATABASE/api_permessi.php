<?php

header('Content-type: application/json');
require_once __DIR__ . '/vendor/autoload.php';
require_once 'jwt.php';
use Firebase\JWT\JWT;
use Firebase\JWT\Key;

session_start();

if(!isset($_SESSION['jwt'])) //se il token di sessione non è settato, non sei autorizzato
{
  http_response_code(401);
  echo json_encode(["error" => "Utente non autorizzato"]); 
}

try
{
  $decoded = JWT::decode($_SESSION['jwt'],new Key(JWT_SECRET,JWT_ALGO)); //decodifica del JWT, per la lettura dei dati dentro la payload del jwt stesso
  
  echo json_encode([
    "utente" => $decoded->sub,
    "permessi" => $decoded->permessi,
    "scadenza" => date('H:i:s',$decoded->exp)
  ]);

}catch(Exception $e)
{
  http_response_code(401);

  //serve per 
  echo json_encode([
    "status" => "Errore",
    "error", $e->getMessage()
  ]);
}



?>