<!doctype html>
<html lang="it">
    <head>
        <title>Crea nuova chat - SwapHub</title>
        <meta charset="utf-8" />
        <meta name="viewport" content="width=device-width, initial-scale=1, shrink-to-fit=no" />
        <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet" />
        <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.1/font/bootstrap-icons.css">
    </head>

    <body style="background-color: #f8f9fa;">
        <main class="container py-5">
            <div class="row justify-content-center">
                <div class="col-md-8 bg-white p-4 rounded shadow-sm" style="border-top: 5px solid #667eea;">
                    <header>
                        <div class="d-flex justify-content-between align-items-center mb-4">
                            <h1 class="fw-bold"><i class="bi bi-plus-circle me-2"></i>Crea Nuova Chat</h1>
                            <a href="/visualizzaUtente.php" class="btn btn-secondary">
                                <i class="bi bi-arrow-left me-1"></i>Indietro
                            </a>
                        </div>
                    </header>
                    
                    <form id="form-create-chat">
                        <!-- Nome Chat -->
                        <div class="mb-3">
                            <label for="nome-chat" class="form-label fw-bold">Nome Chat *</label>
                            <input type="text" class="form-control" id="nome-chat" placeholder="Es: Chat con Mario" required>
                            <small class="text-muted">Dai un nome identificativo alla chat</small>
                        </div>

                        <!-- Tipo Chat -->
                        <div class="mb-3">
                            <label class="form-label fw-bold">Tipo Chat *</label>
                            <div>
                                <div class="form-check form-check-inline">
                                    <input class="form-check-input" type="radio" name="tipo-chat" id="tipo-privata" value="privata" checked>
                                    <label class="form-check-label" for="tipo-privata">
                                        <i class="bi bi-person me-1"></i>Privata
                                    </label>
                                </div>
                                <div class="form-check form-check-inline">
                                    <input class="form-check-input" type="radio" name="tipo-chat" id="tipo-gruppo" value="gruppo">
                                    <label class="form-check-label" for="tipo-gruppo">
                                        <i class="bi bi-people me-1"></i>Gruppo
                                    </label>
                                </div>
                            </div>
                        </div>

                        <!-- Descrizione -->
                        <div class="mb-3">
                            <label for="descrizione-chat" class="form-label fw-bold">Descrizione (opzionale)</label>
                            <textarea class="form-control" id="descrizione-chat" rows="2" placeholder="Descrivi lo scopo della chat..."></textarea>
                        </div>

                        <!-- Selezione Partecipanti -->
                        <div class="mb-4">
                            <label class="form-label fw-bold">Seleziona Partecipanti *</label>
                            
                            <!-- Loading utenti -->
                            <div id="loading-utenti" class="text-center py-3">
                                <div class="spinner-border spinner-border-sm text-primary" role="status">
                                    <span class="visually-hidden">Caricamento...</span>
                                </div>
                                <small class="ms-2 text-muted">Caricamento utenti...</small>
                            </div>

                            <!-- Lista utenti -->
                            <div id="lista-utenti" style="display: none; max-height: 300px; overflow-y: auto;" class="border rounded p-2"></div>

                            <!-- Errore caricamento -->
                            <div id="error-utenti" class="alert alert-danger mt-2" style="display: none;">
                                <i class="bi bi-exclamation-triangle me-2"></i>
                                <span id="error-utenti-message"></span>
                            </div>
                        </div>

                        <!-- Pulsanti -->
                        <div class="d-grid gap-2">
                            <button type="submit" class="btn btn-primary" id="btn-crea">
                                <i class="bi bi-check-circle me-1"></i>Crea Chat
                            </button>
                            <a href="/visualizzaUtente.php" class="btn btn-outline-secondary">Annulla</a>
                        </div>
                    </form>

                    <!-- Alert successo -->
                    <div id="alert-success" class="alert alert-success mt-3" style="display: none;">
                        <i class="bi bi-check-circle-fill me-2"></i>
                        <span id="success-message"></span>
                    </div>

                    <!-- Alert errore -->
                    <div id="alert-error" class="alert alert-danger mt-3" style="display: none;">
                        <i class="bi bi-exclamation-triangle me-2"></i>
                        <span id="error-message"></span>
                    </div>
                </div>
            </div>
        </main>

        <script src="https://cdn.jsdelivr.net/npm/@popperjs/core@2.11.8/dist/umd/popper.min.js"></script>
        <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/js/bootstrap.min.js"></script>
        
        <script>
            // STEP 1: Carica utenti al caricamento pagina
            document.addEventListener('DOMContentLoaded', function() {
                caricaUtenti();
            });

            // STEP 2: Funzione per caricare gli utenti
            function caricaUtenti() {
                const urlAPI = '/api/swapper/api_get_available_users.php';
                console.log('🔍 Chiamata API utenti:', window.location.origin + urlAPI);
                
                fetch(urlAPI)
                    .then(res => {
                        console.log('📡 Status code:', res.status);
                        if(!res.ok) {
                            throw new Error('Errore HTTP ' + res.status);
                        }
                        return res.json();
                    })
                    .then(data => {
                        console.log('✅ Dati ricevuti:', data);
                        document.getElementById('loading-utenti').style.display = 'none';
                        
                        if(data.success) {
                            const listaUtenti = document.getElementById('lista-utenti');
                            listaUtenti.style.display = 'block';
                            
                            if(data.utenti.length === 0) {
                                listaUtenti.innerHTML = '<p class="text-muted text-center py-3">Nessun utente disponibile</p>';
                            } else {
                                data.utenti.forEach(utente => {
                                    const div = document.createElement('div');
                                    div.className = 'form-check mb-2';
                                    div.innerHTML = `
                                        <input class="form-check-input" type="checkbox" value="${utente.username}" id="user-${utente.username}">
                                        <label class="form-check-label" for="user-${utente.username}">
                                            <strong>${utente.Nome} ${utente.Cognome}</strong> 
                                            <small class="text-muted">(@${utente.username})</small>
                                        </label>
                                    `;
                                    listaUtenti.appendChild(div);
                                });
                            }
                        } else {
                            document.getElementById('error-utenti').style.display = 'block';
                            document.getElementById('error-utenti-message').textContent = data.error || 'Errore nel caricamento';
                        }
                    })
                    .catch(err => {
                        console.error('❌ Errore:', err);
                        document.getElementById('loading-utenti').style.display = 'none';
                        document.getElementById('error-utenti').style.display = 'block';
                        document.getElementById('error-utenti-message').textContent = 'Errore: ' + err.message;
                    });
            }

            // STEP 3: Gestione submit del form
            document.getElementById('form-create-chat').addEventListener('submit', function(e) {
                e.preventDefault();
                
                const nomeChat = document.getElementById('nome-chat').value;
                const tipoChat = document.querySelector('input[name="tipo-chat"]:checked').value;
                const descrizione = document.getElementById('descrizione-chat').value;
                
                const checkboxes = document.querySelectorAll('#lista-utenti input[type="checkbox"]:checked');
                const partecipanti = Array.from(checkboxes).map(cb => cb.value);
                
                if(partecipanti.length === 0) {
                    document.getElementById('alert-error').style.display = 'block';
                    document.getElementById('error-message').textContent = 'Seleziona almeno un partecipante';
                    return;
                }
                
                const datiChat = {
                    nome: nomeChat,
                    tipo: tipoChat,
                    descrizione: descrizione,
                    partecipanti: partecipanti
                };
                
                const btnCrea = document.getElementById('btn-crea');
                btnCrea.disabled = true;
                btnCrea.innerHTML = '<span class="spinner-border spinner-border-sm me-2"></span>Creazione...';
                
                const urlCreazione = '/api/swapper/api_create_chat.php';
                console.log('📤 Invio chat a:', window.location.origin + urlCreazione);
                console.log('📦 Dati:', datiChat);
                
                fetch(urlCreazione, {
                    method: 'POST',
                    headers: { 'Content-Type': 'application/json' },
                    body: JSON.stringify(datiChat)
                })
                .then(res => {
                    console.log('📡 Status risposta:', res.status);
                    if(!res.ok) {
                        throw new Error('Errore HTTP ' + res.status);
                    }
                    return res.json();
                })
                .then(data => {
                    console.log('✅ Risposta:', data);
                    btnCrea.disabled = false;
                    btnCrea.innerHTML = '<i class="bi bi-check-circle me-1"></i>Crea Chat';
                    
                    if(data.success) {
                        document.getElementById('alert-success').style.display = 'block';
                        document.getElementById('success-message').textContent = data.messaggio;
                        setTimeout(() => { 
                            window.location.href = '/visualizzaUtente.php'; 
                        }, 2000);
                    } else {
                        document.getElementById('alert-error').style.display = 'block';
                        document.getElementById('error-message').textContent = data.error || 'Errore nella creazione';
                    }
                })
                .catch(err => {
                    console.error('❌ Errore:', err);
                    btnCrea.disabled = false;
                    btnCrea.innerHTML = '<i class="bi bi-check-circle me-1"></i>Crea Chat';
                    document.getElementById('alert-error').style.display = 'block';
                    document.getElementById('error-message').textContent = 'Errore: ' + err.message;
                });
            });
        </script>
    </body>
</html>