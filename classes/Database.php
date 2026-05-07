<?php
require_once __DIR__ . '/../config/config.php';

class Database {
    private $conn;
    private static $instance;

    private function __construct() {
        $this->connect();
    }

    public static function getInstance() {
        if (self::$instance === null) {
            self::$instance = new self();
        }
        return self::$instance;
    }

    private function connect() {
        $this->conn = new mysqli(DB_HOST, DB_USER, DB_PASS, DB_NAME, DB_PORT);

        if ($this->conn->connect_error) {
            die("Database Connection Failed: " . $this->conn->connect_error);
        }

        $this->conn->set_charset("utf8mb4");
    }

    public function getConnection() {
        return $this->conn;
    }

    public function query($sql) {
        return $this->conn->query($sql);
    }

    public function prepare($sql) {
        return $this->conn->prepare($sql);
    }

    public function execute($stmt) {
        return $stmt->execute();
    }

    public function getLastInsertId() {
        return $this->conn->insert_id;
    }

    public function getAffectedRows() {
        return $this->conn->affected_rows;
    }

    public function beginTransaction() {
        return $this->conn->begin_transaction();
    }

    public function commit() {
        return $this->conn->commit();
    }

    public function rollback() {
        return $this->conn->rollback();
    }

    public function escape($string) {
        return $this->conn->real_escape_string($string);
    }

    public function close() {
        if ($this->conn) {
            $this->conn->close();
        }
    }

    public function selectAll($table, $where = '', $order = '', $limit = '') {
        $sql = "SELECT * FROM `$table`";
        if (!empty($where)) $sql .= " WHERE $where";
        if (!empty($order)) $sql .= " ORDER BY $order";
        if (!empty($limit)) $sql .= " LIMIT $limit";

        return $this->query($sql);
    }

    public function selectOne($table, $id) {
        $sql = "SELECT * FROM `$table` WHERE `id` = $id LIMIT 1";
        $result = $this->query($sql);
        return $result ? $result->fetch_assoc() : null;
    }

    public function insert($table, $data) {
        $columns = implode(',', array_keys($data));
        $values = implode("','", array_values($data));
        $sql = "INSERT INTO `$table` ($columns) VALUES ('$values')";
        return $this->query($sql);
    }

    public function update($table, $data, $where) {
        $set = [];
        foreach ($data as $key => $value) {
            $set[] = "`$key` = '$value'";
        }
        $set = implode(', ', $set);
        $sql = "UPDATE `$table` SET $set WHERE $where";
        return $this->query($sql);
    }

    public function delete($table, $where) {
        $sql = "DELETE FROM `$table` WHERE $where";
        return $this->query($sql);
    }
}
?>

