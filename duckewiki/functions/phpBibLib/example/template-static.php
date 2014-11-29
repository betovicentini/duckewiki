<?
$Path['lib'] = './../lib/';
require_once $Path['lib'] . 'lib_bibtex.inc.php';

$Site = array();

// static - use this to re-parse the .bib file every page view
$Site['bibtex'] = new Bibtex('references.bib');
$bb = $Site['bibtex'];

// open the page
?>
<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Transitional//EN"
    "http://www.w3.org/TR/xhtml1/DTD/xhtml1-transitional.dtd">
<html xmlns="http://www.w3.org/1999/xhtml" xml:lang="en" lang="en">
	<head>
		<meta http-equiv="Content-Type" content="text/html; charset=UTF-8" />
		<link rel="stylesheet" type="text/css" href="../docs.css" />
		<link rel="stylesheet" type="text/css" href="../bibtex.css" />
		<title>phpBibLib template, static</title>
	</head>
	<body>
		<div id="content">
		<h2>Basic usage template for phpBibLib, static/non-cached version</h2>
		<p>
			Some text that includes references, like:  
       This and that has long been known to be such and so <?cite('vreeken15', 'siebes06', 
       'DBLP:journals/tkde/MiettinenMGDM08');?>. Furthermore, <?citet('agrawal93')?> 
       clearly did not <?cite($bb, 'agrawal93')?>.
     </p>
<?
// Example 1, citation use

// 1) use this to print all currently cited and selected bibliography entries
 //$Site['bibtex']->PrintBibliography();
// 2) or use this to print only the cited papers, without printing any otherwise selected papers
 //$Site['bibtex']->PrintBibliographyCitedOnly(); 

// - use this to then reset the list of cited and selected entries
 //$Site['bibtex']->ResetBibliography();

// Example 2, selection use
$year = 2011;
$author2 = 'ke';
$Site['bibtex']->SetBibliographyStyle('numeric');
// !) orders other than 'usage' => a' make only sense when caching the content in which citations are used
// !!! so, make sure the cited refs do not get re-ordered !!!
//$Site['bibtex']->SetBibliographyOrder(array('year' => 'd', 'author' => 'a', 'title' => 'a'));
//$Site['bibtex']->Select(	array(	'tags' => array('AND', 'condensed', 'patterns' ) )	);
$Site['bibtex']->Select(array('author' => $author2, 'year' => $year));	

// 1) use this to print all currently selected bibliography entries
 //$Site['bibtex']->PrintBibliography();
// 2) or use this to print only the papers selected above, without (re-)printing the cited papers, without having to reset the bibliography
 //$Site['bibtex']->PrintBibliographySelectedOnly(); 
	
?>

<h2 class="refs">References</h2>
<? 
	// Print the papers cited above
	$Site['bibtex']->PrintBibliographyCitedOnly(); 
?>
<h2 class="refs">
	Further selected references
</h2>
<?
	// Print the papers selected above, without re-printing the cited papers
	$Site['bibtex']->PrintBibliographySelectedOnly(); 
?>
		<div id="footer">
			<hr />
			<a href="../index.html">home</a> &nbsp;&middot;&nbsp; &copy; <a href="http://www.adrem.ua.ac.be/~jvreeken/">Jilles Vreeken</a>
		</div>
		</div>
	</body>
</head>
