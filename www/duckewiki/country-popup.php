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
$which_css = array(
"<link href='css/geral.css' rel='stylesheet' type='text/css' />");
$which_java = array();
$body='';
$title = GetLangVar('namenovo')." ".GetLangVar('namecountry');
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
			$erro++;
	} 
	//checar se pais ja esta cadastrado
	$qq = "SELECT * FROM Country WHERE LOWER(Country)=LOWER('".$nome."')";
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
			$countnid = $rp['CountryID'];
			$country = $rp['Country'];
			echo "
<form >
  <input type='hidden' id='countryid' value='$countnid' >
  <input type='hidden' id='country' value='$country'>
  <script language=\"JavaScript\">
    setTimeout(
      function() {
        passnewidandtxtoselectfield('".$countryid_val."','countryid','".$country."','');
      }
      ,1000);
  </script>
</form>";
	} else {
		$arrayofvalues = array(
			'Country' => $nome
			);
		
		$newcount = InsertIntoTable($arrayofvalues,'CountryID','Country',$conn);
		if (!$newcount) {
			$erro++;
		} else {
			$ok++;
			echo "
<form >
  <input type='hidden' id='countryid' value='$newcount' >
  <input type='hidden' id='country' value='$nome'>
  <script language=\"JavaScript\">
    setTimeout(
      function() {
        passnewidandtxtoselectfield('".$countryid_val."','countryid','".$nome."','');
      }
      ,0.0001);
  </script>
</form>";
		}
		
		
	}
} 
else {
echo "
<br />
<table align='left' class='myformtable' cellpadding='5'>
<thead>
<tr >
<td >";
echo GetLangVar('namenovo')." ".mb_strtolower(GetLangVar('namecadastro'));
echo "</td></tr>
</thead>
<tbody>
<tr>
  <td>
    <table>
    <tr>
      <td class='tdsmallbold' align='right'>".GetLangVar('namepais')."*</td>
      <td class='tdformleft' colspan='2'>
        <form action=country-popup.php method='post'>
          <input type='hidden' name='countryid_val' value='$countryid_val'  />
          <input type='hidden' value='1' name='enviado' />
          <input type='text' name='nome' size='30%' value='$nome' />
        </td>
    </tr>
    <tr>
      <td>&nbsp;</td>
      <td align='right'><input type='submit' class='bsubmit' value=".GetLangVar('nameconcluir')." />
        </form>
      </td>
      <td align='left'>
          <form action=country-popup.php method='post'>
            <input type='hidden' name='countryid_val' value='$countryid_val'  />
            <input type='submit' class='breset' value=".GetLangVar('namevoltar')." />
          </form>
        </td>
      </tr>
    </table>
  </td>
</tr>
</tbody>
</table>
";
}
$which_java = array("<script type='text/javascript' src='javascript/myjavascripts.js'></script>");
FazFooter($which_java,$calendar=FALSE,$footer=$menu);

?>