<?php

// This file contains the pdo driver proxies (SandyPHPVirtPDODriver)

define('SANDBOX_PDO_DRIVERS', "PDO");

$parser = new PHPSQLParser\PHPSQLParser();

class SandyPHPVirtPDODriver extends PDO {
    // Trying to create a PDO object within the sandbox will result in this constructor being called instead. Validate that this is connection is allowed...
    function __construct(string $dsn, ?string $username = null, #[\SensitiveParameter] ?string $password = null, ?array $options = null) {
        $dsn_arr = explode(';', substr($dsn, strpos($dsn, ':') + 1));
        $connectionType = substr($dsn, 0, strpos($dsn, ':'));
        foreach($dsn_arr AS $detail) {
            $kvpair = explode('=', $detail);
            var_dump($kvpair);
            if($kvpair[0] == 'host' AND !in_array($kvpair, SANDBOX_CONFIG['database'][$connectionType]['hosts'])) return false;
            if($kvpair[0] == 'dbname' AND !in_array($kvpair[1], SANDBOX_CONFIG['database'][$connectionType]['database_names'])) return false;
        }
        parent::__construct($dsn, $username, $password, $options);
    }
}

?>