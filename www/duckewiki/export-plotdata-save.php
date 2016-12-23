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
$candownloadpublic = 0;
$uuid = cleanQuery($_SESSION['userid'],$conn);
//echopre($gget);
//echopre($_SESSION);
if(!isset($uuid) || 
	(trim($uuid)=='')) {
		if (count($listsarepublic)==0) {
			header("location: access-denied.php");
		} else {
		   //header("location:  export-plotdata-request.php?idd=".$idd."&tableref=".$tableref);
		   $candownloadpublic = 1;
		}
	//exit();
} else {
	$acclevel = $_SESSION['accesslevel'];
	if ($acclevel=='visitor') {
	  //header("location:  export-plotdata-request.php?idd=".$idd."&tableref=".$tableref);
		   $candownloadpublic = 1;
	}
}
//echo $candownloadpublic."  aqui";
//echopre($gget);
//CABECALHO
$menu = FALSE;
$which_css = array(
"<link href='css/geral.css' rel='stylesheet' type='text/css' />"
);
$which_java = array(
);

//DEFINE NOME DOS ARQUIVOS E OBJETOS
$export_filename = "dadosParcela_".$idd.$tableref.".csv";
$export_filename_metadados = "dadosParcela_".$idd.$tableref."_metadados.txt";
$export_filename_public = "dadosParcela_".$idd.$tableref."_public.csv";
$export_filename_censos = "dadosParcela_".$idd.$tableref."_censos.csv";
$export_filename_censospub = "dadosParcela_".$idd.$tableref."_censospub.csv";

if (file_exists("temp/".$export_filename) && file_exists("temp/".$export_filename_metadados)) {
	$title = 'Baixar dados exportados!';
	$body = '';
	FazHeader($title,$body,$which_css,$which_java,$menu);
	echo "
<br />
<table class='myformtable' cellpadding='5' align='center' width='90%'>
<thead>
<tr><td>Baixar dados de monitoramento</td></tr>
</thead>
<tbody>
<tr><td class='tdsmallbold'>Existem arquivos de exportação prontos para esta localidade!</td></tr>
<tr><td class='tdformnotes'>O arquivo de dados contém medições/atributos (geralmente DAP) de plantas marcadas, em uma data (monitoramento), organizadas em censos!</td></tr>
<tr><td class='tdsmallbold'>Os arquivos foram gerados em ".date ("F d Y H:i:s.", filemtime("temp/".$export_filename))."</td></tr>";
echo "
<tr><td><a target=\"_SELF\" href=\"temp/".$export_filename_metadados."\">Baixar metadados</a></td></tr>";
if ($candownloadpublic==0) {
echo "
<tr><td><a href=\"download.php?file=temp/".$export_filename."\">Baixar dados completos</a></td></tr>";
	if (file_exists("temp/".$export_filename_public)) {
		echo "<tr><td><a href=\"download.php?file=temp/".$export_filename_public."\">Baixar versão de acesso aberto</a></td></tr>";
	}
	//echo "<tr><td><a href=\"download.php?file=temp/".$export_filename_censos."\">Censos</a></td></tr>";
} else {
	$myurl ="export-plotdata-request.php?idd=".$idd."&tableref=".$tableref;
echo "
<tr><td><input type=button value='Solicitar dados completos'  onclick =\"javascript:small_window('".$myurl."&type=completo',900,300,'Solicitar dados completos');\" /></td></tr>
<tr><td><input type=button value='Baixar dados de acesso aberto'  onclick =\"javascript:small_window('".$myurl."&type=free',900,300,'Baixar dados');\" />&nbsp;<img height=\"16\" src=\"icons/icon_question.gif\" ";
$help = "Existem dados livres, de acesso aberto, que podem ter menos informações que os dados completos para os quais é necessário comunicação direta com o responsável pelos dados. Para baixar dados livres você precisa apenas informar seu email válido, no qual você receberá uma mensagem com o link para baixar os dados";
echo " onclick=\"javascript:alert('".$help."');\" /></td></tr>";
}
echo "
<tr><td><hr></td></tr>
<tr><td class='tdformnotes'>Os arquivos estão separados por TABULAÇÃO, tem quebras de linha em formato UNIX, e o encoding de caracteres é UTF-8. Pronto para abrir no R. Para editores de planilhas recomendamos usar Open-  ou Br-  ou Libre- Office que é a melhor alternativa que Excel porque o controle do encoding dos caracteres e quebra de linhas é maior pelo usuário.</td></tr>
<tr>
  <td >
     <form action='export-plotdata_form.php' name='myform' method='post'>
      <input type='hidden' name='idd' value='".$idd."'>
      <input type='hidden' name='tableref' value='".$tableref."'>
      <input type='submit' value='Atualizar esses arquivos!' />&nbsp;<img height=\"16\" src=\"icons/icon_question.gif\" ";
$help = "Irá extrair os dados da base e gerar arquivos para baixar. É um processo lento que depende da quantidade de dados de censos de plantas associados à localidade.";
echo " onclick=\"javascript:alert('".$help."');\" />
     </form>
  </td>
</tr>
</tbody>
</table>";
$which_java = array(
"<script type='text/javascript' src='javascript/myjavascripts.js'></script>"
);
FazFooter($which_java,$calendar=FALSE,$footer=$menu);
} else {
header("location: export-plotdata_form.php?idd=".$idd."&tableref=".$tableref);
}
?>