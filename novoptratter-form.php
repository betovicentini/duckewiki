<?php
//Start session
session_start();
//Check whether the session variable
include "functions/HeaderFooter.php";
include "functions/SelectOptions.php";

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
@extract($ppost);

$gget = cleangetpost($_GET,$conn);
@extract($gget);



HTMLheaders($body);


if (!isset($novo) && !isset($expeditoid)) {
echo "
<br>
<table class='myformtable' align='center' cellpadding='7' >
<thead>
<tr ><td colspan='100%'>Editar ou criar novo ponto método expedito</td></tr>
</thead>
<tbody>
<form action=novoptratter-form.php method='post'>";
echo "
<tr>
  <td  colspan='100%'>
    <select name='expeditoid' onchange='this.form.submit()'>
      <option value=''>".GetLangVar('nameselect')."</option>
      <option value=''>------------</option>
      <option value='criar'>Criar um novo ponto</option>
      <option value=''>------------</option>";

	$qq = "SELECT exp.ExpeditoID, exp.DataColeta, IF(gps.Name<>'' OR gps.Name IS NOT NULL,CONCAT('Ponto ',gps.Name,' [',gaz.PathName,' ',exp.DataColeta,']'), '') as optnome FROM MetodoExpedito as exp LEFT JOIN GPS_DATA as gps ON exp.GPSpointID=gps.PointID  LEFT JOIN Gazetteer AS gaz ON gps.GazetteerID=gaz.GazetteerID ORDER BY gaz.PathName,exp.DataColeta,gps.Name";
	$rrr = mysql_query($qq,$conn);
		while ($row = mysql_fetch_assoc($rrr)) {
			echo "
      <option value=".$row['ExpeditoID'].">".$row['DataColeta']." - ".$row['optnome']."</option>";
		}
	echo "
    </select>
    </td>
</tr>
</tbody>
</table>
</form>	";

	
} else {
if ($expeditoid=='criar') {
	$tt = GetLangVar('novopontoratter');
	unset($expeditoid);
} else {
	$tt = "Editando ponto método expedito";
	if (!isset($final)) {
		$qq = "SELECT * FROM MetodoExpedito WHERE ExpeditoID=".$expeditoid;
		$re = mysql_query($qq,$conn);
		if ($re) {
			$rwe = mysql_fetch_assoc($re);
			$datacol = $rwe['DataColeta'];
			$pessoasids = $rwe['PessoasIDs'];
			$gpsunitnames = $rwe['GPSunitNames'];
			$pessoarr = explode(";",$pessoasids);
			$gpsunitarr = explode(";",$gpsunitnames);
			$tempo_ini = $rwe['Time_Starts'];
			$tempo_fim = $rwe['Time_Ends'];
			$gpspointid = $rwe['GPSpointID'];
			$qq = "SELECT habitatstring(HabitatID,5,FALSE,FALSE) as habtxt FROM Habitat WHERE GPSPointID=".$gpspointid;
			$riq = mysql_query($qq,$conn);
			$nriq = mysql_numrows($riq);
			//echopre($rwe);
			if ($nriq>0) { 
				$riw = mysql_fetch_assoc($riq);
				$habitattxt = $riw['habtxt'];
			}
		}
	}
}

if (!isset($final) || $final>=2) {
	if (isset($gpspointid) && $gpspointid>0) {
		$qq = "SELECT CONCAT('GPSpt-',Name,' --',gaz.PathName,' ',Municipio,' ',Province,' ',Country) as nome FROM GPS_DATA JOIN Gazetteer as gaz USING(GazetteerID) JOIN Municipio  USING(MunicipioID) JOIN Province USING(ProvinceID) JOIN Country USING(CountryID) WHERE GPS_DATA.PointID='".$gpspointid."'";
		$riq = mysql_query($qq,$conn);
		$riw = mysql_fetch_assoc($riq);
		$gpspt = $riw['nome'];
		$locality = getGPSlocality($gpspointid,$name=FALSE,$conn);
	}

	if ($final==2) {
		$pessoarr = $pessoasids;
		$gpsunitarr = $gpsunitnames;
		$npess = count($pessoasids)+2;
	} elseif (!isset($npess)) { $npess=3; }
	
	if ($final==3) {
		$pessoarr = unserialize($pessoasids);
		$gpsunitarr = unserialize($gpsunitnames);
		$npess = count($pessoarr);
	} 
	
$bgi=1;
echo "<br>
<table class='myformtable' cellpadding='5' align='center' >
<thead>
  <tr><td colspan=100%>$tt</td></tr>
</thead>
<tbody>
  <form name='coletaform' action=novoptratter-form.php method='post'>
  <input type='hidden' name='expeditoid'  value='".$expeditoid."'>
  <input type='hidden' name='npess'  value='".$npess."'>
  ";
  if ($bgi % 2 == 0){$bgcolor = $linecolor2 ;} else{$bgcolor = $linecolor1 ;} $bgi++;
  echo "
  <tr bgcolor = $bgcolor>
    <td colspan='100%'>
      <table cellpadding='3'>
        <tr>
          <td class='tdformright'>".GetLangVar('namedata')."</td>
          <td>
            <input name=\"datacol\" value=\"$datacol\" size=\"11\" readonly >
            <a onclick=\"if(self.gfPop)gfPop.fPopCalendar(document.forms['coletaform'].datacol);return false;\" ><img name=\"popcal\" align=\"absmiddle\" src=\"calendar/calbtn.gif\" width=\"34\" height=\"22\" border=\"0\" alt=\"\"></a>
          </td>
        </tr>
      </table>
    </td>
  </tr>";

//dados de localidade
if ($bgi % 2 == 0){$bgcolor = $linecolor2 ;} else{$bgcolor = $linecolor1 ;} $bgi++;
echo "
<tr bgcolor = $bgcolor>
  <td colspan='100%'>
    <table>
      <tr>
        <td class='tdformright' align='center'>Ponto GPS</td>
        <td>
          <table>
            <tr><td class='tdformnotes'>$locality</td></tr>
            <tr><td class='tdformnotes'>"; autosuggestfieldval3('search-gpspoint.php','gpspt',$gpspt,'gpsres','gpspointid',$gpspointid,true,60); 
        echo "&nbsp;*selecione da lista</td></tr>
          </table>
        </td>
      </tr>
    </table>
  </td>
</tr>";

if (!empty($habitattxt)) {
//dados de localidade
if ($bgi % 2 == 0){$bgcolor = $linecolor2 ;} else{$bgcolor = $linecolor1 ;} $bgi++;
echo "
  <input type='hidden' name='habitattxt'  value='".$habitattxt."'>
<tr bgcolor = $bgcolor>
  <td colspan='100%'>
    <table>
      <tr>
        <td class='tdformright' align='center'>Habitat</td>
        <td class='tdformnotes'>$habitattxt</td>
      </tr>
    </table>
  </td>
</tr>";


}

if ($bgi % 2 == 0){$bgcolor = $linecolor2 ;}  
	else{$bgcolor = $linecolor1 ;}
	$bgi++;
	echo "
<tr bgcolor = $bgcolor>
  <td colspan='100%'>
    <table>
      <tr>
        <td class='tdformright'>".GetLangVar('namepessoa')."s</td>
        <td>
          <table>
            <tr class='tdsmallboldright'>
              <td align='center'>".GetLangVar('namenome')."</td>
              <td align='center'>GPS usado para tracking</td>";
              if ($expeditoid>0) {
              echo "<td>Registros de GPS encontrados</td>";
              }
            echo "
            </tr>";
	
for ($z=0;$z<=$npess;$z++) {
	$ppid = $pessoarr[$z]+0;
	$gpsun = $gpsunitarr[$z]+0;
	
	echo "
            <tr>
             <td >
               <select name='pessoasids[]'>";
	echo "
                 <option value=''>".GetLangVar('nameselect')."</option>";
        if ($ppid>0) {
	        $rrr = getpessoa($ppid,$abb=TRUE,$conn);
			$pdrw = mysql_fetch_assoc($rrr);
			$pdn = $pdrw['Abreviacao'];
	echo "
                 <option selected value='".$ppid."'>$pdn</option>";
        
        }
		$rrr = getpessoa('',$abb=TRUE,$conn);
		while ($row = mysql_fetch_assoc($rrr)) {
			echo "
                 <option value=".$row['PessoaID'].">".$row['Abreviacao']."</option>";
		}
	echo "
               </select>
              </td>
              <td>
                <select name='gpsunitnames[]' >
                  <option value=''>".GetLangVar('nameselect')."</option>";
     if ($gpsun>0) {
	       	$qq = "SELECT * FROM Equipamentos WHERE EquipamentoID=".$gpsun;
			$rqs = mysql_query($qq,$conn);
			$rsw = mysql_fetch_assoc($rqs);
echo "
                  <option selected value='".$rsw['EquipamentoID']."' >".$rsw['Name']."</option>";      
        }             
                  
                  
	$qq = "SELECT * FROM Equipamentos WHERE Type='gps' ORDER BY Name ASC";
	$res = mysql_query($qq,$conn);
	while ($row =  mysql_fetch_assoc($res)) {
		echo "
                  <option value='".$row['EquipamentoID']."' >".$row['Name']."</option>";
	}
	echo "
                </select>
              </td>";
              if ($expeditoid>0) {
	              $qq = "SELECT count(*) as ntracks FROM GPS_DATA WHERE GPSName=".$gpsun." AND ExpeditoID='".$expeditoid."' AND Type='Trackpoint'";
				  $rqes = mysql_query($qq,$conn);
				  $rwes = mysql_fetch_assoc($rqes);
	              $ntracks = $rwes['ntracks'];
	              $qq = "SELECT count(*) as npts FROM GPS_DATA WHERE GPSName=".$gpsun." AND ExpeditoID='".$expeditoid."' AND Type='Waypoint'";
				  $rqes = mysql_query($qq,$conn);
				  $rwes = mysql_fetch_assoc($rqes);
	              $npts = $rwes['npts'];
		              echo "<td class='tdformnotes'>Trackpoints: $ntracks & Waypoints: $npts</td>";
	        	} 
            echo "
            </tr>";
}
echo "
            <tr>
             <td align='left'>
				<input type='submit' value='Adicionar pessoas' class='bblue' onclick=\"javascript:document.coletaform.final.value=2\">
			</td>
			</tr>
		  </table>
        </td>
      </tr>
    </table>
  </td>
</tr>";



if (empty($tempo_ini)) { $tempo_ini = "HH:MM:SS";}
if (empty($tempo_fim)) { $tempo_fim = "HH:MM:SS";}

if ($bgi % 2 == 0){$bgcolor = $linecolor2 ;}  
	else{$bgcolor = $linecolor1 ;}
	$bgi++;
	echo "
<tr bgcolor = $bgcolor>
  <td colspan='100%'>
    <table>
      <tr>
        <td class='tdformright'>Horarios</td>
        <td>
          <table>
            <tr class='tdsmallbold'><td>&nbsp;</td><td >Inicio</td><td>Fim</td></tr>
            <tr><td>&nbsp;</td><td><input type='text' name='tempo_ini' value='".$tempo_ini."'></td><td><input type='text' name='tempo_fim' value='".$tempo_fim."'>&nbsp;<b>Formato</b>: <i>Hora:Minuto:Segundo</i>, e.g. 9:30:45</td></tr>
          </table>
        </td>
      </tr>
    </table>
  </td>
</tr>";


if ($bgi % 2 == 0){$bgcolor = $linecolor2 ;}  
	else{$bgcolor = $linecolor1 ;}
	$bgi++;
	echo "
<tr bgcolor = $bgcolor>
  <td colspan=100%>
    <table align='center' >
      <tr>
        <input type='hidden' name='final' value=''>
        <td align='center' ><input type='submit' value='".GetLangVar('namesalvar')."' class='bsubmit' onclick=\"javascript:document.coletaform.final.value=1\"> </td>
      </tr>
    </table>
  </td>
</tr>
</form>
</tbody>
</table>
";

} elseif ($final==1) {

echo "
<form name='myform' action='novoptratter-exec.php' method='post'>
  <input type='hidden' name='expeditoid'  value='".$expeditoid."'>
  <input type='hidden' name='pessoasids'  value='".serialize($pessoasids)."'>
  <input type='hidden' name='gpsunitnames'  value='".serialize($gpsunitnames)."'>
  <input type='hidden' name='gpspointid'  value='".$gpspointid."'>
  <input type='hidden' name='tempo_ini'  value='".$tempo_ini."'>
  <input type='hidden' name='tempo_fim'  value='".$tempo_fim."'>
  <input type='hidden' name='datacol'  value='".$datacol."'>
  <script language=\"JavaScript\">setTimeout('document.myform.submit()',0.0001);</script>
</form>";


}

}

HTMLtrailers();
	

?>
