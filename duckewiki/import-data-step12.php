<?php
//este script checa Vernaculars se eles existirem
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
$title = 'Importar Dados Passo 12';
$body = '';
FazHeader($title,$body,$which_css,$which_java,$menu);

$fields = unserialize($_SESSION['fieldsign']);
if (in_array('VERNACULAR',$fields)) {
	$za = array_keys($fields, "VERNACULAR");
	$vernacularcolum = $za[0];
	$cln = $tbprefix."VernacularIDS";
	$qq = "ALTER TABLE ".$tbname." ADD COLUMN ".$cln." VARCHAR(200) DEFAULT ''";
	@mysql_query($qq,$conn);
	$qq = "UPDATE ".$tbname." SET `".$cln."`=vernacularschecks(`".$vernacularcolum."`) where `".$vernacularcolum."` IS NOT NULL AND `".$vernacularcolum."`<>''";
	mysql_query($qq,$conn);
	$qq = "SELECT DISTINCT `".$vernacularcolum."` FROM `".$tbname."`  WHERE `".$vernacularcolum."`<>'' AND `".$vernacularcolum."` IS NOT NULL AND `".$cln."`='ERRO'";
	$res = mysql_query($qq,$conn);
	$nres = mysql_numrows($res);
	if ($nres>0) {
echo "<br />
<table align='center' class='myformtable' cellpadding='5'>
<thead>
  <tr><td colspan='100%'>Nomes vulgares faltando</td></tr>
</thead>
<tbody>";
if ($bgi % 2 == 0){$bgcolor = $linecolor2 ;}  else{$bgcolor = $linecolor1 ;} $bgi++;
echo "
  <tr bgcolor = '".$bgcolor."'>
    <td>".$nres." registros tem vernaculars que não foram encontrados no wiki!</td>
    <td align='center'>
      <input id='butidx' type='button' style=\"font-size:90%;background-color:#0066CC; color: white;border: thin outset gray;padding: 0.1em\"
 value='Corrigir' ";
$myurl ="vernacular-import-popup.php?colname=".$cln."&orgcol=".$vernacularcolum."&tbname=".$tbname."&buttonidx=butidx"; 
echo " onclick = \"javascript:small_window('$myurl',800,400,'Corrigir e cadastrar vernaculars');\" />
  </td>
  </tr>";
if ($bgi % 2 == 0){$bgcolor = $linecolor2 ;}  else{$bgcolor = $linecolor1 ;} $bgi++;
echo "
<form action='import-data-step12.php' method='post'>";
foreach ($_POST as $vk => $v1) {
echo "<input type='hidden' name='".$vk."' value='".$v1."' />"; 
}
echo "<input name='tbname' value='".$tbname."' type='hidden' />
  <tr bgcolor = '".$bgcolor."'><td align='center' colspan='100%'>
    <input type='submit' value='".GetLangVar('namecontinuar')."' class='bsubmit' />
  </td></tr>
</form>
</tbody>
</table>";
	} else {
		$vernacularschecked=TRUE;
	}
} else {
	$vernacularschecked=TRUE;
}

if ($vernacularschecked) {
	$steps = unserialize($_SESSION['importacaostep']);
	unset($steps[0]);
	$stt = array_values($steps);
	$_SESSION['importacaostep'] = serialize($stt);
echo "
  <form name='myform' action='import-data-hub.php' method='post'>
  <input type='hidden' name='ispopup' value='".$ispopup."' />      
	<script language=\"JavaScript\">setTimeout('document.myform.submit()',0.0001);</script>
  </form>";
}
$which_java = array("<script type='text/javascript' src='javascript/myjavascripts.js'></script>",
"<!-- Create Menu Settings: (Menu ID, Is Vertical, Show Timer, Hide Timer, On Click ('all' or 'lev2'), Right to Left, Horizontal Subs, Flush Left, Flush Top) -->",
"<script type='text/javascript'>qm_create(0,false,0,500,false,false,false,false,false);</script>");
FazFooter($which_java,$calendar=FALSE,$footer=$menu);

?>