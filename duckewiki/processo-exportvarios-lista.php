<?php
session_start();
//INCLUI FUNCOES PHP E VARIAVEIS
include "functions/HeaderFooter.php";
include "functions/MyPhpFunctions.php";
//require  "fpdf16/writehtml.php";
require  "fpdf16/mysql_table.php";
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
$_SESSION['herbario'] = $herbariumsigla;
$_SESSION['metatitle'] = $metatitle;
class myPDF extends PDF_MySQL_Table
{
function Header()
{
    $this->SetFont('Times','B',12);
    $txt = $_SESSION['metatitle'];
    $txt = iconv('UTF-8', 'windows-1252',$txt);
    $this->Cell(0,10,$txt,0,0,'L',0);
    $this->Ln(8);    
    $this->SetFont('Times','B',16);    
    $txt  = "EXSICATAS  PARA  ".$_SESSION['herbario'];
    $txt = iconv('UTF-8', 'windows-1252',$txt);
	//$ll = strlen($vartoprint);
    $this->SetFillColor(235, 245, 233);
    //$this->Cell(0,10,$vartoprint.'    Pg. '.$this->PageNo().'/{nb}',0,0,'L',0);
	$this->Cell(0,10,$txt,0,0,'L',0);    
    $this->SetFont('Times','B',12);    
    $this->Cell(0,10,$_SESSION['sessiondate'].'            Pg. '.$this->PageNo().'/{nb}',0,0,'R');    
	$this->Ln(8);

}

//Page footer
//function Footer()
//{
//    //Position at 1.5 cm from bottom
//    //var_dump(headers_list());
//    //ob_clean();
//    $this->SetY(-10);
//    $this->SetFont('Times','I',8);
//    //Page number
//    $userstring = $_SESSION['userfirstname']." ".$_SESSION['userlastname']." (".$_SESSION['sessiondate'].")";
//    $this->Cell(0,10,"Impresso por ".$userstring." ",0,0,'L');
//    $this->Cell(0,10,'Pg.'.$this->PageNo().'/{nb}',0,0,'R');
//     //Ensure table header is output
//    parent::Footer();
//}
}
$conn_latin1 = ConectaDB($dbname);
mysql_set_charset('latin1',$conn_latin1);


//DEFINE O QUERY
if ($filtroid>0) {
$qq1 = "SELECT DISTINCT CONCAT(pess.Prenome,' ',pess.SegundoNome,' ',pess.Sobrenome) as ToTheAttention,espe.Familias,COUNT(*) as NumberSamples FROM Especimenes as spec LEFT JOIN Identidade as idd ON idd.DetID=spec.DetID LEFT JOIN Especialistas as espe ON espe.FamiliaID=idd.FamiliaID LEFT JOIN Pessoas as pess ON pess.PessoaID=espe.Especialista  LEFT JOIN Pessoas as cole ON cole.PessoaID=spec.ColetorID LEFT JOIN Tax_Familias as famtb ON famtb.FamiliaID=idd.FamiliaID WHERE spec.FiltrosIDS LIKE '%filtroid_".$filtroid.";%' OR spec.FiltrosIDS LIKE '%filtroid_".$filtroid."' GROUP BY pess.PessoaID";
} elseif (!empty($processoid)) {
$qq1 = "SELECT DISTINCT CONCAT(pess.Prenome,' ',pess.SegundoNome,' ',pess.Sobrenome) as ToTheAttention,espe.Familias,COUNT(*) as NumberSamples FROM ProcessosLIST as prcc JOIN Especimenes as spec ON spec.EspecimenID=prcc.EspecimenID LEFT JOIN Identidade as idd ON idd.DetID=spec.DetID LEFT JOIN Especialistas as espe ON espe.FamiliaID=idd.FamiliaID LEFT JOIN Pessoas as pess ON pess.PessoaID=espe.Especialista  LEFT JOIN Pessoas as cole ON cole.PessoaID=spec.ColetorID LEFT JOIN Tax_Familias as famtb ON famtb.FamiliaID=idd.FamiliaID WHERE prcc.EXISTE=1 AND isvalidprocesso(prcc.ProcessoID,'".$processoid."')>0 AND isvalidherbaria('".$herbario."',spec.Herbaria)>0  AND espe.Herbarium='".$herbario."' GROUP BY pess.PessoaID ";
}
$r1 = mysql_query($qq1);
$nr1 = mysql_numrows($r1);

$pdf=new myPDF();
$pdf->AliasNbPages();
$pdf->AddPage();
$pdf->Ln(5);
if (!empty($message)) {
	//$pdf->Cell(0,15,$message,0,0,'L',0);
    $pdf->SetFont('Times','',14);    
	$message = iconv('UTF-8', 'windows-1252',$message);
	$pdf->WriteHTML($message,6);
}
$prop=array('HeaderColor'=>array(255,246,143),
//array(255,150,100),
            //'color1'=>array(210,245,255),
            'color1'=>array(224,238,238),
            //'color2'=>array(255,246,143),
            'padding'=>2,
            'align' => 'L',
            'columnwidths' => array('20%','20%')
            );
            
if ($nr1>0)  {
//$pdf->Ln(20);
//$pdf->Table($qq1,$prop,$fontsize=8);
}
$pdf->Ln(20);
$prop=array('HeaderColor'=>array(255,246,143),
//array(255,150,100),
            //'color1'=>array(210,245,255),
            'color1'=>array(224,238,238),
            //'color2'=>array(255,246,143),
            'padding'=>2,
            'columnwidths' => array('7%','7%', '7%','16%','12%','15%','24%','15%')
            );
            //,'20%'
// && !empty($herbario)            
if (!empty($processoid)) {
///LISTA OS ESPECIALISTAS ENVOLVIDOS NO HERBARIO
$qq = "SELECT \"[__] [__]\" as TG";
if ($duplicatesTraitID>0) {
$qq .= ", traitvaluespecs(".$duplicatesTraitID.", 0, spec.EspecimenID,'', 0, 0)+0 as DUPS";
}
$qq .= ", spec.INPA_ID as ".$herbariumsigla.", CONCAT(cole.Abreviacao,' ', spec.Number) as Specimen,CONCAT(spec.Ano,'-',spec.Mes,'-',spec.Day) as data,famtb.Familia as familia,
gettaxonname(spec.DetID,1,0) as nome, prsp.Name  as Processo ";
$qq .= " FROM ProcessosLIST as prcc JOIN ProcessosEspecs as prsp ON prsp.ProcessoID=prcc.ProcessoID LEFT JOIN Especimenes as spec ON spec.EspecimenID=prcc.EspecimenID LEFT JOIN Identidade as idd ON idd.DetID=spec.DetID LEFT JOIN Pessoas as cole ON cole.PessoaID=spec.ColetorID LEFT JOIN Tax_Familias as famtb ON famtb.FamiliaID=idd.FamiliaID WHERE prcc.EXISTE=1 AND isvalidprocesso(prcc.ProcessoID,'".$processoid."')>0 ORDER BY cole.Abreviacao, spec.Number ASC";
//INPA_ID ASC";
//echo $qq."<br >";
//$pdf->WriteHTML($qq,5);

$pdf->Table($qq,$prop,$fontsize=8);
} elseif ($filtroid>0) {
///LISTA OS ESPECIALISTAS ENVOLVIDOS NO HERBARIO
$qq = "SELECT \"[__] [__]\" as TG";
if ($duplicatesTraitID>0) {
$qq .= ", traitvaluespecs(".$duplicatesTraitID.", 0, spec.EspecimenID,'', 0, 0)+0 as DUPS";
}
$qq .= ", spec.INPA_ID as ".$herbariumsigla.", CONCAT(cole.Abreviacao,' ', spec.Number) as Specimen,CONCAT(spec.Ano,'-',spec.Mes,'-',spec.Day) as data,famtb.Familia as familia,
gettaxonname(spec.DetID,1,0) as nome";
$qq .= " FROM Especimenes as spec LEFT JOIN Identidade as idd ON idd.DetID=spec.DetID LEFT JOIN Pessoas as cole ON cole.PessoaID=spec.ColetorID LEFT JOIN Tax_Familias as famtb ON famtb.FamiliaID=idd.FamiliaID WHERE spec.FiltrosIDS LIKE '%filtroid_".$filtroid.";%' OR spec.FiltrosIDS LIKE '%filtroid_".$filtroid."'  ORDER BY cole.Abreviacao, spec.Number ASC";
//echo $qq."<br >";
//$pdf->WriteHTML($qq,5);
$prop=array('HeaderColor'=>array(255,246,143),
//array(255,150,100),
            //'color1'=>array(210,245,255),
            'color1'=>array(224,238,238),
            //'color2'=>array(255,246,143),
            'padding'=>2,
            'columnwidths' => array('7%','7%', '7%','16%','12%','15%','35%',)
            );
$pdf->Table($qq,$prop,$fontsize=8);

}
//ob_clean();
$pdf->Output();

?>