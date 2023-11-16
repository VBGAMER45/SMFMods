<?php
################################
##	.LORD.
##	Topic Count Based Groups
##	v1.0
################################

	// Fetch the postgroups!
	$request = db_query("
		SELECT ID_GROUP, minPosts
		FROM {$db_prefix}membergroups
		WHERE minPosts != -1", __FILE__, __LINE__);
	$postgroups = array();
	while ($row = mysql_fetch_assoc($request))
		$postgroups[$row['ID_GROUP']] = $row['minPosts'];


	// Sort them this way because if it's done with MySQL it causes a filesort :(.
	arsort($postgroups);

	if (!empty($postgroups))
	{
		// Set all membergroups from most posts to least posts.
		$conditions = '';
		foreach ($postgroups as $id => $minPosts)
		{
			$conditions .= '
					WHEN posts >= ' . $minPosts . (!empty($lastMin) ? ' AND posts <= ' . $lastMin : '') . ' THEN ' . $id;
			$lastMin = $minPosts;
		}

		// A big fat CASE WHEN... END is faster than a zillion UPDATE's ;).
		db_query(" UPDATE {$db_prefix}members SET ID_POST_GROUP = CASE{$conditions} ELSE 0 END"
			, __FILE__, __LINE__);
	}

?>