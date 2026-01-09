<?php
   session_start();
   require_once 'connectdb.php'; //configurazione per db
   require_once 'config.php'; //importazione chiave pepper da file config.php

   if($_SERVER["REQUEST_METHOD"] === "POST")
   {
      $username = trim($_POST['username']);
      $password = trim($_POST['password']);
      $bgcolor = trim($_POST['bgcolor']);
      $role = trim($_POST['role']);

      if(empty($username) || empty($password) || empty($bgcolor) || empty($role))
      {
        header("Location:register.php?errore=Compila i campi");
        exit();
      }

      
      $statoq = $connessione->prepare("SELECT username FROM utenti WHERE username = ?");
      
      if(!$statoq) //se è null
      {
        die("Errore: " . $connessione->error);
      }

      $statoq->bind_param("s",$username); // riferisco al database che deve cercare con la varabile $username di tipo stringa
      $statoq->execute();
      $statoq->store_result();

      if($statoq->num_rows > 0)
      {
        $statoq->close();
        header("Location:register.php?errore=Utente già esistente!");
        exit();
      } 
      $statoq->close(); //verifica dell' esistenza del utente, se il numero di righe restituite dalla query sono maggiori di 0 vuol dire che quel utente che stiamo cercando è gia stato inserito nel db

        $salt = bin2hex(random_bytes(16)); //generazione salt: vengono creata una stringa random binaria di 16 bit, convertita tramite bin2hex in esadecimale
        $passwordhash = hash('sha256', $password . $salt . PEPPER); //hashing della password, attraverso l'algoritmo sha256, della combinazione di password + salt + pepper, per una maggior sicurezza
        
        //inserimento dati persona nel db
        $statoq = $connessione->prepare("INSERT INTO utenti (username, password, salt, role, bgcolor) VALUES (?, ?, ?, ?, ?)");
        
        if(!$statoq) //se è null
        {
        die("Errore: " . $connessione->error);
        }

        $statoq->bind_param("sssss",$username,$passwordhash,$salt,$role,ltrim($bgcolor,'#'));
        $statoq->execute();
        $statoq->close();


        //imposta sessione
        $_SESSION['name'] = $username;
        $_SESSION['color'] = '#' . ltrim($bgcolor, '#');
        $_SESSION['role'] = $role;

         header("Location: visualizzaUtente.php");
         exit();

      }
      
?>

<!doctype html>
<html lang="en">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>SWAPHUB - REGISTRATI</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet">
    <style>
        body {
            background-color: #3a3a3a; /* stesso sfondo della login */
            height: 100vh;
            display: flex;
            justify-content: center;
            align-items: center;
            font-family: 'Inter', sans-serif; /* stesso font */
        }

        .register-container {
            background: #2a2a2a; /* stessa card bianca della login */
            padding: 40px 35px;
            border-radius: 15px;
            box-shadow: 0 8px 25px rgba(0,0,0,0.08);
            width: 350px;
        }

        .register-container h2 {
            font-weight: 700;
            font-size: 1.6rem;
            margin-bottom: 10px;
            color: #32CD32; /* verde chiaro come pulsante registrati */
        }

        .register-container p {
            font-size: 0.9rem;
            color: #6c757d;
            margin-bottom: 25px;
        }

        .form-control {
            height: 45px;
            border-radius: 10px;
            font-size: 0.95rem;
        }

        .btn-registrati {
            width: 100%;
            height: 45px;
            border-radius: 10px;
            background-color: #32CD32; /* lime green */
            border: none;
            font-weight: 500;
        }

        .btn-registrati:hover {
            background-color: #28a428;
        }

        .bottom-text {
            text-align: center;
            margin-top: 20px;
            font-size: 0.9rem;
        }

        .bottom-text a {
            color: #32CD32;
            text-decoration: none;
            font-weight: 500;
        }

        .bottom-text a:hover {
            text-decoration: underline;
        }

        .alert {
            text-align: center;
            font-size: 0.9rem;
            padding: 8px;
        }
    </style>
</head>
<body>

<div class="register-container">
    <p>Inserisci qui le tue credenziali</p>
    <h2>Benvenuto Swapper!</h2>

    <?php if(isset($_GET["errore"])): ?>
        <div id="erroreMessaggio" class="alert alert-danger">
            <?= htmlspecialchars($_GET["errore"]); ?>
        </div>
    <?php endif; ?>

    <form action="register.php" method="POST">
        <div class="mb-3">
            <input type="text" name="username" class="form-control" placeholder="Username" required>
        </div>
        <div class="mb-3">
            <input type="password" name="password" class="form-control" placeholder="Password" required>
        </div>
        <div class="mb-3">
            <input type="color" name="bgcolor" class="form-control form-control-color" value="#32CD32" title="Scegli un colore">
        </div>
        <div class="mb-3">
            <select class="form-select" name="role" required>
                <option value="utente_non_registrato" selected>Utente non registrato</option>
                <option value="utente_registrato">Utente registrato</option>
                <option value="admin">Admin</option>
                <option value="moderatore">Moderatore</option>
                <option value="corriere">Corriere</option>
            </select>
        </div>
        <button type="submit" class="btn-registrati">REGISTRATI</button>
    </form>

     <div class="text-center mt-3">
                <a href="index.php" class="btn btn-secondary btn-sm">TORNA ALLA HOME</a>
                </div>
</div>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/js/bootstrap.min.js"></script>
<script>
if(document.getElementById("erroreMessaggio")){
    setTimeout(() => {
        const errore = document.getElementById("erroreMessaggio");
        if(errore) errore.style.display = "none";
    }, 3000);
}
</script>
</body>
</html>
