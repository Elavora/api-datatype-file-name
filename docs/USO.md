# Guia de uso

`FileName` aceita strings de 1 a 255 bytes e preserva o valor informado.

```php
use Elavora\Api\DataTypes\Filesystem\FileName;

$fileName = FileName::from('relatorio final.pdf');

echo $fileName->value(); // relatorio final.pdf
```

Sao rejeitados:

- caminhos e os caracteres `\ / : * ? " < > |`;
- nomes iniciados ou terminados por ponto;
- controles ASCII, DEL, espaco final e nomes de dispositivo do Windows;
- `.` e `..`.

Unicode valido e espacos internos sao aceitos.

## Validacao do pacote

Execute os comandos a partir da raiz do clone:

```bash
docker run --rm -v "${PWD}:/workspace" -w /workspace composer:2 composer update --no-interaction --no-progress --prefer-dist
docker run --rm -v "${PWD}:/workspace" -w /workspace composer:2 composer check
```
