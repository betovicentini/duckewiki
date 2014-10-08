<?php
session_start();
//INCLUI FUNCOES PHP E VARIAVEIS
include "functions/HeaderFooter.php";
include "functions/MyPhpFunctions.php";

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

//CABECALHO
if ($ispopup==1) {
	$menu = FALSE;
} else {
	$menu = TRUE;
}

//apaga arquivo de progresso
$qqz = "DROP TABLE `temp_".$tbname."`";
@mysql_query($qqz,$conn);


//SAVE RESULTS IN A FILE
$numericfilters = array();
$colalign = array();
$colw = array();
$headd = array();
$coltipos = array();
$filtroarr = array();
$colsort = array();
$listvisible = array();
$hidemenu = array();
$collist = array();
$colvalid = array();
$colvalidtorf = array();
$headd[] = "EDIT";
$coltipos[] = 'ro';
$colw[]  = 100;
$colalign[] = 'center';
$filtroarr[] = '';
$hidemenu[] = 'false';
$listvisible[] = 'false';
$colsort[] = 'connector';
$colvalid[] = '';
$colvalidtorf[] = 'false';
$iddx = 1;
$collist[] = $iddx;
$iddx++;

$headd[] = "EXISTE";
$coltipos[] = 'ch';
$colw[]  = 30;
$colalign[] = 'center';
$filtroarr[] =  '#connector_text_filter';
$hidemenu[] = 'false';
$listvisible[] = 'false';
$colsort[] = 'connector';
$colvalid[] = '';
$colvalidtorf[] = 'false';
$collist[] = $iddx;
$iddx++;

if ($sampletype!='especimenes') {
$headd[] = "TEMP_DATA_COLETA";
$coltipos[] = 'dhxCalendar';
$colw[]  = 70;
$colalign[] = 'center';
$filtroarr[] =  '#connector_text_filter';
$hidemenu[] = 'false';
if ($formid>0) {
$listvisible[] = 'false';
} else {
$listvisible[] = 'true';
}
$colsort[] = 'connector';
$colvalid[] = '';
$colvalidtorf[] = 'false';
$collist[] = $iddx;
$iddx++;


$headd[] = "TEMP_FERT";
$coltipos[] = 'clist';
$colw[]  = 60;
$colalign[] = 'center';
$filtroarr[] =  '#connector_text_filter';
$hidemenu[] = 'false';
if ($formid>0) {
$listvisible[] = 'false';
} else {
$listvisible[] = 'true';
}
$colsort[] = 'connector';
$colvalid[] = '';
$colvalidtorf[] = 'false';
$collist[] = $iddx;
$iddx++;



$headd[] = "TEMP_NDUPS";
$coltipos[] = 'ed';
$colw[]  = 55;
$colalign[] = 'center';
$filtroarr[] =  '#connector_text_filter';
$hidemenu[] = 'false';
if ($formid>0) {
$listvisible[] = 'false';
} else {
$listvisible[] = 'true';
}
$colsort[] = 'connector';
$numericfilters[] = "TEMP_NDUPS";
$colvalid[] = 'ValidInteger';
$colvalidtorf[] = 'false';
$collist[] = $iddx;
$iddx++;






}
if ($sampletype=='especimenes') {
	$pltable = 'Especimenes';
	$pltableid = 'EspecimenID';
	$headd[] = "EspecimenID";
	$coltipos[] = 'ro';
	$colw[]  = 0;
	$colalign[] = 'right';
	$filtroarr[] = '';
	$listvisible[] = 'true';
	$hidemenu[] = 'false';
	$colsort[] = 'connector';
	$collist[] = $iddx;
	$iddx++;
	$colvalid[] = '';
	$colvalidtorf[] = 'false';
} 
else {
	$pltable = 'Plantas';
	$pltableid = 'PlantaID';
}
$headd[] = "PlantaID"; 
$coltipos[] = 'ro';
$colw[]  = 0;
$colalign[] = 'right';
$filtroarr[] = '';
$colsort[] = 'connector';
$listvisible[] = 'true';
$hidemenu[] = 'false';
$collist[] = $iddx;
$iddx++;
$colvalid[] = '';
$colvalidtorf[] = 'false';

$headd[] = "DetID";
$coltipos[] = 'ro';
$colw[]  = 0;
$colalign[] = 'right';
$filtroarr[] = '';
$colsort[] = 'connector';
$listvisible[] = 'true';
$hidemenu[] = 'false';
$collist[] = $iddx;
$iddx++;
$colvalid[] = '';
$colvalidtorf[] = 'false';


$headd[] = "IMG"; 
$coltipos[] = 'ro';
$colw[]  = 50;
$colalign[] = 'center';
$filtroarr[] = '';
$colsort[] = 'connector';
if ($formid>0) {
$listvisible[] = 'false';
} else {
$listvisible[] = 'true';
}
$hidemenu[] = 'false';
$collist[] = $iddx;
$iddx++;
$colvalid[] = '';
$colvalidtorf[] = 'false';

if ($sampletype=='especimenes') {
	$headd[] = "COLETOR";
	$coltipos[] = 'ro';
	$colw[]  = 80;
	$colalign[] =  'left';
	$filtroarr[] = '#connector_text_filter';
	$colsort[] = 'connector';
	$listvisible[] = 'false';
	$hidemenu[] = 'true';
	$collist[] = $iddx;
	$iddx++;
	$colvalid[] = '';
	$colvalidtorf[] = 'false';
	$headd[] = "NUMERO";
	$coltipos[] = 'ro';
	$colw[]  = 60;
	$colalign[]  = 'right';
	$filtroarr[] = '#connector_text_filter';
	$colsort[] = 'connector';
	$listvisible[] = 'false';
	$hidemenu[] = 'true';
	$numericfilters[] = "NUMERO";
	$collist[] = $iddx;
	$iddx++;
	$colvalid[] = '';
	$colvalidtorf[] = 'false';
} 
else {
	$headd[] = "ESPECS";
	$coltipos[] = 'ro';
	$colw[]  = 50;
	$colalign[] = 'left';
	$filtroarr[] = '#connector_text_filter';
	$colsort[] = 'connector';
if ($formid>0) {
$listvisible[] = 'false';
} else {
$listvisible[] = 'true';
}
	$hidemenu[] = 'true';
	$collist[] = $iddx;
	$iddx++;
	$colvalid[] = '';
	$colvalidtorf[] = 'false';

	$headd[] = "TAG_NUM";
	$coltipos[] = 'ro';
	$colw[]  = 80;
	$colalign[] = 'left';
	$filtroarr[] = '#connector_text_filter';
	$colsort[] = 'connector';
	$listvisible[] = 'false';
	$hidemenu[] = 'true';
	$numericfilters[] =  "TAG_NUM";
	$collist[] = $iddx;
	$iddx++;
	$colvalid[] = '';
	$colvalidtorf[] = 'false';

	$headd[] = "LOCAL";
	$coltipos[] = 'ro';
	$colw[]  = 100;
	$colalign[] = 'left';
	$filtroarr[] = '#connector_text_filter';
	$colsort[] = 'connector';
	$listvisible[] = 'false';
	$hidemenu[] = 'true';
	$collist[] = $iddx;
	$iddx++;
	$colvalid[] = '';
	$colvalidtorf[] = 'false';
}
$headd[] = "FAMILIA";
$coltipos[] = 'ro';
$colw[]  = 80;
$colalign[] = 'left';
$filtroarr[] = '#connector_text_filter';
$colsort[] = 'connector';
$listvisible[] = 'false';
$hidemenu[] = 'true';
$collist[] = $iddx;
$iddx++;
$colvalid[] = '';
$colvalidtorf[] = 'false';

$headd[] = "NOME";
$coltipos[] = 'ro';
$colw[]  = 100;
$colalign[] = 'left';
$filtroarr[] = '#connector_text_filter';
$colsort[] = 'connector';
$listvisible[] = 'false';
$hidemenu[] = 'true';
$collist[] = $iddx;
$iddx++;
$colvalid[] = '';
$colvalidtorf[] = 'false';


$headd[] = "NIR";
$coltipos[] = 'ro';
$colw[]  = 100;
$colalign[] = 'center';
$filtroarr[] = '#connector_text_filter';
$colsort[] = 'connector';
$numericfilters[] = 'NIR';
$listvisible[] = 'false';
$hidemenu[] = 'true';
$collist[] = $iddx;
$iddx++;
$colvalid[] = '';
$colvalidtorf[] = 'false';


if ($formid>0) {
$qf = "SELECT  form.* ,TraitTipo, maketraitname(tr.TraitID) AS nome,MultiSelect ,TraitUnit FROM FormulariosTraitsList as form JOIN Traits AS tr USING(TraitID) WHERE FormID=".$formid." AND TraitTipo<>'Variavel|Imagem' ORDER BY Ordem";
	$resf = mysql_query($qf,$conn);
	$traithead = array();
	$traittype = array();
	$traitclasses = array();
	$traitcolumnsids = array();
	while ($rowf = mysql_fetch_assoc($resf)) {
		$qp = '';
		$trid = $rowf['TraitID'];
		$trn = $rowf['nome'];
		$traitcolumnsids[$trn] = $trid;
		$headd[]  = $trn;
		$colw[]  = 60;
		$trtp = $rowf['TraitTipo'];
		$mselec = $rowf['MultiSelect'];
		$trunit = $rowf['TraitUnit'];
		$filtroarr[] = '#connector_text_filter';
		$colsort[] = 'connector';
		$listvisible[] = 'false';
		$hidemenu[] = 'true';
		$collist[] = $iddx;
		$iddx++;
		
		if ($trtp=='Variavel|Quantitativo') {
			//get units
			$qu = "SELECT GROUP_CONCAT(DISTINCT TRIM(TraitUnit) ORDER BY TraitUnit SEPARATOR ',')  as unit FROM Traits_variation  WHERE TraitID=".$trid." AND TRIM(TraitUnit)<>'' AND TraitUnit IS NOT NULL  GROUP BY TraitID";
			//echo $qu."<br />";
			$resu = mysql_query($qu,$conn);
			$rowu = mysql_fetch_assoc($resu);
			$units = explode(",",$rowu['unit']);
			
			$quu = "SELECT GROUP_CONCAT(DISTINCT TRIM(TraitUnit) ORDER BY TraitUnit SEPARATOR ',')  as unit FROM Monitoramento  WHERE TraitID=".$trid." AND TRIM(TraitUnit)<>'' AND TraitUnit IS NOT NULL  GROUP BY TraitID";
			//echo $qu."<br />";
			$resuu = mysql_query($quu,$conn);
			$rowuu = mysql_fetch_assoc($resuu);
			$unitsu = explode(",",$rowuu['unit']);
			//echo "|||||||||||||||||||";
			//echopre($unitsu);
			//echo "dois";
			//echopre($units);
			//echo "------------";
			$units = array_merge((array)$units,(array)$unitsu);
			
			$units[] = $trunit;
			$units = array_unique($units);
			asort($units);
			//echopre($units);
			//echo "|||||||||||||||||||";
			//echo "depois de ordenado";
			//echopre($units);
			$units = array_filter($units);
			//echopre($units);
			//echo "|||||||||||||||||||";
			$coltipos[] = 'ed';
			$colalign[] = 'right';
			$colvalid[] = 'WikiNum';
			$colvalidtorf[] = 'true';
			$numericfilters[] = $trn;
			$headd[] = $trn."_UNIT";
			$traitcolumnsids[$trn."_UNIT"] = $trid;
			$coltipos[] = 'coro';
			$colw[]  = 60;
			$colalign[]  = 'left';
			$colvalid[] = '';
			$colvalidtorf[] = 'false';
			$filtroarr[] = '';
			$colsort[] = 'connector';
			#$traitclasses[$trn."_UNIT"] = implode(",",$units);
	        $selectboxes[$trn."_UNIT"] = implode(",",$units);
	
			$listvisible[] = 'false';
			$hidemenu[] = 'true';
			$collist[] = $iddx;
			$iddx++;
		} else {
			$colvalid[] = '';
			$colvalidtorf[] = 'false';
			if ($trtp=='Variavel|Categoria') {
				$qst = "SELECT GROUP_CONCAT(DISTINCT TRIM(LOWER(TraitName)) ORDER BY TraitName SEPARATOR ',')  as vars FROM Traits  WHERE ParentID=".$trid;
				$resst = mysql_query($qst,$conn);
				$rowst = mysql_fetch_assoc($resst);
				$colalign[] = 'left';
				if (strtolower($mselec)=='sim')  {
					$coltipos[] = 'clist';
					$traitclasses[$trn] = $rowst['vars'];
				} else {
					$selectboxes[$trn] = $rowst['vars'];
					$coltipos[] = 'coro';
				}
			} else {
				$colalign[] = 'left';
				$coltipos[] = 'txttxt';
			}
		}
	}
}
//echopre($collist);
$hdd = implode(",",$headd);
$ffilt = implode(",",$filtroarr);
$ffilt2 = implode(",",$colsort);
$collist = implode(",",$collist);
$colw = implode(",",$colw);
$coltipos = implode(",",$coltipos);
$listvisible = implode(",",$listvisible);
$colalign = implode(",",$colalign);
$hidemenu = implode(",",$hidemenu);
$colvalid = implode(",",$colvalid);
$colvalidtorf = implode(",",$colvalidtorf);

$fnn = $tbname.".php";
$qq = "SELECT count(*) as nrecs FROM ".$tbname;
$rr = @mysql_query($qq,$conn);
$row = @mysql_fetch_assoc($rr);
$nrecs = $row['nrecs'];

$arrofpass = array(
'ffields'   => $hdd,
'filtros'  => $ffilt,   
'filtros2'  => $ffilt2,   
'collist'  => $collist,  
'colw'  => $colw,   
'coltipos'  => $coltipos,   
'listvisible'  => $listvisible,   
'colalign' => $colalign,
'colvalid' => $colvalid,
'colvalidtorf' => $colvalidtorf,
'hidemenu' => $hidemenu,
'nrecs' => $nrecs,
'ispopup'  => $ispopup,   
'tbname' => $tbname,
'fname' => $fnn,
'sampletype' => $sampletype,
'formid' => $formid,
'trclasses' => count($traitclasses)
);

$_SESSION['traitclasses'] = serialize($traitclasses);
$_SESSION['traitcolumnsids'] = serialize($traitcolumnsids);
$url = $_SERVER['HTTP_REFERER'];
$uu = explode("/",$url);
$nu = count($uu)-1;
unset($uu[$nu]);
$url = implode("/",$uu);
$fh = fopen("temp/".$fnn, 'w');
//session_start();
//include \"../functions/MyPhpFunctions.php\";
$stringData = "<?php
session_start();
require_once(\"../dhtmlxconnector/dhtmlxConnector_php/codebase/grid_connector.php\");
require_once(\"../".$relativepathtoroot.$databaseconnection_clean."\");
include \"../functions/MyPhpFunctions.php\";";

if (count($traitcolumnsids)>0) {
	$stringData .= "
\$traitsids = array(";
	$i = 0;
	foreach ($traitcolumnsids as $kk => $vv) {
		if ($i==0) {
			$stringData .= "'".$kk."' => ".$vv;
		} else {
			$stringData .= ", '".$kk."' => ".$vv;
		}
		$i++;
	}
	$stringData .= ");
";
}
$stringData .= "
function myUpdate(\$action){
";
if ($sampletype=='especimenes') {
	$stringData .= "
mysql_query(\"UPDATE `".$tbname."` SET `EXISTE`='{\$action->get_value('EXISTE')}'  WHERE `EspecimenID`='{\$action->get_id()}'\");";
		} else {
$stringData .= "
mysql_query(\"UPDATE `".$tbname."` SET `EXISTE`='{\$action->get_value('EXISTE')}'  WHERE `PlantaID`='{\$action->get_id()}'\");";
$stringData .= "
mysql_query(\"UPDATE `".$tbname."` SET `TEMP_DATA_COLETA`='{\$action->get_value('TEMP_DATA_COLETA')}'  WHERE `PlantaID`='{\$action->get_id()}'\");
mysql_query(\"UPDATE `".$tbname."` SET `TEMP_FERT`='{\$action->get_value('TEMP_FERT')}'  WHERE PlantaID='{\$action->get_id()}'\");
        \$val = \$action->get_value('TEMP_NDUPS');
        \$val = \$val+0;
        \$erro=0;
        if (\$val>0) {
            mysql_query(\"UPDATE `".$tbname."` SET TEMP_NDUPS='{\$action->get_value('TEMP_NDUPS')}'  WHERE PlantaID='{\$action->get_id()}'\");
        } 
";
}


if (count($traitcolumnsids)>0) {
	$traitnames = array_keys($traitcolumnsids);

	foreach ($traitcolumnsids as $kk => $vv) {
		//check whether trait is unit or value
		$pattern = '/_UNIT/';
		$isunit = 0;
		$traitisquantitative = 0;
		if (preg_match($pattern,$kk)) {
			$kktraitval = str_replace('_UNIT','',$kk);
			$kktraitid = ($traitcolumnsids[$kktraitval]+0);
			$isunit=1;
		} else {
			$kkun = $kk.'_UNIT';
			if (in_array($kkun,$traitnames)) {
				//then it is a quantitative trait && update main database when updating unit only. if not value will not be accepted.
				$traitisquantitative = 1;
			}
		}

		if ($sampletype=='especimenes') {
			$stringData .= "
mysql_query(\"UPDATE `".$tbname."` SET `".$kk."`='{\$action->get_value('".$kk."')}' WHERE EspecimenID='{\$action->get_id()}'\");";
			//if running trait is not quantitative and is is not a unit trait, then update main database if  it is a categorical trait and leave traitunit blank;
			if ($traitisquantitative==0 && $isunit==0) {
	$stringData .= "
 \$nd = updatetraits_grid(".$vv.",\$action->get_value('".$kk."'),\$action->get_id(),0, '', '".$dbname."');
 ";
 			} else {
 				//se for quantitativo e for unidade, então atualiza base com valor
 				if ($isunit==1) {
 						$stringData .= "
\$isthereunit = \$action->get_value('CO_DAP_UNIT');
if (!empty(\$isthereunit)) { ";
	$stringData .= "
 \$nd = updatetraits_grid(".$kktraitid.",\$action->get_value('".$kktraitval."'),\$action->get_id(),0,\$action->get_value('".$kk."'), '".$dbname."');
 }
 ";
 // updatetraits_grid($charid,$traitvalue,$specid,$plantaid, $ttunidade, $dbname) 
  
 				} else {
 					//é uma variavel quantitativa, atualiza base apenas se unidade for preenchida
					//$stringData .= " \$nd = updatetraits_grid(".$vv.",\$action->get_value('".$kktraitval."'),\$action->get_id(),0,\$action->get_value('".$kk."'), '".$dbname."'); ";
 				}
 			}
		} else {
$stringData .= "
mysql_query(\"UPDATE `".$tbname."` SET `".$kk."`='{\$action->get_value('".$kk."')}' WHERE PlantaID='{\$action->get_id()}'\");";
			//if running trait is not quantitative and is is not a unit trait, then update main database if  it is a categorical trait and leave traitunit blank;
			if ($traitisquantitative==0 && $isunit==0) {
	$stringData .= "
 \$nd = updatetraits_grid(".$vv.",\$action->get_value('".$kk."'),0,\$action->get_id(), '', '".$dbname."');
 ";
 			} else {
 				//se for quantitativo e for unidade, então atualiza base com valor
 				if ($isunit==1) {
$stringData .= "
\$isthereunit = \$action->get_value('CO_DAP_UNIT');
if (!empty(\$isthereunit)) { ";
	$stringData .= "
 \$nd = updatetraits_grid(".$kktraitid.",\$action->get_value('".$kktraitval."'),0,\$action->get_id(),\$action->get_value('".$kk."'), '".$dbname."');
}
 ";
 				} else {
 					//é uma variavel quantitativa, atualiza base apenas se unidade for preenchida
					//$stringData .= "\$nd = updatetraits_grid(".$vv.",\$action->get_value('".$kktraitval."'),0,\$action->get_id(),\$action->get_value('".$kk."'), '".$dbname."'); ";
 				}
 			}
		



		}
	}
}
$stringData .= "
        \$action->success();
}";
$stringData .= "
function custom_format_spec(\$data){
\$thedetid = \$data->get_value(\"DetID\");
";
if ($sampletype=='especimenes') {
	$stringData .= "
\$pltag = \$data->get_value(\"COLETOR\").\" \".\$data->get_value(\"NUMERO\");
\$thespecimenid = \$data->get_value(\"EspecimenID\");

if (\$data->get_value(\"IMG\")==\"camera.png\") {
  \$imggg=\"<img style='cursor:pointer;' src='icons/\".\$data->get_value(\"IMG\").\"' height='20' onclick=\\\"javascript:small_window('".$url."/showimage_taxa.php?ispopup=1&especimenid=\".\$thespecimenid.\"',700,400,'Ver imagens');\\\" onmouseover=\\\"Tip('Ver imagens da amostra # \".\$pltag.\"');\\\" >\";
} else {
  \$imggg= '';
}

\$imagen=\"<img style='cursor:pointer;' src='icons/\".\$data->get_value(\"EDIT\").\"' height='20' onclick=\\\"javascript:small_window('".$url."/especimenes_dataform.php?ispopup=1&especimenid=\".\$data->get_value(\"EspecimenID\").\"',1000,400,'Editar registro');\\\" onmouseover=\\\"Tip('Editar o especímene # \".\$pltag.\"');\\\" >\";
\$imagen2=\"<img style='cursor:pointer;' src='icons/rednameicon.png' height='20' onclick=\\\"javascript:small_window('".$url."/taxonomia-popup.php?updatechecklist=1&ispopup=1&saveit=true&detid=\".\$data->get_value(\"DetID\").\"&especimenid=\".\$data->get_value(\"EspecimenID\").\"',800,400,'Editar Identificação');\\\" onmouseover=\\\"Tip('Editar Identificação da amostra # \".\$pltag.\"');\\\" >\";
\$imgg3 =\"<img style='cursor:pointer;' src='icons/nota-icon.png' height='20' onclick=\\\"javascript:small_window('".$url."/traits_coletorvariacao.php?apagavarsess=1&saveit=1&formid=".$formnotes."&especimenid=\".\$data->get_value(\"EspecimenID\").\"',800,800,'Editando notas');\\\"  onmouseover=\\\"Tip('Edita notas da amostra # \".\$pltag.\"');\\\" >\";
\$imagen = \$imagen.\"&nbsp;\".\$imagen2.\"&nbsp;\".\$imgg3;


";
} else {
	$stringData .= "
\$pltag = \$data->get_value(\"TAG_NUM\");
\$thespecimenid = \$data->get_value(\"PlantaID\");

if (\$data->get_value(\"IMG\")==\"camera.png\") {
  \$imggg=\"<img style='cursor:pointer;' src='icons/\".\$data->get_value(\"IMG\").\"' height='20' onclick=\\\"javascript:small_window('".$url."/showimage_taxa.php?ispopup=1&plantaid=\".\$thespecimenid.\"',700,400,'Ver imagens');\\\" onmouseover=\\\"Tip('Ver imagens da amostra # \".\$pltag.\"');\\\" >\";
} else {
  \$imggg= '';
}

 if ((\$data->get_value(\"ESPECS\"))>0) {
       \$imagen= \"<img style='cursor:pointer;' src='icons/specimen-icon.png' height='20' onclick=\\\"javascript:small_window('".$url."/checklist_specimens.php?ispopup=1&plantaid=\".\$data->get_value(\"PlantaID\").\"',950,500,'Especimenes');\\\" onmouseover=\\\"Tip('Visualizar amostras da planta # \".\$pltag.\"');\\\" title=''><sup>  \".\$data->get_value(\"ESPECS\").\"</sup>\";
   } else {
        \$imagen = \" \";
   }
   \$data->set_value(\"ESPECS\",\$imagen);

\$imagen= \"<img style='cursor:pointer;' src='icons/\".\$data->get_value(\"EDIT\").\"' height='20' onclick=\\\"javascript:small_window('".$url."/plantas_dataform.php?ispopup=1&submeteu=editando&plantaid=\".\$data->get_value(\"PlantaID\").\"&sessionvars=".$arrsses."',1000,400,'Editando o registro');\\\" onmouseover=\\\"Tip('Editar o registro da planta # \".\$pltag.\"');\\\" title=''>\";
\$imgg1 =\"<img style='cursor:pointer;' src='icons/nota-icon.png' height='20' onclick=\\\"javascript:small_window('".$url."/traits_coletorvariacao.php?apagavarsess=1&saveit=1&formid=".$formnotes."&plantaid=\".\$data->get_value(\"PlantaID\").\"',800,800,'Editando notas');\\\"  onmouseover=\\\"Tip('Edita notas da amostra # \".\$pltag.\"');\\\" >\";
\$imgg2 = \"<img style='cursor:pointer;' src='icons/monitoramento.png' height='20' onclick=\\\"javascript:small_window('".$url."/traits_coletormonitoramento.php?ispopup=1&plantatag=\".\$data->get_value(\"TAG\").\"&plantaid=\".\$data->get_value(\"PlantaID\").\"&submeteu=1',1000,400,'Editando o registro');\\\" onmouseover=\\\"Tip('Ver/Editar variáveis de monitoramento da planta # \".\$pltag.\"');\\\" title=''>\";
\$imgg3 = \"<img style='cursor:pointer;' src='icons/specimen-icon.png' height='20' onclick=\\\"javascript:small_window('".$url."/especimenes_dataform.php?ispopup=1&plantaid=\".\$data->get_value(\"PlantaID\").\"&submeteu=nova',1000,400,'Nova amostra de planta');\\\" onmouseover=\\\"Tip('Novo especímene da planta # \".\$pltag.\"');\\\" title=''>\";
\$imgg4 = \"<img style='cursor:pointer;' src='icons/rednameicon.png' height='17' onclick=\\\"javascript:small_window('".$url."/taxonomia-popup.php?updatechecklist=1&ispopup=1&saveit=true&detid=\".\$data->get_value(\"DetID\").\"&plantaid=\".\$data->get_value(\"PlantaID\").\"',800,400,'Editar Identificação');\\\" onmouseover=\\\"Tip('Editar Identificação da planta # \".\$pltag.\"');\\\" title='' >\";
\$imagen = \$imagen.\"&nbsp;\".\$imgg1.\"&nbsp;\".\$imgg2.\"&nbsp;&nbsp;\".\$imgg3.\"&nbsp;&nbsp;\".\$imgg4;";
}
$stringData .= "
    \$data->set_value(\"IMG\",\$imggg);
    \$data->set_value(\"EDIT\",\$imagen);
    \$nir = \$data->get_value(\"NIR\");
    if (\$nir>0) {
      \$nirimg = \"<img style='cursor:pointer;' src='icons/nirspectra.png' height='20' onmouseover=\\\"Tip('Existem  \".\$nir.\"  spectra para esse registro');\\\" ><br >\".\$nir;
    } else {
      \$nirimg = \$nir;
    }
    \$data->set_value(\"NIR\",\$nirimg);
}
";
$stringData .= "
function removeoperators(\$data){
  \$val = str_replace('>','',\$data);
  \$val = str_replace('<','',\$val);
  \$val = str_replace('=','',\$val);  
  return \$val;
}
function custom_filter(\$filter_by){
";
if (count($numericfilters)>0) {
$i=1;
$idxx = '
$idxss = array('; 
foreach ($numericfilters as $nuvar) {
	$stringData .= "
    \$index".$i." = \$filter_by->index('".$nuvar."');";
	if ($i==1) {
    	$idxx .= "\$index".$i;
	} else {
    	$idxx .= ",\$index".$i;
	}
	$i++;
}
$idxx .= ");";
	$stringData .= $idxx;
} else {
	$stringData .= '\$idxss = array();';
}
$stringData .= "
   foreach (\$idxss as \$idx) {
    if (\$idx!==false) {
      \$vv =  \$filter_by->rules[\$idx][\"value\"];
      if (substr(\$vv,0,1)=='>') {
        \$filter_by->rules[\$idx][\"operation\"]=\">\";
        \$val = str_replace('>','',\$vv);
      }
      if (substr(\$vv,0,1)=='<') {
        \$filter_by->rules[\$idx][\"operation\"]=\"<\";
        \$val = str_replace('<','',\$vv);
      }
      if (substr(\$vv,0,1)=='=') {
        \$filter_by->rules[\$idx][\"operation\"]=\"=\";
        \$val = str_replace('=','',\$vv);
      }
      \$filter_by->rules[\$idx][\"value\"] = removeoperators(\$filter_by->rules[\$idx][\"value\"]);
    }
  }
}
\$grid = new GridConnector(\$res);
\$grid ->event->attach(\"beforeRender\",\"custom_format_spec\");
\$grid ->event->attach(\"beforeFilter\",\"custom_filter\");
\$grid ->dynamic_loading(100);";
if (count($selectboxes)>0) {
foreach ($selectboxes as $kk => $vv) {
	$stringData .= "
\$grid->set_options(\"".$kk."\",array(";
	$i = 0;
	$stvals = explode(",",$vv);
	//stvals = array_unique($stvals);
	foreach ($stvals as $kk => $vv) {
		if ($i==0) {
			$stringData .= "'".$vv."' => '".$vv."'";
		} else {
			$stringData .= ", '".$vv."' =>  '".$vv."'";
		}
		$i++;
	}
	$stringData .= "));
";
	}
}
$stringData .= "
\$grid->event->attach(\"beforeUpdate\",\"myUpdate\");
\$grid ->render_table(\"".$tbname."\",\"".$pltableid."\",\"".$hdd."\");
?>";

//\$grid->set_encoding(\"utf8\");
//echo \"<?xml version='1.0' encoding='UTF-8'\";
//\$grid->set_encoding(\"iso-8859-1\");
//
//\$grid->event->attach(\"beforeUpdate\",\"myUpdate\");
//\$grid->set_options(\"Fert\",".$options.");
fwrite($fh, $stringData);
fclose($fh);


$_SESSION['arrtopass'] = serialize($arrofpass);
echo "
  <form name='myform' action='batchenter-traits-gridview.php' method='post'>";
  foreach ($arrofpass as $kk => $vv) {
echo "
    <input type='hidden' name='".$kk."' value='".$vv."'>";
  }
echo "<script language=\"JavaScript\">setTimeout('document.myform.submit()',0.0001);</script></form>";

?>