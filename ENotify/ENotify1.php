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
	loadTemplate('ENotify1');		
  template_enotify_main();
  
	// We use this to deactivate the SMF Wrapping Templates
  die();
}


// This function updates our unread replies log
function ENotifyUpdateUnreadReplies()
{
	global $context, $db_prefix, $ID_MEMBER;

  // We load up Recent.php, and get the unread replies
  require_once('Recent.php');
  UnreadTopics();
    
  // Make sure that there are replies and insert them into ENotify table
  if (!empty($context['topics']))
  {
    foreach ($context['topics'] as $topic)
    {
      // This is our query we use ignore to make sure we don't get an error if the record exists (unique field defined to avoid the extra select query)
		db_query("INSERT IGNORE INTO {$db_prefix}log_enotify_replies
		(enot_item_id,enot_title,enot_time,enot_link,enot_sender,enot_sender_link,id_member)
		VALUES
		('" .$topic['last_post']['id'] . "','" . htmlspecialchars($topic['last_post']['subject'],ENT_QUOTES). "','" .  $topic['last_post']['timestamp'] . "','" .  $topic['last_post']['href'] . "','" .  $topic['last_post']['member']['name'] . "','" .  $topic['last_post']['member']['href'] . "','" .  $ID_MEMBER . "')
		
		", __FILE__, __LINE__);
      

    }
  }	
}

// This function updates our personal messages log
function ENotifyUpdatePms()
{
	global $context, $scripturl, $db_prefix, $ID_MEMBER;

  // We run a query to get the new PM's that this user has
  $request =db_query("
                SELECT p.id_pm, p.id_member_from, p.fromName, p.msgtime, p.subject, pr.id_member, pr.is_read
                FROM {$db_prefix}personal_messages AS p
                LEFT JOIN {$db_prefix}pm_recipients AS pr ON (pr.id_pm = p.id_pm)
                WHERE pr.id_member = $ID_MEMBER
                AND pr.is_read = 0", __FILE__, __LINE__);
            
  while ($row = mysql_fetch_assoc($request))
			$context['pms'][] = array(
				'id' => $row['id_pm'],
				'subject' => $row['subject'],
				'time' => $row['msgtime'],
				'href' => $scripturl. '?action=pm#'. $row['id_pm'],
				'sender' => $row['fromName'],
				'sender-href' => $scripturl. '?action=profile;u='. $row['id_member_from']
			);
			
	mysql_free_result($request);
    
  // Make sure that there are replies and insert them into ENotify table
  if (!empty($context['pms']))
  {
    foreach ($context['pms'] as $pm)
    {
    	 // This is our query we use ignore to make sure we don't get an error if the record exists (unique field defined to avoid the extra select query)
    	db_query("INSERT IGNORE INTO {$db_prefix}log_enotify_pms
    	(enot_item_id,enot_title,enot_time,enot_link,enot_sender,enot_sender_link,id_member)
    	VALUES
    	('" . $pm['id'] . "','" . $pm['subject'] . "','" . $pm['time']. "','" .  $pm['href']. "','" . $pm['sender'] . "','" . $pm['sender-href'] . "','" . $ID_MEMBER . "')
    	
    	", __FILE__, __LINE__);
     
    }
  }	
}

// Let's get the unread replies
function ENotifyLoad()
{
	global $context, $modSettings, $db_prefix, $ID_MEMBER;

  // Get the unread replies notifications if they are enabled and parse them into an array
  if (!empty($modSettings['enotify_replies']))
  {
    $request =db_query("
                  SELECT id_enot, enot_title, enot_time, enot_link, enot_sender, enot_sender_link, id_member, enot_read
                  FROM {$db_prefix}log_enotify_replies
                  WHERE id_member = $ID_MEMBER
                  AND enot_read = 0
                  ORDER BY enot_time DESC", __FILE__, __LINE__);
                
    
    $context['enot']['replies'] = array();
              
    while ($row = mysql_fetch_assoc($request))
  			$context['enot']['replies'][] = array(
  				'id' => $row['id_enot'],
  				'title' => $row['enot_title'],
  				'time' => $row['enot_time'],
  				'href' => $row['enot_link'],
  				'sender' => $row['enot_sender'],
  				'sender-href' => $row['enot_sender_link']
  			);
  			
  	mysql_free_result($request);
	}
	
  // Get the pm notifications if they are enabled and parse them into an array
  if (!empty($modSettings['enotify_pms']))
  {
    $request = db_query("
                  SELECT id_enot, enot_title, enot_time, enot_link, enot_sender, enot_sender_link, id_member, enot_read
                  FROM {$db_prefix}log_enotify_pms
                  WHERE id_member = $ID_MEMBER
                  AND enot_read = 0
                  ORDER BY enot_time DESC", __FILE__, __LINE__);
               
    $context['enot']['pms'] = array();
              
    while ($row = mysql_fetch_assoc($request))
  			$context['enot']['pms'][] = array(
  				'id' => $row['id_enot'],
  				'title' => $row['enot_title'],
  				'time' => $row['enot_time'],
  				'href' => $row['enot_link'],
  				'sender' => $row['enot_sender'],
  				'sender-href' => $row['enot_sender_link']
  			);
  			
  	mysql_free_result($request);
	}
  
}

// Our garbage collection function
function ENotifyGarbageCollect()
{
	global $modSettings, $db_prefix;
	
	$currenttime = time();
	$exptime = ($modSettings['enotify_exp'] * 60 * 60);
	$deletetime = ($currenttime - $exptime);
	
 db_query("
     DELETE FROM {$db_prefix}log_enotify_replies
     WHERE enot_time < $deletetime", __FILE__, __LINE__);
  
 db_query("
     DELETE FROM {$db_prefix}log_enotify_pms
     WHERE enot_time <  $deletetime", __FILE__, __LINE__);
 
    	
}

// This functions sets the log data read when it is actually read
function ENotifySetRead($id)
{
	global $db_prefix, $context, $ID_MEMBER;

 db_query("
     UPDATE {$db_prefix}log_enotify_replies
        SET enot_read = 1
				WHERE id_member =$ID_MEMBER
        AND id_enot = $id", __FILE__, __LINE__);

  
 db_query("
     UPDATE {$db_prefix}log_enotify_pms
        SET enot_read = 1
				WHERE id_member = $ID_MEMBER
        AND id_enot = $id", __FILE__, __LINE__);

}

?>