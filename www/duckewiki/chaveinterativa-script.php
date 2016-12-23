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


$export_filename = "chaveinterativa".$_SESSION['userlastname']."_".$_SESSION['sessiondate'].".csv";
$export_filename_metadados = "chaveinterativa".$_SESSION['userlastname']."_".$_SESSION['sessiondate']."_definicoesDAScolunas.csv";
$progesstable = "temp_chaveinterativa".$_SESSION['userid']."_".substr(session_id(),0,10);


unlink("temp/".$export_filename);
unlink("temp/".$export_filename_metadados);

$sql = "SELECT var.FamiliaID,var.GeneroID,var.EspecieID,var.InfraEspecieID,
taxanome(var.FamiliaID,var.GeneroID,var.EspecieID,var.InfraEspecieID,'familia') as Familia,
taxanome(var.FamiliaID,var.GeneroID,var.EspecieID,var.InfraEspecieID,'genero') as Genero,
taxanome(var.FamiliaID,var.GeneroID,var.EspecieID,var.InfraEspecieID,'especie') as Especie,
taxanome(var.FamiliaID,var.GeneroID,var.EspecieID,var.InfraEspecieID,'infraespecie') as InfraEspecie,
tr.TraitID as VariavelID,
tr.TraitTipo as VariavelTipo,
tr.TraitName as VariavelNome,
tr.PathName as VariavelNomePath,
tr.TraitUnit as VariavelUnidadePadrao,
traitvalue(var.TraitVariation,tr.TraitID,tr.TraitID) as ValorBruto,
var.TraitUnit as ValorBrutoUnidade,
CONCAT(uu.FirstName,uu.LastName) as AdicionadoPor,
var.AddedDate as AdicionadoEm,
bib.BibKey
From Traits_variation as var 
JOIN Traits as tr USING(TraitID) 
JOIN FormulariosTraitsList AS fm ON fm.TraitID=tr.TraitID 
JOIN Users as uu ON uu.UserID=var.AddedBy 
LEFT JOIN BiblioRefs as bib ON bib.BibID=var.BibkeyID
WHERE 
(var.FamiliaID>0 OR var.GeneroID>0 OR var.EspecieID>0 OR var.InfraEspecieID>0) AND
fm.FormID=".$formid;

$sql1 = "SELECT iddet.FamiliaID,iddet.GeneroID,iddet.EspecieID,iddet.InfraEspecieID,
taxanome(iddet.FamiliaID,iddet.GeneroID,iddet.EspecieID,iddet.InfraEspecieID,'familia') as Familia,
taxanome(iddet.FamiliaID,iddet.GeneroID,iddet.EspecieID,iddet.InfraEspecieID,'genero') as Genero,
taxanome(iddet.FamiliaID,iddet.GeneroID,iddet.EspecieID,iddet.InfraEspecieID,'especie') as Especie,
taxanome(iddet.FamiliaID,iddet.GeneroID,iddet.EspecieID,iddet.InfraEspecieID,'infraespecie') as InfraEspecie,
tr.TraitID as VariavelID,
tr.TraitTipo as VariavelTipo,
tr.TraitName as VariavelNome,
tr.PathName as VariavelNomePath,
tr.TraitUnit as VariavelUnidadePadrao,
traitvalue(var.TraitVariation,tr.TraitID,tr.TraitID) as ValorBruto,
var.TraitUnit as ValorBrutoUnidade,
CONCAT(uu.FirstName,uu.LastName) as AdicionadoPor,
var.AddedDate as AdicionadoEm,
bib.BibKey
From Traits_variation as var 
JOIN Traits as tr USING(TraitID) 
JOIN FormulariosTraitsList AS fm ON fm.TraitID=tr.TraitID 
JOIN Users as uu ON uu.UserID=var.AddedBy 
JOIN Especimenes as specs ON specs.EspecimenID=var.EspecimenID
JOIN Identidade as iddet ON iddet.DetID=specs.DetID
LEFT JOIN BiblioRefs as bib ON bib.BibID=var.BibkeyID
WHERE var.EspecimenID>0 AND
fm.FormID=".$formid;

//$osql = "SELECT resumo.* FROM ((".$sql.") UNION (".$sql1.")) as resumo";
//echo $sql."<br >";
$tbname = "temp_key_".$_SESSION['userid'];

$osql = "DROP TABLE `".$tbname."`";
mysql_query($osql,$conn);

$osql = "CREATE TABLE IF NOT EXISTS `".$tbname."` (".$sql.")";
mysql_query($osql,$conn);
//echo $osql."<br >";

$osql =  "INSERT INTO `".$tbname."` (".$sql1.")";
mysql_query($osql,$conn);

$qrr = "ALTER TABLE `". $tbname."`  ADD `tempID` INT(10) UNSIGNED NOT NULL AUTO_INCREMENT PRIMARY KEY FIRST";
mysql_query($qrr,$conn);

$sql = "CREATE INDEX Familia ON `".$tbname."`  (Familia)";
mysql_query($sql,$conn);

$sql = "CREATE INDEX Especie ON `".$tbname."`  (Especie)";
mysql_query($sql,$conn);

$sql = "CREATE INDEX Genero ON `".$tbname."`  (Genero)";
mysql_query($sql,$conn);
$sql = "CREATE INDEX InfraEspecie ON `".$tbname."`  (InfraEspecie)";
mysql_query($sql,$conn);

//$lixo=999;


$tbresumo = $tbname."resumo";
$osql = "DROP TABLE `".$tbresumo."`";
@mysql_query($osql,$conn);

//$sql = "SELECT DISTINCT Familia,Genero,Especie,InfraEspecie FROM `".$tbname."` WHERE Especie<>'' AND (Especie IS NOT NULL)";

$osql = "CREATE TABLE IF NOT EXISTS `".$tbresumo."` (Familia CHAR(100), Genero CHAR(100), Especie CHAR(100), InfraEspecie CHAR(100), VariavelID INT(10), VariavelNome VARCHAR(100),VariavelTipo VARCHAR(100),ValorResumo VARCHAR(500),ValorNobs INT(10)) CHARACTER SET utf8  ENGINE InnoDB"; 
mysql_query($osql,$conn);
$qrr = "ALTER TABLE `". $tbresumo."`  ADD `tempID` INT(10) UNSIGNED NOT NULL AUTO_INCREMENT PRIMARY KEY FIRST";
mysql_query($qrr,$conn);

//$osql = "CREATE TABLE `".$tbresumo."` (".$sql.")";
//@mysql_query($osql,$conn);



//$qq = "ALTER TABLE ".$tbresumo." ADD COLUMN ValorResumo VARCHAR(500) DEFAULT NULL";
//mysql_query($qq,$conn);
//$qq = "ALTER TABLE ".$tbresumo." ADD COLUMN ValorNobs INT(10) DEFAULT NULL";
//mysql_query($qq,$conn);
//$qq = "ALTER TABLE ".$tbresumo." ADD COLUMN VariavelTipo VARCHAR(100) DEFAULT NULL";
//mysql_query($qq,$conn);
//$qq = "ALTER TABLE ".$tbresumo." ADD COLUMN VariavelID INT(10) DEFAULT 0";
//mysql_query($qq,$conn);
//$qq = "ALTER TABLE ".$tbresumo." ADD COLUMN VariavelNome VARCHAR(100) DEFAULT NULL";
//mysql_query($qq,$conn);




$sql = "SELECT DISTINCT Familia,Genero,Especie,InfraEspecie FROM `".$tbname."` WHERE Especie<>'' AND (Especie IS NOT NULL)";
$rz = mysql_query($sql,$conn);
$nrecs = mysql_numrows($rz);
while($rsw = mysql_fetch_assoc($rz)) {
	$qu = "SELECT ValorBruto,mudaunidade(ValorBruto,VariavelTipo,ValorBrutoUnidade,VariavelUnidadePadrao) as novovalor FROM `".$tbname."` WHERE Genero='".$rsw['Genero']."' AND Especie='".$rsw['Especie']."'";
	if (!empty($rsw['InfraEspecie'])) {
		$qu .= " AND InfraEspecie='".$rsw['InfraEspecie']."'";
	} 
	$sql2 = "SELECT DISTINCT VariavelID,VariavelTipo,VariavelNome FROM `".$tbname."`";
	$rsql2 = mysql_query($sql2,$conn);
	while($rwsql2 = mysql_fetch_assoc($rsql2)) {
		$ovalor  = NULL;
		$otipo = $rwsql2['VariavelTipo'];
		$quu = $qu." AND VariavelID='".$rwsql2['VariavelID']."'";
		$ru = mysql_query($quu,$conn);
		$valores = array();
		while($ruw = mysql_fetch_assoc($ru)) {
			if ($otipo=='Variavel|Quantitativo') {
				$v1 = explode(";",$ruw['novovalor']);
				$valores = array_merge((array)$valores,(array)$v1);
			} else {
				if ($otipo=='Variavel|Categoria') {
					$v1 = explode(";",$ruw['ValorBruto']);
					$valores = array_merge((array)$valores,(array)$v1);
				} else {
					$valores = array_merge((array)$valores,(array)$ruw['ValorBruto']);
				}
			}
		}
		$namostral = count($valores);
		if ($otipo=='Variavel|Quantitativo') {
			if (count($valores)>1) {
				$media = round(array_sum($valores)/count($valores),2);
				$somadesviso= 0;
				foreach ($valores as $i) {
		    	    $somadesviso += pow($i - $media, 2);
			    }
		    	$odesvio = round(sqrt($somadesviso/(count($valores)-1)),2);
	    		$minimo = round(min($valores),1);
	    		$maximo = round(max($valores),1);
		    	$ovalor = $media."+/-".$odesvio." (".$minimo."-".$maximo.")";
			} else {
				$ovalor = implode(";",$valores);
			}
		}
		if ($otipo=='Variavel|Categoria') {
				$v1 = array_count_values($valores);
				arsort($v1);
				$ooo = array();
				foreach($v1 as $kk => $vv) {
					$ooo[] = $kk." (N=".$vv.") ";
				} 
				$ovalor = implode(", ",$ooo);
		}
		if (count($valores)>0 && (!isset($ovalor) || empty($ovalor))) {
			$ovalor = implode("; ",$valores);
		}
		$osql = "INSERT INTO `".$tbresumo."` (Familia, Genero, Especie, InfraEspecie, VariavelID, VariavelNome,VariavelTipo,ValorResumo,ValorNobs) VALUES ('".$rsw['Familia']."','".$rsw['Genero']."','".$rsw['Especie']."','".$rsw['InfraEspecie']."', '".$rwsql2['VariavelID']."','".$rwsql2['VariavelNome']."','".$rwsql2['VariavelTipo']."', '".$ovalor."','".$namostral."')";
		mysql_query($osql,$conn);
		//$sql = "UPDATE ".$tbresumo." SET ValorResumo='".$ovalor."' WHERE tempID=".$rsw['tempID'];
		//mysql_query($sql,$conn);
		//$sql = "UPDATE ".$tbresumo." SET ValorNobs='".$namostral."' WHERE tempID=".$rsw['tempID'];
		//mysql_query($sql,$conn);
}
}
if ($lixo==999) {
$rz = mysql_query($sql,$conn);
//EXECUTA A QUERY E SALVA LINHA POR LINHA NO ARQUIVO DE DADOS
$step=0;
$nrecs = mysql_numrows($rz);
//echo "tem ".$nrecs."  registros<br >";
while($rsw = mysql_fetch_assoc($rz)) {
		//echopre($rsw);
		$fieldscount = mysql_num_fields($rz);
		if ($step==0) {
			$acabeca = array();
			$fh = fopen("temp/".$export_filename, 'w') or die("nao foi possivel gerar o arquivo");
			$count = $fieldscount;
			$header = '';
			for ($i = 0; $i < $count; $i++){
				$cl = mysql_field_name($rz, $i);
				$acabeca[] = $cl;
				if ($i<($count-1)) {
					$header .=  '"'. mysql_field_name($rz, $i).'"'."\t";
				} else {
					$header .=  '"'. mysql_field_name($rz, $i).'"';
				}
			}
			$header .= "\r";
			fwrite($fh, $header);
		}
		$line = '';
		$nff  = count($rsw);
		$nii = 1;
		foreach($rsw as $value){
				if(!isset($value) || $value == ""){
					$value = "\"\"\t";
				} else {
					$value = preg_replace( "/\r|\n|\t/", "", $value);
					$value = str_replace('"', '""', $value);
					if ($nii<$nff) {
						$value = '"' . $value . '"' . "\t";
					} else {
						$value = '"' . $value . '"';
					}
				}
				$nii++;
				$line .= $value;
		}
		$lin = trim($line)."\r";
		fwrite($fh, $lin);
		
		//SAVE LOG
		$total = $nrecs;
		$perc = ($step/$total)*99;
		$qnu = "UPDATE `".$progesstable."` SET percentage=".$perc; 
		//echo $qnu."<br >";
		mysql_query($qnu);
		session_write_close();
		$step++;
	}

fclose($fh);


$qnu = "UPDATE `".$progesstable."` SET percentage=100"; 
mysql_query($qnu);
$message=  $nrecs.";".$fieldscount;
echo $message;
session_write_close();
}
?>