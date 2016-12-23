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
if((!isset($uuid) || 
	(trim($uuid)=='')) && count($listsarepublic)==0) {
		header("location: access-denied.php");
	exit();
} else {
	$acclevel = $_SESSION['accesslevel'];
}

//////PEGA E LIMPA VARIAVEIS
if (!empty($_POST['detset'])) {
	$detset = $_POST['detset'];
	unset($_POST['detset']);
}
if (!empty($_GET['detset'])) {
	$detset = $_GET['detset'];
	unset($_GET['detset']);
}

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


#seleciona o titulo
$qu = "SELECT PathName, DimX,DimY FROM Gazetteer WHERE GazetteerID='".$gazetteerid."'";
$rzz = mysql_query($qu,$conn);
$row = mysql_fetch_assoc($rzz);
$titulo = $row['PathName'];

#seleciona os dados

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
if ($nrz>0) {
$qqz = "SELECT DISTINCT iddet.FamiliaID,iddet.GeneroID,iddet.EspecieID,iddet.InfraEspecieID FROM Plantas as pltb LEFT JOIN Identidade as iddet ON pltb.DetID=iddet.DetID LEFT JOIN Tax_InfraEspecies as infsptb ON iddet.InfraEspecieID=infsptb.InfraEspecieID LEFT JOIN Tax_Especies as spectb ON iddet.EspecieID=spectb.EspecieID LEFT JOIN Tax_Generos as gentb ON iddet.GeneroID=gentb.GeneroID  LEFT JOIN Tax_Familias as famtb ON iddet.FamiliaID=famtb.FamiliaID WHERE (pltb.GazetteerID=".$gazetteerid;
$qu = "SELECT * FROM Gazetteer WHERE ParentID='".$gazetteerid."'";
$rzz = mysql_query($qu,$conn);
$nrzz = mysql_numrows($rzz);
if ($nrzz>0) {
	while ($row = mysql_fetch_assoc($rzz)) {
		$qqz .= " OR pltb.GazetteerID='".$row['GazetteerID']."'";
	}
}
$qqz .= ") AND iddet.EspecieID>0 ORDER BY famtb.Familia,gentb.Genero,spectb.Especie,infsptb.InfraEspecie LIMIT 0,1";
	$rzz = mysql_query($qqz,$conn);
	$rzw = mysql_fetch_assoc($rzz);
	//echopre($rzw);
	$stfamid = "famid_".$rzw['FamiliaID'];
	$stgenid = "genid_".$rzw['GeneroID'];
	$stspecid = "specid_".$rzw['EspecieID'];	
}


$which_css = array(
"<link href='css/geral.css' rel='stylesheet' type='text/css' />"
//"<link rel='stylesheet' type='text/css' media='screen' href='css/autosuggest.css' >"
);
$which_java = array(
"<script type='text/javascript' src='javascript/my_mapimg_functions.js'></script>",
"<script type='text/javascript'>
	changemapimgform('".$gazetteerid."','bottommapimgch','mappopcontainer');
	changeoptionlist('".$stfamid."',".$gazetteerid.",'".$stspecid."');
	changemap('".$stspecid."',".$gazetteerid.",'mappopcontainer');
</script>"
);
$title = 'Espécies na Parcela';
$body = '';
FazHeader($title,$body,$which_css,$which_java,$menu);


echo "
<div id=\"topanel\">&nbsp;$titulo</div>
<div>
<div id=\"panel\">
<form>
<select name='taxafilter' onchange=\"changeoptionlist(this.value,".$gazetteerid.", 0);\" >
<option value=''>Selecione um taxa</option>";
if ($nrz==0) {
echo "
<option selected value=''>Não há árvores para essa parcela</option>";
} 
else {
	$famid=0;
	$specid = 0;
	$genid = 0;
	while ($row = mysql_fetch_assoc($rz)) {
		if ("famid_".$row['FamiliaID']==$stfamid) {
			$txt = "selected";
		} else {
			$txt = '';
		}
		if ($row['FamiliaID']!=$famid) {
			echo "<option $txt  value=\"famid_".$row['FamiliaID']."\">".strtoupper($row['Familia'])."</option>";
		}
		$famid = $row['FamiliaID'];
echo "<option value=\"genid_".$row['GeneroID']."\">&nbsp;&nbsp;".$row['Genero']."</option>";
	}
}
echo "
</select>
</form>";
echo "
<div id=\"txtHint\"></div>
<div id=\"mapbut\"><input type='button' value='Atualizar mapa com spp selecionadas' 
onclick=\"changemap('specieslist',".$gazetteerid.",'mappopcontainer'); changemapimgform('".$gazetteerid."','bottommapimgch','mappopcontainer');\" ></div>
<div id=\"bottommapimgch\"></div>
</div>
<div id=\"mappopcontainer\"></div>
</div>
";


$which_java = array(
"<script type='text/javascript' src='javascript/myjavascripts.js'></script>");
FazFooter($which_java,$calendar=TRUE,$footer=$menu);
?>