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
$which_css = array(
"<link href='css/geral.css' rel='stylesheet' type='text/css' />");
$which_java = array();
$title = 'Exportar GPX';
$body = '';

FazHeader($title,$body,$which_css,$which_java,$menu);

//echo "<span style='font-size: 2em; color: red;' >NAO ESTA FUNCIONANDO AINDA</span>";
echo "
<br />
<form action='exportGPX_form.php' method='post'>
<table class='myformtable' align='center' border=0 cellpadding=\"5\" cellspacing=\"0\" >
<thead>
<tr><td >Exportar dados para um arquivo GPX
&nbsp;<img height=14 src=\"icons/icon_question.gif\" ";
	$help = "Este arquivo pode ser importado ao seu GPS";
	echo " onclick=\"javascript:alert('$help');\"></td>
</tr>
</thead>
<tbody>";
if ($bgi % 2 == 0){$bgcolor = $linecolor2 ;} else {$bgcolor = $linecolor1 ;} $bgi++;
echo "
<tr bgcolor = '".$bgcolor."'>
<td >
<table>
<tr><td class='tdsmallbold'>".GetLangVar('namefiltro')."&nbsp;<img height=14 src=\"icons/icon_question.gif\" ";
	$help = "Selecione um filtro que tem as amostras e/ou plantas que deseja incluir no arquivo";
	echo " onclick=\"javascript:alert('$help');\"></td>
<td>
  <select name='filtroid'>
    <option selected value=''>".GetLangVar('nameselect')."</option>";
	$qq = "SELECT * FROM Filtros WHERE (AddedBy=".$_SESSION['userid']." OR Shared=1) ORDER BY FiltroName";
	$res = @mysql_query($qq,$conn);
	while ($rr = @mysql_fetch_assoc($res)) {
		echo "
    <option value='".$rr['FiltroID']."'>".$rr['FiltroName']."</option>";
	}
echo "
  </select>
</td>
</tr>
</table>
</td>
</tr>";
if ($bgi % 2 == 0) {$bgcolor = $linecolor2 ;} else { $bgcolor = $linecolor1 ;} $bgi++;
echo "
<tr bgcolor = '".$bgcolor."'>
<td >
<table>
<tr>
	<td class='tdsmallbold'>Selecione 1 opção";
	echo "&nbsp;<img height=14 src=\"icons/icon_question.gif\" ";
	$help = "Selecione o que deseja incluir no arquivo";
	echo " onclick=\"javascript:alert('$help');\" />
	</td>
	<td class='tdformnotes'>
	<table>
	<tr><td><input type='radio' name='oque' value=1>&nbsp;Especimenes</td></tr>
	<tr><td><input type='radio' name='oque' value=2>&nbsp;Plantas marcadas</td></tr>
	<tr><td><input type='radio' name='oque' value=3>&nbsp;Ambos - plantas e especimenes</td></tr>
	</table>
	</td>
</tr>
</table>
</td>
</tr>";
if ($bgi % 2 == 0){$bgcolor = $linecolor2 ;} else {$bgcolor = $linecolor1 ;} $bgi++;
echo "
<tr bgcolor = '".$bgcolor."'>
  <td align='center'><input type='submit' value='".GetLangVar('nameenviar')."' class='bsubmit' /></td>
</tr>
</tbody>
</table>
</form>
";
$which_java = array();
FazFooter($which_java,$calendar=FALSE,$footer=$menu);

?>