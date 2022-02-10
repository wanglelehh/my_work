<?php

// autoload_static.php @generated by Composer

namespace Composer\Autoload;

class ComposerStaticInitdafa263ec6519f97b96740b182332345
{
    public static $files = array (
        '1cfd2761b63b0a29ed23657ea394cb2d' => __DIR__ . '/..' . '/topthink/think-captcha/src/helper.php',
        'ffc1d7141d4fcbaeb47a6929f0811ed1' => __DIR__ . '/..' . '/topthink/think-worker/src/command.php',
    );

    public static $prefixLengthsPsr4 = array (
        't' => 
        array (
            'think\\worker\\' => 13,
            'think\\composer\\' => 15,
            'think\\captcha\\' => 14,
        ),
        'a' => 
        array (
            'app\\' => 4,
        ),
        'W' => 
        array (
            'Workerman\\' => 10,
        ),
        'G' => 
        array (
            'GatewayWorker\\' => 14,
        ),
    );

    public static $prefixDirsPsr4 = array (
        'think\\worker\\' => 
        array (
            0 => __DIR__ . '/..' . '/topthink/think-worker/src',
        ),
        'think\\composer\\' => 
        array (
            0 => __DIR__ . '/..' . '/topthink/think-installer/src',
        ),
        'think\\captcha\\' => 
        array (
            0 => __DIR__ . '/..' . '/topthink/think-captcha/src',
        ),
        'app\\' => 
        array (
            0 => __DIR__ . '/../..' . '/application',
        ),
        'Workerman\\' => 
        array (
            0 => __DIR__ . '/..' . '/workerman/workerman',
        ),
        'GatewayWorker\\' => 
        array (
            0 => __DIR__ . '/..' . '/workerman/gateway-worker/src',
        ),
    );

    public static $prefixesPsr0 = array (
        'P' => 
        array (
            'PHPExcel' => 
            array (
                0 => __DIR__ . '/..' . '/phpoffice/phpexcel/Classes',
            ),
        ),
    );

    public static $classMap = array (
        'Datamatrix' => __DIR__ . '/..' . '/tecnick.com/tcpdf/include/barcodes/datamatrix.php',
        'PDF417' => __DIR__ . '/..' . '/tecnick.com/tcpdf/include/barcodes/pdf417.php',
        'QRcode' => __DIR__ . '/..' . '/tecnick.com/tcpdf/include/barcodes/qrcode.php',
        'TCPDF' => __DIR__ . '/..' . '/tecnick.com/tcpdf/tcpdf.php',
        'TCPDF2DBarcode' => __DIR__ . '/..' . '/tecnick.com/tcpdf/tcpdf_barcodes_2d.php',
        'TCPDFBarcode' => __DIR__ . '/..' . '/tecnick.com/tcpdf/tcpdf_barcodes_1d.php',
        'TCPDF_COLORS' => __DIR__ . '/..' . '/tecnick.com/tcpdf/include/tcpdf_colors.php',
        'TCPDF_FILTERS' => __DIR__ . '/..' . '/tecnick.com/tcpdf/include/tcpdf_filters.php',
        'TCPDF_FONTS' => __DIR__ . '/..' . '/tecnick.com/tcpdf/include/tcpdf_fonts.php',
        'TCPDF_FONT_DATA' => __DIR__ . '/..' . '/tecnick.com/tcpdf/include/tcpdf_font_data.php',
        'TCPDF_IMAGES' => __DIR__ . '/..' . '/tecnick.com/tcpdf/include/tcpdf_images.php',
        'TCPDF_IMPORT' => __DIR__ . '/..' . '/tecnick.com/tcpdf/tcpdf_import.php',
        'TCPDF_PARSER' => __DIR__ . '/..' . '/tecnick.com/tcpdf/tcpdf_parser.php',
        'TCPDF_STATIC' => __DIR__ . '/..' . '/tecnick.com/tcpdf/include/tcpdf_static.php',
    );

    public static function getInitializer(ClassLoader $loader)
    {
        return \Closure::bind(function () use ($loader) {
            $loader->prefixLengthsPsr4 = ComposerStaticInitdafa263ec6519f97b96740b182332345::$prefixLengthsPsr4;
            $loader->prefixDirsPsr4 = ComposerStaticInitdafa263ec6519f97b96740b182332345::$prefixDirsPsr4;
            $loader->prefixesPsr0 = ComposerStaticInitdafa263ec6519f97b96740b182332345::$prefixesPsr0;
            $loader->classMap = ComposerStaticInitdafa263ec6519f97b96740b182332345::$classMap;

        }, null, ClassLoader::class);
    }
}