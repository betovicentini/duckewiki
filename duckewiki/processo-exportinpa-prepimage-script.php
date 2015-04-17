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
"<link href='css/geral.css' rel='stylesheet' type='text/css' />");
$which_java = array();
$title = 'Prepara imagens agora';
$body = '';
FazHeader($title,$body,$which_css,$which_java,$menu);

$arquivos = unserialize($_SESSION['arquivos']);
///AGORA QUE GEROU AS ETIQUETAS UNIR AS IMAGENS
//cria pasta onde serao salvas as imagens finais
$pathfinal = "processosimgs_".$tbname;
@mkdir("temp/".$pathfinal, 0755);
$pathetiqueta = "etiqueta_".$tbname;

if (count($arquivos)>0) {
	$amostra = $arquivos[0][0];
	$etiqueta = $arquivos[0][1];
	unset($arquivos[0]);
	$arquivos = array_values($arquivos);
	$_SESSION['arquivos']  = serialize($arquivos);
	$zz = explode("/",$_SERVER['SCRIPT_NAME']);
	$serv = $_SERVER['SERVER_NAME'];
	$returnto = $serv."/".$zz[1]."/processo-exportinpa-prepimage-script.php";
	$arqu = str_replace("_label.pdf","",$etiqueta);
		echo "
<span style='font-size: 1.2em; color: red;' >Atualizando o arquivo ".$arqu."</span>
<form  name='myform' action='../cgi-local/imagick_colaEtiqueta.php' method='get'>
  <input type='hidden' value='".$returnto."' name='returnto' />
  <input type='hidden' value='".$tbname."' name='tbname' />
  <input type='hidden' value='".$pathetiqueta."' name='pathetiqueta' />
  <input type='hidden' value='".$pathfinal."' name='pathfinal' />
  <input type='hidden' value='".$amostra."' name='amostra' />
  <input type='hidden' value='".$etiqueta."' name='etiqueta' />
  <input type='hidden' value='".$zz[1]."' name='folder' />
  <script language=\"JavaScript\">setTimeout('document.myform.submit()',1);</script>
</form>";
} 
else {
	echo "Não foram encontrados arquivos de imagens sem thumbnails<br />";
}





$which_java = array("<script type='text/javascript' src='javascript/myjavascripts.js'></script>");
FazFooter($which_java,$calendar=FALSE,$footer=$menu);
?>


