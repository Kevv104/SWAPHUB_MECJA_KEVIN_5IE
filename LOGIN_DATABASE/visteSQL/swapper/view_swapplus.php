<!doctype html>
<html lang="it">
<head>
  <title>Il Mio Swap+ - SwapHub</title>
  <meta charset="utf-8" />
  <meta name="viewport" content="width=device-width, initial-scale=1" />
  <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet" />
  <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.1/font/bootstrap-icons.css">
</head>
<body style="background-color: #f8f9fa;">
  <main class="container py-5">
    <div class="row justify-content-center">
      <div class="col-md-8 bg-white p-4 rounded shadow-sm" style="border-top: 5px solid #667eea;">
        
        <div class="d-flex justify-content-between align-items-center mb-4">
          <h1 class="fw-bold"><i class="bi bi-star-fill me-2" style="color: gold;"></i>Il Mio Swap+</h1>
          <a href="/login/visualizzaUtente.php" class="btn btn-secondary">
            <i class="bi bi-arrow-left me-1"></i>Indietro
          </a>
        </div>

        <!-- Loading -->
        <div id="loading" class="text-center py-4">
          <div class="spinner-border text-primary" role="status">
            <span class="visually-hidden">Caricamento...</span>
          </div>
          <p class="mt-2 text-muted">Caricamento abbonamento...</p>
        </div>

        <!-- Con Abbonamento -->
        <div id="with-subscription" style="display: none;">
          
          <!-- Alert Stato -->
          <div class="alert" id="status-alert" role="alert">
            <h5 class="mb-0">
              <i class="bi bi-check-circle-fill me-2"></i>
              Abbonamento <span id="status-text">Attivo</span>
            </h5>
          </div>

          <!-- Dettagli -->
          <div class="card mb-3">
            <div class="card-body">
              <h5 class="card-title">Dettagli Abbonamento</h5>
              <table class="table table-borderless mb-0">
                <tr>
                  <td class="text-muted" style="width: 40%;">Data Inizio</td>
                  <td class="fw-bold" id="data-inizio">--</td>
                </tr>
                <tr>
                  <td class="text-muted">Data Scadenza</td>
                  <td class="fw-bold" id="data-fine">--</td>
                </tr>
                <tr>
                  <td class="text-muted">Giorni Rimanenti</td>
                  <td class="fw-bold" id="giorni-rimanenti">--</td>
                </tr>
              </table>
            </div>
          </div>

          <!-- Vantaggi -->
          <div class="card">
            <div class="card-body">
              <h5 class="card-title">Vantaggi Attivi</h5>
              <ul class="mb-0">
                <li>Scambi illimitati</li>
                <li>Supporto prioritario</li>
                <li>Badge Premium</li>
                <li>Spedizioni scontate (20%)</li>
              </ul>
            </div>
          </div>
        </div>

        <!-- Senza Abbonamento -->
        <div id="without-subscription" class="text-center py-5" style="display: none;">
          <i class="bi bi-star" style="font-size: 4rem; color: gold;"></i>
          <h3 class="mt-3">Non hai un abbonamento Swap+</h3>
          <p class="text-muted">Attiva Swap+ per sbloccare vantaggi esclusivi</p>
        </div>

        <!-- Errore -->
        <div id="error-container" class="alert alert-danger" style="display: none;">
          <i class="bi bi-exclamation-triangle me-2"></i>
          <span id="error-message"></span>
        </div>

      </div>
    </div>
  </main>

  <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/js/bootstrap.bundle.min.js"></script>
  
  <script>
    function formatDate(dateString) {
      const date = new Date(dateString);
      return date.toLocaleDateString('it-IT', { 
        year: 'numeric', 
        month: 'long', 
        day: 'numeric' 
      });
    }

    // fetch per caricare dati utente
    fetch('/login/api/swapper/api_get_swapplus.php', {
      credentials: 'include'
    })
      .then(res => res.json())
      .then(data => { 
        document.getElementById('loading').style.display = 'none';
        
        if(data.success) {
          if(data.hasAbbonamento) {
            const abb = data.abbonamento;
            
            document.getElementById('data-inizio').textContent = formatDate(abb.dataInizio);
            document.getElementById('data-fine').textContent = formatDate(abb.dataFine);
            document.getElementById('giorni-rimanenti').textContent = abb.giorniRimanenti + ' giorni';
            
            const statusAlert = document.getElementById('status-alert');
            const statusText = document.getElementById('status-text');
            
            if(abb.statoAbbonamento === 'scaduto') {
              statusAlert.className = 'alert alert-danger';
              statusText.textContent = 'Scaduto';
            } else if(abb.statoAbbonamento === 'in_scadenza') {
              statusAlert.className = 'alert alert-warning';
              statusText.textContent = 'In Scadenza';
            } else {
              statusAlert.className = 'alert alert-success';
              statusText.textContent = 'Attivo';
            }
            
            document.getElementById('with-subscription').style.display = 'block';
          } else {
            document.getElementById('without-subscription').style.display = 'block';
          }
        } else {
          document.getElementById('error-container').style.display = 'block';
          document.getElementById('error-message').textContent = data.error || 'Errore nel caricamento';
        }
      })
      .catch(err => {
        console.error(err);
        document.getElementById('loading').style.display = 'none';
        document.getElementById('error-container').style.display = 'block';
        document.getElementById('error-message').textContent = 'Errore di connessione';
      });
  </script>
</body>
</html>