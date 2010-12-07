<?php

require_once "phing/tasks/ext/pdo/PDOSQLExecTask.php";

class YiiSQLTask extends PDOSQLExecTask {

  private $databaseName;

  public function setDropdb($enable) {
    if ($enable) {
      /** @var $connection PDO */
      $connection = $this->getConnection();
      $connection->exec('DROP database ' . $this->databaseName);
      $connection->exec('CREATE database ' . $this->databaseName);
    }
  }

  public function init() {
    if (file_exists('config/main.php')) {
      $config = include_once('config/main.php');
      $this->setUrl($config['components']['db']['connectionString']);
      $this->setPassword($config['components']['db']['password']);
      $this->setUserid($config['components']['db']['username']);
      $this->databaseName = substr(
        $config['components']['db']['connectionString'],
        stripos($config['components']['db']['connectionString'], 'dbname=')+7
      );
      parent::init();
    }
  }
}

?>
