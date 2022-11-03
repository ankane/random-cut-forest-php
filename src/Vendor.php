<?php

namespace Rcf;

class Vendor
{
    public const VERSION = '0.1.0';

    public const PLATFORMS = [
        'x86_64-linux' => [
            'file' => 'librcf-{{version}}-x86_64-unknown-linux-gnu',
            'checksum' => '8183afdc540c969cd30706a2b8d263fe2b843e0c4f7bab546547ac00b5897bb1',
            'lib' => 'lib/librcf.so',
            'ext' => 'tar.gz'
        ],
        'aarch64-linux' => [
            'file' => 'librcf-{{version}}-aarch64-unknown-linux-gnu',
            'checksum' => '9eca9d77f70593eb64a61345ae86c2da3958c21b626680b5f06bdd73648fc8d3',
            'lib' => 'lib/librcf.so',
            'ext' => 'tar.gz'
        ],
        'x86_64-darwin' => [
            'file' => 'librcf-{{version}}-x86_64-apple-darwin',
            'checksum' => 'f48b030f8c6a86aa8fd4f9edb85cab2e9d87ad763adb9a589ae7a0a4895f76f0',
            'lib' => 'lib/librcf.dylib',
            'ext' => 'tar.gz'
        ],
        'arm64-darwin' => [
            'file' => 'librcf-{{version}}-aarch64-apple-darwin',
            'checksum' => 'a992eaaca2e3039d743ba6d7379ae2fe8fe4d2fef7010f25334c41a22cd247a5',
            'lib' => 'lib/librcf.dylib',
            'ext' => 'tar.gz'
        ],
        'x64-windows' => [
            'file' => 'librcf-{{version}}-x86_64-pc-windows-msvc',
            'checksum' => 'aaf7beb33c1ce879cb9e2c24fc17447d2d3d2846c2b002532098b4dbce3a03aa',
            'lib' => 'lib/rcf.dll',
            'ext' => 'zip'
        ]
    ];

    public static function check($event)
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
        return self::platform('lib');
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
