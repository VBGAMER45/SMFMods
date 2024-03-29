<?php
/*
Simple Audio Video Embedder
Version 2.5
by:vbgamer45
http://www.smfhacks.com

License Information:
Links to http://www.smfhacks.com must remain unless
branding free option is purchased.
*/

if (file_exists(dirname(__FILE__) . '/SSI.php') && !defined('SMF'))
  require_once(dirname(__FILE__) . '/SSI.php');
// Hmm... no SSI.php and no SMF?
elseif (!defined('SMF'))
  die('<b>Error:</b> Cannot install - please verify you put this in the same place as SMF\'s index.php.');


 
// Set up default settings
$smcFunc['db_query']('', "INSERT IGNORE INTO {db_prefix}settings VALUES ('mediapro_default_width', '0')");
$smcFunc['db_query']('', "INSERT IGNORE INTO {db_prefix}settings VALUES ('mediapro_default_height','0')"); 
$smcFunc['db_query']('', "INSERT IGNORE INTO {db_prefix}settings VALUES ('mediapro_copyrightkey','')"); 
$smcFunc['db_query']('', "INSERT IGNORE INTO {db_prefix}settings VALUES ('mediapro_disablesig','0')"); 
$smcFunc['db_query']('', "REPLACE INTO {db_prefix}settings VALUES ('autoLinkUrls','1')"); 




$smcFunc['db_query']('', "CREATE TABLE IF NOT EXISTS {db_prefix}mediapro_cache (
  id int(11) NOT NULL auto_increment,
  mediaurl varchar(255),
  embedcode text,
  PRIMARY KEY (id),
  KEY (mediaurl) 
) ");


$smcFunc['db_query']('', "CREATE TABLE IF NOT EXISTS {db_prefix}mediapro_sites (
  id int(11) NOT NULL auto_increment,
  title varchar(255),
  enabled tinyint default 0,
  website varchar(255),
  regexmatch text,
  embedcode text,
  processregex text,
  height int(5) default 0,
  width int(5) default 0,

  PRIMARY KEY (id)
) ");

$enabledList = array();

	$result = $smcFunc['db_query']('', "
	SELECT
		id, title, website, regexmatch,
		embedcode, height,  width
	FROM {db_prefix}mediapro_sites
	WHERE enabled = 1 ORDER BY title ASC");
	while ($row = $smcFunc['db_fetch_assoc']($result))
	{
		$enabledList[] = $row['id'];
	}



$smcFunc['db_query']('', "REPLACE INTO {db_prefix}mediapro_sites
	(ID,title, website, height,width,  regexmatch, embedcode)
VALUES
(1, 'Youtube','http://www.youtube.com', 385,640, 'htt(p|ps)://[" . '\\' .'\\' . "w.]+youtube" . '\\' .'\\' . ".[" . '\\' .'\\' . "w]+/watch[(" . '\\' .'\\' . "?|" . '\\' .'\\' . "?feature=player_embedded&amp;)" . '\\' .'\\' . "#!]+v=([" . '\\' .'\\' . "w-]+)[" . '\\' .'\\' . "w&;+=-]*[" . '\\' .'\\' . "#t=]*([" . '\\' .'\\' . "d]*)[&;10wshdq=]*','" . '<object width="480" height="600">
<param name="movie" value="http://www.youtube.com/v/$2&fs=1&start=$3"></param>
<param name="allowFullScreen" value="true"></param>
<embed src="http://www.youtube.com/v/$2&fs=1&start=$3" type="application/x-shockwave-flash" allowfullscreen="true" width="480" height="600" wmode="transparent"></embed></object>' . "'),

(2, 'Metacafe','http://www.metacafe.com', 334,540, 'http://www" . '\\' .'\\' . ".metacafe" . '\\' .'\\' . ".com/watch/([" . '\\' .'\\' . "w-]+/[" . '\\' .'\\' . "w_]*)[" . '\\' .'\\' . "w&;=" . '\\' .'\\' . "+_" . '\\' .'\\' . "-" . '\\' .'\\' . "/]*','" . '<embed src="http://www.metacafe.com/fplayer/$1.swf" width="480" height="600" wmode="transparent" pluginspage="http://www.macromedia.com/go/getflashplayer" type="application/x-shockwave-flash" wmode="transparent"></embed>' . "'),

(3, 'Facebook','http://www.facebook.com', 385,640, 'http://www" . '\\' .'\\' . ".facebook" . '\\' .'\\' . ".com/video/video" . '\\' .'\\' . ".php" . '\\' .'\\' . "?v=([" . '\\' .'\\' . "w]+)&*[" . '\\' .'\\' . "w;=]*','" . '<object width="480" height="600" >
       <param name="allowfullscreen" value="true" />
       <param name="allowscriptaccess" value="always" />
       <param name="movie" value="http://www.facebook.com/v/$1" />
       <embed src="http://www.facebook.com/v/$1" type="application/x-shockwave-flash" allowscriptaccess="always" allowfullscreen="true" width="480" height="600"></embed></object>' . "'),

(4, 'Vimeo','http://www.vimeo.com', 385,640, 'http://[w" . '\\' .'\\' . ".]*vimeo" . '\\' .'\\' . ".com/([" . '\\' .'\\' . "d]+)[" . '\\' .'\\' . "w&;=" . '\\' .'\\' . "?+%/-]*','" . '<object type="application/x-shockwave-flash" width="480" height="600" data="http://vimeo.com/moogaloop.swf?clip_id=$1&amp;server=vimeo.com&amp;fullscreen=1&amp;video_info=1">	<param name="quality" value="best"><param name="allowfullscreen" value="true"><param name="scale" value="showAll"><param name="movie" value="http://vimeo.com/moogaloop.swf?clip_id=$1&amp;server=vimeo.com&amp;fullscreen=1&amp;video_info=1"></object>' . "'),


(5, 'College Humor','http://www.collegehumor.com',  385,640, 'http://]*[a-z]*?[" . '\\' .'\\' . ".]?collegehumor" . '\\' .'\\' . ".com/video:([0-9]+)','" . '<embed src="http://www.collegehumor.com/moogaloop/moogaloop.swf?clip_id=$1" quality="best" width="480" height="600" type="application/x-shockwave-flash"></embed>' . "'),

(6, 'Google Video', 'http://video.google.com',  385,640,'[http://]*video" . '\\' .'\\' . ".google" . '\\' .'\\' . ".[" . '\\' .'\\' . "w.]+/videoplay" . '\\' .'\\' . "?docid=([-" . '\\' .'\\' . "d]+)[&" . '\\' .'\\' . "w;=" . '\\' .'\\' . "+.-]*','" . '<embed style="width:480px; height:600px;" id="VideoPlayback" type="application/x-shockwave-flash" src="http://video.google.com/googleplayer.swf?docId=$1" flashvars="" wmode="transparent"> </embed>' . "')

");

$smcFunc['db_query']('', "REPLACE INTO {db_prefix}mediapro_sites
	(ID,title, website, height,width,  regexmatch, embedcode)
VALUES
(7, 'Veoh', 'http://www.veoh.com', 341,410, 'http://www" . '\\' .'\\' . ".veoh" . '\\' .'\\' . ".com/(.*)/watch/([A-Z0-9]*)','" . '<object width="480" height="600" id="veohFlashPlayer" name="veohFlashPlayer"><param name="movie" value="http://www.veoh.com/static/swf/webplayer/WebPlayer.swf?version=AFrontend.5.5.2.1066&permalinkId=$2&player=videodetailsembedded&videoAutoPlay=0&id=anonymous"></param><param name="allowFullScreen" value="true"></param><param name="allowscriptaccess" value="always"></param><embed src="http://www.veoh.com/static/swf/webplayer/WebPlayer.swf?version=AFrontend.5.5.2.1066&permalinkId=$2&player=videodetailsembedded&videoAutoPlay=0&id=anonymous" type="application/x-shockwave-flash" allowscriptaccess="always" allowfullscreen="true" width="480" height="600" id="veohFlashPlayerEmbed" name="veohFlashPlayerEmbed"></embed></object>' . "'),
(8, 'Youku', 'http://www.youku.com', 400,480, 'http://([A-Z0-9]*).youku.com/v_show/id_([A-Z0-9]*).html','" . '
<embed src="http://player.youku.com/player.php/sid/$2/v.swf" quality="high" width="480" height="600" align="middle" allowScriptAccess="sameDomain" type="application/x-shockwave-flash"></embed>' . "'),


(9, 'UStream.tv', 'http://www.ustream.tv', 386,480, 'http://([A-Z0-9]*).ustream.tv/recorded/([0-9]*)','" . '
<object classid="clsid:d27cdb6e-ae6d-11cf-96b8-444553540000" width="480" height="600" id="utv159159" name="utv_n_278276"><param name="flashvars" value="beginPercent=0.4193&amp;endPercent=0.4316&amp;autoplay=false&locale=en_US" /><param name="allowfullscreen" value="true" /><param name="allowscriptaccess" value="always" /><param name="src" value="http://www.ustream.tv/flash/video/$2" /><embed flashvars="beginPercent=0.4193&amp;endPercent=0.4316&amp;autoplay=false&locale=en_US" width="480" height="600" allowfullscreen="true" allowscriptaccess="always" id="utv159159" name="utv_n_278276" src="http://www.ustream.tv/flash/video/$2" type="application/x-shockwave-flash" /></object>

' . "')


");



//1.0.2
$smcFunc['db_query']('', "REPLACE INTO {db_prefix}mediapro_sites
	(ID,title, website, height,width,  regexmatch, embedcode)
VALUES

(10, 'Rutube', 'http://rutube.ru',353,470, 'http://rutube.ru/tracks/([A-Z0-9]*).html" . '\\' .'\\' . "?v=([A-Z0-9]*)','" . '
<OBJECT width="480" height="600"><PARAM name="movie" value="http://video.rutube.ru/$2"></PARAM><PARAM name="wmode" value="window"></PARAM><PARAM name="allowFullScreen" value="true"></PARAM><EMBED src="http://video.rutube.ru/$2" type="application/x-shockwave-flash" wmode="window" width="480" height="600" allowFullScreen="true" ></EMBED></OBJECT>
' . "'),

(11, 'Novamov', 'http://www.novamov.com', 480,600, 'http://www.novamov.com/video/([A-Z0-9]*)','" . '
<iframe style="overflow: hidden; border: 0; width:480px; height:600px" src="http://embed.novamov.com/embed.php?width=480&height=600&v=$1" scrolling="no"></iframe>
' . "'),

(12, 'MyVideo.de', 'http://www.MyVideo.de', 285,470, 'http://www.myvideo.de/watch/([A-Z0-9]*)/(.*)','" . '
<object style="width:480px;height:600px;" width="480" height="600"><param name="movie" value="http://www.myvideo.de/movie/$1"></param><param name="AllowFullscreen" value="true"></param><param name="AllowScriptAccess" value="always"></param><embed src="http://www.myvideo.de/movie/$1" width="480" height="600" type="application/x-shockwave-flash" allowscriptaccess="always" allowfullscreen="true"></embed></object>
' . "'),

(13, 'LiveLeak', 'http://www.liveleak.com', 370,450, 'http://www.liveleak.com/view" . '\\' .'\\' . "?i=(.*)','" . '
<object width="480" height="600"><param name="movie" value="http://www.liveleak.com/e/$1"></param><param name="wmode" value="transparent"></param><param name="allowscriptaccess" value="always"></param><embed src="http://www.liveleak.com/e/$1" type="application/x-shockwave-flash" wmode="transparent" allowscriptaccess="always" width="480" height="600"></embed></object>

' . "'),


(14, 'Sevenload', 'http://www.sevenload.com', 408,500, 'http://([A-Z0-9]*).sevenload.com/videos/([A-Z0-9]*)-(.*)','" . '
<object type="application/x-shockwave-flash" data="http://$1.sevenload.com/pl/$2/500x408/swf" width="480" height="600"><param name="allowFullscreen" value="true" /><param name="allowScriptAccess" value="always" /><param name="movie" value="http://$1.sevenload.com/pl/$2/500x408/swf" /></object>

' . "'),

(15, 'Gametrailers', 'http://www.gametrailers.com', 392,480, 'http://www.gametrailers.com/video/(.*)/([0-9]*)(.*)','" . '
<div style="width:480px;">
<object classid="clsid:d27cdb6e-ae6d-11cf-96b8-444553540000" codebase="http://download.macromedia.com/pub/shockwave/cabs/flash/swflash.cab#version=8,0,0,0" id="gtembed" width="480" height="600">	<param name="allowScriptAccess" value="sameDomain" /> <param name="allowFullScreen" value="true" /> <param name="movie" value="http://www.gametrailers.com/remote_wrap.php?mid=$2"/><param name="quality" value="high" /> <embed src="http://www.gametrailers.com/remote_wrap.php?mid=$2" swLiveConnect="true" name="gtembed" align="middle" allowScriptAccess="sameDomain" allowFullScreen="true" quality="high" pluginspage="http://www.macromedia.com/go/getflashplayer" type="application/x-shockwave-flash" width="480" height="600"></embed> </object>
</div>
' . "'),

(16, 'Funnyordie.com', 'http://www.funnyordie.com', 400,480, 'http://www.funnyordie.com/videos/([A-Z0-9]*)/(.*)','" . '
<object width="480" height="600" classid="clsid:d27cdb6e-ae6d-11cf-96b8-444553540000" id="ordie_player_$1"><param name="movie" value="http://player.ordienetworks.com/flash/fodplayer.swf" /><param name="flashvars" value="key=$1" /><param name="allowfullscreen" value="true" /><param name="allowscriptaccess" value="always"></param><embed width="480" height="600" flashvars="key=$1" allowfullscreen="true" allowscriptaccess="always" quality="high" src="http://player.ordienetworks.com/flash/fodplayer.swf" name="ordie_player_$1" type="application/x-shockwave-flash"></embed></object>

' . "'),

(17, 'Mevio', 'http://www.mevio.com', 336,600, 'http://www.mevio.com/channels/" . '\\' .'\\' . "?cId=([0-9]*)" . '\\' .'\\' . "&amp;mId=([0-9]*)','" . '
<object classid="clsid:d27cdb6e-ae6d-11cf-96b8-444553540000"codebase="http://download.macromedia.com/pub/shockwave/cabs/flash/swflash.cab#version=9,0,0,0" width="480" height="600" id="MevioWM" align="middle"><param name="allowScriptAccess" value="never" /><param name="allowFullScreen" value="true" /><param name="movie" value="http://ui.mevio.com/widgets/mwm/MevioWM.swf?r=36745 " /><param name="quality" value="high" /><param name="FlashVars"     value="distribConfig=http://www.mevio.com/widgets/configFiles/distribconfig_mwm_pcw_default.php?r=36745&autoPlay=false&container=false&rssFeed=/%3FcId=$1%26cMediaId=$2%26format=json&playerIdleEnabled=false&fwSiteSection=DistribGeneric" /><param name="bgcolor" value="#000000" />	<embed src="http://ui.mevio.com/widgets/mwm/MevioWM.swf?r=36745 " quality="high" bgcolor="#000000" width="480" height="600" FlashVars="distribConfig=http://www.mevio.com/widgets/configFiles/distribconfig_mwm_pcw_default.php?r=36745&autoPlay=false&container=false&rssFeed=/%3FcId=$1%26cMediaId=$2%26format=json&playerIdleEnabled=false&fwSiteSection=DistribGeneric"name="MevioWM"align="middle"allowScriptAccess="never"allowFullScreen="true"type="application/x-shockwave-flash"pluginspage="http://www.macromedia.com/go/getflashplayer" /></object>

' . "')


");
//1.0.3

$smcFunc['db_query']('', "REPLACE INTO {db_prefix}mediapro_sites
	(ID,title, website, height,width,  regexmatch, embedcode)
VALUES

(18, 'Crackle', 'http://www.crackle.com', 281,500, 'http://www.crackle.com/c/(.*)/(.*)/([0-9]*)','" . '
<embed src="http://www.crackle.com/p/$1/$2.swf" quality="high" bgcolor="#869ca7" width="480" height="600" name="mtgPlayer" align="middle" play="true" loop="false" allowFullScreen="true" flashvars="id=$3&mu=0&ap=0" quality="high" allowScriptAccess="always" type="application/x-shockwave-flash" pluginspage="http://www.adobe.com/go/getflashplayer"> </embed>

' . "'),


(20, 'SchoolTube', 'http://www.schooltube.com', 375,500, 'http://www.schooltube.com/video/([A-Z0-9]*)/(.*)','" . '
<object width="480" height="600"><param name="movie" value="http://www.schooltube.com/v/$1" /><param name="allowFullScreen" value="true" /><param name="allowscriptaccess" value="always" /><embed src="http://www.schooltube.com/v/16483926ba522476e7ae" type="application/x-shockwave-flash" allowFullScreen="true" allowscriptaccess="always" width="480" height="600" FlashVars="gig_lt=1281633293655&gig_pt=1281633309820&gig_g=2"></embed> <param name="FlashVars" value="gig_lt=1281633293655&gig_pt=1281633309820&gig_g=2" /></object>

' . "'),


(21, 'MySpace', 'http://www.myspace.com', 360,425, 'http://vids.myspace.com/index.cfm" . '\\' .'\\' . "?fuseaction=vids.individual" . '\\' .'\\' . "&amp;videoid=([0-9]*)','" . '
<object width="480" height="600"><param name="allowFullScreen" value="true"/><param name="wmode" value="transparent"/><param name="movie" value="http://mediaservices.myspace.com/services/media/embed.aspx/m=$1,t=1,mt=video"/><embed src="http://mediaservices.myspace.com/services/media/embed.aspx/m=$1,t=1,mt=video" width="480" height="600" allowFullScreen="true" type="application/x-shockwave-flash" wmode="transparent"></embed></object>

' . "'),

(22, 'Mefeedia', 'http://www.mefeedia.com', 450,640, 'http://www.mefeedia.com/watch/([0-9]*)','" . '
<iframe scrolling="no" frameborder="0" width="480" height="600" src="http://www.mefeedia.com/watch/$1&iframe"></iframe>
' . "'),


(24, 'DailyMotion', 'http://www.dailymotion.com', 360,480, 'http://www.dailymotion.com/video/([A-Z0-9]*)_(.*)','" . '
<object width="480" height="600"><param name="movie" value="http://www.dailymotion.com/swf/video/$1?additionalInfos=0"></param><param name="allowFullScreen" value="true"></param><param name="allowScriptAccess" value="always"></param><embed type="application/x-shockwave-flash" src="http://www.dailymotion.com/swf/video/$1?additionalInfos=0" width="480" height="600" allowfullscreen="true" allowscriptaccess="always"></embed></object>
' . "'),


(25, 'Clipfish.de', 'http://www.clipfish.de', 384,464, 'http://www.clipfish.de/video/([0-9]*)/(.*)/','" . '
<object codebase="http://fpdownload.macromedia.com/pub/shockwave/cabs/flash/swflash.cab#version=8,0,0,0" width="480" height="600" > <param name="allowScriptAccess" value="always" /> <param name="movie" value="http://www.clipfish.de/cfng/flash/clipfish_player_3.swf?as=0&videoid=$1&r=1&area=e&c=990000" /> <param name="bgcolor" value="#ffffff" /> <param name="allowFullScreen" value="true" /> <embed src="http://www.clipfish.de/cfng/flash/clipfish_player_3.swf?as=0&vid=$1&r=1&area=e&c=990000" quality="high" bgcolor="#990000" width="480" height="600" name="player" align="middle" allowFullScreen="true" allowScriptAccess="always" type="application/x-shockwave-flash" pluginspage="http://www.macromedia.com/go/getflashplayer"></embed></object>

' . "'),

(26, 'Goear', 'http://www.goear.com', 132,353, 'http://www.goear.com/listen/([A-Z0-9]*)/(.*)','" . '
<object width="480" height="600"><embed src="http://www.goear.com/files/external.swf?file=$1" type="application/x-shockwave-flash" wmode="transparent" quality="high" width="480" height="600"></embed></object>

' . "'),


(27, 'Clipmoon', 'http://www.clipmoon.com', 357,460, 'http://www.clipmoon.com/videos/([0-9]*)/(.*).html','" . '
<embed src="http://www.clipmoon.com/flvplayer.swf" FlashVars="config=http://www.clipmoon.com/flvplayer.php?viewkey=$1&external=no" quality="high" bgcolor="#000000" wmode="transparent" width="480" height="600" loop="false" align="middle" type="application/x-shockwave-flash" pluginspage="http://www.macromedia.com/go/getflashplayer"  scale="exactfit" > </embed>

' . "')


");

/*
(33, 'MegaVideo', 'http://www.megavideo.com', 344,640, 'http://(.*)megavideo.com/" . '\\' .'\\' . "?v=([A-Z_0-9]*)','" . '
<object width="640" height="344"><param name="movie" value="http://www.megavideo.com/v/$2.0.0"></param><param name="allowFullScreen" value="true"></param><embed src="http://www.megavideo.com/v/$2.0.0" type="application/x-shockwave-flash" allowfullscreen="true" width="480" height="600"></embed></object>
' . "'),
*/

// 1.0.4
$smcFunc['db_query']('', "REPLACE INTO {db_prefix}mediapro_sites
	(ID,title, website,height,width,  regexmatch, embedcode)
VALUES
(28, 'Stagevu', 'http://www.stagevu.com', 362,720, 'http://stagevu.com/video/([A-Z0-9]*)','" . '
<iframe style="overflow: hidden; border: 0; width:480px; height:600px" src="http://stagevu.com/embed?width=720&amp;height=306&amp;background=000&amp;uid=$1" scrolling="no"></iframe>
' . "'),
(29, 'Mail.ru', 'http://www.mail.ru', 367,626, 'http://video.mail.ru/mail/([0-9]*)/([0-9]*)/([0-9]*).html','" . '
<object width="480" height="600"><param name="allowScriptAccess" value="always" /><param name="movie" value="http://img.mail.ru/r/video2/player_v2.swf?movieSrc=mail/$1/$2/$3" /><embed src=http://img.mail.ru/r/video2/player_v2.swf?movieSrc=mail/$1/$2/$3 type="application/x-shockwave-flash" width="480" height="600" allowScriptAccess="always"></embed></object>
' . "'),
(30, 'Twitvid', 'http://www.twitvid.com',344,425, 'http://www.twitvid.com/([A-Z0-9]*)','" . '
<object width="480" height="600"><param name="movie" value="http://www.twitvid.com/player/$1"></param><param name="allowscriptaccess" value="always"></param><param name="allowFullScreen" value="true"></param><embed type="application/x-shockwave-flash" src="http://www.twitvid.com/player/$1" quality="high" allowscriptaccess="always" allowNetworking="all" allowfullscreen="true" wmode="transparent" width="480" height="600"></object>
' . "'),
(31, 'Trtube', 'http://www.trtube.com', 350,425, 'http://www.trtube.com/(.*)-([0-9]*).html','" . '
<object width="480" height="600"><param name="allowScriptAccess" value="always"><param name="movie" value="http://www.trtube.com/mediaplayer_3_15.swf?file=http://www.trtube.com/playlist.php?v=$2&image=http://resim.trtube.com/a/102/$2.gif&logo=http://load.trtube.com/img/logoembed.gif&linkfromdisplay=false&linktarget=_blank&autostart=false"><param name="quality" value="high"><param name="bgcolor" value="#ffffff"><param name="allowfullscreen" value="true"><embed src="http://www.trtube.com/mediaplayer_3_15.swf?file=http://www.trtube.com/playlist.php?v=$2&image=http://resim.trtube.com/a/102/$2.gif&logo=http://load.trtube.com/img/logoembed.gif&linkfromdisplay=false&linktarget=_blank&autostart=false" quality="high" bgcolor="#ffffff" allowfullscreen="true" width="480" height="600" name="player" align="middle" type="application/x-shockwave-flash" allowScriptAccess="always" pluginspage="http://www.macromedia.com/go/getflashplayer"></object>
' . "'),
(32, 'BlogTV', 'http://www.blogtv.com', 374,445, 'http://www.blogtv.com/Shows/([0-9]*)/([A-Z_0-9]*)(.*)','" . '
<embed width="480" height="600" src="http://www.blogtv.com/vb/$2" type="application/x-shockwave-flash" allowFullScreen="true"></embed>
' . "'),

(34, 'VH1', 'http://www.vh1.com', 319,512, 'http://www.vh1.com/video/(.*)/([0-9]*)/(.*).jhtml(.*?)','" . '
<embed src="http://media.mtvnservices.com/mgid:uma:video:vh1.com:$2" width="480" height="600" wmode="transparent" type="application/x-shockwave-flash" flashVars="configParams=vid%3D$2%26uri%3Dmgid%3Auma%3Avideo%3Avh1.com%3A$2%26instance%3Dvh1" allowFullScreen="true" allowScriptAccess="always" base="."></embed>
' . "'),
(35, 'BET', 'http://www.bet.com', 319,512, 'http://www.bet.com/video/([0-9]*)','" . '
<embed src="http://media.mtvnservices.com/mgid:media:video:bet.com:$1" width="480" height="600" wmode="transparent" type="application/x-shockwave-flash" flashVars="" allowFullScreen="true" allowScriptAccess="always" base="."></embed>
' . "')


");       

// 1.0.5

$smcFunc['db_query']('', "REPLACE INTO {db_prefix}mediapro_sites
	(ID,title, website,height,width,  regexmatch, embedcode)
VALUES
(36, 'Espn', 'http://espn.go.com', 216,384, 'http://espn.go.com/video/clip" . '\\' .'\\' . "?id=([0-9]*)','" . '
<object width="480" height="600" type="application/x-shockwave-flash" id="ESPN_VIDEO" data="http://espn.go.com/videohub/player/embed.swf" allowScriptAccess="always" allowNetworking="all"><param name="movie" value="http://espn.go.com/videohub/player/embed.swf" /><param name="allowFullScreen" value="true"/><param name="wmode" value="opaque"/><param name="allowScriptAccess" value="always"/><param name="allowNetworking" value="all"/><param name="flashVars" value="id=$1"/></object>
' . "'),
(37, 'CNN iReport', 'http://ireport.cnn.com', 400,300, 'http://ireport.cnn.com/docs/DOC-([0-9]*)','" . '
<object width="480" height="600" classid="clsid:D27CDB6E-AE6D-11cf-96B8-444553540000" id="ep"><param name="allowfullscreen" value="true" /><param name="allowscriptaccess" value="always" /><param name="movie" value="http://ireport.cnn.com/themes/custom/resources/cvplayer/ireport_embed.swf?player=embed&configPath=http://ireport.cnn.com&playlistId=$1&contentId=$1/0&" /><param name="bgcolor" value="#FFFFFF" /><embed src="http://ireport.cnn.com/themes/custom/resources/cvplayer/ireport_embed.swf?player=embed&configPath=http://ireport.cnn.com&playlistId=$1&contentId=$1/0&" type="application/x-shockwave-flash" bgcolor="#FFFFFF" allowfullscreen="true" allowscriptaccess="always" width="480" height="600"></embed></object>
' . "'),
(38, 'PBS', 'http://video.pbs.org', 328,512, 'http://video.pbs.org/video/([0-9]*)/','" . '
<object width="480" height="600" > <param name = "movie" value = "http://www-tc.pbs.org/video/media/swf/PBSPlayer.swf" > </param><param name="flashvars" value="video=$1&player=viral&chapter=1" /> <param name="allowFullScreen" value="true"></param > <param name = "allowscriptaccess" value = "always" > </param><param name="wmode" value="transparent"></param ><embed src="http://www-tc.pbs.org/video/media/swf/PBSPlayer.swf" flashvars="video=$1&player=viral&chapter=1" type="application/x-shockwave-flash" allowscriptaccess="always" wmode="transparent" allowfullscreen="true" width="480" height="600" bgcolor="#000000"></embed></object>

' . "'),
(39, 'TNT', 'http://www.tnt.tv', 375,442, 'http://www.tnt.tv/dramavision/index.jsp" . '\\' .'\\' . "?oid=([0-9]*)','" . '
<object width="480" height="600"" classid="clsid:D27CDB6E-AE6D-11cf-96B8-444553540000" id="ep"><param name="allowfullscreen" value="true" /><param name="allowscriptaccess" value="always" /><param name="movie" value="http://i.cdn.turner.com/v5cache/TNT/cvp/tnt_embed.swf?context=embed&videoId=$1" /><param name="bgcolor" "value="#FFFFFF" /><embed src="http://i.cdn.turner.com/v5cache/TNT/cvp/tnt_embed.swf?context=embed&videoId=$1" type="application/x-shockwave-flash" bgcolor="#FFFFFF" allowfullscreen="true" allowscriptaccess="always" width="480" height="600"></embed></object>

' . "'),
(40, 'Comedy Central','http://www.comedycentral.com', 301,360,  'http://www.comedycentral.com/videos/index.jhtml" . '\\' .'\\' . "?videoId=([0-9]*)" . '\\' .'\\' . "&amp;title=(.*)','" . '
<embed style="display:block" src="http://media.mtvnservices.com/mgid:cms:item:comedycentral.com:$1" width="360" height="301" type="application/x-shockwave-flash" wmode="window" allowFullscreen="true" flashvars="autoPlay=false" allowscriptaccess="always" allownetworking="all" bgcolor="#000000"></embed>

' . "'),
(41, 'Stream.cz', 'http://www.stream.cz', 382,624, 'http://(.*)stream.cz/video/([0-9]*)(.*)','" . '
<object height="382" width="624"><param name="movie" id="VideoSpot" value="http://www.stream.cz/object/$2$3"><param name="allowfullscreen" value="true"><param name="allowscriptaccess" value="always"><param name="wmode" value="transparent"><embed src="http://www.stream.cz/object/$2$3" type="application/x-shockwave-flash" wmode="transparent" allowfullscreen="true" allowscriptaccess="always" height="382" width="624"></object>
' . "')


");

// 1.0.6

$smcFunc['db_query']('', "REPLACE INTO {db_prefix}mediapro_sites
	(ID,title, website, height,width, regexmatch, embedcode)
VALUES

(42, 'BlogDumpsVideo', 'http://www.blogdumpsvideo.com', 350,600, 'http://www.blogdumpsvideo.com/action/viewvideo/([0-9]*)/(.*)/','" . '
<embed src="http://www.blogdumpsvideo.com/HDplayer.swf" FlashVars="config=http://www.blogdumpsvideo.com/videoConfigXmlCodeHD.php?pg=video_$1_no_0_extsite&playList=http://www.blogdumpsvideo.com/videoPlaylistXmlCodeHD.php?pg=video_$1" quality="high" bgcolor="#000000" width="480" height="600" name="flvplayer" align="middle" allowScriptAccess="always" type="application/x-shockwave-flash" pluginspage="http://www.macromedia.com/go/getflashplayer" allowFullScreen="true" />
' . "'),
(43, 'Reuters', 'http://www.reuters.com', 259, 460, 'http://www.reuters.com/news/video/story" . '\\' .'\\' . "?videoId=([0-9]*)(.*)','" . '
<object type="application/x-shockwave-flash" data="http://www.reuters.com/resources_v2/flash/video_embed.swf?videoId=$1" width="480" height="600"><param name="movie" value="http://www.reuters.com/resources_v2/flash/video_embed.swf?videoId=$1"></param><param name="allowFullScreen" value="true"></param><param name="allowScriptAccess" value="always"></param><param name="wmode" value="transparent"><embed src="http://www.reuters.com/resources_v2/flash/video_embed.swf?videoId=$1" type="application/x-shockwave-flash" allowfullscreen="true" allowScriptAccess="always" width="480" height="600" wmode="transparent"></embed></object>
' . "'),
(44, 'MarketNewsVideo', 'http://www.marketnewsvideo.com', 320,400, 'http://www.marketnewsvideo.com/embed/" . '\\' .'\\' . "?id=([A-Z_0-9]*)(.*)','" . '
<iframe src="http://www.marketnewsvideo.com/?id=$1&mv=1&embed=1&width=400&height=320" frameborder="0" width="480" height="600" marginheight="0" marginwidth="0" scrolling="no" name="mnv1282073139"></iframe>
' . "'),
(45, 'Clipsyndicate PlayLists', 'http://www.clipsyndicate.com', 330,425, 'http://www.clipsyndicate.com/video/playlist/([0-9]*)/([0-9]*)(.*)','" . '
<object classid="clsid:D27CDB6E-AE6D-11cf-96B8-444553540000" id="cs_player" width="480" height="600"><param name="movie" value="http://eplayer.clipsyndicate.com/cs_api/get_swf/3/&amp;pl_id=$1&amp;page_count=5&amp;windows=1&amp;show_title=0&amp;va_id=$2&amp;rwpid=273&amp;auto_start=0&amp;auto_next=1" /><param name="allowfullscreen" value="true" /><param name="allowscriptaccess" value="always" /><embed src="http://eplayer.clipsyndicate.com/cs_api/get_swf/3/&amp;pl_id=$1&amp;page_count=5&amp;windows=1&amp;show_title=0&amp;va_id=$2&amp;rwpid=273&amp;auto_start=0&amp;auto_next=1" type="application/x-shockwave-flash" allowscriptaccess="always" allowfullscreen="true" width="480" height="600" /></object>
' . "'),
(46, 'NyTimes', 'http://video.nytimes.com', 373,480, 'http://video.nytimes.com/video/([0-9]*)/([0-9]*)/([0-9]*)/([A-Z_0-9]*)/([0-9]*)/(.*)','" . '
<iframe width="480" height="600" frameborder="0" scrolling="no" marginheight="0" marginwidth="0" id="nyt_video_player" title="New York Times Video - Embed Player" src="http://graphics8.nytimes.com/bcvideo/1.0/iframe/embed.html?videoId=$5&playerType=embed"></iframe>

' . "')

");


$smcFunc['db_query']('', "REPLACE INTO {db_prefix}mediapro_sites
	(ID,title, website,height,width,  regexmatch, embedcode)
VALUES
(47, 'Clipshack', 'http://www.clipshack.com', 370,430, 'http://www.clipshack.com/Clip.aspx" . '\\' .'\\' . "?key=([A-Z0-9]*)','" . '
<embed src="http://www.clipshack.com/player.swf?key=$1" width="480" height="600" wmode="transparent"></embed>
' . "'),
(48, 'Mpora', 'http://video.mpora.com', 315,480, 'http://video.mpora.com/watch/([A-Z0-9]*)/','" . '
<object id="mpora_$1" classid="clsid:D27CDB6E-AE6D-11cf-96B8-444553540000" width="480" height="600"><param name="movie" value="http://video.mpora.com/p/$1" /><param name="allowfullscreen" value="true" /><embed src="http://video.mpora.com/p/$1" width="480" height="600" allowfullscreen="true"></embed></object>
' . "'),
(49, 'Izlesene.com', 'http://www.izlesene.com', 300,400, 'http://www.izlesene.com/video/(.*)/([0-9]*)','" . '
<object width="480" height="600"><param name="allowfullscreen" value="true" /><param name="allowscriptaccess" value="always" /><param name="movie" value="http://www.izlesene.com/embedplayer.swf?video=$2" /><embed src="http://www.izlesene.com/embedplayer.swf?video=$2" wmode="window" bgcolor="#000000" allowfullscreen="true" allowscriptaccess="always" menu="false" scale="noScale" width="480" height="600" type="application/x-shockwave-flash"></embed></object>
' . "'),
(50, 'Rambler.ru', 'http://www.rambler.ru', 370,390, 'http://vision.rambler.ru/users/([A-Z0-9]*)/([0-9]*)/([0-9]*)/','" . '
<object classid="clsid:d27cdb6e-ae6d-11cf-96b8-444553540000" codebase="http://fpdownload.macromedia.com/pub/shockwave/cabs/flash/swflash.cab#version=7,0,0,0" width="480" height="600"><param name="wmode" value="transparent"/><param name="allowFullScreen" value="true"/><param name="movie" value="http://vision.rambler.ru/i/e.swf?id=$1/$2/$3&logo=1" /><embed src="http://vision.rambler.ru/i/e.swf?id=$1/$2/$3&logo=1" width="480" height="600" type="application/x-shockwave-flash" wmode="transparent" allowFullScreen="true" /></object>
' . "'),
(51, 'Tangle.com', 'http://www.tangle.com', 270,330, 'http://www.tangle.com/view_video" . '\\' .'\\' . "?viewkey=([A-Z0-9]*)','" . '
<embed src="http://www.tangle.com/flash/swf/flvplayer.swf" FlashVars="viewkey=$1" wmode="transparent" quality="high" width="480" height="600" name="tangle" align="middle" allowScriptAccess="always" type="application/x-shockwave-flash" pluginspage="http://www.macromedia.com/go/getflashplayer" /></embed>
' . "')
");

$smcFunc['db_query']('', "REPLACE INTO {db_prefix}mediapro_sites
	(ID,title, website, height,width,  regexmatch, embedcode)
VALUES
(52, 'Trophy-clips.com', 'http://www.trophy-clips.com', 320,390, 'http://www.trophy-clips.com/view_video.php" . '\\' .'\\' . "?viewkey=([A-Z0-9]*)','" . '
<object type="application/x-shockwave-flash" width="480" height="600" wmode="transparent" data="http://www.trophy-clips.com/embedplayer.swf?config=http://www.trophy-clips.com/embedconfig.php?vkey=$1">
        <param name="movie" value="http://www.trophy-clips.com/embedplayer.swf?config=http://www.trophy-clips.com/embedconfig.php?vkey=$1" />
        <param name="wmode" value="transparent" />
        <param name="quality" value="high" />
        <param name="menu" value="false" />
		<param name="allowScriptAccess" value="sameDomain" />
		<param name="allowfullscreen" value="true" />
<embed src="http://www.trophy-clips.com/embedplayer.swf" FlashVars="config=http://www.trophy-clips.com/embedconfig.php?vkey=$1" width="480" height="600" allowScriptAccess="sameDomain" type="application/x-shockwave-flash" pluginspage="http://www.macromedia.com/go/getflashplayer" ></embed>
</object>
' . "'),
(53, 'Yahoo', 'http://video.yahoo.com', 322,512, 'http://video.yahoo.com/watch/([0-9]*)/([0-9]*)','" . '
 <object width="480" height="600""><param name="movie" value="http://d.yimg.com/static.video.yahoo.com/yep/YV_YEP.swf?ver=2.2.46" /><param name="allowFullScreen" value="true" /><param name="AllowScriptAccess" VALUE="always" /><param name="bgcolor" value="#000000" /><param name="flashVars" value="id=$2&vid=$1&lang=en-us&intl=us&thumbUrl=http%3A//l.yimg.com/a/i/us/sch/cn/video05/$1_rnd3230d645_19.jpg&embed=1" /><embed src="http://d.yimg.com/static.video.yahoo.com/yep/YV_YEP.swf?ver=2.2.46" type="application/x-shockwave-flash" width="480" height="600" allowFullScreen="true" AllowScriptAccess="always" bgcolor="#000000" flashVars="id=$2&vid=$1&lang=en-us&intl=us&thumbUrl=http%3A//l.yimg.com/a/i/us/sch/cn/video05/$1_rnd3230d645_19.jpg&embed=1" ></embed></object>

' . "')
");



$smcFunc['db_query']('', "REPLACE INTO {db_prefix}mediapro_sites
	(ID,title, website, height,width,  regexmatch, embedcode)
VALUES
(54, 'Bungie.net', 'http://www.bungie.net', 360,640, 'http://www.bungie.net/Silverlight/bungiemediaplayer/embed.aspx" . '\\' .'\\' . "?fid=([0-9]*)','" . '
<iframe src="http://www.bungie.net/Silverlight/bungiemediaplayer/embed.aspx?fid=$1" scrolling="no" style="padding:0;margin:0;border:0;" width="480" height="600" ></iframe>

' . "')
");

// 1.0.10
$smcFunc['db_query']('', "REPLACE INTO {db_prefix}mediapro_sites
	(ID,title, website, height,width, regexmatch, embedcode)
VALUES
(55, 'XFire', 'http://www.xfire.com', 344,380, 'http://www.xfire.com/video/([A-Z0-9]*)/','" . '
<object width="480" height="600"><embed src="http://media.xfire.com/swf/embedplayer.swf" type="application/x-shockwave-flash" allowscriptaccess="always" allowfullscreen="true" width="480" height="600" flashvars="videoid=$1"></embed></object>
' . "'),
(56, 'Worldstarhiphop.com', 'http://www.worldstarhiphop.com', 374,448, 'http://www.worldstarhiphop.com/videos/video.php" . '\\' .'\\' . "?v=([A-Z0-9]*)','" . '
<object width="480" height="600"><param name="movie" value="http://www.worldstarhiphop.com/videos/e/16711680/$1"><param name="allowFullScreen" value="true"></param><embed src="http://www.worldstarhiphop.com/videos/e/16711680/$1" type="application/x-shockwave-flash" allowFullscreen="true" width="480" height="600"></embed></object>
' . "'),
(57, 'TinyPic.com', 'http://www.tinypic.com', 420,440, 'http://[" . '\\' .'\\' . "w.]+tinypic.com/player.php" . '\\' .'\\' . "?v=([A-Z0-9]*)" . '\\' .'\\' . "&amp;s=([A-Z0-9]*)','" . '
<embed width="480" height="600" type="application/x-shockwave-flash" src="http://v$2.tinypic.com/player.swf?file=$1&s=$2">
' . "'),
(58, 'JibJab', 'http://www.jibjab.com', 319,425, 'http://sendables.jibjab.com/view/([A-Za-z0-9]*)','" . '
<object id="A64060" quality="high" data="http://aka.zero.jibjab.com/client/zero/ClientZero_EmbedViewer.swf?external_make_id=$1" pluginspage="http://www.macromedia.com/go/getflashplayer" type="application/x-shockwave-flash" wmode="transparent" width="480" height="600"><param name="wmode" value="transparent"></param><param name="movie" value="http://aka.zero.jibjab.com/client/zero/ClientZero_EmbedViewer.swf?external_make_id=$1"></param><param name="scaleMode" value="showAll"></param><param name="quality" value="high"></param><param name="allowNetworking" value="all"></param><param name="allowFullScreen" value="true" /><param name="FlashVars" value="external_make_id=$1"></param><param name="allowScriptAccess" value="always"></param></object>
' . "'),
(59, 'G4tv', 'http://www.g4tv.com', 418,480, 'http://[w" . '\\' .'\\' . ".]*g4tv.com/videos/([0-9]*)/([a-zA-Z0-9_" . '\\' .'\\' . ""   . "-]*)/','" . '
<object classId="clsid:D27CDB6E-AE6D-11cf-96B8-444553540000" width="480" height="600" id="VideoPlayerLg$1"><param name="movie" value="http://www.g4tv.com/lv3/$1" /><param name="allowScriptAccess" value="always" /><param name="allowFullScreen" value="true" /><embed src="http://g4tv.com/lv3/$1" type="application/x-shockwave-flash" name="VideoPlayer" width="480" height="600" allowScriptAccess="always" allowFullScreen="true" /></object>
' . "'),
(60, 'IGN', 'http://www.ign.com', 270,480, 'http://www.ign.com/videos/([0-9]*)/([0-9]*)/([0-9]*)/([a-zA-Z0-9_=" . '\\' .'\\' . ""  . "-" . '\\' .'\\' . ""  . "?]*)','" . '
<object id="vid" class="ign-videoplayer" width="480" height="600" data="http://media.ign.com/ev/prod/embed.swf" type="application/x-shockwave-flash"><param name="movie" value="http://media.ign.com/ev/prod/embed.swf" /><param name="allowfullscreen" value="true" /><param name="allowscriptaccess" value="always" /><param name="bgcolor" value="#000000" /><param name="flashvars" value="url=http://www.ign.com/videos/$1/$2/$3/$4"/></object>
' . "'),
(61, 'Joystiq', 'http://www.joystiq.com', 266,437, 'http://www.joystiq.com/video/([a-zA-Z0-9]*)','" . '
<object classid="clsid:D27CDB6E-AE6D-11cf-96B8-444553540000" width="480" height="600" id="viddler"><param name="movie" value="http://www.viddler.com/simple/$1" /><param name="allowScriptAccess" value="always" /><param name="allowFullScreen" value="true" /><param name="flashvars" value="fake=1"/><embed src="http://www.viddler.com/simple/$1" width="480" height="600" type="application/x-shockwave-flash" allowScriptAccess="always" allowFullScreen="true" flashvars="fake=1" name="viddler" ></embed></object>
' . "')

");



$smcFunc['db_query']('', "REPLACE INTO {db_prefix}mediapro_sites
	(ID,title, website, height,width,  regexmatch, embedcode)
VALUES
(62, 'VideoBB.com', 'http://www.videobb.com/', 344,425, 'http://www.videobb.com/video/([a-zA-Z0-9]*)','" . '
<object id="vbbplayer" width="480" height="600" classid="clsid:d27cdb6e-ae6d-11cf-96b8-444553540000" ><param name="movie" value="http://www.videobb.com/e/$1" ></param><param name="allowFullScreen" value="true" ></param><param name="allowscriptaccess" value="always"></param><embed src="http://www.videobb.com/e/$1" type="application/x-shockwave-flash" allowscriptaccess="always" allowfullscreen="true" width="480" height="600"></embed></object>
' . "')
");

// 1.0.12

$smcFunc['db_query']('', "REPLACE INTO {db_prefix}mediapro_sites
	(ID,title, website, height,width, regexmatch, embedcode)
VALUES
(63, 'HostingCup', 'http://www.hostingcup.com/', 416,540, 'http://www.hostingcup.com/([a-zA-Z0-9]*).html','" . '
<iframe src="http://www.hostingcup.com/embed-$1.html" frameborder="0" marginheight="0" marginheight="0" scrolling="no" width="480" height="600"></iframe>
' . "'),
(64, 'Movshare.net', 'http://www.Movshare.net/', 662,720, 'http://www.movshare.net/video/([a-zA-Z0-9]*)','" . '
<iframe style="overflow: hidden; border: 0; width:480px; height:600px" src="http://www.movshare.net/embed/$1/?width=480&height=600" scrolling="no"></iframe>
' . "'),
(65, 'HostingBulk.com', 'http://www.hostingbulk.com/', 344,640, 'http://hostingbulk.com/([a-zA-Z0-9]*).html','" . '
<iframe src="http://hostingbulk.com/embed-$1-640x344.html" frameborder="0" marginheight="0" marginheight="0" scrolling="no" width="480" height="600"></iframe>
' . "')

");

// 1.1


$smcFunc['db_query']('', "REPLACE INTO {db_prefix}mediapro_sites
	(ID,title, website, height,width,  regexmatch, embedcode)
VALUES
	(66, 'Smotri', 'http://www.smotri.com', 360,640, 'http://smotri.com/video/view/" . '\\' .'\\' . "?id=([a-zA-Z0-9]*)','" . '
<object id="smotriComVideoPlayer" classid="clsid:d27cdb6e-ae6d-11cf-96b8-444553540000" width="480" height="600"><param name="movie" value="http://pics.smotri.com/player.swf?file=$1&bufferTime=3&autoStart=false&str_lang=rus&xmlsource=http%3A%2F%2Fpics.smotri.com%2Fcskins%2Fblue%2Fskin_color.xml&xmldatasource=http%3A%2F%2Fpics.smotri.com%2Fskin_ng.xml" /><param name="allowScriptAccess" value="always" /><param name="allowFullScreen" value="true" /><param name="bgcolor" value="#ffffff" /><embed src="http://pics.smotri.com/player.swf?file=$1&bufferTime=3&autoStart=false&str_lang=rus&xmlsource=http%3A%2F%2Fpics.smotri.com%2Fcskins%2Fblue%2Fskin_color.xml&xmldatasource=http%3A%2F%2Fpics.smotri.com%2Fskin_ng.xml" quality="high" allowscriptaccess="always" allowfullscreen="true" wmode="opaque"  width="480" height="600" type="application/x-shockwave-flash"></embed></object>
' . "'),
	(67, 'PinkBike.com', 'http://www.pinkbike.com', 375,500, 'http://www.pinkbike.com/video/([0-9]*)/','" . '
<object width="480" height="600"><param name="allowFullScreen" value="true" /><param name="allowScriptAccess" value="always" /><param name="movie" value="http://www.pinkbike.com/v/$1/l/" /><embed src="http://www.pinkbike.com/v/$1/l/" type="application/x-shockwave-flash" width="480" height="600" allowFullScreen="true" allowScriptAccess="always"></embed></object>
' . "')
	
	
");



$smcFunc['db_query']('', "REPLACE INTO {db_prefix}mediapro_sites
	(ID,title, website, height,width,  regexmatch, embedcode)
VALUES
	(68, 'Zippyshare', 'http://www.zippyshare.com', 300,20, 'http://([A-Z0-9]*).zippyshare.com/v/([A-Z0-9]*)/file.html','" . '
<object></object><script type="text/javascript">var zippywww="$1";var zippyfile="$2";var zippydown="ffffff";var zippyfront="000000";var zippyback="ffffff";var zippylight="000000";var zippywidth=480;var zippyauto=false;var zippyvol=80;var zippydwnbtn = 1;</script><script type="text/javascript" src="http://api.zippyshare.com/api/embed.js"></script>
' . "'),
	(69, 'Zippyshare 2', 'http://www.zippyshare.com', 375,500, 'http://([A-Z0-9]*).zippyshare.com/view.jsp" . '\\' .'\\' . "?locale=([A-Z0-9]*)" . '\\' .'\\' . "&amp;key=([A-Z0-9]*)','" . '
<object></object><script type="text/javascript">var zippywww="$1";var zippyfile="$3";var zippydown="ffffff";var zippyfront="000000";var zippyback="ffffff";var zippylight="000000";var zippywidth=480;var zippyauto=false;var zippyvol=80;var zippydwnbtn = 1;</script><script type="text/javascript" src="http://api.zippyshare.com/api/embed.js"></script>
' . "')
	
	
");

// 1.1.3

$smcFunc['db_query']('', "REPLACE INTO {db_prefix}mediapro_sites
(ID,title, website, height,width,  regexmatch, embedcode)
VALUES
(70, 'Google Maps', 'http://maps.google.com/', 350,425, 'htt(p|ps)://(maps" . '\\' .'\\' . ".google" . '\\' .'\\' . ".[^" . '"' . ">]+/" . '\\' .'\\' . "w*?" . '\\' .'\\' . "?[^" . '"' . ">]+)','" . '
<iframe width="480" height="600" frameborder="0" scrolling="no" marginheight="0" marginwidth="0" src="htt$1://$2&output=svembed"></iframe>
' . "'),
(71, 'Youtube Short Url','http://www.youtube.com', 385,640, 'htt(p|ps)://[w" . '\\' .'\\' . ".]*youtu" . '\\' .'\\' . ".be/([-a-zA-Z0-9&;+=_]*)','" . '<iframe title="YouTube video player" width="480" height="600" src="http://www.youtube.com/embed/$2?rel=0" frameborder="0" allowfullscreen></iframe>' . "')

");

// 2.0 Local Streaming
global $boardurl;
$smcFunc['db_query']('', "REPLACE INTO {db_prefix}mediapro_sites
	(ID,title, website, height,width,  regexmatch, embedcode)
VALUES
(72, 'Local FLV', '', 350,425, 'http://(.*).flv','" . '
<object class="playerpreview" type="application/x-shockwave-flash" data="' . $boardurl . '/videos/player_flv_maxi.swf" width="480" height="600">
                <param name="movie" value="' . $boardurl . '/videos/player_flv_maxi.swf" />
                <param name="allowFullScreen" value="true" />
                <param name="FlashVars" value="flv=http://$1.flv&amp;width=480&amp;height=600&amp;showfullscreen=1;showstop=1&amp;showvolume=1&amp;showtime=1&amp;bgcolor1=000000&amp;bgcolor2=000000&amp;playercolor=000000" />
</object>
' . "'),
(73, 'Local SWF', '', 350,425, 'http://(.*).swf','" . '
<object width="480" height="600"
			  classid="clsid:d27cdb6e-ae6d-11cf-96b8-444553540000"
			  codebase="http://fpdownload.macromedia.com/pub/
			  shockwave/cabs/flash/swflash.cab#version=8,0,0,0">
			  <param name="movie" value="http://$1.swf" />
			  <embed src="http://$1.swf" width="480" height="600"
			  type="application/x-shockwave-flash" pluginspage=
			  "http://www.macromedia.com/go/getflashplayer" />
</object>
' . "'),
(74, 'Local MOV', '', 350,425, 'http://(.*).mov','" . '
<OBJECT CLASSID="clsid:02BF25D5-8C17-4B23-BC80-D3488ABDDC6B"

CODEBASE="http://www.apple.com/qtactivex/qtplugin.cab" height="640" width="480">


<PARAM NAME="src" VALUE="http://$1.mov" >
<PARAM NAME="AutoPlay" VALUE="true" >
<PARAM NAME="Controller" VALUE="false" >
<EMBED SRC="http://$1.mov" height="600" width="480" TYPE="video/quicktime" PLUGINSPAGE="http://www.apple.com/quicktime/download/" AUTOPLAY="true" CONTROLLER="false" />
</OBJECT>
' . "'),
(75, 'Local MP4', '', 350,425, 'http://(.*).mp4','" . '
<OBJECT CLASSID="clsid:02BF25D5-8C17-4B23-BC80-D3488ABDDC6B"

CODEBASE="http://www.apple.com/qtactivex/qtplugin.cab" height="640" width="480">


<PARAM NAME="src" VALUE="http://$1.mp4" >
<PARAM NAME="AutoPlay" VALUE="true" >
<PARAM NAME="Controller" VALUE="false" >
<EMBED SRC="http://$1.mp4" height="600" width="480" TYPE="video/quicktime" PLUGINSPAGE="http://www.apple.com/quicktime/download/" AUTOPLAY="true" CONTROLLER="false" />
</OBJECT>
' . "'),
(76, 'Local RM', '', 350,425, 'http://(.*).rm','" . '
<OBJECT ID=RVOCX CLASSID="clsid:CFCDAA03-8BE4-11cf-B84B-0020AFBBCCFA" 
  width="480" height="600">
<PARAM NAME="SRC" VALUE="http://$1.rm">
<PARAM NAME="CONTROLS" VALUE="ImageWindow">
<PARAM NAME="CONSOLE" VALUE="one">
<EMBED SRC="http://$1.rm" width="480" height="600" NOJAVA=true
   CONSOLE=one AUTOSTART=true CONTROLS=ControlPanel>
</OBJECT>
<EMBED SRC="http://$1.rm" width="480" height=40 NOJAVA=true CONTROLS=ControlPanel CONSOLE=one>

' . "'),
(77, 'Local RAM', '', 350,425, 'http://(.*).ram','" . '
<OBJECT ID=RVOCX CLASSID="clsid:CFCDAA03-8BE4-11cf-B84B-0020AFBBCCFA" 
  width="480" height="600">
<PARAM NAME="SRC" VALUE="http://$1.ram">
<PARAM NAME="CONTROLS" VALUE="ImageWindow">
<PARAM NAME="CONSOLE" VALUE="one">
<EMBED SRC="http://$1.ram" width="480" height="600" NOJAVA=true
   CONSOLE=one AUTOSTART=true CONTROLS=ControlPanel>
</OBJECT>
<EMBED SRC="http://$1.ram" width="480" height=40 NOJAVA=true CONTROLS=ControlPanel CONSOLE=one>

' . "'),
(78, 'Local RA', '', 350,425, 'http://(.*).ra','" . '
<OBJECT ID=RVOCX CLASSID="clsid:CFCDAA03-8BE4-11cf-B84B-0020AFBBCCFA" 
  width="480" height="600">
<PARAM NAME="SRC" VALUE="http://$1.ra">
<PARAM NAME="CONTROLS" VALUE="ImageWindow">
<PARAM NAME="CONSOLE" VALUE="one">
<EMBED SRC="http://$1.ra" width="480" height="600" NOJAVA=true
   CONSOLE=one AUTOSTART=true CONTROLS=ControlPanel>
</OBJECT>
<EMBED SRC="http://$1.ra" width="480" height=40 NOJAVA=true CONTROLS=ControlPanel CONSOLE=one>

' . "'),
(79, 'Local AVI', '', 350,425, 'http://(.*).avi','" . '
<OBJECT ID="MediaPlayer" width="480" height="600" CLASSID="CLSID:22D6F312-B0F6-11D0-94AB-0080C74C7E95"
STANDBY="Loading Windows Media Player components..." TYPE="application/x-oleobject">
<PARAM NAME="FileName" VALUE="http://$1.avi">
<PARAM name="autostart" VALUE="true">
<PARAM name="ShowControls" VALUE="true">
<param name="ShowStatusBar" value="false">
<PARAM name="ShowDisplay" VALUE="false">
<EMBED TYPE="application/x-mplayer2" SRC="http://$1.avi" NAME="MediaPlayer"
width="480" height="600" ShowControls="1" ShowStatusBar="0" ShowDisplay="0" autostart="0"> </EMBED>
</OBJECT>
' . "'),
(80, 'Local WMV', '', 350,425, 'http://(.*).wmv','" . '
<OBJECT ID="MediaPlayer" width="480" height="600" CLASSID="CLSID:22D6F312-B0F6-11D0-94AB-0080C74C7E95"
STANDBY="Loading Windows Media Player components..." TYPE="application/x-oleobject">
<PARAM NAME="FileName" VALUE="http://$1.wmv">
<PARAM name="autostart" VALUE="true">
<PARAM name="ShowControls" VALUE="true">
<param name="ShowStatusBar" value="false">
<PARAM name="ShowDisplay" VALUE="false">
<EMBED TYPE="application/x-mplayer2" SRC="http://$1.wmv" NAME="MediaPlayer"
width="480" height="600" ShowControls="1" ShowStatusBar="0" ShowDisplay="0" autostart="0"> </EMBED>
</OBJECT>
' . "')
");

$smcFunc['db_query']('', "REPLACE INTO {db_prefix}mediapro_sites
	(ID,title, website, height,width,  regexmatch, embedcode)
VALUES
(81, 'Local WMA', '', 350,425, 'http://(.*).wma','" . '
<OBJECT ID="MediaPlayer" width="480" height="600" CLASSID="CLSID:22D6F312-B0F6-11D0-94AB-0080C74C7E95"
STANDBY="Loading Windows Media Player components..." TYPE="application/x-oleobject">
<PARAM NAME="FileName" VALUE="http://$1.wma">
<PARAM name="autostart" VALUE="true">
<PARAM name="ShowControls" VALUE="true">
<param name="ShowStatusBar" value="false">
<PARAM name="ShowDisplay" VALUE="false">
<EMBED TYPE="application/x-mplayer2" SRC="http://$1.wma" NAME="MediaPlayer"
width="480" height="600" ShowControls="1" ShowStatusBar="0" ShowDisplay="0" autostart="0"> </EMBED>
</OBJECT>
' . "')
");


$smcFunc['db_query']('', "REPLACE INTO {db_prefix}mediapro_sites
	(ID,title, website, height,width,  regexmatch, embedcode)
VALUES
(82, 'own3d.tv', 'http://www.own3d.tv', 360,640, 'http://www.own3d.tv/video/([0-9]*)/(.*)','" . '
<object width="480" height="600">
    <param name="movie" value="http://www.own3d.tv/stream/$1" />
    <param name="allowscriptaccess" value="always" />
    <param name="allowfullscreen" value="true" />
    <param name="wmode" value="transparent" />
    <embed src="http://www.own3d.tv/stream/$1" type="application/x-shockwave-flash" allowfullscreen="true" allowscriptaccess="always" width="640" height="360" wmode="transparent"></embed>
</object>
' . "')


");


$smcFunc['db_query']('', "REPLACE INTO {db_prefix}mediapro_sites
	(ID,title, website, height,width,  regexmatch, embedcode)
VALUES
(83, 'Facebook Video','http://www.facebook.com', 385,640, 'htt(p|ps)://www" . '\\' .'\\' . ".facebook" . '\\' .'\\' . ".com/video/embed" . '\\' .'\\' . "?video_id=([" . '\\' .'\\' . "w]+)&*[" . '\\' .'\\' . "w;=]*','" . '
<strong><div id="fb-root"></div> <script>(function(d, s, id) { var js, fjs = d.getElementsByTagName(s)[0]; if (d.getElementById(id)) return; js = d.createElement(s); js.id = id; js.src = "//connect.facebook.net/en_US/all.js#xfbml=1"; fjs.parentNode.insertBefore(js, fjs); }(document,"script", "facebook-jssdk"));</script>
<div class="fb-post" data-href="https://www.facebook.com/photo.php?v=$2" data-width="550"><div class="fb-xfbml-parse-ignore"><a href="https://www.facebook.com/photo.php?v=$2">Post</a> 
</div></div>
</strong>'
 . "'),

(84, 'Facebook Pictures','http://www.facebook.com', 385,640, 'htt(p|ps)://www" . '\\' .'\\' . ".facebook" . '\\' .'\\' . ".com/photo.php" . '\\' .'\\' . "?v=([" . '\\' .'\\' . "w]+)&*[" . '\\' .'\\' . "w;=]*','" . '
<strong><div id="fb-root"></div> <script>(function(d, s, id) { var js, fjs = d.getElementsByTagName(s)[0]; if (d.getElementById(id)) return; js = d.createElement(s); js.id = id; js.src = "//connect.facebook.net/en_US/all.js#xfbml=1"; fjs.parentNode.insertBefore(js, fjs); }(document,"script", "facebook-jssdk"));</script>
<div class="fb-post" data-href="https://www.facebook.com/photo.php?v=$2" data-width="550"><div class="fb-xfbml-parse-ignore"><a href="https://www.facebook.com/photo.php?v=$2">Post</a> 
</div></div>
</strong>'
 . "')
");

$smcFunc['db_query']('', "REPLACE INTO {db_prefix}mediapro_sites
	(ID,title, website, height,width,  regexmatch, embedcode)
VALUES
(85, 'Vine','http://www.vine.com', 600,600, 'htt(p|ps)://vine.co/v/([A-Za-z0-9]*)/(.*)','" . '<iframe class="vine-embed" src="https://vine.co/v/$2/embed/simple" width="480" height="600" frameborder="0"></iframe><script async src="//platform.vine.co/static/scripts/embed.js" charset="utf-8"></script>' . "'),
(86, 'Seenive','http://www.seenive.com', 480,480, 'htt(p|ps)://seenive.com/v/([0-9]*)','" . '<iframe width="480" height="600" src="http://seenive.com/v/$2/embed?mute=0" frameborder="0"></iframe>' . "')

");


$smcFunc['db_query']('', "REPLACE INTO {db_prefix}mediapro_sites
	(ID,title, website, height,width,  regexmatch, embedcode)
VALUES
(87, 'SoundCloud','http://www.soundcloud.com', 600,600, 'htt(p|ps)://w.soundcloud.com/player/" . '\\' .'\\' . "?url=https://api.soundcloud.com/tracks/([A-Za-z0-9]*)','" . '<iframe width="100%" height="166" scrolling="no" frameborder="no" src="https://w.soundcloud.com/player/?url=https%3A//api.soundcloud.com/tracks/$2"></iframe>' . "')
");

$smcFunc['db_query']('', "REPLACE INTO {db_prefix}mediapro_sites
	(ID,title, website, height,width,  regexmatch, embedcode)
VALUES
(88, 'Jpopsuki.tv','http://www.jpopsuki.tv', 406,720, 'htt(p|ps)://www.jpopsuki.tv/video/(.*)/([A-Za-z0-9]*)','" . '<iframe src="http://www.jpopsuki.tv/media/embed?key=$3&width=480&height=600&autoplay=false&autolightsoff=false&loop=false" width="480" height="600" frameborder="0" allowfullscreen="allowfullscreen" allowtransparency="true" scrolling="no"></iframe>' . "')
");

global $smcFunc, $boarddir;

   // Make sure still enabled
    if (!empty($enabledList))
    {
        $enableSQL = implode(",",$enabledList);
        
        $smcFunc['db_query']('', "UPDATE {db_prefix}mediapro_sites SET enabled = 1
    	WHERE id IN ($enableSQL)");
   
    }
    


	$mediaProItems = array();

	// Get list of sites that are enabled
	$result = $smcFunc['db_query']('', "
	SELECT
		id, title, website, regexmatch,
		embedcode, height,  width
	FROM {db_prefix}mediapro_sites
	WHERE enabled = 1");
	while ($row = $smcFunc['db_fetch_assoc']($result))
	{
		$mediaProItems[] = $row;
	}

	// Data to write
	$data = '<?php
$mediaProCache = \'' . serialize($mediaProItems)  . '\';
?>';

	// Write the cache to the file
	$fp = fopen($boarddir . "/cache/mediaprocache.php", 'w');
	if ($fp)
	{
		fwrite($fp, $data);
	}

	fclose($fp);
	
?>