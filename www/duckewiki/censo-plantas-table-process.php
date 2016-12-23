<?php
session_start();
//INCLUI FUNCOES PHP E VARIAVEIS
include "functions/HeaderFooter.php";
include "functions/MyPhpFunctions.php";

//FAZ A CONEXAO COM O BANCO DE DADOS
$lang = $_SESSION['lang'];
$dbname = $_SESSION['dbname'];
$conn = ConectaDB($dbname);

//SE ESTIVER MARCANDO
if ($_POST['status']==1) {
$sql = "UPDATE 
`Monitoramento` AS moni, 
checklist_pllist AS pltb,
Traits AS tr
SET moni.CensoID='".$_POST['censoid']."' 
WHERE moni.CensoID IS NULL AND moni.PlantaID=pltb.PlantaID AND tr.TraitID=moni.TraitID ";

//COLUNAS DO QUERY
$colnforfilter = array( "moni.CensoID", "moni.CensoID", "pltb.PlantaID", "pltb.TAG", "pltb.TAGtxt", "pltb.FAMILIA", "pltb.NOME", "pltb.PAIS", "pltb.ESTADO", "pltb.MUNICIPIO", "pltb.LOCAL", "pltb.LOCALSIMPLES", "pltb.PROJETO", "moni.MonitoramentoID", "tr.TraitName", "tr.PathName", "tr.TraitTipo", "moni.TraitVariation", "moni.DataObs", "moni.TraitUnit ");

//SE HOUVER UM FILTRO ATIVO, PEGA DEFINICOES
if(isset($_POST["filtrando"])){
$ff = $_POST;
unset($ff['filtrando']);
unset($ff['censoid']);
unset($ff['status']);
unset($ff['uuid']);
$nc = count($ff);
//SE FOR MAIOR QUE 0 ENTAO HA FILTRO
if ($nc>0) {
   foreach($ff as $kk => $vv) {
      $idx = str_replace("colidx_","",$kk);
      $idx = $idx+0;
      $cln = trim($colnforfilter[$idx]);
      if (substr($vv,0,1)=='>') {
          $filter_by = $vv;
          $cll = " (".$cln ."+0)";
      } else {
        if (substr($vv,0,1)=='<') {
            $filter_by = $vv;
            $cll = " (".$cln."+0)";
        } else {
          if (substr($vv,0,1)=='=') {
              $v1 = trim(str_replace("=","",$vv));
              $v1 = $v1+0;
              if ($v1>0) {
                $filter_by = $vv;
                $cll = " (".$cln."+0)";
              } else {
                $v2 = trim(str_replace("=","",$vv));
                $filter_by = "=LOWER('".$v2."')";
                $cll = " LOWER(".$cln.")";
              }
          } else {
              if (substr($vv,0,1)=='!') {
                 $condicao = ' NOT LIKE ';
                 $vv = trim(str_replace("!","",$vv));
              } else {
                 $condicao = '  LIKE ';
              }
              $vv = mb_strtolower($vv);
              $filter_by = $condicao." '%".$vv."%' ";
              $cll = " LOWER(".$cln.")";
          }
        }
    }
    $sql .=  " AND ".$cll.$filter_by;
   }
}
}
//$fh = fopen("temp/lixao.txt", 'w');
//fwrite($fh, $sql);
//foreach($_POST as $kk => $vk) {
//fwrite($fh,$kk.'    = >    '.$vk."\n");
//}
//fclose($fh);
$rsql = mysql_query($sql, $conn);
if ($rsql) {
	$txt = 'MARCADOS';
} else {
	$txt = 'NÃO FOI POSSÍVEL MARCAR OS REGISTROS. CONSULTE O ADMINISTRADOR';
}
} //SE ESTIVER DESMARCANDO
else {
	$sql = "UPDATE `Monitoramento` AS moni SET moni.CensoID=NULL  WHERE moni.CensoID='".$_POST['censoid']."'";
	$rsql = mysql_query($sql);
	if ($rsql) {
		$txt = 'DESMARCADOS';
	} else {
		$txt = 'NÃO FOI POSSÍVEL DESMARCAR. CONSULTE O ADMINISTRADOR';
	}
//$fh = fopen("temp/lixao.txt", 'w');
//fwrite($fh, $sql);
//fclose($fh);
}
$rr =  mysql_query("SELECT DISTINCT(PlantaID) FROM `Monitoramento` WHERE CensoID='".$_POST['censoid']."'");
$nrr = mysql_numrows($rr);
$rr =  mysql_query("SELECT * FROM `Monitoramento` WHERE CensoID='".$_POST['censoid']."'");
$nmed = mysql_numrows($rr);
echo 'REGISTROS FORAM '.$txt.'. O censo inclui agora '.$nmed.' medições para '.$nrr.'  arvores!';
?>