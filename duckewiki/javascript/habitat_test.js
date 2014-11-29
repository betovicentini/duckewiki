/**
 * @fileoverview This demo is used for MarkerClusterer. It will show 100 markers
 * using MarkerClusterer and count the time to show the difference between using
 * MarkerClusterer and without MarkerClusterer.
 * @author Luke Mahe (v2 author: Xiaoxi Wu)
 
 * modified by A. Vicentini
 */

function $(element) {
  return document.getElementById(element);
}
var habitatTest = {};

habitatTest.pontos =[];
habitatTest.count = null;
habitatTest.map = null;
habitatTest.markerClusterer = null;
habitatTest.markers = [];
habitatTest.infoWindow = null;

habitatTeste.changedata = function(urlfileid) {
		var urlfile = document.getElementById(urlfileid).value;
		$.getJSON(urlfile,function(data){
			var items = [];
    		$.each(data, {
				var lati = data.Latitude;
				var longi = data.Longitude;
				var locality = data.Localidade;
				items.push('<li >lati: '+ lati + 'longi:' + longi + '</li>');
    		});
    		$('<ul/>', {
				'class': 'my-new-list',
				html: items.join('')
				}).appendTo('#mymap');
    	);
	//habitatTest.init();
}

habitatTest.init = function() {
  habitatTest.pontos = data.mypoints;
  habitatTest.count = data.count;

  var boundarr = data.boundaries.split("|");
  var myLatlng = new google.maps.LatLng(boundarr[0],boundarr[3]);
  var myOptions = {
    'gridSize': 100,
    'zoom': 4,
    'center': myLatlng,
    'averageCenter': true,
    'mapTypeId': google.maps.MapTypeId.TERRAIN
  }

  habitatTest.map = new google.maps.Map(document.getElementById("map"), myOptions);

  var southWest = new google.maps.LatLng(boundarr[2],boundarr[5]);
  var northEast = new google.maps.LatLng(boundarr[1],boundarr[4]);
  var bounds = new google.maps.LatLngBounds(southWest,northEast);
  habitatTest.map.fitBounds(bounds);
  
  
  //habitatTest.pics = data.photos;
  //var useGmm = document.getElementById('usegmm');
  //google.maps.event.addDomListener(useGmm, 'click', habitatTest.change);
  
  //var numMarkers = document.getElementById('nummarkers');
  //google.maps.event.addDomListener(numMarkers, 'change', habitatTest.change);

  habitatTest.infoWindow = new google.maps.InfoWindow();
  habitatTest.showMarkers();
};
habitatTest.markerClickFunction = function(pic, latlng) {
  return function(e) {
    e.cancelBubble = true;
    e.returnValue = false;
    if (e.stopPropagation) {
      e.stopPropagation();
      e.preventDefault();
    }
    
    var infoHtml = '<div class="info"><div class="info-body">'+pic.InfoHTML+'</div></div>';

    habitatTest.infoWindow.setContent(infoHtml);
    habitatTest.infoWindow.setPosition(latlng);
    habitatTest.infoWindow.open(habitatTest.map);
  };
};

habitatTest.showMarkers = function() {
  habitatTest.markers = [];

  var type = 0;
  //if ($('usegmm').checked) {
    //type = 0;
  //}

  if (habitatTest.markerClusterer) {
    habitatTest.markerClusterer.clearMarkers();
  }

  //var panel = $('markerlist');
  //panel.innerHTML = '';
  var numMarkers = habitatTest.count;
  for (var i = 0; i < numMarkers; i++) {
    //var titleText = habitatTest.pontos[i].Coletor+'   '+habitatTest.pontos[i].TaxaNoAutor;
    //if (titleText == '') {
      //titleText = 'No title';
    //}
    //if (i%2) { var titleclass = 'maptitle';} else { var titleclass = 'maptitleodd';}
    //var item = document.createElement('DIV');
    //var title = document.createElement('A');
    //title.href = '#';
    //title.className = titleclass;
    //title.innerHTML = titleText;
    //item.appendChild(title);
    //panel.appendChild(item);
    var latLng = new google.maps.LatLng(habitatTest.pontos[i].Latitude,habitatTest.pontos[i].Longitude);
    var imageUrl = 'http://chart.apis.google.com/chart?cht=mm&chs=24x32&chco=' +'FFFFFF,008CFF,000000&ext=.png';
    var markerImage = new google.maps.MarkerImage(imageUrl, new google.maps.Size(24, 32));
    var marker = new google.maps.Marker({
      'position': latLng,
    });
    var fn = habitatTest.markerClickFunction(habitatTest.pontos[i], latLng);
    //var fnnn = '<div class="info"><div class="info-body">isso e um teste</div></div>';
    google.maps.event.addListener(marker, 'click', fn);
    habitatTest.markers.push(marker);
    //google.maps.event.addDomListener(title, 'click', fn);
  }
  //window.setTimeout(habitatTest.time, 0);
};

//habitatTest.clear = function() {
//  $('timetaken').innerHTML = 'cleaning...';
//  for (var i = 0, marker; marker = habitatTest.markers[i]; i++) {
//    marker.setMap(null);
//  }
//};
habitatTest.change = function(habid) {
  habitatTest.clear();
  habitatTest.showMarkers();
};

//habitatTest.time = function() {
//  $('timetaken').innerHTML = 'timing...';
//  var start = new Date();
//  if ($('usegmm').checked) {
//  	 var optmark = {
//    	'maxZoom': 10
//	  }
//    habitatTest.markerClusterer = new MarkerClusterer(habitatTest.map, habitatTest.markers,optmark);
//  } else {
//    for (var i = 0, marker; marker = habitatTest.markers[i]; i++) {
//      marker.setMap(habitatTest.map);
//    }
//  }
//
//  var end = new Date();
//  $('timetaken').innerHTML = end - start;
//};
//
//habitatTest.justgetfile = function(formid,nsamples) {
//	var valor = data.count;
//	var element = document.getElementById(nsamples);
//	element.innerHTML = valor;
//	element.value = valor;
 //   document.forms[formid].submit();
//};
