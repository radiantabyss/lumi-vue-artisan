<?php
namespace LumiVueBuilder;

class Commander
{
    private static $command;
    private static $options;

    public static function run($argv) {
        // chdir(dirname(__FILE__).'/../../../');

        self::parseArgv($argv);
        self::loadEnv();

        $Command = '\\LumiVueBuilder\\Commands\\'.pascal_case(self::$command).'Command';

        try {
            $Command::run(self::$options);
        }
        catch(\Exception $e) {
            echo "\033[31mError:\033[0m ".$e->getMessage();
        }
    }

    private static function parseArgv($argv) {
        $options = [];

        foreach ( $argv as $k => $a ) {
            if ( preg_match('/ \-\-(.+)=(.+)/', $a, $m) ) {
                $options[$m[1]] = $m[2];
            }
            else if ( preg_match('/ \-\-(.+)/', $a, $m) ) {
                $options[$m[1]] = true;
            }
            else if ( preg_match('/ \-(.+)=(.+)/', $a, $m) ) {
                $options[$m[1]] = $m[2];
            }
            else if ( preg_match('/ \-(.+)/', $a, $m) ) {
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
}
