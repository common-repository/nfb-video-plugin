=== NFB Video Plugin ===
Contributors: NFB_ONF
Tags: video, url embed, oembed, post, nfb
Requires at least: 2.8
Tested up to: 3.8.1
Stable tag: trunk


The NFB Video Plugin is designed to allow users to embed videos 
from sites who provide autodiscoverable oembed links on their 
film pages.

== Description ==

The NFB Video Plugin is designed to allow users to embed videos 
from sites who provide autodiscoverable oembed links on their 
film pages.

Previously, to embed a film, we had to provide the entire object
or embed the information ourselves; we had to play with the sizes
and hope that we didn't break it.

When a site contains autodiscovery, you only need to enter "oe"
followed by the pasted link and it will seek out the embed
and set it to the size you defined.

Good examples to test it are :

oehttp://www.nfb.ca/film/louise_en/

Set 1 size for all your embeds!


== Installation ==

1. Upload nfb_video_plugin to your plugin directory
    wp-content/plugins/nfb_video_plugin

2. Go to the Plug-ins tab in your administration panel and
    activate the plugin.

3. Take a page from a site like NFB.ca like : 
     http://www.nfb.ca/film/louise_en/ or http://www.onf.ca/film/paow_paow_te_mort/

4. Type "oe" minus the quotes followed immediately by the URL.
     oehttp://www.nfb.ca/film/louise_en/ or oehttp://www.onf.ca/film/paow_paow_te_mort/

5. Adjust your settings in the Settings->NFB Video Plugin.

== Frequently Asked Questions ==

WHAT IS OEMBED?

oEmbed is a format for allowing an embedded representation 
of a URL on third party sites. The simple API allows a 
website to display embedded content (such as photos or 
videos) when a user posts a link to that resource, without 
having to parse the resource directly.

Find information about oEmbed at http://www.oembed.com/

WHAT WAS USED TO MAKE THIS PLUGIN?

The specifications of oEmbed
http://www.oembed.com/

== Changelog ==
0.9.14
++ More php shorttags changed to longtags, now works on systems that don't allow php shorttag

0.9.13
++ Switched from php shorttag to longtag for compatibilty

0.9.12
++ Missing https protocol in nfb_functions has been corrected

0.9.11
++ Both http and https protocols are checked when attempting to autodiscover on an embedded url

0.9.10
++ All urls properly attach traffic_src=nfb_video_plugin now.

0.9.9
++ All urls in the embed codes of oembeds now show traffic_src=nfb_video_plugin to help stat systems spot what traffic comes directly from this plugin

0.9.8
++ Adjusted caption information for SEO changes made to NFB.ca

0.9.7
++ Fixed the 404 issue, when a url no longer existed. Replaced with red [Invalid Link]

0.9.6
++ Fixed error showing up in PHP 5.3 when ampersand is in front of a variable name.
