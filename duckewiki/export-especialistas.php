<?php
session_start();
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

$which_css = array(
"<link href='css/geral.css' rel='stylesheet' type='text/css' />"
);
$which_java = array(
);
$title = 'Exporta especialistas';
$body = '';

$qq = "SELECT CONCAT(p.Prenome,' ',p.SegundoNome, ' ',p.Sobrenome) AS NOME, p.Abreviacao,IF(p.Email IS NULL, e.Email,p.Email) as Email, f.Familia, (SELECT GROUP_CONCAT(gg.Genero SEPARATOR ';') FROM Tax_Generos AS gg WHERE gg.EspecialistaID=e.EspecialistaID) as Generos,e.Herbarium FROM Especialistas as e JOIN Pessoas as p ON p.PessoaID=e.Especialista JOIN Tax_Familias as f ON f.FamiliaID=e.FamiliaID WHERE e.FamiliaID>0
ORDER BY f.Familia";
$res = mysql_query($qq,$conn);
$nres = mysql_numrows($res);
if ($nres>0) {
	$fh = fopen("temp/exportaEspecialistas_".$uii.".csv", 'w') or die("nao foi possivel gerar o arquivo");
	$count = mysql_num_fields($res);
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
	while($rsw = mysql_fetch_row($res)){
	$line = '';
	$nff  = count($rsw);
	$nii = 1;
	foreach($rsw as $value){
		if(!isset($value) || $value == ""){
			if ($nii<$nff) {
				$value = "  \t";
			} else {
				$value = '  ';
			}
		} else {
			//important to escape any quotes to preserve them in the data.
			$value = str_replace('"', '""', $value);
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
	$lin = trim($line)."\n";
	fwrite($fh, $lin);
	}
fclose($fh);
} 

FazHeader($title,$body,$which_css,$which_java,$menu);

echo "
<br />
<table class='myformtable' cellpadding='5' align='left' width='400'>
<thead>
<tr><td colspan='2'>Baixar tabela especialistas!</td></tr>
</thead>
<tbody>";
if ($nres==0) {
echo "
<tr><td><b>Nenhum registro foi encontrado!</b></td></tr>";
} else {
echo "
<tr>
  <td><b>$nres</b> registros foram preparados</td>
    <td><a href=\"temp/exportaEspecialistas_".$uii.".csv\">Baixar dados</a></td>
</tr>
<tr>
  <td colspan='2'><hr></td>
</tr>
<tr>
  <td colspan='2' class='tdformnotes'>*Os arquivos são separados por TABULAÇÃO, tem quebras de linha em formato Unix, e o encoding de caracteres é UTF-8. O planilha do openoffice é melhor que o Excel para abrir o arquivo porque reconhece automaticamente o encoding dos caracteres (UTF-8). No Excel pode ter erros de grafia.</td>
</tr>";
}
echo "
</tbody>
</table>";



$which_java = array("<script type='text/javascript' src='javascript/myjavascripts.js'></script>");
FazFooter($which_java,$calendar=FALSE,$footer=$menu);
	
?>