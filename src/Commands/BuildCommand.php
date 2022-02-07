<?php
namespace LumiVueArtisan\Commands;

class BuildCommand implements CommandInterface
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

        SpriteCommand::run([]);
    }

    private static function build() {
        if ( self::$options['skip-build'] ) {
            return;
        }

        $Builder = '\\LumiVueArtisan\\Builders\\'.pascal_case($_ENV['LUMI_BUILDER']).'Builder';
        $Builder::run(self::$options);
    }

    private static function publish() {
        if ( self::$options['skip-build'] || self::$options['skip-publish'] ) {
            return;
        }

        $Publisher = '\\LumiVueArtisan\\Publishers\\'.pascal_case($_ENV['PUBLISHER']).'Publisher';
        $Publisher::run(self::$options);
    }
}
