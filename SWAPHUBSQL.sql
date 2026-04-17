-- phpMyAdmin SQL Dump
-- version 5.2.3
-- https://www.phpmyadmin.net/
--
-- Host: localhost
-- Creato il: Apr 13, 2026 alle 06:57
-- Versione del server: 10.11.13-MariaDB-0ubuntu0.24.04.1
-- Versione PHP: 8.3.6

SET SQL_MODE = "NO_AUTO_VALUE_ON_ZERO";
START TRANSACTION;
SET time_zone = "+00:00";


/*!40101 SET @OLD_CHARACTER_SET_CLIENT=@@CHARACTER_SET_CLIENT */;
/*!40101 SET @OLD_CHARACTER_SET_RESULTS=@@CHARACTER_SET_RESULTS */;
/*!40101 SET @OLD_COLLATION_CONNECTION=@@COLLATION_CONNECTION */;
/*!40101 SET NAMES utf8mb4 */;

--
-- Database: `SWAPHUB`
--

-- --------------------------------------------------------

--
-- Struttura della tabella `Categoria`
--

CREATE TABLE `Categoria` (
  `NomeCategoria` varchar(100) NOT NULL,
  `Descrizione` text NOT NULL,
  `dataUltimaMod` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dump dei dati per la tabella `Categoria`
--

INSERT INTO `Categoria` (`NomeCategoria`, `Descrizione`, `dataUltimaMod`) VALUES
('Abbigliamento', 'Vestiti, scarpe e accessori di moda vintage e moderni', '2026-03-29 13:52:47'),
('Casa', 'Arredamento e oggettistica per interni', '2026-03-29 20:52:44'),
('Casa e Giardino', 'Mobili, elettrodomestici e attrezzi vari', '2026-03-29 13:52:47'),
('Collezionismo', 'Monete, francobolli, carte e oggetti rari', '2026-03-29 13:52:47'),
('Elettronica', 'Smartphone, PC, Console e accessori tecnologici', '2026-03-29 13:52:47'),
('Libri', 'Romanzi, manuali e fumetti', '2026-03-29 20:52:44'),
('Sport', 'Attrezzatura tecnica e abbigliamento sportivo', '2026-03-29 20:52:44'),
('Veicoli', 'Auto, moto, biciclette e monopattini', '2026-03-29 13:52:47');

-- --------------------------------------------------------

--
-- Struttura della tabella `Chat`
--

CREATE TABLE `Chat` (
  `idChat` int(11) NOT NULL,
  `nome` varchar(200) NOT NULL,
  `stato` enum('attiva','archiviata','chiusa','') NOT NULL DEFAULT 'attiva',
  `dataCreazione` timestamp NOT NULL DEFAULT current_timestamp(),
  `numPartecipanti` int(5) NOT NULL DEFAULT 2,
  `descrizione` text NOT NULL,
  `tipoChat` enum('privata','gruppo','scambio','') NOT NULL DEFAULT 'privata',
  `idScambio` int(11) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dump dei dati per la tabella `Chat`
--

INSERT INTO `Chat` (`idChat`, `nome`, `stato`, `dataCreazione`, `numPartecipanti`, `descrizione`, `tipoChat`, `idScambio`) VALUES
(1, 'Chat con Pietro', 'attiva', '2026-03-10 10:00:00', 2, 'Discussione su possibile scambio', 'privata', NULL),
(2, 'Amanti del Vintage', 'attiva', '2026-03-01 14:00:00', 2, 'Gruppo per appassionati di oggetti vintage', 'gruppo', NULL),
(3, 'Scambio Completato', 'archiviata', '2026-02-15 09:00:00', 2, 'Chat per scambio già concluso', 'scambio', NULL),
(4, 'Chat con morandi', 'attiva', '2026-03-15 21:45:01', 2, 'chat sanremese', 'privata', NULL),
(5, 'chatTest', 'attiva', '2026-03-29 13:36:18', 2, 'test', 'privata', NULL),
(7, 'prova', 'attiva', '2026-04-10 06:37:28', 2, '4', 'privata', NULL);

-- --------------------------------------------------------

--
-- Struttura della tabella `Consegna`
--

CREATE TABLE `Consegna` (
  `idConsegna` int(11) NOT NULL,
  `idScambio` int(11) NOT NULL,
  `idCorriere` varchar(50) NOT NULL,
  `dataEstCons` timestamp NULL DEFAULT NULL,
  `stato` enum('in_attesa','in_transito','consegnato','problema_consegna') NOT NULL DEFAULT 'in_attesa',
  `infoAggiuntive` text NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dump dei dati per la tabella `Consegna`
--

INSERT INTO `Consegna` (`idConsegna`, `idScambio`, `idCorriere`, `dataEstCons`, `stato`, `infoAggiuntive`) VALUES
(1, 3, 'corriere_fedex', NULL, 'in_transito', 'Spedizione presa in carico dalla filiale di Firenze. Consegna prevista a breve.');

-- --------------------------------------------------------

--
-- Struttura della tabella `Messaggi`
--

CREATE TABLE `Messaggi` (
  `idMessaggio` int(11) NOT NULL,
  `idChat` int(11) NOT NULL,
  `User` varchar(50) NOT NULL,
  `contenuto` text NOT NULL,
  `dataInvio` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dump dei dati per la tabella `Messaggi`
--

INSERT INTO `Messaggi` (`idMessaggio`, `idChat`, `User`, `contenuto`, `dataInvio`) VALUES
(1, 1, 'gianno', 'Ciao Pietro! Interessato allo scambio?', '2026-03-10 10:05:00'),
(2, 1, 'pietro', 'Ciao! Sì, dimmi di più', '2026-03-10 10:10:00'),
(3, 1, 'gianno', 'Ho visto che hai una bici vintage', '2026-03-10 10:15:00'),
(4, 1, 'pietro', 'Esatto! Cosa proponi in cambio?', '2026-03-14 15:30:00'),
(5, 2, 'gianno', 'Benvenuti nel gruppo!', '2026-03-01 14:10:00'),
(6, 2, 'pietro', 'Grazie! Felice di far parte del gruppo', '2026-03-01 14:15:00'),
(7, 2, 'gianno', 'Ho appena trovato una macchina da scrivere del 1950!', '2026-03-05 18:00:00'),
(8, 2, 'pietro', 'Wow! Foto?', '2026-03-05 18:05:00'),
(9, 3, 'gianno', 'Scambio completato con successo!', '2026-02-15 09:05:00'),
(10, 3, 'pietro', 'Perfetto! Grazie mille', '2026-02-15 09:10:00');

-- --------------------------------------------------------

--
-- Struttura della tabella `PartecipaChat`
--

CREATE TABLE `PartecipaChat` (
  `idChat` int(11) NOT NULL,
  `User` varchar(50) NOT NULL,
  `dataAdesione` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dump dei dati per la tabella `PartecipaChat`
--

INSERT INTO `PartecipaChat` (`idChat`, `User`, `dataAdesione`) VALUES
(1, 'gianno', '2026-03-10 10:00:00'),
(1, 'pietro', '2026-03-10 10:00:00'),
(2, 'gianno', '2026-03-01 14:00:00'),
(2, 'pietro', '2026-03-01 14:05:00'),
(3, 'gianno', '2026-02-15 09:00:00'),
(3, 'pietro', '2026-02-15 09:00:00'),
(4, 'gianno', '2026-03-15 21:45:01'),
(4, 'pietro', '2026-03-15 21:45:01'),
(5, 'gianno', '2026-03-29 13:36:18'),
(5, 'pietro', '2026-03-29 13:36:18'),
(7, 'chiara_style', '2026-04-10 06:37:28'),
(7, 'gianno', '2026-04-10 06:37:28');

-- --------------------------------------------------------

--
-- Struttura della tabella `Permesso`
--

CREATE TABLE `Permesso` (
  `idPermesso` int(11) NOT NULL,
  `nomePermesso` varchar(100) NOT NULL,
  `descrizione` text NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dump dei dati per la tabella `Permesso`
--

INSERT INTO `Permesso` (`idPermesso`, `nomePermesso`, `descrizione`) VALUES
(1, 'create_chat', 'Può creare chat private o di gruppo'),
(2, 'send_message', 'Può inviare messaggi in chat'),
(3, 'view_chat', 'Può vedere chat a cui partecipa'),
(4, 'delete_own_message', 'Può eliminare i propri messaggi'),
(5, 'send_friend_request', 'Può inviare richieste amicizia'),
(6, 'accept_friend_request', 'Può accettare richieste amicizia'),
(7, 'reject_friend_request', 'Può rifiutare richieste amicizia'),
(8, 'subscribe_swapplus', 'Può sottoscrivere abbonamento Swap+'),
(9, 'view_own_swapplus', 'Può vedere il proprio abbonamento'),
(10, 'upload_product', 'Può caricare un prodotto'),
(11, 'send_trade_request', 'Può inviare richiesta scambio'),
(12, 'write_review', 'Può scrivere recensione'),
(13, 'edit_account', 'Può modificare il proprio account'),
(14, 'send_report', 'Può inviare segnalazioni'),
(15, 'manage_user_reports', 'Gestione segnalazioni utente'),
(16, 'ban_user', 'Banna utenti'),
(17, 'suspend_user', 'Sospende utenti'),
(18, 'escalate_report_to_admin', 'Invia segnalazioni gravi ad admin'),
(19, 'remove_inappropriate_content', 'Rimuove contenuti inappropriati'),
(20, 'moderate_chat', 'Moderazione chat e messaggi'),
(21, 'manage_platform_policies', 'Gestione policy piattaforma'),
(22, 'manage_critical_reports', 'Gestione segnalazioni critiche'),
(23, 'appoint_moderator', 'Nomina moderatori'),
(24, 'manage_moderators', 'Gestione moderatori'),
(25, 'manage_users', 'Gestione utenti'),
(26, 'manage_couriers', 'Gestione corrieri'),
(27, 'manage_product_categories', 'Gestione categorie prodotto'),
(28, 'manage_sensitive_data', 'Gestione dati riservati piattaforma'),
(29, 'manage_orders', 'Gestione ordini / consegne'),
(30, 'update_shipping_info', 'Aggiorna dati spedizione'),
(31, 'manage_orders', 'Gestione ordini / consegne'),
(32, 'update_shipping_info', 'Aggiorna dati spedizione'),
(33, 'manage_orders', 'Gestione ordini / consegne'),
(34, 'update_shipping_info', 'Aggiorna dati spedizione'),
(35, 'manage_orders', 'Gestione ordini / consegne'),
(36, 'update_shipping_info', 'Aggiorna dati spedizione');

-- --------------------------------------------------------

--
-- Struttura della tabella `Policies`
--

CREATE TABLE `Policies` (
  `idPolicy` int(11) NOT NULL,
  `terminiServizio` text NOT NULL,
  `privacyPolicy` text NOT NULL,
  `codiceCondotta` text NOT NULL,
  `dataUltimaModifica` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dump dei dati per la tabella `Policies`
--

INSERT INTO `Policies` (`idPolicy`, `terminiServizio`, `privacyPolicy`, `codiceCondotta`, `dataUltimaModifica`) VALUES
(1, '<h2>Termini di Servizio</h2><p>Benvenuti su SwapHub. Lo scambio di beni deve avvenire in modo equo e solidale...</p>', '<h2>Privacy Policy</h2><p>I tuoi dati sono trattati secondo il GDPR. Non vendiamo i tuoi dati a terzi...</p>', '<h2>Codice di Condotta</h2><p>Sii rispettoso, non truffare, e imballa bene i pacchi prima di spedirli.</p>', '2026-03-29 13:52:47');

-- --------------------------------------------------------

--
-- Struttura della tabella `Prodotto`
--

CREATE TABLE `Prodotto` (
  `idProdotto` int(11) NOT NULL,
  `Titolo` varchar(200) NOT NULL,
  `Descrizione` text NOT NULL,
  `Disponibilità` enum('disponibile','in_scambio','scambiato','') NOT NULL DEFAULT 'disponibile',
  `img` varchar(255) NOT NULL,
  `dataPubblicazione` timestamp NOT NULL DEFAULT current_timestamp(),
  `Condizioni` enum('Eccellente','Buono','Discreto','Guasto') NOT NULL,
  `User` varchar(50) NOT NULL,
  `NomeCategoria` varchar(100) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dump dei dati per la tabella `Prodotto`
--

INSERT INTO `Prodotto` (`idProdotto`, `Titolo`, `Descrizione`, `Disponibilità`, `img`, `dataPubblicazione`, `Condizioni`, `User`, `NomeCategoria`) VALUES
(1, 'iPhone 13 Pro', 'Tenuto benissimo, batteria 90%, completo di scatola.', 'disponibile', 'uploads/prod/iphone.jpg', '2026-03-29 13:52:47', 'Eccellente', 'gianno', 'Elettronica'),
(2, 'Giacca di pelle Vintage', 'Taglia L, anni 80, vera pelle. Perfetta per la moto.', 'disponibile', 'uploads/prod/giacca.jpg', '2026-03-29 13:52:47', 'Buono', 'sara_vintage', 'Abbigliamento'),
(3, 'PS5 con 2 controller', 'Come nuova, usata pochissimo causa mancanza di tempo.', 'in_scambio', 'uploads/prod/ps5.jpg', '2026-03-29 13:52:47', 'Eccellente', 'pietro', 'Elettronica'),
(4, 'Macchina da scrivere Olivetti', 'Funzionante ma da pulire, nastro da cambiare.', 'scambiato', 'uploads/prod/olivetti.jpg', '2026-03-29 13:52:47', 'Discreto', 'sara_vintage', 'Collezionismo'),
(5, 'Bici da corsa', 'Telaio in carbonio, ruote nuove, usata pochissimo.', 'scambiato', 'uploads/prod/bici.jpg', '2026-03-29 13:52:47', 'Buono', 'gianno', 'Veicoli'),
(6, 'Smartphone Rotto', 'Si accende ma lo schermo è distrutto.', 'disponibile', 'uploads/prod/rotto.jpg', '2026-03-29 13:52:47', 'Guasto', 'bad_user99', 'Elettronica'),
(7, 'Enciclopedia Treccani', 'Collezione completa, ottime condizioni.', 'disponibile', 'uploads/prod/default.jpg', '2026-03-29 20:55:36', 'Eccellente', 'elena_books', 'Libri'),
(8, 'Racchetta da Tennis', 'Modello professionale, usata poco.', 'disponibile', 'uploads/prod/default.jpg', '2026-03-29 20:55:36', 'Buono', 'marco_sport', 'Sport'),
(9, 'Vaso in Ceramica', 'Vaso decorativo fatto a mano.', 'disponibile', 'uploads/prod/default.jpg', '2026-03-29 20:55:36', 'Eccellente', 'chiara_style', 'Casa'),
(10, 'Zaino Trekking', 'Capienza 60L, perfetto per escursioni.', 'disponibile', 'uploads/prod/default.jpg', '2026-03-29 20:55:36', 'Buono', 'marco_sport', 'Sport'),
(11, 'Manga One Piece 1-10', 'Primi dieci volumi della serie.', 'disponibile', 'uploads/prod/default.jpg', '2026-03-29 20:55:36', 'Discreto', 'elena_books', 'Libri'),
(12, 'Lampada Design', 'Lampada da tavolo anni 70.', 'disponibile', 'uploads/prod/default.jpg', '2026-03-29 20:55:36', 'Buono', 'chiara_style', 'Casa');

-- --------------------------------------------------------

--
-- Struttura della tabella `Recensioni`
--

CREATE TABLE `Recensioni` (
  `idRecensione` int(11) NOT NULL,
  `idScambio` int(11) NOT NULL,
  `UserRecensore` varchar(50) NOT NULL,
  `rating` tinyint(1) NOT NULL,
  `commento` text NOT NULL,
  `infoAggiuntive` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dump dei dati per la tabella `Recensioni`
--

INSERT INTO `Recensioni` (`idRecensione`, `idScambio`, `UserRecensore`, `rating`, `commento`, `infoAggiuntive`) VALUES
(1, 2, 'sara_vintage', 5, 'Gianno è super affidabile! La bici è perfetta.', '2026-03-29 13:52:47'),
(2, 2, 'gianno', 4, 'Ottimo scambio, la macchina da scrivere è un bel pezzo da collezione.', '2026-03-29 13:52:47');

-- --------------------------------------------------------

--
-- Struttura della tabella `RichiesteAmicizia`
--

CREATE TABLE `RichiesteAmicizia` (
  `idRichiesta` int(11) NOT NULL,
  `UserMittente` varchar(50) NOT NULL,
  `UserDestinatario` varchar(50) NOT NULL,
  `commento` text NOT NULL,
  `dataInvio` timestamp NOT NULL DEFAULT current_timestamp(),
  `stato` enum('inviata','accettata','rifiutata','') NOT NULL DEFAULT 'inviata'
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dump dei dati per la tabella `RichiesteAmicizia`
--

INSERT INTO `RichiesteAmicizia` (`idRichiesta`, `UserMittente`, `UserDestinatario`, `commento`, `dataInvio`, `stato`) VALUES
(1, 'gianno', 'pietro', '', '2026-03-16 08:33:02', 'accettata'),
(2, 'sara_vintage', 'gianno', 'Ciao! Mi piacciono i tuoi articoli vintage.', '2026-03-29 13:52:47', 'inviata'),
(3, 'bad_user99', 'pietro', '', '2026-03-29 13:52:47', 'rifiutata'),
(4, 'elena_books', 'gianno', 'Ciao Gianni, mi interessano i tuoi articoli!', '2026-03-29 20:55:36', 'inviata'),
(5, 'marco_sport', 'gianno', 'Vorrei scambiare la mia racchetta con qualcosa di tuo.', '2026-03-29 20:55:36', 'inviata'),
(6, 'chiara_style', 'gianno', 'Complimenti per il profilo vintage!', '2026-03-29 20:55:36', 'inviata');

-- --------------------------------------------------------

--
-- Struttura della tabella `Ruolo`
--

CREATE TABLE `Ruolo` (
  `idRuolo` int(11) NOT NULL,
  `nomeRuolo` varchar(50) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dump dei dati per la tabella `Ruolo`
--

INSERT INTO `Ruolo` (`idRuolo`, `nomeRuolo`) VALUES
(1, 'Admin'),
(4, 'Corriere'),
(2, 'Moderatore'),
(3, 'Swapper');

-- --------------------------------------------------------

--
-- Struttura della tabella `RuoloPermesso`
--

CREATE TABLE `RuoloPermesso` (
  `idRuolo` int(11) NOT NULL,
  `idPermesso` int(11) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dump dei dati per la tabella `RuoloPermesso`
--

INSERT INTO `RuoloPermesso` (`idRuolo`, `idPermesso`) VALUES
(1, 21),
(1, 22),
(1, 23),
(1, 24),
(1, 25),
(1, 26),
(1, 27),
(1, 28),
(2, 15),
(2, 16),
(2, 17),
(2, 18),
(2, 19),
(2, 20),
(3, 1),
(3, 2),
(3, 3),
(3, 4),
(3, 5),
(3, 6),
(3, 7),
(3, 8),
(3, 9),
(3, 10),
(3, 11),
(3, 12),
(3, 13),
(3, 14),
(4, 2),
(4, 3),
(4, 29),
(4, 30),
(4, 31),
(4, 32),
(4, 33),
(4, 34),
(4, 35),
(4, 36);

-- --------------------------------------------------------

--
-- Struttura della tabella `Scambio`
--

CREATE TABLE `Scambio` (
  `idScambio` int(11) NOT NULL,
  `idProdottoMit` int(11) NOT NULL,
  `idProdottoDest` int(11) NOT NULL,
  `idUtenteMit` varchar(50) NOT NULL,
  `idUtenteDest` varchar(50) NOT NULL,
  `dataInizio` timestamp NOT NULL DEFAULT current_timestamp(),
  `dataFine` timestamp NULL DEFAULT NULL,
  `stato` enum('proposto','accettato','completato','annullato','in_consegna') NOT NULL DEFAULT 'proposto'
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dump dei dati per la tabella `Scambio`
--

INSERT INTO `Scambio` (`idScambio`, `idProdottoMit`, `idProdottoDest`, `idUtenteMit`, `idUtenteDest`, `dataInizio`, `dataFine`, `stato`) VALUES
(1, 3, 1, 'pietro', 'gianno', '2026-03-29 13:52:47', NULL, 'proposto'),
(2, 4, 5, 'sara_vintage', 'gianno', '2026-03-29 13:52:47', NULL, 'completato'),
(3, 2, 6, 'sara_vintage', 'bad_user99', '2026-03-29 13:52:47', NULL, 'in_consegna'),
(4, 1, 7, 'gianno', 'elena_books', '2026-03-29 20:55:36', NULL, 'proposto');

-- --------------------------------------------------------

--
-- Struttura della tabella `Segnalazioni`
--

CREATE TABLE `Segnalazioni` (
  `idSegnalazione` int(11) NOT NULL,
  `UserSegnalazione` varchar(50) NOT NULL,
  `Tipo` enum('utente','prodotto','messaggio') NOT NULL DEFAULT 'prodotto',
  `idProdotto` int(11) DEFAULT NULL,
  `idMessaggioSegnalato` int(11) DEFAULT NULL,
  `UserSegnalato` varchar(50) NOT NULL,
  `idModeratore` varchar(50) DEFAULT NULL,
  `priorità` enum('bassa','media','alta','urgente') NOT NULL DEFAULT 'media',
  `commento` text NOT NULL,
  `stato` enum('aperta','in_gestione','risolta','rifiutata','escalated') NOT NULL DEFAULT 'aperta',
  `dataInvio` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dump dei dati per la tabella `Segnalazioni`
--

INSERT INTO `Segnalazioni` (`idSegnalazione`, `UserSegnalazione`, `Tipo`, `idProdotto`, `idMessaggioSegnalato`, `UserSegnalato`, `idModeratore`, `priorità`, `commento`, `stato`, `dataInvio`) VALUES
(1, 'sara_vintage', 'prodotto', 6, NULL, 'bad_user99', NULL, 'media', 'L\'utente carica solo oggetti rotti e invia spam nelle chat.', 'aperta', '2026-03-29 13:52:47'),
(2, 'gianno', 'utente', NULL, NULL, 'bad_user99', 'mod_luigi', 'alta', 'Mi ha chiesto soldi fuori dalla piattaforma per concludere lo scambio.', 'in_gestione', '2026-03-29 13:52:47'),
(3, 'pietro', 'utente', NULL, NULL, 'bad_user99', NULL, 'urgente', 'Minacce in chat privata dopo che ho rifiutato uno scambio.', 'escalated', '2026-03-29 13:52:47');

-- --------------------------------------------------------

--
-- Struttura della tabella `SwapPlus`
--

CREATE TABLE `SwapPlus` (
  `idAbbonamento` int(11) NOT NULL,
  `user` varchar(50) NOT NULL,
  `dataInizio` date NOT NULL,
  `dataFine` date NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dump dei dati per la tabella `SwapPlus`
--

INSERT INTO `SwapPlus` (`idAbbonamento`, `user`, `dataInizio`, `dataFine`) VALUES
(2, 'gianno', '2026-01-01', '2027-01-01'),
(3, 'pietro', '2026-03-15', '2027-03-15');

-- --------------------------------------------------------

--
-- Struttura della tabella `UtenteRuolo`
--

CREATE TABLE `UtenteRuolo` (
  `username` varchar(50) NOT NULL,
  `idRuolo` int(11) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dump dei dati per la tabella `UtenteRuolo`
--

INSERT INTO `UtenteRuolo` (`username`, `idRuolo`) VALUES
('admin_boss', 1),
('mod_luigi', 2),
('bad_user99', 3),
('chiara_style', 3),
('elena_books', 3),
('gianno', 3),
('marco_sport', 3),
('pietro', 3),
('sara_vintage', 3),
('corriere_fedex', 4);

-- --------------------------------------------------------

--
-- Struttura della tabella `utenti`
--

CREATE TABLE `utenti` (
  `username` varchar(50) NOT NULL,
  `password` char(100) NOT NULL,
  `salt` char(32) NOT NULL,
  `bgcolor` char(30) NOT NULL,
  `Nome` varchar(100) DEFAULT NULL,
  `Cognome` varchar(100) DEFAULT NULL,
  `Email` varchar(150) DEFAULT NULL,
  `localita` varchar(100) DEFAULT NULL,
  `fotoprofilo` varchar(255) DEFAULT 'uploads/profile/default.png',
  `DataCreazioneProf` timestamp NOT NULL DEFAULT current_timestamp(),
  `NomeAzienda` text DEFAULT NULL,
  `isBanned` tinyint(1) NOT NULL DEFAULT 0,
  `suspendedUntil` datetime DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dump dei dati per la tabella `utenti`
--

INSERT INTO `utenti` (`username`, `password`, `salt`, `bgcolor`, `Nome`, `Cognome`, `Email`, `localita`, `fotoprofilo`, `DataCreazioneProf`, `NomeAzienda`, `isBanned`, `suspendedUntil`) VALUES
('admin_boss', 'eb7f625bb67ba078cdd655ce48e98a2467b5502168e3a29dfd962f2ff3a52cbb', 'd09095bba1782bbc987a5bd4cd67e86a', 'ff0000', 'Mario', 'Rossi', 'admin@swaphub.it', 'Roma, Italia', 'uploads/profile/default.png', '2026-03-29 13:52:47', NULL, 0, NULL),
('bad_user99', 'eb7f625bb67ba078cdd655ce48e98a2467b5502168e3a29dfd962f2ff3a52cbb', 'd09095bba1782bbc987a5bd4cd67e86a', '333333', 'Giacomo', 'Truffi', 'truffa@swaphub.it', 'Torino, Italia', 'uploads/profile/default.png', '2026-03-29 13:52:47', NULL, 0, NULL),
('chiara_style', 'eb7f625bb67ba078cdd655ce48e98a2467b5502168e3a29dfd962f2ff3a52cbb', 'd09095bba1782bbc987a5bd4cd67e86a', 'ffcc00', 'Chiara', 'Neri', 'chiara@email.it', 'Napoli, Italia', 'uploads/profile/default.png', '2026-03-29 20:55:36', NULL, 0, NULL),
('corriere_fedex', 'eb7f625bb67ba078cdd655ce48e98a2467b5502168e3a29dfd962f2ff3a52cbb', 'd09095bba1782bbc987a5bd4cd67e86a', '0000ff', 'Federico', 'Esposito', 'spedizioni@fedex.it', 'Napoli, Italia', 'uploads/profile/default.png', '2026-03-29 13:52:47', NULL, 0, NULL),
('elena_books', 'eb7f625bb67ba078cdd655ce48e98a2467b5502168e3a29dfd962f2ff3a52cbb', 'd09095bba1782bbc987a5bd4cd67e86a', 'f0f0f0', 'Elena', 'Gialli', 'elena@email.it', 'Bologna, Italia', 'uploads/profile/default.png', '2026-03-29 20:55:36', NULL, 0, NULL),
('gianno', 'a7186a11f5ac8f10d42858e53e86f7c9cdad89cb072d414e117af4115dbaab65', 'd09095bba1782bbc987a5bd4cd67e86a', '8e7e43', 'Gianni', 'Morandi', 'gianni@porte.it', 'Reggio nell\'Emilia, Emilia-Romagna, Italia', 'uploads/profile/default.png', '2026-03-13 11:16:44', NULL, 0, NULL),
('lucia_green', 'pass123', '', '', 'Lucia', 'Verdi', 'lucia@email.it', 'Milano', 'uploads/profile/default.png', '2026-03-29 20:53:05', NULL, 0, NULL),
('marco_sport', 'eb7f625bb67ba078cdd655ce48e98a2467b5502168e3a29dfd962f2ff3a52cbb', 'd09095bba1782bbc987a5bd4cd67e86a', 'a0c0f0', 'Marco', 'Rizzo', 'marco@email.it', 'Palermo, Italia', 'uploads/profile/default.png', '2026-03-29 20:55:36', NULL, 0, NULL),
('mario88', 'pass123', '', '', 'Mario', 'Rossi', 'mario@email.it', 'Roma', 'uploads/profile/default.png', '2026-03-29 20:53:05', NULL, 0, NULL),
('mod_luigi', 'eb7f625bb67ba078cdd655ce48e98a2467b5502168e3a29dfd962f2ff3a52cbb', 'd09095bba1782bbc987a5bd4cd67e86a', '00ff00', 'Luigi', 'Verdi', 'mod@swaphub.it', 'Milano, Italia', 'uploads/profile/default.png', '2026-03-29 13:52:47', NULL, 0, NULL),
('pietro', '79be1b6158f3484e709b2b90656516893725edf1f188e2e10139ec09b72f7192', 'f28827455d85343769b993ff9f6d10fe', '32cd32', 'Pietro', 'Morandi', 'pie@tro.it', 'New York, Stati Uniti d\'America', 'uploads/profile/default.png', '2026-03-15 17:20:52', NULL, 0, NULL),
('roberto_tech', 'pass123', '', '', 'Roberto', 'Bianchi', 'roberto@email.it', 'Torino', 'uploads/profile/default.png', '2026-03-29 20:53:05', NULL, 0, NULL),
('sara_vintage', 'eb7f625bb67ba078cdd655ce48e98a2467b5502168e3a29dfd962f2ff3a52cbb', 'd09095bba1782bbc987a5bd4cd67e86a', 'ff00ff', 'Sara', 'Bianchi', 'sara@swaphub.it', 'Firenze, Italia', 'uploads/profile/default.png', '2026-03-29 13:52:47', NULL, 0, NULL),
('sofia_libri', 'pass123', '', '', 'Sofia', 'Galli', 'sofia@email.it', 'Firenze', 'uploads/profile/default.png', '2026-03-29 20:53:05', NULL, 0, NULL);

-- --------------------------------------------------------

--
-- Struttura stand-in per le viste `vista_chat_utente`
-- (Vedi sotto per la vista effettiva)
--
CREATE TABLE `vista_chat_utente` (
`idChat` int(11)
,`nomeChat` varchar(200)
,`tipoChat` enum('privata','gruppo','scambio','')
,`stato` enum('attiva','archiviata','chiusa','')
,`numPartecipanti` int(5)
,`dataCreazione` timestamp
,`descrizione` text
,`username` varchar(50)
,`dataAdesione` timestamp
,`totMessaggi` bigint(21)
,`ultimoMessaggio` mediumtext
,`dataUltimoMessaggio` timestamp /* mariadb-5.3 */
,`autoreUltimoMessaggio` varchar(50)
);

-- --------------------------------------------------------

--
-- Struttura stand-in per le viste `vista_richieste_amicizia`
-- (Vedi sotto per la vista effettiva)
--
CREATE TABLE `vista_richieste_amicizia` (
`idRichiesta` int(11)
,`UserMittente` varchar(50)
,`UserDestinatario` varchar(50)
,`dataInvio` timestamp
,`stato` enum('inviata','accettata','rifiutata','')
,`nomeMittente` varchar(100)
,`cognomeMittente` varchar(100)
,`nomeRicevente` varchar(100)
,`cognomeRicevente` varchar(100)
);

-- --------------------------------------------------------

--
-- Struttura stand-in per le viste `vista_swapplus_utente`
-- (Vedi sotto per la vista effettiva)
--
CREATE TABLE `vista_swapplus_utente` (
`idAbbonamento` int(11)
,`username` varchar(50)
,`dataInizio` date
,`dataFine` date
,`giorniRimanenti` int(8)
,`giorniTrascorsi` int(8)
,`durataGiorni` int(8)
,`statoAbbonamento` varchar(20)
);

--
-- Indici per le tabelle scaricate
--

--
-- Indici per le tabelle `Categoria`
--
ALTER TABLE `Categoria`
  ADD PRIMARY KEY (`NomeCategoria`);

--
-- Indici per le tabelle `Chat`
--
ALTER TABLE `Chat`
  ADD PRIMARY KEY (`idChat`),
  ADD KEY `idScambioFk` (`idScambio`),
  ADD KEY `tipoChat` (`tipoChat`);

--
-- Indici per le tabelle `Consegna`
--
ALTER TABLE `Consegna`
  ADD PRIMARY KEY (`idConsegna`),
  ADD KEY `idScambio` (`idScambio`),
  ADD KEY `idCorriere` (`idCorriere`),
  ADD KEY `stato` (`stato`);

--
-- Indici per le tabelle `Messaggi`
--
ALTER TABLE `Messaggi`
  ADD PRIMARY KEY (`idMessaggio`),
  ADD KEY `idChat` (`idChat`),
  ADD KEY `User` (`User`),
  ADD KEY `dataInvio` (`dataInvio`);

--
-- Indici per le tabelle `PartecipaChat`
--
ALTER TABLE `PartecipaChat`
  ADD PRIMARY KEY (`idChat`,`User`),
  ADD KEY `UserFk` (`User`);

--
-- Indici per le tabelle `Permesso`
--
ALTER TABLE `Permesso`
  ADD PRIMARY KEY (`idPermesso`);

--
-- Indici per le tabelle `Policies`
--
ALTER TABLE `Policies`
  ADD PRIMARY KEY (`idPolicy`);

--
-- Indici per le tabelle `Prodotto`
--
ALTER TABLE `Prodotto`
  ADD PRIMARY KEY (`idProdotto`),
  ADD KEY `NomeCategoria` (`NomeCategoria`);

--
-- Indici per le tabelle `Recensioni`
--
ALTER TABLE `Recensioni`
  ADD PRIMARY KEY (`idRecensione`),
  ADD KEY `idScambio1` (`idScambio`),
  ADD KEY `UserRecensore1` (`UserRecensore`);

--
-- Indici per le tabelle `RichiesteAmicizia`
--
ALTER TABLE `RichiesteAmicizia`
  ADD PRIMARY KEY (`idRichiesta`),
  ADD KEY `UserMittente` (`UserMittente`),
  ADD KEY `UserDestinatario` (`UserDestinatario`),
  ADD KEY `stato` (`stato`);

--
-- Indici per le tabelle `Ruolo`
--
ALTER TABLE `Ruolo`
  ADD PRIMARY KEY (`idRuolo`),
  ADD UNIQUE KEY `nomeRuolo` (`nomeRuolo`);

--
-- Indici per le tabelle `RuoloPermesso`
--
ALTER TABLE `RuoloPermesso`
  ADD PRIMARY KEY (`idRuolo`,`idPermesso`),
  ADD KEY `idPermesso` (`idPermesso`);

--
-- Indici per le tabelle `Scambio`
--
ALTER TABLE `Scambio`
  ADD PRIMARY KEY (`idScambio`),
  ADD KEY `idUtenteMit` (`idUtenteMit`),
  ADD KEY `idUtenteDest` (`idUtenteDest`),
  ADD KEY `idProdottoMit` (`idProdottoMit`),
  ADD KEY `idProdottoDest` (`idProdottoDest`);

--
-- Indici per le tabelle `Segnalazioni`
--
ALTER TABLE `Segnalazioni`
  ADD PRIMARY KEY (`idSegnalazione`),
  ADD KEY `UserSegnalanteFk` (`UserSegnalazione`),
  ADD KEY `IdProdottoFk` (`idProdotto`),
  ADD KEY `UserSegnalatoFk` (`UserSegnalato`),
  ADD KEY `idModeratore` (`idModeratore`);

--
-- Indici per le tabelle `SwapPlus`
--
ALTER TABLE `SwapPlus`
  ADD PRIMARY KEY (`idAbbonamento`),
  ADD KEY `user` (`user`),
  ADD KEY `dataFine` (`dataFine`);

--
-- Indici per le tabelle `UtenteRuolo`
--
ALTER TABLE `UtenteRuolo`
  ADD PRIMARY KEY (`username`),
  ADD KEY `idRuolo` (`idRuolo`);

--
-- Indici per le tabelle `utenti`
--
ALTER TABLE `utenti`
  ADD PRIMARY KEY (`username`),
  ADD UNIQUE KEY `Email` (`Email`);

--
-- AUTO_INCREMENT per le tabelle scaricate
--

--
-- AUTO_INCREMENT per la tabella `Chat`
--
ALTER TABLE `Chat`
  MODIFY `idChat` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=8;

--
-- AUTO_INCREMENT per la tabella `Consegna`
--
ALTER TABLE `Consegna`
  MODIFY `idConsegna` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=2;

--
-- AUTO_INCREMENT per la tabella `Messaggi`
--
ALTER TABLE `Messaggi`
  MODIFY `idMessaggio` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=11;

--
-- AUTO_INCREMENT per la tabella `Permesso`
--
ALTER TABLE `Permesso`
  MODIFY `idPermesso` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=37;

--
-- AUTO_INCREMENT per la tabella `Policies`
--
ALTER TABLE `Policies`
  MODIFY `idPolicy` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=2;

--
-- AUTO_INCREMENT per la tabella `Prodotto`
--
ALTER TABLE `Prodotto`
  MODIFY `idProdotto` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=13;

--
-- AUTO_INCREMENT per la tabella `Recensioni`
--
ALTER TABLE `Recensioni`
  MODIFY `idRecensione` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=3;

--
-- AUTO_INCREMENT per la tabella `RichiesteAmicizia`
--
ALTER TABLE `RichiesteAmicizia`
  MODIFY `idRichiesta` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=7;

--
-- AUTO_INCREMENT per la tabella `Ruolo`
--
ALTER TABLE `Ruolo`
  MODIFY `idRuolo` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=5;

--
-- AUTO_INCREMENT per la tabella `Scambio`
--
ALTER TABLE `Scambio`
  MODIFY `idScambio` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=5;

--
-- AUTO_INCREMENT per la tabella `Segnalazioni`
--
ALTER TABLE `Segnalazioni`
  MODIFY `idSegnalazione` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=4;

--
-- AUTO_INCREMENT per la tabella `SwapPlus`
--
ALTER TABLE `SwapPlus`
  MODIFY `idAbbonamento` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=4;

-- --------------------------------------------------------

--
-- Struttura per vista `vista_chat_utente`
--
DROP TABLE IF EXISTS `vista_chat_utente`;

CREATE ALGORITHM=UNDEFINED DEFINER=`mecja_kevin`@`localhost` SQL SECURITY DEFINER VIEW `vista_chat_utente`  AS SELECT `c`.`idChat` AS `idChat`, `c`.`nome` AS `nomeChat`, `c`.`tipoChat` AS `tipoChat`, `c`.`stato` AS `stato`, `c`.`numPartecipanti` AS `numPartecipanti`, `c`.`dataCreazione` AS `dataCreazione`, `c`.`descrizione` AS `descrizione`, `pc`.`User` AS `username`, `pc`.`dataAdesione` AS `dataAdesione`, (select count(0) from `Messaggi` where `Messaggi`.`idChat` = `c`.`idChat`) AS `totMessaggi`, (select `Messaggi`.`contenuto` from `Messaggi` where `Messaggi`.`idChat` = `c`.`idChat` order by `Messaggi`.`dataInvio` desc limit 1) AS `ultimoMessaggio`, (select `Messaggi`.`dataInvio` from `Messaggi` where `Messaggi`.`idChat` = `c`.`idChat` order by `Messaggi`.`dataInvio` desc limit 1) AS `dataUltimoMessaggio`, (select `Messaggi`.`User` from `Messaggi` where `Messaggi`.`idChat` = `c`.`idChat` order by `Messaggi`.`dataInvio` desc limit 1) AS `autoreUltimoMessaggio` FROM (`Chat` `c` join `PartecipaChat` `pc` on(`c`.`idChat` = `pc`.`idChat`)) ;

-- --------------------------------------------------------

--
-- Struttura per vista `vista_richieste_amicizia`
--
DROP TABLE IF EXISTS `vista_richieste_amicizia`;

CREATE ALGORITHM=UNDEFINED DEFINER=`mecja_kevin`@`localhost` SQL SECURITY DEFINER VIEW `vista_richieste_amicizia`  AS SELECT `ra`.`idRichiesta` AS `idRichiesta`, `ra`.`UserMittente` AS `UserMittente`, `ra`.`UserDestinatario` AS `UserDestinatario`, `ra`.`dataInvio` AS `dataInvio`, `ra`.`stato` AS `stato`, `u1`.`Nome` AS `nomeMittente`, `u1`.`Cognome` AS `cognomeMittente`, `u2`.`Nome` AS `nomeRicevente`, `u2`.`Cognome` AS `cognomeRicevente` FROM ((`RichiesteAmicizia` `ra` join `utenti` `u1` on(`ra`.`UserMittente` = `u1`.`username`)) join `utenti` `u2` on(`ra`.`UserDestinatario` = `u2`.`username`)) ;

-- --------------------------------------------------------

--
-- Struttura per vista `vista_swapplus_utente`
--
DROP TABLE IF EXISTS `vista_swapplus_utente`;

CREATE ALGORITHM=UNDEFINED DEFINER=`mecja_kevin`@`localhost` SQL SECURITY DEFINER VIEW `vista_swapplus_utente`  AS SELECT `s`.`idAbbonamento` AS `idAbbonamento`, `s`.`user` AS `username`, `s`.`dataInizio` AS `dataInizio`, `s`.`dataFine` AS `dataFine`, to_days(`s`.`dataFine`) - to_days(curdate()) AS `giorniRimanenti`, to_days(curdate()) - to_days(`s`.`dataInizio`) AS `giorniTrascorsi`, to_days(`s`.`dataFine`) - to_days(`s`.`dataInizio`) AS `durataGiorni`, cast(case when curdate() > `s`.`dataFine` then 'scaduto' when to_days(`s`.`dataFine`) - to_days(curdate()) <= 7 then 'in_scadenza' else 'attivo' end as char(20) charset utf8mb4) AS `statoAbbonamento` FROM `SwapPlus` AS `s` ;

--
-- Limiti per le tabelle scaricate
--

--
-- Limiti per la tabella `Chat`
--
ALTER TABLE `Chat`
  ADD CONSTRAINT `idScambioFk` FOREIGN KEY (`idScambio`) REFERENCES `Scambio` (`idScambio`) ON DELETE CASCADE;

--
-- Limiti per la tabella `Consegna`
--
ALTER TABLE `Consegna`
  ADD CONSTRAINT `idScambio` FOREIGN KEY (`idScambio`) REFERENCES `Scambio` (`idScambio`) ON DELETE CASCADE;

--
-- Limiti per la tabella `PartecipaChat`
--
ALTER TABLE `PartecipaChat`
  ADD CONSTRAINT `UserFk` FOREIGN KEY (`User`) REFERENCES `utenti` (`username`) ON DELETE CASCADE,
  ADD CONSTRAINT `idChatFk` FOREIGN KEY (`idChat`) REFERENCES `Chat` (`idChat`) ON DELETE CASCADE;

--
-- Limiti per la tabella `Prodotto`
--
ALTER TABLE `Prodotto`
  ADD CONSTRAINT `NomeCategoria` FOREIGN KEY (`NomeCategoria`) REFERENCES `Categoria` (`NomeCategoria`) ON UPDATE CASCADE;

--
-- Limiti per la tabella `Recensioni`
--
ALTER TABLE `Recensioni`
  ADD CONSTRAINT `UserRecensore1` FOREIGN KEY (`UserRecensore`) REFERENCES `utenti` (`username`) ON DELETE CASCADE,
  ADD CONSTRAINT `idScambio1` FOREIGN KEY (`idScambio`) REFERENCES `Scambio` (`idScambio`) ON DELETE CASCADE;

--
-- Limiti per la tabella `RichiesteAmicizia`
--
ALTER TABLE `RichiesteAmicizia`
  ADD CONSTRAINT `UserDestinatarioFk` FOREIGN KEY (`UserDestinatario`) REFERENCES `utenti` (`username`) ON DELETE CASCADE,
  ADD CONSTRAINT `UserMittenteFk` FOREIGN KEY (`UserMittente`) REFERENCES `utenti` (`username`) ON DELETE CASCADE;

--
-- Limiti per la tabella `RuoloPermesso`
--
ALTER TABLE `RuoloPermesso`
  ADD CONSTRAINT `RuoloPermesso_ibfk_1` FOREIGN KEY (`idRuolo`) REFERENCES `Ruolo` (`idRuolo`) ON DELETE CASCADE,
  ADD CONSTRAINT `RuoloPermesso_ibfk_2` FOREIGN KEY (`idPermesso`) REFERENCES `Permesso` (`idPermesso`) ON DELETE CASCADE;

--
-- Limiti per la tabella `Scambio`
--
ALTER TABLE `Scambio`
  ADD CONSTRAINT `idProdottoDest` FOREIGN KEY (`idProdottoDest`) REFERENCES `Prodotto` (`idProdotto`),
  ADD CONSTRAINT `idProdottoMit` FOREIGN KEY (`idProdottoMit`) REFERENCES `Prodotto` (`idProdotto`),
  ADD CONSTRAINT `idUtenteDest` FOREIGN KEY (`idUtenteDest`) REFERENCES `utenti` (`username`) ON DELETE CASCADE,
  ADD CONSTRAINT `idUtenteMit` FOREIGN KEY (`idUtenteMit`) REFERENCES `utenti` (`username`) ON DELETE CASCADE;

--
-- Limiti per la tabella `Segnalazioni`
--
ALTER TABLE `Segnalazioni`
  ADD CONSTRAINT `IdProdottoFk` FOREIGN KEY (`idProdotto`) REFERENCES `Prodotto` (`idProdotto`) ON DELETE CASCADE,
  ADD CONSTRAINT `UserSegnalanteFk` FOREIGN KEY (`UserSegnalazione`) REFERENCES `utenti` (`username`) ON DELETE CASCADE,
  ADD CONSTRAINT `UserSegnalatoFk` FOREIGN KEY (`UserSegnalato`) REFERENCES `utenti` (`username`) ON DELETE CASCADE,
  ADD CONSTRAINT `idModeratore` FOREIGN KEY (`idModeratore`) REFERENCES `utenti` (`username`) ON DELETE CASCADE;

--
-- Limiti per la tabella `SwapPlus`
--
ALTER TABLE `SwapPlus`
  ADD CONSTRAINT `User` FOREIGN KEY (`user`) REFERENCES `utenti` (`username`) ON DELETE CASCADE;

--
-- Limiti per la tabella `UtenteRuolo`
--
ALTER TABLE `UtenteRuolo`
  ADD CONSTRAINT `UtenteRuolo_ibfk_1` FOREIGN KEY (`username`) REFERENCES `utenti` (`username`) ON DELETE CASCADE,
  ADD CONSTRAINT `UtenteRuolo_ibfk_2` FOREIGN KEY (`idRuolo`) REFERENCES `Ruolo` (`idRuolo`) ON DELETE CASCADE;
COMMIT;

/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
