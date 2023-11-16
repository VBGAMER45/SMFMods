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
$txt['ultport_button_select_all'] = 'Seleccionar Todos';

//Default Text
$txt['ultport_no_rows'] = 'Aun no se ha agregado ninguna fila.';
$txt['ultport_no_rows_title'] = 'Información.';

//Titles Modules
$txt['ultport_admin_module_title'] = 'Ultimate Portal Modulos';
$txt['ultport_admin_module_title2'] = 'Modulo';
$txt['ultport_admin_user_posts_title'] = 'Posts Destacados';
$txt['ultport_admin_news_title'] = 'Noticias';
$txt['ultport_admin_board_news_title'] = 'Noticias del Foro';
$txt['ultport_admin_announcements_title'] = 'Anuncio Global';

//Modules Description
$txt['ultport_admin_user_posts_descrip'] = 'Area encargada de realizar la Configuracion General del Modulo Posts Destacados';
$txt['ultport_admin_board_news_descrip'] = 'Area encargada de realizar la Configuracion General del Modulo Noticias del Foro';
$txt['ultport_admin_news_descrip'] = 'Area encargada de realizar la Configuracion General del Modulo Noticias';
$txt['ultport_admin_news_section_descrip'] = 'Zona en la que podras Administrar las Secciones del Modulo Noticias, podras: Agregar, borrar, editar, etc...';
$txt['ultport_admin_news_descrip2'] = 'Zona en la que podras Administrar las Noticias agregadas al Modulo Noticias, podras: Agregar, borrar, editar, etc...';
$txt['ultport_admin_announcements_descrip'] = 'Zona en la que podras Administrar el Anuncio Global que aparecera en el Portal';

//Section User posts
$txt['ultport_admin_user_posts_main'] = 'Configuracion General';
$txt['ultport_admin_up_enable'] = '<span style="color:red"><strong>Activar Modulo Aportes Destacados?</strong></span>';
$txt['ultport_admin_up_limit'] = '<strong>Cantidad de Aportes por Pagina?</strong>
							<br />
							Aqui se establece el Número de Aportes por paginas, para mostrar dentro del bloque (página de inicio).
							Valor por defecto 10.';					
$txt['ultport_admin_up_fields'] = '<strong>Selecciona los Campos que tendra el Formulario del Modulo</strong>
							<br />
							Aqui estableceras que campos queres que sean cargados, al momento de A&ntilde;adir un Nuevo Aporte, las mismas seran obligatorias introducirlas.
							Tambien seran los campos que seran visualizados en el Modulo.
							<br />
							Los campos TITULO y ENLACE AL TEMA son funciones por defecto, no pueden estar desactivados.';					
$txt['ultport_admin_up_cover_save_host'] = '<strong>Caratula del Aporte sera guardado en el host?</strong>
							<br />
							De esta forma tendras la posibilidad de todas las Caratulas de los Aportes sean guardados en tu propio hosting.
							<br />
							Si no desea que se guarde en el hosting, no marque esta opcion.';							
$txt['ultport_admin_up_internal_page_presentation'] = '<strong>Presentar el Aporte en una Pagina Interna?</strong>
							<br />
							Aqui estableceras si el Aporte sera visualizado en una pagina interna (al dar click al boton 
							<strong>Visualizar</strong>), seleccionado esta opcion, el aporte se visualizara en 
							una pagina interna especial para cada Aporte, si no elige esta opcion al darle click al boton <strong>Visualizar</strong>, 
							se dirigira directamente al link o enlace puesto en el Aporte';					
$txt['ultport_admin_up_presentation'] = '<strong>Forma de Presentar la Caratula del Aporte?</strong>
							<br />Tienes 2 (dos) opciones: Normal, Avanzado.
							<ul>
								<li><strong>Normal:</strong> Mostrara la imagen original del link, no sufrira cambios.</li>
								<li><strong>Avanzado:</strong> Mostrara una imagen modificada, de esta forma la presentacion de la caratula del Aporte 
									sera mejorada. Ver el ejemplo para saber como sera el resultado final de la Caratula a mostrar.
									Esta opcion solo se activa si es que esta activado la opcion de agregar <strong>Descripcion al Tema</strong>.
									<br />Eligiendo esta opcion podra escribir su propia Marca de Agua (watermark) para sus Caratulas. 
								</li>
							</ul>';
$txt['ultport_admin_up_normal'] = 'Normal';
$txt['ultport_admin_up_advanced'] = 'Avanzado';
$txt['ultport_admin_up_cover_watermark'] = '<strong>Escribir la Marca de Agua a visualizar</strong>
							<br />De esta forma podra establecer si la imagen tendra un texto como Marca de Agua (watermark), que identifique la imagen como propio del sitio.
							<br />Solo si la imagen es guardada en el hosting, la Marca de Agua elegida, estara escrito por la Caratula, si la imagen no se guarda en el hosting,
							solamente simulara un efecto de Marca de Agua, sin que esta este escrito por la Caratula. 
							<br />Dejar el campo vacio, si no desea tener la Caratula con Marca de Agua.
							<br /><strong>Ejemplo:</strong> <strong><a href="http://www.ultimate-portal.net/image/user-posts/watermark-example.jpg" target="_blank">Imagen de Ejemplo</a></strong>.';
$txt['ultport_admin_up_header_show'] = 'Visualizar los Aportes en el Header';							
$txt['ultport_admin_up_social_bookmarks'] = '<strong>Permitir Compartir la Entrada (Social Bookmarks)</strong>
							<br />La misma sera visualizada tanto en el bloque del modulo, como dentro de la pagina interna del Modulo.';							

//User Posts Fields 							
$txt['ultport_admin_up_field_title'] = 'Titulo';
$txt['ultport_admin_up_field_cover'] = 'Caratula del Tema';
$txt['ultport_admin_up_field_description'] = 'Descripción del Tema';
$txt['ultport_admin_up_field_link_topic'] = 'Enlace al Tema';
$txt['ultport_admin_up_field_topic_author'] = 'Autor del Tema';
$txt['ultport_admin_up_field_member_use_module'] = 'Visualizar quien Agrego el Aporte en el Modulo?';
$txt['ultport_admin_up_field_member_updated_module'] = 'Visualizar quien Actualizo el Aporte en el Modulo?';
$txt['ultport_admin_up_field_type_posts'] = 'Agregar un Seleccionador de <strong><em>Tipo de Aportes</em></strong> al Modulo?';
$txt['ultport_admin_up_field_add_language'] = 'Agregar un Seleccionador de <strong><em>Idiomas</em></strong> al Modulo?';
$txt['ultport_admin_up_extra_field_title'] = 'Administrador de Campos Extras';
$txt['ultport_admin_up_extra_field_description'] = 'Area en la que podras Agregar, modificar, borrar, los campos de <strong>Tipo de Aporte</strong> y el <strong>Seleccionador de Idioma</strong> del Modulo <strong>Aportes destacados</strong>';
$txt['ultport_admin_extra_field_id'] = 'Id';
$txt['ultport_admin_extra_field_icon'] = 'Icono';
$txt['ultport_admin_extra_field_title'] = 'Descripción';
$txt['ultport_admin_add_extra_field_icon'] = '<strong>Icono</strong>
										<br />De esta manera podra establecer el icono o imagen que le correspondera a la opcion elegida por el usuario. Este Campo es obligatorio.
										Tama&ntilde;o de los iconos 32 x 32';
$txt['ultport_admin_add_extra_field_title'] = '<strong>Descripción</strong>
										<br />La descripcion que tendra el campo de acuerdo al tipo de campo que se este agregando.';
$txt['ultport_admin_extra_field_selectfield'] = '<strong>Seleccionar aque Campo le corresponde</strong>
										<br />Si es al Seleccionador de Tipo de Aporte o al Seleccionador de Idioma';
$txt['ultport_admin_extra_field_type'] = 'Seleccionador de Tipo de Aporte';
$txt['ultport_admin_extra_field_lang'] = 'Seleccionador de Idioma';
$txt['ultport_admin_extra_field_action'] = 'Acciones';
$txt['ultport_no_activate_extra_field'] = 'Aun no ha activado la opcion de agregar <strong>Tipo de Aporte</strong> o <strong>Idioma</strong> desde la Configuracion general del mod.';

//Perms Title Redirect
$txt['user_posts_perms'] = 'Administración de Permisos';

//Section News
$txt['ultport_admin_news_main'] = 'Configuracion General';
$txt['ultport_admin_news_main_title'] = 'Preferencias';
$txt['ultport_admin_news_section_title'] = 'Administrador de Secciones';
$txt['ultport_admin_admin_news_title'] = 'Administrador de Noticias';
$txt['ultport_admin_news_enable'] = '<span style="color:red"><strong>Activar Modulo Noticias?</strong></span>';
$txt['ultport_admin_news_limit'] = '<strong>Limite de Visualizacion por pagina</strong>
							<br />
							Aqui se establece el Número de Noticias por paginas, para mostrar dentro del bloque (página de inicio, por defecto 10 Noticias por Pagina)';					
$txt['ultport_global_annoucements'] = '<strong>Anuncio Global</strong> 
							<br/>Aparecera en el Portal (Header), en el bloque principal, si no quiere activarlo, solo dejarlo vacio.';
$txt['ultport_admin_news_sect_id'] = 'Id';
$txt['ultport_admin_news_sect_icon'] = 'Icono';
$txt['ultport_admin_news_sect_title'] = 'Titulo';
$txt['ultport_admin_news_sect_position'] = 'Posición';
$txt['ultport_admin_news_sect_action'] = 'Acciones';
$txt['ultport_admin_add_sect_title'] = 'Agregando Nueva Sección';
$txt['ultport_admin_edit_sect_title'] = 'Editando Sección';
$txt['ultport_admin_news_add_sect_icon'] = '<strong>Icono</strong>
										<br />Puede establecer el icono que tendra la seccion de noticia. <img style="float:right" alt="'.$txt['ultport_admin_add_sect_title'].'" border="0" src="'.$settings['default_theme_url'].'/images/ultimate-portal/news-icon.png" width="50" height="50" />
										<br />Dejar vacio en caso de querer usar el icono por defecto, la imagen tiene redimension automatica <br /> (35 x 35)';
$txt['ultport_admin_news_add_sect_title'] = '<strong>Titulo</strong>
										<br />De esta forma agregara el titulo que tendra la Seccion de Noticia.';
$txt['ultport_admin_news_add_sect_position'] = '<strong>Posición</strong>
										<br />Establece la posicion que tendra dentro del modulo la seccion a ser creada. Por defecto trae la ultima posicion.';
$txt['ultport_admin_add_news_title'] = 'Titulo de la Noticia';
$txt['ultport_admin_add_news_sect_title'] = 'Titulo de la Seccion';
$txt['ultport_admin_add_news_title2'] = 'Agregando Noticia';
$txt['ultport_admin_add_news_section'] = '<strong>Selecciona la Seccion</strong>
										<br />De esta forma, establecera la seccion a la que pertenecera la Noticia.';
$txt['ultport_admin_edit_news_title'] = 'Editando Noticia';										

//Section Board News
$txt['ultport_admin_board_news_main'] = 'Configuracion General';
$txt['ultport_admin_bn_main_title'] = 'Preferencias';
$txt['ultport_admin_bn_limit'] = '<strong>Limite de Visualizacion</strong>
							<br />
							Aqui se establece el Número de Noticias para mostrar dentro del bloque 
							<br />(página de inicio)';
$txt['ultport_admin_bn_lenght'] = '<strong>Maximo numero de Caracteres</strong>
							<br />
							Aqui se establece el Maximo Número de Caracteres que tendra cada Noticia visualizada. Para no establecer limite dejar vacio.';
$txt['ultport_admin_bn_view'] = '<strong>Seleccionar Foros</strong>
							<br />
							Seleccione los foros que quiere que aparezca en el bloque.
							<br />
							Para seleccionar varios foros pulse CTRL + Click en los Foros';
$txt['ultport_admin_bn_select_all'] = 'Todos los foros';

//Section Download Module - Titles | Tabs
$txt['up_download_title'] = 'Descargas';
$txt['up_down_settings_tab'] = 'Configuración General';
$txt['up_down_section_tab'] = 'Administrar Secciones';

//Section Download Module - Description
$txt['up_down_settings_descrip'] = 'Area de Configuración General del Modulo Descargas';
$txt['up_down_section_description'] = 'Area para Agregar, Borrar, Editar, las secciones que tendra el Modulo Descargas';

//Section Download Module - Gral Settings
$txt['up_download_enable'] = 'Activar el Modulo Descarga?';
$txt['up_down_file_limit_page'] = 'Limite de Archivos a visualizar por pagina (por defecto 10)';
$txt['up_down_file_max_size'] = 'Máximo Tama&ntilde;o del Archivo para subir en Kilobytes (0 = Ningún Límite). 
								<br />Valor por defecto = 2048 (2 Mb)';
$txt['up_down_extension_file'] = '<strong>Extension Permitida</strong>
								<br/>Establece las extensiones permitidas para subir los archivos, debes ponerlos entre comas.
								<br/><strong>Valores por Defecto:</strong> zip, tar.gz';								
$txt['up_down_enable_approved_file'] = '<strong>Selecciona si los Ficheros Subidos al modulo seran Aprobados o no, para su visualizacion.</strong>
								<br /><span style="color:red">Solo puede ser aprobado por los Administradores</span>';
$txt['up_down_no_approved_file'] = 'No requiere de Aprobación del Administrador';
$txt['up_down_yes_approved_file'] = 'Si requiere de Aprobación del Administrador';
$txt['up_down_board_post_file_disabled'] = 'Desactivar';
$txt['up_down_enable_send_pm_approved'] = 'Enviar un MP, al usuario que fue aprobado su Archivo Subido?';
$txt['up_down_pm_id_member'] = '<strong>ID del Usuario que aparecera como Emisor del MP</strong>
							<br />Si no establece el ID del usuario, el que envie el MP sera el que lo aprobo.';
$txt['up_down_pm_subject'] = '<strong>Asunto o Tema del MP</strong>
							<br />Puede usar la variable {FILENAME}, de esta forma podra establecer tambien en el Asunto, el nombre del Archivo Aprobado.
							<br /><br /><strong>Ejemplo:</strong> El Archivo {FILENAME} ha sido aprobado.';							
$txt['up_down_pm_body'] = '<strong>Cuerpo del Mensaje</strong>
							<br />Acepta BBCode, NO HTML
							<br/>Puede usar la variable <strong>{FILENAME}</strong> para que en el Cuerpo del mensaje aparezca el 
							link del Archivo que fue Aprobado.
							<br/><strong>Ejemplo:</strong> 
							<br/>Felicidades tu archivo [b]{FILENAME}[/b] ha sido aprobado por el Staff de <strong><em>'. $mbname .'</em></strong>';

//Section Download Module - Sections Settings
$txt['up_down_sect_title'] = 'Titulo';
$txt['up_down_sect_icon'] = 'Icono';
$txt['up_down_sect_perms'] = 'Grupos Permitidos para ver la Sección';
$txt['up_down_sect_board'] = 'Foro de la Seccion.';
$txt['up_down_sect_no_board'] = 'Desactivado';
$txt['up_down_sect_total_files'] = 'Cantidad de Archivos de la Sección';
$txt['up_down_sect_no_rows'] = 'Aun no ha agregado las Secciones.';

//Add section
$txt['up_down_manage_sect_title'] = '<strong>Titulo de la Seccion</strong>';
$txt['up_down_manage_sect_icon'] = '<strong>Icono</strong>
								<br />Sera la imagen que representara a la Seccion a mostrar. 
								<br />Redimensionado automatico a 30x30.
								<br />Dejar vacio para usar la imagen por defecto';
$txt['up_down_manage_sect_perms'] = '<strong>Grupos Permitidos para ver la Sección</strong>
								<br />
								De esta forma podra privatizar las secciones, para que solo lo visualice un determinado grupo.';
$txt['up_down_board_post_file'] = '<strong>Seleccionar el Foro en donde se creara un post con el Fichero Subido</strong>
								<br />De esta forma podra publicarse (una ves que fue aprobado) automaticamente el Archivo Subido al modulo, en el Foro seleccionado.
								<br />Si no Quiere que se cree ningun topic de esa seccion solo Desactivelo, y para esta seccion no sera creado ningun Posts.';								
$txt['up_down_manage_sect_description'] = '<strong>Descripción de la Sección</strong>
								<br />Una Breve descripción para la seccion creada. Solo acepta BBCode, NO HTML';					

//Internal Page Module
$txt['ipage_title'] = 'Paginas Internas';
$txt['ipage_settings_title'] = 'Configuraciones';
$txt['ipage_settings_description'] = 'Configuración General del Modulo Paginas Internas.';
$txt['ipage_enable'] = '<span style="color:#F00;"><strong>Activar Modulo Paginas Internas?</strong></span>';
$txt['ipage_limit'] = 'Cantidad de Paginas Internas a Visualizar por Pagina.';					
$txt['ipage_active_columns'] = '<strong>Activar Columnas Izquierda | Derecha</strong>
					<br/>De esta forma podras establecer si queres que se visualicen las columnas (Derecha | Izquierda)
					al ingresar a la Pagina Principal del Modulo Paginas Internas';
$txt['ipage_social_bookmarks'] = 'Permitir Compartir la Entrada (Social Bookmarks).';

//Section Affiliates
$txt['ultport_admin_affiliates_title'] = 'Afiliados';
$txt['ultport_admin_affiliates_main'] = 'Configuracion General';
$txt['ultport_admin_affiliates_descrip'] = 'Area encargada de realizar la Configuracion General del Modulo Afiliados del Foro';
$txt['ultport_admin_aff_main_title'] = 'Configuracion';
$txt['ultport_admin_aff_description'] = 'Area en la que podras Agregar, modificar y borrar los afiliados de tu sitio';
$txt['ultport_admin_aff_limit'] = '<strong>Cantidad de Banner A Mostrar</strong>';
$txt['ultport_admin_aff_limit_error'] = '<strong>Usted supero la cantidad de afiliados, Cambie el limite o elimine algun afiliado si desea agregar mas</strong>';
$txt['ultport_admin_aff_admin_title'] = 'Administrador de Afiliados';
$txt['ultport_admin_aff_direction'] = '<strong>Direccion de movimiento hacia:</strong>';
$txt['ultport_admin_aff_direction_up'] = 'Arriba';
$txt['ultport_admin_aff_direction_down'] = 'Abajo';
$txt['ultport_admin_aff_direction_noMove'] = 'Sin Movimiento';
$txt['ultport_admin_aff_target'] = '<strong>Destino:</strong>';
$txt['ultport_admin_aff_target_self'] = 'Misma Ventana';
$txt['ultport_admin_aff_target_blank'] = 'Nueva Ventana';
$txt['ultport_admin_aff_scrollDelay'] = '<strong>ScrollDelay</strong>
							<br />
							Define la velocidad del movimiento de las imagens. A menor numeración, mayor velocidad
							<br />';
$txt['ultport_admin_aff_add'] = 'Agregar Minibanner';
$txt['ultport_error_no_add_affiliates_web'] = 'Error, no ha establecido el nombre de la web.';
$txt['ultport_admin_add_title'] = 'Agregar Afiliados';
$txt['ultport_admin_add_aff_title'] = 'Nombre de la web';
$txt['ultport_admin_add_aff_url'] = '<strong>Url de la Web</strong>';
$txt['ultport_admin_add_aff_minibanner'] = 'Minibanner';
$txt['ultport_admin_add_aff_urlbanner'] = '<strong>Direccion de la imagen del minibanner</strong>';
$txt['ultport_admin_add_aff_cant'] = 'Numero';
$txt['ultport_admin_add_aff_id'] = 'ID';
$txt['ultport_admin_add_aff_actions'] = 'Acciones';
$txt['ultport_admin_add_aff_alt'] = '<strong>Texto Alternativo</strong>
							<br />
							Breve descripcion de la pagina
							<br />';

//Section About Us Module - Titles | Tabs
$txt['up_about_title'] = 'Quienes Somos';
$txt['up_about_settings_tab'] = 'Configuración General';
$txt['up_about_enable'] = '<span style="color:#FF0000"><strong>Activar Modulo Quienes Somos?</strong></span>';
$txt['up_about_show_nick'] = '<strong>Mostrar el Nick del Usuario</strong>
							<br/>Activado por defecto, sin posibilidad de modificar.';
$txt['up_about_show_group'] = '<strong>Mostrar el Grupo al que pertenece el Usuario</strong>
							<br/>Activado por defecto, sin posibilidad de modificar.';		
$txt['up_about_show_date_registered'] = '<strong>Mostrar Fecha en que se registro el Usuario</strong>
							<br/>Activado por defecto.';									
$txt['up_about_show_mail'] = '<strong>Mostrar Email del Usuario</strong>
							<br/>Activado por defecto.';									
$txt['up_about_show_pm'] = '<strong>Mostrar Icono de PM para poder enviarle un Mensaje Privado</strong>
							<br/>Activado por defecto.';												
$txt['up_about_extrainfo_title'] = '<strong>Titulo de la Informacion Extra a agregar</strong>
							<br/>De esta forma tendras la posibilidad de agregar un titulo a la Informacion adicional que sera visualizado.';																										
$txt['up_about_extra_info'] = '<strong>Agregar Informacion Extra a la Pagina</strong>
							<br/>De esta forma tendras la posibilidad de agregar, alguna Informacion Extra a la pagina del <strong>Modulo Quienes Somos</strong>.';																									
$txt['up_about_group_view'] = '<strong>Selecciona los Grupos a Visualizar</strong>
							<br/>De esta forma tendras la posibilidad de visualizar solo aquellos grupos que deseas que aparezcan en el Modulo</strong>.';																										

//Section About Us Module - Description

$txt['up_aboutus_settings_descrip'] = 'Area de Configuración General del <strong>Modulo Quienes Somos</strong>';

//Section FAQ Module - Titles | Tabs
$txt['up_faq_title'] = 'Preguntas Frecuentes';
$txt['up_faq_config'] = 'Configuración General';
$txt['up_faq_description'] = 'Area de configuracion General del Modulo Preguntas Frecuentes';
$txt['up_faq_enable'] = '<span style="color:#FF0000"><strong>Activar Modulo Preguntas Frecuentes?</strong></span>';
$txt['up_faq_title_page'] = 'Titulo que tendra la Pagina Principal del Modulo
							<br/>No acepta BBCode';
$txt['up_faq_small_description'] = 'Breve Descripción de lo que se quiere presentar en la Página del <strong>Modulo Preguntas Frecuentes</strong>
							<br/>No acepta BBCode';
$txt['up_faq_perms'] = 'Establecer Permisos de Agregar, Editar, Borrar';

//Errors
$txt['ultport_error_no_perm'] = 'Error, no tiene los privilegios necesarios para realizar esta accion.';
$txt['ultport_error_no_add_title'] = 'Error, no ha establecido el Titulo.';
$txt['ultport_error_no_add_icon'] = 'Error, no ha establecido el Icono.';
$txt['ultport_error_no_delete'] = 'Error, no se ha podido ser eliminado, fallo al recibir el ID.';
$txt['ultport_error_no_add_news_section_title'] = 'Error, no ha establecido el Titulo de la Seccion.';
$txt['ultport_error_no_delete_section'] = 'Error, no se ha podido borrar la seccion, no se designo la ID de la seccion a ser eliminado.';
$txt['ultport_error_no_add_news_title'] = 'Error, no ha establecido el Titulo de la Noticia.';
$txt['ultport_error_no_delete_news'] = 'Error, no se ha podido borrar la noticia, no se designo la ID de la Noticia a ser eliminada.';

//Delete confirmation
$txt['ultport_delete_section_confirmation'] = 'Esta seguro que quiere eliminar, eliminando la Seccion, elimina tambien las noticias que la misma tiene.';
$txt['ultport_delete_download_section_confirmation'] = 'Esta seguro que quiere eliminar, eliminando la Seccion, elimina tambien los archivos que la misma tiene.';
$txt['ultport_delete_news_confirmation'] = 'Esta seguro que quiere eliminar la Noticia?';
$txt['ultport_delete_confirmation'] = 'Esta seguro que quiere eliminar?';

?>