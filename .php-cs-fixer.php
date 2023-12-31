<?php
return (new PhpCsFixer\Config())
    ->setRiskyAllowed(true)
    ->setRules([
        '@PSR1' => true,
        '@PSR2' => true,
        '@Symfony' => true,
        'php_unit_method_casing' => false,
        'phpdoc_summary' => false,
        'phpdoc_align' => false,
        'phpdoc_separation' => false,
        'phpdoc_no_alias_tag' => false,
        'phpdoc_to_comment' => false,
        'no_trailing_whitespace_in_comment' => false,
    ])
    ->setFinder(PhpCsFixer\Finder::create()
        ->in(__DIR__.'/src')
        ->in(__DIR__.'/tests')
        ->in(__DIR__.'/e2e/*/app/Http/Controllers')
        ->in(__DIR__.'/e2e/*/tests/Feature')
    )
;
