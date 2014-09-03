# EU Cookie Directive Bar

This very simple and lightweight javascript module aims to add a small top bar on your website to display a custom message to your visitors, in compliance with the [European Union Directive about cookies and privacy](http://ec.europa.eu/ipg/basics/legal/cookies/index_en.htm).

This module provides only a warning to visitors. Ideally it should contain a link to your Terms or Privacy page with instructions on what are cookies, and how to prevent cookies to be installed on your website. 

The directive allows for this kind of "only warning and instructions" versions (in any case this module blocks incoming cookies), which are widely used by various high-traffic, well-recognized websites.

**Demo is available at [github.io](http://sebastien-bartoli.github.io/eu-cookie/)**

## Usage

Upload the folder anywhere on the internet, but preferably on one of your webserver. Replace the options in `index.html` (see below) with your context. 

Only options 4 through 7 will need to be changed : 

- **4** : replace `//cdn.example.org` with the domain where the folder is uploaded. The `//` before the URL insure the content will be loaded asynchronously AND using http or https depending on what's available.
- **5** : except if you have changed the structure of the folder, you shouldn't have to change this.
- **6** : change `subdomain.example.org` with the domain or subdomain on which you want the cookie to be applied (it should probably be the domain on which you are loading the bar).
- **7** : you can add another, optional 7th option, that many will probably use, in order to completely customize the displayed text. It can handle any HTML code, so have fun.

```HTML
<!-- EU Cookie Directive Bar loads here  -->
<script type="text/javascript">
  (function(eu,c,o,ok,i,e,law,loa,de,r){
    de=c.createElement(o);r=c.getElementsByTagName(o)[0];de.async=1;de.src=ok+i;
    r.parentNode.insertBefore(de,r);eu.cmDomain=e;eu.cmCDN=ok;eu.cmTextContent=law
  })(window,document,'script','//cdn.example.org/','/js/cookie-manager.js','subdomain.example.org');
</script>
<!-- End of EU Cookie Directive Bar -->
```

Then, copy-paste the modified content of `index.html` anywhere on your website, and voila! You're done.

## Dependencies

- [jQuery 1.11.1](http://jquery.com)
- [jquery.cookie.js](https://github.com/carhartl/jquery-cookie)
- [Foundation Icons Fonts 3](http://zurb.com/playground/foundation-icon-fonts-3)

All are included into the repo and loaded automagically by the module without need for intervention. jQuery is loaded only if the hosting website isn't available.

## License

The MIT License (MIT)

Copyright (c) 2014 Sébastien Bartoli

Permission is hereby granted, free of charge, to any person obtaining a copy
of this software and associated documentation files (the "Software"), to deal
in the Software without restriction, including without limitation the rights
to use, copy, modify, merge, publish, distribute, sublicense, and/or sell
copies of the Software, and to permit persons to whom the Software is
furnished to do so, subject to the following conditions:

The above copyright notice and this permission notice shall be included in all
copies or substantial portions of the Software.

THE SOFTWARE IS PROVIDED "AS IS", WITHOUT WARRANTY OF ANY KIND, EXPRESS OR
IMPLIED, INCLUDING BUT NOT LIMITED TO THE WARRANTIES OF MERCHANTABILITY,
FITNESS FOR A PARTICULAR PURPOSE AND NONINFRINGEMENT. IN NO EVENT SHALL THE
AUTHORS OR COPYRIGHT HOLDERS BE LIABLE FOR ANY CLAIM, DAMAGES OR OTHER
LIABILITY, WHETHER IN AN ACTION OF CONTRACT, TORT OR OTHERWISE, ARISING FROM,
OUT OF OR IN CONNECTION WITH THE SOFTWARE OR THE USE OR OTHER DEALINGS IN THE
SOFTWARE.
