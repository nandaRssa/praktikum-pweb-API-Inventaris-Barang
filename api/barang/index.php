<?php

header('Content-Type: application/json');
header('Access-Control-Allow-Origin: *');
header('Access-Control-Allow-Methods: GET, POST, PUT, DELETE, OPTIONS');
header('Access-Control-Allow-Headers: Content-Type');

if ($_SERVER['REQUEST_METHOD'] === 'OPTIONS') {
    http_response_code(200);
    exit();
}

require_once '../../config/database.php';
require_once '../../config/response.php';
require_once '../../models/Barang.php';

$db     = (new Database())->getConnection();
$barang = new Barang($db);
$method = $_SERVER['REQUEST_METHOD'];

$id          = isset($_GET['id'])          ? (int)$_GET['id']              : null;
$search      = isset($_GET['search'])      ? trim($_GET['search'])          : null;
$stok_rendah = isset($_GET['stok_rendah']) ? true                          : false;

try {
    switch ($method) {

        case 'GET':
            if ($stok_rendah) {
                $data = $barang->getStokRendah();
                Response::success("Daftar barang dengan stok rendah (≤5).", $data);
                break;
            }

            if ($search !== null) {
                if (empty($search)) {
                    Response::error("Keyword pencarian tidak boleh kosong.");
                    break;
                }
                $data = $barang->searchByNama($search);
                Response::success("Hasil pencarian barang '{$search}'.", $data);
                break;
            }

            if ($id) {
                $data = $barang->getById($id);
                if (!$data) {
                    Response::notFound("Barang dengan ID {$id} tidak ditemukan.");
                    break;
                }
                Response::success("Data barang ditemukan.", $data);
                break;
            }

            $data = $barang->getAll();
            Response::success("Daftar barang berhasil diambil.", $data);
            break;

        case 'POST':
            $input = json_decode(file_get_contents('php://input'), true);

            $required = ['kode_barang', 'nama_barang', 'kategori_id', 'stok', 'harga', 'supplier_id'];
            $missing  = [];
            foreach ($required as $field) {
                if (!isset($input[$field]) || $input[$field] === '') {
                    $missing[] = $field;
                }
            }
            if (!empty($missing)) {
                Response::error("Field wajib belum diisi: " . implode(', ', $missing));
                break;
            }

            $kode_barang  = trim($input['kode_barang']);
            $nama_barang  = trim($input['nama_barang']);
            $kategori_id  = (int)$input['kategori_id'];
            $stok         = (int)$input['stok'];
            $harga        = (float)$input['harga'];
            $supplier_id  = (int)$input['supplier_id'];

            if ($stok < 0) {
                Response::error("'stok' tidak boleh negatif.");
                break;
            }
            if ($harga < 0) {
                Response::error("'harga' tidak boleh negatif.");
                break;
            }
            if (strlen($kode_barang) > 20) {
                Response::error("'kode_barang' maksimal 20 karakter.");
                break;
            }
            if ($barang->kodeExists($kode_barang)) {
                Response::error("'kode_barang' sudah digunakan.");
                break;
            }

            $newId = $barang->create($kode_barang, $nama_barang, $kategori_id, $stok, $harga, $supplier_id);
            $data  = $barang->getById($newId);
            Response::success("Barang berhasil ditambahkan.", $data, 201);
            break;

        case 'PUT':
            if (!$id) {
                Response::error("Parameter 'id' diperlukan untuk update.");
                break;
            }

            if (!$barang->getById($id)) {
                Response::notFound("Barang dengan ID {$id} tidak ditemukan.");
                break;
            }

            $input = json_decode(file_get_contents('php://input'), true);

            $required = ['kode_barang', 'nama_barang', 'kategori_id', 'stok', 'harga', 'supplier_id'];
            $missing  = [];
            foreach ($required as $field) {
                if (!isset($input[$field]) || $input[$field] === '') {
                    $missing[] = $field;
                }
            }
            if (!empty($missing)) {
                Response::error("Field wajib belum diisi: " . implode(', ', $missing));
                break;
            }

            $kode_barang = trim($input['kode_barang']);
            $nama_barang = trim($input['nama_barang']);
            $kategori_id = (int)$input['kategori_id'];
            $stok        = (int)$input['stok'];
            $harga       = (float)$input['harga'];
            $supplier_id = (int)$input['supplier_id'];

            if ($stok < 0) {
                Response::error("'stok' tidak boleh negatif.");
                break;
            }
            if ($harga < 0) {
                Response::error("'harga' tidak boleh negatif.");
                break;
            }
            if ($barang->kodeExists($kode_barang, $id)) {
                Response::error("'kode_barang' sudah digunakan oleh barang lain.");
                break;
            }

            $barang->update($id, $kode_barang, $nama_barang, $kategori_id, $stok, $harga, $supplier_id);
            $data = $barang->getById($id);
            Response::success("Barang berhasil diperbarui.", $data);
            break;

        case 'DELETE':
            if (!$id) {
                Response::error("Parameter 'id' diperlukan untuk delete.");
                break;
            }

            if (!$barang->getById($id)) {
                Response::notFound("Barang dengan ID {$id} tidak ditemukan.");
                break;
            }

            $barang->delete($id);
            Response::success("Barang berhasil dihapus.");
            break;

        default:
            Response::methodNotAllowed();
    }
} catch (PDOException $e) {
    if ($e->getCode() === '23000') {
        Response::error("Data duplikat: kode_barang sudah ada.");
    } else {
        Response::error("Database error: " . $e->getMessage(), 503);
    }
} catch (Throwable $e) {
    Response::error("Server error: " . $e->getMessage(), 500);
}
