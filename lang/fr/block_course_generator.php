<?php

/**
 * Strings for component 'block_course_generator'
 *
 * @package    block_course_generator
 * @copyright  2025 Josemaria Bolanos <admin@mako.digital>
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
*/

$string['pluginname'] = 'Générateur de cours Dixeo';
$string['blocktitle'] = '';
$string['activity_chooser:addinstance'] = 'Ajouter un bloc Générateur de cours Dixeo';

// Carrousel d'exemples
$string['prompt1'] = 'Créer un cours complet et convivial pour les débutants sur la programmation Python, incluant des exercices pratiques, des quiz et un apprentissage basé sur des projets.';
$string['prompt2'] = 'Générer un cours approfondi couvrant l\'histoire des civilisations anciennes, explorant les événements clés, les pratiques culturelles et les contributions significatives.';
$string['prompt3'] = 'Concevoir un cours de photographie interactif qui enseigne les compétences de base, les réglages de l\'appareil photo, les techniques de composition et les tutoriels de retouche photo.';
$string['prompt4'] = 'Construire un cours complet d\'algèbre pour les lycéens avec des exemples pratiques, une résolution de problèmes étape par étape et des applications dans la vie réelle.';
$string['prompt5'] = 'Générer un cours d\'apprentissage de la langue espagnole pour les débutants en se concentrant sur le vocabulaire essentiel, la pratique de la conversation et les discussions sur le contexte culturel.';
$string['prompt6'] = 'Téléchargez vos propres fichiers pour générer un cours personnalisé. Types de fichiers pris en charge : .pptx, .docx, .pdf, .txt. Taille maximale du fichier : 20 Mo. Limite de taille totale : 50 Mo.';

// Invite
$string['heading'] = 'Que voulez-vous enseigner aujourd\'hui ?';
$string['prompt_placeholder'] = 'Entrez le cours que vous souhaitez générer : sujet, nombre de sections et quiz si nécessaire.';
$string['draganddrop'] = 'Glissez-déposez vos fichiers pour les télécharger';
$string['generate_course'] = 'Générer';
$string['totalsize'] = '<b>Taille totale :</b> {$a}';
$string['removefile'] = 'Supprimer le fichier';

// Génération
$string['heading2'] = 'Nous construisons votre cours !';
$string['inprogress'] = 'Génération du cours en cours...';
$string['dismiss'] = 'Fermer';
$string['generating_course'] = 'Veuillez patienter pendant que nous préparons votre cours. Ce processus peut prendre quelques minutes...';
$string['course_generated'] = 'Cours généré avec succès ! Vous pouvez voir le cours <a href="/course/view.php?id={$a}">ici</a>.';
$string['generate_another'] = 'Ou <a class="reset-prompt" href="/my/">générez-en un nouveau</a>.';

// Étapes
$string['step1'] = 'Validation de l\'entrée';
$string['step2'] = 'Analyse du sujet';
$string['step3'] = 'Structuration des modules';
$string['step4'] = 'Génération de contenu';
$string['step5'] = 'Finalisation des détails';

// Erreurs de fichier
$string['invalidinput'] = 'Informations requises.';
$string['descriptionorfilesrequired'] = 'Veuillez entrer une description du cours ou télécharger des fichiers pour générer le cours.';
$string['uploaderror'] = 'Erreur lors du téléchargement du fichier.';
$string['filetypeinvalid'] = 'Le type de fichier de {$a} n\'est pas pris en charge. Extensions prises en charge : .pptx, .docx, .pdf, .txt.';
$string['filetoolarge'] = 'Le fichier est trop volumineux. Veuillez télécharger un fichier de moins de 20 Mo.';
$string['totaltoolarge'] = 'La taille totale des fichiers dépasse la limite de 50 Mo. Téléchargez des fichiers plus petits ou supprimez-en un pour continuer.';

// Paramètres.
$string['settings'] = 'Générateur de cours Dixeo';
$string['error_generation_failed'] = 'La génération du cours Dixeo a échoué. 
Erreur : {$a}';
$string['error_lti_disabled'] = "La génération de cours Dixeo nécessite l'activation de l'inscription LTI sur votre plateforme";
$string['error_platform_not_registered'] = "Votre plateforme n'est pas enregistrée sur la plateforme Dixeo. Veuillez contacter votre administrateur.";

// URL de la plateforme
$string['platformurl'] = 'URL de la plateforme Dixeo';
$string['platformurl_desc'] = 'Entrez l’URL de base de la plateforme Edunao Dixeo. Le plugin ajoutera automatiquement https:// au début.';
$string['default_platformurl'] = 'https://app.dixeo.com';

// Clé API
$string['apikey'] = 'Clé API Dixeo';
$string['apikey_desc'] = "Entrez la clé API fournie par Edunao pour activer la génération de cours.";
$string['default_apikey'] = 'fa2e6c8adab11e9dcdb171681f11fdc1';

// Catégorie par défaut
$string['categoryname'] = 'Catégorie pour les cours créés';
$string['categoryname_desc'] = 'Entrez le nom de la catégorie locale où les cours seront créés.';
$string['default_categoryname'] = 'Cours Dixeo';

// Lien d’enregistrement et instructions
$string['register'] = 'Enregistrer';
$string['enterurlandkey'] = '<i class="icon fa fa-exclamation-triangle text-warning fa-fw" aria-hidden="true"></i>Entrez l’URL et la clé API de la plateforme Dixeo pour enregistrer votre site.';
$string['error_invalidurlandkey'] = '<i class="icon fa fa-exclamation-triangle text-danger fa-fw" aria-hidden="true"></i>Nous n\'avons pas pu enregistrer votre plateforme. Veuillez vérifier l’URL et la clé API.';
$string['needsregistration'] = '<i class="icon fa fa-exclamation-triangle text-warning fa-fw" aria-hidden="true"></i>Vous devez enregistrer votre plateforme pour utiliser le générateur de cours.';
$string['alreadyregistered'] = '<i class="icon fa fa-check text-success fa-fw" aria-hidden="true"></i>Votre plateforme est déjà enregistrée.';

