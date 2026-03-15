# 📚 DOCUMENTAZIONE ROTTE - SwapHub

*Versione: 1.0 - 15 Marzo 2026*

---

## 📖 Indice

- [Introduzione](#introduzione)
- [Architettura](#architettura)
- [Categorie Rotte](#categorie-rotte)
  - [Chat](#1-chat-5-rotte)
  - [Social](#2-social-5-rotte)
  - [Prodotti e Scambi](#3-prodotti-e-scambi-5-rotte)
  - [Moderazione](#4-moderazione-5-rotte)
  - [Amministrazione](#5-amministrazione-10-rotte)
- [Template Implementazione](#template-implementazione)
- [Convenzioni](#convenzioni)

---

## 🎯 Introduzione

Questo documento elenca **tutte le 30 rotte** del sistema SwapHub, mappate dai permessi definiti nel database.

Ogni rotta segue questo pattern:
```
Utente → mockup_manager.php?azione=PERMESSO → Vista → API → VIEW SQL → Database
```

---

## 🏗️ Architettura

### **Pattern Standard per Ogni Rotta:**

```
┌─────────────────────────────────────────────────────────┐
│ 1. PERMESSO (tabella Permesso nel DB)                  │
│    - ID univoco                                          │
│    - Nome (es: view_chat, send_message)                 │
│    - Categoria (chat, social, market, mod, admin)       │
└─────────────────────────────────────────────────────────┘
                           ↓
┌─────────────────────────────────────────────────────────┐
│ 2. ROTTA (mockup_manager.php)                          │
│    - URL: /login/mockup_manager.php?azione=PERMESSO    │
│    - Verifica autenticazione JWT                        │
│    - Verifica permesso utente (proteggereRotta)         │
└─────────────────────────────────────────────────────────┘
                           ↓
┌─────────────────────────────────────────────────────────┐
│ 3. VISTA HTML (visteSQL/RUOLO/nome_vista.php)          │
│    - Interfaccia utente                                  │
│    - Chiama API via JavaScript fetch                     │
│    - Renderizza dati ricevuti                            │
└─────────────────────────────────────────────────────────┘
                           ↓
┌─────────────────────────────────────────────────────────┐
│ 4. API (api/RUOLO/api_nome.php)                        │
│    - Verifica JWT                                        │
│    - Interroga VIEW SQL o tabelle                        │
│    - Restituisce JSON                                    │
└─────────────────────────────────────────────────────────┘
                           ↓
┌─────────────────────────────────────────────────────────┐
│ 5. VIEW SQL (opzionale, per query complesse)            │
│    - Calcoli automatici                                  │
│    - JOIN pre-configurati                                │
│    - Semplifica API                                      │
└─────────────────────────────────────────────────────────┘
```

---

## 🗂️ Categorie Rotte

---

## 1. CHAT (5 rotte)

### **1.1 create_chat**
- **Permesso ID**: 1
- **Nome**: `create_chat`
- **Ruolo**: Swapper
- **Descrizione**: Crea una nuova chat (privata o di gruppo)
- **Tipo**: CREATE
- **URL**: `/login/mockup_manager.php?azione=create_chat`
- **Vista**: `visteSQL/swapper/create_chat.php`
- **API**: 
  - GET: `api/swapper/api_get_available_users.php` (lista utenti)
  - POST: `api/swapper/api_create_chat.php` (crea chat)
- **VIEW SQL**: Nessuna
- **Tabelle**: `Chat`, `PartecipaChat`
- **Input**: nome, tipo (privata/gruppo), descrizione, partecipanti[]
- **Output**: idChat creato, messaggio successo

---

### **1.2 send_message**
- **Permesso ID**: 2
- **Nome**: `send_message`
- **Ruolo**: Swapper
- **Descrizione**: Invia un messaggio in una chat
- **Tipo**: CREATE
- **URL**: `/login/mockup_manager.php?azione=send_message`
- **Vista**: `visteSQL/swapper/send_message.php`
- **API**: 
  - POST: `api/swapper/api_send_message.php`
- **VIEW SQL**: `vista_chat_utente` (per vedere le chat disponibili)
- **Tabelle**: `Messaggi`
- **Input**: idChat, contenuto
- **Output**: idMessaggio, dataInvio

---

### **1.3 view_chat**
- **Permesso ID**: 3
- **Nome**: `view_chat`
- **Ruolo**: Swapper
- **Descrizione**: Visualizza lista delle proprie chat
- **Tipo**: READ
- **URL**: `/login/mockup_manager.php?azione=view_chat`
- **Vista**: `visteSQL/swapper/view_chat.php`
- **API**: 
  - GET: `api/swapper/api_get_chats.php`
- **VIEW SQL**: 
  ```sql
  CREATE VIEW vista_chat_utente AS
  SELECT 
    c.idChat,
    c.nome,
    c.tipoChat,
    c.stato,
    c.numPartecipanti,
    pc.User AS username,
    (SELECT COUNT(*) FROM Messaggi WHERE idChat = c.idChat) AS totMessaggi,
    (SELECT contenuto FROM Messaggi WHERE idChat = c.idChat ORDER BY dataInvio DESC LIMIT 1) AS ultimoMessaggio
  FROM Chat c
  JOIN PartecipaChat pc ON c.idChat = pc.idChat;
  ```
- **Tabelle**: `Chat`, `PartecipaChat`, `Messaggi`
- **Output**: Array di chat con dettagli

---

### **1.4 delete_own_message**
- **Permesso ID**: 4
- **Nome**: `delete_own_message`
- **Ruolo**: Swapper
- **Descrizione**: Elimina un proprio messaggio
- **Tipo**: DELETE
- **URL**: `/login/mockup_manager.php?azione=delete_own_message`
- **Vista**: `visteSQL/swapper/delete_message.php`
- **API**: 
  - DELETE: `api/swapper/api_delete_message.php`
- **VIEW SQL**: Nessuna
- **Tabelle**: `Messaggi`
- **Input**: idMessaggio
- **Validazione**: User del messaggio === utente loggato
- **Output**: Successo/errore

---

### **1.5 moderate_chat**
- **Permesso ID**: 25
- **Nome**: `moderate_chat`
- **Ruolo**: Moderatore
- **Descrizione**: Modera chat (elimina messaggi inappropriati, chiude chat)
- **Tipo**: UPDATE/DELETE
- **URL**: `/login/mockup_manager.php?azione=moderate_chat`
- **Vista**: `visteSQL/moderatore/moderate_chat.php`
- **API**: 
  - GET: `api/moderatore/api_get_all_chats.php`
  - POST: `api/moderatore/api_moderate_chat.php`
- **VIEW SQL**: Nessuna
- **Tabelle**: `Chat`, `Messaggi`
- **Input**: idChat, azione (elimina_messaggio/chiudi_chat)
- **Output**: Successo/errore

---

## 2. SOCIAL (5 rotte)

### **2.1 send_friend_request**
- **Permesso ID**: 5
- **Nome**: `send_friend_request`
- **Ruolo**: Swapper
- **Descrizione**: Invia richiesta di amicizia
- **Tipo**: CREATE
- **URL**: `/login/mockup_manager.php?azione=send_friend_request`
- **Vista**: `visteSQL/swapper/send_friend_request.php`
- **API**: 
  - GET: `api/swapper/api_get_non_friends.php` (utenti non amici)
  - POST: `api/swapper/api_send_friend_request.php`
- **VIEW SQL**: Nessuna
- **Tabelle**: `RichiesteAmicizia`
- **Input**: userRicevente
- **Output**: idRichiesta, data

---

### **2.2 accept_friend_request**
- **Permesso ID**: 6
- **Nome**: `accept_friend_request`
- **Ruolo**: Swapper
- **Descrizione**: Accetta richiesta di amicizia ricevuta
- **Tipo**: UPDATE
- **URL**: `/login/mockup_manager.php?azione=accept_friend_request`
- **Vista**: `visteSQL/swapper/manage_friend_requests.php`
- **API**: 
  - GET: `api/swapper/api_get_pending_requests.php`
  - POST: `api/swapper/api_accept_friend_request.php`
- **VIEW SQL**: 
  ```sql
  CREATE VIEW vista_richieste_amicizia AS
  SELECT 
    ra.idRichiesta,
    ra.userMittente,
    ra.userRicevente,
    ra.dataRichiesta,
    ra.stato,
    u1.Nome AS nomeMittente,
    u1.Cognome AS cognomeMittente,
    u2.Nome AS nomeRicevente,
    u2.Cognome AS cognomeRicevente
  FROM RichiesteAmicizia ra
  JOIN utenti u1 ON ra.userMittente = u1.username
  JOIN utenti u2 ON ra.userRicevente = u2.username;
  ```
- **Tabelle**: `RichiesteAmicizia`
- **Input**: idRichiesta
- **Output**: Successo/errore

---

### **2.3 reject_friend_request**
- **Permesso ID**: 7
- **Nome**: `reject_friend_request`
- **Ruolo**: Swapper
- **Descrizione**: Rifiuta richiesta di amicizia
- **Tipo**: UPDATE
- **URL**: `/login/mockup_manager.php?azione=reject_friend_request`
- **Vista**: `visteSQL/swapper/manage_friend_requests.php`
- **API**: 
  - POST: `api/swapper/api_reject_friend_request.php`
- **VIEW SQL**: `vista_richieste_amicizia`
- **Tabelle**: `RichiesteAmicizia`
- **Input**: idRichiesta
- **Output**: Successo/errore

---

### **2.4 subscribe_swapplus**
- **Permesso ID**: 8
- **Nome**: `subscribe_swapplus`
- **Ruolo**: Swapper
- **Descrizione**: Attiva abbonamento Swap+
- **Tipo**: CREATE
- **URL**: `/login/mockup_manager.php?azione=subscribe_swapplus`
- **Vista**: `visteSQL/swapper/subscribe_swapplus.php`
- **API**: 
  - POST: `api/swapper/api_subscribe_swapplus.php`
- **VIEW SQL**: Nessuna
- **Tabelle**: `SwapPlus`
- **Input**: durataGiorni (30/90/365)
- **Output**: idAbbonamento, dataInizio, dataFine

---

### **2.5 view_own_swapplus**
- **Permesso ID**: 9
- **Nome**: `view_own_swapplus`
- **Ruolo**: Swapper
- **Descrizione**: Visualizza dettagli abbonamento Swap+
- **Tipo**: READ
- **URL**: `/login/mockup_manager.php?azione=view_own_swapplus`
- **Vista**: `visteSQL/swapper/view_swapplus.php`
- **API**: 
  - GET: `api/swapper/api_get_swapplus.php`
- **VIEW SQL**: 
  ```sql
  CREATE VIEW vista_swapplus_utente AS
  SELECT 
    idAbbonamento,
    user AS username,
    dataInizio,
    dataFine,
    DATEDIFF(dataFine, CURDATE()) AS giorniRimanenti,
    DATEDIFF(CURDATE(), dataInizio) AS giorniTrascorsi,
    DATEDIFF(dataFine, dataInizio) AS durataGiorni,
    CASE
      WHEN CURDATE() > dataFine THEN 'scaduto'
      WHEN DATEDIFF(dataFine, CURDATE()) <= 7 THEN 'in_scadenza'
      ELSE 'attivo'
    END AS statoAbbonamento
  FROM SwapPlus;
  ```
- **Tabelle**: `SwapPlus`
- **Output**: Dettagli abbonamento o messaggio "nessun abbonamento"

---

## 3. PRODOTTI E SCAMBI (5 rotte)

### **3.1 upload_product**
- **Permesso ID**: 10
- **Nome**: `upload_product`
- **Ruolo**: Swapper
- **Descrizione**: Carica/gestisce i propri prodotti
- **Tipo**: CREATE/READ/UPDATE
- **URL**: `/login/mockup_manager.php?azione=upload_product`
- **Vista**: `visteSQL/swapper/manage_products.php`
- **API**: 
  - GET: `api/swapper/api_get_products.php`
  - POST: `api/swapper/api_upload_product.php`
  - PUT: `api/swapper/api_update_product.php`
  - DELETE: `api/swapper/api_delete_product.php`
- **VIEW SQL**: 
  ```sql
  CREATE VIEW vista_prodotti AS
  SELECT 
    p.idProdotto,
    p.Titolo,
    p.Descrizione,
    p.Disponibilità,
    p.Condizioni,
    p.dataPubblicazione,
    p.img,
    p.User AS username,
    p.NomeCategoria,
    u.Nome AS nomeUtente,
    u.Cognome AS cognomeUtente
  FROM Prodotto p
  JOIN utenti u ON p.User = u.username;
  ```
- **Tabelle**: `Prodotto`
- **Input**: Titolo, Descrizione, Condizioni, NomeCategoria, img
- **Output**: Array prodotti, idProdotto creato

---

### **3.2 send_trade_request**
- **Permesso ID**: 11
- **Nome**: `send_trade_request`
- **Ruolo**: Swapper
- **Descrizione**: Propone uno scambio
- **Tipo**: CREATE
- **URL**: `/login/mockup_manager.php?azione=send_trade_request`
- **Vista**: `visteSQL/swapper/send_trade.php`
- **API**: 
  - GET: `api/swapper/api_get_available_products.php`
  - POST: `api/swapper/api_send_trade_request.php`
- **VIEW SQL**: `vista_prodotti`
- **Tabelle**: `Scambio`
- **Input**: idProdottoOfferto, idProdottoRichiesto
- **Output**: idScambio, dataPropostaScambio

---

### **3.3 write_review**
- **Permesso ID**: 12
- **Nome**: `write_review`
- **Ruolo**: Swapper
- **Descrizione**: Scrive recensione su prodotto/utente
- **Tipo**: CREATE
- **URL**: `/login/mockup_manager.php?azione=write_review`
- **Vista**: `visteSQL/swapper/write_review.php`
- **API**: 
  - POST: `api/swapper/api_write_review.php`
- **VIEW SQL**: Nessuna
- **Tabelle**: `Recensioni`
- **Input**: Valutazione (1-5), Commento, idScambio
- **Output**: idRecensione

---

### **3.4 edit_account**
- **Permesso ID**: 13
- **Nome**: `edit_account`
- **Ruolo**: Swapper
- **Descrizione**: Modifica profilo personale
- **Tipo**: UPDATE
- **URL**: `/login/mockup_manager.php?azione=edit_account`
- **Vista**: `visteSQL/swapper/edit_account.php`
- **API**: 
  - GET: `api/swapper/api_get_profile.php`
  - PUT: `api/swapper/api_update_profile.php`
- **VIEW SQL**: Nessuna
- **Tabelle**: `utenti`
- **Input**: Nome, Cognome, Email, localita, fotoprofilo, bgcolor
- **Output**: Successo/errore

---

### **3.5 send_report**
- **Permesso ID**: 14
- **Nome**: `send_report`
- **Ruolo**: Swapper
- **Descrizione**: Segnala contenuto/utente inappropriato
- **Tipo**: CREATE
- **URL**: `/login/mockup_manager.php?azione=send_report`
- **Vista**: `visteSQL/swapper/send_report.php`
- **API**: 
  - POST: `api/swapper/api_send_report.php`
- **VIEW SQL**: Nessuna
- **Tabelle**: `Segnalazioni`
- **Input**: Tipo (utente/prodotto/messaggio), idOggetto, Motivo, Descrizione
- **Output**: idSegnalazione

---

## 4. MODERAZIONE (5 rotte)

### **4.1 manage_user_reports**
- **Permesso ID**: 15
- **Nome**: `manage_user_reports`
- **Ruolo**: Moderatore
- **Descrizione**: Gestisce segnalazioni utenti
- **Tipo**: READ/UPDATE
- **URL**: `/login/mockup_manager.php?azione=manage_user_reports`
- **Vista**: `visteSQL/moderatore/manage_reports.php`
- **API**: 
  - GET: `api/moderatore/api_get_reports.php`
  - POST: `api/moderatore/api_handle_report.php`
- **VIEW SQL**: 
  ```sql
  CREATE VIEW vista_segnalazioni AS
  SELECT 
    s.idSegnalazione,
    s.User AS userSegnalante,
    s.Tipo,
    s.idProdottoSegnalato,
    s.idMessaggioSegnalato,
    s.userSegnalato,
    s.Motivo,
    s.Descrizione,
    s.dataSegnalazione,
    s.Stato,
    u1.Nome AS nomeSegnalante,
    u2.Nome AS nomeSegnalato,
    p.Titolo AS titoloProdotto
  FROM Segnalazioni s
  LEFT JOIN utenti u1 ON s.User = u1.username
  LEFT JOIN utenti u2 ON s.userSegnalato = u2.username
  LEFT JOIN Prodotto p ON s.idProdottoSegnalato = p.idProdotto;
  ```
- **Tabelle**: `Segnalazioni`
- **Output**: Array segnalazioni

---

### **4.2 ban_user**
- **Permesso ID**: 16
- **Nome**: `ban_user`
- **Ruolo**: Moderatore
- **Descrizione**: Banna permanentemente un utente
- **Tipo**: UPDATE
- **URL**: `/login/mockup_manager.php?azione=ban_user`
- **Vista**: `visteSQL/moderatore/ban_user.php`
- **API**: 
  - POST: `api/moderatore/api_ban_user.php`
- **VIEW SQL**: Nessuna
- **Tabelle**: `utenti` (aggiungere campo `isBanned`)
- **Input**: username, motivoBan
- **Output**: Successo/errore

---

### **4.3 suspend_user**
- **Permesso ID**: 17
- **Nome**: `suspend_user`
- **Ruolo**: Moderatore
- **Descrizione**: Sospende temporaneamente un utente
- **Tipo**: UPDATE
- **URL**: `/login/mockup_manager.php?azione=suspend_user`
- **Vista**: `visteSQL/moderatore/suspend_user.php`
- **API**: 
  - POST: `api/moderatore/api_suspend_user.php`
- **VIEW SQL**: Nessuna
- **Tabelle**: `utenti` (aggiungere campo `suspendedUntil`)
- **Input**: username, giorni (7/14/30)
- **Output**: Successo, data fine sospensione

---

### **4.4 escalate_report_to_admin**
- **Permesso ID**: 18
- **Nome**: `escalate_report_to_admin`
- **Ruolo**: Moderatore
- **Descrizione**: Inoltra segnalazione grave agli admin
- **Tipo**: UPDATE
- **URL**: `/login/mockup_manager.php?azione=escalate_report_to_admin`
- **Vista**: `visteSQL/moderatore/escalate_report.php`
- **API**: 
  - POST: `api/moderatore/api_escalate_report.php`
- **VIEW SQL**: `vista_segnalazioni`
- **Tabelle**: `Segnalazioni`
- **Input**: idSegnalazione, noteEscalation
- **Output**: Successo/errore

---

### **4.5 remove_inappropriate_content**
- **Permesso ID**: 19
- **Nome**: `remove_inappropriate_content`
- **Ruolo**: Moderatore
- **Descrizione**: Rimuove contenuto inappropriato
- **Tipo**: DELETE
- **URL**: `/login/mockup_manager.php?azione=remove_inappropriate_content`
- **Vista**: `visteSQL/moderatore/remove_content.php`
- **API**: 
  - POST: `api/moderatore/api_remove_content.php`
- **VIEW SQL**: Nessuna
- **Tabelle**: `Prodotto`, `Messaggi`
- **Input**: tipo (prodotto/messaggio), idOggetto
- **Output**: Successo/errore

---

## 5. AMMINISTRAZIONE (10 rotte)

### **5.1 manage_platform_policies**
- **Permesso ID**: 20
- **Nome**: `manage_platform_policies`
- **Ruolo**: Admin
- **Descrizione**: Gestisce policy piattaforma (termini, privacy)
- **Tipo**: READ/UPDATE
- **URL**: `/login/mockup_manager.php?azione=manage_platform_policies`
- **Vista**: `visteSQL/admin/manage_policies.php`
- **API**: 
  - GET: `api/admin/api_get_policies.php`
  - PUT: `api/admin/api_update_policies.php`
- **VIEW SQL**: Nessuna
- **Tabelle**: `Policies` (da creare)
- **Input**: terminiServizio, privacyPolicy, codiceCondotta
- **Output**: Successo/errore

---

### **5.2 manage_critical_reports**
- **Permesso ID**: 21
- **Nome**: `manage_critical_reports`
- **Ruolo**: Admin
- **Descrizione**: Gestisce segnalazioni critiche escalate
- **Tipo**: READ/UPDATE
- **URL**: `/login/mockup_manager.php?azione=manage_critical_reports`
- **Vista**: `visteSQL/admin/critical_reports.php`
- **API**: 
  - GET: `api/admin/api_get_critical_reports.php`
  - POST: `api/admin/api_resolve_critical_report.php`
- **VIEW SQL**: `vista_segnalazioni` (WHERE Stato = 'escalated')
- **Tabelle**: `Segnalazioni`
- **Output**: Array segnalazioni critiche

---

### **5.3 appoint_moderator**
- **Permesso ID**: 22
- **Nome**: `appoint_moderator`
- **Ruolo**: Admin
- **Descrizione**: Nomina un utente come moderatore
- **Tipo**: CREATE
- **URL**: `/login/mockup_manager.php?azione=appoint_moderator`
- **Vista**: `visteSQL/admin/appoint_moderator.php`
- **API**: 
  - POST: `api/admin/api_appoint_moderator.php`
- **VIEW SQL**: Nessuna
- **Tabelle**: `UtenteRuolo`
- **Input**: username
- **Output**: Successo/errore

---

### **5.4 manage_moderators**
- **Permesso ID**: 23
- **Nome**: `manage_moderators`
- **Ruolo**: Admin
- **Descrizione**: Gestisce staff moderatori (attività, permessi)
- **Tipo**: READ/UPDATE
- **URL**: `/login/mockup_manager.php?azione=manage_moderators`
- **Vista**: `visteSQL/admin/manage_moderators.php`
- **API**: 
  - GET: `api/admin/api_get_moderators.php`
  - POST: `api/admin/api_update_moderator_permissions.php`
- **VIEW SQL**: 
  ```sql
  CREATE VIEW vista_moderatori AS
  SELECT 
    u.username,
    u.Nome,
    u.Cognome,
    ur.idRuolo,
    COUNT(s.idSegnalazione) AS segnalazioniGestite
  FROM utenti u
  JOIN UtenteRuolo ur ON u.username = ur.User
  LEFT JOIN Segnalazioni s ON s.idModeratoreAssegnato = u.username
  WHERE ur.idRuolo = 2
  GROUP BY u.username;
  ```
- **Tabelle**: `UtenteRuolo`, `Segnalazioni`
- **Output**: Array moderatori con statistiche

---

### **5.5 manage_users**
- **Permesso ID**: 24
- **Nome**: `manage_users`
- **Ruolo**: Admin
- **Descrizione**: Gestisce tutti gli utenti (CRUD completo)
- **Tipo**: READ/UPDATE/DELETE
- **URL**: `/login/mockup_manager.php?azione=manage_users`
- **Vista**: `visteSQL/admin/manage_users.php`
- **API**: 
  - GET: `api/admin/api_get_all_users.php`
  - PUT: `api/admin/api_update_user.php`
  - DELETE: `api/admin/api_delete_user.php`
- **VIEW SQL**: 
  ```sql
  CREATE VIEW vista_utenti AS
  SELECT 
    u.*,
    GROUP_CONCAT(r.NomeRuolo) AS ruoli
  FROM utenti u
  LEFT JOIN UtenteRuolo ur ON u.username = ur.User
  LEFT JOIN Ruolo r ON ur.idRuolo = r.idRuolo
  GROUP BY u.username;
  ```
- **Tabelle**: `utenti`, `UtenteRuolo`
- **Output**: Array utenti completo

---

### **5.6 manage_categories**
- **Permesso ID**: 26
- **Nome**: `manage_categories`
- **Ruolo**: Admin
- **Descrizione**: Gestisce categorie prodotti
- **Tipo**: CREATE/READ/UPDATE/DELETE
- **URL**: `/login/mockup_manager.php?azione=manage_categories`
- **Vista**: `visteSQL/admin/manage_categories.php`
- **API**: 
  - GET: `api/admin/api_get_categories.php`
  - POST: `api/admin/api_create_category.php`
  - PUT: `api/admin/api_update_category.php`
  - DELETE: `api/admin/api_delete_category.php`
- **VIEW SQL**: Nessuna
- **Tabelle**: `Categoria`
- **Input**: NomeCategoria, Descrizione
- **Output**: Array categorie

---

### **5.7 view_platform_statistics**
- **Permesso ID**: 27
- **Nome**: `view_platform_statistics`
- **Ruolo**: Admin
- **Descrizione**: Visualizza statistiche globali piattaforma
- **Tipo**: READ
- **URL**: `/login/mockup_manager.php?azione=view_platform_statistics`
- **Vista**: `visteSQL/admin/platform_statistics.php`
- **API**: 
  - GET: `api/admin/api_get_statistics.php`
- **VIEW SQL**: 
  ```sql
  CREATE VIEW vista_statistiche_globali AS
  SELECT 
    (SELECT COUNT(*) FROM utenti) AS totaleUtenti,
    (SELECT COUNT(*) FROM Prodotto) AS totaleProdotti,
    (SELECT COUNT(*) FROM Scambio WHERE Stato = 'completato') AS scambiCompletati,
    (SELECT COUNT(*) FROM Chat) AS totaleChat,
    (SELECT COUNT(*) FROM Segnalazioni WHERE Stato = 'aperta') AS segnalazioniAperte;
  ```
- **Tabelle**: Tutte (aggregate)
- **Output**: Oggetto con statistiche

---

### **5.8 manage_couriers**
- **Permesso ID**: 28
- **Nome**: `manage_couriers`
- **Ruolo**: Admin
- **Descrizione**: Gestisce corrieri (nomina, rimuove)
- **Tipo**: CREATE/READ/DELETE
- **URL**: `/login/mockup_manager.php?azione=manage_couriers`
- **Vista**: `visteSQL/admin/manage_couriers.php`
- **API**: 
  - GET: `api/admin/api_get_couriers.php`
  - POST: `api/admin/api_appoint_courier.php`
  - DELETE: `api/admin/api_remove_courier.php`
- **VIEW SQL**: Nessuna
- **Tabelle**: `UtenteRuolo`
- **Input**: username
- **Output**: Array corrieri

---

### **5.9 manage_orders** (Corriere)
- **Permesso ID**: 29
- **Nome**: `manage_orders`
- **Ruolo**: Corriere
- **Descrizione**: Visualizza ordini assegnati
- **Tipo**: READ
- **URL**: `/login/mockup_manager.php?azione=manage_orders`
- **Vista**: `visteSQL/corriere/manage_orders.php`
- **API**: 
  - GET: `api/corriere/api_get_orders.php`
- **VIEW SQL**: 
  ```sql
  CREATE VIEW vista_consegne AS
  SELECT 
    c.idConsegna,
    c.idScambio,
    c.UserCorriere,
    c.dataConsegna,
    c.indirizzoConsegna,
    c.Stato,
    s.idProdottoOfferto,
    s.idProdottoRichiesto,
    p.Titolo AS titoloProdotto
  FROM Consegna c
  JOIN Scambio s ON c.idScambio = s.idScambio
  JOIN Prodotto p ON s.idProdottoOfferto = p.idProdotto;
  ```
- **Tabelle**: `Consegna`, `Scambio`
- **Output**: Array consegne assegnate

---

### **5.10 update_shipping** (Corriere)
- **Permesso ID**: 30
- **Nome**: `update_shipping`
- **Ruolo**: Corriere
- **Descrizione**: Aggiorna stato spedizione
- **Tipo**: UPDATE
- **URL**: `/login/mockup_manager.php?azione=update_shipping`
- **Vista**: `visteSQL/corriere/update_shipping.php`
- **API**: 
  - PUT: `api/corriere/api_update_shipping.php`
- **VIEW SQL**: `vista_consegne`
- **Tabelle**: `Consegna`
- **Input**: idConsegna, nuovoStato (in_transito/consegnato/fallito)
- **Output**: Successo/errore

---

## 🧩 Template Implementazione

Per ogni nuova rotta, seguire questo template:

### **File da creare:**

1. **VIEW SQL** (se necessaria):
   ```sql
   -- Nome file: XX_view_NOME.sql
   CREATE VIEW vista_NOME AS
   SELECT ...
   FROM ...
   ```

2. **API**:
   ```php
   // Nome file: api_AZIONE_NOME.php
   // Path: api/RUOLO/api_AZIONE_NOME.php
   
   header('Content-Type: application/json');
   require_once '../../jwt.php';
   require_once '../../connectdb.php';
   
   session_start(['cookie_path' => '/login/']);
   
   // Verifica JWT
   // Query VIEW/tabelle
   // Return JSON
   ```

3. **Vista HTML**:
   ```php
   // Nome file: NOME.php
   // Path: visteSQL/RUOLO/NOME.php
   
   <!doctype html>
   <html>
   <!-- NO session_start, NO require -->
   <!-- Fetch API con credentials: 'include' -->
   </html>
   ```

4. **Documentazione**:
   ```markdown
   # Rotta: NOME
   - Permesso ID: XX
   - URL: ...
   - API: ...
   - VIEW SQL: ...
   - Input/Output: ...
   - Test: ...
   ```

5. **Aggiorna mockup_manager.php**:
   ```php
   case 'PERMESSO':
       include 'visteSQL/RUOLO/FILE.php';
       exit;
   ```

---

## 📐 Convenzioni

### **Naming:**
- VIEW SQL: `vista_NOME` (es: `vista_chat_utente`)
- API: `api_VERBO_NOME.php` (es: `api_get_products.php`)
- Vista: `VERBO_NOME.php` (es: `manage_products.php`)

### **Percorsi:**
- API: `/login/api/RUOLO/api_*.php`
- Viste: `/login/visteSQL/RUOLO/*.php`
- Rotta: `/login/mockup_manager.php?azione=PERMESSO`

### **Sicurezza:**
- Sempre `credentials: 'include'` nel fetch
- Sempre `session_start(['cookie_path' => '/login/'])`
- Sempre verificare JWT
- Sempre prepared statements

### **Response JSON Standard:**
```json
{
  "success": true/false,
  "data": {...},         // Se success
  "error": "messaggio"   // Se !success
}
```

---

## 📊 Riepilogo per Ruolo

| Ruolo | Rotte | Categoria |
|-------|-------|-----------|
| **Swapper** | 14 | Chat (4), Social (5), Prodotti (5) |
| **Moderatore** | 6 | Moderazione (5), Chat (1) |
| **Admin** | 8 | Amministrazione (8) |
| **Corriere** | 2 | Logistica (2) |
| **TOTALE** | **30** | - |

---

*Fine Documentazione Rotte SwapHub v1.0*
