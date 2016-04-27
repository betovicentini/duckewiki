<?php
//Start session
ini_set("memory_limit","-1");
ini_set("mysql.allow_persistent","-1");

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
$menu = FALSE;
$which_css = array(
"<link href='css/geral.css' rel='stylesheet' type='text/css' />",
"<link href='css/jquery-ui.css' rel='stylesheet' type='text/css' />"
);
$which_java = array(
);
$body='';
$title = 'Script Teste Executa';
FazHeader($title,$body,$which_css,$which_java,$menu);

$temptab = "temp_nir_export7tb6j7aehu";
$export_filename = "temp_nir_export".substr(session_id(),0,10).".csv";
if (!isset($run)) {
$qz = "SELECT * FROM ".$temptab;
$res = mysql_query($qz,$conn);
$nrecs = mysql_numrows($res);
//PREPARA O CABEÇALHO
$qz = "SELECT * FROM ".$temptab."  LIMIT 0,1";
$res = mysql_query($qz,$conn);
$count = mysql_num_fields($res);
$header = '';
for ($i = 0; $i < $count; $i++){
	$ffil = mysql_field_name($res, $i);
	$header .=  '"' . $ffil . '"' . "\t";
}
$row = mysql_fetch_assoc($res);
$fn = $row['FileName'];
$tbn ="uploads/nir/";
$fnn = $tbn.$fn;
$fop = @fopen($fnn, 'r');
$hhed = array();
while (($data = fgetcsv($fop, 10000, "\t")) !== FALSE) {
	$vv = explode(",",$data[0]);
	$wlen =  round($vv[0],2);
	$wlen = "X".$wlen;
	$hhed[] = $wlen;
}
fclose($fnn);
$i = 1;
$ni = count($hhed);
foreach ($hhed as $cab) {
	if ($i<$ni) {
		$header .=  '"' . $cab . '"' . "\t";
	} else {
		$header .=  '"' . $cab . '"';
	}
	$i++;
}
$header .= "\n";
$fh = fopen("temp/".$export_filename, 'w') or die("nao foi possivel gerar o arquivo");
fwrite($fh, $header);
fclose($fh);
$qz =- "ALTER TABLE ".$temptab." ADD PRIMARY KEY (`SPECTRUM_ID`)";
@mysql_query($qz,$conn);

$qz = "SELECT * FROM ".$temptab;
$res = mysql_query($qz,$conn);

$nrecs = mysql_numrows($res);
$totalrecs = $nrecs;
$stepsize = 100;
$run = 0;
} 
//AGORA ACRESCENTA OS DADOS
$fh = fopen("temp/".$export_filename, 'a') or die("nao foi possivel gerar o arquivo");
$qz = "SELECT * FROM ".$temptab."  LIMIT ".$run.",".$stepsize;
$res = mysql_query($qz,$conn);
$nrecs = mysql_numrows($res);
$step=0;
while ($row = mysql_fetch_assoc($res)) {
		$fn = $row['FileName'];
		echo $fn."<br />";
		$tbn ="uploads/nir/";
		$fnn = $tbn.$fn;
		$fop = @fopen($fnn, 'r');
		$nirdata = array();
		while (($data = fgetcsv($fop, 10000, "\t")) !== FALSE) {
					$vv = explode(",",$data[0]);
					$valor = round($vv[1],30);
					$wlen =  round($vv[0],2);
					$wlen = "X".$wlen;
					$nirdata[$wlen] = $valor;
		}
		fclose($fnn);
		//JUNTA OS DADOS DAS AMOSTRAS COM OS DADOS DE ABSOBANCIA DO ARQUIVO
		$todosvalores = array_merge((array)$row,(array)$nirdata);
		
		//SALVA OS VALORES NO ARQUIVO
		$line = '';
		$nff  = count($todosvalores);
		$nii = 1;
		foreach($todosvalores as $value){
			if(!isset($value) || $value == ""){
				$value = "\t";
			} 
			else {
				//important to escape any quotes to preserve them in the data.
				$value = str_replace('"', '""', $value);
					if ($nii<$nff) {
						$value = '"' . $value . '"' . "\t";
					} else {
						$value = '"' . $value . '"';
					}
			}
			$nii++;
			$line .= $value;
		}
		$lin = trim($line)."\n";
		fwrite($fh, $lin);
}
fclose($fh);

if ($nrecs>0) {
echo "Salvando arquivo ".$run."  of  ".$totalrecs;
echo "
<form name='myform' action='ScriptTeste.php' method='post'>
<input type='hidden'  value=".($run+1+$stepsize)."  name='run' >
<input type='hidden'  value=".$stepsize."  name='stepsize' >
<input type='hidden'  value=".$totalrecs."  name='totalrecs' >

    <script language=\"JavaScript\">setTimeout('document.myform.submit()',0.0001);</script>
</form>";
} else {
echo "Terminei";
}

$which_java = array(
"<script type='text/javascript' src='javascript/myjavascripts.js'></script>"
);
FazFooter($which_java,$calendar=FALSE,$footer=$menu);
?>