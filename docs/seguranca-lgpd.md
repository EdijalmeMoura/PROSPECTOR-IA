# Segurança e LGPD

Resumo dos controles implementados no Prospector IA e como operá-los.

## 1. Autenticação, perfis e acesso

- Autenticação nativa do Laravel com senha em hash (`bcrypt`, 12 rounds).
- RBAC por perfil via middleware `role` (`App\Http\Middleware\EnsureUserRole`)
  e controle de conta ativa (`EnsureUserActive`).
- Rate limit de **5 tentativas de login por minuto** na rota de autenticação.
- Rotas de webhook fora do grupo CSRF, porém protegidas por assinatura HMAC.

## 2. Proteções de aplicação

| Ameaça | Controle |
|---|---|
| CSRF | tokens Blade/`@csrf` em todos os formulários; exceção só para webhooks com HMAC |
| XSS | escape automático do Blade; nenhuma renderização de HTML cruzo de usuários |
| SQL injection | Eloquent/Query Builder com bindings; não há SQL concatenado com entrada |
| Mass assignment | propriedades `$fillable` explícitas em todos os models |
| Exposição de segredos | `.env` não versionado; tokens nunca em logs nem na interface |
| Erros em produção | `APP_DEBUG=false` no `.env.example`; páginas de erro sem stack trace |
| Upload/CSV | validação de cabeçalho, tipo e tamanho; rejeição de linhas inválidas com relatório |

## 3. Auditoria

A tabela `audit_logs` registra eventos relevantes com usuário, ação, entidade e
timestamp, incluindo:

- alterações de etapas e edições de leads;
- aprovações, invalidações e versões de conteúdo;
- tentativas de envio bloqueadas (opt-out, canal não configurado, hash
  divergente) e envios realizados;
- execuções de retenção e pedidos de titulares.

A consulta fica na tela *Auditoria* da interface (perfil administrativo).

## 4. Retenção e exclusão

Comando agendado (`routes/console.php`, diário às 02:30):

```bash
php artisan prospector:purge-retention           # executa
php artisan prospector:purge-retention --dry-run # só relata
```

Regras praticadas:

- conteúdos transitórios do Google Places expiram em
  `RETENTION_TRANSIENT_PLACE_DATA_DAYS` (padrão 30 dias); o `place_id`,
  identificador lícito e não sensível, pode ser mantido para evitar
  reimportação;
- leads, mensagens e logs são purgados conforme `RETENTION_LEAD_MONTHS`,
  `RETENTION_MESSAGE_LOG_MONTHS` e `RETENTION_AUDIT_LOG_MONTHS`;
- opt-outs são preservados (necessários para continuar respeitando a recusa),
  ainda que os dados de contato associados sejam removidos.

## 5. Direitos dos titulares

- **Opt-out** pela tela do lead e por entrada em `opt_outs`; qualquer tentativa
  posterior de envio é bloqueada e auditada.
- **Pedidos de exclusão**: o operador administrativo remove o lead e o comando
  de retenção completa o expurgo dos dados associados dentro do prazo.
- **Revisão e correção**: todos os dados do lead são editáveis na interface,
  com histórico de etapas em `lead_stage_history`.

## 6. Comunicação de saída — travas obrigatórias

1. Nenhuma mensagem externa sai sem **aprovação humana** do conteúdo.
2. Editar um texto aprovado cria nova versão e **invalida a aprovação**
   anterior (status `Substituida`).
3. No instante do envio, o hash do conteúdo é re-checado; divergência bloqueia
   o envio.
4. Opt-out, canal não configurado e ausência de confirmação do provedor também
   bloqueiam; a tentativa falhada é registrada em `message_logs` com status
   `falha` e em auditoria.
5. Limite diário por ambiente: `DAILY_MESSAGE_LIMIT` (padrão 100).

## 7. Dados de demonstração vs. dados reais

- Seeders de demo só executam com `SEED_DEMO_DATA=true`.
- Toda linha de demo carrega `is_demo = true` e aparece separada em filtros,
  métricas e exportações.
- O sistema não possui empresas "inventadas" no fluxo real: sem fonte
  configurada, a busca não retorna nada e informa o motivo.

## 8. Checklist de produção

- [ ] `APP_ENV=production`, `APP_DEBUG=false`, `APP_URL` com HTTPS
- [ ] `APP_KEY` gerado e `.env` com permissão restrita (600)
- [ ] MySQL com usuário dedicado e senha forte; TLS quando aplicável
- [ ] `config:cache`, `route:cache`, `view:cache`
- [ ] cron `schedule:run` ativo e fila drenando (ver [cron-filas.md](cron-filas.md))
- [ ] backups automatizados e testados
- [ ] credenciais de conectores só inseridas após homologação dos opt-ins
- [ ] senha do administrador inicial trocada no primeiro acesso
