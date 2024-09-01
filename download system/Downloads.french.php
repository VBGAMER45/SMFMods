<?php
/*
Download System
Version 1.5
by:vbgamer45
http://www.smfhacks.com
Copyright 2008-2012 SMFHacks.com

############################################
License Information:

Links to http://www.smfhacks.com must remain unless
branding free option is purchased.
#############################################

Downloads English Text Strings
*/

// Downloads.template.php Downloads
global $scripturl;
// Title string
$txt['downloads_text_title'] = 'Téléchargements';

// Principales chaînes de téléchargement
$txt['downloads_text_categoryname'] = 'Nom de la catégorie';
$txt['downloads_text_categorydescription'] = 'Description de la catégorie';
$txt['downloads_text_totalfiles'] = 'Total des fichiers';
$txt['downloads_text_reorder'] = 'Réorganiser';
$txt['downloads_text_options'] = 'Options';
$txt['downloads_text_category'] =  'Catégorie';
$txt['downloads_text_parentcategory'] =	'Catégorie parente';
$txt['downloads_text_catnone'] =	'(Aucun)';

$txt['downloads_text_adminpanel'] = 'Panneau admin des téléchargements';
$txt['downloads_text_addcategory'] = 'Ajouter une catégorie';
$txt['downloads_text_cat_disableratings'] = 'Désactiver les évaluations de téléchargement';
$txt['downloads_text_bbcsupport'] = '(Les codes BBC sont pris en charge)';
$txt['downloads_text_editcategory'] = 'Modifier la catégorie';
$txt['downloads_text_delcategory'] = 'Supprimer la catégorie';
$txt['downloads_text_settings'] = 'Paramètres';
$txt['downloads_text_permissions'] = 'Permissions';
$txt['downloads_rep_deletefile'] = '[Supprimer l\'image]';

$txt['downloads_text_fileswaitapproval'] = 'Fichiers en attente d\'approbation: ';
$txt['downloads_text_filecheckapproval'] = 'Vérifier la liste d\'approbation des fichiers';
$txt['downloads_text_comwaitapproval'] = 'Commentaires en attente d\'approbation: ';
$txt['downloads_text_comcheckapproval'] = 'Vérifier la liste d\'approbation des commentaires';

$txt['downloads_text_filereported'] = 'Fichiers signalés: ';
$txt['downloads_text_filecheckreported'] = 'Vérifier les fichiers signalés';

$txt['downloads_text_comreported'] = 'Commentaires signalés: ';
$txt['downloads_text_comcheckreported'] = 'Vérifier les commentaires signalés';

$txt['downloads_write_error'] = 'Avertissement: le chemin des téléchargements n\'est pas inscriptible! ';
$txt['downloads_text_myfiles'] = '[MesFichiers]';
$txt['downloads_text_search'] = '[Recherche]';
$txt['downloads_text_myfiles2'] = 'MesFichiers';
$txt['downloads_text_search2'] = 'Recherche';
$txt['downloads_text_edit'] = '[Modifier]';
$txt['downloads_text_delete'] = '[Supprimer]';
$txt['downloads_text_unapprove'] = '[Désapprouver]';
$txt['downloads_text_approve'] = '[Approuver]';
$txt['downloads_text_up'] = '[Haut]';
$txt['downloads_text_down'] = '[Bas]';
$txt['downloads_text_reportdownload'] = '[Signaler le téléchargement]';
$txt['downloads_text_delcomment'] = '[Supprimer le commentaire]';
$txt['downloads_text_edcomment'] = '[Modifier le commentaire]';
$txt['downloads_text_repcomment'] = '[Signaler le commentaire]';

$txt['downloads_text_prev'] = 'Téléchargement précédent';
$txt['downloads_text_next'] = 'Téléchargement suivant';
$txt['downloads_text_filesize'] = 'Taille du fichier: ';
$txt['downloads_text_by'] = 'Par:';
$txt['downloads_text_date'] = 'Date: ';
$txt['downloads_text_comments'] = 'Commentaires';
$txt['downloads_text_downloads'] = 'Téléchargements: ';
$txt['downloads_text_views'] = 'Vues: ';
$txt['downloads_text_pages'] = 'Pages: ';
$txt['downloads_text_commentwait'] = 'Votre commentaire n\'apparaîtra pas tant qu\'il n\'aura pas été approuvé.';

$txt['downloads_text_adddownload'] = 'Ajouter un téléchargement';

$txt['downloads_text_returndownload'] = 'Retour aux téléchargements';
$txt['downloads_text_returnfile'] = 'Retour au fichier';
//Chaînes en ligne
$txt['downloads_who_viewdownload'] = ' regardent cette catégorie de téléchargement.';
$txt['downloads_who_viewfile'] = ' regardent ces fichiers.';
$txt['downloads_who_members'] = 'Membres';
$txt['downloads_who_hidden'] = 'Caché';

//Chaînes de formulaire
$txt['downloads_form_title'] = 'Titre:';
$txt['downloads_form_description'] = 'Description:';
$txt['downloads_form_icon'] = 'URL Icone catégorie:';
$txt['downloads_form_uploadicon'] = 'Télécharger l\'icone de la catégorie';
$txt['downloads_write_catpatherror'] = 'Attention : le chemin de l\'image de la catégorie n\'est pas inscriptible ! ';
$txt['downloads_form_filenameicon'] = 'Nom du fichier de l\'icone de la catégorie:';

$txt['downloads_warn_category'] = 'Attention, cela SUPPRIMERA cette catégorie et TOUS les téléchargements, commentaires, évaluations qu\'elle contient...';

$txt['downloads_form_adddownload'] = 'Ajouter un téléchargement';
$txt['downloads_form_category'] =	'Catégorie:';
$txt['downloads_form_keywords'] =	'Mots clés:';
$txt['downloads_form_uploadfile'] = 'Télécharger le fichier:';
$txt['downloads_form_uploadurl'] = 'Ou entrer l\'URL de téléchargement:';

$txt['downloads_form_additionaloptions'] = 'Options supplémentaires:';
$txt['downloads_form_allowcomments'] = 'Autoriser les commentaires sur ce téléchargement.';
$txt['downloads_form_notapproved'] = 'Votre fichier n\'apparaîtra pas dans les téléchargements pour les autres avant d\'être approuvé.';
$txt['downloads_form_editdownload'] = 'Modifier le téléchargement';
$txt['downloads_form_viewratings'] = 'Voir les évaluations';
$txt['downloads_form_ratedownload'] = 'Evaluer le téléchargement';
$txt['downloads_form_norating'] = 'Ce téléchargement n\'a pas encore été évalué.';
$txt['downloads_form_rating'] = 'Evaluations: ';
$txt['downloads_form_ratingby'] = ' par ';
$txt['downloads_form_ratingmembers'] = '  membres.';
$txt['downloads_text_rating'] = 'Evaluations';

$txt['downloads_text_changeowner'] = 'Changer le propriétaire';

$txt['downloads_text_olddownload'] = 'Ancien téléchargement';
$txt['downloads_text_deldownload'] = 'Téléchargement';

// Voir le fichier
$txt['downloads_text_filestats'] = 'Statistiques de téléchargement:';

$txt['downloads_text_postedby'] = 'Posté par:  ';
$txt['downloads_text_addcomment'] = 'Ajouter un commentaire';
$txt['downloads_text_editcomment'] = 'Modifier le commentaire';
$txt['downloads_text_reportcomment'] = 'Signaler le commentaire';
$txt['downloads_text_commodifiedby'] = 'Dernière modification par: ';
$txt['downloads_text_home'] = 'Accueil des téléchargements';

// Supprimer le fichier
$txt['downloads_warn_deletedownload'] = 'Attention, cela supprimera votre téléchargement et vous ne pourrez pas le restaurer.';
$txt['downloads_form_deldownload'] = 'Supprimer le téléchargement';
$txt['downloads_form_deldownload2'] = '[Supprimer le téléchargement]';

$txt['downloads_form_comment'] = 'Commentaire:';

$txt['downloads_form_reportdownload'] = 'Signaler le téléchargement';


$txt['downloads_form_managecats'] = 'Gérer les catégories';
$txt['downloads_form_approvedownloads'] = 'Approuver les téléchargements';
$txt['downloads_form_reportdownloads'] = 'Téléchargements signalés';
$txt['downloads_form_approvecomments'] = 'Approuver les commentaires';
$txt['downloads_form_approveallcomments'] = 'Approuver tous les commentaires';
$txt['downloads_form_reportedcomments'] = 'Commentaires signalés';
// Page des paramètres
$txt['downloads_set_description'] = 'Vous permet de modifier les paramètres importants de votre section de téléchargement.';
$txt['downloads_set_filesize'] = 'Taille maximale de téléchargement: ';
$txt['downloads_set_path'] = 'Chemin des téléchargements: ';
$txt['downloads_set_url'] = 'URL des téléchargements: ';
$txt['downloads_set_whoonline'] = 'Afficher qui visualise un téléchargement';
$txt['downloads_set_commentschoice'] = 'Permettre à l\'utilisateur de basculer si les commentaires peuvent être affichés sur un téléchargement ou non.';
$txt['downloads_set_permissionnotice'] = 'Enfin! N\'oubliez pas de vous assurer que les permissions sont définies pour chaque groupe, afin qu\'ils puissent voir et ajouter aux téléchargements.';
$txt['downloads_set_editpermissions'] = 'Modifier les permissions';
$txt['downloads_set_files_per_page'] = 'Téléchargements par page:';
$txt['downloads_set_commentsappr'] = 'Les commentaires nécessitent-ils une approbation?';
$txt['downloads_set_commentsnewest'] = 'Afficher d\'abord les commentaires les plus récents.';
$txt['downloads_set_showratings'] = 'Afficher les évaluations de téléchargement';
$txt['downloads_set_enable_multifolder'] = 'Activer plusieurs dossiers pour le stockage des téléchargements';
$txt['downloads_index_toprated'] = 'Afficher les téléchargements les mieux notés sur la page principale';
$txt['downloads_index_recent'] = 'Afficher les téléchargements les plus récents sur la page principale';
$txt['downloads_index_mostviewed'] = 'Afficher les téléchargements les plus vus sur la page principale';
$txt['downloads_index_mostdownloaded'] = 'Afficher les téléchargements les plus téléchargés sur la page principale';
$txt['downloads_index_mostcomments'] = 'Afficher les téléchargements les plus commentés sur la page principale';
$txt['downloads_index_showtop'] = 'Afficher les blocs d\'index en haut des téléchargements';

$txt['downloads_set_count_child'] = 'Compte les téléchargements totaux des catégories enfant. <br />(Utilise plus de requêtes)';
$txt['downloads_set_show_quickreply'] = 'Afficher la réponse rapide sur la vue du commentaire';

// Paramètres d\'affichage de la catégorie
$txt['downloads_set_t_downloads'] = 'Afficher le total des téléchargements';
$txt['downloads_set_t_views'] = 'Afficher le total des vues';
$txt['downloads_set_t_filesize'] = 'Afficher la taille du fichier';
$txt['downloads_set_t_date'] = 'Afficher la date';
$txt['downloads_set_t_comment'] = 'Afficher le total des commentaires';
$txt['downloads_set_t_username'] = 'Afficher le nom d\'utilisateur';
$txt['downloads_set_t_rating'] = 'Afficher l\'évaluation';
$txt['downloads_catthumb_settings'] = 'Paramètres d\'affichage de la catégorie';

// Taille de largeur et de hauteur de la catégorie
$txt['downloads_set_cat_height'] = 'Hauteur maximale de l\'image de la catégorie:';
$txt['downloads_set_cat_width'] = 'Largeur maximale de l\'image de la catégorie:';

// Paramètres d\'affichage des téléchargements
$txt['downloads_files_settings'] = 'Paramètres d\'affichage des téléchargements';
$txt['downloads_set_file_prevnext'] = 'Afficher les liens précédent et suivant';
$txt['downloads_set_file_desc'] = 'Afficher la description';
$txt['downloads_set_file_title'] = 'Afficher le titre';
$txt['downloads_set_file_views'] = 'Afficher les vues';
$txt['downloads_set_file_downloads'] = 'Afficher le compte de téléchargement';
$txt['downloads_set_file_lastdownload'] = 'Afficher le dernier téléchargement';
$txt['downloads_set_file_poster'] = 'Afficher posté par';
$txt['downloads_set_file_date'] = 'Afficher la date';
$txt['downloads_set_file_showfilesize'] = 'Afficher la taille du fichier';
$txt['downloads_set_file_showrating'] = 'Afficher l\'évaluation';
$txt['downloads_set_file_keywords'] = 'Afficher les mots clés';

$txt['downloads_save_settings'] = 'Enregistrer les paramètres';



// Shop Settings
$txt['downloads_shop_settings'] = '<b>Paramètres des points de la boutique</b><br /><span class="smalltext">Uniquement si SMF Shop est installé</span>';
$txt['downloads_shop_fileadd']  = 'Points pour un téléchargement ajouté : ';
$txt['downloads_shop_commentadd'] = 'Points pour un commentaire ajouté : ';

// BBC and Image Links code
$txt['downloads_txt_download_linking'] = 'Codes de lien';
$txt['downloads_set_showcode_directlink'] = 'Afficher le code de lien direct';
$txt['downloads_set_showcode_htmllink'] = 'Afficher le code de lien HTML';

$txt['downloads_txt_directlink'] = 'Lien direct';
$txt['downloads_txt_htmllink'] = 'Lien HTML';

// Liste d'approbation
$txt['downloads_app_download'] = 'Télécharger';
$txt['downloads_app_title'] = 'Titre';
$txt['downloads_app_description'] = 'Description';
$txt['downloads_app_date'] = 'Date';
$txt['downloads_app_membername'] = 'Nom du membre';

$txt['downloads_text_withselected'] = 'Avec la sélection';
$txt['downloads_text_performaction'] = 'Effectuer l\'action';

// Liste de rapports
$txt['downloads_rep_filelink'] = 'Lien de téléchargement';
$txt['downloads_rep_reportby'] = 'Signalé par';
$txt['downloads_rep_comment'] = 'Commentaire';
$txt['downloads_rep_viewdownload'] = 'Voir le téléchargement';
$txt['downloads_rep_deletedownload'] = '[Supprimer le téléchargement]';
$txt['downloads_rep_delete'] = '[Supprimer le rapport]';
$txt['downloads_rep_org_comment'] = 'Commentaire original';
// Page de recherche
$txt['downloads_search_download'] = 'Rechercher des téléchargements';
$txt['downloads_search_for'] = 'Rechercher :';
$txt['downloads_search_title'] = 'Rechercher dans le titre du téléchargement';
$txt['downloads_search_description'] = 'Rechercher dans la description du téléchargement';
$txt['downloads_search_keyword'] = 'Rechercher des mots-clés';
$txt['downloads_search'] = 'Recherche';

$txt['downloads_search_days30']  = '30 derniers jours';
$txt['downloads_search_days60']  = '60 derniers jours';
$txt['downloads_search_days90']  = '90 derniers jours';
$txt['downloads_search_days180']  = '180 derniers jours';
$txt['downloads_search_days365']  = '365 derniers jours';
$txt['downloads_search_alltime']  = 'Tous les temps';
$txt['downloads_search_daterange'] = 'Intervalle de dates : ';

$txt['downloads_search_membername'] = 'Posté par (nom du membre) : ';
$txt['downloads_search_advsearch'] = 'Options de recherche avancée';

$txt['downloads_searchresults'] = 'Résultats de recherche';

// MyFiles
$txt['downloads_myfiles'] = 'MesFichiers';
$txt['downloads_myfiles_app'] = 'Téléchargement approuvé';
$txt['downloads_myfiles_notapp'] = 'Téléchargement non approuvé';

// Chaînes de la page d'index
$txt['downloads_main_recent'] = 'Derniers téléchargements';
$txt['downloads_main_viewed'] = 'Les plus vus';
$txt['downloads_main_toprated'] = 'Les mieux notés';
$txt['downloads_main_mostcomments'] = 'Les plus commentés';
$txt['downloads_main_mostdownloads'] = 'Les plus téléchargés';

// Statistiques
$txt['downloads_text_stats']  = 'Statistiques';
$txt['downloads_stats_last']  = 'Les 10 derniers téléchargements ajoutés';
$txt['downloads_stats_viewed'] = 'Les téléchargements les plus vus';
$txt['downloads_stats_toprated'] = 'Les téléchargements les mieux notés';
$txt['downloads_stats_mostcomments'] = 'Les plus commentés';
$txt['downloads_stats_title'] = 'Statistiques des téléchargements';
$txt['downloads_stats_totalviews'] = 'Total des vues';
$txt['downloads_stats_totalcomments'] = 'Total des commentaires';
$txt['downloads_stats_totalfize'] = 'Taille totale des téléchargements';
$txt['downloads_stats_totalfiles'] = 'Total des fichiers';
$txt['downloads_stats_viewstats'] = 'Voir les statistiques des téléchargements';

// Ajouter une sous-catégorie
$txt['downloads_text_addsubcat'] = 'Ajouter une sous-catégorie';
// Tableau
$txt['downloads_text_postingoptions'] = 'Options de publication';
$txt['downloads_text_boardname'] = 'Nom du tableau :';
$txt['downloads_postingoptions_info'] = 'Information : si vous sélectionnez un tableau, un post sera créé lors de l\'approbation d\'un nouveau téléchargement.';
$txt['downloads_posting_showlinktodownload'] = 'Afficher le lien vers la page de téléchargement principale';
$txt['downloads_posting_locktopic'] = 'Verrouiller le sujet';

// Informations sur l'espace et les quotas de fichiers
$txt['downloads_filespace'] = 'Gestionnaire d\'espace de fichier';
$txt['downloads_filespace_note'] = 'Les vignettes ne sont pas comptabilisées dans la limite d\'espace d\'un utilisateur';
$txt['downloads_filespace_filesize'] = 'Espace utilisé';
$txt['downloads_filespace_list'] = 'Liste des fichiers';
$txt['downloads_filespace_list_title'] = 'Liste des fichiers';
$txt['downloads_filespace_recount'] = 'Recompter l\'espace utilisé';
$txt['downloads_filespace_groupquota_title'] = 'Limites d\'espace du groupe de membres';
$txt['downloads_filespace_groupname'] = 'Nom du groupe de membres';
$txt['downloads_filespace_limit'] = 'Limite d\'espace de fichier';
$txt['downloads_filespace_addquota'] = 'Ajouter une limite de quota';

// Chaînes de permission de catégorie
$txt['downloads_text_catpermlist'] = 'Liste des permissions de catégorie';
$txt['downloads_text_catpermlist2'] = 'Permissions de catégorie';
$txt['downloads_text_catperm'] = 'Permission de catégorie';
$txt['downloads_text_addperm'] = 'Ajouter une permission';



//#############################
//Downloads.php Strings
//#############################
$txt['downloads_error_cat_title'] = 'Vous devez entrer un titre de catégorie !';
$txt['downloads_error_no_file_selected'] = 'Aucun téléchargement sélectionné.';
$txt['downloads_error_file_notapproved'] = 'Ce téléchargement n\'a pas encore été approuvé et vous n\'avez pas la permission de le voir.';
$txt['downloads_error_no_title'] = 'Vous devez entrer un titre pour le téléchargement.';
$txt['downloads_error_no_cat'] = 'Vous devez sélectionner une catégorie.';

$txt['downloads_error_file_filesize'] = 'Le téléchargement dépasse la taille de fichier maximale. La taille maximale du fichier est ';
$txt['downloads_error_no_download'] = 'Aucun téléchargement téléchargé trouvé.';
$txt['downloads_error_no_downloadexist'] = 'Aucun téléchargement n\'existe';
$txt['downloads_error_noedit_permission'] = 'Vous n\'avez pas la permission d\'éditer ce téléchargement.';
$txt['downloads_error_nodelete_permission'] = 'Vous n\'avez pas la permission de supprimer ce téléchargement.';
$txt['downloads_error_no_comment'] = 'Vous n\'avez pas entré de commentaire !';
$txt['downloads_error_not_allowcomment'] = 'Attendez une seconde... Ce téléchargement n\'autorise pas les commentaires...';
$txt['downloads_error_no_com_selected'] = 'Aucun commentaire sélectionné.';
$txt['downloads_error_no_user_selected'] = 'Aucun utilisateur sélectionné.';
$txt['downloads_error_no_report_selected'] = 'Aucun rapport sélectionné.';
$txt['downloads_error_no_search'] = 'Vous n\'avez rien entré à rechercher...';
$txt['downloads_error_search_small'] = 'La chaîne de recherche est trop petite, elle doit être supérieure à trois caractères.';
$txt['downloads_error_nocomedit_permission'] = 'Vous n\'avez pas la permission d\'éditer ce commentaire.';
$txt['downloads_error_no_rating_selected'] = 'Aucune note sélectionnée.';
$txt['downloads_error_already_rated'] = 'Vous avez déjà noté ce téléchargement.';
$txt['downloads_error_space_limit'] = 'Impossible de télécharger le fichier car il dépasse votre limite de quota. Information sur le quota : ';
$txt['downloads_error_noquota'] = 'Vous n\'avez pas entré de limite de quota.';
$txt['downloads_error_nogroup'] = 'Aucun groupe de membres sélectionné.';
$txt['downloads_error_quotaexist'] = 'Un quota existe déjà pour ce groupe. Supprimez d\'abord l\'ancien quota.';
$txt['downloads_error_nouser_exists'] = 'Aucun utilisateur existe.';
$txt['downloads_error_nocat_above'] = 'Il n\'y a pas de catégorie au-dessus de l\'actuelle.';
$txt['downloads_error_nocat_below'] = 'Il n\'y a pas de catégorie en dessous de l\'actuelle.';
$txt['downloads_error_norate_own'] = 'Vous ne pouvez pas noter votre propre téléchargement.';
$txt['downloads_error_no_catexists'] = 'Vous devez d\'abord créer une catégorie avant de télécharger un téléchargement.';
$txt['downloads_error_invalid_picture'] = 'Fichier d\'image invalide';

// Limites de quota
$txt['downloads_quotagrouplimit'] ='Limite de quota';
$txt['downloads_quotagspaceused'] ='Espace de quota utilisé';
$txt['downloads_quotaspaceleft'] ='Espace de quota restant';

// Permissions
$txt['downloads_perm_no_view'] = 'Vous n\'êtes pas autorisé à voir cette catégorie.';
$txt['downloads_perm_no_add'] = 'Vous n\'êtes pas autorisé à ajouter un téléchargement dans cette catégorie.';
$txt['downloads_perm_no_edit'] = 'Vous n\'êtes pas autorisé à éditer ce téléchargement dans cette catégorie.';
$txt['downloads_perm_no_delete'] = 'Vous n\'êtes pas autorisé à supprimer ce téléchargement dans cette catégorie.';
$txt['downloads_perm_no_ratefile'] = 'Vous n\'êtes pas autorisé à noter ce téléchargement dans cette catégorie.';
$txt['downloads_perm_no_editcomment'] = 'Vous n\'êtes pas autorisé à éditer ce commentaire dans cette catégorie.';
$txt['downloads_perm_no_addcomment'] = 'Vous n\'êtes pas autorisé à laisser des commentaires dans cette catégorie.';
$txt['downloads_perm_no_report'] = 'Vous n\'êtes pas autorisé à signaler le contenu dans cette catégorie.';
// Notification de commentaire
$txt['downloads_notify_body'] = 'Un nouveau commentaire a été fait sur votre téléchargement posté.' . "\n\n" . 'Vous pouvez le voir à' . "\n" . '%s' . "\n\n";
$txt['downloads_notify_subject'] = 'Nouveau commentaire : %s';
$txt['downloads_notify_title'] = 'M\'informer des commentaires';

$txt['downloads_perm_view'] = 'Voir';
$txt['downloads_perm_add'] = 'Ajouter un téléchargement';
$txt['downloads_perm_edit'] = 'Modifier son propre téléchargement';
$txt['downloads_perm_delete'] = 'Supprimer son propre téléchargement';
$txt['downloads_perm_addcomment'] = 'Ajouter un commentaire';

$txt['downloads_permerr_permexist'] = 'Une permission existe déjà pour ce groupe et cette catégorie. Veuillez d\'abord la supprimer.';

$txt['downloads_perm_allowed'] = 'Autorisé';
$txt['downloads_perm_denied'] = 'Refusé';


$txt['downloads_guest'] = 'Invité';


// Custom Fields
$txt['downloads_custom_fields'] = 'Champs personnalisés';

$txt['downloads_custom_title'] = 'Titre du champ';
$txt['downloads_custom_default_value'] = 'Valeur par défaut';
$txt['downloads_custom_required'] = 'Requis';
$txt['downloads_custom_addfield'] = 'Ajouter un champ';

$txt['downloads_custom_err_title'] = 'Le nom du champ personnalisé est requis.';
$txt['downloads_error_nocustom_above'] = 'Il n\'y a pas de champ personnalisé au-dessus de l\'actuel.';
$txt['downloads_error_nocustom_below'] = 'Il n\'y a pas de champ personnalisé en dessous de l\'actuel.';
$txt['downloads_err_req_custom_field'] = 'Vous avez un champ obligatoire qui manque d\'informations nommé ';

$txt['downloads_err_checkfailed'] = 'La vérification de sécurité a échoué';

$txt['downloads_click_enlarge'] = 'Cliquez pour agrandir';
$txt['downloads_sub_cats'] = 'Sous-catégories: ';

// Sorting Options

$txt['downloads_txt_sortby'] = 'Trier par: ';
$txt['downloads_txt_orderby'] = 'Ordre: ';
$txt['downloads_txt_sort_go'] = 'Go';

$txt['downloads_txt_sort_title'] ='Titre';
$txt['downloads_txt_sort_date'] = 'Date';
$txt['downloads_txt_sort_mostviewed'] = 'Vues';
$txt['downloads_txt_sort_mostcomments'] = 'Commentaires';
$txt['downloads_txt_sort_mostrated'] = 'Évaluation';
$txt['downloads_txt_sort_filesize'] = 'Taille du fichier';
$txt['downloads_txt_sort_mostdowns'] = 'Téléchargements';
$txt['downloads_txt_sort_membername'] = 'Nom du membre';

$txt['downloads_txt_sort_asc'] = 'Ascendant';
$txt['downloads_txt_sort_desc'] = 'Descendant';

$txt['downloads_error_img_size_height'] = 'L\'image dépasse les exigences de taille. Votre hauteur était:  ';
$txt['downloads_error_img_size_width'] = ' Votre largeur était: ';

// Category Display

$txt['downloads_cat_title'] = 'Titre';
$txt['downloads_cat_rating'] = 'Évaluation';
$txt['downloads_cat_downloads'] = 'Téléchargements';
$txt['downloads_cat_views'] = 'Vues';
$txt['downloads_cat_filesize'] = 'Taille du fichier';
$txt['downloads_cat_date'] = 'Date';
$txt['downloads_cat_comments'] = 'Commentaires';
$txt['downloads_cat_membername'] = 'Nom du membre';
$txt['downloads_cat_options'] = 'Options';

$txt['downloads_text_lastdownload'] = 'Dernier téléchargement: ';
$txt['downloads_text_lastdownload2'] =	'Jamais';

// TP Downloads Convertor
$txt['downloads_txt_import_tiny_portal'] = 'Importer les téléchargements de Tiny Portal';
$txt['downloads_txt_import'] = 'Importer';
$txt['downloads_txt_importtp_results'] = 'Résultats de l\'importation des téléchargements de Tiny Portal';
$txt['downloads_txt_import_downloads'] = 'Importer les téléchargements';

$txt['downloads_txt_import_note'] = 'Si vous avez installé un autre système de téléchargement dans le passé, vous pourrez importer ces téléchargements dans le système de téléchargement';

$txt['downloads_txt_categories_imported'] = 'Catégories importées: ';
$txt['downloads_txt_files_imported'] = 'Fichiers importés: ';

$txt['downloads_upload_max_filesize'] = 'PHP: Taille d\'upload maximale';
$txt['downloads_post_max_size'] = 'PHP: Taille maximale du message ';
$txt['downloads_upload_limits_notes'] = 'Les paramètres PHP ci-dessus contrôlent la taille maximale d\'un fichier qui sera accepté. Ces paramètres sont contrôlés par le php.ini ou votre hébergeur web.';

// Mini page text strings
$txt['downloads_txt_gender'] = 'Sexe';
$txt['downloads_txt_posts'] = 'Messages';
$txt['downloads_txt_view_profile'] = 'Voir le profil';
$txt['downloads_txt_www'] = 'WWW';
$txt['downloads_txt_profile_email'] = 'E-mail';


// Begin Download System Text Strings
$txt['downloads_menu'] = 'Téléchargements';
$txt['downloads_admin'] = 'Configuration des Téléchargements';
$txt['downloads_text_settings'] = 'Paramètres';

$txt['downloads_form_approvedownloads'] = 'Approuver les Téléchargements';
$txt['downloads_form_reportdownloads'] = 'Téléchargements signalés';
$txt['downloads_form_approvecomments'] = 'Approuver les Commentaires';
$txt['downloads_filespace'] = 'Gestionnaire d\'espace de fichier';
$txt['downloads_text_catpermlist2'] = 'Autorisations de Catégorie';
$txt['downloads_txt_import'] = 'Importer';

// Permissions
$txt['permissiongroup_downloads'] = 'Système de Téléchargements';
$txt['permissiongroup_simple_downloads'] = 'Système de Téléchargements';

$txt['permissionname_downloads_view'] = 'Voir les Téléchargements';
$txt['permissionhelp_downloads_view'] = 'Permet à l\'utilisateur de voir les Téléchargements';
$txt['cannot_downloads_view'] = 'Vous n\'êtes pas autorisé à voir les Téléchargements';

$txt['permissionname_downloads_add'] = 'Ajouter un Téléchargement';
$txt['permissionhelp_downloads_add'] = 'Permet à l\'utilisateur d\'ajouter un téléchargement.';
$txt['cannot_downloads_add'] = 'Vous n\'êtes pas autorisé à ajouter un téléchargement.';

$txt['permissionname_downloads_edit'] = 'Modifier son propre Téléchargement';
$txt['permissionhelp_downloads_edit'] = 'Permet à l\'utilisateur de modifier leur propre téléchargement.';
$txt['cannot_downloads_edit'] = 'Vous n\'êtes pas autorisé à modifier ce téléchargement';

$txt['permissionname_downloads_delete'] = 'Supprimer son propre Téléchargement';
$txt['permissionhelp_downloads_delete'] = 'Permet à l\'utilisateur de supprimer leur propre téléchargement.';
$txt['cannot_downloads_delete'] = 'Vous n\'êtes pas autorisé à supprimer ce téléchargement.';

$txt['permissionname_downloads_ratefile'] = 'Noter les Téléchargements';
$txt['permissionhelp_downloads_ratefile'] = 'Permet à l\'utilisateur de noter un fichier.';
$txt['cannot_downloads_ratefile'] = 'Vous n\'êtes pas autorisé à noter ce fichier.';

$txt['permissionname_downloads_editcomment'] = 'Modifier son propre Commentaire';
$txt['permissionhelp_downloads_editcomment'] = 'Permet à l\'utilisateur de modifier leurs propres commentaires.';
$txt['cannot_downloads_editcomment'] = 'Vous n\'êtes pas autorisé à modifier ce commentaire.';

$txt['permissionname_downloads_comment'] = 'Laisser des Commentaires';
$txt['permissionhelp_downloads_comment'] = 'Permet à l\'utilisateur de laisser des commentaires sur un téléchargement.';
$txt['cannot_downloads_comment'] = 'Vous n\'êtes pas autorisé à laisser des commentaires.';

$txt['permissionname_downloads_report'] = 'Signaler des Images/Commentaires';
$txt['permissionhelp_downloads_report'] = 'Permet à l\'utilisateur de signaler des téléchargements et des commentaires.';
$txt['cannot_downloads_report'] = 'Vous n\'êtes pas autorisé à signaler du contenu.';

$txt['permissionname_downloads_autocomment'] = 'Approbation Automatique des Commentaires';
$txt['permissionhelp_downloads_autocomment'] = 'Les commentaires n\'ont pas besoin d\'attendre l\'approbation.';

$txt['permissionname_downloads_autoapprove'] = 'Approbation Automatique des Téléchargements';
$txt['permissionhelp_downloads_autoapprove'] = 'Les téléchargements n\'ont pas besoin d\'attendre l\'approbation.';

$txt['permissionname_downloads_manage'] = 'Admin Système de Téléchargement';
$txt['permissionhelp_downloads_manage'] = 'Permet à l\'utilisateur d\'ajouter/supprimer/modifier toutes les catégories. Supprimer les Commentaires, Supprimer les Téléchargements, Approuver les Téléchargements';
$txt['cannot_downloads_manage'] = 'Vous n\'êtes pas autorisé à gérer les téléchargements.';

$txt['whoall_downloads'] = 'Dans les <a href="' . $scripturl . '?action=downloads">Téléchargements</a>';


$txt['down_WaitTime_broken'] = 'Le dernier commentaire de votre IP a eu lieu il y a moins de %1$d secondes. Veuillez réessayer plus tard.';
// FIN des chaînes de texte du système de téléchargement


$txt['downloads_error_invalid_upload_url'] = "L'Url doit commencer par http:// ou https://";
?>