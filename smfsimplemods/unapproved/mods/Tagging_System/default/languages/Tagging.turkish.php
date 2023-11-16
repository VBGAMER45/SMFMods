<?php
global $modSettings;
$txt['tags_menu_btn'] = 'Etiket';
$txt['tags_save_btn'] = 'Kaydet';
//Admin Area
$txt['tags_admin_title'] = 'Etiket Sistemi';
$txt['tags_admin_title_main'] = 'Genel Ayarlar';
$txt['tags_admin_list_cloud_title'] = 'Etiket Bulutu ve Listesi';
$txt['tags_admin_desc_main'] = 'Buradan Etiket Sistemi ayarlarini yapabilirsiniz.';
$txt['tags_admin_main_enabled'] = 'Modu Aktif Et';
$txt['tags_admin_main_required'] = 'Etiket';
$txt['tags_admin_main_board_tags'] = 'Bu kategoriler i�in etiketi kapat';
$txt['tags_admin_main_board_tags_desc'] = 'Kategorinin ID numarasini yazip virg�lle ayirin.';
$txt['tags_admin_main_max_tags'] = 'Baslik basina izin verilen maksimum etiket sayisi.';
$txt['tags_admin_main_min_length_tag'] = 'Minimum etiket uzunlugu';
$txt['tags_admin_main_max_length_tag'] = 'Maksimum etiket uzunlugu';
$txt['tags_admin_main_max_suggested'] = 'Maksimum etiket �nerisi ';
$txt['tags_admin_main_enabled_related_topics'] = 'Etikete g�re benzer konulari aktif et';
$txt['tags_admin_main_max_related_topics'] = 'G�sterilecek maksimum benzer konu';
$txt['tags_admin_cloud_enabled'] = 'Etiket Bulutunu G�ster';
$txt['tags_admin_cloud_limit'] = 'Bulutta g�sterilecek etiket sayisina limit koy';
$txt['tags_admin_cloud_smallest_color'] = 'En k���k etiket rengi';
$txt['tags_admin_cloud_smallest_opacity'] = 'En k���k etiket i�in seffaflik';
$txt['tags_admin_cloud_smallest_fontsize'] = 'En k���k etiket i�in font boyutu';
$txt['tags_admin_cloud_small_color'] = 'K���k etiket i�in renk';
$txt['tags_admin_cloud_small_opacity'] = 'K���k etiket i�in seffaflik';
$txt['tags_admin_cloud_small_fontsize'] = 'K���k etiket i�in font boyutu';
$txt['tags_admin_cloud_medium_color'] = 'Orta etiket i�in renk';
$txt['tags_admin_cloud_medium_opacity'] = 'Orta etiket i�in seffaflik';
$txt['tags_admin_cloud_medium_fontsize'] = 'Orta etiket i�in font boyutu';
$txt['tags_admin_cloud_large_color'] = 'B�y�k etiket i�in renk';
$txt['tags_admin_cloud_large_opacity'] = 'B�y�k etiket i�in seffaflik';
$txt['tags_admin_cloud_large_fontsize'] = 'B�y�k etiket i�in fon boyutu';
$txt['tags_admin_cloud_largest_color'] = 'En b�y�k etiket i�in renk';
$txt['tags_admin_cloud_largest_opacity'] = 'En b�y�k etiket i�in seffaflik';
$txt['tags_admin_cloud_largest_fontsize'] = 'En b�y�k etiket i�in font boyutu';
$txt['tags_admin_list_enabled'] = 'Etiket listesini g�ster';
$txt['tags_admin_list_show_count'] = 'Her bir etiket i�in miktari g�ster';
$txt['tags_admin_search_paginate_limit'] = 'Etiket aramasinda sayfa basina baslik sayisi';
//List&Cloud
$txt['tags_list_title'] = 'T�m etiketler';
$txt['tags_list_title_total'] = 'Toplam: ';
$txt['tags_cloud_title'] = 'Etiket Bulutu';
$txt['tags_search_title'] = 'Arama';
$txt['tags_delete_tag'] = 'Etiketi Sil';
$txt['tags_delete_tag_confirmation'] = 'Bu etiketi silmek istediginizden emin misiniz?';
$txt['tags_no_tags'] = 'G�sterilecek etiket yok';
//errors:
$txt['error_tags_exceeded'] = 'Maksimum etiket sayisi asildi, yapabileceginiz miktar: '.$modSettings['tag_max_per_topic'].'';
$txt['error_tags_required'] = 'Etiket girmelisiniz';
$txt['error_tags_max_length'] = 'Yeni etiketlerden bir tanesi limiti asti: '.$modSettings['tag_max_length'].'';
$txt['error_tags_min_length'] = 'Girmeniz gereken etiket sayisinin altindasiniz '.$modSettings['tag_max_length'].'Etiket izin verilenden uzun';
//Display:
$txt['tags_topic'] = 'Baslik i�in girilen etiketler:';
$txt['tags_related_title'] = 'Etikete G�re Benzer Basliklar';