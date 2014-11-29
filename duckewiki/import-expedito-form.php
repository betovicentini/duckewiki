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
$title = 'Importar Expedito 00';
$body = '';
FazHeader($title,$body,$which_css,$which_java,$menu);

unset($_SESSION['fieldsign']);
echo "
<br />
<table align='center' class='myformtable' cellpadding=\"7\" width='50%'>
<thead>
<tr>
<td colspan='100%' class='tabhead' >Importar Dados Método Expedito&nbsp;&nbsp;<img height=14 src=\"icons/icon_question.gif\" ";
$help = "Apenas os dados do levantamento devem ser importados por esta interface, onde cada linha corresponde ao registro de uma espécie por um observador em um intervalo de tempo. As seguintes colunas podem ser importadas:\\n\\t(a) PTID - identificador de cada ponto (nome ou número)\\n\\t(b) LOCALIDADE_ESPECIFICA - Localidade mais específica de cada ponto (nome, você será avisado se já há cadastro para a localidade, ou se cada localidade faz parte de outra localidade\\n\\t(c) DATA_LEVANTAMENTO - Data em que o ponto foi inventariado\\n\\t(d) LONGITUDE_PONTOGPS - Longitude geral para o ponto de inventário em Décimos de GRAU (S e W negativos)\\n\\t(e) LATITUDE_PONTOGPS - Longitude geral para o ponto de inventário em Décimos de GRAU (S e W negativos)\\n\\t(f) TESTEMUNHO_COLETOR - Nome do coletor do material testemunho\\n\\t(g) TESTEMUNHO_NUMBERO - Número de coleta do coletor do material testemunho\\n\\t(h) Quando o registro não tiver Material Testemunho colunas para a identificação taxonômica: FAMILIA,GENERO,ESPECIE,SUBESPECIE. NÃO INCLUIR AUTORES DE ESPECIE OU SUBESPECIE NAS COLUNAS DE NOME\\n\\t(i) OBSERVADOR - nome/abreviacao da pessoa que fez a observação\\n\\t(j) INTERVALO de tempo em que a observação foi feita períodos de 15 minutos (de 1 a 4, em geral)\\nNOTE QUE SE HOUVER MATERIAL TESTEMUNHO OS DADOS DE COLETA DEVEM JÁ ESTAR CADASTRADOS NO WIKI"; 
echo " onclick=\"javascript:alert('$help');\" /></td>
</tr>
</thead>
<tbody>";
if ($bgi % 2 == 0){$bgcolor = $linecolor2 ;} else {$bgcolor = $linecolor1 ;} $bgi++;
echo "
<tr bgcolor = '".$bgcolor."'>
<form enctype='multipart/form-data' action='import-expedito-step01.php' method='post'>
<input type='hidden' name='ispopup'  value='".$ispopup."' />
  <td style='color: #990000; font-weight:bold' >".GetLangVar('namefile')."</td>
  <td><input type='hidden' name='MAX_FILE_SIZE' value='10000000' /><input name='uploadfile' type='file' width='20' /></td>
</tr>
";
if ($bgi % 2 == 0){$bgcolor = $linecolor2 ;} else {$bgcolor = $linecolor1 ;} $bgi++;
echo "
<tr bgcolor = '".$bgcolor."'>
  <td colspan='100%' class='tdformnotes'>O arquivo para importar deve estar em formato TXT ou CSV, separado por TABULAÇÃO, quebra de linha em formato UNIX e código de fonte UTF-8. O LibreOffice/OpenOffice  permite salvar arquivos em formato CSV com essa opções</td>
</tr>
";
if ($bgi % 2 == 0){$bgcolor = $linecolor2 ;} else {$bgcolor = $linecolor1 ;} $bgi++;
echo "
<tr bgcolor = '".$bgcolor."'>
  <td colspan='100%' align='center'><input type='submit' value='".GetLangVar('nameenviar')."' class='bsubmit' /></td>
</tr>
</form>
</tbody>
</table>";
$which_java = array("<script type='text/javascript' src='javascript/myjavascripts.js'></script>",
"<!-- Create Menu Settings: (Menu ID, Is Vertical, Show Timer, Hide Timer, On Click ('all' or 'lev2'), Right to Left, Horizontal Subs, Flush Left, Flush Top) -->",
"<script type='text/javascript'>qm_create(0,false,0,500,false,false,false,false,false);</script>");
FazFooter($which_java,$calendar=FALSE,$footer=$menu);
?>