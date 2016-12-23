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
$title = 'Plot Trees in Map';
$body = '';
FazHeader($title,$body,$which_css,$which_java,$menu);

$qu = "SELECT PathName, DimX,DimY FROM Gazetteer WHERE GazetteerID='".$gazetteerid."'";
$rzz = mysql_query($qu,$conn);
$row = mysql_fetch_assoc($rzz);
$titulo = $row['PathName'];
echo "
<div id=\"topanel\">&nbsp;$titulo</div>
<div>
<div id=\"panel\">
<form>
<select name='taxafilter' onchange=\"changeoptionlist(this.value,".$gazetteerid.");\" >
  <option value=''>Selecione um taxa</option>";

$qq = "SELECT DISTINCT famtb.Familia, gentb.Genero, iddet.FamiliaID, iddet.GeneroID FROM Plantas as pltb LEFT JOIN Identidade as iddet ON pltb.DetID=iddet.DetID LEFT JOIN Tax_Generos as gentb ON iddet.GeneroID=gentb.GeneroID  LEFT JOIN Tax_Familias as famtb ON iddet.FamiliaID=famtb.FamiliaID WHERE (pltb.GazetteerID=".$gazetteerid;
$qu = "SELECT * FROM Gazetteer WHERE ParentID='".$gazetteerid."'";
$rzz = mysql_query($qu,$conn);
$nrzz = mysql_numrows($rzz);
if ($nrzz>0) {
	while ($row = mysql_fetch_assoc($rzz)) {
		$qq .= " OR pltb.GazetteerID='".$row['GazetteerID']."'";
	}
}
$qq .= ") AND iddet.EspecieID>0 ORDER BY famtb.Familia,gentb.Genero";
$rz = mysql_query($qq,$conn);
$nrz = mysql_numrows($rz);
if ($nrz==0) {
echo "
  <option selected value=''>Não há árvores para essa parcela</option>";
} 
else {
	$famid=0;
	$specid = 0;
	$genid = 0;
	while ($row = mysql_fetch_assoc($rz)) {
		if ($row['FamiliaID']!=$famid) {
			echo "<option  value=\"famid_".$row['FamiliaID']."\">".strtoupper($row['Familia'])."</option>";
		}
		$famid = $row['FamiliaID'];
echo "
  <option  value=\"genid_".$row['GeneroID']."\">&nbsp;&nbsp;".$row['Genero']."</option>";
	}
}
echo "
</select>
</form>";
echo "
<div id=\"mapbut\"><input type='button' value='Atualizar mapa com spp selecionadas' 
onclick=\"changemap('specieslist',".$gazetteerid.",'mapcontainer');\" ></div>
<div id=\"txtHint\"></div></div>
<div id=\"mapcontainer\">
</div>
</div>";

//echo "<img src=\"graph_emptyplot.php?gazetteerid=".$gazetteerid."\">";
$which_java = array("<script type='text/javascript' src='javascript/myjavascripts.js'></script>",
"<!-- Create Menu Settings: (Menu ID, Is Vertical, Show Timer, Hide Timer, On Click ('all' or 'lev2'), Right to Left, Horizontal Subs, Flush Left, Flush Top) -->",
"<script type='text/javascript'>qm_create(0,false,0,500,false,false,false,false,false);</script>");
FazFooter($which_java,$calendar=FALSE,$footer=$menu);
?>