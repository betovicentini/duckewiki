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

$mensagem = "A lista abaixo contém amostras de plantas do Projeto Dinâmica Biológica de Fragmentos Florestais (PDBFF). São coletas férteis e, em sua grande maioria, testemunho de plantas marcadas nas parcelas do PDBFF. Uma duplicata, quando existente, foi mantida na coleção de referência do PDBFF, mas as unicatas apenas INPA (portanto o número de duplicatas abaixo tem 1 amostra a mais do que no pacote).<br /><br />O pacote inclui também duplicatas, cuja distribuição recomendamos seja feita da seguinte forma para cada coleção, segundo o número disponível:<br /><br />1)<b>INPA</b><br />2)<b>ESPECIALISTA</b> (a critério do curador)<br />3)<b>COAH</b> - pela parceria das parcelas do PDBFF com a rede ForestGeo (CTFS) é importante que amostras do PDBFF estejam bem representadas nesta coleção para padronização taxonomica. Neste herbário estão sendo integradas amostras de parcelas da rede na região Amazônica.<br />4) demais duplicatas, se houver, a critério do curador";

$menu = FALSE;
$which_css = array(
"<link href='css/geral.css' rel='stylesheet' type='text/css' />");
$which_java = array();
$title = 'Exportação de dados de processos';
$body = '';
FazHeader($title,$body,$which_css,$which_java,$menu);
echo "<script type='text/javascript' src='javascript/wz_tooltip.js'></script>";
///NOVO OU EDICAO DE UM PROCESSO
if (!isset($processoids) && !isset($filtroid)) {
if ($final>0) {
echo "<br /><span width='80%' style='font-size: 1em; color: red; font-weight: bold; border: thin gray; background-color: yellow; padding: 10px;' >Você precisa indicar pelo menos 1 processo ou 1 filtro!</span><br /><br />";
}
echo "
<br />
<form action='processo-amostras-exportavarios.php' method='post' name='coletaform' >
<table class='myformtable' align='left' cellpadding='7' >
<thead>
<tr ><td colspan='2' >Exporta dados de vários processos</td></tr>
</thead>
<tbody>
<tr>
  <td class='tdsmallbold'>OPÇÃO 1: Selecione 1 ou mais processos</td>
  <td >
    <select name='processoids[]' multiple size=10>";
      $qq = "SELECT * FROM ProcessosEspecs ";
      $qq .= " WHERE Status=0  ORDER BY Name ASC ";
      $rrr = @mysql_query($qq,$conn);
      while ($row = @mysql_fetch_assoc($rrr)) {
			echo "
      <option value=".$row['ProcessoID'].">".$row['Name']."       ".$row['Inicio']."</option>";
		}
	echo "
    </select>
    </td>
</tr>
<tr>
  <td class='tdsmallbold'>OPÇÃO 2: Selecione 1 ou mais filtros</td>
  <td >
    <select name='filtroid' >
      <option value=''>Selecione</option>";
		$qq = "SELECT * FROM Filtros WHERE AddedBy=".$_SESSION['userid']." OR Shared=1 ORDER BY AddedDate, FiltroName";
		$res = @mysql_query($qq,$conn);
		while ($rr = @mysql_fetch_assoc($res)) {
			echo "
            <option value='".$rr['FiltroID']."'>".$rr['FiltroName']." [".$rr['AddedDate']."]</option>";
		}
	echo "
    </select>
  </td>
</tr>
<tr>
<td align='center' colspan='2'>
<table>
<tr>
<td>
<input type='hidden' name='final' value=''>
<input  type='submit'  style=\"color:#4E889C; font-size: 1.2em; font-weight:bold; padding: 4px; cursor:pointer;\"   value='Exportar dados INPA'  onclick=\"javascript:document.coletaform.final.value='1'\" ></td>";
if ($acclevel=='admin') {
echo "
<td><input  type='submit'  style=\"color:#4E889C; font-size: 1.2em; font-weight:bold; padding: 4px; cursor:pointer;\"   value='Prepara Imagens para INPA'  onclick=\"javascript:document.coletaform.final.value='2'\" ></td>";
} else {
echo "<td>&nbsp;</td>";
}
echo "
</tr>
<tr>";
if ($acclevel=='admin') {
echo "
<td><input  type='submit'  style=\"color:#4E889C; font-size: 1.2em; font-weight:bold; padding: 4px; cursor:pointer;\"   value='Listas doações herbários'  onclick=\"javascript:document.coletaform.final.value='3'\" ></td>";
} else {
echo "<td>&nbsp;</td>";
}
echo "
<td><input  type='submit'  style=\"color:#4E889C; font-size: 1.2em; font-weight:bold; padding: 4px; cursor:pointer;\"   value='Imprime Lista Para Herbário'  onclick=\"javascript:document.coletaform.final.value='4'\" ></td>
 </tr>
 </td>
 </table>
</tbody>
</table>
</form>";
} 
else {
$nprc = count($processoids);
if ($nprc>0 && $filtroid>0) {
echo "<br /><span width='80%' style='font-size: 1em; color: red; font-weight: bold; border: thin gray; background-color: yellow; padding: 15px; border: thin black;' >Não pode indicar filtro e processo ao mesmo tempo!</span><br /><br />
<form action='processo-amostras-exportavarios.php'> <input type='submit' value='Voltar'></form>";
} else {
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
  <input type='hidden' name='filtroid'  value='".$filtroid."' />
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
  <input type='hidden' name='filtroid'  value='".$filtroid."' />
  <script language=\"JavaScript\">setTimeout('document.lastform.submit()',1);</script>
</form>";
	}
if ($final==3) {
	$processoid = implode(";",$processoids);
	echo "
<form name='lastform' action='processo-exportvarios-herbaria.php' method='post'>
  <input type='hidden' name='processoid'  value='".$processoid."' />
  <input type='hidden' name='filtroid'  value='".$filtroid."' />
  <script language=\"JavaScript\">setTimeout('document.lastform.submit()',1);</script>
</form>";
	}
if ($final==4) {
	$processoid = implode(";",$processoids);
	echo "
<form name='lastform' action='processo-exportvarios-lista.php' method='post'>
  <input type='hidden' name='processoid'  value='".$processoid."' />
  <input type='hidden' name='filtroid'  value='".$filtroid."' />
<table >
<tr bgcolor = '".$bgcolor."'>
  <td >
    <table>
      <tr><td class='tdsmallbold'>Selecione 1 ou mais opções:</td></tr>
      <tr><td><input type='checkbox' name='printlista'  value='1' />Imprime Lista</td></tr>
      <tr><td><input type='checkbox' name='endprocesso'  value='1' />Conclui Entrega no Herbário ".$herbariumsigla."</td></tr>
    </table>
  </td>
</tr>
<tr bgcolor = '".$bgcolor."'>
  <td >
    <table>
      <tr>
        <td class='tdsmallbold'>Mensagem para aparecer na primeira página:</td>
      </tr>
      <tr>
        <td><textarea name='message' rows=5 cols=80 style='background-color: #F0F0F0;' >".$mensagem."</textarea></td>
      </tr>
    </table>
  </td>
</tr>

<tr>
<td><input type='submit' value='Gerar' style=\"color:#4E889C; font-size: 1.2em; font-weight:bold; padding: 4px; cursor:pointer;\" />
</tr>
</table>
</form>";
	}
}
} 
$which_java = array("<script type='text/javascript' src='javascript/myjavascripts.js'></script>");
FazFooter($which_java,$calendar=TRUE,$footer=$menu);
?>