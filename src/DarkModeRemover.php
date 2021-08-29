<?php
namespace LumiVueBuilder;

class DarkModeRemover
{
    public static function run() {
        $files = scandir('dist/css');
        foreach ( $files as $file ) {
            if ( !preg_match('/app/', $file) ) {
                continue;
            }

            $contents = file_get_contents('dist/css/'.$file);
            $contents = preg_replace('/\@media \(prefers.*?\}\}/', '', $contents);
            file_put_contents('dist/css/'.$file, $contents);
        }
    }
}
