=== Algorithmia AI ===
Contributors: kenburcham
Tags: algorithmia, algorithm, ai, artificial, intelligence, nsfw, alt-text, summarize
Requires at least: 4.6
Tested up to: 4.9
Requires PHP: 5.5
License: GPLv2 or later
License URI: https://www.gnu.org/licenses/gpl-2.0.html

Use Algorithmia AI algorithms from the marketplace in your WordPress.

== Description ==

Add the power of AI algorithms to your WordPress website! 

Algorithmia is a scalable microservices platform that provides AI Algorithms you can call from an API. This plugin uses the Algorithmia PHP client and wraps three of those algorithms (noted below) and provides a template you can use to easily add any algorithm from the marketplace to your website.

Installing this plugin and adding your Algorithmia API key ([sign-up required](https://algorithmia.com/signup)) will allow you to:

1. Auto-tagging (in alt-text) of uploaded image files - Upload an image file and an algorithm will recognize the objects in your image and add the lables automatically to your alt-text. This is important for SEO and helpful for categorizing your photos.
1. Nudity detection of uploaded image files - An algorithm will detect nudity in an uploaded image and either tag it as 'nudity' in the alt-text or optionally block the upload (depending on your settings).
1. Post summarizer - Every post will be summarized into a brief sentence or two and the summary pre-pended to the post! This is a fun example of how to add your own algorithm to this plugin.

This plugin is meant to be a platform that developers can use to easily add new algorithms. 
1. First, browse to the [Algorithmia Marketplace](https://algorithmia.com/algorithms) and discover one of the many algorithms available that you want to use in your website.
1. Determine which [WordPress hook or filter](https://adambrown.info/p/wp_hooks) you want to add to insert some artificial intelligence into the workflow.
1. Copy one of the examples that you can find in this plugin's /algorithms directory, rename it and edit it with your developer ninja skills.
1. Repeat! and be amazed at how easy it is to add AI to your site with just a single API call!

== Installation ==

1. Upload the plugin files to the `/wp-content/plugins/algorithmia-ai` directory, or install the plugin through the WordPress plugins screen directly.
1. Activate the plugin through the 'Plugins' screen in WordPress
1. Use the Settings->Algorithmia screen to configure the plugin (including adding in your API key)

== Screenshots ==

1. The object recognition algorithm automatically adds tags to your alt-txt for your image.
1. Nudity detection can block images with nudity or just mark them as 'nudity' in the alt-text.
1. You can manage these settings in the Settings->Algorithmia admin panel.
1. If both nudity detection and object recognition are enabled (and blocking is turned off) then you get alt-txt from both algorithms.