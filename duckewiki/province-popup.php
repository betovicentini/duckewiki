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
$title = GetLangVar('namenovo')." ".GetLangVar('nameprovince');
$body = '';
FazHeader($title,$body,$which_css,$which_java,$menu);

if ($enviado=='1') {
	$nome = trim(ucfirst(strtolower($nome)));
	if (empty($nome)) {
		echo "
<br />
<table cellpadding=\"1\" width='50%' align='center' class='erro'>
<tr class='tdsmallbold' ><td align='center'>".GetLangVar('erro1')."</td></tr>";
			if (empty($nome)) {
				echo "
<tr class='tdsmallbold' ><td class='tdsmallnotes' align='center'>".GetLangVar('namenome')."</td></tr>";
			}
		echo "
</table><br />";
			$erro++;
	} 
	//checar se pais ja esta cadastrado
	$qq = "SELECT * FROM Province WHERE LOWER(Province)=LOWER('".$nome."') AND CountryID='".$countryid."'";
	$res = mysql_query($qq,$conn);
	$nres = mysql_numrows($res);
	if ($nres==1) {
			echo "
<br />
<table cellpadding=\"1\" width='50%' align='center' class='erro'>
  <tr class='tdsmallbold' ><td align='center'>".GetLangVar('erro3')."</td></tr>
</table>
<br />";
			$erro++;
			$rp = mysql_fetch_assoc($res);
			$provinceid = $rp['ProvinceID']."_".$rp['CountryID'];
			$province = $rp['Province'];
			echo "
<form >
  <input type='hidden' id='provinceid' value='$provinceid' />
  <input type='hidden' id='province' value='$province' />
  <script language=\"JavaScript\">
    setTimeout(
      function() {
        passnewidandtxtoselectfield('".$province_val."','provinceid','".$province."','');
        }
        ,1000);
  </script>
</form>";
	} else {
		$arrayofvalues = array(
			'Province' => $nome,
			'CountryID' => $countryid
			);

		$newcount = InsertIntoTable($arrayofvalues,'ProvinceID','Province',$conn);
		if (!$newcount) {
			$erro++;
		} else {
			$ok++;
			$provid = $provinceid."_".$countryid;
			echo "
<form >
  <input type='hidden' id='provinceid' value='$provid'  />
  <input type='hidden' id='province' value='$province' />
  <script language=\"JavaScript\">
    setTimeout(
      function() {
        passnewidandtxtoselectfield('".$province_val."','provinceid','".$province."','');
        }
        ,1000);
  </script>
</form>";
		}
	}
} 
else {
echo "
<br />
<table align='center' class='myformtable' cellpadding='5'>
<thead>
<tr >
<td >";
echo GetLangVar('namenovo')." ".strtolower(GetLangVar('namecadastro'));
echo "</td></tr>
</thead>
<tbody>
<tr>
  <td>
    <table>
    <form action=province-popup.php method='post'>
      <input type='hidden' name='province_val' value='$province_val' />
      <input type='hidden' name='countryid' value='$countryid' />
      <input type='hidden' value='1' name='enviado' />
      <tr>
        <td class='tdsmallbold' align='right'>".GetLangVar('namenome')."*</td>
        <td class='tdformleft' colspan='2'><input type='text' name='nome' size='30%' value='$nome'></td>
      </tr>
      <tr>
        <td>&nbsp;</td>
        <td align='right'><input type='submit' class='bsubmit' value=".GetLangVar('nameconcluir')." /></td>
</form>
<form action=province-popup.php method='post'>
  <input type='hidden' name='province_val' value='$province_val' />
  <input type='hidden' name='countryid' value='$countryid' />
          <td align='left'><input type='submit' class='breset' value=".GetLangVar('namevoltar')." /></td>
</form>
        </tr>
      </table>
    </td>
</tr>
</tbody>
</table>";


}

$which_java = array("<script type='text/javascript' src='javascript/myjavascripts.js'></script>");
FazFooter($which_java,$calendar=FALSE,$footer=$menu);

?>