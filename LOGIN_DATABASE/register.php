<?php
session_start();
require_once 'connectdb.php'; //configurazione per db
require_once 'config.php'; //importazione chiave pepper da file config.php

if($_SERVER["REQUEST_METHOD"] === "POST")
{
    $username = trim($_POST['username']);
    $password = trim($_POST['password']);
    $bgcolor = trim($_POST['bgcolor']);
    $ruolo_id = trim($_POST['role']); //era $_POST['ruolo'], ma nel form il name è "role"

    if(empty($username) || empty($password) || empty($bgcolor) || empty($ruolo_id))
    {
        header("Location:register.php?errore=Compila i campi");
        exit();
    }

    //verifica se l'utente esiste già
    $statoq = $connessione->prepare("SELECT username FROM utenti WHERE username = ?");
    $statoq->bind_param("s", $username);
    $statoq->execute();
    $statoq->store_result();

    if($statoq->num_rows > 0)
    {
        $statoq->close();
        header("Location:register.php?errore=Utente già esistente!");
        exit();
    }
    $statoq->close();

    //hash della password
    $salt = bin2hex(random_bytes(16)); //generazione salt
    $passwordhash = hash('sha256', $password . $salt . PEPPER); //hashing password

    $bgcolor_clean = ltrim($bgcolor,'#'); //rimuove #

    //inserimento dati persona nel db
    $statoq = $connessione->prepare("INSERT INTO utenti (username, password, salt, bgcolor) VALUES (?, ?, ?, ?)");
    $statoq->bind_param("ssss", $username, $passwordhash, $salt, $bgcolor_clean);
    $statoq->execute();
    $statoq->close();

    //inserimento ruolo dell'utente
    $statoq = $connessione->prepare("INSERT INTO UtenteRuolo (username, idRuolo) VALUES (?, ?)");
    $statoq->bind_param("si", $username, $ruolo_id);
    $statoq->execute();
    $statoq->close();

    //lettura dei permessi associati al ruolo
    $statoq = $connessione->prepare("
        SELECT p.nomePermesso
        FROM Permesso p
        JOIN RuoloPermesso rp ON rp.idPermesso = p.idPermesso
        WHERE rp.idRuolo = ?");
    $statoq->bind_param("i", $ruolo_id);
    $statoq->execute();
    $result = $statoq->get_result(); //corretto da $stmt->get_result()
    $permessi = [];
    while($row = $result->fetch_assoc()){
        $permessi[] = $row['nomePermesso'];
    }
    $statoq->close();

    //imposta sessione
    $_SESSION['name'] = $username;
    $_SESSION['color'] = '#' . $bgcolor_clean;
    $_SESSION['ruoli'] = [$ruolo_id];
    $_SESSION['permessi'] = $permessi;

    header("Location: visualizzaUtente.php");
    exit();
}
else{


?>

<!doctype html>
<html lang="en">
<head>
    <meta charset="utf-8" />
    <meta name="viewport" content="width=device-width, initial-scale=1, shrink-to-fit=no"/>
    <title>Sistema Registrazione - SWAPHUB</title>

    <!-- Bootstrap CSS -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet">

    <style>
        body {
            background-color: #3a3a3a;
            height: 100vh;
            display: flex;
            justify-content: center;
            align-items: center;
            font-family: 'Inter', sans-serif;
        }

        .login-container {
            background: #2a2a2a;
            color: #008000;
            padding: 40px 35px;
            border-radius: 15px;
            box-shadow: 0 8px 25px rgba(0,0,0,0.4);
            width: 350px;
        }

        .login-container h2 {
            font-weight: 700;
            font-size: 1.6rem;
            margin-bottom: 10px;
        }

        .login-container p {
            font-size: 0.9rem;
            color: #6c757d;
            margin-bottom: 25px;
        }

        .form-control {
            height: 45px;
            border-radius: 10px;
            font-size: 0.95rem;
        }

        .btn-primary {
            width: 100%;
            height: 45px;
            border-radius: 10px;
            background-color: #008000;
            border: none;
            font-weight: 500;
        }

        .btn-primary:hover {
            background-color: grey;
        }

        .bottom-text {
            text-align: center;
            margin-top: 20px;
            font-size: 0.9rem;
        }

        .bottom-text a {
            color: #ffff;
            text-decoration: none;
            font-weight: 500;
        }

        .alert {
            text-align: center;
            font-size: 0.9rem;
            padding: 8px;
        }
    </style>
</head>

<body>
<main>
    <div class="login-container">
        <h2>Benvenuto Swapper!</h2>
        <p>Inserisci le tue credenziali</p>

        <?php if(isset($_GET["errore"])): ?>
            <div class="alert alert-danger"><?= htmlspecialchars($_GET["errore"]); ?></div>
        <?php endif; ?>

        <form action="register.php" method="POST">
            <div class="mb-3"><input type="text" name="username" class="form-control" placeholder="Username" required></div>
            <div class="mb-3"><input type="password" name="password" class="form-control" placeholder="Password" required></div>
            <div class="mb-3"><input type="color" name="bgcolor" class="form-control form-control-color" value="#32CD32" title="Scegli un colore"></div>
            <div class="mb-3">
                <select class="form-select" name="role" required>
                    <option value="1">Utente base</option>
                    <option value="2" selected>Swapper</option>
                    <option value="3">Admin</option>
                    <option value="4">Moderatore</option>
                    <option value="5">Corriere</option>
                </select>
            </div>
            <button type="submit" class="btn btn-primary w-100">REGISTRATI</button>
        </form>

        <div class="bottom-text">
            <a href="login.php">Effettua il login</a>
        </div>

        <div class="text-center mt-3">
            <a href=".." class="btn btn-secondary btn-sm">TORNA ALLA HOME</a>
        </div>
    </div>
</main>

<!-- Bootstrap JS -->
<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/js/bootstrap.min.js"></script>
</body>
</html>

<?php 
}
?>
