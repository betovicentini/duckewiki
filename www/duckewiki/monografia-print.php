<?php
session_start();
//INCLUI FUNCOES PHP E VARIAVEIS
include "functions/HeaderFooter.php";
include "functions/MyPhpFunctions.php";
include_once("functions/class.Numerical.php") ;

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
$which_css = array(
"<link href='css/geral.css' rel='stylesheet' type='text/css' />",
"<link rel='stylesheet' type='text/css' href='css/cssmenu.css' />"
);
$which_java = array(
"<script type='text/javascript' src='css/cssmenuCore.js'></script>",
"<script type='text/javascript' src='css/cssmenuAddOns.js'></script>",
"<script type='text/javascript' src='css/cssmenuAddOnsItemBullet.js'></script>"
);
$title = 'Fazer monografia';
$body = '';

$desctemptable = "temp_descricao_".$uuid;

if (!empty($monografiaid)) {
	$qq = "SELECT * FROM Monografias WHERE MonografiaID=".$monografiaid;
	$res = mysql_query($qq);
	$rr = mysql_fetch_assoc($res);

	$titulo= $rr['Titulo'];
	$especimenesids= $rr['EspecimenesIDS'];
	$plantasids= $rr['PlantasIDS'];

	$traitidsarr= $rr['TraitIdsArray'];
	$traitidsgenera= $rr['TraitIdsGenera'];
	$traitidtobreak= $rr['TraitIdToBreak'];
	$traitstobreakarr= $rr['TraitIdToBreakArray'];
	$comentarios= unserialize($rr['ComentariosArray']);
	$addparts= unserialize($rr['AddParts']);
	$specarr = explode(";",$especimenesids);
	if (count($addparts)==0 || !isset($addparts)) {
		header("location: monografia-exec.php?final=3");
	}
} else {
	header("location: monografia-exec.php?final=3");
}
if (!empty($especimenesids) & !isset($prepared)) {
	$qq = "SELECT spec.Herbaria,spec.AddColIDS,Pessoas.Abreviacao,spec.Number,spec.Day,spec.Mes,spec.Ano,spec.GPSPointID,spec.GazetteerID,spec.EspecimenID,idd.DetID,taxfam.Familia,taxgen.Genero,taxsp.Especie,taxisp.InfraEspecie,idd.GeneroID FROM Especimenes as spec JOIN Identidade as idd USING(DetID) LEFT JOIN Tax_InfraEspecies as taxisp ON taxisp.InfraEspecieID=idd.InfraEspecieID LEFT JOIN Tax_Especies as taxsp ON taxsp.EspecieID=idd.EspecieID JOIN Tax_Generos as taxgen ON taxgen.GeneroID=idd.GeneroID JOIN Tax_Familias as taxfam ON idd.FamiliaID=taxfam.FamiliaID JOIN Pessoas ON ColetorID=PessoaID WHERE ";
	$ii=0;
	$tosp = count($specarr)-1;
	for ($i=0;$i<=$tosp;$i++) {
		$vv = $specarr[$i]+0;
		if ($vv>0) {
		if ($ii==$tosp) {
			$qq = $qq." spec.EspecimenID='".$vv."'";
		} else {
			$qq = $qq." spec.EspecimenID='".$vv."' OR ";
		}
		}
		$ii++;
	}
	$qq = $qq." ORDER BY taxfam.Familia, taxgen.Genero, taxsp.Especie, taxisp.InfraEspecie";
	$qu = "DROP TABLE ".$desctemptable;
	mysql_query($qu,$conn);
	$qq = "CREATE TABLE ".$desctemptable." ".$qq;
	mysql_query($qq,$conn);
	$qu = " ALTER TABLE ".$desctemptable." CHANGE EspecimenID EspecimenID INT( 10 ) NOT NULL ";
	@mysql_query($qu,$conn);
	$qu = "ALTER TABLE ".$desctemptable." DROP PRIMARY KEY";
	@mysql_query($qu,$conn);
	$qu = "ALTER TABLE ".$desctemptable." ADD TempID INT(10) unsigned NOT NULL auto_increment PRIMARY KEY";
	@mysql_query($qu,$conn);
	$qu = "ALTER TABLE ".$desctemptable." ADD NAMEINDEX VARCHAR(500), ADD NOME VARCHAR(1000), ADD SINONIMOS VARCHAR(1000), ADD COUNTRY VARCHAR(200), ADD MAJORAREA VARCHAR(200), ADD MINORAREA VARCHAR(500), ADD GAZETTEER VARCHAR(1000)";
	@mysql_query($qu,$conn);
	$qu = "UPDATE ".$desctemptable." SET NAMEINDEX= IF(InfraEspecie IS NOT NULL,CONCAT(Genero,'_',Especie,'_',InfraEspecie),IF(Especie IS NOT NULL,CONCAT(Genero,'_ ',Especie),IF(Genero IS NOT NULL,Genero,Familia))), NOME=getcompletename(DetID,FALSE), SINONIMOS=getcompletename(DetID,TRUE)";
	@mysql_query($qu,$conn);
	$qu = "UPDATE ".$desctemptable." AS tb SET tb.COUNTRY=IF(tb.GPSPointID>0,getGPSlocalityFields(tb.GPSPointID, 'COUNTRY'), IF(tb.GazetteerID>0,getlocalityFields(tb.GazetteerID, 'COUNTRY'),'')), tb.MAJORAREA=IF(tb.GPSPointID>0,getGPSlocalityFields(tb.GPSPointID, 'MAJORAREA'), IF(tb.GazetteerID>0,getlocalityFields(tb.GazetteerID, 'MAJORAREA'),'')), tb.MINORAREA=IF(tb.GPSPointID>0,getGPSlocalityFields(tb.GPSPointID, 'MINORAREA'),
	IF(tb.GazetteerID>0,getlocalityFields(tb.GazetteerID, 'MINORAREA'),''))";
	@mysql_query($qu,$conn);
	$qu = "UPDATE ".$desctemptable." AS tb SET tb.GAZETTEER=IF(tb.GPSPointID>0,getGPSlocalityFields(tb.GPSPointID, 'GAZETTEER'),
	IF(tb.GazetteerID>0,getlocalityFields(tb.GazetteerID, 'GAZETTEER'),''))";
	@mysql_query($qu,$conn);
	$qz = "SELECT DISTINCT GeneroID,Genero, NOME,SINONIMOS, NAMEINDEX FROM ".$desctemptable." ORDER BY Familia,Genero,Especie,InfraEspecie";
	$rz = mysql_query($qz,$conn);
	$nrz = mysql_numrows($rz);
	$_SESSION['exportnresult'] = $nrz;
	//quantas especies por vez?
	$stepsize = 1;
	//quantas vezes então
	$nsteps = ceil($nrz/$stepsize);

	//marca o inicio
	$prepared = 1;
	$step=0;
}
if ($prepared==1 && $step<=$nsteps) {
	$st1 = $step+$stepsize;
	$qq = "SELECT DISTINCT GeneroID,Genero,NOME,SINONIMOS,NAMEINDEX FROM ".$desctemptable." ORDER BY Familia,Genero,NAMEINDEX";
	$qqq = $qq." LIMIT $step,$stepsize";
	//echo $qqq;
	$typeid = 'EspecimenID';

	//se for o primeiro passo, cria o arquivo e salva os metadados no inicio do arquivo
	if ($step==0) {
		unset($_SESSION['passingvars']);
		//cria o arquivo, substituindo se existir
		$export_filename = "monografia_".$_SESSION['userlastname']."_".$_SESSION['sessiondate'].".doc";
		$fh = fopen("temp/".$export_filename, 'w') or die("nao foi possivel gerar o arquivo");

//salva o cabecalho
$header = "
<html xmlns:v=\"urn:schemas-microsoft-com:vml\"
xmlns:w=\"urn:schemas-microsoft-com:office:word\"
xmlns:m=\"http:schemas.microsoft.com/office/2004/12/omml\"
xmlns:css=\"http:macVmlSchemaUri\" xmlns=\"http:www.w3.org/TR/REC-html40\">
<head>
<meta name=Title content=\"\">
<meta name=Keywords content=\"\">
<meta http-equiv=Content-Type content=\"text/html; charset='utf8'\">
<meta name=ProgId content=Word.Document>
<!--[if gte mso 9]>
<xml>
 <w:WordDocument>
  <w:View>Print</w:View>
  <w:Zoom>BestFit</w:Zoom>
 </w:WordDocument>
</xml><![endif]-->
</head>
<body>";
fwrite($fh, $header);

//inicio numeracoes
$ng = 1;
$nsp =1;
$nno = '';
$genus1 = '';
$missingfert = array();
$missingherbaria = array();
} 
elseif ($st1>0) {
	$fh = fopen("temp/".$export_filename, 'a') or die("nao foi possivel abrir o arquivo");
	$vararr = unserialize($_SESSION['passingvars']);
	foreach($vararr as $kk => $vv) {
		$$kk <= $vv;
	}
}

	if ($step>=0 & $st1>=0) {
		$res = mysql_query($qqq,$conn);
		$starttime = microtime(true);

		#para cada especie
		while ($row = mysql_fetch_assoc($res)) {
			$nome = $row['NOME'];
			$nameindex = $row['NAMEINDEX'];
			$adescricao = '';
			if ($genus1!=$row['Genero'] && !empty($traitidsgenera)) {
				/////
					$qu = "SELECT * FROM Tax_Generos WHERE GeneroID='".$row['GeneroID']."'";
					$query = mysql_query($qu,$conn);
					$rww = mysql_fetch_assoc($query);
					$gnnome = trim($rww['Genero']);
					$gnautor = trim($rww['GeneroAutor']);
					$gnbasautor = trim($rww['BasionymAutor']);
					if (empty($gnautor) && !empty($gnbasautor)) {
						$ifbas = str_replace("(","",$gnbasautor);
						$ifbas = str_replace(")","",$gnbasautor);
						$gnautor = " ".$ifbas;
					} else {
						$gnautor .= " ";
					}
					if ($gnautor==" ") { $gnautor=" <font color='red'>FALTA AUTOR</font> ";}
					$gnnome = "<b><i>".$gnnome."</i></b> ".$gnautor;
					if (!empty($rww['PubRevista'])) {
						$gnnome .= ", ".trim($rww['PubRevista']);
					}
					if (!empty($rww['PubVolume'])) {
						$gnnome .= ", ".trim($rww['PubVolume']);
					}
					if (!empty($rww['PubAno']) && $rww['PubAno']>0) {
						$gnnome .= ", ".trim($rww['PubAno']);
					} 
				//////
				$qg = "SELECT * FROM ".$desctemptable." WHERE Genero='".$row['Genero']."'";
				$rsg = mysql_query($qg,$conn);
				$specarrgen = array();
				while ($rgw = mysql_fetch_assoc($rsg)) {
					$specarrgen[] = $rgw['EspecimenID']+0;
				}
				//$genusdescription = makegenusdestription($specarrgen,$traitidsgenera,$typeid,$english,$conn);
				$genus1 = $row['Genero'];

				$header = "\n
				<hr><br />$ng. $gnnome<br />\n
				<br />".$genusdescription."<br /><br />";
				fwrite($fh, $header);

				$ngp = $ng;
				$ng++;
				$nsp = 1;
			}
			if ($nno!=$nome) {
				$adescricao = $adescricao."<br />".$ngp.".".$nsp.". ".$nome." <br />";
					$sinonimos = $row['SINONIMOS'];
					$sinarr = explode(";",$sinonimos);
					$sinome = array();
					foreach ($sinarr as $sv) {
						$sa = explode("|",$sv);
						$zzz = trim($sa[0]);
						//echopre($sa);
						if ($zzz=='especie') {
							$idd = $sa[1]+0;
							$sinome = array_merge((array)$sinome,(array)array(getnome($idd,'especie',$conn)));
						}
						if ($zzz=='infraspecies') {
							$idd = $sa[1]+0;
							$sinome = array_merge((array)$sinome,(array)array(getnome($idd,'infraespecies',$conn)));
						}
					}
					if (count($sinome)>0) {
						if ($english) {
							$adescricao = $adescricao."<br /><b>Sinônimos</b><br />";
						} else {
							$adescricao = $adescricao."<br /><b>Synonyms</b><br />";
						}
						foreach ($sinome as $vv) {
							$adescricao = $adescricao."&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;".$vv."<br />";
						}
					}
				$nsp++;
				$nno=$nome;
			}



			//get the full specimens list
			$qu = "SELECT tb.EspecimenID, COUNTRY, MAJORAREA, MINORAREA, GAZETTEER FROM ".$desctemptable." as tb WHERE NAMEINDEX='".$nameindex."' ORDER BY COUNTRY, MAJORAREA, MINORAREA, GAZETTEER";
			$rss = mysql_query($qu,$conn);
			$newspecarr = array();
			while ($rsw = mysql_fetch_assoc($rss)) {
				$newspecarr[] = $rsw['EspecimenID']+0;
			}
			//get description and used and not used specimens lists
			if ($addparts['descricao']>0) {
				$printN = $addparts['quantvarformat'];
				$resultado = makedescription2($newspecarr,$traitidsarr,$typeid,$img=FALSE,$traitidtobreak,$traitstobreakarr,$printempty=FALSE,$printN,$english,$conn);
				extract($resultado);
				//echopre($resultado);
			}
			//specimens used in descriptions. Mark especimens measured.
			$ll = listspecimens_rodriguesia($nameindex,$specids_used,specids_notused,$typeid,$desctemptable,$conn);
			//echopre ($ll);
			$lista_especimenes = $ll[0];
			$fenol = $ll[1];

			$mf = $ll[3];
			$mh = $ll[2];
			if (count($mf)>0) { $missingfert[$nameindex] = $mf;}
			if (count($mh)>0) { $missingherbaria[$nameindex] = $mh;}

			ksort($fenol);
			if ($english) {
				$fenoltab = array('Jan','Feb','Mar','Apr','May','Jun','Jul','Aug','Sep','Oct','Nov','Dec');
			} else {
				$fenoltab = array('Jan','Fev','Mar','Abr','Mai','Jun','Jul','Ago','Set','Out','Nov','Dez');
			}
			$fntable = "<table cellspacing='0' cellpadding='3' style='font-size: 8pt;border: thin solid; border-collapse: collapse' >
			<tr style='font-weight: bold;background-color:#cccccc'><td>&nbsp;</td>";
			foreach ($fenoltab as $mes) {
				$fntable .="<td style='text-align: center'>$mes</td>";
			}
			$fntable .= "</tr>";

			if (count($fenol)>0) {
				$fenologia = '';
				foreach($fenol as $key => $val) {
					$lixofenol = array(0,0,0,0,0,0,0,0,0,0,0,0);
					if (count($val)>0) {
						$ffenol = array_count_values($val);
					} else {
						$ffenol = false;
					}
					ksort($ffenol);

					if (!empty($key) && count($ffenol)>0) {

						$fenologia = $fenologia." <u>".strtoupper($key)."</u>:";
						$zf = 0;
						$nzf = count($ffenol);
						foreach ($ffenol as $mm => $fn) {
								$month = getmonthstring($mm-1,$abbre=FALSE, $english);
								$monthabrv = getmonthstring($mm-1,$abbre=TRUE,$english);
								$km = array_search($monthabrv,$fenoltab);
								if (is_numeric($km)) {
									$lixofenol[$km] = $fn;
								}
								$fen = $month." (N=".$fn.")";
								if ($zf>0 && $zf<=$nzf) {
									$sp = ', ';
								} else {
									$sp = ' ';
								}
								$fenologia = $fenologia.$sp.$fen;
								$zf++;
						}
						if (array_sum($lixofenol)>0) {
								$fntable .= "<tr ><td>".strtoupper($key)."</td>";
								foreach ($lixofenol as $mval) {
									if ($mval==0) { 
									$ms = '';
									$fntable .="<td>$ms</td>";
									} else { 
									$ms= $mval;
									$fntable .="<td style='font-weight: bold; text-align: center;'>$ms</td>";

									}

								}
								$fntable .= "</tr>";
						}
						$fenologia = $fenologia.'.';
					}
				}
				$fntable .="</table>";
			}
			if ($addparts['descricao']>0) {
					$adescricao = $adescricao."<br />".$mydescription."<br /><br />";
			}
			if ($addparts['habitat']>0) {
				$myhabitat = printhabitat($newspecarr,$typeid,$formidhabitat,$printN=FALSE,$conn);
				if ($myhabitat) { 
					$adescricao = $adescricao."<b>".strtoupper(GetLangVar('namehabitat'))."</b>. ".$myhabitat;
				}
			}
			if ($addparts['fenologia']>0) {
				if ($english) {
							$fenolog="PHENOLOGY";
					} else {
							$fenolog="FENOLOGIA";
				}
				if (!empty($fenologia)) {
					$adescricao = $adescricao."<br /><br /><b>$fenolog</b>. ".$fenologia."<br />".$fntable."<br />";
				}
			}
			if ($addparts['comentarios']>0) {
				if ($english) {
							$spnotas="NOTES ON SPECIES";
					} else {
							$spnotas="NOTAS & COMENTARIOS";
				}
				if (!empty($comentarios[$nameindex])) {
					$adescricao = $adescricao."<br /><br /><b>$spnotas</b>. ".$comentarios[$nameindex]."<br />";
				}
			}
			if ($addparts['materiaexaminado']>0) {
				if (count($specids_notused)>0) {
					if ($english) {
							$zs = 'AVAILABLE SPECIMENS';
							$matex = '(examined in italics)';
					} else {
						$zs = strtoupper(GetLangVar('exsicatasdisponiveis'));
						$zs = strtupperacentos($zs);
						$matex = mb_strtolower(GetLangVar('materialexaminado'));
						$matex = " (".strtloweracentos($matex)." em itálico)";
					}
				} else {
					if ($english) {
							$zs = 'EXAMINED SPECIMENS';
							$matex = '';
					} else {
						$zs = strtoupper(GetLangVar('materialexaminado'));
						$zs = strtupperacentos($zs);
						$matex = '';
					}
				}
				$adescricao = $adescricao."<br /><br /><b>".$zs."</b>: ".$lista_especimenes.".".$matex."<br />";
			}
			fwrite($fh, $adescricao);
			flush();
		}
	}


	//inclui metadados no inicio, pode colocar no final alterando a condicao com st1==nrecs
	$ns = count($specarr)-1;
	if ($step==$ns) {
		$ns = count($specarr)-1;
		if ($ns<=50) { $ns=$ns;} else { $ns=50;}
		$spca = array();
		for ($i=0;$i<=$ns;$i++) {
			$spca[] = $specarr[$i];
		}
		$metadados = createmetadadostable($spca,$traitidsarr,$traitidtobreak,$traitstobreakarr,$typeid,$english,$conn);
	if ($english) {
		$mmtd = "
<b>METADADOS</b>
<br /><br />
Table with variables used in descriptions of all species. These variable may not appear in every single description, but they always appear in the order indicated in the table.
<br />
<br />".$metadados."<br />";
	} else {
		$mmtd = "
<b>METADADOS</b>
<br /><br />
Tabela com as variáveis utilizadas nas descrições das espécies. Essas variáveis não aparecem em todas as descrições, mas aparecem sempre na ordem indicada na tabela.
<br />
<br />".$metadados."<br />";
	}
		fwrite($fh, $mmtd);
	}
	fclose($fh);



	//array que precisa enviar:
	$vararr = array($missingfert,$missingherbaria,$genus1,$nsp,$ng,$nno);
	$kkeys = array("missingfert", "missingherbaria", "genus1", "nsp", "ng", "nno");
	$vararr = array_combine($kkeys,$vararr);
	$_SESSION['passingvars'] = serialize($vararr);

	$endtime = microtime(true); 
	$exectime = $endtime-$starttime;
	$exectime = round(($exectime*100)/60,4);
	if ($step==0) {
		$tfalta = ceil($exectime*$nsteps);
	} else {
		$tfalta = $tfalta-$exectime;
	}
FazHeader($title,$body,$which_css,$which_java,$menu);
	$step = $st1+1;
//if ($step<=6) {
echo "
<form action='monografia-print.php' name='myform' method='post'>
  <input type='hidden' name='prepared' value='".$prepared."'>
  <input type='hidden' name='nsteps' value='".$nsteps."'>
  <input type='hidden' name='step' value='".$step."'>
  <input type='hidden' name='export_filename' value='".$export_filename."'>
  <input type='hidden' name='stepsize' value='".$stepsize."'>
  <input type='hidden' name='tfalta' value='".$tfalta."'>
  <input type='hidden' name='monografiaid' value='".$monografiaid."'>
  <input type='hidden' name='english' value='".$english."'>
  <input type='hidden' name='ispopup' value='".$ispopup."'>
<br />
<table align='center' cellpadding='5' width='50%' class='erro'>
  <tr><td>Processando <b>".$nameindex."</b> (".$step." de ".($nsteps+1).") AGUARDE!</td></tr>
  <tr><td>Faltam aproximadamente ".round($tfalta,2)."  segundos para terminar</td></tr>
</table>
</form>";
echo "<script language=\"JavaScript\">setTimeout('document.myform.submit()',0.00001);</script>";
//}
FazFooter($which_java,$calendar=FALSE,$footer=$menu);

} 
elseif ($step>$nsteps) {
	$fh = fopen("temp/".$export_filename, 'a') or die("nao foi possivel abrir o arquivo");
	$vararr = unserialize($_SESSION['passingvars']);
	foreach($vararr as $kk => $vv) {
		$$kk <= $vv;
	}

		if (count($missingfert)>0) {
			$missfert = '';
			$filtroname = $_SESSION['userlastname']."_faltaFert";
			$missfert = "<br /><hr>FALTANDO FERTILIDADE (filtro criado para essa amostras com o nome de $filtroname):";
			$misfe = array();
			foreach ($missingfert as $kk => $vv) {
				$missfert .= "<br />".$kk."<br />";
				foreach ($vv as $ky => $spref) {
					$missfert .= $spref."<br />";
					$sid = explode("_",$ky);
					if ($sid[1]>0) {
						$misfe[] = $sid[1]+0;
					}
				}
			}
			$qq = "SELECT FROM Filtros WHERE FiltroName='".$filtroname."'";
			$rr = mysql_query($qq,$conn);
			$specids = implode(";",$misfe);
			if ($rr) {
				$qu = "UPDATE Filtros SET EspecimenesIDS=".$specids." WHERE FiltroName='".$filtroname."'";
				mysql_query($qu,$conn);
			} else {
				$qu = "INSERT INTO Filtros (FiltroName,EspecimenesIDS) VALUES ('".$filtroname."','".$specids."')";
				mysql_query($qu,$conn);
			}
			fwrite($fh, $missfert);
		}
		if (count($missingherbaria)>0) {
			$filtroname = $_SESSION['userlastname']."_faltaherb";
			$misinpa = "<br /><br /><hr>FALTANDO HERBARIA (filtro criado para essa amostras com o nome de $filtroname):";
			$misherb = array();
			foreach ($missingherbaria as $kk => $vv) {
				$misinpa .= "<br />".$kk."<br />";
				foreach ($vv as $ky => $spref) {
					$misinpa .= $spref."<br />";
					$sid = explode("_",$ky);
					if ($sid[1]>0) {
						$misherb[] = $sid[1]+0;
					}
				}
			}
			$qq = "SELECT FROM Filtros WHERE FiltroName='".$filtroname."'";
			$rr = mysql_query($qq,$conn);
			$specids = implode(";",$misherb);
			if ($rr) {
				$qu = "UPDATE Filtros SET EspecimenesIDS=".$specids." WHERE FiltroName='".$filtroname."'";
				mysql_query($qu,$conn);
			} else {
				$qu = "INSERT INTO Filtros (FiltroName,EspecimenesIDS) VALUES ('".$filtroname."','".$specids."')";
				mysql_query($qu,$conn);
			}
			fwrite($fh, $misinpa);
		}
		$fimm = "</body></html>"; 
		fwrite($fh, $fimm);
		fclose($fh);


	if (file_exists("temp/".$export_filename)) {
///////
FazHeader($title,$body,$which_css,$which_java,$menu);

echo "
<br />
<table class='myformtable' cellpadding='5' align='center' width=70%>
<thead>
<tr><td colspan='100%'>Resultados</td></tr>
</thead>
<tbody>
<tr>
    <td><a href=\"download.php?file=temp/".$export_filename."\">Baixar tratamento: $export_filename</a></td>
</tr>
<tr>
  <td colspan='100%'><hr></td>
</tr>
</tbody>
</table>";
$which_java = array("<script type='text/javascript' src='javascript/myjavascripts.js'></script>",
"<!-- Create Menu Settings: (Menu ID, Is Vertical, Show Timer, Hide Timer, On Click ('all' or 'lev2'), Right to Left, Horizontal Subs, Flush Left, Flush Top) -->",
"<script type='text/javascript'>qm_create(0,false,0,500,false,false,false,false,false);</script>");
FazFooter($which_java,$calendar=FALSE,$footer=$menu);
////////
	} 
	else {
		header("location: monografia-form.php?ispopup=1");
	}


} //if !empty($especimensids)

?>