<?php
session_start();
//Check whether the session variable
include "functions/databaseSettings_small.php";
include $relativepathtoroot.$databaseconnection;
include "functions/MyPhpFunctions.php";
require_once ("javascript/jpgraph/src/jpgraph.php");
require_once ("javascript/jpgraph/src/jpgraph_scatter.php");

$lang = $_SESSION['lang'];
$dbname = $_SESSION['dbname'];
$conn = ConectaDB($dbname);
$uuid = cleanQuery($_SESSION['userid'],$conn);
if(!isset($uuid) || 
	(trim($uuid)=='')) {
		header("location: access-denied.php");
	exit();
} 

$ppost = cleangetpost($_POST,$conn);
$arval = $ppost;
@extract($ppost);
$gget = cleangetpost($_GET,$conn);
@extract($gget);


$nids = explode(" ",$idds);
$nnids = count($nids);
if ($nnids>1) {
	$idds = $nids;
} else {
	$idds = array($idds);
}
//HTMLheaders('');

//define plot dimensions
if (!isset($imgfile)) {
	unset($_SESSION['plotmapvars']);
	$qq = "SELECT DimX,DimY,Imagens.FileName FROM Gazetteer LEFT JOIN Imagens  USING(GazetteerID) WHERE GazetteerID='".$gazetteerid."' LIMIT 0,1";
	$res = mysql_query($qq,$conn);
	$row = mysql_fetch_assoc($res);
	$dimx = $row['DimX'];
	$dimy = $row['DimY'];
	$imgfile = $row['FileName'];

	$qq = "SELECT 
DISTINCT 
gentb.Genero,
spectb.Especie,
infsptb.InfraEspecie,
iddet.DetID,
pltb.PlantaID,
pltb.PlantaTag,
getplantcoord(pltb.GazetteerID, pltb.X,pltb.Y, pltb.LADO, 'X',".$gazetteerid.") as posX, 
getplantcoord(pltb.GazetteerID, pltb.X,pltb.Y, pltb.LADO, 'Y',".$gazetteerid.") as posY,
mediadaps(moni.TraitVariation) as DAP 
FROM Plantas as pltb 
LEFT JOIN Monitoramento as moni ON moni.PlantaID=pltb.PlantaID 
LEFT JOIN Identidade as iddet ON pltb.DetID=iddet.DetID 
LEFT JOIN Tax_InfraEspecies as infsptb ON iddet.InfraEspecieID=infsptb.InfraEspecieID
LEFT JOIN Tax_Especies as spectb ON iddet.EspecieID=spectb.EspecieID 
LEFT JOIN Tax_Generos as gentb ON iddet.GeneroID=gentb.GeneroID
LEFT JOIN Tax_Familias as famtb ON iddet.FamiliaID=famtb.FamiliaID 
WHERE pltb.X>0 AND pltb.Y>0 AND (pltb.GazetteerID=".$gazetteerid;


$qu = "SELECT * FROM Gazetteer WHERE ParentID='".$gazetteerid."'";
$rzz = mysql_query($qu,$conn);
$nrzz = mysql_numrows($rzz);
if ($nrzz>0) {
	while ($row = mysql_fetch_assoc($rzz)) {
		$qq .= " OR pltb.GazetteerID='".$row['GazetteerID']."'";
	}
}
$qq .= ")";
$filtro = '';
if (count($idds)>0) {
	$ii=0;
	foreach ($idds as $id) {
		$flt = '';
		if (!empty($id)) {
			$vals = explode("_",$id);
			if ($vals[0]=='infspid') {
				$flt = " iddet.InfraEspecieID=".$vals[1];
			} 
			else {
				if ($vals[0]=='specid') {
					$flt = " iddet.EspecieID=".$vals[1];
				} 
				else {
					if ($vals[0]=='genid') {
						$flt = " iddet.GeneroID=".$vals[1];
					} 
					else {
						$flt = " iddet.FamiliaID=".$vals[1];
					}
				}
			}
		    if ($ii==0) {
				$filtro = $filtro." AND (".$flt; 
		    } else {
				$filtro = $filtro." OR ".$flt;
		    }
		    $ii++;
		}
	}
	$filtro .= ")";
}
$qq .= $filtro." AND moni.TraitID=".$daptraitid." ORDER BY famtb.Familia,gentb.Genero,spectb.Especie,infsptb.InfraEspecie";
//$qq."<br>";
$res = @mysql_query($qq,$conn);
$nn = @mysql_numrows($res);
//$nn = 0;
if ($nn>0) {
	//testa novamente inventando coordenadas para parcelas que não tem.
	$qq = "SELECT 
DISTINCT 
gentb.Genero,
spectb.Especie,
infsptb.InfraEspecie,
iddet.DetID,
pltb.PlantaID,
pltb.PlantaTag,
makeplantcoord(pltb.GazetteerID, pltb.X,pltb.Y, pltb.LADO, 'X',".$gazetteerid.") as posX, 
makeplantcoord(pltb.GazetteerID, pltb.X,pltb.Y, pltb.LADO, 'Y',".$gazetteerid.") as posY,
mediadaps(moni.TraitVariation) as DAP 
FROM Plantas as pltb 
LEFT JOIN Monitoramento as moni ON moni.PlantaID=pltb.PlantaID 
LEFT JOIN Identidade as iddet ON pltb.DetID=iddet.DetID 
LEFT JOIN Tax_InfraEspecies as infsptb ON iddet.InfraEspecieID=infsptb.InfraEspecieID
LEFT JOIN Tax_Especies as spectb ON iddet.EspecieID=spectb.EspecieID 
LEFT JOIN Tax_Generos as gentb ON iddet.GeneroID=gentb.GeneroID
LEFT JOIN Tax_Familias as famtb ON iddet.FamiliaID=famtb.FamiliaID 
WHERE (pltb.GazetteerID=".$gazetteerid;
$qu = "SELECT * FROM Gazetteer WHERE ParentID='".$gazetteerid."'";
$rzz = mysql_query($qu,$conn);
$nrzz = mysql_numrows($rzz);
if ($nrzz>0) {
	while ($row = mysql_fetch_assoc($rzz)) {
		$qq .= " OR pltb.GazetteerID='".$row['GazetteerID']."'";
	}
}
$qq .= ")";
$qq .= $filtro." AND moni.TraitID=".$daptraitid." ORDER BY famtb.Familia,gentb.Genero,spectb.Especie,infsptb.InfraEspecie";
//$qq."<br>";
$res = @mysql_query($qq,$conn);
$nnn = @mysql_numrows($res);
} else {
	$nnn = $nn;
}

if ($nnn>0) {
$i =0;
$xx = array();
$yy = array();
$dbhs = array();
$alts = array();
$targ = array();
$nomes = array();
$detids = array();
while ($row = mysql_fetch_assoc($res)) {
	$x = $row['posX'];
	$y = $row['posY'];
	$db = $row['DAP'];
	$xx[] = $x;
	$yy[] = $y;
	$dbhs[] = $db+0;
	$nome = trim($row['Genero']." ".$row['Especie']." ".$row['InfraEspecie']);
	$detids[] = $row['DetID'];
	$nomes[] = $nome;
	$za = $nome." Planta No. ".$row['PlantaTag']." com DAP ".$row['DAP']." mm";
	$alts[] = $za;
	//$targ[] = ";
	$targ[] = "javascript:small_window('showplantdescription.php?plantaid=".$row['PlantaID']."',350,350,'EntrarVariacao');";

}
$numrec = count($xx);
$datax = $xx;
$datay = $yy;
$dataz = $dbhs;

$graphdim = 550;
$mm = $graphdim*0.1;
if ($dimx<$dimy) {
	$gry = $graphdim-$mm;
	$grx = ($dimx*$gry)/$dimy;
	if ($grx<($gry/2)) {
		$grx = ($gry*0.67);
	}
} else {
	$grx = $graphdim-$mm;
	$gry = ($dimy*$grx)/$dimx;
	if ($gry<($grx/2)) {
		$gry = $grx*0.67;
	}
}

	$plotmapvars = array('grx'=> $grx, 'gry'=> $gry, 'mm' => $mm, 'dimx' => $dimx, 'dimy' => $dimy, 'dataz' => $dataz, 'nomes' => $nomes, 'datax' => $datax, 'datay' => $datay, 'targ' => $targ, 'alts' => $alts, 'detids'=> $detids);
	$_SESSION['plotmapvars'] = serialize($plotmapvars);
	}

} //termina o preparo
else {
	$plotmapvars = unserialize($_SESSION['plotmapvars']);
	@extract($plotmapvars);
}
if ($nn>0 || $imgfile<>'') {
$graph = new Graph($grx,$gry);
//echo "<br>mm: ".$mm."<br>";
$graph->SetMargin($mm+5,5,$mm-5,$mm+5);
$graph->SetMarginColor('#CCFF99');
$graph->SetScale('intint',0,$dimy,0,$dimx);
//$graph->SetScale('linlin',0,$dimx,0,$dimy);
$graph->xaxis->SetColor('darkred','darkred');
$graph->yaxis->SetColor('darkred','darkred');

// Set up the title for the graph
$graph->title->Set('Distribuicao Espacial');
$graph->title->SetMargin(20);
//$graph->title->SetFont(FF_FONT1,FS_BOLD,14);
$graph->title->SetColor("darkred");

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

//$graph->SetShadow();
 
//$graph->title->Set("Um teste");
//$graph->title->SetFont(FF_FONT1,FS_BOLD);

//background plot image
// Add background with 25% mix
//$graph->SetBackgroundGradient($aFrom='#669933',$aTo='#CCFF99',$aGradType=1,$aStyle=BGRAD_MARGIN);
//$graph->ygrid->SetLineStyle('dashed');
//$graph->ygrid->setColor('darkgray');



if (!empty($imgfile)) {
	$graph->ygrid->Show(0,0);
	$graph->SetGridDepth(DEPTH_BACK); 
	$graph->SetBackgroundImage("img/originais/".$imgfile,BGIMG_FILLPLOT);
	$graph->SetBackgroundImageMix(90);
} else {
	$graph->ygrid->SetLineStyle('dashed');
	$graph->xgrid->SetLineStyle('dashed');
	$graph->ygrid->setColor('darkgray');
	$graph->xgrid->setColor('darkgray');
	$graph->ygrid->Show(1,0);
	$graph->xgrid->Show(1,0);
}
//




$mm = max($dataz);
$mi = min($dataz);

$daprange = $mm-$mi;
if ($daprange==0) { $daprange=$mm;} 
$dotrange = 10-2;
$nzz = array();
foreach ($dataz as $vv) {
	$vv = $vv+0;
	$nz = (($dotrange*($vv-$mi))/$daprange)+3;
	$nz = ceil($nz);
	//$nz = 5;
	$nzz[] = $nz;
}

$n = count($dataz);

$nun = array_unique($nomes);
$nc = count($nun);

$cores = array();
$marcadores = array();
if ($nc<=10) {
	$mycolors = array('#009900', '#000000', '#0000FF'
	, '#9900FF'
	, '#FF0000'
	, '#FFFF00'
	, '#66FF00'
	, '#996633'
	, '#FF00FF'
	, '#CCFFFF');
	$mymarkers = array(6, 1, 4, 2, 3, 7, 8, 10, 11, 5);
	for( $i=0; $i < $nc; ++$i ) {
		$cores[] = $mycolors[$i];
		$marcadores[] = $mymarkers[$i];
	}
} else {
	for( $i=0; $i < $nc; ++$i ) {
		$cores[] = '#990000';
		$marcadores[] = 6;
	}
}
$arcores = array_combine($cores,$nun);
$armarkers = array_combine($marcadores,$nun);

//echopre($arcores);
$lascores = array();
$format = array();
$mymarcadores = array();
for( $i=0; $i < $n; ++$i ) {
    // Create a faster lookup array so we don't have to search
    // for the correct values in the callback function
    $cor = array_search($nomes[$i],$arcores);  
    $mpch = array_search($nomes[$i],$armarkers);  
    $format[strval($datax[$i])][strval($datay[$i])] = $nzz[$i];
    $lascores[strval($datax[$i])][strval($datay[$i])] = $cor;
    $mymarcadores[strval($datax[$i])][strval($datay[$i])] = $mpch;
}
//
function FCallback($aYVal,$aXVal) {
    global $format;
    global $lascores;
    global $mymarcadores;
    return    array($format[strval($aXVal)][strval($aYVal)],'',$lascores[strval($aXVal)][strval($aYVal)]);
}

$sp1 = new ScatterPlot($datay,$datax);
$sp1->mark->SetType(MARK_FILLEDCIRCLE);
$sp1->mark->SetCallbackYX("FCallback"); 
//$sp1->mark->SetWidth($nzz);
$sp1->SetCSIMTargets($targ,$alts);
//$sp1->SetLegend('Teste');
$graph->Add($sp1);
//$graph->Stroke();
$graph->StrokeCSIM();

}
 
else {
	//echo $qq."<br>";
echo "<font size=3><br>     Não foram encontradas plantas para mapear! Você selecionou uma ou mais espécies?</font>";
}
?>
