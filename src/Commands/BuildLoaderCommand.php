<?php
namespace LumiVueArtisan\Commands;

class BuildLoaderCommand implements CommandInterface
{
    private static $packages;

    public static function run($options) {
        self::$packages = self::getPackages();
        self::buildLoader();
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
            if ( in_array($match, ['@lumi', '@lumi-electron']) ) {
                continue;
            }

            $packages[] = $match;
        }

        return $packages;
    }

    private static function buildLoader() {
        $ignored_packages = isset($_ENV['LUMI_LOADER_IGNORED_PACKAGES']) ? explode(',', $_ENV['LUMI_LOADER_IGNORED_PACKAGES']) : [];

        $loader_components = [
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
                'name' => 'store',
                'path' => '/store/',
                'extension' => 'js',
            ],
        ];

        $code = "const Loader = {";

        foreach ( $loader_components as $loader_component ) {
            $code .= "\n\t".$loader_component['name']."() {\n\t\tvar contexts = {};\n";

            foreach ( self::$packages as $package ) {
                $package = str_replace('@', '', $package);

                if ( $package == '' ) {
                    $module_path = 'src'.$loader_component['path'];
                    $package_namespace = '';
                }
                else {
                    //check if package is ignored
                    if ( in_array($package, $ignored_packages) ) {
                        continue;
                    }

                    $module_path = $_ENV[strtoupper(str_replace('-', '_', $package)).'_PATH'].$loader_component['path'];
                    $package_namespace = str_replace(' ', '', ucwords(str_replace('-', ' ', $package)));
                }


                if ( !file_exists($module_path) ) {
                    continue;
                }

                $code .= "\n\t\tcontexts['".$package_namespace."'] = require.context(`@".$package.$loader_component['path']."`, true, /\.".$loader_component['extension']."/);";
            }

            $code .= "\n\n\t\treturn contexts;\n\t},";
        }

        $code .= "\n}\n\nexport default Loader;";

        file_put_contents('src/loader.js', $code);
    }
}
