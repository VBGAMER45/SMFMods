<?php
/* ***** BEGIN LICENSE BLOCK *****
 * Version: MPL 1.1
 *
 * The contents of this file are subject to the Mozilla Public License Version
 * 1.1 (the "License"); you may not use this file except in compliance with
 * the License. You may obtain a copy of the License at
 * http://www.mozilla.org/MPL/
 *
 * Software distributed under the License is distributed on an "AS IS" basis,
 * WITHOUT WARRANTY OF ANY KIND, either express or implied. See the License
 * for the specific language governing rights and limitations under the
 * License.
 *
 * The Original Code is http://www.sa-mods.info
 *
 * The Initial Developer of the Original Code is
 * wayne Mankertz.
 * Portions created by the Initial Developer are Copyright (C) 2011
 * the Initial Developer. All Rights Reserved.
 *
 * Contributor(s):
 *
 * ***** END LICENSE BLOCK ***** */
function template_main() {

}

function template_kbdolog()
{
	template_show_list('kb_list');
	
}

function template_kbimport()
{
	global $txt, $scripturl;
	echo'
	<div class="cat_bar">
		<h3 class="catbg">'.$txt['kb_import2'].'</h3>
	</div>
		
	<span class="upperframe"><span></span></span>	    
		<div class="roundframe centertext">
		   
		    <a href="'.$scripturl.'?action=admin;area=kb;sa=importsmfa">'.$txt['kb_import3'].'</a>
		    <hr /><a href="'.$scripturl.'?action=admin;area=kb;sa=importtpa">'.$txt['kb_import4'].'</a>
		    <hr /><a href="'.$scripturl.'?action=admin;area=kb;sa=importfaq">'.$txt['kb_impfaq'].'</a>
		   
	    </div>		
    <span class="lowerframe"><span></span></span>';
	
}
function template_kbimportfaq()
{
	global $txt, $context, $scripturl;
	
	echo '
    <form method="post" action="' , $scripturl , '?action=admin;area=kb;sa=importfaq;doimport">
        
		<div class="cat_bar">
		   <h3 class="catbg">'.$txt['kb_impfaq'].'</h3>
		</div>
		
	    <span class="upperframe"><span></span></span>
		    <div class="roundframe centertext">
        
		    '.$txt['kb_import5'].'<br /><br />
		
		    <select name="catid">';
		  
		      foreach ($context['kb_cat'] as $row)
  			    echo '<option value="' , $row['kbid'] , '">' , $row['name'] , '</option>';

            echo '
		    </select><br /><br />
		
           <input type="submit" value="'.$txt['kb_import2'].'" name="submit" />
	       <input type="hidden" id="', $context['session_var'], '" name="', $context['session_var'], '" value="', $context['session_id'], '" /><br /><br />
        
		   ',$context['import_results'],'
		
        </div>
    <span class="lowerframe"><span></span></span>
</form>';	
}

function template_kbimporttp()
{
	global $txt, $context, $scripturl;
	
	echo '
    <form method="post" action="' , $scripturl , '?action=admin;area=kb;sa=importtpa;doimport">
    
	<div class="cat_bar">
		<h3 class="catbg">'.$txt['kb_importtp'].'</h3>
	</div>
	
	<span class="upperframe"><span></span></span>
		<div class="roundframe centertext">
		
    '.$txt['kb_import5'].'<br /><br /><select name="catid">';
		foreach ($context['kb_cat'] as $row)
  			echo '<option value="' , $row['kbid'] , '">' , $row['name'] , '</option>';

    echo '
	</select><br /><br />
	
            <input type="submit" value="'.$txt['kb_import2'].'" name="submit" />
	        <input type="hidden" id="', $context['session_var'], '" name="', $context['session_var'], '" value="', $context['session_id'], '" /><br /><br />
            
			',$context['import_results'],'
			
        </div>
    <span class="lowerframe"><span></span></span>
</form>';	
}

function template_kbimportasmfa()
{
	global $txt, $context, $scripturl;
	
	echo '
    <form method="post" action="' , $scripturl , '?action=admin;area=kb;sa=importsmfa;doimport">
    
	<div class="cat_bar">
		<h3 class="catbg">'.$txt['kb_import3'] .'</h3>
	</div>
	
	<span class="upperframe"><span></span></span>
		<div class="roundframe centertext">
		
    '.$txt['kb_import5'].'<br /><br /><select name="catid">';
		foreach ($context['kb_cat'] as $row)
  		    echo '<option value="' , $row['kbid'] , '">' , $row['name'] , '</option>';

    echo '
	</select><br /><br />
	
           <input type="submit" value="'.$txt['kb_import2'].'" name="submit" />
	       <input type="hidden" id="', $context['session_var'], '" name="', $context['session_var'], '" value="', $context['session_id'], '" /><br /><br />
          
		  ',$context['import_results'],'
		  
        </div>
    <span class="lowerframe"><span></span></span>
</form>';	
}
?>