<?php
set_time_limit(0);

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


HTMLheaders('');


//definicoes
$filename = $_SESSION['sessiondate']."_".$_SESSION['userid']."_".substr(session_id(),0,10).".pdf";
$temptable = "temp_Etiqueta_".$_SESSION['userid']."_".substr(session_id(),0,10);


if (!empty($filtro)) { 
	if (empty($etitype)) { $etitype='EspecimenesIDS';}
	$qzz = "SELECT * FROM Filtros WHERE FiltroID='".$filtro."'";
	$res = mysql_query($qzz,$conn);
	$rr = mysql_fetch_assoc($res);
	$ids_string= $rr[$etitype];
	if ($etitype =='EspecimenesIDS') { 
		$tbname = 'Especimenes';
		$tbname2 = 'Plantas';
	} else {
		$tbname = 'Plantas';
		$tbname2 = 'Especimenes';
	}
} elseif (!empty($especimenesids)) {
	$tbname = 'Especimenes';
}

if (isset($tbname) && !isset($prepared)) {
	unset($_SESSION['etiqueta_sql']);
	unset($_SESSION['exportnresult']);
	unset($_SESSION['qqcolumns']);

	$qd = "DROP TABLE IF EXISTS ".$temptable;
	mysql_query($qd,$conn);
	
	$qd = "SET lc_time_names = 'pt_BR'";
	mysql_query($qd,$conn);
	
	if ($etitype =='EspecimenesIDS') { 
	   	$qqcolumns = "(wikid, coletor, numcol, datacol, addcol, locality, herbnum, descricao, herbarios, tagnum, ndups";
    	
    	
		$qq .= " SELECT maintb.EspecimenID as wikid, colpessoa.Abreviacao as coletor, CONCAT(IF(maintb.Prefixo IS NULL OR maintb.Prefixo='','',CONCAT(maintb.Prefixo,'-')),maintb.Number,IF(maintb.Sufix IS NULL OR maintb.Sufix='','',CONCAT('-',maintb.Sufix))) as numcol, DATE_FORMAT(concat(IF(maintb.Ano>0,maintb.Ano,1),'-',IF(maintb.Mes>0,maintb.Mes,1),'-',IF(maintb.Day>0,maintb.Day,1)),'%d-%b-%Y') as datacol,
		addcolldescr(maintb.AddColIDS) as addcol";
		$qq .= ", IF (maintb.PlantaID>0,
		localidadestring(secondtb.GazetteerID,secondtb.GPSPointID,0,0,0,secondtb.Latitude,secondtb.Longitude,secondtb.Altitude),
		localidadestring(maintb.GazetteerID,maintb.GPSPointID,maintb.MunicipioID,maintb.ProvinceID,maintb.CountryID,maintb.Latitude,maintb.Longitude,maintb.Altitude)) as locality";
		$qq .= ", INPA_ID as herbnum";
		//if ($formid>0) {
			//$qq .= ", notastring(EspecimenID, $formid,TRUE,'Especimenes')  as descricao";
		$qq .= ", labeldescricao(IF(maintb.PlantaID>0,0,maintb.EspecimenID+0),maintb.PlantaID+0,".$formid.",TRUE,FALSE)  as descricao";
		//}
		$qq .= ", maintb.Herbaria as herbarios";
		$qq .= ", plantatag(maintb.PlantaID) as tagnum";
		if ($duplicatesTraitID>0) {
			$qq .= ", nduplicates(".$duplicatesTraitID.",EspecimenID,'Especimenes') as ndups";
		} else {
			if ($duplicatesTraitID2>0) {
				$qq .= ", ".$duplicatesTraitID2." as ndups";
			} else {
				$qq .= ", 1 as ndups";			
			}
		}
	
	} 
	else {
	 	$qqcolumns = "(wikid, tagnum, coletor, numcol, datacol, addcol, locality, herbnum, descricao, herbarios, ndups";
    	
		$qq .= " SELECT maintb.PlantaID as wikid, plantatag(maintb.PlantaID) as tagnum, '' as coletor, '' as numcol, IF(maintb.TaggedDate>0,DATE_FORMAT(maintb.TaggedDate,'%d-%b-%Y'),'') as datacol, addcolldescr(maintb.TaggedBy) as addcol";
		$qq .= ", localidadestring(maintb.GazetteerID,maintb.GPSPointID,0,0,0,maintb.Latitude,maintb.Longitude,maintb.Altitude) as locality";
		$qq .= ", '' as herbnum";
		if ($formid>0) {
			$qq .= ", notastring(PlantaID,$formid,TRUE,'Plantas') as descricao";
		}
		$qq .= ", '' as herbarios";
		if ($duplicatesTraitID>0) {
			$qq .= ", nduplicates(".$duplicatesTraitID.",PlantaID,'Plantas') as ndups";
		} else {
			if ($duplicatesTraitID2>0) {
				$qq .= ", ".$duplicatesTraitID2." as ndups";
			} else {
				$qq .= ", 1 as ndups";			
			}
		}
	}		
	$qqcolumns .= ", familia, detnome, detdetby";
	$qq .=", famtb.Familia as familia";
	$qq .=", IF(iddet.InfraEspecieID>0,CONCAT('<i>',gentb.Genero,' ',sptb.Especie,' </i> ',sptb.EspecieAutor,' <i>',infsptb.InfraEspecieNivel,' ',infsptb.InfraEspecie,'</i> ',infsptb.InfraEspecieAutor),IF(iddet.EspecieID>0,CONCAT('<i>',gentb.Genero,' ',sptb.Especie,'</i> ',sptb.EspecieAutor),'')) as detnome";
	$qq .= ", CONCAT(detpessoa.Abreviacao,' [',DATE_FORMAT(iddet.DetDate,'%d-%b-%Y'),']') as detdetby";
	if ($formidhabitat>0) {
		$qq .= ", IF (maintb.HabitatID>0,habitatstring(maintb.HabitatID, ".$formidhabitat.", TRUE,FALSE),habitatstring(secondtb.HabitatID, ".$formidhabitat.", TRUE,FALSE))  as habitat";
		$qqcolumns .= ", habitat";

	}
	$qqcolumns .= ", vernacular, projeto, logofile, prjurl, herbariosinpa)";
	
	$qq .=",  IF (maintb.VernacularIDS<>'',vernaculars(maintb.VernacularIDS),vernaculars(secondtb.VernacularIDS)) as vernacular";
	$qq .= ", IF (maintb.ProjetoID>0,projetostring(maintb.ProjetoID,TRUE,TRUE),projetostring(secondtb.ProjetoID,TRUE,1)) as projeto";
	$qq .=", IF (maintb.ProjetoID>0,projetologo(maintb.ProjetoID),projetologo(secondtb.ProjetoID)) as logofile";
	$qq .=", IF (maintb.ProjetoID>0,projetourl(maintb.ProjetoID),projetourl(secondtb.ProjetoID)) as prjurl";
	$qq .= ", '".GetLangVar('herbariocaps')."' as herbariosinpa";
	
	$qq .= " FROM ".$tbname."  as maintb LEFT JOIN ".$tbname2." as secondtb ON maintb.PlantaID=secondtb.PlantaID";
	
	if ($etitype =='EspecimenesIDS') { 
		$qu = "SELECT COUNT(*) nrecs FROM Especimenes WHERE FiltrosIDS LIKE '%filtroid_".$filtro.";%' OR FiltrosIDS LIKE '%filtroid_".$filtro."'";
	
		$qq .= " LEFT JOIN Pessoas as colpessoa ON maintb.ColetorID=colpessoa.PessoaID";
	} 
	else {
		$qu = "SELECT COUNT(*) nrecs FROM Plantas WHERE FiltrosIDS LIKE '%filtroid_".$filtro.";%' OR FiltrosIDS LIKE '%filtroid_".$filtro."'";
	}
	$qq .= " LEFT JOIN Identidade as iddet ON IF(maintb.DetID>0,maintb.DetID=iddet.DetID,secondtb.DetID=iddet.DetID)";
	
	$qq .= " LEFT JOIN Tax_InfraEspecies as infsptb ON iddet.InfraEspecieID=infsptb.InfraEspecieID LEFT JOIN Tax_Especies as sptb ON iddet.EspecieID=sptb.EspecieID LEFT JOIN Tax_Generos as gentb ON iddet.GeneroID=gentb.GeneroID  LEFT JOIN Tax_Familias as famtb ON iddet.FamiliaID=famtb.FamiliaID LEFT JOIN Pessoas as detpessoa ON detpessoa.PessoaID=iddet.DetbyID ";


	if ($filtro>0) {
		$qq .= " WHERE maintb.FiltrosIDS LIKE '%filtroid_".$filtro.";%' OR maintb.FiltrosIDS LIKE '%filtroid_".$filtro."'";
	} 
	elseif (!empty($especimenesids)) {
		$specarr = explode(";",$especimenesids);
		$n = 0;
		foreach ($specarr as $vv) {
			if ($n==0) {
				$qq .= " WHERE maintb.EspecimenID=".$vv;
			} else {
				$qq .= " OR maintb.EspecimenID=".$vv;
			}
			$n++;
		}

	}
	
	$ruw =  mysql_query($qu,$conn);
	$ruww = mysql_fetch_assoc($ruw);
	$nrr = $ruww['nrecs'];
	$_SESSION['exportnresult'] = $nrr;
	$stepsize = 100;
	$nsteps = ceil($nrr/$stepsize);
	$_SESSION['etiqueta_sql'] = $qq;
	$_SESSION['qqcolumns'] = $qqcolumns;
	
	$prepared=1;
	$step=0;
	
	
}
echo $step."aquiiii<br>".$nsteps."<br>";
echo $qq."<br>";
$prepared=0;
if ($prepared==1 && $step<=$nsteps) {
	$etiqueta_sql = $_SESSION['etiqueta_sql'];
	$qqcl = $_SESSION['qqcolumns'];
		if ($step==0) {
		$step=0;
		$st1 = 0;
		$qqq = "CREATE TABLE ".$temptable." (TempID INT(10) NOT NULL AUTO_INCREMENT, PRIMARY KEY (TempID)) ".$etiqueta_sql;		
	} else {
		$st1 = $st1+$stepsize+1;
		$qqq = "INSERT INTO ".$temptable." ".$qqcl." ".$etiqueta_sql;
	}
	$qqq .= " LIMIT $st1,$stepsize";
	
	$res = mysql_query($qqq,$conn);
	if ($res) {
echo "
<form action='label-exec.php' name='myform' method='post'>";
foreach ($ppost as $kk => $vv) {
	echo  "<input type='hidden' name='".$kk."' value='".$vv."'>";
}
echo "
  <input type='hidden' name='prepared' value='".$prepared."'>
  <input type='hidden' name='nsteps' value='".$nsteps."'>
  <input type='hidden' name='st1' value='".($st1-1)."'>
  <input type='hidden' name='step' value='".($step+1)."'>
  <input type='hidden' name='stepsize' value='".$stepsize."'>
<br>
<table align='center' cellpadding='5' width='50%' class='erro'>
  <tr><td>Processando passo ".($step+1)." de ".($nsteps+1)."  AGUARDE!</td></tr>
</table><script language=\"JavaScript\">setTimeout('document.myform.submit()',0.00001);</script></form>
";
	}
} // if is set prepared
elseif ($step>$nsteps) { 
	echo "
<form name='myform' action='label-pdf.php' method='post'>
  <input type='hidden' name='logofile' value='".$logofile."'>
  <input type='hidden' name='useprojectlog' value='".$useprojectlog."'>
  <input type='hidden' name='spec_label' value='".$spec_label."'>
  <input type='hidden' name='mini_label' value='".$mini_label."'>
  <input type='hidden' name='det_label' value='".$det_label."'>
<input type='submit'>

<script language=\"JavaScript\">setTimeout('document.myform.submit()',1);</script>
</form>
";

}


HTMLtrailers();
?>