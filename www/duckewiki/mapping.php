<?php
session_start();
include "functions/HeaderFooter.php";
include "functions/SelectOptions.php";

@extract($_POST);
//echopre($_POST);

$lang = $_SESSION['lang'];
$dbname = $_SESSION['dbname'];
$conn = ConectaDB($dbname);

if ($filtro>0) { 
	$qq = "SELECT * FROM Filtros WHERE FiltroID='".$filtro."'";	
} else {
	$qq = "SELECT * FROM Filtros";
	$res = mysql_query($qq,$conn);
	$nfiltros = mysql_numrows($res);
	$random = rand(1,$nfiltros);	
	$qq = "SELECT * FROM Filtros WHERE EspecimenesIDS>0 ORDER BY FiltroID DESC LIMIT ".$random.",1";
}
$res = mysql_query($qq,$conn);
$rs = mysql_fetch_assoc($res);
$filtro = $rs['FiltroID'];
$nsamp = $rs['NsamplesCannMap'];

if ($nsamp==0) { header('location: mapping.php'); }

$filename = "temp/Filtro_ID-".$filtro.".json";
//echo "f:".$filtro." ns:".$nsamp." f:".$filename;

if (file_exists($filename) && empty($updatefiltros))  {
		$st= ceil($nsamp/10);
		//echo "s:".$st." n:".$nsamp;
		$optvals = range(1,$nsamp,$st);
		$optvals = array_merge((array)$optvals,(array)$nsamp);
		$optvals = array_unique($optvals);
} elseif ($filtro>0) {
		header("location: mapping-prep.php?filtro=$filtro");
} 

HTMLheadersMap($filename);

if ($nsamp==0 && $filtro>0) {
	echo "<br/><table class='erro' align='center' width='70%'><tr><td><b>Lat e Long faltando para todas as amostras do filtro</b></td></tr></table>";
}
echo "
<div style=\"float:left; height: 40px; width:1000px; border:0px\">
<table class='topoflist'>
<tr>
  <td>Agrupar amostras</td>
  <td><input type=\"checkbox\" checked=\"checked\" id=\"usegmm\"/></td>
  <td>Mostrar amostras:</td>
  <td>
    <select id=\"nummarkers\">";
	foreach ($optvals as $vv) {
		echo "
      <option value=\"".$vv."\"";
		if ($vv==$nsamp) {echo " selected=\"selected\"";}
		echo " >".$vv."</option>";
	}
	echo "
    </select>
    <!--- <span>Time used: <span id=\"timetaken\"></span> ms</span> --->
    <input type='hidden' id=\"timetaken\">
  </td>
  <td>".GetLangVar('namefiltro').":</td>
  <td>
  <form action='mapping.php' method='post'>
    <select name='filtro' onchange='this.form.submit();'>";
		if ($filtro>0) {
			$qq = "SELECT * FROM Filtros WHERE FiltroID='".$filtro."'";
			$resf = @mysql_query($qq,$conn);
			$rf = @mysql_fetch_assoc($resf);
			echo "
      <option selected='selected' value='".$rf['FiltroID']."'>".$rf['FiltroName']."</option>";
		}
			echo "
      <option value=''>".GetLangVar('nameselect')."</option>";
			$qq = "SELECT * FROM Filtros ORDER BY FiltroName";
			$res = @mysql_query($qq,$conn);
			while ($rr = @mysql_fetch_assoc($res)) {
				echo "
      <option value='".$rr['FiltroID']."'>".$rr['FiltroName']."</option>";
			}	
			echo "
    </select>
  </form>
  </td>
</tr>
</table>
</div>
<div style=\"float:left; height: 600px; width:1000px; border:0px\">
  <div id=\"panel\" style=\"height: 500px; width:330px; border:0.1em solid black;\">
    <table align='center'>
      <tr><td align='center'>Lista de amostras</td></tr>
      <tr><td align='center'><div id=\"markerlist\"></div></td></tr>
    </table>
  </div>
  <div id=\"map-container\" style=\"height: 500px; width:650px; border:0.1em solid black;\">
    <div id=\"map\"></div>
  </div>
</div>
<br/>
<br/>
<br/>
<br/>
<br/>
<br/>
<br/>
<br/>
<br/>
<br/>
<br/>
<br/>
<br/>
<br/>
<br/>
<br/>
<br/>
<br/>
<br/>
<br/>
<br/>
<br/>
<br/>
<br/>
<br/>
<br/>
";

HTMLtrailers();

?>