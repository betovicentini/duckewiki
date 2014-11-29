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

$lang = $_SESSION['lang'];
$dbname = $_SESSION['dbname'];
$conn = ConectaDB($dbname);

$ppost = cleangetpost($_POST,$conn);
$arval = $ppost;
@extract($ppost);
$gget = cleangetpost($_GET,$conn);
@extract($gget);


//$tbname = "temp_lab".substr(session_id(),0,10)."_".$specimenid;
$conn_latin1 = ConectaDB($dbname);
@mysql_set_charset('latin1',$conn_latin1);

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


	$qq = "SELECT * FROM ".$temptable;
	$res = mysql_query($qq,$conn_latin1);
	$l=0;

	while ($rw = mysql_fetch_assoc($res)) {
		$numcol = $rw['numcol'];
		$datacol = $rw['datacol'];
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


		if ($descricao==".") {
			$descricao='';
		}
		if (!empty($vernacular)) {
			$descricao = $descricao." <i>Vernacular</i>: ".$vernacular.".";
		}
		if (!empty($herbarios)) {
			$descricao = $descricao." <i>Depositado em</i>: ".$herbarios.".";
		}
		if (!empty($tagnum)) {
			$descricao = $descricao." <b>Planta marcada no. ".$tagnum."</b>.";
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
				$fname = 'icons/inpa_principal.jpg';
				$url = 'http://www.inpa.gov.br';
			}
			$pdf->Image($fname,$leftmar,$y+8,0,15,'',$url);
			$pdf->SetY($y+14);
			$pdf->SetLeftMargin($txtmar);
			$pdf->SetRightMargin($rightmar);
			$vartxt = $herbariosinpa." INPA      ";
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
				$pdf->Cell(30,5,"INPA: ".$herbnum,0,1,'R');
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
//ob_end_clean();
$filename = "temp_".substr(session_id(),0,10)."_label.pdf";

@unlink("temp/".$filename);
$pdf->Output("temp/".$filename,'F');
//$pdf->Output();

//CABECALHO
$ispopup=1;
if ($ispopup==1) {
	$menu = FALSE;
} else {
	$menu = TRUE;
}
$which_css = array(
"<link href='css/geral.css' rel='stylesheet' type='text/css' />",
"<link rel='stylesheet' type='text/css' href='css/cssmenu.css' />"
);
$which_java = array(
"<script type='text/javascript' src='css/cssmenuCore.js'></script>",
"<script type='text/javascript' src='css/cssmenuAddOns.js'></script>",
"<script type='text/javascript' src='css/cssmenuAddOnsItemBullet.js'></script>"
);
$title = 'Listar espécies';
$body = '';
FazHeader($title,$body,$which_css,$which_java,$menu);

////echo memory_get_usage()."memory inicio";
$qq = "DROP TABLE $temptable";
@mysql_query($qq,$conn_latin1);

$zz = explode("/",$_SERVER['SCRIPT_NAME']);
$href = curPageURL()."/".$zz[1]."/temp/".$filename;
echo "
<table align='center'>
<tr>
<td><a  href='".$href."'><img src='icons/pdf_icon.jpg' height='30' onclick='javascript:this.window.close()'></a></td>
<td valign='middle'><a  href='".$href."'>Etiquetas da amostra</a></td>
</tr>
</table>";
$which_java = array("<script type='text/javascript' src='javascript/myjavascripts.js'></script>",
"<!-- Create Menu Settings: (Menu ID, Is Vertical, Show Timer, Hide Timer, On Click ('all' or 'lev2'), Right to Left, Horizontal Subs, Flush Left, Flush Top) -->",
"<script type='text/javascript'>qm_create(0,false,0,500,false,false,false,false,false);</script>");
FazFooter($which_java,$calendar=FALSE,$footer=$menu);

ini_set("allow_url_fopen", 0);


?>