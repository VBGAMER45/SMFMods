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
function template_main() {
global $context, $scripturl, $modSettings, $txt;

  if(isset($_GET['deleted'])){echo'<div style="text-align: center;" class="information"><strong>'.$txt['knowledgebasedelete'].'</strong></div>';}

	if(isset($_REQUEST['cache_clean']) || isset($_REQUEST['article_recount']) || isset($_REQUEST['comment_recount'])){
        echo'<div class="information">'.$txt['kb_menu_tc'].'</div>';
	}

    if(!empty($context['get_featured']) && !empty($modSettings['kb_efeaturedarticle'])){
	    echo'
        <div class="title_bar">
		    <h3 class="titlebg">'.$txt['kb_featuredarticle'].'</h3>
	    </div>

	    <div class="hslice" id="recent_posts_content">
		    <dl id="ic_recentposts" class="middletext">';

			    foreach ($context['get_featured'] as $featured){

				    echo'<dt><strong><a href="'.$scripturl.'?action=kb;area=article;cont='.$featured['kbnid'].'" rel="nofollow">'.$featured['title'].'</a></strong> ', $txt['by'], ' <a href="'.$scripturl.'?action=profile;u='.$featured['id_member'].'">'.$featured['real_name'].'</a> (<a href="'.$scripturl.'?action=kb;area=cats;cat='.$featured['id_cat'].'">'.$featured['name'].'</a>)</dt>
			        <dd>'.$featured['date'].'</dd>';
			    }
		 echo'
			</dl>
	    </div>
	 <br />';
	}

  template_show_list('kb_list');
  template_kb_copy();
}

function template_kb_search() {
  global $txt, $modSettings, $settings;

   template_show_list('kb_search');

   if(!empty($modSettings['kb_salegend'])){
   echo'
	<div class="tborder" id="topic_icons">
		<div class="description">
			<p class="floatleft smalltext">
				<img src="'.$settings['images_url'].'/topic/normal_post.gif" alt="" align="middle" /> '.$txt['kb_iconmen'].'<br />
				<img src="'.$settings['images_url'].'/topic/hot_post.gif" alt="" align="middle" /> '.$txt['kb_iconmen1'].'<br />
				<img src="'.$settings['images_url'].'/topic/veryhot_post.gif" alt="" align="middle" /> '.$txt['kb_iconmen2'].'
			</p>
			<br class="clear" />
		</div>
	</div>';
	}

  template_kb_copy();
}
function template_KB_profile_main() {

	if(isset($_REQUEST['sa'])){

	    if($_REQUEST['sa']=='main'){
            template_KB_profile_articles_main();
        }
	    if($_REQUEST['sa']=='articles'){
            template_KB_profile_articles();
        }
	    if($_REQUEST['sa']=='unapproved'){
            template_KB_profile_notapproved();
        }
		if($_REQUEST['sa']=='logs'){
            template_KB_profile_articles_logs();
        }
    }
    else{
        template_KB_profile_articles_main();
    }
}
function template_KB_profile_articles_main() {
    global $txt, $membername, $total_articles;

	echo'
	<div class="cat_bar">
		<h3 class="catbg">
			<span class="ie6_header floatleft">
				'.$txt['kb_profile_nainname'].' - '.$membername.'
			</span>
		</h3>
	</div> ';

	echo'
	<span class="upperframe"><span></span></span>
	<div class="roundframe">';

		echo'<strong>'.$txt['kb_profile_articleinfo'].'</strong><hr />';
	    echo''.$txt['kb_profile_articleinfobyuser'].': '.$total_articles.' ';

	echo'
	</div>
	<span class="lowerframe"><span></span></span>';
}

function template_KB_profile_articles() {
   global $txt, $modSettings, $settings;

   template_show_list('kb_profile');

   if(!empty($modSettings['kb_salegend'])){
   echo'
	<div class="tborder" id="topic_icons">
		<div class="description">
			<p class="floatleft smalltext">
				<img src="'.$settings['images_url'].'/topic/normal_post.gif" alt="" align="middle" /> '.$txt['kb_iconmen'].'<br />
				<img src="'.$settings['images_url'].'/topic/hot_post.gif" alt="" align="middle" /> '.$txt['kb_iconmen1'].'<br />
				<img src="'.$settings['images_url'].'/topic/veryhot_post.gif" alt="" align="middle" /> '.$txt['kb_iconmen2'].'
			</p>
			<br class="clear" />
		</div>
	</div>';
	}

    template_kb_copy();
}

function template_KB_profile_notapproved() {
   global $txt, $modSettings, $settings;

   template_show_list('kb_profile');

   if(!empty($modSettings['kb_salegend'])){
   echo'
	<div class="tborder" id="topic_icons">
		<div class="description">
			<p class="floatleft smalltext">
				<img src="'.$settings['images_url'].'/topic/normal_post.gif" alt="" align="middle" /> '.$txt['kb_iconmen'].'<br />
				<img src="'.$settings['images_url'].'/topic/hot_post.gif" alt="" align="middle" /> '.$txt['kb_iconmen1'].'<br />
				<img src="'.$settings['images_url'].'/topic/veryhot_post.gif" alt="" align="middle" /> '.$txt['kb_iconmen2'].'
			</p>
			<br class="clear" />
		</div>
	</div>';
	}

    template_kb_copy();
}

function template_kb_searchmain(){
global $scripturl, $context, $modSettings, $settings, $txt;

		echo'<div class="cat_bar">
		    <h3 class="catbg">'.$txt['kb_searchforform1'].'</h3>
		</div><span class="upperframe"><span></span></span>
		<div class="roundframe">

		<form method="post" action="'.$scripturl.'?action=kb;area=search;sesc=', $context['session_id'], '">

            <dl class="register_form" >
			    <dt>
				    <strong>'.$txt['kb_searchfor'].':</strong>
			    </dt>
			    <dd>
					<input type="text" size="40" name="search" value="" />
			    </dd>
			</dl>

			<dl class="register_form" >
			    <dt>
				    <strong>'.$txt['kb_searchfortltle'].':</strong>
			    </dt>
			    <dd>
					<input type="checkbox" name="searchtitle" checked="checked" />
			    </dd>
			</dl>

			<dl class="register_form" >
			    <dt>
				    <strong>'.$txt['kb_searchforsum'].':</strong>
			    </dt>
			    <dd>
					<input type="checkbox" name="searchdescription" checked="checked" />
			    </dd>
			</dl>



			<dl class="register_form" >
			    <dt>
				    <strong>'.$txt['kb_searchforallcat1'].':</strong>
			    </dt>
			    <dd>
					<select name="cat">
			    <option value="0">'.$txt['kb_searchforallcat'].'</option>';

				foreach($context['knowcat'] as $i => $row){
	                if($row['view'] != '0'){
					   echo'<option value="'.$row['kbid'].'" >'.$row['name'].'</option>';
					}
	             }


				echo'</select>
			    </dd>
			</dl>

			<dl class="register_form" >
			    <dt>
				    <strong>'.$txt['kb_drange'].':</strong>
			    </dt>
			    <dd>
					<select name="daterange">
    	            <option value="0">'.$txt['kb_drange1'].'</option>
    	            <option value="30">'.$txt['kb_drange2'].'</option>
    	            <option value="60">'.$txt['kb_drange3'].'</option>
    	            <option value="90">'.$txt['kb_drange4'].'</option>
    	            <option value="180">'.$txt['kb_drange5'].'</option>
    	            <option value="365">'.$txt['kb_drange6'].'</option>
                </select>
			    </dd>
			</dl>';
			if(empty($modSettings['kb_privmode'])){
           echo' <dl class="register_form" >
			    <dt>
				    <strong>'.$txt['kb_postby'].':</strong>
			    </dt>
			    <dd>
					<input type="text" name="postername" id="postername" value="" />
	                <a href="', $scripturl, '?action=findmember;input=postername;quote=1;sesc=', $context['session_id'], '" onclick="return reqWin(this.href, 350, 400);"><img src="', $settings['images_url'], '/icons/assist.gif" alt="', $txt['find_members'], '" /></a>
	                <a href="', $scripturl, '?action=findmember;input=postername;quote=1;sesc=', $context['session_id'], '" onclick="return reqWin(this.href, 350, 400);">', $txt['find_members'], '</a>
			    </dd>
			</dl>	';
}

				echo'<br /><div style="text-align: center;">
				<input type="submit" name="send" class="button_submit" value="'.$txt['kb_searchforform'] .'" /></div>

		</form></div>
		<span class="lowerframe"><span></span></span>';

        echo'
		<div class="cat_bar">
		    <h3 class="catbg">'.$txt['kb_searchforja'].'</h3>
		</div><span class="upperframe"><span></span></span>
		<div class="roundframe">

			<form method="post" action="'.$scripturl.'?action=kb;jump;sesc=', $context['session_id'], '">
			    <dl class="register_form" >
			    <dt>
				    <strong>'.$txt['kb_pinfi7'].':</strong>
			    </dt>
			    <dd>
					<input type="text" size="4" name="jump" value="" />
			    </dd>
			</dl>

				<br />
                <div style="text-align: center;"><input type="submit" name="send" class="button_submit" value="'.$txt['kb_jumpgo'].'" /></div>

		</form></div>
		<span class="lowerframe"><span></span></span>';

		template_kb_copy();
}

function template_show_search(){
global $scripturl, $context, $data, $txt;

		$data .='
		<form method="post" action="'.$scripturl.'?action=kb;area=search;sesc='. $context['session_id']. '">

			<input type="text" size="15" name="search" value="" />
		    &nbsp;<input type="submit" name="send" class="button_submit" value="'.$txt['kb_searchforform'] .'" />
		</form>';

		return $data;
}

function template_kb_reporta() {
global $kbname, $context, $scripturl, $txt;

	echo'
	<form action="', $scripturl, '?action=kb;area=reporta;save;aid='.$_REQUEST['aid'].'" method="post" accept-charset="', $context['character_set'], '" onsubmit="submitonce(this);smc_saveEntities(\'postmodify\', [\'title\', \'description\']);">';

		echo'
		<div class="cat_bar">
		    <h3 class="catbg">'.$txt['kb_reports22'].' - '.$kbname.'</h3>
		</div>

		<span class="upperframe"><span></span></span>
		<div class="roundframe centertext">

		    <strong>'.$txt['kb_reports22ec'].'</strong><br /><br />

			<textarea rows="6" name="description" cols="60"></textarea>

			<br /><br /><input type="hidden" id="', $context['session_var'], '" name="', $context['session_var'], '" value="', $context['session_id'], '" />

			<input type="submit" name="send" class="button_submit" value="'.$txt['post'].'" />

		</div>
		<span class="lowerframe"><span></span></span>';
	echo'
	</form>';
    template_kb_copy();
}

function template_kb_edit() {
  global $scripturl, $txt, $kname, $settings, $modSettings, $context;
	echo'';
	echo '<div class="error" id="ajax_in_progress" style="display:none;">Fetching Preview......</div>
		<div id="results">
		    <div id="preview_section"', (isset($context['preview_message']) ? '' : ' style="display: none;"'), '>

			<div class="cat_bar">
				<h3 class="catbg">
					<span id="preview_subject">', (empty($context['preview_subject']) ? '' : $context['preview_subject']), '</span>
				</h3>
				</div>
			<div class="windowbg">
				<span class="topslice"><span></span></span>
				<div class="content">
						<div class="post" id="preview_body">
							', (empty($context['preview_message']) ? '<br />' : $context['preview_message']), '
						</div>
					</div>
					<span class="botslice"><span></span></span>
				</div><br /></div></div>';

	if(!isset($_GET['save']) || isset($_REQUEST['preview'])){

	    echo'<form id="kbeditform" action="', $scripturl, '?action=kb;area=edit;save;aid='.$context['edit'][0]['kbnid'].'" enctype="multipart/form-data" method="post" accept-charset="', $context['character_set'], '" >';
        echo'<div class="cat_bar">
			<h3 class="catbg">'.$txt['kb_xubcat2'].' - '.$kname.'</h3>
		</div>
		<span class="upperframe"><span></span></span>
			<div class="roundframe">';
			if(allowedTo('manage_kb')){
			echo'<dl class="register_form" >
			   <dt>
				    <strong>'.$txt['kb_memid'].':</strong>
			   </dt>
			   <dd>
					<input type="text" size="5" name="memid" value="',isset($_REQUEST['memid']) ? $_REQUEST['memid'] : $context['edit'][0]['id_member'],'" />
			   </dd>
			</dl>';
			if(!empty($modSettings['kb_efeaturedarticle'])){
			echo'<dl class="register_form" >
			   <dt>
				    <strong>'.$txt['kb_featuredarticle1'].':</strong>
			   </dt>
			   <dd>
				   <input type="checkbox" name="featured" class="input_check" ' . ( $context['edit'][0]['featured'] ? ' checked="checked" ' : '') . ' />
			   </dd>
			</dl>';
			}
			}
			$_POST['source'] = false;
			echo'<dl class="register_form" >
			   <dt>
				    <strong>'.$txt['kb_source'].':</strong>
			   </dt>
			   <dd>
					<input type="text" size="40" name="source" value="',!empty($context['edit'][0]['source']) ? $context['edit'][0]['source'] : $_POST['source'],'" />
			   </dd>
			</dl>';
			echo'<dl class="register_form" >
			   <dt>
				    <strong>'.$txt['knowledgebase_cat1'] .':</strong>
			   </dt>
			   <dd>
			<select name="cat" id="cat">';
				foreach($context['knowcat'] as $i => $knowl1){
				 if ($knowl1['view'] != '0' && KBAllowedto($knowl1['kbid'],'addarticle')){
				    echo'<option value="', $knowl1['kbid'], '"', ($context['edit'][0]['id_cat'] == $knowl1['kbid'] ? ' selected="selected"' : ''), '>', $knowl1['name'], '</option>';
			      }
			     }
			echo'</select>
			</dd>
			</dl>';
			echo'<dl class="register_form" >
			   <dt>
				    <strong>'.$txt['knowledgebasetitle'].':</strong>
			   </dt>
			   <dd>
					<input type="text" size="40" name="name" value="',!empty($context['preview_subject']) ? $context['preview_subject'] : $context['edit'][0]['title'],'" />
			   </dd>
			</dl>';
			if ($context['show_bbc']){
							echo '<div id="bbcBox_message"></div>';}

						if (!empty($context['smileys']['postform']) || !empty($context['smileys']['popup'])){
							echo '<div id="smileyBox_message"></div>';}

						echo template_control_richedit($context['post_box_name'], 'smileyBox_message', 'bbcBox_message');



			 if($modSettings['kb_enable_attachment']){
			 $modSettings['kb_num_attachment'] = !empty($modSettings['kb_num_attachment']) ? $modSettings['kb_num_attachment'] : '100';

			 echo' <br /><img src="', $settings['images_url'], '/icons/clip.gif" alt="-" /> <a href="javascript:void(0)" onclick="javascript:kbsearch_showhide(\'attachkb\');"><strong>'.$txt['kb_attach7'].'</strong></a>';
			   echo '<div class="attachkb" id="attachkb" style="display:none;">';
		if(!empty($context['kb_article_images'])){
			echo' <br /><hr /><strong>'.$txt['kb_attach5'].':</strong>
			<input type="hidden" name="kb_attach_del[]" value="0" />';
			echo'<div class="smalltext">

						'.$txt['kb_attach6'].'
						</div><hr /><br />';

	        foreach ($context['kb_article_images'] as $attachment){
			$newname = $attachment['filename'];
			echo '

							<div class=smalltext>
							<input type="checkbox" id="attachment_', $attachment['id_file'], '" name="kb_attach_del[]" value="', $attachment['id_file'], '"', empty($attachment['unchecked']) ? ' checked="checked"' : '', ' class="input_check" /> ', $newname, '
						</div>';}}


					if (count($context['kb_article_images']) < $modSettings['kb_num_attachment']){

		            echo'
		            <br /><hr /><strong>'.$txt['kb_attach1'].':</strong>
		            <div class="smalltext">';
		                if(!empty($modSettings['kb_mfile_attachment']) && !empty($modSettings['kb_num_attachment'])){
							echo''.$txt['kb_attach2'].':  ', $modSettings['kb_num_attachment'], ' '.$txt['kb_attach3'].' ' . round($modSettings['kb_mfile_attachment'] / 1024, 2) . 'KB';
						}
						if (!empty($modSettings['kb_attachmentExtensions'])){
			                echo '<br />'.$txt['kb_attach4'].': '.strtr($modSettings['kb_attachmentCheckExtensions'], array(',' => ', ')).'<br />';
						}echo'

						</div>
		               <hr /><br />

						<div class="smalltext">
							<input type="file" size="60" name="attachment[]" id="attachment1" class="input_file" /> (<a href="javascript:void(0);" onclick="cleanFileInput(\'attachment1\');">', $txt['clean_attach'], '</a>)';

		// Show more boxes only if they aren't approaching their limit.
		if (!empty($modSettings['kb_num_attachment'])){
			echo '
							<script type="text/javascript"><!-- // --><![CDATA[
								var allowed_attachments = ', $modSettings['kb_num_attachment'], ' - '.count($context['kb_article_images']).';
								var current_attachment = '.count($context['kb_article_images']).';

								function addAttachment()
								{
									allowed_attachments = allowed_attachments - 1;
									current_attachment = current_attachment + 1;
									if (allowed_attachments <= 0)
										return alert("', $txt['more_attachments_error'], '");

									setOuterHTML(document.getElementById("moreAttachments"), \'<div class="smalltext"><input type="file" size="60" name="attachment[]" id="attachment\' + current_attachment + \'" class="input_file" /> (<a href="javascript:void(0);" onclick="cleanFileInput(\\\'attachment\' + current_attachment + \'\\\');">', $txt['clean_attach'], '</a>)\' + \'</div><div class="smalltext" id="moreAttachments"><a href="#" onclick="addAttachment(); return false;">(', $txt['more_attachments'], ')<\' + \'/a><\' + \'/div>\');

									return true;
								}
							// ]]></script>
						</div>
						<div class="smalltext" id="moreAttachments"><a href="#" onclick="addAttachment(); return false;">(', $txt['more_attachments'], ')</a></div>';
        }
		}echo'</div>';}


				echo'<div id="confirm_buttons">
					<input type="hidden" id="', $context['session_var'], '" name="', $context['session_var'], '" value="', $context['session_id'], '" />
					<input type="submit" name="send" id="sendbutton" class="button_submit" value="'.$txt['post'].'" />
					<input type="submit" name="preview" id="previewbutton" class="button_submit" value="', $txt['preview'], '" />

				</div>
			</div>
			<span class="lowerframe"><span></span></span>';

	echo'</form>';

	}
	template_kb_copy();

}

function template_kb_know() {
  global $txt, $context, $scripturl, $modSettings, $settings;

    if(!empty($context['sa_cat'])){
	  template_show_list('kb_listcat');
      echo'<br />';echo'<br />';
    }

    template_show_list('kb_know');
    if(!empty($modSettings['kb_spinfo'])){
	echo '<br class="clear" />
			<div class="cat_bar" style="width: 390px;"><h3 class="catbg">
			'.$txt['kb_perminfo'].'</h3></div>

			<div style="width: 400px;" class="windowbg2">
			<span class="topslice"><span></span></span>
			<div class="content">
			<div class="floatleft">
			', (KBAllowedto($_GET['cat'],'addarticle')) ? ''.$txt['kb_perminfo1'].'' : ''.$txt['kb_perminfo2'].'', '<br />
			', (allowedTo('com_kb')) ? ''.$txt['kb_perminfo3'].'' : ''.$txt['kb_perminfo4'].'', '<br />
			', (allowedTo('rate_kb')) ? ''.$txt['kb_perminfo6'].'' : ''.$txt['kb_perminfo5'].'', '<br />
			', (KBAllowedto($_GET['cat'],'editarticle')) ? ''.$txt['kb_perminfo7'].'' : ''.$txt['kb_perminfo8'].'', '<br />
			', (KBAllowedto($_GET['cat'],'editanyarticle')) ? ''.$txt['kb_perminfo9'].'' : ''.$txt['kb_perminfo10'].'', '<br />
			', (KBAllowedto($_GET['cat'],'delarticle')) ? ''.$txt['kb_perminfo11'].'' : ''.$txt['kb_perminfo12'].'', '<br />
			', (KBAllowedto($_GET['cat'],'delanyarticle')) ? ''.$txt['kb_perminfo13'].'' : ''.$txt['kb_perminfo14'].'', '<br /></div>
			<div class="floatright">
			<a href="', $scripturl, '?action=help;area=bbcode">'.$txt['kb_perminfo17'].'</a> ', ($modSettings['enableBBC']) ? '<strong>'.$txt['kb_perminfo15'].'</strong>' : '<strong>'.$txt['kb_perminfo16'].'</strong>', '<br />
			<a href="', $scripturl, '?action=help;area=smileys">'.$txt['kb_perminfo18'].'</a> ', ($modSettings['enableBBC']) ? '<strong>'.$txt['kb_perminfo15'].'</strong>' : '<strong>'.$txt['kb_perminfo16'].'</strong>', '<br />
			<a href="', $scripturl, '?action=help;area=bbcode">'.$txt['kb_perminfo19'].'</a> ', (!in_array('img', (empty($modSettings['disabledBBC']) ? array() : explode(',', $modSettings['disabledBBC'])))) ? '<strong>'.$txt['kb_perminfo15'].'</strong>' : '<strong>'.$txt['kb_perminfo16'].'</strong>', '<br />
		    '.$txt['kb_perminfo20'].' ', ($modSettings['enablePostHTML']) ? '<strong>'.$txt['kb_perminfo15'].'</strong>' : '<strong>'.$txt['kb_perminfo16'].'</strong>', '<br /></div>
			<br class="clear" /><br class="clear" />
			</div>
			<span class="botslice"><span></span></span>
			</div>
		    <br class="clear" /><br class="clear" />';
	}
	if(!empty($modSettings['kb_salegend'])){
    echo'
	<div class="tborder" id="topic_icons">
		<div class="description">
			<p class="floatleft smalltext">
				<img src="'.$settings['images_url'].'/topic/normal_post.gif" alt="" align="middle" /> '.$txt['kb_iconmen'].'<br />
				<img src="'.$settings['images_url'].'/topic/hot_post.gif" alt="" align="middle" /> '.$txt['kb_iconmen1'].'<br />
				<img src="'.$settings['images_url'].'/topic/veryhot_post.gif" alt="" align="middle" /> '.$txt['kb_iconmen2'].'
			</p>
			<br class="clear" />
		</div>
	</div>';
	}

  template_kb_copy();
}

function template_kb_addknow() {
  global $scripturl, $txt, $sc, $modSettings, $settings, $context;

	echo '<div class="error" id="ajax_in_progress" style="display:none;">Fetching Preview......</div>
		<div id="results"><div id="preview_section"', (isset($context['preview_message']) ? '' : ' style="display: none;"'), '>
			<div class="cat_bar">
				<h3 class="catbg">
					<span id="preview_subject">', (empty($context['preview_subject']) ? '' : $context['preview_subject']), '</span>
				</h3>
				</div>

			<div class="windowbg">
				<span class="topslice"><span></span></span>
				<div class="content">
						<div class="post" id="preview_body">

							', (empty($context['preview_message']) ? '<br />' : $context['preview_message']), '
						</div>
					</div>
					<span class="botslice"><span></span></span>
				</div><br /></div></div>';

	 echo '<form id=myarform action="', $scripturl, '?action=kb;area=addknow;save;cat='.(!empty($_GET['cat']) ? $_GET['cat'] : '0').'"  enctype="multipart/form-data" method="post" accept-charset="', $context['character_set'], '" onsubmit="submitonce(this);smc_saveEntities(\'postmodify\', [\'title\', \'description\']);">';
        echo'<div class="cat_bar">
			<h3 class="catbg">'.$txt['knowledgebasecataddedit1'].'</h3>
		</div><span class="upperframe"><span></span></span>
			<div class="roundframe">';
			if(allowedTo('manage_kb') && !empty($modSettings['kb_efeaturedarticle'])){
			echo'<dl class="register_form" >
			   <dt>
				    <strong>'.$txt['kb_featuredarticle1'].':</strong>
			   </dt>
			   <dd>
					<input type="checkbox" name="featured" value="" />
			   </dd>
			</dl>';
			}
			echo'<dl class="register_form" >
			   <dt>
				    <strong>'.$txt['kb_source'].':</strong>
			   </dt>
			   <dd>
					<input type="text" size="40" name="source" value="',!empty($_POST['source']) ? $_POST['source'] : '','" />
			   </dd>
			</dl>';
			echo'<dl class="register_form">
			   <dt>
				    <strong>'.$txt['knowledgebase_cat1'].':</strong>
			   </dt>
			   <dd>
			<select name="cat" id="cat">';
		    foreach ($context['knowcat'] as $i => $category){
			if ($category['view'] != '0' && KBAllowedto($category['kbid'],'addarticle')){
			echo '
				<option value="', $category['kbid'], '"', ($_GET['cat'] == $category['kbid'] ? ' selected="selected"' : ''), '>', $category['name'], '</option>';
		    }
			}
			echo '
			</select></dd>
			</dl>';

			echo'<dl class="register_form" >
			   <dt>
				    <strong>'.$txt['knowledgebasetitle'].':</strong>
			   </dt>
			   <dd>
					<input type="text" size="40" name="title" value="',!empty($_POST['title']) ? $_POST['title'] : '','" />
			   </dd>
			</dl>';

					if ($context['show_bbc']){
							echo '<div id="bbcBox_message"></div>';}

						if (!empty($context['smileys']['postform']) || !empty($context['smileys']['popup'])){
							echo '<div id="smileyBox_message"></div>';}

						echo template_control_richedit($context['post_box_name'], 'smileyBox_message', 'bbcBox_message');
			   if ($modSettings['kb_enable_attachment']){
$modSettings['kb_num_attachment'] = !empty($modSettings['kb_num_attachment']) ? $modSettings['kb_num_attachment'] : '100';

			  echo' <br /><img src="', $settings['images_url'], '/icons/clip.gif" alt="-" /> <a href="javascript:void(0)" onclick="javascript:kbsearch_showhide(\'attachkb\');"><strong>'.$txt['kb_attach7'].'</strong></a>';
			   echo '<div class="attachkb" id="attachkb" style="display:none;">
			   <br /><hr />
			   <strong>'.$txt['kb_attach1'].':</strong>
			   <div class="smalltext">';
		            if(!empty($modSettings['kb_mfile_attachment']) && !empty($modSettings['kb_num_attachment'])){
							echo''.$txt['kb_attach2'].':  ', $modSettings['kb_num_attachment'], ' '.$txt['kb_attach3'].' ' . round($modSettings['kb_mfile_attachment'] / 1024, 2) . 'KB
							';
							}
							if (!empty($modSettings['kb_attachmentExtensions'])){
			echo '
							<br />'.$txt['kb_attach4'].': '.strtr($modSettings['kb_attachmentCheckExtensions'], array(',' => ', ')).'<br />';
							}echo'
						</div><hr /><br />

						<div class="smalltext">
							<input type="file" size="60" name="attachment[]" id="attachment1" class="input_file" /> (<a href="javascript:void(0);" onclick="cleanFileInput(\'attachment1\');">', $txt['clean_attach'], '</a>)';';
		';// Show more boxes only if they aren\'t approaching their limit.
		if ($modSettings['kb_num_attachment'] > 1){
			echo '
							<script type="text/javascript"><!-- // --><![CDATA[
								var allowed_attachments = ', $modSettings['kb_num_attachment'], ';
								var current_attachment = 1;

								function addAttachment()
								{
									allowed_attachments = allowed_attachments - 1;
									current_attachment = current_attachment + 1;
									if (allowed_attachments <= 0)
										return alert("', $txt['more_attachments_error'], '");

									setOuterHTML(document.getElementById("moreAttachments"), \'<div class="smalltext"><input type="file" size="60" name="attachment[]" id="attachment\' + current_attachment + \'" class="input_file" /> (<a href="javascript:void(0);" onclick="cleanFileInput(\\\'attachment\' + current_attachment + \'\\\');">', $txt['clean_attach'], '</a>)\' + \'</div><div class="smalltext" id="moreAttachments"><a href="#" onclick="addAttachment(); return false;">(', $txt['more_attachments'], ')<\' + \'/a><\' + \'/div>\');

									return true;
								}
							// ]]></script>
						</div><div class="smalltext" id="moreAttachments"><a href="#" onclick="addAttachment(); return false;">(', $txt['more_attachments'], ')</a></div>';
				}echo'</div>';}
				echo'<div id="confirm_buttons">
					<input type="hidden" id="', $context['session_var'], '" name="', $context['session_var'], '" value="', $context['session_id'], '" />
					<input type="submit" name="send" id="sendbutton" class="button_submit" value="'.$txt['post'].'" />
					<input type="submit" name="preview" id="previewbutton" class="button_submit" value="', $txt['preview'], '" />
				</div>
			</div>
			<span class="lowerframe"><span></span></span>';

	echo'</form>';

	template_kb_copy();

}

function template_kb_catlist() {
  global $scripturl, $txt, $context;

  if(isset($_GET['deleted'])){echo'<div style="text-align: center;" class="information"><strong>'.$txt['knowledgebasedeletecat'].'</strong></div>';}
  if(isset($_GET['edited'])){echo'<div style="text-align: center;" class="information"><strong>'.$txt['knowledgebasedeleteca1'].'</strong></div>';}

   if(isset($_GET['edit'])){

    foreach($context['know'] as $knowl){

	    echo'
		<form action="', $scripturl, '?action=kb;area=listcat;update='.$knowl['kbid'].'" method="post" accept-charset="', $context['character_set'], '" >';
            echo' <div class="cat_bar">
			<h3 class="catbg">'.$txt['knowledgebasecataddedit'].' - '.$context['know'][0]['title'].'</h3>
		 </div>
			<span class="upperframe"><span></span></span>
			<div class="roundframe">
			<dl class="register_form" >
			<dt>
			   <strong>'.$txt['kb_parent'].'</strong>
			</dt><dd>
			<select name="cat">';
			echo'<option value="0">'.$txt['knowledgebasenone'].'</option>';
		        foreach ($context['knowcat'] as $i => $category){
			        if ($category['view'] != '0' && KBAllowedto($category['kbid'],'addarticle')){
			           echo '
				      <option value="' , $category['kbid']  , '" ' , (($knowl['id_parent']  == $category['kbid']) ? ' selected="selected"' : '') ,'>', $category['name'], '</option>';
		            }
			    }
			echo '
			</select></dd>
			</dl>
			<dl class="register_form" >
			    <dt>
				    <strong>'.$txt['knowledgebasetitle'].'</strong>
			    </dt>
			    <dd>
					<input type="text" name="name" value="'.$knowl['title'].'" />
			    </dd>
			</dl>

			<dl class="register_form" >
			    <dt>
				    <strong>'.$txt['knowledgebasedescrip'].'</strong>
			    </dt>
			    <dd>
					<textarea rows="6" name="description" cols="30">', $knowl['content'], '</textarea>
			    </dd>
			</dl>

				<dl class="register_form" >
			    <dt>
				    <strong>'.$txt['kbcimgurl'].'</strong>
			    </dt>
			    <dd>
					<input type="text" name="image" size="64" maxlength="100" value="', $knowl['image'], '" />
			    </dd>
			</dl>
			<br />

			<div style="text-align: center;">
			   <input type="submit" name="submit" value="'.$txt['knowledgebasesubmit'].'" class="button_submit" />
			   <input type="hidden" id="', $context['session_var'], '" name="', $context['session_var'], '" value="', $context['session_id'], '" />
			</div>

			</div>
			<span class="lowerframe"><span></span></span>';

	    echo'
		</form>';
	}
  }
  else{

    template_show_list('kb_list');
  }
  template_kb_copy();
}

function template_kb_catadd() {
  global $context, $txt, $scripturl;

  if(isset($_GET['added'])){echo'<div style="text-align: center;" class="information"><strong>'.$txt['knowledgebasedeletecat2'].'</strong></div>';}
    echo'
	<form action="', $scripturl, '?action=kb;area=catadd;save" method="post" accept-charset="', $context['character_set'], '" >';

        echo'<div class="cat_bar">
			<h3 class="catbg">'.$txt['knowledgebasecatadd'].'</h3>
		 </div>
		<span class="upperframe"><span></span></span>
			<div class="roundframe">

			<dl class="register_form" >
			<dt>
			   <strong>'.$txt['kb_parent'].'</strong>
			</dt><dd>
			<select name="cat">';
			echo'<option value="0">'.$txt['knowledgebasenone'].'</option>';
		        foreach ($context['knowcat'] as $i => $category){
			        if ($category['view'] != '0' && KBAllowedto($category['kbid'],'addarticle')){
			            echo '
				        <option value="' , $category['kbid']  , '">', $category['name'], '</option>';
		            }
			    }
			echo '
			</select></dd>
			</dl>

			<dl class="register_form" >
			    <dt>
				    <strong>'.$txt['knowledgebasetitle'].'</strong>
			    </dt>
			    <dd>
					<input type="text" name="title" value="" />
			    </dd>
			</dl>

			<dl class="register_form" >
			    <dt>
				    <strong>'.$txt['knowledgebasedescrip'].'</strong>
			    </dt>
			    <dd>
					<textarea rows="6" name="description" cols="30"></textarea>
			    </dd>
			</dl>
			<dl class="register_form" >
			    <dt>
				    <strong>'.$txt['kbcimgurl'].'</strong>
			    </dt>
			    <dd>
					<input type="text" name="image" size="64" maxlength="100" value="" />
			    </dd>
			</dl>
			<br />

			<div style="text-align: center;">
			    <input type="submit" name="submit" value="'.$txt['knowledgebasesubmit'].'" class="button_submit" />
			    <input type="hidden" id="', $context['session_var'], '" name="', $context['session_var'], '" value="', $context['session_id'], '" />
			</div>

			</div>
			<span class="lowerframe"><span></span></span>';

	    echo'
	</form>';
	template_kb_copy();

}

function template_kb_perm() {
global $context, $cname, $sc, $txt, $scripturl;

echo'<form action="', $scripturl, '?action=kb;area=permcat;save='.$_GET['perm'].'" method="post" accept-charset="', $context['character_set'], '" >
        <div class="cat_bar">
			<h3 class="catbg">'.$txt['kb_catperm7'].' - '.$cname.'</h3>
		</div>
            <span class="upperframe"><span></span></span>
			<div class="roundframe centertext">
               <strong>'.$txt['kb_perm3'].'</strong><br /><br /> <select name="groupname">
			  	    <option value="-1">'.$txt['kb_catperm3'].'</option>
					<option value="0">'.$txt['kb_catperm2'].'</option>';

						foreach ($context['groups'] as $group)
							echo '<option value="', $group['ID_GROUP'], '">', $group['group_name'], '</option>';


		   echo '</select>';

		   echo'<br /><br />
		    <table class="table_grid" cellspacing="0" width="50%" align="center">
			<thead>
				<tr class="catbg">
					<th scope="col" class="first_th">'.$txt['kb_perm1'].'</th>
					<th scope="col" class="last_th">'.$txt['kb_perm2'].'</th>
				</tr>
			</thead>
			<tbody>
				<tr class="windowbg" id="list_kb_listcat_111">
					<td style="width: 30%; text-align: left;">'.$txt['kb_rlistnor44'].'</td>
					<td style="width: 5%; text-align: center;"><input type="checkbox" name="view" /></td>
				</tr>
				<tr class="windowbg2" id="list_kb_listcat_112">
					<td style="width: 30%; text-align: left;">'.$txt['permissionname_add_knowledge'].'</td>
					<td style="width: 5%; text-align: center;"><input type="checkbox" name="addarticle" /></td>
				</tr>
				<tr class="windowbg" id="list_kb_listcat_113">
					<td style="width: 30%; text-align: left;">'.$txt['kb_editany'].'</td>
					<td style="width: 5%; text-align: center;"><input type="checkbox" name="editanyarticle" /></td>
				</tr>
				<tr class="windowbg2" id="list_kb_listcat_114">
					<td style="width: 30%; text-align: left;">'.$txt['kb_editown'].'</td>
					<td style="width: 5%; text-align: center;"><input type="checkbox" name="editarticle" /></td>
				</tr>
				<tr class="windowbg" id="list_kb_listcat_115">
					<td style="width: 30%; text-align: left;">'.$txt['kb_delany'].'</td>
					<td style="width: 5%; text-align: center;"><input type="checkbox" name="delanyarticle" /></td>
				</tr>
				<tr class="windowbg2" id="list_kb_listcat_116">
					<td style="width: 30%; text-align: left;">'.$txt['kb_delown'].'</td>
					<td style="width: 5%; text-align: center;"><input type="checkbox" name="delarticle" /></td>
				</tr>
			</tbody>
			</table> ';
					echo'
				<br /><br /><input type="submit" name="send" class="button_submit" value="'.$txt['kb_catperm6'] .'" />
				<input type="hidden" name="sc" value="', $sc, '" />

					</div><span class="lowerframe"><span></span></span>
			</form>';

		if(!empty($context['kb_membergroup']) || !empty($context['kb_guest']) || !empty($context['reg_reggroup'])){

			echo'<br />
		   <table class="table_grid" cellspacing="0" width="100%" align="center">
			<thead>
				<tr class="catbg">
					<th scope="col" class="first_th">'.$txt['kb_perm3'].'</th>
					<th scope="col">'.$txt['kb_rlistnor44'].'</th>
					<th scope="col">'.$txt['kb_editany'].'</th>
					<th scope="col">'.$txt['kb_editown'].'</th>
					<th scope="col">'.$txt['kb_delany'].'</th>
					<th scope="col">'.$txt['kb_delown'].'</th>
					<th scope="col" class="last_th">'.$txt['permissionname_add_knowledge'].'</th>
				</tr>
			</thead>
			<tbody> ';

			$windowclass = 'windowbg';

			foreach($context['kb_membergroup'] as $row){

				$windowclass = ($windowclass == 'windowbg') ? 'windowbg2' : 'windowbg';
				echo'<tr class="'.$windowclass.'" id="list_kb_listcat_1331">
				    <td style="width: 10%; text-align: center;">'.$row['group_name'].'</td>
					<td style="width: 10%; text-align: center;">' , ($row['view'] ? '<span style="color: #008000;">'.$txt['kb_catperm4'].'</span>' : '<span style="color: #f00;">'.$txt['kb_catperm5'].'</span>') , '</td>
					<td style="width: 10%; text-align: center;">' , ($row['editanyarticle'] ? '<span style="color: #008000;">'.$txt['kb_catperm4'].'</span>' : '<span style="color: #f00;">'.$txt['kb_catperm5'].'</span>') , '</td>
					<td style="width: 10%; text-align: center;">' , ($row['editarticle'] ? '<span style="color: #008000;">'.$txt['kb_catperm4'].'</span>' : '<span style="color: #f00;">'.$txt['kb_catperm5'].'</span>') , '</td>
					<td style="width: 10%; text-align: center;">' , ($row['delanyarticle'] ? '<span style="color: #008000;">'.$txt['kb_catperm4'].'</span>' : '<span style="color: #f00;">'.$txt['kb_catperm5'].'</span>') , '</td>
					<td style="width: 10%; text-align: center;">' , ($row['delarticle'] ? '<span style="color: #008000;">'.$txt['kb_catperm4'].'</span>' : '<span style="color: #f00;">'.$txt['kb_catperm5'].'</span>') , '</td>
					<td style="width: 10%; text-align: center;">' , ($row['addarticle'] ? '<span style="color: #008000;">'.$txt['kb_catperm4'].'</span>' : '<span style="color: #f00;">'.$txt['kb_catperm5'].'</span>') , '</td>
				</tr> ';
			}

			foreach($context['kb_guest'] as $row){

				$windowclass = ($windowclass == 'windowbg') ? 'windowbg2' : 'windowbg';
				echo'<tr class="'.$windowclass.'" id="list_kb_listcat_1332">
				    <td style="width: 10%; text-align: center;">'.$txt['kb_catperm3'].'</td>
					<td style="width: 10%; text-align: center;">' , ($row['view'] ? '<span style="color: #008000;">'.$txt['kb_catperm4'].'</span>' : '<span style="color: #f00;">'.$txt['kb_catperm5'].'</span>') , '</td>
					<td style="width: 10%; text-align: center;">' , ($row['editanyarticle'] ? '<span style="color: #008000;">'.$txt['kb_catperm4'].'</span>' : '<span style="color: #f00;">'.$txt['kb_catperm5'].'</span>') , '</td>
					<td style="width: 10%; text-align: center;">' , ($row['editarticle'] ? '<span style="color: #008000;">'.$txt['kb_catperm4'].'</span>' : '<span style="color: #f00;">'.$txt['kb_catperm5'].'</span>') , '</td>
					<td style="width: 10%; text-align: center;">' , ($row['delanyarticle'] ? '<span style="color: #008000;">'.$txt['kb_catperm4'].'</span>' : '<span style="color: #f00;">'.$txt['kb_catperm5'].'</span>') , '</td>
					<td style="width: 10%; text-align: center;">' , ($row['delarticle'] ? '<span style="color: #008000;">'.$txt['kb_catperm4'].'</span>' : '<span style="color: #f00;">'.$txt['kb_catperm5'].'</span>') , '</td>
					<td style="width: 10%; text-align: center;">' , ($row['addarticle'] ? '<span style="color: #008000;">'.$txt['kb_catperm4'].'</span>' : '<span style="color: #f00;">'.$txt['kb_catperm5'].'</span>') , '</td>
				</tr> ';
			}

			foreach($context['reg_reggroup'] as $row){

				$windowclass = ($windowclass == 'windowbg') ? 'windowbg2' : 'windowbg';
				echo'<tr class="'.$windowclass.'" id="list_kb_listcat_1333">
				    <td style="width: 10%; text-align: center;">'.$txt['kb_catperm2'].'</td>
					<td style="width: 10%; text-align: center;">' , ($row['view'] ? '<span style="color: #008000;">'.$txt['kb_catperm4'].'</span>' : '<span style="color: #f00;">'.$txt['kb_catperm5'].'</span>') , '</td>
					<td style="width: 10%; text-align: center;">' , ($row['editanyarticle'] ? '<span style="color: #008000;">'.$txt['kb_catperm4'].'</span>' : '<span style="color: #f00;">'.$txt['kb_catperm5'].'</span>') , '</td>
					<td style="width: 10%; text-align: center;">' , ($row['editarticle'] ? '<span style="color: #008000;">'.$txt['kb_catperm4'].'</span>' : '<span style="color: #f00;">'.$txt['kb_catperm5'].'</span>') , '</td>
					<td style="width: 10%; text-align: center;">' , ($row['delanyarticle'] ? '<span style="color: #008000;">'.$txt['kb_catperm4'].'</span>' : '<span style="color: #f00;">'.$txt['kb_catperm5'].'</span>') , '</td>
					<td style="width: 10%; text-align: center;">' , ($row['delarticle'] ? '<span style="color: #008000;">'.$txt['kb_catperm4'].'</span>' : '<span style="color: #f00;">'.$txt['kb_catperm5'].'</span>') , '</td>
					<td style="width: 10%; text-align: center;">' , ($row['addarticle'] ? '<span style="color: #008000;">'.$txt['kb_catperm4'].'</span>' : '<span style="color: #f00;">'.$txt['kb_catperm5'].'</span>') , '</td>
				</tr> ';
			}

			echo'</tbody>
			</table>';
		}
		else{
		    echo'
			    <span class="upperframe"><span></span></span>
			        <div class="roundframe centertext">';

						echo $txt['kb_catperm1'];

		        echo'
				    </div>
			    <span class="lowerframe"><span></span></span>';
		}


	template_kb_copy();
}

function template_kb_knowcont() {
   global $scripturl, $txt, $settings, $memberContext, $total_rates, $sc, $user_info, $modSettings, $context;

	$max_num_stars = 5;

	if(isset($_GET['reported']))
	   echo'<div style="text-align: center;" class="information"><strong>'.$txt['kb_reports24'].'</strong></div>';

    if($context['know'][0]['approved'] == 0)
       echo'<div style="text-align: center;" class="errorbox"><strong>'.$txt['kb_appp24'].'</strong></div>';

	if(isset($_GET['yesa']))
	   echo'<div style="text-align: center;" class="information"><strong>'. $txt['kb_appp244'].'</strong></div>';

	if(isset($_GET['noa']))
	   echo'<div style="text-align: center;" class="errorbox"><strong>'.$txt['kb_appp2444'].'</strong></div>';

	foreach($context['know'] as $knowl){

	   if($knowl['approved'] == 1)
	      $approved = '<span style="color: #008000;">'.$txt['kb_alist6'].'</span>';
	   else
	      $approved = '<span style="color: #f00;">'.$txt['kb_alist7'].'</span>';

       echo'
	        <div class="cat_bar">
				<h3 class="catbg">', $knowl['title'], '</h3>
			</div>';

		echo'
		    <span class="upperframe"><span></span></span>
			<div class="roundframe">';

			$float = empty($modSettings['kb_article_detaildisplay']) ? 'right' : 'left';
			$pos = $float == 'left' ? 'right' : 'left';

		echo'
		    <div style="position: relative; '.$pos.':10px; padding-right:10px; padding-left:10px" class="float'.$float.'">
			    <div class="information">
                    <strong>'.$txt['kb_pinfi9'].'</strong><br /><br />';

						loadMemberData($knowl['id_member']);
						loadMemberContext($knowl['id_member']);

						if($memberContext[$knowl['id_member']]['avatar']['href']){
				           echo' <img class="resizeav" border="0" src="'.$memberContext[$knowl['id_member']]['avatar']['href'].'" alt="" />';
				        }
				        else{echo' <img border="0" src="',$settings['images_url'],'/icons/online.gif" width="50" height="50" alt="" />';}

						echo'
						     <br /><br />
	                <strong>'.$txt['kb_pinfi8'].':</strong>&nbsp;';

						if($knowl['id_member'] != 0){
			                echo  KB_profileLink($knowl['real_name'], $knowl['id_member']);
			            }
			            else{
			                echo $txt['guest_title'];
			            }

				        echo'
						    <br />
                    <strong>'.$txt['kb_pinfi7'].':</strong>&nbsp;'.$knowl['kbnid'].'';

					    if(!empty($modSettings['kb_show_view'])){
				            echo'<br /><strong>'.$txt['kb_pinfi6'].':</strong>&nbsp;'.$knowl['views'].'';
				        }

				        if(allowedTo('rate_kb') && $modSettings['kb_eratings']){

						echo'<br />
					        <strong>'.$txt['kb_pinfi3'].':</strong>
				            <a href="' . $scripturl . '?action=kb;area=rate;value=1;kbnid='.$knowl['kbnid'].';sesc='.$sc.'">
						    <img src="', $settings['default_images_url'], '/sort_up.gif" title="'.$txt['kb_pinfi4'].'" alt="'.$txt['kb_pinfi4'].'" border="0" /></a>&nbsp;&nbsp;
						    <a href="' . $scripturl . '?action=kb;area=rate;value=0;kbnid='.$knowl['kbnid'].';sesc='.$sc.'">
						    <img src="', $settings['default_images_url'], '/sort_down.gif" title="'.$txt['kb_pinfi5'].'" alt="'.$txt['kb_pinfi5'].'" border="0" /></a><br /> ';
				        }
						else{echo'<br />';}

				        if(!empty($modSettings['kb_eratings'])){
				            echo'<strong>'.$txt['kb_pinfi2'].':</strong>&nbsp;'. KB_Stars_Precent($knowl['rate']). '<br />';
				        }

				        echo'
						    <strong>'.$txt['kb_pinfi1'].': </strong>
							<span class="smalltext">'.$knowl['date'].'</span>
				            <br /><strong>'.$txt['kb_alist5'].':</strong>&nbsp;'.$approved.'<br /><br />
		            </div>
			</div>';

			if(!empty($modSettings['kb_social'])){

				echo'
				    <div style="position: relative; left:10px;">
				        <a href="https://twitter.com/share" class="twitter-share-button" data-count="horizontal">'.$txt['kb_tweet'].'</a><script type="text/javascript" src="https://platform.twitter.com/widgets.js"></script>
				        <iframe src="https://www.facebook.com/plugins/like.php?href=' , $scripturl , '?action=kb;area=article;cont='.$_GET['cont'].'&amp;layout=button_count&amp;show_faces=true&amp;width=85&amp;action=like&amp;font&amp;colorscheme=light&amp;height=20" scrolling="no" frameborder="0" style="border:none; overflow:hidden; width:85px; height:20px;" allowTransparency="true"></iframe>
                    </div>';

					if(!empty($knowl['source'])){
					    echo'<br /> <strong>'.$txt['kb_osource'].'</strong>:  ', $knowl['source'], '';
					}

				echo'<hr />';
			}
			else{

			    if(!empty($knowl['source'])){
				    echo' <strong>'.$txt['kb_osource'].'</strong>:  ', $knowl['source'], '
				    <hr />';
				}
			}

	    echo'
			<div style="padding-right:10px; padding-top:10px; padding-left:10px">

			 ', $knowl['content'], '

			</div>
		<br class="clear" /><br class="clear" />
		</div><span class="lowerframe"><span></span></span>';
	}

	if(!empty($context['kbimg']) && $modSettings['kb_enable_attachment']){
		echo'<br />
		    <span class="upperframe"><span></span></span>
			    <div class="roundframe">';
			    echo'
				<div class="highslide-gallery">';

					foreach($context['kbimg'] as $img){

					if(!empty($modSettings['kb_enablehs_attach'])){
						echo'

                            <a id="thumb'.$img['id_article'].'" href="'.$modSettings['kb_url_attachment'].''.$img['filename'].'" class="highslide" onclick="return hs.expand(this, { slideshowGroup: 2, thumbnailId: \'thumb'.$img['id_article'].'\' } )">
	                            <img class="resizeme" src="'.$modSettings['kb_url_attachment'].''.$img['filename'].'" alt="'.$img['filename'].'" title="'.$img['filename'].'" />
							</a>';


						echo'
                            <div class="highslide-caption">
	                            '.$img['filename'].'
                             </div>';
						}
						else{
						echo'<a href="'.$modSettings['kb_url_attachment'].''.$img['filename'].'" rel="lightbox[roadtrip]" title="'.$img['filename'].'">
			            <img class="resizeme" src="'.$modSettings['kb_url_attachment'].''.$img['filename'].'" alt="'.$img['filename'].'" /></a> ';
						}
					}
				echo'</div>';
			echo'
			    </div>
	       <span class="lowerframe"><span></span></span>';
	}

	if(!empty($modSettings['kb_ecom'])){

	 echo'<br /> <div class="cat_bar">
				<h3 class="catbg">'.$txt['kb_ecom2'].''; if(allowedTo('com_kb')){echo'&nbsp;-&nbsp;<a href="javascript:void(0)" onclick="javascript:kbsearch_showhide(\'commentkb\');">'.$txt['kb_articlwnot_add_com'].'</a>';}echo'</h3>
			</div>';

			    echo'<div align="center"><div class="error" id="com_done" style="display:none;"><strong>'.$txt['kb_com_sub_compleat'].'</strong></div></div>';

		    echo'<div class="commentkb" id="commentkb" style="display:none;"> ';
	echo'<form id="mykbform" action="', $scripturl, '?action=kb;area=kb;area=article;comment;arid='.$_GET['cont'].';cont='.$_GET['cont'].'" method="post" accept-charset="', $context['character_set'], '" onsubmit="submitonce(this);smc_saveEntities(\'postmodify\', [\'title\', \'description\']);">
	         <span class="upperframe"><span></span></span>
			<div class="roundframe centertext"><br class="clear" />
			<div class="error" id="ajax_in_progress" style="display:none;">'.$txt['kb_loading'].'</div>';
			if(!allowedTo('auto_approvecom_kb')){
			    echo'<div class="error"><strong>'.$txt['kb_ecomauto'].'</strong></div><br />';
			}
			if ($context['show_bbc'])
							echo '<div id="bbcBox_message"></div>';

						if (!empty($context['smileys']['postform']) || !empty($context['smileys']['popup']))
							echo '<div id="smileyBox_message"></div>';

						echo template_control_richedit($context['post_box_name'], 'smileyBox_message', 'bbcBox_message');
						if($user_info['is_guest']){
						echo template_control_verification($context['visual_verification_id'], 'all');
						}
	     echo'<br /><input type="submit" name="send" class="button_submit" value="'.$txt['kb_catperm6'] .'" />
					<input type="hidden" name="', $context['session_var'], '" value="', $context['session_id'], '"/>
	  </div>
	       <span class="lowerframe"><span></span></span><br />
        </form></div>';
	if(!empty($context['kbcom'])){
		echo''.$txt['pages'].': '.$context['page_index'].'';
	}

	$windowclass =false;

	echo'<div id="results">';
	foreach($context['kbcom'] as $com){
		$windowclass = ($windowclass == 'windowbg') ? 'windowbg2' : 'windowbg';

	echo'<div class="'.$windowclass.'">
				<span class="topslice"><span></span></span>';
			echo'<div class="poster">';
			echo'<ul class="reset">
			<li class="title">';

			if($com['id_member'] != 0){
			   echo  KB_profileLink($com['real_name'], $com['id_member']);

			}
			else{
			    echo $txt['guest_title'];
			}


						loadMemberData($com['id_member']);
						loadMemberContext($com['id_member']);

						if($memberContext[$com['id_member']]['avatar']['href']){
				           echo' <br /><br /><img class="resizeav" border="0" src="'.$memberContext[$com['id_member']]['avatar']['href'].'" alt="" />';
				        }
				        else{echo' <br /><br /><img border="0" src="',$settings['images_url'],'/icons/online.gif" width="50" height="50" alt="" />';}


			echo'
			</li>
			</ul>';
			echo'</div>';
			echo'<div class="postarea">
					<div class="flow_hidden">
						<div class="keyinfo">
						<div class="messageicon">
							<img src="', $settings['images_url']. '/post/xx.gif" alt="" border="0" />
						</div>
						<h5>'.$txt['kb_re'].': ', $context['know'][0]['title'], '</h5>
							<div class="smalltext">&#171; <strong>', $txt['on'], ':</strong> ',$com['date'], ' &#187;</div>
						</div>';
						if($com['id_member'] == $user_info['id'] && $context['user']['is_logged'] && allowedTo('comdel_kb') || allowedTo('manage_kb')){
						echo'<ul class="reset smalltext quickbuttons">

							   <li class="remove_button">
							   		<a href="', $scripturl, '?action=kb;area=kb;area=article;commentdel;arid='.$com['id'].';cont='.$_GET['cont'].'" onclick="return confirm(\''.$txt['knowledgebaseeditedsure101'].'\');">
							   			', $txt['remove'], '
							   		</a>
							   	</li>
							</ul>';
						}
					echo'
						</div>
						<div class="post">
					<div class="inner">
						',$com['comment'], '
					</div>	</div>';
					echo'<br class="clear" /></div>
					<span class="botslice"><span></span></span></div>';

	}
	echo'</div>';
	if(!empty($context['kbcom'])){
		echo''.$txt['pages'].': '.$context['page_index'].'';
	}

	}
	template_kb_copy();
}

function template_kbmanage(){
    global $context, $total_report, $total_approvecom, $total_approve, $scripturl, $txt;
    echo'<script type="text/javascript">
<!--

function confirmSubmit()
{
var agree=confirm("'.$txt['kb_sure_contjs'] .'");
if (agree)
	return true ;
else
	return false ;
}
// -->
</script>';
	echo'
	<div class="cat_bar">
		<h3 class="catbg">'.$txt['kb_manage1'].'</h3>
	</div>';

   echo'
	<div class="information centertext">
	    '.$txt['kb_manage2'].' <strong>'.$total_approve.'</strong> '.$txt['kb_manage3'].'
		<br />'.$txt['kb_manage2'].' <strong>'.$total_approvecom.'</strong> '.$txt['kb_manage4'].'
		<br />'.$txt['kb_manage2'].' <strong>'.$total_report.'</strong> '.$txt['kb_manage5'].'
		<br /><br /><a href="'.$scripturl.'?action=kb;area=catadd">['.$txt['knowledgebasecatadd'].']</a> | <a href="'.$scripturl.'?action=kb;area=listcat">['.$txt['knowledgebasecataddedit'].']</a> | <a href="'.$scripturl.'?action=kb;area=addknow;cat=0">['.$txt['knowledgebasecataddedit1'].']</a>
	</div>';

    template_show_list('kb_know_reports');
	echo'<br />';
	template_show_list('kb_knowcomappr');
	echo'<br />';
	template_show_list('kb_know');

	template_kb_copy();
}
function template_kb_print_above(){
	global $context, $settings, $txt;

	echo '<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Transitional//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-transitional.dtd">
<html xmlns="http://www.w3.org/1999/xhtml"', $context['right_to_left'] ? ' dir="rtl"' : '', '>
	<head>
		<meta http-equiv="Content-Type" content="text/html; charset=', $context['character_set'], '" />
		<title>', $txt['print_page'], ' - ', $context['page_title'] , '</title>
		<style type="text/css">
			body
			{
				color: black;
				background-color: white;
				padding: 12px 5% 10px 5%;
			}
			body, td, .normaltext
			{
				font-family: Verdana, arial, helvetica, serif;
				font-size: small;
			}
			*, a:link, a:visited, a:hover, a:active
			{
				color: black !important;
			}
			table
			{
				empty-cells: show;
			}
			.code
			{
				font-size: x-small;
				font-family: monospace;
				border: 1px solid black;
				margin: 1px;
				padding: 1px;
			}
			.quote
			{
				font-size: x-small;
				border: 1px solid black;
				margin: 1px;
				padding: 1px;
			}
			.smalltext, .quoteheader, .codeheader
			{
				font-size: x-small;
			}
			.largetext
			{
				font-size: large;
			}
			hr
			{
				height: 1px;
				border: 0;
				color: black;
				background-color: black;
			}
		</style>';

	if ($context['browser']['needs_size_fix'])
		echo '
		<link rel="stylesheet" type="text/css" href="', $settings['default_theme_url'], '/fonts-compat.css" />';

	echo '
	</head>
	<body>';
}
function template_kb_print_body(){
	global $context;

	echo $context['kbprintbody'];
}

function template_kb_print_below(){
	global $context;

	echo '
		<br /><br />
			<div style="text-align: center;" class="smalltext">', theme_copyright(), '
				<p>' , $context['kbprint'] , '</p>
			</div>
	</body>
</html>';
}

function template_kb_rss_below(){}

function template_kb_copy() {
echo '
 <br />
    <div class="smalltext" style="text-align: center;">SA Knowledge Base<br />
       &copy; 2011 - '.date("Y").' <a href="https://www.smfhacks.com">SMFHacks.com</a>
    </div>';
}
?>