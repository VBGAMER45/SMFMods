<?php
/*
Zapier for SMF
Version 1.0
by:vbgamer45
https://www.smfhacks.com

License Information:
Links to https://www.smfhacks.com must remain unless
branding free option is purchased.
*/

function template_zapier_settings()
{
	global $context, $txt, $scripturl, $boardurl, $modSettings, $settings;

	echo '
 <div class="cat_bar">
		<h3 class="catbg centertext">
        ', $txt['zapier_admin'] , '
        </h3>
  </div>
	<form method="post" name="frmsettings" id="frmsettings" action="', $scripturl, '?action=admin;area=zapier;sa=settings2" accept-charset="', $context['character_set'], '">
	<table border="0" cellpadding="0" cellspacing="0" width="100%">
    <tr>
	    <td width="50%" colspan="2"  align="center" class="windowbg"><img src="' . $settings['default_images_url'] . '/zapier-small-orange-logo.png" alt="Zapier">
	    <br />
	    ' . $txt['txt_zapier_info'] . '
	    </td>
	    </tr>
    <tr>
	    <td width="50%" colspan="2"  align="center" class="windowbg"><hr> <br><h1>' . $txt['zapier_txt_send_data_to_zapier'] . '</h1>
	    </td>
	    </tr>

    <tr>
	    <td colspan="2"  class="windowbg" align="center">' . $txt['zapier_txt_step1_login'] . ' </td>
	    </tr>
	    
    <tr>
	    <td colspan="2"  class="windowbg" align="center">' . $txt['zapier_txt_step2_app'] . ' </td>
	    </tr>
	    
    <tr>
	    <td width="50%" class="windowbg" align="right" valign="top">' . $txt['zapier_txt_step3_app'] . ' </td>
	     <td  class="windowbg"><select name="action1" id="action1" onchange="UpdateSendData()">
	    <option value="">' . $txt['zapier_txt_action_select_action'] . '</option>
	   <option value="getposts">' . $txt['zapier_txt_action_getposts'] . '</option>
        <option value="gettopics">' . $txt['zapier_txt_action_gettopics'] . '</option>
        <option value="getmembers">' . $txt['zapier_txt_action_getmembers'] . '</option>

	    
	    </select>
	   <div id="chooseboard1" style="display:none">
	   ' . $txt['zapier_txt_step3_app2'] . '<br>
<select name="boards1[]" id="boards1" multiple="multiple" size="5" onchange="UpdateSendData()">
<option value="0">' . $txt['zapier_txt_allboards'] . '</option>';

						foreach ($context['zapier_boards'] as $key => $option)
						{

							 echo '<option value="' . $key . '">' . $option . '</option>';


						}

					echo '</select>
	   
	   </div>
	    
	    </td>
	    </tr>
	    
	    

	    
    <tr>
	    <td class="windowbg" align="center" colspan="2">' . $txt['zapier_txt_step4_app'] . ' <input type="text" id="zapier_retrieve" size="100" value=""></td>
	    </tr>
	    
        <tr>
            <td width="50%" colspan="2"  align="center" class="windowbg"><hr> <br><h1>' . $txt['zapier_txt_create_data']  . '</h1>
            </td>
	    </tr>



    <tr>
	    <td colspan="2"  class="windowbg" align="center">' . $txt['zapier_txt_step1_login'] . ' </td>
	    </tr>
	    
    <tr>
	    <td colspan="2"  class="windowbg" align="center">' . $txt['zapier_txt_create_step2_app'] . ' </td>
	    </tr>

    <tr>
	    <td width="50%" class="windowbg" align="right" valign="top">' . $txt['zapier_txt_step3_app'] . ' </td>
	     <td  class="windowbg"><select name="action2" id="action2" onchange="UpdateCreateData()">
	    <option value="">' . $txt['zapier_txt_action_select_action'] . '</option>
	   <option value="createpost">' . $txt['zapier_txt_action_createpost'] . '</option>
        <option value="registermember">' . $txt['zapier_txt_action_registermember'] . '</option>

	    
	    </select></td>
	  </tr>
	  <tr>
	     <td colspan="2"  class="windowbg" align="center">
	     <table align="center">
	         <tr>
	            <td class="windowbg">
	   <div id="chooseboard2" style="display:none">' . $txt['zapier_txt_payload_topic'] .'
	   <!--
	   ' . $txt['zapier_txt_step3_app2'] . '<br>
<select name="boards2" id="boards2"  size="5" onchange="UpdateCreateData()">
';

						foreach ($context['zapier_boards'] as $key => $option)
						{

							 echo '<option value="' . $key . '">' . $option . '</option>';


						}

					echo '</select>-->
	   
	   </div>
	   <div id="choosemember" style="display:none">' . $txt['zapier_txt_payload_registermember'] .'</div>
	    
	    </td>
	        </tr>
	        
	    </table>
	         </td>
	    </tr>
	    
	
	    
    <tr>
	    <td class="windowbg" align="center" colspan="2">' . $txt['zapier_txt_step4_app'] . ' <input type="text" id="zapier_action" size="100" value=""></td>
	    </tr>
    <tr>
	    <td class="windowbg" align="center" colspan="2">&nbsp;</td>
	    </tr>
	  </table>
	  <input type="hidden" name="sc" value="', $context['session_id'], '" />
  	</form>

    <script language="JavaScript">
    
    function UpdateSendData()
    {
        var siteUrl = "' . $boardurl . '/zapierwebhook.php?hash=' . $modSettings['zapier_hash'] . '";
        
        var actionOption = document.getElementById("action1");
        
        var actionText = actionOption.options[actionOption.selectedIndex].value;
        
        if (actionText != "")
             siteUrl =  siteUrl  + "&action=" +   actionText;

        
        
             
        if (actionText == "getposts" || actionText == "gettopics")    
        {
           
             document.getElementById("chooseboard1").style.display = "block";
             
                 
             var boardList = "";    
             var allBoards = 0;
                 
            var boards1Option = document.getElementById("boards1");      
             
             
         for (var i=0, iLen= boards1Option.options.length; i<iLen; i++)
          {
            opt =  boards1Option.options[i];
        
            if (opt.selected) 
            {
              
              if (opt.value == 0)
                allBoards = 1;
              else
             {
                if (boardList != "")
                    boardList = boardList + ",";
                
                boardList = boardList + opt.value;
                
               }
                
        
      
            }
          }     
  
            if (allBoards == 1)
            {
                 boardList = "";
             }    
                 
              if (boardList != "")
                 {
                    siteUrl =  siteUrl  + "&showboard=" + boardList;
                 }    
             
        }    
        else
            document.getElementById("chooseboard1").style.display = "none";
        
        
        if (actionText == "")
            siteUrl = "";
  
        txtSiteUrl = document.getElementById("zapier_retrieve");
        txtSiteUrl.value = siteUrl;
        
        
    }
    
    
    function UpdateCreateData()
    {
        var siteUrl = "' . $boardurl . '/zapierwebhook.php?hash=' . $modSettings['zapier_hash'] . '";
    
    

        
        var actionOption = document.getElementById("action2");
        
        var actionText = actionOption.options[actionOption.selectedIndex].value;
        
        if (actionText != "")
             siteUrl =  siteUrl  + "&action=" +   actionText;
             
        if (actionText == "createpost")    
        {
           
             document.getElementById("chooseboard2").style.display = "block";
             
        }
        else
       {
            document.getElementById("chooseboard2").style.display = "none";
       }     
            
        if (actionText == "registermember")    
        {
           
             document.getElementById("choosemember").style.display = "block";
             
        }
        else
       {
            document.getElementById("choosemember").style.display = "none";
     
       }     
    
    
    
    
        txtSiteUrl = document.getElementById("zapier_action");
        txtSiteUrl.value = siteUrl;
    
    }
    
    
    
</script>

  ';

}




?>