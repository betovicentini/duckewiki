<?php
//Start session
ini_set("memory_limit","10000M");
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
//"<link rel='stylesheet' type='text/css' href='css/cssmenu.css' />",
"<link rel='stylesheet' type='text/css' media='screen' href='css/autosuggest.css' />",
"<link rel='stylesheet' type='text/css' media='screen' href='javascript/tabber/tabber.css' />",
"<link rel='stylesheet' type='text/css' href='javascript/fileuploader.css' />"
);
$which_java = array(
//"<script type='text/javascript' src='css/cssmenuCore.js'></script>",
//"<script type='text/javascript' src='css/cssmenuAddOns.js'></script>",
//"<script type='text/javascript' src='css/cssmenuAddOnsItemBullet.js'></script>",
"<script type='text/javascript' src='javascript/jquery-latest.js'></script>",
"<script type='text/javascript'> $(document).ready(function(){ $('.toggle_container').hide(); $('h2.trigger').click(function(){ $(this).toggleClass('active').next().slideToggle('slow'); }); });</script>");
$title ='Importar NIR spectra';
$body='';
FazHeader($title,$body,$which_css,$which_java,$menu);
$tbn ="uploads/nir/temp_".$uuuuserid;

echo "
<br />
 <br />
<table class='myformtable' align='center' cellpadding=\"5\" width='80%'>
<thead>
<tr >
<td colspan='2'>Importar NIR-Spectra dos arquivos exportados</td></tr>
</thead>
<tbody>";
if ($bgi % 2 == 0){$bgcolor = $linecolor2 ;} else {$bgcolor = $linecolor1 ;} $bgi++;
echo "
<tr bgcolor = '".$bgcolor."'>
      <td colspan='2'>
        <div id='file-uploader'>
          <noscript>
            Por favor  habilite o JavaScript para subir arquivos
            <!-- or put a simple form for upload here -->
          </noscript>
        </div>
      </td>
</tr>";
echo "
<script src='javascript/fileuploader.js' type='text/javascript'></script>
<script>
    function createUploader(){
      var uploader = new qq.FileUploader({
        element: document.getElementById('file-uploader'),
        action: 'import-nir-upload-doit.php',
        debug: true});
    }
    window.onload = createUploader; 
</script>";
if ($bgi % 2 == 0){$bgcolor = $linecolor2 ;} else {$bgcolor = $linecolor1 ;} $bgi++;
echo "
<tr bgcolor = '".$bgcolor."'>
  <td align='center' class='tdformnotes'>
  Os arquivos devem ter duas colunas: Números de Onda e Absorbância, separados por vírgula; não deve ter cabeçalho na primeira linha; mesmo formato exportado pelo ANTARIS
  <br>
  <br>
  O nome do arquivo deve ter a informação para ligar à base, com a seguinte lógica:
  WIKIID_IDENTIFICADOR_FOLHA_FACE_LEITURA  <br>
  ou  <br>
  IDENTIFICADOR_FOLHA_FACE  (se nao tiver o wikiid; leitura também é opcional)<br>
    <br>
  O identificador deve ser COLETOR-NUMERO  ou TAGNUM se for uma planta marcada!
  </td>
</tr>";
if ($bgi % 2 == 0){$bgcolor = $linecolor2 ;} else {$bgcolor = $linecolor1 ;} $bgi++;
echo "
<tr bgcolor = '".$bgcolor."'>
  <td align='center'>
   <form action='import-nir-exec.php' method='post' name='autorform'>
     <input type='hidden' name='ispopup' value='".$ispopup."' />
     <input type='hidden' name='final' value='' />
     <input type='submit' style='cursor: pointer' value='".GetLangVar('nameimportar')."' class='bsubmit' onclick=\"javascript:document.autorform.final.value=1\" />
    </form>
  </td>
</tr>
</tbody>
</table>
";
$which_java = array("<script type='text/javascript' src='javascript/myjavascripts.js'></script>"
//,"<!-- Create Menu Settings: (Menu ID, Is Vertical, Show Timer, Hide Timer, On Click ('all' or 'lev2'), Right to Left, Horizontal Subs, Flush Left, Flush Top) -->",
//"<script type='text/javascript'>qm_create(0,false,0,500,false,false,false,false,false);</script>"
);
FazFooter($which_java,$calendar=FALSE,$footer=$menu);
?>