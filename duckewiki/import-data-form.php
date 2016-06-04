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
$sql = "SELECT * FROM `Import_Fields`  WHERE `BRAHMS`='BIBKEY'";
$res = mysql_query($sql,$conn);
$nrs = mysql_numrows($res);
if ($nrs==0) {
	$sql = "INSERT INTO `Import_Fields` (`BRAHMS`, `CLASS`, `ORDEM`, `DEFINICAO`, `FieldsToPut`, `NamesToMatch`, `TabelaParaPor`, `LocalityFields`) VALUES ('BIBKEY', 'Genérico', '3.5', 'Bibkey da referência bibliográfica - para variáveis de usuário', NULL, NULL, 'Especimenes;Plantas', 0);";
	@mysql_query($sql,$conn);
}
$sql = "ALTER TABLE `Monitoramento`  ADD `BibID` INT(10) NULL AFTER `CensoID`";
@mysql_query($sql,$conn);

$qtemp = "ALTER TABLE `Traits_variation`  ADD `BibtexIDS` CHAR(100) CHARACTER SET utf8 COLLATE utf8_general_ci NULL DEFAULT NULL COMMENT 'Bibliografia' AFTER `GrupoSppID`";
@mysql_query($qtemp,$conn);


$menu = FALSE;
$which_css = array("<link href='css/geral.css' rel='stylesheet' type='text/css' />");
$which_java = array();
$title = 'Importar dados';
$body = '';
FazHeader($title,$body,$which_css,$which_java,$menu);
echo "
<br />
<table align='center' class='myformtable' cellpadding=\"7\">
<thead>
<tr>
<td colspan='2' class='tabhead' >".GetLangVar('nameimportar')." ".GetLangVar('namedados')."</td>
</tr>
</thead>
<tbody>";
if ($bgi % 2 == 0){$bgcolor = $linecolor2 ;} else {$bgcolor = $linecolor1 ;} $bgi++;
echo "
<tr bgcolor = '".$bgcolor."'>
<form enctype='multipart/form-data' action='import-data-step1.php' method='post'>
  <td style='color: #990000; font-weight:bold' >".GetLangVar('namefile')."*&nbsp;&nbsp;<img height=14 src=\"icons/icon_question.gif\" ";
$help = "O arquivo para importar deve estar em formato TXT ou CSV, separado por TABULAÇÃO, quebra de linha em formato UNIX e código de fonte UTF-8. O LibreOffice/OpenOffice  permite salvar arquivos em formato CSV com essa opções.";
echo " onclick=\"javascript:alert('$help');\" /></td>
  <td><input type='hidden' name='MAX_FILE_SIZE' value='10000000' />
  <input type='hidden' name='ispopup' value='".$ispopup."' />
  <input name='uploadfile' type='file' width='20' /></td>
</tr>
";
if ($bgi % 2 == 0){$bgcolor = $linecolor2 ;} else {$bgcolor = $linecolor1 ;} $bgi++;
echo "
<tr bgcolor = '".$bgcolor."'>
  <td style='color: #990000; font-weight:bold' align='left'>".GetLangVar('nameinclui')."*&nbsp;&nbsp;<img height=14 src=\"icons/icon_question.gif\" ";
$help = "Os dados precisam ser relativos à plantas marcadas, cujos identificadores serão um número de Placa e uma localidade (e.g. uma parcela, uma trilha); ou à amostras coletadas, cujos identificadores são o nome e o número do coletor. Se for atualizar dados já existentes no wiki, precisa então ter o PlantaID e/ou o EspecimenID do wiki no arquivo.";
echo " onclick=\"javascript:alert('$help');\" /></td>
  <td align='left'>
    <table>
      <tr>
        <td align='right'><input type='radio' name='coletas' value='1' /></td>
        <td align='left'>Planta&nbsp;marcada</td>
        <td align='left'>
          <table>
            <tr>
              <td align='right'><input type='radio' name='coletas' value='2' /></td>
              <td align='left'>Exsicata&nbsp;(Amostra&nbsp;Coletada)</td>
            </tr>
            <tr>
              <td align='right'><input type='radio' name='coletas' value='3' /></td>
              <td align='left'>Exsicata&nbsp;de&nbsp;planta&nbsp;marcada</td>
            </tr>
          </table>
        </td>
      </tr>
    </table>
  </td>
</tr>
";
if ($bgi % 2 == 0){$bgcolor = $linecolor2 ;} else {$bgcolor = $linecolor1 ;} $bgi++;
echo "
<tr bgcolor = '".$bgcolor."'>
  <td colspan='2' align='center'><input style='cursor: pointer' type='submit' value='".GetLangVar('nameenviar')."' class='bsubmit' /></td>
</tr>
</form>
</tbody>
</table>";
$which_java = array("<script type='text/javascript' src='javascript/myjavascripts.js'></script>");
FazFooter($which_java,$calendar=FALSE,$footer=$menu);
?>
