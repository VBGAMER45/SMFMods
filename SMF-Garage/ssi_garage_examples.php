<?php
/**********************************************************************************
* ssi_garage_examples.php                                                         *
***********************************************************************************
* SMF Garage: Simple Machines Forum Garage (MOD)                                  *
* =============================================================================== *
* Software Version:           SMF Garage 3.0.0                                    *
* Install for:                2.0.9-2.0.99, 2.1.0-2.1.99                         *
* Original Developer:         RRasco (http://www.smfgarage.com)                   *
* Copyright 2026 by:          vbgamer45 (https://www.smfhacks.com)               *
* Copyright 2015 by:          Bruno Alves (margarett.pt@gmail.com                 *
* Copyright 2007-2011 by:     SMF Garage (http://www.smfgarage.com)               *
*                             RRasco (rrasco@smfgarage.com)                       *
* phpBB Garage by:            Esmond Poynton (esmond.poynton@gmail.com)           *
***********************************************************************************
* See the "SMF_Garage_License.txt" file for details.                              *
*              http://www.opensource.org/licenses/BSD-3-Clause                    *
**********************************************************************************/

$ssi_guest_access = false;

// Include the SSI file.
require(dirname(__FILE__) . '/SSI_Garage.php');

// Viewing the homepage sample?
if (isset($_GET['view']) && $_GET['view'] == 'home1')
{
    template_homepage_sample1('output');
    exit;
}

// Load the main template.
template_ssi_above();
?>

            <h2>SMF SSI_Garage.php Functions</h2>
            <p><strong>Current Version:</strong> 2.2</p>
            <p>This file is used to demonstrate the capabilities of SSI_Garage.php using PHP include functions.</p>
            <p>The examples show the include tag, then the results of it. Examples are separated by horizontal rules.</p>
            
            <h2>Include Code</h2>
            <p>To use SSI_Garage.php, add the following code to the very top of your page before the &lt;html&gt; tag on line 1:</p>
            <div class="codeheader">Code: <a href="javascript:void(0);" onclick="return smfSelectText(this);" class="codeoperation">[Select]</a></div><code class="bbc_code">&lt;?php require(&quot;<?php echo addslashes($user_info['is_admin'] ? realpath($boarddir . '/SSI_Garage.php') : 'SSI_Garage.php'); ?>&quot;); ?&gt;</code>

            <h2>Some notes on usage</h2>
            <p>All the functions have an output method parameter.  This can either be &quot;echo&quot; (the default) or &quot;array&quot;</p>
            <p>If it is &quot;echo&quot;, the function will act normally - otherwise, it will return an array containing information about the requested task. For example, it might return a list of vehicles for ssi_smfg_newestVehicles.</p>            
            <p>The functions displayed as code below were created to allow you to utilize shadowbox and the theme styles associated with your forum.  They will not return any visible output, only includes so you may put them in  your &lt;head&gt; tags.  In order for shadowbox to work with SSI_Garage.php you <b><u>must</u></b> inlcude at least the javascript function.  The CSS includes are required to utilize the SMF table style.</p>
            
            <h2>Additional Guides &amp; FAQ</h2>

            <div id="sidenav" class="windowbg">
                <span class="topslice"><span></span></span>
                <div class="content">
                    <h2 id="functionlist">Function List</h2>
                    <h3>Includes</h3>
                    <ul>
                        <li><a href="#" onclick="showSSIBlock('ssi_smfg_includes'); return false;">All Includes</a></li>
                        <li><a href="#" onclick="showSSIBlock('ssi_smfg_css_includes'); return false;">CSS Includes</a></li>
                        <li><a href="#" onclick="showSSIBlock('ssi_smfg_js_includes'); return false;">JS Includes</a></li>
                    </ul>
                    <h3>Blocks</h3>
                    <ul>
                        <li><a href="#" onclick="showSSIBlock('ssi_smfg_featuredVehicle'); return false;">Featured Vehicle</a></li>
                        <li><a href="#" onclick="showSSIBlock('ssi_smfg_garageStats'); return false;">Garage Stats</a></li>
                        <li><a href="#" onclick="showSSIBlock('ssi_smfg_newestVehicles'); return false;">Newest Vehicles</a></li>
                        <li><a href="#" onclick="showSSIBlock('ssi_smfg_lastUpdatedVehicles'); return false;">Updated Vehicles</a></li>
                        <li><a href="#" onclick="showSSIBlock('ssi_smfg_newestMods'); return false;">Newest Mods</a></li>
                        <li><a href="#" onclick="showSSIBlock('ssi_smfg_lastUpdatedMods'); return false;">Updated Mods</a></li>
                        <li><a href="#" onclick="showSSIBlock('ssi_smfg_mostViews'); return false;">Most Views</a></li>
                        <li><a href="#" onclick="showSSIBlock('ssi_smfg_mostModified'); return false;">Most Modified</a></li>
                        <li><a href="#" onclick="showSSIBlock('ssi_smfg_mostSpent'); return false;">Most Spent</a></li>
                        <li><a href="#" onclick="showSSIBlock('ssi_smfg_topQmile'); return false;">Top Quartermiles</a></li>
                        <li><a href="#" onclick="showSSIBlock('ssi_smfg_topDyno'); return false;">Top Dynoruns</a></li>
                        <li><a href="#" onclick="showSSIBlock('ssi_smfg_topLap'); return false;">Top Laps</a></li>
                        <li><a href="#" onclick="showSSIBlock('ssi_smfg_topRated'); return false;">Top Rated</a></li>
                        <li><a href="#" onclick="showSSIBlock('ssi_smfg_lastBlog'); return false;">Recent Blogs</a></li>
                        <li><a href="#" onclick="showSSIBlock('ssi_smfg_lastService'); return false;">Recent Services</a></li>
                        <li><a href="#" onclick="showSSIBlock('ssi_smfg_lastVideo'); return false;">Recent Videos</a></li>
                        <li><a href="#" onclick="showSSIBlock('ssi_smfg_lastComment'); return false;">Recent Comments</a></li>
                    </ul>
                    <h2 id="other">Other</h2>
                    <ul>
                        <li><a href="#" onclick="toggleVisibleByClass('ssi_preview', false); return false;">Show all examples</a></li>
                        <li><a href="#" onclick="toggleVisibleByClass('ssi_preview', true); return false;">Hide all examples</a></li>
                    </ul>
                </div>
                <span class="botslice"><span></span></span>
            </div>

    <div id="preview" class="windowbg2">
        <span class="topslice"><span></span></span>
        <div class="content">

<!-- INCLUDES ITEMS -->
            <div class="ssi_preview" id="ssi_smfg_includes">
                <h2>JavaScript and CSS Includes</h2>
                <h3>Usage</h3>
                <div class="ssi_result">ssi_smfg_includes([<i>str</i> $output_method])</div>
                <h3>Options</h3>
                <div style='margin-left: 10px;'>
                    <b>$output_method</b>&nbsp;-&nbsp;<i>default</i>:&nbsp;'echo'&nbsp;-&nbsp;Will echo output.
                    <br />
                    <b>$output_method</b>&nbsp;-&nbsp;<i>other available</i>:&nbsp;'return'&nbsp;-&nbsp;Will return output.
                </div>                
                <h3>Code</h3>
                <div class="codeheader">Code: <a href="javascript:void(0);" onclick="return smfSelectText(this);" class="codeoperation">[Select]</a></div><code class="bbc_code">&lt;?php ssi_smfg_includes(); ?&gt;</code>
                <h3>Result</h3>
                <div class="ssi_result"><pre><?php $ssi_includes = ssi_smfg_includes('return');
                echo str_replace(array('<','>'),array('&lt;','&gt;'),$ssi_includes); flush(); ?></pre></div>
            </div>

            <div class="ssi_preview" id="ssi_smfg_css_includes">
                <h2>CSS Includes</h2>
                <h3>Usage</h3>
                <div class="ssi_result">ssi_smfg_css_includes([<i>str</i> $output_method])</div>
                <h3>Options</h3>
                    <div style='margin-left: 10px;'>
                        <b>$output_method</b>&nbsp;-&nbsp;<i>default</i>:&nbsp;'echo'&nbsp;-&nbsp;Will echo output.
                        <br />
                        <b>$output_method</b>&nbsp;-&nbsp;<i>other available</i>:&nbsp;'return'&nbsp;-&nbsp;Will return output.
                    </div>
                <h3>Code</h3>
                <div class="codeheader">Code: <a href="javascript:void(0);" onclick="return smfSelectText(this);" class="codeoperation">[Select]</a></div><code class="bbc_code">&lt;?php ssi_smfg_css_includes(); ?&gt;</code>
                <h3>Result</h3>
                <div class="ssi_result"><pre><?php $ssi_includes = ssi_smfg_css_includes('return');
                echo str_replace(array('<','>'),array('&lt;','&gt;'),$ssi_includes); flush(); ?></pre></div>
            </div>

            <div class="ssi_preview" id="ssi_smfg_js_includes">
                <h2>JavaScript Includes</h2>
                <h3>Usage</h3>
                <div class="ssi_result">ssi_smfg_js_includes([<i>str</i> $output_method])</div>
                <h3>Options</h3>
                    <div style='margin-left: 10px;'>
                        <b>$output_method</b>&nbsp;-&nbsp;<i>default</i>:&nbsp;'echo'&nbsp;-&nbsp;Will echo output.
                        <br />
                        <b>$output_method</b>&nbsp;-&nbsp;<i>other available</i>:&nbsp;'return'&nbsp;-&nbsp;Will return output.
                    </div>
                <h3>Code</h3>
                <div class="codeheader">Code: <a href="javascript:void(0);" onclick="return smfSelectText(this);" class="codeoperation">[Select]</a></div><code class="bbc_code">&lt;?php ssi_smfg_js_includes(); ?&gt;</code>
                <h3>Result</h3>
                <div class="ssi_result"><pre><?php $ssi_includes = ssi_smfg_js_includes('return');
                echo str_replace(array('<','>'),array('&lt;','&gt;'),$ssi_includes); flush(); ?></pre></div>
            </div>

<!-- BLOCK ITEMS -->
            <div class="ssi_preview" id="ssi_smfg_featuredVehicle">
                <h2>Featured Vehicle</h2>
                <h3>Usage</h3>
                <div class="ssi_result">ssi_smfg_featuredVehicle([<i>int</i> $width [, <i>bool</i> $description [, <i>str</i> $output_method]]])</div>
                <h3>Options</h3>
                    <div style='margin-left: 10px;'>
                        <b>$width</b>&nbsp;-&nbsp;<i>default</i>:&nbsp;'200'
                        <br />
                        <b>$description</b>&nbsp;-&nbsp;<i>default</i>:&nbsp;'1'&nbsp;-&nbsp;Disables feature vehicle description. (Description is now block title - cannot disable, but option remains as parameter)
                        <br />
                        <b>$output_method</b>&nbsp;-&nbsp;<i>default</i>:&nbsp;'echo'&nbsp;-&nbsp;Will echo output.
                        <br />
                        <b>$output_method</b>&nbsp;-&nbsp;<i>other available</i>:&nbsp;'array'&nbsp;-&nbsp;Will return following array:
                        <div class="codeheader">Code: <a href="javascript:void(0);" onclick="return smfSelectText(this);" class="codeoperation">[Select]</a></div><code class="bbc_code"><pre><?php $featuredVehicle = ssi_smfg_featuredVehicle(200, 1, 'array'); flush(); print_R(str_replace(array('<','>'),array('&lt;','&gt;'),$featuredVehicle));?></pre></code>
                    </div>
                <h3>Code</h3>
                <div class="codeheader">Code: <a href="javascript:void(0);" onclick="return smfSelectText(this);" class="codeoperation">[Select]</a></div><code class="bbc_code">&lt;?php ssi_smfg_featuredVehicle(); ?&gt;</code>
                <h3>Result</h3>
                <div class="ssi_result"><?php ssi_smfg_featuredVehicle(); flush(); ?></div>
            </div>
            
            <div class="ssi_preview" id="ssi_smfg_garageStats">
                <h2>Garage Stats</h2>
                <h3>Usage</h3>
                <div class="ssi_result">ssi_smfg_garageStats([<i>int</i> $style [, <i>str</i> $output_method]])</div>
                <h3>Options</h3>
                <div style='margin-left: 10px;'>
                    <b>$style</b>&nbsp;-&nbsp;<i>default</i>:&nbsp;'0'&nbsp;-&nbsp;Table style.
                    <br />
                    <b>$style</b>&nbsp;-&nbsp;<i>other available</i>:&nbsp;'1'&nbsp;-&nbsp;TinyPortal style.
                    <br />
                    <b>$output_method</b>&nbsp;-&nbsp;<i>default</i>:&nbsp;'echo'&nbsp;-&nbsp;Will echo output.
                    <br />
                    <b>$output_method</b>&nbsp;-&nbsp;<i>other available</i>:&nbsp;'array'&nbsp;-&nbsp;Will return following array:
                    <div class="codeheader">Code: <a href="javascript:void(0);" onclick="return smfSelectText(this);" class="codeoperation">[Select]</a></div><code class="bbc_code"><pre><?php $garageStats = ssi_smfg_garageStats(0, 'array'); flush(); print_R($garageStats);?></pre></code>
                </div>
                <h3>Code</h3>
                <div class="codeheader">Code: <a href="javascript:void(0);" onclick="return smfSelectText(this);" class="codeoperation">[Select]</a></div><code class="bbc_code">&lt;?php ssi_smfg_garageStats(); ?&gt;</code>
                <h3>Result</h3>
                <div class="ssi_result"><?php ssi_smfg_garageStats(); flush(); ?></div>
            </div>
            
            <div class="ssi_preview" id="ssi_smfg_newestVehicles">
                <h2>Newest Vehicles</h2>
                <h3>Usage</h3>
                <div class="ssi_result">ssi_smfg_newestVehicles([<i>bool</i> $title [, <i>int</i> $limit [, <i>str</i> $output_method]]])</div>
                <h3>Options</h3>
                <div style='margin-left: 10px;'>
                    <b>$title</b>&nbsp;-&nbsp;<i>default</i>:&nbsp;'1'&nbsp;-&nbsp;Disables block title.
                    <br />
                    <b>$limit</b>&nbsp;-&nbsp;<i>default</i>:&nbsp;'5'&nbsp;-&nbsp;Number of items to return.
                    <br />
                    <b>$output_method</b>&nbsp;-&nbsp;<i>default</i>:&nbsp;'echo'&nbsp;-&nbsp;Will echo output.
                    <br />
                    <b>$output_method</b>&nbsp;-&nbsp;<i>other available</i>:&nbsp;'array'&nbsp;-&nbsp;Will return following array (<b>$limit</b> set to '1'):
                    <div class="codeheader">Code: <a href="javascript:void(0);" onclick="return smfSelectText(this);" class="codeoperation">[Select]</a></div><code class="bbc_code"><pre><?php $newestVehicles = ssi_smfg_newestVehicles(1, 1, 'array'); flush(); print_R($newestVehicles);?></pre></code>
                </div>
                <h3>Code</h3>
                <div class="codeheader">Code: <a href="javascript:void(0);" onclick="return smfSelectText(this);" class="codeoperation">[Select]</a></div><code class="bbc_code">&lt;?php ssi_smfg_newestVehicles(); ?&gt;</code>
                <h3>Result</h3>
                <div class="ssi_result"><?php ssi_smfg_newestVehicles(); flush(); ?></div>
            </div>
            
            <div class="ssi_preview" id="ssi_smfg_lastUpdatedVehicles">
                <h2>Updated Vehicles</h2>
                <h3>Usage</h3>
                <div class="ssi_result">ssi_smfg_lastUpdatedVehicles([<i>bool</i> $title [, <i>int</i> $limit [, <i>str</i> $output_method]]])</div>
                <h3>Options</h3>
                <div style='margin-left: 10px;'>
                    <b>$title</b>&nbsp;-&nbsp;<i>default</i>:&nbsp;'1'&nbsp;-&nbsp;Disables block title.
                    <br />
                    <b>$limit</b>&nbsp;-&nbsp;<i>default</i>:&nbsp;'5'&nbsp;-&nbsp;Number of items to return.
                    <br />
                    <b>$output_method</b>&nbsp;-&nbsp;<i>default</i>:&nbsp;'echo'&nbsp;-&nbsp;Will echo output.
                    <br />
                    <b>$output_method</b>&nbsp;-&nbsp;<i>other available</i>:&nbsp;'array'&nbsp;-&nbsp;Will return following array (<b>$limit</b> set to '1'):
                    <div class="codeheader">Code: <a href="javascript:void(0);" onclick="return smfSelectText(this);" class="codeoperation">[Select]</a></div><code class="bbc_code"><pre><?php $updatedVehicles = ssi_smfg_lastUpdatedVehicles(1, 1, 'array'); flush(); print_R($updatedVehicles);?></pre></code>
                </div>
                <h3>Code</h3>
                <div class="codeheader">Code: <a href="javascript:void(0);" onclick="return smfSelectText(this);" class="codeoperation">[Select]</a></div><code class="bbc_code">&lt;?php ssi_smfg_lastUpdatedVehicles(); ?&gt;</code>
                <h3>Result</h3>
                <div class="ssi_result"><?php ssi_smfg_lastUpdatedVehicles(); flush(); ?></div>
            </div>
            
            <div class="ssi_preview" id="ssi_smfg_newestMods">
                <h2>Newest Mods</h2>
                <h3>Usage</h3>
                <div class="ssi_result">ssi_smfg_newestMods([<i>bool</i> $title [, <i>int</i> $limit [, <i>str</i> $output_method]]])</div>
                <h3>Options</h3>
                <div style='margin-left: 10px;'>
                    <b>$title</b>&nbsp;-&nbsp;<i>default</i>:&nbsp;'1'&nbsp;-&nbsp;Disables block title.
                    <br />
                    <b>$limit</b>&nbsp;-&nbsp;<i>default</i>:&nbsp;'5'&nbsp;-&nbsp;Number of items to return.
                    <br />
                    <b>$output_method</b>&nbsp;-&nbsp;<i>default</i>:&nbsp;'echo'&nbsp;-&nbsp;Will echo output.
                    <br />
                    <b>$output_method</b>&nbsp;-&nbsp;<i>other available</i>:&nbsp;'array'&nbsp;-&nbsp;Will return following array (<b>$limit</b> set to '1'):
                    <div class="codeheader">Code: <a href="javascript:void(0);" onclick="return smfSelectText(this);" class="codeoperation">[Select]</a></div><code class="bbc_code"><pre><?php $newestMods = ssi_smfg_newestMods(1, 1, 'array'); flush(); print_R($newestMods);?></pre></code>
                </div>
                <h3>Code</h3>
                <div class="codeheader">Code: <a href="javascript:void(0);" onclick="return smfSelectText(this);" class="codeoperation">[Select]</a></div><code class="bbc_code">&lt;?php ssi_smfg_newestMods(); ?&gt;</code>
                <h3>Result</h3>
                <div class="ssi_result"><?php ssi_smfg_newestMods(); flush(); ?></div>
            </div>
            
            <div class="ssi_preview" id="ssi_smfg_lastUpdatedMods">
                <h2>Updated Mods</h2>
                <h3>Usage</h3>
                <div class="ssi_result">ssi_smfg_lastUpdatedMods([<i>bool</i> $title [, <i>int</i> $limit [, <i>str</i> $output_method]]])</div>
                <h3>Options</h3>
                <div style='margin-left: 10px;'>
                    <b>$title</b>&nbsp;-&nbsp;<i>default</i>:&nbsp;'1'&nbsp;-&nbsp;Disables block title.
                    <br />
                    <b>$limit</b>&nbsp;-&nbsp;<i>default</i>:&nbsp;'5'&nbsp;-&nbsp;Number of items to return.
                    <br />
                    <b>$output_method</b>&nbsp;-&nbsp;<i>default</i>:&nbsp;'echo'&nbsp;-&nbsp;Will echo output.
                    <br />
                    <b>$output_method</b>&nbsp;-&nbsp;<i>other available</i>:&nbsp;'array'&nbsp;-&nbsp;Will return following array (<b>$limit</b> set to '1'):
                    <div class="codeheader">Code: <a href="javascript:void(0);" onclick="return smfSelectText(this);" class="codeoperation">[Select]</a></div><code class="bbc_code"><pre><?php $updatedMods = ssi_smfg_lastUpdatedMods(1, 1, 'array'); flush(); print_R($updatedMods);?></pre></code>
                </div>
                <h3>Code</h3>
                <div class="codeheader">Code: <a href="javascript:void(0);" onclick="return smfSelectText(this);" class="codeoperation">[Select]</a></div><code class="bbc_code">&lt;?php ssi_smfg_lastUpdatedMods(); ?&gt;</code>
                <h3>Result</h3>
                <div class="ssi_result"><?php ssi_smfg_lastUpdatedMods(); flush(); ?></div>
            </div>
            
            <div class="ssi_preview" id="ssi_smfg_mostViews">
                <h2>Most Views</h2>
                <h3>Usage</h3>
                <div class="ssi_result">ssi_smfg_mostViews([<i>bool</i> $title [, <i>int</i> $limit [, <i>str</i> $output_method]]])</div>
                <h3>Options</h3>
                <div style='margin-left: 10px;'>
                    <b>$title</b>&nbsp;-&nbsp;<i>default</i>:&nbsp;'1'&nbsp;-&nbsp;Disables block title.
                    <br />
                    <b>$limit</b>&nbsp;-&nbsp;<i>default</i>:&nbsp;'5'&nbsp;-&nbsp;Number of items to return.
                    <br />
                    <b>$output_method</b>&nbsp;-&nbsp;<i>default</i>:&nbsp;'echo'&nbsp;-&nbsp;Will echo output.
                    <br />
                    <b>$output_method</b>&nbsp;-&nbsp;<i>other available</i>:&nbsp;'array'&nbsp;-&nbsp;Will return following array (<b>$limit</b> set to '1'):
                    <div class="codeheader">Code: <a href="javascript:void(0);" onclick="return smfSelectText(this);" class="codeoperation">[Select]</a></div><code class="bbc_code"><pre><?php $mostViews = ssi_smfg_mostViews(1, 1, 'array'); flush(); print_R($mostViews);?></pre></code>
                </div>
                <h3>Code</h3>
                <div class="codeheader">Code: <a href="javascript:void(0);" onclick="return smfSelectText(this);" class="codeoperation">[Select]</a></div><code class="bbc_code">&lt;?php ssi_smfg_mostViews(); ?&gt;</code>
                <h3>Result</h3>
                <div class="ssi_result"><?php ssi_smfg_mostViews(); flush(); ?></div>
            </div>
            
            <div class="ssi_preview" id="ssi_smfg_mostModified">
                <h2>Most Modified</h2>
                <h3>Usage</h3>
                <div class="ssi_result">ssi_smfg_mostModified([<i>bool</i> $title [, <i>int</i> $limit [, <i>str</i> $output_method]]])</div>
                <h3>Options</h3>
                <div style='margin-left: 10px;'>
                    <b>$title</b>&nbsp;-&nbsp;<i>default</i>:&nbsp;'1'&nbsp;-&nbsp;Disables block title.
                    <br />
                    <b>$limit</b>&nbsp;-&nbsp;<i>default</i>:&nbsp;'5'&nbsp;-&nbsp;Number of items to return.
                    <br />
                    <b>$output_method</b>&nbsp;-&nbsp;<i>default</i>:&nbsp;'echo'&nbsp;-&nbsp;Will echo output.
                    <br />
                    <b>$output_method</b>&nbsp;-&nbsp;<i>other available</i>:&nbsp;'array'&nbsp;-&nbsp;Will return following array (<b>$limit</b> set to '1'):
                    <div class="codeheader">Code: <a href="javascript:void(0);" onclick="return smfSelectText(this);" class="codeoperation">[Select]</a></div><code class="bbc_code"><pre><?php $mostModified = ssi_smfg_mostModified(1, 1, 'array'); flush(); print_R($mostModified);?></pre></code>
                </div>
                <h3>Code</h3>
                <div class="codeheader">Code: <a href="javascript:void(0);" onclick="return smfSelectText(this);" class="codeoperation">[Select]</a></div><code class="bbc_code">&lt;?php ssi_smfg_mostModified(); ?&gt;</code>
                <h3>Result</h3>
                <div class="ssi_result"><?php ssi_smfg_mostModified(); flush(); ?></div>
            </div>
            
            <div class="ssi_preview" id="ssi_smfg_mostSpent">
                <h2>Most Spent</h2>
                <h3>Usage</h3>
                <div class="ssi_result">ssi_smfg_mostSpent([<i>bool</i> $title [, <i>int</i> $limit [, <i>str</i> $output_method]]])</div>
                <h3>Options</h3>
                <div style='margin-left: 10px;'>
                    <b>$title</b>&nbsp;-&nbsp;<i>default</i>:&nbsp;'1'&nbsp;-&nbsp;Disables block title.
                    <br />
                    <b>$limit</b>&nbsp;-&nbsp;<i>default</i>:&nbsp;'5'&nbsp;-&nbsp;Number of items to return.
                    <br />
                    <b>$output_method</b>&nbsp;-&nbsp;<i>default</i>:&nbsp;'echo'&nbsp;-&nbsp;Will echo output.
                    <br />
                    <b>$output_method</b>&nbsp;-&nbsp;<i>other available</i>:&nbsp;'array'&nbsp;-&nbsp;Will return following array (<b>$limit</b> set to '1'):
                    <div class="codeheader">Code: <a href="javascript:void(0);" onclick="return smfSelectText(this);" class="codeoperation">[Select]</a></div><code class="bbc_code"><pre><?php $mostSpent = ssi_smfg_mostSpent(1, 1, 'array'); flush(); print_R($mostSpent);?></pre></code>
                </div>
                <h3>Code</h3>
                <div class="codeheader">Code: <a href="javascript:void(0);" onclick="return smfSelectText(this);" class="codeoperation">[Select]</a></div><code class="bbc_code">&lt;?php ssi_smfg_mostSpent(); ?&gt;</code>
                <h3>Result</h3>
                <div class="ssi_result"><?php ssi_smfg_mostSpent(); flush(); ?></div>
            </div>
            
            <div class="ssi_preview" id="ssi_smfg_topQmile">
                <h2>Top Quartermiles</h2>
                <h3>Usage</h3>
                <div class="ssi_result">ssi_smfg_topQmile([<i>bool</i> $title [, <i>int</i> $limit [, <i>str</i> $output_method]]])</div>
                <h3>Options</h3>
                <div style='margin-left: 10px;'>
                    <b>$title</b>&nbsp;-&nbsp;<i>default</i>:&nbsp;'1'&nbsp;-&nbsp;Disables block title.
                    <br />
                    <b>$limit</b>&nbsp;-&nbsp;<i>default</i>:&nbsp;'5'&nbsp;-&nbsp;Number of items to return.
                    <br />
                    <b>$output_method</b>&nbsp;-&nbsp;<i>default</i>:&nbsp;'echo'&nbsp;-&nbsp;Will echo output.
                    <br />
                    <b>$output_method</b>&nbsp;-&nbsp;<i>other available</i>:&nbsp;'array'&nbsp;-&nbsp;Will return following array (<b>$limit</b> set to '1'):
                    <div class="codeheader">Code: <a href="javascript:void(0);" onclick="return smfSelectText(this);" class="codeoperation">[Select]</a></div><code class="bbc_code"><pre><?php $topQmile = ssi_smfg_topQmile(1, 1, 'array'); flush(); print_R($topQmile);?></pre></code>
                </div>
                <h3>Code</h3>
                <div class="codeheader">Code: <a href="javascript:void(0);" onclick="return smfSelectText(this);" class="codeoperation">[Select]</a></div><code class="bbc_code">&lt;?php ssi_smfg_topQmile(); ?&gt;</code>
                <h3>Result</h3>
                <div class="ssi_result"><?php ssi_smfg_topQmile(); flush(); ?></div>
            </div>
            
            <div class="ssi_preview" id="ssi_smfg_topDyno">
                <h2>Top Dynoruns</h2>
                <h3>Usage</h3>
                <div class="ssi_result">ssi_smfg_topDyno([<i>bool</i> $title [, <i>int</i> $limit [, <i>str</i> $output_method]]])</div>
                <h3>Options</h3>
                <div style='margin-left: 10px;'>
                    <b>$title</b>&nbsp;-&nbsp;<i>default</i>:&nbsp;'1'&nbsp;-&nbsp;Disables block title.
                    <br />
                    <b>$limit</b>&nbsp;-&nbsp;<i>default</i>:&nbsp;'5'&nbsp;-&nbsp;Number of items to return.
                    <br />
                    <b>$output_method</b>&nbsp;-&nbsp;<i>default</i>:&nbsp;'echo'&nbsp;-&nbsp;Will echo output.
                    <br />
                    <b>$output_method</b>&nbsp;-&nbsp;<i>other available</i>:&nbsp;'array'&nbsp;-&nbsp;Will return following array (<b>$limit</b> set to '1'):
                    <div class="codeheader">Code: <a href="javascript:void(0);" onclick="return smfSelectText(this);" class="codeoperation">[Select]</a></div><code class="bbc_code"><pre><?php $topDyno = ssi_smfg_topDyno(1, 1, 'array'); flush(); print_R($topDyno);?></pre></code>
                </div>
                <h3>Code</h3>
                <div class="codeheader">Code: <a href="javascript:void(0);" onclick="return smfSelectText(this);" class="codeoperation">[Select]</a></div><code class="bbc_code">&lt;?php ssi_smfg_topDyno(); ?&gt;</code>
                <h3>Result</h3>
                <div class="ssi_result"><?php ssi_smfg_topDyno(); flush(); ?></div>
            </div>
            
            <div class="ssi_preview" id="ssi_smfg_topLap">
                <h2>Top Laptimes</h2>
                <h3>Usage</h3>
                <div class="ssi_result">ssi_smfg_topLap([<i>bool</i> $title [, <i>int</i> $limit [, <i>str</i> $output_method]]])</div>
                <h3>Options</h3>
                <div style='margin-left: 10px;'>
                    <b>$title</b>&nbsp;-&nbsp;<i>default</i>:&nbsp;'1'&nbsp;-&nbsp;Disables block title.
                    <br />
                    <b>$limit</b>&nbsp;-&nbsp;<i>default</i>:&nbsp;'5'&nbsp;-&nbsp;Number of items to return.
                    <br />
                    <b>$output_method</b>&nbsp;-&nbsp;<i>default</i>:&nbsp;'echo'&nbsp;-&nbsp;Will echo output.
                    <br />
                    <b>$output_method</b>&nbsp;-&nbsp;<i>other available</i>:&nbsp;'array'&nbsp;-&nbsp;Will return following array (<b>$limit</b> set to '1'):
                    <div class="codeheader">Code: <a href="javascript:void(0);" onclick="return smfSelectText(this);" class="codeoperation">[Select]</a></div><code class="bbc_code"><pre><?php $topLap = ssi_smfg_topLap(1, 1, 'array'); flush(); print_R($topLap);?></pre></code>
                </div>
                <h3>Code</h3>
                <div class="codeheader">Code: <a href="javascript:void(0);" onclick="return smfSelectText(this);" class="codeoperation">[Select]</a></div><code class="bbc_code">&lt;?php ssi_smfg_topLap(); ?&gt;</code>
                <h3>Result</h3>
                <div class="ssi_result"><?php ssi_smfg_topLap(); flush(); ?></div>
            </div>
            
            <div class="ssi_preview" id="ssi_smfg_topRated">
                <h2>Top Rated</h2>
                <h3>Usage</h3>
                <div class="ssi_result">ssi_smfg_topRated([<i>bool</i> $title [, <i>int</i> $limit [, <i>str</i> $output_method]]])</div>
                <h3>Options</h3>
                <div style='margin-left: 10px;'>
                    <b>$title</b>&nbsp;-&nbsp;<i>default</i>:&nbsp;'1'&nbsp;-&nbsp;Disables block title.
                    <br />
                    <b>$limit</b>&nbsp;-&nbsp;<i>default</i>:&nbsp;'5'&nbsp;-&nbsp;Number of items to return.
                    <br />
                    <b>$output_method</b>&nbsp;-&nbsp;<i>default</i>:&nbsp;'echo'&nbsp;-&nbsp;Will echo output.
                    <br />
                    <b>$output_method</b>&nbsp;-&nbsp;<i>other available</i>:&nbsp;'array'&nbsp;-&nbsp;Will return following array (<b>$limit</b> set to '1'):
                    <div class="codeheader">Code: <a href="javascript:void(0);" onclick="return smfSelectText(this);" class="codeoperation">[Select]</a></div><code class="bbc_code"><pre><?php $topRated = ssi_smfg_topRated(1, 1, 'array'); flush(); print_R($topRated);?></pre></code>
                </div>
                <h3>Code</h3>
                <div class="codeheader">Code: <a href="javascript:void(0);" onclick="return smfSelectText(this);" class="codeoperation">[Select]</a></div><code class="bbc_code">&lt;?php ssi_smfg_topRated(); ?&gt;</code>
                <h3>Result</h3>
                <div class="ssi_result"><?php ssi_smfg_topRated(); flush(); ?></div>
            </div>
            
            <div class="ssi_preview" id="ssi_smfg_lastBlog">
                <h2>Recent Blogs</h2>
                <h3>Usage</h3>
                <div class="ssi_result">ssi_smfg_lastBlog([<i>bool</i> $title [, <i>int</i> $limit [, <i>str</i> $output_method]]])</div>
                <h3>Options</h3>
                <div style='margin-left: 10px;'>
                    <b>$title</b>&nbsp;-&nbsp;<i>default</i>:&nbsp;'1'&nbsp;-&nbsp;Disables block title.
                    <br />
                    <b>$limit</b>&nbsp;-&nbsp;<i>default</i>:&nbsp;'5'&nbsp;-&nbsp;Number of items to return.
                    <br />
                    <b>$output_method</b>&nbsp;-&nbsp;<i>default</i>:&nbsp;'echo'&nbsp;-&nbsp;Will echo output.
                    <br />
                    <b>$output_method</b>&nbsp;-&nbsp;<i>other available</i>:&nbsp;'array'&nbsp;-&nbsp;Will return following array (<b>$limit</b> set to '1'):
                    <div class="codeheader">Code: <a href="javascript:void(0);" onclick="return smfSelectText(this);" class="codeoperation">[Select]</a></div><code class="bbc_code"><pre><?php $lastBlog = ssi_smfg_lastBlog(1, 1, 'array'); flush(); print_R($lastBlog);?></pre></code>
                </div>
                <h3>Code</h3>
                <div class="codeheader">Code: <a href="javascript:void(0);" onclick="return smfSelectText(this);" class="codeoperation">[Select]</a></div><code class="bbc_code">&lt;?php ssi_smfg_lastBlog(); ?&gt;</code>
                <h3>Result</h3>
                <div class="ssi_result"><?php ssi_smfg_lastBlog(); flush(); ?></div>
            </div>
            
            <div class="ssi_preview" id="ssi_smfg_lastService">
                <h2>Recent Services</h2>
                <h3>Usage</h3>
                <div class="ssi_result">ssi_smfg_lastService([<i>bool</i> $title [, <i>int</i> $limit [, <i>str</i> $output_method]]])</div>
                <h3>Options</h3>
                <div style='margin-left: 10px;'>
                    <b>$title</b>&nbsp;-&nbsp;<i>default</i>:&nbsp;'1'&nbsp;-&nbsp;Disables block title.
                    <br />
                    <b>$limit</b>&nbsp;-&nbsp;<i>default</i>:&nbsp;'5'&nbsp;-&nbsp;Number of items to return.
                    <br />
                    <b>$output_method</b>&nbsp;-&nbsp;<i>default</i>:&nbsp;'echo'&nbsp;-&nbsp;Will echo output.
                    <br />
                    <b>$output_method</b>&nbsp;-&nbsp;<i>other available</i>:&nbsp;'array'&nbsp;-&nbsp;Will return following array (<b>$limit</b> set to '1'):
                    <div class="codeheader">Code: <a href="javascript:void(0);" onclick="return smfSelectText(this);" class="codeoperation">[Select]</a></div><code class="bbc_code"><pre><?php $lastService = ssi_smfg_lastService(1, 1, 'array'); flush(); print_R($lastService);?></pre></code>
                </div>
                <h3>Code</h3>
                <div class="codeheader">Code: <a href="javascript:void(0);" onclick="return smfSelectText(this);" class="codeoperation">[Select]</a></div><code class="bbc_code">&lt;?php ssi_smfg_lastService(); ?&gt;</code>
                <h3>Result</h3>
                <div class="ssi_result"><?php ssi_smfg_lastService(); flush(); ?></div>
            </div>
            
            <div class="ssi_preview" id="ssi_smfg_lastVideo">
                <h2>Recent Videos</h2>
                <h3>Usage</h3>
                <div class="ssi_result">ssi_smfg_lastVideo([<i>bool</i> $title [, <i>int</i> $limit [, <i>str</i> $output_method]]])</div>
                <h3>Options</h3>
                <div style='margin-left: 10px;'>
                    <b>$title</b>&nbsp;-&nbsp;<i>default</i>:&nbsp;'1'&nbsp;-&nbsp;Disables block title.
                    <br />
                    <b>$limit</b>&nbsp;-&nbsp;<i>default</i>:&nbsp;'5'&nbsp;-&nbsp;Number of items to return.
                    <br />
                    <b>$output_method</b>&nbsp;-&nbsp;<i>default</i>:&nbsp;'echo'&nbsp;-&nbsp;Will echo output.
                    <br />
                    <b>$output_method</b>&nbsp;-&nbsp;<i>other available</i>:&nbsp;'array'&nbsp;-&nbsp;Will return following array (<b>$limit</b> set to '1'):
                    <div class="codeheader">Code: <a href="javascript:void(0);" onclick="return smfSelectText(this);" class="codeoperation">[Select]</a></div><code class="bbc_code"><pre><?php $lastVideo = ssi_smfg_lastVideo(1, 1, 'array'); flush(); print_R($lastVideo);?></pre></code>
                </div>
                <h3>Code</h3>
                <div class="codeheader">Code: <a href="javascript:void(0);" onclick="return smfSelectText(this);" class="codeoperation">[Select]</a></div><code class="bbc_code">&lt;?php ssi_smfg_lastVideo(); ?&gt;</code>
                <h3>Result</h3>
                <div class="ssi_result"><?php ssi_smfg_lastVideo(); flush(); ?></div>
            </div>
            
            <div class="ssi_preview" id="ssi_smfg_lastComment">
                <h2>Recent Comments</h2>
                <h3>Usage</h3>
                <div class="ssi_result">ssi_smfg_lastComment([<i>bool</i> $title [, <i>int</i> $limit [, <i>str</i> $output_method]]])</div>
                <h3>Options</h3>
                <div style='margin-left: 10px;'>
                    <b>$title</b>&nbsp;-&nbsp;<i>default</i>:&nbsp;'1'&nbsp;-&nbsp;Disables block title.
                    <br />
                    <b>$limit</b>&nbsp;-&nbsp;<i>default</i>:&nbsp;'5'&nbsp;-&nbsp;Number of items to return.
                    <br />
                    <b>$output_method</b>&nbsp;-&nbsp;<i>default</i>:&nbsp;'echo'&nbsp;-&nbsp;Will echo output.
                    <br />
                    <b>$output_method</b>&nbsp;-&nbsp;<i>other available</i>:&nbsp;'array'&nbsp;-&nbsp;Will return following array (<b>$limit</b> set to '1'):
                    <div class="codeheader">Code: <a href="javascript:void(0);" onclick="return smfSelectText(this);" class="codeoperation">[Select]</a></div><code class="bbc_code"><pre><?php $lastComment = ssi_smfg_lastComment(1, 1, 'array'); flush(); print_R($lastComment);?></pre></code>
                </div>
                <h3>Code</h3>
                <div class="codeheader">Code: <a href="javascript:void(0);" onclick="return smfSelectText(this);" class="codeoperation">[Select]</a></div><code class="bbc_code">&lt;?php ssi_smfg_lastComment(); ?&gt;</code>
                <h3>Result</h3>
                <div class="ssi_result"><?php ssi_smfg_lastComment(); flush(); ?></div>
            </div>
            
        </div>
        <span class="botslice"><span></span></span>
    </div>

<?

template_ssi_below();


function template_ssi_above()
{
    global $settings, $context, $scripturl;

    echo '<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Transitional//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-transitional.dtd">
<html xmlns="http://www.w3.org/1999/xhtml">
    <head>
        <title>SMF 2.0 SSI_Garage.php Examples</title>
        <link rel="stylesheet" type="text/css" href="', $settings['default_theme_url'], '/css/index.css?fin20" />
        <script type="text/javascript" src="', $settings['default_theme_url'], '/scripts/script.js"></script>
        <style type="text/css">
            #wrapper
            {
                width: 90%;
            }
            #upper_section .user
            {
                height: 4em;
            }
            #upper_section .news
            {
                height: 80px;
            }
            #content_section
            {
                position: relative;
                top: -20px;
            }
            #main_content_section h2
            {
                font-size: 1.5em;
                border-bottom: solid 1px #d05800;
                line-height: 1.5em;
                margin: 0.5em 0;
                color: #d05800;
            }
            #liftup
            {
                position: relative;
                top: -70px;
                padding: 1em 2em 1em 1em;
                line-height: 1.6em;
            }
            #footer_section
            {
                position: relative;
                top: -20px;
            }
            #sidenav
            {
                width: 210px;
                float: left;
                margin-right: 20px;
            }
            #sidenav ul
            {
                margin: 0 0 0 15px;
                padding: 0;
                list-style: none;
                font-size: 90%;
            }
            #preview
            {
                margin-left: 230px;
            }
            .ssi_preview
            {
                margin-bottom: 1.5em;
            }
            .ssi_preview h3
            {
                margin: 1em 0 0.5em 0;
            }
            .ssi_result
            {
                background-color: #fff;
                border: 1px solid #99a;
                padding: 10px;
                overflow: hidden;
            }
        </style>
        <script type="text/javascript"><!-- // --><![CDATA[
            var smf_scripturl = "', $scripturl, '";
            var smf_iso_case_folding = ', $context['server']['iso_case_folding'] ? 'true' : 'false', ';
            var smf_charset = "', $context['character_set'], '";

            // Sets all ssi_preview class to hidden, then shows the one requested.
            function showSSIBlock(elementID)
            {
                toggleVisibleByClass("ssi_preview", true);
                document.getElementById(elementID).style.display = "block";
            }

            // Toggle visibility of all sections.
            function toggleVisibleByClass(sClassName, bHide)
            {
                var oSections = document.getElementsByTagName("div");
                for (var i = 0; i < oSections.length; i++)
                {
                    if (oSections[i].className == null || oSections[i].className.indexOf(sClassName) == -1)
                        continue;

                    oSections[i].style.display = bHide ? "none" : "block";
                }
            }
        // ]]></script>';
        
        ssi_smfg_js_includes();
        
        echo '
    </head>
    <body>
        <div id="wrapper">
            <div id="header"><div class="frame">
                <div id="top_section">
                    <h1 class="forumtitle">SMF 2.0 SSI.php Examples</h1>
                    <img id="smflogo" src="Themes/default/images/smflogo.png" alt="Simple Machines Forum" title="Simple Machines Forum" />
                </div>
                <div id="upper_section" class="middletext" style="overflow: hidden;">
                    <div class="user"></div>
                    <div class="news normaltext">
                    </div>
                </div>
            </div></div>
            <div id="content_section"><div class="frame">
                <div id="main_content_section">
                    <div id="liftup" class="flow_auto">';
}

function template_ssi_below()
{
    global $time_start;

    echo '
                        <script type="text/javascript"><!-- // --><![CDATA[
                            showSSIBlock("ssi_smfg_featuredVehicle");
                        // ]]></script>
                    </div>
                </div>
            </div></div>
            <div id="footer_section"><div class="frame">
                <div class="smalltext"><a href="http://www.simplemachines.org">Simple Machines Forum</a></div>
            </div></div>
        </div>
    </body>
</html>';
}
