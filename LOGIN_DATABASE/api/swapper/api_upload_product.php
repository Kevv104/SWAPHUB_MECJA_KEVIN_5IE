<?php

require_once '../../vendor/autoload.php';
require_once '../../connectdb.php';
require_once '../../jwt.php';
require_once '../../sicurezzaRotte.php';

use Firebase\JWT\JWT;
use Firebase\JWT\Key;

header('Content-Type: application/json');

function normalizzaDisponibilita($value) {
    $raw = strtolower(trim((string)$value));

    if ($raw === '1' || $raw === 'disponibile') {
        return 'disponibile';
    }

    if ($raw === '0' || $raw === 'scambiato' || $raw === 'non disponibile') {
        return 'scambiato';
    }

    return 'disponibile';
}

function normalizzaCondizioni($value) {
    $raw = strtolower(trim((string)$value));

    if ($raw === 'ottimo' || $raw === 'eccellente' || $raw === 'excellent') {
        return 'Eccellente';
    }

    if ($raw === 'buono' || $raw === 'good') {
        return 'Buono';
    }

    if ($raw === 'discreto' || $raw === 'fair') {
        return 'Discreto';
    }

    if ($raw === 'rovinato' || $raw === 'scarso' || $raw === 'poor') {
        return 'Rovinato';
    }

    // Default sicuro su un valore normalmente presente nell'enum.
    return 'Buono';
}

// --- CONFIGURAZIONE AMBIENTE ---
if (session_status() === PHP_SESSION_NONE) {
    session_start(['cookie_path' => '/login/']);
}

// Bypass per development/Postman
$isLocalhost = in_array($_SERVER['REMOTE_ADDR'], ['127.0.0.1', '::1']) || 
               (isset($_SERVER['HTTP_HOST']) && strpos($_SERVER['HTTP_HOST'], 'localhost') !== false);

$username = null;

if (isset($_SESSION['jwt'])) {
    try {
        $decoded = JWT::decode($_SESSION['jwt'], new Key(JWT_SECRET, JWT_ALGO));
        $username = $decoded->sub;
    } catch (Exception $e) {
        http_response_code(401);
        echo json_encode(['success' => false, 'message' => 'Token non valido']);
        exit;
    }
} elseif ($isLocalhost) {
    $username = 'gianno';
} else {
    http_response_code(401);
    echo json_encode(['success' => false, 'message' => 'Non autorizzato']);
    exit;
}

// POST: Carica un nuovo prodotto
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $titolo = $_POST['titolo'] ?? '';
    $descrizione = $_POST['descrizione'] ?? '';
    $condizioniRaw = $_POST['condizioni'] ?? '';
    $condizioni = normalizzaCondizioni($condizioniRaw);
    $categoria = $_POST['categoria'] ?? '';
    $disponibilita = normalizzaDisponibilita($_POST['disponibilita'] ?? 'disponibile');
    
    if (empty($titolo) || empty($descrizione) || empty($condizioniRaw) || empty($categoria)) {
        http_response_code(400);
        echo json_encode(['success' => false, 'message' => 'Compila tutti i campi obbligatori']);
        exit;
    }
    
    // Gestione upload immagine
    $img_path = 'IMG/noimage.jpg';
    if (isset($_FILES['immagine']) && $_FILES['immagine']['error'] === 0) {
        $tipiConsentiti = ['image/jpeg', 'image/png', 'image/webp'];
        $maxSize = 5 * 1024 * 1024; // 5MB
        
        $mime = mime_content_type($_FILES['immagine']['tmp_name']); //verifica tipo MIME per sicurezza
        
        if (!in_array($mime, $tipiConsentiti)) { //controllo tipo MIME per sicurezza
            http_response_code(400);
            echo json_encode(['success' => false, 'message' => 'Formato immagine non valido']);
            exit;
        }
        
        if ($_FILES['immagine']['size'] > $maxSize) {
            http_response_code(400);
            echo json_encode(['success' => false, 'message' => 'Immagine troppo grande']);
            exit;
        }
        
        $ext = pathinfo($_FILES['immagine']['name'], PATHINFO_EXTENSION); //estensione originale per mantenere formato
        $filename = uniqid("prodotto_") . '.' . $ext; //nome univoco per evitare conflitti
            $destination = __DIR__ . '/../../IMG/' . $filename;
            $img_path_db = 'IMG/' . $filename;
        
        if (!move_uploaded_file($_FILES['immagine']['tmp_name'], $destination)) { //spostamento file con controllo di successo
            http_response_code(400);
            echo json_encode(['success' => false, 'message' => 'Errore caricamento immagine']);
            exit;
        }
        
            $img_path = $img_path_db;
    }
    
    // Inserimento nel database
    $query = "
        INSERT INTO Prodotto 
        (Titolo, Descrizione, Disponibilità, Condizioni, NomeCategoria, img, User, dataPubblicazione)
        VALUES (?, ?, ?, ?, ?, ?, ?, NOW())
    ";
    
    $stmt = $connessione->prepare($query);
    $stmt->bind_param("sssssss", $titolo, $descrizione, $disponibilita, $condizioni, $categoria, $img_path, $username);
    
    try {
        if ($stmt->execute()) {
            $idProdotto = $connessione->insert_id;
            echo json_encode([
                'success' => true,
                'message' => 'Prodotto caricato con successo',
                'idProdotto' => $idProdotto
            ]);
        } else {
            http_response_code(500);
            echo json_encode(['success' => false, 'message' => 'Errore inserimento prodotto']);
        }
    } catch (Throwable $e) {
        http_response_code(500);
        echo json_encode(['success' => false, 'message' => 'Errore inserimento prodotto: ' . $e->getMessage()]);
    }
    $stmt->close();
    exit;
}

http_response_code(405);
echo json_encode(['success' => false, 'message' => 'Metodo non consentito']);
?>
