[hr]
[center][size=16pt][b]Smush.it! For SMF[/b][/size]
[/center]
[hr]

[color=blue][b][size=12pt][u]Introduction[/u][/size][/b][/color]
This adds the ability to run WPMU DEV WordPress Smush API Smushit service on your attachments. Smush.it uses optimization techniques specific to each image format to remove unnecessary bytes from image files. It is a "lossless" tool, which means it optimizes the images without changing their look or visual quality. Typical savings are in the 5-20% file size reduction.  
After Smush.it runs it will report how many bytes have been saved as well as the percent reduction for each file.  Smaller files save bandwidth, disk space and make your forum faster.

[color=blue][b][size=12pt][u]License[/u][/size][/b][/color]
Ported to SMF by vbgamer45 http://www.smfhacks.com
Orginal by Spuds
This modification is released under a MPL V1.1 license, a copy of it with its provisions is included with the package.

[color=blue][b][size=12pt][u]Features[/u][/size][/b][/color]
[list]
[li]Ability to run Smush.it on all current attachments in a batch mode (based on attachment age/size)[/li]
[li]Admin->Forum->Attachments & Avatars->File Maintenance[/li]
[li]Ability to selectively run Smush.it on any single or selection of attachments[/li]
[li]Admin->Forum->Attachments & Avatars->Browse->Smush.it[/li]
[li]Can run as a scheduled task to Smush.it attachments added in the last 24hrs[/li]
[li]Admin->Maintenance->Scheduled Tasks[/li]
[/list]

[color=blue][b][size=12pt][u]Important Notes[/u][/size][/b][/color]
[list]
[li]The free API on the Smush.it service will not accept files >1M in size, as such no size reduction on those files is possible with this addon[/li]
[li]Unable to copy the Smush.it file back to the attachment directory: This generally indicates that the original attachment file was saved with permissions (or owner/group) that will not allow the forum to replace it.  This can occur if the attachments were FTP-ed to the site or your site changed how PHP is run.  You will need to change the file permissions as needed (666)[/li]
[li]Smush.it returned the following error: Failed to create a temp dir: This means what it says, e.g. Smush.it is unavailable at the current time, all you can do is try again later[/li]
[li]I ran Smush.it and now the file does not show up in the browse list! This means the file was reduced in size below your lower size setting (admin -> configuration -> modification settings -> misc -> Attempt to Sumsh.it all attachments larger than)[/li]
[/list]

[color=blue][b][size=12pt][u]Installation[/u][/size][/b][/color]
Simply install the package to install this addon.