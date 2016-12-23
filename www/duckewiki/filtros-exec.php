<?php
//Start session
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
if(!isset($uuid) ||  (trim($uuid)=='')) {
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
//INICIA O CONTEUDO//


//PREPARA ARQUIVO PARA LOOP DE PROGRESSO
$qqz = "DROP TABLE  `temp_filtro_".substr(session_id(),0,5)."`";
@mysql_query($qqz,$conn);
$qqz = "CREATE TABLE  `temp_filtro_".substr(session_id(),0,5)."` (`percentage` INT(10) NOT NULL) ENGINE = InnoDB CHARACTER SET utf8 COLLATE utf8_general_ci";
mysql_query($qqz,$conn);
$qqz = "INSERT INTO  `temp_filtro_".substr(session_id(),0,5)."` (`percentage`) VALUES ('0');";
mysql_query($qqz,$conn);

//PREPARA O CABECALHO, CSS E JAVASCRIPTS
$which_css = array(
"<link href='css/geral.css' rel='stylesheet' type='text/css' >",
"<link rel='stylesheet' type='text/css' href='css/jquery-ui.css' />"
);
$tbname = "temp_filtroPERFORM_".substr(session_id(),0,10)."_pl";
$which_java = array(
"<script src=\"javascript/jquery-1.10.2.js\"></script>",
"<script src=\"javascript/jquery-ui.js\"></script>",
"<script>
    function CheckProgress() {
      var time = new Date().getTime();
      $.get('filtros_progress.php', { t: time }, function (data) {
          var progress = parseInt(data, 10);
          document.getElementById('probar').value = progress;
        if (progress < 100) {
          document.getElementById('probarperc').innerHTML = 'Processando ' + progress + '%';
          setTimeout(function() { CheckProgress();}, 1);
        } else {
            document.getElementById('probarperc').innerHTML = progress + '%' +' CONCLUIDO';
            window.location = 'filtros-save.php?tbname=".$tbname."&ispopup=1';
        }
      });
  }
    //start your long-running process 
    $.ajax(
            {
                type: 'GET',
                url: 'filtros-perform.php',
                async: true,
                success:
                    function (data) {
                        document.getElementById('loaderimg').style.visibility= 'hidden';
                        document.getElementById('coffeeid').style.visibility= 'hidden';
                        var final = String(data);
                        if (data!='NADA') {
                            document.getElementById('probarperc').innerHTML = 'CONCLUIDO';
                            window.location = 'filtros-save.php?tbname=".$tbname."&ispopup=1';
                        } else {
                            document.getElementById('probarperc').innerHTML = 'NADA ENCONTRADO';
                            document.getElementById('closebutt').style.visibility= 'visible';
                        }
                    }
            });
   CheckProgress();
</script>",
"<style>
#probar {
  width: 300px;
  height: 1.5em;
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
$title = 'Prepara a execução de um filtro';
FazHeader($title,$body,$which_css,$which_java,$menu);
echo "
<br />
<div id='procont'>
<progress id='probar' value='0' max='100'></progress>
<img id='loaderimg' src='icons/loadingcircle.gif'  height='30' />
<div id='probarperc'>Aguarde!</div>
<img id='coffeeid' src='icons/animatedcoffee.gif'  height='50' />
<input id='closebutt' type='button' style='visibility: hidden;'  value='Fechar'  onclick='javascript: window.close();' >
</div>";

$which_java = array();
FazFooter($which_java,$calendar=FALSE,$footer=$menu);
?>