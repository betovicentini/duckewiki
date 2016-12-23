<?php
session_start();
//Check whether the session variable
include "functions/HeaderFooter.php";
include "functions/SelectOptions.php";

$lang = $_SESSION['lang'];
$dbname = $_SESSION['dbname'];
$conn = ConectaDB($dbname);
$uuid = cleanQuery($_SESSION['userid'],$conn);
if(!isset($uuid) || 
	(trim($uuid)=='')) {
		header("location: access-denied.php");
	exit();
} 

$ppost = cleangetpost($_POST,$conn);
$arval = $ppost;
@extract($ppost);
$gget = cleangetpost($_GET,$conn);
@extract($gget);

$body='';
$title = '';
HTMLheaders($body);

$path = "img/geotagged/";

echo "<br>
<table class='sortable autostripe' cellspacing='0' cellpadding='3' align='center' width='100%'>
<thead >
<tr>
<th align='center'>".GetLangVar('nameimagens')."</th>
<th align='center'>".GetLangVar('namenome')."</th>
<th align='center'>".GetLangVar('namedata')."</th>
<th align='center'>".GetLangVar('namelatitude')."</th>
<th align='center'>".GetLangVar('namelongitude')."</th>
<th align='center'>".GetLangVar('namealtitude')."</th>
<th align='center'>".GetLangVar('namefotografo')."s</th>
</tr>
</thead>
<tbody>";
$qq = "SELECT * FROM Imgs_geotagged ORDER BY Latitude,Longitude";
$resul = mysql_query($qq,$conn);
while ($row = mysql_fetch_assoc($resul)) {
	$fname = $row['FileName'];
	$DateTimeOriginal = $row['DateTimeOriginal'];
	$latitude = $row['Latitude'];
	$longitude = $row['Longitude'];
	$altitude = $row['Altitude'];

	$aut = explode(";",$row['Autores']);
	$fotog = $aut;
	foreach ($aut as $kk => $autor) {
		$qq = "SELECT * FROM Pessoas WHERE PessoaID='$autor'";
		$rr = mysql_query($qq,$conn);
		$rw = mysql_fetch_assoc($rr);
		$fotog[$kk] = $rw['Abreviacao'];
	}
	$fotografos = implode(";",$fotog);

	//checar se thumbnail existe, senao cria um
	$pthumb = "img/thumbnails/";
		//echo $path.$fn;
	if (!file_exists($pthumb.$fname)) {
			createthumb($path.$fname,$pthumb.$fname,80,80);
	}
	
	//checar se imagen para visualizacao existe, senao cria uma
	$imgbres = "img/copias_baixa_resolucao/";	
	if (!file_exists($imgbres.$fname)) {
			$zz = getimagesize($path.$fname);
			$width=$zz[0];
			$height = $zz[1];
			if ($width>1800 || $height>1800) {
				createthumb($path.$fname,$imgbres.$fname,1800,1800);
			} else {
				createthumb($path.$fname,$imgbres.$fname,$width,$height);
			}
		}
	
	
	echo "<tr class='small'>
			        <td align='center'>
					<a href=\"".$imgbres.$fname."\" class='MagicZoomPlus' 
					rel=\"zoom-position:center-right;zoom-width:600px;zoom-height:400px;
					zoom-fade:true; smoothing-speed:17;opacity-reverse:true;\" >
						<img width=\"80\" src=\"".$pthumb.$fname."\"/>
					</a>
				</td>
				<td  align='left'>
					&nbsp;<i>".$fname."</i>
				</td>
				<td s' align='left'>
					&nbsp;<i>".$DateTimeOriginal."</i>
				</td>
				<td align='left'>
					&nbsp;<i>".$latitude."</i>
				</td>
				<td  align='left'>
					&nbsp;<i>".$longitude."</i>
				</td>
				<td  align='left'>
					&nbsp;<i>".$altitude."</i>
				</td>
				<td align='left'>
					&nbsp;<i>".$fotografos."</i>
				</td>
	</tr>";
}
echo "</tbody></table>";

HTMLtrailers();
?>

