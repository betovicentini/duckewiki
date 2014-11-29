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

unset($_SESSION['expPlotData']);
$tableref = "templixo";
$idd =0 ;
$title = '';
$which_css = array(
"<link href='css/geral.css' rel='stylesheet' type='text/css' >",
"<link rel='stylesheet' type='text/css' href='css/cssmenu.css' >",
"<link rel='stylesheet' href='http://code.jquery.com/ui/1.10.1/themes/base/jquery-ui.css' />",
"<link rel='stylesheet' href='javascript/countup_jquery/css/styles.css' />",
"<link rel='stylesheet' href='javascript/countup_jquery/countup/jquery.countup.css' />"
);
$which_java = array(
"<script src=\"javascript/jquery-1.10.2.js\"></script>",
"<script src=\"javascript/jquery-ui.js\"></script>",
"<script>
    $.ajax(
            {
                type: 'GET',
                url: 'checklist_species_script.php',
                data: { tableref: '".$tableref."', idd: ".$idd."},
                async: true,
                success:
                    function (data) {
                            document.getElementById('probarperc').innerHTML = 'Salvando...';
                            document.getElementById('countdown').style.visibility= 'hidden';
                       /*   do something - your long process is finished  */
                            window.location = 'checklist_species_savefile.php?tableref=".$tableref."&ispopup=1&idd=".$idd."'; 
                          /*  SaveFile(); */
                    }
            });        
</script>",
"<style>
	#probar {
		width: 300px;
		height: 2em;
	}
	#probarperc {
		text-align: center;
		color: #000000;
		font-size: 25pt;
		font-color: 'red';
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
<!--- <progress id='probar' value='0' max='100'></progress> 
<img id='loaderimg' src='icons/ajax-loader.gif'  />--->
<div id=\"countdown\"></div>
<div id='probarperc'>Aguarde! Pode demorar!</div>
</div>
";
$which_java = array(
"<script src=\"javascript/countup_jquery/countup/jquery.countup.js\"></script>",
"<script src=\"javascript/countup_jquery/js/script.js\"></script>"

//"<script type='text/javascript' src='javascript/myjavascripts.js'></script>",
//"<!-- Create Menu Settings: (Menu ID, Is Vertical, Show Timer, Hide Timer, On Click ('all' or 'lev2'), Right to Left, Horizontal Subs, Flush Left, Flush Top) -->",
//"<script type='text/javascript'>qm_create(0,false,0,500,false,false,false,false,false);</script>"
);
FazFooter($which_java,$calendar=FALSE,$footer=$menu);
?>