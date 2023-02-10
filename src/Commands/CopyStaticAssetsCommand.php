<?php
namespace LumiVueArtisan\Commands;

class CopyStaticAssetsCommand implements CommandInterface
{
    public static function run($options) {
        copy_recursive('static', 'public');
    }
}
