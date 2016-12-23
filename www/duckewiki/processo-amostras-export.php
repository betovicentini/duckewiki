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
if ($ispopup==1) {
	$menu = FALSE;
} else {
	$menu = TRUE;
}
$which_css = array(
"<link href='css/geral.css' rel='stylesheet' type='text/css' />",
//"<link rel='stylesheet' type='text/css' href='css/cssmenu.css' />"
);
$which_java = array(
//"<script type='text/javascript' src='css/cssmenuCore.js'></script>",
//"<script type='text/javascript' src='css/cssmenuAddOns.js'></script>",
//"<script type='text/javascript' src='css/cssmenuAddOnsItemBullet.js'></script>"
);
$title = 'Exportando arquivo do material processado';
$body = '';
FazHeader($title,$body,$which_css,$which_java,$menu);

if (!isset($final)) {
	$formid = $formnotes;
	$tt = "Exportando arquivo do material processado";
echo "
<br />
<form method='post' name='finalform' action='processo-amostras-export.php'>
  <input type='hidden' name='ispopup' value='".$ispopup."'>
  <input type='hidden' name='processoid' value='".$processoid."' />
  <input type='hidden' name='forbrahms' value='1'>
<table class='myformtable' align='left' cellpadding=\"5\">
<thead>
<tr >
<td>$tt</td>
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
<tr>
        <td class='tdformnotes'>Inclui monitoramento?&nbsp;<img height=14 src=\"icons/icon_question.gif\" ";
		$help = "Se selecionado e a amostra for de uma planta marcada, o campo notas na planilha de exporação irá incluir como DESCRICAO DA PLANTA, todas as variáveis relacionadas à amostra e à planta marcada, incluindo dados de monitoramento, como por exemplo, todos os DAPs medidos para planta. Isso pode deixar a etiqueta desnecessariamente grande para uma amostra no herbário. Clicar para excluir dados de monitoramento"; 
		echo " onclick=\"javascript:alert('$help');\" /></td>
        <td class='tdformnotes'>
        <input type='checkbox'  name='monidata'  value=1>
        </td>
</tr>
    </table>
  </td>
</tr>";
//formulario variaveis
//habitat
if ($bgi % 2 == 0) {$bgcolor = $linecolor2 ;} else { $bgcolor = $linecolor1 ;} $bgi++;
echo "
<tr bgcolor = '".$bgcolor."'>
  <td >
    <table>
      <tr>
        <td class='tdsmallbold'>Formulário de hábitat&nbsp;<img height=14 src=\"icons/icon_question.gif\" ";
		$help = "Indique um formulário para gerar o campo de notas da habitat. SE VAZIO TODAS AS INFORMAÇÕES DE HABITAT RELACIONADAS À AMOSTRAS SERÃO INCLUIDAS NA DESCRIÇÃO";
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
    </table>
  </td>
</tr>";
if ($bgi % 2 == 0){$bgcolor = $linecolor2 ;} else {$bgcolor = $linecolor1 ;} $bgi++;
echo "
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
";
$lixooo=1000;
if ($lixooo==0) {
	$qn = "SELECT * FROM Traits WHERE TraitID=".$traitfertid;
	$rn = mysql_query($qn,$conn);
	$rw = mysql_fetch_assoc($rn);
	$tn = $rw['TraitName'];
if ($bgi % 2 == 0){$bgcolor = $linecolor2 ;} else{$bgcolor = $linecolor1 ;} $bgi++;
  echo "
  <tr bgcolor = '".$bgcolor."'>
    <td >
      <table>
        <tr>
          <td>Categorias de <b>".$tn."</b> para <b>EXCLUIR</b></td>
          <td>
            <select name='ferttoexcl' multiple='5'>
    ";
	$qt = "SELECT * FROM Traits WHERE ParentID=".$traitfertid;
    $rt = mysql_query($qt,$conn);
	while ($rtw = mysql_fetch_assoc($rt)) {
		echo "
              <option value='".$rtw['TraitName']."'>".$rtw['TraitName']."&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;</option>";
	}
    echo "
            </select>
          </td>
        </tr>
      </table>
    </td>
  </tr>";
}
if ($bgi % 2 == 0){$bgcolor = $linecolor2 ;} else {$bgcolor = $linecolor1 ;} $bgi++;
echo "
<tr bgcolor = '".$bgcolor."'>
  <td >
    <table align='center' >
      <tr>
        <input type='hidden' name='final' value='' />
        <td align='center' ><input style='cursor:pointer'  type='submit' value='Gerar arquivos' class='bsubmit' onclick=\"javascript:document.finalform.final.value=1\" /> </td>
      </tr>
    </table>
  </td>
</tr>
</tbody>
</table>
</form>
";
} 
else {
	//$basicvariables = implode(";",array( 'datacol', 'taxacompleto', 'localidade', 'gps', 'habitat', 'addcoll', 'herbarios', 'registroINPA', 'Vernacular', 'projeto'));  
	$basicvariables = implode(";",array( 'datacol', 'taxacompleto', 'localidade', 'gps', 'habitat', 'addcoll', 'nirdata', 'registroINPA', 'Vernacular', 'projeto', 'herbarios')); 
	if (empty($herbariumsigla)) {
		$herbariumsigla = 'HERB_NO';
	}
	
	$tbname = 'processo_'.$processoid;
	if ($quais==2) {
		$qwhere = "AND ".$herbariumsigla.">0 ";
	}
	if ($quais==3) {
		$qwhere = "AND (".$herbariumsigla."=0 OR ".$herbariumsigla." IS NULL)";
	}
	if (!empty($ferttoexcl)) {
		$qwhere .= " AND Fert NOT LIKE '%".$ferttoexcl."%' AND Fert<>'' AND Fert IS NOT NULL";
	} 
	$qq = "SELECT count(*) as especimenesids FROM ".$tbname." WHERE EXISTE=1 ".$qwhere;
	$re = mysql_query($qq,$conn);
	$rwe = mysql_fetch_assoc($re);
	$specsids = $rwe['especimenesids'];
	if (!empty($specsids)) {
	  echo "
<form name='lastform' action='export-especimenes-exec.php' method='post'>
  <input type='hidden' name='ispopup' value='".$ispopup."' />
  <input type='hidden' name='formnotas' value='".$formnotas."' />
  <input type='hidden' name='formhabitatdesc' value='".$formhabitatdesc."' />
  <input type='hidden' name='specbasicvars'  value='".$basicvariables."' />
  <input type='hidden' name='processoid'  value='".$processoid."' />
  <input type='hidden' name='forbrahms'  value='".$forbrahms."' />
  <input type='hidden' name='quais'  value='".$quais."' />  
  <input type='hidden' name='monidata'  value='".$monidata."' />  
  <script language=\"JavaScript\">setTimeout('document.lastform.submit()',1);</script>
</form>";
//    <input type='hidden' name='ferttoexcl'  value='".$ferttoexcl."' /> 

	} else {
		echo "
<br />
<table cellpadding=\"7\" width='50%' align='center' class='erro'>
  <tr><td class='tdsmallbold' align='center'>Não há registros para imprimir como indicado!</td></tr>
  <tr><td class='tdsmallbold' align='center'><input style='cursor:pointer' type='button'  class='bsubmit'  value='Fechar'  onclick='javascript: window.close();' ></td></tr>  
</table>
<br />";
	
	}

}

$which_java = array("<script type='text/javascript' src='javascript/myjavascripts.js'></script>"
//, "<!-- Create Menu Settings: (Menu ID, Is Vertical, Show Timer, Hide Timer, On Click ('all' or 'lev2'), Right to Left, Horizontal Subs, Flush Left, Flush Top) -->",
//"<script type='text/javascript'>qm_create(0,false,0,500,false,false,false,false,false);</script>"
);
FazFooter($which_java,$calendar=TRUE,$footer=$menu);
?>