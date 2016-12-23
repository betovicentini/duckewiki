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
if ($ispopup==1) {
	$menu = FALSE;
} else {
	$menu = TRUE;
}

$vars = array(
'update' => $update,
'tbname' => "checklist_plots"
);

function emptyvar($var)
{
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


//PREPARA ARQUIVO PARA LOOP DE PROGRESSO

$qqz = "DROP TABLE `temp_progplot`";
@mysql_query($qqz,$conn);
$qqz = "CREATE TABLE `temp_progplot`  (`percentage` INT(10) NOT NULL)";
mysql_query($qqz,$conn);
$qqz = "INSERT INTO `temp_progplot` (`percentage`) VALUES ('0');";
mysql_query($qqz,$conn);

//PREPARA O CABECALHO, CSS E JAVASCRIPTS
$which_css = array(
"<link href='css/geral.css' rel='stylesheet' type='text/css' >",
//"<link rel='stylesheet' type='text/css' href='css/cssmenu.css' >",
"<link rel='stylesheet' type='text/css' href='css/jquery-ui.css' />"
);
$which_java = array(
"<script src=\"javascript/jquery-1.10.2.js\"></script>",
"<script src=\"javascript/jquery-ui.js\"></script>",
"<script>
    //start your long-running process
    function CheckProgress() {
      var time = new Date().getTime();
      $.get('checklist_plots_progress.php', { t: time }, function (data) {
        var progress = parseInt(data, 10);
          document.getElementById('probar').value = progress;
        if (progress < 100) {
          document.getElementById('probarperc').innerHTML = 'Processando ' + progress + '%';
          setTimeout(function() { CheckProgress();},1);
    	} else {
          document.getElementById('probarperc').innerHTML = progress + '%' +' CONCLUIDO';
          document.getElementById('loaderimg').style.visibility= 'hidden';
          window.location = 'checklist_plots_savefile.php?ispopup=1';
    	}
      });
	}   
    $.ajax(
            {
                type: 'GET',
                url: 'checklist_plots_script_final.php',
                data: '".$varstxt."',
                async: true,
                success:
                    function (data) {
                            document.getElementById('probarperc').innerHTML = 'Salvando...';
                            document.getElementById('countdown').style.visibility= 'hidden';
                            document.getElementById('coffeeid').style.visibility= 'hidden';
                            if (data=='Concluido') {
                                window.location = 'checklist_plots_savefile.php?ispopup=1'; 
                            }
                    }
            }); 	
    CheckProgress();
	</script>
	<style>

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
$title = 'Prepara Checklist Plots Table';
FazHeader($title,$body,$which_css,$which_java,$menu);
echo "
<br />
<div id='procont'>
<progress id='probar' value='0' max='100'></progress>
<img id='loaderimg' src='icons/loadingcircle.gif'  height='30' />
<div id='probarperc'>Aguarde!</div>
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