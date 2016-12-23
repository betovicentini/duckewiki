<?php
set_time_limit(0);
ini_set("allow_url_fopen", 1);
ini_set("allow_url_include", 1);
ob_start();
session_start();

include "functions/HeaderFooter.php";
include "functions/MyPhpFunctions.php";
require  "fpdf16/writehtml.php";


class FOOTERPDF extends PDF
{
//Page header
function Header()
{
    $this->SetFont('Times','',14);
	$vartoprint = strip_tags("   Identificador:_________________________________  <b>".GetLangVar('namedata')."</b>: _____/____/______");
	$ll = strlen($vartoprint);
    $this->SetFillColor(235, 245, 233);
    $message = $vartoprint.'    Pg. '.$this->PageNo().'/{nb}';
    //$message = iconv('UTF-8', 'windows-1252',$vartoprint.'    Pg. '.$this->PageNo().'/{nb}');
    $this->Cell(0,10,$message,1,0,'C',1);
	$var = "Dados coletados por?";
	$ll2 = strlen($var);
	$tt = str_repeat("_", $ll-$ll2);
	$var = $var.$tt;
	$this->Ln(12);
    $this->Cell(0,10,$var,1,0,'C',1);
	$this->Ln(10);

}

//Page footer
function Footer()
{
    //Position at 1.5 cm from bottom
    $this->SetY(-10);
    $this->SetFont('Times','',8);
    //Page number
    $userstring = strip_tags("<i>".$_SESSION['userfirstname']." ".$_SESSION['userlastname']." (".$_SESSION['sessiondate'].")</i>");
    //$userstring = iconv('UTF-8', 'windows-1252',$userstring);
    $this->Cell(0,10,"Impresso por ".$userstring." ",0,0,'L');
    $this->Cell(0,10,'Pg.'.$this->PageNo().'/{nb}',0,0,'R');

}

}

//echopre($_SESSION);
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
$menu = FALSE;
$which_css = array("<link href='css/geral.css' rel='stylesheet' type='text/css' />");
$which_java = array();
$title = 'Imprimir Formulário';
$body = '';
//FazHeader($title,$body,$which_css,$which_java,$menu);

//echopre($ppost);

//$qq = "SET NAMES latin1";
//mysql_query($qq,$conn);
$pdf = new FOOTERPDF('P', 'mm', array(210,290));
$pdf->SetAutoPageBreak(1,20) ;
$pdf->AliasNbPages();
$pdf->SetMargins(8,15,8);
$pdf->AddPage();
if ($formid>0) {
		$conn_latin1 = ConectaDB($dbname);
		mysql_set_charset('latin1',$conn_latin1);
		$qq = "SELECT * FROM Formularios WHERE FormID=".$formid;
		$rf = mysql_query($qq,$conn_latin1);
		$resf= mysql_fetch_assoc($rf);

		$formname = $resf['FormName'];
		#$fieldids = explode(";",$resf['FormFieldsIDS']);
		$qq = "SELECT tr.* FROM FormulariosTraitsList as ff JOIN Traits as tr USING(TraitID) WHERE ff.FormID=".$formid." AND tr.TraitTipo<>'Variavel|Imagem' ORDER BY ff.Ordem";
		//echo $qq."<br />";
		$rr = mysql_query($qq,$conn_latin1);
		$pdf->SetFont('Times','',10);
		$variarn = 0;

		$ln =6; //line space
		while ($row= mysql_fetch_assoc($rr)) { //para cada variavel no relatorio
				$zz = explode("-",$row['PathName']);
				$trclass = trim($zz[0]);
				$pdf->Ln($ln);
			    $vartoprint = "<b>".$row['TraitName']." (".$trclass.")</b>";
			    //$vartoprint = iconv('UTF-8', 'windows-1252',$vartoprint);
			    //echo $vartoprint."<br />";
			    //Line(float x1, float y1, float x2, float y2)
			    //$pdf->Line(20,getX(), 210-20, getX());
				$pdf->WriteHTML($vartoprint,$ln);

				//se categoria
				if ($row['TraitTipo']=='Variavel|Categoria') {
					//opcoes de variaves categoricas
					//mysql_set_charset('latin1');
					$qq = "SELECT * FROM Traits WHERE ParentID='".$row['TraitID']."' ORDER BY TraitName";
					$res = mysql_query($qq,$conn_latin1);
					$nres = mysql_numrows($res);
					$cr = 0;
					while ($rw= mysql_fetch_assoc($res)) { //para cada estado de variacao
						if ($nres>5) {
						if ($cr % 7 == 0 || $cr==0) {
							$pdf->Ln(5);
					    }
					    }
					    $vartoprint =  "\t[  ]-".$rw['TraitName']."\t";
						//$vartoprint = iconv('UTF-8', 'windows-1252',$vartoprint);
						$pdf->WriteHTML($vartoprint,$ln);
						//echo $vartoprint."<br />";

						$cr++;
					} 
				}
				//se quantitativo
				if ($row['TraitTipo']=='Variavel|Quantitativo') {
					$vartoprint =  " ________________________<b>".$row['TraitUnit']."</b> (ou  _______)";
					//$vartoprint = iconv('UTF-8', 'windows-1252',$vartoprint);
					$pdf->WriteHTML($vartoprint,$ln);
					//echo $vartoprint."<br />";

				}
				//se texto
				if ($row['TraitTipo']=='Variavel|Texto') {
					$vartoprint = " _____________________________________________________________________________________________________________________________________________________________________________________________________________________________________________________________________________________________________________";
					//$vartoprint = iconv('UTF-8', 'windows-1252',$vartoprint);
					$pdf->WriteHTML($vartoprint,$ln);
					//echo $vartoprint."<br />";

				}
		}//end of loop de cada variavel relatorio
		ob_get_clean();
		$pdf->Output();
} //if $formid

$which_java = array(
"<script type='text/javascript' src='javascript/myjavascripts.js'></script>");
//FazFooter($which_java,$calendar=FALSE,$footer=$menu);

?>