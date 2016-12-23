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
$which_css = array("<link href='css/geral.css' rel='stylesheet' type='text/css' />");
$which_java = array();
$title = 'Exportando arquivo do material processado';
$body = '';
FazHeader($title,$body,$which_css,$which_java,$menu);

$basicvariables = implode(";",array( 'datacol', 'taxacompleto', 'localidade', 'gps', 'habitat', 'addcoll', 'nirdata', 'registroINPA', 'Vernacular', 'projeto', 'herbarios')); 
$usarmodelo = NULL;
echo "
<form name='lastform' action='export-especimenes-exec.php' method='post'>
  <input type='hidden' name='ispopup' value='1' />
  <input type='hidden' name='specbasicvars'  value='".$basicvariables."' />
  <input type='hidden' name='processoid'  value='".$processoid."' />
  <input type='hidden' name='forbrahms' value='1'>
  <input type='hidden' name='monidata'  value='0' /> 
<table class='myformtable' align='left' cellpadding=\"5\">
<thead>
<tr >
<td>$title</td>
</tr>
</thead>
";
if ($bgi % 2 == 0) {$bgcolor = $linecolor2 ;} else { $bgcolor = $linecolor1 ;} $bgi++;
echo "
<tr bgcolor = '".$bgcolor."'>
  <td>
    <table>
      <tr>
        <td class='tdsmallbold'>".GetLangVar('nameformulario')." ".mb_strtolower(GetLangVar('nameobs')."s");
		echo "&nbsp;<img height=14 src=\"icons/icon_question.gif\" ";
		$help = "Indique um formulário para gerar o campo de notas da exsicata. SE VAZIO TODAS AS INFORMAÇÕES RELACIONADAS À AMOSTRAS SERÃO INCLUIDAS NA DESCRIÇÃO";
		echo " onclick=\"javascript:alert('$help');\" /></td>
        <td class='tdformnotes'>
          <select name='formnotas' >";
		if (!empty($formnotes)) {
			$qq = "SELECT * FROM Formularios WHERE FormID=".$formnotes;
			$rr = mysql_query($qq,$conn);
			$row= mysql_fetch_assoc($rr);
			echo "
            <option selected value='".$row['FormID']."'>".$row['FormName']."</option>";
		} else {
			echo "
            <option value=''>".GetLangVar('nameselect')."</option>";
		}
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
      <tr><td>Usar modelo de descrição&nbsp;<img height=14 src=\"icons/icon_question.gif\" ";
		$help = "Neste caso a descrição gerada será feita utilizando o modelo descritivo do formulário. O formulário precisa ter um modelo para usar esta opção";
		echo " onclick=\"javascript:alert('$help');\" /></td><td><input type='checkbox' value='1' name='usarmodelo' /></td>
		</tr>
     </table>
     </td> 
<tr>";
if ($bgi % 2 == 0) {$bgcolor = $linecolor2 ;} else { $bgcolor = $linecolor1 ;} $bgi++;
echo "
<tr bgcolor = '".$bgcolor."'>
  <td >
    <table>
      <tr>
        <td class='tdsmallbold'>Formulário de hábitat&nbsp;<img height=14 src=\"icons/icon_question.gif\" ";
		$help = "Indique um formulário para gerar o campo de notas da habitat. Se vazio todas as informações de habitat relacionadas à amostras serão incluidas na descrição. IGNORADO NA EXPORTAÇÃO BRAHMS";
		echo " onclick=\"javascript:alert('$help');\" /></td>
        <td class='tdformnotes'>
          <select name='formhabitatdesc' >";
		if (($formidhabitat+0)>0) {
			$qq = "SELECT * FROM Formularios WHERE FormID='".$formidhabitat."'";
			$rr = mysql_query($qq,$conn);
			$row= mysql_fetch_assoc($rr);
			echo "
            <option selected value='".$row['FormID']."'>".$row['FormName']."</option>";
		} else {
			echo "
            <option value=''>".GetLangVar('nameselect')."</option>";
		}
		//formularios usuario
		$qq = "SELECT * FROM Formularios  WHERE (AddedBy=".$_SESSION['userid']." OR Shared=1) AND HabitatForm=1  ORDER BY FormName ASC";
		$rr = mysql_query($qq,$conn);
		while ($row= mysql_fetch_assoc($rr)) {
			echo "
            <option value='".$row['FormID']."'>".$row['FormName']."</option>";
	}	
	echo "
          </select>
        </td>
      </tr>
       <tr><td>Usar modelo de descrição&nbsp;<img height=14 src=\"icons/icon_question.gif\" ";
		$help = "Neste caso a descrição gerada será feita utilizando o modelo descritivo do formulário. O formulário precisa ter um modelo para usar esta opção";
		echo " onclick=\"javascript:alert('$help');\" /></td><td><input type='checkbox' value='1' name='usarmodelohabitat' /></td>
		</tr>
    </table>
  </td>
</tr>";
if ($bgi % 2 == 0) {$bgcolor = $linecolor2 ;} else { $bgcolor = $linecolor1 ;} $bgi++;
echo "
<tr bgcolor = '".$bgcolor."'>
  <td >
    <table>
      <tr><td class='tdsmallbold'>Quais</td></tr><tr><td></td>
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
<td align='center'><input type='submit' value='Exportar' style=\"cursor:pointer;\" />
</tr>
</table>
</form>";
$which_java = array("<script type='text/javascript' src='javascript/myjavascripts.js'></script>");
FazFooter($which_java,$calendar=TRUE,$footer=$menu);
?>