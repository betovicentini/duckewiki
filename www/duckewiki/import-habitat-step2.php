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
"<link rel='stylesheet' type='text/css' href='css/cssmenu.css' />"
);
$which_java = array(
"<script type='text/javascript' src='css/cssmenuCore.js'></script>",
"<script type='text/javascript' src='css/cssmenuAddOns.js'></script>",
"<script type='text/javascript' src='css/cssmenuAddOnsItemBullet.js'></script>"
);
$title = 'Importar Habitat 02';
$body = '';
FazHeader($title,$body,$which_css,$which_java,$menu);


$definedfields = array();
if (!empty($gazetteeridfield)) { $definedfields[] = $gazetteeridfield; }
if (!empty($gazetteerfield)) { $definedfields[] = $gazetteerfield; }
unset($_POST['parentgazetteerid']);
echo "
<form action='import-habitat-hub.php' method='post' name='impprepform'>";
//coloca as variaveis anteriores
foreach ($ppost as $kk => $vv) {
echo "
  <input type='hidden' name='".$kk."' value='".$vv."' />"; 
}

echo "
<br />
<table cellpadding='7' class='myformtable' align='left'>
<thead>
<tr><td colspan='100%'>Definir o significado das demais colunas</td></tr>
<tr class='subhead'>
  <td>Coluna</td>
  <td>Valor mínimo</td>
  <td>Valor máximo</td>
  <td class='selectedval'>Selecione significado&nbsp;<img height=14 src=\"icons/icon_question.gif\" ";
$help = "Você tem 3 opções de tipos de informação: (1) uma CLASSE de habitat, ou seja uma coluna indicando uma categoria de CLASSIFICAÇÃO;\n (2) VARIÁVEL ESTÁTICA, que são variáveis categóricas, quantitativas ou de texto que NAO TEM UMA DATA ASSOCIADA; \n (3) VARIÁVEL DE MONITORAMENTO, que são variáveis categóricas, quantitativas ou de texto associadas a UMA DATA, neste caso você deve obrigatóriamente indica a COLUNA DA DATA da medição ou obs, ou pode indicar uma DATA UNICA para a variável para TODOS os registros no arquivo";
echo " onclick=\"javascript:alert('$help');\" /></td>
</tr>
</thead>
<tbody>
";
	$idx=1;
	$qq = "SELECT * FROM `".$tbname."` PROCEDURE ANALYSE ()";
	$rq = mysql_query($qq,$conn);
	while ($rw = mysql_fetch_assoc($rq)) {
		$fin = $rw['Field_name'];
		$zz = explode(".",$fin);
		$xt = count($zz)-1;
		$fieldname = $zz[$xt];
		$kkv = in_array($fieldname,$definedfields);
		$npr = strlen($tbprefix);
		if (!$kkv && $fieldname!='ImportID' && substr($fieldname,0,$npr)!=$tbprefix) {
			if ($bgi % 2 == 0){$bgcolor = $linecolor2 ;}  else{$bgcolor = $linecolor1 ;} $bgi++;
			echo "
<tr bgcolor = '".$bgcolor."'>
  <td class='tdsmallbold'>".$fieldname."</td>
  <td style='text-align:center' class='tdformnotes'>".$rw['Min_value']."</td>
  <td style='text-align:center' class='tdformnotes'>".$rw['Max_value']."</td>
  <td>
    <select name='fieldsign[".$fieldname."]'>";
	$qf = "SELECT *  FROM `Import_Fields` WHERE `CLASS` LIKE '%outros%' ORDER BY CLASS,DEFINICAO";
	$rqf = mysql_query($qf,$conn);
echo "
      <option value=''>".GetLangVar('nameselect')."</option>";
		while ($rwqf = mysql_fetch_assoc($rqf)) {
			$brh = $rwqf['BRAHMS'];
			$def = $rwqf['DEFINICAO'];
echo "
      <option value='".$brh."' >$def</option>";
		}
	echo "
    </select>
  </td>
</tr>";
		}
		$idx++;
	}
if ($bgi % 2 == 0){$bgcolor = $linecolor2 ;}  else{$bgcolor = $linecolor1 ;} $bgi++;
echo "
<tr bgcolor = '".$bgcolor."'><td colspan='100%' align='center'><input type='submit' value='".GetLangVar('namecontinuar')."' class='bsubmit' /></td></tr>";
if ($bgi % 2 == 0){$bgcolor = $linecolor2 ;}  else{$bgcolor = $linecolor1 ;} $bgi++;
echo "
<tr bgcolor = '".$bgcolor."'><td class='selectedval' colspan='100%' align='left' >*Colunas não definidas serão ignoradas!</td></tr>
</tbody>
</table>
</form>";

$which_java = array("<script type='text/javascript' src='javascript/myjavascripts.js'></script>",
"<!-- Create Menu Settings: (Menu ID, Is Vertical, Show Timer, Hide Timer, On Click ('all' or 'lev2'), Right to Left, Horizontal Subs, Flush Left, Flush Top) -->",
"<script type='text/javascript'>qm_create(0,false,0,500,false,false,false,false,false);</script>");
FazFooter($which_java,$calendar=FALSE,$footer=$menu);
?>