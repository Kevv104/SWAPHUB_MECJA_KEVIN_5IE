<?php
session_start();
require_once __DIR__ . '/vendor/autoload.php';
require_once 'connectdb.php'; //config db
require_once 'config.php'; //importazione del pepper contenuto nel file config.php
require_once 'jwt.php';

use Firebase\JWT\JWT;
use Firebase\JWT\Key;



if($_SERVER["REQUEST_METHOD"] === "POST") 
{
    $username = trim($_POST["username"]);
    $password = trim($_POST["password"]);

    if(empty($username) || empty($password)) //verifica dei campi vuoti
    {
        header("Location:index.php?errore=Compila");
        exit();
    }

    // Prelevo dati utente
    $statoq = $connessione->prepare("SELECT password, salt, bgcolor FROM utenti WHERE username = ?");
    $statoq->bind_param("s", $username);
    $statoq->execute();
    $statoq->store_result();

    if($statoq->num_rows == 1) //se l'utente esiste
    {
        $statoq->bind_result($db_password, $dbsalt, $bgcolor);
        $statoq->fetch();

        $inputhash = hash('sha256', $password . $dbsalt . PEPPER);

        if($inputhash === $db_password) //coincide password
        {
            $statoq->close();

            // Prelevo ruoli dell'utente
            $statoq = $connessione->prepare("SELECT idRuolo FROM UtenteRuolo WHERE username = ?");
            $statoq->bind_param("s", $username);
            $statoq->execute();
            $result = $statoq->get_result();
            $ruoli = [];
            while($row = $result->fetch_assoc()) {
                $ruoli[] = $row['idRuolo'];
            }
            $statoq->close();

            // Prelevo permessi associati ai ruoli
            $permessi = [];
            if(!empty($ruoli)) {
                $ids = implode(',', array_map('intval', $ruoli));
                $query = "SELECT DISTINCT p.nomePermesso
                          FROM Permesso p
                          JOIN RuoloPermesso rp ON rp.idPermesso = p.idPermesso
                          WHERE rp.idRuolo IN ($ids)";
                $result = $connessione->query($query);
                while($row = $result->fetch_assoc()) {
                    $permessi[] = $row['nomePermesso'];
                }
            }

            // Imposto sessione
            $_SESSION['name'] = $username;
            $_SESSION['color'] = "#" . $bgcolor;
            $_SESSION['ruoli'] = $ruoli;
            $_SESSION['permessi'] = $permessi;

            $payload =  //payload jwt
            [
                'iss' => 'swaphub',
                'iat' => time(),
                'exp' => time() + JWT_TTL,
                'sub' => $username,
                'ruoli' => $ruoli,
                'permessi' => $permessi


            ];

            $jwt = JWT::encode($payload, JWT_SECRET, JWT_ALGO); //crea il jwt alla login

            $_SESSION['jwt'] = $jwt; //salvataggio del token in session

            header("Location: visualizzaUtente.php");
            exit();
        }
    }

    $statoq->close();
    header("Location:index.php?errore=Credenziali di accesso errate!");
    exit();
}
?>
