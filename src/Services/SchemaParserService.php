<?php

declare(strict_types=1);

namespace GatewayAPI\Services;

use GatewayAPI\Core\App;
use RuntimeException;

final class SchemaParserService
{
    /**
     * @return array<string, mixed>
     */
    public static function load(?string $schemaPath): array
    {
        if ($schemaPath === null || $schemaPath === '') {
            return ['exists' => false, 'content' => '', 'language' => 'text', 'filename' => null];
        }

        $fullPath = App::storagePath('schemas/' . ltrim($schemaPath, '/'));

        if (!is_file($fullPath)) {
            return ['exists' => false, 'content' => '', 'language' => 'text', 'filename' => basename($schemaPath)];
        }

        $content = file_get_contents($fullPath);

        if ($content === false) {
            throw new RuntimeException('Unable to read schema file.');
        }

        $extension = strtolower((string) pathinfo($fullPath, PATHINFO_EXTENSION));

        if (in_array($extension, ['json'], true)) {
            $decoded = json_decode($content, true);
            if (is_array($decoded)) {
                $content = json_encode($decoded, JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE) ?: $content;
            }
        }

        if (in_array($extension, ['xml', 'wsdl'], true)) {
            $dom = new \DOMDocument('1.0', 'UTF-8');
            $dom->preserveWhiteSpace = false;
            $dom->formatOutput = true;
            if (@$dom->loadXML($content, LIBXML_NONET | LIBXML_NOERROR | LIBXML_NOWARNING)) {
                $content = $dom->saveXML() ?: $content;
            }
        }

        return [
            'exists' => true,
            'content' => $content,
            'language' => match ($extension) {
                'json' => 'json',
                'yaml', 'yml' => 'yaml',
                'graphql', 'sdl' => 'graphql',
                'proto' => 'protobuf',
                'xml', 'wsdl' => 'xml',
                default => 'text',
            },
            'filename' => basename($fullPath),
        ];
    }
}