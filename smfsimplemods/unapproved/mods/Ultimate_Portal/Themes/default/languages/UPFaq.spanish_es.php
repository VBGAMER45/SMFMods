<?php
/*---------------------------------------------------------------------------------
*	Ultimate Portal
*	Version 0.3
*	Project manager: vicram10
*	Copyright 2011
*	Powered by SMFSimple.com
**********************************************************************************/

global $settings, $txt, $scripturl, $boardurl, $mbname;

//Buttons
$txt['ultport_button_save'] = 'Guardar';
$txt['ultport_button_edit'] = 'Editar';
$txt['ultport_button_delete'] = 'Eliminar';
$txt['ultport_button_add'] = 'Publicar';
$txt['ultport_button_view'] = 'Visualizar';
$txt['ultport_button_editing'] = 'Editando';
$txt['ultport_button_back'] = 'Volver';

//Titles Modules
$txt['up_module_title'] = 'Modulo';
$txt['up_module_faq_title'] = 'Preguntas Frecuentes';
$txt['up_faq_index'] = 'Indice';
$txt['up_faq_content'] = 'Contenido';

//Text Normal
$txt['up_faq_add'] = 'Agregar Pregunta &amp; Respuesta';
$txt['up_faq_add_section'] = 'Agregar Sección';
$txt['up_faq_edit'] = 'Editando Pregunta &amp; Respuesta';
$txt['up_faq_edit_section'] = 'Editando Sección';

//Add New FAQ
$txt['up_faq_add_description'] = 'Formulario para agregar nuevas Preguntas &amp; Respuestas al Modulo.';
$txt['up_faq_edit_description'] = 'Formulario para editar la Pregunta &amp; Respuesta elegido del Modulo.';
$txt['up_faq_question'] = '<strong>Pregunta</strong>
					<br/>Sera el titulo que aparecera como la Pregunta a realizar.
					<br/><strong>Ejemplo:</strong> Que es Ultimate Portal?';
$txt['up_faq_section'] = '<strong>Sección a la que Pertenece</strong>
					<br/>De esta manera podras establecer a la Seccion que le pertenece esta Pregunta &amp; Respuesta';
$txt['up_faq_answer'] = '<strong>Respuesta</strong>
					<br/>De esta manera establaeceras la respuesta a la Pregunta.';					
					
//Add New Section
$txt['up_faq_section_description'] = 'Formulario para Agregar / Editar nuevas Secciones al Modulo.';
$txt['up_faq_section_title'] = 'Titulo de la Sección.';

//Errors
$txt['ultport_error_no_active'] = 'Error, el Modulo no esta activado';
$txt['ultport_error_no_faq_main'] = 'Aun no ha sido agregado ninguna Pregunta con su Respuesta.';
$txt['ultport_error_no_perm'] = 'Error, no tiene los privilegios necesarios para realizar esta accion.';
$txt['ultport_error_no_add_title'] = 'Error, no ha establecido el Titulo.';
$txt['ultport_error_no_add_section'] = 'Error, aun no estan creadas las Secciones.';
$txt['ultport_error_no_edit'] = 'Error, fallo al recibir el ID.';
$txt['ultport_error_no_delete'] = 'Error, no se ha podido ser eliminado, fallo al recibir el ID.';
$txt['ultport_error_no_empty'] = 'Error, no puede dejar ningun campo vacio.';
$txt['ultport_error_no_delete_section'] = 'Error, no se ha podido borrar la seccion, no se designo la ID de la seccion a ser eliminado.';

//Delete confirmation
$txt['ultport_delete_section_confirmation'] = 'Esta seguro que quiere eliminar, eliminando la Seccion, elimina tambien las Preguntas y Respuestas que la misma tiene.';
$txt['ultport_delete_confirmation'] = 'Esta seguro que quiere eliminar?';

?>