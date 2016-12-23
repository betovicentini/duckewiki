<?php
//IMPORTA UMA TABELA QUALQUER AO MYSQL
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
$which_css = array(
"<link href='css/geral.css' rel='stylesheet' type='text/css' />",
"<link rel='stylesheet' type='text/css' media='screen' href='css/autosuggest.css' >"
);
$which_java = array(
"<script type='text/javascript' src='javascript/ajax_framework.js'></script>"
);
$title = 'Editar ou definir variáveis';
$body = '';
if ($enviado==1) {
	if ($traitid>0) {
		$qq = "SELECT TraitTipo FROM Traits WHERE TraitID='".$traitid."'";
		$rr = mysql_query($qq,$conn);
		$row = mysql_fetch_assoc($rr);
		$traittipo = $row['TraitTipo'];
		if ($traittipo=='Classe') {
			$traitkind = 'Classe';
		} else {
			if ($traittipo=='Estado') {
				$traitkind = 'Estado';
			} else {
				$tt = explode("|",$traittipo);
				$traitkind = $tt[0];
			}
		}
		if(($traitname=='Habitat' || $traitname=='LocalidadeTipo') && $traittipo=='Classe') {
		FazHeader($title,$body,$which_css,$which_java,$menu);
			echo "
<form action='traits-form.php' method='post'>
<input type='hidden' name='ispopup' value='".$ispopup."'/>
<table class='erro' align='left' cellpadding='7'>
  <tr class='tdsmallbold'><td>".GetLangVar('erro16')."</td></tr>
  <tr><td align='center'><input type='submit' class='bsubmit' value='".GetLangVar('namevoltar')."' /></td></tr>
</table>
</form>
<br>";
$which_java = array("<script type='text/javascript' src='javascript/myjavascripts.js'></script>");
FazFooter($which_java,$calendar=FALSE,$footer=$menu);
			exit;
		} 
	}
	
	if($traitkind=='Estado' || $traitkind=='Classe' ) {
		header("location: traitsclasstate-exec.php?ispopup=".$ispopup."&traitid=$traitid&traitkind=$traitkind");
	} elseif ($traitkind=='Variavel') {
		header("location: traitsvar-exec.php?ispopup=".$ispopup."&traitid=$traitid&traitkind=$traitkind");
	}
	if (empty($traitkind) || !isset($traitkind)) {
		header("location: traits-form.php?ispopup=".$ispopup."&");
	}
} 
else {

FazHeader($title,$body,$which_css,$which_java,$menu);
echo "
<table align='left' >
<tr><td >
<form action='traits-form.php' method='post'>
<input type='hidden' name='enviado' value='1' />
<table class='myformtable' cellpadding=\"7\" align='left' width='100%'>
<thead>
<tr><td>Nova variável ou categoria de variação</td></tr>
</thead>
<tbody>
";
if ($bgi % 2 == 0){$bgcolor = $linecolor2 ;} else {$bgcolor = $linecolor1 ;} $bgi++;
echo "
<tr bgcolor = '".$bgcolor."'>
  <td>
    <table align='left'>
      <tr>
        <td class='tdsmallbold'>".GetLangVar('namenovo')."</td>
        <td class='tdformnotes' align='center'><input type='radio' name='traitkind' value='Classe' />".GetLangVar('traitkind1')."<img src=\"icons/icon_question.gif\" ";
		$help = strip_tags(GetLangVar('traitkind1_desc'));
		echo " onclick=\"javascript:alert('$help');\" /></td>
        <td class='tdformnotes' align='center'><input type='radio' name='traitkind' value='Variavel' />".GetLangVar('traitkind2')."<img src=\"icons/icon_question.gif\" ";
		$help = strip_tags(GetLangVar('traitkind2_desc'));
		echo " onclick=\"javascript:alert('$help');\" /></td>
        <td class='tdformnotes' align='center'><input type='radio' name='traitkind' value='Estado' />".GetLangVar('traitkind3')."&nbsp;<img src=\"icons/icon_question.gif\" ";
		$help = strip_tags(GetLangVar('traitkind3_desc'));
		echo " onclick=\"javascript:alert('$help');\" /></td>
      </tr>
    </table>
  </td>
</tr>
";
if ($bgi % 2 == 0){$bgcolor = $linecolor2 ;} else {$bgcolor = $linecolor1 ;} $bgi++;
echo "
<tr bgcolor = '".$bgcolor."'>
  <td align='center'><input type='submit' value='".GetLangVar('namecontinuar')."' class='bsubmit'\" /></td>
</tr>
</tbody>
</table>
</form>
</td></tr>";

//EDITAR
echo "
<tr><td >
<form action='traits-form.php' method='post'>
<input type='hidden' name='enviado' value='1' />
<table class='myformtable' cellpadding=\"7\" align='left' width='100%'>
<thead>
<tr><td>".GetLangVar('nameeditar')." ".GetLangVar('nametraits')."</td></tr>
</thead>
<tbody>
";
if ($bgi % 2 == 0){$bgcolor = $linecolor2 ;} else {$bgcolor = $linecolor1 ;} $bgi++;
echo "
<tr bgcolor = '".$bgcolor."'>
  <td>
    <table>
      <tr>
        <td class='tdsmallbold'>Selecione variável ou categoria de variação</td>
        <td class='tdformnotes'>"; autosuggestfieldval3('search-traits-toedit.php','trname',$trname,'traitres','traitid',$traitid,true,60); echo "</td>
      </tr>
    </table>
  </td>
</tr>
";
if ($bgi % 2 == 0){$bgcolor = $linecolor2 ;} else {$bgcolor = $linecolor1 ;} $bgi++;
echo "
<tr bgcolor = '".$bgcolor."'>
  <td align='center'><input type='submit' value='".GetLangVar('namecontinuar')."' class='bsubmit'\" /></td>
</tr>
</tbody>
</table>
</form>
";

//DUPLICAR VARIAVEIS
echo "
</td></tr>
<tr><td >
<form action='traits-duplicate.php' method='post' >
<table class='myformtable' cellpadding=\"7\" align='left' width='100%'>
<thead>
<tr><td>Duplicar ".GetLangVar('nametraits')."</td></tr>
</thead>
<tbody>
";
if ($bgi % 2 == 0){$bgcolor = $linecolor2 ;} else {$bgcolor = $linecolor1 ;} $bgi++;
echo "
<tr bgcolor = '".$bgcolor."'>
  <td>
    <table>
      <tr>
        <td class='tdsmallbold'>Selecione uma variável categórica</td>
        <td class='tdformnotes'>"; autosuggestfieldval3('search-traits-categ-only.php','trnamed',$trnamed,'traitresd','otraitid',$otraitid,true,60); echo "</td>
      </tr>
    </table>
  </td>
</tr>
";
if ($bgi % 2 == 0){$bgcolor = $linecolor2 ;} else {$bgcolor = $linecolor1 ;} $bgi++;
echo "
<tr bgcolor = '".$bgcolor."'>
  <td align='center'><input type='submit' value='".GetLangVar('namecontinuar')."' class='bsubmit'\" /></td>
</tr>
</tbody>
</table>
</form>
";

echo "
</td></tr>
</table>";
$which_java = array("<script type='text/javascript' src='javascript/myjavascripts.js'></script>");
FazFooter($which_java,$calendar=FALSE,$footer=$menu);

}

?>