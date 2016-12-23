<?php
session_start();
//INCLUI FUNCOES PHP E VARIAVEIS
include "functions/HeaderFooter.php";
require_once($relativepathtoroot.$databaseconnection_clean);

$uuid = $_POST['uuid'];
$tbname = 'ProjetosEspecs';
$plantas = $_POST['plantas'];
$projetoid = $_POST['projetoid'];
$status = $_POST['status'];
$apagatodos = $_POST['apagatodos'];
if ($apagatodos==1) {
        $rup = mysql_query("DELETE FROM  `".$tbname."` WHERE ProjetoID=".$projetoid);
        echo 0;
} else {
$specarr = explode(",",$plantas);
$nt = count($specarr);
if ($nt>0) {
$atu=0;
$inserido=0;
$nochange = 0;
foreach ($specarr as $spid) {
$rr =  mysql_query("SELECT * FROM `".$tbname."` WHERE PlantaID=".$spid."  AND ProjetoID=".$projetoid);
$nrr = mysql_numrows($rr);
if ($nrr>0) {
  $rrw = mysql_fetch_assoc($rr);
  //$oldIncluido = $rrw['Incluido']+0;
   if ($status==0) {
        $rup = mysql_query("DELETE FROM  `".$tbname."` WHERE `PlantaID`=".$spid."  AND `ProjetoID`=".$projetoid);
        if ($rup) {
            $atu++;
        }
  } else {
        $nochange++;
  }
} else {
  if ($status==1) {
        $qins = "INSERT INTO  `".$tbname."` (`PlantaID`,`ProjetoID`,`AddedBy`,`AddedDate`) VALUES (".$spid.",'".$projetoid."', '".$uuid."', CURRENT_DATE())";
        $rup = mysql_query($qins);
        if ($rup) {
        	$inserido++;
        }
  }
}

}
$rr =  mysql_query("SELECT * FROM `".$tbname."` WHERE PlantaID>0 AND ProjetoID=".$projetoid);
$nrr = mysql_numrows($rr)-1;
echo $nrr;
} 
else {
//echo "Não tem nada no filtro";
echo 0;
}
} 

?>