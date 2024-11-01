=== WPML2WPMSLS ===
Contributors: idealien
Tags: WPML, bilingual, multilingual, i18ln, convert, export, import, multisite, network
Requires at least: 5.2
Tested up to: 5.4.2
Stable tag: 0.2.0
License: GPLv2

Convert posts from an existing WPML multilingual site via WP Import/Export to a WPMS (Network) with Language Switcher so easily it feels like magic!

== Description ==

See <http://wordpress.org/extend/plugins/wpml2wpmsls/>

[WPML](http://wpml.org) stores each entry as a separate post and uses some custom tables to connect the translations. 
If you de-activate the plugin or have issues with functionality related to version updates, you get one site with multi-lingual confusion. 
See [Getting WordPress to speak your language](http://www.slideshare.net/r3df/getting-word-press-to-speak-your-langauge/) for more details how / 
why using a WordPress MultiSite / Network is becomming the recommended strategy for multilingual sites. At least until it is fully supported in core.

This plugin:

*   Converts the WPML translation records into meta fields on actual posts or post types
*   Helps with the export/import to your new WPMS site the data.
*   Restores the meta data back to options so that [Multisite Language Switcher](http://wordpress.org/plugins/multisite-language-switcher/) will recognize all your translations.

You will need to handle conversion of any strings, .po files or other language elements separately.

== Download ==

You can download the latest stable version of WPML2WPMSLS from the WordPress
Plugin Repository at [http://wordpress.org/plugins/WPML2WPMSLS/](http://wordpress.org/plugins/WPML2WPMSLS/)>


== Installation ==

1. Upload the `WPML2WPMSLS` directory to the `/wp-content/plugins/` directory of your WPML-based multilingual site.
1. Login to your WordPress instance as an admin user.
1. Activate the plugin through the 'Plugins' menu in WordPress.
1. Go to the Settings > WPML 2 WPMSLS menu.
1. Set Conversion Mode to WPML and select which post type(s) you want to convert.
1. Press Update. All of the selected post types will have their translations associated together via meta data.
1. Export your post type(s) using the [WordPress export](http://codex.wordpress.org/Tools_Export_Screen).
1. Import your posts to each of your language specific WordPress Network sites.
1. Install, activate and configure [Multisite Language Switcher](http://wordpress.org/plugins/multisite-language-switcher/)
1. Install the WPML2WPMSLS plugin to your WordPRess MS / Network site.
1. Go to the Settings > WPML 2 WPMSLS menu.
1. Set Conversion Mode to WPMS & select which post type(s) you want to finish.
1. Press Update. All of the selected post types will have their translations restored.


== Help and Support ==

Please post questions, request for help to the Wordpress plugin forum dedicated to it: [http://wordpress.org/support/plugin/WPML2WPMSLS](http://wordpress.org/support/plugin/WPML2WPMSLS).

== TO-DO's ==
= Complete the enhancements for WordPress export. =
Currently, if you have 100 posts translated into 3 languages, you would export all posts once, import that same .xml file into 3 sites and delete the 200 from each 
site which are not the corresponding language. Then, you would be able to run steps 7 and 8 of the usage instructions above. With an enhanced WordPress export feature 
built into this plugin you would be able to specify the language for export to filter files. In that same scenario, you would run 3 exports (100 posts each of unique 
language) and import each language-based export file into the new sites before running the same steps 11 thru 13 above.

== Source and Development ==

WPML2WPMSLS welcomes friendly contributors wanting to lend a hand, be it in
the form of code through SVN patches, user support, platform portability
testing, security consulting, localization help, etc.

The [current] goal is to keep the plugin self-contained (ie: no 3rd-party lib)
for easier security maintenance, while keeping the code clean and extensible.

Active development for the plugin is handled via GitHub with releases being published to the WordPress Plugin Repository:

* Development: <https://github.com/Idealien/wpml2wpmsls/>
* Stable:     <http://svn.wp-plugins.org/wpml2wpmsls/trunk/>

== Project History ==

Originally started because, well, you know...[Efficiency or lazyness - your pick](http://www.howtogeek.com/102420/geeks-versus-non-geeks-when-doing-repetitive-tasks-funny-chart/).

== Changelog ==

= 0.1.0 =
Initial release. Limited test environments - feedback very welcome.

= 0.2.0 =
- Update for PHP 7.X support
- Begin transition of development to GitHub - https://github.com/Idealien/wpml2wpmsls
- Begin applying WP Coding Standards

