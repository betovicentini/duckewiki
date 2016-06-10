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
$sessiondate = $_SESSION['sessiondate'];
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

//CABECALHO
$menu = FALSE;
$which_css = array(
"<link href='css/geral.css' rel='stylesheet' type='text/css' />",
"<link rel='stylesheet' type='text/css' href='css/colorbuttons.css' />");
$which_java = array();
$title = 'Projeto';
$body = '';
FazHeader($title,$body,$which_css,$which_java,$menu);
//echo "<span style='color: red; font-size: 2em;'> Estou mexendo aqui. Não use!</span>";
//$sql = "ALTER TABLE `Projetos`  ADD `MorfoFormID` INT(10) NOT NULL AFTER `Equipe`,  ADD `HabitatFormID` INT(10) NOT NULL AFTER `MorfoFormID`";
//@mysql_query($sql,$conn);

$sql = "ALTER TABLE `Projetos`  ADD `MorformsIDs` TEXT DEFAULT NULL AFTER `Equipe`";
@mysql_query($sql,$conn);


//PEGA DADOS VELHOS SE HOUVER
if ($submitted=='editando') {
	$_SESSION['editando']=1;
	$qq = "SELECT * FROM Projetos WHERE ProjetoID='".$projetoid."'";
	$res = mysql_query($qq,$conn);
	$rww = mysql_fetch_assoc($res);
	$projetonome = $rww['ProjetoNome'];
	//$projformidmorfo = $rww['MorfoFormID'];
	$projformidmorfo = explode(";",$rww['MorformsIDs']);
	$projformidhab = $rww['HabitatFormID'];
	$projetourl = $rww['ProjetoURL'];
	$logofile = $rww['LogoFile'];
	$addcolvalue = $rww['Equipe'];
	if (empty($addcoltxt) && !empty($addcolvalue)) {
	$addcolarr = explode(";",$addcolvalue);
	$addcoltxt = '';
	$j=1;
	foreach ($addcolarr as $kk => $val) {
		$qq = "SELECT * FROM Pessoas WHERE PessoaID='".$val."'";
		$res = mysql_query($qq,$conn);
		$row = mysql_fetch_assoc($res);
		if ($j==1) {
			$addcoltxt = $row['Abreviacao'];
		} else {
			$addcoltxt = $addcoltxt."; ".$row['Abreviacao'];
		}
		$j++;
	}
	}
	$agencia = explode(";",$rww['Financiamento']);
	$processo = explode(";",$rww['Processos']);

	$qq = "SELECT * FROM ProjetosEspecs WHERE ProjetoID='".$projetoid."' AND EspecimenID>0";
	//echo $qq."<br />";
	$rr = mysql_query($qq,$conn);
	$nrr = mysql_numrows($rr);
	if ($nrr==0) {
		$qq = "INSERT INTO ProjetosEspecs (EspecimenID, PlantaID, ProjetoID, AddedBy, AddedDate) SELECT EspecimenID, PlantaID, ProjetoID, ".$uuid.", '".$sessiondate."' FROM Especimenes WHERE ProjetoID='".$projetoid."'";
		@mysql_query($qq,$conn);
		$qq = "SELECT * FROM ProjetosEspecs WHERE ProjetoID='".$projetoid."' AND EspecimenID>0";
		$rr = mysql_query($qq,$conn);
		$nrr = mysql_numrows($rr);
	}
	if ($nrr>0) {
		$nspecs = $nrr;
    	$especimenestxt = $nrr." ".strtolower(GetLangVar('nameregistro'))."s";
	}
	$qq = "SELECT * FROM ProjetosEspecs WHERE ProjetoID='".$projetoid."' AND PlantaID>0";
	$rr = mysql_query($qq,$conn);
	$nrr = mysql_numrows($rr);
	if ($nrr==0) {
		$qq = "INSERT INTO ProjetosEspecs (PlantaID, ProjetoID, AddedBy, AddedDate) SELECT PlantaID, ProjetoID, ".$uuid.", '".$sessiondate."' FROM Plantas WHERE ProjetoID='".$projetoid."'";
		@mysql_query($qq,$conn);
		$qq = "SELECT * FROM ProjetosEspecs WHERE ProjetoID='".$projetoid."' AND PlantaID>0";
		$rr = mysql_query($qq,$conn);
		$nrr = mysql_numrows($rr);
	}
	if ($nrr>0) {
		$nplantas = $nrr;
    	$plantastxt = $nrr." ".strtolower(GetLangVar('nameregistro'))."s";
	}
	$txthead =  GetLangVar('nameeditando')." ".strtolower(GetLangVar('nameprojeto'))." ".$quem;
} 
elseif ($submitted=='novo') {
	unset($_SESSION['editando']);
		$txthead = GetLangVar('namenovo')." ".strtolower(GetLangVar('nameprojeto'));
}

if (empty($submitted)) {
echo "
<br />
<table align='center' class='myformtable' cellpadding='7'>
<thead>
<tr><td >".GetLangVar('namecadastro')." ".GetLangVar('nameprojeto')."</td></tr>
</thead>
<tbody>
<tr>
  <td >
    <table>
      <tr>
        <td>
<form action='projeto-form.php' method='post'>
  <input type='hidden' value='editando' name='submitted' />
          <select name='projetoid' onchange='this.form.submit();'>
             <option value=''>Selecione para editar</option>";
				$qq = "SELECT * FROM Projetos ORDER BY ProjetoNome";
				$res = mysql_query($qq,$conn);
				while ($rw = mysql_fetch_assoc($res)) {
					echo "
            <option   value='".$rw['ProjetoID']."'>".$rw['ProjetoNome']."</option>";
				}
	echo "
          </select>
</form>
        </td>
        <td align='center'>
<form action='projeto-form.php' method='post'>
  <input type='hidden' value='novo' name='submitted' />
  <input type='submit' value='".GetLangVar('namenovo')." ".GetLangVar('namecadastro')."' class='bsubmit' />
</form>
        </td>
      </tr>
    </table>
  </td>        
</tr>
";
} 
else {

echo "
<br />
<form enctype='multipart/form-data' action='projeto-exec.php' method='post' name='finalform'>
<input type='hidden' value='$projetoid' name='projetoid' />
<input type='hidden' name='oldespecimensids' value='$especimenesids' />
<table align='center' class='myformtable' cellpadding='7'>
<thead>
  <tr><td >".$txthead."</td></tr>
</thead>
<tbody>
";
if ($bgi % 2 == 0) {$bgcolor = $linecolor2 ;} else { $bgcolor = $linecolor1 ;} $bgi++;
echo "
<tr bgcolor = '".$bgcolor."'>
  <td >
    <table>
      <tr>
        <td class='tdsmallbold' >".GetLangVar('namenome')."*</td>
        <td ><input type='text' name='projetonome' value='$projetonome' size='60' /></td>
        </tr>
        <tr>
        <td class='tdsmallbold' >URL</td>
        <td ><input type='text' name='projetourl' value='$projetourl' size='60'  /></td>
      </tr>
    </table>
  </td>
</tr>";
if ($bgi % 2 == 0) {$bgcolor = $linecolor2 ;} else { $bgcolor = $linecolor1 ;} $bgi++;
	echo "
<tr bgcolor = '".$bgcolor."'>
  <td >
    <table>
      <tr>
        <td class='tdsmallboldright'>Equipe</td>
        <td class='tdformnotes' >
          <input type='hidden' name='addcolvalue' value='$addcolvalue' />
          <textarea name='addcoltxt' readonly cols=50 rows=3 >".$addcoltxt."</textarea>
        </td>
        <td>
          <input type=button value=\"+\" class='bsubmit' ";
		$myurl ="addcollpopup.php?getaddcollids=$addcolvalue&formname=finalform";
		echo " onclick = \"javascript:small_window('$myurl',350,280,'Add_from_Src_to_Dest');\" /></td>
      </tr>
    </table>
  </td>
</tr>
<tr class='tabsubhead'><td >Amostras e Plantas Marcadas</td></tr>";
if ($bgi % 2 == 0) {$bgcolor = $linecolor2 ;} else { $bgcolor = $linecolor1 ;} $bgi++;
echo "
<tr bgcolor = '".$bgcolor."'>
  <td >
    <table>
    <tr>
   <td class='tdsmallbold'  >Especimenes&nbsp;<img height=15 src=\"icons/icon_question.gif\" ";
	$help = "Especimenes que pertencem ao Projeto";
	echo "onclick=\"javascript:alert('$help');\" /></td>
        <td ><span id='especimenestxt'>".$especimenestxt."</span></td>
        <td><input type=button style=\"color:#4E889C; font-size: 1.2em; font-weight:bold; padding: 4px; cursor:pointer;\"  value='Especimenes'  onmouseover=\"Tip('Adiciona ou Edita as Amostras no Projeto');\" ";
		$myurl = "projeto-amostras.php?projetoid=".$projetoid;
		echo " onclick = \"javascript:small_window('".$myurl."',800,500,'Amostras Projetos');\" /></td>";
echo "
      </tr>
    </table>
  </td>
</tr>
";
      
if ($bgi % 2 == 0) {$bgcolor = $linecolor2 ;} else { $bgcolor = $linecolor1 ;} $bgi++;
echo "
<tr bgcolor = '".$bgcolor."'>
  <td >
    <table>
    <tr>
   <td class='tdsmallbold' >".GetLangVar('nametaggedplant')."s&nbsp;<img height=15 src=\"icons/icon_question.gif\" ";
	$help = "Plantas que pertencem ao Projeto";
	echo "onclick=\"javascript:alert('$help');\" /></td>
        <td ><span id='plantastxt'>".$plantastxt."</span></td>
        <td><input type=button style=\"color:#4E889C; font-size: 1.2em; font-weight:bold; padding: 4px; cursor:pointer;\"  value='Plantas'  onmouseover=\"Tip('Adiciona ou Edita as Plantas do Projeto');\" ";
		$myurl = "projeto-plantas.php?projetoid=".$projetoid;
		echo " onclick = \"javascript:small_window('".$myurl."',800,500,'Amostras Projetos');\" /></td>";
echo "
      </tr>
    </table>
  </td>
</tr>";
if ($bgi % 2 == 0) {$bgcolor = $linecolor2 ;} else { $bgcolor = $linecolor1 ;} $bgi++;
echo "
<tr bgcolor = '".$bgcolor."'>
  <td >
    <table>
    <tr>
  <td align='right' class='tdsmallbold'>Formulários de variáveis de usuários
&nbsp;<img height='14' src=\"icons/icon_question.gif\" ";
	$help = 'Para cada formulário selecionado serão criadas planilhas de dados e metadados, relacionando dados das amostras com esse tipo de dados. Apenas Formulários de USO PESSOAL que não podem ser mudados por outros usuários';
	echo " onclick=\"javascript:alert('$help');\" /></td>  
  <td class='tdformnotes'>
    <select name='projformidmorfo[]'  size='10' multiple>";
	//formularios usuario
	//WHERE AddedBy=".$_SESSION['userid']." AND Shared=0 
	$qq = "SELECT * FROM Formularios ORDER BY Formularios.FormName ASC";
	$rr = mysql_query($qq,$conn);
	while ($row= mysql_fetch_assoc($rr)) {
		$seltxt = "";
	    if (count($projformidmorfo)>0) {
			$tem = in_array($row['FormID'],$projformidmorfo);
			if ($tem) { $seltxt = "selected";}
		}
		echo "
      <option  ".$seltxt." value='".$row['FormID']."'>".$row['FormName']." (".$row['AddedDate'].")</option>";
	}
echo "
    </select>
  </td>
    </tr>
  </table>
  </td>
</tr>";

if ($bgi % 2 == 0) {$bgcolor = $linecolor2 ;} else { $bgcolor = $linecolor1 ;} $bgi++;
echo "
<tr bgcolor = '".$bgcolor."'>
  <td >
    <table>
    <tr>
  <td align='right' class='tdsmallbold'>Formulário de hábitat
&nbsp;<img height='14' src=\"icons/icon_question.gif\" ";
	$help = 'Formulário contendo variáveis de hábitat relacionadas a amostras para produção de planilha de dados. Precisa ser um formulário de uso pessoal, não compartilhado';
	echo " onclick=\"javascript:alert('$help');\" /></td>  
  <td class='tdformnotes'>
    <select name='projformidhab' >";
	if (!empty($projformidhab)) {
		$qq = "SELECT * FROM Formularios WHERE FormID=".$projformidhab;
		$rr = mysql_query($qq,$conn);
		$row= mysql_fetch_assoc($rr);
		echo "
      <option selected value='".$row['FormID']."'>".$row['FormName']." (".$row['AddedDate'].")</option>";
	} else {
		echo "
      <option selected value=''>".GetLangVar('nameselect')."</option>";
	}
	//formularios usuario
	$qq = "SELECT * FROM Formularios WHERE AddedBy=".$_SESSION['userid']." AND Shared=0 ORDER BY Formularios.FormName ASC";
	$rr = mysql_query($qq,$conn);
	while ($row= mysql_fetch_assoc($rr)) {
		echo "
      <option  value='".$row['FormID']."'>".$row['FormName']." (".$row['AddedDate'].")</option>";
	}
echo "
    </select>
  </td>
  </tr>
  </table>
  </td>
</tr>";

if ($bgi % 2 == 0) {$bgcolor = $linecolor2 ;} else { $bgcolor = $linecolor1 ;} $bgi++;
echo "
<tr bgcolor = '".$bgcolor."'>
  <td >
    <table>
      <tr><td colspan='4' class='tdsmallbold' align='right'>".GetLangVar('namefinanciamento')."</td></tr>";
	for ($fin=0;$fin<=4;$fin++) {
		$ag = trim($agencia[$fin]);
		if (!empty($ag)) {$proc = trim($processo[$fin]);} else {unset($proc);}
		echo "
      <tr class='tdformnotes'>
        <td >Agencia</td>
        <td><input type='text' name='agencia[]' value='".$ag."' size='20'/></td>
        <td>Processo No.</td>
        <td ><input type='text' name='processo[]' value='".$proc."' size='15'/></td>
      </tr>";
	}
	echo "
    </table>
  </td>
</tr>";

if ($bgi % 2 == 0) {$bgcolor = $linecolor2 ;} else { $bgcolor = $linecolor1 ;} $bgi++;
echo "
<tr bgcolor = '".$bgcolor."'>
  <td >
    <table>
	  <tr>
	    <td class='tdsmallboldright'>".GetLangVar('namelogo')." ".GetLangVar('nameimagem')."</td>
	    <td align='left'>
	    <input type='hidden' name='MAX_FILE_SIZE' value='10000000' />
	    <input name='iconfile' type='file' />
	    <input type='hidden' name='logofile' value='".$logofile."' />
	    </td>";
	if (!empty($logofile)) {
		echo "
		<td class='tdformnotes'><a href='".$logofile."'><img width=\"80\" src=\"".$logofile."\"/></a></td>";
	}
	echo "
	  </tr>
    </table>
  </td>
</tr>";
if ($bgi % 2 == 0) {$bgcolor = $linecolor2 ;} else { $bgcolor = $linecolor1 ;} $bgi++;
echo "
<tr bgcolor = '".$bgcolor."'>
  <td align='center'>
    <table>
      <tr>
        <td align='center'><input type='submit' value='".GetLangVar('namesalvar')."' class='bsubmit' /></form></td>
        <td align='center' >
        <form action='projeto-form.php' method='post'><input type='hidden' value='".$ispopup."' name='ispopup' /><input type='submit' class='breset' value=".GetLangVar('namevoltar')." /></form></td>
      </tr>
    </table>
  </td>
</tr>";
if ($bgi % 2 == 0) {$bgcolor = $linecolor2 ;} else { $bgcolor = $linecolor1 ;} $bgi++;
$minwidth = '350px';
echo "
<tr bgcolor = '".$bgcolor."'>
  <td align='center'>
<a style=\"width: ".$minwidth .";\" href=\"#\" class=\"menuicons_cinza\" onclick = \"javascript:small_window('projeto-dados-metadados-save.php?projetoid=".$projetoid."',800,400,'Dados e metadados de projetos');\">Visualiza dados e metadados</a>
  </td>
</tr>";
} //else if !empty($pessoaid)
echo "
</tbody>
</table>";

$which_java = array("<script type='text/javascript' src='javascript/myjavascripts.js'></script>");
FazFooter($which_java,$calendar=FALSE,$footer=$menu);

?>