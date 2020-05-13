<?php

declare(strict_types=1);

$finder = PhpCsFixer\Finder::create()
    ->in(__DIR__.'/src');

return PhpCsFixer\Config::create()
    ->setRiskyAllowed(true)
    ->setFinder($finder)
    ->setRules(
        [
            'array_syntax' => ['syntax' => 'short'],
            'concat_space' => ['spacing' => 'one'],
            'native_function_invocation' => true,
            'ordered_imports' => true,
            'strict_comparison' => true,
            'strict_param' => true,
        ]
    )
    ->setUsingCache(false);
