<?php
################################
##	.LORD.
##	Topic Count Based Groups
##	v1.0
################################

	// Fetch the postgroups!
	$request = $smcFunc['db_query']('', '
		SELECT id_group, min_posts FROM {db_prefix}membergroups WHERE min_posts != {int:min_posts}',
		array('min_posts' => -1)
	);
	$postgroups = array();
	while ($row = $smcFunc['db_fetch_assoc']($request))
		$postgroups[$row['id_group']] = $row['min_posts'];
	$smcFunc['db_free_result']($request);

	// Sort them this way because if it's done with MySQL it causes a filesort :(.
	arsort($postgroups);

	if (!empty($postgroups))
	{
		// Set all membergroups from most posts to least posts.
		$conditions = '';
		foreach ($postgroups as $id => $min_posts)
		{
			$conditions .= '
					WHEN posts >= ' . $min_posts . (!empty($lastMin) ? ' AND posts <= ' . $lastMin : '') . ' THEN ' . $id;
			$lastMin = $min_posts;
		}

		// A big fat CASE WHEN... END is faster than a zillion UPDATE's ;).
		$smcFunc['db_query']('', '
			UPDATE {db_prefix}members SET id_post_group = CASE {raw:conditions} ELSE 0 END',
			array('conditions' => $conditions)
		);
	}

?>