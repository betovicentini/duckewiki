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
if ($ispopup==1) {
	$menu = FALSE;
} else {
	$menu = TRUE;
}
$title = '';
$which_css = array(
"<link href='css/geral.css' rel='stylesheet' type='text/css' >",
"<link rel='stylesheet' type='text/css' href='css/cssmenu.css' >"
);
$which_java = array(
"<script type='text/javascript' src='css/cssmenuCore.js'></script>",
"<script type='text/javascript' src='css/cssmenuAddOns.js'></script>",
"<script type='text/javascript' src='css/cssmenuAddOnsItemBullet.js'></script>"
);
$body='';
$title = 'Busca de amostras';
FazHeader($title,$body,$which_css,$which_java,$menu);
//////////////
echo "
<br />
<table class='myformtable' align='center' cellpadding=\"5\">
<thead>
<tr >
<td colspan='100%'>
".GetLangVar('namebuscar')." ".GetLangVar('nameamostra')."s&nbsp;&nbsp;<img height='15' src=\"icons/icon_question.gif\" ";
	$help = "Faz uma busca de coletas e gera um filtro segundo uma lista de coletor+sep+numero, onde coletor= Abreviacao, sep é um separador indicado aqui e numero é o número de coleta. A lista deve ser separada por ponto e virgula";
	echo " onclick=\"javascript:alert('".$help."');\">
</td>
</tr>
</thead>
<tbody>
<form method='post' name='finalform' action='search_specimens_save.php'>
<input type='hidden' name='ispopup' value='$ispopup' >
";
if ($bgi % 2 == 0) {$bgcolor = $linecolor2 ;} else { $bgcolor = $linecolor1 ;} $bgi++;
echo "
<tr bgcolor = '".$bgcolor."'>
  <td class='tdsmallbold'>Separador SEP</td>
  <td><input type='text' name='separador' /></td>
</tr>";
if ($bgi % 2 == 0) {$bgcolor = $linecolor2 ;} else { $bgcolor = $linecolor1 ;} $bgi++;
echo "
<tr bgcolor = '".$bgcolor."'>
  <td class='tdsmallbold'>coletor+SEP+numero</td>
  <td><textarea name='tagnumbers' rows='8' cols='60'>$specimenslist</textarea></td>
</tr>
<tr>
  <td colspan='100%'>
    <table align='center'>
      <tr>
        <td><input type='submit' value='".GetLangVar('namebuscar')."' class='bsubmit' /></td>
</form>
<form method='post' action='search_specimens.php'>
      <input type='hidden' name='ispopup' value='$ispopup' >
      <td><input type='submit' value='".GetLangVar('namereset')."' class='breset' /></td>
</form>
      </tr>
    </table>
  </td>
</tr>
</tbody>
</table>";

$which_java = array("<script type='text/javascript' src='javascript/myjavascripts.js'></script>",
"<!-- Create Menu Settings: (Menu ID, Is Vertical, Show Timer, Hide Timer, On Click ('all' or 'lev2'), Right to Left, Horizontal Subs, Flush Left, Flush Top) -->",
"<script type='text/javascript'>qm_create(0,false,0,500,false,false,false,false,false);</script>");
FazFooter($which_java,$calendar=FALSE,$footer=$menu);
?>
