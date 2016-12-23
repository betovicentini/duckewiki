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
if ($ispopup==1) {
	$menu = FALSE;
} else {
	$menu = TRUE;
}
$which_css = array(
"<link href='css/geral.css' rel='stylesheet' type='text/css' />"

);
$which_java = array(
);
$title = 'Entrar dados via tabela';
$body = '';
FazHeader($title,$body,$which_css,$which_java,$menu);

echo "
<form method='post' name='finalform' action='fitobatchenter_traits_gridform.php'>
<input type='hidden' name='ispopup' value='".$ispopup."' /> 
<input type='hidden' name='final' value='1' /> 
<br />
<table class='myformtable' align='left' cellpadding=\"5\">
<thead>
<tr >
<td >Entrar dados de traits via tabela</td></tr>
</thead>
<tbody>
";
if ($bgi % 2 == 0){$bgcolor = $linecolor2 ;} else {$bgcolor = $linecolor1 ;} $bgi++;
echo "
<tr bgcolor = '".$bgcolor."'>
  <td >
    <table>
      <tr>
        <td class='tdsmallbold'>".GetLangVar('namefiltro')."</td>
        <td>
          <select name='filtroid'>";
			echo "
            <option selected value=''>".GetLangVar('nameselect')."</option>";
			$qq = "SELECT * FROM Filtros WHERE AddedBy=".$_SESSION['userid']." OR Shared=1 ORDER BY FiltroName";
			$res = @mysql_query($qq,$conn);
			while ($rr = @mysql_fetch_assoc($res)) {
				echo "
            <option value='".$rr['FiltroID']."'>".$rr['FiltroName']."</option>";
			}
			mysql_free_result($res);
	echo "
          </select>
        </td>
      </tr>
      <tr>
       <td class='tdsmallbold'>Para:</td>
       <td class='tdformnotes'>
          <table>
            <tr>";
              //<td align='right' class='tdformnotes' ><input type='radio' name='sampletype'  value='especimenes' /></td>
              //<td align='left' class='tdformnotes'>especimenes</td> 
	echo "<td align='right' class='tdformnotes' ><input type='radio' name='sampletype' checked value='plantas' /></td>
              <td align='left' class='tdformnotes'>plantas&nbsp;marcadas</td>
            </tr>
          </table>
        </td>
      </tr>
      <tr>
       <td class='tdsmallbold'>Gerar tabela&nbsp;<img height=15 src=\"icons/icon_question.gif\" ";
		$help ="Marcar essa opção se o filtro ou o formulário foi atualizado e você deseja gerar uma nova versão da tabela de edição. Isso demora mais, pois gera a tabela de edição mesmo se ela já existe.";
		echo " onclick=\"javascript:alert('$help');\" /></td>
       <td class='tdformnotes'>
          <table>
            <tr>
              
              <td align='right' class='tdformnotes' ><input type='checkbox' name='updatetable'  value='1' /></td>
              <td align='left' class='tdformnotes'>Se existe, atualiza tabela de edição!</td>
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
  <td >
    <table align='left'>
      <tr>
        <td class='tdsmallbold'>".GetLangVar('nameformulario')."&nbsp;<img height=15 src=\"icons/icon_question.gif\" ";
		$help ="que contém as variáveis que deseja editar";
		echo " onclick=\"javascript:alert('$help');\" /></td>
        <td class='tdformnotes'>
          <select name='formid' >";
			echo "
            <option value=0>".GetLangVar('nameselect')."</option>";
	//formularios usuario
	$qq = "SELECT * FROM Formularios WHERE (AddedBy=".$_SESSION['userid']." OR Shared=1) ORDER BY FormName,Formularios.AddedDate ASC";
	$rr = mysql_query($qq,$conn);
	while ($row= mysql_fetch_assoc($rr)) {
		$txt = '';
		 if ($row['FormID']==166) {
		 	$txt = 'selected';
		 }
		echo "
            <option $txt value='".$row['FormID']."'>".$row['FormName']."</option>";
	}
	echo "
          </select>
        </td>
      </tr>
    </table>
  </td>
</tr>";
if ($bgi % 2 == 0){$bgcolor = $linecolor2 ;} else {$bgcolor = $linecolor1 ;} $bgi++;
echo "
<tr bgcolor = '".$bgcolor."'>
  <td align='center' colspan='2'><input style='cursor: pointer'  type='submit' value='".GetLangVar('nameenviar')."' class='bsubmit' /></td>
</tr>";
echo "
</form>
</tbody>
</table>";

$which_java = array("<script type='text/javascript' src='javascript/myjavascripts.js'></script>");
FazFooter($which_java,$calendar=FALSE,$footer=$menu);
?>