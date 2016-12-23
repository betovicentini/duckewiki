<?php
//Start session
set_time_limit(0);
//Start session
session_start();
//Check whether the session variable
include "functions/HeaderFooter.php";
include "functions/SelectOptions.php";
$lang = $_SESSION['lang'];
$dbname = $_SESSION['dbname'];
$conn = ConectaDB($dbname);
$uuid = cleanQuery($_SESSION['userid'],$conn);
if(!isset($uuid) || 
	(trim($uuid)=='')) {
		header("location: access-denied.php");
	exit();
} 

$ppost = cleangetpost($_POST,$conn);
$arval = $ppost;
@extract($ppost);
$gget = cleangetpost($_GET,$conn);
@extract($gget);

HTMLheaders($body);

if (!isset($viagemid)) {
echo "
<br>
<table class='myformtable' align='left' cellpadding='7' width='50%'>
<thead>
<tr ><td colspan='100%'>Definir ou editar trilhas de busca</td></tr>
</thead>
<tbody>
<form action=exportGPS_kml.php method='post'>";
echo "
<tr>
  <td  colspan='100%'>
    <select name='viagemid' onchange='this.form.submit()'>
      <option value=''>".GetLangVar('nameselect')."</option>
      <option value=''>------------</option>";
	$qq = "SELECT * FROM Expedicoes ORDER BY DateStart DESC";
	$rrr = @mysql_query($qq,$conn);
	while ($row = mysql_fetch_assoc($rrr)) {
			echo "
      <option value=".$row['ViagemID'].">".$row['Name']." [".$row['DateStart']." a ".$row['DateEnd']."]</option>";
		}
	echo "
    </select>
    </td>
</tr>
</tbody>
</table>
</form>";
} 
else {

$qq = "SELECT exp.Name as Viagem,exp.DateStart, exp.DateEnd, trl.TrilhaID,trl.Name as Trilha,gps.Name as Waypoint,gps.DateOriginal as Data, gps.Latitude,gps.Longitude,gps.Altitude FROM Expedicao_Trilhas as trl LEFT JOIN GPS_DATA as gps ON gps.TrilhaID=trl.TrilhaID LEFT JOIN Expedicoes as exp ON exp.ViagemID=trl.ViagemID WHERE gps.Type='Waypoint' AND trl.ViagemID='".$viagemid."' ORDER BY trl.Name,gps.Name";
$res = mysql_query($qq);

#algumas definicoes
$url = $_SERVER['HTTP_REFERER'];
$uu = explode("/",$url);
$nu = count($uu)-1;
unset($uu[$nu]);
$url = implode("/",$uu);




$tr = 0;
$idx =0;
while ($row = mysql_fetch_assoc($res)) {
	if ($idx==0) {
		$myviagem = $row['Viagem']." [".$row['DateStart']." e ".$row['DateEnd']."]";
		$hh = "
<?xml version=\"1.0\" encoding=\"UTF-8\"?>
<kml xmlns=\"http://www.opengis.net/kml/2.2\">
<Document>
  <name>Dados da expedição botânica ".$row['Viagem']." realizada entre ".$row['DateStart']." e ".$row['DateEnd'].". Documento gerado automaticamente pelo site ".$url." por ".$_SESSION['userfirstname']." ".$_SESSION['userlastname']." em ".$_SESSION['sessiondate'].". INSTITUTO NACIONAL DE PESQUISAS DA AMAZÔNIA (INPA), Manaus, Brasil</name>
	<Style id=\"mylinestyle\">
      <LineStyle>
        <color>6414F0FF</color>
        <width>3</width>
      </LineStyle>
    </Style>
 ";
	$tablename = $_SESSION['userlastname']."_".$_SESSION['sessiondate'].".kml";
	$fh = fopen("temp/".$tablename, 'w') or die("nao foi possivel gerar o arquivo");
	$lin = trim($hh);
	fwrite($fh,$lin);
	}
	$idx++;
	$txt = 
"
<Placemark>
  <name>".$row['Waypoint']."</name>
  <visibility>1</visibility>
  <description>Ponto ".$row['Waypoint']." de ".$row['Data']." da  trilha ".$row['Trilha'].", marcada na expedição botânica ".$row['Viagem']." realizada entre ".$row['DateStart']." e ".$row['DateEnd']."</description>
  <Point>
<coordinates>".$row['Longitude'].",".$row['Latitude'].",".$row['Altitude']."</coordinates>
    </Point>
</Placemark>";
	fwrite($fh,$txt);
}



$qq = "SELECT exp.Name as Viagem,exp.DateStart, exp.DateEnd, trl.TrilhaID,trl.Name as Trilha FROM Expedicao_Trilhas as trl LEFT JOIN Expedicoes as exp ON exp.ViagemID=trl.ViagemID WHERE trl.ViagemID='".$viagemid."' ORDER BY trl.Name";
$res = mysql_query($qq);

$tr = 0;
while ($row = mysql_fetch_assoc($res)) {
	$idx++;
	$txt = "
<Placemark>
<name>".$row['Trilha']."</name>
  <description>Trilha de coleta da expedição botânica ".$row['Viagem']." realizada entre ".$row['DateStart']." e ".$row['DateEnd']."</description>
  <styleUrl>#mylinestyle</styleUrl>
  <LineString>
    <altitudeMode>absolute</altitudeMode>
    <coordinates>";
    fwrite($fh,$txt);
    
	$qq = "SELECT exp.Name as Viagem,exp.DateStart, exp.DateEnd, trl.TrilhaID,trl.Name as Trilha,gps.Name as Waypoint,gps.DateOriginal as Data, gps.Latitude,gps.Longitude,gps.Altitude FROM Expedicao_Trilhas as trl LEFT JOIN GPS_DATA as gps ON gps.TrilhaID=trl.TrilhaID LEFT JOIN Expedicoes as exp ON exp.ViagemID=trl.ViagemID WHERE gps.Type='TrackPoint' AND trl.TrilhaID='".$row['TrilhaID']."' ORDER BY gps.TrackName,gps.DateOriginal,gps.TimeOriginal";
$rr = mysql_query($qq);
while ($rw = mysql_fetch_assoc($rr)) {
$txt =$rw['Longitude'].",".$rw['Latitude'].",".$rw['Altitude']."
";
fwrite($fh,$txt);
}
$txt = 
"        </coordinates>
      </LineString>
    </Placemark>
";
fwrite($fh,$txt);
}
//para colocar em description
//<![CDATA[  any html text within here]]>
$txt = "
</Document>
</kml>";

fwrite($fh,$txt);
fclose($fh);


echo "<br>
<table class='myformtable' cellpadding='5' align='center' width=70%>
<thead>
<tr><td colspan=2>Resultados</td></tr>
</thead>
<tbody>
<tr>
	<td><a href='temp/".$tablename."' target='_blank'>Baixar o arquivo $tablename com waypoints da viagem $myviagem</td>
</tr>
<tr>
	<td colspan=100%><hr></td>
</tr>	
</tbody>
</table>";

}
HTMLtrailers();

?>