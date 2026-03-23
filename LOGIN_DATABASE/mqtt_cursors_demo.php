<?php
session_start(['cookie_path' => '/login/']);

require_once __DIR__ . '/vendor/autoload.php';
require_once 'jwt.php';
use Firebase\JWT\JWT;
use Firebase\JWT\Key;

//identifica utente (usa JWT se loggato, altrimenti random)
$username = 'Guest_' . rand(1000, 9999);

if(isset($_SESSION['jwt'])) {
    try {
        $decoded = JWT::decode($_SESSION['jwt'], new Key(JWT_SECRET, JWT_ALGO));
        $username = $decoded->sub;
    } catch(Exception $e) {
        //usa guest se JWT fallisce
    }
}

//colore casuale per l'utente
$colors = ['#FF5733', '#33FF57', '#3357FF', '#FF33A1', '#FFD700', '#00CED1'];
$userColor = $colors[array_rand($colors)];
?>
<!DOCTYPE html>
<html lang="it">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Demo MQTT - Cursori Multiutente</title>
    <style>
        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
        }
        
        body {
            font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            height: 100vh;
            overflow: hidden;
        }
        
        .header {
            background: rgba(255, 255, 255, 0.95);
            padding: 15px 30px;
            box-shadow: 0 2px 10px rgba(0,0,0,0.1);
            display: flex;
            justify-content: space-between;
            align-items: center;
        }
        
        .header h1 {
            font-size: 24px;
            color: #333;
        }
        
        .user-info {
            display: flex;
            align-items: center;
            gap: 10px;
        }
        
        .user-badge {
            background: <?php echo $userColor; ?>;
            color: white;
            padding: 8px 16px;
            border-radius: 20px;
            font-weight: bold;
        }
        
        .status {
            padding: 8px 16px;
            border-radius: 20px;
            font-size: 14px;
            font-weight: bold;
        }
        
        .status.connected {
            background: #10b981;
            color: white;
        }
        
        .status.disconnected {
            background: #ef4444;
            color: white;
        }
        
        .canvas-area {
            position: relative;
            width: 100%;
            height: calc(100vh - 70px);
            background: white;
            cursor: crosshair;
        }
        
        .cursor {
            position: absolute;
            width: 20px;
            height: 20px;
            border-radius: 50%;
            pointer-events: none;
            transition: all 0.1s ease;
            z-index: 1000;
        }
        
        .cursor-label {
            position: absolute;
            top: 25px;
            left: 25px;
            background: rgba(0, 0, 0, 0.8);
            color: white;
            padding: 4px 8px;
            border-radius: 4px;
            font-size: 12px;
            white-space: nowrap;
            pointer-events: none;
        }
        
        .users-list {
            position: fixed;
            bottom: 20px;
            right: 20px;
            background: rgba(255, 255, 255, 0.95);
            padding: 15px;
            border-radius: 10px;
            box-shadow: 0 4px 15px rgba(0,0,0,0.2);
            max-width: 250px;
        }
        
        .users-list h3 {
            font-size: 14px;
            margin-bottom: 10px;
            color: #666;
        }
        
        .user-item {
            display: flex;
            align-items: center;
            gap: 8px;
            margin: 5px 0;
            font-size: 13px;
        }
        
        .user-dot {
            width: 12px;
            height: 12px;
            border-radius: 50%;
        }
        
        .instructions {
            position: fixed;
            top: 100px;
            left: 50%;
            transform: translateX(-50%);
            background: rgba(255, 255, 255, 0.95);
            padding: 20px 30px;
            border-radius: 10px;
            box-shadow: 0 4px 15px rgba(0,0,0,0.2);
            text-align: center;
            animation: fadeOut 5s forwards;
        }
        
        @keyframes fadeOut {
            0%, 70% { opacity: 1; }
            100% { opacity: 0; pointer-events: none; }
        }
        
        .instructions h2 {
            color: #667eea;
            margin-bottom: 10px;
        }
        
        .instructions p {
            color: #666;
            font-size: 14px;
        }
    </style>
</head>
<body>
    <div class="header">
        <h1>🎯 Demo MQTT - Cursori Multiutente</h1>
        <div class="user-info">
            <div class="user-badge">👤 <?php echo $username; ?></div>
            <div class="status disconnected" id="status">● Disconnesso</div>
        </div>
    </div>
    
    <div class="instructions">
        <h2>📡 Apri questa pagina in più browser/dispositivi!</h2>
        <p>Vedrai i cursori degli altri utenti in tempo reale</p>
    </div>
    
    <div class="canvas-area" id="canvas"></div>
    
    <div class="users-list">
        <h3>👥 Utenti connessi: <span id="user-count">0</span></h3>
        <div id="users-container"></div>
    </div>
    
    <script src="https://unpkg.com/mqtt/dist/mqtt.min.js"></script>
    <script>
        //configurazione
        const username = '<?php echo $username; ?>';
        const userColor = '<?php echo $userColor; ?>';
        const mqttTopic = 'swaphub/demo/cursors';
        
        //cursori degli altri utenti
        const cursors = {};
        const connectedUsers = new Set();
        
        const brokerUrl = 'wss://vic-searches-listings-budget.trycloudflare.com';
        const client = mqtt.connect(brokerUrl);
        
        //eventi connessione
        client.on('connect', () => {
            console.log('✅ Connesso a MQTT broker');
            document.getElementById('status').className = 'status connected';
            document.getElementById('status').textContent = '● Connesso';
            
            //sottoscrivi al topic
            client.subscribe(mqttTopic);
            
            //invia presenza ogni 2 secondi
            setInterval(() => {
                sendPresence();
            }, 2000);
        });
        
        client.on('error', (err) => {
            console.error('❌ Errore connessione MQTT:', err);
            document.getElementById('status').className = 'status disconnected';
            document.getElementById('status').textContent = '● Errore';
        });
        
        //ricevi messaggi
        client.on('message', (topic, message) => {
            try {
                const data = JSON.parse(message.toString());
                
                //ignora i propri messaggi
                if(data.username === username) return;
                
                if(data.type === 'cursor') {
                    updateCursor(data.username, data.x, data.y, data.color);
                    connectedUsers.add(data.username);
                } else if(data.type === 'presence') {
                    connectedUsers.add(data.username);
                    if(!cursors[data.username]) {
                        updateCursor(data.username, -100, -100, data.color);
                    }
                }
                
                updateUsersList();
            } catch(e) {
                console.error('Errore parsing messaggio:', e);
            }
        });
        
        //invia posizione cursore
        document.getElementById('canvas').addEventListener('mousemove', (e) => {
            const x = e.clientX;
            const y = e.clientY;
            
            const payload = JSON.stringify({
                type: 'cursor',
                username: username,
                x: x,
                y: y,
                color: userColor,
                timestamp: Date.now()
            });
            
            if(client.connected) {
                client.publish(mqttTopic, payload);
            }
        });
        
        //invia presenza
        function sendPresence() {
            const payload = JSON.stringify({
                type: 'presence',
                username: username,
                color: userColor,
                timestamp: Date.now()
            });
            
            if(client.connected) {
                client.publish(mqttTopic, payload);
            }
        }
        
        //aggiorna cursore
        function updateCursor(user, x, y, color) {
            if(!cursors[user]) {
                //crea nuovo cursore
                const cursorDiv = document.createElement('div');
                cursorDiv.className = 'cursor';
                cursorDiv.id = 'cursor-' + user;
                cursorDiv.style.background = color;
                
                const label = document.createElement('div');
                label.className = 'cursor-label';
                label.textContent = user;
                cursorDiv.appendChild(label);
                
                document.getElementById('canvas').appendChild(cursorDiv);
                cursors[user] = cursorDiv;
            }
            
            //aggiorna posizione
            cursors[user].style.left = x + 'px';
            cursors[user].style.top = y + 'px';
            
            //rimuovi cursori inattivi dopo 5 secondi
            clearTimeout(cursors[user].timeout);
            cursors[user].timeout = setTimeout(() => {
                if(cursors[user]) {
                    cursors[user].remove();
                    delete cursors[user];
                    connectedUsers.delete(user);
                    updateUsersList();
                }
            }, 5000);
        }
        
        //aggiorna lista utenti
        function updateUsersList() {
            const container = document.getElementById('users-container');
            container.innerHTML = '';
            
            connectedUsers.forEach(user => {
                const item = document.createElement('div');
                item.className = 'user-item';
                
                const dot = document.createElement('div');
                dot.className = 'user-dot';
                dot.style.background = cursors[user] ? cursors[user].style.background : '#ccc';
                
                const name = document.createElement('span');
                name.textContent = user;
                
                item.appendChild(dot);
                item.appendChild(name);
                container.appendChild(item);
            });
            
            document.getElementById('user-count').textContent = connectedUsers.size;
        }
        
        //disconnessione
        window.addEventListener('beforeunload', () => {
            if(client.connected) {
                client.end();
            }
        });
    </script>
</body>
</html>