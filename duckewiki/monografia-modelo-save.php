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
$ppost = @cleangetpost($_POST,$conn);
@extract($ppost);
$arval = $ppost;
$gget = @cleangetpost($avars,$conn);
@extract($gget);

if (count($gget)>count($ppost)) {
	$avars = $_GET;
} else {
	$avars = $_POST;
}
//$fnn = 'testando.txt';
//$fh = fopen("temp/".$fnn, 'w');
//$stringData = 'monografiaid='.$monografiaid."\n";
//echopre($avars['modelo']);
//$md = json_encode($avars['modelo'], JSON_UNESCAPED_UNICODE);
//echo $md."<br >";
//$stringData .= $md;
//fwrite($fh, $stringData);
//fclose($fh);
 
$omodelo = json_encode($avars['modelo'], JSON_UNESCAPED_UNICODE);
$lixo=1;
if ($lixo>0) {
if ($monografiaid>0) {
    $nn = json_decode($omodelo);
    $nvars = count($nn-> items);
    if ($listaespecs>0) {
      $arrayofvalues = array('ModeloListaEspecimenes' => $omodelo, 'ModeloSimbolosEspecimenes' => $avars['simbolos']);
    } else {
		$arrayofvalues = array('ModeloDescricoes' => $omodelo, 'ModeloSimbolos' => $omodelo);
	}
	$upp = CompareOldWithNewValues('Monografias','MonografiaID',$monografiaid,$arrayofvalues,$conn);
	if (!empty($upp) && $upp>0) { 
		$updated = UpdateTable($monografiaid,$arrayofvalues,'MonografiaID','Monografias',$conn);
		if ($updated) {
		   echo $nvars;
		} else {
			echo 'nao mudou';
		}
	}
} 
else {
	echo 0;
}
}
session_write_close();
?>
