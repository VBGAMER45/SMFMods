[hr]
[center][size=16pt][b]Google Member Map 3.0[/b][/size]
[b]By Spuds[/b]

[/center]
[hr]

[color=blue][b][size=12pt][u]License[/u][/size][/b][/color]
This modification is released under a MPL V1.1 license, a copy of it with its provisions is included with the package.

[color=blue][b][size=12pt][u]Dependencies[/u][/size][/b][/color]
The Google JavaScript Maps API V3 to create the map and place pins.  This API is available for any web site that is free to consumers. By enabling and using this SMF modification you will be acknowledging and agreeing to the Google<a href="http://code.google.com/apis/maps/terms.html"> terms of use</a>';

[url=http://jscolor.com/]JSColor[/url] project to select the pin color in the admin interface.  JSColor is by Jan Odvárko and is released under the GNU Lesser General Public License. LGPL differs from GPL by allowing you to use JSColor even in non-(L)GPL applications.

[color=blue][b][size=12pt][u]Introduction[/u][/size][/b][/color]
This mod installs a member map to your website which allows your members to pin their location on a map. It uses Google Maps 3.0 API to generate the map and place 'Push" pins. 

Google Earth can also make use of the pin data. This mod allows for the exporting of user pin data in to a .kml file for those that want to use Google Earth to see their member locations.  Simply add a network link in Google Earth to point at http://www.example.com/forums/index.php?action=.kml to get the data for Google Earth.  The capability to export .kml files is controlled by the permission to view the map, and keep in mind Google Earth will appear as a guest to your forum.

[color=blue][b][size=12pt][u]Features[/u][/size][/b][/color]
o Adds a member map button to the main menu
o Adds a member profile area for users to add their pin to their profile.  This will then appear on the map.
o Ability to search by location when placing thier pin
o Adds Profile info bubbles to the map pins
o Ability to cluster pins together to improve map legabilty.  Clusters will un-cluster as you zoom in on them

[color=blue][b][size=12pt][u]How to Use[/u][/size][/b][/color]
In your admin panel you will need to enable it, which implies your acceptance of Google Maps terms of service.  Choose the settings that best work for your site.  There are many settings so you can fine tune the experience for your users and site.  Next, your members will need to edit their profiles and place a pin on the map to show their location and save their profile. That pin will then display on the main member map page. The admin will also need to set the map permissions so users can view the map as well as place a pin on the map. 

[color=blue][b][size=12pt][u]Support[/u][/b][/color]
Please use the member map thread for support with this modification.

[color=blue][b][size=12pt][u]Changelog[/u][/size][/b][/color]
[b]4.0[/b]
+Added support for SMF 2.1 thanks to Matthias

[b]3.0.6 - [/b]
!Fixed updating pins
!Only update pin date if location changed.

[b]3.0 - 11/24/2016 [/b]
o + added api key setting to make google maps work again
o + Fixed some urls and made links https

[b]2.7 - xx yyy 2012[/b]
o + added CSS to style the places select dropdown

[b]2.6 - 18 Dec 2011[/b]
o + added a hidden map reset button to middle of pan control
o + prevented output of gzip data when portamx is active
o ! fixed member names not appearing in bold when they should have been
o ! fixed undefined txt var due to order of load language

[b]2.5 - 07 Dec 2011[/b]
o + re-Released under proper open license
o + all javascript redone to use GoogleMaps V3 api, API key is no longer needed
o + new pin clustering code, can show cluster images as well as pins, has dynamic cluster pin sizing
o + local search now uses places library, pan/zooms to location but does not show pin (limits user confusion)
o + Updated map display code
o + updated kml output for Google Earth use
o + updated info bubble
o + added color picker to the admin panel to make choosing pin colors easier
o + ability to set where you want the button to appear in the top menu