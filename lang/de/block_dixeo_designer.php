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
 * Strings for component 'block_dixeo_designer'
 *
 * @package    block_dixeo_designer
 * @author     Josemaria Bolanos <admin@mako.digital>
 * @copyright  2025 Dixeo (contact@dixeo.com)
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

defined('MOODLE_INTERNAL') || die();

// General.
$string['pluginname'] = 'Dixeo-Kursdesigner';
$string['settings'] = 'Dixeo-Kursdesigner';
$string['blocktitle'] = 'Dixeo-Kursdesigner';
$string['designacourse'] = 'Einen Kurs gestalten';

// Capabilities.
$string['dixeo_designer:addinstance'] = 'Einen Dixeo-Kursdesigner-Block hinzufügen';
$string['dixeo_designer:myaddinstance'] = 'Einen neuen Dixeo-Kursdesigner-Block zu meiner Übersicht hinzufügen';
$string['dixeo_designer:create'] = 'Kurse mit dem Dixeo-Kursdesigner erstellen';
$string['dixeo_designer:manage'] = 'Dixeo-Kursdesigner verwalten';
$string['manage'] = 'Dixeo-Kursdesigner verwalten';
$string['myaddinstance'] = 'Einen neuen Dixeo-Kursdesigner-Block zu meiner Übersicht hinzufügen';

// Platform settings.
$string['apikey'] = 'Dixeo-API-Schlüssel';
$string['apikey_desc'] = 'Geben Sie den von Dixeo bereitgestellten API-Schlüssel ein, um die Kursgenerierung zu aktivieren.';
$string['platformurl'] = 'Dixeo-Plattform-URL';
$string['platformurl_desc'] = 'Geben Sie die Basis-URL der Dixeo-Plattform ein.';
$string['categoryname'] = 'Kategorie für erstellte Kurse';
$string['categoryname_desc'] = 'Geben Sie den Namen der lokalen Kategorie ein, in der Kurse erstellt werden.';
$string['coursetemplate'] = 'Vorlage für die pädagogische Struktur';
$string['coursetemplate_desc'] = 'Wählen Sie die vom Dixeo-Kursdesigner verwendete Vorlage für die pädagogische Struktur.';
$string['coursetemplate_none'] = 'Keine';
$string['coursetemplate_template_alpha'] = 'Vorlage Alpha';
$string['coursetemplate_template_beta'] = 'Vorlage Beta';
$string['coursetemplate_template_gamma'] = 'Vorlage Gamma';
$string['default_apikey'] = '7a853610542f7debe1a854a11d429e74';
$string['default_categoryname'] = 'Dixeo-Kurse';
$string['default_platformurl'] = 'https://dixeo.com';
$string['register'] = 'Registrieren';
$string['alreadyregistered'] = '<i class="icon fa fa-check text-success fa-fw" aria-hidden="true"></i>Ihre Plattform ist bereits registriert.';
$string['enterurlandkey'] = '<i class="icon fa fa-exclamation-triangle text-warning fa-fw" aria-hidden="true"></i>Geben Sie die URL und den API-Schlüssel der Dixeo-Plattform ein, um Ihre Website zu registrieren.';
$string['error_invalidurlandkey'] = '<i class="icon fa fa-exclamation-triangle text-danger fa-fw" aria-hidden="true"></i>Ihre Plattform konnte nicht registriert werden. Bitte prüfen Sie URL und API-Schlüssel.';
$string['error_platform_not_registered'] = 'Ihre Plattform ist nicht auf der Dixeo-Plattform registriert. Bitten Sie Ihren Administrator, die Registrierung hier abzuschließen: {$a}';
$string['needsregistration'] = '<i class="icon fa fa-exclamation-triangle text-warning fa-fw m-0" aria-hidden="true"></i>
<span class="needs-registration">Sie müssen Ihre Plattform registrieren, um den Kursdesigner zu nutzen.</span>
<span class="needs-saving hidden">Speichern Sie zuerst Ihre Änderungen, bevor Sie mit der Registrierung fortfahren.</span>';

// Course design flow.
$string['heading'] = 'Was möchten Sie heute unterrichten?';
$string['heading2'] = 'Wir erstellen Ihren Kurs!';
$string['prompt_placeholder'] = 'Geben Sie den gewünschten Kurs ein: Thema, Anzahl der Abschnitte und ggf. Quiz.';
$string['generate_course'] = 'Generieren';
$string['generate_course_tooltip'] = 'Kurs jetzt generieren';
$string['generate_structure_btn'] = 'Generieren';
$string['generate_structure_tooltip'] = 'Kursstruktur generieren';
$string['regenerate_structure_tooltip'] = 'Kursstruktur neu generieren';
$string['generatecoursestructure'] = 'Struktur gestalten';
$string['generate_another'] = 'Neuen Kurs generieren';
$string['descriptionorfilesrequired'] = 'Bitte geben Sie eine Kursbeschreibung ein oder laden Sie Dateien hoch, um den Kurs zu generieren.';
$string['generating_course'] = 'Bitte warten Sie, wir bereiten Ihren Kurs vor. Dies kann einige Minuten dauern...';
$string['course_generated'] = 'Ihr Kurs «<b> {$a} </b>» wurde erfolgreich erstellt!';
$string['view_course'] = 'Kurs anzeigen';
$string['create_course'] = 'Kurs erstellen';
$string['resources'] = 'Ressourcen';
$string['designer_draft_course_name'] = '[Entwurf] Neuer Kurs';
$string['task_cleanup_draft_courses'] = 'Kursentwürfe älter als 1 Stunde löschen';
$string['designer_default_file_prompt'] = 'Eine Kursstruktur auf Basis der hochgeladenen Dateien generieren.';
$string['designer_default_module_prompt'] = 'Den vollständigen Lerninhalt für dieses Modul generieren.';
$string['designer_filesyncfailed'] = 'Die hochgeladenen Dateien konnten vor der Modulgenerierung nicht synchronisiert werden: {$a}';
$string['designer_filesynctimeout'] = 'Die hochgeladenen Dateien wurden nicht rechtzeitig für die Modulgenerierung synchronisiert.';
$string['designer_module_timeout'] = 'Das Modul „{$a}“ wurde nicht rechtzeitig generiert. Der Server könnte ausgelastet sein; versuchen Sie es später erneut oder erstellen Sie die Aktivität manuell.';
$string['step1'] = 'Eingabe wird geprüft';
$string['step2'] = 'Thema wird analysiert';
$string['step3'] = 'Module werden strukturiert';
$string['step4'] = 'Inhalt wird generiert';
$string['step5'] = 'Details werden fertiggestellt';
$string['invalidinput'] = 'Angaben erforderlich.';
$string['error_title'] = 'Hoppla!';
$string['error_generation_failed'] = 'Kurserstellung fehlgeschlagen: {$a}. Bitte versuchen Sie es erneut.';

// File uploads.
$string['attachfile'] = 'Quelldokument anhängen';
$string['draganddrop'] = 'Dateien zum Hochladen hierher ziehen';
$string['removefile'] = 'Datei entfernen';
$string['totalsize'] = '<b>Gesamtgröße:</b> {$a}';
$string['filetoolarge'] = 'Datei ist zu groß. Bitte laden Sie eine Datei unter 20 MB hoch.';
$string['filetypeinvalid'] = 'Der Dateityp {$a} wird nicht unterstützt. Unterstützte Formate: .pptx, .docx, .pdf, .txt.';
$string['totaltoolarge'] = 'Die Gesamtgröße der Dateien überschreitet das Limit von 50 MB. Laden Sie kleinere Dateien hoch oder entfernen Sie eine Datei.';
$string['uploaderror'] = 'Fehler beim Hochladen der Datei.';
$string['uploading_files'] = 'Wird hochgeladen…';

// Designer interface.
$string['designer_loading'] = 'Kursstruktur wird geladen...';
$string['designer_regenerate'] = 'Neu generieren';
$string['designer_invalid_data'] = 'Ungültige Strukturdaten';
$string['designer_save'] = 'Speichern';
$string['designer_cancel'] = 'Abbrechen';
$string['designer_reload'] = 'Neu laden';
$string['designer_save_now'] = 'Jetzt speichern';
$string['designer_autosave_in'] = 'Auto-Speicherung in:';
$string['designer_version'] = 'Version:';
$string['designer_version_loading'] = 'Wird geladen...';
$string['designer_disabled'] = 'Deaktiviert';
$string['designer_edit'] = 'Bearbeiten';
$string['designer_duplicate'] = 'Duplizieren';
$string['designer_delete'] = 'Löschen';
$string['designer_confirm_delete'] = 'Löschen bestätigen';
$string['designer_delete_module_confirm'] = 'Möchten Sie dieses Modul wirklich löschen?';
$string['designer_delete_section_confirm'] = 'Möchten Sie diesen Abschnitt und alle zugehörigen Module wirklich löschen?';
$string['designer_reload_confirm'] = 'Struktur vom Server neu laden? Nicht gespeicherte Änderungen gehen verloren.';
$string['designer_unsaved_changes'] = 'Sie haben ungespeicherte Änderungen. Möchten Sie wirklich fortfahren?';
$string['designer_saving'] = 'Wird gespeichert...';
$string['designer_saved'] = 'Gespeichert!';
$string['designer_divergent_save'] = 'Abweichendes Speichern';
$string['designer_divergent_message'] = 'Sie haben von einer älteren Version ausgearbeitet. Ihre Änderungen wurden als Version {$a} gespeichert, um die bestehende Versionshistorie zu erhalten. Dies ist ein neuer Zweig von Ihrem Ausgangspunkt.';
$string['designer_ok'] = 'OK';
$string['designer_add_section'] = 'Neuen Abschnitt hinzufügen';
$string['designer_add_activity'] = 'Neue Aktivität hinzufügen';
$string['designer_undo'] = 'Rückgängig';
$string['designer_redo'] = 'Wiederholen';
$string['designer_new_section_title'] = 'Neuer Abschnitt';
$string['designer_new_section_summary'] = 'Beschreiben Sie, worum es in diesem Abschnitt geht';
$string['designer_new_module_type'] = 'Seite';
$string['designer_new_module_title'] = 'Neue Seite';
$string['designer_new_module_summary'] = 'Beschreiben Sie, worum es in dieser Aktivität geht';
$string['designer_new_module_instructions'] = 'Fügen Sie Anweisungen für Lernende hinzu (optional)';
$string['designer_copy_suffix'] = ' (Kopie)';
$string['designer_change_activity_type'] = 'Aktivitätstyp ändern';
$string['designer_expand_all'] = 'Alle aufklappen';
$string['designer_collapse_all'] = 'Alle zuklappen';

// Privacy.
$string['privacy:metadata:userid'] = 'Die ID der Nutzerin/des Nutzers beim Zugriff auf den LTI Consumer';
$string['privacy:metadata:email'] = 'E-Mail-Adresse der Nutzerin/des Nutzers beim Zugriff auf den LTI Consumer';
$string['privacy:metadata:firstname'] = 'Vorname der Nutzerin/des Nutzers beim Zugriff auf den LTI Consumer';
$string['privacy:metadata:lastname'] = 'Nachname der Nutzerin/des Nutzers beim Zugriff auf den LTI Consumer';
$string['privacy:metadata:externalpurpose'] = 'Der LTI Consumer übermittelt Nutzerinformationen und Kontext an den LTI Tool Provider.';
