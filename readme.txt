=== Factolex ===
Contributors: sebmos
Donate link: http://sebmos.at/factolex-wordpress-plugin/
Tags: factolex, tag
Requires at least: 2.5
Tested up to: 2.7
Stable tag: trunk

Factolex is a fact lexicon with an API that allows retrieving facts and use them on your blog. This plugin makes showing facts on a blog very simple.

== Description ==

[Factolex](http://factolex.com/) is a fact lexicon with an API that allows retrieving these facts and use them on your blog. This plugin makes showing facts on a blog very simple.

The most likely use case is spicing up the tag pages with some extra content, basic explanation to a topic, etc.
This is an example fact: http://en.factolex.com/Google:internet

Facts from Factolex are available in **English** and **German**.

== Installation ==

To *install* the plug-in, copy the factolex.php-file into the wp-content/plugins-folder and activate the plug-in in the WordPress Plugin Administration.

To *use* the plugin, write the following code to the template file that should
show the facts:

`<?php 
$factolex = new Factolex_Facts($keyword, $number_of_facts (default: 3), $language (en|de));
echo $factolex->getHtml();
?>`

To use the tag pages tag, use the following line:

`<?php
$keyword = single_tag_title('', false);
?>`