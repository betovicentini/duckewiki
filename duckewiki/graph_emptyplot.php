<?php
session_start();
//INCLUI FUNCOES PHP E VARIAVEIS
include "functions/databaseSettings_small.php";
include $relativepathtoroot.$databaseconnection;
include "functions/MyPhpFunctions.php";
require_once ("javascript/jpgraph/src/jpgraph.php");
require_once ("javascript/jpgraph/src/jpgraph_scatter.php");
require_once ('javascript/jpgraph/src/jpgraph_canvas.php');

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


$nids = explode(" ",$idds);
$nnids = count($nids);
if ($nnids>1) {
	$idds = $nids;
} else {
	$idds = array($idds);	
}

//define plot dimensions
$qq = "SELECT DimX,DimY,Imagens.FileName FROM Gazetteer LEFT JOIN Imagens  USING(GazetteerID) WHERE GazetteerID='".$gazetteerid."' LIMIT 0,1";
//echo $qq."<br />";
$res = mysql_query($qq,$conn);
$res = @mysql_query($qq,$conn);
$nn = @mysql_numrows($res);

if ($nn>0) {

	$row = mysql_fetch_assoc($res);
	$dimx = $row['DimX'];
	$dimy = $row['DimY'];
	$imgfile = $row['FileName'];	
if ($dimx<$dimy) {
	$gry = 450;
	$grx = ($dimx*450)/$dimy;
} else {
	$grx = 450;
	$gry = ($dimy*450)/$dimx;
}
$graph = new CanvasGraph($grx,$gry);
$graph->SetMargin(50,50,50,50);
$graph->SetMarginColor('#CCFF99');
$graph->SetScale('linlin',0,$dimx,0,$dimy);
$graph->xaxis->SetColor('darkred','darkred');
$graph->yaxis->SetColor('darkred','darkred');
if (!empty($imgfile)) {
	$graph->ygrid->Show(0,0);
	$graph->SetGridDepth(DEPTH_BACK); 
	$graph->SetBackgroundImage("img/originais/".$imgfile,BGIMG_FILLPLOT);
	$graph->SetBackgroundImageMix(90);
}
$scale = new CanvasScale($graph);
$qu = "SELECT * FROM Gazetteer WHERE ParentID='".$gazetteerid."'";
$rzz = mysql_query($qu,$conn);
$nrzz = mysql_numrows($rzz);
if ($nrzz==0) {
	while ($rwz = mysql_fetch_assoc($rzz)) {
		$x1 = $rwz['StartX'];
		$y1 = $rwz['StartY']+$rwz['DimY'];
		$x2 = $rwz['StartX']+$rwz['DimX'];
		$y2 = $rwz['StartY'];
		$shape = new Shape($graph,$scale);
		$shape->SetColor('navy');
		$shape->Rectangle($x1,$y1,$x2,$y2);
	}
}

$graph->xaxis->SetTitle('metros (m)',"middle");
$graph->xaxis->SetTitleMargin(20);
$graph->yaxis->title->Set('metros (m)',"middle");
$graph->yaxis->SetTitleMargin(30);

$graph->xaxis->title->SetColor('darkred');
$graph->yaxis->title->SetColor('darkred');
$graph->xaxis->title->SetFont(FF_FONT1,FS_NORMAL,8);
$graph->yaxis->title->SetFont(FF_FONT1,FS_NORMAL,8);

// Setup font for axis
$graph->xaxis->SetFont(FF_FONT1,FS_NORMAL,7);
$graph->yaxis->SetFont(FF_FONT1,FS_NORMAL,7);
$graph->xaxis->SetColor('darkred');
$graph->yaxis->SetColor('darkred');

$graph->Stroke();

}
else {
	echo "
  <font size='3'><br />Essa parcela não pode ser desenhada</font>";
}
?>
