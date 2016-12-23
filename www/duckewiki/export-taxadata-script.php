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


$export_filename = "taxa_export_".$_SESSION['userlastname']."_".$_SESSION['sessiondate'].".csv";
$export_filename_metadados = "taxa_export_".$_SESSION['userlastname']."_".$_SESSION['sessiondate']."_definicoesDAScolunas.csv";
$progesstable = "temp_exporttaxa".$_SESSION['userid']."_".substr(session_id(),0,10);


//echopre($_SESSION);
//EXTRAI VARIAVEIS DO POST
unset($_SESSION['metadados']);
unset($metadados);
unset($_SESSION['qq']);
unset($qq);
unset($_SESSION['qz']);


$sql = "SELECT var.FamiliaID,var.GeneroID,var.EspecieID,var.InfraEspecieID,
taxanome(var.FamiliaID,var.GeneroID,var.EspecieID,var.InfraEspecieID,'familia') as Familia,
taxanome(var.FamiliaID,var.GeneroID,var.EspecieID,var.InfraEspecieID,'genero') as Genero,
taxanome(var.FamiliaID,var.GeneroID,var.EspecieID,var.InfraEspecieID,'especie') as Especie,
taxanome(var.FamiliaID,var.GeneroID,var.EspecieID,var.InfraEspecieID,'infraespecie') as InfraEspecie,
tr.TraitID as VariavelID,
tr.TraitTipo as VariavelTipo,
tr.TraitName as VariavelNome,
tr.PathName as VariavelNomePath,
traitvalue(var.TraitVariation,tr.TraitID,tr.TraitID) as VariavelValorBruto,
var.TraitUnit as VariavelValorBrutoUnidade,
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

echo $sql."<br >";
unlink("temp/".$export_filename);
unlink("temp/".$export_filename_metadados);


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

?>