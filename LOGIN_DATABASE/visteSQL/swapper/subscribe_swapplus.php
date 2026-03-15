<!doctype html>
<html lang="it">
<head>
  <title>Attiva Swap+ - SwapHub</title>
  <meta charset="utf-8" />
  <meta name="viewport" content="width=device-width, initial-scale=1" />
  <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet" />
  <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.1/font/bootstrap-icons.css">
  <style>
    .plan-card {
      cursor: pointer;
      transition: all 0.3s;
      border: 2px solid transparent;
    }
    .plan-card:hover {
      transform: translateY(-5px);
      box-shadow: 0 10px 20px rgba(0,0,0,0.1);
    }
    .plan-card.selected {
      border-color: gold;
      background-color: #fffbf0;
    }
    .price {
      font-size: 2.5rem;
      font-weight: bold;
      color: gold;
    }
    .best-value {
      position: absolute;
      top: -15px;
      right: 20px;
      background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
      color: white;
      padding: 5px 15px;
      border-radius: 20px;
      font-size: 0.8rem;
      font-weight: bold;
    }
  </style>
</head>
<body style="background-color: #f8f9fa;">
  <main class="container py-5">
    <div class="row justify-content-center">
      <div class="col-md-10">
        
        <div class="text-center mb-5">
          <h1 class="fw-bold">
            <i class="bi bi-star-fill me-2" style="color: gold;"></i>
            Attiva Swap+
          </h1>
          <p class="text-muted">Scegli il piano più adatto alle tue esigenze</p>
        </div>

        <!-- Piani Abbonamento -->
        <div class="row g-4 mb-4">
          
          <!-- Piano Mensile -->
          <div class="col-md-4">
            <div class="card plan-card h-100" onclick="selectPlan(30, this)">
              <div class="card-body text-center p-4">
                <h3 class="card-title mb-3">Mensile</h3>
                <div class="price mb-3">
                  <i class="bi bi-star-fill"></i>
                  <div>30</div>
                  <small style="font-size: 1rem; color: #666;">giorni</small>
                </div>
                <p class="text-muted mb-4">Perfetto per iniziare</p>
                <ul class="list-unstyled text-start">
                  <li class="mb-2"><i class="bi bi-check-circle-fill text-success me-2"></i>Scambi illimitati</li>
                  <li class="mb-2"><i class="bi bi-check-circle-fill text-success me-2"></i>Badge Premium</li>
                  <li class="mb-2"><i class="bi bi-check-circle-fill text-success me-2"></i>Priorità richieste</li>
                  <li class="mb-2"><i class="bi bi-check-circle-fill text-success me-2"></i>Supporto prioritario</li>
                </ul>
              </div>
            </div>
          </div>

          <!-- Piano Trimestrale -->
          <div class="col-md-4">
            <div class="card plan-card h-100" onclick="selectPlan(90, this)">
              <div class="card-body text-center p-4">
                <div class="best-value">POPOLARE</div>
                <h3 class="card-title mb-3">Trimestrale</h3>
                <div class="price mb-3">
                  <i class="bi bi-star-fill"></i>
                  <div>90</div>
                  <small style="font-size: 1rem; color: #666;">giorni</small>
                </div>
                <p class="text-muted mb-4">Il più scelto</p>
                <ul class="list-unstyled text-start">
                  <li class="mb-2"><i class="bi bi-check-circle-fill text-success me-2"></i>Scambi illimitati</li>
                  <li class="mb-2"><i class="bi bi-check-circle-fill text-success me-2"></i>Badge Premium</li>
                  <li class="mb-2"><i class="bi bi-check-circle-fill text-success me-2"></i>Priorità richieste</li>
                  <li class="mb-2"><i class="bi bi-check-circle-fill text-success me-2"></i>Supporto prioritario</li>
                  <li class="mb-2"><i class="bi bi-check-circle-fill text-success me-2"></i><strong>Spedizioni -20%</strong></li>
                </ul>
              </div>
            </div>
          </div>

          <!-- Piano Annuale -->
          <div class="col-md-4">
            <div class="card plan-card h-100" onclick="selectPlan(365, this)">
              <div class="card-body text-center p-4">
                <div class="best-value" style="background: linear-gradient(135deg, #f093fb 0%, #f5576c 100%);">MIGLIOR VALORE</div>
                <h3 class="card-title mb-3">Annuale</h3>
                <div class="price mb-3">
                  <i class="bi bi-star-fill"></i>
                  <div>365</div>
                  <small style="font-size: 1rem; color: #666;">giorni</small>
                </div>
                <p class="text-muted mb-4">Massimo risparmio</p>
                <ul class="list-unstyled text-start">
                  <li class="mb-2"><i class="bi bi-check-circle-fill text-success me-2"></i>Scambi illimitati</li>
                  <li class="mb-2"><i class="bi bi-check-circle-fill text-success me-2"></i>Badge Premium</li>
                  <li class="mb-2"><i class="bi bi-check-circle-fill text-success me-2"></i>Priorità richieste</li>
                  <li class="mb-2"><i class="bi bi-check-circle-fill text-success me-2"></i>Supporto prioritario</li>
                  <li class="mb-2"><i class="bi bi-check-circle-fill text-success me-2"></i><strong>Spedizioni -20%</strong></li>
                  <li class="mb-2"><i class="bi bi-check-circle-fill text-success me-2"></i><strong>Accesso anticipato</strong></li>
                </ul>
              </div>
            </div>
          </div>

        </div>

        <!-- Pulsante Attivazione -->
        <div class="text-center mb-4">
          <button id="btn-activate" class="btn btn-lg" style="background: linear-gradient(135deg, #667eea 0%, #764ba2 100%); color: white;" disabled>
            <i class="bi bi-star-fill me-2"></i>
            Attiva Abbonamento
          </button>
          <br>
          <small class="text-muted mt-2 d-block" id="selected-plan-text">Seleziona un piano</small>
        </div>

        <!-- Alert Successo -->
        <div id="alert-success" class="alert alert-success" style="display: none;">
          <h5><i class="bi bi-check-circle-fill me-2"></i>Abbonamento Attivato!</h5>
          <p class="mb-0" id="success-message"></p>
        </div>

        <!-- Alert Errore -->
        <div id="alert-error" class="alert alert-danger" style="display: none;">
          <i class="bi bi-exclamation-triangle me-2"></i>
          <span id="error-message"></span>
        </div>

        <!-- Alert Info (abbonamento esistente) -->
        <div id="alert-info" class="alert alert-info" style="display: none;">
          <h5><i class="bi bi-info-circle-fill me-2"></i>Hai già un abbonamento attivo</h5>
          <p class="mb-0" id="info-message"></p>
        </div>

        <!-- Pulsante Indietro -->
        <div class="text-center">
          <a href="/login/visualizzaUtente.php" class="btn btn-outline-secondary">
            <i class="bi bi-arrow-left me-1"></i>Torna alla Dashboard
          </a>
        </div>

      </div>
    </div>
  </main>

  <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/js/bootstrap.bundle.min.js"></script>
  
  <script>
    let selectedDuration = null;

    // Funzione per selezionare un piano
    function selectPlan(durataGiorni, cardElement) {
      // Rimuovi selezione precedente
      document.querySelectorAll('.plan-card').forEach(card => {
        card.classList.remove('selected');
      });
      
      // Aggiungi selezione corrente
      cardElement.classList.add('selected');
      selectedDuration = durataGiorni;
      
      // Abilita pulsante
      const btnActivate = document.getElementById('btn-activate');
      btnActivate.disabled = false;
      
      // Aggiorna testo
      const planText = document.getElementById('selected-plan-text');
      const nomiPiani = {
        30: 'Piano Mensile (30 giorni)',
        90: 'Piano Trimestrale (90 giorni)',
        365: 'Piano Annuale (365 giorni)'
      };
      planText.textContent = 'Piano selezionato: ' + nomiPiani[durataGiorni];
    }

    // Gestione click pulsante attivazione
    document.getElementById('btn-activate').addEventListener('click', function() {
      if(!selectedDuration) {
        alert('Seleziona prima un piano!');
        return;
      }
      
      const btnActivate = this;
      btnActivate.disabled = true;
      btnActivate.innerHTML = '<span class="spinner-border spinner-border-sm me-2"></span>Attivazione in corso...';
      
      // Nascondi alert precedenti
      document.getElementById('alert-success').style.display = 'none';
      document.getElementById('alert-error').style.display = 'none';
      document.getElementById('alert-info').style.display = 'none';
      
      // Chiamata fetch all api per in caso creare nuovo abbonamento
      fetch('/login/api/swapper/api_subscribe_swapplus.php', {
        method: 'POST',
        headers: { 'Content-Type': 'application/json' },
        credentials: 'include',
        body: JSON.stringify({ durataGiorni: selectedDuration })
      })
        .then(res => res.json())
        .then(data => {
          btnActivate.innerHTML = '<i class="bi bi-star-fill me-2"></i>Attiva Abbonamento';
          
          if(data.success) {
            // Successo!
            document.getElementById('alert-success').style.display = 'block';
            document.getElementById('success-message').innerHTML = 
              `${data.message}<br>` +
              `<strong>Data inizio:</strong> ${new Date(data.abbonamento.dataInizio).toLocaleDateString('it-IT')}<br>` +
              `<strong>Data scadenza:</strong> ${new Date(data.abbonamento.dataFine).toLocaleDateString('it-IT')}<br>` +
              `<strong>Durata:</strong> ${data.abbonamento.durataGiorni} giorni`;
            
            // Disabilita selezione piani
            document.querySelectorAll('.plan-card').forEach(card => {
              card.onclick = null;
              card.style.opacity = '0.5';
              card.style.cursor = 'not-allowed';
            });
            
            // Dopo 3 secondi, redirect a view_swapplus
            setTimeout(() => {
              window.location.href = '/login/mockup_manager.php?azione=view_own_swapplus';
            }, 3000);
            
          } else {
            // Errore
            if(data.abbonamentoEsistente) {
              // Ha già un abbonamento
              document.getElementById('alert-info').style.display = 'block';
              document.getElementById('info-message').innerHTML = 
                `${data.error}<br>` +
                `<strong>Giorni rimanenti:</strong> ${data.abbonamentoEsistente.giorniRimanenti}`;
              btnActivate.style.display = 'none';
            } else {
              // Altro errore
              document.getElementById('alert-error').style.display = 'block';
              document.getElementById('error-message').textContent = data.error;
              btnActivate.disabled = false;
            }
          }
        })
        .catch(err => {
          console.error(err);
          btnActivate.disabled = false;
          btnActivate.innerHTML = '<i class="bi bi-star-fill me-2"></i>Attiva Abbonamento';
          document.getElementById('alert-error').style.display = 'block';
          document.getElementById('error-message').textContent = 'Errore di connessione';
        });
    });
  </script>
</body>
</html>