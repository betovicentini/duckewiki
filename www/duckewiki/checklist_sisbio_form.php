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
FazHeader($title,$body,$which_css,$which_java,$menu);

echo "
<br />
<table class='myformtable' align='left' border=0 cellpadding=\"5\" cellspacing=\"0\">
<thead>
<tr><td colspan=100%>Relatório SISBIO - selecionar filtro
&nbsp;<img height=14 src=\"icons/icon_question.gif\" ";
	$help = "selecione um filtro com os registros para colocar no relatório para o SISBIO";
	echo	" onclick=\"javascript:alert('$help');\" /></td>
</tr>
</thead>
<tbody>
<form action='checklist_sisbio.php' method='post'>
<input type='hidden' name='ispopup' value='".$ispopup."' />";
if ($bgi % 2 == 0){$bgcolor = $linecolor2 ;} else {$bgcolor = $linecolor1 ;} $bgi++;
echo "
<tr bgcolor = '".$bgcolor."'>
<td colspan=100%>
  <table>
    <tr>
      <td class='tdsmallbold'>".GetLangVar('namefiltro')."</td>
      <td>
        <select name='filtro' onchange='this.form.submit();'>
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
</tr>
</form>
</tbody>
</table>
";

$which_java = array("<script type='text/javascript' src='javascript/myjavascripts.js'></script>",
"<!-- Create Menu Settings: (Menu ID, Is Vertical, Show Timer, Hide Timer, On Click ('all' or 'lev2'), Right to Left, Horizontal Subs, Flush Left, Flush Top) -->",
"<script type='text/javascript'>qm_create(0,false,0,500,false,false,false,false,false);</script>");
FazFooter($which_java,$calendar=FALSE,$footer=$menu);

?>