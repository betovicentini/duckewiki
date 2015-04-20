<?php
session_start();
//INCLUI FUNCOES PHP E VARIAVEIS
include "functions/HeaderFooter.php";
include "functions/MyPhpFunctions.php";
require  "fpdf16/writehtml.php";

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

class FOOTERPDF extends PDF
{
//Page header
function Header()
{
    $this->SetFont('Times','',14);
	$vartoprint = strip_tags("   Identificador:_________________________________  <b>".GetLangVar('namedata')."</b>: _____/____/______");
	$ll = strlen($vartoprint);
    $this->SetFillColor(235, 245, 233);
    $message = iconv('UTF-8', 'windows-1252',$vartoprint.'    Pg. '.$this->PageNo().'/{nb}');
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
    $userstring = iconv('UTF-8', 'windows-1252',$userstring);
    $this->Cell(0,10,"Impresso por ".$userstring." ",0,0,'L');
    $this->Cell(0,10,'Pg.'.$this->PageNo().'/{nb}',0,0,'R');

}

}
//$qq = "SET NAMES latin1";
//mysql_query($qq,$conn);

$pdf = new FOOTERPDF('P', 'mm', array(210,290));
//$pdf->SetAutoPageBreak(10,0) ;
$pdf->AliasNbPages();
$pdf->SetMargins(8,15,8);
$pdf->AddPage();
if ($formid>0) {
		$conn_latin1 = ConectaDB($dbname);
		mysql_set_charset('latin1',$conn_latin1);
		$qq = "SELECT * FROM Formularios WHERE FormID='$formid'";
		$rf = mysql_query($qq,$conn_latin1);
		$resf= mysql_fetch_assoc($rf);

		$formname = $resf['FormName'];
		$fieldids = explode(";",$resf['FormFieldsIDS']);
		$qq = "SELECT * FROM Traits WHERE (";
		$i=0;
		foreach ($fieldids as $key => $value) {
				if ($i==0) {
					$qq = $qq." TraitID='".$value."'";
				} else {
					$qq = $qq." OR TraitID='".$value."'";
				}
				$i++;
		}
		$qq = $qq.") AND TraitTipo<>'Variavel|Imagem' ORDER BY PathName";
		$rr = mysql_query($qq,$conn_latin1);
		$pdf->SetFont('Times','',10);
		$variarn = 0;

		$ln =5; //line space
		while ($row= mysql_fetch_assoc($rr)) { //para cada variavel no relatorio
				$zz = explode("-",$row['PathName']);
				$trclass = trim($zz[0]);
				$pdf->Ln($ln);
			    $vartoprint = "<b>".$row['TraitName']." (".$trclass.")</b>";
			    $vartoprint = iconv('UTF-8', 'windows-1252',$vartoprint);
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
						if ($cr % 7 == 0 || $cr==0) {
							$pdf->Ln(5);
					    }
					    $vartoprint =  "\t[  ]-".$rw['TraitName']."\t";
					    $vartoprint = iconv('UTF-8', 'windows-1252',$vartoprint);
						$pdf->WriteHTML($vartoprint,$ln);
						$cr++;
					} 
				}
				//se quantitativo
				if ($row['TraitTipo']=='Variavel|Quantitativo') {
					$vartoprint =  " ______________________________________________<b>".strtolower(GetLangVar('medidoem'))."</b>:_______";
					$vartoprint = iconv('UTF-8', 'windows-1252',$vartoprint);
					$pdf->WriteHTML($vartoprint,$ln);
				}
				//se texto
				if ($row['TraitTipo']=='Variavel|Texto') {
					$vartoprint = " ____________________________________________________________________________________________________________________________________________________________________________________________________________________________________________________________________________________________________________________________________";
					$vartoprint = iconv('UTF-8', 'windows-1252',$vartoprint);
					$pdf->WriteHTML($vartoprint,$ln);

				}
		}//end of loop de cada variavel relatorio
		ob_get_clean();
		$pdf->Output();
} //if $formid
?>