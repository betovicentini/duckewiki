<?php
//Start session
//set_time_limit(0);
//Start session
session_start();
//Check whether the session variable
include "functions/HeaderFooter.php";
include "functions/MyPhpFunctions.php";
$lang = $_SESSION['lang'];
$dbname = $_SESSION['dbname'];
$conn = ConectaDB($dbname);
$uuid = cleanQuery($_SESSION['userid'],$conn);
if((!isset($uuid) || 
	(trim($uuid)=='')) && count($listsarepublic)==0) {
		header("location: access-denied.php");
	exit();
} 

$ppost = cleangetpost($_POST,$conn);
//$arval = $ppost;
@extract($ppost);
$gget = cleangetpost($_GET,$conn);
@extract($gget);


$uuid = $_SESSION['userid'];
$lastname = $_SESSION['userlastname'];
$aclevel = $_SESSION['accesslevel'];
//echopre($_SESSION);
$body = '';

if ($coords==1) {
	include "temp/temp".$export_filename;
	$llati = explode(";",$lati);
	$llong = explode(";",$llongi);
	//$fim = unserialize($_SESSION['temp_habitat_'.substr(session_id(),0,5)]);
	//echopre($fim);
	//$llati = $fim[0];
	//$llong = $fim[1];
	//$export_filename = $fim[2];
}
$kmlfile = "temp/".$export_filename;
//echo $kmlfile."<br >";
$xml  = simplexml_load_file($kmlfile, null, LIBXML_NOCDATA);
//echopre($xml);
    /* Print cityname as headline. */
    //printf('<h3><a id="%s" class="city" href="#">%s</a></h3>', $city, $xml->Document->name);
$markers = array();
$counter = 0;
foreach ($xml->Document->Folder->Placemark as $placemark) {
	$coordinates = $placemark->Point->coordinates;
    list($longtitude, $latitude, $discard) = explode(',', $coordinates, 3);
    /* Save parsed KML as simpler array. We output this as JSON later. */
    /* Save also id so we can match clicked link ad marker.            */
    $mv = explode("__",(string)$placemark->localpath);
    $markers[] = 
    	array(
    	"lat"    => $latitude,
    	"lon"  => $longtitude, 
    	"name"  => (string)$placemark->name,
    	"d" => (string)$placemark->description,
    	"id"  => (string)$placemark->name,
    	//"title" => (string)$placemark->title,
    	"pais" => (string)$mv[0],
    	"prov"=> (string)$mv[1],
    	"muni"=> (string)$mv[2],
    	"gaz" => (string)$mv[3],
    	"classname" => str_replace(".","",(string)$placemark->classe)
    	);
        /* Print officename as link to single office. */
        //printf('<li><a class="office" href="#" id="marker-%d">%s</a></li>', $latitude, $longtitude, $counter, $placemark->name);
        //print "\n";
    $counter++;
}
//$llati = explode(";",$lati);
//$llong = explode(";",$llong);
//echopre($llati);
//echopre($llong);
$centerlat = array_sum($llati)/count($llati);
$centerlong = array_sum($llong)/count($llong);
$maxlat = max($llati);
$minlat =  min($llati);
$maxlong = max($llong);
$minlong =  min($llong);
$boundaries = array($centerlat, $maxlat, $minlat, $centerlong, $maxlong, $minlong);
//$boundaries = implode("|",$boundaries);
//$mm = array((array)array('count'=> $counter),(array)array('mypoints'=> $markers),(array)array('boundaries'=> $boundaries));
$jsonmarkers = json_encode($markers);
//$texttowrite = "var data = { \"count\": ".$counter.",\n \"mypoints\":".$jsonmarkers.",\n\"boundaries\": \"".$boundaries."\"}";
$texttowrite= "var mapData = ".$jsonmarkers;

//$jsonmarkers = "var mapData = ".$jsonmarkers;
$jsonfile = "temp/".str_replace(".kml",".json",$export_filename);
//echo $jsonfile."<br />";
$fh = fopen($jsonfile, 'w') or die("nao foi possivel gerar o arquivo");
fwrite($fh, $texttowrite);
fclose($fh);
//print($jsonmarkers);
$which_css = array(
"<meta name=\"viewport\" content=\"initial-scale=1.0, user-scalable=no\" />",
"<meta http-equiv=\"content-type\" content=\"text/html; charset=UTF-8\"/>",
"<link rel=\"stylesheet\" type=\"text/css\" media=\"screen\" href=\"css/geral.css\" />",
"<link rel=\"stylesheet\" type=\"text/css\" media=\"screen\" href=\"css/cssmenu.css\" />",
"<link rel=\"stylesheet\" type=\"text/css\" media=\"screen\" href=\"css/mapwindow.css\" />",
"<link href=\"http://code.google.com/apis/maps/documentation/javascript/examples/default.css\" rel=\"stylesheet\" type=\"text/css\" />"
);

$datascript ="<script src=\"".$jsonfile."\"></script>";
//"<script type=\"text/javascript\" src=\"javascript/oms.min.js\"></script>",
$loadmap = "<script type=\"text/javascript\">
      google.load('maps', '3', {other_params: 'sensor=false'});
      google.setOnLoadCallback(speedTest.init);</script>";
$which_java = array(
"<script type=\"text/javascript\" src=\"css/cssmenuCore.js\"></script>",
"<script type=\"text/javascript\" src=\"css/cssmenuAddOns.js\"></script>",
"<script type=\"text/javascript\" src=\"css/cssmenuAddOnsItemBullet.js\"></script>",
"<script type=\"text/javascript\" src=\"http://www.google.com/jsapi\"></script>",
"<script type=\"text/javascript\" src=\"javascript/oms.min.js\"></script>",
$scriptmap,$datascript,$loadmap);
//$body = " onload=\"javascript:speedTest.init();\" ";
//FazHeader('Plotando Habitats',$body,$which_css,$which_java,FALSE);
//<div id=\"map_canvas\" style=\"margin-left: 3%; margin-right: 3%; margin-bottom: 3%; margin-top: 3%; width: 70%; height: 93%;\"></div>
echo "
<!DOCTYPE html>
<html>
<head>
  <meta http-equiv=\"content-type\" content=\"text/html; charset=UTF-8\" />
  <meta name=\"viewport\" content=\"initial-scale=1.0, user-scalable=no\" />
  <title>Overlapping Marker Spiderfier demo</title>
  <style>
    html { height: auto; }
    body { height: auto; margin: 0; padding: 0; font-family: Georgia, serif; font-size: 0.9em; }
    table { border-collapse: collapse; border-spacing: 0; }
    p { margin: 0.75em 0; }
    #map_canvas { border:0.1em solid black; position: absolute; margin-left: 2%; margin-right: 0%; margin-bottom: 2%; margin-top: 2%; width: 70%; height: 96%;}
	.subtitulo { font: 1.5em bold; text-align:center; color: #900000 ; padding: 5px;}
	.subsubtitulo { font: 1.1em bold; text-align:center; background-color: #989898  ; padding: 5px;}
	.subsubsubtitulo { font: 1em bold; text-align:center; background-color: #E0E0E0 ; padding: 5px;}

  </style>
  <script src=\"http://maps.google.com/maps/api/js?v=3.7&amp;sensor=false\"></script>
  <script src=\"javascript/oms.min.js\"></script>
  <script>
    window.onload = function() {
      var gm = google.maps;
      var map = new gm.Map(document.getElementById('map_canvas'), {
        mapTypeId: gm.MapTypeId.SATELLITE,
        center: new gm.LatLng(".$centerlat.",".$centerlong."), 
        zoom: 4, 
        scrollwheel: true
      });
      var iw = new gm.InfoWindow();
      var oms = new OverlappingMarkerSpiderfier(map,
        {markersWontMove: true, markersWontHide: true});
      var usualColor = 'eebb22';
      var spiderfiedColor = 'ffee22';
      var iconWithColor = function(color,num) {
        //chst=d_map_pin_letter_withshadow&chld='+ num + '|' + color + '|0000FF';
        return 'http://chart.googleapis.com/chart?chst=d_map_spin&chld=0.8|0|' + color + '|9|_|'+num;
        //chst=d_bubble_text_small&chld=bb|'+num+'|'+ color + '|000000';
        //chst=d_map_xpin_letter&chld=pin|+|' + color + '|000000|ffff00';
        
        
      }
      var shadow = new gm.MarkerImage(
        'https://www.google.com/intl/en_ALL/mapfiles/shadow50.png',
        new gm.Size(37, 34),  // size   - for sprite clipping
        new gm.Point(0, 0),   // origin - ditto
        new gm.Point(10, 34)  // anchor - where to meet map location
      );

      oms.addListener('mouseover', function(marker) {
        iw.setContent(marker.name);
        iw.open(map, marker);
      });
            
      oms.addListener('click', function(marker) {
        iw.setContent(marker.desc);
        iw.open(map, marker);
      });
      
      oms.addListener('spiderfy', function(markers) {
        for(var i = 0; i < markers.length; i ++) {
          markers[i].setIcon(iconWithColor(spiderfiedColor,markers[i].idd));
          markers[i].setShadow(null);
        } 
        iw.close();
      });
      oms.addListener('unspiderfy', function(markers) {
        for(var i = 0; i < markers.length; i ++) {
          markers[i].setIcon(iconWithColor(usualColor,markers[i].idd));
          markers[i].setShadow(shadow);
        }
      });
      

  	  var panel = document.getElementById('markerlist');
  	  panel.innerHTML = '';
      //var bounds = new gm.LatLngBounds();
      var habclass = 'naoenadadisso'; 
      var habmuni =  'naoenadadisso'; 
      var habprov =  'naoenadadisso'; 
      var habgaz =  'naoenadadisso'; 
        
      for (var i = 0; i < window.mapData.length; i ++) {
        var datum = window.mapData[i];
        var loc = new gm.LatLng(datum.lat, datum.lon);
        //bounds.extend(loc);
        
        var marker = new gm.Marker({
          position: loc,
          title: datum.h,
          map: map,
          icon: iconWithColor(usualColor,datum.id),
          shadow: shadow,
          idd: datum.id
        });
        marker.desc = datum.d;
        oms.addMarker(marker);



		if (habclass != datum.classname) {
		    var itemtit = document.createElement('DIV');
		    itemtit.className = 'subtitulo';
		    itemtit.innerHTML = datum.classname;
	    	panel.appendChild(itemtit);
	    	habclass = datum.classname;
		}



		if (habprov != datum.prov) {
		    var itemsubtit = document.createElement('DIV');
		    itemsubtit.className = 'subsubtitulo';
		    itemsubtit.innerHTML = datum.prov;
	    	panel.appendChild(itemsubtit);
	    	habprov = datum.prov;
		}

		if (habgaz != datum.gaz) {
		    var itemsubsubtit = document.createElement('DIV');
		    itemsubsubtit.className = 'subsubsubtitulo';
		    itemsubsubtit.innerHTML = datum.gaz;
	    	panel.appendChild(itemsubsubtit);
	    	habgaz = datum.gaz;
		}

	    var item = document.createElement('DIV');
	    var title = document.createElement('A');
	    //var ull = document.createElement('UL');
	    //var titleimg = document.createElement('IMG');


	    title.href = '#';
	    title.className = 'teste'+i;
		//titleimg.src = iconWithColor(usualColor,datum.id);
		//titleimg.height = 18;
	    title.innerHTML = datum.id;
    	//title.appendChild(titleimg);
    	item.appendChild(title);
    	//ull.appendChild(item);
    	panel.appendChild(item);
		var fn = mytitleclick(datum, loc, iw);
		gm.event.addDomListener(title, 'click', fn);
      }
      

      //var southWest = new google.maps.LatLng(".$boundaries[1].",".$boundaries[4].");
      //var northEast = new google.maps.LatLng(".$boundaries[0].",".$boundaries[3].");
      //var bounds = new google.maps.LatLngBounds(southWest,northEast);
     // map.fitBounds(bounds);
      // map.fitBounds(bounds);

      // for debugging/exploratory use in console
      window.map = map;
      window.oms = oms;

    };
    mytitleclick = function(pic, latlng, iw) {
		return function(e) {
		  var infoHtml = pic.d
  		  iw.setContent(infoHtml);
    	  iw.setPosition(latlng);
	      iw.open(map);
		  };
	};
  </script>";

//<div style=\"position: absolute; font: 1.2em bold; margin-left: 74%; margin-top: 3%; margin-bottom: 0; width:25%; height: 85%; text-align:center;\">Habitats</div>

echo "
<div id=\"map_canvas\" ></div>
<div id=\"markerlist\" style=\"position: absolute; font-size: 0.8em; margin-left: 73%; margin-top: 2%; margin-bottom: 2%; width:25%; height: 96%; overflow: scroll; border:0.1em solid black;\"></div>








</body>
<script src=\"".$jsonfile."\"></script>
</html>";
//HTMLtrailers();
?>