<?php
function dmp($text) {
    var_dump($text);
}
function ddmp($text) {
    dmp($text);die();
}

function delete_recursive($directory) {
    foreach(glob("{$directory}/*") as $file) {
        if ( is_dir($file) ) {
            delete_recursive($file);
        }
        else {
            @unlink($file);
        }
    }

    if ( !glob("{$directory}/*") ) {
        foreach( glob("{$directory}/.*") as $file ) {
            if ( $file == $directory.'/.' || $file == $directory.'/..' ) continue;

            @unlink($file);
        }
    }

    @rmdir($directory);
}

function copy_recursive($source, $dest) {
    // Check for symlinks
    if ( is_link($source) ) {
        return symlink(readlink($source), $dest);
    }

    // Simple copy for a file
    if ( is_file($source) ) {
        return copy($source, $dest);
    }

    // Make destination directory
    if ( !is_dir($dest) ) {
        mkdir($dest);
    }

    // Loop through the folder
    $dir = dir($source);
    while ( false !== $entry = $dir->read() ) {
        // Skip pointers
        if ($entry == '.' || $entry == '..') {
            continue;
        }

        // Deep copy directories
        copy_recursive("$source/$entry", "$dest/$entry");
    }

    // Clean up
    $dir->close();
    return true;
}

function get_files_recursive(string $directory, array $allFiles = []) {
    $files = array_diff(scandir($directory), ['.', '..']);

    foreach ($files as $file) {
        $fullPath = $directory. DIRECTORY_SEPARATOR .$file;

        if( is_dir($fullPath) ) {
            $allFiles += getFiles($fullPath, $allFiles);
        }
        else {
            $allFiles[] = $file;
        }
    }

    return $allFiles;
}

function pascal_case($str) {
    return str_replace(' ', '', ucwords(str_replace(['-', '_', ':'], ' ', $str)));
}
