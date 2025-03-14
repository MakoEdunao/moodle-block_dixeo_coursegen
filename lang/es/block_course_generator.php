<?php

/**
 * Strings for component 'block_course_generator'
 *
 * @package    block_course_generator
 * @copyright  2025 Josemaria Bolanos <admin@mako.digital>
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
*/

$string['pluginname'] = 'Generador de Cursos AI';
$string['blocktitle'] = '';
$string['activity_chooser:addinstance'] = 'Agregar un bloque de Generador de Cursos AI';

// Carrusel de ejemplos
$string['prompt1'] = 'Crea un curso completo y amigable para principiantes sobre programación en Python, incluyendo ejercicios prácticos, cuestionarios y aprendizaje basado en proyectos.';
$string['prompt2'] = 'Genera un curso detallado que cubra la historia de las civilizaciones antiguas, explorando eventos clave, prácticas culturales y contribuciones significativas.';
$string['prompt3'] = 'Diseña un curso interactivo de fotografía que enseñe habilidades básicas, configuraciones de cámara, técnicas de composición y tutoriales de edición de fotos.';
$string['prompt4'] = 'Construye un curso completo de álgebra para estudiantes de secundaria con ejemplos prácticos, resolución de problemas paso a paso y aplicaciones en la vida real.';
$string['prompt5'] = 'Genera un curso de aprendizaje del idioma español para principiantes, enfocándose en vocabulario esencial, práctica de conversación y discusiones sobre contexto cultural.';
$string['prompt6'] = 'Sube tus propios archivos para generar un curso personalizado. Tipos de archivos soportados: .pptx, .docx, .pdf, .txt. Tamaño máximo de archivo: 20MB. Límite de tamaño total: 50MB.';

// Solicitud
$string['heading'] = '¿Qué quieres enseñar hoy?';
$string['prompt_placeholder'] = 'Introduce el curso que deseas generar: tema, número de secciones y cuestionario si es necesario.';
$string['draganddrop'] = 'Arrastra y suelta tus archivos para subirlos';
$string['generate_course'] = 'Generar';
$string['totalsize'] = '<b>Tamaño total:</b> {$a}';
$string['removefile'] = 'Eliminar archivo';

// Generación
$string['heading2'] = '¡Estamos creando tu curso!';
$string['inprogress'] = 'Generación del curso en progreso...';
$string['dismiss'] = 'Descartar';
$string['generating_course'] = 'Por favor espera mientras preparamos tu curso. Este proceso puede tardar unos minutos...';
$string['course_generated'] = '¡Curso generado exitosamente! Puedes ver el curso <a href="/course/view.php?id={$a}">aquí</a>.';
$string['generate_another'] = 'O <a class="reset-prompt" href="/my/">genera otro</a>.';

// Pasos
$string['step1'] = 'Validando entrada';
$string['step2'] = 'Analizando tema';
$string['step3'] = 'Estructurando módulos';
$string['step4'] = 'Generando contenido';
$string['step5'] = 'Finalizando detalles';

// Errores de archivo
$string['invalidinput'] = 'Información requerida.';
$string['descriptionorfilesrequired'] = 'Por favor introduce una descripción del curso o sube archivos para generar el curso.';
$string['uploaderror'] = 'Error al subir el archivo.';
$string['filetypeinvalid'] = 'El tipo de archivo de {$a} no es soportado. Extensiones soportadas: .pptx, .docx, .pdf, .txt.';
$string['filetoolarge'] = 'El archivo es demasiado grande. Por favor sube un archivo con un tamaño menor a 20MB.';
$string['totaltoolarge'] = 'El tamaño total de los archivos excede el límite de 50MB. Sube archivos más pequeños o elimina uno de ellos para continuar.';
