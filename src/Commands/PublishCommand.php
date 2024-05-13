<?php
namespace Lumi\VueArtisan\Commands;

class PublishCommand implements CommandInterface
{
    public static function run($options) {
        $Publisher = '\\Lumi\\Vue\\Artisan\\Publishers\\'.pascal_case($_ENV['PUBLISHER']).'Publisher';
        $Publisher::run($options);
    }
}
