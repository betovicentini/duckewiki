<?php
//Start session
//ini_set("memory_limit","-1");
//ini_set("mysql.allow_persistent","-1");
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

echo "projetoid:  ".$projetoid;


//CABECALHO
$menu = FALSE;

//PREPARA ARQUIVO PARA LOOP DE PROGRESSO
$pgfilename = 'temp_projdadosmeta'.substr(session_id(),0,10)."_pg.txt";
$fh = fopen("temp/".$pgfilename, 'w');
fwrite($fh,"0");
fclose($fh);
session_write_close();

$which_css = array(
"<link href='css/geral.css' rel='stylesheet' type='text/css' >");
$which_java = array(
"<script>
    function CheckProgress() {
    	var pgxhttp = new XMLHttpRequest();
		pgxhttp.onreadystatechange = function() {
			if (pgxhttp.readyState == 4 && pgxhttp.status == 200) {
				var progress = pgxhttp.responseText;
				progress = parseInt(progress, 10);
				document.getElementById(\"probar\").value = progress;
				document.getElementById('probarperc').innerHTML = 'Processando ' + progress + '%';
				if (progress<100) {
		         	CheckProgress();
		      	} else {
					document.getElementById('probarperc').innerHTML = 'Concluido ' + progress + '%';
	      	}
			}
		};
		var url = 'import-data-progress.php?filename=".$pgfilename."';
		pgxhttp.open(\"GET\", url, true);
		pgxhttp.send();        
	}
	function fazgeradados() {
		CheckProgress();
		geradados();
	}       
  function geradados() {
  		var prj = document.getElementById(\"prjid\").value; 
  		alert(prj);
  		var xhttp = new XMLHttpRequest();
		xhttp.onreadystatechange = function() {
			if (xhttp.readyState == 4 && xhttp.status == 200) {
				document.getElementById(\"resultado\").innerHTML = xhttp.responseText;
			}
		};
		var url = 'projeto-dados-metadados-script.php?pgfilename=".$pgfilename."&projetoid='+prj;
		alert(url);
		xhttp.open(\"GET\", url, true);
		xhttp.send(); 
	}
</script>",
"<style>
	#probar {
		width: 300px;
		height: 2em;
	}
	#probarperc {
		text-align: center;
		color: #000000;
	}
	#procont {
		width: 320px;
		margin: auto;
		text-align: center;
	}
	</style>"
);
$body='';
$title = 'Projeto dados e metadados';
FazHeader($title,$body,$which_css,$which_java,$menu);
echo "
<input id='prjid'  type='hidden' value='".$projetoid."' >
<div id='resultado' ></div>
<br />
<input type='button'  value='Gerar os arquivos de dados do projeto'  onclick='javascript: fazgeradados();' >
<br />
<div id='procont'>
<progress id='probar' value='0' max='100'></progress>
<div id='probarperc'></div>
</div>
";
$which_java = array(
);
FazFooter($which_java,$calendar=FALSE,$footer=$menu);
?>