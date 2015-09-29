<?php
/* ***** BEGIN LICENSE BLOCK *****
 * Version: MPL 1.1
 *
 * The contents of this file are subject to the Mozilla Public License Version
 * 1.1 (the "License"); you may not use this file except in compliance with
 * the License. You may obtain a copy of the License at
 * http://www.mozilla.org/MPL/
 *
 * Software distributed under the License is distributed on an "AS IS" basis,
 * WITHOUT WARRANTY OF ANY KIND, either express or implied. See the License
 * for the specific language governing rights and limitations under the
 * License.
 *
 * The Original Code is http://www.sa-mods.info
 *
 * The Initial Developer of the Original Code is
 * wayne Mankertz.
 * Portions created by the Initial Developer are Copyright (C) 2011
 * the Initial Developer. All Rights Reserved.
 *
 * Contributor(s):
 *
 * ***** END LICENSE BLOCK ***** */

if (file_exists(dirname(__FILE__) . '/SSI.php') && !defined('SMF'))
	require_once(dirname(__FILE__) . '/SSI.php');

elseif (!defined('SMF'))
	die('<b>Error:</b> Cannot install - please verify you put this in the same place as SMF\'s index.php.');

global $db_prefix, $modSettings, $sourcedir, $boarddir, $boardurl, $smcFunc;

// First load the SMF 2's Extra DB Functions
db_extend('packages');
db_extend('extra');

    $KBHooks = array(
	    'integrate_pre_include' => '$sourcedir/KB/KBHooks.php',
		'integrate_load_theme' => 'KB_loadTheme',
		'integrate_admin_areas' => 'KB_admin_areas',
	    'integrate_menu_buttons' => 'KB_menu_buttons',
		'integrate_actions' => 'KB_actions',
		'integrate_load_permissions' => 'KB_load_permissions',
		'integrate_buffer' => 'KB_ob',
		'integrate_profile_areas' => 'KB_profile_areas',
	);
    foreach ($KBHooks as $hook => $function)
	add_integration_function($hook, $function);

	$smcFunc['db_insert']('ignore', '{db_prefix}settings',
		array('variable' => 'string','value' => 'string',),
	    array(
		array ('kb_enabled' ,'1'),
		array ('kb_search_engines' ,'1'),
		array ('kb_menutype' ,'2'),
		array ('kb_eratings' ,'1'),
		array ('kb_ecom' ,'1'),
		array ('kb_esearch' ,'1'),
		array ('kb_social' ,'1'),
		array ('kb_spinfo' ,'1'),
		array ('kb_show_view','1'),
		array ('kb_salegend' ,'1'),
		array ('kb_quiksearchindex' ,'1'),
		array ('kb_app' ,'20'),
		array ('kb_cpp' ,'20'),
		array ('kb_knowledge_only' ,'0'),
		array ('kb_disable_pm' ,'0'),
		array ('kb_disable_mlist' ,'0'),
		array ('kb_countsub','1'),
		array ('kb_efeaturedarticle','0'),
		array ('kb_enablersscat','1'),
		array('kb_disable_log','1'),
		array('kb_add_article','1'),
		array('kb_del_article','1'),
		array('kb_edit_article','1'),
		array('kb_app_article','1'),
		array('kb_unapp_article','1'),
		array('kb_add_cat','1'),
		array('kb_edit_cat','1'),
		array('kb_perm_cat','1'),
		array('kb_del_cat','1'),
		array('kb_app_com','1'),
		array('kb_add_com','1'),
		array('kb_del_com','1'),
		array('kb_add_report','1'),
		array('kb_del_report','1'),
		array('kb_del_wait','24'),
		array('kb_log_perpage','30'),
		array('kb_enable_attachment','1'),
		array('kb_num_attachment','0'),
		array('kb_mfile_attachment','0'),
		array('kb_url_attachment',$boardurl.'/articles/'),
		array('kb_path_attachment',$boarddir.'/articles/'),
		array('kb_attachmentDirSizeLimit','0'),
		array('kb_attachmentCheckExtensions','gif,jpg,png'),
		array('kb_attachmentExtensions','0'),
		),
		array());
	
    	$smcFunc['db_create_table']('{db_prefix}kb_attachments',
	array(
		array(
			'name' => 'id_file',
			'type' => 'int',
			'size' => 11,
			'auto' => true,
		),
		array(
			'name' => 'id_article',
			'type' => 'int',
			'size' => 11,
			'default' => 0,
		),
		array(
			'name' => 'type',
			'type' => 'tinyint',
			'size' => 4,
			'default' => 0,
		),
		array(
			'name' => 'thumbnail',
			'type' => 'tinytext',
		),	
		array(
			'name' => 'filename',
			'type' => 'tinytext',
		),	
		array(
			'name' => 'date',
			'type' => 'int',
			'default' => 0,
			'size' => 10
		),
		array(
			'name' => 'filesize',
			'type' => 'int',
			'default' => 0,
			'size' => 11
		),	
		array(
			'name' => 'views',
			'type' => 'int',
			'default' => 0,
			'size' => 11
		),	
	),
	array(
		array(
			'name' => 'id_file',
			'type' => 'primary',
			'columns' => array('id_file'),
		),
	),
	array(),
	'ignore');
	
	$smcFunc['db_create_table']('{db_prefix}kb_comments',
	array(
		array(
			'name' => 'id',
			'type' => 'int',
			'size' => 11,
			'auto' => true,
		),
		array(
			'name' => 'id_article',
			'type' => 'int',
			'size' => 11,
			'default' => 0,
		),
		array(
			'name' => 'id_member',
			'type' => 'mediumint',
			'size' => 8,
			'default' => 0,
		),
		array(
			'name' => 'date',
			'type' => 'int',
			'default' => 0,
			'null' => false
		),
		array(
			'name' => 'comment',
			'type' => 'text',
		),
		array(
			'name' => 'approved',
			'type' => 'int',
			'default' => 0,
		),
	),
	array(
		array(
			'name' => 'id',
			'type' => 'primary',
			'columns' => array('id'),
		),
	),
	array(),
	'ignore');
	
	$smcFunc['db_create_table']('{db_prefix}kb_reports',
	array(
		array(
			'name' => 'id',
			'type' => 'int',
			'size' => 11,
			'auto' => true,
		),
		array(
			'name' => 'id_article',
			'type' => 'int',
			'size' => 11,
			'default' => 0,
		),
		array(
			'name' => 'id_member',
			'type' => 'mediumint',
			'size' => 8,
			'default' => 0,
		),
		array(
			'name' => 'date',
			'type' => 'int',
			'default' => 0,
			'null' => false
		),
		array(
			'name' => 'comment',
			'type' => 'text',
		),	
	),
	array(
		array(
			'name' => 'id',
			'type' => 'primary',
			'columns' => array('id'),
		),
	),
	array(),
	'ignore');
	
    $smcFunc['db_create_table']('{db_prefix}kb_rating',
	array(
		array(
			'name' => 'id',
			'type' => 'int',
			'size' => 11,
			'auto' => true,
		),
		array(
			'name' => 'id_article',
			'type' => 'int',
			'size' => 11,
			'default' => 0,
		),
		array(
			'name' => 'id_member',
			'type' => 'mediumint',
			'size' => 8,
			'default' => 0,
		),
		array(
			'name' => 'value',
			'type' => 'tinyint',
			'size' => 1,
			'default' => 0,
		),
	),
	array(
		array(
			'name' => 'id',
			'type' => 'primary',
			'columns' => array('id'),
		),
	),
	array(),
	'ignore');
	
    $smcFunc['db_create_table']('{db_prefix}kb_catperm',
	array(
		array(
			'name' => 'id',
			'type' => 'mediumint',
			'size' => 8,
			'auto' => true,
		),
		array(
			'name' => 'id_group',
			'type' => 'mediumint',
			'size' => 8,
			'default' => 0,
		),
		array(
			'name' => 'id_cat',
			'type' => 'mediumint',
			'size' => 8,
			'default' => 0,
		),
		array(
			'name' => 'addarticle',
			'type' => 'tinyint',
			'size' => 4,
			'default' => 0,
		),
		array(
			'name' => 'editarticle',
			'type' => 'tinyint',
			'size' => 4,
			'default' => 0,
		),
		array(
			'name' => 'delarticle',
			'type' => 'tinyint',
			'size' => 4,
			'default' => 0,
		),
		array(
			'name' => 'editallarticle',
			'type' => 'tinyint',
			'size' => 4,
			'default' => 0,
		),
		array(
			'name' => 'delallarticle',
			'type' => 'tinyint',
			'size' => 4,
			'default' => 0,
		),
		array(
			'name' => 'ratearticle',
			'type' => 'tinyint',
			'size' => 4,
			'default' => 0,
		),
		array(
			'name' => 'addcomment',
			'type' => 'tinyint',
			'size' => 4,
			'default' => 0,
		),
		array(
			'name' => 'delcomment',
			'type' => 'tinyint',
			'size' => 4,
			'default' => 0,
		),
		array(
			'name' => 'report',
			'type' => 'tinyint',
			'size' => 4,
			'default' => 0,
		),
		array(
			'name' => 'view',
			'type' => 'tinyint',
			'size' => 4,
			'default' => 0,
		),
	),
	array(
		array(
			'name' => 'id',
			'type' => 'primary',
			'columns' => array('id'),
		),
	),
	array(),
	'ignore');
	
	$smcFunc['db_create_table']('{db_prefix}kb_articles',
	array(
		array(
			'name' => 'kbnid',
			'type' => 'smallint',
			'size' => 5,
			'auto' => true,
		),
		array(
			'name' => 'approved',
			'type' => 'int',
			'default' => 0,
		),
		array(
			'name' => 'rate',
			'type' => 'int',
			'default' => 0,
		),
		array(
			'name' => 'title',
			'type' => 'varchar',
			'size' => 100,
		),
		array(
			'name' => 'content',
			'type' => 'text',
		),
		array(
			'name' => 'views',
			'type' => 'int',
			'size' => 10,
			'default' => 0,
		),
		array(
			'name' => 'id_member',
			'type' => 'int',
			'default' => 0,
		),
		array(
			'name' => 'id_cat',
			'type' => 'int',
			'default' => 0,
		),
		array(
			'name' => 'date',
			'type' => 'int',
			'default' => 0,
			'null' => false
		),
		array(
			'name' => 'featured',
			'type' => 'tinyint',
			'size' => 4,
			'default' => 0,
		),
	),
	array(
		array(
			'name' => 'kbnid',
			'type' => 'primary',
			'columns' => array('kbnid'),
		),
	),
	array(),
	'ignore');
		
	$smcFunc['db_create_table']('{db_prefix}kb_category',
	array(
		array(
			'name' => 'kbid',
			'type' => 'smallint',
			'size' => 5,
			'auto' => true,
		),
		array(
			'name' => 'name',
			'type' => 'varchar',
			'size' => 50,
		),	
		array(
			'name' => 'image',
			'type' => 'tinytext',
			'null' => true,
		),
		array(
			'name' => 'description',
			'type' => 'text',
		),	
		array(
			'name' => 'count',
			'type' => 'int',
			'size' => 10,
			'default' => 0,
		),
		array(
			'name' => 'id_parent',
			'type' => 'int',
			'default' => 0,
		),
		array(
			'name' => 'roword',
			'type' => 'int',
			'default' => 0,
		),
	),
	array(
		array(
			'name' => 'kbid',
			'type' => 'primary',
			'columns' => array('kbid'),
		),
	),
	array(),
	'ignore');
	
	$smcFunc['db_create_table']('{db_prefix}kb_log_actions',
	array(
		array(
			'name' => 'id_log',
			'type' => 'smallint',
			'size' => 5,
			'auto' => true,
		),
		array(
			'name' => 'article_id',
			'type' => 'int',
			'size' => 10,
			'default' => 0,
		),
		array(
			'name' => 'user_id',
			'type' => 'int',
			'size' => 10,
			'default' => 0,
		),
		array(
			'name' => 'reason',
			'type' => 'text',
		),
		array(
			'name' => 'time',
			'type' => 'int',
			'default' => 0,
			'null' => false
		),
	),
	array(
		array(
			'name' => 'id_log',
			'type' => 'primary',
			'columns' => array('id_log'),
		),
	),
	array(),
	'ignore');
	
	///////////////////////////////////////////////
	             /*DATABASE UPDATES*/
	///////////////////////////////////////////////
	
	$smcFunc['db_add_column']('{db_prefix}kb_log_actions', 
        array(
		    'name' => 'user_ip',
			'type' => 'tinytext',
			'null' => true,
		)
    );
	$smcFunc['db_add_column']('{db_prefix}kb_log_actions', 
        array(
		    'name' => 'action',
			'type' => 'tinytext',
			'null' => true,
		)
    );
	
	$smcFunc['db_add_column']('{db_prefix}kb_category', 
        array(
		    'name' => 'image',
			'type' => 'tinytext',
			'null' => true,
		)
    );
	
	$smcFunc['db_add_column']('{db_prefix}kb_category', 
        array(
	        'name' => 'roword',
	        'type' => 'int',
			'default' => 0,
	    )
    );
	
	$smcFunc['db_add_column']('{db_prefix}kb_category', 
        array(
	        'name' => 'id_parent',
	        'type' => 'int',
			'default' => 0,
	    )
    );
	
	$smcFunc['db_add_column']('{db_prefix}kb_comments', 
        array(
	        'name' => 'approved',
	        'type' => 'int',
		'default' => 0,
	    )
    );
	
	$smcFunc['db_add_column']('{db_prefix}kb_catperm', 
        array(
	        'name' => 'report',
	        'type' => 'tinyint',
		    'size' => 4,
		'default' => 0,
	    )
    );
	
	$smcFunc['db_add_column']('{db_prefix}kb_catperm', 
        array(
	        'name' => 'addcomment',
	        'type' => 'tinyint',
		    'size' => 4,
		'default' => 0,
	    )
    );
	
	$smcFunc['db_add_column']('{db_prefix}kb_catperm', 
        array(
	        'name' => 'delcomment',
	        'type' => 'tinyint',
		    'size' => 4,
		'default' => 0,
	    )
    );
	
	$smcFunc['db_add_column']('{db_prefix}kb_catperm', 
        array(
	        'name' => 'ratearticle',
	        'type' => 'tinyint',
		    'size' => 4,
		'default' => 0,
	    )
    );
	
	$smcFunc['db_add_column']('{db_prefix}kb_catperm', 
        array(
	        'name' => 'addarticle',
	        'type' => 'tinyint',
		    'size' => 4,
		'default' => 0,
	    )
    );
	
	$smcFunc['db_add_column']('{db_prefix}kb_catperm', 
        array(
	        'name' => 'editanyarticle',
	        'type' => 'tinyint',
		    'size' => 4,
		'default' => 0,
	    )
    );
	
	$smcFunc['db_add_column']('{db_prefix}kb_catperm', 
        array(
	        'name' => 'delanyarticle',
	        'type' => 'tinyint',
		    'size' => 4,
		'default' => 0,
	    )
    );
	
	$smcFunc['db_add_column']('{db_prefix}kb_catperm', 
        array(
	        'name' => 'editarticle',
	        'type' => 'tinyint',
		    'size' => 4,
		'default' => 0,
	    )
    );
	
	$smcFunc['db_add_column']('{db_prefix}kb_catperm', 
        array(
	        'name' => 'delarticle',
	        'type' => 'tinyint',
		    'size' => 4,
		'default' => 0,
	)
    );
	
	$smcFunc['db_add_column']('{db_prefix}kb_articles', 
        array(
	        'name' => 'rate',
	        'type' => 'int',
		'default' => 0,
	    )
    );
	
	$smcFunc['db_add_column']('{db_prefix}kb_articles', 
        array(
	        'name' => 'approved',
	        'type' => 'int',
		'default' => 0,
	    )
    );
	
	$smcFunc['db_add_column']('{db_prefix}kb_articles', 
        array(
	        'name' => 'comments',
	        'type' => 'tinyint',
		    'size' => 4,
		'default' => 0,
	    )
    );
	
	$smcFunc['db_add_column']('{db_prefix}kb_articles', 
        array(
	        'name' => 'featured',
	        'type' => 'tinyint',
		    'size' => 4,
		'default' => 0,
	    )
    );
	
	$smcFunc['db_add_column']('{db_prefix}kb_articles', 
        array(
			'name' => 'source',
			'type' => 'text',
	    )
    );
	$smcFunc['db_change_column']( $db_prefix . 'kb_articles', 'title', array('size' =>   100));
	$smcFunc['db_change_column']( $db_prefix . 'kb_articles', 'veiws', array('name' =>   'views'));
	$smcFunc['db_change_column']( $db_prefix . 'kb_comments', 'id_artical', array('name' =>   'id_article'));
	$smcFunc['db_change_column']( $db_prefix . 'kb_reports', 'id_artical', array('name' =>   'id_article'));
	$smcFunc['db_change_column']( $db_prefix . 'kb_rating', 'id_artical', array('name' =>   'id_article'));
	$smcFunc['db_change_column']( $db_prefix . 'kb_catperm', 'veiw', array('name' =>   'view'));
	$smcFunc['db_change_column']( $db_prefix . 'kb_category', 'parant', array('name' =>   'parent'));
?>            