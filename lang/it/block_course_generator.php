<?php

/**
 * Strings for component 'block_course_generator'
 *
 * @package    block_course_generator
 * @copyright  2025 Josemaria Bolanos <admin@mako.digital>
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
*/

$string['pluginname'] = 'Generatore di Corsi AI';
$string['blocktitle'] = '';
$string['activity_chooser:addinstance'] = 'Aggiungi un blocco Generatore di Corsi AI';

// Esempi di carosello
$string['prompt1'] = 'Crea un corso completo e adatto ai principianti sulla programmazione Python, includendo esercizi pratici, quiz e apprendimento basato su progetti.';
$string['prompt2'] = 'Genera un corso approfondito che copre la storia delle antiche civiltà, esplorando eventi chiave, pratiche culturali e contributi significativi.';
$string['prompt3'] = 'Progetta un corso di fotografia interattivo che insegna competenze di base, impostazioni della fotocamera, tecniche di composizione e tutorial di fotoritocco.';
$string['prompt4'] = 'Costruisci un corso completo di algebra per studenti delle scuole superiori con esempi pratici, risoluzione di problemi passo-passo e applicazioni nella vita reale.';
$string['prompt5'] = 'Genera un corso di apprendimento della lingua spagnola per principianti, concentrandosi sul vocabolario essenziale, pratica di conversazione e discussioni sul contesto culturale.';

// Prompt
$string['heading'] = 'Cosa vuoi insegnare oggi?';
$string['prompt_placeholder'] = 'Inserisci il corso che vuoi generare: argomento, numero di sezioni e quiz se necessario.';
$string['draganddrop'] = 'Trascina e rilascia i tuoi file per caricarli';
$string['generate_course'] = 'Genera';
$string['upload_instructions'] = 'Tipi di file supportati: .pptx, .docx, .pdf, .txt. Dimensione massima del file: 20MB. Limite totale di dimensione: 50MB.';
$string['totalsize'] = '<b>Dimensione totale:</b> {$a}';
$string['removefile'] = 'Rimuovi file';

// Generazione
$string['heading2'] = 'Stiamo costruendo il tuo corso!';
$string['generating_course'] = 'Attendere mentre prepariamo il tuo corso. Questo processo potrebbe richiedere alcuni minuti...';
$string['course_generated'] = 'Corso generato con successo! Puoi <a href="https://123-test.edunao.com/course/view.php?id={$a}">visualizzare il corso</a> o <a class="reset-prompt" href="#">generare un nuovo corso</a>.';

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
