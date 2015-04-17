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
if ($ispopup==1) {
	$menu = FALSE;
} else {
	$menu = TRUE;
}
$title = '';
$which_css = array(
"<link href='css/geral.css' rel='stylesheet' type='text/css' >"
);
$which_java = array(
"<script type='text/javascript' src='javascript/filterlist.js'></script>"
);
$body='';
$title = GetLangVar('namecoletor');
FazHeader($title,$body,$which_css,$which_java,$menu);

if (empty($valuevar)) { $valuevar = 'addcolvalue';}
if (empty($valuetxt)) { $valuetxt = 'addcoltxt';}
echo "
<form name='addcolpop' method='post'>
  <table class='tableform' align='center' cellpadding=\"5\">
  <tr class='tabhead'>
    <td width=150>".GetLangVar('namedisponivel')."</td>
    <td width=20>&nbsp;</td>
    <td width=150>".GetLangVar('nameselecionado')."</td>
  </tr>
  <tr>
    <td>
      <select name='srcList' multiple size='10'>";
	$rrr = getpessoa('',$abb=TRUE,$conn);
	while ($row = mysql_fetch_assoc($rrr)) {
		echo "
        <option value=".$row['PessoaID'].">".$row['Abreviacao']."  [".$row['Prenome']."]</option>";
	}
echo "
      </select>
    </td>
    <td width='30' align='center'>
      <input type='button' value=' >> ' class='breset' onClick=\"javascript:addSrcToDestList('addcolpop');\">
      <br />
      <br />
        <input type='button' value=' << ' class='breset' onclick=\"javascript:deleteFromDestList('addcolpop');\">
    </td>
    <td>
      <select name='destList' multiple size='10'>";
	if (!empty($getaddcollids)) {
		$addcollids = explode(";",$getaddcollids);
		//print_r($addcollids);
		foreach ($addcollids as $addcoid) {
			$rrr = getpessoa($addcoid,$abb=TRUE,$conn);
			$row = mysql_fetch_assoc($rrr);
			echo "
        <option value=".$row['PessoaID'].">".$row['Abreviacao']." [".$row['Prenome']."]</option>";
		}
	}
echo "
      </select>
    </td>
  </tr>
<script type=\"text/javascript\">
<!--
var myfilter = new filterlist(document.addcolpop.srcList);
//-->
</script>    
    <tr>
      <td colspan='100%'>
        <table>
          <tr>
            <td>Filtrar:</td>
            <td><input name='regexp' onKeyUp=\"myfilter.set(this.value);\" /></td>
            <td><input type='button' onclick=\"myfilter.set(this.form.regexp.value)\" value=\"Filtrar\" /></td>
            <td><input type='button' onclick=\"myfilter.reset();this.form.regexp.value=''\" value=\"Limpar\" /></td>
          </tr>
          <tr><td colspan='100%'><input type='checkbox' name=\"toLowerCase\" onclick=\"myfilter.set_ignore_case(!this.checked);\" />&nbsp;Case sensitive</td></tr>
         </table>
     </td>
    </tr>
  
  <tr>
    <td colspan='100%' align='center'>
      <br />
      <input type='button' value=".GetLangVar('nameenviar')." class='bsubmit' onclick =\"javascript:MyArray('addcolpop','$formname','$valuevar','$valuetxt');\">
    </td>
  </tr>
</table>
</form>";

$which_java = array("<script type='text/javascript' src='javascript/myjavascripts.js'></script>",
"<!-- Create Menu Settings: (Menu ID, Is Vertical, Show Timer, Hide Timer, On Click ('all' or 'lev2'), Right to Left, Horizontal Subs, Flush Left, Flush Top) -->",
"<script type='text/javascript'>qm_create(0,false,0,500,false,false,false,false,false);</script>");
FazFooter($which_java,$calendar=FALSE,$footer=$menu);

?>