<?php
/*---------------------------------------------------------------------------------
*	Ultimate Portal
*	Version 0.4
*	Project manager: vicram10
*	Copyright 2011-2921
*	Powered by SMFSimple.com
**********************************************************************************/
	global $mbname, $boardurl, $db_prefix, $context;
	global $smcFunc, $db_name;
	// Define the Manual Installation Status
    $manual_install = false;
    if (file_exists(dirname(__FILE__) . '/SSI.php') && !defined('SMF')){
		require_once(dirname(__FILE__) . '/SSI.php');

		$manual_install = true;
    }
    elseif (!defined('SMF'))
	die('The Ultimate Portal installer wasn\'t able to connect to SMF! Make sure that you are either installing this via the Package Manager or the SSI.php file is in the same directory.');
    if ($manual_install)
	echo '<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Transitional//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-transitional.dtd">
<html xmlns="http://www.w3.org/1999/xhtml"><head>
	<title>Ultimate Portal Database Installer</title>
     <link rel="stylesheet" type="text/css" href="Themes/default/style.css" />
</head>
<body>
	<br /><br />';
	//Call db_extend
	db_extend('packages');
    // The Ultimate Portal Creating tables
	$tables = array(
		// UP Settings
		'ultimate_portal_settings' => array(
			'name' => 'ultimate_portal_settings',
			//Columns
			'columns' => array(
				array(
					'name' => 'variable',
					'type' => 'text',
				),
				array(
					'name' => 'value',
					'type' => 'text',
				),
				array(
					'name' => 'section',
					'type' => 'text',
				),
			),
			//End Columns
			//Primary?
			'indexes' => array(
				array(
					'type' => 'primary',
					'columns' => array('variable(30)')
				),
			)
			//End Primary
		),
		//End Table
		// UP Main Link
		'ultimate_portal_main_links' => array(
			'name' => 'ultimate_portal_main_links',
			//Columns
			'columns' => array(
				array(
					'name' => 'id',
					'type' => 'int',
					'auto' => true,
					'unsigned' => true,
				),
				array(
					'name' => 'icon',
					'type' => 'text',
				),
				array(
					'name' => 'title',
					'type' => 'varchar',
					'size' => 255,
					'null' => 'not null',
					'default' => '',
				),
				array(
					'name' => 'url',
					'type' => 'text',
				),
				array(
					'name' => 'position',
					'type' => 'int',
					'size' => 2,
					'null' => 'not null',
					'unsigned' => true,
					'default' => 0,
				),
				array(
					'name' => 'active',
					'type' => 'tinyint',
					'size' => 1,
					'null' => 'not null',
					'unsigned' => true,
					'default' => 0,
				),
				array(
					'name' => 'top_menu',
					'type' => 'tinyint',
					'size' => 1,
					'null' => 'not null',
					'unsigned' => true,
					'default' => 0,
				),
			),
			//End Columns
			//Primary?
			'indexes' => array(
				array(
					'type' => 'primary',
					'columns' => array('id')
				),
			)
			//End Primary
		),
		//End Table
		// UP Blocks
		'ultimate_portal_blocks' => array(
			'name' => 'ultimate_portal_blocks',
			//Columns
			'columns' => array(
				array(
					'name' => 'id',
					'type' => 'int',
					'size' => 11,
					'auto' => true,
					'unsigned' => true,
				),
				array(
					'name' => 'file',
					'type' => 'varchar',
					'size' => 255,
					'null' => 'null',
					'default' => '',
				),
				array(
					'name' => 'title',
					'type' => 'varchar',
					'size' => 255,
					'null' => 'null',
					'default' => '',
				),
				array(
					'name' => 'icon',
					'type' => 'varchar',
					'size' => 255,
					'null' => 'null',
					'default' => '',
				),
				array(
					'name' => 'position',
					'type' => 'varchar',
					'size' => 20,
					'null' => 'not null',
					'default' => 'left',
				),
				array(
					'name' => 'progressive',
					'type' => 'int',
					'size' => 3,
					'null' => 'not null',
					'unsigned' => true,
					'default' => 100,
				),
				array(
					'name' => 'active',
					'type' => 'varchar',
					'size' => 10,
					'null' => 'null',
					'default' => '',
				),
				array(
					'name' => 'personal',
					'type' => 'int',
					'size' => 2,
					'null' => 'not null',
					'unsigned' => true,
					'default' => 0,
				),
				array(
					'name' => 'content',
					'type' => 'text',
				),
				array(
					'name' => 'perms',
					'type' => 'text',
				),
				array(
					'name' => 'bk_collapse',
					'type' => 'char',
					'size' => 5,
					'null' => 'null',
					'default' => '',
				),
				array(
					'name' => 'bk_no_title',
					'type' => 'char',
					'size' => 5,
					'null' => 'null',
					'default' => '',
				),
				array(
					'name' => 'bk_style',
					'type' => 'char',
					'size' => 5,
					'null' => 'null',
					'default' => '',
				),
				array(
					'name' => 'mbk_view',
					'type' => 'text',
				),
			),
			//End Columns
			//Primary?
			'indexes' => array(
				array(
					'type' => 'primary',
					'columns' => array('id')
				),
			)
			//End Primary
		),
		//End Table
		// UP Extra Fields
		'uposts_extra_field' => array(
			'name' => 'uposts_extra_field',
			//Columns
			'columns' => array(
				array(
					'name' => 'id',
					'type' => 'int',
					'size' => 10,
					'auto' => true,
					'unsigned' => true,
				),
				array(
					'name' => 'title',
					'type' => 'varchar',
					'size' => 40,
					'null' => 'not null',
				),
				array(
					'name' => 'icon',
					'type' => 'varchar',
					'size' => 255,
					'null' => 'not null',
				),
				array(
					'name' => 'field',
					'type' => 'varchar',
					'size' => 100,
					'null' => 'not null',
					'default' => '0',
				),
			),
			//End Columns
			//Primary?
			'indexes' => array(
				array(
					'type' => 'primary',
					'columns' => array('id')
				),
			)
			//End Primary
		),
		//End Table
		// UP User Posts
		'up_user_posts' => array(
			'name' => 'up_user_posts',
			//Columns
			'columns' => array(
				array(
					'name' => 'id',
					'type' => 'int',
					'size' => 10,
					'auto' => true,
					'unsigned' => true,
				),
				array(
					'name' => 'title',
					'type' => 'text',
				),
				array(
					'name' => 'cover',
					'type' => 'text',
				),
				array(
					'name' => 'description',
					'type' => 'text',
				),
				array(
					'name' => 'link_topic',
					'type' => 'text',
				),
				array(
					'name' => 'author',
					'type' => 'tinytext',
				),
				array(
					'name' => 'id_member_add',
					'type' => 'int',
					'size' => '10',
					'unsigned' => true,
					'null' => 'null'
				),
				array(
					'name' => 'username_add',
					'type' => 'tinytext',
				),
				array(
					'name' => 'id_member_updated',
					'type' => 'int',
					'size' => '10',
					'unsigned' => true,
					'null' => 'null'
				),
				array(
					'name' => 'username_updated',
					'type' => 'tinytext',
				),
				array(
					'name' => 'date_add',
					'type' => 'int',
					'size' => '10',
					'unsigned' => true,
					'null' => 'null'
				),
				array(
					'name' => 'date_updated',
					'type' => 'int',
					'size' => '10',
					'unsigned' => true,
					'null' => 'null'
				),
				array(
					'name' => 'type_post',
					'type' => 'text',
				),
				array(
					'name' => 'lang_post',
					'type' => 'text',
				),
			),
			//End Columns
			//Primary?
			'indexes' => array(
				array(
					'type' => 'primary',
					'columns' => array('id')
				),
			)
			//End Primary
		),
		//End Table
		// UP Groups Perms
		'up_groups_perms' => array(
			'name' => 'up_groups_perms',
			//Columns
			'columns' => array(
				array(
					'name' => 'ID_GROUP',
					'type' => 'smallint',
					'size' => 5,
					'null' => 'not null',
					'default' => 0,
					'unsigned' => true,
				),
				array(
					'name' => 'permission',
					'type' => 'varchar',
					'size' => 30,
					'default' => '',
				),
				array(
					'name' => 'value',
					'type' => 'tinyint',
					'size' => 4,
					'null' => 'not null',
					'default' => 1,
					'unsigned' => true,
				),
			),
			//End Columns
			//Primary?
			'indexes' => array(
				array(
					'type' => 'primary',
					'columns' => array('ID_GROUP','permission')
				),
			)
			//End Primary
		),
		//End Table
		// UP News
		'up_news' => array(
			'name' => 'up_news',
			//Columns
			'columns' => array(
				array(
					'name' => 'id',
					'type' => 'int',
					'size' => 11,
					'auto' => true,
					'unsigned' => true,
				),
				array(
					'name' => 'id_category',
					'type' => 'int',
					'size' => 10,
					'null' => 'not null',
					'default' => 0,
					'unsigned' => true,
				),
				array(
					'name' => 'id_member',
					'type' => 'int',
					'size' => 10,
					'null' => 'not null',
					'default' => 0,
					'unsigned' => true,
				),
				array(
					'name' => 'title',
					'type' => 'varchar',
					'size' => 255,
					'null' => 'not null',
				),
				array(
					'name' => 'username',
					'type' => 'varchar',
					'size' => 34,
					'null' => 'not null',
				),
				array(
					'name' => 'body',
					'type' => 'text',
				),
				array(
					'name' => 'date',
					'type' => 'int',
					'size' => 10,
					'null' => 'not null',
					'default' => 0,
					'unsigned' => true,
				),
				array(
					'name' => 'id_member_updated',
					'type' => 'int',
					'size' => 10,
					'null' => 'null',
					'unsigned' => true,
				),
				array(
					'name' => 'username_updated',
					'type' => 'varchar',
					'size' => 34,
					'null' => 'null',
				),
				array(
					'name' => 'date_updated',
					'type' => 'int',
					'size' => 10,
					'null' => 'null',
					'unsigned' => true,
				),
			),
			//End Columns
			//Primary?
			'indexes' => array(
				array(
					'type' => 'primary',
					'columns' => array('id')
				),
			)
			//End Primary
		),
		//End Table
		// UP News Section
		'up_news_sections' => array(
			'name' => 'up_news_sections',
			//Columns
			'columns' => array(
				array(
					'name' => 'id',
					'type' => 'int',
					'size' => 10,
					'auto' => true,
					'unsigned' => true,
				),
				array(
					'name' => 'title',
					'type' => 'varchar',
					'size' => 40,
					'null' => 'not null',
				),
				array(
					'name' => 'icon',
					'type' => 'varchar',
					'size' => 255,
					'null' => 'not null',
				),
				array(
					'name' => 'position',
					'type' => 'int',
					'size' => 4,
					'null' => 'not null',
					'default' => 0,
					'unsigned' => true,
				),
			),
			//End Columns
			//Primary?
			'indexes' => array(
				array(
					'type' => 'primary',
					'columns' => array('id')
				),
			)
			//End Primary
		),
		//End Table
		// UP Downloads Section
		'up_download_sections' => array(
			'name' => 'up_download_sections',
			//Columns
			'columns' => array(
				array(
					'name' => 'id',
					'type' => 'int',
					'size' => 10,
					'auto' => true,
					'unsigned' => true,
				),
				array(
					'name' => 'title',
					'type' => 'text',
				),
				array(
					'name' => 'description',
					'type' => 'text',
				),
				array(
					'name' => 'icon',
					'type' => 'text',
				),
				array(
					'name' => 'id_groups',
					'type' => 'text',
				),
				array(
					'name' => 'total_files',
					'type' => 'int',
					'size' => 10,
					'null' => 'not null',
					'unsigned' => true,
				),
				array(
					'name' => 'id_board',
					'type' => 'smallint',
					'size' => 5,
					'null' => 'not null',
					'default' => 0,
					'unsigned' => true,
				),
			),
			//End Columns
			//Primary?
			'indexes' => array(
				array(
					'type' => 'primary',
					'columns' => array('id')
				),
			)
			//End Primary
		),
		//End Table
		// UP Downloads Files
		'up_download_files' => array(
			'name' => 'up_download_files',
			//Columns
			'columns' => array(
				array(
					'name' => 'id_files',
					'type' => 'int',
					'size' => 10,
					'auto' => true,
					'unsigned' => true,
				),
				array(
					'name' => 'title',
					'type' => 'text',
				),
				array(
					'name' => 'description',
					'type' => 'text',
				),
				array(
					'name' => 'id_member',
					'type' => 'mediumint',
					'size' => 8,
					'null' => 'null',
					'unsigned' => true,
				),
				array(
					'name' => 'membername',
					'type' => 'varchar',
					'size' => 80,
					'null' => 'null',
				),
				array(
					'name' => 'small_description',
					'type' => 'text',
				),
				array(
					'name' => 'id_section',
					'type' => 'int',
					'size' => 10,
					'null' => 'null',
					'unsigned' => true,
				),
				array(
					'name' => 'date_created',
					'type' => 'int',
					'size' => 10,
					'null' => 'null',
					'unsigned' => true,
				),
				array(
					'name' => 'date_updated',
					'type' => 'int',
					'size' => 10,
					'null' => 'null',
					'unsigned' => true,
				),
				array(
					'name' => 'total_downloads',
					'type' => 'int',
					'size' => 100,
					'null' => 'null',
					'unsigned' => true,
				),
				array(
					'name' => 'approved',
					'type' => 'tinyint',
					'size' => 8,
					'null' => 'not null',
					'default' => 0,
					'unsigned' => true,
				),
				array(
					'name' => 'id_topic',
					'type' => 'mediumint',
					'size' => 8,
					'null' => 'not null',
					'default' => 0,
					'unsigned' => true,
				),
			),
			//End Columns
			//Primary?
			'indexes' => array(
				array(
					'type' => 'primary',
					'columns' => array('id_files')
				),
			)
			//End Primary
		),
		//End Table
		// UP Downloads Attachments
		'up_download_attachments' => array(
			'name' => 'up_download_attachments',
			//Columns
			'columns' => array(
				array(
					'name' => 'ID_ATTACH',
					'type' => 'int',
					'size' => 10,
					'auto' => true,
					'unsigned' => true,
				),
				array(
					'name' => 'id_files',
					'type' => 'int',
					'size' => 10,
					'null' => 'null',
					'unsigned' => true,
				),
				array(
					'name' => 'ID_THUMB',
					'type' => 'int',
					'size' => 10,
					'null' => 'null',
					'unsigned' => true,
				),
				array(
					'name' => 'attachmentType',
					'type' => 'tinyint',
					'size' => 3,
					'null' => 'not null',
					'default' => 0,
					'unsigned' => true,
				),
				array(
					'name' => 'filename',
					'type' => 'text',
				),
				array(
					'name' => 'file_hash',
					'type' => 'text',
				),
				array(
					'name' => 'size',
					'type' => 'int',
					'size' => 10,
					'null' => 'not null',
					'default' => 0,
					'unsigned' => true,
				),
				array(
					'name' => 'downloads',
					'type' => 'mediumint',
					'size' => 8,
					'null' => 'not null',
					'default' => 0,
					'unsigned' => true,
				),
				array(
					'name' => 'width',
					'type' => 'mediumint',
					'size' => 8,
					'null' => 'not null',
					'default' => 0,
					'unsigned' => true,
				),
				array(
					'name' => 'height',
					'type' => 'mediumint',
					'size' => 8,
					'null' => 'not null',
					'default' => 0,
					'unsigned' => true,
				),
				array(
					'name' => 'mime_type',
					'type' => 'varchar',
					'size' => 20,
					'null' => 'null',
				),
				array(
					'name' => 'fileext',
					'type' => 'varchar',
					'size' => 8,
					'null' => 'null',
				),
			),
			//End Columns
			//Primary?
			'indexes' => array(
				array(
					'type' => 'primary',
					'columns' => array('ID_ATTACH')
				),
			)
			//End Primary
		),
		//End Table
		// UP Internal Page
		'ultimate_portal_ipage' => array(
			'name' => 'ultimate_portal_ipage',
			//Columns
			'columns' => array(
				array(
					'name' => 'id',
					'type' => 'int',
					'size' => 11,
					'auto' => true,
					'unsigned' => true,
				),
				array(
					'name' => 'title',
					'type' => 'varchar',
					'size' => 255,
					'default' => '',
				),
				array(
					'name' => 'sticky',
					'type' => 'int',
					'size' => 3,
					'default' => 0,
					'unsigned' => true,
				),
				array(
					'name' => 'active',
					'type' => 'varchar',
					'size' => 10,
					'default' => '',
				),
				array(
					'name' => 'type_ipage',
					'type' => 'text',
				),
				array(
					'name' => 'content',
					'type' => 'text',
				),
				array(
					'name' => 'perms',
					'type' => 'text',
				),
				array(
					'name' => 'column_left',
					'type' => 'int',
					'size' => 1,
					'default' => 0,
					'unsigned' => true,
				),
				array(
					'name' => 'column_right',
					'type' => 'int',
					'size' => 1,
					'default' => 0,
					'unsigned' => true,
				),
				array(
					'name' => 'date_created',
					'type' => 'int',
					'size' => 10,
					'null' => 'null',
					'unsigned' => true,
				),
				array(
					'name' => 'date_updated',
					'type' => 'int',
					'size' => 10,
					'null' => 'null',
					'unsigned' => true,
				),
				array(
					'name' => 'id_member',
					'type' => 'int',
					'size' => 10,
					'null' => 'not null',
					'unsigned' => true,
				),
				array(
					'name' => 'username',
					'type' => 'tinytext',
				),
				array(
					'name' => 'id_member_updated',
					'type' => 'int',
					'size' => 10,
					'null' => 'null',
					'unsigned' => true,
				),
				array(
					'name' => 'username_updated',
					'type' => 'tinytext',
				),
			),
			//End Columns
			//Primary?
			'indexes' => array(
				array(
					'type' => 'primary',
					'columns' => array('id')
				),
			)
			//End Primary
		),
		//End Table
		// UP Afiliates
		'up_affiliates' => array(
			'name' => 'up_affiliates',
			//Columns
			'columns' => array(
				array(
					'name' => 'id',
					'type' => 'mediumint',
					'size' => 8,
					'auto' => true,
					'unsigned' => true,
				),
				array(
					'name' => 'title',
					'type' => 'tinytext',
				),
				array(
					'name' => 'url',
					'type' => 'tinytext',
				),
				array(
					'name' => 'imageurl',
					'type' => 'tinytext',
				),
				array(
					'name' => 'alt',
					'type' => 'tinytext',
				),
			),
			//End Columns
			//Primary?
			'indexes' => array(
				array(
					'type' => 'primary',
					'columns' => array('id')
				),
			)
			//End Primary
		),
		//End Table
		// UP Faqs
		'up_faq' => array(
			'name' => 'up_faq',
			//Columns
			'columns' => array(
				array(
					'name' => 'id',
					'type' => 'tinyint',
					'size' => 5,
					'auto' => true,
					'unsigned' => true,
				),
				array(
					'name' => 'question',
					'type' => 'varchar',
					'size' => 255,
					'null' => 'not null',
				),
				array(
					'name' => 'answer',
					'type' => 'text',
				),
				array(
					'name' => 'id_section',
					'type' => 'tinyint',
					'size' => 5,
					'null' => 'not null',
					'unsigned' => true,
				),
			),
			//End Columns
			//Primary?
			'indexes' => array(
				array(
					'type' => 'primary',
					'columns' => array('id')
				),
			)
			//End Primary
		),
		//End Table
		// UP Faqs Sections
		'up_faq_section' => array(
			'name' => 'up_faq_section',
			//Columns
			'columns' => array(
				array(
					'name' => 'id_section',
					'type' => 'tinyint',
					'size' => 5,
					'auto' => true,
					'unsigned' => true,
				),
				array(
					'name' => 'section',
					'type' => 'varchar',
					'size' => 255,
					'null' => 'not null',
				),
			),
			//End Columns
			//Primary?
			'indexes' => array(
				array(
					'type' => 'primary',
					'columns' => array('id_section')
				),
			)
			//End Primary
		),
		//End Table
		// UP Multiblock
		'up_multiblock' => array(
			'name' => 'up_multiblock',
			//Columns
			'columns' => array(
				array(
					'name' => 'id',
					'type' => 'int',
					'size' => 11,
					'auto' => true,
					'unsigned' => true,
				),
				array(
					'name' => 'title',
					'type' => 'text',
				),
				array(
					'name' => 'blocks',
					'type' => 'text',
				),
				array(
					'name' => 'position',
					'type' => 'text',
				),
				array(
					'name' => 'design',
					'type' => 'text',
				),
				array(
					'name' => 'mbk_title',
					'type' => 'char',
					'size' => 5,
					'null' => 'null',
					'default' => '',
				),
				array(
					'name' => 'mbk_collapse',
					'type' => 'char',
					'size' => 5,
					'null' => 'null',
					'default' => '',
				),
				array(
					'name' => 'mbk_style',
					'type' => 'char',
					'size' => 5,
					'null' => 'null',
					'default' => '',
				),
				array(
					'name' => 'order',
					'type' => 'int',
					'size' => 3,
					'null' => 'null',
					'unsigned' => true,
				),
				array(
					'name' => 'enable',
					'type' => 'smallint',
					'size' => 1,
					'null' => 'null',
					'unsigned' => true,
				),
			),
			//End Columns
			//Primary?
			'indexes' => array(
				array(
					'type' => 'primary',
					'columns' => array('id')
				),
			)
			//End Primary
		),
		//End Table
	);

	//Creating Tables
	foreach ($tables as $table)
	{
		$table_name = $table['name'];
		$smcFunc['db_create_table']('{db_prefix}' . $table_name, $table['columns'], $table['indexes']);
		$currentTable = $smcFunc['db_table_structure']('{db_prefix}' . $table_name);
		// Check that all columns are in
		foreach ($table['columns'] as $id => $col)
		{
			$exists = false;
			// TODO: Check that definition is correct
			foreach ($currentTable['columns'] as $col2)
			{
				if ($col['name'] === $col2['name'])
				{
					$exists = true;
					break;
				}
			}

			// Add missing columns
			if (!$exists)
				$smcFunc['db_add_column']('{db_prefix}' . $table_name, $col);

			//Check, not change anything?
			if($exists)
			{
				$smcFunc['db_change_column']('{db_prefix}' . $table_name, $col['name'], $col);
			}

		}
		//End add missing columns
		// Check that all indexes are in and correct
		foreach ($table['indexes'] as $id => $index)
		{
			$exists = false;

			foreach ($currentTable['indexes'] as $index2)
			{
				// Primary is special case
				if ($index['type'] == 'primary' && $index2['type'] == 'primary')
				{
					$exists = true;

					if ($index['columns'] !== $index2['columns'])
					{
						$smcFunc['db_remove_index']('{db_prefix}' . $table_name, 'primary');
						$smcFunc['db_add_index']('{db_prefix}' . $table_name, $index);
					}

					break;
				}
				// Make sure index is correct
				elseif (isset($index['name']) && isset($index2['name']) && $index['name'] == $index2['name'])
				{
					$exists = true;

					// Need to be changed?
					if ($index['type'] != $index2['type'] || $index['columns'] !== $index2['columns'])
					{
						$smcFunc['db_remove_index']('{db_prefix}' . $table_name, $index['name']);
						$smcFunc['db_add_index']('{db_prefix}' . $table_name, $index);
					}

					break;
				}
			}

			if (!$exists)
				$smcFunc['db_add_index']('{db_prefix}' . $table_name, $index);
		}
		//End check indexes
	}

	//Install default rows
	$smcFunc['db_insert']('replace',
		'{db_prefix}ultimate_portal_settings',
		array('variable' => 'text', 'value' => 'text', 'section' => 'text'),
		array(
			array('ultimate_portal_enable', 'on', 'config_preferences'),
			array('ultimate_portal_home_title', ''. $mbname .' - Home', 'config_preferences'),
			array('favicons', '', 'config_preferences'),
			array('up_use_curve_variation', 'on', 'config_preferences'),
			array('ultimate_portal_width_col_left', '20%', 'config_preferences'),
			array('ultimate_portal_width_col_center', '60%', 'config_preferences'),
			array('ultimate_portal_width_col_right', '20%', 'config_preferences'),
			array('ultimate_portal_enable_icons', 'on', 'config_preferences'),
			array('ultimate_portal_enable_version', 'on', 'config_preferences'),
			array('ultimate_portal_enable_col_left', 'on', 'config_preferences'),
			array('ultimate_portal_enable_col_right', 'on', 'config_preferences'),
			array('up_forum_enable_col_left', 'on', 'config_preferences'),
			array('up_forum_enable_col_right', 'on', 'config_preferences'),
			array('ultimate_portal_icons_extention', '.png', 'config_preferences'),
			array('board_news_limit', '5', 'config_board_news'),
			array('board_news_view', '0', 'config_board_news'),
			array('board_news_lenght', '2000', 'config_board_news'),
			array('up_news_enable', 'on', 'enable_up_news'),
			array('up_news_limit', '5', 'config_up_news'),
			array('user_posts_enable', '', 'enable_user_posts'),
			array('user_posts_limit', '10', 'config_user_posts'),
			array('user_posts_field_title', '', 'config_user_posts'),
			array('user_posts_field_cover', 'on', 'config_user_posts'),
			array('user_posts_field_description', 'on', 'config_user_posts'),
			array('user_posts_field_link_topic', '', 'config_user_posts'),
			array('user_posts_field_topic_author', 'on', 'config_user_posts'),
			array('user_posts_field_member_use_module', 'on', 'config_user_posts'),
			array('user_posts_field_member_updated_module', 'on', 'config_user_posts'),
			array('user_posts_field_type_posts', 'on', 'config_user_posts'),
			array('user_posts_internal_page', 'on', 'config_user_posts'),
			array('user_posts_field_add_language', 'on', 'config_user_posts'),
			array('user_posts_cover_view', 'advanced', 'config_user_posts'),
			array('user_posts_cover_save_host', 'on', 'config_user_posts'),
			array('ultimate_portal_cover_watermark', ''.$mbname.'' , 'config_user_posts'),
			array('user_posts_header_show', '', 'config_user_posts'),
			array('user_posts_social_bookmarks', '', 'config_user_posts'),
			array('download_enable', '', 'enable_download'),
			array('download_file_limit_page', '10', 'config_download'),
			array('download_file_max_size', '2048', 'config_download'),
			array('download_extension_file', 'zip,tar.gz', 'config_download'),
			array('down_enable_send_pm_approved', '', 'config_download'),
			array('download_enable_approved_file', 'on', 'config_download'),
			array('download_pm_id_member', '', 'config_download'),
			array('download_pm_subject', '', 'config_download'),
			array('download_pm_body', '', 'config_download'),
			array('up_news_global_announcement', '', 'config_up_news'),
			array('ipage_enable', '', 'enable_ipage'),
			array('ipage_active_columns', '', 'config_ipage'),
			array('ipage_social_bookmarks', 'on', 'config_ipage'),
			array('ipage_limit', '10', 'config_ipage'),
			array('aff_limit', '10', 'config_affiliates'),
			array('aff_scrolldelay', '100', 'config_affiliates'),
			array('aff_direction', '3', 'config_affiliates'),
			array('aff_target', '2', 'config_affiliates'),
			array('about_us_enable', '', 'enable_about_us'),
			array('about_us_registered', 'on', 'config_about_us'),
			array('about_us_view_mail', 'on', 'config_about_us'),
			array('about_us_view_pm', 'on', 'config_about_us'),
			array('about_us_extrainfo_title', '', 'config_about_us'),
			array('about_us_extra_info', '', 'config_about_us'),
			array('about_us_show_nick', 'on', 'config_about_us'),
			array('about_us_show_rank', 'on', 'config_about_us'),
			array('about_us_group_view', '1', 'config_about_us'),
			array('faq_enable', '', 'enable_faq'),
			array('faq_title', '', 'config_faq'),
			array('faq_small_description', '', 'config_faq'),
			array('seo_google_analytics', '', 'config_seo'),
			array('seo_title_keyword', '', 'config_seo'),
			array('seo_google_verification_code', '', 'config_seo'),
			array('up_reduce_site_overload', '', 'config_preferences'),
			array('up_left_right_collapse', 'on', 'config_preferences'),
		),
		array('variable')
	);
	//End Add rows in UP settings table

	//smf_ultimate_portal_main_links INSERT
	$smcFunc['db_insert']('ignore',
		'{db_prefix}ultimate_portal_main_links',
		array('id' => 'int', 'icon' => 'text', 'title' => 'string',  'url' => 'text', 'position' => 'int', 'active' => 'int', 'top_menu' => 'int'),
		array(
			array(1, '<UP_MAIN_LINK_ICON>/home.png', 'Home', '<UP_BOARDURL>/index.php', 1, 1, 0),
			array(2, '<UP_MAIN_LINK_ICON>/forum.png', 'Forum', '<UP_BOARDURL>/index.php?action=forum', 2, 1, 0),
			array(3, '<UP_MAIN_LINK_ICON>/news.png', 'News', '<UP_BOARDURL>/index.php?action=news', 3, 0, 0),
			array(4, '<UP_MAIN_LINK_ICON>/download.png', 'Download', '<UP_BOARDURL>/index.php?action=downloads', 4, 0, 0),
			array(5, '<UP_MAIN_LINK_ICON>/user-posts.png', 'Great Posts', '<UP_BOARDURL>/index.php?action=user-posts', 5, 0, 0),
			array(6, '<UP_MAIN_LINK_ICON>/internal-page.png', 'Internal Page', '<UP_BOARDURL>/index.php?action=internal-page', 6, 0, 0),
			array(7, '<UP_MAIN_LINK_ICON>/about-us.png', 'About Us', '<UP_BOARDURL>/index.php?action=about', 7, 0, 0),
			array(8, '<UP_MAIN_LINK_ICON>/faq.png', 'FAQ&#039;S', '<UP_BOARDURL>/index.php?action=faq', 8, 0, 0),
		),
		array('id')
	);
	//End insert rows in Main links Table

	//Insert rows in Blocks table
	$smcFunc['db_insert']('ignore',
		'{db_prefix}ultimate_portal_blocks',
		array('id' => 'int', 'file' => 'string', 'title' => 'string',  'icon' => 'string', 'position' => 'string', 'progressive' => 'int', 'active' => 'string',
			  'personal' => 'int', 'content' => 'text', 'perms' => 'text', 'bk_collapse' => 'string', 'bk_no_title' => 'string', 'bk_style' => 'string', 'mbk_view' => 'text'),
		array(
			array(1, 'bk-boards-news.php', 'Board News', 'bk-boards-news', 'center', 2, 'checked', 0, '', '', 'on', '', 'on', ''),
			array(2, 'bk-personal-menu.php', 'User', 'bk-personal-menu', 'left', 2, 'checked', 0, '', '', 'on', '', 'on', ''),
			array(3, 'bk-menu.php', 'Menu Portal', 'bk-menu', 'left', 1, 'checked', 0, '', '', 'on', '', 'on', ''),
			array(4, 'bk-news.php', 'News', 'bk-news', 'center', 1, '', 0, '', '', 'on', '', 'on', ''),
			array(5, 'bk-online.php', 'Users Online', 'bk-online', 'left', 3, 'checked', 0, '', '', 'on', '', 'on', ''),
			array(6, 'bk-site-stats.php', 'Stats', 'bk-site-stats', 'right', 2, 'checked', 0, '', '', 'on', '', 'on', ''),
			array(7, 'bk-top-poster.php', 'Top Posters', 'bk-top-poster', 'right', 1, 'checked', 0, '', '', 'on', '', 'on', ''),
			array(8, 'bk-lastUsers.php', 'Last Users', 'bk-lastUsers', 'right', 4, 'checked', 0, '', '', 'on', '', 'on', ''),
			array(9, 'bk-recent-topics.php', 'Recent Topics', 'bk-recent-topics', 'right', 3, 'checked', 0, '', '', 'on', '', 'on', ''),
		),
		array('id')
	);
	//End insert rows in Blocks table

	// OK, time to report, output all the stuff to be shown to the user
	if ($manual_install){
echo '
<table cellpadding="0" cellspacing="0" border="0" class="tborder" width="800" align="center"><tr><td>
<div class="titlebg" style="padding: 1ex" align="center">
	Ultimate Portal Database Installer
</div>
<div class="windowbg2" style="padding: 2ex">
<div style="padding-top:30px">
	<strong>Your database update has been completed successfully!</strong>
	<br /><br />The Ultimate Portal Database was successfully installed.<br />
	Now you should go to the <a href="', $scripturl, '?action=admin;area=preferences">Ultimate Portal</a>.
	<br /><br />
	<span style="color: #FF0000"><strong>Please NOW Delete this file. </strong></span>
</div>
</td></tr></table>
<br />
</body></html>';
    }
?>