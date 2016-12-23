<?php
session_start();
//INCLUI FUNCOES PHP E VARIAVEIS
include "functions/HeaderFooter.php";
include "functions/MyPhpFunctions.php";


//FAZ A CONEXAO COM O BANCO DE DADOS
//$lang = $_SESSION['lang'];
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

//echopre($gget);
//CRIA UM ARQUIVO PARA SALVAR O PROGRESSO

//$fh = fopen("temp/".$pgfilename, 'w');
//fwrite($fh, '0');
//fclose($fh);
//session_write_close();


//echo $saoplantas."  aqui 1";
if (!isset($saoplantas) || $saoplantas!=1) {
		//echo $saoplantas."  aqui 2";
		$qt = "SELECT EspecimenID FROM FiltrosSpecs WHERE FiltroID=".$filtroid." AND EspecimenID>0";
		$rup = mysql_query($qt,$conn);
		$rwu = mysql_numrows($rup);
		if ($rwu>0) {
			$inserido=0;
			$ntotal = $rwu;
			$idx =1;
			while ($row = mysql_fetch_assoc($rup)) {
				$qs = "SELECT * FROM ProjetosEspecs WHERE EspecimenID=".$row['EspecimenID']." AND ProjetoID=".$projetoid;
				$rsp = mysql_query($qs,$conn);
				$nsp = mysql_numrows($rsp);
				if ($nsp==0) {
				    $qins = "INSERT INTO  `ProjetosEspecs` (`EspecimenID`,`ProjetoID`,`AddedBy`,`AddedDate`) VALUES (".$row['EspecimenID'].",".$projetoid.", ".$uuid.", CURRENT_DATE())";
			    	//echo $qins."<br />";
				    $rp = mysql_query($qins);
				    if ($rp) {
			    		$inserido++;
				    }
				}
				$fh = fopen("temp/".$pgfilename, 'w');
				$perc = round(($idx/$ntotal)*99,0);
				$idx++;
				fwrite($fh, $perc);
				fclose($fh);
				session_write_close();

			}
		}
	} elseif ($saoplantas==1) {
		$qt = "SELECT PlantaID FROM FiltrosSpecs WHERE FiltroID=".$filtroid." AND PlantaID>0";
		//echo $qt."<br >";
		$rup = mysql_query($qt,$conn);
		$rwu = mysql_numrows($rup);
		if ($rwu>0) {
			$inserido=0;
			$ntotal = $rwu;
			$idx =1;
			while ($row = mysql_fetch_assoc($rup)) {
				$qs = "SELECT * FROM ProjetosEspecs WHERE PlantaID=".$row['PlantaID']." AND ProjetoID=".$projetoid;
				$rsp = mysql_query($qs,$conn);
				$nsp = mysql_numrows($rsp);
				if ($nsp==0) {
				    $qins = "INSERT INTO  `ProjetosEspecs` (`PlantaID`,`ProjetoID`,`AddedBy`,`AddedDate`) VALUES (".$row['PlantaID'].",".$projetoid.", ".$uuid.", CURRENT_DATE())";
			    //echo $qins."<br />";
				    $rp = mysql_query($qins);
				    if ($rp) {
			    		$inserido++;
				    }
				}
				$fh = fopen("temp/".$pgfilename, 'w');
				$perc = round(($idx/$ntotal)*99,0);
				$idx++;
				fwrite($fh, $perc);
				fclose($fh);
				session_write_close();
			}
		}
		
}
if ($inserido>0) {
$resposta = $inserido." registros foram importados para o projeto com sucesso";
} else {
$resposta = "Houver um erro ou não foram encontrados registros para importar";
}
echo $resposta;
$fh = fopen("temp/".$pgfilename, 'w');
$perc = 100;
fwrite($fh, $perc);
fclose($fh);
session_write_close();

?>