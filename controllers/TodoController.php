<?php
require '../models/TodoModel.php';
require '../views/TodoView.php';

class TodoController {
    private $model;
    private $view;

    public function __construct() {
        $this->model = new TodoModel();
        $this->view = new TodoView();
    }

    public function index() {
        $status = $_GET['status'] ?? 'semua';
        $search = $_GET['q'] ?? '';
        $todos = $this->model->getAllTodos($status, $search);
        $this->view->render($todos, $status, $search);
    }

    public function add() {
    if ($_SERVER['REQUEST_METHOD'] === 'POST') {
        $activity = trim($_POST['activity']);
        $description = trim($_POST['description']);

        if (empty($activity)) {
            // Langsung redirect jika input kosong
            header('Location: /');
            exit;
        }

        
        if (!$this->model->addTodo($activity, $description)) {
            session_start();
            $_SESSION['error'] = "Aktivitas '$activity' sudah ada. Gunakan nama lain.";
        }

        header('Location: /');
        exit;
    }
}

    public function delete() {
        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            $this->model->deleteTodo($_POST['id']);
        }
        header('Location: /');
        exit;
    }

    /**
     * Menangani perubahan status (0 atau 1).
     */
    public function toggle() {
        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            $id = $_POST['id'];
            // Jika checkbox dicentang, status = 1 (selesai), jika tidak, status = 0
            $status = isset($_POST['status']) ? 1 : 0;
            $this->model->updateTodoStatus($id, $status);
        }
        header('Location: ' . $_SERVER['HTTP_REFERER']);
        exit;
    }

    public function detail() {
        if (isset($_GET['id'])) {
            $id = $_GET['id'];
            $todo = $this->model->getTodoById($id);
            if ($todo) {
                header('Content-Type: application/json');
                echo json_encode($todo);
            } else {
                http_response_code(404);
                echo json_encode(['error' => 'Todo tidak ditemukan']);
            }
        }
    }

    public function reorder() {
        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            $data = json_decode(file_get_contents('php://input'), true);
            if (isset($data['order']) && is_array($data['order'])) {
                if ($this->model->updateOrder($data['order'])) {
                    header('Content-Type: application/json');
                    echo json_encode(['status' => 'success']);
                } else {
                    http_response_code(500);
                    echo json_encode(['status' => 'error', 'message' => 'Gagal menyimpan urutan.']);
                }
            } else {
                http_response_code(400);
                echo json_encode(['status' => 'error', 'message' => 'Data tidak valid.']);
            }
        }
    }
}