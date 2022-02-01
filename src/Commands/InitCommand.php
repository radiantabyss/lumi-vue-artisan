<?php
namespace LumiVueArtisan\Commands;

class InitCommand implements CommandInterface
{
    public static function run($options) {
        BuildLoaderCommand::run($options);
        SpriteCommand::run($options);
    }
}
