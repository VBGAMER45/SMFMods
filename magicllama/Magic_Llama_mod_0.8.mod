<id>
Magic Llama Mod For SMF RC1
</id>

<version>
Full 0.8
</version>

<mod info>
This is the Full version of the Magic Llama Mod for SMF RC 1 no other updates from the past are needed!
This mod edits 12 files with 19 Steps!

Please read the README.txt file for more instructions on installing this mod!
</mod info>

<author>
Aquilo
</author>

<homepage>
http://www.xtram.net
</homepage>

<edit file>
index.php
</edit file>

<search for>
	// Load the current user's permissions.
	loadPermissions();
</search for>

<add after>
	// load a new Llama.
	if ($modSettings['freeLlamas'] == 1)
		loadLlamas();
</add after>

<search for>
		'.xml' => array('News.php', 'ShowXmlFeed'),
</search for>

<add after>
		'magicLlama' =>  array('Karma.php', 'magicLlama'),
</add after>

<search for>
		'jsoption' => array('Themes.php', 'SetJavaScript'),
</search for>

<add after>
		'Llamalog' => array('LlamaKeeper.php', 'Llamalog'),
</add after>

<edit file>
Sources/Load.php
</edit file>

<search for>
		'permissions' => array()
</search for>

<replace>
		'permissions' => array(),
		'goodllamas' => $user_settings['goodllamas'],
		'goodpoints' => $user_settings['goodpoints'],
		'badllamas' => $user_settings['badllamas'],
		'badpoints' => $user_settings['badpoints']
</replace>

<search for>
			mem.location, mem.ICQ, mem.AIM, mem.YIM, mem.MSN, mem.posts, mem.lastLogin, mem.karmaGood, mem.karmaBad,
</search for>

<add after>
			mem.badllamas, mem.goodllamas, mem.badpoints, mem.goodpoints,
</add after>

<search for>
		'karma' => array(
			'good' => &$profile['karmaGood'],
			'bad' => &$profile['karmaBad'],
			'allow' => !$user_info['is_guest'] && $user_info['posts'] >= $modSettings['karmaMinPosts'] && allowedTo('karma_edit') && !empty($modSettings['karmaMode']) && $ID_MEMBER != $user
		),
</search for>

<add after>
		'llamamod' => array(
			'goodpoints' => &$profile['goodpoints'],
			'badpoints' => &$profile['badpoints'],
			'goodllamas' => &$profile['goodllamas'],
			'badllamas' => &$profile['badllamas'],
		),
</add after>

<search for>
?>
</search for>

<add before>
// Create new Life for all SMF Boards!
function LoadLlamas ()
{
	global $context, $db_prefix, $modSettings;
	global $options, $settings, $user_info;

	// if the user has the Llamas turned off and you allow it, or the user is a guest give them a blank Llama
	if ((($modSettings['allowllamasoff'] == 1) && isset($options['hide_llama']) && ($options['hide_llama'] == 1)) || ($user_info['is_guest'] == 1))
	{
		$context['LlamaInfo']['Llamaid'] = '';
		return $context;
	}

	// Create a new Llama!
	$Llamaid = substr(md5_hmac(time(), 'Llys'), 0, 15);

	$c = 0;
	$needle = rand(1, 100);

	// start rolling the dice!
	for ($i=0; $i<$modSettings['Llama_chances']; $i++)
	{
		// if the needle is found give them a Llama
		if ($needle == rand(1, 100))
		{
			$c = 1;
		}
	}

	// did we find you a Llama?
	if ($c != 0)
	{
		// setting the Llama type.
		$thisLlama = (int) rand(1, 2);

		// set the point value of this Llama
		$points = (int) rand($modSettings['Type'.$thisLlama.'_min_points'], $modSettings['Type'.$thisLlama.'_max_points']);

		// add him/her to the database.
		db_query("
			INSERT INTO {$db_prefix}llama_info
				(ID, Type, points, llama_id, member, Released, Caught)
			VALUES (NULL, $thisLlama, $points, '$Llamaid', NULL, " . time() . ", NULL)", __FILE__, __LINE__);

		// Freed Llamas to date.
		db_query("
			UPDATE {$db_prefix}settings
				SET value = value+1
			WHERE variable = 'FreedLlamas' LIMIT 1", __FILE__, __LINE__);

		// tag the Llama.
		$context['LlamaInfo']['Llamaid'] = $Llamaid;

	} else {

		// Sorry no Llama this time!
		$context['LlamaInfo']['Llamaid'] = '';
	}

	// a new baby Llama is Released to the world! or not! >:D
	return $context;
}

</add before>

<edit file>
Sources/Karma.php
</edit file>

<search for>
// What's this?  I dunno, what are you talking about?  Never seen this before, nope.  No siree.
</search for>

<add before>
// muhahahahahahahahaha, the Llama's have been set FREE!
function magicLlama()
{
	global $_REQUEST, $user_info, $modSettings, $db_prefix, $context;

	// NO GUESTS ALLOWED!
	if ($user_info['is_guest'])
		is_not_guest();

	// set your user id stored in $context array
	$userid = $context['user']['id'];

	// now lets see if we have a Llama by that name!?
	$request = db_query("
			SELECT *
			FROM {$db_prefix}llama_info
			WHERE llama_id = '$_REQUEST[magicLlama]'
			LIMIT 1", __FILE__, __LINE__);

	// most likely a script kiddy so we'll let the Llamas have at him!
	if (!$request || mysql_num_rows($request) == 0)
		fatal_error('Bad Llama id! >:(', true);

	$Llamainfo = mysql_fetch_assoc($request);
	mysql_free_result($request);

	// oops got here too late! ha...ha...!
	if ($Llamainfo['member'] != '')
	{
		$context['title'] = 'Sorry too late!';
		$context['display'] = $modSettings['lateLlama'];
	}
	else
	{
		// let's get your info in the database befor someone else get's the Llama!
		db_query("
			UPDATE {$db_prefix}llama_info
			SET member = $userid, Caught = " . time() . "
			WHERE llama_id = '$_REQUEST[magicLlama]'
			LIMIT 1", __FILE__, __LINE__);

		$field1 = ($Llamainfo['Type'] == 1) ? 'karmaGood' : 'karmaBad';
		$field2 = ($Llamainfo['Type'] == 1) ? 'goodpoints' : 'badpoints';
		$field3 = ($Llamainfo['Type'] == 1) ? 'goodllamas' : 'badllamas';

		// log the users points
		updateMemberData($userid, array(
					$field1 => $field1 . ' + ' . $Llamainfo['points'],
					$field2 => $field2 . ' + ' . $Llamainfo['points'],
					$field3 => $field3 . ' + 1',
				));

		$context['title'] = 'You got a ' . $modSettings['Type' . $Llamainfo['Type']] . '!';
		$context['display'] = str_replace(
					array("%N", "%P", "%K"),
					array(
						$modSettings['Type' . $Llamainfo['Type']],
						(($Llamainfo['Type']==1) ? $Llamainfo['points'] : '-' . $Llamainfo['points']),
						$modSettings['karmaLabel']),
					$modSettings['Type' . $Llamainfo['Type'] . '_discription']);
	}

	if (!isset($context['page_title']))
		$context['page_title'] = $context['title'];

	loadTemplate('MagicLlama');
	$context['sub_template'] = 'Llama_speak';

	unset($Llamainfo);
	obExit();
}
</add before>

<edit file>
Sources/ModSettings.php
</edit file>

<search for>
			array('text', 'karmaApplaudLabel'),
			array('text', 'karmaSmiteLabel'),
</search for>

<add after>
		array('heading', &$txt['lableLlamas']),
			array('check', 'freeLlamas', &$txt['enableLlamas']),
			array('check', 'showLlamaStats', &$txt['enableLlamastats']),
			array('check', 'allowllamasoff', &$txt['allowllamasoff']),
		array('rule'),
			array('int', 'Llama_chances', &$txt['LlamaChance']),
			array('text', 'Llama_image', &$txt['Llamaimage']),
			array('int', 'llamaW', &$txt['LlamaW']),
			array('int', 'llamaH', &$txt['LlamaH']),
			array('int', 'Llamaspeed', &$txt['Llamaspeed']),
		array('rule'),
			array('text', 'Type1', &$txt['lableLlama1']),
			array('text', 'Type1_min_points', &$txt['Llama1min']),
			array('text', 'Type1_max_points', &$txt['Llama1max']),
			array('text', 'Type1_discription', &$txt['Llama1discription']),
		array('rule'),
			array('text', 'Type2', &$txt['lableLlama2']),
			array('text', 'Type2_min_points', &$txt['Llama2min']),
			array('text', 'Type2_max_points', &$txt['Llama2max']),
			array('text', 'Type2_discription', &$txt['Llama2discription']),
		array('rule'),
			array('text', 'lateLlama', &$txt['lateLlama']),
</add after>

<edit file>
Sources/Subs.php
</edit file>

<search for>
		if (!empty($modSettings['modlog_enabled']))
			$context['admin_areas']['maintenance']['areas']['view_moderation_log'] = '<a href="' . $scripturl . '?action=modlog">' . $txt['modlog_view'] . '</a>';
</search for>

<add after>
		$context['admin_areas']['maintenance']['areas']['view_Llama_log'] = '<a href="' . $scripturl . '?action=Llamalog">' . $txt['LlamaLog'] . '</a>';
</add after>

<edit file>
Themes/default/index.template.php
</edit file>

<search for>
	global $context, $settings, $options, $scripturl, $txt;
</search for>

<add after>
	global $modSettings, $user_info;
</add after>

<search for>
	echo '
	</body>
</html>';
}
</search for>

<add before>
	if (($modSettings['freeLlamas'] == 1) && ($user_info['is_guest'] != 1) && ($context['LlamaInfo']['Llamaid'] != ''))
	{
		srand ((double) microtime() * time());
		$random->x = rand(1, 640);
		$random->y = rand(1, 480);

		if (!$modSettings['llamaW'] || !$modSettings['llamaH'])
			list($width, $height) = getimagesize($settings['images_url'] . '/' . $modSettings['Llama_image']);
		else
		{
			$width  = $modSettings['llamaW'];
			$height = $modSettings['llamaH'];
		}

		echo '

		<script language="JavaScript" type="text/javascript">
			var llama_speed = ', (!empty($modSettings['Llamaspeed']) ? $modSettings['Llamaspeed'] : '40'), ';
		</script>
		<script language="JavaScript" type="text/javascript" src="MoveObj.js"></script>
		<script language="JavaScript" type="text/javascript">
		/*	Floating image script
			By Virtual_Max (http://www.geocities.com/siliconvalley/lakes/8620)
			Permission granted to Dynamicdrive.com to feature it in it\'s archive
			For 100\'s of FREE DHTML scripts and components,
			Visit http://dynamicdrive.com */

		var Llama1;
		function StartLlama()
		{
			Llama1 = new Chip("Llama1",', $width, ',', $height, ');
			Llama1.move();
		}
		window.onUnload = "Llama1.stop();";
		</script>

		<div id="Llama1" style="position: absolute; top: ', $random->y, 'px; left: ', $random->x, 'px; width: ', $width, 'px; height: ', $height, 'px; z-index: 5">
			<a href="', $scripturl, '?action=magicLlama;magicLlama=', $context['LlamaInfo']['Llamaid'],'">
			<img src="', $settings['images_url'], '/', $modSettings['Llama_image'], '" width="', $width, '" height="', $height, '" border="0" /></a>
		</div>

		<script language="JavaScript" type="text/javascript">
			StartLlama();
		</script>
';
	}

</add before>

<edit file>
Sources/Profile.php
</edit file>

<search for>
		'karma' => array(
			'good' => empty($user_profile[$memID]['karmaGood']) ? '0' : $user_profile[$memID]['karmaGood'],
			'bad' => empty($user_profile[$memID]['karmaBad']) ? '0' : $user_profile[$memID]['karmaBad'],
		),
</search for>

<add after>
		'llamamod' => array(
			'goodpoints' => empty($user_profile[$memID]['goodpoints']) ? '0' : $user_profile[$memID]['goodpoints'],
			'badpoints' => empty($user_profile[$memID]['badpoints']) ? '0' : $user_profile[$memID]['badpoints'],
			'goodllamas' => empty($user_profile[$memID]['goodllamas']) ? '0' : $user_profile[$memID]['goodllamas'],
			'badllamas' => empty($user_profile[$memID]['badllamas']) ? '0' : $user_profile[$memID]['badllamas'],
		),
</add after>

<edit file>
Themes/default/Profile.template.php
</edit file>

<search for>
	elseif ($modSettings['karmaMode'] == '2')
		echo '
				<tr>
					<td>
						<b>', $modSettings['karmaLabel'], ' </b>
					</td><td>
						+', $context['member']['karma']['good'], '/-', $context['member']['karma']['bad'], '
					</td>
				</tr>';
</search for>

<add after>
	if ($modSettings['freeLlamas'] == '1' && $modSettings['showLlamaStats'] == '1')
		echo '
				<tr>
					<td>
						<b>', $modSettings['karmaLabel'], ' from ', $modSettings['Type1'], ': </b>
					</td><td>
						+', $context['member']['llamamod']['goodpoints'], '/ ', $modSettings['Type1'], ' Caught: ', $context['member']['llamamod']['goodllamas'], '
					</td>
				</tr><tr>
					<td>
						<b>', $modSettings['karmaLabel'], ' from ', $modSettings['Type2'], ': </b>
					</td><td>
						-', $context['member']['llamamod']['badpoints'], '/ ', $modSettings['Type2'], ' Caught: ', $context['member']['llamamod']['badllamas'], '
					</td>
				</tr>';

</add after>

<Search for>
										<tr>
											<td colspan="2">
												<input type="hidden" name="default_options[show_board_desc]" value="0" />
												<label for="show_board_desc"><input type="checkbox" name="default_options[show_board_desc]" id="show_board_desc" value="1"', !empty($context['member']['options']['show_board_desc']) ? ' checked="checked"' : '', ' class="check" /> ', $txt[732], '</label>
											</td>
										</tr><tr>
</Search for>

<Replace>
					', ($modSettings['allowllamasoff'] ? '<tr>
											<td colspan="2">
												<input type="hidden" name="default_options[hide_llama]" value="0" />
												<label for="hide_llama"><input type="checkbox" name="default_options[hide_llama]"  id="hide_llama" value="1"' . (!empty($context['member']['options']['hide_llama']) ? ' checked="checked"' : '') . ' class="check" />' . $txt['llamaoption'] . '</td>
										</tr>' : ''), '<tr>
											<td colspan="2">
												<input type="hidden" name="default_options[show_board_desc]" value="0" />
												<label for="show_board_desc"><input type="checkbox" name="default_options[show_board_desc]" id="show_board_desc" value="1"', !empty($context['member']['options']['show_board_desc']) ? ' checked="checked"' : '', ' class="check" /> ', $txt[732], '</label>
											</td>
										</tr><tr>
</Replace>

<edit file>
Themes/default/languages/Profile.english.php
</edit file>

<Search for>
$txt[732] = 'Show board descriptions inside boards.';
</Search for>

<Add before>
$txt['llamaoption'] = 'Turn Magic Llama\'s off:';
</Add before>

<edit file>
Themes/default/Settings.template.php
</edit file>

<search for>
		array(
			'id' => 'show_board_desc',
			'label' => $txt[732],
		),
</search for>

<add after>
		array(
			'id' => 'hide_llama',
			'label' => $txt['llamaoption'],
		),
</add after>

<edit file>
Themes/default/languages/Admin.english.php
</edit file>

<search for>
?>
</search for>

<add before>
$txt['LlamaLog'] = 'View Llama Log!';
</add before>

<edit file>
Themes/default/languages/ModSettings.english.php
</edit file>

<search for>
$txt['karmaSmiteLabel'] = 'Karma smite label';
</search for>

<add after>
$txt['lableLlamas'] = 'Magic Llama\'s Settings';
$txt['freeLlamas'] = 'Enable Magic Llama\'s?';
$txt['Llama_chances'] = 'Chances for a Llama (out of 100):';
$txt['Llama_image'] = 'Magic Llama Image:';

$txt['Type1'] = 'Title for Good Llama:';
$txt['Type1_min_points'] = 'Minimum points for Good Llama:';
$txt['Type1_max_points'] = 'Maximum points for Good Llama:';
$txt['Type1_discription'] = 'Message to display for Good Llama:';

$txt['Type2'] = 'Title for Evil Llama:';
$txt['Type2_min_points'] = 'Minimum points for Evil Llama:';
$txt['Type2_max_points'] = 'Maximum points for Evil Llama:';
$txt['Type2_discription'] = 'Message to display for Evil Llama:';

$txt['lateLlama'] = 'Message to display for late Llama:';
$txt['Llamaspeed'] = 'Llama Speed: (0-???)<br /><small>The smaller the number the faster it moves!</small>';
$txt['llamaH'] = 'Height of Image:<br /><small>(0) Zero for auto detect!</small>';
$txt['llamaW'] = 'Width of Image:<br /><small>(0) Zero for auto detect!</small>';
$txt['allowllamasoff'] = 'Allow users to turn this off:';
$txt['showLlamaStats'] = 'Show stats in user profiles:';
</add after>