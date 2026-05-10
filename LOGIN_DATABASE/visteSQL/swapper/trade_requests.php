<!doctype html>
<html lang="it">
<head>
    <title>Richieste di Scambio Ricevute - SwapHub</title>
    <meta charset="utf-8" />
    <meta name="viewport" content="width=device-width, initial-scale=1, shrink-to-fit=no" />
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet" />
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.1/font/bootstrap-icons.css">
    <style>
        .trade-card {
            border: 1px solid #dee2e6;
            border-radius: 0.5rem;
            transition: all 0.2s;
            margin-bottom: 1rem;
        }

        .trade-card:hover {
            box-shadow: 0 4px 8px rgba(0, 0, 0, 0.1);
            border-color: #667eea;
        }

        .trade-card-header {
            background-color: #f8f9fa;
            padding: 1rem;
            border-bottom: 1px solid #dee2e6;
            display: flex;
            justify-content: space-between;
            align-items: center;
        }

        .trade-card-body {
            padding: 1rem;
        }

        .products-offered {
            background-color: #fff3cd;
            padding: 0.75rem;
            border-radius: 0.25rem;
            margin-bottom: 1rem;
            border-left: 4px solid #ffc107;
        }

        .product-chip {
            display: inline-block;
            background-color: #fff;
            border: 1px solid #dee2e6;
            border-radius: 0.25rem;
            padding: 0.5rem 0.75rem;
            margin-right: 0.5rem;
            margin-bottom: 0.5rem;
            font-size: 0.875rem;
        }

        .product-chip i {
            margin-right: 0.25rem;
        }

        .status-badge {
            padding: 0.5rem 1rem;
            border-radius: 0.25rem;
            font-weight: bold;
            font-size: 0.875rem;
        }

        .status-proposto {
            background-color: #e3f2fd;
            color: #1976d2;
        }

        .status-accettato {
            background-color: #f3e5f5;
            color: #7b1fa2;
        }

        .status-completato {
            background-color: #e8f5e9;
            color: #388e3c;
        }

        .status-annullato {
            background-color: #fce4ec;
            color: #c2185b;
        }

        .user-header {
            display: flex;
            align-items: center;
            gap: 1rem;
        }

        .user-avatar {
            width: 48px;
            height: 48px;
            border-radius: 50%;
            object-fit: cover;
            background-color: #f0f0f0;
        }

        .user-info h6 {
            margin-bottom: 0.25rem;
            font-weight: bold;
        }

        .user-info small {
            color: #6c757d;
        }

        .action-buttons {
            display: flex;
            gap: 0.5rem;
        }

        .action-buttons .btn {
            white-space: nowrap;
        }

        .empty-state {
            text-align: center;
            padding: 3rem 1rem;
        }

        .empty-state i {
            font-size: 3rem;
            color: #adb5bd;
            margin-bottom: 1rem;
        }

        .filters {
            background-color: #f8f9fa;
            padding: 1rem;
            border-radius: 0.5rem;
            margin-bottom: 1.5rem;
        }

        .filter-chip {
            display: inline-block;
            background-color: white;
            border: 1px solid #dee2e6;
            border-radius: 0.25rem;
            padding: 0.5rem 0.75rem;
            margin-right: 0.5rem;
            cursor: pointer;
            transition: all 0.2s;
        }

        .filter-chip:hover {
            border-color: #667eea;
            background-color: #f8f9fa;
        }

        .filter-chip.active {
            background-color: #667eea;
            color: white;
            border-color: #667eea;
        }
    </style>
</head>

<body style="background-color: #f8f9fa;">
    <main class="container py-5">
        <div class="row justify-content-center">
            <div class="col-lg-10">
                <div class="bg-white p-4 rounded shadow-sm" style="border-top: 5px solid #667eea;">
                    <header class="mb-4">
                        <div class="d-flex justify-content-between align-items-center mb-3">
                            <h1 class="fw-bold"><i class="bi bi-inbox me-2"></i>Richieste di Scambio Ricevute</h1>
                            <a href="/login/visualizzaUtente.php" class="btn btn-secondary btn-sm">
                                <i class="bi bi-arrow-left me-1"></i>Indietro
                            </a>
                        </div>
                        <small class="text-muted">Visualizza e gestisci le richieste di scambio ricevute da altri Swapper</small>
                    </header>

                    <!-- Filtri stato -->
                    <div class="filters">
                        <strong class="me-2">Filtra per stato:</strong>
                        <span class="filter-chip active" data-filter="all" onclick="filterByStatus('all', this)">
                            Tutte <i class="bi bi-funnel"></i>
                        </span>
                        <span class="filter-chip" data-filter="proposto" onclick="filterByStatus('proposto', this)">
                            <i class="bi bi-clock"></i> In Attesa
                        </span>
                        <span class="filter-chip" data-filter="accettato" onclick="filterByStatus('accettato', this)">
                            <i class="bi bi-check-circle"></i> Accettate
                        </span>
                        <span class="filter-chip" data-filter="completato" onclick="filterByStatus('completato', this)">
                            <i class="bi bi-check2-all"></i> Completate
                        </span>
                        <span class="filter-chip" data-filter="annullato" onclick="filterByStatus('annullato', this)">
                            <i class="bi bi-x-circle"></i> Rifiutate
                        </span>
                    </div>

                    <!-- Loading -->
                    <div id="loading" class="text-center py-5">
                        <div class="spinner-border spinner-border-lg text-primary" role="status">
                            <span class="visually-hidden">Caricamento...</span>
                        </div>
                        <p class="mt-3 text-muted">Caricamento richieste...</p>
                    </div>

                    <!-- Container richieste -->
                    <div id="richieste-container" style="display: none;"></div>

                    <!-- Empty state -->
                    <div id="empty-state" class="empty-state" style="display: none;">
                        <i class="bi bi-inbox"></i>
                        <h5 class="text-muted">Nessuna richiesta</h5>
                        <small class="text-muted">Non hai richieste di scambio per il momento.</small>
                    </div>

                    <!-- Errore -->
                    <div id="error" class="alert alert-danger" style="display: none;">
                        <i class="bi bi-exclamation-triangle me-2"></i>
                        <span id="error-message"></span>
                    </div>
                </div>
            </div>
        </div>
    </main>

    <!-- Modal per accettare scambio (selezione prodotti ricevuti) -->
    <div class="modal fade" id="acceptTradeModal" tabindex="-1">
        <div class="modal-dialog modal-lg">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title"><i class="bi bi-bag-check me-2"></i>Accetta Scambio - Seleziona i Tuoi Prodotti</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                </div>
                <div class="modal-body">
                    <div id="modal-loading" class="text-center py-3">
                        <div class="spinner-border spinner-border-sm text-primary"></div>
                        <small class="ms-2 text-muted">Caricamento prodotti...</small>
                    </div>
                    <div id="modal-content" style="display: none;">
                        <div class="alert alert-info mb-3">
                            <i class="bi bi-info-circle me-2"></i>
                            Seleziona <strong>1-2 prodotti</strong> da ricevere in cambio.
                        </div>
                        <div id="modal-products-container"></div>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Annulla</button>
                    <button type="button" class="btn btn-primary" id="btn-confirm-accept" onclick="confirmAcceptTrade()">
                        <i class="bi bi-check me-1"></i>Accetta Scambio
                    </button>
                </div>
            </div>
        </div>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/@popperjs/core@2.11.8/dist/umd/popper.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/js/bootstrap.min.js"></script>

    <script>
        let allTradeRequests = [];
        let currentFilter = 'all';
        let acceptTradeModal = null;
        let modalIdScambio = null;
        let modalSelectedProducts = [];

        document.addEventListener('DOMContentLoaded', function() {
            acceptTradeModal = new bootstrap.Modal(document.getElementById('acceptTradeModal'));
            caricaRichiesteScambio();
        });

        function caricaRichiesteScambio() {
            fetch('/login/api/swapper/api_get_trade_requests.php', {
                credentials: 'include'
            })
                .then(res => res.json())
                .then(data => {
                    console.log('📦 Richieste caricate:', data);
                    document.getElementById('loading').style.display = 'none';

                    if (data.success && data.richieste && data.richieste.length > 0) {
                        allTradeRequests = data.richieste;
                        renderRichieste();
                        document.getElementById('richieste-container').style.display = 'block';
                    } else {
                        document.getElementById('empty-state').style.display = 'block';
                    }
                })
                .catch(err => {
                    console.error('❌ Errore:', err);
                    document.getElementById('loading').style.display = 'none';
                    document.getElementById('error').style.display = 'block';
                    document.getElementById('error-message').textContent = err.message;
                });
        }

        function renderRichieste() {
            const container = document.getElementById('richieste-container');
            container.innerHTML = '';

            const filtered = currentFilter === 'all' 
                ? allTradeRequests 
                : allTradeRequests.filter(r => r.stato === currentFilter);

            if (filtered.length === 0) {
                container.style.display = 'none';
                document.getElementById('empty-state').style.display = 'block';
                return;
            }

            filtered.forEach(richiesta => {
                const card = createTradeCard(richiesta);
                container.appendChild(card);
            });
        }

        function createTradeCard(richiesta) {
            const div = document.createElement('div');
            div.className = 'trade-card';

            const statusClass = `status-${richiesta.stato}`;
            const canAccept = richiesta.stato === 'proposto';
            const canReject = richiesta.stato === 'proposto';

            let actionsHTML = '';
            if (canAccept || canReject) {
                actionsHTML = `
                    ${canAccept ? `<button class="btn btn-success btn-sm" onclick="openAcceptModal(${richiesta.idScambio})"><i class="bi bi-check me-1"></i>Accetta</button>` : ''}
                    ${canReject ? `<button class="btn btn-danger btn-sm" onclick="rejectTrade(${richiesta.idScambio})"><i class="bi bi-x me-1"></i>Rifiuta</button>` : ''}
                `;
            }

            div.innerHTML = `
                <div class="trade-card-header">
                    <div class="user-header">
                        <img src="${buildProfileImagePath(richiesta.fotoprofiloMittente)}" 
                             alt="${escapeHtml(richiesta.nomeMittente)}" 
                             class="user-avatar"
                             onerror="this.src='/login/uploads/profile/default.png'">
                        <div class="user-info">
                            <h6 class="mb-0">${escapeHtml(richiesta.nomeMittente)} ${escapeHtml(richiesta.cognomeMittente || '')}</h6>
                            <small>@${escapeHtml(richiesta.userMittente)}</small><br>
                            <small class="text-muted">${formatDate(richiesta.dataInizio)}</small>
                        </div>
                    </div>
                    <span class="status-badge ${statusClass}">${escapeHtml(richiesta.stato)}</span>
                </div>
                <div class="trade-card-body">
                    <strong class="d-block mb-2">
                        <i class="bi bi-gift"></i> Prodotti offerti:
                    </strong>
                    <div class="products-offered">
                        ${richiesta.prodottiOfferty.map(p => `
                            <div class="product-chip">
                                <i class="bi bi-box"></i> ${escapeHtml(p.Titolo)}
                            </div>
                        `).join('')}
                    </div>

                    ${richiesta.prodottiRichiesti && richiesta.prodottiRichiesti.length > 0 ? `
                        <strong class="d-block mb-2">
                            <i class="bi bi-arrow-down"></i> Prodotti richiesti:
                        </strong>
                        <div class="products-offered" style="background-color: #e8f5e9; border-left-color: #4caf50;">
                            ${richiesta.prodottiRichiesti.map(p => `
                                <div class="product-chip">
                                    <i class="bi bi-box"></i> ${escapeHtml(p.Titolo)}
                                </div>
                            `).join('')}
                        </div>
                    ` : ''}

                    ${actionsHTML ? `
                        <div class="action-buttons mt-3">
                            ${actionsHTML}
                        </div>
                    ` : ''}
                </div>
            `;

            return div;
        }

        function filterByStatus(status, element) {
            currentFilter = status;
            document.querySelectorAll('.filter-chip').forEach(chip => chip.classList.remove('active'));
            element.classList.add('active');
            renderRichieste();
        }

        function openAcceptModal(idScambio) {
            modalIdScambio = idScambio;
            modalSelectedProducts = [];
            acceptTradeModal.show();
            caricaProdottiPerAccettazione();
        }

        function caricaProdottiPerAccettazione() {
            fetch('/login/api/swapper/api_get_products.php', {
                credentials: 'include'
            })
                .then(res => res.json())
                .then(data => {
                    console.log('📦 Prodotti per accettazione:', data);
                    document.getElementById('modal-loading').style.display = 'none';

                    if (data.success) {
                        const prodotti = data.prodotti.filter(p => p.Disponibilità === 'disponibile');
                        renderModalProducts(prodotti);
                        document.getElementById('modal-content').style.display = 'block';
                    }
                })
                .catch(err => console.error('Errore:', err));
        }

        function renderModalProducts(prodotti) {
            const container = document.getElementById('modal-products-container');
            container.innerHTML = '';

            prodotti.forEach(p => {
                const isSelected = modalSelectedProducts.includes(p.idProdotto);
                const div = document.createElement('div');
                div.className = `form-check mb-2 p-2 border rounded ${isSelected ? 'bg-light' : ''}`;
                div.onclick = () => toggleModalProduct(p.idProdotto);
                div.style.cursor = 'pointer';

                div.innerHTML = `
                    <input class="form-check-input" type="checkbox" ${isSelected ? 'checked' : ''} id="prod-${p.idProdotto}" value="${p.idProdotto}">
                    <label class="form-check-label w-100" for="prod-${p.idProdotto}">
                        <strong>${escapeHtml(p.Titolo)}</strong>
                        <small class="text-muted d-block">${escapeHtml(p.NomeCategoria)} • ${escapeHtml(p.Condizioni)}</small>
                    </label>
                `;
                container.appendChild(div);
            });
        }

        function toggleModalProduct(productId) {
            const index = modalSelectedProducts.indexOf(productId);
            if (index > -1) {
                modalSelectedProducts.splice(index, 1);
            } else {
                if (modalSelectedProducts.length < 2) {
                    modalSelectedProducts.push(productId);
                } else {
                    alert('Massimo 2 prodotti');
                    return;
                }
            }

            // Re-render per aggiornare UI
            const prodotti = allTradeRequests.find(r => r.idScambio === modalIdScambio);
            const allProdotti = document.querySelectorAll('#modal-products-container .form-check-input');
            allProdotti.forEach(input => {
                input.checked = modalSelectedProducts.includes(parseInt(input.value));
                input.parentElement.parentElement.classList.toggle('bg-light', input.checked);
            });

            document.getElementById('btn-confirm-accept').disabled = modalSelectedProducts.length === 0;
        }

        function confirmAcceptTrade() {
            if (modalSelectedProducts.length === 0) {
                alert('Seleziona almeno un prodotto');
                return;
            }

            const btn = document.getElementById('btn-confirm-accept');
            btn.disabled = true;
            btn.innerHTML = '<span class="spinner-border spinner-border-sm me-2"></span>Accettando...';

            fetch('/login/api/swapper/api_accept_trade_request.php', {
                method: 'POST',
                headers: { 'Content-Type': 'application/json' },
                credentials: 'include',
                body: JSON.stringify({
                    idScambio: modalIdScambio,
                    productsToReceive: modalSelectedProducts
                })
            })
                .then(res => res.json())
                .then(data => {
                    if (data.success) {
                        alert('Scambio accettato!');
                        acceptTradeModal.hide();
                        caricaRichiesteScambio();
                    } else {
                        alert('Errore: ' + (data.error || 'Impossibile accettare'));
                    }
                    btn.disabled = false;
                    btn.innerHTML = '<i class="bi bi-check me-1"></i>Accetta Scambio';
                })
                .catch(err => {
                    console.error('Errore:', err);
                    alert('Errore di rete');
                    btn.disabled = false;
                    btn.innerHTML = '<i class="bi bi-check me-1"></i>Accetta Scambio';
                });
        }

        function rejectTrade(idScambio) {
            if (!confirm('Confermi il rifiuto di questo scambio?')) return;

            fetch('/login/api/swapper/api_reject_trade_request.php', {
                method: 'POST',
                headers: { 'Content-Type': 'application/json' },
                credentials: 'include',
                body: JSON.stringify({ idScambio })
            })
                .then(res => res.json())
                .then(data => {
                    if (data.success) {
                        alert('Scambio rifiutato');
                        caricaRichiesteScambio();
                    } else {
                        alert('Errore: ' + (data.error || 'Impossibile rifiutare'));
                    }
                })
                .catch(err => {
                    console.error('Errore:', err);
                    alert('Errore di rete');
                });
        }

        function escapeHtml(value) {
            return String(value ?? '')
                .replace(/&/g, '&amp;')
                .replace(/</g, '&lt;')
                .replace(/>/g, '&gt;')
                .replace(/"/g, '&quot;')
                .replace(/'/g, '&#039;');
        }

        function buildProfileImagePath(fotoprofilo) {
            if (!fotoprofilo) return '/login/uploads/profile/default.png';
            if (fotoprofilo.startsWith('http://') || fotoprofilo.startsWith('https://') || fotoprofilo.startsWith('/')) {
                return fotoprofilo;
            }
            return `/login/${fotoprofilo}`;
        }

        function formatDate(dateStr) {
            return new Date(dateStr).toLocaleDateString('it-IT', {
                year: 'numeric',
                month: 'long',
                day: 'numeric'
            });
        }
    </script>
</body>
</html>
