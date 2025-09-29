<?php
// This file is part of Moodle - http://moodle.org/
//
// Moodle is free software: you can redistribute it and/or modify
// it under the terms of the GNU General Public License as published by
// the Free Software Foundation, either version 3 of the License, or
// (at your option) any later version.
//
// Moodle is distributed in the hope that it will be useful,
// but WITHOUT ANY WARRANTY; without even the implied warranty of
// MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
// GNU General Public License for more details.
//
// You should have received a copy of the GNU General Public License
// along with Moodle.  If not, see <http://www.gnu.org/licenses/>.

/**
 * Strings for component 'block_dixeo_coursegen'
 *
 * @package    block_dixeo_coursegen
 * @author     Josemaria Bolanos <admin@mako.digital>
 * @copyright  2025 Dixeo (contact@dixeo.com)
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

defined('MOODLE_INTERNAL') || die();

$string['alreadyregistered'] = '<i class="icon fa fa-check text-success fa-fw" aria-hidden="true"></i>La tua piattaforma è già registrata.';
$string['apikey'] = 'Chiave API Dixeo';
$string['apikey_desc'] = "Inserisci la chiave API fornita da Dixeo per attivare la generazione dei corsi.";
$string['attachfile'] = 'Allega un documento sorgente';
$string['blocktitle'] = '';
$string['categoryname'] = 'Categoria per i corsi creati';
$string['categoryname_desc'] = 'Inserisci il nome della categoria locale in cui verranno creati i corsi.';
$string['course_generated'] = 'Il tuo corso «<b> {$a} </b>» è stato generato con successo!';
$string['default_apikey'] = '7a853610542f7debe1a854a11d429e74';
$string['default_categoryname'] = 'Corsi Dixeo';
$string['default_platformurl'] = 'https://dixeo.com';
$string['descriptionorfilesrequired'] = 'Inserisci una descrizione del corso o carica dei file per generare il corso.';
$string['dixeo_coursegen:addinstance'] = 'Aggiungi un blocco Generatore di Corsi Dixeo';
$string['dixeo_coursegen:myaddinstance'] = 'Aggiungi un nuovo blocco Generatore di Corsi Dixeo alla mia dashboard';
$string['draganddrop'] = 'Trascina e rilascia i tuoi file per caricarli';
$string['enterurlandkey'] = '<i class="icon fa fa-exclamation-triangle text-warning fa-fw" aria-hidden="true"></i>Inserisci l\'URL e la chiave API della piattaforma Dixeo per registrare il tuo sito.';
$string['error_generation_failed'] = 'Si è verificato un errore imprevisto durante la creazione del corso. Riprova.';
$string['error_invalidurlandkey'] = '<i class="icon fa fa-exclamation-triangle text-danger fa-fw" aria-hidden="true"></i>Non siamo riusciti a registrare la tua piattaforma. Controlla l\'URL e la chiave API.';
$string['error_platform_not_registered'] = 'La tua piattaforma non è registrata sulla piattaforma Dixeo. Chiedi all\'amministratore di completare la registrazione qui: {$a}';
$string['error_title'] = 'Ops!';
$string['filetoolarge'] = 'Il file è troppo grande. Carica un file inferiore a 20MB.';
$string['filetypeinvalid'] = 'Il tipo di file {$a} non è supportato. Estensioni supportate: .pptx, .docx, .pdf, .txt.';
$string['generate_another'] = 'Genera un nuovo corso';
$string['generate_course'] = 'Genera';
$string['generating_course'] = 'Attendere mentre prepariamo il tuo corso. Questo processo potrebbe richiedere alcuni minuti...';
$string['heading'] = 'Cosa vuoi insegnare oggi?';
$string['heading2'] = 'Stiamo creando il tuo corso!';
$string['invalidinput'] = 'Informazioni richieste.';
$string['myaddinstance'] = 'Aggiungi un nuovo blocco Generatore di Corsi Dixeo alla mia dashboard';
$string['needsregistration'] = '<i class="icon fa fa-exclamation-triangle text-warning fa-fw m-0" aria-hidden="true"></i>
<span class="needs-registration">Devi registrare la tua piattaforma per utilizzare il generatore di corsi.</span>
<span class="needs-saving hidden">Salva prima le modifiche per procedere con la registrazione.</span>';
$string['platformurl'] = 'URL della piattaforma Dixeo';
$string['platformurl_desc'] = 'Inserisci l\'URL base della piattaforma Dixeo.';
$string['pluginname'] = 'Generatore di Corsi Dixeo';
$string['privacy:metadata:email'] = 'L\'indirizzo email dell\'utente che accede al Consumer LTI';
$string['privacy:metadata:externalpurpose'] = 'Il Consumer LTI fornisce informazioni sull\'utente e il contesto al Tool Provider LTI.';
$string['privacy:metadata:firstname'] = 'Il nome dell\'utente che accede al Consumer LTI';
$string['privacy:metadata:lastname'] = 'Il cognome dell\'utente che accede al Consumer LTI';
$string['privacy:metadata:userid'] = 'L\'ID dell\'utente che accede al Consumer LTI';
$string['prompt_placeholder'] = 'Inserisci il corso che vuoi generare: argomento, numero di sezioni e quiz se necessario.';
$string['register'] = 'Registra';
$string['removefile'] = 'Rimuovi file';
$string['settings'] = 'Generatore di Corsi Dixeo';
$string['step1'] = 'Validazione input';
$string['step2'] = 'Analisi dell\'argomento';
$string['step3'] = 'Strutturazione dei moduli';
$string['step4'] = 'Generazione dei contenuti';
$string['step5'] = 'Finalizzazione dei dettagli';
$string['totalsize'] = '<b>Dimensione totale:</b> {$a}';
$string['totaltoolarge'] = 'La dimensione totale dei file supera il limite di 50MB. Carica file più piccoli o rimuovine uno per continuare.';
$string['uploaderror'] = 'Errore nel caricamento del file.';
$string['view_course'] = 'Visualizza il tuo corso';
