<?php
// ENotify Template 1.0

function template_enotify_main()
{
	global $context, $settings, $options, $txt;
	
	echo'({
		"title": "'. $txt['notification_feed']. '",
		"generator": "'. $txt['notification_generator']. '",
		"items": [
';

  
  
  if (!empty($context['enot']['replies']))
  {
  	
  	$last_item2 = end($context['enot']['replies']);
  	
  
	  foreach ($context['enot']['replies'] as $enot)
	  {
	      $message = '<a href=\''. $enot['href']. '\'>'. $enot['sender']. ' '. $txt['notification_reply_sent']. ' \''. $enot['title']. '\'.</a>';
	      echo '
	                {
	            			"title": "'. $txt['notification_reply_new']. '",
	            			"message": "'. $message. '"
	            	   }';
	            	   
	      if ($enot != $last_item2 || ($enot == $last_item2 && !empty($context['enot']['pms'])))
	        echo ',';
	        
	      
	        
	      echo "\n";
	      
	      // Now that the notifications are read make sure they are set read as well
	      ENotifySetRead($enot['id']); 
	  }
  }
  
  

  

  if (!empty($context['enot']['pms']))
  {
  	$last_item = end($context['enot']['pms']);
 
	  foreach ($context['enot']['pms'] as $enot)
	  {  
	      $message = '<a href=\''. $enot['href']. '\'>'. $enot['sender']. ' '. $txt['notification_pm_sent']. ' \''. $enot['title']. '\'.</a>';
	
	      echo '
	                {
	            			"title": "'. $txt['notification_pm_new']. '",
	            			"message": "'. $message. '"
	            	   }';
	            	   
	      if ($enot != $last_item)
	        echo ',';
	        
	      echo "\n";
	      
	      // Now that the notifications are read make sure they are set read as well
	      ENotifySetRead($enot['id']);  
	  }
}
  echo '
                ]
      })';
}

?>