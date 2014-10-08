<?php
session_start();
//INCLUI FUNCOES PHP E VARIAVEIS
include "functions/HeaderFooter.php";
include "functions/MyPhpFunctions.php";

//FAZ A CONEXAO COM O BANCO DE DADOS
$lang = $_SESSION['lang'];
$dbname = $_SESSION['dbname'];
$conn = ConectaDB($dbname);

//////PEGA E LIMPA VARIAVEIS
$ppost = cleangetpost($_POST,$conn);
@extract($ppost);
$arval = $ppost;

$gget = cleangetpost($_GET,$conn);
@extract($gget);

//CHECA SE O USUARIO TEM PERMISSAO
$uuid = cleanQuery($_SESSION['userid'],$conn);
if(!isset($uuid) || 
	(trim($uuid)=='')) {
		if (count($listsarepublic)==0) {
		header("location: access-denied.php");
		} else {
		   header("location:  export-plotdata-request.php?gazetteerid=".$gazetteerid);
		}
	exit();
} else {
	$acclevel = $_SESSION['accesslevel'];
	if ($acclevel=='visitor') {
	  header("location:  export-plotdata-request.php?gazetteerid=".$gazetteerid);
	}
}

//echopre($gget);
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

$export_filename = "dadosParcela_".$gazetteerid.".csv";
$export_filename_metadados = "dadosParcela_".$gazetteerid."_metadados.csv";

if (file_exists("temp/".$export_filename) && file_exists("temp/".$export_filename_metadados)) {
	$title = 'Baixar dados exportados!';
	$body = '';
	FazHeader($title,$body,$which_css,$which_java,$menu);
	echo "
<br />
<table class='myformtable' cellpadding='5' align='center' width='90%'>
<thead>
<tr><td>Baixar dados de parcelas</td></tr>
</thead>
<tbody>
<tr><td class='tdsmallbold'>Existem arquivos de exportação prontos para esta parcela!</td></tr>
<tr><td class='tdformnotes'>O arquivo de dados contém dados das árvores da parcela (tag, taxonomia, etc) e inclui DAP de todos os censos existentes para a localidade e, se houver, altura da planta e status!</td></tr>
<tr><td class='tdsmallbold'>Os arquivos foram gerados em ".date ("F d Y H:i:s.", filemtime("temp/".$export_filename))."</td></tr>
<tr><td><a href=\"download.php?file=temp/".$export_filename."\">Baixar dados</a></td></tr>
<tr><td><a href=\"download.php?file=temp/".$export_filename_metadados."\">Baixar metadados</a></td></tr>
<tr><td><hr></td></tr>
<tr><td class='tdformnotes'>*Os arquivos estão separados por TABULAÇÃO, tem quebras de linha em formato Unix, e o encoding de caracteres é UTF-8. O planilha do openoffice é melhor que o Excel para abrir o arquivo porque reconhece automaticamente o encoding dos caracteres (UTF-8). No Excel pode ter erros de grafia.</td></tr>
<tr>
  <td align='center'>
     <form action='export-plotdata_form.php' name='myform' method='post'>
      <input type='hidden' name='ispopup' value='".$ispopup."'>
      <input type='hidden' name='gazetteerid' value='".$gazetteerid."'>
      <input type='submit' value='Atualizar esses arquivos!' class='bsubmit' />
     </form>
  </td>
</tr>
</tbody>
</table>";
$which_java = array(
"<script type='text/javascript' src='javascript/myjavascripts.js'></script>",
"<!-- Create Menu Settings: (Menu ID, Is Vertical, Show Timer, Hide Timer, On Click ('all' or 'lev2'), Right to Left, Horizontal Subs, Flush Left, Flush Top) -->",
"<script type='text/javascript'>qm_create(0,false,0,500,false,false,false,false,false);</script>"
);
FazFooter($which_java,$calendar=FALSE,$footer=$menu);
} else {
		header("location: export-plotdata_form.php?ispopup=1&gazetteerid=".$gazetteerid);
}
?>