<?php
namespace LumiVueArtisan\Publishers;

class ElectronPublisher implements PublisherInterface
{
    private static $options;
    private static $version;

    public static function run($options) {
        //set options
        self::$options = $options;

        //get version
        self::getVersion();

        //copy installer to cdn folder
        self::copyInstaller();

        //zip and copy asar
        self::zipAsar();

        //copy and archive new/updated static files
        self::copyUpdatedStaticFiles();

        //make filezilla xml files for server upload
        self::makeFilezillaXML();
    }

    private static function getVersion() {
        //get version from package.json
        preg_match('/"version"\: "(.*)?"/', file_get_contents('package.json'), $match);
        self::$version = $match[1];
    }

    private static function copyInstaller() {
        //get app name from vue config
        preg_match('/productName\: \'(.*)?\'\,/', file_get_contents('vue.config.js'), $match);
        $app_name = $match[1];

        $archs = ['ia32', 'x64'];
        foreach ( $archs as $arch ) {
            copy('dist_electron/'.$app_name.' Setup '.self::$version.($arch == 'ia32' ? '-x32' : '').'.exe',
                '../files_cdn/public/'.$app_name.' Setup '.self::$version.($arch == 'ia32' ? '-x32' : '').'.exe');
        }
    }

    private static function zipAsar() {
        //zip asar
        $cwd = getcwd();
        chdir('dist_electron/win-unpacked/resources');
        exec('zip -r app.zip app.asar');
        chdir($cwd);
        rename('dist_electron/win-unpacked/resources/app.zip', '../files_cdn/public/app.zip');
    }

    private static function copyUpdatedStaticFiles() {
        mkdir('latest');

        $files = get_files_recursive('updated-static-files');
        foreach ( $files as $file ) {
            if ( $file == 'README.md' ) {
                continue;
            }

            mkdir(pathinfo('latest/'.$file)['dirname'], 0777, true);
            copy($file, 'latest/'.$file);
        }

        exec('zip -r latest.zip latest');
        rename('latest.zip', '../files_cdn/public/latest.zip');
        delete_recursive('latest');
    }

    private static function makeFilezillaXML() {
        $servers = [
            [
                'name' => 'scaleway',
                'ip' => '51.15.45.70',
                'path' => '/var/www/files.dota2mods_4/public',
            ],
            [
                'name' => 'scaleway d2m',
                'ip' => '51.15.118.2',
                'path' => '/var/www/dota2mods_4/web/public',
            ],
            [
                'name' => 'digitalocean',
                'ip' => '95.85.44.42',
                'path' => '/var/www/public/dota2mods',
            ],
            [
                'name' => 'digitalocean nyc',
                'ip' => '67.205.185.234',
                'path' => '/var/www/public/dota2mods',
            ],
            [
                'name' => 'digitalocean sea',
                'ip' => '188.166.252.218',
                'path' => '/var/www/public/dota2mods',
            ],
        ];

        $xml = '<?xml version="1.0" encoding="UTF-8"?><FileZilla3 version="3.33.0" platform="windows"><Queue>';

        $archs = ['ia32', 'x64'];
        foreach ( $servers as $server ) {
            $xml .= '<Server>
                <Host>'.$server['ip'].'</Host>
                <Port>22</Port><Protocol>1</Protocol>
                <Type>0</Type><User>root</User><Pass encoding="base64"></Pass>
                <Logontype>1</Logontype><TimezoneOffset>0</TimezoneOffset>
                <PasvMode>MODE_DEFAULT</PasvMode><MaximumMultipleConnections>0</MaximumMultipleConnections>
                <EncodingType>Auto</EncodingType><BypassProxy>0</BypassProxy>
                <Name>'.$server['name'].'</Name>';

            $path = '1 0 ';
            $exp = explode('/', trim($server['path'], '/'));
            foreach ( $exp as $e ) {
                $path .= strlen($e).' '.$e.' ';
            }

            $path = trim($path);

            foreach ( $archs as $arch ) {
                $xml .= '<File>
                <LocalFile>'.__DIR__.'/../files_cdn/public/Dota2Mods V4 Setup '.self::$version.($arch == 'ia32' ? '-x32' : '').'.exe</LocalFile>
                <RemoteFile>Dota2Mods V4 Setup '.self::$version.($arch == 'ia32' ? '-x32' : '').'.exe</RemoteFile>
                <RemotePath>'.$path.'</RemotePath>
                <Download>0</Download><DataType>1</DataType></File>';
            }

            if ( !in_array($server['name'], ['scaleway', 'scaleway d2m']) ) {
                $xml .= '<File>
                    <LocalFile>'.__DIR__.'/../files_cdn/public/app.zip</LocalFile>
                    <RemoteFile>app.zip</RemoteFile>
                    <RemotePath>'.$path.' 2 v3</RemotePath>
                    <Download>0</Download><DataType>1</DataType></File>';

                //once
                $xml .= '<File>
                    <LocalFile>'.__DIR__.'/../files_cdn/public/decompiler.zip</LocalFile>
                    <RemoteFile>decompiler.zip</RemoteFile>
                    <RemotePath>'.$path.' 2 v3</RemotePath>
                    <Download>0</Download><DataType>1</DataType></File>';

                $xml .= '<File>
                    <LocalFile>'.__DIR__.'/../files_cdn/public/latest.zip</LocalFile>
                    <RemoteFile>latest.zip</RemoteFile>
                    <RemotePath>'.$path.' 2 v3</RemotePath>
                    <Download>0</Download><DataType>1</DataType></File>';
            }

            $xml .= '</Server>';
        }

        $xml .= '</Queue></FileZilla3>';
        file_put_contents('../filezilla.xml', $xml);
    }
}
