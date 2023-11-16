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
$txt['ultport_button_add'] = 'Agregar';
$txt['ultport_button_view'] = 'Visualizar';
$txt['ultport_read_more'] = 'Leer M&aacute;s';

//Titles Modules
$txt['up_module_title'] = 'Modulo';
$txt['up_module_ipage_title'] = 'P&aacute;ginas Internas';
$txt['up_ipage_add_html'] = 'Agregar P&aacute;gina en HTML';
$txt['up_ipage_add_bbc'] = 'Agregar P&aacute;gina en BBC';
$txt['up_ipage_date_created'] = 'Fecha de Creaci&oacute;n:';
$txt['up_ipage_member'] = 'Agregado por:';
$txt['up_ipage_date_updated'] = 'Fecha de Actualizaci&oacute;n:';
$txt['up_ipage_member_updated'] = 'Actualizado por:';
//Please NOT REMOVE THE VARIABLE "IPAGE_URL"
$txt['up_ipage_disabled_any_ipage'] = 'Existen algunas Paginas que estan Desactivadas, para verlos y activarlos puede dar click <strong><a href="IPAGE_URL">AQUI</a></strong>';
$txt['up_ipage_disabled_any_ipage_title'] = 'Paginas Internas Desactivas';

//Add Form
$txt['up_ipage_add_title'] = 'Agregar Pagina Interna';
$txt['up_ipage_edit_title'] = 'Editando Pagina Interna';
$txt['up_ipage_title'] = 'Titulo';
$txt['ipage_column_left'] = '<strong>Activar columna Izquierda para esta P&aacute;gina Interna?</strong>
						<br/>De esta forma, la columna Izquierda podra ser visualizada cuando se este dentro de esta P&aacute;gina Interna';
$txt['ipage_column_right'] = '<strong>Activar columna Derecha para esta P&aacute;gina Interna?</strong>
						<br/>De esta forma, la columna Derecha podra ser visualizada cuando se este dentro de esta P&aacute;gina Interna';
$txt['up_ipage_content'] = 'Contenido';						
$txt['up_ipage_perms'] = 'Quienes pueden ver esta P&aacute;gina Interna?';						
$txt['up_ipage_active'] = 'Activar esta Pagina Interna';						
$txt['up_ipage_sticky'] = 'Dejar Fijo esta Pagina Interna';						
$txt['membergroups_guests'] = 'Visitantes';
$txt['membergroups_members'] = 'Miembros Regulares';

//Errors
$txt['ultport_error_no_active'] = 'Error, el Modulo no esta activado';
$txt['ultport_error_no_add_ipage_title'] = 'Error, no ha establecido el Titulo de la Pagina Interna.';
$txt['ultport_error_no_delete_ippage'] = 'Error, no se ha podido borrar, no se designo la ID de la Pagina Interna a ser eliminada.';
$txt['ultport_error_no_perms_groups'] = 'Error, no tiene los permisos necesarios para realizar esta acci&oacute;n, consulte con el Administrador de <em>'. $mbname .'</em>.';
$txt['ultport_error_no_view'] = 'Error, no puede ver esta Pagina Interna. Puede ser que no este activo, o no tiene los permisos para visualizarlo.';
$txt['ultport_error_no_action'] = 'Error, acci&oacute; no permitida.';

//Delete confirmation
$txt['ultport_delete_confirmation'] = 'Esta seguro que quiere Eliminar?';

?>