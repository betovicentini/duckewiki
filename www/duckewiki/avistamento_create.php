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
$title = 'Cria Avistamento';
FazHeader($title,$body,$which_css,$which_java,$menu);

echo "
<br />
<table align='left' class='myformtable' cellpadding=3 cellspacing=0 width=50%>
<thead>
<tr ><td colspan=100%>Método do avistamento</td></tr>
</thead>
<tbody>
<tr>
<td colspan=100% class='tdformnotes'>
<br />
O Método do Avistamento consiste em caminhar pela floresta e registrar ao longo do caminho as ocorrências de alguma(s) espécie(s) focais. Isso se faz basicamente caminhando com um GPS ligado e marcando pontos onde as plantas são encontradas. 
<br />
<br />
Em cada ponto de avistamento o observador pode anotar os seguintes dados:
<ul>
<li>Descritores do ambiente do ponto - (definição de um habitat local que se associa ao ponto, incluindo lista de espécies/taxa)</li>
<li>Estimativa do raio de visibilidade do ponto (pode ser estimado a posteriori com base nas distâncias observadas)</li>
<li>Nome das espécies focais</li>
<li>Número de indivíduos de cada espécie no raio e altura média dos indivíduos; e/ou a distância de cada indivíduo isoladamente ao ponto de observação e a altura individual de cada planta por espécie</li>
</ul>
<br />O processo de entrada de dados está estruturado da seguinte forma:
<ul>
<li><a href='avistamento_gps.php'>Definição das trilhas de busca</a> - importa dados GPS (gpx) e define o horário de início e fim de cada período de busca. Isso visa limitar os dados espaciais ao percurso percorrido durante a busca e eliminar outros dados no arquivo exportado do GPS. Você pode escolher o nome para cada trilha identificada no arquivo.</li>
<li>Seleção das espécies focais
<li>Depois, para cada trilha definida você poderá registrar observações para cada ponto (waypoint) marcado no percurso.</li>
</ul>
</td>
</tr>
</tbody>
</table>";

$which_java = array("<script type='text/javascript' src='javascript/myjavascripts.js'></script>",
"<!-- Create Menu Settings: (Menu ID, Is Vertical, Show Timer, Hide Timer, On Click ('all' or 'lev2'), Right to Left, Horizontal Subs, Flush Left, Flush Top) -->",
"<script type='text/javascript'>qm_create(0,false,0,500,false,false,false,false,false);</script>");
FazFooter($which_java,$calendar=FALSE,$footer=$menu);

?>