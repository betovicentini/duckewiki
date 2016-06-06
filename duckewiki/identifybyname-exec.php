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
$title = 'Substituir identificação';
$body = '';
FazHeader($title,$body,$which_css,$which_java,$menu);

//verifica se ha registros para substituir
if (!empty($nomesciid) && $nomesciid!='nomesciid' && $filtro>0) {
		$taxid = explode("_",$nomesciid);
		$idd = $taxid[1];
		if ($taxid[0]=='famid') { $taxcol = "FamiliaID='".$idd."' AND GeneroID=0 " ;	}
		if ($taxid[0]=='genusid') { $taxcol = "GeneroID='".$idd."' AND EspecieID=0 " ;	}
		if ($taxid[0]=='speciesid') { $taxcol = "EspecieID='".$idd."' AND InfraEspecieID=0 " ;	}
		if ($taxid[0]=='infspid') { $taxcol = "InfraEspecieID='".$idd."'";  }

		$qq = "SELECT * FROM Especimenes JOIN Identidade USING(DetID) WHERE ".$taxcol." AND FiltrosIDS LIKE '%filtroid_".$filtro."%'";
		$res = mysql_query($qq,$conn);		
		$numspecs = mysql_numrows($res);

		$qq = "SELECT * FROM Plantas JOIN Identidade USING(DetID) WHERE ".$taxcol." AND FiltrosIDS LIKE '%filtroid_".$filtro."%'";
		$res = mysql_query($qq,$conn);		
		$numplantas = mysql_numrows($res);
} 

//cadastro da nova identificacao
if (($numplantas>0 || $numspecs>0) && !empty($detset)) {
	$detarray = unserialize($detset);
	//insere nova determinacao que será usada pelo conjunto de amostras
	$newdetid = InsertIntoTable($detarray,'DetID','Identidade',$conn);
}

$erro=0;
$salvo=0;
//se o cadastro da identificacao foi correto
if ($newdetid>0) {
	$udate = $_SESSION['sessiondate'];
	$chgby = $_SESSION['userid'];
	$qq = "DROP TABLE temp_detall_".$chgby;
	mysql_query($qq,$conn);
	if ($numspecs>0) {
		$qq = "CREATE TABLE temp_detall_".$chgby." (SELECT Especimenes.*,".$chgby." as ChangedBy, '".$udate."' as ChangedDate FROM Especimenes JOIN Identidade USING(DetID) WHERE ".$taxcol." AND FiltrosIDS LIKE '%filtroid_".$filtro."%')";
		mysql_query($qq,$conn);
		$Qq = "ALTER TABLE temp_detall_".$chgby." CHANGE EspecimenID EspecimenID INT( 10 ) NOT NULL";
		mysql_query($Qq,$conn);
		$QQ = "ALTER TABLE temp_detall_".$chgby." DROP PRIMARY KEY";
		mysql_query($QQ,$conn);
		$qcol = "SHOW COLUMNS FROM  temp_detall_".$chgby;
		$rr = mysql_query($qcol,$conn);
		$cols = array();
		while ($row = mysql_fetch_assoc($rr)) {
			$cols[] = $row['Field'];
		}
		$qq = "INSERT INTO ChangeEspecimenes (";
		$i=0;
		$ncols = count($cols)-1;
		foreach ($cols as $colum ) {
			if ($i<$ncols) {
				$qq = $qq.$colum.",";
			} else {
				$qq = $qq.$colum;
			}
			$i++;
		}
		$qq = $qq.") (SELECT * FROM temp_detall_".$chgby.")";
		mysql_query($qq,$conn);
		$qu = "ALTER TABLE temp_detall_".$chgby."  ADD TempID INT(10) unsigned NOT NULL auto_increment PRIMARY KEY";
		mysql_query($qu,$conn);
		$sql = "UPDATE Especimenes INNER JOIN temp_detall_".$chgby." as llixo ON Especimenes.EspecimenID=llixo.EspecimenID SET Especimenes.DetID='".$newdetid."' WHERE llixo.ChangedBy='".$chgby."'";
		$updatedpls = mysql_query($sql,$conn);
		if ($updatedpls) {
			$salvo++;
		} else {
			$erro++;
		}
	}
	if ($numplantas>0) {
		$qq = "CREATE TABLE temp_detall_".$chgby." (SELECT Plantas.*,".$chgby." as ChangedBy, '".$udate."' as ChangedDate FROM Plantas JOIN Identidade USING(DetID) WHERE ".$taxcol." AND FiltrosIDS LIKE '%filtroid_".$filtro."%')";
		mysql_query($qq,$conn);
		$Qq = "ALTER TABLE temp_detall_".$chgby." CHANGE PlantaID PlantaID INT( 10 ) NOT NULL";
		mysql_query($Qq,$conn);
		$QQ = "ALTER TABLE temp_detall_".$chgby." DROP PRIMARY KEY";
		mysql_query($QQ,$conn);
		$qcol = "SHOW COLUMNS FROM  temp_detall_".$chgby;
		$rr = mysql_query($qcol,$conn);
		$cols = array();
		while ($row = mysql_fetch_assoc($rr)) {
			$cols[] = $row['Field'];
		}
		$qq = "INSERT INTO ChangePlantas (";
		$i=0;
		$ncols = count($cols)-1;
		foreach ($cols as $colum ) {
			if ($i<$ncols) {
				$qq = $qq.$colum.",";
			} else {
				$qq = $qq.$colum;
			}
			$i++;
		}
		$qq = $qq.") (SELECT * FROM temp_detall_".$chgby.")";
		mysql_query($qq,$conn);
		$qu = "ALTER TABLE temp_detall_".$chgby."  ADD TempID INT(10) unsigned NOT NULL auto_increment PRIMARY KEY";
		mysql_query($qu,$conn);
		$sql = "UPDATE Plantas INNER JOIN temp_detall_".$chgby." as llixo ON Plantas.PlantaID=llixo.PlantaID SET Plantas.DetID='".$newdetid."' WHERE llixo.ChangedBy='".$chgby."'";
		$updatedpls = mysql_query($sql,$conn);
		if ($updatedpls) {
			$salvo++;
		} else {
			$erro++;
		}
	}
	if ($salvo>0 || $erro==0) {
		echo "
<br />
  <table cellpadding=\"1\" width='50%' align='center' class='success'>
    <tr><td class='tdsmallbold' align='center'> ".GetLangVar('sucesso1')."</td></tr>
  </table>
<br />";
		TaxonomySimple($all=false,$conn);
	}

	if ($erro>0) {
		echo "
<br />
<table cellpadding=\"1\" width='50%' align='center' class='erro'>
  <tr><td class='tdsmallbold' align='center'> ".GetLangVar('erro1')."</td></tr>
</table>
<br />";
	}
} else {
	echo "
<br />
<table cellpadding=\"1\" width='50%' align='center' class='erro'>
  <tr><td class='tdsmallbold' align='center'> ".GetLangVar('erro1')."</td></tr>
</table>
<br />";
}
$which_java = array("<script type='text/javascript' src='javascript/myjavascripts.js'></script>",
"<!-- Create Menu Settings: (Menu ID, Is Vertical, Show Timer, Hide Timer, On Click ('all' or 'lev2'), Right to Left, Horizontal Subs, Flush Left, Flush Top) -->",
"<script type='text/javascript'>qm_create(0,false,0,500,false,false,false,false,false);</script>");
FazFooter($which_java,$calendar=FALSE,$footer=$menu);
?>