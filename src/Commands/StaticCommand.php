<?php
namespace LumiVueArtisan\Commands;

class StaticCommand implements CommandInterface
{
    public static function run($options) {
        copy_recursive('static', 'public');
    }
}
