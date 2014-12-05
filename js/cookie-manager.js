<<<<<<< HEAD
/* Installs with

<!-- CNIL Required CookieBar loads here -->
<script type="text/javascript">
(function(s,g,d,f,x,l,o,a,de,r){de=g.createElement(d);r=g.getElementsByTagName(d)[0];de.async=1;de.src=f+x;r.parentNode.insertBefore(de,r);s.cmDomain=l;s.cmCDN=f;s.cmTextContent=o;s.cmColor=a})(window, document,'script','//ws.sgdf.fr/cdn/', '/js/cookie-manager.js', 'www.sgdf.fr', null, '#ff7f00');
</script>
<!-- End of the CNIL Required CookieBar -->

*/


function main($j){
	$j.getScript( window.cmCDN + '/js/jquery.cookie.js', function(){
		if( typeof($j.cookie('cnil')) === "undefined" ){
			
			var cbhtml = $j(document.createElement('div')).attr('style', 'display:none;').addClass('cookie-bar disappear').append($j(document.createElement('div')).addClass('cookie-bar-content')).append($j(document.createElement('i')).addClass('fi-x')).append($j(document.createElement('div')).addClass('clear'));

=======
function main($){
	$.getScript( window.cmCDN + '/js/jquery.cookie.js', function(){
		if( typeof($.cookie('eu-cookie-directive')) === "undefined" ){
			
			var cbhtml = $(document.createElement('div')).attr('style', 'display:none').addClass('cookie-bar disappear').append($(document.createElement('div')).addClass('cookie-bar-content')).append($(document.createElement('i')).addClass('fi-x')).append($(document.createElement('div')).addClass('clear'));
			
>>>>>>> 72ada220da44935bebc94431842bb78421fbf2b3
			if( document.createStyleSheet ){
				document.createStyleSheet(window.cmCDN + '/css/cookie-manager.css');
				document.createStyleSheet(window.cmCDN + '/css/foundation-icons.css');
			} else {
<<<<<<< HEAD
				var csshtml = $j(document.createElement('link')).attr({rel: 'stylesheet', type: 'text/css', href: window.cmCDN + '/css/cookie-manager.css'});

				var ficsshtml = $j(document.createElement('link')).attr({rel: 'stylesheet', type: 'text/css', href: window.cmCDN + '/css/foundation-icons.css'});

				$j("head").append(csshtml).append(ficsshtml);
			}

			$j(document).scroll(function(){
				evalScrollPosition($j);
			});
			
			$j(document).on('click', '.cookie-bar i, .cookie-bar a', function(e){		
				if($j(e.target).is('i')){
					if(document.createStyleSheet){
						$j('.cookie-bar').hide();
					} else {
						$j('.cookie-bar').addClass('disappear');	
					}		
					
				}		
				$j.cookie('cnil', 'accepted', {expires: 365, path: '/', domain: window.cmDomain});
			});

			var wcmTC = (typeof(window.cmTextContent) !== "undefined")? true : false ;
			
			var cmText = ( wcmTC && typeof(window.cmTextContent.text) !== "undefined") && 
								window.cmTextContent.text || 
								"En poursuivant votre navigation sur ce site, vous acceptez l’utilisation de cookies, notamment à des fins de mesure d'audience et de partage de contenu sur les réseaux sociaux.";
			
			var cmHref = ( wcmTC && typeof(window.cmTextContent.href) !== "undefined" ) && 
								window.cmTextContent.href || 
								"/mentions-legales#utilisation-cookies";
			
			var cmLinkText = ( wcmTC && typeof(window.cmTextContent.linktext) !== "undefined" ) &&
									window.cmTextContent.linktext || 
									"En savoir plus et gérer ces paramètres.";
			
			var cmLink = " <a href='{href}'>{text}</a>";
			cmLink = cmLink .replace('{href}', cmHref)
							.replace('{text}', cmLinkText);

			window.cmTextContent = cmText + cmLink ;

			$j(cbhtml).find('.cookie-bar-content').html(window.cmTextContent);

			$j("body").prepend(cbhtml);
			evalScrollPosition($j);
			var tol = (loadjQuery == 'yep')? 50 : 2000 ;
			window.setTimeout(function(){
				$j('.cookie-bar').show(0);
				if ( typeof(window.cmColor) !== "undefined" ){
					changeColors($j);
				}
				window.setTimeout(function(){
					$j('.cookie-bar').removeClass('disappear');
				}, 500);
			}, tol);
		} 
	});
}

function changeColors($j){
	var newcss = ".cookie-bar{ color: {color} ; border-bottom-color: {border}; background: {background}; } .cookie-bar a{ color: {linkColor}; border-bottom-color: {linkColor};} .cookie-bar a, .cookie-bar i{ color: {linkColor};} .cookie-bar a:hover{ border-bottom-color: {hoverColor};} .cookie-bar a:hover, .cookie-bar i:hover{ color: {hoverColor};}";

	for(var prop in window.cmColor){
		
		// Check if the property is a valid color in rgb, rgba, hex3, hex6 or webcolor name
		if(/^(rgba?\(((25[0-5]|2[0-4][0-9]|1[0-9][0-9]|[1-9][0-9]|[0-9]),?){3}(0?\.[0-9]{1,2}|1)?\)|(#?[a-f0-9]{3}([a-f0-9]{3})?)|(\w{3,20}))$/i.test(window.cmColor[prop])){
			switch(prop){
				case 'link':
					newcss = newcss.replace(/\{linkColor\}/g, window.cmColor[prop]);
				break ;
				case 'hover':
					newcss = newcss.replace(/\{hoverColor\}/g, window.cmColor[prop]);
				break ;
				case 'color':
					newcss = newcss.replace(/\{color\}/g, window.cmColor[prop]);
				break ;
				case 'border':
					newcss = newcss.replace(/\{border\}/g, window.cmColor[prop]);
				break ;
				case 'background':
					newcss = newcss.replace(/\{background\}/g, window.cmColor[prop]);
				break ;
			}
		} 
	}

	newcss = newcss	.replace(/\{color\}/g, '')
					.replace(/\{border\}/g, '')
					.replace(/\{background\}/g, '')
					.replace(/\{linkColor\}/g, '')
					.replace(/\{hoverColor\}/g, '');
	var colorCss = $j(document.createElement('style')).html(newcss);
	$j("head").append(colorCss);
}

function evalScrollPosition($j){
	if($j(document).scrollTop() > $j('.cookie-bar').outerHeight(true) && !$j('.cookie-bar').hasClass('cfixed')){	
		$j('.cookie-bar').addClass('cfixed');
	}
	
	if($j(document).scrollTop() <= $j('.cookie-bar').outerHeight(true) && $j('.cookie-bar').hasClass('cfixed')){		
		$j('.cookie-bar').removeClass('cfixed');	
=======
				var csshtml = $(document.createElement('link')).attr({rel: 'stylesheet', type: 'text/css', href: window.cmCDN + '/css/cookie-manager.css'});

				var ficsshtml = $(document.createElement('link')).attr({rel: 'stylesheet', type: 'text/css', href: window.cmCDN + '/css/foundation-icons.css'});

				$("head").append(csshtml).append(ficsshtml);
			}
									
			$(document).scroll(function(){
				evalScrollPosition($);
			});
			
			$(document).on('click', '.cookie-bar i, a', function(e){		
				if($(e.target).is('i')){			
					if(document.createStyleSheet){
						$('.cookie-bar').hide();
					} else {
						$('.cookie-bar').addClass('disappear');	
					}		
				}		
				$.cookie('eu-cookie-directive', 'accepted', {expires: 365, path: '/', domain: window.cmDomain});
			});

			if( typeof(window.cmTextContent) === "undefined" ){
				window.cmTextContent = "By continuing your visit to this site, you accept the use of cookies, including for audience measurement and content sharing on social networks <a href='/privacy#use-of-cookies'>Read more and manage these settings.</a>" ;
			}

			$(cbhtml).find('.cookie-bar-content').html(window.cmTextContent);
			
			$("body").prepend(cbhtml);
			evalScrollPosition($);
			var tol = (loadjQuery == 'yep')? 50 : 2000 ;
			window.setTimeout(function(){
				$('.cookie-bar').attr('style', '');
				window.setTimeout(function(){
					$('.cookie-bar').removeClass('disappear');
				}, 500);
			}, tol);
		}
	});
}

function evalScrollPosition($){
	if($(document).scrollTop() > $('.cookie-bar').outerHeight(true) && !$('.cookie-bar').hasClass('cfixed')){	
		$('.cookie-bar').addClass('cfixed');
	}
	
	if($(document).scrollTop() <= $('.cookie-bar').outerHeight(true) && $('.cookie-bar').hasClass('cfixed')){		
		$('.cookie-bar').removeClass('cfixed');	
>>>>>>> 72ada220da44935bebc94431842bb78421fbf2b3
	}
}

function loadDependencies(){
<<<<<<< HEAD
	dependencyCalled = true ;
=======
>>>>>>> 72ada220da44935bebc94431842bb78421fbf2b3
	window.clearTimeout(loadjQuery);
	var jqhtml = document.createElement('script');
	jqhtml.async = 1 ;
	jqhtml.src = window.cmCDN + '/js/jquery-1.11.1.min.js' ;
	var el = document.getElementsByTagName('script')[0];
	el.parentNode.insertBefore(jqhtml, el);
}

<<<<<<< HEAD
function isJqueryVersionSupported(required){
	var current = window.jQuery.fn.jquery.split('.') ;
	required = required.split('.');
	return ((parseInt(current[0]) >= parseInt(required[0])) && 
			(parseInt(current[1]) >= parseInt(required[1])))? true : false ;
}

function startScript(){
	if(typeof(window.jQuery) === "function" ){
		if( !isJqueryVersionSupported("1.7") ){
			if( typeof(dependencyCalled) === "undefined" ){
				loadDependencies();
			}
			checkjQuery = window.setTimeout(startScript, 50);
			return false ;
		}
		window.clearTimeout(checkjQuery);
		window.clearTimeout(loadjQuery);
		checkjQuery = null ;
		loadjQuery = 'yep' ;
		if(dependencyCalled){
			jQuery.noConflict();
		}
		main(window.jQuery);
=======
function startScript(){
	if(typeof(window.jQuery) === "function"){
		window.clearTimeout(checkjQuery);
		window.clearTimeout(loadjQuery);
		delete loadjQuery ;
		delete checkjQuery ;
		loadjQuery = 'yep' ;
		main(jQuery);
>>>>>>> 72ada220da44935bebc94431842bb78421fbf2b3
	} else {
		if( loadjQuery != 'yep' ){
			checkjQuery = window.setTimeout(startScript, 50);
		}	
	}
}

<<<<<<< HEAD
var checkjQuery, loadjQuery, dependencyCalled;
=======
var checkjQuery, loadjQuery;
>>>>>>> 72ada220da44935bebc94431842bb78421fbf2b3
loadjQuery = window.setTimeout(loadDependencies, 7000);
startScript();