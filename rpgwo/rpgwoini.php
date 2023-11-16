<?php
require "C:/sites/rpgwoforums/SSI.php";
ob_clean();
header("Content-Type: text/plain");
	$dbresult2 = $smcFunc['db_query']('', "
		SELECT
			ID_SERVER, title, description,ID_MEMBER,enabled,datecreated,
			 server_ip,server_port,server_version,server_updateurl
		FROM  rpgwoforums.rpgwo_servers
		WHERE removed = 0 AND enabled =1");
	while ($row = $smcFunc['db_fetch_assoc']($dbresult2))
	{

		echo 'Name=' . $row['title'] . "\n";
		echo 'Description=' . $row['description'] . "\n";
		echo 'IP=' . $row['server_ip'] . "\n";
		echo 'Port=' . $row['server_port'] . "\n";
		echo 'UpdateURL=' . $row['server_updateurl'] . "\n";

		if ($row['server_version'] == 2)
			echo ";;;Version2\n";
		if ($row['server_version'] == 3)
			echo ";;;Version3\n";

		echo "\n\n";
	}


?>