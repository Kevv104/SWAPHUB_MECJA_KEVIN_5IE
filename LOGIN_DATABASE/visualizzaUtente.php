<?php
  session_start();
  require_once 'connectdb.php';
  require_once __DIR__ . '/vendor/autoload.php';
  require_once 'jwt.php';
  require_once 'auth.php';


  if(!isset($_SESSION["name"]))
  { 
    header("location: index.php?errore=Effettua il login");
    exit();
  }

  $statoq = $connessione->prepare("SELECT idRuolo, bgcolor FROM utenti WHERE username = ?");
  $statoq->bind_param("s",$_SESSION['name']);
  $statoq->execute();
  $statoq->bind_result($role,$bgcolor);
  $statoq->fetch();
  $statoq->close();

  $bgcolor = '#' . ltrim($bgcolor, '#');
?>


<!doctype html>
<html lang="en">
  <head>
    <title>Area Riservata Utente</title>
    <!-- Required meta tags -->
    <meta charset="utf-8" />
    <meta
      name="viewport"
      content="width=device-width, initial-scale=1, shrink-to-fit=no"
    />

    <!-- Bootstrap CSS v5.2.1 -->
    <link
      href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css"
      rel="stylesheet"
      integrity="sha384-T3c6CoIi6uLrA9TneNEoa7RxnatzjcDSCmG1MXxSR1GAsXEV/Dwwykc2MPK8M2HN"
      crossorigin="anonymous"
    />
  </head>

  <body>
    <header>
      <!-- place navbar here -->
    </header>
    <main>
      <body style = "background-color: <?php echo htmlspecialchars($bgcolor); ?>">
         
      <div class = "container vh-100 d-flex flex-column justify-content-center align-items-center text-center">
         
        <h1 class = "mb-3 fw-bold text-center">Benvenuto, <?php echo htmlspecialchars($_SESSION["name"]); ?>!</h1>

        <ul class="list-group">
    <?php
    switch($role)
    {
        case "admin":
            echo '<li class="list-group-item fw-bold">Gestione di tutti gli utenti piattaforma</li>';
            echo '<li class="list-group-item fw-bold">Nomina moderatori</li>';
            echo '<li class="list-group-item fw-bold">Gestione ticket di segnalazione di situazioni gravi, segnalate da moderatori</li>';
            break;

        case "moderatore":
            echo '<li class="list-group-item fw-bold">Gestione maggiorparte delle segnalazioni</li>';
            echo '<li class="list-group-item fw-bold">Decisione (di casi non gravi) su eventuali BAN o sospensioni, di account che vanno contro le regole della piattaforma</li>';
            echo '<li class="list-group-item fw-bold">Libertà di poter rimuovere contenuti inappropriati che vengono trovati, anche senza segnalazione</li>';
            echo '<li class="list-group-item fw-bold">Segnalazione di casi complessi al admin</li>';
            break;

        case "utente_registrato":
            echo '<li class="list-group-item fw-bold">Puoi caricare prodotti</li>';
            echo '<li class="list-group-item fw-bold">Puoi effettuare richieste di scambio</li>';
            echo '<li class="list-group-item fw-bold">Puoi entrare in gruppi di scambio</li>';
            echo '<li class="list-group-item fw-bold">Puoi accettare o chiedere richieste d\'amicizia, con altri utenti registrati</li>';
            echo '<li class="list-group-item fw-bold">Puoi comunicare attraverso commenti, sia durante la trattativa di scambio che sotto gli articoli per chiedere maggiori informazioni</li>';
            echo '<li class="list-group-item fw-bold">Puoi ricercare prodotti che interessano</li>';
            break;

        case "utente_non_registrato":
            echo '<li class="list-group-item fw-bold">Per avere la full SwapHub experience, registrati, è gratis e semplice!</li>';
            break;

        case "corriere":
            echo '<li class="list-group-item fw-bold">Gestisci consegne</li>';
            echo '<li class="list-group-item fw-bold">Aggiorna dati spedizione</li>';
            break;
    }
    ?>
</ul>

        
        <a href = "logout.php">Logout</a>

         </div>
    </main>
    <footer>
      <!-- place footer here -->
    </footer>
    <!-- Bootstrap JavaScript Libraries -->
    <script
      src="https://cdn.jsdelivr.net/npm/@popperjs/core@2.11.8/dist/umd/popper.min.js"
      integrity="sha384-I7E8VVD/ismYTF4hNIPjVp/Zjvgyol6VFvRkX/vR+Vc4jQkC+hVqc2pM8ODewa9r"
      crossorigin="anonymous"
    ></script>

    <script
      src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/js/bootstrap.min.js"
      integrity="sha384-BBtl+eGJRgqQAUMxJ7pMwbEyER4l1g+O15P+16Ep7Q9Q+zqX6gSbd85u4mG4QzX+"
      crossorigin="anonymous"
    ></script>
  </body>
</html>
