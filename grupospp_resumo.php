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
$title = 'Grupos de Espécies';
$body = '';
FazHeader($title,$body,$which_css,$which_java,$menu);


$tablename = "temp_".$_SESSION['username']."_grupossppresumo.txt";
$fh = fopen("temp/".$tablename, 'w') or die("nao foi possivel gerar o arquivo");
//fetch table header
$header = array('GRUPO_NOME',
				'Familia',
				'Genero',
				'Especie',
				'InfraEspecie',
				'NARV');
$hh = implode("\t",$header);
$lin = trim($hh)."\n";
fwrite($fh,$lin);

$query = "SELECT * FROM  Tax_SpeciesGroups ORDER BY GroupName";
$res = mysql_query($query,$conn);	
while($row = mysql_fetch_assoc($res)) {
	$gruponame = $row['GroupName'];
	$grupotipo = $row['FormalGroup'];
	$grupoautor = $row['PesoaID'];
	$sp_membros = $row['Membros'];
	$formaref = $row['Referencias'];

	$arraylist = explode(";",$sp_membros);
	$especies = array();
	$infraespecies = array();
	foreach ($arraylist as $key => $value) {
			$dado = explode("|",$value);
			if (trim($dado[0])=='especie') {
				$especies = array_merge((array)$especies,(array)array("'".$dado[1]."'"));			
			}
			if (trim($dado[0])=='infraspecies') {
				$infraespecies = array_merge((array)$infraespecies,(array)array("'".$dado[1]."'"));			
			}
	}
	$specs = implode(",",$especies);
	$infspecs = implode(",",$infraespecies);
	$qq = "SELECT '".$gruponame."' AS GRUPO_NOME,Familia,Genero,Especie,InfraEspecie,count(*) as NARV FROM Plantas as pl JOIN Identidade as idd USING(DetID) LEFT JOIN Tax_InfraEspecies as infsp ON infsp.InfraEspecieID=idd.InfraEspecieID LEFT JOIN Tax_Especies as sp ON sp.EspecieID=idd.EspecieID LEFT JOIN Tax_Generos as gg ON gg.GeneroID=idd.GeneroID LEFT JOIN Tax_Familias as ff ON ff.FamiliaID=idd.FamiliaID WHERE";
	if (count($especies)>0) { $qq .=" idd.EspecieID IN (".$specs.")";}
	if (count($infraespecies)>0 && count($especies)>0) { $qq .=" OR idd.InfraEspecieID IN (".$infspecs.")"; }
	if (count($infraespecies)>0 && count($especies)==0) { $qq .=" idd.InfraEspecieID IN (".$infspecs.")"; }
	$qq .= " GROUP BY '".$gruponame."',Familia,Genero,Especie,InfraEspecie";
	$rr = @mysql_query($qq,$conn);
	$nr = @mysql_numrows($rr);
	if ($nr>0) {
		while($rw = mysql_fetch_assoc($rr)) {
			$hh = implode("\t",$rw);
			$lin = trim($hh)."\n";
			fwrite($fh,$lin);
		}
	} else {
		$lin = $gruponame."\tNenhuma espécie/arvore encontrada nesse grupo\n";
		fwrite($fh,$lin);
	}
}

fclose($fh);
//////////////


$tablename2 = "temp_".$_SESSION['username']."_grupossppresumo2.txt";
$fh = fopen("temp/".$tablename2, 'w') or die("nao foi possivel gerar o arquivo");
//fetch table header
$header = array('GRUPO_NOME',
				'NUM_SP',
				'NUM_INFSP',
				'NARV');
$hh = implode("\t",$header);
$lin = trim($hh)."\n";
fwrite($fh,$lin);

$query = "SELECT * FROM  Tax_SpeciesGroups ORDER BY GroupName";
$res = mysql_query($query,$conn);	
while($row = mysql_fetch_assoc($res)) {
	$gruponame = $row['GroupName'];
	$grupotipo = $row['FormalGroup'];
	$grupoautor = $row['PesoaID'];
	$sp_membros = $row['Membros'];
	$formaref = $row['Referencias'];

	$arraylist = explode(";",$sp_membros);
	$especies = array();
	$infraespecies = array();
	foreach ($arraylist as $key => $value) {
			$dado = explode("|",$value);
			if (trim($dado[0])=='especie') {
				$especies = array_merge((array)$especies,(array)array("'".$dado[1]."'"));			
			}
			if (trim($dado[0])=='infraspecies') {
				$infraespecies = array_merge((array)$infraespecies,(array)array("'".$dado[1]."'"));			
			}
	}
	$specs = implode(",",$especies);
	$infspecs = implode(",",$infraespecies);
	$nsp = count($especies);
	$ninfsp = count($infraespecies);
	
	$qq = "SELECT '".$gruponame."' AS GRUPO_NOME,'".$nsp."' as NUM_SP,
				'".$ninfspninfsp."' as NUM_INFSP,count(*) as NARV FROM Plantas as pl JOIN Identidade as idd USING(DetID) LEFT JOIN Tax_InfraEspecies as infsp ON infsp.InfraEspecieID=idd.InfraEspecieID LEFT JOIN Tax_Especies as sp ON sp.EspecieID=idd.EspecieID LEFT JOIN Tax_Generos as gg ON gg.GeneroID=idd.GeneroID LEFT JOIN Tax_Familias as ff ON ff.FamiliaID=idd.FamiliaID WHERE";
	if (count($especies)>0) { $qq .=" idd.EspecieID IN (".$specs.")";}
	if (count($infraespecies)>0 && count($especies)>0) { $qq .=" OR idd.InfraEspecieID IN (".$infspecs.")"; }
	if (count($infraespecies)>0 && count($especies)==0) { $qq .=" idd.InfraEspecieID IN (".$infspecs.")"; }
	$rr = @mysql_query($qq,$conn);
	$nr = @mysql_numrows($rr);
	if ($nr==1) {
		$rw = mysql_fetch_assoc($rr);
		$hh = implode("\t",$rw);
		$lin = trim($hh)."\n";
		fwrite($fh,$lin);
	} else {
		$lin = $gruponame."\tNenhuma espécie/arvore encontrada nesse grupo\n";
		fwrite($fh,$lin);
	}
}

fclose($fh);


echo "
<br />
<table class='myformtable' cellpadding='5' align='center' width='70%'>
<thead>
<tr><td colspan='2'>Resultados</td></tr>
</thead>
<tbody>
<tr>
  <td><a href='temp/".$tablename."' target='_blank'>Baixar resumo 1</td>
</tr>
<tr>
  <td><a href='temp/".$tablename2."' target='_blank'>Baixar resumo 2</td>
</tr>
<tr>
  <td colspan='100%'><hr></td>
</tr>
<tr>
  <td colspan='100%' class='tdformnotes'>*Os arquivos são separados por TABULAÇÃO, tem quebras de linha em formato Unix, e o encoding de caracteres é UTF-8. O planilha do openoffice é melhor que o Excel para abrir o arquivo porque reconhece automaticamente o encoding dos caracteres (UTF-8). No Excel pode ter erros de grafia.</td>
</tr>
</tbody>
</table>";
$which_java = array("<script type='text/javascript' src='javascript/myjavascripts.js'></script>",
"<!-- Create Menu Settings: (Menu ID, Is Vertical, Show Timer, Hide Timer, On Click ('all' or 'lev2'), Right to Left, Horizontal Subs, Flush Left, Flush Top) -->",
"<script type='text/javascript'>qm_create(0,false,0,500,false,false,false,false,false);</script>");
FazFooter($which_java,$calendar=FALSE,$footer=$menu);
?>