<?php
/*---------------------------------------------------------------------------------
*	Ultimate Portal
*	Version 0.3
*	Project manager: vicram10
*	Copyright 2011
*	Powered by SMFSimple.com
**********************************************************************************/
global $settings, $txt, $scripturl, $boardurl, $ultimateportalSettings;
global $boarddir, $user_info;

//Buttons
$txt['ultport_button_save'] = 'Guardar';
$txt['ultport_button_edit'] = 'Editar';
$txt['ultport_button_delete'] = 'Eliminar';
$txt['ultport_button_add'] = 'Agregar';
$txt['ultport_button_preview'] = 'Previsualizacion';
$txt['ultport_button_permission'] = 'Permisos';
$txt['ultport_button_select_all'] = 'Seleccionar Todos';
$txt['ultport_button_go_back'] = 'Volver';

//Main CP
$txt['main_description'] = 'Bienvenido(a), <strong>'. $user_info['username'] .'</strong>!. Este es tu "<strong>Centro de Administraci�n del Ultimate Portal</strong>". <br />
Aqu� puedes modificar: 
	<ul>
		<li>Configuraci�n General del Portal</li>
		<li>Administrar bloques </li>
		<li>Asignar Permisos</li> 
		<li>Administrar Modulos</li>
		<li>Administrar los Archivos de Idiomas</li>
		<li>etc..</li>
	</ul>	
Si tienes algun problema, por favor revisa la p�gina de <strong>Manual en Linea</strong>. Si esa informaci�n no te sirve, puedes visitarnos para solicitar ayuda acerca de tu problema.';

$txt['main_blocks_title'] = 'Administraci�n de Bloques';
$txt['main_blocks_description'] = 'Area en la que podras Administrar los bloques del Portal, cambiar posicion, activarlos, asignarles permisos, borrarlos, etc...';
$txt['main_user_posts_title'] = 'Modulo Aportes destacados';
$txt['main_user_posts_description'] = 'Modulo por defecto del Ultimate Portal, podras visualizar de una manera bien presentable los Aportes destacados que hacen tus Usuarios (especial para los foros que Comparten Links de Descarga)';
$txt['main_news_title'] = 'Modulo Noticias';
$txt['main_news_description'] = 'Modulo por defecto del Ultimate Portal, para presentar las Noticias de la Web';
$txt['main_bnews_title'] = 'Modulo Noticias del Foro';
$txt['main_bnews_description'] = 'Modulo por defecto del Ultimate Portal, para presentar los Ultimos Temas del Foro seleccionado';
$txt['main_download_title'] = 'Modulo Descargas';
$txt['main_download_description'] = 'Modulo por defecto del Ultimate Portal, con este modulo podras tener un Gestor completo de Subida/Descarga de Archivos altamente configurable desde el Panel del Modulo.';
$txt['main_ipage_title'] = 'Modulo Paginas Internas';
$txt['main_ipage_description'] = 'Modulo por defecto del Ultimate Portal, con este modulo puedes gestionar las Paginas Internas que tendra el Ultimate Portal.';
$txt['main_affiliates_title'] = 'Modulo Afiliados';
$txt['main_affiliates_description'] = 'Modulo por defecto del Ultimate Portal, con este modulo puedes gestionar los Afiliados / Web Amigas.';
$txt['main_about_title'] = 'Modulo Quienes Somos?';
$txt['main_about_description'] = 'Modulo por defecto del Ultimate Portal, con este modulo podras visualizar el Equipo o Staff de la tu Pagina Web, asi como Informaci�n Extra.';
$txt['main_faq_title'] = 'Modulo Preguntas Frecuentes';
$txt['main_faq_description'] = 'Modulo por defecto del Ultimate Portal, con este modulo podras crear tu propia pagina de Preguntas Frecuentes para tu Web.';
$txt['main_manual_title'] = 'Manual en Linea';
$txt['main_manual_description'] = 'Con este manual, sabras como manejar el Ultimate Portal, detalladamente cada parte del Ultimate Portal es explicado, de esta manera podras
								manejar el Ultimate Portal de la mejor manera posible.';
$txt['main_credits_title'] = 'Creditos';
$txt['main_credits_description'] = '<strong><a href="http://www.smfsimple.com">SMFSimple</a></strong> agradece a todos los que ayudaron a hacer <strong>
Ultimate Portal</strong> lo que es hoy.
	<br /><br /><strong>Fundador de Ultimate Portal y Director del proyecto:</strong> Victor "vicram10" Ramirez
	<br /><br /><strong>Staff:</strong> vicram10, Lean, 4kstore & Distante.
	<br /><br /><strong>Gracias especiales a</strong> Nino_16, royalduke, Suki, Liam, Near, Frony & Maliante!
	<br /><br />
	<strong>Gracias especiales por los iconos utilizados de: </strong> 
	<a href="http://www.famfamfam.com/lab/icons/silk/" target="_blank">FamFamFam</a> | <a href="http://dryicons.com" target="_blank">DryIcons</a> | <a href="http://iconfinder.com" target="_blank">Iconfinder</a>
	<br /><br />
	Y para todos aquellos que olvidamos de saludar y que ayudaron, <strong>Muchas gracias</strong>';

//Confirmation
$txt['ultport_delete_confirmation'] = 'Esta seguro que quiere eliminarlo?';

//Titles
$txt['ultport_admin_category_title'] = 'Ultimate Portal Panel de Control';
$txt['ultport_admin_title'] = 'Ultimate Portal - Centro de Administraci�n';
$txt['ultport_preferences_title'] = 'Preferencias';
$txt['ultport_blocks_title'] = 'Administraci�n de Bloques';

//Preferences
$txt['ultport_admin_preferences_title'] = 'Centro de Administraci�n';
$txt['ultport_admin_preferences_description'] = 'Area en la que se podra configurar por completo el Ultimate Portal';
$txt['ultport_admin_main_title'] = 'Informaci�n';

//Admin Gral Settings - Sections Titles
$txt['ultport_admin_gral_settings_sect_principal'] = 'Principal';
$txt['ultport_admin_gral_settings_sect_view_portal'] = 'Vista del Portal';
$txt['ultport_admin_gral_settings_sect_view_forum'] = 'Vista del Foro';

//Admin Gral Settings
$txt['ultport_admin_gral_settings_title'] = 'Configuraci�n General';
$txt['ultport_admin_gral_settings_description'] = 'Area en la que se encuentra las configuraciones generales del Ultimate Portal';
$txt['ultport_admin_gral_settings_portal_enable'] = '<span style="color:red"><strong>Activar el Ultimate Portal?</strong></span>
												<br />
												De esta forma podras Activar/Desactivar el portal en cualquier momento.';										
$txt['ultport_admin_gral_settings_portal_title'] = '<strong>Nombre del Portal</strong>
												<br />
												De esta forma estableceras el nombre que tendra el portal independientemente del Nombre que tenga el Foro';
$txt['ultport_admin_gral_settings_favicons'] = '<strong>Activar FavIcons?</strong>
												<br />
												De esta forma podras Activar/Desactivar el favicons por defecto. <img alt="favicons" src="'.$settings['default_theme_url'].'/images/ultimate-portal/favicon.png" />';
$txt['ultport_admin_gral_settings_use_curve'] = '<strong>Usa un Theme basado en el theme Curve o es el Theme Curve?</strong>
												<br/>
												De esta forma podra visualizar los bloques correctamente. Esta opcion viene activado por defecto.
												Si no usa el theme Curve o un theme basado en Curve, no debe activar esta opcion, de esta forma se mejora la performance
												de la visualizacion de los bloques.';

//Admin Gral Settings - Section: View Portal
$txt['ultport_admin_gral_settings_height_col_left'] = '<strong>Ancho Columna Izquierda</strong>
												<br />
												De esta forma estableces el ancho que tendra la columna Izquierda del portal. <br />
												Valor en Porcentajes. <strong>Ejemplo:</strong> 20%';
$txt['ultport_admin_gral_settings_height_col_center'] = '<strong>Ancho Columna Central</strong>
												<br />
												De esta forma estableces el ancho que tendra la columna Central del portal. <br />
												Valor en Porcentajes. <strong>Ejemplo:</strong> 60%';
$txt['ultport_admin_gral_settings_height_col_right'] = '<strong>Ancho Columna Derecha</strong>
												<br />
												De esta forma estableces el ancho que tendra la columna Derecha del portal. <br />
												Valor en Porcentajes. <strong>Ejemplo:</strong> 20%';
$txt['ultport_admin_gral_settings_enable_portal_col_left'] = '<strong>Activar columna Izquierda?</strong>
												<br />
												Activado por defecto. Viendo el Portal';
$txt['ultport_admin_gral_settings_enable_portal_col_right'] = '<strong>Activar columna Derecha?</strong>
												<br />
												Activado por defecto. Viendo el Portal';
$txt['ultport_admin_gral_settings_enable_icons'] = '<strong>Activar el Uso de Iconos Graficos?</strong>
												<br />
												Activado por defecto, de esta forma, los bloques, menu principal mostraran los iconos que les corresponde a cada uno de ellos. <br />
												Desactivando esta opcion, no mostrara los iconos, solo el <strong>Titulo</strong>.';
$txt['ultport_admin_gral_settings_icons_extention'] = '<strong>Seleccionar extension de los iconos graficos</strong>
												<br />
												De esta forma podra seleccionar que tipo de extension tendra los iconos de cada bloque. <strong>Ejemplo</strong>: .jpg, .png, .gif, etc.';									
$txt['ultport_admin_gral_settings_enable_version'] = '<strong>Visualizar la version del Portal en el Pie de Pagina?</strong>
												<br />
												Activado por defecto. Opcional para todos los webmaster, de esta forma nadie sabra la version del portal que usas.';

//Admin Gral Settings - Section: View Forum
$txt['ultport_admin_view_forum_enable_col_left'] = '<strong>Activar la columna Izquierda al visualizar el Foro?</strong>
												<br />
												Activado por defecto. De esta manera podras visualizar la columna dentro del foro. <br />
												Desactivando esta opcion, no se cargara el script php (no genera consultas en la BD) de esta columna';
$txt['ultport_admin_view_forum_enable_col_right'] = '<strong>Activar la columna Derecha al visualizar el Foro?</strong>
												<br />
												Activado por defecto. De esta manera podras visualizar la columna dentro del foro. <br />
												Desactivando esta opcion, no se cargara el script php (no genera consultas en la BD) de esta columna.';
//Admin - Language - Maintenance 
$txt['ultport_admin_lang_maintenance_title'] = 'Idioma - Mantenimiento';
$txt['ultport_admin_lang_maintenance_admin'] = 'Administraci�n del Idioma';
$txt['lang_maintenance_duplicate_title'] = 'Duplicaci�n de Idioma';
$txt['ultport_admin_lang_maintenance_edit_info'] = 'Informacion de Archivo de Idioma';
$txt['ultport_admin_lang_maintenance_edit'] = 'Edicion de Archivo de Idioma';
$txt['ultport_admin_lang_maintenance_warning'] = 'Acuerdese de Limpiar la Cache del Foro cuando edite un Archivo de Idioma
												<strong>Administracion---->Mantenimiento----->Rutina------></strong> Limpiar Cache de Archivo. 
												<br/>&nbsp;&nbsp;Es la �ltima Opci�n de Este panel de Control.';

//Admin - Language - Maintenance - Section Admin Language 
$txt['ultport_admin_lang_maintenance_admin_edit_language'] = '<strong>Seleccionar Idioma</strong>
												<br />
												Selecciona el Archivo que desea Editarlo';
$txt['ultport_admin_select_lang_duplicate'] = '<strong>Seleccionar Idioma</strong>
												<br />
												Selecciona el Archivo de Idioma que desea Duplicar';
$txt['ultport_admin_lang_duplicate_new'] = '<strong>Nombre del Nuevo Archivo de Idioma</strong>
												<br />
												Establece el nombre que tendra el archivo de Idioma Nuevo. No hace falta colocar la Extension ".php", 
												La misma es colocado de manera automatica por el Generador
												<br/><strong>Ejemplo:</strong> Modifications.spanish_latin-utf8';

//Admin - Language - Maintenance - Edit Language
$txt['ultport_admin_edit_language_file'] = 'Archivo de Idioma';

//Admin - Perms Settings
$txt['ultport_admin_permissions_settings_title'] = 'Configuraci�n de Permisos';
$txt['ultport_admin_permissions_settings_subtitle'] = 'Establece los permisos para este Grupo';
$txt['ultport_admin_perms_groups'] = 'Selecciona el Grupo a Editar los permisos';

//Perms - Names
$txt['ultport_perms_user_posts_add'] = 'Puede Agregar Aportes destacados de los Usuarios?';
$txt['ultport_perms_user_posts_moderate'] = 'Puede Moderar (Editar / Borrar) el Modulo Aportes destacados?';
$txt['ultport_perms_news_add'] = 'Puede Agregar Noticia?';
$txt['ultport_perms_news_moderate'] = 'Puede Moderar (Editar / Borrar) el Modulo Noticias?';
$txt['ultport_perms_download_add'] = 'Puede Agregar Archivos al Modulo Descarga?';
$txt['ultport_perms_download_moderate'] = 'Puede Moderar (Editar / Borrar / Aprobar / Desaprobar / Agregar Archivos sin necesidad de ser Aprobado) el Modulo Descarga?';
$txt['ultport_perms_ipage_add'] = 'Puede Agregar Paginas al Modulo Paginas Internas?';
$txt['ultport_perms_ipage_moderate'] = 'Puede Moderar (Editar / Borrar) el Modulo Paginas Internas?';
$txt['ultport_perms_faq_add'] = 'Puede Agregar Preguntas/Respuestas al Modulo Preguntas Frecuentes?';
$txt['ultport_perms_faq_moderate'] = 'Puede Moderar (Editar / Borrar) el Modulo Preguntas Frecuentes?';

//Admin - Portal Menu Settings
$txt['ultport_admin_portal_menu_title'] = 'Menu del Portal';
$txt['ultport_admin_mainlinks_icon'] = 'Icono';
$txt['ultport_admin_mainlinks_title'] = 'Titulo';
$txt['ultport_admin_mainlinks_url'] = 'Direcci�n Web';
$txt['ultport_admin_mainlinks_position'] = 'Posici�n';
$txt['ultport_admin_mainlinks_edit'] = 'Editar';
$txt['ultport_admin_mainlinks_delete'] = 'Eliminar';
$txt['ultport_admin_mainlinks_active'] = 'Activar';
$txt['ultport_admin_mainlinks_top_menu'] = 'Agregar al Top Menu?';
$txt['ultport_admin_portal_menu_add_title'] = 'A&ntilde;adir nuevo Link';	

//Admin - Portal Menu Settings - Edit
$txt['ultport_admin_portal_menu_edit_title'] = 'Editar Enlace';

//Admin - Portal Menu Settings - Edit

$txt['ultport_admin_portal_menu_delet_confirm'] = 'Esta seguro que quiere eliminar el Enlace?';



//SEO

$txt['ultport_seo_title'] = 'Gestion SEO';
$txt['ultport_seo_description'] = 'Gestion SEO';
$txt['seo_robots_title'] = 'Configuraci�n Robots.txt';
$txt['seo_config'] = 'Configuraci�n General';
$txt['seo_robots_txt'] = '<strong>Configuraci�n Robots.txt</strong>
						<br/>Area en la que podras establacer la forma de indexar el contenido.';
$txt['seo_robots_added'] = 'Robots.txt';
$txt['seo_title_key_word'] = '<strong>Palabras Claves (en el Titulo del Foro)</strong>
							<br />
							De esta forma podras hacer que sea optimizado para los buscadores, las palabras que identifican a tu foro, logrando buenos resultados.
							La misma aparecera en todo tu foro, las palabras se encontraran en el titulo que aparece en el Navegador. 
							Para mejor optimizacion separarlos por guiones medios "-"
							<br />
							<strong>Ejemplo:</strong> php - smf - simplemachines - portal - mysql';
$txt['seo_google_analytics'] = '<strong>Codigo Google Analytics</strong>
							<br />Si tienes el codigo de Google Analytics, solo agregalo aqui.
							<br /><strong>Ejemplo:</strong> UA-00110011-1';							
$txt['seo_google_verification_code_title'] = 'Google Verification Code';
$txt['seo_google_verification_code'] = '<strong>Introduzca el Nombre del Archivo html para Verificar su sitio en Google</strong>
							<br />Esta opci�n le permitir� crear el archivo.html que google necesita para que usted pueda verificar su sitio
							en las Herramientas para Webmaster de Google.
							<br />No hace falta colocar la extension HTML al nombre del archivo.';
$txt['seo_google_verification_code_error'] = 'Extension no permitida. Solo debe colocar el nombre.';

//Blocks
$txt['ultport_blocks_title'] = 'Administracion de Bloques';
$txt['ultport_blocks_description'] = 'Zona para establecer las distintas posiciones que le corresponderan a los distintos bloques.';
$txt['ultport_blocks_left'] = 'Izquierda';
$txt['ultport_blocks_center'] = 'Centro';
$txt['ultport_blocks_right'] = 'Derecha';
$txt['ultport_blocks_enable'] = 'Activar';

//Blocks - Sect: Position
$txt['ultport_blocks_position_title'] = 'Posicion de Bloques';

//Blocks - Sect: Titles
$txt['ultport_blocks_titles'] = 'Titulos de Bloques';
$txt['ultport_blocks_titles_description'] = 'Area en la que podras establecer los titulos que tendran cada bloque del Ultimate Portal';
$txt['ultport_blocks_titles_id'] = 'Id.';
$txt['ultport_blocks_titles_original_title'] = 'Titulo Actual';
$txt['ultport_blocks_titles_custom_title'] = 'Titulo Personalizado';

//Blocks - Sect: Create Blocks
$txt['ultport_add_bk_title'] = 'Titulo del Bloque';
$txt['ultport_add_bk_icon'] = '<strong>Icono del Bloque</strong>
							<br />
							Se usara el icono por defecto, en caso de no seleccionar nada. Para colocar su icono personalizado, debera de subirlo a la carpeta 
							<br /><strong>Theme/default/images/ultimate-portal/icons</strong> con la extension <strong>'. $ultimateportalSettings['ultimate_portal_icons_extention'] .'</strong>.';
$txt['ultport_add_bk_collapse'] = '<strong>Bloque colapsable?</strong>
							<br />De esta forma podras establecer si el bloque podra o no Colapsarse.';
$txt['ultport_add_bk_style'] = '<strong>Bloque con estilo?</strong>
							<br />De esta forma podras establecer si el bloque tendra o no estilo.';
$txt['ultport_add_bk_no_title'] = '<strong>Bloque sin Titulo?</strong>
							<br />De esta forma podras establecer si el bloque tendra o no Titulo.';							
$txt['ultport_create_blocks_titles'] = 'Crear Bloque';
$txt['ultport_create_blocks_description'] = 'Zona de Creacion de bloques.';
$txt['ultport_creat_bk_html_title'] = 'Crear Bloque HTML';
$txt['ultport_creat_bk_php_title'] = 'Crear Bloque PHP';
$txt['ultport_add_bk_html_titles'] = 'Creaci�n de bloque HTML';
$txt['ultport_add_bk_php_titles'] = 'Creaci�n de bloque PHP';
$txt['ultport_tmp_bk_php_hello'] = 'Hola';
$txt['ultport_tmp_bk_php_content'] = '
/*------------------------------------------------------
*	Ultimate Portal
*	Version 0.4
*	Project Manager: vicram10
*	Copyright 2011-2021
*	Powered by SMFSimple.com
--------------------------------------------------------
Tienes conexi�n del DB, todas las variables globales 
y todas las funciones de Portal y Foro a tu disponibilidad
*/
//NO BORRAR ESTA PARTE, RIESGO DE SEGURIDAD SI SE BORRA
if (!defined(\'SMF\'))
	die(\'Hacking attempt...\');
//FIN DE PARTE IMPORTANTE

global $user_info, $txt, $context;
$username = $user_info[\'username\'];
echo $txt[\'ultport_tmp_bk_php_hello\'] . \'�<strong>\'. $username . \'</strong>\';';
$txt['ultport_admin_bk_title'] = 'Administrando Bloques';
$txt['ultport_admin_bk_description'] = 'Zona en la que podras, editar tus bloques, asignarle permisos, etc.';
$txt['ultport_admin_bk_custom'] = 'Listado de Bloques Personales';
$txt['ultport_admin_bk_system'] = 'Listado de Bloques del Sistema';
$txt['ultport_admin_bk_type'] = 'Tipo';
$txt['ultport_admin_bk_action'] = 'Acciones';
$txt['ultport_admin_edit_bk_html'] = 'Editando Bloque HTML';
$txt['ultport_admin_edit_bk_php'] = 'Editando Bloque PHP';
$txt['ultport_admin_edit_perms'] = 'Editando Permisos del bloque';
$txt['ultport_admin_select_perms'] = '<strong>Selecciona los Grupos</strong>
									<br />
									De esta manera podras establecer cuales son los grupos que podran ver este bloque en particular.';

//Multiblock CP
$txt['ultport_mb_title'] = 'Panel de Control del Multibloque';
$txt['ultport_mb_main'] = 'Principal';
$txt['ultport_mb_main_descrip'] = 'Panel de control multibloque creado, puedes editarlo, a&ntilde;adir o borrar.';
$txt['ultport_mb_add'] = 'A&ntilde;adir Multibloque';
$txt['ultport_mb_next'] = 'Siguiente';
$txt['ultport_mb_title2'] = 'T�tulo del Multibloque';
$txt['ultport_mb_position'] = 'Posici�n del Multibloque | Cabezera | Pie de p�gina';
$txt['ultport_mb_blocks'] = 'Selecciona los bloques que apareceran en el Multibloque';
$txt['ultport_mb_design'] = 'Dise&ntilde;o del Multibloque| 1 Fila 2 Columnas| 2 Filas 1 Columna| 3 Filas 1 Columna';
$txt['ultport_mb_enable'] = 'Activar Multibloque';
$txt['ultport_mbk_title'] = '<strong>Multique sin t�tulo?</strong>
    <br />De esta manera puedes determinar si el Multibloque tendr� o no un t�tulo.';
$txt['ultport_mbk_collapse'] = '<strong>Multibloque colapsable??</strong>
    <br />De esta manera puedes determinar si el Multibloque puede ser colapsable.';
$txt['ultport_mbk_style'] = '<strong>MultiBloque con estilo?</strong>
    <br />De esta manera puedes determinar si el Multibloque tendr� un estilo.';
$txt['ultport_mb_step'] = 'Paso';
$txt['ultport_mb_organization'] = '<strong>Organizaci�n</strong>
    <br />Organiza tu bloque para que aparezca donde quieras.';
$txt['ultport_mb_row'] = 'Fila';
$txt['ultport_mb_column'] = 'Columna';
$txt['ultport_mbk_position'] = 'Posici�n en el MultiBloque';
$txt['ultport_mb_edit'] = 'Editar Multibloque';
$txt['ultport_mb_multiheader'] = 'Posici�n de la cabecera del MultiBloque';
$txt['ultport_mb_footer'] = 'Posici�n del pie de pagina del MultiBloque';
$txt['ultport_mb_delete'] = '&iquest;Est�s seguro que quieres borrar el MultiBloque?';

//Admin Gral Settings - Section: Extra Config
$txt['ultport_exconfig_title'] = 'Configuraciones extras';
$txt['ultport_rso_title'] = '<strong>Reducir sobrecarga del sitio</strong>
<br />
De esta manera puedes reducir la carga (las peticiones a la base de datos es solo de 1 que ves cada 30 minutos) de Ultimate Portal, usando el m�todo de cache por defecto de SMF.
<br />
<strong>Con esta opci�n activada debes vaciar la cache del foro cada vez que cambies, agregues, borres bloques o modulos; para que estos cambios hagan efecto.</strong>.';
$txt['ultport_collapse_left_right'] = '<strong>Activar Colapsar bloques derecho/izquierdo</strong>
<br />
Activado por defecto. Puedes apagar o prender la posibilidad de colapsar los bloques derechos e izquierdos".';

									
									
//Tabs
$txt['ultport_admin_title2'] = 'Prueba';
//Errors
$txt['ultport_error_no_add_bk_title'] = 'Error, No ha agregado un titulo a su bloque.';
$txt['ultport_error_no_add_bk_fopen_error'] = "No se puede abrir el archivo ". $boarddir ."/up-php-blocks/tmp-bk.php. Compruebe que el archivo ". $boarddir ."/up-php-blocks/tmp-bk.php tenga CHMOD (0777)  y averigue si su server soporta la funcion PHP  \"fopen\".";
$txt['ultport_error_fopen_error'] = "No se puede abrir el archivo / escribir. Compruebe que el archivo tenga CHMOD (0777)  y averigue si su server soporta la funcion PHP  \"fopen\".";
$txt['ultport_error_no_add_bk_nofile'] = "No se puede abrir el archivo. Revisa los permisos de escritura en la carpeta: Themes/default/languages en caso que sea al editar un lenguaje o ". $boarddir ."/up-php-blocks si estas editando o agregando un bloque.";
$txt['ultport_error_no_name'] = 'Debe estipular un Titulo, no puede dejar vacio.';
//Permissions to enter the admin panel Ultimate Portal
$txt['ultport_error_enter_admin'] = 'Lo siento pero no tiene permiso para entrar a la Administracion del Portal';

?>