<!doctype html>
<html lang="it">
<head>
  <title>Invia Richiesta di Amicizia - SwapHub</title>
  <meta charset="utf-8" />
  <meta name="viewport" content="width=device-width, initial-scale=1" />
  <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet" />
  <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.1/font/bootstrap-icons.css">
  <style>
    #users-list-container {
      max-height: 600px;
      overflow-y: auto;
      border: 1px solid #dee2e6;
      border-radius: 0.375rem;
    }

    .user-list-item {
      padding: 0.75rem;
      border-bottom: 1px solid #e9ecef;
      display: flex;
      align-items: center;
      gap: 0.75rem;
      transition: background-color 0.15s;
    }

    .user-list-item:hover {
      background-color: #f8f9fa;
    }

    .user-list-item:last-child {
      border-bottom: none;
    }

    .user-avatar-small {
      width: 45px;
      height: 45px;
      border-radius: 50%;
      object-fit: cover;
      flex-shrink: 0;
    }

    .user-info {
      flex-grow: 1;
      min-width: 0;
      overflow: hidden;
    }

    .user-info strong {
      display: block;
      overflow: hidden;
      text-overflow: ellipsis;
      white-space: nowrap;
    }

    .user-info small {
      display: block;
      overflow: hidden;
      text-overflow: ellipsis;
      white-space: nowrap;
    }

    .filter-box {
      position: sticky;
      top: 0;
      background: white;
      z-index: 10;
      padding-bottom: 0.5rem;
    }

    .action-bar {
      display: flex;
      gap: 0.5rem;
      flex-wrap: wrap;
      margin-bottom: 1rem;
    }

    .badge-count {
      background-color: #667eea;
      color: white;
      padding: 0.25rem 0.75rem;
      border-radius: 1rem;
      font-size: 0.875rem;
    }

    .no-results {
      text-align: center;
      padding: 2rem 1rem;
      color: #6c757d;
    }
  </style>
</head>
<body style="background-color: #f8f9fa;">
  <main class="container py-4 py-md-5">
    <div class="row justify-content-center">
      <div class="col-12 col-md-8">
        
        <div class="bg-white p-4 rounded shadow-sm" style="border-top: 5px solid #667eea;">
          
          <div class="d-flex justify-content-between align-items-center mb-4 flex-wrap gap-2">
            <h1 class="fw-bold mb-0">
              <i class="bi bi-person-plus me-2"></i>
              Aggiungi Amici
            </h1>
            <a href="/login/visualizzaUtente.php" class="btn btn-secondary btn-sm">
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
          <div id="users-list-wrapper" style="display: none;">
            
            <!-- Barra azioni -->
            <div class="action-bar">
              <button type="button" class="btn btn-sm btn-outline-secondary" id="btn-select-all">
                <i class="bi bi-check2-all me-1"></i>Seleziona tutti
              </button>
              <button type="button" class="btn btn-sm btn-outline-secondary" id="btn-deselect-all">
                <i class="bi bi-x-circle me-1"></i>Deseleziona
              </button>
              <div class="badge-count ms-auto">
                <span id="selected-count">0</span> / <span id="total-users">0</span>
              </div>
            </div>

            <!-- Filtro ricerca -->
            <div class="mb-3">
              <input type="text" class="form-control form-control-sm" id="search-input" 
                     placeholder="🔍 Cerca per nome o username...">
              <small class="text-muted">Digita per filtrare la lista</small>
            </div>

            <!-- Contenitore con scroll -->
            <div id="users-list-container"></div>

            <!-- Nessun risultato ricerca -->
            <div id="no-results" class="no-results" style="display: none;">
              <i class="bi bi-search" style="font-size: 2rem;"></i>
              <p class="mt-2 mb-0">Nessun utente trovato</p>
            </div>

            <!-- Pulsanti azione -->
            <div class="mt-3 d-grid gap-2">
              <button type="button" class="btn btn-primary" id="btn-add-selected" disabled>
                <i class="bi bi-person-plus-fill me-1"></i>Aggiungi selezionati
              </button>
              <button type="button" class="btn btn-outline-secondary" onclick="window.location.href='/login/visualizzaUtente.php'">
                Annulla
              </button>
            </div>
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
      </div>
    </div>
  </main>

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
    let usersData = [];
    let selectedUsers = new Set();

    function updateCounters() {
      document.getElementById('selected-count').textContent = selectedUsers.size;
      
      // Abilita/disabilita bottone "Aggiungi selezionati"
      const btnAdd = document.getElementById('btn-add-selected');
      if(selectedUsers.size > 0) {
        btnAdd.disabled = false;
      } else {
        btnAdd.disabled = true;
      }
    }
    
    function createUserListItem(user) {
      const div = document.createElement('div');
      div.className = 'user-list-item';
      div.id = `user-item-${user.username}`;
      
      div.innerHTML = `
        <input type="checkbox" class="form-check-input user-checkbox" 
               value="${user.username}" 
               data-nome="${user.Nome} ${user.Cognome}">
        
        <img src="/login/${user.fotoprofilo}" 
             alt="${user.Nome}" 
             class="user-avatar-small"
             onerror="this.src='/login/IMG/noimage.jpg'">
        
        <div class="user-info">
          <strong>${user.Nome} ${user.Cognome}</strong>
          <small class="text-muted">@${user.username}</small>
          ${user.localita ? `<small class="text-muted"><i class="bi bi-geo-alt"></i> ${user.localita}</small>` : ''}
        </div>
      `;
      
      return div;
    }

    // ===== FUNZIONE: Filtra lista =====
    function filterUsers(query) {
      const container = document.getElementById('users-list-container');
      const noResults = document.getElementById('no-results');
      
      const searchTerm = query.toLowerCase();
      let visibleCount = 0;
      
      usersData.forEach(user => {
        const itemDiv = document.getElementById(`user-item-${user.username}`);
        if(!itemDiv) return;
        
        const nome = user.Nome.toLowerCase();
        const cognome = user.Cognome.toLowerCase();
        const username = user.username.toLowerCase();
        
        const match = nome.includes(searchTerm) || 
                      cognome.includes(searchTerm) || 
                      username.includes(searchTerm);
        
        if(match) {
          itemDiv.style.display = '';
          visibleCount++;
        } else {
          itemDiv.style.display = 'none';
        }
      });
      
      if(visibleCount === 0) {
        noResults.style.display = 'block';
      } else {
        noResults.style.display = 'none';
      }
    }

    // ===== FUNZIONE: Seleziona tutti (visibili) =====
    function selectAllVisible() {
      document.querySelectorAll('.user-checkbox:not([style*="display: none"])').forEach(checkbox => {
        checkbox.parentElement.offsetParent !== null && (checkbox.checked = true);
      });
      updateAllCheckboxes();
    }

    // ===== FUNZIONE: Deseleziona tutti =====
    function deselectAll() {
      document.querySelectorAll('.user-checkbox').forEach(checkbox => {
        checkbox.checked = false;
      });
      updateAllCheckboxes();
    }

    // ===== FUNZIONE: Aggiorna lista quando checkbox cambia =====
    function updateAllCheckboxes() {
      selectedUsers.clear();
      document.querySelectorAll('.user-checkbox:checked').forEach(checkbox => {
        selectedUsers.add(checkbox.value);
      });
      updateCounters();
    }

    // ===== FUNZIONE: Invia richieste batch =====
    async function sendBatchRequests() {
      if(selectedUsers.size === 0) {
        alert('Seleziona almeno un utente');
        return;
      }

      const btnAdd = document.getElementById('btn-add-selected');
      const originalText = btnAdd.innerHTML;
      btnAdd.disabled = true;
      btnAdd.innerHTML = '<span class="spinner-border spinner-border-sm me-1"></span>Invio...';

      const usernames = Array.from(selectedUsers);
      let successCount = 0;
      let errorCount = 0;

      for(const username of usernames) {
        try {
          const response = await fetch('/login/api/swapper/api_send_friend_request.php', {
            method: 'POST',
            headers: { 'Content-Type': 'application/json' },
            credentials: 'include',
            body: JSON.stringify({ userRicevente: username })
          });

          const data = await response.json();
          
          if(data.success) {
            successCount++;
            // Rimuovi checkbox da lista visiva
            const itemDiv = document.getElementById(`user-item-${username}`);
            if(itemDiv) {
              itemDiv.style.opacity = '0.5';
              itemDiv.style.pointerEvents = 'none';
            }
          } else {
            errorCount++;
          }
        } catch(err) {
          console.error('Errore:', err);
          errorCount++;
        }
      }

      // Mostra risultato
      btnAdd.disabled = false;
      btnAdd.innerHTML = originalText;

      const toastSuccess = new bootstrap.Toast(document.getElementById('toast-success'));
      document.getElementById('toast-message').textContent = 
        `✅ ${successCount} richiesta${successCount !== 1 ? 'e' : ''} inviata${successCount !== 1 ? 'e' : ''}!` +
        (errorCount > 0 ? ` (${errorCount} errore${errorCount !== 1 ? 'i' : ''})` : '');
      toastSuccess.show();

      // Aggiorna UI
      selectedUsers.clear();
      updateCounters();
      setTimeout(() => {
        window.location.href = '/login/visualizzaUtente.php';
      }, 2000);
    }

    // ===== EVENT LISTENERS =====
    document.addEventListener('DOMContentLoaded', function() {
      // Carica utenti
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
              usersData = data.utenti;
              document.getElementById('users-list-wrapper').style.display = 'block';
              document.getElementById('total-users').textContent = data.totalUtenti;
              
              const container = document.getElementById('users-list-container');
              
              data.utenti.forEach(user => {
                const item = createUserListItem(user);
                // Aggiungi event listener al checkbox
                item.querySelector('.user-checkbox').addEventListener('change', updateAllCheckboxes);
                container.appendChild(item);
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

      // Event: Filtro ricerca
      document.getElementById('search-input').addEventListener('input', function(e) {
        filterUsers(e.target.value);
      });

      // Event: Seleziona tutti
      document.getElementById('btn-select-all').addEventListener('click', selectAllVisible);

      // Event: Deseleziona tutti
      document.getElementById('btn-deselect-all').addEventListener('click', deselectAll);

      // Event: Aggiungi selezionati
      document.getElementById('btn-add-selected').addEventListener('click', sendBatchRequests);
    });
  </script>
</body>
</html>