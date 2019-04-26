# OpCode Warmer (composer plugin)

Optimize your application by warming up OpCode.

## Requirements

- PHP `>=7.0`
- Zend extension [Opcache](http://php.net/manual/en/book.opcache.php)
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

; recommended
opcache.file_update_protection=0
```

## Usage

```bash
$ cd my-project
$ composer warmup-opcode
```

## How does it work?

Since PHP 7.0, the OpCache extension is able to store the compiled OpCode into
files.

This plugin adds the `warmup-opcode` command to
[composer](https://getcomposer.org/) which triggers the compilation for every
PHP file discovered in the project.

When you start the application for the first time, PHP doesn't need to compile
the files, which improve performance.
