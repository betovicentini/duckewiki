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
$title = 'Exportar especímenes';
$body = '';
FazHeader($title,$body,$which_css,$which_java,$menu);

echo "
<form method='post' name='finalform' action='export-taxadata-form.php'>
<table class='myformtable' align='left' cellpadding=\"5\">
<thead>
<tr >
<td >Exportar dados associados à taxa";
		echo "&nbsp;<img height=14 src=\"icons/icon_question.gif\" ";
		$help = "Exporta todos os dados na Base associados à nomes taxonômicos diretamente";
		echo " onclick=\"javascript:alert('$help');\" /></td>
</tr>
</thead>";
//formulario variaveis
if ($bgi % 2 == 0) {$bgcolor = $linecolor2 ;} else { $bgcolor = $linecolor1 ;} $bgi++;
echo "
<tr bgcolor = '".$bgcolor."'>
  <td >
    <table>
      <tr>
        <td class='tdsmallbold'>Formulário de variáveis";
		echo "&nbsp;<img height=14 src=\"icons/icon_question.gif\" ";
		$help = "Indique o formulário com as variáveis à exportar";
		echo " onclick=\"javascript:alert('$help');\" /></td>
        <td class='tdformnotes'>
          <select name='formid' >
            <option value=''>".GetLangVar('nameselect')."</option>";
			//formularios usuario
			$qq = "SELECT * FROM Formularios WHERE AddedBy=".$_SESSION['userid']." OR Shared=1 ORDER BY FormName,Formularios.AddedDate ASC";
			$rr = mysql_query($qq,$conn);
			while ($row= mysql_fetch_assoc($rr)) {
				echo "
            <option value='".$row['FormID']."'>".$row['FormName']."</option>";
			}
		echo "
          </select>
        </td>
      </tr>
    </table>
  </td>
</tr>";
if ($bgi % 2 == 0) {$bgcolor = $linecolor2 ;} else { $bgcolor = $linecolor1 ;} $bgi++;
echo "
<tr bgcolor = '".$bgcolor."'>
  <td >
    <table align='center'>
      <tr>
        <td>
          <input type='submit' value='".GetLangVar('nameenviar')."' class='bsubmit' />
        </td>
      </tr>
    </table>
  </td>
</tr>
</tbody>
</table>
</form>
";
$which_java = array("<script type='text/javascript' src='javascript/myjavascripts.js'></script>");
FazFooter($which_java,$calendar=FALSE,$footer=$menu);
?>
