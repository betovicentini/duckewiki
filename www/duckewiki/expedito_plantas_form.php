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
$title = 'Método Expedito';
$body = '';
FazHeader($title,$body,$which_css,$which_java,$menu);

echo "
<br />
<table class='myformtable' align='left' cellpadding='4' >
<thead>
<tr ><td colspan='100%'>";echo GetLangVar('nameeditar')." expedito</td></tr>
</thead>
<tbody>
<form action='expedito_plantas_exec.php' method=post>
<input type='hidden' name='ispopup'  value='".$ispopup."' />";
$qq = "SELECT * FROM MetodoExpedito ORDER BY DataColeta";
$res = @mysql_query($qq,$conn);
if ($bgi % 2 == 0){$bgcolor = $linecolor2 ;} else{$bgcolor = $linecolor1 ;} $bgi++;
	echo "
<tr bgcolor = '".$bgcolor."'>
  <td class='tdsmallboldright'>Ponto de amostragem</td>
  <td class='tdformnotes' >
    <select name='expeditoid' onchange='this.form.submit();'>
      <option value=''>".GetLangVar('nameselect')."</option>
      <option value=''>------------</option>
      <option value='criar'>Criar um novo ponto</option>
      <option value=''>------------</option>";

	$qq = "SELECT exp.ExpeditoID, exp.DataColeta, IF(gps.Name<>'' OR gps.Name IS NOT NULL,CONCAT('Ponto ',gps.Name,' [',gaz.PathName,' ',exp.DataColeta,']'), '') as optnome FROM MetodoExpedito as exp LEFT JOIN GPS_DATA as gps ON exp.GPSpointID=gps.PointID  LEFT JOIN Gazetteer AS gaz ON gps.GazetteerID=gaz.GazetteerID ORDER BY gaz.PathName,exp.DataColeta,gps.Name";
	$rrr = mysql_query($qq,$conn);
		while ($row = mysql_fetch_assoc($rrr)) {
			echo "
      <option value=".$row['ExpeditoID'].">".$row['DataColeta']." - ".$row['optnome']."</option>";
		}
	echo "
    </select>
    </td>
</tr>
</form>
</tr>
</table>
<br />";
$which_java = array("<script type='text/javascript' src='javascript/myjavascripts.js'></script>",
"<!-- Create Menu Settings: (Menu ID, Is Vertical, Show Timer, Hide Timer, On Click ('all' or 'lev2'), Right to Left, Horizontal Subs, Flush Left, Flush Top) -->",
"<script type='text/javascript'>qm_create(0,false,0,500,false,false,false,false,false);</script>");
FazFooter($which_java,$calendar=FALSE,$footer=$menu);

?>
