<?php
//Este script checa se alguns campos latitude, longitude, angulo
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
$title = 'Importar Dados Passo 03a';
$body = '';
FazHeader($title,$body,$which_css,$which_java,$menu);

$fields = unserialize($_SESSION['fieldsign']);
if (!isset($$newnumericfields)) {
	$datafields = array('ANGULO','LATITUDE','LONGITUDE','X','Y','DIST','LADO','INPA_NUM','REFCOLNUM','REFHERBNUM'); 
	$newwikifields = array('Angulo','Latitude','Longitude','X','Y','Distancia','LADO','INPA_ID','RefColnum','RefHerbNum'); 
	$fields = unserialize($_SESSION['fieldsign']);
	$newdatafields = array();
	foreach ($datafields as $kk => $vv) {
    	if (in_array($vv,$fields)) {
			$ak = array_search($vv,$fields);
			$newdatafields[$ak] = array($tbprefix.$newwikifields[$kk],$vv);
		}
	}
} else {
	$newdatafields = unserialize($newnumericfields);
}

$dataproblems = array();
	foreach ($newdatafields as $orgcol => $novascols) {
		$cll = $novascols[0];
		$brcoln = $novascols[1];

		if ($brcoln=='ANGULO') {
			$qq = "ALTER TABLE ".$tbname." ADD COLUMN ".$cll." FLOAT DEFAULT NULL";
			@mysql_query($qq,$conn);
			$qq = "UPDATE `".$tbname."` SET `".$cll."`=checkangulo(`".$orgcol."`) WHERE `".$orgcol."`<>'' AND `".$orgcol."` IS NOT NULL AND `".$cll."` IS NULL";
			mysql_query($qq,$conn);
			$qq = "SELECT * FROM `".$tbname."` WHERE `".$orgcol."`<>'' AND `".$orgcol."` IS NOT NULL AND `".$cll."` IS NULL";
			$rr = mysql_query($qq,$conn);
			$nr = mysql_numrows($rr);
			if ($nr>0) {
				$dataproblems[$orgcol] = array("Os valores precisam ser menores que 360 e maiores que 0, pois é angulo",'angulo');
			}
		}
		if ($brcoln=='LATITUDE' || $brcoln=='LONGITUDE') {
			$qq = "ALTER TABLE ".$tbname." ADD COLUMN ".$cll." FLOAT DEFAULT NULL";
			@mysql_query($qq,$conn);
			$qq = "UPDATE `".$tbname."` SET `".$cll."`=checkcoordenadas(`".$orgcol."`,'".$brcoln."') WHERE `".$orgcol."`<>'' AND `".$orgcol."` IS NOT NULL AND `".$cll."` IS NULL";
			mysql_query($qq,$conn);
			$qq = "SELECT * FROM `".$tbname."` WHERE `".$orgcol."`<>'' AND `".$orgcol."` IS NOT NULL AND `".$cll."` IS NULL";
			$rr = mysql_query($qq,$conn);
			$nr = mysql_numrows($rr);
			if ($nr>0) {
				$dataproblems[$orgcol] = array("Os valores nessa coluna não correspondem a valores de ".$brcoln." (em décimos de grau)",'latlong',$brcoln);
			}
		}    
		if ($brcoln=='X' || $brcoln=='Y' || $brcoln=='DIST' || $brcoln=='INPA_NUM') {
			$qq = "ALTER TABLE ".$tbname." ADD COLUMN ".$cll." FLOAT DEFAULT NULL";
			mysql_query($qq,$conn);
			$qq = "UPDATE `".$tbname."` SET `".$cll."`=checanumericos(`".$orgcol."`) WHERE `".$orgcol."`<>'' AND `".$orgcol."` IS NOT NULL AND `".$cll."` IS NULL";
			mysql_query($qq,$conn);
			$qq = "SELECT * FROM `".$tbname."` WHERE `".$orgcol."`<>'' AND `".$orgcol."` IS NOT NULL AND `".$cll."` IS NULL";
			$rr = mysql_query($qq,$conn);
			$nr = mysql_numrows($rr);
			if ($nr>0) {
				$dataproblems[$orgcol] = array("Os valores nessa coluna não correspondem a valores de ".$brcoln." (em décimos de grau)",'numerico');
			}
		} 
		//PRECISA MELHORAR O FILTRO DE VALORES NESTE PROXIMO ITEM... SIGLAS DE HERBARIOS
		if ($brcoln=='REFCOLNUM' || $brcoln=='REFHERBNUM'  || $brcoln=='REFHERBARIUM') {
			$qq = "ALTER TABLE ".$tbname." ADD COLUMN ".$cll." VARCHAR(100) DEFAULT ''";
			mysql_query($qq,$conn);
			$qq = "UPDATE `".$tbname."` SET `".$cll."`=TRIM(`".$orgcol."`) WHERE `".$orgcol."`<>'' AND `".$orgcol."` IS NOT NULL  AND `".$cll."`=''";
			mysql_query($qq,$conn);
		} 

		if ($brcoln=='LADO') {
			$qq = "ALTER TABLE ".$tbname." ADD COLUMN ".$cll." VARCHAR(10) DEFAULT ''";
			mysql_query($qq,$conn);
			$qq = "UPDATE `".$tbname."` SET `".$cll."`=checalado(`".$orgcol."`) WHERE `".$orgcol."`<>'' AND `".$orgcol."` IS NOT NULL AND `".$cll."`=''";
			mysql_query($qq,$conn);
			$qq = "SELECT * FROM `".$tbname."` WHERE `".$orgcol."`<>'' AND `".$orgcol."` IS NOT NULL AND `".$cll."`='ERRO'";
			$rr = mysql_query($qq,$conn);
			$nr = mysql_numrows($rr);
			if ($nr>0) {
				$dataproblems[$orgcol] = array("Os valores nessa coluna não correspondem a valores de ".$brcoln." que devem ser E ou D",'lado');
			}
		}  
	}

//tem problemas em colunas avisa e interrompe a importacao
if (count($dataproblems)>0) {
echo "<form action='import-data-step3a.php' method='post'>
<br />
<table align='center' class='myformtable' cellpadding='5'>
<thead>
 <tr><td colspan='100%'>Os seguintes erros em colunas com Datas foram encontrados!<td></tr>
  <tr class='subhead'>
  <td>Coluna</td>
  <td>Erro</td>
  <td>O que fazer?</td>
 </tr>
</thead>
<tbody>
  <input type='hidden' name='newnumericfields' value='".serialize($newdatafields)."' />";
	foreach ($ppost as $kk => $vv) {
	if (!empty($vv)) {
		echo "
  <input type='hidden' name='".$kk."' value='".$vv."' />"; 
	}
}
 	foreach ($dataproblems as $orgcol => $vv) {
 		if ($bgi % 2 == 0){$bgcolor = $linecolor2 ;}  else{$bgcolor = $linecolor1 ;} $bgi++;
			echo "
<tr bgcolor = '".$bgcolor."'><td>$orgcol</td><td>".$vv[0]."</td>";
if (!empty($newdatafields[$orgcol])) {
$cln = $newdatafields[$orgcol][0];
echo "<td align='center'>
<input id='butidx' type='button' style=\"font-size:90%;background-color:#0066CC; color: white;border: thin outset gray;padding: 0.1em\" value='Corrigir' ";
$myurl ="checkdatas-popup.php?colname=".$cln."&orgcol=".$orgcol."&tbname=".$tbname."&buttonidx=butidx&datatipo=".$vv[1];
if (!empty($vv[2])) { $myurl .= "&latlonglink=".$vv[2];}  
echo " onclick = \"javascript:small_window('$myurl',800,400,'Corrigir valores em alguns campos');\" /></td>";

}
echo "</tr>"; 
	 	}

if ($bgi % 2 == 0){$bgcolor = $linecolor2 ;}  else{$bgcolor = $linecolor1 ;} $bgi++;
echo "
<tr bgcolor = '".$bgcolor."'>
  <td align='center' colspan='100%'><input type='submit' value='".GetLangVar('namecontinuar')."' class='bsubmit' /></td>
</tr>
</tbody>
</table>
</form>";
	} 
	else {
		$numericchecked= TRUE;
	}
if ($numericchecked==TRUE) {
	echo "
<form name='myform' action='import-data-hub.php' method='post'>";
//coloca as variaveis anteriores
	foreach ($ppost as $kk => $vv) {
	echo "
  	<input type='hidden' name='".$kk."' value='".$vv."' />"; 
	}
echo "<script language=\"JavaScript\">setTimeout('document.myform.submit()',0.0001);</script>
  </form>";
}
$which_java = array("<script type='text/javascript' src='javascript/myjavascripts.js'></script>",
"<!-- Create Menu Settings: (Menu ID, Is Vertical, Show Timer, Hide Timer, On Click ('all' or 'lev2'), Right to Left, Horizontal Subs, Flush Left, Flush Top) -->",
"<script type='text/javascript'>qm_create(0,false,0,500,false,false,false,false,false);</script>");
FazFooter($which_java,$calendar=FALSE,$footer=$menu);

?>


