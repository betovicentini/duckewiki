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
$ispopup=1;
$menu = FALSE;
$which_css = array("<link href='css/geral.css' rel='stylesheet' type='text/css' />");
$which_java = array();
$title = 'Exportar dados NIR';
$body = '';
FazHeader($title,$body,$which_css,$which_java,$menu);

echo "
<form method='post' name='finalform' action='export-nir-data-form.php'>
<input type='hidden' name='ispopup' value='".$ispopup."' /> 
<input type='hidden' name='final' value='1' /> 
<br />
<table class='myformtable' align='left' cellpadding=\"5\">
<thead>
<tr >
<td colspan='2'>Exportar dados NIR</td></tr>
</thead>
<tbody>
";
if ($bgi % 2 == 0){$bgcolor = $linecolor2 ;} else {$bgcolor = $linecolor1 ;} $bgi++;
echo "
<tr bgcolor = '".$bgcolor."'>
  <td class='tdsmallbold'>Filtro com amostras e/ou plantas</td>
  <td>
    <select name='filtroid'  >";
		if (!empty($filtroid)) {
			$qq = "SELECT * FROM Filtros WHERE FiltroID='".$filtroid."'";
			$res = @mysql_query($qq,$conn);
			$rr = @mysql_fetch_assoc($res);
			echo "
      <option selected value='".$rr['FiltroID']."'>".$rr['FiltroName']."</option>";
		}
			echo "
      <option  value=''>".GetLangVar('nameselect')." um filtro</option>";
			$qq = "SELECT * FROM Filtros WHERE (AddedBy=".$_SESSION['userid']." OR Shared=1) ORDER BY FiltroName";
			$res = @mysql_query($qq,$conn);
			while ($rr = @mysql_fetch_assoc($res)) {
				echo "
      <option value='".$rr['FiltroID']."'>".$rr['FiltroName']."</option>";
			}

	echo "
    </select>
  </td>
</tr>";
if ($bgi % 2 == 0){$bgcolor = $linecolor2 ;} else {$bgcolor = $linecolor1 ;} $bgi++;
echo "
<tr bgcolor = '".$bgcolor."'>
  <td align='center' colspan='2'><input  type='checkbox' checked value='1' name='antaris' />&nbsp;todos os espectros tem o mesmo comprimento e resolução! mais rápido!</td>
</tr>";
if ($bgi % 2 == 0){$bgcolor = $linecolor2 ;} else {$bgcolor = $linecolor1 ;} $bgi++;
echo "
<tr bgcolor = '".$bgcolor."'>
  <td align='center' colspan='2'><input style='cursor: pointer'  type='submit' value='".GetLangVar('nameenviar')."' class='bsubmit' /></td>
</tr>";
echo "
</form>
</tbody>
</table>";

$which_java = array("<script type='text/javascript' src='javascript/myjavascripts.js'></script>");
FazFooter($which_java,$calendar=FALSE,$footer=$menu);
?>