<?php
/**
 * API: Invia una segnalazione su uno Swapper
 * 
 * Metodo: POST
 * Permesso: send_report
 * Input: { userSegnalato, idMotivo, descrizione }
 * 
 * Response: { success, idSegnalazione, message }
 */

header('Content-Type: application/json; charset=utf-8');

session_start();

require_once __DIR__ . '/../../vendor/autoload.php';
require_once __DIR__ . '/../../jwt.php';
require_once __DIR__ . '/../../connectdb.php';

use Firebase\JWT\JWT;
use Firebase\JWT\Key;

$conn = $connessione;

try {
    //Verifica JWT
    if(!isset($_SESSION['jwt'])) {
        throw new Exception('Non autenticato', 401);
    }

    $jwt = $_SESSION['jwt'];
    $decoded = JWT::decode($jwt, new Key(JWT_SECRET, JWT_ALGO));
    if(!$decoded) {
        throw new Exception('JWT non valido', 401);
    }

    $currentUser = $decoded->sub;

    // Step 2: Leggi input JSON
    $input = json_decode(file_get_contents('php://input'), true);
    
    if(!$input) {
        throw new Exception('Richiesta JSON non valida');
    }

    $userSegnalato = $input['userSegnalato'] ?? null;
    $idMotivo = (int)($input['idMotivo'] ?? 0);
    $descrizione = trim($input['descrizione'] ?? '');

    // Validazioni
    if(!$userSegnalato) {
        throw new Exception('Utente da segnalare non specificato');
    }

    if($userSegnalato === $currentUser) {
        throw new Exception('Non puoi segnalare te stesso');
    }

    if($idMotivo <= 0) {
        throw new Exception('Motivo della segnalazione non valido');
    }

    if(strlen($descrizione) > 500) {
        $descrizione = substr($descrizione, 0, 500);
    }

    //Verifica che utente segnalato sia uno Swapper
    $checkSwapperSql = "SELECT ur.idRuolo FROM UtenteRuolo ur
                        INNER JOIN Ruolo r ON ur.idRuolo = r.idRuolo
                        WHERE ur.username = ? AND r.nomeRuolo = 'Swapper'";
 
    
    $checkStmt = $conn->prepare($checkSwapperSql);
    if(!$checkStmt) {
        throw new Exception('Errore DB: ' . $conn->error);
    }

    $checkStmt->bind_param('s', $userSegnalato);
    $checkStmt->execute();
    $checkResult = $checkStmt->get_result();
    
    if($checkResult->num_rows === 0) { //utente segnalato non è uno Swapper o non esiste
        throw new Exception('Utente da segnalare non è uno Swapper', 403);
    }
    $checkStmt->close();

    //Verifica che il motivo sia valido
    $checkMotiveSql = "SELECT idMotivo FROM MotiviBan WHERE idMotivo = ?";
    
    $checkMotiveStmt = $conn->prepare($checkMotiveSql);
    if(!$checkMotiveStmt) {
        throw new Exception('Errore DB: ' . $conn->error);
    }

    $checkMotiveStmt->bind_param('i', $idMotivo);
    $checkMotiveStmt->execute();
    $checkMotiveResult = $checkMotiveStmt->get_result();
    
    if($checkMotiveResult->num_rows === 0) { //motivo non valido
        throw new Exception('Motivo della segnalazione non valido', 400);
    }
    $checkMotiveStmt->close();

    //Controlla se c'è già una segnalazione aperta dello stesso utente verso lo stesso Swapper
    $checkExistingSql = "SELECT idSegnalazione FROM Segnalazioni 
                         WHERE UserSegnalazione = ? 
                           AND UserSegnalato = ? 
                           AND stato = 'aperta'
                         LIMIT 1";
    
    $checkExistingStmt = $conn->prepare($checkExistingSql);
    if(!$checkExistingStmt) {
        throw new Exception('Errore DB: ' . $conn->error);
    }

    $checkExistingStmt->bind_param('ss', $currentUser, $userSegnalato);
    $checkExistingStmt->execute();
    $checkExistingResult = $checkExistingStmt->get_result();
    
    if($checkExistingResult->num_rows > 0) {
        // Segnalazione già esistente - non inserire duplicato
        $row = $checkExistingResult->fetch_assoc();
        http_response_code(200);
        echo json_encode([
            'success' => true,
            'idSegnalazione' => $row['idSegnalazione'],
            'message' => 'Segnalazione già presente (non duplicata)'
        ]);
        $checkExistingStmt->close();
        $conn->close();
        exit;
    }
    $checkExistingStmt->close();

    //BEGIN Transaction per garantire integrità tra inserimento segnalazione e eventuali operazioni future (es. notifiche)
    $conn->begin_transaction();

    try {
        // Inserisci segnalazione
        $insertSql = "INSERT INTO Segnalazioni 
                      (UserSegnalazione, Tipo, UserSegnalato, commento, stato, dataInvio)
                      VALUES (?, 'utente', ?, ?, 'aperta', NOW())";
        
        $insertStmt = $conn->prepare($insertSql);
        if(!$insertStmt) {
            throw new Exception('Errore DB: ' . $conn->error);
        }

        // Aggiungi il motivo nella descrizione se esiste
        $nomeMotivo = '';
        $getMotivoSql = "SELECT nomeMotivo FROM MotiviBan WHERE idMotivo = ?";
        $getMotivoStmt = $conn->prepare($getMotivoSql);
        $getMotivoStmt->bind_param('i', $idMotivo);
        $getMotivoStmt->execute();
        $getMotivoResult = $getMotivoStmt->get_result();
        if($getMotivoResult->num_rows > 0) {
            $row = $getMotivoResult->fetch_assoc();
            $nomeMotivo = $row['nomeMotivo'];
        }
        $getMotivoStmt->close();

        // Formatta il commento con motivo e descrizione
        $commento = "**Motivo**: $nomeMotivo\n";
        if($descrizione) {
            $commento .= "**Descrizione**: $descrizione";
        }

        $insertStmt->bind_param('sss', $currentUser, $userSegnalato, $commento);
        $insertStmt->execute();
        $idSegnalazione = $conn->insert_id;
        $insertStmt->close();

        if($idSegnalazione <= 0) {
            throw new Exception('Errore nell\'inserimento della segnalazione');
        }

        // Commit transaction
        $conn->commit();

        // Step 7: Restituisci successo
        http_response_code(201);
        echo json_encode([
            'success' => true,
            'idSegnalazione' => $idSegnalazione,
            'message' => 'Segnalazione inviata con successo'
        ]);

    } catch(Exception $e) {
        // Rollback transaction se errore
        $conn->rollback();
        throw $e;
    }

} catch(Exception $e) { //gestione errori generale
    http_response_code($e->getCode() ?: 400);
    echo json_encode([
        'success' => false,
        'error' => $e->getMessage()
    ]);
}

$conn->close();
