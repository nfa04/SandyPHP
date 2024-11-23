/* 

Please note: 
This class is not used as is here. Instead the keywords SANDBOXID and SANDBOXCONFIG are replaced with the their respective value in order to pass the information about configuration to this class. Therefore sandboxes will spawn objects of the class according to this scheme: "SandyPHPVirtPDODriver_SANDBOX_SANDBOXID", where SANDBOXID is replaced with the respective id of the running sandbox.

*/

// This file contains the pdo driver proxies (SandyPHPVirtPDODriver)

// !!! WARNING !!!: Query verification should only be used for the mysql dialect. Other SQL dialects will be interpreted correctly in most cases, but are still NOT considered anywhere near secure!

final class SandyPHPVirtPDODriver_SANDBOX_SANDBOXID extends PDO {

    private string $connectionType;
    private $config;
    private $loggerID;

    // Trying to create a PDO object within the sandbox will result in this constructor being called instead. Validate that this is connection is allowed...
    function __construct(string $dsn, ?string $username = null, #[\SensitiveParameter] ?string $password = null, ?array $options = null) {
        $this->config = json_decode('SANDBOXCONFIG', true);
        $this->loggerID = 'SANDBOXLOGGERID';
        $dsn_arr = explode(';', substr($dsn, strpos($dsn, ':') + 1));
        $this->connectionType = substr($dsn, 0, strpos($dsn, ':'));
        foreach($dsn_arr AS $detail) {
            $kvpair = explode('=', $detail);
            if($kvpair[0] == 'host' AND !in_array($kvpair, $this->config[$this->connectionType]['hosts'])) return false;
            if($kvpair[0] == 'dbname' AND !in_array($kvpair[1], $this->config[$this->connectionType]['database_names'])) return false;
        }
        parent::__construct($dsn, $username, $password, $options);
    }

    #[\Override]
    #[\ReturnTypeWillChange]
    public function query(string $sql, ?int $fetchMode = null, mixed ...$fetchModeArgs) {
        return (SandyPHP\Utils\Queries\checkQuery($sql, $this->connectionType, $this->config, $this->loggerID) ? parent::query($sql, $fetchMode, $fetchModeArgs) : false);
    }

    #[\Override]
    #[\ReturnTypeWillChange]
    public function exec(string $statement) {
        return (SandyPHP\Utils\Queries\checkQuery($statement, $this->connectionType, $this->config, $this->loggerID) ? parent::exec($statement) : false);
    }

    #[\Override]
    #[\ReturnTypeWillChange]
    public function prepare(string $query, array $options = []) {
        return (SandyPHP\Utils\Queries\checkQuery($query, $this->connectionType, $this->config, $this->loggerID) ? parent::prepare($query, $options) : false);
    }

}

