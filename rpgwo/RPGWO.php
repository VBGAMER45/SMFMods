<?php
if (!defined('SMF'))
	die('Hacking attempt...');

/*
 *   `ID_SERVER` int(11) NOT NULL AUTO_INCREMENT,
  `title` tinytext,
  `ID_MEMBER` mediumint(8) unsigned NOT NULL DEFAULT '0',

  enabled tinyint(1) default 0,
  server_ip varchar(25),
  server_port int(5) default 0,
  server_version tinyint(1) default 0,
  server_updateurl varchar(255),

  server_players_online int(5) default 0,
  server_api_key varchar(255),
 */

function RPGWOMain()
{
	is_not_guest();

	loadTemplate('rpgwo');

	$subActions = array(
			'worlds' => 'RPGWO_DisplayWorlds',
			'addworld' => 'RPGWO_AddWorld',
			'addworld2' => 'RPGWO_AddWorld2',
			'editworld' => 'RPGWO_EditWorld',
			'editworld2' => 'RPGWO_EditWorld2',
			'deleteworld' => 'RPGWO_DeleteWorld',
			'deleteworld2' => 'RPGWO_DeleteWorld2',
	);


	// Follow the sa or just go to  the main function
    if (isset($_REQUEST['sa']))
	   $sa = $_REQUEST['sa'];
    else
        $sa = '';


	if (!empty($subActions[$sa]))
		$subActions[$sa]();
	else
		RPGWO_DisplayWorlds();


}

function RPGWO_DisplayWorlds()
{
	global $context, $smcFunc, $user_info;
	
	$dbresult2 = $smcFunc['db_query']('', "
		SELECT
			ID_SERVER,title, description,ID_MEMBER,enabled,datecreated,
			 server_ip,server_port,server_version,server_updateurl
		FROM  rpgwo_servers
		WHERE removed = 0 AND ID_MEMBER = " . $user_info['id']);

	$context['rpgwo_worlds'] = array();


	while ($row = $smcFunc['db_fetch_assoc']($dbresult2))
	{
		$context['rpgwo_worlds'][] = $row;
	}

	

	$context['sub_template']  = 'worlds';
	$context['page_title'] =  'Worlds';
}

function RPGWO_AddWorld()
{
	global $context, $smcFunc;

	$context['rpgwo_world'] = array();
	
	$context['sub_template']  = 'add_world';
	$context['page_title'] =  'Add World';
}

function RPGWO_AddWorld2()
{
	global $smcFunc, $context, $user_info;

	$context['rpgwo_world'] = array();

	$errors = array();

	$title = $smcFunc['htmlspecialchars']($_REQUEST['title'],ENT_QUOTES);

	if (empty($title))
		$errors[] = 'World Title is required!';


	$description = $smcFunc['htmlspecialchars']($_REQUEST['description'],ENT_QUOTES);

	$enabled = (int) $_REQUEST['enabled'];


	censorText($title);

	censorText($description);

	$server_ip = $smcFunc['htmlspecialchars']($_REQUEST['server_ip'],ENT_QUOTES);

	if (empty($server_ip))
		$errors[] = 'Server Host/IP is required';
	// Check if ip is valid
//	else if (!filter_var($server_ip, FILTER_VALIDATE_IP))
	//	$errors[] = 'Invalid Server IP Address';


	$server_port = (int) $_REQUEST['server_port'];

	if (empty($server_port))
		$errors[] = 'Server Port is required';


	$server_updateurl = $smcFunc['htmlspecialchars']($_REQUEST['server_updateurl'],ENT_QUOTES);

	if (empty($server_updateurl))
		$errors[] = 'Update URL is required';
	else if (substr($server_updateurl, 0, 7) != "http://" )
		$errors[] = 'Update URL must start with http://';

	$server_version = (int) $_REQUEST['server_version'];
	
	
	// Check if server and IP is in another account
	$dbresult = $smcFunc['db_query']('', "
		SELECT
			COUNT(*) as total 
		FROM rpgwo_servers 
		WHERE ID_MEMBER != " . $user_info['id'] . " AND removed = 0 AND server_ip = '$server_ip' AND server_port = '$server_port'");

	$countRow = $smcFunc['db_fetch_assoc']($dbresult);
	if ($countRow['total'] > 0)
		$errors[] = 'Another account already have this ip address and port! Please contact us if you are the new owner of this server';

	if ($enabled == 1 && empty($errors))
	{
		// Validate Server is working
		if (RPGWO_ISVALIDWorld($server_ip,$server_port) == false)
			$errors[] = 'Server is not UP! If you set a server to enabled. It has to be UP in order to process';
	}



	if (empty($errors))
	{
		$datecreated = time();
		// insert database
	$smcFunc['db_query']('', "INSERT INTO rpgwo_servers
			(title, description,ID_MEMBER,enabled,datecreated,
			 server_ip,server_port,server_version,server_updateurl
			 )
		VALUES ('$title', '$description','" . $user_info['id'] . "',$enabled,'$datecreated',
		    '$server_ip','$server_port','$server_version','$server_updateurl'    
		        
			)");


		redirectexit('action=rpgwo;sa=worlds');
	}
	else
	{

		$tmp = array();
		$tmp['title']  = $title;
		$tmp['description']  = $description;
		$tmp['enabled']  = $enabled;
		$tmp['server_ip']  = $server_ip;
		$tmp['server_port']  = $server_port;
		$tmp['server_version']  = $server_version;
		$tmp['server_updateurl']  = $server_updateurl;
		$context['rpgwo_world'] = $tmp;


		$context['rpgwo_errors'] = $errors;
		$context['sub_template']  = 'add_world';
		$context['page_title'] =  'Add World';

	}




}


function RPGWO_EditWorld()
{
	global $context, $smcFunc, $user_info;

	$id = (int) $_REQUEST['id'];

	$context['rpgwo_world'] = array();


	$dbresult2 = $smcFunc['db_query']('', "
		SELECT
			ID_SERVER, title, description,ID_MEMBER,enabled,datecreated,
			 server_ip,server_port,server_version,server_updateurl
		FROM  rpgwo_servers
		WHERE removed = 0 AND ID_MEMBER = " . $user_info['id'] . " AND ID_SERVER = $id");
	$row = $smcFunc['db_fetch_assoc']($dbresult2);

	if (empty($row['ID_SERVER']))
		fatal_error("You do not have access to this world");

	$context['rpgwo_world'] = $row;


	$context['sub_template']  = 'edit_world';
	$context['page_title'] =  'Edit World';
}

function RPGWO_EditWorld2()
{
	global $smcFunc, $user_info, $context;

	$id = (int) $_REQUEST['id'];

	$context['rpgwo_world'] = array();

	$dbresult2 = $smcFunc['db_query']('', "
		SELECT
			ID_SERVER, title, description,ID_MEMBER,enabled,datecreated,
			 server_ip,server_port,server_version,server_updateurl
		FROM  rpgwo_servers
		WHERE removed = 0 AND ID_MEMBER = " . $user_info['id'] . " AND ID_SERVER = $id");
	$row = $smcFunc['db_fetch_assoc']($dbresult2);

	if (empty($row['ID_SERVER']))
		fatal_error("You do not have access to this world");


	$context['rpgwo_world'] = array();

	$errors = array();

	$title = $smcFunc['htmlspecialchars']($_REQUEST['title'],ENT_QUOTES);

	if (empty($title))
		$errors[] = 'World Title is required!';


	$description = $smcFunc['htmlspecialchars']($_REQUEST['description'],ENT_QUOTES);

	$enabled = (int) $_REQUEST['enabled'];


	censorText($title);

	censorText($description);

	$server_ip = $smcFunc['htmlspecialchars']($_REQUEST['server_ip'],ENT_QUOTES);

	if (empty($server_ip))
		$errors[] = 'Server Host/IP is required';
	// Check if ip is valid
//	else if (!filter_var($server_ip, FILTER_VALIDATE_IP))
	//	$errors[] = 'Invalid Server IP Address';


	$server_port = (int) $_REQUEST['server_port'];

	if (empty($server_port))
		$errors[] = 'Server Port is required';


	$server_updateurl = $smcFunc['htmlspecialchars']($_REQUEST['server_updateurl'],ENT_QUOTES);

	if (empty($server_updateurl))
		$errors[] = 'Update URL is required';
	else if (substr($server_updateurl, 0, 7) != "http://" )
		$errors[] = 'Update URL must start with http://';

	$server_version = (int) $_REQUEST['server_version'];


	// Check if server and IP is in another account
	$dbresult = $smcFunc['db_query']('', "
		SELECT
			COUNT(*) as total 
		FROM rpgwo_servers 
		WHERE ID_MEMBER != " . $user_info['id'] . " AND removed = 0 AND server_ip = '$server_ip' AND server_port = '$server_port'");

	$countRow = $smcFunc['db_fetch_assoc']($dbresult);
	if ($countRow['total'] > 0)
		$errors[] = 'Another account already have this ip address and port! Please contact us if you are the new owner of this server';

	if ($enabled == 1 && empty($errors))
	{
		// Validate Server is working
		if (RPGWO_ISVALIDWorld($server_ip,$server_port) == false)
			$errors[] = 'Server is not UP! If you set a server to enabled. It has to be UP in order to process';
	}



	if (empty($errors))
	{

	$smcFunc['db_query']('', "UPDATE rpgwo_servers SET
	title = '$title', description = '$description',enabled = $enabled,
			 server_ip = '$server_ip',server_port = '$server_port',server_version = '$server_version', server_updateurl = '$server_updateurl' 
			WHERE ID_MEMBER = " . $user_info['id'] . " AND ID_SERVER = $id");


		redirectexit('action=rpgwo;sa=worlds');
	}
	else
	{

		$tmp = array();
		$tmp['ID_SERVER']  = $id;
		$tmp['title']  = $title;
		$tmp['description']  = $description;
		$tmp['enabled']  = $enabled;
		$tmp['server_ip']  = $server_ip;
		$tmp['server_port']  = $server_port;
		$tmp['server_version']  = $server_version;
		$tmp['server_updateurl']  = $server_updateurl;
		$context['rpgwo_world'] = $tmp;


		$context['rpgwo_errors'] = $errors;
		$context['sub_template']  = 'edit_world';
		$context['page_title'] =  'Edit World';

	}





}

function RPGWO_DeleteWorld()
{
	global $context, $smcFunc, $user_info;

	$id = (int) $_REQUEST['id'];

	$dbresult2 = $smcFunc['db_query']('', "
		SELECT
			ID_SERVER, title, description,ID_MEMBER,enabled,datecreated,
			 server_ip,server_port,server_version,server_updateurl
		FROM  rpgwo_servers
		WHERE removed = 0 AND ID_MEMBER = " . $user_info['id'] . " AND ID_SERVER = $id");
	$row = $smcFunc['db_fetch_assoc']($dbresult2);

	if (empty($row['ID_SERVER']))
		fatal_error("You do not have access to this world");
	
	$context['rpgwo_world'] = $row;


	$context['sub_template']  = 'delete_world';
	$context['page_title'] =  'Delete World';
}

function RPGWO_DeleteWorld2()
{
	global $smcFunc, $user_info;
	
	$id = (int) $_REQUEST['id'];

	$dbresult2 = $smcFunc['db_query']('', "
		SELECT
			ID_SERVER, title, description,ID_MEMBER,enabled,datecreated,
			 server_ip,server_port,server_version,server_updateurl
		FROM  rpgwo_servers
		WHERE removed = 0 AND ID_MEMBER = " . $user_info['id'] . " AND ID_SERVER = $id");
	$row = $smcFunc['db_fetch_assoc']($dbresult2);

	if (empty($row['ID_SERVER']))
		fatal_error("You do not have access to this world");

	// Actually delete the server
	$smcFunc['db_query']('', "UPDATE rpgwo_servers SET removed = 1
		WHERE removed = 0 AND ID_MEMBER = " . $user_info['id'] . " AND ID_SERVER = $id");


	redirectexit('action=rpgwo;sa=worlds');
}

function RPGWO_ISVALIDWorld($ip, $port = 0)
{
	global $smcFunc;
	//@ini_set('max_execution_time', '600');

	// Check server port that is enabled.
	$fp = fsockopen($ip, $port, $errno, $errstr, 2);
	if (!$fp)
	{
//server doesn't exist

		return false;
	} else
	{
		//  is it up??
		// Get version packet
		//$data = fread($fp, 25);
		 fclose($fp);

		return true;
	}


}