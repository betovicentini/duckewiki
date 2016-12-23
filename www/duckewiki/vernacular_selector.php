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
$which_css = array(
"<link href='css/geral.css' rel='stylesheet' type='text/css' />");
$which_java = array();
$title = GetLangVar('namevernacular');
$body = '';
FazHeader($title,$body,$which_css,$which_java,$menu);
echo "
<form method='post' name='vernacularpop'>
<table class='myformtable' align='left' cellpadding=\"5\">
<thead>
<tr>
  <td width='150'>".GetLangVar('namedisponivel')."</td>
  <td width='20'>&nbsp;</td>
  <td width='150'>".GetLangVar('nameselecionado')."</td>
</tr>
</thead>
<tbody>
<tr>
  <td>
    <select name=srcList multiple size='10'>";
	$wrr = getvernacular('',$conn);
			while ($aa = mysql_fetch_assoc($wrr)){
				echo "
      <option value='".$aa['VernacularID']."'>".$aa['Vernacular']."</option>";
	}
echo "
    </select>
  </td>
  <td width='30' align='center'>
    <input type='button' value=' >> ' class='breset' onClick=\"javascript:addSrcToDestList('vernacularpop');\" />
    <br />
    <br />
    <input type='button' value=' << ' class='breset' onclick=\"javascript:deleteFromDestList('vernacularpop');\" />
  </td>
  <td>
    <select name=destList multiple size='10'>";
	if (!empty($getvernacularids)) {
		$vernacularids = explode(";",$getvernacularids);
		//print_r($vernacularids);
		foreach ($vernacularids as $verid) {
			$wrr = getvernacular($verid,$conn);
			$aa = mysql_fetch_assoc($wrr);
			echo "
      <option value='".$aa['VernacularID']."'>".$aa['Vernacular']."</option>";
		}
	}
echo "
    </select>
  </td>
</tr>
<tr>
  <td colspan='3' align='center'>
  <br />
    <table align='center'>
      <tr>
        <td><input type='button' value=".GetLangVar('nameenviar')." class='bsubmit' onClick=\"javascript:MyArray('vernacularpop','$formname','vernacularvalue','vernaculartxt');\" /></td>
        <td><input type=button value=".GetLangVar('namenovo')."  class='bblue' ";
		$myurl ="vernacular_dataform.php?submitted=novo"; 
echo  " onclick = \"javascript:small_window('$myurl',500,500,'Vernacular');\" /></td>
        <td><input type=button value='".GetLangVar('nameatualizar')." ".mb_strtolower(GetLangVar('namelista'))."' class='borange' onclick = \"javascript:location.reload(true);\" /></td>
      </tr>
    </table>
  </tr>
</tbody>
</table>
</form>
";
$which_java = array("<script type='text/javascript' src='javascript/myjavascripts.js'></script>");
FazFooter($which_java,$calendar=FALSE,$footer=$menu);
?>