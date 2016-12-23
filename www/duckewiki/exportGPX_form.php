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


//////PEGA E LIMPA VARIAVEIS
$ppost = cleangetpost($_POST,$conn);
@extract($ppost);
$arval = $ppost;

$gget = cleangetpost($_GET,$conn);
@extract($gget);

if ($filtroid>0 && $oque>0 && !empty($oque)) {
//CABECALHO
$menu = FALSE;

//echopre($gget);
//echo "export-plotdata_script.php?idd=".$idd."&tableref=".$tableref;

//PREPARA ARQUIVO PARA LOOP DE PROGRESSO
$qqz = "DROP TABLE `temp_exportGPX.".substr(session_id(),0,10)."`";
mysql_query($qqz,$conn);
$qqz = "CREATE TABLE `temp_exportGPX.".substr(session_id(),0,10)."`  (`percentage` INT(10) NOT NULL) ENGINE = InnoDB CHARACTER SET utf8 COLLATE utf8_general_ci";
mysql_query($qqz,$conn);
$qqz = "INSERT INTO `temp_exportGPX.".substr(session_id(),0,10)."` (`percentage`) VALUES ('0');";
mysql_query($qqz,$conn);

//unset($_SESSION['expPlotData']);
//DEFINE NOME DO ARQUIVO A SER GERADO NA PASTA temp/
$export_filename = "dadosGPX_".$filtroid."_".substr(session_id(),0,10).".gpx";



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
      $.get('exportGPX_progress.php', { t: time }, function (data) {
          var progress = parseInt(data, 10);
          document.getElementById('probar').value = progress;
        if (progress < 100) {
          document.getElementById('probarperc').innerHTML = 'Processando ' + progress + '%';
          setTimeout(function() { CheckProgress();}, 1);
        } else {
          document.getElementById('probarperc').innerHTML = progress + '%' +' CONCLUIDO';
          document.getElementById('loaderimg').style.visibility= 'hidden';
          window.location = 'exportGPX_download.php?export_filename=".$export_filename."';
    }
      });
   }
    //start your long-running process 
    $.ajax(
            {
                type: 'GET',
                url: 'exportGPX_script.php',
                data: 'filtroid=".$filtroid."&oque=".$oque."',
                async: true,
                success:
                    function (data) {
                        document.getElementById('probarperc').innerHTML = data ;
                        document.getElementById('loaderimg').style.visibility= 'hidden';
                        document.getElementById('coffeeid').style.visibility= 'hidden';
                        if (data=='CONCLUIDO') {
                           window.location = 'exportGPX_download.php?export_filename=".$export_filename."';
                        }
                    }
            });
   CheckProgress();         
</script>",
"<style>
#probar {
  width: 300px;
  height: 3em;
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
$title = 'Exportando dados de parcela!';
FazHeader($title,$body,$which_css,$which_java,$menu);
//echo "exportGPX_script.php?filtroid=".$filtroid."&oque=".$oque."&export_filename=".$export_filename;
echo "
<br />
<div id='procont'>
<progress id='probar' value='0' max='100'></progress>
<img id='loaderimg' src='icons/loadingcircle.gif'  height='30' />
<div id='probarperc'>Aguarde!</div>
<img id='coffeeid' src='icons/animatedcoffee.gif'  height='50' />
</div>
";
//DEFINE JAVASCRIPTS PARA O PE (FOOTER)
$which_java = array(
//"<script type='text/javascript' src='javascript/myjavascripts.js'></script>",
//"<!-- Create Menu Settings: (Menu ID, Is Vertical, Show Timer, Hide Timer, On Click ('all' or 'lev2'), Right to Left, Horizontal Subs, Flush Left, Flush Top) -->",
//"<script type='text/javascript'>qm_create(0,false,0,500,false,false,false,false,false);</script>"
);
//FAZ O PE DA PAGINA
FazFooter($which_java,$calendar=FALSE,$footer=$menu);
} else {
	header("location: exportGPX.php");
}
?>