<?php
session_start();
//Check whether the session variable
include "functions/HeaderFooter.php";
include "functions/SelectOptions.php";
include "functions/ImportData.php";

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

//print_r($_GET);
HTMLheaders('');
		
		//taxonomic summary
		$qq = "SELECT DISTINCT Genero,Especie FROM Tax_Especies JOIN Tax_Generos USING(GeneroID) JOIN Tax_Familias USING(FamiliaID) WHERE Genero='".$nn."'";
		$rr = mysql_query($qq,$conn);
		$nsp = mysql_numrows($rr);

		$qq = "SELECT DISTINCT Genero,Especie,InfraEspecie FROM Tax_InfraEspecies JOIN Tax_Especies USING(EspecieID) JOIN Tax_Generos USING(GeneroID) JOIN Tax_Familias USING(FamiliaID) WHERE Genero='".$nn."'";
		$rr = mysql_query($qq,$conn);
		$ninfsp = mysql_numrows($rr);
		
		$qq = "SELECT * FROM Especimenes JOIN Identidade USING(DetID) JOIN Tax_Generos ON Identidade.GeneroID=Tax_Generos.GeneroID WHERE Genero='".$nn."'";
		$rr = mysql_query($qq,$conn);
		$ncols = mysql_numrows($rr); 
		
		$qq = "SELECT * FROM Plantas JOIN Identidade USING(DetID) JOIN Tax_Generos ON Identidade.GeneroID=Tax_Generos.GeneroID WHERE Genero='".$nn."'";
		$rr = mysql_query($qq,$conn);
		$nplantas = mysql_numrows($rr); 
 
		
		//imagens
		$qq = "(SELECT TraitVariation as imagens FROM Traits_variation JOIN Traits USING (TraitID) JOIN Especimenes  ON Traits_variation.EspecimenID=Especimenes.EspecimenID JOIN Identidade USING(DetID) JOIN Tax_Generos ON Identidade.GeneroID=Tax_Generos.GeneroID WHERE TraitTipo='Variavel|Imagem' AND Genero='".$nn."') UNION (SELECT TraitVariation as imagens FROM Traits_variation JOIN Traits USING (TraitID) JOIN Plantas ON Traits_variation.PlantaID=Plantas.PlantaID JOIN Identidade USING(DetID) JOIN Tax_Generos ON Identidade.GeneroID=Tax_Generos.GeneroID WHERE TraitTipo='Variavel|Imagem' AND Genero='".$nn."') UNION (SELECT TraitVariation as imagens FROM Traits_variation JOIN Traits USING (TraitID) JOIN Tax_Generos ON Traits_variation.GeneroID=Tax_Generos.GeneroID WHERE TraitTipo='Variavel|Imagem' AND Genero='".$nn."')";
		$rr = mysql_query($qq,$conn);
		$nrec = mysql_numrows($rr);
		if ($nrec>0) {
			while ($row = mysql_fetch_assoc($rr)) {
				$img = explode(";",$row['imagens']);
				$imgs = array_merge((array)$imgs,(array)$img);				
			}
			$imgs = array_unique($imgs);
			$imagens = implode(";",$imgs);
			$nimgs = count($imgs);
		} else {
			$nimgs = 0;
		}
	
		
		//informacoes do genero
		
		$qq = "SELECT GeneroAutor,Genero,Tax_Generos.Sinonimos as Sinonimos,Tax_Generos.Notas as Notas,Familia,Ordem,Tax_Generos.Valid FROM Tax_Generos JOIN Tax_Familias USING(FamiliaID) WHERE Genero='".$nn."'";
		$rr = mysql_query($qq,$conn);
		if ($rr) {
			$row = mysql_fetch_assoc($rr);
			$genusid = $row['GeneroID'];
			$ordem = $row['Ordem'];			
			$fam = $row['Familia'];
			$famautor = $row['GeneroAutor'];			
			$famsin = trim($row['Sinonimos']);
			$famnotes = trim($row['Notas']);
			$valid = $row['Valid'];
			if ($valid==1) {
				if (!empty($famsin)) {
				$ff = explode(";",$famsin);
				$sino = '';
				foreach ($ff as $k => $vv) {
					$fid = explode("|",$vv);
					$ffid = $fid[1];
						if ($fid[0]=='genero') {
							$qq = "SELECT Genero as nome FROM Tax_Generos WHERE 	GeneroID='".$ffid."'";
						}
						if ($fid[0]=='familia') {
							$qq = "SELECT Familia as nome FROM Tax_Familias WHERE FamiliaID='".$ffid."'";
						}
						$rw = mysql_query($qq,$conn);
						$rww = mysql_fetch_assoc($rw);
						$sinome = "<a href=\"?nn=".$rww['nome']."\">".$rww['nome']."</a>";
						if (!empty($sino)) {					
							$sino = $sino." | ".$sinome;
						} else {
							$sino = $sinome;
						}
				}
				$sino = trim($sino);
				}
			} else {
				$qq = "(SELECT Genero as nome  FROM Tax_Generos WHERE Sinonimos LIKE '%genero|".$genusid.";%' OR Sinonimos LIKE '%genero|".$genusid."') UNION (SELECT Familia as nome  FROM Tax_Familias WHERE Sinonimos LIKE '%genero|".$genusid.";%' OR `Sinonimos` LIKE '%genero|".$genusid."')";
				//echo $qq;
				$rrw = mysql_query($qq,$conn);
				$roww = mysql_fetch_assoc($rrw);
				$sino = "<a href=\"?nn=".$roww['nome']."\">".$roww['nome']."</a>";
			}
	}
		if ($valid==1) {$namevalid = mb_strtolower(GetLangVar('namevalido'));}
		if ($valid==0) {$namevalid = mb_strtolower(GetLangVar('nameinvalido'));}
		
		$orderurl = "http://www.mobot.org/mobot/research/apweb/orders/".mb_strtolower($ordem)."web.htm#".$ordem;
		$famurl = "http://www.mobot.org/mobot/research/apweb/orders/".mb_strtolower($ordem)."web.htm#".$fam;
		$resurl = file_exists($orderurl);
		if (!$resurl) {
			$orderurl = "http://www.mobot.org/mobot/research/apweb/orders/".mb_strtolower($ordem)."web2.htm#".$ordem;
			$famurl = "http://www.mobot.org/mobot/research/apweb/orders/".mb_strtolower($ordem)."web2.htm#".$fam;

			$resurl2 = file_exists($orderurl);
			if (!$resurl2) {
				$orderurl = "http://www.mobot.org/mobot/research/apweb/orders/".$ordem.".html#".$ordem;
				$famurl = "http://www.mobot.org/mobot/research/apweb/orders/".$ordem.".html#".$fam;

				$resurl3 = file_exists($orderurl);
				if ($resurl3) {
					$orderlink = "<a href='".$orderurl."' >".$ordem."</a>";
				} else { $orderlink = $ordem;}				
			}  else { 
					$orderlink = "<a href='".$orderurl."' >".$ordem."</a>";
			}
		} else {
					$orderlink = "<a href='".$orderurl."' >".$ordem."</a>";
		}
		
		$famlink = "<a href='".$famurl."' >".$fam."</a>";

		echo "<br>
		<table class='tableform' align='center'>
		<tr >
			<td  colspan=2 class='tabhead'><i>$nn</i></td>
		</tr>
		<tr>";
			if ($valid==1) {
				echo "<td width=70%><table align='left' cellpadding='5'>";
			} else {
				echo "<td ><table align='left' cellpadding='5' width='100%'>";			
			}
			echo "
				<tr>
					<td class='tdsmallbold'>".GetLangVar('nametaxonomy')."</td>
					<td class='tdsmallbold'><i>$nn</i>&nbsp;$famautor ($famlink | $orderlink)&nbsp;";
					if ($valid==0) {
						echo "[$namevalid]";					
					}
					echo "</td>
				</tr>
				<tr>
					<td class='tdsmallbold'>".GetLangVar('namesinonimos')."</td>
					<td class='tdformnotes'>$sino";
					if ($valid==0) {
						echo " [".mb_strtolower(GetLangVar('namevalido'))."]";					
					}
					echo "</td>
				</tr>	
				<tr>
					<td class='tdsmallbold'>".GetLangVar('nameobs')."s</td>
					<td class='tdformnotes'>$famnotes</td>
				</tr>
			</table>
			</td>";	
			if ($valid==1) {
				echo "<td width=30%><table align='left' cellpadding='5'>
				<tr>
					<td class='tdicon' align='center'><a href='#'><img src=\"icons/tropical-plant-icon.png\" alt=\"Descricao Morfologica\" height=40 class=imgicon></a><br>Morfo</td>
					<td class='tdicon' align='center'><a href='#'><img src=\"icons/library-icon.png\" alt=\"References\" height=40 class=imgicon></a><br>Refs</td>
					<td class='tdicon' align='center'><a href='$famurl'><img src=\"icons/filogeny-icon.jpg\" alt=\"APWEB\" height=40 class=imgicon></a><br>APWeb
					</td>
					<td class='tdformnotes'></td>
				</tr>	
				</table></td>";
			}
		echo "</tr>";
		if ($valid==1) {
		echo "
		<tr><td colspan=2>
			<table class='tablethinborder' width=100% align='center' cellpadding='4'>
		<thead>
		<tr>
			<th colspan=6 class='tabsubhead'>".GetLangVar('messagerecordssummary')."</td>
		</tr>
		<tr>			
				<th colspan=3 class='small' >".GetLangVar('nametaxonomicos')."</th>
				<th colspan=3 class='small'>".GetLangVar('namecolecoes')."</th>
		</tr>
		<tr>			
				<th class='small'>".GetLangVar('namespecies')."</th>
				<th class='small'>".GetLangVar('nameinfraspecies')."</th>
				<th class='small'><img src=\"icons/foto-icon.png\" alt=\"".GetLangVar('nameimagens')."\" height=25 border=0>&nbsp;".GetLangVar('nameimagens')."</th>
				<th class='small'><img src=\"icons/specimen-icon.png\" alt=\"".GetLangVar('namecoleta')."\" height=25 border=0>&nbsp;".GetLangVar('namecoleta')."s</th>
				<th class='small'><img src=\"icons/tree-icon.png\" alt=\"".GetLangVar('namecolecaoviva')."\" height=25 border=0>&nbsp;".GetLangVar('namecolecaoviva')."</th>

		</tr>
		</thead>
		<tbody>
		<tr>
			<td align='center'>
				<a href=\"?nn=$nn&listsp=1\">".$nsp."</a>
			</td>
			<td align='center'><a href=\"?nn=$nn&listinfsp=1\">".$ninfsp."</a></td>
			<td align='center'>";
				if ($nimgs>0) {
					$fn = $imagens;
					echo "<a href=\"javascript:small_window('showpicture.php?fn=$fn',700,500,'MostrarImg');\"><b>".
					$nimgs."</b></a>";
				} else { echo $nimgs;}
			
			echo "</td>
			<td align='center'>";
			if ($ncols>0) {
					echo "<a href=\"?nn=$nn&listcol=1\">".$ncols."</a>";
				} else { echo $ncols;}
			echo "			
			</td>";
			if ($listplantas==1) { echo "<td align='center' class='clicked'>"; } else { echo "<td align='center'>";}
			if ($nplantas>0) {
				echo "<a href=\"?nn=$nn&listplantas=1\">".$nplantas."</a>";
			} else {
				echo $nplantas;
			}
			echo "</td>
		</tr>
		<tbody>
		</table>
		</td>
		</tr>
		";	
		if ($listsp==1 && $nsp>0) {
			echo "
				<tr class='tdsmallbold'>
					<td colspan=2><hr></td>
				</tr>
				<tr>
					<td colspan=2>
					<table class='sortable autostripe' align='center' cellspacing='0' width='95%'>
					<thead>
						<tr>
							<th align='center' align='center' class='tabhead'>".GetLangVar('namegenus')."s</th>					
							<th align='center' align='center' class='tabhead'>".GetLangVar('namespecies')."</th>					
							<th align='center' align='center' class='tabhead'>".GetLangVar('namegeodistribution')." (IPNI)</th>					
						</tr>
					</thead>
					<tbody>
				</tr>";			
				$qq = "SELECT * FROM Tax_Especies JOIN Tax_Generos USING(GeneroID) JOIN Tax_Familias USING(FamiliaID) WHERE Genero='".$nn."'  ORDER BY Genero,Especie";				
				//echo $qq;
				$rr = mysql_query($qq,$conn);
				$bgi=1;
				while ($gen = mysql_fetch_assoc($rr)) {
						$gg = $gen['Genero'];
						$gsp = $gen['Especie'];
						$gau1 = $gen['BasionymAutor'];
						$gau2 = $gen['EspecieAutor'];
						$geodist = $gen['GeoDistribution'];

						echo "<tr";
							if ($bgi % 2 != 0){ echo ' class=odd ' ;}
							$bgi++;
							echo 	"><td ><a href='search-name-gen.php?nn=$gg'><i>".$gg."</i></a></td><td><a href='search-name-sp.php?nn=$gsp&gen=$gg'><i>".$gsp."</i></a>&nbsp;$gau1&nbsp;$gau2</td><td >".$geodist."</td>
							</tr>";		
				}
				echo "</tbody></table></td></tr>";

		}
		if ($listinfsp==1 && $ninfsp>0) {
			echo "
				<tr class='tdsmallbold'>
					<td colspan=2><hr></td>
				</tr>
				<tr>
					<td colspan=2>
					<table class='sortable autostripe' align='center' cellspacing='0' width='100%'>
					<thead >
						<tr>
							<th align='center'>".GetLangVar('namegenus')."s</th>					
							<th align='center'>".GetLangVar('namespecies')."</th>					
							<th align='center'>".GetLangVar('nametipo')."</th>					
							<th align='center'>".GetLangVar('nameinfraspecies')."</th>					
						</tr>
					</thead>
					<tbody >";			
				$qq = "SELECT Genero,Especie,EspecieAutor,Tax_Especies.BasionymAutor as SpBasAu,InfraEspecieNivel as nivel,InfraEspecie,InfraEspecieAutor as autor,Tax_InfraEspecies.BasionymAutor FROM Tax_InfraEspecies JOIN Tax_Especies USING(EspecieID) JOIN Tax_Generos USING(GeneroID) JOIN Tax_Familias USING(FamiliaID) WHERE Genero='".$nn."'  ORDER BY Genero,Especie,InfraEspecie";
				$rr = mysql_query($qq,$conn);
				$bgi=1;
				while ($gen = mysql_fetch_assoc($rr)) {
						$gg = $gen['Genero'];
						$gsp = $gen['Especie'];
						$gau1 = $gen['SpBasAu'];
						$gau2 = $gen['EspecieAutor'];
						$gniv = $gen['nivel'];
						$gn = explode(".",$gniv);
						$gniv = $gn[0];

						$ginfsp = $gen['InfraEspecie'];						
						$gau3 = $gen['autor'];
						$gau4 = $gen['BasionymAutor'];
						echo "<tr";
						if ($bgi % 2 != 0){ echo ' class=odd ' ;}
						$bgi++;
						echo 	"><td><a href='search-name-gen.php?nn=$gg'><i>".$gg."</i></a>&nbsp;</td><td><a href='search-name-sp.php?nn=$gsp&gen=$gg'><i>".$gsp."</i></a>&nbsp;$gau1&nbsp;$gau2&nbsp;</td><td>".$gniv."&nbsp;</td><td><a href='search-name-infsp.php?nn=$ginfsp&gen=$gg&sp=$gsp'><i>".$ginfsp."</i></a>&nbsp;$gau4&nbsp;$gau3</td>
							</tr>";		
				}
				echo "</tbody></table></td></tr>";

		}
		/////////////////
		if ($listcol==1 && $ncols>0) {
			echo "
				<tr class='tdsmallbold'>
					<td colspan=2><hr></td>
				</tr>
				<tr>
					<td colspan=2>
					<table class='sortable autostripe' align='center'>
					<thead >
						<tr>
							<th align='center'>".GetLangVar('nametaxonomy')."</th>					
							<th align='center'>".GetLangVar('namecoletor')."</th>					
							<th align='center'>".GetLangVar('namenumber')."</th>					
							<th align='center'>".GetLangVar('namedata')."</th>					
							<th align='center'>Lat</th>					
							<th align='center'>Long</th>					
							<th align='center'>Alt</th>					
							<th align='center'>".GetLangVar('namegazetteer')."</th>					
							<th align='center'>".GetLangVar('nameobs')."s</th>					

						</tr>
					</thead>
					<tbody >";
					
					
					//resumecoleta() => $resultado = array('listoftraits' => $listoftraits, 'locality' => $locality, 'habitat' => $habitat, 'latitude' => $latdec, 'longitude' => $longdec, '$altitude' => $altitude);

					
					
					$qq = "SELECT * FROM Especimenes JOIN Identidade USING(DetID) JOIN Tax_Generos ON Identidade.GeneroID=Tax_Generos.GeneroID WHERE Genero='".$nn."' ";
					$rr = mysql_query($qq,$conn);
					while ($gen = mysql_fetch_assoc($rr)) {
						$spid = $gen['EspecimenID'];
						$res = resumecoleta($spid, $conn);
						$nomesearch = strip_tags($res['taxnome']);
						$nomesearch = trim($nomesearch);

						echo "<tr";
						if ($bgi % 2 != 0){ echo ' class=odd ' ;}
						$bgi++;
						echo 	" align='center'><td align='left'><a href='search-name-exec.php?nomesearch=".$nomesearch."'>".$res['taxnome']."</a>&nbsp;</td>
								<td>".$res['coletor']."</td>
								<td>".$res['colnum']."</td>
								<td>".$res['datacol']."</td>
								<td>".$res['latitude']."</td>
								<td>".$res['longitude']."</td>
								<td>".$res['altitude']."</td>
								<td>";
								$url ="showlongtext.php?text=".$res['locality'];		
								echo	"<a href=\"javascript:small_window('$url',400,250,'Ajuda_Termo');\">".$res['local']."...</a>
								</td>								
								<td>";
								$url2 ="showlongtext.php?especimenid=".$gen['EspecimenID'];
								$text = trim($res['listoftraits']);
								if (!empty($text)) {
									echo	"<a href=\"javascript:small_window('$url2',400,250,'Ajuda_Termo');\">notas</a>";
								}
								echo "</td>
								</tr>";		

				}
				echo "</tbody></table></td></tr>";

		}
		//////////
		if ($listplantas==1 && $nplantas>0) {
			echo "
				<tr><td colspan=2 class='clicked'>";
				summarizeplantas($famid,$genusid,$speciesid,$infraspid,$conn);
			echo "</td>
				</tr>
				
				<tr>
					<td colspan=2>
					<table class='sortable autostripe' align='center' cellspacing='0' cellpadding='3' width='100%'>
					<thead >
						<tr>
							<th align='center'>".GetLangVar('nametaxonomy')."</th>					
							<th align='center'>".GetLangVar('nametagnumber')."</th>					
							<th align='center'>".GetLangVar('nametaggedby')."</th>					
							<th align='center'>".GetLangVar('namedata')."</th>					
							<th align='center'>Lat</th>					
							<th align='center'>Long</th>					
							<th align='center'>Alt</th>					
							<th align='center'>".GetLangVar('namegazetteer')."</th>					
							<th align='center'>".GetLangVar('nameobs')."</th>					
						</tr>
					</thead>
					<tbody >";
					$qq = "SELECT * FROM Plantas JOIN Identidade USING(DetID) JOIN Tax_Generos ON Identidade.GeneroID=Tax_Generos.GeneroID WHERE Genero='".$nn."' ";
					$rr = mysql_query($qq,$conn);
					while ($gen = mysql_fetch_assoc($rr)) {
						$spid = $gen['PlantaID'];
						$res = resumoplanta($spid, $conn);
						echo "<tr";
						if ($bgi % 2 != 0){ echo ' class=odd ' ;}
						$bgi++;
						$nomesearch = strip_tags($res['taxnome']);
						$nomesearch = trim($nomesearch);
						echo 	" align='center'><td><a href='search-name-exec.php?nomesearch=$nomesearch'>".$res['taxnome']."</a></td>
								<td>".$res['tagnum']."</td>
								<td><small>".$res['taggedbytxt']."</small></td>
								<td><small>".$res['datacol']."</small></td>
								<td><small>".$res['latitude']."</small></td>
								<td><small>".$res['longitude']."</small></td>
								<td><small>".$res['altitude']."</small></td>
								<td>";
								$url ="showlongtext.php?text=".$res['locality'];		
								echo	"<a href=\"javascript:small_window('$url',400,250,'Ajuda_Termo');\">".$res['local']."...</a>
								</td>								
								<td>";
								$url2 ="showlongtext.php?plantaid=".$gen['PlantaID'];
								$text = trim($res['listoftraits']);
								if (!empty($text)) {
									echo	"<a href=\"javascript:small_window('$url2',400,250,'Ajuda_Termo');\">notas</a>";
								}
								echo "</td>
								</tr>";		

				}
				echo "</tbody></table></td></tr>";

		}
		
		}
		echo "</table>
		";


HTMLtrailers();

?>