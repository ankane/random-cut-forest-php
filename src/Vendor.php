<?php

namespace Rcf;

class Vendor
{
    public const VERSION = '0.1.1';

    public const PLATFORMS = [
        'x86_64-linux' => [
            'file' => 'librcf-{{version}}-x86_64-unknown-linux-gnu',
            'checksum' => '66fc7c806be465e750e16eeb8392c6345082696b13145f84bb1f702a882f950f',
            'lib' => 'lib/librcf.so',
            'ext' => 'tar.gz'
        ],
        'aarch64-linux' => [
            'file' => 'librcf-{{version}}-aarch64-unknown-linux-gnu',
            'checksum' => 'ae2f95086dbc2d3d06fce234de85b997f1dfde9bc4dfaf5ae4bf02fce20c2005',
            'lib' => 'lib/librcf.so',
            'ext' => 'tar.gz'
        ],
        'x86_64-darwin' => [
            'file' => 'librcf-{{version}}-x86_64-apple-darwin',
            'checksum' => 'a41e04a3cb7074001f741abe37697e1e278364cf81e450188c68bd3ecdebe89c',
            'lib' => 'lib/librcf.dylib',
            'ext' => 'tar.gz'
        ],
        'arm64-darwin' => [
            'file' => 'librcf-{{version}}-aarch64-apple-darwin',
            'checksum' => 'ae7adfd05a0f81c07b50d87f546dc542ba12acf2407d39e466e576c953e77523',
            'lib' => 'lib/librcf.dylib',
            'ext' => 'tar.gz'
        ],
        'x64-windows' => [
            'file' => 'librcf-{{version}}-x86_64-pc-windows-msvc',
            'checksum' => '18e3c1989a16091e66afa2e13cc598cd2409d995be0f6b5036b7d4fed2f13fe8',
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
