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
$title = GetLangVar('namenovo')." ".GetLangVar('namemunicipio');
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
			$erro++;
echo "
</table>
<br />";
	} 
	//checar se pais ja esta cadastrado
	$qq = "SELECT * FROM Municipio WHERE LOWER(Municipio)=LOWER('".$nome."') AND ProvinceID='".$provinceid."'";
	$res = mysql_query($qq,$conn);
	$nres = mysql_numrows($res);
	if ($nres==1) {
			echo "
<br />
<table cellpadding=\"1\" width='50%' align='center' class='erro'>
  <tr class='tdsmallbold' ><td align='center'>".GetLangVar('erro3')."</td></tr>
</table><br />";
			$erro++;
			$rp = mysql_fetch_assoc($res);
			$municipioid = $rp['MunicipioID']."_".$rp['ProvinceID'];
			$municipio = $rp['Municipio'];
			echo "
<form >
  <input type='hidden' id='municipioid' value='$municipioid'  />
  <input type='hidden' id='municipio' value='$municipio' />
  <script language=\"JavaScript\">
    setTimeout(
      function() {
        passnewidandtxtoselectfield('".$municipioid_val."','municipioid','".$municipio."','');
        }
        ,1000);
  </script>
</form>";
	} 
	else {
		$arrayofvalues = array(
			'ProvinceID' => $provinceid,
			'Municipio' => $nome
			);

		$newcount = InsertIntoTable($arrayofvalues,'MunicipioID','Municipio',$conn);
		if (!$newcount) {
			$erro++;
		} else {
			$ok++;
			$muniid = $municipioid."_".$provinceid;
			echo "
<form >
  <input type='hidden' id='municipioid' value='$muniid' />
  <input type='hidden' id='municipio' value='$municipio' />
  <script language=\"JavaScript\">
    setTimeout(
      function() {
        passnewidandtxtoselectfield('".$municipioid_val."','municipioid','".$municipio."','');
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
      <form action=municipio-popup.php method='post'>
        <input type='hidden' name='municipioid_val' value='$municipioid_val' />
        <input type='hidden' name='provinceid' value='$provinceid' />
        <input type='hidden' value='1' name='enviado' />
        <tr>
          <td class='tdsmallbold' align='right'>".GetLangVar('namenome')."*</td>
          <td class='tdformleft' colspan='2'><input type='text' name='nome' size='30%' value='$nome' /></td>
        </tr>
        <tr>
          <td>&nbsp;</td>
            <td align='right'><input type='submit' class='bsubmit' value=".GetLangVar('nameconcluir')." /></td>
</form>
<form action=municipio-popup.php method='post'>
  <input type='hidden' name='municipioid_val' value='$municipioid_val' />
  <input type='hidden' name='provinceid' value='$provinceid' />
            <td align='left'><input type='submit' class='breset' value=".GetLangVar('namevoltar')."></td>
</form>
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