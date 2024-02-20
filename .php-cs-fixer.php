<?php

declare(strict_types=1);

use PhpCsFixer\Config;
use PhpCsFixer\Finder;

$rules = [
    // Default
    '@PHP80Migration' => true,
    '@PHP80Migration:risky' => true,
    '@Symfony' => true,
    '@Symfony:risky' => true,

    // Override of the Symfony config
    'class_attributes_separation' => [
        'elements' => ['method' => 'one', 'property' => 'one'],
    ], // Instead of ['elements' => ['method' => 'one']]
    'error_suppression' => false, // For testing purpose
    'no_trailing_whitespace_in_string' => false, // For string comparison in tests
    'operator_linebreak' => true, // Instead of ['only_booleans' => true]
    'phpdoc_to_comment' => [
        'ignored_tags' => ['phpstan-var', 'psalm-suppress'],
    ],
    'single_line_throw' => false,

    // Added
    'explicit_string_variable' => true,
    'general_phpdoc_annotation_remove' => [
        'annotations' => ['author', 'since', 'package', 'subpackage'],
    ],
    'header_comment' => ['header' => ''],
    'no_superfluous_elseif' => true,
    'no_useless_else' => true,
    'php_unit_test_case_static_method_calls' => true,
    'phpdoc_add_missing_param_annotation' => true,
    'phpdoc_no_empty_return' => true,
];

$finder = Finder::create()->in(__DIR__)->ignoreDotFiles(false);

$config = new Config();
$config
    ->setFinder($finder)
    ->setRiskyAllowed(true)
    ->setRules($rules)
    ->setUsingCache(true);

return $config;
