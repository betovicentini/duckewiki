<?php
session_start();
//INCLUI FUNCOES PHP E VARIAVEIS
include "functions/HeaderFooter.php";
include "functions/MyPhpFunctions.php";

//FAZ A CONEXAO COM O BANCO DE DADOS
$lang = $_SESSION['lang'];
$dbname = $_SESSION['dbname'];
$conn = ConectaDB($dbname);

//////PEGA E LIMPA VARIAVEIS
$ppost = cleangetpost($_POST,$conn);
@extract($ppost);
$arval = $ppost;

$gget = cleangetpost($_GET,$conn);
@extract($gget);

//CABECALHO
$ispopup=1;
$menu = FALSE;

//ACESSOS AOS TABS COM DADOS (E PUBLICO OU NAO, DEFINIDO EM CONFIGURACOES)
$uuid = cleanQuery($_SESSION['userid'],$conn);
if (($uuid+0)==0) {
$species = 0;
$specimens = 0;
$plantas = 0;
$plots = 0;
if ($listsarepublic['species'] == 'on') {
  $species = 1;
} 
if ($listsarepublic['specimenes'] == 'on') {
$specimens = 1;
}
if ($listsarepublic['plantas'] == 'on') {
$plantas = 1;
}
if ($listsarepublic['plots'] == 'on') {
$plots=1;
}
} else {
$species = 1;
$specimens = 1;
$plantas = 1;
$plots = 1;
}
//echo $species+$specimens+$plantas+$plots." Aqui<br >";
//CHECA SE O USUARIO TEM PERMISSAO
if(($species+$specimens+$plantas+$plots)==0) {
	header("location: access-denied.php");
	exit();
} else {
	$acclevel = $_SESSION['accesslevel'];
}

//echopre($_SESSION);

unset($_SESSION['checklistarray']);
$arratosend = array();
$which_css = array(
"<link href='css/geral.css' rel='stylesheet' type='text/css' />"
);
$which_java = array(
"<script src=\"javascript/jquery-1.10.2.js\"></script>",
"<script src=\"javascript/jquery-ui.js\"></script>",
"<script>
    function saveUserSpeciesFile() {
      var time = new Date().getTime();
      $.get('checklist_species_savefile.php', { t: time, tbname: 'checklist_all' }, function (data) {
         if (data=='CONCLUIDO') {
          document.getElementById('probarperc').innerHTML = 'CONCLUIDO ESPECIES';
          }
      });
      return 1;
	}
    function saveUserSpecimensFile() {
      var time = new Date().getTime();
      $.get('checklist_specimens_save.php', { t: time, tbname: 'checklist_speclist' }, function (data) {
         if (data=='CONCLUIDO') {
          document.getElementById('probarperc').innerHTML = 'CONCLUIDO ESPECIMENES!';
          }
      });
      return 1;
	}

    function saveUserPlantasFile() {
      var time = new Date().getTime();
      $.get('checkllist_plantas_save.php', { t: time, tbname: 'checklist_pllist' }, function (data) {
         if (data=='CONCLUIDO') {
          document.getElementById('probarperc').innerHTML = 'CONCLUIDO PLANTAS!';
          }
      });
      return 1;
	}
    function saveUserPlotsFile() {
      var time = new Date().getTime();
      $.get('checklist_plots_savefile.php', { t: time, tbname: 'checklist_plots' }, function (data) {
         if (data=='CONCLUIDO') {
          document.getElementById('probarperc').innerHTML = 'CONCLUIDO PLOTS!';
          }
      });
      return 1;
	}
	function runall(species,specs,plants,plots){
	    if (species==1) { 
		  var sp = saveUserSpeciesFile();  
		} else { var sp=1;}
		if (specs==1) {
		  var spec = saveUserSpecimensFile();
		} else {
		  var spec=1;
		}
		if (plants==1) {
		var pll = saveUserPlantasFile();              
		} else { var pll = 1;}
		if (plots==1) {
		var plts = saveUserPlotsFile(); 
		} else { var plts=1;}
		//document.getElementById('probarperc').innerHTML = (sp+spec+pll+plts);
		if ((sp+spec+pll+plts)==4) {
			 setTimeout(function(){ 
			window.location = 'checklistview_tabber.php'; 
			}, 6000);
		}
	}
     runall(".$species.", ".$specimens.",".$plantas.",".$plots.");
</script>",
"<style>
#probar {
  width: 300px;
  height: 3em;
}
#probarperc {
  border-style:solid;
  border-width: 1px;
  border-color: black;
  padding: 10px;
  text-align: center;
  color: #000000;
  background-color: yellow;
  width: 320px;
  heigth: 50px;
}
#procont {
  width: 320px;
  margin: auto;
  text-align: center;
}
</style>"
);
$title = 'Prepara tabelas de visualização inicial';
$body = '';
FazHeader($title,$body,$which_css,$which_java,$menu);
echo "
<br />
<div style='  position: fixed;
  top: 50%;
  left: 50%;
  margin-top: -50px;
  margin-left: -100px; align: center;'>
<div id='probarperc'>Inicializando! Aguarde!</div>
<div style='text-align:center; '><img id='coffeeid' src='icons/animatedcoffee.gif'  height='50' /></div>
</div>";

//$arratosend[] = $arrofpass;
//$_SESSION['checklistarray'] = serialize($arratosend);
//echo "<form name='myform' action='checklistview_tabber.php' method='post'><script language=\"JavaScript\">setTimeout('document.myform.submit()',0.0001);</script></form>";

$which_java = array("<script type='text/javascript' src='javascript/myjavascripts.js'></script>");
FazFooter($which_java,$calendar=FALSE,$footer=$menu);

?>