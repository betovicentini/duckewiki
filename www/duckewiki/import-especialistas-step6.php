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


$menu = FALSE;
$which_css = array(
"<link href='css/geral.css' rel='stylesheet' type='text/css' />");
$which_java = array();
$title = 'Importar Especialistas Passo 05';
$body = '';
FazHeader($title,$body,$which_css,$which_java,$menu);

//echopre($ppost);
//FINALIZANDO A IMPORTACAO
$qn = "SELECT * FROM ".$tbname;
$rn = mysql_query($qn,$conn);
$erro=array();
$generos = array();
$acertos = 0;
while($row = mysql_fetch_assoc($rn)) {
	$qt = "SELECT * FROM Pessoas WHERE PessoaID=".$row[$tbprefix.'PessoaID'];
	$rt = mysql_query($qt,$conn);
	$rwt = mysql_fetch_assoc($rt);
	$pess = $rwt['Abreviacao'];
	$pessemail = trim($rwt['Email']);
	if (empty($pessemail)) {
		$qtt = "UPDATE Pessoas SET Email='".$row['emailcol']."' WHERE PessoaID=".$row[$tbprefix.'PessoaID'];
		@mysql_query($qtt,$conn);
	}
	$qf = "SELECT * FROM Tax_Familias WHERE FamiliaID=".$row[$tbprefix.'FamiliaID'];
	$rf = mysql_query($qf,$conn);
	$rwf = mysql_fetch_assoc($rf);
	$famtxt = $rwf['Familia'];
	$arrayofvalues = array(
'Especialista' => $row[$tbprefix.'PessoaID'],
'EspecialistaTXT' => $pess,
'Familias' => $famtxt,
'FamiliaID' => $row[$tbprefix.'FamiliaID'],
'Herbarium' => $row[$herbariocol],
'Email'  => $row[$emailcol]);
	//checkexisting
	$qes = "SELECT * FROM Especialistas WHERE Especialista=".$row[$tbprefix.'PessoaID']." AND FamiliaID=".$row[$tbprefix.'FamiliaID'];
	$res = mysql_query($qes,$conn);
	$nr = mysql_numrows($res);
	if ($nr==0) {
		$newspec = InsertIntoTable($arrayofvalues,'EspecialistaID','Especialistas',$conn);
		if (!$newspec) {
			$erro[] = $row[$especialistacol]." [".$row[$familiacol]."]  não cadastrado ERRO 1";
		} else {
			$acertos++;
			$ok=1;
		}
	} else {
		$rwe = mysql_fetch_assoc($res);
		$newspec = $rwe['EspecialistaID'];
		$erro[] = $row[$especialistacol]." [".$row[$familiacol]."]  já estava cadastrado ERRO 2";
		$ok=1;
	}
	//checa os generos
	if ($ok==1) {
		$gens = explode(";",$row[$tbprefix.'GenerosIDS']);
		$gens2 = explode(";",$row[$generocol]);
		$ng = count($gens);
		if ($ng>0) {
			foreach($gens as $kk => $vv) {
				$vv = $vv+0;
				$vv2 = $gens2[$kk];
				if ($vv>0) {
				$qgg = "SELECT * FROM Tax_Generos WHERE GeneroID=".($vv+0);
				$rgg = mysql_query($qgg,$conn);
				$rwgg = mysql_fetch_assoc($rgg);
				if (($rwgg['EspecialistaID']+0)==0) {
					$ugg = "UPDATE Tax_Generos SET EspecialistaID=".$newspec." WHERE GeneroID=".$vv;
					$urr = mysql_query($ugg,$conn);
					if (!$urr) {
					$erro[] = $ugg." não realizou ";
					}
				} else {
					$erro[] = $row[$especialistacol]." [".$row[$familiacol]."] genero ".$rwgg['Genero']." já tinha especialista anotado";
				}
				} else {
					//$erro[] = $vv2." [".$vv."] não encontrado!";
				}
			}
		}
	}
}
if ($acertos>0) {
			echo "
<br />
<table cellpadding=\"5\" class='success'>
  <tr><td class='tdsmallbold' align='center'>O cadastro foi feito com sucesso para ".$acertos." especialistas!</td></tr>
</table>
<br />";
}
if (count($erro)>0) {
echo "
<br />
  <table cellpadding=\"7\" align='center' class='erro'>
  <tr ><td class='tdformnotes' align='center'>Os seguintes erros foram encontrados:</td></tr>";
  foreach($erro as $er) {
  echo "
<tr ><td class='tdformnotes' align='center'>".$er."</td></tr>";
  }
echo "  
  </table>
<br />";
}

$which_java = array(
"<script type='text/javascript' src='javascript/myjavascripts.js'></script>");
FazFooter($which_java,$calendar=FALSE,$footer=$menu);

?>