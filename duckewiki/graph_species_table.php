<?php
session_start();
//Check whether the session variable
include "functions/databaseSettings_small.php";
include $relativepathtoroot.$databaseconnection;
include "functions/MyPhpFunctions.php";
require_once ("javascript/jpgraph/src/jpgraph.php");
require_once ('javascript/jpgraph/src/jpgraph_canvas.php');
require_once ('javascript/jpgraph/src/jpgraph_table.php');

$lang = $_SESSION['lang'];
$dbname = $_SESSION['dbname'];
$conn = ConectaDB($dbname);
$uuid = cleanQuery($_SESSION['userid'],$conn);
if((!isset($uuid) || 
	(trim($uuid)=='')) && count($listsarepublic)==0) {
		header("location: access-denied.php");
	exit();
} 

//LIMPA AS VARIAVEIS
$ppost = cleangetpost($_POST,$conn);
$arval = $ppost;
@extract($ppost);
$gget = cleangetpost($_GET,$conn);
@extract($gget);


//$plotmapvars = array('grx'=> $grx, 'gry'=> $gry, 'mm' => $mm, 'dimx' => $dimx, 'dimy' => $dimy, 'dataz' => $dataz, 'nomes' => $nomes, 'datax' => $datax, 'datay' => $datay, 'targ' => $targ, 'alts' => $alts);
$plotmapvars = unserialize($_SESSION['plotmapvars']);
$dbhs = $plotmapvars['dataz'];
$dimx = $plotmapvars['dimx'];
$dimy = $plotmapvars['dimy'];
$detids = $plotmapvars['detids'];

//daps
$maior = round(max($dbhs),0);
$menor = round(min($dbhs),0);

//funcao que calcula a area basal
function mybasal(&$item2, $key)
{
	$item2 = $item2/10; //conver to cm
	$item2 = $item2/100; //conver to m
	$pii = pi();
    $item2 = (($item2*$item2)*$pii)/(4);
}
array_walk_recursive($dbhs,'mybasal');
$plotarea = $dimx*$dimy;
$numhect = ceil($plotarea/10000);
$basalarea = array_sum($dbhs);
$basalperhect = round($basalarea/$numhect,5);
$abund = count($dbhs);

//calcula resumo taxonomia
$nomedets = array(); 
$detmod = array();
foreach ($detids as $det) {
	$qq = "SELECT DetbyID,Prenome, Sobrenome,DetModifier FROM Identidade LEFT JOIN Pessoas ON DetbyID=PessoaID WHERE DetID='".$det."' LIMIT 0,1";
	$res = mysql_query($qq,$conn);
	$rw = mysql_fetch_assoc($res);
	if ($rw['DetbyID']>0) {
		$nomedets[] = $rw['Prenome']." ".$rw['Sobrenome'];
	}
	if (!empty($rw['DetModifier'])) {
		$detmod[] = $rw['DetModifier'];
	}
}
$dets = round((count($nomedets)/count($detids))*100,0);
$detsn = array_count_values($nomedets);
$txt = array();
foreach ($detsn as $kk => $vv) {
	$vv = round(($vv/count($detids))*100,0);
	$txt[] = $kk." (".$vv."%)";
}
$ndetsn = count($txt);
$detsn = implode("\n",$txt);

$detsm = round((count($detmod)/count($detids))*100,0);
$detsnm = array_unique($detmod);
$ndetsnm = count($detsnm);
$detsnm = implode("  ",$detsnm);

if (($ndetsn+$ndetsnm)==0) {
	$ydim = 300;
} else {
	$ydim = 300+(5*($ndetsn+$ndetsnm));
}

// Setup graph context
$graph = new CanvasGraph(400,$ydim);
$graph->SetShadow();
//$graph->SetMargin(40,40,40,40);


//"(".$pi."*".$t.")/4*                   


// Setup the basic table
$data = array(
    array('Resumo',        'Valor'),
    array('Área Basal',$basalperhect),
    array('Abundância',$abund.' indivíduos'),
    array('Menor DAP',$menor.' mm'),
    array('Maior DAP',$maior.' mm'),
    array('Taxonomia',''),
    array('Ind. c/ determinação',$dets."% "),
    array('Determinadores',$detsn),
    array('Ind. c/ modificador',$detsm."% "),
    array('Modificadores',$detsnm)
    );
$t = new SuperScriptText();
$t->Set("Área Basal".$basalperhect." m","2");
//$t2 = new SuperScriptText();
//$t2->Set($t."\\*ha","-1");

// Setup the basic table and font
$table = new GTextTable();
//$table->SetScalePos(0,0);
$table->SetPos(20,40);
$table->Set($data);
$table->Set(1,1,$basalperhect." m2/ha");
$table->SetFont(FF_ARIAL,FS_NORMAL,10);
$table->SetColor("darkred");
 
// Set default minimum color width
$table->SetMinColWidth(130);
 
// Set default table alignment
$table->SetAlign('left');
 
// Turn off grid
$table->setGrid(0);
// Set table border
$table->SetBorder(1);
 
// Setup font
//$table->SetRowFont(4,FF_ARIAL,FS_BOLD,11);
$table->SetRowFont(0,FF_ARIAL,FS_BOLD,10);
$table->SetRowFont(5,FF_ARIAL,FS_BOLD,10);

//$table->SetFont(1,2,1,3,FF_ARIAL,FS_BOLD,11);
 
// Setup grids
//$table->SetRowGrid(4,2,'black',TGRID_SINGLE);
//$table->SetColGrid(1,1,'black',TGRID_SINGLE);
//$table->SetRowGrid(1,1,'black',TGRID_SINGLE);
 
// Setup colors
$table->SetFillColor(0,0,0,1,'darkred');
$table->SetFillColor(5,0,5,1,'darkred');

$table->SetAlign(1,0,9,0,'right');
$table->SetAlign(0,1,9,1,'center');
$table->SetRowColor(0,'white');
$table->SetRowColor(5,'white');

//$table->SetRowFillColor(4,'lightgray@0.3');
$table->SetFillColor(2,0,2,1,'lightgray@0.6');
$table->SetFillColor(4,0,4,1,'lightgray@0.6');
$table->SetFillColor(6,0,6,1,'lightgray@0.6');
$table->SetFillColor(8,0,8,1,'lightgray@0.6');
//$table->SetFillColor(1,2,1,3,'lightred');
 
// Add table to graph
$graph->Add($table);
 
// Send back to the client
$graph->Stroke();

?>