<?php
session_start();
//Check whether the session variable
include "functions/databaseSettings_small.php";
include $relativepathtoroot.$databaseconnection;
include "functions/MyPhpFunctions.php";
require_once ("javascript/jpgraph/src/jpgraph.php");

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
//echopre($dbhs);
if (count($dbhs)>5) {
	require_once ("javascript/jpgraph/src/jpgraph_bar.php");
	//create a sequence of class values based on the range
	$mindbh = min($dbhs);
	$maxdbh = max($dbhs);
	$range = $maxdbh-$mindbh;

	if ($range>100) {$interval = 10;}
	if ($range<50) {$interval = 5;}
	$mm = $mindbh-$interval;
	if ($mm<0) {$mm=0;} else {$mm = round($mm,0);}
	//$classes = range($mm,round($maxdbh,0)+$interval,$interval);

	//fixed classes
	//$classes = array(1,10,50,100,150,200,250,300,350,400,450,500,1000,1500,2000,3000);
	$classes = array(1,10,20,30,40,50,100,150,200,250,300,350,400,450,500,1000,1500,2000,3000);
	$ncl = count($classes)/2-1;
	$xclasses = array();
	$ynstems = array();
	$j = 0;
	$n=1;
	
	//function
	function filter_by_value($array, $smaller, $greater){
        if(is_array($array) && count($array)>0) 
        {
            foreach(array_keys($array) as $key){
                $temp[$key] = $array[$key];
                
                if ($temp[$key] >= $smaller && $temp[$key] < $greater){
                    $newarray[$key] = $array[$key];
                }
            }
          }
      return $newarray;
	} 
	
	
	for ($i=1; $i<$ncl+2; $i++) {
		$cl = $classes[$n];
		$ii = $n+1;
		$nj = $n-1;
		if ($cl>=$maxdbh) {$clpost = $cl+5;} else {$clpost = $classes[$ii];}
		$clant = $classes[$nj];
		$smaller = $clant;
		$greater = $clpost;
		$tz = filter_by_value($dbhs, $smaller, $greater);
		$nn = count($tz);
		if ($nn>0) {
			$ynstems[$j] = $nn;
		} else {
			$ynstems[$j] = 0;
		}
		$xclasses[$j] = "$clant-$clpost";
		$j++;
		$n = $n+2;
	}

$datay = $ynstems;
$datax = $xclasses;
$narr = array_combine($datax,$datay);
//echopre($narr);
$lixo = 1000;
if ($lixo>100) {
// Create the graph. These two calls are always required
$graph = new Graph(400,300); //,'auto');
$graph->SetScale('textlin');
$graph->img->SetMargin(60,20,35,75);
$graph->SetMarginColor("lightblue:1.5");

// Set up the title for the graph
$graph->title->Set('Distribuição Diamétrica');
$graph->title->SetMargin(20);
//$graph->title->SetFont(FF_DV_SERIF,FS_BOLD,12);
$graph->title->SetColor("darkred");
//
// Add a drop shadow
$graph->SetShadow();

// Adjust the margin a bit to make more room for titles
$graph->SetMargin(60,30,50,90);
$graph->SetMarginColor('#CCFF99');

$graph->xaxis->SetTickLabels($datax);
$graph->xaxis->SetLabelAngle(45);

$graph->yaxis->scale->SetGrace(10);


// Setup the titles
$graph->xaxis->SetTitle('Classes de DAP (mm)',"center");
$graph->xaxis->SetTitleSide(SIDE_TOP);

$graph->yaxis->title->Set('Número de Fustes');
$graph->xaxis->title->SetColor('darkred');
$graph->yaxis->title->SetColor('darkred');
//$graph->xaxis->title->SetFont(FF_DV_SERIF,FS_BOLD,8);
//$graph->yaxis->title->SetFont(FF_DV_SERIF,FS_BOLD,8);

// Setup font for axis
//$graph->xaxis->SetFont(FF_DV_SERIF,FS_NORMAL,7);
$graph->xaxis->SetTitleMargin(-90);
//$graph->yaxis->SetFont(FF_DV_SERIF,FS_NORMAL,7);
$graph->yaxis->SetTitleMargin(50);

// Adjust fill color
//$bplot->SetFillColor('orange');
//$bplot->SetFillGradient("navy","lightsteelblue",GRAD_WIDE_MIDVER);
// Setup color for gradient fill style 

// Create a bar pot
$bplot = new BarPlot($datay);
$bplot->SetWidth(0.8);
//$graph->SetBackgroundGradient($aFrom='#CCFF99',$aTo='#669933',$aGradType=1,$aStyle=BGRAD_MARGIN);
$bplot->SetFillGradient("blue","lightblue",GRAD_WIDE_MIDVER);
// Set color for the frame of each bar
$bplot->SetColor("orange");
$graph->Add($bplot);
// Display the graph
$graph->Stroke();
} 
}
else {
require_once ('javascript/jpgraph/src/jpgraph_canvas.php');
 
// Setup a basic canvas we can work 
$g = new CanvasGraph(200,200,'auto');
$g->SetMargin(5,11,6,11);
//$g->SetShadow();
//$g->SetMarginColor("teal");
 
// We need to stroke the plotarea and margin before we add the
// text since we otherwise would overwrite the text.
//$g->InitFrame();
 
// Draw a text box in the middle
$txt="Poucos indivíduos!\nNão faz sentido fazer o gráfico!";
$t = new Text($txt,100,100);
//$t->SetFont(FF_ARIAL,FS_BOLD,10);
 
// How should the text box interpret the coordinates?
$t->Align('center','top');
 
// How should the paragraph be aligned?
$t->ParagraphAlign('center');
 
// Add a box around the text, white fill, black border and gray shadow
$t->SetBox("white","black","gray");
 
// Stroke the text
$t->Stroke($g->img);
 
// Stroke the graph
$g->Stroke();
}


?>