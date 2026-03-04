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
require_once '../../models/Kategori.php';

$db       = (new Database())->getConnection();
$kategori = new Kategori($db);
$method   = $_SERVER['REQUEST_METHOD'];

$id = isset($_GET['id']) ? (int)$_GET['id'] : null;

try {
    switch ($method) {

        case 'GET':
            if ($id) {
                $data = $kategori->getById($id);
                if (!$data) {
                    Response::notFound("Kategori dengan ID {$id} tidak ditemukan.");
                    break;
                }
                Response::success("Data kategori ditemukan.", $data);
            } else {
                $data = $kategori->getAll();
                Response::success("Daftar kategori berhasil diambil.", $data);
            }
            break;

        case 'POST':
            $input = json_decode(file_get_contents('php://input'), true);

            if (empty($input['nama_kategori'])) {
                Response::error("Field 'nama_kategori' wajib diisi.");
                break;
            }

            $nama_kategori = trim($input['nama_kategori']);
            $deskripsi     = isset($input['deskripsi']) ? trim($input['deskripsi']) : null;

            if (strlen($nama_kategori) > 100) {
                Response::error("'nama_kategori' maksimal 100 karakter.");
                break;
            }

            $newId = $kategori->create($nama_kategori, $deskripsi);
            $data  = $kategori->getById($newId);
            Response::success("Kategori berhasil ditambahkan.", $data, 201);
            break;

        case 'PUT':
            if (!$id) {
                Response::error("Parameter 'id' diperlukan untuk update.");
                break;
            }

            if (!$kategori->getById($id)) {
                Response::notFound("Kategori dengan ID {$id} tidak ditemukan.");
                break;
            }

            $input = json_decode(file_get_contents('php://input'), true);

            if (empty($input['nama_kategori'])) {
                Response::error("Field 'nama_kategori' wajib diisi.");
                break;
            }

            $nama_kategori = trim($input['nama_kategori']);
            $deskripsi     = isset($input['deskripsi']) ? trim($input['deskripsi']) : null;

            if (strlen($nama_kategori) > 100) {
                Response::error("'nama_kategori' maksimal 100 karakter.");
                break;
            }

            $kategori->update($id, $nama_kategori, $deskripsi);
            $data = $kategori->getById($id);
            Response::success("Kategori berhasil diperbarui.", $data);
            break;

        case 'DELETE':
            if (!$id) {
                Response::error("Parameter 'id' diperlukan untuk delete.");
                break;
            }

            if (!$kategori->getById($id)) {
                Response::notFound("Kategori dengan ID {$id} tidak ditemukan.");
                break;
            }

            if ($kategori->isUsed($id)) {
                Response::error("Kategori tidak dapat dihapus karena masih digunakan oleh barang.", 400);
                break;
            }

            $kategori->delete($id);
            Response::success("Kategori berhasil dihapus.");
            break;

        default:
            Response::methodNotAllowed();
    }
} catch (PDOException $e) {
    Response::error("Database error: " . $e->getMessage(), 503);
} catch (Throwable $e) {
    Response::error("Server error: " . $e->getMessage(), 500);
}
