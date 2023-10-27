<?php
namespace Lumi\Vue\Artisan\Publishers;

class VuePublisher implements PublisherInterface
{
    private static $options;

    public static function run($options) {
        if ( !file_exists('dist') ) {
            return;
        }

        //delete release index.html in favor of index.php
        unlink('dist/index.html');

        @rename('release', 'release2');
        rename('dist', 'release');
        @delete_recursive('release2');
        delete_recursive('public');
    }
}
