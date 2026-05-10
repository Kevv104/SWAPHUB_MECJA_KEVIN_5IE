<?php
//session_start();
   require_once ("sessione.php");
require_once 'connectdb.php'; //configurazione per db
require_once 'config.php'; //importazione chiave pepper da file config.php

// SETUP: Inizializza cartelle necessarie con permessi corretti
function initializeUploadDirs() {
    $base_dir = __DIR__;
    $dirs = [
        $base_dir . '/uploads',
        $base_dir . '/uploads/profile'
    ];
    
    foreach($dirs as $dir) {
        if(!is_dir($dir)) {
            @mkdir($dir, 0777, true);
        }
        @chmod($dir, 0777);
    }
}
initializeUploadDirs();

if($_SERVER["REQUEST_METHOD"] === "POST")
{
    $nome = trim($_POST['nome']);
    $cognome = trim($_POST['cognome']);
    $localita = trim($_POST['localita']);
    $email = trim($_POST['email']);
    $username = trim($_POST['username']);
    $password = trim($_POST['password']);
    $bgcolor = trim($_POST['bgcolor']);
    $ruolo_id = trim($_POST['role']); //era $_POST['ruolo'], ma nel form il name è "role"

    if(empty($nome)||empty($cognome)||empty($localita)||empty($email)||empty($username) || empty($password) || empty($bgcolor) || empty($ruolo_id))
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

    $foto_path = 'uploads/profile/default.png';

    if(isset($_FILES['fotoprofilo']) && $_FILES['fotoprofilo']['error'] === 0) 
    {
       $tipiconsentiti = ['image/jpeg', 'image/png', 'image/webp', 'image/pjpeg'];
       $maxsize = 2 * 1024 * 1024;

       $finfo = finfo_open(FILEINFO_MIME_TYPE);
       $mime = finfo_file($finfo, $_FILES['fotoprofilo']['tmp_name']);
       finfo_close($finfo);

       if(!in_array($mime, $tipiconsentiti)) 
       {
           header("Location: register.php?errore=Formato immagine non valido. Accettate: JPEG, PNG, WebP");
           exit;
       }

       if($_FILES['fotoprofilo']['size'] > $maxsize)
       {
           header("Location: register.php?errore=Immagine troppo grande (max 2MB)");
           exit();
       }

       $upload_dir = __DIR__ . '/uploads/profile';
       
       // Assicura che le cartelle esistano e siano scrivibili
       if(!is_dir($upload_dir)) {
           @mkdir($upload_dir, 0777, true);
       }
       @chmod($upload_dir, 0777);
       
       // Se ancora non scrivibile, tenta con parent
       if(!is_writable($upload_dir)) {
           @chmod(dirname($upload_dir), 0777);
           @chmod($upload_dir, 0777);
       }

       $ext = strtolower(pathinfo($_FILES['fotoprofilo']['name'], PATHINFO_EXTENSION));
       if ($ext === '') {
           $ext = 'jpg';
       }

       // Un solo file per utente: se ricarica la foto, sovrascrive la precedente.
       $filename = 'profile_' . preg_replace('/[^a-zA-Z0-9_-]/', '_', $username) . '.' . $ext;
       $destination = $upload_dir . '/' . $filename;

       // Pulisce eventuali vecchi file della stessa foto profilo, così non si accumulano copie.
       foreach (glob($upload_dir . '/profile_' . preg_replace('/[^a-zA-Z0-9_-]/', '_', $username) . '.*') as $oldProfileFile) {
           if (is_file($oldProfileFile) && basename($oldProfileFile) !== basename($destination)) {
               @unlink($oldProfileFile);
           }
       }

       // Tenta il caricamento
       if(move_uploaded_file($_FILES['fotoprofilo']['tmp_name'], $destination)) {
           // Assicura che il file caricato sia leggibile
           @chmod($destination, 0666);
           $foto_path = 'uploads/profile/' . $filename;
       } else {
           // Se fallisce, usa default
           $foto_path = 'uploads/profile/default.png';
       }
    }

    //inserimento dati persona nel db
    $localita = substr($localita, 0, 100); //limita la lunghezza della localita
    $statoq = $connessione->prepare("INSERT INTO utenti (username, password, salt, bgcolor, nome, cognome,localita, fotoprofilo, email) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?)");
    $statoq->bind_param("sssssssss", $username, $passwordhash, $salt, $bgcolor_clean, $nome, $cognome, $localita, $foto_path, $email);
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
    $_SESSION['foto']  = $foto_path;


    header("Location: index.php?msg=Registrazione completata");
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
    <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.css" rel="stylesheet">


    <style>
        body {
            background-color: #3a3a3a;
            min-height: 100vh;
            display: flex;
            justify-content: center;
            align-items: center;
            font-family: 'Inter', sans-serif;
            padding: 20px;
        }

        main {
            width: 100%;
            display: flex;
            justify-content: center;
        }

        .login-container {
            background: #2a2a2a;
            color: #008000; 
            padding: 40px 35px;
            border-radius: 15px;
            box-shadow: 0 8px 25px rgba(0,0,0,0.4);
            width: 100%;
            max-width: 350px;
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

        @media (max-width: 576px) {
            .login-container {
                padding: 25px 20px;
                max-width: 100%;
            }

            .login-container h2 {
                font-size: 1.3rem;
            }

            .form-control {
                height: 42px;
                font-size: 0.9rem;
            }

            .btn-primary {
                height: 42px;
            }
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

        <form action="register.php" method="POST" enctype="multipart/form-data">

            <div class="mb-3"><input type="text" name="nome" class="form-control" placeholder="Nome" required></div>
            <div class="mb-3"><input type="text" name="cognome" class="form-control" placeholder="Cognome" required></div>
            <div class="mb-3">
             <div class="input-group">
             <span class="input-group-text">
             <i class="bi bi-geo-alt-fill"></i>
             </span>
             <input type="text"
               name="localita"
               id="localita"
               class="form-control"
               placeholder="Località"
               autocomplete="off"
               required>
             </div>
             </div>

             <div class="mb-3">
           <input type="file"
           name="fotoprofilo"
           class="form-control"
            placeholder="Foto Profilo"
           accept="image/*">
         </div>


            <div class="mb-3"><input type="text" name="email" class="form-control" placeholder="Email" required></div>
            <div class="mb-3"><input type="text" name="username" class="form-control" placeholder="Username" required></div>
            <div class="mb-3"><input type="password" name="password" class="form-control" placeholder="Password" required></div>
            
            <div class="mb-3"><input type="color" name="bgcolor" class="form-control form-control-color" value="#32CD32" title="Scegli un colore"></div>
            <div class="mb-3">
                <select class="form-select" name="role" required>
                    <option value="1">Admin</option>
                    <option value="2">Moderatore</option>
                    <option value="3" selected>Swapper</option>
                    <option value="4">Corriere</option>
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

<script> //script per autocomplete città per Località, usando OpenStreetMap
    const input = document.getElementById("localita");

    let timeout = null;

    input.addEventListener("input", function () {
    clearTimeout(timeout);
    const query = this.value;

    if (query.length < 3) return;

    timeout = setTimeout(() => {
        fetch(`https://nominatim.openstreetmap.org/search?format=json&limit=5&addressdetails=1&city=${query}`)
            .then(res => res.json())
            .then(data => showSuggestions(data));
    }, 300);
    });

    function showSuggestions(results) {
    removeSuggestions();

    const list = document.createElement("div");
    list.className = "list-group position-absolute w-100";
    list.id = "suggestions";

      results.forEach(place => {
        const item = document.createElement("button");
        item.type = "button";
        item.className = "list-group-item list-group-item-action";
        item.textContent = place.display_name;

        item.onclick = () => {
            input.value = place.display_name;
            removeSuggestions();
        };

        list.appendChild(item);
    });

    input.parentNode.appendChild(list);
}

    function removeSuggestions() {
    const old = document.getElementById("suggestions");
    if (old) old.remove();
}

document.addEventListener("click", function (e) {
    if (!input.contains(e.target)) removeSuggestions();
});
</script>

</body>
</html>

<?php 
}
?>
