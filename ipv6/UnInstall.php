<?php
/*
IPv6
Version 1.0
by:vbgamer45
https://www.smfhacks.com
Copyright 2025 SMFHacks.com
*/

if (file_exists(dirname(__FILE__) . '/SSI.php') && !defined('SMF'))
  require_once(dirname(__FILE__) . '/SSI.php');

// Show the iframe with the uninstall
$modName = base64_encode("IPv6");
echo '<iframe src ="https://www.smfhacks.com/uninstall.php?modname=' . $modName . '" width="100%" height="200px">
  <p>Your browser does not support iframes.</p>
</iframe>
<b>Other Helpful Mods to make your forum stand out</b><br />
<a href="https://www.smfhacks.com/smf-gallery-pro.php" target="_blank">SMF Gallery Pro</a> - A fully featured gallery for SMF<br />
<a href="https://www.smfhacks.com/smf-store.php" target="_blank">SMF Store</a> - eCommerce Store system using PayPal<br />
<a href="https://www.smfhacks.com/smf-classifieds.php" target="_blank">SMF Classifieds</a> - Auction/Listing system for SMF<br />
<a href="https://www.smfhacks.com/ad-seller-pro.php" target="_blank">Ad Seller Pro</a> - Make more money from your forum with ads<br />
<a href="https://www.smfhacks.com/download-system-pro.php" target="_blank">Downloads System Pro</a> - charge people for downloads<br />
<a href="https://www.smfhacks.com/awesomepostratings.php" target="_blank">Awesome Post Ratings</a> - gain more interaction from your visitors<br />
<a href="https://www.smfhacks.com/newsletter-pro.php" target="_blank">Newsletter Pro</a> - send html emails with open, click and unsubscribe tracking.<br />

';


?>