# WP-CLI Dash Docset Generator

This project generates [Dash](https://kapeli.com/dash) [docsets](https://kapeli.com/docsets) for [all versions](https://github.com/wp-cli/handbook/releases) of the WP-CLI commands documentation.

## Supported Dash features

- ✅ [Table of contents](https://kapeli.com/docsets#tableofcontents)
- ✅ [Index page](https://kapeli.com/docsets#settingindexpage)
- ✅ [Online redirection](https://kapeli.com/docsets#onlineRedirection)

## Usage

### Prerequisites

You’ll need the following software to generate the docsets:

- PHP \>=7.0
- [Composer](https://getcomposer.org/)
- [Make](https://www.gnu.org/software/make/)

### Docset generation

Run `make` to generate the docsets:

```
$ git clone git@github.com:wp-cli/dash-docset-generator.git
$ cd dash-docset-generator
$ make
```

This will generate the following files:

```
.
├─ versions              ← Versioned docsets
│  ├─ X.X.X              ← e.g. 1.2.0
│  │  ├─ WP-CLI.docset   ← Docset
│  │  └─ WP-CLI.tgz      ← Compressed docset
│  └─ ...
├─ WP-CLI.docset         ← Docset for latest version
└─ WP-CLI.tgz            ← Compressed docset for latest version
```

### Submitting generated docsets

[Instructions for submitting user-contributed docsets to Dash](https://github.com/Kapeli/Dash-User-Contributions#contribute-a-new-docset)
