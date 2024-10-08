<?php
/*
Simple Audio Video Embedder
Version 4.5
by:vbgamer45
https://www.smfhacks.com

License Information:
Links to https://www.smfhacks.com must remain unless
branding free option is purchased.
*/
ini_set("display_errors",1);
if (file_exists(dirname(__FILE__) . '/SSI.php') && !defined('SMF'))
  require_once(dirname(__FILE__) . '/SSI.php');
// Hmm... no SSI.php and no SMF?
elseif (!defined('SMF'))
  die('<b>Error:</b> Cannot install - please verify you put this in the same place as SMF\'s index.php.');

// remove old xss flv player
if (file_exists($boarddir . "/videos/player_flv_maxi.swf"))
 	unlink($boarddir . "/videos/player_flv_maxi.swf");


// Set up default settings
db_query("INSERT IGNORE INTO {$db_prefix}settings VALUES ('mediapro_default_width', '0')", __FILE__, __LINE__);
db_query("INSERT IGNORE INTO {$db_prefix}settings VALUES ('mediapro_default_height','0')", __FILE__, __LINE__);

db_query("INSERT IGNORE INTO {$db_prefix}settings VALUES ('mediapro_copyrightkey','')", __FILE__, __LINE__);
db_query("INSERT IGNORE INTO {$db_prefix}settings VALUES ('mediapro_disablesig','0')", __FILE__, __LINE__);
db_query("INSERT IGNORE INTO {$db_prefix}settings VALUES ('mediapro_disablemobile','0')", __FILE__, __LINE__);
db_query("INSERT IGNORE INTO {$db_prefix}settings VALUES ('mediapro_usecustomdiv','0')", __FILE__, __LINE__);
db_query("INSERT IGNORE INTO {$db_prefix}settings VALUES ('mediapro_divclassname','embedsave')", __FILE__, __LINE__);

db_query("INSERT IGNORE INTO {$db_prefix}settings VALUES ('mediapro_showlink','0')", __FILE__, __LINE__);
db_query("INSERT IGNORE INTO {$db_prefix}settings VALUES ('mediapro_max_embeds','0')", __FILE__, __LINE__);


db_query("REPLACE INTO {$db_prefix}settings VALUES ('autoLinkUrls','1')", __FILE__, __LINE__);



db_query("CREATE TABLE IF NOT EXISTS {$db_prefix}mediapro_sites (
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
) ", __FILE__, __LINE__);


$enabledList = array();
	$result = db_query("
	SELECT
		id, title, website, regexmatch,
		embedcode, height,  width
	FROM {$db_prefix}mediapro_sites
	WHERE enabled = 1 ORDER BY title ASC", __FILE__, __LINE__);
	while ($row = mysql_fetch_assoc($result))
	{
		$enabledList[] = $row['id'];
	}




db_query("REPLACE INTO {$db_prefix}mediapro_sites
	(ID,title, website, height,width,  regexmatch, embedcode)
VALUES
(1, 'Youtube','http://www.youtube.com', 385,640, 'htt(p|ps)://[" . '\\' .'\\' . "w.]+youtube" . '\\' .'\\' . ".[" . '\\' .'\\' . "w]+/watch[(" . '\\' .'\\' . "?|" . '\\' .'\\' . "?feature=player_embedded&amp;)" . '\\' .'\\' . "#!]+v=([" . '\\' .'\\' . "w-]+)[" . '\\' .'\\' . "w&;+-]*[" . '\\' .'\\' . "#&;t=]*([" . '\\' .'\\' . "d]*)[&;10wshdq=]*','" . '<iframe width="480" height="600" src="//www.youtube.com/embed/$2?fs=1&start=$3" frameborder="0" allowfullscreen="true"></iframe>' . "'),

(2, 'Metacafe','http://www.metacafe.com', 334,540, 'http://www" . '\\' .'\\' . ".metacafe" . '\\' .'\\' . ".com/watch/([" . '\\' .'\\' . "w-]+/[" . '\\' .'\\' . "w_]*)[" . '\\' .'\\' . "w&;=" . '\\' .'\\' . "+_" . '\\' .'\\' . "-" . '\\' .'\\' . "/]*','" . '<embed src="http://www.metacafe.com/fplayer/$1.swf" width="480" height="600" wmode="transparent" pluginspage="http://www.macromedia.com/go/getflashplayer" type="application/x-shockwave-flash" wmode="transparent"></embed>' . "'),

(3, 'Facebook','http://www.facebook.com', 385,640, 'http://www" . '\\' .'\\' . ".facebook" . '\\' .'\\' . ".com/video/video" . '\\' .'\\' . ".php" . '\\' .'\\' . "?v=([" . '\\' .'\\' . "w]+)&*[" . '\\' .'\\' . "w;=]*','" . '<object width="480" height="600" >
       <param name="allowfullscreen" value="true" />
       <param name="allowscriptaccess" value="always" />
       <param name="movie" value="http://www.facebook.com/v/$1" />
       <embed src="http://www.facebook.com/v/$1" type="application/x-shockwave-flash" allowscriptaccess="always" allowfullscreen="true" width="480" height="600"></embed></object>' . "'),

(4, 'Vimeo','http://www.vimeo.com', 385,640, 'htt(p|ps)://[w" . '\\' .'\\' . ".]*vimeo" . '\\' .'\\' . ".com/([" . '\\' .'\\' . "d]+)[" . '\\' .'\\' . "w&;=" . '\\' .'\\' . "?+%/-]*','" . '<iframe src="//player.vimeo.com/video/$2" width="480" height="600" frameborder="0" webkitallowfullscreen="true" mozallowfullscreen="true" allowFullScreen="true"></iframe>' . "'),


(6, 'Google Video', 'http://video.google.com',  385,640,'[http://]*video" . '\\' .'\\' . ".google" . '\\' .'\\' . ".[" . '\\' .'\\' . "w.]+/videoplay" . '\\' .'\\' . "?docid=([-" . '\\' .'\\' . "d]+)[&" . '\\' .'\\' . "w;=" . '\\' .'\\' . "+.-]*','" . '<embed style="width:480px; height:600px;" id="VideoPlayback" type="application/x-shockwave-flash" src="http://video.google.com/googleplayer.swf?docId=$1" flashvars="" wmode="transparent"> </embed>' . "')

", __FILE__, __LINE__);



db_query("REPLACE INTO {$db_prefix}mediapro_sites
	(ID,title, website, height,width,  regexmatch, embedcode)
VALUES
(7, 'Veoh', 'http://www.veoh.com', 341,410, 'http://www" . '\\' .'\\' . ".veoh" . '\\' .'\\' . ".com/(.*)/watch/([A-Z0-9]*)','" . '<object width="480" height="600" id="veohFlashPlayer" name="veohFlashPlayer"><param name="movie" value="http://www.veoh.com/static/swf/webplayer/WebPlayer.swf?version=AFrontend.5.5.2.1066&permalinkId=$2&player=videodetailsembedded&videoAutoPlay=0&id=anonymous"></param><param name="allowFullScreen" value="true"></param><param name="allowscriptaccess" value="always"></param><embed src="http://www.veoh.com/static/swf/webplayer/WebPlayer.swf?version=AFrontend.5.5.2.1066&permalinkId=$2&player=videodetailsembedded&videoAutoPlay=0&id=anonymous" type="application/x-shockwave-flash" allowscriptaccess="always" allowfullscreen="true" width="480" height="600" id="veohFlashPlayerEmbed" name="veohFlashPlayerEmbed"></embed></object>' . "'),
(8, 'Youku', 'http://www.youku.com', 400,480, 'http://([A-Z0-9]*).youku.com/v_show/id_([A-Z0-9]*).html','" . '
<embed src="http://player.youku.com/player.php/sid/$2/v.swf" quality="high" width="480" height="600" align="middle" allowScriptAccess="sameDomain" type="application/x-shockwave-flash"></embed>' . "')


", __FILE__, __LINE__);



//1.0.2
db_query("REPLACE INTO {$db_prefix}mediapro_sites
	(ID,title, website, height,width,  regexmatch, embedcode)
VALUES

(10, 'Rutube', 'http://rutube.ru',353,470, 'http://rutube.ru/tracks/([A-Z0-9]*).html" . '\\' .'\\' . "?v=([A-Z0-9]*)','" . '
<OBJECT width="480" height="600"><PARAM name="movie" value="http://video.rutube.ru/$2"></PARAM><PARAM name="wmode" value="window"></PARAM><PARAM name="allowFullScreen" value="true"></PARAM><EMBED src="http://video.rutube.ru/$2" type="application/x-shockwave-flash" wmode="window" width="480" height="600" allowFullScreen="true" ></EMBED></OBJECT>
' . "'),

(13, 'LiveLeak', 'http://www.liveleak.com', 370,450, 'htt(p|ps)://www.liveleak.com/view" . '\\' .'\\' . "?i=([^<>]+)','" . '
<object width="480" height="600"><param name="movie" value="htt$1://www.liveleak.com/e/$2"></param><param name="wmode" value="transparent"></param><param name="allowscriptaccess" value="always"></param><embed src="htt$1://www.liveleak.com/e/$2" type="application/x-shockwave-flash" wmode="transparent" allowscriptaccess="always" width="480" height="600"></embed></object>

' . "'),


(16, 'Funnyordie.com', 'http://www.funnyordie.com', 400,480, 'http://www.funnyordie.com/videos/([A-Z0-9]*)/([^<>]+)','" . '
<object width="480" height="600" classid="clsid:d27cdb6e-ae6d-11cf-96b8-444553540000" id="ordie_player_$1"><param name="movie" value="http://player.ordienetworks.com/flash/fodplayer.swf" /><param name="flashvars" value="key=$1" /><param name="allowfullscreen" value="true" /><param name="allowscriptaccess" value="always"></param><embed width="480" height="600" flashvars="key=$1" allowfullscreen="true" allowscriptaccess="always" quality="high" src="http://player.ordienetworks.com/flash/fodplayer.swf" name="ordie_player_$1" type="application/x-shockwave-flash"></embed></object>

' . "')


", __FILE__, __LINE__);
//1.0.3

db_query("REPLACE INTO {$db_prefix}mediapro_sites
	(ID,title, website, height,width,  regexmatch, embedcode)
VALUES

(18, 'Crackle', 'http://www.crackle.com', 281,500, 'http://www.crackle.com/c/(.*)/(.*)/([0-9]*)','" . '
<embed src="http://www.crackle.com/p/$1/$2.swf" quality="high" bgcolor="#869ca7" width="480" height="600" name="mtgPlayer" align="middle" play="true" loop="false" allowFullScreen="true" flashvars="id=$3&mu=0&ap=0" quality="high" allowScriptAccess="always" type="application/x-shockwave-flash" pluginspage="http://www.adobe.com/go/getflashplayer"> </embed>

' . "'),


(20, 'SchoolTube', 'http://www.schooltube.com', 375,500, 'http://www.schooltube.com/video/([A-Z0-9]*)/([^<>]+)','" . '
<object width="480" height="600"><param name="movie" value="http://www.schooltube.com/v/$1" /><param name="allowFullScreen" value="true" /><param name="allowscriptaccess" value="always" /><embed src="http://www.schooltube.com/v/16483926ba522476e7ae" type="application/x-shockwave-flash" allowFullScreen="true" allowscriptaccess="always" width="480" height="600" FlashVars="gig_lt=1281633293655&gig_pt=1281633309820&gig_g=2"></embed> <param name="FlashVars" value="gig_lt=1281633293655&gig_pt=1281633309820&gig_g=2" /></object>

' . "'),


(21, 'MySpace', 'http://www.myspace.com', 360,425, 'http://vids.myspace.com/index.cfm" . '\\' .'\\' . "?fuseaction=vids.individual" . '\\' .'\\' . "&amp;videoid=([0-9]*)','" . '
<object width="480" height="600"><param name="allowFullScreen" value="true"/><param name="wmode" value="transparent"/><param name="movie" value="http://mediaservices.myspace.com/services/media/embed.aspx/m=$1,t=1,mt=video"/><embed src="http://mediaservices.myspace.com/services/media/embed.aspx/m=$1,t=1,mt=video" width="480" height="600" allowFullScreen="true" type="application/x-shockwave-flash" wmode="transparent"></embed></object>

' . "'),

(22, 'Mefeedia', 'http://www.mefeedia.com', 450,640, 'http://www.mefeedia.com/watch/([0-9]*)','" . '
<iframe scrolling="no" frameborder="0" width="480" height="600" src="http://www.mefeedia.com/watch/$1&iframe"></iframe>
' . "'),


(24, 'DailyMotion', 'http://www.dailymotion.com', 360,480, 'htt(p|ps)://www.dailymotion.com/video/([A-Z0-9]*)_([^<>]+)ZSPLITMZhtt(p|ps)://www.dailymotion.com/video/([A-Z0-9]*)ZSPLITMZhtt(p|ps)://dai.ly/([A-Z0-9]*)','" . '
<iframe frameborder="0" width="480" height="600" src="//www.dailymotion.com/embed/video/$2" allowfullscreen="true"></iframe>
' . "'),


(27, 'Clipmoon', 'http://www.clipmoon.com', 357,460, 'http://www.clipmoon.com/videos/([0-9]*)/(.*).html','" . '
<embed src="http://www.clipmoon.com/flvplayer.swf" FlashVars="config=http://www.clipmoon.com/flvplayer.php?viewkey=$1&external=no" quality="high" bgcolor="#000000" wmode="transparent" width="480" height="600" loop="false" align="middle" type="application/x-shockwave-flash" pluginspage="http://www.macromedia.com/go/getflashplayer"  scale="exactfit" > </embed>

' . "')


", __FILE__, __LINE__);


/*
(33, 'MegaVideo', 'http://www.megavideo.com', 344,640, 'http://(.*)megavideo.com/" . '\\' .'\\' . "?v=([A-Z_0-9]*)','" . '
<object width="640" height="344"><param name="movie" value="http://www.megavideo.com/v/$2.0.0"></param><param name="allowFullScreen" value="true"></param><embed src="http://www.megavideo.com/v/$2.0.0" type="application/x-shockwave-flash" allowfullscreen="true" width="480" height="600"></embed></object>
' . "'),
*/


// 1.0.4
db_query("REPLACE INTO {$db_prefix}mediapro_sites
	(ID,title, website,height,width,  regexmatch, embedcode)
VALUES
(29, 'Mail.ru', 'http://www.mail.ru', 367,626, 'http://video.mail.ru/mail/([0-9]*)/([0-9]*)/([0-9]*).html','" . '
<object width="480" height="600"><param name="allowScriptAccess" value="always" /><param name="movie" value="http://img.mail.ru/r/video2/player_v2.swf?movieSrc=mail/$1/$2/$3" /><embed src=http://img.mail.ru/r/video2/player_v2.swf?movieSrc=mail/$1/$2/$3 type="application/x-shockwave-flash" width="480" height="600" allowScriptAccess="always"></embed></object>
' . "'),
(31, 'Trtube', 'http://www.trtube.com', 350,425, 'http://www.trtube.com/(.*)-([0-9]*).html','" . '
<object width="480" height="600"><param name="allowScriptAccess" value="always"><param name="movie" value="http://www.trtube.com/mediaplayer_3_15.swf?file=http://www.trtube.com/playlist.php?v=$2&image=http://resim.trtube.com/a/102/$2.gif&logo=http://load.trtube.com/img/logoembed.gif&linkfromdisplay=false&linktarget=_blank&autostart=false"><param name="quality" value="high"><param name="bgcolor" value="#ffffff"><param name="allowfullscreen" value="true"><embed src="http://www.trtube.com/mediaplayer_3_15.swf?file=http://www.trtube.com/playlist.php?v=$2&image=http://resim.trtube.com/a/102/$2.gif&logo=http://load.trtube.com/img/logoembed.gif&linkfromdisplay=false&linktarget=_blank&autostart=false" quality="high" bgcolor="#ffffff" allowfullscreen="true" width="480" height="600" name="player" align="middle" type="application/x-shockwave-flash" allowScriptAccess="always" pluginspage="http://www.macromedia.com/go/getflashplayer"></object>
' . "'),
(34, 'VH1', 'http://www.vh1.com', 319,512, 'http://www.vh1.com/video/(.*)/([0-9]*)/(.*).jhtml(.*?)','" . '
<embed src="http://media.mtvnservices.com/mgid:uma:video:vh1.com:$2" width="480" height="600" wmode="transparent" type="application/x-shockwave-flash" flashVars="configParams=vid%3D$2%26uri%3Dmgid%3Auma%3Avideo%3Avh1.com%3A$2%26instance%3Dvh1" allowFullScreen="true" allowScriptAccess="always" base="."></embed>
' . "'),
(35, 'BET', 'http://www.bet.com', 319,512, 'http://www.bet.com/video/([0-9]*)','" . '
<embed src="http://media.mtvnservices.com/mgid:media:video:bet.com:$1" width="480" height="600" wmode="transparent" type="application/x-shockwave-flash" flashVars="" allowFullScreen="true" allowScriptAccess="always" base="."></embed>
' . "')


", __FILE__, __LINE__);

// 1.0.5

db_query("REPLACE INTO {$db_prefix}mediapro_sites
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
(40, 'Comedy Central',  'http://www.comedycentral.com', 301,360, 'http://www.comedycentral.com/videos/index.jhtml" . '\\' .'\\' . "?videoId=([0-9]*)" . '\\' .'\\' . "&amp;title=(.*)','" . '
<embed style="display:block" src="http://media.mtvnservices.com/mgid:cms:item:comedycentral.com:$1" width="360" height="301" type="application/x-shockwave-flash" wmode="window" allowFullscreen="true" flashvars="autoPlay=false" allowscriptaccess="always" allownetworking="all" bgcolor="#000000"></embed>

' . "')


", __FILE__, __LINE__);

// 1.0.6

db_query("REPLACE INTO {$db_prefix}mediapro_sites
	(ID,title, website, height,width, regexmatch, embedcode)
VALUES

(42, 'BlogDumpsVideo', 'http://www.blogdumpsvideo.com', 350,600, 'http://www.blogdumpsvideo.com/action/viewvideo/([0-9]*)/(.*)/','" . '
<embed src="http://www.blogdumpsvideo.com/HDplayer.swf" FlashVars="config=http://www.blogdumpsvideo.com/videoConfigXmlCodeHD.php?pg=video_$1_no_0_extsite&playList=http://www.blogdumpsvideo.com/videoPlaylistXmlCodeHD.php?pg=video_$1" quality="high" bgcolor="#000000" width="480" height="600" name="flvplayer" align="middle" allowScriptAccess="always" type="application/x-shockwave-flash" pluginspage="http://www.macromedia.com/go/getflashplayer" allowFullScreen="true" />
' . "'),
(43, 'Reuters', 'http://www.reuters.com', 259, 460, 'http://www.reuters.com/news/video/story" . '\\' .'\\' . "?videoId=([0-9]*)([^<>]+)','" . '
<object type="application/x-shockwave-flash" data="http://www.reuters.com/resources_v2/flash/video_embed.swf?videoId=$1" width="480" height="600"><param name="movie" value="http://www.reuters.com/resources_v2/flash/video_embed.swf?videoId=$1"></param><param name="allowFullScreen" value="true"></param><param name="allowScriptAccess" value="always"></param><param name="wmode" value="transparent"><embed src="http://www.reuters.com/resources_v2/flash/video_embed.swf?videoId=$1" type="application/x-shockwave-flash" allowfullscreen="true" allowScriptAccess="always" width="480" height="600" wmode="transparent"></embed></object>
' . "'),
(44, 'MarketNewsVideo', 'http://www.marketnewsvideo.com', 320,400, 'http://www.marketnewsvideo.com/embed/" . '\\' .'\\' . "?id=([A-Z_0-9]*)([^<>]+)','" . '
<iframe src="http://www.marketnewsvideo.com/?id=$1&mv=1&embed=1&width=400&height=320" frameborder="0" width="480" height="600" marginheight="0" marginwidth="0" scrolling="no" name="mnv1282073139"></iframe>
' . "'),
(45, 'Clipsyndicate PlayLists', 'http://www.clipsyndicate.com', 330,425, 'http://www.clipsyndicate.com/video/playlist/([0-9]*)/([0-9]*)([^<>]+)','" . '
<object classid="clsid:D27CDB6E-AE6D-11cf-96B8-444553540000" id="cs_player" width="480" height="600"><param name="movie" value="http://eplayer.clipsyndicate.com/cs_api/get_swf/3/&amp;pl_id=$1&amp;page_count=5&amp;windows=1&amp;show_title=0&amp;va_id=$2&amp;rwpid=273&amp;auto_start=0&amp;auto_next=1" /><param name="allowfullscreen" value="true" /><param name="allowscriptaccess" value="always" /><embed src="http://eplayer.clipsyndicate.com/cs_api/get_swf/3/&amp;pl_id=$1&amp;page_count=5&amp;windows=1&amp;show_title=0&amp;va_id=$2&amp;rwpid=273&amp;auto_start=0&amp;auto_next=1" type="application/x-shockwave-flash" allowscriptaccess="always" allowfullscreen="true" width="480" height="600" /></object>
' . "'),
(46, 'NyTimes', 'http://video.nytimes.com', 373,480, 'http://video.nytimes.com/video/([0-9]*)/([0-9]*)/([0-9]*)/([A-Z_0-9]*)/([0-9]*)/([^<>]+)','" . '
<iframe width="480" height="600" frameborder="0" scrolling="no" marginheight="0" marginwidth="0" id="nyt_video_player" title="New York Times Video - Embed Player" src="http://graphics8.nytimes.com/bcvideo/1.0/iframe/embed.html?videoId=$5&playerType=embed"></iframe>

' . "')

", __FILE__, __LINE__);


db_query("REPLACE INTO {$db_prefix}mediapro_sites
	(ID,title, website,height,width,  regexmatch, embedcode)
VALUES
(49, 'Izlesene.com', 'http://www.izlesene.com', 300,400, 'http://www.izlesene.com/video/(.*)/([0-9]*)','" . '
<object width="480" height="600"><param name="allowfullscreen" value="true" /><param name="allowscriptaccess" value="always" /><param name="movie" value="http://www.izlesene.com/embedplayer.swf?video=$2" /><embed src="http://www.izlesene.com/embedplayer.swf?video=$2" wmode="window" bgcolor="#000000" allowfullscreen="true" allowscriptaccess="always" menu="false" scale="noScale" width="480" height="600" type="application/x-shockwave-flash"></embed></object>
' . "'),
(50, 'Rambler.ru', 'http://www.rambler.ru', 370,390, 'http://vision.rambler.ru/users/([A-Z0-9]*)/([0-9]*)/([0-9]*)/','" . '
<object classid="clsid:d27cdb6e-ae6d-11cf-96b8-444553540000" codebase="http://fpdownload.macromedia.com/pub/shockwave/cabs/flash/swflash.cab#version=7,0,0,0" width="480" height="600"><param name="wmode" value="transparent"/><param name="allowFullScreen" value="true"/><param name="movie" value="http://vision.rambler.ru/i/e.swf?id=$1/$2/$3&logo=1" /><embed src="http://vision.rambler.ru/i/e.swf?id=$1/$2/$3&logo=1" width="480" height="600" type="application/x-shockwave-flash" wmode="transparent" allowFullScreen="true" /></object>
' . "'),
(53, 'Yahoo', 'http://video.yahoo.com', 322,512, 'http://video.yahoo.com/watch/([0-9]*)/([0-9]*)','" . '
 <object width="480" height="600""><param name="movie" value="http://d.yimg.com/static.video.yahoo.com/yep/YV_YEP.swf?ver=2.2.46" /><param name="allowFullScreen" value="true" /><param name="AllowScriptAccess" VALUE="always" /><param name="bgcolor" value="#000000" /><param name="flashVars" value="id=$2&vid=$1&lang=en-us&intl=us&thumbUrl=http%3A//l.yimg.com/a/i/us/sch/cn/video05/$1_rnd3230d645_19.jpg&embed=1" /><embed src="http://d.yimg.com/static.video.yahoo.com/yep/YV_YEP.swf?ver=2.2.46" type="application/x-shockwave-flash" width="480" height="600" allowFullScreen="true" AllowScriptAccess="always" bgcolor="#000000" flashVars="id=$2&vid=$1&lang=en-us&intl=us&thumbUrl=http%3A//l.yimg.com/a/i/us/sch/cn/video05/$1_rnd3230d645_19.jpg&embed=1" ></embed></object>

' . "'),
(54, 'Bungie.net', 'http://www.bungie.net', 360,640, 'http://www.bungie.net/Silverlight/bungiemediaplayer/embed.aspx" . '\\' .'\\' . "?fid=([0-9]*)','" . '
<iframe src="http://www.bungie.net/Silverlight/bungiemediaplayer/embed.aspx?fid=$1" scrolling="no" style="padding:0;margin:0;border:0;" width="480" height="600" ></iframe>

' . "')
", __FILE__, __LINE__);

// 1.0.10
db_query("REPLACE INTO {$db_prefix}mediapro_sites
	(ID,title, website, height,width, regexmatch, embedcode)
VALUES
(56, 'Worldstarhiphop.com', 'http://www.worldstarhiphop.com', 374,448, 'http://www.worldstarhiphop.com/videos/video.php" . '\\' .'\\' . "?v=([A-Z0-9]*)','" . '
<object width="480" height="600"><param name="movie" value="http://www.worldstarhiphop.com/videos/e/16711680/$1"><param name="allowFullScreen" value="true"></param><embed src="http://www.worldstarhiphop.com/videos/e/16711680/$1" type="application/x-shockwave-flash" allowFullscreen="true" width="480" height="600"></embed></object>
' . "'),
(58, 'JibJab', 'http://www.jibjab.com', 319,425, 'http://www.jibjab.com/view/([A-Za-z0-9]*)','" . '
<object id="A64060" quality="high" data="http://static.jibjabcdn.com/sendables/aa7bc606/client/zero/ClientZero_EmbedViewer.swf?external_make_id=$1" pluginspage="http://www.macromedia.com/go/getflashplayer" type="application/x-shockwave-flash" wmode="transparent" width="480" height="600"><param name="wmode" value="transparent"></param><param name="movie" value="http://aka.zero.jibjab.com/client/zero/ClientZero_EmbedViewer.swf?external_make_id=$1"></param><param name="scaleMode" value="showAll"></param><param name="quality" value="high"></param><param name="allowNetworking" value="all"></param><param name="allowFullScreen" value="true" /><param name="FlashVars" value="external_make_id=$1"></param><param name="allowScriptAccess" value="always"></param></object>
' . "'),
(60, 'IGN', 'http://www.ign.com', 270,480, 'http://www.ign.com/videos/([0-9]*)/([0-9]*)/([0-9]*)/([a-zA-Z0-9_=" . '\\' .'\\' . ""  . "-" . '\\' .'\\' . ""  . "?]*)','" . '
<object id="vid" class="ign-videoplayer" width="480" height="600" data="http://media.ign.com/ev/prod/embed.swf" type="application/x-shockwave-flash"><param name="movie" value="http://media.ign.com/ev/prod/embed.swf" /><param name="allowfullscreen" value="true" /><param name="allowscriptaccess" value="always" /><param name="bgcolor" value="#000000" /><param name="flashvars" value="url=http://www.ign.com/videos/$1/$2/$3/$4"/></object>
' . "'),
(61, 'Joystiq', 'http://www.joystiq.com', 266,437, 'http://www.joystiq.com/video/([a-zA-Z0-9]*)','" . '
<object classid="clsid:D27CDB6E-AE6D-11cf-96B8-444553540000" width="480" height="600" id="viddler"><param name="movie" value="http://www.viddler.com/simple/$1" /><param name="allowScriptAccess" value="always" /><param name="allowFullScreen" value="true" /><param name="flashvars" value="fake=1"/><embed src="http://www.viddler.com/simple/$1" width="480" height="600" type="application/x-shockwave-flash" allowScriptAccess="always" allowFullScreen="true" flashvars="fake=1" name="viddler" ></embed></object>
' . "')

", __FILE__, __LINE__);




// 1.0.12


// 1.1


db_query("REPLACE INTO {$db_prefix}mediapro_sites
	(ID,title, website, height,width,  regexmatch, embedcode)
VALUES
	(67, 'PinkBike.com', 'http://www.pinkbike.com', 375,500, 'http://www.pinkbike.com/video/([0-9]*)/','" . '
<object width="480" height="600"><param name="allowFullScreen" value="true" /><param name="allowScriptAccess" value="always" /><param name="movie" value="http://www.pinkbike.com/v/$1/l/" /><embed src="http://www.pinkbike.com/v/$1/l/" type="application/x-shockwave-flash" width="480" height="600" allowFullScreen="true" allowScriptAccess="always"></embed></object>
' . "'),
(70, 'Google Maps', 'http://maps.google.com/', 350,425, 'htt(p|ps)://(maps" . '\\' .'\\' . ".google" . '\\' .'\\' . ".[^" . '"' . ">]+/" . '\\' .'\\' . "w*?" . '\\' .'\\' . "?[^" . '"' . ">]+)','" . '
<iframe width="480" height="600" frameborder="0" scrolling="no" marginheight="0" marginwidth="0" src="htt$1://$2&output=svembed"></iframe>
' . "'),
(71, 'Youtube Short Url','http://www.youtube.com', 385,640, 'htt(p|ps)://[w" . '\\' .'\\' . ".]*youtu" . '\\' .'\\' . ".be/watch" . '\\' .'\\' . "?v=([-a-zA-Z0-9&;+=_]*)[" . '\\' .'\\' . "w;+=-]*[" . '\\' .'\\' . "#&t=]*([" . '\\' .'\\' . "d]*)[&;10wshdq=]*ZSPLITMZhtt(p|ps)://[w" . '\\' .'\\' . ".]*youtu" . '\\' .'\\' . ".be/([-a-zA-Z0-9&;+=_]*)[" . '\\' .'\\' . "w;+=-]*[" . '\\' .'\\' . "#" . '\\' .'\\' . "?&t=]*([" . '\\' .'\\' . "d]*)[&;10wshdq=]*','" . '<iframe title="YouTube video player" width="480" height="600" src="//www.youtube.com/embed/$2?rel=0&start=$3" frameborder="0" allowFullScreen="true"></iframe>' . "')

", __FILE__, __LINE__);


/*

*/

// 2.0 Local Streaming
global $boardurl;
db_query("REPLACE INTO {$db_prefix}mediapro_sites
	(ID,title, website, height,width,  regexmatch, embedcode)
VALUES
(73, 'Local SWF', '', 350,425, 'htt(p|ps)://([^<>]+)" . '\\' .'\\' . ".swf','" . '
<object width="480" height="600"
			  classid="clsid:d27cdb6e-ae6d-11cf-96b8-444553540000"
			  codebase="http://fpdownload.macromedia.com/pub/
			  shockwave/cabs/flash/swflash.cab#version=8,0,0,0">
			  <param name="movie" value="htt$1://$2.swf" />
			  <embed src="htt$1://$2.swf" width="480" height="600"
			  type="application/x-shockwave-flash" pluginspage=
			  "http://www.macromedia.com/go/getflashplayer" />
</object>
' . "'),
(74, 'Local MOV', '', 350,425, 'htt(p|ps)://([^<>]+)" . '\\' .'\\' . ".mov','" . '
<OBJECT CLASSID="clsid:02BF25D5-8C17-4B23-BC80-D3488ABDDC6B"

CODEBASE="http://www.apple.com/qtactivex/qtplugin.cab" height="600" width="480">

<PARAM NAME="src" VALUE="htt$1://$2.mov" />
<PARAM NAME="AutoPlay" VALUE="true" />
<PARAM NAME="Controller" VALUE="false" />
<EMBED SRC="htt$1://$2.mov" height="600" width="480" TYPE="video/quicktime" PLUGINSPAGE="http://www.apple.com/quicktime/download/" AUTOPLAY="true" CONTROLLER="false" />
</OBJECT>
' . "'),
(75, 'Local MP4', '', 350,425, 'htt(p|ps)://([^<>]+)" . '\\' .'\\' . ".mp4','" . '
<video height="600" width="480" controls>
  <source src="htt$1://$2.mp4" type="video/mp4">
  <object data="htt$1://$2.mp4" height="600" width="480">
    <PARAM NAME="src" VALUE="htt$1://$2.mp4">
    <PARAM NAME="AutoPlay" VALUE="true" >
    <PARAM NAME="Controller" VALUE="false" >
    <embed src="htt$1://$2.mp4" height="600" width="480">
  </object>
</video>
' . "'),
(76, 'Local RM', '', 350,425, 'htt(p|ps)://([^<>]+)" . '\\' .'\\' . ".rm','" . '
<OBJECT ID=RVOCX CLASSID="clsid:CFCDAA03-8BE4-11cf-B84B-0020AFBBCCFA"
  width="480" height="600">
<PARAM NAME="SRC" VALUE="htt$1://$2.\.rm" />
<PARAM NAME="CONTROLS" VALUE="ImageWindow" />
<PARAM NAME="CONSOLE" VALUE="one" />
<EMBED SRC="htt$1://$2.rm" width="480" height="600" NOJAVA=true
   CONSOLE=one AUTOSTART=true CONTROLS=ControlPanel>
</OBJECT>
<EMBED SRC="htt$1://$2.rm" width="480" height=40 NOJAVA=true CONTROLS=ControlPanel CONSOLE=one>

' . "'),
(77, 'Local RAM', '', 350,425, 'htt(p|ps)://([^<>]+)" . '\\' .'\\' . ".ram','" . '
<OBJECT ID=RVOCX CLASSID="clsid:CFCDAA03-8BE4-11cf-B84B-0020AFBBCCFA"
  width="480" height="600">
<PARAM NAME="SRC" VALUE="htt$1://$2.ram" />
<PARAM NAME="CONTROLS" VALUE="ImageWindow" />
<PARAM NAME="CONSOLE" VALUE="one" />
<EMBED SRC="htt$1://$2.ram" width="480" height="600" NOJAVA=true
   CONSOLE=one AUTOSTART=true CONTROLS=ControlPanel>
</OBJECT>
<EMBED SRC="htt$1://$2.ram" width="480" height=40 NOJAVA=true CONTROLS=ControlPanel CONSOLE=one>

' . "'),
(78, 'Local RA', '', 350,425, 'htt(p|ps)://([^<>]+)" . '\\' .'\\' . ".ra','" . '
<OBJECT ID=RVOCX CLASSID="clsid:CFCDAA03-8BE4-11cf-B84B-0020AFBBCCFA"
  width="480" height="600">
<PARAM NAME="SRC" VALUE="htt$1://$2.ra" />
<PARAM NAME="CONTROLS" VALUE="ImageWindow" />
<PARAM NAME="CONSOLE" VALUE="one" />
<EMBED SRC="htt$1://$2.ra" width="480" height="600" NOJAVA=true
   CONSOLE=one AUTOSTART=true CONTROLS=ControlPanel>
</OBJECT>
<EMBED SRC="htt$1://$2.ra" width="480" height=40 NOJAVA=true CONTROLS=ControlPanel CONSOLE=one>

' . "'),
(79, 'Local AVI', '', 350,425, 'htt(p|ps)://([^<>]+)" . '\\' .'\\' . ".avi','" . '
<OBJECT ID="MediaPlayer" width="480" height="600" CLASSID="CLSID:22D6F312-B0F6-11D0-94AB-0080C74C7E95"
STANDBY="Loading Windows Media Player components..." TYPE="application/x-oleobject">
<PARAM NAME="FileName" VALUE="htt$1://$2.avi" />
<PARAM name="autostart" VALUE="true" />
<PARAM name="ShowControls" VALUE="true" />
<param name="ShowStatusBar" value="false" />
<PARAM name="ShowDisplay" VALUE="false" />
<EMBED TYPE="application/x-mplayer2" SRC="htt$1://$2.avi" NAME="MediaPlayer"
width="480" height="600" ShowControls="1" ShowStatusBar="0" ShowDisplay="0" autostart="0"> </EMBED>
</OBJECT>
' . "'),
(80, 'Local WMV', '', 350,425, 'htt(p|ps)://([^<>]+)" . '\\' .'\\' . ".wmv','" . '
<OBJECT ID="MediaPlayer" width="480" height="600" CLASSID="CLSID:22D6F312-B0F6-11D0-94AB-0080C74C7E95"
STANDBY="Loading Windows Media Player components..." TYPE="application/x-oleobject">
<PARAM NAME="FileName" VALUE="htt$1://$2.wmv" />
<PARAM name="autostart" VALUE="true" />
<PARAM name="ShowControls" VALUE="true" />
<param name="ShowStatusBar" value="false" />
<PARAM name="ShowDisplay" VALUE="false" />
<EMBED TYPE="application/x-mplayer2" SRC="htt$1://$2.wmv" NAME="MediaPlayer"
width="480" height="600" ShowControls="1" ShowStatusBar="0" ShowDisplay="0" autostart="0"> </EMBED>
</OBJECT>
' . "')
", __FILE__, __LINE__);


db_query("REPLACE INTO {$db_prefix}mediapro_sites
	(ID,title, website, height,width,  regexmatch, embedcode)
VALUES
(81, 'Local WMA', '', 350,425, 'htt(p|ps)://([^<>]+)" . '\\' .'\\' . ".wma','" . '
<OBJECT ID="MediaPlayer" width="480" height="600" CLASSID="CLSID:22D6F312-B0F6-11D0-94AB-0080C74C7E95"
STANDBY="Loading Windows Media Player components..." TYPE="application/x-oleobject">
<PARAM NAME="FileName" VALUE="htt$1://$2.wma" />
<PARAM name="autostart" VALUE="true" />
<PARAM name="ShowControls" VALUE="true" />
<param name="ShowStatusBar" value="false" />
<PARAM name="ShowDisplay" VALUE="false"  />
<EMBED TYPE="application/x-mplayer2" SRC="htt$1://$2.wma" NAME="MediaPlayer"
width="480" height="600" ShowControls="1" ShowStatusBar="0" ShowDisplay="0" autostart="0"> </EMBED>
</OBJECT>
' . "')


", __FILE__, __LINE__);


db_query("REPLACE INTO {$db_prefix}mediapro_sites
	(ID,title, website, height,width,  regexmatch, embedcode)
VALUES
(82, 'own3d.tv', 'http://www.own3d.tv', 360,640, 'http://www.own3d.tv/video/([0-9]*)/([^<>]+)','" . '
<object width="480" height="600">
    <param name="movie" value="http://www.own3d.tv/stream/$1" />
    <param name="allowscriptaccess" value="always" />
    <param name="allowfullscreen" value="true" />
    <param name="wmode" value="transparent" />
    <embed src="http://www.own3d.tv/stream/$1" type="application/x-shockwave-flash" allowfullscreen="true" allowscriptaccess="always" width="640" height="360" wmode="transparent"></embed>
</object>
' . "')


", __FILE__, __LINE__);


db_query("REPLACE INTO {$db_prefix}mediapro_sites
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

", __FILE__, __LINE__);




db_query("REPLACE INTO {$db_prefix}mediapro_sites
	(ID,title, website, height,width,  regexmatch, embedcode)
VALUES
(87, 'SoundCloud','http://www.soundcloud.com', 600,600, 'htt(p|ps)://w.soundcloud.com/player/" . '\\' .'\\' . "?url=https://api.soundcloud.com/tracks/([A-Za-z0-9]*)','" . '<iframe width="100%" height="166" scrolling="no" frameborder="no" src="https://w.soundcloud.com/player/?url=https%3A//api.soundcloud.com/tracks/$2"></iframe>' . "')
", __FILE__, __LINE__);



db_query("REPLACE INTO {$db_prefix}mediapro_sites
	(ID,title, website, height,width,  regexmatch, embedcode)
VALUES
(90, 'Facebook Video (New)','http://www.facebook.com', 385,466, 'htt(p|ps)://www.facebook.com/video.php" . '\\' .'\\' . "?v=([0-9]*)','" . '
<div id="fb-root"></div> <script>(function(d, s, id) { var js, fjs = d.getElementsByTagName(s)[0]; if (d.getElementById(id)) return; js = d.createElement(s); js.id = id; js.src = "//connect.facebook.net/en_US/all.js#xfbml=1"; fjs.parentNode.insertBefore(js, fjs); }(document,"script", "facebook-jssdk"));</script>
<div class="fb-post" data-href="https://www.facebook.com/video.php?v=$2" data-width="466"><div class="fb-xfbml-parse-ignore"><a href="https://www.facebook.com/view.php?v=$2">Post</a>
</div></div>
'
 . "'),
(91, 'coub.com','http://coub.com', 367,480, 'htt(p|ps)://coub.com/view/([A-Za-z0-9]*)','" . '<iframe src="http://coub.com/embed/$2?muted=false&autostart=false&originalSize=false&hideTopBar=false&startWithHD=false" allowfullscreen="true" frameborder="0" width="480" height="600"></iframe>' . "')
", __FILE__, __LINE__);


db_query("REPLACE INTO {$db_prefix}mediapro_sites
	(ID,title, website, height,width,  regexmatch, embedcode)
VALUES
(92, 'Twitch.tv Channels','https://twitch.tv', 378,620, 'https://www.twitch.tv/([A-Za-z0-9]*)','" . '<div id="twitch-embed#playercount#"></div><script src="https://player.twitch.tv/js/embed/v1.js"></script><script type="text/javascript">  new Twitch.Player("twitch-embed#playercount#", {    channel: "$1", autoplay: false,  height: "378",    width: "620",    parent: "#parent#"  });</script>' . "')
", __FILE__, __LINE__);


 db_query("REPLACE INTO {$db_prefix}mediapro_sites
	(ID,title, website, height,width,  regexmatch, embedcode)
VALUES
(93, 'Deezer.com','Deezer.com', 240,700, 'http://www.deezer.com/([A-Za-z0-9]*)/([0-9]*)','" . '<iframe scrolling="no" frameborder="0" allowTransparency="true" src="http://www.deezer.com/plugins/player?autoplay=false&playlist=true&width=700&height=240&cover=true&type=$1&id=$2&title=&app_id=undefined" width="480" height="600"></iframe>' . "')
", __FILE__, __LINE__);


 db_query("REPLACE INTO {$db_prefix}mediapro_sites
	(ID,title, website, height,width,  regexmatch, embedcode)
VALUES
(94, 'buto.tv','http://buto.tv', 360,640, 'http://play.buto.tv/([A-Za-z0-9]*)','" . '<iframe id="buto_iframe_Vmxtx" src="//embed.buto.tv/$1" width="480" height="600" frameborder="no" scrolling="no" allowfullscreen="true"></iframe>' . "')
", __FILE__, __LINE__);


 db_query("REPLACE INTO {$db_prefix}mediapro_sites
	(ID,title, website, height,width,  regexmatch, embedcode)
VALUES
(95, 'Facebook Video (New v4)','http://www.facebook.com', 385,466, 'htt(p|ps)://www.facebook.com/([0-9]*)/videos/([0-9]*)/(.*)','" . '
<div id="fb-root"></div><script>(function(d, s, id) {  var js, fjs = d.getElementsByTagName(s)[0];  if (d.getElementById(id)) return;  js = d.createElement(s); js.id = id;  js.src = "//connect.facebook.net/en_US/sdk.js#xfbml=1&version=v2.3";  fjs.parentNode.insertBefore(js, fjs);}(document, "script", "facebook-jssdk"));</script><div class="fb-video" data-allowfullscreen="1" data-width="480" data-href="/$2/videos/vb.$2/$3/?type=1"><div class="fb-xfbml-parse-ignore"><blockquote cite="https://www.facebook.com/$2/videos/$3/"><a href="https://www.facebook.com/$2/videos/$3/"></a></blockquote></div></div>

'
 . "')", __FILE__, __LINE__);


 db_query("REPLACE INTO {$db_prefix}mediapro_sites
	(ID,title, website, height,width,  regexmatch, embedcode)
VALUES
 (96, 'Local Ogg', '', 350,425, 'htt(p|ps)://([^<>]+)" . '\\' .'\\' . ".ogg','" . '
<video height="600" width="480" controls>
  <source src="htt$1://$2.ogg" type="video/ogg">
  <object data="htt$1://$2.ogg" height="600" width="480">
    <PARAM NAME="src" VALUE="htt$1://$2.ogg">
    <PARAM NAME="AutoPlay" VALUE="true" >
    <PARAM NAME="Controller" VALUE="false" >
    <embed src="htt$1://$2.ogg" height="600" width="480">
  </object>
</video>
' . "'),
 (97, 'Local Webm', '', 350,425, 'htt(p|ps)://([^<>]+)" . '\\' .'\\' . ".webm','" . '
<video height="600" width="480" controls>
  <source src="htt$1://$2.webm" type="video/webm">
  <object data="htt$1://$2.webm" height="600" width="480">
    <PARAM NAME="src" VALUE="htt$1://$2.webm">
    <PARAM NAME="AutoPlay" VALUE="true" >
    <PARAM NAME="Controller" VALUE="false" >
    <embed src="htt$1://$2.webm" height="600" width="480">
  </object>
</video>
' . "')", __FILE__, __LINE__);



 db_query("REPLACE INTO {$db_prefix}mediapro_sites
	(ID,title, website, height,width,  regexmatch, embedcode)
VALUES
(98, 'Instagram','http://instagram.com', 360,640, 'htt(p|ps)://[www" . '\\' .'\\' . ".]*instagram.com/(p|reel)/([A-Za-z0-9" . '\\' .'\\' . "-" . '\\' .'\\' . "_]*)/([^<>]+)','" . '<blockquote class="instagram-media" data-instgrm-captioned data-instgrm-permalink="https://www.instagram.com/$2/$3/" data-instgrm-version="14" style=" background:#FFF; border:0; border-radius:3px; box-shadow:0 0 1px 0 rgba(0,0,0,0.5),0 1px 10px 0 rgba(0,0,0,0.15); margin: 1px; max-width:540px; min-width:326px; padding:0; width:99.375%; width:-webkit-calc(100% - 2px); width:calc(100% - 2px);"><div style="padding:16px;"> <a href="https://www.instagram.com/$2/$3/" style=" background:#FFFFFF; line-height:0; padding:0 0; text-align:center; text-decoration:none; width:100%;" target="_blank"> <div style=" display: flex; flex-direction: row; align-items: center;"> <div style="background-color: #F4F4F4; border-radius: 50%; flex-grow: 0; height: 40px; margin-right: 14px; width: 40px;"></div> <div style="display: flex; flex-direction: column; flex-grow: 1; justify-content: center;"> <div style=" background-color: #F4F4F4; border-radius: 4px; flex-grow: 0; height: 14px; margin-bottom: 6px; width: 100px;"></div> <div style=" background-color: #F4F4F4; border-radius: 4px; flex-grow: 0; height: 14px; width: 60px;"></div></div></div><div style="padding: 19% 0;"></div> <div style="display:block; height:50px; margin:0 auto 12px; width:50px;"><svg width="50px" height="50px" viewBox="0 0 60 60" version="1.1" xmlns="https://www.w3.org/2000/svg" xmlns:xlink="https://www.w3.org/1999/xlink"><g stroke="none" stroke-width="1" fill="none" fill-rule="evenodd"><g transform="translate(-511.000000, -20.000000)" fill="#000000"><g><path d="M556.869,30.41 C554.814,30.41 553.148,32.076 553.148,34.131 C553.148,36.186 554.814,37.852 556.869,37.852 C558.924,37.852 560.59,36.186 560.59,34.131 C560.59,32.076 558.924,30.41 556.869,30.41 M541,60.657 C535.114,60.657 530.342,55.887 530.342,50 C530.342,44.114 535.114,39.342 541,39.342 C546.887,39.342 551.658,44.114 551.658,50 C551.658,55.887 546.887,60.657 541,60.657 M541,33.886 C532.1,33.886 524.886,41.1 524.886,50 C524.886,58.899 532.1,66.113 541,66.113 C549.9,66.113 557.115,58.899 557.115,50 C557.115,41.1 549.9,33.886 541,33.886 M565.378,62.101 C565.244,65.022 564.756,66.606 564.346,67.663 C563.803,69.06 563.154,70.057 562.106,71.106 C561.058,72.155 560.06,72.803 558.662,73.347 C557.607,73.757 556.021,74.244 553.102,74.378 C549.944,74.521 548.997,74.552 541,74.552 C533.003,74.552 532.056,74.521 528.898,74.378 C525.979,74.244 524.393,73.757 523.338,73.347 C521.94,72.803 520.942,72.155 519.894,71.106 C518.846,70.057 518.197,69.06 517.654,67.663 C517.244,66.606 516.755,65.022 516.623,62.101 C516.479,58.943 516.448,57.996 516.448,50 C516.448,42.003 516.479,41.056 516.623,37.899 C516.755,34.978 517.244,33.391 517.654,32.338 C518.197,30.938 518.846,29.942 519.894,28.894 C520.942,27.846 521.94,27.196 523.338,26.654 C524.393,26.244 525.979,25.756 528.898,25.623 C532.057,25.479 533.004,25.448 541,25.448 C548.997,25.448 549.943,25.479 553.102,25.623 C556.021,25.756 557.607,26.244 558.662,26.654 C560.06,27.196 561.058,27.846 562.106,28.894 C563.154,29.942 563.803,30.938 564.346,32.338 C564.756,33.391 565.244,34.978 565.378,37.899 C565.522,41.056 565.552,42.003 565.552,50 C565.552,57.996 565.522,58.943 565.378,62.101 M570.82,37.631 C570.674,34.438 570.167,32.258 569.425,30.349 C568.659,28.377 567.633,26.702 565.965,25.035 C564.297,23.368 562.623,22.342 560.652,21.575 C558.743,20.834 556.562,20.326 553.369,20.18 C550.169,20.033 549.148,20 541,20 C532.853,20 531.831,20.033 528.631,20.18 C525.438,20.326 523.257,20.834 521.349,21.575 C519.376,22.342 517.703,23.368 516.035,25.035 C514.368,26.702 513.342,28.377 512.574,30.349 C511.834,32.258 511.326,34.438 511.181,37.631 C511.035,40.831 511,41.851 511,50 C511,58.147 511.035,59.17 511.181,62.369 C511.326,65.562 511.834,67.743 512.574,69.651 C513.342,71.625 514.368,73.296 516.035,74.965 C517.703,76.634 519.376,77.658 521.349,78.425 C523.257,79.167 525.438,79.673 528.631,79.82 C531.831,79.965 532.853,80.001 541,80.001 C549.148,80.001 550.169,79.965 553.369,79.82 C556.562,79.673 558.743,79.167 560.652,78.425 C562.623,77.658 564.297,76.634 565.965,74.965 C567.633,73.296 568.659,71.625 569.425,69.651 C570.167,67.743 570.674,65.562 570.82,62.369 C570.966,59.17 571,58.147 571,50 C571,41.851 570.966,40.831 570.82,37.631"></path></g></g></g></svg></div><div style="padding-top: 8px;"> <div style=" color:#3897f0; font-family:Arial,sans-serif; font-size:14px; font-style:normal; font-weight:550; line-height:18px;">View this post on Instagram</div></div><div style="padding: 12.5% 0;"></div> <div style="display: flex; flex-direction: row; margin-bottom: 14px; align-items: center;"><div> <div style="background-color: #F4F4F4; border-radius: 50%; height: 12.5px; width: 12.5px; transform: translateX(0px) translateY(7px);"></div> <div style="background-color: #F4F4F4; height: 12.5px; transform: rotate(-45deg) translateX(3px) translateY(1px); width: 12.5px; flex-grow: 0; margin-right: 14px; margin-left: 2px;"></div> <div style="background-color: #F4F4F4; border-radius: 50%; height: 12.5px; width: 12.5px; transform: translateX(9px) translateY(-18px);"></div></div><div style="margin-left: 8px;"> <div style=" background-color: #F4F4F4; border-radius: 50%; flex-grow: 0; height: 20px; width: 20px;"></div> <div style=" width: 0; height: 0; border-top: 2px solid transparent; border-left: 6px solid #f4f4f4; border-bottom: 2px solid transparent; transform: translateX(16px) translateY(-4px) rotate(30deg)"></div></div><div style="margin-left: auto;"> <div style=" width: 0px; border-top: 8px solid #F4F4F4; border-right: 8px solid transparent; transform: translateY(16px);"></div> <div style=" background-color: #F4F4F4; flex-grow: 0; height: 12px; width: 16px; transform: translateY(-4px);"></div> <div style=" width: 0; height: 0; border-top: 8px solid #F4F4F4; border-left: 8px solid transparent; transform: translateY(-4px) translateX(8px);"></div></div></div> <div style="display: flex; flex-direction: column; flex-grow: 1; justify-content: center; margin-bottom: 24px;"> <div style=" background-color: #F4F4F4; border-radius: 4px; flex-grow: 0; height: 14px; margin-bottom: 6px; width: 224px;"></div> <div style=" background-color: #F4F4F4; border-radius: 4px; flex-grow: 0; height: 14px; width: 144px;"></div></div></a><p style=" color:#c9c8cd; font-family:Arial,sans-serif; font-size:14px; line-height:17px; margin-bottom:0; margin-top:8px; overflow:hidden; padding:8px 0 7px; text-align:center; text-overflow:ellipsis; white-space:nowrap;"><a href="https://www.instagram.com/$2/$3/" style=" color:#c9c8cd; font-family:Arial,sans-serif; font-size:14px; font-style:normal; font-weight:normal; line-height:17px; text-decoration:none;" target="_blank"> </a></p></div></blockquote> <script async src="//www.instagram.com/embed.js"></script>
' . "'),
(99, 'Facebook Video (New v5)','http://www.facebook.com', 385,466, '(http|https):" . '\\' .'\\' . "/" . '\\' .'\\' . "/(|(.+?).)facebook.com/([" . '\\' .'\\' . "w" . '\\' .'\\' . "." . '\\' .'\\' . "_]+/videos/|video.php" . '\\' .'\\' . "?v=)(" . '\\' .'\\' . "d+)(|((/|" . '\\' .'\\' . "?|" . '\\' .'\\' . "&)(.+?)))','" . '
<div id="fb-root"></div><script>(function(d, s, id) {var js, fjs = d.getElementsByTagName(s)[0];if (d.getElementById(id)) return;js = d.createElement(s); js.id = id;js.src = "//connect.facebook.net/en_US/sdk.js#xfbml=1&version=v2.3";fjs.parentNode.insertBefore(js, fjs);}(document, "script", "facebook-jssdk"));</script><div class="fb-video" data-href="https://www.facebook.com/facebook/videos/$5/" data-width="500" data-show-text="false"  data-allowfullscreen="1"><blockquote cite="https://www.facebook.com/facebook/videos/$5/" class="fb-xfbml-parse-ignore"><a href="https://www.facebook.com/facebook/videos/$5/"></a></blockquote></div>
'
 . "'),
 (100, 'Sendvid','http://sendvid.com', 360,640, 'htt(p|ps)://sendvid.com/([A-Za-z0-9]*)','" . '<iframe width="640" height="360" src="//sendvid.com/embed/$2" frameborder="0" allowfullscreen></iframe>
' . "')

 ", __FILE__, __LINE__);



 db_query("REPLACE INTO {$db_prefix}mediapro_sites
	(ID,title, website, height,width,  regexmatch, embedcode)
VALUES
(101, 'Streamable.com','https://streamable.com', 360,640, 'htt(p|ps)://streamable.com/([A-Za-z0-9]*)','" . '<div style="width: 100%; height: 0px; position: relative; padding-bottom: 56.250%;"><iframe src="https://streamable.com/s/$2" frameborder="0" width="100%" height="100%" allowFullScreen="true" style="width: 100%; height: 100%; position: absolute;"></iframe></div>
' . "'),
(102, 'imgur','https://imgur.com', 360,640, 'htt(p|ps)://[A-Za-z" . '\\' .'\\' . ".]*imgur.com/([A-Za-z0-9]*)[" . '\\' .'\\' . ".A-Za-z]*','" . '<blockquote class="imgur-embed-pub" lang="en" data-id="$2"></blockquote><script async src="//s.imgur.com/min/embed.js" charset="utf-8"></script>
' . "'),
(103, 'Youtube Playlist','https://youtube.com', 360,640, 'htt(p|ps)://www.youtube.com/playlist" . '\\' .'\\' . "?list=([A-Za-z0-9]*)','" . '<iframe width="640" height="360" src="https://www.youtube.com/embed/videoseries?list=$2" frameborder="0" allowFullScreen="true"></iframe>
' . "')
", __FILE__, __LINE__);

db_query("REPLACE INTO {$db_prefix}mediapro_sites
	(ID,title, website, height,width,  regexmatch, embedcode)
VALUES
(104, 'Pastebin','https://pastebin.com', 360,640, 'https://pastebin.com/([A-Za-z0-9]*)','" . '<iframe src="https://pastebin.com/embed_iframe/$1" style="border:none;width:100%"></iframe>
' . "'),
(105, 'Twitch Videos','https://www.twitch.tv', 378,620, 'https://go.twitch.tv/videos/([A-Za-z0-9]*)ZSPLITMZhttps://www.twitch.tv/videos/([A-Za-z0-9]*)','" . '<div id="twitch-embed#playercount#"></div>

<script src="https://player.twitch.tv/js/embed/v1.js"></script>

<script type="text/javascript">
  new Twitch.Player("twitch-embed#playercount#", {
    video: "$1",
    height: "600",
    width: "480",
    autoplay: false,
    parent: "#parent#"
  });
</script>

' . "'),
(106, 'Ted Talks','https://www.ted.com', 480,854, 'https://www.ted.com/talks/([A-Za-z0-9_]*)','" . '<iframe src="https://embed.ted.com/talks/$1" width="480" height="600"  frameborder="0" scrolling="no" allowFullScreen="true"></iframe>
' . "'),
(108, 'Spotify','https://spotify.com',80,250, 'https://open.spotify.com/([A-Za-z0-9]*)/([A-Za-z0-9]*)(" . '\\' .'\\' . "?)?([A-Za-z0-9" . '\\' .'\\' . "=" . '\\' .'\\' . "_" . '\\' .'\\' . "-]*)','" . '<iframe src="https://open.spotify.com/embed/$1/$2" width="300" height="380" frameborder="0" allowtransparency="true" allow="encrypted-media"></iframe>
' . "')
", __FILE__, __LINE__);


db_query("REPLACE INTO {$db_prefix}mediapro_sites
	(ID,title, website, height,width,  regexmatch, embedcode)
VALUES
(109, 'Facebook Posts','https://facebook.com', 500,500, 'htt(p|ps)://([A-Za-z]*).facebook.com/([A-Za-z0-9_" . '\\' .'\\' . ".]*)/posts/([0-9]*)','" . '<div id="fb-root"></div><script>(function(d,s,id){var js, fjs = d.getElementsByTagName(s)[0];if (d.getElementById(id)) return;js = d.createElement(s); js.id = id;js.src = "//connect.facebook.net/fr_FR/sdk.js#xfbml=1&version=v2.12";fjs.parentNode.insertBefore(js, fjs);}(document, "script", "facebook-jssdk"));</script><div class="fb-post" data-href="https://www.facebook.com/$3/posts/$4/" data-width="500" data-show-text="true"></div>
' . "'),
(110, 'US News','http://www.usnews.com',332,590, 'htt(p|ps):\/\/www\.usnews\.com\/news\/features\/news-video\?([\w.=_&;]+?)videoId=(\d+)','" . '<iframe width="590" height="332" src="http://launch.newsinc.com/?type=VideoPlayer/Single&widgetId=1&videoId=$3" frameborder="no" scrolling="no" noresize marginwidth="0" marginheight="0" allowFullScreen="true" webkitallowfullscreen="true" mozallowfullscreen="true"></iframe>
' . "'),
(111, 'Local/Remote Ogm', '', 350,425, 'htt(p|ps)://([^<>]+)" . '\\' .'\\' . ".ogm','" . '
<video height="600" width="480" controls>
  <source src="htt$1://$2.ogm" type="video/ogm">
  <object data="htt$1://$2.ogm" height="600" width="480">
    <PARAM NAME="src" VALUE="htt$1://$2.ogm">
    <PARAM NAME="AutoPlay" VALUE="true" >
    <PARAM NAME="Controller" VALUE="false" >
    <embed src="htt$1://$2.ogm" height="600" width="480">
  </object>
</video>
' . "'),
(112, 'Local/Remote Ogv', '', 350,425, 'htt(p|ps)://([^<>]+)" . '\\' .'\\' . ".ogv','" . '
<video height="600" width="480" controls>
  <source src="htt$1://$2.ogv" type="video/ogv">
  <object data="htt$1://$2.ogv" height="600" width="480">
    <PARAM NAME="src" VALUE="htt$1://$2.ogv">
    <PARAM NAME="AutoPlay" VALUE="true" >
    <PARAM NAME="Controller" VALUE="false" >
    <embed src="htt$1://$2.ogv" height="600" width="480">
  </object>
</video>
' . "')
", __FILE__, __LINE__);



db_query("REPLACE INTO {$db_prefix}mediapro_sites
	(ID,title, website, height,width,  regexmatch, embedcode)
VALUES
(114, 'vbox7','https://www.vbox7.com',  315,560, 'https://www.vbox7.com/play:([A-Za-z0-9]*)','" . '<iframe width="560" height="315" src="https://www.vbox7.com/emb/external.php?vid=$1" frameborder="0" allowFullScreen="true"></iframe>
' . "'),
(115, 'Local MP3', '', 350,425, 'htt(p|ps)://([^<>]+)" . '\\' .'\\' . ".mp3','" . '
<audio controls>
  <source src="htt$1://$2.mp3" type="audio/mpeg">
</audio> 

' . "'),
(116, 'Yarn', '', 600,768, 'htt(p|ps)://getyarn.io/yarn-clip/([A-Za-z0-9\\-]*)','" . '
<iframe seamless="seamless" style="width: 100%; border: none; display: block; max-width: 768px; height: 600px;" src="https://getyarn.io/yarn-clip/embed/$2?autoplay=false"> </iframe>

' . "'),
(117, 'Buzzsprout', '', 600,768, 'htt(p|ps)://www.buzzsprout.com/([0-9]*)/([0-9]*)-(([0-9A-Za-z-]*)|([0-9A-Za-z-]*))','" . '
<script src="https://www.buzzsprout.com/$2/$3-$4.js?player=small"></script>

' . "'),
(118, 'TikTok', '', 600,768, 'htt(p|ps)://www.tiktok.com/@([0-9A-Za-z_]*)/video/([0-9]*)','" . '
<blockquote class="tiktok-embed" cite="https://www.tiktok.com/@$2/video/$3" data-video-id="$3" style="max-width: 768px;min-width: 600px;" > <section> <a target="_blank" title="@$2" href="https://www.tiktok.com/@$2">@$2</a> <p> </p>&nbsp; </section> </blockquote> <script async src="https://www.tiktok.com/embed.js"></script>
' . "'),
(119, 'Telegram', '', 600,768, 'htt(p|ps)://t.me/([0-9A-Za-z_]*)/([0-9A-Za-z_]*)ZSPLITMZhtt(p|ps)://telegram.org/([0-9A-Za-z_]*)/([0-9A-Za-z_]*)','" . '
<blockquote></blockquote><script async src="https://telegram.org/js/telegram-widget.js?8" data-telegram-post="$2/$3" data-width="100%"></script>
' . "')



", __FILE__, __LINE__);


db_query("REPLACE INTO {$db_prefix}mediapro_sites
	(ID,title, website, height,width,  regexmatch, embedcode)
VALUES
(120, 'Videoclip.bg','https://videoclip.bg',360,640, 'htt(p|ps)://www.videoclip.bg/watch/([0-9]*)_([A-Za-z0-9-]*)','" . '<iframe width="640" height="360" src="https://www.videoclip.bg/embed/$2" frameborder="0" allowFullScreen="true" allowscriptaccess></iframe>

' . "'),
(121, 'Gfycat','https://gfycat.com',360,640, 'https://gfycat.com/([A-Za-z0-9]*)([A-Za-z0-9-]*)','" . '<div style="position:relative; padding-bottom:calc(56.25% + 44px)"><iframe src="https://gfycat.com/ifr/$1" frameborder="0" scrolling="no" width="100%" height="100%" style="position:absolute;top:0;left:0;" allowFullScreen="true"></iframe></div>

' . "')
", __FILE__, __LINE__);

db_query("REPLACE INTO {$db_prefix}mediapro_sites
	(ID,title, website, height,width,  regexmatch, embedcode)
VALUES
(122, 'codepen.io','https://codepen.io',360,640, 'https://codepen.io/(.+)/pen/(.+)','" . '<iframe width="800" height="450" src="https://codepen.io/$1/full/$2" frameborder="0" allowFullScreen="true" title="CodePen.io"></iframe>

' . "'),
(123, 'ustream.tv','https://ustream.tv',360,640, 'https://ustream.tv/channel/([0-9]+)','" . '<iframe width="800" height="450" src="https://ustream.tv/embed/$1" frameborder="0" allowFullScreen="true" title="UStream video"></iframe>

' . "')
", __FILE__, __LINE__);

db_query("REPLACE INTO {$db_prefix}mediapro_sites
	(ID,title, website, height,width,  regexmatch, embedcode)
VALUES
(124, 'Reddit','https://reddit.com',360,640, 'https://www.reddit.com/r/([A-Za-z0-9_]*)/comments/([A-Za-z0-9_]*)/([A-Za-z0-9_]*)/','" . '<blockquote class="reddit-card" data-card-created="1610762333"><a href="https://www.reddit.com/r/$1/comments/$2/$3/"> from <a href="http://www.reddit.com/r/$1">r/$1</a></blockquote>
<script async src="https://embed.redditmedia.com/widgets/platform.js" charset="UTF-8"></script>

' . "'),
(125, 'Twitch Clips', 'https://twitch.tv/', 378, 620, 'https://clips.twitch.tv/([A-Za-z0-9]*)','" . '<iframe src="https://clips.twitch.tv/embed?clip=$1&parent=#parent#" frameborder="0" allowfullscreen="true" scrolling="no" height="378" width="620"></iframe>' . "'),
(126, 'GitHub Gist', 'https://github.com',  300, 700, 'https://gist.github.com/([A-Za-z0-9-]*/[A-Za-z0-9]*)','" . '<script src="https://gist.github.com/$1.js"></script>'  . "')
", __FILE__, __LINE__);


db_query("REPLACE INTO {$db_prefix}mediapro_sites
	(ID,title, website, height,width,  regexmatch, embedcode)
VALUES
(127, 'MSNBC','https://www.msnbc.com',315,560, 'https://www.msnbc.com/msnbc/watch/([A-Za-z0-9-]*)-([0-9]+)','" . '<iframe width="560" height="315" src="https://www.msnbc.com/msnbc/embedded-video/mmvo$2" scrolling="no" frameborder="0" allowFullScreen="true"></iframe>

' . "'),
(128, 'NBC News','https://www.nbcnews.com',315,560, 'https://www.nbcnews.com/video/([A-Za-z0-9-]*)-([0-9]+)','" . '<iframe width="560" height="315" src="https://www.nbcnews.com/news/embedded-video/mmvo$2" scrolling="no" frameborder="0" allowFullScreen="true"></iframe>

' . "'),
(129, 'Twitch Clips','https://twitch.tv',378,620, 'https://clips.twitch.tv/([A-Za-z0-9]*)','" . '<iframe src="https://clips.twitch.tv/embed?clip=$1&parent=#parent#" frameborder="0" allowfullscreen="true" scrolling="no" height="378" width="620"></iframe>

' . "'),
(130, 'Facebook fb.watch','ttps://fb.watch',378,620, 'https://fb.watch/([A-Za-z0-9]*)/','" . '
<iframe src="https://www.facebook.com/plugins/video.php?height=314&href=https%3A%2F%2Ffb.watch%2F$1%2F&show_text=false&width=560&t=0" width="560" height="314" style="border:none;overflow:hidden" scrolling="no" frameborder="0" allowfullscreen="true" allow="autoplay; clipboard-write; encrypted-media; picture-in-picture; web-share" allowFullScreen="true"></iframe>
' . "'),
(131, 'YouTube Shorts','https://youtube.com',378,620, 'https://www.youtube.com/shorts/([A-Za-z0-9_-]*)ZSPLITMZhttps://youtube.com/shorts/([A-Za-z0-9_-]*)','" . '
<iframe width="480" height="600" src="//www.youtube.com/embed/$1?rel=0&amp;playsinline=1&amp;controls=1&amp;showinfo=0&amp;modestbranding=0" frameborder="0" allowfullscreen="true"></iframe>
' . "'),
 (132, 'PDF Files Remote', '', 340,640, 'htt(p|ps)://([^<>]+)" . '\\' .'\\' . ".pdf','" . '
 <embed width="480" height="600"  src="htt$1://$2.pdf" type="application/pdf"></embed>
' . "')
", __FILE__, __LINE__);


db_query("REPLACE INTO {$db_prefix}mediapro_sites
	(ID,title, website, height,width,  regexmatch, embedcode)
VALUES
(134, 'Threads - Topics','https://threads.net',360,640, 'https://www.threads.net/t/([A-Za-z0-9_]*)','" . '<blockquote class="text-post-media" data-text-post-permalink="https://www.threads.net/t/$1" data-text-post-version="0" id="ig-tp-$1" style=" background:#FFF; border-width: 1px; border-style: solid; border-color: #00000026; border-radius: 16px; max-width:540px; margin: 1px; min-width:326px; padding:0; width:99.375%; width:-webkit-calc(100% - 2px); width:calc(100% - 2px);"> <a href="https://www.threads.net/t/$1" style=" background:#FFFFFF; line-height:0; padding:0 0; text-align:center; text-decoration:none; width:100%; font-family: -apple-system, BlinkMacSystemFont, sans-serif;" target="_blank"> <div style=" padding: 40px; display: flex; flex-direction: column; align-items: center;"><div style=" display:block; height:32px; width:32px; padding-bottom:20px;"> <svg aria-label="Threads" height="32px" role="img" viewBox="0 0 192 192" width="32px" xmlns="http://www.w3.org/2000/svg"> <path d="M141.537 88.9883C140.71 88.5919 139.87 88.2104 139.019 87.8451C137.537 60.5382 122.616 44.905 97.5619 44.745C97.4484 44.7443 97.3355 44.7443 97.222 44.7443C82.2364 44.7443 69.7731 51.1409 62.102 62.7807L75.881 72.2328C81.6116 63.5383 90.6052 61.6848 97.2286 61.6848C97.3051 61.6848 97.3819 61.6848 97.4576 61.6855C105.707 61.7381 111.932 64.1366 115.961 68.814C118.893 72.2193 120.854 76.925 121.825 82.8638C114.511 81.6207 106.601 81.2385 98.145 81.7233C74.3247 83.0954 59.0111 96.9879 60.0396 116.292C60.5615 126.084 65.4397 134.508 73.775 140.011C80.8224 144.663 89.899 146.938 99.3323 146.423C111.79 145.74 121.563 140.987 128.381 132.296C133.559 125.696 136.834 117.143 138.28 106.366C144.217 109.949 148.617 114.664 151.047 120.332C155.179 129.967 155.42 145.8 142.501 158.708C131.182 170.016 117.576 174.908 97.0135 175.059C74.2042 174.89 56.9538 167.575 45.7381 153.317C35.2355 139.966 29.8077 120.682 29.6052 96C29.8077 71.3178 35.2355 52.0336 45.7381 38.6827C56.9538 24.4249 74.2039 17.11 97.0132 16.9405C119.988 17.1113 137.539 24.4614 149.184 38.788C154.894 45.8136 159.199 54.6488 162.037 64.9503L178.184 60.6422C174.744 47.9622 169.331 37.0357 161.965 27.974C147.036 9.60668 125.202 0.195148 97.0695 0H96.9569C68.8816 0.19447 47.2921 9.6418 32.7883 28.0793C19.8819 44.4864 13.2244 67.3157 13.0007 95.9325L13 96L13.0007 96.0675C13.2244 124.684 19.8819 147.514 32.7883 163.921C47.2921 182.358 68.8816 191.806 96.9569 192H97.0695C122.03 191.827 139.624 185.292 154.118 170.811C173.081 151.866 172.51 128.119 166.26 113.541C161.776 103.087 153.227 94.5962 141.537 88.9883ZM98.4405 129.507C88.0005 130.095 77.1544 125.409 76.6196 115.372C76.2232 107.93 81.9158 99.626 99.0812 98.6368C101.047 98.5234 102.976 98.468 104.871 98.468C111.106 98.468 116.939 99.0737 122.242 100.233C120.264 124.935 108.662 128.946 98.4405 129.507Z" /></svg></div>  <div style=" font-size: 15px; line-height: 21px; color: #000000; font-weight: 600; "> View on Threads</div></div></a></blockquote>
<script async src="https://www.threads.net/embed.js"></script>

' . "')", __FILE__, __LINE__);

db_query("REPLACE INTO {$db_prefix}mediapro_sites
	(ID,title, website, height,width,  regexmatch, embedcode)
VALUES
(135, 'Threads - Posts','https://threads.net',360,640, 'https://www.threads.net/@([A-Za-z0-9_]*)/post/([A-Za-z0-9_]*)','" . '<blockquote class="text-post-media" data-text-post-permalink="https://www.threads.net/@$1/post/$2" data-text-post-version="0" id="ig-tp-$2" style=" background:#FFF; border-width: 1px; border-style: solid; border-color: #00000026; border-radius: 16px; max-width:540px; margin: 1px; min-width:270px; padding:0; width:99.375%; width:-webkit-calc(100% - 2px); width:calc(100% - 2px);"> <a href="https://www.threads.net/@$1/post/$2" style=" background:#FFFFFF; line-height:0; padding:0 0; text-align:center; text-decoration:none; width:100%; font-family: -apple-system, BlinkMacSystemFont, sans-serif;" target="_blank"> <div style=" padding: 40px; display: flex; flex-direction: column; align-items: center;"><div style=" display:block; height:32px; width:32px; padding-bottom:20px;"> <svg aria-label="Threads" height="32px" role="img" viewBox="0 0 192 192" width="32px" xmlns="http://www.w3.org/2000/svg"> <path d="M141.537 88.9883C140.71 88.5919 139.87 88.2104 139.019 87.8451C137.537 60.5382 122.616 44.905 97.5619 44.745C97.4484 44.7443 97.3355 44.7443 97.222 44.7443C82.2364 44.7443 69.7731 51.1409 62.102 62.7807L75.881 72.2328C81.6116 63.5383 90.6052 61.6848 97.2286 61.6848C97.3051 61.6848 97.3819 61.6848 97.4576 61.6855C105.707 61.7381 111.932 64.1366 115.961 68.814C118.893 72.2193 120.854 76.925 121.825 82.8638C114.511 81.6207 106.601 81.2385 98.145 81.7233C74.3247 83.0954 59.0111 96.9879 60.0396 116.292C60.5615 126.084 65.4397 134.508 73.775 140.011C80.8224 144.663 89.899 146.938 99.3323 146.423C111.79 145.74 121.563 140.987 128.381 132.296C133.559 125.696 136.834 117.143 138.28 106.366C144.217 109.949 148.617 114.664 151.047 120.332C155.179 129.967 155.42 145.8 142.501 158.708C131.182 170.016 117.576 174.908 97.0135 175.059C74.2042 174.89 56.9538 167.575 45.7381 153.317C35.2355 139.966 29.8077 120.682 29.6052 96C29.8077 71.3178 35.2355 52.0336 45.7381 38.6827C56.9538 24.4249 74.2039 17.11 97.0132 16.9405C119.988 17.1113 137.539 24.4614 149.184 38.788C154.894 45.8136 159.199 54.6488 162.037 64.9503L178.184 60.6422C174.744 47.9622 169.331 37.0357 161.965 27.974C147.036 9.60668 125.202 0.195148 97.0695 0H96.9569C68.8816 0.19447 47.2921 9.6418 32.7883 28.0793C19.8819 44.4864 13.2244 67.3157 13.0007 95.9325L13 96L13.0007 96.0675C13.2244 124.684 19.8819 147.514 32.7883 163.921C47.2921 182.358 68.8816 191.806 96.9569 192H97.0695C122.03 191.827 139.624 185.292 154.118 170.811C173.081 151.866 172.51 128.119 166.26 113.541C161.776 103.087 153.227 94.5962 141.537 88.9883ZM98.4405 129.507C88.0005 130.095 77.1544 125.409 76.6196 115.372C76.2232 107.93 81.9158 99.626 99.0812 98.6368C101.047 98.5234 102.976 98.468 104.871 98.468C111.106 98.468 116.939 99.0737 122.242 100.233C120.264 124.935 108.662 128.946 98.4405 129.507Z" /></svg></div> <div style=" font-size: 15px; line-height: 21px; color: #999999; font-weight: 400; padding-bottom: 4px; "> Post by @$1</div> <div style=" font-size: 15px; line-height: 21px; color: #000000; font-weight: 600; "> View on Threads</div></div></a></blockquote><script async src="https://www.threads.net/embed.js"></script>

' . "')", __FILE__, __LINE__);

db_query("REPLACE INTO {$db_prefix}mediapro_sites
	(ID,title, website, height,width,  regexmatch, embedcode)
VALUES
(136, 'imgur Albums','https://imgur.com/',360,640, 'https://imgur.com/a/([A-Za-z0-9_]*)','" . '<blockquote class="imgur-embed-pub" lang="en" data-id="a/$1"  ></blockquote><script async src="//s.imgur.com/min/embed.js" charset="utf-8"></script>

' . "')", __FILE__, __LINE__);



/*
,
(130, 'Rumble','https://rumble.com',360,640, 'https://rumble.com/embed/([A-Za-z0-9]*)/" . '\\' .'\\' . "?pub=([A-Za-z0-9]*)ZSPLITMZhttps://rumble.com/embed/([A-Za-z0-9]*)/','" . '<iframe class="rumble" width="640" height="360" src="https://rumble.com/embed/$1/?pub=4" frameborder="0" allowfullscreen></iframe>

' . "')
*/

global $db_prefix, $boarddir;

	$mediaProItems = array();

    // Make sure still enabled
    if (!empty($enabledList))
    {
        $enableSQL = implode(",",$enabledList);

        db_query("UPDATE {$db_prefix}mediapro_sites SET enabled = 1
    	WHERE id IN ($enableSQL)", __FILE__, __LINE__);

    }



	// Get list of sites that are enabled
	$result = db_query("
	SELECT
		id, title, website, regexmatch,
		embedcode, height,  width
	FROM {$db_prefix}mediapro_sites
	WHERE enabled = 1 ORDER BY title ASC", __FILE__, __LINE__);
	while ($row = mysql_fetch_assoc($result))
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


/*
(23, 'MaYoMo', 'http://www.mayomo.com', 'http://www.mayomo.com/([0-9]*)-(.*)','" . '
<object id="flowplayer" width="480" height="360" data="http://www.mayomo.com/flash/flowplayer-3.1.5.swf" type="application/x-shockwave-flash"><param name="movie" value="http://www.mayomo.com/flash/flowplayer-3.1.5.swf" /><param name="allowfullscreen" value="true" /><param name="flashvars" value=\'\'config={"clip":{"autoPlay":false,"autoBuffering":true,"url":"http://c0368312.cdn.cloudfiles.rackspacecloud.com/$1.mp4"}}\'\' /></object>
' . "')
*/

?>