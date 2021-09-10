=== Plyo Schema Extender ===
Contributors: Pete Markeiwicz
Tags: Schema, Schema.org, Custom Post Types, Yoast SEO, Yoast Local SEO
Requires at least: 1.0.0
Tested up to: 1.0.0
Stable tag: 1.0.0
License: GPL-2.0+
Requires PHP: 5.6

Plugin for adding some Schema to Yoast SEO.

== Description ==

Plyo Schema Extender provides interface for registering and managing additional Schema, when the Yoast SEO plugin is already installed.

Official development of Plyo Schema Extender (PLSE) is on GitHub. The GitHub repo can be found at [https://github.com/pindiespace/plyo-schema-extender](https://github.com/pindiespace/plyo-schema-extender). Please use the Support tab for potential bugs, issues, or enhancement ideas.

== Screenshots ==

1. Define Custom Post Types and Categories which fire a specific supported Schema
2. Add Schema Data to a post.

== Changelog ==

= 1.0.0 - 2021-08-16 =
* Determined that the AMP plugin fails, unless you explicitly assign Custom Post Types to be processed by AMP. This causes an error in the Web Console that is not fatal, but may be mistaken for a PLSE error.

== Upgrade Notice ==

= 1.0.0 - 2021-06-16 =
* Added: repeater fields.

== Installation ==

= Admin Installer via search =
1. Visit the Add New plugin screen and search for "plyo schema extender".
2. Click the "Install Now" button.
3. Activate the plugin.
4. Navigate to the "Plyo Schema Ext" Menu.

= Admin Installer via zip =
1. Visit the Add New plugin screen and click the "Upload Plugin" button.
2. Click the "Browse..." button and select zip file from your computer.
3. Click "Install Now" button.
4. Once done uploading, activate Plyo Schema Extender in the admin Plugins list.

= Manual =
1. Upload the Plyo Schema Extender folder to the plugins directory in your WordPress installation.
2. Activate the plugin.
3. Navigate to the "Plyo Schema Ext" Menu.

That's it! Now you can add more Schema data to your pages, posts, Custo Post Types.

== Frequently Asked Questions ==

Q: How do I restrict Schema?
A: In plugin options, you can select posts for each schema. You can restrict by Custom Post Type (CPT), or you can create categories for posts and pages which trigger the Schema.

Q: How can I integrate with Yoast Local SEO?
A: If you have Yoast Local SEO installed, just go to the home screen of the plugin in WP_Admin, and click the "load Local" SEO button. The plugin will copy Yoast Local SEO values into your configuration, overwriting anything currently there. Changes in the plugin don't affect Yoast at all.

#### User documentation
TBD

#### What the Schema supported

Some things that are not supported include:
- offers
- reviews
- subjectOf

These sub-objects require that the information be on the page, or on a remote page. If the values in the Schema aren't the same, it could generate an error.

The Service Schema is a particular problem for pricing, since pricing might be bulk, by the hour, or made of of multiple priced sub-services.
https://www.schemaapp.com/schema-markup/services-schema-markup-schema-org-services/


#### Ecosystem

 * Note: If you are using the AMP plugin, make sure you are set to "Standard" and you enable Advanced Settings -> Content Types -> Your Custom Post Type. Otherwise, 
 * you may see an Ajax error in the Web Console.
 * new CPTs must be enabled in the AMP plugin!
 * new CPTs should have 'custom fields' enabled
