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
if(!isset($uuid) || 
	(trim($uuid)=='')) {
		header("location: access-denied.php");
	exit();
} else {
	$acclevel = $_SESSION['accesslevel'];
}

//////PEGA E LIMPA VARIAVEIS
$ppost = cleangetpost($_POST,$conn);
@extract($ppost);
$arval = $ppost;

$gget = cleangetpost($_GET,$conn);
@extract($gget);

//CABECALHO
if ($ispopup==1) {
	$menu = FALSE;
} else {
	$menu = TRUE;
}
$which_css = array(
"<link href='css/geral.css' rel='stylesheet' type='text/css' />",
"<link rel='stylesheet' type='text/css' href='css/cssmenu.css' />"
);
$which_java = array(
"<script type='text/javascript' src='css/cssmenuCore.js'></script>",
"<script type='text/javascript' src='css/cssmenuAddOns.js'></script>",
"<script type='text/javascript' src='css/cssmenuAddOnsItemBullet.js'></script>"
);
$title = 'Exportar GPS';
$body = '';
FazHeader($title,$body,$which_css,$which_java,$menu);

$qq = "SELECT DISTINCT DateOriginal FROM GPS_DATA WHERE Type<>'Waypoint' LIMIT 0,1";
$res = mysql_query($qq);
$row = mysql_fetch_assoc($res);
$dt = $row['DateOriginal'];

$qq = "SELECT * FROM GPS_DATA WHERE DateOriginal='".$dt."' ORDER BY Type DESC,DateTimeOriginal";
$res = mysql_query($qq);

$tablename = "dadosgps_".$_SESSION['username']."_".$_SESSION['sessiondate'];
$fh = fopen("temp/".$tablename, 'w') or die("nao foi possivel gerar o arquivo");
$hh = "
<?xml version=\"1.0\" encoding=\"UTF-8\" standalone=\"no\" ?>
<gpx xmlns=\"http://www.topografix.com/GPX/1/1\" creator=\"WikiFlora\" version=\"1.1\" xmlns:xsi=\"http://www.w3.org/2001/XMLSchema-instance\" xsi:schemaLocation=\"http://www.topografix.com/GPX/1/1 http://www.topografix.com/GPX/1/1/gpx.xsd\">

  <metadata>
    <link href=\"http://www.garmin.com\">
      <text>Garmin International</text>
    </link>
  </metadata>
";
$lin = trim($hh);
fwrite($fh,$lin);
$tr = 0;
while ($row = mysql_fetch_assoc($res)) {

	if ($row['Type']=='Waypoint') {
		$txt = 
"

  <wpt lat=\"".$row['Latitude']."\" lon=\"".$row['Longitude']."\">
    <ele>".$row['Altitude']."</ele>
    <name>".$row['Name']."</name>
    <cmt>".$row['DateTimeOriginal']."</cmt>
    <desc>".$row['DateTimeOriginal']."</desc>
    <sym>Flag, Blue</sym>
    <extensions>
      <gpxx:WaypointExtension xmlns:gpxx=\"http://www.garmin.com/xmlschemas/GpxExtensions/v3\">
        <gpxx:DisplayMode>SymbolAndName</gpxx:DisplayMode>
      </gpxx:WaypointExtension>
    </extensions>
  </wpt>";
		fwrite($fh,$txt);
	} else {
		if ($tr==0) {
			$txt = 
"

  <trk>
    <name>O caminho percorrido</name>
    <extensions>
      <gpxx:TrackExtension xmlns:gpxx=\"http://www.garmin.com/xmlschemas/GpxExtensions/v3\">
        <gpxx:DisplayColor>White</gpxx:DisplayColor>
      </gpxx:TrackExtension>
    </extensions>
    <trkseg>";
		fwrite($fh,$txt);
		}
$txt = 
"
      <trkpt lat=\"".$row['Latitude']."\" lon=\"".$row['Longitude']."\">
        <ele>".$row['Altitude']."</ele>
        <name>".$row['Name']."</name>";
        if (!empty($row['DateTimeOriginal'])) {
        	$txt .= "
        <time>".$row['DateTimeOriginal']."</time>";
        }
        $txt .= "
      </trkpt>";
		fwrite($fh,$txt);
		$tr = $tr+1;
	}
}
if ($tr>0) {
$txt = "
    </trkseg>
  </trk>";
fwrite($fh,$txt);
}

$txt = "

</gpx>";
fwrite($fh,$txt);
fclose($fh);
echo "
<br />
<table class='myformtable' cellpadding='5' align='center' width=70%>
<thead>
<tr><td colspan='2'>Resultados</td></tr>
</thead>
<tbody>
<tr>
  <td><a href='temp/".$tablename."' target='_blank'>Baixar o arquivo com tracks</td>
</tr>
<tr>
  <td colspan='100%'><hr></td>
</tr>
<tr>
  <td colspan='100%' class='tdformnotes'>*Os arquivos são separados por TABULAÇÃO, tem quebras de linha em formato Unix, e o encoding de caracteres é UTF-8. O planilha do openoffice é melhor que o Excel para abrir o arquivo porque reconhece automaticamente o encoding dos caracteres (UTF-8). No Excel pode ter erros de grafia.</td>
</tr>
</tbody>
</table>";
$which_java = array("<script type='text/javascript' src='javascript/myjavascripts.js'></script>",
"<!-- Create Menu Settings: (Menu ID, Is Vertical, Show Timer, Hide Timer, On Click ('all' or 'lev2'), Right to Left, Horizontal Subs, Flush Left, Flush Top) -->",
"<script type='text/javascript'>qm_create(0,false,0,500,false,false,false,false,false);</script>");
FazFooter($which_java,$calendar=FALSE,$footer=$menu);
?>