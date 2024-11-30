<?php

$finder = (new PhpCsFixer\Finder())
    ->in(__DIR__)
    ->exclude([
        'storage',
        'public'
    ]);

return (new PhpCsFixer\Config())
    ->setParallelConfig(new PhpCsFixer\Runner\Parallel\ParallelConfig(4, 20))
    ->setUsingCache(true)
    ->setRules([
        '@Symfony' => true,
        'no_multiline_whitespace_around_double_arrow' => false,
    ])
    ->setFinder($finder);
