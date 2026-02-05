# SaaS Leads

CRM leve para captacao e follow-up de leads, com projetos, imports, funil de status e mensagens via WhatsApp.

## Visao geral
SaaS Leads organiza leads importados (Google Maps ou CSV), permite classificar por status e manter o historico de interacoes. A pagina de lead centraliza contato, localizacao, notas e mensagens.

## Principais recursos
- Projetos e imports para organizar bases.
- Funil de status com contagem e cores por etapa.
- Pesquisa e filtros por status, categoria, nota e termo.
- Envio rapido para WhatsApp com template e variaveis.
- Exportacao e importacao CSV.

## Stack
- PHP 8.x
- Postgres (Supabase)
- HTML + CSS

## Comecando
1. Copie o arquivo de ambiente.
```
copy .env.example .env
```
2. Instale dependencias.
```
composer install
```
3. Suba o servidor embutido do PHP.
```
php -S localhost:8080 -t public
```

## Variaveis de ambiente
- `APP_ENV`
- `APP_DEBUG`
- `APP_URL`
- `DB_DRIVER`
- `DB_HOST`
- `DB_PORT`
- `DB_DATABASE`
- `DB_USERNAME`
- `DB_PASSWORD`
- `GOOGLE_MAPS_API_KEY`

## Banco de dados
- Para Supabase, use `database/schema_postgres.sql`.
- O schema MySQL original esta em `database/schema.sql`.

## Rotas principais
- `/projects`
- `/projects/show?id=1`
- `/imports/new?project_id=1`
- `/leads`
- `/leads/new`
- `/leads/show?id=1`
- `/leads/edit?id=1`
- `/leads/export`
- `/leads/search`

## CSV
- Exportacao: `/leads/export` respeita filtros.
- Importacao: coluna usada (0-based) B(1) Maps URL, F(5) Address, I(8) Phone, K(10) Mobile, O(14) Category, Q(16) Comments, R(17) Rating, S(18) Website.

## WhatsApp
- O link usa `https://wa.me/` com telefone normalizado.
- Templates permitem variaveis `{nome}`, `{cidade}`, `{categoria}`.

## Deploy (Vercel)
- PHP no Vercel usa runtime comunitario (`vercel-php`).
- Crie `api/index.php` apontando para `public/index.php` e um `vercel.json` com rotas.
- Use um banco remoto e configure as variaveis no painel.

## Contribuicao
- Abra uma issue com contexto e passos de reproducao.
- Envie PR pequeno e focado.

## Licenca
Privado.
