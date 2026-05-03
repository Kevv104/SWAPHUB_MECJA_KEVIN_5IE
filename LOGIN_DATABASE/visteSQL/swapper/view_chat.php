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
      display: none;
      position: absolute;
      right: -80px;
      top: 0;
      background: white;
      border: 1px solid #dee2e6;
      border-radius: 0.5rem;
      box-shadow: 0 2px 8px rgba(0,0,0,0.1);
    }

    .message-own:hover .message-actions {
      display: flex;
      gap: 0.25rem;
    }

    .message-actions button {
      padding: 0.25rem 0.5rem;
      font-size: 0.75rem;
      border: none;
      background: none;
      cursor: pointer;
      color: #667eea;
      transition: color 0.2s;
    }

    .message-actions button:hover {
      color: #4c51bf;
    }

    /* Su touch/mobile non c'e' hover: mostriamo sempre le azioni sui messaggi propri */
    @media (hover: none), (max-width: 768px) {
      .message-own .message-actions {
        display: flex;
        position: static;
        margin-bottom: 0.35rem;
        justify-content: flex-end;
        background: transparent;
        border: 0;
        box-shadow: none;
      }

      .message-own .message-content {
        display: flex;
        flex-direction: column;
        align-items: flex-end;
      }
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
            <div class="d-flex justify-content-between align-items-center mb-1">
              <h5 class="mb-0" id="active-chat-name">-</h5>
              <button type="button" class="btn btn-outline-danger btn-sm" id="delete-chat-btn" style="display: none;" onclick="deleteActiveChat()">
                <i class="bi bi-trash3 me-1"></i>Elimina chat
              </button>
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

  <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/js/bootstrap.bundle.min.js"></script>
  
  <script>
    /**
     * VARIABILI GLOBALI
     */
    let activeChat = null;
    let editingMessageId = null;

    /**
     * FORMAT DATE
     */
    function formatDate(dateString) {
      if (!dateString) return 'Mai';
      
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
        hour: '2-digit',
        minute: '2-digit'
      });
    }

    /**
     * FORMAT TIME
     */
    function formatTime(dateString) {
      if (!dateString) return '';
      const date = new Date(dateString);
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
        `${chat.numPartecipanti} partecipanti • ${chat.totMessaggi} messaggi`;

      const deleteBtn = document.getElementById('delete-chat-btn');
      deleteBtn.style.display = chat.isCreator ? 'inline-flex' : 'none';
      
      // Carica messaggi
      loadMessages();
    }

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