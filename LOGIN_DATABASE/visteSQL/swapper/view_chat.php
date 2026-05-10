<!doctype html>
<html lang="it">
<head>
  <title>Le Mie Chat - SwapHub</title>
  <meta charset="utf-8" />
  <meta name="viewport" content="width=device-width, initial-scale=1" />
  <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet" />
  <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.1/font/bootstrap-icons.css">
  <style>
    * {
      box-sizing: border-box;
    }

    body {
      background-color: #f8f9fa;
      overflow: hidden;
    }

    .chat-container {
      height: 100vh;
      display: flex;
      flex-direction: column;
    }

    .chat-wrapper {
      display: flex;
      flex: 1;
      overflow: hidden;
    }

    .chat-list-panel {
      width: 35%;
      border-right: 1px solid #dee2e6;
      overflow-y: auto;
      background-color: #fff;
    }

    .chat-messages-panel {
      width: 65%;
      display: flex;
      flex-direction: column;
      background-color: #fff;
    }

    .messages-header {
      padding: 1.5rem;
      border-bottom: 1px solid #dee2e6;
      background-color: #f8f9fa;
    }

    .messages-body {
      flex: 1;
      overflow-y: auto;
      padding: 1.5rem;
      display: flex;
      flex-direction: column;
    }

    .message-row {
      margin-bottom: 1rem;
      display: flex;
      animation: slideIn 0.3s ease-out;
    }

    @keyframes slideIn {
      from {
        opacity: 0;
        transform: translateY(10px);
      }
      to {
        opacity: 1;
        transform: translateY(0);
      }
    }

    .message-own {
      justify-content: flex-end;
    }

    .message-other {
      justify-content: flex-start;
    }

    .message-bubble {
      max-width: 70%;
      padding: 0.75rem 1rem;
      border-radius: 1rem;
      word-wrap: break-word;
      position: relative;
      transition: all 0.2s;
    }

    .message-content {
      position: relative;
      display: inline-block;
      max-width: 70%;
    }

    .message-content .message-bubble {
      max-width: 100%;
    }

    .message-own .message-bubble {
      background-color: #667eea;
      color: white;
      border-bottom-right-radius: 0.25rem;
    }

    .message-other .message-bubble {
      background-color: #e9ecef;
      color: #333;
      border-bottom-left-radius: 0.25rem;
    }

    .message-info {
      font-size: 0.75rem;
      color: #6c757d;
      margin-top: 0.25rem;
      text-align: right;
    }

    .message-own .message-info {
      text-align: right;
    }

    .message-other .message-info {
      text-align: left;
    }

    .message-actions {
      display: flex;
      gap: 0.25rem;
      justify-content: flex-end;
      margin-bottom: 0.35rem;
      background: rgba(255, 255, 255, 0.9);
      border: 1px solid #dee2e6;
      border-radius: 999px;
      padding: 0.2rem;
      width: fit-content;
      margin-left: auto;
    }

    .message-actions button {
      width: 28px;
      height: 28px;
      padding: 0;
      font-size: 0.8rem;
      border: none;
      background: none;
      cursor: pointer;
      color: #667eea;
      transition: color 0.2s;
      border-radius: 50%;
    }

    .message-actions button:hover {
      color: #4c51bf;
      background-color: #eef1ff;
    }

    .message-own .message-content {
      display: flex;
      flex-direction: column;
      align-items: flex-end;
    }

    .messages-input-area {
      padding: 1.5rem;
      border-top: 1px solid #dee2e6;
      background-color: #f8f9fa;
    }

    .chat-item {
      cursor: pointer;
      transition: all 0.2s;
      border-bottom: 1px solid #f0f0f0;
      padding: 1rem;
    }

    .chat-item:hover {
      background-color: #f8f9fa;
    }

    .chat-item.active {
      background-color: #e7f5ff;
      border-left: 4px solid #667eea;
    }

    .chat-title-button {
      border: 0;
      background: transparent;
      padding: 0;
      width: 100%;
      display: block;
      cursor: default;
      color: inherit;
      text-align: left;
    }

    .chat-title-main {
      transition: color 0.2s ease;
    }

    .chat-header-avatar {
      width: 46px;
      height: 46px;
      border-radius: 50%;
      display: inline-flex;
      align-items: center;
      justify-content: center;
      flex-shrink: 0;
      background: linear-gradient(135deg, #667eea, #4c51bf);
      color: #fff;
      font-weight: 700;
      letter-spacing: 0.04em;
      box-shadow: 0 8px 20px rgba(102, 126, 234, 0.25);
    }

    .chat-header-avatar i {
      font-size: 1.2rem;
    }

    .chat-info-btn {
      white-space: nowrap;
      min-width: 110px;
      box-shadow: 0 8px 18px rgba(13, 110, 253, 0.18);
    }

    .chat-details-divider {
      border-top: 1px solid #dee2e6;
      opacity: 1;
    }

    .chat-details-section {
      padding-top: 1rem;
      margin-top: 1rem;
    }

    .chat-details-offcanvas {
      width: min(460px, 100vw);
    }

    .detail-meta-grid {
      display: grid;
      grid-template-columns: repeat(2, minmax(0, 1fr));
      gap: 0.75rem;
    }

    .detail-meta-box {
      border: 1px solid #e9ecef;
      border-radius: 0.9rem;
      padding: 0.75rem;
      background: #fafbff;
    }

    .detail-pill {
      display: inline-flex;
      align-items: center;
      gap: 0.35rem;
      padding: 0.35rem 0.7rem;
      border-radius: 999px;
      font-size: 0.78rem;
      background: #f1f3ff;
      color: #4c51bf;
      margin: 0 0.35rem 0.35rem 0;
    }

    .detail-member-card {
      display: flex;
      gap: 0.75rem;
      align-items: flex-start;
      padding: 0.85rem;
      border: 1px solid #e9ecef;
      border-radius: 1rem;
      background: #fff;
      margin-bottom: 0.75rem;
    }

    .detail-member-avatar {
      width: 52px;
      height: 52px;
      border-radius: 50%;
      object-fit: cover;
      border: 2px solid #e9ecef;
      flex-shrink: 0;
      background: #f8f9fa;
    }

    .badge-tipo {
      font-size: 0.75rem;
    }

    .no-chat-selected {
      display: flex;
      flex-direction: column;
      justify-content: center;
      align-items: center;
      color: #999;
    }

    .edit-mode-banner {
      background-color: #fff3cd;
      border-bottom: 1px solid #ffc107;
      padding: 0.75rem 1.5rem;
      display: none;
      align-items: center;
      justify-content: space-between;
    }

    .edit-mode-banner.active {
      display: flex;
    }

    @media (max-width: 768px) {
      .chat-list-panel {
        width: 100%;
      }

      .chat-messages-panel {
        display: none;
      }

      .chat-messages-panel.show {
        display: flex;
        width: 100%;
      }

      .chat-wrapper.mobile-chat-open .chat-list-panel {
        display: none;
      }

      .chat-wrapper.mobile-chat-open .chat-messages-panel {
        display: flex;
        width: 100%;
      }

      .mobile-back-btn {
        display: inline-flex !important;
      }

      .chat-details-offcanvas {
        width: 100vw;
      }

      .detail-meta-grid {
        grid-template-columns: 1fr;
      }

      .chat-info-btn {
        min-width: 96px;
      }
    }
  </style>
</head>
<body>
  <div class="chat-container">
    <!-- Header globale -->
    <div class="bg-primary text-white p-3 d-flex justify-content-between align-items-center">
      <h3 class="mb-0"><i class="bi bi-chat-dots me-2"></i>Le Mie Chat</h3>
      <a href="/login/visualizzaUtente.php" class="btn btn-light btn-sm">
        <i class="bi bi-arrow-left me-1"></i>Indietro
      </a>
    </div>

    <!-- Main chat wrapper -->
    <div class="chat-wrapper">
      <!-- LISTA CHAT (Sinistra) -->
      <div class="chat-list-panel">
        <div id="loading-chats" class="text-center py-5">
          <div class="spinner-border text-primary" role="status">
            <span class="visually-hidden">Caricamento...</span>
          </div>
          <p class="mt-2 text-muted">Caricamento chat...</p>
        </div>

        <div id="chat-list-container" style="display: none;"></div>

        <div id="no-chats" class="text-center py-5" style="display: none;">
          <i class="bi bi-chat-square-text" style="font-size: 2rem; color: #ccc;"></i>
          <h5 class="mt-3">Nessuna chat</h5>
          <p class="text-muted small">Non hai ancora partecipato a nessuna chat</p>
        </div>
      </div>

      <!-- ZONE MESSAGGI (Destra) -->
      <div class="chat-messages-panel" id="messages-panel">
        <!-- Edit Mode Banner -->
        <div class="edit-mode-banner" id="edit-banner">
          <span id="edit-text">Modifica messaggio...</span>
          <button class="btn-close btn-close-white" onclick="cancelEdit()"></button>
        </div>

        <!-- Header messaggi -->
        <div class="messages-header" id="messages-header">
          <div class="no-chat-selected" id="no-selection">
            <i class="bi bi-chat-left-dots" style="font-size: 3rem; color: #ccc;"></i>
            <p class="text-muted mt-3">Seleziona una chat per cominciare</p>
          </div>
          <div id="active-chat-header" style="display: none;">
            <button type="button" class="btn btn-outline-secondary btn-sm mobile-back-btn mb-2" style="display: none;" onclick="closeMobileChat()">
              <i class="bi bi-arrow-left me-1"></i>Torna alle chat
            </button>
            <div class="d-flex justify-content-between align-items-start gap-3 mb-1">
              <div class="d-flex align-items-center gap-3 flex-grow-1">
                <div class="chat-header-avatar" id="chat-header-avatar" aria-hidden="true">
                  <i class="bi bi-people-fill"></i>
                </div>
                <div class="chat-title-button" aria-label="Titolo chat">
                  <h5 class="mb-0 chat-title-main" id="active-chat-name">-</h5>
                  <small class="text-muted d-block mt-1" id="active-chat-subtitle">Info disponibili dal pulsante dedicato</small>
                </div>
              </div>
              <div class="d-flex align-items-center gap-2 flex-shrink-0">
                <button type="button" class="btn btn-primary btn-sm chat-info-btn" onclick="openChatDetails()">
                  <i class="bi bi-info-circle me-1"></i>Info chat
                </button>
                <button type="button" class="btn btn-outline-danger btn-sm" id="delete-chat-btn" style="display: none;" onclick="deleteActiveChat()">
                  <i class="bi bi-trash3 me-1"></i>Elimina
                </button>
              </div>
            </div>
            <small class="text-muted" id="active-chat-desc">-</small>
          </div>
        </div>

        <!-- Area messaggi -->
        <div class="messages-body" id="messages-body"></div>

        <!-- Area input -->
        <div class="messages-input-area" id="input-area">
          <form id="message-form" onsubmit="sendMessage(event)">
            <div class="input-group">
              <textarea 
                id="message-input" 
                class="form-control" 
                style="resize: none; height: 45px; max-height: 100px;"
                placeholder="Seleziona una chat per scrivere un messaggio..." 
                rows="1"></textarea>
              <button class="btn btn-primary" id="send-btn" type="submit" disabled>
                <i class="bi bi-send"></i>
              </button>
            </div>
          </form>
        </div>
      </div>
    </div>
  </div>

  <div class="offcanvas offcanvas-end chat-details-offcanvas" tabindex="-1" id="chatDetailsOffcanvas" aria-labelledby="chatDetailsOffcanvasLabel">
    <div class="offcanvas-header border-bottom">
      <div>
        <h5 class="offcanvas-title mb-1" id="chatDetailsOffcanvasLabel">Dettagli chat</h5>
        <small class="text-muted" id="chat-details-subtitle">Informazioni e membri della chat</small>
      </div>
      <button type="button" class="btn-close" data-bs-dismiss="offcanvas" aria-label="Close"></button>
    </div>
    <div class="offcanvas-body" id="chat-details-body">
      <div id="chat-details-loading" class="text-center py-5">
        <div class="spinner-border text-primary" role="status">
          <span class="visually-hidden">Caricamento...</span>
        </div>
        <p class="mt-3 text-muted mb-0">Caricamento dettagli chat...</p>
      </div>

      <div id="chat-details-error" class="alert alert-danger" style="display:none;"></div>

      <div id="chat-details-content" style="display:none;">
        <div class="mb-3">
          <div id="chat-details-pills" class="mb-2"></div>
          <h4 class="fw-bold mb-1" id="chat-details-name">-</h4>
          <div class="text-muted small" id="chat-details-creator">-</div>
        </div>

        <hr class="chat-details-divider my-3">

        <div class="detail-meta-grid mb-3">
          <div class="detail-meta-box">
            <div class="text-muted small">Tipo</div>
            <div class="fw-semibold" id="chat-details-type">-</div>
          </div>
          <div class="detail-meta-box">
            <div class="text-muted small">Stato</div>
            <div class="fw-semibold" id="chat-details-status">-</div>
          </div>
          <div class="detail-meta-box">
            <div class="text-muted small">Partecipanti</div>
            <div class="fw-semibold" id="chat-details-participants">-</div>
          </div>
          <div class="detail-meta-box">
            <div class="text-muted small">Messaggi</div>
            <div class="fw-semibold" id="chat-details-messages">-</div>
          </div>
        </div>

        <div class="chat-details-section mb-3">
          <div class="d-flex justify-content-between align-items-start mb-1">
            <div class="text-muted small">Descrizione</div>
            <div id="chat-description-actions" style="display: none;">
              <button id="btn-edit-desc" class="btn btn-sm btn-outline-primary">Modifica</button>
            </div>
          </div>

          <div id="chat-description-view">
            <div class="p-3 bg-light rounded-3" id="chat-details-description">-</div>
          </div>

          <div id="chat-description-edit" style="display: none;">
            <textarea id="chat-description-textarea" class="form-control mb-2" rows="3"></textarea>
            <div class="d-flex gap-2">
              <button id="btn-save-desc" class="btn btn-primary btn-sm">Salva</button>
              <button id="btn-cancel-desc" class="btn btn-outline-secondary btn-sm">Annulla</button>
            </div>
          </div>
        </div>

        <div class="chat-details-section mb-2 d-flex justify-content-between align-items-center">
          <h6 class="mb-0 fw-bold">Membri</h6>
          <small class="text-muted" id="chat-details-members-count">-</small>
        </div>
        <div id="chat-members-list"></div>
      </div>
    </div>
  </div>

  <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/js/bootstrap.bundle.min.js"></script>
  
  <script>
    /**
     * VARIABILI GLOBALI
     */
    let activeChat = null;
    let editingMessageId = null;

    function escapeHtml(value) {
      return String(value ?? '')
        .replace(/&/g, '&amp;')
        .replace(/</g, '&lt;')
        .replace(/>/g, '&gt;')
        .replace(/"/g, '&quot;')
        .replace(/'/g, '&#039;');
    }

    function buildProfileImagePath(fotoprofilo) {
      if (!fotoprofilo) {
        return '/login/uploads/profile/default.png';
      }

      if (fotoprofilo.startsWith('http://') || fotoprofilo.startsWith('https://') || fotoprofilo.startsWith('/')) {
        return fotoprofilo;
      }

      return `/login/${fotoprofilo}`;
    }

    function getChatInitials(chatName) {
      const cleaned = String(chatName || '').trim();
      if (!cleaned) return 'CH';

      const parts = cleaned.split(/\s+/).filter(Boolean);
      const initials = parts.slice(0, 2).map(part => part.charAt(0)).join('').toUpperCase();
      return initials || cleaned.charAt(0).toUpperCase();
    }

    function formatLongDate(dateString) {
      if (!dateString) return '-';

      const date = parseServerDate(dateString);
      if (!date || Number.isNaN(date.getTime())) return '-';

      return date.toLocaleString('it-IT', {
        day: '2-digit',
        month: 'long',
        year: 'numeric',
        hour: '2-digit',
        minute: '2-digit'
      });
    }

    /**
     * PARSE DATE DA MYSQL (UTC -> locale browser)
     */
    function parseServerDate(dateString) {
      if (!dateString) return null;

      // MySQL tipico: "YYYY-MM-DD HH:mm:ss"
      // Lo interpretiamo come UTC per evitare offset -2h in Italia.
      const normalized = dateString.includes('T')
        ? dateString
        : dateString.replace(' ', 'T');

      return new Date(`${normalized}Z`);
    }

    /**
     * FORMAT DATE
     */
    function formatDate(dateString) {
      if (!dateString) return 'Mai';
      
      const date = parseServerDate(dateString);
      if (!date || Number.isNaN(date.getTime())) return 'Data non valida';
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
        hour: '2-digit',
        minute: '2-digit'
      });
    }

    /**
     * FORMAT TIME
     */
    function formatTime(dateString) {
      if (!dateString) return '';
      const date = parseServerDate(dateString);
      if (!date || Number.isNaN(date.getTime())) return '';
      return date.toLocaleTimeString('it-IT', { 
        hour: '2-digit',
        minute: '2-digit'
      });
    }

    /**
     * CARICA LISTA CHAT
     */
    function loadChats() {
      fetch('/login/api/swapper/api_get_chats.php', {
        credentials: 'include'
      })
        .then(res => res.json())
        .then(data => {
          document.getElementById('loading-chats').style.display = 'none';
          
          if (data.success) {
            if (data.chats.length === 0) {
              document.getElementById('no-chats').style.display = 'block';
            } else {
              document.getElementById('chat-list-container').style.display = 'block';
              const container = document.getElementById('chat-list-container');
              container.innerHTML = '';
              
              data.chats.forEach(chat => {
                const chatItem = document.createElement('div');
                chatItem.className = 'chat-item';
                chatItem.onclick = () => openChat(chat, chatItem);
                if (activeChat && activeChat.idChat === chat.idChat) {
                  chatItem.classList.add('active');
                }
                
                chatItem.innerHTML = `
                  <div class="d-flex justify-content-between align-items-start">
                    <div style="flex: 1;">
                      <h6 class="mb-1 fw-bold">${chat.nomeChat}</h6>
                      <small class="text-muted d-block mb-1">
                        ${chat.autoreUltimoMessaggio ? chat.autoreUltimoMessaggio + ': ' : ''}
                        ${chat.ultimoMessaggio ? chat.ultimoMessaggio.substring(0, 50) : 'Nessun messaggio'}
                      </small>
                      <small class="text-muted">${formatDate(chat.dataUltimoMessaggio)}</small>
                    </div>
                    ${chat.totMessaggi > 0 ? `<span class="badge bg-primary rounded-pill">${chat.totMessaggi}</span>` : ''}
                  </div>
                `;
                container.appendChild(chatItem);
              });

              // Fallback: apri automaticamente la prima chat alla prima renderizzazione
              if (!activeChat && data.chats.length > 0) {
                const firstChatElement = container.querySelector('.chat-item');
                openChat(data.chats[0], firstChatElement);
              }
            }
          } else {
            document.getElementById('no-chats').style.display = 'block';
          }
        })
        .catch(err => {
          console.error(err);
          document.getElementById('loading-chats').style.display = 'none';
          document.getElementById('no-chats').style.display = 'block';
        });
    }

    /**
     * APRI CHAT E CARICA MESSAGGI
     */
    function openChat(chat, chatItemElement) {
      activeChat = chat;
      
      // Aggiorna UI lista
      document.querySelectorAll('.chat-item').forEach(item => {
        item.classList.remove('active');
      });
      if (chatItemElement) {
        chatItemElement.classList.add('active');
      }
      
      // Mostra pannello messaggi e header
      document.getElementById('messages-panel').classList.add('show');
      document.querySelector('.chat-wrapper').classList.add('mobile-chat-open');
      document.getElementById('no-selection').style.display = 'none';
      document.getElementById('active-chat-header').style.display = 'block';
      setInputEnabled(true);
      
      document.getElementById('active-chat-name').textContent = chat.nomeChat;
      document.getElementById('active-chat-desc').textContent = 
        `${chat.numPartecipanti} partecipanti • ${chat.totMessaggi} messaggi • clicca sul nome per i dettagli`;
      document.getElementById('chat-header-avatar').textContent = getChatInitials(chat.nomeChat);

      const deleteBtn = document.getElementById('delete-chat-btn');
      deleteBtn.style.display = chat.isCreator ? 'inline-flex' : 'none';
      
      // Carica messaggi
      loadMessages();
    }

    function openChatDetails() {
      if (!activeChat) return;

      const offcanvasElement = document.getElementById('chatDetailsOffcanvas');
      const offcanvas = bootstrap.Offcanvas.getOrCreateInstance(offcanvasElement);

      document.getElementById('chat-details-loading').style.display = 'block';
      document.getElementById('chat-details-error').style.display = 'none';
      document.getElementById('chat-details-content').style.display = 'none';
      offcanvas.show();

      fetch(`/login/api/swapper/api_get_chat_details.php?idChat=${activeChat.idChat}`, {
        credentials: 'include'
      })
        .then(res => res.json())
        .then(data => {
          document.getElementById('chat-details-loading').style.display = 'none';

          if (!data.success) {
            const errorBox = document.getElementById('chat-details-error');
            errorBox.textContent = data.error || 'Impossibile caricare i dettagli della chat';
            errorBox.style.display = 'block';
            return;
          }

          renderChatDetails(data);
        })
        .catch(err => {
          console.error(err);
          document.getElementById('chat-details-loading').style.display = 'none';
          const errorBox = document.getElementById('chat-details-error');
          errorBox.textContent = 'Errore di caricamento dei dettagli chat';
          errorBox.style.display = 'block';
        });
    }

    function renderChatDetails(data) {
      const chat = data.chat;
      const membri = data.membri || [];

      document.getElementById('chat-details-name').textContent = chat.nomeChat || '-';
      document.getElementById('chat-details-creator').textContent = chat.creator
        ? `Creatore: @${chat.creator}`
        : 'Creatore non registrato';
      document.getElementById('chat-details-type').textContent = chat.tipoChat || '-';
      document.getElementById('chat-details-status').textContent = chat.stato || '-';
      document.getElementById('chat-details-participants').textContent = `${chat.numPartecipanti ?? membri.length} persone`;
      document.getElementById('chat-details-messages').textContent = `${chat.totalMessages ?? 0} messaggi`;
      document.getElementById('chat-details-description').textContent = chat.descrizione || 'Nessuna descrizione disponibile.';

      // Keep last loaded chat for edit actions
      window.__lastChatDetails = chat;

      // Show edit actions if the current user is the creator
      if (chat.youAreCreator) {
        document.getElementById('chat-description-actions').style.display = 'block';
      } else {
        document.getElementById('chat-description-actions').style.display = 'none';
      }
      document.getElementById('chat-details-members-count').textContent = `${membri.length} membri`;

      const pills = document.getElementById('chat-details-pills');
      pills.innerHTML = `
        <span class="detail-pill"><i class="bi bi-people"></i> ${escapeHtml(chat.tipoChat || 'chat')}</span>
        <span class="detail-pill"><i class="bi bi-chat-dots"></i> ${chat.totalMessages ?? 0} messaggi</span>
        <span class="detail-pill"><i class="bi bi-calendar3"></i> ${escapeHtml(formatLongDate(chat.dataCreazione))}</span>
      `;

      const membersList = document.getElementById('chat-members-list');
      if (membri.length === 0) {
        membersList.innerHTML = '<div class="text-muted small">Nessun membro trovato.</div>';
      } else {
        membersList.innerHTML = membri.map(membro => `
          <div class="detail-member-card">
            <img src="${buildProfileImagePath(membro.fotoprofilo)}"
                 alt="${escapeHtml(membro.Nome || membro.username)}"
                 class="detail-member-avatar"
                 onerror="this.src='/login/uploads/profile/default.png'">
            <div class="flex-grow-1">
              <div class="d-flex align-items-start justify-content-between gap-2">
                <div>
                  <div class="fw-bold">${escapeHtml(membro.Nome || '')} ${escapeHtml(membro.Cognome || '')}</div>
                  <div class="text-muted small">@${escapeHtml(membro.username || '')}</div>
                </div>
                ${membro.isCreator ? '<span class="badge bg-primary">Creatore</span>' : ''}
              </div>
              <div class="text-muted small mt-1">
                ${membro.nomeRuolo ? `<span class="me-2"><i class="bi bi-person-badge"></i> ${escapeHtml(membro.nomeRuolo)}</span>` : ''}
                ${membro.localita ? `<span><i class="bi bi-geo-alt"></i> ${escapeHtml(membro.localita)}</span>` : ''}
              </div>
            </div>
          </div>
        `).join('');
      }

      document.getElementById('chat-details-content').style.display = 'block';
    }

    // ---- Edit description handlers ----
    function enableDescriptionEdit() {
      const chat = window.__lastChatDetails || {};
      document.getElementById('chat-description-view').style.display = 'none';
      document.getElementById('chat-description-edit').style.display = 'block';
      document.getElementById('chat-description-textarea').value = chat.descrizione || '';
    }

    function cancelDescriptionEdit() {
      document.getElementById('chat-description-edit').style.display = 'none';
      document.getElementById('chat-description-view').style.display = 'block';
    }

    function saveChatDescription() {
      const chat = window.__lastChatDetails || {};
      const newDesc = document.getElementById('chat-description-textarea').value;
      const payload = { idChat: chat.idChat, descrizione: newDesc };

      const btn = document.getElementById('btn-save-desc');
      btn.disabled = true;
      btn.textContent = 'Salvataggio...';

      fetch('/login/api/swapper/api_update_chat.php', {
        method: 'POST',
        headers: { 'Content-Type': 'application/json' },
        credentials: 'include',
        body: JSON.stringify(payload)
      })
        .then(res => res.json())
        .then(data => {
          btn.disabled = false;
          btn.textContent = 'Salva';
          if (data.success) {
            // aggiorna UI
            window.__lastChatDetails.descrizione = newDesc;
            document.getElementById('chat-details-description').textContent = newDesc || 'Nessuna descrizione disponibile.';
            cancelDescriptionEdit();
          } else {
            alert('Errore: ' + (data.error || 'Impossibile aggiornare la descrizione'));
          }
        })
        .catch(err => {
          console.error(err);
          btn.disabled = false;
          btn.textContent = 'Salva';
          alert('Errore di rete durante il salvataggio');
        });
    }

    // Wire buttons (delegated safe wiring in case elements are re-rendered)
    document.addEventListener('click', function(e) {
      if (e.target && e.target.id === 'btn-edit-desc') enableDescriptionEdit();
      if (e.target && e.target.id === 'btn-cancel-desc') cancelDescriptionEdit();
      if (e.target && e.target.id === 'btn-save-desc') saveChatDescription();
    });

    function setInputEnabled(enabled) {
      const input = document.getElementById('message-input');
      const sendBtn = document.getElementById('send-btn');

      input.disabled = !enabled;
      sendBtn.disabled = !enabled;
      input.placeholder = enabled
        ? 'Scrivi un messaggio...'
        : 'Seleziona una chat per scrivere un messaggio...';
    }

    /**
     * TORNA ALLA LISTA CHAT SU SCHERMI PICCOLI
     */
    function closeMobileChat() {
      document.querySelector('.chat-wrapper').classList.remove('mobile-chat-open');
      document.getElementById('messages-panel').classList.remove('show');
    }

    function resetChatPanel() {
      activeChat = null;
      setInputEnabled(false);
      document.getElementById('messages-body').innerHTML = '';
      document.getElementById('active-chat-header').style.display = 'none';
      document.getElementById('no-selection').style.display = 'flex';
      document.getElementById('message-input').value = '';
      document.getElementById('messages-panel').classList.remove('show');
      document.querySelector('.chat-wrapper').classList.remove('mobile-chat-open');
    }

    function deleteActiveChat() {
      if (!activeChat) return;

      const conferma = confirm(`Confermi la cancellazione della chat "${activeChat.nomeChat}"?`);
      if (!conferma) return;

      fetch('/login/api/swapper/api_delete_chat.php', {
        method: 'POST',
        headers: { 'Content-Type': 'application/json' },
        credentials: 'include',
        body: JSON.stringify({ idChat: activeChat.idChat })
      })
        .then(res => res.json())
        .then(data => {
          if (data.success) {
            resetChatPanel();
            document.getElementById('chat-list-container').style.display = 'none';
            document.getElementById('loading-chats').style.display = 'block';
            document.getElementById('no-chats').style.display = 'none';
            loadChats();
          } else {
            alert('Errore: ' + (data.error || 'Impossibile cancellare la chat'));
          }
        })
        .catch(err => {
          console.error(err);
          alert('Errore di cancellazione chat');
        });
    }

    /**
     * CARICA MESSAGGI DELLA CHAT ATTIVA
     */
    function loadMessages() {
      if (!activeChat) return;
      
      fetch(`/login/api/swapper/api_get_chat_messages.php?idChat=${activeChat.idChat}`, {
        credentials: 'include'
      })
        .then(res => res.json())
        .then(data => {
          if (data.success) {
            displayMessages(data.messages);
          } else {
            console.error(data.error);
          }
        })
        .catch(err => console.error(err));
    }

    /**
     * VISUALIZZA MESSAGGI
     */
    function displayMessages(messages) {
      const container = document.getElementById('messages-body');
      container.innerHTML = '';
      
      messages.forEach(msg => {
        const msgRow = document.createElement('div');
        msgRow.className = `message-row ${msg.isMine ? 'message-own' : 'message-other'}`;
        
        msgRow.innerHTML = `
          <div class="message-content">
            ${msg.isMine ? `
              <div class="message-actions">
                <button onclick="editMessage(${msg.idMessaggio}, this)" title="Modifica">
                  <i class="bi bi-pencil"></i>
                </button>
                <button onclick="deleteMessage(${msg.idMessaggio})" title="Elimina">
                  <i class="bi bi-trash"></i>
                </button>
              </div>
            ` : ''}
            <div class="message-bubble">
              ${msg.contenuto}
            </div>
            <div class="message-info">
              ${!msg.isMine ? `<strong>${msg.user}</strong> • ` : ''}
              ${formatTime(msg.dataInvio)}
            </div>
          </div>
        `;
        container.appendChild(msgRow);
      });
      
      // Scroll to bottom
      container.scrollTop = container.scrollHeight;
    }

    /**
     * INVIA MESSAGGIO
     */
    function sendMessage(event) {
      event.preventDefault();
      
      if (!activeChat) {
        alert('Seleziona prima una chat.');
        return;
      }
      
      const input = document.getElementById('message-input');
      const contenuto = input.value.trim();
      
      if (!contenuto) return;
      
      if (editingMessageId !== null) {
        // Modalità modifica
        updateMessage(editingMessageId, contenuto);
        return;
      }
      
      // Modalità nuovo messaggio
      fetch('/login/api/swapper/api_send_message.php', {
        method: 'POST',
        headers: { 'Content-Type': 'application/json' },
        credentials: 'include',
        body: JSON.stringify({
          idChat: activeChat.idChat,
          contenuto: contenuto
        })
      })
        .then(res => res.json())
        .then(data => {
          if (data.success) {
            input.value = '';
            input.style.height = '45px';
            loadMessages();
            loadChats();
          } else {
            alert('Errore: ' + data.error);
          }
        })
        .catch(err => {
          console.error(err);
          alert('Errore di invio');
        });
    }

    /**
     * MODIFICA MESSAGGIO
     */
    function editMessage(idMessaggio, triggerElement) {
      editingMessageId = idMessaggio;
      
      // Carica il contenuto del messaggio
      const msgElement = triggerElement.closest('.message-row').querySelector('.message-bubble');
      const contenuto = msgElement.textContent;
      
      const input = document.getElementById('message-input');
      input.value = contenuto;
      input.focus();
      
      // Mostra banner di modifica
      document.getElementById('edit-banner').classList.add('active');
      document.getElementById('edit-text').textContent = `Modifica messaggio (ID: ${idMessaggio})`;
    }

    /**
     * UPDATE MESSAGGIO
     */
    function updateMessage(idMessaggio, contenuto) {
      fetch('/login/api/swapper/api_update_message.php', {
        method: 'POST',
        headers: { 'Content-Type': 'application/json' },
        credentials: 'include',
        body: JSON.stringify({
          idMessaggio: idMessaggio,
          contenuto: contenuto
        })
      })
        .then(res => res.json())
        .then(data => {
          if (data.success) {
            cancelEdit();
            loadMessages();
          } else {
            alert('Errore: ' + data.error);
          }
        })
        .catch(err => {
          console.error(err);
          alert('Errore di modifica');
        });
    }

    /**
     * CANCELLA MESSAGGIO
     */
    function deleteMessage(idMessaggio) {
      if (!confirm('Sei sicuro di voler cancellare il messaggio?')) return;
      
      fetch('/login/api/swapper/api_delete_message.php', {
        method: 'POST',
        headers: { 'Content-Type': 'application/json' },
        credentials: 'include',
        body: JSON.stringify({
          idMessaggio: idMessaggio
        })
      })
        .then(res => res.json())
        .then(data => {
          if (data.success) {
            loadMessages();
            loadChats();
          } else {
            alert('Errore: ' + data.error);
          }
        })
        .catch(err => {
          console.error(err);
          alert('Errore di cancellazione');
        });
    }

    /**
     * ANNULLA MODIFICA
     */
    function cancelEdit() {
      editingMessageId = null;
      document.getElementById('edit-banner').classList.remove('active');
      document.getElementById('message-input').value = '';
      document.getElementById('message-input').style.height = '45px';
      document.getElementById('message-input').focus();
    }

    /**
     * INIT
     */
    document.addEventListener('DOMContentLoaded', () => {
      setInputEnabled(false);
      loadChats();
      
      // Auto-expand textarea
      const textarea = document.getElementById('message-input');
      textarea.addEventListener('input', () => {
        textarea.style.height = '45px';
        textarea.style.height = Math.min(textarea.scrollHeight, 100) + 'px';
      });
    });
  </script>
</body>
</html>