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
            if ( preg_match('/\.scss/', $file)  ) {
                continue;
            }

            $contents = file_get_contents($file);
            preg_match_all('/\_\_\(\'(.*?)\'\)/', $contents, $matches);

            foreach ( $matches[1] as $match ) {
                $terms[] = $match;
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
