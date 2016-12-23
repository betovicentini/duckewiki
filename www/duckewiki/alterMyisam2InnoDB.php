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
$title = '';
$which_css = array(
"<link href='css/geral.css' rel='stylesheet' type='text/css' >",
"<link rel='stylesheet' type='text/css' href='css/cssmenu.css' >"
);
$which_java = array(
"<script type='text/javascript' src='css/cssmenuCore.js'></script>",
"<script type='text/javascript' src='css/cssmenuAddOns.js'></script>",
"<script type='text/javascript' src='css/cssmenuAddOnsItemBullet.js'></script>"
);
$body='';
$title = 'AlterFromMyISAMtoInnoDB';
FazHeader($title,$body,$which_css,$which_java,$menu);

if (!isset($tabelas)) {
	$tabs = array();
	$res = mysql_query("SHOW TABLES",$conn);
	while (($row = mysql_fetch_row($res)) != null) {
        $tabs[] = $row[0];
    }
	$tab = $tabs[0];
	$tabelas = serialize($tabs);
} else {
	$tabs = unserialize($tabelas);
	unset($tabs[0]);
	$tabs = array_values($tabs);
	$tab = $tabs[0];
	$tabelas = serialize($tabs);
	$toprint = '';
}

 if (!empty($tab)) {
	$qq = "ALTER TABLE `".$tab."` ENGINE = INNODB";
	//echo $qq;
	$res = mysql_query($qq,$conn);
	if ($res) {
	echo "<p style=\"background-color: 'green';\">Tabela `".$tab."` OK!<p>";
	} else {
	  echo "<p style=\"background-color: 'yellow';\">Tabela `".$tab."` falhou<p>";
   }
	echo "
<form name='myform' method='post' action='alterMyisam2InnoDB.php'>
  <input type='hidden' name='ispopup' value='".$ispopup."' />   
  <input type='hidden' name='tabelas' value='$tabelas' />   
  <input type='hidden' name='toprint' value='$toprint' />   
  <script language=\"JavaScript\">setTimeout('document.myform.submit()',0.00001);</script>
</form>";	
} else {
echo "
<br />
<table align='center'>
<tr>
<form>
<td><input style='cursor:pointer;' type='submit' value='".GetLangVar('nameconcluir')."' class='bblue' onclick='javascript:window.close();'/></td>
</form>
</tr>
</table>
<br />";
 }    

$which_java = array("<script type='text/javascript' src='javascript/myjavascripts.js'></script>",
"<!-- Create Menu Settings: (Menu ID, Is Vertical, Show Timer, Hide Timer, On Click ('all' or 'lev2'), Right to Left, Horizontal Subs, Flush Left, Flush Top) -->",
"<script type='text/javascript'>qm_create(0,false,0,500,false,false,false,false,false);</script>");
FazFooter($which_java,$calendar=FALSE,$footer=$menu);

?>