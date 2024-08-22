<?php 
function checkQuery($sql, $connectionType) {
        $parser = new PhpMyAdmin\SqlParser\Parser($sql);
        
        // Loop over the statements and check them
        foreach($parser->statements AS $statement) {
            switch(get_class($statement)) {
                case "PhpMyAdmin\SqlParser\Statements\SelectStatement":
                    foreach($statement->from AS $exp) {
                        if(($exp->database !== null AND !in_array($exp->database, SANDBOX_CONFIG['database'][$connectionType]['database_names'])) OR ($exp->table !== null AND !in_array($exp->table, SANDBOX_CONFIG['database'][$connectionType]['tables']))) return false;
                    }
                    break;
            }
        }

        return true;
}
?>