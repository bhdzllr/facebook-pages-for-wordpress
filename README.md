# Facebook Pages for WordPress #

With this plugin you can publish WordPress posts directly on a Facebook Page.
You need a [Facebook Developer](https://developers.facebook.com/) Account and
an [app](https://developers.facebook.com/apps/) for your Facebook Page.


## Installation ##

Install [Composer](https://getcomposer.org/) and run `composer install` to
install Facebook SDK for PHP into the "vendor" folder. Then upload to your
WordPress plugins folder ("wp-content/plugins/").


## Setup ##

### Facebook App ###

1. Create an app on Facebook
2. **Settings - Basic**
	* Display Name: Choose app name
	* App Domains: Add domain with and without "www"
	* Privacy Policy and Terms of Service: Add URL
	* App Icon: Add 1024 x 1024 px icon
	* Category: Select category
	* Business: Choose what matches app
	* Site URL: Full site URL e. g. "https://www.example.com/"
3. **Settings - Advanced**
	* Native or desktop app: No
	* API Version: 3.0
	* App Restrictions: Choose what is needed
	* Security: Choose what is needed, e. g. Server IP for whitelist
	* Require App Secret: Yes
	* Allow API Access to App Settings: No
	* Require 2-Factor Reauthorization: No
	* Domain Manager: Full URL with Match Prefix, Prefetch HTML
	* Allow Cross Domain Share Redirects
	* Everything else remains empty
4. **Roles - Roles**
	* Add Users that need access to this app
5. **Review**
	* Do NOT make app public, it works for all users which are mentioned in
	  "Roles" if it is in development mode, no review is needed.
	* Permissions: publish_pages, manage_pages, email, default
6. **Products**
	* Add "Facebook Login"
	* Client OAuth Login: Yes
	* Web OAuth Login: Yes
	* Force Web OAuth Reauthentication: No
	* Use Strict Mode for Redirect URIs: Yes
	* Enforce HTTPS: Yes
	* Embedded Browser OAuth Login: No
	* Valid OAuth Redirect URIs: "https://www.example.com/wp-admin/options-general.php?page=fbpfwp"
	* Login from Devices: No
	* Everything else remains empty

### WordPress ###

After installation "Facebook Pages for Wordpress" is available from the
WordPress administration. The fields for App Id, App Secret and Page Id
(to be taken from the created Facebook app) must be completed and then
an authentication must be done.


## Usage ##

Each post now has a checkbox "Post to Facebook Page" with which the posts can be
posted directly on the Facebook Page. Posts can also be scheduled up to 180 days
in the future (Facebook does not allow longer).

When removing the tick from the checkbox, the post will be deleted from
Facebook. When moving a post in WordPress to the trash, the post remains on
Facebook, when deleting it from WordPress, the post is also deleted from Facebook.


## Thanks ##

Thanks to "prunmit" for the (tips in his post)[https://wordpress.stackexchange.com/questions/195486/allow-facebook-to-preview-posts-before-published]
with the Facebook bots for scheduled posts.
