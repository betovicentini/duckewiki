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

//CABECALHO
$menu = FALSE;

//echo "GET VARIABLES";
//echopre($gget);
//echo "POST VARIABLES";
//echopre($ppost);
//echo "SESSION VARIABLES";
//echopre($_SESSION);
$_SESSION['destvararray'] = serialize($ppost);

//PREPARA ARQUIVO PARA LOOP DE PROGRESSO

$tempfile = "temp_exportespecimenes".$_SESSION['userid']."_".substr(session_id(),0,10);
$qqz = "DROP TABLE `".$tempfile."`";
mysql_query($qqz,$conn);
$qqz = "CREATE TABLE `".$tempfile."`  (`percentage` INT(10) NOT NULL DEFAULT 0) ENGINE = InnoDB CHARACTER SET utf8 COLLATE utf8_general_ci";
@mysql_query($qqz,$conn);
$qqz = "INSERT INTO `".$tempfile."` (`percentage`) VALUES ('0');";
@mysql_query($qqz,$conn);

$title = '';
$which_css = array(
"<link href='css/geral.css' rel='stylesheet' type='text/css' >",
"<link rel='stylesheet' type='text/css' href='css/jquery-ui.css' />"
);
$which_java = array(
"<script src=\"javascript/jquery-1.10.2.js\"></script>",
"<script src=\"javascript/jquery-ui.js\"></script>",
"<script>
    function CheckProgress() {
      var time = new Date().getTime();
      $.get('export-especimendata-progress.php', { t: time }, function (odado) {
        //var progress = parseInt(odado, 10);
        var progress = odado+0;
        document.getElementById('probar').value = progress;
        if (progress < 100) {
          document.getElementById('probarperc').innerHTML = 'Processando ' + progress + '%';
          setTimeout(function() { CheckProgress();}, 100);
        } 

      });
	}
    //start your long-running process 
    $.ajax(
            {
                type: 'GET',
                url: 'export-especimendata-script.php',
                //data: { ".$datatxt."},
                async: true,
                success:
                    function (data) {
                        //document.getElementById('probarperc').innerHTML = data ;
                        document.getElementById('loaderimg').style.visibility= 'hidden';
                        document.getElementById('coffeeid').style.visibility= 'hidden';
                        window.location = 'export-especimendata-save.php?resultado='+data;
                    }
            });
   CheckProgress();         
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
$title = 'Exportando dados de Especímenes!';
FazHeader($title,$body,$which_css,$which_java,$menu);
echo "
<br />
<div id='procont'>
<progress id='probar' value='0' max='100'></progress>
<img id='loaderimg' src='icons/loadingcircle.gif'  height='30' />
<div id='probarperc'>Aguarde!</div>
<img id='coffeeid' src='icons/animatedcoffee.gif'  height='50' />
</div>
";
FazFooter($which_java,$calendar=FALSE,$footer=$menu);
?>