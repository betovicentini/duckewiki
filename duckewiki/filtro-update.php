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
$which_css = array(
"<link href='css/geral.css' rel='stylesheet' type='text/css' />",
"<link rel='stylesheet' type='text/css' href='css/cssmenu.css' />"
);
$which_java = array(
"<script type='text/javascript' src='css/cssmenuCore.js'></script>",
"<script type='text/javascript' src='css/cssmenuAddOns.js'></script>",
"<script type='text/javascript' src='css/cssmenuAddOnsItemBullet.js'></script>"
);
$title = 'Atualizar Filtro';
$body = '';
FazHeader($title,$body,$which_css,$which_java,$menu);
echo "
<br />
<table class='myformtable' align='center' cellpadding='5'>
<thead>
<tr>
  <td colspan='100%'>".GetLangVar('nameatualizar')." ".GetLangVar('namefiltro')."&nbsp;<img height=13 src=\"icons/icon_question.gif\" ";
  $help = "Esta função irá atualizar o filtro selecionado, refazendo a busca realizada para gerar o filtro e selecionando as amostras coletadas e plantas marcadas encontradas. SE O FILTRO É A SOMA DE VÁRIAS BUSCAS, INFELIZMENTE APENAS OS CRITÉRIOS DA PRIMEIRA BUSCA SERÃO UTILIZADOS";
  echo  " onclick=\"javascript:alert('$help');\"></td>
</tr>
</thead>
<tbody>
<form method='post' action='filtros-form.php'>
<input type='hidden' name='updating' value=1>
<input type='hidden' name='final' value=1>
<tr>
  <td colspan='100%' >
    <table>
      <tr>
        <td class='tdsmallbold'>".GetLangVar('namefiltro')."s</td>
        <td>
          <select name='filtroid'>
            <option selected value=''>".GetLangVar('nameselect')."</option>";
			$qq = "SELECT * FROM Filtros WHERE AddedBy=".$_SESSION['userid']." OR Shared=1 ORDER BY FiltroName";
			$res = @mysql_query($qq,$conn);
			while ($rr = @mysql_fetch_assoc($res)) {
				echo "
          <option value='".$rr['FiltroID']."'>".$rr['FiltroName']."</option>";
			}
			mysql_free_result($res);
echo "
          </select>
        </td>
      </tr>
    </table>
  </td>
</tr>
<tr>
  <td colspan='100%'>
    <table align='center'>
      <tr>
        <td><input type='submit' value='".GetLangVar('nameenviar')."' class='bsubmit'></td>
</form>
<form method='post' action='filtro-update.php'>
        <td><input type='submit' value='".GetLangVar('namereset')."' class='breset'></td>
</form>
      </tr>
    </table>
  </td>
</tr>
</tbody>
</table>
";
$which_java = array("<script type='text/javascript' src='javascript/myjavascripts.js'></script>",
"<!-- Create Menu Settings: (Menu ID, Is Vertical, Show Timer, Hide Timer, On Click ('all' or 'lev2'), Right to Left, Horizontal Subs, Flush Left, Flush Top) -->",
"<script type='text/javascript'>qm_create(0,false,0,500,false,false,false,false,false);</script>");
FazFooter($which_java,$calendar=FALSE,$footer=$menu);
?>