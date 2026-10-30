# elavora/api-datatype-file-name

[![Packagist Version](https://img.shields.io/packagist/v/elavora/api-datatype-file-name.svg?style=flat-square)](https://packagist.org/packages/elavora/api-datatype-file-name)
[![PHP Version](https://img.shields.io/packagist/php-v/elavora/api-datatype-file-name.svg?style=flat-square)](https://packagist.org/packages/elavora/api-datatype-file-name)
[![Qualidade](https://github.com/Elavora/api-datatype-file-name/actions/workflows/quality.yml/badge.svg?branch=main)](https://github.com/Elavora/api-datatype-file-name/actions/workflows/quality.yml)
[![CodeQL](https://github.com/Elavora/api-datatype-file-name/actions/workflows/codeql.yml/badge.svg?branch=main)](https://github.com/Elavora/api-datatype-file-name/actions/workflows/codeql.yml)
[![License](https://img.shields.io/packagist/l/elavora/api-datatype-file-name.svg?style=flat-square)](LICENSE)

DataType de nome de arquivo para aplicações Elavora API: valida um nome de arquivo (sem caminho, sem caracteres
reservados) e mantém o valor como veio.

```php
use Elavora\Api\DataTypes\Filesystem\FileName;

FileName::isValid('user.png'); // true
FileName::isValid('.env');     // false

$nome = FileName::from('user.png');
$nome->value(); // 'user.png'
```

Entrada inválida em `FileName::from()` lança `InvalidArgumentException`.

Especificação completa, exemplos e validação local: [docs/USO.md](docs/USO.md).

## Instalação

```bash
composer require elavora/api-datatype-file-name
```

Requisitos:

- PHP 8.3 ou superior.
- `elavora/api-datatype-core` `^2.0` (que traz o `elavora/api-framework` `^2.0`).

Versão `1.0`: atualiza o pacote para o `elavora/api-datatype-core` 2.0, que substitui o `^0.1`, e passa a exigir
PHP 8.3 ou superior. **Quebra:** o construtor deixa de ser público (no core 2 ele é `final protected`),
então `new FileName(...)` não funciona mais; use `FileName::from(...)`. `isValid()` e `value()` não mudam.

Este pacote é base de `elavora/api-datatype-file-path` e `elavora/api-datatype-storage-key`: depois do release da `1.0.0`,
esses pacotes devem exigir `elavora/api-datatype-file-name` `^1.0`.

## Desenvolvimento e testes

Você só precisa de Docker. O ambiente é a imagem `ghcr.io/elavora/php-dev:8.3-latest`: abra o repositório no VS Code
com **Dev Containers: Reopen in Container**, ou rode no PowerShell:

```powershell
docker run --rm --mount "type=bind,source=$($PWD.Path),target=/workspace" -w /workspace ghcr.io/elavora/php-dev:8.3-latest sh -lc 'git config --global --add safe.directory /workspace && composer install --no-interaction --prefer-dist && composer check'
```

Scripts do Composer:

```bash
composer check     # lint, testes e PHPStan
composer test      # testes PHPUnit
composer lint      # sintaxe (elavora-standards lint)
composer analyse   # PHPStan, nível 8
```

O código segue o padrão PER-CS, com as regras do pacote `elavora/code-standards`. No devcontainer, o arquivo é
formatado ao salvar; no PR, o CI formata o que faltou e commita a correção (`style: organiza o código`).
Os testes rodam no CI, no workflow **Qualidade**, em todo PR. O passo a passo, o que roda no CI e como reproduzi-lo
estão em [Desenvolvimento e testes](docs/DESENVOLVIMENTO.md).

## Como contribuir

1. Crie uma branch a partir da `main` (`feat/...`, `fix/...` ou `docs/...`).
2. Faça commits em português do Brasil, no padrão Conventional Commits (`feat: ...`, `fix: ...`, `docs: ...`).
3. Rode `composer check` antes de abrir o PR.
4. Abra o PR para a `main`, preencha o template e aplique as labels que definem a versão
   ([Versões e release](docs/AUTOMACAO.md#versões-e-release)).
5. Confira o comentário do pipeline de qualidade no PR. Ele não bloqueia o merge, mas quem revisa olha o resultado.

## Versões

Cada PR integrado na `main` gera uma versão automaticamente, conforme as labels do PR: sem a label `release` sai uma
beta (`1.1.0-beta.1`), com ela sai a estável (`1.1.0`). As tags novas não têm o prefixo `v`, e as notas de cada
GitHub Release são geradas em português a partir dos PRs. Veja [Versões e release](docs/AUTOMACAO.md#versões-e-release).

## Documentação

1. [Guia de uso](docs/USO.md): a especificação do pacote, com a API pública e exemplos.
2. [Desenvolvimento e testes](docs/DESENVOLVIMENTO.md): ambiente, como rodar os testes e o que roda no CI.
3. [Automação do repositório](docs/AUTOMACAO.md): workflows, Dependabot, estilo de código, labels e versões.

## Licença

MIT
