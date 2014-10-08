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
if((!isset($uuid) || 
	(trim($uuid)=='')) && count($listsarepublic)==0) {
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

//INICIA O CONTEUDO//
if (!empty($tableref) && ($idd+0)>0) {
	$tbname = "temp_lst_".$tableref."_".$idd;
	$seepop = 1;
} else {
	$seepop = 0;
	$tbname = "checklist_all";
}
$vars = array(
'censoid'  =>  $censoid
);
function emptyvar($var)
{
    // returns true whether the var is not empty
    return(!empty($var));
}
$vars = array_filter($vars,"emptyvar");
$varstxt = '';
$ii=0;
foreach ($vars as $kk => $vv) {
   if ($ii==0) {
		$varstxt .= $kk."=".$vv;
	} else {
		$varstxt .= "&".$kk."=".$vv;
	}
	$ii++;
}

//echo "checklist_species_script.php?".$varstxt;
//echopre($vars);

//PREPARA O CABECALHO, CSS E JAVASCRIPTS
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
      $.get('checklist_species_progress.php', { t: time, tbname: '".$tbname."'}, function (data) {
          var progress = parseInt(data, 10);
          document.getElementById('probar').value = progress;
        if (progress < 100) {
          document.getElementById('probarperc').innerHTML = 'Processando ' + progress + '%';
          setTimeout(function() { CheckProgress();}, 1);
    	} else {
          document.getElementById('probarperc').innerHTML = progress + '%' +' CONCLUIDO';
          document.getElementById('loaderimg').style.visibility= 'hidden';
          window.location = 'checklist_species_savefile.php?seepop=".$seepop."&tbname=".$tbname."&tableref=".$tableref."&ispopup=1&idd=".$idd."'; 
    	}
      });
	}
    $.ajax(
            {
                type: 'GET',
                url: 'censo-plantas-table-save.php',
                data: '".$varstxt."',
                async: true,
                success:
                    function (data) {
                            document.getElementById('probarperc').innerHTML = data;
                            document.getElementById('countdown').style.visibility= 'hidden';
                            document.getElementById('coffeeid').style.visibility= 'hidden';
                            window.location = 'censo-plantas-table-grid.php?censoid=".$censoid."'; 
                    }
            }); 
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
$title = 'Prepara Checklist Species Table';
FazHeader($title,$body,$which_css,$which_java,$menu);
echo "
<br />
<div id='procont'>
<progress id='probar' value='0' max='100'></progress>
<img id='loaderimg' src='icons/loadingcircle.gif'  height='30' />
<div id='probarperc'>Aguarde! Preparando table observações</div>
<img id='coffeeid' src='icons/animatedcoffee.gif'  height='50' />
</div>";

$which_java = array(
//"<script src=\"javascript/countup_jquery/countup/jquery.countup.js\"></script>",
//"<script src=\"javascript/countup_jquery/js/script.js\"></script>"

//"<script type='text/javascript' src='javascript/myjavascripts.js'></script>",
//"<!-- Create Menu Settings: (Menu ID, Is Vertical, Show Timer, Hide Timer, On Click ('all' or 'lev2'), Right to Left, Horizontal Subs, Flush Left, Flush Top) -->",
//"<script type='text/javascript'>qm_create(0,false,0,500,false,false,false,false,false);</script>"
);
FazFooter($which_java,$calendar=FALSE,$footer=$menu);
?>