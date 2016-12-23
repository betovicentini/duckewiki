<?php

//define the Paragraph String ~~ Required by Multicell Class
define('PARAGRAPH_STRING', '~~~');

//Tag Based Multicell Class
require_once("class.multicelltag.php");

//Class Extention for header and footer
require_once("header_footer.inc");

$pdf = new pdf_usage();

$pdf->Open();
$pdf->SetMargins(20, 20, 20);
$pdf->AddPage();
$pdf->AliasNbPages(); 


$pdf->SetFont('arial','',11);
$pdf->SetTextColor(200,10,10);
$pdf->SetFillColor(254,255,245);

$pdf->SetStyle("p","times","",11,"130,0,30");
$pdf->SetStyle("pb","times","B",11,"130,0,30");
$pdf->SetStyle("t1","arial","",11,"80,80,260");
$pdf->SetStyle("t3","times","B",14,"203,0,48");
$pdf->SetStyle("t4","arial","BI",11,"0,151,200");
$pdf->SetStyle("hh","times","B",11,"255,189,12");
$pdf->SetStyle("ss","arial","",7,"203,0,48");
$pdf->SetStyle("font","helvetica","",10,"0,0,255");
$pdf->SetStyle("style","helvetica","BI",10,"0,0,220");
$pdf->SetStyle("size","times","BI",13,"0,0,120");
$pdf->SetStyle("color","times","BI",13,"0,255,255");

$txt1 = "Created by <t1 href='mailto:andy@interpid.eu'>Bintintan Andrei, Interpid Team</t1>";
$txt2 = "<p><t3>Description</t3>

\tThis method allows printing of <t4><TAG></t4> formatted text with line breaks. They can be automatic (as soon as the text reaches the right border of the cell) or explicit (via the <pb>\\n</pb> character).</p>

<t3>Features:</t3>
<p>\t- Text can be <hh>aligned</hh>, <hh>cente~~~red</hh> or <hh>justified</hh>
\t- Different <font>Font</font>, <size>Sizes</size>, <style>Styles</style>, <color>Colors</color> can be used 
\t- The cell block can be framed and the background painted
\t- <style href='www.fpdf.org'>Links</style> can be used in any tag
\t- <t4>TAB</t4> spaces (<pb>\\t</pb>) can be used
\t- Variable Y relative positions can be used for <ss ypos='-0.8'>Subscript</ss> or <ss ypos='1.1'>Superscript</ss>
\t- Cell padding (left, right, top, bottom)
\t- Controlled Tag Sizes can be used</p>

\t<size size='50' >Paragraph Example:~~~</size><font> - Paragraph 1</font>
\t<p size='60' > ~~~</p><font> - Paragraph 2</font>
\t<p size='60' > ~~~</p> - Paragraph 2
\t<p size='70' >Sample text~~~</p><p> - Paragraph 3</p>
\t<p size='50' >Sample text~~~</p> - Paragraph 1
\t<p size='60' > ~~~</p><t4> - Paragraph 2</t4>

<t3>Observations:</t3>
<p>
\t- If no <t4><TAG></t4> is specified then the FPDF current settings(font, style, size, color) are used
\t- The <t4>ttags</t4> tag name is reserved for the TAB SPACES
</p>";

$pdf->MultiCellTag(150, 5, $txt1, 1, "L", 1, 5, 5, 5, 5); $pdf->Ln(10);
$pdf->MultiCellTag(0, 5, $txt2, 1, "J", 1, 3, 3, 3, 3); $pdf->Ln(10);

$pdf->Output();

?>