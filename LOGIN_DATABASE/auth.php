<?php
 require_once __DIR__ . '/vendor/autoload.php';
 require_once 'jwt.php';

 use Firebase\JWT\JWT;
 use Firebase\JWT\Key;

 if(!isset($_SESSION['jwt'])) 
 {
   header("Location: index.php?errore=Sessione scaduta");
   exit();
 }

 try 
 {
   $decoded = JWT::decode($_SESSION['jwt'], new Key(JWT_SECRET, JWT_ALGO)); //il token è ancora valido
   http_response_code(200); //ok

 } catch(Exception $e)
 {
    session_unset(); 
    session_destroy(); // il toek  nonè più valido: sessione scaduta
    header("Location: index.php?errore=" . urlencode("Accesso negato o sessione scaduta"));
    exit();
 }
