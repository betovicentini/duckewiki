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

//echopre($gget);
//CABECALHO

	$menu = FALSE;
$which_css = array(
"<link href='css/geral.css' rel='stylesheet' type='text/css' />");
$which_java = array();
$title = 'Exportação de dados de processos';
$body = '';
FazHeader($title,$body,$which_css,$which_java,$menu);
echo "<script type='text/javascript' src='javascript/wz_tooltip.js'></script>";
///NOVO OU EDICAO DE UM PROCESSO
if (!isset($processoids)) {
if ($final>0) {
echo "<br /><span width='80%' style='font-size: 1em; color: red; font-weight: bold; border: thin gray; background-color: yellow; padding: 10px;' >Você precisa indicar pelo menos 1 processo para esta operação</span><br /><br />";
}
echo "
<br />
<form action='processo-amostras-exportavarios.php' method='post' name='coletaform' >
<table class='myformtable' align='left' cellpadding='7' >
<thead>
<tr ><td colspan='3' >Exporta dados de vários processos</td></tr>
</thead>
<tbody>
<tr>
  <td class='tdsmallbold'>Selecione 1 ou mais processos</td>
  <td colspan='2' >
    <select name='processoids[]' multiple size=15>";
      $qq = "SELECT * FROM ProcessosEspecs ";
      $qq .= " WHERE Status=0  ORDER BY Name ASC ";
      $rrr = @mysql_query($qq,$conn);
      while ($row = @mysql_fetch_assoc($rrr)) {
			echo "
      <option value=".$row['ProcessoID'].">".$row['Name']." </option>";
		}
	echo "
    </select>
    </td>
</tr>
<tr>
<td align='center' >
<input type='hidden' name='final' value=''>
<input  type='submit'  style=\"color:#4E889C; font-size: 1.2em; font-weight:bold; padding: 4px; cursor:pointer;\"   value='Exportar dados INPA'  onclick=\"javascript:document.coletaform.final.value='1'\" ></td>";
if ($acclevel=='admin') {
echo "
<td><input  type='submit'  style=\"color:#4E889C; font-size: 1.2em; font-weight:bold; padding: 4px; cursor:pointer;\"   value='Prepara Imagens para INPA'  onclick=\"javascript:document.coletaform.final.value='2'\" ></td>";
}
echo "
<td><input  type='submit'  style=\"color:#4E889C; font-size: 1.2em; font-weight:bold; padding: 4px; cursor:pointer;\"   value='Listas doações herbários'  onclick=\"javascript:document.coletaform.final.value='3'\" ></td>
 </tr>
</tbody>
</table>
</form>";
} 
else {
if ($final==1) {
	$basicvariables = implode(";",array( 'datacol', 'taxacompleto', 'localidade', 'gps', 'habitat', 'addcoll', 'nirdata', 'registroINPA', 'Vernacular', 'projeto', 'herbarios')); 
	$processoid = implode(";",$processoids);
	  echo "
<form name='lastform' action='export-especimenes-exec.php' method='post'>
  <input type='hidden' name='ispopup' value='1' />
  <input type='hidden' name='formnotas' value='".$formnotes."' />
  <input type='hidden' name='formhabitatdesc' value='".$formidhabitat."' />
  <input type='hidden' name='specbasicvars'  value='".$basicvariables."' />
  <input type='hidden' name='processoid'  value='".$processoid."' />
  <input type='hidden' name='forbrahms' value='1'>
  <input type='hidden' name='monidata'  value='0' /> 
<table >
<tr bgcolor = '".$bgcolor."'>
  <td >
    <table>
      <tr>
        <td class='tdsmallbold'>Quais</td><td></td>
        </tr><tr><td></td>
        <td><input type='radio' name='quais'  value='1' />Para todas as amostras</td>
         </tr><tr><td></td>
        <td><input type='radio' name='quais'  value='2' />Para as amostras COM número $herbariumsigla</td>
         </tr><tr><td></td>
        <td><input type='radio' name='quais'  checked value='3' />Para as amostras SEM número $herbariumsigla</td>        
      </tr>
    </table>
  </td>
</tr>
<tr>
<td><input type='submit' value='Exportar' style=\"color:#4E889C; font-size: 1.2em; font-weight:bold; padding: 4px; cursor:pointer;\" />
</tr>
</table>

</form>";
//  <script language=\"JavaScript\">setTimeout('document.lastform.submit()',1);</script>
}
if ($final==2) {
	$processoid = implode(";",$processoids);
	echo "
<form name='lastform' action='processo-amostras-prepimagensinpa.php' method='post'>
  <input type='hidden' name='processoid'  value='".$processoid."' />
  <script language=\"JavaScript\">setTimeout('document.lastform.submit()',1);</script>
</form>";
	}
if ($final==3) {
	$processoid = implode(";",$processoids);
	echo "
<form name='lastform' action='processo-exportvarios-herbaria.php' method='post'>
  <input type='hidden' name='processoid'  value='".$processoid."' />
  <script language=\"JavaScript\">setTimeout('document.lastform.submit()',1);</script>
</form>";
	}	
} 
$which_java = array("<script type='text/javascript' src='javascript/myjavascripts.js'></script>");
FazFooter($which_java,$calendar=TRUE,$footer=$menu);
?>