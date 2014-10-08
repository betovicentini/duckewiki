<?php
set_time_limit(0);
session_start();
//Check whether the session variable
include "functions/HeaderFooter.php";
include "functions/SelectOptions.php";
include_once("functions/class.Numerical.php") ;
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

$uid = $_SESSION['userid'];

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

				
} else {
	header("location: monografia-exec.php?final=3");
}
header("Content-type: application/vnd.ms-word"); 
header("Content-Disposition: attachment;Filename=descricao.doc"); 
echo "
<html xmlns:v=\"urn:schemas-microsoft-com:vml\"
xmlns:w=\"urn:schemas-microsoft-com:office:word\"
xmlns:m=\"http:schemas.microsoft.com/office/2004/12/omml\"
xmlns:css=\"http:macVmlSchemaUri\" xmlns=\"http:www.w3.org/TR/REC-html40\">

<head>
<meta name=Title content=\"\">
<meta name=Keywords content=\"\">
<meta http-equiv=Content-Type content=\"text/html; charset='Windows-1252'\">
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

//HTMLheaders('');
//$dd = @unserialize($_SESSION['destvararray']);
//@extract($dd);
//echopre($rr);
if (!empty($especimenesids)) {

		//order by taxanomy, alphabetically
		$specarr = explode(";",$especimenesids);
		$qq = "SELECT Herbaria,AddColIDS,Abreviacao,Number,Day,Mes,Ano,GPSPointID,GazetteerID,EspecimenID,idd.DetID,Familia,Genero,Especie,InfraEspecie,idd.GeneroID FROM Especimenes as spec JOIN Identidade as idd USING(DetID) LEFT JOIN Tax_InfraEspecies as taxisp ON taxisp.InfraEspecieID=idd.InfraEspecieID LEFT JOIN Tax_Especies as taxsp ON taxsp.EspecieID=idd.EspecieID JOIN Tax_Generos as taxgen ON taxgen.GeneroID=idd.GeneroID JOIN Tax_Familias as taxfam ON idd.FamiliaID=taxfam.FamiliaID JOIN Pessoas ON ColetorID=PessoaID WHERE ";
		$nn = count($specarr)-1;
		$ii=0;
		foreach ($specarr as $kk => $vv) {
			if ($ii==$nn) {
				$qq = $qq." EspecimenID='".$vv."'";
			} else {
				$qq = $qq." EspecimenID='".$vv."' OR ";
			}
			$ii++;
		}

		$qq = $qq." ORDER BY Familia,Genero,Especie,InfraEspecie";
		//$res = mysql_query($qq,$conn);	
		
		$qu = "DROP TABLE Temp_Descricao_".$uid;
		mysql_query($qu,$conn);	
		$qu = "CREATE TABLE Temp_Descricao_".$uid." ".$qq;
		//echo $qu;
		mysql_query($qu,$conn);
		$qu = " ALTER TABLE Temp_Descricao_".$uid." CHANGE EspecimenID EspecimenID INT( 10 ) NOT NULL ";
		mysql_query($qu,$conn);
		$qu = "ALTER TABLE Temp_Descricao_".$uid." DROP PRIMARY KEY";
		mysql_query($qu,$conn);

		$qu = "ALTER TABLE Temp_Descricao_".$uid." ADD TempID INT(10) unsigned NOT NULL auto_increment PRIMARY KEY";
		mysql_query($qu,$conn);

		$qu = "ALTER TABLE Temp_Descricao_".$uid."  ADD SINONIMOS VARCHAR(100), ADD NOME VARCHAR(200), ADD COUNTRY CHAR(20), ADD MAJORAREA CHAR(50), ADD MINORAREA CHAR(50), ADD GAZETTEER CHAR(100), ADD LATITUDE CHAR(20), ADD LONGITUDE CHAR(20), ADD ALTITUDE CHAR(10), ADD COORD_PRECISION CHAR(10), ADD NS CHAR(2), ADD EW CHAR(2), ADD DATUM CHAR(10), ADD NAMEINDEX VARCHAR(200)";
		mysql_query($qu,$conn);

		//echo $qu."<br>";

		$qu = "SELECT * FROM Temp_Descricao_".$uid;
		$res = mysql_query($qu,$conn);

		$nno = '';
		while ($row = mysql_fetch_assoc($res)) {
			$tid = $row['TempID'];
			$newsp = array($row['EspecimenID']);
			$detid = trim($row['DetID']);
			$arn = getcompletename($detid,$conn);
			$sinonimos = $arn[1];
			$nome = $arn[0];
			
			$simplename = explode(" ",getdetnoautor($detid,$conn));
			$simplename = implode("_",$simplename);
			
			
			$gpspointid = $row['GPSPointID'];
			$gazetteerid = $row['GazetteerID'];
			$genero = $row['Genero'];
			$familia = $row['Familia'];

			if ($gpspointid>0) {
				$localarr = getGPSlocalityFields($gpspointid,$name=FALSE,$conn);
			} elseif ($gazetteerid>0) {
				$localarr = getlocalityFields($gazetteerid,$coord=TRUE,$conn);
			}
			$newarr = array('NOME' => $nome, 'SINONIMOS' => $sinonimos, 'NAMEINDEX' => $simplename);
			$arrofvals = array_merge((array)$localarr,(array)$newarr);
			$qu = "UPDATE Temp_Descricao_".$uid." SET ";
			$jj=0;
			$nj = count($arrofvals)-1;
			foreach ($arrofvals as $kkk => $vvv) {
				if ($jj==$nj) {
					$qu = $qu." ".$kkk."='".$vvv."'";
				} else {
					$qu = $qu." ".$kkk."='".$vvv."', ";
				}
				$jj++;
			}
			$qu = $qu." WHERE TempID='".$tid."'";
			mysql_query($qu,$conn);
		}
		
		
		$qu = "SELECT DISTINCT GeneroID,Genero,NOME,SINONIMOS,NAMEINDEX FROM Temp_Descricao_".$uid." ORDER BY Familia,Genero,Especie,InfraEspecie";
		$res = mysql_query($qu,$conn);
		
		$typeid = 'EspecimenID';	
		//inclui metadados no inicio
		echo "<b>METADADOS</b>
		<br><br>
		Tabela com as variáveis utilizadas nas descrições das espécies. Essas variáveis não aparecem em todas as descrições, mas aparecem sempre na ordem indicada na tabela.<br>";
		$ns = count($specarr)-1;
		if ($ns<=50) { $ns=$ns;} else { $ns=50;}
		$spca = array();
		for ($i=0;$i<=$ns;$i++) {
			$spca[] = $specarr[$i];
		}
		$metadados = createmetadadostable($spca,$traitidsarr,$traitidtobreak,$traitstobreakarr,$typeid,$conn);
		echo "<br>".$metadados."<br>";
		$nsp =1;
		$ng = 1;
		$nno = '';
		$missingfert = array();
		$missingherbaria = array();
		$genus1 = '';
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
				$qg = "SELECT * FROM Temp_Descricao_".$uid." WHERE Genero='".$row['Genero']."'";
				$rsg = mysql_query($qg,$conn);
				$specarrgen = array();
				while ($rgw = mysql_fetch_assoc($rsg)) {
					$specarrgen[] = $rgw['EspecimenID']+0;	
				}
				$genusdescription = makegenusdestription($specarrgen,$traitidsgenera,$typeid,$conn);
				$genus1 = $row['Genero'];
				
				$qg = "SELECT * FROM Temp_Descricao_".$uid." WHERE Genero='".$row['Genero']."'";
				
				echo "<hr><br>
				$ng. $gnnome<br><br>".$genusdescription."<br><br>";
				$ngp = $ng;
				$ng++;
				$nsp = 1;
				flush ();
			}
			
			if ($nno!=$nome) {
				$adescricao = $adescricao."<br>".$ngp.".".$nsp.". ".$nome." <br>";
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
						$adescricao = $adescricao."<br><b>Sinônimos</b><br>";
						foreach ($sinome as $vv) {
							$adescricao = $adescricao."&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;".$vv."<br>";
						}
					}
				$nsp++;
				$nno=$nome;
			}
			
			
			
			//get the full specimens list		
			$qu = "SELECT * FROM Temp_Descricao_".$uid." WHERE NOME='".$nome."' ORDER BY COUNTRY, MAJORAREA, MINORAREA, GAZETTEER, LONGITUDE, LATITUDE";
			//echo $qu;
			$rss = mysql_query($qu,$conn);
			$newspecarr = array();
			while ($rsw = mysql_fetch_assoc($rss)) {
				$newspecarr[] = $rsw['EspecimenID']+0;	
			}

			//get description and used and not used specimens lists
			
	
			if ($addparts['descricao']>0) {
				$printN = $addparts['quantvarformat'];
				$resultado = makedescription2($newspecarr,$traitidsarr,$typeid,$img=FALSE,$traitidtobreak,$traitstobreakarr,$printempty=FALSE,$printN,$conn);
				extract($resultado);
			}

			//specimens used in descriptions. Mark especimens measured.
			$ll = listspecimens_rodriguesia($nameindex,$specids_used,specids_notused,$typeid,$conn);
			$lista_especimenes = $ll[0];
			$fenol = $ll[1];
			
			$mf = $ll[3];
			$mh = $ll[2];
			if (count($mf)>0) { $missingfert[$nameindex] = $mf;}
			if (count($mh)>0) { $missingherbaria[$nameindex] = $mh;}
			
			ksort($fenol);
			$fenoltab = array('Jan','Fev','Mar','Abr','Mai','Jun','Jul','Ago','Set','Out','Nov','Dez');
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
								$month = getmonthstring($mm-1,$abbre=FALSE);
								$monthabrv = getmonthstring($mm-1,$abbre=TRUE);
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
					$adescricao = $adescricao."<br>".$mydescription."<br><br>";	
			}
			
			
			if ($addparts['habitat']>0) {	
				$habitatformid = 43;
				$myhabitat = printhabitat($newspecarr,$typeid,$habitatformid,$printN=FALSE,$conn);
				if ($myhabitat) { 
					$adescricao = $adescricao."<b>".strtoupper(GetLangVar('namehabitat'))."</b>. ".$myhabitat;
				}
			}

			if ($addparts['fenologia']>0) {
				if (!empty($fenologia)) {
					$adescricao = $adescricao."<br><br><b>FENOLOGIA</b>. ".$fenologia."<br>".$fntable."<br>";
				}
			}
			
			if ($addparts['comentarios']>0) {
				if (!empty($comentarios[$nameindex])) {
					$adescricao = $adescricao."<br><br><b>NOTAS</b>. ".$comentarios[$nameindex]."<br>";	
				}
			}
			
			if ($addparts['materiaexaminado']>0) {
				if (count($specids_notused)>0) {
					$zs = strtoupper(GetLangVar('exsicatasdisponiveis'));
					$zs = strtupperacentos($zs);
					$matex = strtolower(GetLangVar('materialexaminado'));
					$matex = " (".strtloweracentos($matex)." em itálico)";
				} else {
					$zs = strtoupper(GetLangVar('materialexaminado'));
					$zs = strtupperacentos($zs);			
					$matex = '';
				}
				$adescricao = $adescricao."<br><br><b>".$zs."</b>: ".$lista_especimenes.".".$matex."<br>";
			}
			echo $adescricao;

			flush();
		}
		


		
		//echopre($_SESSION);

		if (count($missingfert)>0) {
			$filtroname = $_SESSION['userlastname']."_faltaFert";
			echo "<br><hr>FALTANDO FERTILIDADE (filtro criado para essa amostras com o nome de $filtroname):";
			$misfe = array();
			foreach ($missingfert as $kk => $vv) {
				echo "<br>".$kk."<br>";
				foreach ($vv as $ky => $spref) {
					echo $spref."<br>";
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
		}
		if (count($missingherbaria)>0) {
			$filtroname = $_SESSION['userlastname']."_faltaherb";
			echo "<br><br><hr>FALTANDO HERBARIA (filtro criado para essa amostras com o nome de $filtroname):";
			$misherb = array();
			foreach ($missingherbaria as $kk => $vv) {
				echo "<br>".$kk."<br>";
				foreach ($vv as $ky => $spref) {
					echo $spref."<br>";
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
		}
} //if !empty($especimensids)
echo "</body>"; 
echo "</html>"; 
//HTMLtrailers();
?>