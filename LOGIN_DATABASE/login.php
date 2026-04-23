<?php
//session_start();
   require_once ("sessione.php");
require_once __DIR__ . '/vendor/autoload.php';
require_once 'connectdb.php'; //config db
require_once 'config.php'; //importazione del pepper contenuto nel file config.php
require_once 'jwt.php';
require_once __DIR__ . '/config/TenantManager.php';

use Firebase\JWT\JWT;
use Firebase\JWT\Key;



if($_SERVER["REQUEST_METHOD"] === "POST") 
{
    $username = trim($_POST["username"] ?? "");
    $password = trim($_POST["password"] ?? "");

    if(empty($username) || empty($password))
    {
        header("Location:index.php?errore=Compila");
        exit();
    }

    $statoq = $connessione->prepare("SELECT password, salt, bgcolor, tenant_id FROM utenti WHERE username = ?");
    $statoq->bind_param("s", $username);
    $statoq->execute();
    $statoq->store_result();

    if($statoq->num_rows == 1)
    {
        $statoq->bind_result($db_password, $dbsalt, $bgcolor, $tenant_id);
        $statoq->fetch();

        $inputhash = hash('sha256', $password . $dbsalt . PEPPER);
        $isHashedMatch = hash_equals($db_password, $inputhash);
        $isLegacyPlainMatch = empty($dbsalt) && hash_equals($db_password, $password);

        if($isHashedMatch || $isLegacyPlainMatch)
        {
            $statoq->close();

            $statoq = $connessione->prepare("SELECT idRuolo FROM UtenteRuolo WHERE username = ?");
            $statoq->bind_param("s", $username);
            $statoq->execute();
            $result = $statoq->get_result();
            $ruoli = [];
            while($row = $result->fetch_assoc()) {
                $ruoli[] = $row['idRuolo'];
            }
            $statoq->close();

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

            $_SESSION['name'] = $username;
            $_SESSION['tenant_id'] = $tenant_id;
            $_SESSION['logged_in'] = true;
            $_SESSION['color'] = "#" . $bgcolor;
            $_SESSION['ruoli'] = $ruoli;
            $_SESSION['permessi'] = $permessi;


            $payload =
            [
                'iss' => 'swaphub',
                'iat' => time(),
                'exp' => time() + JWT_TTL,
                'sub' => $username,
                'tenant_id' => $tenant_id,
                'ruoli' => $ruoli,
                'permessi' => $permessi


            ];

            $jwt = JWT::encode($payload, JWT_SECRET, JWT_ALGO);

            $_SESSION['jwt'] = $jwt;
            $_SESSION['jwt_token'] = $jwt;

            setcookie('last_tenant', $tenant_id, time() + (90 * 24 * 60 * 60), '/');

            header("Location: visualizzaUtente.php");
            exit();
        }
    }

    $statoq->close();
    header("Location:index.php?errore=Credenziali di accesso errate!");
    exit();
}
?>
