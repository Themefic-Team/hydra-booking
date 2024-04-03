<?php

// autoload_static.php @generated by Composer

namespace Composer\Autoload;

class ComposerStaticInitf230e5d46c73a3bfe303ee4885dd24a1
{
    public static $prefixLengthsPsr4 = array (
        'H' => 
        array (
            'HydraBooking\\Services\\' => 22,
            'HydraBooking\\PostType\\' => 22,
            'HydraBooking\\DB\\' => 16,
            'HydraBooking\\Admin\\' => 19,
        ),
    );

    public static $prefixDirsPsr4 = array (
        'HydraBooking\\Services\\' => 
        array (
            0 => __DIR__ . '/../..' . '/includes/services',
        ),
        'HydraBooking\\PostType\\' => 
        array (
            0 => __DIR__ . '/../..' . '/includes/post-type',
        ),
        'HydraBooking\\DB\\' => 
        array (
            0 => __DIR__ . '/../..' . '/database',
        ),
        'HydraBooking\\Admin\\' => 
        array (
            0 => __DIR__ . '/../..' . '/admin',
        ),
    );

    public static $classMap = array (
        'Composer\\InstalledVersions' => __DIR__ . '/..' . '/composer/InstalledVersions.php',
    );

    public static function getInitializer(ClassLoader $loader)
    {
        return \Closure::bind(function () use ($loader) {
            $loader->prefixLengthsPsr4 = ComposerStaticInitf230e5d46c73a3bfe303ee4885dd24a1::$prefixLengthsPsr4;
            $loader->prefixDirsPsr4 = ComposerStaticInitf230e5d46c73a3bfe303ee4885dd24a1::$prefixDirsPsr4;
            $loader->classMap = ComposerStaticInitf230e5d46c73a3bfe303ee4885dd24a1::$classMap;

        }, null, ClassLoader::class);
    }
}
