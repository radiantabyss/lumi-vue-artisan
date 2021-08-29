<?php
namespace LumiVueBuilder;

class Spritesmith
{
    public static function run() {
        self::png();
        self::svg();
    }

    private static function png() {
        exec('npm run sprite');
    }

    private static function svg() {
        exec('npx svg-sprite-generate -d sprites/svgs -o sprites.svg');

        $vue_component = '<template>
        <svg xmlns="http://www.w3.org/2000/svg" style="position: absolute; width: 0; height: 0; overflow: hidden;">

        </svg>
        </template>

        <script>
        export default {
            name: \'SvgSpritesComponent\'
        }
        </script>';

        $sprites = file_get_contents('sprites.svg');
        $sprites = preg_replace('/stroke=".*?"/', 'stroke="currentColor"', $sprites);
        $sprites = preg_replace('/fill=".*?"/', 'fill="currentColor"', $sprites);
        $sprites = str_replace('fill-static', 'fill', $sprites);
        $sprites = str_replace('stroke-static', 'stroke', $sprites);
        $sprites = str_replace('<symbol ', '<symbol fill="currentColor" ', $sprites);
        $sprites = str_replace(['<?xml version="1.0" encoding="utf-8"?>', '</svg>', '<svg xmlns="http://www.w3.org/2000/svg">'], '', $sprites);
        $sprites = str_replace(["\n</symbol>", "\r\n</symbol>"], "</symbol>\n", $sprites);
        $sprites = trim($sprites);

        //handle no-fill custom attribute
        $svgs = scandir('sprites/svgs');
        foreach ( $svgs as $svg ) {
            if ( in_array($svg, ['.', '..']) ) {
                continue;
            }

            $contents = file_get_contents('sprites/svgs/'.$svg);
            if ( preg_match('/no-fill/', $contents) ) {
                preg_match('/\<symbol.*?id="'.str_replace('.svg', '', $svg).'".*?\>/', $sprites, $match);
                $sprites = str_replace($match[0], str_replace('fill="currentColor" ', ' fill="none" ', $match[0]), $sprites);
            }
        }

        $vue_component = str_replace('</svg>', $sprites."\n\n</svg>", $vue_component);
        file_put_contents('src/vues/global-components/SvgSpritesComponent.vue', $vue_component);

        unlink('sprites.svg');
    }
}
