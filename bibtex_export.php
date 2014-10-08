<?php
//Start session
session_start();
//INCLUI FUNCOES PHP E VARIAVEIS
include "functions/HeaderFooter.php";
include "functions/MyPhpFunctions.php";
require_once "functions/BibTex.php";


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
if (!isset($enviado)) {
$which_java = array(
"<script type='text/javascript' >
    function GetValuesFromParent()
    {
    var valor = self.opener.window.document.getElementById('resultado').value;
    document.getElementById('pegouvalor').value = valor;
    document.getElementById('inicioform').submit();
    return true;
    }    
</script>"
);
$body = " onload='javascript:GetValuesFromParent();' ";
$title = 'Exportar BibTex';
FazHeader($title,$body,$which_css,$which_java,$menu);
echo "
<form id='inicioform'  method='post' action='bibtex_export.php' >
<input type='hidden' id='pegouvalor' name='bibids'  value='$bibids' />
<input type='hidden' name='enviado' value='1'/>
</form>
";
$which_java = array("<script type='text/javascript' src='javascript/myjavascripts.js'></script>");
FazFooter($which_java,$calendar=FALSE,$footer=$menu);
}
else {
	header("Content-Type:text/plain");
	$qchh = "SELECT * FROM BiblioRefs";
	if (!empty($bibids)) {
		$arbib = explode(';',$bibids);
		$qchh .= " WHERE ";
		$b=0;
		foreach ($arbib as $bb) {
			if ($b==0) {
			$qchh .= " BibID=".$bb;
			} else {
			$qchh .= " OR BibID=".$bb;
			}
			$b++;
		}
	}
	$rches = mysql_query($qchh, $conn);
	//$fnnol = 'temp_exportBib_'.$uuid.'bib';
	//$fh = fopen("temp/".$fnnol, 'wb');
	while ($rchwo = mysql_fetch_assoc($rches)) {
		$strold = $rchwo['BibRecord'];
		$strold = trim(preg_replace('[\\\r\\\n|\\\r|\\\n|\\\t]', '', $strold));
		$strold .= "\n";
		echo $strold;
		//fwrite($fh, $strold);
	}
	//fclose($fh);
	//echo "<a href='temp/".$fnnol."'  >Baixar arquivo Bib</a>";
}



?>
