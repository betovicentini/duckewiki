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
if((!isset($uuid) || 
	(trim($uuid)=='')) && count($listsarepublic)==0) {
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
$ppost = cleangetpost($_POST,$conn);
@extract($ppost);
$arval = $ppost;

if (!empty($_GET['detset'])) {
	$detset = $_GET['detset'];
	unset($_GET['detset']);
}
$gget = cleangetpost($_GET,$conn);
@extract($gget);

//echopre($ppost);


//CABECALHO
if ($ispopup==1) {
	$menu = FALSE;
} else {
	$menu = TRUE;
}
$menu = FALSE;

$which_css = array(
"<link href='css/geral.css' rel='stylesheet' type='text/css' />",
"<link rel='stylesheet' type='text/css' media='screen' href='css/autosuggest.css' >"
);
$which_java = array(
"<script type='text/javascript' src='javascript/ajax_framework.js'></script>"
);
//PARA IMAGE BYSPECIES
if (isset($getplspid) && !empty($getplspid)) {
	echopre($_GET);
	//GET PLANT AND SPECIES ID
	if ($getplspid!='again') {
echo 
"<form id='getplspidform' action='taxonomia-popup.php' method='post'>
  <input type='hidden' name='saveit' value='".$saveit."' />
  <input type='hidden' name='getplspid' value='again' />
  <input type='hidden' id='thisgetspecimenid' name='especimenid' value='".$especimenid."' />
  <input type='hidden' id='thisgetplantaid' name='plantaid' value='".$plantaid."' />
  <input type='hidden' name='detid' value='".$detid."' />
  <input type='hidden' name='dettextid' value='".$dettextid."' />
  <input type='hidden' name='detsetid' value='".$detsetid."' />
  <input type='hidden' name='ispopup' value='".$ispopup."' />
  <script language=\"JavaScript\">
  setTimeout(
    function() {
      var sourcefield = opener.document.getElementById('pl_".$getplspid."').value;
      var sourcefield2 = opener.document.getElementById('sp_".$getplspid."').value;
      document.getElementById('thisgetplantaid').value= sourcefield;
      document.getElementById('thisgetspecimenid').value= sourcefield2;
      document.getElementById('getplspidform').submit();
    }
    ,0.0001);
  </script>
</form>
";
	} 
	else {
		if ($especimenid>0) {
			$qu = "SELECT DetID FROM Especimenes WHERE EspecimenID='".$especimenid."'";
		}
		if ($plantaid>0) {
			$qu = "SELECT DetID FROM Plantas WHERE PlantaID='".$plantaid."'";
		}
		$rs = mysql_query($qu,$conn);
		$rw = mysql_fetch_assoc($rs);
		$detid = $rw['DetID'];
	}
}
$title = GetLangVar('nameidentificacao');
$body = '';
FazHeader($title,$body,$which_css,$which_java,$menu);


//SE SALVANDO
if ($final=='1') {
	//incomple sem os seguintes campos
	if (empty($nomesciid) || ($determinadorid+0)==0 || empty($datadet) ) {
		echo "
<br />
  <table cellpadding=\"1\" width='50%' align='center' class='erro'>
    <tr class='tdsmallbold' ><td align='center'>".GetLangVar('erro1')."</td></tr>";
			if ( empty($nomesciid) ) {
				echo "
    <tr class='tdsmallbold' ><td class='tdsmallnotes' align='center'>".GetLangVar('namenomecientifico')."</td></tr>";
			}
			if ( empty($datadet) ) {
				echo "
    <tr class='tdsmallbold' ><td class='tdsmallnotes' align='center'>Data de determinação</td></tr>";
			}
			if (($determinadorid+0)==0  ) {
				echo "
    <tr class='tdsmallbold' ><td class='tdsmallnotes' align='center'>Nome do determinador</td></tr>";
			}
			echo "
  </table>
<br />";
			$erro++;
	} 
	if ($erro==0) {
		list($famid,$genusid,$speciesid,$infraspid) = gettaxaids($nomesciid,$conn);
		$detarray = array(
				'FamiliaID' => $famid,
				'GeneroID' => $genusid,
				'EspecieID' => $speciesid,
				'InfraEspecieID' => $infraspid,
				'DetbyID' => $determinadorid,
				'DetDate' => $datadet,
				'DetConfidence' => $detconfidence, 
				'DetModifier' => $detmodifier , 
				'RefColetor' => $refcoletor , 
				'RefColnum' => $refcolnum ,
				'RefHerbarium' => $refherbarium , 
				'RefHerbNum' =>$refherbnum , 
				'RefDetby' =>$refdetby , 
				'RefDetDate' =>$refdatadet , 
				'DetNotes' =>$detnotes);

		$arvals = array_values($detarray);
		if ($saveit && ($especimenid>0 || $plantaid>0)) {
				//echo "ESTOU ENTRANDO AQUI 1";
				if (count($arvals)>0) {
					$arrayofvalues = $detarray;
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
						//echo "ESTOU ENTRANDO AQUI 2";
						$newdetid = InsertIntoTable($arrayofvalues,'DetID','Identidade',$conn);
						if (!$newdetid) {
							$er++;
						} else {
						//echo "ESTOU ENTRANDO AQUI 2A";
							$arrayofvv = array('DetID' => $newdetid);
							//SE FOR UMA AMOSTRA PEGA PLANTA PARA ATUALIZAR SE HOUVER
							if ($especimenid>0 && ($plantaid+0)==0) {
									$qs1 = "SELECT PlantaID FROM Especimenes WHERE EspecimenID=".$especimenid;
									$rs1 = mysql_query($qs1,$conn);
									$nrs1 = mysql_numrows($rs1);
									if ($nrs1>0) {
										$rsw = mysql_fetch_assoc($rs1);
										$plantaid = $rsw['PlantaID'];
									}
							}		
							//PEGA OUTRAS AMOSTRAS TAMBÉM SE HOUVER (TODAS ASSOCIADAS À PLANTA)
							if ($plantaid>0) {
									$qs2 = "SELECT GROUP_CONCAT(EspecimenID SEPARATOR ';') AS amostras FROM Especimenes WHERE PlantaID=".$plantaid;
									//echo $qs2."<br />";
									$rs2 = mysql_query($qs2,$conn);
									$nrs2 = mysql_numrows($rs2);
									if ($nrs2>0) {
										$rsw = mysql_fetch_assoc($rs2);
										if (!empty($rsw['amostras'])) {
											$especimenesids = explode(";",$rsw['amostras']);
										} else {
											$especimenesids = array();
										}
									} else {
										$especimenesids = array();
									}

								//ATUALIZA IDENTIFICACAO DA PLANTA
								CreateorUpdateTableofChanges($plantaid,'PlantaID','Plantas',$conn);
								$newupdate = UpdateTable($plantaid,$arrayofvv,'PlantaID','Plantas',$conn);
									if ($updatechecklist==1) {
									$qq = " DELETE FROM checklist_pllist WHERE PlantaID='".$plantaid."'";
									mysql_query($qq,$conn);
									$sql = "
INSERT INTO checklist_pllist (
SELECT  
pltb.PlantaID,  
pltb.DetID, 
'edit-icon.png' AS EDIT, 
plantatag(pltb.PlantaID) as TAGtxt, 
STRIP_NON_DIGIT(pltb.PlantaTag) as TAG, 
famtb.Familia as FAMILIA, 
acentosPorHTML(gettaxonname(pltb.DetID,1,0)) as NOME,
acentosPorHTML(gettaxonname(pltb.DetID,1,1)) as NOME_AUTOR,
detpessoa.Abreviacao as DETBY, 
IF(YEAR(iddet.DetDate)>0,YEAR(iddet.DetDate),IF(iddet.DetDateYY>0,iddet.DetDateYY,'')) as DETYY,
emorfotipo(pltb.DetID,0,0) as MORFOTIPO,
(IF(pltb.GPSPointID>0,countrygps.Country,IF(pltb.GazetteerID>0,countrygaz.Country,' '))) as PAIS, 
(IF(pltb.GPSPointID>0,provigps.Province,IF(pltb.GazetteerID>0,provgaz.Province,' '))) as ESTADO, 
(IF(pltb.GPSPointID>0,munigps.Municipio,IF(pltb.GazetteerID>0,muni.Municipio,' '))) as MUNICIPIO, 
(IF(pltb.GPSPointID>0,gazgps.PathName,IF(pltb.GazetteerID>0,gaz.PathName,' '))) as LOCAL,
(IF(pltb.GPSPointID>0,gazgps.Gazetteer,IF(pltb.GazetteerID>0,gaz.Gazetteer,' '))) as LOCALSIMPLES,
getlatlong(pltb.Latitude , pltb.Longitude, pltb.GPSPointID, pltb.GazetteerID, muni.MunicipioID, 0, 0, 0) as LONGITUDE,
getlatlong(pltb.Latitude , pltb.Longitude, pltb.GPSPointID, pltb.GazetteerID, muni.MunicipioID, 0, 0, 1) as LATITUDE,
IF(ABS(pltb.Longitude)>0,pltb.Altitude+0,IF(pltb.GPSPointID>0,gpspt.Altitude+0,IF(ABS(gaz.Longitude)>0,gaz.Altitude+0,NULL))) as ALTITUDE,
";
if ($daptraitid>0) {
	$sql .= "
(traitvalueplantas(".$daptraitid.", pltb.PlantaID, 'mm', 0, 1)+0) AS DAPmm,";
}
if ($alturatraitid>0) {
	$sql .= "
(traitvalueplantas(".$alturatraitid.", pltb.PlantaID, 'm', 0, 1))+0 AS ALTURA,";

}
if ($habitotraitid>0) {
	$sql .= "
(traitvalueplantas(".$habitotraitid.", pltb.PlantaID, '', 0, 0)) AS HABITO,";
}
if ($statustraitid>0) {
	$sql .= "
traitvalueplantas(".$statustraitid.",pltb.PlantaID, '', 0,0 ) AS STATUS,";
}
$sql .= " 'mapping.png' AS MAP,
IF((gaz.DimX+gaz.DimY)>0,pltb.GazetteerID,'') AS PLOT, checkplantaspecimens(pltb.PlantaID) AS ESPECIMENES, 
'' as OBS, 
IF(pltb.HabitatID>0,'environment_icon.png','') as HABT,
IF(projetologo(pltb.ProjetoID)<>'',projetologo(pltb.ProjetoID),'') as PRJ,
acentosPorHTML(IF(projetostring(pltb.ProjetoID,0,0)<>'',projetostring(pltb.ProjetoID,0,0),'NÃO FOI DEFINIDO')) AS PROJETOstr, 
IF (checkimgs(0, pltb.PlantaID)>0,'camera.png','') as IMG, 
traitvalueplantas(".$duplicatesTraitID.", pltb.PlantaID, '', 0, 0) as DUPS,
checknir(0,pltb.PlantaID) as NIRSpectra,";
//$sql .= "checkoleo(0,pltb.PlantaID,".$traitsilica.",'oleo') AS OLEO,
$sql .= "pltb.GazetteerID,
pltb.GPSPointID
FROM Plantas as pltb 
LEFT JOIN Identidade as iddet ON pltb.DetID=iddet.DetID 
LEFT JOIN Pessoas as detpessoa ON detpessoa.PessoaID=iddet.DetbyID 
LEFT JOIN Tax_InfraEspecies as infsptb ON iddet.InfraEspecieID=infsptb.InfraEspecieID  
LEFT JOIN Tax_Especies as sptb ON iddet.EspecieID=sptb.EspecieID  
LEFT JOIN Tax_Generos as gentb ON iddet.GeneroID=gentb.GeneroID   
LEFT JOIN Tax_Familias as famtb ON iddet.FamiliaID=famtb.FamiliaID  
LEFT JOIN Gazetteer as gaz ON gaz.GazetteerID=pltb.GazetteerID   
LEFT JOIN Municipio as muni ON gaz.MunicipioID=muni.MunicipioID   
LEFT JOIN Province as provgaz ON muni.ProvinceID=provgaz.ProvinceID   
LEFT JOIN Country  as countrygaz ON provgaz.CountryID=countrygaz.CountryID
LEFT JOIN GPS_DATA as gpspt ON gpspt.PointID=pltb.GPSPointID   
LEFT JOIN Gazetteer as gazgps ON gpspt.GazetteerID=gazgps.GazetteerID   
LEFT JOIN Municipio as munigps ON gazgps.MunicipioID=munigps.MunicipioID  
LEFT JOIN Province as provigps ON munigps.ProvinceID=provigps.ProvinceID   
LEFT JOIN Country  as countrygps ON provigps.CountryID=countrygps.CountryID WHERE pltb.PlantaID='".$plantaid."')";
									mysql_query($sql,$conn);
									//echo $sql."<br />";
								}
							} 
							else {
								if (($especimenid+0)>0) {
									$especimenesids = array($especimenid);
								} else {
									$especimenesids = array();
								}
							}
							$nspecs = count($especimenesids);
							if ($nspecs>0) {
								//echo "ESTOU ENTRANDO AQUI 2B";
								foreach($especimenesids as $especimenid) {
								//echo "<br />EspecimenID = ".$especimenid;
								CreateorUpdateTableofChanges($especimenid,'EspecimenID','Especimenes',$conn);
								$newupdate = UpdateTable($especimenid,$arrayofvv,'EspecimenID','Especimenes',$conn);
								if ($updatechecklist==1) {
									$qq = " DELETE FROM checklist_speclist WHERE EspecimenID='".$especimenid."'";
									mysql_query($qq,$conn);
									$sql = "INSERT INTO checklist_speclist (SELECT  
pltb.GazetteerID,
pltb.GPSPointID,
pltb.EspecimenID, 
pltb.PlantaID, 
thepl.PlantaTag,
pltb.DetID,
(colpessoa.Abreviacao) as COLETOR, 
pltb.Number as NUMERO,
if(CONCAT(pltb.Ano,'-',pltb.Mes,'-',pltb.Day)<>'0000-00-00',CONCAT(pltb.Ano,'-',pltb.Mes,'-',pltb.Day),'FALTA') as DATA,
if(pltb.INPA_ID>0,pltb.INPA_ID+0,NULL) as ".$herbariumsigla.",
pltb.Herbaria as HERBARIA,
famtb.Familia as FAMILIA,
acentosPorHTML(gettaxonname(pltb.DetID,1,0)) as NOME,
acentosPorHTML(gettaxonname(pltb.DetID,1,1)) as NOME_AUTOR,
detpessoa.Abreviacao as DETBY, 
IF(YEAR(iddet.DetDate)>0,YEAR(iddet.DetDate),IF(iddet.DetDateYY>0,iddet.DetDateYY,'')) as DETYY,
emorfotipo(pltb.DetID,0,0) as MORFOTIPO,
localidadefields(pltb.GazetteerID, pltb.GPSPointID, pltb.MunicipioID, pltb.ProvinceID, pltb.CountryID, 'COUNTRY') as PAIS,  
localidadefields(pltb.GazetteerID, pltb.GPSPointID, pltb.MunicipioID, pltb.ProvinceID, pltb.CountryID, 'MAJORAREA') as ESTADO,
localidadefields(pltb.GazetteerID, pltb.GPSPointID, pltb.MunicipioID, pltb.ProvinceID, pltb.CountryID, 'MINORAREA') as MUNICIPIO,
localidadefields(pltb.GazetteerID, pltb.GPSPointID, pltb.MunicipioID, pltb.ProvinceID, pltb.CountryID, 'GAZETTEER') as LOCAL,
localidadefields(pltb.GazetteerID, pltb.GPSPointID, pltb.MunicipioID, pltb.ProvinceID, pltb.CountryID, 'GAZETTEER_SPEC') as LOCALSIMPLES, 
getlatlong(pltb.Latitude , pltb.Longitude, pltb.GPSPointID, pltb.GazetteerID, 0, 0, 0, 0) as LONGITUDE,
getlatlong(pltb.Latitude , pltb.Longitude, pltb.GPSPointID, pltb.GazetteerID, 0, 0, 0, 1) as LATITUDE,
IF(ABS(pltb.Longitude)>0,pltb.Altitude+0,IF(pltb.GPSPointID>0,gpspt.Altitude+0,IF(ABS(gaz.Longitude)>0,gaz.Altitude+0,NULL))) as ALTITUDE,
'edit-icon.png' AS EDIT,
'mapping.png' AS MAP,";
//checkoleo(pltb.EspecimenID,pltb.PlantaID,".$traitsilica.",'oleo') AS OLEO,
$sql  .= "'' as OBS,
IF(pltb.HabitatID>0,'environment_icon.png','') as HABT,
habitaclasse(pltb.HabitatID) AS HABT_CLASSE,
if (checkimgs(pltb.EspecimenID, pltb.PlantaID)>0,'camera.png','') as IMG,
checknir(pltb.EspecimenID,pltb.PlantaID) as NIRSpectra";
if ($duplicatesTraitID>0) {
	$sql  .= "
, traitvaluespecs(".$duplicatesTraitID.", pltb.PlantaID, pltb.EspecimenID,'', 0, 0)+0 as DUPS";
}
if ($daptraitid>0) {
	$sql  .= "
, traitvaluespecs(".$daptraitid.", pltb.PlantaID, pltb.EspecimenID,'mm', 0, 1) as DAPmm";
}
if ($alturatraitid>0) {
	$sql  .= "
, traitvaluespecs(".$alturatraitid.", pltb.PlantaID, pltb.EspecimenID,'mm', 0, 1) as ALTURA";
}
if ($habitotraitid>0) {
	$sql  .= "
, traitvaluespecs(".$habitotraitid.", pltb.PlantaID, pltb.EspecimenID,'', 0, 1) as HABITO";
}
if ($traitfertid>0) {
	$sql  .= "
, traitvaluespecs(".$traitfertid.", 0, pltb.EspecimenID,'', 0, 1) as FERTILIDADE";
}
$sql  .= "
, IF(projetologo(pltb.ProjetoID)<>'',projetologo(pltb.ProjetoID),'') as PRJ,
(IF(projetostring(pltb.ProjetoID,0,0)<>'',projetostring(pltb.ProjetoID,0,0),'NÃO FOI DEFINIDO')) as PROJETOstr";
$sql .= " FROM Especimenes as pltb 
LEFT JOIN Plantas as thepl ON thepl.PlantaID=pltb.PlantaID
LEFT JOIN Pessoas as colpessoa ON pltb.ColetorID=colpessoa.PessoaID
LEFT JOIN Identidade as iddet ON pltb.DetID=iddet.DetID 
LEFT JOIN Pessoas as detpessoa ON detpessoa.PessoaID=iddet.DetbyID
LEFT JOIN Tax_InfraEspecies as infsptb ON iddet.InfraEspecieID=infsptb.InfraEspecieID 
LEFT JOIN Tax_Especies as sptb ON iddet.EspecieID=sptb.EspecieID 
LEFT JOIN Tax_Generos as gentb ON iddet.GeneroID=gentb.GeneroID  
LEFT JOIN Tax_Familias as famtb ON iddet.FamiliaID=famtb.FamiliaID 
LEFT JOIN Gazetteer as gaz ON gaz.GazetteerID=pltb.GazetteerID  
LEFT JOIN Municipio as muni ON gaz.MunicipioID=muni.MunicipioID  
LEFT JOIN Province as provgaz ON muni.ProvinceID=provgaz.ProvinceID  
LEFT JOIN Country  as countrygaz ON provgaz.CountryID=countrygaz.CountryID  
LEFT JOIN GPS_DATA as gpspt ON gpspt.PointID=pltb.GPSPointID  
LEFT JOIN Gazetteer as gazgps ON gpspt.GazetteerID=gazgps.GazetteerID  
LEFT JOIN Municipio as munigps ON gazgps.MunicipioID=munigps.MunicipioID 
LEFT JOIN Province as provigps ON munigps.ProvinceID=provigps.ProvinceID  
LEFT JOIN Country  as countrygps ON provigps.CountryID=countrygps.CountryID";
$sql .= " WHERE pltb.EspecimenID='".$especimenid."')";
			$upr = mysql_query($sql,$conn);
			//echo "<br />".$sql."<br />";
								}
								
								}
							}

							if (!$newupdate) {
								$er++;
							} 
							else {
								if ($updatechecklist!=1) {
								$detset =serialize($detarray);
								$nn = describetaxa($detset,$conn);
								echo "
<form name='myform' >
<input type='hidden' id='sendid' value='".$nn."' />
  <script language=\"JavaScript\">
  setTimeout(
    function() {
      var valor = document.getElementById('sendid').value;
      var element = window.opener.document.getElementById('".$dettextid."');
      element.innerHTML = valor;
      var destination = window.opener.document.getElementById('detid');
      destination.value = '".$newdetid."';
      //window.close();
    }
    ,0.0001);
  </script>
</form>";
								} 
								else {
									//echo "<br />ESTOU ENTRANDO AQUI 3A";

if (empty($reloadwin)) {
								echo "
<form name='myform' >
  <script language=\"JavaScript\">
  setTimeout(
    function() {
      //window.opener.location.reload(true);
      window.close();
    }
    ,0.0001);
  </script>
</form>";
} 
else {
	//echo "ESTOU ENTRANDO AQUI 3";
								echo "
<form name='myform' >
  <script language=\"JavaScript\">
  setTimeout(
    function() {
      window.close();
    }
    ,0.0001);
  </script>
</form>";
}
								}
							}
						}
					} 
			}
		} 
		else {
$detset =serialize($detarray);
$nn = describetaxa($detset,$conn);
//echo $detset;
echo "
<form name='myform' >
<input type='hidden' id='sendid' value='".$nn."' />
<input type='hidden' id='codigoid' value='".$detset."' />
<!---
  <script language=\"JavaScript\">
  setTimeout(
    function() {
var valor = document.getElementById('sendid').value;
var element = window.opener.document.getElementById('".$dettextid."');
element.innerHTML = valor;
var vvv = document.getElementById('codigoid').value;
var destination = window.opener.document.getElementById('".$detsetid."');
destination.value = vvv;
//window.close();
    }
    ,0.0001);
  </script>
--->   
<p align='center' class='success'>Terminado<br />
<input type='button' value='Fechar' 
onclick=\"javascript:sendval_innerHTML('sendid','".$dettextid."');
sendval_closewin('codigoid','".$detsetid."');\" /></p> 
</form>";
		}
	}
}

if ($final!='1' || ($final=='1' && $erro>0)) {

	if ($detid>0) {
		$detarr = getdetsetvar($detid,$conn);
		$famid = $detarr['FamiliaID' ];
		$genusid = $detarr[ 'GeneroID' ];
		$speciesid = $detarr['EspecieID' ];
		$infraspid = $detarr[ 'InfraEspecieID' ];
		$determinadorid = $detarr[ 'DetbyID'];
		$datadet = $detarr[ 'DetDate' ];
		$detconfidence = $detarr[ 'DetConfidence' ];
		$detmodifier = $detarr[ 'DetModifier'];
		$refcoletor = $detarr['RefColetor' ];
		$refcolnum = $detarr[ 'RefColnum' ];
		$refherbarium = $detarr[ 'RefHerbarium' ];
		$refherbnum = $detarr[ 'RefHerbNum' ];
		$refdetby = $detarr[ 'RefDetby'];
		$refdatadet = $detarr['RefDetDate' ];
		$detnotes = $detarr[ 'DetNotes' ];
		//$detset = serialize($detarr);
		$nomesci = strip_tags(gettaxaname($infraspid,$speciesid,$genusid,$famid,$conn));
	}
echo "
<table class='myformtable' align='center' border=0 cellpadding=\"3\" cellspacing=\"0\" >
<thead>
  <tr><td colspan='100%'>".GetLangVar('nameidentificacao')."</td></tr>
</thead>
<tbody>
<form name='finalform' action='taxonomia-popup.php' method='post'>
  <input type='hidden' name='final' value='1' />
  <input type='hidden' name='saveit' value='".$saveit."' />
  <input type='hidden' name='especimenid' value='".$especimenid."' />
  <input type='hidden' name='plantaid' value='".$plantaid."' />
  <input type='hidden' name='detid' value='".$detid."' />
  <input type='hidden' name='dettextid' value='".$dettextid."' />
  <input type='hidden' name='detsetid' value='".$detsetid."' />
  <input type='hidden' name='ispopup' value='".$ispopup."' />
  <input type='hidden' name='updatechecklist' value='".$updatechecklist."' />
  <input type='hidden' name='reloadwin' value='".$reloadwin."' />

  
";

if ($bgi % 2 == 0){$bgcolor = $linecolor2 ;} else {$bgcolor = $linecolor1 ;} $bgi++;
echo "
<tr bgcolor = '".$bgcolor."'>
  <td colspan='100%'>
    <table>
      <tr>
        <td class='tdsmallboldleft'>".GetLangVar('namenomecientifico')."</td>
        <td>"; autosuggestfieldval3('search-name-simple.php','nomesci',$nomesci,'nomeres','nomesciid',$nomesciid,true,60) ;
        
echo "</td>
        <td align='left'><img height='13' src=\"icons/icon_question.gif\" ";
		$help = GetLangVar('notaneedtoselect');
		echo " onclick=\"javascript:alert('$help');\" /></td>
      </tr>
    </table>
  </td>
</tr>";

if ($bgi % 2 == 0){$bgcolor = $linecolor2 ;} else {$bgcolor = $linecolor1 ;}
echo "
<tr bgcolor = '".$bgcolor."'>
  <td colspan='100%'>
    <table  align='left' cellpadding=\"3\" cellspacing=\"0\">
      <tr>
        <td class='tdsmallboldleft'>".GetLangVar('indicedecerteza')."&nbsp;<img height='13' src=\"icons/icon_question.gif\" ";
		$help = GetLangVar('indicedecerteza_help');
		echo " onclick=\"javascript:alert('$help');\" /></td>
        <td class='tdformnotes'>
          <select name='detconfidence'>
            <option value=''>".GetLangVar('nameselect')."</option>
            <option ";
			if ($detconfidence==1) {echo "selected";}
			echo " value='1'>1 - ".GetLangVar('certezaabsoluta')."</option>
            <option ";
			if ($detconfidence==2) {echo "selected";}
			echo " value='2'>2 - ".GetLangVar('namecerteza')."</option>
            <option ";
			if ($detconfidence==3) {echo "selected";}
			echo " value='3'>3 - ".GetLangVar('muitoparecida')."</option>
            <option ";
			if ($detconfidence==4) {echo "selected";}
			echo " value='4'>4 - ".GetLangVar('naoponhomaonofog')."</option>
            <option ";
			if ($detconfidence==5) {echo "selected";}
			echo " value='5'>5 - ".GetLangVar('tenhoduvida')."</option>
          </select>
        </td>
      </tr>
    </table>
  </td>
</tr>";

if ($bgi % 2 == 0){$bgcolor = $linecolor2 ;} else {$bgcolor = $linecolor1 ;}
echo "
<tr bgcolor = '".$bgcolor."'>
  <td colspan='100%'>
    <table  align='left' cellpadding=\"3\" cellspacing=\"0\">
      <tr>
        <td class='tdsmallboldleft' >".GetLangVar('messagenamemodifier')."</td>
        <td><input type='radio' ";
		if (substr($detmodifier,0,2)=='cf') { echo "checked";}
		echo " name='detmodifier' value='cf.' />cf.&nbsp;<img height='13' src=\"icons/icon_question.gif\" ";
		$help = GetLangVar('namecf');
		echo  " onclick=\"javascript:alert('$help');\" />&nbsp;&nbsp;</td>
        <td><input type='radio' ";
		if ($detmodifier=='aff.') { echo "checked";}
		echo " name='detmodifier' value='aff.' />aff.&nbsp;<img height='13' src=\"icons/icon_question.gif\" ";
		$help = GetLangVar('nameaff');
		echo " onclick=\"javascript:alert('$help');\" />&nbsp;&nbsp;</td>
        <td><input type='radio' ";
		if ($detmodifier=='s.s.') { echo "checked";}
		echo " name='detmodifier' value='s.s.' />s.s.&nbsp;<img height='13' src=\"icons/icon_question.gif\" ";
		$help = GetLangVar('namess');
		echo " onclick=\"javascript:alert('$help');\" />&nbsp;&nbsp;</td>
        <td><input type='radio' ";
		if ($detmodifier=='s.l.') { echo "checked";}
		echo " name='detmodifier' value='s.l.' />s.l.&nbsp;<img height='13' src=\"icons/icon_question.gif\" ";
		$help = GetLangVar('namesl');
		echo  " onclick=\"javascript:alert('$help');\" />&nbsp;&nbsp;</td>
        <td><input type='radio' ";
		if ($detmodifier=='vel aff.') { echo "checked";}
		echo " name='detmodifier' value='vel aff.' />vel aff.&nbsp;<img height='13' src=\"icons/icon_question.gif\" ";
		$help = GetLangVar('namevelaff');
		echo " onclick=\"javascript:alert('$help');\" />&nbsp;&nbsp;</td>
        <td><input type='radio' ";
		if (empty($detmodifier) || $detmodifier==' ') { echo "checked";}
		echo " name='detmodifier' value=' ' />none&nbsp;</td>
      </tr>
    </table>
  </td>
</tr>";

if ($bgi % 2 == 0){$bgcolor = $linecolor2 ;} else {$bgcolor = $linecolor1 ;}
echo "
<tr bgcolor = '".$bgcolor."'>
  <td colspan='100%'>
    <table  class='tablethinborder' align='center' width='98%' cellpadding=\"3\" cellspacing=\"0\">
      <tr><td colspan='100%' class='tabsubhead'>".GetLangVar('messageidbasedon')."&nbsp;<img height='13' src=\"icons/icon_question.gif\" ";
		$help = GetLangVar('messageidbasedon_help');
		echo " onclick=\"javascript:alert('$help');\" /></td></tr>
      <tr>
        <td class='small'>".GetLangVar('namecoletor')."</td>
        <td class='small'>
          <select  id='pessoaid' name='refcoletor' >";
				echo "
            <option value=''>".GetLangVar('nameselect')."</option>";
			if (!empty($refcoletor)) {
				$rr = getpessoa($refcoletor,$abb=true,$conn);
				$row = mysql_fetch_assoc($rr);
				echo "
            <option selected value=".$row['PessoaID'].">".$row['Abreviacao']." (".$row['Prenome'].")</option>";
			}
			$rrr = getpessoa('',$abb=true,$conn);
			while ($row = mysql_fetch_assoc($rrr)) {
				$rv = trim($row['Abreviacao']);
				if (!empty($rv)) {
					echo "
            <option value=".$row['PessoaID'].">".$row['Abreviacao']." (".$row['Prenome'].")</option>";
				}
			}
		echo "
          </select>
        </td>
        <td align=left><img src='icons/list-add.png' height=18 ";
		$myurl ="novapessoa-form-popup.php?pessoaid_val=pessoaid&secondid_val=refdetbyid_val";
		echo " onclick = \"javascript:small_window('$myurl',500,350,'Nova Pessoa');\"></td>
        <td>&nbsp;</td>
        <td class='small'>".GetLangVar('namenumber')."</td>
        <td class='small'><input type='text' name='refcolnum' value='$refcolnum' size='5' /></td>
        <td class='small'>".GetLangVar('nameherbarium')." ".GetLangVar('namesigla')."</td>
        <td class='small'><input type='text' name='refherbarium' value='$refherbarium' size='4' /></td>
        <td class='small'>".GetLangVar('nameherbarium')." ".GetLangVar('namenumber')."</td>
        <td class='small'><input type='text' name='refherbnum' value='$refherbnum' size='4' /></td>
      </tr>
      <tr>
        <td colspan='100%'>
          <table  align='left' cellpadding=\"3\" cellspacing=\"0\">
            <tr>
              <td class='small'>".GetLangVar('namedetby')."</td>
              <td class='small'>
                <select id='refdetbyid_val' name='refdetby' >";
				echo "
                  <option value=''>".GetLangVar('nameselect')."</option>";
			if (!empty($refdetby)) {
				$rr = getpessoa($refdetby,$abb=true,$conn);
				$row = mysql_fetch_assoc($rr);
				echo "
                  <option selected value=".$row['PessoaID'].">".$row['Abreviacao']." (".$row['Prenome'].")</option>";
			}
			$rrr = getpessoa('',$abb=true,$conn);
			while ($row = mysql_fetch_assoc($rrr)) {
				$rv = trim($row['Abreviacao']);
				if (!empty($rv)) {
					echo "
                  <option value=".$row['PessoaID'].">".$row['Abreviacao']." (".$row['Prenome'].")</option>";
				}
			}
		echo "
                </select>
              </td>
              <td class='small'>".GetLangVar('namedata')." Det.</td>
              <td class='small'>
                <table  align='left' cellpadding=\"3\" cellspacing=\"0\">
                  <tr>
                    <td class='small'> <input name=\"refdatadet\" value=\"$refdatadet\" size=\"7\" /></td>
                    <td class='small'><a onclick=\"if(self.gfPop)gfPop.fPopCalendar(document.forms['finalform'].refdatadet);return false;\" ><img name=\"popcal\" align=\"absmiddle\" src=\"calendar/calbtn.gif\" width=\"34\" height=\"22\" border=\"0\" alt=\"\"></a></td>
                  </tr>
                </table>
              </td>
            </tr>
          </table>
        </td>
      </tr>
    </table>
  </td>
</tr>";

if ($bgi % 2 == 0){$bgcolor = $linecolor2 ;} else {$bgcolor = $linecolor1 ;}
echo "
<tr bgcolor = '".$bgcolor."'>
  <td colspan='100%'>
    <table  align='left' cellpadding=\"3\" cellspacing=\"0\">
      <tr>
        <td class='tdsmallboldleft'>".GetLangVar('nameobs')."</td>
        <td><textarea name='detnotes' cols='80%' rows='2'>$detnotes</textarea></td>
      </tr>
    </table>
  </td>
</tr>";

if ($bgi % 2 == 0){$bgcolor = $linecolor2 ;} else {$bgcolor = $linecolor1 ;}
echo "
<tr bgcolor = '".$bgcolor."'>
  <td colspan='100%'>
    <table  align='left' border=0 cellpadding=\"3\" cellspacing=\"0\">
      <tr>
        <td class='tdsmallboldright'>".GetLangVar('namedeterminador')."</td>
        <td class='tdformnotes'>
          <select name='determinadorid'>";        
        	if ($determinadorid>0) {
        		$rr = getpessoa($determinadorid,$abb=true,$conn);
        		$row = mysql_fetch_assoc($rr);
        		echo "
            <option selected value=".$row['PessoaID'].">".$row['Abreviacao']." (".$row['Prenome'].")</option>";
        	} else {
        	echo "
            <option selected value=''>".GetLangVar('nameselect')."</option>";
        	}
        	$rrr = getpessoa('',$abb=true,$conn);
        	while ($row = mysql_fetch_assoc($rrr)) {
        		$rv = trim($row['Abreviacao']);
        		if (!empty($rv)) {
        			echo "
            <option value=".$row['PessoaID'].">".$row['Abreviacao']." (".$row['Prenome'].")</option>";
        		}
        	}
        echo "
          </select>
        </td>
        <td class='tdsmallboldright'>".GetLangVar('namedata')."</td>
        <td><input class=\"plain\" name=\"datadet\" value=\"$datadet\" size=\"11\"  readonly><a onclick=\"if(self.gfPop)gfPop.fPopCalendar(document.forms['finalform'].datadet);return false;\" ><img name=\"popcal\" align=\"absmiddle\" src=\"calendar/calbtn.gif\" width=\"34\" height=\"22\" border=\"0\" alt=\"\" /></a></td>
      </tr>
    </table>
  </td>
</tr>
";
if ($bgi % 2 == 0){$bgcolor = $linecolor2 ;} else {$bgcolor = $linecolor1 ;}
echo "
<tr bgcolor = '".$bgcolor."'><td colspan='100%' align='center'><input type='submit' value='".GetLangVar('nameenviar')."' class='bsubmit' /></td></tr>
</tbody>
</table>
</form>
";

}
$which_java = array("<script type='text/javascript' src='javascript/myjavascripts.js'></script>");
FazFooter($which_java,$calendar=TRUE,$footer=$menu);
?>