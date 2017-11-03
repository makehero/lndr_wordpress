=== Lndr ===
Contributors: xcf33
Donate link: http://www.lndr.co
Tags: Langing page, SaaS, 3rd party integration
Requires at least: 4.7
Tested up to: 4.8
Stable tag: 1.0
License: GPLv2 or later
License URI: http://www.gnu.org/licenses/gpl-2.0.html

This plugin allows you to deploy Lndr landing page to your wordpress websites.

== Description ==

This plugin allows users that uses Lndr landing page building platform to publish web pages they built in Lndr to a WordPress website. One of the primary uses for this is to allow users to publish to a domain name that runs on WordPress.

Once this plugin is installed and configured correctly, you will be able to publish landing pages from Lndr to the WordPress website url. Pages published to WordPress will appear as pages in WordPress.

== Before you start ==

You will need to create a free user account on Lndr (http://www.lndr.co) in order to use the platform. Additionally, after you have successfully created an account, you can obtain an API key under your user account (https://www.lndr.co/users/edit) that will be needed as part of the WordPress plugin configuration.

This WordPress plugin does not collect or track additional data or usage information. For full term of use on the Lndr product as well as privacy policy, please see http://www.lndr.co/tou.html

== Installation ==

1. Upload `lndr directory` to the `/wp-content/plugins/` directory
2. Activate the plugin through the 'Plugins' menu in WordPress
3. Configure the plugin under settings => lndr, save configuration.

== Frequently asked questions ==

= How do I know if the module is working properly? =

There are some useful plugins to test if everything is working correctly.

https://wordpress.org/plugins/wp-crontrol/ (To see all of WordPress Cron jobs)
https://wordpress.org/plugins/monkeyman-rewrite-analyzer/ (To ensure web service endpoints rewrite rule is in place)
https://wordpress.org/plugins/debug-bar/ (For general debugging)

= I have everything setup correctly, however my page is not being imported into WordPress = 

If you do not see Lndr page published on your wordpress website. It might be because of the WordPress Cron (WP) cron and due to low traffic volume. For example, for WordPress sites hosted on WP engine, see https://wpengine.com/support/wp-cron-wordpress-scheduling/, by contacting support and enable "alternate cron" will resolve the issue

== Other usage notes ==

1. Your Wordpress website will automatically create Lndr pages for landing pages from Lndr that are published to the Wordpress domain name. The pages might not appear instantly on Wordpress (via Wordpress Cron). If you want to see it in Wordpress right away, please download the https://wordpress.org/plugins/wp-crontrol/ plugin and go to tools >> cron events (wp-admin/tools.php?page=crontrol_admin_manage_page) and click on "run now" next to the "lndr_cron" hook

2. You can find the list of Lndr landing pages published in Wordpress by visiting your Lndr pages section (wp-admin/edit.php?post_type=page)

== Screenshots ==

1. https://raw.githubusercontent.com/makehero/lndr_wordpress/master/screenshot1.png

== Changelog ==

1.0 Fully working version

== Upgrade notice ==

None