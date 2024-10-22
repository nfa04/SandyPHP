<?php 

namespace SandyPHP\Utils\Queries;

use PhpMyAdmin;

function is_query(string $query) {
    // WARNING: THIS FUNCTION DOES NOT VALIDATE THE QUERY
    return preg_match("~((ACCESSIBLE|ACCOUNT|ACTION|ACTIVE|ADD|ADMIN|AFTER|AGAINST|AGGREGATE|ALGORITHM|ALL|ALTER|ALWAYS|ANALYZE|AND|ANY|ARRAY|AS|ASC|ASCII|ASENSITIVE|AT|ATTRIBUTE|AUTHENTICATION|AUTO|AUTOEXTEND_SIZE|AUTO_INCREMENT|AVG|AVG_ROW_LENGTH|BACKUP|BEFORE|BEGIN|BERNOULLI|BETWEEN|BIGINT|BINARY|BINLOG|BIT|BLOB|BLOCK|BOOL|BOOLEAN|BOTH|BTREE|BUCKETS|BULK|BY|BYTE|CACHE|CALL|CASCADE|CASCADED|CASE|CATALOG_NAME|CHAIN|CHALLENGE_RESPONSE|CHANGE|CHANGED|CHANNEL|CHAR|CHARACTER|CHARSET|CHECK|CHECKSUM|CIPHER|CLASS_ORIGIN|CLIENT|CLONE|CLOSE|COALESCE|CODE|COLLATE|COLLATION|COLUMN|COLUMNS|COLUMN_FORMAT|COLUMN_NAME|COMMENT|COMMIT|COMMITTED|COMPACT|COMPLETION|COMPONENT|COMPRESSED|COMPRESSION|CONCURRENT|CONDITION|CONNECTION|CONSISTENT|CONSTRAINT|CONSTRAINT_CATALOG|CONSTRAINT_NAME|CONSTRAINT_SCHEMA|CONTAINS|CONTEXT|CONTINUE|CONVERT|CPU|CREATE|CROSS|CUBE|CUME_DIST|CURRENT|CURRENT_DATE|CURRENT_TIME|CURRENT_TIMESTAMP|CURRENT_USER|CURSOR|CURSOR_NAME|DATA|DATABASE|DATABASES|DATAFILE|DATE|DATETIME|DAY|DAY_HOUR|DAY_MICROSECOND|DAY_MINUTE|DAY_SECOND|DEALLOCATE|DEC|DECIMAL|DECLARE|DEFAULT|DEFAULT_AUTH|DEFINER|DEFINITION|DELAYED|DELAY_KEY_WRITE|DELETE|DENSE_RANK|DESC|DESCRIBE|DESCRIPTION|DETERMINISTIC|DIAGNOSTICS|DIRECTORY|DISABLE|DISCARD|DISK|DISTINCT|DISTINCTROW|DIV|DO|DOUBLE|DROP|DUAL|DUMPFILE|DUPLICATE|DYNAMIC|EACH|ELSE|ELSEIF|EMPTY|ENABLE|ENCLOSED|ENCRYPTION|END|ENDS|ENFORCED|ENGINE|ENGINES|ENGINE_ATTRIBUTE|ENUM|ERROR|ERRORS|ESCAPE|ESCAPED|EVENT|EVENTS|EVERY|EXCEPT|EXCHANGE|EXCLUDE|EXECUTE|EXISTS|EXIT|EXPANSION|EXPIRE|EXPLAIN|EXPORT|EXTENDED|EXTENT_SIZE|FACTOR|FAILED_LOGIN_ATTEMPTS|FALSE|FAST|FAULTS|FETCH|FIELDS|FILE|FILE_BLOCK_SIZE|FILTER|FINISH|FIRST|FIRST_VALUE|FIXED|FLOAT|FLOAT4|FLOAT8|FLUSH|FOLLOWING|FOLLOWS|FOR|FORCE|FOREIGN|FORMAT|FOUND|FROM|FULL|FULLTEXT|FUNCTION|GENERAL|GENERATE|GENERATED|GEOMCOLLECTION|GEOMETRY|GEOMETRYCOLLECTION|GET|GET_FORMAT|GET_SOURCE_PUBLIC_KEY|GLOBAL|GRANT|GRANTS|GROUP|GROUPING|GROUPS|GROUP_REPLICATION|GTIDS|GTID_ONLY|HANDLER|HASH|HAVING|HELP|HIGH_PRIORITY|HISTOGRAM|HISTORY|HOST|HOSTS|HOUR|HOUR_MICROSECOND|HOUR_MINUTE|HOUR_SECOND|IDENTIFIED|IF|IGNORE|IGNORE_SERVER_IDS|IMPORT|IN|INACTIVE|INDEX|INDEXES|INFILE|INITIAL|INITIAL_SIZE|INITIATE|INNER|INOUT|INSENSITIVE|INSERT|INSERT_METHOD|INSTALL|INSTANCE|INT|INT1|INT2|INT3|INT4|INT8|INTEGER|INTERSECT|INTERVAL|INTO|INVISIBLE|INVOKER|IO|IO_AFTER_GTIDS|IO_BEFORE_GTIDS|IO_THREAD|IPC|IS|ISOLATION|ISSUER|ITERATE|JOIN|JSON|JSON_TABLE|JSON_VALUE|KEY|KEYRING|KEYS|KEY_BLOCK_SIZE|KILL|LAG|LANGUAGE|LAST|LAST_VALUE|LATERAL|LEAD|LEADING|LEAVE|LEAVES|LEFT|LESS|LEVEL|LIKE|LIMIT|LINEAR|LINES|LINESTRING|LIST|LOAD|LOCAL|LOCALTIME|LOCALTIMESTAMP|LOCK|LOCKED|LOCKS|LOG|LOGFILE|LOGS|LONG|LONGBLOB|LONGTEXT|LOOP|LOW_PRIORITY|MANUAL|MASTER|MATCH|MAXVALUE|MAX_CONNECTIONS_PER_HOUR|MAX_QUERIES_PER_HOUR|MAX_ROWS|MAX_SIZE|MAX_UPDATES_PER_HOUR|MAX_USER_CONNECTIONS|MEDIUM|MEDIUMBLOB|MEDIUMINT|MEDIUMTEXT|MEMBER|MEMORY|MERGE|MESSAGE_TEXT|MICROSECOND|MIDDLEINT|MIGRATE|MINUTE|MINUTE_MICROSECOND|MINUTE_SECOND|MIN_ROWS|MOD|MODE|MODIFIES|MODIFY|MONTH|MULTILINESTRING|MULTIPOINT|MULTIPOLYGON|MUTEX|MYSQL_ERRNO|NAME|NAMES|NATIONAL|NATURAL|NCHAR|NDB|NDBCLUSTER|NESTED|NETWORK_NAMESPACE|NEVER|NEW|NEXT|NO|NODEGROUP|NONE|NOT|NOWAIT|NO_WAIT|NO_WRITE_TO_BINLOG|NTH_VALUE|NTILE|NULL|NULLS|NUMBER|NUMERIC|NVARCHAR|OF|OFF|OFFSET|OJ|OLD|ON|ONE|ONLY|OPEN|OPTIMIZE|OPTIMIZER_COSTS|OPTION|OPTIONAL|OPTIONALLY|OPTIONS|OR|ORDER|ORDINALITY|ORGANIZATION|OTHERS|OUT|OUTER|OUTFILE|OVER|OWNER|PACK_KEYS|PAGE|PARALLEL|PARSER|PARSE_TREE|PARTIAL|PARTITION|PARTITIONING|PARTITIONS|PASSWORD|PASSWORD_LOCK_TIME|PATH|PERCENT_RANK|PERSIST|PERSIST_ONLY|PHASE|PLUGIN|PLUGINS|PLUGIN_DIR|POINT|POLYGON|PORT|PRECEDES|PRECEDING|PRECISION|PREPARE|PRESERVE|PREV|PRIMARY|PRIVILEGES|PRIVILEGE_CHECKS_USER|PROCEDURE|PROCESS|PROCESSLIST|PROFILE|PROFILES|PROXY|PURGE|QUALIFY|QUARTER|QUERY|QUICK|RANDOM|RANGE|RANK|READ|READS|READ_ONLY|READ_WRITE|REAL|REBUILD|RECOVER|RECURSIVE|REDO_BUFFER_SIZE|REDUNDANT|REFERENCE|REFERENCES|REGEXP|REGISTRATION|RELAY|RELAYLOG|RELAY_LOG_FILE|RELAY_LOG_POS|RELAY_THREAD|RELEASE|RELOAD|REMOVE|RENAME|REORGANIZE|REPAIR|REPEAT|REPEATABLE|REPLACE|REPLICA|REPLICAS|REPLICATE_DO_DB|REPLICATE_DO_TABLE|REPLICATE_IGNORE_DB|REPLICATE_IGNORE_TABLE|REPLICATE_REWRITE_DB|REPLICATE_WILD_DO_TABLE|REPLICATE_WILD_IGNORE_TABLE|REPLICATION|REQUIRE|REQUIRE_ROW_FORMAT|RESET|RESIGNAL|RESOURCE|RESPECT|RESTART|RESTORE|RESTRICT|RESUME|RETAIN|RETURN|RETURNED_SQLSTATE|RETURNING|RETURNS|REUSE|REVERSE|REVOKE|RIGHT|RLIKE|ROLE|ROLLBACK|ROLLUP|ROTATE|ROUTINE|ROW|ROWS|ROW_COUNT|ROW_FORMAT|ROW_NUMBER|RTREE|S3|SAVEPOINT|SCHEDULE|SCHEMA|SCHEMAS|SCHEMA_NAME|SECOND|SECONDARY|SECONDARY_ENGINE|SECONDARY_ENGINE_ATTRIBUTE|SECONDARY_LOAD|SECONDARY_UNLOAD|SECOND_MICROSECOND|SECURITY|SELECT|SENSITIVE|SEPARATOR|SERIAL|SERIALIZABLE|SERVER|SESSION|SET|SHARE|SHOW|SHUTDOWN|SIGNAL|SIGNED|SIMPLE|SKIP|SLAVE|SLOW|SMALLINT|SNAPSHOT|SOCKET|SOME|SONAME|SOUNDS|SOURCE|SOURCE_AUTO_POSITION|SOURCE_BIND|SOURCE_COMPRESSION_ALGORITHMS|SOURCE_CONNECT_RETRY|SOURCE_DELAY|SOURCE_HEARTBEAT_PERIOD|SOURCE_HOST|SOURCE_LOG_FILE|SOURCE_LOG_POS|SOURCE_PASSWORD|SOURCE_PORT|SOURCE_PUBLIC_KEY_PATH|SOURCE_RETRY_COUNT|SOURCE_SSL|SOURCE_SSL_CA|SOURCE_SSL_CAPATH|SOURCE_SSL_CERT|SOURCE_SSL_CIPHER|SOURCE_SSL_CRL|SOURCE_SSL_CRLPATH|SOURCE_SSL_KEY|SOURCE_SSL_VERIFY_SERVER_CERT|SOURCE_TLS_CIPHERSUITES|SOURCE_TLS_VERSION|SOURCE_USER|SOURCE_ZSTD_COMPRESSION_LEVEL|SPATIAL|SPECIFIC|SQL|SQLEXCEPTION|SQLSTATE|SQLWARNING|SQL_AFTER_GTIDS|SQL_AFTER_MTS_GAPS|SQL_BEFORE_GTIDS|SQL_BIG_RESULT|SQL_BUFFER_RESULT|SQL_CALC_FOUND_ROWS|SQL_NO_CACHE|SQL_SMALL_RESULT|SQL_THREAD|SQL_TSI_DAY|SQL_TSI_HOUR|SQL_TSI_MINUTE|SQL_TSI_MONTH|SQL_TSI_QUARTER|SQL_TSI_SECOND|SQL_TSI_WEEK|SQL_TSI_YEAR|SRID|SSL|STACKED|START|STARTING|STARTS|STATS_AUTO_RECALC|STATS_PERSISTENT|STATS_SAMPLE_PAGES|STATUS|STOP|STORAGE|STORED|STRAIGHT_JOIN|STREAM|STRING|SUBCLASS_ORIGIN|SUBJECT|SUBPARTITION|SUBPARTITIONS|SUPER|SUSPEND|SWAPS|SWITCHES|SYSTEM|TABLE|TABLES|TABLESAMPLE|TABLESPACE|TABLE_CHECKSUM|TABLE_NAME|TEMPORARY|TEMPTABLE|TERMINATED|TEXT|THAN|THEN|THREAD_PRIORITY|TIES|TIME|TIMESTAMP|TIMESTAMPADD|TIMESTAMPDIFF|TINYBLOB|TINYINT|TINYTEXT|TLS|TO|TRAILING|TRANSACTION|TRIGGER|TRIGGERS|TRUE|TRUNCATE|TYPE|TYPES|UNBOUNDED|UNCOMMITTED|UNDEFINED|UNDO|UNDOFILE|UNDO_BUFFER_SIZE|UNICODE|UNINSTALL|UNION|UNIQUE|UNKNOWN|UNLOCK|UNREGISTER|UNSIGNED|UNTIL|UPDATE|UPGRADE|URL|USAGE|USE|USER|USER_RESOURCES|USE_FRM|USING|UTC_DATE|UTC_TIME|UTC_TIMESTAMP|VALIDATION|VALUE|VALUES|VARBINARY|VARCHAR|VARCHARACTER|VARIABLES|VARYING|VCPU|VIEW|VIRTUAL|VISIBLE|WAIT|WARNINGS|WEEK|WEIGHT_STRING|WHEN|WHERE|WHILE|WINDOW|WITH|WITHOUT|WORK|WRAPPER|WRITE|X509|XA|XID|XML|XOR|YEAR|YEAR_MONTH|ZEROFILL|ZONE) .*)+~", $query);
}

function throwDatabaseException(\Logger $logger) {
    $exception = new \DatabaseAccessPolicyViolation();
    $logger->log($exception);
    throw $exception;
}

function throwDatabaseActionException(\Logger $logger, string $action) {
    $exception = new \DatabaseActionPolicyViolation($action);
    $logger->log($exception);
    throw $exception;
}

function checkQuery(string $sql, string $connectionType, array $ruleset, string $loggerID) {
        global $SandyPHP_LoggerManager;
        $logger = $SandyPHP_LoggerManager->getLoggerByID($loggerID);
        $parser = new PhpMyAdmin\SqlParser\Parser($sql);
        $rules = $ruleset[$connectionType];
        // Loop over the statements and check them
        foreach($parser->statements AS $statement) {
            switch(get_class($statement)) {
                case "PhpMyAdmin\SqlParser\Statements\AlterStatement":
                    if(!in_array('ALTER', $rules['actions'])) {
                        throwDatabaseActionException($logger, 'ALTER');
                        return false;
                    }
                    if((!in_array($statement->table->database, $rules['database_names']) AND $statement->table->database !== null) OR (!in_array($statement->table->table, $rules['tables']) AND $statement->table->table !== null)) {
                        throwDatabaseException($logger);
                        return false;
                    }
                    foreach($statement->altered AS $altered) {
                        if(($altered->field->database !== null AND !in_array($altered->field->database, $rules['database_names']) OR $altered->field->database !== null)) {
                        throwDatabaseException($logger);
                        return false;
                    }
                    }
                    break;
                case "PhpMyAdmin\SqlParser\Statements\SelectStatement":
                    if(!in_array('SELECT', $rules['actions'])) {
                        throwDatabaseActionException($logger, 'SELECT');
                        return false;
                    }
                    foreach($statement->from AS $fromExpression) {
                        if($fromExpression->database !== null AND !in_array($fromExpression->database, $rules['database_names'])) {
                        throwDatabaseException($logger);
                        return false;
                    }
                        if($fromExpression->table !== null AND !in_array($fromExpression->table, $rules['tables'])) {
                        throwDatabaseException($logger);
                        return false;
                    }
                    }
                    foreach($statement->where AS $condition) {
                        // "WHERE" may contain a subquery, detect and check it too...
                        $needle = trim(str_replace(array('(', ')'), '', substr($condition->expr, strpos($condition->expr, '=') + 1)));
                        if(is_query($needle) AND !checkQuery($needle, $connectionType, $ruleset)) {
                        throwDatabaseException($logger);
                        return false;
                    }
                    }
                    break;
                case "PhpMyAdmin\SqlParser\Statements\BackupStatement":
                    if(!in_array('BACKUP', $rules['actions'])) {
                        throwDatabaseActionException($logger, 'BACKUP');
                        return false;
                    }
                    foreach($statement->tables AS $table) {
                        if(!in_array($table, $rules['tables'])) {
                        throwDatabaseException($logger);
                        return false;
                    }
                    }
                    break;
                case "PhpMyAdmin\SqlParser\Statements\CallStatement":
                    if(!in_array('CALL', $rules['actions'])) {
                        throwDatabaseActionException($logger, 'CALL');
                        return false;
                    }
                    break;
                case "PhpMyAdmin\SqlParser\Statements\ChecksumStatement":
                    if(!in_array('CHECKSUM', $rules['actions'])) {
                        throwDatabaseActionException($logger, 'CHECKSUM');
                        return false;
                    }
                    break;
                case "PhpMyAdmin\SqlParser\Statements\CreateStatement":
                    if(!in_array('CREATE', $rules['actions'])) {
                        throwDatabaseActionException($logger, 'CREATE');
                        return false;
                    }
                    if(!in_array($statement->name->table, $rules['tables']) OR !in_array($statement->name->database, $rules['database_names'])) {
                        throwDatabaseException($logger);
                        return false;
                    }
                    break;
                case "PhpMyAdmin\SqlParser\Statements\DeleteStatement":
                    if(!in_array('DELETE', $rules['actions'])) {
                        throwDatabaseActionException($logger, 'DELETE');
                        return false;
                    }
                    foreach($statement->from AS $from) {
                        if(($from->database !== null AND !in_array($from->database, $rules['database_names'])) OR ($from->table !== null AND !in_array($from->table, $rules['tables']))) {
                        throwDatabaseException($logger);
                        return false;
                    }
                    foreach($statement->where AS $where) {
                        if(is_query($where->expr) AND !checkQuery($where->expr, $connectionType, $ruleset, $loggerID)) return false; // Exceptions will be thrown within the called recursive checkQuery function
                    }
                }
                    break;
                case "PhpMyAdmin\SqlParser\Statements\DropStatement":
                    if(!in_array('DROP', $rules['actions'])) {
                        throwDatabaseActionException($logger, 'DROP');
                        return false;
                    }
                    foreach($statement->fields AS $field) {
                        if(($field->database !== null OR !in_array($field->database, $rules['database_names'])) AND ($field->table !== null OR !in_array($field->table, $rules['tables']))) {
                        throwDatabaseException($logger);
                        return false;
                    }
                    }
                    break;
                case "PhpMyAdmin\SqlParser\Statements\InsertStatement":
                    if(!in_array('INSERT', $rules['actions'])) {
                        throwDatabaseActionException($logger, 'INSERT');
                        return false;
                    }
                    if(($statement->into->dest->database !== null OR !in_array($statement->into->dest->database, $rules['database_names'])) AND ($statement->into->dest->table !== null OR !in_array($statement->into->dest->table, $rules['tables']))) {
                        throwDatabaseException($logger);
                        return false;
                    }
                    break;
                case "PhpMyAdmin\SqlParser\Statements\KillStatement":
                    if(!in_array('KILL', $rules['actions'])) {
                        throwDatabaseActionException($logger, 'KILL');
                        return false;
                    }
                    break;
                case "PhpMyAdmin\SqlParser\Statements\LoadStatement":
                    if(!in_array('LOAD', $rules['actions'])) {
                        throwDatabaseActionException($logger, 'LOAD');
                        return false;
                    }
                    if(in_array('local', array_map('strtolower', $statement->options->options))) {
                        throwDatabaseException($logger);
                        return false;
                    }
                    break;
                case "PhpMyAdmin\SqlParser\Statements\LockStatement":
                    // No table checks apply here
                    if(!in_array('LOCK', $rules['actions'])) {
                        throwDatabaseActionException($logger, 'LOCK');
                        return false;
                    }
                    break;
                case "PhpMyAdmin\SqlParser\Statements\MaintenanceStatement":
                    if(!in_array('MAINTENANCE', $rules['actions'])) {
                        throwDatabaseActionException($logger, 'Maintenance Statement');
                        return false;
                    }
                    break;
                case "PhpMyAdmin\SqlParser\Statements\OptimizeStatement":
                    if(!in_array('OPTIMIZE', $rules['actions'])) {
                        throwDatabaseActionException($logger, 'OPTIMIZE');
                        return false;
                    }
                    foreach($statement->tables AS $table) {
                        if(($table->table !== null AND !in_array($table->table, $rules['tables'])) OR ($table->database !== null AND !in_array($table->database, $rules['database_names']))) {
                            throwDatabaseException($logger);
                            return false;
                        }
                    }
                    break;
                case "PhpMyAdmin\SqlParser\Statements\PurgeStatement":
                    if(!in_array('PURGE', $rules['actions'])) {
                        throwDatabaseActionException($logger, 'PURGE');
                        return false;
                    }
                    break;
                case "PhpMyAdmin\SqlParser\Statements\RenameStatement":
                    if(!in_array('RENAME', $rules['actions'])) {
                        throwDatabaseActionException($logger, 'RENAME');
                        return false;
                    }
                    var_dump($statement);
                    foreach($statement->renames AS $rename) {
                        if(($rename->new->database !== null AND !in_array($rename->new->database, $rules['database_names'])) OR ($rename->new->table !== null AND !in_array($rename->new->table, $rules['tables']))) {
                            throwDatabaseException($logger);
                            return false;
                        }
                    }
                    break;
                case "PhpMyAdmin\SqlParser\Statements\RepairStatement":
                    if(!in_array('REPAIR', $rules['actions'])) {
                        throwDatabaseActionException($logger, 'REPAIR');
                        return false;
                    }
                    foreach($statement->tables AS $table) {
                        if(($table->database !== null AND !in_array($table->database, $rules['database_names'])) OR ($table->table !== null AND !in_array($table->table, $rules['tables']))) {
                            throwDatabaseException($logger);
                            return false;
                        }
                    }
                    break;
                case "PhpMyAdmin\SqlParser\Statements\ReplaceStatement":
                    if(!in_array('REPLACE', $rules['actions'])) {
                        throwDatabaseActionException($logger, 'REPLACE');
                        return false;
                    }
                    if(($statement->into->dest->database !== null AND !in_array($statement->into->dest->database, $rules['database_names'])) OR ($statement->into->dest->table !== null AND !in_array($statement->into->dest->table, $rules['tables']))) {
                        throwDatabaseException($logger);
                        return false;
                    }
                    break;
                    case "PhpMyAdmin\SqlParser\Statements\RestoreStatement":
                        // Not available
                        throwDatabaseException($logger);
                        return false;
                        break;
                    case "PhpMyAdmin\SqlParser\Statements\SetStatement":
                        if(!in_array('SET', $rules['actions'])) {
                            throwDatabaseActionException($logger, 'SET');
                            return false;
                        }
                        if(!in_array('SET DEFAULT ROLE', $rules['actions']) AND in_array('default', array_map('strtolower', $statement->end_options->options))) {
                            throwDatabaseException($logger);
                            return false;
                        }
                        break;
                    case "PhpMyAdmin\SqlParser\Statements\ShowStatement":
                        if(!in_array('SHOW', $rules['actions'])) {
                            throwDatabaseActionException($logger, 'SHOW');
                            return false;
                        }
                        // Can only be allowed/not allowed, no further configuration possible
                        break;
                    case "PhpMyAdmin\SqlParser\Statements\TransactionStatement":
                        // No reason to block this behaviour, case only exists for this comment
                        break;
                    case "PhpMyAdmin\SqlParser\Statements\TruncateStatement":
                        if(!in_array('TRUNCATE', $rules['actions'])) {
                            throwDatabaseActionException($logger, 'TRUNCATE');
                            return false;
                        }
                        if(($statement->table->database !== null AND !in_array($statement->table->database, $rules['database_names'])) OR ($statement->table->table !== null AND !in_array($statement->table->table, $rules['tables']))) {
                            throwDatabaseException($logger);
                            return false;
                        }
                        break;
                    case "PhpMyAdmin\SqlParser\Statements\UpdateStatement":
                        if(!in_array('UPDATE', $rules['actions'])) {
                            throwDatabaseActionException($logger, 'UPDATE');
                            return false;
                        }
                        foreach($statement->tables AS $table) {
                            if(($table->database !== null AND !in_array($table->database, $rules['database_names'])) OR ($table->table !== null AND !in_array($table->table, $rules['tables']))) {
                                throwDatabaseException($logger);
                                return false;
                            }
                        }
                        foreach($statement->where AS $where) {
                            if(is_query($where->expr) AND !checkQuery($where->expr, $connectionType, $ruleset, $loggerID)) return false; // Exceptions will be thrown within the called recursive checkQuery function
                        }
                        break;
                    case "PhpMyAdmin\SqlParser\Statements\WithStatement":
                        // No reason to generally refuse these statements, therefore no action check
                        foreach($statement->withers AS $with) {
                            foreach($with->statement->statements AS $sub_statement) {
                                if(!checkQuery($sub_statement->build(), $connectionType, $ruleset, $loggerID)) return false;
                            }
                        }
                        break;
            }
        }

        return true;
}
?>