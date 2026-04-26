<?php

declare(strict_types=1);

namespace Visualbuilder\FilamentDesignSystem\Theme;

use InvalidArgumentException;

/**
 * Generates an 11-shade ramp from a single hex seed.
 *
 * The seed is anchored at a chosen shade (default 500). Other shades are
 * computed by setting target lightness on a Tailwind-ish curve while
 * preserving hue. Saturation is gently rolled off at the very pale (50/100)
 * and very dark (900/950) ends so washed-out tints and inky shadows still
 * look natural.
 *
 * The output is a colour-string ramp suitable for write_tokens —
 * { "50": "#…", "100": "#…", … "950": "#…" }.
 */
class PaletteGenerator
{
    /**
     * Target lightness per shade (0..1). Anchored on 500 ≈ 0.55 to match
     * Tailwind's perceptual mid-tone.
     */
    public const LIGHTNESS_CURVE = [
        '50' => 0.97,
        '100' => 0.93,
        '200' => 0.86,
        '300' => 0.77,
        '400' => 0.66,
        '500' => 0.55,
        '600' => 0.46,
        '700' => 0.38,
        '800' => 0.29,
        '900' => 0.21,
        '950' => 0.13,
    ];

    /**
     * @return array<string, string>
     */
    public static function generate(string $hex, string $anchor = '500'): array
    {
        $hex = static::normaliseHex($hex);

        if (! array_key_exists($anchor, self::LIGHTNESS_CURVE)) {
            throw new InvalidArgumentException(
                "anchor must be one of: " . implode(', ', array_keys(self::LIGHTNESS_CURVE)) . ".",
            );
        }

        [$h, $s] = static::hexToHsl($hex);

        $palette = [];

        // PHP coerces numeric string array keys to integers, so we cast back to
        // strings inside the loop. The anchor arg always arrives as a string.
        foreach (self::LIGHTNESS_CURVE as $shade => $targetL) {
            $key = (string) $shade;

            if ($key === $anchor) {
                $palette[$key] = '#' . $hex;
                continue;
            }

            $shadeNum = (int) $shade;
            $sMul = match (true) {
                $shadeNum <= 100 => 0.55,
                $shadeNum <= 200 => 0.78,
                $shadeNum >= 950 => 0.70,
                $shadeNum >= 900 => 0.82,
                $shadeNum >= 800 => 0.92,
                default => 1.0,
            };

            $palette[$key] = static::hslToHex($h, max(0.0, min(1.0, $s * $sMul)), $targetL);
        }

        return $palette;
    }

    protected static function normaliseHex(string $hex): string
    {
        $hex = ltrim(trim($hex), '#');

        if (strlen($hex) === 3) {
            $hex = $hex[0] . $hex[0] . $hex[1] . $hex[1] . $hex[2] . $hex[2];
        }

        if (! preg_match('/^[0-9a-f]{6}$/i', $hex)) {
            throw new InvalidArgumentException("Not a hex colour: #{$hex}");
        }

        return strtolower($hex);
    }

    /**
     * @return array{0: float, 1: float, 2: float} Hue (0–360), saturation (0–1), lightness (0–1).
     */
    protected static function hexToHsl(string $hex): array
    {
        $r = hexdec(substr($hex, 0, 2)) / 255;
        $g = hexdec(substr($hex, 2, 2)) / 255;
        $b = hexdec(substr($hex, 4, 2)) / 255;

        $max = max($r, $g, $b);
        $min = min($r, $g, $b);
        $l = ($max + $min) / 2;

        if ($max === $min) {
            return [0.0, 0.0, $l];
        }

        $d = $max - $min;
        $s = $l > 0.5 ? $d / (2 - $max - $min) : $d / ($max + $min);

        $h = match (true) {
            $r === $max => ($g - $b) / $d + ($g < $b ? 6 : 0),
            $g === $max => ($b - $r) / $d + 2,
            default => ($r - $g) / $d + 4,
        } * 60;

        return [$h, $s, $l];
    }

    protected static function hslToHex(float $h, float $s, float $l): string
    {
        $h = fmod($h, 360);
        if ($h < 0) {
            $h += 360;
        }
        $h /= 360;

        if ($s === 0.0) {
            $r = $g = $b = $l;
        } else {
            $q = $l < 0.5 ? $l * (1 + $s) : $l + $s - $l * $s;
            $p = 2 * $l - $q;
            $r = static::hueToRgb($p, $q, $h + 1 / 3);
            $g = static::hueToRgb($p, $q, $h);
            $b = static::hueToRgb($p, $q, $h - 1 / 3);
        }

        return sprintf('#%02x%02x%02x', (int) round($r * 255), (int) round($g * 255), (int) round($b * 255));
    }

    protected static function hueToRgb(float $p, float $q, float $t): float
    {
        if ($t < 0) {
            $t += 1;
        }
        if ($t > 1) {
            $t -= 1;
        }
        if ($t < 1 / 6) {
            return $p + ($q - $p) * 6 * $t;
        }
        if ($t < 1 / 2) {
            return $q;
        }
        if ($t < 2 / 3) {
            return $p + ($q - $p) * (2 / 3 - $t) * 6;
        }
        return $p;
    }
}
