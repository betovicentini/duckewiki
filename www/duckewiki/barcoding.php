<?php
require_once "javascript/jpgraph/src/jpgraph.php";
require_once "javascript/jpgraph/src/jpgraph_canvas.php";
require_once "javascript/jpgraph/src/jpgraph_barcode.php";

$params = array(
    array('code',1),array('data',''),array('modwidth',1),array('info',false),
    array('notext',false),array('checksum',false),array('showframe',false),
    array('vertical',false) , array('backend','IMAGE'), array('file',''),
    array('scale',1), array('height',70), array('pswidth','') );

$n=count($params);
for($i=0; $i < $n; ++$i ) {
    $v  = $params[$i][0];
    if(!isset($$v)) {
    	if (empty($_GET[$params[$i][0]])) {
			$$v = $params[$i][1];
	    } else {
			$$v = $_GET[$params[$i][0]];
		}
	}
}


//the barcode
if( $code==20 ) {
	$encoder = BarcodeFactory::Create(6);
	$encoder->UseExtended();
}
    else {
	$encoder = BarcodeFactory::Create($code);
}
$b =  $backend=='EPS' ? 'PS' : $backend;
$b = substr($backend,0,5) == 'IMAGE' ? 'IMAGE' : $b;
$e = BackendFactory::Create($b,$encoder);
if( substr($backend,0,5) == 'IMAGE' ) {
if( substr($backend,5,1) == 'J' ) 
    $e->SetImgFormat('JPEG');
}
if( $e ) {
	if( $backend == 'EPS' )
	    $e->SetEPS();
	if( $pswidth!='' )
	    $modwidth = $pswidth;
	$e->SetModuleWidth($modwidth);
	$e->AddChecksum($checksum);
	$e->NoText($notext);
	$e->SetScale($scale);
	$e->SetVertical($vertical);
	$e->ShowFrame($showframe);
	$e->SetHeight($height);
	$r = $e->Stroke($data,$file,$info,$info);
	//$gdImgHandler = $e->Stroke(_IMG_HANDLER);
	// Stroke image to a file and browser
	// Default is PNG so use ".png" as suffix
	//$e->img->Stream($file);
	//$imgbr = $e->Stroke(_IMG_HANDLER); 
	//if( $r )
	    //echo nl2br(htmlspecialchars($r));
	//if( $file != '' )
	    //echo "<p>Wrote file $file.";
}

?>