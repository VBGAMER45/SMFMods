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
	global $sourcedir, $ezPortalVersion, $context, $ezpSettings, $boardurl, $boarddir;

	// Hold Current Version
	$ezPortalVersion = '5.6';

	// Subs for EzPortal
	require_once($sourcedir . '/Subs-EzPortalMain.php');

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
	loadtemplate('EzPortal');

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
	// Check Permission
	isAllowedTo('ezportal_manage');

	checkSession();

	$ezp_url = htmlspecialchars($_REQUEST['ezp_url'],ENT_QUOTES);
	$ezp_path = htmlspecialchars($_REQUEST['ezp_path'],ENT_QUOTES);
	$ezp_portal_enable = isset($_REQUEST['ezp_portal_enable']) ? 1 : 0;
	$ezp_allowstats = isset($_REQUEST['ezp_allowstats']) ? 1 : 0;
	$ezp_portal_homepage_title = htmlspecialchars($_REQUEST['ezp_portal_homepage_title'],ENT_QUOTES);

	$ezp_hide_edit_delete = isset($_REQUEST['ezp_hide_edit_delete']) ? 1 : 0;
	$ezp_disable_tinymce_html = isset($_REQUEST['ezp_disable_tinymce_html']) ? 1 : 0;
	$ezp_pages_seourls = isset($_REQUEST['ezp_pages_seourls']) ? 1 : 0;

	$ezp_shoutbox_enable = isset($_REQUEST['ezp_shoutbox_enable']) ? 1 : 0;
	$ezp_shoutbox_showdate = isset($_REQUEST['ezp_shoutbox_showdate']) ? 1 : 0;
	$ezp_shoutbox_archivehistory = isset($_REQUEST['ezp_shoutbox_archivehistory']) ? 1 : 0;

	$ezp_shoutbox_hidesays = isset($_REQUEST['ezp_shoutbox_hidesays']) ? 1 : 0;
	$ezp_shoutbox_hidedelete = isset($_REQUEST['ezp_shoutbox_hidedelete']) ? 1 : 0;
	$ezp_shoutbox_history_number = (int) $_REQUEST['ezp_shoutbox_history_number'];
	$ezp_shoutbox_refreshseconds = (int) $_REQUEST['ezp_shoutbox_refreshseconds'];
	$ezp_shoutbox_showsmilies = isset($_REQUEST['ezp_shoutbox_showsmilies']) ? 1 : 0;
	$ezp_shoutbox_showbbc = isset($_REQUEST['ezp_shoutbox_showbbc']) ? 1 : 0;

    $ezp_disablemobiledevices = isset($_REQUEST['ezp_disablemobiledevices']) ? 1 : 0;

    EzPortalCheck_htaccess();

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

    'ezp_disablemobiledevices' => $ezp_disablemobiledevices,
	'ezp_pages_seourls' => $ezp_pages_seourls,

	)
	);

	redirectexit('action=ezportal;sa=settings');

}

function EzPortalBlocksMain()
{
	global $txt, $context, $db_prefix;

	// Check Permission
	if (allowedTo('ezportal_blocks') == false)
		isAllowedTo('ezportal_manage');

	EzPortalBlockMangerTabs();

	// Set the subtemplate
	$context['sub_template']  = 'ezportal_blocks';

	// Set the page title
	$context['page_title'] = $txt['ezp_blocks'];

	// Get Columns
	$dbresult = db_query("
	SELECT
		id_column, column_title, active
	FROM {$db_prefix}ezp_columns
	ORDER BY id_column ASC", __FILE__, __LINE__);
	$context['ezPortalAdminColumns'] = array();

	while($row = mysql_fetch_assoc($dbresult))
	{
		$blocks = array();

		// Get all the ezBlocks under these columns
		$dbresult2 = db_query("
		SELECT
			l.customtitle, l.id_layout, l.active, l.id_order
		FROM {$db_prefix}ezp_block_layout AS l
		WHERE l.id_column = " . $row['id_column'] . "
		ORDER BY l.id_order ASC", __FILE__, __LINE__);
		while($row2 = mysql_fetch_assoc($dbresult2))
			$blocks[] = $row2;
		mysql_free_result($dbresult2);

		$row['blocks'] = $blocks;

		$context['ezPortalAdminColumns'][] = $row;

	}
	mysql_free_result($dbresult);

}

function EzPortalAddBlock()
{
	global $txt, $context, $db_prefix;

	// Check Permission
	if (allowedTo('ezportal_blocks') == false)
		isAllowedTo('ezportal_manage');

	EzPortalBlockMangerTabs();

	// Set the subtemplate
	$context['sub_template']  = 'ezportal_add_block';

	// Set the page title
	$context['page_title'] = $txt['ezp_addblock'];

	// Get Portal ezBlock List
	$dbresult = db_query("
	SELECT
		id_block, blocktitle
	FROM {$db_prefix}ezp_blocks
	ORDER BY id_block ASC
	", __FILE__, __LINE__);
	$context['ezp_blocks'] = array();
	while ($row = mysql_fetch_assoc($dbresult))
    {
        if (isset($txt[$row['blocktitle']]['title']))
            $row['blocktitle'] = $txt[$row['blocktitle']]['title'];

		$context['ezp_blocks'][] = $row;
    }
	mysql_free_result($dbresult);

	if (isset($_REQUEST['column']))
		$column = (int) $_REQUEST['column'];
	else
		$column = 0;

	$context['ezportal_column'] = $column;

}

function EzPortalAddBlock2()
{
	global $txt, $context, $db_prefix, $sourcedir, $ezpSettings;

	// Check Permission
	if (allowedTo('ezportal_blocks') == false)
		isAllowedTo('ezportal_manage');

	EzPortalBlockMangerTabs();

	// Set the subtemplate
	$context['sub_template']  = 'ezportal_add_block2';

	// Set the page title
	$context['page_title'] = $txt['ezp_addblock'];

	if (isset($_REQUEST['column']))
		$column = (int) $_REQUEST['column'];
	else
		$column = 0;

	$context['ezportal_column'] = $column;

	// Get the blocktype
	$context['ezportal_blocktype'] = (int) $_REQUEST['blocktype'];

	// Get Columns
	$dbresult = db_query("
	SELECT
		id_column, column_title
	FROM {$db_prefix}ezp_columns
	ORDER BY column_order ASC
	", __FILE__, __LINE__);
	$context['ezp_columns'] = array();
	while ($row = mysql_fetch_assoc($dbresult))
		$context['ezp_columns'][] = $row;
	mysql_free_result($dbresult);

	// Get Permissions
	$dbresult = db_query("
	SELECT
		ID_GROUP, groupName
	FROM {$db_prefix}membergroups
	WHERE minPosts = -1 AND ID_GROUP <> 3 ORDER BY groupName", __FILE__, __LINE__);
	while ($row = mysql_fetch_assoc($dbresult))
	{
		$context['groups'][$row['ID_GROUP']] = array(
			'ID_GROUP' => $row['ID_GROUP'],
			'groupName' => $row['groupName'],
			);
	}
	mysql_free_result($dbresult);

	// Look up ezBlock information
	$dbresult = db_query("
	SELECT
		blockdata, data_editable, blocktitle, blocktype
	FROM {$db_prefix}ezp_blocks
	WHERE id_block = " . $context['ezportal_blocktype'], __FILE__, __LINE__);
	$row = mysql_fetch_assoc($dbresult);

    if (isset($txt[$row['blocktitle']]['title']))
        $row['blocktitle'] = $txt[$row['blocktitle']]['title'];

	$context['ezp_block_data'] = $row;
	mysql_free_result($dbresult);

	$context['ezp_showtinymcetoggle'] = false;
	if ($context['ezp_block_data']['blocktype'] == 'HTML'   && empty($ezpSettings['ezp_disable_tinymce_html']))
	{
		SetupEditor();
		$context['ezp_showtinymcetoggle'] = true;
	}


	// Look up any parameters for this ezBlock
	$dbresult = db_query("
	SELECT
		title, defaultvalue, required, parameter_type, id_parameter
	FROM {$db_prefix}ezp_block_parameters
	WHERE id_block = " . $context['ezportal_blocktype']  . " ORDER BY id_order ASC"
	, __FILE__, __LINE__);
	$context['ezp_block_parameters'] = array();

	$editorCreated = false;

	while ($row = mysql_fetch_assoc($dbresult))
	{
	   if (isset($txt[$context['ezp_block_data']['blocktitle']]['param'][$row['title']]))
            $row['title'] = $txt[$context['ezp_block_data']['blocktitle']]['param'][$row['title']];

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
				$request = db_query("
				SELECT
					b.ID_BOARD, b.name AS bName, c.name AS cName
				FROM {$db_prefix}boards AS b, {$db_prefix}categories AS c
				WHERE b.ID_CAT = c.ID_CAT ORDER BY c.catOrder, b.boardOrder", __FILE__, __LINE__);
				while ($row2 = mysql_fetch_assoc($request))
					$context['ezportal_boards'][$row2['ID_BOARD']] = $row2['cName'] . ' - ' . $row2['bName'];
				mysql_free_result($request);

		}
		if ($row['parameter_type'] == 'select')
		{
			$context['ezp_select_' . $row['id_parameter']] = array();
			$request = db_query("
				SELECT
					selectvalue,selecttext
				FROM {$db_prefix}ezp_paramaters_select
				WHERE id_parameter = " . $row['id_parameter'] . " ORDER BY id_select ASC", __FILE__, __LINE__);
				while ($row2 = mysql_fetch_assoc($request))
					$context['ezp_select_' . $row['id_parameter']][$row2['selectvalue']] = $row2['selecttext'];
				mysql_free_result($request);
		}

		if ($row['parameter_type'] == 'bbc')
		{
			/// Used for the editor
			require_once($sourcedir . '/Subs-Post.php');
			$context['post_box_name'] = 'bbcfield' . $row['id_parameter'] .'';
			$context['post_form'] = 'frmaddblock';
		}



	}
	mysql_free_result($dbresult);

	// Load the EzBlock icons
	$dbresult = db_query("
	SELECT
		id_icon, icon
	FROM {$db_prefix}ezp_icons
	ORDER BY icon ASC
	", __FILE__, __LINE__);
	$context['ezp_icons'] = array();
	while ($row = mysql_fetch_assoc($dbresult))
		$context['ezp_icons'][] = $row;
	mysql_free_result($dbresult);


}

function EzPortalAddBlock3()
{
	global $txt, $db_prefix, $func;

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
	$dbresult = db_query("
	SELECT
		b.blockdata bdata, b.data_editable
	FROM {$db_prefix}ezp_blocks AS b
	WHERE id_block = $blocktype LIMIT 1
	", __FILE__, __LINE__);
	$blockrow = mysql_fetch_assoc($dbresult);
	mysql_free_result($dbresult);

	$blocktitle = $func['htmlspecialchars']($_REQUEST['blocktitle'],ENT_QUOTES);
	$icon = (int) $_REQUEST['icon'];

	$can_collapse = isset($_REQUEST['can_collapse']) ? 1 : 0;
	$hidetitlebar = isset($_REQUEST['hidetitlebar']) ? 1 : 0;
	$hidemobile = isset($_REQUEST['hidemobile']) ? 1 : 0;
	$showonlymobile = isset($_REQUEST['showonlymobile']) ? 1 : 0;
	$block_header_class = $func['htmlspecialchars']($_REQUEST['block_header_class'],ENT_QUOTES);
	$block_body_class = $func['htmlspecialchars']($_REQUEST['block_body_class'],ENT_QUOTES);

	$blockdata = '';
	if (isset($_REQUEST['blockdata']))
		$blockdata = htmlentities($_REQUEST['blockdata'],ENT_QUOTES);

	if ($blockrow['data_editable'] == 0)
		$blockdata = $blockrow['bdata'];


	if (isset($_REQUEST['parameter']))
		$parameters = $_REQUEST['parameter'];

	// Validate all the parameters if they exist
	$dbresult = db_query("
	SELECT
		defaultvalue, required, parameter_type, id_parameter, title
	FROM {$db_prefix}ezp_block_parameters
	WHERE id_block = " . $blocktype
	, __FILE__, __LINE__);
	while ($row = mysql_fetch_assoc($dbresult))
	{
		// Checked passed data type
		if ($row['parameter_type'] == 'int')
			$parameters[$row['id_parameter']] = (int) $parameters[$row['id_parameter']];
		if ($row['parameter_type'] == 'float')
			$parameters[$row['id_parameter']] = (float) $parameters[$row['id_parameter']];
		if ($row['parameter_type'] == 'multiboardselect')
		{
			$parameters[$row['id_parameter']] = implode(',',$parameters[$row['id_parameter']]);

		}

		if ($row['parameter_type'] == 'bbc')
		{
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
	mysql_free_result($dbresult);

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
	db_query("INSERT INTO {$db_prefix}ezp_block_layout
			(id_column, id_block, id_order, customtitle, permissions, blockmanagers, can_collapse, active, blockdata, id_icon,hidetitlebar,hidemobile,showonlymobile,block_header_class,block_body_class)
			VALUES ($column, $blocktype,1000,'$blocktitle','$finalPermissions','$finalManagers',$can_collapse, 1,'$blockdata','$icon',$hidetitlebar,$hidemobile,$showonlymobile,'$block_header_class','$block_body_class')", __FILE__, __LINE__);

	$layoutID = db_insert_id();

	// Reorder ezBlocks
	ReOrderBlocksbyColumn($column);

	cache_put_data('ezportal_column_' . $column, null, 60);

	// Insert Parameters
	if (isset($parameters))
    {
		foreach ($parameters  as $key => $param)
		{
			// Make it db safe
			$paramData = addslashes($param);
			// Insert the parameter
			db_query("INSERT INTO {$db_prefix}ezp_block_parameters_values
				(id_layout, id_parameter, data)
				VALUES ($layoutID,$key,'$paramData')", __FILE__, __LINE__);

		}

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
	redirectexit('action=ezportal;sa=visiblesettings;block=' .$layoutID);


}

function EzPortalEditBlock()
{
	global $txt, $context, $db_prefix, $user_info, $sourcedir, $ezpSettings;

	$block = (int) $_REQUEST['block'];

	// Get ezBlock Data
	$dbresult = db_query("
	SELECT
		l.id_column, l.id_block, l.id_layout, l.customtitle, l.permissions, l.can_collapse,
		l.blockmanagers, l.blockdata, b.data_editable, b.blocktitle, b.blocktype,
		l.visibileactions, l.visibileboards, l.visibileareascustom, l.id_icon, l.hidetitlebar,
		l.hidemobile,l.showonlymobile, l.block_header_class, l.block_body_class  
	FROM {$db_prefix}ezp_block_layout AS l
		INNER JOIN {$db_prefix}ezp_blocks AS b ON (l.id_block = b.id_block)
	WHERE l.id_layout = $block LIMIT 1
	", __FILE__, __LINE__);
	$row = mysql_fetch_assoc($dbresult);
    if (isset($txt[$row['blocktitle']]['title']))
        $row['blocktitle'] = $txt[$row['blocktitle']]['title'];
	$context['ezp_block_info'] = $row;
	mysql_free_result($dbresult);

	$context['ezp_showtinymcetoggle'] = false;
	if ($context['ezp_block_info']['blocktype'] == 'HTML'   && empty($ezpSettings['ezp_disable_tinymce_html']))
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
			$dbresult = db_query("
		SELECT
			m.id_menu, m.id_order, m.linkurl, m.title, m.enabled
		FROM {$db_prefix}ezp_menu as m
			INNER JOIN {$db_prefix}ezp_block_layout AS l ON (l.id_layout = m.id_layout)
		WHERE l.id_layout = $block
		ORDER BY id_order ASC
		", __FILE__, __LINE__);
		$context['ezp_menu_block_items'] = array();
		while($row = mysql_fetch_assoc($dbresult))
		{
			$context['ezp_menu_block_items'][] = $row;
		}
		mysql_free_result($dbresult);
	}


	EzPortalBlockMangerTabs();

	// Set the subtemplate
	$context['sub_template']  = 'ezportal_edit_block';

	// Set the page title
	$context['page_title'] = $txt['ezp_editblock'];

	// Get Columns
	$dbresult = db_query("
	SELECT
		id_column, column_title
	FROM {$db_prefix}ezp_columns
	ORDER BY column_order ASC
	", __FILE__, __LINE__);
	$context['ezp_columns'] = array();
	while ($row = mysql_fetch_assoc($dbresult))
		$context['ezp_columns'][] = $row;
	mysql_free_result($dbresult);

	// Get Permissions
	$dbresult = db_query("
	SELECT
		ID_GROUP, groupName
	FROM {$db_prefix}membergroups
	WHERE minPosts = -1 AND ID_GROUP <> 3 ORDER BY groupName", __FILE__, __LINE__);
	while ($row = mysql_fetch_assoc($dbresult))
	{
		$context['groups'][$row['ID_GROUP']] = array(
			'ID_GROUP' => $row['ID_GROUP'],
			'groupName' => $row['groupName'],
			);
	}
	mysql_free_result($dbresult);

	// Look up any parameters for this ezBlock
	$dbresult = db_query("
	SELECT
		p.title, p.defaultvalue, p.required, p.parameter_type, p.id_parameter, v.data
	FROM {$db_prefix}ezp_block_parameters AS p
		LEFT JOIN {$db_prefix}ezp_block_parameters_values AS v ON (p.id_parameter = v.id_parameter AND v.id_layout = $block)
	WHERE p.id_block = " . $context['ezp_block_info']['id_block']  . " ORDER BY p.id_order ASC"
	, __FILE__, __LINE__);
	$context['ezp_block_parameters'] = array();
	$editorCreated = false;
	while ($row = mysql_fetch_assoc($dbresult))
	{

 	   if (isset($txt[$context['ezp_block_info']['blocktitle']]['param'][$row['title']]))
            $row['title'] = $txt[$context['ezp_block_info']['blocktitle']]['param'][$row['title']];

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
			$request = db_query("
			SELECT
				b.ID_BOARD, b.name AS bName, c.name AS cName
			FROM {$db_prefix}boards AS b, {$db_prefix}categories AS c
			WHERE b.ID_CAT = c.ID_CAT ORDER BY c.catOrder, b.boardOrder", __FILE__, __LINE__);
			while ($row2 = mysql_fetch_assoc($request))
				$context['ezportal_boards'][$row2['ID_BOARD']] = $row2['cName'] . ' - ' . $row2['bName'];
			mysql_free_result($request);

		}

		if ($row['parameter_type'] == 'select')
		{
			$context['ezp_select_' . $row['id_parameter']] = array();
			$request = db_query("
				SELECT
					selectvalue,selecttext
				FROM {$db_prefix}ezp_paramaters_select
				WHERE id_parameter = " .  $row['id_parameter'] . " ORDER BY id_select ASC", __FILE__, __LINE__);
				while ($row2 = mysql_fetch_assoc($request))
					$context['ezp_select_' . $row['id_parameter']][$row2['selectvalue']] = $row2['selecttext'];
				mysql_free_result($request);
		}

		if ($row['parameter_type'] == 'bbc')
		{
			/// Used for the editor
			require_once($sourcedir . '/Subs-Post.php');
			$context['post_box_name'] = 'bbcfield' . $row['id_parameter'] .'';
			$context['post_form'] = 'frmeditblock';
		}

	}
	mysql_free_result($dbresult);

	// Load the EzBlock icons
	$dbresult = db_query("
	SELECT
		id_icon, icon
	FROM {$db_prefix}ezp_icons
	ORDER BY icon ASC
	", __FILE__, __LINE__);
	$context['ezp_icons'] = array();
	while ($row = mysql_fetch_assoc($dbresult))
		$context['ezp_icons'][] = $row;
	mysql_free_result($dbresult);


}

function EzPortalEditBlock2()
{
	global $txt, $db_prefix, $user_info, $func;

	checkSession();

	$block = (int) $_REQUEST['block'];
	if (empty($block))
		fatal_lang_error('ezp_err_no_block_selected',false);

	$column = (int) $_REQUEST['column'];

	if (empty($column))
		fatal_lang_error('ezp_err_no_column_selected', false);

	// Get ezBlock Data
	$dbresult = db_query("
	SELECT
		l.id_block, l.blockmanagers, l.id_column, l.id_order,b.blockdata bdata, b.data_editable

	FROM {$db_prefix}ezp_block_layout AS l
		INNER JOIN {$db_prefix}ezp_blocks AS b ON (l.id_block = b.id_block)
	WHERE l.id_layout = $block LIMIT 1
	", __FILE__, __LINE__);
	$row = mysql_fetch_assoc($dbresult);
	$context['ezp_block_info'] = $row;
	mysql_free_result($dbresult);

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

	$blocktitle = $func['htmlspecialchars']($_REQUEST['blocktitle'],ENT_QUOTES);
	$icon = (int) $_REQUEST['icon'];

	$can_collapse = isset($_REQUEST['can_collapse']) ? 1 : 0;
	$hidetitlebar = isset($_REQUEST['hidetitlebar']) ? 1 : 0;
	$hidemobile = isset($_REQUEST['hidemobile']) ? 1 : 0;
	$showonlymobile = isset($_REQUEST['showonlymobile']) ? 1 : 0;

	$block_header_class = $func['htmlspecialchars']($_REQUEST['block_header_class'],ENT_QUOTES);
	$block_body_class = $func['htmlspecialchars']($_REQUEST['block_body_class'],ENT_QUOTES);

	$blockdata = '';
	if (isset($_REQUEST['blockdata']))
		$blockdata = htmlentities($_REQUEST['blockdata'],ENT_QUOTES);

	// If it is not editable set it to the default data
	if ($row['data_editable'] == 0)
		$blockdata = $row['bdata'];

	// Validate all the parameters if they exist
	if (isset($_REQUEST['parameter']))
		$parameters = $_REQUEST['parameter'];

	$dbresult = db_query("
	SELECT
		defaultvalue, required, parameter_type, id_parameter,title
	FROM {$db_prefix}ezp_block_parameters
	WHERE id_block = " . $context['ezp_block_info']['id_block']
	, __FILE__, __LINE__);
	while ($row = mysql_fetch_assoc($dbresult))
	{
		// Checked passed data type
		if ($row['parameter_type'] == 'int')
			$parameters[$row['id_parameter']] = (int) $parameters[$row['id_parameter']];
		if ($row['parameter_type'] == 'float')
			$parameters[$row['id_parameter']] = (float) $parameters[$row['id_parameter']];
		if ($row['parameter_type'] == 'multiboardselect')
		{
			$parameters[$row['id_parameter']] = implode(',',$parameters[$row['id_parameter']]);

		}

		if ($row['parameter_type'] == 'bbc')
		{
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
	mysql_free_result($dbresult);


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
	db_query("UPDATE {$db_prefix}ezp_block_layout
	      SET id_column = $column, customtitle = '$blocktitle', permissions = '$finalPermissions', blockmanagers = '$finalManagers',
	      can_collapse = $can_collapse, blockdata = '$blockdata', id_icon = '$icon', hidetitlebar = '$hidetitlebar', hidemobile = '$hidemobile', showonlymobile = '$showonlymobile',
	      block_header_class = '$block_header_class', block_body_class = '$block_body_class'  
	      WHERE id_layout = '$block'
	      ", __FILE__, __LINE__);

	// Update EzBlock Parameters.
	if (isset($parameters))
		foreach ($parameters  as $key => $param)
		{
			// Make it db safe
			$paramData = addslashes($param);
			db_query("UPDATE {$db_prefix}ezp_block_parameters_values
				SET data = '$paramData' WHERE id_layout = $block AND id_parameter = $key
				", __FILE__, __LINE__);
			$affectedRows = mysql_affected_rows();
			if ($affectedRows == 0)
			{
					db_query("INSERT INTO {$db_prefix}ezp_block_parameters_values
				(id_layout, id_parameter, data)
				VALUES ($block,$key,'$paramData')", __FILE__, __LINE__);
			}
		}

    if (isset($parameters))
        cache_put_data('ezportal_layoutparm_' . $block,  null, 120);



	if ($context['ezp_block_info']['id_column'] != $column)
	{
		cache_put_data('ezportal_column_' . $context['ezp_block_info']['id_column'], null, 60);

		db_query("UPDATE {$db_prefix}ezp_block_layout
	      SET id_order = 1000
	      WHERE id_layout = '$block'
	      ", __FILE__, __LINE__);

		ReOrderBlocksbyColumn($context['ezp_block_info']['id_column']);
	}

	cache_put_data('ezportal_column_' . $column, null, 60);

	CleanUpDuplicateValues($block);



	// Reorder ezBlocks
	ReOrderBlocksbyColumn($column);



	// Redirect to the ezBlock Manager
	redirectexit('action=ezportal;sa=blocks');

}

function EzPortalDeleteBlock()
{
	global $txt, $context, $db_prefix;

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
	$dbresult = db_query("
	SELECT
		customtitle
	FROM {$db_prefix}ezp_block_layout
	WHERE id_layout = " . $context['ezp_block_layout_id'] . " LIMIT 1", __FILE__, __LINE__);
	$row = mysql_fetch_assoc($dbresult);
	$context['ezp_block_layout_title'] = $row['customtitle'];
	mysql_free_result($dbresult);

}

function EzPortalDeleteBlock2()
{
	global $db_prefix;

	// Check Permission
	isAllowedTo('ezportal_blocks');

	checkSession();

	$blockid = (int) $_REQUEST['blockid'];

	$result = db_query("
	SELECT
		id_column
	FROM {$db_prefix}ezp_block_layout
	WHERE id_layout = " . $blockid . " LIMIT 1", __FILE__, __LINE__);
	$row = mysql_fetch_assoc($result);
	$column = $row['id_column'];
	mysql_free_result($result);
	cache_put_data('ezportal_column_' . $column, null, 60);

	// Delete the ezBlock
	db_query("
	DELETE FROM {$db_prefix}ezp_block_layout
	WHERE id_layout = " . $blockid . " LIMIT 1", __FILE__, __LINE__);

	// Delete ezBlock Parameters
	db_query("
	DELETE FROM {$db_prefix}ezp_block_parameters_values
	WHERE id_layout = " . $blockid, __FILE__, __LINE__);



	cache_put_data('ezportal_columns', null, 60);

	// Redirect to the Block Manager
	redirectexit('action=ezportal;sa=blocks');

}

function EzPortalPageManager()
{
	global $txt, $context, $db_prefix, $scripturl;

	// Check Permission
	if (allowedTo('ezportal_page') == false)
		isAllowedTo('ezportal_manage');

	// Do Tabs
	EzPortalPageManagerTabs();

	$context['start'] = $_REQUEST['start'];

	// Get Total Pages
	$dbresult = db_query("
	SELECT
		COUNT(*) AS total
	FROM {$db_prefix}ezp_page", __FILE__, __LINE__);
	$rowTotal = mysql_fetch_assoc($dbresult);
	$total = $rowTotal['total'];
	mysql_free_result($dbresult);

	$dbresult = db_query("
	SELECT
		id_page,date, title,views
	FROM {$db_prefix}ezp_page
	ORDER BY id_page DESC
	LIMIT $context[start], 10", __FILE__, __LINE__);
	$context['ezp_pages'] = array();
	while ($row = mysql_fetch_assoc($dbresult))
		$context['ezp_pages'][] = $row;
	mysql_free_result($dbresult);

	$context['page_index'] = constructPageIndex($scripturl . '?action=ezportal;sa=pagemanager', $_REQUEST['start'], $total, 10);

	// Set the subtemplate
	$context['sub_template']  = 'ezportal_pagemanager';

	// Set the page title
	$context['page_title'] = $txt['ezp_pagemanager'];

}

function EzPortalAddPage()
{
	global $txt, $context, $db_prefix;

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
	$dbresult = db_query("
	SELECT
		ID_GROUP, groupName
	FROM {$db_prefix}membergroups
	WHERE minPosts = -1 AND ID_GROUP <> 3 ORDER BY groupName", __FILE__, __LINE__);
	$context['groups'] = array();
	while ($row = mysql_fetch_assoc($dbresult))
	{
		$context['groups'][$row['ID_GROUP']] = array(
			'ID_GROUP' => $row['ID_GROUP'],
			'groupName' => $row['groupName'],
			);
	}
	mysql_free_result($dbresult);

	// Setup Editor
	SetupEditor();

}

function EzPortalAddPage2()
{
	global $txt, $db_prefix, $func;

	// Check Permission
	if (allowedTo('ezportal_page') == false)
		isAllowedTo('ezportal_manage');

	checkSession();

	$pagetitle = $func['htmlspecialchars']($_REQUEST['pagetitle'],ENT_QUOTES);
	$pagecontent = htmlentities($_REQUEST['pagecontent'],ENT_QUOTES);
	$metatags = htmlentities($_REQUEST['metatags'],ENT_QUOTES);

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

	$addDate = time();
	db_query("INSERT INTO {$db_prefix}ezp_page
			(date, title, content, permissions, is_html,metatags)
			VALUES ($addDate, '$pagetitle', '$pagecontent', '$finalPermissions', 1,'$metatags')", __FILE__, __LINE__);

	// Redirect to the Page Manager
	redirectexit('action=ezportal;sa=pagemanager');
}


function EzPortalEditPage()
{
	global $txt, $context, $db_prefix;

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
	$dbresult = db_query("
	SELECT
		ID_GROUP, groupName
	FROM {$db_prefix}membergroups
	WHERE minPosts = -1 AND ID_GROUP <> 3 ORDER BY groupName", __FILE__, __LINE__);
	$context['groups'] = array();
	while ($row = mysql_fetch_assoc($dbresult))
	{
		$context['groups'][$row['ID_GROUP']] = array(
			'ID_GROUP' => $row['ID_GROUP'],
			'groupName' => $row['groupName'],
			);
	}
	mysql_free_result($dbresult);

	// Setup Editor
	SetupEditor();

	// Look up the page and get it ready for the template
	$dbresult = db_query("
	SELECT
		id_page, title, content, permissions, metatags
	FROM {$db_prefix}ezp_page
	WHERE id_page = $pageID LIMIT 1", __FILE__, __LINE__);
	$row = mysql_fetch_assoc($dbresult);
	$context['ezp_editpage_data'] = $row;
	$context['ezp_editpage_data']['content'] = html_entity_decode($row['content']);

	mysql_free_result($dbresult);

}

function EzPortalEditPage2()
{
	global $db_prefix, $func;

	checkSession();

	// Check Permission
	if (allowedTo('ezportal_page') == false)
		isAllowedTo('ezportal_manage');

	if (empty($_REQUEST['p']))
		fatal_lang_error('ezp_err_no_page_selected', false);

	// Store the Page ID
	$pageID = (int) $_REQUEST['p'];

	$pagetitle = $func['htmlspecialchars']($_REQUEST['pagetitle'],ENT_QUOTES);
	$pagecontent = htmlentities($_REQUEST['pagecontent'],ENT_QUOTES);
	$metatags = htmlentities($_REQUEST['metatags'],ENT_QUOTES);

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

	db_query("
	UPDATE {$db_prefix}ezp_page
	SET title = '$pagetitle', content = '$pagecontent', permissions = '$finalPermissions', metatags = '$metatags' WHERE id_page = $pageID LIMIT 1", __FILE__, __LINE__);

    cache_put_data('ezportal_page_' . $pageID, null, 120);

	// Redirect to the Page Manager
	redirectexit('action=ezportal;sa=pagemanager');
}

function EzPortalDeletePage()
{
	global $txt, $context, $db_prefix;

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

	$dbresult = db_query("
	SELECT
		id_page, title
	FROM {$db_prefix}ezp_page
	WHERE id_page = $pageID LIMIT 1", __FILE__, __LINE__);
	$row = mysql_fetch_assoc($dbresult);
	$context['ezp_deletepage_data'] = $row;
	mysql_free_result($dbresult);

}

function EzPortalDeletePage2()
{
	global $db_prefix;

	checkSession();

	// Check Permission
	if (allowedTo('ezportal_page') == false)
		isAllowedTo('ezportal_manage');

	if (empty($_REQUEST['p']))
		fatal_lang_error('ezp_err_no_page_selected', false);

	// Store the Page ID
	$pageID = (int) $_REQUEST['p'];

	// Delete the entry
	db_query("
	DELETE FROM {$db_prefix}ezp_page
	WHERE id_page = $pageID LIMIT 1", __FILE__, __LINE__);

	// Redirect to the Page Manager
	redirectexit('action=ezportal;sa=pagemanager');
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

	// Set the subtemplate
	$context['sub_template']  = 'ezportal_credits';

	// Set the page title
	$context['page_title'] = $txt['ezp_credits'];
}

function EzPortalAdminTabs()
{
	global $txt, $context, $txt, $scripturl;

	adminIndex('ezportal_admin');

	$context['current_action2'] = 'admin';

	$context['admin_tabs'] = array(
			'title' => $txt['ezp_tab_settings'],
			'description' => $txt['ezp_tab_settings_desc'],
			'tabs' => array(),
		);
	$context['admin_tabs']['tabs'][] = array(
			'title' => $txt['ezp_settings'],
			'description' => '',
			'href' => $scripturl . '?action=ezportal;sa=settings',
			'is_selected' => $_REQUEST['sa'] == 'settings',
		);


	$context['admin_tabs']['tabs'][] = array(
				'title' => $txt['ezp_modules'],
				'description' => '',
				'href' => $scripturl . '?action=ezportal;sa=modules',
				'is_selected' => $_REQUEST['sa'] == 'modules',
			);




	$context['admin_tabs']['tabs'][] = array(
				'title' => $txt['ezp_import'],
				'description' => '',
				'href' => $scripturl . '?action=ezportal;sa=import',
				'is_selected' => $_REQUEST['sa'] == 'import',
			);


  	$context['admin_tabs']['tabs'][] = array(
				'title' => $txt['ezp_txt_ordercopyright'],
				'description' => '',
				'href' => $scripturl . '?action=ezportal;sa=copyright',
				'is_selected' => $_REQUEST['sa'] == 'copyright',
			);



	$context['admin_tabs']['tabs'][] = array(
				'title' => $txt['ezp_credits'],
				'description' => '',
				'href' => $scripturl . '?action=ezportal;sa=credits',
				'is_selected' => $_REQUEST['sa'] == 'credits',
			);


	$context['admin_tabs']['tabs'][count($context['admin_tabs']['tabs']) - 1]['is_last'] = true;

}

function EzPortalBlockMangerTabs()
{
	global $txt, $context, $txt, $scripturl;

	adminIndex('ezportal_blocks');

	$context['current_action2'] = 'admin';

	$context['admin_tabs'] = array(
			'title' => $txt['ezp_tab_blockmanager'],
			'description' => $txt['ezp_tab_blockmanager_desc'],
			'tabs' => array(),
		);
	$context['admin_tabs']['tabs'][] = array(
			'title' => $txt['ezp_blocks'],
			'description' => '',
			'href' => $scripturl . '?action=ezportal;sa=blocks',
			'is_selected' => $_REQUEST['sa'] == 'blocks',
		);
	$context['admin_tabs']['tabs'][] = array(
			'title' => $txt['ezp_download_blocks'],
			'description' => '',
			'href' => $scripturl . '?action=ezportal;sa=downloadblock',
			'is_selected' => $_REQUEST['sa'] == 'downloadblock',
		);
	$context['admin_tabs']['tabs'][] = array(
			'title' => $txt['ezp_installed_blocks'],
			'description' => '',
			'href' => $scripturl . '?action=ezportal;sa=installedblocks',
			'is_selected' => $_REQUEST['sa'] == 'installedblocks',
		);

	$context['admin_tabs']['tabs'][count($context['admin_tabs']['tabs']) - 1]['is_last'] = true;

}

function EzPortalPageManagerTabs()
{
	global $txt, $context, $txt, $scripturl;

	adminIndex('ezportal_pages');

	$context['current_action2'] = 'admin';

	$context['admin_tabs'] = array(
		'title' =>  $txt['ezp_tab_pagemanager'],
		'description' => $txt['ezp_tab_pagemanager_desc'],
		'tabs' => array(),
	);
	$context['admin_tabs']['tabs'][] = array(
		'title' => $txt['ezp_pagemanager'],
		'description' => '',
		'href' => $scripturl . '?action=ezportal;sa=pagemanager',
		'is_selected' => $_REQUEST['sa'] == 'pagemanager',
	);

	$context['admin_tabs']['tabs'][count($context['admin_tabs']['tabs']) - 1]['is_last'] = true;

}

function EzPortalImportPortal()
{
	global $txt, $context, $db_name, $db_prefix;

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

	// Check if they have TP, MX, or SP, DP installed
	$dbresult = db_query("
	SHOW TABLES
	FROM `$db_name`", __FILE__, __LINE__);
	while ($tableRow = mysql_fetch_row($dbresult))
	{
		// Check if Portal MX is installed
		if ($tableRow[0] == ($db_prefix . 'portamx_settings'))
			$context['portals']['MX'] = true;

		// Check if Simple Portal is installed
		if ($tableRow[0] == ($db_prefix . 'sp_functions'))
			$context['portals']['SP'] = true;

		// Check if Tiny Portal is installed
		if ($tableRow[0] == ($db_prefix . 'tp_settings'))
			$context['portals']['TP'] = true;
	}
	mysql_free_result($dbresult);

}

function EzPortalImportPortal2()
{
	global $sourcedir;

	// Check Permission
	isAllowedTo('ezportal_manage');

	// Check which type we are converting from
	$type = $_REQUEST['type'];

	// Load the conversion module
	require_once($sourcedir . '/Subs-EzPortal-Convert.php');

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
		case 'dp':
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
	global $ezpSettings, $modSettings, $forum_version, $ezPortalVersion, $db_prefix;

	// Check if we allow stats collection
	if ($ezpSettings['ezp_allowstats'] != 1)
		return;

	// Get Total Pages
	$dbresult = db_query("
	SELECT
		COUNT(*) AS total
	FROM {$db_prefix}ezp_page
	", __FILE__, __LINE__);
	$totalRow = mysql_fetch_assoc($dbresult);
	$totalPages = $totalRow['total'];
	mysql_free_result($dbresult);

	// Total ezBlocks
	$dbresult = db_query("
	SELECT
		COUNT(*) AS total
	FROM {$db_prefix}ezp_blocks
	", __FILE__, __LINE__);
	$totalRow = mysql_fetch_assoc($dbresult);
	$totalBlocks = $totalRow['total'];
	mysql_free_result($dbresult);

	// Collect stats function if enabled we will collect some stats on your forum
	// These include forum size,member count, post count, and portal stats

	echo base64_encode("TT:" . $modSettings['totalTopics']  ."#TMSG:" . $modSettings['totalMessages']  ."#TMEM:" . $modSettings['totalMembers']  ."#SMFVER" . $forum_version . "#EZPVER" . $ezPortalVersion . "#TP" . $totalPages . "#TB" . $totalBlocks);

	// End output
	die('');

}

function EzPortalViewPage()
{
	global $context, $db_prefix, $user_info;

	if (empty($_REQUEST['p']))
		fatal_lang_error('ezp_err_no_page_selected', false);

	// Store the Page ID
	$pageID = (int) $_REQUEST['p'];

	// Check If Page exists
    if (($row = cache_get_data('ezportal_page_' . $pageID, 120)) == null)
    {
    	$dbresult = db_query("
    	SELECT
    		id_page, title, content, permissions, metatags
    	FROM {$db_prefix}ezp_page
    	WHERE id_page = $pageID LIMIT 1", __FILE__, __LINE__);

    	$row = mysql_fetch_assoc($dbresult);
    	mysql_free_result($dbresult);

        cache_put_data('ezportal_page_' . $pageID, $row, 120);

    }

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
	$context['ezp_pagecontent'] = html_entity_decode($row['content'],ENT_QUOTES);

		// Auto Embed Media Pro
		global $sourcedir;
		if (file_exists($sourcedir . '/AutoEmbedMediaPro.php'))
		{

				require_once($sourcedir . '/AutoEmbedMediaPro.php');
			if (function_exists("MediaProProcess"))
				$context['ezp_pagecontent'] = MediaProProcess($context['ezp_pagecontent']);

		}




	$context['html_headers'] .=  html_entity_decode($row['metatags'],ENT_QUOTES);

	// Setup the subtemplate
	$context['sub_template']  = 'ezportal_viewpage';

	// Updated Page Views
	db_query("
	UPDATE {$db_prefix}ezp_page
	SET views = views + 1
	WHERE id_page = $pageID LIMIT 1", __FILE__, __LINE__);
}

function EzPortalEditColumn()
{
	global $txt, $context, $db_prefix;

	// Check Permission
	isAllowedTo('ezportal_blocks');

	EzPortalBlockMangerTabs();

	$columnID = (int) $_REQUEST['column'];

	// Set Page Title
	$context['page_title'] = $txt['ezp_editcolumn'];

	// Setup the subtemplate
	$context['sub_template']  = 'ezportal_edit_column';

	// Get Column Information
	$dbresult = db_query("
	SELECT
		id_column, column_title, can_collapse, column_width, column_percent, active
	FROM {$db_prefix}ezp_columns
	WHERE id_column = $columnID LIMIT 1", __FILE__, __LINE__);
	$row = mysql_fetch_assoc($dbresult);
	$context['ezp_column_data'] = $row;
	mysql_free_result($dbresult);


}
function EzPortalEditColumn2()
{
	global $db_prefix;

	// Check Permission
	isAllowedTo('ezportal_blocks');

	checkSession();

	// Get column ID
	$column = (int) $_REQUEST['column'];
	$active = (int) $_REQUEST['active'];
	$columnwidth = (int) $_REQUEST['columnwidth'];
	$columnpercent = (int) $_REQUEST['columnpercent'];
	$can_collapse = isset($_REQUEST['can_collapse']) ? 1 : 0;

	if ($columnwidth < 0)
		$columnwidth = 0;

	// Update category
	db_query("
	UPDATE {$db_prefix}ezp_columns
	SET column_width = '$columnwidth', column_percent = '$columnpercent', active = $active,
	can_collapse = '$can_collapse'
	WHERE id_column = " . $column . " LIMIT 1", __FILE__, __LINE__);



	cache_put_data('ezportal_columns', null, 60);

	// Redirect to the Block Manager
	redirectexit('action=ezportal;sa=blocks');
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
	global $txt, $ezpSettings;

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
	redirectexit('action=ezportal;sa=installedblocks');

}

function SetupEzPortal()
{
	global $sourcedir, $context, $maintenance, $user_info, $ID_MEMBER, $ezpSettings, $boarddir, $boardurl, $db_prefix, $settings, $disableEzPortal;

	// If wireless skip the ezPortal stuff.
	if (WIRELESS)
		return;

     // Disable ezPortal
    if (!empty($disableEzPortal))
        return;

	// Actions that should not load EzPortal
	if (isset($_REQUEST['action']))
	{
		if (in_array($_REQUEST['action'],array('.xml','dlattach','quotefast','findmember', 'helpadmin', 'printpage', 'spellcheck')))
			return;
	}
	// Check for XML if found ignore it
	if (isset($_REQUEST['xml']))
	{
		return;
	}

	// Check maintenance mode
	if (!empty($maintenance) && $maintenance == 1  && $context['user']['is_admin'] == false)
		return;


	// Subs for EzPortal
	require_once($sourcedir . '/Subs-EzPortalMain.php');

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
				return;

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

	// Load EzPortal template
	loadtemplate('EzPortal');

	if (!empty($context['linktree']))
      foreach ($context['linktree'] as $key => $tree)
         if (strpos($tree['url'], '#') !== false && strpos($tree['url'], 'action=forum#') === false)
            $context['linktree'][$key]['url'] = str_replace('#', '?action=forum#', $tree['url']);

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
		if (!isset($_REQUEST['board']) && !isset($_REQUEST['topic']))
			$is_vis_on_board_index = true;
	}


	// Check for any ezBlocks that are collapsed.
	$collapsedEzBlocks = array();
    $collapsedEzColumns = array();
	if (!$context['user']['is_guest'])
	{
	   if (($collapsedEzBlocks = cache_get_data('ezportal_block_collaspe_' . $ID_MEMBER, 90)) == null)
       {
    		$request = db_query("
    		SELECT
    			value
    		FROM {$db_prefix}themes
    		WHERE ID_MEMBER = $ID_MEMBER AND ID_THEME = 0 AND variable = 'ezportal_ezblockcollapse'", __FILE__, __LINE__);
    		$row = mysql_fetch_assoc($request);

    		if (!empty($row['value']))
    			$collapsedEzBlocks = explode(",",$row['value']);
    		else
    			$collapsedEzBlocks = array();

    		mysql_free_result($request);

            cache_put_data('ezportal_block_collaspe_' . $ID_MEMBER, $collapsedEzBlocks, 90);
        }

        if (($collapsedEzColumns = cache_get_data('ezportal_col_collaspe_' . $ID_MEMBER, 90)) == null)
        {
    		$request = db_query("
    		SELECT
    			value
    		FROM {$db_prefix}themes
    		WHERE ID_MEMBER = $ID_MEMBER AND ID_THEME = 0 AND variable = 'ezportal_ezcolumncollapse'", __FILE__, __LINE__);
    		$row = mysql_fetch_assoc($request);
    		if (!empty($row['value']))
    			$collapsedEzColumns = explode(",",$row['value']);
    		else
    			$collapsedEzColumns = array();

    		mysql_free_result($request);

            cache_put_data('ezportal_col_collaspe_' . $ID_MEMBER, $collapsedEzColumns, 90);
        }

	}


	// Load ezBlocks and ezColumns
	$ezColumnsCache = array();

	if (($ezColumnsCache = cache_get_data('ezportal_columns', 60)) == null)
	{
		$dbresult = db_query("
		SELECT
			id_column, column_title, column_width, column_percent, active, can_collapse, visibileactions, visibileboards,
			visibileareascustom, visibilepages
		FROM {$db_prefix}ezp_columns
		WHERE active = 1
		ORDER BY id_column ASC", __FILE__, __LINE__);

		while($row = mysql_fetch_assoc($dbresult))
		{
			$ezColumnsCache[] = $row;
		}
		mysql_free_result($dbresult);
		cache_put_data('ezportal_columns', $ezColumnsCache, 60);
	}

	$context['ezPortalColumns'] = array();

	if (!empty($ezColumnsCache))
	foreach($ezColumnsCache as $row)
	{

		// Check if the column is collasped
			if (!$context['user']['is_guest'])
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
			/*
			 if (isset($_REQUEST['Blog']))
			 	$visibleAction = true;
			*/

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
		if ($row['visibileboards'] == '' && $row['visibileactions'] == '' && $row['visibileareascustom'] == ''  && $row['visibilepages'] == '')
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
		$ezColumnsBlockColumnCache = array();
		if (($ezColumnsBlockColumnCache = cache_get_data('ezportal_column_' . $row['id_column'], 60)) == null)
		{
			$dbresult2 = db_query("
			SELECT
				l.customtitle, l.id_layout, l.active, l.id_order, l.blockdata, b.blocktype,
				l.id_block, l.permissions, l.can_collapse, l.blockmanagers, b.blockdata bdata,
				l.visibileactions, l.visibileboards, l.hidetitlebar, l.visibileareascustom, i.icon, l.visibilepages,
				l.hidemobile,l.showonlymobile, l.block_header_class, l.block_body_class 
			FROM {$db_prefix}ezp_block_layout AS l
				INNER JOIN {$db_prefix}ezp_blocks AS b ON (b.id_block = l.id_block)
			LEFT JOIN {$db_prefix}ezp_icons AS i ON (l.id_icon = i.id_icon)
			WHERE l.active = 1 AND l.id_column = " . $row['id_column'] . "
			ORDER BY l.id_order ASC", __FILE__, __LINE__);

			while($row2 = mysql_fetch_assoc($dbresult2))
			{
				$ezColumnsBlockColumnCache[] = $row2;
			}
			mysql_free_result($dbresult2);

			cache_put_data('ezportal_column_' . $row['id_column'], $ezColumnsBlockColumnCache, 60);
		}


		if (!empty($ezColumnsBlockColumnCache) > 0)
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


			// Setup wether they can manage this ezblock
			$row2['IsManager'] = $isManager;

			// Get all parameters for this ezblock
			if (strtolower($row2['blocktype']) == 'php' || strtolower($row2['blocktype']) == 'builtin')
			{
				// Get any parameters
				$parameters = array();
                if (($parameters= cache_get_data('ezportal_layoutparm_' . $row2['id_layout'], 120)) == null)
                {
    				$dbresult3 = db_query("
    				SELECT
    					p.id_parameter, p.parameter_name, v.data
    				FROM {$db_prefix}ezp_block_parameters AS p
    					INNER JOIN {$db_prefix}ezp_block_parameters_values AS v ON (p.id_parameter = v.id_parameter)
    				WHERE v.id_layout = " . $row2['id_layout']
    				, __FILE__, __LINE__);
    				while($parameterRow = mysql_fetch_assoc($dbresult3))
    					$parameters[] = $parameterRow;
    				mysql_free_result($dbresult3);

                    cache_put_data('ezportal_layoutparm_' . $row2['id_layout'], $parameters, 120);
                }

				$row2['parameters'] = $parameters;
			}

			// Check if it is collapsed
			if (!$context['user']['is_guest'])
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
		$context['ezPortalColumns'][] = $row;
	}


	// Check Permission
	$context['ezportal_block_manager'] = false;
	if (allowedTo('ezportal_blocks') == true)
		$context['ezportal_block_manager'] = true;

	// Expand and collapse code
	$context['html_headers'] .= '
	<script language="JavaScript" type="text/javascript"><!-- // --><![CDATA[
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
				document.getElementById(myEzItem).style.height = "0px";
				document.getElementById(myEzItem).style.overflowY = "hidden";
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
				document.layers[myEzItem].height = "0px";
				document.layers[myEzItem].overflowY = "hidden";
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
				document.all[myEzItem].style.height = "0px";
				document.all[myEzItem].style.overflowY = "hidden";
			}
		}

		';


		$context['html_headers'] .= 'EzPortalSaveBlockState(ezBlockID,ezCollapseState,isBlock);';

		$context['html_headers'] .= '
		if (myImage.src == "' . $settings['images_url'] . '/collapse.gif")
			myImage.src = "' . $settings['images_url'] . '/expand.gif";
		else
			myImage.src = "' . $settings['images_url'] . '/collapse.gif";

	}';


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
	global $db_prefix, $func;

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
			$title = $func['htmlspecialchars']($titleArray[$key],ENT_QUOTES);
			$order = (int) $orderArray[$key];

			// Update the ezBlock
			db_query("
			UPDATE {$db_prefix}ezp_block_layout
			SET active = $active, id_order = $order, customtitle = '$title'
			WHERE id_layout = $key", __FILE__, __LINE__);
		}

	// Finally Reorder the ezBlocks
	ReOrderBlocksbyColumn($column);

	cache_put_data('ezportal_column_' . $column, null, 60);

	// Redirect to the ezBlock Manager
	redirectexit('action=ezportal;sa=blocks');
}

function ReOrderBlocksbyColumn($columnID)
{
	global $db_prefix;

	$dbresult = db_query("
	SELECT
		id_order, id_layout
	FROM {$db_prefix}ezp_block_layout
	WHERE id_column = $columnID ORDER BY id_order ASC", __FILE__, __LINE__);
	if (db_affected_rows() != 0)
	{
		$count = 1;
		while($row2 = mysql_fetch_assoc($dbresult))
		{
			db_query("UPDATE {$db_prefix}ezp_block_layout
			SET id_order = $count WHERE id_layout  = " . $row2['id_layout'], __FILE__, __LINE__);
			$count++;
		}
	}
	mysql_free_result($dbresult);
}

function EzPortalInstalledBlocks()
{
	global $txt, $context, $db_prefix;

	// Check Permission
	isAllowedTo('ezportal_blocks');

	EzPortalBlockMangerTabs();

	// Set the subtemplate
	$context['sub_template']  = 'ezportal_installed_blocks';

	// Set the page title
	$context['page_title'] = $txt['ezp_installed_blocks'];

	// Get Columns
	$dbresult = db_query("
	SELECT
		id_block, blocktitle, blockauthor, blockwebsite, blockversion, forumversion, no_delete
	FROM {$db_prefix}ezp_blocks
	ORDER BY id_block DESC", __FILE__, __LINE__);
	$context['ezportal_installed_blocks'] = array();

	while($row = mysql_fetch_assoc($dbresult))
		$context['ezportal_installed_blocks'][] = $row;
	mysql_free_result($dbresult);

}

function EzPortalForumHomePage()
{
	global $context, $mbname, $ezpSettings;

	// Setup Page Title
	if (empty($ezpSettings['ezp_portal_homepage_title']))
		$context['page_title'] = $mbname;
	else
		$context['page_title'] = $ezpSettings['ezp_portal_homepage_title'];

	loadtemplate('EzPortal');
	$context['sub_template']  = 'ezportal_frontpage';
}

function EzPortalCheck_syntax($code)
{
    return @eval('return true;' . $code);
}

function EzPortalUninstallBlock()
{
	global $txt, $context, $db_prefix;

	// Check Permission
	if (allowedTo('ezportal_blocks') == false)
		isAllowedTo('ezportal_manage');

	checkSession('get');

	$block = (int) $_REQUEST['block'];
	$blocksInUse = '';
	// Check if ezBlock is in use if so we can't delete it.
	$dbresult = db_query("
	SELECT
		id_order, id_layout, customtitle
	FROM {$db_prefix}ezp_block_layout
	WHERE id_block = $block", __FILE__, __LINE__);
	while($row = mysql_fetch_assoc($dbresult))
		$blocksInUse .= $row['customtitle'] . '<br />';

	mysql_free_result($dbresult);

	if ($blocksInUse != '')
		EzPortalMessage($txt['ezp_txt_uninstall_block2'],$txt['ezp_err_uninstall_block'] . $blocksInUse);
	else
	{
		// Uninstall the ezBlock
		db_query("
		DELETE FROM {$db_prefix}ezp_blocks
		WHERE id_block = $block", __FILE__, __LINE__);

		// Delete ezBlock Parameters
		db_query("
		DELETE FROM {$db_prefix}ezp_block_parameters
		WHERE id_block = $block", __FILE__, __LINE__);

		// Redirect to the installed ezBlock list
		redirectexit('action=ezportal;sa=installedblocks');
	}
}

function EzPortalBlockState()
{
	global $db_prefix, $ID_MEMBER, $context;

	checkSession('get');

    $context['template_layers'] = array();

	if (!isset($_REQUEST['blockid']))
		return;

	if (!isset($_REQUEST['state']))
		return;

	// Get the collapse state of the ezBlock
	$state = (int) $_REQUEST['state'];

	$layoutID = (int) $_REQUEST['blockid'];

	// If we are a guest do a session to save it.
	if ($context['user']['is_guest'])
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


	// Get current ezBlocks that are collsapsed
	$request = db_query("
	SELECT
		value
	FROM {$db_prefix}themes
	WHERE ID_MEMBER = $ID_MEMBER AND ID_THEME = 0 AND variable =  'ezportal_ezblockcollapse'", __FILE__, __LINE__);
	$row = mysql_fetch_assoc($request);

	if (!empty($row['value']))
		$collapsedEzBlocks = explode(",",$row['value']);
	else
		$collapsedEzBlocks = array();

	mysql_free_result($request);

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

	db_query("REPLACE INTO {$db_prefix}themes
					(ID_MEMBER, ID_THEME, variable, value)
				VALUES ($ID_MEMBER,0,'ezportal_ezblockcollapse','$finalCollapseList')", __FILE__, __LINE__);

    cache_put_data('ezportal_block_collaspe_' . $ID_MEMBER, null, 90);

	// Nothing else to do end it!
	die('');
}

function EzPortalVisibleSettings()
{
	global $txt, $context, $db_prefix;

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
	$request = db_query("
	SELECT
		b.ID_BOARD, b.name AS bName, c.name AS cName
	FROM {$db_prefix}boards AS b
		INNER JOIN {$db_prefix}categories AS c ON (b.ID_CAT = c.ID_CAT)
	ORDER BY c.catOrder, b.boardOrder", __FILE__, __LINE__);
	while ($row = mysql_fetch_assoc($request))
		$context['ezportal_boards'][] = $row;
	mysql_free_result($request);

	// Load All Actions
	$context['ezportal_actions'] = array();
	$request = db_query("
	SELECT
		action, title, is_mod
	FROM {$db_prefix}ezp_visible_actions
	", __FILE__, __LINE__);
	while ($row = mysql_fetch_assoc($request))
		$context['ezportal_actions'][] = $row;
	mysql_free_result($request);

	// Load all pages
	$context['ezportal_pages'] = array();
	$request = db_query("
	SELECT
		title, id_page
	FROM {$db_prefix}ezp_page
	", __FILE__, __LINE__);
	while ($row = mysql_fetch_assoc($request))
		$context['ezportal_pages'][] = $row;
	mysql_free_result($request);

	// Set Enabled Boards and Actions
	if ($layout != 0)
	{
		$request = db_query("
		SELECT
			visibileactions, visibileboards, visibileareascustom, visibilepages
		FROM {$db_prefix}ezp_block_layout
		WHERE id_layout = $layout", __FILE__, __LINE__);
	}

	if ($column != 0)
	{
		$request = db_query("
		SELECT
			visibileactions, visibileboards, visibileareascustom, visibilepages
		FROM {$db_prefix}ezp_columns
		WHERE id_column = $column", __FILE__, __LINE__);
	}

	$row = mysql_fetch_assoc($request);

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

	mysql_free_result($request);

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

	$context['ezp_all'] = ($visibleBoards == '' && $visibleActions == '' && empty($visibilepages)  &&  empty($visibleCustom)) ? 1 : 0;

	// Set the page title
	$context['page_title'] = $txt['ezp_txt_update_visible_options'];

	// Set the subtemplate
	$context['sub_template']  = 'ezportal_visible_options';

}

function EzPortalVisibleSettings2()
{
	global $db_prefix;

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
		db_query("
		UPDATE {$db_prefix}ezp_block_layout
		SET visibileactions = '$finalActions', visibileboards = '$finalBoards',
		visibileareascustom = '$finalCustom', visibilepages = '$finalPages'
		WHERE id_layout = $layout", __FILE__, __LINE__);


		$result = db_query("
		SELECT
			id_column
		FROM {db_prefix}ezp_block_layout
		WHERE id_layout = " .  $layout . " LIMIT 1", __FILE__, __LINE__);

		$row = mysql_fetch_assoc($result);

		cache_put_data('ezportal_column_' . $row['id_column'], null, 60);

	}

	if ($column != 0)
	{
		db_query("
		UPDATE {$db_prefix}ezp_columns
		SET visibileactions = '$finalActions', visibileboards = '$finalBoards',
		visibileareascustom = '$finalCustom', visibilepages = '$finalPages'
		WHERE id_column = $column", __FILE__, __LINE__);

		cache_put_data('ezportal_columns', null, 60);
	}



	// Redirect to the ezBlock Manager
	redirectexit('action=ezportal;sa=blocks');
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
	global $db_prefix, $func;

	checkSession();

	// Check Permission
	isAllowedTo('ezportal_blocks');

	$actiontitle = $func['htmlspecialchars']($_REQUEST['actiontitle'],ENT_QUOTES);
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
	db_query("INSERT INTO {$db_prefix}ezp_visible_actions
			(action, title, is_mod)
			VALUES ('$newaction', '$actiontitle',1)", __FILE__, __LINE__);

	// Redirect to the ezBlock Manager
	redirectexit('action=ezportal;sa=blocks');
}

function EzPortalDeleteVisibleAction()
{
	global $db_prefix;

	// Check Permission
	isAllowedTo('ezportal_blocks');

	$newaction= htmlspecialchars($_REQUEST['newaction'],ENT_QUOTES);

	db_query("DELETE FROM {$db_prefix}ezp_visible_actions
			WHERE action = '$newaction' LIMIT 1", __FILE__, __LINE__);

	redirectexit('action=ezportal;sa=blocks');
}

function EzPortalAddShout()
{
	global $ezpSettings, $db_prefix, $ID_MEMBER, $func, $context, $txt;

	// Guests can't shout
	is_not_guest();

	// Spam Protection
	spamProtection('ezportal');

	if (isset($_SESSION['ban']['cannot_post']))
		fatal_error($txt['ezp_shoutbox_error_banned_post'], false);


	$context['template_layers'] = array();
	if (!isset($_REQUEST['shout']))
		return;

	// Check if the shoutbox is enabled
	if ($ezpSettings['ezp_shoutbox_enable'] == 0)
		fatal_lang_error('ezp_shoutbox_error_disabled');


	$t = time();

	$shout = $func['htmlspecialchars']($_REQUEST['shout'], ENT_QUOTES);

	$shout = trim($shout);

	// Insert the shout
	if ($shout != '')
		db_query("INSERT INTO {$db_prefix}ezp_shoutbox
			(id_member, date, shout)
			VALUES ($ID_MEMBER, '$t','$shout')", __FILE__, __LINE__);

	cache_put_data('ezBlockshout', null, 60);

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
	global $db_prefix;

	isAllowedTo('admin_forum');

	$shout = (int) $_REQUEST['shout'];

	db_query("DELETE FROM {$db_prefix}ezp_shoutbox
			WHERE id_shout = '$shout' LIMIT 1", __FILE__, __LINE__);

	cache_put_data('ezBlockshout', null, 60);

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
	global $ezpSettings, $db_prefix, $txt, $context, $scripturl;

	if ($ezpSettings['ezp_shoutbox_archivehistory'] == 0)
		fatal_error($txt['ezp_err_viewshouthistory'], false);

	// Set the page title
	$context['page_title'] = $txt['ezp_txt_shouthistory'];

	// Set the subtemplate
	$context['sub_template']  = 'ezportal_shoutbox_history';

	// Get Count of Shoutbox
	$result = db_query("
	SELECT
		COUNT(*) total
	FROM {$db_prefix}ezp_shoutbox", __FILE__, __LINE__);
	$totalRow = mysql_fetch_assoc($result);
	mysql_free_result($result);

	// Get the most Recent Shout Boxes first
	$context['start'] = (int) $_REQUEST['start'];

	$dbresult = db_query("
		SELECT
			s.shout, s.date, s.id_shout, s.id_member, m.realName, mg.onlineColor, mg.ID_GROUP
		FROM {$db_prefix}ezp_shoutbox AS s
		LEFT JOIN {$db_prefix}members AS m ON (s.ID_MEMBER = m.ID_MEMBER)
		LEFT JOIN {$db_prefix}membergroups AS mg ON (mg.ID_GROUP = IF(m.ID_GROUP = 0, m.ID_POST_GROUP, m.ID_GROUP))
		ORDER BY s.id_shout DESC LIMIT $context[start], " . $ezpSettings['ezp_shoutbox_history_number'] , __FILE__, __LINE__);
	$context['ezshouts_history'] = array();
	while($shoutRow = mysql_fetch_assoc($dbresult))
		$context['ezshouts_history'][] = $shoutRow;
	mysql_free_result($dbresult);

	$context['page_index'] = constructPageIndex($scripturl . '?action=ezportal;sa=shouthistory' , $_REQUEST['start'], $totalRow['total'], 10);


}

function EzPortalMenuAdd()
{
	global $txt, $context, $db_prefix;

	// Check Permission
	if (allowedTo('ezportal_blocks') == false)
		isAllowedTo('ezportal_manage');

	$block = (int) $_REQUEST['block'];
	$context['ezp_layout_id'] = $block;


	// Get Permissions
	$dbresult = db_query("
	SELECT
		ID_GROUP, groupName
	FROM {$db_prefix}membergroups
	WHERE minPosts = -1 AND ID_GROUP <> 3 ORDER BY groupName", __FILE__, __LINE__);
	while ($row = mysql_fetch_assoc($dbresult))
	{
		$context['groups'][$row['ID_GROUP']] = array(
			'ID_GROUP' => $row['ID_GROUP'],
			'groupName' => $row['groupName'],
			);
	}
	mysql_free_result($dbresult);

	$context['sub_template']  = 'ezportal_menu_add';
	$context['page_title'] = $txt['ezp_txt_menu_add'];
}

function EzPortalMenuAdd2()
{
	global $txt, $db_prefix, $func;

	// Check Permission
	if (allowedTo('ezportal_blocks') == false)
		isAllowedTo('ezportal_manage');

	$layoutid = (int) $_REQUEST['layoutid'];
	$menutitle = $func['htmlspecialchars']($_REQUEST['menutitle'],ENT_QUOTES);
	$menulink = $func['htmlspecialchars']($_REQUEST['menulink'], ENT_QUOTES);
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
	db_query("INSERT INTO {$db_prefix}ezp_menu
			(id_layout, id_order, title, linkurl, permissions, newwindow)
			VALUES ($layoutid, 1000,'$menutitle','$menulink','$finalPermissions',$newwindow)", __FILE__, __LINE__);

	ReOrderMenuItems($layoutid);

	cache_put_data('ezBlockMenu_' . $layoutid, null, 90);

	// Redirect back to the menu manager
	redirectexit("action=ezportal;sa=editblock;block=" . $layoutid);

}

function EzPortalMenuEdit()
{
	global $txt, $context, $db_prefix;

	// Check Permission
	if (allowedTo('ezportal_blocks') == false)
		isAllowedTo('ezportal_manage');

	// Get Permissions
	$dbresult = db_query("
	SELECT
		ID_GROUP, groupName
	FROM {$db_prefix}membergroups
	WHERE minPosts = -1 AND ID_GROUP <> 3 ORDER BY groupName", __FILE__, __LINE__);
	while ($row = mysql_fetch_assoc($dbresult))
	{
		$context['groups'][$row['ID_GROUP']] = array(
			'ID_GROUP' => $row['ID_GROUP'],
			'groupName' => $row['groupName'],
			);
	}
	mysql_free_result($dbresult);

	$id = (int) $_REQUEST['id'];

	$dbresult = db_query("
	SELECT
		id_menu, id_layout, id_order, title, linkurl, permissions, newwindow, enabled
	FROM {$db_prefix}ezp_menu
	WHERE id_menu = $id", __FILE__, __LINE__);
	$menuRow = mysql_fetch_assoc($dbresult);
	mysql_free_result($dbresult);

	$context['ezp_menu_row'] = $menuRow;


	$context['sub_template']  = 'ezportal_menu_edit';
	$context['page_title'] = $txt['ezp_txt_menu_edit'];

}

function EzPortalMenuEdit2()
{
	global $txt, $db_prefix, $func;

	// Check Permission
	if (allowedTo('ezportal_blocks') == false)
		isAllowedTo('ezportal_manage');

	$layoutid = (int) $_REQUEST['layoutid'];
	$menuid = (int) $_REQUEST['menuid'];
	$menutitle = $func['htmlspecialchars']($_REQUEST['menutitle'],ENT_QUOTES);
	$menulink = $func['htmlspecialchars']($_REQUEST['menulink'], ENT_QUOTES);
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
	db_query("UPDATE {$db_prefix}ezp_menu
	SET title = '$menutitle', linkurl = '$menulink', permissions = '$finalPermissions',
	newwindow = $newwindow, enabled = $menuenabled
	WHERE ID_MENU = $menuid
	", __FILE__, __LINE__);


	ReOrderMenuItems($layoutid);

	cache_put_data('ezBlockMenu_' . $layoutid, null, 90);

	// Redirect back to the menu manager
	redirectexit("action=ezportal;sa=editblock;block=" . $layoutid);
}

function EzPortalMenuDelete()
{
	global $txt, $context, $db_prefix;

	// Check Permission
	if (allowedTo('ezportal_blocks') == false)
		isAllowedTo('ezportal_manage');

	$id = (int) $_REQUEST['id'];

	$dbresult = db_query("
	SELECT
		id_menu, id_layout, id_order, title, linkurl, permissions, newwindow, enabled
	FROM {$db_prefix}ezp_menu
	WHERE id_menu = $id", __FILE__, __LINE__);
	$menuRow = mysql_fetch_assoc($dbresult);
	mysql_free_result($dbresult);

	$context['ezp_menu_row'] = $menuRow;

	$context['sub_template']  = 'ezportal_menu_delete';
	$context['page_title'] = $txt['ezp_txt_menu_delete'];
}

function EzPortalMenuDelete2()
{
	global $db_prefix;
	// Check Permission
	if (allowedTo('ezportal_blocks') == false)
		isAllowedTo('ezportal_manage');

	$layoutid = (int) $_REQUEST['layoutid'];
	$menuid = (int) $_REQUEST['menuid'];
	db_query("
	DELETE FROM {$db_prefix}ezp_menu
	WHERE id_menu = $menuid LIMIT 1", __FILE__, __LINE__);

	cache_put_data('ezBlockMenu_' . $layoutid, null, 90);


	// Redirect back to the menu manager
	redirectexit("action=ezportal;sa=editblock;block=" . $layoutid);
}

function ReOrderMenuItems($layoutid)
{
	global $db_prefix;

	$dbresult = db_query("
	SELECT
		id_menu
	FROM {$db_prefix}ezp_menu
	WHERE id_layout = $layoutid ORDER BY id_order ASC", __FILE__, __LINE__);
	if(db_affected_rows() != 0)
	{
		$count = 1;
		while($row2 = mysql_fetch_assoc($dbresult))
		{
			db_query("UPDATE {$db_prefix}ezp_menu
			SET id_order = $count WHERE id_menu = " . $row2['id_menu'], __FILE__, __LINE__);
			$count++;
		}
	}
	mysql_free_result($dbresult);
}

function EzPortalMenuUp()
{
	global $db_prefix, $txt;

	// Check Permission
	if (allowedTo('ezportal_blocks') == false)
		isAllowedTo('ezportal_manage');
	//Get the id
	@$id = (int) $_REQUEST['id'];
	$layoutid = (int) $_REQUEST['block'];

	ReOrderMenuItems($layoutid);


	// Check if there is a category above it
	// First get our row order
	$dbresult1 = db_query("
	SELECT
		id_layout, id_order, id_menu
	FROM {$db_prefix}ezp_menu
	WHERE id_menu = $id", __FILE__, __LINE__);
	$row = mysql_fetch_assoc($dbresult1);

	$id_layout = $row['id_layout'];
	$oldrow = $row['id_order'];
	$o = $row['id_order'];
	$o--;

	mysql_free_result($dbresult1);
	$dbresult = db_query("
	SELECT
		id_menu, id_order, id_layout
	FROM {$db_prefix}ezp_menu
	WHERE id_layout = $id_layout AND id_order = $o", __FILE__, __LINE__);

	if (db_affected_rows()== 0)
		fatal_error($txt['ezp_err_menu_up'], false);
	$row2 = mysql_fetch_assoc($dbresult);


	// Swap the order Id's
	db_query("UPDATE {$db_prefix}ezp_menu
		SET id_order = $oldrow WHERE id_menu = " .$row2['id_menu'], __FILE__, __LINE__);

	db_query("UPDATE {$db_prefix}ezp_menu
		SET id_order = $o WHERE id_menu = $id", __FILE__, __LINE__);

	mysql_free_result($dbresult);


	cache_put_data('ezBlockMenu_' . $layoutid, null, 90);

	redirectexit("action=ezportal;sa=editblock;block=" . $layoutid);

}

function EzPortalMenuDown()
{
	global $db_prefix, $txt;

	// Check Permission
	if (allowedTo('ezportal_blocks') == false)
		isAllowedTo('ezportal_manage');

	//Get the id
	@$id = (int) $_REQUEST['id'];
	$layoutid = (int) $_REQUEST['block'];

	ReOrderMenuItems($layoutid);


	// Check if there is a category below it
	// First get our row order
	$dbresult1 = db_query("
	SELECT
		id_layout, id_menu, id_order
	FROM {$db_prefix}ezp_menu
	WHERE id_menu = $id LIMIT 1", __FILE__, __LINE__);
	$row = mysql_fetch_assoc($dbresult1);
	$id_layout = $row['id_layout'];

	$oldrow = $row['id_order'];
	$o = $row['id_order'];
	$o++;

	mysql_free_result($dbresult1);
	$dbresult = db_query("
	SELECT
		id_menu, id_layout, id_order
	FROM {$db_prefix}ezp_menu
	WHERE id_layout = $id_layout AND id_order = $o", __FILE__, __LINE__);
	if (db_affected_rows()== 0)
		fatal_error($txt['ezp_err_menu_down'], false);
	$row2 = mysql_fetch_assoc($dbresult);

	// Swap the order Id's
	db_query("UPDATE {$db_prefix}ezp_menu
		SET id_order = $oldrow WHERE id_menu = " .$row2['id_menu'], __FILE__, __LINE__);

	db_query("UPDATE {$db_prefix}ezp_menu
		SET id_order = $o WHERE id_menu = $id", __FILE__, __LINE__);

	cache_put_data('ezBlockMenu_' . $layoutid, null, 90);

	redirectexit("action=ezportal;sa=editblock;block=" . $layoutid);
}

function CleanUpDuplicateValues($layoutID)
{
	global $db_prefix;

	$result = db_query("SELECT COUNT(*) AS total, id_parameter FROM {$db_prefix}ezp_block_parameters_values
			WHERE id_layout = $layoutID GROUP BY id_parameter ", __FILE__, __LINE__);
	while ($row = mysql_fetch_assoc($result))
	{
		if ($row['total'] > 1)
		{
			$limitValue  = ($row['total'] -1);
			db_query("DELETE FROM {$db_prefix}ezp_block_parameters_values
		 WHERE id_parameter  = " . $row['id_parameter'] . " AND id_layout = $layoutID LIMIT $limitValue", __FILE__, __LINE__);

		}
	}
	mysql_free_result($result);

}

function EzPortalColumnState()
{
	global $db_prefix, $ID_MEMBER, $context;

	checkSession('get');

	if (!isset($_REQUEST['columnid']))
		return;

	if (!isset($_REQUEST['state']))
		return;

	// Get the collapse state of the column
	$state = (int) $_REQUEST['state'];

	$columnID = (int) $_REQUEST['columnid'];

	// If we are a guest do a session to save it.
	if ($context['user']['is_guest'])
	{

		$collapsedezColumns = array();

		if (isset($_SESSION['ezp_column_guests']))
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

		return;
	}


	// Get current ezColumns that are collsapsed
	$request = db_query("
	SELECT
		value
	FROM {$db_prefix}themes
	WHERE ID_MEMBER = $ID_MEMBER AND ID_THEME = 0 AND variable =  'ezportal_ezcolumncollapse'", __FILE__, __LINE__);
	$row = mysql_fetch_assoc($request);

	if (!empty($row['value']))
		$collapsedezColumns = explode(",",$row['value']);
	else
		$collapsedezColumns  = '';

	mysql_free_result($request);

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

	db_query("REPLACE INTO {$db_prefix}themes
					(ID_MEMBER, ID_THEME, variable, value)
				VALUES ($ID_MEMBER,0,'ezportal_ezcolumncollapse','$finalCollapseList')", __FILE__, __LINE__);


    cache_put_data('ezportal_col_collaspe_' . $ID_MEMBER, $collapsedezColumns, 90);

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
    global $db_prefix;

    isAllowedTo('ezportal_manage');

   	db_query("DELETE FROM {$db_prefix}ezp_shoutbox", __FILE__, __LINE__);

    cache_put_data('ezBlockshout', null, 60);

    redirectexit("action=ezportal;sa=shouthistory");
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
			file_put_contents($boarddir . "/.htaccess",$data . $checkContents);



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