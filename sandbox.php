<?php

define('SANDBOX_CONFIG', json_decode(file_get_contents('exampleconfig.json'), true));
define('SANDBOX_WHITELIST', json_decode(file_get_contents('whitelist.json'), true));
define('SANDBOX_SUPPORTED_PDO_DRIVERS', array('mysql'));

require 'logger.php';
require 'exceptions.php';
require 'notices.php';

function handleException(SandyPHPException $exception, bool $output_log, bool $debug_output) {
    global $logger;
    if(isset($this->config['log'])) $logger->logException($exception);
    if($debug_output) echo $exception->getMessage();
}

require 'vendor/autoload.php';
require 'querycheck.php';
/*require 'SandyPHPVirtPDODriver.php'; // PDO is enabled by default, that's why I will assume it is installed. Otherwise it will throw an error anyway...
if (function_exists('mysqli_init') && extension_loaded('mysqli')) require 'SandyPHPVirtMySQLIDriver.php';
else require 'MYSQLIwarning.php';*/

ini_set('display_errors', true);
error_reporting(E_ALL);

// This code defines sandboxes in which extensions to StudEzy will be running...

ob_start(); // Start an output buffer in order for the script to be able to send custom headers (if it's allowed to)

function SandyPHP_version_info() {
   return 'SandyPHP 0.0.0beta - Not intended for use on any publicly accessible servers!';
}

function isNetworkLocation(string $filename) : bool {
    return preg_match("~^(https?|ftp|ssh2)://~", $filename);
}

function isAllowedWrapper(string $filename) {
    return !preg_match("~^(php|zlib|glob|phar|rar|ogg|expect|unix|udg)://~", $filename);
}

function file_is_within_folders(string $filename, $allowedPaths) : bool {
    $match = false;
    foreach($allowedPaths as $allowedPath) {
        if(strncmp(realpath($filename), $allowedPath, strlen($allowedPath)) === 0) $match = true;
    }
    return $match;
}

class SandyPHPSandbox {

    private PHPSandbox $PHPsandbox;
    protected $config;
    protected $logger;
    private $id;

    public function getPHPSandbox() {
        return $this->PHPSandbox;
    }

    protected function tailorClass(string $class) {
        // This is bad code, I know... if anyone can find a better solution pls fix!
        if(!class_exists($class.'_SANDBOX_'.$this->id)) eval(str_replace('SANDBOXCONFIG', json_encode($this->config['database']), str_replace('SANDBOXID', $this->id, file_get_contents($class.'.php'))));
    }

    protected function storage_access_granted($filename) {
            if(basename($filename) == 'manifest.json' OR (isNetworkLocation($filename) AND !$this->network_access_granted()) OR !isAllowedWrapper($filename)) return false; // Applications are not allowed access to their manifest file, they can request configuration info instead
            else if(file_is_within_folders($this->storage_get_realpath($filename), $this->config['permissions']['storage']) OR $this->config['permissions']['storage'][0] === '*') {
                $this->logger->log(new StorageAccessNotice($this->storage_get_realpath($filename), $this->config['debug_output']['notice']));
                return true;
            }
            $ex = new StorageAccessPolicyViolation($this->storage_get_realpath($filename));
            $this->logger->log($ex);
            throw $ex;
            return false;
    }

    protected function network_access_granted() {
        if(!$this->config['permissions']['network'] OR !isset($this->config['permissions']['network'])) {
            $networkException = new NetworkAccessPolicyViolation();
            $this->logger->log($networkException);
            throw $networkException;
            return false;
        }
        $this->logger->log(new NetworkAccessNotice($this->config['debug_output']['notice']));
        return true;
    }

    protected function sharedmemory_access_granted() {
        if(!$this->config['permissions']['sharedmemory'] OR !isset($this->config['permissions']['sharedmemory'])) {
            $sharedmemException = new SharedMemoryAccessPolicyViolation();
            $this->logger->log($sharedmemException);
            throw $sharedmemException;
            return false;
        }
        $this->logger->log(new SharedMemoryAccessNotice($this->config['debug_output']['notice']));
        return true;
    }

    protected function binary_execution_access_granted(string $binary_id) {
        if(!$this->config['permissions']['bin_exec'] OR !isset($this->config['permissions']['bin_exec'])) {
            $binExecException = new BinaryExecutionPermissionViolation();
            $this->logger->log($binExecException);
            throw $binExecException;
            return false;
        } 
        $this->logger->log(new BinaryExecutionNotice($binary_id, $this->config['debug_output']['notice']));
        return true;
    }

    protected function interprocess_communication_access_granted() {
        if(!$this->config['permissions']['iproc_com'] OR !isset($this->config['permissions']['iproc_com'])) {
            $iprocException = new InterprocessCommunicationPermissionViolation();
            $this->logger->log($iprocException);
            throw $iprocException;
            return false;
        } 
        $this->logger->log(new InterprocessCommunicationNotice($this->config['debug_output']['notice']));
        return true;

    }

    protected function hostinfo_access_granted() {
        if(!$this->config['permissions']['hostinfo'] OR !isset($this->config['permissions']['hostinfo'])) {
            $this->logger->log(new HostInfoAccessNotice(false, $this->config['debug_output']['notice']));
            return false;
        } 
        $this->logger->log(new HostInfoAccessNotice(true, $this->config['debug_output']['notice']));
        return true;
    }

    protected function storage_get_realpath($filename) {
        if(isNetworkLocation($filename)) return $filename;
        else if(isset($this->config['storage']['fsroot'])) return $this->config['storage']['fsroot'].str_replace('..', '', $filename);
        else return realpath($filename);
    }

    public function __construct($config_options) {
        $this->config = $config_options;
        $this->id = uniqid();

        ob_start(); // Start an output buffer as it might have been flushed

        // Create the PHPSandbox SandyPHP is based on
        $this->PHPSandbox = new \PHPSandbox\PHPSandbox();

        // Initialize the logging system
        $this->logger = new Logger($this->config['log']['file']);
        $this->logger->setExceptionsEnabled((isset($this->config['log']['level']['exception']) ? $this->config['log']['level']['exception'] : true)); // Default value is true
        $this->logger->setNoticesEnabled((isset($this->config['log']['level']['notice']) ? $this->config['log']['level']['notice'] : true)); // Default: true
        $this->logger->log(new SandboxCreationNotice($this->id, $this->config['debug_output']['notice']));

        // Do some basic sandbox configuration...
        $this->PHPSandbox->whitelistFunc('SandyPHP_version_info');
        $this->PHPSandbox->whitelistFunc(SANDBOX_WHITELIST);
        $this->PHPSandbox->allow_includes = false; //$this->config['permissions']['include'] | false;
        $this->PHPSandbox->allow_escaping = $this->config['permissions']['escape'] | false;
        $this->PHPSandbox->allow_functions = $this->config['permissions']['functions'] | false;
        $this->PHPSandbox->allow_aliases = true;
        $this->PHPSandbox->validate_types = true;
        $this->PHPSandbox->auto_whitelist_functions = true;
        $this->PHPSandbox->allow_classes = true;
        
        if(isset($config['exposeScripts'])) {
            foreach($this->config['exposeScripts']['scripts'] AS $script) {
                require_once $script;
            }
            if(isset($this->config['exposeScripts']['whitelist']['classes']) AND $this->config['exposeScripts']['whitelist']['classes'] !== false) $this->PHPSandbox->whitelistClass($this->config['exposeScripts']['whitelist']['classes']);
            if(isset($this->config['exposeScripts']['whitelist']['functions']) AND $this->config['exposeScripts']['whitelist']['functions'] !== false) $this->PHPSandbox->whiteListFunc($this->config['exposeScripts']['whitelist']['functions']);
        }

        // Redefine all functions associated with file handling to enforce storage access restrictions

        // #1 file_get_contents
        $this->PHPSandbox->defineFunc('file_get_contents', function(string $filename) {
            if($this->storage_access_granted($filename)) return file_get_contents($this->storage_get_realpath($filename));
            return false;
        });

        // #2 file_put_contents
        $this->PHPSandbox->defineFunc('file_put_contents', function(string $filename, $flags, $ctx = null) {
            return ($this->storage_access_granted($filename) ? file_put_contents($this->storage_get_realpath($filename), $flags, $ctx) : false);
        });


        // #3 fopen
        $this->PHPSandbox->defineFunc('fopen', function(string $filename, $mode, $use_include_path = false, $context = null) {
            return ($this->storage_access_granted($filename) ? fopen($this->storage_get_realpath($filename), $mode, $use_include_path, $context) : false);
        });


        // #4 openssl_x509_export_to_file
        $this->PHPSandbox->defineFunc('openssl_x509_export_to_file', function($certificate, $output_filename, $no_text = true) {
            return ($this->storage_access_granted($output_filename) ? openssl_x509_export_to_file($certificate, $this->storage_get_realpath($output_filename), $no_text) : false);
        });


        // #5 openssl_pkcs12_export_to_file
        $this->PHPSandbox->defineFunc('openssl_pkcs12_export_to_file', function($certificate, string $output_filename, OpenSSLAsymmetricKey|OpenSSLCertificate|array|string $private_key, string $passphrase, array $options = []) {
            return ($this->storage_access_granted($output_filename) ? openssl_pkcs12_export_to_file($certificate, $this->storage_get_realpath($output_filename), $private_key, $passphrase, $options) : false);
        });

        // #6 openssl_csr_export_to_file
        $this->PHPSandbox->defineFunc('openssl_csr_export_to_file', function(OpenSSLCertificateSigningRequest|string $csr, string $output_filename, bool $no_text = true) {
            return ($this->storage_access_granted($output_filename) ? openssl_csr_export_to_file($csr, $this->storage_get_realpath($output_filename), $no_text) : false);
        });

        // #7 openssl_pkey_export_to_file
        $this->PHPSandbox->defineFunc('openssl_pkey_export_to_file', function(OpenSSLAsymmetricKey|OpenSSLCertificate|array|string $key, string $output_filename, ?string $passphrase = null, ?array $options = null) {
            return ($this->storage_access_granted($output_filename) ? openssl_pkey_export_to_file($key, $this->storage_get_realpath($output_filename), $passphrase, $options) : false);
        });

        // #8 gzfile
        $this->PHPSandbox->defineFunc('gzfile', function(string $filename, int $use_include_path = 0) {
            return ($this->storage_access_granted($filename) ? gzfile($this->storage_get_realpath($filename), $use_include_path) : false);
        });

        // #9 gzopen
        $this->PHPSandbox->defineFunc('gzopen', function(string $filename, string $mode, int $use_include_path = 0) {
            return ($this->storage_access_granted($filename) ? gzopen($this->storage_get_realpath($filename), $mode, $use_include_path) : false);
        });

        // #10 readgzfile
        $this->PHPSandbox->defineFunc('readgzfile', function(string $filename, int $use_include_path = 0) {
            return ($this->storage_access_granted($filename) ? readgzfile($key, $this->storage_get_realpath($output_filename), $passphrase, $options) : false);
        });

        // #11 hash_file
        $this->PHPSandbox->defineFunc('hash_file', function(string $algo, string $filename, bool $binary = false, array $options = []) {
            return ($this->storage_access_granted($filename) ? hash_file($algo, $this->storage_get_realpath($filename), $binary, $options) : false);
        });

        // #12 hash_hmac_file
        $this->PHPSandbox->defineFunc('hash_hmac_file', function(string $algo, string $filename, string $key, bool $binary = false) {
            return ($this->storage_access_granted($filename) ? hash_hmac_file($algo, $this->storage_get_realpath($filename, $key, $binary)) : false);
        });

        // #13 hash_update_file
        $this->PHPSandbox->defineFunc('hash_update_file', function(HashContext $context, string $filename, $stream_context = null) {
            return ($this->storage_access_granted($filename) ? hash_update_file($context, $this->storage_get_realpath($filename), $stream_context) : false);
        });

        // #14 highlight_file
        $this->PHPSandbox->defineFunc('highlight_file', function(string $filename, bool $return = false) {
            return ($this->storage_access_granted($filename) ? highlight_file($this->storage_get_realpath($filename), $return) : false);
        });

        // #15 is_uploaded_file
        $this->PHPSandbox->defineFunc('is_uploaded_file', function(string $filename) {
            return ($this->storage_access_granted($filename) AND $this->config['permissions']['fileupload'] ? is_uploaded_file($this->storage_get_realpath($filename)) : false);
        });

        // #16 move_uploaded_file
        $this->PHPSandbox->defineFunc('move_uploaded_file', function(string $from, string $to) {
            return ($this->storage_access_granted($to) AND $this->config['permissions']['fileupload'] ? move_uploaded_file($from, $this->storage_get_realpath($to)) : false);
        });

        // #17 parse_ini_file
        $this->PHPSandbox->defineFunc('parse_ini_file', function(string $filename, bool $process_sections = false, int $scanner_mode = INI_SCANNER_NORMAL) {
            return ($this->storage_access_granted($filename) ? parse_ini_file($this->storage_get_realpath($filename), $process_sections, $scanner_mode) : false);
        });

        // #18 md5_file
        $this->PHPSandbox->defineFunc('md5_file', function(string $filename, bool $binary = false) {
            return ($this->storage_access_granted($filename) ? md5_file($this->storage_get_realpath($filename), $binary) : false);
        });

        // #19 sha1_file
        $this->PHPSandbox->defineFunc('sha1_file', function(string $filename, bool $binary = false) {
            return ($this->storage_access_granted($filename) ? sha1_file($this->storage_get_realpath($filename), $binary) : false);
        });

        // #20 readfile
        $this->PHPSandbox->defineFunc('readfile', function(string $filename, bool $use_include_path = false, $context = null) {
            return ($this->storage_access_granted($filename) ? readfile($this->storage_get_realpath($filename), $use_include_path, $context) : false);
        });

        // #21 file
        $this->PHPSandbox->defineFunc('file', function(string $filename, int $flags = 0, $context = null) {
            return ($this->storage_access_granted($filename) ? file($this->storage_get_realpath($filename), $flags, $context) : false);
        });

        // #22 fileatime
        $this->PHPSandbox->defineFunc('fileatime', function(string $filename) {
            return ($this->storage_access_granted($filename) ? fileatime($this->storage_get_realpath($filename)) : false);
        });

        // #23 filectime
        $this->PHPSandbox->defineFunc('filectime', function(string $filename) {
            return ($this->storage_access_granted($filename) ? filectime($this->storage_get_realpath($filename)) : false);
        });

        // #24 filegroup
        $this->PHPSandbox->defineFunc('filegroup', function(string $filename) {
            return ($this->storage_access_granted($filename) ? filegroup($this->storage_get_realpath($filename)) : false);
        });

        // #25 fileinode
        $this->PHPSandbox->defineFunc('fileinode', function(string $filename) {
            return ($this->storage_access_granted($filename) ? fileinode($this->storage_get_realpath($filename)) : false);
        });

        // #26 filemtime
        $this->PHPSandbox->defineFunc('filemtime', function(string $filename) {
            return ($this->storage_access_granted($filename) ? filemtime($this->storage_get_realpath($filename)) : false);
        });

        // #27 fileowner
        $this->PHPSandbox->defineFunc('fileowner', function(string $filename) {
            return ($this->storage_access_granted($filename) ? fileowner($this->storage_get_realpath($filename)) : false);
        });

        // #28 fileperms
        $this->PHPSandbox->defineFunc('fileperms', function(string $filename) {
            return ($this->storage_access_granted($filename) ? fileperms($this->storage_get_realpath($filename)) : false);
        });

        // #29 filesize
        $this->PHPSandbox->defineFunc('filesize', function(string $filename) {
            return ($this->storage_access_granted($filename) ? filesize($this->storage_get_realpath($filename)) : false);
        });

        // #30 filetype
        $this->PHPSandbox->defineFunc('filetype', function(string $filename) {
            return ($this->storage_access_granted($filename) ? filetype($this->storage_get_realpath($filename)) : false);
        });

        // #31 file_exists
        $this->PHPSandbox->defineFunc('file_exists', function(string $filename) {
            return ($this->storage_access_granted($filename) ? file_exists($this->storage_get_realpath($filename)) : false);
        });

        // #32 is_file
        $this->PHPSandbox->defineFunc('is_file', function(string $filename) {
            return ($this->storage_access_granted($filename) ? is_file($this->storage_get_realpath($filename)) : false);
        });

        // #33 finfo_file
        $this->PHPSandbox->defineFunc('finfo_file', function(finfo $finfo, string $filename, int $flags = FILEINFO_NONE, $context = null) {
            return ($this->storage_access_granted($filename) ? finfo_file($finfo, $this->storage_get_realpath($filename), $flags, $context) : false);
        });

        // #34 simplexml_load_file
        $this->PHPSandbox->defineFunc('simplexml_load_file', function(string $filename, ?string $class_name = SimpleXMLElement::class, int $options = 0, string $namespace_or_prefix = "", bool $is_prefix = false) {
            return ($this->storage_access_granted($filename) ? simplexml_load_file($this->storage_get_realpath($filename), $class_name, $options, $namespace_or_prefix, $is_prefix) : false);
        });

        // #35 unlink
        $this->PHPSandbox->defineFunc('unlink', function(string $filename, $context = null) {
            return ($this->storage_access_granted($filename) ? unlink($this->storage_get_realpath($filename), $context) : false);
        });

        $this->PHPSandbox->defineFunc('opendir', function(string $directory, $context = null) {
            return ($this->storage_access_granted($directory) ? opendir($this->storage_get_realpath($directory), $context) : false);
        });

        $this->PHPSandbox->defineFunc('dir', function(string $directory, $context = null) {
            return ($this->storage_access_granted($directory) ? dir($this->storage_get_realpath($directory), $context) : false);
        });

        $this->PHPSandbox->defineFunc('get_meta_tags', function(string $filename, bool $use_include_path = false) {
            return ($this->storage_access_granted($filename) ? get_meta_tags($this->storage_get_realpath($filename), $use_include_path) : false);
        });

        $this->PHPSandbox->defineFunc('rmdir', function(string $directory, $context = null) {
            return ($this->storage_access_granted($directory) ? rmdir($this->storage_get_realpath($directory), $context) : false);
        });

        $this->PHPSandbox->defineFunc('mkdir', function(string $directory, int $permissions = 0777, bool $recursive = false, $context = null) {
            return ($this->storage_access_granted($directory) ? mkdir($this->storage_get_realpath($directory), $permissions) : false);
        });

        $this->PHPSandbox->defineFunc('rename', function(string $from, string $to, $context = null) {
            return ($this->storage_access_granted($from) AND $this->storage_access_granted($to) ? rename($this->storage_get_realpath($from), $this->storage_get_realpath($to), $context) : false);
        });

        $this->PHPSandbox->defineFunc('copy', function(string $from, string $to, $context = null) {
            return ($this->storage_access_granted($from) AND $this->storage_access_granted($to) ? copy($this->storage_get_realpath($from), $this->storage_get_realpath($to), $context) : false);
        });

        $this->PHPSandbox->defineFunc('tempnam', function(string $directory, string $prefix) {
            return ($this->storage_access_granted($directory) ? tempnam($this->storage_get_realpath($directory), $prefix) : false);
        });

        $this->PHPSandbox->defineFunc('is_writable', function(string $filename) {
            return ($this->storage_access_granted($filename) ? is_writable($this->storage_get_realpath($filename)) : false);
        });

        $this->PHPSandbox->defineFunc('is_writeable', function(string $filename) {
            return ($this->storage_access_granted($filename) ? is_writeable($this->storage_get_realpath($filename)) : false);
        });

        $this->PHPSandbox->defineFunc('is_readable', function(string $filename) {
            return ($this->storage_access_granted($filename) ? is_readable($this->storage_get_realpath($filename)) : false);
        });

        $this->PHPSandbox->defineFunc('is_executable', function(string $filename) {
            return ($this->storage_access_granted($filename) ? is_executable($this->storage_get_realpath($filename)) : false);
        });

        $this->PHPSandbox->defineFunc('is_dir', function(string $filename) {
            return ($this->storage_access_granted($filename) ? is_dir($this->storage_get_realpath($filename)) : false);
        });

        $this->PHPSandbox->defineFunc('is_link', function(string $filename) {
            return ($this->storage_access_granted($filename) ? is_link($this->storage_get_realpath($filename)) : false);
        });

        $this->PHPSandbox->defineFunc('stat', function(string $filename) {
            return ($this->storage_access_granted($filename) ? stat($this->storage_get_realpath($filename)) : false);
        });

        $this->PHPSandbox->defineFunc('lstat', function(string $filename) {
            return ($this->storage_access_granted($filename) ? lstat($this->storage_get_realpath($filename)) : false);
        });

        $this->PHPSandbox->defineFunc('chown', function(string $filename, string|int $user) {
            return ($this->storage_access_granted($filename) ? chown($this->storage_get_realpath($filename), $user) : false);
        });

        $this->PHPSandbox->defineFunc('chgrp', function(string $filename, string|int $group) {
            return ($this->storage_access_granted($filename) ? chgrp($this->storage_get_realpath($filename), $group) : false);
        });

        $this->PHPSandbox->defineFunc('lchown', function(string $filename, string|int $user) {
            return ($this->storage_access_granted($filename) ? lchown($this->storage_get_realpath($filename), $user) : false);
        });

        $this->PHPSandbox->defineFunc('lchgrp', function(string $filename, string|int $group) {
            return ($this->storage_access_granted($filename) ? lchgrp($this->storage_get_realpath($filename), $group) : false);
        });

        $this->PHPSandbox->defineFunc('chmod', function(string $filename, int $permissions) {
            return ($this->storage_access_granted($filename) ? chmod($this->storage_get_realpath($filename), $permissions) : false);
        });

        $this->PHPSandbox->defineFunc('touch', function(string $filename, ?int $mtime = null, ?int $atime = null) {
            return ($this->storage_access_granted($filename) ? touch($this->storage_get_realpath($filename), $mtime, $atime) : false);
        });

        $this->PHPSandbox->defineFunc('disk_total_space', function(string $directory) {
            return ($this->storage_access_granted($directory) AND $this->hostinfo_access_granted() ? disk_total_space($this->storage_get_realpath($directory)) : false);
        });

        $this->PHPSandbox->defineFunc('disk_free_space', function(string $directory) {
            return ($this->storage_access_granted($directory) AND $this->hostinfo_access_granted() ? disk_free_space($this->storage_get_realpath($directory)) : false);
        });

        $this->PHPSandbox->defineFunc('diskfreespace', function(string $directory) {
            return ($this->storage_access_granted($directory) AND $this->hostinfo_access_granted() ? diskfreespace($this->storage_get_realpath($directory)) : false);
        });

        $this->PHPSandbox->defineFunc('fsockopen', function(string $hostname, int $port = -1, int &$error_code = null, string &$error_message = null, ?float $timeout = null) {
            return ($this->storage_access_granted($hostname) ? fsockopen($this->storage_get_realpath($hostname), $port, $error_code, $error_message, $timeout) : false);
        });

        $this->PHPSandbox->defineFunc('pfsockopen', function(string $hostname, int $port = -1, int &$error_code = null, string &$error_message = null, ?float $timeout = null) {
            return ($this->storage_access_granted($hostname) ? pfsockopen($this->storage_get_realpath($hostname), $port, $error_code, $error_message, $timeout) : false);
        });

        $this->PHPSandbox->defineFunc('getimagesize', function(string $filename, array &$image_info = null) {
            return ($this->storage_access_granted($hostname) ? getimagesize($this->storage_get_realpath($filename), $image_info) : false);
        });

        $this->PHPSandbox->defineFunc('iptcembed', function(string $iptc_data, string $filename, int $spool = 0) {
            return ($this->storage_access_granted($filename) ? iptcembed($iptc_data, $this->storage_get_realpath($filename), $spool) : false);
        });

        $this->PHPSandbox->defineFunc('readlink', function(string $path) {
            return ($this->storage_access_granted($path) ? readlink($this->storage_get_realpath($path)) : false);
        });

        $this->PHPSandbox->defineFunc('linkinfo', function(string $path) {
            return ($this->storage_access_granted($path) ? linkinfo($this->storage_get_realpath($path)) : false);
        });

        $this->PHPSandbox->defineFunc('symlink', function(string $target, string $link) {
            return ($this->storage_access_granted($target) AND $this->storage_access_granted($link) ? symlink($this->storage_get_realpath($target), $this->storage_get_realpath($link)) : false);
        });

        $this->PHPSandbox->defineFunc('link', function(string $target, string $link) {
            return ($this->storage_access_granted($target) AND $this->storage_access_granted($link) ? link($this->storage_get_realpath($target), $this->storage_get_realpath($link)) : false);
        });

        $this->PHPSandbox->defineFunc('bzopen', function(string|resource $file, string $mode) {
            return (($this->storage_access_granted($file) OR get_resource_type($file) == 'stream') ? bzopen((get_resource_type($file) == 'stream' ? $file : $this->storage_get_realpath($file)), $mode): false);
        });

        $this->PHPSandbox->defineFunc('ftp_get', function(FTP\Connection $ftp, string $local_filename, string $remote_filename, int $mode = FTP_BINARY, int $offset = 0) {
            return ($this->storage_access_granted($local_filename) AND $this->network_access_granted() ? ftp_get($ftp, $this->storage_get_realpath($local_filename), $remote_filename, $mode, $offset) : false);
        });

        $this->PHPSandbox->defineFunc('ftp_put', function(FTP\Connection $ftp, string $remote_filename, string $local_filename, int $mode = FTP_BINARY, int $offset = 0) {
            return ($this->storage_access_granted($local_filename) AND $this->network_access_granted() ? ftp_put($ftp, $remote_filename, $this->storage_get_realpath($local_filename), $mode, $offset) : false);
        });

        $this->PHPSandbox->defineFunc('ftp_append', function(FTP\Connection $ftp, string $remote_filename, string $local_filename, int $mode = FTP_BINARY) {
            return ($this->storage_access_granted($local_filename) AND $this->network_access_granted() ? ftp_append($ftp, $remote_filename, $this->storage_get_realpath($local_filename), $mode) : false);
        });

        $this->PHPSandbox->defineFunc('ftp_nb_put', function(FTP\Connection $ftp, string $remote_filename, string $local_filename, int $mode = FTP_BINARY, int $offset = 0) {
            return ($this->storage_access_granted($local_filename) AND $this->network_access_granted() ? ftp_nb_put($ftp, $remote_filename, $this->storage_get_realpath($local_filename), $mode, $offset) : false);
        });

        $this->PHPSandbox->defineFunc('posix_mkfifo', function(string $filename, int $permissions) {
            return ($this->storage_access_granted($filename) ? posix_mkfifo($this->storage_get_realpath($filename), $permissions) : false);
        });

        $this->PHPSandbox->defineFunc('posix_mknod', function(string $filename, int $flags, int $major = 0, int $minor = 0) {
            return ($this->storage_access_granted($filename) ? posix_mknod($this->storage_get_realpath($filename), $flags, $major, $minor) : false);
        });

        $this->PHPSandbox->defineFunc('posix_access', function(string $filename, int $flags = 0) {
            return ($this->storage_access_granted($filename) ? posix_access($this->storage_get_realpath($filename), $flags) : false);
        });

        $this->PHPSandbox->defineFunc('chdir', function(string $directory) {
            return ($this->storage_access_granted($directory) ? chdir($this->storage_get_realpath($directory)) : false);
        });

        $this->PHPSandbox->defineFunc('scandir', function(string $directory, int $sorting_order = SCANDIR_SORT_ASCENDING, ?resource $context = null) {
            return ($this->storage_access_granted($directory) ? scandir($this->storage_get_realpath($directory), $sorting_order, $context) : false);
        });

        // Restrict ability to send emails
        $this->PHPSandbox->defineFunc('mail', function(string $to, string $subject, string $message, array|string $additional_headers = [], string $additional_params = "") {
            return ($this->config['permissions']['mail'] ? mail($to, $subject, $message, $additional_headers, $additional_params) : false);
        });

        $this->PHPSandbox->defineFunc('mb_send_mail', function(string $to, string $subject, string $message, array|string $additional_headers = [], string $additional_params = "") {
            return ($this->config['permissions']['mail'] ? mb_send_mail($to, $subject, $message, $additional_headers, $additional_params) : false);
        });

        // Some functions need to be disabled but should not throw errors inside the sandbox in order for scripts using them not to fail, so they are redefined returning a value which won't cause most scripts to fail...
        // #36 ini_set
        $this->PHPSandbox->defineFunc('ini_set', function(string $option, string|int|float|bool|null $value) {
            return false;
        });

        // #37 ini_alter
        $this->PHPSandbox->defineFunc('ini_alter', function(string $option, string|int|float|bool|null $value) {
            return false;
        });

        // #38 ini_restore
        $this->PHPSandbox->defineFunc('ini_restore', function(string $option) {
            return false;
        });

        // #39 set_include_path
        $this->PHPSandbox->defineFunc('set_include_path', function(string $include_path) {
            return false;
        });

        // #40 ignore_user_abort
        $this->PHPSandbox->defineFunc('ignore_user_abort', function(?bool $enable = null) {
            return false;
        });

        $this->PHPSandbox->defineFunc('pdo_drivers', function() {
            return array_map(function($driver) {
                if(in_array($driver, SANDBOX_SUPPORTED_PDO_DRIVERS)) return 'SandyPHPVirtPDODriver for '.$driver;
                return 'No SandyPHPVirtPDODriver for '.$driver;
            }, pdo_drivers());
        });


        // Restrict access to host information
        // gethostname
        $this->PHPSandbox->defineFunc('gethostname', function() {
            return ($this->hostinfo_access_granted() ? gethostname() : 'PHPSandboxVirtEnv');
        });

        $this->PHPSandbox->defineFunc('net_get_interfaces', function() {
            try {
                return ($this->hostinfo_access_granted() ? net_get_interfaces() : false);
            } catch(SandyPHPException $exception) {
                handleException($exception);
            }
        });

        $this->PHPSandbox->defineFunc('getmyuid', function() {
            return ($this->hostinfo_access_granted() ? getmyuid() : false);
        });

        $this->PHPSandbox->defineFunc('getmygid', function() {
            return ($this->hostinfo_access_granted() ? getmygid() : false);
        });

        $this->PHPSandbox->defineFunc('getmypid', function() {
            return ($this->hostinfo_access_granted() ? getmypid() : false);
        });

        $this->PHPSandbox->defineFunc('getmyinode', function() {
            return ($this->hostinfo_access_granted() ? getmyinode() : false);
        });

        $this->PHPSandbox->defineFunc('getlastmod', function() {
            return ($this->hostinfo_access_granted() ? getlastmod() : false);
        });

        $this->PHPSandbox->defineFunc('openlog', function(string $prefix, int $flags, int $facility) {
            return ($this->hostinfo_access_granted() ? openlog($prefix, $flags, $facility) : false);
        });

        $this->PHPSandbox->defineFunc('sys_getloadavg', function() {
            return ($this->hostinfo_access_granted() ? sys_getloadavg() : false);
        });

        $this->PHPSandbox->defineFunc('get_current_user', function() {
            return ($this->hostinfo_access_granted() ? get_current_user() : false);
        });

        $this->PHPSandbox->defineFunc('sys_get_temp_dir', function() {
            return ($this->hostinfo_access_granted() ? sys_get_temp_dir() : 'PHPSandboxVirtStorage');
        });

        $this->PHPSandbox->defineFunc('phpinfo', function() {
            return ($this->hostinfo_access_granted() ? phpinfo() : 'SandyPHP');
        });

        $this->PHPSandbox->defineFunc('phpversion', function() {
            return ($this->hostinfo_access_granted() ? phpversion() : 'SandyPHP 0.0.0beta');
        });

        $this->PHPSandbox->defineFunc('phpcredits', function() {
            return ($this->hostinfo_access_granted() ? phpcredits() : 'Credits can be found on the PHP website, this feature is disabled for security reasons, big thanks to the PHP guys anyway!');
        });

        $this->PHPSandbox->defineFunc('php_sapi_name', function() {
            return ($this->hostinfo_access_granted() ? php_sapi_name() : false);
        });

        $this->PHPSandbox->defineFunc('php_uname', function(string $mode = "a") {
            return ($this->hostinfo_access_granted() ? php_uname($mode) : 'SandyPHP');
        });

        $this->PHPSandbox->defineFunc('posix_getpid', function() {
            return ($this->hostinfo_access_granted() ? posix_getpid() : 1801332415715); // Number is for the letters in SandyPHP
        });

        $this->PHPSandbox->defineFunc('posix_getppid', function() {
            return ($this->hostinfo_access_granted() ? posix_getppid() : 1801332415715); // Number is for the letters in SandyPHP
        });

        $this->PHPSandbox->defineFunc('posix_getuid', function() {
            return ($this->hostinfo_access_granted() ? posix_getuid() : 'SandyPHPVirtUser');
        });

        $this->PHPSandbox->defineFunc('posix_setuid', function(int $user_id) {
            return ($this->hostinfo_access_granted() ? posix_setuid($user_id) : false);
        });

        $this->PHPSandbox->defineFunc('posix_geteuid', function() {
            return ($this->hostinfo_access_granted() ? posix_geteuid() : 'SandyPHPVirtUser');
        });

        $this->PHPSandbox->defineFunc('posix_seteuid', function(int $user_id) {
            return ($this->hostinfo_access_granted() ? posix_seteuid($user_id) : false);
        });

        $this->PHPSandbox->defineFunc('posix_getgid', function() {
            return ($this->hostinfo_access_granted() ? posix_getgid() : false);
        });

        $this->PHPSandbox->defineFunc('posix_setgid', function(int $group_id) {
            return ($this->hostinfo_access_granted() ? posix_setgid($group_id) : false);
        });

        $this->PHPSandbox->defineFunc('posix_getegid', function() {
            return ($this->hostinfo_access_granted() ? posix_getegid() : false);
        });

        $this->PHPSandbox->defineFunc('posix_setegid', function(int $group_id) {
            return ($this->hostinfo_access_granted() ? posix_setegid($group_id) : false);
        });

        $this->PHPSandbox->defineFunc('posix_getgroups', function() {
            return ($this->hostinfo_access_granted() ? posix_getgroups() : false);
        });

        $this->PHPSandbox->defineFunc('posix_getlogin', function() {
            return ($this->hostinfo_access_granted() ? posix_getlogin() : false);
        });

        $this->PHPSandbox->defineFunc('posix_getpgrp', function() {
            return ($this->hostinfo_access_granted() ? posix_getpgrp() : false);
        });

        $this->PHPSandbox->defineFunc('posix_setsid', function() {
            return ($this->hostinfo_access_granted() ? posix_setsid() : false);
        });

        $this->PHPSandbox->defineFunc('posix_setpgid', function(int $process_id, int $process_group_id) {
            return ($this->hostinfo_access_granted() ? posix_setpgid($process_id, $process_group_id) : false);
        });

        $this->PHPSandbox->defineFunc('posix_getpgid', function(int $process_id) {
            return ($this->hostinfo_access_granted() ? posix_getpgid($process_id) : false);
        });

        $this->PHPSandbox->defineFunc('posix_getsid', function(int $process_id) {
            return ($this->hostinfo_access_granted() ? posix_getsid($process_id) : false);
        });

        $this->PHPSandbox->defineFunc('posix_uname', function() {
            return ($this->hostinfo_access_granted() ? posix_uname() : false);
        });

        $this->PHPSandbox->defineFunc('posix_times', function() {
            return ($this->hostinfo_access_granted() ? posix_times() : false);
        });

        $this->PHPSandbox->defineFunc('posix_ctermid', function() {
            return ($this->hostinfo_access_granted() ? posix_ctermid() : false);
        });

        $this->PHPSandbox->defineFunc('posix_ttyname', function(resource|int $file_descriptor) {
            return ($this->hostinfo_access_granted() ? posix_ttyname($file_descriptor) : false);
        });

        $this->PHPSandbox->defineFunc('posix_isatty', function(resource|int $file_descriptor) {
            return ($this->hostinfo_access_granted() ? posix_isatty($file_descriptor) : false);
        });

        $this->PHPSandbox->defineFunc('posix_initgroups', function(string $username, int $group_id) {
            return ($this->hostinfo_access_granted() ? posix_initgroups($username, $group_id) : false);
        });

        $this->PHPSandbox->defineFunc('posix_getcwd', function() {
            return ($this->hostinfo_access_granted() ? posix_getcwd() : false);
        });

        $this->PHPSandbox->defineFunc('posix_getgrnam', function(string $name) {
            return ($this->hostinfo_access_granted() ? posix_getgrnam($name) : false);
        });

        $this->PHPSandbox->defineFunc('posix_getgrgid', function(string $group_id) {
            return ($this->hostinfo_access_granted() ? posix_getgrgid($group_id) : false);
        });

        $this->PHPSandbox->defineFunc('posix_getpwnam', function(string $username) {
            return ($this->hostinfo_access_granted() ? posix_getpwnam($username) : false);
        });

        $this->PHPSandbox->defineFunc('posix_getpwuid', function(string $user_id) {
            return ($this->hostinfo_access_granted() ? posix_getpwuid($user_id) : false);
        });

        $this->PHPSandbox->defineFunc('posix_getrlimit', function(?int $resource = null) {
            return ($this->hostinfo_access_granted() ? posix_getrlimit($resource) : false);
        });

        $this->PHPSandbox->defineFunc('posix_setrlimit', function(int $resource, int $soft_limit, int $hard_limit) {
            return ($this->hostinfo_access_granted() ? posix_setrlimit($resource, $soft_limit, $hard_limit) : false);
        });

        $this->PHPSandbox->defineFunc('getrusage', function(int $mode = 0) {
            return ($this->hostinfo_access_granted() ? getrusage($mode) : false);
        });

        $this->PHPSandbox->defineFunc('session_save_path', function(?string $path = null) {
            return ($this->hostinfo_access_granted() ? session_save_path($path) : false);
        });

        // Restrict shared memory usage
        $this->PHPSandbox->defineFunc('shmop_open', function(int $key, string $mode, int $permissions, int $size) {
            return ($this->sharedmemory_access_granted() ? shmop_open($key, $mode, $permissions, $size) : false);
        });

        $this->PHPSandbox->defineFunc('shmop_read', function(Shmop $shmop, int $offset, int $size) {
            return ($this->sharedmemory_access_granted() ? shmop_read($shmop, $offset, $size) : false);
        });

        $this->PHPSandbox->defineFunc('shmop_size', function(Shmop $shmop) {
            return ($this->sharedmemory_access_granted() ? shmop_read($shmop) : false);
        });

        $this->PHPSandbox->defineFunc('shmop_write', function(Shmop $shmop, string $data, int $offset) {
            return ($this->sharedmemory_access_granted() ? shmop_read($shmop, $data, $offset) : false);
        });

        $this->PHPSandbox->defineFunc('shmop_delete', function(Shmop $shmop) {
            return ($this->sharedmemory_access_granted() ? shmop_read($shmop) : false);
        });

        // Restrict rewriting headers
        $this->PHPSandbox->defineFunc('header', function(string $header, bool $replace = true, int $response_code = 0) {
            return ($this->config['permissions']['headers'] ? header($header, $replace, $response_code) : false);
        });

        $this->PHPSandbox->defineFunc('http_response_code', function(int $response_code = 0) {
            return ($this->config['permissions']['headers'] ? http_response_code($response_code) : false);
        });

        $this->PHPSandbox->defineFunc('get_headers', function(string $url, bool $associative = false, ?resource $context = null) {
            return ($this->config['permissions']['headers'] ? get_headers($url, $associative = false, $context) : false);
        });

        $this->PHPSandbox->defineFunc('apache_request_headers', function() {
            return ($this->config['permissions']['headers'] ? apache_request_headers() : false);
        });

        $this->PHPSandbox->defineFunc('getallheaders', function() {
            return ($this->config['permissions']['headers'] ? getallheaders() : false);
        });

        // Restrict setting cookies
        $this->PHPSandbox->defineFunc('setrawcookie', function(string $name, string $value = null, int $expires_or_options = 0, string $path = null, string $domain = null, bool $secure = false, bool $httponly = false) {
            return ($this->config['permissions']['cookies'] ? setrawcookie($name, $value, $expires_or_options, $path, $domain, $secure, $httponly) : false);
        });

        $this->PHPSandbox->defineFunc('setcookie', function(string $name, string $value = "", int $expires_or_options = 0, string $path = "", string $domain = "", bool $secure = false, bool $httponly = false) {
            return ($this->config['permissions']['cookies'] ? setcookie($name, $value, $expires_or_options, $path, $domain, $secure, $httponly) : false);
        });

        // Redefine other network functions
        // getservbyname
        $this->PHPSandbox->defineFunc('getservbyname', function(string $service, string $protocol) {
            return ($this->network_access_granted() ? getservbyname($service, $protocol) : false);
        });

        // getservbyport
        $this->PHPSandbox->defineFunc('getservbyport', function(int $port, string $protocol) {
            return ($this->network_access_granted() ? getservbyport($port, $protocol) : false);
        });

        // gethostbyaddr
        $this->PHPSandbox->defineFunc('gethostbyaddr', function(string $ip) {
            return ($this->network_access_granted() ? gethostbyaddr($ip) : false);
        });

        // gethostbyname
        $this->PHPSandbox->defineFunc('gethostbyname', function(string $hostname) {
            return ($this->network_access_granted() ? gethostbyname($hostname) : false);
        });

        // gethostbynamel
        $this->PHPSandbox->defineFunc('gethostbynamel', function(string $hostname) {
            return ($this->network_access_granted() ? gethostbynamel($hostname) : false);
        });

        // dns_check_record
        $this->PHPSandbox->defineFunc('dns_check_record', function(string $hostname, string $type = "MX") {
            return ($this->network_access_granted() ? dns_check_record($hostname, $type) : false);
        });

        // checkdnsrr
        $this->PHPSandbox->defineFunc('checkdnsrr', function(string $hostname, string $type = "MX") {
            return ($this->network_access_granted() ? checkdnsrr($hostname, $type) : false);
        });

        // dns_get_record
        $this->PHPSandbox->defineFunc('dns_get_record', function(string $hostname, int $type = DNS_ANY, array &$authoritative_name_servers = null, array &$additional_records = null, bool $raw = false) {
            return ($this->network_access_granted() ? dns_get_record($hostname, $type, $authoritative_name_servers, $additional_records, $raw) : false);
        });

        // dns_get_mx
        $this->PHPSandbox->defineFunc('dns_get_mx', function(string $hostname, array &$hosts, array &$weights = null) {
            return ($this->network_access_granted() ? getmxrr($hostname,  $hosts, $weights) : false);
        });

        // getmxrr
        $this->PHPSandbox->defineFunc('getmxrr', function(string $hostname, array &$hosts, array &$weights = null) {
            return ($this->network_access_granted() ? getmxrr($hostname,  $hosts, $weights) : false);
        });

        // stream_socket_client
        $this->PHPSandbox->defineFunc('stream_socket_client', function(string $address, int &$error_code = null, string &$error_message = null, ?float $timeout = null, int $flags = STREAM_CLIENT_CONNECT, ?resource $context = null) {
            return ($this->network_access_granted() ? stream_socket_client($address,  $error_code, $error_message, $timeout, $flags, $context) : false);
        });

        // stream_socket_server
        $this->PHPSandbox->defineFunc('stream_socket_server', function(string $address, int &$error_code = null, string &$error_message = null, int $flags = STREAM_SERVER_BIND | STREAM_SERVER_LISTEN, ?resource $context = null) {
            return ($this->network_access_granted() ? stream_socket_server($address,  $error_code, $error_message, $flags, $context) : false);
        });

        // curl_init
        $this->PHPSandbox->defineFunc('curl_init', function(?string $url = null) {
            return ($this->network_access_granted() ? curl_init($url) : false);
        });

        // ftp_connect
        $this->PHPSandbox->defineFunc('ftp_connect', function(string $hostname, int $port = 21, int $timeout = 90) {
            return ($this->network_access_granted() ? ftp_connect($hostname, $port, $timeout) : false);
        });

        // ftp_ssl_connect
        $this->PHPSandbox->defineFunc('ftp_ssl_connect', function(string $hostname, int $port = 21, int $timeout = 90) {
            return ($this->network_access_granted() ? ftp_ssl_connect($hostname, $port, $timeout) : false);
        });

        $this->PHPSandbox->defineFunc('socket_select', function(?array &$read, ?array &$write, ?array &$except, ?int $seconds, int $microseconds = 0) {
            return ($this->network_access_granted() ? socket_select($read, $write, $except, $seconds, $microseconds) : false);
        });

        $this->PHPSandbox->defineFunc('socket_create_listen', function(int $port, int $backlog = SOMAXCONN) {
            return ($this->network_access_granted() ? socket_create_listen($port, $backlog) : false);
        });

        $this->PHPSandbox->defineFunc('socket_create', function(int $domain, int $type, int $protocol) {
            return ($this->network_access_granted() ? socket_create($domain, $type, $protocol) : false);
        });

        $this->PHPSandbox->defineFunc('socket_create_pair', function(int $domain, int $type, int $protocol, array &$pair) {
            return ($this->network_access_granted() ? socket_create_pair($domain, $type, $protocol, $pair) : false);
        });

        $this->PHPSandbox->defineFunc('socket_addrinfo_bind', function(AddressInfo $address) {
            return ($this->network_access_granted() ? socket_addrinfo_bind($address) : false);
        });

        $this->PHPSandbox->defineFunc('socket_addrinfo_connect', function(AddressInfo $address) {
            return ($this->network_access_granted() ? socket_addrinfo_connect($address) : false);
        });

        $this->PHPSandbox->defineFunc('xmlwriter_open_uri', function(string $uri) {
            return ($this->network_access_granted() ? xmlwriter_open_uri($uri) : false);
        });

        // Redefine sleep functions, as sleeping might be disabled
        $this->PHPSandbox->defineFunc('sleep', function(int $seconds) {
            return ($this->config['permissions']['sleep'] ? sleep($seconds) : false);
        });

        $this->PHPSandbox->defineFunc('usleep', function(int $microseconds) {
            return ($this->config['permissions']['sleep'] ? usleep($microseconds) : false);
        });

        $this->PHPSandbox->defineFunc('time_nanosleep', function(int $seconds, int $nanoseconds) {
            return ($this->config['permissions']['sleep'] ? time_nanosleep($seconds, $nanoseconds) : false);
        });


        $this->PHPSandbox->defineFunc('time_sleep_until', function(float $timestamp) {
            return ($this->config['permissions']['sleep'] ? time_sleep_until($timestamp) : false);
        }); 

        // Restrict access to executing binaries
        $this->PHPSandbox->defineFunc('exec', function(string $command, array &$output = null, int &$result_code = null) {
            return ($this->binary_execution_access_granted($command) ? exec($command, $output, $result_code) : false);
        });

        $this->PHPSandbox->defineFunc('system', function(string $command, int &$result_code = null) {
            return ($this->binary_execution_access_granted($command) ? system($command, $result_code) : false);
        });

        $this->PHPSandbox->defineFunc('passthru', function(string $command, int &$result_code = null) {
            return ($this->binary_execution_access_granted($command) ? passthru($command, $result_code) : false);
        });

        $this->PHPSandbox->defineFunc('shell_exec', function(string $command) {
            return ($this->binary_execution_access_granted($command) ? shell_exec($command) : false);
        });

        $this->PHPSandbox->defineFunc('popen', function(string $command, string $mode) {
            return ($this->binary_execution_access_granted($command) ? popen($command, $mode) : false);
        });

        $this->PHPSandbox->defineFunc('proc_open', function(array|string $command, array $descriptor_spec, array &$pipes, ?string $cwd = null, ?array $env_vars = null,?array $options = null) {
            return ($this->binary_execution_access_granted($command) ? proc_open($command, $descriptor_spec, $pipes, $cwd, $env_vars, $options) : false);
        });

        // Restrict access to interprocess communication
        $this->PHPSandbox->defineFunc('posix_kill', function(int $process_id, int $signal) {
            return ($this->interprocess_communication_access_granted() ? posix_kill($process_id, $signal) : false);
        });

        $this->PHPSandbox->defineFunc('msg_get_queue', function(int $key, int $permissions = 0666) {
            return ($this->interprocess_communication_access_granted() ? msg_get_queue($key, $permissions) : false);
        });

        $this->PHPSandbox->defineFunc('msg_queue_exists', function(int $key) {
            return ($this->interprocess_communication_access_granted() ? msg_queue_exists($key) : false);
        });

        $this->PHPSandbox->defineFunc('sem_get', function(int $key, int $max_acquire = 1, int $permissions = 0666, bool $auto_release = true) {
            return ($this->interprocess_communication_access_granted() ? sem_get($key, $max_acquire, $permissions, $auto_release) : false);
        });

        $this->PHPSandbox->defineFunc('stream_socket_pair', function(int $domain, int $type, int $protocol) {
            return ($this->interprocess_communication_access_granted() ? stream_socket_pair($domain, $type, $protocol) : false);
        });

        // Reimplement the procedural style functions for mysqli
        $this->PHPSandbox->defineFunc('mysqli_connect', function(?string $hostname = null, ?string $username = null, #[\SensitiveParameter] ?string $password = null, ?string $database = null, ?int $port = null, ?string $socket = null) {
            return ((in_array($hostname, $this->config['database']['mysql']['hosts']) AND in_array($database, $this->config['database']['mysql']['database_names'])) ? mysqli_connect($hostname, $username, $password, $database, $port, $socket) : false);
        });

        $this->PHPSandbox->defineFunc('mysqli_execute_query', function(mysqli $mysql, string $query, ?array $params = null) {
            return (checkQuery($query, 'mysql', $this->config['database']) ? mysqli_execute_query($mysql, $query, $params) : false);
        });

        $this->PHPSandbox->defineFunc('mysqli_multi_query', function(mysqli $mysql, string $query) {
            return (checkQuery($query, 'mysql', $this->config['database']) ? mysqli_multi_query($mysql, $query) : false);
        });

        $this->PHPSandbox->defineFunc('mysqli_prepare', function(mysqli $mysql, string $query) {
            return (checkQuery($query, 'mysql', $this->config['database']) ? mysqli_prepare($mysql, $query) : false);
        });

        $this->PHPSandbox->defineFunc('mysqli_query', function(mysqli $mysql, string $query, int $result_mode = MYSQLI_STORE_RESULT) {
            return (checkQuery($query, 'mysql', $this->config['database']) ? mysqli_query($mysql, $query, $result_mode) : false);
        });

        $this->PHPSandbox->defineFunc('mysqli_real_connect', function(?string $hostname = null, ?string $username = null, #[\SensitiveParameter] ?string $password = null, ?string $database = null, ?int $port = null, ?string $socket = null, int $flags = 0) {
            return ((in_array($hostname, $this->config['database']['mysql']['hosts']) AND in_array($database, $this->config['database']['mysql']['database_names'])) ? mysqli_real_connect($hostname, $username, $password, $database, $port, $socket, $flags) : false);
        });

        $this->PHPSandbox->defineFunc('mysqli_select_db', function(mysqli $mysql, string $database) {
            return (in_array($database, $this->config['database']['mysql']['database_names']) ? mysqli_select_db($mysql, $database) : false);
        });

        $this->PHPSandbox->defineFunc('mysqli_real_query', function(mysqli $mysql, string $query) {
            return (checkQuery($query, 'mysql', $this->config['database']) ? mysqli_real_query($mysql, $query) : false);
        });

        // Reimplement the mysqli_stmt_* procedural functions
        $this->PHPSandbox->defineFunc('mysqli_stmt_prepare', function(mysqli_stmt $statement, string $query) {
            return (checkQuery($query, 'mysql', $this->config['database']) ? mysqli_stmt_prepare($statement, $query) : false);
        });


        // Add empty functions to prevent errors inside the sandbox and make normal includes (which wouldn't respect storage restrictions) provided by the sandboxing library useless
        $this->PHPSandbox->defineFunc('include', function() {});
        $this->PHPSandbox->defineFunc('include_once', function() {});
        $this->PHPSandbox->defineFunc('require', function() {});
        $this->PHPSandbox->defineFunc('require_once', function() {});

        // redefine zend_version function
        $this->PHPSandbox->defineFunc('zend_version', function() {
            return 'SandyPHP based on zend: '.zend_version();
        });

        $this->PHPSandbox->blacklistClass(['PDO' => 'PDO']);
        $this->PHPSandbox->blacklistClass(['mysqli' => 'mysqli']);
        $this->PHPSandbox->blacklistClass(['mysqli_stmt' => 'mysqli_stmt']);        

        // Make sure these classes only exist if the respective driver is enabled
        if(isset($this->config['database']['mysql'])) {
            if (function_exists('mysqli_init') && extension_loaded('mysqli')) {
                $this->tailorClass('SandyPHPVirtMySQLIDriver');
                $this->PHPSandbox->defineClass('mysqli', 'SandyPHPVirtMySQLIDriver_SANDBOX_'.$this->id);
                $this->PHPSandbox->defineClass('mysqli_stmt', 'SandyPHPVirtMySQLiPreparedStatement_SANDBOX_'.$this->id);
            } else {
                require_once 'MYSQLIwarning.php';
                $this->PHPSandbox->defineClass('mysqli', 'MySQLIErrorDummy');
            }
        }
        if(isset($this->config['database']['mysql']) OR isset($this->config['database']['sqlite'])) {
            $this->tailorClass('SandyPHPVirtPDODriver');
            $this->PHPSandbox->defineClass('PDO', 'SandyPHPVirtPDODriver_SANDBOX_'.$this->id);
        }
    }

    public function runFile(string $filename, bool $print_output = true) {
        return $this->run(file_get_contents($filename), $print_output);
    }

    public function run(string $code, bool $print_output = true) {
        $execID = uniqid(); // This id is important for logging. It marks the start and end of the execution of any script. It is temporary.
        $this->logger->log(new ScriptExecutionStartNotice($this->id, $execID, $this->config['debug_output']['notice']));
        $this->PHPSandbox->execute($this->static_include_files($code));
        $this->logger->log(new ScriptExecutionEndNotice($this->id, $execID, $this->config['debug_output']['notice']));
        return ($print_output ? ob_get_contents() : ob_get_clean());
    }

    public function setOption(string $option, $value) {
        $config[$option] = $value;
    }

    // Reimplement safe includes by finding and injecting to be included files into the sandbox
    protected function static_include_files($code) {
        if($this->config['permissions']['include']) {
            $function_calls = explode(';', $code);
            $includes = preg_grep("~(include|include_once|require|require_once)~", $function_calls);
            foreach($includes AS $include) {
                $filename = preg_replace("~(\"|'|\))~", '', trim(preg_split("~( |\()~", $include)[2]));
                // Includes over the network are explicitely disabled for security reasons
                if($this->storage_access_granted($filename) AND !isNetworkLocation($filename)) $function_calls[array_search($include, $function_calls)] = preg_replace('~(require|require_once|include|include_once) \(?".*"\)?~', str_replace('?>', '', str_replace('<?php', '', file_get_contents($this->storage_get_realpath($filename)))), $include);
            }
            return implode(';', $function_calls);
        }
        return false;
    }

    public function __destruct() {
        $this->logger->log(new SandboxDestructionNotice($this->id, $this->config['debug_output']['notice']));
    }

}

    $sbox = new SandyPHPSandbox(SANDBOX_CONFIG);
    $sbox->runFile('testscript.sphp');

    //ob_end_flush();

echo "\nSandyPHP exited successfully and used ".(memory_get_peak_usage() / 1000000)."mb of RAM during peaks\n";
?>