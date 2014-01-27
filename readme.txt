=== Google Authenticator by XLT ===
Contributors: XLT
Donate link: http://xlt.pl/googleauth.html
Tags: android, authentication, blackberry, iphone, login, otp, password, 2 factor authentication, 2FA, App, Authenticator, google, google authenticator, security
Requires at least: 3.6
Tested up to: 3.8
Stable tag: trunk
License: GPLv2

WordPress Auth using Google Authenticator is a simple plugin which allows authorization with Google Authenticator tokens.

== Description ==

WordPress Auth using Google Authenticator is a simple plugin which allows authorization with Google Authenticator tokens. Very easy to install and configure. 

= Plugin's Official Site =

WordPress Auth using Google Authenticator by XLT ([http://xlt.pl/googleauth.html](http://xlt.pl/googleauth.html))


== Installation ==

1. Login to WordPress admin panel, go to 'Plugins', next click 'Add New', choose 'Upload', click 'Browse' and find a xlt-totp-auth.zip and next click 'Upload Now'

2. If plugin is installed, remember to activate it.

3. Go to Settings >> XLT TOTP Auth and check if "Token authorization enabled" is checked.

4. Go to Users >> Your profile and scroll to the bottom of page and find XLT TOTP Auth section.

5. Check "Enabled TOTP Auth". "Secret code" will appear. Click "Generate new" and wait for new secret code. After generation click "Update profile".

6. Scan QR Code in your Google Authenticator application or enter "Secret code for Google Authenticator" code manually. You should also write this code in safe place. If you reinstall Google Authenticator software or loose your phone you will not be able to login.

7. Next time on login screen "Google Authenticator token" field will appear. Enter your login, password and generated code and login. If user has not enabled "TOTP Auth" just leave this field empty.

== Frequently Asked Questions ==

Q: What should I do if I lost my phone or reinstall Google Authenticator?<br/>
A: Probably the best way is to delete plugin folder using FTP or any file manager. After that plugin will not work.

== Screenshots ==

1. screenshot-1.png
2. screenshot-2.png

== Upgrade Notice ==

 No upgrades for now. Current version v1.0.

== Changelog == 

18/01/2014 – v1.0
