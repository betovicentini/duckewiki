<?php
//Start session
session_start();
//INCLUI FUNCOES PHP E VARIAVEIS
include "functions/HeaderFooter.php";
include "functions/MyPhpFunctions.php";

//FAZ A CONEXAO COM O BANCO DE DADOS
//$lang = $_SESSION['lang'];
$dbname = $_SESSION['dbname'];
$conn = ConectaDB($dbname);

//////PEGA E LIMPA VARIAVEIS
$ppost = cleangetpost($_POST,$conn);
@extract($ppost);
$arval = $ppost;
$gget = cleangetpost($_GET,$conn);
@extract($gget);

//$fnn = 'testando.txt';
//$fh = fopen("temp/".$fnn, 'w');
//$stringData = 'monografiaid='.$monografiaid.'    '.$_GET['modelo'];
//fwrite($fh, $stringData);
//fclose($fh);

if ($monografiaid>0) {
    $nn = json_decode($_GET['modelo']);
    $nvars = count($nn-> items);
	$arrayofvalues = array('ModeloDescricoes' => $_GET['modelo'], 'ModeloSimbolos' => $_GET['simbolos']);
	$upp = CompareOldWithNewValues('Monografias','MonografiaID',$monografiaid,$arrayofvalues,$conn);
	if (!empty($upp) && $upp>0) { 
		$updated = UpdateTable($monografiaid,$arrayofvalues,'MonografiaID','Monografias',$conn);
		if ($updated) {
		   echo $nvars;
		} else {
			echo 'nao mudou';
		}
	}
} else {
	echo 0;
}
session_write_close();
?>
