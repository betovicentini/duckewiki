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
HTMLheaders('');
		
		//taxonomic summary		
		$qq = "SELECT * FROM Especimenes JOIN Identidade USING(DetID) JOIN Tax_InfraEspecies ON Identidade.InfraEspecieID=Tax_InfraEspecies.InfraEspecieID JOIN Tax_Especies ON Tax_InfraEspecies.EspecieID=Tax_Especies.EspecieID JOIN Tax_Generos ON Tax_Especies.GeneroID=Tax_Generos.GeneroID WHERE Especie='".$sp."' AND Genero='".$gen."' AND InfraEspecie='".$nn."'";
		$rr = mysql_query($qq,$conn);
		$ncols = mysql_numrows($rr); 
		
		$qq = "SELECT * FROM Plantas JOIN Identidade USING(DetID) JOIN Tax_InfraEspecies ON Identidade.InfraEspecieID=Tax_InfraEspecies.InfraEspecieID JOIN Tax_Especies ON Tax_InfraEspecies.EspecieID=Tax_Especies.EspecieID JOIN Tax_Generos ON Tax_Especies.GeneroID=Tax_Generos.GeneroID WHERE Especie='".$sp."' AND Genero='".$gen."' AND InfraEspecie='".$nn."'";
		$rr = mysql_query($qq,$conn);
		$nplantas = mysql_numrows($rr); 
		
		//imagens
		$qq = "(SELECT TraitVariation as imagens FROM Traits_variation JOIN Traits USING (TraitID) JOIN Especimenes  ON Traits_variation.EspecimenID=Especimenes.EspecimenID JOIN Identidade USING(DetID) JOIN Tax_InfraEspecies ON Identidade.InfraEspecieID=Tax_InfraEspecies.InfraEspecieID JOIN Tax_Especies ON Tax_InfraEspecies.EspecieID=Tax_Especies.EspecieID JOIN Tax_Generos ON Tax_Especies.GeneroID=Tax_Generos.GeneroID WHERE Especie='".$sp."' AND Genero='".$gen."' AND InfraEspecie='".$nn."' AND TraitTipo='Variavel|Imagem') UNION (SELECT TraitVariation as imagens FROM Traits_variation JOIN Traits USING (TraitID) JOIN Plantas ON Traits_variation.PlantaID=Plantas.PlantaID JOIN Identidade USING(DetID) JOIN Tax_InfraEspecies ON Identidade.InfraEspecieID=Tax_InfraEspecies.InfraEspecieID JOIN Tax_Especies ON Tax_InfraEspecies.EspecieID=Tax_Especies.EspecieID JOIN Tax_Generos ON Tax_Especies.GeneroID=Tax_Generos.GeneroID WHERE Especie='".$sp."' AND Genero='".$gen."' AND InfraEspecie='".$nn."' AND TraitTipo='Variavel|Imagem') UNION (SELECT TraitVariation as imagens FROM Traits_variation JOIN Traits USING (TraitID) JOIN Tax_InfraEspecies ON Traits_variation.InfraEspecieID=Tax_InfraEspecies.InfraEspecieID JOIN Tax_Especies ON Tax_InfraEspecies.EspecieID=Tax_Especies.EspecieID JOIN Tax_Generos ON Tax_Especies.GeneroID=Tax_Generos.GeneroID WHERE Especie='".$sp."' AND Genero='".$gen."' AND InfraEspecie='".$nn."' AND TraitTipo='Variavel|Imagem')";		
		//echo $qq;
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
		
		$qq = "SELECT InfraEspecieNivel,InfraEspecieID,InfraEspecie,Especie,Tax_Especies.BasionymAutor as EspeBas, EspecieAutor, Tax_InfraEspecies.BasionymAutor,InfraEspecieAutor,Genero,Tax_InfraEspecies.Sinonimos as Sinonimos,Tax_InfraEspecies.Notas,Tax_InfraEspecies.GeoDistribution,Tax_InfraEspecies.PubRevista,Tax_InfraEspecies.PubVolume,Tax_InfraEspecies.PubAno,Familia,Tax_InfraEspecies.Valid,Ordem FROM Tax_InfraEspecies JOIN Tax_Especies USING(EspecieID) JOIN Tax_Generos USING(GeneroID) JOIN Tax_Familias USING(FamiliaID) WHERE Especie='".$sp."' AND Genero='".$gen."' AND InfraEspecie='".$nn."'";
		
		//echo $qq;
		$rr = mysql_query($qq,$conn);
		if ($rr) {
			$row = mysql_fetch_assoc($rr);
			$infspeciesid = $row['InfraEspecieID'];
			$ordem = $row['Ordem'];			
			$fam = $row['Familia'];
			$infspaut1 = $row['BasionymAutor'];
			$infspaut2 = $row['InfraEspecieAutor'];			

			$spaut1 = $row['EspeBas'];
			$spaut2 = $row['EspecieAutor'];

			$famsin = trim($row['Sinonimos']);
			$famnotes = trim($row['Notas']);
			$inflevel = trim($row['InfraEspecieNivel']);
			
			
			$valid = $row['Valid'];
			if ($valid==1) {
				if (!empty($famsin)) {
					$ff = explode(";",$famsin);
					if (count($ff)>1) {
					$sino = '';
					foreach ($ff as $k => $vv) {
						$fid = explode("|",$vv);
						$ffid = $fid[1];
						if ($fid[0]=='especie' || $fid[0]=='infraespecie') {
							$qq = "SELECT Especie as nome FROM Tax_Especies WHERE 	EspecieID='".$ffid."'";
							if ($fid[0]=='infraespecie') {
								$qq = "SELECT InfraEspecie as nome FROM Tax_InfraEspecies WHERE InfraEspecieID='".$ffid."'";
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
					}
					} else {$sino=$famsin;}
					$sino = trim($sino);
				}
			} else {
				$qq = "(SELECT Genero as nome  FROM Tax_Generos WHERE Sinonimos LIKE '%infraespecie|".$infspeciesid.";%' OR Sinonimos LIKE '%infraespecie|".$infspeciesid."') UNION (SELECT concat(Genero,\" \",Especie) as nome  FROM Tax_Especies JOIN Tax_Generos USING(GeneroID) WHERE Tax_Especies.Sinonimos LIKE '%infraespecie|".$infspeciesid.";%' OR Tax_Especies.Sinonimos LIKE '%infraespecie|".$infspeciesid."') UNION (SELECT concat(Genero,\" \",Especie,\" \",InfraEspecie) as nome  FROM Tax_InfraEspecies JOIN Tax_Especies USING(EspecieID) JOIN Tax_Generos USING(GeneroID) WHERE Tax_InfraEspecies.Sinonimos LIKE '%infraespecie|".$infspeciesid.";%' OR Tax_InfraEspecies.Sinonimos LIKE '%infraespecie|".$infspeciesid."')";				
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
		$famlink = "<a href=\"search-name-exec.php?nomesearch=$fam\">".$fam."</a>";
		echo "<br>
		<table class='tableform' align='center'>
		<tr >
			<td  colspan=2 class='tabhead'><i>$gen&nbsp;$sp</i>&nbsp;
			$inflevel&nbsp;<i>$nn<i></td>
		</tr>
		<tr>";
			if ($valid==1) {
				echo "<td width=70%><table align='left' cellpadding='5'>";
			} else {
				echo "<td ><table align='left' cellpadding='5' width='100%'>";			
			}
			$espaut = trim($spaut1." ".$spaut2);
			$infspaut = trim($infspaut1." ".$infspaut2);
			echo "
				<tr>
					<td class='tdsmallbold'>".GetLangVar('nametaxonomy')."</td>
					<td class='tdformnotes'><b><i>$gen&nbsp;$sp</i></b>&nbsp;$espaut&nbsp;&nbsp;
			$inflevel&nbsp;&nbsp;<b><i>$nn<i></b>
			&nbsp;$infspaut <br>($famlink | $orderlink)&nbsp;";
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
				<th class='small'><img src=\"icons/foto-icon.png\" alt=\"".GetLangVar('nameimagens')."\" height=25 border=0>&nbsp;".GetLangVar('nameimagens')."</th>
				<th class='small'><img src=\"icons/specimen-icon.png\" alt=\"".GetLangVar('namecoleta')."\" height=25 border=0>&nbsp;".GetLangVar('namecoleta')."s</th>
				<th class='small'><img src=\"icons/tree-icon.png\" alt=\"".GetLangVar('namecolecaoviva')."\" height=25 border=0>&nbsp;".GetLangVar('namecolecaoviva')."</th>

		</tr>
		</thead>
		<tbody>
		<tr>
			<td align='center'>";
				if ($nimgs>0) {
					$fn = $imagens;
					echo "<a href=\"javascript:small_window('showpicture.php?fn=$fn',700,500,'MostrarImg');\"><b>".
					$nimgs."</b></a>";
				} else { echo $nimgs;}
			
			echo "</td>
			<td align='center'>";
			if ($ncols>0) {
					echo "<a href=\"?gen=$gen&nn=$nn&sp=$sp&listcol=1\">".$ncols."</a>";
				} else { echo $ncols;}
			echo "			
			</td>";
			if ($listplantas==1) { echo "<td align='center' class='clicked'>"; } else { echo "<td align='center'>";}
			if ($nplantas>0) {
				echo "<a href=\"?gen=$gen&nn=$nn&sp=$sp&listplantas=1\">".$nplantas."</a>";
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

					
					
					$qq = "SELECT * FROM Especimenes JOIN Identidade USING(DetID) JOIN Tax_InfraEspecies USING(InfraEspecieID) JOIN Tax_Especies ON Tax_InfraEspecies.EspecieID=Tax_Especies.EspecieID JOIN Tax_Generos ON Tax_Especies.GeneroID=Tax_Generos.GeneroID WHERE Especie='".$sp."' AND Genero='".$gen."' AND InfraEspecie='".$nn."'";
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
					$qq = "SELECT * FROM Plantas JOIN Identidade USING(DetID) JOIN Tax_InfraEspecies USING(InfraEspecieID) JOIN Tax_Especies ON Tax_InfraEspecies.EspecieID=Tax_Especies.EspecieID JOIN Tax_Generos ON Tax_Especies.GeneroID=Tax_Generos.GeneroID WHERE Especie='".$sp."' AND Genero='".$gen."' AND InfraEspecie='".$nn."'";
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