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
	if (($nz==0 || empty($em) || empty($nome)) & $type=='completo') {
		echo "Campos obrigatórios faltando";
		unset($enviado);
	} 
	if ((empty($em) || empty($nome)) & $type=='free') {
			echo "Campos obrigatórios faltando";
			unset($enviado);
	}
}


$export_filename = "dadosParcela_".$idd.$tableref.".csv";
$export_filename_metadados = "dadosParcela_".$idd.$tableref."_metadados.txt";
$export_filename_public = "dadosParcela_".$idd.$tableref."_public.csv";
$export_filename_censos = "dadosParcela_".$idd.$tableref."_censos.csv";
$export_filename_censospub = "dadosParcela_".$idd.$tableref."_censospub.csv";

if($type=='free') {
	$censosfile = $export_filename_censospub;
} else {
	$censosfile = $export_filename_censos;
}

//$qwhere = " WHERE isvalidlocalandsub(pltb.GazetteerID, pltb.GPSPointID, ".$idd.", '".$tableref."')>0 AND moni.CensoID >0";
$gazetteerid = $idd;
if (!isset($enviado)) {
	//dados local
	$sql= "SELECT CONCAT(gaz.PathName,' [',Municipio,'- ',Province,' - ',Country,']') as nome, gaz.GazetteerID as nomeid FROM Gazetteer as gaz JOIN Municipio  USING(MunicipioID) JOIN Province USING(ProvinceID) JOIN Country USING(CountryID) WHERE GazetteerID=".$gazetteerid;
	$rz = @mysql_query($sql,$conn);
	$row = mysql_fetch_assoc($rz);
	$local = $row['nome'];
	
		//CONTA O NUMERO DE CENSOS
	$whichcensos = array();
	if (file_exists("temp/".$censosfile)) {
				$fop = @fopen("temp/".$censosfile, 'r');
				$i=0;
				while (($data = fgetcsv($fop, 10000, "\t")) !== FALSE) {
						$whichcensos[$data[0]] = 1;
				}
	}
	//$qz = "SELECT DISTINCT moni.CensoID FROM Monitoramento as moni JOIN Plantas AS pltb ON moni.PlantaID=pltb.PlantaID  ".$qwhere;

   //$rz = @mysql_query($qz,$conn);
   $ncensos = count($whichcensos);
   if ($type=='free') {
		$titulo = 'Baixando dados de acesso abero';
		$listacensos = 'Os dados incluem os seguintes censos:';
		$emailmsg = 'Um email será enviado à este endereço com os links dos dados de acesso aberto';
   } else {
		$titulo = 'Solicitando dados';
		$listacensos = "Dos ".$ncensos." censos, me interessam*";
		$emailmsg = 'Um email será enviado à este endereço e aos responsáveis pelos dados indicando interesse';
   }
	if ($ncensos>0) {
//PEGA OS EMAILS DOS RESPONSAVEIS PELOS CENSOS
echo "
<form method='post' action='export-plotdata-request.php'>
  <input type='hidden'  name='gazetteerid' value='".$gazetteerid."'/>
  <input type='hidden'  name='idd' value='".$idd."'/>
  <input type='hidden'  name='tableref' value='".$tableref."'/>
  <input type='hidden'  name='type' value='".$type."'/>
  <input type='hidden'  name='enviado' value='1'/>
  <table class='myformtable' align='center' cellpadding='7' >
  <thead><tr><td colspan='2'>".$titulo."</td></tr>
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
      <td class='tdformright'>".$listacensos."</td>
      <td class='tdformnotes'>
        <table>
           <tr><td>Censo</td><td>Data</td><td>Responsável</td></tr>";
		foreach($whichcensos as $censoid => $vv) {
			$sql = "SELECT * FROM Censos LEFT JOIN Users ON ResponsavelID=UserID WHERE CensoID=".$censoid;
			$rzz = mysql_query($sql,$conn);
			$rww = mysql_fetch_assoc($rzz);

			$cnome =  $rww['CensoNome'];
			$sql = "SELECT MIN(DataObs) AS minDATA, MAX(DataObs) AS maxDATA FROM Monitoramento WHERE CensoID=".$censoid;
			$rzz2 = mysql_query($sql,$conn);
			$rww2 = mysql_fetch_assoc($rzz2);
			$cen = $whichcensos[$censoid];
			if (($cen+0)==1) {
				$txt = 'checked';
			} else {
				$txt = '';
			}
			if ($type!='free') {
					$cnome = "<input type='checkbox' name='whichcensos[".$rww['CensoID']."]' ".$txt." value='1'>&nbsp;".$cnome;
			} else {
					$cnome = "<input type='hidden' name='whichcensos[".$rww['CensoID']."]'  value='whichcensos[".$rww['CensoID']."]'>&nbsp;".$cnome;
			}
			echo "
          <tr><td>".$cnome."</td><td>".$rww2['minDATA']."&nbsp;a&nbsp;".$rww2['maxDATA']."</td><td>".$rww['FirstName']." ".$rww['LastName']."&nbsp;[".$rww['Email']."]</td></tr>";
		}
echo "
     </table>
    </tr>
";
 if ($bgi % 2 == 0){$bgcolor = $linecolor2 ;}  else {$bgcolor = $linecolor1 ;} $bgi++;
echo "
<tr bgcolor = '".$bgcolor."'>
      <td class='tdformright'>Qual o seu email*</td>
      <td class='tdformnotes'><input name='email' type='text' style='height: 15px; width: 250px;'  /><br>".$emailmsg."</td>
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
			$cnome =  $rww['CensoNome'];
			$sql = "SELECT MIN(DataObs) AS minDATA, MAX(DataObs) AS maxDATA FROM Monitoramento WHERE CensoID=".$censoid;
			//echo $sql."<br />";
			$rzz2 = mysql_query($sql,$conn);
			$rww2 = mysql_fetch_assoc($rzz2);
			$cennome[] = $cnome."  [".$rww2['minDATA']." a ".$rww2['DataObs']."]";
}
$osmails = array_unique($osmails);
$emaildestinatario = implode(",",$osmails);

$url = $_SERVER['HTTP_REFERER'];
$uu = explode("/",$url);
$nu = count($uu)-1;
unset($uu[$nu]);
$url = implode("/",$uu);
if ($type=='free') {
	$assunto = 'DADOS DE CENSOS EM '.$gazetteer.'  BAIXADOS POR '.$nome;
	$solic = "Os dados solicitados podem ser acessados no link<br />".$url."/temp/".$export_filename;
	$solic .= "<br />Metadados no link<br /> ".$url."/temp/".$export_filename_metadados;
	$solic .= "<br /><br />Os dados dos seguintes censos estão incluídos no arquivo <br >".implode("<br >",$cennome).".<br ><br />";
} else {
	$assunto = $nome.' SOLICITA DADOS DE CENSOS EM '.$gazetteer;
	$solic = "Solicito permissão para usar os dados de plantas marcadas da localidade ".$gazetteer.", especificamente os dados dos seguintes censos: <br >".implode("<br >",$cennome).".<br ><br />";
}
$pis = array_unique($pis);
$mensagemHTML = "Prezado(s) ".implode(",", $pis).":<br ><br >".$solic;
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
	echo "<div align='center' style='font-size: 1.5em;' >O email foi enviado com SUCESSO!<div>";
} else {
	echo "Houve um erro! Não foi possível enviar sua mensagem!";
}

}
$which_java = array("<script type='text/javascript' src='javascript/myjavascripts.js'></script>");
FazFooter($which_java,$calendar=FALSE,$footer=$menu);
?>