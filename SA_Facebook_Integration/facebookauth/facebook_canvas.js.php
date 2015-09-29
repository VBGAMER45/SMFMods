<?php 
  global $modSettings, $context;
 if(!empty($modSettings['fb_app_dtheme']) && !empty($modSettings['fb_app_atheme'])){
    $context['html_headers'] .= '
	<script language="JavaScript" type="text/javascript">
			
			function createCookie(name,value,days) {
                if (days) {
                  var date = new Date();
                  date.setTime(date.getTime()+(days*24*60*60*1000));
                  var expires = "; expires="+date.toGMTString();
                }
                else var expires = "";
                document.cookie = name+"="+value+expires+"; path=/";
            }

            function readCookie(name) {
                var nameEQ = name + "=";
                var ca = document.cookie.split(\';\');
                for(var i=0;i < ca.length;i++) {
                  var c = ca[i];
                  while (c.charAt(0)==\' \') c = c.substring(1,c.length);
                  if (c.indexOf(nameEQ) == 0) return c.substring(nameEQ.length,c.length);
                }
               return null;
            }

            function eraseCookie(name) {
               createCookie(name,"",-1);
            }
		
	</script>';
	$context['html_headers'] .= '
	<script type="text/javascript">
    if ( top === window )
    {
       if(readCookie(\'theme_changed\') !== \'\')
       {
	      '.($modSettings['fb_app_showalerts'] ? 'alert(\'switching back themes\')' : '').'
		  location.href=\'' . $scripturl . '?nocanvas\'
          createCookie(\'theme_changed\',\'\',7) 
       }
    }
    else{
     
	   if(readCookie(\'theme_changed\') !== \'done\')
       {
	     '.($modSettings['fb_app_showalerts'] ? 'alert(\'switching themes\')' : '').'
		 location.href=\'' . $scripturl . '?canvas\'
          createCookie(\'theme_changed\',\'done\',7) 
       }	
   }
   </script>';
	}
	
?>