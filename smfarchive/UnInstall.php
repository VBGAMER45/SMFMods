<?php
/*
SMF Archive
Version 3.0
by:vbgamer45
https://www.smfhacks.com
Copyright 2008-2023 http://www.samsonsoftware.com
*/

if (file_exists(dirname(__FILE__) . '/SSI.php') && !defined('SMF'))
  require_once(dirname(__FILE__) . '/SSI.php');

// Show the iframe with the uninstall
$modName = base64_encode("SMF Archive");
echo '<iframe src="https://www.smfhacks.com/uninstall.php?modname=' . $modName . '" width="100%" height="200px">
  <p>Your browser does not support iframes.</p>
</iframe>
<b>Other Helpful Mods</b><br />
<a href="https://www.smfhacks.com/smf-gallery-pro.php" target="_blank">SMF Gallery Pro</a> - A fully featured gallery for SMF<br />
<a href="https://www.smfhacks.com/download-system-pro.php" target="_blank">Downloads System Pro</a><br />
<a href="https://www.smfhacks.com/smf-store.php" target="_blank">SMF Store</a><br />
<a href="https://www.smfhacks.com/smf-classifieds.php" target="_blank">SMF Classifieds</a><br />

<a href="https://www.smfhacks.com/newsletter-pro.php" target="_blank">Newsletter Pro</a><br />

';


?>