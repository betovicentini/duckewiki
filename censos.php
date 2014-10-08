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
if ($ispopup==1) {
	$menu = FALSE;
} else {
	$menu = TRUE;
}
$which_css = array(
"<link href='css/geral.css' rel='stylesheet' type='text/css' />",
"<link rel='stylesheet' type='text/css' href='css/cssmenu.css' />",
"<link rel='stylesheet' type='text/css' href='css/colorbuttons.css' />",
"<link rel='stylesheet' type='text/css' media='screen' href='css/autosuggest.css' />"
);
$which_java = array(
"<script type='text/javascript' src='javascript/ajax_framework.js'></script>",
"<script type='text/javascript' src='css/cssmenuCore.js'></script>",
"<script type='text/javascript' src='css/cssmenuAddOns.js'></script>",
"<script type='text/javascript' src='css/cssmenuAddOnsItemBullet.js'></script>",
"<script type='text/javascript' >
function openuser(){
	var usid = document.getElementById('responsaveltag').value;
	if (usid!='') {
		//alert(usid);
         small_window('usuario-form.php?usuarioid='+usid+'&ispopup=1&submitted=editando',600,500,'Editando usuário');
	} else {
		alert('Precisa selecionar um responsável');
	}
}
</script>"
);
$title = 'Definir Censos';
$body = '';
FazHeader($title,$body,$which_css,$which_java,$menu);

//ATUALIZA A TABELA CENSOS PARA ADICIONAR UM RESPONSAVEL PELOS DADOS DO CENSO (A PESSOA QUE AUTORIZA O USO DOS DADOS)
$qq = "ALTER TABLE `Censos`  ADD `ResponsavelID` INT(10) NULL DEFAULT NULL COMMENT 'UserID' AFTER `DataFim`";
@mysql_query($qq,$conn);

echo "
<br />
<form action='censos.php' name='finalform' method='post'>
<input type='hidden' name='ispopup' value='".$ispopup."' />
<table align='center' cellspacing='0' cellpadding='5' class='myformtable'>
<thead>
  <tr><td colspan='2'>Definir Censos&nbsp;<img height='13' src=\"icons/icon_question.gif\" ";
		$help = 'Você pode definir 1 ou mais censos para um conjunto de variáveis de monitoramento associadas à árvores marcadas. Selecione primeiro o filtro que contém as árvores e depois indique as datas que definem o censo. O filtro pode incluir árvores que não fazem parte do censo, que não farão parte do censo se não houver medições para elas durante o período indicado';
		echo " onclick=\"javascript:alert('$help');\" /></td></tr>
</thead>
<tbody>
<tr>";
if (!isset($filtro)) {
	///UPDATE TABLES CASE THEY HAVE NOT BEEN YET
	$qq = "CREATE TABLE IF NOT EXISTS Censos (
				CensoID INT(10) unsigned NOT NULL auto_increment,
				CensoNome VARCHAR(200),
				DataInicio DATE,
				DataFim DATE,
				ResponsavelID INT(10) NULL DEFAULT NULL COMMENT 'UserID',
				AddedBy INT(10),
				AddedDate DATE,
				PRIMARY KEY (CensoID)) CHARACTER SET utf8";
	@mysql_query($qq,$conn);
	$qq = "ALTER TABLE Censos ENGINE = InnoDB";
	@mysql_query($qq,$conn);
	$update = "ALTER TABLE Monitoramento ADD COLUMN CensoID INT(10) DEFAULT 0 AFTER DataOBS";
	@mysql_query($update,$conn);
	$qq = "ALTER TABLE `Monitoramento` ADD INDEX `data` (`DataObs`)";
	@mysql_query($qq,$conn);

if ($bgi % 2 == 0){$bgcolor = $linecolor2 ;}  else {$bgcolor = $linecolor1 ;} $bgi++;
echo "
<tr bgcolor = '".$bgcolor."'>
  <td class='tdsmallbold'>Filtro que contém as árvores:</td>
        <td>
          <select name='filtro'>";
		if (!empty($filtro)) {
			$qq = "SELECT * FROM Filtros WHERE FiltroID='".$filtroid."'";
			$res = @mysql_query($qq,$conn);
			$rr = @mysql_fetch_assoc($res);
			echo "
          <option selected value='".$rr['FiltroID']."'>".$rr['FiltroName']."</option>";
		}
			echo "
          <option selected value=''>Selecione</option>";
			$qq = "SELECT * FROM Filtros WHERE (AddedBy=".$_SESSION['userid']." OR Shared=1) AND PlantasIDS IS NOT NULL ORDER BY FiltroName";
			$res = @mysql_query($qq,$conn);
			while ($rr = @mysql_fetch_assoc($res)) {
				echo "
          <option value='".$rr['FiltroID']."'>".$rr['FiltroName']."</option>";
			}

	echo "
          </select>
        </td>
</tr>
";
if ($bgi % 2 == 0){$bgcolor = $linecolor2 ;}  else {$bgcolor = $linecolor1 ;} $bgi++;
echo "
<tr bgcolor = '".$bgcolor."'>
  <td colspan='2' align='center'><input type='submit' value='Continuar' class='bblue' /></td>
</tr>";
}
if (!isset($datainicio) && !isset($datafim) && ($filtro+0)>0) {
	//quantas árvores tem no filtro
	$qz = "SELECT COUNT(*) as npl FROM Plantas AS pl WHERE pl.FiltrosIDS LIKE '%filtroid_".$filtro."%'";
	$rz = mysql_query($qz,$conn);
	$rwz = mysql_fetch_assoc($rz);
	$narv = $rwz['npl'];

if ($bgi % 2 == 0){$bgcolor = $linecolor2 ;}  else {$bgcolor = $linecolor1 ;} $bgi++;
echo "
<tr bgcolor = '".$bgcolor."'>
  <td colspan='2' class='tdformnotes'>O filtro selecionado contém $narv árvores! O novo censo será definido APENAS para as árvores desse conjunto que tenham medições nas variáveis incluídas no formulário, com data de observação dentro do período indicado aqui.</td>
</tr>
";	
	//já tem censos cadastrados
	$qz = "SELECT DISTINCT CensoID FROM Monitoramento LEFT JOIN Plantas AS pl USING(PlantaID) WHERE pl.FiltrosIDS LIKE '%filtroid_".$filtro."%' AND CensoID>0";
	$rz = @mysql_query($qz,$conn);
	$ncensos = @mysql_numrows($rz);
	
	if ($ncensos>0) {
		if ($bgi % 2 == 0){$bgcolor = $linecolor2 ;}  else {$bgcolor = $linecolor1 ;} $bgi++;
		echo "
<tr bgcolor = '".$bgcolor."'>
  <td class='tdsmallbold'>Censos definidos</td>
    <td>
    <table>
      <tr>
        <td>
          <select name='censos' multiple size='5' readonly>";
			while ($rr = @mysql_fetch_assoc($rz)) {
				$qk = "SELECT * FROM Censos WHERE CensoID='".$rr['CensoID']."'";
				$rk = @mysql_query($qk,$conn);
				$rwk = @mysql_fetch_assoc($rk);
				$qz = "SELECT COUNT(DISTINCT TraitID) as trs FROM Monitoramento LEFT JOIN Plantas AS pl USING(PlantaID) WHERE pl.FiltrosIDS LIKE '%filtroid_".$filtro."%' AND CensoID='".$rr['CensoID']."'";
				$rzk = @mysql_query($qz,$conn);
				$rwzk = @mysql_fetch_assoc($rzk);
				$trs = $rwzk['trs'];
				$qz = "SELECT COUNT(DISTINCT PlantaID) as pls FROM Monitoramento LEFT JOIN Plantas AS pl USING(PlantaID) WHERE pl.FiltrosIDS LIKE '%filtroid_".$filtro."%' AND CensoID='".$rr['CensoID']."'";
				$rzk = @mysql_query($qz,$conn);
				$rwzk = @mysql_fetch_assoc($rzk);
				$pls = $rwzk['pls'];

				echo "
          <option value='".$rwk['CensoID']."'>".$rwk['CensoNome']." [".$rwk['DataInicio']." à ".$rwk['DataFim']."] - inclui ".$pls." árvores e ".$trs." variáveis</option>";
			}
	echo "
          </select>
        </td>
        <td><input type=button class='bsubmit' value='Editar/atualizar um censo'  onclick =\"javascript:small_window('censo-edit.php?ispopup=1&filtro=".$filtro."',900,400,'Editar censo');\" /></td>
      </tr>
    </table>
  </td>
</tr>
";
	}
if ($bgi % 2 == 0){$bgcolor = $linecolor2 ;}  else {$bgcolor = $linecolor1 ;} $bgi++;
		echo "
<tr bgcolor = '".$bgcolor."'>
  <td class='tdsmallbold'>Novo Censo</td>
  <td>
    <table>
      <tr>
        <td>Formulário com váriaveis do censo:</td>
        <td colspan='3' class='tdformnotes'>
          <select name='formularioid' >";
			echo "
            <option value=''>".GetLangVar('nameselect')."</option>";
		//formularios usuario
		$qq = "SELECT * FROM Formularios WHERE AddedBy=".$_SESSION['userid']." OR Shared=1 ORDER BY FormName,Formularios.AddedDate ASC";
		$rr = mysql_query($qq,$conn);
		while ($row= mysql_fetch_assoc($rr)) {
			echo "
            <option value='".$row['FormID']."'>".$row['FormName']."</option>";
		}
	echo "
          </select>
        </td>
      </tr>
      <tr>
        <td>Nome para o censo</td>
        <td colspan='3'>
          <input type='hidden' name='filtro' value='".$filtro."' />
          <input type='text' value='".$censonome."' name='censonome' size='60'/>
        </td>
      <tr>
        <td>Data início</td>
        <td>
          <select name='datainicio'>";
			echo "
          <option selected value=''>Selecione das medições</option>";
			$qzz = "SELECT DISTINCT DataOBS FROM Monitoramento LEFT JOIN Plantas AS pl USING(PlantaID) WHERE pl.FiltrosIDS LIKE '%filtroid_".$filtro."%' AND CensoID=0 ORDER BY DataOBS ASC";
			$rzz = mysql_query($qzz,$conn);
			while ($rrr = @mysql_fetch_assoc($rzz)) {
				echo "
          <option value='".$rrr['DataOBS']."'>".$rrr['DataOBS']."</option>";
			}
	echo "
          </select>
        </td>
        <td align='right'> ou escolha:</td>
        <td><input class=\"plain\" name=\"datainicio_nova\" value=\"$datainicio_nova\" size=\"11\"  readonly><a onclick=\"if(self.gfPop)gfPop.fPopCalendar(document.forms['finalform'].datainicio_nova);return false;\" ><img name=\"popcal\" align=\"absmiddle\" src=\"calendar/calbtn.gif\" width=\"34\" height=\"22\" border=\"0\" alt=\"\" /></a></td>
      </tr>
      <tr>
        <td>Data fim:</td>
        <td>
          <select name='datafim'>";
			echo "
          <option selected value=''>Selecione das medições</option>";
			$qzz = "SELECT DISTINCT DataOBS FROM Monitoramento LEFT JOIN Plantas AS pl USING(PlantaID) WHERE pl.FiltrosIDS LIKE '%filtroid_".$filtro."%' AND CensoID=0 ORDER BY DataOBS DESC";
			$rzz = mysql_query($qzz,$conn);
			while ($rrr = @mysql_fetch_assoc($rzz)) {
				echo "
          <option value='".$rrr['DataOBS']."'>".$rrr['DataOBS']."</option>";
			}
	echo "
          </select>
        </td>
        <td align='right'> ou escolha:</td>
        <td><input class=\"plain\" name=\"datafim_nova\" value=\"$datafim_nova\" size=\"11\"  readonly><a onclick=\"if(self.gfPop)gfPop.fPopCalendar(document.forms['finalform'].datafim_nova);return false;\" ><img name=\"popcal\" align=\"absmiddle\" src=\"calendar/calbtn.gif\" width=\"34\" height=\"22\" border=\"0\" alt=\"\" /></a></td>
      </tr>
      <tr>
        <td class='tdsmallboldright'>Responsável&nbsp;<img style='cursor: pointer;' height=12 src=\"icons/icon_question.gif\" ";
				$help = "O email dessa pessoa será usado para contato de usuários solicitando dados";
				echo " onclick=\"javascript:alert('$help');\" /></td>
        <td>
          <select id='responsaveltag'  name='responsavelid' >";
		 	if (!isset($responsavelid)) {
				echo "
        <option value=''>".GetLangVar('nameselect')." ".strtolower(GetLangVar('nameeditar'))."</option>";
			} 
			echo "
        <option value=''>----</option>";
			$wrr = mysql_query("SELECT * FROM Users ORDER BY FirstName,LastName",$conn);
			while ($aa = mysql_fetch_assoc($wrr)){
				echo "
        <option value='".$aa['UserID']."'>".$aa['FirstName']." ".$aa['LastName']."</option>";
			}
	echo "
    </select>
       </td>
        <td colspan='2'>
            <input type='button'  onclick='javascript: openuser();'  value='Ver registro'>
        </td>
      </tr>
    </table>
  </td>
</tr>";
if ($bgi % 2 == 0){$bgcolor = $linecolor2 ;}  else {$bgcolor = $linecolor1 ;} $bgi++;
echo "
<tr bgcolor = '".$bgcolor."'>
  <td colspan='2' align='center'><input type='submit' value='Continuar' class='bblue' /></td>
</tr>";
}
if ((!empty($datainicio) || !empty($datainicio_nova)) && (!empty($datafim) || !empty($datafim_nova)) && ($filtro+0)>0 && ($formularioid+0)>0) {
	if (empty($datainicio)) {
		$datainicio = $datainicio_nova;
	}
	if (empty($datafim)) {
		$datafim = $datafim_nova;
	}
	$arrayofvalues = array(
				'CensoNome' => $censonome,
				'DataInicio' => $datainicio,
				'DataFim' => $datafim,
				'ResponsavelID' => $responsavelid
	);
	$censoset = InsertIntoTable($arrayofvalues,'CensoID','Censos',$conn);
	if ($censoset>0) {
		$qq = "SELECT FormFieldsIDS FROM Formularios WHERE FormID='".$formularioid."'";
		$rq = mysql_query($qq,$conn);
		$rqw = mysql_fetch_assoc($rq);
		$traits = explode(";",$rqw['FormFieldsIDS']);
		$tr = 0;
		foreach ($traits as $vv)  {
			$qzz = "UPDATE Monitoramento as moni, Plantas as pl SET moni.CensoID='".$censoset."' WHERE pl.PlantaID=moni.PlantaID AND pl.FiltrosIDS LIKE '%filtroid_".$filtro."%' AND moni.DataOBS>='".$datainicio."' AND  moni.DataOBS<='".$datafim."' AND moni.TraitID='".$vv."'";
			$rzz = @mysql_query($qzz,$conn);
			if ($rzz) {
				$tr++;
			}
		}
		if ($tr>0) {
			if ($bgi % 2 == 0){$bgcolor = $linecolor2 ;}  else {$bgcolor = $linecolor1 ;} $bgi++;
			echo "
<tr bgcolor = '".$bgcolor."'>
  <td colspan='2' style='font-size: 1.5em; color: red;'>O censo foi definido com sucesso!</td>
</tr>
";
			}
	}
if ($bgi % 2 == 0){$bgcolor = $linecolor2 ;}  else {$bgcolor = $linecolor1 ;} $bgi++;
echo "
<tr bgcolor = '".$bgcolor."'>
  <td colspan='2' align='center'><input type='button' value='Fechar' class='bblue' onclick='javascript: window.close();'/></td>
</tr>";
}


echo "
</tbody>
</table>
</form>
";

$which_java = array("<script type='text/javascript' src='javascript/myjavascripts.js'></script>",
"<!-- Create Menu Settings: (Menu ID, Is Vertical, Show Timer, Hide Timer, On Click ('all' or 'lev2'), Right to Left, Horizontal Subs, Flush Left, Flush Top) -->",
"<script type='text/javascript'>qm_create(0,false,0,500,false,false,false,false,false);</script>");
FazFooter($which_java,$calendar=TRUE,$footer=$menu);

?>