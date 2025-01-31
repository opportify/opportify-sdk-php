<?php

use PhpCsFixer\Config;
use PhpCsFixer\Finder;

return (new Config())
    ->setRiskyAllowed(true)
    ->setIndent("    ") 
    ->setLineEnding("\n")
    ->setRules([
        '@PSR12' => true,  
        'not_operator_with_successor_space' => false,
    ])
    ->setFinder(
        Finder::create()
            ->in(__DIR__) 
            ->exclude(['vendor', 'node_modules']) 
    );
