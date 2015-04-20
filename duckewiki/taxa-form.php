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
} 

//////PEGA E LIMPA VARIAVEIS
$ppost = cleangetpost($_POST,$conn);
@extract($ppost);
$arval = $ppost;

$gget = cleangetpost($_GET,$conn);
@extract($gget);

//CABECALHO
if (!isset($ispopup)) {
$ispopup=1;
}
if ($ispopup==1) {
	$menu = FALSE;
} else {
	$menu = TRUE;
}
$title = '';
$which_css = array(
"<link rel='stylesheet' type='text/css' href='css/geral.css' />",
"<link rel='stylesheet' type='text/css' media='screen' href='css/autosuggest.css' />"
);
$which_java = array(
"<script type='text/javascript' src='javascript/ajax_framework.js'></script>"
);
$title = 'Taxonomia';
$body= '';
FazHeader($title,$body,$which_css,$which_java,$menu);

//FAZ O CADASTRO QUANDO O USUARIO SUBMETEU
if (($final>0 && !empty($nomesciid)) || ($final==2))  {
	if (!empty($nomesciid) && $nomesciid!='nomesciid') {
		list($famid,$genusid,$speciesid,$infraspid) = gettaxaids($nomesciid,$conn);
	}
	//NO CASO DE EDICAO DE UM NOME
	$filename = '';
	if ($final==1 && !empty($nomesciid)) { 
		if ($infraspid>0) {
			$filename = 'infraespecie-popup.php';
		} else {
			if ($speciesid>0) {
				$filename = 'especie-popup.php';
			} else {
				if ($genusid>0) {
					$filename = 'genero-popup.php';
				} else {
					$filename = 'familia-popup.php';
				}
			}
		}
	} else {
		if ($final==2) { $filename = 'taxanew-form.php';}
		if ($final==3) { $filename = 'taxa-delete.php';}
		if ($final==4) { $filename = 'traits_coletorvariacao.php';}
	}
	if (!empty($filename)) {
	echo "
<br/>
<br/>
<form name='myform' action='".$filename."' method='post'>
  <input type='hidden' name='famid' value='".$famid."' />
  <input type='hidden' name='genusid' value='".$genusid."' />
  <input type='hidden' name='speciesid' value='".$speciesid."' />
  <input type='hidden' name='infraspid' value='".$infraspid."' />
  <input type='hidden' name='naoeimportacao' value='1' />
  <input type='hidden' name='ispopup' value='".$ispopup."' />
  <input type='hidden' name='taxavariacao' value='1' />
   <input type='hidden' name='nomesciid' value='".$nomesciid."' />
   <input type='hidden' name='apagavarsess' value='1' />
  <script language=\"JavaScript\">
    setTimeout(document.myform.submit(),0.0001);
  </script>
</form>";
	//
	}
} 
if (!isset($final) || ($final+0)==0 || ($final!=2 && empty($nomesciid))) {
//taxonomia
echo "
<br />
<form name='finalform' action='taxa-form.php' method='post' >
<input type='hidden' name='ispopup' value='".$ispopup."' />
<table align='center' class='myformtable' cellspacing='0' cellpadding='5'>
<thead>
<tr><td colspan='100%'>".GetLangVar('namecadastrar')." ".strtolower(GetLangVar('nameor')." ".GetLangVar('nameeditar')." ".GetLangVar('nametaxa'))."</td></tr>
</thead>
<tbody>
";
if ($bgi % 2 == 0){$bgcolor = $linecolor2 ;} else{$bgcolor = $linecolor1 ;} $bgi++;
echo "
<tr bgcolor = '".$bgcolor."'>
  <td colspan='100%'>
    <table>
      <tr>
        <td class='tdsmallboldleft'>".GetLangVar('nametaxonomy')."</td>
        <td>"; autosuggestfieldval3('search-name-simple.php','nomesci',$nomesci,'nomeres','nomesciid',$nomesciid,true,60);
echo "</td>
        <td align='left'><img height='13' src=\"icons/icon_question.gif\" ";
		$help = GetLangVar('notaneedtoselect');
		echo " onclick=\"javascript:alert('$help');\" alt='Help' />
        </td>
      </tr>
    </table>
</td>
</tr>";
if ($bgi % 2 == 0){$bgcolor = $linecolor2 ;} else{$bgcolor = $linecolor1 ;} $bgi++;
echo "
<tr bgcolor = '".$bgcolor."'>
  <td colspan='100%'>
    <table align='center'>
      <tr>
        <td align='center'>
          <input type='hidden' name='final' value='' />
          <input type = 'submit' class='bsubmit' value='".GetLangVar('nameeditar')." ".GetLangVar('nametaxa')."' onclick=\"javascript:document.finalform.final.value=1\" />
        </td>
        <td align='center'>&nbsp;&nbsp;&nbsp;<input type = 'submit' class='bblue' value='".GetLangVar('namenovo')." ".GetLangVar('nametaxa')."' onclick=\"javascript:document.finalform.final.value=2\" /></td>
        <td align='center'>&nbsp;&nbsp;<input type = 'submit' class='borange' value='Variação' onclick=\"javascript:document.finalform.final.value=4\" /></td>
        <!---
        <td align='center'><input type = 'submit' class='borange' value='".GetLangVar('nameexcluir')." ".GetLangVar('nametaxa')."' onclick=\"javascript:document.finalform.final.value=3\" /></td>
        --->
      </tr>
    </table>
  </td>
</tr>
</tbody>
</table>
</form>
";
}

$which_java = array(
"<script type='text/javascript' src='javascript/myjavascripts.js'></script>");
FazFooter($which_java,$calendar=FALSE,$footer=$menu);

?>