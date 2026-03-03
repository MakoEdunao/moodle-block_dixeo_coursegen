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

$string['alreadyregistered'] = '<i class="icon fa fa-check text-success fa-fw" aria-hidden="true"></i>Ihre Plattform ist bereits registriert.';
$string['apikey'] = 'Dixeo-API-Schlüssel';
$string['apikey_desc'] = 'Geben Sie den von Dixeo bereitgestellten API-Schlüssel ein, um die Kursgenerierung zu aktivieren.';
$string['attachfile'] = 'Quelldokument anhängen';
$string['blocktitle'] = '';
$string['categoryname'] = 'Kategorie für erstellte Kurse';
$string['categoryname_desc'] = 'Geben Sie den Namen der lokalen Kategorie ein, in der Kurse erstellt werden.';
$string['course_generated'] = 'Ihr Kurs «<b> {$a} </b>» wurde erfolgreich erstellt!';
$string['default_apikey'] = '7a853610542f7debe1a854a11d429e74';
$string['default_categoryname'] = 'Dixeo-Kurse';
$string['default_platformurl'] = 'https://dixeo.com';
$string['descriptionorfilesrequired'] = 'Bitte geben Sie eine Kursbeschreibung ein oder laden Sie Dateien hoch, um den Kurs zu generieren.';
$string['dixeo_coursegen:addinstance'] = 'Einen Dixeo-Kursgenerator-Block hinzufügen';
$string['dixeo_coursegen:myaddinstance'] = 'Einen neuen Dixeo-Kursgenerator-Block zu meiner Übersicht hinzufügen';
$string['dixeo_coursegen:create'] = 'Kurse mit dem Dixeo-Kursgenerator erstellen';
$string['draganddrop'] = 'Dateien zum Hochladen hierher ziehen';
$string['enterurlandkey'] = '<i class="icon fa fa-exclamation-triangle text-warning fa-fw" aria-hidden="true"></i>Geben Sie die URL und den API-Schlüssel der Dixeo-Plattform ein, um Ihre Website zu registrieren.';
$string['error_generation_failed'] = 'Beim Erstellen des Kurses ist ein unerwarteter Fehler aufgetreten. Bitte versuchen Sie es erneut.';
$string['error_invalidurlandkey'] = '<i class="icon fa fa-exclamation-triangle text-danger fa-fw" aria-hidden="true"></i>Ihre Plattform konnte nicht registriert werden. Bitte prüfen Sie URL und API-Schlüssel.';
$string['error_platform_not_registered'] = 'Ihre Plattform ist nicht auf der Dixeo-Plattform registriert. Bitten Sie Ihren Administrator, die Registrierung hier abzuschließen: {$a}';
$string['error_title'] = 'Hoppla!';
$string['filetoolarge'] = 'Datei ist zu groß. Bitte laden Sie eine Datei unter 20 MB hoch.';
$string['filetypeinvalid'] = 'Der Dateityp {$a} wird nicht unterstützt. Unterstützte Formate: .pptx, .docx, .pdf, .txt.';
$string['generate_another'] = 'Neuen Kurs generieren';
$string['generate_course'] = 'Generieren';
$string['generating_course'] = 'Bitte warten Sie, wir bereiten Ihren Kurs vor. Dies kann einige Minuten dauern...';
$string['heading'] = 'Was möchten Sie heute unterrichten?';
$string['heading2'] = 'Wir erstellen Ihren Kurs!';
$string['invalidinput'] = 'Angaben erforderlich.';
$string['myaddinstance'] = 'Einen neuen Dixeo-Kursgenerator-Block zu meiner Übersicht hinzufügen';
$string['needsregistration'] = '<i class="icon fa fa-exclamation-triangle text-warning fa-fw m-0" aria-hidden="true"></i>
<span class="needs-registration">Sie müssen Ihre Plattform registrieren, um den Kursgenerator zu nutzen.</span>
<span class="needs-saving hidden">Speichern Sie zuerst Ihre Änderungen, bevor Sie mit der Registrierung fortfahren.</span>';
$string['platformurl'] = 'Dixeo-Plattform-URL';
$string['platformurl_desc'] = 'Geben Sie die Basis-URL der Dixeo-Plattform ein.';
$string['pluginname'] = 'Dixeo-Kursgenerator';
$string['privacy:metadata:email'] = 'E-Mail-Adresse der Nutzerin/des Nutzers beim Zugriff auf den LTI Consumer';
$string['privacy:metadata:externalpurpose'] = 'Der LTI Consumer übermittelt Nutzerinformationen und Kontext an den LTI Tool Provider.';
$string['privacy:metadata:firstname'] = 'Vorname der Nutzerin/des Nutzers beim Zugriff auf den LTI Consumer';
$string['privacy:metadata:lastname'] = 'Nachname der Nutzerin/des Nutzers beim Zugriff auf den LTI Consumer';
$string['privacy:metadata:userid'] = 'ID der Nutzerin/des Nutzers beim Zugriff auf den LTI Consumer';
$string['prompt_placeholder'] = 'Geben Sie den gewünschten Kurs ein: Thema, Anzahl der Abschnitte und ggf. Quiz.';
$string['register'] = 'Registrieren';
$string['removefile'] = 'Datei entfernen';
$string['settings'] = 'Dixeo-Kursgenerator';
$string['step1'] = 'Eingabe wird geprüft';
$string['step2'] = 'Thema wird analysiert';
$string['step3'] = 'Module werden strukturiert';
$string['step4'] = 'Inhalt wird generiert';
$string['step5'] = 'Details werden fertiggestellt';
$string['totalsize'] = '<b>Gesamtgröße:</b> {$a}';
$string['totaltoolarge'] = 'Die Gesamtgröße der Dateien überschreitet das Limit von 50 MB. Laden Sie kleinere Dateien hoch oder entfernen Sie eine Datei.';
$string['uploaderror'] = 'Fehler beim Hochladen der Datei.';
$string['view_course'] = 'Kurs anzeigen';
