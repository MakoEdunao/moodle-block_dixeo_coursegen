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

$string['alreadyregistered'] = '<i class="icon fa fa-check text-success fa-fw" aria-hidden="true"></i>Votre plateforme est déjà enregistrée.';
$string['apikey'] = 'Clé API Dixeo';
$string['apikey_desc'] = "Entrez la clé API fournie par Dixeo pour activer la génération de cours.";
$string['attachfile'] = 'Joindre un document source';
$string['blocktitle'] = '';
$string['categoryname'] = 'Catégorie pour les cours créés';
$string['categoryname_desc'] = 'Entrez le nom de la catégorie locale où les cours seront créés.';
$string['course_generated'] = 'Votre cours «<b> {$a} </b>» a été généré avec succès !';
$string['default_apikey'] = '7a853610542f7debe1a854a11d429e74';
$string['default_categoryname'] = 'Cours Dixeo';
$string['default_platformurl'] = 'https://dixeo.com';
$string['descriptionorfilesrequired'] = 'Veuillez saisir une description du cours ou télécharger des fichiers pour générer le cours.';
$string['dixeo_coursegen:addinstance'] = 'Ajouter un bloc Générateur de Cours Dixeo';
$string['dixeo_coursegen:myaddinstance'] = 'Ajouter un nouveau bloc Générateur de Cours Dixeo à mon tableau de bord';
$string['draganddrop'] = 'Glissez-déposez vos fichiers pour les télécharger';
$string['enterurlandkey'] = '<i class="icon fa fa-exclamation-triangle text-warning fa-fw" aria-hidden="true"></i>Entrez l’URL et la clé API de la plateforme Dixeo pour enregistrer votre site.';
$string['error_generation_failed'] = 'Une erreur inattendue est survenue lors de la création du cours. Veuillez réessayer.';
$string['error_invalidurlandkey'] = '<i class="icon fa fa-exclamation-triangle text-danger fa-fw" aria-hidden="true"></i>Impossible d’enregistrer votre plateforme. Veuillez vérifier l’URL et la clé API.';
$string['error_platform_not_registered'] = 'Votre plateforme n’est pas enregistrée sur la plateforme Dixeo. Veuillez demander à votre administrateur de compléter l’enregistrement ici : {$a}';
$string['error_title'] = 'Oups !';
$string['filetoolarge'] = 'Le fichier est trop volumineux. Veuillez télécharger un fichier de moins de 20 Mo.';
$string['filetypeinvalid'] = 'Le type de fichier {$a} n’est pas pris en charge. Extensions supportées : .pptx, .docx, .pdf, .txt.';
$string['generate_another'] = 'Générer un nouveau cours';
$string['generate_course'] = 'Générer';
$string['generating_course'] = 'Veuillez patienter pendant la préparation de votre cours. Ce processus peut prendre quelques minutes...';
$string['heading'] = 'Que voulez-vous enseigner aujourd’hui ?';
$string['heading2'] = 'Nous construisons votre cours !';
$string['invalidinput'] = 'Information requise.';
$string['myaddinstance'] = 'Ajouter un nouveau bloc Générateur de Cours Dixeo à mon tableau de bord';
$string['needsregistration'] = '<i class="icon fa fa-exclamation-triangle text-warning fa-fw m-0" aria-hidden="true"></i>
<span class="needs-registration">Vous devez enregistrer votre plateforme pour utiliser le générateur de cours.</span>
<span class="needs-saving hidden">Enregistrez d’abord vos modifications avant de poursuivre l’enregistrement.</span>';
$string['platformurl'] = 'URL de la plateforme Dixeo';
$string['platformurl_desc'] = 'Entrez l’URL de base de la plateforme Dixeo.';
$string['pluginname'] = 'Générateur de Cours Dixeo';
$string['privacy:metadata:email'] = 'L’adresse e-mail de l’utilisateur accédant au consommateur LTI';
$string['privacy:metadata:externalpurpose'] = 'Le consommateur LTI fournit des informations utilisateur et contexte au fournisseur d’outils LTI.';
$string['privacy:metadata:firstname'] = 'Le prénom de l’utilisateur accédant au consommateur LTI';
$string['privacy:metadata:lastname'] = 'Le nom de famille de l’utilisateur accédant au consommateur LTI';
$string['privacy:metadata:userid'] = 'L’ID de l’utilisateur accédant au consommateur LTI';
$string['prompt_placeholder'] = 'Indiquez le cours à générer : sujet, nombre de sections et quiz si nécessaire.';
$string['register'] = 'Enregistrer';
$string['removefile'] = 'Supprimer le fichier';
$string['settings'] = 'Générateur de Cours Dixeo';
$string['step1'] = 'Validation des données';
$string['step2'] = 'Analyse du sujet';
$string['step3'] = 'Structuration des modules';
$string['step4'] = 'Génération du contenu';
$string['step5'] = 'Finalisation des détails';
$string['totalsize'] = '<b>Taille totale :</b> {$a}';
$string['totaltoolarge'] = 'La taille totale des fichiers dépasse la limite de 50 Mo. Téléchargez des fichiers plus petits ou supprimez-en un pour continuer.';
$string['uploaderror'] = 'Erreur lors du téléchargement du fichier.';
$string['view_course'] = 'Voir votre cours';
