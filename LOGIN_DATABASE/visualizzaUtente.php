<?php
  session_start();
  require_once 'sicurezzaRotte.php';
  proteggereRotta();
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
<html lang="it">
  <head>
    <title>Area Riservata Utente</title>
    <meta charset="utf-8" />
    <meta name="viewport" content="width=device-width, initial-scale=1, shrink-to-fit=no" />
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet" />
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.1/font/bootstrap-icons.css">
  </head>
  <body style="background-color: #f8f9fa;">
    <main class="container py-5">
        <div class="row justify-content-center">
            <div class="col-md-10 bg-white p-4 rounded shadow-sm" style="border-top: 5px solid <?php echo $bgcolor; ?>;">
                
                <h1 class="mb-4 fw-bold text-center">Benvenuto, <?php echo htmlspecialchars($_SESSION["name"]); ?>!</h1>

                <div class="mt-4">
                    <div class="d-flex align-items-center mb-3">
                        <h2 class="h4 mb-0 fw-bold text-dark">I tuoi permessi</h2>
                        <span id="jwt-timeout-badge" class="badge rounded-pill bg-success ms-3 shadow-sm" style="font-size: 0.9rem;">
                            <i class="bi bi-clock-history me-1"></i> Tempo rimasto: <span id="countdown-timer">--:--</span>
                        </span>
                    </div>
                   
                    <div class="table-responsive">
                        <table class="table table-hover align-middle border m-0">
                            <thead class="table-light text-dark">
                                <tr>
                                    <th style="width: 70%">Codice Permesso</th>
                                    <th style="width: 30%" class="text-center">Azione (Mockup)</th>
                                </tr>
                            </thead>
                            <tbody id="tabella-permessi-body" class="text-dark"></tbody>
                        </table>
                    </div>
                </div>

                <div class="accordion mt-5" id="rawJsonAccordion">
                    <div class="accordion-item bg-dark border-secondary">
                        <h2 class="accordion-header">
                            <button class="accordion-button collapsed bg-dark text-white" type="button" data-bs-toggle="collapse" data-bs-target="#collapseRawJson">
                                <i class="bi bi-code-slash me-2 text-info"></i> Visualizza dati grezzi richiesta (JSON)
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
                    <a href="logout.php" class="btn btn-danger px-4">Logout</a>
                </div>
            </div>
        </div>
    </main>

    <script src="https://cdn.jsdelivr.net/npm/@popperjs/core@2.11.8/dist/umd/popper.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/js/bootstrap.min.js"></script>
    
    <script>
    function timerCountdown(expireTimestamp) {
        const timerDisplay = document.getElementById('countdown-timer');
        const badge = document.getElementById('jwt-timeout-badge');

        if (!timerDisplay || !badge) return;

        const interval = setInterval(() => {
            const oraAttuale = Math.floor(Date.now() / 1000);
            const tempoRimanente = expireTimestamp - oraAttuale;

            if (tempoRimanente <= 0) {
                clearInterval(interval);
                timerDisplay.textContent = "Scaduto";
                badge.className = "badge rounded-pill bg-danger px-3 py-2";
                alert("Sessione scaduta! Verrai reindirizzato alla login");
                window.location.href = "logout.php";
                return;
            }

            const m = Math.floor(tempoRimanente / 60);
            const s = Math.floor(tempoRimanente % 60);

            timerDisplay.textContent = `${m}:${s < 10 ? '0' : ''}${s}`;

            if (tempoRimanente < 60) {
                badge.className = "badge rounded-pill bg-warning text-dark px-3 py-2";
            }
        }, 1000);
    }
    </script>

    <script>
        fetch('api_permessi.php')
            .then(res => res.json())
            .then(data => {
                document.getElementById('raw-json').innerText = JSON.stringify(data, null, 4);
                const tbody = document.getElementById('tabella-permessi-body');
                
                if(data.permessi && tbody) {
                    tbody.innerHTML = "";

                    data.permessi.forEach(p => {
                        let tr = document.createElement('tr');

                        let tdNome = document.createElement('td');
                        tdNome.className = "text-dark fw-bold";
                        tdNome.textContent = p;
                        
                       
                        let tdAzione = document.createElement('td');
                        tdAzione.className = "text-center";
                       tdAzione.innerHTML = `
                        <a href="mockup_manager.php?azione=${p}" class="btn btn-primary btn-sm px-4 shadow-sm">
                         Apri Mockup
                        </a>`; // tutti i pulsanti mandano a un file php (mockupmanager, dove ci sono tutti i 25 permessi mockup)

                        tr.appendChild(tdNome);
                        tr.appendChild(tdAzione);
                        tbody.appendChild(tr);
                    });
                }

                if(data.scadenza_ts) {
                    // Ora la funzione esiste sicuramente perché è scritta sopra
                    timerCountdown(data.scadenza_ts);
                }
            })
            .catch(err => {
                console.error(err);
                const tbody = document.getElementById('tabella-permessi-body');
                if(tbody) tbody.innerHTML = `<tr><td colspan="2" class="text-danger text-center">Errore: ${err.message}</td></tr>`;
            });
    </script>
  </body>
</html>