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
$ispopup=1;
if ($ispopup==1) {
	$menu = FALSE;
} else {
	$menu = TRUE;
}
$which_css = array(
"<link href='css/geral.css' rel='stylesheet' type='text/css' />",
"<link rel='stylesheet' type='text/css' href='css/cssmenu.css' />",
"<link rel='stylesheet' type='text/css' href='css/colorbuttons.css' />"
);
$which_java = array(
"<script type='text/javascript' src='css/cssmenuCore.js'></script>",
"<script type='text/javascript' src='css/cssmenuAddOns.js'></script>",
"<script type='text/javascript' src='css/cssmenuAddOnsItemBullet.js'></script>"
);
$title = 'Ferramentas Administrativas';
$body = '';
FazHeader($title,$body,$which_css,$which_java,$menu);
$minwidth = '500px';
echo "
<div style='padding: 10px; width: 100%;'>
<a style=\"width: ".$minwidth .";\" href=\"#\" class=\"menuicons_cinza\" onclick = \"javascript:small_window('checklist_specimens.php?update=1&ispopup=1',600,400,'');\">Checklist Especímenes - atualiza tabela</a>";
echo "<br>";
echo "<a style=\"width: ".$minwidth .";\" href=\"#\" class=\"menuicons_cinza\" onclick = \"javascript:small_window('checklist_species_form.php?update=1&ispopup=1',600,200,'');\">Checklist Espécies - atualiza tabela</a>";
echo "<br>";
echo "<a style=\"width: ".$minwidth .";\" href=\"#\" class=\"menuicons_cinza\" onclick = \"javascript:small_window('checkllist_plantas.php?update=1&ispopup=1',600,400,'');\">Checklist Plantas - atualiza tabela</a>";
echo "<br>";
echo "<a style=\"width: ".$minwidth .";\" href=\"#\" class=\"menuicons_cinza\" onclick = \"javascript:small_window('checklist_plots.php?update=1&ispopup=1',600,400,'');\">Checklist Plots - atualiza tabela</a>";
echo "<br>";
echo "<a style=\"width: ".$minwidth .";\" href=\"#\" class=\"menuicons_azulescuro\" onclick = \"javascript:small_window('images_checkthumbs.php?ispopup=1',400,300,'');\">Gera thumbnails/baixa resolução para imagens que não tem</a>";
echo "<br>";
echo "<a style=\"width: ".$minwidth .";\" href=\"#\" class=\"menuicons_amarelo\" onclick = \"javascript:small_window('droptemptables.php?ispopup=1',350,300,'Apaga Tabelas temp');\">Limpa Banco de Dados (remove tabelas temp_*)</a>";
echo "<br>";
echo "<a style=\"width: ".$minwidth .";\" href=\"#\" class=\"menuicons_verde1\" onclick = \"javascript:small_window('updateQueryTables.php?ispopup=1',400,300,'');\">Atualiza tabelas de busca (localidade e taxonomia) (Semi-deprecado)</a>";
echo "<br>";
echo "<a href=\"#\" style=\"width: ".$minwidth .";\" class=\"menuicons_verde1\" onclick = \"javascript:small_window('create_mysql_functions.php?ispopup=1',250,200,'Funções MYSQL');\">Instala funções MYSQL</a>";
echo "<br>";
echo "<a style=\"width: ".$minwidth .";\" href=\"#\" class=\"menuicons_verde1\" onclick = \"javascript:small_window('updateLinkPlantasEspecimenes.php?ispopup=1',800,200,'Checa e atualiza link entre plantas e especímenes');\">Checa/atualiza link entre plantas e especímenes</a>";
echo "<br>";
echo "<a style=\"width: ".$minwidth .";\" href=\"#\" class=\"menuicons_branco\" onclick = \"javascript:small_window('config_db.php?ispopup=1',850,600,'');\">Configurações</a>";
echo "<br>";
echo "<a style=\"width: ".$minwidth .";\" href=\"#\" class=\"menuicons_branco\" onclick = \"javascript:small_window('usuario-form.php?ispopup=1',600,400,'');\">Edita/Adiciona Usuários</a>";
echo "<br>";
echo "<a style=\"width: ".$minwidth .";\" href=\"#\" class=\"menuicons_branco\" onclick = \"javascript:small_window('UnificaEspecies.php?ispopup=1',600,400,'');\">Unifica Especies</a>";
echo "<br>";
echo "<a style=\"width: ".$minwidth .";\" href=\"#\" class=\"menuicons_branco\" onclick = \"javascript:small_window('formulario-fix.php?ispopup=1',600,400,'');\">Ajusta Formulários</a>";
echo "<br>";
echo "<a style=\"width: ".$minwidth .";\" href=\"#\" class=\"menuicons_azul\" onclick = \"javascript:small_window('alterMyisam2InnoDB.php?ispopup=1',400,300,'');\">Converte as tabelas do DB de MyISAM para InnoDB (se precisar!)</a>";
echo "<br>";
echo "<a style=\"width: ".$minwidth .";\" href=\"#\" class=\"menuicons_azul\" onclick = \"javascript:small_window('updateSetRelations.php?ispopup=1',400,300,'');\">Cria relações InnoDB</a>";
echo "
</div>";
$which_java = array("<script type='text/javascript' src='javascript/myjavascripts.js'></script>",
"<!-- Create Menu Settings: (Menu ID, Is Vertical, Show Timer, Hide Timer, On Click ('all' or 'lev2'), Right to Left, Horizontal Subs, Flush Left, Flush Top) -->",
"<script type='text/javascript'>qm_create(0,false,0,500,false,false,false,false,false);</script>");
FazFooter($which_java,$calendar=FALSE,$footer=$menu);

?>