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

//VARIAVEIS QUE VEM DE OUTROS LINKS
$detid =0;
$detid = $famid+$genid+$specid+$infspecid+$detid;
if (empty($tbname)) {
	if ($filtro>0 || $detid>0 || $quickview>0 || $specimenid>0) {
		$tbname = "temp_Planta".substr(session_id(),0,10);
		$qq = "DROP TABLE ".$tbname;
		@mysql_query($qq,$conn);
		$update=1;
	} else {
		if ($idd>0 && !empty($tableref)) {
			$tbname = "temp_Planta".substr(session_id(),0,10);
			$qq = "DROP TABLE ".$tbname;
			$rq = mysql_query($qq,$conn);
			mysql_free_result($rq);
			$qq = "CREATE TABLE ".$tbname." (SELECT * FROM checklist_pllist WHERE isvalidlocal(GazetteerID,GPSPointID, ".$idd.", '".$tableref."'))"; 
			$rq = mysql_query($qq,$conn);
			mysql_free_result($rq);
			$update=0;
		} else {
			$tbname = "checklist_pllist";
		}
	}
}

$qq = "SELECT * FROM ".$tbname;
$rr = @mysql_query($qq,$conn);
$nr = @mysql_numrows($rr);
@mysql_free_result($rr);

//$formnotes = 60;
//$exsicatatrait = 350;
//$duplicatesTraitID =  496;
//$formidhabitat = 5;
//$update=1;
//$_SESSION['checklistplantas_progress'] = $tbname;

$vars = array('tbname'  =>  $tbname,
' quickview'  =>  $quickview,
' specimenid'  =>  $specimenid,
' filtro'  =>  $filtro,
' idd'  =>  $idd,
' tableref'  =>  $tableref,
' quicktbname'  =>  $quicktbname,
' famid'  =>  $famid,
' genid'  =>  $genid,
' specid'  =>  $specid,
'  infspecid'  =>  $infspecid,
'  detid'  =>  $detid,
' update'  =>  $update,
' nr'  =>  $nr);

echo json_encode($vars);

//unset($_SESSION['expPlotData']);

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
    function CheckProgress() {
      var time = new Date().getTime();
      $.get('checkllist_plantas_progress.php', { t: time, tbname: '".$tbname."'}, function (data) {
          var progress = parseInt(data, 10);
          document.getElementById('probar').value = progress;
        if (progress < 100) {
          document.getElementById('probarperc').innerHTML = 'Processando ' + progress + '%';
          setTimeout(function() { CheckProgress();}, 1);
    	} else {
          document.getElementById('probarperc').innerHTML = progress + '%' +' CONCLUIDO';
          document.getElementById('loaderimg').style.visibility= 'hidden';
          window.location = 'checkllist_plantas_save.php?tbname=".$tbname."&ispopup=1';
    	}
      });
	}
    //start your long-running process 
    $.ajax(
            {
                type: 'GET',
                url: 'checkllist_plantas_script.php',
                data: { tbname: '".$tbname."', quickview: '".$quickview."', specimenid: '".$specimenid."', filtro: '".$filtro."', idd: '".$idd."', tableref: '".$tableref."', quicktbname: '".$quicktbname."', famid: '".$famid."', genid: '".$genid."', specid: '".$specid."',  infspecid: '".$infspecid."',  detid: '".$detid."', update: '".$update."', nr: '".$nr."'},
                async: true,
                success:
                    function (data) {
                        document.getElementById('probarperc').innerHTML = data ;
                        document.getElementById('loaderimg').style.visibility= 'hidden';
                        if (data=='Concluido') {
                           window.location = 'checkllist_plantas_save.php?tbname=".$tbname."&ispopup=1';
                        }
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
$title = 'Preparando o Checklist de plantas marcadas!';
FazHeader($title,$body,$which_css,$which_java,$menu);
echo "
<br />
<div id='procont'>
<progress id='probar' value='0' max='100'></progress>
<img id='loaderimg' src='icons/ajax-loader.gif'  />
<div id='probarperc'>Aguarde!</div>
</div>
";
$which_java = array(
//"<script type='text/javascript' src='javascript/myjavascripts.js'></script>",
//"<!-- Create Menu Settings: (Menu ID, Is Vertical, Show Timer, Hide Timer, On Click ('all' or 'lev2'), Right to Left, Horizontal Subs, Flush Left, Flush Top) -->",
//"<script type='text/javascript'>qm_create(0,false,0,500,false,false,false,false,false);</script>"
);
FazFooter($which_java,$calendar=FALSE,$footer=$menu);
?>