<?php

// autoload_static.php @generated by Composer

namespace Composer\Autoload;

class ComposerStaticInit1eb5373e9bd94bcb9982340e757c034a
{
    public static $prefixLengthsPsr4 = array (
        'W' => 
        array (
            'Workerman\\' => 10,
        ),
        'P' => 
        array (
            'PHPSocketIO\\' => 12,
        ),
    );

    public static $prefixDirsPsr4 = array (
        'Workerman\\' => 
        array (
            0 => __DIR__ . '/..' . '/workerman/workerman-for-win',
        ),
        'PHPSocketIO\\' => 
        array (
            0 => __DIR__ . '/..' . '/workerman/phpsocket.io-for-win/src',
        ),
    );

    public static function getInitializer(ClassLoader $loader)
    {
        return \Closure::bind(function () use ($loader) {
            $loader->prefixLengthsPsr4 = ComposerStaticInit1eb5373e9bd94bcb9982340e757c034a::$prefixLengthsPsr4;
            $loader->prefixDirsPsr4 = ComposerStaticInit1eb5373e9bd94bcb9982340e757c034a::$prefixDirsPsr4;

        }, null, ClassLoader::class);
    }
}
