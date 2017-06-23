This plugin is created to enable Lndr landing page builder to be on Wordpress based website. For more information, please visit Lndr at http://alpha.makehero.co. The plugin is developed and tested for Wordpress 4.7 or later

*******************************
* Setup and configuration
*******************************
1. Enable the plugin "Lndr page builder" under plugins page
2. Go to settings => Lndr or (wp-admin/options-general.php?page=lndr) and enter your Lndr API token. You will be able to obtain the API token from your Lndr user account (under user profile)

*******************************
* Usage
*******************************
1. Your Wordpress website will automatically create Lndr pages for landing pages from Lndr that are published to the Wordpress domain name. The pages might not appear instantly on Wordpress (via Wordpress Cron). If you want to see it in Wordpress right away, please download the https://wordpress.org/plugins/wp-crontrol/ plugin and go to tools >> cron events (wp-admin/tools.php?page=crontrol_admin_manage_page) and click on "run now" next to the "lndr_cron" hook

2. You can find the list of Lndr landing pages published in Wordpress by visiting your Lndr pages section (wp-admin/edit.php?post_type=page)

*******************************
* Development notes
*******************************
There are some useful plugins to test if everything is working correctly.

https://wordpress.org/plugins/wp-crontrol/ (To see all of Wordpress Cron jobs)
https://wordpress.org/plugins/monkeyman-rewrite-analyzer/ (To ensure web service endpoints rewrite rule is in place)
https://wordpress.org/plugins/debug-bar/ (For general debugging)

*******************************
* Hosting notes
*******************************
If you do not see Lndr page published in your wordpress website. It might be because of the Wordpress Cron (WP) cron
For WP engine, see https://wpengine.com/support/wp-cron-wordpress-scheduling/, by contacting support and enable "alternate cron" will resolve the issue

