<?php

namespace SandboxModules;

ini_set('display_errors', true);
error_reporting(E_ALL);

// This code defines sandboxes in which extensions to StudEzy will be running...

require '../../classes/user.php';
require 'vendor/autoload.php';

ob_start();

$sandbox = new \PHPSandbox\PHPSandbox();

define('SANDBOX_CONFIG', json_decode(file_get_contents('exampleconfig.json'), true));
define('SANDBOX_WHITELIST', json_decode(file_get_contents('whitelist.json'), true));
define('SANDBOX_SUPPORTED_PDO_DRIVERS', array());

function studezy_version_info() {
   return 'StudEzy 0.0.0beta';
}

function isNetworkLocation(string $filename) : bool {
    return preg_match("~^(https?|ftp)://~", $filename);
}

function isAllowedWrapper(string $filename) {
    return !preg_match("~^(php|zlib|glob|phar|ssh2|rar|ogg|expect|unix|udg)://~", $filename);
}


function file_is_within_folders(string $filename, $allowedPaths) : bool {
    $match = false;
    foreach($allowedPaths as $allowedPath) {
        if(strncmp(realpath($filename), $allowedPath, strlen($allowedPath)) === 0) $match = true;
    }
    return $match;
}

function storage_access_granted($filename) {
    if(basename($filename) == 'manifest.json' OR (isNetworkLocation($filename) AND !SANDBOX_CONFIG['permissions']['network']) OR !isAllowedWrapper($filename)) return false; // Applications are not allowed access to their manifest file, they can request configuration info instead
    else if(file_is_within_folders(storage_get_realpath($filename), SANDBOX_CONFIG['permissions']['storage']) OR SANDBOX_CONFIG['permissions']['storage'][0] === '*') return true;
    die('FATAL: request for non-whitelisted file: <i>'.storage_get_realpath($filename).'</i>');
    return false;
}

function storage_get_realpath($filename) {
    if(isNetworkLocation($filename)) return $filename;
    else if(isset(SANDBOX_CONFIG['storage']['fsroot'])) return SANDBOX_CONFIG['storage']['fsroot'].str_replace('..', '', $filename);
    else return realpath($filename);
}


$sandbox->whitelistFunc('studezy_version_info');
$sandbox->whitelistFunc(SANDBOX_WHITELIST);
$sandbox->allow_includes = true; //SANDBOX_CONFIG['permissions']['include'] | false;
$sandbox->allow_escaping = SANDBOX_CONFIG['permissions']['escape'] | false;
$sandbox->allow_functions = SANDBOX_CONFIG['permissions']['functions'] | false;
$sandbox->allow_aliases = true;
$sandbox->validate_types = false;
$sandbox->auto_whitelist_functions = true;

if(isset(SANDBOX_CONFIG['exposeScripts'])) {
    foreach(SANDBOX_CONFIG['exposeScripts']['scripts'] AS $script) {
        require_once $script;
    }
    if(isset(SANDBOX_CONFIG['exposeScripts']['whitelist']['classes']) AND SANDBOX_CONFIG['exposeScripts']['whitelist']['classes'] !== false) $sandbox->whitelistClass(SANDBOX_CONFIG['exposeScripts']['whitelist']['classes']);
    if(isset(SANDBOX_CONFIG['exposeScripts']['whitelist']['functions']) AND SANDBOX_CONFIG['exposeScripts']['whitelist']['functions'] !== false) $sandbox->whiteListFunc(SANDBOX_CONFIG['exposeScripts']['whitelist']['functions']);
}


// Redefine all functions associated with file handling to enforce storage access restrictions

// #1 file_get_contents
$sandbox->defineFunc('file_get_contents', function(string $filename) {
    if(storage_access_granted($filename)) return file_get_contents(storage_get_realpath($filename));
    return false;
});

// #2 file_put_contents
$sandbox->defineFunc('file_put_contents', function(string $filename, $flags, $ctx = null) {
    return (storage_access_granted($filename) ? file_put_contents(storage_get_realpath($filename), $flags, $ctx) : false);
});


// #3 fopen
$sandbox->defineFunc('fopen', function(string $filename, $mode, $use_include_path = false, $context = null) {
    return (storage_access_granted($filename) ? fopen(storage_get_realpath($filename), $mode, $use_include_path, $context) : false);
});


// #4 openssl_x509_export_to_file
$sandbox->defineFunc('openssl_x509_export_to_file', function($certificate, $output_filename, $no_text = true) {
    return (storage_access_granted($output_filename) ? openssl_x509_export_to_file($certificate, storage_get_realpath($output_filename), $no_text) : false);
});


// #5 openssl_pkcs12_export_to_file
$sandbox->defineFunc('openssl_pkcs12_export_to_file', function($certificate, string $output_filename, OpenSSLAsymmetricKey|OpenSSLCertificate|array|string $private_key, string $passphrase, array $options = []) {
    return (storage_access_granted($output_filename) ? openssl_pkcs12_export_to_file($certificate, storage_get_realpath($output_filename), $private_key, $passphrase, $options) : false);
});

// #6 openssl_csr_export_to_file
$sandbox->defineFunc('openssl_csr_export_to_file', function(OpenSSLCertificateSigningRequest|string $csr, string $output_filename, bool $no_text = true) {
    return (storage_access_granted($output_filename) ? openssl_csr_export_to_file($csr, storage_get_realpath($output_filename), $no_text) : false);
});

// #7 openssl_pkey_export_to_file
$sandbox->defineFunc('openssl_pkey_export_to_file', function(OpenSSLAsymmetricKey|OpenSSLCertificate|array|string $key, string $output_filename, ?string $passphrase = null, ?array $options = null) {
    return (storage_access_granted($output_filename) ? openssl_pkey_export_to_file($key, storage_get_realpath($output_filename), $passphrase, $options) : false);
});

// #8 gzfile
$sandbox->defineFunc('gzfile', function(string $filename, int $use_include_path = 0) {
    return (storage_access_granted($filename) ? gzfile(storage_get_realpath($filename), $use_include_path) : false);
});

// #9 gzopen
$sandbox->defineFunc('gzopen', function(string $filename, string $mode, int $use_include_path = 0) {
    return (storage_access_granted($filename) ? gzopen(storage_get_realpath($filename), $mode, $use_include_path) : false);
});

// #10 readgzfile
$sandbox->defineFunc('readgzfile', function(string $filename, int $use_include_path = 0) {
    return (storage_access_granted($filename) ? readgzfile($key, storage_get_realpath($output_filename), $passphrase, $options) : false);
});

// #11 hash_file
$sandbox->defineFunc('hash_file', function(string $algo, string $filename, bool $binary = false, array $options = []) {
    return (storage_access_granted($filename) ? hash_file($algo, storage_get_realpath($filename), $binary, $options) : false);
});

// #12 hash_hmac_file
$sandbox->defineFunc('hash_hmac_file', function(string $algo, string $filename, string $key, bool $binary = false) {
    return (storage_access_granted($filename) ? hash_hmac_file($algo, storage_get_realpath($filename, $key, $binary)) : false);
});

// #13 hash_update_file
$sandbox->defineFunc('hash_update_file', function(HashContext $context, string $filename, $stream_context = null) {
    return (storage_access_granted($filename) ? hash_update_file($context, storage_get_realpath($filename), $stream_context) : false);
});

// #14 highlight_file
$sandbox->defineFunc('highlight_file', function(string $filename, bool $return = false) {
    return (storage_access_granted($filename) ? highlight_file(storage_get_realpath($filename), $return) : false);
});

// #15 is_uploaded_file
$sandbox->defineFunc('is_uploaded_file', function(string $filename) {
    return (storage_access_granted($filename) AND SANDBOX_CONFIG['permissions']['fileupload'] ? is_uploaded_file(storage_get_realpath($filename)) : false);
});

// #16 move_uploaded_file
$sandbox->defineFunc('move_uploaded_file', function(string $from, string $to) {
    return (storage_access_granted($to) AND SANDBOX_CONFIG['permissions']['fileupload'] ? move_uploaded_file($from, storage_get_realpath($to)) : false);
});

// #17 parse_ini_file
$sandbox->defineFunc('parse_ini_file', function(string $filename, bool $process_sections = false, int $scanner_mode = INI_SCANNER_NORMAL) {
    return (storage_access_granted($filename) ? parse_ini_file(storage_get_realpath($filename), $process_sections, $scanner_mode) : false);
});

// #18 md5_file
$sandbox->defineFunc('md5_file', function(string $filename, bool $binary = false) {
    return (storage_access_granted($filename) ? md5_file(storage_get_realpath($filename), $binary) : false);
});

// #19 sha1_file
$sandbox->defineFunc('sha1_file', function(string $filename, bool $binary = false) {
    return (storage_access_granted($filename) ? sha1_file(storage_get_realpath($filename), $binary) : false);
});

// #20 readfile
$sandbox->defineFunc('readfile', function(string $filename, bool $use_include_path = false, $context = null) {
    return (storage_access_granted($filename) ? readfile(storage_get_realpath($filename), $use_include_path, $context) : false);
});

// #21 file
$sandbox->defineFunc('file', function(string $filename, int $flags = 0, $context = null) {
    return (storage_access_granted($filename) ? file(storage_get_realpath($filename), $flags, $context) : false);
});

// #22 fileatime
$sandbox->defineFunc('fileatime', function(string $filename) {
    return (storage_access_granted($filename) ? fileatime(storage_get_realpath($filename)) : false);
});

// #23 filectime
$sandbox->defineFunc('filectime', function(string $filename) {
    return (storage_access_granted($filename) ? filectime(storage_get_realpath($filename)) : false);
});

// #24 filegroup
$sandbox->defineFunc('filegroup', function(string $filename) {
    return (storage_access_granted($filename) ? filegroup(storage_get_realpath($filename)) : false);
});

// #25 fileinode
$sandbox->defineFunc('fileinode', function(string $filename) {
    return (storage_access_granted($filename) ? fileinode(storage_get_realpath($filename)) : false);
});

// #26 filemtime
$sandbox->defineFunc('filemtime', function(string $filename) {
    return (storage_access_granted($filename) ? filemtime(storage_get_realpath($filename)) : false);
});

// #27 fileowner
$sandbox->defineFunc('fileowner', function(string $filename) {
    return (storage_access_granted($filename) ? fileowner(storage_get_realpath($filename)) : false);
});

// #28 fileperms
$sandbox->defineFunc('fileperms', function(string $filename) {
    return (storage_access_granted($filename) ? fileperms(storage_get_realpath($filename)) : false);
});

// #29 filesize
$sandbox->defineFunc('filesize', function(string $filename) {
    return (storage_access_granted($filename) ? filesize(storage_get_realpath($filename)) : false);
});

// #30 filetype
$sandbox->defineFunc('filetype', function(string $filename) {
    return (storage_access_granted($filename) ? filetype(storage_get_realpath($filename)) : false);
});

// #31 file_exists
$sandbox->defineFunc('file_exists', function(string $filename) {
    return (storage_access_granted($filename) ? file_exists(storage_get_realpath($filename)) : false);
});

// #32 is_file
$sandbox->defineFunc('is_file', function(string $filename) {
    return (storage_access_granted($filename) ? is_file(storage_get_realpath($filename)) : false);
});

// #33 finfo_file
$sandbox->defineFunc('finfo_file', function(finfo $finfo, string $filename, int $flags = FILEINFO_NONE, $context = null) {
    return (storage_access_granted($filename) ? finfo_file($finfo, storage_get_realpath($filename), $flags, $context) : false);
});

// #34 simplexml_load_file
$sandbox->defineFunc('simplexml_load_file', function(string $filename, ?string $class_name = SimpleXMLElement::class, int $options = 0, string $namespace_or_prefix = "", bool $is_prefix = false) {
    return (storage_access_granted($filename) ? simplexml_load_file(storage_get_realpath($filename), $class_name, $options, $namespace_or_prefix, $is_prefix) : false);
});

// #35 unlink
$sandbox->defineFunc('unlink', function(string $filename, $context = null) {
    return (storage_access_granted($filename) ? unlink(storage_get_realpath($filename), $context) : false);
});

$sandbox->defineFunc('opendir', function(string $directory, $context = null) {
    return (storage_access_granted($directory) ? opendir(storage_get_realpath($directory), $context) : false);
});

$sandbox->defineFunc('dir', function(string $directory, $context = null) {
    return (storage_access_granted($directory) ? dir(storage_get_realpath($directory), $context) : false);
});

$sandbox->defineFunc('get_meta_tags', function(string $filename, bool $use_include_path = false) {
    return (storage_access_granted($filename) ? get_meta_tags(storage_get_realpath($filename), $use_include_path) : false);
});

$sandbox->defineFunc('rmdir', function(string $directory, $context = null) {
    return (storage_access_granted($directory) ? rmdir(storage_get_realpath($directory), $context) : false);
});

$sandbox->defineFunc('mkdir', function(string $directory, int $permissions = 0777, bool $recursive = false, $context = null) {
    return (storage_access_granted($directory) ? mkdir(storage_get_realpath($directory), $permissions) : false);
});

$sandbox->defineFunc('rename', function(string $from, string $to, $context = null) {
    return (storage_access_granted($from) AND storage_access_granted($to) ? rename(storage_get_realpath($from), storage_get_realpath($to), $context) : false);
});

$sandbox->defineFunc('copy', function(string $from, string $to, $context = null) {
    return (storage_access_granted($from) AND storage_access_granted($to) ? copy(storage_get_realpath($from), storage_get_realpath($to), $context) : false);
});

$sandbox->defineFunc('tempnam', function(string $directory, string $prefix) {
    return (storage_access_granted($directory) ? tempnam(storage_get_realpath($directory), $prefix) : false);
});

$sandbox->defineFunc('is_writable', function(string $filename) {
    return (storage_access_granted($filename) ? is_writable(storage_get_realpath($filename)) : false);
});

$sandbox->defineFunc('is_writeable', function(string $filename) {
    return (storage_access_granted($filename) ? is_writeable(storage_get_realpath($filename)) : false);
});

$sandbox->defineFunc('is_readable', function(string $filename) {
    return (storage_access_granted($filename) ? is_readable(storage_get_realpath($filename)) : false);
});

$sandbox->defineFunc('is_executable', function(string $filename) {
    return (storage_access_granted($filename) ? is_executable(storage_get_realpath($filename)) : false);
});

$sandbox->defineFunc('is_dir', function(string $filename) {
    return (storage_access_granted($filename) ? is_dir(storage_get_realpath($filename)) : false);
});

$sandbox->defineFunc('is_link', function(string $filename) {
    return (storage_access_granted($filename) ? is_link(storage_get_realpath($filename)) : false);
});

$sandbox->defineFunc('stat', function(string $filename) {
    return (storage_access_granted($filename) ? stat(storage_get_realpath($filename)) : false);
});

$sandbox->defineFunc('lstat', function(string $filename) {
    return (storage_access_granted($filename) ? lstat(storage_get_realpath($filename)) : false);
});

$sandbox->defineFunc('chown', function(string $filename, string|int $user) {
    return (storage_access_granted($filename) ? chown(storage_get_realpath($filename), $user) : false);
});

$sandbox->defineFunc('chgrp', function(string $filename, string|int $group) {
    return (storage_access_granted($filename) ? chgrp(storage_get_realpath($filename), $group) : false);
});

$sandbox->defineFunc('lchown', function(string $filename, string|int $user) {
    return (storage_access_granted($filename) ? lchown(storage_get_realpath($filename), $user) : false);
});

$sandbox->defineFunc('lchgrp', function(string $filename, string|int $group) {
    return (storage_access_granted($filename) ? lchgrp(storage_get_realpath($filename), $group) : false);
});

$sandbox->defineFunc('chmod', function(string $filename, int $permissions) {
    return (storage_access_granted($filename) ? chmod(storage_get_realpath($filename), $permissions) : false);
});

$sandbox->defineFunc('touch', function(string $filename, ?int $mtime = null, ?int $atime = null) {
    return (storage_access_granted($filename) ? touch(storage_get_realpath($filename), $mtime, $atime) : false);
});

$sandbox->defineFunc('disk_total_space', function(string $directory) {
    return (storage_access_granted($directory) AND SANDBOX_CONFIG['permissions']['hostinfo'] ? disk_total_space(storage_get_realpath($directory)) : false);
});

$sandbox->defineFunc('disk_free_space', function(string $directory) {
    return (storage_access_granted($directory) AND SANDBOX_CONFIG['permissions']['hostinfo'] ? disk_free_space(storage_get_realpath($directory)) : false);
});

$sandbox->defineFunc('diskfreespace', function(string $directory) {
    return (storage_access_granted($directory) AND SANDBOX_CONFIG['permissions']['hostinfo'] ? diskfreespace(storage_get_realpath($directory)) : false);
});

$sandbox->defineFunc('fsockopen', function(string $hostname, int $port = -1, int &$error_code = null, string &$error_message = null, ?float $timeout = null) {
    return (storage_access_granted($hostname) ? fsockopen(storage_get_realpath($hostname), $port, $error_code, $error_message, $timeout) : false);
});

$sandbox->defineFunc('pfsockopen', function(string $hostname, int $port = -1, int &$error_code = null, string &$error_message = null, ?float $timeout = null) {
    return (storage_access_granted($hostname) ? pfsockopen(storage_get_realpath($hostname), $port, $error_code, $error_message, $timeout) : false);
});

$sandbox->defineFunc('getimagesize', function(string $filename, array &$image_info = null) {
    return (storage_access_granted($hostname) ? getimagesize(storage_get_realpath($filename), $image_info) : false);
});

$sandbox->defineFunc('iptcembed', function(string $iptc_data, string $filename, int $spool = 0) {
    return (storage_access_granted($filename) ? iptcembed($iptc_data, storage_get_realpath($filename), $spool) : false);
});

$sandbox->defineFunc('readlink', function(string $path) {
    return (storage_access_granted($path) ? readlink(storage_get_realpath($path)) : false);
});

$sandbox->defineFunc('linkinfo', function(string $path) {
    return (storage_access_granted($path) ? linkinfo(storage_get_realpath($path)) : false);
});

$sandbox->defineFunc('symlink', function(string $target, string $link) {
    return (storage_access_granted($target) AND storage_access_granted($link) ? symlink(storage_get_realpath($target), storage_get_realpath($link)) : false);
});

$sandbox->defineFunc('link', function(string $target, string $link) {
    return (storage_access_granted($target) AND storage_access_granted($link) ? link(storage_get_realpath($target), storage_get_realpath($link)) : false);
});

$sandbox->defineFunc('bzopen', function(string|resource $file, string $mode) {
    return ((storage_access_granted($file) OR get_resource_type($file) == 'stream') ? bzopen((get_resource_type($file) == 'stream' ? $file : storage_get_realpath($file)), $mode): false);
});

$sandbox->defineFunc('ftp_get', function(FTP\Connection $ftp, string $local_filename, string $remote_filename, int $mode = FTP_BINARY, int $offset = 0) {
    return (storage_access_granted($local_filename) AND SANDBOX_CONFIG['permissions']['network'] ? ftp_get($ftp, storage_get_realpath($local_filename), $remote_filename, $mode, $offset) : false);
});

$sandbox->defineFunc('ftp_put', function(FTP\Connection $ftp, string $remote_filename, string $local_filename, int $mode = FTP_BINARY, int $offset = 0) {
    return (storage_access_granted($local_filename) AND SANDBOX_CONFIG['permissions']['network'] ? ftp_put($ftp, $remote_filename, storage_get_realpath($local_filename), $mode, $offset) : false);
});

$sandbox->defineFunc('ftp_append', function(FTP\Connection $ftp, string $remote_filename, string $local_filename, int $mode = FTP_BINARY) {
    return (storage_access_granted($local_filename) AND SANDBOX_CONFIG['permissions']['network'] ? ftp_append($ftp, $remote_filename, storage_get_realpath($local_filename), $mode) : false);
});

$sandbox->defineFunc('ftp_nb_put', function(FTP\Connection $ftp, string $remote_filename, string $local_filename, int $mode = FTP_BINARY, int $offset = 0) {
    return (storage_access_granted($local_filename) AND SANDBOX_CONFIG['permissions']['network'] ? ftp_nb_put($ftp, $remote_filename, storage_get_realpath($local_filename), $mode, $offset) : false);
});

// Restrict ability to send emails
$sandbox->defineFunc('mail', function(string $to, string $subject, string $message, array|string $additional_headers = [], string $additional_params = "") {
    return (SANDBOX_CONFIG['permissions']['mail'] ? mail($to, $subject, $message, $additional_headers, $additional_params) : false);
});

$sandbox->defineFunc('mb_send_mail', function(string $to, string $subject, string $message, array|string $additional_headers = [], string $additional_params = "") {
    return (SANDBOX_CONFIG['permissions']['mail'] ? mb_send_mail($to, $subject, $message, $additional_headers, $additional_params) : false);
});

// Some functions need to be disabled but should not throw errors inside the sandbox in order for scripts using them not to fail, so they are redefined returning a value which won't cause most scripts to fail...
// #36 ini_set
$sandbox->defineFunc('ini_set', function(string $option, string|int|float|bool|null $value) {
    return false;
});

// #37 ini_alter
$sandbox->defineFunc('ini_alter', function(string $option, string|int|float|bool|null $value) {
    return false;
});

// #38 ini_restore
$sandbox->defineFunc('ini_restore', function(string $option) {
    return false;
});

// #39 set_include_path
$sandbox->defineFunc('set_include_path', function(string $include_path) {
    return false;
});

// #40 ignore_user_abort
$sandbox->defineFunc('ignore_user_abort', function(?bool $enable = null) {
    return false;
});

$sandbox->defineFunc('pdo_drivers', function() {
    return array_map(function($driver) {
        if(in_array($driver, SANDBOX_SUPPORTED_PDO_DRIVERS)) return 'SandyPHPVirtPDODriver for '.$driver;
        return 'No SandyPHPVirtPDODriver for '.$driver;
    }, pdo_drivers());
});


// Restrict access to host information
// gethostname
$sandbox->defineFunc('gethostname', function() {
    return (SANDBOX_CONFIG['permissions']['hostinfo'] ? gethostname() : 'PHPSandboxVirtEnv');
});

$sandbox->defineFunc('net_get_interfaces', function() {
    return (SANDBOX_CONFIG['permissions']['hostinfo'] ? net_get_interfaces() : false);
});

$sandbox->defineFunc('getmyuid', function() {
    return (SANDBOX_CONFIG['permissions']['hostinfo'] ? getmyuid() : false);
});

$sandbox->defineFunc('getmygid', function() {
    return (SANDBOX_CONFIG['permissions']['hostinfo'] ? getmygid() : false);
});

$sandbox->defineFunc('getmypid', function() {
    return (SANDBOX_CONFIG['permissions']['hostinfo'] ? getmypid() : false);
});

$sandbox->defineFunc('getmyinode', function() {
    return (SANDBOX_CONFIG['permissions']['hostinfo'] ? getmyinode() : false);
});

$sandbox->defineFunc('getlastmod', function() {
    return (SANDBOX_CONFIG['permissions']['hostinfo'] ? getlastmod() : false);
});

$sandbox->defineFunc('openlog', function(string $prefix, int $flags, int $facility) {
    return (SANDBOX_CONFIG['permissions']['hostinfo'] ? openlog($prefix, $flags, $facility) : false);
});

$sandbox->defineFunc('sys_getloadavg', function() {
    return (SANDBOX_CONFIG['permissions']['hostinfo'] ? sys_getloadavg() : false);
});

$sandbox->defineFunc('get_current_user', function() {
    return (SANDBOX_CONFIG['permissions']['hostinfo'] ? get_current_user() : false);
});

$sandbox->defineFunc('sys_get_temp_dir', function() {
    return (SANDBOX_CONFIG['permissions']['hostinfo'] ? sys_get_temp_dir() : 'PHPSandboxVirtStorage');
});

$sandbox->defineFunc('phpinfo', function() {
    return (SANDBOX_CONFIG['permissions']['hostinfo'] ? phpinfo() : 'SandyPHP');
});

$sandbox->defineFunc('phpversion', function() {
    return (SANDBOX_CONFIG['permissions']['hostinfo'] ? phpversion() : 'SandyPHP 0.0.0beta');
});

$sandbox->defineFunc('phpcredits', function() {
    return (SANDBOX_CONFIG['permissions']['hostinfo'] ? phpcredits() : 'Credits can be found on the PHP website, this feature is disabled for security reasons, big thanks to the PHP guys anyway!');
});

$sandbox->defineFunc('php_sapi_name', function() {
    return (SANDBOX_CONFIG['permissions']['hostinfo'] ? php_sapi_name() : false);
});

$sandbox->defineFunc('php_uname', function(string $mode = "a") {
    return (SANDBOX_CONFIG['permissions']['hostinfo'] ? php_uname($mode) : 'SandyPHP');
});


// Restrict rewriting headers
$sandbox->defineFunc('header', function(string $header, bool $replace = true, int $response_code = 0) {
    return (SANDBOX_CONFIG['permissions']['headers'] ? header($header, $replace, $response_code) : false);
});

$sandbox->defineFunc('http_response_code', function(int $response_code = 0) {
    return (SANDBOX_CONFIG['permissions']['headers'] ? http_response_code($response_code) : false);
});

// Restrict setting cookies
$sandbox->defineFunc('setrawcookie', function(string $name, string $value = null, int $expires_or_options = 0, string $path = null, string $domain = null, bool $secure = false, bool $httponly = false) {
    return (SANDBOX_CONFIG['permissions']['cookies'] ? setrawcookie($name, $value, $expires_or_options, $path, $domain, $secure, $httponly) : false);
});

$sandbox->defineFunc('setcookie', function(string $name, string $value = "", int $expires_or_options = 0, string $path = "", string $domain = "", bool $secure = false, bool $httponly = false) {
    return (SANDBOX_CONFIG['permissions']['cookies'] ? setcookie($name, $value, $expires_or_options, $path, $domain, $secure, $httponly) : false);
});

// Redefine other network functions
// getservbyname
$sandbox->defineFunc('getservbyname', function(string $service, string $protocol) {
    return (SANDBOX_CONFIG['permissions']['network'] ? getservbyname($service, $protocol) : false);
});

// getservbyport
$sandbox->defineFunc('getservbyport', function(int $port, string $protocol) {
    return (SANDBOX_CONFIG['permissions']['network'] ? getservbyport($port, $protocol) : false);
});

// gethostbyaddr
$sandbox->defineFunc('gethostbyaddr', function(string $ip) {
    return (SANDBOX_CONFIG['permissions']['network'] ? gethostbyaddr($ip) : false);
});

// gethostbyname
$sandbox->defineFunc('gethostbyname', function(string $hostname) {
    return (SANDBOX_CONFIG['permissions']['network'] ? gethostbyname($hostname) : false);
});

// gethostbynamel
$sandbox->defineFunc('gethostbynamel', function(string $hostname) {
    return (SANDBOX_CONFIG['permissions']['network'] ? gethostbynamel($hostname) : false);
});

// dns_check_record
$sandbox->defineFunc('dns_check_record', function(string $hostname, string $type = "MX") {
    return (SANDBOX_CONFIG['permissions']['network'] ? dns_check_record($hostname, $type) : false);
});

// checkdnsrr
$sandbox->defineFunc('checkdnsrr', function(string $hostname, string $type = "MX") {
    return (SANDBOX_CONFIG['permissions']['network'] ? checkdnsrr($hostname, $type) : false);
});

// dns_get_record
$sandbox->defineFunc('dns_get_record', function(string $hostname, int $type = DNS_ANY, array &$authoritative_name_servers = null, array &$additional_records = null, bool $raw = false) {
    return (SANDBOX_CONFIG['permissions']['network'] ? dns_get_record($hostname, $type, $authoritative_name_servers, $additional_records, $raw) : false);
});

// dns_get_mx
$sandbox->defineFunc('dns_get_mx', function(string $hostname, array &$hosts, array &$weights = null) {
    return (SANDBOX_CONFIG['permissions']['network'] ? getmxrr($hostname,  $hosts, $weights) : false);
});

// getmxrr
$sandbox->defineFunc('getmxrr', function(string $hostname, array &$hosts, array &$weights = null) {
    return (SANDBOX_CONFIG['permissions']['network'] ? getmxrr($hostname,  $hosts, $weights) : false);
});

// stream_socket_client
$sandbox->defineFunc('stream_socket_client', function(string $address, int &$error_code = null, string &$error_message = null, ?float $timeout = null, int $flags = STREAM_CLIENT_CONNECT, ?resource $context = null) {
    return (SANDBOX_CONFIG['permissions']['network'] ? stream_socket_client($address,  $error_code, $error_message, $timeout, $flags, $context) : false);
});

// stream_socket_server
$sandbox->defineFunc('stream_socket_server', function(string $address, int &$error_code = null, string &$error_message = null, int $flags = STREAM_SERVER_BIND | STREAM_SERVER_LISTEN, ?resource $context = null) {
    return (SANDBOX_CONFIG['permissions']['network'] ? stream_socket_server($address,  $error_code, $error_message, $flags, $context) : false);
});

// curl_init
$sandbox->defineFunc('curl_init', function(?string $url = null) {
    return (SANDBOX_CONFIG['permissions']['network'] ? curl_init($url) : false);
});

// ftp_connect
$sandbox->defineFunc('ftp_connect', function(string $hostname, int $port = 21, int $timeout = 90) {
    return (SANDBOX_CONFIG['permissions']['network'] ? ftp_connect($hostname, $port, $timeout) : false);
});

// ftp_ssl_connect
$sandbox->defineFunc('ftp_ssl_connect', function(string $hostname, int $port = 21, int $timeout = 90) {
    return (SANDBOX_CONFIG['permissions']['network'] ? ftp_ssl_connect($hostname, $port, $timeout) : false);
});



// Redefine sleep functions, as sleeping might be disabled
$sandbox->defineFunc('sleep', function(int $seconds) {
    return (SANDBOX_CONFIG['permissions']['sleep'] ? sleep($seconds) : false);
});

$sandbox->defineFunc('usleep', function(int $microseconds) {
    return (SANDBOX_CONFIG['permissions']['sleep'] ? usleep($microseconds) : false);
});

$sandbox->defineFunc('time_nanosleep', function(int $seconds, int $nanoseconds) {
    return (SANDBOX_CONFIG['permissions']['sleep'] ? time_nanosleep($seconds, $nanoseconds) : false);
});


$sandbox->defineFunc('time_sleep_until', function(float $timestamp) {
    return (SANDBOX_CONFIG['permissions']['sleep'] ? time_sleep_until($timestamp) : false);
}); 

// Restrict access to executing binaries
$sandbox->defineFunc('exec', function(string $command, array &$output = null, int &$result_code = null) {
    return (SANDBOX_CONFIG['permissions']['bin_exec'] ? exec($command, $output, $result_code) : false);
});

$sandbox->defineFunc('system', function(string $command, int &$result_code = null) {
    return (SANDBOX_CONFIG['permissions']['bin_exec'] ? system($command, $result_code) : false);
});

$sandbox->defineFunc('passthru', function(string $command, int &$result_code = null) {
    return (SANDBOX_CONFIG['permissions']['bin_exec'] ? passthru($command, $result_code) : false);
});

$sandbox->defineFunc('shell_exec', function(string $command) {
    return (SANDBOX_CONFIG['permissions']['bin_exec'] ? shell_exec($command) : false);
});

$sandbox->defineFunc('popen', function(string $command, string $mode) {
    return (SANDBOX_CONFIG['permissions']['bin_exec'] ? popen($command, $mode) : false);
});

$sandbox->defineFunc('proc_open', function(array|string $command, array $descriptor_spec, array &$pipes, ?string $cwd = null, ?array $env_vars = null,?array $options = null) {
    return (SANDBOX_CONFIG['permissions']['bin_exec'] ? proc_open($command, $descriptor_spec, $pipes, $cwd, $env_vars, $options) : false);
});

// Reimplement safe includes by finding and injecting to be included files into the sandbox
function include_files($code) {
    if(SANDBOX_CONFIG['permissions']['include']) {
        $function_calls = explode(';', $code);
        $includes = preg_grep("~(include|include_once|require|require_once)~", $function_calls);
        foreach($includes AS $include) {
            $filename = preg_replace("~(\"|'|\))~", '', trim(preg_split("~( |\()~", $include)[2]));
            if(storage_access_granted($filename) AND !isNetworkLocation($filename)) $function_calls[array_search($include, $function_calls)] = str_replace('?>', '', str_replace('<?php', '', file_get_contents(storage_get_realpath($filename))));
        }
        return implode(';', $function_calls);
    }
    return false;
}

// Add empty functions to prevent errors inside the sandbox and make normal includes (which wouldn't respect storage restrictions) provided by the sandboxing library useless
$sandbox->defineFunc('include', function() {});
$sandbox->defineFunc('include_once', function() {});
$sandbox->defineFunc('require', function() {});
$sandbox->defineFunc('require_once', function() {});


// redefine zend_version function
$sandbox->defineFunc('zend_version', function() {
    return 'SandboxedPHP based on zend: '.zend_version();
});


$sandbox->execute(include_files('<?php require "test.php";  var_dump(pdo_drivers()); phpinfo(); ?>'));

ob_end_flush();

var_dump(memory_get_peak_usage() / 1000000);
?>