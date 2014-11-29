<?php
session_start();
//Check whether the session variable
include "functions/HeaderFooter.php";
include "functions/SelectOptions.php";

$lang = $_SESSION['lang'];
$dbname = $_SESSION['dbname'];
$conn = ConectaDB($dbname);
$uuid = cleanQuery($_SESSION['userid'],$conn);
if(!isset($uuid) || (trim($uuid)=='')) {
	header("location: access-denied.php");
	exit();
}

$ppost = cleangetpost($_POST,$conn);
$arval = $ppost;
@extract($ppost);
$gget = cleangetpost($_GET,$conn);
@extract($gget);

//echopre($_FILES);
//echopre($ppost);

$body= '';
$title = GetLangVar('nameeditar')." ".GetLangVar('namelocalidade');
PopupHeader($title,$body);

$erros=0;

//FAZ O CADASTRO DA LOCALIDADE QUANDO CLICA SALVAR OU QUANDO VOLTA DE TER FEITO THUM
$lixo = 11;
if ($lixo>10) {

if ($ppost['enviado']=='1' || isset($imgdone)) {

//FAZ O REGISTRO SE NAO FOR RETORNO DA PRODUCAO DE THUMBNAILS DE IMAGENS DE PARCELA
if (!isset($imgdone)) {
//ELIMITA REDUNDANCIAS DE CODIGO RUIM
echo "
<table  align='center' class='erro' border=\"0\" cellpadding=\"0\" cellspacing=\"0\" width=\"80%\">";
	//if ($gazetteertipo=='digite aqui um novo tipo') {
	//	unset($gazetteertipo);
	//}
	//if (empty($gazetteertipo) && !empty($normalizedtipo)) {
	//	$ggtipo = $normalizedtipo;
	//} elseif (!empty($gazetteertipo)) {
	//	$ggtipo = $gazetteertipo;
	//}

//CHECA POR CAMPOS OBRIGATÓRIOS
	//if (empty($ggtipo) || empty($gazetteer)) {
	if (empty($gazetteer)) {
		echo "
<tr><td colspan=2>".GetLangVar('nameobrigatorio').": <i>".GetLangVar('namenome')."</i></td></tr>";
//& ".GetLangVar('nametipo')."</i></td></tr>";
		$erros++;
	} 

//CHECA VALOR DAS COORDENADAS GEOGRAFICAS E GERA EM DECIMOS DE GRAUS PARA ARMAZENAMENTO
	$coord = coordinates('','',$latgrad,$longgrad,$latminu,$longminu,$latsec,$longsec,$latnors,$longwore);
	@extract($coord);
	if ($longsec>60 || $latminu>60 || abs($latgrad)>180 || $longsec>60 || $longminu>60 || abs($longgrad)>180) {
			echo "
<tr><td colspan=2>".GetLangVar('namecoordenadas')." > 60 </td></tr>";
			$erros++;
		}
echo "</table><br>";

//CHECA VALORES DE DIMENSOES DE PARCELA, SE FOR O CASO
$dm = ($dimx+0)+($dimy+0);
if ($dm>0) {
	if (($dimx+0)==0 || ($dimy+0)==0) {
			echo "
<tr><td colspan=2>PARCELA: Valores de dimx OU de dimy está incorreto! Ou não é numérico ou não é >0</td></tr>";
			$erros++;
	}
	if ($parentgazid>0) {
		$qq = "SELECT DimX,DimY FROM Gazetteer WHERE GazetteerID=".$parentgazid;
		$rq = mysql_query($qq,$conn);
		$rqw = mysql_fetch_assoc($rq);
		if (($rqw['DimX']+0)>0 && (($startx+0)==0 || ($starty+0)==0)) {
		echo "
<tr><td colspan=2>PARCELA: Esta localidade é uma subparcela de uma parcela de ".$rqw['DimX']."x".$rqw['DimY']." -- precisa indicar a Posição X e Posição Y da subparcela na parcela</td></tr>";
			$erros++;
		}
	}
}
if (isset($dimrad)) {
	if ($dimrad!=($dimrad+0)) {
			echo "
<tr><td colspan=2>PARCELA: Valores de dimrad não é numérico</td></tr>";
			$erros++;
	} 
	if ($parentgazid>0) {
		$qq = "SELECT DimDiameter,DimX,DimY FROM Gazetteer WHERE GazetteerID=".$parentgazid;
		$rq = mysql_query($qq,$conn);
		$rqw = mysql_fetch_assoc($rq);		
		if ((($rqw['DimX']+0)>0 || ($rqw['DimDiameter']+0)>0) && (($startx+0)==0 || ($starty+0)==0)) {
		echo "
<tr><td colspan=2>PARCELA: Esta localidade é uma subparcela de uma parcela de ".$rqw['DimX']."x".$rqw['DimY']." -- precisa indicar a Posição X e Posição Y da subparcela na parcela</td></tr>";
			$erros++;
		}
	}
}

//CASO NAO TENHA DADO NENHUM ERRO, FAZ O CADASTRO DE FATO
if ($erros==0) {
	//ADICIONA UMA COLUNA NOVA CASO SEJA NECESSARIO.
	$qal = "ALTER TABLE `Gazetteer`  ADD `DimDiameter` FLOAT(10) NOT NULL AFTER `DimY`";
	@mysql_query($qal,$conn);

	//DEFINE O ARRAY PARA ARMAZENAR OS RESULTADOS
	$fieldsaskeyofvaluearray = array(
	'ParentID' => $parentgazid,
	'Gazetteer' => $gazetteer,
	'MunicipioID' => $municipioid,
	//'GazetteerTIPOtxt' => $gazetteertipo,
	'Notas' => trim($gazetteernota),
	'Latitude' => $latdec,
	'Longitude' => $longdec,
	 'Altitude' => $altitude,
	 'StartX'=> $startx,
	 'StartY'=> $starty,
	 'DimX'=> $dimx,
	 'DimY'=> $dimy,
	 'DimDiameter' => $dimrad
	 );
	
	//EXTRAI NOVAMENTE AS COORDENADAS (PRECISA DISSO? AV 2012-AGO-26 
	$coord = coordinates($latdec,$longdec,$latgrad,$longgrad,$latminu,$longminu,$latsec,$longsec,$latnors,$longwore);
	@extract($coord);
	
	//SE NAO ESTIVER EDITANDO O REGISTRO É NOVO
	if ($_SESSION['editando']!=1)  {
			//$check = "SELECT * FROM Gazetteer WHERE GazetteerTIPOtxt='$gazetteertipo' AND Gazetteer='$gazetteer' AND ParentID='$parentgazid'";
			$check = "SELECT * FROM Gazetteer WHERE LOWER(Gazetteer)=LOWER('".$gazetteer."') AND ParentID='$parentgazid'";
			$res = @mysql_query($check,$conn);
			$nres = mysql_numrows($res);
			if ($nres>0) { //se ja tem um com esse nome
			 echo "
<table align='center' class='erro' cellpadding=\"5\" cellspacing=0 width='90%'>
<tr><td>".GetLangVar('erro3')."</td></tr>
</table>";
			} else {
			$newgazid = InsertIntoTable($fieldsaskeyofvaluearray,'GazetteerID','Gazetteer',$conn);
			if ($newgazid) {
				$gazetteerid = $newgazid;
				//UpdateGazetteerPath($newgazid,$conn);
  				$qupd = "UPDATE Gazetteer SET PathName=upgazpath(GazetteerID) WHERE GazetteerID=".$gazetteerid;
				@mysql_query($qupd,$conn);	
			 echo "<table align='center' class='success' cellpadding=\"5\" cellspacing=0 width='90%'><tr>
				<td>".GetLangVar('sucesso1')."</td></tr>
				";
			} else {
			 echo "<table align='center' class='erro' cellpadding=\"5\" cellspacing=0 width='90%'><tr>
				<td>Erro!</td></tr>
			";
			}
			echo "
			</table>";
			}
	} 
	//SE ESTIVER EDITANDO
	else {
			//ATUALIZA SE HOUVER DIFERENCA DOS VALORES
			$check = "SELECT * FROM Gazetteer WHERE GazetteerID=".$gazetteerid;
			$ch = mysql_query($check,$conn);
			$chh = mysql_fetch_assoc($ch);
			$update=0;
			foreach ($fieldsaskeyofvaluearray as $kj => $vj) {
				if ($chh[$kj]!=$vj) {
					$update++;
				}
			}
			if ($update>0) {
				CreateorUpdateTableofChanges($gazetteerid,'GazetteerID','Gazetteer',$conn);
				$newgazid = UpdateTable($gazetteerid,$fieldsaskeyofvaluearray,'GazetteerID','Gazetteer',$conn);
				if (!$newgazid) {
					$erro++;
				} else {
					//ATUALIZA O PATH DO GAZETTEER
					//UpdateGazetteerPath($gazetteerid,$conn);
					$qupd = "UPDATE Gazetteer SET PathName=upgazpath(GazetteerID) WHERE GazetteerID=".$gazetteerid;
					@mysql_query($qupd,$conn);
				}
			}
			//CHECA SE APAGOU ALGUMA IMAGEM E TIRA
			$qq = "SELECT * FROM Imagens WHERE GazetteerID=".$gazetteerid;
			$rrr = @mysql_query($qq,$conn);
			$plotimages = array();
			$imgerro =0;
			while ($rw = @mysql_fetch_assoc($rrr)) {
				$tvar = 'imgtodel_'.$rw['ImageID'];
				if ($ppost[$tvar]==1) {
					$imgarray = array('GazetteerID' => 0);
					CreateorUpdateTableofChanges($rw['ImageID'],'ImageID','Imagens',$conn);
					$updateimageid = UpdateTable($rw['ImageID'],$imgarray,'ImageID','Imagens',$conn);
					if (!$updateimageid) {
						$imgerro++;
					}
				}
			}
			if ($erro==0) {
				 echo "
<table align='center' class='success' cellpadding=\"5\" cellspacing=0 width='90%'>
<tr><td>".GetLangVar('sucesso1')."</td></tr>
</table><br>";
			} else {
				echo "
<table align='center' class='erro' cellpadding=\"5\" cellspacing=0 width='90%'>
<tr><td>Erro!</td></tr></table><br>";
			}
			if ($imgerro>0) {
				 echo "
<table align='center' class='erro' cellpadding=\"5\" cellspacing=0 width='90%'>
<tr><td>WARNING! Não foi possível apagar a imagem!</td></tr>
</table><br>";
			}
			
	}
	
//CASO TENHA SUBIDO IMAGENS PARA PARCELA, REGISTRA ELAS, E ENVIA PARA PRODUZR THUMBNAILS
    $arvalfiles = $_FILES;
	if ($gazetteerid>0 && count($arvalfiles)>0 && $erro==0) {
	////////////////////////////////
	$newimagefile = array();
	foreach ($arvalfiles as $value) { //para cada IMAGEM no array
		//SE FOR IMAGEM ENTAO É UM ARRAY
		if (is_array($value)) {
		$fname = trim($value['name']);
        move_uploaded_file($value["tmp_name"],"img/temp/$fname");  //move o arquivo para a pasta final MAS AQUI PODERIA SER TEMPORARIO PORQUE AINDA NAO GRAVOU OS DADOS
		
		$ext = explode(".",$value['name']);
		$ll = count($ext)-1;
		$imgext = strtoupper($ext[$ll]);
		$inputfile = "img/temp/$fname";
		if ($imgext=='JPG' || $imgext=='TIFF' || $imgext=='TIF' || $imgext=='JPEG') {
				$metadata = @read_exif_data($inputfile);
				$DateTimeOriginal =$metadata['DateTimeOriginal'];
				$dattt = explode(" ",$DateTimeOriginal);
				$dateoriginal = $dattt[0];
				$timeoriginal = $dattt[1];
				$tt = explode(":",$timeoriginal);
				$ttsec = (((($tt[0]*60)+$tt[1])*60)+$tt[2]);
				$dd = str_replace(":","-",$dateoriginal);
				$dd = new DateTime($dd);
				$dateoriginal = $dd->format("Y-m-d");
		}		
		
		$sql = "ALTER TABLE `Imagens`  ADD `GazetteerID` INT(10) NOT NULL AFTER `Camera`";
		@mysql_query($sql,$conn);
		$imgarray =  array(
				  'FileName' => $fname,
				  'DateTimeOriginal' => $DateTimeOriginal,
				  'DateOriginal' => $dateoriginal,
				  'TimeOriginal' => $timeoriginal,
				  'GazetteerID' => $gazetteerid);
		if (!empty($fname)) {
		$newimg = InsertIntoTable($imgarray,'ImageID','Imagens',$conn);
		}
		if ($newimg) {
				$copiado = @copy($inputfile,"img/originais/".$fname);
				if ($copiado) {
						unlink($inputfile);
						$newimagefile[] = $fname;

					}
		}
	}
	}

	if (count($newimagefile)>0) {
		$_SESSION['newimagfiles'] = serialize($newimagefile);
		//unset($ppost['enviado']);
	    $ppost = array_merge((array)$ppost,(array)array( 'gazetteerid' => $gazetteerid));
		$_SESSION['othervars'] = serialize($ppost);
		$zz = explode("/",$_SERVER['SCRIPT_NAME']);
		$serv = $_SERVER['SERVER_NAME'];
		$returnto = $serv."/".$zz[1]."/localidadenova-exec.php";
		header("location: http://".$serv."/cgi-local/imagick_function.php?returnto=".$returnto."&folder=".$zz[1]."&returnvar=imgdone");
	} 
	///////////////////////////////
	}
}
}
else {
		unset($_SESSION['newimagfiles']);
		$othvv = unserialize($_SESSION['othervars']);
		@extract($othvv);
		//echopre($othvv);
	}
/////////
	if ($erros==0 && $gazetteerid>0 && isset($gazetteer_val)) {
		$qq = "SELECT GazetteerID,MunicipioID FROM Gazetteer WHERE GazetteerID=".$gazetteerid;
		$res = mysql_query($qq,$conn);
		$rr = mysql_fetch_assoc($res);
		//$gazz = $gazetteertipo." ".$gazetteer;
		$gazz = $gazetteer;
		$gazid = $rr['GazetteerID']."_".$rr['MunicipioID'];
		echo "
<form >
  <input type='hidden' id='gazid' value='".$gazid."'>
  <input type='hidden' id='gazetteer' value='$gazz'>
  <script language=\"JavaScript\">
  setTimeout(
    function() {
      passnewidandtxtoselectfield('".$gazetteer_val."','gazid','".$gazz."','');
    }
    ,0.001);
  </script>
</form>";
	 } else {
echo "
<br>
<table cellpadding=\"7\" align='center' class='success'>
  <tr><td class='tdsmallbold' align='center'><input type='button' value='Concluir' onclick=\"javascript:this.window.close()\"</td></tr>
</table>
<br>";
	}

//////////////	
} //terminou a atualizacao dos dados
else {
	unset($_SESSION['editando']);
	unset($_SESSION['newimagfiles']);
	unset($_SESSION['othervars']);
} 
}
/////////////////////////////////////////////////////////////////
if (!isset($ppost['enviado']) || $erros>0) {
echo "
<table align=\"center\" class=\"myformtable\" cellpadding=\"6\" width=\"800\">
<thead>
<tr>
  <td colspan=\"100%\">";
if (!empty($gazetteerid) && $gazetteerid>0) {
	$qq = "SELECT PathName,ParentID,Municipio,MunicipioID,Province,ProvinceID,Country,CountryID FROM Gazetteer JOIN Municipio USING(MunicipioID) JOIN Province USING(ProvinceID) JOIN Country USING(CountryID) WHERE GazetteerID='".$gazetteerid."'";
	$res = mysql_query($qq,$conn);
	$row = mysql_fetch_assoc($res);
	$municipioid = $row['MunicipioID'];
	$provinciaid = $row['ProvinceID'];
	$paisid = $row['CountryID'];
	$politico = $row['Country']." ".$row['Province']." ".$row['Municipio'];
	$gazparentid = $row['ParentID'];
	$gazpathname = $row['PathName'];
	echo GetLangVar('nameeditar')." ".GetLangVar('namegazetteer');
} 
else {
	echo GetLangVar('namecadastrar')." ".strtolower(GetLangVar('namenova')." ".GetLangVar('namegazetteer'));
}
echo "
  </td>
</tr>";
if ($gazpathname!='') {
echo "
<tr style=\"background-color: #FFCC33; color: #000000; font-size: 0.8em; font-style: regular\">
  <td align=\"center\" colspan=\"100%\">Editando registro para <i>".$gazpathname."</i> [".$politico."]</td>
</tr>";
}
echo "
<tr class=\"subhead\"><td colspan=\"100%\" >".GetLangVar('namegeopolitical')."</td></tr>
</thead>
<tbody>
<tr>
<form action=\"localidadenova-exec.php\" method=\"post\">
  <input type=\"hidden\" name=\"gazetteerid\" value=\"".$gazetteerid."\">
  <input type=\"hidden\" name=\"gazetteer_val\" value=\"".$gazetteer_val."\">
<td colspan=\"100%\">
<table>
  <tr>
  <td class=\"tdformright\">".GetLangVar('namepais')."</td>
  <td >
    <select name=\"paisid\" onchange=\"this.form.submit();\">";
	if (empty($paisid)) {
		$paisid=30;
	} 
	$rr = getpais($paisid,$conn);
	$row = mysql_fetch_assoc($rr);
	echo "
      <option selected value=\"".$row['CountryID']."\">".$row['Country']."</option>";
	$rrr = getpais('',$conn);
	while ($row = mysql_fetch_assoc($rrr)) {
		echo "
      <option value=\"".$row['CountryID']."\">".$row['Country']."</option>";
	}
echo "
    </select>
  </td>
</form>
<form action=\"localidadenova-exec.php\" method=\"post\">
  <input type=\"hidden\" name=\"paisid\" value=\"".$paisid."\">
  <input type=\"hidden\" name=\"gazetteerid\" value=\"".$gazetteerid."\">
  <input type=\"hidden\" name=\"gazetteer_val\" value=\"".$gazetteer_val."\">
<td class=\"tdformright\">".GetLangVar('namemajorarea')."</td>
<td >
  <select name=\"provinciaid\" onchange=\"this.form.submit();\">";
	if (empty($provinciaid)) {
		echo "
    <option>".GetLangVar('nameselect')."</option>";
	} 
	else {
		$rr = getprovincia($provinciaid,$paisid,$conn);
		$row = mysql_fetch_assoc($rr);
		echo "
    <option selected value=\"".$row['ProvinceID']."\">".$row['Province']."</option>";
	}
	$newrr = getprovincia('',$paisid,$conn);
	while ($row = mysql_fetch_assoc($newrr)) {
		echo "<option value=\"".$row['ProvinceID']."\">".$row['Province']."</option>";
	}
echo "
</select>
</td>
</form>
<form action=\"localidadenova-exec.php\" method=\"post\">
  <input type=\"hidden\" name=\"paisid\" value=\"".$paisid."\">
  <input type=\"hidden\" name=\"provinciaid\" value=\"".$provinciaid."\">
  <input type=\"hidden\" name=\"gazetteerid\" value=\"".$gazetteerid."\">
  <input type=\"hidden\" name=\"gazetteer_val\" value=\"".$gazetteer_val."\">
<td class=\"tdformright\">".GetLangVar('nameminorarea')."</td>
<td >
  <select name=\"municipioid\" onchange=\"this.form.submit();\">";
	if (empty($municipioid)) {
		echo "
    <option>".GetLangVar('nameselect')."</option>";
	} 
	else {
		$rr = getmunicipio($municipioid,$provinciaid,$conn);
		$row = mysql_fetch_assoc($rr);
		echo "
    <option selected value=\"".$row['MunicipioID']."\">".$row['Municipio']."</option>";
	}
	$newrr = getmunicipio('',$provinciaid,$conn);
	while ($row = mysql_fetch_assoc($newrr)) {
		echo "
    <option value=\"".$row['MunicipioID']."\">".$row['Municipio']."</option>";
	}
echo "
  </select>
</td>
</form>
</tr>
</table>
</td>
</tr>
</tbody>";
  //se editando
if ($gazetteerid>0) {
	$_SESSION['editando'] =1;
	$qq = "SELECT * FROM Gazetteer WHERE GazetteerID=".$gazetteerid;
	$rr = mysql_query($qq,$conn);
	$row = mysql_fetch_assoc($rr);
	$parentgazid = $row['ParentID'];
	//$gazetteertipo = $row['GazetteerTIPOtxt'];
	$normalizedtipo = $row['Tipo'];
	$gazetteernota = trim($row['Notas']);
	$gazetteer = $row['Gazetteer'];
	$gazpathname = trim($row['PathName']);

	$altitude = $row['Altitude'];
	$longdec = trim($row['Longitude']);
	$latdec = trim($row['Latitude']);

	$dimx = trim($row['DimX']);
	$dimy = trim($row['DimY']);
	$startx = trim($row['StartX']);
	$starty = trim($row['StartY']);
	$dimrad = trim($row['DimDiameter']);

	$qq = "SELECT * FROM Imagens WHERE GazetteerID=".$gazetteerid;
	$rrr = @mysql_query($qq,$conn);
	$plotimages = array();
	while ($rw = @mysql_fetch_assoc($rrr)) {
		$plotimages[] = $rw['ImageID'];
	}
	
	$coord = coordinates($latdec,$longdec,$latgrad,$longgrad,$latminu,$longminu,$latsec,$longsec,$latnors,$longwore);
	@extract($coord);
}
echo "
</table>";
if (!empty($municipioid)) {
echo "
<form id=\"varform2\"  enctype=\"multipart/form-data\" action=\"localidadenova-exec.php\" method=\"post\">
  <input type=\"hidden\" name=\"MAX_FILE_SIZE\" value=\"10000000\">
  <input type=\"hidden\" name=\"paisid\" value=\"".$paisid."\">
  <input type=\"hidden\" name=\"provinciaid\" value=\"".$provinciaid."\">
  <input type=\"hidden\" name=\"municipioid\" value=\"".$municipioid."\">
  <input type=\"hidden\" name=\"formsubmitted\" value=\"".$formsubmitted."\">
  <input type=\"hidden\" name=\"doitnocoord\" value=\"".$doitnocoord."\">
  <input type=\"hidden\" name=\"coordenadasok\" value=\"".$coordenadasok."\">
  <input type=\"hidden\" name=\"gazetteerid\" value=\"".$gazetteerid."\">
  <input type=\"hidden\" name=\"gazpathname\" value=\"".$gazpathname."\">
  <input type=\"hidden\" name=\"normalizedtipo\" value=\"".$normalizedtipo."\">
  <input type=\"hidden\" name=\"gazetteer_val\" value=\"".$gazetteer_val."\">
<table align=\"center\" class=\"myformtable\" cellpadding=\"6\" width=\"800\">
<thead>
<tr class=\"subhead\"><td colspan=\"100%\">".GetLangVar('namegazetteer')." ".GetLangVar('namedefinicao')."</td></tr>
</thead>
<tbody>
";
//if ($bgi % 2 == 0){$bgcolor = $linecolor2 ;}  else{$bgcolor = $linecolor1 ;} $bgi++;
//echo "
//<tr bgcolor = \"$bgcolor\">
//<td class=\"tdsmallbold\" align=\"right\">".GetLangVar('nametipo')."</td>
//<td >
//  <table>
//    <tr>
//      <td>
//        <select id=\"gaztipotxt\" onchange=\"javascript:getselectoptionsendtoinput('gaztipotxt','gazetteertipo');\">";
//		if ($gazetteertipo!='') {
//echo "
//          <option selected value=\"".$gazetteertipo."\">".$gazetteertipo."</option>";
//		} 
//echo "
//          <option value=\"\">".GetLangVar('nameselect')."</option>
//          <option value=\"\">------</option>";
//		$qqq = "SELECT DISTINCT GazetteerTIPOtxt FROM Gazetteer WHERE (GazetteerTIPOtxt<>'' AND GazetteerTIPOtxt IS NOT NULL) ORDER BY GazetteerTIPOtxt";
//		$sql = mysql_query($qqq,$conn);
//		while ($aa = mysql_fetch_assoc($sql)){
//			echo "
//          <option value=\"".$aa['GazetteerTIPOtxt']."\">".$aa['GazetteerTIPOtxt']."</option>";
//		}
//		if (empty($gazetteertipo)) {
//			$gazetteertipo = 'digite aqui um novo tipo';
//		}
//		echo "
//          </select>
//        </td>
//        <td class=\"tdsmallbold\" align=center>".strtolower(GetLangVar('nameor'))."&nbsp;".strtolower(GetLangVar('namenovo')).":</td>
//        <td align=\"left\"><input type=\"text\" id=\"gazetteertipo\" name=\"gazetteertipo\" value=\"".$gazetteertipo."\"></td>
//      </tr>
//    </table>
//  </td>
//</tr>";
//
if ($bgi % 2 == 0){$bgcolor = $linecolor2 ;}  else{$bgcolor = $linecolor1 ;} $bgi++;
echo "
<tr bgcolor =\"".$bgcolor."\">
  <td class=\"tdsmallbold\" align=\"right\">".GetLangVar('namenome')."</td>
  <td>
    <table>
      <tr>
        <td><input type=\"text\" name=\"gazetteer\" value=\"".$gazetteer."\"></td>
      </tr>
      </table>
    </td>
</tr>";

if ($bgi % 2 == 0){$bgcolor = $linecolor2 ;}  else{$bgcolor = $linecolor1 ;} $bgi++;
echo "
<tr bgcolor =\"".$bgcolor."\">
  <td class=\"tdsmallbold\" align=\"right\">".GetLangVar('messagepertencea')." ".GetLangVar('namelocalidade')."</td>
  <td>
    <table>
      <tr>
        <td>
          <select name=\"parentgazid\">";
			if (empty($parentgazid)) {
				echo "
            <option value=\"\">".GetLangVar('novagazetteer2')."</option>";
			} else {
				$rr = getgazetteer($parentgazid,$municipioid,$conn);
				$row = mysql_fetch_assoc($rr);
				echo "
            <option selected value=\"".$row['GazetteerID']."\">".$row['Gazetteer']."</option>";
//                        <option selected value=\"".$row['GazetteerID']."\">".$row['GazetteerTIPOtxt']." ".$row['Gazetteer']."</option>";
			}
			echo "
            <option value=\"\">----</option>";
			//$rr = getgazetteer('',$municipioid,$conn);
			//$qr = "SELECT GazetteerID, GazetteerTIPOtxt, Gazetteer, PathName FROM Gazetteer WHERE MunicipioID=".$municipioid;
			$qr = "SELECT GazetteerID, Gazetteer, PathName FROM Gazetteer WHERE MunicipioID=".$municipioid;
			if ($gazetteerid>0)  {
				$qr .=" AND GazetteerID<>".$gazetteerid;
			}
			//$qr .= " ORDER BY GazetteerTIPOtxt, Gazetteer,PathName";
			$qr .= " ORDER BY Gazetteer,PathName";
			
			$rr = mysql_query($qr,$conn);
			$level = '';
			while ($aa = mysql_fetch_assoc($rr)){
					$PathName = $aa['PathName'];
					//$gaztipo = $aa['GazetteerTIPOtxt'];
					//$trm = $gaztipo." ".$aa['Gazetteer'];
					$trm = $aa['Gazetteer'];
					$txt = str_replace($trm, "", $PathName);
					if ($txt!='') {
						$txt = "[".$txt."]";
					}
					if ($level!=$PathName) {
						echo "
            //<option value=\"".$aa['GazetteerID']."\">".$aa['Gazetteer']." ".$txt."</option>";
            //<option value=\"".$aa['GazetteerID']."\">".$gaztipo." ".$aa['Gazetteer']." ".$txt."</option>";
					} else {
						$espaco = $espaco.'&nbsp;';
						echo "
            <option value=\"".$aa['GazetteerID']."\">".$espaco." ".$aa['Gazetteer']." ".$txt."</option>";
//            <option value=\"".$aa['GazetteerID']."\">$espaco".$gaztipo." ".$aa['Gazetteer']." ".$txt."</option>";

					}
					$level=$PathName;
			}
echo "
          </select>
        </td>
      </tr>
    </table>
  </td>
</tr>";
			//echo $qr."<br>";

if ($bgi % 2 == 0){$bgcolor = $linecolor2 ;}  else{$bgcolor = $linecolor1 ;} $bgi++;
echo "
<tr bgcolor =\"".$bgcolor."\">
  <td class=\"tdsmallbold\" align=\"right\">".GetLangVar('nameobs')."</td>
  <td>
    <table>
      <tr>
        <td>";
		$gaz = trim($gazetteernota);
		if (empty($gaz)) {$gazetteernota='';}
echo " <textarea cols=80 rows=1 name=\"gazetteernota\">".trim($gazetteernota)."</textarea></td>
      </tr>
    </table>
  </td>
</tr>";

if ($bgi % 2 == 0){$bgcolor = $linecolor2 ;}  else{$bgcolor = $linecolor1 ;} $bgi++;
echo "
<tr bgcolor =\"".$bgcolor."\">
  <td class=\"tdsmallbold\" align=\"right\">".GetLangVar('namecoordenadas')."</td>
  <td>
    <table cellpadding=\"2\">
      <tr class=\"tdformnotes\">
        <td align=\"right\"><i>Latitude<font size=\"3\" color=\"red\">*</font></i></td>
        <td >
          <table border=0 cellpadding=\"3\">
            <tr class=\"tdformnotes\">
              <td ><input type=\"text\" size=\"6\" name=\"latgrad\" value=\"".$latgrad."\" ></td>
              <td align=\"left\"><sup>o</sup></td>
              <td ><input type=\"text\" size=\"3\" name=\"latminu\" value=\"".$latminu."\" ></td>
              <td align=\"left\">\"'\"</td>
              <td ><input type=\"text\" size=3 name=\"latsec\" value=\"$latsec\" ></td>
              <td align=\"left\">\"</td>
              <td align=\"right\"><input type=\"radio\" name=\"latnors\" ";
				if ($latnors=='N') { echo "checked";}
					echo " value=\"N\"></td>
              <td align=\"left\">N</td>
              <td align=\"right\"><input type=\"radio\" name=\"latnors\" "; 
					if ($latnors=='S') { echo "checked";}
					echo "  value=\"S\"></td>
              <td align=\"left\">S</td>
            <tr>
          </table>
        </td>
        <td colspan=3>&nbsp;</td>
      </tr>
      <tr>
        <td align=\"right\"><i>Longitude<font size=\"3\" color=\"red\">*</font></i></td>
        <td >
          <table border=0 cellpadding=\"3\">
            <tr class=\"tdformnotes\">
              <td align=\"center\"><input type=\"text\" size=6 name=\"longgrad\" value=\"$longgrad\" ></td>
              <td align=\"left\"><sup>o</sup></td>
              <td align=\"left\"><input type=\"text\" size=3 name=\"longminu\" value=\"$longminu\" ></td>
              <td align=\"left\">\"'\"</td>
              <td align=\"left\"><input type=\"text\" size=3 name=\"longsec\" value=\"$longsec\" ></td>
              <td align=\"left\">\"</td>
              <td align=\"left\">
              <td align=\"right\"><input type=\"radio\" name=\"longwore\" ";
				if ($longwore=='W') { echo "checked";}
					echo " value=\"W\"></td>
              <td align=\"left\">W</td>
              <td align=\"right\"><input type=\"radio\" name=\"longwore\" ";
					if ($longwore=='E') { echo "checked";}
					echo "  value=\"E\"></td>
              <td align=\"left\">E</td>
            </tr>
          </table>
        </td>
        <td >&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;</td>
        <td align=\"right\"><i>Altitude</i></td>
        <td >
          <table border=0 cellpadding=\"3\">
            <tr class=\"tdformnotes\">
              <td align=\"center\"><input type=\"text\" size=6 name=\"altitude\" value=\"$altitude\"></td>
              <td align=\"left\">m</td>
            </tr>
          </table>
        </td>
      </tr>
    </table>
  </td>
</tr>";
$txt = "se em décimo de grau ou de minuto inserir como casa decimal (\".\"), nos campos grau e minuto, respectivamente";
echo "
<tr bgcolor =\"".$bgcolor."\">
  <td>&nbsp;</td><td align=\"left\" class=\"tdformnotes\" style=\"color: red;\">&nbsp;&nbsp;*&nbsp;".$txt."</td>
</tr>";

if ($bgi % 2 == 0){$bgcolor = $linecolor2 ;}  else{$bgcolor = $linecolor1 ;} $bgi++;
echo "
<tr bgcolor =\"".$bgcolor."\">
  <td class=\"tdsmallbold\" align=\"right\">É uma parcela?&nbsp;<img height=12 src=\"icons/icon_question.gif\"";
$help = "Informações aqui ajudam a usar esses valores para calcular a posição das árvores dentro das parcelas e produzir mapas com a distribuição das plantas"; 
		echo " onclick=\"javascript:alert('".$help."');\"></td>
  <td>
    <table class=\"tdformnotes\">
      <tr >
        <td align=\"right\">Dimensão X (m):</td>
        <td><input size=6 type=\"text\" value=\"".$dimx."\" name=\"dimx\"></td>
        <td>&nbsp;&nbsp;&nbsp;</td>
        <td align=\"right\">Posição&nbsp;X&nbsp;(m)&nbsp;<img height=12 src=\"icons/icon_question.gif\"";
$help = "Quando for uma subparcela de outra parcela, indicar aqui a coordenada X da posição da subparcela na parcela"; 
		echo " onclick=\"javascript:alert('".$help."');\"></td>
        <td><input size=6 type=\"text\" value=\"".$startx."\" name=\"startx\"></td>
        <td >&nbsp;&nbsp;&nbsp;&nbsp;</td>
        <td align=\"right\">Diâmetro (m, se circular):</td>
        <td><input size=6 type=\"text\" value=\"".$dimrad."\" name=\"dimrad\"></td>
      </tr>
      <tr>
        <td align=\"right\">Dimensão Y (m):</td>
        <td><input size=6 type=\"text\" value=\"".$dimy."\" name=\"dimy\"></td>
        <td>&nbsp;&nbsp;&nbsp;</td>
        <td align=\"right\">Posição&nbsp;Y&nbsp;(m)&nbsp;<img height=12 src=\"icons/icon_question.gif\"";
$help = "Quando for uma subparcela de outra parcela, indicar aqui a coordenada Y da posição da subparcela na parcela"; 
		echo " onclick=\"javascript:alert('".$help."');\"></td>
        <td><input size=6 type=\"text\" value=\"".$starty."\" name=\"starty\"></td>
        <td colspan=3>&nbsp;</td>
      </tr>
      <tr>
        <td align=\"right\">Imagem&nbsp;de&nbsp;fundo&nbsp;<img height=12 src=\"icons/icon_question.gif\"";
$help = "Imagems de fundo para plotar árvores na parcela. Um layer topográfico, por exemplo. Deve estar no formato e orientação exata (X,Y) e com tamanho relativo ao da parcela, sem margens em branco, titulos ou legendas. Pode subir quantos layers quiser"; 
		echo " onclick=\"javascript:alert('".$help."');\"></td>
        <td colspan=7 align=\"left\">
          <table>";
//echopre($plotimages);
if (count($plotimages)>0) {          
foreach ($plotimages as $vv) {
	if ($vv>0) {
		$qq = "SELECT * FROM Imagens WHERE ImageID='".$vv."'";
		$rt = mysql_query($qq,$conn);
		$rtw = mysql_fetch_assoc($rt);
		//diretorios das imagens
		$pthumb = 'img/thumbnails/';
		$imgbres = 'img/lowres/';
		$pathorg = 'img/originais/';
		$path = 'img/copias_baixa_resolucao/';
		$imagid = $rtw['ImageID'];
		$filename = trim($rtw['FileName']);
		$fotodata = $rtw['DateOriginal'];
		if (file_exists($pathorg.$filename)) {
			$fn = explode("_",$filename);
			unset($fn[0]);
			unset($fn[1]);
			$fn = implode("_",$fn);
			$fntxt = $filename;
			if ($fotodata!='0000-00-00') {
				$fntxt = $fntxt."   [".$fotodata."]";
			} 
			
			echo "
    <tr class=\"cl\">
      <td class=\"cl\">
        <table class=\"clean\">
          <tr class=\"cl\" >
            <td class=\"cl\" >
             <a href=\"".$imgbres.$filename."\" class=\"MagicZoomPlus\"  rel=\"zoom-position:right;zoom-height:200px; zoom-fade:true; smoothing-speed:17;opacity-reverse:true;\" >
              <img width=\"40\" src=\"".$pthumb.$filename."\"/></a></td>
            <td class=\"cl\" >&nbsp;</td>
            <td class=\"tinny\" id=\"fname_".$vv."\"  class=\"tdformnotes\">$fntxt</td>";
			$fndeleted = "<STRIKE>$fntxt</STRIKE>";
			echo "
              <input type=\"hidden\" id=\"fnamedeleted_".$vv."\" value=\"".$fndeleted."\">
              <input type=\"hidden\" id=\"imgtodel_".$vv."\" name=\"imgtodel_".$vv."\" value=\"\">
              <input type=\"hidden\" id=\"imagid_".$vv."\" name=\"imagid_".$vv."\" value=\"".$imagid."\">
              <input type=\"hidden\" id=\"fnameundeleted".$vv."\" value=\"".$fntxt."\">
            <td class=\"cl\" ><img height=14 src=\"icons/application-exit.png\" onclick=\"javascript:deletimage('fnamedeleted_".$vv."','fname_".$vv."','imgtodel_".$vv."',1);\"></td>
            <td class=\"cl\" ><img height=14 src=\"icons/list-add.png\" onclick=\"javascript:deletimage('fnameundeleted".$vv."','fname_".$vv."','imgtodel_".$vv."',0);\"></td>
          </tr>
        </table>
      </td>
    </tr>";
		}
		}
}
}	
		$varname = 'pltimg';
echo "
            <tr>
              <td>
                <input type=\"file\"  name=\"$varname\">
                <script type=\"text/javascript\">
                  window.addEvent('domready', function(){ new MultiUpload($( 'varform2' ).$varname);});
                </script>
              </td>
            </tr>
          </table>
        </td>
      </tr>
    </table>
  </td>
</tr>";
if ($bgi % 2 == 0){$bgcolor = $linecolor2 ;}  else{$bgcolor = $linecolor1 ;} $bgi++;
echo "
<tr bgcolor =\"".$bgcolor."\">
  <td colspan=\"100%\" align=\"center\">
    <table>
      <tr>
        <input type=\"hidden\" id=\"enviado\" name=\"enviado\" value=\"\">
        <td align=\"center\" ><input type=submit value=\"".GetLangVar('namesalvar')."\" class=\"bsubmit\" onclick=\"javascript:document.getElementById('enviado').value=1\"></td>
      </tr>
    </table>
  </td>
</tr>
<tbody>
</table>
</form>";
} 

} 
//HTMLtrailers();
PopupTrailers();

?>