<?php
session_start();
//INCLUI FUNCOES PHP E VARIAVEIS
require_once("../../includes/botanicaamazon3_clean.php");

$uuid = $_POST['uuid'];
$tbname = 'MonografiaEspecs';
$especimenes = $_POST['especimenes'];
$monografiaid = $_POST['monografiaid'];
$status = $_POST['status'];
$temptb = $_POST['temptb'];
$specarr = explode(",",$especimenes);
$nt = count($specarr);
if ($nt>0) {
$atu=0;
$inserido=0;
$nochange = 0;
foreach ($specarr as $spid) {
$rr =  mysql_query("SELECT * FROM `".$tbname."` WHERE EspecimenID=".$spid."  AND MonografiaID=".$monografiaid);
$nrr = mysql_numrows($rr);
if ($nrr>0) {
  $rrw = mysql_fetch_assoc($rr);
  $oldIncluido = $rrw['Incluido']+0;
   if ($oldIncluido!=$status) {
        $rup = mysql_query("UPDATE  `".$tbname."` SET `Incluido`=".$status."  WHERE `EspecimenID`=".$spid."  AND MonografiaID=".$monografiaid);
        if ($rup) {
        	$atu++;
        }
  } else {
        $nochange++;
  }
} else {
  if ($status==1) {
        $qins = "INSERT INTO  `".$tbname."` (`Incluido`,`EspecimenID`,`MonografiaID`,`AddedBy`,`AddedDate`) VALUES ('1' ,".$spid.",'".$monografiaid."', '".$uuid."', CURRENT_DATE())";
        $rup = mysql_query($qins);
        if ($rup) {
        	$inserido++;
        }
  }
}
mysql_query("UPDATE  `".$temptb."` SET `Incluido`=".$status."  WHERE `EspecimenID`=".$spid." ");
}

$rr =  mysql_query("SELECT * FROM `".$tbname."` WHERE EspecimenID>0 AND Incluido>0 AND Incluido IS NOT NULL AND MonografiaID=".$monografiaid);
$nrr = mysql_numrows($rr)-1;
if ($status==0) {
	$txt = 'DESMARCADOS';
} else {
$txt = 'MARCADOS';
}
if ($atu>0) {
//echo 'Foram '.$txt."  ".$atu." registros. ";
}
if ($inserido>0) {
//echo 'Foram inseridos '.$inserido." registros. ";
}
if ($nochange>0) {
//echo $nochange." registros não mudaram!";
}
//echo "\n".$qins;
echo $nrr;
} 
else {
//echo "Não tem nada no filtro";
echo 0;
}

?>