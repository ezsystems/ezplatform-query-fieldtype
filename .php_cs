<?php

return EzSystems\EzPlatformCodeStyle\PhpCsFixer\EzPlatformInternalConfigFactory::build()->setFinder(
    PhpCsFixer\Finder::create()
        ->in([__DIR__ . '/src', __DIR__ . '/spec'])
        ->files()->name('*.php')
);
