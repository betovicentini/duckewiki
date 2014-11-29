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


//DEFINE NOME DOS ARQUIVOS E OBJETOS
$export_filename = "dadosParcela_".$gazetteerid.".csv";
$export_filename_metadados = "dadosParcela_".$gazetteerid."_metadados.csv";
$metadados = array();

//CONTA O NUMERO DE CENSOS
$qz = "SELECT DISTINCT moni.CensoID FROM Monitoramento as moni JOIN Plantas AS pl ON moni.PlantaID=pl.PlantaID LEFT JOIN Gazetteer as gaz ON pl.GazetteerID=gaz.GazetteerID LEFT JOIN GPS_DATA as gps ON pl.GPSPointID=gps.PointID WHERE 
(pl.GazetteerID='".$gazetteerid."' OR gps.GazetteerID='".$gazetteerid."' OR gaz.ParentID='".$gazetteerid."') AND
moni.CensoID>0 AND moni.TraitID='".$daptraitid."'";
//echo $qz;
$rz = @mysql_query($qz,$conn);
$ncensos = @mysql_numrows($rz);

//SE HOUVER PELO MENOS UM CENSO, DEFINE QUERY E CONSTROI METADADOS
if ($ncensos>0) {
	$sql = "SELECT pltb.PlantaID AS WikiPlantaID, pltb.PlantaTag as TAG, getidentidade(DetID,1,0,1,0,0) AS Familia, getidentidade(pltb.DetID,1,0,0,1,0) AS Genero, getidentidade(pltb.DetID,1,0,0,0,1) AS Nome";
	$idx=0;
	$metadados['idx'.$idx][0] = 'WikiPlantaID';
	$metadados['idx'.$idx][1] = 'Id de referência da planta na base de dados';
	$idx++;
	$metadados['idx'.$idx][0] = 'TAG';
	$metadados['idx'.$idx][1] = 'Número da placa da árvore no campo';
	$idx++;
	$metadados['idx'.$idx][0] = 'Familia';
	$metadados['idx'.$idx][1] = 'Familia taxonômica, se vazio a planta não está identificada neste nível';
	$idx++;
	$metadados['idx'.$idx][0] = 'Genero';
	$metadados['idx'.$idx][1] = 'Gênero taxonômico, se vazio a planta não está identificada neste nível';
	$idx++;
	$metadados['idx'.$idx][0] = 'Nome';
	$metadados['idx'.$idx][1] = 'Identificação completa da planta (sem autores), no nível que estiver identificada';
	$idx++;
	//PARA CADA CENSO ADICIONA QUERY E METADADOS
	while ($rr = @mysql_fetch_assoc($rz)) {
		$qz = "SELECT * FROM Censos WHERE CensoID='".$rr['CensoID']."'";
		$ruz = mysql_query($qz,$conn);
		$rwz = mysql_fetch_assoc($ruz);
		$cso = substr($rwz['DataFim'],0,4);
		$coln = "DAP".$cso;
		$coln2 = "DAP".$cso."unit";
		$coln3 = "DAP".$cso."date";
		$sql .= ", censotrait(".$daptraitid.",pltb.PlantaID, 1, 0,0 ) AS ".$coln.", censotrait(".$daptraitid.",pltb.PlantaID, 1, 0,1 ) AS ".$coln2.", censotrait(".$daptraitid.",pltb.PlantaID, 1, 1,0 ) AS ".$coln3;
		$metadados['idx'.$idx][0] = $coln;
		$metadados['idx'.$idx][1] = "DAP da planta segundo o censo ".$rwz['CensoNome'].", que compreende o período de ".$rwz['DataInicio']." à ".$rwz['DataFim'];
		$idx++;
		$metadados['idx'.$idx][0] = $coln2;
		$metadados['idx'.$idx][1] = "Unidade de medida do DAP da planta para censo ".$rwz['CensoNome'];
		$idx++;
		$metadados['idx'.$idx][0] = $coln3;
		$metadados['idx'.$idx][1] = "Data exata da medição DAP para censo ".$rwz['CensoNome'];
		$idx++;
	}
	if ($statustraitid>0) {
		$sql .= ", traitvalueplantas(".$statustraitid.", pltb.PlantaID, '', 0,0 ) AS STATUS";
//				$sql .= ", censotrait(".$statustraitid.",pltb.PlantaID, 1, 0,0 ) AS STATUS, censotrait(".$statustraitid.",pltb.PlantaID, 1, 1,0 ) AS STATUSdata";
		$metadados['idx'.$idx][0] = 'STATUS';
		$metadados['idx'.$idx][1] = "Status da planta, se viva ou morta, se vazio indica que a planta está viva";
		$idx++;
	}
	if ($habitotraitid>0) {
		$sql .= ", traitvalueplantas(".$habitotraitid.", pltb.PlantaID, '', 0,0 ) AS HABITO";
		$metadados['idx'.$idx][0] = 'HABITO';
		$metadados['idx'.$idx][1] = "Forma de vida, se não anotado, provavelmente é arvore";
		$idx++;
	}
	if ($alturatraitid>0) {
		$sql .= ", censotrait(".$alturatraitid.",pltb.PlantaID, 1, 0,0 ) AS ALTURA, censotrait(".$alturatraitid.",pltb.PlantaID, 1, 0,1 ) AS ALTURAunit, censotrait(".$alturatraitid.",pltb.PlantaID, 1, 1,0 ) AS ALTURAdata";
		$metadados['idx'.$idx][0] = 'ALTURA';
		$metadados['idx'.$idx][1] = "Altura da planta";
		$idx++;
		$metadados['idx'.$idx][0] = 'ALTURAunit';
		$metadados['idx'.$idx][1] = "Unidade de medida da altura da planta";
		$idx++;
		$metadados['idx'.$idx][0] = 'ALTURAdata';
		$metadados['idx'.$idx][1] = "Data de medição da altura da planta";
		$idx++;
	}
$sql .= ", checkplantaspecimen(pltb.PlantaID) AS N_SPECIMENES";
$metadados['idx'.$idx][0] = 'N_SPECIMENES';
$metadados['idx'.$idx][1] = "Número de amostras coletadas para essa planta, ou seja, material registrado como amostra coletada - Especimenes";
$idx++;

 $sql .= ", localidadefields(pltb.GazetteerID, pltb.GPSPointID,0,0,0, 'GAZETTEER')  as GAZETTEER,
parcelafiels(pltb.GazetteerID, pltb.GPSPointID, 'PARGAZ_SPEC')  as PAR_GAZ_SPEC,
parcelafiels(pltb.GazetteerID, pltb.GPSPointID, 'PARDIMX')  as PAR_GAZ_DIMx,
parcelafiels(pltb.GazetteerID, pltb.GPSPointID, 'PARDIMY')  as PAR_GAZ_DIMy,
localidadefields(pltb.GazetteerID, pltb.GPSPointID,0,0,0, 'GAZETTEER_SPEC')  as GAZETTEER_SPEC,
parcelafiels(pltb.GazetteerID, pltb.GPSPointID, 'DIMX')  as GAZ_DIMx,
parcelafiels(pltb.GazetteerID, pltb.GPSPointID, 'DIMY')  as GAZ_DIMy,
parcelafiels(pltb.GazetteerID, pltb.GPSPointID, 'STARTX')  as GAZ_Startx,
parcelafiels(pltb.GazetteerID, pltb.GPSPointID, 'STARTY')  as GAZ_Starty"; 
 $sql .= ",  pltb.X as Pos_X, pltb.Y as Pos_Y, pltb.LADO as Pos_LADO, pltb.Referencia as Pos_REF, pltb.Distancia as Pos_DIST, pltb.Angulo as Pos_ANGULO ";
 			$metadados['idx'.$idx][0] = 'GAZETTEER';
			$metadados['idx'.$idx][1] = "As demais subdivisoes de MINORAREA. Quando houver mais de uma, concatenadas de forma hierarquica do maior para o menor";
			$idx++;
			$metadados['idx'.$idx][0] = 'PAR_GAZ_SPEC';
			$metadados['idx'.$idx][1] = "A localidade a qual pertence GAZETTEER_SPEC";
			$idx++;
			$metadados['idx'.$idx][0] = 'PAR_GAZ_DIMx';
			$metadados['idx'.$idx][1] = "A dimensão X da parcela PAR_GAZ_SPEC";
			$idx++;
			$metadados['idx'.$idx][0] = 'PAR_GAZ_DIMy';
			$metadados['idx'.$idx][1] = "A dimensão Y da parcela PAR_GAZ_SPEC";
			$idx++;
			$metadados['idx'.$idx][0] = 'GAZETTEER_SPEC';
			$metadados['idx'.$idx][1] = "A localidade mais especifica da planta";
			$idx++;
			$metadados['idx'.$idx][0] = 'GAZ_DIMx';
			$metadados['idx'.$idx][1] = "A dimensão X da parcela GAZETTEER_SPEC";
			$idx++;
			$metadados['idx'.$idx][0] = 'GAZ_DIMy';
			$metadados['idx'.$idx][1] = "A dimensão Y da parcela GAZETTEER_SPEC";
			$idx++;
			$metadados['idx'.$idx][0] = 'GAZ_Startx';
			$metadados['idx'.$idx][1] = "A posicao X da subparcela GAZETTEER_SPEC na parcela PAR_GAZ_SPEC";
			$idx++;
			$metadados['idx'.$idx][0] = 'GAZ_Starty';
			$metadados['idx'.$idx][1] = "A posicao Y da subparcela GAZETTEER_SPEC na parcela PAR_GAZ_SPEC";
			$idx++;
			$metadados['idx'.$idx][0] = 'Pos_X';
			$metadados['idx'.$idx][1] = "Posicao X da planta marcada em relacao a GAZETTEER_SPEC quando este for uma parcela";
			$idx++;
			$metadados['idx'.$idx][0] = 'Pos_Y';
			$metadados['idx'.$idx][1] = "Posicao Y da planta marcada em relacao a GAZETTEER_SPEC quando este for uma parcela";
			$idx++;
			$metadados['idx'.$idx][0] = 'Pos_LADO';
			$metadados['idx'.$idx][1] = "Para dados da grade do PPBio no PNVirua, indicando o lado das coordenadas X,Y";
			$idx++;
			$metadados['idx'.$idx][0] = 'Referencia';
			$metadados['idx'.$idx][1] = "Qual a referencia dos valores X e Y, em relacao a que vertice da parcela";
			$idx++;
			$metadados['idx'.$idx][0] = 'Pos_DIST';
			$metadados['idx'.$idx][1] = "Distancia em linha reta do GAZETTEER_SPEC a planta marcada, para ser usada em combinacao com Pos_ANGULO, para as situacoes em que GAZETTEER_SPEC e um marco de uma grade, numa trilha, etc.";
			$idx++;
			$metadados['idx'.$idx][0] = 'Pos_ANGULO';
			$metadados['idx'.$idx][1] = "O angulo de direcao da planta a partir do GAZETTEER_SPEC em relacao ao norte magnetico";
			
	$sql .= " FROM Plantas AS pltb LEFT JOIN Gazetteer as gaz ON pltb.GazetteerID=gaz.GazetteerID LEFT JOIN GPS_DATA as gps ON pltb.GPSPointID=gps.PointID WHERE ";

	//FAZ UM LOOP PARA IR MOSTRANDO O PROGRESSO NA EXPORTACAO (EVITA PROBLEMAS DE MEMORIA)
	$qz = "SELECT DISTINCT moni.PlantaID FROM Monitoramento as moni JOIN Plantas AS pl ON moni.PlantaID=pl.PlantaID LEFT JOIN Gazetteer as gaz ON pl.GazetteerID=gaz.GazetteerID LEFT JOIN GPS_DATA as gps ON pl.GPSPointID=gps.PointID WHERE (pl.GazetteerID='".$gazetteerid."' OR gps.GazetteerID='".$gazetteerid."' OR gaz.ParentID='".$gazetteerid."')";
	//WHERE istreeinplot(pl.GazetteerID,pl.GPSPointID, ".$gazetteerid.")>0";
//$fh = fopen("temp/lixao.txt", 'w') or die("Não foi possivel gerar o arquivo");
//fwrite($fh, $sql);
//fclose($fh);
	$rz = mysql_query($qz,$conn);
	//echo $qz."<br>";
	$ntrees = mysql_numrows($rz);
	$idx = 0;
	while ($treesres = mysql_fetch_assoc($rz)) {
		$qqq = $sql." pltb.PlantaID='".$treesres['PlantaID']."'";
		//echo $qqq."<br />"."<br />";
					
		$rsql = mysql_query($qqq, $conn);
		if ($rsql) {
		while($rsw = mysql_fetch_assoc($rsql)) {
			//SALVA OS DADOS NO ARQUIVO TXT
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
				} 
				else {
					$fh = fopen("temp/".$export_filename, 'a') or die("Não foi possivel abrir o arquivo");
				}
				//PARA CADA LINHA
				$line = '';
					//INCLUI OS VALORES DE CADA COLUNA
				$vi = 1;
				$vu = count($rsw);
				foreach($rsw as $value){
						//SE O VALOR FOR UMA DATA VAZIA SUBSTITUI POR NADA
						if ($value=='0000-00-00') {
							$value='';
						}
						//INCLUI O VALOR E O SEPARADOR
						if(!isset($value) || $value == ""){
							$value = "";
						}
						else
							{
							//important to escape any quotes to preserve them in the data.
							$value = str_replace('"', '""', $value);
							//needed to encapsulate data in quotes because some data might be multi line.
							//the good news is that numbers remain numbers in Excel even though quoted.
							$value = "\"".$value ."\"";
						}
						if ($vi==$vu) {
							$line .= $value;
						} else {
							$line .= $value."\t";
						}
						$vi++;
					}
				$lin = trim($line)."\n";
				fwrite($fh, $lin);
				fclose($fh);
		}
		$perc = ceil(($idx/$ntrees)*100);
		$qnu = "UPDATE `temp_exportplotdata.".substr(session_id(),0,10)."` SET percentage=".$perc; 
		//echo $qnu."<br>";
		mysql_query($qnu);
		mysql_free_result($rsql);
		session_write_close();
		$idx++;
	}
}
	// termina de produzir o arquivo de dados
		//se tiver terminado, então salva metadados 
		$fh = fopen("temp/".$export_filename_metadados, 'w') or die("não foi possivel gerar o arquivo");
		$stringData = "COLUNA\tDEFINICAO"; 
		foreach ($metadados as $kk => $vv) {
			$stringData = $stringData."\n".$vv[0]."\t".$vv[1];
		}
		fwrite($fh, $stringData);
		fclose($fh);
		$message=  "100% CONCLUÍDO";
		echo $message;
		session_write_close();
		//$message=  "NÃO EXISTEM DADOS DE ÁRVORES PARA ESSA PARCELA";
		//session_write_close();
} 
else {  // SE NAO HOUVER CENSO
	$message=  "NÃO EXISTEM DADOS DE ÁRVORES PARA ESSA PARCELA";
	echo $message;
	session_write_close();
	//session_write_close();
}
?>