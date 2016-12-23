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

$dd = @unserialize($_SESSION['destvararray']);
@extract($dd);

//echopre($dd);
//echopre($ppost);
$lixo=10;
if ($lixo==10) {
if (!isset($prepared)) {
	$_SESSION['destvararray'] = serialize($ppost);
	unset($_SESSION['metadados']);
	unset($metadados);
	unset($_SESSION['qq']);
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
	
		$qq = " SELECT pltb.PlantaID AS WikiPlantaID, IF((pltb.InSituExSitu='' OR pltb.InSituExSitu IS NULL),pltb.PlantaTag,IF(pltb.InSituExSitu LIKE 'Insitu',CONCAT('JB-X-',pltb.PlantaTag),CONCAT('JB-N-',pltb.PlantaTag))) as TAG_NUM"; 
		$qq .= ", traitvalueplantas(".$statustraitid.",pltb.PlantaID, '', 0,0 ) as STATUS";
		$idx=0;
		if (!empty($basvar['nomenoautor'])) {
			$metadados['idx'.$idx][0] = 'NOME';
			$metadados['idx'.$idx][1] = 'Taxonomia no nivel de identificacao sem autores';
			$idx++;
			$qq .=", IF(iddet.InfraEspecieID>0,CONCAT(gentb.Genero,' ',spectb.Especie,' ',infsptb.InfraEspecie),IF(iddet.EspecieID>0,CONCAT(gentb.Genero,' ',spectb.Especie),IF(iddet.GeneroID>0,gentb.Genero,famtb.Familia))) as NOME";
		} 
		if (!empty($basvar['nomeautor'])) {
			$metadados['idx'.$idx][0] = 'NOME_AUTOR';
			$metadados['idx'.$idx][1] = 'Taxonomia no nivel de identificacao, se em nivel de especie ou de infraespecie, nome dos autores incluidos';
			$idx++;
			$qq .=", IF(iddet.InfraEspecieID>0,CONCAT(gentb.Genero,' ',spectb.Especie,' ',spectb.EspecieAutor,' ',infsptb.InfraEspecieNivel,' ',infsptb.InfraEspecie,' ',infsptb.InfraEspecieAutor),IF(iddet.EspecieID>0,CONCAT(gentb.Genero,' ',spectb.Especie,' ',spectb.EspecieAutor),IF(iddet.GeneroID>0,gentb.Genero,famtb.Familia))) as NOMEeAUTOR";
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
				
			$qq .=", famtb.Familia as FAMILY, gentb.Genero as GENUS, spectb.Especie as SP1, spectb.EspecieAutor as AUTHOR1, infsptb.InfraEspecieNivel as RANK1, infsptb.InfraEspecie as SP2, infsptb.InfraEspecieAutor as AUTHOR2, iddet.DetModifier as CF, detpessoa.Abreviacao as DETBY, DAY(iddet.DetDate) as DETDD, MONTH(iddet.DetDate) as DETMM, YEAR(iddet.DetDate) as DETYY";
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
			$metadados['idx'.$idx][0] = 'GAZETTEER';
			$metadados['idx'.$idx][1] = "As demais subdivisoes de MINORAREA. Quando houver mais de uma, concatenadas de forma hierarquica do maior para o menor";
			$idx++;
			$metadados['idx'.$idx][0] = 'GAZETTEER2';
			$metadados['idx'.$idx][1] = "A localidade mais especifica da planta";
			$idx++;
			$metadados['idx'.$idx][0] = 'WikiGazetteerID';
			$metadados['idx'.$idx][1] = "ID de GAZETTEER2 na base de dados";
			$idx++;
			$metadados['idx'.$idx][0] = 'WikiGazetteerParentID';
			$metadados['idx'.$idx][1] = "ID da Localidade a qual pertence GAZETTEER2 na base de dados, 0 se não existir!";
			$idx++;
			//$qq .= ",  Country as COUNTRY, Province as MAJORAREA, Municipio as MINORAREA, IF(pltb.GPSPointID>0,gazgps.PathName,IF(pltb.GazetteerID>0,gaz.PathName,'')) as GAZETTEER, IF(pltb.GPSPointID>0,TRIM(CONCAT(gazgps.GazetteerTIPOtxt,' ',gazgps.Gazetteer)),IF(pltb.GazetteerID>0,TRIM(CONCAT(gaz.GazetteerTIPOtxt,' ',gaz.Gazetteer)),'')) as GAZETTEER2";
			$qq .= ",  Country as COUNTRY, Province as MAJORAREA, Municipio as MINORAREA, IF(pltb.GPSPointID>0,gazgps.PathName,IF(pltb.GazetteerID>0,gaz.PathName,'')) as GAZETTEER, IF(pltb.GPSPointID>0,TRIM(gazgps.Gazetteer),IF(pltb.GazetteerID>0,TRIM(gaz.Gazetteer),'')) as GAZETTEER2,  IF(pltb.GPSPointID>0,gazgps.GazetteerID,IF(pltb.GazetteerID>0,gaz.GazetteerID,0)) AS WikiGazetteerID,  IF(pltb.GPSPointID>0,gazgps.ParentID,IF(pltb.GazetteerID>0,gaz.ParentID,0)) AS WikiGazetteerParentID";
		}
		if (!empty($basvar['gps'])) {
			$metadados['idx'.$idx][0] = 'LONGITUDE';
			$metadados['idx'.$idx][1] = "Longitude em decimos de grau, com valores negativos para posicoes W e valores positivos para posicoes E";
			$idx++;
			$metadados['idx'.$idx][0] = 'LATITUDE';
			$metadados['idx'.$idx][1] = "Latitude em decimos de grau, com valores negativos para posicoes S e valores positivos para posicoes N";
			$idx++;
			$metadados['idx'.$idx][0] = 'ALTITUDE';
			$metadados['idx'.$idx][1] = "Altitude em m sobre o nivel do mar";
			$idx++;
			$metadados['idx'.$idx][0] = 'COORD_PRECISION';
			$metadados['idx'.$idx][1] = "Precisao das coordenadas, onde GPS_planta indica que a coordenada e do individuo; GPS quando for a coordenada de um ponto de GPS da proximidade do individuo; Localidade indica que a coordenada se refere a uma das localidades em GAZETTEER; Municipio e a coordenada de MINORAREA";
			$idx++;
			$qq .= ", IF(ABS(pltb.Longitude)>0,pltb.Longitude,IF(pltb.GPSPointID>0,gpspt.Longitude,IF(gaz.Longitude<>0,gaz.Longitude,muni.Longitude))) as LONGITUDE, IF(ABS(pltb.Longitude)>0,pltb.Latitude,IF(pltb.GPSPointID>0,gpspt.Latitude,IF(gaz.Longitude<>0,gaz.Latitude,muni.Latitude))) as LATITUDE, IF(ABS(pltb.Longitude)>0,pltb.Altitude,IF(pltb.GPSPointID>0,gpspt.Altitude,IF(gaz.Longitude<>0,gaz.Altitude,''))) as ALTITUDE, IF(ABS(pltb.Longitude)>0,'GPS-planta',IF(pltb.GPSPointID>0,'GPS',IF(gaz.Longitude<>0,'Localidade','Municipio'))) as COORD_PRECISION";
		}
		if (!empty($basvar['xylocal'])) {
				$metadados['idx'.$idx][0] = 'Pos_X';
				$metadados['idx'.$idx][1] = "Posicao X da planta marcada em relacao ao GAZETTEER2 quando este for uma parcela";
				$idx++;
				$metadados['idx'.$idx][0] = 'Pos_Y';
				$metadados['idx'.$idx][1] = "Posicao Y da planta marcada em relacao ao GAZETTEER2 quando este for uma parcela";
				$idx++;
				$metadados['idx'.$idx][0] = 'Pos_LADO';
				$metadados['idx'.$idx][1] = "Para dados da grade do PPBio no PNVirua, indicando o lado das coordenadas X,Y";
				$idx++;
				$metadados['idx'.$idx][0] = 'Referencia';
				$metadados['idx'.$idx][1] = "Qual a referencia dos valores X e Y, em relacao a que vertice da parcela";
				$idx++;
				$metadados['idx'.$idx][0] = 'Pos_DIST';
				$metadados['idx'.$idx][1] = "Distancia em linha reta do GAZETTEER2 a planta marcada, para ser usada em combinacao com Pos_ANGULO, para as situacoes em que GAZETTEER2 e um marco de uma grade, numa trilha, etc.";
				$idx++;
				$metadados['idx'.$idx][0] = 'Pos_ANGULO';
				$metadados['idx'.$idx][1] = "O angulo de direcao da planta a partir do GAZETTEER2 em relacao ao norte magnetico";
				$idx++;	
				$qq .= ",  pltb.X as Pos_X, pltb.Y as Pos_Y, pltb.LADO as Pos_LADO, pltb.Referencia as Pos_REF, pltb.Distancia as Pos_DIST, pltb.Angulo as Pos_ANGULO";
		}
		if ($formvariables>0) {
			if ($meanvalues==1) { $onlymean = 1;} else { $onlymean=0;}
			$qu = "SELECT tr.TraitID,tr.TraitTipo,tr.TraitName,tr.TraitUnit,tr.TraitDefinicao, tr.PathName FROM Traits as tr JOIN FormulariosTraitsList ON FormulariosTraitsList.TraitID=tr.TraitID  WHERE FormulariosTraitsList.FormID=".$formvariables."  ORDER BY FormulariosTraitsList.Ordem";
			$ru = mysql_query($qu,$conn);
			while($rw = mysql_fetch_assoc($ru)) {
				$tid = $rw['TraitID'];
				$ttipo = $rw['TraitTipo'];
				$ttname = trim($rw['TraitName']);
				$ptname = trim($rw['PathName']);
				$ptname = str_replace(" - ".$ttname,"",$ptname);
				$tdefini = trim($rw['TraitDefinicao']);
				//nome da coluna
				$tascol = substr(strtoupper($ttname),0,5).substr(strtolower($ptname),0,3);
				$val =  RemoveAcentos($tascol);
				$val = str_replace("  ", " ", $val);
				$val = str_replace("  ", " ", $val);
				$val = str_replace(" ", "_", $val);
				$symb = array(" ", ".",'/',"-",")","(",";");
				$val  = str_replace($symb, "", $val);
				$val = str_replace(" ", "", $val);
				//$tascol = strtoupper($val);
				$tascol = $val;
				$metadados['idx'.$idx][0] = $tascol;
				if ($ttipo=='Variavel|Quantitativo') {
					$tti = explode("|",$ttipo);
					$tdef = $ttname.". (".$tdefini."). Variavel ".$tti[1].". Unidade de medida padrao ".$rw['TraitUnit'].".";
					$tdef = str_replace("..",".",$tdef);
					$tdef = str_replace(".)",")",$tdef);
					$metadados['idx'.$idx][1] = $tdef;
					$idx++;
					$metadados['idx'.$idx][0] = $tascol."_UNIT";
					$metadados['idx'.$idx][1] = "Unidade da medicao da variavel $tascol";
					$idx++;
					$qq = $qq.", traitvariation_plantas($tid,pltb.PlantaID, 0, $onlymean) AS ".$tascol.", 
					traitvariation_plantas($tid,pltb.PlantaID, 1, 0) AS ".$tascol."_UNIT";
				} else {
					$tti = explode("|",$ttipo);
					$tdef = $ttname.". (".$tdefini."). Variavel ".$tti[1].".";
					$tdef = str_replace("..",".",$tdef);
					$tdef = str_replace(".)",")",$tdef);
					$metadados['idx'.$idx][1] = $tdef;
					$idx++;
					$qq = $qq.", traitvariation_plantas($tid,pltb.PlantaID, 0, 0) AS ".$tascol;
				}	
			}
		}
		$temcenso = count($censos);
		if ($temcenso>0) {
			$censoN = 0;
			foreach ($censos as $cen) {
				if ($cen>0) {
					$qz = "SELECT * FROM Censos WHERE CensoID='".$cen."'";
					$ruz = mysql_query($qz,$conn);
					$rwz = mysql_fetch_assoc($ruz);
					//$cso = substr($rwz['DataInicio'],2,2);
					$cso2 = substr($rwz['DataFim'],0,4);
					$cso = $cso2;
					$qu = "SELECT DISTINCT TraitID FROM Monitoramento JOIN FiltrosSpecs as fl ON Monitoramento.PlantaID=fl.PlantaID WHERE FiltroID=".$filtro." AND CensoID='".$cen."'";
					$ruu = mysql_query($qu,$conn);
					while($rwu = mysql_fetch_assoc($ruu)) {
						$tid = $rwu['TraitID'];
						$qt = "SELECT TraitTipo, TraitName, TraitDefinicao, PathName FROM Traits WHERE TraitID='".$tid."'";
						$rt = mysql_query($qt,$conn);
						$rtw = mysql_fetch_assoc($rt);
						$ttipo = $rtw['TraitTipo'];
						$ttname = trim($rtw['TraitName']);
						$ptname = trim($rtw['PathName']);
						$ptname = str_replace(" - ".$ttname,"",$ptname);
						$tdefini = trim($rtw['TraitDefinicao']);
						//nome da coluna
						//echopre($rtw);
						$tascol = substr(strtoupper($ttname),0,5).substr(strtolower($ptname),0,3);
						$val =  RemoveAcentos($tascol);
						$val = str_replace("  ", " ", $val);
						$val = str_replace("  ", " ", $val);
						$val = str_replace(" ", "_", $val);
						$symb = array(" ", ".",'/',"-",")","(",";");
						$val  = str_replace($symb, "", $val);
						$val = str_replace(" ", "", $val);
						$tascol = $val."".$cso;
						$metadados['idx'.$idx][0] = $tascol;
						if ($ttipo=='Variavel|Quantitativo') {
							$tti = explode("|",$ttipo);
							$tdef = $ttname.". (".$tdefini."). Variavel ".$tti[1].". Unidade de medida padrão ".$rwu['TraitUnit'].".";
							$tdef = str_replace("..",".",$tdef);
							$tdef = str_replace(".)",")",$tdef);
							$metadados['idx'.$idx][1] = $tdef;
							$idx++;
							$metadados['idx'.$idx][0] = $tascol."_UNIT";
							$metadados['idx'.$idx][1] = "Unidade da medição da variável".$tascol;
							$idx++;
							$metadados['idx'.$idx][0] = $tascol."_DATA";
							$metadados['idx'.$idx][1] = "Data da medição da variável.". $tascol;
							$idx++;
							$qq = $qq.", censotrait($tid,pltb.PlantaID, ".$cen.", 0,0 ) AS ".$tascol.", censotrait($tid,pltb.PlantaID, ".$cen.", 0, 1) AS ".$tascol."_UNIT, censotrait($tid,pltb.PlantaID, ".$cen.", 1, 0) AS ".$tascol."_DATE";
						} else {
							$tti = explode("|",$ttipo);
							$tdef = $ttname.". (".$tdefini."). Variável ".$tti[1].".";
							$tdef = str_replace("..",".",$tdef);
							$tdef = str_replace(".)",")",$tdef);
							$metadados['idx'.$idx][1] = $tdef;
							$idx++;
							$metadados['idx'.$idx][0] = $tascol."_DATA";
							$metadados['idx'.$idx][1] = "Data da medição da variável.". $tascol;
							$idx++;
							$qq = $qq.", censotrait($tid,pltb.PlantaID, ".$cen.", 0,0 ) AS ".$tascol.", censotrait($tid,pltb.PlantaID, ".$cen.", 1, 0) AS ".$tascol."_DATA";
						
					}
					}
				}
			}
		}
		if ($formnotes>0) { 
			$qq .= ", traitvariation_nota_plantas($formnotes,pltb.PlantaID) as NOTAS";
			$metadados['idx'.$idx][0] = "NOTAS";
			$metadados['idx'.$idx][1] = "Campo de notas da amostra, gerado por concatenação da variação ligada à amostra para algumas variáveis selecionadas pelo usuário";
			$idx++;
		}
		if (!empty($basvar['habitat'])) {
			$qq = $qq.", habitaclasse(pltb.HabitatID) AS  HABITAT_CLASSE";
			$metadados['idx'.$idx][0] = "HABITAT_CLASSE";
			$metadados['idx'.$idx][1] = "Classe de habitat ou ambiente da planta";
			$idx++;
		}
		if (!isset($habmean)) {
			$habmean=0;
		}
		if ($formhabitat>0) {
				$qu = "SELECT tr.TraitID,tr.TraitTipo,tr.TraitName,tr.TraitUnit,tr.TraitDefinicao FROM Traits as tr JOIN FormulariosTraitsList ON FormulariosTraitsList.TraitID=tr.TraitID  WHERE FormulariosTraitsList.FormID=".$formhabitat."  ORDER BY FormulariosTraitsList.Ordem";
				$ruu = mysql_query($qu,$conn);
				$hi=0;
				while($rwu = mysql_fetch_assoc($ruu)) {
					$tid = $rwu['TraitID'];
					$ttipo = $rwu['TraitTipo'];
					$ttname = trim($rwu['TraitName']);
					$ptname = trim($rwu['PathName']);
					$tdefini = trim($rwu['TraitDefinicao']);
					$ptname = str_replace(" - ".$ttname,"",$ptname);
					$tascol = substr(strtoupper($ttname),0,5).substr(strtolower($ptname),0,3);
					$val =  RemoveAcentos($tascol);
					$val = str_replace("  ", " ", $val);
					$val = str_replace("  ", " ", $val);
					$val = str_replace(" ", "_", $val);
					$symb = array(" ", ".",'/',"-",")","(",";");
					$val  = str_replace($symb, "", $val);
					$val = str_replace(" ", "", $val);
					//$tascol = strtoupper($val)."_HABITAT";
					$tascol = $val;
					$metadados['idx'.$idx][0] = $tascol;
					if ($ttipo=='Variavel|Quantitativo') {
							$tti = explode("|",$ttipo);
							$tdef = $ttname.". (".$tdefini."). Variavel ".$tti[1].". Unidade de medida padrão ".$rwu['TraitUnit'].".";
							$tdef = str_replace("..",".",$tdef);
							$tdef = str_replace(".)",")",$tdef);
							$metadados['idx'.$idx][1] = $tdef;
							$idx++;
							$metadados['idx'.$idx][0] = $tascol."_UNIT";
							$metadados['idx'.$idx][1] = "Unidade de medição da variável ".$tascol;
							$idx++;
							$qq = $qq.", habitatvariation($tid,hablocal.HabitatID,0,$habmean) AS ".$tascol.", habitatvariation($tid,hablocal.HabitatID,1,$habmean) AS  ".$tascol."_UNIT";
					} else {
						$tti = explode("|",$ttipo);
						$tdef = $ttname.". (".$tdefini."). Variável ".$tti[1].".";
						$tdef = str_replace("..",".",$tdef);
						$tdef = str_replace(".)",")",$tdef);
						$metadados['idx'.$idx][1] = $tdef;
						$idx++;
						$qq = $qq.", habitatvariation($tid,hablocal.HabitatID,0,$habmean) AS ".$tascol;
					}
				}
			
		}
		if (!empty($basvar['vernacular'])) {
			$qq .= ", vernaculars(pltb.VernacularIDS) as NOME_VULGAR";
			$metadados['idx'.$idx][0] = "NOME_VULGAR";
			$metadados['idx'.$idx][1] = "Nome vulgar registrado para o indivíduo";
			$idx++;
		}
		//marcado por 
		if (!empty($basvar['taggedby'])) {
			$qq .=", addcolldescr(pltb.TaggedBy) as MARCADO_POR";
			$metadados['idx'.$idx][0] = "MARCADO_POR";
			$metadados['idx'.$idx][1] = 'Nome das pessoas que marcaram as árvores no campo';
			$idx++;
		}
		if (!empty($basvar['datacol'])) {
			$qq .= ", pltb.TaggedDate as DATA_MARCACAO";
			$metadados['idx'.$idx][0] = "DATA_MARCACAO";
			$metadados['idx'.$idx][1] = 'Data da marcação da planta no campo';
			$idx++;
		}
		if (!empty($basvar['projeto'])) {
			$qq .= ", projetostring(pltb.ProjetoID,1,0) as PROJETO";
			$metadados['idx'.$idx][0] = "PROJETO";
			$metadados['idx'.$idx][1] = 'Projeto a que se refere o trabalho';
			$idx++;
		}
		if (!empty($basvar['coletas'])) {
			$qq .= ", colpessoa.Abreviacao as COLLECTOR";
			$qq .= ",  sptb.Number as NUMBER";
			$metadados['idx'.$idx][0] = 'COLLECTOR';
			$metadados['idx'.$idx][1] = 'Nome do coletor da amostra';
			$idx++;
			$metadados['idx'.$idx][0] = 'NUMBER';
			$metadados['idx'.$idx][1] = 'Número de coleta do coletor';
			$idx++;
			
			$qq .= ", CONCAT(sptb.Ano,'-',sptb.Mes,'-',sptb.Day) as DATA_COLETA";
			$metadados['idx'.$idx][0] = 'DATA_COLETA';
			$metadados['idx'.$idx][1] = 'Data em que a amostra foi coletada';
			$idx++;

			$qq .=", addcolldescr(sptb.AddColIDS) as ADDCOLL";
			$metadados['ADDCOLL'] = 'Nome dos demais coletores';
			$metadados['idx'.$idx][0] = 'ADDCOLL';
			$metadados['idx'.$idx][1] = 'Nome dos demais coletores da amostra';
			$idx++;
			
			$qq .=", sptb.INPA_ID as INPA_NUM";
			$metadados['INPA_NUM'] = 'Número de registro do herbário INPA';
			$metadados['idx'.$idx][0] = 'INPA_NUM';
			$metadados['idx'.$idx][1] = 'Número de registro do herbário INPA';
			$idx++;
			
			$qq .= ", labeldescricao(0,pltb.PlantaID+0,0,TRUE,FALSE) as DESCRICAO";
			$metadados['idx'.$idx][0] = "DESCRICAO";
			$metadados['idx'.$idx][1] = 'Notas compeltas sobre a planta';
			$idx++;

		}
		$qq = $qq." FROM Plantas as pltb";
		if (!empty($basvar['coletas'])) {
			$qq .= " LEFT JOIN Especimenes as sptb ON sptb.PlantaID=pltb.PlantaID LEFT JOIN Pessoas as colpessoa ON sptb.ColetorID=colpessoa.PessoaID";
		}
		if (!empty($basvar['nomenoautor']) || !empty($basvar['nomeautor']) || !empty($basvar['taxacompleto'])) {
			$qq .= " LEFT JOIN Identidade as iddet ON pltb.DetID=iddet.DetID LEFT JOIN Tax_InfraEspecies as infsptb ON iddet.InfraEspecieID=infsptb.InfraEspecieID LEFT JOIN Tax_Especies as spectb ON iddet.EspecieID=spectb.EspecieID LEFT JOIN Tax_Generos as gentb ON iddet.GeneroID=gentb.GeneroID  LEFT JOIN Tax_Familias as famtb ON iddet.FamiliaID=famtb.FamiliaID LEFT JOIN Pessoas as detpessoa ON detpessoa.PessoaID=iddet.DetbyID ";
			} 
		if (!empty($basvar['localidade']) || !empty($basvar['gps'])) {
			$qq .= " LEFT JOIN Gazetteer as gaz ON gaz.GazetteerID=pltb.GazetteerID LEFT JOIN GPS_DATA as gpspt ON gpspt.PointID=pltb.GPSPointID LEFT JOIN Gazetteer as gazgps ON gazgps.GazetteerID=pltb.GazetteerID LEFT JOIN Municipio as muni ON gaz.MunicipioID=muni.MunicipioID LEFT JOIN Province  ON Province.ProvinceID=muni.ProvinceID LEFT JOIN Country  ON Country.CountryID=Province.CountryID";
		}
		if (!empty($basvar['projeto'])) {
			$qq .=" LEFT JOIN Projetos ON pltb.ProjetoID=Projetos.ProjetoID";	
		}
		if ($formhabitat>0) { 
			$qq .=" LEFT JOIN Habitat as hablocal ON hablocal.LocalityID=pltb.GazetteerID";
		}
	
		$qq .= " JOIN FiltrosSpecs as fl ON Plantas.PlantaID=fl.PlantaID WHERE FiltroID=".$filtro;
		$prepared = 1;
		$qz = "SELECT * FROM Plantas JOIN FiltrosSpecs as fl ON Plantas.PlantaID=fl.PlantaID WHERE FiltroID=".$filtro;
		$rz = mysql_query($qz,$conn);
		$nrz = mysql_numrows($rz);
		$stepsize = 1000;
		$nsteps = ceil($nrz/$stepsize);
		$_SESSION['metadados'] = serialize($metadados);
		$_SESSION['qq'] = $qq;
		$export_filename = "plantas_export_".$_SESSION['userlastname']."_".$_SESSION['sessiondate'].".csv";
} //if is not set prepared
//$prepared=0;
if ($prepared==1 && $step<=$nsteps) {
	if (empty($step)) {
		$step=0;
		$st1 = 0;
	} else {
		$qq = $_SESSION['qq'];
		$st1 = $st1+$stepsize+1;
	}
		$qqq = $qq." LIMIT $st1,$stepsize";
		$res = mysql_query($qqq,$conn);
		//echo $qqq."<br />";
		$starttime = microtime(true);
		$sttime = microtime();
		if ($res) {
			if ($step==0) {
				
				$fh = fopen("temp/".$export_filename, 'w') or die("nao foi possivel gerar o arquivo");
				$count = mysql_num_fields($res);
				$header = '';
				for ($i = 0; $i < $count; $i++){
					$header .= mysql_field_name($res, $i)."\t";
				}
				$header .= "\n";
				fwrite($fh, $header);
			} else {
				$fh = fopen("temp/".$export_filename, 'a') or die("nao foi possivel abrir o arquivo");
			}
			while($rsw = mysql_fetch_row($res)){
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
			//$endtime = microtime(true); 
			//$exectime = $endtime-$starttime;
			//$exectime = round(($exectime*100)/60,4);
			if ($step==0) {
				//$tfalta = ceil($exectime*$nsteps);
			} else {
				//$tfalta = $tfalta-$exectime;
			}
			//$tfalta = round(($tfalta/60),2);
$title = 'Exportar dados de monitoramento';
$body = '';
FazHeader($title,$body,$which_css,$which_java,$menu);
echo "
<form action='export-monitoramento-exec.php' name='myform' method='post'>
  <input type='hidden' name='prepared' value='".$prepared."'>
  <input type='hidden' name='ispopup' value='".$ispopup."'>
  <input type='hidden' name='nsteps' value='".$nsteps."'>
  <input type='hidden' name='st1' value='".($st1-1)."'>
  <input type='hidden' name='step' value='".($step+1)."'>
  <input type='hidden' name='export_filename' value='".$export_filename."'>
  <input type='hidden' name='stepsize' value='".$stepsize."'>
  <input type='hidden' name='tfalta' value='".$tfalta."'>
<br />
<table align='center' cellpadding='5' class='erro'>
  <tr><td class='tdformnotes'>Processando passo ".($step+1)." de ".($nsteps+1)."  AGUARDE!</td></tr>
  <!--- <tr><td class='tdformnotes'>Faltam aproximadamente ".$tfalta."  segundos para terminar</td></tr>--->
</table>
</form>
<script language=\"JavaScript\">setTimeout('document.myform.submit()',0.00001);</script>";

$which_java = array("<script type='text/javascript' src='javascript/myjavascripts.js'></script>",
"<!-- Create Menu Settings: (Menu ID, Is Vertical, Show Timer, Hide Timer, On Click ('all' or 'lev2'), Right to Left, Horizontal Subs, Flush Left, Flush Top) -->",
"<script type='text/javascript'>qm_create(0,false,0,500,false,false,false,false,false);</script>");
FazFooter($which_java,$calendar=FALSE,$footer=$menu);
		}
} // if is set prepared	
elseif ($step>$nsteps) { 
	$metadados = unserialize($_SESSION['metadados']);
	//echopre($metadados);
	$cdate = date("Y-m-d");
	$export_filename_metadados = "plantas_export_".$_SESSION['userlastname']."_".$_SESSION['sessiondate']."_definicoesDAScolunas.csv";
	$fh = fopen("temp/".$export_filename_metadados, 'w') or die("nao foi possivel gerar o arquivo");
	$stringData = "COLUNA\tDEFINICAO"; 
	foreach ($metadados as $kk => $vv) {
		$stringData = $stringData."\n".$vv[0]."\t".$vv[1];
	}
	fwrite($fh, $stringData);
	fclose($fh);
	if (file_exists("temp/".$export_filename) && file_exists("temp/".$export_filename_metadados)) {
		header("location: export-monitoramento-save.php?ispopup=1");
	} else {
		header("location: export-monitoramento-form.php?ispopup=1");
	}
}
}
?>