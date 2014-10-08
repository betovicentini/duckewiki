<?php
session_start();
//Check whether the session variable
include "functions/HeaderFooter.php";
include "functions/SelectOptions.php";
include_once("functions/class.Numerical.php") ;
$lang = $_SESSION['lang'];
$dbname = $_SESSION['dbname'];
$conn = ConectaDB($dbname);
$uuid = cleanQuery($_SESSION['userid'],$conn);
if(!isset($uuid) || 
	(trim($uuid)=='')) {
		header("location: access-denied.php");
	exit();
} 

$ppost = cleangetpost($_POST,$conn);
$arval = $ppost;
@extract($ppost);
$gget = cleangetpost($_GET,$conn);
@extract($gget);

HTMLheaders($body);



$faltacoord = array();
$jaexiste = array();
$houveerro = array();
$deucerto = array();
$erro =0;
$ok=0;
for ($i=$colnumde;$i<=$colnumate;$i++) {   //para cada coleta da lista, fazer o cadastro
	$elementid = "traitids_".$i;
	if (!isset($especimenid) || empty($especimenid) || $especimenid==0) {
		$qq = "SELECT * FROM Especimenes WHERE ColetorID='$pessoaid' AND Number='$colnum'";
		$res = mysql_query($qq,$conn);
		$nres = @mysql_numrows($res);
		if ($nres>0) {
			$ja = array($i);
			$jaexiste = array_merge((array)$jaexiste,(array)$ja);
			$erro++;
		} 
	} 
	

	$gpsptid = "gpspointid_".$i;
	$gaztid = "gazetteerid_".$i;
	//localidade
	if (empty($$gpsptid) && empty($gpspointid) && empty($$gaztid) && empty($gazetteerid)) {
		$jac = array($i);
		$faltacoord = array_merge((array)$faltacoord,(array)$jac);
		$erro++;	
	}

	
	$er=0;

	//caso contrario se nao houver nenhum erro
	if ($erro==0) { 	
		//cria registro novo e obtem especimenid
		$data = explode("-",$datacol);
		if (count($data)==3) {
			$detyear = $data[0];
			$detmonth = $data[1];
			$detday = $data[2];
		} else {
			if (count($data)==2) {
				$detyear = $data[0];
				$detmonth = $data[1];			
			} else {
				$detyear = $data;
			}
		}
		
		$habvar = "habitatid_".$i;
		if ($$habvar>0 && $$habvar!=$habitatid) {
			$habitatval = $$habvar;
		} else {
			$habitatval = $habitatid;
		}
		$gpsptid = "gpspointid_".$i;
		
		if (empty($$gpsptid)) { $gpsid = $gpspointid;} else {
			$gpsid = $$gpsptid;
		}
		
		$gaztid = "gazetteerid_".$i;
		if (empty($$gaztid)) { $gazid = $gazetteerid;} else {
			$gazid = $$gaztid;
		}
		
		if (empty($plantaid)) {$plantaid= " ";}
		$arrayofvalues = array(
			'ColetorID' => $pessoaid,
			'AddColIDS' => $addcolvalue,
			'HabitatID' => $habitatval,
			'GPSPointID' => $gpsid,
			'GazetteerID' => $gazid,
			'ProjetoID' => $projetoid,
			'Number' => $i,
			'Day' => $detday,
			'Mes' => $detmonth,
			'Ano' => $detyear);
			
			//print_r($arrayofvalues);
			
			$newspec = InsertIntoTable($arrayofvalues,'EspecimenID','Especimenes',$conn);
			//echo "aqui".$erro;	
			if (!$newspec) {
				$er++;
			} else {
				$ok++;
			}
			//echo "aqui $especimenid".$erro;	
	} 

	//cadastro da identificacao
	if ($erro==0) { //se nao houve erro no cadastro
		//seleciona a identidade antiga e indica o que deve ser feito
	

		$dettset  = "detset_".$i;
		$arrayofvalues = unserialize($$dettset);
		if (count($arrayofvalues)>0 && is_array($arrayofvalues)) {
			$newdetid = InsertIntoTable($arrayofvalues,'DetID','Identidade',$conn);
			if (!$newdetid) {
				$er++;
			} else {
				$ok++;
			}
		} else {
			$newdetid='';
		}
			
	} //se erro==0


	$especimenid=$newspec;
		

	if (!empty($_SESSION[$elementid]) && $erro==0) {
			$traitarray = unserialize($_SESSION[$elementid]);
			if (count($traitarray)>0) {
				$resultado = updatetraits($traitarray,$especimenid,'EspecimenID',$conn);
				if (!$resultado) {
					$er++;
				} else {
					$ok++;
				}
			}
	}
	//echo "DAQUI".$updated." and ".$detchange." and ".$_SESSION['editando'];

	//update id
	if ($er==0 && !empty($newdetid)) { 
			//echo "here";
			$arrayofvalues = array('DetID' => $newdetid);
			$newupdate = UpdateTable($especimenid,$arrayofvalues,'EspecimenID','Especimenes',$conn);
			if (!$newupdate) {
				$er++;
			} else {
				$ok++;
			}
	}

	if ($ok>0 && $er==0) {
		$deu = array($i);
		$deucerto = array_merge((array)$deucerto,(array)$deu);		
	} else {
		$he = array($i);
		$houvererro = array_merge((array)$houvererro,(array)$he);
	}
	
	unset($_SESSION[$elementid]);

}

if (count($jaexiste)>0) {
	echo "<br><table cellpadding=\"1\" width='50%' align='center' class='erro'>
		<tr><td class='tdsmallbold' align='center'>".GetLangVar('erro3')."</td></tr>";
		foreach ($jaexiste as $vv) {
			echo "<tr><td> $vv </td></tr>";
		}
	echo	"</table><br>";

}

if (count($faltacoord)>0) {
	echo "<br><table cellpadding=\"1\" width='50%' align='center' class='erro'>
		<tr><td class='tdsmallbold' align='center'>".GetLangVar('erro1')."</td></tr>";
		foreach ($faltacoord as $vv) {
			echo "<tr><td> $vv </td></tr>";
		}
	echo	"</table><br>";
}

if (count($houveerro)>0) {
	$herr = count($houveerro);
	echo "<br><table cellpadding=\"1\" width='50%' align='center' class='erro'>
		<tr><td class='tdsmallbold' align='center'>Houve erro nessas $herr coletas:</td></tr>";
		foreach ($houveerro as $vv) {
			echo "<tr><td> $vv </td></tr>";
		}
	echo	"</table><br>";
}

if (count($deucerto)>0) {
	$ok = count($deucerto);
	echo "<br><table cellpadding=\"1\" width='50%' align='center' class='success'>
		<tr><td class='tdsmallbold' align='center'>$ok coletas foram cadastradas corretamente!</td></tr>";
	echo	"</table><br>";
}


HTMLtrailers();

?>