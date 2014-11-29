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
$title = 'Salva busca de amostras';
FazHeader($title,$body,$which_css,$which_java,$menu);
$specs = explode(";",$tagnumbers);
$ff = array();
$nf = array();
foreach ($specs as $vv) {
	$zz = explode($separador,$vv);
	$qq = "SELECT EspecimenID FROM Especimenes JOIN Pessoas ON ColetorID=PessoaID WHERE Abreviacao LIKE '".trim($zz[0])."%' AND Number='".trim($zz[1])."'";
	$res = mysql_query($qq,$conn);
	//echo $qq;
	//echo "<br />";
	$nr = mysql_numrows($res);
	if ($nr==1) {
		$row = mysql_fetch_assoc($res);
		$ff[$vv] = $row['EspecimenID']; 
	} else {
		$nf[] = $vv;
	}
}

if (count($nf)>0) {
	echo "<br /><table class='erro' align='center'><tr><td>".count($nf)." registros não foram encontrados </td></tr>";
	foreach ($nf as $v) {
		echo "<tr><td>".$v."</td></tr>";
	}
	echo "</table>";
}

if (count($ff)>0) {
	$_SESSION['filtrospecids'] = serialize($ff);
	echo "<br /><table class='success' align='center'><tr><td>".count($ff)." registros foram encontrados </td></tr>";
		echo "<form >
					<tr><td><input type=button value='".GetLangVar('namesalvar')." ".strtolower(GetLangVar('namefiltro'))."' class='bsubmit' ";
						$myurl ="filtros-exec.php?ispopup=1";
					echo " onclick = \"javascript:small_window('$myurl',350,280,'Filtro');\" />
				</td></tr>
				</form>
		</table>";
}

$which_java = array("<script type='text/javascript' src='javascript/myjavascripts.js'></script>",
"<!-- Create Menu Settings: (Menu ID, Is Vertical, Show Timer, Hide Timer, On Click ('all' or 'lev2'), Right to Left, Horizontal Subs, Flush Left, Flush Top) -->",
"<script type='text/javascript'>qm_create(0,false,0,500,false,false,false,false,false);</script>");
FazFooter($which_java,$calendar=FALSE,$footer=$menu);

?>
