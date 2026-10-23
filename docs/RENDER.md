# Deploy no Render (Docker)

O serviço deve usar a branch `arena/5288f8c2-prospector-ia`, runtime **Docker**,
Root Directory vazio (raiz do repositório) e Dockerfile Path **`./Dockerfile`**.
Não sobrescreva o Docker Command. Health Check Path: **`/up`**.
O container escuta em `0.0.0.0:$PORT` (padrão 10000); TLS fica no proxy do Render.

## Variáveis obrigatórias

- `APP_ENV=production`, `APP_DEBUG=false`
- `APP_KEY=base64:...` (gere uma vez com `php artisan key:generate --show`
  em um ambiente Laravel ou `printf 'base64:'; openssl rand -base64 32`;
  mantenha a mesma chave nos próximos deploys)
- `APP_URL=https://SEU-SERVICO.onrender.com`
- `TRUST_PROXIES=true`, `SESSION_SECURE_COOKIE=true`
- `DB_CONNECTION=mysql`, `DB_HOST`, `DB_PORT=3306`, `DB_DATABASE`,
  `DB_USERNAME`, `DB_PASSWORD`: banco MySQL 8 externo acessível pelo Render.
- `LOG_CHANNEL=stderr`, `CACHE_STORE=database`, `SESSION_DRIVER=database`
- `QUEUE_CONNECTION=sync` inicialmente; para `database` é necessário um worker
  separado executando `php artisan queue:work --tries=3 --timeout=120`.
- `RUN_MIGRATIONS=true`: executa `migrate --force` antes de iniciar o servidor.
  Use apenas uma instância migradora; em múltiplas réplicas execute a migração
  separadamente no pre-deploy/shell e deixe essa variável como `false`.

Não use SQLite no filesystem efêmero do serviço: dados somem em redeploy/restart.
Arquivos enviados também precisam de armazenamento persistente (disco ou objeto).
Integrações são opcionais: copie as variáveis necessárias de `.env.example`;
sem credenciais, conectores permanecem não configurados.

## Primeiro acesso

Depois das migrações, no Shell do serviço, defina `ADMIN_EMAIL` e uma
`ADMIN_PASSWORD` forte nas variáveis do Render e execute **uma vez**:

```sh
php artisan db:seed --class=ServiceCatalogSeeder --force
php artisan db:seed --class=AdminUserSeeder --force
```

Não execute `DatabaseSeeder`/`DemoSeeder` em produção. O entrypoint não cria
usuários nem dados fictícios. O AdminUserSeeder redefine a senha se repetido.
Configure também cron externo para `php artisan schedule:run` a cada minuto
(retenção/LGPD). O processo web não executa cron nem worker automaticamente.

## Validar

1. Fazer deploy do commit contendo o Dockerfile (Manual Deploy → latest commit).
2. Confirmar build concluído, migrações sem erro e health check `/up` HTTP 200.
3. Conferir login e acesso ao painel via HTTPS, sem loops ou erro CSRF.

Build local, se Docker estiver disponível: `docker build -t prospector-ia .`.
Nunca incluir `.env`, tokens ou banco real no contexto de build.
