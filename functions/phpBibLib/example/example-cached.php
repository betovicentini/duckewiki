<?
$Path['lib'] = './../lib/';
require_once $Path['lib'] . 'lib_bibtex.inc.php';

$Site = array();

if(!file_exists('./../dbib.sqlite'))
	copy('./../empty-dbib.sqlite', './dbib.sqlite');
$bdb = new PDO('sqlite:./dbib.sqlite');
$Site['bibtex'] = new DBibtex($bdb, 'references.bib');
$bb = $Site['bibtex'];
?>
<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Transitional//EN"
    "http://www.w3.org/TR/xhtml1/DTD/xhtml1-transitional.dtd">
<html xmlns="http://www.w3.org/1999/xhtml" xml:lang="en" lang="en">
	<head>
		<meta http-equiv="Content-Type" content="text/html; charset=UTF-8" />
		<link rel="stylesheet" type="text/css" href="../bibtex.css" />
		<link rel="stylesheet" type="text/css" href="../docs.css" />
		<title>Example usage of cached version of phpBibLib</title>
	</head>
	<body>
		<div id="content">
		<h2>Using phpBibLib, cached</h2>
		<p>
			The library can be used directly on .bib files, or cached using a (SQLite) database. 
			This file is an example of the <i>cached</i> version. 
			Go <a href="example-static.php">here</a> to see the demonstration of the static, non DB cached, version.
		</p>
		<p>The basic outline is the same for both scenarios. First, one instantiates a Bibtex object and tells it where to obtain the references from, in this case the 'refs.bib' and 'references.bib' files.<br />
			 Note that currently the `cite` and `citet` functions can both access a Bibtex object either as their first argument, or as default case, through the global $Site variable.
		</p>
		<pre>$Path['lib'] = './lib/';
require_once $Path['lib'] . 'lib_bibtex.inc.php';

$bib = new Bibtex('refs', 'references.bib');
       
$Site['bibtex'] = $bib; // if we want to use cite([Bibtex $bib], <key1>, 
                        //  <key2>, ..), without specifying what bib-object 
                        //  to cite from as it's first argument, store the
                        //  object in $Site['bibtex'].</pre>
		<p>
			The above Bibtex object parses the provided bibtex files on every run; 
			fine for a small site using small bibtex files, like a personal list of 
			your publications, but inefficient nevertheless.<br/>
			Alternatively, one can use a database cached version of the	library,
			which only re-parses the provided bibtex files if their date of modification
			do not match with data in the database. Although the code uses fairly 
			standard SQL and the PDO interface, it has only been tested using SQLite.<br />
			Analogously to above, one can create a Database Cached version of a Bibtex object as follows:
		</p>
		<pre>$bdb = new PDO('sqlite:./dbib.sqlite');             // get a PDO for a sqlite database,
                                                    //  see below for the schema
$bib = new DBibtex($bdb, 'refs', 'references.bib'); // create the actual object. 
                                                    //  if no .bib's are given, only 
                                                    //  data in database is used, no 
                                                    //  attempts to reparse are made</pre>
    <p>
    	Where the scheme for the database is:	
    </p>
    <pre>CREATE TABLE bibmeta (fname TEXT, fdate TEXT);
CREATE TABLE bibdata (type TEXT, key TEXT, author TEXT, title TEXT, 
  publisher TEXT, year TEXT, booktitle TEXT, editor TEXT, journal TEXT, 
  volume TEXT, number TEXT, note TEXT, implementationurl TEXT, paperurl 
  TEXT, tags TEXT);</pre>
		<p>
			Once we have the Bibtex object, we can use it in two ways, i.e.,
			<ul>
				<li>by the standard LaTeX way of sprinkling some text with cite- and citet-functions, or,</li>
				<li>by querying it directly, setting conditions on bibtex fields,</li>
			</ul>
			after which we can (optionally) pretty-print the resulting bibliography afterwards. Note that these two ways of selecting bibliogaphic entries can be combined. 
			That is, under the hood, both methods simply 'activate' the selected/cited references, and 
			upon printing the list, all 'activated' publications are printed.<br />
			We support all sorts of ordering of the bibliography; not however that if one uses in-text cites, and one requires something else than usage-based order, a pre-scan of the content containing the cites is necessary.			
		</p>
    <p>
    	As possible use cases we see
    	<ul>
    		<li>easily maintainable, dynamic, bibtex file driven, personal (e.g. <a href="http://www.patternsthatmatter.org" alt="website of Dr. Matthijs van Leeuwen">here</a>) or research group publication listing websites.</li>
    		<li>neat scientifically cited overviews, such as for tutorials, e.g. on <a href="http://www.usefulpatterns.org/msop/">Mining Sets of Patterns</a></li>
    		<li>all the other, endless, possible uses one can have of having the data in some bibtex files readily available in php.</li>
    	</ul>
    </p>
		<p>
			As I'm particularly bad at writing help-files, I'll just give a live demonstration of the cached, DB version of the library below. 
			You can find the live demonstration of the static, non-DB cached version <a href="example-static.php">here</a>.
			(Note that in the download package you'll find this demo included separately for the two variants.)
			The library has a (growing) number of undocumented features, so don't be surprised if it can already do what you want, although it is not specified here.
		</p>

		<h2>Usage Examples - Cached</h2>
		<p>First, one instantiates a Bibtex object and tells it where to obtain the references from, in this case the 'refs.bib' and 'references.bib' files.
			 Note that currently the `cite` and `citet` functions can both access a Bibtex object either as their first argument, or as default case, through the global $Site variable.
		</p>
		<pre>$bdb = new PDO('sqlite:./dbib.sqlite');             // get a PDO for a sqlite database,
                                                    //  see below for the schema
$bib = new DBibtex($bdb, 'refs', 'references.bib'); // create the actual object. 
                                                    //  if no .bib's are given, only 
                                                    //  data in database is used, no 
                                                    //  attempts to reparse are made
$Site['bibtex'] = $bib;</pre>
		<p>
			We can use the library in two ways, i.e., 1) by querying it directly, and printing the resulting list of publications, or, 
			2) by the standard LaTeX way of sprinkling some text with cite- and citet-functions, and (optionally) printing the list of referenced publications.
			For printing the list, these two can be combined. That is, under the hood, both methods simply 'activate' the selected/cited references, and 
			upon printing the list, all 'activated' publications are printed.
		</p>
		<p>Let us start by considering some examples of the 2nd method. For these examples, we consider the following text which we will store in a file called <i>example-content.inc.php</i>.
		</p>
<pre>This and that has long been known to be such and so &lt;?cite('vreeken15', 'siebes06', 
'DBLP:journals/tkde/MiettinenMGDM08');?>. Furthermore, &lt;?citet('agrawal93')?> 
clearly did not &lt;?cite($bib, 'agrawal93')?>.</pre>
		<p>
			This example includes two calls to the basic <i>cite</i> function, requesting two references present in the provided database, and one missing; and one call to the <i>citet</i> function.
			The former function displays a (list of) citations in the currently selected style, whereas the latter automatically prints the names of the authors of the reference and then adds its citation.
			Both functions use, if it is an object the first argument as the Bibtex library, otherwise they fall back to the '$Site['bibtex']' object.
		</p>
		<h3>Basic citing, without prescanning. Numeric references, usage-ordered.</h3>
		<p>In its most basic set-up, we can use the below code</p>
		<pre>$Site['bibtex']->SetBibliographyStyle('numeric'); // not necessary here, is the default
$Site['bibtex']->SetBibliographyOrder('usage');   // not necessary here, is the default
include 'example-content.inc.php';
$Site['bibtex']->PrintBibliography();</pre>
		<p>
			to obtain a numbered bibliography, and to sort the bibliography (and hence, deal the numbers in the order of) based on the order in which they are used in the content, resulting in
		</p>
		<div class="example-output">
<?
$Site['bibtex']->ResetBibliography();
$Site['bibtex']->SetBibliographyStyle('numeric');
$Site['bibtex']->SetBibliographyOrder('usage');
include 'example-content.inc.php';
echo '<br /><br/>';
$Site['bibtex']->PrintBibliography();
?>
		</div>
		<h3>Basic citing, without prescanning. Abbrv references, usage-ordered.</h3>
		<p>Alternatively, we can use the 'abbrv' citation style instead of 'numeric' in the above example, i.e., by using</p>
		<pre>$Site['bibtex']->SetBibliographyStyle('abbrv');</pre>
		<p>which will make the library spit out citations-keys of the first characters of the last name, up till the first last name starting with a capital, of up to the first three authors, adding a '+' if there are more authors, and the year in two digits. This gives us the following output:</p>
		<div class="example-output">
<?
$Site['bibtex']->ResetBibliography();
$Site['bibtex']->SetBibliographyStyle('abbrv');
$Site['bibtex']->SetBibliographyOrder('usage');
include 'example-content.inc.php';
echo '<br /><br />';
$Site['bibtex']->PrintBibliography();
?>
		</div>
		<h3>Basic citing, without prescanning. Natbib references, usage-ordered</h3>
		<p>
			Third, we have the 'natbib' option
		</p>
			<pre>$Site['bibtex']->SetBibliographyStyle('abbrv');</pre>
		<p>
			This option gives us the names of the authors, if there are up to two, or write the last name of the first author and adds 'et al.', and also provides the year.
		</p>
		<div class="example-output">
<?
$Site['bibtex']->ResetBibliography();
$Site['bibtex']->SetBibliographyStyle('natbib');
$Site['bibtex']->SetBibliographyOrder('usage');
include 'example-content.inc.php';
echo '<br /><br />';
$Site['bibtex']->PrintBibliography();
?>
	</div>
	<h3>With prescanning. Numeric references, alphabetic-ordered</h3>
	<p>
		In practice, usage-based ordering might not be what we want. Instead, we might want to have the printed bibliography sorted alphabetically, or by year of publication. 
		This means that <i>cite</i> cannot just deal number on the fly, and hence we are required to scan the content file before printing the citations. This correlates with the well-known LaTeX compile .tex - compile .bib - compile .tex loop. 
		In our code, to have this pre-scanning, we have to replace the simple 'include' with
	</p>
	<pre>$Site['bibtex']->IncludeBibContent('example-content.inc.php');</pre>
	<p>
		which gives us the freedom using more fancy order styles, i.e. we have the options 'usage', 'alphabetic', 'year_a' and 'year_d' (where a stands for ascending and d for descending).
		Now, the following code
	</p>
	<pre>$Site['bibtex']->SetBibliographyStyle('numeric');
$Site['bibtex']->SetBibliographyOrder('alphabetic');
$Site['bibtex']->IncludeBibContent('example-content.inc.php', '$bib'); // where '$bib' is
$Site['bibtex']->PrintBibliography();                                  // optional if you 
                                                                       // use $Site['bibtex']</pre>
    <p>results in</p>
		<div class="example-output">
<?
$Site['bibtex']->ResetBibliography();
$Site['bibtex']->SetBibliographyStyle('numeric');
$Site['bibtex']->SetBibliographyOrder('alphabetic');
$Site['bibtex']->IncludeBibContent('example-content.inc.php', '$bb');
echo '<br /><Br/>';
$Site['bibtex']->PrintBibliography();
?>
		</div>
		<h3>With prescanning. Numeric references, year-asc ordered.</h3>
		<p>And, as an example of using 'year_a' as option for the bibliography order,</p>
		<pre>$Site['bibtex']->SetBibliographyOrder('year_a');</pre>
		<p>we get</p>
		<div class="example-output">
<?
$Site['bibtex']->ResetBibliography();
$Site['bibtex']->SetBibliographyStyle('numeric');
$Site['bibtex']->SetBibliographyOrder('year_a');
$Site['bibtex']->IncludeBibContent('example-content.inc.php', '$bb');
echo '<br /><Br/>';
$Site['bibtex']->PrintBibliography();

$author = 'Vreeken';
?>
		</div>
		<h3>Showing results of queries</h3>
		<p>As a final example (for now) consider the following code</p>
		<pre>$Site['bibtex']->SetBibliographyStyle('numeric');
$Site['bibtex']->SetBibliographyOrder('year_d');
$Site['bibtex']->Select(array('author' => '<?=$author?>'));
$Site['bibtex']->PrintBibliography();</pre>
    <p>which neatly gives us all publications in the above-mentioned bib-files that have '<?=$author?>' in the author field.</p>
		<div class="example-output">
<?
$Site['bibtex']->ResetBibliography();
$Site['bibtex']->SetBibliographyStyle('numeric');
$Site['bibtex']->SetBibliographyOrder(array('year' => 'd', 'author' => 'a', 'title' => 'a'));
$Site['bibtex']->Select(array('author' => $author));
$Site['bibtex']->PrintBibliography();
$author2 = 'ke';
$year = 2011;
?>
		</div>
  	<p>
    	Note that alternatively, we could have selected on publications of a particular year, using
    	<pre>$Site['bibtex']->Select('author' => '<?=$author2;?>', 'year' => 2011)';</pre>
    	e.g. for publications in <?=$year;?> of authors whose name include '<?=$author2;?>'.
    </p>
		<div class="example-output">
<?
$Site['bibtex']->ResetBibliography();
$Site['bibtex']->SetBibliographyStyle('numeric');
$Site['bibtex']->SetBibliographyOrder('year_d');
$Site['bibtex']->Select(array('author' => $author2, 'year' => $year));
$Site['bibtex']->PrintBibliography();
?>
		</div>
    <p>
    	More options might become (or already are) available, but simply not documented. Check the code. 
    </p>
    <p>
    	As a final remark, the usage-list of the current Bibtex object can be reset using
    	<pre>$Site['bibtex']->ResetBibliography();</pre>
    	which allows us to have different bibliography print-outs within the same document. w00t.
    </p>
    <p>That's all for now. Go on, <a href="../index.html#download">play</a>.</p>
		<div id="footer">
			<hr />
			<a href="index.html">home</a> &nbsp;&middot;&nbsp; &copy; <a href="http://www.adrem.ua.ac.be/~jvreeken/">Jilles Vreeken</a>
		</div>
		</div>
	</body>
</head>
