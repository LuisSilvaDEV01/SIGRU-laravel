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
- **Docker** — `Dockerfile` + `docker-compose.yml` (app + MySQL com os 3 SQLs)

---

## Como rodar com Docker (recomendado para a equipe)

Não precisa instalar PHP 8.3, Composer nem MySQL na máquina. Só **Docker Desktop** (ligado).

```bash
git clone https://github.com/LuisSilvaDEV01/SIGRU-laravel.git
cd SIGRU-laravel
docker compose up --build
```

> Não rode `php artisan serve` ao mesmo tempo na porta 8000 — deixa só o Docker.

Na primeira subida o Composer instala as dependências e o MySQL importa sozinho:

1. `sigru_create.sql`
2. `sigru_seed.sql`
3. `sigru_avancado.sql`

Acesse:

| Serviço | URL / conexão |
|---------|----------------|
| App | http://localhost:8000 |
| Health | http://localhost:8000/health |
| MySQL (Workbench) | `127.0.0.1:3307` · user `root` · senha `sigru` · database `sigru` |

> A porta **3307** no host evita conflito com XAMPP/MySQL local na 3306.

### Comandos úteis

```bash
# subir em segundo plano
docker compose up --build -d

# logs
docker compose logs -f app
docker compose logs -f mysql

# parar
docker compose down

# zerar o banco e reimportar os 3 SQLs (apaga o volume)
docker compose down -v
docker compose up --build
```

---

## Como rodar sem Docker (Laragon)

O Laravel 13 exige **PHP 8.3+**. O PHP 8.2 do XAMPP **não** serve.

```bash
composer install
cp .env.example .env
php artisan key:generate
```

Importe no MySQL, nesta ordem:

1. `database/sql/sigru_create.sql`
2. `database/sql/sigru_seed.sql`
3. `database/sql/sigru_avancado.sql`

Ajuste o `.env`:

```env
DB_CONNECTION=mysql
DB_HOST=127.0.0.1
DB_PORT=3306
DB_DATABASE=sigru
DB_USERNAME=root
DB_PASSWORD=
```

```bash
php artisan serve
```

- App: http://127.0.0.1:8000  
- Health: http://127.0.0.1:8000/health  

---

## Módulos já migrados

1. Dashboard (views do dia)
2. Fila virtual
3. Registro de refeição (`sp_registrar_refeicao`)
4. Carteiras (`sp_recarregar_carteira`)
5. Estoque (`sp_movimentar_estoque` + trigger)

## Autores

Luís Otávio de Souza e Silva, Matheus Gonçalves Dias — Sistemas de Informação / Unimontes.
