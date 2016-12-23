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

//echopre($ppost);
//CABECALHO
$ispopup=1;
$menu = FALSE;
$which_css = array(
"<link href='css/geral.css' rel='stylesheet' type='text/css' />",
"<link rel='stylesheet' type='text/css' href='css/colorbuttons.css' />",
"<link rel='stylesheet' type='text/css' media='screen' href='css/autosuggest.css' />"
);
$which_java = array(
"<script type='text/javascript' src='javascript/ajax_framework.js'></script>"
);
$title = 'Configurações';
$body = '';
FazHeader($title,$body,$which_css,$which_java,$menu);

if (!isset($final)) {
$numvars = array('habitotraitid','pomtraitid', 'statustraitid', 'traitfertid', 'alturatraitid', 'daptraitid', 'duplicatesTraitID', 'exsicatatrait','traitsilica','localidadetraitid','folhaimgtraitid','florimgtraitid','frutoimgtraitid');
$vartxt = array('habtrname','pomtrname', 'statustrname', 'traitferttrname', 'alttrname', 'daptrname', 'duplicatestrname', 'exsicatatrname','traitsilicaname','localidadestrname','folhaimgtrname','florimgtrname','frutoimgtrname');
//,'introtext'
//get traitnames
foreach ($numvars as $kk => $tt) {
		$tid = $$tt;
		$vv = $vartxt[$kk];
		if ($tid>0) {
		$qu = "SELECT * FROM Traits WHERE TraitID='".$tid."'";
		$ru = mysql_query($qu,$conn);
			$rwu = mysql_fetch_assoc($ru);
			$tgn = $rwu['TraitName']."  (".$rwu['PathName'].")";
			$$vv = $tgn;
		} else {
			$$vv = 'Selecione uma opção';
		}
}

echo "
<form enctype='multipart/form-data' action='config_db.php' method='post' name='finalform' >
<input type='hidden' name='ispopup' value='".$ispopup."' />
<input type='hidden' name='final' value='1' /> 
<table class='myformtable' align='left' cellpadding=\"4\">
<thead>
<tr >
<td colspan='2'>Configurações da base</td></tr>
</thead>
<tbody>
";
if ($blockacess>0) {
	$txc = 'checked';
} else {
	$txc = '';
}
if ($bgi % 2 == 0){$bgcolor = $linecolor2 ;} else {$bgcolor = $linecolor1 ;} $bgi++;
echo "
<tr style=\"background-color: ".$bgcolor."\">
  <td align='right' style='font-size: 0.9em; font-weight: bold; color: darkred'>Bloqueia o acesso aos usuários (exceto admin)&nbsp;
  </td> 
  <td><input type='checkbox' name='blockacess' $txc value='1' />em caso de manutenção e atualização de tabelas!</td>
</tr>";
if ($bgi % 2 == 0){$bgcolor = $linecolor2 ;} else {$bgcolor = $linecolor1 ;} $bgi++;
echo "
<tr style=\"background-color: ".$bgcolor."\">
  <td align='right' style='font-size: 0.9em; font-weight: bold; color: darkred'>Banco de dados*
&nbsp;<img height='14' src=\"icons/icon_question.gif\" ";
	$help = 'Nome da base de dados MYSQL';
	echo " onclick=\"javascript:alert('$help');\" />
  </td>
  <td><input type='text' style='height: 2em; width: 400px;'  name='dbname' value='".$dbname."' /></td>
</tr>";
if ($bgi % 2 == 0){$bgcolor = $linecolor2 ;} else {$bgcolor = $linecolor1 ;} $bgi++;
echo "
<tr style=\"background-color: ".$bgcolor."\">
  <td align='right' style='font-size: 0.9em; font-weight: bold; color: darkred'>Arquivo com conexão MySQL*
  &nbsp;<img height='14' src=\"icons/icon_question.gif\" ";
	$help = 'Nome do subdiretório e nome de arquivo para conexão com banco de dados, onde está definido o servidor e a senha! A opção Caminho relativo complementa essa informação!';
	echo " onclick=\"javascript:alert('$help');\" /></td>
  <td><input type='text' style='height: 2em; width: 400px;'  name='databaseconnection' value='".$databaseconnection."' /></td>
</tr>";
if ($bgi % 2 == 0){$bgcolor = $linecolor2 ;} else {$bgcolor = $linecolor1 ;} $bgi++;
echo "
<tr style=\"background-color: ".$bgcolor."\">
  <td align='right' style='font-size: 0.9em; font-weight: bold; color: darkred'>Arquivo com conexão MySQL Simplificada*
  &nbsp;<img height='14' src=\"icons/icon_question.gif\" ";
	$help = 'Mesmo que o anterior, mas para o arquivo de conexão simplificado, necessário em alguns scripts!';
	echo " onclick=\"javascript:alert('$help');\" /></td>
  <td><input type='text' style='height: 2em; width: 400px;'  name='databaseconnection_clean' value='".$databaseconnection_clean."' /></td>
</tr>";
if ($bgi % 2 == 0){$bgcolor = $linecolor2 ;} else {$bgcolor = $linecolor1 ;} $bgi++;
echo "
<tr style=\"background-color: ".$bgcolor."\">
  <td align='right' style='font-size: 0.9em; font-weight: bold; color: darkred'>Caminho relativo dos arquivos de conexão MySQL*
  &nbsp;<img height='14' src=\"icons/icon_question.gif\" ";
	$help = 'Caminho relativo ao subdiretório com os arquivos de conexão';
	echo " onclick=\"javascript:alert('$help');\" /></td>
  <td><input type='text' style='height: 2em; width: 400px;' name='relativepathtoroot' value='".$relativepathtoroot."' /></td>
</tr>";
if ($bgi % 2 == 0){$bgcolor = $linecolor2 ;} else {$bgcolor = $linecolor1 ;} $bgi++;
echo "
<tr style=\"background-color: ".$bgcolor."\">
  <td align='right' style='font-size: 0.9em; font-weight: bold; color: darkred'>Título da base*
  &nbsp;<img height='14' src=\"icons/icon_question.gif\" ";
	$help = 'Titulo do site, que será colocado no HEADER de cada página HTML produzida pelos scripts, e aparecerá no topo da página';
	echo " onclick=\"javascript:alert('$help');\" /></td>
  <td><textarea name='metatitle' style='height: 4em; width: 400px;'>".$metatitle."</textarea></td>
</tr>";
if ($bgi % 2 == 0){$bgcolor = $linecolor2 ;} else {$bgcolor = $linecolor1 ;} $bgi++;
echo "
<tr style=\"background-color: ".$bgcolor."\">
  <td align='right' style='font-size: 0.9em; font-weight: bold; color: darkred'>Descrição metados*
   &nbsp;<img height='14' src=\"icons/icon_question.gif\" ";
	$help = 'Descrição do site, que será colocado no HEADER de cada página HTML produzida pelos scripts!';
	echo " onclick=\"javascript:alert('$help');\" /></td>
<td><textarea name='metadesc' style='height: 4em; width: 400px;'>".$metadesc."</textarea></td>
</tr>";
if ($bgi % 2 == 0){$bgcolor = $linecolor2 ;} else {$bgcolor = $linecolor1 ;} $bgi++;
echo "
<tr style=\"background-color: ".$bgcolor."\">
  <td align='right' style='font-size: 0.9em; font-weight: bold; color: darkred'>Keywords dos metados*
&nbsp;<img height='14' src=\"icons/icon_question.gif\" ";
	$help = 'Palavras chaves do site, que será colocado no HEADER de cada página HTML produzida pelos scripts!';
	echo " onclick=\"javascript:alert('$help');\" /></td>
  <td><textarea name='metakeyw' style='height: 4em; width: 400px;'>".$metakeyw."</textarea></td>
</tr>";
if ($bgi % 2 == 0){$bgcolor = $linecolor2 ;} else {$bgcolor = $linecolor1 ;} $bgi++;
echo "
<tr style=\"background-color: ".$bgcolor."\">
  <td align='right' style='font-size: 0.9em; font-weight: bold; color: darkred'>Nome da Instituição*
&nbsp;<img height='14' src=\"icons/icon_question.gif\" ";
	$help = 'Nome da sua Instituição, que será colocado no HEADER de cada página HTML produzida pelos scripts!';
	echo " onclick=\"javascript:alert('$help');\" /></td>
  <td><textarea name='metacompany' style='height: 4em; width: 400px;'>".$metacompany."</textarea></td>
</tr>";
if ($bgi % 2 == 0){$bgcolor = $linecolor2 ;} else {$bgcolor = $linecolor1 ;} $bgi++;
echo "
<tr style=\"background-color: ".$bgcolor."\">
  <td align='right' style='font-size: 0.9em; font-weight: bold; color: darkred'>Lingua Padrão*
&nbsp;<img height='14' src=\"icons/icon_question.gif\" ";
	$help = 'DESATUALIZADO. A mudanção não terá efeito. #stá aqui para lembrar que a página poderia ser rapidamente traduzida para inglês, español ou portugues, atualizando todos os scripts, incluindo titulos de campos, etc na tabela varlang (com traduções) e nas páginas pela função php GetLangVar();';
	echo " onclick=\"javascript:alert('$help');\" /></td>
  <td>
    <select name='lang' readonly>";
  if (isset($lang)) {
echo "
      <option style='height: 4em; width: 400px;' selected value='".$lang."' >".$lang."</option>";
  } else {
echo "
      <option style='height: 4em; width: 400px;' value='' >".GetLangVar("nameselect")."</option>";
  }
echo "  
      <option value='' >-------------</option>
      <option value='Portuguese' >Português</option>
      <option value='Spanish' >Español</option>
      <option value='English' >English</option>
    </select>
  </td>
</tr>";
if ($bgi % 2 == 0){$bgcolor = $linecolor2 ;} else {$bgcolor = $linecolor1 ;} $bgi++;
echo "
<tr style=\"background-color: ".$bgcolor."\">
  <td align='right' class='tdsmallbold'>Título para o site!&nbsp;<img height='14' src=\"icons/icon_question.gif\" ";
	$help = 'Titulo para o canto esquerdo superior da página do site';
	echo " onclick=\"javascript:alert('$help');\" /></td>  
  <td><textarea name='sitetitle' style='height: 4em; width: 400px;'>".$sitetitle."</textarea></td>
</tr>";
if ($bgi % 2 == 0){$bgcolor = $linecolor2 ;} else {$bgcolor = $linecolor1 ;} $bgi++;
echo "
<tr style=\"background-color: ".$bgcolor."\">
  <td align='right' class='tdsmallbold'>Logo para o site!&nbsp;<img height='14' src=\"icons/icon_question.gif\" ";
	$help = 'Imagem de logo para o canto esquerdo superior da página';
	echo " onclick=\"javascript:alert('$help');\" /></td>  
  <td>";
if (!empty($sitelogo)) {
echo "<img height='100' src=\"icons/".$sitelogo."\"  />";
}
echo "
      <input name='oldsitelogo' type='hidden'  value='".$sitelogo."' />
      <input name='sitelogo' type='file' width='20' />
  </td>
</tr>";

if ($bgi % 2 == 0){$bgcolor = $linecolor2 ;} else {$bgcolor = $linecolor1 ;} $bgi++;
echo "
<tr style=\"background-color: ".$bgcolor."\">
  <td align='right' class='tdsmallbold'>Texto Introdução<img height='14' src=\"icons/icon_question.gif\" ";
	$help = 'Texto de apresentação da base e que aparece na página inicial! Pode incluir HTML';
	echo " onclick=\"javascript:alert('$help');\" /></td>  
  <td><textarea name='introtext' style='height: 4em; width: 400px;'>".$introtext."</textarea></td>
</tr>";


if ($bgi % 2 == 0){$bgcolor = $linecolor2 ;} else {$bgcolor = $linecolor1 ;} $bgi++;
echo "
<tr style=\"background-color: ".$bgcolor."\">
  <td align='right' class='tdsmallbold'>Variável com DAP de planta
  &nbsp;<img height='14' src=\"icons/icon_question.gif\" ";
	$help = 'Variável que será usada em alguns scripts automáticos para dados de parcelas florestais. Selecione aqui a variável Diâmetro À Altura do Peito!';
	echo " onclick=\"javascript:alert('$help');\" /></td>
  <td class='tdformnotes'>"; autosuggestfieldval3('search-traits.php','daptrname',$daptrname,'daptraitres','daptraitid',$daptraitid,true,73); echo "</td>
</tr>
";
if ($bgi % 2 == 0){$bgcolor = $linecolor2 ;} else {$bgcolor = $linecolor1 ;} $bgi++;
echo "
<tr style=\"background-color: ".$bgcolor."\">
  <td align='right' class='tdsmallbold'>Variável POM de DAP de planta de parcela
  &nbsp;<img height='14' src=\"icons/icon_question.gif\" ";
	$help = 'Variável que será usada em alguns scripts automáticos para dados de parcelas florestais. Selecione aqui a variável POINT OF MEASUREMENT (POM) referente à altura no tronco quando não for 1.3 metros (AP).';
	echo " onclick=\"javascript:alert('$help');\" /></td>
  <td class='tdformnotes'>"; autosuggestfieldval3('search-traits.php','pomtrname',$pomtrname,'pomtraitres','pomtraitid',$pomtraitid,true,73); echo "</td>
</tr>
";

if ($bgi % 2 == 0){$bgcolor = $linecolor2 ;} else {$bgcolor = $linecolor1 ;} $bgi++;
echo "
<tr style=\"background-color: ".$bgcolor."\">
  <td align='right' class='tdsmallbold'>Variável ALTURA de planta
  &nbsp;<img height='14' src=\"icons/icon_question.gif\" ";
	$help = 'Variável que será usada em alguns scripts automáticos para dados de parcelas florestais. Selecione aqui a variável Altura da Planta!';
	echo " onclick=\"javascript:alert('$help');\" /></td>
  <td class='tdformnotes'>"; autosuggestfieldval3('search-traits.php','alttrname',$alttrname,'alttraitres','alturatraitid',$alturatraitid,true,73); echo "</td>
</tr>
";
if ($bgi % 2 == 0){$bgcolor = $linecolor2 ;} else {$bgcolor = $linecolor1 ;} $bgi++;
echo "
<tr style=\"background-color: ".$bgcolor."\">
  <td align='right' class='tdsmallbold'>Variável HABITO de planta
  &nbsp;<img height='14' src=\"icons/icon_question.gif\" ";
	$help = 'Variável que será usada em alguns scripts automáticos. Selecione aqui a variável referente ao Habito de uma planta!';
	echo " onclick=\"javascript:alert('$help');\" /></td>
  <td class='tdformnotes'>"; autosuggestfieldval3('search-traits.php','habtrname',$habtrname,'habtraitres','habitotraitid',$habitotraitid,true,73); echo "</td>
</tr>
";
if ($bgi % 2 == 0){$bgcolor = $linecolor2 ;} else {$bgcolor = $linecolor1 ;} $bgi++;
echo "
<tr style=\"background-color: ".$bgcolor."\">
  <td align='right' class='tdsmallbold'>Variável STATUS de planta de parcela
  &nbsp;<img height='14' src=\"icons/icon_question.gif\" ";
	$help = 'Variável que será usada em alguns scripts automáticos para dados de parcelas florestais. Selecione aqui a variável Status da Planta na Parcela, morto ou vivo!';
	echo " onclick=\"javascript:alert('$help');\" /></td>
  <td class='tdformnotes'>"; autosuggestfieldval3('search-traits.php','statustrname',$statustrname,'statustraitres','statustraitid',$statustraitid,true,73); echo "</td>
</tr>
";
if ($bgi % 2 == 0){$bgcolor = $linecolor2 ;} else {$bgcolor = $linecolor1 ;} $bgi++;
echo "
<tr style=\"background-color: ".$bgcolor."\">
  <td align='right' class='tdsmallbold'>Variável IMAGEM DE EXSICATA
  &nbsp;<img height='14' src=\"icons/icon_question.gif\" ";
	$help = 'Variável que será usada em alguns scripts para visualizar imagens de plantas herborizadas. Selecionar variável do tipo IMAGEM, onde são armazenadas imagens de exsicatas!';
	echo " onclick=\"javascript:alert('$help');\" /></td>
  <td class='tdformnotes'>"; autosuggestfieldval3('search-traits.php','exsicatatrname',$exsicatatrname,'exsicatatraitres','exsicatatrait',$exsicatatrait,true,73); echo "</td>
</tr>
";
if ($bgi % 2 == 0){$bgcolor = $linecolor2 ;} else {$bgcolor = $linecolor1 ;} $bgi++;
echo "
<tr style=\"background-color: ".$bgcolor."\">
  <td align='right' class='tdsmallbold'>Variável IMAGEM DE FOLHA
  &nbsp;<img height='14' src=\"icons/icon_question.gif\" ";
	$help = 'Variável que será usada em alguns scripts para visualizar imagens de plantas frescas. Selecionar variável do tipo IMAGEM, onde são armazenadas imagens de folhas frescas!';
	echo " onclick=\"javascript:alert('$help');\" /></td>
  <td class='tdformnotes'>"; autosuggestfieldval3('search-traits.php','folhaimgtrname',$folhaimgtrname,'folhaimgtraitres','folhaimgtraitid',$folhaimgtraitid,true,73); echo "</td>
</tr>
";
if ($bgi % 2 == 0){$bgcolor = $linecolor2 ;} else {$bgcolor = $linecolor1 ;} $bgi++;
echo "
<tr style=\"background-color: ".$bgcolor."\">
  <td align='right' class='tdsmallbold'>Variável IMAGEM DE FRUTO
  &nbsp;<img height='14' src=\"icons/icon_question.gif\" ";
	$help = 'Variável que será usada em alguns scripts para visualizar imagens de plantas herborizadas. Selecionar variável do tipo IMAGEM, onde são armazenadas imagens de frutos!';
	echo " onclick=\"javascript:alert('$help');\" /></td>
  <td class='tdformnotes'>"; autosuggestfieldval3('search-traits.php','frutoimgtrname',$frutoimgtrname,'frutoimgtraitres','frutoimgtraitid',$frutoimgtraitid,true,73); echo "</td>
</tr>
";
if ($bgi % 2 == 0){$bgcolor = $linecolor2 ;} else {$bgcolor = $linecolor1 ;} $bgi++;
echo "
<tr style=\"background-color: ".$bgcolor."\">
  <td align='right' class='tdsmallbold'>Variável IMAGEM DE FLORES
  &nbsp;<img height='14' src=\"icons/icon_question.gif\" ";
	$help = 'Variável que será usada em alguns scripts para visualizar imagens de plantas herborizadas. Selecionar variável do tipo IMAGEM, onde são armazenadas imagens de flores!';
	echo " onclick=\"javascript:alert('$help');\" /></td>
  <td class='tdformnotes'>"; autosuggestfieldval3('search-traits.php','florimgtrname',$florimgtrname,'florimgtraitres','florimgtraitid',$florimgtraitid,true,73); echo "</td>
</tr>
";
if ($bgi % 2 == 0){$bgcolor = $linecolor2 ;} else {$bgcolor = $linecolor1 ;} $bgi++;
echo "
<tr style=\"background-color: ".$bgcolor."\">
  <td align='right' class='tdsmallbold'>Variável NÚMERO DE DUPLICATAS de amostra
  &nbsp;<img height='14' src=\"icons/icon_question.gif\" ";
	$help = 'Variável que será usada em alguns scripts para gerar etiquetas. Selecionar variável referente ao número de duplicatas de amostras herborizadas!';
	echo " onclick=\"javascript:alert('$help');\" /></td>  
  <td class='tdformnotes'>"; autosuggestfieldval3('search-traits.php','duplicatestrname',$duplicatestrname,'duplicatestraitres','duplicatesTraitID',$duplicatesTraitID,true,73); echo "</td>
</tr>
";
if ($bgi % 2 == 0){$bgcolor = $linecolor2 ;} else {$bgcolor = $linecolor1 ;} $bgi++;
echo "
<tr style=\"background-color: ".$bgcolor."\">
  <td align='right' class='tdsmallbold'>Variável de FERTILIDADE de amostra  
&nbsp;<img height='14' src=\"icons/icon_question.gif\" ";
	$help = 'Variável que será usada em alguns scripts para gerar visualizações e downloads. Selecione variável que contém informações sobre a fertilidade de uma amostra coletada (fruto, flor, estéril)';
	echo " onclick=\"javascript:alert('$help');\" /></td>  
  <td class='tdformnotes'>"; autosuggestfieldval3('search-traits.php','traitferttrname',$traitferttrname,'traitfertres','traitfertid',$traitfertid,true,73); echo "</td>
</tr>
";
if ($bgi % 2 == 0){$bgcolor = $linecolor2 ;} else {$bgcolor = $linecolor1 ;} $bgi++;
echo "
<tr style=\"background-color: ".$bgcolor."\">
  <td align='right' class='tdsmallbold'>Variável de SILICA de amostra  
&nbsp;<img height='14' src=\"icons/icon_question.gif\" ";
	$help = 'Variável que será usada em alguns scripts para gerar visualizações e downloads. Selecione variável que contém informações sobre a existencia de material em silica de uma amostra coletada';
	echo " onclick=\"javascript:alert('$help');\" /></td>  
  <td class='tdformnotes'>"; autosuggestfieldval3('search-traits.php','traitsilicaname',$traitsilicaname,'traitsilicatres','traitsilica',$traitsilica,true,73); echo "</td>
</tr>
";
if ($bgi % 2 == 0){$bgcolor = $linecolor2 ;} else {$bgcolor = $linecolor1 ;} $bgi++;
echo "
<tr style=\"background-color: ".$bgcolor."\">
  <td align='right' class='tdsmallbold'>Variável NOTAS DE LOCALIDADE
  &nbsp;<img height='14' src=\"icons/icon_question.gif\" ";
	$help = 'Variável que será usada em alguns scripts. Selecionar variável referente a notas de localidade';
	echo " onclick=\"javascript:alert('$help');\" /></td>  
  <td class='tdformnotes'>"; autosuggestfieldval3('search-traits.php','localidadestrname',$localidadestrname,'localidadestraitres','localidadetraitid',$localidadetraitid,true,73); echo "</td>
</tr>
";
if ($bgi % 2 == 0){$bgcolor = $linecolor2 ;} else {$bgcolor = $linecolor1 ;} $bgi++;
echo "
<tr style=\"background-color: ".$bgcolor."\">
  <td align='right' class='tdsmallbold'>Formulário de notas padrão
&nbsp;<img height='14' src=\"icons/icon_question.gif\" ";
	$help = 'Formulário contendo variáveis para fazer descrições de amostras coletadas e plantas marcadas, usado para mostrar dados em algumas visualizações!';
	echo " onclick=\"javascript:alert('$help');\" /></td>  
  <td class='tdformnotes'>
    <select name='formnotes' >";
	if (!empty($formnotes)) {
		$qq = "SELECT * FROM Formularios WHERE FormID='".$formnotes."'";
		$rr = mysql_query($qq,$conn);
		$row= mysql_fetch_assoc($rr);
		echo "
      <option selected value='".$row['FormID']."'>".$row['FormName']." (".$row['AddedDate'].")</option>";
	} else {
		echo "
      <option value=''>".GetLangVar('nameselect')."</option>";
	}
	//formularios usuario
	$qq = "SELECT * FROM Formularios WHERE AddedBy=".$_SESSION['userid']." OR Shared=1 ORDER BY Formularios.FormName ASC";
	$rr = mysql_query($qq,$conn);
	while ($row= mysql_fetch_assoc($rr)) {
		echo "
      <option value='".$row['FormID']."'>".$row['FormName']."</option>";
	}
echo "
    </select>
  </td>
</tr>";
if ($bgi % 2 == 0){$bgcolor = $linecolor2 ;} else {$bgcolor = $linecolor1 ;} $bgi++;
echo "
<tr style=\"background-color: ".$bgcolor."\">
  <td align='right' class='tdsmallbold'>Formulário de habitat padrão
&nbsp;<img height='14' src=\"icons/icon_question.gif\" ";
	$help = 'Formulário contendo variáveis de hábitat (relacionadas a uma localidade) para fazer descrições em algumas visualizações de dados!';
	echo " onclick=\"javascript:alert('$help');\" /></td>  
  <td class='tdformnotes'>
    <select name='formidhabitat' >";
	if (!empty($formidhabitat)) {
		$qq = "SELECT * FROM Formularios WHERE FormID='$formidhabitat'";
		$rr = mysql_query($qq,$conn);
		$row= mysql_fetch_assoc($rr);
		echo "
      <option selected value='".$row['FormID']."'>".$row['FormName']." (".$row['AddedDate'].")</option>";
	} else {
		echo "
      <option value=''>".GetLangVar('nameselect')."</option>";
	}
	//formularios usuario
	$qq = "SELECT * FROM Formularios WHERE AddedBy=".$_SESSION['userid']." OR Shared=1 ORDER BY Formularios.FormName ASC";
	$rr = mysql_query($qq,$conn);
	while ($row= mysql_fetch_assoc($rr)) {
		echo "
      <option value='".$row['FormID']."'>".$row['FormName']."</option>";
	}
echo "
    </select>
  </td>
</tr>";
if ($bgi % 2 == 0){$bgcolor = $linecolor2 ;} else {$bgcolor = $linecolor1 ;} $bgi++;
echo "
<tr style=\"background-color: ".$bgcolor."\">
  <td align='right' class='tdsmallbold'>Nome do Herbário Padrão  
&nbsp;<img height='14' src=\"icons/icon_question.gif\" ";
	$help = 'Nome por extenso do herbário padrão da base, que será adicionado às etiquetas.';
	echo " onclick=\"javascript:alert('$help');\" /></td>  
  <td><input type='text' style='height: 2em; width: 400px;'  name='herbariumnome' value='".$herbariumnome."' /></td>
</tr>
";
if ($bgi % 2 == 0){$bgcolor = $linecolor2 ;} else {$bgcolor = $linecolor1 ;} $bgi++;
echo "
<tr style=\"background-color: ".$bgcolor."\">
  <td align='right' class='tdsmallbold'>Sigla do Herbário Padrão  
&nbsp;<img height='14' src=\"icons/icon_question.gif\" ";
	$help = 'Sigla herbário padrão da base, que será adicionado às etiquetas e formulários';
	echo " onclick=\"javascript:alert('$help');\" /></td>  
  <td><input type='text' style='height: 2em; width: 400px;'  name='herbariumsigla' value='".$herbariumsigla."' /></td>
</tr>
";
if ($bgi % 2 == 0){$bgcolor = $linecolor2 ;} else {$bgcolor = $linecolor1 ;} $bgi++;
echo "
<tr style=\"background-color: ".$bgcolor."\">
  <td align='right' class='tdsmallbold'>Logo do Herbário Padrão  
&nbsp;<img height='14' src=\"icons/icon_question.gif\" ";
	$help = 'Imagem de logo para adicionar às etiquetas';
	echo " onclick=\"javascript:alert('$help');\" /></td>  
  <td>";
if (!empty($herbariumlogo)) {

echo "<img  src=\"icons/".$herbariumlogo."\"  height='50'/>";
}
echo "
      <input type='hidden' name='MAX_FILE_SIZE' value='10000000' />
      <input name='oldherbariumlogo' type='hidden'  value='".$herbariumlogo."' />
      <input name='herbariumlogo' type='file' width='20' />
  </td>
</tr>";
if ($bgi % 2 == 0){$bgcolor = $linecolor2 ;} else {$bgcolor = $linecolor1 ;} $bgi++;
echo "
<tr style=\"background-color: ".$bgcolor."\">
  <td align='right' class='tdsmallbold'>Dados públicos?  
&nbsp;<img height='14' src=\"icons/icon_question.gif\" ";
	$help = 'Indique quais listas (se houver) serão de acesso aberto (públicas). De outra forma uma política de dados será seguido  para conjunto de dado (à implementar)!';
	echo " onclick=\"javascript:alert('$help');\" /></td>  
  <td>
  <table style='font-size: 0.8em;' >";
$chk ='';
$chk2 ='';  
if ($listsarepublic['species']=='on') {
$chk='checked';
}
if ($listsarepublic['speciesdownload']=='on') {
$chk2='checked';
}
echo "<tr ><td style='border:1px solid yellow;  background-color: yellow;' >Checklist</td>
<td style='border:1px solid yellow;  background-color: yellow;' ><input type='checkbox'  ".$chk." name=\"listsarepublic[species]\" />&nbsp;Público pode ler apenas<br />
<input type='checkbox'  ".$chk2." name=\"listsarepublic[speciesdownload]\" />&nbsp;Público pode baixar lista</td>";
$chk ='';
$chk2 ='';
if ($listsarepublic['specimenes']=='on') {
$chk='checked';
}
if ($listsarepublic['especimenesdownload']=='on') {
$chk2='checked';
}
echo "<td style='border:1px solid lightblue;  background-color: lightblue;' >Especímenes</td>
<td style='border:1px solid lightblue;  background-color: lightblue;' ><input type='checkbox'  ".$chk." name=\"listsarepublic[specimenes]\" />&nbsp;Público pode ler apenas<br />
<input type='checkbox'  ".$chk2." name=\"listsarepublic[especimenesdownload]\" />&nbsp;Público pode baixar lista</td>
</tr>";
$chk ='';
$chk2 ='';
if ($listsarepublic['plantas']=='on') {
$chk='checked';
}
if ($listsarepublic['plantasdownload']=='on') {
$chk2='checked';
}
echo "<tr><td style='border:1px solid green; background-color: green;' >Plantas</td>
<td style='border:1px solid green;  background-color: green;' ><input type='checkbox'  ".$chk." name=\"listsarepublic[plantas]\" />&nbsp;Público pode ler apenas<br />
<input type='checkbox'  ".$chk2." name=\"listsarepublic[plantasdownload]\" />&nbsp;Público pode baixar lista</td>
";
$chk ='';
$chk2 ='';
$chk3 ='';
if ($listsarepublic['plots']=='on') {
$chk='checked';
}
if ($listsarepublic['plotsdownload']=='on') {
$chk2='checked';
}
if ($listsarepublic['plotsdata']=='on') {
$chk3='checked';
}
echo "<td style='border:1px solid black;  background-color: black; color: white;' >Plots</td>
<td style='border:1px solid black;  background-color: black; color: white;' ><input type='checkbox'  ".$chk." name=\"listsarepublic[plots]\" />&nbsp;Público pode ler apenas<br />
<input type='checkbox'  ".$chk2." name=\"listsarepublic[plotsdownload]\" />&nbsp;Público pode baixar lista<br />
<input type='checkbox'  ".$chk3." name=\"listsarepublic[plotsdata]\" />&nbsp;Público pode baixar dados/censos<br />
</td>
</tr>";
echo "</table></td>
</tr>";
if ($bgi % 2 == 0){$bgcolor = $linecolor2 ;} else {$bgcolor = $linecolor1 ;} $bgi++;
echo "
<tr style=\"background-color: ".$bgcolor."\">
  <td colspan='2' align='center'><input type='submit' value='".GetLangVar('nameenviar')."' class='bsubmit' /></td>
</tr>";
echo "
</tbody>
</table>
</form>
<br />
";
}
else {
//echopre($ppost);
	$strvars = array('dbname', 'databaseconnection', 'databaseconnection_clean', 'relativepathtoroot', 'metatitle', 'metadesc', 'metakeyw', 'metacompany', 'lang', 'herbariumnome', 'herbariumsigla', 'blockacess','herbariumlogo','sitetitle','sitelogo','introtext');
	$numvars = array( 'habitotraitid','pomtraitid', 'statustraitid', 'traitfertid', 'alturatraitid', 'daptraitid', 'duplicatesTraitID', 'exsicatatrait', 'formidhabitat', 'formnotes','traitsilica','localidadetraitid','folhaimgtraitid','florimgtraitid','frutoimgtraitid');
	$strsim = array('databaseconnection', 'databaseconnection_clean', 'relativepathtoroot', 'lang', 'herbariumnome', 'herbariumsigla', 'blockacess', 'herbariumlogo','sitetitle','sitelogo','introtext');

$nc = $_FILES['herbariumlogo']['tmp_name']; 
if (!empty($nc)) {
	$ext = explode(".",$_FILES['herbariumlogo']['name']);
	$ll = count($ext)-1;
	$imgext = strtoupper($ext[$ll]);
	$newname = 'herbariumlogo_default.'.strtolower($imgext);

	//move o arquivo para a pasta de icones
	move_uploaded_file($_FILES['herbariumlogo']["tmp_name"],"icons/".$newname); 
	$ppost['herbariumlogo'] = $newname;
} else {
	$ppost['herbariumlogo'] = $oldherbariumlogo;
}

	$nc = $_FILES['sitelogo']['tmp_name']; 
	//echopre($_FILES);
	if (!empty($nc)) {
		$ext = explode(".",$_FILES['sitelogo']['name']);
		$ll = count($ext)-1;
		$imgext = strtoupper($ext[$ll]);
		$newname = 'sitelogo_default.'.strtolower($imgext);
		//move o arquivo para a pasta de icones
		move_uploaded_file($_FILES['sitelogo']["tmp_name"],"icons/".$newname); 
		$ppost['sitelogo'] = $newname;
	} else {
		$ppost['sitelogo'] = $oldsitelogo;
	}


	$url = $_SERVER['HTTP_REFERER'];
	$uu = explode("/",$url);
	$nu = count($uu)-1;
	unset($uu[$nu]);
	$url = implode("/",$uu);

	$fh = fopen("functions/databaseSettings.php", 'w');
	$stringData = "<?php";
	foreach ($strvars as $kk) {
		$vv = $ppost[$kk];
		$stringData .= "
\$".$kk." = \"".$vv."\";";
	}
	$stringData .= "
\$metaurl = \"".$url."\";";
	foreach ($numvars as $kk) {
		$vv = $ppost[$kk]+0;
		$stringData .= "
\$".$kk." = ".$vv.";";
	}
	$stringData .= "
\$listsarepublic = array();";
	foreach ($listsarepublic as $kk => $vv) {
		$kk = str_replace('"', "", $kk);
		$kk = str_replace("'", "", $kk);
		$kk = str_replace("\\", "", $kk);
		$stringData .= "
\$listsarepublic['".$kk."'] = \"".$vv."\";";
	}

	$stringData .= "
?>";
	fwrite($fh, $stringData);
	fclose($fh);
	
	$fh = fopen("functions/databaseSettings_small.php", 'w');
	$stringData = "<?php";
	foreach ($strsim as $kk) {
		$vv = $ppost[$kk];
		$stringData .= "
\$".$kk." = \"".$vv."\";";
	}
	$stringData .= "
\$metaurl = \"".$url."\";";
	foreach ($numvars as $kk) {
		$vv = $ppost[$kk]+0;
		//echo $vv."  em ".$kk."<br />";
		$stringData .= "
\$".$kk." = ".$vv.";";
	}
	
	$stringData .= "
\$listsarepublic = array();";
	foreach ($listsarepublic as $kk => $vv) {
		$kk = str_replace('"', "", $kk);
		$kk = str_replace("'", "", $kk);
		$kk = str_replace("\\", "", $kk);
		$stringData .= "
\$listsarepublic['".$kk."'] = ".$vv.";";
	}
	
	
	$stringData .= "
?>";
	fwrite($fh, $stringData);
	fclose($fh);
	//echo "<textarea>".$stringData."</textarea>";
	echo "
<br />
<table align='center' class='success' cellpadding=\"5\">
<tr><td>As configurações foram salvas com sucesso!</td></tr>
<tr><td><input type=\"button\" class='bred' value=\"Fechar\" onclick=\"javascript: window.close();\" /></td></tr>
</table>
";
}

$which_java = array(
"<script type='text/javascript' src='javascript/myjavascripts.js'></script>"
);
FazFooter($which_java,$calendar=FALSE,$footer=$menu);

?>
