<?php
/*
Zapier for SMF
Version 1.0
by:vbgamer45
https://www.smfhacks.com

License Information:
Linkscontaining//www.smfhacks.com must remain unless
branding free option is purchased.
*/

$txt['zapier_txt_settings'] = 'Settings';
$txt['zapier_txt_settings_desc'] = 'Settings and options for Zapier Settings';
$txt['zapier_txt_savesettings'] = 'Save Settings';
$txt['zapier_admin'] = 'Zapier/API Settings';


$txt['zapier_txt_send_data_to_zapier'] = 'Send Data to Zapier/Pull Data from API';

$txt['zapier_txt_create_data'] = 'Create Data on your forum';



$txt['zapier_txt_step1_login'] = 'Step 1: Login to <a href="https://zapier.com/app/zaps" target="_blank"><strong>Zapier</strong></a> and click the button Make a Zap!';
$txt['zapier_txt_step2_app'] = 'Step 2: Use app Webhooks by Zapier and under Triggers select Retrieve Poll.';
$txt['zapier_txt_step3_app'] = 'Step 3: Choose Forum Action: ';
$txt['zapier_txt_step3_app2'] = 'Choose Board(s): ';
$txt['zapier_txt_step3_app2b'] = 'Choose a Single Board ';
$txt['zapier_txt_step4_app'] = 'Step 4: Use the URL generated below';

$txt['zapier_txt_action_getposts'] = 'Get Posts';
$txt['zapier_txt_action_gettopics'] = 'Get Topics';
$txt['zapier_txt_action_getmembers'] = 'Get Members';

$txt['zapier_txt_action_select_action'] = '(Select an action)';

$txt['zapier_txt_allboards'] = 'All Boards!';


$txt['zapier_txt_create_step2_app'] = 'Step 2: Use app Webhooks by Zapier and under Actions select Post request as a form.';

$txt['zapier_txt_action_createpost'] = 'Create Topic or Add Reply';
$txt['zapier_txt_action_registermember'] = 'Register Member';

$txt['zapier_txt_copy'] = 'Copy Url';

$txt['zapier_txt_payload_topic'] = 'For Zapier: Payload Type use "Form" and for Data supports the following fields:
<br>boardid - Number containing the Board ID to post to. This field is required.
<br>subject - The subject of the message. This field is required.
<br>message - The message to post. This field is required.
<br>memberid - Number containing the Member ID to post as if set to 0 will post as guest. This field is optional. 
<br>topicid -  Number containing the topic id to post the reply. If set 0 this will create a new topic in the board instead of adding a reply. This field is optional."';

$txt['zapier_txt_payload_registermember'] = 'For Zapier: Payload Type use "Form" and for Data supports the following fields:
<br>username - String containing the username of member. This field is required.
<br>email -String containing a valid email address. This field is required.
<br>password - String containing the password if not entered the system will generate and return the password. This field is optional. 
<br>displayname -  String containing the display name. If not entered the username will be used as the display name. This field is optional.
<br>group -  Number containing the membergroup ID in SMF to register the member under This field is optional.';


$txt['txt_zapier_info'] = 'Zapier requires a paid plan in order to use their premium webhook app feature. They offer a 14 day trial to test it as well. You can also use the interfaces before as an API call.';


?>