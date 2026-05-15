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

    private function getType($value) {
        if (is_int($value)) return 'i';
        if (is_double($value)) return 'd';
        return 's';
    }

    public function selectAll($table, $where = '', $params = [], $order = '', $limit = '') {
        $sql = "SELECT * FROM `$table`";
        if (!empty($where)) $sql .= " WHERE $where";
        if (!empty($order)) $sql .= " ORDER BY $order";
        if (!empty($limit)) $sql .= " LIMIT $limit";

        if (empty($params)) {
            return $this->query($sql);
        }

        $stmt = $this->conn->prepare($sql);
        $types = "";
        foreach ($params as $param) {
            $types .= $this->getType($param);
        }
        $stmt->bind_param($types, ...$params);
        $stmt->execute();
        return $stmt->get_result();
    }

    public function selectOne($table, $id) {
        $sql = "SELECT * FROM `$table` WHERE `id` = ? LIMIT 1";
        $stmt = $this->conn->prepare($sql);
        $stmt->bind_param('i', $id);
        $stmt->execute();
        $result = $stmt->get_result();
        return $result ? $result->fetch_assoc() : null;
    }

    public function insert($table, $data) {
        $columns = "`" . implode("`, `", array_keys($data)) . "`";
        $placeholders = implode(", ", array_fill(0, count($data), "?"));
        $sql = "INSERT INTO `$table` ($columns) VALUES ($placeholders)";
        
        $stmt = $this->conn->prepare($sql);
        $types = "";
        $values = [];
        foreach ($data as $value) {
            $types .= $this->getType($value);
            $values[] = $value;
        }
        $stmt->bind_param($types, ...$values);
        return $stmt->execute();
    }

    public function update($table, $data, $where, $whereParams = []) {
        $set = [];
        $types = "";
        $values = [];
        
        foreach ($data as $key => $value) {
            $set[] = "`$key` = ?";
            $types .= $this->getType($value);
            $values[] = $value;
        }
        $set = implode(', ', $set);
        
        $sql = "UPDATE `$table` SET $set WHERE $where";
        
        foreach ($whereParams as $param) {
            $types .= $this->getType($param);
            $values[] = $param;
        }
        
        $stmt = $this->conn->prepare($sql);
        $stmt->bind_param($types, ...$values);
        return $stmt->execute();
    }

    public function delete($table, $where, $params = []) {
        $sql = "DELETE FROM `$table` WHERE $where";
        $stmt = $this->conn->prepare($sql);
        
        if (!empty($params)) {
            $types = "";
            foreach ($params as $param) {
                $types .= $this->getType($param);
            }
            $stmt->bind_param($types, ...$params);
        }
        
        return $stmt->execute();
    }
}
?>

