<?php
/**********************************************************************************
* ENotify.php                                                                     *
***********************************************************************************
* ENotify: Ajax Notifications Mod for SMF                                         *
* Eren "forsakenlad" Yaþarkurt                                                    *
***********************************************************************************
* This modification is a free contribution to SMF and all the code and images     *
* used can be used freely as long as it stays within SMF, otherwise written       *
* permission is required.                                                         * 
**********************************************************************************/

if (!defined('SMF'))
	die('Hacking attempt...');

// The main function of ENotifyMain
function ENotifyMain()
{
	global $modSettings;

  // Guests can't have unread things, we don't know anything about them.
	is_not_guest();
  
  // Update our unread replies log
  if (!empty($modSettings['enotify_replies']))
    ENotifyUpdateUnreadReplies();

  // Update our personal messages log  
  if (!empty($modSettings['enotify_pms']))
    ENotifyUpdatePms();
  
  // Load up the notifications at last :)
  ENotifyLoad();
  
  // Run our garbage collection randomly (setting 0.05% chance for it to run)
  $random = rand(1, 5000);
  if ($random == '1987')
    ENotifyGarbageCollect();
    
  // Load the language and the template file.
	loadLanguage('ENotify');
	loadTemplate('ENotify');		
  template_enotify_main();
  
	// We use this to deactivate the SMF Wrapping Templates
  die();
}


// This function updates our unread replies log
function ENotifyUpdateUnreadReplies()
{
	global $context, $smcFunc;

  // We load up Recent.php, and get the unread replies
  require_once('Recent.php');
  UnreadTopics();
    
  // Make sure that there are replies and insert them into ENotify table
  if (!empty($context['topics']))
  {
    foreach ($context['topics'] as $topic)
    {
      // This is our query we use ignore to make sure we don't get an error if the record exists (unique field defined to avoid the extra select query)
      $smcFunc['db_insert']('ignore',
    		'{db_prefix}log_enotify_replies',
    		array('enot_item_id' => 'int', 'enot_title' => 'string-255', 'enot_time' => 'int', 'enot_link' => 'text', 'enot_sender' => 'string-255', 'enot_sender_link' => 'text', 'id_member' => 'int'),
    		array($topic['last_post']['id'], $topic['last_post']['subject'], $topic['last_post']['timestamp'], $topic['last_post']['href'], $topic['last_post']['member']['name'], $topic['last_post']['member']['href'], $context['user']['id']),
    		array('id_enot')
    	);
    }
  }	
}

// This function updates our personal messages log
function ENotifyUpdatePms()
{
	global $context, $scripturl, $smcFunc;

  // We run a query to get the new PM's that this user has
  $request = $smcFunc['db_query']('', '
                SELECT p.id_pm, p.id_member_from, p.from_name, p.msgtime, p.subject, pr.id_member, pr.is_new
                FROM {db_prefix}personal_messages AS p
                LEFT JOIN {db_prefix}pm_recipients AS pr ON (pr.id_pm = p.id_pm)
                WHERE pr.id_member = {int:id_member}
                AND pr.is_new = 1',
                array(
                    'id_member' => $context['user']['id'],
                )
             );
            
  while ($row = $smcFunc['db_fetch_assoc']($request))
			$context['pms'][] = array(
				'id' => $row['id_pm'],
				'subject' => $row['subject'],
				'time' => $row['msgtime'],
				'href' => $scripturl. '?action=pm#'. $row['id_pm'],
				'sender' => $row['from_name'],
				'sender-href' => $scripturl. '?action=profile;u='. $row['id_member_from']
			);
			
	$smcFunc['db_free_result']($request);
    
  // Make sure that there are replies and insert them into ENotify table
  if (!empty($context['pms']))
  {
    foreach ($context['pms'] as $pm)
    {
      // This is our query we use ignore to make sure we don't get an error if the record exists (unique field defined to avoid the extra select query)
      $smcFunc['db_insert']('ignore',
    		'{db_prefix}log_enotify_pms',
    		array('enot_item_id' => 'int', 'enot_title' => 'string-255', 'enot_time' => 'int', 'enot_link' => 'text', 'enot_sender' => 'string-255', 'enot_sender_link' => 'text', 'id_member' => 'int'),
    		array($pm['id'], $pm['subject'], $pm['time'],  $pm['href'], $pm['sender'], $pm['sender-href'], $context['user']['id']),
    		array('id_enot')
    	);
    }
  }	
}

// Let's get the unread replies
function ENotifyLoad()
{
	global $context, $modSettings, $smcFunc;

  // Get the unread replies notifications if they are enabled and parse them into an array
  if (!empty($modSettings['enotify_replies']))
  {
    $request = $smcFunc['db_query']('', '
                  SELECT id_enot, enot_title, enot_time, enot_link, enot_sender, enot_sender_link, id_member, enot_read
                  FROM {db_prefix}log_enotify_replies
                  WHERE id_member = {int:id_member}
                  AND enot_read = 0
                  ORDER BY enot_time DESC',
                  array(
                      'id_member' => $context['user']['id'],
                  )
               );
    
    $context['enot']['replies'] = array();
              
    while ($row = $smcFunc['db_fetch_assoc']($request))
  			$context['enot']['replies'][] = array(
  				'id' => $row['id_enot'],
  				'title' => $row['enot_title'],
  				'time' => $row['enot_time'],
  				'href' => $row['enot_link'],
  				'sender' => $row['enot_sender'],
  				'sender-href' => $row['enot_sender_link']
  			);
  			
  	$smcFunc['db_free_result']($request);
	}
	
  // Get the pm notifications if they are enabled and parse them into an array
  if (!empty($modSettings['enotify_pms']))
  {
    $request = $smcFunc['db_query']('', '
                  SELECT id_enot, enot_title, enot_time, enot_link, enot_sender, enot_sender_link, id_member, enot_read
                  FROM {db_prefix}log_enotify_pms
                  WHERE id_member = {int:id_member}
                  AND enot_read = 0
                  ORDER BY enot_time DESC',
                  array(
                      'id_member' => $context['user']['id'],
                  )
               );
               
    $context['enot']['pms'] = array();
              
    while ($row = $smcFunc['db_fetch_assoc']($request))
  			$context['enot']['pms'][] = array(
  				'id' => $row['id_enot'],
  				'title' => $row['enot_title'],
  				'time' => $row['enot_time'],
  				'href' => $row['enot_link'],
  				'sender' => $row['enot_sender'],
  				'sender-href' => $row['enot_sender_link']
  			);
  			
  	$smcFunc['db_free_result']($request);
	}
  
}

// Our garbage collection function
function ENotifyGarbageCollect()
{
	global $modSettings, $smcFunc;
	
	$currenttime = time();
	$exptime = ($modSettings['enotify_exp'] * 60 * 60);
	$deletetime = ($currenttime - $exptime);
	
  $smcFunc['db_query']('', '
     DELETE FROM {db_prefix}log_enotify_replies
     WHERE enot_time < {int:deletetime}',
     array(
         'deletetime' => $deletetime,
     )
  );
  
  $smcFunc['db_query']('', '
     DELETE FROM {db_prefix}log_enotify_pms
     WHERE enot_time < {int:deletetime}',
     array(
         'deletetime' => $deletetime,
     )
  );	
}

// This functions sets the log data read when it is actually read
function ENotifySetRead($id)
{
	global $smcFunc, $context;

  $smcFunc['db_query']('', '
     UPDATE {db_prefix}log_enotify_replies
        SET enot_read = 1
				WHERE id_member = {int:id_member}
        AND id_enot = {int:id_enot}',
     array(
         'id_member' => $context['user']['id'],
         'id_enot' => $id,
     )
  );
  
  $smcFunc['db_query']('', '
     UPDATE {db_prefix}log_enotify_pms
        SET enot_read = 1
				WHERE id_member = {int:id_member}
        AND id_enot = {int:id_enot}',
     array(
         'id_member' => $context['user']['id'],
         'id_enot' => $id,
     )
  );
}

?>