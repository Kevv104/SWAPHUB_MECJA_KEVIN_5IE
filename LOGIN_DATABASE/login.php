<?php 
 session_start();
 require_once 'connectdb.php'; //config db
 require_once 'config.php'; //importazione del pepper contenuto nel file config.php
 if($_SERVER["REQUEST_METHOD"] === "POST") 
 {
  $username = trim($_POST["username"]);
  $password = trim($_POST["password"]);

  if(empty($username) || empty($password)) //verifica dei campi vuoti
  {
   header("Location:index.php?errore=Compila");
   exit();
  }

  $statoq = $connessione->prepare("SELECT password, salt, role, bgcolor FROM utenti WHERE username = ?");

  if(!$statoq) //se è null
  {
    die("Errore: " . $connessione->error);
  }

  $statoq->bind_param("s",$username);
  $statoq->execute();
  $statoq->store_result();

  if($statoq->num_rows == 1) //se l'utente esiste
  {
    $statoq->bind_result($db_password,$dbsalt,$role,$bgcolor);
    $statoq->fetch();

    $inputhash = hash('sha256', $password . $dbsalt . PEPPER);
    
     if($inputhash === $db_password)  //coincide password
     {
        $_SESSION['name'] = $username; //salvataggio nome inserito
        $_SESSION['color'] = "#" . $bgcolor; //serve per definire il colore in hex
        $_SESSION['role'] = $role; //salvataggio ruolo

        $statoq->close();
        header("Location:visualizzaUtente.php");
        exit();
     } 
  }
   
   $statoq->close(); 
   header("Location:index.php?errore=Credenziali di accesso errate!"); //se le credenziali non sono corrette o presenti nel db, allora errore
   exit();


   header("Location: index.php"); //per evitare accessi diretti a index.php senza inserire nulla
   exit();


}
?>