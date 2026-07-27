# api-datatype-file-name

[![Packagist Version](https://img.shields.io/packagist/v/elavora/api-datatype-file-name.svg?style=flat-square)](https://packagist.org/packages/elavora/api-datatype-file-name)
[![PHP Version](https://img.shields.io/packagist/php-v/elavora/api-datatype-file-name.svg?style=flat-square)](https://packagist.org/packages/elavora/api-datatype-file-name)
[![Composer Quality](https://github.com/Elavora/api-datatype-file-name/actions/workflows/quality.yml/badge.svg?branch=main)](https://github.com/Elavora/api-datatype-file-name/actions/workflows/quality.yml)
[![CodeQL](https://github.com/Elavora/api-datatype-file-name/actions/workflows/codeql.yml/badge.svg?branch=main)](https://github.com/Elavora/api-datatype-file-name/actions/workflows/codeql.yml)
[![License](https://img.shields.io/packagist/l/elavora/api-datatype-file-name.svg?style=flat-square)](https://packagist.org/packages/elavora/api-datatype-file-name)

DataType imutavel para validar nomes de arquivo portaveis entre filesystems.

## Requisitos

- PHP 8.3 ou superior.
- Demais requisitos declarados em [`composer.json`](composer.json).

## Instalacao

```bash
composer require elavora/api-datatype-file-name
```

## Inicio rapido

```php
use Elavora\Api\DataTypes\Filesystem\FileName;

$valor = FileName::from('relatorio.pdf');
$normalizado = $valor->value();
```

O valor e preservado sem `trim` ou outra normalizacao silenciosa.

## Documentacao

Consulte o [guia de uso](docs/USO.md) para as restricoes e a validacao local.
