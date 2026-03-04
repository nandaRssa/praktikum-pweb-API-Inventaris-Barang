<?php
class Database
{
    private string $host     = 'localhost';
    private string $db_name  = 'inventaris_toko';
    private string $username = 'root';
    private string $password = '';
    private ?PDO $conn       = null;

    public function getConnection(): PDO
    {
        if ($this->conn !== null) {
            return $this->conn;
        }

        try {
            $dsn = "mysql:host={$this->host};dbname={$this->db_name};charset=utf8mb4";
            $options = [
                PDO::ATTR_ERRMODE            => PDO::ERRMODE_EXCEPTION,
                PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
                PDO::ATTR_EMULATE_PREPARES   => false,
            ];
            $this->conn = new PDO($dsn, $this->username, $this->password, $options);
        } catch (PDOException $e) {
            http_response_code(503);
            echo json_encode([
                'status'  => 'error',
                'message' => 'Koneksi database gagal: ' . $e->getMessage(),
                'data'    => null
            ]);
            exit();
        }

        return $this->conn;
    }
}
