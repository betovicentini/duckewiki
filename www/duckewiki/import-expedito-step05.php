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
$title = 'Importar Expedito 05';
$body = '';
FazHeader($title,$body,$which_css,$which_java,$menu);

$nnv = $_SESSION['fieldsign'];
$newv = unserialize($nnv);
if (!isset($pessoasvars)) {
	$colsign = array(
	"TESTEMUNHO_COLETOR",
	"OBSERVADOR");
	$pessoasvars = array();
	foreach ($colsign as $kk) {
		$datalev = trim($newv[$kk]);
		if (!empty($datalev)) {
			$pessoasvars[$kk] = $datalev;
		}
	}
} else {
	$ppeo = $_SESSION['peoplevars'];
	$pessoasvars = unserialize($ppeo);
}
if (count($pessoasvars)>0) {
	$oldpessoasvars = $pessoasvars;
	$idx=1;
	foreach ($pessoasvars as $peskk => $pesvv) {
		$cln = $tbprefix.$peskk;
		$qq = "ALTER TABLE ".$tbname." ADD COLUMN ".$cln." VARCHAR(100) DEFAULT ''";
		mysql_query($qq,$conn);
		//echo $qq."<br />";
		$qq = "UPDATE ".$tbname." SET `".$cln."`=checkpessoas(`".$pesvv."`) where `".$pesvv."`<>'' AND `".$pesvv."` IS NOT NULL";
		mysql_query($qq,$conn);
		$qq = "SELECT DISTINCT `".$pesvv."` FROM `".$tbname."`  WHERE `".$pesvv."`<>'' AND `".$pesvv."` IS NOT NULL AND `".$cln."`='ERRO'";
		$res = mysql_query($qq,$conn);
		$nres = mysql_numrows($res);
		if ($nres>0) {
			if ($nused==0) {
echo "<br />
<table align='left' class='myformtable' cellpadding='5'>
<thead>
  <tr><td colspan='100%'>ATENÇÃO! Sobre colunas com nomes de pessoas</td></tr>
  <tr class='subhead'>
    <td>Nome da coluna</td>
    <td>Problema encontrado</td>
    <td>O que fazer?</td>
  </tr>
</thead>
<tbody>";
$nused=1;
			}
			if ($bgi % 2 == 0){$bgcolor = $linecolor2 ;}  else{$bgcolor = $linecolor1 ;} $bgi++;
echo "
  <tr bgcolor = '".$bgcolor."'>
    <td>".$pesvv."</td>";
echo "
    <td>".$nres." registros tem pessoas que não foram encontrados no wiki!</td>";
    
echo "
    <td align='center'>
      <input id='butidx_".$idx."' type='button' style=\"font-size:90%;background-color:#0066CC; color: white;border: thin outset gray;padding: 0.1em\"
 value='Corrigir' ";
$myurl ="novaspessoas-popup.php?colname=".$cln."&orgcol=".$pesvv."&tbname=".$tbname."&buttonidx=butidx_".$idx; 
echo " onclick = \"javascript:small_window('$myurl',800,400,'Corrigir valores de nomes de pessoas');\">
    </td>    
    
  </tr>";

		} 
		else {
			unset($oldpessoasvars[$peskk]);
		}
		$idx++;
	}
	if ($nused>0) {
			if ($bgi % 2 == 0){$bgcolor = $linecolor2 ;}  else{$bgcolor = $linecolor1 ;} $bgi++;
			if (count($oldpessoasvars)>0) {
echo "
<form action='import-expedito-step05.php' method='post'>";
$_SESSION['peoplevars'] = serialize($oldpessoasvars);
echo "
  <input name='tbname' value='".$tbname."' type='hidden' >
  <input type='hidden' name='tbprefix' value='".$tbprefix."'>
  <tr bgcolor = '".$bgcolor."'><td align='center' colspan='100%'>
    <input type='submit' value='".GetLangVar('namecontinuar')."' class='bsubmit' />
  </td></tr>
</form>";
			} else {
				echo "
<form action='import-expedito-step05.php' method='post'>

  <input type='hidden' name='tbprefix' value='".$tbprefix."'>
  <input name='tbname' value='".$tbname."' type='hidden' >
  <input name='var_moni_ok' value='1' type='hidden' >
  <tr bgcolor = '".$bgcolor."'><td align='center' colspan='100%'><input type='submit' value='".GetLangVar('namecontinuar')."' class='bsubmit' /></td></tr>
</form>";
			}
echo "
</tbody>
</table>";
		} 
	else {
		$done=TRUE;
	}
} 
else {
	$done=TRUE;
}
$_SESSION['fieldsign'] = serialize($newv);
if ($done) {
echo "
<form name='myform' action='import-expedito-step06.php' method='post'>
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