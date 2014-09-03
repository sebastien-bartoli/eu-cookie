# EU Cookie Directive Bar

This very simple and lightweight javascript module aims to add a small top bar on your website to display a custom message to your visitors, in compliance with the European Union Directive about cookies and privacy.

## Usage

Upload the folder anywhere on the internet, but preferably on one of your webserver. Replace the options in index.html with your context. 

Only options 4 through 7 will need to be changed : 

- 4 : replace "//cdn.example.org" with the domain where the folder is uploaded. The "//" before the URL insure the content will be loaded asynchronously AND using http or https depending on what's available.
- 5 : except if you have changed the structure of the folder, you shouldn't have to change this.
- 6 : change "subdomain.example.org" with the domain or subdomain on which you want the cookie to be applied (it should probably be the domain on which you are loading the bar).
- 7 : you can add another, optional 7th option, that many will probably use, in order to completely customise the displayed text. It can handle any HTML code, so have fun.

Then, copy-paste the modified content of index.html anywhere on your website, and voila! You're done.

## Dependencies

The module is using jQuery 1.11.1 and jquery.cookie.js, both are loaded by the module without need for intervention. jQuery is loaded only if the hosting website isn't available.
