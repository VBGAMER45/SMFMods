<?php
/*
S3 System for SMF
Version 1.0
by:vbgamer45
https://www.smfhacks.com
Copyright 2021 SMFHacks.com

############################################
License Information:
S3 System for SMF is NOT free software.
This software may not be redistributed.

The license is good for a single instance / install on a website.
You are allowed only one active install for each license purchase.

Links to https://www.smfhacks.com must remain unless
branding free option is purchased.
#############################################
*/
@ini_set('max_execution_time', 0);
@ini_set('memory_limit', '1024M');
// Need SSI to do queries to the database
global $ssi_guest_access, $sourcedir;
$ssi_guest_access = 1;
require(dirname(__FILE__) . '/SSI.php');

if (empty($modSettings['s3_enabled']))
	return;

require_once  $sourcedir. '/s3system/vendor/autoload.php';
echo 'Start S3 Sync';
$time_start = microtime(true);
		try
		{			$s3_client = new \Aws\S3\S3Client([
						'credentials' => [
							'key'    => $modSettings['s3_access_key'],
							'secret' => $modSettings['s3_secret_access_key'],
						],
						'http'        => [
							'verify' => false,
						],
						'region'      => $modSettings['s3_region'],
						'version'     => 'latest',
					]);

				if (empty($modSettings['s3_cron_items']))
					$modSettings['s3_cron_items'] = 100;

				$noThumbnails = "";
				if (empty($modSettings['s3_thumbnails']))
					$noThumbnails = " AND attachment_type != 3 ";

				// Loop though all the files
			    $dbresult = $smcFunc['db_query']('', "
				SELECT
					id_attach, filename, file_hash, id_folder
				FROM {db_prefix}attachments
				WHERE s3 = 0 AND attachment_type != 1 $noThumbnails ORDER BY id_attach DESC LIMIT " . $modSettings['s3_cron_items']);
					while ($row = $smcFunc['db_fetch_assoc']($dbresult))
					{
						$filename = getAttachmentFilename($row['filename'], $row['id_attach'], $row['id_folder'], false, $row['file_hash']);

						if (empty($row['file_hash']))
							$row['file_hash'] = $row['filename'];

							if (file_exists($filename))
							{

								$fp = fopen($filename, "r");
								// acl public or private
								$s3_client->upload($modSettings['s3_bucket'], $row['file_hash'],$fp, 'private');

								fclose($fp);

								// Delete original file
								if ($modSettings['s3_delete_local'] == 1)
								{
									@unlink($filename);
								}

								// Mark that it is on S3
								$smcFunc['db_query']('', "UPDATE {db_prefix}attachments SET s3 = 1 WHERE id_attach = " . $row['id_attach']);
							}
							else // handle case via not exist for some reason
								$smcFunc['db_query']('', "UPDATE {db_prefix}attachments SET s3 = 2 WHERE id_attach = " . $row['id_attach']);


					}
				$smcFunc['db_free_result']($dbresult);


				}
				catch (\Aws\S3\Exception\S3Exception $e)
				{
					log_error($e->getMessage());
				}

echo 'Finished S3 Sync '. "\n";

$time_end = microtime(true);
$time = $time_end - $time_start;
echo ' Time Taken: ' . $time . "\n";
?>