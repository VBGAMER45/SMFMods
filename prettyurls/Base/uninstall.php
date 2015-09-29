<?php

//	Pretty URLs - Base v0.8.1

//	All I do is disable URL rewriting. If you're uninstalling the mod manually, please just ignore me.

$modSettings['pretty_enable_filters'] = false;


// Show the iframe with the uninstall
$modName = base64_encode("Pretty Urls");
echo '<iframe src ="http://www.smfhacks.com/uninstall.php?modname=' . $modName . '" width="100%" height="200px">
  <p>Your browser does not support iframes.</p>
</iframe>
<b>Other Helpful Mods</b><br />
<a href="http://www.smfhacks.com/download-system-pro.php" target="_blank">Downloads System Pro</a><b>Other Helpful Mods</b><br /><br />

<a href="http://www.smfhacks.com/smf-gallery-pro.php" target="_blank">SMF Gallery Pro</a><br />

<a href="http://www.smfhacks.com/smf-store.php" target="_blank">SMF Store</a><br />
<a href="http://www.smfhacks.com/smf-classifieds.php" target="_blank">SMF Classifieds</a><br />

<a href="http://www.smfhacks.com/newsletter-pro.php" target="_blank">Newsletter Pro</a><br />
';


?>
