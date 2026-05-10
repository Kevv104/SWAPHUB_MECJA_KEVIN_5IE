<!doctype html>
<html lang="it">
<head>
    <title>Seleziona Prodotti - Scambio SwapHub</title>
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
            position: relative;
            overflow: hidden;
        }

        .product-card:hover {
            border-color: #667eea;
            box-shadow: 0 4px 8px rgba(102, 126, 234, 0.15);
        }

        .product-card.selected {
            border-color: #28a745;
            background-color: #f0f8f4;
            box-shadow: 0 4px 12px rgba(40, 167, 69, 0.2);
        }

        .product-card.selected::after {
            content: '';
            position: absolute;
            top: 8px;
            right: 8px;
            width: 24px;
            height: 24px;
            background-color: #28a745;
            border-radius: 50%;
            display: flex;
            align-items: center;
            justify-content: center;
        }

        .product-card.selected i {
            position: absolute;
            top: 8px;
            right: 8px;
            color: white;
            z-index: 1;
        }

        .product-image {
            height: 200px;
            object-fit: cover;
            width: 100%;
            background-color: #f0f0f0;
        }

        .product-info {
            padding: 0.75rem;
        }

        .badge-disponibilita {
            display: inline-block;
            padding: 0.25rem 0.5rem;
            border-radius: 0.25rem;
            font-size: 0.75rem;
            font-weight: bold;
        }

        .badge-disponibile {
            background-color: #d4edda;
            color: #155724;
        }

        .products-grid {
            display: grid;
            grid-template-columns: repeat(auto-fill, minmax(180px, 1fr));
            gap: 1rem;
        }

        .counter {
            position: absolute;
            top: 0.5rem;
            left: 0.5rem;
            background-color: #667eea;
            color: white;
            border-radius: 50%;
            width: 28px;
            height: 28px;
            display: flex;
            align-items: center;
            justify-content: center;
            font-weight: bold;
            font-size: 0.85rem;
        }

        .alert-selection-state {
            position: sticky;
            top: 0;
            z-index: 10;
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
                    <header>
                        <div class="d-flex justify-content-between align-items-center mb-4">
                            <h1 class="fw-bold"><i class="bi bi-bag-check me-2"></i>Seleziona Prodotti da Scambiare</h1>
                            <a href="javascript:history.back()" class="btn btn-secondary btn-sm">
                                <i class="bi bi-arrow-left me-1"></i>Indietro
                            </a>
                        </div>
                    </header>

                    <!-- Info box con requisiti -->
                    <div class="info-box">
                        <strong><i class="bi bi-info-circle me-1"></i>Regole di scambio:</strong><br>
                        <small>Seleziona <strong>1-2 prodotti disponibili</strong> da offrire in scambio. Puoi selezionare un massimo di 2 prodotti.</small>
                    </div>

                    <!-- Alert stato selezione -->
                    <div id="alert-selection" class="alert alert-warning alert-selection-state" style="display: none;">
                        <div class="d-flex justify-content-between align-items-center">
                            <span>
                                <i class="bi bi-exclamation-triangle me-2"></i>
                                <span id="selection-text"></span>
                            </span>
                            <button type="button" class="btn-close" onclick="document.getElementById('alert-selection').style.display='none';"></button>
                        </div>
                    </div>

                    <!-- Chi stai scambiando con -->
                    <div class="alert alert-info mb-3">
                        <i class="bi bi-arrow-left-right me-2"></i>
                        <strong>Stai per scambiare con:</strong> <span id="partner-display" class="fw-bold">-</span>
                    </div>

                    <!-- Loading prodotti -->
                    <div id="loading-prodotti" class="text-center py-5">
                        <div class="spinner-border spinner-border-lg text-primary" role="status">
                            <span class="visually-hidden">Caricamento...</span>
                        </div>
                        <p class="mt-3 text-muted">Caricamento prodotti disponibili...</p>
                    </div>

                    <!-- Griglia prodotti -->
                    <div id="prodotti-container" style="display: none;">
                        <div class="products-grid" id="prodotti-grid"></div>
                    </div>

                    <!-- Nessun prodotto disponibile -->
                    <div id="no-prodotti" class="alert alert-warning" style="display: none;">
                        <i class="bi bi-info-circle me-2"></i>
                        Non hai prodotti disponibili per lo scambio. Carica prima alcuni prodotti in "Gestisci Prodotti".
                    </div>

                    <!-- Errore caricamento -->
                    <div id="error-prodotti" class="alert alert-danger" style="display: none;">
                        <i class="bi bi-exclamation-triangle me-2"></i>
                        <span id="error-prodotti-message"></span>
                    </div>

                    <!-- Sezione di riepilogo e azione -->
                    <div id="action-section" style="display: none;" class="mt-4 border-top pt-3">
                        <div class="row mb-3">
                            <div class="col-sm-6">
                                <div class="card border-light">
                                    <div class="card-body">
                                        <small class="text-muted">Prodotti Selezionati</small>
                                        <h3 class="card-title">
                                            <span id="count-selected">0</span> / 2
                                        </h3>
                                    </div>
                                </div>
                            </div>
                            <div class="col-sm-6">
                                <div class="card border-light">
                                    <div class="card-body">
                                        <small class="text-muted">Stato</small>
                                        <h5 class="card-title">
                                            <span id="status-badge" class="badge bg-warning">Incompleto</span>
                                        </h5>
                                    </div>
                                </div>
                            </div>
                        </div>

                        <div class="d-grid gap-2">
                            <button type="button" class="btn btn-primary btn-lg" id="btn-proponi" disabled>
                                <i class="bi bi-send me-1"></i>Proponi Scambio
                            </button>
                            <a href="javascript:history.back()" class="btn btn-outline-secondary">Annulla</a>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </main>

    <script src="https://cdn.jsdelivr.net/npm/@popperjs/core@2.11.8/dist/umd/popper.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/js/bootstrap.min.js"></script>

    <script>
        let selectedProducts = [];
        const MAX_PRODUCTS = 2;
        const MIN_PRODUCTS = 1;
        let partnerUsername = null;
        let allProducts = [];

        // STEP 1: Inizializzazione
        document.addEventListener('DOMContentLoaded', function() {
            // Leggi l'username dall'associato dal step precedente
            partnerUsername = sessionStorage.getItem('tradePartnerUsername');
            if (!partnerUsername) {
                alert('Errore: partner di scambio non trovato. Torna indietro e riprova.');
                window.history.back();
                return;
            }

            document.getElementById('partner-display').textContent = partnerUsername;
            caricaProdottiDisponibili();
        });

        // STEP 2: Carica prodotti disponibili dell'utente
        function caricaProdottiDisponibili() {
            fetch('/login/api/swapper/api_get_products.php', {
                credentials: 'include'
            })
                .then(res => res.json())
                .then(data => {
                    console.log('📦 Prodotti caricati:', data);
                    document.getElementById('loading-prodotti').style.display = 'none';

                    if (data.success && data.prodotti.length > 0) {
                        // Filtra solo prodotti con Disponibilità = 'disponibile'
                        allProducts = data.prodotti.filter(p => p.Disponibilità === 'disponibile');

                        if (allProducts.length === 0) {
                            document.getElementById('no-prodotti').style.display = 'block';
                        } else {
                            renderProdotti();
                            document.getElementById('prodotti-container').style.display = 'block';
                            document.getElementById('action-section').style.display = 'block';
                        }
                    } else {
                        document.getElementById('no-prodotti').style.display = 'block';
                    }
                })
                .catch(err => {
                    console.error('❌ Errore:', err);
                    document.getElementById('loading-prodotti').style.display = 'none';
                    document.getElementById('error-prodotti').style.display = 'block';
                    document.getElementById('error-prodotti-message').textContent = err.message;
                });
        }

        // STEP 3: Renderizza la griglia di prodotti
        function renderProdotti() {
            const grid = document.getElementById('prodotti-grid');
            grid.innerHTML = '';

            allProducts.forEach(prodotto => {
                const isSelected = selectedProducts.includes(prodotto.idProdotto);
                const selectionIndex = selectedProducts.indexOf(prodotto.idProdotto);

                const card = document.createElement('div');
                card.className = `product-card ${isSelected ? 'selected' : ''}`;
                card.style.cursor = 'pointer';

                if (isSelected) {
                    card.innerHTML += `<div class="counter">${selectionIndex + 1}</div>`;
                }

                card.innerHTML += `
                    <img src="${buildProductImagePath(prodotto.img)}" 
                         alt="${escapeHtml(prodotto.Titolo)}" 
                         class="product-image"
                         onerror="this.src='/login/uploads/profile/default.png'">
                    ${isSelected ? '<i class="bi bi-check-circle-fill" style="position: absolute; top: 8px; right: 8px; color: white; font-size: 1.5rem; text-shadow: 0 0 2px rgba(0,0,0,0.3);"></i>' : ''}
                    <div class="product-info">
                        <strong class="d-block text-truncate">${escapeHtml(prodotto.Titolo)}</strong>
                        <small class="text-muted d-block text-truncate">${escapeHtml(prodotto.NomeCategoria)}</small>
                        <small class="text-muted d-block mb-2">${escapeHtml(prodotto.Condizioni)}</small>
                        <span class="badge-disponibilita badge-disponibile">Disponibile</span>
                    </div>
                `;

                card.addEventListener('click', function() {
                    toggleProductSelection(prodotto.idProdotto);
                });

                grid.appendChild(card);
            });
        }

        // STEP 4: Toggle selezione prodotto
        function toggleProductSelection(productId) {
            const index = selectedProducts.indexOf(productId);

            if (index > -1) {
                // Deseleziona
                selectedProducts.splice(index, 1);
            } else {
                // Seleziona
                if (selectedProducts.length < MAX_PRODUCTS) {
                    selectedProducts.push(productId);
                } else {
                    showSelectionAlert(`Massimo ${MAX_PRODUCTS} prodotti selezionabili`);
                    return;
                }
            }

            updateUI();
        }

        // STEP 5: Aggiorna UI
        function updateUI() {
            document.getElementById('count-selected').textContent = selectedProducts.length;

            if (selectedProducts.length >= MIN_PRODUCTS) {
                document.getElementById('btn-proponi').disabled = false;
                document.getElementById('status-badge').className = 'badge bg-success';
                document.getElementById('status-badge').textContent = 'Pronto';
                document.getElementById('alert-selection').style.display = 'none';
            } else {
                document.getElementById('btn-proponi').disabled = true;
                document.getElementById('status-badge').className = 'badge bg-warning';
                document.getElementById('status-badge').textContent = 'Incompleto';
                showSelectionAlert(`Seleziona almeno ${MIN_PRODUCTS} prodotto`);
            }

            renderProdotti();
        }

        // STEP 6: Mostra alert per stato selezione
        function showSelectionAlert(message) {
            document.getElementById('selection-text').textContent = message;
            document.getElementById('alert-selection').style.display = 'block';
        }

        // STEP 7: Proponi scambio
        document.getElementById('btn-proponi').addEventListener('click', function() {
            if (selectedProducts.length === 0) {
                alert('Seleziona almeno un prodotto');
                return;
            }

            const btn = this;
            btn.disabled = true;
            btn.innerHTML = '<span class="spinner-border spinner-border-sm me-2"></span>Invio...';

            const payload = {
                partnerUsername: partnerUsername,
                productsOffered: selectedProducts
            };

            fetch('/login/api/swapper/api_send_trade_request.php', {
                method: 'POST',
                headers: { 'Content-Type': 'application/json' },
                credentials: 'include',
                body: JSON.stringify(payload)
            })
                .then(res => res.json())
                .then(data => {
                    console.log('✅ Risposta:', data);
                    if (data.success) {
                        alert('Richiesta di scambio inviata con successo!');
                        sessionStorage.removeItem('tradePartnerUsername');
                        window.location.href = '/login/visualizzaUtente.php';
                    } else {
                        alert('Errore: ' + (data.error || 'Impossibile inviare la richiesta'));
                        btn.disabled = false;
                        btn.innerHTML = '<i class="bi bi-send me-1"></i>Proponi Scambio';
                    }
                })
                .catch(err => {
                    console.error('❌ Errore:', err);
                    alert('Errore di rete: ' + err.message);
                    btn.disabled = false;
                    btn.innerHTML = '<i class="bi bi-send me-1"></i>Proponi Scambio';
                });
        });

        // Helper functions
        function escapeHtml(value) {
            return String(value ?? '')
                .replace(/&/g, '&amp;')
                .replace(/</g, '&lt;')
                .replace(/>/g, '&gt;')
                .replace(/"/g, '&quot;')
                .replace(/'/g, '&#039;');
        }

        function buildProductImagePath(img) {
            if (!img) return '/login/uploads/profile/default.png';
            if (img.startsWith('http://') || img.startsWith('https://') || img.startsWith('/')) {
                return img;
            }
            return `/login/${img}`;
        }
    </script>
</body>
</html>
