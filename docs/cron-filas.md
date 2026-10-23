# Cron e filas

O Prospector IA precisa de dois processamentos recorrentes: a **retenção LGPD**
e o **processamento da fila** de pesquisas de leads.

## 1. Agendador do Laravel (cron do sistema)

Adicione uma única entrada no crontab do servidor (usuário dono da aplicação):

```cron
* * * * * cd /caminho/para/PROSPECTOR-IA && php artisan schedule:run >> /dev/null 2>&1
```

A agenda definida em `routes/console.php` executa:

| Horário | Comando | Finalidade |
|---|---|---|
| Diariamente às 02:30 | `prospector:purge-retention` | Purga dados transitórios e registros fora do prazo de retenção (LGPD) |
| A cada minuto | `queue:work --stop-when-empty --max-time=60 --tries=2` | Drena a fila em hospedagens sem supervisor (com `withoutOverlapping`) |

### Teste manual da retenção

```bash
# Relata o que seria removido, sem remover nada
php artisan prospector:purge-retention --dry-run

# Executa a purga de fato
php artisan prospector:purge-retention
```

Os prazos vêm do `.env` (`RETENTION_*`) e podem ser ajustados sem alterar
código:

```dotenv
RETENTION_TRANSIENT_PLACE_DATA_DAYS=30   # conteúdo do Google Places (só place_id permanece)
RETENTION_LEAD_MONTHS=36
RETENTION_MESSAGE_LOG_MONTHS=24
RETENTION_AUDIT_LOG_MONTHS=60
```

## 2. Processamento de filas

### Modo padrão — driver `database` (hospedagem compartilhada)

Não exige serviços extras. O próprio agendador drena a fila a cada minuto
(acima). Para um servidor dedicado, prefira um worker contínuo:

```bash
php artisan queue:work --tries=2 --max-time=3600 --sleep=3
```

### Modo Redis (quando disponível)

```dotenv
QUEUE_CONNECTION=redis
REDIS_HOST=127.0.0.1
REDIS_PASSWORD=null
REDIS_PORT=6379
```

Mantenha o worker sob um gerenciador de processos, por exemplo **Supervisor**:

```ini
[program:prospector-worker]
process_name=%(program_name)s_%(process_num)02d
command=php /caminho/para/PROSPECTOR-IA/artisan queue:work redis --tries=2 --max-time=3600
autostart=true
autorestart=true
user=www-data
numprocs=1
redirect_stderr=true
stdout_logfile=/caminho/para/PROSPECTOR-IA/storage/logs/worker.log
```

Com worker contínuo e Supervisor, o `queue:work` no agendador se torna
redundante e pode ser removido do `routes/console.php`.

## 3. O que roda na fila

- `App\Jobs\RunSearchJob` — executa uma pesquisa de leads via conector
  configurado, com paginação, contadores (encontrada/duplicada/rejeitada/
  qualificada) e persistência transacional. Sem credencial, o job falha de
  forma explícita e **não cria registros fictícios**.

## 4. Monitoramento

```bash
php artisan queue:failed          # jobs com falha
php artisan queue:retry all       # reprocessar falhas
php artisan queue:flush           # descartar falhas (com cautela)
tail -f storage/logs/laravel.log  # logs da aplicação
```

Pesquisas também podem ser reprocessadas pela interface, no histórico de
pesquisas, o que cria um novo `SearchJob` mantendo o anterior para auditoria.
