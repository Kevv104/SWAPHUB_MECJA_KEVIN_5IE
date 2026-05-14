# 📘 SwapHub User Guide

Guida operativa app SWAPHUB.

Utente test Swapper: `gianno`  
Password: `g67`

## Indice

- [Panoramica rapida](#panoramica-rapida)
- [Tabella stato completa dei mock](#tabella-stato-completa-dei-mock)
- [Come funziona la struttura PHP in visteSQL](#come-funziona-la-struttura-php-in-vistesql)
- [Guida operativa casi implementati](#guida-operativa-casi-implementati)
- [Auth, Sessione e JWT](#auth-sessione-e-jwt)
- [Ruoli applicativi e cosa possono fare](#ruoli-applicativi-e-cosa-possono-fare)
- [Cosa manca oggi](#cosa-manca-oggi)

## Panoramica rapida

I casi d'uso sono instradati da [mockup_manager.php](../LOGIN_DATABASE/mockup_manager.php) tramite il parametro `azione`.

I nomi dei casi d'uso derivano dai codici permesso salvati nel DB (`Permesso.nomePermesso` in [SWAPHUB.sql](../../SWAPHUB.sql)); in alcuni punti il backend applica una normalizzazione tra codice DB e nome mostrato nel mock.

Esempio:

`mockup_manager.php?azione=create_chat`

In base al caso d'uso, il manager:

- include una vista reale in [LOGIN_DATABASE/visteSQL/swapper](../LOGIN_DATABASE/visteSQL/swapper)
- oppure reindirizza a una vista
- oppure mostra un placeholder testuale (mock non ancora implementato)

## Tabella stato completa dei mock

Legenda rapida stato: ✅ Implementato, 🟡 Parziale, 🔴 Futuro.

| Codice permesso DB (`nomePermesso`) | Azione mock (`azione`) | Area | Stato | Riferimento reale |
| --- | --- | --- | --- | --- |
| [create_chat](#create_chat) | [create_chat](#create_chat) | Chat | ✅ Implementato | [mockup_manager.php](../LOGIN_DATABASE/mockup_manager.php), [create_chat.php](../LOGIN_DATABASE/visteSQL/swapper/create_chat.php), [api_create_chat.php](../LOGIN_DATABASE/api/swapper/api_create_chat.php) |
| `view_chat` / `manage_chat` | [manage_chat](#view_chat) | Chat | 🟡 Parziale | [login.php](../LOGIN_DATABASE/login.php), [api_permessi.php](../LOGIN_DATABASE/api_permessi.php), [view_chat.php](../LOGIN_DATABASE/visteSQL/swapper/view_chat.php) |
| [view_chat](#view_chat) | [view_chat](#view_chat) | Chat | ✅ Implementato | Route di compatibilita' in [mockup_manager.php](../LOGIN_DATABASE/mockup_manager.php), vista [view_chat.php](../LOGIN_DATABASE/visteSQL/swapper/view_chat.php) |
| `moderate_chat` | `moderate_chat` | Chat | 🔴 Futuro | Placeholder in [mockup_manager.php](../LOGIN_DATABASE/mockup_manager.php) |
| [send_friend_request](#send_friend_request) | [send_friend_request](#send_friend_request) | Social | ✅ Implementato | [send_friend_request.php](../LOGIN_DATABASE/visteSQL/swapper/send_friend_request.php), [api_send_friend_request.php](../LOGIN_DATABASE/api/swapper/api_send_friend_request.php) |
| `accept_friend_request` | [manage_friend_request](#manage_friend_request) | Social | ✅ Implementato | Mapping in [login.php](../LOGIN_DATABASE/login.php), vista [accept_friend_request.php](../LOGIN_DATABASE/visteSQL/swapper/accept_friend_request.php), [api_accept_friend_request.php](../LOGIN_DATABASE/api/swapper/api_accept_friend_request.php) |
| [subscribe_swapplus](#subscribe_swapplus) | [subscribe_swapplus](#subscribe_swapplus) | Social | ✅ Implementato | [subscribe_swapplus.php](../LOGIN_DATABASE/visteSQL/swapper/subscribe_swapplus.php), [api_subscribe_swapplus.php](../LOGIN_DATABASE/api/swapper/api_subscribe_swapplus.php) |
| [view_own_swapplus](#view_own_swapplus) | [view_own_swapplus](#view_own_swapplus) | Social | ✅ Implementato | [view_swapplus.php](../LOGIN_DATABASE/visteSQL/swapper/view_swapplus.php), [api_get_swapplus.php](../LOGIN_DATABASE/api/swapper/api_get_swapplus.php) |
| `upload_product` | [manage_products](#manage_products) | Market | ✅ Implementato | Mapping in [login.php](../LOGIN_DATABASE/login.php), vista [manage_products.php](../LOGIN_DATABASE/visteSQL/swapper/manage_products.php), API in [LOGIN_DATABASE/api/swapper](../LOGIN_DATABASE/api/swapper) |
| [send_trade_request](#send_trade_request) | [send_trade_request](#send_trade_request) | Market | ✅ Implementato | [send_trade_request.php](../LOGIN_DATABASE/visteSQL/swapper/send_trade_request.php), [api_send_trade_request.php](../LOGIN_DATABASE/api/swapper/api_send_trade_request.php), [api_get_trade_requests.php](../LOGIN_DATABASE/api/swapper/api_get_trade_requests.php), [api_accept_trade_request.php](../LOGIN_DATABASE/api/swapper/api_accept_trade_request.php), [api_reject_trade_request.php](../LOGIN_DATABASE/api/swapper/api_reject_trade_request.php) |
| [send_trade_request](#send_trade_request) | [manage_trade_request](#send_trade_request) | Market | ✅ Implementato (unificato) | Stesso flusso e stessa vista [send_trade_request.php](../LOGIN_DATABASE/visteSQL/swapper/send_trade_request.php) |
| `write_review` | `write_review` | Market | 🔴 Futuro | Placeholder in [mockup_manager.php](../LOGIN_DATABASE/mockup_manager.php) |
| `edit_account` | `edit_account` | Account | 🔴 Futuro | Placeholder in [mockup_manager.php](../LOGIN_DATABASE/mockup_manager.php) |
| [send_report](#send_report) | [send_report](#send_report) | Moderazione | ✅ Implementato | [send_report.php](../LOGIN_DATABASE/visteSQL/swapper/send_report.php), [api_send_report.php](../LOGIN_DATABASE/api/swapper/api_send_report.php) |
| `manage_user_reports` | `manage_user_reports` | Moderazione | 🔴 Futuro | Placeholder in [mockup_manager.php](../LOGIN_DATABASE/mockup_manager.php) |
| `ban_user` | `ban_user` | Moderazione | 🔴 Futuro | Placeholder in [mockup_manager.php](../LOGIN_DATABASE/mockup_manager.php) |
| `suspend_user` | `suspend_user` | Moderazione | 🔴 Futuro | Placeholder in [mockup_manager.php](../LOGIN_DATABASE/mockup_manager.php) |
| `escalate_report_to_admin` | `escalate_report_to_admin` | Moderazione | 🔴 Futuro | Placeholder in [mockup_manager.php](../LOGIN_DATABASE/mockup_manager.php) |
| `remove_inappropriate_content` | `remove_inappropriate_content` | Moderazione | 🔴 Futuro | Placeholder in [mockup_manager.php](../LOGIN_DATABASE/mockup_manager.php) |
| `manage_platform_policies` | `manage_platform_policies` | Admin | 🔴 Futuro | Placeholder in [mockup_manager.php](../LOGIN_DATABASE/mockup_manager.php) |
| `manage_critical_reports` | `manage_critical_reports` | Admin | 🔴 Futuro | Placeholder in [mockup_manager.php](../LOGIN_DATABASE/mockup_manager.php) |
| `appoint_moderator` | `appoint_moderator` | Admin | 🔴 Futuro | Placeholder in [mockup_manager.php](../LOGIN_DATABASE/mockup_manager.php) |
| `manage_moderators` | `manage_moderators` | Admin | 🔴 Futuro | Placeholder in [mockup_manager.php](../LOGIN_DATABASE/mockup_manager.php) |
| `manage_users` | `manage_users` | Admin | 🔴 Futuro | Placeholder in [mockup_manager.php](../LOGIN_DATABASE/mockup_manager.php) |

### Mappatura codici permesso DB -> mock

- `accept_friend_request` viene mostrato come `manage_friend_request`.
- `upload_product` viene mostrato come `manage_products`.
- `view_chat` viene normalizzato in `manage_chat` (co-esiste anche il permesso `manage_chat` nel dump).
- `send_message` e `reject_friend_request` oggi non compaiono come pulsanti separati nel mock manager.

Riferimenti mapping: [login.php](../LOGIN_DATABASE/login.php), [api_permessi.php](../LOGIN_DATABASE/api_permessi.php), [SWAPHUB.sql](../../SWAPHUB.sql).

## Come funziona la struttura PHP in visteSQL

Pattern usato nei casi implementati:

1. `mockup_manager.php` riceve `azione`.
2. Include una vista PHP in [LOGIN_DATABASE/visteSQL/swapper](../LOGIN_DATABASE/visteSQL/swapper).
3. La vista mostra form/lista e chiama API JSON in [LOGIN_DATABASE/api/swapper](../LOGIN_DATABASE/api/swapper).
4. Le API verificano utente e permessi, poi eseguono la logica su DB.

In pratica:

- le viste in `visteSQL` sono il livello UI
- le API sono il livello applicativo
- `mockup_manager.php` è il router dei casi d'uso

## Guida operativa casi implementati

### create_chat

Percorso reale:

- [mockup_manager.php](../LOGIN_DATABASE/mockup_manager.php) (`azione=create_chat`)
- [create_chat.php](../LOGIN_DATABASE/visteSQL/swapper/create_chat.php)
- [api_get_available_users.php](../LOGIN_DATABASE/api/swapper/api_get_available_users.php)
- [api_create_chat.php](../LOGIN_DATABASE/api/swapper/api_create_chat.php)

Come usarla:

1. Apri dashboard in [visualizzaUtente.php](../LOGIN_DATABASE/visualizzaUtente.php).
2. Seleziona il caso d'uso `create_chat`.
3. La vista carica gli utenti disponibili.
4. Inserisci nome/chat, partecipanti e invia.
5. L'API crea chat e partecipazioni; la nuova chat è poi visibile in `view_chat`.

### view_chat

Percorso reale:

- [view_chat.php](../LOGIN_DATABASE/visteSQL/swapper/view_chat.php)
- [api_get_chats.php](../LOGIN_DATABASE/api/swapper/api_get_chats.php)
- [api_get_chat_details.php](../LOGIN_DATABASE/api/swapper/api_get_chat_details.php)
- [api_get_chat_messages.php](../LOGIN_DATABASE/api/swapper/api_get_chat_messages.php)

Come usarla:

1. Vai su `view_chat` o `manage_chat` (entrambi puntano alla stessa vista).
2. Apri una chat dalla lista.
3. Visualizzi dettagli e messaggi reali della chat.

### send_friend_request

Percorso reale:

- [send_friend_request.php](../LOGIN_DATABASE/visteSQL/swapper/send_friend_request.php)
- [api_get_non_friends.php](../LOGIN_DATABASE/api/swapper/api_get_non_friends.php)
- [api_send_friend_request.php](../LOGIN_DATABASE/api/swapper/api_send_friend_request.php)

Come usarla:

1. Apri il caso `send_friend_request`.
2. Seleziona uno user dalla lista proposta.
3. Invia la richiesta.

### manage_friend_request

Percorso reale:

- [accept_friend_request.php](../LOGIN_DATABASE/visteSQL/swapper/accept_friend_request.php)
- [api_accept_friend_request.php](../LOGIN_DATABASE/api/swapper/api_accept_friend_request.php)

Come usarla:

1. Apri il caso `manage_friend_request`.
2. Accetta una richiesta in ingresso.

### subscribe_swapplus

Percorso reale:

- [subscribe_swapplus.php](../LOGIN_DATABASE/visteSQL/swapper/subscribe_swapplus.php)
- [api_subscribe_swapplus.php](../LOGIN_DATABASE/api/swapper/api_subscribe_swapplus.php)

Come usarla:

1. Apri `subscribe_swapplus`.
2. Scegli durata 30/90/365 giorni.
3. Conferma la sottoscrizione.

### view_own_swapplus

Percorso reale:

- [view_swapplus.php](../LOGIN_DATABASE/visteSQL/swapper/view_swapplus.php)
- [api_get_swapplus.php](../LOGIN_DATABASE/api/swapper/api_get_swapplus.php)

Come usarla:

1. Apri `view_own_swapplus`.
2. Controlla stato abbonamento e giorni residui.

### manage_products

Percorso reale:

- [manage_products.php](../LOGIN_DATABASE/visteSQL/swapper/manage_products.php)
- [api_get_products.php](../LOGIN_DATABASE/api/swapper/api_get_products.php)
- [api_upload_product.php](../LOGIN_DATABASE/api/swapper/api_upload_product.php)
- [api_update_product.php](../LOGIN_DATABASE/api/swapper/api_update_product.php)
- [api_delete_product.php](../LOGIN_DATABASE/api/swapper/api_delete_product.php)

Come usarla:

1. Apri `manage_products`.
2. Inserisci un nuovo prodotto dal form.
3. Modifica o elimina solo prodotti di cui sei proprietario.

### send_trade_request

Percorso reale:

- [send_trade_request.php](../LOGIN_DATABASE/visteSQL/swapper/send_trade_request.php)
- [api_send_trade_request.php](../LOGIN_DATABASE/api/swapper/api_send_trade_request.php)
- [api_get_trade_requests.php](../LOGIN_DATABASE/api/swapper/api_get_trade_requests.php)
- [api_accept_trade_request.php](../LOGIN_DATABASE/api/swapper/api_accept_trade_request.php)
- [api_reject_trade_request.php](../LOGIN_DATABASE/api/swapper/api_reject_trade_request.php)

Come usarla:

1. Apri `send_trade_request`.
2. Seleziona utente destinatario e prodotti.
3. Invia la richiesta di scambio.
4. Nella stessa area (azione `manage_trade_request`, oggi unificata) visualizzi le richieste ricevute.
5. Accetti o rifiuti la richiesta dalle API dedicate.

Nota:

`manage_trade_request` è stato unificato nel flusso `send_trade_request`; operativamente il ciclo è completo (creazione + gestione).

### send_report

Percorso reale:

- [send_report.php](../LOGIN_DATABASE/visteSQL/swapper/send_report.php)
- [api_get_swappers_list.php](../LOGIN_DATABASE/api/swapper/api_get_swappers_list.php)
- [api_get_report_reasons.php](../LOGIN_DATABASE/api/swapper/api_get_report_reasons.php)
- [api_send_report.php](../LOGIN_DATABASE/api/swapper/api_send_report.php)

Come usarla:

1. Apri `send_report`.
2. Seleziona utente da segnalare e motivo.
3. Invia la segnalazione.

## Auth, Sessione e JWT

Questa parte è centrale: senza autenticazione valida non puoi usare i mock operativi.

File chiave:

- [index.php](../LOGIN_DATABASE/index.php)
- [register.php](../LOGIN_DATABASE/register.php)
- [login.php](../LOGIN_DATABASE/login.php)
- [logout.php](../LOGIN_DATABASE/logout.php)
- [api_permessi.php](../LOGIN_DATABASE/api_permessi.php)

Flusso:

1. Registrazione (`register.php`): crea l'utente e associa il ruolo applicativo.
2. Login (`login.php`): verifica credenziali, carica ruoli/permessi, genera JWT.
3. Sessione PHP: salva profilo utente, ruoli, permessi, token e metadati UI.
4. Esposizione permessi (`api_permessi.php`): il frontend legge i permessi effettivi.
5. Accesso ai casi d'uso: `mockup_manager.php` applica il controllo rotta tramite sicurezza middleware.

Perche' sessione + JWT insieme:

- La sessione mantiene stato server-side e semplifica la navigazione dashboard.
- Il JWT standardizza l'identita' utente per API e controlli autorizzativi.
- La combinazione tiene allineate vista PHP e chiamate API AJAX.

Nota sui codici permesso:

- I permessi nel JWT derivano da `Permesso.nomePermesso`.
- Prima di essere esposti al frontend passano nella normalizzazione di [login.php](../LOGIN_DATABASE/login.php) e [api_permessi.php](../LOGIN_DATABASE/api_permessi.php).
- Per questo motivo alcuni pulsanti mock usano nomi UI diversi dal codice DB originale.

## Ruoli applicativi e cosa possono fare

Riferimenti: [login.php](../LOGIN_DATABASE/login.php), [api_permessi.php](../LOGIN_DATABASE/api_permessi.php), [mockup_manager.php](../LOGIN_DATABASE/mockup_manager.php)

### Swapper

- Chat: create/view/manage chat
- Social: richieste amicizia
- SwapPlus: sottoscrizione e visualizzazione
- Market: prodotti e trade
- Moderazione base: invio segnalazioni

### Moderatore

- Nel modello permessi esistono azioni come `manage_user_reports`, `ban_user`, `suspend_user`, `remove_inappropriate_content`.
- Nel codice corrente queste azioni sono ancora placeholder testuali nel mock manager.

### Admin

- Nel modello permessi esistono azioni come `manage_platform_policies`, `manage_critical_reports`, `appoint_moderator`, `manage_users`.
- Nel codice corrente queste azioni sono ancora placeholder testuali nel mock manager.

### Corriere

- Il ruolo Corriere esiste nel modello ruoli/permessi.
- Nel mock manager attuale non ha ancora un flusso dedicato completo lato viste/API.
- Va considerato come area prevista ma non ancora resa operativa end-to-end.

## Cosa manca oggi

### 🔴 Non implementato (placeholder)

- `moderate_chat`, `write_review`, `edit_account`.
- Tutte le azioni avanzate Moderatore/Admin (`manage_user_reports`, `ban_user`, `suspend_user`, `escalate_report_to_admin`, `remove_inappropriate_content`, `manage_platform_policies`, `manage_critical_reports`, `appoint_moderator`, `manage_moderators`, `manage_users`).

### 🟡 Da rifinire

- `manage_chat` e' ancora un redirect su `view_chat` (funziona, ma non e' un pannello separato).

### ✅ Pronto e usabile

- Accesso, sessione e permessi (Auth/JWT).
- Chat base, amicizie, SwapPlus, prodotti, trade (invio+gestione unificata), segnalazioni.

In sintesi: il core Swapper e' operativo; moderazione avanzata, amministrazione e area Corriere sono ancora in fase di sviluppo.