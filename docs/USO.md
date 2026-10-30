# Guia de uso

DataType de nome de arquivo para aplicações Elavora API.

## Instalação

```bash
composer require elavora/api-datatype-file-name
```

## Quando usar

- Validar valores antes de chegar na regra de negócio.
- Evitar passar strings soltas entre services, DTOs e persistência.
- Reutilizar a mesma validação em controllers, comandos e testes.

## Exemplo rápido

```php
use Elavora\Api\DataTypes\Filesystem\FileName;

$nome = FileName::from('user.png');

$nome->value();                     // 'user.png'
(string) $nome;                     // 'user.png'
json_encode($nome);                 // '"user.png"'
FileName::isValid('user.png');      // true
FileName::isValid('.env');          // false
```

O construtor não é público: crie sempre por `FileName::from()`.

## Versão e compatibilidade

| Item | Valor |
|---|---|
| Última versão antes do upgrade | `0.2.0` (tag legada `v0.2.0`) |
| Versão do upgrade | `1.0.0` |
| PHP | `>=8.3` |
| `elavora/api-datatype-core` | `^2.0` |

As versões seguintes saem do fluxo central de release: veja [Versões e release](AUTOMACAO.md#versões-e-release).
A versão `1.0.0` atualiza o pacote para o `elavora/api-datatype-core` `^2.0` (que traz o framework 2) e exige PHP 8.3
ou superior.

**Quebra de compatibilidade:** no `elavora/api-datatype-core` 2 o construtor de `AbstractDataType` é
`final protected`, então `new FileName('user.png')` deixa de funcionar fora da própria classe. Troque por
`FileName::from('user.png')`. `FileName::isValid()` e `value()` continuam iguais.

**Pacotes dependentes:** `elavora/api-datatype-file-path` e `elavora/api-datatype-storage-key` usam este pacote. Depois
do release da `1.0.0`, eles devem passar a exigir `elavora/api-datatype-file-name` `^1.0`.

### `composer.json`

O `composer.json` do pacote deve ter:

- `require.php` = `">=8.3"` e `require.elavora/api-datatype-core` = `"^2.0"`
- `extra.branch-alias.dev-main` = `"1.0-dev"`
- `autoload.psr-4` = `"Elavora\Api\DataTypes\Filesystem\\": "src/"` (sem mudança)
- `require-dev.phpunit/phpunit` = `"^10.5 || ^11.0 || ^12.0"` (sem mudança)
- `scripts.test` = `"phpunit -c phpunit.xml"` (sem mudança)
- `require-dev`, `scripts` e `config` de qualidade (`elavora/code-standards`, PHPStan, `lint`, `analyse`, `check`):
  veja [`composer.json` em Automação](AUTOMACAO.md#composerjson)
- nenhum `repositories` (nem `path`, nem `vcs`)
- `composer validate --strict --no-check-publish` sem erros.

## Criação e entrada inválida

`Elavora\Api\DataTypes\Filesystem\FileName::from(mixed $value): static` valida o valor com `isValid()` e devolve um
`FileName` com o valor recebido, sem alteração. Se o valor for inválido, lança `\InvalidArgumentException` com a
mensagem `Valor invalido para Elavora\Api\DataTypes\Filesystem\FileName.`

```php
use Elavora\Api\DataTypes\Filesystem\FileName;

try {
    FileName::from('.env');
} catch (\InvalidArgumentException $e) {
    echo $e->getMessage(); // Valor invalido para Elavora\Api\DataTypes\Filesystem\FileName.
}
```

Métodos da instância: `value(): string|int|bool|float|null` (aqui sempre `string`), `jsonSerialize()` (mesmo valor) e
`__toString()`.

## Validação do nome de arquivo

`Elavora\Api\DataTypes\Filesystem\FileName::isValid(mixed $value): bool` não lança exceção. O valor só é válido
quando é uma `string` que atende a todas as regras abaixo:

- não é vazia;
- tem no máximo 255 bytes;
- não é `.` nem `..`;
- não começa com `.` (arquivos ocultos como `.env` são rejeitados);
- não termina com `.`;
- não contém nenhum dos caracteres `/`, `:`, `*`, `?`, `"`, `<`, `>` e `|`.

Qualquer outro tipo (`int`, `bool`, `null`, `array`, objeto) é inválido. Não há normalização: o valor de `from()` é
igual ao recebido.

| Entrada | `isValid` | `from()` / valor |
|---|---|---|
| `'user.png'` | `true` | `user.png` |
| `'relatorio final.pdf'` | `true` | `relatorio final.pdf` |
| `'arquivo'` (sem extensão) | `true` | `arquivo` |
| `'a.tar.gz'` | `true` | `a.tar.gz` |
| `str_repeat('a', 255)` | `true` | o mesmo valor |
| `str_repeat('a', 256)` | `false` | lança `InvalidArgumentException` |
| `''` | `false` | lança `InvalidArgumentException` |
| `'.'`, `'..'` | `false` | lança `InvalidArgumentException` |
| `'.env'` (começa com ponto) | `false` | lança `InvalidArgumentException` |
| `'arquivo.'` (termina com ponto) | `false` | lança `InvalidArgumentException` |
| `'user/name.png'` | `false` | lança `InvalidArgumentException` |
| `'a:b'`, `'a*b'`, `'a?b'`, `'a"b'`, `'a<b'`, `'a>b'`, `'a\|b'` | `false` | lança `InvalidArgumentException` |
| `123` (`int`) | `false` | lança `InvalidArgumentException` |
| `null`, `false` | `false` | lança `InvalidArgumentException` |

O pacote valida só o nome. Ele não trata caminhos, não consulta o sistema de arquivos e não restringe extensões.

Contraexemplo (comportamento que não vale mais): `new FileName('user.png')` funcionava no
`elavora/api-datatype-core` `^0.1` e falha no `^2.0`, porque o construtor é `final protected`.

## Principais pontos de entrada

- `Elavora\Api\DataTypes\Filesystem\FileName` (`final readonly`, herda de `Elavora\Api\DataTypes\AbstractDataType`)

## Dependências de runtime

- `elavora/api-datatype-core` `^2.0`

## Validação do pacote

O passo a passo para rodar lint, PHPStan e testes em container está em [Desenvolvimento e testes](DESENVOLVIMENTO.md).
Na aplicação consumidora, rode os testes dela depois de instalar o pacote.

## Observações

- Mantenha regras de produto fora deste pacote.
- Prefira configurar extensões no bootstrap da aplicação.
- Instale apenas os módulos que a aplicação realmente usa.
