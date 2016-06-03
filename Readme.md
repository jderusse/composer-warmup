# OpCode Warmer (composer plugin)

Optimize your application by warmuping OpCode.

## Requirements

- PHP `>=7.0`
- Zend Opcache
- composer `>=1.0.0`

## Install

```bash
$ composer global require "jderusse/composer-warmup"
```

## Configure

```ini
; /etc/php/7.0/cli/conf.d/10-opcache.ini
zend_extension=opcache.so
opcache.enable_cli=1
opcache.file_cache='/tmp/opcache'

; recommanded
opcache.file_update_protection=0
opcache.validate_timestamps=0
```

## Usage

```bash
$ cd my-project
$ composer warmup-opcode
```

## How does it work ?

Since PHP 7.0, the OpCache extension is able to store the compiled OpCode into
files.

This plugin add the `warmup-opcode` command to
[composer](https://getcomposer.org/) which triggers the compilation for every
PHP file discovered in the project.

When you start the application for the first time, PHP don't need to compile
the files which improve performances, as you can see in this [blackfire
comparison](https://blackfire.io/profiles/compare/a5e55813-de07-437c-9ddf-e8aefc6a8a81/graph).
