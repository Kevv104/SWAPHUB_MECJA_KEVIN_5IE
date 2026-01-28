<?php
  session_start();
  require_once 'auth.php';
  require_once 'connectdb.php';
  require_once __DIR__ . '/vendor/autoload.php';
  require_once 'jwt.php';


  $statoq = $connessione->prepare("
    SELECT ur.idRuolo, u.bgcolor 
    FROM utenti u 
    INNER JOIN UtenteRuolo ur ON u.username = ur.username 
    WHERE u.username = ?
");
  $statoq->bind_param("s",$_SESSION['name']);
  $statoq->execute();
  $statoq->bind_result($roleID,$bgcolor);
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

    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.1/font/bootstrap-icons.css">
  </head>
   <main class="container py-5">
        <div class="row justify-content-center">
            <div class="col-md-10 bg-white p-4 rounded shadow-sm">
                
                <h1 class="mb-4 fw-bold text-center">Benvenuto, <?php echo htmlspecialchars($_SESSION["name"]); ?>!</h1>

                <div class="mt-4">
                    <h2 class="h4 mb-3"><i class="bi bi-shield-lock"></i> I tuoi permessi</h2>
                    <div class="table-responsive">
                        <table class="table table-dark table-hover align-middle border-secondary m-0">
                            <thead class="table-secondary text-dark">
                                <tr>
                                    <th style="width: 70%">Codice Permesso</th>
                                    <th style="width: 30%" class="text-center">Azione (Mockup)</th>
                                </tr>
                            </thead>
                            <tbody id="tabella-permessi-body">
                                </tbody>
                        </table>
                    </div>
                </div>

                <div class="accordion mt-5" id="rawJsonAccordion">
                    <div class="accordion-item bg-dark border-secondary">
                        <h2 class="accordion-header">
                            <button class="accordion-button collapsed bg-dark text-white" type="button" data-bs-toggle="collapse" data-bs-target="#collapseRawJson">
                                <i class="bi bi-code-slash me-2"></i> Visualizza dati grezzi richiesta (JSON)
                            </button>
                        </h2>
                        <div id="collapseRawJson" class="accordion-collapse collapse" data-bs-parent="#rawJsonAccordion">
                            <div class="accordion-body p-0">
                                <pre id="raw-json" class="text-info bg-black m-0 p-3 small"></pre>
                            </div>
                        </div>
                    </div>
                </div>

                <div class="text-center mt-4">
                    <a href="logout.php" class="btn btn-danger">Logout</a>
                </div>

            </div>
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

    <script>
        fetch('api_permessi.php') //invia richiesta get HTTP al server, cercando di ottenere il file api_permessi.php
            .then(res => res.json()) // promise in js, si attende la risposta dal server, convertendola in oggetto JSON
            .then(data => { //promise in js, che utilizza i dati ottenuti dalla get
                
                
                document.getElementById('raw-json').innerText = JSON.stringify(data, null, 4); //salvataggio del oggetto json in html element "raw-json", formattazione json.strigify
                
                
                const ul = document.getElementById('lista-permessi'); //individua l'elemento html lista-permessi per inserire il raw json
                if(data.permessi) { //se l' array permessi esiste nella risposta della get (oggetto json)
                    data.permessi.forEach(p => { // con un foreach si scorre su ogni singolo permesso dell' array
                        let li = document.createElement('li'); //si crea un item dove inserire il permesso
                        li.innerText = p; //viene inserito il testo del permesso
                        ul.appendChild(li); //aggiunge con append il li alla lista
                    });
                }
            })
            .catch(err => alert("Errore: " + err)); //gestione eventuali errori
    </script>

    <script src = "timerJWT.js"></script>
  </body>
</html>
