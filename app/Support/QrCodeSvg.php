<?php

namespace App\Support;

use InvalidArgumentException;

class QrCodeSvg
{
    private const VERSION = 3;
    private const SIZE = 29;
    private const DATA_CODEWORDS = 55;
    private const ECC_CODEWORDS = 15;

    public static function dataUri(string $payload, int $scale = 6): string
    {
        return 'data:image/svg+xml;base64,' . base64_encode(self::svg($payload, $scale));
    }

    public static function svg(string $payload, int $scale = 6): string
    {
        $matrix = self::matrix($payload);
        $size = self::SIZE * $scale;
        $rects = [];

        foreach ($matrix as $y => $row) {
            foreach ($row as $x => $dark) {
                if ($dark) {
                    $rects[] = sprintf('<rect x="%d" y="%d" width="%d" height="%d"/>', $x * $scale, $y * $scale, $scale, $scale);
                }
            }
        }

        return sprintf(
            '<svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 %1$d %1$d" width="%1$d" height="%1$d" role="img"><rect width="100%%" height="100%%" fill="#fff"/><g fill="#111827">%2$s</g></svg>',
            $size,
            implode('', $rects)
        );
    }

    private static function matrix(string $payload): array
    {
        $data = self::dataCodewords($payload);
        $codewords = array_merge($data, self::reedSolomon($data, self::ECC_CODEWORDS));
        $modules = array_fill(0, self::SIZE, array_fill(0, self::SIZE, false));
        $reserved = array_fill(0, self::SIZE, array_fill(0, self::SIZE, false));

        self::drawFunctionPatterns($modules, $reserved);
        self::drawCodewords($modules, $reserved, $codewords);
        self::applyMask($modules, $reserved, 0);
        self::drawFormatBits($modules, $reserved, 0);

        return $modules;
    }

    private static function dataCodewords(string $payload): array
    {
        $bytes = array_values(unpack('C*', $payload));
        if (count($bytes) > 53) {
            throw new InvalidArgumentException('QR payload is too long for local ticket QR generator.');
        }

        $bits = [0, 1, 0, 0];
        self::appendBits($bits, count($bytes), 8);
        foreach ($bytes as $byte) {
            self::appendBits($bits, $byte, 8);
        }

        $capacity = self::DATA_CODEWORDS * 8;
        $terminator = min(4, $capacity - count($bits));
        for ($i = 0; $i < $terminator; $i++) {
            $bits[] = 0;
        }
        while (count($bits) % 8 !== 0) {
            $bits[] = 0;
        }

        $codewords = [];
        for ($i = 0; $i < count($bits); $i += 8) {
            $value = 0;
            for ($j = 0; $j < 8; $j++) {
                $value = ($value << 1) | $bits[$i + $j];
            }
            $codewords[] = $value;
        }
        for ($pad = 0xec; count($codewords) < self::DATA_CODEWORDS; $pad ^= 0xec ^ 0x11) {
            $codewords[] = $pad;
        }

        return $codewords;
    }

    private static function appendBits(array &$bits, int $value, int $length): void
    {
        for ($i = $length - 1; $i >= 0; $i--) {
            $bits[] = ($value >> $i) & 1;
        }
    }

    private static function drawFunctionPatterns(array &$modules, array &$reserved): void
    {
        self::finder($modules, $reserved, 0, 0);
        self::finder($modules, $reserved, self::SIZE - 7, 0);
        self::finder($modules, $reserved, 0, self::SIZE - 7);

        for ($i = 8; $i < self::SIZE - 8; $i++) {
            self::set($modules, $reserved, $i, 6, $i % 2 === 0);
            self::set($modules, $reserved, 6, $i, $i % 2 === 0);
        }

        self::alignment($modules, $reserved, 22, 22);
        self::set($modules, $reserved, 8, self::SIZE - 8, true);

        for ($i = 0; $i < 9; $i++) {
            if ($i !== 6) {
                $reserved[8][$i] = $reserved[$i][8] = true;
            }
        }
        for ($i = self::SIZE - 8; $i < self::SIZE; $i++) {
            $reserved[8][$i] = $reserved[$i][8] = true;
        }
    }

    private static function finder(array &$modules, array &$reserved, int $x, int $y): void
    {
        for ($dy = -1; $dy <= 7; $dy++) {
            for ($dx = -1; $dx <= 7; $dx++) {
                $xx = $x + $dx; $yy = $y + $dy;
                if ($xx < 0 || $yy < 0 || $xx >= self::SIZE || $yy >= self::SIZE) continue;
                $dark = ($dx >= 0 && $dx <= 6 && $dy >= 0 && $dy <= 6)
                    && ($dx === 0 || $dx === 6 || $dy === 0 || $dy === 6 || ($dx >= 2 && $dx <= 4 && $dy >= 2 && $dy <= 4));
                self::set($modules, $reserved, $xx, $yy, $dark);
            }
        }
    }

    private static function alignment(array &$modules, array &$reserved, int $cx, int $cy): void
    {
        for ($dy = -2; $dy <= 2; $dy++) {
            for ($dx = -2; $dx <= 2; $dx++) {
                self::set($modules, $reserved, $cx + $dx, $cy + $dy, max(abs($dx), abs($dy)) !== 1);
            }
        }
    }

    private static function set(array &$modules, array &$reserved, int $x, int $y, bool $dark): void
    {
        $modules[$y][$x] = $dark;
        $reserved[$y][$x] = true;
    }

    private static function drawCodewords(array &$modules, array $reserved, array $codewords): void
    {
        $bits = [];
        foreach ($codewords as $codeword) {
            self::appendBits($bits, $codeword, 8);
        }

        $i = 0;
        $up = true;
        for ($right = self::SIZE - 1; $right >= 1; $right -= 2) {
            if ($right === 6) $right--;
            for ($vert = 0; $vert < self::SIZE; $vert++) {
                $y = $up ? self::SIZE - 1 - $vert : $vert;
                for ($j = 0; $j < 2; $j++) {
                    $x = $right - $j;
                    if (!$reserved[$y][$x]) {
                        $modules[$y][$x] = ($bits[$i++] ?? 0) === 1;
                    }
                }
            }
            $up = !$up;
        }
    }

    private static function applyMask(array &$modules, array $reserved, int $mask): void
    {
        for ($y = 0; $y < self::SIZE; $y++) {
            for ($x = 0; $x < self::SIZE; $x++) {
                if (!$reserved[$y][$x] && (($x + $y) % 2 === 0)) {
                    $modules[$y][$x] = !$modules[$y][$x];
                }
            }
        }
    }

    private static function drawFormatBits(array &$modules, array &$reserved, int $mask): void
    {
        $data = (1 << 3) | $mask; // Error correction L, mask 0.
        $bits = $data << 10;
        for ($i = 14; $i >= 10; $i--) {
            if (($bits >> $i) & 1) $bits ^= 0x537 << ($i - 10);
        }
        $format = (($data << 10) | $bits) ^ 0x5412;

        $a = [[8,0],[8,1],[8,2],[8,3],[8,4],[8,5],[8,7],[8,8],[7,8],[5,8],[4,8],[3,8],[2,8],[1,8],[0,8]];
        $b = [[self::SIZE-1,8],[self::SIZE-2,8],[self::SIZE-3,8],[self::SIZE-4,8],[self::SIZE-5,8],[self::SIZE-6,8],[self::SIZE-7,8],[8,self::SIZE-8],[8,self::SIZE-7],[8,self::SIZE-6],[8,self::SIZE-5],[8,self::SIZE-4],[8,self::SIZE-3],[8,self::SIZE-2],[8,self::SIZE-1]];
        for ($i = 0; $i < 15; $i++) {
            $dark = (($format >> $i) & 1) === 1;
            [$x, $y] = $a[$i]; self::set($modules, $reserved, $x, $y, $dark);
            [$x, $y] = $b[$i]; self::set($modules, $reserved, $x, $y, $dark);
        }
    }

    private static function reedSolomon(array $data, int $degree): array
    {
        $generator = [1];
        for ($i = 0; $i < $degree; $i++) {
            $generator[] = 0;
            for ($j = count($generator) - 1; $j > 0; $j--) {
                $generator[$j] = $generator[$j - 1] ^ self::gfMultiply($generator[$j], self::gfPow(2, $i));
            }
            $generator[0] = self::gfMultiply($generator[0], self::gfPow(2, $i));
        }

        $remainder = array_fill(0, $degree, 0);
        foreach ($data as $byte) {
            $factor = $byte ^ array_shift($remainder);
            $remainder[] = 0;
            for ($i = 0; $i < $degree; $i++) {
                $remainder[$i] ^= self::gfMultiply($generator[$degree - 1 - $i], $factor);
            }
        }

        return $remainder;
    }

    private static function gfPow(int $x, int $power): int
    {
        $result = 1;
        while ($power-- > 0) $result = self::gfMultiply($result, $x);
        return $result;
    }

    private static function gfMultiply(int $x, int $y): int
    {
        $z = 0;
        for ($i = 7; $i >= 0; $i--) {
            $z = ($z << 1) ^ ((($z >> 7) & 1) * 0x11d);
            if ((($y >> $i) & 1) !== 0) $z ^= $x;
        }
        return $z & 0xff;
    }
}
