# WP-CLI Dash Docset Generator

Generates Dash docsets for [all versions](https://github.com/wp-cli/handbook/releases) of WP-CLI commands documentation.

## Supported Dash features

- ✅ [Table of contents](https://kapeli.com/docsets#tableofcontents)
- ✅ [Index page](https://kapeli.com/docsets#settingindexpage)
- ✅ [Online redirection](https://kapeli.com/docsets#onlineRedirection)

## Requirements

- PHP \>=7.0
- [Composer](https://getcomposer.org/)
- [Make](https://www.gnu.org/software/make/)

## Usage

Run `make` to build the docset:

```
$ git clone git@github.com:ptrkcsk/wp-cli-dash-docset-generator.git
$ cd wp-cli-dash-docset-generator
$ make
```
