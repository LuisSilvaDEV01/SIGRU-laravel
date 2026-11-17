# SIGRU (Laravel)

Sistema Integrado de Gerenciamento do Restaurante Universitário — Unimontes.

> **Evolução avançada** do repositório original [SIGRU-web](https://github.com/LuisSilvaDEV01/SIGRU-web) (PHP procedural).  
> Esta versão reescreve a aplicação em **Laravel**, consome o mesmo domínio MySQL e usa de fato as **views, triggers e stored procedures** do Seminário II (BD II).

## O que mudou em relação ao SIGRU-web

| Antes (`SIGRU-web`) | Agora (este repositório) |
|---------------------|--------------------------|
| PHP procedural na raiz | Laravel na raiz |
| SQL solto em `database/` | Scripts em `database/sql/` |
| App antigo como único código | App legado preservado em `legacy/` |
| Updates manuais de estoque/carteira | Procedures + triggers do banco |

## Estrutura

- **Laravel** — aplicação atual (Dashboard, Fila, Refeição, Carteiras, Estoque)
- **`legacy/`** — app PHP procedural (versão anterior, preservada)
- **`database/sql/`** — scripts acadêmicos do banco:
  - `sigru_create.sql`
  - `sigru_seed.sql`
  - `sigru_avancado.sql`

## Banco de dados

Ordem de importação no MySQL (Laragon/Workbench):

1. `database/sql/sigru_create.sql`
2. `database/sql/sigru_seed.sql`
3. `database/sql/sigru_avancado.sql`

Configuração em `.env`:

```env
DB_CONNECTION=mysql
DB_HOST=127.0.0.1
DB_PORT=3306
DB_DATABASE=sigru
DB_USERNAME=root
DB_PASSWORD=
```

## Como rodar

O Laravel 13 exige **PHP 8.3+** (ex.: Laragon). PHP 8.2 do XAMPP não serve para esta app.

```bash
composer install
cp .env.example .env
php artisan key:generate
# ajuste DB_* no .env e importe os 3 SQLs
php artisan serve
```

- App: http://127.0.0.1:8000
- Health: http://127.0.0.1:8000/health

## Módulos já migrados

1. Dashboard (views do dia)
2. Fila virtual
3. Registro de refeição (`sp_registrar_refeicao`)
4. Carteiras (`sp_recarregar_carteira`)
5. Estoque (`sp_movimentar_estoque` + trigger)

## Autores

Luís Otávio de Souza e Silva, Matheus Gonçalves Dias — Sistemas de Informação / Unimontes.
