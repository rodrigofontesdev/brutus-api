<?php

return (new PhpCsFixer\Config())
    ->setParallelConfig(new PhpCsFixer\Runner\Parallel\ParallelConfig(4, 20))
    ->setUsingCache(true)
    ->setRules([
        '@Symfony' => true
    ]);
