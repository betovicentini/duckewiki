<?php
session_start();
//INCLUI FUNCOES PHP E VARIAVEIS
include "functions/HeaderFooter.php";
include "functions/MyPhpFunctions.php";
include "pessoas-duplicadas-funcao.php";

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
extract($ppost);
$arval = $ppost;

$gget = cleangetpost($_GET,$conn);
extract($gget);

//echopre($ppost);
//CABECALHO
$menu = FALSE;
$which_css = array("<link href='css/geral.css' rel='stylesheet' type='text/css' />",
"<link rel='stylesheet' type='text/css' href='css/jquery-ui.css' />");
$which_java = array(
"<script src=\"javascript/jquery-1.10.2.js\"></script>",
"<script src=\"javascript/jquery-ui.js\"></script>");
$title = 'Novas pessoas';
$body = '';
FazHeader($title,$body,$which_css,$which_java,$menu);


if (count($pessoapaga)>0 && $pessoavalida>0) {
	//echopre($pessoapaga);
	//echo $pessoavalida;
	//mergepessoa($id1,$id2, $conn);
	foreach ($pessoapaga as $id1) {
		//echo "id1".$id1."  ".$pessoavalida."<br />";
		$fez = mergepessoa($id1,$pessoavalida, $conn);
		if ($fez==1) {
			echo "Corrigiu<br />";
		} else {
			echo "ERRO<br />";
		}
	}
}


echo "
<form action='pessoas-duplicadas2.php' method='post' name='coletaform' >
<table align='center' cellpadding='7' class='myformtable'>
<tbody>";
	if ($bgi % 2 == 0){$bgcolor = $linecolor2 ;}  else { $bgcolor = $linecolor1 ;} $bgi++;
echo "
<tr bgcolor = '".$bgcolor."'><td>Pessoa a apagar</td>
<td >
<select name=\"pessoapaga[]\"  multiple size=10>";
	$q = "SELECT * FROM Pessoas ORDER BY Sobrenome";
	$m = mysql_query($q,$conn);
	$mn = mysql_numrows($m);
    while ($r = mysql_fetch_assoc($m)) {
		$nome = trim($r['Prenome']." ".$r['SegundoNome']);
		$nome .= " ".$r['Sobrenome'];
		echo "
<option value='".$r['PessoaID']."' >".$r['Abreviacao']." [".$nome."]</option>";
	} 
echo "
</select>
</td></tr>
";
	if ($bgi % 2 == 0){$bgcolor = $linecolor2 ;}  else { $bgcolor = $linecolor1 ;} $bgi++;
echo "
<tr bgcolor = '".$bgcolor."'>
<td>Junta com a seguinte pessoa</td>
<td>
<select name=\"pessoavalida\">
<option value='' >Selecione a pessoa valida</option>";
	$q = "SELECT * FROM Pessoas ORDER BY Sobrenome";
	$m = mysql_query($q,$conn);
	$mn = mysql_numrows($m);
    while ($r = mysql_fetch_assoc($m)) {
		$nome = trim($r['Prenome']." ".$r['SegundoNome']);
		$nome .= " ".$r['Sobrenome'];
		echo "
<option value='".$r['PessoaID']."' >".$r['Abreviacao']." [".$nome."]</option>";
	} 
echo "
</select>
</td></tr>
";
	if ($bgi % 2 == 0){$bgcolor = $linecolor2 ;}  else { $bgcolor = $linecolor1 ;} $bgi++;
echo "
<tr bgcolor = '".$bgcolor."'>
<tr><td align='center' colspan=2>
<input style='cursor: pointer'  type='submit' value='Salvar' class='bsubmit'  /></td>
</tr>
</table>
</form>
<br />";


$which_java = array("<script type='text/javascript' src='javascript/myjavascripts.js'></script>");
FazFooter($which_java,$calendar=FALSE,$footer=$menu);

?>