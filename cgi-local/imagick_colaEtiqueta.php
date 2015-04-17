<?php
session_start();
$folder = $_GET['folder'];
$pathetiqueta = $_GET['pathetiqueta'];
$pathfinal = $_GET['pathfinal'];
$returnto = $_GET['returnto'];
$returnvar = $_GET['returnvar'];
$amostra = $_GET['amostra'];
$etiqueta = $_GET['etiqueta'];

//path das imagens relativo ao local deste arquivo
$path = "../".$folder."/img/originais/";
$pathetiqueta = "../".$folder."/temp/".$pathetiqueta."/";
$pathfinal = "../".$folder."/temp/".$pathfinal."/";

if (file_exists($pathetiqueta.$etiqueta)) {
echo $_SERVER['SCRIPT_NAME']."<br />";
	echo "convert -density 300 -trim ".$pathetiqueta.$etiqueta." ".$pathetiqueta.$etiqueta."<br >";
	echo "convert -density 300 -trim ".$pathetiqueta.$etiqueta." -quality 100 ".$jpg."<br >";
	echo "convert ".$path.$amostra." ".$pathetiqueta.$etiqueta."  -gravity SouthEast -geometry +10+10 -composite ".$pathfinal.$amostra."<br >";
	//converte o pdf gerado pelo wiki da etiqueta (passo necessario, mas não sei porque)
	system("convert -density 300 -trim ".$pathetiqueta.$etiqueta." ".$pathetiqueta.$etiqueta);
	//converte  a etiqueta em jpg
	$jpg = str_replace(".pdf","",$etiqueta);
	$jpg = $jpg.".jpg";
	system("convert -density 300 -trim ".$pathetiqueta.$etiqueta." -quality 100 ".$jpg);
	//adiciona a etiqueta no cando direito inferior da imagem da amostra
	$fim = system("convert ".$path.$amostra." ".$pathetiqueta.$etiqueta."  -gravity SouthEast -geometry +10+10 -composite ".$pathfinal.$amostra);
} else {
	//NAO EXISTE
	//copia sem adiconar etiqueta
	$fim2 = system("cp ".$path.$amostra."/*.* ".$pathfinal.$amostra."/");
}
//header("location: http://".$returnto."?tbname=".$tbname);
?>