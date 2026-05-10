<!doctype html>
<html lang="it">
<head>
    <title>Scambi - SwapHub</title>
    <meta charset="utf-8" />
    <meta name="viewport" content="width=device-width, initial-scale=1, shrink-to-fit=no" />
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet" />
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.1/font/bootstrap-icons.css">
    <style>
        .product-card {
            border: 2px solid #dee2e6;
            border-radius: 0.5rem;
            cursor: pointer;
            transition: all 0.2s;
        }
        .product-card:hover {
            border-color: #667eea;
            box-shadow: 0 4px 8px rgba(102, 126, 234, 0.15);
        }
        .product-card.selected {
            border-color: #28a745;
            background-color: #f0f8f4;
        }
        .product-card.selected i {
            position: absolute;
            top: 8px;
            right: 8px;
            color: #28a745;
        }
        .product-image {
            height: 150px;
            object-fit: cover;
            width: 100%;
        }
        .user-avatar {
            width: 40px;
            height: 40px;
            border-radius: 50%;
            object-fit: cover;
        }
        .trade-card {
            border: 1px solid #dee2e6;
            margin-bottom: 1rem;
        }
    </style>
</head>
<body style="background-color: #f8f9fa;">
    <main class="container py-4">
        <div class="row justify-content-center">
            <div class="col-lg-10">
                <div class="bg-white p-4 rounded shadow-sm">
                    <div class="d-flex justify-content-between align-items-center mb-4">
                        <h2 class="fw-bold"><i class="bi bi-arrow-left-right me-2"></i>Gestisci Scambi</h2>
                        <a href="visualizzaUtente.php" class="btn btn-secondary btn-sm">Indietro</a>
                    </div>

                    <!-- Tabs -->
                    <ul class="nav nav-tabs mb-3" role="tablist">
                        <li class="nav-item">
                            <button class="nav-link active" id="tab-invia" data-bs-toggle="tab" data-bs-target="#invia" type="button">
                                <i class="bi bi-send me-1"></i>Invia Scambio
                            </button>
                        </li>
                        <li class="nav-item">
                            <button class="nav-link" id="tab-ricevute" data-bs-toggle="tab" data-bs-target="#ricevute" type="button">
                                <i class="bi bi-inbox me-1"></i>Richieste Ricevute
                            </button>
                        </li>
                        <li class="nav-item">
                            <button class="nav-link" id="tab-inviate" data-bs-toggle="tab" data-bs-target="#inviate" type="button">
                                <i class="bi bi-send-check me-1"></i>Richieste Inviate
                            </button>
                        </li>
                    </ul>

                    <!-- TAB 1: Invia Scambio -->
                    <div class="tab-content">
                        <div class="tab-pane fade show active" id="invia">
                            <div id="step1-invia">
                                <h4>1. Scegli Swapper</h4>
                                <div id="loading-utenti" class="text-center py-3">
                                    <div class="spinner-border spinner-border-sm" role="status"></div>
                                    Caricamento...
                                </div>
                                <div id="lista-utenti" style="display: none; max-height: 300px; overflow-y: auto;"></div>
                                <div id="user-selected" style="display: none;" class="alert alert-success mt-2">
                                    Swapper selezionato: <strong id="selected-user-display"></strong>
                                    <button type="button" class="btn btn-sm btn-outline-secondary ms-2" onclick="resetUtente()">Cambia</button>
                                </div>
                                <button type="button" class="btn btn-primary mt-3" id="btn-step1" onclick="goToStep2Invia()" disabled>Continua</button>
                            </div>

                            <div id="step2-invia" style="display: none;">
                                <h4>2. Seleziona Prodotti (1-2)</h4>
                                <div id="loading-prodotti" class="text-center py-3">
                                    <div class="spinner-border spinner-border-sm" role="status"></div>
                                    Caricamento...
                                </div>
                                <div id="prodotti-grid" class="row g-2" style="display: none;"></div>
                                <div id="no-prodotti" class="alert alert-warning" style="display: none;">
                                    Nessun prodotto disponibile
                                </div>
                                <div class="mt-3">
                                    <small class="d-block mb-2">Selezionati: <strong id="count-selected">0</strong>/2</small>
                                    <button type="button" class="btn btn-primary" id="btn-step2" onclick="goToStep3Invia()" disabled>Continua</button>
                                    <button type="button" class="btn btn-outline-secondary ms-2" onclick="backToStep1Invia()">Indietro</button>
                                </div>
                            </div>

                            <div id="step3-invia" style="display: none;">
                                <h4>3. Conferma</h4>
                                <div class="alert alert-info">
                                    Di' scambiare con <strong id="confirm-partner"></strong>
                                </div>
                                <div id="confirm-products"></div>
                                <button type="button" class="btn btn-success" onclick="inviaScambio()">Invia Richiesta</button>
                                <button type="button" class="btn btn-outline-secondary ms-2" onclick="backToStep2Invia()">Indietro</button>
                            </div>
                        </div>

                        <!-- TAB 2: Richieste Ricevute -->
                        <div class="tab-pane fade" id="ricevute">
                            <div id="loading-ricevute" class="text-center py-3">
                                <div class="spinner-border spinner-border-sm" role="status"></div>
                                Caricamento...
                            </div>
                            <div id="content-ricevute" style="display: none;"></div>
                            <div id="empty-ricevute" class="text-center py-3 text-muted" style="display: none;">
                                Nessuna richiesta ricevuta
                            </div>
                        </div>

                        <!-- TAB 3: Richieste Inviate -->
                        <div class="tab-pane fade" id="inviate">
                            <div id="loading-inviate" class="text-center py-3">
                                <div class="spinner-border spinner-border-sm" role="status"></div>
                                Caricamento...
                            </div>
                            <div id="content-inviate" style="display: none;"></div>
                            <div id="empty-inviate" class="text-center py-3 text-muted" style="display: none;">
                                Nessuna richiesta inviata
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </main>

    <!-- Modal: Seleziona prodotti da ricevere -->
    <div class="modal fade" id="modalAccetta" tabindex="-1">
        <div class="modal-dialog">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title">Seleziona i Tuoi Prodotti da Offrire in Cambio (1-2)</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                </div>
                <div class="modal-body">
                    <div id="modal-info-offered" class="alert alert-warning mb-3"></div>
                    <div id="modal-products" style="max-height: 250px; overflow-y: auto;"></div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Annulla</button>
                    <button type="button" class="btn btn-success" id="btn-confirm-accept">Invia Proposta</button>
                </div>
            </div>
        </div>
    </div>

    <div class="modal fade" id="modalConfermaMittente" tabindex="-1">
        <div class="modal-dialog">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title">Conferma scambio</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                </div>
                <div class="modal-body">
                    <div id="modal-conferma-info" class="alert alert-info mb-3"></div>
                    <div id="modal-conferma-prodotti"></div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Annulla</button>
                    <button type="button" class="btn btn-success" id="btn-confirm-sender">Ti va bene questo per questo?</button>
                </div>
            </div>
        </div>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/@popperjs/core@2.11.8/dist/umd/popper.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/js/bootstrap.min.js"></script>

    <script>
        let selectedUserId = null;
        let selectedProducts = [];
        let allProducts = [];
        let tradeToAccept = null;
        let tradeToConfirm = null;
        let productsToReceive = [];
        const modal = new bootstrap.Modal(document.getElementById('modalAccetta'), { backdrop: 'static' });
        const modalConfermaMittente = new bootstrap.Modal(document.getElementById('modalConfermaMittente'), { backdrop: 'static' });

        // ===== TAB INVIA =====
        document.addEventListener('DOMContentLoaded', () => {
            caricaUtenti();
            caricaRichieste();
            caricaRichiesteInviate();
        });

        function caricaUtenti() {
            fetch('/login/api/swapper/api_get_swappers_list.php', { credentials: 'include' })
                .then(r => r.json())
                .then(d => {
                    console.log('Utenti:', d);
                    document.getElementById('loading-utenti').style.display = 'none';
                    if (d.success && d.utenti.length > 0) {
                        let html = '';
                        d.utenti.forEach(u => {
                            html += `<div class="form-check mb-2">
                                <input class="form-check-input user-radio" type="radio" name="utente" value="${u.username}" id="u-${u.username}">
                                <label class="form-check-label w-100" for="u-${u.username}">
                                    <img src="${buildImg(u.fotoprofilo)}" class="user-avatar me-2" onerror="this.src='/login/uploads/profile/default.png'">
                                    ${escapeHtml(u.Nome)} ${escapeHtml(u.Cognome)} (@${escapeHtml(u.username)})
                                </label>
                            </div>`;
                        });
                        document.getElementById('lista-utenti').innerHTML = html;
                        document.getElementById('lista-utenti').style.display = 'block';
                        document.querySelectorAll('.user-radio').forEach(r => {
                            r.addEventListener('change', e => {
                                selectedUserId = e.target.value;
                                document.getElementById('user-selected').style.display = 'block';
                                document.getElementById('selected-user-display').textContent = e.target.labels[0].textContent.trim();
                                document.getElementById('btn-step1').disabled = false;
                            });
                        });
                    }
                });
        }

        function resetUtente() {
            selectedUserId = null;
            document.querySelectorAll('.user-radio').forEach(r => r.checked = false);
            document.getElementById('user-selected').style.display = 'none';
            document.getElementById('btn-step1').disabled = true;
        }

        function goToStep2Invia() {
            if (!selectedUserId) return;
            document.getElementById('step1-invia').style.display = 'none';
            document.getElementById('step2-invia').style.display = 'block';
            caricaProdotti();
        }

        function caricaProdotti() {
            fetch('/login/api/swapper/api_get_products.php', { credentials: 'include' })
                .then(r => r.json())
                .then(d => {
                    document.getElementById('loading-prodotti').style.display = 'none';
                    if (d.success && d.data) {
                        allProducts = d.data.filter(p => p.Disponibilità === 'disponibile');
                        if (allProducts.length === 0) {
                            document.getElementById('no-prodotti').style.display = 'block';
                        } else {
                            renderProdotti();
                            document.getElementById('prodotti-grid').style.display = 'grid';
                        }
                    }
                });
        }

        function renderProdotti() {
            const grid = document.getElementById('prodotti-grid');
            grid.innerHTML = '';
            allProducts.forEach(p => {
                const isSelected = selectedProducts.includes(p.idProdotto);
                const card = document.createElement('div');
                card.className = 'col-md-6';
                card.innerHTML = `<div class="card product-card ${isSelected ? 'selected' : ''}" style="cursor: pointer;" onclick="toggleProduct(${p.idProdotto})">
                    ${isSelected ? '<i class="bi bi-check-circle-fill"></i>' : ''}
                    <img src="${buildImg(p.img)}" class="product-image" onerror="this.src='/login/uploads/profile/default.png'">
                    <div class="p-2">
                        <strong>${escapeHtml(p.Titolo)}</strong>
                        <small class="text-muted d-block">${escapeHtml(p.NomeCategoria)}</small>
                    </div>
                </div>`;
                grid.appendChild(card);
            });
        }

        function toggleProduct(id) {
            const idx = selectedProducts.indexOf(id);
            if (idx > -1) {
                selectedProducts.splice(idx, 1);
            } else {
                if (selectedProducts.length < 2) {
                    selectedProducts.push(id);
                } else {
                    alert('Max 2 prodotti');
                    return;
                }
            }
            updateStep2UI();
        }

        function updateStep2UI() {
            document.getElementById('count-selected').textContent = selectedProducts.length;
            document.getElementById('btn-step2').disabled = selectedProducts.length === 0;
            renderProdotti();
        }

        function goToStep3Invia() {
            if (selectedProducts.length === 0) return;
            document.getElementById('step2-invia').style.display = 'none';
            document.getElementById('step3-invia').style.display = 'block';
            document.getElementById('confirm-partner').textContent = selectedUserId;
            let html = '';
            selectedProducts.forEach(id => {
                const p = allProducts.find(x => x.idProdotto === id);
                if (p) html += `<div class="p-2 border rounded mb-2">${escapeHtml(p.Titolo)}</div>`;
            });
            document.getElementById('confirm-products').innerHTML = html;
        }

        function backToStep1Invia() {
            selectedProducts = [];
            document.getElementById('step2-invia').style.display = 'none';
            document.getElementById('step1-invia').style.display = 'block';
        }

        function backToStep2Invia() {
            document.getElementById('step3-invia').style.display = 'none';
            document.getElementById('step2-invia').style.display = 'block';
        }

        function inviaScambio() {
            fetch('/login/api/swapper/api_send_trade_request.php', {
                method: 'POST',
                headers: { 'Content-Type': 'application/json' },
                credentials: 'include',
                body: JSON.stringify({ partnerUsername: selectedUserId, productsOffered: selectedProducts })
            })
                .then(r => r.json())
                .then(d => {
                    if (d.success) {
                        alert('✅ Scambio inviato!');
                        location.reload();
                    } else {
                        alert('❌ ' + (d.error || 'Errore'));
                    }
                });
        }

        // ===== TAB RICEVUTE =====
        function caricaRichieste() {
            fetch('/login/api/swapper/api_get_trade_requests.php', { credentials: 'include' })
                .then(r => r.json())
                .then(d => {
                    document.getElementById('loading-ricevute').style.display = 'none';
                    if (!d.success || d.richieste.length === 0) {
                        document.getElementById('empty-ricevute').style.display = 'block';
                        return;
                    }
                    let html = '';
                    d.richieste.forEach(t => {
                        const status = ['proposto', 'accettato', 'completato'].includes(t.stato) ? t.stato : 'annullato';
                        const badge = status === 'proposto' ? '<span class="badge bg-warning">Nuova</span>' : status === 'accettato' ? '<span class="badge bg-success">Accettata</span>' : '<span class="badge bg-info">Completata</span>';
                        const usernameDisplay = escapeHtml(t.userMittente || 'Utente');
                        html += `<div class="trade-card p-3">
                            <div class="d-flex justify-content-between align-items-start">
                                <div><strong>${escapeHtml(t.nomeMittente || t.mittenteNome || '')} ${escapeHtml(t.cognomeMittente || t.mittenteCognome || '')}</strong><br>
                                <small class="text-muted">Mittente: @${usernameDisplay}</small><br>
                                <small class="text-muted">${new Date(t.data).toLocaleDateString('it')}</small></div>
                                ${badge}
                            </div>
                            <div class="mt-2">
                                <small class="fw-bold d-block mb-1">Ti propone:</small>
                                ${(t.prodottiOfferty || []).map(p => `<span class="badge bg-light text-dark">${escapeHtml(p.Titolo)}</span>`).join(' ')}
                            </div>`;
                        if (status === 'proposto') {
                            html += `<div class="mt-2">
                                <button class="btn btn-sm btn-success" onclick="openAcceptModal(${t.idScambio})">Rispondi con i tuoi prodotti</button>
                                <button class="btn btn-sm btn-danger" onclick="rifiuta(${t.idScambio})">Rifiuta</button>
                            </div>`;
                        }
                        html += `</div>`;
                    });
                    document.getElementById('content-ricevute').innerHTML = html;
                    document.getElementById('content-ricevute').style.display = 'block';
                });
        }

        function caricaRichiesteInviate() {
            fetch('/login/api/swapper/api_get_sent_trade_requests.php', { credentials: 'include' })
                .then(r => r.json())
                .then(d => {
                    document.getElementById('loading-inviate').style.display = 'none';
                    if (!d.success || !d.richieste || d.richieste.length === 0) {
                        document.getElementById('empty-inviate').style.display = 'block';
                        return;
                    }

                    let html = '';
                    d.richieste.forEach(t => {
                        const badge = t.stato === 'proposto'
                            ? '<span class="badge bg-warning text-dark">In attesa</span>'
                            : t.stato === 'accettato'
                                ? '<span class="badge bg-info text-dark">Controproposta ricevuta</span>'
                                : t.stato === 'completato'
                                    ? '<span class="badge bg-success">Completato</span>'
                                    : '<span class="badge bg-secondary">Annullato</span>';

                        html += `<div class="trade-card p-3">
                            <div class="d-flex justify-content-between align-items-start">
                                <div>
                                    <strong>Destinatario: ${escapeHtml(t.nomeDestinatario || '')} ${escapeHtml(t.cognomeDestinatario || '')}</strong><br>
                                    <small class="text-muted">@${escapeHtml(t.userDestinatario || '')}</small><br>
                                    <small class="text-muted">${new Date(t.dataInizio).toLocaleDateString('it')}</small>
                                </div>
                                ${badge}
                            </div>
                            <div class="mt-2">
                                <small class="fw-bold d-block mb-1">Tu offri:</small>
                                ${(t.prodottiOfferty || []).map(p => `<span class="badge bg-light text-dark">${escapeHtml(p.Titolo)}</span>`).join(' ')}
                            </div>
                            ${t.prodottiRichiesti && t.prodottiRichiesti.length > 0 ? `
                                <div class="mt-2">
                                    <small class="fw-bold d-block mb-1">L'altro propone:</small>
                                    ${(t.prodottiRichiesti || []).map(p => `<span class="badge bg-light text-dark">${escapeHtml(p.Titolo)}</span>`).join(' ')}
                                </div>
                            ` : ''}
                            ${t.stato === 'accettato' ? `
                                <div class="mt-3">
                                    <button class="btn btn-sm btn-success" onclick="openSenderConfirmModal(${t.idScambio})">Ti va bene questo per questo?</button>
                                </div>
                            ` : ''}
                        </div>`;
                    });

                    document.getElementById('content-inviate').innerHTML = html;
                    document.getElementById('content-inviate').style.display = 'block';
                });
        }

        function openAcceptModal(idScambio) {
            tradeToAccept = idScambio;
            productsToReceive = [];
            
            // Mostra i prodotti che l'altro sta offrendo
            fetch('/login/api/swapper/api_get_trade_requests.php', { credentials: 'include' })
                .then(r => r.json())
                .then(d => {
                    if (d.success && d.richieste) {
                        const trade = d.richieste.find(x => x.idScambio === idScambio);
                        if (trade && trade.prodottiOfferty && trade.prodottiOfferty.length > 0) {
                            const offered = trade.prodottiOfferty.map(p => '<strong>' + escapeHtml(p.Titolo) + '</strong>').join(', ');
                            document.getElementById('modal-info-offered').innerHTML = '<i class="bi bi-gift me-2"></i><strong>Ti propone:</strong> ' + offered;
                        }
                    }
                });
            
            fetch('/login/api/swapper/api_get_products.php', { credentials: 'include' })
                .then(r => r.json())
                .then(d => {
                    if (d.success && d.data) {
                        const available = d.data.filter(p => p.Disponibilità === 'disponibile');
                        let html = '';
                        available.forEach(p => {
                            html += `<div class="form-check mb-2">
                                <input class="form-check-input prod-recv" type="checkbox" value="${p.idProdotto}" id="pr-${p.idProdotto}">
                                <label class="form-check-label w-100" for="pr-${p.idProdotto}" style="cursor: pointer;">
                                    ${escapeHtml(p.Titolo)}
                                </label>
                            </div>`;
                        });
                        document.getElementById('modal-products').innerHTML = html;
                        document.querySelectorAll('.prod-recv').forEach(cb => {
                            cb.addEventListener('change', e => {
                                if (e.target.checked) {
                                    if (productsToReceive.length < 2) productsToReceive.push(parseInt(e.target.value));
                                    else e.target.checked = false;
                                } else {
                                    productsToReceive = productsToReceive.filter(x => x !== parseInt(e.target.value));
                                }
                                document.getElementById('btn-confirm-accept').disabled = productsToReceive.length === 0;
                            });
                        });
                        modal.show();
                    }
                });
        }

        document.getElementById('btn-confirm-accept').addEventListener('click', () => {
            fetch('/login/api/swapper/api_accept_trade_request.php', {
                method: 'POST',
                headers: { 'Content-Type': 'application/json' },
                credentials: 'include',
                body: JSON.stringify({ idScambio: tradeToAccept, productsToReceive })
            })
                .then(r => r.json())
                .then(d => {
                    if (d.success) {
                        alert('✅ Proposta inviata al mittente!');
                        modal.hide();
                        caricaRichieste();
                        caricaRichiesteInviate();
                    } else {
                        alert('❌ ' + (d.error || 'Errore'));
                    }
                });
        });

        function openSenderConfirmModal(idScambio) {
            tradeToConfirm = idScambio;
            fetch('/login/api/swapper/api_get_sent_trade_requests.php', { credentials: 'include' })
                .then(r => r.json())
                .then(d => {
                    if (!d.success || !d.richieste) {
                        return;
                    }

                    const trade = d.richieste.find(x => x.idScambio === idScambio);
                    if (!trade) {
                        return;
                    }

                    document.getElementById('modal-conferma-info').innerHTML = `<strong>Ti va bene questo per questo?</strong><br>Destinatario: ${escapeHtml(trade.nomeDestinatario || '')} ${escapeHtml(trade.cognomeDestinatario || '')} <small class="text-muted">(@${escapeHtml(trade.userDestinatario || '')})</small>`;

                    document.getElementById('modal-conferma-prodotti').innerHTML = `
                        <div class="mb-2"><strong>I tuoi prodotti:</strong></div>
                        ${(trade.prodottiOfferty || []).map(p => `<span class="badge bg-light text-dark me-1 mb-1">${escapeHtml(p.Titolo)}</span>`).join('')}
                        <div class="mt-3 mb-2"><strong>I prodotti dell'altro:</strong></div>
                        ${(trade.prodottiRichiesti || []).map(p => `<span class="badge bg-light text-dark me-1 mb-1">${escapeHtml(p.Titolo)}</span>`).join('') || '<span class="text-muted">Nessun prodotto selezionato</span>'}
                    `;

                    modalConfermaMittente.show();
                });
        }

        document.getElementById('btn-confirm-sender').addEventListener('click', () => {
            fetch('/login/api/swapper/api_confirm_trade_request.php', {
                method: 'POST',
                headers: { 'Content-Type': 'application/json' },
                credentials: 'include',
                body: JSON.stringify({ idScambio: tradeToConfirm })
            })
                .then(r => r.json())
                .then(d => {
                    if (d.success) {
                        alert('✅ Scambio confermato!');
                        modalConfermaMittente.hide();
                        caricaRichieste();
                        caricaRichiesteInviate();
                    } else {
                        alert('❌ ' + (d.error || 'Errore'));
                    }
                });
        });

        function rifiuta(id) {
            if (!confirm('Rifiutare?')) return;
            fetch('/login/api/swapper/api_reject_trade_request.php', {
                method: 'POST',
                headers: { 'Content-Type': 'application/json' },
                credentials: 'include',
                body: JSON.stringify({ idScambio: id })
            })
                .then(r => r.json())
                .then(d => {
                    if (d.success) {
                        alert('✅ Rifiutato');
                        caricaRichieste();
                    }
                });
        }

        function escapeHtml(v) {
            return String(v || '').replace(/[&<>"']/g, m => ({ '&': '&amp;', '<': '&lt;', '>': '&gt;', '"': '&quot;', "'": '&#039;' }[m]));
        }

        function buildImg(img) {
            if (!img) return '/login/uploads/profile/default.png';
            return img.startsWith('http') || img.startsWith('/') ? img : `/login/${img}`;
        }
    </script>
</body>
</html>
