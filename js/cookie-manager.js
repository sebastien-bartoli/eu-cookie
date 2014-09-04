function main($){
	$.getScript( window.cmCDN + '/js/jquery.cookie.js', function(){
		if( typeof($.cookie('eu-cookie-directive')) === "undefined" ){
			
			var cbhtml = $(document.createElement('div')).attr('style', 'display:none').addClass('cookie-bar disappear').append($(document.createElement('div')).addClass('cookie-bar-content')).append($(document.createElement('i')).addClass('fi-x')).append($(document.createElement('div')).addClass('clear'));
			
			if( document.createStyleSheet ){
				document.createStyleSheet(window.cmCDN + '/css/cookie-manager.css');
				document.createStyleSheet(window.cmCDN + '/css/foundation-icons.css');
			} else {
				var csshtml = $(document.createElement('link')).attr({rel: 'stylesheet', type: 'text/css', href: window.cmCDN + '/css/cookie-manager.css'});

				var ficsshtml = $(document.createElement('link')).attr({rel: 'stylesheet', type: 'text/css', href: window.cmCDN + '/css/foundation-icons.css'});

				$("head").append(csshtml).append(ficsshtml);
			}
									
			$(document).scroll(function(){
				evalScrollPosition($);
			});
			
			$(document).on('click', '.cookie-bar i, a', function(e){		
				if($(e.target).is('i')){			
					$('.cookie-bar').addClass('disappear');	
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
	}
}

function loadDependencies(){
	window.clearTimeout(loadjQuery);
	var jqhtml = document.createElement('script');
	jqhtml.async = 1 ;
	jqhtml.src = window.cmCDN + '/js/jquery-1.11.1.min.js' ;
	var el = document.getElementsByTagName('script')[0];
	el.parentNode.insertBefore(jqhtml, el);
}

function startScript(){
	if(typeof(window.jQuery) === "function"){
		window.clearTimeout(checkjQuery);
		window.clearTimeout(loadjQuery);
		delete loadjQuery ;
		delete checkjQuery ;
		loadjQuery = 'yep' ;
		main(jQuery);
	} else {
		if( loadjQuery != 'yep' ){
			checkjQuery = window.setTimeout(startScript, 50);
		}	
	}
}

var checkjQuery, loadjQuery;
loadjQuery = window.setTimeout(loadDependencies, 7000);
startScript();