<?php 

// This is a mock class in case the mysqli extension doesn't exist, this file will be loaded throwing errors. This prevents a fatal error because the mysqli class could not be found.

final class MySQLIErrorDummy {

    public function __construct(?string $hostname = null, ?string $username = null, #[\SensitiveParameter] ?string $password = null, ?string $database = null, ?int $port = null, ?string $socket = null) {  
            die('Fatal error: SandyPHP could not find the mysqli extension. Please make sure it\'s installed.'."\n");
    }

}
/*
handleException(new MYSQLINotFoundException());*/

?>