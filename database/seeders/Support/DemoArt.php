<?php

namespace Database\Seeders\Support;

use Illuminate\Support\Facades\Storage;

/**
 * Generates placeholder SVG artwork for the demo content (master plan, plot
 * layouts, gallery scenes) so the site looks complete before real photos
 * are uploaded. Every gallery image is labelled as a demo image.
 */
class DemoArt
{
    protected const NAVY = '#0B1F3A';

    protected const GOLD = '#C9A54C';

    public static function save(string $name, string $svg): string
    {
        $path = 'uploads/demo/'.$name.'.svg';
        Storage::disk('public')->put($path, $svg);

        return $path;
    }

    /**
     * Top-down master plan: blocks of plots, roads, parks and a central boulevard.
     */
    public static function masterPlan(int $width = 1920, int $height = 1080, bool $dark = true, int $seed = 7): string
    {
        mt_srand($seed);
        $bg1 = $dark ? '#0B1F3A' : '#F3EFE4';
        $bg2 = $dark ? '#123056' : '#E7DFC9';
        $block = $dark ? 'rgba(255,255,255,0.05)' : 'rgba(11,31,58,0.06)';
        $line = $dark ? 'rgba(255,255,255,0.16)' : 'rgba(11,31,58,0.18)';
        $road = $dark ? 'rgba(255,255,255,0.10)' : 'rgba(255,255,255,0.9)';
        $park = $dark ? 'rgba(74,163,110,0.28)' : 'rgba(74,163,110,0.35)';
        $gold = $dark ? 'rgba(201,165,76,0.55)' : 'rgba(201,165,76,0.7)';

        $svg = self::open($width, $height)
            ."<defs><linearGradient id='bg' x1='0' y1='0' x2='1' y2='1'><stop offset='0' stop-color='{$bg1}'/><stop offset='1' stop-color='{$bg2}'/></linearGradient>"
            ."<radialGradient id='glow' cx='0.7' cy='0.35' r='0.6'><stop offset='0' stop-color='".self::GOLD."' stop-opacity='".($dark ? '0.18' : '0.12')."'/><stop offset='1' stop-color='".self::GOLD."' stop-opacity='0'/></radialGradient></defs>"
            ."<rect width='{$width}' height='{$height}' fill='url(#bg)'/>";

        $blockW = 220;
        $blockH = 150;
        $gap = 34;

        for ($y = -40; $y < $height + $blockH; $y += $blockH + $gap) {
            for ($x = -60; $x < $width + $blockW; $x += $blockW + $gap) {
                $isPark = mt_rand(1, 100) <= 9;
                if ($isPark) {
                    $svg .= "<rect x='{$x}' y='{$y}' width='{$blockW}' height='{$blockH}' rx='18' fill='{$park}'/>";
                    for ($t = 0; $t < 7; $t++) {
                        $cx = $x + mt_rand(20, $blockW - 20);
                        $cy = $y + mt_rand(20, $blockH - 20);
                        $r = mt_rand(8, 16);
                        $svg .= "<circle cx='{$cx}' cy='{$cy}' r='{$r}' fill='{$park}'/>";
                    }

                    continue;
                }

                $svg .= "<rect x='{$x}' y='{$y}' width='{$blockW}' height='{$blockH}' rx='6' fill='{$block}' stroke='{$line}' stroke-width='1.2'/>";
                $cols = 6;
                $pw = $blockW / $cols;
                for ($c = 1; $c < $cols; $c++) {
                    $lx = round($x + $c * $pw, 1);
                    $svg .= "<line x1='{$lx}' y1='{$y}' x2='{$lx}' y2='".($y + $blockH)."' stroke='{$line}' stroke-width='1'/>";
                }
                $mid = $y + $blockH / 2;
                $svg .= "<line x1='{$x}' y1='{$mid}' x2='".($x + $blockW)."' y2='{$mid}' stroke='{$line}' stroke-width='1'/>";

                if (mt_rand(1, 100) <= 22) {
                    $px = round($x + mt_rand(0, $cols - 1) * $pw, 1);
                    $py = mt_rand(0, 1) ? $y : $mid;
                    $svg .= "<rect x='{$px}' y='{$py}' width='".round($pw, 1)."' height='".($blockH / 2)."' fill='{$gold}'/>";
                }
            }
        }

        // Main boulevards.
        $cy = round($height * 0.58);
        $cx = round($width * 0.62);
        $svg .= "<rect x='0' y='".($cy - 22)."' width='{$width}' height='44' fill='{$road}'/>"
            ."<line x1='0' y1='{$cy}' x2='{$width}' y2='{$cy}' stroke='".self::GOLD."' stroke-opacity='0.55' stroke-width='2' stroke-dasharray='18 14'/>"
            ."<rect x='".($cx - 22)."' y='0' width='44' height='{$height}' fill='{$road}'/>"
            ."<line x1='{$cx}' y1='0' x2='{$cx}' y2='{$height}' stroke='".self::GOLD."' stroke-opacity='0.55' stroke-width='2' stroke-dasharray='18 14'/>"
            ."<circle cx='{$cx}' cy='{$cy}' r='70' fill='{$bg1}' stroke='".self::GOLD."' stroke-opacity='0.7' stroke-width='3'/>"
            ."<circle cx='{$cx}' cy='{$cy}' r='46' fill='{$park}'/>"
            ."<rect width='{$width}' height='{$height}' fill='url(#glow)'/>";

        return $svg.'</svg>';
    }

    /**
     * Plot layout card: plot outline with dimensions, house footprint and label.
     */
    public static function plotLayout(string $label, string $dimensions, string $area, string $tone = '#C9A54C', bool $measurements = true): string
    {
        [$w, $h] = array_map('intval', explode('x', strtolower(str_replace(["'", ' ', '×'], ['', '', 'x'], $dimensions))) + [1 => 50]);
        $maxW = 470;
        $maxH = 300;
        $scale = min($maxW / max($w, 1), $maxH / max($h, 1));
        $pw = round($w * $scale);
        $ph = round($h * $scale);
        $x = round(400 - $pw / 2);
        $y = round(250 - $ph / 2) + 10;
        $houseW = round($pw * 0.62);
        $houseH = round($ph * 0.58);
        $hx = $x + round(($pw - $houseW) / 2);
        $hy = $y + round($ph * 0.12);

        $trees = '';
        foreach ([[70, 120], [110, 400], [720, 140], [690, 420], [60, 300], [740, 300]] as [$tx, $ty]) {
            $trees .= "<circle cx='{$tx}' cy='{$ty}' r='26' fill='#7FB38D' fill-opacity='0.55'/><circle cx='".($tx + 14)."' cy='".($ty - 10)."' r='16' fill='#5E9B70' fill-opacity='0.5'/>";
        }

        return self::open(800, 600)
            ."<defs><linearGradient id='g' x1='0' y1='0' x2='1' y2='1'><stop offset='0' stop-color='#FBF8F1'/><stop offset='1' stop-color='#EDE5D3'/></linearGradient>"
            ."<pattern id='grid' width='24' height='24' patternUnits='userSpaceOnUse'><path d='M24 0H0V24' fill='none' stroke='#0B1F3A' stroke-opacity='0.05'/></pattern></defs>"
            ."<rect width='800' height='600' fill='url(#g)'/><rect width='800' height='600' fill='url(#grid)'/>"
            ."<rect x='0' y='520' width='800' height='80' fill='#0B1F3A' fill-opacity='0.9'/>"
            ."<line x1='0' y1='560' x2='800' y2='560' stroke='{$tone}' stroke-width='3' stroke-dasharray='26 18'/>"
            .$trees
            ."<rect x='{$x}' y='{$y}' width='{$pw}' height='{$ph}' fill='#FFFFFF' stroke='".self::NAVY."' stroke-width='3' stroke-dasharray='10 7' rx='4'/>"
            ."<rect x='{$hx}' y='{$hy}' width='{$houseW}' height='{$houseH}' fill='{$tone}' fill-opacity='0.18' stroke='{$tone}' stroke-width='2.5' rx='3'/>"
            .($measurements
                ? "<line x1='{$x}' y1='".($y - 22)."' x2='".($x + $pw)."' y2='".($y - 22)."' stroke='".self::NAVY."' stroke-width='1.5'/>"
                    ."<line x1='{$x}' y1='".($y - 30)."' x2='{$x}' y2='".($y - 14)."' stroke='".self::NAVY."' stroke-width='1.5'/>"
                    ."<line x1='".($x + $pw)."' y1='".($y - 30)."' x2='".($x + $pw)."' y2='".($y - 14)."' stroke='".self::NAVY."' stroke-width='1.5'/>"
                    ."<rect x='".(400 - 34)."' y='".($y - 34)."' width='68' height='22' rx='11' fill='#FBF8F1'/>"
                    ."<text x='400' y='".($y - 18)."' text-anchor='middle' font-family='Inter, Arial, sans-serif' font-size='14' font-weight='600' fill='".self::NAVY."'>{$w} ft</text>"
                    ."<line x1='".($x + $pw + 22)."' y1='{$y}' x2='".($x + $pw + 22)."' y2='".($y + $ph)."' stroke='".self::NAVY."' stroke-width='1.5'/>"
                    ."<text x='".($x + $pw + 32)."' y='".($y + $ph / 2 + 5)."' font-family='Inter, Arial, sans-serif' font-size='14' font-weight='600' fill='".self::NAVY."'>{$h} ft</text>"
                : '')
            ."<text x='400' y='".($hy + $houseH / 2 - 4)."' text-anchor='middle' font-family='Georgia, serif' font-size='40' font-weight='700' fill='".self::NAVY."'>".e(strtoupper($label)).'</text>'
            ."<text x='400' y='".($hy + $houseH / 2 + 24)."' text-anchor='middle' font-family='Inter, Arial, sans-serif' font-size='15' fill='".self::NAVY."' fill-opacity='0.7'>".e($area).'</text>'
            .'</svg>';
    }

    /**
     * Illustrated gallery scene for a category, clearly labelled as a demo image.
     */
    public static function scene(string $category, int $variant = 0, int $width = 1200, int $height = 800): string
    {
        $palettes = [
            ['#F6D9A8', '#F2B880', '#8DB580'],
            ['#BFD8F2', '#E6EEF7', '#86B07A'],
            ['#F4C7A1', '#F9E3C8', '#9CBF85'],
            ['#C9DDF0', '#F0E6D2', '#7FA874'],
        ];
        [$sky1, $sky2, $grass] = $palettes[$variant % count($palettes)];
        $horizon = round($height * 0.62);

        $svg = self::open($width, $height)
            ."<defs><linearGradient id='sky' x1='0' y1='0' x2='0' y2='1'><stop offset='0' stop-color='{$sky1}'/><stop offset='1' stop-color='{$sky2}'/></linearGradient></defs>"
            ."<rect width='{$width}' height='{$height}' fill='url(#sky)'/>"
            ."<circle cx='".round($width * (0.15 + 0.2 * ($variant % 3)))."' cy='".round($height * 0.2)."' r='".round($height * 0.07)."' fill='#FFF6DD' fill-opacity='0.9'/>"
            ."<path d='M0 {$horizon} Q ".round($width * 0.25).' '.($horizon - 40).' '.round($width * 0.5)." {$horizon} T {$width} {$horizon} V {$height} H 0 Z' fill='{$grass}'/>";

        $svg .= match ($category) {
            'roads' => self::sceneRoad($width, $height, $horizon),
            'parks' => self::sceneTrees($width, $height, $horizon, 9).self::scenePath($width, $height, $horizon),
            'development', 'construction' => self::sceneCrane($width, $height, $horizon).self::sceneBuildings($width, $height, $horizon, 4),
            'events' => self::sceneBunting($width, $height).self::sceneTent($width, $height, $horizon),
            'site-visit' => self::sceneBuildings($width, $height, $horizon, 2).self::scenePeople($width, $height, $horizon),
            'possession' => self::sceneHouse($width, $height, $horizon),
            default => self::sceneGate($width, $height, $horizon).self::sceneTrees($width, $height, $horizon, 4),
        };

        $tag = 'Demo image · '.ucwords(str_replace('-', ' ', $category));

        return $svg."<rect x='24' y='".($height - 64)."' rx='18' width='".(strlen($tag) * 9 + 36)."' height='38' fill='".self::NAVY."' fill-opacity='0.78'/>"
            ."<text x='42' y='".($height - 39)."' font-family='Inter, Arial, sans-serif' font-size='16' font-weight='600' fill='#FFFFFF'>".e($tag).'</text></svg>';
    }

    /**
     * Stylised area map with roads and a location pin.
     */
    public static function areaMap(int $width = 1200, int $height = 800): string
    {
        return self::open($width, $height)
            ."<rect width='{$width}' height='{$height}' fill='#EEF1EA'/>"
            ."<path d='M-20 520 C 300 470, 520 600, 1220 430' stroke='#FFFFFF' stroke-width='46' fill='none'/>"
            ."<path d='M-20 520 C 300 470, 520 600, 1220 430' stroke='#F2C46D' stroke-width='6' fill='none'/>"
            ."<path d='M380 -20 C 420 300, 330 520, 460 820' stroke='#FFFFFF' stroke-width='30' fill='none'/>"
            ."<path d='M820 -20 L 760 820' stroke='#FFFFFF' stroke-width='22' fill='none'/>"
            ."<path d='M-20 200 L 1220 260' stroke='#FFFFFF' stroke-width='18' fill='none'/>"
            ."<rect x='520' y='280' width='240' height='170' rx='16' fill='".self::NAVY."' fill-opacity='0.1' stroke='".self::NAVY."' stroke-opacity='0.4' stroke-width='3' stroke-dasharray='12 8'/>"
            ."<circle cx='170' cy='640' r='70' fill='#B9D7A8'/><circle cx='1010' cy='640' r='90' fill='#B9D7A8'/><circle cx='980' cy='120' r='60' fill='#B9D7A8'/>"
            ."<path d='M640 210 c-40 0-70 30-70 68 0 52 70 118 70 118s70-66 70-118c0-38-30-68-70-68z' fill='".self::GOLD."' stroke='".self::NAVY."' stroke-width='4'/>"
            ."<circle cx='640' cy='278' r='22' fill='".self::NAVY."'/>"
            ."<text x='640' y='490' text-anchor='middle' font-family='Georgia, serif' font-size='30' font-weight='700' fill='".self::NAVY."'>Project Site</text>"
            ."<text x='640' y='522' text-anchor='middle' font-family='Inter, Arial, sans-serif' font-size='16' fill='".self::NAVY."' fill-opacity='0.7'>Illustrative map · not to scale</text>"
            .'</svg>';
    }

    protected static function open(int $width, int $height): string
    {
        return "<svg xmlns='http://www.w3.org/2000/svg' viewBox='0 0 {$width} {$height}' width='{$width}' height='{$height}' preserveAspectRatio='xMidYMid slice'>";
    }

    protected static function sceneRoad(int $w, int $h, int $horizon): string
    {
        $mid = $w / 2;
        $svg = "<path d='M".($mid - 30)." {$horizon} L ".($mid + 30)." {$horizon} L ".($w * 0.85)." {$h} L ".($w * 0.15)." {$h} Z' fill='#4A5466'/>";
        for ($i = 0; $i < 7; $i++) {
            $t1 = $i / 7;
            $t2 = ($i + 0.45) / 7;
            $y1 = $horizon + ($h - $horizon) * $t1 * $t1;
            $y2 = $horizon + ($h - $horizon) * $t2 * $t2;
            $svg .= "<line x1='{$mid}' y1='".round($y1)."' x2='{$mid}' y2='".round($y2)."' stroke='#F5D27A' stroke-width='".round(2 + 10 * $t2)."'/>";
        }
        foreach ([0.2, 0.32, 0.68, 0.8] as $i => $fx) {
            $x = round($w * $fx);
            $top = $horizon - 80 - $i * 10;
            $svg .= "<line x1='{$x}' y1='".($horizon + 10)."' x2='{$x}' y2='{$top}' stroke='#2F3A4B' stroke-width='5'/><circle cx='{$x}' cy='{$top}' r='9' fill='#FFE7A3'/>";
        }

        return $svg.self::sceneTrees($w, $h, $horizon, 4);
    }

    protected static function sceneTrees(int $w, int $h, int $horizon, int $count): string
    {
        $svg = '';
        for ($i = 0; $i < $count; $i++) {
            $x = round(($i + 0.5) * $w / $count + (($i % 2) ? 30 : -30));
            $size = 46 + ($i * 13) % 40;
            $base = $horizon + 20 + ($i * 37) % 90;
            $svg .= "<rect x='".($x - 6)."' y='".($base - $size)."' width='12' height='{$size}' fill='#6B4F3A'/>"
                ."<circle cx='{$x}' cy='".($base - $size)."' r='".round($size * 0.8)."' fill='#4F8A5B'/>"
                ."<circle cx='".($x + round($size * 0.35))."' cy='".($base - $size - 12)."' r='".round($size * 0.5)."' fill='#5E9B6A'/>";
        }

        return $svg;
    }

    protected static function scenePath(int $w, int $h, int $horizon): string
    {
        return "<path d='M".($w * 0.45)." {$h} C ".($w * 0.5).' '.($horizon + 120).', '.($w * 0.62).' '.($horizon + 80).', '.($w * 0.55)." {$horizon}' stroke='#EBDDBF' stroke-width='60' fill='none' stroke-linecap='round'/>";
    }

    protected static function sceneBuildings(int $w, int $h, int $horizon, int $count): string
    {
        $svg = '';
        for ($i = 0; $i < $count; $i++) {
            $bw = 150;
            $bh = 140 + ($i * 53) % 120;
            $x = round($w * 0.42 + $i * ($bw + 18));
            if ($x + $bw > $w) {
                break;
            }
            $y = $horizon - $bh + 30;
            $svg .= "<rect x='{$x}' y='{$y}' width='{$bw}' height='{$bh}' fill='#E9E2D2' stroke='#B7AA8D' stroke-width='3'/>";
            for ($r = 0; $r < floor(($bh - 30) / 40); $r++) {
                for ($c = 0; $c < 3; $c++) {
                    $svg .= "<rect x='".($x + 18 + $c * 44)."' y='".($y + 18 + $r * 40)."' width='26' height='22' fill='#7FA6C9' fill-opacity='0.8'/>";
                }
            }
        }

        return $svg;
    }

    protected static function sceneCrane(int $w, int $h, int $horizon): string
    {
        $x = round($w * 0.24);
        $top = round($h * 0.14);

        return "<rect x='{$x}' y='{$top}' width='22' height='".($horizon - $top + 40)."' fill='#E3A82B'/>"
            ."<path d='M{$x} {$top} h22 m-22 40 l22 -40 m-22 80 l22 -40 m-22 80 l22 -40' stroke='#B47F12' stroke-width='3'/>"
            ."<rect x='".($x - 160)."' y='".($top - 18)."' width='".(round($w * 0.38) + 160)."' height='18' fill='#E3A82B'/>"
            ."<line x1='".($x + round($w * 0.3))."' y1='{$top}' x2='".($x + round($w * 0.3))."' y2='".($top + 230)."' stroke='#3B3B3B' stroke-width='3'/>"
            ."<rect x='".($x + round($w * 0.3) - 34)."' y='".($top + 230)."' width='68' height='40' fill='#C46B3C'/>"
            ."<rect x='".($x - 160)."' y='".($top - 4)."' width='60' height='40' fill='#4A5466'/>";
    }

    protected static function sceneBunting(int $w, int $h): string
    {
        $svg = "<path d='M0 90 Q ".($w / 2)." 190 {$w} 90' stroke='#3B3B3B' stroke-width='3' fill='none'/>";
        $colors = ['#C9A54C', '#0B1F3A', '#D9534F', '#4F8A5B'];
        for ($i = 1; $i < 14; $i++) {
            $x = $i * $w / 14;
            $y = 90 + 100 * (1 - pow(2 * $i / 14 - 1, 2));
            $svg .= "<path d='M".round($x - 22).' '.round($y).' L '.round($x + 22).' '.round($y).' L '.round($x).' '.round($y + 46)." Z' fill='".$colors[$i % 4]."'/>";
        }

        return $svg;
    }

    protected static function sceneTent(int $w, int $h, int $horizon): string
    {
        $x = round($w * 0.3);
        $tw = round($w * 0.4);

        return "<path d='M{$x} ".($horizon + 40).' L '.($x + $tw / 2).' '.($horizon - 170).' L '.($x + $tw).' '.($horizon + 40)." Z' fill='#FFFFFF' stroke='#C9A54C' stroke-width='5'/>"
            ."<path d='M".($x + $tw / 2 - 60).' '.($horizon + 40).' L '.($x + $tw / 2).' '.($horizon - 60).' L '.($x + $tw / 2 + 60).' '.($horizon + 40)." Z' fill='#0B1F3A'/>";
    }

    protected static function scenePeople(int $w, int $h, int $horizon): string
    {
        $svg = '';
        foreach ([0.18, 0.25, 0.31, 0.62, 0.7] as $i => $fx) {
            $x = round($w * $fx);
            $base = $horizon + 120 + ($i % 2) * 30;
            $color = ['#0B1F3A', '#C9A54C', '#4A5466', '#7A4E3A', '#2F6F8F'][$i];
            $svg .= "<circle cx='{$x}' cy='".($base - 150)."' r='20' fill='#D9A57A'/>"
                ."<rect x='".($x - 24)."' y='".($base - 126)."' width='48' height='90' rx='20' fill='{$color}'/>"
                ."<rect x='".($x - 18)."' y='".($base - 46)."' width='14' height='50' fill='#2B2B2B'/><rect x='".($x + 4)."' y='".($base - 46)."' width='14' height='50' fill='#2B2B2B'/>";
        }

        return $svg;
    }

    protected static function sceneHouse(int $w, int $h, int $horizon): string
    {
        $x = round($w * 0.32);
        $hw = round($w * 0.36);
        $hy = $horizon - 150;

        return "<rect x='{$x}' y='{$hy}' width='{$hw}' height='210' fill='#F4EEE2' stroke='#B7AA8D' stroke-width='4'/>"
            ."<path d='M".($x - 30)." {$hy} L ".($x + $hw / 2).' '.($hy - 130).' L '.($x + $hw + 30)." {$hy} Z' fill='#0B1F3A'/>"
            ."<rect x='".($x + $hw / 2 - 40)."' y='".($hy + 90)."' width='80' height='120' fill='#8A5A3B'/>"
            ."<rect x='".($x + 40)."' y='".($hy + 50)."' width='80' height='60' fill='#7FA6C9'/><rect x='".($x + $hw - 120)."' y='".($hy + 50)."' width='80' height='60' fill='#7FA6C9'/>"
            ."<g transform='translate(".round($w * 0.76).' '.round($h * 0.28).")'><circle r='46' fill='#C9A54C'/><circle r='18' fill='#FFF6DD'/><rect x='40' y='-10' width='110' height='20' rx='6' fill='#C9A54C'/><rect x='118' y='10' width='14' height='26' fill='#C9A54C'/><rect x='94' y='10' width='14' height='18' fill='#C9A54C'/></g>";
    }

    protected static function sceneGate(int $w, int $h, int $horizon): string
    {
        $x = round($w * 0.3);
        $gw = round($w * 0.4);
        $top = $horizon - 260;

        return "<rect x='{$x}' y='{$top}' width='70' height='300' fill='#E9E2D2' stroke='#B7AA8D' stroke-width='3'/>"
            ."<rect x='".($x + $gw - 70)."' y='{$top}' width='70' height='300' fill='#E9E2D2' stroke='#B7AA8D' stroke-width='3'/>"
            ."<path d='M{$x} {$top} Q ".($x + $gw / 2).' '.($top - 120).' '.($x + $gw)." {$top} L ".($x + $gw).' '.($top + 50).' Q '.($x + $gw / 2).' '.($top - 60)." {$x} ".($top + 50)." Z' fill='#0B1F3A'/>"
            ."<rect x='".($x + $gw / 2 - 110)."' y='".($top - 40)."' width='220' height='34' rx='6' fill='#C9A54C'/>"
            ."<path d='M".($x + 70).' '.($horizon + 40).' L '.($x + 70).' '.($top + 80).' M '.($x + $gw - 70).' '.($horizon + 40).' L '.($x + $gw - 70).' '.($top + 80)."' stroke='#3B3B3B' stroke-width='3'/>";
    }
}
