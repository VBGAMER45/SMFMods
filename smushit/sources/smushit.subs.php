<?php

/**
 * Smush.it for SMF
 *
 * @author spuds http://addons.elkarte.net/2015/05/Smushit/
 * @license MPL 1.1 http://mozilla.org/MPL/1.1/
 * Ported to SMF by vbgamer45 http://www.smfhacks.com
 *
 */
ini_set("display_errors",1);
if (!defined('SMF'))
	die('Hacking attempt...');	
/**
 * Batch processing of attachments from the attachment file maintenance section
 *
 * - runs as a paused loop to prevent overload
 * - can be slow ;)
 */
function smushitAttachments()
{
	global $txt, $context, $modSettings;

	// Going to need these to communicate
	loadLanguage('smushit');
	loadTemplate('smushit');

	// Make sure the session is valid
	checkSession('get');

	// You have to be able to admin the forum to do this.
	isAllowedTo('admin_forum');

	// Batch size -- how many attachments to process per loop
	$chunk_size = 5;

	// On first entry we need to set some parameters
	if (empty($_GET['step']))
	{
		$context['smushit_results'] = array();
		$_GET['step'] = 0;

		// Find out how many images we are going to process
		$images = smushit_getNumFiles(false);
		$_SESSION['smushit_images'] = $images;

		// Save the form post values for future loops
		$_SESSION['smushitage'] = (time() - 24 * 60 * 60 * (int) $_POST['smushitage']);
		$_SESSION['smushitsize'] = (!empty($modSettings['smushit_attachments_size'])
			? 1024 * $modSettings['smushit_attachments_size'] : 0);
	}

	// Set up this pass through the loop so we know which data chunk to work on
	$images = (isset($_SESSION['smushit_images']))
		? $_SESSION['smushit_images'] : 0;
	if (isset($_SESSION['smushit_results']))
		$context['smushit_results'] = $_SESSION['smushit_results'];

	// Get the next group of attachments that meet our criteria
	$files = smushit_getFiles((int) $_GET['step'], $chunk_size, '', '', $_SESSION['smushitsize'], $_SESSION['smushitage']);

	// While we have attachments that have not been smushed yet the we .... smush.em
	foreach ($files as $row)
	{
		if (empty($row['smushit']))
			smushitMain($row);
	}

	// Update the pointer and see if we have more to do ....
	$_GET['step'] += $chunk_size;
	if ($_GET['step'] < $images)
		pauseAttachmentSmushit($images);

	// Got here we must be doing well, well as in we did something, first lets clean up
	unset($_GET['step'], $_SESSION['smushit_results'], $_SESSION['smushit_images'], $_SESSION['smushitage'], $_SESSION['smushitsize']);

	// Do a final exit to the sub template to show what we did
	$context['page_title'] = $txt['smushit_attachments'];
	$context[$context['admin_menu_name']]['current_subsection'] = 'maintenance';
	$context['sub_template'] = 'attachment_smushit';
	$context['completed'] = true;
}

/**
 * Sets up for the next loop
 *
 * @param int $max_steps
 */
function pauseAttachmentSmushit($max_steps = 0)
{
	global $context, $txt, $time_start;

	// Try get more time...
	@set_time_limit(600);
	if (function_exists('apache_reset_timeout'))
		@apache_reset_timeout();

	// Have we already used our maximum time, don't want to just run forever.
	if (array_sum(explode(' ', microtime())) - array_sum(explode(' ', $time_start)) > 30)
	{
		$context['smushit_results'][9999999] = '|' . $txt['smushit_attachments_timeout'] . ' ' . array_sum(explode(' ', microtime())) - array_sum(explode(' ', $time_start));

		return;
	}

	// Set the context vars for display via the admin template 'not_done'
	$context['continue_get_data'] = '?action=admin;area=manageattachments;sa=smushit;step=' . $_GET['step'] . ';' . $context['session_var'] . '=' . $context['session_id'];
	$context['page_title'] = $txt['not_done_title'];
	$context['continue_post_data'] = '';
	$context['continue_countdown'] = 3;
	$context['sub_template'] = 'not_done';
	$context[$context['admin_menu_name']]['current_subsection'] = 'maintenance';
	$context['continue_percent'] = round(((int) $_GET['step'] / $max_steps) * 100);
	$context['continue_percent'] = min($context['continue_percent'], 100);

	// Save for the next loop of love
	$_SESSION['smushit_results'] = $context['smushit_results'];

	obExit();
}

/**
 * Show a list of attachment files available for smush.it
 *
 * - called by ?action=admin;area=manageattachments;sa=smushit
 * - uses the 'browse' sub template
 * - allows sorting by name, date, size and smush.it.
 * - paginates results.
 */
function SmushitBrowse()
{
	global $context, $txt, $scripturl, $modSettings, $sourcedir;

	loadLanguage('smushit');


	$context['sub_template'] = 'browse';
	
	$context['browse_type'] = 'smushit';

	// Set the options for the list.
	$listOptions = array(
		'id' => 'file_list',
		'title' => $txt['smushit_attachment_check'],
		'items_per_page' => $modSettings['defaultMaxMessages'],
		'base_href' => $scripturl . '?action=admin;area=manageattachments;sa=smushitbrowse',
		'default_sort_col' => 'filesize',
		'no_items_label' => $txt['smushit_attachment_empty'],
		'get_items' => array(
			'function' => 'smushit_getFiles',
		),
		'get_count' => array(
			'function' => 'smushit_getNumFiles',
		),
		'columns' => array(
			'name' => array(
				'header' => array(
					'value' => $txt['attachment_name'],
				),
				'data' => array(
					'function' => function($rowData) use ($modSettings, $context, $scripturl)
					{
						$link = '<a href="';
						$link .= sprintf('%1$s?action=dlattach;topic=%2$d.0&id=%3$d', $scripturl, $rowData['id_topic'], $rowData['id_attach']);
						$link .= '"';

						// Show a popup on click if it's a picture and we know its dimensions.
						if (!empty($rowData['width']) && !empty($rowData['height']))
							$link .= sprintf(' onclick="return reqWin(this.href' .  ' + \';image\\' . ', %1$d, %2$d, true);"', $rowData['width'] + 20, $rowData['height'] + 20);

						$link .= sprintf('>%1$s</a>', preg_replace('~&amp;#(\d{1,7}|x[0-9a-fA-F]{1,6});~', '&#\1;', htmlspecialchars($rowData['filename'])));

						// Show the dimensions.
						if (!empty($rowData['width']) && !empty($rowData['height']))
							$link .= sprintf(' <span class="smalltext">%1$dx%2$d</span>', $rowData['width'], $rowData['height']);

						return $link;
					}
				),
				'sort' => array(
					'default' => 'a.filename',
					'reverse' => 'a.filename DESC',
				),
			),
			'filesize' => array(
				'header' => array(
					'value' => $txt['attachment_file_size'],
				),
				'data' => array(
					'function' => function ($rowData) use ($txt)
					{
						return sprintf('%1$s%2$s', round($rowData['size'] / 1024, 2), $txt['kilobyte']);
					}
				),
				'sort' => array(
					'default' => 'a.size DESC',
					'reverse' => 'a.size',
				),
			),
			'smushed' => array(
				'header' => array(
					'value' => $txt['smushited'],
				),
				'data' => array(
					'function' => function ($rowData) use ($txt)
					{
						return (($rowData['smushit'] == 0) ? $txt['no'] : $txt['yes']);
					}
				),
				'sort' => array(
					'default' => 'a.smushit DESC',
					'reverse' => 'a.smushit',
				),
			),
			'post' => array(
				'header' => array(
					'value' => $txt['subject'],
				),
				'data' => array(
					'function' => function($rowData) use ($txt, $scripturl)
					{
						return sprintf('%1$s <a href="%2$s?topic=%3$d.0.msg%4$d#msg%4$d">%5$s</a>', $txt['in'], $scripturl, $rowData['id_topic'], $rowData['id_msg'], $rowData['subject']);
					}
				),
				'sort' => array(
					'default' => 'm.subject',
					'reverse' => 'm.subject DESC',
				),
			),
			'date' => array(
				'header' => array(
					'value' => $txt['date'],
				),
				'data' => array(
					'function' => function($rowData) use ($txt, $context, $scripturl)
					{
						// The date the message containing the attachment was posted
						$date = empty($rowData['poster_time']) ? $txt['never'] : timeformat($rowData['poster_time']);
						return $date;
					}
				),
				'sort' => array(
					'default' => 'm.poster_time',
					'reverse' => 'm.poster_time DESC',
				),
			),
			'check' => array(
				'header' => array(
					'value' => '<input type="checkbox" onclick="invertAll(this, this.form);" class="input_check" />',
				),
				'data' => array(
					'sprintf' => array(
						'format' => '<input type="checkbox" name="smushit[%1$d]" class="input_check" />',
						'params' => array(
							'id_attach' => false,
						),
					),
				),
			),
		),
		'form' => array(
			'href' => $scripturl . '?action=admin;area=manageattachments;sa=smushitselect',
			'include_sort' => true,
			'include_start' => true,
		),
		'additional_rows' => array(
			array(
				'position' => 'below_table_data',
				'value' => '<input type="submit" name="smushit_submit" class="button_submit" value="' . $txt['smushit_attachment_now'] . '" />',
				'style' => 'text-align: right;',
			),
			array(
				'position' => 'after_title',
				'value' => isset($_SESSION['truth_or_consequence'])
					? $_SESSION['truth_or_consequence'] : $txt['smushit_attachment_check_desc'],
			),
		),

	);

	// Clear errors
	if (isset($_SESSION['truth_or_consequence']))
		unset($_SESSION['truth_or_consequence']);

	// Create the list.
	require_once($sourcedir . '/Subs-List.php');
	createList($listOptions);
}

/**
 * Retrieves the attachment information for the selected range
 *
 * - Called from browse or batch
 *
 * @param int $start
 * @param int $chunk_size
 * @param string $sort
 * @param string $type
 * @param int $size
 * @param string $age
 *
 * @return array $files
 */
function smushit_getFiles($start, $chunk_size, $sort = '', $type = '', $size = 0, $age = '')
{
	global $modSettings, $smcFunc;



	// Init
	if ($sort == '')
		$sort = 'a.id_attach DESC';

	if ($size === 0 && !empty($modSettings['smushit_attachment_size']))
		$size = 1024 * $modSettings['smushit_attachment_size'];

	// Make the query, smushit cant be larger than 1M :(
	$request = $smcFunc['db_query']('', '
		SELECT
			a.id_folder, a.filename, a.file_hash, a.attachment_type, a.id_attach,
			a.id_member, a.width, a.height, a.smushit, a.fileext, a.size, a.downloads,
			m.id_msg, m.id_topic, m.subject, m.id_msg, m.poster_time
		FROM {db_prefix}attachments AS a
			INNER JOIN {db_prefix}messages AS m ON (m.id_msg = a.id_msg)
		WHERE a.attachment_type = {int:attach}
			AND a.size BETWEEN {int:attach_size} AND 1024000
			AND (a.fileext = \'jpg\' OR a.fileext = \'png\' OR a.fileext = \'gif\')' .
		(($age != '') ? 'AND m.poster_time > {int:poster_time} ' : '') .
		(($type != '') ? 'AND a.smushit = {int:smushit}' : '') . '
		ORDER BY {raw:sort}
		' . ((!empty($chunk_size)) ? 'LIMIT {int:offset}, {int:limit} ' : ''),
		array(
			'offset' => $start,
			'limit' => $chunk_size,
			'attach' => 0,
			'attach_size' => $size,
			'poster_time' => $age,
			'sort' => $sort,
			'smushit' => 0,
		)
	);
	// Put the results in an array
	$files = array();
	while ($row = $smcFunc['db_fetch_assoc']($request))
		$files[] = $row;
	$smcFunc['db_free_result']($request);

	return $files;
}

/**
 * Determines how many files meet our smush.it criteria
 *
 * - Uses age, size, type as parameters in determining the list
 *
 * @param boolean $not_smushed
 */
function smushit_getNumFiles($not_smushed = false)
{
	global $modSettings, $smcFunc;
	// Get the image attachment count that meets the criteria
	$request = $smcFunc['db_query']('', '
		SELECT COUNT(a.id_attach)
		FROM {db_prefix}attachments AS a
			INNER JOIN {db_prefix}messages AS m ON (m.id_msg = a.id_msg)
		WHERE a.attachment_type = {int:attach}
			AND a.size BETWEEN {int:attach_size} AND 1024000
			AND m.poster_time > {int:poster_time}
			AND (a.fileext = \'jpg\' OR a.fileext = \'png\' OR a.fileext = \'gif\')' .
		(($not_smushed) ? 'AND a.smushit = {int:smushit}' : ''),
		array(
			'attach' => 0,
			'smushit' => 0,
			'attach_size' => !empty($modSettings['smushit_attachment_size'])
				? 1024 * $modSettings['smushit_attachment_size'] : 0,
			'poster_time' => isset($_POST['smushitage']) ? (time() - 24 * 60 * 60 * (int) $_POST['smushitage']) : 0,
		)
	);
	// Survey says we have this much work to do
	list ($num_files) = $smcFunc['db_fetch_row']($request);
	$smcFunc['db_free_result']($request);

	return $num_files;
}

/**
 * Main smushit controller
 *
 * - Runs smush.it on the supplied attachments
 * - Uses values set in the admin panel, size/age/format
 * - Copy's attachment to the forum base for processing
 * - Calls smush.it on the file, if successful then copy's file back to attachments
 * - updates database with any changes
 *
 * @param mixed[] $file
 */
function smushitMain($file)
{
	global $context, $txt, $smcFunc, $sourcedir;


	// Some needed functions
	require_once($sourcedir . '/CurlFetchWebdata.class.php');
	require_once($sourcedir . '/Subs-Graphics.php');

	// Get the actual attachment file location
	$filename_withpath = getAttachmentFilename($file['filename'], $file['id_attach'], $file['id_folder'], false, $file['file_hash']);

	// Read in the file data, we will pass this to the smush.it service
	$file_data = file_get_contents($filename_withpath);

	// Send in the data for processing
	$fetch_data = make_smushit_request($file, $file_data);

	// Success on the request?
	if ($fetch_data && $fetch_data->result('code') == 200 && !$fetch_data->result('error'))
	{
		// Parse the JSON response
		$response = json_decode($fetch_data->result('body'));

		// We have a valid response and an image and a size savings then we continue on like lemmings.
		if ($response && $response->success == true)
		{
			// We have and image and a size savings then we continue on like lemmings.
			if ((!empty($response->data->bytes_saved) && intval($response->data->bytes_saved) > 0 && !empty($response->data->image)))
			{
				// Decode the image in the response
				$image = base64_decode($response->data->image);
				$image_md5 = md5($response->data->image);

				// Corruption is not an option
				if ($response->data->image_md5 == $image_md5)
				{
					// See what kind of image file we got back and if we are allowed to change it if needed.
					$tempfile = $filename_withpath . '.tmp';

					// Save the image to a .tmp file
					file_put_contents($tempfile, $image);

					// See what we really have now
					save_smushit_file($tempfile, $filename_withpath, $file, $response);
				}
				else
					$context['smushit_results'][$file['id_attach']] = $file['filename'] . '|' . $txt['smushit_attachments_corrupt'];
			}
			// No savings in size possible, mark it as smushed so we don't try again
			else
			{
				if (isset($response->data->bytes_saved) && $response->data->bytes_saved <= 0)
				{
					$smcFunc['db_query']('', '
						UPDATE {db_prefix}attachments
						SET smushit = {int:smushit}
						WHERE id_attach = {int:id_attach}
						LIMIT 1',
						array(
							'id_attach' => $file['id_attach'],
							'smushit' => 1
						)
					);
				}

				// Just a general smush.it error or no savings message
				$context['smushit_results'][$file['id_attach']] = $file['filename'] . '|' . $txt['smushit_attachments_nosavings'];
			}
		}
		// Failure on the smushit size, invalid response bad image, to big, other
		else
			$context['smushit_results'][$file['id_attach']] = $file['filename'] . '|' . $txt['smushit_attachments_error'] . ' ' . $response->success;
	}

	// Done with this one, make sure we clean up after ourselves
	unset($result, $image, $response);
}

/**
 * Validates the returned image is good
 * If all tests pass, the returned image is saved in place of the existing one
 *
 * @param string $tempfile
 * @param string $filename_withpath
 * @param array $file
 * @param object $response
 */
function save_smushit_file($tempfile, $filename_withpath, $file, $response)
{
	global $txt, $context, $modSettings, $smcFunc;


	// See what we really have now
	$sizes = @getimagesize($tempfile);
	$known = array(1 => 'gif', 	2 => 'jpg', 3 => 'png', 9 => 'jpg');
	$smushit_ext = (isset($sizes[2], $known[$sizes[2]])) ? $known[$sizes[2]] : 'na';

	// Things are cool with the returned file type?
	if ((strtolower($file['fileext']) === 'gif' && $smushit_ext === 'png' && isset($modSettings['smushit_attachments_png'])) || (strtolower($file['fileext']) == $smushit_ext))
	{
		// Trust but verify ... ok really don't trust at all ... just verify that the returned file is good
		//  a) an image
		//  b) the same WxH dimensional size
		//  c) free of any hitchhikers
		if ($sizes !== false && $sizes[0] == $file['width'] && $sizes[1] == $file['height'] && checkImageContents($tempfile))
		{
			// Can we can copy over the original file
			if (!is_writable($filename_withpath))
			{
				$orig_perm = @fileperms($filename_withpath);
				@chmod($filename_withpath, 0664);
				clearstatcache();
			}

			// No turning back now .. onward men !!
			if (@copy($tempfile, $filename_withpath))
			{
				// In the slim chance the perm changed worked, try to set it back to what it was
				if (isset($orig_perm))
				{
					@chmod($filename_withpath, $orig_perm);
					unset($orig_perm);
				}

				$context['smushit_results']['+' . $file['id_attach']] = $file['filename'] . '|' . sprintf($txt['smushit_attachments_reduction'] . " %01.1f%% (%s) bytes", $response->data->compression, $response->data->bytes_saved);

				// Update the attachment database with the new file size and potentially new type / mime (gif-png)
				$smcFunc['db_query']('', '
					UPDATE {db_prefix}attachments
					SET size = {int:size},
						fileext = {string:ext},
						mime_type = {string:mime},
						smushit = {int:smushit}
					WHERE id_attach = {int:id_attach}
					LIMIT 1',
					array(
						'size' => $response->data->after_size,
						'ext' => $smushit_ext,
						'mime' => (isset($sizes['mime']) ? $sizes['mime'] : 'image/' . $smushit_ext),
						'id_attach' => $file['id_attach'],
						'smushit' => 1,
					)
				);
			}
			// Image failed to copy back to the attach directory
			else
				$context['smushit_results'][$file['id_attach']] = $file['filename'] . '|' . $txt['smushit_attachments_copyfail'];
		}
		// Image failed validation, skipping
		else
			$context['smushit_results'][$file['id_attach']] = $file['filename'] . $file['width'] . $file['height'] . '|' . $txt['smushit_attachments_verify'];
	}
	// Not allowed to change the file format so skip it
	else
		$context['smushit_results'][$file['id_attach']] = $file['filename'] . '|' . $txt['smushit_attachments_noformatchange'];
}

/**
 * Make the curl call to the smushit service
 *
 * @param array $file
 * @param string $file_data
 *
 * @return bool|Curl_Fetch_Webdata
 */
function make_smushit_request($file, $file_data)
{
	global $context, $txt;

	if (!empty($file_data))
	{
		// Standard headers
		$headers = array(
			'accept' => 'application/json',
			'content-type' => 'application/binary',
			'lossy' => 'false',
		);

		// Going to need Curl to make this call happen
		$fetch_data = new Curl_Fetch_Webdata(array(CURLOPT_HTTPHEADER => $headers, CURLOPT_TIMEOUT => 30));
		$fetch_data->get_url_data('https://smushpro.wpmudev.org/1.0/', $file_data);

		// Free up some space
		unset($file_data);

		if ($fetch_data->result('error'))
			$context['smushit_results'][$file['id_attach']] = $file['filename'] . '|' . $txt['smushit_attachments_error'] . ' ' . $fetch_data->result('error');

		return $fetch_data;
	}
	// Error on the web_fetch_data or a non JSON result ...
	else
		$context['smushit_results'][$file['id_attach']] = $file['filename'] . '|' . $txt['smushit_attachments_network'];

	return false;
}

/**
 * Called from the browse selection list
 *
 * - Runs smush.it on the selected files
 */
function SmushitSelect()
{
	global $context, $settings, $txt, $smcFunc;


	// Check the session
	checkSession('post');

	if (!empty($_POST['smushit']))
	{
		$attachments = array();
		loadLanguage('smushit');

		// All the attachments that have been selected to smush.it
		foreach ($_POST['smushit'] as $smushID => $dummy)
			$attachments[] = (int) $smushID;

		// While we have attachments to work on
		if (!empty($attachments))
		{
			// Make the query
			$request = $smcFunc['db_query']('', '
				SELECT
					a.id_folder, a.filename, a.file_hash, a.attachment_type, a.id_attach, a.id_member, a.width, a.height,
					m.id_msg, m.id_topic, a.fileext, a.size, a.downloads, m.subject, m.id_msg, m.poster_time
				FROM {db_prefix}attachments AS a
					INNER JOIN {db_prefix}messages AS m ON (m.id_msg = a.id_msg)
				WHERE a.id_attach IN ({array_int:attachments})',
				array(
					'attachments' => $attachments,
				)
			);
			// Put the results in an array
			$files = array();
			while ($row = $smcFunc['db_fetch_assoc']($request))
				$files[] = $row;
			$smcFunc['db_free_result']($request);

			// Do the smush.it oh baby.
			foreach ($files as $row)
			{
				smushitMain($row);

				// Try get more time...
				@set_time_limit(60);
				if (function_exists('apache_reset_timeout'))
					@apache_reset_timeout();
			}

			// Errors or savings?
			if (isset($context['smushit_results']))
			{
				$truth_or_consequence = '';
				$savings = 0;

				// Build the string of painful errors or blissful savings
				foreach ($context['smushit_results'] as $attach_id => $result)
				{
					$attach_id = str_replace('+', '', $attach_id, $count);
					list($filename, $result) = explode('|', $result, 2);

					// Build the string, we only have a textbox to show our results
					if ($count != 0)
					{
						// Keep track of the size savings
						if (preg_match('~.*\((\d*)\).*~', $result, $thissavings))
							$savings += $thissavings[1];
						$truth_or_consequence .= $txt['smushit_valid'] . ' ' . $filename . ': ' . $result;
					}
					else
						$truth_or_consequence .=  $txt['smushit_invalid'] . ' ' . $filename . ': ' . $result;

					$truth_or_consequence .= '<br />';
				}

				// Show the total savings in a usable format
				if ($savings != 0)
				{
					$units = array('B', 'KB', 'MB', 'GB', 'TB');
					$savings = max($savings, 0);
					$pow = floor(($savings ? log($savings) : 0) / log(1024));
					$pow = min($pow, count($units) - 1);
					$savings /= pow(1024, $pow);
					$truth_or_consequence .= '<strong>' . $txt['smushit_attachments_savings'] . ' ' . round($savings, 2) . ' ' . $units[$pow] . '</strong>';
				}

				// Save it in session
				$_SESSION['truth_or_consequence'] = $truth_or_consequence;
			}
		}
	}

	// Done, back to the browse list we go
	$_REQUEST['sort'] = isset($_REQUEST['sort']) ? (string) $_REQUEST['sort'] : 'filesize';
	if (isset($_REQUEST["desc"]))
		$_REQUEST['sort'] .= ';desc';

	redirectexit('action=admin;area=manageattachments;sa=smushitbrowse;sort=' . $_REQUEST['sort'] . ';start=' . $_REQUEST['start']);
}




/**
 * Called from the scheduled task area, runs smushit on a reoccurring basis
 */
function scheduled_smushit()
{
	global $modSettings;

	// Need to do this so we have some basic $txt available.
	loadEssentialThemeData();
	loadLanguage('Admin');
	loadLanguage('smushit');

	// Get the large files
	$size = (!empty($modSettings['smushit_attachments_size']) ? 1024 * $modSettings['smushit_attachments_size']
		: 0);

	// Use a bit of a buffer to look back a couple of days, smush.it can be down from time to time
	$age = time() - (72 * 60 * 60);

	// Load the attachment files that  match
	$files = smushit_getFiles(0, 0, '', 'unsmushed', $size, $age);

	// While we have attachments .... smush.em
	if (!empty($files))
	foreach ($files as $row)
	{
		smushitMain($row);

		// Try get more time...
		@set_time_limit(60);
		if (function_exists('apache_reset_timeout'))
			@apache_reset_timeout();
	}

	return true;
}



