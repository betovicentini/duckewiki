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
$which_css = array(
"<link href='css/geral.css' rel='stylesheet' type='text/css' />",
"<link rel='stylesheet' type='text/css' href='css/cssmenu.css' />"
);
$which_java = array(
"<script type='text/javascript' src='css/cssmenuCore.js'></script>",
"<script type='text/javascript' src='css/cssmenuAddOns.js'></script>",
"<script type='text/javascript' src='css/cssmenuAddOnsItemBullet.js'></script>",
"<script type='text/javascript' src='javascript/filterlist.js'></script>"
);
$title = 'Especimenes';
$body = '';
FazHeader($title,$body,$which_css,$which_java,$menu);
if (!empty($especimenesids)) {
			$arraylist = explode(";",$especimenesids);
			$nomearr = array();
			foreach ($arraylist as $key => $value) {
				$qq = "SELECT * FROM Especimenes JOIN Pessoas ON  ColetorID=PessoaID WHERE EspecimenID='".$value."'";
				$wrr = mysql_query($qq,$conn);
				$aa = mysql_fetch_assoc($wrr);
				$nome = $aa['Abreviacao']." ".$aa['Number'];
				$nome = array($nome);
				$nomearr = array_merge((array)$nomearr,(array)$nome);
			}
			$nometxt = implode("; ",$nomearr);

			//or
			$nspecs = count($arraylist);
			$nometxt = $nspecs." ".GetLangVar('nameregistro')."s";
} else {
	$nometxt = "0 ".mb_strtolower(GetLangVar('nameregistro'))."s";
	$especimenesids = '';
}
$kv = 'selec_'.$_SESSION['userid'];
if ($final==1) {
		$_SESSION[$kv] = $especimenesids;
echo "
<form name='myform' >
  <script language=\"JavaScript\">
      setTimeout(
          function() {
            var element = self.opener.document.getElementById('".$elementtxtid."');
            element.innerHTML = '".($nometxt)."';
            var destination = self.opener.document.getElementById('".$elementid."');
            destination.value = '$especimenesids';
            window.close();
            } ,0.0001);
    </script>
</form>
";
} else {
if (isset($_SESSION[$kv])) {
	$destlistlist = $_SESSION[$kv];
}
if ($specsids==1) {
	$destlistlist = $_SESSION['especsids'];
}

echo "<br />
<form method='post' name='labelform'>
<table class='myformtable' align='left' cellpadding=\"5\">
<thead>
<tr ><td colspan='100%'>".GetLangVar('nameselect')." ".GetLangVar('namecoleta')."s</td></tr>
</thead>
<tbody>
<tr class='tabsubhead'>
  <td>".GetLangVar('namedisponivel')."</td>
  <td>&nbsp;</td>
  <td>".GetLangVar('nameselecionado')."</td>
</tr>
<tr>
  <td>
    <select name='srcList' multiple size='10'>";
	$qq = "SELECT * FROM Especimenes JOIN Pessoas ON  ColetorID=PessoaID $restrict ORDER BY Abreviacao,Number+0 ASC";
	$wrr = mysql_query($qq,$conn);
	while ($aa = mysql_fetch_assoc($wrr)){
		$nome = $aa['Abreviacao']." ".$aa['Number']." (".$aa['Day']."-".$aa['Mes']."-".$aa['Ano'].")";
		echo "
      <option value='".$aa['EspecimenID']."'>$nome</option>";
	}
echo "
      </select>
  </td>
  <td align='center'>
    <input type='button' value=' >> ' class='breset' onClick=\"javascript:addSrcToDestList('labelform');\">
    <br />";
//if ($_SESSION['editando']!=1) {
	echo "
  <br />
  <input type='button' value=' << ' class='breset' onclick=\"javascript:deleteFromDestList('labelform');\">";
//}
echo "
  </td>
  <td>
    <select name='destList' multiple size='10'>";
	if (!empty($destlistlist)) {
			$arraylist = explode(";",$destlistlist);
			foreach ($arraylist as $key => $value) {
				$qq = "SELECT * FROM Especimenes JOIN Pessoas ON  ColetorID=PessoaID WHERE EspecimenID='".$value."'";
				$wrr = mysql_query($qq,$conn);
				$aa = mysql_fetch_assoc($wrr);
				$nome = $aa['Abreviacao']." ".$aa['Number']." (".$aa['Day']."-".$aa['Mes']."-".$aa['Ano'].")";
				echo "
      <option value='".$aa['EspecimenID']."'>$nome</option>";
			}
	}
echo "
    </select>
</td>
</tr>
<script type=\"text/javascript\">
<!--
var myfilter = new filterlist(document.labelform.srcList);
//-->
</script>    
    <tr>
      <td colspan='100%'>
        <table cellpadding='5'>
          <tr>
            <td>Filtrar:</td>
            <td><input name='regexp' onKeyUp=\"myfilter.set(this.value);\" /></td>
            <td><input type='button' onclick=\"myfilter.set(this.form.regexp.value)\" value=\"Filtrar\" /></td>
            <td><input type='button' onclick=\"myfilter.reset();this.form.regexp.value=''\" value=\"Limpar\" /></td>
          </tr>
          <tr><td colspan='4'><input type='checkbox' name=\"toLowerCase\" onclick=\"myfilter.set_ignore_case(!this.checked);\" />&nbsp;Case sensitive</td></tr>
         </table>
     </td>
    </tr>
</form>
<form method='post' name='finalform' action='selectespecimene-popup.php'>
  <input type='hidden' name='especimenesids' value='".$especimenesids."' />
  <input type='hidden' name='elementid' value='".$elementid."' />
  <input type='hidden' name='elementtxtid' value='".$elementtxtid."' />
  <input type='hidden' name='final' value='1' />
</form>
<tr>
  <td colspan='100%'>
    <table align='center'>
      <tr>
        <td><input type='button' value='".GetLangVar('nameenviar')."' class='bsubmit' onClick = \"javascript:sendarrayatoself('labelform','destList','finalform','especimenesids');\" /></td>
<!---
<form method='post' action='label-form>
        <td><input type='submit' value='".GetLangVar('namereset')."' class='breset' /></td>
</form>
--->
      </tr>
    </table>
   </td>
</tr>
</table>
</form>
";
}

$which_java = array("<script type='text/javascript' src='javascript/myjavascripts.js'></script>",
"<!-- Create Menu Settings: (Menu ID, Is Vertical, Show Timer, Hide Timer, On Click ('all' or 'lev2'), Right to Left, Horizontal Subs, Flush Left, Flush Top) -->",
"<script type='text/javascript'>qm_create(0,false,0,500,false,false,false,false,false);</script>");
FazFooter($which_java,$calendar=FALSE,$footer=$menu);

?>

