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

if (!defined('SMF'))
	die('Hacking attempt...');

function ModifyS3Settings($return_config = false)
{
	global $txt, $scripturl, $context, $settings, $sc, $smcFunc, $boarddir;

	$txt['s3_cronurl_note'] .= "<br>" . $txt['s3_cronurl'] . ': ' .  $boarddir . '/s3cron.php';

	$config_vars = array(
			array('check', 's3_enabled'),
			array('text', 's3_access_key'),
			array('text', 's3_secret_access_key'),
			array('text', 's3_bucket'),
			array('text', 's3_region'),
			array('text', 's3_domain', 'subtext' =>  $txt['s3_domain_note']),
			array('check', 's3_thumbnails'),
			array('check', 's3_delete_local'),
			array('int', 's3_cron_items'),
			array('title', 's3_cronjob'),
			array('desc', 's3_cronurl_note'),
	);

	// Saving?
	if (isset($_GET['save']))
	{

	// Get the settings
	$s3_enabled = isset($_REQUEST['s3_enabled']) ? 1 : 0;
	$s3_delete_local =  isset($_REQUEST['s3_delete_local']) ? 1 : 0;
	$s3_access_key = $smcFunc['htmlspecialchars'](trim($_REQUEST['s3_access_key']), ENT_QUOTES);
	$s3_secret_access_key = $smcFunc['htmlspecialchars'](trim($_REQUEST['s3_secret_access_key']), ENT_QUOTES);
	$s3_bucket = $smcFunc['htmlspecialchars'](trim($_REQUEST['s3_bucket']), ENT_QUOTES);
	$s3_region = $smcFunc['htmlspecialchars'](trim($_REQUEST['s3_region']), ENT_QUOTES);
	$s3_domain = $smcFunc['htmlspecialchars'](trim($_REQUEST['s3_domain']), ENT_QUOTES);

	$_POST['s3_access_key'] = $s3_access_key;
	$_POST['s3_secret_access_key'] = $s3_secret_access_key;
	$_POST['s3_bucket'] = $s3_bucket;
	$_POST['s3_region'] = $s3_region;
	$_POST['s3_domain'] = $s3_domain;

	if (empty($s3_bucket) || empty($s3_region) || empty($s3_access_key) || empty($s3_secret_access_key))
		$s3_enabled = 0;

	// Check keys and settings are valid
	if ($s3_enabled == 1)
	{
		if (!preg_match('/[A-Z0-9]{20}/', $s3_access_key))
			fatal_error($txt['error_bad_access_key'],false);

		if (!preg_match('/[A-Za-z0-9\/+=]{40}/', $s3_secret_access_key))
			fatal_error($txt['error_bad_secret_access_key'],false);

		require_once dirname(__FILE__) . '/s3system/vendor/autoload.php';

		$bucket_endpoint = false;

		// Check the settings
		try
		{
			$s3_client = new \Aws\S3\S3Client([
						'credentials' => [
							'key'    => $s3_access_key,
							'secret' => $s3_secret_access_key,
						],
						'http'        => [
							'verify' => false,
						],
						'region'      => $s3_region,
						'version'     => 'latest',
						//'bucket_endpoint' => $bucket_endpoint,
						//'endpoint' => $s3_domain,
					]);
					// Upload Test File
					$s3_client->upload($s3_bucket, 'dprocheck.txt', 'Does This Work?');
					// Delete the file
					$s3_client->deleteObject([
						'Bucket' => $s3_bucket,
						'Key'    => 'dprocheck.txt',
					]);
				}
				catch (\Aws\S3\Exception\S3Exception $e)
				{
					fatal_error($e->getMessage(),false);
				}
	}




		saveDBSettings($config_vars);
		redirectexit('action=admin;area=featuresettings;sa=s3');
	}

	$context['post_url'] = $scripturl . '?action=admin;area=featuresettings;save;sa=s3';
	$context['settings_title'] = $txt['amazon_s3'];

	prepareDBSettingContext($config_vars);
}

function DeleteS3File($filename)
{
	global $modSettings;
	require_once dirname(__FILE__) . '/s3system/vendor/autoload.php';

		try
		{

					$s3_client = new \Aws\S3\S3Client([
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



					// Delete the file
					$s3_client->deleteObject([
						'Bucket' => $modSettings['s3_bucket'],
						'Key'    => $filename,
					]);

					}
				catch (\Aws\S3\Exception\S3Exception $e)
				{
					log_error($e->getMessage());
				}

}

function S3GetSignedUrl($filename, $orginalFilename, $image = 0)
{
	global $modSettings;
	require_once dirname(__FILE__) . '/s3system/vendor/autoload.php';

		try
		{
				if (empty($modSettings['s3_domain']))
					$s3_client = new \Aws\S3\S3Client([
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
				else
					$s3_client = new \Aws\S3\S3Client([
						'credentials' => [
							'key'    => $modSettings['s3_access_key'],
							'secret' => $modSettings['s3_secret_access_key'],
						],
						'http'        => [
							'verify' => false,
						],
						'region'      => $modSettings['s3_region'],
						'version'     => 'latest',
						'signature_version' => 'v4',
						'endpoint' => $modSettings['s3_domain'],
						'bucket_endpoint' => true,
					]);



					// public url
				 	//$fileUrl =	$s3_client->getObjectUrl($modSettings['s3_bucket'],$filename);
					if ($image == 0)
					{
						$cmd = $s3_client->getCommand('GetObject', [
						'Bucket' => $modSettings['s3_bucket'],
						'Key' => $filename,
						'ResponseContentType' => 'application/octet-stream',
						'ResponseContentDisposition'    => 'attachment; filename="' . $orginalFilename  .'"'
					]);

					}
					else
					{
						$extension = substr(strrchr($orginalFilename, '.'), 1);
						$extension = str_replace("_thumb","",$extension);

						$cmd = $s3_client->getCommand('GetObject', [
						'Bucket' => $modSettings['s3_bucket'],
						'Key' => $filename,
						'ResponseContentType' => 'image/' . $extension,
					]);

					}

					$request = $s3_client->createPresignedRequest($cmd, '+60 minutes');

					// Get the actual presigned-url
					$presignedUrl = (string)$request->getUri();



					return $presignedUrl;

					}
				catch (\Aws\S3\Exception\S3Exception $e)
				{
					log_error($e->getMessage());
				}

}


function s3_integrate_modify_features(&$subActions)
{
	global $context, $txt;
	$subActions['s3'] = 'ModifyS3Settings';

	$context[$context['admin_menu_name']]['tab_data']['tabs']['s3'] = array('description' => $txt['amazon_s3_note']);

}

function s3_integrate_admin_areas(&$admin_areas)
{
	global $txt;

	$admin_areas['config']['areas']['featuresettings']['subsections']['s3'] = array($txt['amazon_s3']);

}

function s3_integrate_pre_download_request()
{
	global $modSettings;
	if ($modSettings['s3_enabled'] == 1)
		$modSettings['enableCompressedOutput'] = 0;
}
?>