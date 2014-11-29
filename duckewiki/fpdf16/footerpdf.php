<?php
include('writehtml.php');

class FOOTERPDF extends PDF
{
//Page header
function Header()
{
    $this->SetFont('Times','',14);
	$vartoprint = strip_tags("<b>".GetLangVar('nameamostra').":</b>______________________________________  <b>".GetLangVar('namedata')."</b>: _____/____/______");	
    $this->SetFillColor(235, 245, 233);
    $this->Cell(0,10,$vartoprint.'    Pg. '.$this->PageNo().'/{nb}',1,0,'C',1);
	$this->Ln(15);
	//$this->WriteHTML($vartoprint);
}

//Page footer
function Footer()
{
	$userdatestring = strip_tags("<i>".$_SESSION['userfirstname']." ".$_SESSION['userlastname']." (".$_SESSION['sessiondate'].")</i>");
    //Position at 1.5 cm from bottom
    $this->SetY(-10);
    //Arial italic 8
    $this->SetFont('Arial','I',8);
    //Page number
    $this->Cell(0,10,"Impresso por ".$userdatestring." ",0,0,'L');
    $this->Cell(0,10,'Pg.'.$this->PageNo().'/{nb}',0,0,'R');

}

}
?>