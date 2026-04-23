<?php
require_once __DIR__ . '/../../sessione.php';
require_once __DIR__ . '/../../config/TenantManager.php';

if (!isset($_SESSION['name']) || !isset($_SESSION['tenant_id'])) {
  header('Location: /login/index.php?errore=SessioneScaduta');
  exit;
}

$tenantInfo = TenantManager::get_current_tenant();
$tenantLabel = $tenantInfo['city'] ?? ('Tenant #' . (int)$_SESSION['tenant_id']);
?>

<!doctype html>
<html lang="it">
<head>
  <title>Le Mie Chat - SwapHub</title>
  <meta charset="utf-8" />
  <meta name="viewport" content="width=device-width, initial-scale=1" />
  <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet" />
  <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.1/font/bootstrap-icons.css">
  <style>
    .chat-item {
      cursor: pointer;
      transition: all 0.2s;
    }
    .chat-item:hover {
      background-color: #f8f9fa;
      transform: translateX(5px);
    }
    .badge-tipo {
      font-size: 0.75rem;
    }
  </style>
</head>
<body style="background-color: #f8f9fa;">
  <main class="container py-5">
    <div class="row justify-content-center">
      <div class="col-md-10 bg-white p-4 rounded shadow-sm" style="border-top: 5px solid #667eea;">
        
        <div class="d-flex justify-content-between align-items-center mb-4">
          <div>
            <h1 class="fw-bold mb-1"><i class="bi bi-chat-dots me-2"></i>Le Mie Chat</h1>
            <span class="badge text-bg-light border">Tenant attivo: <?= htmlspecialchars($tenantLabel); ?></span>
          </div>
          <a href="/login/visualizzaUtente.php" class="btn btn-secondary">
            <i class="bi bi-arrow-left me-1"></i>Indietro
          </a>
        </div>

        <!-- Loading -->
        <div id="loading" class="text-center py-5">
          <div class="spinner-border text-primary" role="status">
            <span class="visually-hidden">Caricamento...</span>
          </div>
          <p class="mt-2 text-muted">Caricamento chat...</p>
        </div>

        <!-- Lista Chat -->
        <div id="chat-list" style="display: none;">
          <div class="mb-3">
            <span class="text-muted">Totale: <strong id="total-chats">0</strong> chat</span>
          </div>
          
          <div id="chats-container"></div>
        </div>

        <!-- Nessuna Chat -->
        <div id="no-chats" class="text-center py-5" style="display: none;">
          <i class="bi bi-chat-square-text" style="font-size: 4rem; color: #ccc;"></i>
          <h3 class="mt-3">Nessuna chat disponibile</h3>
          <p class="text-muted">Non hai ancora partecipato a nessuna chat</p>
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
    // Funzione per formattare le date
    function formatDate(dateString) {
      if(!dateString) return 'Mai';
      
      const date = new Date(dateString);
      const now = new Date();
      const diffMs = now - date;
      const diffMins = Math.floor(diffMs / 60000);
      const diffHours = Math.floor(diffMs / 3600000);
      const diffDays = Math.floor(diffMs / 86400000);
      
      if(diffMins < 1) return 'Ora';
      if(diffMins < 60) return diffMins + ' min fa';
      if(diffHours < 24) return diffHours + ' ore fa';
      if(diffDays < 7) return diffDays + ' giorni fa';
      
      return date.toLocaleDateString('it-IT', { 
        day: 'numeric',
        month: 'short'
      });
    }

    // Funzione per ottenere badge tipo chat
    function getTipoBadge(tipo) {
      const badges = {
        'privata': '<span class="badge bg-primary badge-tipo"><i class="bi bi-person me-1"></i>Privata</span>',
        'gruppo': '<span class="badge bg-success badge-tipo"><i class="bi bi-people me-1"></i>Gruppo</span>',
        'scambio': '<span class="badge bg-warning text-dark badge-tipo"><i class="bi bi-arrow-left-right me-1"></i>Scambio</span>'
      };
      return badges[tipo] || '<span class="badge bg-secondary badge-tipo">' + tipo + '</span>';
    }

    // Funzione per ottenere badge stato
    function getStatoBadge(stato) {
      const badges = {
        'attiva': '<span class="badge bg-success">Attiva</span>',
        'archiviata': '<span class="badge bg-secondary">Archiviata</span>',
        'chiusa': '<span class="badge bg-danger">Chiusa</span>'
      };
      return badges[stato] || '<span class="badge bg-secondary">' + stato + '</span>';
    }

    // Funzione per troncare testo
    function truncate(str, maxLength) {
      if(!str) return '';
      return str.length > maxLength ? str.substring(0, maxLength) + '...' : str;
    }

    // fetch all' api per ottenere le chat
    fetch('/login/api/swapper/api_get_chats.php', {
      credentials: 'include'
    })
      .then(res => res.json())
      .then(data => {
        document.getElementById('loading').style.display = 'none';
        
        if(data.success) {
          if(data.chats.length === 0) {
            document.getElementById('no-chats').style.display = 'block';
          } else {
            document.getElementById('chat-list').style.display = 'block';
            document.getElementById('total-chats').textContent = data.totalChats;
            
            const container = document.getElementById('chats-container');
            
            data.chats.forEach(chat => {
              const chatCard = document.createElement('div');
              chatCard.className = 'card mb-3 chat-item';
              chatCard.onclick = () => {
                
                alert('Chat ID: ' + chat.idChat + '\n(Da implementare: visualizzazione messaggi)'); 
              };
              
              chatCard.innerHTML = `
                <div class="card-body">
                  <div class="d-flex justify-content-between align-items-start mb-2">
                    <div>
                      <h5 class="card-title mb-1">
                        <i class="bi bi-chat-fill me-2" style="color: #667eea;"></i>
                        ${chat.nomeChat}
                      </h5>
                      <div class="mb-2">
                        ${getTipoBadge(chat.tipoChat)}
                        ${getStatoBadge(chat.stato)}
                        <span class="badge bg-light text-dark badge-tipo">
                          <i class="bi bi-people me-1"></i>${chat.numPartecipanti}
                        </span>
                      </div>
                    </div>
                    <small class="text-muted">${formatDate(chat.dataUltimoMessaggio)}</small>
                  </div>
                  
                  ${chat.descrizione ? `<p class="text-muted small mb-2"><em>${truncate(chat.descrizione, 100)}</em></p>` : ''}
                  
                  <div class="d-flex align-items-center text-muted small">
                    <i class="bi bi-chat-square-quote me-2"></i>
                    <span>
                      ${chat.autoreUltimoMessaggio ? 
                        `<strong>${chat.autoreUltimoMessaggio}:</strong> ${truncate(chat.ultimoMessaggio, 80)}` : 
                        '<em>Nessun messaggio</em>'
                      }
                    </span>
                  </div>
                  
                  <div class="mt-2">
                    <small class="text-muted">
                      <i class="bi bi-envelope me-1"></i>
                      ${chat.totMessaggi} messaggi
                    </small>
                  </div>
                </div>
              `;
              
              container.appendChild(chatCard);
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