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

$string['alreadyregistered'] = '<i class="icon fa fa-check text-success fa-fw" aria-hidden="true"></i>Tu plataforma ya está registrada.';
$string['apikey'] = 'Clave API de Dixeo';
$string['apikey_desc'] = "Introduce la clave API proporcionada por Dixeo para activar la generación de cursos.";
$string['attachfile'] = 'Adjuntar un documento fuente';
$string['blocktitle'] = '';
$string['categoryname'] = 'Categoría para cursos creados';
$string['categoryname_desc'] = 'Introduce el nombre de la categoría local donde se crearán los cursos.';
$string['course_generated'] = '¡Tu curso «<b> {$a} </b>» se ha generado correctamente!';
$string['default_apikey'] = '7a853610542f7debe1a854a11d429e74';
$string['default_categoryname'] = 'Cursos Dixeo';
$string['default_platformurl'] = 'https://dixeo.com';
$string['descriptionorfilesrequired'] = 'Por favor, introduce una descripción del curso o sube archivos para generar el curso.';
$string['dixeo_coursegen:addinstance'] = 'Agregar un bloque Generador de Cursos Dixeo';
$string['dixeo_coursegen:myaddinstance'] = 'Agregar un nuevo bloque Generador de Cursos Dixeo a mi panel';
$string['draganddrop'] = 'Arrastra y suelta tus archivos para subirlos';
$string['enterurlandkey'] = '<i class="icon fa fa-exclamation-triangle text-warning fa-fw" aria-hidden="true"></i>Introduce la URL y la clave API de la plataforma Dixeo para registrar tu sitio.';
$string['error_generation_failed'] = 'Ocurrió un error inesperado al crear el curso. Por favor, inténtalo de nuevo.';
$string['error_invalidurlandkey'] = '<i class="icon fa fa-exclamation-triangle text-danger fa-fw" aria-hidden="true"></i>No pudimos registrar tu plataforma. Por favor, verifica la URL y la clave API.';
$string['error_platform_not_registered'] = 'Tu plataforma no está registrada en la plataforma Dixeo. Por favor, pide a tu administrador que complete el registro aquí: {$a}';
$string['error_title'] = '¡Vaya!';
$string['filetoolarge'] = 'El archivo es demasiado grande. Por favor, sube un archivo menor de 20MB.';
$string['filetypeinvalid'] = 'El tipo de archivo {$a} no es compatible. Extensiones soportadas: .pptx, .docx, .pdf, .txt.';
$string['generate_another'] = 'Generar un nuevo curso';
$string['generate_course'] = 'Generar';
$string['generating_course'] = 'Por favor, espera mientras preparamos tu curso. Este proceso puede tardar unos minutos...';
$string['heading'] = '¿Qué quieres enseñar hoy?';
$string['heading2'] = '¡Estamos construyendo tu curso!';
$string['invalidinput'] = 'Información requerida.';
$string['myaddinstance'] = 'Agregar un nuevo bloque Generador de Cursos Dixeo a mi panel';
$string['needsregistration'] = '<i class="icon fa fa-exclamation-triangle text-warning fa-fw m-0" aria-hidden="true"></i>
<span class="needs-registration">Necesitas registrar tu plataforma para usar el generador de cursos.</span>
<span class="needs-saving hidden">Guarda tus cambios antes de continuar con el registro.</span>';
$string['platformurl'] = 'URL de la plataforma Dixeo';
$string['platformurl_desc'] = 'Introduce la URL base de la plataforma Dixeo.';
$string['pluginname'] = 'Generador de Cursos Dixeo';
$string['privacy:metadata:email'] = 'La dirección de correo electrónico del usuario que accede al Consumidor LTI';
$string['privacy:metadata:externalpurpose'] = 'El Consumidor LTI proporciona información de usuario y contexto al Proveedor de Herramientas LTI.';
$string['privacy:metadata:firstname'] = 'El nombre del usuario que accede al Consumidor LTI';
$string['privacy:metadata:lastname'] = 'El apellido del usuario que accede al Consumidor LTI';
$string['privacy:metadata:userid'] = 'El ID del usuario que accede al Consumidor LTI';
$string['prompt_placeholder'] = 'Introduce el curso que deseas generar: tema, número de secciones y cuestionario si es necesario.';
$string['register'] = 'Registrar';
$string['removefile'] = 'Eliminar archivo';
$string['settings'] = 'Generador de Cursos Dixeo';
$string['step1'] = 'Validando entrada';
$string['step2'] = 'Analizando tema';
$string['step3'] = 'Estructurando módulos';
$string['step4'] = 'Generando contenido';
$string['step5'] = 'Finalizando detalles';
$string['totalsize'] = '<b>Tamaño total:</b> {$a}';
$string['totaltoolarge'] = 'El tamaño total de los archivos supera el límite de 50MB. Sube archivos más pequeños o elimina uno para continuar.';
$string['uploaderror'] = 'Error al subir el archivo.';
$string['view_course'] = 'Ver tu curso';

// Editor strings
$string['editor_loading'] = 'Cargando estructura del curso...';
$string['editor_invalid_data'] = 'Datos de estructura no válidos';
$string['editor_save'] = 'Guardar';
$string['editor_cancel'] = 'Cancelar';
$string['editor_reload'] = 'Recargar';
$string['editor_save_now'] = 'Guardar ahora';
$string['editor_autosave_in'] = 'Guardado automático en:';
$string['editor_version'] = 'Versión:';
$string['editor_version_loading'] = 'Cargando...';
$string['editor_disabled'] = 'Desactivado';
$string['editor_edit'] = 'Editar';
$string['editor_duplicate'] = 'Duplicar';
$string['editor_delete'] = 'Eliminar';
$string['editor_confirm_delete'] = 'Confirmar eliminación';
$string['editor_delete_module_confirm'] = '¿Está seguro de que desea eliminar este módulo?';
$string['editor_delete_section_confirm'] = '¿Está seguro de que desea eliminar esta sección y todos sus módulos?';
$string['editor_reload_confirm'] = '¿Recargar estructura desde el servidor? Los cambios no guardados se perderán.';
$string['editor_unsaved_changes'] = 'Tiene cambios sin guardar. ¿Está seguro de que desea salir?';
$string['editor_saving'] = 'Guardando...';
$string['editor_saved'] = '¡Guardado!';
$string['editor_divergent_save'] = 'Guardado divergente';
$string['editor_divergent_message'] = 'Estaba trabajando desde una versión anterior. Sus cambios se han guardado como versión {$a} para preservar el historial. Esta es una nueva rama desde su punto de partida.';
$string['editor_ok'] = 'OK';
$string['editor_add_section'] = 'Añadir nueva sección';
$string['editor_add_activity'] = 'Añadir nueva actividad';
$string['editor_undo'] = 'Deshacer';
$string['editor_redo'] = 'Rehacer';
$string['editor_new_section_title'] = 'Nueva sección';
$string['editor_new_section_summary'] = 'Describa de qué trata esta sección';
$string['editor_new_module_type'] = 'Página';
$string['editor_new_module_title'] = 'Nueva página';
$string['editor_new_module_hints'] = 'Describa de qué trata esta página';
$string['editor_copy_suffix'] = ' (Copia)';
$string['editor_change_activity_type'] = 'Cambiar tipo de actividad';
