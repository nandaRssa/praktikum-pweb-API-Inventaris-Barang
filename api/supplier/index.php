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
require_once '../../models/Supplier.php';

$db       = (new Database())->getConnection();
$supplier = new Supplier($db);
$method   = $_SERVER['REQUEST_METHOD'];
$id       = isset($_GET['id']) ? (int)$_GET['id'] : null;

try {
    switch ($method) {

        case 'GET':
            if ($id) {
                $data = $supplier->getById($id);
                if (!$data) {
                    Response::notFound("Supplier dengan ID {$id} tidak ditemukan.");
                    break;
                }
                Response::success("Data supplier ditemukan.", $data);
            } else {
                $data = $supplier->getAll();
                Response::success("Daftar supplier berhasil diambil.", $data);
            }
            break;

        case 'POST':
            $input = json_decode(file_get_contents('php://input'), true);

            if (empty($input['nama_supplier'])) {
                Response::error("Field 'nama_supplier' wajib diisi.");
                break;
            }

            $nama_supplier = trim($input['nama_supplier']);
            $kontak        = isset($input['kontak']) ? trim($input['kontak']) : null;
            $email         = isset($input['email'])  ? trim($input['email'])  : null;
            $alamat        = isset($input['alamat'])  ? trim($input['alamat']) : null;

            if (strlen($nama_supplier) > 150) {
                Response::error("'nama_supplier' maksimal 150 karakter.");
                break;
            }

            if ($email && !filter_var($email, FILTER_VALIDATE_EMAIL)) {
                Response::error("Format email tidak valid.");
                break;
            }

            if ($email && $supplier->emailExists($email)) {
                Response::error("Email sudah digunakan oleh supplier lain.");
                break;
            }

            $newId = $supplier->create($nama_supplier, $kontak, $email, $alamat);
            $data  = $supplier->getById($newId);
            Response::success("Supplier berhasil ditambahkan.", $data, 201);
            break;

        case 'PUT':
            if (!$id) {
                Response::error("Parameter 'id' diperlukan untuk update.");
                break;
            }

            if (!$supplier->getById($id)) {
                Response::notFound("Supplier dengan ID {$id} tidak ditemukan.");
                break;
            }

            $input = json_decode(file_get_contents('php://input'), true);

            if (empty($input['nama_supplier'])) {
                Response::error("Field 'nama_supplier' wajib diisi.");
                break;
            }

            $nama_supplier = trim($input['nama_supplier']);
            $kontak        = isset($input['kontak']) ? trim($input['kontak']) : null;
            $email         = isset($input['email'])  ? trim($input['email'])  : null;
            $alamat        = isset($input['alamat'])  ? trim($input['alamat']) : null;

            if ($email && !filter_var($email, FILTER_VALIDATE_EMAIL)) {
                Response::error("Format email tidak valid.");
                break;
            }

            if ($email && $supplier->emailExists($email, $id)) {
                Response::error("Email sudah digunakan oleh supplier lain.");
                break;
            }

            $supplier->update($id, $nama_supplier, $kontak, $email, $alamat);
            $data = $supplier->getById($id);
            Response::success("Supplier berhasil diperbarui.", $data);
            break;

        case 'DELETE':
            if (!$id) {
                Response::error("Parameter 'id' diperlukan untuk delete.");
                break;
            }

            if (!$supplier->getById($id)) {
                Response::notFound("Supplier dengan ID {$id} tidak ditemukan.");
                break;
            }

            if ($supplier->isUsed($id)) {
                Response::error("Supplier tidak dapat dihapus karena masih digunakan oleh barang.", 400);
                break;
            }

            $supplier->delete($id);
            Response::success("Supplier berhasil dihapus.");
            break;

        default:
            Response::methodNotAllowed();
    }
} catch (PDOException $e) {
    Response::error("Database error: " . $e->getMessage(), 503);
} catch (Throwable $e) {
    Response::error("Server error: " . $e->getMessage(), 500);
}
