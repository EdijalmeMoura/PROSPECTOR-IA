<?php

declare(strict_types=1);

namespace App\Support;

class DomainNormalizer
{
    /**
     * Normaliza a URL de um site para o domínio base (sem protocolo/www).
     * Retorna null quando não houver domínio utilizável.
     */
    public static function normalize(?string $website): ?string
    {
        if ($website === null || trim($website) === '') {
            return null;
        }

        $value = trim($website);
        if (! preg_match('#^https?://#i', $value)) {
            $value = 'http://' . $value;
        }

        $host = parse_url($value, PHP_URL_HOST);
        if (! is_string($host) || $host === '') {
            return null;
        }

        $host = mb_strtolower(rtrim($host, '.'));
        $host = preg_replace('/^www\./', '', $host);

        // Ignora hosts inválidos ou sem ponto (ex.: "localhost").
        if (! is_string($host) || ! str_contains($host, '.')) {
            return null;
        }

        return $host;
    }
}
