<?php
namespace LumiVueArtisan\Commands;

class PublishCommand implements CommandInterface
{
    public static function run($options) {
        $Publisher = '\\LumiVueArtisan\\Publishers\\'.pascal_case($_ENV['PUBLISHER']).'Publisher';
        $Publisher::run($options);
    }
}
