=== Plugin Name ===
Contributors: swhitley
Tags: WHMCS, theme, themes, template, templates, smarty, copy, clone, html
Requires at least: 2.7.0
Tested up to: 3.0.1
Stable tag: 1.00

Copy the HTML from your WordPress theme to another application's template library.  Designed for use with WHMCS but will work with other applications.

Add the following hints to your WordPress theme files. The hints will be used to update another application's template files. Click Sync Template whenever your WordPress header or footer change.

&lt;!--header.end--&gt;	The end of your WordPress header code.

&lt;!--footer.begin--&gt;	The beginning of your WordPress footer code.



Include file contents from another template directory.

&lt;!--{unique id}.inc--&gt;	The contents of {directory}/{unique id}.inc will be added to the code at the specified location.



Exclude WordPress code from your templates.

&lt;!--tpl.exclude--&gt;	The beginning of the text to exclude.

&lt;!--/tpl.exclude--&gt;	The end of the text to exclude.


== Installation ==

1. Upload `templatesync.php` and all included files to the `/wp-content/plugins/` directory.
1. Activate the plugin through the 'Plugins' menu in WordPress.
1. Modify plugin options through the `Settings` menu.


== Change Log ==


1.0

08/12/2010 Shannon Whitley   

- Initial Creation
