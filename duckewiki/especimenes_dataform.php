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
if (!empty($_POST['detset'])) {
	$detset = $_POST['detset'];
	unset($_POST['detset']);
}
if (!empty($_GET['detset'])) {
	$detset = $_GET['detset'];
	unset($_GET['detset']);
}

$ppost = cleangetpost($_POST,$conn);
@extract($ppost);
$arval = $ppost;

$gget = cleangetpost($_GET,$conn);
@extract($gget);

//CABECALHO

$menu = FALSE;
$which_css = array(
"<link href='css/geral.css' rel='stylesheet' type='text/css' />",
"<link rel='stylesheet' type='text/css' media='screen' href='css/autosuggest.css' >"
);
$which_java = array("<script type='text/javascript' src='javascript/ajax_framework.js'></script>");
$title = 'Especímene';
$body = '';
FazHeader($title,$body,$which_css,$which_java,$menu);

if ($especimenid>0) {
	$submeteu=='editando';
} elseif (!$submeteu='nova') {
	echo "
<form >
  <script language=\"JavaScript\">
    window.close();
  </script>
</form>";
}
//echopre($gget);
$lixo=1;
if ($lixo>0) {
//coordenadas
if (!isset($coord)) {
	$coord = @coordinates('','',$latgrad,$longgrad,$latminu,$longminu,$latsec,$longsec,$latnors,$longwore);
	@extract($coord);
}

///final record register 
if (!empty($final)) {
	$oano=0;
	if (!empty($datacol) && !empty($collyy)) {
		$minhadata = explode("-",$datacol);
		$oano = $minhadata[0];
		if ($minhadata[2]!=$colldd || $minhadata[1]!=$collmm || $minhadata[0]!=$collyy) {
			$erro++;
			echo "
<br />
<table cellpadding=\"7\" width='50%' align='center' class='erro'>
  <tr><td class='tdsmallbold' align='center'>Dois valores para data, escolha 1 das opções, ano, mes e dia separados, ou data completa</td></tr>
</table>
<br />";
		}
	}
	$erdata=0;
	if (empty($datacol) && !empty($collyy)) {
		$curyear = explode("-",$_SESSION['sessiondate']);
		if ($collyy>1700 && $collyy<=$curyear[0]) {
			$thecollyy=$collyy;
		} else {
			$erdata++;
			unset($thecollyy);
		}
		if ($colldd>=1 && $colldd<=31 && !empty($colldd)) {
			$thecolldd=$colldd;
		} elseif (!empty($colldd)) {
			$erdata++;
			unset($thecolldd);
		}
		if ($collmm>=1 && $collmm<=12 && !empty($collmm)) {
			$thecollnn=$collmm;
		} elseif (!empty($collmm)) {
			$erdata++;
			unset($thecollmm);
		}
		if ($erdata>0) {
			$erro++;
			echo "
<br />
<table cellpadding=\"7\" width='50%' align='center' class='erro'>
  <tr><td class='tdsmallbold' align='center'>Valores para mes, ano ou dia incorretos!</td></tr>
</table>
<br />";
		} else {
			$dataseparada=TRUE;
			$oano = $collyy;
			$datacol = $thecollyy."-".$thecollmm."-".$thecolldd;
		}
	} 
	else {
		$dataseparada=FALSE;
	}

	//coordenadas
	$erro =0;
	//checa se o resitro ja existe
	if ($_SESSION['editando']!=1) {
		if (!isset($especimenid) || empty($especimenid)) {
			$qq = "SELECT * FROM Especimenes WHERE ColetorID='$pessoaid' AND Number='".$colnum."'";
			//if ($oano>0) { $qq .=" AND Ano='".$oano."'"; }
			$res = mysql_query($qq,$conn);
			$nres = @mysql_numrows($res);
			if ($nres>0) {
				echo "
<br />
<table cellpadding=\"7\" width='50%' align='center' class='erro'>
  <tr><td class='tdsmallbold' align='center'>".GetLangVar('erro3')."</td></tr>
</table>
<br />";
				$erro++;
			} 
		} 
	}
	//checa por campos obrigatorios
	$localidadeid = trim($localidadeid);
	if ((abs($latdec)+abs($longdec)+0)==0 && ($gazetteerid+0)==0 && $gsppointid+0==0 && empty($localidadeid) && $plantaid+0==0) {
		$misslocality = TRUE;
	} else {
		$misslocality = FALSE;
	}	
	//echo "AQUI ".$submeteu."<br >".$especimenid." ou aqui ".$gpspointid."<br />";
	//SE ESTIVER EDITANDO, NAO SE PREOCUPA COM LOCALIDADE VAZIA OU DATA VAZIA
	if ($especimenid>0) {
		$misslocality = FALSE;
		if (empty($datacol)) {
			$datacol = "0000-00-00";
		}
	}
	if (empty($datacol)  || empty($colnum) || empty($pessoaid) || $misslocality==TRUE) {
	//if (empty($colnum) || empty($pessoaid) ) {
		echo "
<br />
<ol style='color: red; font-size: 1.3em; margin-left: 25%' >".GetLangVar('erro1')."";
			if (empty($datacol) && empty($oano)) {
				echo "
<li >".GetLangVar('namedata')."</li>";
			}
			if (empty($pessoaid)) {
				echo "
<li >".GetLangVar('namecoletor')."</li>";
			}
			if (empty($colnum)) {
				echo "
<li >".GetLangVar('namenumber')."</li>";
			}
			if ($misslocality) {
				echo "
<li >Localidade ou coordenada geográfica</li>";
			}
			echo "
</ol>
";
			$erro++;
	} 

	//se localidade nao é gazetteer ou gpspoint quando há uma planta marcada
	$localidadeid = trim($localidadeid);
	if (!empty($localidadeid)) {
			$locid = explode("_",$localidadeid);
			if ($locid[0]!='gazetteer' && $plantaid>0 && (!isset($gpspoint) || $gpspoint==0)) {
			echo "
<br />
<table cellpadding=\"7\" width='50%' align='center' class='erro'>
  <tr><td class='tdsmallbold' align='center'>Essa amostra é de uma planta marcada, a localidade nao pode ser uma unidade administrativa!</td></tr>
</table>
";
$erro++;
		}
	}

	//checar se coordenadas nao tem valores exdruxulos
	if (!empty($latdec) && !empty($longdec) && (abs($latdec)>90 || abs($longdec)>180)) {
		echo "
<table align='center' class='erro'>
  <tr><td>".GetLangVar('namelatitude')." > 90 || ".GetLangVar('namelongitude')." > 180</td></tr>
</table>";
		$erro++;
	}

	$changedtraits=0;
	//faz o cadastro das variaveis se houver
	if (!empty($_SESSION['variation'])) {
		if ($_SESSION['editando']==1) {
				$tempids='';
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
		}

	}//se empty $traitids

	//caso contrario se nao houver nenhum erro
	if ($erro==0) { 
		//cria registro novo e obtem especimenid
		if ($dataseparada==FALSE) {
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
					$detyear = $data[0];
				}
			}
		} 
		else {
			$detyear = $thecollyy;
			$detmonth = $thecollmm;
			$detday = $thecolldd;
		}

		if ($gpspointid>0 && $gazetteerid>0 ) { 
			unset($localidadeid);
		} //prioriza a coordenada do ponto de GPS

		if (empty($plantaid)) {$plantaid= 0;}

		$arrayofvalues = array(
			'ColetorID' => $pessoaid,
			'AddColIDS' => $addcolvalue,
			'VernacularIDS' => $vernacularvalue);

		if (!empty($localidadeid)) {
			$locid = explode("_",$localidadeid);
			if ($locid[0]=='gazetteer') {
				$arv = array('GazetteerID' => $locid[1]);
			} elseif ($locid[0]=='municipio') {
				$arv = array();
				$arv = array('GazetteerID' => 0, 'MunicipioID' => $locid[1]);
			} elseif ($locid[0]=='province') {
				$arv = array('GazetteerID' => 0, 'MunicipioID' => 0, 'ProvinceID' => $locid[1]);
			} elseif ($locid[0]=='country') {
				$arv = array('GazetteerID' => 0, 'MunicipioID' => 0,  'ProvinceID' => 0, 'CountryID' => $locid[1]);
			}
		} else {
			$arv = array('GazetteerID' => $gazetteerid);
		}
		$arv2 = array(
			'HabitatID' => $habitatid,
			'GPSPointID' => $gpspointid,
			'ProjetoID' => $projetoid);

		if ($plantaid>0) {
			$arrayofvaluesplanta = array_merge((array)$arv,(array)$arv2);
		} else {
			$arrayofvalues = array_merge((array)$arrayofvalues,(array)$arv,(array)$arv2);
		}

		$arv = array(
			'PlantaID' => $plantaid,
			'Number' => $colnum,
			'Day' => $detday,
			'Mes' => $detmonth,
			'Ano' => $detyear,
			'Latitude' => $latdec,
			'Longitude' => $longdec,
			'Altitude' => $altitude
		);
		$arrayofvalues = array_merge((array)$arrayofvalues,(array)$arv);
		$updated =0;
		if (empty($especimenid)) {
			//echo "spec";
			//echopre($arrayofvalues);
			if (($detid+0)>0) {
				$arrayofvalues['DetID'] = $detid;
			}
			$newspec = InsertIntoTable($arrayofvalues,'EspecimenID','Especimenes',$conn);
			if (!$newspec) {
				echo "
<br />
<table cellpadding=\"7\" width='50%' align='center' class='erro'>
  <tr><td class='tdsmallbold' align='center'>".GetLangVar('erro2')."</td></tr>
</table>
<br />
";
				$erro++;
			} else {
				$inseriunovo = 1;
			}
		} 
		else {
			$upp = CompareOldWithNewValues('Especimenes','EspecimenID',$especimenid,$arrayofvalues,$conn);
			if (!empty($upp) && $upp>0) { //if new values differ from old, then update
				$updated++;
				CreateorUpdateTableofChanges($especimenid,'EspecimenID','Especimenes',$conn);
				$updatespecid = UpdateTable($especimenid,$arrayofvalues,'EspecimenID','Especimenes',$conn);
				if (!$updatespecid) {
					$erro++;
				}
			} else {
				$upp=0;
			}
		}
		///
		if ($plantaid>0) {
			$uppl = CompareOldWithNewValues('Plantas','PlantaID',$plantaid,$arrayofvaluesplanta,$conn);
			if (!empty($uppl) && $uppl>0) { //if new values differ from old, then update
				$updated++;
				CreateorUpdateTableofChanges($plantaid,'PlantaID','Plantas',$conn);
				$updateplantaid = UpdateTable($plantaid,$arrayofvaluesplanta,'PlantaID','Plantas',$conn);
				if (!$updateplantaid) {
					$erro++;
				}
			} else {
				$uppl=0;
			}
		}
		///


	} //	end checa por campos obrigatorios

	$er=0;
	//cadastro da identificacao
	if ($erro==0) { //se nao houve erro no cadastro
		//seleciona a identidade antiga e indica o que deve ser feito
		if ($_SESSION['editando'] && (!isset($plantaid) || $plantaid==0 || empty($plantaid))) { //editando
			$qq = "SELECT Identidade.* FROM Especimenes JOIN Identidade USING(DetID) WHERE EspecimenID='$especimenid'";
			$res = mysql_query($qq,$conn);
			$row = mysql_fetch_assoc($res);
			$olddetid = $row['DetID'];
			if (!empty($detset)) {
				$arrayofvalues = unserialize($detset);
				$detchanged = CompareOldWithNewValues('Identidade','DetID',$olddetid,$arrayofvalues,$conn);
			}
			if ($detchanged==0 || empty($detchanged)) { //se for identifico nesse campos nao faz nada
				$detchange = 'naomudou';
			} else {
				$detchange = 'mudou';
			}
		} 
		elseif ($plantaid>0) {
			$qq = "SELECT Identidade.* FROM Plantas JOIN Identidade USING(DetID) WHERE PlantaID='".$plantaid."'";
			$res = mysql_query($qq,$conn);
			$row = mysql_fetch_assoc($res);
			$olddetid = $row['DetID'];
			if (!empty($detset)) {
				$arrayofvalues = unserialize($detset);
				$detchanged = CompareOldWithNewValues('Identidade','DetID',$olddetid,$arrayofvalues,$conn);
			}
			if ($detchanged==0 || empty($detchanged)) { //se for identifico nesse campos nao faz nada
				$detchange = 'naomudou';
			} else {
				$detchange = 'mudou';
			}
		}
		//se mudou ou se e nova, insere nova determinacao
		if (empty($detchange) || $detchange=='mudou') {
			$arrayofvalues = unserialize($detset);
			if (count($arrayofvalues)>0 && ($arrayofvalues['FamiliaID']+0)>0) {
				//echo "det";
				//echopre($arrayofvalues);
				$newdetid = InsertIntoTable($arrayofvalues,'DetID','Identidade',$conn);
				if (!$newdetid) {
					$er++;
					echo "
<br />
<table cellpadding=\"7\" width='50%' align='center' class='erro'>
  <tr><td class='tdsmallbold' align='center'>".GetLangVar('erro2')."</td></tr>
</table>
<br />";
				} 
			}
		} 
	} //se erro==0


	if ($plantaid>0  && $uppl==0 && $newdetid>0) {
				CreateorUpdateTableofChanges($plantaid,'PlantaID','Plantas',$conn);
	} elseif ($especimenid>0  && $upp==0  && $newdetid>0)  {
				CreateorUpdateTableofChanges($especimenid,'EspecimenID','Especimenes',$conn);
	} elseif (empty($especimenid)) {
			$especimenid=$newspec;
	}

	//nota de localidade verifica e cadastra se for o caso
	if ($localidadetraitid>0) {
		$notaslocalidade = trim($notaslocalidade);
		$qlocal = "SELECT * FROM Traits_variation WHERE EspecimenID=".$especimenid." AND TraitID=".$localidadetraitid;
		$rsl = @mysql_query($qlocal,$conn);
		$nrsl = mysql_numrows($rsl);
		if ($nrsl>0) {
			//echo "aqui poxa<br />";
			$rwl = mysql_fetch_assoc($rsl);
			$oldnotaslocalidade = trim($rwl['TraitVariation']);

			$nlen1 = strtolower($oldnotaslocalidade);
			$nlen2 = strtolower($notaslocalidade);
			//echo $nlen1."<br />";
			//echo $nlen2."<br />";
			//echo strcmp($nlen1, $nlen2)."<br />";
			$compar = abs(strcmp($nlen1, $nlen2));
			//echo $compar."<br />";
			if ($compar>0) {
				CreateorUpdateTableofChanges($rwl['TraitVariationID'] ,'TraitVariationID','Traits_variation',$conn);
				//$qdel = "DELETE FROM `Traits_variation` WHERE `TraitVariationID`=".$rwl['TraitVariationID'];
				//mysql_query($qdel,$conn);
				//$qlocal = "INSERT INTO Traits_variation (TraitVariationID,TraitVariation,TraitID,EspecimenID,AddedBy,AddedDate) VALUES (".$rwl['TraitVariationID'].", '".$notaslocalidade."', ".($localidadetraitid+0).", ".$especimenid.", ".($uuid+0).", CURRENT_DATE())";
				$qlocal = "UPDATE `Traits_variation` SET `TraitVariation`=\"".$notaslocalidade."\", `AddedBy`=\"".$uuid."\", `AddedDate`=\"".$_SESSION['sessiondate']."\" WHERE `TraitVariationID`=".$rwl['TraitVariationID'];
				$nlocal = mysql_query($qlocal,$conn);
				if ($nlocal) {
					echo $qlocal." Atualizou!<br />";
					$updated++;
				} else {
					//echo $qlocal."<br />";
					echo "FALHOU e nao entendo!<br />";
				}
			} else {
				//echo 'diz que não é diferente... estranho';
			}
		} else {
			if (!empty($notaslocalidade)) {
				$qlocal = "INSERT INTO Traits_variation (TraitVariation,TraitID,EspecimenID,AddedBy,AddedDate) VALUES ('".$notaslocalidade."', ".($localidadetraitid+0).", ".$especimenid.", ".($uuid+0).", CURRENT_DATE())";
				@mysql_query($qlocal,$conn);
				$updated++;
			}
		}
	}
	
	
	

	if (	(
				($_SESSION['editando']==1 && $changedtraits>0 && !empty($_SESSION['variation'])) || 
				($_SESSION['editando']!=1 && !empty($_SESSION['variation']))
			) 
			&& $erro==0
		) {
		$traitarray = unserialize($_SESSION['variation']);
		if (count($traitarray)>0) {
			$resultado = updatetraits($traitarray,$especimenid,'EspecimenID',$conn);
			if (!$resultado) {
				$erro++;
				echo "
<br />
<table cellpadding=\"7\" width='50%' align='center' class='erro'>
  <tr><td class='tdsmallbold' align='center'>".GetLangVar('erro2')."</td></tr>
</table>
<br />";
			} else {
				$updated++;
			}
		}
	}

	//update id
	if ($er==0 && $newdetid>0) { 
			$arrayofvalues = array('DetID' => $newdetid);
			if ($plantaid>0) {
				$newupdate = UpdateTable($plantaid,$arrayofvalues,'PlantaID','Plantas',$conn);
			} else {
				$newupdate = UpdateTable($especimenid,$arrayofvalues,'EspecimenID','Especimenes',$conn);
			}
			if (!$newupdate) {
				$erro++;
				echo "
<br />
<table cellpadding=\"7\" width='50%' align='center' class='erro'>
  <tr><td class='tdsmallbold' align='center'>".GetLangVar('erro2')."</td></tr>
</table>
<br />";
			} else {
				$updated++;
			}
	}


	if ($erro==0) {
		if ($_SESSION['editando']==1 && empty($updated) && $detchange=='naomudou' && $final!=2) {
			echo "
<br />
<table cellpadding=\"7\" width='50%' align='center' class='erro'>
  <tr><td class='tdsmallbold' align='center'>".GetLangVar('messagenochange')."</td></tr>
</table>
<br />";
		} elseif ($_SESSION['editando']!=1 || $updated>0 || $detchange='mudou') {
		
//ATUALIZA A TABELA CHECKLIST SPECIMENS
$newtbname = 'tempSpec_'.$uuid;
if ($_SESSION['editando']==1) {
	$qq = " DELETE FROM checklist_speclist WHERE EspecimenID='".$especimenid."'";
	mysql_query($qq,$conn);
	$qq = " DELETE FROM ".$newtbname." WHERE EspecimenID='".$especimenid."'";
	mysql_query($qq,$conn);
}

if ($inseriunovo==1 && $plantaid>0) {
	$qq = 'UPDATE Especimenes as sp, Plantas as pl SET sp.DetID=pl.DetID WHERE sp.PlantaID=pl.PlantaID AND (sp.DetID=0 or sp.DetID IS NULL) AND sp.PlantaID='.$plantaid;
	mysql_query($qq,$conn);
	$qq = 'UPDATE Especimenes as sp, Plantas as pl SET sp.GazetteerID=pl.GazetteerID WHERE sp.PlantaID=pl.PlantaID AND (sp.GazetteerID=0 or sp.GazetteerID IS NULL) AND sp.PlantaID='.$plantaid;
	mysql_query($qq,$conn);
	$qq = 'UPDATE Especimenes as sp, Plantas as pl SET sp.GPSPointID=pl.GPSPointID WHERE sp.PlantaID=pl.PlantaID AND (sp.GPSPointID=0 or sp.GPSPointID IS NULL) AND sp.PlantaID='.$plantaid;
	mysql_query($qq,$conn);
}


$sql = "INSERT INTO checklist_speclist (SELECT  pltb.GazetteerID, pltb.GPSPointID, pltb.EspecimenID,  pltb.PlantaID,  thepl.PlantaTag, pltb.DetID, (colpessoa.Abreviacao) as COLETOR,  pltb.Number as NUMERO, if(CONCAT(pltb.Ano,'-',pltb.Mes,'-',pltb.Day)<>'0000-00-00',CONCAT(pltb.Ano,'-',pltb.Mes,'-',pltb.Day),'FALTA') as DATA, if(pltb.INPA_ID>0,pltb.INPA_ID+0,NULL) as ".$herbariumsigla.", famtb.Familia as FAMILIA, acentosPorHTML(gettaxonname(pltb.DetID,1,0)) as NOME, acentosPorHTML(gettaxonname(pltb.DetID,1,1)) as NOME_AUTOR, emorfotipo(pltb.DetID,0,0) as MORFOTIPO, 
localidadefields(pltb.GazetteerID, pltb.GPSPointID, pltb.MunicipioID, pltb.ProvinceID, pltb.CountryID, 'COUNTRY') as PAIS,  
localidadefields(pltb.GazetteerID, pltb.GPSPointID, pltb.MunicipioID, pltb.ProvinceID, pltb.CountryID, 'MAJORAREA') as ESTADO,
localidadefields(pltb.GazetteerID, pltb.GPSPointID, pltb.MunicipioID, pltb.ProvinceID, pltb.CountryID, 'MINORAREA') as MUNICIPIO,
localidadefields(pltb.GazetteerID, pltb.GPSPointID, pltb.MunicipioID, pltb.ProvinceID, pltb.CountryID, 'GAZETTEER') as LOCAL,
localidadefields(pltb.GazetteerID, pltb.GPSPointID, pltb.MunicipioID, pltb.ProvinceID, pltb.CountryID, 'GAZETTEER_SPEC') as LOCALSIMPLES,  
getlatlong(pltb.Latitude , pltb.Longitude, pltb.GPSPointID, pltb.GazetteerID, 0, 0, 0, 0) as LONGITUDE, getlatlong(pltb.Latitude , pltb.Longitude, pltb.GPSPointID, pltb.GazetteerID, 0, 0, 0, 1) as LATITUDE, IF(ABS(pltb.Longitude)>0,pltb.Altitude+0,IF(pltb.GPSPointID>0,gpspt.Altitude+0,IF(ABS(gaz.Longitude)>0,gaz.Altitude+0,NULL))) as ALTITUDE, 'edit-icon.png' AS EDIT, 'mapping.png' AS MAP,  '' as OBS, IF(pltb.HabitatID>0,'environment_icon.png','') as HABT, IF (checkimgs(pltb.EspecimenID, pltb.PlantaID)>0,'camera.png','') as IMG, checknir(pltb.EspecimenID,pltb.PlantaID) as NIRSpectra, "; 
if ($duplicatesTraitID>0) {$sql .= " traitvaluespecs(".$duplicatesTraitID.", pltb.PlantaID, pltb.EspecimenID,'', 0, 0)+0 as DUPS,";}
if ($daptraitid>0) { $sql .= " traitvaluespecs(".$daptraitid.", pltb.PlantaID, pltb.EspecimenID,'mm', 0, 1)+0 as DAPmm,";}
if ($alturatraitid>0) { $sql .= " traitvaluespecs(".$alturatraitid.", pltb.PlantaID, pltb.EspecimenID,'mm', 0, 1)+0 as ALTURA,"; }
if ($habitotraitid>0) { $sql .= " (traitvaluespecs(".$habitotraitid.", pltb.PlantaID, pltb.EspecimenID,'', 0, 1)) as HABITO,"; }
if ($traitfertid>0) { $sql .= " (traitvaluespecs(".$traitfertid.", pltb.PlantaID, pltb.EspecimenID,'', 0, 1)) as FERTILIDADE,"; }
$sql .= " IF(projetologo(pltb.ProjetoID)<>'',projetologo(pltb.ProjetoID),'') as PRJ, acentosPorHTML(IF(projetostring(pltb.ProjetoID,0,0)<>'',projetostring(pltb.ProjetoID,0,0),'NÃO FOI DEFINIDO')) as PROJETOstr";
$sql .= " FROM Especimenes as pltb LEFT JOIN Plantas as thepl ON thepl.PlantaID=pltb.PlantaID LEFT JOIN Pessoas as colpessoa ON pltb.ColetorID=colpessoa.PessoaID LEFT JOIN Identidade as iddet ON pltb.DetID=iddet.DetID  LEFT JOIN Tax_InfraEspecies as infsptb ON iddet.InfraEspecieID=infsptb.InfraEspecieID  LEFT JOIN Tax_Especies as sptb ON iddet.EspecieID=sptb.EspecieID  LEFT JOIN Tax_Generos as gentb ON iddet.GeneroID=gentb.GeneroID   LEFT JOIN Tax_Familias as famtb ON iddet.FamiliaID=famtb.FamiliaID  LEFT JOIN Gazetteer as gaz ON gaz.GazetteerID=pltb.GazetteerID   LEFT JOIN Municipio as muni ON gaz.MunicipioID=muni.MunicipioID   LEFT JOIN Province as provgaz ON muni.ProvinceID=provgaz.ProvinceID   LEFT JOIN Country  as countrygaz ON provgaz.CountryID=countrygaz.CountryID   LEFT JOIN GPS_DATA as gpspt ON gpspt.PointID=pltb.GPSPointID   LEFT JOIN Gazetteer as gazgps ON gpspt.GazetteerID=gazgps.GazetteerID   LEFT JOIN Municipio as munigps ON gazgps.MunicipioID=munigps.MunicipioID  LEFT JOIN Province as provigps ON munigps.ProvinceID=provigps.ProvinceID   LEFT JOIN Country  as countrygps ON provigps.CountryID=countrygps.CountryID";
$sql .= " WHERE pltb.EspecimenID='".$especimenid."')";

//echo "<br />"."<br />".$sql."<br />";
mysql_query($sql,$conn);

$sql2 = "INSERT INTO ".$newtbname." SELECT 0 AS Marcado,tb.* FROM `checklist_speclist` as tb WHERE tb.EspecimenID='".$especimenid."'";
mysql_query($sql2,$conn);
//echo "<br />"."<br />".$sql2."<br />";
$foiumsucesso=1;
echo "
<br />
<table cellpadding=\"7\" width='50%' align='center' class='success'>
  <tr><td class='tdsmallbold' align='center'>".GetLangVar('sucesso1')."</td></tr>";
if ($ispopup==1) {
echo "
  <tr><td class='tdsmallbold' align='center'>
<form>
  <input style='cursor: pointer' type='button' value='Fechar a janela' onclick=\"javascript:window.close();\" />
</form>  
  </td></tr>";
}

////////ACRESCENTAR OPCOES ///
	$delvals = array('prefix',
'sufix',
'detid',
'detdoubt',
'vernacularvalue',
'arrayofvars',
'colnum',
'cooraction',
'detaction',
'especimenid',
'fert',
'final',
'habaction',
'localaction',
'plantaid',
'traitarray',
'traitids',
'dettext',
'detset',
'detid',
'vernaculartxt',
'vernacularvalue');
unset($detset);
	foreach ($delvals as $vv) {
		unset($ppost[$vv]);
	}
	unset($_SESSION['variation']);
	unset($_SESSION['editando']);
echo "
  <tr><td class='tdsmallbold' align='center' >
  <form action='especimenes_dataform.php' method='post'>";
foreach ($ppost as $kk => $vv) {
echo "<input type='hidden' value='".$vv."' name='".$kk."' />";

}
echo "<input type='submit' class='bsumit' value='Adicionar outra mesmo local' />
</form>  
</td></tr>";

echo "
</table>
<br />";
		}
	} //se erro==0

////////////////////////////////////////
//ISSO NAO DEVE FAZER NADA NESTE MOMENTO
	if ($final==2 && $erro==0) {
		//unset($prefix,$sufix,$detid,detdoubt,$vernacularvalue,$arrayofvars,$colnum,$cooraction,$detaction,$especimenid,$fert,$habaction, $localaction, $plantaid, $traitarray, $traitids,$dettext,$detset,$detid,$vernaculartxt,$vernacularvalue);
		//unset($_SESSION['variation']);
		//unset($_SESSION['editando']);
	} 
	elseif ($erro==0 && $final==1) {
		unset($prefix,$sufix,$addcoltxt, $addcolvalue, $locality, $gpspt, $detid, $vernaculartxt,$vernacularvalue, $altitude, $altmax, $altmin, $arrayofvars, $colnum, $cooraction, $datacol,$detaction, $determinadorid, $especimenid, $gazetteerid, $localidadeid, $gpspointid, $projetoid, $habaction, $habitatid, $habito, $latgrad, $latminu, $latnors, $latsec, $localaction, $longgrad, $longminu, $longsec, $longwore, $pessoaid, $plantaid, $traitids,$plantaid,$dettext,$detset);
		unset($_SESSION['variation']);
		unset($_SESSION['editando']);
	} 
	if ($final==3 && $erro==0) {
	}
/////////////////////////////////////////


}  //se final nao estiver vazio



//////////O FORMULARIO ///////////
if ($foiumsucesso!=1 || $final==2) {
//se for novo limpa garante que variaves estao limpas
if ($submeteu=='nova' || $final==2) {
	unset($_SESSION['variation']);
	unset($_SESSION['editando']);
}
//se for edicao extrair info antiga a primeira vez e armazenar info de que esta 
if (($submeteu=='editando' && ($erro+0)==0) || ($especimenid>0 && empty($final) && ($erro+0)==0)) {
	if ($localidadetraitid>0) {
	$qlocal = "SELECT * FROM Traits_variation WHERE EspecimenID=".$especimenid." AND TraitID=".$localidadetraitid;
	$rsl = mysql_query($qlocal,$conn);
	$rwl = mysql_fetch_assoc($rsl);
	$notaslocalidade = $rwl['TraitVariation'];
	}
	unset($_SESSION['variation']);
	unset($_SESSION['editando']);
	$_SESSION['editando']=1;
	$qq = "SELECT * FROM Especimenes WHERE EspecimenID=".$especimenid;
	$res = mysql_query($qq,$conn);
	$row = mysql_fetch_assoc($res);
	$detid = $row['DetID'];
	$plantaid = $row['PlantaID'];
	if (($detid+0)==0 && $plantaid>0) {
		$qq = "SELECT DetID FROM Plantas WHERE PlantaID=".$plantaid;
		$rsew = mysql_query($qq,$conn);
		$rwew = mysql_fetch_assoc($rsew);
		$detid = $rwew['DetID'];
	}
	$detset = getdetsetvar($detid,$conn);
	$detset = serialize($detset);
	$dettext = describetaxa($detset,$conn);
	if ($plantaid==0) {unset($plantaid);}

	//coletor e numero
	$pessoaid = $row['ColetorID'];
	$colnum = $row['Number'];
	$prefix = $row['Prefixo'];
	$sufix = $row['Sufix'];
	$yy = $row['Ano'];
	$mm = $row['Mes'];
	$dd = $row['Day'];
	$colldd = $dd;
	$collmm = $mm;
	$collyy = $yy;
	$latdec = $row['Latitude'];
	$longdec = $row['Longitude'];
	$inpaid = $row['INPA_ID'];



	$coord = @coordinates($latdec,$longdec,'','','','','','','','');
	@extract($coord);

	$altitude = $row['Altitude'];
	$altmin = $row['AltitudeMin'];
	$altmax = $row['AltitudeMax'];

	$gazetteerid = $row['GazetteerID'];
	$muniid = $row['MunicipioID'];
	$provid = $row['ProvinceID'];
	$countid = $row['CountryID'];
	if ($gazetteerid>0) {
		$localidadeid = 'gazetteer_'.$gazetteerid;
	} elseif ($muniid>0) {
			$localidadeid = 'municipio_'.$muniid;
		} elseif ($provid>0) {
			$localidadeid = 'province_'.$provid;
		} elseif ($countid>0) {
			$localidadeid = 'country_'.$countid;
	}
	$gpspointid = $row['GPSPointID']+0;
	$projetoid = $row['ProjetoID'];
	$habitatid = $row['HabitatID'];
	$datacol = $yy."-".$mm."-".$dd;

	//traits
	$tempids='';
	$oldvals = storeoriginaldatatopost($especimenid,'EspecimenID',$formid,$conn,$tempids);
	//coletor e outros 
	$addcolvalue = $row['AddColIDS'];
	$addcolarr = explode(";",$addcolvalue);
	$addcoltxt = '';
	$j=1;
	foreach ($addcolarr as $kk => $val) {
		$qq = "SELECT * FROM Pessoas WHERE PessoaID='$val'";
		$res = mysql_query($qq,$conn);
		$rrw = mysql_fetch_assoc($res);
		if ($j==1) {
			$addcoltxt = 	$rrw['Abreviacao'];
		} else {
			$addcoltxt = $addcoltxt."; ".$rrw['Abreviacao'];
		}
		$j++;
	}

	//vernacular
	$vernacularvalue = $row['VernacularIDS'];
	$vernarr = explode(";",$vernacularvalue);
	$vernaculartxt = '';
	$j=1;
	foreach ($vernarr as $kk => $val) {
		$qq = "SELECT * FROM Vernacular WHERE VernacularID='$val'";
		$res = mysql_query($qq,$conn);
		$rrw = mysql_fetch_assoc($res);
		if ($j==1) {
			$vernaculartxt =  $rrw['Vernacular'];
			if (!empty($rrw['Language'])) { $vernaculartxt=$vernaculartxt." (".$rrw['Language'].")";}
		} else {
			if (!empty($rrw['Language'])) { $vtxt= $rrw['Vernacular']." (".$rrw['Language'].")";} else {$vtxt=$rrw['Vernacular'];}
			$vernaculartxt = $vernaculartxt."; ".$vtxt;
		}
		$j++;
	}

	$_SESSION['variation'] = serialize($oldvals);

	//if (!empty($lixo)) { $plantaid = $newplantaid;}

}

//processa acao da comparacao entre planta e coleta quando for o caso (e.g. amostra de planta marcada)
if ($plantaid>0 && is_numeric($plantaid) && empty($final)) {
		$qq = "SELECT * FROM Plantas WHERE PlantaID='".$plantaid."'";
		$rr = mysql_query($qq,$conn);
		$row= mysql_fetch_assoc($rr);
		$inexsitu = $row['InSituExSitu'];
		$plantnum = sprintf("%06s", $row['PlantaTag']);
		if ($inexsitu=='Insitu') { $plantnum = $insituprefix.$plantnum;}
		if ($inexsitu=='Exsitu') { $plantnum = $exsituprefix.$plantnum;}
		$gpspointid = $row['GPSPointID'];
		$gazetteerid = $row['GazetteerID'];
		$projetoid = $row['ProjetoID'];
		$habitatid = $row['HabitatID'];
		if (empty($dettext)) {
			$detset = getdetsetvar($row['DetID'],$conn);
			$detset = serialize($detset);
			$dettext = describetaxa($detset,$conn);
		}
}
$muniid = $muniid+0;
$provid = $provid+0;
$countid = $countid+0;
if (($gpspointid+0)>0 || $gazetteerid>0 || $muniid>0 || $provid>0 || $countid>0) {
	$qq = "SELECT localidadestring(".($gazetteerid+0).",".($gpspointid+0).",".$muniid.",".$provid.",".$countid.",".($latdec+0).", ".($longdec+0).", ".($altitude+0).") as locality";
	//echo $qq."<br>";
	$riq = mysql_query($qq,$conn);
	$riw = mysql_fetch_assoc($riq);
	$localtxt = $riw['locality'];
	if ($gpspointid>0) {
		$gaztxt = '';
		$gpstxt = $localtxt;
	} else {
		$gaztxt = $localtxt;
		$gpstxt = '';
	}
} 
//variaveis dos formularios
$arrayofvars = array("detdoubt" => $detdoubt, "vernacularvalue" => $vernacularvalue, "vernaculartxt" => $vernaculartxt, "addcoltxt" => $addcoltxt, "addcolvalue" => $addcolvalue, "altitude" => $altitude, "altmax" => $altmax, "altmin" => $altmin, "colnum" => $colnum, "datacol" => $datacol, "datadet" => $datadet, "determinadorid" => $determinadorid, "especimenid" => $especimenid, "famid" => $famid, "fert" => $fert, "gazetteerid" => $gazetteerid, "genusid" => $genusid, "habitatid" => $habitatid, "habito" => $habito, "infraspid" => $infraspid, "latgrad" => $latgrad, "latminu" => $latminu, "latnors" => $latnors, "latsec" => $latsec, "longgrad" => $longgrad, "longminu" => $longminu, "longsec" => $longsec, "longwore" => $longwore, 'latdec' => $latdec, 'longdec' => $longdec, "plantaid" => $plantaid, "pessoaid" => $pessoaid, "speciesid" => $speciesid, "traitids" => $traitids, 'arrayofvars' => $arrayofvars, 'cooraction' => $cooraction, 'localaction' => $localaction, 'habaction' => $habaction, 'detaction' => $detaction, 'detid' => $detid);

//extrair dados de habitat
if (!empty($habitatid)) {
	$habitat = describehabitat($habitatid,$img=TRUE,$conn);
}

if (!empty($_SESSION['variation'])) {
	$traitarray = unserialize($_SESSION['variation']);
	$traitids = describetraits($traitarray,$img=TRUE,$conn);
} 

echo "<br />
<table class='myformtable' align='center' cellpadding='7' width='80%' >
<thead>
<tr >
  <td colspan='2'>";
	if ($_SESSION['editando']==1) {
		$pr = GetLangVar('nameeditando')." ";
	} else {
		$pr = GetLangVar('namenova')." ";
	}
	$pr .= strtolower(GetLangVar('nameamostra'))." ".strtolower(GetLangVar('namecoletada'))."&nbsp;<img height='13' src='icons/icon_question.gif'";
	echo $pr;
	$help = GetLangVar('helpamostracoletada');
	echo " onclick=\"javascript:alert('$help');\" /></td>
</tr>
</thead>
<tbody>";

if ($plantaid>0) {
	if ($bgi % 2 == 0){$bgcolor = $linecolor2 ;} else{$bgcolor = $linecolor1 ;} $bgi++; 
echo "
<tr bgcolor = '".$bgcolor."'>
  <td class='tdsmallboldright'>".GetLangVar('nametaggedplant')."</td>
  <td>
  <input type='hidden' name='plantnum' value='".$plantnum."' />
  <input type='hidden' name='plantaid' value='".$plantaid."' />
  <input type='text' class='selectedval' value='".$plantnum."' readonly /></td>
</tr>";
}

if ($bgi % 2 == 0){$bgcolor = $linecolor2 ;} else{$bgcolor = $linecolor1 ;} $bgi++; 
echo "
<tr bgcolor = '".$bgcolor."'>
<form name='coletaform' action='especimenes_dataform.php' method='post'>
<input type='hidden' value='$ispopup' name='ispopup' />
<input type='hidden' value='$especimenid' name='especimenid' />
<input type='hidden' value='$plantaid' name='plantaid' />
<td class='tdsmallboldright'>".GetLangVar('namecoletor')."</td>
<td >
  <table  align='left' border=0 cellpadding=\"7\" cellspacing=\"0\">
    <tr>
      <td><img src='icons/list-add.png' height='15' ";
		$myurl ="novapessoa-form-popup.php?pessoaid_val=coletorid"; 
		echo " onclick = \"javascript:small_window('$myurl',500,350,'Nova Pessoa');\"></td>
	";
		if ($_SESSION['editando']!=1 ||  $_SESSION['accesslevel']=='admin') {
			echo "
      <td class='tdsmallnotes'>
        <select id='coletorid' name='pessoaid'>";
			if (empty($pessoaid)) {
				echo "
          <option value='' class='optselectdowlight'>".GetLangVar('nameselect')."</option>";
			} 
			else {
				$rr = getpessoa($pessoaid,$abb=FALSE,$conn);
				$row = mysql_fetch_assoc($rr);
				echo "
          <option selected class='selectedval' value=".$row['PessoaID'].">".$row['Abreviacao']." [".$row['Prenome']."]</option>";
			}
			$rrr = getpessoa('',$abb=TRUE,$conn);
			while ($row = mysql_fetch_assoc($rrr)) {
				echo "
          <option value=".$row['PessoaID'].">".$row['Abreviacao']." [".$row['Prenome']."]</option>";
			}
			echo "
        </select>";
		} else {
			$rr = getpessoa($pessoaid,$abb=TRUE,$conn);
			$row = mysql_fetch_assoc($rr);
			$nnome = $row['Abreviacao']." [".$row['Prenome']."]";
			echo "
      <input type='hidden'  value='$pessoaid' name='pessoaid' />
      <td><input class='selectedval' type='text'  value='$nnome' readonly />";
		}
echo "</td>
      <td class='tdsmallboldright'>".GetLangVar('namenumber')."</td>";
	if ($_SESSION['editando']==1 && !empty($colnum) &&  $_SESSION['accesslevel']!='admin') {
	echo "
      <td >
        <input type='hidden' name='colnum' value='$colnum' size='5' />
        <input class='selectedval' size='8' type='text' value='$colnum' readonly />
      </td>";
} else {
	echo "
      <td><input type='text' name='colnum' value='$colnum' size='8' /></td>";
}
echo "
      <td>
        <table cellpadding='3'>
            <tr>
              <td class='tdsmallboldright'>".GetLangVar('namedata')."</td>";
			//if ((empty($datacol) && $_SESSION['editando']!=1) || $final=='2' || $_SESSION['accesslevel']=='admin') {
	echo "
              <td>
                <table>
                  <tr>
                    <td><input name=\"datacol\" value=\"$datacol\" size=\"11\" readonly /></td>
                    <td><a onclick=\"if(self.gfPop)gfPop.fPopCalendar(document.forms['coletaform'].datacol,[[1800,01,01],[2020,01,01]]);return false;\" ><img name=\"popcal\" align=\"absmiddle\" src=\"calendar/calbtn.gif\" width=\"34\" height=\"22\" border=\"0\" alt=\"\"></a></td>
                  </tr>
                </table>
              </td>";
if (empty($datacol)) {              
echo "
              <!---<td>ou&nbsp;</td>
              <td>
                <table cellpadding='3'>
                  <tr>
                    <td class='tdsmallbold'>Ano&nbsp;<input type='text' name=\"collyy\" value=\"$collyy\" size=\"2\" /></td>
                    <td class='tdsmallbold'>Mes&nbsp;<input type='text' name=\"collmm\" value=\"$collmm\" size=\"2\" /></td>
                    <td class='tdsmallbold'>Dia&nbsp;<input type='text' name=\"colldd\" value=\"$colldd\" size=\"2\" /></td>
                  </tr>
                </table>
              </td>
              --->";
}              
echo "
            </tr>
          </table>
        </td>
              ";

		//} else {
			//echo "
			//<td ><input class='selectedval' type='text'  value='$datacol' name='datacol' readonly /></td>";
		//}
	echo "
    </tr>
  </table>
</td>
</tr>";

if ($bgi % 2 == 0){$bgcolor = $linecolor2 ;}  else{$bgcolor = $linecolor1 ;} $bgi++;
echo "
<tr bgcolor = '".$bgcolor."'>
  <input type='hidden' id='addcolvalue'  name='addcolvalue' value='$addcolvalue' />
  <td class='tdsmallboldright'>".GetLangVar('nameaddcoll')."</td>
  <td >
    <table>
      <tr>
        <td class='tdformnotes' ><textarea name='addcoltxt' id='addcoltxt'  col=100 row=3 readonly>".$addcoltxt."</textarea></td>
        <td><input type=button value=\"+\" class='bsubmit'  ";
		$myurl ="addcollpopup.php?valuevar=addcolvalue&addcoltxt=addcoltxt&getaddcollids=".$addcolvalue."&formname=coletaform"; 
		echo " onclick = \"javascript:small_window('$myurl',800,500,'Seleciona Coletores Adicionas');\" /></td>
      </tr>
    </table>
  </td>
</tr>
";
//taxonomia
if ($bgi % 2 == 0){$bgcolor = $linecolor2 ;} else{$bgcolor = $linecolor1 ;} $bgi++;
echo "
<tr bgcolor = '".$bgcolor."'>
  <td class='tdsmallboldright'>".GetLangVar('nametaxonomy')."</td>
  <td >
    <table >
      <tr >
        <td id='dettexto'>$dettext</td>
        <input type='hidden' id='detsetcode' name='detset' value='".$detset."' />";
		if (empty($dettext)) {
				$butname = GetLangVar('nameselect');
			} else {
				$butname = GetLangVar('nameeditar');
		} 
		echo "
        <td><input type=button value='$butname' class='bsubmit' ";
			$myurl ="taxonomia-popup.php?ispopup=1&detid=$detid&dettextid=dettexto&detsetid=detsetcode"; 
			echo " onclick = \"javascript:small_window('$myurl',800,400,'TaxonomyPopup');\" /></td>";
		if ($_SESSION['editando']) {
			echo "
        <td><input type=button value='DetHistory' class='bblue' ";
			$myurl =" detchangespopup.php?ispopup=1&especimenid=$especimenid"; 
			echo " onclick = \"javascript:small_window('$myurl',800,300,'Det History');\" /></td>";
		}
	echo "
      </tr>
    </table>
  </td>
</tr>";
if ($bgi % 2 == 0){$bgcolor = $linecolor2 ;} else{$bgcolor = $linecolor1 ;} $bgi++;
echo "
<tr bgcolor = '".$bgcolor."'>
  <input type='hidden' name='vernacularvalue' value='$vernacularvalue' />
  <td class='tdsmallboldright'>".GetLangVar('namevernacular')."</td>
  <td >
    <table>
      <tr>
        <td class='tdformnotes' ><input size='30%' type='text' name='vernaculartxt' value='$vernaculartxt' readonly /></td>
        <td><input type=button value=\"+\" class='bsubmit' ";
		$myurl ="vernacular_selector.php?getvernacularids=$vernacularvalue&formname=coletaform"; 
		echo " onclick = \"javascript:small_window('$myurl',350,280,'Add_from_Src_to_Dest');\" /></td>
      </tr>
    </table>
  </td>
</tr>
"; 
//dados de localidade
if ($bgi % 2 == 0){$bgcolor = $linecolor2 ;} else{$bgcolor = $linecolor1 ;} $bgi++;
echo "
<tr bgcolor = '".$bgcolor."'>
  <td class='tdsmallboldright'>".GetLangVar('namelocalidade')."&nbsp;<img height='14' src=\"icons/icon_question.gif\" ";
	$help = 'Selecione uma localidade ou um ponto de gps para a amostra. Detalhes na localidade podem ser colocada no campo de notas de localidade';
	echo " onclick=\"javascript:alert('$help');\" /></td>
  <td>
  <table>
    <tr><td class='tdformnotes' colspan='2'>".$localtxt."</td></tr>
    <tr>
      <td class='tdsmallboldright'>OPÇÃO&nbsp;01&nbsp;-&nbsp;Localidade&nbsp;<img height='14' src=\"icons/icon_question.gif\" ";
	$help = 'Se for uma localidade dentro do município e você for cadastrar como nova, procure cadastrar apenas a informação chave da localidade e deixe detalhes para NOTAS DE LOCALIDADE. Por exemplo, pode cadastrar Comunidade Seringalzinho com sublocalidade de Parque Nacional do Jaú e colocar em NOTAS coisas como, Trilha atrás da comunidade, Campina próxima à comunidade, etc. Localidades podem ser hierárquicas mas servem para agregar e portanto devem ser simples e abrangentes';
	echo " onclick=\"javascript:alert('$help');\" /></td>
      <td>"; 
		autosuggestfieldval5('search-localidadeseadmin.php','locality',$gaztxt,'localres','localidadeid',$localidadeid,true,60,'País, provincia , município ou localidade cadastrada');
		echo "
      </td>";
	  $myurl = "localidade_dataexec.php?ispopup=1&municipioid=$municipioid&paisid=$paisid&provinciaid=$provinciaid";
		echo "
      <td><input type=button class='bblue' value='".GetLangVar('namenova')."'  onclick =\"javascript:small_window('$myurl',900,300,'Cadastrar nova localidade');\" /></td>
	</tr>
    <tr>
      <td class='tdsmallboldright'>OPÇÃO&nbsp;02&nbsp;-&nbsp;Ponto&nbsp;de&nbsp;GPS</td>
      <td>"; 
		autosuggestfieldval5('search-gpspoint.php','gpspt',$gpstxt,'gpsres','gpspointid',$gpspointid,true,60, 'Ponto de GPS cadastrado'); 
		echo "
      </td>
</tr>";
if (isset($localidadetraitid)) {
echo "
    <tr>
      <td class='tdsmallboldright'>Notas de localidade</td>
      <td><textarea name='notaslocalidade' rows=3 cols=50>".$notaslocalidade."</textarea></td>
	</tr>";
}
echo "
  </table>
  </td>
</tr>";

//habitat descricao
if ($bgi % 2 == 0) {$bgcolor = $linecolor2 ;} else { $bgcolor = $linecolor1 ;} $bgi++;
	echo "
<tr bgcolor = '".$bgcolor."'>
  <td class='tdsmallboldright'>".GetLangVar('namehabitat')."</td>
  <td >
    <table align='left' cellpadding=\"7\" cellspacing=\"0\" class='tdformnotes'>
      <input type='hidden' id='habitatidfield'  name='habitatid' value='$habitatid' />
      <tr>
        <td id='habitatfield' class='tdformnotes'>$habitat</td>";
		if (empty($habitatid)) {
			$buthab = GetLangVar('nameselect');
		} else {
			$buthab = GetLangVar('nameeditar');
		} 
		echo "
        <td align='center'><input type='button' value='$buthab' class='bsubmit' onclick = \"javascript:small_window('habitat-popup.php?ispopup=1&pophabitatid=$habitatid&elementidval=habitatidfield&elementidtxt=habitatfield&opening=1',850,400,'Selecione um habitat');\" /></td>
      </tr>
    </table>
  </td>
</tr>";
if ($plantaid==0 || !isset($plantaid) || empty($plantaid)) {
///coordenadas da planta
if ($bgi % 2 == 0) {$bgcolor = $linecolor2 ;} else { $bgcolor = $linecolor1 ;} $bgi++;
	echo "
<tr bgcolor = '".$bgcolor."'>
  <td align='right'>
    <table>
      <tr><td class='tdsmallboldright'>".GetLangVar('namecoordenadas')."</td><td align='right'><img height='13' src=\"icons/icon_question.gif\" ";
				$help = GetLangVar('messageexplaincoordenadas');
				echo " onclick=\"javascript:alert('$help');\" /></td></tr>
    </table>
  </td>
  <td>
    <table>
      <tr class='tdformnotes'>
        <td align='right'><i>Latitude</i></td>
        <td >
          <table border=0 cellpadding=\"3\">
            <tr class='tdformnotes'>
              <td ><input type='text' size='6' name='latgrad' value='$latgrad' /></td>
              <td align='left'><sup>o</sup></td>
              <td ><input type='text' size='3' name='latminu' value='$latminu' /></td>
              <td align='left'>'</td>
              <td ><input type='text' size='3' name='latsec' value='$latsec' /></td>
              <td align='left'>\"</td>
              <td align='right'><input type='radio' name='latnors' "; 
				if ($latnors=='N') { echo "checked";}
				echo " value='N' /></td>
              <td align='left'>N</td>
              <td align='right'><input type='radio' name='latnors' "; 
					if ($latnors=='S') { echo "checked";}
					echo "  value='S' /></td>
              <td align='left'>S</td>
            <tr>
          </table>
        </td>
      </tr>
      <tr>
        <td align='right'><i>Longitude</i></td>
        <td >
          <table border=0 cellpadding=\"3\">
            <tr class='tdformnotes'>
              <td align='center'><input type='text' size='6' name='longgrad' value='$longgrad' /></td>
              <td align='left'><sup>o</sup></td>
              <td align='left'><input type='text' size='3' name='longminu' value='$longminu' /></td>
              <td align='left'>'</td>
              <td align='left'><input type='text' size='3' name='longsec' value='$longsec' /></td>
              <td align='left'>\"</td>
              <td align='right'><input type='radio' name='longwore' "; 
			if ($longwore=='W') { echo "checked";}
			echo " value='W' /></td>
              <td align='left'>W</td>
              <td align='right'><input type='radio' name='longwore' "; 
			if ($longwore=='E') { echo "checked";}
			echo "  value='E' /></td>
              <td align='left'>E</td>
            </tr>
          </table>
        </td>
      </tr>
      <tr>
        <td align='right'><i>Altitude</i></td>
        <td >
          <table border=0 cellpadding=\"3\">
            <tr class='tdformnotes'>
              <td align='center'><input type='text' size='6' name='altitude' value='$altitude' /></td>
              <td align='left'>m</td>
            </tr>
          </table>
        </td>
      </tr>
    </table>
  </td>
</tr>";
}
if ($bgi % 2 == 0){$bgcolor = $linecolor2 ;} else {$bgcolor = $linecolor1 ;} $bgi++;
if (empty($traitids)) {
	$butname = GetLangVar('nameselect');
} else {
	$butname = GetLangVar('nameeditar');
} 
echo "
<tr bgcolor = '".$bgcolor."'>
  <td class='tdsmallboldright'>".GetLangVar('nameobs')."s</td>
  <td >
    <table  align='left' border=0 cellpadding=\"7\" cellspacing=\"0\" class='tdformnotes'>
      <tr>
        <td id='traitids' class='tdformnotes'>".$traitids."</td>
        <td align='left'><input  type='button' value='$butname' class='bsubmit' onclick = \"javascript:small_window('traits_coletorvariacao.php?ispopup=1&elementid=traitids',800,500,'Entrar Variacao');\" /></td>
      </tr>
    </table>
  </td>
</tr>";
if ($bgi % 2 == 0){$bgcolor = $linecolor2 ;} else {$bgcolor = $linecolor1 ;} $bgi++;
echo "
<tr bgcolor = '".$bgcolor."'>
  <td class='tdsmallboldright'>".GetLangVar('nameprojeto')."</td>
  <td >
    <select name='projetoid' >";
		if ($projetoid==0 || empty($projetoid)) {
		echo "
      <option value=''>".GetLangVar('nameselect')."</option>";
			} else {
				$qq = "SELECT * FROM Projetos WHERE ProjetoID='".$projetoid."'";
				$prjres = mysql_query($qq,$conn);
				$prjrow = mysql_fetch_assoc($prjres);
				echo "
      <option  selected value='".$prjrow['ProjetoID']."'>".$prjrow['ProjetoNome']."</option>";
			}
			echo "
      <option value=''>----</option>";
		$qq = "SELECT * FROM Projetos ORDER BY ProjetoNome";
		$resss = mysql_query($qq,$conn);
		while ($rwww = mysql_fetch_assoc($resss)) {
			echo "
      <option   value='".$rwww['ProjetoID']."'>".$rwww['ProjetoNome']."</option>";
		}
	echo "
    </select>
  </td>
</tr>";
if ($bgi % 2 == 0){$bgcolor = $linecolor2 ;} else {$bgcolor = $linecolor1 ;} $bgi++;
echo "
<tr bgcolor = '".$bgcolor."'>
  <td colspan='100%'>
    <table align='center' >
      <tr>
        <input type='hidden' name='final' value='' />
        <td align='center' ><input style='cursor: pointer'  type='submit' value='".GetLangVar('namesalvar')."' class='bsubmit' onclick=\"javascript:document.coletaform.final.value='1'\" /></td>
      </tr>
    </table>
  </td>
</tr>
</form>
</tbody>
</table>
";
} 

}

$which_java = array("<script type='text/javascript' src='javascript/myjavascripts.js'></script>");
FazFooter($which_java,$calendar=TRUE,$footer=$menu);

?>