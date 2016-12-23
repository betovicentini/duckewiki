<?php
session_start();
include "functions/databaseSettings_small.php";
include $relativepathtoroot.$databaseconnection;
require_once ("javascript/jpgraph/src/jpgraph.php");
require_once ('javascript/jpgraph/src/jpgraph_scatter.php');

function FCallback($aYVal,$aXVal) {
    global $format;    
    $_SESSION['aYVal'] = $_SESSION['aYVal']." - ".$aYVal;
    $_SESSION['aXVal'] = $_SESSION['aXVal']." - ".$aXVal;
    $ay = $aYVal;
    $ax = $aXVal;
    
    return array($format[strval($ax)][strval($ay)][0]*3,'',
         $format[strval($ax)][strval($ay)][1]);
}

extract($_GET);
//unset($_SESSION['aYVal'],$_SESSION['aXVal']);

$dd = $_SESSION['gvcateg'];                        
//$dd = unserialize($dd);
$data = $dd[0];
$species = $dd[1];
$xlab = $dd[2];
$title = $dd[3];
$maxchar = $dd[4]+0;
$yylab = $dd[5];

$n = count($data);
for( $i=0; $i < $n; ++$i ) {
    $datax[$i] = $data[$i][0];
    $datay[$i] = $data[$i][1];
    $format[strval($datax[$i])][strval($datay[$i])] = array($data[strval($i)][2],$data[strval($i)][3],$data[strval($i)][4]);
}
// Setup a basic graph
$colwd = 20;
$rowd = 40;

$lmar = ($maxchar*5)+40;
$bmar = 150;

$nsp = count($species);
$wdd = ($nsp*$colwd)+$lmar;

if ($wdd<300) { $wdd=300;}

$nvals = count($xlab);
$ydd = ($nvals*$rowd)+$bmar;

if ($ydd<300) { $ydd=300;}

$graph = new Graph(strval($wdd),strval($ydd));
$graph->SetMarginColor('wheat');
$graph->img->SetMargin($lmar,30,10,$bmar);
$graph->SetMarginColor('#EEE9E9');
$nx = count($species)+1;
$ny = count($xlab)+1;
$graph->SetScale('intint',0,$ny,0,$nx);
//$graph->SetScale("linlin");

$graph->xaxis->SetColor('darkred');
$graph->yaxis->SetColor('darkred');

//$graph->title->Set($title);
//$graph->title->SetFont(FF_DV_SERIF,FS_BOLD,12);
//$graph->title->SetColor('darkred');
//$graph->title->SetMargin(20);

$graph->SetTickDensity(TICKD_DENSE,TICKD_DENSE);


$xxlab = array(" ");

$lab = array_merge((array)$xxlab,(array)$species,(array)$xxlab);
$graph->xaxis->SetTickLabels($lab);
$graph->xaxis->SetFont(FF_DV_SERIF,FS_NORMAL,8);
$graph->xaxis->SetLabelAngle(45);

$ylab = array_merge((array)$xxlab,(array)$xlab,(array)$xxlab);
$graph->yaxis->SetTickLabels($ylab);


// Make sure X-axis as at the bottom of the graph and not at the default Y=0
$graph->xaxis->SetPos('min');
$graph->yaxis->SetPos('min');
		
// Set X-scale to start at 0/
$graph->xscale->SetAutoMin(0);
$graph->yscale->SetAutoMin(0);


//$graph->yaxis->scale->SetGrace(1,1);
//$graph->xaxis->scale->SetGrace(1,1);

$graph->xgrid->SetLineStyle('dotted');
$graph->xgrid->SetFill(true,'#EFEFEF@0.5','#BBCCFF@0.5'); 
$graph->ygrid->SetFill(false,'#EFEFEF@0.5','#BBCCFF@0.5'); 
$graph->xgrid->Show();
// Create the scatter plot

$sp1 = new ScatterPlot($datay,$datax);
$sp1->mark->SetType(MARK_FILLEDCIRCLE);
  
// Specify the callback
$sp1->mark->SetCallbackYX('FCallback');
 
//Uncomment the following two lines to display the values
//$sp1->value->Show();
//$sp1->value->SetFont(FF_FONT1,FS_BOLD);
//Add custom labels to plot
$nbal = count($datax);
for ($i=0; $i<$nbal; $i++) {
	    // $rlu is an array where $rlu[$x][$y] = "Label Text"
	$str = " ".$format[strval($datax[$i])][strval($datay[$i])][2];
	$txt = new Text($str);
	$txt->SetScalePos($datax[$i]+0,$datay[$i]+0);
	$txt->SetColor("blue");
	$graph->AddText($txt);
}


 
// Add the scatter plot to the graph
$graph->Add($sp1);
 

$graph->Stroke();

?>