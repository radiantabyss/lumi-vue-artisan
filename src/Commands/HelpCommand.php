<?php
namespace LumiVueArtisan\Commands;

class HelpCommand implements CommandInterface
{
    public static function run($options) {
        echo "Commands List:\n
\033[32mbuild\033[0m                 Builds the code
    \033[93m--fast\033[0m             \033[90mskips 'npm install' and sprites when building\033[0m
    \033[93m--skip-sprites\033[0m
    \033[93m--skip-build\033[0m
    \033[93m--skip-publish\033[0m
    \033[93m--keep-dark-mode\033[0m
    \033[93m--version=\033[0m        Required for Electron\n
\033[32mpublish\033[0m               Publishes the build (release or upload)
\033[32msprite\033[0m                Builds the sprites\n\n
\033[32mcopy-static\033[0m    Copies static assets to publi folder. Use this before starting the dev server.c\n\n";
    }
}
