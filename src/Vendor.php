<?php

namespace Rcf;

class Vendor
{
    public const VERSION = '0.2.0';

    public const PLATFORMS = [
        'x86_64-linux' => [
            'file' => 'librcf-{{version}}-x86_64-unknown-linux-gnu',
            'checksum' => 'df88398e8dd950fbc503b382b280823713ca53931561e25b6d2f1c868bd37811',
            'lib' => 'lib/librcf.so',
            'ext' => 'tar.gz'
        ],
        'aarch64-linux' => [
            'file' => 'librcf-{{version}}-aarch64-unknown-linux-gnu',
            'checksum' => 'a420ae9792eac906efe895f50b4b89fe92d5711baba848415d07d89cbec6e625',
            'lib' => 'lib/librcf.so',
            'ext' => 'tar.gz'
        ],
        'x86_64-darwin' => [
            'file' => 'librcf-{{version}}-x86_64-apple-darwin',
            'checksum' => 'bfd2240cd40026791a6d9c44262f23a0a7439576c95d1d08b6b697a048aa16e9',
            'lib' => 'lib/librcf.dylib',
            'ext' => 'tar.gz'
        ],
        'arm64-darwin' => [
            'file' => 'librcf-{{version}}-aarch64-apple-darwin',
            'checksum' => '01fcb7b1330792223a5c7272ebe25a2ada2b081908331defe88242410ca5cddb',
            'lib' => 'lib/librcf.dylib',
            'ext' => 'tar.gz'
        ],
        'x64-windows' => [
            'file' => 'librcf-{{version}}-x86_64-pc-windows-msvc',
            'checksum' => '6f4d42a0e56b4845310868b5ac8990a6001ef5f2676053c40a244d53b03a1716',
            'lib' => 'lib/rcf.dll',
            'ext' => 'zip'
        ]
    ];

    public static function check($event = null)
    {
        $dest = self::defaultLib();
        if (file_exists($dest)) {
            echo "✔ librcf found\n";
            return;
        }

        $dir = self::libDir();
        if (!file_exists($dir)) {
            mkdir($dir);
        }

        echo "Downloading librcf...\n";

        $file = self::platform('file');
        $ext = self::platform('ext');
        $url = self::withVersion("https://github.com/ankane/librcf/releases/download/v{{version}}/$file.$ext");
        $contents = file_get_contents($url);

        $checksum = hash('sha256', $contents);
        if ($checksum != self::platform('checksum')) {
            throw new Exception("Bad checksum: $checksum");
        }

        $tempDest = tempnam(sys_get_temp_dir(), 'rcf') . '.' . $ext;
        file_put_contents($tempDest, $contents);

        $archive = new \PharData($tempDest);
        if ($ext != 'zip') {
            $archive = $archive->decompress();
        }
        $archive->extractTo(self::libDir());

        echo "✔ Success\n";
    }

    public static function defaultLib()
    {
        return self::libDir() . '/' . self::libFile();
    }

    private static function libDir()
    {
        return __DIR__ . '/../lib';
    }

    private static function libFile()
    {
        return self::withVersion('librcf-{{version}}/' . self::platform('lib'));
    }

    private static function platform($key)
    {
        return self::PLATFORMS[self::platformKey()][$key];
    }

    private static function platformKey()
    {
        if (PHP_OS_FAMILY == 'Windows') {
            return 'x64-windows';
        } elseif (PHP_OS_FAMILY == 'Darwin') {
            if (php_uname('m') == 'x86_64') {
                return 'x86_64-darwin';
            } else {
                return 'arm64-darwin';
            }
        } else {
            if (php_uname('m') == 'x86_64') {
                return 'x86_64-linux';
            } else {
                return 'aarch64-linux';
            }
        }
    }

    private static function withVersion($str)
    {
        return str_replace('{{version}}', self::VERSION, $str);
    }
}
