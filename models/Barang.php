<?php

class Barang
{
    private PDO $conn;
    private string $table = 'barang';

    private string $selectJoin = "
        SELECT 
            b.id, b.kode_barang, b.nama_barang,
            b.kategori_id, k.nama_kategori,
            b.stok, b.harga,
            b.supplier_id, s.nama_supplier,
            b.status_stok, b.created_at, b.updated_at
        FROM barang b
        LEFT JOIN kategori k ON b.kategori_id = k.id
        LEFT JOIN supplier s ON b.supplier_id = s.id
    ";

    public function __construct(PDO $db)
    {
        $this->conn = $db;
    }

    public function getAll(): array
    {
        $stmt = $this->conn->query($this->selectJoin . " ORDER BY b.id DESC");
        return $stmt->fetchAll();
    }

    public function getById(int $id): array|false
    {
        $stmt = $this->conn->prepare($this->selectJoin . " WHERE b.id = ?");
        $stmt->execute([$id]);
        return $stmt->fetch();
    }

    public function searchByNama(string $keyword): array
    {
        $stmt = $this->conn->prepare(
            $this->selectJoin . " WHERE b.nama_barang LIKE ? ORDER BY b.id DESC"
        );
        $stmt->execute(["%{$keyword}%"]);
        return $stmt->fetchAll();
    }

    public function getStokRendah(): array
    {
        $stmt = $this->conn->query(
            $this->selectJoin . " WHERE b.stok <= 5 ORDER BY b.stok ASC"
        );
        return $stmt->fetchAll();
    }

    public function determineStatus(int $stok): string
    {
        if ($stok === 0)    return 'Habis';
        if ($stok <= 5)     return 'Hampir Habis';
        return 'Aman';
    }

    public function create(
        string $kode_barang,
        string $nama_barang,
        int $kategori_id,
        int $stok,
        float $harga,
        int $supplier_id
    ): int {
        $status = $this->determineStatus($stok);
        $stmt = $this->conn->prepare(
            "INSERT INTO {$this->table} 
             (kode_barang, nama_barang, kategori_id, stok, harga, supplier_id, status_stok)
             VALUES (?, ?, ?, ?, ?, ?, ?)"
        );
        $stmt->execute([$kode_barang, $nama_barang, $kategori_id, $stok, $harga, $supplier_id, $status]);
        return (int)$this->conn->lastInsertId();
    }

    public function update(
        int $id,
        string $kode_barang,
        string $nama_barang,
        int $kategori_id,
        int $stok,
        float $harga,
        int $supplier_id
    ): int {
        $status = $this->determineStatus($stok);
        $stmt = $this->conn->prepare(
            "UPDATE {$this->table} SET 
             kode_barang = ?, nama_barang = ?, kategori_id = ?,
             stok = ?, harga = ?, supplier_id = ?, status_stok = ?
             WHERE id = ?"
        );
        $stmt->execute([$kode_barang, $nama_barang, $kategori_id, $stok, $harga, $supplier_id, $status, $id]);
        return $stmt->rowCount();
    }

    public function delete(int $id): int
    {
        $stmt = $this->conn->prepare("DELETE FROM {$this->table} WHERE id = ?");
        $stmt->execute([$id]);
        return $stmt->rowCount();
    }

    public function kodeExists(string $kode, int $excludeId = 0): bool
    {
        $stmt = $this->conn->prepare(
            "SELECT COUNT(*) FROM {$this->table} WHERE kode_barang = ? AND id != ?"
        );
        $stmt->execute([$kode, $excludeId]);
        return (int)$stmt->fetchColumn() > 0;
    }
}
