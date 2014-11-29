<?php
set_time_limit(0);
ini_set("allow_url_fopen", 1);
ini_set("allow_url_include", 1);
ob_start();
session_start();

include "functions/HeaderFooter.php";
include "functions/MyPhpFunctions.php";
require  "fpdf16/writehtml.php";
require  "fpdf16/PDF_Label.php";
require_once('fpdf16/php-barcode-2.0.1.php');
require_once("fpdf16/MultiCellTag/class.multicelltag.php");

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
$which_css = array(
"<link href='css/geral.css' rel='stylesheet' type='text/css' />"
//, "<link rel='stylesheet' type='text/css' href='css/cssmenu.css' />"
);
$which_java = array(
//"<script type='text/javascript' src='css/cssmenuCore.js'></script>",
//"<script type='text/javascript' src='css/cssmenuAddOns.js'></script>",
//"<script type='text/javascript' src='css/cssmenuAddOnsItemBullet.js'></script>"
);
$title = 'Gera o PDF das Etiquetas';
$body = '';

//mysql_set_charset('latin1',$conn_latin1);

$temptable = "temp_Etiqueta_".$_SESSION['userid']."_".substr(session_id(),0,10);


//echopre($_POST);

if ($spec_label==1) {
	$conn_latin1 = ConectaDB($dbname);
	mysql_set_charset('latin1',$conn_latin1);
	$filename = $_SESSION['sessiondate']."_".substr(session_id(),0,10)."_herb.pdf";

	$x1=0;
	$x2= 142;
	$y1=0;

	$xxpos = array(0,105,0,105);
	$yypos = array(0,0,145,145);

	$pdf = new PDF('P', 'mm', array(210,290));
	$pdf->SetAutoPageBreak(1,0) ;
	$pdf->SetMargins(8,8,8);
	$pdf->AddPage();
	$pdf->SetFont('Arial','B',12);


	//mysql_set_charset('latin1');
	$qq = "SELECT * FROM ".$temptable;
	$res = mysql_query($qq,$conn_latin1);
	$l=0;

	while ($rw = mysql_fetch_assoc($res)) {
		$numcol = $rw['numcol'];
		$collyy = $rw['year']+0;
		$collmm = $rw['month']+0;
		$colldd = $rw['day']+0;
		if ($colldd>0) {
			$datacol = $colldd.'-'.$collmm.'-'.$collyy;
		} 
		else {
			if ($collmm>0) {
				$datacol = $collmm.'-'.$collyy;
			} 
			else {
				if ($collyy>0) {
					$datacol = $collyy;
				} 
				else {
					$datacol = '';
				}
			}
		}
		//$datacol = $rw['datacol'];
		$detnome = $rw['detnome'];
		$familia = $rw['familia'];
		$detdetby = $rw['detdetby'];
		$herbnum = $rw['herbnum'];
		$projeto = $rw['projeto'];
		$locality = $rw['locality'];
		$coletor = $rw['coletor'];
		$addcol = $rw['addcol'];
		$vernacular = $rw['vernacular'];
		$habitat = $rw['habitat'];
		$descricao = $rw['descricao'];
		$herbarios = $rw['herbarios'];
		$herbariosinpa = $rw['herbariosinpa'];
		$logofile = $rw['logofile'];
		$prjurl = $rw['prjurl'];
		$ndups = $rw['ndups'];
		$tagnum = $rw['tagnum'];
		$dapmm = round($rw['DAPmm'],1);
		$altmet = round($rw['ALTURAm'],1);


		if ($descricao==".") {
			$descricao='';
		}
		if ($altmet>0) {
			$descricao =  $altmet." metros de altura. ".$descricao;
		}
		if ($dapmm>0) {
			$descricao = "DAP de ".$dapmm." mm. ".$descricao;
		}
		if (!empty($vernacular)) {
			$descricao = $descricao." <i>Vernacular</i>: ".$vernacular.".";
		}
		if (!empty($herbarios)) {
			//$descricao = $descricao." <i>Depositado em</i>: ".$herbarios.".";
		}
		if (!empty($tagnum)) {
			$descricao = $descricao." <b>Planta marcada no. ".$tagnum."</b>.";
		}
		//ENTAO É UMA UNICATA
		if ($ndups==1) {
			$descricao = $descricao." <b>UNICATA</b>.";
		}
		
		
		$descricao = trim($descricao);
		$nd = 1;
		while($nd<=$ndups) {

		$ln=5;
		if ($l%4==0 && $l>0) {
			$pdf->AddPage();
			$ll=0;
		}
		$x = $xxpos[$ll];
		$y = $yypos[$ll];
		$ll++;
		if ($l%2==0) {
			$rightmar = 108;
			$leftmar = 3;
			$txtmar = 45;
		} else {
			$rightmar = 3;
			$leftmar = 108;
			$txtmar = 145;
		}
		//if ($nspec==1) {$rightmar=3;}
		$pdf->SetXY($x,$y);
		$pdf->SetLeftMargin($leftmar);
		$pdf->SetRightMargin($rightmar);

		$pdf->Ln(2);
		$pdf->SetFont('Arial','',8);
		$pdf->WriteHTML("____________________________________________________________________",$ln);
		$pdf->Ln(2);

		$pdf->SetLeftMargin(($leftmar));
		if (!empty($projeto) && !empty($logofile) && $useprojectlog=='1') { 
			$fname = $logofile;
			$url = $prjurl;
		} else {
			$fname = 'icons/'.$herbariumlogo;
			$url = 'http://www.inpa.gov.br';
		}

		$pdf->Image($fname,$leftmar,$y+8,0,15,'',$url);
		$pdf->SetY($y+14);
		$pdf->SetLeftMargin($txtmar);
		$pdf->SetRightMargin($rightmar);
		$vartxt = $herbariosinpa." ".$herbariumsigla."      ";
		$pdf->SetFont('Arial','B',12);
		$ln=1;
		$pdf->WriteHTML($vartxt,$ln);
		$pdf->SetFont('Arial','',10);
		$pdf->Ln(4);
		$vartxt2 = "Manaus-Amazonas-Brazil";
		$pdf->WriteHTML($vartxt2,$ln);
		$pdf->Ln(5);
		$pdf->SetLeftMargin($leftmar);
		$pdf->Ln(4);
		if (!empty($familia)) {
			$pdf->SetFont('Arial','B',12);
			$pdf->Cell(65,5,$familia,0,0,'L');
		}
		$pdf->SetFont('Arial','B',10);
		if ($herbnum>0) {
			$pdf->Cell(30,5,$herbariumsigla.": ".$herbnum,0,1,'R');
		} else {
			$pdf->Ln(5);
		}
		$ln=5;
		if (!empty($detnome)) {
			$pdf->Ln(2);
			$pdf->SetFont('Arial','B',10);
			$pdf->WriteHTML($detnome,$ln);
		}
		if (!empty($detdetby)) {
			$pdf->Ln(5);
			$ln=4;
			$pdf->SetFont('Arial','',8);
			$pdf->WriteHTML("         Det: ".$detdetby.".",$ln);
		}
		$pdf->Ln(7);
		$ln = 4;
		$pdf->SetFont('Arial','',9);
		$pdf->WriteHTML($locality,$ln);
			$lenhab = strlen($habitat);
			$lendesc = strlen($descricao);

			$lenboth = $lenhab+$lendesc;
			$habitat = trim($habitat);
			if (!empty($habitat) && $habitat!=".") {
				if ($lenboth>1000) {
					$pdf->SetFont('Arial','',8);
					$ln = 4;
				} else {
					$ln = 4;
					$pdf->SetFont('Arial','',9);
				}
				$pdf->Ln(6);
				$pdf->WriteHTML($habitat,$ln);
			}
			if (!empty($descricao) && $descricao!=".") {
				$ln = 4;
				if ($lenboth>1000) {
					$pdf->SetFont('Arial','',8);
					$ln = 3;
				} else {
					$pdf->SetFont('Arial','',9);
					$ln = 4;
				}
				$pdf->Ln(6);
				$pdf->WriteHTML($descricao,$ln);
			}
			$ln=2;
			$pdf->Ln(3);
			$pdf->SetFont('Arial','',8);
			$pdf->WriteHTML("____________________________________________________________",$ln);
			$colref = "";
			if (!empty($coletor)) {
				$colref .= $coletor." ".$numcol;
			}
			$lgntxt = "abcdefghijklmnopqrstuvxwyzabcdefghijklmnopqrstuvxy";
			$nl = strlen($lgntxt);
			$colref2= '';
			if (!empty($datacol)) {
				$colref2 = "  ".$datacol." ";
				$txtlg = strlen($colref.$colref2);
				$n_sbp = $nl-$txtlg;
				for ($i=1;$i<=$n_sbp;$i++) {
					$colref .= "  ";
				}
				$colref .= $colref2;
			}
			$ln=5;
			$pdf->Ln(3);
			$pdf->SetFont('Arial','B',10);
			$pdf->WriteHTML($colref,$ln);
			if (!empty($addcol)) {
				$ln=3;
				$pdf->Ln(5);
				$pdf->SetFont('Arial','',8);
				$pdf->WriteHTML("& ".$addcol,$ln);
			}
			$pdf->Ln(2);
			$pdf->SetFont('Arial','',8);
			$pdf->WriteHTML("____________________________________________________________",$ln);
			$pdf->Ln(5);
			if (!empty($projeto)) {
				if (!empty($logofile)) { 
					$fname = $logofile;
					$url = $prjurl;
					$tmh = getimagesize($fname);
					$nw = ($tmh[0]*10)/$tmh[1];
					$pdf->Image($fname,$leftmar+2,$pdf->getY(),0,10,'',$url);
					$pdf->SetY($pdf->getY()+0.5);
					$txtmar = $leftmar+2+$nw+1;
					$pdf->SetLeftMargin($txtmar);
				}
			$ln=3;
			$pdf->SetFont('Arial','',7);
			$pdf->WriteHTML($projeto,$ln);
			}
			$l++;
			flush();
			ob_flush();

			$nd++;

			}

	}
			ob_end_clean();
			@unlink("temp/".$filename);
			$pdf->Output("temp/".$filename,'F');
			//$pdf->Output();
}  //end print specimen

if ($mini_label==1) {
	$conn_utf8 = ConectaDB($dbname);
	mysql_set_charset('utf8',$conn_utf8); 

	$filename_mini = $_SESSION['sessiondate']."_".substr(session_id(),0,10)."_mini.pdf";
	$textonly=TRUE;
	if ($textonly) {

	//mysql_set_charset('uft8');
	$qq = "SELECT * FROM ".$temptable;
	//echo $qq."<br />";
	$res = mysql_query($qq,$conn_utf8);
	$l=0;
	$zz = explode("/",$_SERVER['SCRIPT_NAME']);
	while ($rw = mysql_fetch_assoc($res)) {
		$imgf = curPageURL()."/".$zz[1]."/";
		$l++;
		$numcol = $rw['numcol'];
		//$datacol = $rw['datacol'];
		$collyy = $rw['year']+0;
		$collmm = $rw['month']+0;
		$colldd = $rw['day']+0;
		if ($colldd>0) {
			$datacol = $colldd.'-'.$collmm.'-'.$collyy;
		} 
		else {
			if ($collmm>0) {
				$datacol = $collmm.'-'.$collyy;
			} 
			else {
				if ($collyy>0) {
					$datacol = $collyy;
				} 
				else {
					$datacol = '';
				}
			}
		}
		$detnome = $rw['detnome'];
		$familia = $rw['familia'];
		$herbnum = $rw['herbnum'];
		$coletororginal = $rw['coletor'];
		$coletor = str_replace(".","",$rw['coletor']);
		$coletor = str_replace(",","_",$coletor);

		$projeto = $rw['projeto'];
		$tagnum = $rw['tagnum'];

		//$data =  RemoveAcentos($coletor)." ".$numcol." ".$datacol;
		//echo $data;
		//$ncc = replacenumbersbychar($numcol);
	    $data = tiraacentos($coletororginal)."_".$numcol."_".$datacol;

		$ffile = "img/barcodes/";
	    $ffile .= tiraacentos($coletor)."_".$numcol."_".$datacol.".png";
	    
	    //$familia."_".tiraacentos($coletor)."_".$numcol;

		//echo $ffile."<br />";
		if (!file_exists($ffile)) {
			$code = 8;
			$backend = 'IMAGEPNG';
			$modwidth=1;
			$height=70;
			$scale=2;
			$file = $ffile;
			$notext = TRUE;
			//$imgf .= "barcoding.php?code=8&data='".$codetxt."'&backend=IMAGEPNG&modwidth=1&height=70&scale=1&file=$ffile";
			include "barcoding.php";
			//echo "<b>$ffile  produzida</b><br />";
		} else {
			//include 'barcoding.php';
			//echo $ffile."  JÁ EXISTE<br />";
			//echo "<img src='".$ffile."'>";
		}
	}

	#especificacao para AveryL7651
	$pdf = new PDF_Label(array('paper-size'=>'A4', 'metric'=>'mm', 'marginLeft'=>6.1, 'marginTop'=>10.9, 'NX'=>5, 'NY'=>13, 'SpaceX'=>1.375, 'SpaceY'=>0, 'width'=>38.1, 'height'=>21.2, 'font-size'=>7));
	$pdf->AddPage();
	//mysql_set_charset('latin1');
	$qq = "SELECT * FROM ".$temptable;
	$res = mysql_query($qq,$conn_utf8);
	$l=0;
	$zz = explode("/",$_SERVER['SCRIPT_NAME']);
	while ($rw = mysql_fetch_assoc($res)) {
		$imgf = curPageURL()."/".$zz[1]."/";
		$l++;
		$numcol = $rw['numcol'];
		//$datacol = $rw['datacol'];
		$collyy = $rw['year']+0;
		$collmm = $rw['month']+0;
		$colldd = $rw['day']+0;
		if ($colldd>0) {
			$datacol = $colldd.'-'.$collmm.'-'.$collyy;
		} 
		else {
			if ($collmm>0) {
				$datacol = $collmm.'-'.$collyy;
			} 
			else {
				if ($collyy>0) {
					$datacol = $collyy;
				} 
				else {
					$datacol = '';
				}
			}
		}
		$detnome = $rw['detnome'];
		$familia = $rw['familia'];
		$herbnum = $rw['herbnum'];
		$coletororginal = $rw['coletor'];
		$coletor = str_replace(".","",$rw['coletor']);
		$coletor = str_replace(",","_",$coletor);

		$projeto = $rw['projeto'];
		$tagnum = $rw['tagnum'];

		//$label =  $familia."\n".$coletororginal." ".$numcol."\n".$datacol;
		//echo $data;
		//$ncc = replacenumbersbychar($numcol);
	    //$data = $coletororginal."_".$numcol."_".$datacol;

		$ffile = "img/barcodes/";
	    $ffile .= tiraacentos($coletor)."_".$numcol."_".$datacol.".png";
	    //echo $ffile."<br />";
	    //echo "<img src='".$ffile."'/>";
		//$pdf->Image($ffile,10,10,30,0,'png','');
		$txt =  "  ".tiraacentos($coletororginal)." ".$numcol."\n  ".$datacol."   ".$familia;
		//$pdf->SetFont('Arial','R',8);
		$pdf -> Add_ImageLabelWide($ffile,36,8,$ty='png', $txt,7);
		//$pdf->Add_Label($text);
	}

	@unlink("temp/".$filename_mini);
	$pdf->Output("temp/".$filename_mini,'F');
	} 
} //end mini

if ($det_label==1) {
	$conn_latin1 = ConectaDB($dbname);
	mysql_set_charset('latin1',$conn_latin1);
	$filename_det = $_SESSION['sessiondate']."_".substr(session_id(),0,10)."_det.pdf";

	//mysql_set_charset('latin1');
	//$qq = "SELECT * FROM ".$temptable;
	//$res = mysql_query($qq,$conn_latin1);
	//$l=0;
	$zz = explode("/",$_SERVER['SCRIPT_NAME']);

	$pimaco = array(
		array('name' => 'A4 3x11 PIMACO_256-356',  'paper-size'=>'A4',  'metric'=>'mm',  'marginLeft'=>7.11,  'marginTop'=>8.89,  'NX'=>3,  'NY'=>11,  'SpaceX'=>2.922,  'SpaceY'=>0,  'width'=>63.5,  'height'=>25.4,  'font-size'=>10),
 		array('name' => 'A4 5x13 PIMACO_251-351',  'paper-size'=>'A4',  'metric'=>'mm',  'marginLeft'=>4.572,  'marginTop'=>10.67,  'NX'=>5,  'NY'=>13,  'SpaceX'=>3.11,  'SpaceY'=>0,  'width'=>38.1,  'height'=>21.082,  'font-size'=>8),
 		array('name' => 'A4 2x11 PIMACO_254-354',  'paper-size'=>'A4',  'metric'=>'mm',  'marginLeft'=>4.572,  'marginTop'=>8.636,  'NX'=>2,  'NY'=>11,  'SpaceX'=>2.413,  'SpaceY'=>0,  'width'=>99.06,  'height'=>25.4,  'font-size'=>10)
	);
	//echopre($pimaco);

	$pdf = new fpdf_multicelltag($pimaco[0]);
	$pdf->AddPage();
	//mysql_set_charset('latin1');
	$qq = "SELECT * FROM ".$temptable;
	$res = mysql_query($qq,$conn_latin1);
	$l=0;
	$zz = explode("/",$_SERVER['SCRIPT_NAME']);
	$pdf->SetFont('times','',10);
	$pdf->SetStyle("i","times","I",10,'black');
	$pdf->SetStyle("b","times","B",10,'black');
	$pdf->SetStyle("familia","times","B",10,'black');
	$pdf->SetStyle("colid","times","",9,'black');
	$pdf->SetStyle("detdetby","times","",9,'black');
	while ($rw = mysql_fetch_assoc($res)) {
		$imgf = curPageURL()."/".$zz[1]."/";
		$l++;
		$numcol = $rw['numcol'];
		//$datacol = $rw['datacol'];
		$collyy = $rw['year']+0;
		$collmm = $rw['month']+0;
		$colldd = $rw['day']+0;
		if ($colldd>0) {
			$datacol = $colldd.'-'.$collmm.'-'.$collyy;
		} 
		else {
			if ($collmm>0) {
				$datacol = $collmm.'-'.$collyy;
			} 
			else {
				if ($collyy>0) {
					$datacol = $collyy;
				} 
				else {
					$datacol = '';
				}
			}
		}
		$detnome = $rw['detnome'];
		$detdetby = $rw['detdetby'];
		$familia = $rw['familia'];
		$herbnum = $rw['herbnum'];
		$coletororginal = $rw['coletor'];
		$coletor = str_replace(".","",$rw['coletor']);
		$coletor = str_replace(",","_",$coletor);

		$projeto = $rw['projeto'];
		$tagnum = $rw['tagnum'];

		//$label =  $familia."\n".$coletororginal." ".$numcol."\n".$datacol;
		//echo $data;
		//$ncc = replacenumbersbychar($numcol);
	    //$data = $coletororginal."_".$numcol."_".$datacol;

		//$ffile = "img/barcodes/";
	    //$ffile .= tiraacentos($coletor)."_".$numcol."_".$datacol.".png";
	    //echo $ffile."<br />";
	    //echo "<img src='".$ffile."'/>";
		//$pdf->Image($ffile,10,10,30,0,'png','');
		//$pdf->SetFont('Arial','R',8);
		//$pdf -> Add_ImageLabelWide($ffile,36,8,$ty='png', $txt,7);
		//echo $txt."<br />";
		//SetStyle($tag,$family,$style,$size,$color)

		$txt =  "<colid>".$coletororginal." ".$numcol."</colid>    <familia>".strtoupper($familia)."</familia>\n".$detnome."\n<detdetby><b>Det</b>: ".$detdetby."</detdetby>";
		$pdf->Add_Label_Tag($txt);
	}

	@unlink("temp/".$filename_det);
	$pdf->Output("temp/".$filename_det,'F');
} //end det

FazHeader($title,$body,$which_css,$which_java,$menu);

////echo memory_get_usage()."memory inicio";
$qq = "DROP TABLE $temptable";
@mysql_query($qq,$conn_latin1);

$zz = explode("/",$_SERVER['SCRIPT_NAME']);
if ($spec_label==1) {
$href = curPageURL()."/".$zz[1]."/temp/".$filename;
echo "<p >Clique para baixar etiquetas de herbário! <a  href='".$href."' target='_blank'>PDF</a> </p>";
}
if ($mini_label==1) {
$href = curPageURL()."/".$zz[1]."/temp/".$filename_mini;
echo "<p >Clique para baixar mini etiquetas com código de barras <a  href='".$href."' target='_blank'>PDF</a></p>";
}
if ($det_label==1) {
$href = curPageURL()."/".$zz[1]."/temp/".$filename_det;
echo "<p >Clique para baixar etiquetas com determinacoes (5 x 13) <a  href='".$href."' target='_blank'>PDF</a></p>";
}

$which_java = array("<script type='text/javascript' src='javascript/myjavascripts.js'></script>"
//,"<!-- Create Menu Settings: (Menu ID, Is Vertical, Show Timer, Hide Timer, On Click ('all' or 'lev2'), Right to Left, Horizontal Subs, Flush Left, Flush Top) -->",
//"<script type='text/javascript'>qm_create(0,false,0,500,false,false,false,false,false);</script>"
);
FazFooter($which_java,$calendar=FALSE,$footer=$menu);
ini_set("allow_url_fopen", 0);

?>