Magic Llama Mod v2.0 for SMF 2.1
==================================

By vbgamer45 - https://www.smfhacks.com
Original concept by Aquilo (2004) - rewritten for SMF 2.1.

Description
-----------
A gamification mod that randomly releases virtual llamas on forum pages.
Members click floating llama images to catch them, earning or losing points.
Good llamas give points, evil llamas take them away!

Installation
------------
1. Upload the mod package via Admin > Package Manager > Upload Package
2. Install the package - it will create tables, settings, and register hooks
3. Configure settings at Admin > Configuration > Modification Settings
4. Set "Llama Chances" higher (e.g. 50) for testing, then lower for production

Features
--------
- Floating animated llama that bounces around the page
- Two llama types: Good (earns points) and Evil (loses points)
- Configurable point ranges for each type
- Custom catch messages with placeholders (%N = name, %K = type, %P = points)
- AJAX-powered catching (no page reload)
- Race condition protection (double-catch prevention)
- User option to hide llamas from their profile
- Admin log to view all released/caught llamas
- Admin maintenance to clean up uncaught llamas
- Self-contained points system (no modifications to core members table)
- Profile stats page showing llama catch history

Uninstallation
--------------
Uninstall via Admin > Package Manager. This will:
- Remove all hooks
- Drop the magic_llama and magic_llama_members tables
- Remove all mod settings
- Delete installed files

Compatibility
-------------
- SMF 2.1.x
- Uses hook-based architecture (zero core file edits)

Credits
-------
- Original mod: Aquilo (http://www.xtram.net)
- Floating object script: Virtual_Max
- SMF 2.1 rewrite: vbgamer45 (https://www.smfhacks.com)

