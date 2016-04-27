<?php
//Start session
ini_set("memory_limit","-1");
ini_set("mysql.allow_persistent","-1");

//Start session
session_start();

//INCLUI FUNCOES PHP E VARIAVEIS
include "functions/HeaderFooter.php";
include "functions/MyPhpFunctions.php";
include_once("functions/class.Numerical.php") ;

//FAZ A CONEXAO COM O BANCO DE DADOS
//$lang = $_SESSION['lang'];
$dbname = $_SESSION['dbname'];
$conn = ConectaDB($dbname);

//////PEGA E LIMPA VARIAVEIS
//$ppost = cleangetpost($_POST,$conn);
//@extract($ppost);
//$arval = $ppost;
//$gget = cleangetpost($_GET,$conn);
//@extract($gget);

$dd = @unserialize($_SESSION['destvararray']);
@extract($dd);
//echopre($dd);

##DEFINE ARQUIVOS
$export_filename = "plantas_export_".$_SESSION['userlastname']."_".$_SESSION['sessiondate'].".csv";
$export_filename_metadados = "plantas_export_".$_SESSION['userlastname']."_".$_SESSION['sessiondate']."_definicoesDAScolunas.csv";



	//$_SESSION['destvararray'] = serialize($ppost);
	unset($_SESSION['metadados']);
	unset($metadados);
	unset($_SESSION['qq']);
	if (empty($filtro)) { 
		//header("location: export-monitoramento-form.php");
	}
	$basvar = array();
	if (count($basicvariables)>0) {
		foreach($basicvariables as $val) {
			$var = array($val => $val);
			$basvar = array_merge((array)$basvar,(array)$var);
		}
	}
	
//echopre($basvar);
	
$qq = " SELECT 
pltb.PlantaID AS WikiPlantaID, 
pltb.PlantaTag as TAG_NUM"; 
$idx=0;
$metadados['idx'.$idx][0] = 'WikiPlantaID';
$metadados['idx'.$idx][1] = 'Identificador da planta na base de dados onde estão os dados';
$idx++;
$metadados['idx'.$idx][0] = 'TAG_NUM';
$metadados['idx'.$idx][1] = 'Número da placa da árvore';
$idx++;

if ($statustraitid>0) {
$qq .= ", traitvalueplantas(".$statustraitid.",pltb.PlantaID, '', 0,0 ) as STATUS";
$metadados['idx'.$idx][0] = 'STATUS';
$metadados['idx'.$idx][1] = 'SE A PLANTA ESTÁ VIVA OU MORTA';
$idx++;
}
if (!empty($basvar['nomenoautor'])) {
	$metadados['idx'.$idx][0] = 'NOME';
	$metadados['idx'.$idx][1] = 'Taxonomia no nivel de identificacao sem autores';
	$idx++;
	$qq .=", gettaxonname(pltb.DetID,1,0) as NOME";
} 
if (!empty($basvar['nomeautor'])) {
	$metadados['idx'.$idx][0] = 'NOME_AUTOR';
	$metadados['idx'.$idx][1] = 'Taxonomia no nivel de identificacao, se em nivel de especie ou de infraespecie, nome dos autores incluidos';
	$idx++;
	$qq .=", gettaxonname(pltb.DetID,1,1) as NOME_AUTOR";
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
		$metadados['idx'.$idx][0] = 'SP1_MORFOTIPO';
		$metadados['idx'.$idx][1] =  'Se o nome sp1 é morfotipo não publicado';
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
		$metadados['idx'.$idx][0] = 'SP2_MORFOTIPO';
		$metadados['idx'.$idx][1] =  'Se o nome sp2 é morfotipo não publicado';
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
spectb.Especie as SP1, 
IF(spectb.Morfotipo=1,'',spectb.EspecieAutor) as AUTHOR1, 
spectb.Morfotipo as SP1_MORFOTIPO, 
infsptb.InfraEspecieNivel as RANK1, 
infsptb.InfraEspecie as SP2, 
IF(infsptb.Morfotipo=1,'',infsptb.InfraEspecieAutor) as AUTHOR2, 
infsptb.Morfotipo as SP2_MORFOTIPO, 
iddet.DetModifier as CF, 
detpessoa.Abreviacao as DETBY, 
DAY(iddet.DetDate) as DETDD, 
MONTH(iddet.DetDate) as DETMM, 
YEAR(iddet.DetDate) as DETYY";
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
			//$metadados['idx'.$idx][0] = 'WikiGazetteerID';
			//$metadados['idx'.$idx][1] = "ID de GAZETTEER2 na base de dados";
			//$idx++;
			//$metadados['idx'.$idx][0] = 'WikiGazetteerParentID';
			//$metadados['idx'.$idx][1] = "ID da Localidade a qual pertence GAZETTEER2 na base de dados, 0 se não existir!";
			//$idx++;
			//$qq .= ",  Country as COUNTRY, Province as MAJORAREA, Municipio as MINORAREA, IF(pltb.GPSPointID>0,gazgps.PathName,IF(pltb.GazetteerID>0,gaz.PathName,'')) as GAZETTEER, IF(pltb.GPSPointID>0,TRIM(CONCAT(gazgps.GazetteerTIPOtxt,' ',gazgps.Gazetteer)),IF(pltb.GazetteerID>0,TRIM(CONCAT(gaz.GazetteerTIPOtxt,' ',gaz.Gazetteer)),'')) as GAZETTEER2";
			$qq .= ",  
			localidadefields(pltb.GazetteerID, pltb.GPSPointID,0, 0,0,'COUNTRY')  as COUNTRY,
			localidadefields(pltb.GazetteerID, pltb.GPSPointID,0, 0,0,'MINORAREA')  as MINORAREA, 
			localidadefields(pltb.GazetteerID, pltb.GPSPointID,0, 0,0,'MAJORAREA')  as MAJORAREA,
			localidadefields(pltb.GazetteerID, pltb.GPSPointID,0, 0,0,'GAZfirstPARENT')  as GAZETTEER_CURTO,
			localidadefields(pltb.GazetteerID, pltb.GPSPointID,0, 0,0,'GAZETTEER')  as GAZETTEER_COMPLETA,
			localidadefields(pltb.GazetteerID, pltb.GPSPointID,0, 0,0,'GAZETTEER_SPEC')  as GAZETTEER_SPECIFIC";
			//$qq .= ",  Country as COUNTRY, Province as MAJORAREA, Municipio as MINORAREA, IF(pltb.GPSPointID>0,gazgps.PathName,IF(pltb.GazetteerID>0,gaz.PathName,'')) as GAZETTEER, IF(pltb.GPSPointID>0,TRIM(gazgps.Gazetteer),IF(pltb.GazetteerID>0,TRIM(gaz.Gazetteer),'')) as GAZETTEER2,  IF(pltb.GPSPointID>0,gazgps.GazetteerID,IF(pltb.GazetteerID>0,gaz.GazetteerID,0)) AS WikiGazetteerID,  IF(pltb.GPSPointID>0,gazgps.ParentID,IF(pltb.GazetteerID>0,gaz.ParentID,0)) AS WikiGazetteerParentID";
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
			//$metadados['idx'.$idx][0] = 'COORD_PRECISION';
			//$metadados['idx'.$idx][1] = "Precisao das coordenadas, onde GPS_planta indica que a coordenada e do individuo; GPS quando for a coordenada de um ponto de GPS da proximidade do individuo; Localidade indica que a coordenada se refere a uma das localidades em GAZETTEER; Municipio e a coordenada de MINORAREA";
			//$idx++;
			$qq .= ", 
getlatlongdms(pltb.Latitude, pltb.Longitude, pltb.GPSPointID, pltb.GazetteerID, 0, 0, 0, 1,5)  as LATITUDE,
getlatlongdms(pltb.Latitude, pltb.Longitude, pltb.GPSPointID, pltb.GazetteerID, 0, 0,0, 0,5)  as LONGITUDE,
getaltitude(pltb.Altitude, pltb.GPSPointID,pltb.GazetteerID) as ALTITUDE";
			//$qq .= ", IF(ABS(pltb.Longitude)>0,pltb.Longitude,IF(pltb.GPSPointID>0,gpspt.Longitude,IF(gaz.Longitude<>0,gaz.Longitude,muni.Longitude))) as LONGITUDE, IF(ABS(pltb.Longitude)>0,pltb.Latitude,IF(pltb.GPSPointID>0,gpspt.Latitude,IF(gaz.Longitude<>0,gaz.Latitude,muni.Latitude))) as LATITUDE, IF(ABS(pltb.Longitude)>0,pltb.Altitude,IF(pltb.GPSPointID>0,gpspt.Altitude,IF(gaz.Longitude<>0,gaz.Altitude,''))) as ALTITUDE, IF(ABS(pltb.Longitude)>0,'GPS-planta',IF(pltb.GPSPointID>0,'GPS',IF(gaz.Longitude<>0,'Localidade','Municipio'))) as COORD_PRECISION";
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
				$qq .= ",  
				pltb.X as Pos_X, 
				pltb.Y as Pos_Y, 
				pltb.LADO as Pos_LADO, 
				pltb.Referencia as Pos_REF, 
				pltb.Distancia as Pos_DIST, 
				pltb.Angulo as Pos_ANGULO";
		}
		if ($formvariables>0) {
			if ($meanvalues==1) { $onlymean = 1;} else { $onlymean=0;}
			$qu = "SELECT 
			tr.TraitID,
			tr.TraitTipo,
			tr.TraitName,
			tr.TraitUnit,tr.
			TraitDefinicao, 
			tr.PathName 
			FROM Traits as tr WHERE tr.FormulariosIDS LIKE '%formid_".$formvariables."%'";
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
					$qq = $qq.", 
					traitvariation_plantas($tid,pltb.PlantaID, 0, $onlymean) AS ".$tascol.", 
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
					$qu = "SELECT DISTINCT TraitID FROM Monitoramento LEFT JOIN Plantas AS pl USING(PlantaID) WHERE pl.FiltrosIDS LIKE '%filtroid_".$filtro."%' AND CensoID='".$cen."'";
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
		else {
			#pega o diâmetro mais recente
				if ($daptraitid>0) {
					$qq .=", traitvaluespecs(".$daptraitid.",pltb.PlantaID,0,'mm',0,1) as DAPmax_mm";
					$metadados['idx'.$idx][0] = "DAPmm";
					$metadados['idx'.$idx][1] = "DAP em mm, o valor máximo se houver mais de 1";
					$idx++;
				}
		}
		if ($alturatraitid>0) {
					$qq .=", traitvaluespecs(".$alturatraitid.",pltb.PlantaID,0,'m',0,1) as ALTURAm";
					$metadados['idx'.$idx][0] = "ALTURAm";
					$metadados['idx'.$idx][1] = "ALTURA em metros, o valor máximo se houver mais de 1";
					$idx++;
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
				$qu = "SELECT tr.TraitID,tr.TraitTipo,tr.TraitName,tr.TraitUnit,tr.TraitDefinicao FROM Traits as tr WHERE tr.FormulariosIDS LIKE '%formid_".$formhabitat."%'";
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
		$qq = $qq." FROM Plantas as pltb";
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
		$qq .= " WHERE pltb.FiltrosIDS LIKE '%filtroid_".$filtro."%'";

		
		
//A QUERY ESTA PRONTA, AGORA PRECISA GERAR O ARQUIVO
		$qz = "SELECT * FROM Plantas WHERE FiltrosIDS LIKE '%filtroid_".$filtro."%'";
		$rz = mysql_query($qz,$conn);
		$nrz = mysql_numrows($rz);
		$_SESSION['exportnresult'] = $nrz;
		
		$nsteps = ceil($nrz/$stepsize);
		//$_SESSION['metadados'] = serialize($metadados);
		//$_SESSION['qq'] = $qq;


//echo $qq." LIMIT $st1,$stepsize<br />";

//if ($lixao==6789) {
$stepsize = 100;
//$step=0;
for ($st1 = 0; $st1 <= $nrz;$st1=$st1+$stepsize+1) {
//	while($st1<=$nrz) {
		$qqq = $qq." LIMIT $st1,$stepsize";
		$res = mysql_query($qqq,$conn);
		if ($res) {
			if ($st1==0) {
				
				$fh = fopen("temp/".$export_filename, 'w') or die("nao foi possivel gerar o arquivo");
				$count = mysql_num_fields($res);
				$_SESSION['exportnfields'] = $count;
				$header = '';
				for ($i = 0; $i < $count; $i++){
					if ($i<($count-1)) {
						$header .=  '"'. mysql_field_name($res, $i).'"'."\t";
					} else {
						$header .=  '"'. mysql_field_name($res, $i).'"';
					}
				}
				$header .= "\n";
				fwrite($fh, $header);
			} else {
				$fh = fopen("temp/".$export_filename, 'a') or die("nao foi possivel abrir o arquivo");
			}
			while($rsw = mysql_fetch_assoc($res)){
				$line = '';
				foreach($rsw as $value){
					if(!isset($value) || $value == ""){
						$value = "\t";
					} else{
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
		}
		$perc = ceil(($st1/$nrz)*99);
		$qnu = "UPDATE `temp_exportplantas".substr(session_id(),0,10)."` SET percentage=".$perc; 
		mysql_query($qnu);
		session_write_close();
		//$st1 = $st1+$stepsize+1;
		//$step++;
	}

###GERA METADADOS
$fh = fopen("temp/".$export_filename_metadados, 'w') or die("nao foi possivel gerar o arquivo");
$stringData = "COLUNA\tDEFINICAO"; 
foreach ($metadados as $kk => $vv) {
	$stringData = $stringData."\n".$vv[0]."\t".$vv[1];
}
fwrite($fh, $stringData);
fclose($fh);

$qnu = "UPDATE `temp_exportplantas".substr(session_id(),0,10)."` SET percentage=100"; 
mysql_query($qnu);
$message=  "CONCLUÍDO";
echo $message;
session_write_close();
//}

?>