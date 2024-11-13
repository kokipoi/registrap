<?php
class Database {
    private $host = "localhost";
    private $db_name = "duoc";
    private $username = "root"; // o el usuario que tengas configurado
    private $password = ""; // contraseña si tienes una
    public $conn;

    public function getConnection() {
        $this->conn = null;
        try {
            $this->conn = new PDO("mysql:host=" . $this->host . ";dbname=" . $this->db_name, $this->username, $this->password);
            $this->conn->exec("set names utf8");
        } catch (PDOException $exception) {
            echo "Error de conexión: " . $exception->getMessage();
        }
        return $this->conn;
    }
}
?>
