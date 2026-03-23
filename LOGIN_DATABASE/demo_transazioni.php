<!doctype html>
<html lang="it">
<head>
  <title>Demo Transazioni PDO - SwapHub</title>
  <meta charset="utf-8" />
  <meta name="viewport" content="width=device-width, initial-scale=1" />
  <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet" />
  <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.1/font/bootstrap-icons.css">
  <style>
    .code-block {
      background: #f8f9fa;
      border-left: 4px solid #007bff;
      padding: 15px;
      font-family: 'Courier New', monospace;
      font-size: 0.9rem;
    }
    .result-box {
      background: #fff;
      border: 2px solid #dee2e6;
      padding: 20px;
      border-radius: 8px;
      min-height: 200px;
      max-height: 400px;
      overflow-y: auto;
    }
    .error-text { color: #dc3545; font-weight: bold; }
    .success-text { color: #28a745; font-weight: bold; }
    .warning-text { color: #ffc107; font-weight: bold; }
  </style>
</head>
<body style="background-color: #f0f2f5;">
  <main class="container py-5">
    
    <div class="text-center mb-5">
      <h1 class="fw-bold">🔬 Demo: Transazioni PDO</h1>
      <p class="text-muted">Dimostrazione pratica dell'importanza delle transazioni nei database</p>
    </div>

    <!-- Spiegazione -->
    <div class="row mb-4">
      <div class="col-12">
        <div class="card">
          <div class="card-body">
            <h3><i class="bi bi-info-circle me-2"></i>Scenario</h3>
            <p>Creiamo una chat con <strong>3 partecipanti</strong>. Al <strong>2° partecipante</strong> viene simulato un errore.</p>
            
            <div class="row mt-3">
              <div class="col-md-6">
                <div class="alert alert-danger">
                  <h5>❌ SENZA Transazioni</h5>
                  <ul class="mb-0">
                    <li>Chat viene creata ✓</li>
                    <li>1° partecipante aggiunto ✓</li>
                    <li>2° partecipante → <strong>ERRORE</strong> ✗</li>
                    <li><strong class="error-text">Risultato: DATABASE INCONSISTENTE</strong></li>
                    <li>Chat esiste ma incompleta!</li>
                  </ul>
                </div>
              </div>
              <div class="col-md-6">
                <div class="alert alert-success">
                  <h5>✅ CON Transazioni</h5>
                  <ul class="mb-0">
                    <li>BEGIN TRANSACTION</li>
                    <li>Chat creata (temporaneo)</li>
                    <li>1° partecipante aggiunto (temporaneo)</li>
                    <li>2° partecipante → <strong>ERRORE</strong> ✗</li>
                    <li><strong class="success-text">ROLLBACK → TUTTO ANNULLATO</strong></li>
                    <li><strong class="success-text">DATABASE CONSISTENTE</strong></li>
                  </ul>
                </div>
              </div>
            </div>
          </div>
        </div>
      </div>
    </div>

    <!-- Pulsanti Test -->
    <div class="row mb-4">
      <div class="col-md-6">
        <div class="card h-100">
          <div class="card-header bg-danger text-white">
            <h5 class="mb-0"><i class="bi bi-x-circle me-2"></i>Test SENZA Transazioni</h5>
          </div>
          <div class="card-body">
            <p>Esegue operazioni database <strong>senza protezione</strong>.</p>
            <button class="btn btn-danger w-100" onclick="testNoTransaction()">
              <i class="bi bi-play-fill me-2"></i>
              Esegui SENZA Transazioni
            </button>
          </div>
        </div>
      </div>
      
      <div class="col-md-6">
        <div class="card h-100">
          <div class="card-header bg-success text-white">
            <h5 class="mb-0"><i class="bi bi-check-circle me-2"></i>Test CON Transazioni</h5>
          </div>
          <div class="card-body">
            <p>Usa <code>BEGIN</code>, <code>COMMIT</code>, <code>ROLLBACK</code> PDO.</p>
            <button class="btn btn-success w-100" onclick="testWithTransaction()">
              <i class="bi bi-play-fill me-2"></i>
              Esegui CON Transazioni
            </button>
          </div>
        </div>
      </div>
    </div>

    <!-- Risultati -->
    <div class="row mb-4">
      <div class="col-md-6">
        <h5>Output SENZA Transazioni:</h5>
        <div id="result-no-transaction" class="result-box">
          <p class="text-muted">Clicca il pulsante rosso per eseguire...</p>
        </div>
      </div>
      <div class="col-md-6">
        <h5>Output CON Transazioni:</h5>
        <div id="result-with-transaction" class="result-box">
          <p class="text-muted">Clicca il pulsante verde per eseguire...</p>
        </div>
      </div>
    </div>

    <!-- Verifica Database -->
    <div class="row">
      <div class="col-12">
        <div class="card">
          <div class="card-header bg-primary text-white">
            <h5 class="mb-0"><i class="bi bi-database me-2"></i>Verifica Stato Database</h5>
          </div>
          <div class="card-body">
            <p>Dopo aver eseguito i test, controlla il database con queste query:</p>
            <div class="code-block">
              <code>
                -- Vedi le chat create (dovrebbe esserci solo quella SENZA transazioni)<br>
                SELECT * FROM Chat WHERE nome LIKE 'Test Chat%' ORDER BY idChat DESC LIMIT 5;<br><br>
                -- Vedi i partecipanti<br>
                SELECT * FROM PartecipaChat WHERE idChat IN (SELECT idChat FROM Chat WHERE nome LIKE 'Test Chat%');<br><br>
                -- Conta: quanti partecipanti ha ogni chat di test?<br>
                SELECT c.idChat, c.nome, COUNT(p.User) as numPartecipantiReali<br>
                FROM Chat c<br>
                LEFT JOIN PartecipaChat p ON c.idChat = p.idChat<br>
                WHERE c.nome LIKE 'Test Chat%'<br>
                GROUP BY c.idChat;
              </code>
            </div>
            <div class="alert alert-warning mt-3">
              <strong>🔍 Risultato Atteso:</strong><br>
              - Chat "Test Chat NO TRANSACTION": esiste con 1 partecipante (inconsistente!)<br>
              - Chat "Test Chat WITH TRANSACTION": NON esiste (rollback eseguito correttamente)
            </div>
          </div>
        </div>
      </div>
    </div>

  </main>

  <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/js/bootstrap.bundle.min.js"></script>
  
  <script>
    //test senza transazioni
    function testNoTransaction() {
      const resultDiv = document.getElementById('result-no-transaction');
      resultDiv.innerHTML = '<div class="spinner-border text-danger"></div> Esecuzione in corso...';
      
      fetch('/login/api/demo/api_demo_NO_TRANSACTION.php', {
        method: 'POST',
        headers: { 'Content-Type': 'application/json' },
        credentials: 'include',
        body: JSON.stringify({
          nome: 'Test Chat NO TRANSACTION ' + new Date().toLocaleTimeString(),
          partecipanti: ['pietro', 'gianno', 'mario'] //3 partecipanti, errore al 2°
        })
      })
        .then(res => res.text()) // ← Cambiato da .json() a .text()
        .then(text => {
          // Mostra la risposta grezza
          console.log('RISPOSTA API:', text);
          
          // Prova a parsare come JSON
          try {
            const data = JSON.parse(text);
            let html = '<div class="mb-3">';
            html += '<strong class="error-text">❌ RISULTATO SENZA TRANSAZIONI:</strong><br>';
            html += '<pre class="mt-2">' + JSON.stringify(data, null, 2) + '</pre>';
            html += '</div>';
            html += '<div class="alert alert-danger">';
            html += '<strong>⚠️ PROBLEMA:</strong><br>';
            html += 'Chat e primo partecipante SALVATI nel database anche se c\'è stato un errore!<br>';
            html += 'Verifica con le query SQL sopra.';
            html += '</div>';
            resultDiv.innerHTML = html;
          } catch(e) {
            // Se non è JSON, mostra il testo HTML grezzo
            resultDiv.innerHTML = '<div class="alert alert-danger"><strong>ERRORE PHP:</strong><pre>' + text + '</pre></div>';
          }
        })
        .catch(err => {
          resultDiv.innerHTML = '<div class="alert alert-danger">Errore: ' + err.message + '</div>';
        });
    }

    //test con transazioni
    function testWithTransaction() {
      const resultDiv = document.getElementById('result-with-transaction');
      resultDiv.innerHTML = '<div class="spinner-border text-success"></div> Esecuzione in corso...';
      
      fetch('/login/api/demo/api_demo_WITH_TRANSACTION.php', {
        method: 'POST',
        headers: { 'Content-Type': 'application/json' },
        credentials: 'include',
        body: JSON.stringify({
          nome: 'Test Chat WITH TRANSACTION ' + new Date().toLocaleTimeString(),
          partecipanti: ['pietro', 'gianno', 'mario'] //3 partecipanti, errore al 2°
        })
      })
        .then(res => res.text()) // ← Cambiato da .json() a .text()
        .then(text => {
          // Mostra la risposta grezza
          console.log('RISPOSTA API:', text);
          
          // Prova a parsare come JSON
          try {
            const data = JSON.parse(text);
            let html = '<div class="mb-3">';
            html += '<strong class="success-text">✅ RISULTATO CON TRANSAZIONI:</strong><br>';
            html += '<pre class="mt-2">' + JSON.stringify(data, null, 2) + '</pre>';
            html += '</div>';
            html += '<div class="alert alert-success">';
            html += '<strong>✅ CORRETTO:</strong><br>';
            html += 'ROLLBACK eseguito → NESSUNA modifica salvata nel database!<br>';
            html += 'Verifica con le query SQL sopra: questa chat NON esiste.';
            html += '</div>';
            resultDiv.innerHTML = html;
          } catch(e) {
            // Se non è JSON, mostra il testo HTML grezzo
            resultDiv.innerHTML = '<div class="alert alert-danger"><strong>ERRORE PHP:</strong><pre>' + text + '</pre></div>';
          }
        })
        .catch(err => {
          resultDiv.innerHTML = '<div class="alert alert-danger">Errore: ' + err.message + '</div>';
        });
    }
  </script>
</body>
</html>