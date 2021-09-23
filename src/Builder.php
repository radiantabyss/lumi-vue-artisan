<?php
namespace LumiVueBuilder;

class Builder
{
    private static $options = [
        'skip-sprites' => false,
        'skip-build' => false,
        'skip-publish' => false,
        'keep-dark-mode' => false,
    ];

    public static function run($options) {
        //set options
        self::$options = array_merge(self::$options, $options);

        //run sprites
        self::sprites();

        //build
        self::build();

        //publish
        self::publish();
    }

    private static function sprites() {
        if ( self::$options['skip-sprites'] ) {
            return;
        }

        Spritesmith::run();
    }

    private static function build() {
        if ( self::$options['skip-build'] ) {
            return;
        }

        $Builder = '\\LumiVueBuilder\\Builders\\'.pascal_case($_ENV['BUILDER']).'Builder';
        $Builder::run(self::$options);
    }

    private static function publish() {
        if ( self::$options['skip-build'] || self::$options['skip-publish'] ) {
            return;
        }

        $Publisher = '\\LumiVueBuilder\\Publishers\\'.pascal_case($_ENV['PUBLISHER']).'Publisher';
        $Publisher::run(self::$options);
    }
}