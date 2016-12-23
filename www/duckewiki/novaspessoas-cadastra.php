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
$menu = FALSE;
$which_css = array(
"<link href='css/geral.css' rel='stylesheet' type='text/css' />"
);
$which_java = array();
$title = 'Novas pessoas';
$body = '';
FazHeader($title,$body,$which_css,$which_java,$menu);

$qq = "SELECT DISTINCT  `".$abreviacao."`,`".$prenome."`,`".$sobrenome."`, `".$segundonome."` FROM `".$tbname."` WHERE `".$colname."`=0";
//echo $qq."<br />";
$res = @mysql_query($qq,$conn);
$nres = mysql_numrows($res);
$erro=0;
//if ($lixo==10) {
if ($nres>0) {
	while($row = mysql_fetch_assoc($res)) {
		$arrayofvalues = array(
			'Prenome' => $row[$prenome],
			'Sobrenome' => $row[$sobrenome],
			'SegundoNome' => $row[$segundonome],
			'Abreviacao' => $row[$abreviacao]
		);
		$check = "SELECT * FROM Pessoas WHERE Abreviacao='".$row[$abreviacao]."'";
		$checkres = mysql_query($check,$conn);
		$ncheckres = mysql_numrows($checkres);
		if ($ncheckres==0) {
			$newspec = InsertIntoTable($arrayofvalues,'PessoaID','Pessoas',$conn);
			session_write_close();
		}
	}
}
$qq = "UPDATE `".$tbname."` SET `".$colname."`=checarpessoaimport(`".$abreviacao."`,`".$prenome."`,`".$sobrenome."`) WHERE `".$colname."`=0";
@mysql_query($qq,$conn);

$qq = "SELECT DISTINCT  `".$abreviacao."`,`".$prenome."`,`".$sobrenome."`, `".$segundonome."` FROM `".$tbname."` WHERE `".$colname."`=0";
//echo $qq."<br />";
$res = @mysql_query($qq,$conn);
$nres = mysql_numrows($res);
if ($nres==0) {
	$txt = "Corrigido";
} 
else {
	$txt = "Erro";
}
//flush();
	echo "
<input type='button' value='Finalizar'  onclick=\"javascript:changebutton('".$buttonidx."','".$txt."');\" />
";
//}
//<form >
 // <script language=\"JavaScript\">
   // setTimeout( function() { changebutton('".$buttonidx."','".$txt."');},0.0001);
  //</script>
//</form>";

	
$which_java = array(
"<script type='text/javascript' src='javascript/myjavascripts.js'></script>");
FazFooter($which_java,$calendar=FALSE,$footer=$menu);

?>