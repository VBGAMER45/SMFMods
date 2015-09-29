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
 *
 * The Initial Developer of the Original Code is
 * wayne Mankertz.
 * Portions created by the Initial Developer are Copyright (C) 2011
 * the Initial Developer. All Rights Reserved.
 *
 * Contributor(s):
 *
 * ***** END LICENSE BLOCK ***** */
function template_main() {

 //if(!$user){

	//}

}

function template_fbc_main() {
    global $modSettings, $statuses, $statuses, $fb_hook_object, $context, $settings, $txt, $scripturl;

	$com_colour = empty($modSettings['comcolour']) ? 'light' : 'dark';
	$com_com = !empty($modSettings['fb_admin_commets_post']) ? $modSettings['fb_admin_commets_post'] : '0';
	$lcolour = empty($modSettings['lboxcolour']) ? 'light' : 'dark';
	$lfaxes = empty($modSettings['likesbhowface']) ? 'true' : 'false';
	$lhead = empty($modSettings['likesbhowhead']) ? 'true' : 'false';
	$lst = empty($modSettings['lboxshowstream']) ? 'true' : 'false';
	$pageurl = empty($modSettings['fb_admin_page_url']) ?  'http://www.facebook.com/pages/SA-Mod-Development/243668362371720' : $modSettings['fb_admin_page_url'];

	$roundframe = !empty($modSettings['fb_app_enablelbox']) ? '<div class="roundframe centertext">' : '<div class="roundframe">';
	$roundframe1 = !empty($modSettings['fb_app_enablecom']) ? '<div class="roundframe centertext">' : '<div class="roundframe">';

	if(!empty($modSettings['fb_app_enablelbox']) && !empty($modSettings['fb_app_enablecom']))
	  $roundfloat = '<div class="floatright">';
	else
	  $roundfloat = '';
	if(!empty($modSettings['fb_app_enablelbox']) && !empty($modSettings['fb_app_enablecom']))
	  $roundfloa1t = '</div>';
	else
	 $roundfloa1t = '';

	template_fbc_menu();

    echo'<div class="cat_bar">
			<h3 class="catbg">
		      '.$txt['fb_main5'].'
	        </h3>
		</div>';

	if(!empty($modSettings['fb_app_enablelbox']) || !empty($modSettings['fb_app_enablecom'])){
	echo'
    <span class="upperframe"><span></span></span>';

	if(!empty($modSettings['fb_app_enablelbox']) && empty($modSettings['fb_app_enablecom'])){
	   echo''.$roundframe.'';
	}
	elseif(!empty($modSettings['fb_app_enablecom']) && empty($modSettings['fb_app_enablelbox'])){
	   echo''.$roundframe1.'';
	}
	else{
	   echo'<div class="roundframe">';
	}

	if(!empty($modSettings['fb_app_enablelbox'])){
	   echo''.$roundfloat.'';
	   $fb_hook_object->call_facebook_hook('show_facebook_likebox',array(''.$pageurl.'','292',$lcolour,$lfaxes,$lst,$lhead,true));
	   echo''.$roundfloa1t.'';
	}

	if(!empty($modSettings['fb_app_enablecom'])){
	   $fb_hook_object->call_facebook_hook('show_facebook_comments',array($scripturl,$com_com,'750',$com_colour,true));
	}

	echo'<br class="clear" /><br class="clear" /></div>
	<span class="lowerframe"><span></span></span>';
	}

    template_fbc_copy();
}

function template_regfb_agree()
{
	global $boarddir, $context, $settings, $options, $scripturl, $txt, $modSettings;

	echo '
		<form action="', $scripturl, '?action=facebookintegrate;area=connect" method="post" accept-charset="', $context['character_set'], '" id="registration">
			<div class="cat_bar">
				<h3 class="catbg">', $txt['registration_agreement'], '</h3>
			</div>
			<span class="upperframe"><span></span></span>
			<div class="roundframe">
				<p>', $context['agreement'], '</p>
			</div>
			<span class="lowerframe"><span></span></span>
			<div id="confirm_buttons">
			<input type="submit" name="accept_agreement" value="', $txt['agreement_agree'], '" class="button_submit" />
			</div>
			<input type="hidden" name="accept_agreement" value="1" />
		</form>';
}

function template_fbc_set() {
   global $context,$sc, $modSettings, $profileUrl,$scripturl, $txt;
   $user = fb_cookie_parse();
   if(!$user){
   echo'<div class="information">
	   <div align="center" class="error">'.$txt['fb_nocon'].'</div>';
    echo'<div align="center"><a href="'.$profileUrl.'">'.$txt['fb_nocon1'].'</a></div></div>';
	}else{
     echo' <form action="', $scripturl, '?action=profile;area=fsettings;u='.$_GET['u'].';save" method="post" accept-charset="', $context['character_set'], '" />
	 <div class="cat_bar">
				<h3 class="catbg">
					<span class="ie6_header floatleft">
					  '.$txt['fb_main5'].'
					</span>
				</h3>
			</div>
			<p class="windowbg description">'.$txt['fb_set1'].'</p>
			<div class="windowbg2">
				<span class="topslice"><span></span></span>
				<div class="content">
	       <dl class="register_form" >
		   <dt>
			<label for="pwp">'.$txt['fb_set2'].'</label>
				</dt>
					<dd>
					<input type="checkbox" name="pwp" class="input_check" ' . ( $context['fbpwp'] ? ' checked="checked" ' : '') . ' />
				</dd>
				<dt>';
			if ($modSettings['enable_buddylist']){
			echo'
			<label for="impf">'.$txt['fb_set3'].'</label>
				</dt>
					<dd>
					<button type="button"  onclick="javascript:window.location.href = \''. $scripturl, '?action=profile;area=fsettings;u='.$_GET['u'].';import\';">'.$txt['fb_set4'].'</button>
				</dd>';
			}
		echo'
				<dt><label for="impf">'.$txt['fb_aimp'].'<div class="smalltext">'.$txt['fb_aimp1'].'</div></label>
				</dt>
					<dd>
					<button type="button"  onclick="javascript:window.location.href = \''. $scripturl, '?action=profile;area=fsettings;u='.$_GET['u'].';doavatar\';">'.$txt['fb_set4'].'</button>
				</dd></dl><hr />
		  <div align="center"><a href="'.$scripturl. '?action=facebookintegrate;area=usyncc;sesc='.$sc.'" onclick="return confirm(\''.$txt['fb_sync4'].'\');">'.$txt['fb_sync3'].'</a></div>
		 <div class="righttext">
						<input type="submit" value="', $txt['change_profile'], '" class="button_submit" /></div>
		 </div>
				<span class="botslice"><span></span></span>
			</div> ';
			}
			 template_fbc_copy();
}

function template_fbbp() {
   global $modSettings, $loginUrl, $txt;

   echo'<div class="cat_bar">
		<h3 class="catbg">
	       '.$txt['fb_dfbreg6'].'
		</h3>
		  </div>
	         <span class="upperframe"><span></span></span>
			    <div class="roundframe centertext">
				'.$txt['fb_dfbreg7'].'
				<a href="'.$loginUrl.'"><img src="'.$modSettings['fb_log_logo'].'" alt="" /></a>
			 </div>
	    <span class="lowerframe"><span></span></span>';
		 template_fbc_copy();

}

function template_fbc_friends() {
     global $txt, $fbuser, $modSettings, $fb_hook_object, $context, $Url, $friends;

	 template_fbc_menu();

   if($fbuser){
   echo' <div class="cat_bar">
		<h3 class="catbg">
		'.$txt['fb_friends'].'
	</h3></div>
	<span class="upperframe"><span></span></span>
	<div class="roundframe centertext">
	<div class="pagesection">
				<div class="nextlinks">', $context['page_index'], '</div>
		 </div>';
	 if(!empty($friends['data'])){
    $counter = 0;
		foreach($friends['data'] as $friend){

		$forum_name_body = strip_tags(htmlspecialchars($context['forum_name'], ENT_QUOTES));

		echo'<script type="text/javascript">
		   function newInvite1(obj){
                 var receiverUserIds = FB.ui({method: \'apprequests\', to: \'\' + obj + \'\', message: \'Invite to '.$forum_name_body.'.\', data: \''.$friend['id'].'\'});
            }
		</script>';

			if ($counter % 4 == 0) {
				echo '	<table width="100%" cellpadding="1" cellspacing="1" border="0" >
				<tr class="windowbg">';
			}
			echo '<td align="right" width="25%">';
				echo '<span class="floatleft"><a href="http://www.facebook.com/profile.php?id='.$friend['id'].'" target="blank">
				<fb:profile-pic uid="'.$friend['id'].'" size="square" facebook-logo="true" linked="false"/></a></span>
				<span class="floatright"><a href="http://www.facebook.com/profile.php?id='.$friend['id'].'" target="blank">'.$fb_hook_object->character_clean($friend['name']).'</a><br /> '.$friend['id'].'';
			echo '<br /> <a href="javascript:void(0)" onclick="newInvite1('.$friend['id'].'); return true;"><span>Invite</span> | <a href="http://www.facebook.com/messages/'.$friend['id'].'" target="blank"><span>'.$txt['message'].'</span></a></span>';

			echo '</td>';

			if ($counter % 4 != 0 && $counter != sizeof($friends['data'])) {

				//echo '	</tr>';
			}
			$counter++;
		}

		if ($counter % 4 != 0) {
			echo '<td colspan="2" align="right" width="100%">&nbsp;</td></tr>';
		}
		echo '
						</table>';
						 echo'<div class="pagesection">
				<div class="nextlinks">', $context['page_index'], '</div>
		 </div>';}
	 else{echo''.$txt['fb_nofriends'].'';}
     echo' </div>
	<span class="lowerframe"><span></span></span>';
		 }
		 else{
 echo'<div class="information"><div align="center" class="error">'.$txt['fb_nocon'].'</div>';
    echo'<div align="center"><a href="'.$Url.'">'.$txt['fb_nocon1'].'</a></div></div>';
 }
 template_fbc_copy();

}

function template_fblogsync() {
global $txt, $scripturl, $modSettings, $context;

    echo' <form action="', $scripturl, '?action=login2;sync" name="frmLogin" id="frmLogin" method="post" accept-charset="', $context['character_set'], '" ', empty($context['disable_login_hashing']) ? ' onsubmit="hashLoginPassword(this, \'' . $context['session_id'] . '\');"' : '', '>';
	       echo' <span class="upperframe"><span></span></span>
	            <div class="roundframe centertext">';
				if(isset($_GET['nt'])){
				echo'<div class="error">'.$txt['fb_regallready4'].' <strong>'.$_GET['u'].'</strong> '.$txt['fb_regallready5'].'</div><br />';
				}
				echo'<dl class="register_form" >
					<dt><strong>'.$txt['fb_regname'].'</strong></dt>
					<dd><input type="text" name="user" size="20" value="', $context['default_username'], '" class="input_text" /></dd>
					<dt><strong>'.$txt['fb_regpass'].'</strong></dt>
					<dd><input type="password" name="passwrd" value="', $context['default_password'], '" size="20" class="input_password" /></dd>
				</dl>
				<p><input type="submit" value="', $txt['login'], '" class="button_submit" /></p>';
				echo'</div>
	          <span class="lowerframe"><span></span></span></form>';
			  template_fbc_copy();
}

function template_fbconnect() {
global $txt, $scripturl, $fb_object, $FacebookName, $FaceBookUsername, $modSettings, $context;

		SAFacebookhooks::face_init();

	echo'<form action="', $scripturl, '?action=facebookintegrate;area=connect;register" method="post" accept-charset="', $context['character_set'], '" >';
      echo'
				<div class="cat_bar" align="center">
		              <h3 class="catbg">
		                '. $txt['fb_reg'].'
	                  </h3>
				   </div>
				<div class="information" align="center"><strong>'.$txt['fb_reg2'].'</strong><br /><br />
	<strong><div class="error">'.$txt['fb_regallready1'].'<a href="'.$scripturl.'?action=facebookintegrate;area=logsync"> '.$txt['fb_regallready2'].'</a> '.$txt['fb_regallready3'].'</div></strong></div>
	               <span class="upperframe"><span></span></span>
	            <div class="roundframe centertext"><div class="cat_bar">
		              <h3 class="catbg">
		                '.$txt['fb_reg44'] .'
	                  </h3>
				   </div>';
	  echo'<dl class="register_form" >
				<dt>
					<strong>'.$txt['fb_regname'].'</strong>
				</dt>
				<dd>
					<input type="text" name="real_name" value="'.(!empty($FaceBookUsername) ? $FaceBookUsername : $FacebookName).'" />
				</dd>
			</dl>';

      echo'<dl class="register_form" >
			   <dt>
					<strong>'.$txt['fb_regpass'].'</strong>
			   </dt>
			   <dd>
					<input type="password" name="passwrd1" value="" />
			   </dd>
		   </dl>';

      echo'<dl class="register_form" >
			   <dt>
					<strong>'.$txt['fb_regpassv'].'</strong>
			   </dt>
			   <dd>
					<input type="password" name="passwrd2" value="" />
			   </dd>
		   </dl>';
	if(!empty($modSettings['fb_app_enablecp'])){
   if (!empty($context['profile_fields']) || !empty($context['custom_fields']))
	{
	echo'<div class="cat_bar">
		              <h3 class="catbg">
				'.$txt['fb_app_add_info'].'
		</h3></div>';
		if (!empty($context['profile_fields']))
	{
        foreach ($context['profile_fields'] as $key => $field)
		{

					echo '<dl class="register_form">
						<dt>
							<strong', !empty($field['is_error']) ? ' style="color: red;"' : '', '>', $field['label'], '</strong>';

				// Does it have any subtext to show?
				if (!empty($field['subtext']))
					echo '
							<span class="smalltext">', $field['subtext'], '</span>';

				echo '
						</dt>
						<dd>';

				// Want to put something infront of the box?
				if (!empty($field['preinput']))
					echo '
							', $field['preinput'];

				// What type of data are we showing?
				if ($field['type'] == 'label')
					echo '
							', $field['value'];

				// Maybe it's a text box - very likely!
				elseif (in_array($field['type'], array('int', 'float', 'text', 'password')))
					echo '
							<input type="', $field['type'] == 'password' ? 'password' : 'text', '" name="', $key, '" id="', $key, '" size="', empty($field['size']) ? 30 : $field['size'], '" value="', $field['value'], '" tabindex="', $context['tabindex']++, '" ', $field['input_attr'], ' class="input_', $field['type'] == 'password' ? 'password' : 'text', '" />';

				// You "checking" me out? ;)
				elseif ($field['type'] == 'check')
					echo '
							<input type="hidden" name="', $key, '" value="0" /><input type="checkbox" name="', $key, '" id="', $key, '" ', !empty($field['value']) ? ' checked="checked"' : '', ' value="1" tabindex="', $context['tabindex']++, '" class="input_check" ', $field['input_attr'], ' />';

				// Always fun - select boxes!
				elseif ($field['type'] == 'select')
				{
					echo '
							<select name="', $key, '" id="', $key, '" tabindex="', $context['tabindex']++, '">';

					if (isset($field['options']))
					{
						// Is this some code to generate the options?
						if (!is_array($field['options']))
							$field['options'] = eval($field['options']);
						// Assuming we now have some!
						if (is_array($field['options']))
							foreach ($field['options'] as $value => $name)
								echo '
								<option value="', $value, '" ', $value == $field['value'] ? 'selected="selected"' : '', '>', $name, '</option>';
					}

					echo '
							</select>';
				}

				// Something to end with?
				if (!empty($field['postinput']))
					echo '
							', $field['postinput'];

				echo '
						</dd></dl>';

		}
}
		foreach ($context['custom_fields'] as $field)
			echo '
			<dl class="register_form">
						<dt>
							<strong>', $field['name'], '</strong>
							<span class="smalltext">', $field['desc'], '</span>
						</dt>
						<dd>
							', $field['input_html'], '
						</dd>
					</dl>
						';
	}
	}
		       echo'<br /><input type="submit" name="submit" value="'.$txt['fb_regs'].'" class="button_submit" />';

	   echo'</div>
	          <span class="lowerframe"><span></span></span><input type="hidden" name="accept_agreement" value="1" /></form>';

			template_fbc_copy();
}
function template_fbc_menu() {
global $modSettings, $context, $pic, $user, $txt, $Url, $settings, $scripturl;
if ($context['user']['is_logged']){

  if(!empty($modSettings['fb_mode3']) || !empty($modSettings['fb_mode2']) || !empty($modSettings['fb_mode4'])){
  echo '<div class="buttonlist floatright">
		<ul>';
		    if(!empty($modSettings['fb_mode2'])){
			    echo'<li><a ', ($context['fb_do'] == 'main' ? 'class="active"' : '') . ' href="'.$scripturl.'?action=facebook;area=main"><span>'.$txt['fb_main5'] .'</span></a></li>';
			}
			if(!empty($modSettings['fb_mode4'])){
			    echo'<li><a href="javascript:void(0)" onclick="newInvite(); return false;"><span>'.$txt['fb_invite'].'</span></a></li>';
			}
			if(!empty($modSettings['fb_mode2'])){
			    echo'<li><a ', ($context['fb_do'] == 'friends' ? 'class="active"' : '') . ' href="'.$scripturl.'?action=facebook;area=friends"><span>'.$txt['fb_friendsyour'].'</span></a></li>';
			}
			if(!empty($modSettings['fb_mode3'])){
			    echo'<li>'; echo SAFacebookhooks::facebook_showPub(
					    array(
							'subject' => $context['forum_name'],
							'body' => !empty($settings['site_slogan']) ? $settings['site_slogan'] : '',
							'href' => $scripturl,
							'txt_label' => $txt['fb_woym'],
                            'isPost' => false,
				        )
					);echo'</li>';
		    }
		echo'</ul>
	  </div><br /><br />';
	 }
	}
}
function template_fbc_copy() {
echo '<br />
<div class="smalltext" align="center">SA Facebook Intergration '.SAFacebookhooks::VERSION.'<br />
&copy; 2014 <a href="http://www.smfhacks.com">SMF Hacks</a>
</div>';
}
?>