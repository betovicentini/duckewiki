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
 $ispopup=1; $menu = FALSE;
$which_css = array(
"<link href='css/geral.css' rel='stylesheet' type='text/css' />",
"<link rel='stylesheet' type='text/css' media='screen' href='css/autosuggest.css' />"
//, "<link rel='stylesheet' type='text/css' href='css/cssmenu.css' />"
);
$which_java = array(
"<script type='text/javascript' src='javascript/ajax_framework.js'></script>"
//"<script type='text/javascript' src='css/cssmenuCore.js'></script>",
//"<script type='text/javascript' src='css/cssmenuAddOns.js'></script>",
//"<script type='text/javascript' src='css/cssmenuAddOnsItemBullet.js'></script>"
);
$title = 'Importar locais';
$body = '';
FazHeader($title,$body,$which_css,$which_java,$menu);

echo "
<br />
<table align='left' class='myformtable' cellpadding=\"7\">
<thead>
<tr>
<td colspan='2' class='tabhead' >".GetLangVar('nameimportar')." localidades</td>
</tr>
</thead>
<tbody>";
if ($bgi % 2 == 0){$bgcolor = $linecolor2 ;} else {$bgcolor = $linecolor1 ;} $bgi++;
echo "
<tr bgcolor = '".$bgcolor."'>
<form enctype='multipart/form-data' action='import-locais-step1.php' method='post'>
  <td style='color: #990000; font-weight:bold' >".GetLangVar('namefile')."*&nbsp;&nbsp;<img style='cursor: pointer' height=14 src=\"icons/icon_question.gif\" ";
$help = "O arquivo para importar deve estar em formato TXT ou CSV, separado por TABULAÇÃO, quebra de linha em formato UNIX e código de fonte UTF-8. O LibreOffice/OpenOffice  permite salvar arquivos em formato CSV com essa opções.";
echo " onclick=\"javascript:alert('$help');\" /></td>
  <td><input type='hidden' name='MAX_FILE_SIZE' value='10000000' />
  <input type='hidden' name='ispopup' value='".$ispopup."' />
  <input  name='uploadfile' type='file' width='20' /></td>
</tr>
";
if ($bgi % 2 == 0){$bgcolor = $linecolor2 ;} else {$bgcolor = $linecolor1 ;} $bgi++;
echo "
<tr bgcolor = '".$bgcolor."'>
  <td colspan='2' align='center'><input style='cursor: pointer' type='submit' value='".GetLangVar('nameenviar')."' class='bsubmit' /></td>
</tr>
</form>
</tbody>
</table>";

$which_java = array("<script type='text/javascript' src='javascript/myjavascripts.js'></script>"
//,"<!-- Create Menu Settings: (Menu ID, Is Vertical, Show Timer, Hide Timer, On Click ('all' or 'lev2'), Right to Left, Horizontal Subs, Flush Left, Flush Top) -->",
//"<script type='text/javascript'>qm_create(0,false,0,500,false,false,false,false,false);</script>"
);
FazFooter($which_java,$calendar=FALSE,$footer=$menu);
?>
