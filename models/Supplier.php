<!-- Nanda Raissa (247006111108) -->

<?php

class Supplier
{
    private PDO $conn;
    private string $table = 'supplier';

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

    public function create(string $nama_supplier, ?string $kontak, ?string $email, ?string $alamat): int
    {
        $stmt = $this->conn->prepare(
            "INSERT INTO {$this->table} (nama_supplier, kontak, email, alamat) VALUES (?, ?, ?, ?)"
        );
        $stmt->execute([$nama_supplier, $kontak, $email, $alamat]);
        return (int)$this->conn->lastInsertId();
    }

    public function update(int $id, string $nama_supplier, ?string $kontak, ?string $email, ?string $alamat): int
    {
        $stmt = $this->conn->prepare(
            "UPDATE {$this->table} SET nama_supplier = ?, kontak = ?, email = ?, alamat = ? WHERE id = ?"
        );
        $stmt->execute([$nama_supplier, $kontak, $email, $alamat, $id]);
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
        $stmt = $this->conn->prepare("SELECT COUNT(*) FROM barang WHERE supplier_id = ?");
        $stmt->execute([$id]);
        return (int)$stmt->fetchColumn() > 0;
    }

    public function emailExists(string $email, int $excludeId = 0): bool
    {
        $stmt = $this->conn->prepare(
            "SELECT COUNT(*) FROM {$this->table} WHERE email = ? AND id != ?"
        );
        $stmt->execute([$email, $excludeId]);
        return (int)$stmt->fetchColumn() > 0;
    }
}
