<?php

declare(strict_types=1);

$finder = new PhpCsFixer\Finder();
$finder->in(__DIR__ . '/src')
    ->append([__FILE__]);

$config = new PhpCsFixer\Config();
$config->setUsingCache(false)
    ->setRules(
        [
            'native_function_invocation' => [
                'exclude' => [
                    '_'
                ],
                'strict' => false,
            ],
        ]
    )
    ->setRiskyAllowed(true)
    ->setFinder($finder);

return $config;
