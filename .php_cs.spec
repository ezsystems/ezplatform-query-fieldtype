<?php

$config = EzSystems\EzPlatformCodeStyle\PhpCsFixer\EzPlatformInternalConfigFactory::build();

$config
    ->setRules(
        array_merge(
            $config->getRules(),
            [
                'visibility_required' => false,
            ]
        )
    )
    ->setFinder(
        PhpCsFixer\Finder::create()
            ->in(__DIR__ . '/spec')
            ->files()->name('*.php')
    );

return $config;
