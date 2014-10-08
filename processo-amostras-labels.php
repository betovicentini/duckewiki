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
$title = 'Imprimindo etiquetas para herbários';
$body = '';
FazHeader($title,$body,$which_css,$which_java,$menu);

if (!isset($final)) {
	$tt = "Imprimindo etiquetas de processo";
echo "
<br />
<table class='myformtable' cellpadding='7' align='center'  width='80%'>
<thead>
  <tr><td >$tt</td></tr>
</thead>
<tbody>
  <form name='coletaform' action='processo-amostras-labels.php' method='post'>
  <input type='hidden' name='ispopup' value='$ispopup' >
  <input type='hidden' name='processoid' value='".$processoid."' />";
  
if ($bgi % 2 == 0){$bgcolor = $linecolor2 ;} else {$bgcolor = $linecolor1 ;} $bgi++;
echo "
<tr bgcolor = '".$bgcolor."'>
  <td >
    <table align='left'>
      <tr>
        <td class='tdsmallbold'>".GetLangVar('nameformulario')."  ".GetLangVar('namenota')."&nbsp;&nbsp;<img height=15 src=\"icons/icon_question.gif\" ";
		$help = "Selecione um formulário que contém variáveis para gera as notas. Neste caso a descrição será feita na ordem das variáveis no formulário. Se deixar vazio irá colocar na etiqueta TODA informação associada á amostra, ordenadas pela estrutura das variáveis (classe + nome da variável)";
		echo " onclick=\"javascript:alert('$help');\" /></td>
        <td class='tdformnotes'>
          <select name='formid' >";
		if (!empty($formnotes)) {
			$qq = "SELECT * FROM Formularios WHERE FormID='$formnotes'";
			$rr = mysql_query($qq,$conn);
			$row= mysql_fetch_assoc($rr);
			echo "
            <option selected value='".$row['FormID']."'>".$row['FormName']." (".$row['AddedDate'].")</option>";
		} else {
			echo "
            <option value=0>".GetLangVar('nameselect')."</option>";
		}
	//formularios usuario
	$qq = "SELECT * FROM Formularios WHERE (AddedBy=".$_SESSION['userid']." OR Shared=1) AND HabitatForm<>1 ORDER BY FormName,Formularios.AddedDate ASC";
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

if ($bgi % 2 == 0){$bgcolor = $linecolor2 ;} else {$bgcolor = $linecolor1 ;} $bgi++;
echo "
<tr bgcolor = '".$bgcolor."'>
  <td >
    <table align='left'>
      <tr>
        <td class='tdsmallbold'>".GetLangVar('nameformulario')." ".GetLangVar('namehabitat')." &nbsp;&nbsp;<img height=15 src=\"icons/icon_question.gif\" ";
		$help = "Selecione um formulário que contém variáveis usadas em descrições de hábitat. O HabitatID da amostra será usado como referência para a variação nessas variáveis, e uma descrição será produzida seguindo a ordem das variáveis no formulário. O formato da descrição é definido na função habitatstring.sql";
		echo " onclick=\"javascript:alert('$help');\" /></td>
        <td class='tdformnotes'>
          <select name='formidhabitat' >
            <option value=''>".GetLangVar('nameselect')."</option>";
	//formularios usuario
	$qq = "SELECT * FROM Formularios WHERE (AddedBy=".$_SESSION['userid']." OR Shared=1) AND HabitatForm=1  ORDER BY FormName,Formularios.AddedDate ASC";
	$rr = mysql_query($qq,$conn);
	while ($row= mysql_fetch_assoc($rr)) {
		if (($formidhabitat+0)==$row['FormID']) {
			$slt = 'selected';
		} else {
			$slt ='';
		}
		echo "
            <option $slt value='".$row['FormID']."'>".$row['FormName']."</option>";
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
        <td class='tdsmallbold'>".GetLangVar('namelogo')."</td>
        <td><input type=radio name='useprojectlog' value='1' />&nbsp;".GetLangVar('nameprojeto')."s</td>
        <td><input type=radio name='useprojectlog' checked value='0' />&nbsp;INPA</td>
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
        <td class='tdsmallbold'>Quantas</td>
        <td><input type='radio' name='duplisTraitID' value='1' />Uma etiqueta por amostra apenas</td>
        <td><input type='radio' name='duplisTraitID' checked value='".$duplicatesTraitID."' />Uma etiqueta por duplicata</td>
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
        <td><input type='radio' name='quais' checked value='1' />Para todas as amostras</td>
         </tr><tr><td></td>
        <td><input type='radio' name='quais'  value='2' />Para as amostras COM número $herbariumsigla</td>
         </tr><tr><td></td>
        <td><input type='radio' name='quais'  value='3' />Para as amostras SEM número $herbariumsigla</td>        
      </tr>
    </table>
  </td>
</tr>";
if ($bgi % 2 == 0){$bgcolor = $linecolor2 ;} else {$bgcolor = $linecolor1 ;} $bgi++;
echo "
<tr bgcolor = '".$bgcolor."'>
  <td >
    <table align='center' >
      <tr>
        <input type='hidden' name='final' value='' />
        <td align='center' ><input type='submit' value='Imprimir' class='bsubmit' onclick=\"javascript:document.coletaform.final.value=1\" /> </td>
      </tr>
    </table>
  </td>
</tr>
</form>
</tbody>
</table>
";
} else {
	if ($duplisTraitID==1) {
		$duplicatesTraitID2 = 1;
		unset($duplisTraitID);
	}
	if (empty($herbariumsigla)) {
	$herbariumsigla = 'HERB_NO';
	}
	$tbname = 'processo_'.$processoid;
	if ($quais==2) {
		$inpa = "AND ".$herbariumsigla.">0 ";
	}
	if ($quais==3) {
		$inpa = "AND (".$herbariumsigla."=0 OR ".$herbariumsigla." IS NULL)";
	}
	$qq = "SELECT count(*) as especimenesids FROM ".$tbname." WHERE EXISTE=1 ".$inpa;
	$re = mysql_query($qq,$conn);
	$rwe = mysql_fetch_assoc($re);
	$specsids = $rwe['especimenesids'];
	//echo $specsids."<br />";
	if (!empty($specsids)) {
	  echo "
<form name='lastform' action='label-exec.php' method='post'>
  <input type='hidden' name='ispopup' value='".$ispopup."' />
  <input type='hidden' name='useprojectlog' value='".$useprojectlog."' />
  <input type='hidden' name='duplicatesTraitID2' value='".$duplicatesTraitID2."' />
  <input type='hidden' name='duplisTraitID' value='".$duplisTraitID."' />
  <input type='hidden' name='spec_label' value='1' />
  <input type='hidden' name='etitype'  value='EspecimenesIDS' />
  <input type='hidden' name='formid'  value='".$formid."' />
    <input type='hidden' name='formidhabitat'  value='".$formidhabitat."' />
    <input type='hidden' name='processoid'  value='".$processoid."' />
    <input type='hidden' name='quais'  value='".$quais."' />
    <input type='hidden' name='monidata'  value='".$monidata."' />
  <script language=\"JavaScript\">setTimeout('document.lastform.submit()',1);</script>
</form>";
//    <input type='hidden' name='especimenesids'  value='".$specsids."' />

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