<?php

declare(strict_types=1);

/*
|--------------------------------------------------------------------------
| Configurações de domínio do Prospector IA
|--------------------------------------------------------------------------
|
| Parâmetros regionais, catálogo de serviços (semente editável no painel),
| pesos da qualificação, limites de pesquisa, retenção (LGPD) e integrações.
| Credenciais ficam apenas em variáveis de ambiente, nunca no código.
|
*/

return [

    'locale' => 'pt_BR',
    'timezone' => 'America/Recife',
    'currency' => 'BRL',

    /*
    | Região inicial: São Lourenço da Mata e Região Metropolitana do Recife.
    | Novos municípios podem ser adicionados sem migração, via painel/config.
    */
    'region' => [
        'state' => 'PE',
        'default_city' => 'São Lourenço da Mata',
        'cities' => [
            'São Lourenço da Mata',
            'Recife',
            'Jaboatão dos Guararapes',
            'Olinda',
            'Paulista',
            'Cabo de Santo Agostinho',
            'Camaragibe',
            'Abreu e Lima',
            'Igarassu',
            'Ipojuca',
            'Moreno',
            'Itapissuma',
            'Araçoiaba',
            'Ilha de Itamaracá',
        ],
    ],

    /*
    | Categorias de segmento empresarial (filtro opcional). "Todos" é padrão.
    */
    'segments' => [
        'Todos',
        'Alimentação e bebidas',
        'Saúde e estética',
        'Educação',
        'Varejo',
        'Serviços profissionais',
        'Moda e beleza',
        'Construção e imóveis',
        'Tecnologia',
        'Automotivo',
        'Turismo e hospedagem',
        'Entretenimento e eventos',
        'Esporte e fitness',
        'Pet e veterinário',
        'Outros',
    ],

    /*
    | Catálogo comercial inicial. As linhas vão para a tabela "services" e
    | passam a ser editáveis pelo painel administrativo (não é hard-code).
    */
    'service_catalog' => [
        [
            'code' => 'PACOTE_8_ARTES',
            'name' => 'Pacote de 8 artes',
            'description' => 'Pacote com 8 artes para redes sociais.',
            'price' => '250.00',
            'is_active' => true,
        ],
        [
            'code' => 'LOGOTIPO_EXPRESS',
            'name' => 'Logotipo express',
            'description' => 'Criação de logotipo em formato express.',
            'price' => '300.00',
            'is_active' => true,
        ],
        [
            'code' => 'WORDPRESS_MELHORIA',
            'name' => 'Melhoria pontual de WordPress',
            'description' => 'Melhoria pontual de site WordPress, a partir de.',
            'price' => '200.00',
            'is_active' => true,
        ],
    ],

    /*
    | Qualificação de leads — pontuação explicável de 0 a 100.
    | A soma dos pesos é 100. Cada critério gera uma justificativa textual.
    */
    'scoring' => [
        'weights' => [
            'design_need' => 30,       // Necessidade observável de design/melhoria
            'service_fit' => 25,       // Compatibilidade com os serviços
            'evidence_reliability' => 20, // Confiabilidade/atualidade das evidências
            'channel_available' => 15, // Canal comercial permitido disponível
            'regional_fit' => 10,      // Adequação regional
        ],
        'thresholds' => [
            'high' => 80,   // 80-100: prioridade alta
            'medium' => 60, // 60-79: prioridade média
            // < 60: precisa de mais evidências
        ],
    ],

    /*
    | Etapas do funil comercial (CRM).
    */
    'pipeline_stages' => [
        'novo',
        'pesquisado',
        'qualificado',
        'aguardando_aprovacao',
        'contatado',
        'respondeu',
        'proposta_enviada',
        'negociacao',
        'ganho',
        'perdido',
    ],

    /*
    | Pesquisa de empresas.
    */
    'search' => [
        'default_target' => 50,
        'max_target' => 200,
        'google_page_size' => (int) env('GOOGLE_PLACES_PAGE_SIZE', 20),
        'google_max_results' => (int) env('GOOGLE_PLACES_MAX_RESULTS_PER_JOB', 100),
        'google_timeout' => (int) env('GOOGLE_PLACES_TIMEOUT_SECONDS', 15),
        'google_retry' => (int) env('GOOGLE_PLACES_RETRY_TIMES', 2),
    ],

    /*
    | Provedores. A ausência de chave deixa o conector em estado
    | "não configurado"; a aplicação nunca simula respostas de API.
    */
    'providers' => [
        'search' => env('SEARCH_PROVIDER', 'null'), // null|google
        'ai' => env('AI_PROVIDER', 'null'),         // null|openai_compatible
    ],

    'ai' => [
        'base_url' => env('AI_BASE_URL'),
        'model' => env('AI_MODEL'),
        'timeout' => (int) env('AI_TIMEOUT_SECONDS', 30),
        'retry' => (int) env('AI_RETRY_TIMES', 1),
    ],

    'communication' => [
        // Aprovação humana é obrigatória e não pode ser desligada pela UI.
        'human_approval_required' => true,
        'daily_message_limit' => (int) env('DAILY_MESSAGE_LIMIT', 100),
        'channels' => [
            'email' => [
                'configured' => ! in_array(env('MAIL_MAILER', 'log'), ['', 'log'], true),
            ],
            'whatsapp' => [
                'configured' => (bool) env('WHATSAPP_PHONE_NUMBER_ID') && (bool) env('WHATSAPP_ACCESS_TOKEN'),
            ],
            'instagram' => [
                'configured' => (bool) env('META_APP_ID') && (bool) env('META_PAGE_ACCESS_TOKEN'),
            ],
        ],
    ],

    /*
    | LGPD: retenção e bases legais.
    */
    'retention' => [
        // Dados transitórios do Places que as regras do Google não permitem
        // reter indefinidamente (place_id pode ser mantido; o restante, não).
        'transient_place_data_days' => (int) env('RETENTION_TRANSIENT_PLACE_DATA_DAYS', 30),
        'lead_months' => (int) env('RETENTION_LEAD_MONTHS', 36),
        'message_log_months' => (int) env('RETENTION_MESSAGE_LOG_MONTHS', 24),
        'audit_log_months' => (int) env('RETENTION_AUDIT_LOG_MONTHS', 60),
    ],

    'legal_bases' => [
        'execucao_contrato',
        'legitimo_interesse',
        'consentimento',
        'obrigacao_legal',
    ],
];
