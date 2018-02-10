builtFiles = versions WP-CLI.tgz

all: clean WP-CLI.tgz

$(builtFiles): vendor
	php build.php

vendor:
	composer install

clean:
	rm -rf src/versions
	rm -rf versions
	rm -rf vendor
	rm -rf WP-CLI.docset
	rm -f WP-CLI.tgz

.PHONY: all clean
