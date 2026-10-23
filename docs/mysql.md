# Banco de dados — MySQL 8

O Prospector IA usa MySQL 8 em todos os ambientes que não sejam de teste
automatizado (os testes usam SQLite em memória e não tocam neste banco).

## 1. Criar banco e usuário

```sql
CREATE DATABASE prospector
  CHARACTER SET utf8mb4
  COLLATE utf8mb4_unicode_ci;

CREATE USER 'prospector'@'localhost'
  IDENTIFIED BY 'defina-uma-senha-forte';

GRANT SELECT, INSERT, UPDATE, DELETE, CREATE, ALTER, INDEX, DROP,
      REFERENCES, LOCK TABLES
  ON prospector.*
  TO 'prospector'@'localhost';

FLUSH PRIVILEGES;
```

Não use o usuário `root` da aplicação. O conjunto de privilégios acima cobre
migrations e operação normal; remova `CREATE/ALTER/DROP` depois da implantação
se sua política de segurança exigir.

## 2. Configurar o `.env`

```dotenv
DB_CONNECTION=mysql
DB_HOST=127.0.0.1
DB_PORT=3306
DB_DATABASE=prospector
DB_USERNAME=prospector
DB_PASSWORD=sua-senha-forte
```

Para conexões com TLS (recomendado em bancos gerenciados):

```dotenv
MYSQL_ATTR_SSL_CA=/caminho/ca.pem
```

## 3. Migrations e seeders

```bash
php artisan migrate --force
php artisan db:seed --force
```

São 22 migrations que criam, entre outras, as tabelas: `users`, `search_jobs`,
`companies`, `company_sources`, `leads`, `lead_contacts`, `lead_evidence`,
`services`, `ai_generations`, `approvals`, `campaigns`, `message_logs`,
`opt_outs`, `proposals`, `deals` e `audit_logs`, com chaves estrangeiras,
índices e tabelas de apoio (tarefas, notas, histórico de etapas, filas e
sessões).

## 4. Filas e sessões no banco

As filas usam o driver `database` por padrão (`QUEUE_CONNECTION=database`),
compatível com hospedagens compartilhadas. As tabelas `jobs`, `job_batches` e
`failed_jobs` já são criadas pelas migrations, assim como a tabela `sessions`
(`SESSION_DRIVER=database`).

## 5. Backup e restauração

```bash
# Backup
mysqldump --single-transaction --routines --triggers \
  -u prospector -p prospector | gzip > backup-$(date +%F).sql.gz

# Restauração
gunzip < backup-2026-10-22.sql.gz | mysql -u prospector -p prospector
```

A retenção automática de dados pessoais (LGPD) roda diariamente pelo comando
`prospector:purge-retention` — ver [cron-filas.md](cron-filas.md) e
[seguranca-lgpd.md](seguranca-lgpd.md). Backups não substituem a política de
retenção: alinhe o ciclo de vida dos backups com os prazos legais.

## 6. Troubleshooting

| Sintoma | Causa provável e correção |
|---|---|
| `SQLSTATE[HY000] [2002]` | MySQL fora do ar ou `DB_HOST/DB_PORT` incorretos |
| `Access denied for user` | senha errada ou privilégios insuficientes |
| `Unknown database` | execute o `CREATE DATABASE` antes do migrate |
| Erro de charset/emoji | confirme `utf8mb4` no banco e nas tabelas |
| `Specified key was too long` | não deve ocorrer no MySQL 8; em 5.7 ative `innodb_large_prefix` |
