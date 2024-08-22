<?php

// This file contains the pdo driver proxies (SandyPHPVirtPDODriver)

// !!! WARNING !!!: Query verification should only be used for the mysql dialect. Other SQL dialects will be interpreted correctly in most cases, but are still NOT considered anywhere near secure!

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

    #[\Override]
    public function query(string $sql, ?int $fetchMode = null, mixed ...$fetchModeArgs) {
        return (checkQuery($sql, $this->connectionType) ? parent::query($sql, $fetchMode, $fetchModeArgs) : false);
    }

    #[\Override]
    public function exec(string $statement) {
        return (checkQuery($statement, $this->connectionType) ? parent::exec($statement) : false);
    }

    #[\Override]
    public function prepare(string $query, array $options = []) {
        return (checkQuery($query, $this->connectionType) ? parent::prepare($query, $options) : false);
    }

}

?>