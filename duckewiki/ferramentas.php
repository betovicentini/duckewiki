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
//"<link rel='stylesheet' type='text/css' href='css/cssmenu.css' />",
"<link rel='stylesheet' type='text/css' href='css/colorbuttons.css' />"
);
$which_java = array(
//"<script type='text/javascript' src='css/cssmenuCore.js'></script>",
//"<script type='text/javascript' src='css/cssmenuAddOns.js'></script>",
//"<script type='text/javascript' src='css/cssmenuAddOnsItemBullet.js'></script>"
);
$title = 'Ferramentas';
$body = '';
FazHeader($title,$body,$which_css,$which_java,$menu);
$minwidth = '450px';
echo "
<div style='padding: 10px; width: 100%;'>
<a style=\"width: ".$minwidth .";\" href=\"#\" class=\"menuicons_azulescuro\" onclick = \"javascript:small_window('identify-batch-trees.php?ispopup=1',600,300,'Identificar um grupo de árvores com mesmo nome');\">Identificar um grupo de árvores com mesmo nome</a>
<br />
<a style=\"width: ".$minwidth .";\" href=\"#\" class=\"menuicons_azulescuro\" onclick = \"javascript:small_window('identifybyname.php?ispopup=1',600,300,'Identificação de uma espécie local');\">Identificação de uma espécie local (substituição de nome)</a>
<br />
<a style=\"width: ".$minwidth .";\" href=\"#\" class=\"menuicons_azulescuro\" onclick = \"javascript:small_window('identifybyimg-form.php?ispopup=1',800,600,'Identificação por imagens');\">Identificação de plantas de um filtro por imagens</a>
<br />
<a style=\"width: ".$minwidth .";\" href=\"#\" class=\"menuicons_azulescuro\" onclick = \"javascript:small_window('display-image-byspecies-form.php?ispopup=1',1000,700,'Compare Imagens');\">Identificação por comparação de imagens da base</a>
<br />";
//<a href=\"#\" style=\"width: ".$minwidth .";\" class=\"menuicons_amarelo\" onclick = \"javascript:small_window('batchenter_traits_form.php?ispopup=1',800,600,'Edita via tabela');\">Entra dados via tabela</a>
//<br>";
echo "
<a href=\"#\" style=\"width: ".$minwidth .";\" class=\"menuicons_amarelo\" onclick = \"javascript:small_window('traits-calculate-form.php?ispopup=1',800,600,'Calcula variável');\">Variáveis - criar por cálculo com outras variáveis</a>";
echo "<br>";
echo "<a style=\"width: ".$minwidth .";\" href=\"#\" class=\"menuicons_amarelo\" onclick = \"javascript:small_window('edit-batchoneforall-exec.php?ispopup=1&cleanssession=1',800,600,'Valor único de amostras');\">Atualiza valores para várias amostras</a>";
echo "<br>";
echo "<a style=\"width: ".$minwidth .";\" href=\"#\" class=\"menuicons_amarelo\" onclick = \"javascript:small_window('edit-batchoneforalltrees-exec.php?ispopup=1&cleanssession=1',800,600,'Valor único de plantas');\">Atualiza valores para várias plantas</a>";
echo "<br>";
echo "<a style=\"width: ".$minwidth .";\" href=\"#\" class=\"menuicons_amarelo\" onclick = \"javascript:small_window('graph-variables-form.php?ispopup=1',600,300,'Visualiza variáveis graficamente');\">Gráficos de variáveis (comparar valores entre espécies)</a>";
echo "<br>";
echo "<a style=\"width: ".$minwidth .";\" href=\"#\" class=\"menuicons_verde\" onclick = \"javascript:small_window('gps-ponto-form.php?ispopup=1',500,400,'GPS pontos - editar/cadastrar');\">GPS pontos - editar/cadastrar</a>";
echo "<br>";
echo "<a style=\"width: ".$minwidth .";\" href=\"#\" class=\"menuicons_verde\" onclick = \"javascript:small_window('gps-ponto-batchlocality.php?ispopup=1',800,600,'GPS pontos batch local');\">GPS pontos - mudar a localidade de um conjuntos de pontos</a>";
echo "<br>";
echo "<a style=\"width: ".$minwidth .";\" href=\"#\" class=\"menuicons_roxo\" onclick = \"javascript:small_window('inpa-form.php?ispopup=1',600,300,'Cadastra numeração do herbário');\">Nos. de registro do Herbário Associado à Base (INPA)</a>";
echo "<br>";
echo "<a style=\"width: ".$minwidth .";\" href=\"#\" class=\"menuicons_roxo\" onclick = \"javascript:small_window('herbarios-form.php?ispopup=1',600,300,'Registra Herbário');\">Registra o(s) herbário(s) de depósito para especímenes</a>";
echo "<br>";
echo "<a style=\"width: ".$minwidth .";\" href=\"#\" class=\"menuicons_cinza\" onclick = \"javascript:small_window('especimenes_duplicados.php?ispopup=1',900,550,'Checa por especímenes duplicados');\">Especímenes duplicados - checar e corrigir</a>";
echo "</div>";
$which_java = array("<script type='text/javascript' src='javascript/myjavascripts.js'></script>",
"<!-- Create Menu Settings: (Menu ID, Is Vertical, Show Timer, Hide Timer, On Click ('all' or 'lev2'), Right to Left, Horizontal Subs, Flush Left, Flush Top) -->",
"<script type='text/javascript'>qm_create(0,false,0,500,false,false,false,false,false);</script>");
FazFooter($which_java,$calendar=FALSE,$footer=$menu);

?>