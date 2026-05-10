<!doctype html>
<html lang="it">
<head>
    <title>Richieste di Scambio - SwapHub</title>
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

        .products-to-receive {
            background-color: #d4edda;
            padding: 0.75rem;
            border-radius: 0.25rem;
            margin-bottom: 1rem;
            border-left: 4px solid #28a745;
        }

        .product-chip {
            display: inline-block;
            background-color: #fff;
            border: 1px solid #dee2e6;
            border-radius: 0.25rem;
            padding: 0.5rem 0.75rem;
            margin-right: 0.5rem;
            margin-bottom: 0.5rem;
            font-size: 0.85rem;
        }

        .status-badge {
            display: inline-block;
            padding: 0.35rem 0.6rem;
            border-radius: 0.25rem;
            font-size: 0.8rem;
            font-weight: bold;
        }

        .status-proposto {
            background-color: #e7f0ff;
            color: #004085;
        }

        .status-accettato {
            background-color: #d4edda;
            color: #155724;
        }

        .status-completato {
            background-color: #d1ecf1;
            color: #0c5460;
        }

        .status-annullato {
            background-color: #f8d7da;
            color: #721c24;
        }

        .user-info {
            display: flex;
            align-items: center;
            gap: 0.75rem;
        }

        .user-avatar {
            width: 40px;
            height: 40px;
            border-radius: 50%;
            object-fit: cover;
            background-color: #f0f0f0;
        }

        .empty-state {
            text-align: center;
            padding: 2rem;
            color: #6c757d;
        }

        .empty-state i {
            font-size: 3rem;
            opacity: 0.5;
            display: block;
            margin-bottom: 1rem;
        }

        .info-box {
            background: #e7f0ff;
            border-left: 4px solid #667eea;
            padding: 1rem;
            border-radius: 0.25rem;
            margin-bottom: 1.5rem;
        }
    </style>
</head>

<body style="background-color: #f8f9fa;">
    <main class="container py-5">
        <div class="row justify-content-center">
            <div class="col-lg-10">

                <div class="bg-white p-4 rounded shadow-sm" style="border-top: 5px solid #667eea;">
                    <header class="mb-4">
                        <div class="d-flex justify-content-between align-items-center">
                            <h1 class="fw-bold"><i class="bi bi-inbox me-2"></i>Richieste di Scambio</h1>
                            <a href="/login/visualizzaUtente.php" class="btn btn-secondary btn-sm">
                                <i class="bi bi-arrow-left me-1"></i>Indietro
                            </a>
                        </div>
                    </header>

                    <div class="info-box">
                        <i class="bi bi-info-circle me-2"></i>
                        Qui vedi tutte le richieste di scambio ricevute. Puoi accettarle, rifiutarle o visualizzare lo stato dei tuoi scambi.
                    </div>

                    <!-- Tabs -->
                    <ul class="nav nav-tabs mb-3" role="tablist">
                        <li class="nav-item" role="presentation">
                            <button class="nav-link active" id="tab-ricevute" data-bs-toggle="tab" data-bs-target="#ricevute" type="button">
                                <i class="bi bi-inbox me-1"></i>Ricevute
                            </button>
                        </li>
                        <li class="nav-item" role="presentation">
                            <button class="nav-link" id="tab-accettate" data-bs-toggle="tab" data-bs-target="#accettate" type="button">
                                <i class="bi bi-check-circle me-1"></i>Accettate
                            </button>
                        </li>
                        <li class="nav-item" role="presentation">
                            <button class="nav-link" id="tab-completate" data-bs-toggle="tab" data-bs-target="#completate" type="button">
                                <i class="bi bi-check2-all me-1"></i>Completate
                            </button>
                        </li>
                    </ul>

                    <!-- TAB: Richieste ricevute -->
                    <div class="tab-content">
                        <div class="tab-pane fade show active" id="ricevute" role="tabpanel">
                            <div id="loading-ricevute" class="text-center py-4">
                                <div class="spinner-border text-primary" role="status">
                                    <span class="visually-hidden">Caricamento...</span>
                                </div>
                                <p class="mt-2 text-muted">Caricamento richieste...</p>
                            </div>
                            <div id="content-ricevute" style="display: none;"></div>
                            <div id="empty-ricevute" class="empty-state" style="display: none;">
                                <i class="bi bi-inbox"></i>
                                <p>Nessuna richiesta di scambio ricevuta</p>
                            </div>
                        </div>

                        <!-- TAB: Richieste accettate -->
                        <div class="tab-pane fade" id="accettate" role="tabpanel">
                            <div id="loading-accettate" class="text-center py-4">
                                <div class="spinner-border text-primary" role="status">
                                    <span class="visually-hidden">Caricamento...</span>
                                </div>
                                <p class="mt-2 text-muted">Caricamento richieste...</p>
                            </div>
                            <div id="content-accettate" style="display: none;"></div>
                            <div id="empty-accettate" class="empty-state" style="display: none;">
                                <i class="bi bi-check-circle"></i>
                                <p>Nessuno scambio accettato</p>
                            </div>
                        </div>

                        <!-- TAB: Richieste completate -->
                        <div class="tab-pane fade" id="completate" role="tabpanel">
                            <div id="loading-completate" class="text-center py-4">
                                <div class="spinner-border text-primary" role="status">
                                    <span class="visually-hidden">Caricamento...</span>
                                </div>
                                <p class="mt-2 text-muted">Caricamento richieste...</p>
                            </div>
                            <div id="content-completate" style="display: none;"></div>
                            <div id="empty-completate" class="empty-state" style="display: none;">
                                <i class="bi bi-check2-all"></i>
                                <p>Nessuno scambio completato</p>
                            </div>
                        </div>
                    </div>

                </div>

            </div>
        </div>
    </main>

    <!-- Modal: Seleziona prodotti da ricevere -->
    <div class="modal fade" id="modalAccetta" tabindex="-1">
        <div class="modal-dialog modal-lg">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title">Seleziona Prodotti da Ricevere</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                </div>
                <div class="modal-body">
                    <div class="alert alert-info mb-3">
                        <i class="bi bi-info-circle me-2"></i>
                        Seleziona 1-2 prodotti tuoi che offri in cambio.
                    </div>
                    <div id="modal-products" style="max-height: 300px; overflow-y: auto;"></div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Annulla</button>
                    <button type="button" class="btn btn-success" id="btn-confirm-accept">Conferma Scambio</button>
                </div>
            </div>
        </div>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/@popperjs/core@2.11.8/dist/umd/popper.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/js/bootstrap.min.js"></script>

    <script>
        let tradeSelected = null;
        let selectedProductsToReceive = [];
        let MAX_PRODUCTS = 2;
        let allMyProducts = [];
        const modal = new bootstrap.Modal(document.getElementById('modalAccetta'));

        document.addEventListener('DOMContentLoaded', function() {
            caricaRichieste();
        });

        function caricaRichieste() {
            fetch('/login/api/swapper/api_get_trade_requests.php', {
                credentials: 'include'
            })
                .then(res => res.json())
                .then(data => {
                    console.log('✅ Richieste:', data);
                    
                    if (!data.success) {
                        console.error('Errore API:', data.error);
                        return;
                    }

                    // Filtra per stato
                    const ricevute = data.richieste.filter(r => r.stato === 'proposto');
                    const accettate = data.richieste.filter(r => r.stato === 'accettato');
                    const completate = data.richieste.filter(r => r.stato === 'completato');

                    renderTab('ricevute', ricevute, true);
                    renderTab('accettate', accettate, false);
                    renderTab('completate', completate, false);
                })
                .catch(err => {
                    console.error('Errore:', err);
                });
        }

        function renderTab(tabName, richieste, hasActions) {
            const loadingEl = document.getElementById(`loading-${tabName}`);
            const contentEl = document.getElementById(`content-${tabName}`);
            const emptyEl = document.getElementById(`empty-${tabName}`);

            loadingEl.style.display = 'none';

            if (richieste.length === 0) {
                emptyEl.style.display = 'block';
                return;
            }

            let html = '';
            richieste.forEach(richiesta => {
                const statusClass = `status-${richiesta.stato}`;
                const statusLabel = getStatusLabel(richiesta.stato);

                html += `
                    <div class="trade-card">
                        <div class="trade-card-header">
                            <div class="user-info">
                                <img src="${buildProfileImage(richiesta.fotoProfilo)}" alt="${escapeHtml(richiesta.mittenteNome)}" class="user-avatar" onerror="this.src='/login/uploads/profile/default.png'">
                                <div>
                                    <strong>${escapeHtml(richiesta.mittenteNome)} ${escapeHtml(richiesta.mittenteCognome)}</strong>
                                    <small class="text-muted d-block">@${escapeHtml(richiesta.username)}</small>
                                </div>
                            </div>
                            <span class="status-badge ${statusClass}">${statusLabel}</span>
                        </div>
                        <div class="trade-card-body">
                            <div class="products-offered">
                                <strong><i class="bi bi-arrow-right me-1"></i>Prodotti Offerti</strong>
                                <div class="mt-2">
                                    ${(richiesta.prodottiOfferty || []).map(p => `
                                        <span class="product-chip">${escapeHtml(p.Titolo)}</span>
                                    `).join('')}
                                </div>
                            </div>
                            ${richiesta.prodottiRichiesti && richiesta.prodottiRichiesti.length > 0 ? `
                                <div class="products-to-receive">
                                    <strong><i class="bi bi-arrow-left me-1"></i>Prodotti Richiesti da Te</strong>
                                    <div class="mt-2">
                                        ${(richiesta.prodottiRichiesti || []).map(p => `
                                            <span class="product-chip">${escapeHtml(p.Titolo)}</span>
                                        `).join('')}
                                    </div>
                                </div>
                            ` : ''}
                            <small class="text-muted d-block">
                                <i class="bi bi-calendar me-1"></i>Data: ${new Date(richiesta.data).toLocaleDateString('it-IT')}
                            </small>
                        </div>
                        ${hasActions ? `
                            <div style="padding: 1rem; border-top: 1px solid #dee2e6; display: flex; gap: 0.5rem;">
                                <button class="btn btn-sm btn-success" onclick="openAcceptModal(${richiesta.idScambio}, '${richiesta.username}')">
                                    <i class="bi bi-check me-1"></i>Accetta
                                </button>
                                <button class="btn btn-sm btn-danger" onclick="rifiutaScambio(${richiesta.idScambio})">
                                    <i class="bi bi-x me-1"></i>Rifiuta
                                </button>
                            </div>
                        ` : ''}
                    </div>
                `;
            });

            contentEl.innerHTML = html;
            contentEl.style.display = 'block';
        }

        function getStatusLabel(stato) {
            const labels = {
                'proposto': '⏳ In Sospeso',
                'accettato': '✔ Accettato',
                'completato': '✓ Completato',
                'annullato': '✗ Rifiutato'
            };
            return labels[stato] || stato;
        }

        function openAcceptModal(idScambio, username) {
            tradeSelected = idScambio;

            // Carica i prodotti disponibili
            fetch('/login/api/swapper/api_get_products.php', {
                credentials: 'include'
            })
                .then(res => res.json())
                .then(data => {
                    if (data.success && data.data) {
                        allMyProducts = data.data.filter(p => p.Disponibilità === 'disponibile');
                        renderProductsModal();
                        modal.show();
                    }
                })
                .catch(err => console.error('Errore:', err));
        }

        function renderProductsModal() {
            const container = document.getElementById('modal-products');
            container.innerHTML = '';
            selectedProductsToReceive = [];

            if (allMyProducts.length === 0) {
                container.innerHTML = '<p class="text-warning">Nessun prodotto disponibile</p>';
                return;
            }

            allMyProducts.forEach(prod => {
                const div = document.createElement('div');
                div.className = 'form-check mb-2';
                div.innerHTML = `
                    <input class="form-check-input product-checkbox" type="checkbox" value="${prod.idProdotto}" id="prod-${prod.idProdotto}">
                    <label class="form-check-label w-100" for="prod-${prod.idProdotto}">
                        <strong>${escapeHtml(prod.Titolo)}</strong>
                        <small class="text-muted d-block">${escapeHtml(prod.NomeCategoria)}</small>
                    </label>
                `;
                div.addEventListener('change', function(e) {
                    if (e.target.checked) {
                        if (selectedProductsToReceive.length < MAX_PRODUCTS) {
                            selectedProductsToReceive.push(parseInt(e.target.value));
                        } else {
                            e.target.checked = false;
                            alert(`Massimo ${MAX_PRODUCTS} prodotti`);
                        }
                    } else {
                        selectedProductsToReceive = selectedProductsToReceive.filter(id => id !== parseInt(e.target.value));
                    }
                    updateModalUI();
                });
                container.appendChild(div);
            });
        }

        function updateModalUI() {
            const btn = document.getElementById('btn-confirm-accept');
            if (selectedProductsToReceive.length >= 1) {
                btn.disabled = false;
            } else {
                btn.disabled = true;
            }
        }

        document.getElementById('btn-confirm-accept').addEventListener('click', function() {
            if (selectedProductsToReceive.length === 0) {
                alert('Seleziona almeno un prodotto');
                return;
            }

            const btn = this;
            btn.disabled = true;
            btn.innerHTML = '<span class="spinner-border spinner-border-sm me-2"></span>Conferma...';

            fetch('/login/api/swapper/api_accept_trade_request.php', {
                method: 'POST',
                headers: { 'Content-Type': 'application/json' },
                credentials: 'include',
                body: JSON.stringify({
                    idScambio: tradeSelected,
                    productsToReceive: selectedProductsToReceive
                })
            })
                .then(res => res.json())
                .then(data => {
                    if (data.success) {
                        alert('✅ Scambio accettato!');
                        modal.hide();
                        caricaRichieste();
                    } else {
                        alert('❌ Errore: ' + (data.error || 'Impossibile accettare'));
                    }
                    btn.disabled = false;
                    btn.innerHTML = 'Conferma Scambio';
                })
                .catch(err => {
                    console.error('Errore:', err);
                    alert('❌ Errore di rete');
                    btn.disabled = false;
                    btn.innerHTML = 'Conferma Scambio';
                });
        });

        function rifiutaScambio(idScambio) {
            if (!confirm('Vuoi rifiutare questo scambio?')) return;

            fetch('/login/api/swapper/api_reject_trade_request.php', {
                method: 'POST',
                headers: { 'Content-Type': 'application/json' },
                credentials: 'include',
                body: JSON.stringify({ idScambio })
            })
                .then(res => res.json())
                .then(data => {
                    if (data.success) {
                        alert('✅ Scambio rifiutato');
                        caricaRichieste();
                    } else {
                        alert('❌ Errore: ' + (data.error || 'Impossibile rifiutare'));
                    }
                })
                .catch(err => {
                    console.error('Errore:', err);
                    alert('❌ Errore di rete');
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

        function buildProfileImage(fotoprofilo) {
            if (!fotoprofilo) return '/login/uploads/profile/default.png';
            if (fotoprofilo.startsWith('http://') || fotoprofilo.startsWith('https://') || fotoprofilo.startsWith('/')) {
                return fotoprofilo;
            }
            return `/login/${fotoprofilo}`;
        }
    </script>
</body>
</html>
