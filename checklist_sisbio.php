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

//CABECALHO
$ispopup=1;
if ($ispopup==1) {
	$menu = FALSE;
} else {
	$menu = TRUE;
}
$title = '';
$which_css = array(
"<link href='css/geral.css' rel='stylesheet' type='text/css' >",
"<link rel='stylesheet' type='text/css' href='css/cssmenu.css' >",
"<link rel='stylesheet' type='text/css' href='css/jquery-ui.css' />"
);
$which_java = array(
"<script src=\"javascript/jquery-1.10.2.js\"></script>",
"<script src=\"javascript/jquery-ui.js\"></script>",
"<script>
    //start your long-running process 
	function SaveFile() {
      var time = new Date().getTime();
      $.get('checklist_sisbio_savefile.php', { t: time }, function (data) {
          window.location = 'checklist_view_generic.php';
      });
	}   
    $.ajax(
            {
                type: 'GET',
                url: 'checklist_sisbio_script.php',
                data: { filtro: '".$filtro."'},
                async: true,
                success:
                    function (data) {
                        if (data>0) {
                              document.getElementById('probarperc').innerHTML = 'Concluído';
                              document.getElementById('loaderimg').style.visibility= 'hidden';
                              /* do something - your long process is finished */
                              SaveFile();
                        } else {
                             document.getElementById('probarperc').innerHTML = \"Não foram encontradas ESPECIMENES nesse filtro. Nada para exportar!<br ><input type='button' onclick='window.close();'  value='Fechar'>\";
                        }
                    }
            });
</script>",
"<style>
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
$title = 'SISBIO Relatório';
FazHeader($title,$body,$which_css,$which_java,$menu);
echo "
<br />
<div id='procont'>
<img id='loaderimg' src='icons/ajax-loader.gif'  />
<div id='probarperc'>Aguarde!  $filtro</div>
</div>";
$which_java = array(
//"<script type='text/javascript' src='javascript/myjavascripts.js'></script>",
//"<!-- Create Menu Settings: (Menu ID, Is Vertical, Show Timer, Hide Timer, On Click ('all' or 'lev2'), Right to Left, Horizontal Subs, Flush Left, Flush Top) -->",
//"<script type='text/javascript'>qm_create(0,false,0,500,false,false,false,false,false);</script>"
);
FazFooter($which_java,$calendar=FALSE,$footer=$menu);
?>