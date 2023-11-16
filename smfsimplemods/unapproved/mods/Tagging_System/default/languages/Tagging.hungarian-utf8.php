<?php
global $modSettings;
$txt['tags_menu_btn'] = 'Címkék';
$txt['tags_save_btn'] = 'Mentés';
//Admin Area
$txt['tags_admin_title'] = 'Címkéző rendszer';
$txt['tags_admin_title_main'] = 'Általános beállítások';
$txt['tags_admin_list_cloud_title'] = 'Lista és címke felhő';
$txt['tags_admin_desc_main'] = 'Itt tudod beállítani az általános beállításait a címkéző rendszernek';
$txt['tags_admin_main_enabled'] = 'Módosítás engedélyezése';
$txt['tags_admin_main_required'] = 'Címkék szükségesek';
$txt['tags_admin_main_board_tags'] = 'Címkék kikapcsolása a következő témáknál';
$txt['tags_admin_main_board_tags_desc'] = 'A táblák id számait vesszővel válaszd el';
$txt['tags_admin_main_max_tags'] = 'Témánkénti minimum címkék száma';
$txt['tags_admin_main_min_length_tag'] = 'Minimum címke hossz';
$txt['tags_admin_main_max_length_tag'] = 'Maximum címke hossz';
$txt['tags_admin_main_max_suggested'] = 'Javasolt maximum címke';
$txt['tags_admin_main_enabled_related_topics'] = 'Engedélyezed a hasonló témák címkéket';
$txt['tags_admin_main_max_related_topics'] = 'Maximum hasonló téma mutatása';
$txt['tags_admin_cloud_enabled'] = 'Címkefelhő mutatása';
$txt['tags_admin_cloud_limit'] = 'Címkék limitálása melyeket a felhőben mutat';
$txt['tags_admin_cloud_smallest_color'] = 'Legkisebb címke színe';
$txt['tags_admin_cloud_smallest_opacity'] = 'Átlátszósága a legkisebb címkének';
$txt['tags_admin_cloud_smallest_fontsize'] = 'Betű mérete a legkisebb címkének';
$txt['tags_admin_cloud_small_color'] = 'A legkisebb címke színe';
$txt['tags_admin_cloud_small_opacity'] = 'Átlátszósága a kicsi címkének';
$txt['tags_admin_cloud_small_fontsize'] = 'Betű mérete a kicsi címkének';
$txt['tags_admin_cloud_medium_color'] = 'Közepes címke színe';
$txt['tags_admin_cloud_medium_opacity'] = 'Átlátszósága a közepes címkének';
$txt['tags_admin_cloud_medium_fontsize'] = 'Betű mérete a közepes címkének';
$txt['tags_admin_cloud_large_color'] = 'Nagy címke színe';
$txt['tags_admin_cloud_large_opacity'] = 'Átlátszósága a nagy címkének';
$txt['tags_admin_cloud_large_fontsize'] = 'Betű mérete a nagy címkének';
$txt['tags_admin_cloud_largest_color'] = 'Legnagyobb címke színe';
$txt['tags_admin_cloud_largest_opacity'] = 'Átlátszósága a legnagyobb címkének';
$txt['tags_admin_cloud_largest_fontsize'] = 'Betű mérete a legnagyobb címkének';
$txt['tags_admin_list_enabled'] = 'Címkék listájának mutatása';
$txt['tags_admin_list_show_count'] = 'Mutasd minden egyes tag összegét';
$txt['tags_admin_search_paginate_limit'] = 'Témák száma oldalanként a keresésnél';
//List&Cloud
$txt['tags_list_title'] = 'Minden címke';
$txt['tags_list_title_total'] = 'Összes: ';
$txt['tags_cloud_title'] = 'Címke felhő';
$txt['tags_search_title'] = 'Keresés';
$txt['tags_delete_tag'] = 'címke törlése';
$txt['tags_delete_tag_confirmation'] = 'Biztos vagy benne, hogy törölni akarod ezt a címkét?';
$txt['tags_no_tags'] = 'Nincsenek címkék a megjelenítéshez';
//errors:
$txt['error_tags_exceeded'] = 'A címkék száma meghaladta a maximumot, a limit: '.$modSettings['tag_max_per_topic'].'';
$txt['error_tags_required'] = 'Címkék szükségesek';
$txt['error_tags_max_length'] = 'Egyike az új címkéknek meghaladta a megengedett a megengedett maximális hosszt: '.$modSettings['tag_max_length'].'';
$txt['error_tags_min_length'] = 'Egyike az új címkéknek kevesebb '.$modSettings['tag_max_length'].' betűt tartalmaz';
//Display:
$txt['tags_topic'] = 'Ennek a témának a címkéi:';
$txt['tags_related_title'] = 'Hasonló témák címkék alapján';