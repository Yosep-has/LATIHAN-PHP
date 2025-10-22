
<?php
require_once (__DIR__ . '/../config.php');

class TodoModel
{
    private $conn;

    public function __construct()
    {
        // Inisialisasi koneksi database PostgreSQL
        $this->conn = pg_connect('host=' . DB_HOST . ' port=' . DB_PORT . ' dbname=' . DB_NAME . ' user=' . DB_USER . ' password=' . DB_PASSWORD);
        if (!$this->conn) {
            die('Koneksi database gagal');
        }
    }

    public function getAllTodos()
    {
        $query = 'SELECT * FROM todo';
        $result = pg_query($this->conn, $query);
        $todos = [];
        if ($result && pg_num_rows($result) > 0) {
            while ($row = pg_fetch_assoc($result)) {
                $todos[] = $row;
            }
        }
        return $todos;
    }

    public function createTodo($activity)
    {
        $query = 'INSERT INTO todo (activity) VALUES ($1)';
        $result = pg_query_params($this->conn, $query, [$activity]);
        return $result !== false;
    }

    public function updateTodo($id, $activity, $status)
    {
        $query = 'UPDATE todo SET activity=$1, status=$2 WHERE id=$3';
        $result = pg_query_params($this->conn, $query, [$activity, $status, $id]);
        return $result !== false;
    }

    public function deleteTodo($id)
    {
        $query = 'DELETE FROM todo WHERE id=$1';
        $result = pg_query_params($this->conn, $query, [$id]);
        return $result !== false;
    }
}
