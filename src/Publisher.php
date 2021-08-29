<?php
namespace LumiVueBuilder;

class Publisher
{
    public static function run($options) {
        $Publisher = '\\LumiVueBuilder\\Publishers\\'.pascal_case($_ENV['PUBLISHER']).'Publisher';
        $Publisher::run($options);
    }
}
