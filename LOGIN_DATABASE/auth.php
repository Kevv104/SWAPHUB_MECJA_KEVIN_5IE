<?php
 require_once __DIR__ . '/vendor/autoload.php';
 require_once 'jwt.php';

 use Firebase\JWT\JWT;
 use Firebase\JWT\Key;

 if(!isset($_SESSION['jwt'])) 
 {
   header("Location: login.php");
   exit();
 }

 try 
 {
   $decoded = JWT::decode($_SESSION['jwt'], new Key(JWT_SECRET, JWT_ALGO)); //il token è ancora valido
   http_response_code(200); //ok

 } catch(Exception $e)
 {
    http_response_code(500); //no ok
    session_destroy(); // il toek  nonè più valido: sessione scaduta
    header("Location: login.php?errore=Sessione scaduta");
    exit();
 }
