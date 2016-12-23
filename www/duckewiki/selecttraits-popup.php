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
$title = 'Seleciona variáveis';
$body = '';
FazHeader($title,$body,$which_css,$which_java,$menu);

if (!empty($traitids)) {
			$arraylist = explode(";",$traitids);
			$_SESSION[$kv] = serialize($traitids);
			$nspecs = count($arraylist);
			$nometxt = $nspecs." ".GetLangVar('nameregistro')."s";
} else {
	$nometxt = "0 ".mb_strtolower(GetLangVar('nameregistro'))."s";
	$traitids = '';
}
$kv = 'traitssel'.$_SESSION['userid'];
if ($final==1) {

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
} 
else {
if (!empty($_SESSION[$kv])) {
	$arrayoftraists = unserialize($_SESSION[$kv]);
	//echopre($arrayoftraists);
}
echo "<br />
<form method='post' name='labelform'>
<table class='myformtable' align='left' cellpadding=\"5\">
<thead>
<tr ><td colspan='100%'>Seleciona variáveis</td></tr>
</thead>
<tbody>
<tr class='tabsubhead'>
  <td>".GetLangVar('namedisponivel')."</td>
  <td>&nbsp;</td>
  <td>".GetLangVar('nameselecionado')."</td>
</tr>
<tr>
  <td>
        <select name='srcList' multiple size='10' style=\"width:500px;\">";
		$filtro ="SELECT * FROM `Traits` WHERE `TraitName`<>'' AND TraitTipo<>'Estado' AND TraitTipo<>'Classe' ORDER BY `PathName` ASC";
		$res = mysql_query($filtro,$conn);
		while ($aa = mysql_fetch_assoc($res)){
			$PathName = $aa['PathName'];
			$level = $aa['MenuLevel'];
			$tipo = $aa['TraitTipo'];
			if ($level==1) {
				//$espaco='';
			} else {
				//$espaco = str_repeat('&nbsp;&nbsp;&nbsp;',$level);
			}
			if ($tipo=='Classe') { //if is a class or a state does not allow selection
				echo "          
        <option style='color:  #990000; font-size: 1.3em;' value=''>$espaco<i>".($aa['TraitName'])."</i></option>";
			} 
			else {
				//$espaco = $espaco.str_repeat('- ',$level-1);
				$tp = explode("|",$tipo);
				if ($tp[1]=='Categoria') {
					$qu = "SELECT * FROM `Traits` WHERE `ParentID`='".$aa['TraitID']."'";
					$ru = mysql_query($qu,$conn);
					$ncat = mysql_numrows($ru);
					$std = array();
					while ($rwu = mysql_fetch_assoc($ru)) {
						$std[] = mb_strtolower($rwu['TraitName']);
					}
					$stads = implode(";",$std);
					$stlen = strlen($stads);
					if ($stlen>30) {
						$stt = substr($stads,0,50);
						$stt = $stt."....";
					} else {
						$stt = $stads;
					}
					$nn = $aa['PathName']." [Categorias: $stt]";
					$bgcol = "#E0FFFF";
				} 
				else {
					$nn = $aa['PathName']." [".$tp[1]."]";
					if ($tp[1]=='Quantitativo') {
						$bgcol = "#FAFAD2";
					}
					if ($tp[1]=='Texto') {
						$bgcol = "#D3D3D3";
					}
					if ($tp[1]=='Imagem') {
						$bgcol = "#FFE4E1";
					}
				}
				echo "
        <option style='background: ".$bgcol.";' value='".$aa['TraitID']."|".$aa['PathName']."' alt=\"$nn\" >".$espaco.$nn."</option>";
				
			}
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
        <select name='destList' multiple size='10' style=\"max-width:300px;\">";
		if (count($arrayoftraists)>0) {
			foreach ($arrayoftraists as $thetrait) {
				$val = $thetrait+0;
				$qq = "SELECT * FROM `Traits` WHERE `TraitID`='".$val."'";
				$rr = mysql_query($qq,$conn);
				$rwt = mysql_fetch_assoc($rr);
				echo "
          <option selected value='".$rwt['TraitID']."|".$rwt['PathName']."'>".$rwt['PathName']."</option>";
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
<form method='post' name='finalform' action='selecttraits-popup.php'>
  <input type='hidden' name='especimenesids' value='".$especimenesids."' />
  <input type='hidden' name='elementid' value='".$elementid."' />
  <input type='hidden' name='elementtxtid' value='".$elementtxtid."' />
  <input type='hidden' name='final' value='1' />
  <input type='hidden' name='ispopup' value='".$ispopup."' />
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

