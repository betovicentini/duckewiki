<?php

# Example2.php

require_once( 'PogProgressBar.php' );

// start objects
$objBar0 = new PogProgressBar( 'pb0' );
$objBar1 = new PogProgressBar( 'pb1' );
$objBar2 = new PogProgressBar( 'pb2' );
$objBar3 = new PogProgressBar( 'pb3' );

// set themes
$objBar1->setTheme( 'blue' );
$objBar2->setTheme( 'green' );
$objBar3->setTheme( 'red' );

?>
<html>
	<head>
		<title>PogProgressBar - Example 2</title>
	</head>
	<body>
		<table align="center" cellpadding="0" cellspacing="20" border="0">
			<tr><td>PogProgressBar - Example 2</td></tr>
			<tr><td>All progress</td></tr>
			<tr><td><? $objBar0->draw(); ?></td></tr>
			<tr><td>Bar 1</td></tr>
			<tr><td><? $objBar1->draw(); ?></td></tr>
			<tr><td>Bar 2</td></tr>
			<tr><td><? $objBar2->draw(); ?></td></tr>
			<tr><td>Bar 3</td></tr>
			<tr><td><? $objBar3->draw(); ?></td></tr>
		</table>
	</body>
<html>
<?php

// time for each
$intMax1 = 300;
$intMax2 = 250;
$intMax3 = 700;

$intCount = 0;
while ( $objBar0->getProgress() != 100 )
{
	$objBar1->setProgress( $intCount * 100 / $intMax1 );
	if ( $objBar1->getProgress() == 100 )
	{
		$objBar2->setProgress( ( $intCount - $intMax1 ) * 100 / $intMax2 );
	}
	$objBar3->setProgress( $intCount * 100 / $intMax3 );
	$objBar0->setProgress(
		(
			( $objBar1->getProgress() * $intMax1 ) +
			( $objBar2->getProgress() * $intMax2 ) +
			( $objBar3->getProgress() * $intMax3 )
		) / ( $intMax1 + $intMax2 + $intMax3 )
	);
	++$intCount;
	usleep( 100 );
}

?>