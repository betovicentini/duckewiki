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
$vars = array('tbname'  =>  $tbname,
'idd'  =>  $idd,
'tableref'  =>  $tableref,
'update'  =>  $update);
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


//PREPARA ARQUIVO PARA LOOP DE PROGRESSO
$qqz = "DROP TABLE `temp_progspp".$tbname."`";
@mysql_query($qqz,$conn);
$qqz = "CREATE TABLE `temp_progspp".$tbname."`  (`percentage` INT(10) NOT NULL) ENGINE = InnoDB CHARACTER SET utf8 COLLATE utf8_general_ci";
mysql_query($qqz,$conn);
$qqz = "INSERT INTO `temp_progspp".$tbname."` (`percentage`) VALUES ('0');";
mysql_query($qqz,$conn);

//PREPARA O CABECALHO, CSS E JAVASCRIPTS
$which_css = array(
"<link href='css/geral.css' rel='stylesheet' type='text/css' >",
"<link rel='stylesheet' type='text/css' href='css/cssmenu.css' >",
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
                url: 'checklist_species_script.php',
                data: '".$varstxt."',
                async: true,
                success:
                    function (data) {
                            document.getElementById('probarperc').innerHTML = 'Salvando...';
                            document.getElementById('countdown').style.visibility= 'hidden';
                            document.getElementById('coffeeid').style.visibility= 'hidden';
                            if (data=='Concluido') {
                                window.location = 'checklist_species_savefile.php?seepop=".$seepop."&tbname=".$tbname."&tableref=".$tableref."&ispopup=1&idd=".$idd."'; 
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
$title = 'Prepara Checklist Species Table';
FazHeader($title,$body,$which_css,$which_java,$menu);
echo "
<br />
<div id='procont'>
<progress id='probar' value='0' max='100'></progress>
<img id='loaderimg' src='icons/loadingcircle.gif'  height='30' />
<div id='probarperc'>Aguarde!</div>
<img id='coffeeid' src='icons/animatedcoffee.gif'  height='50' />
</div>";

$which_java = array();
FazFooter($which_java,$calendar=FALSE,$footer=$menu);
?>