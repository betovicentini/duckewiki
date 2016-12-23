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
$menu = FALSE;


$naotempermissao=0;
//echopre($gget);
//echopre($ppost);


if ($checklist==1) {
	// && $acclevel!='visitor'
	if (($uuid>0) || isset($public_nir)) {
		$addname = trim($gget['famid']."_".$gget['genid']."_".$gget['specid']."_".$gget['infspecid']);
		$export_filename = "temp_nir_export".$addname.".csv";
		$orgget = $gget;
		//unset($gget['checklist']);
		//$_SESSION['exportnir'.substr(session_id(),0,10)] = serialize($gget);
		$antaris=1;
		} else {
		$naotempermissao=1;
		}
		$varstxt = "";
		$i=0;
		foreach($orgget as $kk => $vv) {
			if ($i==0) {
				$varstxt .= $kk."=".$vv;
			} else {
				$varstxt .= "&".$kk."=".$vv;
			}
			$i++;
		}
} else {
		$varstxt = "filtroid=".$filtroid."&checklist=".$checklist;
		$orgget = $ppost;
		$export_filename = "temp_nir_export".substr(session_id(),0,10).".csv";
}

if ($naotempermissao==0) {
	if (!file_exists("temp/".$export_filename) || $updatefile==1) {
//PREPARA ARQUIVO PARA LOOP DE PROGRESSO
$qqz = "DROP TABLE `temp_exportnirdata.".substr(session_id(),0,10)."`";
mysql_query($qqz,$conn);
$qqz = "CREATE TABLE `temp_exportnirdata.".substr(session_id(),0,10)."`  (`percentage` INT(10) NOT NULL) ENGINE = InnoDB CHARACTER SET utf8 COLLATE utf8_general_ci";
mysql_query($qqz,$conn);
$qqz = "INSERT INTO `temp_exportnirdata.".substr(session_id(),0,10)."` (`percentage`) VALUES ('0');";
mysql_query($qqz,$conn);

if ($antaris==1) {
	$fnscript = "export-nir-data-script_antaris.php";
} else {
	$fnscript = "export-nir-data-script.php";
}

//echo $varstxt."<br >".$fnscript ;


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
      $.get('export-nir-data-progress.php', { t: time }, function (data) {
          var progress = parseInt(data, 10);
          document.getElementById('probar').value = progress;
        if (progress < 100) {
          document.getElementById('probarperc').innerHTML = 'Processando ' + progress + '%';
          setTimeout(function() { CheckProgress();}, 1);
    	} else {
          document.getElementById('probarperc').innerHTML = progress + '%' +' CONCLUIDO';
          document.getElementById('loaderimg').style.visibility= 'hidden';
          window.location = \"export-nir-data-save.php?notagain=1&ispopup=1&export_filename=".$export_filename."\";
    	}
      });
	}
    //start your long-running process 
    $.ajax(
            {
                type: 'GET',
                url: '".$fnscript."?".$varstxt."',
                async: true,
                success:
                    function (data) {
                        document.getElementById('probarperc').innerHTML = data ;
                        document.getElementById('loaderimg').style.visibility= 'hidden';
                        document.getElementById('coffeeid').style.visibility= 'hidden';
                        //window.location = 'export-nir-data-save.php?&ispopup=1';
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
$title = 'Exportando dados NIR!';
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
	} 
	else {
echo "
<form name='saveform'  action='export-nir-data-save.php' method='post'>
  <input type='hidden' name='export_filename' value='".$export_filename."' >";
foreach ($orgget as $kk => $vv) {
echo "
  <input type='hidden' name='".$kk."' value='".$vv."' >";
}
echo "
  <script language=\"JavaScript\">
    setTimeout('document.saveform.submit()',1);
</script>
</form>";
	}
} 
else {
	echo "Você não tem permissão para baixar dados NIR";
}
//                data: { ".$varstxt ." },

$which_java = array();
FazFooter($which_java,$calendar=FALSE,$footer=$menu);
?>