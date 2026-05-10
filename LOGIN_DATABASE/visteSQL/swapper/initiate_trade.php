<!doctype html>
<html lang="it">
<head>
    <title>Avvia Scambio - SwapHub</title>
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
                        <h1 class="fw-bold"><i class="bi bi-arrow-left-right me-2"></i>Avvia Scambio</h1>
                        <a href="/login/visualizzaUtente.php" class="btn btn-secondary">
                            <i class="bi bi-arrow-left me-1"></i>Indietro
                        </a>
                    </div>
                </header>
                
                <div class="alert alert-info" role="alert">
                    <i class="bi bi-info-circle me-2"></i>
                    <strong>Come funziona:</strong> Seleziona un utente con cui desideri fare uno scambio. Potrai poi scegliere quali dei tuoi prodotti disponibili offrire in cambio.
                </div>

                <form id="form-select-user">
                    <!-- Descrizione -->
                    <div class="mb-3">
                        <label class="form-label fw-bold">
                            <i class="bi bi-person-circle me-1"></i>Scegli con chi scambiare *
                        </label>
                        <small class="text-muted d-block mb-2">Seleziona uno utente dalla lista</small>
                    </div>

                    <!-- Loading utenti -->
                    <div id="loading-utenti" class="text-center py-3">
                        <div class="spinner-border spinner-border-sm text-primary" role="status">
                            <span class="visually-hidden">Caricamento...</span>
                        </div>
                        <small class="ms-2 text-muted">Caricamento utenti disponibili...</small>
                    </div>

                    <!-- Lista utenti (scrollable) -->
                    <div id="lista-utenti" style="display: none; max-height: 400px; overflow-y: auto;" class="border rounded p-2 mb-3"></div>

                    <!-- Errore caricamento -->
                    <div id="error-utenti" class="alert alert-danger mt-2" style="display: none;">
                        <i class="bi bi-exclamation-triangle me-2"></i>
                        <span id="error-utenti-message"></span>
                    </div>

                    <!-- User selezionato -->
                    <div id="user-selected" style="display: none;" class="alert alert-success mb-3">
                        <i class="bi bi-check-circle me-2"></i>
                        <strong>Scambi con:</strong> <span id="selected-user-display"></span>
                    </div>

                    <!-- Pulsanti -->
                    <div class="d-grid gap-2">
                        <button type="submit" class="btn btn-primary" id="btn-continua" disabled>
                            <i class="bi bi-arrow-right me-1"></i>Continua a Selezione Prodotti
                        </button>
                        <a href="/login/visualizzaUtente.php" class="btn btn-outline-secondary">Annulla</a>
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
        let selectedUserId = null;

        // STEP 1: Carica utenti al caricamento pagina
        document.addEventListener('DOMContentLoaded', function() {
            caricaUtenti();
        });

        // STEP 2: Funzione per caricare gli utenti disponibili per scambio
        function caricaUtenti() {
            const urlAPI = '/login/api/swapper/api_get_swappers_list.php';
            console.log('🔍 Caricamento utenti per scambio:', window.location.origin + urlAPI);
            
            fetch(urlAPI, {
                credentials: 'include'
            })
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
                            listaUtenti.innerHTML = '<p class="text-muted text-center py-3">Nessun utente disponibile per scambio</p>';
                        } else {
                            listaUtenti.innerHTML = '';
                            data.utenti.forEach(utente => {
                                const div = document.createElement('div');
                                div.className = 'form-check mb-2';
                                div.innerHTML = `
                                    <input class="form-check-input user-radio" type="radio" name="utente-scambio" value="${utente.username}" id="user-${utente.username}">
                                    <label class="form-check-label w-100" for="user-${utente.username}" style="cursor: pointer;">
                                        <div class="d-flex align-items-center">
                                            <img src="${buildProfileImagePath(utente.fotoprofilo)}" 
                                                 alt="${escapeHtml(utente.Nome)}" 
                                                 class="rounded-circle me-2" 
                                                 width="32" height="32"
                                                 onerror="this.src='/login/uploads/profile/default.png'">
                                            <div>
                                                <strong>${escapeHtml(utente.Nome || '')} ${escapeHtml(utente.Cognome || '')}</strong>
                                                <small class="text-muted d-block">@${escapeHtml(utente.username)}</small>
                                                ${utente.localita ? `<small class="text-muted"><i class="bi bi-geo-alt"></i> ${escapeHtml(utente.localita)}</small>` : ''}
                                            </div>
                                        </div>
                                    </label>
                                `;
                                listaUtenti.appendChild(div);
                            });

                            // Aggiungi listener ai radio button
                            document.querySelectorAll('.user-radio').forEach(radio => {
                                radio.addEventListener('change', function() {
                                    selectedUserId = this.value;
                                    document.getElementById('btn-continua').disabled = false;
                                    document.getElementById('user-selected').style.display = 'block';
                                    document.getElementById('selected-user-display').textContent = 
                                        this.labels[0].querySelector('strong').textContent;
                                });
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
        document.getElementById('form-select-user').addEventListener('submit', function(e) {
            e.preventDefault();
            
            if (!selectedUserId) {
                alert('Seleziona un utente per continuare');
                return;
            }

            // Passa il username selezionato al passo successivo (selezione prodotti)
            // Per ora usiamo sessionStorage per mantenere lo stato
            sessionStorage.setItem('tradePartnerUsername', selectedUserId);
            
            // Reindirizza alla pagina di selezione prodotti
            console.log('✅ Reindirizzamento a selezione prodotti per utente:', selectedUserId);
            window.location.href = '/login/mockup_manager.php?azione=select_trade_products';
        });

        // STEP 4: Helper functions
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
    </script>
</body>
</html>
