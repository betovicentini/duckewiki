<?php
//Start session
session_start();
//INCLUI FUNCOES PHP E VARIAVEIS
include "functions/HeaderFooter.php";
include "functions/MyPhpFunctions.php";

//FAZ A CONEXAO COM O BANCO DE DADOS
$lang = $_SESSION['lang'];
$dbname = $_SESSION['dbname'];
$conn = ConectaDB($dbname);

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

//INICIA O CONTEUDO//
$detid =0;
$detid = $famid+$genid+$specid+$infspecid+$detid;

$newtbname = "temp_sisbio_".substr(session_id(),0,10);

$headd = array("Marcado","EspecimenID","EDIT","Latitude_Dg","Longitude_Dg", "DATUM", "CoorRef", "REINO", "TAXON", "NIVEL_TAXONOMICO", "METODO", "UNIDADE", "QUANTIDADE", "DATA_INICIAL", "DATA_FINAL", "TIPO_DESTINACAO", "INSTITUICAO", "TOMBAMENTO");

$headexplan = array("Marcar ou desmarcar o registro", "Identificador do ESPECIME em Especimenes","Links para editar o registro","Latitude em décimos de grau","Longitude em décimos de grau","DATUM da coordenada","Reino","Nome da espécie","Nível da identificação", "Método de coleta","Unidade","Quantidade","Data_Inicial","Data_Final","Tipo_Destinação", "Instituição repositora","Tombamento");

$exportcols = array("false","false","false","true","true", "true", "true", "true", "true", "true", "true", "true", "true", "true", "true", "true", "true", "true");

$colw = array(
"Marcado" => 70,
"EspecimenID" => 0,
"EDIT" => 50,
"Latitude_Dg" => 50,
"Longitude_Dg" => 50,
 "DATUM" => 50,
 "CoorRef" => 50,
 "REINO" => 50,
 "TAXON" => 150,
 "NIVEL_TAXONOMICO" => 60,
 "METODO" => 80,
 "UNIDADE" => 80,
 "QUANTIDADE" => 10,
 "DATA_INICIAL" => 60,
 "DATA_FINAL" => 60,
 "TIPO_DESTINACAO" => 80,
 "INSTITUICAO" => 100,
  "TOMBAMENTO" => 60);
//VETORES PARA ATRIBUIR FORMATO
$listvisible = $headd;
$filt = $headd; //define colunas com filtro
$filt2 = $headd;
$coltipos = $headd;

//FAZ UM LOOP PARA CADA COLUNA E DEFINE OS VETORES DE FORMATO
	$listvisible = $headd;
	$filt = $headd;
	$filt2 = $headd;
	$coltipos = $headd;
	//COLUNAS SEM FILTRO
	$nofilter = array("EDIT");
	//COLUNAS QUE SAO IMAGENS
	$numericfilter = array("Latitude_Dg", "Longitude_Dg","QUANTIDADE");
	$imgfields = array("EDIT");
	//COLUNAS QUE NAO DEVEM APARECER
	$hidefields = array("EspecimenID","Marcado","EDIT");
	$i=1;
	$colidx = array();
	$collist = array();
	$coltipos = array();
	$colalign = $headd;
	$hidemenu = array();
	foreach ($headd as $kk => $vv) {
		$qqr = "SELECT * FROM ".$newtbname." PROCEDURE ANALYSE() WHERE Field_name LIKE '%".$newtbname.".".$vv."%'";
		$rr = @mysql_query($qqr,$conn);
		$row = @mysql_fetch_assoc($rr);
		if (!in_array($vv,$nofilter)) {
			if (in_array($vv,$numericfilter)) {
				$filt[$kk] = '#connector_text_filter';
				$colalign[$kk] = "right";
				$filt2[$kk] = "connector";
			} else {
				$filt[$kk] = "#connector_text_filter";
				$colalign[$kk] = "left";
				$filt2[$kk] = "connector";
			}
		} else {
				$filt[$kk] = '';
				$filt2[$kk] = "connector";
				$colalign[$kk] = "left";
		}
		if (!in_array($vv,$imgfields)) {
			if (in_array($vv,$numericfilter)) {
				$coltipos[$kk] = "rotxt";
			} else {
				$coltipos[$kk] = "rotxt";
			}
		} else {
			$coltipos[$kk] = 'ro';
			if (empty($colalign[$kk])) {
				$colalign[$kk] = "center";
			}
			if ($vv=='EDIT') {
			} else {
				$colidx[] = ($i-1);
			}
		}
		if (!in_array($vv,$hidefields)) {
			$listvisible[$kk] = 'false';
			$hidemenu[] = 'false';
		} else {
			$listvisible[$kk] = 'true';
			$hidemenu[] = 'true';
		}
		$collist[] = $i;
		$i++;
	}
	
	//MUDA A PRIMEIRA COLUNA, Incluido PARA EDITAVEL CHECKBOX
	$coltipos[0] = 'ch';
	$colalign[0]  = 'center';

	//IMPLODE ARRAY GERANDO STRINGS COM VALORES SEPARADOS POR virgula
	$hdd = implode(",",$headd);
	$ffilt = implode(",",$filt);
	$ffilt2 = implode(",",$filt2);
	$collist = implode(",",$collist);
	$colw = implode(",",$colw);
	$coltipos = implode(",",$coltipos);
	$listvisible = implode(",",$listvisible);
	$colidx = implode(",",$colidx);
	$colalign = implode(",",$colalign);
	$hidemenu = implode(",",$hidemenu);
	$exportcols = implode(",",$exportcols);
	$headexplan= implode(",",$headexplan);


	//CONTA O NUMERO DE REGISTROS PARA DYNAMIC LOADING DO GRID
	$qq = "SELECT count(*) as nrecs FROM ".$newtbname;
	$rr = @mysql_query($qq,$conn);
	$row = @mysql_fetch_assoc($rr);
	$nrecs = $row['nrecs'];
	
	//EXTRAI A URL 
	$url = $_SERVER['HTTP_REFERER'];
	$uu = explode("/",$url);
	$nu = count($uu)-1;
	unset($uu[$nu]);
	$url = implode("/",$uu);
	
	//NOME DO ARQUIVO QUE EXECUTA O GRID
	IF ($uuid>0) {
		$fnn = $newtbname.".php";
	} else {
		$fnn = $newtbname.".php";
	}
	$fh = fopen("temp/".$fnn, 'w');
	$stringData = "<?php
require_once(\"../dhtmlxconnector/dhtmlxConnector_php/codebase/grid_connector.php\");
require_once(\"../".$relativepathtoroot.$databaseconnection_clean."\");
function custom_format_sisbio(\$data){
	\$imagen=\"<img style='cursor:pointer;' src='icons/\".\$data->get_value(\"EDIT\").\"' height='20' onclick=\\\"javascript:small_window('".$url."/especimenes_dataform.php?especimenid=\".\$data->get_value(\"EspecimenID\").\"',1000,400,'Editando registro de amostra');\\\" onmouseover=\\\"Tip('Editar o registro');\\\" >\";
    \$data->set_value(\"EDIT\",\$imagen);
    \$ll = \$data->get_value(\"Longitude_Dg\");
    \$ll = \$ll+0;
    if (abs(\$ll)==0) {
    	\$data->set_value(\"DATUM\",'');
    	\$data->set_value(\"CoorRef\",'');
    }
}
\$grid = new GridConnector(\$res);
\$grid ->event->attach(\"beforeRender\",\"custom_format_sisbio\");
\$grid ->dynamic_loading(".($nrecs+1).");
\$grid ->render_sql(\"SELECT * FROM `".$newtbname."`\",\"EspecimenID\",\"".$hdd."\");
?>";
fwrite($fh, $stringData);
fclose($fh);

$arrofpass = array(
'ffields'   => $hdd,
'filtros'  => $ffilt,   
'filtros2'  => $ffilt2,   
'collist'  => $collist,  
'colw'  => $colw,   
'coltipos'  => $coltipos,   
'listvisible'  => $listvisible,   
'ispopup'  => 1,   
'nrecs' => $nrecs,
'tbname' => $newtbname,
'fname' => $fnn,
'colidx' => $colidx,
'colalign' => $colalign,
'hidemenu' => $hidemenu,
'usertbname' => $newtbname,
'exportcols' => $exportcols,
'headertxt' => $headexplan
);
$_SESSION['arrofpass'] = serialize($arrofpass);


?>