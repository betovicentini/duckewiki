/**
 * @fileoverview This demo is used for MarkerClusterer. It will show 100 markers
 * using MarkerClusterer and count the time to show the difference between using
 * MarkerClusterer and without MarkerClusterer.
 * @author Luke Mahe (v2 author: Xiaoxi Wu)
 */

function $(element) {
  return document.getElementById(element);
}
var speedTest = {};

speedTest.pontos =[];
speedTest.count = null;
speedTest.map = null;
speedTest.markerClusterer = null;
speedTest.markers = [];
speedTest.infoWindow = null;


speedTest.init = function() {
  speedTest.pontos = data.mypoints;
  speedTest.count = data.count;

  var boundarr = data.boundaries.split("|");
  var myLatlng = new google.maps.LatLng(boundarr[0],boundarr[3]);
  var myOptions = {
    'gridSize': 100,
    'zoom': 4,
    'center': myLatlng,
    'averageCenter': true,
    'mapTypeId': google.maps.MapTypeId.TERRAIN
  }

  speedTest.map = new google.maps.Map(document.getElementById("map"), myOptions);

  var southWest = new google.maps.LatLng(boundarr[2],boundarr[5]);
  var northEast = new google.maps.LatLng(boundarr[1],boundarr[4]);
  var bounds = new google.maps.LatLngBounds(southWest,northEast);
  speedTest.map.fitBounds(bounds);
  
  
  //speedTest.pics = data.photos;
  var useGmm = document.getElementById('usegmm');
  google.maps.event.addDomListener(useGmm, 'click', speedTest.change);
  
  var numMarkers = document.getElementById('nummarkers');
  google.maps.event.addDomListener(numMarkers, 'change', speedTest.change);

  speedTest.infoWindow = new google.maps.InfoWindow();

  speedTest.showMarkers();
  
};
speedTest.markerClickFunction = function(pic, latlng) {
  return function(e) {
    e.cancelBubble = true;
    e.returnValue = false;
    if (e.stopPropagation) {
      e.stopPropagation();
      e.preventDefault();
    }
    var coletor = pic.Coletor;
    var dd = pic.Data;
    var detby = pic.Determinacao;
    var taxon = pic.Taxon;
    var projeto = pic.Projeto;
    var localidade = pic.Localidade;

	if (detby) {
		var dettxt = "Det: "+detby;
	} else { 
		var dettxt = '';
	}
    //.photo_title;
    //var url = pic;
    //var fileurl = imageUrl;

    var infoHtml = '<div class="info"><div class="info-body"><table class="info-table"><tr><td class="lgf">'+ taxon +'</td></tr><tr><td >'+ dettxt +'</td></tr><tr><td><b>'+ coletor +'</b> ('+ dd +')</td></tr><tr><td colspan="100%">'+ localidade +'</td></tr></table></div></div>';

    speedTest.infoWindow.setContent(infoHtml);
    speedTest.infoWindow.setPosition(latlng);
    speedTest.infoWindow.open(speedTest.map);
  };
};

speedTest.showMarkers = function() {
  speedTest.markers = [];

  var type = 1;
  if ($('usegmm').checked) {
    type = 0;
  }

  if (speedTest.markerClusterer) {
    speedTest.markerClusterer.clearMarkers();
  }

  var panel = $('markerlist');
  panel.innerHTML = '';
  var numMarkers = $('nummarkers').value;
  
  for (var i = 0; i < numMarkers; i++) {
    var titleText = speedTest.pontos[i].Coletor+'   '+speedTest.pontos[i].TaxaNoAutor;
    if (titleText == '') {
      titleText = 'No title';
    }
    if (i%2) { var titleclass = 'maptitle';} else { var titleclass = 'maptitleodd';}
    var item = document.createElement('DIV');
    var title = document.createElement('A');
    title.href = '#';
    title.className = titleclass;
    title.innerHTML = titleText;

    item.appendChild(title);
    panel.appendChild(item);


    var latLng = new google.maps.LatLng(speedTest.pontos[i].Latitude,speedTest.pontos[i].Longitude);

    var imageUrl = 'http://chart.apis.google.com/chart?cht=mm&chs=24x32&chco=' +'FFFFFF,008CFF,000000&ext=.png';

    var markerImage = new google.maps.MarkerImage(imageUrl, new google.maps.Size(24, 32));

    var marker = new google.maps.Marker({
      'position': latLng,
    });
    var fn = speedTest.markerClickFunction(speedTest.pontos[i], latLng);
    //var fnnn = '<div class="info"><div class="info-body">isso e um teste</div></div>';
    google.maps.event.addListener(marker, 'click', fn);
    speedTest.markers.push(marker);
    google.maps.event.addDomListener(title, 'click', fn);
  }
  window.setTimeout(speedTest.time, 0);
};

speedTest.clear = function() {
  $('timetaken').innerHTML = 'cleaning...';
  for (var i = 0, marker; marker = speedTest.markers[i]; i++) {
    marker.setMap(null);
  }
};

speedTest.change = function() {
  speedTest.clear();
  speedTest.showMarkers();
};

speedTest.time = function() {
  $('timetaken').innerHTML = 'timing...';
  var start = new Date();
  if ($('usegmm').checked) {
  	 var optmark = {
    	'maxZoom': 10
	  }
    speedTest.markerClusterer = new MarkerClusterer(speedTest.map, speedTest.markers,optmark);
  } else {
    for (var i = 0, marker; marker = speedTest.markers[i]; i++) {
      marker.setMap(speedTest.map);
    }
  }

  var end = new Date();
  $('timetaken').innerHTML = end - start;
};

//speedTest.justgetfile = function(formid,nsamples) {
//	var valor = data.count;
//	var element = document.getElementById(nsamples);
//	element.innerHTML = valor;
//	element.value = valor;
 //   document.forms[formid].submit();
//};
