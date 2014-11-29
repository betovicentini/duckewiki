<?php 
session_start();
require_once ('javascript/jpgraph/src/jpgraph.php');
require_once ('javascript/jpgraph/src/jpgraph_stock.php');

$dd = $_SESSION['gvqt'];     
$datay = $dd[0];
$species = $dd[1];
$ylim = $dd[2];
$title = $dd[3];
$nmeasarr = $dd[4];
$yylab = $dd[5];
$specarr = $dd[6];

$colwd = 22;
$rowd = 40;
$lmar = 100;
$bmar = 150;
$tmar = 10;
$rmar = 30;

//definicoes dos tamanhos das imagens
$nsp = count($species);
$wdd = ($nsp*$colwd)+$lmar;
if ($wdd<500) { $wdd=500;}

$graph = new Graph(strval($wdd),500);
$graph->SetMarginColor('wheat');
$graph->SetScale('textlin');
$graph->img->SetMargin(strval($lmar),strval($tmar),strval($rmar),strval($bmar));

$graph->xaxis->SetColor('darkred');
$graph->yaxis->SetColor('darkred');

//$graph->title->Set($title);
//$graph->title->SetFont(FF_DV_SERIF,FS_BOLD,11);
//$graph->title->SetColor('darkred');
//$graph->title->SetMargin(20);

$graph->SetTickDensity(TICKD_DENSE,TICKD_DENSE);

// Make sure X-axis as at the bottom of the graph and not at the default Y=0
$graph->xaxis->SetPos('min');
$graph->yaxis->SetPos( 'min' );

$graph->xscale->SetAutoMin(0);
$graph->xaxis->scale->SetGrace(1,1);

//$yg = $ylim[1]*0.1;
$graph->yaxis->scale->SetGrace(1,1);

//$xxlab = array("--");
//$lab = array_merge((array)$xxlab,(array)$species,(array)$xxlab,(array)$xxlab);
$lab = $species;
$graph->xaxis->SetTickLabels($lab);
$graph->xaxis->SetFont(FF_DV_SERIF,FS_NORMAL,8);
$graph->xaxis->SetLabelAngle(45);
$graph->xaxis->SetLabelMargin(5);

$graph->yaxis->title->Set($yylab);
$graph->yaxis->title->SetFont(FF_DV_SERIF,FS_BOLD,8);
$graph->yaxis->title->SetColor('darkred');
$graph->yaxis->title->SetMargin(20);
	
$graph->xgrid->SetLineStyle('dotted');
//$graph->xgrid->SetFill(true,'#EFEFEF@0.5','#BBCCFF@0.5'); 
//$graph->ygrid->SetFill(false,'#EFEFEF@0.5','#BBCCFF@0.5'); 
$graph->xgrid->Show();

$graph->ygrid->SetLineStyle('dotted');
$graph->ygrid->SetFill(true,'#EFEFEF@0.5','#BBCCFF@0.5'); 
$graph->xgrid->SetFill(false,'#EFEFEF@0.5','#BBCCFF@0.5'); 
$graph->ygrid->Show();

// Create a new stock plot
$p1 = new BoxPlot($datay);
$p1->SetWidth(12);
$p1->SetColor('black','yellow','black', 'blue');
$p1->SetMedianColor('black','black');
$p1->SetCenter();

$targetarray = array();
$targetalts = array();
$aWinTargets = array();
$i=1;
foreach ($species as $spp) {
	$specids = $specarr[$spp];
	
	$url = array("#".$specids);
	$i++;
	$targetarray = array_merge((array)$targetarray,(array)$url);

	$zi = explode(";",$specids);
	
	$url = array($spp." (Nobs=".count($zi).")");
	$targetalts = array_merge((array)$targetalts,(array)$url);
	
	$url = array("_blank");
	$aWinTargets = array_merge((array)$aWinTargets,(array)$url);
}
$p1->SetCSIMTargets($targetarray,$targetalts,$aWinTargets);
// Setup URL target for image map
//$p1->SetCSIMTargets(array('#1','#2','#3','#4','#5'));

// Width of the bars (in pixels)
//$p1->SetWidth(9);
$nbal = count($nmeasarr);
$jj=3;
$zf = ($ylim[1]/500)*28;
for ($i=0; $i<$nbal; $i++) {
	$str = strval($nmeasarr[$i]);
	$txt = new Text($str);
	$txt->Align('left','top','center');
	$zx = $i+0.32;
	$zy = $datay[$jj]+$zf;
	$jj = $jj+5;
	$txt->SetScalePos($zx,$zy);
	$txt->SetColor("blue");
	$graph->AddText($txt);
}
//$p1->SetCenter();
// Uncomment the following line to hide the horizontal end lines
//$p1->HideEndLines();

// Add the plot to the graph and send it back to the browser
$graph->Add($p1);
//$graph->StrokeCSIM();
//$graph -> StrokeCSIMImage();
$graph -> Stroke();
?>