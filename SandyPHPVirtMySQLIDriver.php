<?

final class SandyPHPVirtMYSQLIDriver extends mysqli {

    public function __construct(?string $hostname = null, ?string $username = null, #[\SensitiveParameter] ?string $password = null, ?string $database = null, ?int $port = null, ?string $socket = null) {
        if($this->connectionAllowed($hostname, $database)) return parent::__construct($hostname, $username, $password, $database, $port, $socket);
        return false;
    }

    protected function connectionAllowed($hostname, $database) {
        if(!in_array($hostname, SANDBOX_CONFIG['database']['mysql']['hosts']) || !in_array($database, SANDBOX_CONFIG['database']['mysql']['database_names'])) return false;
        return true;
    }

    #[\Override]
    public function change_user(string $username, #[\SensitiveParameter] string $password, ?string $database) {
        if(in_array($database, SANDBOX_CONFIG['database']['mysql']['database_names'])) return parent::change_user($username, $password, $database);
        return false;
    }

    #[\Override]
    public function connect(?string $hostname = null, ?string $username = null, #[\SensitiveParameter] ?string $password = null, ?string $database = null, ?int $port = null, ?string $socket = null) {
        if($this->connectionAllowed($hostname, $database)) return parent::connect($hostname, $username, $password, $database, $port, $socket);
        return false;
    }

    #[\Override]
    public function execute_query(string $query, ?array $params = null) {
        if(checkQuery($query, 'mysql')) parent::execute_query($query, $params);
        return false;
    }

    #[\Override]
    public function multi_query(string $query) {
        if(checkQuery($query, 'mysql')) parent::multi_query($query);
        return false;
    }

    #[\Override]
    public function prepare(string $query) {
        if(checkQuery($query, 'mysql')) parent::prepare($query);
        return false;
    }
    
    #[\Override]
    public function query(string $query, int $result_mode = MYSQLI_STORE_RESULT) {
        if(checkQuery($query, 'mysql')) parent::query($query, $result_mode);
        return false;
    }

    #[\Override]
    public function real_connect(?string $hostname = null, ?string $username = null, #[\SensitiveParameter] ?string $password = null, ?string $database = null, ?int $port = null, ?string $socket = null, int $flags = 0) {
        if($this->connectionAllowed($hostname, $database)) return parent::real_connect($hostname, $username, $password, $database, $port, $socket, $flags);
        return false;
    }

    #[\Override]
    public function real_query(string $query)  {
        if(checkQuery($query)) parent::real_query($query);
        return false;
    }

    #[\Override]
    public function select_db(string $database) {
        if(in_array($database, SANDBOX_CONFIG['database']['mysql']['database_names'])) return parent::select_db;
        return false;
    }
}


?>