 		var pontos =[];
      var map = null;
      var markers = [];
      var markerClusterer = null;
      function initialize(boundaries,locations) {
        if(GBrowserIsCompatible()) {         
  	      var bbarr = document.getElementById(boundaries).value;
  			var boundarr = bbarr.split("|");
	  		var pto = document.getElementById(locations).value;
 			var arr1 = pto.split("|");
  			for (var j = 0; j < arr1.length; j++) {
  				var arr2 = arr1[j].split("$$");
				pontos[j] = arr2;
			}
		  var myLatlng = new google.maps.LatLng(boundarr[0],boundarr[3]);
		  var myOptions = {
		    'zoom': 2,
		    'center': myLatlng,
		    'mapTypeId': google.maps.MapTypeId.HYBRID
		  }

			var southWest = new google.maps.LatLng(boundarr[2],boundarr[5]);
			var northEast = new google.maps.LatLng(boundarr[1],boundarr[4]);
			var bounds = new google.maps.LatLngBounds(southWest,northEast);
		  	map = new GMap2(document.getElementById('map'));
		  	map.fitBounds(bounds);
	        map.addControl(new GLargeMapControl());
          	map.addControl(new GMapTypeControl());
	          var icon = new GIcon(G_DEFAULT_ICON);
    	      icon.image = "icons/specimen-icon.png";
          	for (var i = 0; i < 1000; ++i) {
           		 var latlng = new GLatLng(pontos[i].[1], pontos[i].[2]);
           		 var marker = new GMarker(latlng, {icon: icon});
           		 markers.push(marker);
         		 }
          	refreshMap();
	        }
      }
      function refreshMap() {
        if (markerClusterer != null) {
          markerClusterer.clearMarkers();
        }
        var zoom = parseInt(document.getElementById("zoom").value, 10);
        var size = parseInt(document.getElementById("size").value, 10);
        var style = document.getElementById("style").value;
        zoom = zoom == -1 ? null : zoom;
        size = size == -1 ? null : size;
        style = style == "-1" ? null: parseInt(style, 10);
        markerClusterer = new MarkerClusterer(map, markers, {maxZoom: zoom, gridSize: size, styles: styles[style]});
      }