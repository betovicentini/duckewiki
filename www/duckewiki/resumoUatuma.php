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

//CABECALHO
$menu = FALSE;

$which_css = array("<link href='css/geral.css' rel='stylesheet' type='text/css' />");
$which_java = array();
$title = 'Dados do Uatumã';
$body = '';
FazHeader($title,$body,$which_css,$which_java,$menu);

echo "
<table>
<tr>
<td style='vertical-align: top;'>
<table class='myformtable' align='left' border=0 cellpadding=\"7\" cellspacing=\"0\" >
<thead>
  <tr><td colspan='2'>Resumo UATUMÃ desde 2015</td></tr>
</thead>
<tbody>";
echo 
"<tr><td colspan=2 align='center'>NÚMERO DE AMOSTRAS</td></tr>"; 
#número de amostras
$qq = "SELECT COUNT(*) as numspecs FROM Especimenes WHERE Ano>=2015";
$res = mysql_query($qq);
$rr = mysql_fetch_assoc($res);
$numspecs = $rr['numspecs'];

echo "
<tr><td>Especímenes</td><td>".$numspecs."</td></tr>";

#numero plantas
$qq = "SELECT COUNT(*) as numplantas FROM Plantas";
$res = mysql_query($qq);
$rr = mysql_fetch_assoc($res);
$numplantas = $rr['numplantas'];
echo "
<tr><td>Plantas marcadas</td><td>".$numplantas."</td></tr>";


#plantas com especímenes
$qq = "SELECT COUNT(*) as numspecplantas FROM Especimenes WHERE PlantaID>0";
$res = mysql_query($qq);
$rr = mysql_fetch_assoc($res);
$numspecplantas = $rr['numspecplantas'];
echo "
<tr><td>Plantas marcadas com coleta</td><td>".$numspecplantas."</td></tr>";


echo 
"<tr><td colspan=2 align='center'>TAXONOMIA GERAL</td></tr>"; 

#taxonomico
$qq = "SELECT DISTINCT Familia FROM Especimenes JOIN Identidade USING(DetID) JOIN Tax_Familias USING(FamiliaID) WHERE Ano>=2015 AND Identidade.FamiliaID>0";
$res = mysql_query($qq);
$numfamilias_spec = mysql_numrows($res);
mysql_free_result($res);

echo "
<tr><td>Número de Famílias</td><td>".$numfamilias_spec."</td></tr>";


$qq = "SELECT DISTINCT Familia,Genero FROM Especimenes JOIN Identidade USING(DetID) JOIN Tax_Generos USING(GeneroID) JOIN Tax_Familias ON Identidade.FamiliaID=Tax_Familias.FamiliaID WHERE Ano>=2015 AND Identidade.GeneroID>0";
$res = mysql_query($qq);
$numgeneros_spec = mysql_numrows($res);
mysql_free_result($res);

echo "
<tr><td>Número de Gêneros</td><td>".$numgeneros_spec."</td></tr>";

$qq = "SELECT DISTINCT Familia,Genero,Especie FROM Especimenes JOIN Identidade USING(DetID) JOIN Tax_Especies USING(EspecieID) JOIN Tax_Generos ON Tax_Especies.GeneroID=Tax_Generos.GeneroID JOIN Tax_Familias ON Tax_Generos.FamiliaID=Tax_Familias.FamiliaID WHERE Ano>=2015 AND Identidade.EspecieID>0";
$res = mysql_query($qq);
$numespecies_spec = mysql_numrows($res);
mysql_free_result($res);

echo "
<tr><td>Número de Espécies</td><td>".$numespecies_spec."</td></tr>";


$qq = "SELECT DISTINCT Familia,Genero,Especie FROM Especimenes JOIN Identidade USING(DetID) JOIN Tax_Especies USING(EspecieID) JOIN Tax_Generos ON Tax_Especies.GeneroID=Tax_Generos.GeneroID JOIN Tax_Familias ON Tax_Generos.FamiliaID=Tax_Familias.FamiliaID WHERE Especimenes.Ano>=2015 AND Identidade.EspecieID>0 AND Tax_Especies.Morfotipo=1";
$res = mysql_query($qq);
$numespecies_morfo = mysql_numrows($res);
mysql_free_result($res);

echo "
<tr><td>N Espécies que são morfotipos</td><td>".$numespecies_morfo."</td></tr>";

echo 
"<tr><td colspan=2 align='center'>NÍVEL IDENTIFICAÇÃO</td></tr>"; 

$qq = "SELECT * FROM Especimenes JOIN Identidade USING(DetID) WHERE Especimenes.Ano>=2015 AND Identidade.EspecieID>0";
$res = mysql_query($qq);
$nnivelspec = mysql_numrows($res);
mysql_free_result($res);

echo "
<tr><td>Nível de espécie</td><td>".$nnivelspec."</td></tr>";


$qq = "SELECT * FROM Especimenes LEFT JOIN  Identidade USING(DetID) WHERE Especimenes.Ano>=2015 AND Identidade.EspecieID=0 AND Identidade.GeneroID>0";
$res = mysql_query($qq);
$nivelgenero = mysql_numrows($res);
mysql_free_result($res);
echo "
<tr><td>Nível de gênero</td><td>".$nivelgenero."</td></tr>";


$qq = "SELECT * FROM Especimenes LEFT JOIN Identidade USING(DetID) WHERE Especimenes.Ano>=2015 AND (Identidade.GeneroID=0 OR (Identidade.GeneroID IS NULL)) AND Identidade.FamiliaID>0";
$res = mysql_query($qq);
$nivelfam = mysql_numrows($res);
mysql_free_result($res);
echo "
<tr><td>Nível de família</td><td>".$nivelfam."</td></tr>";

$qq = "SELECT * FROM Especimenes WHERE Especimenes.Ano>=2015 AND (Especimenes.DetID IS NULL)";
$res = mysql_query($qq);
$semdet = mysql_numrows($res);
mysql_free_result($res);
echo "
<tr><td>Nem a família identificada</td><td>".$semdet."</td></tr>";
echo "
</tbody>
</table>
</td>";

echo "
<td style='vertical-align: top;'>
<table class='myformtable' align='left' border=0 cellpadding=\"7\" cellspacing=\"0\" >
<thead>
  <tr><td colspan='2'>PLANTAS COM EXTRAÇÕES DE ÓLEO</td></tr>
</thead>
<tbody>";
#número de plantas/amostras extraídas
$qq = "SELECT COUNT(*) as noleo FROM Especimenes WHERE checkoleo(EspecimenID,PlantaID,".$traitsilica.",'oleo')>0";
$res = mysql_query($qq);
$rr = mysql_fetch_assoc($res);
$noleo = $rr['noleo'];
echo "
<tr><td>Número de plantas com óleo extraído</td><td>".$noleo."</td></tr>";
echo 
"<tr><td colspan=2 align='center'>TAXONOMIA</td></tr>"; 
$qq = "SELECT DISTINCT Familia FROM Especimenes JOIN Identidade USING(DetID) JOIN Tax_Familias USING(FamiliaID) WHERE Ano>=2015 AND Identidade.FamiliaID>0 AND 
checkoleo(EspecimenID,PlantaID,".$traitsilica.",'oleo')>0";
$res = mysql_query($qq);
$onumfamilias_spec = mysql_numrows($res);
mysql_free_result($res);
echo "
<tr><td>Número de Famílias</td><td>".$onumfamilias_spec."</td></tr>";

$qq = "SELECT DISTINCT Familia,Genero FROM Especimenes JOIN Identidade USING(DetID) JOIN Tax_Generos USING(GeneroID) JOIN Tax_Familias ON Identidade.FamiliaID=Tax_Familias.FamiliaID WHERE Ano>=2015 AND Identidade.GeneroID>0 AND checkoleo(EspecimenID,PlantaID,".$traitsilica.",'oleo')>0";
$res = mysql_query($qq);
$onumgeneros_spec = mysql_numrows($res);
mysql_free_result($res);
echo "
<tr><td>Número de Gêneros</td><td>".$onumgeneros_spec."</td></tr>";


$qq = "SELECT DISTINCT Familia,Genero,Especie FROM Especimenes JOIN Identidade USING(DetID) JOIN Tax_Especies USING(EspecieID) JOIN Tax_Generos ON Tax_Especies.GeneroID=Tax_Generos.GeneroID JOIN Tax_Familias ON Tax_Generos.FamiliaID=Tax_Familias.FamiliaID WHERE Ano>=2015 AND Identidade.EspecieID>0  AND checkoleo(EspecimenID,PlantaID,".$traitsilica.",'oleo')>0";
$res = mysql_query($qq);
$onumespecies_spec = mysql_numrows($res);
mysql_free_result($res);
echo "
<tr><td>Número de Espécies</td><td>".$onumespecies_spec."</td></tr>";

$qq = "SELECT DISTINCT Familia,Genero,Especie FROM Especimenes JOIN Identidade USING(DetID) JOIN Tax_Especies USING(EspecieID) JOIN Tax_Generos ON Tax_Especies.GeneroID=Tax_Generos.GeneroID JOIN Tax_Familias ON Tax_Generos.FamiliaID=Tax_Familias.FamiliaID WHERE Especimenes.Ano>=2015 AND Identidade.EspecieID>0 AND Tax_Especies.Morfotipo=1 AND checkoleo(EspecimenID,PlantaID,".$traitsilica.",'oleo')>0";
$res = mysql_query($qq);
$onumespecies_morfo = mysql_numrows($res);
mysql_free_result($res);
echo "
<tr><td>Número de Morfoespécies</td><td>".$onumespecies_morfo."</td></tr>";

echo 
"<tr><td colspan=2 align='center'>NÍVEL IDENTIFICAÇÃO</td></tr>"; 

$qq = "SELECT * FROM Especimenes JOIN Identidade USING(DetID) WHERE Especimenes.Ano>=2015 AND Identidade.EspecieID>0 AND checkoleo(EspecimenID,PlantaID,".$traitsilica.",'oleo')>0";
$res = mysql_query($qq);
$onnivelspec = mysql_numrows($res);
mysql_free_result($res);
echo "
<tr><td>Id no nível de espécie</td><td>".$onnivelspec."</td></tr>";


$qq = "SELECT * FROM Especimenes JOIN Identidade USING(DetID) WHERE Especimenes.Ano>=2015 AND Identidade.EspecieID=0 AND Identidade.GeneroID>0 AND checkoleo(EspecimenID,PlantaID,".$traitsilica.",'oleo')>0";
$res = mysql_query($qq);
$onivelgenero = mysql_numrows($res);
mysql_free_result($res);
echo "
<tr><td>Id no nível de gênero</td><td>".$onivelgenero."</td></tr>";

$qq = "SELECT * FROM Especimenes JOIN Identidade USING(DetID) WHERE Especimenes.Ano>=2015 AND Identidade.GeneroID=0 AND Identidade.FamiliaID>0 AND checkoleo(EspecimenID,PlantaID,".$traitsilica.",'oleo')>0";
$res = mysql_query($qq);
$onivelfam = mysql_numrows($res);
mysql_free_result($res);
echo "
<tr><td>Id no nível de família</td><td>".$onivelfam."</td></tr>";

$qq = "SELECT * FROM Especimenes WHERE Especimenes.Ano>=2015 AND (DetID IS NULL) AND checkoleo(EspecimenID,PlantaID,".$traitsilica.",'oleo')>0";
$res = mysql_query($qq);
$osemdet = mysql_numrows($res);
mysql_free_result($res);
echo "
<tr><td>Sem nenhuma ID</td><td>".$osemdet."</td></tr>";

echo "
</tbody>
</table>
</br>
</td>
</tr>
</table>
";

$which_java = array();
FazFooter($which_java,$calendar=FALSE,$footer=$menu);

?>