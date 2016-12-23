<?php
//Start session
ini_set("memory_limit","-1");
ini_set("mysql.allow_persistent","-1");

//Start session
session_start();

//INCLUI FUNCOES PHP E VARIAVEIS
include "functions/HeaderFooter.php";
include "functions/MyPhpFunctions.php";

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


$perc =1;
	//echo $perc."<br />";
$qnu = "UPDATE `temp_exportplotdata.".substr(session_id(),0,10)."` SET percentage=".$perc; 
mysql_query($qnu);


//DEFINE NOME DOS ARQUIVOS E OBJETOS
$export_filename = "dadosParcela_".$idd.$tableref.".csv";
$export_filename_metadados = "dadosParcela_".$idd.$tableref."_metadados.txt";
$export_filename_public = "dadosParcela_".$idd.$tableref."_public.csv";
$export_filename_censos = "dadosParcela_".$idd.$tableref."_censos.csv";
$export_filename_censospub = "dadosParcela_".$idd.$tableref."_censospub.csv";


//DEFINE O QUERY
$qwhere = " WHERE isvalidlocalandsub(pltb.GazetteerID, pltb.GPSPointID, ".$idd.", '".$tableref."')>0 AND moni.CensoID >0";

//CONTA O NUMERO DE CENSOS
$qz = "SELECT DISTINCT moni.CensoID FROM Monitoramento as moni JOIN Plantas AS pltb ON moni.PlantaID=pltb.PlantaID ".$qwhere;
//echo $qz."<br >";
$rz = mysql_query($qz,$conn);
$ncensos = mysql_numrows($rz);

//CONTA O NÚMERO MÁXIMO DE DAPS/INDIVIDUO/CENSO (PARA GERAR COLUNAS)
IF ($daptraitid>0) {
	$qq = "SELECT  max(substrCount(TraitVariation,';')+1) as ndaps FROM Monitoramento as moni JOIN Plantas AS pltb ON moni.PlantaID=pltb.PlantaID ".$qwhere. " AND moni.TraitID=".$daptraitid;
	$rz = mysql_query($qq,$conn);
	$rwz = mysql_fetch_assoc($rz);
	$ndaps = $rwz['ndaps'];
} 
else {
	$ndaps = 0;
}

$qz = "SELECT MonitoramentoID,CensoID,IF(TraitID=".$daptraitid.",SPLIT_STR_MAX(TraitVariation,';'),0) as DAPmax FROM Monitoramento as moni JOIN Plantas AS pltb ON moni.PlantaID=pltb.PlantaID ".$qwhere." ORDER BY moni.CensoID,pltb.PlantaTag";
$monisids = mysql_query($qz,$conn);
$nvals = mysql_numrows($monisids);
//echo $qz."<br />";

//IDS DAS VARIAVEIS INCLUIDAS NO CENSO
$qq = "SELECT  DISTINCT moni.TraitID, maketraitname(moni.TraitID) as TraitNome FROM Monitoramento as moni JOIN Plantas AS pltb ON moni.PlantaID=pltb.PlantaID ".$qwhere;
//echo $qq."<br />";
$rz = mysql_query($qq,$conn);
$traitids = array();
while ($rwz = mysql_fetch_assoc($rz)) {
	if ($rwz['TraitID']!=$daptraitid) {
		$traitids[$rwz['TraitNome']] = $rwz['TraitID'];
	}
}

//echopre($traitids);
//CONSTROI O QUERY
//SE HOUVER PELO MENOS UM CENSO, DEFINE QUERY E CONSTROI METADADOS
if ($ncensos>0) {
//INFORMACOES DA PLANTA
$sql = "SELECT 
moni.MonitoramentoID AS WikiMonitoramentoID,
pltb.PlantaID AS WikiPlantaID, 
pltb.PlantaTag as TAG_NUM, 
getidentidade(pltb.DetID,1,0,1,0,0) AS FAMILIA, 
getidentidade(pltb.DetID,1,0,0,1,0) AS GENERO, 
getidentidade(pltb.DetID,1,0,0,0,1) AS NOME";
if ($habitotraitid>0) {
	$sql .= ", traitvalueplantas(".$habitotraitid.", pltb.PlantaID, '', 0,0 ) AS HABITO";
}
#INFORMAÇÕES DE LOCALIDADE
$sql .= ", 
localidadefields(pltb.GazetteerID, pltb.GPSPointID,0,0,0, 'COUNTRY')  as COUNTRY,
localidadefields(pltb.GazetteerID, pltb.GPSPointID,0,0,0, 'MAJORAREA')  as PROVINCE,
localidadefields(pltb.GazetteerID, pltb.GPSPointID,0,0,0, 'MINORAREA')  as MUNICIPIO,
localidadefields(pltb.GazetteerID, pltb.GPSPointID,0,0,0, 'GAZETTEER')  as GAZETTEER,
parcelafiels(pltb.GazetteerID, pltb.GPSPointID, 'PARGAZ_SPEC')  as PAR_GAZ_SPEC,
parcelafiels(pltb.GazetteerID, pltb.GPSPointID, 'PARDIMX')  as PAR_GAZ_DIMx,
parcelafiels(pltb.GazetteerID, pltb.GPSPointID, 'PARDIMY')  as PAR_GAZ_DIMy,
localidadefields(pltb.GazetteerID, pltb.GPSPointID,0,0,0, 'GAZETTEER_SPEC')  as GAZETTEER_SPEC,
parcelafiels(pltb.GazetteerID, pltb.GPSPointID, 'DIMX')  as GAZ_DIMx,
parcelafiels(pltb.GazetteerID, pltb.GPSPointID, 'DIMY')  as GAZ_DIMy,
parcelafiels(pltb.GazetteerID, pltb.GPSPointID, 'STARTX')  as GAZ_Startx,
parcelafiels(pltb.GazetteerID, pltb.GPSPointID, 'STARTY')  as GAZ_Starty,
pltb.X as POS_X, 
pltb.Y as POS_Y, 
pltb.LADO as POS_LADO, 
pltb.Referencia as POS_REF, 
pltb.Distancia as POS_DIST, 
pltb.Angulo as POS_ANGULO,
habitaclasse(pltb.HabitatID) AS  HABITAT_CLASSE";
#INFORMAÇÕES DO CENSO
$sql .= ",
cs.CensoNome AS CensoNome";
#coluna de data e unidade de medida
$sql .= ",
moni.DataObs as DATA_OBS,
maketraitname(moni.TraitID) as TRAIT_NOME,
moni.TraitUnit as UNIDADE_MEDIDA";
if ($ndaps>0) {
	#colunas de dap dados de dap
	$dapsql = "";
	for($p=1;$p<=$ndaps;$p++) {
		$dapsql .= ", pegadap(moni.TraitVariation,';',".$p.", ".$daptraitid.",moni.TraitID) as DAP_".$p;
	}
	$sql .= $dapsql;
}
#COLOCA COLUNAS DE OUTRAS VARIAVEIS PRESENTES NO CENSO SELECIONADO
foreach($traitids as $kk => $vv) {
	$sql .=  ", traitvalue (moni.TraitVariation,moni.TraitID, ".$vv.")  AS ".$kk;
}
$sql .= " FROM Monitoramento as moni JOIN Plantas AS pltb ON moni.PlantaID=pltb.PlantaID JOIN Censos as cs ON moni.CensoID=cs.CensoID";

//EXECUTA O QUERY
//$qq = $sql.$qwhere." ORDER BY cs.CensoNome,moni.DataObs";
//echo $qqq."<br >";
//$rsql = mysql_query($qqq, $conn);
//$nvals= 1000;
//$stepsize = 200;
//$nsteps = ceil($nvals/$stepsize);
//$step=0;
//$st1 = 0;
$idx = 0;
$censosss = array();
$censossspub = array();
$publicrecs = array();
$addcoltxt = array();
$excludedcolumns = array();
while($rowmoni = mysql_fetch_assoc($monisids)) {
	$dapmax = $rowmoni['DAPmax']+0;
	$curcenso = $rowmoni['CensoID']+0;
	
	$qcens = "SELECT * FROM Censos WHERE CensoID=".$curcenso;
	$rcen = mysql_query($qcens,$conn);
	$rwcen = mysql_fetch_assoc($rcen);
	$excludefrompublic = array(); //define colunas a excluir de tabelas que são abertas e publicas
	$excludecensosids=0;
	$dappublicmin = $rwcen['DapAcesso']+0;
	if ($rwcen['CensoAcesso']==1) {
		$excludecensosids = 1;
	} else {
		if ($rwcen['CensoAcesso']==3) {
			$excludefrompublic[] = "GENERO";
			$excludefrompublic[] = "NOME";
			$excludedcolumns[] = "GENERO";
			$excludedcolumns[] = "NOME";
		}
	}
	//echopre($rwcen);
	$censosss[] = $curcenso;
	//equipe do censo
	$addcolvalue =  $rwcen['EquipePessoaID'];
	$addcolarr = explode(";",$addcolvalue);
	$equipe = array();
	foreach ($addcolarr as $kk => $val) {
		$qq = "SELECT * FROM Pessoas WHERE PessoaID='$val'";
		$respes = mysql_query($qq,$conn);
		$rwpess = mysql_fetch_assoc($respes);
		$pess = $rwpess['Prenome']." ".$rwpess['SegundoNome']."  ".$rwpess['Sobrenome'];
		$pess = str_replace("  "," ",$pess);
		$pess = str_replace("  "," ",$pess);
		$equipe[] = $pess." [".$rwpess['Abreviacao']."]";
	}
	asort($equipe);
	$addcoltxt["'".$curcenso."'"] = implode(";",$equipe);
	//if ($step>0) {
		//$st1 = $st1+$stepsize+1;
	//}
	//$qqq = $qq." LIMIT $st1,$stepsize";
	$qqq = $sql." WHERE moni.MonitoramentoID=".$rowmoni['MonitoramentoID'];
	$rsql = mysql_query($qqq,$conn);
	//echo $qqq."<br />";
//SALVA OS DADOS NO ARQUIVO TXT
	//$rsql=FALSE;
	if ($rsql) {
		while($rsw = mysql_fetch_assoc($rsql)) {
	if ($idx==0) {
		$fh = fopen("temp/".$export_filename, 'w') or die("Não foi possivel gerar o arquivo");
		$count = mysql_num_fields($rsql);
		$header = '';
		for ($i = 0; $i < $count; $i++){
			 if ($i==($count-1)) {
				$header .= "\"".mysql_field_name($rsql, $i)."\"";
			} else {
				 $header .= "\"".mysql_field_name($rsql, $i)."\"\t";
			 }
		}
		$header .= "\n";
		fwrite($fh, $header);
		
		//make public file
		$fhpub = fopen("temp/".$export_filename_public, 'w') or die("Não foi possivel gerar o arquivo");
		$headerpub = '';
		for ($i = 0; $i < $count; $i++){
			$fieldname = mysql_field_name($rsql, $i);
			//$fnn = strtoupper($fieldname);
			//if (!in_array($fieldname,$excludefrompublic,TRUE)) {
				 if ($i==($count-1)) {
					$headerpub .= "\"".$fieldname."\"";
				} else {
					 $headerpub .= "\"".$fieldname."\"\t";
			 	}
			 //}
		}
		$headerpub .= "\n";
		fwrite($fhpub, $headerpub);
	} 
	else {
		$fh = fopen("temp/".$export_filename, 'a') or die("Não foi possivel abrir o arquivo");
		$fhpub = fopen("temp/".$export_filename_public, 'a') or die("Não foi possivel gerar o arquivo");
	}
	//PARA CADA LINHA
	$line = '';
	$linepub = '';
	//INCLUI OS VALORES DE CADA COLUNA
	$vi = 1;
	$vu = count($rsw);
	foreach($rsw as $fieldname => $value){
		//SE O VALOR FOR UMA DATA VAZIA SUBSTITUI POR NADA
		if ($value=='0000-00-00') {
				$value='';
		}
		//INCLUI O VALOR E O SEPARADOR
		//if(!isset($value) || $value == ""){
		//	$value = "\"\"";
		//}
		//else {
			//important to escape any quotes to preserve them in the data.
			$value = str_replace("\"","", $value);
			//needed to encapsulate data in quotes because some data might be multi line.
			//the good news is that numbers remain numbers in Excel even though quoted.
			$value = "\"".$value ."\"";
		//}
		//$fs = @array_search($fieldname,$excludefrompublic);
		//$dapm = $dappublicmin[$fs];
		if ($vi==$vu) {
			$line .= $value;
			if (in_array($fieldname,$excludefrompublic,TRUE)) {
				$vpub = 'restrito';
			} else {
				$vpub = $value;
			}
			if ($dapmax>=$dappublicmin && $excludecensosids==0) {
				$linepub .= $vpub;
				$censossspub[] = $curcenso;
				$publicrecs[] = $rowmoni['MonitoramentoID'];
			}
		} 
		else {
			$line .= $value."\t";
			if (in_array($fieldname,$excludefrompublic,TRUE)) {
				$vpub = 'restrito';
			} else {
				$vpub = $value;
			}
			if ($dapmax>=$dappublicmin && $excludecensosids==0) {
					//echo "curcenso ".$curcenso."  excludecensosids DAPMAX".$dapmax."  DAPMIN".$dappublicmin;
					//echopre($excludecensosids);
					$linepub .= $vpub."\t";
					$publicrecs[] = $rowmoni['MonitoramentoID'];
					$censossspub[] = $curcenso;
			} 
			echo "<br /> curcenso ".$curcenso."  DAPMAX".$dapmax."  DAPMIN".$dappublicmin."   exclude=".$excludecensosids;
		}
		$vi++;
	}
	$line = trim($line)."\n";
	fwrite($fh, $line);
	fclose($fh);
	$linepub = trim($linepub);
	if (!empty($linepub)) {
		$linepub = trim($linepub)."\n";
		fwrite($fhpub, $linepub);
	}
	fclose($fhpub);
	$perc = ceil(($idx/$nvals)*100)-1;
	//echo $perc."<br />";
	$qnu = "UPDATE `temp_exportplotdata.".substr(session_id(),0,10)."` SET percentage=".$perc; 
	mysql_query($qnu);
	session_write_close();
	$idx++;
}


	}

	$st1 = ($st1-1);
	$step = ($step+1);
}


///GERA E SALVA OS METADADOS
///COLUNAS DA PLANILHA DE DADOS:
	$metadados = array();
	$idx2=0;
	$metadados['idx'.$idx2][0] = 'WikiMonitoramentoID';
	$metadados['idx'.$idx2][1] = 'Id de referência da medição na base de dados - valores unicos, identificador das linhas';
	$idx2++;
	$metadados['idx'.$idx2][0] = 'WikiPlantaID';
	$metadados['idx'.$idx2][1] = 'Id de referência da planta na base de dados';
	$idx2++;
	$metadados['idx'.$idx2][0] = 'TAG_NUM';
	$metadados['idx'.$idx2][1] = 'Número da placa da árvore no campo';
	$idx2++;
	$metadados['idx'.$idx2][0] = 'FAMILIA';
	$metadados['idx'.$idx2][1] = 'Familia taxonômica, se vazio a planta não está identificada neste nível';
	$idx2++;
	$metadados['idx'.$idx2][0] = 'GENERO';
	$metadados['idx'.$idx2][1] = 'Gênero taxonômico, se vazio a planta não está identificada neste nível';
	$idx2++;
	$metadados['idx'.$idx2][0] = 'NOME';
	$metadados['idx'.$idx2][1] = 'Identificação completa da planta (sem autores), no nível que estiver identificada';
	$idx2++;
	if ($habitotraitid>0) {
		$metadados['idx'.$idx2][0] = 'HABITO';
		$metadados['idx'.$idx2][1] = "Forma de vida, se não anotado, provavelmente é arvore";
		$idx2++;
	}
	$metadados['idx'.$idx2][0] = 'GAZETTEER';
	$metadados['idx'.$idx2][1] = "As demais subdivisoes de MINORAREA. Quando houver mais de uma, concatenadas de forma hierarquica do maior para o menor";
	$idx2++;
	$metadados['idx'.$idx2][0] = 'PAR_GAZ_SPEC';
	$metadados['idx'.$idx2][1] = "A localidade a qual pertence GAZETTEER_SPEC";
	$idx2++;
	$metadados['idx'.$idx2][0] = 'PAR_GAZ_DIMx';
	$metadados['idx'.$idx2][1] = "A dimensão X da parcela PAR_GAZ_SPEC";
	$idx2++;
	$metadados['idx'.$idx2][0] = 'PAR_GAZ_DIMy';
	$metadados['idx'.$idx2][1] = "A dimensão Y da parcela PAR_GAZ_SPEC";
	$idx2++;
	$metadados['idx'.$idx2][0] = 'GAZETTEER_SPEC';
	$metadados['idx'.$idx2][1] = "A localidade mais especifica da planta";
	$idx2++;
	$metadados['idx'.$idx2][0] = 'GAZ_DIMx';
	$metadados['idx'.$idx2][1] = "A dimensão X da parcela GAZETTEER_SPEC";
	$idx2++;
	$metadados['idx'.$idx2][0] = 'GAZ_DIMy';
	$metadados['idx'.$idx2][1] = "A dimensão Y da parcela GAZETTEER_SPEC";
	$idx2++;
	$metadados['idx'.$idx2][0] = 'GAZ_Startx';
	$metadados['idx'.$idx2][1] = "A posicao X da subparcela GAZETTEER_SPEC na parcela PAR_GAZ_SPEC";
	$idx2++;
	$metadados['idx'.$idx2][0] = 'GAZ_Starty';
	$metadados['idx'.$idx2][1] = "A posicao Y da subparcela GAZETTEER_SPEC na parcela PAR_GAZ_SPEC";
	$idx2++;
	$metadados['idx'.$idx2][0] = 'Pos_X';
	$metadados['idx'.$idx2][1] = "Posicao X da planta marcada em relacao a GAZETTEER_SPEC quando este for uma parcela";
	$idx2++;
	$metadados['idx'.$idx2][0] = 'Pos_Y';
	$metadados['idx'.$idx2][1] = "Posicao Y da planta marcada em relacao a GAZETTEER_SPEC quando este for uma parcela";
	$idx2++;
	$metadados['idx'.$idx2][0] = 'Pos_LADO';
	$metadados['idx'.$idx2][1] = "Para dados da grade do PPBio no PNVirua, indicando o lado das coordenadas X,Y";
	$idx2++;
	$metadados['idx'.$idx2][0] = 'Referencia';
	$metadados['idx'.$idx2][1] = "Qual a referencia dos valores X e Y, em relacao a que vertice da parcela";
	$idx2++;
	$metadados['idx'.$idx2][0] = 'Pos_DIST';
	$metadados['idx'.$idx2][1] = "Distancia em linha reta do GAZETTEER_SPEC a planta marcada, para ser usada em combinacao com Pos_ANGULO, para as situacoes em que GAZETTEER_SPEC e um marco de uma grade, numa trilha, etc.";
	$idx2++;
	$metadados['idx'.$idx2][0] = 'Pos_ANGULO';
	$metadados['idx'.$idx2][1] = "O angulo de direcao da planta a partir do GAZETTEER_SPEC em relacao ao norte magnetico";
	$idx2++;
	$metadados['idx'.$idx2][0] = "HABITAT_CLASSE";
	$metadados['idx'.$idx2][1] = "Classe de habitat ou ambiente da planta";
	$idx2++;
	$metadados['idx'.$idx2][0] = "CensoNome";
	$metadados['idx'.$idx2][1] = "Nome do censo em que a medição está inserida";
	$idx2++;
	$metadados['idx'.$idx2][0] = "DATA_OBS";
	$metadados['idx'.$idx2][1] = "Data da observação";
	$idx2++;
	$metadados['idx'.$idx2][0] = "UNIDADE_MEDIDA";
	$metadados['idx'.$idx2][1] = "Unidade de medida da medição, quando for variável quantitativa";
	$idx2++;
	if ($ndaps>0) {
		$metadados['idx'.$idx2][0] = "DAP_number";
		$metadados['idx'.$idx2][1] = "Diâmetro a altura do peito, geralmente 1.3 metros - se houver mais de uma coluna estas representam múltiplos fustes do mesmo individuo medidos no censo";
		$idx2++;
	}
	foreach($traitids as $kk => $vv) {
		$qq = "SELECT  * FROM Traits WHERE TraitID=".$vv;
		$rz = mysql_query($qq,$conn);
		$rwz = mysql_fetch_assoc($rz);
		$metadados['idx'.$idx2][0] = $kk;
		$metadados['idx'.$idx2][1] = $rwz['TraitDefinicao'];
		$idx2++;
	}
	
	$censosss = array_unique($censosss);
	$censossspub = array_unique($censossspub);
	$excludedcolumns = array_unique($excludedcolumns);
	//asort($addcoltxt);

	//SALVA METADADOS
	$fh = fopen("temp/".$export_filename_metadados, 'w') or die("não foi possivel gerar o arquivo");
	$stringData = "DADOS GERADOS DINAMICAMENTE\n";
	
	$sql= "SELECT CONCAT(gaz.PathName,' [',Municipio,'- ',Province,' - ',Country,']') as nome, gaz.GazetteerID as nomeid FROM Gazetteer as gaz JOIN Municipio  USING(MunicipioID) JOIN Province USING(ProvinceID) JOIN Country USING(CountryID) WHERE GazetteerID=".$idd;
	$rz = @mysql_query($sql,$conn);
	$row = mysql_fetch_assoc($rz);
	$local = $row['nome'];

	//EXTRAI A URL 
		$url = $_SERVER['HTTP_REFERER'];
		$uu = explode("/",$url);
		$nu = count($uu)-1;
		unset($uu[$nu]);
		$url = implode("/",$uu);
	$stringData .= "DATA: ".date ("F d Y H:i:s.", filemtime("temp/".$export_filename))."\n";
	$stringData .= "URL: ".$url."\n\n";
	$stringData .= "LOCALIDADE:\n".$local."\n";
	$stringData .=  "\nCENSOS INCLUIDOS:\n";
		$cols = array("CENSONOME","DATA_INICIO","DATA_FIM","RESPONSAVEL","EMAIL");
		$cl  = implode("\t",$cols);
		$stringData .=  $cl."\n";
		$txtdetalhes = array();
		foreach ($censosss as $censoid) {
			$sql = "SELECT * FROM Censos LEFT JOIN Users ON ResponsavelID=UserID WHERE CensoID=".$censoid;
			$rzz = mysql_query($sql,$conn);
			$rww = mysql_fetch_assoc($rzz);

			$cnome =  $rww['CensoNome'];
			$sql = "SELECT MIN(DataObs) AS minDATA, MAX(DataObs) AS maxDATA FROM Monitoramento WHERE CensoID=".$censoid;
			$rzz2 = mysql_query($sql,$conn);
			$rww2 = mysql_fetch_assoc($rzz2);
			$txtnome = array($cnome,$rww2['minDATA'],$rww2['maxDATA'],$rww['FirstName']." ".$rww['LastName'],$rww['Email']);
			$txtn  = implode("\t",$txtnome);
			$stringData .=  $txtn."\n";
			
			$equipe = $addcoltxt["'".$censoid."'"];

			if ($rww['MetaCensoID']>0) {
				$sql = "SELECT * FROM Censos LEFT JOIN Users ON ResponsavelID=UserID WHERE CensoID=".$rww['MetaCensoID'];
				$rzz3 = mysql_query($sql,$conn);
				$rww3 = mysql_fetch_assoc($rzz3);
				$metmp = $rww3['MetaDados'];
				$metpoly = $rww3['DataPolicy'];
			} else {
				$metmp = $rww['MetaDados'];
				$metpoly = $rww['DataPolicy'];
			}

			if (!empty($equipe)) {
				$equipe = "\n\t\tEQUIPE: ".$equipe;
			}
			if (!empty($metmp)) {
				$met = "\n\t\tDESCRIÇÃO: ".$metmp;
			} else {
				$met = '';
			}
			if (!empty($metpoly)) {
				$poli = "\n\t\tPOLITICA PARA USO DOS DADOS: ".$metpoly;
			} else {
				$poli = '';
			}
			if (!empty($met) || !empty($equipe) || !empty($poli)) {
				//echo  "\t\t".$cnome.$equipe.$met.$poli;
				$txtdetalhes[] = "\t".$cnome.$equipe.$met.$poli;
			}
			//echo  "<br >TESTE 2 \t\t".$cnome.$equipe.$met.$poli;

		}
		//echopre($txtdetalhes);
		$stringData .=  "\n\n";
		$stringData .=  "***CADA LINHA DA TABELA DE DADOS REPRESENTA UMA MEDIÇÃO DE UM INDIVIDUO NUMA DATA***\n\n";
		$stringData .=  "EXPLICAÇÃO DAS COLUNAS NA TABELA DE DADOS:\n\n";
		$stringData .= "COLUNA\tDEFINICAO\tACESSO"; 
		foreach ($metadados as $kk => $vv) {
					if (in_array($vv[0],$excludedcolumns,TRUE)) {
						$notinpublic = "com permissão do responsável";
					} else {
						$notinpublic = "aberto";
					}
			$stringData .= "\n".$vv[0]."\t".$vv[1]."\t".$notinpublic;
		}
		$stringData .=  "\n\n\n***ATENÇÃO  --  ";
		$stringData .=  "algumas colunas podem não ter valores de fato (ou 0) porque não são necessárias para este conjunto de dados. Simplesmente ignore!***\n";
		if (count($txtdetalhes)>0) {
			$stringData .=  "\n\nDETALHES DOS CENSOS:\n";
			//foreach($txtdetalhes as $vv) {
			//	$stringData .=  $vv."\n";
			//}
			$detx = implode("\n",$txtdetalhes);
			$stringData .=  $detx;
			$stringData .=  "\n\n";
		}
			$stringData .=  "***DADOS PODEM CONTER ERROS!***\n";
			fwrite($fh, $stringData);
			fclose($fh);
			$message=  "100% CONCLUIDO";
} else {
	$message=  "NÃO HÁ DADOS DE CENSO PARA ESSA LOCALIDADE";
}



//SALVA IDS DOS CENSOS INCLUIDOS NOS ARQUIVOS
$fh = fopen("temp/".$export_filename_censos, 'w') or die("não foi possivel gerar o arquivo");
unset($stringData);
foreach ($censosss as $cc) {
		$stringData = $stringData.$cc."\n";
}
fwrite($fh, $stringData);
fclose($fh);

$fh = fopen("temp/".$export_filename_censospub, 'w') or die("não foi possivel gerar o arquivo");
unset($stringData);
foreach ($censossspub as $cc) {
		$stringData = $stringData.$cc."\n";
}
fwrite($fh, $stringData);
fclose($fh);

//SE NAO HOUVER DADOS TOTALMENTE PUBLICOS (SEM NECESSIDADE DE SOLICITACAO), APAGA O ARQUIVO VAZIO
$publicrecs = array_unique($publicrecs);
if (count($publicrecs)==0 || !isset($publicrecs)) {
	//echo "temp/".$export_filename_public;
	unlink("temp/".$export_filename_public);
} 
$qnu = "UPDATE `temp_exportplotdata.".substr(session_id(),0,10)."` SET percentage=100"; 
mysql_query($qnu);
echo $message;
session_write_close();
?>