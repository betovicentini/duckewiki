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

//echopre($ppost);

//CABECALHO
$menu = FALSE;
$which_css = array(
"<link href='css/geral.css' rel='stylesheet' type='text/css' />",
"<link rel='stylesheet' type='text/css' media='screen' href='css/autosuggest.css' >"
);
$which_java = array(
"<script type='text/javascript' src='javascript/ajax_framework.js'></script>"
);
$title = 'Planta Marcada';
$body = '';
FazHeader($title,$body,$which_css,$which_java,$menu);

if (($plantaid+0)>0) {
	$submeteu=='editando';
} elseif (!$submeteu='nova') {
	echo "
<form >
  <script language=\"JavaScript\">
    window.close();
  </script>
</form>";
}

$qq = "ALTER TABLE `Plantas` CHANGE `LADO` `LADO` CHAR(100) CHARACTER SET utf8 COLLATE utf8_general_ci NULL DEFAULT NULL";
@mysql_query($qq,$conn);

//coordenadas
if (!isset($coord)) {
	$coord = @coordinates('','',$latgrad,$longgrad,$latminu,$longminu,$latsec,$longsec,$latnors,$longwore);
	@extract($coord);
}

/////////////////////////////////
/////////////////////////////////
//////inicio do cadastro/////////
/////////////////////////////////
/////////////////////////////////
$erro =0;
if (($final>0 && $nosample=='no') || ($final>0 && !empty($especimensids))) {
	//checa se ja existe uma arvore com esse numero para essa localidade
	if (!isset($plantaid) || empty($plantaid)) {
		$qq = "SELECT * FROM Plantas WHERE PlantaTag='".$plantnum."'";
		if (!empty($gazetteerid) && empty($gpspointid)) { 
			$qq= $qq." AND GazetteerID='".$gaztteerid."'";
		} elseif (!empty($gpspointid)) {
			$qq= $qq." AND GPSPointID='".$gpspointid."'";
		}
		$res = mysql_query($qq,$conn);
		$nres = @mysql_numrows($res);
		if ($nres>0) {
			echo "
<br />
<table cellpadding=\"1\" width='50%' align='center' class='erro'>
  <tr><td class='tdsmallbold' align='center'>".GetLangVar('erro3')."</td></tr>
</table>
<br />
";
			$erro++;
		}
	}

	//prioriza a coordenada do ponto de GPS se também foi informada a localidade
	if ($gpspointid>0 && $gazetteerid>0 ) { 
		$gazetteerid=0;
	} elseif ($gazetteerid>0) {
		$sql = "SELECT IF((DimX+DimY+DimDiameter)>0,TRUE,FALSE) as parcela FROM Gazetteer WHERE GazetteerID=".$gazetteerid;
		$sqr = mysql_query($sql,$conn);
		$sqw = mysql_fetch_assoc($sqr);
		$parcela = $sqw['parcela'];
	}

	//faz o mesmo para procedência se for o caso
	if ($procedenciagps>0 && $procedenciaid>0 ) { $procedenciaid=0;}

	//soma valores de localidade, que deve ser obrigatória
	$ggpt = ($gpspointid+0)+($gazetteerid+0);
	$obrll = (abs($longdec+0))+(abs($latdec+0));
	$obrxy = ($plpos_x+0)+($plpos_y+0);
	$obrda = ($plpos_dist+0)+($plpos_angle+0);

	//checa por erros ou campos obrigatórios
	$missdata = 0;
	$missarray = array();

	//se tiver coordenadas X e Y e a localidade nao for uma parcela
	if ($obrxy>0 && $parcela!=1) {
		$missdata++;
		$missarray[] = "Tem coordenada X e Y, mas a localidade não é uma parcela, i.e. ela não tem dimensão X ou Y ou R (editar a localidade e corrigir antes de processeguir)";
	} 
	if ($obrda>0 && $ggpt==0)  {
		$missdata++;
		$missarray[] = "Tem coordenada distância e ângulo, mas esta faltando o ponto de GPS ou a localidade.";
	} 
	if ($obrda>0 && $ggpt==0)  {
		$missdata++;
		$missarray[] = "Tem coordenada distância e ângulo, mas esta faltando o ponto de GPS ou a localidade.";
	} 
	if (($ggpt+$obrll)==0) {
		$missdata++;
		$missarray[] = "Alguma informação de localidade é requisito para cadastrar uma árvore";
	}
	if (empty($plantnum)) {
		$missdata++;
		$missarray[] = "Número da planta é obrigatório!";
	}
	//if (empty($$datacol)) {
		//$missdata++;
		//$missarray[] = "Data de marcação é obrigatória!";
	//}
	//checa se coordenadas estao ok
	if (ABS($latdec+0)>0 && ABS($longdec+0)>0 && (abs($latdec)>90 || abs($longdec)>180)) {
		$missdata++;
		$missarray[] = GetLangVar('namelatitude')." > 90, ou ".GetLangVar('namelongitude')." > 180";
	}
	if ($missdata>0) {
	echo "
<br />
  <table cellpadding=\"6\" align='center' class='erro'>";
  		foreach($missarray as $vv) {
		echo "
    <tr><td>".$vv."</td></tr>";
  		}
echo "
  </table>
<br />";
		$erro++;
	} 
	//checa se houve mudança em TRAITS associados a planta em edição se estiver editando
	$changedtraits=0;
	if (!empty($_SESSION['variation']) && $_SESSION['editando']=='edit_'.$plantaid) {
			$tempids = '';
			$oldtraitids = storeoriginaldatatopost($plantaid,'PlantaID',$formid,$conn,$tempids);
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
	}//se empty $traitids


	//se nao ha erro faz o cadastro dos dados na tabela Plantas
	$updated =0;
	if ($erro==0) {
		$arrayofvalues = array(
			'PlantaTag' => $plantnum,
			'VernacularIDS' => $vernacularvalue,
			'Latitude' => $latdec,
			'Longitude' => $longdec,
			'Altitude' => $altitude,
			'GazetteerID' => $gazetteerid,
			'GPSPointID' => $gpspointid,
			'ProcedenciaID' => $procedenciaid,
			'ProcGPSID' => $procedenciagps,
			'InSituExSitu' => $inexsitu,
			'HabitatID' => $habitatid,
			'NoSample' => $nosample,
			'TaggedBy' => $addcolvalue,
			'TaggedDate' => $datacol,
			'X' => $plpos_x,
			'Y' => $plpos_y,
			'LADO' => $xyref,
			'Distancia' => $plpos_dist,
			'Angulo' => $plpos_angle
			);
		//se nao editando insere valores novos
		if (empty($plantaid) && empty($_SESSION['editando'])) { 
				$newspec = InsertIntoTable($arrayofvalues,'PlantaID','Plantas',$conn);
				if (!$newspec) {
					echo "
<br />
<table cellpadding=\"1\" width='50%' align='center' class='erro'>
  <tr><td class='tdsmallbold' align='center'>".GetLangVar('erro2')." 1</td></tr>
</table>
<br />
";
					$erro++;
				} 
			} 
			//caso contrario faz um update dos valores
			else { 
				//compara com os valores salvos
				$upp = CompareOldWithNewValues('Plantas','PlantaID',$plantaid,$arrayofvalues,$conn);
				//se houver diferenca atualiza
				if (!empty($upp) && $upp>0) { 
					CreateorUpdateTableofChanges($plantaid,'PlantaID','Plantas',$conn);
					$updatespecid = UpdateTable($plantaid,$arrayofvalues,'PlantaID','Plantas',$conn);
					if (!$updatespecid) {
						$erro++;
					} else {
						$updated++;
					}
				} 
			}

	}

	$er=0;
	//se nao houve erro faz o cadastro da determinação dessa planta
	$newdetid =0;
	if ($erro==0) { 
		$detchanged = 0;
		//seleciona a identidade antiga e indica o que deve ser feito
		if ($_SESSION['editando']=='edit_'.$plantaid) { 
			$qq = "SELECT DetID FROM Plantas WHERE PlantaID=".$plantaid;
			$res = mysql_query($qq,$conn);
			$row = mysql_fetch_assoc($res);
			$olddetid = $row['DetID'];
			if (!empty($detset)) {
				$arrayofvalues = unserialize($detset);
				$detchanged = CompareOldWithNewValues('Identidade','DetID',$olddetid,$arrayofvalues,$conn);
			}
		}
		//se mudou ou se e nova, insere nova determinacao
		if (($detchanged>0 || $_SESSION['editando']!='edit_'.$plantaid) && !empty($detset)) {
			$arrayofvalues = unserialize($detset);
			if (count($arrayofvalues)>0 && ($arrayofvalues['FamiliaID']+0)>0) {
				$newdetid = InsertIntoTable($arrayofvalues,'DetID','Identidade',$conn);
				if (!$newdetid) {
					$er++;
					echo "
<br />
<table cellpadding=\"1\" width='50%' align='center' class='erro'>
  <tr><td class='tdsmallbold' align='center'>Não foi possível cadastrar a identificação!</td></tr>
</table>
<br />";
				}
			} 
		} 
	} 

	//se estiver editando e ainda nao criou um log do registro antigo, cria o log
	if ($plantaid>0 && $newdetid>0 && $updated==0) {
		CreateorUpdateTableofChanges($plantaid,'PlantaID','Plantas',$conn);
	} elseif (($plantaid+0)==0) {
		$plantaid=$newspec;
	}

	//atualiza a identificação se for o caso
	if ($newdetid>0 && $er==0) { 
		$arrayofvalues = array('DetID' => $newdetid);
		$newupdate = UpdateTable($plantaid,$arrayofvalues,'PlantaID','Plantas',$conn);
		if (!$newupdate) {
			$erro++;
			echo "
<br />
<table cellpadding=\"1\" width='50%' align='center' class='erro'>
  <tr><td class='tdsmallbold' align='center'>".GetLangVar('erro2')."</td></tr>
</table>
<br />";
		} else {
			$updated++;
		}
	}

	//faz o cadastro de variáveis associadas à planta se for o caso
	if ((($_SESSION['editando']=='edit_'.$plantaid && $changedtraits>0 && !empty($_SESSION['variation'])) || 
   		(empty($_SESSION['editando']) && !empty($_SESSION['variation']))) && $erro==0) {
   		$traitarray = unserialize($_SESSION['variation']);
   		if (count($traitarray)>0) {
			$resultado = updatetraits($traitarray,$plantaid,'PlantaID',$bibtex_id,$conn);
			if (!$resultado) {
				$erro++;
				echo "
<br />
<table cellpadding=\"1\" width='50%' align='center' class='erro'>
  <tr><td class='tdsmallbold' align='center'>".GetLangVar('erro2')." 5</td></tr>
</table>
<br />";
			} else {
				$updated++;
			}
		}
	}//se empty $traitids

	//se tiver especimenes associados a essa planta cria o link
	$ppspecid=0;
	if (!empty($especimensids)) {
		$ids = explode(";",$especimensids);
		foreach($ids as $specid) {
				//compara com os valores salvos
				$arrayofvalues = array('PlantaID' => $plantaid);
				$sqq = "SELECT PlantaID FROM Especimenes WHERE EspecimenID=".$specid;
				$rsq = mysql_query($sqq,$conn);
				$rwq = mysql_fetch_assoc($rsq);
				$oldpltid = $rwq['PlantaID']+0;
				if ($oldpltid==0) {
					CreateorUpdateTableofChanges($specid,'EspecimenID','Especimenes',$conn);
					$tt = UpdateTable($specid,$arrayofvalues,'EspecimenID','Especimenes',$conn);
					if ($tt) {
						$updated++;
					}
				} elseif ($oldpltid>0 && $oldpltid!=$plantaid) {
					$ppspecid++;
				}
		}
		//se tem amostras que ja nao estao mais relacionadas, então atualiza o link
		$oldids = explode(";",$_SESSION['oldspecids']);
		$diffids = array_diff($oldids,$ids);
		foreach($diffids as $specid) {
				//compara com os valores salvos
				$arrayofvalues = array('PlantaID' => 0);
				CreateorUpdateTableofChanges($specid,'EspecimenID','Especimenes',$conn);
				$tt = UpdateTable($specid,$arrayofvalues,'EspecimenID','Especimenes',$conn);
				if ($tt) {
					$updated++;
				}
		}
	}
	if ($ppspecid>0) {
		echo "
<br />
<table cellpadding=\"1\" width='50%' align='center' class='erro'>
  <tr><td class='tdsmallbold' align='center'><b>$ppspecid</b> amostras indicadas para esta planta já estavam associadas a outra planta. Portanto, o link não foi feito.</td></tr>
</table>
<br />";
	}
	if ($erro==0) {
		if ($_SESSION['editando']== 'edit_'.$plantaid && $updated==0 && $detchange==0 && $final==1) {
				echo "
<br />
<table cellpadding=\"1\" width='50%' align='center' class='erro'>
  <tr><td class='tdsmallbold' align='center'>".GetLangVar('messagenochange')."</td></tr>
</table>
<br />";
		} elseif ((empty($_SESSION['editando']) || $updated>0 || $detchange>0) && $final==1) {

//ATUALIZA A TABELA CHECKLIST SPECIMENS
if (!empty($_SESSION['editando'])) {
	$qq = " DELETE FROM checklist_pllist WHERE PlantaID='".$plantaid."'";
	mysql_query($qq,$conn);
} 
$sql = "INSERT INTO checklist_pllist (
SELECT  pltb.PlantaID,  
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
acentosPorHTML(IF(pltb.GPSPointID>0,countrygps.Country,IF(pltb.GazetteerID>0,countrygaz.Country,' '))) as PAIS,  
acentosPorHTML(IF(pltb.GPSPointID>0,provigps.Province,IF(pltb.GazetteerID>0,provgaz.Province,' '))) as ESTADO,  
acentosPorHTML(IF(pltb.GPSPointID>0,munigps.Municipio,IF(pltb.GazetteerID>0,muni.Municipio,' '))) as MUNICIPIO,  
acentosPorHTML(IF(pltb.GPSPointID>0,gazgps.PathName,IF(pltb.GazetteerID>0,gaz.PathName,' '))) as LOCAL, 
acentosPorHTML(IF(pltb.GPSPointID>0,gazgps.Gazetteer,IF(pltb.GazetteerID>0,gaz.Gazetteer,' '))) as LOCALSIMPLES, 
getlatlong(pltb.Latitude , pltb.Longitude, pltb.GPSPointID, pltb.GazetteerID, muni.MunicipioID, 0, 0, 0) as LONGITUDE,
getlatlong(pltb.Latitude , pltb.Longitude, pltb.GPSPointID, pltb.GazetteerID, muni.MunicipioID, 0, 0, 1) as LATITUDE,
IF(ABS(pltb.Longitude)>0,pltb.Altitude+0,IF(pltb.GPSPointID>0,gpspt.Altitude+0,IF(ABS(gaz.Longitude)>0,gaz.Altitude+0,NULL))) as ALTITUDE,";
if ($daptraitid>0) { $sql .= " (traitvalueplantas(".$daptraitid.", pltb.PlantaID, 'mm', 0, 1)+0) AS DAPmm,"; }
if ($alturatraitid>0) { $sql .= " (traitvalueplantas(".$alturatraitid.", pltb.PlantaID, 'm', 0, 1))+0 AS ALTURA,"; }
if ($habitotraitid>0) { $sql .= " acentosPorHTML(traitvalueplantas(".$habitotraitid.", pltb.PlantaID, '', 0, 0)) AS HABITO,";}
if ($statustraitid>0) {
	$sql .= "
traitvalueplantas(".$statustraitid.",pltb.PlantaID, '', 0,0 ) AS STATUS,";
}
$sql .= "
'mapping.png' AS MAP, 
IF((gaz.DimX+gaz.DimY)>0,pltb.GazetteerID,'') AS PLOT, 
checkplantaspecimens(pltb.PlantaID) AS ESPECIMENES, '' as OBS, 
IF(pltb.HabitatID>0,'environment_icon.png','') as HABT, 
IF(projetologo(pltb.ProjetoID)<>'',projetologo(pltb.ProjetoID),'') as PRJ, 
acentosPorHTML(IF(projetostring(pltb.ProjetoID,0,0)<>'',projetostring(pltb.ProjetoID,0,0),'NÃO FOI DEFINIDO')) AS PROJETOstr, 
if (checkimgs(0, pltb.PlantaID)>0,'camera.png','') as IMG, traitvalueplantas(".$duplicatesTraitID.", pltb.PlantaID, '', 0, 0) as DUPS,
checknir(0,pltb.PlantaID) as NIRSpectra,";
//$sql .= "checkoleo(0,pltb.PlantaID,".$traitsilica.",'oleo') AS OLEO,
$sql .= "pltb.GazetteerID,
pltb.GPSPointID 
FROM Plantas as pltb LEFT JOIN Identidade as iddet ON pltb.DetID=iddet.DetID  LEFT JOIN Pessoas as detpessoa ON detpessoa.PessoaID=iddet.DetbyID LEFT JOIN Tax_InfraEspecies as infsptb ON iddet.InfraEspecieID=infsptb.InfraEspecieID  LEFT JOIN Tax_Especies as sptb ON iddet.EspecieID=sptb.EspecieID  LEFT JOIN Tax_Generos as gentb ON iddet.GeneroID=gentb.GeneroID   LEFT JOIN Tax_Familias as famtb ON iddet.FamiliaID=famtb.FamiliaID  LEFT JOIN Gazetteer as gaz ON gaz.GazetteerID=pltb.GazetteerID   LEFT JOIN Municipio as muni ON gaz.MunicipioID=muni.MunicipioID   LEFT JOIN Province as provgaz ON muni.ProvinceID=provgaz.ProvinceID   LEFT JOIN Country  as countrygaz ON provgaz.CountryID=countrygaz.CountryID   LEFT JOIN GPS_DATA as gpspt ON gpspt.PointID=pltb.GPSPointID   LEFT JOIN Gazetteer as gazgps ON gpspt.GazetteerID=gazgps.GazetteerID   LEFT JOIN Municipio as munigps ON gazgps.MunicipioID=munigps.MunicipioID  LEFT JOIN Province as provigps ON munigps.ProvinceID=provigps.ProvinceID   LEFT JOIN Country  as countrygps ON provigps.CountryID=countrygps.CountryID WHERE pltb.PlantaID='".$plantaid."')";

//echo $sql."<br />";
mysql_query($sql,$conn);
	echo "
<br />
<table cellpadding=\"5\" align='center' class='success'>
  <tr><td class='tdsmallbold' align='center'>".GetLangVar('sucesso1')."</td></tr>";
  if ($ispopup==1) {
	echo  "
  <tr><td class='tdsmallbold' align='center'>
<form>
<input  style='cursor:pointer' type='button' value='Concluir' onclick=\"javascript:window.close();\" />
</form>
</td></tr>";
  } 
////////ACRESCENTAR OPCOES ///
	$delvals = array(
'plantaid',
'plantnum',
'dettext',
'detset',
'traitids',
'arrayofvars',
'cooraction',
'localaction',
'habaction',
'detaction',
'especimensids',
'especimenstxt',
'plantnum',
'final'
);

	unset($detset);
	foreach ($delvals as $vv) {
		unset($ppost[$vv]);
	}
	unset($_SESSION['variation']);
	unset($_SESSION['editando']);
	unset($_SESSION['oldspecids']);
echo "
  <tr><td class='tdsmallbold' align='center' >
<form name='coletaform' action='plantas_dataform.php' method='post'>
";
//<input type='hidden' value='no' name='nosample' />
$tokeep = array("addcoltxt","addcolvalue","datacol","gazetteerid","locality","gpspointid","gpspt","habitatid","nosample");
foreach ($ppost as $kk => $vv) {
	if (in_array($kk,$tokeep)) {
		echo "<input type='hidden' value='".$vv."' name='".$kk."' />";
	}
}
echo  "<input type='hidden' value='".$plantnum." foi o número cadastrado anterior' name='fromprevious' />";
if (($plantnum+1)>0) {
	echo  "<input type='hidden' value='".($plantnum+1)."' name='plantnum' />";
}
echo "<input type='submit' class='bsumit' value='Adicionar outra planta do mesmo local' />
</form>  
</td></tr>"; 
echo "
</table>
<br />
";
		}
	} 

//limpa variaveis da memoria
if ($final==2 && $erro==0) {
	unset($altitude,   $altmax,  $altmin,  $latgrad,  $latminu,  $latnors,  $latsec,  $longgrad,  $longminu,  $longsec,  $longwore,  $plantaid, $plantnum, $dettext, $detset, $traitids,  $arrayofvars,  $cooraction,  $localaction,  $habaction,  $detaction,  $especimensids,  $especimenstxt,  $plantnum);
	unset($_SESSION['variation']);
	unset($_SESSION['editando']);
	unset($_SESSION['oldspecids']);
	$submeteu='nova';
} elseif ($erro==0) {
	unset($detdoubt, $nosample, $addcoltxt, $gpspointid, $addcolvalue,  $altitude,  $altmax,  $altmin,  $colnum,  $datacol,  $datadet,  $determinadorid,  $famid,  $gazetteerid,  $genusid,  $habitatid,  $infraspid,  $latgrad,  $latminu,  $latnors,  $latsec,  $longgrad,  $longminu,  $longsec,  $longwore,  $plantaid,  $speciesid,  $traitids,  $arrayofvars,  $cooraction,  $localaction,  $habaction,  $detaction,  $especimensids,  $especimenstxt,  $plantnum,  $inexsitu);
	unset($_SESSION['variation']);
	unset($_SESSION['editando']);
	unset($_SESSION['oldspecids']);
}

} 
/////////////////////////////////////////
/////////////////////////////////////////
///////////termina o cadastro////////////
/////////////////////////////////////////
/////////////////////////////////////////

///////////Começa o formulário////////////
if (!isset($final) || $final==2 || $erro>0) {
	if ($submeteu=='nova' || $final==2) {
		unset($_SESSION['variation']);
		unset($_SESSION['editando']);
		unset($_SESSION['oldspecids']);
	}
//se for edicao extrair info antiga
//se estiver editando um registro
if ($submeteu=='editando' && $erro==0) {
	unset($_SESSION['oldspecids']);
	unset($_SESSION['variation']);
	$_SESSION['editando'] = 'edit_'.$plantaid;
	//if (!empty($plantaid)) {
	$qq = "SELECT * FROM Plantas WHERE PlantaID='".$plantaid."'";
	$res = mysql_query($qq,$conn);
	$row = mysql_fetch_assoc($res);

	//echopre($row);
	//determinacao antiga
	$detid = $row['DetID']+0;
	$detset = getdetsetvar($detid,$conn);
	$detset = serialize($detset);
	$xyref = $row['LADO'];
	$plpos_dist = $row['Distancia']+0;
	$plpos_angle = $row['Angulo']+0;
	$plpos_x = $row['X']+0;
	$plpos_y = $row['Y']+0;

	$dettaxa = getdet($detid,$conn);
	$detnome = $dettaxa[0];
	$detdetby = trim($dettaxa[1]);
	$familia = strtoupper(trim($dettaxa[2]));
	
	$nosample = $row['NoSample'];
    $vernacularvalue = $row['VernacularIDS'];

	$plantnum = $row['PlantaTag'];
	$latdec = $row['Latitude'];
	$longdec = $row['Longitude'];
	$coord = @coordinates($latdec,$longdec,'','','','','','','','');
	@extract($coord);
	$altitude = $row['Altitude'];

	$procedenciaid = $row['ProcedenciaID'];
	$procedenciagps = $row['ProcGPSID'];

/// jb manaus
	$inexsitu = $row['InSituExSitu'];

///
	$qq = "SELECT EspecimenID FROM Especimenes WHERE PlantaID='".$plantaid."'";
	$rss = mysql_query($qq,$conn);
	while ($rww = @mysql_fetch_assoc($rss)) {
			if (empty($especimensids)) { $especimensids = $rww['EspecimenID'];} else {
			$especimensids = $especimensids.";".$rww['EspecimenID'];}
	}
	if (empty($especimensids)) {$nosample='no';} else {
		$_SESSION['oldspecids']=$especimensids;
	}

	$gazetteerid = $row['GazetteerID'];
	$gpspointid = $row['GPSPointID'];
	$habitatid = $row['HabitatID'];

	//echopre($row);
	$tempids ='';
	$oldvals = storeoriginaldatatopost($plantaid,'PlantaID',$formid,$conn,$tempids);
	$traitarray = $oldvals;
	$datacol = $row['TaggedDate'];
	$addcolvalue = $row['TaggedBy'];

	$qu = "SELECT monitoramentostring(".$plantaid.",0,1,1) as monidesc";
	//echo $qu."<br />";
	$rs = @mysql_query($qu,$conn);
	if ($rs) {
		$rw = @mysql_fetch_assoc($rs);
		$monidesc = $rw['monidesc'];
	}
	$_SESSION['variation'] = serialize($oldvals);
}
$dettext = describetaxa($detset,$conn);
if ((empty($addcoltxt) || !isset($addcoltxt)) && !empty($addcolvalue)) {
	$addcolarr = explode(";",$addcolvalue);
	$addcoltxt = '';
	$j=1;
	foreach ($addcolarr as $kk => $val) {
		$qq = "SELECT * FROM Pessoas WHERE PessoaID='".$val."'";
		$res = mysql_query($qq,$conn);
		$rwp = mysql_fetch_assoc($res);
		if ($j==1) {
			$addcoltxt = 	$rwp['Abreviacao'];
		} else {
			$addcoltxt = $addcoltxt."; ".$rwp['Abreviacao'];
		}
		$j++;
	}
}
if ((empty($vernaculartxt) || !isset($vernaculartxt)) && !empty($vernacularvalue))  {
	$vernarr = explode(";",$vernacularvalue);
	$vernaculartxt = '';
	$j=1;
	foreach ($vernarr as $kk => $val) {
			$qq = "SELECT * FROM Vernacular WHERE VernacularID='$val'";
			$res = mysql_query($qq,$conn);
			$rrw = mysql_fetch_assoc($res);
			if ($j==1) {
				$vernaculartxt = 	$rrw['Vernacular'];
				if (!empty($rrw['Language'])) { $vernaculartxt=$vernaculartxt." (".$rrw['Language'].")";}
			} else {
				if (!empty($rrw['Language'])) { $vtxt= $rrw['Vernacular']." (".$rrw['Language'].")";} else {$vtxt=$rrw['Vernacular'];}
				$vernaculartxt = $vernaculartxt."; ".$vtxt;
			}
			$j++;
	}
}
if ((($gpspointid+0)>0 || ($gazetteerid+0)>0)) {
	$qq = "SELECT localidadestring(".($gazetteerid+0).",".($gpspointid+0).",0,0,0,".($latdec+0).", ".($longdec+0).", ".($altitude+0).") as locality";
	//echo $qq."<br />";
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
//procedencia
if ($procedenciagps>0 || $procedenciaid>0) {
	$qq = "SELECT localidadestring(".$gazetteerid.",".$procedenciaid.",0,0,0,0,0,0) as locality";
	$riq = mysql_query($qq,$conn);
	$riw = mysql_fetch_assoc($riq);
	$proctxt = $riw['locality'];
	if ($procedenciagps>0) {
		$procgaztxt = '';
		$procgpstxt = $proctxt;
	} else {
		$procgaztxt = $proctxt;
		$procgpstxt = '';
	}
} 
//se for indicado coletas entao, tem amostra!
if ($nosample=='no' && !empty($especimensids)) {
		unset($nosample);
}

	//neste caso apagou referencia a amostras coletadas
	if (!empty($_SESSION['oldspecids']) && empty($especimensids) && $_SESSION['editando']==('edit_'.$plantaid)) { 
		$nosample='no'; 
	}
//variaveis do formulario
$arrayofvars = array(
	 "nosample" => $nosample,
	 "detdoubt" => $detdoubt,
	 "addcoltxt" => $addcoltxt,
	 "addcolvalue" => $addcolvalue,
	 "altitude" => $altitude,
	 "colnum" => $colnum,
	 "datacol" => $datacol,
	 "datadet" => $datadet,
	 "determinadorid" => $determinadorid,
	 "famid" => $famid,
	 "gazetteerid" => $gazetteerid,
	 "genusid" => $genusid,
	 "habitatid" => $habitatid,
	 "infraspid" => $infraspid,
	 "latgrad" => $latgrad,
	 "latminu" => $latminu,
	 "latnors" => $latnors,
	 "latsec" => $latsec,
	 "longgrad" => $longgrad,
	 "longminu" => $longminu,
	 "longsec" => $longsec,
	 "longwore" => $longwore,
	 'latdec' => $latdec,
	 'longdec' => $longdec,
	 "plantaid" => $plantaid,
	 "speciesid" => $speciesid,
	 "traitids" => $traitids,
	 'arrayofvars' => $arrayofvars,
	 'cooraction' => $cooraction,
	 'localaction' => $localaction,
	 'habaction' => $habaction,
	 'detaction' => $detaction,
	 'especimensids' => $especimensids,
	 'especimenstxt' => $especimenstxt,
	 'plantnum' => $plantnum,
	 'inexsitu' => $inexsitu,
	 'detid' => $detid,
	 'procedenciaid' => $procedenciaid,
	 'gpspointid'=> $gpspointid,
	 'procedenciagps'=> $procedenciagps);

//extrair dados de habitat
if (!empty($habitatid)) {
	$habitat = describehabitat($habitatid,$img=TRUE,$conn);
}
if (!empty($traitarray)) {
	$traitids = describetraits($traitarray,$img=TRUE,$conn);
}
//abre tabela do formulario
if ($_SESSION['editando']==('edit_'.$plantaid)) {
	$ed = GetLangVar('nameeditando');
} 
else {
	$ed = GetLangVar('namenova');
}
echo "
<br />
<table class='myformtable' align='center' cellpadding='6' width='90%'>
<thead>
  <tr><td colspan='2' >".$ed." ".strtolower(GetLangVar('nametaggedplant'))."</td>
</tr>
</thead>
<tbody>
";
//numero da planta
if (empty($plantnum)) {
if ($bgi % 2 == 0){$bgcolor = $linecolor2 ;}  else {$bgcolor = $linecolor1 ;} $bgi++;
echo "
<tr bgcolor = '".$bgcolor."'>
  <td class='tdsmallboldright'>".GetLangVar('nametagnumber')."</td>
    <form name='especimenesform' action='plantas_dataform.php' method='post' >
      <input type='hidden' name='ispopup' value='$ispopup' />
      <input type='hidden' name='submeteu' value='$submeteu' />
    ";
    //hidden input tags for most variables
	$ll = $arrayofvars;
	unset($ll['arryofvars'],$ll['plantnum'],$ll['inexsitu'],$ll['especimensids'],$ll['especimenstxt'],$ll['nosample']);
	@hiddeninputs($ll);  
echo "
  <td >
    <table>
      <tr>
        <td><input id='tagplantnum'  type='text' name='plantnum' value='$plantnum' /></td>";

////// in situ ex situ, jardim botanico de manaus option, inicio
//if (empty($_SESSION['editando'])) {
//echo "
//        <td class='tdsmallboldcenter' colspan='2'>
//          <table>
//           <tr><td><input type='radio' name='inexsitu' value='Insitu' onselect=\"javascript:changevaluebyid('JB-N-','tagplantnum');\" />&nbsp;<i>In Situ</i></td></tr>
//           <tr><td><input type='radio' name='inexsitu' value='Exsitu' onselect=\"javascript:changevaluebyid('JB-X-','tagplantnum');\" />&nbsp;<i>Ex Situ</i></td></tr>
//           </table>
//        </td>";
//} 
///////////// in situ ex situ, jardim botanico de manaus option, fim
echo "
      </tr>
    </table>
  </td>
</tr>";

//seleciona coletas desta planta marcada se houver
if (!isset($fromprevious)) {
if (!empty($especimensids)) {
		$ids = explode(";",$especimensids);
		$qq = "SELECT CONCAT(addcolldescr(espc.ColetorID),' ',espc.Number) as colref FROM Especimenes as espc WHERE ";
		if (count($ids)>1) {
			$ii=0;
			foreach($ids as $val) {
				if ($ii>0) {
					$qq= $qq." OR ";
				}
				$qq = $qq." EspecimenID='".$val."'";
				$ii++;
			}
		} else {
				$qq = $qq." EspecimenID='".$especimensids."'";
		}
		$rr = mysql_query($qq,$conn);
		$ii=0;
		while ($row = mysql_fetch_assoc($rr)) {
			if ($ii==0) {
				$especimenstxt = $row['colref']; 
			} else {
				$especimenstxt = $especimenstxt."; ".$row['colref']; 
			}
			$ii++;
		}
}
if ($bgi % 2 == 0){$bgcolor = $linecolor2 ;}  else {$bgcolor = $linecolor1 ;} $bgi++;
echo "
<tr bgcolor = '".$bgcolor."'>
  <td class='tdsmallboldright'>".GetLangVar('nameamostra')."s ".GetLangVar('namecoletada')."s</td>
  <td >
    <table>
      <tr>";
			if ($_SESSION['editando']== 'edit_'.$plantaid) {
				$bt = "Adicionar"; 
			} else {
				$bt = "Editar";
			}
			echo "
        <td class='tdformnotes' >
          <input type='hidden' name='plantaid' value='$plantaid' />
          <input type='hidden' name='especimensids' value='$especimensids' />
          <input type='text' name='especimenstxt' value='$especimenstxt' readonly size='50%' />
        </td>
        <td><input  style='cursor:pointer' type=button value=\"".$bt."\" class='bsubmit' ";
			$myurl ="coletaspopup.php?getespecimensids=$especimensids&formname=especimenesform"; 
			echo " onclick = \"javascript:small_window('".$myurl."',350,280,'Amostras coletadas da planta');\" /></td>
        <td class='tdsmallboldright'>";
			if (empty($especimensids)) {
				echo "<input type='checkbox' name='nosample' value='no' ";
						if ($nosample=='no') { echo 'checked';}
							echo " onchange='this.form.submit();'>&nbsp;
				".GetLangVar('namenao')." ".strtolower(GetLangVar('namecoletada'));
			}
	echo "</td>
      </tr>
    </table>
  </td>
</tr>";
} else {
	//<input type='hidden' value='no' name='nosample' />
	$tokeep = array("fromprevious");
	foreach ($ppost as $kk => $vv) {
		if (!in_array($kk,$tokeep)) {
			echo "<input type='hidden' value='".$vv."' name='".$kk."' />";
		}
	}
if ($bgi % 2 == 0){$bgcolor = $linecolor2 ;}  else {$bgcolor = $linecolor1 ;} $bgi++;
echo "
<tr bgcolor = '".$bgcolor."'>
  <td colspan='2' ><input type='submit' value='Continuar' ></td>
</tr>";
}
echo "
</form>
";
} elseif (!empty($plantnum)) {
echo "
<form name='coletaform' action='plantas_dataform.php' method='post' >
  <input type='hidden' name='plantnum' value='$plantnum' />
  <input type='hidden' name='inexsitu' value='$inexsitu' />
  <input type='hidden' name='plantaid' value='$plantaid' />
  <input type='hidden' name='ispopup' value='$ispopup' />
  <input type='hidden' name='submeteu' value='$submeteu' />
";
if (isset($fromprevious)) {
	$estilo = "style='font-size: 2em; color: red; font-weight: bold;' ";
	$estilo2 = "<td align='left'><span style='font-size: 1em; color: blue; ' >".$fromprevious."</span></td>";
}
if ($bgi % 2 == 0){$bgcolor = $linecolor2 ;}  else {$bgcolor = $linecolor1 ;} $bgi++;
echo "
<tr bgcolor = '".$bgcolor."'>
  <td class='tdsmallboldright'>".GetLangVar('nametagnumber')."</td>
  <td >
    <table>
      <tr>
        <td><input $estilo id='tagplantnum'  type='text' name='plantnum' value='$plantnum' /></td>
        <td>".$estilo2."</td>
      </tr>
    </table>
  </td>
</tr>";
//seleciona coletas desta planta marcada se houver
if (!empty($especimensids)) {
		$ids = explode(";",$especimensids);
		$qq = "SELECT CONCAT(addcolldescr(espc.ColetorID),' ',espc.Number) as colref FROM Especimenes as espc WHERE ";
		if (count($ids)>1) {
			$ii=0;
			foreach($ids as $val) {
				if ($ii>0) {
					$qq= $qq." OR ";
				}
				$qq = $qq." EspecimenID='".$val."'";
				$ii++;
			}
		} else {
				$qq = $qq." EspecimenID='".$especimensids."'";
		}
		$rr = mysql_query($qq,$conn);
		$ii=0;
		while ($row = mysql_fetch_assoc($rr)) {
			if ($ii==0) {
				$especimenstxt = $row['colref']; 
			} else {
				$especimenstxt = $especimenstxt."; ".$row['colref']; 
			}
			$ii++;
		}
}
if ($bgi % 2 == 0){$bgcolor = $linecolor2 ;}  else {$bgcolor = $linecolor1 ;} $bgi++;
echo "
<tr bgcolor = '".$bgcolor."'>
  <td class='tdsmallboldright'>".GetLangVar('nameamostra')."s ".GetLangVar('namecoletada')."s</td>
  <td >
    <table>
      <tr>";
			if ($_SESSION['editando']== 'edit_'.$plantaid) {
				$bt = "Adicionar"; 
			} else {
				$bt = "Editar";
			}
			echo "
        <td class='tdformnotes' >
          <input type='hidden' name='especimensids' value='$especimensids' />
          <input type='text' name='especimenstxt' value='$especimenstxt' readonly size='50%' />
        </td>
        <td><input  style='cursor:pointer' type=button value=\"".$bt."\" class='bsubmit' ";
			$myurl ="coletaspopup.php?getespecimensids=$especimensids&formname=coletaform"; 
			echo " onclick = \"javascript:small_window('".$myurl."',350,280,'Amostras coletadas da planta');\" /></td>
        <td class='tdsmallboldright'>";
			if (empty($especimensids)) {
				echo "
          <input type='checkbox' name='nosample' value='no' ";
				if ($nosample=='no') { echo 'checked';}
				echo ">&nbsp;".GetLangVar('namenao')." ".strtolower(GetLangVar('namecoletada'));
			}
	echo "</td>
      </tr>
    </table>
  </td>
</tr>";
if ($bgi % 2 == 0){$bgcolor = $linecolor2 ;} else {$bgcolor = $linecolor1 ;} $bgi++;
echo "
  <tr bgcolor = '".$bgcolor."'>
    <td class='tdsmallboldright'>".GetLangVar('nametaggedby')."</td>
    <td >
      <table>
        <tr>
          <td class='tdformnotes' ><input type='text' style='font-size:10px; width:200px;  color:#999999; padding:5px; border:solid 1px #999999;' name='addcoltxt' value='$addcoltxt' readonly /></td>
          <input type='hidden' name='addcolvalue' value='$addcolvalue' />
          <td><input  style='cursor:pointer' type=button value=\"+\" class='bsubmit' ";
			$myurl ="addcollpopup.php?getaddcollids=$addcolvalue&formname=coletaform"; 
			echo " onclick = \"javascript:small_window('$myurl',600,400,'Coletores Adicionais');\" /></td>
          </td>
          <td class='tdsmallboldright'>".GetLangVar('namedata')."</td>";
		if ((empty($datacol) && empty($_SESSION['editando'])) || $final==2 || $datacol==0) {
			echo "
          <td>
            <input name=\"datacol\" value=\"$datacol\" size=\"11\" readonly />
            <a onclick=\"if(self.gfPop)gfPop.fPopCalendar(document.forms['coletaform'].datacol);return false;\" ><img name=\"popcal\" align=\"absmiddle\" src=\"calendar/calbtn.gif\" width=\"34\" height=\"22\" border=\"0\" alt=\"\"></a>
          </td>";
		} else {
			echo "
          <td><input class='selectedval' type='text'  value='$datacol' name='datacol' readonly /></td>";
		}
	echo "
        </tr>
      </table>
    </td>
</tr>"; 
//taxonomia
if ($bgi % 2 == 0){$bgcolor = $linecolor2 ;}  else {$bgcolor = $linecolor1 ;} $bgi++;
echo "
  <tr bgcolor = '".$bgcolor."'>
    <td class='tdsmallboldright'>".GetLangVar('nametaxonomy')."</td>
    <td >
      <table >
        <tr >
          <td id='dettexto'>$dettext</td>
          <input type='hidden' id='detsetcode' name='detset' value='$detset' />";
			if (empty($dettext)) {
				$butname = GetLangVar('nameselect');
			} else {
				$butname = GetLangVar('nameeditar');
			} 
		echo "
          <td>&nbsp;&nbsp;&nbsp;</td>
          <td><input  style='cursor:pointer' type=button value='$butname' class='bsubmit' ";
			$myurl ="taxonomia-popup.php?ispopup=1&detid=$detid&dettextid=dettexto&detsetid=detsetcode"; 
			echo " onclick = \"javascript:small_window('$myurl',900,400,'Taxonomia Popup');\" /></td>";
		if ($_SESSION['editando']=='edit_'.$plantaid) {
			echo "
          <td><input  style='cursor:pointer' type=button value='DetHistory' class='bblue' ";
			$myurl ="detchangespopup.php?plantaid=$plantaid"; 
			echo " onclick = \"javascript:small_window('$myurl',900,400,'Det History');\" /></td>";
		}
		echo "
        </tr>
      </table>
    </td>
  </tr>
";
//dados de nome vulgar
if ($bgi % 2 == 0) {$bgcolor = $linecolor2 ;} else { $bgcolor = $linecolor1 ;} $bgi++;
	echo "
  <tr bgcolor = '".$bgcolor."'>
    <input type='hidden' name='vernacularvalue' value='$vernacularvalue' />
    <td class='tdsmallboldright'>".GetLangVar('namevernacular')."</td>
    <td  >
      <table>
        <tr>
          <td class='tdformnotes' ><input size='60%' type='text' name='vernaculartxt' value='$vernaculartxt' readonly /></td>
          <td><input  style='cursor:pointer' type=button value=\"+\" class='bsubmit' ";
			$myurl ="vernacular_selector.php?getvernacularids=$vernacularvalue&formname=coletaform"; 
			echo " onclick = \"javascript:small_window('$myurl',350,280,'Add_from_Src_to_Dest');\" /></td>
        </tr>
      </table>
    </td>
  </tr>"; 

//dados de localidade
if ($bgi % 2 == 0){$bgcolor = $linecolor2 ;} else{$bgcolor = $linecolor1 ;} $bgi++;
echo "
<tr bgcolor = '".$bgcolor."'>
  <td class='tdsmallboldright'>".GetLangVar('namelocalidade')."&nbsp;<img height='14' src=\"icons/icon_question.gif\" ";
	$help = GetLangVar('localidadetipos2');
	echo " onclick=\"javascript:alert('$help');\" /></td>
  <td>
  <table>
    <tr><td class='tdformnotes' colspan='100%'>$localtxt</td></tr>
    <tr>
      <td class='tdsmallboldright'>OPÇÃO&nbsp;01&nbsp;-&nbsp;Localidade</td>
      <td>"; 
		autosuggestfieldval3('search-gazetteer-new.php','locality',$gaztxt,'localres','gazetteerid',$gazetteerid,true,60);
		echo "
      </td>";
	  $myurl = "localidade_dataexec.php?ispopup=1&municipioid=$municipioid&paisid=$paisid&provinciaid=$provinciaid";
		echo "
      <td><input  style='cursor:pointer' type=button class='bblue' value='".GetLangVar('namenova')."'  onclick =\"javascript:small_window('$myurl',900,300,'Cadastrar nova localidade');\" /></td>
	</tr>
    <tr>
      <td class='tdsmallboldright'>OPÇÃO&nbsp;02&nbsp;-&nbsp;Ponto&nbsp;de&nbsp;GPS</td>
      <td>"; 
		autosuggestfieldval3('search-gpspoint.php','gpspt',$gpstxt,'gpsres','gpspointid',$gpspointid,true,60); 
		echo "
      </td>
	</tr>
  </table>
  </td>
</tr>";
if ($bgi % 2 == 0){$bgcolor = $linecolor2 ;} else{$bgcolor = $linecolor1 ;} $bgi++;
echo "
  <tr bgcolor = '".$bgcolor."'>
    <td class='tdsmallboldright'>Coordenadas&nbsp;<img style='cursor:pointer;' height='14' src=\"icons/icon_question.gif\" ";
	$help = "Coordenadas da planta na localidade, que pode ser uma parcela retangular ou quadrada com posição X e Y, circular - Distância e ângulo do ponto central, ou um ponto de gps em qualquer lugar - distancia e angulo em relação ao ponto, como um marco numa trilha";
	echo " onclick=\"javascript:alert('$help');\" /></td>
    <td>
      <table >";
		if ($gazetteerid>0) {
			$sql = "SELECT IF((DimX+DimY+DimDiameter)>0,TRUE,FALSE) as parcela FROM Gazetteer WHERE GazetteerID=".$gazetteerid;
			$sqr = mysql_query($sql,$conn);
			$sqw = mysql_fetch_assoc($sqr);
			$parcela = $sqw['parcela'];
		}
		if ($parcela) {
		echo "
        <tr><td colspan='100%' class='tdsmallbold'>A localidade é uma parcela!</td></tr>";
		}
echo "
        <tr>
          <td class='tdsmallboldright'>&nbsp;&nbsp;X&nbsp;<img style='cursor:pointer;' height='14' src=\"icons/icon_question.gif\" ";
			$help = 'Referência da X na localidade';
			if ($parcela) {
				$help .= ', que neste caso é uma parcela';
			}
			echo " onclick=\"javascript:alert('$help');\" /></td>
          <td><input type='text'  style='text-align:right' value='".$plpos_x."' name='plpos_x' size='5' />&nbsp;m</td>
          <td class='tdsmallboldright'>&nbsp;&nbsp;Y</td>
          <td><input type='text' style='text-align:right' value='".$plpos_y."'  name='plpos_y' size='5' />&nbsp;m</td>
        </tr>
        <tr>  
          <td colspan='100%'>
          <table>
            <tr>
                <td class='tdsmallboldright'>Referência&nbsp;<img style='cursor:pointer;' height='14' src=\"icons/icon_question.gif\" ";
			$help = 'Referência das coordenadas X e Y na localidade';
			if ($parcela) {
				$help .= ', que neste caso é uma parcela';
			}
			echo " onclick=\"javascript:alert('$help');\" /></td>
                <td>
                  <select name='xyref'>";
					if (!empty($xyref)) {
						if ($xyref=='xyref00') { $xyreftxt = 'Vértice Esquerdo-Inferior (0,0 ou SW';}
						if ($xyref=='xyref01') { $xyreftxt = 'Vértice Esquerdo-Superior (0,1 ou NW)';}
						if ($xyref=='xyref11') { $xyreftxt = 'Vértice Direito-Superior (1,1 ou NE)';}
						if ($xyref=='xyref10') { $xyreftxt = 'Vértice Direito-Inferior (1,0 ou SE)';}
						if ($xyref=='E') { $xyreftxt = 'LADO E, esquerdo em relação à linha central (PPBIO)';}
						if ($xyref=='D') { $xyreftxt = 'LADO D, direito em relação à linha central (PPBIO)';}
						echo "
                    <option selected value='".$xyref."'>$xyreftxt</option>
                    <option value=''>----------</option>";
					} else {
						echo "
                  <option value=''>".GetLangVar('nameselect')."</option>";
					}
					echo "
                  <option value='xyref00'>Vértice Esquerdo-Inferior (0,0)</option>
                  <option value='xyref01'>Vértice Esquerdo-Superior (0,1)</option>
                  <option value='xyref11'>Vértice Direito-Superior (1,1)</option>
                  <option value='xyref10'>Vértice Direito-Inferior (1,0)</option>
                  <option value='E'>LADO E, esquerdo em relação à linha central (PPBIO)</option>
                  <option value='D'>LADO D, direito em relação à linha central (PPBIO)</option>
                </select>
              </td>
            </tr>
          </table>
        </td>
      </tr>
      <tr>
        <td class='tdsmallboldright'>&nbsp;&nbsp;Distância&nbsp;<img style='cursor:pointer;'  height='14' src=\"icons/icon_question.gif\" ";
	$help = 'Distância a um ponto de referência, que é ou uma localidade ou um ponto de GPS, com prioridade para o segundo se ambos informados';
	echo " onclick=\"javascript:alert('$help');\" /></td>
        <td><input type='text'  style='text-align:right'  value='".$plpos_dist."' name='plpos_dist' size='5' />&nbsp;m</td>
        <td class='tdsmallboldright'>&nbsp;&nbsp;Bússola&nbsp;<img style='cursor:pointer;'  height='14' src=\"icons/icon_question.gif\" ";
	$help = 'Ângulo de Azimute, variando de 0 a 360 graus, indicando o sentido da posição da planta em relação ao ponto de referência, seja este uma localidade ou ponto de GPS';
	echo " onclick=\"javascript:alert('$help');\" /></td>
        <td><input type='text'  style='text-align:right;' value='".$plpos_angle."' name='plpos_angle' size='5' />&nbsp;dg&nbsp;N</td>
        <td>&nbsp;</td>
      </tr>
    </table>
  </td>
</tr>";
//habitat descricao
if ($bgi % 2 == 0){$bgcolor = $linecolor2 ;} else {$bgcolor = $linecolor1;} $bgi++;
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
        <td align='center'><input  style='cursor:pointer' type=button value='$buthab' class='bsubmit' onclick = \"javascript:small_window('habitat-popup.php?ispopup=1&pophabitatid=$habitatid&elementidval=habitatidfield&elementidtxt=habitatfield&opening=1',850,400,'Selecione um habitat');\" /></td>
      </tr>
    </table>
  </td>
</tr>";
if ($inexsitu=='Exsitu') {
//dados de procedencia se for o caso
if ($bgi % 2 == 0){$bgcolor = $linecolor2 ;} else{$bgcolor = $linecolor1 ;} $bgi++;
echo "
<tr bgcolor = '".$bgcolor."'>
  <td class='tdsmallboldright'>".GetLangVar('nameprocedencia')."&nbsp;<img height='14' src=\"icons/icon_question.gif\" ";
	$help = GetLangVar('messageexplainprocedencia');
	echo " onclick=\"javascript:alert('$help');\" /></td>
  <td>
  <table>
    <tr><td class='tdformnotes' colspan='100%'>$proctxt</td></tr>
    <tr>
      <td class='tdsmallbold'>OPÇÃO&nbsp;01&nbsp;-&nbsp;Localidade</td>
      <td>"; 
		autosuggestfieldval3('search-gazetteer-new.php','procgaztxt',$procgaztxt,'localress','procedenciaid',$procedenciaid,true,60);
		echo "
      </td>
	</tr>
    <tr>
      <td class='tdsmallboldright'>OPÇÃO&nbsp;02&nbsp;-&nbsp;Ponto&nbsp;de&nbsp;GPS</td>
      <td>"; 
		autosuggestfieldval3('search-gpspoint.php','procgpstxt',$procgpstxt,'gpsress','procedenciagps',$procedenciagps,true,60); 
		echo "
      </td>
	</tr>
  </table>
  </td>
</tr>";
}
///coordenadas da planta
if ($bgi % 2 == 0){ $bgcolor = $linecolor2 ;} else{ $bgcolor = $linecolor1;} $bgi++;
echo "
<tr bgcolor = '".$bgcolor."'>
  <td class='tdsmallboldright'>Latitude&nbsp;&&nbsp;Longitude&nbsp;<img style='cursor:pointer;' height='13' src=\"icons/icon_question.gif\" ";
		$help = GetLangVar('messageexplaincoordenadas');
		echo " onclick=\"javascript:alert('$help');\" /></td>
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
              <td align='left'>
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
if ($bgi % 2 == 0){$bgcolor = $linecolor2 ;} else {$bgcolor = $linecolor1 ;} $bgi++;
echo "
<tr bgcolor = '".$bgcolor."'>
  <td class='tdsmallboldright'>".GetLangVar('nameobs')."s</td>
  <td >
    <table  align='left' border=0 cellpadding=\"3\" cellspacing=\"0\">
      <tr>
        <td id='traitids' class='tdformnotes'>$traitids</td>
        <td align='left'><input  style='cursor:pointer'  type=button value=\"".GetLangVar('nameselect')."\" class='bsubmit' onclick = \"javascript:small_window('traits_coletorvariacao.php?&elementid=traitids&submeteu=1',1000,500,'EntrarVariacao');\" /></td>
      </tr>
    </table>
  </td>
</tr>";
if ($bgi % 2 == 0){$bgcolor = $linecolor2 ;} else { $bgcolor = $linecolor1;} $bgi++;
echo "
<tr bgcolor = '".$bgcolor."'>
  <td class='tdsmallboldright'>Dados de Monitoramento</td>
  <td >
    <table  align='left' border=0 cellpadding=\"3\" cellspacing=\"0\">
      <tr>
        <td id='traitids_moni' class='tdformnotes'>".$monidesc."</td>
        <td align='left'><input  style='cursor:pointer'  type=button value=\"".GetLangVar('nameselect')."\" class='bsubmit' onclick = \"javascript:small_window('traits_coletormonitoramento.php?ispopup=1&elementid=traitids_moni&plantatag=".$plantnum."&plantaid=".$plantaid."&submeteu=1',1000,500,'Dados de Monitoramento');\" /></td>
      </tr>
    </table>
  </td>
</tr>";
if ($bgi % 2 == 0){$bgcolor = $linecolor2 ;}  else {$bgcolor = $linecolor1 ;} $bgi++;
echo "
<tr bgcolor = '".$bgcolor."'>
  <td colspan='100%'>
    <table align='center' >
      <tr>
        <input type='hidden' name='final' value='' />
        <td align='center' ><input  style='cursor:pointer' type='submit' value='".GetLangVar('namesalvar')."' class='bsubmit' onclick=\"javascript:document.coletaform.final.value=1\" /></td>
        <!---<td align='left'><input  style='cursor:pointer' type='submit' value='".GetLangVar('messagesalvareduplicar')."' class='bblue' onclick=\"javascript:document.coletaform.final.value=2\" /></td>--->
</form>
<form action='plantas_dataform.php' method='post'>
        <td align='left'><input  style='cursor:pointer' type='submit' value='".GetLangVar('namevoltar')."' class='breset' /></td>
</form>
      </tr>
    </table>
  </td>
</tr>
";
}

echo "
</tbody>
</table>";
}

$which_java = array(
"<script type='text/javascript' src='javascript/myjavascripts.js'></script>");
FazFooter($which_java,$calendar=TRUE,$footer=$menu);

?>