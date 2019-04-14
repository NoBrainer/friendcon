function initialize() {
  	var mapCanvas = document.getElementById('map');

    var mapOptions = {
      center: new google.maps.LatLng(40.243728, -76.803466),
      disableDefaultUI: true,
      scrollwheel: false,
      zoom: 16,
      mapTypeId: google.maps.MapTypeId.ROADMAP
    }
    //Create map
    var map = new google.maps.Map(mapCanvas, mapOptions);

    //Create marker
    var marker = new google.maps.Marker({
      position: new google.maps.LatLng(40.243728, -76.803466),
      map: map,
      title: 'Sheraton Harrisburg Hershey Hotel',
      icon: 'images/map-marker.png'
 	});

    //Map marker info
    var contentString = '<div id="map-info">'+
      '<h5>Sheraton Harrisburg Hershey Hotel</h5>'+
      '<p style="text-align:left; margin:0;"><strong>Sheraton Harrisburg Hershey Hotel</strong>, is the home of Friendcon! The modern rooms come with free WiFi, flat-screen TVs, coffeemakers and desks with ergonomic chairs, not to mention they have yet to kick us out! </p> <p><a title="REGISTER A ROOM VIA THEIR WEBSITE" href="http://www.sheratonharrisburghershey.com/">REGISTER A ROOM VIA THEIR WEBSITE</a> </p>'+
      '</div>';

    //Add info to marker 
	var infowindow = new google.maps.InfoWindow({
	  content: contentString
	});

	google.maps.event.addListener(marker, 'click', function() {
		infowindow.open(map,marker);
	});

    //Keep map centered
    google.maps.event.addDomListener(window, 'resize', function() {
    	var center = map.getCenter();
    	google.maps.event.trigger(map, "resize");
    	map.setCenter(center); 
	});
  }
  google.maps.event.addDomListener(window, 'load', initialize);