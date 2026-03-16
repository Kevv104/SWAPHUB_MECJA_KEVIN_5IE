<!doctype html>
<html lang="it">
<head>
  <title>Invia Richiesta di Amicizia - SwapHub</title>
  <meta charset="utf-8" />
  <meta name="viewport" content="width=device-width, initial-scale=1" />
  <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet" />
  <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.1/font/bootstrap-icons.css">
  <style>
    .user-card {
      transition: all 0.2s;
      cursor: pointer;
    }
    .user-card:hover {
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
              <i class="bi bi-person-plus me-2"></i>
              Aggiungi Amici
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
            <p class="mt-2 text-muted">Caricamento utenti...</p>
          </div>

          <!-- Lista Utenti -->
          <div id="users-list" style="display: none;">
            <div class="mb-3">
              <span class="text-muted">
                Utenti disponibili: <strong id="total-users">0</strong>
              </span>
            </div>
            
            <div id="users-container" class="row g-3"></div>
          </div>

          <!-- Nessun Utente -->
          <div id="no-users" class="text-center py-5" style="display: none;">
            <i class="bi bi-people" style="font-size: 4rem; color: #ccc;"></i>
            <h3 class="mt-3">Nessun utente disponibile</h3>
            <p class="text-muted">Sei già amico con tutti gli utenti della piattaforma!</p>
          </div>

          <!-- Errore -->
          <div id="error-container" class="alert alert-danger" style="display: none;">
            <i class="bi bi-exclamation-triangle me-2"></i>
            <span id="error-message"></span>
          </div>

        </div>

        <!-- Alert Successo (toast) -->
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

        <!-- Alert Errore (toast) -->
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
    // Funzione per inviare richiesta
    function sendRequest(username, nomeCognome, buttonElement) {
      buttonElement.disabled = true;
      buttonElement.innerHTML = '<span class="spinner-border spinner-border-sm me-1"></span>Invio...';
      
      fetch('/login/api/swapper/api_send_friend_request.php', {
        method: 'POST',
        headers: { 'Content-Type': 'application/json' },
        credentials: 'include',
        body: JSON.stringify({ userRicevente: username })
      })
        .then(res => res.json())
        .then(data => {
          if(data.success) {
            // Successo - Mostra toast e rimuovi card
            const toastSuccess = new bootstrap.Toast(document.getElementById('toast-success'));
            document.getElementById('toast-message').textContent = 
              `Richiesta inviata a ${nomeCognome}!`;
            toastSuccess.show();
            
            // Rimuovi la card dell'utente
            buttonElement.closest('.col-md-6').remove();
            
            // Aggiorna contatore
            const totalUsers = parseInt(document.getElementById('total-users').textContent);
            document.getElementById('total-users').textContent = totalUsers - 1;
            
            // Se non ci sono più utenti, mostra messaggio
            if(totalUsers - 1 === 0) {
              document.getElementById('users-list').style.display = 'none';
              document.getElementById('no-users').style.display = 'block';
            }
            
          } else {
            // Errore
            const toastError = new bootstrap.Toast(document.getElementById('toast-error'));
            document.getElementById('toast-error-message').textContent = data.error;
            toastError.show();
            
            buttonElement.disabled = false;
            buttonElement.innerHTML = '<i class="bi bi-person-plus me-1"></i>Aggiungi';
          }
        })
        .catch(err => {
          console.error(err);
          const toastError = new bootstrap.Toast(document.getElementById('toast-error'));
          document.getElementById('toast-error-message').textContent = 'Errore di connessione';
          toastError.show();
          
          buttonElement.disabled = false;
          buttonElement.innerHTML = '<i class="bi bi-person-plus me-1"></i>Aggiungi';
        });
    }

    // Carica lista utenti
    fetch('/login/api/swapper/api_get_non_friends.php', {
      credentials: 'include'
    })
      .then(res => res.json())
      .then(data => {
        document.getElementById('loading').style.display = 'none';
        
        if(data.success) {
          if(data.utenti.length === 0) {
            document.getElementById('no-users').style.display = 'block';
          } else {
            document.getElementById('users-list').style.display = 'block';
            document.getElementById('total-users').textContent = data.totalUtenti;
            
            const container = document.getElementById('users-container');
            
            data.utenti.forEach(user => {
              const userCard = document.createElement('div');
              userCard.className = 'col-md-6';
              
              userCard.innerHTML = `
                <div class="card user-card h-100">
                  <div class="card-body">
                    <div class="d-flex align-items-center">
                      <img src="/login/${user.fotoprofilo}" 
                           alt="${user.Nome}" 
                           class="user-avatar me-3"
                           onerror="this.src='/login/IMG/noimage.jpg'">
                      
                      <div class="flex-grow-1">
                        <h5 class="mb-1">${user.Nome} ${user.Cognome}</h5>
                        <p class="text-muted small mb-1">@${user.username}</p>
                        ${user.localita ? `<p class="text-muted small mb-0">
                          <i class="bi bi-geo-alt"></i> ${user.localita}
                        </p>` : ''}
                      </div>
                      
                      <button class="btn btn-primary btn-sm" 
                              onclick="sendRequest('${user.username}', '${user.Nome} ${user.Cognome}', this)">
                        <i class="bi bi-person-plus me-1"></i>Aggiungi
                      </button>
                    </div>
                  </div>
                </div>
              `;
              
              container.appendChild(userCard);
            });
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