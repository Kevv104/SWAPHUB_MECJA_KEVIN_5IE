<!doctype html>
<html lang="it">
<head>
  <title>Richieste di Amicizia - SwapHub</title>
  <meta charset="utf-8" />
  <meta name="viewport" content="width=device-width, initial-scale=1" />
  <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet" />
  <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.1/font/bootstrap-icons.css">
  <style>
    .request-card {
      transition: all 0.2s;
    }
    .request-card:hover {
      transform: translateY(-3px);
      box-shadow: 0 5px 15px rgba(0,0,0,0.1);
    }
    .user-avatar {
      width: 60px;
      height: 60px;
      border-radius: 50%;
      object-fit: cover;
    }
  </style>
</head>
<body style="background-color: #f8f9fa;">
  <main class="container py-5">
    <div class="row justify-content-center">
      <div class="col-md-10">
        
        <div class="bg-white p-4 rounded shadow-sm" style="border-top: 5px solid #667eea;">
          
          <div class="d-flex justify-content-between align-items-center mb-4">
            <h1 class="fw-bold">
              <i class="bi bi-person-check me-2"></i>
              Richieste di Amicizia
            </h1>
            <a href="/login/visualizzaUtente.php" class="btn btn-secondary">
              <i class="bi bi-arrow-left me-1"></i>Indietro
            </a>
          </div>

          <!-- Loading -->
          <div id="loading" class="text-center py-5">
            <div class="spinner-border text-primary" role="status">
              <span class="visually-hidden">Caricamento...</span>
            </div>
            <p class="mt-2 text-muted">Caricamento richieste...</p>
          </div>

          <!-- Lista Richieste -->
          <div id="requests-list" style="display: none;">
            <div class="mb-3">
              <span class="text-muted">
                Richieste in attesa: <strong id="total-requests">0</strong>
              </span>
            </div>
            
            <div id="requests-container"></div>
          </div>

          <!-- Nessuna Richiesta -->
          <div id="no-requests" class="text-center py-5" style="display: none;">
            <i class="bi bi-inbox" style="font-size: 4rem; color: #ccc;"></i>
            <h3 class="mt-3">Nessuna richiesta</h3>
            <p class="text-muted">Non hai richieste di amicizia in attesa</p>
          </div>

          <!-- Errore -->
          <div id="error-container" class="alert alert-danger" style="display: none;">
            <i class="bi bi-exclamation-triangle me-2"></i>
            <span id="error-message"></span>
          </div>

        </div>

        <!-- Toast Successo -->
        <div class="position-fixed bottom-0 end-0 p-3" style="z-index: 11">
          <div id="toast-success" class="toast hide" role="alert">
            <div class="toast-header bg-success text-white">
              <i class="bi bi-check-circle-fill me-2"></i>
              <strong class="me-auto">Successo</strong>
              <button type="button" class="btn-close btn-close-white" data-bs-dismiss="toast"></button>
            </div>
            <div class="toast-body" id="toast-message"></div>
          </div>
        </div>

        <!-- Toast Errore -->
        <div class="position-fixed bottom-0 end-0 p-3" style="z-index: 11">
          <div id="toast-error" class="toast hide" role="alert">
            <div class="toast-header bg-danger text-white">
              <i class="bi bi-exclamation-triangle me-2"></i>
              <strong class="me-auto">Errore</strong>
              <button type="button" class="btn-close btn-close-white" data-bs-dismiss="toast"></button>
            </div>
            <div class="toast-body" id="toast-error-message"></div>
          </div>
        </div>

      </div>
    </div>
  </main>

  <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/js/bootstrap.bundle.min.js"></script>
  
  <script>
    /**
     * Formatta la data in modo relativo (es. "5 min fa", "2 ore fa")
     */
    function formatDate(dateString) {
        if (!dateString) return '';
        const date = new Date(dateString);
        const now = new Date();
        const diffMs = now - date;
        const diffMins = Math.floor(diffMs / 60000);
        const diffHours = Math.floor(diffMs / 3600000);
        const diffDays = Math.floor(diffMs / 86400000);

        if (diffMins < 1) return 'Ora';
        if (diffMins < 60) return diffMins + ' min fa';
        if (diffHours < 24) return diffHours + ' ore fa';
        if (diffDays < 7) return diffDays + ' giorni fa';

        return date.toLocaleDateString('it-IT', {
            day: 'numeric',
            month: 'short',
            year: 'numeric'
        });
    }

    /**
     * Funzione per ACCETTARE una richiesta
     */
    function acceptRequest(idRichiesta, nomeCognome, cardElement) {
        const btnAccept = cardElement.querySelector('.btn-accept');
      const btnReject = cardElement.querySelector('.btn-reject');

        btnAccept.disabled = true;
      btnReject.disabled = true;
        btnAccept.innerHTML = '<span class="spinner-border spinner-border-sm me-1"></span>...';

        fetch('/login/api/swapper/api_accept_friend_request.php', {
            method: 'POST',
            headers: { 'Content-Type': 'application/json' },
            credentials: 'include',
            body: JSON.stringify({ idRichiesta: idRichiesta })
        })
        .then(res => res.json())
        .then(data => {
            if (data.success) {
                const toastSuccess = new bootstrap.Toast(document.getElementById('toast-success'));
                document.getElementById('toast-message').textContent = `Ora sei amico con ${nomeCognome}!`;
                toastSuccess.show();
                cardElement.remove();
                updateCounter();
            } else {
                throw new Error(data.error || 'Errore durante l\'accettazione');
            }
        })
        .catch(err => {
            alert(err.message);
            btnAccept.disabled = false;
          btnReject.disabled = false;
            btnAccept.innerHTML = '<i class="bi bi-check-circle me-1"></i>Accetta';
        });
    }

      /**
       * Funzione per RIFIUTARE una richiesta
       */
      function rejectRequest(idRichiesta, nomeCognome, cardElement) {
        if (!confirm(`Vuoi davvero rifiutare la richiesta di ${nomeCognome}?`)) return;

        const btnAccept = cardElement.querySelector('.btn-accept');
        const btnReject = cardElement.querySelector('.btn-reject');

        btnAccept.disabled = true;
        btnReject.disabled = true;

        fetch('/login/api/swapper/api_reject_friend_request.php', {
          method: 'POST',
          headers: { 'Content-Type': 'application/json' },
          credentials: 'include',
          body: JSON.stringify({ idRichiesta: idRichiesta })
        })
        .then(res => res.json())
        .then(data => {
          if (data.success) {
            cardElement.remove();
            updateCounter();
          } else {
            alert("Errore: " + data.error);
            btnAccept.disabled = false;
            btnReject.disabled = false;
          }
        })
        .catch(err => console.error("Errore di rete:", err));
      }

    /**
     * Aggiorna il contatore visivo delle richieste
     */
    function updateCounter() {
        const container = document.getElementById('requests-container');
        const count = container.children.length;
        document.getElementById('total-requests').textContent = count;
        if (count === 0) {
            document.getElementById('requests-list').style.display = 'none';
            document.getElementById('no-requests').style.display = 'block';
        }
    }

    /**
     * Caricamento INIZIALE delle richieste
     */
    fetch('/login/api/swapper/api_get_pending_requests.php', {
        credentials: 'include'
    })
    .then(res => res.json())
    .then(data => {
        document.getElementById('loading').style.display = 'none';

        if (data.success) {
            if (data.richieste.length === 0) {
                document.getElementById('no-requests').style.display = 'block';
            } else {
                document.getElementById('requests-list').style.display = 'block';
                document.getElementById('total-requests').textContent = data.totalRichieste;
                const container = document.getElementById('requests-container');

                data.richieste.forEach(req => {
                    const requestCard = document.createElement('div');
                    requestCard.className = 'card request-card mb-3';
                    
                    // Gestione dinamica percorso immagine
                    const fotoPath = req.fotoprofilo.startsWith('uploads') ? `/login/${req.fotoprofilo}` : req.fotoprofilo;

                    requestCard.innerHTML = `
                        <div class="card-body">
                            <div class="d-flex align-items-start">
                                <img src="${fotoPath}" 
                                     alt="${req.Nome}" 
                                     class="user-avatar me-3"
                                     onerror="this.src='/login/uploads/profile/default.png'">
                                
                                <div class="flex-grow-1">
                                    <h5 class="mb-1">${req.Nome} ${req.Cognome}</h5>
                                    <p class="text-muted small mb-1">@${req.userMittente}</p>
                                    ${req.localita ? `<p class="text-muted small mb-2"><i class="bi bi-geo-alt"></i> ${req.localita}</p>` : ''}
                                    ${req.commento ? `<p class="mb-2"><i class="bi bi-chat-quote"></i> <em>"${req.commento}"</em></p>` : ''}
                                    <small class="text-muted">
                                        <i class="bi bi-clock"></i> ${formatDate(req.dataInvio)}
                                    </small>
                                </div>
                                
                                <div class="d-flex gap-2">
                                    <button class="btn btn-success btn-sm btn-accept" 
                                            onclick="acceptRequest(${req.idRichiesta}, '${req.Nome} ${req.Cognome}', this.closest('.card'))">
                                        <i class="bi bi-check-circle me-1"></i>Accetta
                                    </button>
                                  <button class="btn btn-outline-danger btn-sm btn-reject" 
                                      onclick="rejectRequest(${req.idRichiesta}, '${req.Nome} ${req.Cognome}', this.closest('.card'))">
                                    <i class="bi bi-x-circle me-1"></i>Rifiuta
                                  </button>
                                </div>
                            </div>
                        </div>
                    `;
                    container.appendChild(requestCard);
                });
            }
        } else {
            document.getElementById('error-container').style.display = 'block';
            document.getElementById('error-message').textContent = data.error || 'Errore nel caricamento';
        }
    })
    .catch(err => {
        document.getElementById('loading').style.display = 'none';
        document.getElementById('error-container').style.display = 'block';
        document.getElementById('error-message').textContent = 'Errore di connessione al server.';
    });
</script>
</body>
</html>