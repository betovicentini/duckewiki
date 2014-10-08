<?php
session_start();
//INCLUI FUNCOES PHP E VARIAVEIS
include "functions/HeaderFooter.php";
include "functions/SelectOptions.php";

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
if ($ispopup==1) {
	$menu = FALSE;
} else {
	$menu = TRUE;
}
$title = '';
$which_css = array(
"<link rel='stylesheet' type='text/css' href='css/geral.css'>",
"<link rel='stylesheet' type='text/css' media='screen' href='css/autosuggest.css' >",
"<link rel='stylesheet' type='text/css' href='css/cssmenu.css' >"
);
$which_java = array(
"<script type='text/javascript' src='javascript/ajax_framework.js'></script>",
"<script type='text/javascript' src='css/cssmenuCore.js'></script>",
"<script type='text/javascript' src='css/cssmenuAddOns.js'></script>",
"<script type='text/javascript' src='css/cssmenuAddOnsItemBullet.js'></script>"
);
newheader($title,$body,$which_css,$which_java,$menu);
echo "
<br>
<table class='myformtable' align='center' cellpadding='4' cellspacing='0'>
<thead>
<tr >";
if ($newsp==2 || $newsp==3) {
	echo "
  <td colspan=4 class='tabhead'>".GetLangVar('namecadastrar')." ".GetLangVar('namespecies')."</td>";
}
if ($newsp==4 || $newsp==5) {
	echo "
  <td colspan=4 class='tabhead'>".GetLangVar('namecadastrar')." ".GetLangVar('nameinfraspecies')."</td>";
}

echo "
</tr>
</thead>
<tbody>
<tr>
  <td class='tdsmallbold' align='right'>".GetLangVar('messagepertencea')."</td>
  <td colspan=3>
<form action=taxanew-exec.php method='post'>
  <input type='hidden' name='newsp' value='$newsp'>
  <input type='hidden' name='pertencea' value='1'>
    <table align='left' border=0 cellpadding=\"3\" cellspacing=\"0\">
    <tr>";

if ($pertencea!=1) {
	if ($newsp<4) {
		echo "
  <td class='tdformnotes'>";
  autosuggestfieldval3('search-famgen.php','genus',$genus,'genusres','genusid',$genusid,true,60);
echo "</td>";
	} else {
		echo "
  <td class='tdformnotes'>";
  autosuggestfieldval3('search-species.php','species',$species,'speciesnameres','speciesid',$speciesid,true,60);
echo "</td>";
	}
	echo "
  <td align='center'><input type='submit' class='bsubmit' value='".GetLangVar('namecontinuar')."'></td>";
} else {
	if ($newsp<4) {
		echo "
  <td><input type=text class='selectedval' value='".$genus."' readonly></td>";
	} else {
		echo "
  <td><input type=text class='selectedval' value='".$species."' readonly></td>";
	}
}
echo "
</tr>
</table>
</form>
</td>
</tr>";
if ((($newsp==2 || $newsp==3) && !empty($genusid)) || (($newsp==4 || $newsp==5) && !empty($speciesid))) {
echo "
<form action=taxanewregister.php method='post'>
  <input type='hidden' name='famid' value='$famid'>
  <input type='hidden' name='genusid' value='$genusid'>
  <input type='hidden' name='speciesid' value='$speciesid'>
  <input type='hidden' name='newsp' value='$newsp'>";
if ($newsp==4 || $newsp==5) {
echo "
<tr>
  <td class='tdsmallbold' align='right'>".GetLangVar('nametipo')."</td>
  <td colspan=3>
    <table>
      <tr>
        <td>";
	if ($newsp==4) {
	$qq = "SELECT DISTINCT InfraEspecieNivel FROM Tax_InfraEspecies WHERE InfraEspecieNivel IS NOT NULL AND InfraEspecieNivel<>' ' ORDER BY InfraEspecieNivel ";
	$qqq = mysql_query($qq,$conn);

	echo "
          <select name='subvar'>
            <option selected value=$subvar>$subvar</option>";
		while ($rw = mysql_fetch_assoc($qqq)) {
			echo "
            <option value=".$rw['InfraEspecieNivel'].">".$rw['InfraEspecieNivel']."</option>";
		}
	echo "
          </select>";
	} else {
		echo "
          <select name='subvar'>
            <option selected value='morfossp'>morfossp</option>
          </select>";
	}
echo "
        </td>
      </tr>
    </table>
  </td>
</tr>";
}
echo "
<tr>
  <td class='tdsmallbold' align='right'>".GetLangVar('namenome')."</td>
  <td colspan=3>
    <table>
      <tr>
        <td><input name='spnome' type='text' value='$spnome' size='30'></td>
        <td class='tdsmallbold' align='right'>".GetLangVar('nameautor')."</td>";
if ($newsp==2 || $newsp==4) {
	echo "
        <td><input type='text' value='$autor' name='autor' size='15'></td>";
} else {
	echo "
        <td>
          <select  name='autor' >
            <option value=''>".GetLangVar('nameselect')."</option>";
			$rrr = getpessoa('',$abb=true,$conn);
			while ($row = mysql_fetch_assoc($rrr)) {
				$rv = trim($row['Abreviacao']);
				if (!empty($rv)) {
					echo "
            <option value=".$row['Abreviacao'].">".$row['Abreviacao']." (".$row['Prenome'].")</option>";
				}
			}
	echo "
          </select>
        </td>";
}
echo "
      </tr>
    </table>
  </td>
</tr>";
if ($newsp==2 || $newsp==4) {
echo "
<tr>
  <td class='tdsmallbold' align='right'>".GetLangVar('namejournal')."</td>
  <td colspan=3>
    <table>
      <tr>
        <td><input name='pubrevista' type='text' value='$pubrevista' size='58'></td>
      </tr>
    </table>
  </td>
</tr>
<tr>
  <td class='tdsmallbold' align='right'>".GetLangVar('namevolume')."</td>
  <td colspan=3>
    <table>
      <tr>
        <td><input name='pubvolume' type='text' value='$pubvolume' size='30'></td>
        <td class='tdsmallbold' align='right'>".GetLangVar('nameano')."</td>
        <td><input name='pubano' type='text' value='$pubano' size='15'></td>
      </tr>
    </table>
  </td>
</tr>
<tr>
  <td class='tdsmallbold' align='right'>".GetLangVar('namegeodistribution')."</td>
  <td colspan=3>
    <table>
      <tr>
        <td>
          <textarea name='geodist' cols=50 rows=2>$geodist</textarea></td>
      </tr>
    </table>
  </td>
</tr>";
}
echo "
<tr>
  <td class='tdsmallbold' align='right'>".GetLangVar('nameobs')."</td>
  <td colspan=3>
    <table>
      <tr><td><textarea name='notas' cols=50 rows=3>$notas</textarea></td></tr>
    </table>
  </td>
</tr>
<tr>
  <td colspan=4>
    <table align='center'>
      <tr>
        <td align='center'><input type = 'submit' class='bsubmit' value='".GetLangVar('nameenviar')."'></td>
        <td>&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;</td>
</form>
<form action=taxa-form.php method='post'>
        <td align='center'><input type = 'submit' class='breset' value='".GetLangVar('namereset')."'></td>
</form>
      </tr>
    </table>
  </td>
</tr>";
}

echo "
</tbody>
</table>
";

$which_java = array("<script type='text/javascript' src='javascript/popupform.js'></script>",
"<!-- Create Menu Settings: (Menu ID, Is Vertical, Show Timer, Hide Timer, On Click ('all' or 'lev2'), Right to Left, Horizontal Subs, Flush Left, Flush Top) -->",
"<script type='text/javascript'>qm_create(0,false,0,500,false,false,false,false,false);</script>");
newfooter($which_java,$calendar=FALSE,$footer=$menu);


?>