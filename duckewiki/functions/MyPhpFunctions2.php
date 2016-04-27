<?php
function omenudeicons($quais, $vertical=FALSE, $position='right', $iconwidth='35', $iconheight='35' ) { 
if ($vertical) {
	$separador= '<br />';
	$hgt = "width=\"".$iconwidth."\"";
} else {
	$separador= '&nbsp;';
	$hgt = "height=\"".$iconheight."\"";
}
if ($position=='right') {
	$poss = 'float: right;';
} else {
	$poss = 'float: left;';
}
$stilo =" border:1px solid #cccccc;  -webkit-box-shadow:inset 0 0 6px #cccccc; -moz-box-shadow: inset 0 0 6px #cccccc; cursor: pointer;";
if ($_SESSION['userid']>0) {
$toprint = "";
$linkss = array(
'inicio' => "<a href='index.php'><img src=\"icons/blue-home-icon.png\" ".$hgt." style=\"".$stilo."\"  onmouseover=\"Tip('Ir para o Início');\" /></a>",
'especimen' => "<img src=\"icons/specimen-icon.png\" ".$hgt." style=\"".$stilo."\" onclick = \"javascript:small_window('especimenes_dataform.php?ispopup=1&submeteu=nova',1000,600,'Editando/Criando um Especímene');\" onmouseover=\"Tip('Adicionar novo especímene um por um');\" />",
'especimenbatch' => "<img src=\"icons/specimensbatch-icon.png\" ".$hgt." style=\"".$stilo."\" onclick = \"javascript:small_window('batchenter_especimenes_form.php',1000,600,'Adicionar especimenes');\" onmouseover=\"Tip('Adicionar vários especímenes de uma vez');\" />",
'planta' => "<img src=\"icons/tree-icon.png\" ".$hgt." style=\"".$stilo."\" onclick = \"javascript:small_window('plantas_dataform.php?ispopup=1&submeteu=nova',1000,600,'Editando/Criando uma Planta Marcada');\" onmouseover=\"Tip('Adicionar nova planta marcada');\" />",
'taxonomia' => "<img src=\"icons/diversity.png\" ".$hgt." style=\"".$stilo."\" title=\"\"  
onmouseover=\"Tip('Editar ou cadastrar Taxonomia');\" onclick = \"javascript:small_window('taxa-form.php?ispopup=1',800,500,'Taxonomia');\" />",
'local' => "<img src=\"icons/mapping.png\" ".$hgt." style=\"".$stilo."\"  onmouseover=\"Tip('Adicionar/Editar localidades');\" onclick = \"javascript:small_window('localidade_dataform.php?ispopup=1',600,400,'Habitat');\" />",
'habitat' => "<img src=\"icons/environment_icon.png\" ".$hgt." style=\"".$stilo."\" onclick = \"javascript:small_window('habitat-popup.php?ispopup=1&submeteu=nova',600,400,'Habitat');\" onmouseover=\"Tip('Editar ou cadastrar Habitat');\" />",
'pessoas' => "<img src=\"icons/people.png\" ".$hgt." style=\"".$stilo."\" onmouseover=\"Tip('Editar ou cadastrar pessoas');\" onclick = \"javascript:small_window('novapessoa-form.php?ispopup=1',800,500,'Pessoas');\" />",
'formularios' => "<img src=\"icons/formularios.png\" ".$hgt." style=\"".$stilo."\" onmouseover=\"Tip('Editar ou criar um formulário de variáveis');\" onclick = \"javascript:small_window('formularios-form.php?ispopup=1',800,500,'Formulario');\" />",
'variaveis' => "<img src=\"icons/variaveis.png\" ".$hgt." style=\"".$stilo."\" onmouseover=\"Tip('Editar ou criar variáveis');\"  onclick = \"javascript:small_window('traits-form.php?ispopup=1',800,500,'Variáveis');\" />",
'definicoes' => "<img src=\"icons/definicoes.png\" ".$hgt." style=\"".$stilo."\" onmouseover=\"Tip('Definições e Métodos Especiais');\" onclick = \"javascript:small_window('definicoes.php?ispopup=1',400,400,'Definições e Métodos Especiais');\" />",
'ferramentas' => "<img src=\"icons/ferramentas.png\" ".$hgt." style=\"".$stilo."\" onmouseover=\"Tip('Ferramentas');\" onclick = \"javascript:small_window('ferramentas.php?ispopup=1',550,500,'Ferramentas');\" />",
'graficos' => "<img src=\"icons/graphicon.png\" ".$hgt." style=\"".$stilo."\" onmouseover=\"Tip('Gráficos de variáveis');\"  onclick = \"javascript:small_window('graph-variables-form.php?ispopup=1',700,600,'Gráficos de variáveis');\" />",
'buscas' => "<img src=\"icons/search.png\" ".$hgt." style=\"".$stilo."\" onmouseover=\"Tip('Criar um filtro para uma busca específica');\" onclick = \"javascript:small_window('filtros-form.php?ispopup=1',800,500,'Filtro');\" />",
'filtros' => "<img src=\"icons/search_plus_blue.png\" ".$hgt." style=\"".$stilo."\" onmouseover=\"Tip('Ferramentas de Filtro');\" onclick = \"javascript:small_window('filtros-tools.php?ispopup=1',450,400,'Ferramentas de Filtro');\" />",
'exportar' => "<img src=\"icons/download.png\" ".$hgt." style=\"".$stilo."\"   onmouseover=\"Tip('Exportar e baixar dados');\" onclick = \"javascript:small_window('export_menu.php?ispopup=1',400,400,'Exportar e baixar dados');\" />",
'importar' => "<img src=\"icons/import.png\" ".$hgt." style=\"".$stilo."\"  onclick = \"javascript:small_window('importar_menu.php?ispopup=1',500,300,'Importar');\" onmouseover=\"Tip('Importar Dados');\"/>",
'imprimir' => "<img src=\"icons/document-print.png\" ".$hgt." style=\"".$stilo."\" onmouseover=\"Tip('Imprime em PDF');\" onclick = \"javascript:small_window('print_menu.php?ispopup=1',400,400,'Imprime em PDF');\" />",
'admim' => "<img src=\"icons/admin.png\" ".$hgt." style=\"".$stilo."\" onmouseover=\"Tip('Ferramentas Administrativas');\" onclick = \"javascript:small_window('administrative_tools.php?ispopup=1',650,500,'Ferramentas Administrativas');\" />",
'teste' => "<img src=\"icons/workingscript.png\" ".$hgt." style=\"".$stilo."\" onmouseover=\"Tip('Roda o script em StripTeste.php');\" onclick = \"javascript:small_window('ScriptTeste.php?ispopup=0',700,600,'Roda o script em StripTeste.php');\" />",
'logout' => "<img src=\"icons/logout.png\" ".$hgt." style=\"".$stilo."\" onmouseover=\"Tip('Sai do sistema');\" onclick = \"javascript: self.location='logout.php';\" />",
'login' => "<img src=\"icons/login.jpg\" ".$hgt." style=\"".$stilo."\" onmouseover=\"Tip('Autenticar-se');\" onclick = \"javascript: self.location='login-form.php?ispopup=1';\" />",
'editbytable' => "<img src=\"icons/nota-icon.png\" ".$hgt." style=\"".$stilo."\" onmouseover=\"Tip('Edita/Adiciona Dados de Variáveis para Plantas ou Especímenes');\" onclick = \"javascript:small_window('batchenter_traits_form.php?ispopup=0',700,600,'Edita/Adiciona Dados de Variáveis para Plantas ou Especímenes');\" />",
'bibtex' => "<img src=\"icons/livros.png\" ".$hgt." style=\"".$stilo."\" onmouseover=\"Tip('Referências Bibliográficas');\" onclick = \"javascript:small_window('bibtext-gridsave.php?ispopup=0',900,600,'Referências Bibliográficas');\" />",
'fitotable' => "<img src=\"icons/plantfito.png\" ".$hgt." style=\"".$stilo."\" onmouseover=\"Tip('Registro de MORTAS e SILICA PPP');\" onclick = \"javascript:small_window('ppp-plantas-gridsave.php?ispopup=0',900,700,'Registro de MORTAS e SILICA PPP');\" />",
'processos' => "<img src=\"icons/samples.png\" ".$hgt." style=\"".$stilo."\" onmouseover=\"Tip('Processa Amostras Fisicas');\" onclick = \"javascript:small_window('processo-amostras-form.php?ispopup=0',820,600,'Processo de Amostras Físicas');\" />",
'variaveistable' => "<img src=\"icons/categories.png\" ".$hgt." style=\"".$stilo."\" onmouseover=\"Tip('Edita variáveis');\" onclick = \"javascript:small_window('traits_definition_form.php?ispopup=0',1100,700,'Edita variáveis');\" />",
);
//'fitotable' => "<img src=\"icons/plantfito.png\" ".$hgt." style=\"".$stilo."\" onmouseover=\"Tip('Script provisório para REGISTRO FITODEMOGRAFICO');\" onclick = \"javascript:small_window('fitobatchenter_traits_form.php?ispopup=0',700,600,'Script provisório para REGISTRO FITODEMOGRAFICO');\" />",

if ((empty($quais) || count($quais)==0) && $_SESSION['accesslevel']!='visitor') {
	$quais = array_keys($linkss);
}
if (count($quais)>0) {
$toprint .= "<div style=\"".$poss."\">";
foreach ($quais as $vv) {
	$tp = $linkss[$vv];
	$toprint .= $separador.$tp;
}
$toprint .= "</div><br /><br />";
}

} 
else {
$toprint = "
<div style=\"vertical-align:top; position: absolute;  top:0px; right: 0px;  font-size: 0.8em;\">&nbsp;<img src=\"icons/login.jpg\" ".$hgt." style=\"".$stilo."\" onmouseover=\"Tip('Autenticar-se');\" onclick = \"javascript: self.location='login-form.php';\" />&nbsp;&nbsp;<a href='index.php'>&nbsp;<img src=\"icons/blue-home-icon.png\" ".$hgt." style=\"".$stilo."\"  onmouseover=\"Tip('Ir para o Início');\" /></a>
</div><div 
style='padding: 100px; font-family:\"Verdana\", Arial, sans-serif;  font-size: 1.2em; font-color: #800000;'>".$_SESSION['introtext']."</div>";
}
//style='padding-left: 5%; padding-top: 5%; font-size: 1.1em; text-align: left; line-height: 150%;

//if (empty($toprint)) {
	//$toprint = "<br /><br /><br /><br />";
//}
echo $toprint;
}

function formatgaznome($nome) {
		$nome = strtolower($nome);
		$nn  = explode(" ",$nome);
		$res = array();
		$i=0;
		foreach ($nn as $vv) {
				$vv = trim($vv);
				if (!empty($vv)) {
					$nstr = strlen($vv);
					if ($nstr>3 || $i==0) {
							$vv = ucfirst($vv);
					}
					$res[] = $vv;
				$ii++;
				}
		}
		$res = implode(" ",$res);
		return($res);
}

function gettaxatxt($nomeid,$conn) {
	$nn = explode("_",$nomeid);
	$tipo = $nn[0];
	$id = $nn[1];
	if ($tipo=='infspid') {
			$qq = "SELECT CONCAT('<i>',Tax_Generos.Genero,' ',Tax_Especies.Especie,' ',Tax_InfraEspecies.InfraEspecieNivel,' ',Tax_InfraEspecies.InfraEspecie,'</i> [',Tax_Familias.Familia,']') as nn FROM Tax_InfraEspecies JOIN Tax_Especies USING(EspecieID) JOIN Tax_Generos USING(GeneroID) JOIN Tax_Familias USING(FamiliaID) WHERE InfraEspecieID='".$id."'";
		}
	if ($tipo=='speciesid') {
			$qq = "SELECT CONCAT('<i>',Tax_Generos.Genero,' ',Tax_Especies.Especie,'</i> [',Tax_Familias.Familia,']') as nn FROM Tax_Especies JOIN Tax_Generos USING(GeneroID) JOIN Tax_Familias USING(FamiliaID) WHERE Tax_Especies.EspecieID='".$id."'";}
	if ($tipo=='genusid') {
			$qq = "SELECT CONCAT('<i>',Tax_Generos.Genero,'</i> [',Tax_Familias.Familia,']') as nn FROM Tax_Generos JOIN Tax_Familias USING(FamiliaID) WHERE Tax_Generos.GeneroID='".$id."'";}
	if ($tipo=='famid') {
			$qq = "SELECT Familia AS nn FROM Tax_Familias WHERE FamiliaID='".$id."'";
	}
	$res = mysql_query($qq,$conn);
	$row = mysql_fetch_assoc($res);
	$results = $row['nn'];
	return $results;
}

function echopre($array) {
	echo "<pre>";
	print_r($array);
	echo "</pre>";
}

function coordinates($latitude,$longitude,$latgrad,$longgrad,$latminu,$longminu,$latsec,$longsec,$latnors,$longwore) {
	if (empty($latitude) && empty($longitude)) {
		if (empty($latsec)) {$latseg=0;} else {$latseg=$latsec;}
		if (empty($latminu)) {$latmi=0;} else {$latmi=$latminu;}
		if (empty($latgrad)) {$latgg=0;} else {$latgg=str_replace(",", ".", $latgrad);}
		$latdec = abs(((($latseg/60)+$latmi)/60)+$latgg);
		if (empty($latnors) && $latgrad<1) { $latnors='S';} elseif (empty($latnors)) {$latnors='N';}

		if (empty($longwore) && $longgrad<1) { $longwore='W';} elseif (empty($longwore)) {$longwore='E';}

		if ($latnors=='S') {$latdec = $latdec*(-1);}
		if (empty($longsec)) {$longseg=0;} else {$longseg=$longsec;}
		if (empty($longminu)) {$longmi=0;} else {$longmi=$longminu;}
		if (empty($longgrad)) {$longgg=0;} else {$longgg=str_replace(",", ".", $longgrad);}
			$longdec = abs(((($longseg/60)+$longmi)/60)+$longgg);
			if ($longwore=='W') {$longdec = $longdec*(-1);}
		if ($latdec==0) {$latdec='';}
		if ($longdec==0) {$longdec='';}

		$coord = array('latdec'=>$latdec,'longdec'=>$longdec);
		return($coord);
	} else {
		$latt = abs($latitude);
		if ($latitude<0) {$latnors='S';} else {$latnors='N';}
		$latgrad = floor($latt);
		$ll = $latt-$latgrad;
		$ll = $ll*60;
		$latminu = floor($ll);
		$ll = $ll-$latminu;
		$ll = $ll*60;
		$latsec = floor($ll);

		$latt = abs($longitude);
		if ($longitude<0) {$longwore='W';} else {$longwore='E';}
		$longgrad = floor($latt);
		$ll = $latt-$longgrad;
		$ll = $ll*60;
		$longminu = floor($ll);
		$ll = $ll-$longminu;
		$ll = $ll*60;
		$longsec = floor($ll);


		if ($latgrad==0 && $latminu==0 && $latsec==0) {
			$latgrad='';
			$latminu='';
			$latsec='';
			$latnors='';
			}
		if ($longgrad==0 && $longminu==0 && $longsec==0) {
			$longgrad='';
			$longminu='';
			$longsec='';
			$longwore='';
			}

		$coord = array('latgrad'=>$latgrad,'latminu'=>$latminu,'latsec'=>$latsec,'latnors'=>$latnors,'longgrad'=>$longgrad,'longminu'=>$longminu,'longsec'=>$longsec,'longwore'=>$longwore);
		return($coord);
	}
}

function getlocalitymenu($gazetteerID,$conn) {
	$qq = "SELECT * FROM Gazetteer JOIN Municipio USING(MunicipioID) JOIN Province USING(ProvinceID)
	JOIN Country USING(CountryID) WHERE GazetteerID='$gazetteerID'";
	$rr = mysql_query($qq,$conn);
	if ($rr) {
		$row = mysql_fetch_assoc($rr);
		$municipioID = $row['MunicipioID'];
		$provinciaID = $row['ProvinceID'];
		$countryid = $row['CountryID'];

		$row = mysql_fetch_assoc(getpais($countryid,$conn));
		$pais = $row['Country'];

		$row = mysql_fetch_assoc(getprovincia($provinciaID,$countryid,$conn));
		$provincia = $row['Province'];

		$row = mysql_fetch_assoc(getmunicipio($municipioID,$provinciaID,$conn));
		$municipio = $row['Municipio'];

		$row = mysql_fetch_assoc(getgazetteer($gazetteerID,$municipioID,$conn));
		$parentID =	$row['GazetteerID'];
		$i=0;
		$textparent = '';
		$latitude = '';
		while (!empty($parentID) && $parentID>0) {
			$row = mysql_fetch_assoc(getgazetteer($parentID,$municipioID,$conn));
			//$gaz = "<b>".$row['GazetteerTIPOtxt']." ".$row['Gazetteer']."</b>";
			$gaz = "<b>".$row['Gazetteer']."</b>";
			if (empty($textparent)) {
				$textparent = $gaz;
			} else {$textparent = $gaz.". ".$textparent;}
			$parentID = $row['ParentID'];
			$i++;
		}
		$text = "(".strtoupper(substr($pais,0,2))."-".strtoupper(substr($provincia,0,2)).". ".$municipio.") ".$textparent.".";
	} else {
		$text='';
	}
	return $text;
}


function getGPSlocality($gpspointid,$name=FALSE,$conn) {
	$qq = "SELECT * FROM GPS_DATA WHERE PointID='$gpspointid'";
	$rr = mysql_query($qq,$conn);
	if ($rr) {
		$row = mysql_fetch_assoc($rr);
		$gazetteerid = $row['GazetteerID'];
		$latitude = $row['Latitude'];
		$longitude = $row['Longitude'];
		$altitude = $row['Altitude'];
		$datum = $row['GPSMapDatum'];
		$nome = $row['Name'];
		$localidade = getlocality($gazetteerid,$coord=FALSE,$conn);
		if ($name) {
			$text = $localidade." ".$nome;
		}
		//$lat = $latitude,6);
		//$long = round($longitude,6);
		if (!empty($altitude) && !empty($longitude)) {
			$text = $localidade." (Lat: ".$latitude."; Long:".$longitude;
		} else {
			$text = $localidade;
		}
		if (!empty($altitude)) {
			$altitude = round($altitude,0);
			$text = $text."; Alt: ".$altitude." m";
		}
		if (!empty($datum)) {
			$text = $text."; Datum:".$datum;
		}
		$text = $text.").";
	}
	return $text;
}

function getlocalidade($localidadeid,$conn) {
		$locid = explode("_",$localidadeid);
		if ($locid[0]=='gazetteerid') {
			$resultado = getlocality($locid[1],$coord=TRUE,$conn);
		} elseif ($locid[0]=='municipioid') {
			$qq = "SELECT CONCAT(UPPER(Country),'. ',Province,'. ',muni.Municipio,IF(ABS(muni.Longitude)>0,CONCAT('. Lat:',muni.Latitude,' Long:',muni.Longitude),'')) as nome FROM  Municipio as muni JOIN Province USING(ProvinceID) JOIN Country USING(CountryID) WHERE MunicipioID='".$locid[1]."'";
		} elseif ($locid[0]=='provinceid') {
			$qq = "SELECT CONCAT(UPPER(Country),'. ',Province,'. ') as nome FROM Province JOIN Country USING(CountryID) WHERE ProvinceID='".$locid[1]."'";
		} elseif ($locid[0]=='paisid') {
			$qq = "SELECT UPPER(Country) as nome FROM  Country WHERE CountryID='".$locid[1]."'";
		}
		if (isset($qq)) {
			$rr = mysql_query($qq,$conn);
			$row = mysql_fetch_assoc($rr);
			$resultado = $row['nome'];
		}

	return $resultado;
}
function getlocality($gazetteerID,$coord=TRUE,$conn) {
	//$coord=FALSE;
	//GazetteerTIPOtxt,
	$qqq = "SELECT MunicipioID,ProvinceID,CountryID,Country,Province,Municipio,Municipio.Latitude as MuniLat,
		Municipio.Longitude as MuniLong, Gazetteer.Latitude as GazLat,Gazetteer.Longitude as GazLong, Gazetteer.Altitude as GazAlt,
		ParentID,Gazetteer FROM Gazetteer JOIN Municipio USING(MunicipioID) JOIN Province USING(ProvinceID)
	JOIN Country USING(CountryID)";
	$qq = $qqq."  WHERE GazetteerID='$gazetteerID'";
	//echo $qq;
	$rr = mysql_query($qq,$conn);
	if ($rr) {
		$row = mysql_fetch_assoc($rr);
		$municipioID = $row['MunicipioID'];
		$provinciaID = $row['ProvinceID'];
		$countryid = $row['CountryID'];
		$pais = $row['Country'];
		$provincia = $row['Province'];
		$municipio = $row['Municipio'];
		//$tipo = $row['GazetteerTIPOtxt'];
		$parentID =	$row['ParentID'];
		//$gaz = "<b>".$row['GazetteerTIPOtxt']." ".$row['Gazetteer']."</b>";
		$gaz = "<b>".$row['Gazetteer']."</b>";
		$municoor=FALSE;
		if ($coord) {
			if (empty($latitude) && (!empty($row['MuniLat']) || !empty($row['GazLat']))) {
					if (!empty($row['GazLat'])) {
						$latitude= $row['GazLat'];
						$gaz = $gaz." (Lat: ".$latitude; 
					} elseif (!empty($row['MuniLat']) && $parentID==0) {
						$latitude= $row['MuniLat'];
						$municoor=TRUE;

					}
				}
				if (empty($longitude) && (!empty($row['MuniLong']) || !empty($row['GazLong']))) {
					if (!empty($row['GazLong'])) {
						$longitude= $row['GazLong'];
						$gaz = $gaz." Long: ".$longitude;
					} elseif (!empty($row['MuniLong']) && $parentID==0) {
						$longitude= $row['MuniLong'];
						$municoor=TRUE;
					}
				}
				if (empty($altitude) && !empty($row['GazAlt'])) {
					$altitude= $row['GazAlt'];
					$gaz = $gaz.", Altitude ".$altitude." m";
				}
				if (!empty($latitude) && !$municoor) {
					$gaz = $gaz.")";
				}
		}
		$i=0;
		$textparent = '';
		while (!empty($parentID) && $parentID>0) {
			$qq = $qqq." WHERE GazetteerID='$parentID'";
			//echo $qq;
			$res = mysql_query($qq,$conn);
			$rs = mysql_fetch_assoc($res);
			//$gazz = $rs['GazetteerTIPOtxt']." ".$rs['Gazetteer'];
			$gazz = $rs['Gazetteer'];
			$parentID = $rs['ParentID'];
			if ($coord && empty($latitude)) {
				if (empty($latitude) && (!empty($rs['MuniLat']) || !empty($rs['GazLat']))) {
					if (!empty($rs['GazLat'])) {
						$latitude= $rs['GazLat'];
						$gazz = $gazz." (Lat: ".$latitude; 
					} elseif (!empty($rs['MuniLat'])) {
						$latitude= $rs['MuniLat'];
						$municoor=TRUE;
					}
				}
				if (empty($longitude) && (!empty($rs['MuniLong']) || !empty($rs['GazLong']))) {
					if (!empty($rs['GazLong'])) {
						$longitude= $rs['GazLong'];
						$gazz = $gazz." Long: ".$longitude;
					} elseif (!empty($rs['MuniLong'])) {
						$longitude= $rs['MuniLong'];
						$municoor=TRUE;
					}
				}
				if (empty($altitude) && !empty($rs['GazAlt'])) {
					$altitude= $rs['GazAlt'];
					$gazz = $gazz.", Altitude ".$altitude." m";
				}
				if (!empty($latitude) && !$municoor) {
					$gazz = $gazz.")";
				}
			}
			$gazz = trim($gazz);
			$textparent = $gazz.". ".$textparent;
			$i++;
		}
		if (!empty($textparent)) { $tt = $textparent." ".$gaz.".";} else {$tt = $gaz.".";}
		$text = strtupperacentos($pais.". ".$provincia.". ".$municipio);

		if ($municoor && !empty($latitude) && !empty($longitude)) {
				$text = $text." (Lat: ".$latitude."; Long: ".$longitude.")";
		} 
		$text = $text.". ".$tt;
	} else {
		$text='';
	}
	return $text;
}

function getpessoa($pessoaid,$abb=TRUE,$conn){
	if ($abb==TRUE) {
		$ff ='Abreviacao';
	} else { $ff = 'Prenome,Sobrenome';}
	if (empty($pessoaid)) {
		$qq = "SELECT PessoaID,Prenome,Sobrenome,SegundoNome,Abreviacao,Email,Notes,checkiniciais(Prenome,SegundoNome,Sobrenome) as Iniciais FROM Pessoas ORDER BY ".$ff." ASC";
	} else {
		$qq = "SELECT PessoaID,Prenome,Sobrenome,SegundoNome,Abreviacao,Email,Notes,checkiniciais(Prenome,SegundoNome,Sobrenome) as Iniciais FROM Pessoas WHERE PessoaID=".$pessoaid;
	}
	$rr = mysql_query($qq,$conn);
	return $rr;
}


function getuser($usuarioid,$conn){
	$ff = 'FirstName,LastName';
	if (empty($usuarioid)) {
		$qq = "SELECT * FROM Users WHERE AccessLevel!='admin' ORDER BY ".$ff." ASC";
	} else {
		$qq = "SELECT * FROM Users WHERE UserID='$usuarioid'";
	}
	$rr = mysql_query($qq,$conn);
	return $rr;
}

function getpais($countryid,$conn){
	if (empty($countryid)) {
		$qq = "SELECT CountryID,Country FROM Country ORDER BY Country ASC";
	} else {
		$qq = "SELECT CountryID,Country FROM Country WHERE CountryID='$countryid'";
	}
	$rr = mysql_query($qq,$conn);
	return $rr;
}


function getprovincia($provinciaID,$paisID,$conn){
	if (empty($provinciaID)) {
			$qq = "SELECT Province,ProvinceID,Sigla FROM Province WHERE CountryID='$paisID' ORDER BY Province ASC";
	} else {
		$qq = "SELECT Province,ProvinceID,Sigla FROM Province WHERE ProvinceID='$provinciaID'";
	}
	$rr = mysql_query($qq,$conn);
	return $rr;
}

function getmunicipio($municipioID,$provinciaID,$conn){
	if (empty($municipioID)) {
			$qq = "SELECT Municipio,MunicipioID FROM Municipio WHERE ProvinceID='$provinciaID' ORDER BY Municipio ASC";
	} else {
		$qq = "SELECT Municipio,MunicipioID FROM Municipio WHERE MunicipioID='$municipioID'";
	}
	$rr = mysql_query($qq,$conn);
	return $rr;
}

function getgazetteer($gazetteerID,$municipioID,$conn){
	if (empty($gazetteerID) && !empty($municipioID)) {
	//ORDER BY GazetteerTIPOtxt,Gazetteer ASC";
			$qq = "SELECT * FROM Gazetteer WHERE MunicipioID='$municipioID' ORDER BY Gazetteer ASC";
	} elseif (!empty($gazetteerID)) {
		$qq = "SELECT * FROM Gazetteer WHERE GazetteerID='$gazetteerID'";
	} else {
		//ORDER BY GazetteerTIPOtxt,
		$qq = "SELECT * FROM Gazetteer ORDER BY Gazetteer ASC";
	}
	$rr = mysql_query($qq,$conn);
	return $rr;
}

function getvernacular($vernacularid,$conn){
	if (empty($vernacularid)) {
			$qq = "SELECT * FROM Vernacular ORDER BY Vernacular ASC";
	} else {
		$qq = "SELECT * FROM Vernacular WHERE VernacularID='$vernacularid'";
	}
	$rr = mysql_query($qq,$conn);
	return $rr;
}

function UpdateGazetteerPath($gazetteerid,$conn) {
	$filtro = "SELECT * FROM Gazetteer WHERE GazetteerID='".$gazetteerid."'";
	$res = mysql_query($filtro,$conn);
	if ($res) {
			$row = mysql_fetch_assoc($res);
			unset($charpath);
			$gazetteerID = $row['GazetteerID'];
			$gazetteer = $row['Gazetteer'];
			//$gazetteertipo = $row['GazetteerTIPOtxt'];
			$parentID = $row['ParentID'];

			//initial gazetteer name
				//$charpath = trim($gazetteertipo)." ".trim($row['Gazetteer']);
				$charpath = trim($row['Gazetteer']);
			//get name of all parent nodes
				$gg = $parentID;
				$i=1;
				while (!empty($gg)) {
					//,GazetteerTIPOtxt
					$query="SELECT Gazetteer,Gazetteer.ParentID FROM Gazetteer WHERE GazetteerID='$gg'";
					$rr = mysql_query($query,$conn);
					$aa = mysql_fetch_assoc($rr);
					//$tipo = trim($aa['GazetteerTIPOtxt']);
					$nome = trim($aa['Gazetteer']);
					//$charpath=$tipo." ".$nome." ".$charpath;
					$charpath= $nome." ".$charpath;
					$gg = $aa['ParentID'];

					$i++;
			}

			//store the results
			$charpath = trim($charpath);
			if (!empty($charpath)) {
				$forkeyoff = "SET FOREIGN_KEY_CHECKS=0";
					mysql_query($forkeyoff,$conn);
				$update = "UPDATE Gazetteer SET PathName='$charpath',MenuLevel='$i' WHERE GazetteerID='$gazetteerID'";
					mysql_query($update,$conn);
				$forkeyoff = "SET FOREIGN_KEY_CHECKS=1";
					mysql_query($forkeyoff,$conn);
			}
	}

}

function listgazetteerNew($municipioid,$provinciaid,$conn){
	$municipioid = $municipioid+0;
	$provinciaid = $provinciaid+0;

	if ($provinciaid>0) {
		//,Gazetteer.GazetteerTIPOtxt as GazTipo
		$query="SELECT Gazetteer.GazetteerID,Gazetteer.ParentID,Country,Country.CountryID,Province,Municipio,MunicipioID,ProvinceID,Gazetteer.PathName,Gazetteer,Gazetteer.MenuLevel FROM Gazetteer JOIN Municipio USING(MunicipioID) JOIN Province USING(ProvinceID) JOIN Country USING(CountryID)";
		if ($municipioid>0) {
			$query = $query." WHERE MunicipioID='".$municipioid."' ORDER BY PathName,MenuLevel,Gazetteer ASC";
		} else {
			if ($provinciaid>0) {
				$query = $query." WHERE ProvinceID='".$provinciaid."' ORDER BY PathName,MenuLevel,Gazetteer ASC";
			} 
		}
	$nnn = mysql_query($query,$conn);
	return $nnn;
	} else {
		return FALSE;
	}
}

function listgpswaypoinds($municipioid,$provinciaid,$gazetteerid,$countryid,$conn){
	$municipioid = $municipioID+0;
	$provinciaid = $provinciaid+0;
	$gazetteerid = $gazetteerid+0;
	$countryid = $countryid+0;

	//gaz.GazetteerTIPOtxt as GazTipo, 
	$query="SELECT gps.PointID,gps.Name,gps.DateOriginal,Country,Country.CountryID,Province,Municipio,MunicipioID,ProvinceID,gaz.PathName,gaz.Gazetteer,gaz.MenuLevel FROM GPS_DATA as gps JOIN Gazetteer as gaz USING(GazetteerID) JOIN Municipio USING(MunicipioID) JOIN Province USING(ProvinceID) JOIN Country USING(CountryID)";
	if ($gazetteerid>0) {
			$query = $query." WHERE Type='Waypoint' AND GazetteerID='".$gazetteerid."' ORDER BY PathName,MenuLevel,Gazetteer,DateOriginal,Name ASC";
	} else {
		if ($municipioid>0) {
			$query = $query." WHERE Type='Waypoint' AND MunicipioID='".$municipioid."' ORDER BY PathName,MenuLevel,Gazetteer,DateOriginal,Name ASC";
		} else {
			if ($provinciaid>0) {
				$query = $query." WHERE Type='Waypoint' AND ProvinceID='".$provinciaid."' ORDER BY Municipio,PathName,MenuLevel,Gazetteer,DateOriginal,Name ASC";
			} else {
				if ($countryid>0) {
					$query = $query." WHERE Type='Waypoint' AND CountryID='".$countryid."' ORDER BY Province,Municipio,PathName,MenuLevel,Gazetteer,DateOriginal,Name ASC";
				} else {
					$query = $query." WHERE Type='Waypoint' ORDER BY Country,Province,Municipio,PathName,MenuLevel,Gazetteer,DateOriginal,Name ASC";
				}
			}
		}
	}
	$nnn = mysql_query($query,$conn);
	return $nnn;
}

function listgazetteer($municipioID,$provinciaid,$conn){
	//,GazetteerTIPOtxt 
	$filtro = "SELECT GazetteerID,Gazetteer,Gazetteer.ParentID FROM Gazetteer";
	$res = mysql_query($filtro,$conn);
	if ($res) {
		while ($row = mysql_fetch_assoc($res)) {
			unset($charpath);
			$gazetteerID = $row['GazetteerID'];
			$gazetteer = $row['Gazetteer'];
			//$gazetteertipo = $row['GazetteerTIPOtxt'];
			$parentID = $row['ParentID'];
			//echo $gazetteer."\t".trim($gazetteer)."<br>";
			//if (empty($parentID)) {
				//$charpath = trim($gazetteertipo)." ".trim($row['Gazetteer']);
				$charpath = trim($row['Gazetteer']);
			//} else {
				//$charpath = '';
			//}
				$gg = $parentID;
				$i=1;
				while (!empty($gg)) {
					//,GazetteerTIPOtxt
					$query="SELECT Gazetteer,Gazetteer.ParentID FROM Gazetteer WHERE GazetteerID='$gg'";
					$rr = mysql_query($query,$conn);
					$aa = mysql_fetch_assoc($rr);
					//$tipo = trim($aa['GazetteerTIPOtxt']);
					$nome = trim($aa['Gazetteer']);
					//$charpath=$tipo." ".$nome." ".$charpath;
					$charpath = $nome." ".$charpath;
					$gg = $aa['ParentID'];
					$i++;
				}
			$charpath = trim($charpath);
			if (!empty($charpath)) {
				$forkeyoff = "SET FOREIGN_KEY_CHECKS=0";
					mysql_query($forkeyoff,$conn);
				$update = "UPDATE Gazetteer SET PathName='$charpath',MenuLevel='$i' WHERE GazetteerID='$gazetteerID'";
					mysql_query($update,$conn);
				$forkeyoff = "SET FOREIGN_KEY_CHECKS=1";
					mysql_query($forkeyoff,$conn);
			}
		}
	}
	//GazetteerTIPOtxt as GazTipo,
	$query="SELECT Gazetteer.GazetteerID,Gazetteer.ParentID,Country,Province,Municipio,MunicipioID,ProvinceID,Gazetteer.PathName,Gazetteer,Gazetteer.MenuLevel FROM Gazetteer JOIN Municipio USING(MunicipioID) JOIN Province USING(ProvinceID) JOIN Country USING(CountryID)";
	if (!empty($municipioID)) {
		//,GazTipo,
		$query = $query." WHERE MunicipioID='$municipioID' ORDER BY PathName,MenuLevel,Gazetteer ASC";
	} else {
		if (!empty($provinciaid)) {
			//GazTipo,
			$query = $query." WHERE ProvinceID='$provinciaid' ORDER BY PathName,MenuLevel,Gazetteer ASC";
		} else {
			//GazTipo,
			$query = $query." ORDER BY PathName,MenuLevel,Gazetteer ASC";
		}
	}
	//echo $query;
	$nnn = mysql_query($query,$conn);
	return $nnn;
}



//SELECT lixo.GazetteerTIPO,lixo.GazetteerID,lixo.ParentID,lixo.Gazetteer,lixo.Latitude,lixo.Longitude,lixo.Altitude,lixo.AltitudeMin,lixo.AltitudeMax,Gazetteer.GazetteerTIPO as ParentTipo,Gazetteer.Gazetteer as ParentGazetteerFROM lixo JOIN Gazetteer ON lixo.ParentID=Gazetteer.GazetteerID ORDER BY ParentID,GazetteerID
function getfamilies($famid,$conn,$showinvalid=TRUE){
	if (empty($famid)) {
		if ($showinvalid) {
			$qq = "SELECT FamiliaID,Familia,Valid FROM Tax_Familias ORDER BY Familia ASC";
		} else {
			$qq = "SELECT FamiliaID,Familia,Valid FROM Tax_Familias WHERE Valid=1 ORDER BY Familia ASC";
		}
		$resultado = mysql_query($qq,$conn);
		return $resultado;
	} else {
		$qq = "SELECT FamiliaID,Familia,Valid FROM Tax_Familias WHERE FamiliaID='$famid'";
		$rr = mysql_query($qq,$conn);
		$res = @mysql_fetch_assoc($rr);
		$rowediting = $res;
		$valid = $res['Valid'];
		if ($valid!=1) {
			$qq = "SELECT *  FROM Tax_Familias WHERE Sinonimos LIKE '%familia|".$famid.";%' OR `Sinonimos` LIKE '%familia|".$famid."'";
			$rrr = @mysql_query($qq,$conn);
			$nrr = @mysql_numrows($rrr);
			$row = @mysql_fetch_assoc($rrr);
			$ffid = $row['FamiliaID'];
			if (!$nrr>0) { $ffid=$famid;}
		//echo $ffid;
		}  else {$ffid = $famid;}
		$qq = "SELECT FamiliaID,Familia,Valid FROM Tax_Familias WHERE FamiliaID='$ffid'";
		$resul = mysql_query($qq,$conn);
		$row = mysql_fetch_assoc($resul);
		return array($row,$ffid,$rowediting);
	}
}

function getgenera($genusid,$famid,$conn,$showinvalid=TRUE){
	if (empty($genusid)) {
		if ($showinvalid) {
			$qq = "SELECT Genero,GeneroID,Familia,FamiliaID FROM Tax_Generos JOIN Tax_Familias USING(FamiliaID) WHERE FamiliaID='$famid' ORDER BY Genero ASC";
		} else {
			$qq = "SELECT Genero,GeneroID,Familia,FamiliaID FROM Tax_Generos JOIN Tax_Familias USING(FamiliaID) WHERE FamiliaID='$famid' AND Tax_Generos.Valid=1 ORDER BY Genero ASC";
		}
	} else {
		$qq = "SELECT Genero,GeneroID,Familia,FamiliaID FROM Tax_Generos JOIN Tax_Familias USING(FamiliaID) WHERE GeneroID='".$genusid."'";
	}
	$rr = @mysql_query($qq,$conn);
	return $rr;
}

function getspecies($speciesid,$genusid,$conn,$showinvalid=TRUE){
	if (empty($speciesid)) {
		if ($showinvalid) {
			$qq = "SELECT FamiliaID,Familia,GeneroID,Genero,Especie,EspecieID,EspecieAutor FROM Tax_Especies JOIN Tax_Generos USING(GeneroID) JOIN Tax_Familias USING(FamiliaID) WHERE GeneroID='$genusid' ORDER BY Especie ASC";
		} else {
			$qq = "SELECT FamiliaID,Familia,GeneroID,Genero,Especie,EspecieID,EspecieAutor FROM Tax_Especies JOIN Tax_Generos USING(GeneroID) JOIN Tax_Familias USING(FamiliaID) WHERE GeneroID='$genusid' AND Tax_Especies.Valid=1 ORDER BY Especie ASC";
		}
	} else {
		$qq = "SELECT FamiliaID,Familia,GeneroID,Genero,Especie,EspecieID,EspecieAutor FROM Tax_Especies JOIN Tax_Generos USING(GeneroID) JOIN Tax_Familias USING(FamiliaID) WHERE EspecieID='$speciesid'";
	}
	$rr = @mysql_query($qq,$conn);
	return $rr;
}


function getinfraspecies($infraspid,$speciesid,$conn,$showinvalid=TRUE){
	if (empty($infraspid)) {
		if ($showinvalid) {
			$qq = "SELECT FamiliaID,Familia,GeneroID,Genero,EspecieID,Especie,EspecieAutor,InfraEspecie,InfraEspecieID,InfraEspecieNivel,InfraEspecieAutor FROM Tax_InfraEspecies JOIN Tax_Especies USING(EspecieID) JOIN Tax_Generos USING(GeneroID) JOIN Tax_Familias USING(FamiliaID) WHERE EspecieID='$speciesid' ORDER BY InfraEspecie ASC";
		} else {
			$qq = "SELECT FamiliaID,Familia,GeneroID,Genero,EspecieID,Especie,EspecieAutor,InfraEspecie,InfraEspecieID,InfraEspecieNivel,InfraEspecieAutor FROM Tax_InfraEspecies JOIN Tax_Especies USING(EspecieID) JOIN Tax_Generos USING(GeneroID) JOIN Tax_Familias USING(FamiliaID) WHERE EspecieID='$speciesid' AND Tax_InfraEspecies.Valid=1  ORDER BY InfraEspecie ASC";
		}
	} else {
		$qq = "SELECT FamiliaID,Familia,GeneroID,Genero,EspecieID,Especie,EspecieAutor,InfraEspecie,InfraEspecieID,InfraEspecieNivel,InfraEspecieAutor FROM Tax_InfraEspecies JOIN Tax_Especies USING(EspecieID) JOIN Tax_Generos USING(GeneroID) JOIN Tax_Familias USING(FamiliaID) WHERE InfraEspecieID='$infraspid'";
	}
	$rr = mysql_query($qq,$conn);
	return $rr;
}

function getdet($detid,$conn) {
	$qq = "SELECT * FROM Identidade LEFT JOIN Pessoas ON DetbyID=PessoaID WHERE DetID='$detid'";
	//echo $qq;
	$res = mysql_unbuffered_query($qq,$conn);
	$rw = mysql_fetch_assoc($res);
	$detmodifier = $rw['DetModifier'];

	$detdat = explode("-",$rw['DetDate']);
	$mm= $detdat[1]-1;
	$mes = getmonthstring($mm,$abbre=TRUE);
	if ($detdat[2]>0 && $detdat[0]>0) {
		$datadet = $detdat[2]."-".$mes."-".$detdat[0];
	} else {
		$datadet ='';
	}


	$nom = gettaxanamesseparate($rw['InfraEspecieID'],$rw['EspecieID'],$rw['GeneroID'],$rw['FamiliaID'],$conn);

	$family = $nom['FAMILY'];
	$genero = $nom['GENUS'];
	$sp1autor = $nom['AUTHOR1'];
	$sp1 = $nom['SP1'];
	$sp2level = $nom['RANK1'];
	$sp2autor = $nom['AUTHOR2'];
	$sp2 = trim($nom['SP2']);

	if (empty($genero)) { 
		$nome = $family;
	} else {
		$nome = $nome."<i>".$genero."</i>"; 

		if (substr($detmodifier,0,2)=='cf') { $detmodifier='cf.';}
		if (!empty($detmodifier) && empty($sp2) && (substr($detmodifier,0,2)=='cf' || substr($detmodifier,0,3)=='aff')) { 
					$nome = $nome." <i>".$detmodifier."</i> "; 
		}
		if (!empty($sp1)) { $nome = $nome." <i>".$sp1."</i> ".$sp1autor; }
		if (!empty($detmodifier) && empty($sp2) && (substr($detmodifier,0,3)=='s.s' || substr($detmodifier,0,3)=='s.l' || substr($detmodifier,0,3)=='vel')) { 
					$nome = $nome." <i>".$detmodifier."</i> "; 
		}
		if (!empty($detmodifier) && !empty($sp2) && (substr($detmodifier,0,2)=='cf' || substr($detmodifier,0,3)=='aff')) { 
					$nome = $nome." <i>".$detmodifier."</i> "; 
		}
		if (!empty($sp2)) { 
			$nome = $nome."  ".$sp2level." <i>".$sp2."</i> ".$sp2autor; 
		} 
		if (!empty($detmodifier) && !empty($sp2) && (substr($detmodifier,0,3)=='s.s' || substr($detmodifier,0,3)=='s.l' || substr($detmodifier,0,3)=='vel')) { 
					$nome = $nome." <i>".$detmodifier."</i> "; 
		}
		$nome = str_replace("  "," ",$nome);
	}

	if (!empty($rw['Abreviacao'])) {
		if (!empty($datadet)) {
			$determinador = $rw['Abreviacao']." [".$datadet."] ";
		} else {
			$determinador = $rw['Abreviacao'];
		}
		$determinador = trim($determinador);
		$detbyonly = $rw['Abreviacao'];
	} else {$determinador = '';}
	$qq = "SELECT * FROM Tax_Familias WHERE FamiliaID='".$rw['FamiliaID']."'";
	$rs = mysql_unbuffered_query($qq,$conn);
	$row = mysql_fetch_assoc($rs);
	$familia = $row['Familia'];
	return array($nome,$determinador,$familia,$detbyonly);
}

function getdetnoautor($detid,$conn) {
	$qq = "SELECT * FROM Identidade LEFT JOIN Pessoas ON DetbyID=PessoaID WHERE DetID='$detid'";
	//echo $qq;
	$res = mysql_query($qq,$conn);
	$rw = mysql_fetch_assoc($res);
	$nome = getaxanamenoautor($rw['InfraEspecieID'],$rw['EspecieID'],$rw['GeneroID'],$rw['FamiliaID'],$conn);
	mysql_free_result($res);
	return $nome;
}

function getaxanamenoautor($infraspid,$speciesid,$genusid,$famid,$conn) {
				$infraspid = trim($infraspid);
				$speciesid = trim($speciesid);
				$genusid = trim($genusid);
				$famid = trim($famid);
				if (!empty($infraspid)) {
							$rr = getinfraspecies($infraspid,$speciesid,$conn,$showinvalid=TRUE);
							$rw= mysql_fetch_assoc($rr);
							//mysql_free_result($rr);
				} else {
					if (!empty($speciesid)) {
							$rr = getspecies($speciesid,$genusid,$conn,$showinvalid=TRUE);
							$rw= mysql_fetch_assoc($rr);
							//mysql_free_result($rr);
					} else {
						if (!empty($genusid)) {
							$rr = getgenera($genusid,$famid,$conn,$showinvalid=TRUE);
							$rw= mysql_fetch_assoc($rr);
							//mysql_free_result($rr);
						} else {
							$rww = getfamilies($famid,$conn,$showinvalid=TRUE);
							$rw = $rww[0];
							$nrw=1;
							$sofam = True;
						}
					}
				}
				if (empty($nrw)) {
					$nrw = mysql_numrows($rr);
				}
				if ($nrw==1) {
					if ($sofam) { 
						$nome = $rw['Familia']; 
					} 
					if (!empty($rw['Genero'])) { 
						$nome = $rw['Genero']; 
					} 
					if (!empty($rw['Especie'])) { 
						$nome = $nome." ".$rw['Especie'];
					}
					if (!empty($rw['InfraEspecie'])) { 
						$nome = $nome."  ".$rw['InfraEspecieNivel']." ".$rw['InfraEspecie'];
					}
				} else {$nome = "";}
			$nome = trim($nome);
			return($nome);
}

function gettaxaname($infraspid,$speciesid,$genusid,$famid,$conn) {
				$infraspid = trim($infraspid);
				$speciesid = trim($speciesid);
				$genusid = trim($genusid);
				$famid = trim($famid);
				if (!empty($infraspid)) {
							$rr = getinfraspecies($infraspid,$speciesid,$conn,$showinvalid=TRUE);
							$rw= mysql_fetch_assoc($rr);
				} else {
					if (!empty($speciesid)) {
							$rr = getspecies($speciesid,$genusid,$conn,$showinvalid=TRUE);
							$rw= mysql_fetch_assoc($rr);
					} else {
						if (!empty($genusid)) {
							$rr = getgenera($genusid,$famid,$conn,$showinvalid=TRUE);
							$rw= mysql_fetch_assoc($rr);
						} else {
							$rww = getfamilies($famid,$conn,$showinvalid=TRUE);
							$rw = $rww[0];
							$nrw=1;
							//$rw= mysql_fetch_assoc($rr);
							$sofam = True;
						}
					}
				}
				if (empty($nrw)) {
					$nrw = mysql_numrows($rr);
				}
				if ($nrw==1) {
					$nome = strtoupper($rw['Familia']);
					if ($sofam) { $nome = $rw['Familia']; } 
					if (!empty($rw['Genero'])) { 
						$nome = $nome." <i>".$rw['Genero']."</i>"; 
					} 
					if (!empty($rw['Especie'])) { $nome = $nome." <i>".$rw['Especie']."</i> ".$rw['EspecieAutor']; }
					if (!empty($rw['InfraEspecie'])) { $nome = $nome."  ".$rw['InfraEspecieNivel']." <i>".$rw['InfraEspecie']."</i> ".$rw['InfraEspecieAutor']; } 
				} else {$nome = "";}
			$nome = trim($nome);
			return($nome);
}

function gettaxanamesseparate($infraspid,$speciesid,$genusid,$famid,$conn) {
				$infraspid = trim($infraspid);
				$speciesid = trim($speciesid);
				$genusid = trim($genusid);
				$famid = trim($famid);
				if (!empty($infraspid)) {
							$rr = getinfraspecies($infraspid,$speciesid,$conn,$showinvalid=TRUE);
							$rw= mysql_fetch_assoc($rr);
				} else {
					if (!empty($speciesid)) {
							$rr = getspecies($speciesid,$genusid,$conn,$showinvalid=TRUE);
							$rw= mysql_fetch_assoc($rr);
					} else {
						if (!empty($genusid)) {
							$rr = getgenera($genusid,$famid,$conn,$showinvalid=TRUE);
							$rw= mysql_fetch_assoc($rr);
						} else {
							$rww = getfamilies($famid,$conn,$showinvalid=TRUE);
							$rw = $rww[0];
							$nrw='1';
							//$rw= mysql_fetch_assoc($rr);
							$sofam = True;
						}
					}
				}
				if ($nrw!='1') {
					$nrw = mysql_numrows($rr);
				}
				if ($nrw==1) {
					$family = $rw['Familia']; 
					if (!empty($rw['Genero'])) { 
						$genero = $rw['Genero']; 
					} 
					if (!empty($rw['Especie'])) { 
						$sp1 = $rw['Especie'];
						$sp1autor = $rw['EspecieAutor']; 
					}
					if (!empty($rw['InfraEspecie'])) { 
						$sp2level = $rw['InfraEspecieNivel'];
						$sp2 = $rw['InfraEspecie'];
						$sp2autor = $rw['InfraEspecieAutor']; 
					}
					 $result = array(
					 	'FAMILY' => $family,
						 'GENUS'=> $genero ,
						 'SP1'=> $sp1,
						 'AUTHOR1'=> $sp1autor,
						 'RANK1'=> $sp2level,
						 'SP2'=> $sp2,
						 'AUTHOR2'=> $sp2autor);
				} else {
					 $result = false;
				}

			return($result);
}

function getdetINPAfields($detid,$detpor=true,$conn) {
	$qq = "SELECT * FROM Identidade LEFT JOIN Pessoas ON DetbyID=PessoaID WHERE DetID='$detid'";
	$res = mysql_unbuffered_query($qq,$conn);
	$rw = mysql_fetch_assoc($res);
	$nomes = gettaxanamesseparate($rw['InfraEspecieID'],$rw['EspecieID'],$rw['GeneroID'],$rw['FamiliaID'],$conn);
	if (is_array($nomes)) {
		if ($detpor) {
			if (!empty($rw['DetModifier'])) {
				$detmodifier = $rw['DetModifier'];
			} 
			if (!empty($rw['Abreviacao'])) {
				$determinador = trim($rw['Abreviacao']);
				$detdate = $rw['DetDate'];
				$dd = explode("-",$detdate);
				$detdd = $dd[2];
				$detmm = $dd[1];
				$detyy = $dd[0];
			} 
			if ($detmodifier=='cf' || $detmodifier=='aff.'  || $detmodifier=='cf.') {
				$cf = $detmodifier;
	 		} elseif (!empty($detmodifier)) {
	 			if (!empty($nomes['sp2'])) { 
	 				$nomes['sp2'] = $nomes['sp2']." ".$detmodifier; 
	 			} elseif (!empty($nomes['sp1'])) {
	 				 $nomes['sp1'] = $nomes['sp1']." ".$detmodifier;
	 			}
	 		}
	 		$nresul = array(
	 				'CF' => $cf,
	 				'DETBY' => $determinador,
	 				'DETDD' => $detdd,
	 				'DETMM' => $detmm,
	 				'DETYY' => $detyy);
			$result = array_merge((array)$nomes,(array)$nresul);
		} else { $result = $nomes;}
		return $result;
	} else {
		return false;
	}
}

function getGPSlocalityFields($gpspointid,$name=FALSE,$conn) {
	$qq = "SELECT * FROM GPS_DATA WHERE PointID='$gpspointid'";
	$rr = mysql_unbuffered_query($qq,$conn);
	if ($rr) {
		$row = mysql_fetch_assoc($rr);
		$gazetteerid = $row['GazetteerID'];
		$latitude = $row['Latitude']+0;
		$longitude = $row['Longitude']+0;
		$altitude = $row['Altitude'];
		$datum = $row['GPSMapDatum'];
		$nome = $row['Name'];
		$resultado = getlocalityFields($gazetteerid,$coord=FALSE,$conn);
		if ($name && !empty($resultado['GAZETTEER'])) {
			$ll = $resultado['GAZETTEER'];
			$text = $ll.". GPS Waypoint ".$nome.".";
		}
		//$lat = round($latitude,6);
		//$long = round($longitude,6);
		if ($latitude<0) { $latnors = 'S';} elseif($latitude>0) { $latnors ='N';}
		if ($longitude<0) { $longwore = 'W';} elseif($longitude>0) { $longwore ='E';}
		if (!empty($altitude)) {
			$altitude = round($altitude,0);
		}
		$resultado['LATITUDE'] = $latitude;
		$resultado['LONGITUDE'] = $longitude;
		$resultado['ALTITUDE'] = $altitude;
		$resultado['COORD_PRECISION'] = 'GPS';
		$rrs = array('NS' => $latnors ,'EW' => $longwore , 'DATUM' => $datum);
		$resultado = array_merge((array)$resultado,(array)$rrs);
		return $resultado;
	}
	return false;
}
function getlocalityFields($gazetteerID,$coord=TRUE,$conn) {
	//$coord=FALSE;
	//GazetteerTIPOtxt,
	$qqq = "SELECT MunicipioID,ProvinceID,CountryID,Country,Province,Municipio,Municipio.Latitude as MuniLat, Municipio.Longitude as MuniLong, Gazetteer.Latitude as GazLat,Gazetteer.Longitude as GazLong, Gazetteer.Altitude as GazAlt, ParentID,Gazetteer FROM Gazetteer JOIN Municipio USING(MunicipioID) JOIN Province USING(ProvinceID)
	JOIN Country USING(CountryID)";
	$qq = $qqq."  WHERE GazetteerID='$gazetteerID'";
	//echo $qq;
	$rr = mysql_unbuffered_query($qq,$conn);
	if ($rr) {
		$row = mysql_fetch_assoc($rr);
		$municipioID = $row['MunicipioID'];
		$provinciaID = $row['ProvinceID'];
		$countryid = $row['CountryID'];
		$pais = $row['Country'];
		$provincia = $row['Province'];
		$municipio = $row['Municipio'];
		//$tipo = $row['GazetteerTIPOtxt'];
		$parentID = $row['ParentID'];
		//$gaz = $row['GazetteerTIPOtxt']." ".$row['Gazetteer'];
		$gaz = $row['Gazetteer'];
		$justgaz = $gaz;

		$resultado = array('COUNTRY' => $pais, 'MAJORAREA' => $provincia, 'MINORAREA' => $municipio);
		$municoor=FALSE;
		if ($coord) {
			if (empty($latitude) && (!empty($row['MuniLat']) || !empty($row['GazLat']))) {
					if (!empty($row['GazLat'])) {
						$latitude= $row['GazLat'];
						//$gaz = $gaz." (Lat: ".$latitude;
						$coorref='Gazetteer - '.$justgaz;
					} elseif (!empty($row['MuniLat']) && $parentID==0) {
						$latitude= $row['MuniLat'];
						$municoor=TRUE;
						$coorref='MinorArea';
					}
				}
				if (empty($longitude) && (!empty($row['MuniLong']) || !empty($row['GazLong']))) {
					if (!empty($row['GazLong'])) {
						$longitude= $row['GazLong'];
						//$gaz = $gaz." Long: ".$longitude;
						$coorref='Gazetteer - '.$justgaz;

					} elseif (!empty($row['MuniLong']) && $parentID==0) {
						$longitude= $row['MuniLong'];
						$municoor=TRUE;
						$coorref='MinorArea';
					}
				}
				if (empty($altitude) && !empty($row['GazAlt'])) {
					$altitude= $row['GazAlt'];
					//$gaz = $gaz.", Altitude ".$altitude." m";
				}
				if (!empty($latitude) && !$municoor) {
					//$gaz = $gaz.")";
				}
		}
		$textparent = '';
		while (!empty($parentID) && $parentID>0) {
			$qq = $qqq." WHERE GazetteerID='$parentID'";
			$res = mysql_unbuffered_query($qq,$conn);
			$rs = mysql_fetch_assoc($res);
			//$gazz = $rs['GazetteerTIPOtxt']." ".$rs['Gazetteer'];
			$gazz = $rs['Gazetteer'];
			//$jsg =  $rs['GazetteerTIPOtxt']." ".$rs['Gazetteer'];
			$jsg = $rs['Gazetteer'];
			$parentID = $rs['ParentID'];
			//$municoor=FALSE;
			if ($coord && empty($latitude)) {
				if (empty($latitude) && (!empty($rs['MuniLat']) || !empty($rs['GazLat']))) {
					if (!empty($rs['GazLat'])) {
						$latitude= $rs['GazLat'];
						$coorref = 'Gazetteer - '.$jsg;
					} elseif (!empty($rs['MuniLat'])) {
						$latitude= $rs['MuniLat'];
						$municoor=TRUE;
						$coorref = 'MinorArea';
					}
				}
				if (empty($longitude) && (!empty($rs['MuniLong']) || !empty($rs['GazLong']))) {
					if (!empty($rs['GazLong'])) {
						$longitude= $rs['GazLong'];
						$coorref = 'Gazetteer - '.$jsg;
					} elseif (!empty($rs['MuniLong'])) {
						$longitude= $rs['MuniLong'];
						$municoor=TRUE;
						$coorref = 'MinorArea';
					}
				}
				if (empty($altitude) && !empty($rs['GazAlt'])) {
					$altitude= $rs['GazAlt'];
				} 
				//if ($municoor) { $coorref = 'MinorArea';} else { $coorref = 'Gazetteer';}
			}
			$textparent = $gazz.". ".$texparent;
		} //end while
		if (!empty($textparent)) { $tt = $textparent." ".$gaz;} else {$tt = $gaz;}
		if ($coord) {
			$rrs = array('LATITUDE' => $latitude, 'LONGITUDE' => $longitude, 'ALTITUDE' => $altitude, 'COORD_PRECISION' => $coorref);
			$resultado = array_merge((array)$resultado,(array)$rrs);
		}
		$text = $tt;
		$rrs = array('GAZETTEER' => $tt);
		$resultado = array_merge((array)$resultado,(array)$rrs);
		return $resultado;
	} else {
		return false;
	}
}
//insert new record in table and return Id value ($idcolname) if insert is sucessful else return False
function InsertIntoTable($fieldsaskeyofvaluearray,$idcolname,$table,$conn) {

	if (count($fieldsaskeyofvaluearray)>0) {
	$dbname = $_SESSION['dbname'];
	$userid = $_SESSION['userid'];
	$sessiondate = $_SESSION['sessiondate'];


	$forkeyoff = "SET FOREIGN_KEY_CHECKS=0";
		mysql_unbuffered_query($forkeyoff,$conn);

	$qqq = "INSERT INTO $table (";
	foreach($fieldsaskeyofvaluearray as $key => $val) {
		$qqq = $qqq." ".$key.",";
	}
	$qqq = $qqq." AddedBy, AddedDate) VALUES (";
	foreach($fieldsaskeyofvaluearray as $key => $val) {
		$qqq = $qqq." '".$val."',";
	}
	$qqq = $qqq." '$userid', '$sessiondate')";
	//echo "<br>here $qqq<br>";
	$res = mysql_unbuffered_query($qqq,$conn);

	$forkeyonn = "SET FOREIGN_KEY_CHECKS=1";
		mysql_unbuffered_query($forkeyoff,$conn);
	if ($res) {
		$qqq = "SELECT $idcolname FROM $table ORDER BY $idcolname DESC LIMIT 1";
		$rr = mysql_unbuffered_query($qqq,$conn);
		$row = mysql_fetch_assoc($rr);
		$idvalue = $row[$idcolname];
		//mysql_free_result($res);
		return $idvalue;
	} else {
		//mysql_free_result($res);
		return FALSE;
	}
	} 
}

function listhabitatnew($conn){
//,GazetteerTIPOtxt as GazTipo 
	$query="SELECT Habitat.PathName,HabitatID,HabitatTipo,Habitat,Habitat.MenuLevel,Gazetteer,GazetteerID,Gazetteer.PathName as GazPath FROM Habitat LEFT JOIN Gazetteer ON Habitat.LocalityID=GazetteerID 
ORDER BY Habitat.PathName,GazPath,Gazetteer,Habitat ASC";
//GazTipo,
	$nnn = mysql_query($query,$conn);
	return $nnn;
}

function listhabitat($conn){
	$fhab = "SELECT * FROM Habitat";
	$res = mysql_query($fhab,$conn) or die(mysql_error());
	if ($res) {
		while ($row = mysql_fetch_assoc($res)) {
			unset($charpath);
			$HabitatID = $row['HabitatID'];
			$Habitat = $row['Habitat'];
			$parentID = $row['ParentID'];
			$tipo = $row['HabitatTipo'];
			if ($tipo=='Class') {
				$charpath = $row['Habitat'];
			} else {
				$charpath = "";
			}
			$gg = $parentID;
			$i=1;
			while (!empty($gg)) {
				$query="SELECT * FROM Habitat WHERE HabitatID='$gg'";
				$rr = mysql_query($query,$conn);
				$aa = mysql_fetch_assoc($rr);
				$nome = $aa['Habitat'];
				$ttt = $aa['HabitatTipo'];
				$charpath = $nome.". ".$charpath;
				$gg = $aa['ParentID'];
				$i++;
				//mysql_free_result($rr);
			}
			if (!empty($charpath)) {
				$charpath = trim($charpath);
				$forkeyoff = "SET FOREIGN_KEY_CHECKS=0";
					mysql_query($forkeyoff,$conn);
				$update = "UPDATE Habitat SET PathName='$charpath',MenuLevel='$i' WHERE HabitatID='$HabitatID'";
					mysql_query($update,$conn);
				$forkeyoff = "SET FOREIGN_KEY_CHECKS=1";
					mysql_query($forkeyoff,$conn);
			}
			/////

			///////
		}
	}
	//mysql_free_result($res);
	//,GazetteerTIPOtxt as GazTipo
	$query="SELECT Habitat.PathName,HabitatID,HabitatTipo,Habitat,Habitat.MenuLevel,Gazetteer,GazetteerID,Gazetteer.PathName as GazPath FROM Habitat LEFT JOIN Gazetteer ON Habitat.LocalityID=GazetteerID 
ORDER BY Habitat.PathName,GazPath,Gazetteer,Habitat ASC";
//GazTipo,
	$nnn = mysql_query($query,$conn);
	return $nnn;
}


function updatehabitatpath($habitatid,$conn){
	$fhab = "SELECT * FROM Habitat WHERE HabitatID='$habitatid'";
	$res = mysql_query($fhab,$conn) or die(mysql_error());
	if ($res) {
		while ($row = mysql_fetch_assoc($res)) {
			unset($charpath);
			$HabitatID = $row['HabitatID'];
			$Habitat = $row['Habitat'];
			$parentID = $row['ParentID'];
			$tipo = $row['HabitatTipo'];
			if ($tipo=='Class') {
				$charpath = $row['Habitat'];
			} else {
				$charpath = "";
			}
			$gg = $parentID;
			$i=1;
			while (!empty($gg)) {
				$query="SELECT * FROM Habitat WHERE HabitatID='$gg'";
				$rr = mysql_query($query,$conn);
				$aa = mysql_fetch_assoc($rr);
				$nome = $aa['Habitat'];
				$ttt = $aa['HabitatTipo'];
				$charpath = $nome.". ".$charpath;
				$gg = $aa['ParentID'];
				$i++;
				//mysql_free_result($rr);
			}
			if (!empty($charpath)) {
				$charpath = trim($charpath);
				$forkeyoff = "SET FOREIGN_KEY_CHECKS=0";
					mysql_query($forkeyoff,$conn);
				$update = "UPDATE Habitat SET PathName='$charpath',MenuLevel='$i' WHERE HabitatID='$HabitatID'";
					mysql_query($update,$conn);
				$forkeyoff = "SET FOREIGN_KEY_CHECKS=1";
					mysql_query($forkeyoff,$conn);
			}
		}
	}
}

function listtraits($filtro,$conn){
	$ltri = mysql_query("SELECT * FROM Traits",$conn);
	if ($ltri) {
		while ($ltt = mysql_fetch_assoc($ltri)) {
			unset($charpath);
			$TraitID = $ltt['TraitID'];
			$parentID = $ltt['ParentID'];
			$charpath = $ltt['TraitName'];
			$gg = $parentID;
			$i=1;
			while (!empty($gg)) {
				$query="SELECT * FROM Traits WHERE TraitID='$gg'";
				$rr = mysql_unbuffered_query($query,$conn);
				$aa = mysql_fetch_assoc($rr);
				$nome = $aa['TraitName'];
				$gg = $aa['ParentID'];
				$charpath= $nome." - ".$charpath;
				$i++;
				//mysql_free_result($rr);
			} 
			if (!empty($charpath)) {
				$forkeyoff = "SET FOREIGN_KEY_CHECKS=0";
					mysql_unbuffered_query($forkeyoff,$conn);
				$update = "UPDATE Traits SET PathName='$charpath',MenuLevel='$i' WHERE TraitID='$TraitID'";
					mysql_unbuffered_query($update,$conn);
				$forkeyoff = "SET FOREIGN_KEY_CHECKS=1";
					mysql_unbuffered_query($forkeyoff,$conn);
			}
		}
	}
	//mysql_free_result($res);
	if (!empty($filtro)) { 
		$query= $filtro." ORDER BY PathName ASC";
		$nnn = mysql_query($query,$conn);
		return $nnn;
	}
}

function listtraitsengl($filtro,$conn){
	$forkeyoff = "SET FOREIGN_KEY_CHECKS=0";
	@mysql_unbuffered_query($forkeyoff,$conn);
	
	$update = "ALTER TABLE `Traits` CHANGE `TraitDefinicao` `TraitDefinicao` VARCHAR(1000) CHARACTER SET utf8 COLLATE utf8_general_ci NULL DEFAULT NULL";
	@mysql_unbuffered_query($update,$conn);
	$update = "ALTER TABLE `Traits` CHANGE `TraitDefinicao_English` `TraitDefinicao_English` VARCHAR( 1000 ) CHARACTER SET utf8 COLLATE utf8_general_ci NOT NULL";
	@mysql_unbuffered_query($update,$conn);
	$update = "ALTER TABLE Traits ADD COLUMN PathName_English VARCHAR(500), ADD COLUMN MenuLevel_English INT(10)";
	@mysql_unbuffered_query($update,$conn);
	//echo $update;
	$forkeyoff = "SET FOREIGN_KEY_CHECKS=1";
	mysql_unbuffered_query($forkeyoff,$conn);
	$ltri = mysql_query("SELECT * FROM Traits",$conn);
	if ($ltri) {
		while ($ltt = mysql_fetch_assoc($ltri)) {
			unset($charpath);
			$TraitID = $ltt['TraitID'];
			$parentID = $ltt['ParentID'];
			$charpath = $ltt['TraitName_English'];
			$gg = $parentID;
			$i=1;
			while (!empty($gg)) {
				$query="SELECT * FROM Traits WHERE TraitID='$gg'";
				$rr = mysql_unbuffered_query($query,$conn);
				$aa = mysql_fetch_assoc($rr);
				$nome = $aa['TraitName_English'];
				$gg = $aa['ParentID'];
				$charpath= $nome." - ".$charpath;
				$i++;
				//mysql_free_result($rr);
			} 
			if (!empty($charpath)) {
				$forkeyoff = "SET FOREIGN_KEY_CHECKS=0";
					mysql_unbuffered_query($forkeyoff,$conn);
				$update = "UPDATE Traits SET PathName_English='$charpath',MenuLevel_English='$i' WHERE TraitID='$TraitID'";
					mysql_unbuffered_query($update,$conn);
				$forkeyoff = "SET FOREIGN_KEY_CHECKS=1";
					mysql_unbuffered_query($forkeyoff,$conn);
			}
		}
	}
	//mysql_free_result($res);
	if (!empty($filtro)) { 
		$query= $filtro." ORDER BY PathName_English ASC";
		$nnn = mysql_query($query,$conn);
		return $nnn;
	}
}

function updatesingletraitpath($traitid,$conn){
	$ltri = mysql_query("SELECT * FROM Traits WHERE TraitID='".$traitid."'",$conn);
	if ($ltri) {
			$ltt = mysql_fetch_assoc($ltri);
			unset($charpath);
			$parentID = $ltt['ParentID'];
			$charpath = $ltt['TraitName'];
			$charpath_eng = $ltt['TraitName_English'];

			$gg = $parentID;
			$i=1;
			while (!empty($gg)) {
				$query="SELECT * FROM Traits WHERE TraitID='$gg'";
				$rr = mysql_unbuffered_query($query,$conn);
				$aa = mysql_fetch_assoc($rr);
				$nome = $aa['TraitName'];
				$nome_eng = $aa['TraitName_English'];
				$gg = $aa['ParentID'];
				$charpath= $nome." - ".$charpath;
				$charpath_eng = $nome_eng." - ".$charpath_eng;

				$i++;
			} 
			if (!empty($charpath)) {
				$forkeyoff = "SET FOREIGN_KEY_CHECKS=0";
					mysql_unbuffered_query($forkeyoff,$conn);
				$update = "UPDATE Traits SET PathName='$charpath',MenuLevel='$i',PathName_English='$charpath' WHERE TraitID='".$traitid."'";
				$upd = mysql_query($update,$conn);
				$forkeyoff = "SET FOREIGN_KEY_CHECKS=1";
					mysql_unbuffered_query($forkeyoff,$conn);
			}

	}
	//mysql_free_result($res);
	if ($upd) { 
		return TRUE;
	} else {
		return FALSE;
	}
}


function describetraits_keyorder($traitsarray,$img=FALSE,$conn) {
$varname = '';
foreach ($traitsarray as $key => $value) {
	$ttid = explode("_",$key);
	$traitid = trim($ttid[1]);
	$ttype = trim($ttid[0]);
	$variation = trim($value);

	if (!empty($variation) && $variation!='none' && $ttype!='traitunit') {
		$qq = "SELECT * FROM Traits WHERE TraitID='$traitid'";
		$resul = mysql_query($qq,$conn);
		$row = mysql_fetch_assoc($resul);
		$traittipo = $row['TraitTipo'];
		$traitname = $row['TraitName'];
		$variation = trim($variation);

	if (($ttype=='traitvar' || $ttype=='trait') && !empty($traittipo)) {

		if ($traittipo!='Variavel|Imagem' || ($traittipo!='Variavel|Imagem' && $img==TRUE)) {
			if (empty($varname)) {
				$varname = $row['TraitName']." (";
			} else {
				$varname = $varname."; ".$row['TraitName']." (";
			}
		} 

		if ($traittipo=='Variavel|Categoria') {
			$aarvar = explode(";",$variation);
			$nvar = count($aarvar);
			$i =1;
			foreach ($aarvar as $kk => $val) {
				$qq = "SELECT * FROM Traits WHERE TraitID='$val'";
				$rr = mysql_query($qq,$conn);
				$rw = mysql_fetch_assoc($rr);
				$varname = $varname.strtolower($rw['TraitName']);
				if ($i<$nvar) { $varname = $varname.", ";}
				if ($i==$nvar) { $varname = $varname;}
				$i++;
				mysql_free_result($rr);
				unset($rw);
			}
		}
		if ($traittipo=='Variavel|Quantitativo') {
			$varunit = $traitsarray['traitunit_'.$traitid];
			$aarvar = explode(";",$variation);
			$nv = count($aarvar);
			if ($nv>1) {
				$mean = @round(Numerical::mean($aarvar),1);
				$stdev = @round(Numerical::standardDeviation($aarvar),1);
				$maxvar = max($aarvar);
				$minvar = min($aarvar);
				if ($varunit!=GetLangVar('namenumero')) {
					$varname = trim($varname).$mean."+/-".$stdev." [".$minvar."-".$maxvar."] ".strtolower($varunit);
				} else {
					$varname = trim($varname).$minvar."-".$mean."-".$maxvar." ".strtolower($varunit);
				}
			} elseif ($nv==1) {
				$varname = trim($varname).$variation." ".$varunit;
			}
		}
		if ($traittipo=='Variavel|Texto') {
			$varname = $varname.trim($variation);
		}

		if ($traittipo=='Variavel|Imagem') {
			$string = 'trait_'.$traitid;
			$variation = $traitsarray[$string];
			if ($variation!='imagem') {
				$aarvar = explode(";",$variation );
				$nv = count($aarvar);
				if ($nv>=1) {
					$fn = $variation;
					if ($img) {
						$imgname = "<img src='icons/ico_open.gif' onclick = \"javascript:small_window('showpicture.php?fn=$fn',700,500,'MostrarImg');\">";
						$imgname = $imgname."&nbsp;<b>$nv</b>&nbsp;imgs";
						$varname = $varname.$imgname;
					}
				}
			}
		}
			$varname = trim($varname).")";
		} //end if has a $value

		mysql_free_result($resul);
	} //end if variation

	} //end for each

	if (!empty($varname)) {
		$varname = trim($varname).".";
	}
	unset($row);
	return $varname;
}

//checa se o novo nome esta presente num nome antigo
function TraitNameCheck($traitname,$traittipo,$strict,$conn,$charid,$parentid) { 
	$charwords = explode(" ",$traitname);
	$nwords = count($charwords);
	$prob = 0;
	$result = array();
	for ($i = 0; $i < $nwords; $i++) {
		$word = trim($charwords[$i]);
		if (!empty($word) && strlen($word)>=3) { //ignora palavras curtas
				if ($strict) {
					$query="SELECT * FROM Traits WHERE TraitName='$word' AND TraitTipo='$traittipo'";
				} else {
					$query="SELECT * FROM Traits WHERE TraitName LIKE '$word' AND TraitTipo='$traittipo'";
				}
				if (!empty($charid)) { $query = $query." AND TraitID!='$charid'";}
				if (!empty($parentid)) { $query = $query." AND ParentID=='$parentid'";}
				$rrr = @mysql_query($query,$conn);
				$nr= @mysql_num_rows($rrr);
				if ($nr>0) {
					$prob++;
					$row = mysql_fetch_assoc($rrr);
					$zz = array($row['TraitID'] => $row['TraitTipo']." ".$row['TraitName']); 
					$result = array_merge((array)$result,(array)$zz);
				} 
		}
	}
	return $result;
}

function UpdateTable($id,$fieldsaskeyofvaluearray,$idcolname,$table,$conn) {
	$userid = $_SESSION['userid'];
	$sessiondate = $_SESSION['sessiondate'];
	$forkeyoff = "SET FOREIGN_KEY_CHECKS=0";
		mysql_query($forkeyoff,$conn);
	$qq = "SET NAMES utf8";
	mysql_query($qq,$conn);
	$qqq = "UPDATE $table SET";
		foreach($fieldsaskeyofvaluearray as $key => $val) {
			$qqq = $qqq." ".$key."= '".$val."', ";
		}
		$qqq = $qqq." AddedBy='$userid', AddedDate='$sessiondate' WHERE $idcolname='$id'";
	$res = mysql_query($qqq,$conn);
	//echo $qqq."<br><br>";
	$forkeyonn = "SET FOREIGN_KEY_CHECKS=1";
		mysql_query($forkeyoff,$conn);
	return $res;
}


function CreateorUpdateTableofChanges($id,$idcolname,$table,$conn) {
	$dbname = $_SESSION['dbname'];
	$userid = $_SESSION['userid'];
	$sessiondate = $_SESSION['sessiondate'];
	//create table if not exist and add change fields (add word 'Changes' to start of table name as default pattern for change tables
	$changetable = "Change".$table;
	$qq = "USE $dbname";
	mysql_query($qq,$conn);
	
	$forkeyoff = "SET FOREIGN_KEY_CHECKS=0";
	@mysql_unbuffered_query($forkeyoff,$conn);
	
	$qq = "CREATE TABLE IF NOT EXISTS $changetable LIKE $table";
	$rr = mysql_query($qq,$conn);
	if ($rr) {
		//add fields that track who made changes
		$qq="ALTER TABLE ".$dbname.".".$changetable."  ADD COLUMN ChangeID INT(10) NOT NULL, ADD COLUMN ChangedBy INT(10), ADD COLUMN ChangedDate DATE";
		mysql_query($qq,$conn);
		$qq = "ALTER TABLE $changetable CHANGE $idcolname $idcolname INT( 10 ) UNSIGNED NOT NULL";
		mysql_query($qq,$conn);
		$qq = "ALTER TABLE ".$dbname.".".$changetable." DROP PRIMARY KEY, ADD PRIMARY KEY (ChangeID)";
		mysql_query($qq,$conn);
		$qq = "ALTER TABLE ".$dbname.".".$changetable."  CHANGE `ChangeID` `ChangeID` INT( 10 ) NOT NULL AUTO_INCREMENT ";
		mysql_query($qq,$conn);
	}
	//store old value into change table
	$sql = "SELECT * FROM $table WHERE $idcolname='$id'";
	$res = mysql_query($sql,$conn);
	//echo $sql;
	$row = mysql_fetch_assoc($res);
	$nr = mysql_numrows($res);
	$toreturn = FALSE;
	if ($nr>0) {
		$qqq = "INSERT INTO $changetable (";
		foreach($row as $key => $val) {
			$qqq = $qqq." ".$key.",";
		}
		$qqq = $qqq." ChangedBy, ChangedDate) VALUES (";
		foreach($row as $key => $val) {
			$qqq = $qqq." '".$val."',";
		}
		$qqq = $qqq." '$userid', '$sessiondate')";
	


		//INSERE O REGISTRO DE MUDANCA
		mysql_query($qqq,$conn);
		$toreturn = TRUE;
	}
	$forkeyoff = "SET FOREIGN_KEY_CHECKS=1";
	@mysql_unbuffered_query($forkeyoff,$conn);
	return $toreturn;
}

function getoriginalhabitat($id,$conn) {
	$result = array();
	$qq = "SELECT * FROM Habitat WHERE HabitatID='$id'";
	$rr = @mysql_query($qq,$conn);
	$row = @mysql_fetch_assoc($rr);
	$name1 = array('habitattipo' => $row['HabitatTipo']);
	$name2 = array('parentid' => $row['ParentID']);
	$name3 = array('habitatname' => $row['Habitat']);
	$name4 = array('habitatdefinicao' => $row['Descricao']);
	$name5 = array('gazetteerid' => $row['LocalityID']);
	$name6 = array('gpspointid' => $row['GPSPointID']);
	$splist = array('specieslistids' => $row['EspeciesIds']);
	$result = array_merge((array)$result,(array)$name1,(array)$name2,(array)$name3,(array)$name4,(array)$name5,(array)$name6,(array)$splist);
	$qq = "SELECT * FROM Habitat_Variation WHERE HabitatID='$id'";
	$rr = @mysql_query($qq,$conn);
	$nn = @mysql_numrows($rr);
	if ($nn>0) {
		while ($row = mysql_fetch_assoc($rr)) {;
			$charid = $row['TraitID'];
			$aa = "SELECT * FROM Traits WHERE TraitID='$charid'";
			$rrr = mysql_query($aa,$conn);
			$rw = mysql_fetch_assoc($rrr);
			$tipo = $rw['TraitTipo'];
			$multiselect = $rw['MultiSelect'];
			//se imagem
			if ($tipo=='Variavel|Imagem') { //
				$traitkey = 'trait_'.$charid;
				$aar = array($traitkey => $row['HabitatVariation']);
				$result = array_merge((array)$result, (array)$aar);
			} else {
				//se quantitativo
					if ($tipo=='Variavel|Quantitativo') {
						$traitkey = 'traitvar_'.$charid;
						$aar = array($traitkey => $row['HabitatVariation']);
						$result = array_merge((array)$result, (array)$aar);
						$traitkey = 'traitunit_'.$charid;
						$aar = array($traitkey => $row['TraitUnit']);
						$result = array_merge((array)$result, (array)$aar);
					} else {
						$traitkey = 'traitvar_'.$charid;
						if ($tipo=='Variavel|Categoria' && $multiselect=='Sim') {
							$amulti = explode(";", $row['HabitatVariation']);
							$cr=1;
							$aar = array();
							foreach ($amulti as $kk => $vvv) {
								if (!empty($vvv)) {
									$vvv = trim($vvv);
									$tk = "traitmulti_".$charid."_".$vvv;
									$aaaar = array($tk => $vvv);
									$aar = array_merge((array)$aar, (array)$aaaar);
									$cr++;
								}
							}
						} else {
							$aar = array($traitkey => $row['HabitatVariation']);
						}
						$result = array_merge((array)$result, (array)$aar);
					}
			}
		}
	}
	return $result;
}

function describetaxa($detset,$conn) {
	$arr = unserialize($detset);
	//$newkeys = array('famid' ,'genusid' ,'speciesid' ,'infraspid' ,'determinadorid' ,'datadet','detconfidence' ,'detmodifier','refcoletor','refcolnum','refherbarium','refherbnum' ,'refdetby' ,'refdatadet','detnotes' );
	//extract($arr);
	//echopre($arr);
	$infraspid = $arr['InfraEspecieID'];
	$speciesid = $arr['EspecieID'];
	$famid = $arr['FamiliaID'];
	$genusid = $arr['GeneroID'];

	$determinadorid = $arr['DetbyID'];

	$detmodifier = $arr['DetModifier'];

	$datadet = $arr['DetDate'];
	$nome = gettaxaname($infraspid,$speciesid,$genusid,$famid,$conn);
	$nn = $nome;
	$nn = trim($nome);
	if (!empty($nn)) {
		$detmodifier = trim($detmodifier);
		if (!empty($detmodifier)) {
			$nn .= " (".$detmodifier.")";
		}
		if ($determinadorid>0) {
			$zz = getpessoa($determinadorid,$abb=TRUE,$conn);
			$zr = mysql_fetch_assoc($zz);
			$determinador = $zr['Abreviacao'];
		} 
		$nn .=" [Detby: ".$determinador." - ".$datadet."]";
	}
	return $nn;
}

function getdetsetvar($detid,$conn) {
	$qq = "SELECT * FROM Identidade WHERE DetID='$detid'";
		$rs = mysql_query($qq,$conn);
		$rw = mysql_fetch_assoc($rs);
	$detfields = array('FamiliaID' , 'GeneroID' ,'EspecieID' , 'InfraEspecieID' , 'DetbyID', 'DetDate' , 'DetConfidence' , 'DetModifier','RefColetor' , 'RefColnum' , 'RefHerbarium' , 'RefHerbNum' , 'RefDetby','RefDetDate' , 'DetNotes' );
	//$detarray = array('famid' ,'genusid' ,'speciesid' ,'infraspid' ,'determinadorid' ,'datadet','detconfidence' ,'detmodifier','refcoletor','refcolnum','refherbarium','refherbnum' ,'refdetby' ,'refdatadet','detnotes' );
	$detset = array();
	foreach ($detfields as $kk => $vf) {
		$val = $rw[$vf];
		//$kk = $detarray[$kk];
		$detset[$vf] = $val;
	}
	return $detset;
}

function describetaxacomposition($specieslistids,$conn,$includeheadings=TRUE) {
//lista de especies importantes na fisionomia
			$arraylist = explode(";",$specieslistids);
			if ($includeheadings) {
				$families = GetLangVar('namefamily');
				$genera = GetLangVar('namegenus');
				$species = GetLangVar('namespecies');
			} else {
				$families = '';
				$genera = '';
				$species = '';
			}
			foreach ($arraylist as $key => $value) {
				$dado = explode("|",$value);
				if (trim($dado[0])=='familia') {
					$rr = getfamilies($dado[1],$conn,$showinvalid=TRUE);
					$row = $rr[2];
					if ($families==GetLangVar('namefamily')) {
						$families = "<b>".$families."</b>: ".$row['Familia'];
					} else {
						if (!empty($families)) {
							$families = $families.", ".$row['Familia'];
						} else {
							 $families = $row['Familia'];
						}
					}
				}
				if (trim($dado[0])=='genero') {
					$rr = getgenera($dado[1],$famid,$conn,$showinvalid=TRUE);
					$row = @mysql_fetch_assoc($rr);
					if ($genera==GetLangVar('namegenus')) {
						$genera = "<b>".$genera."</b>: <i>".$row['Genero']."</i>";
					} else {
						if (!empty($genera)) {
							$genera = $genera.", <i>".$row['Genero']."</i>";
						} else {
							 $genera = "<i>".$row['Genero']."</i>";
						}
					}
				}
				if (trim($dado[0])=='especie') {
					$rr = getspecies($dado[1],$genusid,$conn,$showinvalid=TRUE);
					$row = mysql_fetch_assoc($rr);
					if ($species == GetLangVar('namespecies')) {
						$species = "<b>".$species."</b>: <i>".$row['Genero']." ".$row['Especie']."</i> ".$row['EspecieAutor'];
					} else {
						if (!empty($species)) {
							$species = $species.", <i>".$row['Genero']." ".$row['Especie']."</i> ".$row['EspecieAutor'];
						} else {
							$species = "<i>".$row['Genero']." ".$row['Especie']."</i> ".$row['EspecieAutor'];
						}
					}
				}
				if (trim($dado[0])=='infraspecies') {
					$rr = getinfraspecies($dado[1],$speciesid,$conn,$showinvalid=TRUE);
					$row = mysql_fetch_assoc($rr);
					if ($species == GetLangVar('namespecies')) {
						$species = $species." <i>".$row['Genero']." ".$row['Especie']."</i> ".$row['EspecieAutor']." ".$row['InfraEspecieNivel']." <i>".$row['InfraEspecie']."</i> ".$row['InfraEspecieAutor'];
					} else {
						if (!empty($species)) {
							$species = $species.", <i>".$row['Genero']." ".$row['Especie']."</i> ".$row['EspecieAutor']." ".$row['InfraEspecieNivel']." <i>".$row['InfraEspecie']."</i> ".$row['InfraEspecieAutor'];
						} else {
							$species = "<i>".$row['Genero']." ".$row['Especie']."</i> ".$row['EspecieAutor']." ".$row['InfraEspecieNivel']." <i>".$row['InfraEspecie']."</i> ".$row['InfraEspecieAutor'];
						}
					}
				}
			}
			unset($specieslist);
			//mysql_free_result($rr);
			unset($row);
			if ($includeheadings) {
				$fam = GetLangVar('namefamily');
				$gen = GetLangVar('namegenus');
				$sp = GetLangVar('namespecies');
			}
			if ($families!=$fam && !empty($families)) {
				$specieslist = $families.".";
			} 
			if ($genera!=$gen && !empty($genera)) {
				$specieslist = $specieslist." ".$genera.".";
			}
			if ($species!=$sp && !empty($species)) {
				$specieslist = $specieslist." ".$species.".";
			}
			$specieslist = trim($specieslist);
			$specieslist = str_replace("..",".",$specieslist);
			$specieslist = str_replace("..",".",$specieslist);
			return $specieslist;
}

function describehabitat($habitatid,$img=false,$conn) {
	include_once("functions/class.Numerical.php") ;
	//ob_start();
	//atualiza pathname
	//$nn = listhabitat($conn);
	$query="SELECT  * FROM Habitat  WHERE HabitatID = ".$habitatid;
	//echo $query;
	$nnn = mysql_query($query,$conn);
	$row = mysql_fetch_assoc($nnn);
	$pathname = $row['PathName'];
	$specieslistids = $row['EspeciesIds'];

	$qq = "SELECT * FROM Habitat_Variation as habvar JOIN Traits USING(TraitID) 
		WHERE HabitatID='$habitatid' ORDER BY Traits.PathName";
	$query = mysql_query($qq,$conn);
	if ($query) {
		$nquer = 1;
	} else { $nquer==0;}

	if ($nquer>0) {
	$classe = '';
	$varname = ' ';
	while ($rows = mysql_fetch_assoc($query)) { //para cada caractere
		$cl = str_replace(" - ".$rows['TraitName'],' ',$rows['PathName']);
		$cl = str_replace("Habitat - ",' ',$cl);
		$cl = trim($cl);
		$variation = trim($rows['HabitatVariation']);
	if (!empty($variation) && $variation!='none') {
		if ($cl!=$classe && $varname==" ") {
			//$varname = $varname."<b>".$cl."</b>: ";
		} elseif ($cl!=$classe) {
			$varname = $varname.". <b>".$cl."</b>: ";
		}
		if ($rows['TraitTipo']=='Variavel|Imagem' && !$img) {
			$noname = false;
		} else {
			$noname= true;
		}
		if ($cl==$classe && $noname) {
			$varname = $varname."; ".$rows['TraitName']." (";
		} elseif ($noname) {
			$varname = $varname." ".$rows['TraitName']." (";
		}

		$classe=$cl;
		//echo $rows['TraitName']."aqui<br>";
		if ($rows['TraitTipo']=='Variavel|Categoria') {
			$aarvar = explode(";",$rows['HabitatVariation']);
			$nvar = count($aarvar);
			$i =1;
			foreach ($aarvar as $kk => $val) {
				$qq = "SELECT * FROM Traits WHERE TraitID='$val'";
				$rr = mysql_unbuffered_query($qq);
				$rw = mysql_fetch_assoc($rr);
				$varname = $varname.strtolower($rw['TraitName']);
				if ($i<$nvar) { $varname = $varname.", ";}
				if ($i==$nvar) { $varname = $varname;}
				$i++;
				//mysql_free_result($rr);
			}
		}

		if ($rows['TraitTipo']=='Variavel|Quantitativo') {
			$varunit = $rows['TraitUnit'];
			$variation = $rows['HabitatVariation'];
			$aarvar = explode(";",$rows['HabitatVariation']);
			//print_r($aarvar);
			$nv = count($aarvar);
			if ($nv>1) {
				$mean = @round(Numerical::mean($aarvar),1);
				$stdev = @round(Numerical::standardDeviation($aarvar),1);
				$maxvar = max($aarvar);
				$minvar = min($aarvar);
				if ($varunit!=GetLangVar('namenumero')) {
					$varname = trim($varname).$mean."+/-".$stdev." [".$minvar."-".$maxvar."] ".strtolower($varunit);
				} else {
					$varname = trim($varname).$minvar."-".$mean."-".$maxvar." ".strtolower($varunit);
				}

			} elseif ($nv==1) {
				$varname = $varname.$variation." ".$varunit;
			}
		}
		if ($rows['TraitTipo']=='Variavel|Texto') {
			//$varname = $rows['TraitName'].": ";
			$variation = $rows['HabitatVariation'];
			$varname = $varname.$variation;
		}
		if ($rows['TraitTipo']=='Variavel|Imagem') {
			$variation = trim($rows['HabitatVariation']);
			if ($variation!='imagem') {
				$jpgar = explode(";",$variation);
				$nv = count($jpgar);
				$jj = trim($jpgar[0]);
				if ($nv>1 && (empty($jj) || $jj==0)) {
					unset($jpgar[0]);
					$fn = implode(";",$jpgar);
				} else {
					$fn = trim($variation);
				}
				if (!empty($fn)) {
					if ($img==true) {
						$imgname = "<img src='icons/ico_open.gif' onclick = \"javascript:small_window('showpicture.php?fn=$fn',700,500,'MostrarImg');\">";
						$imgname = $imgname."<b>".$nv."</b>&nbsp;imgs";
					} else { 
						$imgname='';
					}

					$varname = $varname.$imgname;
				}
			}
		}
			if ($noname) {
				$varname = trim($varname).")";
			}
		} 

		//mysql_free_result($query);
		//flush();
		//ob_flush();
	} //foreach variable
} 
	$pp = str_replace("\."," ",$pathname);
	$texttoprint = "<b>".strtupperacentos($pathname)."</b> ".$varname;

	if (!empty($specieslistids)) {
		//$splist = describetaxacomposition($specieslistids,$conn,$includeheadings=FALSE);
		//$splist = " <b>".GetLangVar('messageimportanttaxa')."</b>: ".$splist;
		//$texttoprint = $texttoprint.$splist;
	}
	//$texttoprint = $pathname.". ".$texttoprint;
	unset($rows);
	unset($row);
	//ob_end_clean();
	return $texttoprint;
}

function describetraits($traitsarray,$img=FALSE,$conn) {
	include_once("functions/class.Numerical.php") ;
	$qq = "SELECT * FROM Traits";
	$j=0;
	foreach ($traitsarray as $key => $value) {
		$arraykey = explode("_",$key);
		$charid = $arraykey[1];
		$varorunit = $arraykey[0];
		if ($varorunit=='traitvar' || $varorunit=='trait') {
			if ($j==0) {
				$qq = $qq." WHERE TraitID='".$charid."'";
			} else {
				$qq = $qq." OR TraitID='".$charid."'";
			}
		$j++;
		}
	}
	//echo $qq;
	//$nn = listtraits($qq,$conn);
	$query= $qq." ORDER BY PathName ASC";
	$nn = mysql_query($query,$conn);
	$classe = '';
	$varname = '';
	while ($row = mysql_fetch_assoc($nn)) { //para cada caractere
		$cl = str_replace(" - ".$row['TraitName'],' ',$row['PathName']);
		$cl = trim($cl);
		$traittipo = $row['TraitTipo'];
		$string = 'traitvar_'.$row['TraitID'];
		$variation = trim($traitsarray[$string]);
		if (empty($variation)) { //se for uma imagem
			$string = 'trait_'.$row['TraitID'];
			$variation = trim($traitsarray[$string]);
		}
		if (!empty($variation) && $variation!='none') {
		if ($cl!=$classe && $varname=='') {
			$varname = $varname."<b>".$cl."</b>: ";
		} elseif ($cl!=$classe) {
			$varname = $varname.". <b>".$cl."</b>: ";
		}
		if ($cl==$classe) {
			$varname = $varname."; ".$row['TraitName']." (";
		} else {
			$varname = $varname." ".$row['TraitName']." (";
		}
		$classe=$cl;
		//echo "<br>".$variation." ".$row['TraitName']."  Aqui::".$traittipo;
		if ($traittipo=='Variavel|Categoria') {
			$aarvar = explode(";",$variation);
			$nvar = count($aarvar);
			$i =1;
			foreach ($aarvar as $kk => $val) {
				$qq = "SELECT * FROM Traits WHERE TraitID='$val'";
				$rr = mysql_query($qq,$conn);
				$rw = mysql_fetch_assoc($rr);
				$varname = $varname.strtolower($rw['TraitName']);
				if ($i<$nvar) { $varname = $varname.", ";}
				if ($i==$nvar) { $varname = $varname;}
				$i++;
				//mysql_free_result($rr);
			}
		}
		if ($row['TraitTipo']=='Variavel|Quantitativo') {
			$varunit = $traitsarray['traitunit_'.$row['TraitID']];
			$aarvar = explode(";",$variation);
			//print_r($aarvar);
			$nv = count($aarvar);
			if ($nv>1) {
				$mean = @round(Numerical::mean($aarvar),1);
				$stdev = @round(Numerical::standardDeviation($aarvar),1);
				$maxvar = max($aarvar);
				$minvar = min($aarvar);
				if ($varunit!=GetLangVar('namenumero')) {
					$varname = trim($varname).$mean."+/-".$stdev." [".$minvar."-".$maxvar."] ".strtolower($varunit);
				} else {
					$varname = trim($varname).$minvar."-".$mean."-".$maxvar." ".strtolower($varunit);
				}
			} elseif ($nv==1) {
				$varname = trim($varname).$variation." ".$varunit;
			}
		}
		if ($row['TraitTipo']=='Variavel|Texto') {
			$varname = $varname.trim($variation);
		}
		if ($row['TraitTipo']=='Variavel|Taxonomy') {
			$specieslist = strip_tags(describetaxacomposition($variation,$conn,$includeheadings=TRUE));
			$varname = $varname.trim($specieslist);
		}
		if ($row['TraitTipo']=='Variavel|LinkEspecimenes') {
			$qsp = "SELECT CONCAT(pess.Abreviacao,' ',spec.Number,'    -  ', if (gettaxonname(spec.DetID,1,0) IS NULL,'',gettaxonname(spec.DetID,1,0))) as nome, spec.EspecimenID, CONCAT(spec.Ano,'-',spec.Mes,'-',spec.Day) as datacol  FROM Especimenes as spec JOIN Pessoas as pess ON spec.ColetorID=pess.PessoaID WHERE spec.EspecimenID=".$variation;
			$rsp = mysql_query($qsp,$conn);
			$rwsp = mysql_fetch_assoc($rsp);
			$varname  = $varname.$rwsp['nome'];
		}
		if ($row['TraitTipo']=='Variavel|Pessoa') {
			$addcolarr = explode(";",$variation);
			$addcoltxt = '';
			$j=1;
			foreach ($addcolarr as $kk => $vl) {
				$qq = "SELECT * FROM Pessoas WHERE PessoaID='".$vl."'";
				$res = mysql_query($qq,$conn);
				$rrw = mysql_fetch_assoc($res);
				if ($j==1) {
					$addcoltxt = $rrw['Abreviacao'];
				} else {
					$addcoltxt = $addcoltxt."; ".$rrw['Abreviacao'];
				}
				$j++;
			}
			$varname = $varname.trim($addcoltxt);
		}

		if ($row['TraitTipo']=='Variavel|Imagem') {
			$string = 'trait_'.$row['TraitID'];
			$variation = trim($traitsarray[$string]);
			if ($variation!='imagem' && !empty($variation)) {
				$aarvar = explode(";",$variation);
				foreach($aarvar as $kk => $vv) {
					$tvv = trim($vv);
					if (empty($tvv)) {
						unset($aarvar[$kk]);
					}
				}
				$nv = count($aarvar);
				if ($nv>=1) {
					$fn = implode(";",$aarvar);
					if ($img) {
					$imgname = "<img src='icons/ico_open.gif' onclick = \"javascript:small_window('showpicture.php?fn=$fn',700,500,'MostrarImg');\">";
					}
					$imgname = $imgname."&nbsp;<b>$nv</b>&nbsp;imgs";
					$varname = $varname.$imgname;
				}
			}
		}
		$varname = trim($varname).")";
		} //end if has a $value
	} //end for each
	//mysql_free_result($nn);
	unset($row);
	return $varname;
}

//this function is not being used...may be deleted
function updatetraits_old($arraryofvalue,$linkid,$linktype,$conn) {
$erro =0;
foreach ($arraryofvalue as $key => $value) {
		$arraykey = explode("_",$key);
		$charid = $arraykey[1];
		$varorunit = $arraykey[0];

		if (!empty($value) || count($value)>0) {
		$qq = "SELECT * FROM Traits WHERE TraitID='$charid'";
			$nch = mysql_query($qq,$conn);
			$rwch = mysql_fetch_assoc($nch);
			$traittipo = $rwch['TraitTipo'];



		$update = 0;
		if (!is_array($value)) {
			$vv = trim($value);
			if (empty($vv)) {$value=' ';}
		} else {
			$vv = $value;
		}
		if ($varorunit=='traitvar' && !empty($value)) {
			if (is_array($value)) {
				$value = implode(";",$value);
			} 
		}
		if ($varorunit=='traitunit' && $traittipo!='Variavel|Quantitativo' ) {
			$value = '';
		}
		if ($varorunit=='traitunit' && !empty($value)) {
			$ttunidade = $value;
			$tt = $arraryofvalue['traitvar_'.$charid];
			if ($tt) {
					$value = $arraryofvalue['traitvar_'.$charid];
			} else {
					$value = ' ';
			}
		} 
		if ($varorunit=='traitnone' && $value=='none') {
				$tt = $arraryofvalue['traitvar_'.$charid];
				if ($tt) {
					$value = $arraryofvalue['traitvar_'.$charid];
				} else {
					$value = ' ';
				}
		}
		if (!empty($value) && $varorunit!='traitmulti') {
			$qq = "SELECT * FROM Traits_variation WHERE TraitID='$charid' AND $linktype='$linkid'";
			$teste = mysql_query($qq,$conn);
			$update = @mysql_numrows($teste);
		}
		if ($value=='none') {$value=' ';}

		$fieldsaskeyofvaluearray = array($linktype => $linkid);
		$zz= 	array('TraitID' => $charid, 'TraitVariation' => $value, 'TraitUnit' => $ttunidade);
		$fieldsaskeyofvaluearray = array_merge((array)$fieldsaskeyofvaluearray,(array)$zz);

		//print_r($fieldsaskeyofvaluearray);
		//echo "<br>";

		//faz o cadastro ou atualiza variacao
		if (!empty($value) && $value!=' ' && $update==0 && $varorunit!='traitmulti' && $varorunit!='traitimg') {
			$newtrait = InsertIntoTable($fieldsaskeyofvaluearray,
			'TraitVariationID','Traits_variation',$conn);
			if (!$newtrait) {
				$erro++;
			}
			//echo "inseriu $varorunit $charid $value";
			//print_r($fieldsaskeyofvlauearray);
			//echo "<br>";

		}
		if ($update>0 && $varorunit!='traitmulti' && $varorunit!='traitimg') {
			$rrr = @mysql_fetch_assoc($teste);
			$oldval = trim($rrr['TraitVariation']);
			$tvv = @trim($value);
			$oldid  = $rrr['TraitVariationID'];

			//update if newvalue is different from old value
			if (($tvv!=$oldval  || $varorunit=='traitunit') && !empty($value) ) { 
				CreateorUpdateTableofChanges($oldid,'TraitVariationID','Traits_variation',$conn);
				$newupdate = UpdateTable($oldid,$fieldsaskeyofvaluearray,'TraitVariationID','Traits_variation',$conn);
				if (!$newupdate) {
					$erro++;
					}
			}
	 	}
	}
} //end for each  variable
	if ($erro==0) {return TRUE;} else {return FALSE;}
} //end of function

function updatetraits($arraryofvalue,$linkid,$linktype,$bibtexids,$conn) {
$erro =0;
foreach ($arraryofvalue as $key => $value) {
		$arraykey = explode("_",$key);
		$charid = $arraykey[1];
		$varorunit = $arraykey[0];

		if ($varorunit!='traitimgold') {
		//if (!empty($value) || count($value)>0) {
		$qq = "SELECT * FROM Traits WHERE TraitID='".$charid."'";
		$nch = mysql_query($qq,$conn);
		$rwch = mysql_fetch_assoc($nch);
		$traittipo = $rwch['TraitTipo'];
		$update = 0;
		if (!is_array($value)) {
			$vv = trim($value);
			if (empty($vv)) {$value=' ';}
		} else {
			$vv = $value;
			if ($varorunit=='traitvar' && count($vv)>0) {
				$value = implode(";",$vv);
			} 
		}
		//echo $traittipo."<br>";
		if ($traittipo=='Variavel|Quantitativo'  && $varorunit!='traitunit' ) {
			$ttunidade = $arraryofvalue['traitunit_'.$charid];
		} 
		else {
			$ttunidade = ' ';
		}
		if ($varorunit=='tratunit' && !empty($value)) {
			$value = ' ';
		}
		if ($varorunit=='traitnone' && $value=='none') {
				$tt = $arraryofvalue['traitvar_'.$charid];
				if ($tt) {
					$value = $arraryofvalue['traitvar_'.$charid];
				} else {
					$value = ' ';
				}
		} 
		elseif ($value=='none') {$value=' ';}
		if (!empty($value) && $varorunit!='traitmulti' && $varorunit!='traitunit') {
			if ($linktype=='HabitatID') {
				$tablename = "Habitat_Variation";
				$fieldname = "HabitatVariation";
				$tbidfie = "HabitatVariationID";
			} else {
				$tablename = "Traits_variation";
				$fieldname = "TraitVariation";
				$tbidfie = "TraitVariationID";
			}
			$qq = "SELECT * FROM ".$tablename." WHERE TraitID='".$charid."' AND ".$linktype."='".$linkid."'";
			$teste = mysql_query($qq,$conn);
			$update = @mysql_numrows($teste);
		}
		$fieldsaskeyofvaluearray = array($linktype => $linkid);
		if (!empty($bibtexids)) {
			$zz = array('TraitID' => $charid, $fieldname => $value, 'TraitUnit' => $ttunidade, 'BibtexIDS' => $bibtexids);
			$qtemp = "ALTER TABLE `Traits_variation`  ADD `BibtexIDS` CHAR(100) CHARACTER SET utf8 COLLATE utf8_general_ci NULL DEFAULT NULL COMMENT 'Bibliografia' AFTER `GrupoSppID`";
			@mysql_query($qtemp,$conn);
		} else {
			$zz = array('TraitID' => $charid, $fieldname => $value, 'TraitUnit' => $ttunidade);
		}
		$fieldsaskeyofvaluearray = array_merge((array)$fieldsaskeyofvaluearray,(array)$zz);
		//echopre($fieldsaskeyofvaluearray);
		//faz o cadastro ou atualiza variacao
		if (!empty($value) && $value!=' ' && $update==0 && $varorunit!='traitmulti' && $varorunit!='traitimg' && $varorunit!='traitunit' & $charid>0) {
			$newtrait = InsertIntoTable($fieldsaskeyofvaluearray,
			$tbidfie,$tablename,$conn);
			if (!$newtrait) {
				$erro++;
			}
		}
		if ($update>0 && $varorunit!='traitmulti' && $varorunit!='traitimg' && $varorunit!='traitunit' && $charid>0) {
			$rrr = @mysql_fetch_assoc($teste);
			$oldval = trim($rrr[$fieldname]);
			$tvv = $value;
			$oldid  = $rrr[$tbidfie];
			$oldunit  = $rrr['TraitUnit'];
			//update if newvalue is different from old value
			if ($tvv!=$oldval || $oldunit!=$ttunidade) {
				CreateorUpdateTableofChanges($oldid,$tbidfie,$tablename,$conn);
				$newupdate = UpdateTable($oldid,$fieldsaskeyofvaluearray,$tbidfie,$tablename,$conn);
				if (!$newupdate) {
					$erro++;
					}
			}
	 	}
	}
} //end for each  variable
	if ($erro==0) {return TRUE;} else {return FALSE;}
} //

///////TRAIT DEFINITION (QUANDO EDITANDO VIA GRID, PEGA OS VALORES DA DEFINICAO DA VARIAVEL E SE FOR DIFERENTE ALTERA
function update_traitsdefin($charid,$colvalue,$colname,$link) {
require_once("../".$link);
//require_once("../".$relativepathtoroot."includes/".$dbname.".php");
$conn = ConectaDB($dbname);
$erro =0;
//pega o valor antigo
$fieldsaskeyofvaluearray = array();
$fieldsaskeyofvaluearray[$colname] = $colvalue;
$sql  = "SELECT `".$colname."` FROM Traits WHERE TraitID='".$charid."'";
$rr = mysql_query($sql,$conn);
$rw = mysql_fetch_assoc($rr);
//CHANGE IF DIFFERENT
if ($rw[$colname]!=$colvalue) {
		CreateorUpdateTableofChanges($charid,"TraitID","Traits",$conn);
		$newtrait = UpdateTable($charid,$fieldsaskeyofvaluearray,"TraitID","Traits",$conn);
		if (!$newtrait) {
			$erro++;
		}
}
//return "../".$link.".php";
if ($erro==0) {return TRUE;} else {return FALSE;}
} 

function updatetraits_grid($charid,$traitvalue,$specid,$plantaid, $ttunidade, $dbname) {
require_once("../../../includes/".$dbname.".php");
$conn = ConectaDB($dbname);
$erro =0;
//pega o valor antigo
if ($specid>0) {
	$linktype='EspecimenID';
	$linkid = $specid;
} elseif ($plantaid>0) {
	$linktype='PlantaID';
	$linkid = $plantaid;
}
$qq = "SELECT * FROM Traits_variation WHERE TraitID='".$charid."' AND ".$linktype."='".$linkid."'";
$teste = mysql_query($qq,$conn);
$update = @mysql_numrows($teste);

$qq = "SELECT * FROM Traits WHERE TraitID='".$charid."'";
$nch = mysql_query($qq,$conn);
$rwch = mysql_fetch_assoc($nch);
$traittipo = $rwch['TraitTipo'];
if ($traittipo=='Variavel|Categoria') {
		$traitvalue = str_replace("'","",$traitvalue);
		$ez = explode(",",$traitvalue);
		$statevals = array();
		$ez = array_filter($ez);
		foreach( $ez as $vv) {
			$vv = trim($vv);
			if (!empty($vv)) {
				$qq = "SELECT TraitID FROM Traits WHERE ParentID='".$charid."'  AND LOWER(TraitName)=LOWER('".$vv."')";
				//echo $qq."<br />";
				$nch = mysql_query($qq,$conn);
				$rwch = mysql_fetch_assoc($nch);
				$statevals[] = $rwch['TraitID'];
			}
		}
		if (count($statevals)>0) {
			$traitvalue = implode(";",$statevals);
		} else {
			$traitvalue = "";
		}
}
$fieldsaskeyofvaluearray = array(
$linktype => $linkid,
'TraitID' => $charid, 
 "TraitVariation" => $traitvalue, 
'TraitUnit' => $ttunidade
);
$newtrait=0;
if (!empty($traitvalue) && $traitvalue!=' ' && $update==0) {
			$newtrait = InsertIntoTable($fieldsaskeyofvaluearray, "TraitVariationID",  "Traits_variation", $conn);
			if (!$newtrait) {
					$erro++;
			}
} elseif ($update>0 && !empty($traitvalue) && $traitvalue!=' ') {
			$rrr = @mysql_fetch_assoc($teste);
			$oldval = trim($rrr[ "TraitVariation"]);
			$tvv = $traitvalue;
			$oldid  = $rrr["TraitVariationID"];
			$oldunit  = $rrr['TraitUnit'];
			//update if newvalue is different from old value
			if ($tvv!=$oldval || $oldunit!=$ttunidade) {
				CreateorUpdateTableofChanges($oldid,"TraitVariationID","Traits_variation",$conn);
				$newtrait = UpdateTable($oldid,$fieldsaskeyofvaluearray,"TraitVariationID","Traits_variation",$conn);
				if (!$newtrait) {
					$erro++;
				}
			}
}
if ($erro==0) {return TRUE;} else {return FALSE;}

} //

function updatetraits_gridNoconn($charid,$traitvalue,$specid,$plantaid, $ttunidade, $conn) {
//require_once("../../../includes/".$dbname.".php");
//$conn = ConectaDB($dbname);
$erro =0;
//pega o valor antigo
if ($specid>0) {
	$linktype='EspecimenID';
	$linkid = $specid;
	$linktype2 = 'PlantaID';
	$link2id = $plantaid;
} elseif ($plantaid>0) {
	$linktype='PlantaID';
	$linkid = $plantaid;
	$linktype2 = 'EspecimenID';
	$link2id = $specid;
}
$qq = "SELECT * FROM Traits_variation WHERE TraitID='".$charid."' AND ".$linktype."='".$linkid."'";
$teste = mysql_query($qq,$conn);
$update = @mysql_numrows($teste);

$qq = "SELECT * FROM Traits WHERE TraitID='".$charid."'";
$nch = mysql_query($qq,$conn);
$rwch = mysql_fetch_assoc($nch);
$traittipo = $rwch['TraitTipo'];
if ($traittipo=='Variavel|Categoria') {
		$traitvalue = str_replace("'","",$traitvalue);
		$ez = explode(",",$traitvalue);
		$statevals = array();
		$ez = array_filter($ez);
		foreach( $ez as $vv) {
			$vv = trim($vv);
			if (!empty($vv)) {
				$qq = "SELECT TraitID FROM Traits WHERE ParentID='".$charid."'  AND LOWER(TraitName)=LOWER('".$vv."')";
				//echo $qq."<br />";
				$nch = mysql_query($qq,$conn);
				$rwch = mysql_fetch_assoc($nch);
				$statevals[] = $rwch['TraitID'];
			}
		}
		if (count($statevals)>0) {
			$traitvalue = implode(";",$statevals);
		} else {
			$traitvalue = "";
		}
}
$fieldsaskeyofvaluearray = array(
$linktype => $linkid,
$linktype2 => $link2id,
'TraitID' => $charid, 
 "TraitVariation" => $traitvalue, 
'TraitUnit' => $ttunidade
);
$newtrait=0;
if (!empty($traitvalue) && $traitvalue!=' ' && $update==0) {
			$newtrait = InsertIntoTable($fieldsaskeyofvaluearray, "TraitVariationID",  "Traits_variation", $conn);
			if (!$newtrait) {
					$erro++;
			}
} elseif ($update>0 && !empty($traitvalue) && $traitvalue!=' ') {
			$rrr = @mysql_fetch_assoc($teste);
			$oldval = trim($rrr[ "TraitVariation"]);
			$tvv = $traitvalue;
			$oldid  = $rrr["TraitVariationID"];
			$oldunit  = $rrr['TraitUnit'];
			//update if newvalue is different from old value
			if ($tvv!=$oldval || $oldunit!=$ttunidade) {
				CreateorUpdateTableofChanges($oldid,"TraitVariationID","Traits_variation",$conn);
				$newtrait = UpdateTable($oldid,$fieldsaskeyofvaluearray,"TraitVariationID","Traits_variation",$conn);
				if (!$newtrait) {
					$erro++;
				}
			}
}
if ($erro==0) {return TRUE;} else {return FALSE;}

} //

function updatetraits_fromgrid($charid,$traitvalue,$specid,$plantaid, $ttunidade, $conn) {
$erro =0;
//pega o valor antigo
if ($specid>0) {
	$linktype='EspecimenID';
	$linkid = $specid;
} elseif ($plantaid>0) {
	$linktype='PlantaID';
	$linkid = $plantaid;
}
$qq = "SELECT * FROM Traits_variation WHERE TraitID='".$charid."' AND ".$linktype."='".$linkid."'";
$teste = mysql_query($qq,$conn);
$update = @mysql_numrows($teste);

$qq = "SELECT * FROM Traits WHERE TraitID='".$charid."'";
$nch = mysql_query($qq,$conn);
$rwch = mysql_fetch_assoc($nch);
$traittipo = $rwch['TraitTipo'];
if ($traittipo=='Variavel|Categoria') {
		$traitvalue = str_replace("'","",$traitvalue);
		$ez = explode(",",$traitvalue);
		$statevals = array();
		$ez = array_filter($ez);
		//echopre ($ez);
		foreach( $ez as $vv) {
			$vv = trim($vv);
			if (!empty($vv)) {
				$qq = "SELECT TraitID FROM Traits WHERE ParentID='".$charid."'  AND LOWER(TraitName)=LOWER('".$vv."')";
				//echo $qq."<br />";
				$nch = mysql_query($qq,$conn);
				$rwch = mysql_fetch_assoc($nch);
				$statevals[] = $rwch['TraitID'];
			}
		}
		if (count($statevals)>0) {
			$traitvalue = implode(";",$statevals);
		} else {
			$traitvalue = "";
		}
}
$fieldsaskeyofvaluearray = array(
$linktype => $linkid,
'TraitID' => $charid, 
 "TraitVariation" => $traitvalue, 
'TraitUnit' => $ttunidade
);
$newtrait=0;

if (!empty($traitvalue) && $traitvalue!=' ' && $update==0) {
			$newtrait = InsertIntoTable($fieldsaskeyofvaluearray, "TraitVariationID",  "Traits_variation", $conn);
//echopre($fieldsaskeyofvaluearray);
			if (!$newtrait) {
					$erro++;
			}
} elseif ($update>0 && !empty($traitvalue) && $traitvalue!=' ') {
			$rrr = @mysql_fetch_assoc($teste);
			$oldval = trim($rrr[ "TraitVariation"]);
			$tvv = $traitvalue;
			$oldid  = $rrr["TraitVariationID"];
			$oldunit  = $rrr['TraitUnit'];
			//update if newvalue is different from old value
			if ($tvv!=$oldval || $oldunit!=$ttunidade) {
				CreateorUpdateTableofChanges($oldid,"TraitVariationID","Traits_variation",$conn);
				$newtrait = UpdateTable($oldid,$fieldsaskeyofvaluearray,"TraitVariationID","Traits_variation",$conn);
				if (!$newtrait) {
					$erro++;
				}
			}
}
if ($erro==0) {return TRUE;} else {return FALSE;}

} //

function updatemonitoramento($arraryofvalue,$dataobs,$pltid,$conn) {
$err =0;
foreach ($arraryofvalue as $key => $value) {
		$arraykey = explode("_",$key);
		$charid = $arraykey[1];
		$varorunit = $arraykey[0];

		if ($varorunit!='traitimgold') {
		//if (!empty($value) || count($value)>0) {
		$qq = "SELECT * FROM Traits WHERE TraitID='".$charid."'";
		$nch = mysql_query($qq,$conn);
		$rwch = mysql_fetch_assoc($nch);
		$traittipo = $rwch['TraitTipo'];
		$update = 0;
		if (!is_array($value)) {
			$vv = trim($value);
			if (empty($vv)) {$value=' ';}
		} else {
			$vv = $value;
			if ($varorunit=='traitvar' && count($vv)>0) {
				$value = implode(";",$vv);
			} 
		}
		//echo $traittipo."<br>";
		if ($traittipo=='Variavel|Quantitativo'  && $varorunit!='traitunit' ) {
			$ttunidade = $arraryofvalue['traitunit_'.$charid];
		} else {
			$ttunidade = ' ';
		}
		if ($varorunit=='tratunit' && !empty($value)) {
			$value = ' ';
		}
		if ($varorunit=='traitnone' && $value=='none') {
				$tt = $arraryofvalue['traitvar_'.$charid];
				if ($tt) {
					$value = $arraryofvalue['traitvar_'.$charid];
				} else {
					$value = ' ';
				}
		} elseif ($value=='none') {$value=' ';}
		if (!empty($value) && $varorunit!='traitmulti' && $varorunit!='traitunit') {

			$tablename = "Monitoramento";
			$fieldname = "TraitVariation";
			$tbidfie = "MonitoramentoID";
			$qq = "SELECT * FROM Monitoramento WHERE TraitID='".$charid."' AND DataObs='".$dataobs."' AND PlantaID='".$pltid."'";
			//echo $qq."<br />";
			$teste = mysql_query($qq,$conn);
			$update = @mysql_numrows($teste);
		}
		$fieldsaskeyofvaluearray = array('PlantaID' => $pltid, 'DataObs' => $dataobs);
		$zz = array('TraitID' => $charid, 'TraitVariation' => $value, 'TraitUnit' => $ttunidade);
		$fieldsaskeyofvaluearray = array_merge((array)$fieldsaskeyofvaluearray,(array)$zz);
		//faz o cadastro ou atualiza variacao
		//echopre($fieldsaskeyofvaluearray);
		//echo "value:  ".$value."  varorunit:".$varorunit."  update:".$update;
		if (!empty($value) && $value!=' ' && $update==0 && $varorunit!='traitmulti' && $varorunit!='traitimg' && $varorunit!='traitunit' && $charid>0) {
			$newtrait = InsertIntoTable($fieldsaskeyofvaluearray,
			$tbidfie,$tablename,$conn);
			if (!$newtrait) {
				$err++;
			}
		}
		if ($update>0 && $varorunit!='traitmulti' && $varorunit!='traitimg' && $varorunit!='traitunit' && $charid>0) {
			$rrr = @mysql_fetch_assoc($teste);
			$oldval = trim($rrr[$fieldname]);
			$tvv = $value;
			$oldid  = $rrr[$tbidfie];
			$oldunit  = $rrr['TraitUnit'];
			//update if newvalue is different from old value
			if ($tvv!=$oldval || $oldunit!=ttunidade) {
				CreateorUpdateTableofChanges($oldid,$tbidfie,$tablename,$conn);
				$newupdate = UpdateTable($oldid,$fieldsaskeyofvaluearray,$tbidfie,$tablename,$conn);
				if (!$newupdate) {
					$err++;
					}
			}
	 	}
	}
} //end for each  variable
	if ($err==0) {return TRUE;} else {return FALSE;}
} //
function puttraitrow3($oldvals,$formid,$idd,$traitids,$conn,$page,$title) {
		$qq = "SELECT * FROM Formularios WHERE FormID='".$formid."'";
		$res = mysql_unbuffered_query($qq,$conn);
		$rr = mysql_fetch_assoc($res);
		$FormFieldsIDS= $rr['FormFieldsIDS'];
		$fieldids = explode(";",$FormFieldsIDS);

		$qq = "SELECT * FROM Traits WHERE ";
		$i=0;
		foreach ($fieldids as $key => $value) {
				if ($i==0) {
					$qq = $qq." TraitID='".$value."'";
				} else {
					$qq = $qq." OR TraitID='".$value."'";
				}
				$i++;
		}
		$qq = $qq." ORDER BY PathName";
		$rr = mysql_query($qq);
		while ($row= mysql_fetch_assoc($rr)) { //para cada variavel no relatorio
				$tt = str_replace(" - "," ",$row['PathName']);

				if ($row['TraitTipo']=='Variavel|Categoria') {
				//opcoes de variaves categoricas

					$qq = "SELECT * FROM Traits WHERE ParentID='".$row['TraitID']."' ORDER BY TraitName";
					$trrw = mysql_query($qq,$conn);

					$nstates = mysql_numrows($trrw);
						//echopre($oldvals);
					if ($nstates>2) {

						//echo "traitvar_".$row['TraitID']."_".$idd." ".$nstates."<br>";
						if (!$page) {
							$valores_array = $oldvals["traitvar_".$row['TraitID']."_".$idd];
						}
						//echopre($valores_array);


						echo "<td title='".$title."'><table ><tr>";
						if ($row['MultiSelect']!='Sim') {
							echo "<td style='border:0'><select name='traitvar_".$row['TraitID']."_".$idd."'>";
							echo "<option value=''>----</option>";

						} else {
							echo "<td style='border:0'><select size='2' name='traitvar_".$row['TraitID']."_".$idd."[]' multiple='yes'>";
						}


						while ($rww= mysql_fetch_assoc($trrw)) { //para cada estado de variacao
							//unset($valor);
							//unset($toe);
							if ($row['MultiSelect']=='Sim') {
									$ttn = "traitmulti_".$row['TraitID']."_".$rww['TraitID'];
									if ($page) {
										$valor =  trim($oldvals[$ttn]);
									} else {
										if (in_array($rww['TraitID'],$valores_array)) {
											$valor = $rww['TraitID'];
										} else {
											unset($valor);
										}
									}
							} else {
								$ttn = "traitvar_".$row['TraitID'];
								if ($page) {
									$valor =  trim($oldvals[$ttn]);
								} else {
									$valor =  trim($oldvals[$ttn."_".$idd]);
								}
							}
							echo "<option ";
							if (!empty($valor) && $valor==$rww['TraitID']) {
								echo " selected ";
							}
							echo " value='".$rww['TraitID']."'>".$rww['TraitName']."</option>";
						} 
						if ($row['MultiSelect']=='Sim') {
							echo "<option value=''>----</option>";
						}
						echo "</select></td>";
					} else {
						if (!$page) {
							$valores = $oldvals["traitvar_".$row['TraitID']."_".$idd];
						}
						if ($row['MultiSelect']!='Sim') {
							$tttipo = 'radio';
						} else {
							$tttipo = 'checkbox';
						}
					echo "<td style='border:0'><table><tr>";
						while ($rww= mysql_fetch_assoc($trrw)) {
							if ($row['MultiSelect']=='Sim') {
									$ttn = "traitmulti_".$row['TraitID']."_".$rww['TraitID'];
									if ($page) {
										$valor =  trim($oldvals[$ttn]);
									} else {
										if (in_array($rww['TraitID'],$valores)) {
											$valor = $rww['TraitID'];
										} else {
											unset($valor);
										}
									}
									$varn = "traitvar_".$row['TraitID']."_".$idd."[]";
							} else {
								$ttn = "traitvar_".$row['TraitID'];
								if ($page) {
									$valor =  trim($oldvals[$ttn]);
								} else {
									$valor =  trim($oldvals[$ttn."_".$idd]);
								}
								$varn = "traitvar_".$row['TraitID']."_".$idd;
							}
							echo "
							<td align='right'>
								<input type='".$tttipo."' name='".$varn."' ";
							if (!empty($valor) && $valor==$rww['TraitID']) {
								echo " checked ";
							}
							echo " value='".$rww['TraitID']."' /></td>
							<td align='left'>".$rww['TraitName']."</td>" ;
						}
					}
					echo "</tr></table></td>";
				}


				//se quantitativo
				if ($row['TraitTipo']=='Variavel|Quantitativo') {
					$string = 'traitvar_'.$row['TraitID'];
					if ($page) {
						$val =  trim($oldvals[$string]);
						} else {
						$val =  trim($oldvals[$string."_".$idd]);
					}
					echo "
					<td  title='".$title."'><table border=0>
						<tr><td style='border:0' >
							<input type='text' name='traitvar_".$row['TraitID']."_".$idd."' value='$val' size='10' />";
						echo "</td>
						<td style='border:0' >
							<select name='traitunit_".$row['TraitID']."_".$idd."' style=\"width: 20mm\" >";

							$string = 'traitunit_'.$row['TraitID'];
							if ($page) {
								$val =  trim($oldvals[$string]);
							} else {
								$val =  trim($oldvals[$string."_".$idd]);
							}
							$vnamearr = array();
							if (empty($val) && !empty($row['TraitUnit'])) {
								$vnamearr[] = $row['TraitUnit'];
								echo "<option selected value='".$row['TraitUnit']."'>".$row['TraitUnit']."</option>";
							} elseif (!empty($val)) {
								$vnamearr[] = $val;
								echo "<option selected value='".$val."'>".$val."</option>";
							}
							$qq = "SELECT DISTINCT TraitUnit FROM Traits WHERE TraitUnit<>'' ORDER BY TraitUnit ASC";
							$res = mysql_query($qq,$conn);
							if ($res) {
								while ($rwu=mysql_fetch_assoc($res)) {
									$varname = $rwu['TraitUnit'];
									echo "<option value='".$varname."'>".$varname."</option>";
									$vnamearr[] = $varname;
								}
							} 

								$qq = "SELECT * FROM VarLang WHERE VariableName LIKE '%traitunit%' ORDER BY '$lang' ASC";
								$rs = mysql_query($qq,$conn);
								if ($rs) {
									while ($rwu=mysql_fetch_assoc($rs)) {
										$varname = $rwu['VariableName'];
										if (!in_array($varname,$vnamearr)) {
											$zz = explode("_",$varname);
											if ($zz[1]!='desc') {
												echo "<option value='".GetLangVar($varname)."'>".GetLangVar($varname)."</option>";
											}
										}
									}
								}

					echo "</select>
					</td></tr>
					</table></td>
					";
				}

				//se imagem
				if ($row['TraitTipo']=='Variavel|Imagem') {
					$string = 'trait_'.$row['TraitID'];
					$imgfile = 'traitimg_'.$row['TraitID'];
						if ($page) {
								$val =  trim($oldvals[$string]);
								$oldimgvals = $val;
								$str = 'traitimgautor_'.$row['TraitID'];
								$valautors = trim($oldvals[$str]);

							} else {
								$val =  trim($oldvals[$string."_".$idd]);
								$str = 'traitimgautor_'.$row['TraitID']."_".$idd;
								$valautors = trim($oldvals[$str]);
							}

					echo "<td  title='".$title."'>
					<input type=hidden name ='traitimgautor_".$row['TraitID']."_".$idd."' value='".$oldimgvals."' />
					<table>";
					$val = explode(";",$val);
					if (count($val)>0) {
						foreach ($val as $kk => $vv) {
							$vv = $vv+0;
							if ($vv>0) {
								$qq = "SELECT * FROM Imagens WHERE ImageID='$vv'";
								$rt = mysql_query($qq,$conn);
								$rtw = mysql_fetch_assoc($rt);

								//diretorios das imagens
								$pthumb = 'img/thumbnails/';
								$imgbres = 'img/lowres/';
								$path = 'img/copias_baixa_resolucao/';

								$imagid = $rtw['ImageID'];
								$filename = trim($rtw['FileName']);

								$autor = $rtw['Autores'];
								//echo 'fotografo  2 = '.$autor;
								$autorarr = explode(";",$autor);
								if (count($autorarr)>0) {
									$j=1;
									foreach ($autorarr as $aut) {
										$qq = "SELECT * FROM Pessoas WHERE PessoaID='".$aut."'";
											$res = mysql_query($qq,$conn);
											$rwr = mysql_fetch_assoc($res);
										if ($j==1) {
											$autotxt = 	$rwr['Abreviacao'];
										} else {
											$autotxt = $autotxt."; ".$rwr['Abreviacao'];
										}
										$j++;
									}
								} 
								//echo '<br>fotografo  3 = '.$autotxt."<br>";

								$fotodata = $rtw['DateOriginal'];


								if (file_exists($path.$filename)) {
									$fn = explode("_",$filename);
									unset($fn[0]);
									unset($fn[1]);
									$fn = implode("_",$fn);

									$fntxt = $fn."   [";
									if (!empty($autotxt)) { $fntxt = $fntxt." ".GetLangVar('namefotografo').": ".$autotxt." - ".$fotodata."]";} else {
										$fntxt = $fntxt.$fotodata."]";
									}

									echo "<tr >
									<td ><table >
									<tr>
									<td >
									<a href=\"".$imgbres.$filename."\" class='MagicZoomPlus'  rel=\"zoom-position:right;zoom-height:200px; zoom-fade:true; smoothing-speed:17;opacity-reverse:true;\" >
									<img width=\"40\" src=\"".$pthumb.$filename."\"/>
									</a></td>
									<td >&nbsp;</td>
									<td class='tinny' id='fname_".$row['TraitID']."_".$imagid."_".$idd."'>$fntxt</td>";
									$fndeleted = "<STRIKE>$fn</STRIKE>";
									echo "<input type='hidden' id='fnamedeleted_".$row['TraitID']."_".$imagid."_".$idd."' value='$fndeleted' />";
									echo "<input type='hidden' id='imgtodel_".$row['TraitID']."_".$imagid."_".$idd."' name='imgtodel_".$row['TraitID']."_".$imagid."_".$idd."' value='' />";
									echo "<input type='hidden' id='imagid_".$row['TraitID']."_".$imagid."_".$idd."'  name='imagid_".$row['TraitID']."_".$imagid."_".$idd."' value='$imagid' />";
									echo "<input type='hidden' id='fnameundeleted".$row['TraitID']."_".$imagid."_".$idd."' value='$fn' />";

									echo "<td ><img height=14 src=\"icons/application-exit.png\"";
									echo	" onclick=\"javascript:deletimage('fnamedeleted_".$row['TraitID']."_".$imagid."_".$idd."','fname_".$row['TraitID']."_".$imagid."_".$idd."','imgtodel_".$row['TraitID']."_".$imagid."_".$idd."',1);\" />
									</td>
									<td ><img height=14 src=\"icons/list-add.png\"";
									echo	" onclick=\"javascript:deletimage('fnameundeleted".$row['TraitID']."_".$imagid."_".$idd."','fname_".$row['TraitID']."_".$imagid."_".$idd."','imgtodel_".$row['TraitID']."_".$imagid."_".$idd."',0);\" />
									</td>
									</tr>
									</table></tr>";
								} else {
									$refname = 'traitimg_'.$row['TraitID']."_".$idd;
									$val = eval('unset($'.$refname.');');
								}
							}
						}
					}
					echo	"<tr >
							<td >
							<table ><td >";
								$varname = 'trait_'.$row['TraitID']."_".$idd;
								echo "<input type=\"file\" name=\"$varname\" />
											<script type=\"text/javascript\">
												window.addEvent('domready', function(){
												new MultiUpload($( 'finalform' ).$varname );});
											</script>
								<input type=hidden name='traitimg_".$row['TraitID']."_".$idd."' value='imagem' />
							</td>
							<td >
								<td >".GetLangVar('namefotografo')."s</td>
								<input type='hidden' name='traitimgautor_".$row['TraitID']."_".$idd."' value='".$autor."' />
									<td ><input type='text' name='traitimgautortxt_".$row['TraitID']."_".$idd."' value='".$autortxt."' readonly /></td>
									<td ><input type=button value=\"".GetLangVar('nameselect')."\" class='bsubmit' ";
											$valuevar = "traitimgautor_".$row['TraitID']."_".$idd;
											$valuetxt = "traitimgautortxt_".$row['TraitID']."_".$idd;
											$myurl ="addcollpopup.php?getaddcollids=$valautors&valuevar=$valuevar&valuetxt=$valuetxt&formname=finalform"; 
											echo " onclick = \"javascript:small_window('$myurl',400,400,'Add_from_Src_to_Dest');\" />
									</td></tr>
								</table>
							</td>
							</tr></table></td>";
				}

				//se texto
				if ($row['TraitTipo']=='Variavel|Texto') {
					//echo "<input type=hidden name='traitnone_".$row['TraitID']."_".$idd."' value='none' />";
					$string = 'traitvar_'.$row['TraitID'];
					if ($page) {
						$val = $oldvals[$string];
					} else {
						$val= $oldvals[$string."_".$idd];
					}
					//tem um problema aqui quando apaga os dados
					echo "<td  title='".$title.": ".$val."'><textarea name='traitvar_".$row['TraitID']."_".$idd."' cols='30' rows='1' >".$val."</textarea></td>";
				}
		}//end of loop de cada variavel relatorio
}

function gettraits($linkid,$linktype,$conn) {
	$qq = "SELECT * FROM Traits_variation WHERE $linktype='$linkid'";
	$rr = mysql_query($qq,$conn);
	$results = array();
	while ($row = mysql_fetch_assoc($rr)) {
		$charid = $row['TraitID'];
		$variation = trim($row['TraitVariation']);
		$traitunit = $row['TraitUnit'];
//echo $charid." variation = ".$variation." ".$traitunit."<br>$qq";
		$qq = "SELECT * FROM Traits WHERE TraitID='$charid'";
		$rrr = mysql_query($qq,$conn);
		$rw  = mysql_fetch_assoc($rrr);
		$tipo = $rw['TraitTipo'];
		if (!empty($variation)) {
			if ($tipo=='Variavel|Categoria') {
				$arrvals = explode(";",$variation);
				if (count($arrvals)<=1) {
					$aar = array('traitvar_'.$charid => $variation);
					$results = array_merge((array)$results,(array)$aar);
				}
				if (count($arrvals)>1) {
					foreach ($arrvals as $kk => $vv) {
						$aar = array('traitmulti_'.$charid.'_'.$vv => $vv);
						$results = array_merge((array)$results,(array)$aar);
					}
				}
		} else {
			if ($tipo=='Variavel|Quantitativo') {
				$aar = array('traitunit_'.$charid => $traitunit);
				$results = array_merge((array)$results,(array)$aar);
			} 
			$aar = array('traitvar_'.$charid => $variation);
			$results = array_merge((array)$results,(array)$aar);
		}
		} //if empty variation
 } // end while
 return $results;
} //end of function


function storeoriginaldatatopost($id,$typeid,$formid,$conn,$traitids) {
	if ($formid>0) {
		$qq = "SELECT * FROM Formularios WHERE FormID='$formid'";
		$res = mysql_query($qq,$conn);
		$rw = mysql_fetch_assoc($res);
		$traitids = explode(";",$rw['FormFieldsIDS']);
		//print_r($traitids);
		$nn=1;
		//mysql_free_result($res);
	} else {
		if (empty($traitids)) {
			$qq = "SELECT TraitID FROM Traits_variation WHERE $typeid='$id'";
			$rr = mysql_query($qq,$conn);
			$nn = mysql_numrows($rr);
			$traitids = array();
			while ($rw = mysql_fetch_assoc($rr)) {
				$cid = $rw['TraitID'];
				$traitids = array_merge((array)$traitids,(array)$cid);
			}
		} else {
			$nn=1;
		}
		//mysql_free_result($rr);
	}
	$result = array();
	if ($nn>0) {
		foreach ($traitids as $ttid) {
			$qq = "SELECT * FROM Traits_variation WHERE $typeid='$id' AND TraitID='$ttid'";
			$resul = mysql_query($qq,$conn);
			$nnn = mysql_numrows($resul);
			if ($nnn>0) {
				$row = mysql_fetch_assoc($resul);
			}

				$charid = $row['TraitID'];
				$variation = trim($row['TraitVariation']);
				$aa = "SELECT * FROM Traits WHERE TraitID='$charid'";
				$rrr = mysql_unbuffered_query($aa,$conn);
				$rw = mysql_fetch_assoc($rrr);
				$tipo = $rw['TraitTipo'];
				//se imagem
				if (!empty($variation)) {
					if ($tipo=='Variavel|Imagem') { 
						$traitkey = 'trait_'.$charid;
						$ttkey = 'traitimgold_'.$charid;

						//echo "HHHHHHERE".$traitkey.$row['TraitVariation'];
						$z = explode(";",$row['TraitVariation']);
						if (is_array($z)) {
							foreach ($z as $k => $v) {
								$vv = trim($v);
								if (empty($vv) || $vv=='imagem') {
									unset($z[$k]);
								}
							}
						}
						if (count($z)>=1) {
							$varia = implode(";",$z);
							$aar = array($traitkey => $varia, $ttkey => $varia);
							$result = array_merge((array)$result, (array)$aar);
						}
						//echopre($aar);
					} else {
					//se quantitativo
					if ($tipo=='Variavel|Quantitativo') {
						$traitkey = 'traitvar_'.$charid;
						$aar = array($traitkey => $row['TraitVariation']);
						$result = array_merge((array)$result, (array)$aar);
						$traitkey = 'traitunit_'.$charid;
						$aar = array($traitkey => $row['TraitUnit']);
						$result = array_merge((array)$result, (array)$aar);
					} else {
						$traitkey = 'traitvar_'.$charid;
						//se categorico prepara array de valores
						$multiselect = $rw['MultiSelect'];
						if ($tipo=='Variavel|Categoria' && $multiselect=='Sim') {
							$amulti = explode(";", $row['TraitVariation']);
							$aar = array();
							foreach ($amulti as $kk => $vvv) {
								if (!empty($vvv)) {
									$vvv = trim($vvv);
									$tk = "traitmulti_".$charid."_".$vvv;
									$aaaar = array($tk => $vvv);
									$aar = array_merge((array)$aar, (array)$aaaar);
								}
							}
							$tk = "traitvar_".$charid;
							$aaaar = array($traitkey => $variation);
							$aar = array_merge((array)$aar, (array)$aaaar);
						} else {
							$aar = array($traitkey => $row['TraitVariation']);
						}
						$result = array_merge((array)$result, (array)$aar);
					}
					}
			} //end if variation
		//}
		  //mysql_free_result($rrr);
		  //mysql_free_result($resul);
		}
	}
	//echo "<br>Final";
	return $result;
}



function storeoriginaldatatopost2($id,$typeid,$conn) {
	if (count($id)>1) {
		$qq = "SELECT * FROM Traits_variation WHERE ";
		$yz=1;
		foreach ($id as $idd) {
			if ($yz!=1) { $qq= $qq." OR ";}
			$qq = $qq." $typeid='$idd'";
			$yz++;
		}
	} else {
		$qq = "SELECT * FROM Traits_variation WHERE $typeid='$id'";
	}
	//echo $qq;
	$rr = mysql_query($qq,$conn);
	$nn = mysql_numrows($rr);
	//$res = array($nn);
	$result = array();
	if ($nn>0) {
		while ($row = mysql_fetch_assoc($rr)) {
			//echo "<br>Initial";
			//print_r($result);
			$charid = $row['TraitID'];
			$variation = trim($row['TraitVariation']);
			$aa = "SELECT * FROM Traits WHERE TraitID='$charid'";
			$rrr = mysql_query($aa,$conn);
			$rw = mysql_fetch_assoc($rrr);
			$tipo = $rw['TraitTipo'];
			//se imagem
			if (!empty($variation)) {
			if ($tipo=='Variavel|Imagem') { 
				$traitkey = 'trait_'.$charid;
				//echo "HHHHHHERE".$traitkey.$row['TraitVariation'];
				$z = explode(";",$row['TraitVariation']);
				if (is_array($z)) {
					foreach ($z as $k => $v) {
						$vv = trim($v);
						if (empty($vv) || $vv=='imagem') {
							unset($z[$k]);
						}
					}
				}
				if (count($z)>=1) {
					$varia = implode(";",$z);
					$aar = array($traitkey => $varia);
					$result = array_merge((array)$result, (array)$aar);
				}
			} else {
				//se quantitativo
				if ($tipo=='Variavel|Quantitativo') {
						$traitkey = 'traitvar_'.$charid;
						$aar = array($traitkey => $row['TraitVariation']);
						$result = array_merge((array)$result, (array)$aar);
						$traitkey = 'traitunit_'.$charid;
						$aar = array($traitkey => $row['TraitUnit']);
						$result = array_merge((array)$result, (array)$aar);
				} else {
						$traitkey = 'traitvar_'.$charid;
						//se categorico prepara array de valores
						$multiselect = $rw['MultiSelect'];
						if ($tipo=='Variavel|Categoria' && $multiselect=='Sim') {
							$amulti = explode(";", $row['TraitVariation']);
							$aar = array();
							foreach ($amulti as $kk => $vvv) {
								if (!empty($vvv)) {
									$vvv = trim($vvv);
									$tk = "traitmulti_".$charid."_".$vvv;
									$aaaar = array($tk => $vvv);
									$aar = array_merge((array)$aar, (array)$aaaar);
								}
							}
							$tk = "traitvar_".$charid;
							$aaaar = array($traitkey => $variation);
							$aar = array_merge((array)$aar, (array)$aaaar);
						} else {
							$aar = array($traitkey => $row['TraitVariation']);
						}
						$result = array_merge((array)$result, (array)$aar);
				}
			}
			} //end if variation
		}
	}
	//echo "<br>Final";
	return $result;
}


function EnteringVarFor($especimenid,$plantid,$infraspid,$speciesid,$genusid,$famid,$conn) {
	if (!empty($especimenid)) {
			$oldvals = storeoriginaldatatopost($especimenid,'EspecimenID',$formid,$conn,$traitids);
			$qq = "SELECT * FROM Especimenes JOIN Pessoas ON ColetorID=PessoaID WHERE EspecimenID='$especimenid'";
			$rr = mysql_query($qq,$conn);
			$row= mysql_fetch_assoc($rr);
			echo "<td class='tdsmallbold'>".$row['Abreviacao']." ".$row['Number']."</td>";
		} else {
			if (!empty($plantid)) {
				$oldvals = storeoriginaldatatopost($plantid,'PlantaID',$formid,$conn,$traitids);
				$qq = "SELECT * FROM Plantas WHERE PlantaID='$plantid'";
				$rr = mysql_query($qq,$conn);
				$row= mysql_fetch_assoc($rr);
					$jbinexsitu = $row['InSituExSitu'];
						if ($jbinexsitu=='Exsitu') {
							$jbtext = "JB-X";
						} elseif ($jbinexsitu=='Insitu') {
							$jbtext = "JB-N";
						} else {
							$jbtext ='';
						}
				echo "<td class='tdsmallbold'>".$jbtext." ".$row['PlantaTag']."</td>";
			} else {
				if (!empty($infraspid)) {
						$oldvals = storeoriginaldatatopost($infraspid,'InfraEspecieID',$formid,$conn,$traitids);
						$rr = getinfraspecies($infraspid,$speciesid,$conn,$showinvalid=TRUE);
						$row = mysql_fetch_assoc($rr);
						$rr = getspecies($speciesid,$genusid,$conn,$showinvalid=TRUE);
						$rw = mysql_fetch_assoc($rr);
						$rr = getgenera($genusid,$famid,$conn,$showinvalid=TRUE);
						$rrr = mysql_fetch_assoc($rr);
						echo "<td class='tdsmallbold'><i>".$rrr['Genero']." ".$rw['Especie']."</i> ".$row['InfraEspecieNivel']." <i>".$row['InfraEspecie']."</i></td>";
				} else {
					if (!empty($speciesid)) {
						$oldvals = storeoriginaldatatopost($speciesid,'EspecieID',$formid,$conn,$traitids);
						$rr = getspecies($speciesid,$genusid,$conn,$showinvalid=TRUE);
						$rw = mysql_fetch_assoc($rr);
						$rr = getgenera($genusid,$famid,$conn,$showinvalid=TRUE);
						$rrr = mysql_fetch_assoc($rr);
						echo "<td class='tdsmallbold'><i>".$rrr['Genero']." ".$rw['Especie']."</i></td>";
					} else {
						if (!empty($genusid)) {
							$oldvals = storeoriginaldatatopost($genusid,'GeneroID',$formid,$conn,$traitids);
							$rr = getgenera($genusid,$famid,$conn,$showinvalid=TRUE);
							$rrr = mysql_fetch_assoc($rr);
							echo "<td class='tdsmallbold'><i>".$rrr['Genero']."</i></td>";
						} else {
							$oldvals = storeoriginaldatatopost($famid,'FamiliaID',$formid,$conn,$traitids);
							$rr = getfamilies($famid,$conn,$showinvalid=TRUE);
							$row = $rr[0];
							echo "<td class='tdsmallbold'><i>".$row['Familia']."</i></td>";
						}
					}
				}
			}
		}
	return $oldvals;
}


function createthumb($name,$filename,$new_w,$new_h){
//createthumb() is called with the following parameters: 
//The name of the original image (if needed with folder name), the name of the thumbnail picture, and the dimensions.
//These lines get the information if gd is at least version 2.0 and check if the original image is a JPEG or PNG.
//Accordingly, a new image object is created called src_image.

	$system=explode('.',$name);
	//print_r($system);
	$system[1] = strtolower($system[1]);
	if (preg_match('/jpg|jpeg/',$system[1])){
		$src_img=imagecreatefromjpeg($name);
	}
	if (preg_match('/png/',$system[1])){
		$src_img=imagecreatefrompng($name);
	}

//These lines get the dimensions of the original image by using imageSX() and imageSY(), and calculate the dimensions of the thumbnail accordingly, keeping the correct aspect ratio. The desired dimensions are stored in thumb_w and thumb_h.
	//list($width,$height) = getimagesize($name);
	$old_x=imageSX($src_img);
	$old_y=imageSY($src_img);
	if ($old_x > $old_y) {
		$thumb_w=$new_w;
		$thumb_h=$old_y*($new_h/$old_x);
	}
	if ($old_x < $old_y) {
		$thumb_w=$old_x*($new_w/$old_y);
		$thumb_h=$new_h;
	}
	if ($old_x == $old_y) {
		$thumb_w=$new_w;
		$thumb_h=$new_h;
	}

//These lines create the image as a true colour version using ImageCreateTrueColor() and resize and copy the original image into the new thumbnail image, on the top left position.
	$dst_img=imagecreatetruecolor($thumb_w,$thumb_h);
	imagecopyresampled($dst_img,$src_img,0,0,0,0,$thumb_w,$thumb_h,$old_x,$old_y); 
	//imagecopyresized($dst_img,$src_img,0,0,0,0,$thumb_w,$thumb_h,$old_x,$old_y); 

//These lines check the file system extension of the original image and create the thumbnail accordingly. The thumbnail gets saved onto the server (by adding a filename to imagejpeg() or imagepng() function) and the two image objects get destroyed to free the memory.
	if (preg_match("/png/",$system[1]))
	{
		imagepng($dst_img,$filename); 
	} else {
		imagejpeg($dst_img,$filename); 
	}
	@imagedestroy($dst_img); 
	@imagedestroy($src_img); 
}


function hiddeninputs($arrayofvars) {
		if (is_array($arrayofvars)) {
			foreach ($arrayofvars as $key => $val) {
				if (!empty($val) && !empty($key)) {
					echo "
  <input type='hidden' name='$key' value='".$val."' />";
				}
			}
		}
}

function CompareTaggedTreeWithSample(
	$conn,
	$action='novacoleta-exe.php',
	$arrayofvars=NULL
	) 
		{

	$plantaid =$arrayofvars['plantaid'];
	$famid =$arrayofvars['famid'];
	$genusid =$arrayofvars['genusid'];
	$speciesid =$arrayofvars['speciesid'];
	$infraspid =$arrayofvars['infraspid'];
	$determinadorid =$arrayofvars['determinadorid'];
	$datadet =$arrayofvars['datadet'];
	$gazetteerid =$arrayofvars['gazetteerid'];
	$habitatid =$arrayofvars['habitatid'];
	$latitude =trim($arrayofvars['latdec']);
	$longitude =trim($arrayofvars['longdec']);
	$altitude =trim($arrayofvars['altitude']);

	$detaction = trim($arrayofvars['detaction']);
	$cooraction = trim($arrayofvars['cooraction']);
	$localaction = trim($arrayofvars['localaction']);
	$habaction = trim($arrayofvars['habaction']);

	if (empty($detaction) || empty($cooraction) || empty($localaction) || empty($habaction)) {
		//extrai dados da planta
		$qq = "SELECT * FROM Plantas JOIN Identidade USING(DetID) JOIN Tax_Especies ON Identidade.EspecieID=Tax_Especies.EspecieID JOIN Tax_Generos ON Tax_Especies.GeneroID=Tax_Generos.GeneroID JOIN Tax_Familias ON Tax_Generos.FamiliaID=Tax_Familias.FamiliaID WHERE PlantaID='".$plantaid."'";
		$rr = mysql_query($qq,$conn);
		$row= mysql_fetch_assoc($rr);
		$platitude = trim($row['Latitude']);
		$plongitude = trim($row['Longitude']);
		$paltitude = trim($row['Altitude']);
		$pgazetteerid = $row['ProcedenciaID'];
		$phabitatid = $row['HabitatID'];
		$pfamid = $row['FamiliaID'];
		$pgenusid = $row['GeneroID'];
		$pspeciesid = $row['EspecieID'];
		$pinfraspid = $row['InfraEspecieID'];
		$pdeterminadorid = $row['DetbyID'];
		$pdatadet = $row['DetDate'];
		$pdetid = $row['DetID'];

		$res = array();
		//se houver diferenca na identificacao
		$err=0;
		if ($famid!=$pfamid || $genusid!=$pgenusid || $speciesid!=$pspeciesid || $infraspid!=$pinfraspid) {

				$actname = trim(gettaxaname($infraspid,$speciesid,$genusid,$famid,$conn));
				$pname = trim(gettaxaname($pinfraspid,$pspeciesid,$pgenusid,$pfamid,$conn));

				if (empty($actname) && !empty($pname)) { //se a coleta nao tiver identificacao, pega a da planta
					$res = array_merge((array)$res,(array)array('determinadorid' => $pdeterminadorid,'datadet' => $pdatadet));
					$res = array_merge((array)$res,(array)array('infraspid' => $pinfraspid, 'speciesid' => $pspeciesid, 'genusid' => $pgenusid, 'famid' => $pfamid));
					$taxon=2;
				} elseif ($actname!=$pname) { //se forem diferentes entao pergunda
					$taxon=1;
				}
		}
		//se as coordenadas da planta forem diferentes
			//echo "aqui".$platitude."  ".$latitude."  ".$plongitude."  ".$longitude ."  ".$paltitude."  ".$altitude;

		if ($platitude!=$latitude  || $plongitude!=$longitude || $paltitude!=$altitude || $paltitude!=$altitude) {
			if (!empty($latitude)) {
				$colcoor++;
			}
			if (!empty($platitude)) {
				$pcoor++;
			}
			if (!empty($longitude)) {
				$colcoor++;
			}
			if (!empty($plongitude)) {
				$pcoor++;
			}
			if (!empty($altitude)) {
				$colcoor++;
			}
			if (!empty($paltitude)) {
				$pcoor++;
			}
			if ($pcoor>$colcoor) { //se a data planta for mais completo
				$coor='planta';
			} 
			if ($pcoor<$colcoor) { //se a da coleta for mais completa
				$coor='coleta';
			}
			//se estiver vazio o valor da coleta e nao o da planta, entao pega o da planta, ou se for identifico tambem pega
			if (empty($colcoor) && !empty($pcoor)) { 
				$res = array_merge((array)$res,(array)array('latdec' => $platitude, 'longdec' => $plongitude, 'altitude' => $paltitude));
			}
		}

		//se a localidade for diferente
		if ($pgazetteerid!=$gazetteerid) {
			if (empty($gazetteerid) && !empty($pgazetteerid)) { //se for vazio, pega o valor da planta
				$res = array_merge((array)$res,(array)array('gazetteerid' => $pgazetteerid));
				$local=2;
			} else {
				$local=1;
			}

		}


		//se o habitat for diferente
		if ($habitatid!=$phabitatid) {
			if ((empty($habitatid) || $habitatid==0) && !empty($phabitatid)) { //se for vazio, pega o valor da planta
				$res = array_merge((array)$res,(array)array('habitatid' => $phabitatid));
				$hab=2;
			} elseif ($phabitatid>0 && $habitatid>0) {
				$hab=1;
			}
		}

		//solicita acao caso haja diferenca entre valor da planta e valor da coleta

		if ($taxon>0 || $local>0 || $hab>0 || !empty($coor)) {


			//abre tabela do erro
			$detaction = trim($arrayofvars['detaction']);
			$cooraction = trim($arrayofvars['cooraction']);
			$localaction = trim($arrayofvars['localaction']);
			$habaction = trim($arrayofvars['habaction']);
			echo "
			<form action=$action method='post'>";
				$ll = $arrayofvars;
				$ll['detaction'] = NULL;
				$ll['cooraction'] = NULL;
				$ll['localaction'] = NULL;
				$ll['habaction'] = NULL;
				hiddeninputs($ll);	//hidden values for most variables

							print_r($$ll);

			$ii=0;
			if ($taxon>0 && empty($detaction)) {
				if ($ii==0) {
					echo "<br><table class='tdorangebg' align='center' cellpadding=\"4\" cellspacing=\"0\">
					<tr ><td align='center' colspan=100%><b>".GetLangVar('namewarning')."!</b></td></tr>";
				}
				$ii++;

				echo "<tr class='tdsmallbold'><td colspan=100%> ($ii) ".GetLangVar('erro21')." ".GetLangVar('nameselect').":</td>
				</tr>
				<tr class='tdsmalldescription'>
					<td align='center'><input type='radio' ";  
					if ($taxon==1) {echo " checked ";}
					echo "value='1' name='detaction' />
					</td>
					<td align='left'>".GetLangVar('messageaction1')." [$pname = $actname]</td>
				</tr>
				<tr class='tdsmalldescription'>
					<td align='center'><input type='radio'";  
					if ($taxon==2) {echo " checked ";}
					echo " value='2' name='detaction' />
					</td>
					<td align='left'>".GetLangVar('messageaction2')." [$actname = $pname]</td>
				</tr>
				<tr class='tdsmalldescription'>
					<td align='center'><input type='radio' value='3' name='detaction' /></td>
					<td align='left'>".GetLangVar('messageaction3')."</td>
				</tr>";
			} else {
				echo "<input type='hidden' name='detaction' value='$detaction' />";
			}
			if (!empty($coor) && empty($cooraction)) {
				if ($ii==0) {
					echo "<br><table class='tdorangebg' align='center' cellpadding=\"4\" cellspacing=\"0\">
					<tr ><td align='center' colspan=3><b>".GetLangVar('namewarning')."!</b></td></tr>";
				}
				$ii++;

				if ($coor=='planta') {
						echo "<tr class='tdsmallbold'><td colspan=100%> ($ii) ".GetLangVar('erro22')." (".GetLangVar('nametaggedplant')." + ".strtolower(GetLangVar('namecompleta')).") ".GetLangVar('nameselect').":</td></tr>";
				} else {
						echo "<tr class='tdsmallbold'><td colspan=100%> ($ii) ".GetLangVar('erro22')." (".strtolower(GetLangVar('namecoleta'))." + ".GetLangVar('namecompleta').") ".GetLangVar('nameselect').":</td></tr>";
				}
				echo "<tr class='tdsmalldescription'><td align='center'><input type='radio' ";  
					if ($coor=='coleta') {echo " checked ";}
					echo "  value='1' name='cooraction' /></td><td align='left'>".GetLangVar('messageaction1')."</td></tr>
					<tr class='tdsmalldescription'><td align='center'><input type='radio'  ";  
					if ($coor=='planta') {echo " checked ";}
					echo "  value='2' name='cooraction' /></td><td align='left'>".GetLangVar('messageaction2')."</td></tr>
						<tr class='tdsmalldescription'><td align='center'><input type='radio' value='3' name='cooraction' /></td><td align='left'>".GetLangVar('messageaction3')."</td></tr>
						";
			} else {
				echo "<input type='hidden' name='cooraction' value='$cooraction' />";
			}
			if ($local>0 && empty($localaction)) {
				if ($ii==0) {
					echo "<br><table class='tdorangebg' align='center' cellpadding=\"4\" cellspacing=\"0\">
					<tr ><td align='center' colspan=100%><b>".GetLangVar('namewarning')."!</b></td></tr>";
				}
				$ii++;
				echo "<tr class='tdsmallbold'><td colspan=100%> ($ii) ".GetLangVar('erro23')." ".GetLangVar('nameselect').":</td></tr>";
				echo "<tr class='tdsmalldescription'>
						<td align='center'>
							<input type='radio'   ";  
							if ($local==1) {echo " checked ";}
							echo "   value='1' name='localaction' />
						</td>
						<td align='left'>".GetLangVar('messageaction1')."</td></tr>
						<tr class='tdsmalldescription'><td align='center'><input type='radio' ";  
							if ($local==2) {echo " checked ";}
							echo " value='2' name='localaction' /></td><td align='left'>".GetLangVar('messageaction2')."</td></tr>
						<tr class='tdsmalldescription'><td align='center'><input type='radio' value='3' name='localaction' /></td><td align='left'>".GetLangVar('messageaction3')."</td></tr>
						";
			} else {
				echo "<input type='hidden' name='localaction' value='$localaction' />";
			}
			if ($hab>0 && empty($habaction)) {
				if ($ii==0) {
					echo "<br><table class='tdorangebg' align='center' cellpadding=\"3\" cellspacing=\"0\">
					<tr ><td align='center'  colspan=100%><b>".GetLangVar('namewarning')."!</b></td></tr>";
				}
				$ii++;
				echo "<tr class='tdsmallbold'><td  colspan=100%> ($ii) ".GetLangVar('erro24')." ".GetLangVar('nameselect').":</td></tr>";
				echo "<tr class='tdsmalldescription'><td align='center'><input type='radio' ";  
							if ($hab==1) {echo " checked ";}
							echo " value='1' name='habaction' /></td><td align='left'>".GetLangVar('messageaction1')."</td></tr>
						<tr class='tdsmalldescription'><td align='center'><input type='radio' ";  
							if ($hab==2) {echo " checked ";}
							echo " value='2' name='habaction' /></td><td align='left'>".GetLangVar('messageaction2')."</td></tr>
						<tr class='tdsmalldescription'><td align='center'><input type='radio' value='3' name='habaction' /></td><td align='left'>".GetLangVar('messageaction3')."</td></tr>
					";
			} else {
				echo "<input type='hidden' name='habaction' value='$habaction' />";
			}
			//fecha tabela de erros
			if ($ii>0) {
				echo "</td></tr>
					<tr ><td colspan=3 align='center'><input type='submit' value='".GetLangVar('namemudar')."' class='bsubmit' /></td></tr>
				</table>";
			}
				echo "</form>";
		}
		return($res);

		} //end if an action is empty
}

function CompareOldWithNewValues($tablename,$idcol,$id,$arrayofvalues,$conn) {
	$qq = "SELECT * FROM $tablename WHERE $idcol='".$id."'";
	//echo $qq;
	$rr = mysql_query($qq,$conn);
	$row = mysql_fetch_assoc($rr);
	$changed=0;
	foreach ($arrayofvalues as $key => $val) {
		$oldval = trim($row[$key]);
		if (!empty($oldval)) {
			$VV = $val;
		} else {
			$VV = trim($val);
		}
		//echo $key."\tOld".$oldval."\tNew".$val."<br>";
		if ($oldval!=$VV && (!empty($VV) || $VV>0)) {
			$changed++;
		}
	}
	return($changed);
}

//funcao check se coletas sao da mesma planta
function CheckSamplesConflictforPlants($gazetteerid,$habitatid,$latitude,$longitude,$altitude,$altmin,$altmax,$especimensids,$conn) {
		$ids = explode(";",$especimensids);
		foreach ($ids as $id) {
			$qq = "SELECT * FROM Especimenes JOIN Gazetteer USING(GazetteerID) 
		JOIN Municipio USING(MunicipioID) JOIN Province USING(ProvinceID)
	JOIN Country USING(CountryID) WHERE EspecimenID='".$id."'";
				$rr = mysql_query($qq,$conn);
				$nsample = mysql_numrows($rr);
				$row= mysql_fetch_assoc($rr);
				//$local = $row['Country']." ".$row['Province']." ".$row['Municipio']." (".$row['GazetteerTIPOtxt']." ".$row['Gazetteer'].")";
				$local = $row['Country']." ".$row['Province']." ".$row['Municipio']." (".$row['Gazetteer'].")";
				$localidades = array_merge((array)$localidades,(array)$local);

				$qq = "SELECT * FROM Especimenes WHERE EspecimenID='".$id."'";
				$rr = mysql_query($qq,$conn);
				$row= mysql_fetch_assoc($rr);
				$tdetid = $row['DetID'];
				$tdetset = getdetsetvar($tdetid,$conn);
				$nome = $tdetset['famid'];
				$nome = $nome."_".$tdetset['genusid'];
				$nome = $nome."_".$tdetset['speciesid'];
				$nome = $nome."_".$tdetset['infraspid'];
				$nomes = array_merge((array)$nomes,(array)$nome);

				$tgazetteerid= $row['GazetteerID'];

				$tlatitude = trim($row['Latitude']);
				$tlongitude = trim($row['Longitude']);
				$taltitude = trim($row['Altitude']);
				$taltmin = trim($row['AltitudeMin']);
				$taltmax = trim($row['AltitudeMax']);

				$coord = "Latitude: ".$tlatitude." Longitude: ".$tlongitude." Altitude: ".$taltitude." (".$tmin."-".$tmax.")";
				$coordenadas = array_merge((array)$coordenadas,(array)$coord);


				$thabitatid = $row['HabitatID'];
				$zzh = describehabitat($thabitatid,$img=FALSE,$conn); 
				//echo $thabitatid."\t".$zzh."<br>";
				$habitats	= array_merge((array)$habitats,(array)$zzh);

				$qq = "SELECT * FROM Especimenes JOIN Pessoas ON ColetorID=PessoaID WHERE EspecimenID='".$id."'";
				$rr = mysql_query($qq,$conn);
				$row= mysql_fetch_assoc($rr);
				$coleta = $row['Abreviacao']." ".$row['Number'];
				$coletas = array_merge((array)$coletas,(array)$coleta);

		}
		$nnomes = count(array_unique($nomes));
		$nlocais = count(array_unique($localidades));
		$ncoord = count(array_unique($coordenadas));
		$nhabitats = count(array_unique($habitats));

		if ($nnomes>1 || $nlocais>1 || $ncoord>1 || $nhabitats>1) {
			echo "<br>
					<table class='erro' align='center' width='90%'>
						<tr class='tdorangebg' align='center'><td><b>".GetLangVar('namewarning')."!</b></td></tr> 
						<tr class='tdformnotes'><td>".GetLangVar('messageconflitoentrecolecoes');
			if ($nnomes>1) {
				echo " <b>".strtolower(GetLangVar('nametaxonomy'))."</b>";
			} 
			if ($nlocais>1) {
				echo " <b>".strtolower(GetLangVar('namelocalidade'))."</b>";
			}
			if ($ncoord>1) {
				echo " <b>".strtolower(GetLangVar('namecoordenadas'))."</b>";
			}
			if ($nhabitats>1) {
				echo " <b>".strtolower(GetLangVar('namehabitat'))."</b>";
			}
			echo "</td></tr>
			<tr><td colspan=2>
			<table class='sortable autostripe' cellspacing='0' cellpadding='3' align='center' >
			<thead >
			<tr>
				<th align='center'>".GetLangVar('namecoleta')."</th>
				<th align='center'>".GetLangVar('nametaxonomy')."</th>
				<th align='center'>".GetLangVar('namelocalidade')."</th>
				<th align='center'>".GetLangVar('namecoordenadas')."</th>
				<th align='center'>".GetLangVar('namehabitat')."</th>
			</tr>
			</thead>
			<tbody>";
				$i=0;
				foreach ($coletas as $col) {
					echo "<tr class='tdsmalldescription'><td>".$col."</td>
								<td><i>".$nomes[$i]."</i></td>
								<td>".$localidades[$i]."</td>
								<td>".$coordenadas[$i]."</td>
								<td>".$habitats[$i]."</td>


								</tr>";
					$i++;
				}
			echo "</tbody></table>
			</td></tr></table>
			";
			$ok=FALSE;
		} else {
			$ok=TRUE;
		}
		return($ok);

}

function CompareSampleWithTaggedTreeSample(
	$especimenid,
	$conn,
	$action='planta-exe.php',
	$arrayofvars=NULL
	) {

	$pfamid= trim($arrayofvars['famid']);
	$pgenusid= trim($arrayofvars['genusid']);
	$pspeciesid= trim($arrayofvars['speciesid']);
	$pinfraspid= trim($arrayofvars['infraspid']);
	$pdeterminador= trim($arrayofvars['determinador']);
	$pdatadet= trim($arrayofvars['datadet']);
	$pgazetteerid= trim($arrayofvars['gazetteerid']);
	$procedenciaid= trim($arrayofvars['procedenciaid']);
	$phabitatid= trim($arrayofvars['habitatid']);
	$platitude= trim($arrayofvars['latdec']);
	$plongitude= trim($arrayofvars['longdec']);
	$paltitude= trim($arrayofvars['altitude']);

	$detaction = trim($arrayofvars['detaction']);
	$cooraction = trim($arrayofvars['cooraction']);
	$localaction = trim($arrayofvars['localaction']);
	$habaction = trim($arrayofvars['habaction']);

	if (empty($detaction) || empty($cooraction) || empty($localaction) || empty($habaction)) {
		//extrai dados da planta
		$qq = "SELECT * FROM Especimenes WHERE EspecimenID='".$especimenid."'";
		$rr = mysql_query($qq,$conn);
		$row= mysql_fetch_assoc($rr);
		$latitude = $row['Latitude'];
		$longitude = $row['Longitude'];
		$altitude = $row['Altitude'];
		$gazetteerid = $row['GazetteerID'];
		$habitatid = $row['HabitatID'];
		$detid = $row['DetID'];


		$qq = "SELECT * FROM Identidade WHERE DetID='".$detid."'";
		$rr = mysql_query($qq,$conn);
		$rwwo= mysql_fetch_assoc($rr);
		$famid = $rwwo['FamiliaID'];
		$genusid = $rwwo['GeneroID'];
		$speciesid = $rwwo['EspecieID'];
		$infraspid = $rwwo['InfraEspecieID'];
		$determinadorid = $rwwo['DetbyID'];
		$datadet = $rwwo['DetDate'];
		$detid = $rwwo['DetID'];

		$res = array();
		//se houver diferenca na identificacao
		$err=0;
			$actname = trim(gettaxaname($infraspid,$speciesid,$genusid,$famid,$conn));
			$pname = trim(gettaxaname($pinfraspid,$pspeciesid,$pgenusid,$pfamid,$conn));

			//echo "$actname, $pname";
			if (!empty($actname) && empty($pname)) { //se a planta nao tiver identificacao, pega a da coleta
					$res = array_merge((array)$res,(array)array('determinadorid' => $determinadorid,'datadet' => $datadet));
					$res = array_merge((array)$res,(array)array('infraspid' => $infraspid, 'speciesid' => $speciesid, 'genusid' => $genusid, 'famid' => $famid));
					$taxon=2;
			} elseif ($actname!=$pname) { //se forem diferentes entao pergunda
					$taxon=1;
			}
		//se as coordenadas da planta forem diferentes
		if ($platitude!=$latitude  || $plongitude!=$longitude || $paltitude!=$altitude || $paltitude!=$altitude) {
			if (!empty($latitude)) {
				$colcoor++;
			}
			if (!empty($platitude)) {
				$pcoor++;
			}
			if (!empty($longitude)) {
				$colcoor++;
			}
			if (!empty($plongitude)) {
				$pcoor++;
			}
			if (!empty($altitude)) {
				$colcoor++;
			}
			if (!empty($paltitude)) {
				$pcoor++;
			}
			if (!empty($altmin)) {
				$colcoor++;
			}
			if ($pcoor>$colcoor) { //se a data planta for mais completo
				$coor='planta';
			} 
			if ($pcoor<$colcoor) { //se a da coleta for mais completa
				$coor='coleta';
			} 
			$arraofv = array('latdec' => $latitude, 'longdec' => $longitude, 'altitude' => $altitude);
			//se estiver vazio o valor da planta e nao o da coleta, entao pega o da coleta, ou se for identifico tambem pega
			if (empty($coor) || (empty($pcoor) && !empty($colcoor)) || $cooraction==1) { 
				$res = array_merge((array)$res,(array)$arraofv);
			}
		}

		//se a localidade for diferente
		if ($arrayofvars['inexsitu']=='Exsitu') {
			if ($procedenciaid!=$gazetteerid) {
				if (!empty($gazetteerid) && empty($procedenciaid)) { //se for vazio, pega o valor da planta
					$res = array_merge((array)$res,(array)array('procedenciaid' => $gazetteerid));
					$local=2;
					//echo "$procedenciaid and $gazetteerid";
				} else {
					$local=1;
				}
			} 
		} else {
			if ($pgazetteerid!=$gazetteerid) {
				if (!empty($gazetteerid) && empty($pgazetteerid)) { //se for vazio, pega o valor da planta
					$res = array_merge((array)$res,(array)array('gazetteerid' => $gazetteerid));
					$local=2;
				} else {
					$local=1;
				}
			}
		}


		//se o habitat for diferente
		if ($habitatid!=$phabitatid) {
			if (!empty($habitatid) && empty($phabitatid)) { //se for vazio, pega o valor da planta
				$res = array_merge((array)$res,(array)array('habitatid' => $habitatid));
				$hab=2;
			} elseif (!empty($phabitatid)) {
				$hab=1;
			}
		}

		//solicita acao caso haja diferenca entre valor da planta e valor da coleta
		if ($taxon>0 || $local>0 || $hab>0 || !empty($coor)) {
			$resultado  = FALSE;
			echo "
				<form action=$action method='post'>";
					$ll = $arrayofvars;
					$ll['detaction'] = NULL;
					$ll['cooraction'] = NULL;
					$ll['localaction'] = NULL;
					$ll['habaction'] = NULL;
					@hiddeninputs($ll);	//hidden values for most variables
			$ii=0;
			if ($taxon>0 && empty($detaction)) {
				if ($ii==0) {
					echo "<br><table class='tdorangebg' align='center' cellpadding=\"3\" cellspacing=\"0\">
					<tr ><td align='center' colspan=3><b>".GetLangVar('namewarning')."!</b></td></tr>";
				}
				$ii++;

				echo "<tr class='tdsmallbold'><td>&nbsp;</td><td colspan=3> ($ii) ".GetLangVar('erro21')." ".GetLangVar('nameselect').":</td></tr>
				<tr class='tdsmalldescription'><td>&nbsp;</td><td align='center'><input type='radio' ";  
					if ($taxon==2) {echo " checked ";}
					echo "  value='1' name='detaction' /></td><td align='left'>".
				GetLangVar('messageaction1')." [$pname = $actname]</td></tr>
				<tr class='tdsmalldescription'><td>&nbsp;</td><td align='center'><input type='radio' ";  
					if ($taxon==1) {echo " checked ";}
					echo "value='2' name='detaction' /></td><td align='left'>".
				GetLangVar('messageaction2')." [$actname = $pname]</td></tr>
				<tr class='tdsmalldescription'><td>&nbsp;</td><td align='center'><input type='radio' value='3' name='detaction' /></td><td align='left'>".
				GetLangVar('messageaction3')."</td></tr>";
			} else {
				echo "<input type='hidden' name='detaction' value='$detaction' />";
			}
			if (!empty($coor) && empty($cooraction)) {
				if ($ii==0) {
					echo "<br><table class='tdorangebg' align='center' cellpadding=\"3\" cellspacing=\"0\">
					<tr ><td align='center' colspan=3><b>".GetLangVar('namewarning')."!</b></td></tr>";
				}
				$ii++;

				if ($coor=='planta') {
						echo "<tr class='tdsmallbold'><td colspan=3> ($ii) ".GetLangVar('erro22')." (".GetLangVar('nametaggedplant')." + ".GetLangVar('namecompleta').") ".GetLangVar('nameselect').":</td></tr>";
				} else {
						echo "<tr class='tdsmallbold'><td colspan=3> ($ii) ".GetLangVar('erro22')." (".GetLangVar('namecoleta')." + ".GetLangVar('namecompleta').") ".GetLangVar('nameselect').":</td></tr>";
				}
				echo "<tr class='tdsmalldescription'><td>&nbsp;</td><td align='center'><input type='radio' ";  
					if ($coor=='coleta') {echo " checked ";}
					echo "value='1' name='cooraction' /></td><td align='left'>".GetLangVar('messageaction1')."</td></tr>
						<tr class='tdsmalldescription'><td>&nbsp;</td><td align='center'><input type='radio' ";  
					if ($coor=='planta') {echo " checked ";}
					echo "value='2' name='cooraction' /></td><td align='left'>".GetLangVar('messageaction2')."</td></tr>
						<tr class='tdsmalldescription'><td>&nbsp;</td><td align='center'><input type='radio' value='3' name='cooraction' /></td><td align='left'>".GetLangVar('messageaction3')."</td></tr>";
			} else {
				echo "<input type='hidden' name='cooraction' value='$cooraction' />";
			}
			if ($local>0 && empty($localaction)) {
				if ($ii==0) {
					echo "<br><table class='tdorangebg' align='center' cellpadding=\"3\" cellspacing=\"0\">
					<tr ><td align='center' colspan=3><b>".GetLangVar('namewarning')."!</b></td></tr>";
				}
				$ii++;
				echo "<tr class='tdsmallbold'><td colspan=3> ($ii) ".GetLangVar('erro23')." ".GetLangVar('nameselect').":</td></tr>";
				echo "<tr class='tdsmalldescription'><td>&nbsp;</td><td align='center'><input type='radio' ";  
					if ($local==2) {echo " checked ";}
					echo "value='1' name='localaction' /></td><td align='left'>".GetLangVar('messageaction1')."</td></tr>
						<tr class='tdsmalldescription'><td>&nbsp;</td><td align='center'><input type='radio' ";  
					if ($local==1) {echo " checked ";}
					echo "value='2' name='localaction' /></td><td align='left'>".GetLangVar('messageaction2')."</td></tr>
						<tr class='tdsmalldescription'><td>&nbsp;</td><td align='center'><input type='radio' value='3' name='localaction' /></td><td align='left'>".GetLangVar('messageaction3')."</td></tr>";
			} else {
				echo "<input type='hidden' name='localaction' value='$localaction' />";
			}
			if ($hab>0 && empty($habaction)) {
				if ($ii==0) {
					echo "<br><table class='tdorangebg' align='center' cellpadding=\"3\" cellspacing=\"0\">
					<tr ><td align='center' colspan=3><b>".GetLangVar('namewarning')."!</b></td></tr>";
				}
				$ii++;
				echo "<tr class='tdsmallbold'><td colspan=3> ($ii) ".GetLangVar('erro24')." ".GetLangVar('nameselect').":</td></tr>";
				echo "<tr class='tdsmalldescription'><td>&nbsp;</td><td align='center'><input type='radio'  ";  
					if ($hab==2) {echo " checked ";}
					echo "value='1' name='habaction' /></td><td align='left'>".GetLangVar('messageaction1')."</td></tr>
						<tr class='tdsmalldescription'><td>&nbsp;</td><td align='center'><input type='radio'  ";  
					if ($hab==1) {echo " checked ";}
					echo "value='2' name='habaction' /></td><td align='left'>".GetLangVar('messageaction2')."</td></tr>
						<tr class='tdsmalldescription'><td>&nbsp;</td><td align='center'><input type='radio' value='3' name='habaction' /></td><td align='left'>".GetLangVar('messageaction3')."</td></tr>";
			} else {
				echo "<input type='hidden' name='habaction' value='$habaction' />";
			}
			//fecha tabela de erros
			if ($ii>0) {
				echo "</td></tr>
					<tr ><td colspan=3 align='center'><input type='submit' value='".GetLangVar('namemudar')."' class='bsubmit' /></td></tr>
				</table>";
			}
				echo "</form>";
		} 
			if ($ii==0) {
				return($res);
			} else {
				return FALSE;
			}
		}
}

function gettaxaids($nomeid,$conn) {
	$nn = explode("_",$nomeid);
	$tipo = $nn[0];
	$id = $nn[1];
	if ($tipo=='infspid') {
			$qq = "SELECT Tax_InfraEspecies.InfraEspecieID, Tax_Especies.EspecieID, Tax_Generos.GeneroID, Tax_Generos.FamiliaID FROM Tax_InfraEspecies JOIN Tax_Especies USING(EspecieID) JOIN Tax_Generos USING(GeneroID) WHERE InfraEspecieID='$id'";}
	if ($tipo=='speciesid') {
			$qq = "SELECT Tax_Especies.EspecieID, Tax_Generos.GeneroID, Tax_Generos.FamiliaID  FROM Tax_Especies JOIN Tax_Generos USING(GeneroID) WHERE EspecieID='$id'";}
	if ($tipo=='genusid') {
			$qq = "SELECT GeneroID,FamiliaID  FROM Tax_Generos WHERE GeneroID='$id'";}
	if ($tipo=='famid') {
			$qq = "SELECT FamiliaID FROM Tax_Familias WHERE FamiliaID='$id'";}
	$res = mysql_query($qq,$conn);
	$row = mysql_fetch_assoc($res);
	$famid = $row['FamiliaID'];
	$genusid = $row['GeneroID'];
	$speciesid = $row['EspecieID'];
	$infraspid = $row['InfraEspecieID'];
	$results = array($famid,$genusid,$speciesid,$infraspid);
	return $results;
}

function TaxonomySimple($all=true,$conn) {
	$qqarr = array();
	if ($all) { $tablename = 'TaxonomySimple'; } else { $tablename = 'TaxonomySimpleSearch';}
	echo "-----------------Gerando ".$tablename." ------------------<br />";
		$usid = $_SESSION['userid'];
		$qqq = "DROP TABLE ".$tablename;
		mysql_query($qqq,$conn);
		
		$tempname = 'temp_taxonomy_'.$usid;
		$qu = "DROP TABLE ".$tempname;
		mysql_query($qu,$conn);
	if ($all) {
		$qqarr[] =  "(SELECT Familia,CONCAT(Genero,' ',Especie,' ',InfraEspecieNivel,' ',InfraEspecie) as nome, CONCAT('infspid_',InfraEspecieID) as nomeid, InfraEspecieID as id, Tax_Generos.FamiliaID, Tax_Generos.GeneroID, Tax_Especies.EspecieID, InfraEspecieID   FROM Tax_InfraEspecies JOIN Tax_Especies USING(EspecieID) JOIN Tax_Generos USING(GeneroID) JOIN Tax_Familias USING(FamiliaID) )";
		$qqarr[] =  "(SELECT Familia, Familia as nome, CONCAT('famid_',FamiliaID) as nomeid, FamiliaID as id, FamiliaID, NULL as GeneroID, NULL as EspecieID, NULL as InfraEspecieID FROM Tax_Familias)";
		$qqarr[] =  "(SELECT  Familia, Genero as nome, CONCAT('genusid_',GeneroID) as nomeid, GeneroID as id, Tax_Familias.FamiliaID, GeneroID, NULL as EspecieID, NULL as InfraEspecieID  FROM Tax_Generos JOIN Tax_Familias USING(FamiliaID))";
		$qqarr[] =  "(SELECT Familia,CONCAT(Genero,' ',Especie) as nome, CONCAT('speciesid_',EspecieID) as nomeid, EspecieID as id, Tax_Generos.FamiliaID, Tax_Generos.GeneroID, EspecieID, NULL as InfraEspecieID    FROM Tax_Especies JOIN Tax_Generos USING(GeneroID) JOIN Tax_Familias USING(FamiliaID) )";
	} else {
		$qqarr[] =  "(SELECT Familia, CONCAT(Genero,' ',Especie,' ',InfraEspecieNivel,' ',InfraEspecie) as nome, CONCAT('infspid_',Identidade.InfraEspecieID) as nomeid,  InfraEspecieID as id, Tax_Generos.FamiliaID, Tax_Generos.GeneroID, Tax_Especies.EspecieID, Tax_InfraEspecies.InfraEspecieID   FROM Especimenes JOIN Identidade USING(DetID) JOIN Tax_InfraEspecies USING(InfraEspecieID) JOIN Tax_Especies ON Tax_InfraEspecies.EspecieID=Tax_Especies.EspecieID JOIN Tax_Generos ON Tax_Especies.GeneroID=Tax_Generos.GeneroID JOIN Tax_Familias ON Tax_Generos.FamiliaID=Tax_Familias.FamiliaID)";
		$qqarr[] =  "(SELECT Familia,Familia as nome, CONCAT('famid_',Identidade.FamiliaID) as nomeid,FamiliaID as id, Tax_Familias.FamiliaID, NULL as GeneroID, NULL as EspecieID, NULL as InfraEspecieID  FROM Especimenes JOIN Identidade USING(DetID) JOIN Tax_Familias USING(FamiliaID))";
		$qqarr[] =  "(SELECT Familia, Genero as nome, CONCAT('genusid_',Identidade.GeneroID) as nomeid, GeneroID as id,Tax_Familias.FamiliaID, Tax_Generos.GeneroID, NULL as EspecieID, NULL as InfraEspecieID  FROM Especimenes JOIN Identidade USING(DetID) JOIN Tax_Generos USING(GeneroID) JOIN Tax_Familias ON Tax_Generos.FamiliaID=Tax_Familias.FamiliaID)";
		$qqarr[] =  "(SELECT Familia,CONCAT(Genero,' ',Especie) as nome, CONCAT('speciesid_',Identidade.EspecieID) as nomeid, EspecieID as id, Tax_Generos.FamiliaID, Tax_Generos.GeneroID, Tax_Especies.EspecieID, NULL as InfraEspecieID    FROM Especimenes JOIN Identidade USING(DetID) JOIN Tax_Especies USING(EspecieID) JOIN Tax_Generos ON Tax_Especies.GeneroID=Tax_Generos.GeneroID JOIN Tax_Familias ON Tax_Generos.FamiliaID=Tax_Familias.FamiliaID) ";
		$qqu = "SELECT * FROM Plantas";
		$ru = mysql_query($qqu,$conn);
		$nru = mysql_numrows($ru);
		if ($nru>0) {
			$qqarr[] =  "(SELECT Familia,CONCAT(Genero,' ',Especie,' ',InfraEspecieNivel,' ',InfraEspecie) as nome, CONCAT('infspid_',Identidade.InfraEspecieID) as nomeid, Identidade.InfraEspecieID as id, Tax_Generos.FamiliaID, Tax_Generos.GeneroID, Tax_Especies.EspecieID, Tax_InfraEspecies.InfraEspecieID  FROM Plantas JOIN Identidade USING(DetID) JOIN Tax_InfraEspecies USING(InfraEspecieID) JOIN Tax_Especies ON Tax_InfraEspecies.EspecieID=Tax_Especies.EspecieID JOIN Tax_Generos ON Tax_Especies.GeneroID=Tax_Generos.GeneroID JOIN Tax_Familias ON Tax_Generos.FamiliaID=Tax_Familias.FamiliaID) ";
			$qqarr[] =  "(SELECT Familia,Familia as nome, CONCAT('famid_',Identidade.FamiliaID) as nomeid,  Identidade.FamiliaID as id,Tax_Familias.FamiliaID, NULL as GeneroID, NULL as EspecieID, NULL as InfraEspecieID   FROM Plantas JOIN Identidade USING(DetID) JOIN Tax_Familias USING(FamiliaID))";
			$qqarr[] =  "(SELECT Familia, Genero as nome, CONCAT('genusid_',Identidade.GeneroID) as nomeid, Identidade.GeneroID as id,Tax_Familias.FamiliaID, Tax_Generos.GeneroID, NULL as EspecieID, NULL as InfraEspecieID  FROM Plantas JOIN Identidade USING(DetID) JOIN Tax_Generos USING(GeneroID) JOIN Tax_Familias ON Tax_Generos.FamiliaID=Tax_Familias.FamiliaID)";
			$qqarr[] =  "(SELECT Familia,CONCAT(Genero,' ',Especie) as nome, CONCAT('speciesid_',Identidade.EspecieID) as nomeid, Identidade.EspecieID as id, Tax_Generos.FamiliaID, Tax_Generos.GeneroID, Tax_Especies.EspecieID, NULL as InfraEspecieID    FROM Plantas JOIN Identidade USING(DetID) JOIN Tax_Especies USING(EspecieID) JOIN Tax_Generos ON Tax_Especies.GeneroID=Tax_Generos.GeneroID JOIN Tax_Familias ON Tax_Generos.FamiliaID=Tax_Familias.FamiliaID)";
		}
	}
	$ii=0;
	foreach ($qqarr as $kk => $sql) {
		if ($ii==0) {
			$qbse = "CREATE TABLE ".$tempname." CHARACTER SET utf8 ENGINE InnoDB ".$sql;
		} else {
			$qbse = "INSERT INTO ".$tempname." (Familia, nome,nomeid,id,FamiliaID,GeneroID, EspecieID,InfraEspecieID) ".$sql;
		}
		$forkeyoff = "SET FOREIGN_KEY_CHECKS=0";
		mysql_query($forkeyoff,$conn);
		
		$rup = mysql_query($qbse,$conn);
		$forkeyoff = "SET FOREIGN_KEY_CHECKS=1";
		mysql_query($forkeyoff,$conn);
			//echo "<br />".$qbse."<br />";
		if ($rup) { echo "feito".$ii."<br />"; } else { echo "<br />".$qbse."<br />";}
		session_write_close();
		flush();
		$ii++;
	}
	$qq = "SELECT DISTINCT Familia,nome,nomeid, id, FamiliaID, GeneroID, EspecieID, InfraEspecieID FROM ".$tempname." ORDER BY Familia,nome";
	$qq = "CREATE TABLE ".$tablename." CHARACTER SET utf8 ENGINE InnoDB ".$qq;
	$rss = mysql_query($qq,$conn);
	//$sql = "ALTER TABLE ".$tablename." ADD nomeautor CHAR(255)";
	//mysql_query($sql,$conn);
	if ($rss) {
		echo "-----------------------Concluido -  tabela ".$tablename." criada com sucesso!-------------------<br />";
	} else {
		echo "erro:<br />".$qq."<br />";
	}
	
	$qu = "DROP TABLE ".$tempname;
	mysql_query($qu,$conn);
	$forkeyoff = "SET FOREIGN_KEY_CHECKS=0";
	@mysql_query($forkeyoff,$conn);
	$qrr = "ALTER TABLE `". $tablename."`  ADD `temp_taxonID` INT(10) UNSIGNED NOT NULL AUTO_INCREMENT PRIMARY KEY FIRST";
	 mysql_query($qrr,$conn);
	$qu = "CREATE INDEX Familia ON ".$tablename." (Familia)";
	mysql_query($qu,$conn);
	$qu = "CREATE INDEX nomeid ON ".$tablename." (nomeid)";
	mysql_query($qu,$conn);
	$qu = "CREATE INDEX nome ON ".$tablename." (nome)";
	mysql_query($qu,$conn);
	///CREATE CONSTRAINT
	$qrr = "ALTER TABLE `". $tablename."` CHANGE `FamiliaID` `FamiliaID` INT(10) UNSIGNED NULL DEFAULT NULL";
	 mysql_query($qrr,$conn);
	$qrr =  "UPDATE `". $tablename."` SET `FamiliaID`= NULL WHERE `FamiliaID`=0";
	mysql_query($qrr,$conn);
	$qrr =  "ALTER TABLE `". $tablename."` DROP INDEX `FamiliaID`, ADD INDEX `FamiliaID` (`FamiliaID`) COMMENT ''  ";
	mysql_query($qrr,$conn);
	$qrr  = "ALTER TABLE `". $tablename."` ADD FOREIGN KEY ( `FamiliaID` ) REFERENCES `Tax_Familias` ( `FamiliaID` )";
	$rd = mysql_query($qrr,$conn);

	$qrr = "ALTER TABLE `". $tablename."` CHANGE `GeneroID` `GeneroID` INT(10) UNSIGNED NULL DEFAULT NULL";
	 mysql_query($qrr,$conn);
	$qrr =  "UPDATE `". $tablename."` SET `GeneroID`= NULL WHERE `GeneroID`=0";
	mysql_query($qrr,$conn);
	$qrr =  "ALTER TABLE `". $tablename."` DROP INDEX `GeneroID`, ADD INDEX `GeneroID` (`GeneroID`) COMMENT ''  ";
	mysql_query($qrr,$conn);
	$qrr  = "ALTER TABLE `". $tablename."` ADD FOREIGN KEY ( `GeneroID` ) REFERENCES `Tax_Generos` ( `GeneroID` )";
	$rd = mysql_query($qrr,$conn);

	$qrr = "ALTER TABLE `". $tablename."` CHANGE `EspecieID` `EspecieID` INT(10) UNSIGNED NULL DEFAULT NULL";
	 mysql_query($qrr,$conn);
	$qrr =  "UPDATE `". $tablename."` SET `EspecieID`= NULL WHERE `EspecieID`=0";
	mysql_query($qrr,$conn);
	$qrr =  "ALTER TABLE `". $tablename."` DROP INDEX `EspecieID`, ADD INDEX `EspecieID` (`EspecieID`) COMMENT ''  ";
	mysql_query($qrr,$conn);
	$qrr  = "ALTER TABLE `". $tablename."` ADD FOREIGN KEY ( `EspecieID` ) REFERENCES `Tax_Especies` ( `EspecieID` )";
	$rd = mysql_query($qrr,$conn);

	$qrr = "ALTER TABLE `". $tablename."` CHANGE `InfraEspecieID` `InfraEspecieID` INT(10) UNSIGNED NULL DEFAULT NULL";
	 mysql_query($qrr,$conn);
	$qrr =  "UPDATE `". $tablename."` SET `InfraEspecieID`= NULL WHERE `InfraEspecieID`=0";
	mysql_query($qrr,$conn);
	$qrr =  "ALTER TABLE `". $tablename."` DROP INDEX `InfraEspecieID`, ADD INDEX `InfraEspecieID` (`InfraEspecieID`) COMMENT ''  ";
	mysql_query($qrr,$conn);
	$qrr  = "ALTER TABLE `". $tablename."` ADD FOREIGN KEY ( `InfraEspecieID` ) REFERENCES `Tax_InfraEspecies` ( `InfraEspecieID` )";
	$rd = mysql_query($qrr,$conn);
	
	$forkeyoff = "SET FOREIGN_KEY_CHECKS=1";
	@mysql_query($forkeyoff,$conn);
}

function TaxonomySimpleInsert($idd,$tableref,$conn) {
	$tablename = 'TaxonomySimple'; 
	if ($tableref=='infspid') {
		$qqarr = "SELECT Familia,CONCAT(Genero,' ',Especie,' ',InfraEspecieNivel,' ',InfraEspecie) as nome, CONCAT('infspid_',InfraEspecieID) as nomeid, InfraEspecieID as id, Tax_Generos.FamiliaID, Tax_Generos.GeneroID, Tax_Especies.EspecieID, InfraEspecieID   FROM Tax_InfraEspecies JOIN Tax_Especies USING(EspecieID) JOIN Tax_Generos USING(GeneroID) JOIN Tax_Familias USING(FamiliaID) WHERE Tax_InfraEspecies.InfraEspecieID=".$idd;
	} 
	if ($tableref=='speciesid') {
		$qqarr = "SELECT Familia,CONCAT(Genero,' ',Especie) as nome, CONCAT('speciesid_',EspecieID) as nomeid, EspecieID as id, Tax_Generos.FamiliaID, Tax_Generos.GeneroID, EspecieID, NULL as InfraEspecieID    FROM Tax_Especies JOIN Tax_Generos USING(GeneroID) JOIN Tax_Familias USING(FamiliaID)  WHERE Tax_Especies.EspecieID=".$idd;
	} 
	if ($tableref=='genusid') {
		$qqarr = "SELECT  Familia, Genero as nome, CONCAT('genusid_',GeneroID) as nomeid, GeneroID as id, Tax_Familias.FamiliaID, GeneroID, NULL as EspecieID, NULL as InfraEspecieID  FROM Tax_Generos JOIN Tax_Familias USING(FamiliaID)  WHERE Tax_Generos.GeneroID=".$idd;
	} 
	if ($tableref=='famid') {
		$qqarr = "SELECT Familia, Familia as nome, CONCAT('famid_',FamiliaID) as nomeid, FamiliaID as id, FamiliaID, NULL as GeneroID, NULL as EspecieID, NULL as InfraEspecieID FROM Tax_Familias WHERE Tax_Familias.FamiliaID=".$idd;
	} 
	
	$qbse = "INSERT INTO ".$tablename." (Familia, nome,nomeid,id,FamiliaID,GeneroID, EspecieID,InfraEspecieID) (".$qqarr.")";
	
	$forkeyoff = "SET FOREIGN_KEY_CHECKS=0";
	mysql_query($forkeyoff,$conn);
	$rup = mysql_query($qbse,$conn);
	//echo $qbse."<br />";
	$forkeyoff = "SET FOREIGN_KEY_CHECKS=1";
	mysql_query($forkeyoff,$conn);
}

function TaxonomySimpleNew($all=true,$conn) {
	if ($all) { $tablename = 'TaxonomySimple'; } else { $tablename = 'TaxonomySimpleSearch';}
		$usid = $_SESSION['userid'];
		$tempname = 'temp_taxonomy_'.$usid;
		$qqq = "DROP TABLE ".$tablename;
			mysql_query($qqq,$conn);
	if ($all) {
		$qq	=	"(SELECT Familia as nome, Familia, CONCAT('famid_',FamiliaID) as nomeid FROM Tax_Familias)";
		$qq = $qq." UNION (SELECT Genero as nome, Familia, CONCAT('genusid_',GeneroID) as nomeid FROM Tax_Generos JOIN Tax_Familias USING(FamiliaID))";
		$qq = $qq." UNION (SELECT CONCAT(Genero,' ',Especie) as nome,  Familia, CONCAT('speciesid_',EspecieID) as nomeid  FROM Tax_Especies JOIN Tax_Generos USING(GeneroID) JOIN Tax_Familias USING(FamiliaID))";
		$qq = $qq." UNION (SELECT CONCAT(Genero,' ',Especie,' ',InfraEspecieNivel,' ',InfraEspecie) as nome,  Familia, CONCAT('infspid_',InfraEspecieID) as nomeid  FROM Tax_InfraEspecies JOIN Tax_Especies USING(EspecieID) JOIN Tax_Generos USING(GeneroID) JOIN Tax_Familias USING(FamiliaID))";
	} else {
		$qq	= "(SELECT Familia as nome, Familia, CONCAT('famid_',Identidade.FamiliaID) as nomeid FROM Especimenes JOIN Identidade USING(DetID) JOIN Tax_Familias USING(FamiliaID))";
		$qq = $qq." UNION (SELECT Genero as nome,  Familia, CONCAT('genusid_',Identidade.GeneroID) as nomeid FROM Especimenes JOIN Identidade USING(DetID) JOIN Tax_Generos USING(GeneroID) JOIN Tax_Familias ON Tax_Familias.FamiliaID=Tax_Generos.FamiliaID)";
		$qq = $qq." UNION (SELECT CONCAT(Genero,' ',Especie) as nome,  Familia, CONCAT('speciesid_',Identidade.EspecieID) as nomeid FROM Especimenes JOIN Identidade USING(DetID) JOIN Tax_Especies USING(EspecieID) JOIN Tax_Generos ON Tax_Especies.GeneroID=Tax_Generos.GeneroID  JOIN Tax_Familias ON Tax_Familias.FamiliaID=Tax_Generos.FamiliaID)";
		$qq = $qq." UNION (SELECT CONCAT(Genero,' ',Especie,' ',InfraEspecieNivel,' ',InfraEspecie) as nome,  Familia, CONCAT('infspid_',Identidade.InfraEspecieID) as nomeid FROM Especimenes JOIN Identidade USING(DetID) JOIN Tax_InfraEspecies USING(InfraEspecieID) JOIN Tax_Especies ON Tax_InfraEspecies.EspecieID=Tax_Especies.EspecieID JOIN Tax_Generos ON Tax_Especies.GeneroID=Tax_Generos.GeneroID  JOIN Tax_Familias ON Tax_Familias.FamiliaID=Tax_Generos.FamiliaID)";

		$qqu = "SELECT * FROM Plantas";
		$ru = mysql_query($qqu,$conn);
		$nru = mysql_numrows($ru);
		if ($nru>0) {
			$qq	= $qq." UNION (SELECT Familia as nome, Familia, CONCAT('famid_',Identidade.FamiliaID) as nomeid FROM Plantas JOIN Identidade USING(DetID) JOIN Tax_Familias USING(FamiliaID))";
			$qq = $qq." UNION (SELECT Genero as nome,  Familia, CONCAT('genusid_',Identidade.GeneroID) as nomeid FROM Plantas JOIN Identidade USING(DetID) JOIN Tax_Generos USING(GeneroID) JOIN Tax_Familias ON Tax_Familias.FamiliaID=Tax_Generos.FamiliaID)";
			$qq = $qq." UNION (SELECT CONCAT(Genero,' ',Especie) as nome,  Familia, CONCAT('speciesid_',Identidade.EspecieID) as nomeid FROM Plantas JOIN Identidade USING(DetID) JOIN Tax_Especies USING(EspecieID) JOIN Tax_Generos ON Tax_Especies.GeneroID=Tax_Generos.GeneroID JOIN Tax_Familias ON Tax_Familias.FamiliaID=Tax_Generos.FamiliaID)";
			$qq = $qq." UNION (SELECT CONCAT(Genero,' ',Especie,' ',InfraEspecieNivel,' ',InfraEspecie) as nome,  Familia, CONCAT('infspid_',Identidade.InfraEspecieID) as nomeid FROM Plantas JOIN Identidade USING(DetID) JOIN Tax_InfraEspecies USING(InfraEspecieID) JOIN Tax_Especies ON Tax_InfraEspecies.EspecieID=Tax_Especies.EspecieID JOIN Tax_Generos ON Tax_Especies.GeneroID=Tax_Generos.GeneroID JOIN Tax_Familias ON Tax_Familias.FamiliaID=Tax_Generos.FamiliaID)";
		}
	}
	$forkeyoff = "SET FOREIGN_KEY_CHECKS=0";
	@mysql_unbuffered_query($forkeyoff,$conn);
	$qu = "DROP TABLE ".$tempname;
	mysql_query($qu,$conn);
	$qqq = "CREATE TABLE ".$tempname." CHARACTER SET utf8 ENGINE InnoDB ".$qq;
	mysql_query($qqq,$conn);
	$qq = "SELECT DISTINCT nome, Familia,nomeid FROM ".$tempname." ORDER BY nome";
	$qqq = "CREATE TABLE ".$tablename." CHARACTER SET utf8 ENGINE InnoDB ".$qq;
	mysql_query($qqq,$conn);
	$qu = "DROP TABLE ".$tempname;
	mysql_query($qu,$conn);
	$qu = "CREATE INDEX nomeid ON ".$tablename." (nomeid)";
	mysql_query($qu,$conn);
	$qu = "CREATE INDEX nome ON ".$tablename." (nome)";
	mysql_query($qu,$conn);
	$forkeyoff = "SET FOREIGN_KEY_CHECKS=1";
	@mysql_unbuffered_query($forkeyoff,$conn);
}

function LocalitySimpleBrasil($gspoints=false,$conn) { 
		$sessiondate = $_SESSION['sessiondate'];
		$tablename = 'LocalitySimpleBrasil';
		$qqq = "DROP TABLE ".$tablename;
			mysql_query($qqq,$conn);


		$qq = "(SELECT Country as nome, Country as searchname, ' ' as lat, ' ' as logitude, ' ' as alt, CONCAT('paisid_',CountryID) as nomeid, '".$sessiondate."' as dataupdated FROM Country WHERE Country LIKE 'Brasil')";
			$qq = $qq." UNION (SELECT CONCAT(Province,' - ',UPPER(Country)) as nome,Province as searchname, ' ' as lat, ' ' as logitude, ' ' as alt, CONCAT('provinceid_',ProvinceID)  as nomeid, '".$sessiondate."' as dataupdated FROM Province JOIN Country USING(CountryID) WHERE Country LIKE 'Brasil')";
			$qq = $qq." UNION (SELECT CONCAT(Municipio,' - ',Province,' - ',UPPER(Country)) as nome, Municipio as searchname,Municipio.Latitude as lat, Municipio.Longitude as logitude, ' ' as alt, CONCAT('municipioid_',MunicipioID) as nomeid, '".$sessiondate."' as dataupdated FROM Municipio JOIN Province USING(ProvinceID) JOIN Country USING(CountryID) WHERE Country LIKE 'Brasil')";
			$qq = $qq." UNION (SELECT CONCAT(PathName,' - ',Municipio,' - ',Province,' - ',UPPER(Country))  as nome, PathName as searchname, Gazetteer.Latitude as lat, Gazetteer.Longitude as logitude, Altitude as alt, CONCAT('gazetteerid_',GazetteerID) as nomeid, '".$sessiondate."' as dataupdated  FROM Gazetteer JOIN Municipio USING(MunicipioID) JOIN Province USING(ProvinceID) JOIN Country USING(CountryID) WHERE Country LIKE 'Brasil')";
			if ($gpspoints) {
				$qq .= " UNION (SELECT CONCAT('GPSpt-',gps.Name,' --',gaz.PathName,' ',Municipio,' ',Province,' ',Country) as nome, gps.Name as searchname, gps.PointID as nomeid,'".$sessiondate."' as dataupdated FROM GPS_DATA as gps JOIN Gazetteer as gaz USING(GazetteerID) JOIN Municipio  USING(MunicipioID) JOIN Province USING(ProvinceID) JOIN Country USING(CountryID) WHERE gps.Type='Waypoint' AND WHERE Country LIKE 'Brasil')";
			}
			$forkeyoff = "SET FOREIGN_KEY_CHECKS=0";
			@mysql_unbuffered_query($forkeyoff,$conn);
			$qq = "CREATE TABLE ".$tablename." CHARACTER SET utf8 ENGINE InnoDB ".$qq;
			mysql_query($qq,$conn);
			//$qq = "DROP TABLE ".$tempname;
			//mysql_query($qq,$conn);
			//$qq = "CREATE TABLE ".$tempname." SELECT * FROM ".$tablename." ORDER BY nome ASC";
			//mysql_query($qq,$conn);
			$qu = "CREATE INDEX nomeid ON ".$tablename." (nomeid)";
			mysql_query($qu,$conn);
			$qu = "CREATE INDEX nome ON ".$tablename." (nome (100))";
			mysql_query($qu,$conn);
			$qu = "CREATE INDEX searchname ON ".$tablename." (searchname (100))";
			mysql_query($qu,$conn);
			$forkeyoff = "SET FOREIGN_KEY_CHECKS=1";
			@mysql_unbuffered_query($forkeyoff,$conn);
	}

function LocalitySimple($gpspoints=FALSE,$all=TRUE,$conn) { 
		$sessiondate = $_SESSION['sessiondate'];
		$usid = $_SESSION['userid'];
		$tempname = 'temp_locality_'.$usid;
		if ($all) { $tablename = 'LocalitySimple'; } else { $tablename = 'LocalitySimpleSearch';}
		
		echo "-----------------Gerando ".$tablename." ------------------<br />";
		session_write_close();
		flush();
		
		$qqq = "DROP TABLE ".$tablename;
		@mysql_query($qqq,$conn);
		
		$tempname = 'temp_localsimples_'.$usid;
		$qu = "DROP TABLE ".$tempname;
		@mysql_query($qu,$conn);

		$qarry = array();
		if ($all) {
			$qarry[] = "SELECT Country as nome, Country as searchname, NULL as lat, NULL as logitude, NULL as alt, CONCAT('paisid_',CountryID) as nomeid, CURDATE() as dataupdated FROM Country";
			$qarry[] = "SELECT CONCAT(Province,' - ',UPPER(Country)) as nome, Province as searchname, NULL as lat, NULL as logitude, NULL as alt, CONCAT('provinceid_',ProvinceID)  as nomeid, CURDATE() as dataupdated FROM Province JOIN Country USING(CountryID)";
			$qarry[] = "SELECT CONCAT(Municipio,' - ',Province,' - ',UPPER(Country)) as nome, Municipio as searchname,Municipio.Latitude as lat, Municipio.Longitude as logitude, NULL as alt, CONCAT('municipioid_',MunicipioID) as nomeid, CURDATE() as dataupdated FROM Municipio JOIN Province USING(ProvinceID) JOIN Country USING(CountryID)";
			$qarry[] = "SELECT CONCAT(Gazetteer,' - ',TRIM(REPLACE(PathName,Gazetteer, '')),' - ',Municipio,' - ',Province,' - ',UPPER(Country))  as nome,  PathName as searchname,  getlocallatlong(0, Gazetteer.GazetteerID, Gazetteer.MunicipioID, 1)  AS lat, getlocallatlong(0, Gazetteer.GazetteerID, Gazetteer.MunicipioID, 0)  AS logitude,  NULL as alt,  CONCAT('gazetteerid_',GazetteerID) as nomeid, CURDATE()  as dataupdated  FROM Gazetteer JOIN Municipio USING(MunicipioID) JOIN Province USING(ProvinceID) JOIN Country USING(CountryID)";
			if ($gpspoints) {
				$qarry[] = "SELECT CONCAT('GPSpt-',gps.Name,' -',TRIM(REPLACE(gaz.PathName, gaz.Gazetteer, '')),' - ',Municipio,' - ',Province,' - ',Country) as nome, gps.Name as searchname, gps.Latitude as lat, gps.Longitude as logitude, CONCAT('gpsid_', gps.PointID) as nomeid, CURDATE()  as dataupdated FROM GPS_DATA as gps JOIN Gazetteer as gaz USING(GazetteerID) JOIN Municipio  USING(MunicipioID) JOIN Province USING(ProvinceID) JOIN Country USING(CountryID) WHERE gps.Type='Waypoint'";
			}
		} 
		else {
		//country by gazetteer from especimenes
		$qarry[] = "SELECT Country as nome, Country as searchname, NULL as lat, NULL as logitude, NULL as alt, CONCAT('paisid_',crt.CountryID) as nomeid, CURDATE()  as dataupdated FROM Especimenes JOIN Gazetteer as gaz USING(GazetteerID) JOIN Municipio as muni ON gaz.MunicipioID=muni.MunicipioID JOIN Province as prov ON prov.ProvinceID=muni.ProvinceID JOIN Country as crt ON crt.CountryID=prov.CountryID";
		//countries directly linked to especimens (rare old stuff)
		$qarry[] = "SELECT Country as nome, Country as searchname, NULL as lat, NULL as logitude, NULL as alt, CONCAT('paisid_',crt.CountryID) as nomeid, CURDATE()  as dataupdated FROM Especimenes JOIN Country as crt ON crt.CountryID=Especimenes.CountryID WHERE (Especimenes.GazetteerID=0 OR Especimenes.GazetteerID IS NULL) AND (Especimenes.GPSPointID=0 OR Especimenes.GPSPointID IS NULL)";
		//majorarea by especimenes 
		$qarry[] = "SELECT CONCAT(Province,' - ',UPPER(Country)) as nome,Province as searchname, NULL as lat, NULL as logitude, NULL as alt,  CONCAT('provinceid_',prov.ProvinceID)  as nomeid, CURDATE()  as dataupdated FROM Especimenes JOIN Gazetteer as gaz USING(GazetteerID) JOIN Municipio as muni ON gaz.MunicipioID=muni.MunicipioID JOIN Province as prov ON prov.ProvinceID=muni.ProvinceID JOIN Country as crt ON crt.CountryID=prov.CountryID";   
		//majorarea directly linked to especimens (rare old stuff)
		$qarry[] = "SELECT CONCAT(Province,' - ',UPPER(Country)) as nome,Province as searchname, NULL as lat, NULL as logitude, NULL as alt,  CONCAT('provinceid_',prov.ProvinceID)  as nomeid, CURDATE()  as dataupdated FROM Especimenes JOIN Province as prov ON prov.ProvinceID=Especimenes.ProvinceID JOIN Country as crt ON crt.CountryID=prov.CountryID WHERE (Especimenes.GazetteerID=0 OR Especimenes.GazetteerID IS NULL) AND (Especimenes.GPSPointID=0 OR Especimenes.GPSPointID IS NULL)";
		//minor area by especimenes 
		$qarry[] = "SELECT CONCAT(muni.Municipio,' - ',prov.Province,' - ',UPPER(crt.Country)) as nome, muni.Municipio as searchname,muni.Latitude as lat, muni.Longitude as logitude, NULL as alt, CONCAT('municipioid_',muni.MunicipioID) as nomeid, CURDATE()  as dataupdated FROM Especimenes JOIN Gazetteer as gaz USING(GazetteerID) JOIN Municipio as muni ON gaz.MunicipioID=muni.MunicipioID JOIN Province as prov ON prov.ProvinceID=muni.ProvinceID JOIN Country as crt ON crt.CountryID=prov.CountryID";
		//directly linked to especimens (rare old stuff)
		$qarry[] = "SELECT CONCAT(Municipio,' - ',Province,' - ',UPPER(Country)) as nome, Municipio as searchname,muni.Latitude as lat, muni.Longitude as logitude, NULL as alt, CONCAT('municipioid_',muni.MunicipioID) as nomeid, CURDATE()  as dataupdated FROM Especimenes JOIN Municipio as muni ON Especimenes.MunicipioID=muni.MunicipioID JOIN Province as prov ON prov.ProvinceID=muni.ProvinceID JOIN Country as crt ON crt.CountryID=prov.CountryID WHERE (Especimenes.GazetteerID=0 OR Especimenes.GazetteerID IS NULL) AND (Especimenes.GPSPointID=0 OR Especimenes.GPSPointID IS NULL)";

		//gazetteers directly connected to especimenes (LEVEL 1)
		$qarry[] = "SELECT CONCAT(gaz.Gazetteer,' - ', TRIM(REPLACE(gaz.PathName, gaz.Gazetteer, '')),' - ',Municipio,' - ',Province,' - ',UPPER(Country))  as nome, PathName as searchname, getlocallatlong(0, gaz.GazetteerID, gaz.MunicipioID, 1)  AS lat,
getlocallatlong(0, gaz.GazetteerID, gaz.MunicipioID, 0)  AS logitude, NULL as alt, CONCAT('gazetteerid_',gaz.GazetteerID) as nomeid, CURDATE()  as dataupdated  FROM Especimenes JOIN Gazetteer as gaz USING(GazetteerID) JOIN Municipio as muni ON gaz.MunicipioID=muni.MunicipioID JOIN Province as prov ON prov.ProvinceID=muni.ProvinceID JOIN Country as crt ON crt.CountryID=prov.CountryID";
		//gazetteers directly connected to especimenes (LEVEL 2)
		$qarry[] = "SELECT CONCAT(gaz2.Gazetteer,' - ', TRIM(REPLACE(gaz2.PathName, gaz2.Gazetteer, '')) ,' - ',Municipio,' - ',Province,' - ',UPPER(Country))  as nome, gaz2.PathName as searchname,  getlocallatlong(0, gaz2.GazetteerID, gaz2.MunicipioID, 1)  AS lat,
getlocallatlong(0, gaz2.GazetteerID, gaz2.MunicipioID, 0)  AS logitude, NULL as alt,  CONCAT('gazetteerid_',gaz2.GazetteerID) as nomeid, CURDATE()  as dataupdated  FROM Especimenes JOIN Gazetteer as gaz ON Especimenes.GazetteerID=gaz.GazetteerID  JOIN Gazetteer as gaz2 ON gaz2.GazetteerID=gaz.ParentID JOIN Municipio as muni ON gaz.MunicipioID=muni.MunicipioID JOIN Province as prov ON prov.ProvinceID=muni.ProvinceID JOIN Country as crt ON crt.CountryID=prov.CountryID";
		//gazetteers directly connected to especimenes (LEVEL 3)
		$qarry[] = "SELECT  CONCAT(gaz3.Gazetteer,' - ', TRIM(REPLACE(gaz3.PathName, gaz3.Gazetteer, '')),' - ',Municipio,' - ',Province,' - ',UPPER(Country))  as nome, gaz3.PathName as searchname, getlocallatlong(0, gaz3.GazetteerID, gaz3.MunicipioID, 1)  AS lat,
getlocallatlong(0, gaz3.GazetteerID, gaz3.MunicipioID, 0)  AS logitude, NULL as alt,  CONCAT('gazetteerid_',gaz3.GazetteerID) as nomeid, CURDATE()  as dataupdated  FROM Especimenes JOIN Gazetteer as gaz ON Especimenes.GazetteerID=gaz.GazetteerID JOIN Gazetteer as gaz2 ON gaz2.GazetteerID=gaz.ParentID JOIN Gazetteer as gaz3 ON gaz3.GazetteerID=gaz2.ParentID JOIN Municipio as muni ON gaz.MunicipioID=muni.MunicipioID JOIN Province as prov ON prov.ProvinceID=muni.ProvinceID JOIN Country as crt ON crt.CountryID=prov.CountryID";
		
		///ALL THE SAME FOR  GPSDATA
		//paises ligados com amostras por dados GPS
		$qarry[] = "SELECT  Country as nome, Country as searchname, NULL as lat, NULL as logitude, NULL as alt,  CONCAT('paisid_',crt.CountryID) as nomeid, CURDATE()  as dataupdated FROM Especimenes JOIN GPS_DATA as gps ON GPSPointID=gps.PointID JOIN Gazetteer as gaz ON gaz.GazetteerID=gps.GazetteerID JOIN Municipio as muni ON gaz.MunicipioID=muni.MunicipioID JOIN Province as prov ON prov.ProvinceID=muni.ProvinceID JOIN Country as crt ON crt.CountryID=prov.CountryID";
		//provincias ligadas com amostras por dados de gps
		$qarry[] = "SELECT  CONCAT(Province,' - ',UPPER(Country)) as nome,Province as searchname, NULL as lat, NULL as logitude, NULL as alt, CONCAT('provinceid_',prov.ProvinceID)  as nomeid, CURDATE()  as dataupdated FROM Especimenes JOIN GPS_DATA as gps ON GPSPointID=gps.PointID JOIN Gazetteer as gaz ON gaz.GazetteerID=gps.GazetteerID JOIN Municipio as muni ON gaz.MunicipioID=muni.MunicipioID JOIN Province as prov ON prov.ProvinceID=muni.ProvinceID JOIN Country as crt ON crt.CountryID=prov.CountryID";
		//municipios ligados com amostras
		$qarry[] = "SELECT  CONCAT(Municipio,' - ',Province,' - ',UPPER(Country)) as nome, Municipio as searchname,muni.Latitude as lat, muni.Longitude as logitude, NULL as alt, CONCAT('municipioid_',muni.MunicipioID) as nomeid,CURDATE()  as dataupdated FROM Especimenes JOIN GPS_DATA as gps ON GPSPointID=gps.PointID JOIN Gazetteer as gaz ON gaz.GazetteerID=gps.GazetteerID JOIN Municipio as muni ON gaz.MunicipioID=muni.MunicipioID JOIN Province as prov ON prov.ProvinceID=muni.ProvinceID JOIN Country as crt ON crt.CountryID=prov.CountryID";
		//GAZETTEER ligados com amostras por dados GPS (LEVEL 1)
		$qarry[] = "SELECT DISTINCT CONCAT(gaz.Gazetteer,' - ', TRIM(REPLACE(gaz.PathName, gaz.Gazetteer, '')),' - ',Municipio,' - ',Province,' - ',UPPER(Country))  as nome, PathName as searchname, getlocallatlong(0, gaz.GazetteerID, gaz.MunicipioID, 1)  AS lat,
getlocallatlong(0, gaz.GazetteerID, gaz.MunicipioID, 0)  AS logitude, NULL as alt, CONCAT('gazetteerid_',gaz.GazetteerID) as nomeid, CURDATE()   as dataupdated  FROM Especimenes JOIN GPS_DATA as gps ON GPSPointID=gps.PointID JOIN Gazetteer as gaz ON gaz.GazetteerID=gps.GazetteerID JOIN Municipio as muni ON gaz.MunicipioID=muni.MunicipioID JOIN Province as prov ON prov.ProvinceID=muni.ProvinceID JOIN Country as crt ON crt.CountryID=prov.CountryID";
		//GAZETTEER ligados com amostras por dados GPS (LEVEL 2)
		$qarry[] = "SELECT  CONCAT(gaz2.Gazetteer,' - ', TRIM(REPLACE(gaz2.PathName, gaz2.Gazetteer, '')),' - ',Municipio,' - ',Province,' - ',UPPER(Country))  as nome, gaz2.PathName as searchname, getlocallatlong(0, gaz2.GazetteerID, gaz2.MunicipioID, 1)  AS lat,
getlocallatlong(0, gaz2.GazetteerID, gaz2.MunicipioID, 0)  AS logitude, NULL as alt, CONCAT('gazetteerid_',gaz2.GazetteerID) as nomeid, CURDATE()   as dataupdated  FROM Especimenes JOIN GPS_DATA as gps ON GPSPointID=gps.PointID JOIN Gazetteer as gaz  ON gps.GazetteerID=gaz.GazetteerID  JOIN Gazetteer as gaz2 ON gaz2.GazetteerID=gaz.ParentID JOIN Municipio as muni ON gaz.MunicipioID=muni.MunicipioID JOIN Province as prov ON prov.ProvinceID=muni.ProvinceID JOIN Country as crt ON crt.CountryID=prov.CountryID";
		//GAZETTEER ligados com amostras por dados GPS (LEVEL 3)
		$qarry[] = "SELECT  CONCAT(gaz3.Gazetteer,' - ', TRIM(REPLACE(gaz3.PathName, gaz3.Gazetteer, '')),' - ',Municipio,' - ',Province,' - ',UPPER(Country))  as nome, gaz3.PathName as searchname, getlocallatlong(0, gaz3.GazetteerID, gaz3.MunicipioID, 1)  AS lat,
getlocallatlong(0, gaz3.GazetteerID, gaz3.MunicipioID, 0)  AS logitude, NULL as alt, CONCAT('gazetteerid_',gaz3.GazetteerID) as nomeid, CURDATE()   as dataupdated  FROM Especimenes JOIN GPS_DATA as gps ON GPSPointID=gps.PointID JOIN Gazetteer as gaz  ON gps.GazetteerID=gaz.GazetteerID  JOIN Gazetteer as gaz2 ON gaz2.GazetteerID=gaz.ParentID JOIN Gazetteer as gaz3 ON gaz3.GazetteerID=gaz2.ParentID JOIN Municipio as muni ON gaz.MunicipioID=muni.MunicipioID JOIN Province as prov ON prov.ProvinceID=muni.ProvinceID JOIN Country as crt ON crt.CountryID=prov.CountryID";
		
		/////////GAZETTEER COM PLANTAS AGORA
		//country by gazetteer from PLANTAS
		
		;
		
		$qarry[] = "SELECT  Country as nome, Country as searchname, NULL as lat, NULL as logitude, NULL as alt, CONCAT('paisid_',crt.CountryID) as nomeid, CURDATE()   as dataupdated FROM Plantas JOIN Gazetteer as gaz USING(GazetteerID) JOIN Municipio as muni ON gaz.MunicipioID=muni.MunicipioID JOIN Province as prov ON prov.ProvinceID=muni.ProvinceID JOIN Country as crt ON crt.CountryID=prov.CountryID";
		//PROVINCE by gazetteer from PLANTAS
		$qarry[] = "SELECT DISTINCT CONCAT(Province,' - ',UPPER(Country)) as nome,Province as searchname, NULL as lat, NULL as logitude, NULL as alt, CONCAT('provinceid_',prov.ProvinceID)  as nomeid, CURDATE()   as dataupdated FROM Plantas JOIN Gazetteer as gaz USING(GazetteerID) JOIN Municipio as muni ON gaz.MunicipioID=muni.MunicipioID JOIN Province as prov ON prov.ProvinceID=muni.ProvinceID JOIN Country as crt ON crt.CountryID=prov.CountryID";   
		//MUNICIPIO by gazetteer from PLANTAS
		$qarry[] = "SELECT  CONCAT(Municipio,' - ',Province,' - ',UPPER(Country)) as nome, Municipio as searchname,muni.Latitude as lat, muni.Longitude as logitude, NULL as alt, CONCAT('municipioid_',muni.MunicipioID) as nomeid,CURDATE()  as dataupdated FROM Plantas JOIN Gazetteer as gaz USING(GazetteerID) JOIN Municipio as muni ON gaz.MunicipioID=muni.MunicipioID JOIN Province as prov ON prov.ProvinceID=muni.ProvinceID JOIN Country as crt ON crt.CountryID=prov.CountryID";
		//gazetteers directly connected to PLANTAS (LEVEL 1)
		$qarry[] = "SELECT CONCAT(gaz.Gazetteer,' - ', TRIM(REPLACE(gaz.PathName, gaz.Gazetteer, '')),' - ',Municipio,' - ',Province,' - ',UPPER(Country))  as nome, PathName as searchname, getlocallatlong(0, gaz.GazetteerID, gaz.MunicipioID, 1)  AS lat,
getlocallatlong(0, gaz.GazetteerID, gaz.MunicipioID, 0)  AS logitude,NULL as alt, CONCAT('gazetteerid_',gaz.GazetteerID) as nomeid, CURDATE()   as dataupdated  FROM Plantas JOIN Gazetteer as gaz USING(GazetteerID) JOIN Municipio as muni ON gaz.MunicipioID=muni.MunicipioID JOIN Province as prov ON prov.ProvinceID=muni.ProvinceID JOIN Country as crt ON crt.CountryID=prov.CountryID";
		//gazetteers directly connected to PLANTAS (LEVEL 2)
		$qarry[] = "SELECT  CONCAT(gaz2.Gazetteer,' - ', TRIM(REPLACE(gaz2.PathName, gaz2.Gazetteer, '')),' - ',Municipio,' - ',Province,' - ',UPPER(Country))  as nome, gaz2.PathName as searchname,  getlocallatlong(0, gaz2.GazetteerID, gaz2.MunicipioID, 1)  AS lat,
getlocallatlong(0, gaz2.GazetteerID, gaz2.MunicipioID, 0)  AS logitude,NULL as alt,  CONCAT('gazetteerid_',gaz2.GazetteerID) as nomeid, '".$sessiondate."' as dataupdated  FROM Plantas JOIN Gazetteer as gaz ON Plantas.GazetteerID=gaz.GazetteerID  JOIN Gazetteer as gaz2 ON gaz2.GazetteerID=gaz.ParentID JOIN Municipio as muni ON gaz.MunicipioID=muni.MunicipioID JOIN Province as prov ON prov.ProvinceID=muni.ProvinceID JOIN Country as crt ON crt.CountryID=prov.CountryID";
		//gazetteers directly connected to PLANTAS (LEVEL 3)
		$qarry[] = "SELECT  CONCAT(gaz3.Gazetteer,' - ', TRIM(REPLACE(gaz3.PathName, gaz3.Gazetteer, '')),' - ',Municipio,' - ',Province,' - ',UPPER(Country))  as nome, gaz3.PathName as searchname, getlocallatlong(0, gaz3.GazetteerID, gaz3.MunicipioID, 1)  AS lat,
getlocallatlong(0, gaz3.GazetteerID, gaz3.MunicipioID, 0)  AS logitude,NULL as alt,  CONCAT('gazetteerid_',gaz3.GazetteerID) as nomeid, CURDATE()  as dataupdated  FROM Plantas JOIN Gazetteer as gaz ON Plantas.GazetteerID=gaz.GazetteerID JOIN Gazetteer as gaz2 ON gaz2.GazetteerID=gaz.ParentID JOIN Gazetteer as gaz3 ON gaz3.GazetteerID=gaz2.ParentID JOIN Municipio as muni ON gaz.MunicipioID=muni.MunicipioID JOIN Province as prov ON prov.ProvinceID=muni.ProvinceID JOIN Country as crt ON crt.CountryID=prov.CountryID";
		//paises ligados com PLANTAS por dados GPS
		$qarry[] = "SELECT  Country as nome, Country as searchname, ' ' as lat, ' ' as logitude, ' ' as alt, CONCAT('paisid_',crt.CountryID) as nomeid, CURDATE()  as dataupdated FROM Plantas JOIN GPS_DATA as gps ON GPSPointID=gps.PointID JOIN Gazetteer as gaz ON gaz.GazetteerID=gps.GazetteerID JOIN Municipio as muni ON gaz.MunicipioID=muni.MunicipioID JOIN Province as prov ON prov.ProvinceID=muni.ProvinceID JOIN Country as crt ON crt.CountryID=prov.CountryID";
		//PROVINCIAS ligados com PLANTAS por dados GPS
		$qarry[] = "SELECT  CONCAT(Province,' - ',UPPER(Country)) as nome,Province as searchname, ' ' as lat, ' ' as logitude, ' ' as alt, CONCAT('provinceid_',prov.ProvinceID)  as nomeid, CURDATE()  as dataupdated FROM Plantas JOIN GPS_DATA as gps ON GPSPointID=gps.PointID JOIN Gazetteer as gaz ON gaz.GazetteerID=gps.GazetteerID JOIN Municipio as muni ON gaz.MunicipioID=muni.MunicipioID JOIN Province as prov ON prov.ProvinceID=muni.ProvinceID JOIN Country as crt ON crt.CountryID=prov.CountryID";
		//MUNICIPIOS ligados com PLANTAS por dados GPS
		$qarry[] = "SELECT  CONCAT(Municipio,' - ',Province,' - ',UPPER(Country)) as nome, Municipio as searchname,muni.Latitude as lat, muni.Longitude as logitude, ' ' as alt, CONCAT('municipioid_',muni.MunicipioID) as nomeid,CURDATE()  as dataupdated FROM Plantas JOIN GPS_DATA as gps ON GPSPointID=gps.PointID JOIN Gazetteer as gaz ON gaz.GazetteerID=gps.GazetteerID JOIN Municipio as muni ON gaz.MunicipioID=muni.MunicipioID JOIN Province as prov ON prov.ProvinceID=muni.ProvinceID JOIN Country as crt ON crt.CountryID=prov.CountryID";
		//GAZETTEER ligados com PLANTAS por dados GPS (LEVEL 1)
		$qarry[] = "SELECT  CONCAT(gaz.Gazetteer,' - ', TRIM(REPLACE(gaz.PathName, gaz.Gazetteer, '')),' - ',Municipio,' - ',Province,' - ',UPPER(Country))  as nome, PathName as searchname, getlocallatlong(0, gaz.GazetteerID, gaz.MunicipioID, 1)  AS lat,
getlocallatlong(0, gaz.GazetteerID, gaz.MunicipioID, 0)  AS logitude,NULL as alt, CONCAT('gazetteerid_',gaz.GazetteerID) as nomeid, CURDATE()  as dataupdated  FROM Plantas JOIN GPS_DATA as gps ON GPSPointID=gps.PointID JOIN Gazetteer as gaz ON gaz.GazetteerID=gps.GazetteerID JOIN Municipio as muni ON gaz.MunicipioID=muni.MunicipioID JOIN Province as prov ON prov.ProvinceID=muni.ProvinceID JOIN Country as crt ON crt.CountryID=prov.CountryID";
		//GAZETTEER ligados com PLANTAS por dados GPS (LEVEL 2)

		$qarry[] = "SELECT  CONCAT(gaz2.Gazetteer,' - ', TRIM(REPLACE(gaz2.PathName, gaz2.Gazetteer, '')),' - ',Municipio,' - ',Province,' - ',UPPER(Country))  as nome, gaz2.PathName as searchname, getlocallatlong(0, gaz2.GazetteerID, gaz2.MunicipioID, 1)  AS lat,
getlocallatlong(0, gaz2.GazetteerID, gaz2.MunicipioID, 0)  AS logitude,NULL as alt, CONCAT('gazetteerid_',gaz2.GazetteerID) as nomeid, CURDATE()  as dataupdated  FROM Plantas JOIN GPS_DATA as gps ON GPSPointID=gps.PointID JOIN Gazetteer as gaz  ON gps.GazetteerID=gaz.GazetteerID  JOIN Gazetteer as gaz2 ON gaz2.GazetteerID=gaz.ParentID JOIN Municipio as muni ON gaz.MunicipioID=muni.MunicipioID JOIN Province as prov ON prov.ProvinceID=muni.ProvinceID JOIN Country as crt ON crt.CountryID=prov.CountryID";
		//GAZETTEER ligados com PLANTAS por dados GPS (LEVEL 3)
		$qarry[] = "SELECT  CONCAT(gaz3.Gazetteer,' - ', TRIM(REPLACE(gaz3.PathName, gaz3.Gazetteer, '')),' - ',Municipio,' - ',Province,' - ',UPPER(Country))  as nome, gaz3.PathName as searchname, getlocallatlong(0, gaz3.GazetteerID, gaz3.MunicipioID, 1)  AS lat,
getlocallatlong(0, gaz3.GazetteerID, gaz3.MunicipioID, 0)  AS logitude,NULL as alt, CONCAT('gazetteerid_',gaz3.GazetteerID) as nomeid, CURDATE()  as dataupdated  FROM Plantas JOIN GPS_DATA as gps ON GPSPointID=gps.PointID JOIN Gazetteer as gaz  ON gps.GazetteerID=gaz.GazetteerID  JOIN Gazetteer as gaz2 ON gaz2.GazetteerID=gaz.ParentID JOIN Gazetteer as gaz3 ON gaz3.GazetteerID=gaz2.ParentID JOIN Municipio as muni ON gaz.MunicipioID=muni.MunicipioID JOIN Province as prov ON prov.ProvinceID=muni.ProvinceID JOIN Country as crt ON crt.CountryID=prov.CountryID";
		}

	$ii=0;
	
	$qq = "CREATE TABLE IF NOT EXISTS  ".$tempname." ( nome VARCHAR(400), searchname VARCHAR(400), lat DOUBLE, logitude DOUBLE, alt DOUBLE, nomeid CHAR(200), dataupdated DATE) CHARACTER SET utf8  ENGINE InnoDB"; 
mysql_query($qq,$conn);
//echo $qq."<br >";
	foreach ($qarry as $kk => $sql) {
		//if ($ii==0) {
			//$qbse = "CREATE TABLE ".$tempname." CHARACTER SET utf8 ENGINE InnoDB ".$sql;
		//} else {
			$qbse = "INSERT INTO ".$tempname." (nome, searchname,lat,logitude,alt,nomeid, dataupdated) ".$sql;
		//}
		$forkeyoff = "SET FOREIGN_KEY_CHECKS=0";
		mysql_query($forkeyoff,$conn);
		$rup = mysql_query($qbse,$conn);
		$forkeyoff = "SET FOREIGN_KEY_CHECKS=1";
		mysql_query($forkeyoff,$conn);
			//echo "<br />".$qbse."<br />";
		if ($rup) { echo "feito".$ii."<br />"; } else { echo "<br />".$qbse."<br />";}
		session_write_close();
		flush();
		$ii++;
	}
	$forkeyoff = "SET FOREIGN_KEY_CHECKS=0";
	@mysql_query($forkeyoff,$conn);
	$qrr = "ALTER TABLE `". $tempname."`  ADD `temp_localID` INT(10) UNSIGNED NOT NULL AUTO_INCREMENT PRIMARY KEY FIRST";
	 mysql_query($qrr,$conn);
	$qu = "CREATE INDEX searchname ON ".$tempname." (searchname)";
	mysql_query($qu,$conn);
	$qu = "CREATE INDEX nomeid ON ".$tempname." (nomeid)";
	mysql_query($qu,$conn);
	$qu = "CREATE INDEX nome ON ".$tempname." (nome)";
	mysql_query($qu,$conn);
	$qu = "CREATE INDEX lat ON ".$tempname." (lat)";
	mysql_query($qu,$conn);
	$qu = "CREATE INDEX logitude ON ".$tempname." (logitude)";
	mysql_query($qu,$conn);

	$qq = "SELECT DISTINCT nome, searchname,lat,logitude,alt,nomeid, dataupdated FROM ".$tempname." WHERE nome<>'' AND nome IS NOT NULL ORDER BY searchname,nome";
	$qq = "CREATE TABLE ".$tablename." CHARACTER SET utf8 ENGINE InnoDB ".$qq;
	$rss = mysql_query($qq,$conn);
	//$sql = "ALTER TABLE ".$tablename." ADD nomeautor CHAR(255)";
	//mysql_query($sql,$conn);
	if ($rss) {
		echo "-----------------------Concluido -  tabela ".$tablename." criada com sucesso!-------------------<br />";
	} else {
		echo "erro:<br />".$qq."<br />";
	}

	//$qu = "DROP TABLE ".$tempname;
	//mysql_query($qu,$conn);
	$qrr = "ALTER TABLE `". $tablename."`  ADD `temp_localID` INT(10) UNSIGNED NOT NULL AUTO_INCREMENT PRIMARY KEY FIRST";
	 mysql_query($qrr,$conn);
	$qu = "CREATE INDEX searchname ON ".$tablename." (searchname)";
	mysql_query($qu,$conn);
	$qu = "CREATE INDEX nomeid ON ".$tablename." (nomeid)";
	mysql_query($qu,$conn);
	$qu = "CREATE INDEX nome ON ".$tablename." (nome)";
	mysql_query($qu,$conn);
	
	$forkeyoff = "SET FOREIGN_KEY_CHECKS=1";
	@mysql_query($forkeyoff,$conn);
}

function UpdataLocalitySimple($gazid,$paisid,$conn) { 
		$tablename = 'LocalitySimple'; 
		//DESABILITA CHAVES EXTERNAS
		$forkeyoff = "SET FOREIGN_KEY_CHECKS=0";
		mysql_query($forkeyoff,$conn);

		//INSERE O REGISTRO DE MUDANCA
		$qq = "INSERT INTO $tablename (SELECT CONCAT(PathName,' - ',Municipio,' - ',Province,' - ',UPPER(Country))  as nome, PathName as searchname, Gazetteer.Latitude as lat, Gazetteer.Longitude as logitude, Altitude as alt, CONCAT('gazetteerid_',GazetteerID) as nomeid, '".$sessiondate."' as dataupdated  FROM Gazetteer JOIN Municipio USING(MunicipioID) JOIN Province USING(ProvinceID) JOIN Country USING(CountryID) WHERE Gazetteer.GazetteerID='".$gazid."')";
		mysql_query($qq,$conn);

		if ($paisid=30) {
			$tablename = 'LocalitySimpleBrasil'; 

			$qq = "INSERT INTO $tablename (SELECT CONCAT(PathName,' - ',Municipio,' - ',Province,' - ',UPPER(Country))  as nome, PathName as searchname, Gazetteer.Latitude as lat, Gazetteer.Longitude as logitude, Altitude as alt, CONCAT('gazetteerid_',GazetteerID) as nomeid, '".$sessiondate."' as dataupdated  FROM Gazetteer JOIN Municipio USING(MunicipioID) JOIN Province USING(ProvinceID) JOIN Country USING(CountryID) WHERE Gazetteer.GazetteerID='".$gazid."')";
			@mysql_query($qq,$conn);
		}


		$tablename = 'LocalitySimpleSearch'; 
		$qq = "INSERT INTO $tablename (SELECT CONCAT(PathName,' - ',Municipio,' - ',Province,' - ',UPPER(Country))  as nome, PathName as searchname, Gazetteer.Latitude as lat, Gazetteer.Longitude as logitude, Altitude as alt, CONCAT('gazetteerid_',GazetteerID) as nomeid, '".$sessiondate."' as dataupdated  FROM Gazetteer JOIN Municipio USING(MunicipioID) JOIN Province USING(ProvinceID) JOIN Country USING(CountryID) WHERE Gazetteer.GazetteerID='".$gazid."')";
		@mysql_query($qq,$conn);

		//HABILITA CHAVE INTERNA
		$forkeyoff = "SET FOREIGN_KEY_CHECKS=1";
		mysql_query($forkeyoff,$conn);
}


//autosuggest function
function autosuggestfieldvalwithunit($file,$id,$valor,$idres,$nomeid,$all,$unittagid) {
echo "<table>
<tr><td>
<div class=\"search-wrap\">
	<input type='hidden' id=\"$nomeid\" name=\"$nomeid\" value='".${$nomeid}."' />
	<input type=\"text\" id=\"".$id."\" name=\"".$id."\" value=\"".$valor."\" onkeyup=\"javascript:autosuggestwithunit('".$id."','".$idres."','".$file."','".$nomeid."',".$all.",'".$unittagid."')\"  size='50px'  autocomplete=\"off\"  />
	<div id=\"".$idres."\" class=\"results\"></div>
</div>
</td>
</tr></table>";
//autocomplete=\"off\" 
}

//autosuggest function
function autosuggestfieldval($file,$id,$valor,$idres,$nomeid,$all) {
echo "<table>
<tr><td>
<div class=\"search-wrap\">
	<input type='hidden' id=\"$nomeid\" name=\"$nomeid\" value='".${$nomeid}."' />
	<input type=\"text\" id=\"".$id."\" name=\"".$id."\" value=\"".$valor."\" onkeyup=\"javascript:autosuggest('".$id."','".$idres."','".$file."','".$nomeid."',".$all.")\"  autocomplete=\"off\"  />
	<div id=\"".$idres."\" class=\"results\"></div>
</div>
</td>
</tr></table>";
//autocomplete=\"off\" 
}

//autosuggest function
function autosuggestfieldval2($file,$id,$valor,$idres,$nomeid,$nomeval,$all) {
echo "<table>
<tr><td>
<div class=\"search-wrap\">
	<input type='hidden' id=\"$nomeid\" name=\"$nomeid\" value=\"".$nomeval."\" />
	<input type=\"text\" id=\"".$id."\" name=\"".$id."\" value=\"".$valor."\" onkeyup=\"javascript:autosuggest('".$id."','".$idres."','".$file."','".$nomeid."',".$all.")\"   autocomplete=\"off\"  />
	<div id=\"".$idres."\" class=\"results\"></div>
</div>
</td>
</tr></table>";
//autocomplete=\"off\"
}

function autosuggestfieldval3($file,$id,$valor,$idres,$nomeid,$nomeval,$all,$sizeofinput) {
echo "<table>
<tr><td>
<div class=\"search-wrap\">
  <input type='hidden' id=\"$nomeid\" name=\"$nomeid\" value=\"".$nomeval."\" />
  <input size='".$sizeofinput."px' type=\"text\" id=\"".$id."\" name=\"".$id."\" value=\"".$valor."\" onclick=\"javascript:SelectAll('".$id."');\" onkeyup=\"javascript:autosuggest('".$id."','".$idres."','".$file."','".$nomeid."',".$all.");\"  autocomplete=\"off\" />
  <div id=\"".$idres."\" class=\"results\"></div>
</div>
</td>
</tr></table>";
//autocomplete=\"off\" 
}

function autosuggestfieldval4($file,$id,$valor,$idres,$nomeid,$nomeval,$municipioid,$all,$sizeofinput) {
echo "<table>
<tr><td>
<div class=\"search-wrap\">
  <input type='hidden' id=\"$nomeid\" name=\"$nomeid\" value=\"".$nomeval."\" />
  <input size='".$sizeofinput."px' type=\"text\" id=\"".$id."\" name=\"".$id."\" value=\"".$valor."\" onclick=\"javascript:SelectAll('".$id."');\" onkeyup=\"javascript:autosuggestmuni('".$id."','".$idres."','".$file."','".$nomeid."',".$all.",".$municipioid.");\"  autocomplete=\"off\" />
  <div id=\"".$idres."\" class=\"results\"></div>
</div>
</td>
</tr></table>";
//autocomplete=\"off\" 
}

//igual ao tres mas com nota adicionada
function autosuggestfieldval5($file,$id,$valor,$idres,$nomeid,$nomeval,$all,$sizeofinput, $noteoninput) {
echo "<table>
<tr><td>
<div class=\"search-wrap\">
  <input type='hidden' id=\"$nomeid\" name=\"$nomeid\" value=\"".$nomeval."\" />
  <span style=\"color: ##900000 ; font-size: 0.6em\">".$noteoninput."</span><br />
  <input size='".$sizeofinput."px' type=\"text\" id=\"".$id."\" name=\"".$id."\" value=\"".$valor."\" onclick=\"javascript:SelectAll('".$id."');\" onkeyup=\"javascript:autosuggest('".$id."','".$idres."','".$file."','".$nomeid."',".$all.");\"  autocomplete=\"off\" />
  <div id=\"".$idres."\" class=\"results\"></div>
</div>
</td>
</tr></table>";
//autocomplete=\"off\" 
}

//describe collections
function resumecoleta($especimenid, $conn) {
	$qq = "SELECT * FROM Especimenes LEFT JOIN Identidade USING(DetID) WHERE EspecimenID='".$especimenid."'";
	$res = mysql_query($qq,$conn);
	$row = mysql_fetch_assoc($res);
	if ($row['FamiliaID']) {$famid = $row['FamiliaID'];} else {$famid = ' ';}
	if ($row['GeneroID']) {$genusid = $row['GeneroID'];} else {$genusid = ' ';}
	if ($row['EspecieID']) {$speciesid = $row['EspecieID'];} else {$speciesid = ' ';}
	if ($row['InfraEspecieID']) {$infraspid = $row['InfraEspecieID'];} else {$infraspid = ' ';}
	$colnum = $row['Number'];
	$determinadorid = $row['DetbyID'];
	$pessoaid = $row['ColetorID'];
	$yy = $row['Ano'];
	$mm = $row['Mes'];
	$dd = $row['Day'];
	$latdec = $row['Latitude'];
	$longdec = $row['Longitude'];
	$plantaid = $row['PlantaID'];
	if ($plantaid==0) {$plantaid='--';}

	$coord = @coordinates($latdec,$longdec,'','','','','','','','');
	//echo "Aqui".print_r($coord);
	$altitude = $row['Altitude'];
	$altmin = $row['AltitudeMin'];
	$altmax = $row['AltitudeMax'];
	$gazetteerid = $row['GazetteerID'];

	$habitatid = $row['HabitatID'];
	$ttids = $row['LastCharIDS'];
	$datacol = $yy."-".$mm."-".$dd;
	$datadet = $row['DetDate'];
	$addcolvalue = $row['AddColIDS'];
	$addcolarr = explode(";",$addcolvalue);
	$addcoltxt = '';
	$j=1;
	foreach ($addcolarr as $kk => $val) {
		$qq = "SELECT * FROM Pessoas WHERE PessoaID='$val'";
		$res = mysql_query($qq,$conn);
		$rwr = mysql_fetch_assoc($res);
		if ($j==1) {
			$addcoltxt = 	$rwr['Abreviacao'];
		} else {
			$addcoltxt = $addcoltxt."; ".$rwr['Abreviacao'];
		}
		$j++;
	}

	$taxnome = gettaxaname($infraspid,$speciesid,$genusid,$famid,$conn);
		$zz = getpessoa($determinadorid,$abb=TRUE,$conn);
		$zr = mysql_fetch_assoc($zz);
		$determinador = $zr['Abreviacao'];

		$zz = getpessoa($pessoaid,$abb=TRUE,$conn);
		$zr = mysql_fetch_assoc($zz);
		$coletor = $zr['Abreviacao'];
	if (!empty($gazetteerid)) {
		$locality = getlocality($gazetteerid,$conn);
		$rr = getgazetteer($gazetteerid,'',$conn);
		$rww = mysql_fetch_assoc($rr);
		$gaz = $rww['TraitName']." ".$rww['Gazetteer'];
	} else {$locality='--';}

	if (!empty($habitatid)) {
		$habitat = describehabitat($habitatid,$img=TRUE,$conn);
	} else {$habitat='--';}

	$oldvals = storeoriginaldatatopost($especimenid,'EspecimenID',0,$conn,'');
	if ($oldvals) {
		$traitarray = $oldvals;
		$listoftraits = describetraits($traitarray,$img=TRUE,$conn);
	}

	$resultado = array('coletor' => $coletor, 'colnum' => $colnum, 'datacol' => $datacol, 'taxnome' => $taxnome, 'listoftraits' => $listoftraits, 'locality' => $locality, 'local' => $gaz, 'habitat' => $habitat, 'latitude' => $latdec, 'longitude' => $longdec, '$altitude' => $altitude);
	return ($resultado);
}




function resumoplanta($plantaid, $conn) {
	$qq = "SELECT * FROM Plantas LEFT JOIN Identidade USING(DetID) WHERE PlantaID='".$plantaid."'";
	$res = mysql_query($qq,$conn);
	$row = mysql_fetch_assoc($res);
	if ($row['FamiliaID']) {$famid = $row['FamiliaID'];} else {$famid = ' ';}
	if ($row['GeneroID']) {$genusid = $row['GeneroID'];} else {$genusid = ' ';}
	if ($row['EspecieID']) {$speciesid = $row['EspecieID'];} else {$speciesid = ' ';}
	if ($row['InfraEspecieID']) {$infraspid = $row['InfraEspecieID'];} else {$infraspid = ' ';}
	$tagnum = $row['PlantaTag'];
	$determinadorid = $row['DetbyID'];
	$datacol = $row['TaggedDate'];
	$latdec = $row['Latitude'];
	$longdec = $row['Longitude'];

	$procedenciaid = $row['ProcedenciaID'];

	//jb
		$inexsitu = $row['InSituExSitu'];
	if ($inexsitu=='Exsitu') {$tagnum='JB-X '.$tagnum;}
	if ($inexsitu=='Insitu') {$tagnum='JB-N '.$tagnum;}

	//

	$especimensids = $row['EspecimensIDS'];
	if ($especimensids==0) {$especimensids='--';}

	$coord = @coordinates($latdec,$longdec,'','','','','','','','');

	$altitude = $row['Altitude'];
	$altmin = $row['AltitudeMin'];
	$altmax = $row['AltitudeMax'];
	$gazetteerid = $row['GazetteerID'];

	$habitatid = $row['HabitatID'];


	$ttids = $row['LastCharIDS'];
	$datadet = $row['DetDate'];

	$taggedby = $row['TaggedBy'];
	$taggedbyarr = explode(";",$taggedby);
	$taggedbytxt = '';
	$j=1;
	foreach ($taggedbyarr as $kk => $val) {
		$qq = "SELECT * FROM Pessoas WHERE PessoaID='$val'";
		$res = mysql_query($qq,$conn);
		$rwr = mysql_fetch_assoc($res);
		if ($j==1) {
			$taggedbytxt = 	$rwr['Abreviacao'];
		} else {
			$taggedbytxt = $taggedbytxt."; ".$rwr['Abreviacao'];
		}
		$j++;
	}

	$taxnome = gettaxaname($infraspid,$speciesid,$genusid,$famid,$conn);
		$zz = getpessoa($determinadorid,$abb=TRUE,$conn);
		$zr = mysql_fetch_assoc($zz);
		$determinador = $zr['Abreviacao'];

	if (!empty($gazetteerid)) {
		$locality = getlocality($gazetteerid,$conn);
		$rr = getgazetteer($gazetteerid,'',$conn);
		$rww = mysql_fetch_assoc($rr);
		$gaz = $rww['TraitName']." ".$rww['Gazetteer'];
	} else {$locality='--';}

	if (!empty($habitatid)) {
		$habitat = describehabitat($habitatid,$img=TRUE,$conn);
	} else {$habitat='--';}
	$oldvals = storeoriginaldatatopost($plantaid,'PlantaID',0,$conn,'');
	if ($oldvals) {
		$traitarray = $oldvals;
		$listoftraits = describetraits($traitarray,0,$conn);
	}

	$resultado = array('procedencia' => $procedenciaid, 'taggedby' => $taggedby, 'tagnum' => $tagnum, 'datacol' => $datacol, 'taxnome' => $taxnome, 'listoftraits' => $listoftraits, 'locality' => $locality, 'local' => $gaz, 'habitat' => $habitat, 'latitude' => $latdec, 'longitude' => $longdec, '$altitude' => $altitude, 'especimensids' => $especimensids, 'taggedbytxt' => $taggedbytxt);
	return ($resultado);
}

function getmonthstring($mm,$abbre=FALSE, $english=FALSE) {

	if (!$abbre) {
		if ($english) {
				$mmarr = array('January','February','March','April','May','June','July','August','September','October','November','December');
		} else {
		$mmarr = array('Janeiro','Fevereiro','Março','Abril','Maio','Junho','Julho','Agosto','Setembro','Outubro','Novembro','Dezembro');
		}
	} else {
		if ($english) {
			$mmarr = array('Jan','Feb','Mar','Apr','May','Jun','Jul','Aug','Sep','Oct','Nov','Dec');
		} else {
		$mmarr = array('Jan','Fev','Mar','Abr','Mai','Jun','Jul','Ago','Set','Out','Nov','Dez');
		}

	}
	$month = $mmarr[$mm];
	return $month;
}

function summarizeplantas($famid,$genusid,$speciesid,$infraspid,$conn) {
	$qq = "SELECT * FROM Plantas JOIN Identidade USING(DetID) JOIN Tax_Especies ON Identidade.EspecieID=Tax_Especies.EspecieID JOIN Tax_Generos ON Identidade.GeneroID=Tax_Generos.GeneroID JOIN Tax_Familias ON Identidade.FamiliaID=Tax_Familias.FamiliaID WHERE";
	if (!empty($infraspid)) {
		$qq = $qq." InfraEspecieID=$infraspid";
	} else {
		if (!empty($speciesid)) {
			$qq = $qq." EspecieID=$speciesid";
		} else {
			if (!empty($genusid)) {
				$qq = $qq." GeneroID=$genusid";
			} elseif(!empty($famid)) {
				$qq = $qq." Identidade.FamiliaID='$famid'";
			}
		}
	}
	//echo $qq;
	$rr = mysql_query($qq,$conn);
	if ($rr) {
		while ($rw = mysql_fetch_assoc($rr)) {
			$genus = $rw['Genero'];
			$genera = array_merge((array)$genera,(array)$genus);
			$genera = array_unique($genera);
			$ngen = count($genera);

			$sp = $rw['Especie'];
			$sps = array_merge((array)$sps,(array)$sp);
			$sps = array_unique($sps);
			$nsps = count($sps);

			$infsp = $rw['InfraEspecie'];
			$infsps = array_merge((array)$infsps,(array)$infsp);
			$infsps = array_unique($infsps);
			$ninfsps = count($infsps);

			$gaz = $rw['GazetteerID'];
			$gazs = array_merge((array)$gazs,(array)$gaz);
			$gazs = array_unique($gazs);
			$ngazs = count($gazs);

			$inxn = $rw['InSituExSitu'];
			if ($inxn =='Exsitu') { $exsitu = array_merge((array)$exsitu,(array)$inxn);}

			if ($inxn =='Insitu') { $insitu = array_merge((array)$insitu,(array)$inxn);}
		}

	echo "<table class='tablethinborder' align='right' cellpadding=5>
		<tr class='clicked'>
				<td ><b>".$ngen."</b> &nbsp; ".strtolower(GetLangVar('namegenus'))."s</td>
				<td>&nbsp;</td>
				<td ><b>".$nsps."</b>&nbsp;".strtolower(GetLangVar('namespecies'))."</td>
				<td>&nbsp;</td>
				<td ><b>".$ninfsps."</b> &nbsp; ".strtolower(GetLangVar('nameinfraspecies'))."</td>
				<td>&nbsp;</td>
				<td ><b>".$ngazs."</b>&nbsp; ".strtolower(GetLangVar('namelocalidade'))."s</td>
		</tr>
		</table>";




	}
}

function updatevernacular($vernacularvalue,$level,$id,$conn) {
				$erro=0;
				$zz = $level."|".$id;
				$qq = "SELECT *  FROM Vernacular WHERE TaxonomyIDS LIKE '%".$zz.";%' OR `TaxonomyIDS` LIKE '%".$zz."'";
				$rrr = mysql_query($qq,$conn);
				while ($row = mysql_fetch_assoc($rrr)) {
					$olv = $row['VernacularID'];
					$oldvals = array_merge((array)$oldvals,(array)$olv);
				}
				$oldvernaval = $oldvals;
				$newvernval = explode(";",$vernacularvalue);
				//check or update new vernacular values
				foreach ($newvernval as $kk => $vv) {
							$ve = trim($vv);
							if (!@in_array($ve,$oldvernaval)) {
								$qq = "SELECT * FROM Vernacular WHERE VernacularID='$ve'";
								$res = mysql_query($qq,$conn);
								$rw = mysql_fetch_assoc($res);
								$otaxids = trim($rw['TaxonomyIDS']);
								if (!empty($otaxids)) {
									$otaxidsarr = explode(";",$otaxids);
									$newtax = array_merge((array)$otaxidsarr,(array)$zz);
									$newvvv = array_unique($newtax);
									$newvertaxids = implode(";",$newvvv);
								} else {
									$newvertaxids = $zz;

								}
							} else {
								$newvertaxids = $zz;
							}
							$forkeyoff = "SET FOREIGN_KEY_CHECKS=0";
							@mysql_unbuffered_query($forkeyoff,$conn);
							$qq = "UPDATE Vernacular SET TaxonomyIDS='$newvertaxids' WHERE VernacularID='$ve'";
							$verupdate = mysql_query($qq,$conn);
							$forkeyoff = "SET FOREIGN_KEY_CHECKS=1";
							@mysql_unbuffered_query($forkeyoff,$conn);
							
							if (!$vernupdate) {
									$erro++;
							}
				}

				if (!empty($oldvals)) {
				//delete a vernacular value if deleted
				foreach ($oldvernaval as $kk => $vv) {
							$ve = trim($vv);
							$inr = in_array($ve,$newvernval);
							//echo $inr;
							if (!in_array($ve,$newvernval)) {
								$qq = "SELECT * FROM Vernacular WHERE VernacularID='$ve'";
								//echo $qq;
								$res = mysql_query($qq,$conn);
								$rw = mysql_fetch_assoc($res);
								$otaxids = $rw['TaxonomyIDS'];
								$otaxidsarr = explode(";",$otaxids);
								$oldkey = array_search($zz,$otaxidsarr);
								$otaxidsarr[$oldkey] = NULL;
								if (count($otaxidsarr)>1) {
									$newtaxarr = implode(";",$otaxidsarr);
								} elseif(count($otaxidsarr)==1) {
									$newtaxarr = $otaxidsarr[0];
								} else { $newtaxarr='';}
								$forkeyoff = "SET FOREIGN_KEY_CHECKS=0";
								@mysql_unbuffered_query($forkeyoff,$conn);
								$qq = "UPDATE Vernacular SET TaxonomyIDS='$newtaxarr' WHERE VernacularID='$ve'";
								$verupdate = mysql_query($qq,$conn);
								$forkeyoff = "SET FOREIGN_KEY_CHECKS=1";
								@mysql_unbuffered_query($forkeyoff,$conn);
							
								if (!$vernupdate) {
											$erro++;
								}
							} 
				}
				}
		if ($erro>0) {
			return FALSE;
		} else {
			return TRUE;
		}
}

function describevernacular($vernacularvalue,$conn) {
	$vernarr = explode(";",$vernacularvalue);
	$vernaculartxt = '';
	$j=1;
	foreach ($vernarr as $kk => $val) {
		$qq = "SELECT * FROM Vernacular WHERE VernacularID='$val'";
		$res = mysql_query($qq,$conn);
		$row = mysql_fetch_assoc($res);
		if ($j==1) {
			$vernaculartxt = 	$row['Vernacular'];
			if (!empty($row['Language'])) { $vernaculartxt=$vernaculartxt." (".$row['Language'].")";}
		} else {
			if (!empty($row['Language'])) { $vtxt= $row['Vernacular']." (".$row['Language'].")";} else {$vtxt=$row['Vernacular'];}
			$vernaculartxt = $vernaculartxt."; ".$vtxt;
		}
		$j++;
	}
	return($vernaculartxt);
}

//Downloaded from Php.net posted by james -at- bandit.co -dot- nz 08-Apr-2010 01:07
// array_intersect that splits the needle array into two - one filled with "intersected" results, and one filled with the remainder
function array_intersect_split($needle, $haystack, $preserve_keys = false) {
    if(!is_array($needle) || !is_array($haystack)) return false;
    $new_arr = array();
    foreach($needle as $key => $value) {
        if(($loc = array_search($value, $haystack))!==false) {
            if(!$preserve_keys) $new_arr[] = $value;
            else $new_arr[$key] = $value;
            unset($needle[$key]);
        }
    }
    //print_r($result);
    $result = array($new_arr,$needle);
    return($result);
}

function listtraitsasfieldcols($conn){
	//ob_start();
	$res = mysql_query("SELECT * FROM Traits",$conn);
	if ($res) {
		while ($row = mysql_fetch_assoc($res)) {
			unset($charpath);
			$TraitID = $row['TraitID'];
			$parentID = $row['ParentID'];
			$charpath =  str_replace(' ','_',$row['TraitName']);
			$gg = $parentID;
			$i=1;
			while (!empty($gg)) {
				$query="SELECT * FROM Traits WHERE TraitID='$gg'";
				$rr = mysql_query($query,$conn);
				$aa = mysql_fetch_assoc($rr);
				$nome = $aa['TraitName'];
				$nome = str_replace(' ','_',$nome);
				$gg = $aa['ParentID'];
				$charpath= $nome."_".$charpath;
				$i++;
			} 
			if (!empty($charpath)) {
				$forkeyoff = "SET FOREIGN_KEY_CHECKS=0";
					mysql_unbuffered_query($forkeyoff,$conn);
				$update = "UPDATE Traits SET TraitAsCol='$charpath',MenuLevel='$i' WHERE TraitID='$TraitID'";
					mysql_unbuffered_query($update,$conn);
				$forkeyoff = "SET FOREIGN_KEY_CHECKS=1";
					mysql_unbuffered_query($forkeyoff,$conn);
			}
			//flush();
		}
		//ob_end_clean();
	}
}

function gettraitsasfieldcolnames($formid,$mean=false,$conn) {
	listtraitsasfieldcols($conn);
	//ob_start();
		if ($formid>0) {
			$qq = "SELECT * FROM Formularios WHERE FormID='$formid'";
			$res = mysql_unbuffered_query($qq,$conn);
			$rw = mysql_fetch_assoc($res);
			$traitids = explode(";",$rw['FormFieldsIDS']);
			//mysql_free_result($res);
			//unset($rw);

			$resultado= array();
			foreach ($traitids as $ttid) {
				$qq = "SELECT * FROM Traits WHERE TraitID='$ttid'";
				$rrr = mysql_unbuffered_query($qq,$conn);
				$rwr = mysql_fetch_assoc($rrr);
				$tipo = $rwr['TraitTipo'];
				$traitname = RemoveAcentos($rwr['TraitAsCol']);
				$traitname = strtoupper($traitname);

				//se quantitativo
				if ($tipo=='Variavel|Quantitativo') {
					if ($mean) {
						$tarr = array($traitname." VARCHAR(100)",$traitname."_UNIT  VARCHAR(10)",$traitname."_MEAN  VARCHAR(10)",$traitname."_SD VARCHAR(10)");
					} else {
						$tarr = array($traitname."  VARCHAR(100)",$traitname."_UNIT VARCHAR(10)");
					}
				} else {
					if ($tipo=='Variavel|Texto') {
						$len = 500;
					} else {
						$len = 100;
					}
					$tarr = array($traitname." VARCHAR(".$len.")");
				}
				$resultado = array_merge((array)$resultado,(array)$tarr);
			}
			//echopre($resultado);
			return $resultado;
		} else {
			return false;
		}
}

function gettraitsasfieldcolnames_monitor($formid,$mean=false,$censo,$monitorbydate,$plantaids_array,$conn) {
	listtraitsasfieldcols($conn);
	//ob_start();
		if ($formid>0) {
			$qq = "SELECT * FROM Formularios WHERE FormID='$formid'";
			$res = mysql_unbuffered_query($qq,$conn);
			$rw = mysql_fetch_assoc($res);
			$traitids = explode(";",$rw['FormFieldsIDS']);
			//mysql_free_result($res);
			//unset($rw);

			$resultado= array();
			foreach ($traitids as $ttid) {
				$qq = "SELECT * FROM Traits WHERE TraitID='$ttid'";
				$rrr = mysql_unbuffered_query($qq,$conn);
				$rwr = mysql_fetch_assoc($rrr);
				$tipo = $rwr['TraitTipo'];
				$traitname = RemoveAcentos($rwr['TraitAsCol']);
				$traitname = strtoupper($traitname);

				$dates_array  = array();
				//determina quantas datas tem no filtro
				if ($monitorbydate==1) {
					$dates_array  = array();
					$qu = "SELECT DISTINCT DataObs FROM Monitoramento WHERE TraitID='$ttid' AND ";
					$uu = 0;
					$np = count($plantaids_array)-1;
					foreach ($plantaids_array as $kk => $vv) {
						$qu = $qu." PlantaID='".$vv."'";
						if ($uu<$np) {
							$qu = $qu." OR";
						}
						$uu++;
					}
					$qu = $qu." ORDER BY DataObs";
					$resul = mysql_unbuffered_query($qu,$conn);
					$nresul = mysql_numrows($resul);
					while ($resw = mysql_fetch_assoc($resul)) {
						$dd = trim($resw['DataObs']);
						if (!empty($dd)) {
							$dd = str_replace("-","_",$dd);
							$dates_array[] = $dd;
						}
					}
				} elseif (count($censo)>1) {
					$dates_array = $censo;
				} else {
					$dates_array = array(' ');
				}
				unset($tarr);
				foreach ($dates_array as $da) {
					$dda = trim($da);
					if (!empty($dda)) {
						$tnome = $traitname."_".$dda;
					} else {
						$tnome = $traitname;
					}
					//se quantitativo
					if ($tipo=='Variavel|Quantitativo') {
						if ($mean) {
							$tarr = array($tnome." VARCHAR(100)",$tnome."_UNIT  VARCHAR(10)",$tnome."_MEAN  VARCHAR(10)",$tnome."_SD VARCHAR(10)");
						} else {
							$tarr = array($tnome."  VARCHAR(100)",$tnome."_UNIT VARCHAR(10)");
						}
					} else {
						if ($tipo=='Variavel|Texto') {
							$len = 500;
						} else {
							$len = 100;
						}
						$tarr = array($tnome." VARCHAR(".$len.")");
					}
					if ($monitorbydate!=1) {
						$tarr = array_merge((array)$tarr,(array)array($tnome."_DataObs DATE"));
					}
					$resultado = array_merge((array)$resultado,(array)$tarr);
				}
			}
			return $resultado;
		} else {
			return false;
		}
}

function gettraitsasfiels_monitor($id,$typeid,$formid,$mean=false,$censo,$monitorbydate,$conn) {
	//ob_start();
	if ($formid>0) {
		$qq = "SELECT * FROM Formularios WHERE FormID='$formid'";
		$res = mysql_unbuffered_query($qq,$conn);
		$rw = mysql_fetch_assoc($res);
		$traitids = explode(";",$rw['FormFieldsIDS']);
	} else {
		return false;
	}
		$result = array();

		foreach ($traitids as $ttid) {
			unset($traitname);
			unset($variation);
			unset($tnome);


			$aa = "SELECT * FROM Traits WHERE TraitID='$ttid'";
			$rrr = mysql_query($aa,$conn);
			$rw = mysql_fetch_assoc($rrr);
			$tipo = $rw['TraitTipo'];
			$traitname = RemoveAcentos($rw['TraitAsCol']);
			$traitname = strtoupper($traitname);

			$qq = "SELECT * FROM Monitoramento WHERE PlantaID='$id' AND TraitID='$ttid' ORDER BY DataObs";
			$resul = mysql_query($qq,$conn);
			$nrss = mysql_numrows($resul);

			if ($nrss>0) {
				$cz = 0;
				$incenso = 1;
				while ($row = mysql_fetch_assoc($resul)) {
					$charid = $row['TraitID'];
					$variation = trim($row['TraitVariation']);
					$dataobs = trim($row['DataObs']);

					if ($monitorbydate==1) {
						$tnome = $traitname."_".str_replace("-","_",$dataobs);
					} else {
						if (count($censo)>1) { 
							$tnome = $traitname."_".$censo[$cz];
						} else {
							$tnome = $traitname;
						}
					}
					$cz++;

					$needed = in_array($incenso,$censo);
					$incenso++;

					if ($needed) {
						//se imagem
						if ($tipo=='Variavel|Imagem') { 
							if (!empty($variation)) {
								$aarvar = explode(";",$variation);
								$imgarr = array();
								foreach($aarvar as $imgvv) {
									$imgvv = trim($imgvv);
									if (!empty($imgvv)) {
										$qq= "SELECT * FROM Imagens WHERE ImageID='$imgvv'";
										$resimg = mysql_unbuffered_query($qq,$conn);
										$rowimg = mysql_fetch_assoc($resimg);
										$imga = array($rowimg['FileName']);
										$imgarr = array_merge((array)$imgarr, (array)$imga);
									}
								}
								$imagenames = implode(";",$imgarr);
								$catarr = array($tnome => $imagenames);
							} else {
								$catarr = array($tnome => ' ');
							}
						} else {
							//se quantitativo
							if ($tipo=='Variavel|Quantitativo') {
								if (!empty($variation)) {
									$traitkey = 'traitvar_'.$charid;
									if ($mean) {
										include_once("functions/class.Numerical.php") ;
										$aarvar = explode(";",$variation);
										$nv = count($aarvar);
										if ($nv>1) {
											$mean = @round(Numerical::mean($aarvar),1);
											$stdev = @round(Numerical::standardDeviation($aarvar),1);
											$maxvar = max($aarvar);
											$minvar = min($aarvar);
											$catarr = array($tnome => $variation,$tnome.'_UNIT' => $row['TraitUnit'], $tnome."_MEAN" => $mean, $tnome."_SD" => $stdev);
										} elseif ($nv==1) {
											$catarr = array($tnome => $variation,$tnome.'_UNIT' => $row['TraitUnit'], $tnome."_MEAN" => $variation,
											$tnome."_SD" => ' ');
										}

									} else {
											$catarr = array($tnome => $variation,$tnome.'_UNIT' => $row['TraitUnit']);
									}
								} else {
									if ($mean) {
											$catarr = array($tnome => ' ',$tnome.'_UNIT' => $row['TraitUnit'], $tnome."_MEAN" => ' ',
											$tnome."_SD" => ' ');
									} else {
										$catarr = array($tnome => ' ',$tnome.'_UNIT' => $row['TraitUnit']);
									}
								}
							} else {
								if (!empty($variation)) {
									if ($tipo=='Variavel|Categoria') {
											$multiselect = $rw['MultiSelect'];
											if ($multiselect=='Sim') {
												$amulti = explode(";", $row['TraitVariation']);
											} elseif ($tipo=='Variavel|Categoria') {
												$amulti = array($row['TraitVariation']);
											} 
											if (count($amulti)>0) {
												$aar = array();
												foreach ($amulti as $kk => $vvv) {
													$vvv = trim($vvv);
													if (!empty($vvv)) {
														$cat = "SELECT * FROM Traits WHERE TraitID='$vvv'";
														$catres = mysql_unbuffered_query($cat,$conn);
														$catrow = mysql_fetch_assoc($catres);
														$statename = strtolower($catrow['TraitName']);
														$statename = trim($statename);
														$aaaar = array($statename);
														$aar = array_merge((array)$aar, (array)$aaaar);
													}
												}
												$catvalues = implode(';',$aar);
												$catarr = array($tnome => $catvalues);
											} 
									} else {
										$catarr = array($tnome => $variation);
									}
								} else {
									$catarr = array($tnome => ' ');
								}
							}
						} //end trait if (if image)
						if ($monitorbydate!=1) {
							$catarr = array_merge((array)$catarr,(array)array($tnome."_DataObs" => $dataobs));
						}
						$result = array_merge((array)$result,(array)$catarr);
						flush();
					} //end if needed
				} //end while dates
			} //end if there is variation for the trait
		} //end for earch trait in form
		return $result;
}

function gettraitsasfiels($id,$typeid,$formid,$mean=false,$conn) {
	//ob_start();
	if ($formid>0) {
		$qq = "SELECT * FROM Formularios WHERE FormID='$formid'";
		$res = mysql_unbuffered_query($qq,$conn);
		$rw = mysql_fetch_assoc($res);
		$traitids = explode(";",$rw['FormFieldsIDS']);
		$nn=1;
	} else {
		$qq = "SELECT TraitID FROM Traits_variation WHERE $typeid='$id'";
		$rr = mysql_unbuffered_query($qq,$conn);
		if ($rr) {
			$nn = 1;
			$traitids = array();
			while ($rw = mysql_fetch_assoc($rr)) {
				$cid = $rw['TraitID'];
				$traitids = array_merge((array)$traitids,(array)$cid);
			}
		}
	}
	//listtraitsasfieldcols($conn);
	$result = array();
	if ($nn>0) {
		foreach ($traitids as $ttid) {
			unset($traitname);
			unset($variation);

			$aa = "SELECT * FROM Traits WHERE TraitID='$ttid'";
			$rrr = mysql_query($aa,$conn);
			$rw = mysql_fetch_assoc($rrr);
			$tipo = $rw['TraitTipo'];
			$traitname = RemoveAcentos($rw['TraitAsCol']);
			$traitname = strtoupper($traitname);
			//mysql_free_result($rrr);

			//echo "aqui ".$traitname;

			$qq = "SELECT * FROM Traits_variation WHERE $typeid='$id' AND TraitID='$ttid'";
			$resul = mysql_query($qq,$conn);
			$nrss = mysql_numrows($resul);

			if ($nrss>0) {
				$row = mysql_fetch_assoc($resul);
				$charid = $row['TraitID'];
				$variation = trim($row['TraitVariation']);
			} else {
				$charid = $ttid;
				$variation = '';
			}

			mysql_free_result($resul);

			//echo $traitname.": ".$variation."<br>";

			//se imagem
			if ($tipo=='Variavel|Imagem') { 
				if (!empty($variation)) {
					$aarvar = explode(";",$variation);
					$imgarr = array();
					foreach($aarvar as $imgvv) {
						$imgvv = trim($imgvv);
						if (!empty($imgvv)) {
							$qq= "SELECT * FROM Imagens WHERE ImageID='$imgvv'";
							$resimg = mysql_unbuffered_query($qq,$conn);
							$rowimg = mysql_fetch_assoc($resimg);
							$imga = array($rowimg['FileName']);
							$imgarr = array_merge((array)$imgarr, (array)$imga);
						}
					}
					$imagenames = implode(";",$imgarr);
					$catarr = array($traitname => $imagenames);
				} else {
					$catarr = array($traitname => ' ');
				}
			} else {
				//se quantitativo
				if ($tipo=='Variavel|Quantitativo') {
					if (!empty($variation)) {
						$traitkey = 'traitvar_'.$charid;
						if ($mean) {
							include_once("functions/class.Numerical.php") ;
							$aarvar = explode(";",$variation);
							$nv = count($aarvar);
							if ($nv>1) {
								$mean = @round(Numerical::mean($aarvar),1);
								$stdev = @round(Numerical::standardDeviation($aarvar),1);
								$maxvar = max($aarvar);
								$minvar = min($aarvar);
								$catarr = array($traitname => $variation,$traitname.'_UNIT' => $row['TraitUnit'], $traitname."_MEAN" => $mean, $traitname."_SD" => $stdev);
							} elseif ($nv==1) {
								$catarr = array($traitname => $variation,$traitname.'_UNIT' => $row['TraitUnit'], $traitname."_MEAN" => $variation,
								$traitname."_SD" => ' ');
							}

						} else {
								$catarr = array($traitname => $variation,$traitname.'_UNIT' => $row['TraitUnit']);
						}
					} else {
						if ($mean) {
								$catarr = array($traitname => ' ',$traitname.'_UNIT' => $row['TraitUnit'], $traitname."_MEAN" => ' ',
								$traitname."_SD" => ' ');
						} else {
							$catarr = array($traitname => ' ',$traitname.'_UNIT' => $row['TraitUnit']);
						}
					}
				} else {
					if (!empty($variation)) {
						if ($tipo=='Variavel|Categoria') {
								$multiselect = $rw['MultiSelect'];
								if ($multiselect=='Sim') {
									$amulti = explode(";", $row['TraitVariation']);
								} elseif ($tipo=='Variavel|Categoria') {
									$amulti = array($row['TraitVariation']);
								} 
								if (count($amulti)>0) {
									$aar = array();
									foreach ($amulti as $kk => $vvv) {
										$vvv = trim($vvv);
										if (!empty($vvv)) {
											$cat = "SELECT * FROM Traits WHERE TraitID='$vvv'";
											$catres = mysql_unbuffered_query($cat,$conn);
											$catrow = mysql_fetch_assoc($catres);
											$statename = strtolower($catrow['TraitName']);
											$statename = trim($statename);
											$aaaar = array($statename);
											$aar = array_merge((array)$aar, (array)$aaaar);
										}
									}
									$catvalues = implode(';',$aar);
									$catarr = array($traitname => $catvalues);
								} 
						} else {
							$catarr = array($traitname => $variation);
						}
					} else {
						$catarr = array($traitname => ' ');
					}
				}
			} //end trait if (if image)
			//echo(str_repeat('&nbsp;',256));
			//echopre($catarr);
			$result = array_merge((array)$result,(array)$catarr);
			flush();
			//ob_flush();
		} //end for earch trait in form
		//echo "Number of fields: ".count($result)."<br>";
		//echopre($result);
		//ob_end_clean();
		return $result;

	} else {
		return false;
	}
}

function RemoveAcentosEnco($str, $enc = 'UTF-8') {
	$acentos = array(
    'A' => '/&Agrave;|&Aacute;|&Acirc;|&Atilde;|&Auml;|&Aring;/',
    'a' => '/&agrave;|&aacute;|&acirc;|&atilde;|&auml;|&aring;/',
    'C' => '/&Ccedil;/',
    'c' => '/&ccedil;/',
    'E' => '/&Egrave;|&Eacute;|&Ecirc;|&Euml;/',
    'e' => '/&egrave;|&eacute;|&ecirc;|&euml;/',
    'I' => '/&Igrave;|&Iacute;|&Icirc;|&Iuml;/',
    'i' => '/&igrave;|&iacute;|&icirc;|&iuml;/',
    'N' => '/&Ntilde;/',
    'n' => '/&ntilde;/',
    'O' => '/&Ograve;|&Oacute;|&Ocirc;|&Otilde;|&Ouml;/',
    'o' => '/&ograve;|&oacute;|&ocirc;|&otilde;|&ouml;/',
    'U' => '/&Ugrave;|&Uacute;|&Ucirc;|&Uuml;/',
    'u' => '/&ugrave;|&uacute;|&ucirc;|&uuml;/',
    'Y' => '/&Yacute;/',
    'y' => '/&yacute;|&yuml;/',
    'a.' => '/&ordf;/',
    'o.' => '/&ordm;/'
	);
	return preg_replace(array_keys($acentos), array_values($acentos), $str);
    //return preg_replace($acentos, array_keys($acentos), htmlentities($str,ENT_NOQUOTES, $enc));
}

function RemoveAcentos($Msg) {
$a = array(
"/Â|À|Á|Ä|Ã/"=>"A",
"/â|ã|à|á|ä/"=>"a",
"/Ê|È|É|Ë/"=>"E",
"/ê|è|é|ë/"=>"e",
"/Î|Í|Ì|Ï/"=>"I",
"/î|í|ì|ï/"=>"i",
"/Ô|Õ|Ò|Ó|Ö/"=>"O",
"/ô|õ|ò|ó|ö/"=>"o",
"/Û|Ù|Ú|Ü/"=>"U",
"/û|ú|ù|ü/"=>"u",
"/ç/"=>"c",
"/Ç/"=> "C");
	return preg_replace(array_keys($a), array_values($a), $Msg);
}

function tiraacentos($Msg) {
//$Msg = strtolower($Msg);
$aacentos = array('/Â/', '/À/', '/Á/', '/Ä/', '/Ã/', '/Ê/', '/È/', '/É/', '/Ë/', '/Î/', '/Í/', '/Ì/', '/Ï/', '/Ô/', '/Õ/', '/Ò/', '/Ó/', '/Ö/', '/Û/', '/Ù/', '/Ú/', '/Ü/', '/Ç/','/â/', '/à/', '/á/', '/ä/', '/ã/', '/ê/', '/è/', '/é/', '/ë/', '/î/', '/í/', '/ì/', '/ï/', '/ô/', '/ã/', '/ò/', '/ó/', '/ö/', '/û/', '/ù/', '/ú/', '/ü/', '/ç/');
$aahtml = array('A', 'A', 'A', 'A','A', 'E', 'E', 'E', 'E', 'I', 'I', 'I', 'I', 'O', 'O', 'O', 'O', 'O', 'U',  'U', 'U','U', 'C','a', 'a', 'a', 'a','a', 'e', 'e', 'e', 'e', 'i', 'i', 'i', 'i', 'o', 'o', 'o', 'o', 'o', 'u',  'u', 'u','u', 'c');
$a = array_combine($aacentos,$aahtml);
return preg_replace(array_keys($a), array_values($a), $Msg);
}

function replacenumbersbychar($Msg) {
$aacentos = array('/0/', '/1/', '/2/', '/3/', '/4/', '/5/', '/6/', '/7/', '/8/', '/9/');
$aahtml = array('A', 'B', 'C', 'D', 'E', 'F', 'G', 'H', 'I', 'J');
$a = array_combine($aacentos,$aahtml);
return preg_replace(array_keys($a), array_values($a), $Msg);
}

function replacecharbynumbers($Msg) {
$aacentos = array('/A/', '/B/', '/C/', '/D/', '/E/', '/F/', '/G/', '/H/', '/I/', '/J/');
$aahtml = array('0', '1', '2', '3', '4', '5', '6', '7', '8', '9');
$a = array_combine($aacentos,$aahtml);
return preg_replace(array_keys($a), array_values($a), $Msg);
}


function replaceacentoshtmltag($Msg) {
//$Msg = strtolower($Msg);
$aacentos = array('/Â/', '/À/', '/Á/', '/Ä/', '/Ã/', '/Ê/', '/È/', '/É/', '/Ë/', '/Î/', '/Í/', '/Ì/', '/Ï/', '/Ô/', '/Õ/', '/Ò/', '/Ó/', '/Ö/', '/Û/', '/Ù/', '/Ú/', '/Ü/', '/Ç/','/â/', '/à/', '/á/', '/ä/', '/ã/', '/ê/', '/è/', '/é/', '/ë/', '/î/', '/í/', '/ì/', '/ï/', '/ô/', '/ã/', '/ò/', '/ó/', '/ö/', '/û/', '/ù/', '/ú/', '/ü/', '/ç/','/ /');
$aahtml = array('&Acirc;', '&Agrave;', '&Aacute;', '&Auml;','&Atilde;', '&Ecirc;', '&Egrave;', '&Eacute;', '&Euml;', '&Icirc;', '&Iacute;', '&Igrave;', '&Iuml;', '&Ocirc;', '&Otilde;', '&Ograve;', '&Oacute;', '&Ouml;', '&Ucirc;',  '&Ugrave;', '&Uacute;','&Uuml;', '&Ccedil;','&acirc;', '&agrave;', '&aacute;', '&auml;','&atilde;', '&ecirc;', '&egrave;', '&eacute;', '&euml;', '&icirc;', '&iacute;', '&igrave;', '&iuml;', '&ocirc;', '&otilde;', '&ograve;', '&oacute;', '&ouml;', '&ucirc;',  '&ugrave;', '&uacute;','&uuml;', '&ccedil;','&nbsp;');
$a = array_combine($aacentos,$aahtml);
return preg_replace(array_keys($a), array_values($a), $Msg);
}

function strtloweracentos($Msg) {
$Msg = strtolower($Msg);
$arupper = array('/Â/', '/À/', '/Á/', '/Ä/', '/Ã/', '/Ê/', '/È/', '/É/', '/Ë/', '/Î/', '/I/', '/I/', '/I/', '/Ô/', '/Õ/', '/Ò/', '/Ó/', '/Ö/', '/Û/', '/Ù/', '/Ú/', '/Ü/', '/Ç/','Ñ');
$arlower = array('â', 'à', 'á', 'ä','ã', 'ê', 'è', 'é', 'ë', 'î', 'í', 'ì', 'ï', 'ô', 'õ', 'ò', 'ó', 'ö', 'û',  'ù', 'ú','ü', 'ç','/ñ/');
$a = array_combine($arupper,$arlower);
return preg_replace(array_keys($a), array_values($a), $Msg);
}

function strtupperacentos($Msg) {
$Msg = strtoupper($Msg);
$arupper = array('Â', 'À', 'Á', 'Ä', 'Ã', 'Ê', 'È', 'É', 'Ë', 'Î', 'I', 'I', 'I', 'Ô', 'Õ', 'Ò', 'Ó', 'Ö', 'Û', 'Ù', 'Ú', 'Ü', 'Ç','Ñ');
$arlower = array('/â/', '/à/', '/á/', '/ä/','/ã/', '/ê/', '/è/', '/é/', '/ë/', '/î/', '/í/', '/ì/', '/ï/', '/ô/', '/õ/', '/ò/', '/ó/', '/ö/', '/û/',  '/ù/', '/ú/','/ü/', '/ç/','/ñ/');
$a = array_combine($arlower,$arupper);
return preg_replace(array_keys($a), array_values($a), $Msg);
}


function RemoveLetras($Msg) {
	$msg = trim(RemoveAcentos($Msg));
	$msg = strtoupper($msg);
	//$msg =  preg_replace("/[_-&$%]/"," ", $msg);
	//$msg = ereg_replace("[^A-Za-z0-9]", "", $msg);
	$msg =  preg_replace("/[ABCDEFGHIJKLMNOPQRSTUVXYZabcdefghijklmnopq]/"," ", $msg);
	$msg = trim($msg);
	return($msg);
}
function WriteToTXTFile($filename,$texttowrite,$relativepath,$append=FALSE) {
	$fn = $relativepath.$filename;
	if ($append) {
		$fh = fopen($fn, 'a');
	} else {
		$fh = fopen($fn, 'w+');
	}
	$res = fwrite($fh, $texttowrite); //or die("N&atilde;o foi poss&iacute;vel salvar no arquivo $filename em $relativepath");
	fclose($fh);
}

function curPageURL() {
 $pageURL = 'http';
 if ($_SERVER["HTTPS"] == "on") {$pageURL .= "s";}
 $pageURL .= "://";
 if ($_SERVER["SERVER_PORT"] != "80") {
  $pageURL .= $_SERVER["SERVER_NAME"].":".$_SERVER["SERVER_PORT"].$_SERVER["REQUEST_URI"];
 } else {
  $pageURL .= $_SERVER["SERVER_NAME"];
 }
 return $pageURL;
}

function getLatLong($gpspointid,$localid,$conn) {
	if ($gpspointid>0) {
		$qq = "SELECT * FROM GPS_DATA WHERE PointID='$gpspointid'";
		$rr = mysql_query($qq,$conn);
		if ($rr) {
			$row = mysql_fetch_assoc($rr);
			$resarr = array(
			'Latitude' => $row['Latitude']+0,
			'Longitude' => $row['Longitude']+0,
			'Altitude' => $row['Altitude']+0);
		}
	}
	if ((empty($gpspointid) || $gpspointid==0 || $resarr['Longitude']<>0) && $localid>0) {
//GazetteerTIPOtxt,
		$qqq = "SELECT MunicipioID,ProvinceID,CountryID,Country,Province,Municipio,Municipio.Latitude as MuniLat, Municipio.Longitude as MuniLong, Gazetteer.Latitude as GazLat,Gazetteer.Longitude as GazLong, Gazetteer.Altitude as GazAlt, ParentID,Gazetteer FROM Gazetteer JOIN Municipio USING(MunicipioID) JOIN Province USING(ProvinceID)
		JOIN Country USING(CountryID)";
		$qq = $qqq."  WHERE GazetteerID='$localid'";
		$rr = mysql_query($qq,$conn);
		if ($rr) {
			$row = mysql_fetch_assoc($rr);
			$parentID = $row['ParentID'];

			$municoor=FALSE;
				if (empty($latitude) && (!empty($row['MuniLat']) || !empty($row['GazLat']))) {
					if (!empty($row['GazLat'])) {
						$latitude= $row['GazLat'];
					} elseif (!empty($row['MuniLat']) && $parentID==0) {
						$latitude= $row['MuniLat'];
						$municoor=TRUE;

					}
				}
				if (empty($longitude) && (!empty($row['MuniLong']) || !empty($row['GazLong']))) {
					if (!empty($row['GazLong'])) {
						$longitude= $row['GazLong'];
					} elseif (!empty($row['MuniLong']) && $parentID==0) {
						$longitude= $row['MuniLong'];
						$municoor=TRUE;
					}
				}
				if (empty($altitude) && !empty($row['GazAlt'])) {
					$altitude= $row['GazAlt'];
				}

			$i=0;
			while (!empty($parentID) && $parentID>0) {
				$qq = $qqq." WHERE GazetteerID='$parentID'";
				$res = mysql_query($qq,$conn);
				$rs = mysql_fetch_assoc($res);
				//$gazz = $rs['GazetteerTIPOtxt']." ".$rs['Gazetteer'];
				$gazz = $rs['Gazetteer'];
				$parentID = $rs['ParentID'];
				if (empty($latitude) && (!empty($rs['MuniLat']) || !empty($rs['GazLat']))) {
					if (!empty($rs['GazLat'])) {
						$latitude= $rs['GazLat'];
					} elseif (!empty($rs['MuniLat'])) {
						$latitude= $rs['MuniLat'];
						$municoor=TRUE;
					}
				}
				if (empty($longitude) && (!empty($rs['MuniLong']) || !empty($rs['GazLong']))) {
					if (!empty($rs['GazLong'])) {
						$longitude= $rs['GazLong'];
					} elseif (!empty($rs['MuniLong'])) {
						$longitude= $rs['MuniLong'];
						$municoor=TRUE;
					}
				}
				if (empty($altitude) && !empty($rs['GazAlt'])) {
					$altitude= $rs['GazAlt'];
				}
			$i++;
			}
			$resarr = array(
			'Latitude' => $latitude+0,
			'Longitude' => $longitude+0,
			'Altitude' => $altitude+0);
		} 
	} 

	if ($resarr['Longitude']<>0) {
		return $resarr;
	} else {
		return false;
	}
}

function summarizeTaxaFiltro($filtro,$conn) {
$qq = "SELECT * FROM Filtros WHERE FiltroID='".$filtro."'";
$res = @mysql_query($qq,$conn);
$rw = mysql_fetch_assoc($res);
$specarr = explode(";",$rw['EspecimenesIDS']);

$qq = "DROP TABLE Temp_TaxaSummary";
@mysql_query($qq,$conn);

$qq = "CREATE TABLE Temp_TaxaSummary LIKE Especimenes";
mysql_query($qq,$conn);
$nsamp=0;
foreach($specarr as $vv) {
		//HABILITA CHAVE INTERNA
		$forkeyoff = "SET FOREIGN_KEY_CHECKS=0";
		mysql_query($forkeyoff,$conn);
		

		$qq = "INSERT INTO Temp_TaxaSummary SELECT * FROM Especimenes WHERE EspecimenID='".$vv."'";
		mysql_query($qq,$conn);

		//HABILITA CHAVE INTERNA
		$forkeyoff = "SET FOREIGN_KEY_CHECKS=0";
		mysql_query($forkeyoff,$conn);

		$nsamp++;
}

$qq = "SELECT COUNT(DISTINCT Familia) as cfam FROM Temp_TaxaSummary as Ttax JOIN Identidade USING(DetID) JOIN Tax_Familias USING(FamiliaID) WHERE Familia<>'Indet'";
$res = @mysql_query($qq,$conn);
$rw = mysql_fetch_assoc($res);
$nfam = $rw['cfam'];

$qq = "SELECT COUNT(*) as nfamindet FROM Temp_TaxaSummary as Ttax JOIN Identidade USING(DetID) JOIN Tax_Familias USING(FamiliaID) WHERE Familia='Indet'";
$res = @mysql_query($qq,$conn);
$rw = mysql_fetch_assoc($res);
$nfamindet = $rw['nfamindet'];

$qq = "SELECT COUNT(DISTINCT Genero) as cgen FROM Temp_TaxaSummary as Ttax JOIN Identidade USING(DetID) JOIN Tax_Generos USING(GeneroID) WHERE Genero<>'Indet'";
$res = @mysql_query($qq,$conn);
$rw = mysql_fetch_assoc($res);
$ngen = $rw['cgen'];

$qq = "SELECT COUNT(*) as ngenindet FROM Temp_TaxaSummary as Ttax JOIN Identidade USING(DetID) JOIN Tax_Generos USING(GeneroID) WHERE Genero='Indet'";
$res = @mysql_query($qq,$conn);
$rw = mysql_fetch_assoc($res);
$ngenindet = $rw['ngenindet'];

$qq = "SELECT COUNT(DISTINCT Genero,Especie) as csp FROM Temp_TaxaSummary as Ttax JOIN Identidade USING(DetID) JOIN Tax_Generos ON Identidade.GeneroID=Tax_Generos.GeneroID JOIN Tax_Especies USING(EspecieID) WHERE Especie<>'Indet' AND Especie NOT LIKE 'sp.%'";
$res = @mysql_query($qq,$conn);
$rw = mysql_fetch_assoc($res);
$nsp = $rw['csp'];

$qq = "SELECT COUNT(*) as nspindet FROM Temp_TaxaSummary as Ttax JOIN Identidade USING(DetID) JOIN Tax_Generos ON Identidade.GeneroID=Tax_Generos.GeneroID JOIN Tax_Especies USING(EspecieID) WHERE Especie='Indet' OR Especie LIKE 'sp.%'";
$res = @mysql_query($qq,$conn);
$rw = mysql_fetch_assoc($res);
$nspindet = $rw['nspindet'];

$qq = "SELECT COUNT(DISTINCT Genero,Especie,InfraEspecie) as cspinf FROM Temp_TaxaSummary as Ttax JOIN Identidade USING(DetID) JOIN Tax_Generos ON Identidade.GeneroID=Tax_Generos.GeneroID JOIN Tax_Especies ON Identidade.EspecieID=Tax_Especies.EspecieID JOIN Tax_InfraEspecies USING(InfraEspecieID) WHERE InfraEspecie<>'Indet' AND InfraEspecie NOT LIKE 'sp.%' AND Identidade.InfraEspecieID>0";
$res = @mysql_query($qq,$conn);
$rw = mysql_fetch_assoc($res);
$cspinf = $rw['cspinf'];


$qq = "SELECT COUNT(*) as ninfindet FROM Temp_TaxaSummary as Ttax JOIN Identidade USING(DetID) JOIN Tax_Generos ON Identidade.GeneroID=Tax_Generos.GeneroID JOIN Tax_Especies ON Identidade.EspecieID=Tax_Especies.EspecieID JOIN Tax_InfraEspecies USING(InfraEspecieID) WHERE (InfraEspecie='Indet' OR InfraEspecie LIKE 'sp.%') AND Identidade.InfraEspecieID>0";
$res = @mysql_query($qq,$conn);
$rw = mysql_fetch_assoc($res);
$ninfindet = $rw['ninfindet'];


$totalindet = $nfamindet+$ngenindet+$nspindet+$ninfindet;

$results = array("N Total Amostras" => $nsamp, "N Familias" => $nfam, "N Gêneros" => $ngen,  "N Especies" => $nsp, "N InfraEspecies" => $cspinf,'N Total Indet' => $totalindet, 'N Familia Indet' => $nfamindet, "N Gênero Indet" => $ngenindet,"N Especie Indet" => $nspindet,  "N InfraEspecie Indet" => $ninfindet );

return $results;
}

function microtime_float()
{
    list($usec, $sec) = explode(" ", microtime());
    return ((float)$usec + (float)$sec);
}


function puttraitrow2($oldvals,$formid,$idd,$traitids,$conn,$page,$title) {
		//echopre($oldvals);
		$qq = "SELECT * FROM Formularios WHERE FormID='".$formid."'";
		$res = mysql_unbuffered_query($qq,$conn);
		$rr = mysql_fetch_assoc($res);
		$FormFieldsIDS= $rr['FormFieldsIDS'];
		$fieldids = explode(";",$FormFieldsIDS);

		$qq = "SELECT * FROM Traits WHERE ";
		$i=0;
		foreach ($fieldids as $key => $value) {
				if ($i==0) {
					$qq = $qq." TraitID='".$value."'";
				} else {
					$qq = $qq." OR TraitID='".$value."'";
				}
				$i++;
		}
		$qq = $qq." ORDER BY PathName";
		$rr = mysql_query($qq);
		while ($row= mysql_fetch_assoc($rr)) { //para cada variavel no relatorio
				$tt = str_replace(" - "," ",$row['PathName']);

				if ($row['TraitTipo']=='Variavel|Categoria') {
				//opcoes de variaves categoricas

					$qq = "SELECT * FROM Traits WHERE ParentID='".$row['TraitID']."' ORDER BY TraitName";
					$trrw = mysql_query($qq,$conn);

					$nstates = mysql_numrows($trrw);
						//echopre($oldvals);
					if ($nstates>2) {

						//echo "traitvar_".$row['TraitID']."_".$idd." ".$nstates."<br>";
						if (!$page) {
							$valores_array = $oldvals["traitvar_".$row['TraitID']."_".$idd];
						}
						//echopre($valores_array);


						echo "<td title='".$title."'><table ><tr>";
						if ($row['MultiSelect']!='Sim') {
							echo "<td style='border:0'><select name='traitvar_".$row['TraitID']."_".$idd."'>";
							echo "<option value=''>----</option>";

						} else {
							echo "<td style='border:0'><select size='2' name='traitvar_".$row['TraitID']."_".$idd."[]' multiple='yes'>";
						}


						while ($rww= mysql_fetch_assoc($trrw)) { //para cada estado de variacao
							//unset($valor);
							//unset($toe);
							if ($row['MultiSelect']=='Sim') {
									$ttn = "traitmulti_".$row['TraitID']."_".$rww['TraitID'];
									if ($page) {
										$valor =  trim($oldvals[$ttn]);
									} else {
										if (in_array($rww['TraitID'],$valores_array)) {
											$valor = $rww['TraitID'];
										} else {
											unset($valor);
										}
									}
							} else {
								$ttn = "traitvar_".$row['TraitID'];
								if ($page) {
									$valor =  trim($oldvals[$ttn]);
								} else {
									$valor =  trim($oldvals[$ttn."_".$idd]);
								}
							}
							echo "<option ";
							if (!empty($valor) && $valor==$rww['TraitID']) {
								echo " selected ";
							}
							echo " value='".$rww['TraitID']."'>".$rww['TraitName']."</option>";
						} 
						if ($row['MultiSelect']=='Sim') {
							echo "<option value=''>----</option>";
						}
						echo "</select></td>";
					} else {
						if (!$page) {
							$valores = $oldvals["traitvar_".$row['TraitID']."_".$idd];
						}
						if ($row['MultiSelect']!='Sim') {
							$tttipo = 'radio';
						} else {
							$tttipo = 'checkbox';
						}
					echo "<td style='border:0'><table><tr>";
						while ($rww= mysql_fetch_assoc($trrw)) {
							if ($row['MultiSelect']=='Sim') {
									$ttn = "traitmulti_".$row['TraitID']."_".$rww['TraitID'];
									if ($page) {
										$valor =  trim($oldvals[$ttn]);
									} else {
										if (in_array($rww['TraitID'],$valores)) {
											$valor = $rww['TraitID'];
										} else {
											unset($valor);
										}
									}
									$varn = "traitvar_".$row['TraitID']."_".$idd."[]";
							} else {
								$ttn = "traitvar_".$row['TraitID'];
								if ($page) {
									$valor =  trim($oldvals[$ttn]);
								} else {
									$valor =  trim($oldvals[$ttn."_".$idd]);
								}
								$varn = "traitvar_".$row['TraitID']."_".$idd;
							}
							echo "
							<td align='right'>
								<input type='".$tttipo."' name='".$varn."' ";
							if (!empty($valor) && $valor==$rww['TraitID']) {
								echo " checked ";
							}
							echo " value='".$rww['TraitID']."'></td>
							<td align='left'>".$rww['TraitName']."</td>" ;
						}
					}
					echo "</tr></table></td>";
				}


				//se quantitativo
				if ($row['TraitTipo']=='Variavel|Quantitativo') {
					$string = 'traitvar_'.$row['TraitID'];
					if ($page) {
						$val =  trim($oldvals[$string]);
						} else {
						$val =  trim($oldvals[$string."_".$idd]);
					}
					echo "
					<td  title='".$title."'><table border=0>
						<tr><td style='border:0' >
							<input type='text' name='traitvar_".$row['TraitID']."_".$idd."' value='$val' size='10'>";
						echo "</td>
						<td style='border:0' >
							<select name='traitunit_".$row['TraitID']."_".$idd."' style=\"width: 20mm\" >";

							$string = 'traitunit_'.$row['TraitID'];
							if ($page) {
								$val =  trim($oldvals[$string]);
							} else {
								$val =  trim($oldvals[$string."_".$idd]);
							}
							$vnamearr = array();
							if (empty($val) && !empty($row['TraitUnit'])) {
								$vnamearr[] = $row['TraitUnit'];
								echo "<option selected value='".$row['TraitUnit']."'>".$row['TraitUnit']."</option>";
							} elseif (!empty($val)) {
								$vnamearr[] = $val;
								echo "<option selected value='".$val."'>".$val."</option>";
							}
							$qq = "SELECT DISTINCT TraitUnit FROM Traits WHERE TraitUnit<>'' ORDER BY TraitUnit ASC";
							$res = mysql_query($qq,$conn);
							if ($res) {
								while ($rwu=mysql_fetch_assoc($res)) {
									$varname = $rwu['TraitUnit'];
									echo "<option value='".$varname."'>".$varname."</option>";
									$vnamearr[] = $varname;
								}
							} 

								$qq = "SELECT * FROM VarLang WHERE VariableName LIKE '%traitunit%' ORDER BY '$lang' ASC";
								$rs = mysql_query($qq,$conn);
								if ($rs) {
									while ($rwu=mysql_fetch_assoc($rs)) {
										$varname = $rwu['VariableName'];
										if (!in_array($varname,$vnamearr)) {
											$zz = explode("_",$varname);
											if ($zz[1]!='desc') {
												echo "<option value='".GetLangVar($varname)."'>".GetLangVar($varname)."</option>";
											}
										}
									}
								}

					echo "</select>
					</td></tr>
					</table></td>
					";
				}

				//se texto
				if ($row['TraitTipo']=='Variavel|Texto') {
					//echo "<input type=hidden name='traitnone_".$row['TraitID']."_".$idd."' value='none'>";
					$string = 'traitvar_'.$row['TraitID'];
					if ($page) {
						$val = $oldvals[$string];
					} else {
						$val= $oldvals[$string."_".$idd];
					}
					//tem um problema aqui quando apaga os dados
					echo "<td  title='".$title.": ".$val."'><textarea name='traitvar_".$row['TraitID']."_".$idd."' cols='30' rows='1' >".$val."</textarea></td>";
				}
		}//end of loop de cada variavel relatorio
}


function puttraitrow($oldvals,$formid,$idd,$fieldids,$conn) {
		//echopre($oldvals);
		if ($formid>0) {
			$qq = "SELECT * FROM Formularios WHERE FormID='".$formid."'";
			$res = mysql_unbuffered_query($qq,$conn);
			$rr = mysql_fetch_assoc($res);
			$FormFieldsIDS= $rr['FormFieldsIDS'];
			$fieldids = explode(";",$FormFieldsIDS);
		} 
		$qq = "SELECT * FROM Traits WHERE ";
		$i=0;
		foreach ($fieldids as $key => $value) {
				if ($i==0) {
					$qq = $qq." TraitID='".$value."'";
				} else {
					$qq = $qq." OR TraitID='".$value."'";
				}
				$i++;
		}
		$qq = $qq." ORDER BY PathName";
		$rr = mysql_query($qq);
		while ($row= mysql_fetch_assoc($rr)) { //para cada variavel no relatorio
				$tt = str_replace(" - "," ",$row['PathName']);
				$ttnome = "<font size=0.8em color='red'>".str_replace(" ","_",ucfirst(strtolower($tt))).":</font>";

				if ($row['TraitTipo']=='Variavel|Categoria') {
				//opcoes de variaves categoricas

					$qq = "SELECT * FROM Traits WHERE ParentID='".$row['TraitID']."' ORDER BY TraitName";
					$trrw = mysql_query($qq,$conn);

					$nstates = mysql_numrows($trrw);

					if ($nstates>2) {
						echo "<td><table ><tr><td align='left'>".$ttnome."</td>";
						if ($row['MultiSelect']!='Sim') {
							echo "<td><select name='traitvar_".$row['TraitID']."_".$idd."'>";
						} else {
							echo "<td><select size='4' name='traitvar_".$row['TraitID']."_".$idd."[]' multiple='yes'>";
						}
						if ($row['MultiSelect']!='Sim') {
							echo "<option value=''>".GetLangVar('nameselect')."</option>";
						} else {
							echo "<option value=''>&nbsp;...&nbsp;</option>";
						}
						while ($rww= mysql_fetch_assoc($trrw)) { //para cada estado de variacao
							//unset($valor);
							//unset($toe);
							if ($row['MultiSelect']=='Sim') {
									$ttn = "traitmulti_".$row['TraitID']."_".$rww['TraitID'];
									$valor =  trim($oldvals[$ttn]);
							} else {
								$ttn = "traitvar_".$row['TraitID'];
								$valor =  trim($oldvals[$ttn]);
							}
							echo "<option ";
							if (!empty($valor) && $valor==$rww['TraitID']) {
								echo " selected ";
							}
							echo " value='".$rww['TraitID']."'>".$rww['TraitName']."</option>";
						} 
						echo "</select></td>";
					} else {
						if ($row['MultiSelect']!='Sim') {
							$tttipo = 'radio';
						} else {
							$tttipo = 'checkbox';
						}
						echo "<td><table class='dettable'><tr><td colspan=100% align='left'>".$ttnome."</td></tr><tr>";
						while ($rww= mysql_fetch_assoc($trrw)) {
							if ($row['MultiSelect']=='Sim') {
									$ttn = "traitmulti_".$row['TraitID']."_".$rww['TraitID'];
									$valor =  trim($oldvals[$ttn]);
									$varn = "traitvar_".$row['TraitID']."_".$idd."[]";
							} else {
								$ttn = "traitvar_".$row['TraitID'];
								$valor =  trim($oldvals[$ttn]);
								$varn = "traitvar_".$row['TraitID']."_".$idd;
							}
							echo "
							<td align='right'>
								<input type='".$tttipo."' name='".$varn."' ";
							if (!empty($valor) && $valor==$rww['TraitID']) {
								echo " checked ";
							}
							echo " value='".$rww['TraitID']."'></td><td align='left'>".$rww['TraitName']."</td>" ;
						}
					}
					echo "</tr></table></td>";
				}


				//se quantitativo
				if ($row['TraitTipo']=='Variavel|Quantitativo') {
					$string = 'traitvar_'.$row['TraitID'];
					$val = $oldvals[$string];
					echo "
					<td><table>
						<tr><td>".$ttnome."</td></tr>
						<tr><td>
							<input type='text' name='traitvar_".$row['TraitID']."_".$idd."' value='$val' size='10'>";
						echo "</td>
						<td >
							<select style=\"width: 15mm\" name='traitunit_".$row['TraitID']."_".$idd."'>";

							$string = 'traitunit_'.$row['TraitID'];
							$val = $oldvals[$string];
							if (empty($val) && !empty($row['TraitUnit'])) {
								echo "<option selected value='".$row['TraitUnit']."'>".$row['TraitUnit']."</option>";
							} elseif (!empty($val)) {
								echo "<option selected value='".$val."'>".$val."</option>";
							}
							$qq = "SELECT * FROM VarLang WHERE VariableName LIKE '%traitunit%' ORDER BY '$lang' ASC";
							$res = mysql_query($qq,$conn);
							if ($res) {
							while ($rwu=mysql_fetch_assoc($res)) {
								$varname = $rwu['VariableName'];
								$zz = explode("_",$varname);
								if ($zz[1]!='desc') {
									$subsname = 'traitunit'.$menugrp;
									echo "<option value='".GetLangVar($varname)."'>".GetLangVar($varname)."</option>";
								}
							}
							}
					echo "</select>
					</td></tr>
					</table></td>
					";
				}


				//se imagem
				if ($row['TraitTipo']=='Variavel|Imagem') {
					$string = 'trait_'.$row['TraitID'];
					$imgfile = 'traitimg_'.$row['TraitID'];
					$val = explode(";",$oldvals[$string]);

					$oldimgvals = $oldvals[$string];
					echo	"<td>
					<table>
					<tr><td>".$ttnome."</td><td><font size=0.8em color='red'>Fotografos</font></td></tr>
					<tr><td>";

					if (count($val)>0) {
						echo "<input type=hidden name ='traitimgold_".$row['TraitID']."_".$idd."' value='".$oldimgvals."'>";
							foreach ($val as $kk => $vv) {
								$vv = trim($vv);
								if (!empty($vv)) {
								$qq = "SELECT * FROM Imagens WHERE ImageID='$vv'";
								$rt = mysql_query($qq,$conn);
								$rtw = mysql_fetch_assoc($rt);
								$path = "img/originais/";
								$imagid = $rtw['ImageID'];
								$filename = trim($rtw['FileName']);

								$autor = $rtw['Autores'];
								$autorarr = explode(";",$autor);
								if (count($autorarr)>0) {
									$j=1;
									foreach ($autorarr as $aut) {
										$qq = "SELECT * FROM Pessoas WHERE PessoaID='".$aut."'";
											$res = mysql_query($qq,$conn);
											$rwr = mysql_fetch_assoc($res);
										if ($j==1) {
											$autotxt = 	$rwr['Abreviacao'];
										} else {
											$autotxt = $autotxt."; ".$rwr['Abreviacao'];
										}
										$j++;
									}
								} 
								$fotodata = $rtw['DateOriginal'];
								if (file_exists($path.$filename)) {
									$pthumb = "img/thumbnails/";
									if (!file_exists($pthumb.$filename)) {
											createthumb($path.$filename,$pthumb.$filename,80,80);
									}
									$imgbres = "img/copias_baixa_resolucao/";
									if (!file_exists($imgbres.$filename)) {
										$zz = getimagesize($path.$filename);
										$width=$zz[0];
										$height = $zz[1];
										if ($width>1200 || $height>1200) {
											createthumb($path.$filename,$imgbres.$filename,1200,1200);
										} else {
											createthumb($path.$filename,$imgbres.$filename,$width,$height);
										}
									}

								$fn = explode("_",$filename);
								unset($fn[0]);
								unset($fn[1]);
								$fn = implode("_",$fn);


									$fntxt = $fn."   [";
									if (!empty($autotxt)) { $fntxt = $fntxt." ".GetLangVar('namefotografo').": ".$autotxt." - ".$fotodata."]";} else {
										$fntxt = $fntxt.$fotodata."]";
									}

									echo "<table class='clean'>
									<tr class='cl' >
									<td class='cl' >
									<a href=\"".$imgbres.$filename."\" class='MagicZoomPlus'  rel=\"zoom-position:right;zoom-height:200px; zoom-fade:true; smoothing-speed:17;opacity-reverse:true;\" >
									<img width=\"40\" src=\"".$pthumb.$filename."\"/>
									</a>
									</td>
									<td class='cl' >&nbsp;</td>
									<td class='tinny' id='fname_".$row['TraitID']."_".$imagid."_".$idd."'  class='tdformnotes'>$fntxt</td>";
									$fndeleted = "<STRIKE>$fn</STRIKE>";
									echo "<input type='hidden' id='fnamedeleted_".$row['TraitID']."_".$imagid."_".$idd."' value='$fndeleted'>";
									echo "<input type='hidden' id='imgtodel_".$row['TraitID']."_".$imagid."_".$idd."' name='imgtodel_".$row['TraitID']."_".$imagid."_".$idd."' value=''>";
									echo "<input type='hidden' id='imagid_".$row['TraitID']."_".$imagid."_".$idd."' name='imagid_".$row['TraitID']."_".$imagid."_".$idd."' value='$imagid'>";
									echo "<input type='hidden' id='fnameundeleted".$row['TraitID']."_".$imagid."_".$idd."' value='$fn'>";

									echo "<td class='cl' ><img height=14 src=\"icons/application-exit.png\"";
									echo	" onclick=\"javascript:deletimage('fnamedeleted_".$row['TraitID']."_".$imagid."_".$idd."','fname_".$row['TraitID']."_".$imagid."_".$idd."','imgtodel_".$row['TraitID']."_".$imagid."_".$idd."',1);\">
									</td>
									<td class='cl' ><img height=14 src=\"icons/list-add.png\"";
									echo	" onclick=\"javascript:deletimage('fnameundeleted".$row['TraitID']."_".$imagid."_".$idd."','fname_".$row['TraitID']."_".$imagid."_".$idd."','imgtodel_".$row['TraitID']."_".$imagid."_".$idd."',0);\">
									</td>
									</tr>
									</table>";
								} else {
									$refname = 'traitimg_'.$row['TraitID'];
									$val = eval('unset($'.$refname.');');
								}
								}

							}
					}
					$varname = 'trait_'.$row['TraitID']."_".$idd;
					echo "<input type=\"file\" name=\"$varname\"> 
							<script type=\"text/javascript\">
								window.addEvent('domready', function(){
									new MultiUpload($( 'varform2' ).$varname );});
							</script>
								<input type=hidden name='traitimg_".$row['TraitID']."_".$idd."' value='imagem'>
						</td>
						<td class='cl' align='left'>
							<select name='traitimgautor_".$row['TraitID']."_".$idd."[]' multiple size=3>";
								$wrr = getpessoa('',$abb=TRUE,$conn);
								echo "<option value=''>---</option>";
								while ($aa = mysql_fetch_assoc($wrr)){
									if ($aa['Abreviacao']) {
										echo "<option value='".$aa['PessoaID']."'>".$aa['Abreviacao']."</option>";
									}
								}
							echo "</select>
						</td>
						</tr>
					</table>
					</td>";
				}

				//se texto
				if ($row['TraitTipo']=='Variavel|Texto') {
					echo "<input type=hidden name='traitnone_".$row['TraitID']."_".$idd."' value='none'>";
					$string = 'traitvar_'.$row['TraitID'];
					if (!isset($_POST[$string])) {
						$val = $oldvals[$string];
					} else {
						$val= $_POST[$string];
					}
					//tem um problema aqui quando apaga os dados
					echo "<td class='cl'>".$ttnome."<br><textarea name='traitvar_".$row['TraitID']."_".$idd."' cols='20' rows='2' >".$val."</textarea></td>";
				}
		}//end of loop de cada variavel relatorio

}


function getcompletename($detid,$conn) {
	$qq = "SELECT * FROM Identidade WHERE DetID='$detid'";
	$res = mysql_unbuffered_query($qq,$conn);
	$rw = mysql_fetch_assoc($res);
	$infraspid = trim($rw['InfraEspecieID']);
	$speciesid = trim($rw['EspecieID']);
	$genusid = trim($rw['GeneroID']);
	$famid = trim($rw['FamiliaID']);
	if (!empty($infraspid)) {
			$qu = "SELECT Tax_InfraEspecies.*,Tax_Especies.Especie,Tax_Especies.EspecieAutor,Tax_Especies.BasionymAutor as EspecieBasio,Tax_Generos.Genero,Tax_Generos.FamiliaID FROM Tax_InfraEspecies JOIN Tax_Especies USING(EspecieID) JOIN Tax_Generos USING(GeneroID) WHERE InfraEspecieID='$infraspid'";
	} else {
		if (!empty($speciesid)) {
			$qu = "SELECT Tax_Especies.*,Tax_Generos.Genero,Tax_Generos.FamiliaID FROM Tax_Especies JOIN Tax_Generos USING(GeneroID) WHERE EspecieID='$speciesid'";
		} else {
			if (!empty($genusid)) {
				$qu = "SELECT * FROM Tax_Generos WHERE GeneroID='$genusid'";
			} else {
				$qu = "SELECT * FROM Tax_Familias WHERE FamiliaID='$famid'";
			}
		}
	}
	$query = mysql_query($qu,$conn);
	$rww = mysql_fetch_assoc($query);
	$famid = $rww['FamiliaID'];
	$genusid = $rww['GeneroID'];
	$genus = $rww['Genero'];

	$sinonimos = '';

	if ($infraspid>0)  {
		$infrasp = trim($rww['InfraEspecie']);
		$infraspautor = $rww['InfraEspecieAutor'];
		$infrabasautor = $rww['BasionymAutor'];
		$subvar = $rww['InfraEspecieNivel'];
		$spnome = $rww['Especie'];
		$spautor = $rww['EspecieAutor'];
		$spbasautor = trim($rww['EspecieBasio']);
			$sinonimos = $rww['Sinonimos'];

	} else {
		if ($speciesid>0) {
			$spnome = $rww['Especie'];
			$spautor = $rww['EspecieAutor'];
			$spbasautor = $rww['BasionymAutor'];
				$sinonimos = $rww['Sinonimos'];

		} 
	}
	$nome = "<b><i>".$genus." ".$spnome."</i></b>";
	if (!empty($spbasautor)) {
		$spbas = str_replace("(","",$spbasautor);
		$spbas = str_replace(")","",$spbas);
		$nome = $nome." (".$spbas.")";
	}
	$nome = $nome." ".$spautor;
	if (!empty($infrasp)) {
		$nome = $nome." ".$subvar." <b><i>".$infrasp."</i></b>";
		if (!empty($infrabasautor)) {
			$ifbas = str_replace("(","",$infrabasautor);
			$ifbas = str_replace(")","",$ifbas);
			$nome = $nome." (".$ifbas.") ";
		}
		$nome = $nome." ".$infraspautor;
	}
	$pubrevista = trim($rww['PubRevista']);
	$pubvolume = trim($rww['PubVolume']);
	$pubano = trim($rww['PubAno']);
	if (!empty($pubrevista) && !empty($spnome)) {
		if ($pubano==0) { $pubano = "<b>?data?</b>";}
		$nome = $nome.", ".$pubrevista.", ".$pubvolume.", ".$pubano.".";
	}
	//$sinonimos = $rww['Sinonimos'];
	return array($nome,$sinonimos);
}

function getnome($id,$idlevel,$conn) {
	if ($idlevel=='especie') { $speciesid=$id;}
	if ($idlevel=='infraespecies') { $infraspid=$id;}

	if (!empty($infraspid)) {
			$qu = "SELECT Tax_InfraEspecies.*,Tax_Especies.Especie,Tax_Especies.EspecieAutor,Tax_Especies.BasionymAutor as EspecieBasio,Tax_Generos.Genero,Tax_Generos.FamiliaID FROM Tax_InfraEspecies JOIN Tax_Especies USING(EspecieID) JOIN Tax_Generos USING(GeneroID) WHERE InfraEspecieID='$infraspid'";
	} else {
		if (!empty($speciesid)) {
			$qu = "SELECT Tax_Especies.*,Tax_Generos.Genero,Tax_Generos.FamiliaID FROM Tax_Especies JOIN Tax_Generos USING(GeneroID) WHERE EspecieID='$speciesid'";
		} 
	}
	$query = mysql_query($qu,$conn);
	$rww = mysql_fetch_assoc($query);
	$genusid = $rww['GeneroID'];
	$genus = $rww['Genero'];

	if ($infraspid>0)  {
		$infrasp = trim($rww['InfraEspecie']);
		$infraspautor = $rww['InfraEspecieAutor'];
		$infrabasautor = $rww['BasionymAutor'];
		$subvar = $rww['InfraEspecieNivel'];
		$spnome = $rww['Especie'];
		$spautor = $rww['EspecieAutor'];
		$spbasautor = trim($rww['EspecieBasio']);
	} else {
		if ($speciesid>0) {
			$spnome = $rww['Especie'];
			$spautor = $rww['EspecieAutor'];
			$spbasautor = $rww['BasionymAutor'];
		} 
	}
	$nome = "<i>".$genus." ".$spnome."</i>";
	if (!empty($spbasautor)) {
		$spbas = str_replace("(","",$spbasautor);
		$spbas = str_replace(")","",$spbas);
		$nome = $nome." (".$spbas.")";
	}
	$nome = $nome." ".$spautor;
	if (!empty($infrasp)) {
		$nome = $nome." ".$subvar." <i>".$infrasp."</i>";
		if (!empty($infrabasautor)) {
			$ifbas = str_replace("(","",$infrabasautor);
			$ifbas = str_replace(")","",$ifbas);
			$nome = $nome." (".$ifbas.") ";
		}
		$nome = $nome." ".$infraspautor;
	}
	$pubrevista = trim($rww['PubRevista']);
	$pubvolume = trim($rww['PubVolume']);
	$pubano = trim($rww['PubAno']);
	if (!empty($pubrevista) && !empty($spnome)) {
		if ($pubano==0) { $pubano = "<b>?data?</b>";}
		$nome = $nome.", ".$pubrevista.", ".$pubvolume.", ".$pubano.".";
	}
	//$sinonimos = $rww['Sinonimos'];
	return $nome;
}


function getmonthroman($mm) {
	$mmarr = array('I','II','III','IV','V','VI','VII','VIII','IX','X','XI','XII');
	$month = $mmarr[$mm];
	return $month;
}

function getfertility($specid,$traitfertid,$conn) {
	$qq = "SELECT * FROM Traits_variation WHERE EspecimenID='".$specid."' AND TraitID='".$traitfertid."'";
	$res = mysql_query($qq,$conn);
	$rr = mysql_fetch_assoc($res);
	$var = trim($rr['TraitVariation']);
	if (!empty($var)) {
		$arvar = explode(";",$var);
		$fert = array();
		foreach ($arvar as $vv) {
			$qq = "SELECT * FROM Traits WHERE TraitID='".$vv."'";
			$rses = mysql_query($qq,$conn);
			$rrs = mysql_fetch_assoc($rses);
			$var = strtolower($rrs['TraitName']);
			//echo $var." ".substr($var,1,2)."<br>";

			if (substr($var,0,2)=='fl') {
				$fert = array_merge((array)$fert,(array)array("fl"));
			}
			if (substr($var,0,2)=='fr') {
				$fert = array_merge((array)$fert,(array)array("fr"));
			}
			if (substr($var,0,3)=='bot' || substr($var,1,2)=='bud') {
				$fert = array_merge((array)$fert,(array)array("bt"));
			}
			if (substr($var,0,2)=='es' || substr($var,1,2)=='st') {
				$fert = array_merge((array)$fert,(array)array("st"));
			}
		}
		$resultado = implode(";",$fert);
	} else {
		$resultado = '';
	}
	return $resultado;
}


function makedescription($iddsarr,$formid,$typeid,$img=FALSE,$conn) {
		//include_once("functions/class.Numerical.php") ;
		//$qq = "SELECT DISTINCT tvar.TraitID,tnames.TraitName,tnames.PathName,tnames.TraitTipo,tnames.TraitUnit FROM Traits_variation as tvar JOIN Traits as tnames USING(TraitID) WHERE (";
//		$ii=0;
//		$nn = count($iddsarr)-1;
//		foreach ($iddsarr as $vv) {
//			if ($ii<$nn) {
//				$qq = $qq.$typeid."='".$vv."' OR ";
//			} elseif ($ii==$nn) {
//				$qq = $qq.$typeid."='".$vv."'";
//			}
//			$ii++;
//		}
//		$qq = $qq.")";
		$traitids = array();
		if ($formid>0) {
			$qu = "SELECT * FROM Formularios WHERE FormID='$formid'";
			$res = mysql_query($qu,$conn);
			$rzw = mysql_fetch_assoc($res);
			$traitids = explode(";",$rzw['FormFieldsIDS']);

		} else {
			return FALSE;
		}

		$mydescription = '';
		$oldpname = '';
		$nnrr = count($traitids)-1;
		$ih =0;
		foreach ($traitids as $cid) {
			//while ($rw = mysql_fetch_assoc($rr)) {
			//$
			//$cid = $rw['TraitID'];
			//$traittipo = trim($rw['TraitTipo']);
			//$varunit = trim($rw['TraitUnit']);

			//echo $cid." ".$rw['TraitName']." ".$rw['TraitID'];

			$qq = "SELECT tvar.TraitID,tnames.TraitName,tnames.PathName,tvar.TraitVariation,tvar.TraitUnit,tnames.TraitTipo FROM Traits_variation as tvar JOIN Traits as tnames USING(TraitID) WHERE TraitID='".$cid."' AND (";
			$ii=0;
			$nn = count($iddsarr)-1;
			foreach ($iddsarr as $vv) {
				if ($ii<$nn) {
					$qq = $qq.$typeid."='".$vv."' OR ";
				} elseif ($ii==$nn) {
					$qq = $qq.$typeid."='".$vv."'";
				}
				$ii++;
			}
			$qq = $qq.")";

			//merge variation for specimens
			$rwr = mysql_query($qq,$conn);
			$nrwr = mysql_numrows($rwr);
			$variation = array();
			$hu=0;
		if ($nrwr>0) {
			while ($rww = mysql_fetch_assoc($rwr)) {
					if ($hu==0) {
						$pathname = $rww['PathName'];
						$traittipo = $rww['TraitTipo'];
						$traitname = $rww['TraitName'];
						$varunit = $rww['TraitUnit'];
					}
					$hu++;
					$vvar = trim($rww['TraitVariation']);
					//echo $vvar." ".$traittipo."<br>";
					if (!empty($vvar)) {
						if ($traittipo=='Variavel|Categoria') {
							$aarvar = explode(";",$vvar);
							$nvar = count($aarvar);
							$i =1;
							foreach ($aarvar as $kk => $val) {
								$qq = "SELECT * FROM Traits WHERE TraitID='$val'";
								$ror = mysql_query($qq,$conn);
								$rwo = mysql_fetch_assoc($ror);
								$varsing = strtolower($rwo['TraitName']);
								$variation  = array_merge((array)$variation,(array)array($varsing));
								mysql_free_result($ror);
								unset($rwo);
							}
						}
						if ($traittipo=='Variavel|Quantitativo') {
							$aarvar = explode(";",$vvar);
							$variation  = array_merge((array)$variation,(array)$aarvar);
							//echopre($variation);
						}
						if ($traittipo=='Variavel|Texto') {
							$variation  = array_merge((array)$variation,(array)array($vvar));
						}

						if ($traittipo=='Variavel|Imagem') {

						}
					}
					//echopre($variation);
			}
			//summarize variation
			$varname = '';
			if (count($variation)>0) {
					if ($traittipo=='Variavel|Categoria') {
						$aarvar = array_count_values($variation);
						$nvar = count($aarvar)-1;
						$ik= 0;
						foreach ($aarvar as $kkk => $vall) {
							if ($ik<$nvar) {
								$varname = 	$varname.$kkk." (N=".$vall."), ";
							} elseif ($ik==$nvar) {
								$varname = 	$varname.$kkk." (N=".$vall.")";
							}
							$ik++;
						}
					}
					if ($traittipo=='Variavel|Quantitativo') {
							$aarvar = $variation;
							$nv = count($aarvar);
							if ($nv>1) {
								$mean = @round(Numerical::mean($aarvar),1);
								$stdev = @round(Numerical::standardDeviation($aarvar),1);
								$maxvar = max($aarvar);
								$minvar = min($aarvar);
								if (substr($varunit,0,2)!='nu') {
									$varname = $mean."+/-".$stdev." [".$minvar."-".$maxvar."] ".strtolower($varunit);
								} else {
									$varname = $minvar."-".$mean."-".$maxvar." ".strtolower($varunit);
								}
								$varname = $varname." (N=".$nv.")";
							} elseif ($nv==1) {
								$varname = trim($varname).$variation[0]." ".$varunit;
							}
					}
					if ($traittipo=='Variavel|Texto') {
							$aarvar  = array_unique($variation);
							$varname = implode(". ",$aarvar);
					}
					if ($traittipo=='Variavel|Imagem') {
						}

			}

			//$pathname = trim($rw['PathName']);
			//$traitname = trim($rw['TraitName']);

			$basename = explode("-",$pathname);
			$nbase = count($basename)-2;
			$pbase = $basename[$nbase];

			//echo " ".$pathname." <br>";
			//actual description
			if (!empty($varname)) {
				if ($oldpname!=$pbase) { 
					$nname = "<b>".strtoupper($pbase)."</b> - ".$traitname;
					$oldpname = $pbase;
				} else {
					$nname = $traitname;
				}
				if ($ih==0) {
					$mydescription = $mydescription." ".$nname.": ".$varname;
				}
				if ($ih<=$nnrr && $ih>0) {
					$mydescription = $mydescription.".  ".$nname.": ".$varname;
				}
				$ih++;
			}
			//echo $mydescription." ".$traittipo."<br>";
			unset($varname,$nname,$traitname,$pathname,$varunit);
		}
	}
		$mydescription = $mydescription.".";
		return $mydescription;
}

function summarize_variables_array($iddsarr,$formid,$typeid,$conn) {
		$traitids = array();
		if ($formid>0) {
			$qu = "SELECT * FROM Formularios WHERE FormID='$formid'";
			$res = mysql_query($qu,$conn);
			$rzw = mysql_fetch_assoc($res);
			$traitids = explode(";",$rzw['FormFieldsIDS']);
		} else {
			return FALSE;
		}
		$mydescription = array();
		$specarray = array();
		$ttraitsids = array();
		foreach ($traitids as $cid) {
			$cid = trim($cid);
			$qq = "SELECT tvar.TraitID,tnames.TraitName,tnames.PathName,tvar.TraitVariation,tvar.TraitUnit,tnames.TraitTipo,tnames.TraitUnit as DefUnit,tvar.".$typeid." FROM Traits_variation as tvar JOIN Traits as tnames USING(TraitID) WHERE TraitID='".$cid."' AND (";
			$ii=0;
			$nn = count($iddsarr)-1;
			foreach ($iddsarr as $vv) {
				if ($ii<$nn) {
					$qq = $qq.$typeid."='".$vv."' OR ";
				} elseif ($ii==$nn) {
					$qq = $qq.$typeid."='".$vv."'";
				}
				$ii++;
			}
			$qq = $qq.")";

			//merge variation for specimens
			$rwr = mysql_query($qq,$conn);
			$nrwr = mysql_numrows($rwr);
			$variation = array();
			$hu=0;
			$specids = array();
		if ($nrwr>0) {
			while ($rww = mysql_fetch_assoc($rwr)) {
					if ($hu==0) {
						$pathname = $rww['PathName'];
						$traittipo = $rww['TraitTipo'];
						$traitname = $rww['TraitName'];
						$varunit = $rww['TraitUnit'];
					}
					$hu++;
					$vvar = trim($rww['TraitVariation']);
					//echo $vvar." ".$traittipo."<br>";
					if (!empty($vvar)) {
						if ($traittipo=='Variavel|Categoria') {
							$spid = $rww[$typeid];
							$specids = array_merge((array)$specids,(array)array($spid));
							$aarvar = explode(";",$vvar);
							$nvar = count($aarvar);
							$i =1;
							foreach ($aarvar as $kk => $val) {
								$qq = "SELECT * FROM Traits WHERE TraitID='$val'";
								$ror = mysql_query($qq,$conn);
								$rwo = mysql_fetch_assoc($ror);
								$varsing = strtolower($rwo['TraitName']);
								$variation  = array_merge((array)$variation,(array)array($varsing));
								mysql_free_result($ror);
								unset($rwo);

							}
						}
						if ($traittipo=='Variavel|Quantitativo') {
							$spid = $rww[$typeid];
							$specids = array_merge((array)$specids,(array)array($spid));

							$uni = $rww['TraitUnit'];
							$defuni = $rww['DefUnit'];
							$aarvar = explode(";",$vvar);
							if ($uni!=$defuni) {
								if ($defuni=='metros') {
									if ($uni=='cm') {
										foreach ($aarvar as $kk => $vv) {
											$aarvar[$kk] = $vv/100;
										}
									} elseif ($uni=='mm') {
										foreach ($aarvar as $kk => $vv) {
											$aarvar[$kk] = $vv/1000;
										}
									}
								}
								if ($defuni=='cm') {
									if ($uni=='metros') {
										foreach ($aarvar as $kk => $vv) {
											$aarvar[$kk] = $vv*100;
										}
									} elseif ($uni=='mm') {
										foreach ($aarvar as $kk => $vv) {
											$aarvar[$kk] = $vv/10;
										}
									}
								}
								if ($defuni=='mm') {
									if ($uni=='metros') {
										foreach ($aarvar as $kk => $vv) {
											$aarvar[$kk] = $vv*1000;
										}
									} elseif ($uni=='cm') {
										foreach ($aarvar as $kk => $vv) {
											$aarvar[$kk] = $vv*10;
										}
									}
								}
							}
							$variation  = array_merge((array)$variation,(array)$aarvar);
							//echopre($variation);
						}
						if ($traittipo=='Variavel|Texto') {
							//$variation  = array_merge((array)$variation,(array)array($vvar));
						}

						if ($traittipo=='Variavel|Imagem') {

						}
					} 
			}
		if ($traittipo=='Variavel|Categoria' || $traittipo=='Variavel|Quantitativo') {
			//summarize variation
			$varname = array();
			if (count($variation)>0) {
					if ($traittipo=='Variavel|Categoria') {
						$varname = array_count_values($variation);
					}
					if ($traittipo=='Variavel|Quantitativo') {
							$aarvar = $variation;
							$nv = count($aarvar);
							$maxvar = max($aarvar);
							$minvar = min($aarvar);
							if ($nv>1) {
								$nmeas = count($aarvar);
								$mean = @round(Numerical::mean($aarvar),3);
								$median = @round(Numerical::median($aarvar),3);
								$stdev = @round(Numerical::standardDeviation($aarvar),3);
								$varname = array(
									'MeanValue' => $median, 
									'SDValue' => $stdev, 
									'MaxValue' => $maxvar,
									'MinValue' => $minvar,
									'Unit' => $varunit,
									'Nmeasurements' => $nmeas);
							} elseif ($nv==1) {
									$varname = array(
									'MeanValue' => $variation[0],
									'Unit' => $varunit,
									'Nmeasurements' => 1);
							}
					}
					if ($traittipo=='Variavel|Texto') {
					}
					if ($traittipo=='Variavel|Imagem') {
					}
			} 

			//$basename = explode("-",$pathname);
//			$nbase = count($basename)-2;
//			$pbase = $basename[$nbase];
//
//			$nname = strtoupper($pbase)." - ".$traitname;
			$tid = "tid_".$cid;
			$varn = array($tid => $varname);
			$ttraitsids[] = $cid;
			$speca= array($tid => implode(";",$specids));
			$specarray = array_merge((array)$specarray,(array)$speca);
			$mydescription = array_merge((array)$mydescription,(array)$varn);

		}
		} else {
//			$qq = "SELECT tnames.TraitName,tnames.PathName,tnames.TraitUnit,tnames.TraitTipo FROM Traits as tnames WHERE TraitID='".$cid."'";
//			$rrw = mysql_query($qq,$conn);
//			$rwr = mysql_fetch_assoc($rrw);
//			$pathname = $rwr['PathName'];
//			$traitname = $rwr['TraitName'];
//
//			$basename = explode("-",$pathname);
//			$nbase = count($basename)-2;
//			$pbase = $basename[$nbase];
//			$nname = strtoupper($pbase)." - ".$traitname;
			$tid = "tid_".$cid;
			$varn = array($tid => ' ');
			$mydescription = array_merge((array)$mydescription,(array)$varn);
		}
	unset($varname,$nname,$traitname,$pathname,$varunit);
	}
	return array($mydescription,$ttraitsids,$specarray);
}

function GetMonitoringData_batch($plantaid,$censo,$formid,$conn) {
	if ($formid>0) {
		$qq = "SELECT * FROM Formularios WHERE FormID='$formid'";
		$res = mysql_query($qq,$conn);
		$rw = mysql_fetch_assoc($res);
		$traitids = explode(";",$rw['FormFieldsIDS']);
		$nn=1;
	} else {
		return false;
	}
	$result = array();
	foreach ($traitids as $ttid) {
		$sc = $censo-1;
		if ($sc<0) {$sc=0;}
			$qq = "SELECT * FROM Monitoramento WHERE PlantaID='$plantaid' AND TraitID='$ttid' ORDER BY DataObs LIMIT ".$sc.",1";
			$resul = mysql_query($qq,$conn);
			$nnn = mysql_numrows($resul);
			if ($nnn>0) {
				$row = mysql_fetch_assoc($resul);
				$charid = $row['TraitID'];
				$dataobs = $row['DataObs'];
				$variation = trim($row['TraitVariation']);
				//echo $qq;
				if (!empty($variation)) {
					$aa = "SELECT * FROM Traits WHERE TraitID='$charid'";
					$rrr = mysql_query($aa,$conn);
					$rw = mysql_fetch_assoc($rrr);
					$tipo = $rw['TraitTipo'];

				//se imagem
				if ($tipo=='Variavel|Imagem') { 
						$traitkey = 'trait_'.$charid;
						$z = explode(";",$row['TraitVariation']);
						if (is_array($z)) {
							foreach ($z as $k => $v) {
								$vv = trim($v);
								if (empty($vv) || $vv=='imagem') {
									unset($z[$k]);
								}
							}
						}
						if (count($z)>=1) {
							$varia = implode(";",$z);
							$aar = array($traitkey => $varia);
							$result = array_merge((array)$result, (array)$aar);
						}
				} else {
					//se quantitativo
					if ($tipo=='Variavel|Quantitativo') {
						$traitkey = 'traitvar_'.$charid;
						$aar = array($traitkey => $row['TraitVariation']);
						$result = array_merge((array)$result, (array)$aar);
						$traitkey = 'traitunit_'.$charid;
						$aar = array($traitkey => $row['TraitUnit']);
						$result = array_merge((array)$result, (array)$aar);
					} else {
						$traitkey = 'traitvar_'.$charid;
						//se categorico prepara array de valores
						$multiselect = $rw['MultiSelect'];
						if ($tipo=='Variavel|Categoria' && $multiselect=='Sim') {
							$amulti = explode(";", $row['TraitVariation']);
							$aar = array();
							foreach ($amulti as $kk => $vvv) {
								if (!empty($vvv)) {
									$vvv = trim($vvv);
									$tk = "traitmulti_".$charid."_".$vvv;
									$aaaar = array($tk => $vvv);
									$aar = array_merge((array)$aar, (array)$aaaar);
								}
							}
							$tk = "traitvar_".$charid;
							$aaaar = array($traitkey => $variation);
							$aar = array_merge((array)$aar, (array)$aaaar);
						} else {
							$aar = array($traitkey => $row['TraitVariation']);
						}
						$result = array_merge((array)$result, (array)$aar);
					}
				}
			} //end if variation
			$aar = array('dataobs_'.$charid => $dataobs);
			$result = array_merge((array)$result, (array)$aar);
		} // end if $nnn
	}
	return $result;
}

function puttraitrow_monitor($oldvals,$formid,$idd,$traitids,$conn,$page,$title,$datanew) {

		$qq = "SELECT * FROM Formularios WHERE FormID='".$formid."'";
		$res = mysql_unbuffered_query($qq,$conn);
		$rr = mysql_fetch_assoc($res);
		$FormFieldsIDS= $rr['FormFieldsIDS'];
		$fieldids = explode(";",$FormFieldsIDS);

		$qq = "SELECT * FROM Traits WHERE ";
		$i=0;
		foreach ($fieldids as $key => $value) {
				if ($i==0) {
					$qq = $qq." TraitID='".$value."'";
				} else {
					$qq = $qq." OR TraitID='".$value."'";
				}
				$i++;
		}
		$qq = $qq." ORDER BY PathName";
		$rr = mysql_query($qq);
		while ($row= mysql_fetch_assoc($rr)) { //para cada variavel no relatorio
				$tt = str_replace(" - "," ",$row['PathName']);

				if (!$page) {
					$dd = "dataobs_".$row['TraitID']."_".$idd;
					$dataobs = $oldvals[$dd];
				} else {
					$dd = "dataobs_".$row['TraitID'];
					$dataobs = $oldvals[$dd];
				}
				if (!empty($datanew)) {
					$dataobs = $datanew;
				}
				echo "<td title='".$title."' align='center'>
						<table><tr>";


				if ($row['TraitTipo']=='Variavel|Categoria') {
				//opcoes de variaves categoricas

					$qq = "SELECT * FROM Traits WHERE ParentID='".$row['TraitID']."' ORDER BY TraitName";
					$trrw = mysql_query($qq,$conn);

					$nstates = mysql_numrows($trrw);
						//echopre($oldvals);
					if ($nstates>2) {

						//echo "traitvar_".$row['TraitID']."_".$idd." ".$nstates."<br>";
						if (!$page) {
							$valores_array = $oldvals["traitvar_".$row['TraitID']."_".$idd];
						}
						//echopre($valores_array);


						echo "<td title='".$title."'><table ><tr>";
						if ($row['MultiSelect']!='Sim') {
							echo "<td style='border:0'><select name='traitvar_".$row['TraitID']."_".$idd."'>";
							echo "<option value=''>----</option>";

						} else {
							echo "<td style='border:0'><select size='4' name='traitvar_".$row['TraitID']."_".$idd."[]' multiple='yes'>";
						}


						while ($rww= mysql_fetch_assoc($trrw)) { //para cada estado de variacao
							//unset($valor);
							//unset($toe);
							if ($row['MultiSelect']=='Sim') {
									$ttn = "traitmulti_".$row['TraitID']."_".$rww['TraitID'];
									if ($page) {
										$valor =  trim($oldvals[$ttn]);
									} else {
										if (in_array($rww['TraitID'],$valores_array)) {
											$valor = $rww['TraitID'];
										} else {
											unset($valor);
										}
									}
							} else {
								$ttn = "traitvar_".$row['TraitID'];
								if ($page) {
									$valor =  trim($oldvals[$ttn]);
								} else {
									$valor =  trim($oldvals[$ttn."_".$idd]);
								}
							}
							echo "<option ";
							if (!empty($valor) && $valor==$rww['TraitID']) {
								echo " selected ";
							}
							echo " value='".$rww['TraitID']."'>".$rww['TraitName']."</option>";
						} 
						if ($row['MultiSelect']=='Sim') {
							echo "<option value=''>----</option>";
						}
						echo "</select></td>";
					} else {
						if (!$page) {
							$valores = $oldvals["traitvar_".$row['TraitID']."_".$idd];
						}
						if ($row['MultiSelect']!='Sim') {
							$tttipo = 'radio';
						} else {
							$tttipo = 'checkbox';
						}
					echo "<td style='border:0'><table><tr>";
						while ($rww= mysql_fetch_assoc($trrw)) {
							if ($row['MultiSelect']=='Sim') {
									$ttn = "traitmulti_".$row['TraitID']."_".$rww['TraitID'];
									if ($page) {
										$valor =  trim($oldvals[$ttn]);
									} else {
										if (in_array($rww['TraitID'],$valores)) {
											$valor = $rww['TraitID'];
										} else {
											unset($valor);
										}
									}
									$varn = "traitvar_".$row['TraitID']."_".$idd."[]";
							} else {
								$ttn = "traitvar_".$row['TraitID'];
								if ($page) {
									$valor =  trim($oldvals[$ttn]);
								} else {
									$valor =  trim($oldvals[$ttn."_".$idd]);
								}
								$varn = "traitvar_".$row['TraitID']."_".$idd;
							}
							echo "
							<td align='right'>
								<input type='".$tttipo."' name='".$varn."' ";
							if (!empty($valor) && $valor==$rww['TraitID']) {
								echo " checked ";
							}
							echo " value='".$rww['TraitID']."'></td>
							<td align='left'>".$rww['TraitName']."</td>" ;
						}
					}
					echo "</tr></table></td>";
				}


				//se quantitativo
				if ($row['TraitTipo']=='Variavel|Quantitativo') {
					$string = 'traitvar_'.$row['TraitID'];
					if ($page) {
						$val =  trim($oldvals[$string]);
						} else {
						$val =  trim($oldvals[$string."_".$idd]);
					}
					echo "
					<td  title='".$title."'><table border=0>
						<tr><td style='border:0' >
							<input type='text' name='traitvar_".$row['TraitID']."_".$idd."' value='$val' size='10'>";
						echo "</td>
						<td style='border:0' >
							<select name='traitunit_".$row['TraitID']."_".$idd."' style=\"width: 20mm\" >";

							$string = 'traitunit_'.$row['TraitID'];
							if ($page) {
								$val =  trim($oldvals[$string]);
							} else {
								$val =  trim($oldvals[$string."_".$idd]);
							}
							$vnamearr = array();
							if (empty($val) && !empty($row['TraitUnit'])) {
								$vnamearr[] = $row['TraitUnit'];
								echo "<option selected value='".$row['TraitUnit']."'>".$row['TraitUnit']."</option>";
							} elseif (!empty($val)) {
								$vnamearr[] = $val;
								echo "<option selected value='".$val."'>".$val."</option>";
							}
							$qq = "SELECT DISTINCT TraitUnit FROM Traits WHERE TraitUnit<>'' ORDER BY TraitUnit ASC";
							$res = mysql_query($qq,$conn);
							if ($res) {
								while ($rwu=mysql_fetch_assoc($res)) {
									$varname = $rwu['TraitUnit'];
									echo "<option value='".$varname."'>".$varname."</option>";
									$vnamearr[] = $varname;
								}
							} 

								$qq = "SELECT * FROM VarLang WHERE VariableName LIKE '%traitunit%' ORDER BY '$lang' ASC";
								$rs = mysql_query($qq,$conn);
								if ($rs) {
									while ($rwu=mysql_fetch_assoc($rs)) {
										$varname = $rwu['VariableName'];
										if (!in_array($varname,$vnamearr)) {
											$zz = explode("_",$varname);
											if ($zz[1]!='desc') {
												echo "<option value='".GetLangVar($varname)."'>".GetLangVar($varname)."</option>";
											}
										}
									}
								}

					echo "</select>
					</td></tr>
					</table></td>
					";
				}

				//se texto
				if ($row['TraitTipo']=='Variavel|Texto') {
					//echo "<input type=hidden name='traitnone_".$row['TraitID']."_".$idd."' value='none'>";
					$string = 'traitvar_'.$row['TraitID'];
					if ($page) {
						$val = $oldvals[$string];
					} else {
						$val= $oldvals[$string."_".$idd];
					}
					//tem um problema aqui quando apaga os dados
					echo "<td  title='".$title.": ".$val."'><textarea name='traitvar_".$row['TraitID']."_".$idd."' cols='30' rows='1' >".$val."</textarea></td>";
				}
				echo "</tr>
					<tr>
					<td >
						<table ><tr>
						<td style='border: 0px'>
							<input name=\"dataobs_".$row['TraitID']."_".$idd."\" value=\"$dataobs\" size=\"11\" readonly >
							</td>
						<td style='border: 0px'>
						<a onclick=\"if(self.gfPop)gfPop.fPopCalendar(document.forms['finalform'].dataobs_".$row['TraitID']."_".$idd.");return false;\" >
							<img name=\"popcal\" align=\"absmiddle\" src=\"calendar/calbtn.gif\" width=\"34\" height=\"22\" border=\"0\" alt=\"\"></a>
						</td>
						</tr>
						</table>
					</td></tr></table></td>";
		}//end of loop de cada variavel relatorio
}

function GetMonitoringData($plantaid,$dataobs,$formid,$conn) {
	if ($formid>0) {
		$qq = "SELECT * FROM Formularios WHERE FormID='$formid'";
		$res = mysql_query($qq,$conn);
		$rw = mysql_fetch_assoc($res);
		$traitids = explode(";",$rw['FormFieldsIDS']);
		$nn=1;
	} else {
		$qq = "SELECT TraitID FROM Monitoramento WHERE PlantaID='$plantaid' AND DataObs='$dataobs'";
		$rr = mysql_query($qq,$conn);
		$nn = mysql_numrows($rr);
		$traitids = array();
		while ($rw = mysql_fetch_assoc($rr)) {
			$cid = $rw['TraitID'];
			$traitids = array_merge((array)$traitids,(array)$cid);
		}
	}
	//echo $qq."<br>";
	//echopre($traitids);
	$result = array();
	if ($nn>0) {
		foreach ($traitids as $ttid) {
			$qq = "SELECT * FROM Monitoramento WHERE PlantaID='$plantaid' AND TraitID='$ttid' AND DataObs='$dataobs'";
			$resul = mysql_query($qq,$conn);
			$nnn = mysql_numrows($resul);
			$row = mysql_fetch_assoc($resul);
			$charid = $row['TraitID'];
			$variation = trim($row['TraitVariation']);
			//echo $qq;
			if (!empty($variation)) {
			$aa = "SELECT * FROM Traits WHERE TraitID='$charid'";
			$rrr = mysql_query($aa,$conn);
			$rw = mysql_fetch_assoc($rrr);
			$tipo = $rw['TraitTipo'];

				//se imagem
				if ($tipo=='Variavel|Imagem') { 
						$traitkey = 'trait_'.$charid;
						//echo "HHHHHHERE".$traitkey.$row['TraitVariation'];
						$z = explode(";",$row['TraitVariation']);
						if (is_array($z)) {
							foreach ($z as $k => $v) {
								$vv = trim($v);
								if (empty($vv) || $vv=='imagem') {
									unset($z[$k]);
								}
							}
						}
						if (count($z)>=1) {
							$varia = implode(";",$z);
							$aar = array($traitkey => $varia);
							$result = array_merge((array)$result, (array)$aar);
						}
						//echopre($aar);
				} else {
					//se quantitativo
					if ($tipo=='Variavel|Quantitativo') {
						$traitkey = 'traitvar_'.$charid;
						$aar = array($traitkey => $row['TraitVariation']);
						$result = array_merge((array)$result, (array)$aar);
						$traitkey = 'traitunit_'.$charid;
						$aar = array($traitkey => $row['TraitUnit']);
						$result = array_merge((array)$result, (array)$aar);
					} else {
						$traitkey = 'traitvar_'.$charid;
						//se categorico prepara array de valores
						$multiselect = $rw['MultiSelect'];
						if ($tipo=='Variavel|Categoria' && $multiselect=='Sim') {
							$amulti = explode(";", $row['TraitVariation']);
							$aar = array();
							foreach ($amulti as $kk => $vvv) {
								if (!empty($vvv)) {
									$vvv = trim($vvv);
									$tk = "traitmulti_".$charid."_".$vvv;
									$aaaar = array($tk => $vvv);
									$aar = array_merge((array)$aar, (array)$aaaar);
								}
							}
							$tk = "traitvar_".$charid;
							$aaaar = array($traitkey => $variation);
							$aar = array_merge((array)$aar, (array)$aaaar);
						} else {
							$aar = array($traitkey => $row['TraitVariation']);
						}
						$result = array_merge((array)$result, (array)$aar);
					}
				}
			} //end if variation
		}
	}
	return $result;
}

function tableofmonitortraits($plantaid,$plantatag,$conn) {

$qq = "SELECT Distinct DataObs FROM Monitoramento WHERE PlantaID='$plantaid' ORDER BY DataObs ASC";
$rr = mysql_query($qq,$conn);
$nr = @mysql_numrows($rr);
//echo "<br>".$nr." here ".$qq;
if ($nr>0) {
	echo "
<br>
<table class='sortable autostripe' cellspacing='0' cellpadding='7' align='left'>
<thead >
<tr>
  <th align='center'>".GetLangVar('nametraits')."</th>";
	$arrofdates= array();
	while ($rw = mysql_fetch_assoc($rr)) {
		echo "
  <th align='center'>".$rw['DataObs']."</th>";
		$newdate = array($rw['DataObs']);
		$arrofdates = array_merge((array)$arrofdates,(array)$newdate);
	}
	echo "
</tr>
</thead>
<tbody>";

	$qq = "SELECT Distinct TraitName,TraitID FROM Monitoramento JOIN Traits USING(TraitID) WHERE PlantaID='$plantaid' ORDER BY TraitName";
	$rer = mysql_query($qq,$conn);
	while ($row = mysql_fetch_assoc($rer)) {
		$ttid = $row['TraitID'];
		$tname = $row['TraitName'];
		echo "<tr class='tdformnotes' ><td class='tdthinborder'  >".$tname."</td>";

		foreach ($arrofdates as $data) {
			$qq = "SELECT TraitID,PlantaID,TraitTipo,TraitName,Monitoramento.TraitUnit,TraitVariation,DataObs,MultiSelect FROM Monitoramento JOIN Traits USING(TraitID) WHERE PlantaID='$plantaid' AND TraitID='$ttid' AND DataObs='$data'";
			$rrr = mysql_query($qq,$conn);
			$nrr = @mysql_numrows($rrr);
			if ($nrr==1) {
					$rw = mysql_fetch_assoc($rrr);
					$traittipo = $rw['TraitTipo'];
					$variation = $rw['TraitVariation'];
					$multsel = $rw['MultiSelect'];
					//echo $variation;
					if ($traittipo=='Variavel|Categoria' && $multsel=='Sim') {
						$aarvar = explode(";",$variation);
						$nvar = count($aarvar);
						$i =1;
						$varname='';
						foreach ($aarvar as $kk => $val) {
							$qq = "SELECT * FROM Traits WHERE TraitID='$val'";
							$res = mysql_query($qq,$conn);
							$resw = mysql_fetch_assoc($res);
							$varname = $varname.strtolower($resw['TraitName']);
							if ($i<$nvar) {$varname = $varname.", ";}
							if ($i==$nvar) {$varname = $varname;}
							$i++;
						}
					} elseif ($traittipo=='Variavel|Categoria') {
						$varid= $variation;
						$qq = "SELECT * FROM Traits WHERE TraitID='$varid'";
						$res = mysql_query($qq,$conn);
						$resw = mysql_fetch_assoc($res);
						$varname = strtolower($resw['TraitName']);
					}

					if ($traittipo=='Variavel|Quantitativo' || $traittipo=='Variavel|SemiQuantitativo') {
						$varunit = $rw['TraitUnit'];
						$varname = $variation." (".$varunit.")";
					}

					if ($rw['TraitTipo']=='Variavel|Texto') {
						$varname = $varname.trim($variation);
					}

					if ($rw['TraitTipo']=='Variavel|Imagem') {
						$imgaar = explode(";",$variation );
						$nv = count($aarvar);
						if ($nv>=1) {
						$imgname = "<table class='clean'>";
						foreach ($imgaar as $imgvv) {
							if (!empty($imgvv)) {
								$qq = "SELECT * FROM Imagens WHERE ImageID='$imgvv'";
								$rt = mysql_query($qq,$conn);
								$rtw = mysql_fetch_assoc($rt);
								$path = "img/originais/";
								$imagid = $rtw['ImageID'];
								$filename = trim($rtw['FileName']);

								$autor = $rtw['Autores'];
								//echo 'fotografo  2 = '.$autor;
								$autorarr = explode(";",$autor);
								if (count($autorarr)>0) {
									$j=1;
									foreach ($autorarr as $aut) {
										$qq = "SELECT * FROM Pessoas WHERE PessoaID='".$aut."'";
											$res = mysql_query($qq,$conn);
											$rwr = mysql_fetch_assoc($res);
										if ($j==1) {
											$autotxt = 	$rwr['Abreviacao'];
										} else {
											$autotxt = $autotxt."; ".$rwr['Abreviacao'];
										}
										$j++;
									}
								} 
								$fotodata = $rtw['DateOriginal'];


								$pthumb = "img/thumbnails/";
								//echo $path.$fn;
								if (!file_exists($pthumb.$filename)) {
										createthumb($path.$filename,$pthumb.$filename,80,80);
								}
								$imgbres = "img/copias_baixa_resolucao/";
								if (!file_exists($imgbres.$filename)) {
									$zz = getimagesize($path.$filename);
									$width=$zz[0];
									$height = $zz[1];
									if ($width>1200 || $height>1200) {
										createthumb($path.$filename,$imgbres.$filename,1200,1200);
									} else {
										createthumb($path.$filename,$imgbres.$filename,$width,$height);
									}
								}

								$fn = explode("_",$filename);
								unset($fn[0]);
								unset($fn[1]);
								$fn = implode("_",$fn);

								$fntxt = $fn."  <br> [";
								if (!empty($autotxt)) { $fntxt = $fntxt." ".GetLangVar('namefotografo').": ".$autotxt." - ".$fotodata."]";} else {
									$fntxt = $fntxt.$fotodata."]";
								}

								$imgname = $imgname."
									<tr><td><a href=\"".$imgbres.$filename."\" class='MagicZoomPlus'  rel=\"zoom-position:right;zoom-width:600px; zoom-fade:true; smoothing-speed:17;opacity-reverse:true;\" >
										<img width=\"40\" src=\"".$pthumb.$filename."\"/>
									</a>
									<td class='tinny'>&nbsp;&nbsp;$fntxt</td></tr>";
							}
						}
							$varname = $imgname."</table>";
						}
					}
				echo "<td align='center'>".$varname."</td>";
			} else {  //if if values is present 
				echo "<td align='center'>&nbsp;</td>";
			}
		} //end for each date
		echo "</tr>";
	} //end for each trait
	echo "</tbody></table><br />";
} //end if ($rr>0) {
} //end function


function updatetraits_monitoramento($arraryofvalue,$linkid,$conn) {
$erro =0;
foreach ($arraryofvalue as $key => $value) {

		$arraykey = explode("_",$key);
		$charid = $arraykey[1];
		$varorunit = $arraykey[0];
		if ($varorunit!='dataobs') {
			$dataobs = $arraryofvalue['dataobs_'.$charid];

			$qq = "SELECT * FROM Traits WHERE TraitID='$charid'";
			$nch = mysql_query($qq,$conn);
			$rwch = mysql_fetch_assoc($nch);
			$traittipo = $rwch['TraitTipo'];

			$update = 0;
			if (!is_array($value)) {
				$vv = trim($value);
				if (empty($vv)) {$value=' ';}
			} else {
				$vv = $value;
				if ($varorunit=='traitvar' && count($vv)>0) {
					$value = implode(";",$vv);
				} 
			}

			if ($traittipo=='Variavel|Quantitativo'  && $varorunit!='traitunit' ) {
				$ttunidade = $arraryofvalue['traitunit_'.$charid];
			} else {
				$ttunidade = ' ';
			}
			if ($varorunit=='traitunit' && !empty($value)) {
				$value = ' ';
			}
			if ($varorunit=='traitnone' && $value=='none') {
				$tt = $arraryofvalue['traitvar_'.$charid];
				if ($tt) {
					$value = $arraryofvalue['traitvar_'.$charid];
				} else {
					$value = ' ';
				}
			} elseif ($value=='none') {$value=' ';}

			if (!empty($value) && $varorunit!='traitmulti' && $varorunit!='traitunit') {
				$qq = "SELECT * FROM Monitoramento WHERE TraitID='$charid' AND PlantaID='$linkid' AND DataObs='$dataobs'";
				$teste = mysql_query($qq,$conn);
				$update = @mysql_numrows($teste);
			}

			$fieldsaskeyofvaluearray= array('PlantaID' => $linkid, 'TraitID' => $charid, 'TraitVariation' => $value, 'TraitUnit' => $ttunidade, 'DataObs' => $dataobs);

		//echopre($fieldsaskeyofvaluearray);
		//faz o cadastro ou atualiza variacao
		if (!empty($value) && $value!=' ' && $update==0 && $varorunit!='traitmulti' && $varorunit!='traitimg' && $varorunit!='traitunit') {
			$newtrait = InsertIntoTable($fieldsaskeyofvaluearray,
			'MonitoramentoID','Monitoramento',$conn);
			if (!$newtrait) {
				$erro++;
			}
		}
		if ($update>0 && $varorunit!='traitmulti' && $varorunit!='traitimg' && $varorunit!='traitunit') {
			$rrr = @mysql_fetch_assoc($teste);
			$oldval = trim($rrr['TraitVariation']);
			$tvv = $value;
			$oldid  = $rrr['MonitoramentoID'];

			//update if newvalue is different from old value
			if ($tvv!=$oldval) { 
				CreateorUpdateTableofChanges($oldid,'MonitoramentoID','Monitoramento',$conn);
				$newupdate = UpdateTable($oldid,$fieldsaskeyofvaluearray,'MonitoramentoID','Monitoramento',$conn);
				if (!$newupdate) {
					$erro++;
					}
			}
	 	}
	} //endif dataobs
} //end for each  variable
	if ($erro==0) {return TRUE;} else {return FALSE;}
} //end of function

function printtrait_variation($iddsarr,$traitid,$typeid,$printN,$english=FALSE,$conn) {
			$qq = "SELECT tvar.TraitID,tnames.TraitName,tnames.TraitName_English,tnames.PathName_English,tnames.PathName,tvar.TraitVariation,tvar.TraitUnit,tnames.TraitTipo,tvar.".$typeid." FROM Traits_variation as tvar JOIN Traits as tnames USING(TraitID) WHERE TraitID='".$traitid."' AND (";
			$ii=0;
			$nn = count($iddsarr)-1;
			foreach ($iddsarr as $vv) {
				if ($ii<$nn) {
					$qq = $qq.$typeid."='".$vv."' OR ";
				} elseif ($ii==$nn) {
					$qq = $qq.$typeid."='".$vv."'";
				}
				$ii++;
			}
			$qq = $qq.")";

			//merge variation for specimens
			//echo $qq."<br />";
			$rwr = mysql_query($qq,$conn);
			$nrwr = mysql_numrows($rwr);
			$variation = array();
			$hu=0;
			$specids_used = array();
			$specids_notused = array();
			if ($nrwr>0) {
				while ($rww = mysql_fetch_assoc($rwr)) {
					if ($hu==0) {
						$traittipo = $rww['TraitTipo'];
						if ($english) {
							$traitname = $rww['TraitName_English'];
							$pathname = $rww['PathName_English'];
						} else {
							$traitname = $rww['TraitName'];
							$pathname = $rww['PathName'];
						}
						$varunit = $rww['TraitUnit'];
					}
					$hu++;
					$vvar = trim($rww['TraitVariation']);


					if (!empty($vvar)) {
						if ($traittipo=='Variavel|Categoria') {
							$aarvar = explode(";",$vvar);
							$nvar = count($aarvar);
							$i =1;
							$tovar = array();
							$toend = array();
							$tostart = array();
							foreach ($aarvar as $kk => $val) {
								$qq = "SELECT * FROM Traits WHERE TraitID='$val'";
								$ror = mysql_query($qq,$conn);
								$rwo = mysql_fetch_assoc($ror);
								if ($english) {
									$varsing = strtolower($rwo['TraitName_English']);
									$vs = $varsing;
									if (strtoupper($vs)=='LIGHT' || strtoupper($vs)=='DARK') {
										$toend[] = trim($varsing);
									} 
									else {
										$tostart[] = trim($varsing);
									}
									if (strtoupper($vs)=='LIGHT') {
										$tostart[] = trim($varsing);
									} 
									else {
										$toend[] = trim($varsing);
									}
									mysql_free_result($ror);
									unset($rwo);
								} 
								else {
									$varsing = strtolower($rwo['TraitName']);
									$varsing = strtloweracentos($varsing);
									$vs = RemoveAcentos($varsing);
									if (strtoupper($vs)=='CLARO' || strtoupper($vs)=='ESCURO') {
										$toend[] = trim($varsing);
									} 
									else {
										$tostart[] = trim($varsing);
									}
									if (strtoupper($vs)=='LEVE') {
										$tostart[] = trim($varsing);
									} 
									else {
										$toend[] = trim($varsing);
									}
									mysql_free_result($ror);
									unset($rwo);
								}

							}

							//if (count($tostart)==1 && count($toend)==1) {
							//	$tovar  = array($tostart[0].'-'.$toend[0]);
							//} else {
								$tv =  array_merge((array)$tostart,(array)$toend);
								$tovar = array_unique($tv);
								$tovar = implode("-",$tovar);
							//}
							if (!empty($tovar)) {
								$variation  = array_merge((array)$variation,(array)array($tovar));
							}

						}
						if ($traittipo=='Variavel|Quantitativo') {
							$aarvar = explode(";",$vvar);
							$variation  = array_merge((array)$variation,(array)$aarvar);
						}
						if ($traittipo=='Variavel|Texto') {
							$variation  = array_merge((array)$variation,(array)array($vvar));
						}

						if ($traittipo=='Variavel|Imagem') {

						}
						$specids_used[] = $rww[$typeid];
					} else {
						$specids_notused[] = $rww[$typeid];
					}
			}
			//summarize variation
			$varname = '';
			if (count($variation)>0) {
					if ($traittipo=='Variavel|Categoria') {
						//$variation = sort($variation);
						$aarvar = array_count_values($variation);
						//echopre($aarvar);
						$nvar = count($aarvar)-1;
						$ik= 0;
						foreach ($aarvar as $kkk => $vall) {
							if ($ik<$nvar) {
								$varname = 	$varname.$kkk;
								if ($printN==1) { $varname .= " (N=".$vall.")";}
								$varname .= 	", ";
							} elseif ($ik==$nvar) {
								$varname = 	$varname.$kkk;
								if ($printN==1) { $varname .= " (N=".$vall.")";}
							}
							$ik++;
						}
					}
					if ($traittipo=='Variavel|Quantitativo') {
							$aarvar = $variation;
							$nv = count($aarvar);
							$vvunit = trim(strtoupper(RemoveAcentos($varunit)));
							if ($nv>1) {
								$mean = @round(Numerical::mean($aarvar),1);
								$stdev = @round(Numerical::standardDeviation($aarvar),1);
								$maxvar = round(max($aarvar),1);
								$minvar = round(min($aarvar),1);
								if ($stdev>0 && $nv>2) { $sdv = "&#177;".$stdev; } else { $sdv='';}
								if ($nv==2 && $minvar==$maxvar) { $svv=' '; } else { $svv=" [".$minvar."-".$maxvar."] ";}

								if ($vvunit=='NUMERO') { 
									$vu = '';} 
								else { 
									$vu = strtolower($varunit);
								}
								if ($printN==1) {
									$varname = $mean.$sdv.$svv.$vu;
									$varname .= " (N=".$nv.")";
								}
								if ($printN==2) { 
									$varname = $minvar."-".$maxvar." ".$vu;
								}
								if ($printN==3) { 
									$varname = $minvar."-".$mean."-".$maxvar." ".$vu;
								}
								if ($printN==4) { 
									$varname = $mean.$sdv.$svv.$vu;
								}
								$varname = str_replace(".",",",$varname);
							} elseif ($nv==1) {
								$vvar = round($variation[0],1);
								if ($vvunit=='NUMERO') { $vu = '';} else { $vu = strtolower($varunit);}
								$varname = trim($varname).$vvar." ".$vu;
								$varname = str_replace(".",",",$varname);
							}
					}
					if ($traittipo=='Variavel|Texto') {
							$aarvar  = array_unique($variation);
							$varname = implode(". ",$aarvar);
					}
					if ($traittipo=='Variavel|Imagem') {
						}

			}

			$varname = trim($varname);
			if (empty($varname)) {
				$descvar = FALSE;
			} else {
				$descvar = $varname;
			}
		} else {
			$specids_notused = 	$iddsarr;
			$descvar = FALSE;
		}
		$resultado = array();
		$descvar = str_replace("<","&lt;", $descvar);
		$descvar = str_replace(">","&gt;", $descvar);
		$resultado['variation'] = $descvar;
		$resultado['specids_notused'] = $specids_notused;
		$resultado['specids_used'] = $specids_used;
		return $resultado;

}


function printtraitname($currentpathname,$previouspathname,$currentclassadj,$jaindumento,$conn) {

		//vetores originais de palavras em cada trait path [SUM(classe(n))+TraitName]
			$cpatharr_org = explode("-",$currentpathname);
			$ppatharr_org = explode("-",$previouspathname);

		//remove acentos para facilitar comparações quando há erros de acentuação
		$cpath= RemoveAcentos($currentpathname);
			$cpath= trim(strtoupper($cpath));
		$ppath= RemoveAcentos($previouspathname);
			$ppath= trim(strtoupper($ppath));

		//vetores de palavras em cada trait path [SUM(classe(n))+TraitName]
		$cpatharr = explode("-",$cpath);
		$ppatharr = explode("-",$ppath);

		//nome da classe mais inclusiva 
		$idxclass = count($cpatharr)-2;
		$cclass = trim($cpatharr[$idxclass]);

		$idxclass = count($ppatharr)-2;
		$pclass = trim($ppatharr[$idxclass]);

		//nome das variaveis
		$idxtrait = count($cpatharr)-1;
		$ctrait = trim($cpatharr[$idxtrait]);
		$ctrait_org = trim($cpatharr_org[$idxtrait]);

		$idxtrait = count($ppatharr)-1;
		$ptrait = trim($ppatharr[$idxtrait]);
		$ptrait_org = trim($ppatharr_org[$idxtrait]);

		$resultado = '';

		//coloca a classe caso a atual seja diferente da anterior
		if ($cclass!=$pclass && $cclass!='INDUMENTO') {
			$idxclass = count($cpatharr_org)-2;
			$classevalendo = trim($cpatharr_org[$idxclass]);
			$resultado .= " <b>".$classevalendo." ".$currentclassadj."</b> &mdash; ";
			$pclassTorF=TRUE;
			$validcl = $cclass;
		} else {
			$idxclass = count($cpatharr_org)-2;
			$validcl = $pclass;
		}


		//compara o nome da variavel com o nome da classe. Se o nome da variavel tiver palavras na mesma ordem que o nome da classe, apaga essas palavras do nome da variavel
		$oldtn = explode(" ",$validcl);
		$newtn = explode(" ",$ctrait);
		$restn_org = explode(" ",$ctrait_org);
		//echopre($oldtn);
		//echopre($newtn);
		//echopre($restn_org);
		//echo "<hr>";
		$restn = $newtn;
		$cwords = count($newtn);
		if ($cwords>0) {
			for ($i=0;$i<$cwords;$i++) {
				$word = $newtn[$i];
				$key = array_search($word,$oldtn);
				//echo "kk: ".$key;
				if (is_numeric($key)) {
					$ii = $key+1;
					if ($ii>=0 && (strtoupper($newtn[$ii])=='DE' || strtoupper($newtn[$ii])=='DA' || strtoupper($newtn[$ii])=='DO' || strtoupper($newtn[$ii])=='NA' || strtoupper($newtn[$ii])=='NO')) {
						unset($restn_org[$ii]);
						unset($restn[$ii]);
					} 

					unset($restn[$key]);
					unset($restn_org[$key]);
					$ii = $key-1;
					if ($ii>=0 && (strtoupper($newtn[$ii])=='DE' || strtoupper($newtn[$ii])=='DA' || strtoupper($newtn[$i])=='DO' || strtoupper($newtn[$ii])=='NA' || strtoupper($newtn[$ii])=='NO')) {
						unset($restn_org[$ii]);
						unset($restn[$ii]);
					}
				}
			}
		}
		//echopre($restn_org);
		//echo "<hr>";
		///////

		//compara o nome da variavel com o nome da anterior quando mudou de classe. Se o nome da variavel tiver palavras na mesma ordem que o nome da variavel anterior, apaga essas palavras do nome da variavel atual
		if (!$pclassTorF) {
			$oldtn = explode(" ",$ptrait);
			$newtn = $restn;
			$cwords = count($newtn);
			if ($cwords>0) {
				for ($i=0;$i<$cwords;$i++) {
					$word = $newtn[$i];
					$key = array_search($word,$oldtn);
					if (is_numeric($key)) {
						$ii = $key+1;
						if ($ii>=0 && (strtoupper($newtn[$ii])=='DE' || strtoupper($newtn[$ii])=='DA' || strtoupper($newtn[$ii])=='DO' || strtoupper($newtn[$ii])=='NA' || strtoupper($newtn[$ii])=='NO')) {
							unset($restn_org[$ii]);
							unset($restn[$ii]);
						} 

						unset($restn[$key]);
						unset($restn_org[$key]);
						$ii = $key-1;
						if ($ii>=0 && (strtoupper($newtn[$ii])=='DE' || strtoupper($newtn[$ii])=='DA' || strtoupper($newtn[$i])=='DO' || strtoupper($newtn[$ii])=='NA' || strtoupper($newtn[$ii])=='NO')) {
							unset($restn_org[$ii]);
							unset($restn[$ii]);
						}
					}
				}
			}
		}

		if ($cclass=='INDUMENTO') {
			$idxclass = count($ppatharr_org)-2;
			$pcl = trim($ppatharr_org[$idxclass]);
			$cpatharr_org[$idxclass] = $pcl;
			$toppath = implode("-",$cpatharr_org);
			$key = array_search('Tamanho',$restn_org);
			if (is_numeric($key)) {
				unset($restn_org[$key]);
			}
			$key = array_search('Densidade',$restn_org);
			if (is_numeric($key)) {
				unset($restn_org[$key]);
			}
			$key = array_search('Tipo',$restn_org);
			if (is_numeric($key)) {
				unset($restn_org[$key]);
			}
			$cct = implode(" ",$restn_org);
			$cct = strtolower($cct);
			$cct = strtloweracentos($cct);
			if ($jaindumento==0) {
				$currenttrait = 'Indumento '.$cct;
				$jaindumento++;
			} else {
				$currenttrait = $cct;
				$jaindumento++;
			}
		} else {
			$currenttrait = implode(" ",$restn_org);
			$toppath = $currentpathname;
			$jaindumento=0;
		}
		$cct = strtolower($currenttrait);
		$cct = strtloweracentos($cct);
		$resultado .= $cct;
		$rr = array($resultado,$pclassTorF,$toppath,$jaindumento);
		return($rr);
}

function printhabitat($iddsarr,$typeid,$habitatformid,$printN,$conn) {
			if ($typeid=='EspecimenID') { $tb='Especimenes';} else { $tb='Plantas';}

			$qq = "SELECT ".$typeid.",HabitatID FROM ".$tb." JOIN Habitat USING(HabitatID) WHERE ";
			$ii=0;
			$nn = count($iddsarr)-1;
			foreach ($iddsarr as $vv) {
				if ($ii<$nn) {
					$qq = $qq.$typeid."='".$vv."' OR ";
				} elseif ($ii==$nn) {
					$qq = $qq.$typeid."='".$vv."'";
				}
				$ii++;
			}
			$qq = $qq." ORDER BY PathName ASC";
			//merge variation for specimens
			$res = mysql_query($qq,$conn);
			$nres = mysql_numrows($res);

			if ($nres) {
				$arrayofhabitats = array();
				while ($row = mysql_fetch_assoc($res)) {
					$habid = $row['HabitatID']+0;
					$specid = $row[$typeid];
					$qu = "SELECT * FROM Habitat WHERE HabitatID='".$habid."'";
					$rs = mysql_query($qu,$conn);
					$rw = mysql_fetch_assoc($rs);
					$habtipo = $rw['HabitatTipo'];
					if ($habtipo=='Class') {
							$arrayofhabitats['habclassid_'.$habid] = array();
					} else {
						$pid = $rw['ParentID']+0;
						while ($pid>=1) {
								$quq = "SELECT * FROM Habitat WHERE HabitatID='".$pid."'";
								$rss = mysql_query($quq,$conn);
								$rws = mysql_fetch_assoc($rss);
								$ht = $rws['HabitatTipo'];
								if ($ht=='Class') { 
									$arrayofhabitats['habclassid_'.$pid][] = $habid;
								} 
								$pid = $rws['ParentID'];
						}
					}

				}
			}

			$habitatdescription = '';

			if (count($arrayofhabitats)>0) {
				foreach ($arrayofhabitats as $key => $habitatids) {
						$habitatids = array_unique($habitatids);

						if (count($habitatids)>0) {
						$cl = explode("_",$key);
						$clid = $cl[1];
						$qu = "SELECT * FROM Habitat WHERE HabitatID='".$clid."'";
						$rws = mysql_query($qu,$conn);
						$rww = mysql_fetch_assoc($rws);
						$classname = $rww['PathName'];
						$classname = trim($classname);
						$classname = strtolower($classname);
						$classname = strtloweracentos($classname);
						$classname = ucfirst($classname);
						$nclass = count($habitatids);
						$habitatdescription = $habitatdescription."<u>".$classname."</u>";
						if ($printN) {
							$habitatdescription .= " (N=".$nclass.")";
						}

						if ($habitatformid>0) {
							$habitattraits = array();
							$qu = "SELECT * FROM Formularios WHERE FormID='$habitatformid'";
							$rse = mysql_query($qu,$conn);
							$rzw = mysql_fetch_assoc($rse);
							$habitattraits = explode(";",$rzw['FormFieldsIDS']);
						}

						$iht = 0;
						$ntraits = count($habitattraits);
						foreach ($habitattraits as $habtrait) {
							$habtrait = $habtrait+0;
							$qq = "SELECT tvar.TraitID,tnames.TraitName,tnames.PathName,tvar.HabitatVariation,tvar.TraitUnit,tnames.TraitTipo FROM Habitat_Variation as tvar JOIN Traits as tnames USING(TraitID) WHERE tvar.TraitID='".$habtrait."' AND (";
							$ii=0;
							$nn = count($habitatids)-1;
							foreach ($habitatids as $vv) {
								if ($ii<$nn) {
									$qq = $qq."HabitatID='".$vv."' OR ";
								} elseif ($ii==$nn) {
									$qq = $qq."HabitatID='".$vv."'";
								}
								$ii++;
							}
							$qq = $qq.")";
/////////
							//merge variation for habitats
							$rwre = mysql_query($qq,$conn);
							$nrwr = mysql_numrows($rwre);
							$variation = array();
							$hu=0;
							if ($nrwr>0) {
								while ($rwe = mysql_fetch_assoc($rwre)) {
									if ($hu==0) {
										$pathname = $rwe['PathName'];
										$traittipo = $rwe['TraitTipo'];
										$traitname = $rwe['TraitName'];
										$varunit = $rwe['TraitUnit'];
									}
									//echopre($rwe);
									$hu++;
									$vvar = trim($rwe['HabitatVariation']);

									if (!empty($vvar)) {
										if ($traittipo=='Variavel|Categoria') {
											$aarvar = explode(";",$vvar);
											$nvar = count($aarvar);
											$i =1;
											foreach ($aarvar as $kk => $val) {
												$qq = "SELECT * FROM Traits WHERE TraitID='$val'";
												$ror = mysql_query($qq,$conn);
												$rwo = mysql_fetch_assoc($ror);
												$varsing = strtolower($rwo['TraitName']);
												$variation  = array_merge((array)$variation,(array)array($varsing));
												mysql_free_result($ror);
												unset($rwo);
											}
										}
										if ($traittipo=='Variavel|Quantitativo') {
											$aarvar = explode(";",$vvar);
											$variation  = array_merge((array)$variation,(array)$aarvar);
										}
										if ($traittipo=='Variavel|Texto') {
											$variation  = array_merge((array)$variation,(array)array($vvar));
										}

										if ($traittipo=='Variavel|Imagem') {

										}
									}
							}

							//summarize variation
							$varname = '';
							if (count($variation)>0) {
									if ($traittipo=='Variavel|Categoria') {
										$aarvar = array_count_values($variation);
										$nvar = count($aarvar)-1;
										$ik= 0;
										foreach ($aarvar as $kkk => $vall) {
											$varname = $varname.$kkk;
											if ($printN) {
												$varname .= " (N=".$vall.")";
											}
											if ($ik<$nvar) {
												$varname .= ", ";
											} 
											$ik++;
										}
									}
									if ($traittipo=='Variavel|Quantitativo') {
											$aarvar = $variation;
											$nv = count($aarvar);
											if ($nv>1) {
												$mean = @round(Numerical::mean($aarvar),1);
												$stdev = @round(Numerical::standardDeviation($aarvar),1);
												$maxvar = max($aarvar);
												$minvar = min($aarvar);
												if ($stdev>0 && $nv>2) { $sdv = "+/-".$stdev; } else { $sdv='';}
												if ($nv==2 && $minvar==$maxvar) { $svv=''; } else { $svv=" [".$minvar."-".$maxvar."] ";}
												if (substr($varunit,-1,4)=='mero') { $vu = '';} else { $vu = strtolower($varunit);}
												$varname = $mean.$sdv.$svv.$vu;
												if ($printN) {
													$varname .= " (N=".$nv.")";
												}
											} elseif ($nv==1) {
												$varname = trim($varname).$variation[0]." ".$varunit;
											}
									}
									if ($traittipo=='Variavel|Texto') {
											$aarvar  = array_unique($variation);
											$varname = implode(". ",$aarvar);
									}
									if ($traittipo=='Variavel|Imagem') {
										}

							}
							$varname = trim($varname);
							if (!empty($varname)) {
								$traitname = strtolower($traitname);
								$traitname = strtloweracentos($traitname);
								$traitname = ucfirst($traitname);
								if ($iht>0 && $iht<=$ntraits) {
									$spp = "; ";
								} elseif ($iht==0) {
									$spp = " &mdash; ";
								}
									$habitatdescription = $habitatdescription.$spp.$traitname." ".$varname;
								$iht++;
							}
/////////
						}
				}
				$habitatdescription = $habitatdescription.". ";
				}
			}
		}
	$resultado = trim($habitatdescription);
	if (!empty($resultado)) {
		return $resultado;
	} else {
		return FALSE;
	}
}

function listspecimens_rodriguesia($nome,$list_measured,$list_notmeasured,$typeid,$desctemptable,$conn) {
			if ((count($list_measured)>0 && count($list_notmeasured)>0) || (count($list_measured)==0 && count($list_notmeasured)>0)) {
				$mark = TRUE;
				//"mark is true";
			} 
			$missingfert = array();
			$missingherbaria = array();

			$fenologia = array();
			//Padrão aproximado:
			//BRASIL. BAHIA: Ilhéus, Reserva da CEPEC, 15.XII.1996, fl. e fr., R.C. Vieira et al. 10987 (MBM, RB, SP).

			$pais = '';
			$prov = '';
			$muni = '';
			$gaz = '';
			$local = '';
			$usedgaz = array();
			$ij=0;

			$uid = $_SESSION['userid'];

			$qu = "SELECT tb.*, 
			IF(tb.GPSPointID>0,getGPSlocalityFields(tb.GPSPointID, 'LATITUDE'), IF(tb.GazetteerID>0,getlocalityFields(tb.GazetteerID, 'LATITUDE'),'')) AS LATITUDE, IF(tb.GPSPointID>0,getGPSlocalityFields(tb.GPSPointID, 'LONGITUDE'), IF(tb.GazetteerID>0,getlocalityFields(tb.GazetteerID, 'LONGITUDE'),'')) AS LONGITUDE,  IF(tb.GPSPointID>0,getGPSlocalityFields(tb.GPSPointID, 'ALTITUDE'), IF(tb.GazetteerID>0,getlocalityFields(tb.GazetteerID, 'ALTITUDE'),'')) AS ALTITUDE FROM ".$desctemptable." as tb WHERE NAMEINDEX ='".$nome."' ORDER BY COUNTRY, MAJORAREA, MINORAREA, GAZETTEER, LONGITUDE, LATITUDE";

			//echo "<br>".$qu."<br>";
			$rss = mysql_query($qu,$conn);
			$nspecs = mysql_numrows($rss)-1;
			//echo $qu."<br />";
			while ($rsw = mysql_fetch_assoc($rss)) {
				$ppais = trim($rsw['COUNTRY']);
				$pprov  = trim($rsw['MAJORAREA']);
				$pmuni  = trim($rsw['MINORAREA']);
				$pgaz  = trim($rsw['GAZETTEER']);

				$latitude  = trim($rsw['LATITUDE']);
				$longitude  = trim($rsw['LONGITUDE']);
				$altitude  = trim($rsw['ALTITUDE']);

				$coords ='';
				if (!empty($longitude)) {
					$rescoords = coordinates($latitude,$longitude,$latgrad,$longgrad,$latminu,$longminu,$latsec,$longsec,$latnors,$longwore);
					$latt = $rescoords['latgrad']."<sup>o</sup> ".$rescoords['latminu']."' ".$rescoords['latsec']."\" ".$rescoords['latnors'];
					$longtt = $rescoords['longgrad']."<sup>o</sup> ".$rescoords['longminu']."' ".$rescoords['longsec']."\" ".$rescoords['longwore'];
				    $coords = $latt." ".strtolower(GetLangVar('nameand'))." ".$longtt;
				    if (!empty($altitude)) {
				    		$coords = $coords." (".$altitude." m)";
				    }
				}
				if ($ppais!=$pais) {
					$local = $local." ".strtoupper($ppais).".";
					$pais = $ppais;
				}
				if ($pprov!=$prov) {
					$local = $local." ".strtoupper($pprov).":";
					$prov=$pprov;
				}
				if ($pmuni!=$muni) {
					$local = $local." ".$pmuni." -";
					$muni=$pmuni;
				}
				if ($pgaz!=$gaz && !empty($pgaz)) {
					//$zz = explode(".",$pgaz);
					//if (count($zz)>1) {
					//	if (in_array($zz[0],$usedgaz)) { 
					//		$pgaz= $zz[1];
					//	}
					//	if (in_array($zz[1],$usedgaz)) { 
					//		$pgaz= '';
					//	}
					//} 
					//$pg = trim($pgaz);
					//if (!empty($pg) && !is_array($pg)) {
						//$usedgaz = array_merge((array)$usedgaz,(array)array($pgaz));
					if (substr($local,-1,1)=='-') {
						$sep = "";
					} else {
						$sep = ". ";
					}
					$local = $local.$sep." <u>".$pgaz."</u>,";
					$gaz=$pgaz;
					//}
				} else {
					$local = $local."; ";
				}

				if (!empty($coords)) {
					$local = $local." ".$coords.", ";
				}




				$specid = $rsw['EspecimenID']+0;
				//$newspecarr  = array_merge((array)$newspecarr,(array)array($specid));

				//$traitfertid = 99;
				$fert = explode(";",getfertility($specid,$traitfertid,$conn));
				$mes = $rsw['Mes'];
				$ff ='';
				if (is_array($fert) && count($fert)>0) {
					$ff = trim(implode(". e ",$fert));
					if (!empty($ff)) {
						$ff = $ff.".";
					}
					foreach ($fert as $fe) {
						$fenologia[$fe][] = $mes;
					}
				} else {
					$ff = '';
				}

				$colect = explode(",",$rsw['Abreviacao']);
				$clnn = $colect[0];
				$colect = $colect[1]." ".$colect[0];
				$colnum = $rsw['Number'];
				$clnn = $clnn." ".$colnum;
				if (empty($ff)) { 
					$ff = "<b><font color='red'>FALTA FERT</font></b>";
					$missingfert["spid_".$specid] = $clnn;
				}
				$coldd = $rsw['Day'];
				$colmm = $rsw['Mes']-1;
				$colyy = $rsw['Ano'];
				if ($colyy>0) {
					$coldata = $colyy;
					if ($colmm>0) {
						$mm = getmonthroman($colmm);
						$coldata = $mm.".".$coldata;
					}
					if ($coldd>0) {
						$coldata = $coldd.".".$coldata;
					}
				} else {
					$coldata = "<b><font color='red'>FALTA DATA</font></b>";
				}
				$local = $local." ".$coldata.", ".$ff;

				if ($mark) {
					if (in_array($specid,$list_measured)) {
						$tobold = "<i>";
						$closebold = "</i>";
					} else {
						$tobold = "";
						$closebold = "";
					}
				} else {
						$tobold = "<i>";
						$closebold = "</i>";
				}

				$local = $local.", <b>".$tobold.$colect;

				$addcolids = trim($rsw['AddColIDS']);
				if (!empty($addcolids)) { $local = $local." et al.";}
				$local = $local." ".$colnum.$closebold."</b>";

				$herbs = trim($rsw['Herbaria']);
				if (!empty($herbs)) {
					$hh = str_replace(";",", ",$herbs);
				} else {
					$hh = "<b><font color='red'>FALTA HERBARIA</font></b>";
					$missingherbaria["spid_".$specid] = $clnn;
				}
				$local = $local." (".$hh.")";

				if ($ij<$nspecs) {
					$local = $local;
				} 
				$ij++;
			}

	return array($local,$fenologia,$missingherbaria,$missingfert);
} //endof function

function makegenusdestription($iddsarr,$traitidsarr,$typeid,$english,$conn) {
		//the whole form traitid and traitorder
		if (!empty($traitidsarr)) {
			$newtraitids = explode(";",$traitidsarr);
		} else {
			return FALSE;
		}
		$ih =0;
		$mydescription = '';
		$previouspathname = '';
		$nnrr = count($newtraitids)-1;
		$jaindumento =0;
		foreach ($newtraitids as $cid) {

			$qq = "SELECT * FROM Traits WHERE TraitID='".$cid."'";
			$roq = mysql_query($qq,$conn);
			$roqw = mysql_fetch_assoc($roq);
			$varname = printtrait_variation($iddsarr,$cid,$typeid,$printN=FALSE,$english,$conn);
			$varname = $varname['variation'];

			if ($varname)
			{
				if ($english) {
					$cupn = $roqw['PathName_English'];
				} else {
					$cupn = $roqw['PathName'];
				}
				$tres = printtraitname($cupn,$previouspathname,'',$jaindumento,$conn);
				$pclassTorF = $tres[1];
				$toprint = trim($tres[0]);
				$currentpathname = $tres[2];
				$jaindumento = $tres[3];
				if ($ih<=$nnrr && $ih>0 && !$pclassTorF && !empty($toprint)) {
							$toprint = "; ".$toprint;
					} else {
						if ($pclassTorF && $ih>0) {
							$toprint = ". ".$toprint;
						} elseif ($jaindumento>1 && empty($toprint)) {
							$toprint = ", ".$toprint;
						}
				}
				$mydescription = $mydescription.$toprint." ".$varname;
				$ih++;
				$previouspathname= $currentpathname;
			}

		}
	$mydescription = trim($mydescription);
	if (!empty($mydescription)) {
		$mydescription = $mydescription.".";
		return $mydescription;
	} else {
		return FALSE;
	}
}





function printtrait_variationsimple($iddsarr,$traitid,$typeid,$conn) {
			$qq = "SELECT tvar.TraitID,tnames.TraitName,tnames.PathName,tvar.TraitVariation,tvar.TraitUnit,tnames.TraitTipo,tvar.".$typeid." FROM Traits_variation as tvar JOIN Traits as tnames USING(TraitID) WHERE TraitID='".$traitid."' AND (";
			$ii=0;
			$nn = count($iddsarr)-1;
			foreach ($iddsarr as $vv) {
				if ($ii<$nn) {
					$qq = $qq.$typeid."='".$vv."' OR ";
				} elseif ($ii==$nn) {
					$qq = $qq.$typeid."='".$vv."'";
				}
				$ii++;
			}
			$qq = $qq.")";
			//merge variation for specimens
			$rwr = mysql_query($qq,$conn);
			$nrwr = mysql_numrows($rwr);
			$variation = array();
			$hu=0;
			if ($nrwr>0) {
				while ($rww = mysql_fetch_assoc($rwr)) {
					if ($hu==0) {
						$pathname = $rww['PathName'];
						$traittipo = $rww['TraitTipo'];
						$traitname = $rww['TraitName'];
						$varunit = $rww['TraitUnit'];
					}
					$hu++;
					$vvar = trim($rww['TraitVariation']);


					if (!empty($vvar)) {
						if ($traittipo=='Variavel|Categoria') {
							$aarvar = explode(";",$vvar);
							$nvar = count($aarvar);
							$i =1;
							foreach ($aarvar as $kk => $val) {
								$qq = "SELECT * FROM Traits WHERE TraitID='$val'";
								$ror = mysql_query($qq,$conn);
								$rwo = mysql_fetch_assoc($ror);
								$varsing = strtolower($rwo['TraitName']);
								$varsing = strtloweracentos($varsing);
								$variation  = array_merge((array)$variation,(array)array($varsing));
								mysql_free_result($ror);
								unset($rwo);
							}
						}
						if ($traittipo=='Variavel|Quantitativo') {
							$aarvar = explode(";",$vvar);
							$variation  = array_merge((array)$variation,(array)$aarvar);
						}
						if ($traittipo=='Variavel|Texto') {
							$variation  = array_merge((array)$variation,(array)array($vvar));
						}

						if ($traittipo=='Variavel|Imagem') {

						}
					}
			}
			//summarize variation
			$varname = '';
			if (count($variation)>0) {
					if ($traittipo=='Variavel|Categoria') {
						$aarvar = array_count_values($variation);
						$nvar = count($aarvar)-1;
						$ik= 0;
						foreach ($aarvar as $kkk => $vall) {
							if ($ik<$nvar) {
								$varname = 	$varname.$kkk.", ";
							} elseif ($ik==$nvar) {
								$varname = 	$varname.$kkk."";
							}
							$ik++;
						}
					}
					if ($traittipo=='Variavel|Quantitativo') {
							$aarvar = $variation;
							$nv = count($aarvar);
							if ($nv>1) {
								$mean = @round(Numerical::mean($aarvar),1);
								$stdev = @round(Numerical::standardDeviation($aarvar),1);
								$maxvar = round(max($aarvar),1);
								$minvar = round(min($aarvar),1);
								if ($stdev>0 && $nv>2) { $sdv = "+/-".$stdev; } else { $sdv='';}
								if ($nv==2 && $minvar==$maxvar) { $svv=''; } else { $svv=" [".$minvar."-".$maxvar."] ";}
								if (substr($varunit,-1,4)=='mero') { $vu = '';} else { $vu = strtolower($varunit);}
								$varname = $mean.$sdv.$svv.$vu;
								$varname = $varname;
							} elseif ($nv==1) {
								$varname = trim($varname).$variation[0]." ".$varunit;
							}
					}
					if ($traittipo=='Variavel|Texto') {
							$aarvar  = array_unique($variation);
							$varname = implode(". ",$aarvar);
					}
					if ($traittipo=='Variavel|Imagem') {
						}

			}
			$varname = trim($varname);

			if (empty($varname)) {
				$resultado = FALSE;
			} else {
				$varname = str_replace("<","&lt;", $varname);
				$varname = str_replace(">","&gt;", $varname);
				$resultado = $varname;
			}
		} else {
			$resultado = FALSE;
		}
		return $resultado;
}

function createmetadadostable($iddsarr,$traitidsarr,$traitidtobreak,$traitstobreakarr,$typeid,$english,$conn) {
		//the whole form traitid and traitorder
		if (!empty($traitidsarr)) {
			$traitids = explode(";",$traitidsarr);
		} else {
			return FALSE;
		}

		//the traitids to break se for o caso
		if (!empty($traitstobreakarr)) {
			$traitstobreak = explode(";",$traitstobreakarr);
		}

		//verificar se foi especificado que deve haver um descricao separada das variaveis em traitstobreakarr, para cada estado de variacao da variavel  traitidtobreak e organizar se for o caso
		$newtraitarray = array();
		if ($traitidtobreak>0 && !empty($traitstobreakarr)) {

			//cria um array vazio para armazenar a nova ordem de variaveis e eliminar aqueles contidos em traitstobreakarr
			$newtraitids = array();

			//para cada variavel no formulario de variaveis da descricao (traitids), checa se ele esta no formulario das variaveis que devem ser descritas separadamente para cada estado de variacao do caractere definido por traitidtobreak
			foreach ($traitids as $tid) {
				//se a variavel nao estiver no formulario traitstobreakarr, entao nao precisa de descricao separada para esta variavel e pode adicionar ao novo array para a descricao
				if (!in_array($tid,$traitstobreak) || $tid==$traitidtobreak) {
					$newtraitids[] = $tid;
				}
			}
			//se a variavel para separar variaveis nao estiver na nova lista, entao e porque ela nao estava no formulario da descricao e sera adicionada no final das demais variaveis
			if (!in_array($traitidtobreak,$newtraitids)) {
				$newtraitids[] = $traitidtobreak;
			} 

			//para cada variavel 
			$merged=0;
			foreach ($newtraitids as $tid) {

				//prepar a lista de amostras para cada variavel

				//se a variavel nao for a variavel para separar a descricao por seus estados de variacao, entao a lista sao todos as amostras no filtro. Caso contrario, seleciona as amostras para cada estado de variacao
				if ($tid!=$traitidtobreak) {
					$newtraitarray["tid_".$tid] = $iddsarr;
				} elseif ($tid==$traitidtobreak) {
					//cria um array para separar a lista de amostras para cada estado
					$breakstates = array();

					//para cada estado de variacao do variavel traiitdtobreak pega a lista de amostras correspondentes
					$qq = "SELECT * FROM Traits WHERE ParentID='".$traitidtobreak."'";
					$ror = mysql_query($qq,$conn);
					while ($rwo = mysql_fetch_assoc($ror)) {
						$stateid = $rwo['TraitID'];
						$statespecs = array();
						//para cada amostra no set verifica se ela tem variacao
						foreach ($iddsarr as $spid) {
							$qq = "SELECT * FROM Traits_variation as tvar JOIN Traits as tnames USING(TraitID) WHERE TraitID='".$traitidtobreak."' AND ". $typeid."='".$spid."' AND TraitVariation LIKE '".$stateid."'";
							$rro = mysql_query($qq,$conn);
							$nrro = mysql_numrows($rro);
							//se a amostra tem variacao para o estado de variacao, entao adiciona a mostra na lista das amostras para o estado de variacao 
							if ($nrro>0) {
								$statespecs[] = $spid;
							}
						}
						//se ha 1 ou mais amostras para o estado de variacao, entao coloca o a lista de amostras no array de resultado que contem todos os estados de variacao e suas respectivas amostras...
						if (count($statespecs)>0) {
							$breakstates['stateid_'.$stateid] = $statespecs;
						}
					}
					//se ha pelo menos 1 estado de variacao com amostras, entao adiciona a variavel na ordem para a descricao
					if (count($breakstates)>0) {
						$newtraitarray["tid_".$tid] = $breakstates;
						$merged++;
					} 
				} 
			}

			//se o trait to break nao encontrou amostras, readiciona as variaveis
			if ($merged==0) {
				foreach ($traitstobreak as $tid) {
					$newtraitarray["tid_".$tid] = $iddsarr;
				}
			}
		} else {  //caso contrario adiciona a lista total de amostras todas as variaveis do formulario
			foreach ($traitids as $tid) {
					$newtraitarray["tid_".$tid] = $iddsarr;
			}
		}

		//começa a fazer a descricao e cria um objeto com texto vazio para concatenacao e uso no loop de cada variavel na lista
		if ($english) {
		$mydescription .= "<table align='center' cellspacing='0' cellpadding='3' style='font-size: 8pt;border: thin solid; border-collapse: collapse' >
		<tr style='font-weight: bold; background-color:#cccccc'><td>&nbsp;</td><td>CLASS</td><td>VARIABLE</td><td>TYPE</td><td>DEFINITION</td><td>UNIT</td><td>CATEGORIES</td></tr>";
		} else {
		$mydescription .= "<table align='center' cellspacing='0' cellpadding='3' style='font-size: 8pt;border: thin solid; border-collapse: collapse' >
		<tr style='font-weight: bold; background-color:#cccccc'><td>&nbsp;</td><td>CLASSE</td><td>VARIÁVEL</td><td>TIPO</td><td>DEFINIÇÃO</td><td>UNIDADE</td><td>CATEGORIAS</td></tr>";

		}
		//numero de variaveis no array
		$nvnarr = count($newtraitarray)-1;
		//indice do loop
		$ih =1;
		$bgi=1;
		foreach ($newtraitarray as $tids => $specids) {
			//pega o traitid
			$td = explode("_",$tids);
			$cid = $td[1];
			unset($clname,$tname,$traittipo,$tdefinicao,$tunit,$testados);
			$qq = "SELECT DISTINCT Traits.*,tvar.TraitUnit as tvarUnit FROM Traits_variation as tvar JOIN Traits USING(TraitID) WHERE TraitID='".$cid."' AND ".$typeid." IN (".implode(",",$iddsarr).")";
			$roq = mysql_query($qq,$conn);
			$nroq =  mysql_numrows($roq);
			if ($nroq==1) {
				$roqw = mysql_fetch_assoc($roq);
				if ($english) {
					$cur_pathname = explode("-",$roqw['PathName_English']);
					$tname = trim($roqw['TraitName_English']);
					$tdefinicao = trim($roqw['TraitDefinicao_English']);
				} else {
					$cur_pathname = explode("-",$roqw['PathName']);
					$tname = trim($roqw['TraitName']);
					$tdefinicao = trim($roqw['TraitDefinicao']);
				}

				$ncp = count($cur_pathname)-2;
				$clname = $cur_pathname[$ncp];
				$tunit = trim($roqw['tvarUnit']);
				$traittipo = trim($roqw['TraitTipo']);

			} else {
				$tunit = array();
				while ($roqw = mysql_fetch_assoc($roq)) {
					if ($english) {
						$cur_pathname = explode("-",$roqw['PathName_English']);
						$tname = trim($roqw['TraitName_English']);
						$tdefinicao = trim($roqw['TraitDefinicao_English']);
					} else {
						$cur_pathname = explode("-",$roqw['PathName']);
						$tname = trim($roqw['TraitName']);
						$tdefinicao = trim($roqw['TraitDefinicao']);
					}

					$ncp = count($cur_pathname)-2;
					$clname = $cur_pathname[$ncp];
					$tunit[]= trim($roqw['tvarUnit']);
					$traittipo = trim($roqw['TraitTipo']);

				}
				$tunit = array_unique($tunit);
				$tunit = implode(";",$tunit);
			}
			if ($traittipo=='Variavel|Categoria') {
				$tunit = ' ';

				$qq = "SELECT  * FROM Traits WHERE ParentID='".$cid."'";
				$ruq = mysql_query($qq,$conn);
				$stnames = array();
				while ($ruqq = mysql_fetch_assoc($ruq)) {
					if ($english) {
						$rru = strtolower($ruqq['TraitName_English']);
					} else {
						$rru = strtolower($ruqq['TraitName']);
						$rru = strtloweracentos($rru);
					}


					$rru = str_replace("<","&lt;", $rru);
					$rru = str_replace(">","&gt;", $rru);
					$stnames[] = trim($rru);
				}
				sort($stnames);
				$testados = implode("<br>",$stnames);
			} else {
				$testados = ' ';
			}



			//pega o nome da variavel e da classe da variavel
			if (!empty($clname)) {
				if ($bgi % 2 == 0){$bgcolor = '#cccccc';}  
				else{$bgcolor = '#ffffff' ;}
				$bgi++;
				$mydescription .=  "<tr style='background-color:".$bgcolor."'><td>$ih</td><td>$clname</td><td>$tname</td><td>".str_replace("Variavel|","",$traittipo)."</td><td>$tdefinicao</td><td>$tunit</td><td>$testados</td></tr>";
				$ih++;
			}
			//inclui, se for o caso de $cid=$traitidtobreak, a descricao para as variaveis de cada estado; neste caso $specids e um array com listas de amostras para cada estado
			if ($traitidtobreak>0 && !empty($traitstobreakarr) && $cid==$traitidtobreak) 		{
				//descricao por estado de traitidtobreak
				$breakdescription ='';

				//array para armazenar a lista de amostras usadas na descricao dessa parte
				$idarr = array();
				$nnrt = count($traitstobreak)-1;
				$key = array_search($traitidtobreak,$traitstobreak);
				if ($traitstobreak[$key]==$traitidtobreak) {
						unset($traitstobreak[$key]);
				}

				//$mydescription .= "<tr><td colspan=7>Variáveis descritas separadamente para cada uma das variáveis acima</td></tr>";
				foreach ($traitstobreak as $trbr) {
					unset($clname,$tname,$traittipo,$tdefinicao,$tunit,$testados);
					$qq = "SELECT DISTINCT Traits.*,tvar.TraitUnit as tvarUnit FROM Traits_variation as tvar JOIN Traits USING(TraitID) WHERE TraitID='".$trbr."' AND ".$typeid." IN (".implode(",",$iddsarr).")";
					$roqo = mysql_query($qq,$conn);
					$nroqo =  mysql_numrows($roqo);
					if ($nroqo==1) {
						$roqwo = mysql_fetch_assoc($roqo);
						if ($english) {
							$cur_pathname = explode("-",$roqwo['PathName_English']);
							$tname = trim($roqwo['TraitName_English']);
							$tdefinicao = trim($roqwo['TraitDefinicao_English']);
						} else {
							$cur_pathname = explode("-",$roqwo['PathName']);
							$tname = trim($roqwo['TraitName']);
							$tdefinicao = trim($roqwo['TraitDefinicao']);
						}
						$ncp = count($cur_pathname)-2;
						$clname = $cur_pathname[$ncp];
						$tunit = trim($roqwo['tvarUnit']);
						$traittipo = trim($roqwo['TraitTipo']);

					} else {
						$tunit = array();
						while ($roqw = mysql_fetch_assoc($roqo)) {
							if ($english) {
								$cur_pathname = explode("-",$roqwo['PathName_English']);
								$tname = trim($roqwo['TraitName_English']);
								$tdefinicao = trim($roqwo['TraitDefinicao_English']);
							} else {
								$cur_pathname = explode("-",$roqwo['PathName']);
								$tname = trim($roqwo['TraitName']);
								$tdefinicao = trim($roqwo['TraitDefinicao']);
							}
							$ncp = count($cur_pathname)-2;
							$clname = $cur_pathname[$ncp];
							$tunit[]= trim($roqwo['tvarUnit']);
							$traittipo = trim($roqwo['TraitTipo']);

						}
						$tunit = array_unique($tunit);
						$tunit = implode(";",$tunit);
					}
					if ($traittipo=='Variavel|Categoria') {
						$tunit = '&nbsp;';
						$qq = "SELECT  * FROM Traits WHERE ParentID='".$trbr."'";
						$ruqq = mysql_query($qq,$conn);
						$stnamesq = array();
						while ($ruqqw = mysql_fetch_assoc($ruqq)) {
							if ($english) {
								$rruw = strtolower($ruqqw['TraitName_English']);
							} else {
								$rruw = strtolower($ruqqw['TraitName']);
								$rruw = strtloweracentos($rruw);
							}
							$rruw = str_replace("<","&lt;", $rruw);
							$rruw = str_replace(">","&gt;", $rruw);
							$stnamesq[] = trim($rruw);
						}
						sort($stnamesq);
						$testadosq = implode("<br>",$stnamesq);
					} else {
						$testadosq = ' ';
					}
					if (!empty($clname)) {
						if ($bgi % 2 == 0){$bgcolor = '#cccccc';}  
							else{$bgcolor = '#ffffff' ;}
						$bgi++;
						$mydescription .=  "<tr style='background-color:".$bgcolor."'><td>$ih</td><td>$clname</td><td>$tname</td><td>".str_replace("Variavel|","",$traittipo)."</td><td>$tdefinicao</td><td>$tunit</td><td>$testadosq</td></tr>";
						$ih++;
					}
				}
				//$mydescription .= "<tr><td colspan=7><td><br></td></tr>";
		}
	}
	$mydescription .= "</table>";
	return $mydescription;
}

function validate_date($date)
    {
        $date = str_replace(array('\'', '-', '.', ','), '/', $date);
        $date = explode('/', $date);

	    if(    count($date) == 3
            and    is_numeric($date[0])
            and    is_numeric($date[1])
            and is_numeric($date[2]) and
            (    checkdate($date[0], $date[1], $date[2]) //mmddyyyy
            or    checkdate($date[1], $date[0], $date[2]) //ddmmyyyy
            or    checkdate($date[1], $date[2], $date[0])) //yyyymmdd
        )
        {
            if (checkdate($date[0], $date[1], $date[2])) { //mmddyyyy
            	$yy = $date[2];
            	$dd = $date[0];
            	$mm = $date[1];
            }
            if (checkdate($date[1], $date[0], $date[2]))  { //ddmmyyyy
            	$yy = $date[2];
            	$dd = $date[1];
            	$mm = $date[0];            
            }
            if (checkdate($date[1], $date[2], $date[0])) { //yyyymmdd
            	$yy = $date[0];
            	$dd = $date[1];
            	$mm = $date[2];            
            }
        
        	$dd = $yy."-".$mm."-".$dd;        
        	//$ddd = date_create($dd);
			return $dd;         
        } else {
	        return false;
	    }
} 


function updatetraits_monitoramento_onimport($arraydevalores,$linkid,$updaterecs,$conn) {
$erro =0;
$traitsused = array();
foreach ($arraydevalores as $key => $value) {
		$arraykey = explode("_",$key);
		$charid = $arraykey[2];
		$varorunit = $arraykey[1];
		$orgcoln = $arraykey[0];
		if ($varorunit=='traitvar') {
			$dataobs = $arraydevalores[$orgcoln.'_dataobs_'.$charid];
			$qq = "SELECT * FROM Traits WHERE TraitID='$charid'";
			$nch = mysql_query($qq,$conn);
			$rwch = mysql_fetch_assoc($nch);
			$traittipo = $rwch['TraitTipo'];
			$update = 0;
			$value = trim($value);
			if ($traittipo=='Variavel|Quantitativo') {
				$ttunidade = $arraydevalores[$orgcoln.'_traitunit_'.$charid];
			} else {
				$ttunidade = '';
			}
			if (!empty($value)) {
				$qq = "SELECT * FROM Monitoramento WHERE TraitID='$charid' AND PlantaID='$linkid' AND DataObs='$dataobs'";
				$teste = mysql_query($qq,$conn);
				$update = @mysql_numrows($teste);
				$fieldsaskeyofvaluearray= array('PlantaID' => $linkid, 'TraitID' => $charid, 'TraitVariation' => $value, 'TraitUnit' => $ttunidade, 'DataObs' => $dataobs);
				if ($update==0) {
					$newtrait = InsertIntoTable($fieldsaskeyofvaluearray,
					'MonitoramentoID','Monitoramento',$conn);
					if (!$newtrait) {
						$erro++;
					} else {
						$traitsused[] = $charid;
					}
				} else {
					$rrr = @mysql_fetch_assoc($teste);
					$oldval = trim($rrr['TraitVariation']);
					$tvv = $value;
					$oldid  = $rrr['MonitoramentoID'];
					//update if newvalue is different from old value
					if ($tvv!=$oldval) {
						if (empty($updaterecs) || $updaterecs=='adicionar') {
							if ($traittipo=='Variavel|Quantitativo' || $traittipo=='Variavel|Categoria') {
								$oldarr = explode(";",$oldval);
								$newarr = explode(";",$tvv);
								$vals = array_merge((array)$oldarr,(array)$newarr);
								if ($traittipo=='Variavel|Categoria') {
									$vals = array_unique($vals);
								}
								$vari = implode(";",$vals);
							}
							if ($traittipo=='Variavel|Texto') {
								$vari = $oldval.". ".$tvv;
							}
							$fieldsaskeyofvaluearray['TraitVariation'] = $vari;
						}
						CreateorUpdateTableofChanges($oldid,'MonitoramentoID','Monitoramento',$conn);
						$newupdate = UpdateTable($oldid,$fieldsaskeyofvaluearray,'MonitoramentoID','Monitoramento',$conn);
						if (!$newupdate) {
							$erro++;
						} else {
							$traitsused[] = $charid;
						}
					}
				}
		 	}
	} //endif dataobs
} //end for each  variable
	if ($erro==0) {return $traitsused;} else {return FALSE;}
} //end of function

function updatetraits_estatic_onimport($arraydevalores,$linktype,$linkid,$updaterecs,$conn) {
$erro =0;
$traitsused = array();
foreach ($arraydevalores as $key => $value) {
		$arraykey = explode("_",$key);
		$charid = $arraykey[2];
		$varorunit = $arraykey[1];
		$orgcoln = $arraykey[0];
		if ($varorunit=='traitvar') {
			$qq = "SELECT * FROM Traits WHERE TraitID='$charid'";
			$nch = mysql_query($qq,$conn);
			$rwch = mysql_fetch_assoc($nch);
			$traittipo = $rwch['TraitTipo'];
			$update = 0;
			$value = trim($value);
			if ($traittipo=='Variavel|Quantitativo') {
				$ttunidade = $arraydevalores[$orgcoln.'_traitunit_'.$charid];
			} else {
				$ttunidade = '';
			}
			if (!empty($value)) {
				$qq = "SELECT * FROM Traits_variation WHERE TraitID='$charid' AND ".$linktype."='$linkid'";
				$teste = mysql_query($qq,$conn);
				$update = @mysql_numrows($teste);
				$fieldsaskeyofvaluearray= array($linktype => $linkid, 'TraitID' => $charid, 'TraitVariation' => $value, 'TraitUnit' => $ttunidade);
				if ($update==0) {
					$newtrait = InsertIntoTable($fieldsaskeyofvaluearray,
					'TraitVariationID','Traits_variation',$conn);
					if (!$newtrait) {
						$erro++;
					} else {
							$traitsused[] = $charid;
						}
				} else {
					$rrr = @mysql_fetch_assoc($teste);
					$oldval = trim($rrr['TraitVariation']);
					$tvv = $value;
					$oldid  = $rrr['TraitVariationID'];
					//update if newvalue is different from old value
					if ($tvv!=$oldval) {
						if (empty($updaterecs) || $updaterecs=='adicionar') {
							if ($traittipo=='Variavel|Quantitativo' || $traittipo=='Variavel|Categoria') {
								$oldarr = explode(";",$oldval);
								$newarr = explode(";",$tvv);
								$vals = array_merge((array)$oldarr,(array)$newarr);
								if ($traittipo=='Variavel|Categoria') {
									$vals = array_unique($vals);
								}
								$vari = implode(";",$vals);
							}
							if ($traittipo=='Variavel|Texto') {
								$vari = $oldval.". ".$tvv;
							}
							$fieldsaskeyofvaluearray['TraitVariation'] = $vari;
						}
						CreateorUpdateTableofChanges($oldid,'TraitVariationID','Traits_variation',$conn);
						$newupdate = UpdateTable($oldid,$fieldsaskeyofvaluearray,'TraitVariationID','Traits_variation',$conn);
						if (!$newupdate) {
							$erro++;
						} else {
							$traitsused[] = $charid;
						}
					}
				}
		 	}
	} //endif dataobs
} //end for each  variable
	if ($erro==0) {return $traitsused;} else {return FALSE;}
} //end of function

function update_moni($charid,$traitvalue,$plantaid, $dbname, $dataobs) {
require_once("../../../includes/".$dbname.".php");
$conn = ConectaDB($dbname);
$erro =0;
//pega o valor antigo
if (empty($dataobs)) {
$dataobs = @date("Y-m-d");
}
$qq = "SELECT * FROM Traits WHERE TraitID='".$charid."'";
$nch = mysql_query($qq,$conn);
$rwch = mysql_fetch_assoc($nch);
$traittipo = $rwch['TraitTipo'];
if ($traittipo=='Variavel|Categoria') {
		$traitvalue = str_replace("'","",$traitvalue);
		$ez = explode(",",$traitvalue);
		$statevals = array();
		$ez = array_filter($ez);
		foreach( $ez as $vv) {
			$vv = trim($vv);
			if (!empty($vv)) {
				$qq = "SELECT TraitID FROM Traits WHERE ParentID='".$charid."'  AND LOWER(TraitName)=LOWER('".$vv."')";
				//echo $qq."<br />";
				$nch = mysql_query($qq,$conn);
				$rwch = mysql_fetch_assoc($nch);
				$statevals[] = $rwch['TraitID'];
			}
		}
		if (count($statevals)>0) {
			$traitvalue = implode(";",$statevals);
		} else {
			$traitvalue = "";
		}
}
$fieldsaskeyofvaluearray = array(
'PlantaID' => $plantaid,
'TraitID' => $charid, 
 "TraitVariation" => $traitvalue, 
'DataObs' => $dataobs
);
$newtrait=0;
if (!empty($traitvalue) && $traitvalue!=' ') {
		$newtrait = InsertIntoTable($fieldsaskeyofvaluearray, "MonitoramentoID",  "Monitoramento", $conn);
		if (!$newtrait) {
			$erro++;
		}
} 
if ($erro==0) {return TRUE;} else {return FALSE;}

} 


function updatetraits_monitoramento_onimport_habitat($arraydevalores,$linkid,$updaterecs,$conn) {
$erro =0;
$traitsused = array();
foreach ($arraydevalores as $key => $value) {
		$arraykey = explode("_",$key);
		$charid = $arraykey[2];
		$varorunit = $arraykey[1];
		$orgcoln = $arraykey[0];
		if ($varorunit=='traitvar') {
			$dataobs = $arraydevalores[$orgcoln.'_dataobs_'.$charid];
			$qq = "SELECT * FROM Traits WHERE TraitID='$charid'";
			$nch = mysql_query($qq,$conn);
			$rwch = mysql_fetch_assoc($nch);
			$traittipo = $rwch['TraitTipo'];
			$update = 0;
			$value = trim($value);
			if ($traittipo=='Variavel|Quantitativo') {
				$ttunidade = $arraydevalores[$orgcoln.'_traitunit_'.$charid];
			} else {
				$ttunidade = '';
			}
			if (!empty($value)) {
				$qq = "SELECT * FROM Habitat_Variation WHERE TraitID='$charid' AND HabitatID='$linkid' AND DataObs='$dataobs'";
				$teste = mysql_query($qq,$conn);
				$update = @mysql_numrows($teste);
				$fieldsaskeyofvaluearray= array('HabitatID' => $linkid, 'TraitID' => $charid, 'Habitat_Variation' => $value, 'TraitUnit' => $ttunidade, 'DataObs' => $dataobs);
				if ($update==0) {
					$newtrait = InsertIntoTable($fieldsaskeyofvaluearray,
					'HabitatVariationID','Habitat_Variation',$conn);
					if (!$newtrait) {
						$erro++;
					} else {
						$traitsused[] = $charid;
					}
				} else {
					$rrr = @mysql_fetch_assoc($teste);
					$oldval = trim($rrr['TraitVariation']);
					$tvv = $value;
					$oldid  = $rrr['HabitatVariationID'];
					//update if newvalue is different from old value
					if ($tvv!=$oldval) {
						if (empty($updaterecs) || $updaterecs=='adicionar') {
							if ($traittipo=='Variavel|Quantitativo' || $traittipo=='Variavel|Categoria') {
								$oldarr = explode(";",$oldval);
								$newarr = explode(";",$tvv);
								$vals = array_merge((array)$oldarr,(array)$newarr);
								if ($traittipo=='Variavel|Categoria') {
									$vals = array_unique($vals);
								}
								$vari = implode(";",$vals);
							}
							if ($traittipo=='Variavel|Texto') {
								$vari = $oldval.". ".$tvv;
							}
							$fieldsaskeyofvaluearray['TraitVariation'] = $vari;
						}
						CreateorUpdateTableofChanges($oldid,'HabitatVariationID','Habitat_Variation',$conn);
						$newupdate = UpdateTable($oldid,$fieldsaskeyofvaluearray,'HabitatVariationID','Habitat_Variation',$conn);
						if (!$newupdate) {
							$erro++;
						} else {
							$traitsused[] = $charid;
						}
					}
				}
		 	}
	} //endif dataobs
} //end for each  variable
	if ($erro==0) {return $traitsused;} else {return FALSE;}
}


function updatetraits_estatic_onimport_habitat($arraydevalores,$linktype,$linkid,$updaterecs,$conn) {
$erro =0;
$traitsused = array();
foreach ($arraydevalores as $key => $value) {
		$arraykey = explode("_",$key);
		$charid = $arraykey[2];
		$varorunit = $arraykey[1];
		$orgcoln = $arraykey[0];
		if ($varorunit=='traitvar') {
			$qq = "SELECT * FROM Traits WHERE TraitID='$charid'";
			$nch = mysql_query($qq,$conn);
			$rwch = mysql_fetch_assoc($nch);
			$traittipo = $rwch['TraitTipo'];
			$update = 0;
			$value = trim($value);
			if ($traittipo=='Variavel|Quantitativo') {
				$ttunidade = $arraydevalores[$orgcoln.'_traitunit_'.$charid];
			} else {
				$ttunidade = '';
			}
			if (!empty($value)) {
				$qq = "SELECT * FROM Habitat_Variation WHERE TraitID='$charid' AND ".$linktype."='$linkid'";
				$teste = mysql_query($qq,$conn);
				$update = @mysql_numrows($teste);
				$fieldsaskeyofvaluearray= array($linktype => $linkid, 'TraitID' => $charid, 'HabitatVariation' => $value, 'TraitUnit' => $ttunidade);
				if ($update==0) {
					$newtrait = InsertIntoTable($fieldsaskeyofvaluearray,
					'HabitatVariationID','Habitat_Variation',$conn);
					if (!$newtrait) {
						$erro++;
					} else {
							$traitsused[] = $charid;
						}
				} else {
					$rrr = @mysql_fetch_assoc($teste);
					$oldval = trim($rrr['HabitatVariation']);
					$tvv = $value;
					$oldid  = $rrr['HabitatVariationID'];
					//update if newvalue is different from old value
					if ($tvv!=$oldval) {
						if (empty($updaterecs) || $updaterecs=='adicionar') {
							if ($traittipo=='Variavel|Quantitativo' || $traittipo=='Variavel|Categoria') {
								$oldarr = explode(";",$oldval);
								$newarr = explode(";",$tvv);
								$vals = array_merge((array)$oldarr,(array)$newarr);
								if ($traittipo=='Variavel|Categoria') {
									$vals = array_unique($vals);
								}
								$vari = implode(";",$vals);
							}
							if ($traittipo=='Variavel|Texto') {
								$vari = $oldval.". ".$tvv;
							}
							$fieldsaskeyofvaluearray['TraitVariation'] = $vari;
						}
						CreateorUpdateTableofChanges($oldid,'HabitatVariationID','Habitat_Variation',$conn);
						$newupdate = UpdateTable($oldid,$fieldsaskeyofvaluearray,'HabitatVariationID','Habitat_Variation',$conn);
						if (!$newupdate) {
							$erro++;
						} else {
							$traitsused[] = $charid;
						}
					}
				}
		 	}
	} //endif dataobs
} //end for each  variable
	if ($erro==0) {return $traitsused;} else {return FALSE;}
}

function cleanQuery($string, $conn)
{
  $za = @unserialize($string);
  if (is_array($za) && count($za)>0) {
	//do nothing in case string is serialized;
  } else {
	  if(get_magic_quotes_gpc())  // prevents duplicate backslashes
	  {
    		$string = stripslashes($string);
	  }
	  //This method should stop the bulk of the SQL injection attacks, but crackers and hackers are very creative and are always finding new methods to break into systems. There are additional steps that can be taken to filter out certain words, such as drop, grant, union, etc., but using this method will strip these words from searches performed by you users. However, if you want to add another level of security and do not have an issue with certain words being deleted from queries, you can add the following just before if (phpversion() >= ’4.3.0′).
	  //$badWords = array("/http/i");
	  //$string = preg_replace($badWords, "", $string);
	  if (phpversion() >= '4.3.0')
	  {
    	$string = mysql_real_escape_string($string, $conn);
	  }
	  else
	  {
    	$string = mysql_escape_string($string, $conn);
	  }
  }
	  return $string;
}
function cleangetpost($array,$conn) {
	$cleanedarr = array();
	foreach ($array as $kk => $vv) {
		$kk = cleanQuery($kk,$conn);
		if (!is_array($vv)) {
			$vv = cleanQuery($vv,$conn);
		} 
		else {
			$runarr = array();
			foreach ($vv as $kkk => $vvv) {
				$kkk = cleanQuery($kkk,$conn);
				if (!is_array($vvv)) {
					$vvv = cleanQuery($vvv,$conn);
				} else {
					$runarr2 = array();
					foreach ($vvv as $kk2 => $vv2) {
						$kk2 = cleanQuery($kk2,$conn);
						if (!is_array($vv2)) {
							$vv2 = cleanQuery($vv2,$conn);
						} else {
							$runarr3 = array();
							foreach ($vv2 as $kk3 => $vv3) {
								$kk3 = cleanQuery($kk3,$conn);
								if (!is_array($vv3)) {
									$vv3 = cleanQuery($vv3,$conn);
								} else {
									$vv3 = 'ERRO cleangetpost';
								}
								$runarr3[$kk3] = $vv3;
							}
							$vv2 = $runarr3;
						}
						$runarr2[$kk2] = $vv2;
					}
					$vvv = $runarr2;
				}
				$runarr[$kkk] = $vvv;
			}
			$vv = $runarr;
		}
		$cleanedarr[$kk] = $vv;
	}
	return $cleanedarr;
}


function backup_tables($tables = '*', $savetopathfile,$conn)
{
  $link = $conn;
  //get all of the tables
  if($tables == '*')
  {
    $tables = array();
    $result = mysql_query('SHOW TABLES',$conn);
    while($row = mysql_fetch_row($result))
    {
      $tables[] = $row[0];
    }
  }
  else
  {
    $tables = is_array($tables) ? $tables : explode(';',$tables);
  }
  
  //cycle through
  foreach($tables as $table)
  {
    $result = mysql_query('SELECT * FROM '.$table);
    $num_fields = mysql_num_fields($result);
    
    $return.= 'DROP TABLE '.$table.';';
    $row2 = mysql_fetch_row(mysql_query('SHOW CREATE TABLE '.$table));
    $return.= "\n\n".$row2[1].";\n\n";
    
    for ($i = 0; $i < $num_fields; $i++) 
    {
      while($row = mysql_fetch_row($result))
      {
        $return.= 'INSERT INTO '.$table.' VALUES(';
        for($j=0; $j<$num_fields; $j++) 
        {
          $row[$j] = addslashes($row[$j]);
          $row[$j] = ereg_replace("\n","\\n",$row[$j]);
          if (isset($row[$j])) { $return.= '"'.$row[$j].'"' ; } else { $return.= '""'; }
          if ($j<($num_fields-1)) { $return.= ','; }
        }
        $return.= ");\n";
      }
    }
    $return.="\n\n\n";
  }
  
  //save file
  $handle = fopen($savetopathfile,'w+');
  fwrite($handle,$return);
  fclose($handle);
}

function returnDEThistoryAStable($pltid,$spptid,$conn) {
if ($pltid>0 && ($spptid+0)==0) {
	$qq = "SELECT DetID FROM Plantas JOIN Gazetteer USING(GazetteerID) WHERE PlantaID='".$pltid."'";
} elseif ($spptid>0) {
	$qq = "SELECT DetID FROM Especimenes WHERE EspecimenID='".$spptid."'";
}
$qr = mysql_query($qq,$conn);
$rw = mysql_fetch_assoc($qr);
$detid = $rw['DetID'];
$detarr = getdetsetvar($detid,$conn);
$detset = serialize($detarr);
$dettext = describetaxa($detset,$conn);
if ($pltid>0) {
	$qq = "SELECT DISTINCT DetID FROM ChangePlantas WHERE PlantaID='".$pltid."' AND DetID>0 ORDER BY ChangedDate DESC";
} elseif ($spptid>0) {
	$qq = "SELECT DISTINCT DetID FROM ChangeEspecimenes WHERE EspecimenID='".$spptid."' AND DetID>0 ORDER BY ChangedDate DESC";
}
$qr = mysql_query($qq,$conn);
$nqr = mysql_numrows($qr);
$result = array();
if ($nqr>0) {
	$i=0;
	while ($row = mysql_fetch_assoc($qr)) {
			$did = $row['DetID'];
			if ($did!=$detid && $did>0) {
				$dearr = getdetsetvar($did,$conn);
				$deset = serialize($dearr);
				$detext = describetaxa($deset,$conn);
				$result[] = $detext;
			}
	}
} 
return($result);
}

function makedescription2($iddsarr,$traitidsarr,$typeid,$img=FALSE,$traitidtobreak,$traitstobreakarr,$printempty=TRUE,$printN,$english=FALSE,$conn) {
		$spidsused = array();
		$spnotused = array();

		//the whole form traitid and traitorder
		if (!empty($traitidsarr)) {
			$traitids = explode(";",$traitidsarr);
		} else {
			return FALSE;
		}

		//the traitids to break se for o caso
		if (!empty($traitstobreakarr)) {
			$traitstobreak = explode(";",$traitstobreakarr);
		}

		//verificar se foi especificado que deve haver um descricao separada das variaveis em traitstobreakarr, para cada estado de variacao da variavel  traitidtobreak e organizar se for o caso
		$newtraitarray = array();
		if ($traitidtobreak>0 && !empty($traitstobreakarr)) {

			//cria um array vazio para armazenar a nova ordem de variaveis e eliminar aqueles contidos em traitstobreakarr
			$newtraitids = array();

			//para cada variavel no formulario de variaveis da descricao (traitids), checa se ele esta no formulario das variaveis que devem ser descritas separadamente para cada estado de variacao do caractere definido por traitidtobreak
			foreach ($traitids as $tid) {
				//se a variavel nao estiver no formulario traitstobreakarr, entao nao precisa de descricao separada para esta variavel e pode adicionar ao novo array para a descricao
				if (!in_array($tid,$traitstobreak) || $tid==$traitidtobreak) {
					$newtraitids[] = $tid;
				}
			}
			//se a variavel para separar variaveis nao estiver na nova lista, entao e porque ela nao estava no formulario da descricao e sera adicionada no final das demais variaveis
			if (!in_array($traitidtobreak,$newtraitids)) {
				$newtraitids[] = $traitidtobreak;
			} 

			//para cada variavel 
			$merged=0;
			foreach ($newtraitids as $tid) {

				//prepar a lista de amostras para cada variavel

				//se a variavel nao for a variavel para separar a descricao por seus estados de variacao, entao a lista sao todos as amostras no filtro. Caso contrario, seleciona as amostras para cada estado de variacao
				if ($tid!=$traitidtobreak) {
					$newtraitarray["tid_".$tid] = $iddsarr;
				} elseif ($tid==$traitidtobreak) {
					//cria um array para separar a lista de amostras para cada estado
					$breakstates = array();

					//para cada estado de variacao do variavel traiitdtobreak pega a lista de amostras correspondentes
					$qq = "SELECT * FROM Traits WHERE ParentID='".$traitidtobreak."'";
					$ror = mysql_query($qq,$conn);
					while ($rwo = mysql_fetch_assoc($ror)) {
						$stateid = $rwo['TraitID'];
						$statespecs = array();
						//para cada amostra no set verifica se ela tem variacao
						foreach ($iddsarr as $spid) {
							$qq = "SELECT * FROM Traits_variation as tvar JOIN Traits as tnames USING(TraitID) WHERE TraitID='".$traitidtobreak."' AND ". $typeid."='".$spid."' AND TraitVariation LIKE '".$stateid."'";
							$rro = mysql_query($qq,$conn);
							$nrro = mysql_numrows($rro);
							//se a amostra tem variacao para o estado de variacao, entao adiciona a mostra na lista das amostras para o estado de variacao 
							if ($nrro>0) {
								$statespecs[] = $spid;
							}
						}
						//se ha 1 ou mais amostras para o estado de variacao, entao coloca o a lista de amostras no array de resultado que contem todos os estados de variacao e suas respectivas amostras...
						if (count($statespecs)>0) {
							$breakstates['stateid_'.$stateid] = $statespecs;
						}
					}
					//se ha pelo menos 1 estado de variacao com amostras, entao adiciona a variavel na ordem para a descricao
					if (count($breakstates)>0) {
						$newtraitarray["tid_".$tid] = $breakstates;
						$merged++;
					} 
				} 
			}

			//se o trait to break nao encontrou amostras, readiciona as variaveis
			if ($merged==0) {
				foreach ($traitstobreak as $tid) {
					$newtraitarray["tid_".$tid] = $iddsarr;
				}
			}
		} else {  //caso contrario adiciona a lista total de amostras todas as variaveis do formulario
			foreach ($traitids as $tid) {
					$newtraitarray["tid_".$tid] = $iddsarr;
			}
		}

		//cria a lista vazias para armazenar as lista de amostras utilizadas e nao utilizadas na descricao para fazer a lista de material examinado e material adicional listado, respectivamente
		$specids_notused = array();
		$specids_used = array();

		//começa a fazer a descricao e cria um objeto com texto vazio para concatenacao e uso no loop de cada variavel na lista
		$mydescription = '';
		//nome da classe da variavel 
		$prev_pathname = '';
		$ja_ind = 0;
		//numero de variaveis no array
		$nvnarr = count($newtraitarray)-1;
		//indice do loop
		$ih =0;
		foreach ($newtraitarray as $tids => $specids) {
			//pega o traitid
			$td = explode("_",$tids);
			$cid = $td[1];

			$qq = "SELECT * FROM Traits WHERE TraitID='".$cid."'";
			$roq = mysql_query($qq,$conn);
			$roqw = mysql_fetch_assoc($roq);

			//pega o nome da variavel e da classe da variavel
			if ($english) {
				$tname = $roqw['TraitName_English'];
				$cur_pathname = $roqw['PathName_English'];
			} else {
				$tname = $roqw['TraitName'];
				$cur_pathname = $roqw['PathName'];
			}

			//inclui, se for o caso de $cid=$traitidtobreak, a descricao para as variaveis de cada estado; neste caso $specids e um array com listas de amostras para cada estado
			if ($traitidtobreak>0 && !empty($traitstobreakarr) && $cid==$traitidtobreak) 		{

				//descricao por estado de traitidtobreak
				$breakdescription ='';

				//array para armazenar a lista de amostras usadas na descricao dessa parte
				$idarr = array();

				$nnrt = count($traitstobreak)-1;

				$key = array_search($traitidtobreak,$traitstobreak);
				if ($traitstobreak[$key]==$traitidtobreak) {
						unset($traitstobreak[$key]);
				}

				$nbrstates = count($specids);
				//para cada estado de variacao descreve a variacao
				$ihtt = 0;
				foreach ($specids as $statekey => $statespecs) {
						//se apenas 1 amostra por estado, entao cria array para funcionar abaixo
						if (!is_array($statespecs)) { 
							$statespecs = array($statespecs);
						}
						//pega o id to estado de variacao
						$tdd = explode("_",$statekey);
						$stid = $tdd[1];

						//number de amostras que tem o estado de variacao
							$nstate = count($statespecs);

						//seleciona as definicoes do estado
						$qq = "SELECT * FROM Traits WHERE TraitID='".$stid."'";
						$roqs = mysql_query($qq,$conn);
						$roqsw = mysql_fetch_assoc($roqs);

						//se a mais de um tipo de flor, entao coloca o valor, caso contrario nao especifica o adjetivo
						if ($nbrstates>1) {
							if ($english) {
									$statename2 = $roqsw['PathName_English'];
								} else {
									$statename2 = $roqsw['PathName'];
							}
						} else {
							$statename2 = '';
						}
						$iht =0;
						$previouspathname = $prev_pathname;
						$jaindumento = 0;
						foreach ($traitstobreak as $trbr) {
							$st = printtrait_variation($statespecs,$trbr,$typeid,$printN,$english,$conn);
							$statevarname = trim($st['variation']);
							$specids_notused = array_merge((array)$st['specids_notused'],(array)$specids_notused);
							$specids_used = array_merge((array)$st['specids_used'],(array)$specids_used);

							//se indicar quando a variacao para o caractere estiver vazia
							if (empty($statevarname) && $printempty) {
								$statevarname = GetLangVar('nameindisponivel');
							}

							if (!empty($statevarname)) {
								$qq = "SELECT * FROM Traits WHERE TraitID='".$trbr."'";
								$rses = mysql_query($qq,$conn);
								$rsesw = mysql_fetch_assoc($rses);
								//echopre($rsesw);
								//get the class name
								if ($english) {
									$currentpathname = $rsesw['PathName_English'];
								} else {
									$currentpathname = $rsesw['PathName'];
								}
								$tres =  printtraitname($currentpathname,$previouspathname,$statename2,$jaindumento,$conn);
								$pclassTorF = $tres[1];
								$toprint = trim($tres[0]);
								$currentpathname = $tres[2];
								$jaindumento = $tres[3];
								if ($iht<=$nnrt && $iht>0 && !$pclassTorF && !empty($toprint)) {
									$toprint = "; ".$toprint;
								} else {
									if ($pclassTorF || $iht>0) {
										$toprint = ". ".$toprint;
									} elseif ($jaindumento>1 && empty($toprint)) {
										$toprint = ", ".$toprint;
									}
								}
								$breakdescription = trim($breakdescription);
								if (!$printempty && $statevarname==GetLangVar('nameindisponivel')) {
								} else {
									$breakdescription .= $toprint." ".$statevarname;
									$iht++;
								}
							}
							$previouspathname = $currentpathname;
						}
						if ($ihtt>0 ) { $breakdescription .= ". ";}
						$ihtt++;
						$idarr = array_merge((array)$idarr,(array)$statespecs);
					}
					$breakdescription = str_replace("..",".",$breakdescription);
					$st = printtrait_variation($idarr,$cid,$typeid,$printN,$english,$conn);
					$varbasename = $st['variation'];
					$specids_notused = array_merge((array)$st['specids_notused'],(array)$specids_notused);
					$specids_used = array_merge((array)$st['specids_used'],(array)$specids_used);
					$varname = $varbasename.". ".trim($breakdescription);

				} else { //end break description
					$st = printtrait_variation($specids,$cid,$typeid,$printN,$english,$conn);
					$varname = $st['variation'];
					$specids_notused = array_merge((array)$st['specids_notused'],(array)$specids_notused);
					$specids_used = array_merge((array)$st['specids_used'],(array)$specids_used);
				}
				if (empty($varname) && $printempty) {
					$varname = GetLangVar('nameindisponivel');
				}
				if (!empty($varname)) {
					$trrs = printtraitname($cur_pathname,$prev_pathname,'',$ja_ind,$conn);
					$pcltorf = $trrs[1];
					$toprint = $trrs[0];
					$cur_pathname = $trrs[2];
					$ja_ind = $trrs[3];
					if ($ih<=$nvnarr && $ih>0 && !$pcltorf && !empty($toprint)) {
						$toprint = "; ".$toprint;
					} else {
						if ($pcltorf && $ih>0) {
							$toprint = ". ".$toprint;
						} elseif ($ja_ind>1 && empty($toprint)) {
							$toprint = ", ".$toprint;
						} 
					}
					/////////
					$mydescription = $mydescription.$toprint." ".$varname;
					$ih++;
					$prev_pathname = $cur_pathname;
				}
				unset($varname,$nname,$traitname,$pathname,$varunit,$breakdescription);
	}

	$specids_notused = array_unique($specids_notused);
	$specids_used = array_unique($specids_used);
	foreach($specids_used as $vv) {
		if (in_array($vv,$specids_notused)) {
			$key = 	array_search($vv,$specids_notused);
			unset($specids_notused[$key]);
		}
	}
	$mydescription = trim($mydescription);
	$mydescription = $mydescription.".";
    $mydescription = str_replace("..",".",$mydescription);

	$resultado = array();
	$resultado['mydescription'] = $mydescription;
	$resultado['specids_notused'] = $specids_notused;
	$resultado['specids_used'] = $specids_used;
	return $resultado;
}

function printtraitfrom($traitid, $traittipo,$multiselect, $traituunit , $conn) {
echo " <table class='clean'>";
	//se categoria
if ($traittipo=='Variavel|Categoria') {
	$tname = "traitvar_".$traitid;
	$val = eval('return $'.$tname.';');
	$val = trim($val);
	//opcoes de variaves categoricas
	echo "
      <tr class='cl'>";
	if ($multiselect!='Sim') {
		if (empty($val) || $val=='none') {
			$txt = 'checked';
		}
		echo "
        <td class='cl'><input type='radio' ".$txt." name='".$tname."' value='none' />".$noneword."</td>";
		} 
	else {
		echo "
          <td class='cl'><input type='hidden' name='".$tname."' value=' ' /></td>";
	}
	echo "
          <td class='cl'>";
		$qq = "SELECT * FROM Traits WHERE ParentID='".$traitid."' ORDER BY TraitName";
		$res = mysql_query($qq,$conn);
		$nres = mysql_numrows($res);
		echo "
            <table class='clean'>"; 
	$cr = 0;
	while ($rw= mysql_fetch_assoc($res)) { //para cada estado de variacao
		if ($multiselect=='Sim') {
				$typein = 'checkbox';
				$tname = "traitmulti_".$traitid."_".$rw['TraitID'];
				$valor = eval('return $'.$tname.';');
		} else {
				$typein='radio';
				$tname = "traitvar_".$traitid;
				$valor = eval('return $'.$tname.';');
		}
		if ($cr % 4 == 0 || $cr==0) {
			echo "
              <tr class='cl'>";
	    }
		//$val = trim($val);
		$tid = $rw['TraitID'];
		$stnomen = $rw['TraitName'];
		$stdefien = $rw['TraitDefinicao'];
		$stnomen = str_replace(" ","&nbsp;",$stnomen);
	    echo "
                <td class='cl'>
                  <table class='clean'>
                    <tr class='cl'>
                      <td class='cl' align='right'>
                        <input type='".$typein."' name='$tname' ";
						if ($valor==$rw['TraitID']) {echo " checked='checked' ";}
						echo " value='".$rw['TraitID']."'  /></td>
                      <td class='cl' align='left'>".$stnomen."</td>
                      <td class='cl' align='left'><img height='12' src=\"icons/icon_question.gif\" ";
						$help = $stdefien;
			echo " onclick=\"javascript:alert('$help');\" alt='Explica variável' />&nbsp;</td>
                    </tr>
                  </table>
                </td>";
		$cr++;
		if ($cr % 4 == 0 || $cr==$nres) {
			echo "
              </tr>";
	    }
	} 
	echo "
            </table>
        </td>
    </tr>";
}
//se quantitativo
if ($traittipo=='Variavel|Quantitativo') {
	$string = 'traitvar_'.$traitid;
	if (!isset($ppost[$string])) {
		$val = eval('return $'. $string . ';');
	} else {
		$val= $ppost[$string];
	}
	echo "
    <tr class='cl'>
      <td class='cl'><input name='traitvar_".$traitid."' value='$val' /></td>
      <td class='cl'>
        <select name='traitunit_".$traitid."'>";
			$string = 'traitunit_'.$traitid;
			$valu = eval('return $'. $string . ';');
			if (empty($valu) && !empty($traituunit )) {
				echo "
          <option selected='selected' value='".$traituunit ."'>".$traituunit ."</option>";
		} elseif (!empty($valu)) {
				echo "
          <option selected='selected' value='".$valu."'>".$valu."</option>";
		}
		$qq = "SELECT * FROM VarLang WHERE VariableName LIKE '%traitunit%' ORDER BY '$lang' ASC";
		$res = mysql_query($qq,$conn);
		if ($res) {
		while ($rwu=mysql_fetch_assoc($res)) {
			$varname = $rwu['VariableName'];
			$zz = explode("_",$varname);
			if ($zz[1]!='desc') {
				$subsname = 'traitunit'.$menugrp;
				echo "
          <option value='".GetLangVar($varname)."'>".GetLangVar($varname)."</option>";
			}
		}
		}
	echo "
        </select>
      </td>
    </tr>";
}
//se imagem
if ($traittipo=='Variavel|Imagem') {
	$string = 'trait_'.$traitid ;
	$imgfile = 'traitimg_'.$traitid ;
	$vval = eval('return $'.$string.';');
	$valimg = explode(";",$vval);
	$oldimgvals = eval('return $'.$string.';');
	$str = 'traitimgautor_'.$traitid;
	$valautors = eval('return $'.$str .';');
	if (count($valimg)>0) {
		echo "
    <tr class='cl'>
      <td class='cl' colspan='2'>
          <input type='hidden' name ='traitimgold_".$traitid."' value='".$oldimgvals."' />
      </td>
    </tr>";
		foreach ($valimg as $kk => $vv) {
			$vv = $vv+0;
			if ($vv>0) {
				$qq = "SELECT * FROM Imagens WHERE ImageID='".$vv."'";
				$rt = mysql_query($qq,$conn);
				$rtw = mysql_fetch_assoc($rt);

				//diretorios das imagens
				$pthumb = 'img/thumbnails/';
				$imgbres = 'img/lowres/';
				$path = 'img/copias_baixa_resolucao/';
				$orgpath = 'img/originais/';

				$imagid = $rtw['ImageID'];
				$filename = trim($rtw['FileName']);

				$autor = $rtw['Autores'];
				$autorarr = explode(";",$autor);
				if (count($autorarr)>0) {
					$j=1;
					foreach ($autorarr as $aut) {
						$qq = "SELECT * FROM Pessoas WHERE PessoaID='".$aut."'";
							$res = mysql_query($qq,$conn);
							$rwr = mysql_fetch_assoc($res);
						if ($j==1) {
							$autotxt = 	$rwr['Abreviacao'];
						} else {
							$autotxt = $autotxt."; ".$rwr['Abreviacao'];
						}
						$j++;
					}
				} 

				$fotodata = $rtw['DateOriginal'];
				$fnl = trim($filename);
				if (file_exists($orgpath.$filename) && $fnl!='') {
					$fn = explode("_",$filename);
					unset($fn[0]);
					unset($fn[1]);
					$fntxt = '';
					$fn = implode("_",$fn);
					if (!empty($autotxt)) { 
						$fntxt = $fn." [".GetLangVar('namefotografo').": ".$autotxt;
					}
					if (!empty($fotodata)) {
						if ($fntxt!='') {
							$fntxt .= " - ".$fotodata."]  ";
						} else {
							$fntxt = $fn." [".$fotodata."]  ";
						}
					} elseif ($fntxt!='') {
						$fntxt .= "]  ";
					}

					if ($fntxt=='') {
						$fntxt = $fn;
					} 

					echo "
    <tr class='cl'>
      <td class='cl' colspan='2'>
        <table class='clean'>
          <tr class='cl' >
            <td class='cl' ><a href=\"".$imgbres.$filename."\" class='MagicZoomPlus'  rel=\"zoom-position:right;zoom-height:200px; zoom-fade:true; smoothing-speed:17;opacity-reverse:true;\" ><img width=\"40\" src=\"".$pthumb.$filename."\" alt='Imagem' /></a></td>
            <td class='cl' >&nbsp;</td>
            <td class='tinny' id='fname_".$traitid."_".$imagid."'  class='tdformnotes'>$fntxt";
			$fndeleted = "<STRIKE>$fn</STRIKE>";
			echo "
            </td>  
            <td class='cl' ><img height='15' src=\"icons/application-exit.png\" onclick=\"javascript:deletimage('fnamedeleted_".$traitid."_".$imagid."','fname_".$traitid."_".$imagid."','imgtodel_".$traitid."_".$imagid."',1);\" /></td>
            <td class='cl' ><img height='15' src=\"icons/list-add.png\" onclick=\"javascript:showimage('fnameundeleted".$traitid."_".$imagid."','fname_".$traitid."_".$imagid."','imgtodel_".$traitid."_".$imagid."',0);\" /></td>
            <td class='cl' >
              <input type='hidden' id='fnamedeleted_".$traitid."_".$imagid."' value='$fndeleted' />
              <input type='hidden' id='imgtodel_".$traitid."_".$imagid."' name='imgtodel_".$traitid."_".$imagid."' value='' />
              <input type='hidden' id='imagid_".$traitid."_".$imagid."' name='imagid_".$traitid."_".$imagid."' value='$imagid' />
              <input type='hidden' id='fnameundeleted".$traitid."_".$imagid."' value='$fntxt' />
            </td>
          </tr>
        </table>
      </td>
    </tr>";
				} 
				else {
					$refname = 'traitimg_'.$traitid;
					$val = eval('unset($'.$refname.');');
				}
			}
		}
	}
	echo "
    <tr class='cl'>
      <td class='cl'>";
		$varname = 'trait_'.$traitid;
		echo "
        <input type=\"file\" name=\"$varname\" />
        <script type=\"text/javascript\">
          window.addEvent('domready', function(){ new MultiUpload($( '".$myformname."' ).$varname );});
        </script>
        <input type='hidden' name='traitimg_".$traitid."' value='imagem' />
      </td>
      <td class='cl'>
        <table class='dettable'>
          <tr>
            <td class='cl'>".GetLangVar('namefotografo')."s</td>
              <td>
                <input type='hidden' name='traitimgautor_".$traitid."' value='".$valautors."' />
                <input type='text' name='traitimgautortxt_".$traitid."' value='".$addcoltxt."' readonly='readonly' />
              </td>
              <td><input type='button' value=\"".GetLangVar('nameselect')."\" class='bsubmit' ";
				$valuevar = "traitimgautor_".$traitid;
				$valuetxt = "traitimgautortxt_".$traitid;
				$myurl ="addcollpopup.php?getaddcollids=$valautors&amp;valuevar=$valuevar&amp;valuetxt=$valuetxt&amp;formname=varform2"; 
				echo " onclick = \"javascript:small_window('$myurl',400,400,'Add_from_Src_to_Dest');\" /></td>
            </tr>
          </table>
        </td>
    </tr>";
}
//se texto
if ($$traittipo=='Variavel|Texto') {
	echo "
    ";
	$string = 'traitvar_'.$traitid;
	if (!isset($ppost[$string])) {
		$valtxt = eval('return $'. $string . ';');
	} else {
		$valtxt= $ppost[$string];
	}
	//tem um problema aqui quando apaga os dados
	echo "
    <tr class='cl'>
      <td class='cl'>
        <input type='hidden' name='traitnone_".$traitid."' value='none' />
        <textarea name='traitvar_".$traitid."' cols='80' rows='3' >".$valtxt."</textarea>
      </td>
    </tr>";
}
echo "
  </table>";
}
?>
