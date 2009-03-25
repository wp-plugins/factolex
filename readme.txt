=== Factolex ===
Contributors: sebmos
Donate link: http://sebmos.at/factolex-wordpress-plugin/
Tags: factolex, tag
Requires at least: 2.5
Tested up to: 2.7.1
Stable tag: trunk

Factolex is a fact lexicon with an API that allows retrieving facts and use them on your blog. This plugin makes showing facts on a blog very simple.

== Description ==

[Factolex](http://factolex.com/) is a fact lexicon with an API that allows retrieving these facts and use them on your blog. This plugin makes showing facts on a blog very simple.

The most likely use case is spicing up the tag pages with some extra content, basic explanation to a topic, etc.
This is an example fact: http://en.factolex.com/Google:internet

Facts from Factolex are available in **English** and **German**.

== Installation ==

To *install* the plug-in, copy the factolex.php-file into the wp-content/plugins-folder and activate the plug-in in the WordPress Plugin Administration.

There are two ways to *get data* and two ways to *use the data* with the plugin, they require editing your theme's template files.

**Step 1: Initialize Plugin**

`$factolex = new Factolex_Facts();`

**Step 2: Get Data** 

*2.1 Get Terms by Name*

Get only those terms that equal the search query.

`$factolex->getByTerm($query, $language (en|de));`

*2.2 Get Term by ID*

Get one term based on Factolex' internal term ID.

`$factolex->getById($id);`

*2.3 Search for Terms*

Returns all terms, including those that don't exactly equal the search query.

`$factolex->search($query, $language (en|de));`

**Step 3: Return the Data**

*3.1 Return HTML*

`$factolex->getHTML($maximum_facts);`

*3.2 Return Array*

`$factolex->getArray();`

`
$data = array(
	array(
		'title'	=> 'Term title',
		'id'	=> 'Internal Term ID',
		'link'	=> 'Link to term on Factolex homepage',
		'tags'	=> 'Tags, separated by spaces',
		'facts'	=> array(
			array(
				'title'		=> 'Fact text',
				'tags'		=> 'Tags, separated by spaces',
				'source'	=> 'Link to source, if available'
			)
		)
	)
);`

**Example**

To search for terms on the tag page and return the three best ones.

`<?php
$factolex = new Factolex_Facts();
$factolex->getByTerm(single_tag_title('', false), 'en');
echo $factolex->getHtml(3);
?>`

**Nofollow-Links**

To automatically nofollow links to Factolex, edit the FACTOLEX_NOFOLLOW constant in factolex.php to 1.