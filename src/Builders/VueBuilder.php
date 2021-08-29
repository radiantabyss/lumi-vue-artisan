<?php
namespace LumiVueBuilder\Builders;

class VueBuilder implements BuilderInterface
{
    private static $options;

    public static function run($options) {
        //set options
        self::$options = $options;

        //build
        self::build();

        //write index.php
        self::writeIndex();

        //create htaccess file for apache servers
        self::htaccess();

        //copy static content
        self::copyStatic();

        //set error page
        self::errorPage();

        //handle dark mode
        self::darkModeRemover();
    }

    private static function build() {
        exec('npm run build');

        if ( !file_exists('dist') ) {
            throw new \Exception('Vue Builder failed.');
        }
    }

    private static function writeIndex() {
        //get file names
        $file_names = self::getFileNames();

        //get api url
        $api_url = $_ENV['VUE_APP_API_URL'];

        //replace in index src
        $index_src_contents = file_get_contents('index-src.php');
        $index_src_contents = str_replace(
            ['{{app_css}}', '{{app_js}}', '{{vendors_js}}', '{{api_url}}'],
            [$file_names['app_css'], $file_names['app_js'], $file_names['vendors_js'], $api_url],
            $index_src_contents
        );

        file_put_contents('dist/index.php', $index_src_contents);
    }

    private static function getFileNames() {
        $app_css = $app_js = $vendors_js = null;

        $css_files = scandir('dist/css');
        foreach ( $css_files as $file ) {
            if ( preg_match('/app/', $file) && !preg_match('/map/', $file) ) {
                $app_css = $file;
            }

            //delete map files
            if ( preg_match('/map/', $file) ) {
                unlink('dist/css/'.$file);
            }
        }

        $js_files = scandir('dist/js');
        foreach ( $js_files as $file ) {
            if ( preg_match('/app/', $file) && !preg_match('/map/', $file) ) {
                $app_js = $file;
            }

            if ( preg_match('/chunk/', $file) && !preg_match('/map/', $file) ) {
                $vendors_js = $file;
            }

            //delete map files
            if ( preg_match('/map/', $file) ) {
                unlink('dist/js/'.$file);
            }
        }

        return compact('app_css', 'app_js', 'vendors_js');
    }

    private static function htaccess() {
        file_put_contents('dist/.htaccess', '<IfModule mod_rewrite.c>
            RewriteEngine On
            RewriteBase /
            RewriteRule ^index\.php$ - [L]
            RewriteCond %{REQUEST_FILENAME} !-f
            RewriteCond %{REQUEST_FILENAME} !-d
            RewriteRule . /index.php [L]
        </IfModule>
        ');
    }

    private static function copyStatic() {
        copy_recursive('static', 'dist');
    }

    private static function errorPage() {
        //make error page
        copy('dist/index.html', 'dist/404.html');
        $contents = file_get_contents('dist/404.html');
        file_put_contents('dist/404.html', str_replace('<div id=app></div>', '<div id=app>'.
        '<div class="content text-center pt-30">'.
        '    <div class="title title--small mb-20 mt-40">(404) Not found.</div>'.
        '    <div class="subtitle">We\'re sorry, the page you\'re looking for doesn\'t exist.</div>'.
        '</div></div>',
        $contents));
    }

    private static function darkModeRemover() {
        if ( self::$options['keep-dark-mode'] ) {
            return;
        }

        DarkModeRemover::run();
    }
}
