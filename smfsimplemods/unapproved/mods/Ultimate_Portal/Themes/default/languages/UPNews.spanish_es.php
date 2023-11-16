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

//Titles Modules
$txt['up_module_title'] = 'Modulo';
$txt['up_module_news_title'] = 'Noticias';
$txt['up_module_news_edit'] = 'Editando Noticia';
$txt['up_module_news_add'] = 'Agregando Noticia';

//Edit NEWS
$txt['ultport_edit_news_title'] = 'Titulo de la Noticia';
$txt['ultport_edit_news_section'] = '<strong>Selecciona la Seccion</strong>
<br />De esta forma, establecera la seccion a la que pertenecera la Noticia.';

//Main
$txt['up_module_category_name'] = 'Categoria';
$txt['up_module_last_news'] = 'Ultima Noticia';
$txt['up_module_news_date'] = 'Fecha';

//Show New
$txt['up_module_news_added_portal_for'] = 'Agregado al portal por [MEMBER], [DATE]';
$txt['up_module_news_updated_for'] = 'Noticia Actualizada por [UPDATED_MEMBER], [UPDATED_DATE]';

//Errors
$txt['ultport_error_no_active_news'] = 'Error, el modulo Noticias no esta activado';
$txt['ultport_error_no_add_news_title'] = 'Error, no ha establecido el Titulo de la Noticia.';
$txt['ultport_error_no_delete_news'] = 'Error, no se ha podido borrar la noticia, no se designo la ID de la Noticia a ser eliminada.';
$txt['ultport_error_no_groups_delete'] = 'Error, no tiene los permisos necesarios para borrar la Noticia, consulte con el Administrador de <em>'. $mbname .'</em>.';
$txt['ultport_error_no_perms_groups'] = 'Error, no tiene los permisos necesarios para realizar esta acción, consulte con el Administrador de <em>'. $mbname .'</em>.';

//Delete confirmation
$txt['ultport_delete_news_confirmation'] = 'Esta seguro que quiere eliminar la Noticia?';

?>