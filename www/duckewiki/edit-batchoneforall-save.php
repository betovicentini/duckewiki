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

$recsupdated = 0;
if ($final==1) {
	$qq = '';
	if ($filtro>0) {
		$qq  = "SELECT * FROM Especimenes  JOIN FiltrosSpecs as fl ON Especimenes.EspecimenID=fl.EspecimenID WHERE FiltroID=".$filtro;
	} 
	else {
		$specarr = explode(";",$especimenesids);
		if (count($specarr)>0) {
			$qq = "SELECT * FROM Especimenes WHERE ";
			$ii=0;
			foreach ($specarr as $vv) {
			  if ($ii==0) {
				$qq .= " EspecimenID=".$vv;
			  } else {
				$qq .= " OR EspecimenID=".$vv;
				}
				$ii++;
			}
		}
	}
	if ($qq<>'') {
	$rs = mysql_query($qq,$conn);
	$nrss = mysql_numrows($rs);
	if ($nrss>0) {
	  while ($rw = mysql_fetch_assoc($rs)) {
		$especimenid = $rw['EspecimenID'];
		$olddetid = $rw['DetID'];
		$changedtraits = 0;
		$detchanged =0;
		//checar variaveis de formulários
		if (!empty($_SESSION['variation'])) {
				$tempids='';
				$formid = 0;
				$oldtraitids = storeoriginaldatatopost($especimenid,'EspecimenID',$formid,$conn,$tempids);
				$newtraitids = unserialize($_SESSION['variation']);
				//compare arrays
				foreach ($newtraitids as $key => $val) {
					$oldval = trim($oldtraitids[$key]);
					$vv = trim($val);
					if ($vv!='imagem' && $vv!='none' && !empty($vv) && ($vv!=$oldval || empty($oldval))) {
						$changedtraits++;
					}
				}
				if ($changedtraits==0 && !empty($_SESSION['variation'])) {
						$changedtraits++;
				}
				if ($changedtraits>0) {
					$traitarray = unserialize($_SESSION['variation']);
					if (count($traitarray)>0) {
						$resultado = updatetraits($traitarray,$especimenid,'EspecimenID',$bibtex_id,$conn);
					}
				}
		}
		$arrayofvalues = array();
		if (!empty($detset)) {
			$arrayofdet = unserialize($detset);
			$detchanged = CompareOldWithNewValues('Identidade','DetID',$olddetid,$arrayofdet,$conn);
		}
		if ($detchanged>0) {
			$arrayofdet = unserialize($detset);
			$newdetid = InsertIntoTable($arrayofdet,'DetID','Identidade',$conn);
			$arrayofvalues = array('DetID' => $newdetid);
		}	
		$oldgpsptid = $rw['GPSPointID'];
		if ($gpspointid>0 && $oldgpsptid<>$gpspointid) { 
				unset($gazetteerid);
				$arv = array(
						'GazetteerID' => 0,
						'MunicipioID' => 0,
						'ProvinceID' => 0,
						'CountryID' => 0,
						'GPSPointID' => $gpspointid);
						$arrayofvalues = array_merge((array)$arrayofvalues,(array)$arv);
		} 
		elseif (($gazetteerid+0)>0) {
				$arv = array(
						'GazetteerID' => $gazetteerid,
						'MunicipioID' => 0,
						'ProvinceID' => 0,
						'CountryID' => 0,
						'GPSPointID' => 0);
						$arrayofvalues = array_merge((array)$arrayofvalues,(array)$arv);
		}
		$oldhabitatid = $rw['HabitatID'];
		if ($oldhabitatid<>$habitatid && $habitatid>0) {
			$arv = array('HabitatID' => $habitatid);
			$arrayofvalues = array_merge((array)$arrayofvalues,(array)$arv);
		}
		$oldaddcolvalue = $rw['AddColIDS'];
		if ($addcolvalue<>$oldaddcolvalue && !empty($addcolvalue)) {
			$arv = array('AddColIDS' => $addcolvalue);
			$arrayofvalues = array_merge((array)$arrayofvalues,(array)$arv);
		}
		$oldvernacularvalue = $rw['VernacularIDS'];
		if ($vernacularvalue<>$oldvernacularvalue && !empty($vernacularvalue)) {
			$arv = array('VernacularIDS' => $vernacularvalue);
			$arrayofvalues = array_merge((array)$arrayofvalues,(array)$arv);
		}
		$oldprojetoid = $rw['ProjetoID'];
		if ($oldprojetoid<>$projetoid && $projetoid>0) {
			$arv = array('ProjetoID' => $projetoid);
			$arrayofvalues = array_merge((array)$arrayofvalues,(array)$arv);
		}
		if (count($arrayofvalues)>0) {
			CreateorUpdateTableofChanges($especimenid,'EspecimenID','Especimenes',$conn);
			$newupdate = UpdateTable($especimenid,$arrayofvalues,'EspecimenID','Especimenes',$conn);
			$recsupdated++;
		}
  	  }
	}
  }
} 
else {
	header("location: edit-batchoneforall-exec.php");
} 

if ($recsupdated>0) {
$title = 'Um valor para várias amostras salvando';
$body = '';
FazHeader($title,$body,$which_css,$which_java,$menu);
echo "
<br />
<table cellpadding=\"1\" width='50%' align='center' class='success'>
  <tr><td class='tdsmallbold' align='center'>$recsupdated registros foram atualizados com sucesso!</td></tr>
</table>
<br />";
$which_java = array("<script type='text/javascript' src='javascript/myjavascripts.js'></script>",
"<!-- Create Menu Settings: (Menu ID, Is Vertical, Show Timer, Hide Timer, On Click ('all' or 'lev2'), Right to Left, Horizontal Subs, Flush Left, Flush Top) -->",
"<script type='text/javascript'>qm_create(0,false,0,500,false,false,false,false,false);</script>");
FazFooter($which_java,$calendar=FALSE,$footer=$menu);
}
?>