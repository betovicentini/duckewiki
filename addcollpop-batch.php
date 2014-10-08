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
"<link href='css/geral.css' rel='stylesheet' type='text/css' >",
"<link rel='stylesheet' type='text/css' href='css/cssmenu.css' >"
);
$which_java = array(
"<script type='text/javascript' src='css/cssmenuCore.js'></script>",
"<script type='text/javascript' src='css/cssmenuAddOns.js'></script>",
"<script type='text/javascript' src='css/cssmenuAddOnsItemBullet.js'></script>"
);
$body='';
$title = GetLangVar('namecoletor');
FazHeader($title,$body,$which_css,$which_java,$menu);

if (empty($valuevar)) { $valuevar = 'addcolvalue';}
if (empty($valuetxt)) { $valuetxt = 'addcoltxt';}

if ($final!=1) {
echo "
<form name='addcolpop' method='post' action='addcollpop-batch.php'>
<table class='tableform' align='center' cellpadding=\"5\" />
  <input type='hidden' value='$especimenesids' name='especimenesids' />
  <input type='hidden' value='$formname' name='formname' />
  <input type='hidden' value='$valuetxt' name='valuetxt' />
  <input type='hidden' name='valuevar' value='$valuevar' />
  <input type='hidden' name='getaddcollids' value='$getaddcollids' />
<tr class='tabhead'>
  <td width=150>".GetLangVar('namedisponivel')."</td>
  <td width=20>&nbsp;</td>
  <td width=150>".GetLangVar('nameselecionado')."</td>
</tr>
<tr>
  <td>
    <select name=srcList multiple size=10>";
	$rrr = getpessoa('',$abb=TRUE,$conn);
	while ($row = mysql_fetch_assoc($rrr)) {
		echo "
      <option value=".$row['PessoaID'].">".$row['Abreviacao']."</option>";
	}
echo "
    </select>
  </td>
  <td width='30' align='center'>
      <input type='button' value=' >> ' class='breset' onClick=\"javascript:addSrcToDestList('addcolpop');\" />
      <br />";
//if ($_SESSION['editando']!=1) {
	echo "
      <br />
      <input type='button' value=' << ' class='breset' onclick=\"javascript:deleteFromDestList('addcolpop');\">";
//}
echo "
  </td>
  <td>
    <select name=destList multiple size=10>";
	if (!empty($getaddcollids)) {
		$addcollids = explode(";",$getaddcollids);
		//print_r($addcollids);
		foreach ($addcollids as $addcoid) {
			$rrr = getpessoa($addcoid,$abb=TRUE,$conn);
			$row = mysql_fetch_assoc($rrr);
			echo "
      <option value=".$row['PessoaID'].">".$row['Abreviacao']."</option>";
		}
	}
echo "
    </select>
  </td>
</tr>
<tr>
  <td colspan=3 align='center'>
    <br />
    <input type='button' value=".GetLangVar('nameenviar')." class='bsubmit' onclick =\"javascript:sendarrayatoself('addcolpop','destList','finalaqui','getaddcollids');\" />
  </td>
</tr>
</form>
<form name='finalaqui' action='addcollpop-batch.php' method='post'>
  <input type='hidden' name='allinlist' value='' />
  <input type='hidden' name='final' value='1' />
  <input type='hidden' value='$especimenesids' name='especimenesids' />
  <input type='hidden' value='$formname' name='formname' />
  <input type='hidden' value='$valuetxt' name='valuetxt' />
  <input type='hidden' name='valuevar' value='$valuevar' />
  <input type='hidden' name='applytoallinlist' value='$applytoallinlist' />
  <input type='hidden' name='getaddcollids' value='$getaddcollids' />
</form>
</table>
";
} else {
	$addcolvalue = $getaddcollids;
	$addcolarr = explode(";",$getaddcollids);
	$addcoltxt = '';
	$j=1;
	foreach ($addcolarr as $kk => $val) {
			$qq = "SELECT * FROM Pessoas WHERE PessoaID='$val'";
			$res = mysql_query($qq,$conn);
			$rrw = mysql_fetch_assoc($res);
			if ($j==1) {
				$addcoltxt = 	$rrw['Abreviacao'];
			} else {
				$addcoltxt = $addcoltxt."; ".$rrw['Abreviacao'];
			}
			$j++;
		}
	
	echo "
<form >
  <input type='hidden' id='addcolvalue' value='$addcolvalue' />
  <input type='hidden' id='addcoltxt' value='$addcoltxt' />
  <script language=\"JavaScript\">
    setTimeout(
      function() {";
				if (!empty($especimenesids) && 	$allinlist>0) {
					$specarr = explode(";",$especimenesids);
					$ns = count($specarr);
					$coun = 1;
					foreach ($specarr as $iv) {
					echo "
          sendval_innerHTML('addcoltxt','addcoltxt_".$iv."');
          sendvalclosewin('addcolvalue_".$iv."','$addcolvalue')";
					if ($coun<$ns) { echo ";";}
				}	
				} else {
				echo "
          sendval_innerHTML('addcoltxt','$valuetxt');
          sendvalclosewin('$valuevar','$addcolvalue')";
		}
			echo "
        },
        0.0001);
  </script>
</form>";

}


$which_java = array("<script type='text/javascript' src='javascript/myjavascripts.js'></script>",
"<!-- Create Menu Settings: (Menu ID, Is Vertical, Show Timer, Hide Timer, On Click ('all' or 'lev2'), Right to Left, Horizontal Subs, Flush Left, Flush Top) -->",
"<script type='text/javascript'>qm_create(0,false,0,500,false,false,false,false,false);</script>");
FazFooter($which_java,$calendar=FALSE,$footer=$menu);

?>