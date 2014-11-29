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
$title = 'Importar Expedito 04';
$body = '';
FazHeader($title,$body,$which_css,$which_java,$menu);

//////variaveis deste formulario
$nnv = $_SESSION['fieldsign'];
$newv = unserialize($nnv);


//checa campos com datas
$datalev = trim($newv['DATA_LEVANTAMENTO']);
if (!empty($datalev)) {
		$cll = $tbprefix."DataColeta";
		$orgcol = $datalev;
		$qq = "ALTER TABLE ".$tbname." ADD COLUMN ".$cll." DATE DEFAULT NULL";
		@mysql_query($qq,$conn);
		$qq = "UPDATE `".$tbname."` SET `".$cll."`=date_check(`".$orgcol."`) WHERE `".$orgcol."`<>'' AND `".$orgcol."` IS NOT NULL AND `".$cll."` IS NULL";
		$rr = mysql_query($qq,$conn);
		$qq = "SELECT * FROM `".$tbname."` WHERE `".$orgcol."`<>'' AND `".$orgcol."` IS NOT NULL AND `".$cll."` IS NULL";
		$rr = mysql_query($qq,$conn);
		$nr = mysql_numrows($rr);
		if ($nr>0) {
				$dataproblems[$orgcol] = array("$nr registros não tem um valor valido para Data!",'data');
		}
}

//tem problemas em colunas avisa e interrompe a importacao
if (count($dataproblems)>0) {
echo "
<br />
<form action='import-expedito-step04.php' method='post'>
<input type='hidden' name='fieldsign' value='".$fieldsign."'>
<table align='center' class='myformtable' cellpadding='7'>
<thead>
 <tr><td align='center' colspan='100%'>Os seguintes erros em colunas com Datas foram encontrados</td></tr>
 <tr class='subhead'>
  <td>Coluna</td>
  <td>Erro</td>
  <td>O que fazer?</td>
 </tr>
</thead>
<tbody>";
	foreach ($ppost as $kk => $vv) {
		if (!empty($vv)) {
		echo "
  <input type='hidden' name='".$kk."' value='".$vv."'>"; 
		}
	}
   $idx  = 1;
   foreach ($dataproblems as $orgcol => $vv) {
		$cln = $tbprefix."DataColeta";
if ($bgi % 2 == 0){$bgcolor = $linecolor2 ;}  else{$bgcolor = $linecolor1 ;} $bgi++;
echo "
<tr bgcolor = '".$bgcolor."'>
  <td>$orgcol</td>
  <td>".$vv[0]."</td>
  <td align='center'><input id='butidx".$idx."' type='button' style=\"font-size:90%;background-color:#0066CC; color: white;border: thin outset gray;padding: 0.1em\" value='Corrigir' ";
$myurl ="checkdatas-popup.php?colname=".$cln."&orgcol=".$orgcol."&tbname=".$tbname."&buttonidx=butidx".$idx."&datatipo=".$vv[1]; 
echo " onclick = \"javascript:small_window('$myurl',800,400,'Corrigir campos de datas');\"></td>
</tr>"; 
		$idx++;
	 }

if ($bgi % 2 == 0){$bgcolor = $linecolor2 ;}  else{$bgcolor = $linecolor1 ;} $bgi++;
echo "
<tr bgcolor = '".$bgcolor."'><td align='center' colspan='100%'><input type='submit' value='".GetLangVar('namecontinuar')."' class='bsubmit' /></td></tr>
</tbody>
</table>
</form>";
} 
	else {
		$datachecked= TRUE;
}

unset($_SESSION['peoplevars']);
$_SESSION['fieldsign'] = serialize($newv);
if ($datachecked==TRUE) {

	echo "
<form name='myform' action='import-expedito-step05.php' method='post'>
";
//coloca as variaveis anteriores
	foreach ($ppost as $kk => $vv) {
	echo "
  	<input type='hidden' name='".$kk."' value='".$vv."'>"; 
	}
//echo "<script language=\"JavaScript\">setTimeout('document.myform.submit()',0.0001);</script>
echo "
  <table cellpadding=\"1\" width='50%' align='center'>
    <tr><td class='tdsmallbold' align='center'><input type='submit' value='continuar' class='bsubmit' /></td></tr>
  </table> 
 </form>";
}
$which_java = array("<script type='text/javascript' src='javascript/myjavascripts.js'></script>",
"<!-- Create Menu Settings: (Menu ID, Is Vertical, Show Timer, Hide Timer, On Click ('all' or 'lev2'), Right to Left, Horizontal Subs, Flush Left, Flush Top) -->",
"<script type='text/javascript'>qm_create(0,false,0,500,false,false,false,false,false);</script>");
FazFooter($which_java,$calendar=FALSE,$footer=$menu);
?>