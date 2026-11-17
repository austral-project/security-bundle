<?php

declare(strict_types=1);

// Header name from the package name: austral/entity-file-bundle -> "EntityFile Bundle", austral/contracts -> "Contracts".
$package = json_decode((string) file_get_contents(__DIR__.'/composer.json'), true)['name'] ?? 'austral/unknown';
$words = explode('-', substr($package, strlen('austral/')));
$isBundle = 'bundle' === end($words);
$headerName = implode('', array_map('ucfirst', $isBundle ? array_slice($words, 0, -1) : $words)).($isBundle ? ' Bundle' : '');

$finder = (new PhpCsFixer\Finder())
    ->in(array_filter([__DIR__.'/src', __DIR__.'/tests'], 'is_dir'))
    ->ignoreDotFiles(true)
    ->ignoreVCS(true);

return (new PhpCsFixer\Config())
    ->setRiskyAllowed(true)
    ->setRules([
        '@Symfony' => true,
        '@Symfony:risky' => true,
        '@PHP82Migration' => true,
        'declare_strict_types' => true,
        'strict_param' => true,
        'nullable_type_declaration_for_default_null_value' => true,
        'ordered_imports' => ['imports_order' => ['class', 'function', 'const']],
        'header_comment' => [
            'header' => <<<TXT
                This file is part of the Austral {$headerName} package.

                (c) Austral <support@austral.dev>

                For the full copyright and license information, please view the LICENSE
                file that was distributed with this source code.
                TXT,
            'comment_type' => 'comment',
            'location' => 'after_open',
        ],
    ])
    ->setFinder($finder);
