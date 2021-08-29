<?php
namespace LumiVueBuilder;

class Commander
{
    private static $command;
    private static $options;

    public static function run($argv) {
        self::parseArgv($argv);
        self::loadEnv();
        self::{self::$command.'Command'}();
    }

    private static function parseArgv($argv) {
        $options = [];

        foreach ( $argv as $k => $a ) {
            if ( preg_match('@\-\-(.+)=(.+)@', $a, $m) ) {
                $options[$m[1]] = $m[2];
            }
            else if ( preg_match('@\-\-(.+)@', $a, $m) ) {
                $options[$m[1]] = true;
            }
            else if ( preg_match('@\-(.+)=(.+)@', $a, $m) ) {
                $options[$m[1]] = $m[2];
            }
            else if ( preg_match('@\-(.+)@', $a, $m) ) {
                $options[$m[1]] = true;
            }
            else {
                $options[$k] = $a;
            }
        }

        //called command
        $command = $options[1];

        //remove unused options
        unset($options['builder']);
        unset($options[1]);

        self::$command = $command;
        self::$options = $options;
    }

    private static function loadEnv() {
        //get api url from env file
        $dotenv = \Dotenv\Dotenv::createImmutable(getcwd(), '.env.local');
        $dotenv->load();
    }

    private static function helpCommand() {
        echo "Commands List:\n
\033[32mbuild\033[0m                 Builds the code
    --skip-sprites
    --skip-build
    --skip-publish
    --keep-dark-mode
    --version=        Required for Electron
\033[32mpublish\033[0m               Publishes the build (release or upload)
\033[32msprite\033[0m                Builds the sprites";
    }

    private static function buildCommand() {
        Builder::run(self::$options);
    }

    private static function publishCommand() {
        Publisher::run(self::$options);
    }

    private static function spriteCommand() {
        SpriteSmith::run();
    }
}
