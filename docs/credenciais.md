# Credenciais e conectores

Nenhum segredo acompanha o repositório. Todas as integrações permanecem
**desligadas** até que uma credencial real seja informada no `.env`. Sem
credencial, a interface exibe "Conector não configurado" e o backend falha de
modo explícito — nunca simula dados.

## 1. Google Places API (New) — busca de estabelecimentos

1. Crie um projeto no Google Cloud e ative a **Places API (New)**.
2. Crie uma chave de API em *Credentials*, restringindo-a à API e, de
   preferência, ao IP/servidor de produção.
3. Configure no `.env`:

```dotenv
SEARCH_PROVIDER=google
GOOGLE_PLACES_API_KEY=chave-real
GOOGLE_PLACES_TIMEOUT_SECONDS=15
GOOGLE_PLACES_RETRY_TIMES=2
GOOGLE_PLACES_PAGE_SIZE=20
GOOGLE_PLACES_MAX_RESULTS_PER_JOB=100
```

Comportamento implementado:

- paginação da API New (Text Search/Details) respeitando o máximo por job;
- timeout e retry com backoff via HTTP client do Laravel;
- erros HTTP e de quota são capturados, logados **sem a chave** e refletidos no
  job (`SearchProviderException`), sem derrubar a aplicação;
- apenas o `place_id` tem retenção permanente. Nome, endereço, telefone e
  demais conteúdos são tratados como transitórios e expiram em
  `RETENTION_TRANSIENT_PLACE_DATA_DAYS`;
- dados complementares de fontes independentes são registrados à parte pelo
  operador, nunca atribuídos ao Google indevidamente.

## 2. Provedor de IA (OpenAI-compatível)

A camada de IA é abstrata (`App\Services\Ai\AiProviderInterface`). O provider
`openai_compatible` aceita qualquer endpoint compatível com o formato de
chat/completions:

```dotenv
AI_PROVIDER=openai_compatible
AI_BASE_URL=https://seu-provedor/v1
AI_API_KEY=chave-real
AI_MODEL=nome-do-modelo
AI_TIMEOUT_SECONDS=30
AI_RETRY_TIMES=1
```

Com `AI_PROVIDER=null` (padrão), o sistema responde que a IA não está
disponível e não gera conteúdo. As saídas são estruturadas, vinculadas às
evidências do lead e gravadas com incertezas explícitas em `ai_generations`.
Não há inferência de intenção de compra, faturamento ou orçamento.

## 3. WhatsApp Business Cloud API

Exige número de telefone verificado na Meta e aprovação de templates
(opt-in do destinatário):

```dotenv
WHATSAPP_PHONE_NUMBER_ID=
WHATSAPP_ACCESS_TOKEN=
WHATSAPP_VERIFY_TOKEN=token-definido-por-voce
WHATSAPP_APP_SECRET=
WHATSAPP_API_VERSION=v21.0
```

O webhook de verificação e entrega fica em `api/webhooks/meta/*`, com checagem
de assinatura HMAC SHA-256. Mensagens só são enviadas após aprovação humana e
respeitam opt-out e o limite diário (`DAILY_MESSAGE_LIMIT`).

## 4. Meta (Instagram/Messenger)

```dotenv
META_APP_ID=
META_APP_SECRET=
META_PAGE_ACCESS_TOKEN=
META_GRAPH_VERSION=v21.0
```

Aplica-se a mesma regra: APIs oficiais, permissões e opt-in. Não existe
automação de navegador nem contorno de bloqueio/CAPTCHA em nenhum canal.

## 5. E-mail

```dotenv
MAIL_MAILER=smtp
MAIL_HOST=smtp.seu-provedor.com.br
MAIL_PORT=587
MAIL_USERNAME=remetente@dominio.com.br
MAIL_PASSWORD=senha-ou-app-password
MAIL_SCHEME=tls
MAIL_FROM_ADDRESS="contato@dominio.com.br"
MAIL_FROM_NAME="Prospector IA"
```

Com `MAIL_MAILER=log` (padrão), as mensagens são apenas escritas em
`storage/logs/laravel.log` — útil para homologação sem envio real.

## 6. Rotatividade e segurança de segredos

- Rodar `php artisan config:cache` após alterar qualquer credencial.
- Nunca colar chaves em commits, prints, tickets ou código-fonte.
- Se uma chave vazar, revogar imediatamente no provedor e gerar outra.
- Logs passam por saneamento: tokens e chaves não são registrados.
- O usuário administrador inicial não recebe senha por e-mail; defina
  `ADMIN_PASSWORD` no momento do seed e troque a senha no primeiro acesso.
