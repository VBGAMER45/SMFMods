<?php

/*******************************************************************************
	ATTENTION: If you are trying to install this manually, you should try
	the package manager. If that does not work, you can run this file by
	accessing it by url. Please ensure it is in the same location as your
	forum's SSI.php file.
*******************************************************************************/

//	Pretty URLs Extras 0.9

//	If SSI.php is in the same place as this file, and SMF isn't defined, this is being run standalone.
if (file_exists(dirname(__FILE__) . '/SSI.php') && !defined('SMF'))
{
	require_once(dirname(__FILE__) . '/SSI.php');
}
//	Hmm... no SSI.php and no SMF?
elseif (!defined('SMF'))
	die('<b>Error:</b> Cannot install - please verify you put this in the same place as SMF\'s SSI.php.');

require_once($sourcedir . '/Subs-PrettyUrls.php');

//	Add these filters
$prettyFilters = unserialize($modSettings['pretty_filters']);
//	A patch to fix the relative URLs used by the arcade mod
$prettyFilters['arcade'] = array(
	'description' => 'A patch for the arcade mod',
	'enabled' => 0,
	'rewrite' => array(
		'priority' => 70,
		'rule' => 'RewriteRule ^arcade/index\.php$ index.php?action=arcade [L,QSA]',
	),
	'title' => 'Arcade <a href="http://www.smfarcade.info" target="_blank">Website</a>',
);
//	A redirection patch for the SEO4SMF mod
$prettyFilters['seo4smf'] = array(
	'description' => 'A patch to redirect pages from the SEO4SMF format to the Pretty URLs format',
	'enabled' => 0,
	'rewrite' => array(
		'priority' => 25,
		'rule' => array(
			'RewriteRule ^(.*)-b([0-9]*)\.([0-9]*)/;(.*) index.php?board=$2.$3;$4 [L,QSA]',
			'RewriteRule ^(.*)-b([0-9]*)\.([0-9]*)/$ index.php?board=$2.$3 [L,QSA]',
			'RewriteRule ^(.*)-b([0-9]*)\.([0-9])$ index.php?board=$2.$3 [L,QSA]',
			'RewriteRule ^(.*)-t([0-9]*)\.([0-9]*)\.html;((\?:from|msg|new)[0-9]*);(.*)$ index.php?topic=$2.$4;$6 [L,QSA]',
			'RewriteRule ^(.*)-t([0-9]*)\.([0-9]*)\.html;((\?:from|msg|new)[0-9]*) index.php?topic=$2.$4 [L,QSA]',
			'RewriteRule ^(.*)-t([0-9]*)\.([0-9]*)\.html;(.*)$ index.php?topic=$2.$3;$4 [L,QSA]',
			'RewriteRule ^(.*)-t([0-9]*)\.([0-9]*)\.html$ index.php?topic=$2.$3 [L,QSA]',
		),
	),
	'title' => 'SEO4SMF redirections',
);
//	Pretty URLs for Tiny Portal's articles
$prettyFilters['tp-articles'] = array(
	'description' => 'Rewrite Tiny Portal article URLs',
	'enabled' => 0,
	'filter' => array(
		'priority' => 30,
		'callback' => 'pretty_tp_articles_filter',
	),
	'rewrite' => array(
		'priority' => 30,
		'rule' => 'RewriteRule ^page/([^/]+)/?$ ./index.php?pretty;page=$1 [L,QSA]',
	),
	'title' => 'Tiny Portal articles <a href="http://www.tinyportal.net" target="_blank">Website</a>',

);

//	Pretty URLs for Tagging System Tags
$prettyFilters['smftags'] = array(
        "description" => "Tagging system for topics filter",
        "enabled"  => 0,
        "filter"  => array(
            "priority"  => 31,
            "callback"  => "pretty_tagging_filter"
        ),
        "rewrite"  => array(
            "priority"  => 31,
            "rule"  => "RewriteRule ^tags/([^/]+)/([0-9]*)/?$ ./index.php?action=tags;tagid=$2 [L,QSA]"
        ),
      "title"  => 'Tagging System Pretty Filter <a href="http://custom.simplemachines.org/mods/index.php?mod=579" target="_blank">Website</a>'
);


//	Pretty URLs for Download System
$prettyFilters['downloadsystem'] = array(
        "description" => "Download System filter for downloads",
        "enabled"  => 0,
        "filter"  => array(
            "priority"  => 32,
            "callback"  => "pretty_downloadssystem_filter"
        ),
        "rewrite"  => array(
            "priority"  => 32,
            "rule"  => array(
            		"RewriteRule ^downloads/([^/]+)/([^/]+)/([0-9]*)/?$ ./index.php?action=downloads;sa=view;down=$3 [L,QSA]",
        			),
         ),
      "title"  => 'Download System Pretty Filter <a href="http://www.smfhacks.com/download-system-pro.php" target="_blank">Website</a>'
);


//	Pretty URLs for SMF Gallery
$prettyFilters['smfgallery'] = array(
        "description" => "SMF Gallery filter for pictures",
        "enabled"  => 0,
        "filter"  => array(
            "priority"  => 33,
            "callback"  => "pretty_smfgallery_filter"
        ),
        "rewrite"  => array(
            "priority"  => 33,
            "rule"  => array(
            		"RewriteRule ^gallery/([^/]+)/([^/]+)/([0-9]*)/?$ ./index.php?action=gallery;sa=view;id=$3 [L,QSA]",
            		"RewriteRule ^gallery/([^/]+)/([^/]+)/([0-9]*)/?$ ./index.php?action=gallery;sa=view&id=$3 [L,QSA]",
        			),
         ),
      "title"  => 'SMF Gallery Pretty Filter <a href="http://www.smfhacks.com/smf-gallery.php" target="_blank">Website</a>'
);

//	Pretty URLs for SMF Articles
$prettyFilters['smfarticles'] = array(
        "description" => "SMF Articles filter for articles",
        "enabled"  => 0,
        "filter"  => array(
            "priority"  => 34,
            "callback"  => "pretty_smfarticles_filter"
        ),
        "rewrite"  => array(
            "priority"  => 34,
            "rule"  => array(
            		"RewriteRule ^articles/([^/]+)/([^/]+)/([0-9]*)/?$ ./index.php?action=articles;sa=view;article=$3 [L,QSA]",
        			),
         ),
      "title"  => 'SMF Articles Pretty Filter <a href="http://custom.simplemachines.org/mods/index.php?mod=1354" target="_blank">Website</a>'
);

//	Pretty URLs for SMF Store
$prettyFilters['smfstore'] = array(
        "description" => "SMF Store filter for store products",
        "enabled"  => 0,
        "filter"  => array(
            "priority"  => 35,
            "callback"  => "pretty_smfstore_filter"
        ),
        "rewrite"  => array(
            "priority"  => 35,
            "rule"  => array(
            		"RewriteRule ^store/([^/]+)/([^/]+)/([0-9]*)/?$ ./index.php?action=store;sa=view;id=$3 [L,QSA]",
        			),
         ),
      "title"  => 'SMF Store Pretty Filter <a href="http://www.smfhacks.com/smf-store.php" target="_blank">Website</a>'
);

//	Pretty URLs for SMF Classifieds
$prettyFilters['smfclassifieds'] = array(
        "description" => "SMF Classifieds filter for auctions and listings",
        "enabled"  => 0,
        "filter"  => array(
            "priority"  => 36,
            "callback"  => "pretty_smfclassifieds_filter"
        ),
        "rewrite"  => array(
            "priority"  => 36,
            "rule"  => array(
            		"RewriteRule ^classifieds/([^/]+)/([^/]+)/([0-9]*)/?$ ./index.php?action=classifieds;sa=view;id=$3 [L,QSA]",
        			),
         ),
      "title"  => 'SMF Classifieds Pretty Filter <a href="http://www.smfhacks.com/smf-classifieds.php" target="_blank">Website</a>'
);

//	Pretty URLs for EzPortal Pages
$prettyFilters['ezportalpages'] = array(
        "description" => "Pretty Urls for ezPortal pages",
        "enabled"  => 0,
        "filter"  => array(
            "priority"  => 37,
            "callback"  => "pretty_ezportalpages_filter"
        ),
        "rewrite"  => array(
            "priority"  => 37,
            "rule"  => array(
            		"RewriteRule ^ezportal/pages/([^/]+)/([0-9]*)/?$ ./index.php?action=ezportal;sa=page;p=$2 [L,QSA]",
        			),
         ),
      "title"  => 'EzPortal Pages Pretty Filter <a href="http://www.ezportal.com" target="_blank">Website</a>'
);

//   Pretty URLs for Aeva Media
$prettyFilters['aeva'] = array(
   'description' => 'Aeva Media filter',
   'enabled'  => 0,
   'filter'  => array(
      'priority'  => 38,
      'callback'  => 'pretty_aeva_filter'
   ),
   'rewrite'  => array(
      'priority'  => 38,
      'rule'  => array(
         'RewriteRule ^media/(thumba?|preview)/([0-9]+)/?(.*)$ index.php?action=media;sa=media;in=$2;$1;$3 [L,QSA]',
         'RewriteRule ^media/(?:(album|item|media)/([0-9]+)/?)?(.*)$ index.php?action=media;sa=$1;in=$2;$3 [L,QSA]',
      ),
   ),
   'title'  => 'Aeva Media Pretty Filter <a href="http://aeva.noisen.com/" target="_blank">Website</a>'
);

// Pretty URLs for GoogleTagged
$prettyFilters['googletagged'] = array(
        "description" => "GoogleTagged",
        "enabled"  => 0,
        "filter"  => array(
            "priority"  => 39,
            "callback"  => "pretty_googletagged_filter"
        ),
        "rewrite"  => array(
            "priority"  => 39,
            "rule"  => "RewriteRule ^tagged/([0-9]*)/([^/]+)/?$ ./index.php?action=tagged;id=$1;tag=$2 [L,QSA]"
        ),
      "title"  => 'GoogleTagged Pretty URLs Filter'
);


updateSettings(array('pretty_filters' => isset($smcFunc) ? serialize($prettyFilters) : addslashes(serialize($prettyFilters))));

//	Update everything now
pretty_update_filters();

?>
