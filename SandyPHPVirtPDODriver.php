<?php

// This file contains the pdo driver proxies (SandyPHPVirtPDODriver)

define('SANDBOX_PDO_DRIVERS', "PDO");

final class SandyPHPVirtPDODriver extends PDO {

    protected string $connectionType;

    // Trying to create a PDO object within the sandbox will result in this constructor being called instead. Validate that this is connection is allowed...
    function __construct(string $dsn, ?string $username = null, #[\SensitiveParameter] ?string $password = null, ?array $options = null) {
        $dsn_arr = explode(';', substr($dsn, strpos($dsn, ':') + 1));
        $this->connectionType = substr($dsn, 0, strpos($dsn, ':'));
        foreach($dsn_arr AS $detail) {
            $kvpair = explode('=', $detail);
            if($kvpair[0] == 'host' AND !in_array($kvpair, SANDBOX_CONFIG['database'][$this->connectionType]['hosts'])) return false;
            if($kvpair[0] == 'dbname' AND !in_array($kvpair[1], SANDBOX_CONFIG['database'][$this->connectionType]['database_names'])) return false;
        }
        parent::__construct($dsn, $username, $password, $options);
    }

    protected function checkQuery($sql) {
        $parser = new PhpMyAdmin\SqlParser\Parser($sql);
        
        // Loop over the statements and check them
        foreach($parser->statements AS $statement) {
            switch(get_class($statement)) {
                case "PhpMyAdmin\SqlParser\Statements\SelectStatement":
                    foreach($statement->from AS $exp) {
                        if(($exp->database !== null AND !in_array($exp->database, SANDBOX_CONFIG['database'][$this->connectionType]['database_names'])) OR ($exp->table !== null AND !in_array($exp->table, SANDBOX_CONFIG['database'][$this->connectionType]['tables']))) return false;
                    }
                    break;
            }
        }

        return true;
    }

    #[\Override]
    public function query(string $sql, ?int $fetchMode = null, mixed ...$fetchModeArgs) {
        return ($this->checkQuery($sql) ? parent::query($sql, $fetchMode, $fetchModeArgs) : false);
    }

}

?>