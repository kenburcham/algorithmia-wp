=== Algorithmia ===
Contributors: kenburcham, peckjon, https://algorithmia.com
Tags: algorithmia, algorithm, ai, artificial intelligence, machine learning, template, object detection, nsfw, nudity detection, alt-text, summarize
Requires at least: 4.6
Requires PHP: 5.6
Tested up to: 4.9.7
Stable tag: 2.0.1
License: MIT
License URI: https://opensource.org/licenses/MIT

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

This section describes how to install the plugin and get it working.

1. Register for an account on https://algorithmia.com and take note of your API key.
2. Upload the plugin files to the `/wp-content/plugins/algorithmia-ai` directory, or install the plugin through the WordPress plugins screen directly.
3. Activate the plugin through the 'Plugins' screen in WordPress
4. Use the Settings->Algorithmia screen to configure the plugin (including adding in your API key)

== Screenshots ==

1. The object recognition algorithm automatically adds tags to your alt-txt for your image.
2. Nudity detection can block images with nudity or just mark them as 'nudity' in the alt-text.
3. You can manage these settings in the Settings->Algorithmia admin panel.
4. If both nudity detection and object recognition are enabled (and blocking is turned off) then you get alt-txt from both algorithms.

== Frequently Asked Questions ==

= Do I need to Register for Algorithmia? =

Yes. Visit [algorithmia.com](https://algorithmia.com) to register.

= What other algorithms are supported? =

This plugin currently supports image object detection, nudity detection, and text summarization -- but any Algorithmia API can be integrated. Use this plugin as a template and visit [algorithmia.com/algorithms](https://algorithmia.com/algorithms) to find other APIs.

== Changelog ==

= 1.0 =
* Initial release of (old) content recommendation widget.

= 2.0 =
* Complete rewrite using official Algorithmia PHP Client

= 2.0.1 =
* Require trunk of algorithmiaio/algorithmia-php (PHP 5.6-7 compatible)
