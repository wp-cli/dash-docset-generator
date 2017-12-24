builtFiles = WP-CLI.docset/Contents/Resources/Documents/commands \
             WP-CLI.docset/Contents/Resources/docSet.dsidx

all: clean WP-CLI.tgz

WP-CLI.tgz: $(builtFiles)
	tar --exclude='.DS_Store' -cvzf WP-CLI.tgz WP-CLI.docset

$(builtFiles): vendor
	php build.php

vendor:
	composer install

clean:
	rm -rf vendor
	rm -rf WP-CLI.docset/Contents/Resources/Documents/commands
	rm WP-CLI.docset/Contents/Resources/docSet.dsidx

.PHONY: all clean
