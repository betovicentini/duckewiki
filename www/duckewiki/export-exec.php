<?php
session_start();
set_time_limit(0);
ini_set('max_execution_time', 7200);
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

$dd = @unserialize($_SESSION['destvararray']);
@extract($dd);

if (empty($filtro)) { 
	header("location: export-monitoramento-form.php");
}

$basvar = array();
if (count($basicvariables)>0) {
	foreach($basicvariables as $val) {
		$var = array($val => $val);
		$basvar = array_merge((array)$basvar,(array)$var);
	}
}

	$expfl = "temp_espexport_".$_SESSION['userlastname'];
	$qd = "DROP TABLE IF EXISTS ".$expfl;
	mysql_query($qd,$conn);

	$qq = "CREATE TABLE ".$expfl." (EspecID INT(10) NOT NULL AUTO_INCREMENT, PRIMARY KEY (PlID))"; 
	$qq .= " SELECT pltb.EspecimenID AS EspecID, CONCAT(IF(pltb.Prefixo='','',pltb.Prefixo),'-',colpessoa.Abreviacao,'-',IF(pltb.Sufix='','',pltb.Sufix)) as TAG_NUM,
	colpessoa.Abreviacao as COLETOR,
	pltb.Prefixo as PREFIXO,
	pltb.Number as NUMBER,
	pltb.Sufix as SUFIXO
	"; 
		
	if (!empty($basvar['nomenoautor'])) {			
		$metadados['NOME'] = 'Taxonomia no nivel de identificacao sem autores';
		$qq .=", IF(iddet.InfraEspecieID>0,CONCAT(gentb.Genero,' ',sptb.Especie,' ',infsptb.InfraEspecie),IF(iddet.EspecieID>0,CONCAT(gentb.Genero,' ',sptb.Especie),IF(iddet.GeneroID>0,gentb.Genero,famtb.Familia))) as NOME";
	} 
	if (!empty($basvar['nomeautor'])) {
		$metadados['NOMEeAUTOR'] = 'Taxonomia no nivel de identificacao, se em nivel de especie ou de infraespecie, nome dos autores incluidos';
		$qq .=", IF(iddet.InfraEspecieID>0,CONCAT(gentb.Genero,' ',sptb.Especie,' ',sptb.EspecieAutor,' ',infsptb.InfraEspecieNivel,' ',infsptb.InfraEspecie,' ',infsptb.InfraEspecieAutor),IF(iddet.EspecieID>0,CONCAT(gentb.Genero,' ',sptb.Especie,' ',sptb.EspecieAutor),IF(iddet.GeneroID>0,gentb.Genero,famtb.Familia))) as NOMEeAUTOR";
	}
	if (!empty($basvar['taxacompleto'])) {
		$metadados['FAMILY'] = 'Familia botanica';
		$metadados['GENUS'] = 'Genero botanico, onde Indet= indeterminado nesse nivel';
		$metadados['SP1'] = 'Epiteto da especie';
		$metadados['AUTHOR1'] = 'Autoridade do nome da especie';
		$metadados['RANK1'] = 'Categoria de nivel infra-especifico, variedade, subespecie, forma, etc.';
		$metadados['SP2'] = 'Nome da categoria infra-especifica';
		$metadados['AUTHOR2'] = 'Autoridade do nome da categoria infra-especifica';
		$metadados['CF'] = "Modificadores de nome como aff. cf. vel. aff. etc, indicado por quem fez a identificacao";
		$metadados['DETBY'] = "Nome da pessoa que fez a identificacao";
		$metadados['DETDD'] = "Dia da data de identificacao";
		$metadados['DETMM'] = "Mes da data de identificacao";
		$metadados['DETYY'] = "Ano da data de identificacao";
			
		$qq .=", famtb.Familia as FAMILY, gentb.Genero as GENUS, sptb.Especie as SP1, sptb.EspecieAutor as AUTHOR1, infsptb.InfraEspecieNivel as RANK1, infsptb.InfraEspecie as SP2, infsptb.InfraEspecieAutor as AUTHOR2, iddet.DetModifier as CF, detpessoa.Abreviacao as DETBY, DAY(iddet.DetDate) as DETDD, MONTH(iddet.DetDate) as DETMM, YEAR(iddet.DetDate) as DETYY";
	}
	if (!empty($basvar['localidade'])) {
		$metadados['COUNTRY'] = "Nome do pais";
		$metadados['MAJORAREA'] = "Nome da primeira subdivisao administrativa do pais, provincias, estados, departamentos, cantoes, dependendo do pais";
		$metadados['MINORAREA'] = "Nome da subdivisao de MAJORAREA, geralmente os municipios ou condados";
		$metadados['GAZETTEER'] = "As demais subdivisoes de MINORAREA. Quando houver mais de uma, concatenadas de forma hierarquica do maior para o menor";
		$metadados['GAZETTEER2'] = "O GAZETTEER mais especifico da planta";
		
		$qq .= ",  Country as COUNTRY, Province as MAJORAREA, Municipio as MINORAREA, IF(pltb.GPSPointID>0,gazgps.PathName,IF(pltb.GazetteerID>0,gaz.PathName,''))as GAZETTEER, IF(pltb.GPSPointID>0,gazgps.Gazetteer,IF(pltb.GazetteerID>0,gaz.Gazetteer,''))as GAZETTEER2";
	}
	if (!empty($basvar['gps'])) {
		$metadados['LONGITUDE'] = "Longitude em decimos de grau, com valores negativos para posicoes W e valores positivos para posicoes E";
		$metadados['LATITUDE'] = "Laitude em decimos de grau, com valores negativos para posicoes S e valores positivos para posicoes N";
		$metadados['ALTITUDE'] = "Altitude em m sobre o nivel do mar";
		$metadados['COORD_PRECISION'] = "Precisao das coordenadas, onde GPS_planta indica que a coordenada e do individuo; GPS quando for a coordenada de um ponto de GPS da proximidade do individuo; Localidade indica que a coordenada se refere a uma das localidades em GAZETTEER; Municipio e a coordenada de MINORAREA";
		
		$qq .= ", IF(ABS(pltb.Longitude)>0,pltb.Longitude,IF(pltb.GPSPointID>0,gpspt.Longitude,IF(gaz.Longitude<>0,gaz.Longitude,muni.Longitude))) as LONGITUDE, IF(ABS(pltb.Longitude)>0,pltb.Latitude,IF(pltb.GPSPointID>0,gpspt.Latitude,IF(gaz.Longitude<>0,gaz.Latitude,muni.Latitude))) as LATITUDE, IF(ABS(pltb.Longitude)>0,pltb.Altitude,IF(pltb.GPSPointID>0,gpspt.Altitude,IF(gaz.Longitude<>0,gaz.Altitude,''))) as ALTITUDE, IF(ABS(pltb.Longitude)>0,'GPS-planta',IF(pltb.GPSPointID>0,'GPS',IF(gaz.Longitude<>0,'Localidade','Municipio'))) as COORD_PRECISION";
	}
	
	if ($formvariables>0) {
		$qu = "SELECT * FROM Traits JOIN FormulariosTraitsList ON FormulariosTraitsList.TraitID=Traits.TraitID  WHERE FormulariosTraitsList.FormID=".$formvariables."  ORDER BY FormulariosTraitsList.Ordem";
		$ru = mysql_query($qu,$conn);
		while($rw = mysql_fetch_assoc($ru)) {
			$tid = $rw['TraitID'];
			$ttipo = $rw['TraitTipo'];
			$ttname = trim($rw['TraitName']);
			$tdefini = trim($rw['TraitDefinicao']);
			$tascol = $rw['TraitAsCol'];
			$tascol = str_replace("(","",$tascol);
			$tascol = str_replace(")","",$tascol);
			
			if ($ttipo=='Variavel|Quantitativo') {
				$tti = explode("|",$ttipo);
				$tdef = $ttname.". (".$tdefini."). Variavel ".$tti[1].". Unidade de medida padrao ".$rw['TraitUnit'].".";
				$tdef = str_replace("..",".",$tdef);
				$tdef = str_replace(".)",")",$tdef);
				$metadados[$tascol] = $tdef;
				$metadados[$tascol."_UNIT"] = "Unidade da medicao da variavel $tascol";
				$qq = $qq.", traitvariation_specimens($tid,PlantaID, 0) AS ".$tascol.", 
				traitvariation_specimens($tid,PlantaID, 1) AS ".$tascol."_UNIT";
			} else {
				$tti = explode("|",$ttipo);
				$tdef = $ttname.". (".$tdefini."). Variavel ".$tti[1].".";
				$tdef = str_replace("..",".",$tdef);
				$tdef = str_replace(".)",")",$tdef);
				$metadados[$tascol] = $tdef;
				$qq = $qq.", traitvariation_specimens($tid,PlantaID, 0) AS ".$tascol;
			}	
		}
	}

	if ($formnotes>0) { 
	
	}
	if (!empty($basvar['habitat'])) {
		$qq = $qq.", habitaclasse(pltb.HabitatID) AS  HABITAT_CLASSE";
	}	
	if ($formhabitat>0) {
			$qu = "SELECT * FROM Traits JOIN FormulariosTraitsList ON FormulariosTraitsList.TraitID=Traits.TraitID  WHERE FormulariosTraitsList.FormID=".$formhabitat."  ORDER BY FormulariosTraitsList.Ordem";
			$ruu = mysql_query($qu,$conn);
			$hi=0;
			while($rwu = mysql_fetch_assoc($ruu)) {
				$tid = $rwu['TraitID'];
				$ttipo = $rwu['TraitTipo'];
				$ttname = trim($rwu['TraitName']);
				$tdefini = trim($rwu['TraitDefinicao']);
				$tascol = $rwu['TraitAsCol']."hab";
				$tascol = str_replace("(","",$tascol);
				$tascol = str_replace(")","",$tascol);

				if ($ttipo=='Variavel|Quantitativo') {
						$tti = explode("|",$ttipo);
						$tdef = $ttname.". (".$tdefini."). Variavel ".$tti[1].". Unidade de medida padrao ".$rwu['TraitUnit'].".";
						$tdef = str_replace("..",".",$tdef);
						$tdef = str_replace(".)",")",$tdef);
						$metadados[$tascol] = $tdef." (habitat)";
						$metadados[$tascol."_UNIT"] = "Unidade da medicao da variavel $tascol (habitat)";
						$metadados[$tascol."_DATE"] = "Data da medicao da 	variavel $tascol (habitat)";
						$qq = $qq.", habitatvariation($tid,pltb.HabitatID,0) AS ".$tascol.", habitatvariation($tid,pltb.HabitatID,1) AS  ".$tascol."_UNIT";
				} else {
					$tti = explode("|",$ttipo);
					$tdef = $ttname.". (".$tdefini."). Variavel ".$tti[1].".";
					$tdef = str_replace("..",".",$tdef);
					$tdef = str_replace(".)",")",$tdef);
					$metadados[$tascol] = $tdef." (habitat)";
					$metadados[$tascol."_DATE"] = "Data da medicao da variavel $tascol (habitat)";
					$qq = $qq.", habitatvariation($tid,pltb.HabitatID,0) AS ".$tascol;				
				}	
			}
		
	}
	if (!empty($basvar['vernacular'])) {	

	}
	//marcado por 
	if (!empty($basvar['taggedby'])) {
	
	}	
	if (!empty($basvar['datacol'])) {
		$qq .= ", TaggedDate as DATAdaMARCACAO";
	}
	if (!empty($basvar['projeto'])) {
		$qq .= ", ProjetoNome as PROJETO";
	}

	
	$qq = $qq." FROM Especimenes as pltb LEFT JOIN Pessoas as colpessoa ON pltb.ColetorID=colpessoa.PessoaID";
	
	if (!empty($basvar['nomenoautor']) || !empty($basvar['nomeautor']) || !empty($basvar['taxacompleto'])) {
		$qq .= " JOIN Identidade as iddet USING(DetID) LEFT JOIN Tax_InfraEspecies as infsptb ON iddet.InfraEspecieID=infsptb.InfraEspecieID LEFT JOIN Tax_Especies as sptb ON iddet.EspecieID=sptb.EspecieID LEFT JOIN Tax_Generos as gentb ON iddet.GeneroID=gentb.GeneroID  LEFT JOIN Tax_Familias as famtb ON iddet.FamiliaID=famtb.FamiliaID LEFT JOIN Pessoas as detpessoa ON detpessoa.PessoaID=iddet.DetbyID ";
		} 
	if (!empty($basvar['localidade']) || !empty($basvar['gps'])) {
		$qq .= " LEFT JOIN Gazetteer as gaz ON gaz.GazetteerID=pltb.GazetteerID LEFT JOIN GPS_DATA as gpspt ON gpspt.PointID=pltb.GPSPointID LEFT JOIN Gazetteer as gazgps ON gazgps.GazetteerID=pltb.GazetteerID LEFT JOIN Municipio as muni ON gaz.MunicipioID=muni.MunicipioID LEFT JOIN Province  ON Province.ProvinceID=muni.ProvinceID LEFT JOIN Country  ON Country.CountryID=Province.CountryID";
	}
	if (!empty($basvar['projeto'])) {
		$qq .=" LEFT JOIN Projetos ON pltb.ProjetoID=Projetos.ProjetoID";	
	}
	$qq .= " JOIN FiltrosSpecs as fl ON pltb.EspecimenID=fl.EspecimenID WHERE fl.FiltroID=".$filtro;
	$criou = mysql_query($qq,$conn);
	
if ($criou) {
	$expfl = "temp_espexport_".$_SESSION['userlastname'];

	$qq = "SELECT * FROM ".$expfl." ORDER BY TAG_NUM"; 
	$ress = mysql_query($qq,$conn);
	$nresult = mysql_numrows($ress);
	$count = mysql_num_fields($ress);

	$cdate = date("Y-m-d"); // get current date
	$export_filename = "especimenes_export_".$_SESSION['userlastname']."_".$cdate.".csv";
	$fh = fopen("temp/".$export_filename, 'w') or die("nao foi possivel gerar o arquivo");
	
// fetch table header
	$header = '';
	for ($i = 0; $i < $count; $i++){
		$header .= mysql_field_name($ress, $i)."\t";
	}
	fwrite($fh, $header);

// fetch data each row, store on tabular row data
	while($rsw = mysql_fetch_row($ress)){
		$line = '';
		foreach($rsw as $value){
			if(!isset($value) || $value == ""){
				$value = "\t";
			}else{
				//important to escape any quotes to preserve them in the data.
				$value = str_replace('"', '""', $value);
				//needed to encapsulate data in quotes because some data might be multi line.
				//the good news is that numbers remain numbers in Excel even though quoted.
				$value = '"' . $value . '"' . "\t";
			}
	
			$line .= $value;
		}
		$lin = trim($line)."\n";
		fwrite($fh, $lin);
	}
	fclose($fh);
	
	$export_filename_metadados = "especimenes_export_".$_SESSION['userlastname']."_".$cdate."_metadados.csv";
	$fh = fopen("temp/".$export_filename_metadados, 'w') or die("nao foi possivel gerar o arquivo");
	$stringData = "COLUNA\tDEFINICAO"; 
	foreach ($metadados as $kk => $vv) {
		$stringData = $stringData."\n".$kk."\t".$vv;
	}
	$stringData = $stringData;
	fwrite($fh, $stringData);
	fclose($fh);
	
	if (file_exists("temp/".$export_filename)) {
		header("location: export-especimenes-save.php");
	} else {
		header("location: export-especimenes-form.php");
	}
} else { 
	header("location: export-especimenes-form.php");
}


?>