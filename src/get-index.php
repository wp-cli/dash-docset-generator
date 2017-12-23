<?php

$commandsIndex = new DOMDocument();

@$commandsIndex->loadHTML(
	file_get_contents( 'https://developer.wordpress.org/cli/commands/' )
);

$commandsIndex
	->getElementsByTagName( 'body' )
	->item( 0 )
	->removeChild( $commandsIndex->getElementById( 'wporg-header' ) );
$commandsIndex
	->getElementsByTagName( 'body' )
	->item( 0 )
	->removeChild( $commandsIndex->getElementById( 'wporg-footer' ) );
$commandsIndex
	->getElementsByTagName( 'body' )
	->item( 0 )
	->removeChild( $commandsIndex->getElementById( 'wpadminbar' ) );

$commandsIndex->saveHTMLFile(
	__DIR__ . '/../WP-CLI.docset/Contents/Resources/Documents/index.html'
);
