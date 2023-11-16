<?php
/*
Traduccion Tags system por Losox.
*/
global $modSettings;
$txt['tags_menu_btn'] = 'Tags';
$txt['tags_save_btn'] = 'Guardar';
//Area del admin
$txt['tags_admin_title'] = 'Sistema de Tags';
$txt['tags_admin_title_main'] = 'Configuracion general';
$txt['tags_admin_list_cloud_title'] = 'Lista y nube de tags';
$txt['tags_admin_desc_main'] = 'Desde aquí se puede establecer la configuración general para el mod sistema de tags';
$txt['tags_admin_main_enabled'] = 'Activar mod';
$txt['tags_admin_main_required'] = 'Tags son requeridos';
$txt['tags_admin_main_board_tags'] = 'Desactivar tags en estos foros';
$txt['tags_admin_main_board_tags_desc'] = 'Ingresa el numero de ID de los foros separados por comas';
$txt['tags_admin_main_max_tags'] = 'Maximo numero de tags por tema';
$txt['tags_admin_main_min_length_tag'] = 'Longitud minima del tag';
$txt['tags_admin_main_max_length_tag'] = 'Longitud maxima del tag';
$txt['tags_admin_main_max_suggested'] = 'Maximo numero de tags sugeridos';
$txt['tags_admin_main_enabled_related_topics'] = 'Activar temas relacionados por tags';
$txt['tags_admin_main_max_related_topics'] = 'Temas maximos a mostrar en la lista de relacionados';
$txt['tags_admin_cloud_enabled'] = 'Mostrar la nube de tags';
$txt['tags_admin_cloud_limit'] = 'Limite de tags a mostrar en la nube';
$txt['tags_admin_cloud_smallest_color'] = 'Color para los tags mas pequeños';
$txt['tags_admin_cloud_smallest_opacity'] = 'Opacidad para los tags mas pequeños';
$txt['tags_admin_cloud_smallest_fontsize'] = 'Tamaño de fuente para los tags mas pequeños';
$txt['tags_admin_cloud_small_color'] = 'Color para los tags pequeños';
$txt['tags_admin_cloud_small_opacity'] = 'Opacidad para los tags pequeños';
$txt['tags_admin_cloud_small_fontsize'] = 'Tamaño de fuente para los tags pequeños';
$txt['tags_admin_cloud_medium_color'] = 'Color para los tags medianos';
$txt['tags_admin_cloud_medium_opacity'] = 'Opacidad para los tags medianos';
$txt['tags_admin_cloud_medium_fontsize'] = 'Tamaño de fuente para los tags medianos';
$txt['tags_admin_cloud_large_color'] = 'Color para los tags largos';
$txt['tags_admin_cloud_large_opacity'] = 'Opacidad para los tags largos';
$txt['tags_admin_cloud_large_fontsize'] = 'Tamaño de fuente para los tags largos';
$txt['tags_admin_cloud_largest_color'] = 'Color para los tags mas largos';
$txt['tags_admin_cloud_largest_opacity'] = 'Opacidad para los tags mas largos';
$txt['tags_admin_cloud_largest_fontsize'] = 'Tamaño de fuente para los tags mas largos';
$txt['tags_admin_list_enabled'] = 'Mostrar la lista de tags';
$txt['tags_admin_list_show_count'] = 'Mostrar la cantidad de cada tag';
$txt['tags_admin_search_paginate_limit'] = 'Cantidad de temas por pagina de busquedas';
//Lista&nubes
$txt['tags_list_title'] = 'Todos los Tags';
$txt['tags_list_title_total'] = 'Total: ';
$txt['tags_cloud_title'] = 'Nube de Tags';
$txt['tags_search_title'] = 'Busqueda';
$txt['tags_delete_tag'] = 'Borrar tag';
$txt['tags_delete_tag_confirmation'] = 'Esta seguro que desea borrar este tag?';
$txt['tags_no_tags'] = 'No hay tags para mostrar';
//Errores:
$txt['error_tags_exceeded'] = 'Numero maximo de tags excedido, el limite es: '.$modSettings['tag_max_per_topic'].'';
$txt['error_tags_required'] = 'Los tags son requeridos';
$txt['error_tags_max_length'] = 'Uno de sus nuevos tags supero la longitud maxima: '.$modSettings['tag_max_length'].'';
$txt['error_tags_min_length'] = 'Uno de sus nuevos tags tiene menos de '.$modSettings['tag_max_length'].' letras';
//Display:
$txt['tags_topic'] = 'Tags para este tema:';
$txt['tags_related_title'] = 'Temas Relacionados';