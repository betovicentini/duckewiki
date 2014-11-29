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

//$tbname = "TraitsEditDefinitions";
$tbname = 'temp_TraitsEditDefinitions_'.$uuid;


//PREPARA ARQUIVO PARA LOOP DE PROGRESSO
$qqz = "DROP TABLE `temp_".$tbname."`";
@mysql_query($qqz,$conn);
$qqz = "CREATE TABLE `temp_".$tbname."`  (`percentage` INT(10) NOT NULL) ENGINE = InnoDB CHARACTER SET utf8 COLLATE utf8_general_ci";
mysql_query($qqz,$conn);
$qqz = "INSERT INTO `temp_".$tbname."` (`percentage`) VALUES ('0');";
mysql_query($qqz,$conn);


//CRIA A TABELA DE LINK ENTRE TAXONOMIA E TRAITS SE AINDA NAO EXISTIR
$qq = "CREATE TABLE IF NOT EXISTS `TraitsLinkedTaxa` (
TraitLinkID INT(10) NOT NULL AUTO_INCREMENT,
TraitID INT(10),
FamiliaID INT(10),
GeneroID INT(10),
nomeid CHAR(100),
AddedBy INT(10),
AddedDate DATE,
PRIMARY KEY (TraitLinkID)) CHARACTER SET utf8 ENGINE = InnoDB ";
@mysql_query($qq,$conn);


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
      $.get('traits_definition_progress.php', { t: time, tbname: '".$tbname."'}, function (data) {
          var progress = parseInt(data, 10);
          document.getElementById('probar').value = progress;
        if (progress < 100) {
          document.getElementById('probarperc').innerHTML = 'Processando ' + progress + '%';
          setTimeout(function() { CheckProgress();}, 1);
    	} else {
          document.getElementById('probarperc').innerHTML = progress + '%' +' CONCLUIDO';
          document.getElementById('loaderimg').style.visibility= 'hidden';
          window.location = 'traits_definition_save.php?tbname=".$tbname."';
    	}
      });
	}
    $.ajax(
            {
                type: 'GET',
                url: 'traits_definition_script.php',
                async: true,
                success:
                    function (data) {
                        document.getElementById('probarperc').innerHTML = data ;
                        document.getElementById('loaderimg').style.visibility= 'hidden';
                        document.getElementById('coffeeid').style.visibility= 'hidden';
                        if (data=='Concluido') {
                              window.location = 'traits_definition_save.php?tbname=".$tbname."';
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
$title = 'Prepara Tabela de Definição/Edição de Variáveis';
//FAZ O CABECALHO
FazHeader($title,$body,$which_css,$which_java,$menu);
//CORPO DO DOCUMENTO
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
$which_java = array( );
//FAZ O PE DA PAGINA
FazFooter($which_java,$calendar=FALSE,$footer=$menu);

?>