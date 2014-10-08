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
"<link rel='stylesheet' type='text/css' href='css/cssmenu.css' />",
"<link rel='stylesheet' type='text/css' media='screen' href='css/autosuggest.css' >"
);
$which_java = array(
"<script type='text/javascript' src='css/cssmenuCore.js'></script>",
"<script type='text/javascript' src='css/cssmenuAddOns.js'></script>",
"<script type='text/javascript' src='css/cssmenuAddOnsItemBullet.js'></script>",
"<script type='text/javascript' src='javascript/ajax_framework.js'></script>"
);
$title = 'GPS Ponto BATCHLocality';
$body = '';
FazHeader($title,$body,$which_css,$which_java,$menu);

unset($_SESSION["GPSPOINTIDS"]);
echo "
<br />
<table class='myformtable' align='center' cellpadding='4' >
<thead>
<tr ><td colspan='100%'>Mudar localidade de 1 ou mais pontos</td></tr>
</thead>
<tbody>
<form action='gps-ponto-batchlocality-exec.php' method=post>
  <input type='hidden' name='ispopup' value='".$ispopup."' />
";
if ($bgi % 2 == 0){$bgcolor = $linecolor2 ;} else{$bgcolor = $linecolor1 ;} $bgi++;
	echo "
<tr bgcolor = '".$bgcolor."'>
<td colspan='100%'>
<table>
<tr>
  <td class='tdsmallboldright'>Selecione um ou mais pontos</td>
  <td style='font-size:0.6em'>
    <select name='gpspointids[]' multiple size='20' style='width: 500px; '>";
$qq = "SELECT DISTINCT CONCAT(gps.DateOriginal,' ', IF(gps.Name<>'' OR gps.Name IS NOT NULL, CONCAT('Ponto ',gps.Name,' -- '),''),gaz.PathName,' [',Municipio,' ',Province,' ',Country, IF(gps.FileName<>'',CONCAT(' -- ' ,gps.FileName),''),']') as nome, PointID as nomeid FROM GPS_DATA as gps LEFT JOIN Gazetteer as gaz USING(GazetteerID) LEFT  JOIN Municipio  USING(MunicipioID) LEFT  JOIN Province USING(ProvinceID) LEFT  JOIN Country USING(CountryID) LEFT JOIN Equipamentos as equip ON gps.GPSName=equip.EquipamentoID WHERE LOWER(gps.Type)='waypoint' ";
if ($filtro==1) {
	$qq .= " AND gps.AddedBy='".$uuid."'";
	$chf = "checked";
}
if (!isset($filtroordem)) {
	$filtroordem=4;
}
if ($filtroordem==1) {
	$fo1 = "checked";
	$qq .= " ORDER BY gps.Latitude,gps.Longitude,gps.DateOriginal,gaz.PathName,gps.Name";
}
if ($filtroordem==2) {
	$fo2 = "checked";
	$qq .= " ORDER BY gps.Name,gaz.PathName,gps.DateOriginal,gps.Latitude,gps.Longitude";
}
if ($filtroordem==3) {
	$fo3 = "checked";
	$qq .= " ORDER BY gaz.PathName,gps.DateOriginal,gps.Latitude,gps.Longitude,gps.Name";
}
if ($filtroordem==4) {
	$fo4 = "checked";
	$qq .= " ORDER BY gps.DateOriginal,gps.Name,gps.Latitude,gps.Longitude,gaz.PathName";
}
		$rrr = mysql_query($qq,$conn);
		while ($row = mysql_fetch_assoc($rrr)) {
			echo "
      <option value=".$row['nomeid'].">".$row['nome']."</option>";
			}
		if (mysql_numrows($rrr)==0) {
			echo "
      <option value=''>Não há pontos cadastrados!</option>";
		}	
		echo "
    </select></td>
</tr>";

if ($bgi % 2 == 0){$bgcolor = $linecolor2 ;} else{$bgcolor = $linecolor1 ;} $bgi++;
	echo "
<tr bgcolor = '".$bgcolor."'>
<td class='tdsmallbold'>Atribuir&nbsp;a&nbsp;localidade</td><td>";
$locality = 'Digite para buscar localidade, municipio, provincia, pais';
autosuggestfieldval3('search-gazetteer.php','locality',$locality,'localres','GazetteerID',$GazetteerID,true,60);
echo "</td></tr>";
if ($bgi % 2 == 0) {$bgcolor = $linecolor2 ;} else {$bgcolor = $linecolor1 ;} $bgi++;
echo "
<tr bgcolor = '".$bgcolor."'><td colspan='100%' align='center'><input type='submit' value='".GetLangVar('namesalvar')."' class='bsubmit' /></td></tr>
</form>";
if ($bgi % 2 == 0){$bgcolor = $linecolor2 ;} else{$bgcolor = $linecolor1 ;} $bgi++;
echo "
<form action='gps-ponto-batchlocality.php' method=post>
  <input type='hidden' name='ispopup' value='".$ispopup."' />
<tr bgcolor = '".$bgcolor."'>
  <td class='tdsmallbold'>Filtrar por:</td>
  <td>
    <table>
      <tr>
        <td><input type='checkbox' name='filtro' $chf value='1' /></td>
        <td>Pontos que eu inseri/editei apenas</td>
      </tr>
      <tr><td><input type='radio' name='filtroordem' $fo1 value='1' /></td><td>Ordenar por latitude+longitude (padrão)</td></tr>
      <tr><td><input type='radio' name='filtroordem' $fo2 value='2' /></td><td>Ordenar por nome do ponto</td></tr>
      <tr><td><input type='radio' name='filtroordem' $fo3 value='3' /></td><td>Ordenar por localidade</td></tr>
      <tr><td><input type='radio' name='filtroordem' $fo4 value='4' /></td><td>Ordenar por data</td></tr>
    </table>
  </td>
</tr>";
if ($bgi % 2 == 0){$bgcolor = $linecolor2 ;} else{$bgcolor = $linecolor1 ;} $bgi++;
echo "
<tr bgcolor = '".$bgcolor."'><td colspan='100%' align='center'><input type='submit' value='Filtrar/ordenar' class='bblue' /></td></tr>
</form></tbody>
</table>
<br />";
$which_java = array("<script type='text/javascript' src='javascript/myjavascripts.js'></script>",
"<!-- Create Menu Settings: (Menu ID, Is Vertical, Show Timer, Hide Timer, On Click ('all' or 'lev2'), Right to Left, Horizontal Subs, Flush Left, Flush Top) -->",
"<script type='text/javascript'>qm_create(0,false,0,500,false,false,false,false,false);</script>");
FazFooter($which_java,$calendar=FALSE,$footer=$menu);

?>