<?php
//Start session
ini_set("memory_limit","-1");
ini_set("mysql.allow_persistent","-1");

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
$which_css = array(
"<link href='css/geral.css' rel='stylesheet' type='text/css' />",
"<link href='css/jquery-ui.css' rel='stylesheet' type='text/css' />"
);
$which_java = array(
);
$body='';
$title = 'Script Teste Executa';
FazHeader($title,$body,$which_css,$which_java,$menu);

//$qn = "SELECT * FROM (SELECT list.ProcessoID,list.EspecimenID,list.Herbaria,list.INPA, COUNT(*) as nn FROM `ProcessosLIST` AS list LEFT JOIN ProcessosEspecs as prc USING(ProcessoID) WHERE prc.ProcessoID=18 AND (list.INPA IS NOT NULL) GROUP BY CONCAT(ProcessoID,EspecimenID, INPA)) AS zz WHERE zz.nn>1";
////echo $qn."<br />";
//$rn = mysql_query($qn,$conn);
//while($rw = mysql_fetch_assoc($rn)) {
//$herb = $rw['Herbaria'];
//if ($herb=='' OR empty($herb)) {
//	$herb = " ((Herbaria IS NULL) OR TRIM(Herbaria)='') ";
//} else {
//	$herb = " Herbaria='".$herb."'";
//}
//$inpa = $rw['INPA'];
//if ($inpa=='' OR empty($inpa)) {
//	$inpa = " ((INPA IS NULL) OR TRIM(INPA)='' OR INPA=0) ";
//} else {
//	$inpa = " INPA=".$inpa;
//}
//	$qz = "SELECT ProcessosListID FROM `ProcessosLIST` WHERE ProcessoID='".$rw['ProcessoID']."' AND EspecimenID='".$rw['EspecimenID']."' AND ".$inpa."  AND EXISTE=1 ORDER BY ProcessosListID LIMIT 0,1";
//	//echo $qz."<br />";
//	$rz = mysql_query($qz);
//	$nrr = mysql_numrows($rz);
//	if ($nrr==0) {
//		$qz = "SELECT ProcessosListID FROM `ProcessosLIST` WHERE ProcessoID='".$rw['ProcessoID']."' AND EspecimenID='".$rw['EspecimenID']."' AND ".$inpa."  ORDER BY ProcessosListID LIMIT 0,1";
//		//echo $qz."<br />";
//		$rz = mysql_query($qz);
//	}
//	$rww = mysql_fetch_assoc($rz);
//	$keep = $rww['ProcessosListID'];
//	if ($keep>0) {
//		$qzz = "DELETE  FROM `ProcessosLIST` WHERE ProcessoID='".$rw['ProcessoID']."' AND EspecimenID='".$rw['EspecimenID']."' AND ".$inpa." AND ProcessosListID<>".$keep;
//		//echo $qzz."<br />";
//		$del = mysql_query($qzz);
//		if ($del) {
//			echo "=<br />";
//			session_write_close();
//		}
//		
//	
//	} 
//
//}
//
//
//
//
IF ($final==1) {
	$dataar = unserialize($data);
	echopre($dataar);
	if ($escolha==1) {
		$arrayofvv = array('DetID' => $dataar['plDetID']);
		//ATUALIZA IDENTIFICACAO DA AMOSTRA
		$especimenid = $dataar['EspecimenID'];
		CreateorUpdateTableofChanges($especimenid,'EspecimenID','Especimenes',$conn);
		$newupdate = UpdateTable($especimenid,$arrayofvv,'EspecimenID','Especimenes',$conn);
	}
	if ($escolha==2) {
		$arrayofvv = array('DetID' => $dataar['specDetID']);
		//ATUALIZA IDENTIFICACAO DA PLANTA
		$plantaid = $dataar['PlantaID'];
		CreateorUpdateTableofChanges($plantaid,'PlantaID','Plantas',$conn);
		$newupdate = UpdateTable($plantaid,$arrayofvv,'PlantaID','Plantas',$conn);
	}
}
$qq = "SELECT pl.DetID as plDetID, pl.PlantaID, gettaxonname(pl.DetID,1,0) AS plNOME, plpes.Abreviacao as plDetBy, iddpl.DetDate as plDATA, spec.DetID as specDetID, spec.EspecimenID,spec.PlantaID AS SPEC_PlantaID,   gettaxonname(spec.DetID,1,0) AS specNOME, specpes.Abreviacao as specDetBy,  iddspec.DetDate as specDATA FROM Especimenes as spec JOIN Plantas as pl USING(PlantaID) LEFT JOIN Identidade as iddpl ON iddpl.DetID=pl.DetID  LEFT JOIN Identidade as iddspec ON iddspec.DetID=spec.DetID LEFT JOIN Pessoas as plpes ON plpes.PessoaID=iddpl.DetByID LEFT JOIN Pessoas as specpes ON specpes.PessoaID=iddspec.DetByID  WHERE (iddspec.FamiliaID<>iddpl.FamiliaID OR iddspec.GeneroID<>iddpl.GeneroID OR iddspec.EspecieID<>iddpl.EspecieID OR iddspec.InfraEspecieID<>iddpl.InfraEspecieID) LIMIT 0,1";
$res = mysql_query($qq,$conn);
$nres = mysql_numrows($res);
if ($nres>0) {
$row = mysql_fetch_assoc($res);
//echopre($row);
echo "
<form action='ScriptTeste.php' method='post'>
<input type='hidden' name='data' value='".serialize($row)."' >
<input type='hidden' name='final' value='1' >
ESCOLHA O REGISTRO VALIDO
<table padding=7>
<tr>
<td><input type='radio' name='escolha'  value=1 onclick='javascript: this.form.submit();' ></td>
<td>".$row['plDetID']."</td>
<td>".$row['PlantaID']."</td>
<td>".$row['plNOME']."</td>
<td>".$row['plDetBy']."</td>
<td>".$row['plDATA']."</td>
<td>&nbsp;</td>
</tr>
<tr>
<td><input type='radio' name='escolha'  value=2 onclick='javascript: this.form.submit();'></td>
<td>".$row['specDetID']."</td>
    <td>".$row['EspecimenID']."</td>
    <td>".$row['SPEC_PlantaID']."</td>
    <td>".$row['specNOME']."</td>
    <td>".$row['specDetBy']."</td>
    <td>".$row['specDATA']."</td>
</tr>
<tr><td colspan=5><input type='submit'  value='atualizar' /></td></tr>
</table>
</form>";

} else {
	echo "Não encontrei mais nenhuma amostra com nome diferente de planta";
}








$which_java = array(
"<script type='text/javascript' src='javascript/myjavascripts.js'></script>"
);
FazFooter($which_java,$calendar=FALSE,$footer=$menu);
?>