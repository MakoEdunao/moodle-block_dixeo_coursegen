<?php

/**
 * Strings for component 'block_course_generator'
 *
 * @package    block_course_generator
 * @copyright  2025 Josemaria Bolanos <admin@mako.digital>
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
*/

$string['pluginname'] = 'Generatore di Corsi Dixeo';
$string['blocktitle'] = '';
$string['activity_chooser:addinstance'] = 'Aggiungi un blocco Generatore di Corsi Dixeo';

// Esempi di carosello
$string['prompt1'] = 'Crea un corso completo e adatto ai principianti sulla programmazione Python, includendo esercizi pratici, quiz e apprendimento basato su progetti.';
$string['prompt2'] = 'Genera un corso approfondito che copre la storia delle antiche civiltà, esplorando eventi chiave, pratiche culturali e contributi significativi.';
$string['prompt3'] = 'Progetta un corso di fotografia interattivo che insegna competenze di base, impostazioni della fotocamera, tecniche di composizione e tutorial di fotoritocco.';
$string['prompt4'] = 'Costruisci un corso completo di algebra per studenti delle scuole superiori con esempi pratici, risoluzione di problemi passo-passo e applicazioni nella vita reale.';
$string['prompt5'] = 'Genera un corso di apprendimento della lingua spagnola per principianti, concentrandosi sul vocabolario essenziale, pratica di conversazione e discussioni sul contesto culturale.';
$string['prompt6'] = 'Carica i tuoi file per generare un corso personalizzato. Tipi di file supportati: .pptx, .docx, .pdf, .txt. Dimensione massima del file: 20MB. Limite totale di dimensione: 50MB.';

// Prompt
$string['heading'] = 'Cosa vuoi insegnare oggi?';
$string['prompt_placeholder'] = 'Inserisci il corso che vuoi generare: argomento, numero di sezioni e quiz se necessario.';
$string['draganddrop'] = 'Trascina e rilascia i tuoi file per caricarli';
$string['generate_course'] = 'Genera';
$string['totalsize'] = '<b>Dimensione totale:</b> {$a}';
$string['removefile'] = 'Rimuovi file';

// Generazione
$string['heading2'] = 'Stiamo costruendo il tuo corso!';
$string['inprogress'] = 'Generazione del corso in corso...';
$string['dismiss'] = 'Chiudi';
$string['generating_course'] = 'Attendere mentre prepariamo il tuo corso. Questo processo potrebbe richiedere alcuni minuti...';
$string['course_generated'] = 'Corso generato con successo! Puoi visualizzare il corso <a href="/course/view.php?id={$a}">qui</a>.';
$string['generate_another'] = 'Oppure <a class="reset-prompt" href="/my/">genera un nuovo corso</a>.';

// Passaggi
$string['step1'] = 'Validazione dell\'input';
$string['step2'] = 'Analisi del soggetto';
$string['step3'] = 'Strutturazione dei moduli';
$string['step4'] = 'Generazione del contenuto';
$string['step5'] = 'Finalizzazione dei dettagli';

// Errori di file
$string['invalidinput'] = 'Informazioni richieste.';
$string['descriptionorfilesrequired'] = 'Inserisci una descrizione del corso o carica file per generare il corso.';
$string['uploaderror'] = 'Errore durante il caricamento del file.';
$string['filetypeinvalid'] = 'Il tipo di file di {$a} non è supportato. Estensioni supportate: .pptx, .docx, .pdf, .txt.';
$string['filetoolarge'] = 'Il file è troppo grande. Carica un file più piccolo di 20MB.';
$string['totaltoolarge'] = 'La dimensione totale dei file supera il limite di 50MB. Carica file più piccoli o rimuovi uno per continuare.';

// Impostazioni.
$string['settings'] = 'Generatore di Corsi Dixeo';
$string['error_generation_failed'] = 'Generazione del corso Dixeo non riuscita. Errore: {$a}';
$string['error_lti_disabled'] = "La generazione del corso Dixeo richiede l'abilitazione dell'iscrizione LTI sulla tua piattaforma";
$string['error_platform_not_registered'] = "La tua piattaforma non è registrata sulla piattaforma Dixeo. Contatta l'amministratore.";

// URL della piattaforma
$string['platformurl'] = 'URL della piattaforma Dixeo';
$string['platformurl_desc'] = "Inserisci l'URL base della piattaforma Edunao Dixeo. Il plugin aggiungerà automaticamente https://.";
$string['default_platformurl'] = 'https://app.dixeo.com';

// Chiave API
$string['apikey'] = 'Chiave API Dixeo';
$string['apikey_desc'] = "Inserisci la chiave API fornita da Edunao per attivare la generazione dei corsi.";
$string['default_apikey'] = 'fa2e6c8adab11e9dcdb171681f11fdc1';

// Categoria predefinita
$string['categoryname'] = 'Categoria per i corsi creati';
$string['categoryname_desc'] = 'Inserisci il nome della categoria locale in cui verranno creati i corsi.';
$string['default_categoryname'] = 'Corsi Dixeo';

// Link di registrazione e istruzioni
$string['register'] = 'Registra';
$string['enterurlandkey'] = '<i class="icon fa fa-exclamation-triangle text-warning fa-fw" aria-hidden="true"></i>Inserisci l\'URL e la chiave API della piattaforma Dixeo per registrare il tuo sito.';
$string['error_invalidurlandkey'] = '<i class="icon fa fa-exclamation-triangle text-danger fa-fw" aria-hidden="true"></i>Non è stato possibile registrare la tua piattaforma. Controlla l\'URL e la chiave API.';
$string['needsregistration'] = '<i class="icon fa fa-exclamation-triangle text-warning fa-fw" aria-hidden="true"></i>Devi registrare la tua piattaforma per utilizzare il generatore di corsi.';
$string['alreadyregistered'] = '<i class="icon fa fa-check text-success fa-fw" aria-hidden="true"></i>La tua piattaforma è già registrata.';
