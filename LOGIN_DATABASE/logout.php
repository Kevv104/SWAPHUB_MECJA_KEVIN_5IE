<?php
  //session_start();
   require_once ("sessione.php");
  $_SESSION = array();
  session_destroy();
  header("Location: index.php?logout=1");
  exit();
?>