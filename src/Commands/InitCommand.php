<?php
namespace LumiVueArtisan\Commands;

class InitCommand implements CommandInterface
{
    private static $options = [];

    public static function run($options) {
        BuildLoaderCommand::run();
        SpriteCommand::run();
    }
}
