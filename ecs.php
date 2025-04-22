<?php

declare(strict_types=1);

use PhpCsFixer\Fixer\Import\NoUnusedImportsFixer;
use Symplify\EasyCodingStandard\Config\ECSConfig;
use Symplify\EasyCodingStandard\ValueObject\Set\SetList;

return ECSConfig::configure()
    ->withPaths([
        __DIR__ . '/app',
        __DIR__ . '/routes',
        __DIR__ . '/tests',
    ])
    // Reglas específicas
    ->withRules([
        NoUnusedImportsFixer::class,
    ])
    // Agregar sets preparados para formato y buenas prácticas
    ->withSets([
        SetList::PSR_12,       // Estilo de codificación estándar PHP (incluye indentación)
        SetList::CLEAN_CODE,   // Buenas prácticas generales
        SetList::COMMON,       // Reglas comunes útiles
        SetList::LARAVEL,      // Estilo específico de Laravel (opcional, pero recomendado)
    ]);
