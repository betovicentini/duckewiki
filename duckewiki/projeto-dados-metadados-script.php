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

$qprj = "SELECT * FROM Projetos WHERE ProjetoID=".$projetoid;
$rq = mysql_query($qprj,$conn);
$rqw = mysql_fetch_assoc($rq);
$nomeprj = $rqw['ProjetoNome'];
$morfoformid = explode(";",$rqw['MorformsIDs']);
//echopre($morfoformid);

//$morfoformid = $rqw['MorfoFormID'];
$habitatformid = $rqw['HabitatFormID'];

$qsamples = "SELECT * FROM ProjetosEspecs WHERE ProjetoID=".$projetoid." AND EspecimenID>0";
$rq = mysql_query($qsamples,$conn);
$nsamples = mysql_numrows($rq);
$files = array();
if ($nsamples>0) {
///PREPARA ARQUIVOS COM DADOS DE ESPECIMENES
$metadados = array();
$idx=0;
$qq = "SELECT pltb.EspecimenID AS WikiEspecimenID, 
UPPER(CONCAT(TRIM(SUBSTRING(colpessoa.SobreNome,1,3)),TRIM(pltb.Number),IF (pltb.Ano>0,pltb.Ano,''))) as IDENTIFICADOR, 
colpessoa.Abreviacao as COLLECTOR, 
pltb.Number as NUMBER"; 
$metadados['idx'.$idx][0] = 'WikiEspecimenID';
$metadados['idx'.$idx][1] = 'Identificador Amostra do Wiki';
$idx++;
$metadados['idx'.$idx][0] = 'IDENTIFICADOR';
$metadados['idx'.$idx][1] = 'Um identificador curto para a amostra que é composto das três primeiras letras do sobrenome do coletor e do numero e ano de coleta';
$idx++;
$metadados['idx'.$idx][0] = 'COLLECTOR';
$metadados['idx'.$idx][1] = 'Nome do coletor da amostra';
$idx++;
$metadados['idx'.$idx][0] = 'NUMBER';
$metadados['idx'.$idx][1] = 'Número de coleta do coletor';
$idx++;
$qq .= ", IF (pltb.Day>0,pltb.Day,'')  as COLLDD, IF (pltb.Mes>0,pltb.Mes,'')  as COLLMM, IF (pltb.Ano>0,pltb.Ano,'')  as COLLYY";
$metadados['idx'.$idx][0] = 'COLLDD';
$metadados['idx'.$idx][1] = 'Dia em que a amostra foi coletada';
$idx++;
$metadados['idx'.$idx][0] = 'COLLMM';
$metadados['idx'.$idx][1] = 'Mes em que a amostra foi coletada';
$idx++;
$metadados['idx'.$idx][0] = 'COLLYY';
$metadados['idx'.$idx][1] = 'Ano em que a amostra foi coletada';
$idx++;
$metadados['idx'.$idx][0] = 'FAMILY';
$metadados['idx'.$idx][1] = 'Familia botanica';
$idx++;
$metadados['idx'.$idx][0] = 'GENUS';
$metadados['idx'.$idx][1] = 'Genero botanico, onde Indet= indeterminado nesse nivel';
$idx++;
$metadados['idx'.$idx][0] = 'SPECIES';
$metadados['idx'.$idx][1] = 'Epiteto da especie';
$idx++;
$metadados['idx'.$idx][0] = 'INFRASPECIES';
$metadados['idx'.$idx][1] = 'Nome da categoria infra-especifica';
$idx++;
$qq .=", 
famtb.Familia as FAMILY, 
gentb.Genero as GENUS, 
sptb.Especie as SPECIES, 
infsptb.InfraEspecie as INFRASPECIES, 
getidentidade(pltb.DetID, 0, 0, 0,0, 1) as NOME";
$metadados['idx'.$idx][0] = 'NOME';
$metadados['idx'.$idx][1] = 'Taxonomia no nivel de identificacao sem autores';
$idx++;
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

$metadados['idx'.$idx][0] = 'LONGITUDE';
$metadados['idx'.$idx][1] = "Longitude em decimos de grau, com valores negativos para posicoes W e valores positivos para posicoes E";
$idx++;
$metadados['idx'.$idx][0] = 'LATITUDE';
$metadados['idx'.$idx][1] = "Latitude em decimos de grau, com valores negativos para posicoes S e valores positivos para posicoes N";
$idx++;
$metadados['idx'.$idx][0] = 'ALTITUDE';
$metadados['idx'.$idx][1] = "Altitude em m sobre o nivel do mar";
$idx++;
$qq .= ", 
getlatlongdms(pltb.Latitude, pltb.Longitude, pltb.GPSPointID, pltb.GazetteerID, pltb.MunicipioID, pltb.ProvinceID, CountryID, 1,5)  as LATITUDE,
getlatlongdms(pltb.Latitude, pltb.Longitude, pltb.GPSPointID, pltb.GazetteerID, pltb.MunicipioID, pltb.ProvinceID,pltb.CountryID, 0,5)  as LONGITUDE,
getaltitude(pltb.Altitude, pltb.GPSPointID,pltb.GazetteerID) as ALTITUDE";
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
if ($traitfertid>0) {
	$qq .=", traitvaluespecs(".$traitfertid.",pltb.PlantaID,pltb.EspecimenID,'m',0,0) as FERTILIDADE";
	$metadados['idx'.$idx][0] = "FERTILIDADE";
	$metadados['idx'.$idx][1] = "Tipo de material coletado";
	$idx++;
}
$qq .= " FROM ProjetosEspecs as oprj JOIN Especimenes as pltb ON pltb.EspecimenID=oprj.EspecimenID";
$qq .= " LEFT JOIN Pessoas as colpessoa ON pltb.ColetorID=colpessoa.PessoaID";
$qq .= "
LEFT JOIN Identidade as iddet ON pltb.DetID=iddet.DetID 
LEFT JOIN Tax_InfraEspecies as infsptb ON iddet.InfraEspecieID=infsptb.InfraEspecieID 
LEFT JOIN Tax_Especies as sptb ON iddet.EspecieID=sptb.EspecieID 
LEFT JOIN Tax_Generos as gentb ON iddet.GeneroID=gentb.GeneroID  
LEFT JOIN Tax_Familias as famtb ON iddet.FamiliaID=famtb.FamiliaID ";
$qq .=  " WHERE oprj.ProjetoID=".$projetoid;

$rsql = mysql_query($qq, $conn);
$nrsql = mysql_numrows($rsql);

$qnu = "UPDATE `temp_projdadosmeta.".substr(session_id(),0,10)."` SET percentage=1"; 
mysql_query($qnu);
session_write_close();

$export_filename = "dadosAmostras_".$projetoid.".csv";
$metadados_file =  "dadosAmostras_".$projetoid."_metadados.csv";
$fdate = @date ("Y-m-d", filemtime("temp/".$export_filename));
$cdate = @date("Y-m-d");
if ($nrsql>0 && $fdate!==$cdate) {
$perctot1 = 50; 
$idx=0;
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
		$idx++;
}
$fh = fopen("temp/".$metadados_file, 'w') or die("não foi possivel gerar o arquivo");
$stringData = "\"COLUNA\"\t\"DEFINICAO\""; 
foreach ($metadados as $kk => $vv) {
		$stringData = $stringData."\n\"".$vv[0]."\"\t\"".$vv[1]."\"";
}
fwrite($fh, $stringData);
fclose($fh);
}
$perc = 40;
$qnu = "UPDATE `temp_projdadosmeta.".substr(session_id(),0,10)."` SET percentage=".$perc; 
mysql_query($qnu);
session_write_close();

//////////////////////////////

foreach($morfoformid as $mfid) {
//$export_filename = "dadosMorfo_".$projetoid.".csv";
//$metadados_file =   "dadosMorfo_".$projetoid."_metadados.csv";
$export_filename  = "dados_form-".$mfid."_projeto-".$projetoid.".csv";
$metadados_file = "dados_form-".$mfid."_projeto-".$projetoid."_metadados.csv";
$fdate = @date ("Y-m-d", filemtime("temp/".$export_filename));
$cdate = @date("Y-m-d");
//&& $fdate!==$cdate
if ($mfid>0 ) {
$fmean=0;
$qu = "SELECT tr.TraitID,tr.TraitTipo,tr.TraitName,tr.TraitUnit,tr.TraitDefinicao,prt.TraitName as ParentName FROM FormulariosTraitsList AS fr JOIN Traits as tr ON tr.TraitID=fr.TraitID INNER JOIN Traits as prt ON tr.ParentID=prt.TraitID WHERE  fr.FormID=".$mfid." ORDER BY fr.Ordem";
//echo $qu."<br />";
$ruw = mysql_query($qu,$conn);
$qq = "SELECT pltb.EspecimenID AS WikiEspecimenID ";
$metadados = array();
$idx=0;
while($rwuw = mysql_fetch_assoc($ruw)) {
			//echopre($rwuw);
			$tid = $rwuw['TraitID'];
			$ttipo = $rwuw['TraitTipo'];
			$ttname = trim($rwuw['TraitName']);
			$tdefini = trim($rwuw['TraitDefinicao']);
			$parn = RemoveAcentos($rwuw['ParentName']);
			$val = str_replace("  ", " ", $parn);
			$val = str_replace("  ", " ", $val);
			$symb = array(" ", ".",'/',"-",")","(",";");
			$val  = str_replace($symb, "", $val);
			$val = str_replace(" ", "", $val);
			$parn = strtoupper(substr($val, 0, 3));
			$tascol = RemoveAcentos($rwuw['TraitName']);
			$symb = array(".",'/',"-",")","(",";");
			$val  = str_replace($symb, "", $tascol);
			$val = str_replace("  ", " ", $val);
			$val = str_replace("  ", " ", $val);
			$trn = explode(" ",$val);
			$newn = array();
			foreach($trn as $str) {
				$val = trim($str);
				$tr = strtoupper(substr($val, 0, 3));
				$newn[] = $tr;
			}
			$trn = implode("",$newn);
			$tascol = $parn."_".$trn;
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
			} 
			else {
				$tti = explode("|",$ttipo);
				$tdef = $ttname.". (".$tdefini."). Variavel ".$tti[1].".";
				$tdef = str_replace("..",".",$tdef);
				$tdef = str_replace(".)",")",$tdef);
				$metadados['idx'.$idx][1] = $tdef;
				$idx++;
				$qq .= ", traitvariation_specimens($tid,pltb.EspecimenID,0,0) AS ".$tascol;
			}
}
$qq .= " FROM ProjetosEspecs as oprj JOIN Especimenes as pltb ON pltb.EspecimenID=oprj.EspecimenID";
$qq .=  " WHERE oprj.ProjetoID=".$projetoid;
//echo $qq."<br />";
$rsql2 = mysql_query($qq, $conn);
$nrsql = mysql_numrows($rsql2);
if ($nrsql>0) {
//echo mysql_num_fields($rsql);
$idx=0;
while($rsw2 = mysql_fetch_assoc($rsql2)) {
	 //echopre($rsw2);
	//SALVA OS DADOS NO ARQUIVO TXT
		if ($idx==0) {
			$fhh = fopen("temp/".$export_filename, 'w') or die("Não foi possivel gerar o arquivo");
			//fwrite($fhh, "este é um teste\n");
			$count = mysql_num_fields($rsql2);
			$header = '';
			for ($i = 0; $i < $count; $i++){
			     if ($i==($count-1)) {
					$header .= "\"".mysql_field_name($rsql2, $i)."\"";
				 } else {
					 $header .= "\"".mysql_field_name($rsql2, $i)."\"\t";
				 }
			}
			$header .= "\n";
			//echo $header."<br />";
			fwrite($fhh, $header);
			//session_write_close();
		} 
		else {
			$fhh = fopen("temp/".$export_filename, 'a') or die("Não foi possivel abrir o arquivo");
		}
		//PARA CADA LINHA
		$line = '';
			//INCLUI OS VALORES DE CADA COLUNA
		$vi = 1;
		$vu = count($rsw2);
		foreach($rsw2 as $value){
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
		fwrite($fhh, $lin);
		fclose($fhh);
		$idx++;
		//$perc = $perc+((15*$idx)/$nrsql);
		//$qnu = "UPDATE `temp_projdadosmeta.".substr(session_id(),0,10)."` SET percentage=".$perc; 
		//mysql_query($qnu);
		//session_write_close();
}
//fclose($fh);
//$qnu = "UPDATE `temp_projdadosmeta.".substr(session_id(),0,10)."` SET percentage=30"; 
//mysql_query($qnu);
//session_write_close();
$fh = fopen("temp/".$metadados_file, 'w') or die("não foi possivel gerar o arquivo");
$stringData = "\"COLUNA\"\t\"DEFINICAO\""; 
foreach ($metadados as $kk => $vv) {
		$stringData = $stringData."\n\"".$vv[0]."\"\t\"".$vv[1]."\"";
}
fwrite($fh, $stringData);
fclose($fh);
}

} 

}


$perc = 60;
$qnu = "UPDATE `temp_projdadosmeta.".substr(session_id(),0,10)."` SET percentage=".$perc; 
mysql_query($qnu);
session_write_close();
$export_filename = "dadosAmbientais_".$projetoid.".csv";
$metadados_file =   "dadosAmbientais_".$projetoid."_metadados.csv";
$fdate = @date ("Y-m-d", filemtime("temp/".$export_filename));
$cdate = @date("Y-m-d");
if ($habitatformid>0 && $fdate!==$cdate) {
$fmean=0;
$qu = "SELECT tr.TraitID,tr.TraitTipo,tr.TraitName,tr.TraitUnit,tr.TraitDefinicao,prt.TraitName as ParentName FROM FormulariosTraitsList AS fr JOIN Traits as tr ON tr.TraitID=fr.TraitID INNER JOIN Traits as prt ON tr.ParentID=prt.TraitID WHERE  fr.FormID=".$habitatformid." ORDER BY fr.Ordem";
$ruw = mysql_query($qu,$conn);
$qq = "SELECT pltb.EspecimenID AS WikiEspecimenID ";
$metadados = array();
$idx=0;
while($rwuw = mysql_fetch_assoc($ruw)) {
			$tid = $rwuw['TraitID'];
			$ttipo = $rwuw['TraitTipo'];
			$ttname = trim($rwuw['TraitName']);
			$tdefini = trim($rwuw['TraitDefinicao']);
			$parn = RemoveAcentos($rwuw['ParentName']);
			$val = str_replace("  ", " ", $parn);
			$val = str_replace("  ", " ", $val);
			$symb = array(" ", ".",'/',"-",")","(",";");
			$val  = str_replace($symb, "", $val);
			$val = str_replace(" ", "", $val);
			$parn = strtoupper(substr($val, 0, 3));
			$tascol = RemoveAcentos($rwuw['TraitName']);
			$symb = array(".",'/',"-",")","(",";");
			$val  = str_replace($symb, "", $tascol);
			$val = str_replace("  ", " ", $val);
			$val = str_replace("  ", " ", $val);
			$trn = explode(" ",$val);
			$newn = array();
			foreach($trn as $str) {
				$val = trim($str);
				$tr = strtoupper(substr($val, 0, 3));
				$newn[] = $tr;
			}
			$trn = implode("",$newn);
			$tascol = $parn."_".$trn;
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
			} 
			else {
				$tti = explode("|",$ttipo);
				$tdef = $ttname.". (".$tdefini."). Variavel ".$tti[1].".";
				$tdef = str_replace("..",".",$tdef);
				$tdef = str_replace(".)",")",$tdef);
				$metadados['idx'.$idx][1] = $tdef;
				$idx++;
				$qq .= ", traitvariation_specimens($tid,pltb.EspecimenID,0,0) AS ".$tascol;
			}
}
$qq .= " FROM ProjetosEspecs as oprj JOIN Especimenes as pltb ON oprj.EspecimenID=pltb.EspecimenID ";
$qq .=  " WHERE oprj.ProjetoID=".$projetoid;
//echo $qq."<br />";
$rsql = mysql_query($qq, $conn);
$nrsql = mysql_numrows($rsql);
if ($nrsql>0) {
$idx=0;
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
		$idx++;
		//$perc = $perc+((15*$idx)/$nrsql);
		//$qnu = "UPDATE `temp_projdadosmeta.".substr(session_id(),0,10)."` SET percentage=".$perc; 
		//mysql_query($qnu);
		//session_write_close();

}
//$qnu = "UPDATE `temp_projdadosmeta.".substr(session_id(),0,10)."` SET percentage=30"; 
//mysql_query($qnu);
//session_write_close();
$fh = fopen("temp/".$metadados_file, 'w') or die("não foi possivel gerar o arquivo");
$stringData = "\"COLUNA\"\t\"DEFINICAO\""; 
foreach ($metadados as $kk => $vv) {
		$stringData = $stringData."\n\"".$vv[0]."\"\t\"".$vv[1]."\"";
}
fwrite($fh, $stringData);
fclose($fh);
}
} 
$perc = 80;
$qnu = "UPDATE `temp_projdadosmeta.".substr(session_id(),0,10)."` SET percentage=".$perc; 
mysql_query($qnu);
session_write_close();


//CHECA SE TEM DADOS MOLECULARE E SE TIVER GERA FASTAS
$qmol = "SELECT Marcador, count(*) as Namostras FROM MolecularData JOIN ProjetosEspecs as prj USING(EspecimenID) WHERE prj.ProjetoID=".$projetoid." GROUP BY Marcador";
$rmol = @mysql_query($qmol, $conn);
$nrmol = @mysql_numrows($rmol);
//
if ($nrmol>0) {
	$idx=0;
	 while($rwmol = mysql_fetch_assoc($rmol)) {
	 	if ($rwmol['Namostras']>0) {
	 		$mark= $rwmol['Marcador'];
	 		$mark = str_replace(" ","-",$mark);
	 		
	 		 $stringData = '';
	 		$stringData2 = "\"WikiEspecimenID\"\t\"NOMEsequencia\"\t\"FAMILY\"\t\"GENUS\"\t\"SPECIES\"\t\"INFRASPECIES\"\t\"NOME\"\t\"NCBI\"";
			$export_filename = "dadosMoleculares_".$mark."_".$projetoid.".fasta";
			$metadados_file =   "dadosMoleculares_".$mark."_".$projetoid."_metadados.csv";
			$sql = "SELECT DISTINCT ProjetosEspecs.EspecimenID FROM ProjetosEspecs JOIN MolecularData USING(EspecimenID) WHERE Marcador='".$rwmol['Marcador']."' AND ProjetoID=".$projetoid."";
			//echo $sql."<br />";
			$rs = mysql_query($sql,$conn);
			while($rsw = mysql_fetch_assoc($rs)) {
				$sqlmol = "SELECT mol.EspecimenID AS WikiEspecimenID,
famtb.Familia as FAMILY, 
gentb.Genero as GENUS, 
sptb.Especie as SPECIES, 
infsptb.InfraEspecie as INFRASPECIES, 
getidentidade(pltb.DetID, 0, 0, 0,0, 1) as NOME,
mol.Sequencia,
mol.NCBI,
mol.Marcador,
mol.Best
FROM MolecularData as mol JOIN  Especimenes as pltb ON pltb.EspecimenID=mol.EspecimenID 
LEFT JOIN Identidade as iddet ON pltb.DetID=iddet.DetID 
LEFT JOIN Tax_InfraEspecies as infsptb ON iddet.InfraEspecieID=infsptb.InfraEspecieID 
LEFT JOIN Tax_Especies as sptb ON iddet.EspecieID=sptb.EspecieID 
LEFT JOIN Tax_Generos as gentb ON iddet.GeneroID=gentb.GeneroID  
LEFT JOIN Tax_Familias as famtb ON iddet.FamiliaID=famtb.FamiliaID
WHERE mol.Marcador LIKE '".$rwmol['Marcador']."' AND mol.EspecimenID=".$rsw['EspecimenID'];
				//echo $sqlmol."<br />";
				$rss = mysql_query($sqlmol,$conn);
				$nrss = mysql_numrows($rss);
				if ($nrss>1) {
					$sqlmol2 = $sqlmol." AND mol.BEST=1";
					$rss = mysql_query($sqlmol2,$conn);
					//echo $sqlmol."<br />";
					$nrss = mysql_numrows($rss);
					if ($nrss==0) {
						$sqlmol2 = $sqlmol." LIMIT 0,1";
						//echo $sqlmol."<br />";
						$rss = mysql_query($sqlmol2,$conn);
					}
				}
				$nrss = mysql_numrows($rss);
				if ($nrss==1) {
					$moldat = mysql_fetch_assoc($rss);
					$gg = strtoupper(substr($moldat['GENUS'],0,1));
					//$gg = $moldat['GENUS'];
					$spp = strtolower($moldat['SPECIES']);
					$spp = str_replace("sp. nov.","",$spp);
					$spp = trim($spp);
					$spp = str_replace("."," ",$spp);
					$spp = str_replace("  "," ",$spp);
					$spp = str_replace(" ","-",$spp);
					//$spp = $spp,0,8);
					$infspp = trim(strtolower($moldat['INFRASPECIES']));
					$infspp = str_replace("."," ",$infspp);
					$infspp = str_replace("  "," ",$infspp);
					$infspp = str_replace(" ","-",$infspp);
					if (!empty($infspp)) {
						$spp1 = substr($spp.$infspp,0,14);
						$nt1 = $gg.$spp1."_".$moldat['WikiEspecimenID'];
					} else {
						if (!empty($spp)) {
							$spp1 = substr($spp,0,14);
							$nt1 = $gg.$spp1."_".$moldat['WikiEspecimenID'];
						} else {
							$nt1 = $moldat['GENUS']."_".$moldat['WikiEspecimenID'];
						}
					}
					$stringData .= ">".$nt1."\n";
					$seq = trim($moldat['Sequencia']);
					$stringData .= $seq."\n";
					$stringData2 .= "\n\"".$moldat['WikiEspecimenID']."\"\t\"".$nt1."\"\t\"".$moldat['FAMILY']."\"\t\"".$moldat['GENUS']."\"\t\"".$moldat['SPECIES']."\"\t\"".$moldat['INFRASPECIES']."\"\t\"".$moldat['NOME']."\"\t\"".$moldat['NCBI']."\"";
				}
		}
		$fh = fopen("temp/".$export_filename, 'w') or die("não foi possivel gerar o arquivo");
		fwrite($fh, $stringData);
		fclose($fh);
		$fh = fopen("temp/".$metadados_file, 'w') or die("não foi possivel gerar o arquivo");
		fwrite($fh, $stringData2);
		fclose($fh);
 	}
 	//$perc = $perc+((15*$idx)/$nrmol);
	//$qnu = "UPDATE `temp_projdadosmeta.".substr(session_id(),0,10)."` SET percentage=".$perc; 
	//mysql_query($qnu);
	//session_write_close();
	$idx++;
 }
} 
else {
	//$perc = $perc+((15*$idx)/$nrmol);
	//$qnu = "UPDATE `temp_projdadosmeta.".substr(session_id(),0,10)."` SET percentage=".$perc; 
	//mysql_query($qnu);
	//session_write_close();
}
$perc = 100;
$qnu = "UPDATE `temp_projdadosmeta.".substr(session_id(),0,10)."` SET percentage=".$perc; 
mysql_query($qnu);
echo "CONCLUIDO";
session_write_close();
 
} else {
echo "NAO EXISTEM DADOS PARA ESTE PROJETO";
session_write_close();
}

?>