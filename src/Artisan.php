<?php
namespace LumiVueArtisan;

class Artisan
{
    private static $command;
    private static $options;

    public static function run($argv) {
        self::parseArgv($argv);

        $Command = '\\LumiVueArtisan\\Commands\\'.pascal_case(self::$command).'Command';

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
            if ( preg_match('/\-\-(.+)=(.+)/', $a, $m) ) {
                $options[$m[1]] = $m[2];
            }
            else if ( preg_match('/\-\-(.+)/', $a, $m) ) {
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
        unset($options[0]);
        unset($options[1]);

        self::$command = $command;
        self::$options = $options;
    }
}
