<?php
session_start();
//INCLUI FUNCOES PHP E VARIAVEIS
include "functions/HeaderFooter.php";
include "functions/MyPhpFunctions.php";

//FAZ A CONEXAO COM O BANCO DE DADOS
$lang = $_SESSION['lang'];
$dbname = $_SESSION['dbname'];
$conn = ConectaDB($dbname);

//CHECA SE O USUARIO TEM PERMISSAO
$uuid = cleanQuery($_SESSION['userid'],$conn);
if((!isset($uuid) || 
	(trim($uuid)=='')) && count($listsarepublic)==0) {
		header("location: access-denied.php");
	exit();
} else {
	$acclevel = $_SESSION['accesslevel'];
}

//////PEGA E LIMPA VARIAVEIS
if (!empty($_POST['detset'])) {
	$detset = $_POST['detset'];
	unset($_POST['detset']);
}
if (!empty($_GET['detset'])) {
	$detset = $_GET['detset'];
	unset($_GET['detset']);
}

$ppost = cleangetpost($_POST,$conn);
@extract($ppost);
$arval = $ppost;

$gget = cleangetpost($_GET,$conn);
@extract($gget);


$uuid = $_SESSION['userid'];
$lastname = $_SESSION['userlastname'];
$aclevel = $_SESSION['accesslevel'];

$body = '';


$kmlfile = "temp/".$filename;

//echo $kmlfile;

$xml  = simplexml_load_file($kmlfile, 'SimpleXMLElement', LIBXML_NOWARNING);
$markers = array();
$counter = 0;

foreach ($xml->Document->Folder->Folder as $folder) {
	foreach ($folder as $place) {
	if (count($place)>0) {
	//echopre($place);
	$placemark = $place;
	$coordinates = $placemark->Point->coordinates;
    list($longtitude, $latitude, $discard) = explode(',', $coordinates, 3);
    /* Save parsed KML as simpler array. We output this as JSON later. */
    /* Save also id so we can match clicked link ad marker.            */
    //$mv = explode("__",(string)$placemark->localpath);
    $markers[] = array(
    	"lat"    => $latitude,
    	"lon"  => $longtitude, 
    	"name"  => (string)$placemark->name,
    	"d" => (string)$placemark->description,
    	"id"  => (string)$placemark->name,
    	"classname" => str_replace(".","",(string)$placemark->classe),
    	"nimgs" => (string)$placemark->nimgs,
    	"identif" => (string)$placemark->identif,
    	"country" => (string)$placemark->country,
    	"prov" => (string)$placemark->prov,
    	"muni" => (string)$placemark->muni,
    	"gazz" => (string)$placemark->gazz
    	);
    $counter++;
    }
    }
}
//echopre($markers);

$centerlat = $latcenter;
$centerlong = $longcenter;
$jsonmarkers = json_encode($markers);

$texttowrite= "var mapData = ".$jsonmarkers;
$jsonfile = "temp/".str_replace(".kml",".json",$filename);
$fh = fopen($jsonfile, 'w') or die("nao foi possivel gerar o arquivo");
fwrite($fh, $texttowrite);
fclose($fh);
//print_r($markers);
echo "
<!DOCTYPE html>
<html>
<head>
  <meta http-equiv=\"content-type\" content=\"text/html; charset=UTF-8\" />
  <meta name=\"viewport\" content=\"initial-scale=1.0, user-scalable=no\" />
  <title>Mapeando amostras</title>
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
        mapTypeId: gm.MapTypeId.TERRAIN,
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
          title: datum.name,
          map: map,
          icon: iconWithColor(usualColor,datum.id),
          shadow: shadow,
          idd: datum.id
        });
        marker.desc = datum.d;
        oms.addMarker(marker);


		
//		if (habclass != datum.classname) {
//		    var itemtit = document.createElement('DIV');
//		    itemtit.className = 'subtitulo';
//		    itemtit.innerHTML = datum.classname;
//	    	panel.appendChild(itemtit);
//	    	habclass = datum.classname;
//		}
//
		if (habprov != datum.prov) {
		    var itemprov = document.createElement('DIV');
		    itemprov.className = 'subtitulo';
		    itemprov.innerHTML = datum.prov;
	    	panel.appendChild(itemprov);
	    	habprov = datum.prov;
		}
		
		if (habmuni != datum.muni) {
		    var itemmuni = document.createElement('DIV');
		    itemmuni.className = 'subsubtitulo';
		    itemmuni.innerHTML = datum.muni;
	    	panel.appendChild(itemmuni);
	    	habmuni = datum.muni;
		}

		if (habgaz != datum.gazz) {
		    var itemgazz = document.createElement('DIV');
		    itemgazz.className = 'subsubsubtitulo';
		    itemgazz.innerHTML = datum.gazz;
	    	panel.appendChild(itemgazz);
	    	habgaz = datum.gazz;
		}

	    var item = document.createElement('DIV');
	    var title = document.createElement('A');
	    //var ull = document.createElement('UL');
	    //var titleimg = document.createElement('IMG');


	    title.href = '#';
	    title.className = 'teste'+i;
		//titleimg.src = iconWithColor(usualColor,datum.id);
		//titleimg.height = 18;		
	    title.innerHTML = datum.identif;
    	//title.appendChild(titleimg);
    	item.appendChild(title);
    	//ull.appendChild(item);
    	panel.appendChild(item);
		var fn = mytitleclick(datum, loc, iw);
		gm.event.addDomListener(title, 'click', fn);
      }


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

?>