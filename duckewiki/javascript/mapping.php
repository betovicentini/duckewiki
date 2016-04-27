<?php
session_start();
include "functions/HeaderFooter.php";
include "functions/SelectOptions.php";
function calculate_average($arr) {
    $count = count($arr); //total numbers in array
    foreach ($arr as $value) {
        $total = $total + $value; // total value of array numbers
    }
    $average = ($total/$count); // get average value
    return $average;
}


$lang = $_SESSION['lang'];
$dbname = $_SESSION['dbname'];
$betoconn = ConectaDB($dbname);
$qq = "DROP TABLE Temp_Map";
mysql_query($qq,$betoconn);
$qq = "CREATE TABLE Temp_Map (
  id int(11) NOT NULL auto_increment,
  Coletor char(52) collate utf8_unicode_ci NOT NULL,
  Numero int(10) NOT NULL default '0',
  Coords char(30) collate utf8_unicode_ci,
  Longitude char(30),
  Latitude char(30),
 	PRIMARY KEY  (id)
) ENGINE=MyISAM  DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci";
mysql_query($qq,$betoconn);

$qq = "INSERT INTO Temp_Map (Coletor,Numero,Coords,Longitude,Latitude) SELECT pess.Abreviacao as Coletor,s.Number as Numero, CONCAT(gps.Longitude,',',gps.Latitude) as Coords,gps.Longitude,gps.Latitude FROM Especimenes as s JOIN Gazetteer as gps ON s.GazetteerID = gps.GazetteerID JOIN Pessoas as pess ON pess.PessoaID = s.ColetorID WHERE CONCAT(gps.Longitude,',',gps.Latitude)<>',' AND gps.Latitude<>0";
mysql_query($qq,$betoconn);

$qq = "SELECT AVG(Latitude) as AVG_Lat, MAX(Latitude) as MAX_Lat, MIN(Latitude) as MIN_Lat, AVG(Longitude) as AVG_Long, MAX(Longitude) as MAX_Long, MIN(Longitude) as MIN_Long FROM Temp_Map";
$res = mysql_query($qq,$betoconn);
$rw = mysql_fetch_assoc($res);
$centerlong = $rw['AVG_Long'];
$centerlat =  $rw['AVG_Lat'];

//echopre($rw);

$nb =2;
$maxlat = (abs($rw['MAX_Lat'])+$nb)*($rw['MAX_Lat']/abs($rw['MAX_Lat']));
$minlat = (abs($rw['MIN_Lat'])+$nb)*($rw['MIN_Lat']/abs($rw['MIN_Lat']));
$maxlong = (abs($rw['MAX_Long'])+$nb)*($rw['MAX_Long']/abs($rw['MAX_Long']));
$minlong = (abs($rw['MIN_Long'])+$nb)*($rw['MIN_Long']/abs($rw['MIN_Long']));

$centerlong = ($maxlong+$minlong)/2;
$centerlat = ($maxlat+$minlat)/2;


$qq = "SELECT * FROM Temp_Map";
$rs = mysql_query($qq,$betoconn);
$resul = array();
$i=0;
$latarr = array();
$longarr = array();
while ($row = mysql_fetch_assoc($rs)) {
	$lat = $row['Latitude'];
	$lng = $row['Longitude'];
	$nome = utf8_encode($row['Coletor'])." ".$row['Numero'];
	$rr = implode("$$",array($nome,$lat,$lng));
	$resul[$i] = $rr;
	$latarr[$i] = $lat;
	$longarr[$i] = $lng;
	$i++;
}

//echo calculate_average($latarr)." ".min($latarr)." ".max($latarr)."<br>";
//echo calculate_average($longarr)." ".min($longarr)." ".max($longarr)."<br>";

$boundaries = implode("|",array(
		$centerlat => (max($latarr)+ min($latarr))/2,
		$maxlat => max($latarr),
		$minlat => min($latarr),
		$centerlong => (max($longarr)+ min($longarr))/2,
		$maxlong => max($longarr),
		$minlong => min($longarr))
		);
echopre($boundaries);
$rew = implode("|",$resul);
//echo $rew;
echo "<!DOCTYPE html public>
<html xmlns=\"http://www.w3.org/1999/xhtml\" lang=\"en\" xml:lang=\"en\">
<head>
<title>Teste apenas</title>
<input type=\"hidden\" id=\"boundaries\" value=\"".$boundaries."\">
<input type=\"hidden\" id=\"locations\" value=\"".$rew."\">
<style type=\"text/css\">
      body {
        margin: 0;
        padding: 0;
        font-family: Arial;
        font-size: 14px;
      }

      #panel {
        float: left;
        width: 300px;
        height: 550px;
      }

      #map-container {
        margin-left: 300px;
      }

      #map {
        width: 100%;
        height: 550px;
      }

      #markerlist {
        height: 400px;
        margin: 10px 5px 0 10px;
        overflow: auto;
      }

      .title {
        border-bottom: 1px solid #e0ecff;
        overflow: hidden;
        width: 256px;
        cursor: pointer;
        padding: 2px 0;
        display: block;
        color: #000;
        text-decoration: none;
      }

      .title:visited {
        color: #000;
      }

      .title:hover {
        background: #e0ecff;
      }

      #timetaken {
        color: #f00;
      }

      .info {
        width: 200px;
      }

      .info img {
        border: 0;
      }

      .info-body {
        width: 200px;
        height: 200px;
        line-height: 200px;
        margin: 2px 0;
        text-align: center;
        overflow: hidden;
      }

      .info-img {
        height: 220px;
        width: 200px;
      }

    </style>


<script type=\"text/javascript \"src=\"http://maps.google.com/maps/api/js?sensor=true\"></script>
<script type=\"text/javascript\" src=\"javascript/markerclusterer.js\"></script>
 <script src=\"javascript/data.json\" type=\"text/javascript\"></script>
<script type=\"text/javascript\" src=\"javascript/speed_test.js\"></script>

</head> <body onload=\"initialize()\" onunload=\"GUnload()\">
    <div>
      <span>Max zoom level: 
        <select id=\"zoom\">
          <option value=\"-1\">Default</option>
          <option value=\"7\">7</option>
          <option value=\"8\">8</option>
          <option value=\"9\">9</option>

          <option value=\"10\">10</option>
          <option value=\"11\">11</option>
          <option value=\"12\">12</option>
          <option value=\"13\">13</option>
          <option value=\"14\">14</option>
        </select>

      </span>
      <span style=\"margin-left:20px;\">Cluster size:
        <select id=\"size\">
          <option value=\"-1\">Default</option>
          <option value=\"40\">40</option>
          <option value=\"50\">50</option>
          <option value=\"70\">70</option>

          <option value=\"80\">80</option>
        </select>
      </span>
      <span style=\"margin-left:20px;\">Cluster style: 
        <select id=\"style\">
          <option value=\"-1\">Default</option>
          <option value=\"0\">People</option>
          <option value=\"1\">Conversation</option>

          <option value=\"2\">Heart</option>
       </select>
       <input type=\"button\" value=\"Refresh Map\" style=\"margin-left:20px;\" onclick=\"refreshMap()\"></input>
    </div>
    <div style=\"width:800px;height:400px;margin-top:10px;\" id=\"map\"></div>
  </body>
</html>";

//<input type=\"hidden\" id=\"centerlong\" value=\"".$centerlong."\">
//<input type=\"hidden\" id=\"centerlat\" value=\"".$centerlat."\">
?>

