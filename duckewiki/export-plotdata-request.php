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

//////PEGA E LIMPA VARIAVEIS
$ppost = cleangetpost($_POST,$conn);
@extract($ppost);
$arval = $ppost;

$gget = cleangetpost($_GET,$conn);
@extract($gget);

//CABECALHO
$menu = FALSE;
$which_css = array("<link href='css/geral.css' rel='stylesheet' type='text/css' />");
$which_java = array();
$body='';
$title = 'Solicita dados de parcela';
FazHeader($title,$body,$which_css,$which_java,$menu);

if (isset($enviado)) {
	$nz = @count($whichcensos);
	$em = trim($email);
	$nome = trim($nome);
	if ($nz==0 || empty($em) || empty($nome)) {
		echo "Campos obrigatórios faltando";
		unset($enviado);
	} 
}

if (!isset($enviado)) {
	//dados local
	$sql= "SELECT CONCAT(gaz.PathName,' [',Municipio,'- ',Province,' - ',Country,']') as nome, gaz.GazetteerID as nomeid FROM Gazetteer as gaz JOIN Municipio  USING(MunicipioID) JOIN Province USING(ProvinceID) JOIN Country USING(CountryID) WHERE GazetteerID='".$gazetteerid."'";
	$rz = @mysql_query($sql,$conn);
   $row = mysql_fetch_assoc($rz);
	$local = $row['nome'];
	
		//CONTA O NUMERO DE CENSOS
	$qz = "SELECT DISTINCT moni.CensoID FROM Monitoramento as moni JOIN Plantas AS pl ON moni.PlantaID=pl.PlantaID LEFT JOIN Gazetteer as gaz ON pl.GazetteerID=gaz.GazetteerID LEFT JOIN GPS_DATA as gps ON pl.GPSPointID=gps.PointID WHERE 
(pl.GazetteerID='".$gazetteerid."' OR gps.GazetteerID='".$gazetteerid."' OR gaz.ParentID='".$gazetteerid."') AND
moni.CensoID>0 AND moni.TraitID='".$daptraitid."'";
   $rz = @mysql_query($qz,$conn);
   $ncensos = @mysql_numrows($rz);
	if ($ncensos>0) {
	
//PEGA OS EMAILS DOS RESPONSAVEIS PELOS CENSOS
echo "
<form method='post' action='export-plotdata-request.php'>
  <input type='hidden'  name='gazetteerid' value='".$gazetteerid."'/>
  <input type='hidden'  name='enviado' value='1'/>
  <table class='myformtable' align='center' cellpadding='7' width='60%'>
  <thead><tr><td colspan='2'>Solicitando dados</td></tr>
  <tbody>";
  
 if ($bgi % 2 == 0){$bgcolor = $linecolor2 ;}  else {$bgcolor = $linecolor1 ;} $bgi++;
echo "
<tr bgcolor = '".$bgcolor."'>
      <td class='tdformright'>Localidade:</td>
      <td class='tdformnotes'>
        <input type='hidden'  name='gazetteer' value='".$local."'/>
      ".$local."</td>
    </tr>
";
  
 if ($bgi % 2 == 0){$bgcolor = $linecolor2 ;}  else {$bgcolor = $linecolor1 ;} $bgi++;
echo "
<tr bgcolor = '".$bgcolor."'>
      <td class='tdformright'>Dos ".$ncensos." censos, me interessam*</td>
      <td class='tdformnotes'>
        <table>
           <tr><td>Incluir censo</td><td>Data</td><td>Responsavel</td></tr>";
		while ($row = mysql_fetch_assoc($rz)) {
			$censoid = $row['CensoID'];
			$sql = "SELECT * FROM Censos LEFT JOIN Users ON ResponsavelID=UserID WHERE CensoID=".$censoid;
			$rzz = mysql_query($sql,$conn);
			$rww = mysql_fetch_assoc($rzz);
			
			$cen = $whichcensos[$rww['CensoID']];
			if (($cen+0)==1) {
				$txt = 'checked';
			} else {
				$txt = '';
			}
			echo "
          <tr><td><input type='checkbox' name='whichcensos[".$rww['CensoID']."]' value='1'>&nbsp;".$rww['CensoNome']."</td><td>".$rww['DataInicio']." a ".$rww['DataFim']."</td><td>".$rww['FirstName']." ".$rww['LastName']."  [".$rww['Email']."]</td></tr>";
		}
echo "
     </table>
    </tr>
";
  
 if ($bgi % 2 == 0){$bgcolor = $linecolor2 ;}  else {$bgcolor = $linecolor1 ;} $bgi++;
echo "
<tr bgcolor = '".$bgcolor."'>
      <td class='tdformright'>Qual o seu email*</td>
      <td class='tdformnotes'><input name='email' type='text' style='height: 15px; width: 250px;'  /></td>
    </tr>
";
  
 if ($bgi % 2 == 0){$bgcolor = $linecolor2 ;}  else {$bgcolor = $linecolor1 ;} $bgi++;
echo "
<tr bgcolor = '".$bgcolor."'>
      <td class='tdformright'>Seu nome completo*</td>
      <td class='tdformnotes'><input name='nome' type='text' style='height: 15px; width: 250px;'  /></td>
    </tr>
";
  
 if ($bgi % 2 == 0){$bgcolor = $linecolor2 ;}  else {$bgcolor = $linecolor1 ;} $bgi++;
echo "
<tr bgcolor = '".$bgcolor."'>
      <td class='tdformright'>Seu instituição*</td>
      <td class='tdformnotes'><input name='instituicao' type='text' style='height: 15px; width: 250px;' /></td>
    </tr>
";
  
 if ($bgi % 2 == 0){$bgcolor = $linecolor2 ;}  else {$bgcolor = $linecolor1 ;} $bgi++;
echo "
<tr bgcolor = '".$bgcolor."'>
      <td class='tdformright'>O que pretende fazer com os dados?*</td>
      <td class='tdformnotes'><textarea name='razao' cols=50 rows=8></textarea></td>
    </tr>
";
  
 if ($bgi % 2 == 0){$bgcolor = $linecolor2 ;}  else {$bgcolor = $linecolor1 ;} $bgi++;
echo "
<tr bgcolor = '".$bgcolor."'>
      <td colspan='2' align='center'><input style='cursor: pointer' type='submit' class='bsubmit' value='Enviar' /></td>
    </tr>
  </tbody>
  </table>
</form>";
			//echopre($sql);
		} 
		else {
			echo "Não há dados definidos para exportar";
		}
} 
else {

$osmails = array();
$pis = array();
$cennome = array();
foreach($whichcensos as $censoid => $vv) {
			$sql = "SELECT * FROM Censos LEFT JOIN Users ON ResponsavelID=UserID WHERE CensoID=".$censoid;
			$rzz = mysql_query($sql,$conn);
			$rww = mysql_fetch_assoc($rzz);
			$osmails[] = $rww['Email'];
			$pis[] = $rww['FirstName'];
			$cennome[] = $rww['CensoNome']."  [".$rww['DataInicio']." a ".$rww['DataFim']."]";
}
$emaildestinatario = implode(",",$osmails);
$assunto = 'SOLICITAÇÃO DE DADOS DE PLANTAS MARCADAS EM '.$gazetteer;
$mensagemHTML = "Prezado(s) ".implode(",", $pis).":
<br >
Solicito permissão para usar os dados de plantas marcadas da localidade ".$gazetteer.", especificamente os dados dos seguintes censos:
".implode("<br >",$cennome).".<br >";
$mensagemHTML .= "Esses dados serão usados para: ".$razao."<br ><br >";
$mensagemHTML .= "Obrigado(a)!<br ><br >".$nome."<br>".$instituicao;
$emailsender = $_SERVER['SERVER_ADMIN'];
$headers = "MIME-Version: 1.1\n";
$headers .= "Content-type: text/html; charset=utf-8\n";
$headers .= "X-Priority: 1\n";
$headers .= "From: ".$emailsender."\n";
$headers .= "Cc: ".$emaildestinatario.",".$email."\n";
$headers .= "Reply-To: ".$email."\n";
//'alberto.vicentini@botanicaamazonica.wiki.br';
$send = mail($emaildestinatario, $assunto, $mensagemHTML, $headers ,"-r".$emailsender);
if(!$send){ // Se for Postfix
    $headers .= "Return-Path: " . $emailsender . $quebra_linha; // Se "não for Postfix"
    $send = mail($emaildestinatario, $assunto, $mensagemHTML, $headers );    
}
if ($send) {
	echo "O email foi enviado com SUCESSO!";
} else {
	echo "Houve um erro! Não foi possível enviar sua mensagem!";
}

}
$which_java = array("<script type='text/javascript' src='javascript/myjavascripts.js'></script>");
FazFooter($which_java,$calendar=FALSE,$footer=$menu);
?>