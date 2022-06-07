<?php
require dirname(__FILE__)  . '/SSI.php';

$mods = trim($_REQUEST['mods']);
$smfversion = addslashes($_REQUEST['smfversion']);

//format packageid:version|packageid:version
$updatesFound = array();
if (!empty($mods))
{
	$packages = explode("|",$mods);

	if (!empty($packages))
	{
		foreach($packages as $package)
		{
			$tmp = explode(",", $package);

			$packageID = trim($tmp[0]);
			$packageVersion = trim($tmp[1]);

			$packageID = addslashes($packageID);
			$packageVersion = addslashes($packageVersion);

			// Check if package is
			$dbresult1 = $smcFunc['db_query']('', "
				SELECT
					ID_MOD,packageid,title,version,filename,category  
				FROM {db_prefix}db_version_checker 
				WHERE packageid = '$packageID' LIMIT 1");

			$row = $smcFunc['db_fetch_assoc']($dbresult1);
			if (!empty($row['ID_MOD']))
			{
				if ($packageVersion != $row['version'])
				{
					$tmpPackage = array();
					$tmpPackage['packageid'] = $row['packageid'];
					//$tmpPackage['title'] = $row['title'];
					$tmpPackage['version'] = $row['version'];
					$tmpPackage['filename'] = $row['filename'];
					$tmpPackage['category'] = $row['category'];

					$updatesFound[] = $tmpPackage;

				}
			}

		}

	}
}

ob_clean();
echo serialize($updatesFound);

exit;