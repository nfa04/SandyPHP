/* 

Please note: 
This class is not used as is here. Instead the keywords SANDBOXID and SANDBOXCONFIG are replaced with the their respective value in order to pass the information about configuration to this class. Therefore sandboxes will spawn objects of the class according to this scheme: "SandyPHPVirtMYSQLIDriver_SANDBOX_SANDBOXID", where SANDBOXID is replaced with the respective id of the running sandbox.

*/

final class SandyPHPVirtMYSQLIDriver_SANDBOX_SANDBOXID extends mysqli {

    private $config;

    public function __construct(?string $hostname = null, ?string $username = null, #[\SensitiveParameter] ?string $password = null, ?string $database = null, ?int $port = null, ?string $socket = null) {
        $this->config = json_decode('SANDBOXCONFIG', true);
        if($this->connectionAllowed($hostname, $database)) return parent::__construct($hostname, $username, $password, $database, $port, $socket);
        return false;
    }

    protected function connectionAllowed($hostname, $database) {
        if(!in_array($hostname, $this->config['mysql']['hosts']) || !in_array($database, $this->config['mysql']['database_names'])) return false;
        return true;
    }

    #[\Override]
    public function change_user(string $username, #[\SensitiveParameter] string $password, ?string $database) {
        if(in_array($database, $this->config['mysql']['database_names'])) return parent::change_user($username, $password, $database);
        return false;
    }

    #[\Override]
    public function connect(?string $hostname = null, ?string $username = null, #[\SensitiveParameter] ?string $password = null, ?string $database = null, ?int $port = null, ?string $socket = null) {
        if($this->connectionAllowed($hostname, $database)) return parent::connect($hostname, $username, $password, $database, $port, $socket);
        return false;
    }

    #[\Override]
    public function execute_query(string $query, ?array $params = null) {
        if(checkQuery($query, 'mysql', $this->config)) parent::execute_query($query, $params);
        return false;
    }

    #[\Override]
    public function multi_query(string $query) {
        if(checkQuery($query, 'mysql', $this->config)) parent::multi_query($query);
        return false;
    }

    #[\Override]
    public function prepare(string $query) {
        if(checkQuery($query, 'mysql', $this->config)) parent::prepare($query);
        return false;
    }
    
    #[\Override]
    public function query(string $query, int $result_mode = MYSQLI_STORE_RESULT) {
        if(checkQuery($query, 'mysql', $this->config)) parent::query($query, $result_mode);
        return false;
    }

    #[\Override]
    public function real_connect(?string $hostname = null, ?string $username = null, #[\SensitiveParameter] ?string $password = null, ?string $database = null, ?int $port = null, ?string $socket = null, int $flags = 0) {
        if($this->connectionAllowed($hostname, $database)) return parent::real_connect($hostname, $username, $password, $database, $port, $socket, $flags);
        return false;
    }

    #[\Override]
    public function real_query(string $query)  {
        if(checkQuery($query, 'mysql', $this->config)) parent::real_query($query);
        return false;
    }

    #[\Override]
    public function select_db(string $database) {
        if(in_array($database, $this->config['mysql']['database_names'])) return parent::select_db($database);
        return false;
    }
}

