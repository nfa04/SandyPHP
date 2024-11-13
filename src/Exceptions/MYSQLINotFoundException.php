<?php
namespace SandyPHP\Exceptions;
class MYSQLINotFoundException extends SandyPHPException implements Throwable {

public function __construct() {
    $this->message = 'SandyPHP couldn\'t find the MySQLI extension.';
}

}
?>