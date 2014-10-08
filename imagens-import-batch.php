<?php
//Start session
//ini_set("memory_limit","10000M");
session_start();
//Check whether the session variable
include "functions/HeaderFooter.php";
include "functions/MyPhpFunctions.php";

$lang = $_SESSION['lang'];
$dbname = $_SESSION['dbname'];
$conn = ConectaDB($dbname);
$uuid = cleanQuery($_SESSION['userid'],$conn);
if(!isset($uuid) ||  (trim($uuid)=='')) { header("location: access-denied.php"); exit(); } 

$ppost = cleangetpost($_POST,$conn);
$arval = $ppost;
@extract($ppost);
$gget = cleangetpost($_GET,$conn);
@extract($gget);
//CABECALHO
$ispopup=1;
if ($ispopup==1) {
	$menu = FALSE;
} else {
	$menu = TRUE;
}
$which_css = array(
"<link href='css/geral.css' rel='stylesheet' type='text/css' />",
"<link rel='stylesheet' type='text/css' media='screen' href='css/autosuggest.css' />",
"<link rel='stylesheet' type='text/css' media='screen' href='javascript/tabber/tabber.css' />",
"<link rel='stylesheet' type='text/css' href='javascript/fileuploader.css' />"
);
$which_java = array(
"<script type='text/javascript' src='javascript/jquery-latest.js'></script>",
"<script type='text/javascript'>$(document).ready(function(){ $('.toggle_container').hide(); $('h2.trigger').click(function(){ $(this).toggleClass('active').next().slideToggle('slow'); }); });</script>"
);

$title ='Importar várias imagens';
$body='';
FazHeader($title,$body,$which_css,$which_java,$menu);

unset($_SESSION['plantasidsimgs']);
unset($_SESSION['especimenesids']);
unset($_SESSION['imgpost']);
echo "
<br />
<br />
<h2 class='trigger'><a href='#'>Arquivos para importar:</a></h2>
<div class='toggle_container'>
  <div class='block'>
  <table>
    <tr>
      <td>
        <div id='file-uploader'>
          <noscript>
            Por favor  habilite o JavaScript para subir arquivos
            <!-- or put a simple form for upload here -->
          </noscript>
        </div>
      </td>
    </tr>
  </table>
  </div>
</div>
<script src='javascript/fileuploader.js' type='text/javascript'></script>
<script>
    function createUploader(){
      var uploader = new qq.FileUploader({
        element: document.getElementById('file-uploader'),
        action: 'imagesupload-doit.php',
        debug: true});
    }
    window.onload = createUploader; 
</script>";

echo "
<form action='imagens-import-batch-form1.php' method='post' name='autorform'>
<input type='hidden' name='ispopup' value='".$ispopup."' />
<h2 class='trigger'><a href='#'>Opções:</a></h2>
<div class='toggle_container'>
  <div class='block'>
    <table cellpadding='7' >";
if ($bgi % 2 == 0){$bgcolor = $linecolor2 ;} else {$bgcolor = $linecolor1 ;} $bgi++;
echo "
<tr bgcolor = '".$bgcolor."'>
  <td>
    <table>
      <tr>
        <td class='tdsmallbold' style='color: #990000;'>".GetLangVar('traittolinkto')."*&nbsp;&nbsp;<img height=14 src=\"icons/icon_question.gif\"";
        $help = GetLangVar('traittolinkto_help');
        echo " onclick=\"javascript:alert('$help');\"></td>
        <td>
          <select name='traitid'>
            <option value=''>".GetLangVar('nameselect')."</option>";
            $filtro ="SELECT * FROM Traits WHERE TraitTipo='Variavel|Imagem' ORDER BY TraitName";
            $resaa = mysql_query($filtro,$conn);
            while ($aa = mysql_fetch_assoc($resaa)){
              if (!empty($aa['TraitName'])) {
                $PathName = $aa['PathName'];
                $level = $aa['MenuLevel'];
                $tipo = $aa['TraitTipo'];
                echo "
            <option value='".$aa['TraitID']."'>".$aa['TraitName']."</option>";
                }
            }
    echo "
          </select>
        </td>
      </tr>
    </table>
  </td>
</tr>";    
if ($bgi % 2 == 0){$bgcolor = $linecolor2 ;} else {$bgcolor = $linecolor1 ;} $bgi++;
echo "
    <tr bgcolor = '".$bgcolor."'>
      <td >
        <table>
          <tr>
            <td class='tdsmallboldright'>".GetLangVar('namefotografo')."s</td>
            <td><textarea type='text' name='addcoltxt' value='".$addcoltxt."' readonly></textarea></td>
            <td>
            <input type='hidden' name='addcolvalue' value='$addcolvalue' />
            <input type=button value=\"".GetLangVar('nameselect')."\" class='bsubmit' ";
            $myurl ="addcollpopup.php?getaddcollids=$addcolvalue&formname=autorform"; 
            echo " onclick = \"javascript:small_window('$myurl',400,400,'Selecione Pessoas');\" /></td>
          </tr>
        </table>
      </td>
    </tr>";
//if ($bgi % 2 == 0){$bgcolor = $linecolor2 ;} else {$bgcolor = $linecolor1 ;} $bgi++;
echo "
<!---
<tr bgcolor = '".$bgcolor."'>
  <td >
    <input type='hidden' name='addcolvalue' value='".$addcolvalue."' />
    <table>
      <tr>
        <td class='tdsmallboldright'>Camera ".GetLangVar('namenome')."</td>
        <td align='left'>
          <select name='camera' >
            <option value=''>".GetLangVar('nameselect')."</option>";
			$qq = "SELECT * FROM Equipamentos WHERE Type='camera' OR Type='scanner' ORDER BY Type,Name ASC";
			$res = mysql_query($qq,$conn);
			$tipo = ' ';
			while ($row =  mysql_fetch_assoc($res)) {
			if ($tipo!=strtoupper($row['Type']."s")) {
				$tipo =strtoupper($row['Type']."s");
				echo "
            <option value='' class='redtext' >$tipo</option>";
			}
			echo "
            <option value='".$row['EquipamentoID']."' >".$row['Name']."</option>";
		}
echo "
          </select>
        </td>
      </tr>
    </table>
  </td>
</tr> 
--->
</table>
</div>
</div>";
echo "
<h2 class='trigger'><a href='#'>Relacionar com:</a></h2>
<div class='toggle_container'>
<div class='block'>
<table  cellpadding='7' >";
if ($bgi % 2 == 0){$bgcolor = $linecolor2 ;} else {$bgcolor = $linecolor1 ;} $bgi++;
echo "
<tr bgcolor = '".$bgcolor."'>
  <td >
    <table>
      <tr>
        <td class='tdsmallbold' style='color: #990000;'>Relacionar A POSTERIORI&nbsp;&nbsp;<img height=14 src=\"icons/icon_question.gif\"";
        $help ='Selecione esta opção para importar imagens sem relacionar com plantas, especimenes ou localidades.\nVocê poderá DEPOIS relacionar as imagens importadas com:\n\tespecímenes\n\tplantas marcadas \n\tlocalidades\n\n, filtrando dados pela data de criação da imagem.\nUSE ESSA OPÇÃO APENAS SE SOUBER O QUE ESTÁ FAZENDO';
        echo " onclick=\"javascript:alert('".$help."');\"></td>
        <td class='tdformnotes'>
          <input type='checkbox' name='linkposterior' value=1>&nbsp;Importar sem relação com registros
        </td>
      </tr>
    </table>
  </td>
</tr>";  
//if ($bgi % 2 == 0){$bgcolor = $linecolor2 ;} else {$bgcolor = $linecolor1 ;} $bgi++;
echo "
<!---
<tr bgcolor = '".$bgcolor."'>
  <td >
    <table align='left'>
      <tr>
        <td class='tdsmallboldleft' style='color: #990000;'>".GetLangVar('namegeoreferenciar')."&nbsp;<img height=15 src=\"icons/icon_question.gif\"";
			$help = GetLangVar('georeferenceimage_help');
			echo " onclick=\"javascript:alert('$help');\" /></td>
        <td class='tdformnotes'>GPS ".GetLangVar('namenome')."&nbsp;&nbsp;&nbsp;</td>
        <td>
          <select name='gpsunit'>
            <option value=''>".GetLangVar('nameselect')."</option>";
            $qq = "SELECT * FROM Equipamentos WHERE Type='gps' ORDER BY Name ASC";
            $res = mysql_query($qq,$conn);
            while ($row =  mysql_fetch_assoc($res)) {
                echo "
            <option value='".$row['EquipamentoID']."' >".$row['Name']."</option>";
            }
        echo "
          </select>
        </td>
        <td class='tdformnotes'>".GetLangVar('nametolerancia')."&nbsp;<img height=15 src=\"icons/icon_question.gif\"";
        $help = GetLangVar('tolerancia_help');
        if (empty($tolerancia)) { $tolerancia=60;} //default tolerancia em segundos
        echo  " onclick=\"javascript:alert('$help');\"></td>
        <td><input name='tolerancia' value=$tolerancia size='2' />&nbsp;".GetLangVar('namesegundo')."</td>
      </tr>
    </table>
  </td>
</tr>
--->
";
if ($bgi % 2 == 0){$bgcolor = $linecolor2 ;} else { $bgcolor = $linecolor1 ;} $bgi++;
echo "
<tr bgcolor = '".$bgcolor."'>
  <td >
    <table cellpadding='4'>
      <tr>
        <td class='tdsmallbold' style='color: #990000;'>".GetLangVar('nameamostra')."s&nbsp;".strtolower(GetLangVar('namecoletada'))."s&nbsp;<img height=15 src=\"icons/icon_question.gif\"";
        $help = "Relacione as imagens com amostras coletadas (especímenes), a partir de um padrão de nome dos arquivos que inclua coletor e número";
        echo " onclick=\"javascript:alert('$help');\" />&nbsp;&nbsp;</td>
        <td class='tdsmallbold'>SEP=</td>
        <td><input type='text' size='4' name='fnpattern_sep' value='".$fnpattern_sep."' /><img height=15 src=\"icons/icon_question.gif\"";
        $help = "Qual o separador das informações no nome do arquivo?";
        echo " onclick=\"javascript:alert('$help');\" /></td>
        <td>
          <table class='dettable'>
            <tr><td class='tdsmallbold' colspan='2'>".GetLangVar('namepadrao')."</td></tr>
            <tr>
              <td ><input type='radio' name='fnpattern' value='1' /></td>
              <td><b>Coletor+SEP+Numero+SEP+qualqueroutracoisa.ext</b> [por exe: <i>Carvalho_1034_qualquer_coisa_se_for_o_caso.jpg</i>; ou coletor pode ser abreviado: Carv_1034_blabla.jpg]</td>
            </tr>
            <tr>
              <td><input type='radio' name='fnpattern' value='2'></td>
              <td class='tdformnotes'><b>INICIAIS_Coletor+SEP+Numero+qualqueroutracoisa.ext</b> [por ex:  <i>FAC_1034_DSC0667.jpg</i> para uma imagem de coleta de Fernanda Antunes Carvalho]</td>
            </tr>
            <tr>
              <td><input type='radio' name='fnpattern' value='3'></td>
              <td class='tdformnotes'><b>WikiEspecimenID_qualqueroutracoisa.ext</b> [por ex:  <i>123598_DSC0667.jpg</i> para uma imagem de uma coleta cujo identificador na base seja 123598]</td>
            </tr>
          </table>
        </td>
      </tr>
    </table>
  </td>
</tr>";
if ($bgi % 2 == 0){$bgcolor = $linecolor2 ;} else {$bgcolor = $linecolor1 ;} $bgi++;
echo "
<tr bgcolor = '".$bgcolor."'>
  <td>
    <table cellpadding='7'>
      <tr>
        <td class='tdsmallbold' style='color: #990000;'>".GetLangVar('nameplanta')."s&nbsp;".strtolower(GetLangVar('namemarcada'))."s&nbsp;<img height=15 src=\"icons/icon_question.gif\"";
        $help = "Relacione as imagens com plantas marcadas, tendo o número da placa da planta (TAG), ou o Identificador da planta na base (WikiPlantaID) como parte do nome dos arquivos.";
        echo " onclick=\"javascript:alert('$help');\" />&nbsp;&nbsp;&nbsp;</td>
        <td class='tdsmallbold'>SEP=</td><td><input type='text' size='4' name='fnpattern_seppl' value='".$fnpattern_seppl."' /><img height=15 src=\"icons/icon_question.gif\"";
        $help = "Qual o separador das informações no nome do arquivo?";
        echo " onclick=\"javascript:alert('$help');\" /></td>
        <td>
          <table class='dettable'>
            <tr><td class='tdsmallbold' colspan='2'>".GetLangVar('namepadrao')."</td></tr>
            <tr>
              <td ><input type='radio' name='fnpattern_pl' value='1' /></td>
              <td class='tdformnotes'><b>TAG+SEP+qualqueroutracoisa.ext</b> [por ex: <i>1026_qualqueroutracoisa.jpg</i>, onde TAG é o número da árvore. Precisa selecionar um filtro de localidade onde o número das árvores é único]</td>
            </tr>
            <tr>
              <td class='tdsmallbold'>".GetLangVar('namefiltro')."</td>
              <td>
                <select name='filtro'>";
					echo "
                  <option selected value=''>".GetLangVar('nameselect')."</option>";
					$qq = "SELECT * FROM Filtros WHERE (AddedBy=".$_SESSION['userid']." OR Shared=1) AND PlantasIDS IS NOT NULL AND PlantasIDS<>'' ORDER BY FiltroName";
					$res = mysql_query($qq,$conn);
					while ($rr = mysql_fetch_assoc($res)) {
						echo "
                  <option value='".$rr['FiltroID']."'>".$rr['FiltroName']."</option>";
					}
					echo "
                </select>
              </td>
            </tr>
              <tr>
              <td ><input type='radio' name='fnpattern_pl' value='2' /></td>
              <td class='tdformnotes'><b>WikiPlantaID+SEP+qualqueroutracoisa.ext</b> [por ex: <i>1026_qualqueroutracoisa.jpg</i>, onde WikiPlantaID é o identificador da árvore na base de dados. Não precisa selecionar um filtro!</td>
            </tr>
          </table>
        </td>
      </tr>
    </table>
  </td>
</tr>
</table>
</div>
</div>
<table style='position: relative'  align='center'>
<tr>
  <td align='center'>
    <input type='hidden' name='final' value='' />
    <input type='submit' value='".GetLangVar('nameimportar')."' class='bsubmit' onclick=\"javascript:document.autorform.final.value=1\" />
  </td>
</tr>
</table>
</form>
";
$which_java = array("<script type='text/javascript' src='javascript/myjavascripts.js'></script>");
FazFooter($which_java,$calendar=FALSE,$footer=$menu);
?>