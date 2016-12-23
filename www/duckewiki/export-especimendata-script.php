<?php
//Start session
ini_set("memory_limit","-1");
ini_set("mysql.allow_persistent","-1");

//Start session
session_start();

//INCLUI FUNCOES PHP E VARIAVEIS
include "functions/HeaderFooter.php";
include "functions/MyPhpFunctions.php";
//include_once("functions/class.Numerical.php") ;

//FAZ A CONEXAO COM O BANCO DE DADOS
//$lang = $_SESSION['lang'];
$dbname = $_SESSION['dbname'];
$conn = ConectaDB($dbname);

//////PEGA E LIMPA VARIAVEIS
$ppost = cleangetpost($_POST,$conn);
@extract($ppost);
$arval = $ppost;
$gget = cleangetpost($_GET,$conn);
@extract($gget);


$export_filename = "especimenes_export_".$_SESSION['userlastname']."_".$_SESSION['sessiondate'].".csv";
$export_filename_metadados = "especimenes_export_".$_SESSION['userlastname']."_".$_SESSION['sessiondate']."_definicoesDAScolunas.csv";
if (!isset($usarmodelo)) { $usarmodelo=0;}

if (empty($herbariumsigla)) {
	$herbariumsigla = 'HERB_NO';
}

$progesstable = "temp_exportespecimenes".$_SESSION['userid']."_".substr(session_id(),0,10);


//echopre($_SESSION);
//EXTRAI VARIAVEIS DO POST
$dd = @unserialize($_SESSION['destvararray']);
@extract($dd);
//echopre($dd);
unset($_SESSION['metadados']);
unset($metadados);
unset($_SESSION['qq']);
unset($qq);
unset($_SESSION['qz']);



	if (!empty($specbasicvars)) {
		$basicvariables = explode(";",$specbasicvars);
	}
	$basvar = array();
	if (count($basicvariables)>0) {
		foreach($basicvariables as $val) {
			$var = array($val => $val);
			$basvar = array_merge((array)$basvar,(array)$var);
		}
	}
	if (!isset($formvarmean)) {
		$formvarmean=0;
	}
	if (!isset($habmean)) {
		$habmean=0;
	}
$qqbrahms = " SELECT DISTINCT 
pltb.EspecimenID AS WikiEspecimenID, 
pltb.PlantaID AS WikiPlantaID, 
prcc.".$herbariumsigla." AS accession, 
colpessoa.Abreviacao as collector, 
'' as prefix, 
pltb.Number as number, 
'' as suffix, 
addcolldescr(AddColIDS) as addcoll, 
IF (pltb.Day>0,pltb.Day,'')  as colldd, 
IF (pltb.Mes>0,pltb.Mes,'')  as collmm, 
IF (pltb.Ano>0,pltb.Ano,'')  as collyy";
if ($duplicatesTraitID>0) {
	$qqbrahms .=", traitvaluespecs(".$duplicatesTraitID.",0,pltb.EspecimenID,'',0,0) as inicial";
} else {
	$qqbrahms .=", '' as inicial";
}
$qqbrahms .=", 
famtb.Familia as family, 
gentb.Genero as genus, 
iddet.DetModifier as detstatus, 
IF(sptb.Morfotipo=1,'',sptb.Especie) as sp1, 
IF(infsptb.Morfotipo=1,'',infsptb.InfraEspecieNivel) as rank1, 
IF(infsptb.Morfotipo=1,'',infsptb.InfraEspecie) as sp2, 
detpessoa.Abreviacao as detby, 
IF(DAY(iddet.DetDate)>0,DAY(iddet.DetDate),IF(iddet.DetDateDD>0,iddet.DetDateDD,'')) as detdd, 
IF(MONTH(iddet.DetDate)>0,MONTH(iddet.DetDate),IF(iddet.DetDateMM>0,iddet.DetDateMM,'')) as detmm,
IF(YEAR(iddet.DetDate)>0,YEAR(iddet.DetDate),IF(iddet.DetDateYY>0,iddet.DetDateYY,'')) as detyy";
$qqbrahms .= ",  
localidadefields(pltb.GazetteerID, pltb.GPSPointID,pltb.MunicipioID, pltb.ProvinceID,pltb.CountryID,'COUNTRY')  as country,
localidadefields(pltb.GazetteerID, pltb.GPSPointID,pltb.MunicipioID, pltb.ProvinceID,pltb.CountryID, 'MAJORAREA')  as majorarea,
localidadefields(pltb.GazetteerID, pltb.GPSPointID,pltb.MunicipioID, pltb.ProvinceID,pltb.CountryID, 'MINORAREA')  as minorarea, 
localidadefields(pltb.GazetteerID, pltb.GPSPointID,pltb.MunicipioID, pltb.ProvinceID,pltb.CountryID, 'GAZfirstPARENT')  as gazetteer";
if ($localidadetraitid>0) {
	$qqbrahms .=", CONCAT(localidadefields(pltb.GazetteerID, pltb.GPSPointID,pltb.MunicipioID, pltb.ProvinceID,pltb.CountryID, 'GAZminusParent'),'. ', traitvaluespecs(".$localidadetraitid.",0,pltb.EspecimenID,'',0,0))  as locnotes";
} else {
$qqbrahms .=", localidadefields(pltb.GazetteerID, pltb.GPSPointID,pltb.MunicipioID, pltb.ProvinceID,pltb.CountryID, 'GAZminusParent') as locnotes";
}
if ($formidhabitat>0) {
	$qqbrahms .= ", habitatstring2(pltb.HabitatID, ".$formidhabitat.", TRUE,FALSE)  as habitattxt";
} else {
	$qqbrahms .=", '' as habitattxt";
}
$qqbrahms .= ", 
abs(getlatlongdms(pltb.Latitude+0, pltb.Longitude+0, pltb.GPSPointID, pltb.GazetteerID, pltb.MunicipioID, pltb.ProvinceID, CountryID, 1,5))  as `lat`,
getlatlongdms(pltb.Latitude+0, pltb.Longitude+0, pltb.GPSPointID, pltb.GazetteerID, pltb.MunicipioID, pltb.ProvinceID,pltb.CountryID, 1,4) as NS,
abs(getlatlongdms(pltb.Latitude+0, pltb.Longitude+0, pltb.GPSPointID, pltb.GazetteerID, pltb.MunicipioID, pltb.ProvinceID,pltb.CountryID, 0,5))  as `long`,
'DD' as llunit,
getlatlongdms(pltb.Latitude+0, pltb.Longitude+0, pltb.GPSPointID, pltb.GazetteerID, pltb.MunicipioID, pltb.ProvinceID,pltb.CountryID, 0,4)  as EW,
ROUND(getaltitude(pltb.Altitude+0, pltb.GPSPointID,pltb.GazetteerID),0) as alt";
if ($usarmodelo==1) {
$qqbrahms .= ", '' as plantdesc";
} else {
	if ($monidata==1) {
		$qqbrahms .= ", labeldescricao(pltb.EspecimenID,pltb.PlantaID,".($formnotas+0).",1,0) as plantdesc";
	} else {
		$qqbrahms .= ", labelnotes_nomoni(pltb.EspecimenID,0,".($formnotas+0).",1,0) as plantdesc";
	}
}
$qqbrahms .= ", vernaculars(pltb.VernacularIDS) as vernacular";
$qqbrahms .= ", '".$herbariumsigla."' as dups";
	//if ($traitsilica>0) {
		//$qqbrahms .= ", checkspecsilica(pltb.EspecimenID,".$traitsilica.") as HasSilica";
	//}
if ($exsicatatrait>0) {
		//EXTRAI A URL 
	$url = $_SERVER['HTTP_REFERER'];
	$uu = explode("/",$url);
	$nu = count($uu)-1;
	unset($uu[$nu]);
	$httppath = implode("/",$uu);
	$qqbrahms .= ", getspecexsicataimg(pltb.EspecimenID,'".$httppath."/img/originais',".$exsicatatrait.") as ImagesLinks";
}
//$qqbrahms .=", pltb.Herbaria as Herbaria";
$qqbrahms .= ", projetostringbrahmsnovo(pltb.EspecimenID,0) as project";
////////////////////

$qq = " SELECT DISTINCT pltb.EspecimenID AS WikiEspecimenID, pltb.PlantaID AS WikiPlantaID, CONCAT(
colpessoa.SobreNome,'_',IF(pltb.Prefixo IS NULL OR pltb.Prefixo='','',CONCAT(pltb.Prefixo,'-')),pltb.Number,IF(pltb.Sufix IS NULL OR pltb.Sufix='','',CONCAT('-',pltb.Sufix))) as IDENTIFICADOR, colpessoa.Abreviacao as COLLECTOR, pltb.Number as NUMBER
"; 

	$idx=0;
	$metadados['idx'.$idx][0] = 'WikiEspecimenID';
	$metadados['idx'.$idx][1] = 'Identificador Amostra do Wiki';
	$idx++;
	$metadados['idx'.$idx][0] = 'WikiPlantaID';
	$metadados['idx'.$idx][1] = 'Identificador da Planta marcada do Wiki, quando houver';
	$idx++;
	$metadados['idx'.$idx][0] = 'IDENTIFICADOR';
	$metadados['idx'.$idx][1] = 'Coletor + número';
	$idx++;
	$metadados['idx'.$idx][0] = 'COLLECTOR';
	$metadados['idx'.$idx][1] = 'Nome do coletor da amostra';
	$idx++;
	$metadados['idx'.$idx][0] = 'NUMBER';
	$metadados['idx'.$idx][1] = 'Número de coleta do coletor';
	$idx++;
	if (!empty($basvar['datacol'])) {
		$qq .= ", IF (pltb.Ano>0,CONCAT(pltb.Ano,'-',pltb.Mes,'-',pltb.Day),'SemData') as DATA_COLETA, IF (pltb.Day>0,pltb.Day,'')  as COLLDD, IF (pltb.Mes>0,pltb.Mes,'')  as COLLMM, IF (pltb.Ano>0,pltb.Ano,'')  as COLLYY";
		$metadados['idx'.$idx][0] = 'DATA_COLETA';
		$metadados['idx'.$idx][1] = 'Data em que a amostra foi coletada';
		$idx++;
		$metadados['idx'.$idx][0] = 'COLLDD';
		$metadados['idx'.$idx][1] = 'Dia em que a amostra foi coletada';
		$idx++;
		$metadados['idx'.$idx][0] = 'COLLMM';
		$metadados['idx'.$idx][1] = 'Mes em que a amostra foi coletada';
		$idx++;
		$metadados['idx'.$idx][0] = 'COLLYY';
		$metadados['idx'.$idx][1] = 'Ano em que a amostra foi coletada';
		$idx++;

	}
	if (!empty($basvar['addcoll'])) {
		$qq .=", addcolldescr(AddColIDS) as ADDCOLL";
		$metadados['ADDCOLL'] = 'Nome dos demais coletores';
		$metadados['idx'.$idx][0] = 'ADDCOLL';
		$metadados['idx'.$idx][1] = 'Nome dos demais coletores da amostra';
		$idx++;
	}

	
	if (!empty($basvar['registroINPA'])) {
		if (!empty($processoid)) {
			$qq .=", prcc.".$herbariumsigla;
		} else {
			$qq .=", pltb.INPA_ID as ".$herbariumsigla;
		}
		//$metadados['INPA_NUM'] = 'Número de registro do herbário INPA';
		$metadados['idx'.$idx][0] = $herbariumsigla;
		$metadados['idx'.$idx][1] = 'Número de registro do herbário '.$herbariumsigla;
		$idx++;
	}
	if (!empty($basvar['herbarios'])) {
		$qq .=", pltb.Herbaria as HERBARIA";
		$metadados['idx'.$idx][0] = 'HERBARIA';
		$metadados['idx'.$idx][1] ='Herbários onde as amostras estão depositadas';
		$idx++;
	}



	if (!empty($basvar['nomenoautor'])) {
		$metadados['idx'.$idx][0] = 'NOME';
		$metadados['idx'.$idx][1] = 'Taxonomia no nivel de identificacao sem autores';
		$idx++;
		$qq .=", 
gettaxonname(pltb.DetID,1,0) as NOME";
//getidentidade(pltb.DetID, 0, 0, 0,0, 1) as NOME";
		//$qq .=", IF(iddet.InfraEspecieID>0,CONCAT(gentb.Genero,' ',sptb.Especie,' ',infsptb.InfraEspecie),IF(iddet.EspecieID>0,CONCAT(gentb.Genero,' ',sptb.Especie),IF(iddet.GeneroID>0,gentb.Genero,famtb.Familia))) as NOME";
	} 
	//getidentidade(identid INT(10), morftp BOOLEAN, autors BOOLEAN, famonly BOOLEAN, genonly BOOLEAN, modif BOOLEAN)
	if (!empty($basvar['nomeautor'])) {
		$metadados['idx'.$idx][0] = 'NOME_AUTOR';
		$metadados['idx'.$idx][1] = 'Taxonomia no nivel de identificacao, se em nivel de especie ou de infraespecie, nome dos autores incluidos';
		$idx++;
		$qq .=", 
gettaxonname(pltb.DetID,1,1) as NOME_AUTOR";
//getidentidade(pltb.DetID, 0, 1, 0,0, 1) as NOMEeAUTOR";
		//$qq .=", IF(iddet.InfraEspecieID>0,CONCAT(gentb.Genero,' ',sptb.Especie,' ',sptb.EspecieAutor,' ',infsptb.InfraEspecieNivel,' ',infsptb.InfraEspecie,' ',infsptb.InfraEspecieAutor),IF(iddet.EspecieID>0,CONCAT(gentb.Genero,' ',sptb.Especie,' ',sptb.EspecieAutor),IF(iddet.GeneroID>0,gentb.Genero,famtb.Familia))) as NOMEeAUTOR";
	}

	if (!empty($basvar['taxacompleto'])) {
			$metadados['idx'.$idx][0] = 'FAMILY';
			$metadados['idx'.$idx][1] = 'Familia botanica';
			$idx++;
			$metadados['idx'.$idx][0] = 'GENUS';
			$metadados['idx'.$idx][1] = 'Genero botanico, onde Indet= indeterminado nesse nivel';
			$idx++;
			$metadados['idx'.$idx][0] = 'SP1';
			$metadados['idx'.$idx][1] = 'Epiteto da especie';
			$idx++;
			$metadados['idx'.$idx][0] = 'AUTHOR1';
			$metadados['idx'.$idx][1] =  'Autoridade do nome da especie';
			$idx++;
			$metadados['idx'.$idx][0] = 'RANK1';
			$metadados['idx'.$idx][1] = 'Categoria de nivel infra-especifico, variedade, subespecie, forma, etc.';
			$idx++;
			$metadados['idx'.$idx][0] = 'SP2';
			$metadados['idx'.$idx][1] = 'Nome da categoria infra-especifica';
			$idx++;
			$metadados['idx'.$idx][0] = 'AUTHOR2';
			$metadados['idx'.$idx][1] =  'Autoridade do nome da categoria infra-especifica';
			$idx++;
			$metadados['idx'.$idx][0] = 'CF';
			$metadados['idx'.$idx][1] = "Modificadores de nome como aff. cf. vel. aff. etc, indicado por quem fez a identificacao";
			$idx++;
			$metadados['idx'.$idx][0] = 'DETBY';
			$metadados['idx'.$idx][1] = "Nome da pessoa que fez a identificacao";
			$idx++;
			$metadados['idx'.$idx][0] = 'DETDD';
			$metadados['idx'.$idx][1] = "Dia da data de identificacao";
			$idx++;
			$metadados['idx'.$idx][0] = 'DETMM';
			$metadados['idx'.$idx][1] = 'Mes da data de identificacao';
			$idx++;
			$metadados['idx'.$idx][0] = 'DETYY';
			$metadados['idx'.$idx][1] = "Ano da data de identificacao";
			$idx++;
			$qq .=", 
famtb.Familia as FAMILY, 
gentb.Genero as GENUS, 
sptb.Especie as SP1, 
IF(sptb.Morfotipo=1,'',sptb.EspecieAutor) as AUTHOR1, 
infsptb.InfraEspecieNivel as RANK1, 
infsptb.InfraEspecie as SP2, 
IF(infsptb.Morfotipo=1,'',infsptb.InfraEspecieAutor) as AUTHOR2, 
iddet.DetModifier as CF, 
detpessoa.Abreviacao as DETBY, 
IF(DAY(iddet.DetDate)>0,DAY(iddet.DetDate),IF(iddet.DetDateDD>0,iddet.DetDateDD,'')) as DETDD, 
IF(MONTH(iddet.DetDate)>0,MONTH(iddet.DetDate),IF(iddet.DetDateMM>0,iddet.DetDateMM,'')) as DETMM,
IF(YEAR(iddet.DetDate)>0,YEAR(iddet.DetDate),IF(iddet.DetDateYY>0,iddet.DetDateYY,'')) as DETYY";
	}

	if (!empty($basvar['localidade'])) {
		$metadados['idx'.$idx][0] = 'COUNTRY';
		$metadados['idx'.$idx][1] = "Nome do pais";
		$idx++;
		$metadados['idx'.$idx][0] = 'MAJORAREA';
		$metadados['idx'.$idx][1] = "Nome da primeira subdivisao administrativa do pais, provincias, estados, departamentos, cantoes, dependendo do pais";
		$idx++;
		$metadados['idx'.$idx][0] = 'MINORAREA';
		$metadados['idx'.$idx][1] = "Nome da subdivisao de MAJORAREA, geralmente os municipios ou condados";
		$idx++;
		$metadados['idx'.$idx][0] = 'GAZETTEER_CURTO';
		$metadados['idx'.$idx][1] = "Primeira divisão de MINORAREA";
		$idx++;
		$metadados['idx'.$idx][0] = 'GAZETTEER_COMPLETA';
		$metadados['idx'.$idx][1] = "As demais subdivisoes de MINORAREA. Quando houver mais de uma, concatenadas de forma hierarquica do maior para o menor";
		$idx++;
		$metadados['idx'.$idx][0] = 'GAZETTEER_SPECIFIC';
		$metadados['idx'.$idx][1] = "A localidade mais especifica da planta coletada";
		$idx++;
		$qq .= ",  
localidadefields(pltb.GazetteerID, pltb.GPSPointID,pltb.MunicipioID, pltb.ProvinceID,pltb.CountryID,'COUNTRY')  as COUNTRY,
localidadefields(pltb.GazetteerID, pltb.GPSPointID,pltb.MunicipioID, pltb.ProvinceID,pltb.CountryID, 'MINORAREA')  as MINORAREA, 
localidadefields(pltb.GazetteerID, pltb.GPSPointID,pltb.MunicipioID, pltb.ProvinceID,pltb.CountryID, 'MAJORAREA')  as MAJORAREA,
localidadefields(pltb.GazetteerID, pltb.GPSPointID,pltb.MunicipioID, pltb.ProvinceID,pltb.CountryID, 'GAZfirstPARENT')  as GAZETTEER_CURTO,
localidadefields(pltb.GazetteerID, pltb.GPSPointID,pltb.MunicipioID, pltb.ProvinceID,pltb.CountryID, 'GAZETTEER')  as GAZETTEER_COMPLETA,
localidadefields(pltb.GazetteerID, pltb.GPSPointID,pltb.MunicipioID, pltb.ProvinceID,pltb.CountryID, 'GAZETTEER_SPEC')  as GAZETTEER_SPECIFIC";
	}
	if (!empty($basvar['gps'])) {
		$metadados['idx'.$idx][0] = 'LONGITUDE';
		$metadados['idx'.$idx][1] = "Longitude em decimos de grau, com valores negativos para posicoes W e valores positivos para posicoes E";
		$idx++;
		$metadados['idx'.$idx][0] = 'LATITUDE';
		$metadados['idx'.$idx][1] = "Latitude em decimos de grau, com valores negativos para posicoes S e valores positivos para posicoes N";
		$idx++;
		$metadados['idx'.$idx][0] = 'LATDG';
		$metadados['idx'.$idx][1] = "Graus de Latitude";
		$idx++;
		$metadados['idx'.$idx][0] = 'LATMM';
		$metadados['idx'.$idx][1] = "Minutos de Latitude";
		$idx++;
		$metadados['idx'.$idx][0] = 'LATSS';
		$metadados['idx'.$idx][1] = "Segundos de Latitude";
		$idx++;
		$metadados['idx'.$idx][0] = 'LATNS';
		$metadados['idx'.$idx][1] = "Norte e Sul de Latitude";
		$idx++;
		$metadados['idx'.$idx][0] = 'LONGDG';
		$metadados['idx'.$idx][1] = "Graus de Longitude";
		$idx++;
		$metadados['idx'.$idx][0] = 'LONGMM';
		$metadados['idx'.$idx][1] = "Minutos de Longitude";
		$idx++;
		$metadados['idx'.$idx][0] = 'LONGSS';
		$metadados['idx'.$idx][1] = "Segundos de Longitude";
		$idx++;
		$metadados['idx'.$idx][0] = 'LONGEW';
		$metadados['idx'.$idx][1] = "Leste e Oeste de Longitude";
		$idx++;
		$metadados['idx'.$idx][0] = 'ALTITUDE';
		$metadados['idx'.$idx][1] = "Altitude em m sobre o nivel do mar";
		$idx++;
		$qq .= ", 
getlatlongdms(pltb.Latitude+0, pltb.Longitude+0, pltb.GPSPointID, pltb.GazetteerID, pltb.MunicipioID, pltb.ProvinceID, CountryID, 1,5)  as LATITUDE,
getlatlongdms(pltb.Latitude+0, pltb.Longitude+0, pltb.GPSPointID, pltb.GazetteerID, pltb.MunicipioID, pltb.ProvinceID,pltb.CountryID, 0,5)  as LONGITUDE,
getlatlongdms(pltb.Latitude+0, pltb.Longitude+0, pltb.GPSPointID, pltb.GazetteerID, pltb.MunicipioID, pltb.ProvinceID, CountryID, 1,1)  as LATDG,
getlatlongdms(pltb.Latitude+0, pltb.Longitude+0, pltb.GPSPointID, pltb.GazetteerID, pltb.MunicipioID, pltb.ProvinceID,pltb.CountryID, 1,2)  as LATMM,
getlatlongdms(pltb.Latitude+0, pltb.Longitude+0, pltb.GPSPointID, pltb.GazetteerID, pltb.MunicipioID, pltb.ProvinceID,pltb.CountryID, 1,3) as LATSS,
getlatlongdms(pltb.Latitude+0, pltb.Longitude+0, pltb.GPSPointID, pltb.GazetteerID, pltb.MunicipioID, pltb.ProvinceID,pltb.CountryID, 1,4) as LATNS,
getlatlongdms(pltb.Latitude+0, pltb.Longitude+0, pltb.GPSPointID, pltb.GazetteerID, pltb.MunicipioID, pltb.ProvinceID,pltb.CountryID, 0,1)  as LONGDG,
getlatlongdms(pltb.Latitude+0, pltb.Longitude+0, pltb.GPSPointID, pltb.GazetteerID, pltb.MunicipioID, pltb.ProvinceID,pltb.CountryID, 0,2) as LONGMM,
getlatlongdms(pltb.Latitude+0, pltb.Longitude+0, pltb.GPSPointID, pltb.GazetteerID, pltb.MunicipioID, pltb.ProvinceID,pltb.CountryID, 0,3)  as LONGSS,
getlatlongdms(pltb.Latitude+0, pltb.Longitude+0, pltb.GPSPointID, pltb.GazetteerID, pltb.MunicipioID, pltb.ProvinceID,pltb.CountryID, 0,4)  as LONGEW,
getaltitude(pltb.Altitude+0, pltb.GPSPointID,pltb.GazetteerID) as ALTITUDE";
	}

	$metadados['idx'.$idx][0] = 'TAG_PlantaMarcada';
	$metadados['idx'.$idx][1] = 'Número da placa da planta, caso seja uma exsicata de uma planta marcada';
	$idx++;
	$qq .=", IF(pltb.PlantaID>0,plspectb.PlantaTag,'') as TAG_PlantaMarcada";

	if ($daptraitid>0) {
	$qq .=", traitvaluespecs(".$daptraitid.",pltb.PlantaID,pltb.EspecimenID,'mm',0,1) as DAPmm";
		$metadados['idx'.$idx][0] = "DAPmm";
		$metadados['idx'.$idx][1] = "DAP em mm, o valor máximo se houver mais de 1";
		$idx++;
	}
	if ($alturatraitid>0) {
	$qq .=", traitvaluespecs(".$alturatraitid.",pltb.PlantaID,pltb.EspecimenID,'m',0,1) as ALTURAm";
		$metadados['idx'.$idx][0] = "ALTURAm";
		$metadados['idx'.$idx][1] = "ALTURA em metros, o valor máximo se houver mais de 1";
		$idx++;
	}
	if ($habitotraitid>0) {
		$qq .= ", (traitvaluespecs(".$habitotraitid.", pltb.PlantaID, pltb.EspecimenID,'', 0, 1)) as HABITO";
		$metadados['idx'.$idx][0] = "HABITO";
		$metadados['idx'.$idx][1] = "Forma de vida da planta";
		$idx++;
	}
	if ($traitfertid>0) {
		$qq .= ", (traitvaluespecs(".$traitfertid.", 0, pltb.EspecimenID,'', 0, 1)) as FERTILIDADE";
		$metadados['idx'.$idx][0] = "FERTILIDADE";
		$metadados['idx'.$idx][1] = "Estado do especímene";
		$idx++;
	}
	if ($traitsilica>0) {
		$qq .= ", checkoleo(pltb.EspecimenID,pltb.PlantaID,".$traitsilica.",'oleo') AS OLEO";
		$metadados['idx'.$idx][0] = "OLEO";
		$metadados['idx'.$idx][1] = "Se foi extraída uma amostra de óleo";
		$idx++;
	}
	if ($usarmodelo==1) {
		$quq .= ", '' as NOTAS";
	} else {
			if ($monidata==1) {
				$quq = ", labeldescricao(pltb.EspecimenID,pltb.PlantaID,".($formnotas+0).",1,0) as NOTAS";
			} else {
				$quq = ", labelnotes_nomoni(pltb.EspecimenID,0,".($formnotas+0).",1,0) as NOTAS";
			}
	}

		$metadados['idx'.$idx][0] = "NOTAS";
		$metadados['idx'.$idx][1] = "Campo de notas da amostra, gerado por concatenação da variação ligada à amostra para variáveis selecionadas pelo usuário";
		$idx++;
		$qq .= $quq;
		//echo $quq;

	if (!empty($basvar['habitat'])) {
		$qq .= ", habitaclasse(pltb.HabitatID) AS  HABITAT_CLASSE";
		$metadados['idx'.$idx][0] = "HABITAT_CLASSE";
		$metadados['idx'.$idx][1] = "Classe de habitat ou ambiente onde foi coletada a planta";
		$idx++;
	}
	if ($formhabitatdesc>0) {
		$qq .= ", habitatstring(pltb.HabitatID, ".$formhabitatdesc.", TRUE,FALSE)  as HABITAT_NOTAS";
		$metadados['idx'.$idx][0] = "HABITAT_NOTAS";
		$metadados['idx'.$idx][1] = "Notas do ambiente da planta";
		$idx++;
	}
	if ($formhabitat>0) {
			$qu = "SELECT tr.TraitID,tr.TraitTipo,tr.TraitName,tr.TraitUnit,tr.TraitDefinicao,prt.TraitName as ParentName FROM Traits as tr INNER JOIN Traits as prt ON tr.ParentID=prt.TraitID JOIN FormulariosTraitsList ON FormulariosTraitsList.TraitID=tr.TraitID  WHERE FormulariosTraitsList.FormID=".$formhabitat."  ORDER BY FormulariosTraitsList.Ordem";
			$ruu = mysql_query($qu,$conn);
			$hi=0;
			while($rwu = mysql_fetch_assoc($ruu)) {
				$tid = $rwu['TraitID'];
				$ttipo = $rwu['TraitTipo'];
				$ttname = trim($rwu['TraitName']);
				$tdefini = trim($rwu['TraitDefinicao']);
				$tascol = $rwu['ParentName']."_".$rwu['TraitName'];
				$val =  RemoveAcentos($tascol);
				$val = str_replace("  ", " ", $val);
				$val = str_replace("  ", " ", $val);
				$val = str_replace(" ", "_", $val);
				$symb = array(" ", ".",'/',"-",")","(",";");
				$val  = str_replace($symb, "", $val);
				$val = str_replace(" ", "", $val);
				$tascol = strtoupper($val)."_HABITAT";
				$metadados['idx'.$idx][0] = $tascol;
				if ($ttipo=='Variavel|Quantitativo') {
						$tti = explode("|",$ttipo);
						$tdef = $ttname.". (".$tdefini."). Variavel ".$tti[1].". Unidade de medida padrao ".$rwu['TraitUnit'].".";
						$tdef = str_replace("..",".",$tdef);
						$tdef = str_replace(".)",")",$tdef);
						$metadados['idx'.$idx][1] = $tdef;
						$idx++;
						$metadados['idx'.$idx][0] = $tascol."_UNIT";
						$metadados['idx'.$idx][1] = "Unidade da medicao da variavel $tascol";
						$idx++;
						$qq .= ", habitatvariation($tid,pltb.HabitatID,0,$habmean) AS ".$tascol.", habitatvariation($tid,pltb.HabitatID,1,$habmean) AS  ".$tascol."_UNIT";
				} else {
					$tti = explode("|",$ttipo);
					$tdef = $ttname.". (".$tdefini."). Variavel ".$tti[1].".";
					$tdef = str_replace("..",".",$tdef);
					$tdef = str_replace(".)",")",$tdef);
					$metadados['idx'.$idx][1] = $tdef;
					$idx++;
					$qq .= ", habitatvariation($tid,pltb.HabitatID,0,$habmean) AS ".$tascol;
				}
			}
	}
	if ($formvariables>0) {
		if ($formvarmean==1) { $fmean = 1; } else { $fmean=0;}
		$qu = "SELECT tr.TraitID,tr.TraitTipo,tr.TraitName,tr.TraitUnit,tr.TraitDefinicao,prt.TraitName as ParentName FROM Traits as tr INNER JOIN Traits as prt ON tr.ParentID=prt.TraitID JOIN FormulariosTraitsList ON FormulariosTraitsList.TraitID=tr.TraitID  WHERE FormulariosTraitsList.FormID=".$formvariables."  ORDER BY FormulariosTraitsList.Ordem";
		$ruw = mysql_query($qu,$conn);
		while($rwuw = mysql_fetch_assoc($ruw)) {
			$tid = $rwuw['TraitID'];
			$ttipo = $rwuw['TraitTipo'];
			$ttname = trim($rwuw['TraitName']);
			$tdefini = trim($rwuw['TraitDefinicao']);
			$tascol = $rwuw['ParentName']."_".$rwuw['TraitName'];
			$val =  RemoveAcentos($tascol);
			$val = str_replace("  ", " ", $val);
			$val = str_replace("  ", " ", $val);
			$val = str_replace(" ", "_", $val);
			$symb = array(" ", ".",'/',"-",")","(",";");
			$val  = str_replace($symb, "", $val);
			$val = str_replace(" ", "", $val);
			$tascol = strtoupper($val);
			$metadados['idx'.$idx][0] = $tascol;
			if ($ttipo=='Variavel|Quantitativo') {
				$tti = explode("|",$ttipo);
				$tdef = $ttname.". (".$tdefini."). Variavel ".$tti[1].". Unidade de medida padrao ".$rwuw['TraitUnit'].".";
				$tdef = str_replace("..",".",$tdef);
				$tdef = str_replace(".)",")",$tdef);
				$metadados['idx'.$idx][1] = $tdef;
				$idx++;
				$metadados['idx'.$idx][0] = $tascol."_UNIT";
				$metadados['idx'.$idx][1] = "Unidade da medicao da variavel $tascol";
				$idx++;
				$qq .= ", traitvariation_specimens($tid,pltb.EspecimenID,0,$fmean) AS ".$tascol.", traitvariation_specimens($tid,pltb.EspecimenID,1,$fmean) AS  ".$tascol."_UNIT";
			} else {
				$tti = explode("|",$ttipo);
				$tdef = $ttname.". (".$tdefini."). Variavel ".$tti[1].".";
				$tdef = str_replace("..",".",$tdef);
				$tdef = str_replace(".)",")",$tdef);
				$metadados['idx'.$idx][1] = $tdef;
				$idx++;
				$qq .= ", traitvariation_specimens($tid,pltb.EspecimenID,0,0) AS ".$tascol;
				//ECHO ", traitvariation_specimens($tid,pltb.EspecimenID,0,0) AS ".$tascol."<br />";
			}
		}
	}

	if (!empty($basvar['vernacular'])) {
		$qq .= ", vernaculars(pltb.VernacularIDS) as NOME_VULGAR";
		$metadados['idx'.$idx][0] = "NOME_VULGAR";
		$metadados['idx'.$idx][1] = "Nome vulgar registrado para a amostra";
		$idx++;
	}
	//marcado por 
	if (!empty($basvar['projeto'])) {
		$qq .= ", projetostringbrahmsnovo(pltb.EspecimenID,0) as PROJETO";
		$metadados['idx'.$idx][0] = "PROJETO";
		$metadados['idx'.$idx][1] = 'Projeto a que se refere o trabalho';
		$idx++;
	}
	$qq .= " FROM Especimenes as pltb ";
	$qqbrahms .= " FROM Especimenes as pltb ";
	if (!empty($processoid)) {
		$qq .= " LEFT JOIN ProcessosLIST as prcc ON pltb.EspecimenID=prcc.EspecimenID";
		$qqbrahms .= " LEFT JOIN ProcessosLIST as prcc ON pltb.EspecimenID=prcc.EspecimenID";
	}
	$qq .= " LEFT JOIN Plantas as plspectb ON pltb.PlantaID=plspectb.PlantaID LEFT JOIN Pessoas as colpessoa ON pltb.ColetorID=colpessoa.PessoaID";
	$qqbrahms .= " LEFT JOIN Plantas as plspectb ON pltb.PlantaID=plspectb.PlantaID LEFT JOIN Pessoas as colpessoa ON pltb.ColetorID=colpessoa.PessoaID";

	if (!empty($basvar['nomenoautor']) || !empty($basvar['nomeautor']) || !empty($basvar['taxacompleto'])) {
		$qq .= "
LEFT JOIN Identidade as iddet ON pltb.DetID=iddet.DetID 
LEFT JOIN Tax_InfraEspecies as infsptb ON iddet.InfraEspecieID=infsptb.InfraEspecieID 
LEFT JOIN Tax_Especies as sptb ON iddet.EspecieID=sptb.EspecieID 
LEFT JOIN Tax_Generos as gentb ON iddet.GeneroID=gentb.GeneroID  
LEFT JOIN Tax_Familias as famtb ON iddet.FamiliaID=famtb.FamiliaID 
LEFT JOIN Pessoas as detpessoa ON detpessoa.PessoaID=iddet.DetbyID ";
		$qqbrahms .= "
LEFT JOIN Identidade as iddet ON pltb.DetID=iddet.DetID 
LEFT JOIN Tax_InfraEspecies as infsptb ON iddet.InfraEspecieID=infsptb.InfraEspecieID 
LEFT JOIN Tax_Especies as sptb ON iddet.EspecieID=sptb.EspecieID 
LEFT JOIN Tax_Generos as gentb ON iddet.GeneroID=gentb.GeneroID  
LEFT JOIN Tax_Familias as famtb ON iddet.FamiliaID=famtb.FamiliaID 
LEFT JOIN Pessoas as detpessoa ON detpessoa.PessoaID=iddet.DetbyID ";
		} 
	$qqff0 = '';
	if ($filtro>0) {
		$qqff = " JOIN FiltrosSpecs as fl ON pltb.EspecimenID=fl.EspecimenID WHERE fl.FiltroID=".$filtro;
	} 
	else {
		if (!empty($especimenesids)) {
			$specarr = explode(";",$especimenesids);
			$n = 0;
			foreach ($specarr as $vv) {
				if ($n==0) {
					$qqff = " WHERE pltb.EspecimenID=".$vv;
				} else {
					$qqff .= " OR pltb.EspecimenID=".$vv;
				}
				$n++;
			}
		} 
		elseif (!empty($processoid)) {
				$qqff0 = ' LEFT JOIN ProcessosLIST as prcc ON pltb.EspecimenID=prcc.EspecimenID ';
				$prarr = explode(";",$processoid);
				if (count($prarr)==1) {
					$qqff = " WHERE prcc.EXISTE=1 AND prcc.ProcessoID=".$processoid;
				} else {
					$qqff = " WHERE prcc.EXISTE=1 AND isvalidprocesso(prcc.ProcessoID,'".$processoid."')>0";
				}
				if ($quais==2) {
					$qqff .= " AND prcc.".$herbariumsigla.">0 ";
				}
				if ($quais==3) {
					$qqff .= " AND (prcc.".$herbariumsigla."=0 OR prcc.".$herbariumsigla." IS NULL)";
				}
				if (!empty($ferttoexcl)) {
					//$qqff .= " AND prcc.Fert NOT LIKE '%".$ferttoexcl."%' AND prcc.Fert<>'' AND Fert IS NOT NULL";
				} 
		}
	}
	
	
	//$qqbrahms .= $qqff;
	//$qq .= $qqff;
	$qwhere = $qqff;

//$_SESSION['metadados'] = serialize($metadados);
if ($forbrahms==1) {
		$qq = $qqbrahms;
		mysql_set_charset('latin1', $conn);
		$qblin = "\r\n";
	} else {
		$qblin = "\n";
}

@unlink("temp/".$export_filename);
@unlink("temp/".$export_filename_metadados);


//CONTA REGISTROS
$qz = "SELECT * FROM Especimenes as pltb ".$qqff0." ".$qqff;
$rz = mysql_query($qz,$conn);
$nrecs = mysql_num_rows($rz);
//$perc = 2;
$step=0;

//EXECUTA A QUERY E SALVA LINHA POR LINHA NO ARQUIVO DE DADOS
//echo "<br ><br >aqui 3<br >".$qz."<br><br ><br >";
while($linha = mysql_fetch_assoc($rz)) {
//echo "<br ><br >aqui 4<br >".$qq."<br><br ><br >";
	$qqq = $qq." WHERE pltb.EspecimenID=".$linha['EspecimenID']; 
	//echo $qqq."  aqui<br />";
	$res = @mysql_query($qqq,$conn);
	if ($res) {
		//$nnres = mysql_numrows($res);
		$fieldscount = mysql_num_fields($res);
		//$_SESSION['exportnfields'] = $count;
		//$_SESSION['exportnresult'] = $nnres;
		if ($step==0) {
			$acabeca = array();
			$fh = fopen("temp/".$export_filename, 'w') or die("nao foi possivel gerar o arquivo");
			$count = $fieldscount;
			$header = '';
			for ($i = 0; $i < $count; $i++){
				$cl = mysql_field_name($res, $i);
				$acabeca[] = $cl;
				if ($cl!='WikiPlantaID' || $forbrahms<>1) {
					if ($i<($count-1)) {
						$header .=  '"'. mysql_field_name($res, $i).'"'."\t";
					} else {
						$header .=  '"'. mysql_field_name($res, $i).'"';
					}
				}
			}
			$header .= $qblin;
			fwrite($fh, $header);
		}
		$rsw = mysql_fetch_row($res);
		$line = '';
		$nff  = count($rsw);
		$nii = 1;
		foreach($rsw as $value){
			if ($acabeca[$coluna]!='WikiPlantaID' || $forbrahms<>1) {
				if ($acabeca[$coluna]=='plantdesc' && $forbrahms==1 && $usarmodelo==1) {
					$aspecid = $rsw[0];
					$aplantaid = $rsw[1];
					$value = formulariomodelo($aspecid,$aplantaid,$formnotas);
					//echo "aqui".$value."<br >";
				} elseif ($acabeca[$coluna]=='NOTAS' && $usarmodelo==1) {
					$aspecid = $rsw[0];
					$aplantaid = $rsw[1];
					$value = formulariomodelo($aspecid,$aplantaid,$formnotas);
				}
				if(!isset($value) || $value == ""){
					$value = "\"\"\t";
				} else {
					//apagar quebra de linhas
					$value = preg_replace( "/\r|\n|\t/", "", $value);

					//important to escape any quotes to preserve them in the data.
					$value = str_replace('"', '""', $value);
					//$value = trim($value);
					//if (empty($value) || $value=="") { $value= "NA"; }
					
					//needed to encapsulate data in quotes because some data might be multi line.
					//the good news is that numbers remain numbers in Excel even though quoted.
					if ($nii<$nff) {
						$value = '"' . $value . '"' . "\t";
					} else {
						$value = '"' . $value . '"';
					}
				}
				$nii++;
				$line .= $value;
				}
			}
		$lin = trim($line).$qblin;
		fwrite($fh, $lin);
		
		//SAVE LOG
		$total = $nrecs;
		$perc = ($step/$total)*9.9;
		$qnu = "UPDATE `".$progesstable."` SET percentage=".$perc; 
		//echo $qnu."<br >";
		mysql_query($qnu);
		session_write_close();
		$step++;
	}
}
fclose($fh);

//$metadados = unserialize($_SESSION['metadados']);
$fh = fopen("temp/".$export_filename_metadados, 'w') or die("nao foi possivel gerar o arquivo");
$stringData = "COLUNA\tDEFINICAO"; 
if (!$forbrahms==1) {
	foreach ($metadados as $kk => $vv) {
		$stringData = $stringData."\n".$vv[0]."\t".$vv[1];
	}
} 
else {
	$stringData .= "\n\nPlanilha para o Brahms, não tem metadados ainda"; 
}
fwrite($fh, $stringData);
fclose($fh);

$qnu = "UPDATE `".$progesstable."` SET percentage=10"; 
mysql_query($qnu);
$message=  $nrecs.";".$fieldscount;
echo $message;
session_write_close();
?>