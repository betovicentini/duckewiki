var map;

var pontos = [];

var themarkers = [];

function initialize(boundaries,locations) {
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
    zoom: 1,
    center: myLatlng,
    mapTypeId: google.maps.MapTypeId.HYBRID
  }

  map = new google.maps.Map(document.getElementById("map_canvas"), myOptions);

  // Add 5 markers to the map at random locations
  var southWest = new google.maps.LatLng(boundarr[2],boundarr[5]);
  var northEast = new google.maps.LatLng(boundarr[1],boundarr[4]);
  //document.write(southWest);
  //document.write(northEast);
  var bounds = new google.maps.LatLngBounds(southWest,northEast);
  map.fitBounds(bounds);
  setMarkers(map, pontos);
}

function setMarkers(map, pontos) {
  // Add markers to the map
  for (var i = 0; i < pontos.length; i++) {
    var local1 = pontos[i];
    var myLatLng = new google.maps.LatLng(local1[1], local1[2]);
    var marker = new google.maps.Marker({
        position: myLatLng,
        map: map
    });
    var j = i + 1;
    marker.setTitle(j.toString());
    attachSecretMessage(marker, local1[0]);
    themarkers.push(marker);
  }
   var markerclustereropt = {
    averageCenter: false,
    gridSize: 50, 
    maxZoom: 15
  }
   var markerCluster = new MarkerClusterer(map, themarkers,markerclustereropt);
}
// The five markers show a secret message when clicked
// but that message is not within the marker's instance data

function attachSecretMessage(marker, infotext) {
  var infowindow = new google.maps.InfoWindow(
      { content: infotext,
        size: new google.maps.Size(50,50)
      });
  google.maps.event.addListener(marker, 'click', function() {
    infowindow.open(map,marker);
  });
}      