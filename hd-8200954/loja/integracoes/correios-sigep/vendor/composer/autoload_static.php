<?php

// autoload_static.php @generated by Composer

namespace Composer\Autoload;

class ComposerStaticInit6e286fb0bf0975118439a4e07b72cdcf
{
    public static $prefixesPsr0 = array (
        'P' => 
        array (
            'PhpSigep' => 
            array (
                0 => __DIR__ . '/../..' . '/src',
            ),
        ),
    );

    public static $classMap = array (
        'PhpSigepFPDF' => __DIR__ . '/..' . '/stavarengo/php-sigep-fpdf/PhpSigepFPDF.php',
    );

    public static function getInitializer(ClassLoader $loader)
    {
        return \Closure::bind(function () use ($loader) {
            $loader->prefixesPsr0 = ComposerStaticInit6e286fb0bf0975118439a4e07b72cdcf::$prefixesPsr0;
            $loader->classMap = ComposerStaticInit6e286fb0bf0975118439a4e07b72cdcf::$classMap;

        }, null, ClassLoader::class);
    }
}
