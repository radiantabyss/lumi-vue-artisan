<?php
namespace LumiVueBuilder\Commands;

class BuildLoaderCommand implements CommandInterface
{
    private static $options = [];
    private static $packages;

    public static function run($options) {
        self::$packages = self::getPackages();
        self::build();
    }

    private static function getPackages() {
        if ( !file_exists('vue.config.js') ) {
            throw new \Exception('vue.config.js not found.');
        }

        $vue_config = file_get_contents('vue.config.js');
        preg_match('/configureWebpack:(\s+|){.*resolve.*alias:(\s+|){.*?}/is', $vue_config, $match);

        if ( !$match ) {
            throw new \Exception('Parsing vue.config.js failed.');
        }

        preg_match_all('/(\'|")(@.*?)(\'|")/', $match[0], $matches);

        $packages = ['@'];
        foreach ( $matches[2] as $match ) {
            if ( in_array($match, ['@lumi', '@lumi-loader']) ) {
                continue;
            }

            $packages[] = $match;
        }

        return $packages;
    }

    private static function build() {
        $items = [
            [
                'name' => 'actions',
                'path' => '/http/actions/',
                'extension' => 'vue',
            ],
            [
                'name' => 'components',
                'path' => '/components/',
                'extension' => 'vue',
            ],
            [
                'name' => 'middleware',
                'path' => '/http/middleware/',
                'extension' => 'js',
            ],
            [
                'name' => 'modals',
                'path' => '/modals/',
                'extension' => 'vue',
            ],
            [
                'name' => 'routes',
                'path' => '/routes/',
                'extension' => 'js',
            ],
            [
                'name' => 'store',
                'path' => '/store/',
                'extension' => 'js',
            ],
        ];

        $code = "const Loader = {";

        foreach ( $items as $item ) {
            $code .= "\n\t".$item['name']."() {\n\t\tvar contexts = [];\n";

            foreach ( self::$packages as $package ) {
                if ( $package == '@' ) {
                    $module_path = 'src'.$item['path'];
                }
                else {
                    $module_path = $_ENV[strtoupper(str_replace('-', '_', str_replace('@', '', $package))).'_PATH'].$item['path'];
                }

                if ( !file_exists($module_path) ) {
                    continue;
                }

                $code .= "\n\t\tcontexts = contexts.concat(require.context(`".$package.$item['path']."`, true, /\.".$item['extension']."/));";
            }

            $code .= "\n\n\t\treturn contexts;\n\t},";
        }

        $code .= "\n}\n\nexport default Loader;";

        file_put_contents('src/loader.js', $code);
    }
}
