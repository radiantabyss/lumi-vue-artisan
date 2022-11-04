<?php
namespace LumiVueArtisan\Builders;

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
        exec('npm install');
        exec('npm run build');

        if ( !file_exists('dist') ) {
            throw new \Exception('Vue Builder failed.');
        }
    }

    private static function writeIndex() {
        //get file names
        $file_names = self::getFileNames();

        //check if is ssr
        if ( file_exists('ssr-env.php') ) {
            copy('index-src.php', 'dist/index.php');

            //replace in ssr-env
            $ssr_env_contents = file_get_contents('ssr-env.php');
            $ssr_env_contents = str_replace(
                [
                    "'APP_CSS' => ''",
                    "'APP_JS' => ''",
                    "'VENDORS_JS' => ''",
                    "'BACK_URL' => ''",
                    "'UPLOADS_URL' => ''",
                ],
                [
                    "'APP_CSS' => '".$file_names['app_css']."'",
                    "'APP_JS' => '".$file_names['app_js']."'",
                    "'VENDORS_JS' => '".$file_names['vendors_js']."'",
                    "'BACK_URL' => '".$_ENV['VUE_APP_BACK_URL']."'",
                    "'UPLOADS_URL' => '".$_ENV['VUE_APP_UPLOADS_URL']."'",
                ],
                $ssr_env_contents
            );

            file_put_contents('ssr-env.php', $ssr_env_contents);
        }
        else {
            //replace in index src
            $index_src_contents = file_get_contents('index-src.php');
            $index_src_contents = str_replace(
                [
                    '{{app_css}}',
                    '{{app_js}}',
                    '{{vendors_js}}',
                    '{{api_url}}'
                ],
                [
                    $file_names['app_css'],
                    $file_names['app_js'],
                    $file_names['vendors_js'],
                    $_ENV['VUE_APP_BACK_URL']
                ],
                $index_src_contents
            );

            file_put_contents('dist/index.php', $index_src_contents);
        }
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

        $files = scandir('dist/css');
        foreach ( $files as $file ) {
            if ( !preg_match('/app/', $file) ) {
                continue;
            }

            $contents = file_get_contents('dist/css/'.$file);
            $contents = preg_replace('/\@media \(prefers.*?\}\}/', '', $contents);
            file_put_contents('dist/css/'.$file, $contents);
        }
    }
}
