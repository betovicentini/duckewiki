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


$menu = FALSE;
$which_css = array(
"<link href='css/geral.css' rel='stylesheet' type='text/css' />");
$which_java = array();
$title = 'Importar dados Moleculares';
$body = '';
FazHeader($title,$body,$which_css,$which_java,$menu);

if (isset($erro)) {
	echo "
<br /><span style='color: red; font-size:1.5em;'>$erro</span>";
}
echo "
<br />
<form enctype='multipart/form-data' action='import-molecular-passo1.php' method='post'>
<input type='hidden' name='MAX_FILE_SIZE' value='10000000' />
<table align='center' class='myformtable' cellpadding=\"7\" width='90%'>
<thead>
<tr>
<td colspan='2' class='tabhead' >".GetLangVar('nameimportar')." sequencias</td>
</tr>
</thead>
<tbody>";
if ($bgi % 2 == 0){$bgcolor = $linecolor2 ;} else {$bgcolor = $linecolor1 ;} $bgi++;
echo "
<tr bgcolor = '".$bgcolor."'>
  <td style='color: #990000; font-weight:bold' >".GetLangVar('namefile')."*</td>
  <td>
  <input name='uploadfile' type='file' size='30' />";
  
  //&nbsp;FASTA &nbsp;&nbsp;<img height=14 src=\"icons/icon_question.gif\" ";
//$help = "O arquivo para importar deve estar em formato FASTA, sendo o nome da sequencia composto de uma das opções:\\\n\\\n 1. WikiEspecimenID_NCBIid = identificador deste wiki separado por espaço ou _ do identificador do genebank; \\\n\\\n 2. WikiEspecimenID";
//echo " onclick=\"javascript:alert('".$help."');\" /></td>
echo "</td>
</tr>
";
if ($bgi % 2 == 0){$bgcolor = $linecolor2 ;} else {$bgcolor = $linecolor1 ;} $bgi++;
echo "
<tr bgcolor = '".$bgcolor."'>
  <td style='color: #990000; font-weight:bold' >Nome do Marcador*</td>
  <td>
    <table>
      <tr><td><select name='marcadorsel' >
              <option  value='' >Selecione</option>";  

  $qn = "SELECT DISTINCT Marcador FROM MolecularData ORDER BY Marcador";
  $rn = mysql_query($qn,$conn);
  while($rnw = mysql_fetch_assoc($rn)) {
echo "
        <option  value='".$rnw['Marcador']."' >".$rnw['Marcador']."</option>";  
  }
echo "
    </select></td></tr>
    <tr><td><input name='marcadornovo' size=50>&nbsp;<span style='font-size: 0.7em'>digite aqui se não cadastrado</span></td></tr>
    </table>
  </td>
</tr>
";

if ($bgi % 2 == 0){$bgcolor = $linecolor2 ;} else {$bgcolor = $linecolor1 ;} $bgi++;
echo "
<tr bgcolor = '".$bgcolor."'>
  <td colspan='2' align='center'><input style='cursor: pointer' type='submit' value='".GetLangVar('nameenviar')."' class='bsubmit' /></td></tr>";

if ($bgi % 2 == 0){$bgcolor = $linecolor2 ;} else {$bgcolor = $linecolor1 ;} $bgi++;
echo "
<tr bgcolor = '".$bgcolor."'>
  <td colspan='2'  style='color: #990000;' class='tdformnotes'> O arquivo para importar deve estar em formato FASTA, sendo o nome da sequencia composto de uma das opções:<br />1. WikiEspecimenID - formato mais simples
<br />2. WikiEspecimenID+NCBIid = identificador do wiki + codigo genebank (separador '_')
<br />2. WikiEspecimenID+NCBIid+LABEL = identificador do wiki + codigo genebank + uma etiqueta de sua escolha para a sequencia (separador '_')
</td></tr>
</tbody>
</table>
</form>
";
$which_java = array(
"<script type='text/javascript' src='javascript/myjavascripts.js'></script>");
FazFooter($which_java,$calendar=FALSE,$footer=$menu);
?>
