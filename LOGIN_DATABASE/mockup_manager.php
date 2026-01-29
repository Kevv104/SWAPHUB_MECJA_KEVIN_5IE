<?php
   session_start();
   require_once 'sicurezzaRotte.php';

   $p = $_GET['azione'] ?? '';
   proteggereRotta($p); //per controllare se si ha il permesso alla azione

   $stile = [
    'chat' => ['colore' => 'text-info', 'bg' => 'border-info'],
    'social' => ['colore' => 'text-primary', 'bg' => 'border-primary'],
    'market' => ['colore' => 'text-success', 'bg' => 'border-success'],
    'mod' => ['colore' => 'text-warning', 'bg' => 'border-warning'],
    'admin' => ['colore' => 'text-danger', 'bg' => 'border-danger'],
    ];
?>

<!DOCTYPE html>
<html lang="it">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Mockup permesso: <?php echo htmlspecialchars($p); ?></title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet" />
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.1/font/bootstrap-icons.css">
</head>
<body class="bg-light p-5">
    <div class="container" style="max-width: 700px;">
        <div class="card shadow border-0">
            <div class="card-body p-5 text-center">

            <?php
             //switch che gestisce i mockup dei permessi, suddivisi nelle quattro macro tematiche
              switch($p) 
              {
                 //CHAT
                 case 'create_chat': echo "<h3>Crea chat</h3><p>Sezione della chat.</p>"; break;
                 case 'send_message': echo "<h3>Invia messaggio</h3><p>sezione per scrivere il messaggio.</p>"; break;
                 case 'view_chat': echo "<h3>Visualizza chat</h3><p>Chat in caricamento....</p>"; break;
                 case 'delete_own_message': echo "<h3>Elimina messaggio</h3><p>Eliminazione in corso...</p>"; break;
                 case 'moderate_chat': echo "<h3>Modera chat</h3><p>Sezione moderazione chat.</p>"; break;
                
                 //SOCIAL
                 case 'send_friend_request': echo "<h3>Invia amicizia</h3><p>Invio in corso...</p>"; break;
                 case 'accept_friend_request': echo "<h3>Accetta amicizia</h3><p>Amicizia accettata.</p>"; break;
                 case 'reject_friend_request': echo "<h3>Rifiuta amicizia</h3><p>Amicizia rifiutata.</p>"; break;
                 case 'subscribe_swapplus': echo "<h3>Abbonamento Swap+</h3><p>Sezione per iscriversi a Swap+.</p>"; break;
                 case 'view_own_swapplus': echo "<h3>Il tuo abbonamento</h3><p>Visualizza il tuo abbonamento Swap+.</p>"; break;

                 //PRODOTTI
                 case 'upload_product': echo "<h3>Carica Prodotto</h3>Sezione caricamento prodotto</p>"; break;
                 case 'send_trade_request': echo "<h3>Invia Scambio</h3><p>Invio in corso...</p>"; break;
                 case 'write_review': echo "<h3>Scrivi Recensione</h3><p>Sezione per scrivere recensioni prodotto.</p>"; break;
                 case 'edit_account': echo "<h3>Modifica Account</h3><p>Sezione di modifica impostazioni personali.</p>"; break;
                 case 'send_report': echo "<h3>Invia Segnalazione</h3><p>Invio segnalazione in corso...</p>"; break;
              
                 //MODERAZIONE
                  case 'manage_user_reports': echo "<h3>Gestione Segnalazioni</h3><p>Pannello gestione segnalazioni</p>"; break;
                  case 'ban_user': echo "<h3>Banna Utente</h3><p>Utente bannato.</p>"; break;
                  case 'suspend_user': echo "<h3>Sospendi Utente</h3><p>Utente sospeso.</p>"; break;
                  case 'escalate_report_to_admin': echo "<h3>Escala ad Admin</h3><p>Segnalazione inviata ad admin.</p>"; break;
                  case 'remove_inappropriate_content': echo "<h3>Rimuovi Contenuto</h3><p>Contenuto rimosso correttamente.</p>"; break;
              
                 //ADMIN
                  case 'manage_platform_policies': echo "<h3>Policy Piattaforma</h3><p>Modifica dei termini e condizioni del servizio.</p>"; break;
                  case 'manage_critical_reports': echo "<h3>Segnalazioni Critiche</h3><p>Gestione casi gravi escalation.</p>"; break;
                  case 'appoint_moderator': echo "<h3>Nomina Moderatore</h3><p>Assegnazione ruolo moderatore a un utente fidato.</p>"; break;
                  case 'manage_moderators': echo "<h3>Gestione Staff</h3><p>Monitoraggio attività e permessi dei moderatori.</p>"; break;
                  case 'manage_users': echo "<h3>Gestione Utenti Global</h3><p>Controllo totale sull'anagrafica utenti.</p>"; break;

                  default: echo "<h3>Azione</h3><p>Mockup per la funzionalità: $p</p>"; break;
                 }
             ?>

             <hr>
             <a href="visualizzaUtente.php" class="btn btn-outline-dark btn-sm">
                 Torna alla Dashboard
             </a>
            </div>
        </div>
    </div>
</body>
</html>