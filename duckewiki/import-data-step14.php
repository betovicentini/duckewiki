<?php
//este script finaliza a importacao
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

$menu = FALSE;
$which_css = array(
"<link href='css/geral.css' rel='stylesheet' type='text/css' />");
$which_java = array();
$title = 'Importar Dados Passo 14';
$body = '';
FazHeader($title,$body,$which_css,$which_java,$menu);

//echopre($ppost);
$lixo=11;
if ($lixo>10) {
if (empty($arraysubmited)) {
	$qq = "ALTER TABLE `".$tbname."` ADD COLUMN ".$tbprefix."_CRMODIPLANTASIDS INT(1) DEFAULT 0, ADD COLUMN ".$tbprefix."_CRMODISPECSIDS INT(1) DEFAULT 0";
 	@mysql_query($qq,$conn);
 	$qq = "SHOW COLUMNS FROM `".$tbname."` LIKE '".$tbprefix."%'";
 	$rq = mysql_query($qq,$conn);
 	$fieldschecked = array();
 	$arrayoffields = array();
 	$monitorvars = array();
 	$estaticvars = array();
 	$idtocomp = array('FamiliaID','GeneroID', 'EspecieID', 'InfraEspecieID');
	$plantas_arra = array();
    if ($coletas==1) {
 		$qq = "SELECT FieldsToPut FROM Import_Fields WHERE TabelaParaPor LIKE '%Plantas%' AND FieldsToPut<>'' AND FieldsToPut IS NOT NULL";
 		$rqq = mysql_query($qq,$conn);
 		while ($rww = mysql_fetch_assoc($rqq)) {
 			$plantas_arra[] = $rww['FieldsToPut'];
 		}
 	}
 	$coletas_arra = array();
 	if ($coletas==2 || $coletas==3) {
 		$qq = "SELECT FieldsToPut FROM Import_Fields WHERE TabelaParaPor LIKE '%Especimenes%' AND FieldsToPut<>'' AND FieldsToPut IS NOT NULL";
 		$rqq = mysql_query($qq,$conn); 
 		while ($rww = mysql_fetch_assoc($rqq)) {
 			$coletas_arra[] = $rww['FieldsToPut'];
 		}
 	}
	$identidate_arr = array();
	$qq = "SELECT FieldsToPut FROM Import_Fields WHERE TabelaParaPor LIKE '%Identidade%'";
 	$rqq = mysql_query($qq,$conn); 
 	while ($rww = mysql_fetch_assoc($rqq)) {
 		$identidate_arr[] = $rww['FieldsToPut'];
 	}
 	while ($rw = mysql_fetch_assoc($rq)) {
	$rz = $rw['Field'];
	$rzz = str_replace($tbprefix,"",$rz);
	//$fieldschecked[$rzz] = $rz;
	if ($rzz=='PlGazetteerID') {
		$rzz = 'GazetteerID';
	}
	$arrayoffields['allfield'][$rzz] = $rz;
	if (@in_array($rzz,$plantas_arra)) {
		$arrayoffields['plantas'][$rzz] = $rz;
	}
	if (@in_array($rzz,$coletas_arra)) {
		//$coletas_basico[$rzz] = $rz;
		$arrayoffields['coletas'][$rzz] = $rz;
	}
	if (@in_array($rzz,$identidate_arr)) {
		//$identidate_basico[$rzz] = $rz;
		$arrayoffields['identidade'][$rzz] = $rz;
	}
	if (@in_array($rzz,$idtocomp)) {
		//$idtocomp_basico[$rzz] = $rz;
		$arrayoffields['identidade_tocheck'][$rzz] = $rz;
	}
	$rt = substr($rzz,0,8);
	if ($rt=='ESTATIC_') {
			$rz1 = str_replace('ESTATIC_',"",$rzz);
			$estaticvars[$rz1] = $rz;
			$arrayoffields['estaticvars'][$rz1] = $rz;
	}
	$rt = substr($rzz,0,14);
	if ($rt=='MONITORAMENTO_') {
			$rz1 = str_replace('MONITORAMENTO_',"",$rzz);
			$monitorvars[$rz1] = $rz;
			$arrayoffields['monitorvars'][$rz1] = $rz;
	}
 	}
 
 	$qq = "SELECT COUNT(*) as ntotal FROM `".$tbname."`";
	$rq = mysql_query($qq,$conn);
	$rw = mysql_fetch_assoc($rq);
	$ntotal = $rw['ntotal'];
	$arrayoffields['ntotal'] = $ntotal;
	$nrecstart = 0;
	$nrecs = 100;
	$arrayoffields['plins'] = 0;
	$arrayoffields['plup'] = 0;
	$arrayoffields['spins'] = 0;
	$arrayoffields['spup'] = 0;
	$arrayoffields['idsins'] = 0;
	$arrayoffields['idsups'] = 0;
	$arrayoffields['idsnochange'] = 0;
	$arrayoffields['moni'] = 0;
	$arrayoffields['esta'] = 0;
	$arrayoffields['idx'] = 0;
	$arrayoffields['crmodi_plantasids'] = array();
	$arrayoffields['crmodi_especimenid'] = array();
	$arrayoffields['traitidsmoni'] = array();
	$arrayoffields['traitidsestatic'] = array();
} 
else {
	$nrecs = 100;
	$arrayoffields = unserialize($arraysubmited);
	$nrecstart = $arrayoffields['reclimit']+$nrecs;
	$ntotal = $arrayoffields['ntotal'];
}
	$arrayoffields['reclimit'] = $nrecstart;
	$ff = implode(",", $arrayoffields['allfield']);
	$qq = "SELECT ImportID,$ff FROM `".$tbname."` ";
	if ($taggedplants=='1') {

		//$qq .= " WHERE `".$tbprefix."_CRMODIPLANTASIDS`=0 ";
	}
	if ($coletas=='1') {
		if ($taggedplants=='1') { //$qq .= " AND `".$tbprefix."_CRMODISPECSIDS`=0 ";} else
		//$qq .= " WHERE `".$tbprefix."_CRMODISPECSIDS`=0 ";
		}
	}
	$qq .= "LIMIT $nrecstart,$nrecs";
	//echo $qq."<br />";
	$rq = mysql_query($qq,$conn);
	$numrecs = mysql_numrows($rq);
	if ($numrecs>0) {
		//echopre($_POST);
	//echo "----------<br />";
	while ($rw = mysql_fetch_assoc($rq)) {
		//echopre($rw);
		unset($upvars);
		$plkk = array_keys($rw);
		$plvv = array_values($rw);
		$olddetid=0;
		$plantaid=0;
		$especimenid=0;
		if ($coletas==1) {
			$plantaid = $rw[$tbprefix.'PlantaID'];
			$kk = @array_intersect($plkk,$arrayoffields['plantas']);
			$valores = @array_intersect_key($plvv,$kk);
			$tokk = @array_keys($arrayoffields['plantas']);
			$arrayofvalues = @array_combine($tokk, $valores);
			//echo "este é o novo";
			//echopre($arrayofvalues);
			if (count($arrayofvalues)>0) {
				if (empty($plantaid) || $plantaid==0) {
					$pltag = $rw[$tbprefix.'PlantaTag'];
					$plgaz = $rw[$tbprefix.'PlGazetteerID'];
					$qq = "SELECT * FROM `".$tbname."`  WHERE `".$tbprefix."PlantaTag`='".$pltag."' AND  `".$tbprefix."PlantaID`>0 AND `".$tbprefix."PlGazetteerID`=".$plgaz;

					//echo $qq."<br />";
					$rrr = @mysql_query($qq,$conn);
					$nrrr = mysql_numrows($rrr);
					if ($nrrr==1) {
						$rwww = mysql_fetch_assoc($rrr);
						$plantaid = $rwww[$tbprefix."PlantaID"];
						$upvars = 'adicionar';
						$qq = "UPDATE `".$tbname."` SET `".$tbprefix."_CRMODIPLANTASIDS`=1 WHERE ImportID=".$rw['ImportID'];
						mysql_query($qq,$conn);
					}
				}
				if (empty($plantaid) || $plantaid==0) {
					$plantaid = InsertIntoTable($arrayofvalues,'PlantaID','Plantas',$conn);
					if ($plantaid>0) {
						$arrayoffields['plins']++;
						$qq = "UPDATE `".$tbname."` SET `".$tbprefix."_CRMODIPLANTASIDS`=1, `".$tbprefix."PlantaID`=".$plantaid." WHERE ImportID=".$rw['ImportID'];
						mysql_query($qq,$conn);
						$arrayoffields['crmodi_plantasids'][] = $plantaid;

					} else {
						$plnotinserted++;
					}
				} 
				else { //caso contrario faz um update dos valores
					$upp = CompareOldWithNewValues('Plantas','PlantaID',$plantaid,$arrayofvalues,$conn);
					if (!empty($upp) && $upp>0) { 
						CreateorUpdateTableofChanges($plantaid,'PlantaID','Plantas',$conn);
						$updatepl = UpdateTable($plantaid,$arrayofvalues,'PlantaID','Plantas',$conn);
						if ($updatepl) {
							$arrayoffields['plup']++;
							$arrayoffields['crmodi_plantasids'][] = $plantaid;
							$qq = "UPDATE `".$tbname."` SET `".$tbprefix."_CRMODIPLANTASIDS`=1 WHERE ImportID=".$rw['ImportID'];
							mysql_query($qq,$conn);
							$arrayoffields['crmodi_plantasids'][] = $plantaid;
						} else {
							$plnotupdated++;
						}
					}
				}
			}
			if ($plantaid>0) {
				$qq = "SELECT DetID FROM Plantas WHERE PlantaID='".$plantaid."'";
				$rids = mysql_query($qq,$conn);
				$ddid = mysql_fetch_assoc($rids);
				$olddetid = $ddid['DetID']+0;
				//echo $olddetid."  aqui <br />";
			}
		}
		if ($coletas==2 || $coletas==3) {
			$kk = array_intersect($plkk,$arrayoffields['coletas']);
			$valores = array_intersect_key($plvv,$kk);
			$tokk = array_keys($arrayoffields['coletas']);
			$arrayofvalues = array_combine($tokk, $valores);
			$plantaid = $rw[$tbprefix.'PlantaID']+0;
			$especimenid = $rw[$tbprefix.'EspecimenID']+0;
			//echo "here-------------$especimenid";
			//echopre($arrayofvalues);
			if (count($arrayofvalues)>0) {
				if ($plantaid>0) {
					$aa = array('PlantaID' => $plantaid);
					$arrayofvalues = array_merge((array)$aa,(array)$arrayofvalues);
				}
				if (empty($especimenid) || $especimenid==0) { //garante update caso o registro tenha sido inserido anteriormente pelo script.
					$specol = $rw[$tbprefix.'ColetorID'];
					$specnum = $rw[$tbprefix.'Number'];
					$qq = "SELECT * FROM `".$tbname."`  WHERE `".$tbprefix."ColetorID`='".$specol."' AND `".$tbprefix."Number`='".$specnum."' AND  `".$tbprefix."EspecimenID`>0";
					$rrr = @mysql_query($qq,$conn);
					$nrrr = @mysql_numrows($rrr);
					if ($nrrr==1) {
						$rwww = mysql_fetch_assoc($rrr);
						$especimenid = $rwww[$tbprefix."EspecimenID"];
						$upvars = 'adicionar';
						$qq = "UPDATE `".$tbname."` SET `".$tbprefix."_CRMODISPECSIDS`=1 WHERE ImportID=".$rw['ImportID'];
						mysql_query($qq,$conn);
					}
				}
				if (empty($especimenid) || $especimenid==0) { //caso o registro da amostra não tenha sido feito, insere a exsicata
					//echopre($arrayofvalues);
					$especimenid = InsertIntoTable($arrayofvalues,'EspecimenID','Especimenes',$conn);
					if ($especimenid>0) {
						$arrayoffields['spins']++;
						$qq = "UPDATE `".$tbname."` SET `".$tbprefix."_CRMODISPECSIDS`=1, `".$tbprefix."EspecimenID`=".$especimenid." WHERE ImportID=".$rw['ImportID'];
						//echo $qq."<br />";
						mysql_query($qq,$conn);
						$arrayoffields['crmodi_especimenid'][] = $especimenid;
					} else {
						$specnotinserted++;
					}
				} else { //caso contrario faz um update dos valores
					$upp = CompareOldWithNewValues('Especimenes','EspecimenID',$especimenid,$arrayofvalues,$conn);
					if ($upp>0) { 
						CreateorUpdateTableofChanges($especimenid,'EspecimenID','Especimenes',$conn);
						$updatespec =UpdateTable($especimenid,$arrayofvalues,'EspecimenID','Especimenes',$conn);
						if ($updatespec) {
							$arrayoffields['spup']++;
							$qq = "UPDATE `".$tbname."` SET `".$tbprefix."_CRMODISPECSIDS`=1 WHERE ImportID=".$rw['ImportID'];
							mysql_query($qq,$conn);
							$arrayoffields['crmodi_especimenid'][] = $especimenid;
						} else {
							$specupdated++;
						}
					}

				}
			}
			if ($especimenid>0) {
					$qq = "SELECT DetID FROM Especimenes WHERE EspecimenID=".$especimenid;
					$rids = mysql_query($qq,$conn);
					$ddid = mysql_fetch_assoc($rids);
					$olddetid = $ddid['DetID']+0;
			}
		}
		$kk = @array_intersect($plkk,$arrayoffields['identidade']);
		$valores = @array_intersect_key($plvv,$kk);
		$tokk = @array_keys($arrayoffields['identidade']);
		$arrayofvalues = @array_combine($tokk, $valores);

		$kk = @array_intersect($plkk,$arrayoffields['identidade_tocheck']);
		$valores = @array_intersect_key($plvv,$kk);
		$tokk = @array_keys($arrayoffields['identidade_tocheck']);
		$arrayofdettocheck = @array_combine($tokk, $valores);
		if (count($arrayofvalues)>0) {
			$upp=0;
			if ($olddetid>0) {
				$upp = CompareOldWithNewValues('Identidade','DetID',$olddetid,$arrayofvalues,$conn);
			}
			if ($upp>0 || $olddetid==0) {
				//echopre($arrayofdettocheck);
				//echopre($arrayofvalues);
				//echo $olddetid."--------<br />";
				$newdetid = InsertIntoTable($arrayofvalues,'DetID','Identidade',$conn);
				$arrayofvalues = array('DetID' => $newdetid);
				if ($plantaid>0) {
					CreateorUpdateTableofChanges($plantaid,'PlantaID','Plantas',$conn);
					$newdet = UpdateTable($plantaid,$arrayofvalues,'PlantaID','Plantas',$conn);
				}
				if ($especimenid>0) {
					CreateorUpdateTableofChanges($especimenid,'EspecimenID','Especimenes',$conn);
					$newdet = UpdateTable($especimenid,$arrayofvalues,'EspecimenID','Especimenes',$conn);
				}
				if ($newdet>0 && $upp>0) {
					$arrayoffields['idsups']++;
				} elseif ($newdet>0) {
					$arrayoffields['idsins']++;
				}
			} else {
				$arrayoffields['idsnochange']++;
			}
		}
		if (!isset($updaterecs) && !isset($upvars)) { $upvars='';} elseif (isset($updaterecs)) { $upvars = $updaterecs;}
		if (count($arrayoffields['monitorvars'])>0 && $plantaid>0) {
			$kk = array_intersect($plkk,$arrayoffields['monitorvars']);
			$valores = array_intersect_key($plvv,$kk);
			$tokk = array_keys($arrayoffields['monitorvars']);
			$arrayofvalues = array_combine($tokk, $valores);
			$trup = updatetraits_monitoramento_onimport($arrayofvalues,$plantaid,$upvars,$conn);
			if (is_array($trup)) {
				$arrayoffields['moni']++;
				$arrayoffields['traitidsmoni']  = array_merge($arrayoffields['traitidsmoni'],$trup);
			}
		}
		if (count($arrayoffields['estaticvars'])>0 && ($especimenid>0 || $plantaid>0)) {
			$kk = array_intersect($plkk,$arrayoffields['estaticvars']);
			$valores = array_intersect_key($plvv,$kk);
			$tokk = array_keys($arrayoffields['estaticvars']);
			$arrayofvalues = array_combine($tokk, $valores);
			if ($coletas==1) {
				$linktype ='PlantaID';
				$linkid = $plantaid; 
			}
			if ($coletas==2 || $coletas==3) {
				$linktype ='EspecimenID';
				$linkid = $especimenid; 
			}
			//echo "variaveis estaticas";
			//echopre($arrayofvalues);
			//echo "variaveis estaticas END";
		 	$staticup = updatetraits_estatic_onimport($arrayofvalues,$linktype,$linkid,$upvars,$conn);
		 	if (is_array($staticup)) {
		 		$nst = count($staticup);
				$arrayoffields['esta'] = $arrayoffields['esta']+$nst;
				$arrayoffields['traitidsestatic']  = array_merge($arrayoffields['traitidsestatic'],$staticup);
			} 
		}
		echo "&nbsp;";
		flush();
	}

echo "
<br />
  <table class='success' cellpadding=\"3\" align='center'>
    <tr><td align='center'>".($nrecstart+$nrecs)." registros de $ntotal importados!</td></tr>
  </table>
<br />";
	flush();
echo "
  <form name='myform' action='import-data-step14.php' method='post'>";
	$zz = unserialize($_SESSION['firstdefinitions']);
	foreach ($zz as $kk => $vv) {
		if (!empty($vv)) {
			echo "
  <input type='hidden' name='".$kk."' value='".$vv."' />";
        }
	}
	echo "
     <input type='hidden' name='arraysubmited' value='".serialize($arrayoffields)."' />
<!---<script language=\"JavaScript\">setTimeout('document.myform.submit()',0.0001);</script>--->
<input type='submit' value='Continuar' class='bsubmit' />
</form>
  ";
  flush();
} 
	else {





	$tbb = str_replace("temp_","",$tbname);
	$filtronome = $tbb."_Importado_".$_SESSION['sessiondate'];

	//$svv = trim($_SESSION['expeditoimporfile']);
	$savetopathfile = "uploads/data_files/".$tbb.".sql";
	backup_tables($tbname,$savetopathfile,$conn);

	if (count($arrayoffields['traitidsestatic'])>0) {
		$trs = array_unique($arrayoffields['traitidsestatic']);
		$trstr =  implode(";",$trs);
		$fomrestatic = "varEsta_".$tbb."_".$_SESSION['sessiondate'];
		$fieldsaskeyofvaluearray = array(
		'FormName' => $fomrestatic,
		'FormFieldsIDS' => $trstr
		);
		$formid = InsertIntoTable($fieldsaskeyofvaluearray,'FormID','Formularios',$conn);
		$formnome = "formid_".$formid;
		foreach ($trs as $value) {
			$sql = "UPDATE Traits SET Traits.FormulariosIDS=IF(Traits.FormulariosIDS<>'',CONCAT(Traits.FormulariosIDS,';','".$formnome."'),'".$formnome."') WHERE Traits.TraitID=".$value;
			mysql_query($sql,$conn);
		}
	}
	if (count($arrayoffields['traitidsmoni'])>0) {
		$trs = array_unique($arrayoffields['traitidsmoni']);
		$trstr =  implode(";",$trs);
		$formmonitor = "varMoni_".$tbb."_".$_SESSION['sessiondate'];
		$fieldsaskeyofvaluearray = array(
		'FormName' => $formmonitor,
		'FormFieldsIDS' => $trstr
		);
		$formid = InsertIntoTable($fieldsaskeyofvaluearray,'FormID','Formularios',$conn);
		$formnome = "formid_".$formid;
		foreach ($trs as $value) {
			$sql = "UPDATE Traits SET Traits.FormulariosIDS=IF(Traits.FormulariosIDS<>'',CONCAT(Traits.FormulariosIDS,';','".$formnome."'),'".$formnome."') WHERE Traits.TraitID=".$value;
			mysql_query($sql,$conn);
		}
	}

	$especimenesids = implode(";",$arrayoffields['crmodi_especimenid']);
	$plantasids = implode(";",$arrayoffields['crmodi_plantasids']);
	$arrayofvals = array('FiltroName' => $filtronome, 'EspecimenesIDS' => $especimenesids, 'FiltroDefinitions' => $definitions, 'PlantasIDS' => $plantasids);
	$newfiltro = InsertIntoTable($arrayofvals,'FiltroID','Filtros',$conn);
    $filnn = "filtroid_".$newfiltro;
	if ($coletas==1 && $newfiltro>0) {
		$sql = "UPDATE `Plantas` as pl, `".$tbname."` as tb SET pl.`FiltrosIDS`=IF(pl.`FiltrosIDS`<>'',CONCAT(pl.`FiltrosIDS`,';','".$filnn."'),'".$filnn."') WHERE pl.`PlantaID`=tb.`".$tbprefix."PlantaID` AND tb.`".$tbprefix."_CRMODIPLANTASIDS`=1"; 
		//echo $sql;
		mysql_query($sql,$conn);
	}

	if (($coletas==2 || $coletas==3) && $newfiltro>0) {
		$sql = "UPDATE `Especimenes` as pl, `".$tbname."` as tb SET pl.`FiltrosIDS`=IF(pl.`FiltrosIDS`<>'',CONCAT(pl.`FiltrosIDS`,';','".$filnn."'),'".$filnn."') WHERE pl.`EspecimenID` = tb.`".$tbprefix."EspecimenID` AND tb.`".$tbprefix."_CRMODISPECSIDS`=1";
		mysql_query($sql,$conn);
		//echo $sql;
	}

echo "
<br /><table cellpadding='5' class='myformtable' align='center'>
<thead>
 <tr><td >Atenção!</td></tr>
</thead>
<tbody>";
if ($arrayoffields['plins']>0) {
if ($bgi % 2 == 0){$bgcolor = $linecolor2 ;}  else{$bgcolor = $linecolor1 ;} $bgi++;
			echo "
  <tr bgcolor = '".$bgcolor."'><td  >Foram inseridos ".$arrayoffields['plins']." registros de plantas marcadas</td></tr>";
}
if ($arrayoffields['plup']>0) {
if ($bgi % 2 == 0){$bgcolor = $linecolor2 ;}  else{$bgcolor = $linecolor1 ;} $bgi++;
			echo "
  <tr bgcolor = '".$bgcolor."'><td  >Foram atualizados ".$arrayoffields['plup']." registros de plantas marcadas</td></tr>";
}
if ($arrayoffields['spins']>0) {
if ($bgi % 2 == 0){$bgcolor = $linecolor2 ;}  else{$bgcolor = $linecolor1 ;} $bgi++;
			echo "
  <tr bgcolor = '".$bgcolor."'><td  >Foram inseridos ".$arrayoffields['spins']." registros de coletas (especimenes)</td></tr>";
}
if ($arrayoffields['spup']>0) {
if ($bgi % 2 == 0){$bgcolor = $linecolor2 ;}  else{$bgcolor = $linecolor1 ;} $bgi++;
			echo "
  <tr bgcolor = '".$bgcolor."'><td  >Foram atualizados ".$arrayoffields['spup']." registros de coletas (especimenes)</td></tr>";
}
if ($arrayoffields['idsins']>0) {
if ($bgi % 2 == 0){$bgcolor = $linecolor2 ;}  else{$bgcolor = $linecolor1 ;} $bgi++;
			echo "
  <tr bgcolor = '".$bgcolor."'><td  >Foram inseridas ".$arrayoffields['idsins']." novas identificações</td></tr>";
  $qq = "UPDATE `Identidade` SET DetDate=CONCAT(DetDateYY,'-',DetDateMM,'-',DetDateDD) WHERE DetDate is null  AND DetDateYY>0 AND DetDateDD>0";
  mysql_query($qq,$conn);
}
if ($arrayoffields['idsnochange']>0) {
if ($bgi % 2 == 0){$bgcolor = $linecolor2 ;}  else{$bgcolor = $linecolor1 ;} $bgi++;
			echo "
  <tr bgcolor = '".$bgcolor."'><td  >Para ".$arrayoffields['idsnochange']." registros não houve mudança de identificação</td></tr>";
}
if ($arrayoffields['idsups']>0) {
if ($bgi % 2 == 0){$bgcolor = $linecolor2 ;}  else{$bgcolor = $linecolor1 ;} $bgi++;
			echo "
  <tr bgcolor = '".$bgcolor."'><td  >Foram atualizadas ".$arrayoffields['idsups']." novas identificações</td></tr>";
  $qq = "UPDATE `Identidade` SET DetDate=CONCAT(DetDateYY,'-',DetDateMM,'-',DetDateDD) WHERE DetDate is null  AND DetDateYY>0 AND DetDateDD>0";
  mysql_query($qq,$conn);

}
if ($arrayoffields['moni']>0) {
if ($bgi % 2 == 0){$bgcolor = $linecolor2 ;}  else{$bgcolor = $linecolor1 ;} $bgi++;
			echo "
  <tr bgcolor = '".$bgcolor."'><td  >Foram atualizadas/inseridas ".$arrayoffields['moni']." registros para variáveis de monitoramento</td></tr>";
}
if ($arrayoffields['esta']>0) {
if ($bgi % 2 == 0){$bgcolor = $linecolor2 ;}  else{$bgcolor = $linecolor1 ;} $bgi++;
			echo "
  <tr bgcolor = '".$bgcolor."'><td  >Foram atualizadas/inseridas ".$arrayoffields['esta']." registros para variáveis estáticas</td></tr>";
}
$nrecs = $arrayoffields['plins']+$arrayoffields['plup']+$arrayoffields['spins']+$arrayoffields['spup'];
if ($nrecs>0) {
	if ($bgi % 2 == 0){$bgcolor = $linecolor2 ;}  else{$bgcolor = $linecolor1 ;} $bgi++;
	echo "
  <tr bgcolor = '".$bgcolor."'><td  >Os registros atualizados/inseridos estão marcados como filtro <b>$filtronome</b></td></tr>";
}
if (count($arrayoffields['traitidsestatic'])>0) {
	$trs = array_unique($arrayoffields['traitidsestatic']);
	$ntr = count($trs);
if ($bgi % 2 == 0){$bgcolor = $linecolor2 ;}  else{$bgcolor = $linecolor1 ;} $bgi++;
	echo "
  <tr bgcolor = '".$bgcolor."'><td  >$ntr variáveis estáticas foram importadas e estão agrupadas no formulário <b>$fomrestatic</b></td></tr>";
}
if (count($arrayoffields['traitidsmoni'])>0) {
	$trs = array_unique($arrayoffields['traitidsmoni']);
	$ntr = count($trs);
if ($bgi % 2 == 0){$bgcolor = $linecolor2 ;}  else{$bgcolor = $linecolor1 ;} $bgi++;
	echo "
  <tr bgcolor = '".$bgcolor."'><td  >$ntr variáveis de monitoramento foram importadas e estão agrupadas no formulário <b>$formmonitor</b></td></tr>";
}

if ($bgi % 2 == 0){$bgcolor = $linecolor2 ;}  else{$bgcolor = $linecolor1 ;} $bgi++;
echo "
<tr bgcolor = '".$bgcolor."'>
  <td align='center'><input style=\"cursor:pointer;\" type='button' value=\"".GetLangVar('nameconcluir')."\" class='bsubmit'  onclick=\"javascript: window.close();\" /></td>
</tr>
</tbody>
</table>";
session_write_close();

//TaxonomySimple($all=true,$conn);
//TaxonomySimple($all=false,$conn);
//LocalitySimpleBrasil($gspoints=false,$conn);
//LocalitySimple($gspoints=false,$all=TRUE,$conn);
//LocalitySimple($gspoints=false,$all=FALSE,$conn);
}

}
$which_java = array(
"<script type='text/javascript' src='javascript/myjavascripts.js'></script>"
);
FazFooter($which_java,$calendar=FALSE,$footer=$menu);
?>
