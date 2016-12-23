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

if (count($gget)>count($ppost)) {
	$nvars = $gget;
} else {
	$nvars = $ppost;
}

$menu = FALSE;
$which_css = array(
"<link href='css/geral.css' rel='stylesheet' type='text/css' />");
$which_java = array();
$title = 'Importar Especialistas Passo 05';
$body = '';
FazHeader($title,$body,$which_css,$which_java,$menu);

//echopre($ppost);
//CHECA OS GENEROS
if (!isset($generosvars)) {
		$generosvars = array($generocol);
		$colnames = array($tbprefix."GenerosIDS");
		$collfam = $tbprefix.'FamiliaID';
} 
else {
	$generosvars = unserialize($genusvars);
}
if (count($generosvars)>0) {
	$oldgenerosvars = $generosvars;
	$idx=1;
	foreach ($generosvars as $peskk => $pesvv) {
		$cln = $colnames[$peskk];
		$qq = "ALTER TABLE ".$tbname." ADD COLUMN ".$cln." VARCHAR(100) DEFAULT ''";
		//echo $qq."<br />";
		@mysql_query($qq,$conn);
		$qq = "UPDATE ".$tbname." SET `".$cln."`=checkgeneros(`".$pesvv."`, `".$collfam."`) where `".$pesvv."`<>'' AND `".$pesvv."` IS NOT NULL";
		//echo $qq."<br />";
		mysql_query($qq,$conn);
		$qq = "SELECT DISTINCT `".$pesvv."` FROM `".$tbname."`  WHERE `".$pesvv."`<>'' AND `".$pesvv."` IS NOT NULL AND `".$cln."`='ERRO'";
		//echo $qq."<br />";
		$res = mysql_query($qq,$conn);
		$nres = mysql_numrows($res);
		if ($nres>0) {
			if ($nused==0) {
echo "<br />
<table align='left' class='myformtable' cellpadding='5'>
<thead>
  <tr><td colspan='3'>ATENÇÃO! A coluna com gêneros</td></tr>
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
    <td>".$nres." registros tem generos que não foram encontrados no wiki!</td>";
    
echo "
    <td align='center'>
      <input id='butidx_".$idx."' type='button' style=\"font-size:90%;background-color:#0066CC; color: white;border: thin outset gray;padding: 0.1em\"
 value='Corrigir' ";
$myurl ="novosgeneros-popup.php?famcolname=".$collfam."&colname=".$cln."&orgcol=".$pesvv."&tbname=".$tbname."&buttonidx=butidx_".$idx; 
echo " onclick = \"javascript:small_window('$myurl',800,400,'Corrigir valores de generos');\" />
    </td>    
    
  </tr>";

		} 
		else {
			unset($oldgenerosvars[$peskk]);
		}
		$idx++;
	}
	if ($nused>0) {
			if ($bgi % 2 == 0){$bgcolor = $linecolor2 ;}  else{$bgcolor = $linecolor1 ;} $bgi++;
echo "
<form action='import-especialistas-step6.php' method='post'>
";
foreach ($nvars as $kk => $vv) {
echo "
  <input type='hidden' name='".$kk."' value='".$vv."' />"; 
}
echo "
  <input name='peoplevars' value='".serialize($oldgenerosvars)."' type='hidden' />";
echo "
  <tr bgcolor = '".$bgcolor."'><td align='center' colspan='3'>
    <input type='submit' value='".GetLangVar('namecontinuar')."' class='bsubmit' />
  </td></tr>
</form>";
echo "
</tbody>
</table>";

			} else {
				$done=TRUE;
			}
} 
else {
			$done=TRUE;
}
if ($done) {
echo "
  <form name='myform' action='import-especialistas-step6.php' method='post'>";
foreach ($nvars as $kk => $vv) {
echo "
  <input type='hidden' name='".$kk."' value='".$vv."' />"; 
}
echo "<script language=\"JavaScript\">setTimeout('document.myform.submit()',0.0001);</script>";
echo
"</form>";
}

$which_java = array(
"<script type='text/javascript' src='javascript/myjavascripts.js'></script>");
FazFooter($which_java,$calendar=FALSE,$footer=$menu);

?>