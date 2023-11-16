<?php
/*---------------------------------------------------------------------------------
*	Ultimate Portal
*	Version 0.3
*	Project manager: vicram10
*	Copyright 2011
*	Powered by SMFSimple.com
**********************************************************************************/

global $settings, $txt, $scripturl, $boardurl, $mbname;
global $ultimateportalSettings;

//Buttons
$txt['ultport_button_save'] = 'Guardar';
$txt['ultport_button_edit'] = 'Editar';
$txt['ultport_button_delete'] = 'Eliminar';
$txt['ultport_button_add'] = 'Publicar';
$txt['ultport_button_view'] = 'Visualizar';
$txt['ultport_button_editing'] = 'Editando';

//Titles Modules
$txt['down_module_title'] = 'Modulo';
$txt['down_module_title2'] = 'Descargas';
$txt['down_module_search_page_title'] = 'Resultado de la Busqueda';
$txt['down_module_section_title'] = 'Secciones';
$txt['down_module_new_file_title'] = 'Enviar nuevo Archivo';
$txt['down_module_edit_file_title'] = 'Editando Archivo';
$txt['down_module_upload_file_title'] = 'Archivos Asociados';
$txt['down_module_more_popular_title'] = 'Descargas mas Populares';
$txt['down_module_last_files_title'] = 'últimos Archivos';
$txt['down_module_stats_title'] = 'Estadísticas del Modulo Descargas';
$txt['down_module_unapproved_title'] = 'Archivos no Aprobados';


//New Form
$txt['down_module_warning'] = '<strong>Nota:</strong> Tu envio no aparecerá instantaneamente - debes esperar a que el Administrador apruebe tu archivo antes de que aparezca en el Modulo Descargas.';
$txt['down_module_file_name'] = 'Nombre del Archivo';
$txt['down_module_file_description'] = 'Descripción';
$txt['down_module_image_description'] = 'Capturas';
$txt['down_module_file_small_description'] = 'Breve Descripción
								<br />Se usará en la pagina de resultados de busqueda. 
								<br />100 caracteres como máximo. 
								<br />HTML y BBC no permitidos';
$txt['down_module_file_section'] = 'Sección a la que Pertenece';								
$txt['down_module_file_upload'] = '<strong>Subir Archivo</strong>
								<br/>Puede subir Imagenes';								
$txt['down_module_file_upload_other'] = 'Agregar Otro';							
$txt['down_module_file_upload_max_size'] = 'Tama&ntilde;o Máximo de Archivos permitido [SIZE] KB
								<br/>Image Extension: gif,jpeg,jpg
								<br/>File Extension: '. $ultimateportalSettings['download_extension_file'];

//Section 
$txt['down_module_search'] = 'Buscar';
$txt['down_module_actions'] = 'Operaciones';
$txt['down_module_new_file'] = 'Agregar nuevo Archivo';
$txt['down_attach'] = 'Archivos Adjuntos';
$txt['smf130'] = 'Seleccionar aquellos que desea eliminar';

//Search form
$txt['down_search'] = 'Buscaste';
$txt['down_author'] = 'Subido por';
$txt['down_date_created'] = 'Enviado';
$txt['down_date_updated'] = 'Ultima Actualización';
$txt['down_total_downloads'] = 'Total de Descargas';

//Specific File 
$txt['down_file_title_downloads'] = 'Descargas';
$txt['down_file_title_section'] = 'Sección';
$txt['down_file_uploaded_user'] = 'Ver los archivos subidos por este Usuario';
$txt['down_file_no_attachment'] = 'No hay archivos para descargar.';
$txt['down_file_warning_no_approved'] = 'Atención: Este Archivo no ha sido aprobado.';
$txt['down_file_approved'] = 'Aprobar el Archivo';
$txt['down_file_post_link'] = 'Link al Archivo';
$txt['down_file_topic_link'] = 'Ir al Topic de este Archivo';

//Stats
$txt['down_top_uploader'] = 'Top 5: Usuarios con mas Archivos Subidos';
$txt['down_top_sections'] = 'Top 5: Secciones con mas Actividad';

//Profile
$txt['down_profile_title'] = 'Perfil de ';
$txt['down_profile_total_files'] = 'Total de Archivos subidos por este usuario: ';

//Errors or Confirmation txt
$txt['ultport_error_no_active'] = 'Error, este Modulo no esta Activo';
$txt['ultport_delete_confirmation'] = 'Esta Seguro que desea borrar el Archivo?';
$txt['down_error_no_title'] = 'Error, no ha establecido el Titulo.';
$txt['down_error_no_description'] = 'Error, no ha establecido la Descripción.';
$txt['down_error_no_small_description'] = 'Error, no ha establecido la breve Descripción.';
$txt['ultport_error_no_perms_groups'] = 'Error, no tiene los permisos necesarios para realizar esta acción, consulte con el Administrador de <em>'. $mbname .'</em>.';
$txt['down_error_no_section'] = 'Error, no ha sido creado las Secciones.';
$txt['down_error_no_action'] = 'Error, accion no permitida.';
$txt['down_error_no_found'] = 'Error, petición no encontrada.';
$txt['down_error_max_size'] = 'Tu archivo es demasiado grande. El tama&ntilde;o máximo permitido para archivos adjuntos es %d KB.';
$txt['down_error_canot_upload_file'] = 'No puede ser subido. Las Extensiones permitidas son: ';
$txt['down_error_no_files_section'] = 'No hay archivos en esta Sección';

?>