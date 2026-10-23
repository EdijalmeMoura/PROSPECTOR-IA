# Prospector IA

Plataforma de prospecção de leads (MVP v1.0) em **Laravel 12 / PHP 8.3+**, com
interface em português do Brasil, foco em **São Lourenço da Mata/PE e Região
Metropolitana do Recife (RMR)** e conformidade com a **LGPD**.

O sistema pesquisa, pontua, qualifica e organiza leads de serviços de design e
web (pacote de artes, logotipo express, melhoria de WordPress), com aprovação
humana obrigatória antes de qualquer mensagem externa.

> **Princípio de honestidade dos dados:** sem credencial configurada, o sistema
> exibe **"Conector não configurado"** e nunca simula respostas de API, inventa
> empresas nem declara leads qualificados que a fonte não retornou.

---

## 1. Stack e requisitos

| Componente | Tecnologia |
|---|---|
| Backend | PHP 8.3+, Laravel 12 |
| Banco | MySQL 8 (SQLite apenas para testes automatizados) |
| Frontend | Blade + Bootstrap 5 (100% local, sem CDN) + JavaScript |
| Filas | `database` por padrão; Redis quando disponível |
| Testes | PHPUnit 11 (PHPUnit, não Pest) |
| HTTP externo | HTTP client do Laravel com timeout, retry e log; sem credenciais em logs |
| Servidor de aplicação | FrankenPHP/Caddy (`docker/Caddyfile`) ou qualquer stack PHP-FPM |

Configurações regionais fixas: locale `pt_BR`, timezone `America/Recife`, moeda
BRL, meta padrão de **50 leads por pesquisa**.

---

## 2. Módulos implementados

1. **Pesquisa de leads** — filtros (municípios da RMR expansíveis, quantidade,
   termos), progresso em tempo real, contadores de registros
   encontrada/duplicada/rejeitada/qualificada, histórico e reprocessamento.
2. **Resultados** — score de 0 a 100, evidências vinculadas, estado de
   qualificação e ações disponíveis por lead.
3. **Cadastro manual e importação CSV** — preview com validação linha a linha,
   rejeição de linhas inválidas, deduplicação por CNPJ/telefone/place_id.
4. **Conector modular de fontes** — primeiro provedor: **Google Places API
   (New)**. Só opera com credencial real, com paginação, tratamento de quota e
   erros HTTP. Persistência permanente apenas do `place_id`; demais conteúdos
   são transitórios e purgados pela política de retenção.
5. **Score explicável** — 30 necessidade de design/site + 25 compatibilidade com
   os serviços + 20 confiabilidade das evidências + 15 canal de contato
   disponível + 10 região. Faixas: **80+ alta, 60–79 média, <60 apenas
   evidências** (não qualificável). O sistema **não infere** intenção de compra,
   faturamento ou orçamento.
6. **Banco de dados** — 22 migrations (16 entidades de negócio + tabelas de
   apoio), FKs, índices, factories e transações onde há escrita multi-tabela.
7. **Camada de IA abstrata** — provedor configurável (`null` ou
   `openai_compatible`), saída estruturada vinculada a evidências e incertezas
   explícitas. Sem provedor configurado, responde "não disponível" sem inventar.
8. **Central de aprovação humana** — versões com hash de conteúdo; editar um
   texto aprovado cria nova versão e **invalida a aprovação anterior**;
   re-checagem de integridade imediatamente antes do envio.
9. **Adaptadores de comunicação oficiais** — e-mail, WhatsApp Business Cloud API
   e Meta, via APIs oficiais. Sem automação de navegador, sem contorno de
   CAPTCHA/login/bloqueios. Toda falha de canal fica registrada em
   `message_logs` com auditoria.
10. **CRM** — etapas Novo → Pesquisado → Qualificado → Aguardando aprovação →
    Contatado → Respondeu → Proposta enviada → Negociação → Ganho/Perdido, com
    filtros, tarefas de acompanhamento, exportação e opt-out.
11. **LGPD e segurança** — RBAC por perfil, CSRF, escape de saída (XSS),
    queries parametrizadas (SQLi), rate limit de login, auditoria, retenção
    automatizada e pedidos de titulares (opt-out e exclusão).
12. **Dashboard** — métricas reais calculadas do banco (não há estimativas
    fabricadas), com separação clara entre dados reais e de demonstração.

---

## 3. Início rápido (desenvolvimento)

```bash
composer install
cp .env.example .env
php artisan key:generate
# Edite o .env com os dados do MySQL (veja docs/mysql.md)
php artisan migrate --seed          # cria admin, catálogo de serviços e (opcional) demo
npm install && npm run build        # assets locais do Bootstrap
php artisan serve                   # ou FrankenPHP: veja docker/Caddyfile
```

Usuário administrador inicial: `admin@prospector.local` — a senha é definida
pelo `AdminUserSeeder` via variável `ADMIN_PASSWORD` (padrão de primeira
execução: `trocar-minha-senha`, **troque imediatamente**).

Dados de demonstração: somente com `SEED_DEMO_DATA=true` no `.env`. Todo
registro de demo leva o marcador `is_demo = true` e nunca se mistura com dados
reais nos filtros e métricas.

Documentação detalhada:

- [docs/instalacao.md](docs/instalacao.md) — instalação completa e dependências
- [docs/mysql.md](docs/mysql.md) — banco MySQL 8, permissões e troubleshooting
- [docs/cron-filas.md](docs/cron-filas.md) — agendamento (cron) e processamento de filas
- [docs/credenciais.md](docs/credenciais.md) — Google Places, IA e canais Meta/WhatsApp
- [docs/seguranca-lgpd.md](docs/seguranca-lgpd.md) — controles de segurança e retenção

---

## 4. Testes automatizados

```bash
php vendor/bin/phpunit
```

Os testes usam SQLite em memória (`:memory:`) e `RefreshDatabase`, portanto não
tocam o banco de desenvolvimento. Resultado da execução mais recente:

```
OK — 52 testes, 148 assertions
```

Cobertura: autenticação/RBAC, CRUD e revisão de leads, deduplicação, cálculo de
score, preview/importação CSV, jobs de busca e comportamento do conector não
configurado (sem dados falsos), fluxo de aprovação/versões/hash/opt-out/canal
indisponível, IA indisponível, webhooks Meta (verificação de token + HMAC),
retenção LGPD (purga e `--dry-run`), CRUD de serviços, separação real/demo e
smoke test de todas as rotas principais.

---

## 5. Status do projeto

### Concluído

- Modelagem completa: 22 migrations, 19 models, factories e seeders
  (admin, catálogo de serviços editável, demo sinalizada)
- Pesquisa de leads com jobs em fila, orquestrador, contadores e reprocessamento
- Conector Google Places (New) com paginação, timeout, retry e tratamento de
  quota; provedor `null` que falha de forma explícita sem credencial
- Score explicável com a fórmula ponderada e as três faixas de qualificação
- Cadastro manual, importação CSV com preview/validação/deduplicação
- Camada de IA abstrata com provider nulo seguro e provider OpenAI-compatível
- Aprovação humana com versionamento, hash, invalidação por edição e
  verificação no momento do envio
- Adaptadores e-mail/WhatsApp/Meta com opt-out, limite diário e auditoria
- CRM com pipeline de 9 etapas, tarefas, filtros e exportação
- LGPD: opt-out, auditoria, comando de retenção agendado, separação real/demo
- Dashboard com métricas reais e 44 telas Blade em Bootstrap local
- 52 testes automatizados verdes e smoke HTTP de todas as rotas
- Servidor FrankenPHP/Caddy funcional (`docker/Caddyfile`)

### Pendente (não bloqueado, requer ambiente/decisão)

- Implantação em produção real (domínio, TLS, MySQL dedicado, supervisor/Redis)
- Assets compilados em pipeline de CI (hoje o build local funciona)
- Novos provedores de busca além do Google Places (arquitetura de drivers pronta)
- Testes de carga e ajuste fino de workers FrankenPHP/PHP-FPM

### Bloqueado por credencial (código pronto, aguardando chaves reais)

- **Google Places API New**: busca real de estabelecimentos — defina
  `SEARCH_PROVIDER=google` e `GOOGLE_PLACES_API_KEY`
- **IA generativa**: geração de textos estruturados — defina `AI_PROVIDER`,
  `AI_BASE_URL`, `AI_API_KEY` e `AI_MODEL`
- **WhatsApp Business / Meta**: envio real — defina as variáveis
  `WHATSAPP_*` e `META_*` após aprovação nas plataformas oficiais
- **E-mail SMTP**: envio real — troque `MAIL_MAILER=log` por `smtp` com host real

Nenhum desses itens é "simulado": enquanto as credenciais não existem, a
interface informa que o conector/canal não está configurado.

---

## 6. Regras de ouro do projeto

- Nunca versionar `.env` nem credenciais; tokens nunca aparecem em logs ou na UI.
- `APP_DEBUG=false` em produção.
- Aprovação humana é obrigatória antes de qualquer mensagem externa.
- Sem automação de navegador e sem contornar CAPTCHA, login ou limites técnicos.
- Não se infere intenção de compra, faturamento ou orçamento sem evidência; a
  ausência de atualização de um estabelecimento não prova inatividade.
- Do Google Places, apenas o `place_id` tem retenção permanente; o restante dos
  dados é transitório e expira conforme a política de retenção.

## Deploy no Render

Consulte [docs/RENDER.md](docs/RENDER.md) para Docker, variáveis, banco e primeiro acesso.
