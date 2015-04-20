<?php
session_start();
//INCLUI FUNCOES PHP E VARIAVEIS
include "functions/HeaderFooter.php";
include "functions/MyPhpFunctions.php";
//define('FPDF_FONTPATH','fpdf16/font');
require  "fpdf16/writehtml.php";
//require  "fpdf16/PDF_Label.php";
//require_once('fpdf16/php-barcode-2.0.1.php');
//require_once("fpdf16/MultiCellTag/class.multicelltag.php");

/////////DEFINE THE FUNCTION THAT GENERATES FILE LABELS
//function savelabeltofile($especimenID,$path,$duplicatesTraitID,$formnotes,$formidhabitat, $conn) {}


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

$qd = "SET lc_time_names = 'pt_BR'";
@mysql_query($qd,$conn);
//echo $qq."<br /><br />";
$conn_latin1 = ConectaDB($dbname);
@mysql_set_charset('latin1',$conn_latin1);
//echo $FPDF_FONTPATH." aqui aqui ";
$qz = "SELECT tr.TraitVariation,tr.EspecimenID FROM Traits_variation AS tr JOIN ProcessosLIST as prcc ON prcc.EspecimenID=tr.EspecimenID WHERE prcc.".$herbariumsigla.">0 AND prcc.EXISTE=1 AND isvalidprocesso(prcc.ProcessoID,'".$processoid."')>0 AND tr.TraitID=".$exsicatatrait;
$rz = mysql_query($qz,$conn);
//echo $qz."<br />";
$nrz = mysql_numrows($rz);
$arquivos= array();
$pathetiqueta = "etiqueta_".$tbname;
@mkdir("temp/".$pathetiqueta, 0755);
if ($nrz>0) {
	//GERA AS ETIQUETAS
	$perc = 0;
	$idx = 0;
	while($rw = mysql_fetch_assoc($rz)) {
		$especimenid = $rw['EspecimenID'];
		//echopre($rw);
		//$herbariosinpa = $rw['herbariosinpa'];
		$amostras = explode(";",$rw['TraitVariation']);
		//pega uma imagem apenas se houver mais de uma
		$imgid = trim($amostras[0]);
		$qi = "SELECT * FROM `Imagens` WHERE ImageID=".$imgid;
		//echo '<br />'.$qi."<br /><br />";
		$ri = mysql_query($qi,$conn);
		$rwi = mysql_fetch_assoc($ri);
		$amostra = $rwi['FileName'];

		//GERA ETIQUETA PARA AMOSTRAS
$qq = " SELECT 
maintb.EspecimenID as wikid, 
colpessoa.Abreviacao as coletor, 
maintb.Ano as year,
maintb.Mes as month,
maintb.Day as day,
CONCAT(IF(maintb.Prefixo IS NULL OR maintb.Prefixo='','',CONCAT(maintb.Prefixo,'-')),maintb.Number,IF(maintb.Sufix IS NULL OR maintb.Sufix='','',CONCAT('-',maintb.Sufix))) as numcol, 
DATE_FORMAT(concat(IF(maintb.Ano>0,maintb.Ano,1),'-',IF(maintb.Mes>0,maintb.Mes,1),'-',IF(maintb.Day>0,maintb.Day,1)),'%d-%b-%Y') as datacol, 
addcolldescr(maintb.AddColIDS) as addcol";
$qq .= ", 
localidadestring(maintb.GazetteerID,maintb.GPSPointID,maintb.MunicipioID,maintb.ProvinceID,maintb.CountryID,maintb.Latitude,maintb.Longitude,maintb.Altitude) as locality, 
INPA_ID as herbnum,
maintb.Herbaria as herbarios,
plantatag(maintb.PlantaID) as tagnum";
if ($duplicatesTraitID>0) {
	$qq .= ", nduplicates(".$duplicatesTraitID.",EspecimenID,'Especimenes') as ndups";
} else {
	$qq .= ", 1 as ndups";
}
$qq .= ", labeldescricao(maintb.EspecimenID+0,maintb.PlantaID+0,".$formnotes.",TRUE,FALSE) as descricao";
$qq .=", famtb.Familia as familia";
$qq .=", IF(iddet.InfraEspecieID>0,CONCAT('<i>',gentb.Genero,' ',sptb.Especie,' </i> ',sptb.EspecieAutor,' <i>',infsptb.InfraEspecieNivel,' ',infsptb.InfraEspecie,'</i> ',infsptb.InfraEspecieAutor),IF(iddet.EspecieID>0,CONCAT('<i>',gentb.Genero,' ',sptb.Especie,'</i> ',sptb.EspecieAutor), IF(iddet.GeneroID>0,CONCAT('<i>',gentb.Genero,'<i>'),''))) as detnome";
$qq .= ", CONCAT(detpessoa.Abreviacao,' [',DATE_FORMAT(iddet.DetDate,'%d-%b-%Y'),']') as detdetby";
if ($formidhabitat>0) {
	$qq .= ", habitatstring(maintb.HabitatID, ".$formidhabitat.", TRUE,FALSE)  as habitat";
}
$qq .= ", vernaculars(maintb.VernacularIDS) as vernacular";
$qq .= ", projetostring(maintb.ProjetoID,TRUE,TRUE) as projeto";
$qq .= ", projetologo(maintb.ProjetoID) as logofile";
$qq .= ", projetourl(maintb.ProjetoID) as prjurl";
$qq .= " FROM Especimenes  as maintb";
$qq .= " LEFT JOIN Pessoas as colpessoa ON maintb.ColetorID=colpessoa.PessoaID";
$qq .= " LEFT JOIN Identidade as iddet ON maintb.DetID=iddet.DetID";
$qq .= " LEFT JOIN Tax_InfraEspecies as infsptb ON iddet.InfraEspecieID=infsptb.InfraEspecieID 
LEFT JOIN Tax_Especies as sptb ON iddet.EspecieID=sptb.EspecieID 
LEFT JOIN Tax_Generos as gentb ON iddet.GeneroID=gentb.GeneroID  
LEFT JOIN Tax_Familias as famtb ON iddet.FamiliaID=famtb.FamiliaID 
LEFT JOIN Pessoas as detpessoa ON detpessoa.PessoaID=iddet.DetbyID ";
$qq .= " WHERE maintb.EspecimenID=".$especimenid;
$res = mysql_query($qq,$conn_latin1);
$rw = mysql_fetch_assoc($res);

//COMECA UM PDF E DEFINE ALGUNS PARAMETROS
$l=0;
$ll=0;
$tamanho_x = 105;
$tamanho_y = 145;
$pdf = new PDF('P', 'mm', array($tamanho_x,$tamanho_y));
//$pdf->directory='temp/';
$pdf->SetAutoPageBreak(1,0) ;
$pdf->SetMargins(8,8,8);
$pdf->AddPage();
$pdf->SetFont('Helvetica','B',12);
$ln=5;  //tamanho vertical da linha
$x = 0;  //posicao X da etiqueta //pega do array que contém definicoes para quatro posicoes
$y = 0; //posicao Y da etiqueta //pega do array que contém definicoes para quatro posicoes
$rightmar = 3;  //margem
$leftmar = 3;
$txtmar = 45;
$pdf->SetXY($x,$y);
$pdf->SetLeftMargin($leftmar);
$pdf->SetRightMargin($rightmar);
$pdf->Ln(2);
$pdf->SetFont('Helvetica','',8);
$pdf->WriteHTML("____________________________________________________________________",$ln);
$pdf->Ln(2);
$pdf->SetLeftMargin(($leftmar));
//PEGA AS VARIAVEIS DA AMOSTRA PARA FAZER A ETIQUETA
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
$logofile = $rw['logofile'];
$prjurl = $rw['prjurl'];
$tagnum = $rw['tagnum'];
$useprojectlog =0;
$ndups = 1;

//COMECA A CONSTRUIR A DESCRICAO
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
if (!empty($projeto) && !empty($logofile) && $useprojectlog=='1') { 
	$fname = $logofile;
	$url = $prjurl;
} else {
	$fname = 'icons/'.$herbariumlogo;
	$url = 'http://www.inpa.gov.br';
}
//ADICIONA O LOGO
$pdf->Image($fname,$leftmar,$y+8,0,15,'',$url);
$pdf->SetY($y+14);
$pdf->SetLeftMargin($txtmar);
$pdf->SetRightMargin($rightmar);

$herbariosinpa = iconv('UTF-8', 'windows-1252',"HERBÁRIO");
$vartxt = $herbariosinpa." ".$herbariumsigla."      ";  ///PRECISA ADICIONAR ISSO COMO VARIAVEL
$pdf->SetFont('Helvetica','B',12);
$ln=1;
$pdf->WriteHTML($vartxt,$ln);
$pdf->SetFont('Helvetica','',10);
$pdf->Ln(4);
$vartxt2 = "Manaus-Amazonas-Brazil";   ///PRECISA ADICIONAR ISSO COMO VARIAVEL
$pdf->WriteHTML($vartxt2,$ln);
$pdf->Ln(5);
$pdf->SetLeftMargin($leftmar);
$pdf->Ln(4);
if (!empty($familia)) {
	$pdf->SetFont('Helvetica','B',12);
	$pdf->Cell(65,5,$familia,0,0,'L');
}
$pdf->SetFont('Helvetica','B',10);
//ADICIONA O NÚMERO DE HERBÁRIO
if ($herbnum>0) {
	$pdf->Cell(30,5,$herbariumsigla.": ".$herbnum,0,1,'R');
} else {
	$pdf->Ln(5);
}
$ln=5;
if (!empty($detnome)) {
	$pdf->Ln(2);
	$pdf->SetFont('Helvetica','B',10);
	$pdf->WriteHTML($detnome,$ln);
}
if (!empty($detdetby)) {
	$pdf->Ln(5);
	$ln=4;
	$pdf->SetFont('Helvetica','',8);
	$pdf->WriteHTML("         Det: ".$detdetby.".",$ln);
}
$pdf->Ln(7);
$ln = 4;
$pdf->SetFont('Helvetica','',9);
$pdf->WriteHTML($locality,$ln);
$lenhab = strlen($habitat);
$lendesc = strlen($descricao);
$lenboth = $lenhab+$lendesc;
$habitat = trim($habitat);
if (!empty($habitat) && $habitat!=".") {
	if ($lenboth>1000) {
		$pdf->SetFont('Helvetica','',8);
		$ln = 4;
	} else {
		$ln = 4;
		$pdf->SetFont('Helvetica','',9);
	}
	$pdf->Ln(6);
	$pdf->WriteHTML($habitat,$ln);
}
if (!empty($descricao) && $descricao!=".") {
	$ln = 4;
	if ($lenboth>1000) {
		$pdf->SetFont('Helvetica','',8);
		$ln = 3;
	} else {
		$pdf->SetFont('Helvetica','',9);
		$ln = 4;
	}
	$pdf->Ln(6);
	$pdf->WriteHTML($descricao,$ln);
}
$ln=2;
$pdf->Ln(3);
$pdf->SetFont('Helvetica','',8);
//$pdf->WriteHTML("____________________________________________________________",$ln);
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
$pdf->SetFont('Helvetica','B',10);
$pdf->WriteHTML($colref,$ln);
if (!empty($addcol)) {
	$ln=3;
	$pdf->Ln(5);
	$pdf->SetFont('Helvetica','',8);
	$pdf->WriteHTML("& ".$addcol,$ln);
}
$pdf->Ln(2);
$pdf->SetFont('Helvetica','',8);
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
$pdf->SetFont('Helvetica','',7);
$pdf->WriteHTML($projeto,$ln);
	}
//flush();
//ob_flush();


//SALVA NO ARQUIVO NA PASTA INDICADA
$filename = "INPA-".$herbnum."_label.pdf";
@unlink("temp/".$pathetiqueta.'/'.$filename);
//echo "<br />".$path.'/'.$filename."<br />";
$pdf->Output("temp/".$pathetiqueta.'/'.$filename,'F');

$jpgn = "temp/".$pathetiqueta."/INPA-".$herbnum."_label.jpg";
$href = "temp/".$pathetiqueta.'/'.$filename;
// Set the content type header - in this case image/jpeg
//$im = imagecreatefromjpeg ($href);
//imagejpeg($im,$jpgn,100); 
//$imagick = new Imagick(); 
//$imagick->readImage($href); 
//$imagick->writeImages($jpgn, false); 


$arquivos[] = array($amostra,$filename);

$perc =floor(($idx/($nrz))*100);
if ($perc==100) {$perc=99;}
$qnu = "UPDATE `temp_".$tbname."_progress` SET percentage=".$perc; 
@mysql_query($qnu);
$idx++;
}
$arquivos = array_values($arquivos);
$_SESSION['arquivos'] = serialize($arquivos);
}
$perc =100;
$qnu = "UPDATE `temp_".$tbname."_progress` SET percentage=".$perc; 
@mysql_query($qnu);
echo "Concluido";

?>