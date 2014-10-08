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
$ispopup=1;
if ($ispopup==1) {
	$menu = FALSE;
} else {
	$menu = TRUE;
}
$title = '';
$which_css = array(
"<link href='css/geral.css' rel='stylesheet' type='text/css' >",
"<link rel='stylesheet' type='text/css' href='css/cssmenu.css' >"
);
$which_java = array(
"<script type='text/javascript' src='css/cssmenuCore.js'></script>",
"<script type='text/javascript' src='css/cssmenuAddOns.js'></script>",
"<script type='text/javascript' src='css/cssmenuAddOnsItemBullet.js'></script>"
);
$body= '';
$title = "Corrigindo valores no arquivo a ser importado";
FazHeader($title,$body,$which_css,$which_java,$menu);

if ($final==1) {
	foreach ($inputs as $orvaluefield => $novovalor) {
		$nv = " ".$novovalor;
		$nvv = " ".$orvaluefield;
		if ($nv<>$nvv) {
			$qq = "UPDATE `".$tbname."` SET `".$orgcol."`='".$novovalor."' WHERE `".$orgcol."`='".$orvaluefield."'";
			mysql_query($qq,$conn);
		}
	}
	if ($datatipo=='ano') {
		$qq = "UPDATE `".$tbname."` SET `".$colname."`=date_check_ano(`".$orgcol."`) WHERE `".$orgcol."`<>'' AND `".$orgcol."` IS NOT NULL AND `".$colname."`=0";
	}
	if ($datatipo=='dia') {
		$qq = "UPDATE `".$tbname."` SET `".$colname."`=date_check_dd(`".$orgcol."`) WHERE `".$orgcol."`<>'' AND `".$orgcol."` IS NOT NULL AND `".$colname."`=0";
	}
	if ($datatipo=='mes') {
		$qq = "UPDATE `".$tbname."` SET `".$colname."`=date_check_mm(`".$orgcol."`) WHERE `".$orgcol."`<>'' AND `".$orgcol."` IS NOT NULL AND `".$colname."`=0";
	}
	if ($datatipo=='data') {
		$qq = "UPDATE `".$tbname."` SET `".$colname."`=date_check(`".$orgcol."`) WHERE `".$orgcol."`<>'' AND `".$orgcol."` IS NOT NULL AND `".$colname."` IS NULL";
	}
	if ($datatipo=='angulo') {
		$qq = "UPDATE `".$tbname."` SET `".$colname."`=checkangulo(`".$orgcol."`) WHERE `".$orgcol."`<>'' AND `".$orgcol."` IS NOT NULL AND `".$colname."` IS NULL";
	}
	if ($datatipo=='latlong') {
		$qq = "UPDATE `".$tbname."` SET `".$colname."`=checkcoordenadas(`".$orgcol."`,'".$latlonglink."') WHERE `".$orgcol."`<>'' AND `".$orgcol."` IS NOT NULL AND `".$colname."` IS NULL";
	}
	if ($datatipo=='numerico') {
		$qq = "UPDATE `".$tbname."` SET `".$colname."`=checanumericos(`".$orgcol."`) WHERE `".$orgcol."`<>'' AND `".$orgcol."` IS NOT NULL AND `".$colname."` IS NULL";
	}
	if ($datatipo=='intervalo') {
		$qq = "UPDATE `".$tbname."` SET `".$colname."`=IF(IsInteger(`".$orgcol."`),TRIM(`".$orgcol."`)+0,0) WHERE `".$orgcol."`<>'' AND `".$orgcol."` IS NOT NULL AND `".$colname."`=0";
	}
	if ($datatipo=='lado') {
		$qq = "UPDATE `".$tbname."` SET `".$colname."`=checalado(`".$orgcol."`) WHERE `".$orgcol."`<>'' AND `".$orgcol."` IS NOT NULL AND `".$colname."`='ERRO'";
	}
	mysql_query($qq,$conn);
}
$query = "SELECT `".$orgcol."`, COUNT(*) AS nn FROM `".$tbname."`  WHERE `".$orgcol."`<>'' AND `".$orgcol."` IS NOT NULL AND (`".$colname."`=0 OR `".$colname."` IS NULL) GROUP BY `".$orgcol."` LIMIT 0,30";
$res = mysql_query($query,$conn);
$nres = mysql_numrows($res);
$erro=0;
if ($nres>0) {
echo "
<br />
<table align='center' class='myformtable' cellpadding='7'>
<thead>
  <tr><td colspan='100%'>Os seguintes valores da coluna ".$orgcol." tem erros</td>
  </tr>
    <tr class='subhead'>
	<td >Número de registros</td>
    <td >Valor original</td>
    <td >Corrigir para</td>
    </td>
  </tr>
</thead>
<tbody>
<form action='checkdatas-popup.php' method='post'>
  <input type='hidden' name='tbname' value='".$tbname."' />
  <input type='hidden' name='colname' value='".$colname."' />
  <input type='hidden' name='orgcol' value='".$orgcol."' />
  <input type='hidden' name='buttonidx' value='".$buttonidx."' />
  <input type='hidden' name='datatipo' value='".$datatipo."' />
  <input type='hidden' name='latlonglink' value='".$latlonglink."' />
  <input type='hidden' name='final' value='1' />";
while ($row = mysql_fetch_assoc($res)) {
	$data= $row[$orgcol];	
	if ($bgi % 2 == 0){$bgcolor = $linecolor2 ;}  else{$bgcolor = $linecolor1 ;} $bgi++;
		echo "
<tr bgcolor = '".$bgcolor."'>
  <td class='tdsmallbold' align='center'>".$row['nn']."</td>
  <td class='tdsmallbold' align='center'>".$data."</td>
  <td class='tdformnotes' >";
if ($datatipo=='ano' || $datatipo=='dia' || $datatipo=='mes' || $datatipo=='angulo' || $datatipo=='numerico') {
echo "<input type='text' name='inputs[$data]' value='".$data."' /><br />Númérico apenas</td></tr>";
}
if ($datatipo=='data') {
echo "<input type='text' name='inputs[$data]' value='".$data."' /><br />AAAA-MM-DD  e.g. 2011-07-01</td></tr>";

}
if ($datatipo=='latlong') {
echo "<input type='text' name='inputs[$data]' value='".$data."' /><br />Em décimo de graus, S e W valores negativos</td></tr>";
}
if ($datatipo=='lado') {
echo "<input type='text' name='inputs[$data]' value='".$data."' /><br />Letras E ou D apenas</td></tr>";
}  
	}
if ($bgi % 2 == 0){$bgcolor = $linecolor2 ;}  else{$bgcolor = $linecolor1 ;} $bgi++;
echo "
  <tr bgcolor = '".$bgcolor."'>
  <td align='center' colspan='100%'><input type='submit' value='".GetLangVar('namesalvar')."' class='bsubmit' /></td></tr>
</tbody>
</table>
</form>";
} else {
echo "
  <form >
    <script language=\"JavaScript\">
      setTimeout( function() { changebutton('".$buttonidx."','Foram cadastrados');},0.0001);
    </script>
  </form>";
}

$which_java = array("<script type='text/javascript' src='javascript/myjavascripts.js'></script>",
"<!-- Create Menu Settings: (Menu ID, Is Vertical, Show Timer, Hide Timer, On Click ('all' or 'lev2'), Right to Left, Horizontal Subs, Flush Left, Flush Top) -->",
"<script type='text/javascript'>qm_create(0,false,0,500,false,false,false,false,false);</script>");
FazFooter($which_java,$calendar=FALSE,$footer=$menu);

?>