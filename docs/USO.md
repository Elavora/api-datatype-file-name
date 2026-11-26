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

$nome = new FileName('user.png');

$nome->value();                     // 'user.png'
(string) $nome;                     // 'user.png'
json_encode($nome);                 // '"user.png"'
FileName::isValid('user.png');      // true
FileName::isValid('.env');          // false
```

O construtor não é público: crie sempre por `new FileName()`.

## Versão e compatibilidade

| Item | Valor |
|---|---|
| Última versão antes do upgrade | `0.2.0` (tag legada `v0.2.0`) |
| Versão do upgrade | `1.0.0` |
| PHP | `>=8.4` |
| `elavora/api-datatype-core` | `^2.0` |

As versões seguintes saem do fluxo central de release: veja [Versões e release](AUTOMACAO.md#versões-e-release).
A versão `1.0.0` atualiza o pacote para o `elavora/api-datatype-core` `^2.0` (que traz o framework 2) e exige PHP 8.3
ou superior.

**Quebra de compatibilidade:** no `elavora/api-datatype-core` 2 o construtor de `AbstractDataType` é
`final protected`, então `new FileName('user.png')` deixa de funcionar fora da própria classe. Troque por
`new FileName('user.png')`. `FileName::isValid()` e `value()` continuam iguais.

### Correção: caracteres de controle (versão `3.1.5`)

Tipo de mudança: correção de validação (`fix`). Versão proposta: **patch, `3.1.5`** (PR com a label `bug`; a
estável sai com a label `release`, conforme [Versões e release](AUTOMACAO.md#versões-e-release)). Justificativa: a
API pública (assinaturas, tipos de retorno e exceção) não muda e o pacote existe para rejeitar nome inválido; um nome
com NUL nunca foi um valor utilizável (o PostgreSQL recusa e a aplicação responde 500 ao tentar gravar), então tratá-lo como inválido corrige um defeito e não cria
contrato novo. Efeito aceito: nomes com controles, separadores Unicode, marcas bidi de override ou UTF-8 malformado,
que antes passavam, agora são rejeitados. Quem armazena nomes desse tipo deve saneá-los antes.

Dependentes que precisam subir a constraint para `^3.1.5` e rodar a suíte: `elavora/api-datatype-file-path` (hoje
`^3.0`), `elavora/api-datatypes` (hoje `^3.1`) e, por transitividade, `elavora/api-datatype-storage-key` (se usar nome
ou caminho), `elavora/api-skeleton` e a aplicação Dossier, que precisam de `composer update
elavora/api-datatype-file-name`. Nenhum `composer.json` de `elavora/api-framework` declara este pacote (conferir no
`org/dependencias.md`, não acessível nesta etapa). Como `^3.0` e `^3.1` já admitem a `3.1.5`, a subida do piso serve
para garantir a correção.

**Pacotes dependentes:** `elavora/api-datatype-file-path` e `elavora/api-datatype-storage-key` usam este pacote. Depois
do release da `1.0.0`, eles devem passar a exigir `elavora/api-datatype-file-name` `^1.0`.

### `composer.json`

O `composer.json` do pacote deve ter:

- `require.php` = `">=8.4"` e `require.elavora/api-datatype-core` = `"^2.0"`
- `extra.branch-alias.dev-main` = `"1.0-dev"`
- `autoload.psr-4` = `"Elavora\Api\DataTypes\Filesystem\\": "src/"` (sem mudança)
- `require-dev.phpunit/phpunit` = `"^10.5 || ^11.0 || ^12.0"` (sem mudança)
- `scripts.test` = `"phpunit -c phpunit.xml"` (sem mudança)
- `require-dev`, `scripts` e `config` de qualidade (`elavora/code-standards`, PHPStan, `lint`, `analyse`, `check`):
  veja [`composer.json` em Automação](AUTOMACAO.md#composerjson)
- nenhum `repositories` (nem `path`, nem `vcs`)
- `composer validate --strict --no-check-publish` sem erros.

## Criação e entrada inválida

`Elavora\Api\DataTypes\Filesystem\new FileName(mixed $value): static` valida o valor com `isValid()` e devolve um
`FileName` com o valor recebido, sem alteração. Se o valor for inválido, lança `\InvalidArgumentException` com a
mensagem `Valor invalido para Elavora\Api\DataTypes\Filesystem\FileName.`

```php
use Elavora\Api\DataTypes\Filesystem\FileName;

try {
    new FileName('.env');
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
- não contém nenhum dos caracteres `/`, `:`, `*`, `?`, `"`, `<`, `>` e `|`;
- não contém caracteres de controle nem de formatação enganosa (lista abaixo);
- é UTF-8 bem formado (bytes que não formam UTF-8 válido tornam o nome inválido).

### Caracteres de controle e de formatação

Um nome que contenha qualquer um dos pontos de código abaixo é inválido, em qualquer posição (início, meio ou fim):

| Faixa | Descrição | Exemplos |
|---|---|---|
| `U+0000` a `U+001F` | controles C0, incluindo NUL, TAB, LF e CR | `"\0"`, `"\t"`, `"\n"`, `"\r"`, `"\x1B"` |
| `U+007F` | DEL | `"\x7F"` |
| `U+0080` a `U+009F` | controles C1 (em UTF-8, `"\xC2\x80"` a `"\xC2\x9F"`) | `"\u{85}"` (NEL) |
| `U+2028`, `U+2029` | separadores de linha e de parágrafo | `"\u{2028}"` |
| `U+202A` a `U+202E` | marcas bidi de embutimento e override (LRE, RLE, PDF, LRO, RLO) | `"\u{202E}"` |
| `U+2066` a `U+2069` | marcas bidi de isolamento (LRI, RLI, FSI, PDI) | `"\u{2066}"` |

Motivos: NUL e demais controles são rejeitados pelo PostgreSQL (texto com NUL) e quebram logs, cabeçalhos HTTP e
sistemas de arquivos; `U+2028`/`U+2029` são quebras de linha Unicode; as marcas bidi de override e isolamento
reordenam a exibição e permitem nomes enganosos (por exemplo `"relatorio\u{202E}fdp.exe"` aparece como
`relatorioexe.pdf`). Ficam fora desta regra, de propósito, as demais marcas invisíveis (`U+200B` a `U+200F`,
`U+061C`, `U+FEFF`) e os espaços comuns: continuam válidos, para não rejeitar texto legítimo em árabe, hebraico e
outros scripts.

Ao ser rejeitado, o valor segue o fluxo normal: `isValid()` devolve `false` e `new FileName()` lança
`InvalidArgumentException` com a mensagem `Valor invalido para Elavora\Api\DataTypes\Filesystem\FileName.` (a mesma
de qualquer entrada inválida; não há mensagem específica por caractere). O `documentedType()` passa a citar os
caracteres de controle na `description` (seção "Documentação do tipo (OPTIONS)", abaixo).

Qualquer outro tipo (`int`, `bool`, `null`, `array`, objeto) é inválido. Não há normalização: o valor de `new FileName()` é
igual ao recebido.

| Entrada | `isValid` | `new FileName()` / valor |
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
| `"a\0b"` (NUL no meio), `"\0"`, `"a\0"` | `false` | lança `InvalidArgumentException` |
| `"a\nb"`, `"a\rb"`, `"a\tb"`, `"a\x1Fb"` | `false` | lança `InvalidArgumentException` |
| `"arquivo\n"` (LF no fim) | `false` | lança `InvalidArgumentException` |
| `"a\x7Fb"` (DEL) | `false` | lança `InvalidArgumentException` |
| `"a\u{85}b"`, `"a\u{9F}b"` (C1) | `false` | lança `InvalidArgumentException` |
| `"a\u{2028}b"`, `"a\u{2029}b"` | `false` | lança `InvalidArgumentException` |
| `"a\u{202E}b"`, `"a\u{202A}b"`, `"a\u{2066}b"`, `"a\u{2069}b"` | `false` | lança `InvalidArgumentException` |
| `"a\xFFb"` (UTF-8 inválido) | `false` | lança `InvalidArgumentException` |
| `'relatório.pdf'`, `'日本語.txt'`, `'a b.txt'`, `"a\u{200B}b"`, `"a\u{200F}b"` | `true` | o mesmo valor |
| `123` (`int`) | `false` | lança `InvalidArgumentException` |
| `null`, `false` | `false` | lança `InvalidArgumentException` |

O pacote valida só o nome. Ele não trata caminhos, não consulta o sistema de arquivos e não restringe extensões.

Contraexemplo (bug corrigido): até a `3.1.4`, `FileName::isValid("a\0b")` devolvia `true` (a regex
`/^[^\/:*?"<>|]+$/` só excluía os oito caracteres reservados). O NUL chegava ao PostgreSQL, que rejeita texto com NUL,
e a aplicação consumidora respondia 500. O comportamento correto é `false`.

Contraexemplo (comportamento que não vale mais): `new FileName('user.png')` funcionava no
`elavora/api-datatype-core` `^0.1` e falha no `^2.0`, porque o construtor é `final protected`.

## Cenários de aceite (caracteres de controle)

Cada cenário vale para `isValid()` (resultado indicado) e para `new FileName()` (lança `InvalidArgumentException` com a
mensagem padrão quando `false`; devolve `value()` igual à entrada quando `true`).

1. `"a\0b"`, `"\0"`, `"a\0"` e `"\0a"`: `false`.
2. `"a\nb"`, `"a\rb"`, `"a\tb"`, `"a\x0Bb"`, `"a\x0Cb"`, `"a\x1Bb"`, `"a\x1Fb"`: `false`.
3. `"arquivo\n"` e `"arquivo\r\n"` (quebra no fim): `false`.
4. `"a\x7Fb"` (DEL): `false`.
5. `"a\u{80}b"`, `"a\u{85}b"`, `"a\u{9F}b"` (C1): `false`; `"a\u{A0}b"` (espaço sem quebra, `U+00A0`): `true`.
6. `"a\u{2028}b"` e `"a\u{2029}b"`: `false`.
7. `"a\u{202A}b"`, `"a\u{202B}b"`, `"a\u{202C}b"`, `"a\u{202D}b"`, `"a\u{202E}b"`: `false`.
8. `"a\u{2066}b"`, `"a\u{2067}b"`, `"a\u{2068}b"`, `"a\u{2069}b"`: `false`.
9. `"a\xFFb"` e `"\xC2"` (UTF-8 malformado): `false`, sem warning nem exceção interna.
10. Válidos que continuam válidos: `'user.png'`, `'relatório final.pdf'`, `'日本語.txt'`, `'a b.txt'`, `'a.tar.gz'`,
    `"a\u{200B}b"`, `"a\u{200F}b"`, `"a\u{FEFF}b"` e `"\u{627}\u{644}.txt"` (árabe).
11. Regressão: as regras anteriores seguem iguais (vazio, `.`, `..`, começa ou termina com `.`, os oito caracteres
    reservados, `int`/`null`/`bool`/`array`).
12. O limite de 255 continua em bytes: `str_repeat('a', 255)` é `true`; `str_repeat('a', 256)` é `false`; 128 vezes
    `"é"` (256 bytes) é `false`.
13. Nenhum cenário acima altera a assinatura pública nem a mensagem de `new FileName()`.

## Principais pontos de entrada

- `Elavora\Api\DataTypes\Filesystem\FileName` (`final readonly`, herda de `Elavora\Api\DataTypes\AbstractDataType`)

## Documentação do tipo (OPTIONS)

`FileName` implementa `Elavora\Api\Framework\Contracts\DescribesType` e declara, via `documentedType()`, o que o
OPTIONS público da API mostra para o campo:

- `type`: `string`
- `example`: `user.png`
- `description`: de 1 a 255 caracteres, sem `/ : * ? " < > |`, sem caracteres de controle (NUL, quebras de linha, DEL, C1, separadores e marcas bidi Unicode), sem começar nem terminar com ponto e diferente de `.` e `..`.
## Dependências de runtime

- `elavora/api-datatype-core` `^4.0`

## Validação do pacote

O passo a passo para rodar lint, PHPStan e testes em container está em [Desenvolvimento e testes](DESENVOLVIMENTO.md).
Na aplicação consumidora, rode os testes dela depois de instalar o pacote.

## Observações

- Mantenha regras de produto fora deste pacote.
- Prefira configurar extensões no bootstrap da aplicação.
- Instale apenas os módulos que a aplicação realmente usa.
