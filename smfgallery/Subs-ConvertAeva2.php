<?php
/*
SMF Gallery Lite Edition
Version 5.0
by:vbgamer45
http://www.smfhacks.com
Copyright 2008-2017 SMFHacks.com

############################################
License Information:
SMF Gallery is NOT free software.
This software may not be redistributed.

Links to http://www.smfhacks.com must remain unless
branding free option is purchased.
#############################################
*/


function GalleryAevaImportMain()
{
    global $modSettings, $sourcedir, $boarddir, $boardurl, $smcFunc, $AevaSettings;

    isAllowedTo('smfgallery_manage');
    // Unlimited Exectuion time.
    @ini_set("max_execution_time", "900");
    @ini_set('display_errors',1);
    @ini_set('memory_limit', '164M');

    $modSettings['disableQueryCheck'] = 1;
    require_once($sourcedir . '/Aeva-Subs.php');
    aeva_loadSettings();
    require_once($sourcedir . '/Subs-Graphics.php');
    require_once($sourcedir . '/Subs-Package.php');

    // Check the gallery path
    	if (empty($modSettings['gallery_path']))
    		$modSettings['gallery_path'] = $boarddir . '/gallery/';

    	if (empty($modSettings['gallery_url']))
    		$modSettings['gallery_url'] = $boardurl . '/gallery/';


    $result =  $smcFunc['db_query']('', "SELECT name,value FROM {db_prefix}aeva_settings");
    $AevaSettings = array();

    		while ($row = $smcFunc['db_fetch_row']($result))
    			$AevaSettings[$row[0]] = $row[1];
    		$smcFunc['db_free_result']($result);

    if (function_exists('apache_reset_timeout'))
    		@apache_reset_timeout();
    		
	if (empty($AevaSettings['data_dir_path']))
		$AevaSettings['data_dir_path'] = $boarddir . '/mgal_data';

    if (isset($_REQUEST['importstep']))
        $importstep = $_REQUEST['importstep'];
    else
        $importstep = 'welcome';

	// Gallery Actions
	$subActions = array(
		'import0' => 'AevaImport0',
		'import1' => 'AevaImport1',
        'import2' => 'AevaImport2',
        'import2b' => 'AevaImport2b',
        'import3' => 'AevaImport3',
        'import4' => 'AevaImport4',
        'import5' => 'AevaImport5',
        'import6' => 'AevaImport6',
        'import7' => 'AevaImport7',
    );


    if (!empty($subActions[$importstep]))
		$subActions[$importstep]();
	else
		AevaImportWelcome();


}

function AevaImportWelcome()
{
    global $txt, $context;

    $context['page_title'] = $txt['gallery_import_welcome'];
    $context['sub_template']  = 'import_welcomeaeva';

}


 function AevaImport0()
 {
    global $context, $smcFunc, $modSettings, $AevaSettings, $txt;

    // Create Tables needed

     $smcFunc['db_query']('', "CREATE TABLE IF NOT EXISTS {db_prefix}gallery_rating
        (ID int(11) NOT NULL auto_increment,
        ID_PICTURE int(11) NOT NULL,
        ID_MEMBER mediumint(8) unsigned NOT NULL default '0',
        value tinyint(2) NOT NULL,
        PRIMARY KEY  (ID))");

         $smcFunc['db_query']('', "CREATE TABLE IF NOT EXISTS {db_prefix}gallery_creport
            (ID int(11) NOT NULL auto_increment,
            ID_PICTURE int(11) NOT NULL,
            ID_COMMENT int(11) NOT NULL,
            ID_MEMBER mediumint(8) unsigned NOT NULL default '0',
            comment text,
            date int(10) unsigned NOT NULL default '0',
            PRIMARY KEY  (ID))");


            //User Gallery Category
             $smcFunc['db_query']('', "CREATE TABLE IF NOT EXISTS {db_prefix}gallery_usercat
            (USER_ID_CAT mediumint(8) NOT NULL auto_increment,
            ID_MEMBER mediumint(8) unsigned NOT NULL,
            title VARCHAR(100) NOT NULL,
            description text,
            roworder mediumint(8) unsigned NOT NULL default '0',
            image VARCHAR(255),
            filename tinytext,
            ID_PARENT smallint(5) unsigned NOT NULL default '0',
            total int(11) NOT NULL default '0',
            sortby tinytext,
            orderby tinytext,
            LAST_ID_PICTURE int(11) NOT NULL default '0',
            PRIMARY KEY  (USER_ID_CAT))");



        //Gallery Member Quota Information
         $smcFunc['db_query']('', "CREATE TABLE IF NOT EXISTS {db_prefix}gallery_userquota
        (ID_MEMBER mediumint(8) unsigned NOT NULL,
        totalfilesize int(12) NOT NULL default '0',
        PRIMARY KEY  (ID_MEMBER))");

        //Gallery Gruop Quota limit
         $smcFunc['db_query']('', "CREATE TABLE IF NOT EXISTS {db_prefix}gallery_groupquota
        (ID_GROUP smallint(5) unsigned NOT NULL default '0',
        totalfilesize int(12) NOT NULL default '0',
        PRIMARY KEY  (ID_GROUP))");


         $smcFunc['db_query']('', "ALTER TABLE {db_prefix}gallery_userquota CHANGE totalfilesize totalfilesize BIGINT NOT NULL  default '0'");
         $smcFunc['db_query']('', "ALTER TABLE {db_prefix}gallery_groupquota CHANGE totalfilesize totalfilesize BIGINT NOT NULL  default '0'");



         $smcFunc['db_query']('', "CREATE TABLE IF NOT EXISTS {db_prefix}gallery_catperm
        (ID mediumint(8) NOT NULL auto_increment,
        ID_GROUP mediumint(8) NOT NULL default '0',
        ID_CAT mediumint(8) unsigned NOT NULL default '0',
        view tinyint(4) NOT NULL default '0',
        addpic tinyint(4) NOT NULL default '0',
        editpic tinyint(4) NOT NULL default '0',
        delpic tinyint(4) NOT NULL default '0',
        ratepic tinyint(4) NOT NULL default '0',
        addcomment tinyint(4) NOT NULL default '0',
        editcomment tinyint(4) NOT NULL default '0',
        report tinyint(4) NOT NULL default '0',
        addvideo tinyint(4) NOT NULL default '0',
        viewimagedetail tinyint(4) NOT NULL default '0',
        PRIMARY KEY  (ID))");


         $smcFunc['db_query']('', "CREATE TABLE IF NOT EXISTS {db_prefix}gallery_usersettings
        (
        ID_MEMBER mediumint(8) unsigned NOT NULL,
        title tinytext,
        password tinytext,
        private tinyint(4) default 0,
        icon tinytext,
        gallery_index_toprated tinyint(4) default 0,
        gallery_index_recent tinyint(4) default 0,
        gallery_index_mostviewed tinyint(4) default 0,
        gallery_index_mostcomments tinyint(4) default 0,
        gallery_index_showtop tinyint(4) default 0,
        gallery_index_featured tinyint(4) default 0,
        PRIMARY KEY (ID_MEMBER))");


         $smcFunc['db_query']('', "CREATE TABLE IF NOT EXISTS {db_prefix}gallery_userprivate
        (
        ID_OWNER mediumint(8) unsigned NOT NULL,
        ID_MEMBER mediumint(8) unsigned NOT NULL)");


        // Custom Fields table
         $smcFunc['db_query']('', "CREATE TABLE IF NOT EXISTS {db_prefix}gallery_custom_field
        (ID_CUSTOM mediumint(8) NOT NULL auto_increment,
        ID_CAT int(10) NOT NULL,
        roworder mediumint(8) unsigned NOT NULL default '0',
        title tinytext,
        defaultvalue tinytext,
        is_required tinyint(4) NOT NULL default '0',
        showoncatlist tinyint(4) NOT NULL default '0',
        KEY ID_CAT (ID_CAT),
        PRIMARY KEY  (ID_CUSTOM))
        Engine=MyISAM");

         $smcFunc['db_query']('', "CREATE TABLE IF NOT EXISTS {db_prefix}gallery_custom_field_data
        (
        ID_CUSTOM mediumint(8) NOT NULL,
        ID_PICTURE int(11) NOT NULL default '0',
        value tinytext,
        KEY ID_PICTURE (ID_PICTURE)
        )
        Engine=MyISAM");



         $smcFunc['db_query']('', "CREATE TABLE IF NOT EXISTS {db_prefix}gallery_exif_data
        (
        ID_PICTURE int(11) NOT NULL default '0',

        file_filedatetime tinytext,
        file_filesize tinytext,
        file_filetype tinytext,
        file_mimetype tinytext,
        file_sectionsfound tinytext,

        computed_height tinytext,
        computed_width tinytext,
        computed_iscolor tinytext,
        computed_ccdwidth tinytext,
        computed_aperturefnumber tinytext,
        computed_copyright tinytext,

        idfo_imagedescription text,
        idfo_make varchar(255),
        idfo_model varchar(255),
        idfo_orientation tinytext,
        idfo_xresolution tinytext,
        idfo_yresolution tinytext,
        idfo_resolutionunit tinytext,
        idfo_software tinytext,
        idfo_datetime tinytext,
        idfo_artist tinytext,

        exif_exposuretime tinytext,
        exif_fnumber tinytext,
        exif_exposureprogram tinytext,
        exif_isospeedratings tinytext,
        exif_exifversion tinytext,
        exif_datetimeoriginal tinytext,
        exif_datetimedigitized tinytext,
        exif_shutterspeedvalue tinytext,
        exif_aperturevalue tinytext,
        exif_exposurebiasvalue tinytext,
        exif_maxaperturevalue tinytext,
        exif_meteringmode tinytext,
        exif_lightsource tinytext,
        exif_flash tinytext,
        exif_focallength tinytext,
        exif_colorspace tinytext,
        exif_exifimagewidth tinytext,
        exif_exifimagelength tinytext,
        exif_focalplanexresolution tinytext,
        exif_focalplaneyresolution tinytext,
        exif_focalplaneresolutionunit tinytext,
        exif_customrendered tinytext,
        exif_exposuremode tinytext,
        exif_whitebalance tinytext,
        exif_scenecapturetype tinytext,

        exif_lenstype tinytext,
        exif_lensid tinytext,
        exif_lensinfo tinytext,

        gps_latituderef tinytext,
        gps_latitude tinytext,
        gps_longituderef tinytext,
        gps_longitude tinytext,

        PRIMARY KEY (ID_PICTURE)
        )
        Engine=MyISAM");


         $smcFunc['db_query']('', "CREATE TABLE IF NOT EXISTS {db_prefix}gallery_log_mark_view (
          id_member mediumint(8) unsigned NOT NULL default '0',
          id_cat int(10) NOT NULL default '0',
          id_picture int(11) NOT NULL default '0',
          user_id_cat int(10) NOT NULL default '0',
          KEY (user_id_cat),
          KEY (id_cat),
          KEY (id_member),
          KEY (id_picture),
          PRIMARY KEY (id_member, id_picture, id_cat, user_id_cat)
        ) Engine=MyISAM");



// Likes table
$smcFunc['db_query']('', "CREATE TABLE IF NOT EXISTS {db_prefix}gallery_like(
ID_LIKE int(11) NOT NULL auto_increment,
ID_PICTURE int(11) NOT NULL default '0',
ID_COMMENT int(11) NOT NULL default '0',
ID_MEMBER mediumint(8) unsigned NOT NULL default '0',
logdate int(10),
KEY ID_PICTURE (ID_PICTURE),
KEY ID_COMMENT (ID_COMMENT),
KEY logdate (logdate),
KEY ID_MEMBER (ID_MEMBER),
PRIMARY KEY  (ID_LIKE))

");

// Create Favorites
$smcFunc['db_query']('', "CREATE TABLE IF NOT EXISTS {db_prefix}gallery_favorites
(ID int(11) NOT NULL auto_increment,
ID_PICTURE int(11) unsigned NOT NULL default '0',
ID_MEMBER mediumint(8) unsigned NOT NULL default '0',
KEY ID_PICTURE (ID_PICTURE),
KEY ID_MEMBER (ID_MEMBER),
PRIMARY KEY  (ID))");


		 $smcFunc['db_query']('', "TRUNCATE {db_prefix}gallery_pic");
		 $smcFunc['db_query']('', "TRUNCATE {db_prefix}gallery_comment");
		 $smcFunc['db_query']('', "TRUNCATE {db_prefix}gallery_cat");
		 $smcFunc['db_query']('', "TRUNCATE {db_prefix}gallery_rating");
		 $smcFunc['db_query']('', "TRUNCATE {db_prefix}gallery_creport");
		 $smcFunc['db_query']('', "TRUNCATE {db_prefix}gallery_userquota");
		 $smcFunc['db_query']('', "TRUNCATE {db_prefix}gallery_groupquota");
		 $smcFunc['db_query']('', "TRUNCATE {db_prefix}gallery_usercat");
		 $smcFunc['db_query']('', "TRUNCATE {db_prefix}gallery_catperm");
		 $smcFunc['db_query']('', "TRUNCATE {db_prefix}gallery_usersettings");
		 $smcFunc['db_query']('', "TRUNCATE {db_prefix}gallery_userprivate");
		 $smcFunc['db_query']('', "TRUNCATE {db_prefix}gallery_custom_field");
		 $smcFunc['db_query']('', "TRUNCATE {db_prefix}gallery_custom_field_data");
		 $smcFunc['db_query']('', "TRUNCATE {db_prefix}gallery_exif_data");
		 $smcFunc['db_query']('', "TRUNCATE {db_prefix}gallery_log_mark_view");
		 $smcFunc['db_query']('', "TRUNCATE {db_prefix}gallery_favorites");
		 $smcFunc['db_query']('', "TRUNCATE {db_prefix}gallery_like");

		updateSettings(
		array(
		'gallery_set_count_child' => 1,
		'gallery_index_showusergallery' => 1,
		'gallery_make_medium' => 1,
	));

        $context['page_title'] = $txt['gallery_import_clearingsmfgallery'];
        $context['import_step_title'] = $context['page_title'];

		CountDownCode('import1');

 }

function AevaImport1()
{
    global $context, $smcFunc, $modSettings, $AevaSettings, $txt;
    // Import Categories
		$complete = 0;

		$notInSQL = '';

		if (isset($_REQUEST['post_data']))
			$notInSQL = $_REQUEST['post_data'];

		if (empty($_REQUEST['start']))
		{
			$result  =  $smcFunc['db_query']('', "
			SELECT id_album, album_of, name, description, parent, a_order, featured, master
			FROM {db_prefix}aeva_albums
			WHERE featured = 1
			");
			$finalArray =  array();
			while($row = $smcFunc['db_fetch_assoc']($result))
			{
				$finalArray[] = $row;
			}
			foreach($finalArray as $row)
			{

				if (empty($notInSQL))
				{
					$notInSQL .= ' WHERE id_album NOT IN(' . $row['id_album'];
				}
				else
					$notInSQL .= ', ' . $row['id_album'];

				 $smcFunc['db_query']('', "INSERT IGNORE INTO {db_prefix}gallery_cat
				(id_cat,title,description,id_parent,roworder)
				VALUES ('" . $row['id_album'] . "','" . addslashes($row['name']) . "','" . addslashes($row['description']) . "','" . $row['parent'] . "','" . $row['a_order'] . "' )");

				$notInSQL = ImportGalleryMainCategory($row['id_album'],$notInSQL);
			}

			if (!empty($notInSQL))
			{
				$notInSQL .= ') ';
			}
		}



	$context['start'] = empty($_REQUEST['start']) ? 15 : (int) $_REQUEST['start'];
	$context['start_time'] =  time();


	// Determine the total members with posts.
	$request = $smcFunc['db_query']('', "
		SELECT COUNT(*) FROM {db_prefix}aeva_albums $notInSQL ");
	list($totalProcess) = $smcFunc['db_fetch_row']($request);
	$smcFunc['db_free_result']($request);

	// Initialize the variables.
	$increment = 15;
	if (empty($_REQUEST['start']))
		$_REQUEST['start'] = 0;

	// Grab the first set of members to verify.
	$request =  $smcFunc['db_query']('', "
		SELECT id_album, album_of, name, description, parent, a_order, featured, master  FROM {db_prefix}aeva_albums
		$notInSQL
		LIMIT " . $_REQUEST['start'] . ","  . ( $increment));

	while ($row = $smcFunc['db_fetch_assoc']($request))
	{
				 $smcFunc['db_query']('', "INSERT INTO {db_prefix}gallery_usercat
				(user_id_cat,title,description,id_parent,roworder,id_member)
				VALUES ('" . $row['id_album'] . "','" . addslashes($row['name']) . "','" . addslashes($row['description']) . "','" . $row['parent'] . "','" . $row['a_order'] . "', '" . $row['album_of'] . "')");

	}
	$smcFunc['db_free_result']($request);

	$_REQUEST['start'] += $increment;

	// Continue?
	if($_REQUEST['start'] < $totalProcess)
	{

		$context['continue_get_data'] = ';start=' . $_REQUEST['start'];
		$context['continue_post_data'] = "<input type='hidden' name='post_data' value='$notInSQL' />";
		$context['continue_percent'] = round(100 * $_REQUEST['start'] / $totalProcess);


	}
	else
		$complete = 1;

        $context['page_title'] = $txt['gallery_import_categories'];
        $context['import_step_title'] = $context['page_title'];


		if ($complete == 0)
			CountDownCode('import1');
		else
		{
				 $smcFunc['db_query']('', "INSERT INTO {db_prefix}gallery_cat
				(title, description,roworder,ID_PARENT,redirect,image)
			VALUES ('Member Galleries', 'Galleries created by members',0,0,1,'')");

			$context['continue_post_data'] = "<input type='hidden' name='post_data' value='$notInSQL' />";

			CountDownCode('import2');

		}

}

function AevaImport2()
{
    global $context, $smcFunc, $modSettings, $AevaSettings, $txt;
    // Import Pictures
    $complete = 0;

		$notInSQL = '';
		$notInSQL2 = '';

		if (isset($_REQUEST['post_data']))
			$notInSQL = $_REQUEST['post_data'];

		$notInSQL2 = str_replace("id_album NOT IN","m.album_id IN",$notInSQL);

		if (empty($_REQUEST['post_data']))
		{
			$context['page_title'] = $txt['gallery_import_mainpictures'];
            $context['import_step_title'] = $context['page_title'];

			CountDownCode('import2b');

			return;
		}



		$context['start'] = empty($_REQUEST['start']) ? 30 : (int) $_REQUEST['start'];
		$context['start_time'] =  time();

		$notInSQL = $_REQUEST['post_data'];


		// Determine the total members with posts.

		$request =  $smcFunc['db_query']('', "
			SELECT COUNT(*) from ({db_prefix}aeva_media as m)
LEFT JOIN {db_prefix}aeva_files as f on (f.id_file = m.id_file)
LEFT JOIN {db_prefix}aeva_files as t on (t.id_file = m.id_thumb)
LEFT JOIN {db_prefix}aeva_files as p on (p.id_file = m.id_preview)
$notInSQL2 ");
		list($totalProcess) = $smcFunc['db_fetch_row']($request);
		$smcFunc['db_free_result']($request);

		// Initialize the variables.
		$increment = 25;
		if (empty($_REQUEST['start']))
			$_REQUEST['start'] = 0;

		// Grab the first set of members to verify.
		$request = $smcFunc['db_query']('', "
			select m.id_media, m.id_member,
m.id_file, m.id_thumb, m.id_preview,  m.type, m.title,
m.description, m.approved, m.album_id, m.time_added, m.views, m.keywords,
m.num_comments, m.voters, m.rating, f.exif, m.embed_url,
f.filename, f.filesize, f.directory, f.width, f.height,  f.id_file fFile,
t.filename thumbfilename, t.directory thumbdirectory, t.id_file tFile,
p.filename previewfilename,  p.directory previewdirectory, p.id_file pFile
 from ({db_prefix}aeva_media as m)
LEFT JOIN {db_prefix}aeva_files as f on (f.id_file = m.id_file)
LEFT JOIN {db_prefix}aeva_files as t on (t.id_file = m.id_thumb)
LEFT JOIN {db_prefix}aeva_files as p on (p.id_file = m.id_preview)
			$notInSQL2
			LIMIT " . $_REQUEST['start'] . ","  . ( $increment));


		while ($row = $smcFunc['db_fetch_assoc']($request))
		{
				$mediumfilename = '';
				$thumbfilename = '';
				$filename = '';

				echo 'Processing: ' . $row['id_media'] . '<br />';
				flush();

				$skipExtra = false;
				if (isset($_REQUEST['skip']))
				{
					if ($row['id_media'] == $_REQUEST['skip'])
						$skipExtra = true;
				}
				
				$skipEntry = false;
				
				// Media already inserted???
				
		$requestCount = $smcFunc['db_query']('', "select count(*) as total from {db_prefix}gallery_pic where id_picture = " .$row['id_media']);
		$picCount = $smcFunc['db_fetch_assoc']($requestCount);
				if ($picCount['total'] > 0)	
					$skipEntry  = true;

				if ($skipEntry == false)
				{
	
					// Make direcotries
					$myDirectories = explode("/",$row['directory']);
					//print_R($myDirectories);
					$myDirParent = $modSettings['gallery_path'] ;
					$myDirPathSmall = '';
					foreach($myDirectories as $myDir)
					{
						$myDirParent .= $myDir . "/";
						$myDirPathSmall .= $myDir . "/";
						//echo 'Mkdir ' . $myDir . "<br />";
						if (!file_exists($myDirParent))
							@mkdir($myDirParent,755);
					}
	
	
	
					if (!empty($row['thumbfilename']))
					{
						$extension = substr(strrchr($row['thumbfilename'], '.'), 1);
						$thumbfilename  = $row['directory'] . "/" . 'thumb_' . $row['id_media'] . '.' . $extension;
						$tmp= aeva_getEncryptedFilename($row['thumbfilename'], $row['id_thumb'],true);
						@copy($AevaSettings['data_dir_path'] . "/" .  $row['directory'] . "/" . $tmp, $modSettings['gallery_path'] .  $thumbfilename);
						@chmod($myDirParent . $mediumfilename, 0755);
	
	
					}
	
					// Copy Medium Size
					if (!empty($row['previewfilename']))
					{
						$extension = substr(strrchr($row['previewfilename'], '.'), 1);
						$mediumfilename = $row['directory'] . "/" . 'medium_' . $row['id_media'] . '.' . $extension;
						$tmp= aeva_getEncryptedFilename($row['previewfilename'], $row['id_preview'],false);
						@copy($AevaSettings['data_dir_path'] . "/" .  $row['directory'] . "/" . $tmp, $modSettings['gallery_path']  . $mediumfilename );
						@chmod($myDirParent . $thumbfilename, 0755);
					}
					else
					{
						if ($row['type'] == 'embed')
						{
							$extension = substr(strrchr($row['thumbfilename'], '.'), 1);
							$mediumfilename = $row['directory'] . "/" . 'medium_' . $row['id_media'] . '.' . $extension;
	
							$tmp= aeva_getEncryptedFilename($row['thumbfilename'], $row['id_thumb'],true);
							@copy($AevaSettings['data_dir_path'] . "/" .  $row['directory'] . "/" . $tmp, $modSettings['gallery_path']  . $mediumfilename );
							@chmod($myDirParent . $mediumfilename, 0755);
						}
					}
	
	
					// Copy file
					if (!empty($row['filename']))
					{
						$extension = substr(strrchr($row['filename'], '.'), 1);
						$filename = $row['directory'] . "/" . 'main_' . $row['id_media'] . '.' . $extension;
						$tmp= aeva_getEncryptedFilename($row['filename'], $row['id_file'],false);
	
						@copy($AevaSettings['data_dir_path'] . "/" .  $row['directory'] . "/" . $tmp, $modSettings['gallery_path'] . $filename);
						@chmod($myDirParent . $filename, 0755);
					}
					else
					{
	
						if ($row['type'] == 'embed')
						{
							$extension = substr(strrchr($row['thumbfilename'], '.'), 1);
							$filename = $row['directory'] . "/" . 'main_' . $row['id_media'] . '.' . $extension;
	
							$tmp= aeva_getEncryptedFilename($row['thumbfilename'], $row['id_thumb'],true);
							@copy($AevaSettings['data_dir_path'] . "/" .  $row['directory'] . "/" . $tmp, $modSettings['gallery_path']  . $filename);
							@chmod($myDirParent . $filename, 0755);
						}
	
					}
	
					if(empty($mediumfilename) && $skipExtra == false)
					{
	
						@createThumbnail($modSettings['gallery_path'] . $filename, $modSettings['gallery_medium_width'], $modSettings['gallery_medium_height']);
						$renameResult = @rename($modSettings['gallery_path'] . $filename . '_thumb',  $modSettings['gallery_path'] .  $row['directory'] . "/" .  'medium_' . $row['id_media'] . "." . $extension);
	
						if ($renameResult == true)
						{
							@$mediumfilename =  $row['directory'] . "/" .  'medium_' . $row['id_media'] . "." . $extension;
							@chmod($modSettings['gallery_path'] . $row['directory'] . "/" .  'medium_' . $row['id_media'] . $extension, 0755);
						}
						else
							$skipExtra = true;
					}
	
					// Embed videos
					/*
	                if ($row['type'] == 'embed')
					{
	
						$finalEmbed =$row['embed_url'] .  $row['description'];
	
						$row['description'] = $finalEmbed;
					}
	                */
	                 // Convert linked images
	                if (!empty($row['embed_url']))
	                {
	                      $finalFilname = str_replace("/","",$row['filename']);
	                      $filename = $finalFilname;
	
	                        $destination = $modSettings['gallery_path']  . $finalFilname;
	                        $source = $row['embed_url'];
	
	                    	// Get the image file, we have to work with something after all
	                    	$fp_destination = fopen($destination, 'wb');
	                    	if ($fp_destination && substr($source, 0, 7) == 'http://')
	                    	{
	                    		$fileContents = fetch_web_data($source);
	
	                    		fwrite($fp_destination, $fileContents);
	                    		fclose($fp_destination);
	
	                    		$sizes = @getimagesize($destination);
	                    	}
	                    	elseif ($fp_destination)
	                    	{
	                    		$sizes = @getimagesize($source);
	
	                    		$fp_source = fopen($source, 'rb');
	                    		if ($fp_source !== false)
	                    		{
	                    			while (!feof($fp_source))
	                    				fwrite($fp_destination, fread($fp_source, 8192));
	                    			fclose($fp_source);
	                    		}
	                    		else
	                    			$sizes = array(-1, -1, -1);
	                    		fclose($fp_destination);
	                    	}
	                    	// We can't get to the file.
	                    	else
	                    		$sizes = array(-1, -1, -1);
	
	                    		// Create thumbnail
	                    		createThumbnail($modSettings['gallery_path']  . $finalFilname, $modSettings['gallery_thumb_width'], $modSettings['gallery_thumb_height']);
	                            $thumbname = 'thumb_' . $finalFilname;
	                    		rename($modSettings['gallery_path']  . $finalFilname . '_thumb',  $modSettings['gallery_path']  . $thumbname );
	
	                    		@chmod($modSettings['gallery_path']  . $thumbname , 0755);
	
	                    		// Medium Image
	                    			createThumbnail($modSettings['gallery_path']  . $finalFilname, $modSettings['gallery_medium_width'], $modSettings['gallery_medium_height']);
	                                $mediumimage = 'medium_' . $finalFilname;
	                    			rename($modSettings['gallery_path']  .  $finalFilname. '_thumb',  $modSettings['gallery_path']  .$mediumimage);
	
	                    			@chmod($modSettings['gallery_path'] . $mediumimage, 0755);
	
	                }
	
	
	$row['filesize'] = (int) $row['filesize'];
	$row['height'] = (int) $row['height'];
	$row['width'] = (int) $row['width'];
	$row['voters'] = (int)  $row['voters'];
	$row['rating'] = (int)  $row['rating'];
	
						 $smcFunc['db_query']('', "INSERT INTO {db_prefix}gallery_pic
						(id_picture,title,description,id_member,id_cat,
						views, filesize, height, width,
	filename, thumbfilename, mediumfilename,totalratings,rating,
	commenttotal, approved, date, keywords,allowcomments )
						VALUES ('" . $row['id_media'] . "','" . addslashes($row['title']) . "',
						'" . addslashes($row['description']) . "', '" . $row['id_member'] . "', '" . $row['album_id'] . "', '" . $row['views'] . "',
						'" . $row['filesize'] .  "', '" . $row['height'] . "', '" . $row['width'] . "',
						 '" . addslashes($filename) . "', '" . addslashes($thumbfilename) . "','" . addslashes($mediumfilename) . "','" . $row['voters']  . "','" . $row['rating']  . "',
						 '" . $row['num_comments'] .  "',
						 '" . $row['approved'] . "','" . $row['time_added'] . "'
						 , '" . addslashes($row['keywords']) ."',1)");
	
						$picID =  $smcFunc['db_insert_id']('{db_prefix}gallery_pic', 'id_picture');
						if ($row['exif']!= ''  && $skipExtra == false)
							ProcessEXIFData($filename,$picID);
			} // end skip entry



		}
		$smcFunc['db_free_result']($request);

		$_REQUEST['start'] += $increment;

		// Continue?
		if($_REQUEST['start'] < $totalProcess)
		{

			$context['continue_get_data'] = ';start=' . $_REQUEST['start'];

			$context['continue_post_data'] = "<input type='hidden' name='post_data' value='$notInSQL' />";
			$context['continue_percent'] = round(100 * $_REQUEST['start'] / $totalProcess);


		}
		else
			$complete = 1;


        $context['page_title'] = $txt['gallery_import_mainpictures'];
        $context['import_step_title'] = $context['page_title'];

		if ($complete == 0)
			CountDownCode('import2');
		else
		{
			CountDownCode('import2b');
			$context['continue_post_data'] = "<input type='hidden' name='post_data' value='$notInSQL' />";

		}

}


function AevaImport2b()
{
    global $context, $smcFunc, $modSettings, $AevaSettings, $txt;

    $complete = 0;

		$notInSQL = '';


		$result =  $smcFunc['db_query']('', "SELECT ID_CAT FROM {db_prefix}gallery_cat
				where redirect = 1");
        $catRow = $smcFunc['db_fetch_assoc']($result);
        $mainCategoryID = (int) $catRow['ID_CAT'];


		if (isset($_REQUEST['post_data']))
			$notInSQL = $_REQUEST['post_data'];

            $notInSQL2  = str_replace("id_album NOT IN","m.album_id IN",$notInSQL);



		$context['start'] = empty($_REQUEST['start']) ? 30 : (int) $_REQUEST['start'];
		$context['start_time'] =  time();

		$notInSQL = $_REQUEST['post_data'];




		// Determine the total members with posts.

		$request = $smcFunc['db_query']('', "
			SELECT COUNT(*) from ({db_prefix}aeva_media as m)
LEFT JOIN {db_prefix}aeva_files as f on (f.id_file = m.id_file)
LEFT JOIN {db_prefix}aeva_files as t on (t.id_file = m.id_thumb)
LEFT JOIN {db_prefix}aeva_files as p on (p.id_file = m.id_preview)
$notInSQL2 ");
		list($totalProcess) = $smcFunc['db_fetch_row']($request);
		$smcFunc['db_free_result']($request);

		// Initialize the variables.
		$increment =25;
		if (empty($_REQUEST['start']))
			$_REQUEST['start'] = 0;

		// Grab the first set of members to verify.
		$request = $smcFunc['db_query']('', "
			select m.id_media, m.id_member,
m.id_file, m.id_thumb, m.id_preview,  m.type, m.title,
m.description, m.approved, m.album_id, m.time_added, m.views, m.keywords,
m.num_comments, m.voters, m.rating, f.exif,
f.filename, f.filesize, f.directory, f.width, f.height,  f.id_file fFile,
t.filename thumbfilename, t.directory thumbdirectory, t.id_file tFile,
p.filename previewfilename,  p.directory previewdirectory, p.id_file pFile
 from ({db_prefix}aeva_media as m)
LEFT JOIN {db_prefix}aeva_files as f on (f.id_file = m.id_file)
LEFT JOIN {db_prefix}aeva_files as t on (t.id_file = m.id_thumb)
LEFT JOIN {db_prefix}aeva_files as p on (p.id_file = m.id_preview)
			$notInSQL2
			LIMIT " . $_REQUEST['start'] . ","  . ( $increment));

		while ($row = $smcFunc['db_fetch_assoc']($request))
		{
				$mediumfilename = '';
				$thumbfilename = '';
				$filename = '';

				echo 'Processing: ' . $row['id_media'] . '<br />';
				flush();

				$skipExtra = false;
				if (isset($_REQUEST['skip']))
				{
					if ($row['id_media'] == $_REQUEST['skip'])
						$skipExtra = true;
				}

				// Make direcotries
				$myDirectories = explode("/",$row['directory']);
				//print_R($myDirectories);
				$myDirParent = $modSettings['gallery_path'] ;
				$myDirPathSmall = '';
				foreach($myDirectories as $myDir)
				{
					$myDirParent .= $myDir . "/";
					$myDirPathSmall .= $myDir . "/";
					//echo 'Mkdir ' . $myDir . "<br />";
					if (!file_exists($myDirParent))
						@mkdir($myDirParent,755);
				}



				if (!empty($row['thumbfilename']) )
				{
					$extension = substr(strrchr($row['thumbfilename'], '.'), 1);
					$thumbfilename  = $row['directory'] . "/" . 'thumb_' . $row['id_media'] . '.' . $extension;
					$tmp= aeva_getEncryptedFilename($row['thumbfilename'], $row['id_thumb'],true);
					@copy($AevaSettings['data_dir_path'] . "/" .  $row['directory'] . "/" . $tmp, $modSettings['gallery_path'] .  $thumbfilename);
					@chmod($myDirParent . $mediumfilename, 0755);


				}

				// Copy Medium Size
				if (!empty($row['previewfilename']))
				{
					$extension = substr(strrchr($row['previewfilename'], '.'), 1);
					$mediumfilename = $row['directory'] . "/" . 'medium_' . $row['id_media'] . '.' . $extension;
					$tmp= aeva_getEncryptedFilename($row['previewfilename'], $row['id_preview'],false);
					@copy($AevaSettings['data_dir_path'] . "/" .  $row['directory'] . "/" . $tmp, $modSettings['gallery_path']  . $mediumfilename );
					@chmod($myDirParent . $thumbfilename, 0755);
				}


				// Copy file
				if (!empty($row['filename']))
				{
					$extension = substr(strrchr($row['filename'], '.'), 1);
					$filename = $row['directory'] . "/" . 'main_' . $row['id_media'] . '.' . $extension;
					$tmp= aeva_getEncryptedFilename($row['filename'], $row['id_file'],false);

					@copy($AevaSettings['data_dir_path'] . "/" .  $row['directory'] . "/" . $tmp, $modSettings['gallery_path'] . $filename);
					@chmod($myDirParent . $filename, 0755);
				}

				if(empty($mediumfilename) && $skipExtra == false)
				{

					@createThumbnail($modSettings['gallery_path'] . $filename, $modSettings['gallery_medium_width'], $modSettings['gallery_medium_height']);
					$renameResult = @rename($modSettings['gallery_path'] . $filename . '_thumb',  $modSettings['gallery_path'] .  $row['directory'] . "/" .  'medium_' . $row['id_media'] . "." . $extension);
					if ($renameResult == true)
					{
						@$mediumfilename =  $row['directory'] . "/" .  'medium_' . $row['id_media'] . "." . $extension;
						@chmod($modSettings['gallery_path'] . $row['directory'] . "/" .  'medium_' . $row['id_media'] . $extension, 0755);
					}
					else
						$skipExtra = true;
				}


                // Convert linked images
                if (!empty($row['embed_url']))
                {
                      $finalFilname = str_replace("/","",$row['filename']);
                      $filename = $finalFilname;

                        $destination = $modSettings['gallery_path']  . $finalFilname;
                        $source = $row['embed_url'];

                    	// Get the image file, we have to work with something after all
                    	$fp_destination = fopen($destination, 'wb');
                    	if ($fp_destination && substr($source, 0, 7) == 'http://')
                    	{
                    		$fileContents = fetch_web_data($source);

                    		fwrite($fp_destination, $fileContents);
                    		fclose($fp_destination);

                    		$sizes = @getimagesize($destination);
                    	}
                    	elseif ($fp_destination)
                    	{
                    		$sizes = @getimagesize($source);

                    		$fp_source = fopen($source, 'rb');
                    		if ($fp_source !== false)
                    		{
                    			while (!feof($fp_source))
                    				fwrite($fp_destination, fread($fp_source, 8192));
                    			fclose($fp_source);
                    		}
                    		else
                    			$sizes = array(-1, -1, -1);
                    		fclose($fp_destination);
                    	}
                    	// We can't get to the file.
                    	else
                    		$sizes = array(-1, -1, -1);




                    		// Create thumbnail
                    		createThumbnail($modSettings['gallery_path']  . $finalFilname, $modSettings['gallery_thumb_width'], $modSettings['gallery_thumb_height']);
                            $thumbname = 'thumb_' . $finalFilname;
                    		rename($modSettings['gallery_path']  . $finalFilname . '_thumb',  $modSettings['gallery_path']  . $thumbname );

                    		@chmod($modSettings['gallery_path']  . $thumbname , 0755);

                    		// Medium Image


                    			createThumbnail($modSettings['gallery_path']  . $finalFilname, $modSettings['gallery_medium_width'], $modSettings['gallery_medium_height']);
                                $mediumimage = 'medium_' . $finalFilname;
                    			rename($modSettings['gallery_path']  .  $finalFilname. '_thumb',  $modSettings['gallery_path']  .$mediumimage);

                    			@chmod($modSettings['gallery_path'] . $mediumimage, 0755);

                }





					 $smcFunc['db_query']('', "INSERT ignore INTO {db_prefix}gallery_pic
					(id_picture,title,description,id_member,user_id_cat,id_cat,
					views, filesize, height, width,
filename, thumbfilename, mediumfilename,totalratings,rating,
commenttotal, approved, date, keywords,allowcomments)
					VALUES ('" . $row['id_media'] . "','" . addslashes($row['title']) . "',
					'" . addslashes($row['description']) . "', '" . $row['id_member'] . "', '" . $row['album_id'] . "', '$mainCategoryID', '" . $row['views'] . "',
					'" . $row['filesize'] .  "', '" . $row['height'] . "', '" . $row['width'] . "',
					 '" . addslashes($filename) . "', '" . addslashes($thumbfilename) . "','" . addslashes($mediumfilename) . "','" . $row['voters']  . "','" . $row['rating']  . "',
					 '" . $row['num_comments'] .  "',
					 '" . $row['approved'] . "','" . $row['time_added'] . "'
					 , '" . addslashes($row['keywords']) ."',1)");

					$picID =  $smcFunc['db_insert_id']('{db_prefix}gallery_pic', 'id_picture');
					if ($row['exif']!= ''  && $skipExtra == false && !empty($picID))
						ProcessEXIFData($filename,$picID);



		}
		$smcFunc['db_free_result']($request);


		$_REQUEST['start'] += $increment;

		// Continue?
		if($_REQUEST['start'] < $totalProcess)
		{

			$context['continue_get_data'] = ';start=' . $_REQUEST['start'];

			$context['continue_post_data'] = "<input type='hidden' name='post_data' value='$notInSQL' />";
			$context['continue_percent'] = round(100 * $_REQUEST['start'] / $totalProcess);


		}
		else
			$complete = 1;


        $context['page_title'] = $txt['gallery_import_userpictures'];
        $context['import_step_title'] = $context['page_title'];

		if ($complete == 0)
			CountDownCode('import2b');
		else
			CountDownCode('import3');

}

function AevaImport3()
{
    global $context, $smcFunc, $txt;

    $complete = 0;


        $context['page_title'] = $txt['gallery_import_ratings'];
        $context['import_step_title'] = $context['page_title'];


		$context['start'] = empty($_REQUEST['start']) ? 35 : (int) $_REQUEST['start'];
		$context['start_time'] =  time();


		// Determine the total members with posts.
		$request = $smcFunc['db_query']('', "
			SELECT COUNT(*) FROM {db_prefix}aeva_log_ratings ");
		list($totalProcess) = $smcFunc['db_fetch_row']($request);
		$smcFunc['db_free_result']($request);

		// Initialize the variables.
		$increment = 35;
		if (empty($_REQUEST['start']))
			$_REQUEST['start'] = 0;

		// Grab the first set of members to verify.
		$request =  $smcFunc['db_query']('', "
			SELECT id_media, rating, id_member FROM {db_prefix}aeva_log_ratings

			LIMIT " . $_REQUEST['start'] . ","  . ( $increment));

		while ($row = $smcFunc['db_fetch_assoc']($request))
		{
					 $smcFunc['db_query']('', "INSERT INTO {db_prefix}gallery_rating
					(id_picture,id_member, value)
					VALUES ('" . $row['id_media'] . "','" . $row['rating']. "','3')");

		}
		$smcFunc['db_free_result']($request);

		$_REQUEST['start'] += $increment;

		// Continue?
		if($_REQUEST['start'] < $totalProcess)
		{

			$context['continue_get_data'] = ';start=' . $_REQUEST['start'];
			$context['continue_percent'] = round(100 * $_REQUEST['start'] / $totalProcess);


		}
		else
			$complete = 1;


		if ($complete == 0)
			CountDownCode('import3');
		else
			CountDownCode('import4');

}

function AevaImport4()
{
    global $context, $smcFunc, $txt;

    $complete = 0;


	$context['start'] = empty($_REQUEST['start']) ? 35 : (int) $_REQUEST['start'];
	$context['start_time'] =  time();


	// Determine the total members with posts.
	$request = $smcFunc['db_query']('', "
		SELECT COUNT(*) FROM {db_prefix}aeva_comments ");
	list($totalProcess) = $smcFunc['db_fetch_row']($request);
	$smcFunc['db_free_result']($request);

	// Initialize the variables.
	$increment = 35;
	if (empty($_REQUEST['start']))
		$_REQUEST['start'] = 0;

	// Grab the first set of members to verify.
	$request = $smcFunc['db_query']('', "
		SELECT id_comment, id_media, id_member, message, posted_on, approved FROM {db_prefix}aeva_comments

		LIMIT " . $_REQUEST['start'] . ","  . ( $increment));

	while ($row = $smcFunc['db_fetch_assoc']($request))
	{

				 $smcFunc['db_query']('', "INSERT INTO {db_prefix}gallery_comment
				(ID_COMMENT,id_picture,id_member, comment, date, approved)
				VALUES ('" . $row['id_comment'] . "','" . $row['id_media']. "','" . $row['id_member']. "',
				'" . addslashes($row['message']). "','" . $row['posted_on']. "','" . $row['approved']. "')");

	}
	$smcFunc['db_free_result']($request);

	$_REQUEST['start'] += $increment;

	// Continue?
	if($_REQUEST['start'] < $totalProcess)
	{

		$context['continue_get_data'] = ';start=' . $_REQUEST['start'];
		$context['continue_percent'] = round(100 * $_REQUEST['start'] / $totalProcess);


	}
	else
		$complete = 1;


        $context['page_title'] = $txt['gallery_import_comments'];
        $context['import_step_title'] = $context['page_title'];

		if ($complete == 0)
			CountDownCode('import4');
		else
			CountDownCode('import5');
}


function AevaImport5()
{
    global $context, $smcFunc, $txt;

    // Import Unviewed Log
    		$complete = 0;


    $context['start'] = empty($_REQUEST['start']) ? 60 : (int) $_REQUEST['start'];
	$context['start_time'] =  time();


	// Determine the total members with posts.
	$request = $smcFunc['db_query']('', "
		SELECT COUNT(*) FROM {db_prefix}aeva_log_media as m, {db_prefix}gallery_pic as p
		WHERE p.id_picture = m.id_media ");
	list($totalProcess) = $smcFunc['db_fetch_row']($request);
	$smcFunc['db_free_result']($request);

	// Initialize the variables.
	$increment = 60;
	if (empty($_REQUEST['start']))
		$_REQUEST['start'] = 0;

	// Grab the first set of members to verify.
	$request = $smcFunc['db_query']('', "
		SELECT m.id_media, m.id_member, p.user_id_cat, p.id_cat
		FROM {db_prefix}aeva_log_media as m, {db_prefix}gallery_pic as p
		WHERE p.id_picture = m.id_media

		LIMIT " . $_REQUEST['start'] . ","  . ( $increment));

	while ($row = $smcFunc['db_fetch_assoc']($request))
	{
				 $smcFunc['db_query']('', "INSERT INTO {db_prefix}gallery_log_mark_view
				(id_picture,id_member, id_cat, user_id_cat)
				VALUES ('" . $row['id_media'] . "','" . $row['id_member']. "',
				'" . $row['id_cat']. "','" . $row['user_id_cat']. "')");

	}
	$smcFunc['db_free_result']($request);

	$_REQUEST['start'] += $increment;

	// Continue?
	if($_REQUEST['start'] < $totalProcess)
	{

		$context['continue_get_data'] = ';start=' . $_REQUEST['start'];
		$context['continue_percent'] = round(100 * $_REQUEST['start'] / $totalProcess);


	}
	else
		$complete = 1;

        $context['page_title'] =  $txt['gallery_import_unviewedlog'];
        $context['import_step_title'] = $context['page_title'];


		if ($complete == 0)
			CountDownCode('import5');
		else
			CountDownCode('import6');
}

function AevaImport6()
{
    global $txt, $context, $smcFunc;

  		$complete = 0;

        $context['page_title'] =  $txt['gallery_import_recounting'];
        $context['import_step_title'] = $context['page_title'];



		$dbresult =  $smcFunc['db_query']('', "SELECT ID_CAT FROM {db_prefix}gallery_cat");
		while($row = $smcFunc['db_fetch_assoc']($dbresult))
		{
			UpdateCategoryTotals2($row['ID_CAT']);
		}
		$smcFunc['db_free_result']($dbresult);

		echo 'Recounting User Galleries...<br />';
		$dbresult = $smcFunc['db_query']('', "SELECT USER_ID_CAT FROM {db_prefix}gallery_usercat");
		while($row = $smcFunc['db_fetch_assoc']($dbresult))
		{
			UpdateUserCategoryTotals2($row['USER_ID_CAT']);
		}
		$smcFunc['db_free_result']($dbresult);

		$complete = 1;


		if ($complete == 0)
			CountDownCode('import6');
		else
			CountDownCode('import7');
}

function AevaImport7()
{
    global $txt, $context, $scripturl;

    updateSettings(
		array(
		'gallery_avea_imported' => 1,
	));



    $context['page_title'] = $txt['gallery_import_complete'];
    $context['sub_template']  = 'import_completeaeva';

}


function UpdateCategoryTotals2($ID_CAT)
{
	global $smcFunc;

	$dbresult = $smcFunc['db_query']('', "SELECT COUNT(*) AS total FROM {db_prefix}gallery_pic WHERE ID_CAT = $ID_CAT AND approved = 1");
	$row = $smcFunc['db_fetch_assoc']($dbresult);
	$total = $row['total'];
	$smcFunc['db_free_result']($dbresult);

	if (empty($total))
		$total = 0;

	// Update the count
	//$dbresult = $smcFunc['db_query']('', "UPDATE {db_prefix}gallery_cat SET total = $total WHERE ID_CAT = $ID_CAT LIMIT 1");

}

function UpdateUserCategoryTotals2($USER_ID_CAT)
{
	global $smcFunc;

	$dbresult = $smcFunc['db_query']('', "SELECT COUNT(*) AS total FROM {db_prefix}gallery_pic WHERE USER_ID_CAT = $USER_ID_CAT AND approved = 1");
	$row = $smcFunc['db_fetch_assoc']($dbresult);
	$total = $row['total'];
	$smcFunc['db_free_result']($dbresult);

	if (empty($total))
		$total = 0;

	// Update the count
	$dbresult = $smcFunc['db_query']('', "UPDATE {db_prefix}gallery_usercat SET total = $total WHERE USER_ID_CAT = $USER_ID_CAT LIMIT 1");

}

function CountDownCode($actionName = '')
{
	global $context, $scripturl;

    $context['continue_action'] = $actionName;
	$context['sub_template']  = 'importconvert';


}

function ImportGalleryMainCategory($id_album,$notInSQL)
{
	global $smcFunc;

	$resultFind  = $smcFunc['db_query']('', "
			SELECT id_album, album_of, name, description, parent, a_order, featured, master
			FROM {db_prefix}aeva_albums
			WHERE parent = $id_album
			");
	$row = $smcFunc['db_fetch_assoc']($resultFind);
	if (empty($row['id_album']))
		return $notInSQL;

				if (empty($notInSQL))
				{
					$notInSQL .= ' WHERE id_album NOT IN(' . $row['id_album'];
				}
				else
					$notInSQL .= ', ' . $row['id_album'];

				 $smcFunc['db_query']('', "INSERT INTO {db_prefix}gallery_cat
				(id_cat,title,description,id_parent,roworder)
				VALUES ('" . $row['id_album'] . "','" . addslashes($row['name']) . "','" . addslashes($row['description']) . "','" . $row['parent'] . "','" . $row['a_order'] . "' )");

				$notInSQL = ImportGalleryMainCategory($row['id_album'],$notInSQL);

		return $notInSQL;
}


function ProcessEXIFData($filename,$pictureid)
{
	global $smcFunc, $boarddir, $modSettings;

	if (empty($modSettings['gallery_path']))
		$modSettings['gallery_path'] = $boarddir . '/gallery/';

	$filename = $modSettings['gallery_path'] . $filename;

	// Check if EXIF Data exists
	if (!function_exists('exif_read_data'))
		return;


	// Check if extension supports EXIF
	$extension = strtolower(substr(strrchr($filename, '.'), 1));
	if ($extension != 'jpg' && $extension != 'jpeg' && $extension != 'tiff')
		return;

	// Read the EXIF data from the picture
	$exifData = @exif_read_data($filename, 'ANY_TAG', true);

	// Check if any data was found
	if (!$exifData)
		return;

	foreach(@$exifData['FILE'] as $record => $value)
	{
		@$exifData['FILE'][$record] = addslashes(htmlspecialchars($value,ENT_QUOTES));
	}
	foreach(@$exifData['COMPUTED'] as $record => $value)
	{
		@$exifData['COMPUTED'][$record] = addslashes(htmlspecialchars($value,ENT_QUOTES));
	}
	foreach(@$exifData['IFD0'] as $record => $value)
	{
		@$exifData['IFD0'][$record] = addslashes(htmlspecialchars($value,ENT_QUOTES));
	}
	foreach(@$exifData['EXIF'] as $record => $value)
	{
		@$exifData['EXIF'][$record] = addslashes(htmlspecialchars($value,ENT_QUOTES));
	}

	// Insert the exif data
	 $smcFunc['db_query']('', "
	REPLACE INTO {db_prefix}gallery_exif_data
	(ID_PICTURE,
	file_filedatetime,file_filesize,file_filetype,file_mimetype,file_sectionsfound,computed_height,
	computed_width,computed_iscolor,computed_ccdwidth,computed_aperturefnumber,computed_copyright,
	idfo_imagedescription,idfo_make,idfo_model,idfo_orientation,idfo_xresolution,idfo_yresolution,
	idfo_resolutionunit,idfo_software,idfo_datetime,idfo_artist,exif_exposuretime, exif_fnumber,exif_exposureprogram,
	exif_isospeedratings,exif_exifversion,exif_datetimeoriginal,exif_datetimedigitized,exif_shutterspeedvalue,
	exif_aperturevalue,exif_exposurebiasvalue,exif_maxaperturevalue,exif_meteringmode,exif_lightsource,
	exif_flash,exif_focallength,exif_colorspace,exif_exifimagewidth,exif_exifimagelength,exif_focalplanexresolution,
	exif_focalplaneyresolution,exif_focalplaneresolutionunit, exif_customrendered,exif_exposuremode,
	exif_whitebalance,exif_scenecapturetype
	)
	VALUES ('$pictureid',
	'" . @$exifData['FILE']['FileDateTime']. "','" . @$exifData['FILE']['FileSize']. "','" . @$exifData['FILE']['FileType']. "','" . @$exifData['FILE']['MimeType']. "','" . @$exifData['FILE']['SectionsFound']. "',
	'" . @$exifData['COMPUTED']['Height']. "',
	'" . @$exifData['COMPUTED']['Width']. "','" . @$exifData['COMPUTED']['IsColor']. "','" . @$exifData['COMPUTED']['CCDWidth']. "','" . @$exifData['COMPUTED']['ApertureFNumber']. "','" . @$exifData['COMPUTED']['Copyright']. "',
	'" . @$exifData['IFD0']['ImageDescription']. "','" . @$exifData['IFD0']['Make']. "','" . @$exifData['IFD0']['Model']. "','" . @$exifData['IFD0']['Orientation']. "','" . @$exifData['IFD0']['XResolution']. "','" . @$exifData['IFD0']['YResolution']. "',
	'" . @$exifData['IFD0']['ResolutionUnit']. "','" . @$exifData['IFD0']['Software']. "','" . @$exifData['IFD0']['DateTime']. "','" . @$exifData['IFD0']['Artist']. "','" . @$exifData['EXIF']['ExposureTime']. "','" . @$exifData['EXIF']['FNumber']. "','" . @$exifData['EXIF']['ExposureProgram']. "',
	'" . @$exifData['EXIF']['ISOSpeedRatings']. "','" . @$exifData['EXIF']['ExifVersion']. "','" . @$exifData['EXIF']['DateTimeOriginal']. "','" . @$exifData['EXIF']['DateTimeDigitized']. "','" . @$exifData['EXIF']['ShutterSpeedValue']. "',
	'" . @$exifData['EXIF']['ApertureValue']. "','" . @$exifData['EXIF']['ExposureBiasValue']. "','" . @$exifData['EXIF']['MaxApertureValue']. "','" . @$exifData['EXIF']['MeteringMode']. "','" . @$exifData['EXIF']['LightSource']. "',
	'" . @$exifData['EXIF']['Flash']. "','" . @$exifData['EXIF']['FocalLength']. "','" . @$exifData['EXIF']['ColorSpace']. "','" . @$exifData['EXIF']['ExifImageWidth']. "','" . @$exifData['EXIF']['ExifImageLength']. "','" . @$exifData['EXIF']['FocalPlaneXResolution']. "',
	'" . @$exifData['EXIF']['FocalPlaneYResolution']. "','" . @$exifData['EXIF']['FocalPlaneResolutionUnit']. "','" . @$exifData['EXIF']['CustomRendered']. "','" . @$exifData['EXIF']['ExposureMode']. "',
	'" . @$exifData['EXIF']['WhiteBalance']. "','" . @$exifData['EXIF']['SceneCaptureType']. "')");



}




?>