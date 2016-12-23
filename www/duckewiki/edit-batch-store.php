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

$houveerro = array();
$deucerto = array();
$erro =0;
$ok=0;
$zz = unserialize($_SESSION['specimensbatch']);
$largespecarr = unserialize($zz['largespecarr']);
$formid = $zz['formid'];
$variables = $zz['variable'];

//get specimens that where edited
$specarr = array();
$longvariables = array();
foreach ($variables as $kk => $vv) {
	$spids = $largespecarr[$kk];
	$specarr = array_merge((array)$specarr,(array)$spids);
	$longvariables = array_merge((array)$longvariables,(array)$vv);
}

//faz um array com as variavesi de traits
if ($formid>0) {
	$alltraits = array_combine($specarr,$specarr);
	$ii=0;
	foreach ($longvariables as $kk => $vk) {
		$ta = explode("_",$kk);
		$varounit = $ta[0];
		if (substr($varounit,0,5)=='trait') {
			$nta = count($ta);
			$idd = $ta[$nta-1];
			unset($ta[$nta-1]);
			$newta = implode("_",$ta);
			$nnk = array($newta => $vk);
			if (is_array($alltraits[$idd])) {
				$alltraits[$idd] = array_merge((array)$alltraits[$idd],(array)$nnk);
			} else {
				$alltraits[$idd] = $nnk;
			}
		}
	}
}

$exdruxulo=1;
if ($exdruxulo>0) {

foreach ($specarr as $i) {   //para cada coleta da lista, fazer o cadastro
	$erro=0;
	$coletorid = $longvariables['coletor_'.$i];
	$colnum = $longvariables['colnum_'.$i];
	$datacol = $longvariables['datacol_'.$i];
	$gpspointid = $longvariables['gpspointid_'.$i];
	$gazetteerid = $longvariables['gazetteerid_'.$i];
	$habitatid = $longvariables['habitatid_'.$i];
	$addcolvalue = $longvariables['addcolvalue_'.$i];
	$vernacularvalue = $longvariables['vernacularvalue_'.$i];
	$projetoid = $longvariables['projetoid_'.$i];

	$data = explode("-",$datacol);
		if (count($data)==3) {
			$collyy = $data[0];
			$collmm = $data[1];
			$colldd = $data[2];
		} else {
			if (count($data)==2) {
				$collyy = $data[0];
				$collmm = $data[1];
			} else {
				$collyy = $data;
			}
		}
	if ($formid>0) {
		$newtraits = $alltraits[$i];
		//echo "EspecimenID = $i<br />";
		//echopre($newtraits);
		$ttids = '';
		$oldtraitids = 	storeoriginaldatatopost($i,'EspecimenID',$formid,$conn,$ttids);
		//compare arrays
		$changedtraits=0;
		foreach ($newtraits as $key => $val) {
			$oldval = trim($oldtraitids[$key]);
			if (is_array($val)) {
				$val = implode(";",$val);
			}
			$vv = $val;
			if ($vv!='imagem' && $vv!='none' && ($vv!=$oldval || (empty($oldval) && !empty($val)))) {
				$changedtraits++;
			}
		}
		if ($changedtraits==0 && count($newtraits)>0) {
				$changedtraits++;
		}
	}



	if (empty($plantaid)) {$plantaid= " ";}

		$arrayofvalues = array(
			'ColetorID' => $coletorid,
			'AddColIDS' => $addcolvalue,
			'VernacularIDS' => $vernacularvalue,
			'HabitatID' => $habitatid,
			'GPSPointID' => $gpspointid,
			'GazetteerID' => $gazetteerid,
			'ProjetoID' => $projetoid,
			'PlantaID' => $plantaid,
			'Number' => $colnum,
			'Day' => $colldd,
			'Mes' => $collmm,
			'Ano' => $collyy);

		$upp = CompareOldWithNewValues('Especimenes','EspecimenID',$i,$arrayofvalues,$conn);
		if (!empty($upp) && $upp>0) { //if new values differ from old, then update
			CreateorUpdateTableofChanges($i,'EspecimenID','Especimenes',$conn);
			$updatespecid = UpdateTable($i,$arrayofvalues,'EspecimenID','Especimenes',$conn);
			if (!$updatespecid) {
				$erro++;
			} else {
				$ok++;
			}
		}

	//cadastro da identificacao
	if ($erro==0) { //se nao houve erro no cadastro

		$detset = $longvariables["detset_".$i];
		$detid = $longvariables["detid_".$i];

		if (!empty($detset)) {
			$arrayofvalues = unserialize($detset);

			//get old  detid
			$detchanged =0;
			if ($detid>0) {
				$oldetarr = getdetsetvar($detid,$conn);
				foreach ($oldetarr as $kk => $vv) {
					$newval = $arrayofvalues[$kk];
					if ($newval!=$vv) {
						$detchanged++;
					}
				}
			} else {
				$detchanged++;
			}

			if ($detchanged>0) { //then arrays are not identical
				$newdetid = InsertIntoTable($arrayofvalues,'DetID','Identidade',$conn);
				if (!$newdetid) {
					$er++;
				} else {
					$arrayofvv = array('DetID' => $newdetid);
					$newupdate = UpdateTable($i,$arrayofvv,'EspecimenID','Especimenes',$conn);
					if (!$newupdate) {
						$erro++;
					} else {
						$ok++;
					}
				}
			} 
		} //if detset
	} //se erro==0

	if ($erro==0 && $changedtraits>0) { 
			$traitarray = $newtraits;
			$resultado = updatetraits($traitarray,$i,'EspecimenID',$bibtex_id,$conn);
			if (!$resultado) {
				$erro++;
			} else {
				$ok++;
			}
	}


	if ($ok>0 && $erro==0) {
		$deu = array($i);
		$deucerto = array_merge((array)$deucerto,(array)$deu);
	} else {
		$he = array($i);
		$houvererro = array_merge((array)$houvererro,(array)$he);
	}

}

$title = 'Cadastrar várias amostras';
$body = '';
FazHeader($title,$body,$which_css,$which_java,$menu);
if (count($houveerro)>0) {
	$herr = count($houveerro);
	echo "
<br />
<table cellpadding=\"1\" width='50%' align='center' class='erro'>
  <tr><td class='tdsmallbold' align='center'>Houve erro nessas $herr coletas:</td></tr>";
	foreach ($houveerro as $vv) {
		echo "
  <tr><td> $vv </td></tr>";
	}
	echo "
</table>
<br />";
}

if (count($deucerto)>0) {
	$ok = count($deucerto);
	echo "
<br />
<table cellpadding=\"1\" width='50%' align='center' class='success'>
  <tr><td class='tdsmallbold' align='center'>$ok coletas foram cadastradas corretamente!</td></tr>
</table>
<br />";
}

}
$which_java = array("<script type='text/javascript' src='javascript/myjavascripts.js'></script>",
"<!-- Create Menu Settings: (Menu ID, Is Vertical, Show Timer, Hide Timer, On Click ('all' or 'lev2'), Right to Left, Horizontal Subs, Flush Left, Flush Top) -->",
"<script type='text/javascript'>qm_create(0,false,0,500,false,false,false,false,false);</script>");
FazFooter($which_java,$calendar=TRUE,$footer=$menu);
?>