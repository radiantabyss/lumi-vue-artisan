<?php
namespace LumiVueArtisan\Commands;

class CopyStaticCommand implements CommandInterface
{
    public static function run($options) {
        copy_recursive('static', 'public');
    }
}
