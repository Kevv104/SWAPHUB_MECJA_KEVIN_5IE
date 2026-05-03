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

// --- CONFIGURAZIONE AMBIENTE (BYPASS POSTMAN) ---
$isDevelopment = true;

if ($isDevelopment) {
    $username = 'gianno';
} else {
    if (session_status() === PHP_SESSION_NONE) {
        session_start(['cookie_path' => '/login/']);
    }

    if (!isset($_SESSION['jwt'])) {
        http_response_code(401);
        echo json_encode(['success' => false, 'message' => 'Non autorizzato']);
        exit;
    }

    try {
        $decoded = JWT::decode($_SESSION['jwt'], new Key(JWT_SECRET, JWT_ALGO));
        $username = $decoded->sub;
    } catch (Exception $e) {
        http_response_code(401);
        echo json_encode(['success' => false, 'message' => 'Token non valido']);
        exit;
    }
}

// POST: Aggiorna un prodotto
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $idProdotto = $_POST['idProdotto'] ?? '';
    $titolo = $_POST['titolo'] ?? '';
    $descrizione = $_POST['descrizione'] ?? '';
    $condizioniRaw = $_POST['condizioni'] ?? '';
    $condizioni = normalizzaCondizioni($condizioniRaw);
    $categoria = $_POST['categoria'] ?? '';
    $disponibilita = normalizzaDisponibilita($_POST['disponibilita'] ?? 'disponibile');
    
    if (empty($idProdotto) || empty($titolo) || empty($descrizione) || empty($condizioniRaw) || empty($categoria)) {
        http_response_code(400);
        echo json_encode(['success' => false, 'message' => 'Compila tutti i campi obbligatori']);
        exit;
    }
    
    // Verifica che il prodotto appartenga all'utente
    $verificaQuery = "SELECT username, img FROM vista_prodotti WHERE idProdotto = ?";
    $verificaStmt = $connessione->prepare($verificaQuery);
    $verificaStmt->bind_param("i", $idProdotto);
    $verificaStmt->execute();
    $verificaResult = $verificaStmt->get_result();
    
    if ($verificaResult->num_rows === 0) {
        http_response_code(404);
        echo json_encode(['success' => false, 'message' => 'Prodotto non trovato']);
        exit;
    }

    $prodottoEsistente = $verificaResult->fetch_assoc();
    if ($prodottoEsistente['username'] !== $username) {
        http_response_code(403);
        echo json_encode(['success' => false, 'message' => 'Non autorizzato']);
        exit;
    }
    $verificaStmt->close();

    $img_path = $prodottoEsistente['img'] ?: 'IMG/noimage.jpg';

    if (isset($_FILES['immagine']) && $_FILES['immagine']['error'] === 0) {
        $tipiConsentiti = ['image/jpeg', 'image/png', 'image/webp'];
        $maxSize = 5 * 1024 * 1024;

        $mime = mime_content_type($_FILES['immagine']['tmp_name']);
        if (!in_array($mime, $tipiConsentiti, true)) {
            http_response_code(400);
            echo json_encode(['success' => false, 'message' => 'Formato immagine non valido']);
            exit;
        }

        if ($_FILES['immagine']['size'] > $maxSize) {
            http_response_code(400);
            echo json_encode(['success' => false, 'message' => 'Immagine troppo grande']);
            exit;
        }

        $ext = pathinfo($_FILES['immagine']['name'], PATHINFO_EXTENSION);
        $filename = uniqid('prodotto_') . '.' . $ext;
        $destination = __DIR__ . '/../../IMG/' . $filename;

        if (!move_uploaded_file($_FILES['immagine']['tmp_name'], $destination)) {
            http_response_code(500);
            echo json_encode(['success' => false, 'message' => 'Errore caricamento immagine']);
            exit;
        }

        if (!empty($prodottoEsistente['img']) && strpos($prodottoEsistente['img'], 'IMG/') === 0) {
            $vecchioFile = __DIR__ . '/../../' . $prodottoEsistente['img'];
            if (file_exists($vecchioFile) && basename($vecchioFile) !== 'noimage.jpg') {
                @unlink($vecchioFile);
            }
        }

        $img_path = 'IMG/' . $filename;
    }
    
    // Aggiornamento
    $query = "
        UPDATE Prodotto
        SET Titolo = ?, Descrizione = ?, Disponibilità = ?, Condizioni = ?, NomeCategoria = ?, img = ?
        WHERE idProdotto = ? AND User = ?
    ";
    
    $stmt = $connessione->prepare($query);
    $stmt->bind_param("ssssssis", $titolo, $descrizione, $disponibilita, $condizioni, $categoria, $img_path, $idProdotto, $username);
    
    try {
        if ($stmt->execute()) {
            echo json_encode([
                'success' => true,
                'message' => 'Prodotto aggiornato con successo'
            ]);
        } else {
            http_response_code(500);
            echo json_encode(['success' => false, 'message' => 'Errore aggiornamento prodotto']);
        }
    } catch (Throwable $e) {
        http_response_code(500);
        echo json_encode(['success' => false, 'message' => 'Errore aggiornamento prodotto: ' . $e->getMessage()]);
    }
    $stmt->close();
    exit;
}

http_response_code(405);
echo json_encode(['success' => false, 'message' => 'Metodo non consentito']);
?>
