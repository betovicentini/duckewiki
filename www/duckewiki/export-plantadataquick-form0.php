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

$menu = FALSE;
$which_css = array(
"<link href='css/geral.css' rel='stylesheet' type='text/css' />"
);
$which_java = array();
$title = 'Exportar dados de censos';
$body = '';
FazHeader($title,$body,$which_css,$which_java,$menu);

unset($_SESSION['metadados']);
unset($_SESSION['destvararray']);
unset($_SESSION['qq']);
unset($_SESSION['exportnresult']);



$qz = "SELECT DISTINCT CensoID FROM Monitoramento WHERE CensoID>0";
$rz = @mysql_query($qz,$conn);
$ncensos = @mysql_numrows($rz);
if ($ncensos>0) {
echo "
<br />
<table class='myformtable' align='left' cellpadding=\"5\">
<thead>
<tr >
<td >Exportar dados de parcelas</td></tr>
</thead>
<tbody>
";
if ($bgi % 2 == 0){$bgcolor = $linecolor2 ;}  else {$bgcolor = $linecolor1 ;} $bgi++;
	echo "
<form method='post' name='finalform' action='export-plantadataquick-form.php'>
<input type='hidden' name='filtro' value='".$filtro."' /> 
<tr bgcolor = '".$bgcolor."'>
  <td>
  <table>
  <tr>
    <td class='tdsmallbold'>Selecione 1 ou mais censos:</td>
    <td>
      <select name='censos[]' multiple size='20'>";
			while ($rr = @mysql_fetch_assoc($rz)) {
				$qk = "SELECT * FROM Censos WHERE CensoID='".$rr['CensoID']."'";
				$rk = @mysql_query($qk,$conn);
				$rwk = @mysql_fetch_assoc($rk);
				$qz = "SELECT COUNT(DISTINCT TraitID) as trs FROM Monitoramento WHERE CensoID='".$rr['CensoID']."'";
				$rzk = @mysql_query($qz,$conn);
				$rwzk = @mysql_fetch_assoc($rzk);
				$trs = $rwzk['trs'];
				$qz = "SELECT COUNT(DISTINCT PlantaID) as pls FROM Monitoramento WHERE CensoID='".$rr['CensoID']."'";
				$rzk = @mysql_query($qz,$conn);
				$rwzk = @mysql_fetch_assoc($rzk);
				$pls = $rwzk['pls'];
				echo "
          <option value='".$rwk['CensoID']."'>".$rwk['CensoNome']." [".$rwk['DataInicio']." à ".$rwk['DataFim']."] - inclui ".$pls." árvores e ".$trs." variáveis</option>";
			}
	echo "
      </select>
    </td>
    <td>
      <table>
        <tr>
          <td><input type='button' class='bsubmit' value='Editar/atualizar um censo'  onclick =\"javascript:small_window('censo-edit.php?ispopup=1&filtro=".$filtro."',900,400,'Editar censo');\" /></td>
        </tr>
        <tr>
          <td><input type='button' class='bblue' value='Criar um censo'  onclick =\"javascript:small_window('censos.php?ispopup=1&filtro=".$filtro."',900,400,'Editar censo');\" /></td>
        </tr>
      </table>
    </td>
  </tr>
  </table>
  </td>
</tr>";
if ($bgi % 2 == 0){$bgcolor = $linecolor2 ;} else {$bgcolor = $linecolor1 ;} $bgi++;
echo "
<tr bgcolor = '".$bgcolor."'>
  <td align='center'><input type='submit' value='".GetLangVar('nameenviar')."' class='bsubmit' /></td>
</tr>";
echo "
</form>
</tbody>
</table>";
	} 
	else {
echo "
<br />
<table class='myformtable' align='left' cellpadding=\"5\">
<thead>
<tr >
<td >Exportar dados de parcelas</td></tr>
</thead>
<tbody>
<tr bgcolor = '".$bgcolor."'>
  <td>
    <table>
      <tr>  <td class='tdsmallbold'>Não há censos cadastrados</td></tr>
      <tr><td><input type='button' class='bsubmit' value='Criar um censo'  onclick =\"javascript:small_window('censos.php',900,400,'Criar censo');\" /></td></tr>
    </table>
  </td>
</tr>
</tbody>
</table>";
	}


$which_java = array(
"<script type='text/javascript' src='javascript/myjavascripts.js'></script>");
FazFooter($which_java,$calendar=FALSE,$footer=$menu);
?>