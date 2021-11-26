<?php
namespace LumiVueBuilder\Commands;

class HelpCommand implements CommandInterface
{
    private static $options = [];

    public static function run($options) {
        echo "Commands List:\n
\033[32minit\033[0m                 Builders Loader and Sprites
\033[32mbuild-loader\033[0m                 Builds Loader
\033[32mbuild\033[0m                 Builds the code
    --skip-sprites
    --skip-build
    --skip-publish
    --keep-dark-mode
    --version=        Required for Electron
\033[32mpublish\033[0m               Publishes the build (release or upload)
\033[32msprite\033[0m                Builds the sprites";
    }
}
