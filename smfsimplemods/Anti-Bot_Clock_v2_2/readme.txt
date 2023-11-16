[size=3][color=navy][b]Anti Bot: Captcha Clock v2.2[/b][/color][/size]
[hr]

[right][table]
[tr][td][color=navy][b]Compatible With:[/b][/color][/td][td]SMF 1.1.X - SMF 2 Beta & RC1[/td][/tr]
[tr][td][color=navy][b]Created By:[/b][/color][/td][td][url=http://custom.simplemachines.org/mods/index.php?action=profile;u=63186][b]Karl Benson[/b][/url][/td][/tr]
[tr][td][color=navy][b]Developed By:[/b][/color][/td][td][url=http://www.simplemachines.org/community/index.php?action=profile;u=192278][b].LORD.[/b][/url][/td][/tr]
[tr][td][color=navy][b]Version:[/b][/color][/td][td]2.2[/td][/tr]
[tr][td][/td][td][/td][/tr]
[tr][td][color=navy][b]Languajes:[/b][/color][/td][td]English[/td][/tr]
[/table][/right]

A new and unique [b]Anti-Bot Registration Check[/b]. Rather than getting registrants filling in letters, have them tell the time on a clock.

[quote author=Karl Benson]It is a cat and mouse game between forum software and bot-creators to secure forums against spam bots.
Using generic/centralised anti-spam measures makes it viable for bot-creators to try to get past them.
If every forum employs completely different anti-bot measures it makes it almost impossible to create bots for mass-automated registration.
[/quote]

[size=8pt]Some people think that this MOD is "ugly".
I have tried to make it more agreeable for humans without decreasing the difficulty for bots.
I deleted effects little or not detrimental for bot, and unpleasant for people.
And I added other effects more annoying to bots and less for humans.[/size]

[color=navy][b]Version 2.0:[/b][/color] Work for SMF 1.1.X and SMF 2 Beta & RC1 and many more (See ChangeLog)

[code=CHANGELOG]2.2 - 23th May 2009
	o Now the page in cache is ignored and alwys is reloaded.
	  It is useful against "Send Form" and "Go Back".
	o Have been added an error message that notified that the page has expired
	o Some images have been improved
2.1 - 11th May 2009
	o Add change to improve the security - tranks szcoder for notify
2.0 - 2nd May 2009
	o .LORD. Take the development
	o Work for SMF 1.1.X and SMF 2 Beta & RC1
	o You can enable and disable this MOD in: Members > Registration > Settings 
	o Also You can Configurate this MOD
	o Fix bug drawing the hour hand (your users couldn't register)
	o Changed and improve some code
	o The ABClock.php have been remade
	o Changed the drawing clocks's (clocks more cute for humans and effectives with bots)
		- The dots (noise) have been eliminated.
		- The effects colorized have been eliminated.
		- The effects have been disable. (FILTER_GRAYSCALE and FILTER_MEAN_REMOVAL eliminated).
		+ The radial lines (noise) have been added.
		+ The reloj rotate in a range angles.
		+ A new reloj have been added, and new images added.
		+ Effect cristal. Cute for humans, noise for bots (can be improved)
1.0 - 20th March 2008
	o Initial release
	o Creates a clock from several different face and hands sets
	o Utilizes new gd functions in PHP5 to colorize and style the clock
	o Generates a different clock and time each time.
[/code]

[color=navy][b]REQUIRED:[/b][/color] A manual edit is REQUIRED for ALL themes (other than SMF Core Default) which have a custom Register.template.php
[b]If you don't do perform the edit, nobody will be able to register using those themes.[/b]

[color=red][b]REQUIRES Minimium[/color]:
- PHP >= 4.3.2 & GD Library 2.0.34 (or newer)
- Do NOT install the mod if your server does not meet the minimum requirements.
- To check what GD Library your server has goto Admin > Support & Credits
- If you get an all black image or it doesn't appear correctly, you might have a dodgy version of the library.
Please do NOT ask me how to install GD library or upgrade it.  I don't know.