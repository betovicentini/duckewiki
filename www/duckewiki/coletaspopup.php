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
$title = '';
$which_css = array("<link href='css/geral.css' rel='stylesheet' type='text/css' />");
$which_java = array();
$body='';
$title = GetLangVar('namecoleta');
FazHeader($title,$body,$which_css,$which_java,$menu);

echo "
<br />
<form method='post' name='addcolpop'>
<table class='myformtable' align='left' cellpadding=\"5\">
<thead>
<tr>
  <td width=150>".GetLangVar('namedisponivel')."</td>
  <td width=20>&nbsp;</td>
  <td width=150>".GetLangVar('nameselecionado')."</td>
</tr>
</thead>
<tbody>
<tr>
  <td>
    <select name=srcList multiple size=10>";
	$qq = "SELECT * FROM Especimenes JOIN Pessoas ON ColetorID=PessoaID ORDER BY Abreviacao,Number+0 ASC";
	$rrr = mysql_query($qq,$conn);
	while ($row = mysql_fetch_assoc($rrr)) {
		echo "
      <option value=".$row['EspecimenID'].">".$row['Abreviacao']." ".$row['Number']."</option>";
	}
echo "
    </select>
  </td>
  <td width='30' align='center'>
    <input type='button' value=' >> ' class='breset' onClick=\"javascript:addSrcToDestList('addcolpop');\" />
    <br /><br />";
	echo "
    <input type='button' value=' << ' class='breset' onclick=\"javascript:deleteFromDestList('addcolpop');\" />";
//}
echo "
  </td>
  <td>
    <select name=destList multiple size=10>";
	if (!empty($getespecimensids)) {
		$especimensids = explode(";",$getespecimensids);
		foreach ($especimensids as $especid) {
			$qq = "SELECT * FROM Especimenes JOIN Pessoas ON ColetorID=PessoaID WHERE EspecimenID='".$especid."'";
			$rrr = mysql_query($qq,$conn);
			$row = mysql_fetch_assoc($rrr);
			echo "
      <option value=".$row['EspecimenID'].">".$row['Abreviacao']." ".$row['Number']."</option>";
		}
	}
echo "
    </select>
  </td>
</tr>
<tr>
  <td colspan='3' align='center'>
    <table>
      <tr>
        <td><input type='button' value=".GetLangVar('nameenviar')." class='bsubmit' onClick =\"javascript:MyArray('addcolpop','$formname','especimensids','especimenstxt');self.opener.window.document.forms['$formname'].submit();\" /></td>
        <td><input type=button value='".mb_strtolower(GetLangVar('namenova'))."' class='bblue' ";
        $myurl ="especimenes_dataform.php?submeteu=nova";
        echo " onclick = \"javascript:small_window('$myurl',700,500,'Nova Coleta');\" /></td>
        <td><input type=button value='".GetLangVar('nameatualizar')." ".mb_strtolower(GetLangVar('namelista'))."' class='borange' onclick = \"javascript:location.reload(true);\" /></td>
      </tr>
    </table>
  </td>
</tr>
</tbody>
</table>
</form>";

$which_java = array("<script type='text/javascript' src='javascript/myjavascripts.js'></script>");
FazFooter($which_java,$calendar=FALSE,$footer=$menu);

?>