<!-- Nanda Raissa (247006111108) -->

<?php

class Kategori
{
    private PDO $conn;
    private string $table = 'kategori';

    public function __construct(PDO $db)
    {
        $this->conn = $db;
    }

    public function getAll(): array
    {
        $stmt = $this->conn->query("SELECT * FROM {$this->table} ORDER BY id DESC");
        return $stmt->fetchAll();
    }

    public function getById(int $id): array|false
    {
        $stmt = $this->conn->prepare("SELECT * FROM {$this->table} WHERE id = ?");
        $stmt->execute([$id]);
        return $stmt->fetch();
    }

    public function create(string $nama_kategori, ?string $deskripsi): int
    {
        $stmt = $this->conn->prepare(
            "INSERT INTO {$this->table} (nama_kategori, deskripsi) VALUES (?, ?)"
        );
        $stmt->execute([$nama_kategori, $deskripsi]);
        return (int)$this->conn->lastInsertId();
    }

    public function update(int $id, string $nama_kategori, ?string $deskripsi): int
    {
        $stmt = $this->conn->prepare(
            "UPDATE {$this->table} SET nama_kategori = ?, deskripsi = ? WHERE id = ?"
        );
        $stmt->execute([$nama_kategori, $deskripsi, $id]);
        return $stmt->rowCount();
    }

    public function delete(int $id): int
    {
        $stmt = $this->conn->prepare("DELETE FROM {$this->table} WHERE id = ?");
        $stmt->execute([$id]);
        return $stmt->rowCount();
    }

    public function isUsed(int $id): bool
    {
        $stmt = $this->conn->prepare("SELECT COUNT(*) FROM barang WHERE kategori_id = ?");
        $stmt->execute([$id]);
        return (int)$stmt->fetchColumn() > 0;
    }
}
