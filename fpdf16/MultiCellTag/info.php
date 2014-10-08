<html>

<head>
<META HTTP-EQUIV="Content-Type" CONTENT="text/html; charset=UTF-8">
<title>FPDF Add On - Tag-based MultiCell</title>
<link href="info.css" rel="stylesheet" type="text/css">
</head>

<body>
<h2>Tag-based MultiCell(FPDF Add On)</h2>
<h4 class="title1">Informations:</h4>
<blockquote>
Author: <A HREF="mailto:andy@interpid.eu">Bintintan Andrei, Interpid Team</A>
<br>
</blockquote>
<h4 class="title1">License:</h4>
<blockquote>
<?php echo nl2br(file_get_contents('license_short.txt'));?>
</blockquote>
<h4 class="title1">Description:</h4>
<blockquote>
This <a href="http://www.fpdf.org">FPDF</a> Add On Class allows creation of <i><b>TAG based formatted text areas</b></i> with line breaks. They can be automatic
(as soon as the text reaches the right border of the cell) or explicit (via the \n character). <br>
The use of
tags allows to change the font, the style (bold, italic, underline), the size, and the color of characters and many other features.<br>
</blockquote>
<h4 class="title1">Features:</h4>
	<ul>
	    <li>Text can be aligned, centered or justified</li>
	    <li>Different Font, Sizes, Styles, Colors can be used</li>
	    <li>The cell block can be framed and the background painted</li>
	    <li>Links can be used in any tag</li>
	    <li>TAB spaces (\t) can be used</li>
	    <li>Variable Y relative positions can be used for Subscript or Superscript</li>
	    <li>Cell padding (left, right, top, bottom)</li>
	    <li>Controlled Tag Sizes can be used</li>
    </ul>
<br>
<h4 class="title1">Methods:</h4>
<ul>
	<li><tt>SetStyle(<b>string</b> tag, <b>string</b> family, <b>string</b> style, <b>int</b> size, <b>string</b> color)</tt></li>
</ul>
<blockquote>
Sets the current settings for the specified tag name. The names "ttags" and "pparg" are reserved for tab spaces. Don't use
empty tag names.<br>
<br>
<u>tag</u>: name of the tag<br>
<u>family</u>: family of the font<br>
<u>style</u>: N (normal) or com.bination of B, I, U<br>
<u>size</u>: size<br>
<u>color</u>: color (comma-separated RGB components)<br>
</blockquote>
<br>
<ul>
	<li><tt>MultiCellTag(<b>float</b> w, <b>float</b> h, <b>string</b> txt [, <b>mixed</b> border [, <b>string</b> align [, <b>int</b> fill [, <b>int</b> pad_left [, <b>int</b> pad_top [, <b>int</b> pad_right [, <b>int</b> pad_bottom ]]]]]]])</tt></li>
</ul>
<blockquote>
Outputs the tag-based MultiCell. \n = new line, \t = tab space. All parameters are and
behave the same as the MultiCell function.<br>
<br>
<u>w</u>: width of cells. If 0, they extend up to the right margin of the page;<br>
<u>h</u>: height of the cell lines;<br>
<u>txt</u>: string to print;<br>
<u>border</u>: Indicates if borders must be drawn around the cell block. Can be 0(no border) or 1(full border) or a combination of L(left) R(right) T (top) B(bottom) characters. Default is 1;<br>
<u>align</u>: Sets the text alignment. Possible values are: L, C, R, J. Default is J;<br>
<u>fill</u>: Indicates if the cell background must be painted (1) or transparent (0). Default is 0;<br>
<u>pad_left</u>: Indicates the Cell Pad Left Space. Default is 0;<br>
<u>pad_top</u>: Indicates the Cell Pad Top Space. Default is 0;<br>
<u>pad_right</u>: Indicates the Cell Pad Right Space. Default is 0;<br>
<u>pad_bottom</u>: Indicates the Cell Pad Bottom Space. Default is 0;<br>
</blockquote>

<h4 class="title1">Example:</h4>
<blockquote>
<b>Source Code:</b>
<div class='codediv'>
<?php 
	highlight_file("example.php");
?>
</div>
<br>
<b>View the result <a target="_blank" href="tag_multicell_example.pdf">here</a></b>
</blockquote>
</body>
</html>
