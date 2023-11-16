[size=3][color=navy][b]Anti Bot: Unrecognizable Form v1.1[/b][/color][/size]
[hr]

[right][table]
[tr][td][color=navy][b]Compatible With:[/b][/color][/td][td]SMF 1.1.X - SMF 2 Beta & RC1[/td][/tr]
[tr][td][color=navy][b]Created By:[/b][/color][/td][td][url=http://www.simplemachines.org/community/index.php?action=profile;u=192278][b].LORD.[/b][/url][/td][/tr]
[tr][td][color=navy][b]Version:[/b][/color][/td][td]1.1[/td][/tr]
[tr][td][color=navy][b]Initial Release:[/b][/color][/td][td]2nd May 2009[/td][/tr]
[tr][td][/td][td][/td][/tr]
[tr][td][color=navy][b]Languajes:[/b][/color][/td][td]All[/td][/tr]
[/table][/right]

This MOD make a fake Form and make unrecognizable (for bots) the real Form.

The bots will use the "fake Form" and the humans the "real Form".

Your users will not notice the difference, and the bots receive a error message for sidetrack.

How to test this MOD?
1.- Open the form to register a new user (not send).
2.- Installing the MOD.
3.- Send the form opened in the step 1. (and see the "error message")
4.- Now send a form opened after installing the MOD. (and register without problem)

Why? The bots will continue using the "old form", the form SMF's by default.

Extra: How it works?
[url=http://www.simplemachines.org/community/index.php?topic=309200.msg2051709#msg2051709]Post 1[/url]
[url=http://www.simplemachines.org/community/index.php?topic=309200.msg2065077#msg2065077]Post 2[/url]
[url=http://www.simplemachines.org/community/index.php?topic=309200.msg2084906#msg2084906]Post 3[/url]

[quote author=Karl Benson]It is a cat and mouse game between forum software and bot-creators to secure forums against spam bots.
Using generic/centralised anti-spam measures makes it viable for bot-creators to try to get past them.
If every forum employs completely different anti-bot measures it makes it almost impossible to create bots for mass-automated registration.
[/quote]

[code=CHANGELOG]1.1 - 29th May 2009
	o Now the register page isn't cacheabled. It is useful against "Send Form" and "Go Back"
	o Fix a bug in Register.template and password visible. Thanks DistantJ for report
1.0 - 2nd May 2009
	o Initial release
	o Adds Mutation in the Form Register
[/code]
