<?php

function normalizzaPermessoRotta(string $permesso): ?string
{
  $mappa = [
    'upload_product' => 'manage_products',
    'accept_friend_request' => 'manage_friend_request',
    'reject_friend_request' => null,
  ];

  if (array_key_exists($permesso, $mappa)) {
    return $mappa[$permesso];
  }

  return $permesso;
}

 function proteggereRotta($permessoNecessario = null) 
 {
   if(!isset($_SESSION['name'])) 
   {
     header("Location: index.php?errore=SessioneScaduta");
     exit;
   }

   if($permessoNecessario !== null) //se è richiesto un permesso specifico
   {
      $permessiSessione = [];

      if (isset($_SESSION['permessi']) && is_array($_SESSION['permessi'])) {
        foreach ($_SESSION['permessi'] as $permesso) {
          $permessoNormalizzato = normalizzaPermessoRotta($permesso);

          if ($permessoNormalizzato !== null) {
            $permessiSessione[] = $permessoNormalizzato;
          }
        }
      }

      $permessoRichiesto = normalizzaPermessoRotta($permessoNecessario);

      if($permessoRichiesto === null || !in_array($permessoRichiesto, $permessiSessione))
      {
         http_response_code(403);
         die("<h1>403 - Accesso Negato</h1><p>Non hai il permesso: <b>$permessoNecessario</b></p>");
      }
   }
 }




?>