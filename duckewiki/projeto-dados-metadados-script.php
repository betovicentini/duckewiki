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

$qnu = "UPDATE `temp_projdadosmeta".substr(session_id(),0,10)."` SET percentage=1"; 
mysql_query($qnu);
session_write_close();

$export_filename = "dadosAmostras_".$projetoid.".csv";
$metadados_file =  "dadosAmostras_".$projetoid."_metadados.csv";
$fdate = @date ("Y-m-d", filemtime("temp/".$export_filename));
$cdate = @date("Y-m-d");
// && $fdate!==$cdate
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
		//$perc = ($idx/$nrsql)*100;
		//$perc = ($perc/5)+1;
		//$qnu = "UPDATE `temp_projdadosmeta".substr(session_id(),0,10)."` SET percentage=".$perc; 
		//mysql_query($qnu,$conn);
		//session_write_close();
}
$fh = fopen("temp/".$metadados_file, 'w') or die("não foi possivel gerar o arquivo");
$stringData = "\"COLUNA\"\t\"DEFINICAO\""; 
foreach ($metadados as $kk => $vv) {
		$stringData = $stringData."\n\"".$vv[0]."\"\t\"".$vv[1]."\"";
}
fwrite($fh, $stringData);
fclose($fh);

$perc = 20;
$qnu = "UPDATE `temp_projdadosmeta".substr(session_id(),0,10)."` SET percentage=".$perc; 
mysql_query($qnu);
session_write_close();
//////////////////////////////

$npart = count($morfoformid);
$perform = 30/$npart;
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
		//$perc2 = ($idx/$nrsql)*100;
		//$perc = (($perc2*$perform)/100)+$perc;
		//$qnu = "UPDATE `temp_projdadosmeta".substr(session_id(),0,10)."` SET percentage=".$perc; 
		//mysql_query($qnu,$conn);
		//session_write_close();
		
		//$perc = $perc+((15*$idx)/$nrsql);
		//$qnu = "UPDATE `temp_projdadosmeta".substr(session_id(),0,10)."` SET percentage=".$perc; 
		//mysql_query($qnu);
		//session_write_close();
}
//fclose($fh);
//$qnu = "UPDATE `temp_projdadosmeta".substr(session_id(),0,10)."` SET percentage=30"; 
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
$perc = 30;
$qnu = "UPDATE `temp_projdadosmeta".substr(session_id(),0,10)."` SET percentage=".$perc; 
mysql_query($qnu);
session_write_close();

$export_filename = "dadosAmbientais_".$projetoid.".csv";
$metadados_file =   "dadosAmbientais_".$projetoid."_metadados.csv";
$fdate = @date ("Y-m-d", filemtime("temp/".$export_filename));
$cdate = @date("Y-m-d");
//&& $fdate!==$cdate
if ($habitatformid>0 ) {
$fmean=0;
$qu = "SELECT tr.TraitID,tr.TraitTipo,tr.TraitName,tr.TraitUnit,tr.TraitDefinicao,prt.TraitName as ParentName FROM FormulariosTraitsList AS fr JOIN Traits as tr ON tr.TraitID=fr.TraitID INNER JOIN Traits as prt ON tr.ParentID=prt.TraitID WHERE  fr.FormID=".$habitatformid." ORDER BY fr.Ordem";
$ruw = mysql_query($qu,$conn);
$qq = "SELECT pltb.EspecimenID AS WikiEspecimenID ";
$metadados = array();
$idx=0;
$perform = 10;
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
		//$perc2 = ($idx/$nrsql)*100;
		//$perc = (($perc2*$perform)/100)+$perc;
		//$qnu = "UPDATE `temp_projdadosmeta".substr(session_id(),0,10)."` SET percentage=".$perc; 
		//mysql_query($qnu,$conn);
		//session_write_close();
}
//$qnu = "UPDATE `temp_projdadosmeta".substr(session_id(),0,10)."` SET percentage=30"; 
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
$perc = 40;
$qnu = "UPDATE `temp_projdadosmeta".substr(session_id(),0,10)."` SET percentage=".$perc; 
mysql_query($qnu);
session_write_close();


//CHECA SE TEM DADOS MOLECULARE E SE TIVER GERA FASTAS
$qmol = "SELECT Marcador, count(*) as Namostras FROM MolecularData JOIN ProjetosEspecs as prj USING(EspecimenID) WHERE prj.ProjetoID=".$projetoid." GROUP BY Marcador";
$rmol = @mysql_query($qmol, $conn);
$nrmol = @mysql_numrows($rmol);
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
	//$qnu = "UPDATE `temp_projdadosmeta".substr(session_id(),0,10)."` SET percentage=".$perc; 
	//mysql_query($qnu);
	//session_write_close();
	$idx++;
 }
} 
else {
	//$perc = $perc+((15*$idx)/$nrmol);
	//$qnu = "UPDATE `temp_projdadosmeta".substr(session_id(),0,10)."` SET percentage=".$perc; 
	//mysql_query($qnu);
	//session_write_close();
}
$perc = 50;
$qnu = "UPDATE `temp_projdadosmeta".substr(session_id(),0,10)."` SET percentage=".$perc; 
mysql_query($qnu,$conn);
//echo "CONCLUIDO";
session_write_close();
 
} 
}

$qsamples = "SELECT * FROM ProjetosEspecs WHERE ProjetoID=".$projetoid." AND PlantaID>0";
//echo $qsamples."<br />";
$rq = mysql_query($qsamples,$conn);
$nplantas = mysql_numrows($rq);
if ($nplantas>0) {
///PREPARA ARQUIVOS COM DADOS DE PLANTAS
$metadadospl = array();
$idx=0;

$qq = " SELECT 
pltb.PlantaID AS WikiPlantaID, 
pltb.PlantaTag as TAG_NUM"; 
$idx=0;
$metadadospl['idx'.$idx][0] = 'WikiPlantaID';
$metadadospl['idx'.$idx][1] = 'Identificador da planta na base de dados onde estão os dados';
$idx++;
$metadadospl['idx'.$idx][0] = 'TAG_NUM';
$metadadospl['idx'.$idx][1] = 'Número da placa da árvore';
$idx++;
$metadadospl['idx'.$idx][0] = 'NOME';
$metadadospl['idx'.$idx][1] = 'Taxonomia no nivel de identificacao sem autores';
$idx++;
$qq .=", gettaxonname(pltb.DetID,1,0) as NOME";
$metadadospl['idx'.$idx][0] = 'NOME_AUTOR';
$metadadospl['idx'.$idx][1] = 'Taxonomia no nivel de identificacao, se em nivel de especie ou de infraespecie, nome dos autores incluidos';
$idx++;
$qq .=", gettaxonname(pltb.DetID,1,1) as NOME_AUTOR";
$metadadospl['idx'.$idx][0] = 'FAMILY';
$metadadospl['idx'.$idx][1] = 'Familia botanica';
$idx++;
$metadadospl['idx'.$idx][0] = 'GENUS';
$metadadospl['idx'.$idx][1] = 'Genero botanico, onde Indet= indeterminado nesse nivel';
$idx++;
$metadadospl['idx'.$idx][0] = 'SP1';
$metadadospl['idx'.$idx][1] = 'Epiteto da especie';
$idx++;
$metadadospl['idx'.$idx][0] = 'AUTHOR1';
$metadadospl['idx'.$idx][1] =  'Autoridade do nome da especie';
$idx++;
$metadadospl['idx'.$idx][0] = 'SP1_MORFOTIPO';
$metadadospl['idx'.$idx][1] =  'Se o nome sp1 é morfotipo não publicado';
$idx++;
$metadadospl['idx'.$idx][0] = 'RANK1';
$metadadospl['idx'.$idx][1] = 'Categoria de nivel infra-especifico, variedade, subespecie, forma, etc.';
$idx++;
$metadadospl['idx'.$idx][0] = 'SP2';
$metadadospl['idx'.$idx][1] = 'Nome da categoria infra-especifica';
$idx++;
$metadadospl['idx'.$idx][0] = 'AUTHOR2';
$metadadospl['idx'.$idx][1] =  'Autoridade do nome da categoria infra-especifica';
$idx++;
$metadadospl['idx'.$idx][0] = 'SP2_MORFOTIPO';
$metadadospl['idx'.$idx][1] =  'Se o nome sp2 é morfotipo não publicado';
$idx++;
$metadadospl['idx'.$idx][0] = 'CF';
$metadadospl['idx'.$idx][1] = "Modificadores de nome como aff. cf. vel. aff. etc, indicado por quem fez a identificacao";
$idx++;
$metadadospl['idx'.$idx][0] = 'DETBY';
$metadadospl['idx'.$idx][1] = "Nome da pessoa que fez a identificacao";
$idx++;
$metadadospl['idx'.$idx][0] = 'DETDATA';
$metadadospl['idx'.$idx][1] = "Data de identificacao";
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
iddet.DetDate as DETDATA";

$metadadospl['idx'.$idx][0] = 'COUNTRY';
$metadadospl['idx'.$idx][1] = "Nome do pais";
$idx++;
$metadadospl['idx'.$idx][0] = 'MAJORAREA';
$metadadospl['idx'.$idx][1] = "Nome da primeira subdivisao administrativa do pais, provincias, estados, departamentos, cantoes, dependendo do pais";
$idx++;
$metadadospl['idx'.$idx][0] = 'MINORAREA';
$metadadospl['idx'.$idx][1] = "Nome da subdivisao de MAJORAREA, geralmente os municipios ou condados";
$idx++;
$metadadospl['idx'.$idx][0] = 'GAZETTEER_CURTO';
$metadadospl['idx'.$idx][1] = "Primeira divisão de MINORAREA";
$idx++;
$metadadospl['idx'.$idx][0] = 'GAZETTEER_COMPLETA';
$metadadospl['idx'.$idx][1] = "As demais subdivisoes de MINORAREA. Quando houver mais de uma, concatenadas de forma hierarquica do maior para o menor";
$idx++;
$metadadospl['idx'.$idx][0] = 'GAZETTEER_SPECIFIC';
$metadadospl['idx'.$idx][1] = "A localidade mais especifica da planta coletada";
$idx++;
$qq .= ",  
localidadefields(pltb.GazetteerID, pltb.GPSPointID,0, 0,0,'COUNTRY')  as COUNTRY,
localidadefields(pltb.GazetteerID, pltb.GPSPointID,0, 0,0,'MINORAREA')  as MINORAREA, 
localidadefields(pltb.GazetteerID, pltb.GPSPointID,0, 0,0,'MAJORAREA')  as MAJORAREA,
localidadefields(pltb.GazetteerID, pltb.GPSPointID,0, 0,0,'GAZfirstPARENT')  as GAZETTEER_CURTO,
localidadefields(pltb.GazetteerID, pltb.GPSPointID,0, 0,0,'GAZETTEER')  as GAZETTEER_COMPLETA,
localidadefields(pltb.GazetteerID, pltb.GPSPointID,0, 0,0,'GAZETTEER_SPEC')  as GAZETTEER_SPECIFIC";

$metadadospl['idx'.$idx][0] = 'LONGITUDE';
$metadadospl['idx'.$idx][1] = "Longitude em decimos de grau, com valores negativos para posicoes W e valores positivos para posicoes E";
$idx++;
$metadadospl['idx'.$idx][0] = 'LATITUDE';
$metadadospl['idx'.$idx][1] = "Latitude em decimos de grau, com valores negativos para posicoes S e valores positivos para posicoes N";
$idx++;
$metadadospl['idx'.$idx][0] = 'ALTITUDE';
$metadadospl['idx'.$idx][1] = "Altitude em m sobre o nivel do mar";
$idx++;
			//$metadadospl['idx'.$idx][0] = 'COORD_PRECISION';
			//$metadadospl['idx'.$idx][1] = "Precisao das coordenadas, onde GPS_planta indica que a coordenada e do individuo; GPS quando for a coordenada de um ponto de GPS da proximidade do individuo; Localidade indica que a coordenada se refere a uma das localidades em GAZETTEER; Municipio e a coordenada de MINORAREA";
			//$idx++;
$qq .= ", 
getlatlongdms(pltb.Latitude, pltb.Longitude, pltb.GPSPointID, pltb.GazetteerID, 0, 0, 0, 1,5)  as LATITUDE,
getlatlongdms(pltb.Latitude, pltb.Longitude, pltb.GPSPointID, pltb.GazetteerID, 0, 0,0, 0,5)  as LONGITUDE,
getaltitude(pltb.Altitude, pltb.GPSPointID,pltb.GazetteerID) as ALTITUDE";

$metadadospl['idx'.$idx][0] = 'Pos_X';
$metadadospl['idx'.$idx][1] = "Posicao X da planta marcada em relacao ao GAZETTEER_SPECIFIC quando este for uma parcela";
$idx++;
$metadadospl['idx'.$idx][0] = 'Pos_Y';
$metadadospl['idx'.$idx][1] = "Posicao Y da planta marcada em relacao ao GAZETTEER_SPECIFIC quando este for uma parcela";
$idx++;
$metadadospl['idx'.$idx][0] = 'Pos_LADO';
$metadadospl['idx'.$idx][1] = "Para dados da grade do PPBio no PNVirua, indicando o lado das coordenadas X,Y";
$idx++;
$metadadospl['idx'.$idx][0] = 'Referencia';
$metadadospl['idx'.$idx][1] = "Qual a referencia dos valores X e Y, em relacao a que vertice da parcela";
$idx++;
$metadadospl['idx'.$idx][0] = 'Pos_DIST';
$metadadospl['idx'.$idx][1] = "Distancia em linha reta do GAZETTEER_SPECIFIC a planta marcada, para ser usada em combinacao com Pos_ANGULO, para as situacoes em que GAZETTEER_SPECIFIC e um marco de uma grade, numa trilha, etc.";
$idx++;
$metadadospl['idx'.$idx][0] = 'Pos_ANGULO';
$metadadospl['idx'.$idx][1] = "O angulo de direcao da planta a partir do GAZETTEER_SPECIFIC em relacao ao norte magnetico";
$idx++;
$qq .= ",  
pltb.X as Pos_X, 
pltb.Y as Pos_Y, 
pltb.LADO as Pos_LADO, 
pltb.Referencia as Pos_REF, 
pltb.Distancia as Pos_DIST, 
pltb.Angulo as Pos_ANGULO";

$qq .= ", habitaclasse(pltb.HabitatID) AS  HABITAT_CLASSE";
$metadadospl['idx'.$idx][0] = "HABITAT_CLASSE";
$metadadospl['idx'.$idx][1] = "Classe de habitat ou ambiente da planta";
$idx++;

$qq .= ", vernaculars(pltb.VernacularIDS) as NOME_VULGAR";
$metadadospl['idx'.$idx][0] = "NOME_VULGAR";
$metadadospl['idx'.$idx][1] = "Nome vulgar registrado para o indivíduo";
$idx++;

$qq .=", addcolldescr(pltb.TaggedBy) as MARCADO_POR";
$metadadospl['idx'.$idx][0] = "MARCADO_POR";
$metadadospl['idx'.$idx][1] = 'Nome das pessoas que marcaram as árvores no campo';
$idx++;
$qq .= ", pltb.TaggedDate as DATA_MARCACAO";
$metadadospl['idx'.$idx][0] = "DATA_MARCACAO";
$metadadospl['idx'.$idx][1] = 'Data da marcação da planta no campo';
$idx++;

$qq .= ", projetostringbrahmsnovo(0,pltb.PlantaID) as PROJETO";
$metadadospl['idx'.$idx][0] = "PROJETO";
$metadadospl['idx'.$idx][1] = 'Projeto a que se refere o trabalho';
$idx++;

$qq .= " FROM ProjetosEspecs as oprj JOIN Plantas as pltb ON pltb.PlantaID=oprj.PlantaID";
//$qq = $qq." FROM Plantas as pltb";
$qq .= " 
LEFT JOIN Identidade as iddet ON pltb.DetID=iddet.DetID 
LEFT JOIN Tax_InfraEspecies as infsptb ON iddet.InfraEspecieID=infsptb.InfraEspecieID 
LEFT JOIN Tax_Especies as spectb ON iddet.EspecieID=spectb.EspecieID 
LEFT JOIN Tax_Generos as gentb ON iddet.GeneroID=gentb.GeneroID  
LEFT JOIN Tax_Familias as famtb ON iddet.FamiliaID=famtb.FamiliaID 
LEFT JOIN Pessoas as detpessoa ON detpessoa.PessoaID=iddet.DetbyID ";
$qq .=  " WHERE oprj.ProjetoID=".$projetoid;

//echo $qq."<br />";
$rsql = mysql_query($qq, $conn);
$nrsql = mysql_numrows($rsql);
$export_filenamepl = "dadosPlantas_".$projetoid.".csv";
$metadados_filepl =  "dadosPlantas_".$projetoid."_metadados.csv";
//$fdate = @date ("Y-m-d", filemtime("temp/".$export_filenamepl));
//$cdate = @date("Y-m-d");
//&& $fdate!==$cdate
if ($nrsql>0) {
$idx=0;
while($rsw = mysql_fetch_assoc($rsql)) {
	//SALVA OS DADOS NO ARQUIVO TXT
		if ($idx==0) {
			$fh = fopen("temp/".$export_filenamepl, 'w') or die("Não foi possivel gerar o arquivo");
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
			$fh = fopen("temp/".$export_filenamepl, 'a') or die("Não foi possivel abrir o arquivo");
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
					$value = "\"\"";
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
		//$perc2 = ($idx/$nrsql)*100;
		//$perc = (($perc2*$perform)/100)+$perc;
		//$qnu = "UPDATE `temp_projdadosmeta".substr(session_id(),0,10)."` SET percentage=".$perc; 
		//mysql_query($qnu,$conn);
		//session_write_close();
}
$fh = fopen("temp/".$metadados_filepl, 'w') or die("não foi possivel gerar o arquivo");
$stringData = "\"COLUNA\"\t\"DEFINICAO\""; 
foreach ($metadadospl as $kk => $vv) {
		$stringData = $stringData."\n\"".$vv[0]."\"\t\"".$vv[1]."\"";
}
fwrite($fh, $stringData);
fclose($fh);
}
$perc = 60;
$qnu = "UPDATE `temp_projdadosmeta".substr(session_id(),0,10)."` SET percentage=".$perc; 
mysql_query($qnu,$conn);
session_write_close();

//dados de monitoramento
$sql = "SELECT 
pltb.PlantaID as WikiPlantaID,
pltb.PlantaTag as PlantaTag, 
getidentidade(pltb.DetID,1,0,1,0,0) AS Family, 
getidentidade(pltb.DetID,1,0,0,1,0) AS Genus, 
getidentidade(pltb.DetID,1,0,0,0,1) AS Species,
parcelafiels(pltb.GazetteerID, pltb.GPSPointID, 'PARGAZ_SPEC')  as LocalPai,
parcelafiels(pltb.GazetteerID, pltb.GPSPointID, 'PARDIMX')  as LocalPai_DIMx,
parcelafiels(pltb.GazetteerID, pltb.GPSPointID, 'PARDIMY')  as LocalPai_DIMy,
localidadefields(pltb.GazetteerID, pltb.GPSPointID,0,0,0, 'GAZETTEER_SPEC')  as Local,
parcelafiels(pltb.GazetteerID, pltb.GPSPointID, 'DIMX')  as Local_DIMx,
parcelafiels(pltb.GazetteerID, pltb.GPSPointID, 'DIMY')  as Local_DIMy,
parcelafiels(pltb.GazetteerID, pltb.GPSPointID, 'STARTX')  as Local_Startx,
parcelafiels(pltb.GazetteerID, pltb.GPSPointID, 'STARTY')  as Local_Starty,
pltb.X as PlantaPos_X, 
pltb.Y as PlantaPos_Y, 
pltb.LADO as PlantaPos_LADO,
trr.TraitName,
moni.TraitVariation,
moni.TraitUnit as TraitVariationUNIT,
moni.DataObs as TraitVariationDATE,
Censos.CensoNome
FROM ProjetosEspecs as oprj JOIN Monitoramento as moni on moni.PlantaID=oprj.PlantaID JOIN Plantas AS pltb ON moni.PlantaID=pltb.PlantaID LEFT JOIN Censos ON Censos.CensoID=moni.CensoID JOIN Traits as trr ON moni.TraitID=trr.TraitID";
$sql .=  " WHERE oprj.ProjetoID=".$projetoid;
//echo $sql."<br />";
$rsql = mysql_query($sql, $conn);
$nrsql = mysql_numrows($rsql);
$export_filenamemoni = "dadosPlantasCensos_".$projetoid.".csv";
$metadados_filemoni =  "dadosPlantasCensos_".$projetoid."_metadados.csv";
if ($nrsql>0) {
$idx=0;
$perform = 20;
$persetp = $perform/$nrsql;
while($rsw = mysql_fetch_assoc($rsql)) {
	//SALVA OS DADOS NO ARQUIVO TXT
		if ($idx==0) {
			$fh = fopen("temp/".$export_filenamemoni, 'w') or die("Não foi possivel gerar o arquivo");
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
			$fh = fopen("temp/".$export_filenamemoni, 'a') or die("Não foi possivel abrir o arquivo");
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
					$value = "\"\"";
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
		//$perc2 = ($idx/$nrsql)*100;
		//$perc = $persetp+$perc;
		//$qnu = "UPDATE `temp_projdadosmeta".substr(session_id(),0,10)."` SET percentage=".$perc; 
		//mysql_query($qnu,$conn);
		//session_write_close();
}

$perc = 80;
$qnu = "UPDATE `temp_projdadosmeta".substr(session_id(),0,10)."` SET percentage=".$perc; 
mysql_query($qnu,$conn);
session_write_close();

$metadadosmoni = array();
$idx=0;
$metadadosmoni['idx'.$idx][0] = 'WikiPlantaID';
$metadadosmoni['idx'.$idx][1] = 'Identificador da planta na base de dados onde estão os dados';
$idx++;
$metadadosmoni['idx'.$idx][0] = 'TAG_NUM';
$metadadosmoni['idx'.$idx][1] = 'Número da placa da árvore';
$idx++;
$metadadosmoni['idx'.$idx][0] = 'FAMILY';
$metadadosmoni['idx'.$idx][1] = 'Familia botanica';
$idx++;
$metadadosmoni['idx'.$idx][0] = 'GENUS';
$metadadosmoni['idx'.$idx][1] = 'Genero botanico, onde Indet= indeterminado nesse nivel';
$idx++;
$metadadosmoni['idx'.$idx][0] = 'SPECIES';
$metadadosmoni['idx'.$idx][1] = 'Epiteto da especie';
$idx++;
$metadadosmoni['idx'.$idx][0] = 'LocalPai';
$metadadosmoni['idx'.$idx][1] = "Nome da localidade pai";
$idx++;
$metadadosmoni['idx'.$idx][0] = 'LocalPai_DIMx';
$metadadosmoni['idx'.$idx][1] = "Dimensão X da localidade pai. Quando for uma parcela";
$idx++;
$metadadosmoni['idx'.$idx][0] = 'LocalPai_DIMy';
$metadadosmoni['idx'.$idx][1] = "Dimensão Y da localidade pai. Quando for uma parcela";
$idx++;
$metadadosmoni['idx'.$idx][0] = 'LOCAL';
$metadadosmoni['idx'.$idx][1] = "A localidade mais especifica da planta coletada";
$idx++;
$metadadosmoni['idx'.$idx][0] = 'Local_DIMx';
$metadadosmoni['idx'.$idx][1] = "Dimensão X da localidade. Quando for uma parcela ou subparcela";
$idx++;
$metadadosmoni['idx'.$idx][0] = 'Local_DIMy';
$metadadosmoni['idx'.$idx][1] = "Dimensão Y da localidade pai. Quando for uma parcela ou subparcela";
$idx++;

$metadadosmoni['idx'.$idx][0] = 'PlantaPos_X';
$metadadosmoni['idx'.$idx][1] = "Posicao X da planta marcada em relacao ao LOCAL quando este for uma parcela";
$idx++;
$metadadosmoni['idx'.$idx][0] = 'PlantaPos_Y';
$metadadosmoni['idx'.$idx][1] = "Posicao Y da planta marcada em relacao ao LOCAL quando este for uma parcela";
$idx++;
$metadadosmoni['idx'.$idx][0] = 'PlantaPos_LADO';
$metadadosmoni['idx'.$idx][1] = "Para dados da grade do PPBio no PNVirua, indicando o lado das coordenadas X,Y";
$idx++;
$metadadosmoni['idx'.$idx][0] = 'TraitName';
$metadadosmoni['idx'.$idx][1] = "Nome da variável à qual o dado da linha se refere";
$idx++;
$metadadosmoni['idx'.$idx][0] = 'TraitVariation';
$metadadosmoni['idx'.$idx][1] = "Valor da variável à qual o dado da linha se refere";
$idx++;
$metadadosmoni['idx'.$idx][0] = 'TraitVariationUNIT';
$metadadosmoni['idx'.$idx][1] = "Unidade de medida do valor da variável";
$idx++;
$metadadosmoni['idx'.$idx][0] = 'TraitVariationDATE';
$metadadosmoni['idx'.$idx][1] = "Data de medição do valor da variável";
$idx++;
$metadadosmoni['idx'.$idx][0] = 'TraitVariationDATE';
$metadadosmoni['idx'.$idx][1] = "Data de medição do valor da variável";
$idx++;

$fh = fopen("temp/".$metadados_filemoni, 'w') or die("não foi possivel gerar o arquivo");
$stringData = "\"COLUNA\"\t\"DEFINICAO\""; 
foreach ($metadadosmoni as $kk => $vv) {
		$stringData = $stringData."\n\"".$vv[0]."\"\t\"".$vv[1]."\"";
}
fwrite($fh, $stringData);
fclose($fh);
}


}
$perc = 100;
$qnu = "UPDATE `temp_projdadosmeta".substr(session_id(),0,10)."` SET percentage=".$perc; 
mysql_query($qnu,$conn);
echo "CONCLUIDO";
session_write_close();
?>