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
