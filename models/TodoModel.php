<?php

class TodoModel {
    private $db;

    public function __construct() {
    // Memanggil file konfigurasi Anda
    // Pastikan path ini benar. Jika config.php ada di folder root, path ini seharusnya sudah tepat.
    require_once __DIR__ . '/../config.php';

    // Menggunakan konstanta dari config.php
    $host = DB_HOST;
    $port = DB_PORT;
    $dbname = DB_NAME;
    $user = DB_USER;
    $password = DB_PASSWORD;

    try {
        $this->db = new PDO("pgsql:host=$host;port=$port;dbname=$dbname", $user, $password);
        $this->db->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
    } catch (PDOException $e) {
        die("Koneksi database gagal: " . $e->getMessage());
    }
}

    /**
     * Mengambil semua todo dengan filter dan pencarian.
     * Disesuaikan untuk kolom 'activity' dan 'status'.
     */
    public function getAllTodos($filterStatus = 'semua', $search = '') {
        $sql = "SELECT * FROM todo";
        $params = [];
        $whereClauses = [];

        if ($filterStatus === 'selesai') {
            $whereClauses[] = "status = 1";
        } elseif ($filterStatus === 'belum_selesai') {
            $whereClauses[] = "status = 0";
        }

        if (!empty($search)) {
            $whereClauses[] = "(activity ILIKE :search OR description ILIKE :search)";
            $params[':search'] = '%' . $search . '%';
        }

        if (!empty($whereClauses)) {
            $sql .= " WHERE " . implode(' AND ', $whereClauses);
        }

        // Menggunakan sort_order jika ada, jika tidak urutkan berdasarkan created_at
        $sql .= " ORDER BY sort_order ASC, created_at DESC";

        $stmt = $this->db->prepare($sql);
        $stmt->execute($params);
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }
    
    public function getTodoById($id) {
        $stmt = $this->db->prepare("SELECT * FROM todo WHERE id = :id");
        $stmt->execute([':id' => $id]);
        return $stmt->fetch(PDO::FETCH_ASSOC);
    }

    /**
     * Menambahkan todo baru.
     * Disesuaikan untuk kolom 'activity' dan 'description'.
     */
    public function addTodo($activity, $description) {
    $checkStmt = $this->db->prepare("SELECT COUNT(*) FROM todo WHERE activity = :activity");
    $checkStmt->execute([':activity' => $activity]);
    if ($checkStmt->fetchColumn() > 0) {
        return false; 
    }

    $sql = "INSERT INTO todo (activity, description) VALUES (:activity, :description)";
    $stmt = $this->db->prepare($sql);
    
    return $stmt->execute([
        ':activity' => $activity,
        ':description' => $description
    ]);
}

    public function deleteTodo($id) {
        $stmt = $this->db->prepare("DELETE FROM todo WHERE id = :id");
        return $stmt->execute([':id' => $id]);
    }

    /**
     * Mengubah status (0 atau 1).
     * Trigger database akan menangani 'updated_at' secara otomatis.
     */
    public function updateTodoStatus($id, $status) {
        $sql = "UPDATE todo SET status = :status WHERE id = :id";
        $stmt = $this->db->prepare($sql);
        return $stmt->execute([
            ':id' => $id,
            ':status' => $status
        ]);
    }

    public function isActivityExists($activity) {
        $stmt = $this->db->prepare("SELECT COUNT(*) FROM todo WHERE activity = :activity");
        $stmt->execute([':activity' => $activity]);
        return $stmt->fetchColumn() > 0;
    }
    
    public function updateOrder(array $todoIds) {
        $this->db->beginTransaction();
        try {
            foreach ($todoIds as $index => $id) {
                $sql = "UPDATE todo SET sort_order = :order WHERE id = :id";
                $stmt = $this->db->prepare($sql);
                $stmt->execute([':order' => $index + 1, ':id' => $id]);
            }
            $this->db->commit();
            return true;
        } catch (Exception $e) {
            $this->db->rollBack();
            return false;
        }
    }
}