# EU cookie directive bar

<<<<<<< HEAD
### v0.2.1
=======
### v0.1.1
>>>>>>> 72ada220da44935bebc94431842bb78421fbf2b3

This very simple, lightweight & responsive javascript UI component aims to add a small top bar on your website to display a custom message to your visitors, in compliance with the [European Union Directive about cookies and privacy](http://ec.europa.eu/ipg/basics/legal/cookies/index_en.htm).

This component provides only a warning to visitors. Ideally it should contain a link to your Terms or Privacy page with instructions on what are cookies, and how to prevent cookies to be installed on the user's browser. 

The EU directive allows for this kind of "only warning and instructions" versions **(this component DOES NOT blocks incoming cookies)**, which are widely used by various high-traffic, well-recognized websites, such as [Adobe](http://www.adobe.com/fr/), [Le Monde](http://www.lemonde.fr) or [SoundCloud](http://www.soundcloud.com).

<<<<<<< HEAD

## Usage

Upload the folder anywhere on the internet, but preferably on one of your webserver. Replace the options in the code below with your context.

Parameters 4 through 8 might need to be changed to accomodate your configuration : 

- **4** : `(string)` replace `//cdn.example.org` with the domain where the folder is uploaded. The `//` before the URL insure the content will be loaded asynchronously AND using http or https depending on what's available.
- **5** : `(string)` except if you have changed the structure of the folders, you shouldn't have to change this.
- **6** : `(string)` change `subdomain.example.org` with the domain or subdomain on which you want the cookie to be applied (it should probably be the domain on which you are loading the bar).
- **7** : `(string) OR (object/json)`you can add another, optional 7th option, that many will probably use, in order to completely customize the displayed text. It can handle any HTML code. It can also be a JSON object which will change only the specified parts of the default text content :
    -  `text` : the content of the text before the link
    - `href` : the URL of the link
    - `linktext` : the link text content
- **8** : `(object/json)` an 8th argument is a JSON object for the colors of the cookie-bar. It recognizes 5 different properties, and each property accept any valid rgb, rgba, hexadecimal ( 3 or 6 ) or webcolor name value :
    - `link` : the color of the links in the cookie-bar and also of the "x" symbol to close it
    - `hover` : the color of the links and "x" symbol when the cursor is over it
    - `color` : the color of the text
    - `border` : the bottom border color
    - `background` : the background color of the bar
=======
**Demo is available at [github.io](http://sebastien-bartoli.github.io/eu-cookie/)**

You can see on the demo, that the bar appears at each reload. That is because the cookie is getting deleted each time so you can see how it works, on production website, once accepted ( clicked on the link or on the X symbol ), it won't appear for a year or until the user deletes cookies.

## Usage

Upload the folder anywhere on the internet, but preferably on one of your webserver. Replace the options in `index.html` (see below) with your context. 

Parameters 4 through 7 will need to be changed to accomodate your configuration : 

- **4** : replace `//cdn.example.org` with the domain where the folder is uploaded. The `//` before the URL insure the content will be loaded asynchronously AND using http or https depending on what's available.
- **5** : except if you have changed the structure of the folders, you shouldn't have to change this.
- **6** : change `subdomain.example.org` with the domain or subdomain on which you want the cookie to be applied (it should probably be the domain on which you are loading the bar).
- **7** : you can add another, optional 7th option, that many will probably use, in order to completely customize the displayed text. It can handle any HTML code, so have fun.
>>>>>>> 72ada220da44935bebc94431842bb78421fbf2b3

```HTML
<!-- EU Cookie Directive Bar loads here  -->
<script type="text/javascript">
<<<<<<< HEAD
(function(s,g,d,f,x,l,o,a,de,r){
de=g.createElement(d);r=g.getElementsByTagName(d)[0];de.async=1;de.src=f+x;r.parentNode.insertBefore(de,r);s.cmDomain=l;s.cmCDN=f;s.cmTextContent=o;s.cmColor=a})
(window, document,'script','//cdn.example.org/', 'js/cookie-manager.js', 'www.example.org' [, mixed [, object/json ]] );
=======
  (function(eu,c,o,ok,i,e,law,loa,de,r){
    de=c.createElement(o);r=c.getElementsByTagName(o)[0];de.async=1;de.src=ok+i;
    r.parentNode.insertBefore(de,r);eu.cmDomain=e;eu.cmCDN=ok;eu.cmTextContent=law
  })(window,document,'script','//cdn.example.org/','/js/cookie-manager.js','subdomain.example.org');
>>>>>>> 72ada220da44935bebc94431842bb78421fbf2b3
</script>
<!-- End of EU Cookie Directive Bar -->
```

<<<<<<< HEAD
Then, copy-paste the modified code source anywhere on your website (preferably inside the footer), and *voila!* You're done.
=======
Then, copy-paste the modified content of `index.html` anywhere on your website, and *voila!* You're done.
>>>>>>> 72ada220da44935bebc94431842bb78421fbf2b3

## Dependencies

- [jQuery 1.11.1](http://jquery.com)
- [jquery.cookie.js](https://github.com/carhartl/jquery-cookie)
- [Foundation Icons Fonts 3](http://zurb.com/playground/foundation-icon-fonts-3)

All are included into the repo and loaded automagically by the module without need for intervention. jQuery is loaded only if it isn't available on the hosting website.

## Changelog

<<<<<<< HEAD
#### v0.2 => v0.2.1

- the 7th text argument can now be a json object to change just part of the default text
- checks if the jQuery version on the page is supported, if not load another jquery version anyway

#### v0.1.1 => v0.2

- accept an optional 8th argument with the colors properties


=======
>>>>>>> 72ada220da44935bebc94431842bb78421fbf2b3
#### v0.1 => v0.1.1

- fixed Internet Explorer not displaying dynamically loading stylesheets 

## Roadmap

Next steps for this :

- use & configure bower to install dependencies and remove them from the repo
<<<<<<< HEAD
=======
- add an 8th parameter to change the colors of the cookie-bar easily without having to go in the css file
>>>>>>> 72ada220da44935bebc94431842bb78421fbf2b3
- find a way to block cookies being set on the browser until this is validated *(is there a need for that ?)*

## License

The MIT License (MIT)

Copyright (c) 2014 Sébastien Bartoli

<<<<<<< HEAD
Permission is hereby granted, free of charge, to any person obtaining a copy of this software and associated documentation files (the "Software"), to deal in the Software without restriction, including without limitation the rights to use, copy, modify, merge, publish, distribute, sublicense, and/or sell copies of the Software, and to permit persons to whom the Software is furnished to do so, subject to the following conditions:

The above copyright notice and this permission notice shall be included in all copies or substantial portions of the Software.

THE SOFTWARE IS PROVIDED "AS IS", WITHOUT WARRANTY OF ANY KIND, EXPRESS OR IMPLIED, INCLUDING BUT NOT LIMITED TO THE WARRANTIES OF MERCHANTABILITY, FITNESS FOR A PARTICULAR PURPOSE AND NONINFRINGEMENT. IN NO EVENT SHALL THE AUTHORS OR COPYRIGHT HOLDERS BE LIABLE FOR ANY CLAIM, DAMAGES OR OTHER LIABILITY, WHETHER IN AN ACTION OF CONTRACT, TORT OR OTHERWISE, ARISING FROM, OUT OF OR IN CONNECTION WITH THE SOFTWARE OR THE USE OR OTHER DEALINGS IN THE SOFTWARE.
=======
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
>>>>>>> 72ada220da44935bebc94431842bb78421fbf2b3
