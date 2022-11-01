<?php

declare(strict_types=1);

$rules = [
    // Default
    '@PSR12'                                           => true,
    '@PSR12:risky'                                     => true,
    '@Symfony'                                         => true,
    '@Symfony:risky'                                   => true,
    '@PHP74Migration'                                  => true,
    '@PHP74Migration:risky'                            => true,

    // Override of the Symfony config
    'binary_operator_spaces'                           => [
        'default'   => 'single_space',
        'operators' => [
            '=>' => 'align_single_space',
        ],
    ],
    'class_attributes_separation'                      => [
        'elements' => ['method' => 'one', 'property' => 'one'],
    ],
    'class_definition'                                 => [
        'inline_constructor_arguments' => false,
        'space_before_parenthesis'     => true,
        'single_line'                  => true,
    ], // To be PSR12
    'increment_style'                                  => ['style' => 'post'],
    'no_trailing_whitespace_in_string'                 => false, // For string comparison in tests
    'phpdoc_summary'                                   => false,
    'single_line_throw'                                => false,

    // Added
    'explicit_string_variable'                         => true,
    'general_phpdoc_annotation_remove'                 => [
        'annotations' => ['author', 'since', 'package', 'subpackage'],
    ],
    'global_namespace_import'                          => [
        'import_classes'   => true,
        'import_constants' => false,
        'import_functions' => false,
    ],
    'header_comment'                                   => ['header' => ''],
    'no_superfluous_elseif'                            => true,
    'no_useless_else'                                  => true,
    'nullable_type_declaration_for_default_null_value' => true,
    'operator_linebreak'                               => true,
    'ordered_imports'                                  => [
        'sort_algorithm' => 'alpha',
        'imports_order'  => ['class', 'function', 'const'],
    ],
    'php_unit_test_case_static_method_calls'           => true,
    'phpdoc_add_missing_param_annotation'              => true,
    'phpdoc_no_empty_return'                           => true,
];

$finder = PhpCsFixer\Finder::create()->in(__DIR__);

$config = new PhpCsFixer\Config();
$config
    ->setFinder($finder)
    ->setRiskyAllowed(true)
    ->setRules($rules)
    ->setUsingCache(true);

return $config;
