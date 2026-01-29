<?php

 function proteggereRotta($permessoNecessario = null) 
 {
   if(!isset($_SESSION['name'])) 
   {
     header("Location: index.php?errore=SessioneScaduta");
     exit;
   }

   if($permessoNecessario !== null) //se è richiesto un permesso specifico
   {
      if(!isset($_SESSION['permessi']) || !in_array($permessoNecessario, $_SESSION['permessi']))
      {
         http_response_code(403);
         die("<h1>403 - Accesso Negato</h1><p>Non hai il permesso: <b>$permessoNecessario</b></p>");
      }
   }
 }




?>