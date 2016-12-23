<?php
//Start session
ini_set("memory_limit","-1");
ini_set("mysql.allow_persistent","-1");
//IMPORTA UMA TABELA QUALQUER AO MYSQL
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

$marcadorsel = trim($marcadorsel);
$marcadornovo = trim($marcadornovo);
if (empty($marcadorsel) && empty($marcadornovo)) {
	header("location: import-molecular-form.php?erro=marcador não indicado");
} else {
	if (!empty($marcadorsel)) {
		$marcador = $marcadorsel;
	} else {
		$marcador = $marcadornovo;
	}
}

$menu = FALSE;
$which_css = array(
"<link href='css/geral.css' rel='stylesheet' type='text/css' />");
$which_java = array();
$title = 'Importa sequencias moleculares';
$body = '';
$fname = $_FILES['uploadfile']['name'];
$fileuploaded = $_FILES['uploadfile']['tmp_name'];
if (!empty($fname)) {
FazHeader($title,$body,$which_css,$which_java,$menu);

$ext = explode(".",$fname);
$ll = count($ext)-1;
$extens = $ext[$ll];
unset($ext[$ll]);
$fn = implode(".",$ext);
$importdate = date("Y-m-d");
$newfilename = $fn."_Importado_".$_SESSION['userid']."_".$importdate.".".$extens;

$vv = implode("",$ext);
$vv = str_replace(" ", "", $vv);
$vv = str_replace("-", "", $vv);
$vv = str_replace("&", "", $vv);
$vv = str_replace("/", " ", $vv);
$vv = str_replace("\\", " ", $vv);
$tbname = "temp_".$vv."_".$_SESSION['userid'];

move_uploaded_file($_FILES["uploadfile"]["tmp_name"],"uploads/molecular/".$newfilename);
$fileuploaded = "uploads/molecular/".$newfilename;

//if ($lixo==234567) {
$handle = @fopen($fileuploaded, 'r');
if ($handle) {
	$i=0;
	$erros = array();
    while (!feof($handle)) {
        $buffer = fgets($handle);
        $bb = substr($buffer,0,1);
        if ($bb=='>' && $i>0) {
        		if ($wikiid>0) {
					$qz = "SELECT * FROM Especimenes WHERE EspecimenID=".$wikiid;
					$rz = mysql_query($qz,$conn);
					$nrz = mysql_numrows($rz);
					if ($nrz>0) {
						//echo "<br />FOUND WIKI ID";
						$ncbiid = @$nomearr[1];
						$label = @$nomearr[2];
						$best = @$nomearr[3];
						$arrayofvalues = array(
'EspecimenID' => $wikiid,
'Sequencia' => $seq,
'Marcador' => $marcador,
'NCBI' => $ncbiid,
'LABEL' => $label,
'BEST' => $best
);
						$newseq = InsertIntoTable($arrayofvalues,'MolecularID','MolecularData',$conn);
						//echopre($arrayofvalues);
					} else {
						$erro = $nome."  - ERRO: valor WikiEspecimenID não encontrado na base";
						//print($erro);
						$erros[] = $erro;
					}
			} 
			$nome = str_replace(">","",$buffer);
			$nomearr = explode("_",$nome);
			$wikiid = $nomearr[0]+0;
			$seq = '';
        } else {
        	$seq = $seq.$buffer;
        }
        $i++;
    }
    fclose($handle);
    if (count($erros)>0) {
    	echo "Os seguintes erros foram encontrados:<br />";
    	foreach ($erros as $er) {
    		echo $er."<br />";
    	}
    } else {
        echo "<br /><span style='color: red; font-size:1.5em;'>As sequências foram importadas com sucesso!</span>";
    }
}
$which_java = array("<script type='text/javascript' src='javascript/myjavascripts.js'></script>");
FazFooter($which_java,$calendar=FALSE,$footer=$menu);
} else {
	header("location: import-molecular-form.php?erro=arquivo nao informado");
}

?>
