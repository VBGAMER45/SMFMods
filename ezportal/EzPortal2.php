<?php
/*
EzPortal
Version 5.4
by:vbgamer45
https://www.ezportal.com
Copyright 2010-2025 https://www.samsonsoftware.com
*/
function EzPortalMain()
{
	global $sourcedir, $ezPortalVersion, $context, $ezpSettings, $boardurl, $boarddir, $modSettings;

	// Hold Current Version
	$ezPortalVersion = '5.5.6';

	// Subs for EzPortal
	require_once($sourcedir . '/Subs-EzPortalMain2.php');

	// Setup a template layer for the ezPortal Admin Area's
	$context['template_layers'][] = 'ezportal';

	// Load EzPortal Settings
	LoadEzPortalSettings();

	// Be sure that the paths are setup
	if (empty($ezpSettings['ezp_url']))
		$ezpSettings['ezp_url'] = $boardurl . '/ezportal/';

	if (empty($ezpSettings['ezp_path']))
		$ezpSettings['ezp_path'] = $boarddir . '/ezportal/';

	// Load Language
	if (loadlanguage('EzPortal') == false)
		loadLanguage('EzPortal','english');

	// Load EzPortal template
	$context['ezportal21beta'] = false;

	// Load the main template file
    if (function_exists("set_tld_regex"))
    {
	   loadtemplate('EzPortal2.1');
	   $context['show_bbc'] = 1;
	   $context['ezportal21beta'] = true;
    }
    else
    {

    	if (empty($modSettings['ezp_responsivemode']))
        	loadtemplate('EzPortal2');
    	else
		{
			global $settings;
			if (isset($_REQUEST['action']) && $_REQUEST['action'] == 'admin')
				$context['html_headers'] .= '<link rel="stylesheet" type="text/css" href="' . $settings['default_theme_url'] . '/css/ezportal.css?fin20" />';
			loadtemplate('EzPortal2r');

		}

	}

	// Sub Action Array
	$subActions = array(
		'settings' => 'EzPortalSettings',
		'settings2' => 'EzPortalSaveSettings',
		'modules' => 'EzPortalModules',
		'blocks' => 'EzPortalBlocksMain',
		'blocks2' => 'EzPortalBlocksSave',
		'import' => 'EzPortalImportPortal',
		'import2' => 'EzPortalImportPortal2',
		'pagemanager' => 'EzPortalPageManager',
		'addpage' => 'EzPortalAddPage',
		'addpage2' => 'EzPortalAddPage2',
		'editpage' => 'EzPortalEditPage',
		'editpage2' => 'EzPortalEditPage2',
		'deletepage' => 'EzPortalDeletePage',
		'deletepage2' => 'EzPortalDeletePage2',
		'addblock' => 'EzPortalAddBlock',
		'addblock2' => 'EzPortalAddBlock2',
		'addblock3' => 'EzPortalAddBlock3',
		'editblock' => 'EzPortalEditBlock',
		'editblock2' => 'EzPortalEditBlock2',
		'deleteblock' => 'EzPortalDeleteBlock',
		'deleteblock2' => 'EzPortalDeleteBlock2',
		'statcheck' => 'EzPortalCollectStats',
		'page' => 'EzPortalViewPage',
		'editcolumn' => 'EzPortalEditColumn',
		'editcolumn2' => 'EzPortalEditColumn2',
		'downloadblock' => 'EzPortalDownloadBlocks',
		'installedblocks' => 'EzPortalInstalledBlocks',
		'importblock' => 'EzPortalImportBlock',
		'uninstallblock' => "EzPortalUninstallBlock",
		'blockstate' => 'EzPortalBlockState',
		'columnstate' => 'EzPortalColumnState',
		'visiblesettings' => 'EzPortalVisibleSettings',
		'visiblesettings2' => 'EzPortalVisibleSettings2',
		'addvisibleaction' => 'EzPortalAddVisibleAction',
		'addvisibleaction2' => 'EzPortalAddVisibleAction2',
		'deletevisibleaction' => 'EzPortalDeleteVisibleAction',
		'addshout' => 'EzPortalAddShout',
		'removeshout' => "EzPortalRemoveShout",
		'shouthistory' => 'EzPortalViewShoutHistory',
		'menuadd' => 'EzPortalMenuAdd',
		'menuadd2' => 'EzPortalMenuAdd2',
		'menuedit' => 'EzPortalMenuEdit',
		'menuedit2' => 'EzPortalMenuEdit2',
		'menudelete' => 'EzPortalMenuDelete',
		'menudelete2' => 'EzPortalMenuDelete2',
		'menuup' => 'EzPortalMenuUp',
		'menudown' => 'EzPortalMenuDown',
		'shoutframe' => "EzPortalShoutFrame",
        'copyright' => 'EzPortal_CopyrightRemoval',
        'deleteshouthistory' => 'EzPortalDeleteAllShoutHistory',
        'deleteshouthistory2' => 'EzPortalDeleteAllShoutHistory2',
	);

	if (isset($_REQUEST['sa']))
		$sa = $_GET['sa'];
	else
		$sa = '';

	if (!empty($subActions[$sa]))
		$subActions[$sa]();
	else
		EzPortalCredits();

}

function EzPortalModules()
{
	global $txt, $context, $sourcedir, $forum_version;

	require_once($sourcedir . '/Subs-Package.php');
	loadClassFile('Class-Package.php');

	// Check Permission
	isAllowedTo('ezportal_manage');

	EzPortalAdminTabs();

	// Set the subtemplate
	$context['sub_template']  = 'ezportal_modules';

	// Set the page title
	$context['page_title'] = $txt['ezp_modules'];

	// Download Modules
	$moduleList = new xmlArray(fetch_web_data('http://www.ezportal.com/modules.xml'), true);

	// Check valid module list
	if (!$moduleList->exists('module-list'))
		fatal_error($txt['ezp_err_invalid_module_list']);

	$context['module-list'] = array();

	$moduleNumber = 0;
    $moduleList = $moduleList->path('module-list[0]');
	$modules = $moduleList->set('module');

	$the_version = strtr($forum_version, array('SMF ' => ''));

	foreach ($modules as $i => $module)
	{

		$module = $module->to_array();
		// Add module to the list
		$context['module-list'][] = $module;

		$moduleNumber++;
	}


}

function EzPortalSettings()
{
	global $txt, $context;

	// Check Permission
	isAllowedTo('ezportal_manage');

	EzPortalAdminTabs();

	// Set the subtemplate
	$context['sub_template']  = 'ezportal_settings';

	// Set the page title
	$context['page_title'] = $txt['ezp_settings'];

}

function EzPortalSaveSettings()
{
	global $smcFunc;
	// Check Permission
	isAllowedTo('ezportal_manage');

	checkSession();

	$ezp_url = htmlspecialchars($_REQUEST['ezp_url'],ENT_QUOTES);
	$ezp_path = htmlspecialchars($_REQUEST['ezp_path'],ENT_QUOTES);
	$ezp_portal_enable = isset($_REQUEST['ezp_portal_enable']) ? 1 : 0;
	$ezp_allowstats = isset($_REQUEST['ezp_allowstats']) ? 1 : 0;
	$ezp_portal_homepage_title = $smcFunc['htmlspecialchars']($_REQUEST['ezp_portal_homepage_title'],ENT_QUOTES);

	$ezp_responsivemode = isset($_REQUEST['ezp_responsivemode']) ? 1 : 0;

	$ezp_hide_edit_delete = isset($_REQUEST['ezp_hide_edit_delete']) ? 1 : 0;
	$ezp_disable_tinymce_html = isset($_REQUEST['ezp_disable_tinymce_html']) ? 1 : 0;

	$ezp_shoutbox_enable = isset($_REQUEST['ezp_shoutbox_enable']) ? 1 : 0;
	$ezp_shoutbox_showdate = isset($_REQUEST['ezp_shoutbox_showdate']) ? 1 : 0;
	$ezp_shoutbox_archivehistory = isset($_REQUEST['ezp_shoutbox_archivehistory']) ? 1 : 0;

	$ezp_shoutbox_hidesays = isset($_REQUEST['ezp_shoutbox_hidesays']) ? 1 : 0;
	$ezp_shoutbox_hidedelete = isset($_REQUEST['ezp_shoutbox_hidedelete']) ? 1 : 0;
	$ezp_shoutbox_history_number = (int) $_REQUEST['ezp_shoutbox_history_number'];

	$ezp_shoutbox_refreshseconds = (int) $_REQUEST['ezp_shoutbox_refreshseconds'];
	$ezp_shoutbox_showsmilies = isset($_REQUEST['ezp_shoutbox_showsmilies']) ? 1 : 0;
	$ezp_shoutbox_showbbc = isset($_REQUEST['ezp_shoutbox_showbbc']) ? 1 : 0;

    $ezp_disableblocksinadmin = isset($_REQUEST['ezp_disableblocksinadmin']) ? 1 : 0;
    $ezp_disablemobiledevices = isset($_REQUEST['ezp_disablemobiledevices']) ? 1 : 0;

    $ezp_pages_seourls = isset($_REQUEST['ezp_pages_seourls']) ? 1 : 0;

    EzPortalCheck_htaccess();

    updateSettings(
    	array(
    	'ezp_responsivemode' => $ezp_responsivemode,
    	)

    	);


	UpdatePortalSettings(array(
	'ezp_url' => $ezp_url,
	'ezp_path' => $ezp_path,
	'ezp_portal_enable' => $ezp_portal_enable,
	'ezp_portal_homepage_title' => $ezp_portal_homepage_title,
	'ezp_allowstats' => $ezp_allowstats,

	'ezp_hide_edit_delete' => $ezp_hide_edit_delete,
	'ezp_disable_tinymce_html' => $ezp_disable_tinymce_html,

	'ezp_shoutbox_enable' => $ezp_shoutbox_enable,
	'ezp_shoutbox_showdate' => $ezp_shoutbox_showdate,
	'ezp_shoutbox_archivehistory' => $ezp_shoutbox_archivehistory,
	'ezp_shoutbox_hidesays' => $ezp_shoutbox_hidesays,
	'ezp_shoutbox_hidedelete' => $ezp_shoutbox_hidedelete,
	'ezp_shoutbox_history_number' => $ezp_shoutbox_history_number,

	'ezp_shoutbox_refreshseconds' => $ezp_shoutbox_refreshseconds,
	'ezp_shoutbox_showsmilies' => $ezp_shoutbox_showsmilies,
	'ezp_shoutbox_showbbc' => $ezp_shoutbox_showbbc,

    'ezp_disableblocksinadmin' => $ezp_disableblocksinadmin,
    'ezp_disablemobiledevices' => $ezp_disablemobiledevices,


    'ezp_pages_seourls' => $ezp_pages_seourls,

	)
	);

	// Fix cache issue for forum button
	updateSettings(array('settings_updated' => time()));

	redirectexit('action=admin;area=ezpsettings;sa=settings');

}

function EzPortalBlocksMain()
{
	global $txt, $context, $smcFunc;

	// Check Permission
	if (allowedTo('ezportal_blocks') == false)
		isAllowedTo('ezportal_manage');

	EzPortalBlockMangerTabs();

	// Set the subtemplate
	$context['sub_template']  = 'ezportal_blocks';

	// Set the page title
	$context['page_title'] = $txt['ezp_blocks'];

	// Get Columns
	$dbresult = $smcFunc['db_query']('', "
	SELECT
		id_column, column_title, active
	FROM {db_prefix}ezp_columns
	ORDER BY id_column ASC");
	$context['ezPortalAdminColumns'] = array();

	while($row = $smcFunc['db_fetch_assoc']($dbresult))
	{
		$blocks = array();

		// Get all the ezBlocks under these columns
		$dbresult2 = $smcFunc['db_query']('', "
		SELECT
			l.customtitle, l.id_layout, l.active, l.id_order
		FROM {db_prefix}ezp_block_layout AS l
		WHERE l.id_column = " . $row['id_column'] . "
		ORDER BY l.id_order ASC");
		while($row2 = $smcFunc['db_fetch_assoc']($dbresult2))
			$blocks[] = $row2;
		$smcFunc['db_free_result']($dbresult2);

		$row['blocks'] = $blocks;

		$context['ezPortalAdminColumns'][] = $row;

	}
	$smcFunc['db_free_result']($dbresult);

}

function EzPortalAddBlock()
{
	global $txt, $context, $smcFunc;

	// Check Permission
	if (allowedTo('ezportal_blocks') == false)
		isAllowedTo('ezportal_manage');

	EzPortalBlockMangerTabs();

	// Set the subtemplate
	$context['sub_template']  = 'ezportal_add_block';

	// Set the page title
	$context['page_title'] = $txt['ezp_addblock'];

	// Get Portal ezBlock List
	$dbresult = $smcFunc['db_query']('', "
	SELECT
		id_block, blocktitle
	FROM {db_prefix}ezp_blocks
	ORDER BY id_block ASC
	");
	$context['ezp_blocks'] = array();
	while ($row = $smcFunc['db_fetch_assoc']($dbresult))
    {
        if (isset($txt[$row['blocktitle']]['title']))
            $row['blocktitle'] = $txt[$row['blocktitle']]['title'];

		$context['ezp_blocks'][] = $row;
    }

	$smcFunc['db_free_result']($dbresult);

	if (isset($_REQUEST['column']))
		$column = (int) $_REQUEST['column'];
	else
		$column = 0;

	$context['ezportal_column'] = $column;

}

function EzPortalAddBlock2()
{
	global $txt, $context, $smcFunc, $sourcedir, $ezpSettings;

	// Check Permission
	if (allowedTo('ezportal_blocks') == false)
		isAllowedTo('ezportal_manage');

	EzPortalBlockMangerTabs();

	loadLanguage('Admin');

	// Set the subtemplate
	$context['sub_template']  = 'ezportal_add_block2';

	// Set the page title
	$context['page_title'] = $txt['ezp_addblock'];

	if (isset($_REQUEST['column']))
		$column = (int) $_REQUEST['column'];
	else
		$column = 0;

	$context['ezportal_column'] = $column;

	if (!isset($_REQUEST['blocktype']))
		return;

	// Get the blocktype
	$context['ezportal_blocktype'] = (int) $_REQUEST['blocktype'];

	// Get Columns
	$dbresult = $smcFunc['db_query']('', "
	SELECT
		id_column, column_title
	FROM {db_prefix}ezp_columns
	ORDER BY column_order ASC
	");
	$context['ezp_columns'] = array();
	while ($row = $smcFunc['db_fetch_assoc']($dbresult))
		$context['ezp_columns'][] = $row;
	$smcFunc['db_free_result']($dbresult);

	// Get Permissions
	$dbresult = $smcFunc['db_query']('', "
	SELECT
		ID_GROUP, group_name
	FROM {db_prefix}membergroups
	WHERE min_posts = -1 AND ID_GROUP <> 3 ORDER BY group_name");
	while ($row = $smcFunc['db_fetch_assoc']($dbresult))
	{
		$context['groups'][$row['ID_GROUP']] = array(
			'ID_GROUP' => $row['ID_GROUP'],
			'group_name' => $row['group_name'],
			);
	}
	$smcFunc['db_free_result']($dbresult);

	// Look up ezBlock information
	$dbresult = $smcFunc['db_query']('', "
	SELECT
		blockdata, data_editable, blocktype, blocktitle
	FROM {db_prefix}ezp_blocks
	WHERE id_block = " . $context['ezportal_blocktype']);
	$row = $smcFunc['db_fetch_assoc']($dbresult);

    if (isset($txt[$row['blocktitle']]['title']))
            $row['blocktitle'] = $txt[$row['blocktitle']]['title'];

	$context['ezp_block_data'] = $row;
	$smcFunc['db_free_result']($dbresult);

	$context['ezp_showtinymcetoggle'] = false;
	if ($context['ezp_block_data']['blocktype'] == 'HTML'  && empty($ezpSettings['ezp_disable_tinymce_html']))
	{
		SetupEditor();
		$context['ezp_showtinymcetoggle'] = true;
	}

	// Look up any parameters for this ezBlock
	$dbresult = $smcFunc['db_query']('', "
	SELECT
		title, defaultvalue, required, parameter_type, id_parameter
	FROM {db_prefix}ezp_block_parameters
	WHERE id_block = " . $context['ezportal_blocktype'] . " ORDER BY id_order ASC"
	);
	$context['ezp_block_parameters'] = array();

	$editorCreated = false;

	while ($row = $smcFunc['db_fetch_assoc']($dbresult))
	{
	   if (isset($txt[$context['ezp_block_data']['blocktitle']]['param'][$row['title']]))
            $row['title'] = $txt[$context['ezp_block_data']['blocktitle']]['param'][$row['title']];

		if (empty($row['parameter_type']))
			$row['parameter_type'] = "";

		$context['ezp_block_parameters'][] = $row;

		if ($row['parameter_type'] == 'html')
		{
			if ($editorCreated == false)
			{
				// Create the text editor
				SetupEditor();
				$editorCreated = true;
			}

		}

		if ($row['parameter_type'] == 'boardselect' || $row['parameter_type'] == 'multiboardselect')
		{
				$context['ezportal_boards'] = array('');
				$request = $smcFunc['db_query']('', "
				SELECT
					b.ID_BOARD, b.name AS bName, c.name AS cName
				FROM {db_prefix}boards AS b, {db_prefix}categories AS c
				WHERE b.ID_CAT = c.ID_CAT ORDER BY c.cat_order, b.board_order");
				while ($row2 = $smcFunc['db_fetch_assoc']($request))
					$context['ezportal_boards'][$row2['ID_BOARD']] = $row2['cName'] . ' - ' . $row2['bName'];
				$smcFunc['db_free_result']($request);

		}
		if ($row['parameter_type'] == 'select')
		{
			$context['ezp_select_' . $row['id_parameter']] = array();
			$request = $smcFunc['db_query']('', "
				SELECT
					selectvalue,selecttext
				FROM {db_prefix}ezp_paramaters_select
				WHERE id_parameter = " . $row['id_parameter']. " ORDER BY id_select ASC");
				while ($row2 = $smcFunc['db_fetch_assoc']($request))
					$context['ezp_select_' . $row['id_parameter']][$row2['selectvalue']] = $row2['selecttext'];
				$smcFunc['db_free_result']($request);
		}

		if ($row['parameter_type'] == 'bbc')
		{
			/// Used for the editor
			require_once($sourcedir . '/Subs-Editor.php');
			// Now create the editor.
			$editorOptions = array(
				'id' => 'bbcfield' . $row['id_parameter'],
				'value' => '',
				'width' => '90%',
				'form' => 'frmaddblock',
				'labels' => array(
					'post_button' => ''
				),
			);


			create_control_richedit($editorOptions);
			$context['post_box_name'] = $editorOptions['id'];

		}



	}
	$smcFunc['db_free_result']($dbresult);


	// Load the EzBlock icons
	$dbresult = $smcFunc['db_query']('', "
	SELECT
		id_icon, icon
	FROM {db_prefix}ezp_icons
	ORDER BY icon ASC
	");
	$context['ezp_icons'] = array();
	while ($row = $smcFunc['db_fetch_assoc']($dbresult))
		$context['ezp_icons'][] = $row;
	$smcFunc['db_free_result']($dbresult);


}

function EzPortalAddBlock3()
{
	global $txt, $smcFunc, $sourcedir, $context;

	checkSession();

	// Check Permission
	if (allowedTo('ezportal_blocks') == false)
		isAllowedTo('ezportal_manage');

	$column = (int) $_REQUEST['column'];

	if (empty($column))
		fatal_lang_error('ezp_err_no_column_selected', false);

	$blocktype = (int) $_REQUEST['blocktype'];

	if (empty($blocktype))
		fatal_lang_error('ezp_err_no_block_selected', false);


	// Get ezBlock Data
	$dbresult = $smcFunc['db_query']('', "
	SELECT
		b.blockdata bdata, b.data_editable
	FROM {db_prefix}ezp_blocks AS b
	WHERE id_block = $blocktype  LIMIT 1
	");
	$blockrow = $smcFunc['db_fetch_assoc']($dbresult);
	$smcFunc['db_free_result']($dbresult);

	$blocktitle = $smcFunc['htmlspecialchars']($_REQUEST['blocktitle'],ENT_QUOTES);
	$icon = (int) $_REQUEST['icon'];

	$can_collapse = isset($_REQUEST['can_collapse']) ? 1 : 0;
	$hidetitlebar = isset($_REQUEST['hidetitlebar']) ? 1 : 0;
	$hidemobile = isset($_REQUEST['hidemobile']) ? 1 : 0;
	$showonlymobile = isset($_REQUEST['showonlymobile']) ? 1 : 0;

	$block_header_class = $smcFunc['htmlspecialchars']($_REQUEST['block_header_class'],ENT_QUOTES);
	$block_body_class = $smcFunc['htmlspecialchars']($_REQUEST['block_body_class'],ENT_QUOTES);

	$blockdata = '';
	if (isset($_REQUEST['blockdata']))
		$blockdata = htmlentities($_REQUEST['blockdata'],ENT_QUOTES);

	if ($blockrow['data_editable'] == 0)
		$blockdata = $blockrow['bdata'];


	if (isset($_REQUEST['parameter']))
		$parameters = $_REQUEST['parameter'];

	// Validate all the parameters if they exist
	$dbresult = $smcFunc['db_query']('', "
	SELECT
		defaultvalue, required, parameter_type, id_parameter, title
	FROM {db_prefix}ezp_block_parameters
	WHERE id_block = " . $blocktype
	);
	while ($row = $smcFunc['db_fetch_assoc']($dbresult))
	{
		// Checked passed data type
		if ($row['parameter_type'] == 'int')
			$parameters[$row['id_parameter']] = (int) $parameters[$row['id_parameter']];
		if ($row['parameter_type'] == 'float')
			$parameters[$row['id_parameter']] = (float) $parameters[$row['id_parameter']];
		if ($row['parameter_type'] == 'multiboardselect')
		{
			if (is_array($parameters[$row['id_parameter']]))
				$parameters[$row['id_parameter']] = implode(',',$parameters[$row['id_parameter']]);

		}

		if ($row['parameter_type'] == 'bbc')
		{

			if (!empty($_REQUEST['bbcfield' . $row['id_parameter'] . '_mode']) && isset($_REQUEST['bbcfield' . $row['id_parameter']]))
			{
				require_once($sourcedir . '/Subs-Editor.php');

				$_REQUEST['bbcfield' . $row['id_parameter']] = html_to_bbc($_REQUEST['bbcfield' . $row['id_parameter']]);

				// We need to unhtml it now as it gets done shortly.
				$_REQUEST['bbcfield' . $row['id_parameter']] = un_htmlspecialchars($_REQUEST['bbcfield' . $row['id_parameter']]);

			}

	$_REQUEST['bbcfield' . $row['id_parameter']] = $smcFunc['htmlspecialchars']($_REQUEST['bbcfield' . $row['id_parameter']], ENT_QUOTES);

			$parameters[$row['id_parameter']] = $_REQUEST['bbcfield' . $row['id_parameter']];

		}

		if ($row['parameter_type'] == 'checkbox')
		{
			$parameters[$row['id_parameter']] = isset($parameters[$row['id_parameter']]) ? 1 : 0;
		}


		// Required paramater
		if ($row['required'] == 1)
		{
			 if ($parameters[$row['id_parameter']] == '')
			 {
			 	// Throw an error
			 	fatal_error($txt['ezp_err_no_parameter_value'] . $row['title'],false);
			 }
		}
	}
	$smcFunc['db_free_result']($dbresult);

	// Get Permissions
	$permissionsArray = array();

	if (isset($_REQUEST['groups']))
	{
		foreach ($_REQUEST['groups'] as $rgroup)
			$permissionsArray[] = (int) $rgroup;
	}
	$finalPermissions = implode(",",$permissionsArray);

	// Get Managers for this ezBlock if any
	$managersArray = array();

	if (isset($_REQUEST['managers']))
	{
		foreach ($_REQUEST['managers'] as $rgroup)
			$managersArray[] = (int) $rgroup;
	}
	$finalManagers = implode(",",$managersArray);

	// Insert the ezBlock
	$smcFunc['db_query']('', "INSERT INTO {db_prefix}ezp_block_layout
			(id_column, id_block, id_order, customtitle, permissions, blockmanagers, can_collapse, active, blockdata, id_icon, hidetitlebar, hidemobile,showonlymobile,block_header_class,block_body_class)
			VALUES ($column, $blocktype,1000,'$blocktitle','$finalPermissions','$finalManagers',$can_collapse, 1,'$blockdata','$icon', $hidetitlebar, $hidemobile,$showonlymobile,'$block_header_class','$block_body_class')");

	$layoutID = $smcFunc['db_insert_id']('{db_prefix}ezp_block_layout', 'id_layout');

	// Reorder ezBlocks
	ReOrderBlocksbyColumn($column);

	cache_put_data('ezportal_column_' . $column, null, 60);

	// Insert Parameters
	if (isset($parameters))
    {
		foreach ($parameters  as $key => $param)
		{
			// Make it db safe
			$paramData = $smcFunc['db_escape_string']($param);
			// Insert the parameter
			$smcFunc['db_query']('', "INSERT INTO {db_prefix}ezp_block_parameters_values
				(id_layout, id_parameter, data)
				VALUES ($layoutID,$key,'$paramData')");

		}

        //cache_put_data('ezportal_layoutparm_' . $layoutID, $parameters, 120);
        cache_put_data('ezportal_layoutparm_' . $layoutID,  null, 120);
    }


	// Enable the shoutbox if we are adding a shoutbox block
	if ($blockrow['bdata']  == 'EzBlockShoutBoxBlock')
	{
			UpdatePortalSettings(array(
			'ezp_shoutbox_enable' => 1,
			)
			);
	}

	// Redirect to the visibile options for the ezBlock
	redirectexit('action=admin;area=ezpblocks;sa=visiblesettings;block=' .$layoutID);

}

function EzPortalEditBlock()
{
	global $txt, $context, $smcFunc, $user_info, $sourcedir, $ezpSettings;

	$block = (int) $_REQUEST['block'];

	// Get ezBlock Data
	$dbresult = $smcFunc['db_query']('', "
	SELECT
		l.id_column, l.id_block, l.id_layout, l.customtitle, l.permissions, l.can_collapse,
		l.blockmanagers, l.blockdata, b.data_editable, b.blocktitle, l.active, 
		l.visibileactions, l.visibileboards, l.visibileareascustom, l.id_icon, b.blocktype,
		l.hidetitlebar, l.hidemobile, l.showonlymobile, l.block_header_class, l.block_body_class 
	FROM {db_prefix}ezp_block_layout AS l
		INNER JOIN {db_prefix}ezp_blocks AS b ON (l.id_block = b.id_block)
	WHERE l.id_layout = $block LIMIT 1
	");
	$row = $smcFunc['db_fetch_assoc']($dbresult);
    if (isset($txt[$row['blocktitle']]['title']))
        $row['blocktitle'] = $txt[$row['blocktitle']]['title'];

	$context['ezp_block_info'] = $row;
	$smcFunc['db_free_result']($dbresult);

	$context['ezp_showtinymcetoggle'] = false;
	if ($context['ezp_block_info']['blocktype'] == 'HTML'  && empty($ezpSettings['ezp_disable_tinymce_html']))
	{
		SetupEditor();
		$context['ezp_showtinymcetoggle'] = true;

	}


	$canManageBlocks = false;

	// Check Permission
	if (allowedTo('ezportal_blocks') || allowedTo('manage_ezportal'))
		$canManageBlocks = true;

	if ($canManageBlocks == false)
	{
		$blockManagers = explode(',',$context['ezp_block_info']['blockmanagers']);
		$canManageBlocks = count(array_intersect($user_info['groups'], $blockManagers)) == 0 ? false : true;
	}

	if ($canManageBlocks == false)
		fatal_lang_error('ezp_err_no_block_manage_permission',false);

	// If it is a menu ezBlock load the data
	if ($context['ezp_block_info']['blocktitle'] == 'Menu ezBlock')
	{
			$dbresult = $smcFunc['db_query']('', "
		SELECT
			m.id_menu, m.id_order, m.linkurl, m.title, m.enabled
		FROM {db_prefix}ezp_menu as m
			INNER JOIN {db_prefix}ezp_block_layout AS l ON (l.id_layout = m.id_layout)
		WHERE l.id_layout = $block
		ORDER BY id_order ASC
		");
		$context['ezp_menu_block_items'] = array();
		while($row = $smcFunc['db_fetch_assoc']($dbresult))
		{
			$context['ezp_menu_block_items'][] = $row;
		}
		$smcFunc['db_free_result']($dbresult);
	}

	EzPortalBlockMangerTabs();

	// Set the subtemplate
	$context['sub_template']  = 'ezportal_edit_block';

	// Set the page title
	$context['page_title'] = $txt['ezp_editblock'];

	// Get Columns
	$dbresult = $smcFunc['db_query']('', "
	SELECT
		id_column, column_title
	FROM {db_prefix}ezp_columns
	ORDER BY column_order ASC
	");
	$context['ezp_columns'] = array();
	while ($row = $smcFunc['db_fetch_assoc']($dbresult))
		$context['ezp_columns'][] = $row;
	$smcFunc['db_free_result']($dbresult);

	// Get Permissions
	$dbresult = $smcFunc['db_query']('', "
	SELECT
		ID_GROUP, group_name
	FROM {db_prefix}membergroups
	WHERE min_posts = -1 AND ID_GROUP <> 3 ORDER BY group_name");
	while ($row = $smcFunc['db_fetch_assoc']($dbresult))
	{
		$context['groups'][$row['ID_GROUP']] = array(
			'ID_GROUP' => $row['ID_GROUP'],
			'group_name' => $row['group_name'],
			);
	}
	$smcFunc['db_free_result']($dbresult);

	// Look up any parameters for this ezBlock
	$dbresult = $smcFunc['db_query']('', "
	SELECT
		p.title, p.defaultvalue, p.required, p.parameter_type, p.id_parameter, v.data
	FROM {db_prefix}ezp_block_parameters AS p
		LEFT JOIN {db_prefix}ezp_block_parameters_values AS v ON (p.id_parameter = v.id_parameter AND v.id_layout = $block)
	WHERE p.id_block = " . $context['ezp_block_info']['id_block']  . " ORDER BY p.id_order ASC"
	);
	$context['ezp_block_parameters'] = array();
	$editorCreated = false;
	while ($row = $smcFunc['db_fetch_assoc']($dbresult))
	{
 	   if (isset($txt[$context['ezp_block_info']['blocktitle']]['param'][$row['title']]))
            $row['title'] = $txt[$context['ezp_block_info']['blocktitle']]['param'][$row['title']];

 	   if (empty($row['parameter_type']))
			$row['parameter_type'] = "";

		$context['ezp_block_parameters'][] = $row;



		if ($row['parameter_type'] == 'html')
		{
			if ($editorCreated == false)
			{
				// Create the text editor
				SetupEditor();
				$editorCreated = true;
			}

		}

	if ($row['parameter_type'] == 'boardselect' || $row['parameter_type'] == 'multiboardselect')
		{
			$context['ezportal_boards'] = array();
			$request = $smcFunc['db_query']('', "
			SELECT
				b.ID_BOARD, b.name AS bName, c.name AS cName
			FROM {db_prefix}boards AS b, {db_prefix}categories AS c
			WHERE b.ID_CAT = c.ID_CAT ORDER BY c.cat_order, b.board_order");
			while ($row2 = $smcFunc['db_fetch_assoc']($request))
				$context['ezportal_boards'][$row2['ID_BOARD']] = $row2['cName'] . ' - ' . $row2['bName'];
			$smcFunc['db_free_result']($request);

		}

		if ($row['parameter_type'] == 'select')
		{
			$context['ezp_select_' . $row['id_parameter']] = array();
			$request = $smcFunc['db_query']('', "
				SELECT
					selectvalue,selecttext
				FROM {db_prefix}ezp_paramaters_select
				WHERE id_parameter = " . $row['id_parameter'] . " ORDER BY id_select ASC");
				while ($row2 = $smcFunc['db_fetch_assoc']($request))
					$context['ezp_select_' . $row['id_parameter']][$row2['selectvalue']] = $row2['selecttext'];
				$smcFunc['db_free_result']($request);
		}

		if ($row['parameter_type'] == 'bbc')
		{
			/// Used for the editor
			require_once($sourcedir . '/Subs-Editor.php');

			if ($context['ezportal21beta'] == true)
			 $row['data'] = str_replace(array('"', '<', '>', '&nbsp;'), array('&quot;', '&lt;', '&gt;', ' '),  $row['data']);

			// Now create the editor.
			$editorOptions = array(
				'id' => 'bbcfield' . $row['id_parameter'],
				'value' => $row['data'],
				'width' => '90%',
				'form' => 'frmeditblock',
				'labels' => array(
					'post_button' => ''
				),
			);


			create_control_richedit($editorOptions);
			$context['post_box_name'] = $editorOptions['id'];

		}


	}
	$smcFunc['db_free_result']($dbresult);



	// Load the EzBlock icons
	$dbresult = $smcFunc['db_query']('', "
	SELECT
		id_icon, icon
	FROM {db_prefix}ezp_icons
	ORDER BY icon ASC
	");
	$context['ezp_icons'] = array();
	while ($row = $smcFunc['db_fetch_assoc']($dbresult))
		$context['ezp_icons'][] = $row;
	$smcFunc['db_free_result']($dbresult);

}

function EzPortalEditBlock2()
{
	global $txt, $smcFunc, $user_info, $sourcedir, $context;

	checkSession();

	$block = (int) $_REQUEST['block'];
	if (empty($block))
		fatal_lang_error('ezp_err_no_block_selected',false);

	$column = (int) $_REQUEST['column'];

	if (empty($column))
		fatal_lang_error('ezp_err_no_column_selected', false);

	// Get ezBlock Data
	$dbresult = $smcFunc['db_query']('', "
	SELECT
		l.id_block, l.blockmanagers, l.id_column, l.id_order,b.blockdata bdata, b.data_editable
	FROM {db_prefix}ezp_block_layout AS l
		INNER JOIN {db_prefix}ezp_blocks AS b ON (l.id_block = b.id_block)
	WHERE l.id_layout = $block LIMIT 1
	");
	$row = $smcFunc['db_fetch_assoc']($dbresult);
	$context['ezp_block_info'] = $row;
	$smcFunc['db_free_result']($dbresult);

	$canManageBlocks = false;

	// Check Permission
	if (allowedTo('ezportal_blocks') || allowedTo('manage_ezportal'))
		$canManageBlocks = true;

	if ($canManageBlocks == false)
	{
		$blockManagers = explode(',',$context['ezp_block_info']['blockmanagers']);
		$canManageBlocks = count(array_intersect($user_info['groups'], $blockManagers)) == 0 ? false : true;
	}

	if ($canManageBlocks == false)
		fatal_lang_error('ezp_err_no_block_manage_permission',false);

	$blocktitle = $smcFunc['htmlspecialchars']($_REQUEST['blocktitle'],ENT_QUOTES);
	$active = (int) $_REQUEST['active'];
	$icon = (int) $_REQUEST['icon'];

	$can_collapse = isset($_REQUEST['can_collapse']) ? 1 : 0;
	$hidetitlebar = isset($_REQUEST['hidetitlebar']) ? 1 : 0;
	$hidemobile = isset($_REQUEST['hidemobile']) ? 1 : 0;
	$showonlymobile = isset($_REQUEST['showonlymobile']) ? 1 : 0;
	$block_header_class = $smcFunc['htmlspecialchars']($_REQUEST['block_header_class'],ENT_QUOTES);
	$block_body_class = $smcFunc['htmlspecialchars']($_REQUEST['block_body_class'],ENT_QUOTES);

	$blockdata = '';
	if (isset($_REQUEST['blockdata']))
		$blockdata = htmlentities($_REQUEST['blockdata'],ENT_QUOTES);

	// If it is not editable set it to the default data
	if ($row['data_editable'] == 0)
		$blockdata = $row['bdata'];

	// Validate all the parameters if they exist
	if (isset($_REQUEST['parameter']))
		$parameters = $_REQUEST['parameter'];

	$dbresult = $smcFunc['db_query']('', "
	SELECT
		defaultvalue, required, parameter_type, id_parameter,title
	FROM {db_prefix}ezp_block_parameters
	WHERE id_block = " . $context['ezp_block_info']['id_block']
	);
	while ($row = $smcFunc['db_fetch_assoc']($dbresult))
	{
		// Checked passed data type
		if ($row['parameter_type'] == 'int')
			$parameters[$row['id_parameter']] = (int) $parameters[$row['id_parameter']];
		if ($row['parameter_type'] == 'float')
			$parameters[$row['id_parameter']] = (float) $parameters[$row['id_parameter']];
		if ($row['parameter_type'] == 'multiboardselect')
		{

			if (!empty($parameters[$row['id_parameter']]))
				$parameters[$row['id_parameter']] = implode(',',$parameters[$row['id_parameter']]);

		}


		if ($row['parameter_type'] == 'bbc')
		{


			if (!empty($_REQUEST['bbcfield' . $row['id_parameter'] . '_mode']) && isset($_REQUEST['bbcfield' . $row['id_parameter']]))
			{
				require_once($sourcedir . '/Subs-Editor.php');

				$_REQUEST['bbcfield' . $row['id_parameter']] = html_to_bbc($_REQUEST['bbcfield' . $row['id_parameter']]);

				// We need to unhtml it now as it gets done shortly.
				$_REQUEST['bbcfield' . $row['id_parameter']] = un_htmlspecialchars($_REQUEST['bbcfield' . $row['id_parameter']]);

			}


				$_REQUEST['bbcfield' . $row['id_parameter']] = $smcFunc['htmlspecialchars']($_REQUEST['bbcfield' . $row['id_parameter']], ENT_QUOTES);


			$parameters[$row['id_parameter']] = $_REQUEST['bbcfield' . $row['id_parameter']];

		}

		if ($row['parameter_type'] == 'checkbox')
		{
			$parameters[$row['id_parameter']] = isset($parameters[$row['id_parameter']]) ? 1 : 0;
		}


		// Required parameter
		if ($row['required'] == 1)
		{
			 if ($parameters[$row['id_parameter']] == '')
			 {
			 	// Throw an error
			 	fatal_error($txt['ezp_err_no_parameter_value'] . $row['title'],false);
			 }
		}
	}
	$smcFunc['db_free_result']($dbresult);

	// Get Permissions
	$permissionsArray = array();

	if (isset($_REQUEST['groups']))
	{
		foreach ($_REQUEST['groups'] as $rgroup)
			$permissionsArray[] = (int) $rgroup;
	}
	$finalPermissions = implode(",",$permissionsArray);

	// Get Managers for this ezBlock if any
	$managersArray = array();

	if (isset($_REQUEST['managers']))
	{
		foreach ($_REQUEST['managers'] as $rgroup)
			$managersArray[] = (int) $rgroup;
	}
	$finalManagers = implode(",",$managersArray);

	// Update the ezBlock
	$smcFunc['db_query']('', "UPDATE {db_prefix}ezp_block_layout
	      SET id_column = $column, customtitle = '$blocktitle', permissions = '$finalPermissions', blockmanagers = '$finalManagers',
	      can_collapse = $can_collapse, blockdata = '$blockdata', id_icon = '$icon', hidetitlebar = '$hidetitlebar', hidemobile = '$hidemobile', showonlymobile = '$showonlymobile',
	      block_header_class = '$block_header_class', block_body_class = '$block_body_class', active = '$active'  
	      WHERE id_layout = '$block'
	      ");


	// Update EzBlock Parameters.
	if (isset($parameters))
		foreach ($parameters  as $key => $param)
		{
			// Make it db safe
			$paramData = $smcFunc['db_escape_string']($param);



			$smcFunc['db_query']('', "UPDATE {db_prefix}ezp_block_parameters_values
				SET data = '$paramData' WHERE id_layout = $block AND id_parameter = $key
				");

			$affectedRows =$smcFunc['db_affected_rows']();
			if ($affectedRows == 0)
			{
				$smcFunc['db_query']('', "INSERT INTO {db_prefix}ezp_block_parameters_values
				(id_layout, id_parameter, data)
				VALUES ($block,$key,'$paramData')");
			}


		}

 	if (isset($parameters))
        cache_put_data('ezportal_layoutparm_' . $block,  null, 120);

	if ($context['ezp_block_info']['id_column'] != $column)
	{
		cache_put_data('ezportal_column_' . $context['ezp_block_info']['id_column'], null, 60);

		$smcFunc['db_query']('', "UPDATE {db_prefix}ezp_block_layout
	      SET id_order = 1000
	      WHERE id_layout = '$block'
	      ");

		ReOrderBlocksbyColumn($context['ezp_block_info']['id_column']);
	}

	cache_put_data('ezportal_column_' . $column, null, 60);

	CleanUpDuplicateValues($block);

	// Reorder ezBlocks
	ReOrderBlocksbyColumn($column);



	// Redirect to the ezBlock Manager
	redirectexit('action=admin;area=ezpblocks;sa=blocks');

}

function EzPortalDeleteBlock()
{
	global $txt, $context, $smcFunc;

	// Check Permission
	isAllowedTo('ezportal_blocks');

	EzPortalBlockMangerTabs();

	// Set the subtemplate
	$context['sub_template']  = 'ezportal_delete_block';

	// Set the page title
	$context['page_title'] = $txt['ezp_deleteblock'];

	// Get the block id that they are trying to delete
	$context['ezp_block_layout_id'] = (int) $_REQUEST['block'];

	// Get some information on the block they are deleting.
	$dbresult = $smcFunc['db_query']('', "
	SELECT
		customtitle
	FROM {db_prefix}ezp_block_layout
	WHERE id_layout = " . $context['ezp_block_layout_id'] . " LIMIT 1");
	$row = $smcFunc['db_fetch_assoc']($dbresult);
	$context['ezp_block_layout_title'] = $row['customtitle'];
	$smcFunc['db_free_result']($dbresult);

}

function EzPortalDeleteBlock2()
{
	global $smcFunc;

	// Check Permission
	isAllowedTo('ezportal_blocks');

	checkSession();

	$blockid = (int) $_REQUEST['blockid'];

	$result = $smcFunc['db_query']('', "
	SELECT
		id_column
	FROM {db_prefix}ezp_block_layout
	WHERE id_layout = " . $blockid . " LIMIT 1");
	$row = $smcFunc['db_fetch_assoc']($result);
	$column = $row['id_column'];
	$smcFunc['db_free_result']($result);
	cache_put_data('ezportal_column_' . $column, null, 60);

	// Delete the ezBlock
	$smcFunc['db_query']('', "
	DELETE FROM {db_prefix}ezp_block_layout
	WHERE id_layout = " . $blockid . " LIMIT 1");

	// Delete ezBlock Parameters
	$smcFunc['db_query']('', "
	DELETE FROM {db_prefix}ezp_block_parameters_values
	WHERE id_layout = " . $blockid);



	cache_put_data('ezportal_columns', null, 60);

	// Redirect to the Block Manager
	redirectexit('action=admin;area=ezpblocks;sa=blocks');

}

function EzPortalPageManager()
{
	global $txt, $context, $smcFunc, $scripturl;

	// Check Permission
	if (allowedTo('ezportal_page') == false)
		isAllowedTo('ezportal_manage');

	// Do Tabs
	EzPortalPageManagerTabs();

	$context['start'] = $_REQUEST['start'];

	// Get Total Pages
	$dbresult = $smcFunc['db_query']('', "
	SELECT
		COUNT(*) AS total
	FROM {db_prefix}ezp_page");
	$rowTotal = $smcFunc['db_fetch_assoc']($dbresult);
	$total = $rowTotal['total'];
	$smcFunc['db_free_result']($dbresult);

	$dbresult = $smcFunc['db_query']('', "
	SELECT
		id_page,date, title,views
	FROM {db_prefix}ezp_page
	ORDER BY id_page DESC
	LIMIT $context[start], 10");
	$context['ezp_pages'] = array();
	while ($row = $smcFunc['db_fetch_assoc']($dbresult))
		$context['ezp_pages'][] = $row;
	$smcFunc['db_free_result']($dbresult);

	$context['page_index'] = constructPageIndex($scripturl . '?action=ezportal;sa=pagemanager', $_REQUEST['start'], $total, 10);

	// Set the subtemplate
	$context['sub_template']  = 'ezportal_pagemanager';

	// Set the page title
	$context['page_title'] = $txt['ezp_pagemanager'];

}

function EzPortalAddPage()
{
	global $txt, $context, $smcFunc;

	// Check Permission
	if (allowedTo('ezportal_page') == false)
		isAllowedTo('ezportal_manage');

	// Do Tabs
	EzPortalPageManagerTabs();

	// Set the subtemplate
	$context['sub_template']  = 'ezportal_addpage';

	// Set the page title
	$context['page_title'] = $txt['ezp_addpage'];

	loadLanguage('Admin');

	// Load the membergroups
	$dbresult = $smcFunc['db_query']('', "
	SELECT
		ID_GROUP, group_name
	FROM {db_prefix}membergroups
	WHERE min_posts = -1 AND ID_GROUP <> 3 ORDER BY group_name");
	$context['groups'] = array();
	while ($row = $smcFunc['db_fetch_assoc']($dbresult))
	{
		$context['groups'][$row['ID_GROUP']] = array(
			'ID_GROUP' => $row['ID_GROUP'],
			'group_name' => $row['group_name'],
			);
	}
	$smcFunc['db_free_result']($dbresult);

	$context['ezp_page_bbc'] = 0;

	if (isset($_REQUEST['btnsubmit']))
	{
		if ($_REQUEST['btnsubmit'] == $txt['ezp_bbc_addpage'])
			$context['ezp_page_bbc'] = 1;

	}


	// Setup Editor
	if ($context['ezp_page_bbc'] == 0)
		SetupEditor();
	else
	{
		global $sourcedir;
			/// Used for the editor
			require_once($sourcedir . '/Subs-Editor.php');
			// Now create the editor.
			$editorOptions = array(
				'id' => 'pagecontent',
				'value' => '',
				'width' => '90%',
				'form' => 'frmaddpage',
				'labels' => array(
					'post_button' => ''
				),
			);


			create_control_richedit($editorOptions);
			$context['post_box_name'] = $editorOptions['id'];

	}

}

function EzPortalAddPage2()
{
	global $smcFunc;

	// Check Permission
	if (allowedTo('ezportal_page') == false)
		isAllowedTo('ezportal_manage');

	checkSession();

	$pagetitle = $smcFunc['htmlspecialchars']($_REQUEST['pagetitle'],ENT_QUOTES);

	$bbc = 0;

	if (empty($_REQUEST['bbc']))
	{
		$pagecontent = htmlentities($_REQUEST['pagecontent'], ENT_QUOTES);

	}
	else
	{

		$bbc = 1;

		if (!empty($_REQUEST['pagecontent_mode']) && isset($_REQUEST['pagecontent']))
		{
			global $sourcedir;
			require_once($sourcedir . '/Subs-Editor.php');

			$_REQUEST['pagecontent'] = html_to_bbc($_REQUEST['pagecontent']);

			// We need to unhtml it now as it gets done shortly.
			$pagecontent = un_htmlspecialchars($_REQUEST['pagecontent']);

		}
		else
		{

			$pagecontent = $smcFunc['htmlspecialchars']($_REQUEST['pagecontent'],ENT_QUOTES);

		}

	}

	$metatags = htmlentities($_REQUEST['metatags'],ENT_QUOTES);
	$icon = $smcFunc['htmlspecialchars']($_REQUEST['icon'],ENT_QUOTES);

	if (trim($pagetitle) == '')
		fatal_lang_error('ezp_err_no_page_title',false);

	if (trim($pagecontent) == '')
		fatal_lang_error('ezp_err_no_page_cotent',false);

	// Get Permissions
	$permissionsArray = array();

	if (isset($_REQUEST['groups']))
	{
		foreach ($_REQUEST['groups'] as $rgroup)
			$permissionsArray[] = (int) $rgroup;
	}

	$finalPermissions = implode(",",$permissionsArray);


	$menutitle = $smcFunc['htmlspecialchars']($_REQUEST['menutitle'],ENT_QUOTES);
	$showinmenu = isset($_REQUEST['showinmenu']) ? 1 : 0;

	$addDate = time();
	$smcFunc['db_query']('', "INSERT INTO {db_prefix}ezp_page
			(date, title, content, permissions, is_html,metatags,menutitle,showinmenu,icon,bbc)
			VALUES ($addDate, '$pagetitle', '$pagecontent', '$finalPermissions', 1,'$metatags','$menutitle','$showinmenu','$icon','$bbc')");


	EzPortalUpdatePageCount();

	// Redirect to the Page Manager
	redirectexit('action=admin;area=ezppagemanager;sa=pagemanager');
}


function EzPortalEditPage()
{
	global $txt, $context, $smcFunc;

	// Check Permission
	if (allowedTo('ezportal_page') == false)
		isAllowedTo('ezportal_manage');

	if (empty($_REQUEST['p']))
		fatal_lang_error('ezp_err_no_page_selected', false);

	// Do Tabs
	EzPortalPageManagerTabs();

	// Store the Page ID
	$pageID = (int) $_REQUEST['p'];

	// Set the subtemplate
	$context['sub_template']  = 'ezportal_editpage';

	// Set the page title
	$context['page_title'] = $txt['ezp_editpage'];

	loadLanguage('Admin');
	// Load the membergroups
	$dbresult = $smcFunc['db_query']('', "
	SELECT
		ID_GROUP, group_name
	FROM {db_prefix}membergroups
	WHERE min_posts = -1 AND ID_GROUP <> 3 ORDER BY group_name");
	$context['groups'] = array();
	while ($row = $smcFunc['db_fetch_assoc']($dbresult))
	{
		$context['groups'][$row['ID_GROUP']] = array(
			'ID_GROUP' => $row['ID_GROUP'],
			'group_name' => $row['group_name'],
			);
	}
	$smcFunc['db_free_result']($dbresult);



	// Look up the page and get it ready for the template
	$dbresult = $smcFunc['db_query']('', "
	SELECT
		id_page, title, content, permissions, metatags, menutitle, showinmenu, icon, bbc
	FROM {db_prefix}ezp_page
	WHERE id_page = $pageID LIMIT 1");
	$row = $smcFunc['db_fetch_assoc']($dbresult);
	$context['ezp_editpage_data'] = $row;
	$context['ezp_editpage_data']['content'] = html_entity_decode($row['content']);

	$smcFunc['db_free_result']($dbresult);
	$context['ezp_page_bbc'] = $row['bbc'];

	// Setup Editor
	if ($context['ezp_page_bbc'] == 0)
		SetupEditor();
	else
	{
		global $sourcedir;
			/// Used for the editor
			require_once($sourcedir . '/Subs-Editor.php');
			// Now create the editor.
			$editorOptions = array(
				'id' => 'pagecontent',
				'value' => $row['content'],
				'width' => '90%',
				'form' => 'frmeditpage',
				'labels' => array(
					'post_button' => ''
				),
			);


			create_control_richedit($editorOptions);
			$context['post_box_name'] = $editorOptions['id'];

	}


}

function EzPortalEditPage2()
{
	global $smcFunc;

	checkSession();

	// Check Permission
	if (allowedTo('ezportal_page') == false)
		isAllowedTo('ezportal_manage');

	if (empty($_REQUEST['p']))
		fatal_lang_error('ezp_err_no_page_selected', false);

	// Store the Page ID
	$pageID = (int) $_REQUEST['p'];

	$dbresult = $smcFunc['db_query']('', "
	SELECT
		id_page, title, content, permissions, metatags, menutitle, showinmenu, icon, bbc
	FROM {db_prefix}ezp_page
	WHERE id_page = $pageID LIMIT 1");
	$row = $smcFunc['db_fetch_assoc']($dbresult);


	$pagetitle = $smcFunc['htmlspecialchars']($_REQUEST['pagetitle'],ENT_QUOTES);

	if (empty($row['bbc']))
		$pagecontent = htmlentities($_REQUEST['pagecontent'],ENT_QUOTES);
	else
	{
		if (!empty($_REQUEST['pagecontent_mode']) && isset($_REQUEST['pagecontent']))
		{
			global $sourcedir;
			require_once($sourcedir . '/Subs-Editor.php');

			$_REQUEST['pagecontent'] = html_to_bbc($_REQUEST['pagecontent']);

			// We need to unhtml it now as it gets done shortly.
			$pagecontent = un_htmlspecialchars($_REQUEST['pagecontent']);

		}
		else
		{
			$pagecontent = $smcFunc['htmlspecialchars']($_REQUEST['pagecontent'],ENT_QUOTES);

		}


	}


	$metatags = htmlentities($_REQUEST['metatags'],ENT_QUOTES);
	$icon = $smcFunc['htmlspecialchars']($_REQUEST['icon'],ENT_QUOTES);

	if (trim($pagetitle) == '')
		fatal_lang_error('ezp_err_no_page_title',false);

	if (trim($pagecontent) == '')
		fatal_lang_error('ezp_err_no_page_cotent',false);


	$menutitle = $smcFunc['htmlspecialchars']($_REQUEST['menutitle'],ENT_QUOTES);
	$showinmenu = isset($_REQUEST['showinmenu']) ? 1 : 0;


	// Get Permissions
	$permissionsArray = array();

	if (isset($_REQUEST['groups']))
	{
		foreach ($_REQUEST['groups'] as $rgroup)
			$permissionsArray[] = (int) $rgroup;
	}

	$finalPermissions = implode(",",$permissionsArray);

	$smcFunc['db_query']('', "
	UPDATE {db_prefix}ezp_page
	SET title = '$pagetitle', content = '$pagecontent', permissions = '$finalPermissions', metatags = '$metatags',
	showinmenu = '$showinmenu', menutitle = '$menutitle', icon = '$icon'
	WHERE id_page = $pageID LIMIT 1");

    cache_put_data('ezportal_page_' . $pageID, null, 120);

    EzPortalUpdatePageCount();

	// Redirect to the Page Manager
	redirectexit('action=admin;area=ezppagemanager;sa=pagemanager');
}

function EzPortalDeletePage()
{
	global $txt, $context, $smcFunc;

	// Check Permission
	if (allowedTo('ezportal_page') == false)
		isAllowedTo('ezportal_manage');

	if (empty($_REQUEST['p']))
		fatal_lang_error('ezp_err_no_page_selected', false);

	// Do Tabs
	EzPortalPageManagerTabs();

	// Store the Page ID
	$pageID = (int) $_REQUEST['p'];

	// Set the subtemplate
	$context['sub_template']  = 'ezportal_deletepage';

	// Set the page title
	$context['page_title'] = $txt['ezp_deletepage'];

	$dbresult = $smcFunc['db_query']('', "
	SELECT
		id_page, title
	FROM {db_prefix}ezp_page
	WHERE id_page = $pageID LIMIT 1");
	$row = $smcFunc['db_fetch_assoc']($dbresult);
	$context['ezp_deletepage_data'] = $row;
	$smcFunc['db_free_result']($dbresult);

}

function EzPortalDeletePage2()
{
	global $txt, $smcFunc;

	checkSession();

	// Check Permission
	if (allowedTo('ezportal_page') == false)
		isAllowedTo('ezportal_manage');

	if (empty($_REQUEST['p']))
		fatal_lang_error('ezp_err_no_page_selected', false);

	// Store the Page ID
	$pageID = (int) $_REQUEST['p'];

	// Delete the entry
	$smcFunc['db_query']('', "
	DELETE FROM {db_prefix}ezp_page
	WHERE id_page = $pageID LIMIT 1");

	EzPortalUpdatePageCount();

	// Redirect to the Page Manager
	redirectexit('action=admin;area=ezppagemanager;sa=pagemanager');
}

function EzPortalMessage($title, $description = '')
{
	global $context;

	$context['ezportal_message_title'] = $title;
	$context['ezportal_message_description'] = $description;

	// Set the page title
	$context['page_title'] = $title;

	// Set the subtemplate
	$context['sub_template']  = 'ezportal_messageform';

}

function EzPortalCredits()
{
	global $txt, $context;

	EzPortalAdminTabs();

	// Set the subtemplate
	$context['sub_template']  = 'ezportal_credits';

	// Set the page title
	$context['page_title'] = $txt['ezp_credits'];
}

function EzPortalAdminTabs()
{
	global $txt, $context, $txt, $scripturl;

	$context['current_action2'] = 'admin';

		if ($context['ezportal21beta'] == false)
		{
			@$context[$context['admin_menu_name']]['tab_data'] = array(
				'title' => $txt['ezp_tab_settings'],
				'description' =>  $txt['ezp_tab_settings_desc'],
				'tabs' => array(
					'settings' => array(
						'description' => '',
					),
				'modules' => array(
						'description' => '',
					),
				'import' => array(
						'description' => '',
					),
				'copyright' => array(
						'description' => $txt['ezp_txt_ordercopyright'],
					),

				'credits' => array(
						'description' => '',
					),
				),
			);
		}
		else
		{
			@$context[$context['admin_menu_name']]['tab_data'] = array(
					'title' => $txt['ezp_tab_settings'],
					'description' =>  $txt['ezp_tab_settings_desc'],
					'tabs' => array(
						'settings' => array(
							'description' => '',
						),
					'import' => array(
							'description' => '',
						),
					'copyright' => array(
							'description' => $txt['ezp_txt_ordercopyright'],
						),

					'credits' => array(
							'description' => '',
						),
					),
				);
		}

		$context['html_headers'] .= '<style>
dl.ezpsettings {
 clear:right;
 overflow:auto;
 margin:0 0 10px 0;
 padding:5px
}
dl.ezpsettings dt {
 width:30%;
 float:left;
 margin:0 0 10px 0;
 clear:both
}
dl.ezpsettings dt.windowbg {
 width:98%;
 float:left;
 margin:0 0 3px 0;
 padding:0 0 5px 0;
 clear:both
}
dl.ezpsettings dd {
 width:70%;
 float:right;
 margin:0 0 3px 0
}
dl.ezpsettings img {
 margin:0 10px 0 0;
 vertical-align:middle
}</style>';


}

function EzPortalBlockMangerTabs()
{
	global $txt, $context, $txt;

	$context['current_action2'] = 'admin';

		@$context[$context['admin_menu_name']]['tab_data'] = array(
			'title' => $txt['ezp_tab_blockmanager'],
			'description' => $txt['ezp_tab_blockmanager_desc'],
			'tabs' => array(
				'blocks' => array(
					'description' => '',
				),
			'downloadblock' => array(
					'description' => '',
				),
			'installedblocks' => array(
					'description' => '',
				),

			),
		);

		$context['html_headers'] .= '<style>
dl.ezpsettings {
 clear:right;
 overflow:auto;
 margin:0 0 10px 0;
 padding:5px
}
dl.ezpsettings dt {
 width:30%;
 float:left;
 margin:0 0 10px 0;
 clear:both
}
dl.ezpsettings dt.windowbg {
 width:98%;
 float:left;
 margin:0 0 3px 0;
 padding:0 0 5px 0;
 clear:both
}
dl.ezpsettings dd {
 width:70%;
 float:right;
 margin:0 0 3px 0
}
dl.ezpsettings img {
 margin:0 10px 0 0;
 vertical-align:middle
}</style>';




}

function EzPortalPageManagerTabs()
{
	global $txt, $context, $txt, $scripturl;


		$context[$context['admin_menu_name']]['tab_data'] = array(
			'title' => $txt['ezp_tab_pagemanager'],
			'description' =>  $txt['ezp_tab_pagemanager_desc'],
			'tabs' => array(
				'pagemanager' => array(
					'description' => '',
				),



			),
		);

	$context['current_action2'] = 'admin';

		$context['html_headers'] .= '<style>
dl.ezpsettings {
 clear:right;
 overflow:auto;
 margin:0 0 10px 0;
 padding:5px
}
dl.ezpsettings dt {
 width:30%;
 float:left;
 margin:0 0 10px 0;
 clear:both
}
dl.ezpsettings dt.windowbg {
 width:98%;
 float:left;
 margin:0 0 3px 0;
 padding:0 0 5px 0;
 clear:both
}
dl.ezpsettings dd {
 width:70%;
 float:right;
 margin:0 0 3px 0
}
dl.ezpsettings img {
 margin:0 10px 0 0;
 vertical-align:middle
}</style>';



}

function EzPortalImportPortal()
{
	global $txt, $context, $db_name, $smcFunc;

	// Check Permission
	isAllowedTo('ezportal_manage');

	// Make the nice Admin Tabs xD
	EzPortalAdminTabs();

	fatal_error($txt['ezp_txt_err_no_import_yet'],false);

	// Set the subtemplate
	$context['sub_template']  = 'ezportal_import';

	// Set the page title
	$context['page_title'] = $txt['ezp_import'];

	// Setup checks
	$context['portals']['MX'] = false;
	$context['portals']['TP'] = false;
	$context['portals']['SP'] = false;
	$context['portals']['DP'] = false;

	// Check if they have TP, MX, DP or SP installed
	$dbresult = $smcFunc['db_query']('', "
	SHOW TABLES
	FROM `$db_name`");
	while ($tableRow = $smcFunc['db_fetch_row']($dbresult))
	{
		// Check if Portal MX is installed
		if ($tableRow[0] == ($smcFunc . 'portamx_settings'))
			$context['portals']['MX'] = true;

		// Check if Simple Portal is installed
		if ($tableRow[0] == ($smcFunc . 'sp_functions'))
			$context['portals']['SP'] = true;

		// Check if Tiny Portal is installed
		if ($tableRow[0] == ($smcFunc . 'tp_settings'))
			$context['portals']['TP'] = true;
	}
	$smcFunc['db_free_result']($dbresult);

}

function EzPortalImportPortal2()
{
	global $sourcedir;

	// Check Permission
	isAllowedTo('ezportal_manage');

	// Check which type we are converting from
	$type = $_REQUEST['type'];

	// Load the conversion module
	require_once($sourcedir . '/Subs-EzPortal-Convert2.php');

	switch($type)
	{
		case "tp":
			EzPortalImportTinyPortal();
			break;
		case "mx":
			EzPortalImportPortalMX();
			break;
		case "sp":
			EzPortalImportSimplePortal();
			break;
		case "dp":
			EzPortalImportDreamPortal();
			break;
		case 'adk':
			EzPortalImportADKPortal();
			break;
		case 'up':
			EzPortalImportUltimatePortal();
			break;

	}

}

function EzPortalCollectStats()
{
	global $ezpSettings, $modSettings, $forum_version, $ezPortalVersion, $smcFunc;

	// Check if we allow stats collection
	if ($ezpSettings['ezp_allowstats'] != 1)
		return;

	// Get Total Pages
	$dbresult = $smcFunc['db_query']('', "
	SELECT
		COUNT(*) AS total
	FROM {db_prefix}ezp_page
	");
	$totalRow = $smcFunc['db_fetch_assoc']($dbresult);
	$totalPages = $totalRow['total'];
	$smcFunc['db_free_result']($dbresult);

	// Total ezBlocks
	$dbresult = $smcFunc['db_query']('', "
	SELECT
		COUNT(*) AS total
	FROM {db_prefix}ezp_blocks
	");
	$totalRow = $smcFunc['db_fetch_assoc']($dbresult);
	$totalBlocks = $totalRow['total'];
	$smcFunc['db_free_result']($dbresult);

	// Collect stats function if enabled we will collect some stats on your forum
	// These include forum size,member count, post count, and portal stats

	echo base64_encode("TT:" . $modSettings['totalTopics']  ."#TMSG:" . $modSettings['totalMessages']  ."#TMEM:" . $modSettings['totalMembers']  ."#SMFVER" . $forum_version . "#EZPVER" . $ezPortalVersion . "#TP" . $totalPages . "#TB" . $totalBlocks);

	// End output
	die('');

}

function EzPortalViewPage()
{
	global $context, $smcFunc, $user_info;

	if (empty($_REQUEST['p']))
		fatal_lang_error('ezp_err_no_page_selected', false);

	// Store the Page ID
	$pageID = (int) $_REQUEST['p'];


    if (($row = cache_get_data('ezportal_page_' . $pageID, 120)) == null)
    {

    	// Check If Page exists
    	$dbresult = $smcFunc['db_query']('', "
    	SELECT
    		id_page, title, content, permissions, metatags, bbc 
    	FROM {db_prefix}ezp_page
    	WHERE id_page = $pageID LIMIT 1");


    	$row = $smcFunc['db_fetch_assoc']($dbresult);
    	$smcFunc['db_free_result']($dbresult);
        cache_put_data('ezportal_page_' . $pageID, $row, 120);

    }

    $context['ezpage_info'] = $row;


	if (empty($row['id_page']))
		fatal_lang_error('ezp_err_page_does_not_exist', false);

	// Check Page Permissions
	$permissionsGroups = explode(',',$row['permissions']);


	$has_Permission = count(array_intersect($user_info['groups'], $permissionsGroups)) == 0 ? false : true;


	if ($has_Permission == false)
		fatal_lang_error('ezp_err_no_page_permission', false);

	// Set Page Title
	$context['page_title'] = $row['title'];

	// Setup Page Properties
	if (empty($context['ezpage_info']['bbc']))
		$context['ezp_pagecontent'] = html_entity_decode($row['content'],ENT_QUOTES);
	else
		$context['ezp_pagecontent'] = parse_bbc($row['content'],1);

		// Auto Embed Media Pro
		global $sourcedir;
		if (file_exists($sourcedir . '/AutoEmbedMediaPro2.php'))
		{

				require_once($sourcedir . '/AutoEmbedMediaPro2.php');
			if (function_exists("MediaProProcess"))
				$context['ezp_pagecontent'] = MediaProProcess($context['ezp_pagecontent']);

		}

	global $scripturl;


	$context['ezp_pagecontent'] = str_replace('{$member.id}',$user_info['id'], $context['ezp_pagecontent']);
	$context['ezp_pagecontent'] = str_replace('{$member.name}',$user_info['name'], $context['ezp_pagecontent']);
	$context['ezp_pagecontent'] = str_replace('{$member.email}',$user_info['email'], $context['ezp_pagecontent']);
	$context['ezp_pagecontent'] = str_replace('{$member.link}','<a href="' . $scripturl . '?action=profile;u=' . $user_info['id'] . '">' . $user_info['name'] . '</a>', $context['ezp_pagecontent']);



	$context['html_headers'] .=  html_entity_decode($row['metatags'],ENT_QUOTES);

	// Setup the subtemplate
	$context['sub_template']  = 'ezportal_viewpage';

	// Updated Page Views
	$smcFunc['db_query']('', "
	UPDATE {db_prefix}ezp_page
	SET views = views + 1
	WHERE id_page = $pageID LIMIT 1");
}



function EzPortalEditColumn()
{
	global $txt, $context, $smcFunc;

	// Check Permission
	isAllowedTo('ezportal_blocks');

	EzPortalBlockMangerTabs();

	$columnID = (int) $_REQUEST['column'];

	// Set Page Title
	$context['page_title'] = $txt['ezp_editcolumn'];

	// Setup the subtemplate
	$context['sub_template']  = 'ezportal_edit_column';

	// Get Column Information
	$dbresult = $smcFunc['db_query']('', "
	SELECT
		id_column, column_title, can_collapse, column_width, column_percent, active, sticky 
	FROM {db_prefix}ezp_columns
	WHERE id_column = $columnID LIMIT 1");
	$row = $smcFunc['db_fetch_assoc']($dbresult);
	$context['ezp_column_data'] = $row;
	$smcFunc['db_free_result']($dbresult);

}

function EzPortalEditColumn2()
{
	global $smcFunc;

	// Check Permission
	isAllowedTo('ezportal_blocks');

	checkSession();

	// Get column ID
	$column = (int) $_REQUEST['column'];
	$active = (int) $_REQUEST['active'];
	$columnwidth = (int) $_REQUEST['columnwidth'];
	$columnpercent = (int) $_REQUEST['columnpercent'];
	$can_collapse = isset($_REQUEST['can_collapse']) ? 1 : 0;
	$sticky = isset($_REQUEST['sticky'])  ? 1 : 0;

	if ($columnwidth < 0)
		$columnwidth = 0;

	// Update category
	$smcFunc['db_query']('', "
	UPDATE {db_prefix}ezp_columns
	SET column_width = '$columnwidth', column_percent = '$columnpercent', active = $active,
	can_collapse = $can_collapse, sticky = '$sticky'
	WHERE id_column = " . $column . " LIMIT 1");

	cache_put_data('ezportal_columns', null, 60);

	// Redirect to the Block Manager
	redirectexit('action=admin;area=ezpblocks;sa=blocks');
}

function EzPortalDownloadBlocks()
{
	global $txt, $context;

	// Check Permission
	if (allowedTo('ezportal_blocks') == false)
		isAllowedTo('ezportal_manage');

	EzPortalBlockMangerTabs();

	// Set the subtemplate
	$context['sub_template']  = 'ezportal_download_block';

	// Set the page title
	$context['page_title'] = $txt['ezp_download_blocks'];

}

function EzPortalImportBlock()
{
	global $txt, $smcFunc, $ezpSettings;

	if (allowedTo('ezportal_blocks') == false)
		isAllowedTo('ezportal_manage');

	// Process the file
	if (isset($_FILES['blockfile']['name']) && $_FILES['blockfile']['name'] != '')
	{
		$extension = substr($_FILES['blockfile']['name'], strrpos(substr($_FILES['blockfile']['name'], 0, -3), '.'));

		$compressedFile = true;
		if ($extension == '.xml')
			$compressedFile = false;

		move_uploaded_file($_FILES['blockfile']['tmp_name'],$ezpSettings['ezp_path'] . 'blocks/' . $_FILES['blockfile']['name']);

         if (!@is_writable($ezpSettings['ezp_path'] . 'blocks/' . $_FILES['blockfile']['name']))
            @chmod($ezpSettings['ezp_path'] . 'blocks/' . $_FILES['blockfile']['name'], 0755);
         if (!@is_writable($ezpSettings['ezp_path'] . 'blocks/' . $_FILES['blockfile']['name']))
            @chmod($ezpSettings['ezp_path'] . 'blocks/' . $_FILES['blockfile']['name'], 0777);

		EzPortalProcessBlockFile($_FILES['blockfile']['name'], $compressedFile);

	}
	else
		fatal_lang_error('ezp_err_no_block_uploaded',false);

	// Redirect to the installed ezBlock list if worked
	redirectexit('action=admin;area=ezpblocks;sa=installedblocks');

}

function SetupEzPortal()
{
	global $sourcedir, $context, $maintenance, $user_info, $ezpSettings, $boarddir, $boardurl, $smcFunc, $settings, $disableEzPortal, $modSettings;

	if (function_exists("set_tld_regex"))
	{
		if (!defined('WIRELESS'))
			define("WIRELESS",1);
	}
	else
	{
		// If wireless skip the ezPortal stuff.
		if (defined(WIRELESS))
		{
			$wirelessCheck = WIRELESS;
			if (!empty($wirelessCheck))
				return false;
		}
	}

    // Disable ezPortal
    if (!empty($disableEzPortal))
        return false;

	// Actions that should not load EzPortal
	if (isset($_REQUEST['action']))
	{
		if (in_array($_REQUEST['action'],array('.xml','dlattach','quotefast','findmember', 'login2', 'logintfa', 'helpadmin', 'printpage', 'spellcheck',"requestmembers","verificationcode","jsoption","likes","viewquery","viewsmfile","xmlhttp")))
			return false;

		if ($_REQUEST['action'] == 'pm' && isset($_REQUEST['sa']) && isset($_REQUEST['sa']) == 'popup')
			return false;

		if ($_REQUEST['action'] == 'profile' && isset($_REQUEST['area']) &&  in_array($_REQUEST['area'],array('popup', 'alerts_popup', 'download', 'dlattach')))
			return false;


	}
	// Check for XML if found ignore it
	if (isset($_REQUEST['xml']))
	{
		return false;
	}

	// Check maintenance mode
	if (!empty($maintenance) && $maintenance == 1  && $context['user']['is_admin'] == false)
		return false;


	// Subs for EzPortal
	require_once($sourcedir . '/Subs-EzPortalMain2.php');

	// Load EzPortal Settings
	LoadEzPortalSettings();


    // Check if we are disabling ezportal for mobile devices
    if (!empty($ezpSettings['ezp_disablemobiledevices']))
    {

            $user_agents = array(
			array('iPhone', 'iphone'),
			array('iPod', 'ipod'),
			array('PocketIE', 'iemobile'),
			array('Opera Mini', isset($_SERVER['HTTP_X_OPERAMINI_PHONE_UA']) ? $_SERVER['HTTP_X_OPERAMINI_PHONE_UA'] : "Opera Mini"),
			array('Opera Mobile', 'Opera Mobi'),
			array('Android', 'android'),
			array('Symbian', 'symbian'),
			array('BlackBerry', 'blackberry'),
			array('BlackBerry Storm', 'blackberry05'),
			array('Palm', 'palm'),
			array('Web OS', 'webos'),
		);

		foreach ($user_agents as $ua)
		{
			$string = (string) $ua[1];

			if (!empty($string))
			if ((strpos(strtolower($_SERVER['HTTP_USER_AGENT']), $string)))
				return false;

        }
    }


    // Check if disabling the block in the ezportal admin area.
    if (isset($_REQUEST['action']))
    {
        if ($_REQUEST['action'] == 'admin')
        {
            if (!empty($ezpSettings['ezp_disableblocksinadmin']))
                return false;
        }
    }


	// Be sure that the paths are setup
	if (empty($ezpSettings['ezp_url']))
		$ezpSettings['ezp_url'] = $boardurl . '/ezportal/';

	if (empty($ezpSettings['ezp_path']))
		$ezpSettings['ezp_path'] = $boarddir . '/ezportal/';

	// Load Language
	if (loadlanguage('EzPortal') == false)
		loadLanguage('EzPortal','english');

	$context['ezportal21beta'] = false;
	// Load the main template file
   if (function_exists("set_tld_regex"))
    {
	   loadtemplate('EzPortal2.1');
	   $context['html_headers'] .= '<link rel="stylesheet" type="text/css" href="' . $settings['default_theme_url'] . '/css/ezportal2.1.css?fin21" />';
        $context['ezportal21beta'] = true;
    }
    else
    {
    	if (empty($modSettings['ezp_responsivemode']))
        	loadtemplate('EzPortal2');
    	else
    	{
    		loadtemplate('EzPortal2r');
    		$context['html_headers'] .= '<link rel="stylesheet" type="text/css" href="' . $settings['default_theme_url'] . '/css/ezportal.css?fin20" />';
   		}
    }

if (!empty($context['linktree']))
      foreach ($context['linktree'] as $key => $tree)
	  {
	  	if (isset($tree['url']))
		{
			if (strpos($tree['url'], '#c') !== false && strpos($tree['url'], 'action=forum#c') === false)
				$context['linktree'][$key]['url'] = str_replace('#c', '?action=forum#c', $tree['url']);
		}
	  }

   // Setup template layer
	$context['template_layers'][] = 'ezblock';

	$is_vis_on_forum = false;
	$is_vis_on_portal = false;
	$is_vis_on_board_index = false;
	// Check if we are on portal homepage
	if ($ezpSettings['ezp_portal_enable'])
	{
		if (!isset($_REQUEST['action']) && !isset($_REQUEST['board']) &&  !isset($_REQUEST['topic']))
			$is_vis_on_portal = true;
	}
	// Check if we are on portal homepage
	$context['ezPortal'] = '';
	if ($is_vis_on_portal == true)
	{
		$context['ezPortal'] = 'ezPortal';
	}

	if (isset($_REQUEST['board']) ||  isset($_REQUEST['topic']))
	{
		$is_vis_on_forum = true;

		// EzPortal homepage enabled so we need to add a new button action
		if ($ezpSettings['ezp_portal_enable'] == 1 )
			$context['current_action2'] = 'forum';
	}

	$vis_page = 0;

	if (isset($_REQUEST['action']))
	{
		if ($_REQUEST['action'] == 'forum')
		{
			$is_vis_on_forum = true;
			$context['robot_no_index'] = false;
		}

		if ($_REQUEST['action'] == 'ezportal' && isset($_REQUEST['sa']))
		{
			if ($_REQUEST['sa'] == 'page')
				$vis_page = (int) $_REQUEST['p'];
		}
	}
	else
	{
		if (!isset($_REQUEST['board']) &&  !isset($_REQUEST['topic']) && empty($ezpSettings['ezp_portal_enable']))
			$is_vis_on_board_index = true;
	}


	if ($is_vis_on_forum  == true)
	{
		if (!isset($_REQUEST['board']) &&  !isset($_REQUEST['topic']))
			$is_vis_on_board_index = true;
	}



	// Check for any ezBlocks that are collapsed.
	$collapsedEzBlocks = array();
    $collapsedEzColumns = array();
	if (!$user_info['is_guest'])
	{
	   if (($collapsedEzBlocks = cache_get_data('ezportal_block_collaspe_' . $user_info['id'], 90)) === null)
       {
		$request = $smcFunc['db_query']('', "
		SELECT
			value
		FROM {db_prefix}themes
		WHERE ID_MEMBER = " . $user_info['id'] . " AND ID_THEME = 0 AND variable = 'ezportal_ezblockcollapse'");
		$row = $smcFunc['db_fetch_assoc']($request);

		if (!empty($row['value']))
			$collapsedEzBlocks = explode(",",$row['value']);
		else
			$collapsedEzBlocks = array();

		$smcFunc['db_free_result']($request);

        cache_put_data('ezportal_block_collaspe_' . $user_info['id'], $collapsedEzBlocks, 90);
       }



        if (($collapsedEzColumns = cache_get_data('ezportal_col_collaspe_' . $user_info['id'], 90)) === null)
        {
    		$request =  $smcFunc['db_query']('',"
    		SELECT
    			value
    		FROM {db_prefix}themes
    		WHERE ID_MEMBER = " . $user_info['id'] . " AND ID_THEME = 0 AND variable = 'ezportal_ezcolumncollapse'");
    		$row = $smcFunc['db_fetch_assoc']($request);

    		if (!empty($row['value']))
    			$collapsedEzColumns = explode(",",$row['value']);
    		else
    			$collapsedEzColumns = array();

    		$smcFunc['db_free_result']($request);

            cache_put_data('ezportal_col_collaspe_' . $user_info['id'], $collapsedEzColumns, 90);
        }



	}

	$ezColumnsCache = array();
	if (($ezColumnsCache = cache_get_data('ezportal_columns', 60)) === null)
	{
		// Load ezBlocks and ezColumns
		$dbresult = $smcFunc['db_query']('', "
		SELECT
			id_column, column_title, column_width, column_percent, active, can_collapse, visibileactions, visibileboards,
			visibileareascustom, visibilepages, sticky 
		FROM {db_prefix}ezp_columns
		WHERE active = 1
		ORDER BY id_column ASC");
		while($row = $smcFunc['db_fetch_assoc']($dbresult))
		{

			// center column
			if (function_exists("set_tld_regex"))
			if ($row['column_title'] == 'Center')
			{

			$dbresult5 = $smcFunc['db_query']('', "
				SELECT
					count(*) as total
				FROM {db_prefix}ezp_block_layout AS l
					INNER JOIN {db_prefix}ezp_blocks AS b ON (b.id_block = l.id_block)
				LEFT JOIN {db_prefix}ezp_icons AS i ON (l.id_icon = i.id_icon)
				WHERE l.active = 1 AND l.id_column = " . $row['id_column'] . "
				");
				$row5 = $smcFunc['db_fetch_assoc']($dbresult5);



				if ($row5['total'] == 0)
				{
					$row['visibileboards'] = '';
					$row['visibileactions'] = '';
					$row['visibileareascustom'] = '';
				     $row['visibilepages'] = '';
				}




			}


			$ezColumnsCache[] = $row;
		}
		$smcFunc['db_free_result']($dbresult);


		if (function_exists("set_tld_regex"))
		{
			// Load center anyway for responsive...

			$dbresult = $smcFunc['db_query']('', "
			SELECT
				id_column, column_title, column_width, column_percent, active, can_collapse, visibileactions, visibileboards,
				visibileareascustom, visibilepages, sticky
			FROM {db_prefix}ezp_columns
			WHERE active = 0 and column_title = 'Center'
			ORDER BY id_column ASC");
			while($row = $smcFunc['db_fetch_assoc']($dbresult))
			{
				$row['visibileboards'] = '';
				$row['visibileactions'] = '';
				$row['visibileareascustom'] = '';
				$row['visibilepages'] = '';

				$ezColumnsCache[] = $row;
			}

		}


		cache_put_data('ezportal_columns', $ezColumnsCache, 60);
	}

	$context['ezPortalColumns'] = array();

	if (!empty($ezColumnsCache))
	foreach($ezColumnsCache as $row)
	{

		// Check if the column is collapsed
			if (!$user_info['is_guest'])
			{
				if (in_array($row['id_column'],$collapsedEzColumns))
					$row['IsCollapsed'] = 1;
				else
					$row['IsCollapsed'] = 0;
			}
			else
			{

				if (isset($_SESSION['ezp_column_guests']))
				{
					$guestCollapsedEzColumns = explode(",",$_SESSION['ezp_column_guests']);
					if (in_array($row['id_column'],$guestCollapsedEzColumns))
						$row['IsCollapsed'] = 1;
					else
						$row['IsCollapsed'] = 0;
				}
				else
					$row['IsCollapsed'] = 0;

			}


		// Check if we should even show any blocks if not continue
		$visibleAction = false;
		if ($row['visibileactions'] != '' && isset($_REQUEST['action']))
		{
			$columnActions = explode(",",$row['visibileactions']);

			if (in_array($_REQUEST['action'],$columnActions))
				$visibleAction = true;
		}

		// Check for visible board
		$visibleBoard = false;
		if ($row['visibileboards'] != '' && isset($_REQUEST['board']))
		{
			$columnBoards = explode(",",$row['visibileboards']);

			if (in_array($_REQUEST['board'],$columnBoards))
				$visibleBoard = true;
		}

		$visiblePage = false;
		if ($row['visibilepages'] != '' && !empty($vis_page))
		{
			$columnPages = explode(",",$row['visibilepages']);

			if (in_array($vis_page,$columnPages))
				$visiblePage = true;
		}


		$can_show = false;
		if ($row['visibileareascustom'] != '')
		{
			$customVis = explode(",",$row['visibileareascustom']);

			if ($is_vis_on_forum)
			{
				if (in_array('forum',$customVis))
					$can_show = true;

			}
			if ($is_vis_on_portal)
			{
				if (in_array('portal',$customVis))
					$can_show = true;
			}

			if ($is_vis_on_board_index)
			{
				if (in_array('boardindex',$customVis))
					$can_show = true;
			}
		}

		// if empty all is good
		if ($row['visibileboards'] == '' && $row['visibileactions'] == '' && $row['visibileareascustom'] == '' && $row['visibilepages'] == '')
		{
			$visibleAction  = true;
			$visibleBoard = true;
			$visiblePage = true;
			$can_show = true;

		}

		// If both false skip the whole column
		if ($can_show == false && $visibleAction == false && $visibleBoard == false && $visiblePage == false)
			continue;

		$blocks = array();

		// Get all the ezBlocks under these columns
		$ezColumnsBlockColumnCache  = array();
		if (($ezColumnsBlockColumnCache = cache_get_data('ezportal_column_' . $row['id_column'], 60)) === null)
		{
			$dbresult2 = $smcFunc['db_query']('', "
			SELECT
				l.customtitle, l.id_layout, l.active, l.id_order, l.blockdata, b.blocktype,
				l.id_block, l.permissions, l.can_collapse, l.blockmanagers, b.blockdata bdata,
				l.visibileactions, l.visibileboards, l.hidetitlebar, l.visibileareascustom, i.icon, l.visibilepages,
				l.hidemobile, l.showonlymobile, l.block_header_class, l.block_body_class 
			FROM {db_prefix}ezp_block_layout AS l
				INNER JOIN {db_prefix}ezp_blocks AS b ON (b.id_block = l.id_block)
			LEFT JOIN {db_prefix}ezp_icons AS i ON (l.id_icon = i.id_icon)
			WHERE l.active = 1 AND l.id_column = " . $row['id_column'] . "
			ORDER BY l.id_order ASC");

			while($row2 = $smcFunc['db_fetch_assoc']($dbresult2))
			{
				$ezColumnsBlockColumnCache[] = $row2;
			}
			$smcFunc['db_free_result']($dbresult2);
			cache_put_data('ezportal_column_' . $row['id_column'], $ezColumnsBlockColumnCache, 60);


		}

		// column not active center column fix 2.1....
		if ($row['active'] == 0)
			$ezColumnsBlockColumnCache = array();


		if (!empty($ezColumnsBlockColumnCache))
		foreach($ezColumnsBlockColumnCache as $row2)
		{
			if ($row2['blocktype'] == 'builtin')
				$row2['blockdata'] = $row2['bdata'];
			// Check Visisble Options
			$visibleBlockAction = false;
			if ($row2['visibileactions'] != '' && isset($_REQUEST['action']))
			{
				$ezblockActions = explode(",",$row2['visibileactions']);

				if (in_array($_REQUEST['action'],$ezblockActions))
					$visibleBlockAction = true;
			}

			// Check for visible board
			$visibleBlockBoard = false;
			if ($row2['visibileboards'] != '' && isset($_REQUEST['board']))
			{
				$ezblockBoards = explode(",",$row2['visibileboards']);

				if (in_array($_REQUEST['board'],$ezblockBoards))
					$visibleBlockBoard = true;
			}

			// Check for visible pages
			$visibleBlockPage = false;
			if ($row2['visibilepages'] != '' && !empty($vis_page))
			{
				$ezblockPages = explode(",",$row2['visibilepages']);

				if (in_array($vis_page,$ezblockPages))
					$visibleBlockPage = true;
			}

			$ez_can_show = false;
			if ($row2['visibileareascustom'] != '')
			{

				$EZcustomVis = explode(",",$row2['visibileareascustom']);

				if ($is_vis_on_forum)
				{
					if (in_array('forum',$EZcustomVis))
						$ez_can_show = true;

				}
				if ($is_vis_on_portal)
				{
					if (in_array('portal',$EZcustomVis))
						$ez_can_show = true;

				}

				if ($is_vis_on_board_index)
				{
					if (in_array('boardindex',$EZcustomVis))
						$ez_can_show = true;
				}
			}

			// if empty all is good
			if ($row2['visibileboards'] == '' && $row2['visibileactions'] == '' && $row2['visibileareascustom'] == '' && $row2['visibilepages'] == '')
			{
				$visibleBlockAction = true;
				$visibleBlockBoard = true;
				$visibleBlockPage = true;
				$ez_can_show = true;

			}

			$showMobileBlock = 1;
			// Hiding on mobile view
			if ($row2['hidemobile'] == 1)
			{

		            $user_agents = array(
					array('iPhone', 'iphone'),
					array('iPod', 'ipod'),
					array('PocketIE', 'iemobile'),
					array('Opera Mini', isset($_SERVER['HTTP_X_OPERAMINI_PHONE_UA']) ? $_SERVER['HTTP_X_OPERAMINI_PHONE_UA'] : "Opera Mini"),
					array('Opera Mobile', 'Opera Mobi'),
					array('Android', 'android'),
					array('Symbian', 'symbian'),
					array('BlackBerry', 'blackberry'),
					array('BlackBerry Storm', 'blackberry05'),
					array('Palm', 'palm'),
					array('Web OS', 'webos'),
				);

				foreach ($user_agents as $ua)
				{
					$string = (string) $ua[1];
					if (!empty($string))
					if ((strpos(strtolower($_SERVER['HTTP_USER_AGENT']), $string)))
						$showMobileBlock = false;

		        }

			}

			// Show Only on mobile
			if ($row2['showonlymobile'] == 1)
			{
				$desktopYes = 1;

		            $user_agents = array(
					array('iPhone', 'iphone'),
					array('iPod', 'ipod'),
					array('PocketIE', 'iemobile'),
					array('Opera Mini', isset($_SERVER['HTTP_X_OPERAMINI_PHONE_UA']) ? $_SERVER['HTTP_X_OPERAMINI_PHONE_UA'] : "Opera Mini"),
					array('Opera Mobile', 'Opera Mobi'),
					array('Android', 'android'),
					array('Symbian', 'symbian'),
					array('BlackBerry', 'blackberry'),
					array('BlackBerry Storm', 'blackberry05'),
					array('Palm', 'palm'),
					array('Web OS', 'webos'),
				);

				foreach ($user_agents as $ua)
				{
					$string = (string) $ua[1];
					if (!empty($string))
						if ((strpos(strtolower($_SERVER['HTTP_USER_AGENT']), $string)))
							$desktopYes = 0;

		        }

		        if ($desktopYes == 1)
		        	$showMobileBlock = false;


			}



			// If both false skip the whole column
			if ($ez_can_show == false && $visibleBlockAction == false && $visibleBlockBoard == false && $visibleBlockPage == false)
				continue;

			if ($showMobileBlock == false)
				continue;

			// Check Permissions on blocks and grab blocks
			$permissionsGroups = explode(',',$row2['permissions']);
			$managerezBlocks = explode(',',$row2['blockmanagers']);

			$isManager = false;

			$hasPermissionView = count(array_intersect($user_info['groups'], $permissionsGroups)) == 0 ? false : true;

			// No permission to view the ezBlock hide it by skipping it!
			if ($hasPermissionView == false)
				continue;

			// Check ezBlock Managers Permissions
			$isManager = count(array_intersect($user_info['groups'], $managerezBlocks)) == 0 ? false : true;


			// Setup whether they can manage this ezblock
			$row2['IsManager'] = $isManager;

			// Get all parameters for this ezblock
			if (strtolower($row2['blocktype']) == 'php' || strtolower($row2['blocktype']) == 'builtin')
			{
				// Get any parameters
				$parameters = array();
                if (($parameters= cache_get_data('ezportal_layoutparm_' . $row2['id_layout'], 120)) == null)
                {


    				$dbresult3 = $smcFunc['db_query']('', "
    				SELECT
    					p.id_parameter, p.parameter_name, v.data
    				FROM {db_prefix}ezp_block_parameters AS p
    					INNER JOIN {db_prefix}ezp_block_parameters_values AS v ON (p.id_parameter = v.id_parameter)
    				WHERE v.id_layout = " . $row2['id_layout']
    				);
    				while($parameterRow = $smcFunc['db_fetch_assoc']($dbresult3))
    					$parameters[] = $parameterRow;
    				$smcFunc['db_free_result']($dbresult3);

                    cache_put_data('ezportal_layoutparm_' . $row2['id_layout'], $parameters, 120);
                }

				$row2['parameters'] = $parameters;
			}

			// Check if it is collapsed
			if (!$user_info['is_guest'])
			{
				if (in_array($row2['id_layout'],$collapsedEzBlocks))
					$row2['IsCollapsed'] = 1;
				else
					$row2['IsCollapsed'] = 0;
			}
			else
			{

				if (isset($_SESSION['ezp_block_guests']))
				{
					$guestCollapsedEzBlocks = explode(",",$_SESSION['ezp_block_guests']);
					if (in_array($row2['id_layout'],$guestCollapsedEzBlocks))
						$row2['IsCollapsed'] = 1;
					else
						$row2['IsCollapsed'] = 0;
				}
				else
					$row2['IsCollapsed'] = 0;

			}

			$blocks[] = $row2;
		}

		$row['blocks'] = $blocks;
		// Only save the column if there is something in it!
		if ($context['ezportal21beta'] == true)
		{
			if (!empty($blocks) || $row['column_title'] == 'Center')
				$context['ezPortalColumns'][] = $row;
		}
		else
		{
			if (!empty($blocks))
				$context['ezPortalColumns'][] = $row;

		}
	}

	/// Nothing here to do!
	if (empty($context['ezPortalColumns']))
		return false;


	// Check Permission
	$context['ezportal_block_manager'] = false;
	if (allowedTo('ezportal_blocks') == true)
		$context['ezportal_block_manager'] = true;

	if (!isset($context['html_headers']))
		$context['html_headers'] = '';
	// Expand and collapse code
	$context['html_headers'] .= '
	<script type="text/javascript"><!-- // --><![CDATA[
	function EzToogle(myEzItem,ezBlockID,myImage,isBlock)
	{
		var ezCollapseState = 0;

		if (document.getElementById && document.getElementById(myEzItem)!= null) {
			if (document.getElementById(myEzItem).style.display == "none")
			{
				document.getElementById(myEzItem).style.display = "";
				ezCollapseState = 0;
			}
			else
			{
				document.getElementById(myEzItem).style.display = "none";
				ezCollapseState = 1;
			}
		} else if (document.layers && document.layers[myEzItem]!= null) {
			if (document.layers[myEzItem].display == "none")
			{
				document.layers[myEzItem].display = "";
				ezCollapseState = 0;
			}
			else
			{
				document.layers[myEzItem].display = "none";
				ezCollapseState = 1;
			}
		} else if (document.all) {
			if (document.all[myEzItem].style.display == "none")
			{
				document.all[myEzItem].style.display = "";
				ezCollapseState = 0;
			}
			else
			{
				document.all[myEzItem].style.display = "none";
				ezCollapseState = 1;
			}
		}';

		$context['html_headers'] .= 'EzPortalSaveBlockState(ezBlockID,ezCollapseState,isBlock);';


		if ($context['ezportal21beta'] == false)
		{

			$context['html_headers'] .= '
			if (myImage.src == "' . $settings['images_url'] . '/collapse.gif")
				myImage.src = "' . $settings['images_url'] . '/expand.gif";
			else
				myImage.src = "' . $settings['images_url'] . '/collapse.gif";

		}';

		}
		else
		{
			$context['html_headers'] .= '
			if (myImage.src == "' . $ezpSettings['ezp_url'] . 'icons/collapse.png")
				myImage.src = "' . $ezpSettings['ezp_url'] . 'icons/expand.png";
			else
				myImage.src = "' . $ezpSettings['ezp_url'] .'icons/collapse.png";

		}';

		}


	$context['html_headers'] .= '
	function EzPortalSaveBlockState(ezBlock,ezState,isBlock)
	{
		var tempImage = new Image();
		if (isBlock == 1)
			tempImage.src = smf_scripturl + (smf_scripturl.indexOf("?") == -1 ? "?" : "&") + "action=ezportal;sa=blockstate;blockid=" + ezBlock + ";state=" + ezState + ";sesc=" + "' . $context['session_id'] . '" +  ";" + (new Date().getTime());
		else
			tempImage.src = smf_scripturl + (smf_scripturl.indexOf("?") == -1 ? "?" : "&") + "action=ezportal;sa=columnstate;columnid=" + ezBlock + ";state=" + ezState + ";sesc=" + "' . $context['session_id'] . '" +  ";" + (new Date().getTime());

	}';

	$context['html_headers'] .= '
	// ]]></script>';

	$context['ezp_loaded'] = true;

}

function EzPortalBlocksSave()
{
	global $txt, $smcFunc;

	if (allowedTo('ezportal_blocks') == false)
		isAllowedTo('ezportal_manage');

	checkSession();

	$column = (int) $_REQUEST['column'];

	if (empty($column))
		fatal_lang_error('ezp_err_no_column_selected', false);

	if (isset($_REQUEST['order']))
		foreach($_REQUEST['order'] as $key => $value)
		{
			$orderArray = $_REQUEST['order'];
			$titleArray = $_REQUEST['title'];
			$activeArray = $_REQUEST['active'];

			$active = (int) $activeArray[$key];
			$title = $smcFunc['htmlspecialchars']($titleArray[$key],ENT_QUOTES);
			$order = (int) $orderArray[$key];

			// Update the ezBlock
			$smcFunc['db_query']('', "
			UPDATE {db_prefix}ezp_block_layout
			SET active = $active, id_order = $order, customtitle = '$title'
			WHERE id_layout = $key");
		}

	// Finally Reorder the ezBlocks
	ReOrderBlocksbyColumn($column);

	cache_put_data('ezportal_column_' . $column, null, 60);

	// Redirect to the ezBlock Manager
	redirectexit('action=admin;area=ezpblocks;sa=blocks');
}

function ReOrderBlocksbyColumn($columnID)
{
	global $smcFunc;

	$dbresult = $smcFunc['db_query']('', "
	SELECT
		id_order, id_layout
	FROM {db_prefix}ezp_block_layout
	WHERE id_column = $columnID ORDER BY id_order ASC");
	if ($smcFunc['db_affected_rows']() != 0)
	{
		$count = 1;
		while($row2 = $smcFunc['db_fetch_assoc']($dbresult))
		{
			$smcFunc['db_query']('', "UPDATE {db_prefix}ezp_block_layout
			SET id_order = $count WHERE id_layout  = " . $row2['id_layout']);
			$count++;
		}
	}
	$smcFunc['db_free_result']($dbresult);
}

function EzPortalInstalledBlocks()
{
	global $txt, $context, $smcFunc;

	// Check Permission
	isAllowedTo('ezportal_blocks');

	EzPortalBlockMangerTabs();

	// Set the subtemplate
	$context['sub_template']  = 'ezportal_installed_blocks';

	// Set the page title
	$context['page_title'] = $txt['ezp_installed_blocks'];

	// Get Columns
	$dbresult = $smcFunc['db_query']('', "
	SELECT
		id_block, blocktitle, blockauthor, blockwebsite, blockversion, forumversion, no_delete
	FROM {db_prefix}ezp_blocks
	ORDER BY id_block DESC");
	$context['ezportal_installed_blocks'] = array();

	while($row = $smcFunc['db_fetch_assoc']($dbresult))
		$context['ezportal_installed_blocks'][] = $row;
	$smcFunc['db_free_result']($dbresult);

}

function EzPortalForumHomePage()
{
	global $context, $mbname, $ezpSettings, $modSettings;

	// Setup Page Title
	if (empty($ezpSettings['ezp_portal_homepage_title']))
		$context['page_title'] = $mbname;
	else
		$context['page_title'] = $ezpSettings['ezp_portal_homepage_title'];

    if (function_exists("set_tld_regex"))
    {
	   loadtemplate('EzPortal2.1');
        $context['ezportal21beta'] = true;

    }
    else
    {
    	if (empty($modSettings['ezp_responsivemode']))
        	loadtemplate('EzPortal2');
    	else
    		loadtemplate('EzPortal2r');
    }

	$context['sub_template']  = 'ezportal_frontpage';
}

function EzPortalCheck_syntax($code)
{
    return @eval('return true;' . $code);
}

function EzPortalUninstallBlock()
{
	global $txt, $smcFunc;

	// Check Permission
	if (allowedTo('ezportal_blocks') == false)
		isAllowedTo('ezportal_manage');

	checkSession('get');

	$block = (int) $_REQUEST['block'];
	$blocksInUse = '';
	// Check if ezBlock is in use if so we can't delete it.
	$dbresult = $smcFunc['db_query']('', "
	SELECT
		id_order, id_layout, customtitle
	FROM {db_prefix}ezp_block_layout
	WHERE id_block = $block");
	while($row = $smcFunc['db_fetch_assoc']($dbresult))
		$blocksInUse .= $row['customtitle'] . '<br />';

	$smcFunc['db_free_result']($dbresult);

	if ($blocksInUse != '')
		EzPortalMessage($txt['ezp_txt_uninstall_block2'],$txt['ezp_err_uninstall_block'] . $blocksInUse);
	else
	{
		// Uninstall the ezBlock
		$smcFunc['db_query']('', "
		DELETE FROM {db_prefix}ezp_blocks
		WHERE id_block = $block");

		// Delete ezBlock Parameters
		$smcFunc['db_query']('', "
		DELETE FROM {db_prefix}ezp_block_parameters
		WHERE id_block = $block");

		// Redirect to the installed ezBlock list
		redirectexit('action=admin;area=ezpblocks;sa=installedblocks');
	}
}

function EzPortalBlockState()
{
	global $smcFunc, $user_info, $context;

	checkSession('get');


	if (!isset($_REQUEST['blockid']))
		return;

	if (!isset($_REQUEST['state']))
		return;


    $context['template_layers'] = array();


	// Get the collapse state of the ezBlock
	$state = (int) $_REQUEST['state'];

	$layoutID = (int) $_REQUEST['blockid'];

	// If we are a guest do a session to save it.
	if ($user_info['is_guest'])
	{

		$collapsedEzBlocks = array();

		if (isset($_SESSION['ezp_block_guests']))
			$collapsedEzBlocks  = explode(',',$_SESSION['ezp_block_guests']);

		if ($state == 0)
		{
			foreach($collapsedEzBlocks as $key => $value)
			{
				if ($value == $layoutID)
					unset($collapsedEzBlocks[$key]);
			}
		}
		else
			$collapsedEzBlocks[] = $layoutID;

		$_SESSION['ezp_block_guests'] = implode(",",$collapsedEzBlocks);

		die('');
	}


	// Get current ezBlocks that are collapsed
	$request = $smcFunc['db_query']('', "
	SELECT
		value
	FROM {db_prefix}themes
	WHERE ID_MEMBER = " . $user_info['id'] . " AND ID_THEME = 0 AND variable =  'ezportal_ezblockcollapse'");
	$row = $smcFunc['db_fetch_assoc']($request);
	if (!empty($row['value']))
		$collapsedEzBlocks = explode(",",$row['value']);
	else
		$collapsedEzBlocks = array();

	$smcFunc['db_free_result']($request);

	if ($state == 0)
	{
		// Not collapsed
		foreach($collapsedEzBlocks as $key => $value)
		{
			if ($value == $layoutID)
				unset($collapsedEzBlocks[$key]);
		}

	}
	else
	{
		// Collapsed
		$collapsedEzBlocks[] = $layoutID;
	}

	$finalCollapseList = implode(",",$collapsedEzBlocks);

	$smcFunc['db_query']('', "REPLACE INTO {db_prefix}themes
					(ID_MEMBER, ID_THEME, variable, value)
				VALUES (" . $user_info['id'] .",0,'ezportal_ezblockcollapse','$finalCollapseList')");


    cache_put_data('ezportal_block_collaspe_' . $user_info['id'], null, 90);

	// Nothing else to do end it!
	die('');
}

function EzPortalVisibleSettings()
{
	global $txt, $context, $smcFunc;

	// Check Permission
	if (allowedTo('ezportal_blocks') == false)
		isAllowedTo('ezportal_manage');

	$column = 0;

	if (isset($_REQUEST['column']))
		$column	= (int) $_REQUEST['column'];

	$context['ezportal_column'] = $column;

	$layout = 0;

	if (isset($_REQUEST['block']))
		$layout	= (int) $_REQUEST['block'];

	$context['ezportal_block'] = $layout;

	// Loads all boards on the forum
	$context['ezportal_boards'] = array();
	$request = $smcFunc['db_query']('', "
	SELECT
		b.ID_BOARD, b.name AS bName, c.name AS cName
	FROM {db_prefix}boards AS b
		INNER JOIN {db_prefix}categories AS c ON (b.ID_CAT = c.ID_CAT)
	ORDER BY c.cat_order, b.board_order");
	while ($row = $smcFunc['db_fetch_assoc']($request))
		$context['ezportal_boards'][] = $row;
	$smcFunc['db_free_result']($request);

	// Load All Actions
	$context['ezportal_actions'] = array();
	$request = $smcFunc['db_query']('', "
	SELECT
		action, title, is_mod
	FROM {db_prefix}ezp_visible_actions
	");
	while ($row = $smcFunc['db_fetch_assoc']($request))
		$context['ezportal_actions'][] = $row;
	$smcFunc['db_free_result']($request);

	// Load all pages
	$context['ezportal_pages'] = array();
	$request = $smcFunc['db_query']('', "
	SELECT
		title, id_page
	FROM {db_prefix}ezp_page
	");
	while ($row = $smcFunc['db_fetch_assoc']($request))
		$context['ezportal_pages'][] = $row;
	$smcFunc['db_free_result']($request);

	// Set Enabled Boards and Actions
	if ($layout != 0)
	{
		$request = $smcFunc['db_query']('', "
		SELECT
			visibileactions, visibileboards, visibileareascustom, visibilepages
		FROM {db_prefix}ezp_block_layout
		WHERE id_layout = $layout");
	}

	if ($column != 0)
	{
		$request = $smcFunc['db_query']('', "
		SELECT
			visibileactions, visibileboards, visibileareascustom, visibilepages
		FROM {db_prefix}ezp_columns
		WHERE id_column = $column");
	}

	$row = $smcFunc['db_fetch_assoc']($request);

	if ($row['visibileactions'] != '')
		$visibleActions = explode(",",$row['visibileactions']);
	else
		$visibleActions = '';

	if ($row['visibileboards'] != '')
		$visibleBoards = explode(",",$row['visibileboards']);
	else
		$visibleBoards = '';

	if ($row['visibileareascustom'] != '')
		$visibleCustom = explode(",",$row['visibileareascustom']);
	else
		$visibleCustom = array();

	if ($row['visibilepages'] != '')
		$visibilepages = explode(",",$row['visibilepages']);
	else
		$visibilepages = array();


	$context['ezp_visibleCustom'] = $visibleCustom;

	$smcFunc['db_free_result']($request);

	foreach ($context['ezportal_actions'] as $key => $ezActions)
		{
			if ($visibleActions != '')
				if (in_array($ezActions['action'],$visibleActions))
					$context['ezportal_actions'][$key]['selected'] = 1;
				else
					$context['ezportal_actions'][$key]['selected'] = 0;
			else
				$context['ezportal_actions'][$key]['selected'] = 0;
		}

		foreach ($context['ezportal_boards'] as $key => $ezBoards)
		{
			if ($visibleBoards != '')
				if (in_array($ezBoards['ID_BOARD'],$visibleBoards))
					$context['ezportal_boards'][$key]['selected'] = 1;
				else
					$context['ezportal_boards'][$key]['selected'] = 0;
			else
				$context['ezportal_boards'][$key]['selected'] = 0;
		}

	// EzPortal pages
	foreach ($context['ezportal_pages'] as $key => $ezPages)
		{
			if ($visibilepages != '')
				if (in_array($ezPages['id_page'],$visibilepages))
					$context['ezportal_pages'][$key]['selected'] = 1;
				else
					$context['ezportal_pages'][$key]['selected'] = 0;
			else
				$context['ezportal_pages'][$key]['selected'] = 0;
		}

	EzPortalBlockMangerTabs();

	$context['ezp_all'] = ($visibleBoards == '' && $visibleActions == '' && empty($visibilepages)  && empty($visibleCustom)) ? 1 : 0;

	// Set the page title
	$context['page_title'] = $txt['ezp_txt_update_visible_options'];

	// Set the subtemplate
	$context['sub_template']  = 'ezportal_visible_options';

}

function EzPortalVisibleSettings2()
{
	global $smcFunc;

	// Check Permission
	if (allowedTo('ezportal_blocks') == false)
		isAllowedTo('ezportal_manage');

	$column = 0;

	if (isset($_REQUEST['column']))
		$column	= (int) $_REQUEST['column'];

	$layout = 0;

	if (isset($_REQUEST['block']))
		$layout	= (int) $_REQUEST['block'];

	$actionsArray = array();

	// get checked actions
	if (isset($_REQUEST['visactions']))
	{
		foreach ($_REQUEST['visactions'] as $action)
			$actionsArray[] = htmlspecialchars($action,ENT_QUOTES);
	}
	$finalActions = implode(",",$actionsArray);

	// Get checked visible boards
	$boardsArray = array();

	if (isset($_REQUEST['visboards']))
	{
		foreach ($_REQUEST['visboards'] as $board)
			$boardsArray[] = (int) $board;
	}
	$finalBoards = implode(",",$boardsArray);

	$pagesArray = array();
	if (isset($_REQUEST['vispages']))
	{
		foreach ($_REQUEST['vispages'] as $page)
			$pagesArray[] = (int) $page;
	}
	$finalPages = implode(",",$pagesArray);

	$customArray = array();
	if (isset($_REQUEST['cus']))
	{
		foreach ($_REQUEST['cus'] as $custom)
			$customArray[] = htmlspecialchars($custom,ENT_QUOTES);
	}
	$finalCustom = implode(",",$customArray);

	if (isset($_REQUEST['all']))
	{
		$finalActions = '';
		$finalBoards = '';
		$finalCustom = '';
		$finalPages = '';

	}

	// Update Column or ezBlock
	if ($layout != 0)
	{
		$smcFunc['db_query']('', "
		UPDATE {db_prefix}ezp_block_layout
		SET visibileactions = '$finalActions', visibileboards = '$finalBoards',
		visibileareascustom = '$finalCustom', visibilepages = '$finalPages'
		WHERE id_layout = $layout");

		$result = $smcFunc['db_query']('', "
		SELECT
			id_column
		FROM {db_prefix}ezp_block_layout
		WHERE id_layout = " .  $layout . " LIMIT 1");
		$row = $smcFunc['db_fetch_assoc']($result);

		cache_put_data('ezportal_column_' . $row['id_column'], null, 60);


	}

	if ($column != 0)
	{
		$smcFunc['db_query']('', "
		UPDATE {db_prefix}ezp_columns
		SET visibileactions = '$finalActions', visibileboards = '$finalBoards',
		visibileareascustom = '$finalCustom', visibilepages = '$finalPages'
		WHERE id_column = $column");

		cache_put_data('ezportal_columns', null, 60);
	}



	// Redirect to the ezBlock Manager
	redirectexit('action=admin;area=ezpblocks;sa=blocks');
}

function EzPortalAddVisibleAction()
{
	global $txt, $context;

	// Check Permission
	isAllowedTo('ezportal_blocks');

	EzPortalBlockMangerTabs();

	// Set the page title
	$context['page_title'] = $txt['ezp_txt_visible_add_new_action'];

	// Set the subtemplate
	$context['sub_template']  = 'ezportal_add_visible_action';

}

function EzPortalAddVisibleAction2()
{
	global $smcFunc;

	checkSession();

	// Check Permission
	isAllowedTo('ezportal_blocks');

	$actiontitle = $smcFunc['htmlspecialchars']($_REQUEST['actiontitle'],ENT_QUOTES);
	$newaction = htmlspecialchars($_REQUEST['newaction'],ENT_QUOTES);

	if ($actiontitle == '')
		fatal_lang_error('ezp_err_actiontitle',false);

	if ($newaction == '')
		fatal_lang_error('ezp_err_actionname',false);

	if (substr_count($newaction,'http://') > 0)
		fatal_lang_error('ezp_err_bad_action_url',false);

	if (substr_count($newaction,'https://') > 0)
		fatal_lang_error('ezp_err_bad_action_url',false);

	// Insert Action
	$smcFunc['db_query']('', "INSERT INTO {db_prefix}ezp_visible_actions
			(action, title, is_mod)
			VALUES ('$newaction', '$actiontitle',1)");

	// Redirect to the ezBlock Manager
	redirectexit('action=admin;area=ezpblocks;sa=blocks');
}

function EzPortalDeleteVisibleAction()
{
	global $smcFunc;

	// Check Permission
	isAllowedTo('ezportal_blocks');

	$newaction= htmlspecialchars($_REQUEST['newaction'],ENT_QUOTES);

	$smcFunc['db_query']('', "DELETE FROM {db_prefix}ezp_visible_actions
			WHERE action = '$newaction' LIMIT 1");

	redirectexit('action=admin;area=ezpblocks;sa=blocks');
}

function EzPortalAddShout()
{
	global $ezpSettings, $smcFunc, $user_info, $context, $txt;

	// Guests can't shout
	is_not_guest();

	loadLanguage('Errors');

	if (isset($_SESSION['ban']['cannot_post']))
		fatal_error($txt['ezp_shoutbox_error_banned_post'], false);



	$context['template_layers'] = array();
	if (!isset($_REQUEST['shout']))
		return;

	// Spam Protection
	spamProtection('ezportal');

	// Check if the shoutbox is enabled
	if ($ezpSettings['ezp_shoutbox_enable'] == 0)
		fatal_lang_error('ezp_shoutbox_error_disabled');



	$t = time();

	$shout = $smcFunc['htmlspecialchars']($_REQUEST['shout'], ENT_QUOTES);

	$shout = trim($shout);

	// Insert the shout
	if ($shout != '')
		$smcFunc['db_query']('', "INSERT INTO {db_prefix}ezp_shoutbox
			(id_member, date, shout)
			VALUES (" . $user_info['id'] . ", '$t',{string:shout})",
					array(
			'shout' => $shout,

			)
		)
		;

	cache_put_data('ezBlockshout', null, 60);

	ob_clean();

	if (isset($_SESSION['shoutbox_url']))
    {
		header("Location: " . $_SESSION['shoutbox_url']);
	   obExit(false);
    }
    else
		redirectexit('');


}

function EzPortalRemoveShout()
{
	global $smcFunc, $context;
	isAllowedTo('admin_forum');

    $context['template_layers'] = array();

	$shout = (int) $_REQUEST['shout'];

	$smcFunc['db_query']('', "DELETE FROM {db_prefix}ezp_shoutbox
			WHERE id_shout = '$shout' LIMIT 1");

	cache_put_data('ezBlockshout', null, 60);


	ob_clean();
	if (isset($_SESSION['shoutbox_url']))
    {
		header("Location: " . $_SESSION['shoutbox_url']);
	   obExit(false);
    }
    else
		redirectexit('');
}

function EzPortalViewShoutHistory()
{
	global $ezpSettings, $txt, $context, $scripturl, $smcFunc;

	if ($ezpSettings['ezp_shoutbox_archivehistory'] == 0)
		fatal_error($txt['ezp_err_viewshouthistory'], false);

	// Set the page title
	$context['page_title'] = $txt['ezp_txt_shouthistory'];

	// Set the subtemplate
	$context['sub_template']  = 'ezportal_shoutbox_history';

	// Get Count of Shoutbox
	$result = $smcFunc['db_query']('', "
	SELECT
		COUNT(*) total
	FROM {db_prefix}ezp_shoutbox");
	$totalRow = $smcFunc['db_fetch_assoc']($result);
	$smcFunc['db_free_result']($result);

	// Get the most Recent Shout Boxes first
	$context['start'] = (int) $_REQUEST['start'];

	$dbresult = $smcFunc['db_query']('', "
		SELECT
			s.shout, s.date, s.id_shout, s.id_member, m.real_name, mg.online_color, mg.ID_GROUP
		FROM {db_prefix}ezp_shoutbox AS s
		LEFT JOIN {db_prefix}members AS m ON (s.ID_MEMBER = m.ID_MEMBER)
		LEFT JOIN {db_prefix}membergroups AS mg ON (mg.ID_GROUP = IF(m.ID_GROUP = 0, m.ID_POST_GROUP, m.ID_GROUP))
		ORDER BY s.id_shout DESC LIMIT $context[start], " . $ezpSettings['ezp_shoutbox_history_number']);
	$context['ezshouts_history'] = array();
	while($shoutRow = $smcFunc['db_fetch_assoc']($dbresult))
		$context['ezshouts_history'][] = $shoutRow;
	$smcFunc['db_free_result']($dbresult);

	$context['page_index'] = constructPageIndex($scripturl . '?action=ezportal;sa=shouthistory' , $_REQUEST['start'], $totalRow['total'], 10);


}

function EzPortalMenuAdd()
{
	global $txt, $context, $smcFunc;

	// Check Permission
	if (allowedTo('ezportal_blocks') == false)
		isAllowedTo('ezportal_manage');

	$block = (int) $_REQUEST['block'];
	$context['ezp_layout_id'] = $block;


	// Get Permissions
	$dbresult = $smcFunc['db_query']('', "
	SELECT
		ID_GROUP, group_name
	FROM {db_prefix}membergroups
	WHERE min_posts = -1 AND ID_GROUP <> 3 ORDER BY group_name");
	while ($row = $smcFunc['db_fetch_assoc']($dbresult))
	{
		$context['groups'][$row['ID_GROUP']] = array(
			'ID_GROUP' => $row['ID_GROUP'],
			'group_name' => $row['group_name'],
			);
	}
	$smcFunc['db_free_result']($dbresult);

	$context['sub_template']  = 'ezportal_menu_add';
	$context['page_title'] = $txt['ezp_txt_menu_add'];
}

function EzPortalMenuAdd2()
{
	global $txt, $smcFunc;

	// Check Permission
	if (allowedTo('ezportal_blocks') == false)
		isAllowedTo('ezportal_manage');

	$layoutid = (int) $_REQUEST['layoutid'];
	$menutitle = $smcFunc['htmlspecialchars']($_REQUEST['menutitle'],ENT_QUOTES);
	$menulink = $smcFunc['htmlspecialchars']($_REQUEST['menulink'], ENT_QUOTES);
	$newwindow = isset($_REQUEST['newwindow']) ? 1 : 0;


	if (empty($menutitle))
		fatal_error($txt['ezp_err_menu_title'], false);

	if (empty($menulink))
		fatal_error($txt['ezp_err_menu_link'], false);


	// Get Permissions
	$permissionsArray = array();

	if (isset($_REQUEST['groups']))
	{
		foreach ($_REQUEST['groups'] as $rgroup)
			$permissionsArray[] = (int) $rgroup;
	}
	$finalPermissions = implode(",",$permissionsArray);

	// Insert the menu item
	$smcFunc['db_query']('', "INSERT INTO {db_prefix}ezp_menu
			(id_layout, id_order, title, linkurl, permissions, newwindow)
			VALUES ($layoutid, 1000,'$menutitle','$menulink','$finalPermissions',$newwindow)");

	ReOrderMenuItems($layoutid);

	cache_put_data('ezBlockMenu_' . $layoutid, null, 90);

	// Redirect back to the menu manager
	redirectexit("action=ezportal;sa=editblock;block=" . $layoutid);

}

function EzPortalMenuEdit()
{
	global $txt, $context, $smcFunc;

	// Check Permission
	if (allowedTo('ezportal_blocks') == false)
		isAllowedTo('ezportal_manage');

	// Get Permissions
	$dbresult = $smcFunc['db_query']('', "
	SELECT
		ID_GROUP, group_name
	FROM {db_prefix}membergroups
	WHERE min_posts = -1 AND ID_GROUP <> 3 ORDER BY group_name");
	while ($row = $smcFunc['db_fetch_assoc']($dbresult))
	{
		$context['groups'][$row['ID_GROUP']] = array(
			'ID_GROUP' => $row['ID_GROUP'],
			'group_name' => $row['group_name'],
			);
	}
	$smcFunc['db_free_result']($dbresult);

	$id = (int) $_REQUEST['id'];

	$dbresult = $smcFunc['db_query']('', "
	SELECT
		id_menu, id_layout, id_order, title, linkurl, permissions, newwindow, enabled
	FROM {db_prefix}ezp_menu
	WHERE id_menu = $id");
	$menuRow = $smcFunc['db_fetch_assoc']($dbresult);
	$smcFunc['db_free_result']($dbresult);

	$context['ezp_menu_row'] = $menuRow;


	$context['sub_template']  = 'ezportal_menu_edit';
	$context['page_title'] = $txt['ezp_txt_menu_edit'];

}

function EzPortalMenuEdit2()
{
	global $txt, $smcFunc;

	// Check Permission
	if (allowedTo('ezportal_blocks') == false)
		isAllowedTo('ezportal_manage');

	$layoutid = (int) $_REQUEST['layoutid'];
	$menuid = (int) $_REQUEST['menuid'];
	$menutitle = $smcFunc['htmlspecialchars']($_REQUEST['menutitle'],ENT_QUOTES);
	$menulink = $smcFunc['htmlspecialchars']($_REQUEST['menulink'], ENT_QUOTES);
	$newwindow = isset($_REQUEST['newwindow']) ? 1 : 0;
	$menuenabled = isset($_REQUEST['menuenabled']) ? 1 : 0;

	if (empty($menutitle))
		fatal_error($txt['ezp_err_menu_title'], false);

	if (empty($menulink))
		fatal_error($txt['ezp_err_menu_link'], false);


	// Get Permissions
	$permissionsArray = array();

	if (isset($_REQUEST['groups']))
	{
		foreach ($_REQUEST['groups'] as $rgroup)
			$permissionsArray[] = (int) $rgroup;
	}
	$finalPermissions = implode(",",$permissionsArray);

	// Update the menu item
	$smcFunc['db_query']('', "UPDATE {db_prefix}ezp_menu
	SET title = '$menutitle', linkurl = '$menulink', permissions = '$finalPermissions',
	newwindow = $newwindow, enabled = $menuenabled
	WHERE ID_MENU = $menuid
	");


	ReOrderMenuItems($layoutid);

	cache_put_data('ezBlockMenu_' . $layoutid, null, 90);

	// Redirect back to the menu manager
	redirectexit("action=ezportal;sa=editblock;block=" . $layoutid);
}

function EzPortalMenuDelete()
{
	global $txt, $context, $smcFunc;

	// Check Permission
	if (allowedTo('ezportal_blocks') == false)
		isAllowedTo('ezportal_manage');

	$id = (int) $_REQUEST['id'];

	$dbresult = $smcFunc['db_query']('', "
	SELECT
		id_menu, id_layout, id_order, title, linkurl, permissions, newwindow, enabled
	FROM {db_prefix}ezp_menu
	WHERE id_menu = $id");
	$menuRow = $smcFunc['db_fetch_assoc']($dbresult);
	$smcFunc['db_free_result']($dbresult);

	$context['ezp_menu_row'] = $menuRow;

	$context['sub_template']  = 'ezportal_menu_delete';
	$context['page_title'] = $txt['ezp_txt_menu_delete'];
}

function EzPortalMenuDelete2()
{
	global $smcFunc;
	// Check Permission
	if (allowedTo('ezportal_blocks') == false)
		isAllowedTo('ezportal_manage');

	$layoutid = (int) $_REQUEST['layoutid'];
	$menuid = (int) $_REQUEST['menuid'];
	$smcFunc['db_query']('', "
	DELETE FROM {db_prefix}ezp_menu
	WHERE id_menu = $menuid LIMIT 1");

	cache_put_data('ezBlockMenu_' . $layoutid, null, 90);

	// Redirect back to the menu manager
	redirectexit("action=ezportal;sa=editblock;block=" . $layoutid);
}

function ReOrderMenuItems($layoutid)
{
	global $smcFunc;

	$dbresult = $smcFunc['db_query']('', "
	SELECT
		id_menu
	FROM {db_prefix}ezp_menu
	WHERE id_layout = $layoutid ORDER BY id_order ASC");
	if($smcFunc['db_affected_rows']() != 0)
	{
		$count = 1;
		while($row2 = $smcFunc['db_fetch_assoc']($dbresult))
		{
			$smcFunc['db_query']('', "UPDATE {db_prefix}ezp_menu
			SET id_order = $count WHERE id_menu = " . $row2['id_menu']);
			$count++;
		}
	}
	$smcFunc['db_free_result']($dbresult);
}

function EzPortalMenuUp()
{
	global $smcFunc, $txt;

	// Check Permission
	if (allowedTo('ezportal_blocks') == false)
		isAllowedTo('ezportal_manage');
	//Get the id
	@$id = (int) $_REQUEST['id'];
	$layoutid = (int) $_REQUEST['block'];

	ReOrderMenuItems($layoutid);


	// Check if there is a category above it
	// First get our row order
	$dbresult1 = $smcFunc['db_query']('', "
	SELECT
		id_layout, id_order, id_menu
	FROM {db_prefix}ezp_menu
	WHERE id_menu = $id");
	$row = $smcFunc['db_fetch_assoc']($dbresult1);

	$id_layout = $row['id_layout'];
	$oldrow = $row['id_order'];
	$o = $row['id_order'];
	$o--;

	$smcFunc['db_free_result']($dbresult1);
	$dbresult = $smcFunc['db_query']('', "
	SELECT
		id_menu, id_order, id_layout
	FROM {db_prefix}ezp_menu
	WHERE id_layout = $id_layout AND id_order = $o");

	if ($smcFunc['db_affected_rows']()== 0)
		fatal_error($txt['ezp_err_menu_up'], false);
	$row2 = $smcFunc['db_fetch_assoc']($dbresult);


	// Swap the order Id's
	$smcFunc['db_query']('', "UPDATE {db_prefix}ezp_menu
		SET id_order = $oldrow WHERE id_menu = " .$row2['id_menu']);

	$smcFunc['db_query']('', "UPDATE {db_prefix}ezp_menu
		SET id_order = $o WHERE id_menu = $id");

	$smcFunc['db_free_result']($dbresult);

	cache_put_data('ezBlockMenu_' . $layoutid, null, 90);

	redirectexit("action=ezportal;sa=editblock;block=" . $layoutid);

}

function EzPortalMenuDown()
{
	global $smcFunc, $txt;

	// Check Permission
	if (allowedTo('ezportal_blocks') == false)
		isAllowedTo('ezportal_manage');

	//Get the id
	@$id = (int) $_REQUEST['id'];
	$layoutid = (int) $_REQUEST['block'];

	ReOrderMenuItems($layoutid);

	// Check if there is a category below it
	// First get our row order
	$dbresult1 = $smcFunc['db_query']('', "
	SELECT
		id_layout, id_menu, id_order
	FROM {db_prefix}ezp_menu
	WHERE id_menu = $id LIMIT 1");
	$row = $smcFunc['db_fetch_assoc']($dbresult1);
	$id_layout = $row['id_layout'];

	$oldrow = $row['id_order'];
	$o = $row['id_order'];
	$o++;

	$smcFunc['db_free_result']($dbresult1);
	$dbresult = $smcFunc['db_query']('', "
	SELECT
		id_menu, id_layout, id_order
	FROM {db_prefix}ezp_menu
	WHERE id_layout = $id_layout AND id_order = $o");
	if ($smcFunc['db_affected_rows']()== 0)
		fatal_error($txt['ezp_err_menu_down'], false);
	$row2 = $smcFunc['db_fetch_assoc']($dbresult);

	// Swap the order Id's
	$smcFunc['db_query']('', "UPDATE {db_prefix}ezp_menu
		SET id_order = $oldrow WHERE id_menu = " .$row2['id_menu']);

	$smcFunc['db_query']('', "UPDATE {db_prefix}ezp_menu
		SET id_order = $o WHERE id_menu = $id");

	cache_put_data('ezBlockMenu_' . $layoutid, null, 90);

	redirectexit("action=ezportal;sa=editblock;block=" . $layoutid);
}

function CleanUpDuplicateValues($layoutID)
{
	global $smcFunc;

	$result = $smcFunc['db_query']('', "SELECT COUNT(*) AS total, id_parameter FROM {db_prefix}ezp_block_parameters_values
			WHERE id_layout = $layoutID GROUP BY id_parameter ");
	while ($row = $smcFunc['db_fetch_assoc']($result))
	{
		if ($row['total'] > 1)
		{
			$limitValue  = ($row['total'] -1);
			$smcFunc['db_query']('', "DELETE FROM {db_prefix}ezp_block_parameters_values
		 WHERE id_parameter  = " . $row['id_parameter'] . " AND id_layout = $layoutID LIMIT $limitValue");

		}
	}
	$smcFunc['db_free_result']($result);

}

function EzPortalColumnState()
{
	global $smcFunc, $user_info, $context;

	checkSession('get');

	if (!isset($_REQUEST['columnid']))
		return;

	if (!isset($_REQUEST['state']))
		return;

	// Get the collapse state of the ezColumn
	$state = (int) $_REQUEST['state'];

	$columnID = (int) $_REQUEST['columnid'];

	$collapsedezColumns = array();

	// If we are a guest do a session to save it.
	if ($user_info['is_guest'])
	{

		if (isset($_SESSION['ezp_block_guests']) && !empty($_SESSION['ezp_column_guests']))
			$collapsedezColumns  = explode(',',$_SESSION['ezp_column_guests']);

		if ($state == 0)
		{
			foreach($collapsedezColumns as $key => $value)
			{
				if ($value == $columnID)
					unset($collapsedezColumns[$key]);
			}
		}
		else
			$collapsedezColumns[] = $columnID;

		$_SESSION['ezp_column_guests'] = implode(",",$collapsedezColumns);

		die('');
	}


	// Get current ezColumns that are collapsed
	$request = $smcFunc['db_query']('', "
	SELECT
		value
	FROM {db_prefix}themes
	WHERE ID_MEMBER = " . $user_info['id'] . " AND ID_THEME = 0 AND variable = 'ezportal_ezcolumncollapse'");
	$row = $smcFunc['db_fetch_assoc']($request);
	if (!empty($row['value']))
		$collapsedezColumns = explode(",",$row['value']);
	else
		$collapsedezColumns = array();

	$smcFunc['db_free_result']($request);

	if ($state == 0)
	{
		// Not collapsed
		foreach($collapsedezColumns as $key => $value)
		{
			if ($value == $columnID)
				unset($collapsedezColumns[$key]);
		}

	}
	else
	{
		// Collapsed
		$collapsedezColumns[] = $columnID;
	}

	$finalCollapseList = implode(",",$collapsedezColumns);

	$smcFunc['db_query']('', "REPLACE INTO {db_prefix}themes
					(ID_MEMBER, ID_THEME, variable, value)
				VALUES (" . $user_info['id'] .",0,'ezportal_ezcolumncollapse','$finalCollapseList')");

    cache_put_data('ezportal_col_collaspe_' . $user_info['id'], $collapsedezColumns, 90);
	// Nothing else to do end it!
	die('');
}

function EzPortalShoutFrame()
{
	global $context, $ezpSettings;

	$num = (int) $_REQUEST['num'];

	if ($num > 100)
		$num = 100;

	$context['template_layers'] = array();
	$context['sub_template'] = 'ezpotal_shoutbox';

	 EzBlockShoutBoxBlock(array(),$num,true);

	 if ($ezpSettings['ezp_shoutbox_refreshseconds'] < 5)
		 $ezpSettings['ezp_shoutbox_refreshseconds']  = 5;

	 $context['html_headers'] .= '<meta http-equiv="refresh" content="' . $ezpSettings['ezp_shoutbox_refreshseconds'] . '" />';
}

function EzPortalCheckInfo()
{
    global $modSettings, $boardurl;

    if (isset($modSettings['ezp_copyrightkey']))
    {
        $m = 40;
        if (!empty($modSettings['ezp_copyrightkey']))
        {
            if ($modSettings['ezp_copyrightkey'] == sha1($m . '-' . $boardurl))
            {
                return false;
            }
            else
                return true;
        }
    }

    return true;
}

function EzPortal_CopyrightRemoval()
{
    global $context, $mbname, $txt;
	isAllowedTo('ezportal_manage');

    if (isset($_REQUEST['save']))
    {

        $ezp_copyrightkey = $_REQUEST['ezp_copyrightkey'];

        updateSettings(
    	array(
    	'ezp_copyrightkey' => $ezp_copyrightkey,
    	)

    	);
    }


	EzPortalAdminTabs();

	$context['page_title'] =  $txt['ezp_txt_copyrightremoval'];

	$context['sub_template']  = 'ezportalcopyright';
}

function EzPortalDeleteAllShoutHistory()
{
    global $context, $txt;
    isAllowedTo('ezportal_manage');

	EzPortalAdminTabs();

	$context['page_title'] =  $txt['ezp_txt_deleteallshoutbox'];

	$context['sub_template']  = 'ezportal_delete_shoutboxhistory';
}

function EzPortalDeleteAllShoutHistory2()
{
    global $smcFunc;

    isAllowedTo('ezportal_manage');

   	$smcFunc['db_query']('', "DELETE FROM {db_prefix}ezp_shoutbox");

    cache_put_data('ezBlockshout', null, 60);


    redirectexit("action=ezportal;sa=shouthistory");
}

function EzPortalUpdatePageCount()
{
	global $smcFunc;

	$ezportal_menucount = 0;

	$dbresult = $smcFunc['db_query']('', "
	SELECT
		COUNT(*) AS total
	FROM {db_prefix}ezp_page
	WHERE showinmenu = 1");
	$row = $smcFunc['db_fetch_assoc']($dbresult);

	$ezportal_menucount = $row['total'];

	    updateSettings(
    	array(
    	'ezportal_menucount' => $ezportal_menucount,
    	'settings_updated' => time(),
    	)

    	);
}

function EzPortalCheck_htaccess()
{
	global $boarddir;

	if (file_exists($boarddir . "/.htaccess"))
	{
		$checkContents = file_get_contents($boarddir . "/.htaccess");

		if (substr_count($checkContents,"ezPortal") == 0)
		{
			// Append
		$data = '
# ezPortal MOD Starts
RewriteEngine on
RewriteBase /
RewriteRule ^pages/([-_!~*()$a-zA-Z0-9]+)-([0-9]*)?$ ./index.php?action=ezportal;sa=page;p=$2 [L,QSA]
# ezPortal MOD ENDS
';

		if (substr_count($checkContents,"PRETTYURLS") == 0)
			file_put_contents($boarddir . "/.htaccess",$data, FILE_APPEND);
		else
			file_put_contents($boarddir . "/.htaccess",$data . $checkContents );



		}

	}
	else
	{
		// create the file
		$data = '# ezPortal MOD Starts
RewriteEngine on
RewriteBase /
RewriteRule ^pages/([-_!~*()$a-zA-Z0-9]+)-([0-9]*)?$ ./index.php?action=ezportal;sa=page;p=$2 [L,QSA]
# ezPortal MOD ENDS
';

		file_put_contents($boarddir . "/.htaccess",$data);

	}

}
?>