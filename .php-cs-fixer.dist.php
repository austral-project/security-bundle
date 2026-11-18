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
    // Austral code style: 2-space indentation (same as .editorconfig)
    ->setIndent('  ')
    ->setLineEnding("\n")
    // Risky rules (strict_types, strict in_array, base64_decode strict...) change runtime
    // behaviour: they stay off until each bundle has been reviewed and covered by tests.
    ->setRiskyAllowed(false)
    ->setRules([
        '@Symfony' => true,
        '@PHP8x4Migration' => true,
        'nullable_type_declaration_for_default_null_value' => true,
        // Austral brace style: control structure braces on their own line, else/elseif/catch too
        'braces_position' => [
            'control_structures_opening_brace' => 'next_line_unless_newline_at_signature_end',
        ],
        'control_structure_continuation_position' => ['position' => 'next_line'],
        // Keep the existing docblocks (@param / @return) and do not force a final period
        'no_superfluous_phpdoc_tags' => false,
        'phpdoc_summary' => false,
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
