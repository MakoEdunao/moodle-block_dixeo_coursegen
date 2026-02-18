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

$string['alreadyregistered'] = '<i class="icon fa fa-check text-success fa-fw" aria-hidden="true"></i>A sua plataforma já está registada.';
$string['apikey'] = 'Chave API Dixeo';
$string['apikey_desc'] = 'Introduza a chave API fornecida pela Dixeo para ativar a geração de cursos.';
$string['attachfile'] = 'Anexar documento de origem';
$string['blocktitle'] = '';
$string['categoryname'] = 'Categoria para cursos criados';
$string['categoryname_desc'] = 'Introduza o nome da categoria local onde os cursos serão criados.';
$string['course_generated'] = 'O seu curso «<b> {$a} </b>» foi gerado com sucesso!';
$string['default_apikey'] = '7a853610542f7debe1a854a11d429e74';
$string['default_categoryname'] = 'Cursos Dixeo';
$string['default_platformurl'] = 'https://dixeo.com';
$string['descriptionorfilesrequired'] = 'Introduza uma descrição do curso ou carregue ficheiros para gerar o curso.';
$string['dixeo_coursegen:addinstance'] = 'Adicionar um bloco Gerador de Cursos Dixeo';
$string['dixeo_coursegen:myaddinstance'] = 'Adicionar um novo bloco Gerador de Cursos Dixeo ao meu painel';
$string['dixeo_coursegen:create'] = 'Criar cursos com o Gerador de Cursos Dixeo';
$string['draganddrop'] = 'Arraste e largue os seus ficheiros para carregar';
$string['enterurlandkey'] = '<i class="icon fa fa-exclamation-triangle text-warning fa-fw" aria-hidden="true"></i>Introduza o URL e a chave API da plataforma Dixeo para registar o seu site.';
$string['error_generation_failed'] = 'Ocorreu um erro inesperado ao criar o curso. Tente novamente.';
$string['error_invalidurlandkey'] = '<i class="icon fa fa-exclamation-triangle text-danger fa-fw" aria-hidden="true"></i>Não foi possível registar a sua plataforma. Verifique o URL e a chave API.';
$string['error_platform_not_registered'] = 'A sua plataforma não está registada na plataforma Dixeo. Peça ao seu administrador para concluir o registo aqui: {$a}';
$string['error_title'] = 'Ops!';
$string['filetoolarge'] = 'O ficheiro é demasiado grande. Carregue um ficheiro com menos de 20 MB.';
$string['filetypeinvalid'] = 'O tipo de ficheiro {$a} não é suportado. Extensões suportadas: .pptx, .docx, .pdf, .txt.';
$string['generate_another'] = 'Gerar um novo curso';
$string['generate_course'] = 'Gerar';
$string['generating_course'] = 'Aguarde enquanto preparamos o seu curso. Este processo pode demorar alguns minutos...';
$string['heading'] = 'O que quer ensinar hoje?';
$string['heading2'] = 'Estamos a construir o seu curso!';
$string['invalidinput'] = 'Informação obrigatória.';
$string['myaddinstance'] = 'Adicionar um novo bloco Gerador de Cursos Dixeo ao meu painel';
$string['needsregistration'] = '<i class="icon fa fa-exclamation-triangle text-warning fa-fw m-0" aria-hidden="true"></i>
<span class="needs-registration">Precisa de registar a sua plataforma para usar o gerador de cursos.</span>
<span class="needs-saving hidden">Guarde primeiro as suas alterações antes de prosseguir com o registo.</span>';
$string['platformurl'] = 'URL da plataforma Dixeo';
$string['platformurl_desc'] = 'Introduza o URL base da plataforma Dixeo.';
$string['pluginname'] = 'Gerador de Cursos Dixeo';
$string['privacy:metadata:email'] = 'O endereço de e-mail do utilizador que acede ao consumidor LTI';
$string['privacy:metadata:externalpurpose'] = 'O consumidor LTI fornece informações do utilizador e contexto ao fornecedor da ferramenta LTI.';
$string['privacy:metadata:firstname'] = 'O nome próprio do utilizador que acede ao consumidor LTI';
$string['privacy:metadata:lastname'] = 'O apelido do utilizador que acede ao consumidor LTI';
$string['privacy:metadata:userid'] = 'O ID do utilizador que acede ao consumidor LTI';
$string['prompt_placeholder'] = 'Introduza o curso que deseja gerar: tópico, número de secções e questionário se necessário.';
$string['register'] = 'Registar';
$string['removefile'] = 'Remover ficheiro';
$string['settings'] = 'Gerador de Cursos Dixeo';
$string['step1'] = 'A validar dados';
$string['step2'] = 'A analisar o tema';
$string['step3'] = 'A estruturar módulos';
$string['step4'] = 'A gerar conteúdo';
$string['step5'] = 'A finalizar detalhes';
$string['totalsize'] = '<b>Tamanho total:</b> {$a}';
$string['totaltoolarge'] = 'O tamanho total dos ficheiros excede o limite de 50 MB. Carregue ficheiros mais pequenos ou remova um para continuar.';
$string['uploaderror'] = 'Erro ao carregar ficheiro.';
$string['view_course'] = 'Ver o seu curso';

// Editor strings
$string['editor_loading'] = 'A carregar estrutura do curso...';
$string['editor_invalid_data'] = 'Dados de estrutura inválidos';
$string['editor_save'] = 'Guardar';
$string['editor_cancel'] = 'Cancelar';
$string['editor_reload'] = 'Recarregar';
$string['editor_save_now'] = 'Guardar agora';
$string['editor_autosave_in'] = 'Auto-guardar em:';
$string['editor_version'] = 'Versão:';
$string['editor_version_loading'] = 'A carregar...';
$string['editor_disabled'] = 'Desativado';
$string['editor_edit'] = 'Editar';
$string['editor_duplicate'] = 'Duplicar';
$string['editor_delete'] = 'Eliminar';
$string['editor_confirm_delete'] = 'Confirmar eliminação';
$string['editor_delete_module_confirm'] = 'Tem a certeza de que deseja eliminar este módulo?';
$string['editor_delete_section_confirm'] = 'Tem a certeza de que deseja eliminar esta secção e todos os seus módulos?';
$string['editor_reload_confirm'] = 'Recarregar estrutura do servidor? As alterações não guardadas serão perdidas.';
$string['editor_unsaved_changes'] = 'Tem alterações não guardadas. Tem a certeza de que deseja sair?';
$string['editor_saving'] = 'A guardar...';
$string['editor_saved'] = 'Guardado!';
$string['editor_divergent_save'] = 'Guardar divergente';
$string['editor_divergent_message'] = 'Estava a trabalhar a partir de uma versão mais antiga. As suas alterações foram guardadas como versão {$a} para preservar o histórico. Este é um novo ramo a partir do seu ponto de partida.';
$string['editor_ok'] = 'OK';
$string['editor_add_section'] = 'Adicionar nova secção';
$string['editor_add_activity'] = 'Adicionar nova atividade';
$string['editor_undo'] = 'Desfazer';
$string['editor_redo'] = 'Refazer';
$string['editor_new_section_title'] = 'Nova secção';
$string['editor_new_section_summary'] = 'Descreva do que trata esta secção';
$string['editor_new_module_type'] = 'Página';
$string['editor_new_module_title'] = 'Nova página';
$string['editor_new_module_hints'] = 'Descreva do que trata esta página';
$string['editor_copy_suffix'] = ' (Cópia)';
$string['editor_change_activity_type'] = 'Alterar tipo de atividade';
