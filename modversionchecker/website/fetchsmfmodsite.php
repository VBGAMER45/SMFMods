<?php
require dirname(__FILE__)  . '/SSI.php';

/*
 * CREATE TABLE `smf_db_version_checker` (
  `ID_MOD` int(11) NOT NULL AUTO_INCREMENT,
  `ID_PACKER_SERVER` int(10) DEFAULT '0',
  `packageid` varchar(255) DEFAULT NULL,
  `title` varchar(255) DEFAULT NULL,
  `filename` varchar(255) DEFAULT NULL,
  `version` varchar(50) DEFAULT NULL,
  `versionfor` varchar(1000) DEFAULT NULL,
  `author` varchar(255) DEFAULT NULL,
  `description` longtext,
  PRIMARY KEY (`ID_MOD`),
  KEY `VERSION` (`version`)
) ENGINE=MyISAM DEFAULT CHARSET=latin1
 */

// First get the categoreis
$modSettings['disableQueryCheck'] = 1;
$xmlData = simplexml_load_file("https://custom.simplemachines.org/packages/mods/");


//print_r($xmlData);
$modCategories = array();

foreach($xmlData->section as $section)
	{
		//print_r($section);
		// get the remote attribute
		foreach($section->remote[0]->attributes() as $a => $b) {
			//echo $a,'="',$b,"\"\n";

			if ($a == 'href')
				$modCategories[] = (int) $b;


		}


	}

//print_r($modCategories);

// now fetch
$totalMods = 0;
foreach($modCategories as $category)
{
	$start = 0;
	$done = false;
	$count = 0;

	while($done === false)
	{
		$xmlData = simplexml_load_file("https://custom.simplemachines.org/packages/mods/" . $category . '?start=' . $start);
		foreach($xmlData->section->modification as $mod)
		{
			$modTitle = addslashes($mod->name);
			$id = addslashes($mod->id);
			$filename = addslashes($mod->filename);
			$modVersion = addslashes($mod->version);
			$modSMFVersionFor = '';

			foreach($mod->version[0]->attributes() as $a => $b)
			{
				//echo $a,'="',$b,"\"\n";

				if ($a == 'for')
					$modSMFVersionFor = addslashes($b);

			}

			$modAuthor = addslashes($mod->author);
			$modDescription = addslashes($mod->description);
			flush();
			echo "id: " . $id . "<br>\n";

			$count++;

			if (empty($id))
				continue;

			$totalMods++;

/*
			$smcFunc['db_query']('', "INSERT INTO {db_prefix}db_version_checker
						(packageid,title,filename,version,versionfor,author,description)
					VALUES ('$id','$modTitle','$filename','$modVersion','$modSMFVersionFor','$modAuthor','$modDescription')
					ON DUPLICATE KEY UPDATE packageid = '$id',title = '$modTitle',filename = '$filename',version = '$modVersion',versionfor = '$modSMFVersionFor',author = '$modAuthor',description = '$modDescription';
					");

			*/

			$result = mysqli_query($db_connection,"INSERT INTO smfhacks.smf_db_version_checker
						(packageid,title,filename,version,versionfor,author,description,category)
					VALUES ('$id','$modTitle','$filename','$modVersion','$modSMFVersionFor','$modAuthor','$modDescription','$category')
					ON DUPLICATE KEY UPDATE packageid = '$id',title = '$modTitle',filename = '$filename',version = '$modVersion',versionfor = '$modSMFVersionFor',author = '$modAuthor',description = '$modDescription', category = '$category'");
		if (!$result)
			echo mysqli_error($db_connection) ;


		}

		// no more mods found
		if ($count == 0)
			$done = true;


		$count = 0;
		$start += 50;

	}
}


echo 'Total Mods:' . $totalMods;