<?php
namespace Lumi\VueArtisan\Commands;

class LangCommand implements CommandInterface
{
    public static function run($options) {
        $terms = self::terms();
        $langs = self::langs();

        foreach ( $langs as $lang ) {
            $lang_contents = isset($options['force']) ? [] : decode_json(file_get_contents('static/lang/'.$lang.'.json', 'UTF-8'));

            foreach ( $terms as $term ) {
                $lang_contents[$term] = $lang_contents[$term] ?? $term;
            }

            file_put_contents('static/lang/'.$lang.'.json', json_encode($lang_contents, JSON_PRETTY_PRINT|JSON_UNESCAPED_UNICODE));
        }
    }

    private static function terms() {
        $files = get_files_recursive('src');
        $terms = [];

        foreach ( $files as $file ) {
            //ignore scss files
            if ( preg_match('/\.scss/', $file)  ) {
                continue;
            }

            $contents = file_get_contents($file);

            $regexs = [
                '/\_\_\(\'(.*?)\'\)/', // __() function
                '/<t>(.*?)<\/t>/s', // <t> tags
            ];

            foreach ( $regexs as $regex ) {
                preg_match_all($regex, $contents, $matches);

                if ( !count($matches) || !count($matches[1]) ) {
                    continue;
                }
                
                foreach ( $matches[1] as $match ) {
                    $terms[] = $match;
                }
            }
        }

        return array_unique($terms);
    }

    private static function langs() {
        $files = scandir('static/lang');
        $langs = [];

        foreach ( $files as $file ) {
            if ( preg_match('/\.json/', $file) ) {
                $langs[] = str_replace('.json', '', $file);
            }
        }

        return $langs;
    }
}
