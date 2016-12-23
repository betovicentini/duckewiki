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
$title = 'Apaga formularios';
$body = '';
FazHeader($title,$body,$which_css,$which_java,$menu);
if (!isset($final)) {
echo "
<form action='formularios-delete.php' method='post' name='formform'>
  <input type='hidden' name='ispopup' value='$ispopup' />
<table align='left' cellpadding='10px' class='myformtable' >
<thead>
  <tr ><td>".GetLangVar('nameformulario')."&nbsp;&nbsp;<img height=13 src=\"icons/icon_question.gif\" ";
	$help = "Selecione 1 ou mais formulários para apagar. Não há risco de perda de dados!";
	echo " onclick=\"javascript:alert('$help');\" /></td></tr>
</thead>
<tbody>
<tr>
  <td>
    <table>
      <tr>
        <td class='tdsmallbold'>".GetLangVar('nameformulario')."</td>
        <td class='tdformnotes'>
          <select name='formids[]' multiple size='10'>
            <option value=''>".GetLangVar('nameselect')."</option>";
            if ($acclevel=='admin') {
				$qq = "SELECT * FROM Formularios ORDER BY Formularios.FormName ASC";
			} else {
				$qq = "SELECT * FROM Formularios WHERE AddedBy=".$_SESSION['userid']." ORDER BY Formularios.FormName ASC";
			}
			$rr = mysql_query($qq,$conn);
			while ($row= mysql_fetch_assoc($rr)) {
				echo "
            <option style='width: 300px;' value='".$row['FormID']."'>".$row['FormName']."</option>";
			}
		echo "
          </select>
        </td>
      </tr>
    </table>
  </td>
</tr>
<tr>  
  <td  align='center'>
  <input type='hidden' value='' name='final' /> 
  <input type='submit' value='Apagar' class='bsubmit' onclick=\"javascript:document.formform.final.value=1\" />&nbsp;&nbsp;
  <input type='button' value='Fechar' class='bblue' onclick=\"javascript:window.close();\" />
</td>
</tr>
</tbody>
</table>
</form>
";
} elseif ($final==1) {
		//echopre($ppost);
		//echopre($formids);
		$ndeleted = 0;
		foreach ($formids as $formid) {
				//APAGA DA TABELA FormulariosTraitsList
				$qn = "DELETE FROM FormulariosTraitsList WHERE FormID='".$formid."'";
				$nr2 = @mysql_query($qn,$conn);
				//APAGA DA TABELA Formularios
				$qn = "DELETE FROM Formularios WHERE  FormID='".$formid."'";
				$nr3 = @mysql_query($qn,$conn);
				if ($nr2 && $nr3) {
					$ndeleted++;
				}
		}
		$nforms = count($formids);
echo "
<table align='center' cellpadding='7' class='myformtable'>
<thead>
  <tr ><td colspan='2'>Formulários apagados</td></tr>
</thead>
<tbody>
<tr> <td colspan='2'>Foram apagados ".$ndeleted." formulários de ".$nforms." selecionados para remoção!</td></tr>
<tr>  
  <td  align='center'>
  <input type='button' value='Fechar' class='bsubmit' onclick=\"javascript:window.close();\" /> </td>
  <td  align='center'>
<form action='formularios-delete.php' method='post' name='formform'>
  <input type='hidden' name='ispopup' value='$ispopup' />
  <input type='submit' value='Apagar outros' class='bblue' /> 
    </form>
   </td> 
</tr>
</tbody>
</table>
";
}
$which_java = array("<script type='text/javascript' src='javascript/myjavascripts.js'></script>");
FazFooter($which_java,$calendar=FALSE,$footer=$menu);

?>