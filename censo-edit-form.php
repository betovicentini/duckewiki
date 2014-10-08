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

//echopre($ppost);
//CABECALHO
$ispopup=1;
if ($ispopup==1) {
	$menu = FALSE;
} else {
	$menu = TRUE;
}
$which_css = array(
"<link href='css/geral.css' rel='stylesheet' type='text/css' />",
"<link rel='stylesheet' type='text/css' href='css/colorbuttons.css' />",
"<link rel='stylesheet' type='text/css' media='screen' href='css/autosuggest.css' />"
);
$which_java = array(
"<script type='text/javascript' src='javascript/ajax_framework.js'></script>"
);
$title = 'Censos';
$body = '';
FazHeader($title,$body,$which_css,$which_java,$menu);
//echopre($ppost);
echo "
<br />
<form action='censo-edit-exec.php' name='finalform' method='post'>
<table align='center' cellspacing='0' cellpadding='7' class='myformtable'>
<thead>
  <tr><td colspan=2>Editar Censos</td></tr>
</thead>
<tbody>
<tr>";
if ($bgi % 2 == 0){$bgcolor = $linecolor2 ;}  else {$bgcolor = $linecolor1 ;} $bgi++;
echo "
<tr bgcolor = '".$bgcolor."'>
    <td>
          <select name='censoid' onchange='javascript: this.form.submit();'>
            <option value=''>Selecione o censo para editar</option>";
				$qk = "SELECT * FROM Censos";
				$rk = @mysql_query($qk,$conn);
				while ($rwk = @mysql_fetch_assoc($rk)) {
				echo "
            <option value='".$rwk['CensoID']."'>".$rwk['CensoNome']."</option>";
				}
	echo "
          </select>
  </td>
  <td>
  <input type='hidden' name='novo'  value=''>
  <input type='submit'  class='bblue' value='Novo Censo'   onclick=\"javascript:document.finalform.novo.value='1'\" >
  </td>
</tr>
</tbody>
</table>
</form>
";
$which_java = array("<script type='text/javascript' src='javascript/myjavascripts.js'></script>");
FazFooter($which_java,$calendar=TRUE,$footer=$menu);

?>