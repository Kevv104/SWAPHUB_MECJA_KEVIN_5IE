<?php
    // Session already started by mockup_manager.php
?>

<!DOCTYPE html>
<html lang="it">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Gestione Prodotti - SwapHub</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.css" rel="stylesheet">
    <style>
        body {
            background-color: #f8f9fa;
            padding: 20px 0;
        }

        .container-main {
            max-width: 1000px;
            margin: 0 auto;
        }

        .header-section {
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            color: white;
            padding: 30px;
            border-radius: 10px;
            margin-bottom: 30px;
            display: flex;
            justify-content: space-between;
            align-items: center;
        }

        .header-section h1 {
            margin: 0;
            font-size: 2rem;
        }

        .btn-add-product {
            background-color: #28a745;
            border: none;
        }

        .btn-add-product:hover {
            background-color: #218838;
        }

        .header-actions {
            display: flex;
            gap: 10px;
            align-items: center;
        }

        .product-card {
            background: white;
            border: 1px solid #e0e0e0;
            border-radius: 10px;
            margin-bottom: 20px;
            overflow: hidden;
            transition: box-shadow 0.3s ease;
        }

        .product-card-clickable {
            cursor: pointer;
        }

        .product-card:hover {
            box-shadow: 0 5px 15px rgba(0, 0, 0, 0.1);
        }

        .product-image {
            width: 100%;
            height: 250px;
            object-fit: cover;
            background-color: #f0f0f0;
        }

        .product-info {
            padding: 20px;
        }

        .product-title {
            font-size: 1.3rem;
            font-weight: 600;
            color: #333;
            margin-bottom: 10px;
        }

        .product-description {
            color: #666;
            font-size: 0.95rem;
            margin-bottom: 15px;
            line-height: 1.5;
        }

        .product-details {
            display: grid;
            grid-template-columns: 1fr 1fr;
            gap: 10px;
            margin-bottom: 15px;
            font-size: 0.9rem;
        }

        .detail-item {
            background: #f8f9fa;
            padding: 10px;
            border-radius: 5px;
        }

        .detail-item strong {
            color: #667eea;
        }

        .badge-condition {
            display: inline-block;
            padding: 5px 10px;
            border-radius: 20px;
            font-size: 0.85rem;
            font-weight: 500;
        }

        .condition-ottimo {
            background-color: #d4edda;
            color: #155724;
        }

        .condition-eccellente {
            background-color: #d4edda;
            color: #155724;
        }

        .condition-buono {
            background-color: #cfe2ff;
            color: #084298;
        }

        .condition-discreto {
            background-color: #fff3cd;
            color: #664d03;
        }

        .condition-rovinato {
            background-color: #f8d7da;
            color: #842029;
        }

        .product-actions {
            display: flex;
            gap: 10px;
            margin-top: 15px;
            border-top: 1px solid #e0e0e0;
            padding-top: 15px;
        }

        .modal-header {
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            color: white;
        }

        .modal-content {
            border: 0;
        }

        .modal-footer {
            position: sticky;
            bottom: 0;
            z-index: 2;
            background: #fff;
            border-top: 1px solid #dee2e6;
        }

        .product-form-scroll {
            max-height: 60vh;
            overflow-y: auto;
            overscroll-behavior: contain;
        }

        .form-label {
            font-weight: 600;
            color: #333;
        }

        .empty-state {
            text-align: center;
            padding: 60px 20px;
            color: #999;
        }

        .empty-state i {
            font-size: 4rem;
            margin-bottom: 20px;
            opacity: 0.5;
        }

        .loading {
            display: none;
            text-align: center;
            padding: 20px;
        }

        .spinner-border {
            color: #667eea;
        }

        .alert-notification {
            position: fixed;
            top: 20px;
            right: 20px;
            z-index: 1050;
            min-width: 300px;
        }

        .product-detail-view {
            display: none;
        }

        .product-detail-card {
            background: #fff;
            border: 1px solid #e0e0e0;
            border-radius: 12px;
            overflow: hidden;
        }

        .product-detail-image {
            width: 100%;
            height: 360px;
            object-fit: cover;
            background: #f0f0f0;
        }

        .product-detail-content {
            padding: 20px;
        }

        @media (max-width: 576px) {
            body {
                padding: 10px 0;
            }

            .container-main {
                max-width: 100%;
                padding: 0 12px;
            }

            .header-section {
                flex-direction: column;
                gap: 15px;
                text-align: center;
                padding: 20px;
                margin-bottom: 20px;
            }

            .header-actions {
                width: 100%;
                flex-direction: column;
            }

            .header-section h1 {
                font-size: 1.5rem;
            }

            .product-details {
                grid-template-columns: 1fr;
            }

            .product-actions {
                flex-wrap: wrap;
            }

            .product-actions .btn {
                flex: 1 1 100%;
            }

            .product-image {
                height: 180px;
            }

            .product-info {
                padding: 14px;
            }

            .btn-add-product {
                width: 100%;
            }

            .btn-back-dashboard {
                width: 100%;
            }

            .modal-footer .btn {
                flex: 1 1 auto;
            }

            .product-form-scroll {
                max-height: 58vh;
            }

            .alert-notification {
                left: 12px;
                right: 12px;
                min-width: auto;
            }
        }

        @media (max-width: 992px) {
            .product-image {
                height: 210px;
            }

            .product-detail-image {
                height: 240px;
            }
        }
        </style>
        <script>
            const BASE_URL = '/login/';
            const DEFAULT_IMAGE = BASE_URL + 'IMG/noimage.jpg';
        </script>
</head>
<body>
    <div class="container-main">
        <!-- Header -->
        <div class="header-section">
            <div>
                <h1><i class="bi bi-box"></i> Gestione Prodotti</h1>
                <p class="mb-0 mt-2">Gestisci i tuoi articoli in vendita</p>
            </div>
            <div class="header-actions">
                <a href="/login/visualizzaUtente.php" class="btn btn-outline-light btn-back-dashboard">
                    <i class="bi bi-arrow-left-circle"></i> Torna ai Mockup
                </a>
                <button class="btn btn-light btn-add-product" data-bs-toggle="modal" data-bs-target="#modalNewProduct">
                    <i class="bi bi-plus-circle"></i> Nuovo Prodotto
                </button>
            </div>
        </div>

        <!-- Loading -->
        <div class="loading" id="loading">
            <div class="spinner-border" role="status">
                <span class="visually-hidden">Caricamento...</span>
            </div>
        </div>

        <!-- Lista Prodotti -->
        <div id="productsList" class="row">
            <div class="empty-state">
                <i class="bi bi-inbox"></i>
                <p>Nessun prodotto caricato</p>
            </div>
        </div>

        <div id="productDetailView" class="product-detail-view"></div>
    </div>

    <!-- Modal Nuovo/Modifica Prodotto -->
    <div class="modal fade" id="modalNewProduct" tabindex="-1">
        <div class="modal-dialog modal-dialog-scrollable modal-fullscreen-sm-down">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title" id="modalTitle">Nuovo Prodotto</h5>
                    <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
                </div>
                <form id="formProduct" enctype="multipart/form-data">
                    <div class="modal-body product-form-scroll">
                        <input type="hidden" id="productId" name="idProdotto">

                        <div class="mb-3">
                            <label for="titolo" class="form-label">Titolo *</label>
                            <input type="text" class="form-control" id="titolo" name="titolo" required>
                        </div>

                        <div class="mb-3">
                            <label for="descrizione" class="form-label">Descrizione *</label>
                            <textarea class="form-control" id="descrizione" name="descrizione" rows="3" required></textarea>
                        </div>

                        <div class="mb-3">
                            <label for="categoria" class="form-label">Categoria *</label>
                            <select class="form-select" id="categoria" name="categoria" required>
                                <option value="">Seleziona categoria</option>
                                <option value="Abbigliamento">Abbigliamento</option>
                                <option value="Elettronica">Elettronica</option>
                                <option value="Libri">Libri</option>
                                <option value="Mobili">Mobili</option>
                                <option value="Veicoli">Veicoli</option>
                                <option value="Sport">Sport</option>
                                <option value="Giocattoli">Giocattoli</option>
                                <option value="Altro">Altro</option>
                            </select>
                        </div>

                        <div class="mb-3">
                            <label for="condizioni" class="form-label">Condizioni *</label>
                            <select class="form-select" id="condizioni" name="condizioni" required>
                                <option value="">Seleziona condizioni</option>
                                <option value="Eccellente">Eccellente</option>
                                <option value="Buono">Buono</option>
                                <option value="Discreto">Discreto</option>
                                <option value="Rovinato">Rovinato</option>
                            </select>
                        </div>

                        <div class="mb-3">
                            <label for="disponibilita" class="form-label">Disponibilità</label>
                            <select class="form-select" id="disponibilita" name="disponibilita">
                                <option value="disponibile">Disponibile</option>
                                <option value="scambiato">Scambiato</option>
                            </select>
                        </div>

                        <div class="mb-3">
                            <label for="immagine" class="form-label">Immagine</label>
                            <input type="file" class="form-control" id="immagine" name="immagine" accept="image/*">
                            <small class="text-muted">Formati supportati: JPG, PNG, WebP (Max 5MB)</small>
                        </div>
                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Annulla</button>
                        <button type="submit" class="btn btn-primary" id="submitProductBtn">Aggiungi Prodotto</button>
                    </div>
                </form>
            </div>
        </div>
    </div>

    <!-- Alert Notifiche -->
    <div id="alertContainer"></div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/js/bootstrap.bundle.min.js"></script>
    <script>
        let currentEditingId = null;
        let productsCache = [];
        const modal = new bootstrap.Modal(document.getElementById('modalNewProduct'));

        // Carica prodotti al load
        document.addEventListener('DOMContentLoaded', () => {
            loadProducts();
        });

        // Carica lista prodotti
        function loadProducts() {
            showLoading(true);
            closeProductDetail();
            
            fetch('/login/api/swapper/api_get_products.php', {
                method: 'GET',
                credentials: 'include'
            })
            .then(res => {
                console.log('Response status:', res.status);
                return res.text();
            })
            .then(text => {
                console.log('Raw response:', text);
                try {
                    const data = JSON.parse(text);
                    showLoading(false);
                    
                    if (data.success) {
                        productsCache = data.data || [];
                        renderProducts(data.data);
                    } else {
                        showAlert(data.message || 'Errore caricamento prodotti', 'danger');
                    }
                } catch(e) {
                    showLoading(false);
                    showAlert('Errore: risposta non valida dal server', 'danger');
                    console.error('JSON Parse error:', e, 'Text:', text);
                }
            })
            .catch(err => {
                showLoading(false);
                showAlert('Errore di connessione: ' + err.message, 'danger');
                console.error(err);
            });
        }

        // Render lista prodotti
        function renderProducts(prodotti) {
            const container = document.getElementById('productsList');
            
            if (prodotti.length === 0) {
                container.innerHTML = `
                    <div class="empty-state col-12">
                        <i class="bi bi-inbox"></i>
                        <p>Nessun prodotto caricato</p>
                    </div>
                `;
                return;
            }

            const normalizeImagePath = (img) => {
                if (!img) {
                    return DEFAULT_IMAGE;
                }

                const normalized = String(img).toLowerCase();
                if (normalized.startsWith('uploads/prod/')) {
                    return DEFAULT_IMAGE;
                }

                if (img.startsWith('http://') || img.startsWith('https://') || img.startsWith('/')) {
                    return img;
                }

                return BASE_URL + img;
            };

            container.innerHTML = prodotti.map(p => `
                <div class="col-md-6 col-lg-4">
                    <div class="product-card product-card-clickable" onclick="openProductDetail(${p.idProdotto}, event)">
                        <img src="${normalizeImagePath(p.img)}" alt="${p.Titolo}" class="product-image" onerror="this.onerror=null;this.src='${DEFAULT_IMAGE}';">
                        <div class="product-info">
                            <div class="product-title">${p.Titolo}</div>
                            <p class="product-description">${p.Descrizione.substring(0, 80)}...</p>
                            
                            <div class="product-details">
                                <div class="detail-item">
                                    <strong>Categoria:</strong>
                                    <div>${p.NomeCategoria}</div>
                                </div>
                                <div class="detail-item">
                                    <strong>Data:</strong>
                                    <div>${new Date(p.dataPubblicazione).toLocaleDateString('it-IT')}</div>
                                </div>
                            </div>

                            <div>
                                <span class="badge-condition condition-${p.Condizioni.toLowerCase()}">
                                    ${p.Condizioni}
                                </span>
                                <span class="badge ${(p.Disponibilità === 'disponibile' || p.Disponibilità === 1) ? 'bg-success' : 'bg-danger'} ms-2">
                                    ${p.Disponibilità === 'disponibile' || p.Disponibilità === 1 ? 'Disponibile' : 'Non Disponibile'}
                                </span>
                            </div>

                            <div class="product-actions">
                                <button class="btn btn-sm btn-primary" onclick="event.stopPropagation(); editProduct(${p.idProdotto})">
                                    <i class="bi bi-pencil"></i> Modifica
                                </button>
                                <button class="btn btn-sm btn-danger" onclick="event.stopPropagation(); deleteProduct(${p.idProdotto})">
                                    <i class="bi bi-trash"></i> Elimina
                                </button>
                            </div>
                        </div>
                    </div>
                </div>
            `).join('');
        }

        function openProductDetail(idProdotto, event) {
            if (event && event.target && event.target.closest('.product-actions')) {
                return;
            }

            const prodotto = productsCache.find(p => Number(p.idProdotto) === Number(idProdotto));
            if (!prodotto) {
                showAlert('Prodotto non trovato', 'danger');
                return;
            }

            const detailContainer = document.getElementById('productDetailView');
            const listContainer = document.getElementById('productsList');

            const imagePath = (!prodotto.img)
                ? DEFAULT_IMAGE
                : (prodotto.img.startsWith('http://') || prodotto.img.startsWith('https://') || prodotto.img.startsWith('/'))
                    ? prodotto.img
                    : BASE_URL + prodotto.img;

            const isDisponibile = (prodotto.Disponibilità === 'disponibile' || prodotto.Disponibilità === 1);

            detailContainer.innerHTML = `
                <div class="product-detail-card">
                    <img src="${imagePath}" alt="${prodotto.Titolo}" class="product-detail-image" onerror="this.onerror=null;this.src='${DEFAULT_IMAGE}';">
                    <div class="product-detail-content">
                        <div class="d-flex flex-wrap gap-2 justify-content-between align-items-start mb-3">
                            <h2 class="h4 mb-0">${prodotto.Titolo}</h2>
                            <span class="badge ${isDisponibile ? 'bg-success' : 'bg-danger'}">
                                ${isDisponibile ? 'Disponibile' : 'Non Disponibile'}
                            </span>
                        </div>

                        <p class="product-description mb-4">${prodotto.Descrizione || ''}</p>

                        <div class="product-details mb-4">
                            <div class="detail-item">
                                <strong>Categoria:</strong>
                                <div>${prodotto.NomeCategoria || '-'}</div>
                            </div>
                            <div class="detail-item">
                                <strong>Condizioni:</strong>
                                <div>${prodotto.Condizioni || '-'}</div>
                            </div>
                            <div class="detail-item">
                                <strong>Pubblicato il:</strong>
                                <div>${prodotto.dataPubblicazione ? new Date(prodotto.dataPubblicazione).toLocaleString('it-IT') : '-'}</div>
                            </div>
                            <div class="detail-item">
                                <strong>Utente:</strong>
                                <div>${prodotto.username || '-'}</div>
                            </div>
                        </div>

                        <div class="d-flex flex-wrap gap-2">
                            <button class="btn btn-outline-secondary" onclick="closeProductDetail()">
                                <i class="bi bi-arrow-left"></i> Torna alla lista
                            </button>
                            <button class="btn btn-primary" onclick="closeProductDetail(); editProduct(${prodotto.idProdotto});">
                                <i class="bi bi-pencil"></i> Modifica prodotto
                            </button>
                        </div>
                    </div>
                </div>
            `;

            listContainer.style.display = 'none';
            detailContainer.style.display = 'block';
            window.scrollTo({ top: 0, behavior: 'smooth' });
        }

        function closeProductDetail() {
            const detailContainer = document.getElementById('productDetailView');
            const listContainer = document.getElementById('productsList');

            detailContainer.style.display = 'none';
            detailContainer.innerHTML = '';
            listContainer.style.display = 'flex';
        }

        // Modifica prodotto
        function editProduct(idProdotto) {
            const prodotto = productsCache.find(p => Number(p.idProdotto) === Number(idProdotto));
            if (!prodotto) {
                showAlert('Prodotto non trovato', 'danger');
                return;
            }

            currentEditingId = Number(idProdotto);
            document.getElementById('productId').value = currentEditingId;
            document.getElementById('titolo').value = prodotto.Titolo || '';
            document.getElementById('descrizione').value = prodotto.Descrizione || '';
            document.getElementById('categoria').value = prodotto.NomeCategoria || '';
            document.getElementById('condizioni').value = prodotto.Condizioni || '';
            document.getElementById('disponibilita').value =
                (prodotto.Disponibilità === 'disponibile' || prodotto.Disponibilità === 1) ? 'disponibile' : 'scambiato';
            document.getElementById('modalTitle').textContent = 'Modifica Prodotto';
            document.getElementById('submitProductBtn').textContent = 'Salva Modifiche';
            modal.show();
        }

        // Elimina prodotto
        function deleteProduct(idProdotto) {
            if (confirm('Sei sicuro di voler eliminare questo prodotto?')) {
                fetch('/login/api/swapper/api_delete_product.php', {
                    method: 'DELETE',
                    headers: {
                        'Content-Type': 'application/x-www-form-urlencoded'
                    },
                    body: 'idProdotto=' + idProdotto,
                    credentials: 'include'
                })
                .then(res => res.json())
                .then(data => {
                    if (data.success) {
                        showAlert('Prodotto eliminato', 'success');
                        loadProducts();
                    } else {
                        showAlert(data.message, 'danger');
                    }
                })
                .catch(err => {
                    showAlert('Errore: ' + err.message, 'danger');
                });
            }
        }

        // Salva prodotto (nuovo/modifica)
        document.getElementById('formProduct').addEventListener('submit', function(e) {
            e.preventDefault();

            const formData = new FormData(this);
            if (currentEditingId) {
                formData.set('idProdotto', currentEditingId);
            }
            const url = currentEditingId
                ? '/login/api/swapper/api_update_product.php'
                : '/login/api/swapper/api_upload_product.php';

            fetch(url, {
                method: 'POST',
                body: formData,
                credentials: 'include'
            })
            .then(async res => {
                const rawText = await res.text();
                let data;

                try {
                    data = JSON.parse(rawText);
                } catch (parseErr) {
                    console.error('Risposta non JSON da API prodotto:', rawText);
                    throw new Error('Risposta non valida dal server');
                }

                if (!res.ok) {
                    throw new Error(data.message || 'Errore server');
                }

                return data;
            })
            .then(data => {
                if (!data.success) {
                    throw new Error(data.message || 'Operazione non riuscita');
                }

                showAlert(data.message, 'success');
                modal.hide();
                resetForm();
                loadProducts();
            })
            .catch(err => {
                showAlert('Errore: ' + err.message, 'danger');
            });
        });

        // Reset form
        function resetForm() {
            document.getElementById('formProduct').reset();
            document.getElementById('productId').value = '';
            document.getElementById('modalTitle').textContent = 'Nuovo Prodotto';
            document.getElementById('submitProductBtn').textContent = 'Aggiungi Prodotto';
            currentEditingId = null;
        }

        // Mostra/nascondi loading
        function showLoading(show) {
            document.getElementById('loading').style.display = show ? 'block' : 'none';
        }

        // Mostra alert notifiche
        function showAlert(message, type = 'info') {
            const alertHtml = `
                <div class="alert alert-${type} alert-dismissible fade show alert-notification" role="alert">
                    ${message}
                    <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
                </div>
            `;
            
            const container = document.getElementById('alertContainer');
            const alertDiv = document.createElement('div');
            alertDiv.innerHTML = alertHtml;
            container.appendChild(alertDiv);

            setTimeout(() => {
                alertDiv.remove();
            }, 5000);
        }

        // Reset form quando si apre il modal
        document.getElementById('modalNewProduct').addEventListener('show.bs.modal', () => {
            if (!currentEditingId) {
                resetForm();
            }
        });

        // Evita warning aria-hidden quando il modal viene chiuso con focus interno
        document.getElementById('modalNewProduct').addEventListener('hide.bs.modal', () => {
            if (document.activeElement && typeof document.activeElement.blur === 'function') {
                document.activeElement.blur();
            }
        });
    </script>
</body>
</html>
