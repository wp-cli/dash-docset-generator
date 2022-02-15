builtFiles = versions WP-CLI.tgz

.PHONY: all
all: $(builtFiles)

.PHONY: clean
clean:
	rm -rf composer.phar src/versions vendor versions WP-CLI.docset WP-CLI.tgz

$(builtFiles): vendor
	php build.php

composer.phar:
	scripts/install-composer.sh

vendor: composer.phar
	php composer.phar install
