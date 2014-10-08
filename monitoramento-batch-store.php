<?php
session_start();
//INCLUI FUNCOES PHP E VARIAVEIS
include "functions/HeaderFooter.php";
include "functions/MyPhpFunctions.php";
include_once("functions/class.Numerical.php") ;

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
$title = 'Monitoramento';
$body = '';

$houveerro = array();
$deucerto = array();
$erro =0;
$ok=0;

$variables = unserialize($_SESSION['monitoramento']);
$largespecarr = unserialize($_SESSION['largespecarr']);

$exdruxulo=1;
if ($exdruxulo>0) {

$detnotupdated = 0;
$deucerto = array();
$houveerro = array();
foreach ($variables as $pg => $vararr) {
	$specids = $largespecarr[$pg];
	if ($formid>0) {
		$alltraits = array_combine($specids,$specids);
		$ii=0;
		foreach ($vararr as $kk => $vk) {
			$ta = explode("_",$kk);
			$varounit = $ta[0];
			$nta = count($ta);
			$idd = $ta[$nta-1]+0;
			unset($ta[$nta-1]);
			$newta = implode("_",$ta);
			$nnk = array($newta => $vk);
			if ($varounit!='nomenoautor' && $varounit!='tagnum' && $varounit!='nomesciid' && $varounit!='nomesci' && $varounit!='detid' && $varounit!='detset' && $varounit!='detnota') {
				if (is_array($alltraits[$idd])) {
						$alltraits[$idd] = array_merge((array)$alltraits[$idd],(array)$nnk);
				} elseif ($idd>0) {
						$alltraits[$idd] = $nnk;
				}
			}
		}
	}
	//echopre($alltraits);
	foreach ($specids as $i) {   //para cada coleta da lista, fazer o cadastro
		$erro=0;
		$ok=0;
		$changedtraits=0;
		if ($formid>0) {
			$oldtraitids = 	GetMonitoringData_batch($i,$censo,$formid,$conn);
			//compare arrays
			$newtraits = $alltraits[$i];
			foreach ($newtraits as $key => $val) {
				$tt = explode("_",$key);
				if ($tt[0]!='dataobs' && $tt[0]!='traitunit') {
					$zi =0;
					$cid = $tt[1]+0;
					$dd = 'dataobs_'.$cid;
						$oldate = $oldtraitids[$dd];
						$newdate = $newtraits[$dd];
					$du = 'traitunit_'.$cid;
						$oldunit = $oldtraitids[$du];
						$newunit = $newtraits[$du];

					$oldval = trim($oldtraitids[$key]);
					if (is_array($val)) {
						$val = implode(";",$val);
					}
					$vv = trim($val);
					$oldplusnew = trim($vv.$oldval);
					if ($vv!='imagem' && $vv!='none' && !empty($oldplusnew)) {
						if ($oldate==$newdate && $vv!=$oldval) {
							$changedtraits++;
							$zi++;
						}
						if (empty($oldval) && !empty($vv)) {
							$changedtraits++;
							$zi++;
						}
						if (!empty($oldplusnew) && $oldate!=$newdate) {
							$changedtraits++;
							$zi++;
						}
					}
					if ($zi==0) {
						unset($newtraits[$key],$newtraits[$dd],$newtraits[$du]);
					}
				}
			}
			if ($erro==0 && $changedtraits>0) { 
				$resultado = updatetraits_monitoramento($newtraits,$i,$conn);
				//echopre($newtraits);
				//echopre($oldtraitids);
				if (!$resultado) {
					$erro++;
				} else {
					$ok++;
				}
			}
			if ($ok>0 && $erro==0) {
				$deucerto[] = $i;
			} else {
				$houvererro[] = $i;
			}
		} //end if formid>0

		/////////////// atualiza identificacao se for diferente
		if ($includetaxonomia>0) {
			$newdetarray= array();
			$detnota = $vararr['detnota_'.$i]+0;
			if ($detnota==1) {$detnotes = 'Não coletada, det de campo';} else { $detnotes=''; }
			$detid = $vararr['detid_'.$i]+0;
			if ($includetaxonomia==1) {
				$nomesciid = trim($vararr['nomesciid_'.$i]);
				if (!empty($nomesciid)) {
				$olddet = trim($vararr['nomenoautor_'.$i]);
				$newdet = trim($vararr['nomesci_'.$i]);

				$znn = explode("_",$nomesciid);
					if ($olddet!=$newdet && $znn[1]>0) {
						list($famid,$genusid,$speciesid,$infraspid) = gettaxaids($nomesciid,$conn);
						$qu = "SELECT * FROM Users WHERE UserID='".$_SESSION['userid']."'";
						$rs = mysql_query($qu,$conn);
						$rw = mysql_fetch_assoc($rs);
						if (!empty($rw['PessoaID']) && ($rw['PessoaID']+0)>0) {
							$determinadorid = $rw['PessoaID']+0;
							$datadet = $_SESSION['sessiondate'];
						}
						$newdetarray = array(
						'FamiliaID' => $famid,
						'GeneroID' => $genusid,
						'EspecieID' => $speciesid,
						'InfraEspecieID' => $infraspid,
						'DetbyID' => $determinadorid,
						'DetDate' => $datadet,
						'DetNotes' => $detnotes);
					}
				} 
			} elseif ($includetaxonomia==2) {
				$detset = $vararr['detset_'.$i];
				$newdetarray = unserialize($detset);
				$detby = $newdetarray['DetbyID']+0;
				if ($detby==0) {
					$qu = "SELECT * FROM Users WHERE UserID='".$_SESSION['userid']."'";
					$rs = mysql_query($qu,$conn);
					$rw = mysql_fetch_assoc($rs);
					if (!empty($rw['PessoaID']) && ($rw['PessoaID']+0)>0) {
						$newdetarray['DetbyID'] = $rw['PessoaID']+0;
					}
				}
				$detdate = $newdetarray['DetDate'];
				if (empty($detdate)) {
					$newdetarray['DetDate'] = $_SESSION['sessiondate'];
				}
				if ($detnota==1) {
					$newdetarray['DetNotes'] = $detnotes." ".$newdetarray['DetNotes'];
				}

			}
				if (count($newdetarray)>0) {
					//get old  detid
					$detchanged =0;
					if ($detid>0) {
						$oldetarr = getdetsetvar($detid,$conn);
						foreach ($oldetarr as $kk => $vv) {
							$newval = $newdetarray[$kk];
							if ($newval!=$vv) {
								$detchanged++;
							}
						}
					} else {
						$detchanged++;
					}

					if ($detchanged>0) { //then arrays are not identical
						//echo "<br />nova identificacao";
						//echopre($newdetarray);
						//echopre($oldetarr);
						//echo "-----------------------";
						$newdetid = InsertIntoTable($newdetarray,'DetID','Identidade',$conn);
						if (!$newdetid) {
							$detnotupdated++;
						} else {
							$arrayofvv = array('DetID' => $newdetid);
							CreateorUpdateTableofChanges($i,'PlantaID','Plantas',$conn);
							$newupdate = UpdateTable($i,$arrayofvv,'PlantaID','Plantas',$conn);
							if (!$newupdate) {
								$detnotupdated++;
							} else {
								$deucerto[] = $i;
							}
						}
					} 
				} //end if there is a newdetarray
		} //end if include taxonomy
	} //end for each plant in each page
} //end for each page of plantas


} //end exdruxulo

FazHeader($title,$body,$which_css,$which_java,$menu);

if (count($houveerro)>0) {
	$herr = count($houveerro);
	echo "
<br />
<table cellpadding=\"3\" width='80%' align='center' class='erro'>
  <tr><td class='tdsmallbold' align='center'>Houve erro nessas $herr arvores:</td></tr>";
		foreach ($houveerro as $vv) {
			echo "
  <tr><td> $vv </td></tr>";
		}
	echo "
</table>
<br />";
}
if ($detnotupdated>0) {
	echo "
<br />
<table cellpadding=\"3\" width='80%' align='center' class='erro'>
<tr><td class='tdsmallbold' align='center'>As identificações de $detnotupdated árvores nao puderam ser atualizadas</td></tr>
</table>
<br />";
}
if (count($deucerto)>0) {
	$ok = count($deucerto);
	echo "
<br />
<table cellpadding=\"3\" width='80%' align='center' class='success'>
<tr><td class='tdsmallbold' align='center'>Dados de $ok árvores foram cadastrados corretamente!</td></tr>
<form action='monitoramento-batch-form.php'>
<tr><td><input type='submit' value='Entrar novos dados de monitoramento' class='bsubmit' /></td></tr>
</form>
</table>
<br />";
}
$which_java = array("<script type='text/javascript' src='javascript/myjavascripts.js'></script>",
"<!-- Create Menu Settings: (Menu ID, Is Vertical, Show Timer, Hide Timer, On Click ('all' or 'lev2'), Right to Left, Horizontal Subs, Flush Left, Flush Top) -->",
"<script type='text/javascript'>qm_create(0,false,0,500,false,false,false,false,false);</script>");
FazFooter($which_java,$calendar=FALSE,$footer=$menu);

?>