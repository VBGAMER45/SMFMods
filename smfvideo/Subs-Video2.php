<?php
/*
SMF Gallery Pro - Video Addon
Version 4.0
by: vbgamer45
http://www.smfhacks.com
Copyright 2006-2013 http://www.samsonsoftware.com

############################################
License Information:
SMF Gallery Pro - Video Addon is NOT free software.
This software may not be redistributed.

The pro edition license is good for a single instance / install on a website.
You are allowed only one active install for each license purchase.

Links to http://www.smfhacks.com must remain unless
branding free option is purchased.
#############################################
*/

function AddVideo()
{
	global $context, $mbname, $txt, $modSettings, $user_info, $smcFunc, $sourcedir, $gallerySettings;

	isAllowedTo('smfgalleryvideo_add');

	loadtemplate('Video2');

	@$cat = (int) $_REQUEST['cat'];
	$context['gallery_user_id'] = 0;

	if (!isset($_REQUEST['u']))
	{
		GetCatPermission($cat,'addvideo');

		if ($context['user']['is_guest'])
			$groupid = -1;
		else
			$groupid =  $user_info['groups'][0];

		$dbresult = $smcFunc['db_query']('', "
		SELECT c.ID_CAT, c.title, p.view, p.addvideo
		FROM {db_prefix}gallery_cat AS c
		LEFT JOIN {db_prefix}gallery_catperm AS p ON (p.ID_GROUP = $groupid AND c.ID_CAT = p.ID_CAT)
		WHERE c.redirect = 0 ORDER BY c.roworder ASC");
		if ($smcFunc['db_num_rows']($dbresult) == 0)
		 	fatal_error($txt['gallery_error_no_catexists'] , false);

		$context['gallery_cat'] = array();
		 while($row = $smcFunc['db_fetch_assoc']($dbresult))
			{
				// Check if they have permission to add to this category.
				if ($row['view'] == '0' || $row['addvideo'] == '0' )
					continue;

				$context['gallery_cat'][] = $row;
			}
		$smcFunc['db_free_result']($dbresult);
	}
	else
	{
		//This is a user gallery add video
		$u = (int) $_REQUEST['u'];
		$context['gallery_user_id'] = $u;
		$g_manage = allowedTo('smfgallery_manage');
		$g_gallery = allowedTo('smfgallery_usergallery');
		// Check permissions
		if(!$g_manage && ($user_info['id'] != $u || !$g_gallery))
			fatal_error($txt['gallery_user_noperm'],false);

		$dbresult = $smcFunc['db_query']('', "
		SELECT
			USER_ID_CAT, title, roworder
		FROM {db_prefix}gallery_usercat
		WHERE ID_MEMBER = $u ORDER BY roworder ASC");
		if ($smcFunc['db_num_rows']($dbresult) == 0)
		 	fatal_error($txt['gallery_error_no_catexists'] , false);

		$context['gallery_cat'] = array();

		while($row = $smcFunc['db_fetch_assoc']($dbresult))
			{
				// ID_CAT on purpose for Add Video page
				$context['gallery_cat'][] = array(
					'ID_CAT' => $row['USER_ID_CAT'],
					'title' => $row['title'],
					'roworder' => $row['roworder'],
				);
			}
		$smcFunc['db_free_result']($dbresult);

	}
	$context['sub_template']  = 'addvideo';
	$context['page_title'] = $mbname . ' - ' . $txt['gallery_text_title'] . ' - ' . $txt['gallery_form_addvideo'];
	// Check if spellchecking is both enabled and actually working.
	$context['show_spellchecking'] = !empty($modSettings['enableSpellChecking']) && function_exists('pspell_new');


	// Used for the editor
	require_once($sourcedir . '/Subs-Editor.php');

	// Now create the editor.
		$editorOptions = array(
			'id' => 'description',
			'value' => '',
			'width' => '90%',
			'form' => 'picform',
			'labels' => array(
				'post_button' => ''
			),
		);


		create_control_richedit($editorOptions);
		$context['post_box_name'] = $editorOptions['id'];


}

function AddVideo2()
{
	global $txt, $scripturl, $modSettings, $sourcedir, $gd2, $user_info, $boardurl, $smcFunc, $gallerySettings;

	isAllowedTo('smfgallery_add');

	// Check if gallery path is writable
	if (!is_writable($modSettings['gallery_path']))
		fatal_error($txt['gallery_write_error'] . $modSettings['gallery_path']);

	$title = htmlspecialchars($_REQUEST['title'],ENT_QUOTES);
	$description = htmlspecialchars($_REQUEST['description'],ENT_QUOTES);
	$keywords = htmlspecialchars($_REQUEST['keywords'],ENT_QUOTES);
	$cat = (int) $_REQUEST['cat'];
	$userid = (int) $_REQUEST['userid'];
	$videofilename = '';
	$allowcomments = isset($_REQUEST['allowcomments']) ? 1 : 0;
	$sendemail = isset($_REQUEST['sendemail']) ? 1 : 0;
	$markmature = isset($_REQUEST['markmature']) ? 1 : 0;


	if ($userid == 0)
		GetCatPermission($cat,'addvideo');
	else
	{
		$g_manage = allowedTo('smfgallery_manage');
		$g_gallery = allowedTo('smfgallery_usergallery');
		// Check permissions
		if(!$g_manage && ($user_info['id'] != $userid || !$g_gallery))
			fatal_error($txt['gallery_user_noperm'],false);
	}

	// Check if pictures are auto approved
	$approved = (allowedTo('smfgallery_autoapprove') ? 1 : 0);
	// Allow comments on picture if no setting set.
	if (empty($modSettings['gallery_commentchoice']))
		$allowcomments = 1;

	if (trim($title) == '')
		fatal_error($txt['gallery_error_no_title'],false);
	if (empty($cat))
		fatal_error($txt['gallery_error_no_cat'],false);

	if ($modSettings['gallery_set_enable_multifolder'])
		CreateGalleryFolder();

	if ($userid == 0)
	{
		$result = $smcFunc['db_query']('', "
		SELECT
			f.title, f.is_required, f.ID_CUSTOM
		FROM  {db_prefix}gallery_custom_field as f
		WHERE f.is_required = 1 AND f.ID_CAT = " . $cat);
		while ($row2 = $smcFunc['db_fetch_assoc']($result))
		{
	 		if (!isset($_REQUEST['cus_' . $row2['ID_CUSTOM']]))
	 			fatal_error($txt['gallery_err_req_custom_field'] . $row2['title'], false);
	 		else
	 		{
	 			if ($_REQUEST['cus_' . $row2['ID_CUSTOM']] == '')
	 				fatal_error($txt['gallery_err_req_custom_field'] . $row2['title'], false);
	 		}
	 	}
		$smcFunc['db_free_result']($result);
	}

	// Get category infomation
	if ($userid == 0)
	{
		$dbresult = $smcFunc['db_query']('', "
		SELECT
			ID_BOARD, postingsize, locktopic, showpostlink, tweet_items, id_topic
		FROM {db_prefix}gallery_cat
		WHERE ID_CAT = $cat");
		$rowcat = $smcFunc['db_fetch_assoc']($dbresult);
		$smcFunc['db_free_result']($dbresult);
	}

	$image_resized = 0;
	$testGD = get_extension_funcs('gd');
	$gd2 = in_array('imagecreatetruecolor', $testGD) && function_exists('imagecreatetruecolor');
	unset($testGD);
	$filesize  = 0;

	require_once($sourcedir . '/Subs-Graphics.php');

	//Process Uploaded file
	$is_upload = false;
	if (isset($_FILES['picture']['name']) && $_FILES['picture']['name'] != '')
	{
		$is_upload = true;
		$sizes = @getimagesize($_FILES['picture']['tmp_name']);
		// No size, then it's probably not a valid pic.
		if ($sizes === false)
			fatal_error($txt['gallery_error_invalid_picture'],false);



		if ((!empty($modSettings['gallery_max_width']) && $sizes[0] > $modSettings['gallery_max_width']) || (!empty($modSettings['gallery_max_height']) && $sizes[1] > $modSettings['gallery_max_height']))
		{
			if(!empty($modSettings['gallery_resize_image']))
			{
				// Check to resize image?
				DoImageResize($sizes,$_FILES['picture']['tmp_name']);
				$image_resized = 1;
			}
			else
			{
				// Delete the temp file
				@unlink($_FILES['picture']['tmp_name']);
				fatal_error($txt['gallery_error_img_size_height'] . $sizes[1] . $txt['gallery_error_img_size_width'] . $sizes[0],false);
			}
		}

		// Get the filesize
		if ($image_resized == 1)
			$filesize = filesize($_FILES['picture']['tmp_name']);
		else
			$filesize = $_FILES['picture']['size'];

		if (!empty($modSettings['gallery_max_filesize']) && $filesize > $modSettings['gallery_max_filesize'])
		{
			// Delete the temp file
			@unlink($_FILES['picture']['tmp_name']);
			fatal_error($txt['gallery_error_img_filesize'] . round($modSettings['gallery_max_filesize'] / 1024, 2) . 'kb',false);
		}
	}


		// Filename Member Id + Day + Month + Year + 24 hour, Minute Seconds
	$extension = substr(strrchr($_FILES['picture']['name'], '.'), 1);
	$filename = $user_info['id'] . '_' . date('d_m_y_g_i_s') . '.' . $extension;

	// Check it
	if ($is_upload == false)
	{
		$filesize = filesize($modSettings['gallery_path'] . 'videos/video_128.png');
		$sizes = @getimagesize($modSettings['gallery_path'] . 'videos/video_128.png');
		$extension = substr(strrchr($modSettings['gallery_path'] . 'videos/video_128.png', '.'), 1);
		$filename = $user_info['id'] . '_' . date('d_m_y_g_i_s') . '.' . $extension;
	}

	$extrafolder = '';

		if ($modSettings['gallery_set_enable_multifolder'])
			$extrafolder = $modSettings['gallery_folder_id'] . '/';

		if ($is_upload == true)
			move_uploaded_file($_FILES['picture']['tmp_name'], $modSettings['gallery_path'] . $extrafolder .  $filename);
		else
		{
			copy($modSettings['gallery_path'] . 'videos/video_128.png',$modSettings['gallery_path'] . $extrafolder .  $filename);

			$sizes = @getimagesize($modSettings['gallery_path'] . $extrafolder .  $filename);
		}

		@chmod($modSettings['gallery_path'] . $extrafolder .  $filename, 0644);
		// Create thumbnail
		createThumbnail($modSettings['gallery_path'] . $extrafolder .  $filename, $modSettings['gallery_thumb_width'], $modSettings['gallery_thumb_height']);
		rename($modSettings['gallery_path'] . $extrafolder .  $filename . '_thumb',  $modSettings['gallery_path'] . $extrafolder .  'thumb_' . $filename);

		$thumbname = 'thumb_' . $filename;
		@chmod($modSettings['gallery_path'] . $extrafolder .  'thumb_' . $filename, 0755);


		$mediumimage = '';

		if ($modSettings['gallery_make_medium'])
		{
			createThumbnail($modSettings['gallery_path'] . $extrafolder .  $filename, $modSettings['gallery_medium_width'], $modSettings['gallery_medium_height']);
			rename($modSettings['gallery_path'] . $extrafolder .  $filename . '_thumb',  $modSettings['gallery_path'] . $extrafolder .  'medium_' . $filename);
			$mediumimage = 'medium_' . $filename;
			@chmod($modSettings['gallery_path'] . $extrafolder .  'medium_' . $filename, 0755);
		}


				$mediatype = 0;
				// Do Video File Processing
				if (isset($_FILES['video']['name']) && $_FILES['video']['name'] != '')
				{
					$videofilesize = $_FILES['picture']['size'];
					// Check if valid file extension
					$videoextension = substr(strrchr($_FILES['video']['name'], '.'), 1);
					$videofilename = $user_info['id'] . '_' . date('d_m_y_g_i_s') . '.' . $videoextension;

					// Check if video exceeds file size limits
					if (!empty($modSettings['gallery_video_maxfilesize']) && $videofilesize > $modSettings['gallery_video_maxfilesize'])
						fatal_error($txt['gallery_err_videosize'] . $modSettings['gallery_video_maxfilesize'],false);

					$file_extension = explode(",",$modSettings['gallery_video_filetypes']);
					$extfound = false;
					foreach($file_extension as $fileext)
					{
						if (strtolower($videoextension) == strtolower($fileext))
							$extfound = true;
					}
					// Check if extension found
					if ($extfound == false)
						fatal_error($txt['gallery_err_extension']  . $videoextension,false);
					// Copy the File to the videos folder3
					move_uploaded_file($_FILES['video']['tmp_name'], $modSettings['gallery_path'] . 'videos/' .  $videofilename);
					// Adjust the permissions on the moved file
					@chmod($modSettings['gallery_path'] . 'videos/' .  $videofilename, 0644);

					$mediatype = 1;


					if (class_exists('ffmpeg_movie') && $is_upload == false)
					{

						@ini_set('memory_limit', '256M');
						$ffmpeg = new ffmpeg_movie($modSettings['gallery_path'] . 'videos/' .  $videofilename);
						//echo 'here5 ' . $modSettings['gallery_path'] . 'videos/' .  $videofilename;

                        if (is_object($ffmpeg))
                        {

    						//echo $ffmpeg->getVideoCodec();
    						//print_r($ffmpeg);
    						$frame = $ffmpeg->getFrame(5);
    						///print_r($frame);
    						if ($frame != false)
    						{
    							//echo 'here3';
    							$videoHeight = $frame->getHeight();
    							$videoWidth = $frame->getWidth();


    							$videoImage = $frame->toGDImage();
    							imagejpeg($videoImage,$modSettings['gallery_path'] . $extrafolder .  $filename);
    							//echo $modSettings['gallery_path'] . $extrafolder .  $filename;

    						@chmod($modSettings['gallery_path'] . $extrafolder .  $filename, 0644);

    							// Create thumbnail
    							@unlink($modSettings['gallery_path'] . $extrafolder .  'thumb_' . $filename);
    							createThumbnail($modSettings['gallery_path'] . $extrafolder .  $filename, $modSettings['gallery_thumb_width'], $modSettings['gallery_thumb_height']);
    							rename($modSettings['gallery_path'] . $extrafolder .  $filename . '_thumb',  $modSettings['gallery_path'] . $extrafolder .  'thumb_' . $filename);


    							$thumbname = 'thumb_' . $filename;
    							@chmod($modSettings['gallery_path'] . $extrafolder .  'thumb_' . $filename, 0755);

    							$mediumimage = '';

    							if ($modSettings['gallery_make_medium'])
    							{
    								@unlink($modSettings['gallery_path'] . $extrafolder .  'medium_' . $filename);
    								createThumbnail($modSettings['gallery_path'] . $extrafolder .  $filename, $modSettings['gallery_medium_width'], $modSettings['gallery_medium_height']);
    								rename($modSettings['gallery_path'] . $extrafolder .  $filename . '_thumb',  $modSettings['gallery_path'] . $extrafolder .  'medium_' . $filename);
    								$mediumimage = 'medium_' . $filename;
    								@chmod($modSettings['gallery_path'] . $extrafolder .  'medium_' . $filename, 0755);
    							}

    						}
                        }

					}



				}

                $videofinal = $videofilename;

				// Get the Video File url
				if (isset($_REQUEST['videourl']))
					$videourl = htmlspecialchars($_REQUEST['videourl'],ENT_QUOTES);
				else
					$videourl = '';

				if ($videourl != '')
				{
					//if (substr_count($videourl,'youtube.com') > 0)
					//	$mediatype = 2;

                    if (substr_count($videourl,'video.google.com') > 0)
						$mediatype = 3;
					else
						$mediatype = 5;

					$videofinal = $videourl;
				}
				// End Video File Processing
				// Create the Database entry
				$t = time();
				$gallery_pic_id = 0;
				If ($userid == 0)
				{
					$smcFunc['db_query']('', "INSERT INTO {db_prefix}gallery_pic
							(ID_CAT, filesize,thumbfilename,filename, height, width, keywords, title, description,ID_MEMBER,date,approved,allowcomments,sendemail,type,videofile,mature,mediumfilename)
						VALUES ($cat, $filesize,'" . $extrafolder .  $thumbname . "', '" . $extrafolder . $filename . "', $sizes[1], $sizes[0], '$keywords','$title', '$description',$user_info[id],$t,$approved, $allowcomments,$sendemail,$mediatype,'$videofinal',$markmature,'" . $extrafolder .  $mediumimage . "')");

					$gallery_pic_id = $smcFunc['db_insert_id']('{db_prefix}gallery_pic', 'id_picture');

					// If we are using multifolders get the next folder id
					if ($modSettings['gallery_set_enable_multifolder'])
						ComputeNextFolderID($gallery_pic_id);

					// Check for any custom fields
					$result = $smcFunc['db_query']('', "
					SELECT
						f.title, f.is_required, f.ID_CUSTOM
					FROM  {db_prefix}gallery_custom_field as f
							WHERE f.ID_CAT = " . $cat);
					while ($row2 = $smcFunc['db_fetch_assoc']($result))
					{
						if (isset($_REQUEST['cus_' . $row2['ID_CUSTOM']]))
						{
							$custom_data = htmlspecialchars($_REQUEST['cus_' . $row2['ID_CUSTOM']],ENT_QUOTES);

							$smcFunc['db_query']('', "INSERT INTO {db_prefix}gallery_custom_field_data
							(ID_PICTURE, ID_CUSTOM, value)
							VALUES('$gallery_pic_id', " . $row2['ID_CUSTOM'] . ", '$custom_data')");
						}
					}
					$smcFunc['db_free_result']($result);

				}
				else
				{
					$smcFunc['db_query']('', "INSERT INTO {db_prefix}gallery_pic
								(USER_ID_CAT, filesize,thumbfilename,filename, height, width, keywords, title, description,ID_MEMBER,date,approved,allowcomments,sendemail,type,videofile,mature,mediumfilename)
							VALUES ($cat, $filesize,'" . $extrafolder .  $thumbname . "', '" . $extrafolder .  $filename . "', $sizes[1], $sizes[0], '$keywords','$title', '$description',$user_info[id],$t,$approved, $allowcomments,$sendemail,$mediatype,'$videofinal',$markmature,'" . $extrafolder . $mediumimage . "')");

					$gallery_pic_id = $smcFunc['db_insert_id']('{db_prefix}gallery_pic', 'id_picture');

					// If we are using multifolders get the next folder id
					if ($modSettings['gallery_set_enable_multifolder'])
						ComputeNextFolderID($gallery_pic_id);
				}

				UpdateUserFileSizeTable($user_info['id'],$filesize);

				if ($userid == 0 && $rowcat['ID_BOARD'] != 0 && $approved == 1)
				{
					if (empty($modSettings['gallery_url']))
						$modSettings['gallery_url'] = $boardurl . '/gallery/';

					if($rowcat['postingsize'] == 1)
						$postimg = $filename;
					else
						$postimg = $thumbname;
					// Create the post
					require_once($sourcedir . '/Subs-Post.php');

					if ($rowcat['showpostlink'] == 1)
						$showpostlink = "\n\n" . $scripturl . '?action=gallery;sa=view;id=' . $gallery_pic_id;
					else
						$showpostlink = '';

					$msgOptions = array(
						'id' => 0,
						'subject' => $title,
						'body' => '[b]' . $title . "[/b]\n\n[img height={$sizes[1]} width={$sizes[0]}]" . $modSettings['gallery_url']  . $extrafolder . $postimg . "[/img]$showpostlink\n\n$description",
						'icon' => 'xx',
						'smileys_enabled' => 1,
						'attachments' => array(),
					);
					$topicOptions = array(
						'id' => $rowcat['id_topic'],
						'board' => $rowcat['ID_BOARD'],
						'poll' => null,
						'lock_mode' => $rowcat['locktopic'],
						'sticky_mode' => null,
						'mark_as_read' => true,
					);
					$posterOptions = array(
						'id' => $user_info['id'],
						'update_post_count' => !$user_info['is_guest'] && !isset($_REQUEST['msg']),
					);
					// Fix height & width of posted image in message
					preparsecode($msgOptions['body']);

					createPost($msgOptions, $topicOptions, $posterOptions);

					$smcFunc['db_query']('', "UPDATE {db_prefix}gallery_pic
					SET ID_TOPIC = " .$topicOptions['id'] . " WHERE ID_PICTURE = $gallery_pic_id
					");

				}

				// Last recheck Image if it was resized
				if ($image_resized == 1)
					RecheckResizedImage($modSettings['gallery_path'] . $extrafolder .  $filename,$gallery_pic_id,$filesize,$user_info['id']);
				// Check for Watermark
				if ($modSettings['gallery_set_water_enabled'])
					DoWaterMark($modSettings['gallery_path'] . $extrafolder .  $filename);



                if (function_exists('Gallery_AddRelatedPicture'))
                    Gallery_AddRelatedPicture($gallery_pic_id, $title);


                if (function_exists('Gallery_TweetItem'))
                 if ($rowcat['tweet_items'] == 1 && $approved == 1)
                    Gallery_TweetItem($title,$gallery_pic_id);



 		if ($approved == 1)
 		{


                If($userid == 0)
					UpdateCategoryTotals($cat);
				else
					UpdateUserCategoryTotals($cat);


 			UpdateMemberPictureTotals($user_info['id']);

 			If($userid == 0)
 				Gallery_UpdateLatestCategory($cat);
 			else
 				Gallery_UpdateUserLatestCategory($cat);


 		   SendMemberWatchNotifications($user_info['id'], $scripturl . '?action=gallery;sa=view;id=' .  $gallery_pic_id );
 		}
        else
        {
            $body = $txt['gallery_txt_itemwaitingapproval2'];
            $body = str_replace("%url",$scripturl . '?action=gallery;sa=approvelist',$body);
            $body = str_replace("%title",$title,$body);

            if (function_exists('Gallery_emailAdmins'))
                Gallery_emailAdmins($txt['gallery_txt_itemwaitingapproval'],$body);
        }


			// Update the SMF Shop Points
			if (isset($modSettings['shopVersion']))
 				$smcFunc['db_query']('', "UPDATE {db_prefix}members
				 	SET money = money + " . $modSettings['gallery_shop_picadd'] . "
				 	WHERE ID_MEMBER = {$user_info[id]}
				 	LIMIT 1");


 		if (isset($_REQUEST['copyimage']))
 		{
 			redirectexit('action=gallery;sa=copyimage;id=' . $gallery_pic_id);
 		}
 		else if (isset($_SESSION['last_gallery_url']))
 		{
 			redirectexit($_SESSION['last_gallery_url']);
 		}
 		else
 		{
			// Redirect to the users image page.
			if ($user_info['id'] != 0)
				redirectexit('action=gallery;sa=myimages;u=' . $user_info['id']);
			else
				redirectexit('action=gallery;cat=' . $cat);
 		}

}

function EditVideo()
{
	global $context, $mbname, $txt, $modSettings, $user_info, $smcFunc, $sourcedir, $gallerySettings;

	is_not_guest();
	$id = (int) $_REQUEST['id'];
	if (empty($id))
		fatal_error($txt['gallery_error_no_pic_selected']);

	if ($context['user']['is_guest'])
		$groupid = -1;
	else
		$groupid =  $user_info['groups'][0];
	loadtemplate('Video2');

	// Check if the user owns the picture or is admin
    $dbresult = $smcFunc['db_query']('', "
    SELECT
    	p.ID_PICTURE, p.thumbfilename, p.USER_ID_CAT, p.width, p.height, p.allowcomments,
     p.ID_CAT, p.keywords, p.commenttotal, p.filesize, p.filename, p.approved, p.views, p.title, p.ID_MEMBER,
       m.real_name, p.date, p.description, p.sendemail,p.type,p.videofile, p.mature
       FROM {db_prefix}gallery_pic as p
       LEFT JOIN {db_prefix}members AS m ON (m.ID_MEMBER = p.ID_MEMBER)
       WHERE p.ID_PICTURE = $id  LIMIT 1");
	if ($smcFunc['db_affected_rows']()== 0)
    	fatal_error($txt['gallery_error_no_pictureexist'],false);
    $row = $smcFunc['db_fetch_assoc']($dbresult);
	// Check the category permission
    if (!isset($_REQUEST['u']) && $row['USER_ID_CAT'] == 0)
		GetCatPermission($row['ID_CAT'],'editpic');

	// Gallery picture information
	$context['gallery_pic'] = array(
		'ID_PICTURE' => $row['ID_PICTURE'],
		'ID_MEMBER' => $row['ID_MEMBER'],
		'commenttotal' => $row['commenttotal'],
		'views' => $row['views'],
		'title' => $row['title'],
		'description' => $row['description'],
		'filesize' => round($row['filesize']  / 1024, 2),
		'filename' => $row['filename'],
		'thumbfilename' => $row['thumbfilename'],
		'width' => $row['width'],
		'height' => $row['height'],
		'allowcomments' => $row['allowcomments'],
		'ID_CAT' => $row['ID_CAT'],
		'USER_ID_CAT' => $row['USER_ID_CAT'],
		'date' => timeformat($row['date']),
		'keywords' => $row['keywords'],
		'real_name' => $row['real_name'],
		'sendemail' => $row['sendemail'],
		'type' => $row['type'],
		'videofile'  => $row['videofile'],
		'mature' => $row['mature'],
        'featured' => $row['featured'],
		'allowratings' => $row['allowratings'],
	);
	$smcFunc['db_free_result']($dbresult);
	$context['is_usergallery'] = false;

	if(allowedTo('smfgallery_manage') || (allowedTo('smfgallery_edit') && $user_info['id'] == $context['gallery_pic']['ID_MEMBER']))
	{
		//Get the category information
		if($context['gallery_pic']['USER_ID_CAT'] == 0)
		{
		 	$dbresult = $smcFunc['db_query']('', "
		 	SELECT
		 		c.ID_CAT, c.title, p.view, p.addvideo
		 	FROM {db_prefix}gallery_cat AS c
		 	LEFT JOIN {db_prefix}gallery_catperm AS p ON (p.ID_GROUP = $groupid AND c.ID_CAT = p.ID_CAT)
		 	WHERE c.redirect = 0 ORDER BY c.roworder ASC");
			$context['gallery_cat'] = array();
		 	while($row = $smcFunc['db_fetch_assoc']($dbresult))
			{
				// Check if they have permission to add to this category.
				if ($row['view'] == '0' || $row['addvideo'] == '0' )
					continue;

				$context['gallery_cat'][] = $row;
			}
			$smcFunc['db_free_result']($dbresult);
		}
		else
		{
		 	$dbresult = $smcFunc['db_query']('', "SELECT USER_ID_CAT, title FROM {db_prefix}gallery_usercat WHERE ID_MEMBER = " . $context['gallery_pic']['ID_MEMBER'] . " ORDER BY roworder ASC");
			$context['gallery_cat'] = array();
		 	while($row = $smcFunc['db_fetch_assoc']($dbresult))
			{
				$context['gallery_cat'][] = array(
				'ID_CAT' => $row['USER_ID_CAT'],
				'title' => $row['title'],
				);
			}
			$smcFunc['db_free_result']($dbresult);

			$context['is_usergallery'] = true;
		}
		$context['page_title'] = $mbname . ' - ' . $txt['gallery_text_title'] . ' - ' . $txt['gallery_form_editvideo'];
		$context['sub_template']  = 'editvideo';
		// Check if spellchecking is both enabled and actually working.
		$context['show_spellchecking'] = !empty($modSettings['enableSpellChecking']) && function_exists('pspell_new');

	// Used for the editor
	require_once($sourcedir . '/Subs-Editor.php');

	// Now create the editor.
		$editorOptions = array(
			'id' => 'description',
			'value' =>$context['gallery_pic']['description'],
			'width' => '90%',
			'form' => 'picform',
			'labels' => array(
				'post_button' => ''
			),
		);


		create_control_richedit($editorOptions);
		$context['post_box_name'] = $editorOptions['id'];

	}
	else
		fatal_error($txt['gallery_error_noedit_permission']);

}

function EditVideo2()
{
	global $txt, $modSettings, $sourcedir, $gd2, $smcFunc, $user_info, $gallerySettings;

	is_not_guest();
	$id = (int) $_REQUEST['id'];
	if (empty($id))
		fatal_error($txt['gallery_error_no_pic_selected']);

	require_once($sourcedir . '/Subs-Graphics.php');

	//Check the user permissions
    $dbresult = $smcFunc['db_query']('', "
    SELECT
    	ID_MEMBER, ID_CAT, USER_ID_CAT, thumbfilename, filename, filesize, videofile,
    	ID_TOPIC
    FROM {db_prefix}gallery_pic
    WHERE ID_PICTURE = $id LIMIT 1");

    $row = $smcFunc['db_fetch_assoc']($dbresult);
	$memID = $row['ID_MEMBER'];
	$oldfilesize = $row['filesize'];
	$oldfilename = $row['filename'];
	$oldthumbfilename  = $row['thumbfilename'];
	$oldvideofile = $row['videofile'];
	$USER_ID_CAT = $row['USER_ID_CAT'];
	$ID_TOPIC = $row['ID_TOPIC'];
	//Check the category permission

	if ($row['USER_ID_CAT'] == 0)
		GetCatPermission($row['ID_CAT'],'editpic');
	$smcFunc['db_free_result']($dbresult);
	if (allowedTo('smfgallery_manage') || (allowedTo('smfgallery_edit') && $user_info['id'] == $memID))
	{
		if (!is_writable($modSettings['gallery_path']))
			fatal_error($txt['gallery_write_error'] . $modSettings['gallery_path']);
		$title = htmlspecialchars($_REQUEST['title'],ENT_QUOTES);
		$description = htmlspecialchars($_REQUEST['description'],ENT_QUOTES);
		$keywords = htmlspecialchars($_REQUEST['keywords'],ENT_QUOTES);
		$cat = (int) $_REQUEST['cat'];
		$allowcomments = isset($_REQUEST['allowcomments']) ? 1 : 0;
		$sendemail = isset($_REQUEST['sendemail']) ? 1 : 0;
		$markmature = isset($_REQUEST['markmature']) ? 1 : 0;
		//Check if pictures are auto approved
		$approved = (allowedTo('smfgallery_autoapprove') ? 1 : 0);
        $featured = isset($_REQUEST['featured']) ? 1 : 0;

		// Allow comments on picture if no setting set.
		if(empty($modSettings['gallery_commentchoice']))
			$allowcomments = 1;

		if (trim($title) == '')
			fatal_error($txt['gallery_error_no_title'],false);
		if (empty($cat))
			fatal_error($txt['gallery_error_no_cat'],false);


	$context['is_usergallery'] = false;
	// Check for any required custom fields
	if ($row['USER_ID_CAT'] == 0)
	{
		$result =$smcFunc['db_query']('', "
		SELECT
			f.title, f.is_required, f.ID_CUSTOM
		FROM  {db_prefix}gallery_custom_field as f
		WHERE f.is_required = 1 AND f.ID_CAT = " . $cat);
		while ($row2 = $smcFunc['db_fetch_assoc']($result))
		{
	 		if (!isset($_REQUEST['cus_' . $row2['ID_CUSTOM']]))
	 			fatal_error($txt['gallery_err_req_custom_field'] . $row2['title'], false);
	 		else
	 		{
	 			if ($_REQUEST['cus_' . $row2['ID_CUSTOM']] == '')
	 				fatal_error($txt['gallery_err_req_custom_field'] . $row2['title'], false);
	 		}
	 	}
		$smcFunc['db_free_result']($result);
	}
    else
        $context['is_usergallery'] = true;


	$image_resized = 0;
	$testGD = get_extension_funcs('gd');
	$gd2 = in_array('imagecreatetruecolor', $testGD) && function_exists('imagecreatetruecolor');
	unset($testGD);
	// Process Uploaded file
		if (isset($_FILES['picture']['name']) && $_FILES['picture']['name'] != '')
		{
			$sizes = @getimagesize($_FILES['picture']['tmp_name']);
			// No size, then it's probably not a valid pic.
			if ($sizes === false)
				fatal_error($txt['gallery_error_invalid_picture'],false);


			if ((!empty($modSettings['gallery_max_width']) && $sizes[0] > $modSettings['gallery_max_width']) || (!empty($modSettings['gallery_max_height']) && $sizes[1] > $modSettings['gallery_max_height']))
			{
				if(!empty($modSettings['gallery_resize_image']))
				{
					//Check to resize image?
					DoImageResize($sizes,$_FILES['picture']['tmp_name']);
					$image_resized = 1;
				}
				else
				{
					//Delete the temp file
					fatal_error($txt['gallery_error_img_size_height'] . $sizes[1] . $txt['gallery_error_img_size_width'] . $sizes[0],false);
				}
			}
			//Get the filesize
			if($image_resized == 1)
				$filesize = filesize($_FILES['picture']['tmp_name']);
			else
				$filesize = $_FILES['picture']['size'];

			if(!empty($modSettings['gallery_max_filesize']) && $filesize > $modSettings['gallery_max_filesize'])
			{
				//Delete the temp file
				@unlink($_FILES['picture']['tmp_name']);
				fatal_error($txt['gallery_error_img_filesize'] . round($modSettings['gallery_max_filesize'] / 1024, 2) . 'kb',false);
			}

			// Delete the old files
			@unlink($modSettings['gallery_path'] . $oldfilename );
			@unlink($modSettings['gallery_path'] . $oldthumbfilename);
			$extrafolder = '';

			if ($modSettings['gallery_set_enable_multifolder'])
				$extrafolder = $modSettings['gallery_folder_id'] . '/';

			// Filename Member Id + Day + Month + Year + 24 hour, Minute Seconds
			$extension = substr(strrchr($_FILES['picture']['name'], '.'), 1);
			$filename = $user_info['id'] . '_' . date('d_m_y_g_i_s') . '.' . $extension;
			move_uploaded_file($_FILES['picture']['tmp_name'], $modSettings['gallery_path'] . $extrafolder . $filename);
			@chmod($modSettings['gallery_path'] . $extrafolder . $filename, 0644);
			//Create thumbnail
			createThumbnail($modSettings['gallery_path'] . $extrafolder . $filename,  $modSettings['gallery_thumb_width'],  $modSettings['gallery_thumb_height']);
			rename($modSettings['gallery_path'] . $extrafolder . $filename . '_thumb',  $modSettings['gallery_path'] . $extrafolder . 'thumb_' . $filename);
			$thumbname = 'thumb_' . $filename;
			@chmod($modSettings['gallery_path'] . $extrafolder .  'thumb_' . $filename, 0755);

			$mediumimage = '';

			if ($modSettings['gallery_make_medium'])
			{
				createThumbnail($modSettings['gallery_path'] . $extrafolder .  $filename, $modSettings['gallery_medium_width'], $modSettings['gallery_medium_height']);
				rename($modSettings['gallery_path'] . $extrafolder .  $filename . '_thumb',  $modSettings['gallery_path'] . $extrafolder .  'medium_' . $filename);
				$mediumimage = 'medium_' . $filename;
				@chmod($modSettings['gallery_path'] . $extrafolder .  'medium_' . $filename, 0755);
			}


			// Update the Database entry
			$t = time();

			$smcFunc['db_query']('', "UPDATE {db_prefix}gallery_pic
					SET ID_CAT = $cat, filesize = $filesize, filename = '" . $extrafolder . $filename . "',  thumbfilename = '" . $extrafolder . $thumbname . "', height = $sizes[1], width = $sizes[0], approved = $approved, date =  $t, title = '$title', description = '$description', keywords = '$keywords', allowcomments = $allowcomments, sendemail = $sendemail, mature = $markmature, mediumfilename = '" . $extrafolder . $mediumimage . "' WHERE ID_PICTURE = $id LIMIT 1");

					UpdateUserFileSizeTable($memID,$oldfilesize * -1);
					UpdateUserFileSizeTable($memID,$filesize);
					// Last recheck Image if it was resized
					if ($image_resized == 1)
						RecheckResizedImage($modSettings['gallery_path'] . $extrafolder . $filename,$id,$filesize,$memID);
					// Check for Watermark
					if ($modSettings['gallery_set_water_enabled'])
						DoWaterMark($modSettings['gallery_path'] . $extrafolder . $filename);
					UpdateCategoryTotalByPictureID($id);
			}
					// Change the picture owner if selected
					if (allowedTo('smfgallery_manage') && isset($_REQUEST['pic_postername']))
					{
						$pic_postername = str_replace('"','', $_REQUEST['pic_postername']);
						$pic_postername = str_replace("'",'', $pic_postername);
						$pic_postername = str_replace('\\','', $pic_postername);
						$pic_postername = htmlspecialchars($pic_postername, ENT_QUOTES);
						$memid = 0;
						$dbresult = $smcFunc['db_query']('', "
						SELECT
							real_name, ID_MEMBER
						FROM {db_prefix}members
						WHERE real_name = '$pic_postername' OR member_name = '$pic_postername'  LIMIT 1");
						$row = $smcFunc['db_fetch_assoc']($dbresult);
						$smcFunc['db_free_result']($dbresult);

						if ($smcFunc['db_affected_rows']() != 0)
						{
							// Member found update the picture owner
							$memid = $row['ID_MEMBER'];
							$smcFunc['db_query']('', "UPDATE {db_prefix}gallery_pic
							SET ID_MEMBER = $memid WHERE ID_PICTURE = $id LIMIT 1");
						}
					}

				$mediatype = 0;
				// Do Video File Processing
				if (isset($_FILES['video']['name']) && $_FILES['video']['name'] != '')
				{
					$videofilesize = $_FILES['picture']['size'];
					// Check if valid file extension
					$videoextension = substr(strrchr($_FILES['video']['name'], '.'), 1);
					$videofilename = $user_info['id'] . '_' . date('d_m_y_g_i_s') . '.' . $videoextension;
					// Check if video exceeds file size limits
					if (!empty($modSettings['gallery_video_maxfilesize']) && $videofilesize > $modSettings['gallery_video_maxfilesize'])
						fatal_error($txt['gallery_err_videosize'] . $modSettings['gallery_video_maxfilesize'],false);

					$file_extension = explode(",",$modSettings['gallery_video_filetypes']);
					$extfound = false;
					foreach($file_extension as $fileext)
					{
						if (strtolower($videoextension) == strtolower($fileext))
							$extfound = true;
					}
					// Check if extension found
					if ($extfound == false)
						fatal_error($txt['gallery_err_extension']  . $videoextension,false);

					// Delete the old videofile
					@unlink($modSettings['gallery_path'] . 'videos/' . $oldvideofile );
					// Copy the File to the videos folder
					move_uploaded_file($_FILES['video']['tmp_name'], $modSettings['gallery_path'] . 'videos/' .  $videofilename);
					// Adjust the permissions on the moved file
					@chmod($modSettings['gallery_path'] . 'videos/' .  $videofilename, 0644);
							$mediatype = 1;
					$smcFunc['db_query']('', "UPDATE {db_prefix}gallery_pic
							SET type =$mediatype, videofile = '$videofilename' WHERE ID_PICTURE = $id LIMIT 1");



					if (class_exists('ffmpeg_movie'))
					{

						@ini_set('memory_limit', '300M');
						$ffmpeg = new ffmpeg_movie($modSettings['gallery_path'] . 'videos/' .  $videofilename);
						//echo 'here5 ' . $modSettings['gallery_path'] . 'videos/' .  $videofilename;

						$extension = 'jpg';
						$filename = $user_info['id'] . '_' . date('d_m_y_g_i_s') . '.' . $extension;

						if ($modSettings['gallery_set_enable_multifolder'])
							CreateGalleryFolder();

						$extrafolder = '';

						if ($modSettings['gallery_set_enable_multifolder'])
							$extrafolder = $modSettings['gallery_folder_id'] . '/';

						if (is_object($ffmpeg))
                        {
    						//echo $ffmpeg->getVideoCodec();
    						//print_r($ffmpeg);
    						$frame = $ffmpeg->getFrame(5);
    						///print_r($frame);
    						if ($frame != false)
    						{
    							//echo 'here3';
    							$videoHeight = $frame->getHeight();
    							$videoWidth = $frame->getWidth();


    							$videoImage = $frame->toGDImage();
    							imagejpeg($videoImage,$modSettings['gallery_path'] . $extrafolder .  $filename);
    							//echo $modSettings['gallery_path'] . $extrafolder .  $filename;

    						@chmod($modSettings['gallery_path'] . $extrafolder .  $filename, 0644);

    							// Create thumbnail
    							@unlink($modSettings['gallery_path'] . $extrafolder .  'thumb_' . $filename);
    							createThumbnail($modSettings['gallery_path'] . $extrafolder .  $filename, $modSettings['gallery_thumb_width'], $modSettings['gallery_thumb_height']);
    							rename($modSettings['gallery_path'] . $extrafolder .  $filename . '_thumb',  $modSettings['gallery_path'] . $extrafolder .  'thumb_' . $filename);


    							$thumbname = 'thumb_' . $filename;
    							@chmod($modSettings['gallery_path'] . $extrafolder .  'thumb_' . $filename, 0755);

    							$mediumimage = '';

    							if ($modSettings['gallery_make_medium'])
    							{
    								@unlink($modSettings['gallery_path'] . $extrafolder .  'medium_' . $filename);
    								createThumbnail($modSettings['gallery_path'] . $extrafolder .  $filename, $modSettings['gallery_medium_width'], $modSettings['gallery_medium_height']);
    								rename($modSettings['gallery_path'] . $extrafolder .  $filename . '_thumb',  $modSettings['gallery_path'] . $extrafolder .  'medium_' . $filename);
    								$mediumimage = 'medium_' . $filename;
    								@chmod($modSettings['gallery_path'] . $extrafolder .  'medium_' . $filename, 0755);
    							}


    							$smcFunc['db_query']('', "UPDATE {db_prefix}gallery_pic
    							SET filename =  '" . $extrafolder . "$filename', thumbfilename = '" . $extrafolder . "$thumbname', mediumfilename = '" . $extrafolder . "$mediumimage' WHERE ID_PICTURE = $id LIMIT 1");

    						}
						} // is object


					}

				}

				// Get the Video File url
				if (isset($_REQUEST['videourl']))
					$videourl = htmlspecialchars($_REQUEST['videourl'],ENT_QUOTES);
				else
					$videourl = '';

				if ($videourl != '')
				{
					//if (substr_count($videourl,'youtube.com') > 0)
					//	$mediatype = 2;

					if (substr_count($videourl,'video.google.com') > 0)
						$mediatype = 3;
					else
						$mediatype = 5;


					$smcFunc['db_query']('', "UPDATE {db_prefix}gallery_pic SET type =$mediatype, videofile = '$videourl' WHERE ID_PICTURE = $id LIMIT 1");
				}
				// End Video File Processing

				if ($ID_TOPIC != 0 && $USER_ID_CAT ==  0)
					{
						UpdateMessagePost($ID_TOPIC, $id);

				}

				if ($USER_ID_CAT == 0)
				$smcFunc['db_query']('', "UPDATE {db_prefix}gallery_pic
					SET ID_CAT = $cat, approved = $approved, title = '$title', description = '$description', keywords = '$keywords', allowcomments = $allowcomments, sendemail = $sendemail, mature = $markmature WHERE ID_PICTURE = $id LIMIT 1");
				else
				$smcFunc['db_query']('', "UPDATE {db_prefix}gallery_pic
									SET USER_ID_CAT = $cat, approved = $approved, title = '$title', description = '$description', keywords = '$keywords', allowcomments = $allowcomments, sendemail = $sendemail, mature = $markmature WHERE ID_PICTURE = $id LIMIT 1");

				// Redirect to the users image page.
				redirectexit('action=gallery;sa=myimages;u=' . $user_info['id']);
	}
	else
		fatal_error($txt['gallery_error_noedit_permission']);
}

function VideoSettings()
{
	global $context, $mbname, $txt;
	isAllowedTo('smfgallery_manage');
	loadtemplate('Video2');
	DoGalleryAdminTabs('adminset');
	$context['page_title'] = $mbname . ' - ' . $txt['gallery_text_title'] . ' - ' . $txt['gallery_text_videosettings'];
	$context['sub_template']  = 'video_settings';
}

function VideoSettings2()
{
	isAllowedTo('smfgallery_manage');

	$gallery_video_maxfilesize = (int) $_REQUEST['gallery_video_maxfilesize'];
	$gallery_video_allowlinked = isset($_REQUEST['gallery_video_allowlinked']) ? 1 : 0;
	$gallery_video_playerheight = (int) $_REQUEST['gallery_video_playerheight'];
	$gallery_video_playerwidth = (int) $_REQUEST['gallery_video_playerwidth'];
	$gallery_video_filetypes =  htmlspecialchars($_REQUEST['gallery_video_filetypes']);
	$gallery_video_showbbclinks = isset($_REQUEST['gallery_video_showbbclinks']) ? 1 : 0;
	$gallery_video_showdowloadlink = isset($_REQUEST['gallery_video_showdowloadlink']) ? 1 : 0;
    $mediapro_default_height = (int) $_REQUEST['mediapro_default_height'];
    $mediapro_default_width = (int) $_REQUEST['mediapro_default_width'];
	// Save the setting information
	updateSettings(
	array('gallery_video_maxfilesize' => $gallery_video_maxfilesize,
	'gallery_video_allowlinked' => $gallery_video_allowlinked ,
	'gallery_video_playerheight' => $gallery_video_playerheight,
	'gallery_video_playerwidth' => $gallery_video_playerwidth,
	'gallery_video_filetypes' => $gallery_video_filetypes,
	'gallery_video_showbbclinks' => $gallery_video_showbbclinks,
	'gallery_video_showdowloadlink' => $gallery_video_showdowloadlink,
    'mediapro_default_height' => $mediapro_default_height,
    'mediapro_default_width' => $mediapro_default_width,
	));
	redirectexit('action=admin;area=gallery;sa=videoset');
}

function showvideobox($filename)
{
	global $modSettings;

	// Check if the file starts with http:// which means it is a linked video
	if (substr($filename,0,7) == 'http://')
	{
	 	$result =  VideoProProcess('<a href="' . $filename . '">' . $filename . '</a>');
		if ('<a href="' . $filename . '">' . $filename . '</a>' != $result)
			echo $result;
		else
		{
			 if (substr_count($filename,'.flv')  > 0)
			{


			echo ' <object class="playerpreview" type="application/x-shockwave-flash" data="' . $modSettings['gallery_url'] . 'videos/player_flv_maxi.swf" width="',$modSettings['gallery_video_playerwidth'],'" height="',$modSettings['gallery_video_playerheight'],'">
		                <param name="movie" value="' . $modSettings['gallery_url'] . 'videos/player_flv_maxi.swf" />
		                <param name="allowFullScreen" value="true" />

		                <param name="FlashVars" value="flv='  .  $filename . '&amp;width=',$modSettings['gallery_video_playerwidth'],'&amp;height=',$modSettings['gallery_video_playerheight'],'&amp;showfullscreen=1;showstop=1&amp;showvolume=1&amp;showtime=1&amp;bgcolor1=000000&amp;bgcolor2=000000&amp;playercolor=000000" />
		                </object>';

			}
			else
			{
				echo '<video src="' .   $filename . '" width="',$modSettings['gallery_video_playerwidth'],'" height="',$modSettings['gallery_video_playerheight'],'" controls>
          <object data="' .     $filename . '" width="',$modSettings['gallery_video_playerwidth'],'" height="',$modSettings['gallery_video_playerheight'],'">
            <embed src="'  .  $filename . '" width="',$modSettings['gallery_video_playerwidth'],'" height="',$modSettings['gallery_video_playerheight'],'">
          </object>
</video> ';
			}

		}
	}
	else
	{
		// Not a linked video
		// Get the extension then determine which box type to show
		$extension = substr(strrchr($filename, '.'), 1);

		switch ($extension)
		{
			case 'flv':
			echo ' <object class="playerpreview" type="application/x-shockwave-flash" data="' . $modSettings['gallery_url'] . 'videos/player_flv_maxi.swf" width="',$modSettings['gallery_video_playerwidth'],'" height="',$modSettings['gallery_video_playerheight'],'">
                <param name="movie" value="' . $modSettings['gallery_url'] . 'videos/player_flv_maxi.swf" />
                <param name="allowFullScreen" value="true" />
                <param name="FlashVars" value="flv=' . $modSettings['gallery_url'] . 'videos/' .  $filename . '&amp;width=',$modSettings['gallery_video_playerwidth'],'&amp;height=',$modSettings['gallery_video_playerheight'],'&amp;showfullscreen=1;showstop=1&amp;showvolume=1&amp;showtime=1&amp;bgcolor1=000000&amp;bgcolor2=000000&amp;playercolor=000000" />
                </object>
                ';
			break;

			case 'mp4':
				echo '
                <video width="',$modSettings['gallery_video_playerwidth'],'" height="',$modSettings['gallery_video_playerheight'],'" controls>
  <source src="' . $modSettings['gallery_url'] .  'videos/' .  $filename . '" type="video/mp4">
  <object data="' . $modSettings['gallery_url'] .  'videos/' .  $filename . '" width="',$modSettings['gallery_video_playerwidth'],'" height="',$modSettings['gallery_video_playerheight'],'">
    <PARAM NAME="src" VALUE="' . $modSettings['gallery_url'] .  'videos/' .  $filename . '">
    <PARAM NAME="AutoPlay" VALUE="true" >
    <PARAM NAME="Controller" VALUE="false" >
    <embed src="' . $modSettings['gallery_url'] .  'videos/' .  $filename . '" width="',$modSettings['gallery_video_playerwidth'],'" height="',$modSettings['gallery_video_playerheight'],'">
  </object>
</video> ';
			break;


            case 'mp3':

            echo '<audio controls height="100" width="100">
  <source src="' . $modSettings['gallery_url'] .  'videos/' .  $filename . '" type="audio/mpeg">
  <embed height="50" width="100" src="' . $modSettings['gallery_url'] .  'videos/' .  $filename . '">
</audio>';

            break;


             case 'wav':

            echo '
  <embed height="50" width="100" src="' . $modSettings['gallery_url'] .  'videos/' .  $filename . '">
';

            break;

             case 'mid':

            echo '
  <embed height="50" width="100" src="' . $modSettings['gallery_url'] .  'videos/' .  $filename . '">
';

            break;

            /*
			case 'mov':
				echo '<OBJECT CLASSID="clsid:02BF25D5-8C17-4B23-BC80-D3488ABDDC6B"

CODEBASE="http://www.apple.com/qtactivex/qtplugin.cab" HEIGHT="640" WIDTH="',$modSettings['gallery_video_playerwidth'],'">
<PARAM NAME="src" VALUE="' . $modSettings['gallery_url'] .  'videos/' .  $filename . '" >
<PARAM NAME="AutoPlay" VALUE="true" >
<PARAM NAME="Controller" VALUE="false" >
<EMBED SRC="' . $modSettings['gallery_url'] .  'videos/' .  $filename . '" HEIGHT="',$modSettings['gallery_video_playerheight'],'" WIDTH="',$modSettings['gallery_video_playerwidth'],'" TYPE="video/quicktime" PLUGINSPAGE="http://www.apple.com/quicktime/download/" AUTOPLAY="true" CONTROLLER="false" />
</OBJECT>';
			break;
			case 'rpm':

				echo '<OBJECT ID=RVOCX CLASSID="clsid:CFCDAA03-8BE4-11cf-B84B-0020AFBBCCFA"
  WIDTH="',$modSettings['gallery_video_playerwidth'],'" HEIGHT="',$modSettings['gallery_video_playerheight'],'">
<PARAM NAME="SRC" VALUE="' . $modSettings['gallery_url'] .  'videos/' .  $filename . '">
<PARAM NAME="CONTROLS" VALUE="ImageWindow">
<PARAM NAME="CONSOLE" VALUE="one">
<EMBED SRC="' . $modSettings['gallery_url'] .  'videos/' .  $filename . '" WIDTH="',$modSettings['gallery_video_playerwidth'],'" HEIGHT="',$modSettings['gallery_video_playerheight'],'" NOJAVA=true
   CONSOLE=one AUTOSTART=true CONTROLS=ControlPanel>
</OBJECT>
<EMBED SRC="' . $modSettings['gallery_url'] .  'videos/' .  $filename . '" WIDTH="',$modSettings['gallery_video_playerwidth'],'" HEIGHT=40 NOJAVA=true CONTROLS=ControlPanel CONSOLE=one>
';

			break;

			case 'rm':

				echo '<OBJECT ID=RVOCX CLASSID="clsid:CFCDAA03-8BE4-11cf-B84B-0020AFBBCCFA"
  WIDTH="',$modSettings['gallery_video_playerwidth'],'" HEIGHT="',$modSettings['gallery_video_playerheight'],'">
<PARAM NAME="SRC" VALUE="' . $modSettings['gallery_url'] .  'videos/' .  $filename . '">
<PARAM NAME="CONTROLS" VALUE="ImageWindow">
<PARAM NAME="CONSOLE" VALUE="one">
<EMBED SRC="' . $modSettings['gallery_url'] .  'videos/' .  $filename . '" WIDTH="',$modSettings['gallery_video_playerwidth'],'" HEIGHT="',$modSettings['gallery_video_playerheight'],'" NOJAVA=true
   CONSOLE=one AUTOSTART=true CONTROLS=ControlPanel>
</OBJECT>
<EMBED SRC="'  . $modSettings['gallery_url'] .  'videos/' .  $filename . '" WIDTH="',$modSettings['gallery_video_playerwidth'],'" HEIGHT=40 NOJAVA=true CONTROLS=ControlPanel CONSOLE=one>
';
				break;
			case 'ra':

				echo '<OBJECT ID=RVOCX CLASSID="clsid:CFCDAA03-8BE4-11cf-B84B-0020AFBBCCFA"
  WIDTH="',$modSettings['gallery_video_playerwidth'],'" HEIGHT="',$modSettings['gallery_video_playerheight'],'">
<PARAM NAME="SRC" VALUE="' . $modSettings['gallery_url'] .  'videos/' .  $filename . '">
<PARAM NAME="CONTROLS" VALUE="ImageWindow">
<PARAM NAME="CONSOLE" VALUE="one">
<EMBED SRC="' . $modSettings['gallery_url'] .  'videos/' .  $filename . '" WIDTH="',$modSettings['gallery_video_playerwidth'],'" HEIGHT="',$modSettings['gallery_video_playerheight'],'" NOJAVA=true
   CONSOLE=one AUTOSTART=true CONTROLS=ControlPanel>
</OBJECT>
<EMBED SRC="' . $modSettings['gallery_url'] .  'videos/' .  $filename . '" WIDTH="',$modSettings['gallery_video_playerwidth'],'" HEIGHT=40 NOJAVA=true CONTROLS=ControlPanel CONSOLE=one>
';

			break;

			case 'ram':

				echo '<OBJECT ID=RVOCX CLASSID="clsid:CFCDAA03-8BE4-11cf-B84B-0020AFBBCCFA"
  WIDTH="',$modSettings['gallery_video_playerwidth'],'" HEIGHT="',$modSettings['gallery_video_playerheight'],'">
<PARAM NAME="SRC" VALUE="' . $modSettings['gallery_url'] .  'videos/' .  $filename . '">
<PARAM NAME="CONTROLS" VALUE="ImageWindow">
<PARAM NAME="CONSOLE" VALUE="one">
<EMBED SRC="' . $modSettings['gallery_url'] .  'videos/' .  $filename . '" WIDTH="',$modSettings['gallery_video_playerwidth'],'" HEIGHT="',$modSettings['gallery_video_playerheight'],'" NOJAVA=true
   CONSOLE=one AUTOSTART=true CONTROLS=ControlPanel>
</OBJECT>
<EMBED SRC="' . $modSettings['gallery_url'] .  'videos/' .  $filename . '" WIDTH="',$modSettings['gallery_video_playerwidth'],'" HEIGHT=40 NOJAVA=true CONTROLS=ControlPanel CONSOLE=one>
';

			break;

            */
        case 'webm':
            echo '<video width="',$modSettings['gallery_video_playerwidth'],'" height="',$modSettings['gallery_video_playerheight'],'" controls>
                      <source src="' . $modSettings['gallery_url'] .  'videos/' .  $filename . '" type="video/webm">
                      <object data="' . $modSettings['gallery_url'] .  'videos/' .  $filename . '" width="',$modSettings['gallery_video_playerwidth'],'" height="',$modSettings['gallery_video_playerheight'],'">
                        <embed src="' . $modSettings['gallery_url'] .  'videos/' .  $filename . '" width="',$modSettings['gallery_video_playerwidth'],'" height="',$modSettings['gallery_video_playerheight'],'">
                      </object>
            </video>
     ';
            break;

        case 'ogg':
            echo '<video  width="',$modSettings['gallery_video_playerwidth'],'" height="',$modSettings['gallery_video_playerheight'],'" controls>
                      <source src="' . $modSettings['gallery_url'] .  'videos/' .  $filename . '" type="video/ogg">
                      <object data="' . $modSettings['gallery_url'] .  'videos/' .  $filename . '" width="',$modSettings['gallery_video_playerwidth'],'" height="',$modSettings['gallery_video_playerheight'],'">
                        <embed src="' . $modSettings['gallery_url'] .  'videos/' .  $filename . '" width="',$modSettings['gallery_video_playerwidth'],'" height="',$modSettings['gallery_video_playerheight'],'">
                      </object>
            </video>
     ';
            break;

			case 'swf':
			// Flash Videos

			echo '
			<object width="',$modSettings['gallery_video_playerwidth'],'" height="',$modSettings['gallery_video_playerheight'],'"
			  classid="clsid:d27cdb6e-ae6d-11cf-96b8-444553540000"
			  codebase="http://fpdownload.macromedia.com/pub/
			  shockwave/cabs/flash/swflash.cab#version=8,0,0,0">
			  <param name="movie" value="' . $modSettings['gallery_url'] .  'videos/' .  $filename . '" />
			  <embed src="' . $modSettings['gallery_url'] .  'videos/' .  $filename . '" width="',$modSettings['gallery_video_playerwidth'],'" height="',$modSettings['gallery_video_playerheight'],'"
			  type="application/x-shockwave-flash" pluginspage=
			  "http://www.macromedia.com/go/getflashplayer" />
</object>';

			break;
			default:

		echo '
                <object data="' . $modSettings['gallery_url'] .  'videos/' .  $filename . '" width="',$modSettings['gallery_video_playerwidth'],'" height="',$modSettings['gallery_video_playerheight'],'">
            <embed src="' . $modSettings['gallery_url'] .  'videos/' .  $filename . '" width="',$modSettings['gallery_video_playerwidth'],'" height="',$modSettings['gallery_video_playerheight'],'">
          </object>

     ';


			break;
		}


	}


}

function VideoProProcess($message)
{
	global $smcFunc, $modSettings;

	// If it is short don't do anything
	if (strlen($message) < 7)
		return $message;

	$mediaProItems = array();


	// Get list of sites that are enabled
	$result = $smcFunc['db_query']('', "
	SELECT
		id, title, website, regexmatch,
		embedcode, height,  width
	FROM {db_prefix}mediapro_sites
	");
	while ($row = $smcFunc['db_fetch_assoc']($result))
	{
		$mediaProItems[] = $row;
	}

	// Loop though main array of enabled sites to process
	if (count($mediaProItems) > 0)
	foreach($mediaProItems as $mediaSite)
	{

  		if (!empty($modSettings['mediapro_default_width']))
			$movie_width = $modSettings['mediapro_default_width'];
		else
			$movie_width  = $mediaSite['width'];

		if (!empty($modSettings['mediapro_default_height']))
			$movie_height = $modSettings['mediapro_default_height'];
		else
			$movie_height = $mediaSite['height'];



			$mediaSite['embedcode'] = str_replace('width="480"','width="' . $movie_width  .'"', $mediaSite['embedcode']);
			$mediaSite['embedcode'] = str_replace('width:480','width="' . $movie_width  .'px', $mediaSite['embedcode']);
			$mediaSite['embedcode'] = str_replace('width=480','width=' . $movie_width , $mediaSite['embedcode']);



			 $mediaSite['embedcode'] = str_replace('height="600"','height="' . $movie_height .'"', $mediaSite['embedcode']);
			 $mediaSite['embedcode'] = str_replace('height:600','height:' . $movie_height.'px', $mediaSite['embedcode']);
			 $mediaSite['embedcode'] = str_replace('height=600','height=' . $movie_height, $mediaSite['embedcode']);


		$message = preg_replace('#<a href="' . $mediaSite['regexmatch'] . '"(.*?)</a>#i', $mediaSite['embedcode'], $message);
	}

	// Return the updated message content
	return $message;
}

?>