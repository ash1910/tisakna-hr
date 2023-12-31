<?php

// autoload_static.php @generated by Composer

namespace Composer\Autoload;

class ComposerStaticInit23b67a1ba8eb742b5d538afd10eebddb
{
    public static $files = array (
        'a16312f9300fed4a097923eacb0ba814' => __DIR__ . '/..' . '/igorw/get-in/src/get_in.php',
    );

    public static $prefixLengthsPsr4 = array (
        'Z' => 
        array (
            'Zend\\Diactoros\\' => 15,
        ),
        'P' => 
        array (
            'Psr\\Http\\Message\\' => 17,
        ),
        'I' => 
        array (
            'Ivory\\HttpAdapter\\' => 18,
        ),
    );

    public static $prefixDirsPsr4 = array (
        'Zend\\Diactoros\\' => 
        array (
            0 => __DIR__ . '/..' . '/zendframework/zend-diactoros/src',
        ),
        'Psr\\Http\\Message\\' => 
        array (
            0 => __DIR__ . '/..' . '/psr/http-message/src',
        ),
        'Ivory\\HttpAdapter\\' => 
        array (
            0 => __DIR__ . '/..' . '/egeloen/http-adapter/src',
        ),
    );

    public static $prefixesPsr0 = array (
        'G' => 
        array (
            'Geocoder' => 
            array (
                0 => __DIR__ . '/..' . '/willdurand/geocoder/src',
            ),
        ),
        'B' => 
        array (
            'Buzz' => 
            array (
                0 => __DIR__ . '/..' . '/kriswallsmith/buzz/lib',
            ),
        ),
    );

    public static function getInitializer(ClassLoader $loader)
    {
        return \Closure::bind(function () use ($loader) {
            $loader->prefixLengthsPsr4 = ComposerStaticInit23b67a1ba8eb742b5d538afd10eebddb::$prefixLengthsPsr4;
            $loader->prefixDirsPsr4 = ComposerStaticInit23b67a1ba8eb742b5d538afd10eebddb::$prefixDirsPsr4;
            $loader->prefixesPsr0 = ComposerStaticInit23b67a1ba8eb742b5d538afd10eebddb::$prefixesPsr0;

        }, null, ClassLoader::class);
    }
}
