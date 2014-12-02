var Map, Infowindow, Geo = null ;
var Data = {};

// Differents kinds of MapDisplays are :
//		- "groupes", the default type, displays the SGDF groups on a map
//		- "bethleem", displays the events from the "Lumière de Bethleem" event
//		- "education-internationale", displays all the scouting NSO/NSA/MO on a map, with data from the international department
//		- "benevolat", displays the SGDF groups with contact for the DT and email form
var MapType = window.googleMapType || "groupes" ;

function sgdfmap_initialize(){ // Called by the google maps script injection
	var myOptions = {
	 	zoom: 5,
	 	mapTypeControlOptions: {
	 		mapTypeIds: [google.maps.MapTypeId.ROADMAP, 'SGDF']
	 	},
	 	mapTypeId: 'SGDF',
		center: new google.maps.LatLng(46.2, 2.2),
		disableDefaultUI : true,
		zoomControl : true,
		backgroundColor: '#ddd'
	};
	switch(MapType){
		case 'education-internationale':
			myOptions.zoom = 2 ;
			myOptions.center = new google.maps.LatLng(22.5,10);
			myOptions.minZoom = 2 ;
			myOptions.maxZoom = 7 ;
		break ;
	}
	Map = new google.maps.Map(document.getElementById("map_canvas"), myOptions);
	
	styleMap();
	loadMapData();
	switch(MapType){
		case 'education-internationale':
		break;
		default:
			geolocalise();
		break;
	}	
}

function putSearchFieldListener($){ // Called by loadjQuery function
	$(document).on('submit', 'input#addressSearch, form', function(event){
		event.stopPropagation();
		displayPostalCodeOnMap();
		return false ;
	});

	$(document).on('click', '.map-results form a[type=button]', function(event){
		event.stopPropagation();
		displayPostalCodeOnMap();
		return false ;
	});
}

function displayPostalCodeOnMap(){
	var codePostal = document.getElementById('addressSearch').value ;
	if( Geo === null ){
		Geo = new google.maps.Geocoder();
	}
	var GeoRequest = {
		address: codePostal,
		region: 'fr'
	};
	Geo.geocode(GeoRequest, function(results, status){
		if(status == google.maps.GeocoderStatus.OK){
            Map.panTo(results[0].geometry.location);
            Map.setZoom(12);
        }
	});
}

function integrateContent(params){
	// root, selector, value, hide, format, attribute
	if(params.root === "document"){
		params.root = $(document);
	}
	if(typeof(params.hide) === "undefined" || params.hide === null){
		params.hide = true ;
	}
	if(typeof(params.value) === "string"){
		params.value = params.value.replace(/(?:\r\n|\r|\n)/g, '<br />');
	}
	var element = params.root.find(params.selector);
	var defined = (typeof(params.value) === "undefined" || params.value === null || params.value.length === 0 || params.value === false)? false : true ;
	
	if(!defined && params.hide){
		if( params.selector.match(/.+(span|a)$/gi) ){
			element = element.parent();
		} else if ( params.selector.match(/\+\s(ul)/gi) ){
			element = element.prev();
		}
		return element.addClass('hide');
	} else {
		var attributeChange = (typeof(params.attribute) === "undefined" || params.attribute === null)? false : true ;
		var format = ( typeof(params.format) === "undefined" || params.format === null )? false : true ;
		if(attributeChange){
			return (format)? element.attr(params.attribute, params.format(params.value)) : element.attr(params.attribute, params.value);
		} else {
			return (format)? element.html(params.format(params.value)) : element.html(params.value);
		}
	}
}

function returnInfowindowContent(contentObject){
	var html = "" ;
	switch( MapType ){
		case "bethleem":
		break;
		case "education-internationale":
			var infos = contentObject.k ;
			var selected = window.selectedAsso ;
			var association = infos.associations[selected];
			console.log(infos);
			$ = window.jQuery ;
			html = $('#overlay_info');
			
			// Pays
			integrateContent({root: html, selector: '.countryName', value: infos.countryName});
			integrateContent({root: html, selector: '.countryPopulation span', value: infos.population});			
			integrateContent({root: html, selector: '.countryFederation span', value: infos.federation});
			integrateContent({root: html, selector: '.countrySummary', value: infos.securite});

			// AssoList
			var templateAsso = '<div class="fedeAsso" data-name="{assoName}"><div class="fedeAssoLogo"><img src="{assoLogo}"></div><div class="fedeAssoBadges assoBadges"><img src="//ws.sgdf.fr/cdn/images/wosm.svg" class="{OMMSHide} assoBadgeOMMS"><img src="//ws.sgdf.fr/cdn/images/wagggs.svg" class="assoBadgeAMGE {AMGEHide}"></div></div>';
			html.find('.fedeWrapper').html("");
			if( infos.associations.length > 0 ){
				$.each(infos.associations, function(i, asso){
					var AlogoURL = (asso.logo)? "//ws.sgdf.fr" + asso.logo.replace('./..', '') : '' ;
					var tAsso = templateAsso 	.replace('{assoName}', asso.name)
												.replace('{assoLogo}', AlogoURL)
												.replace('{OMMSHide}', asso.affiliation.some(function(el){ return el === "OMMS"})? '' : 'hide' )
												.replace('{AMGEHide}', asso.affiliation.some(function(el){ return el === "AMGE"})? '' : 'hide' );
					html.find('.fedeWrapper').append(tAsso);
				});
			} else {
				html.find('#federation_card').addClass('hide');
			}
			

			// Asso
			if( typeof(association) !== "undefined" ){
				integrateContent({root: html, selector: 'h2.assoName span', value: association.name});
				integrateContent({root: html, selector: '.assoLogo img', value: association.logo, attribute: "src", format : function(content){
					return "//ws.sgdf.fr" + content.replace('./..', '') ;
				}});
				integrateContent({root: html, selector: 'h2.assoName .assoBadgeOMMS', value: !association.affiliation.every(function(el){return el !== "OMMS"}) });
				integrateContent({root: html, selector: 'h2.assoName .assoBadgeAMGE', value: !association.affiliation.every(function(el){return el !== "AMGE"}) });
				integrateContent({root: html, selector: '.assoMembers span', value: association.members});
				integrateContent({root: html, selector: '.assoCreation span', value: association.creation});
				integrateContent({root: html, selector: '.assoBranches + ul', value: association.branches, format: function(content){
					var contenthtml = "";
					content.forEach(function(el){
						contenthtml += "<li>" + el + "</li>";
					});
					return contenthtml; 
				}});
			} else {
				html.find("#asso_card").addClass('hide');
			}

			// Liens
			integrateContent({root: html, selector: '.linksErascout span', value: infos.erascout});
			integrateContent({root: html, selector: '.linksVolontaires span', value: infos.volunteers});
			integrateContent({root: html, selector: '.linksProjects span', value: infos.projects});
			integrateContent({root: html, selector: '.linksCompaProjects span', value: infos.compaProjects});
			integrateContent({root: html, selector: '.linksPioKProjects span', value: infos.pioKProjects});
			integrateContent({root: html, selector: '.linksEvents span', value: infos.events});
			integrateContent({root: html, selector: '.linksArticles + ul', value: infos.articles, format: function(content){
				var contenthtml = "";
				content.forEach(function(el){
					contenthtml += "<li>" + "<a href='"+ el.link +"'>" + el.name + "</a>" + "</li>";
				});
				return contenthtml; 
			}});
			integrateContent({root: html, selector: '.linksTestimony + ul', value: infos.testimonies, format: function(content){
				var contenthtml = "";
				content.forEach(function(el){
					contenthtml += "<li>" + "<a href='"+ el.link +"'>" + el.name + "</a>" + "</li>";
				});
				return contenthtml; 
			}});
			integrateContent({root: html, selector: '.linksBases + ul', value: infos.bases, format: function(content){
				var contenthtml = "";
				content.forEach(function(el){
					contenthtml += "<li>" + "<a href='"+ el.link +"'>" + el.name + "</a>" + "</li>";
				});
				return contenthtml; 
			}});
			integrateContent({root: html, selector: '.linksGroups + ul', value: infos.groups, format: function(content){
				var contenthtml = "";
				content.forEach(function(el){
					contenthtml += "<li>" + el + "</li>";
				});
				return contenthtml; 
			}});

			// Contacts
			if(typeof(association) !== "undefined"){
				integrateContent({root: html, selector: '.contactWebsite a', value: association.website});
				integrateContent({root: html, selector: '.contactWebsite a', attribute: "href", value: association.website});
				var iconhtml = "<li><a href='{url}'><i class='icon-{reseau}'></i></a></li>";
				var fbhtml = "" ;
				if( association.facebook ){
					fbhtml = iconhtml.replace('{url}', function(){ return (association.facebook.match(/(facebook)\.(com)/gi))? association.facebook : 'https://facebook.com/' + association.facebook ; }).replace('{reseau}', 'facebook');
				}
				var twhtml = "" ;
				if( association.twitter ){
					twhtml = iconhtml.replace('{url}', function(){ return (association.twitter.match(/(twitter)\.(com)/gi))? association.twitter : "https://twitter.com/" + association.twitter.replace('@', '') }).replace('{reseau}', 'twitter');
				}
				integrateContent({root: html, selector: '.contactSocialNetworks', value: fbhtml+twhtml});
			} else {
				html.find(".contactWebsite, .contactSocialNetworks").addClass('hide');
			}
			integrateContent({root: html, selector: '.contactName', value: function(){ return (infos.contact)? infos.contact.name : null ; }});
			integrateContent({root: html, selector: '.contactEmail a', value: function(){ return (infos.contact)? infos.contact.name : null ; }});
			integrateContent({root: html, selector: '.contactEmail a', attribute: "href", value:  function(){ return (infos.contact)? infos.contact.email : null ; }});
			integrateContent({root: html, selector: '.contactPhone span', value:  function(){ return (infos.contact)? infos.contact.phone : null ; }});

			$.each($('#overlay_info > .card:not(.hide)'), function(i, card){
				var allHidden = true ;	
				$.each( $(card).children("p"), function(i, value){ 
					if( !$(value).hasClass('hide') && $(value).html().length > 0){ 
						allHidden = false; 
					}
				}); 
				if(allHidden && $(card).children("p").length > 0){ 
					if( $(card).is('#asso_card') && typeof(association.name) !== "undefined" &&association.name.length > 0 ){
						// DO NOTHING
					} else {
						$(card).addClass('hide'); 
					}
				}
			});

		default:
			var templatehtml = "<div><table border='0' cellpadding='0' cellspacing='0'><tbody><tr><td><h1>{nom}</h1></td></tr>{trTelephone}{trCourriel}{trWeb}</tbody></table><br />Voir aussi :<table border='0' cellpadding='0' cellspacing='0'><tbody><tr><td><h1>{nomCR}</h1></td></tr>{trTelephoneCR}{trCourrielCR}</tbody></table></div>";
			
			var templateTelephone = "<tr><td>Tel : {telephone}</td></tr>";
			var templateCourriel = "<tr><td><a href='mailto:{courriel}'>Contactez-les par courriel</a></td></tr>";
			var templateWeb = "<tr><td><a href='{web}'>Visitez le site internet</a></td></tr>";

			html = templatehtml .replace('{nom}', contentObject.nom)
								.replace('{trTelephone}', function(){
									return 	(typeof(contentObject.telephone) !== "undefined" && 
											!!contentObject.telephone )? 
											templateTelephone.replace('{telephone}', contentObject.telephone) 
											: "";
								})
								.replace('{trCourriel}', function(){
									return 	(typeof(contentObject.courriel) !== "undefined" && 
											!!contentObject.courriel )? 
											templateCourriel.replace('{courriel}', contentObject.courriel) 
											: "";
								})
								.replace('{trWeb}', function(){
									return 	(typeof(contentObject.web) !== "undefined" && 
											!!contentObject.web )? 
											templateWeb.replace('{web}', contentObject.web) 
											: "";
								})
								.replace('{nomCR}', contentObject.nom_cr)
								.replace('{trTelephoneCR}', function(){
									return 	(typeof(contentObject.telephone_cr) !== "undefined" && 
											!!contentObject.telephone_cr )? 
											templateTelephone.replace('{web}', contentObject.telephone_cr) 
											: "";
								})
								.replace('{trCourrielCR}', function(){
									return 	(typeof(contentObject.courriel_cr) !== "undefined" && 
											!!contentObject.courriel_cr )? 
											templateCourriel.replace('{web}', contentObject.courriel_cr) 
											: "";
								});
		return html ;
	}
	
}

function putEventListenerOnMap($){ // Called by loadjQuery function
	window.clearTimeout(mapDataLoaded);
	mapDataLoaded = null ;
	if( Map !== null && typeof( Data[MapType] ) !== "undefined" ){

		switch( MapType ){
			case "bethleem":
			break;
			case "education-internationale":
				
				$.getScript(window.gmCDN + 'js/jquery.masonry.min.js', function(){
					
					Data[MapType].addListener("click", function(event){
						if(Data['sgdf-international'].getFeatureById(event.feature.k.ISO_A2)){
							window.CountryData = event.feature.k ;
							window.selectedAsso = 0 ;

							if($('#map_overlay').hasClass('moving')){
								console.warn("delayed");
								return window.setTimeout(google.maps.event.trigger, 50, Data[MapType], "click", event);
							}



							returnInfowindowContent(Data['sgdf-international'].getFeatureById(window.CountryData.ISO_A2));

							$('#map_overlay')	.queue(function(){
													$('#overlay_info').masonry({
														itemSelector: '.card',
														containerStyle: null
													});
													$('#overlay_info .card').animate({top:'+=550'},0);
													$(this).dequeue();
												}).delay(300)
												.queue(function(){
													$(this).removeClass('hidden');
													$(this).dequeue();
												}).delay(800)
												.queue(function(){
													$('#overlay_info .card').each(function(i){
														$(this).delay(200*i).animate({top: '-=550'}, 500);
													});
													$(this).dequeue();
												}).delay(1200)
												.queue(function(){
													$('.overlay_close').animate({bottom: '0'}, 300);
													$(this).dequeue();
												});
						}
					});

					$(document).on('click', '.overlay_close', function(event){
						
						$('#map_overlay')	.queue(function(){
												$('#map_overlay').addClass('moving');
												$('.overlay_close').animate({bottom: '-3em'}, 300);
												$(this).dequeue();
											}).delay(600)
											.queue(function(){
												$($('#overlay_info .card').get().reverse()).each(function(i){
													$(this).delay(200*i).animate({top: '+=550'}, 500);
												});
												$(this).dequeue();
											}).delay(1200)
											.queue(function(){
												$(this).addClass('hidden');
												$(this).dequeue();
											}).delay(800)
											.queue(function(){
												$("#map_overlay").find('.infoHidden').removeClass('infoHidden');
												$("#map_overlay").find('.hide').removeClass('hide');
												$("#map_overlay").find('ul').html("");
												$('#overlay_info').masonry('destroy');
												$('#map_overlay').removeClass('moving');
												$(this).dequeue();
											});
					});

					$(document).on('click', '.fedeAsso', function(event){
						window.selectedAsso = $(this).index();
						$('#map_overlay')	.queue(function(){
												$($('#overlay_info .card').get().reverse()).each(function(i){
													$(this).delay(200*i).animate({top: '+=550'}, 500);
												});
												$('.fede_overlay').removeClass('show');
												$(this).dequeue();
											}).delay(1200)
											.queue(function(){
												$("#map_overlay").find('.infoHidden').removeClass('infoHidden');
												$("#map_overlay").find('.hide').removeClass('hide');
												$("#map_overlay").find('ul').html("");
												$('#overlay_info').masonry('destroy');
												$('.card').css({'opacity': 0});
												$(this).dequeue();
											}).delay(300)
											.queue(function(){
												returnInfowindowContent(Data['sgdf-international'].getFeatureById(window.CountryData.ISO_A2));
												$(this).dequeue();
											}).delay(100)
											.queue(function(){
												$('#overlay_info').masonry({
													itemSelector: '.card',
													containerStyle: null
												});
												$('#overlay_info .card').animate({top:'+=550'},0);
												$('.card').css({'opacity': 1});
												$(this).dequeue();
											}).delay(100)
											.queue(function(){
												$('#overlay_info .card').each(function(i){
													$(this).delay(200*i).animate({top: '-=550'}, 500);
												});
												$(this).dequeue();
											});
					});

					$(document).on("mouseenter", ".fedeAsso", function(event){
						$('.fede_overlay').html($(this).data('name')).addClass('show');
					});

					$(document).on("mouseleave", ".fedeAsso", function(event){
						$('.fede_overlay').html("").removeClass('show');
					});
				});

				google.maps.event.addListenerOnce(Data[MapType], 'addfeature', function(event){
					$('#map_loader').delay(1500).fadeOut(255);
					loadEIJSONData($);
				});

				Data[MapType].addListener('mouseover', function(event){
					Data[MapType].overrideStyle(event.feature, {
						fillColor: "#00A89D",
						fillOpacity: .8,
						strokeWeight: 2,
						strokeColor: "white"
					});					
				});

				Data[MapType].addListener('mouseout', function(event){
					Data[MapType].revertStyle(event.feature);
					ColorCountries(event.feature);
				});

				

			break;
			default:
				Data[MapType].addListener('click', function(event){
				Infowindow.setContent(returnInfowindowContent(event.feature.k));
					var anchor = new google.maps.MVCObject();
					anchor.set('position', event.latLng);
					Infowindow.open(Map, anchor);
				});

				$.getScript( window.gmCDN + 'js/jquery.debounce.js', function(){

					google.maps.event.addListener(Map, "zoom_changed", $.debounce(250, function(){
							zoomLevel = Map.getZoom();
							if(zoomLevel < 8){
								Data[MapType].setStyle({
									icon: { url :  window.gmCDN + 'images/mini_icone_sgdf.png', 
											size: new google.maps.Size(20, 16),
							                origin: new google.maps.Point(0, 0),
							                anchor: new google.maps.Point(5.5, 15.5) }
								});
							} else {
								Data[MapType].setStyle({
									icon: { url :  window.gmCDN + 'images/icone_sgdf.png', 
											size: new google.maps.Size(39, 31),
							                origin: new google.maps.Point(0, 0),
							                anchor: new google.maps.Point(11, 31) }
								});
							}
						}
					));

				});

				google.maps.event.trigger(Map, "zoom_changed");
			break;
		}

	} else {
		var mapDataLoaded = window.setTimeout(function(){ putEventListenerOnMap(jQuery); }, 50);
	}
}

function ColorCountries(country){
	if( typeof(country) !== "undefined" ){
		var feature = Data['sgdf-international'].getFeatureById(country.k.ISO_A2);
		if(!feature){
			var countryColor = "#aaa";
			if( country.k.ISO_A2 === "FR" ){
				countryColor = "#0055A4";
			}
			Data[MapType].overrideStyle(country, { fillColor: countryColor, fillOpacity: .95 });
		} else {
			/*if(feature.k.francophonie !== false){
				var opacity = 0 ;
				switch(feature.k.francophonie){
					case 'member':
						opacity = .3 ;
					break;
					case 'associate':
						opacity = .25 ;
					break;
					case 'obs':
						opacity = .2 ;
					break;
				}
				Data[MapType].overrideStyle(country, {fillColor: '#0055A4', fillOpacity: opacity});
			}*/
		}
	} else {
		Data[MapType].forEach(function(feature){
			ColorCountries(feature);
		});
	}
}

function loadEIJSONData($){
		Data["sgdf-international"] = new google.maps.Data();
		Data["sgdf-international"].loadGeoJson(window.gmCDN + 'data/sgdf-international.json', {idPropertyName: "iso"});

		window.setTimeout(ColorCountries , 2000);
}

function loadMapData(){
	var jsonPath, dataStyle, geoJSONID ;
	switch( MapType ){
		case "bethleem":
		break;
		case "education-internationale":
			jsonPath = window.gmCDN + "data/countries.json";
			if ( !('withCredentials' in new XMLHttpRequest()) && window.location.hostname === "www.sgdf.fr"){
				console.error("Can't load countries data because of credentials error");
			}

			geoJSONID = "ISO_A2" ;

			dataStyle = {
				fillOpacity: 0,
				strokeWeight: .5,
				strokeColor: "#666"
			};
			
		break;
		case "groupes":
			jsonPath = window.gmCDN + 'data/groupes_sgdf.json' ;
			if ( !('withCredentials' in new XMLHttpRequest()) && window.location.hostname === "www.sgdf.fr"){
				jsonPath = 'http://www.sgdf.fr/templates/sgdf_design_2013/includes/groupes_sgdf.json';
			}

			
			dataStyle = {
				icon: { url :  window.gmCDN + 'images/icone_sgdf.png', 
						size: new google.maps.Size(39, 31),
		                origin: new google.maps.Point(0, 0),
		                anchor: new google.maps.Point(11, 31) }
			};

			Infowindow = new google.maps.InfoWindow({content:""});
		break;
	}

	if( typeof(jsonPath) !== "undefined" ){
		Data[MapType] = new google.maps.Data();
		Data[MapType].loadGeoJson(jsonPath, {idPropertyName:geoJSONID});
		Data[MapType].setStyle(dataStyle);
		Data[MapType].setMap(Map);
	}
}

function styleMap(){
	var featureOpts = null ;
	switch( MapType ){
		case "bethleem":
		break;
		case "education-internationale":
			featureOpts = [
				{	"featureType":"water",
					"stylers":[
						{"color":"#7cccde"}
					]
				},{	"featureType":"administrative",
					"elementType":"all",
					"stylers":[
						{"visibility":"off"}
					]
				},{	"featureType":"road",
					"elementType":"all",
					"stylers":[
						{"visibility":"off"}
					]
				},{	"featureType":"transit",
					"elementType":"all",
					"stylers":[
						{"visibility":"off"}
					]
				},{	"featureType":"poi",
					"elementType":"all",
					"stylers":[
						{"visibility":"off"}
					]
				},{	"featureType":"administrative.country",
					"elementType":"labels",
					"stylers":[
						{"visibility":"on"}
					]
				}
			];
		break;
		default:
			featureOpts = [];
		break;
	}

	var styledMapOptions = {
		name: 'SGDF'
	};
	var customMapType = new google.maps.StyledMapType(featureOpts, styledMapOptions);
	Map.mapTypes.set('SGDF', customMapType);
}

function geolocalise(){
	if(navigator.geolocation) {
        navigator.geolocation.getCurrentPosition(function(position) {
            Map.panTo(new google.maps.LatLng(position.coords.latitude, position.coords.longitude));
            Map.setZoom(12);
        });
    }
}

function loadDependencies(){
	window.clearTimeout(loadjQuery);
	var jqhtml = document.createElement('script');
	jqhtml.async = 1 ;
	jqhtml.src = window.gmCDN + 'js/jquery-1.11.1.min.js' ;
	var el = document.getElementsByTagName('script')[0];
	el.parentNode.insertBefore(jqhtml, el);
}

function isjQueryLoaded(){
	if(typeof(window.jQuery) === "function"){
		window.clearTimeout(checkjQuery);
		window.clearTimeout(loadjQuery);
		checkjQuery = null ;
		loadjQuery = 'yep' ;
		if(document.getElementById('addressSearch')){
			putSearchFieldListener(jQuery);
		}
		putEventListenerOnMap(jQuery);
	} else {
		if( loadjQuery != 'yep' ){
			checkjQuery = window.setTimeout(isjQueryLoaded, 50);
		}	
	}
}

var gmhtml = document.createElement('script');
gmhtml.async = 1 ;
gmhtml.src = "https://maps.googleapis.com/maps/api/js?key=AIzaSyA7on8yUVqBLV7hUYpiqPiV-Uh_HYu5M3Q&sensor=true&language=fr&callback=sgdfmap_initialize" ;
var el = document.getElementsByTagName('script')[0];
el.parentNode.insertBefore(gmhtml, el);

if(document.getElementById('addressSearch') || document.getElementById('map_loader')){
	var checkjQuery, loadjQuery;
	loadjQuery = window.setTimeout(loadDependencies, 7000);
	isjQueryLoaded();
}