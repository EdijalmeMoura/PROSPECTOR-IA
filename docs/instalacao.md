# Instalação

Guia completo para rodar o Prospector IA em ambiente de desenvolvimento ou em
servidor próprio (hospedagem compatível com PHP).

## 1. Pré-requisitos

- PHP **8.3 ou superior** com extensões: `pdo_mysql`, `mbstring`, `openssl`,
  `tokenizer`, `xml`, `ctype`, `json`, `bcmath`, `fileinfo`, `curl`
- Composer 2.x
- MySQL 8 (ver [mysql.md](mysql.md))
- Node.js 18+ e npm (apenas para compilar os assets)
- Git

Conferir as extensões:

```bash
php -m
composer check-platform-reqs
```

## 2. Obter o código e dependências

```bash
git clone <url-do-repositorio> PROSPECTOR-IA
cd PROSPECTOR-IA
composer install --no-dev --optimize-autoloader   # remova --no-dev em desenvolvimento
npm ci && npm run build
```

Todo o frontend (Bootstrap 5 e ícones) é servido localmente a partir de
`public/` — não há dependência de CDN em runtime.

## 3. Variáveis de ambiente

```bash
cp .env.example .env
php artisan key:generate
```

Edite o `.env` e configure, no mínimo:

- `APP_URL`, `APP_ENV`, `APP_DEBUG=false` (produção)
- conexão MySQL (`DB_*`)
- credenciais de conectores, se houver (ver [credenciais.md](credenciais.md))

O arquivo `.env` nunca é versionado (ver `.gitignore`) e o `.env.example`
distribuído não contém segredos.

## 4. Banco e dados iniciais

```bash
php artisan migrate --force
php artisan db:seed --force
```

Os seeders criam:

1. usuário administrador (`AdminUserSeeder`, senha via `ADMIN_PASSWORD`);
2. catálogo de serviços editável (`ServiceCatalogSeeder`): pacote de 8 artes
   R$ 250, logotipo express R$ 300, melhoria WordPress a partir de R$ 200.

Para carregar dados de demonstração (marcados com `is_demo=true`):

```bash
SEED_DEMO_DATA=true php artisan db:seed --class=DemoSeeder --force
```

## 5. Servidor de aplicação

### Opção A — FrankenPHP (recomendado, binário único)

O repositório traz `docker/Caddyfile` pronto para o FrankenPHP:

```bash
frankenphp run --config docker/Caddyfile
```

Ajuste no arquivo o endereço (`:8000`) e o caminho absoluto da pasta `public/`.
Em produção, remova `auto_https off` e informe o domínio para TLS automático.

### Opção B — PHP embutido (desenvolvimento)

```bash
php artisan serve
```

### Opção C — PHP-FPM + Nginx/Apache

Aponte o document root para a pasta `public/` do projeto e configure o
`storage/` com permissão de escrita para o usuário do PHP:

```bash
chown -R www-data:www-data storage bootstrap/cache
chmod -R 775 storage bootstrap/cache
```

## 6. Agendamento e filas

São obrigatórios para retenção LGPD e processamento assíncrono de pesquisas.
Ver [cron-filas.md](cron-filas.md).

## 7. Otimizações de produção

```bash
php artisan config:cache
php artisan route:cache
php artisan view:cache
php artisan event:cache
composer install --no-dev --optimize-autoloader
```

Lembre-se de reexecutar `config:cache` sempre que mudar variáveis de ambiente.

## 8. Verificação pós-instalação

- `https://seu-dominio/up` deve retornar status HTTP 200;
- login em `/login` com o usuário administrador;
- sem credenciais, a tela de pesquisa mostra **"Conector não configurado"** —
  esse é o comportamento correto, não um erro.
