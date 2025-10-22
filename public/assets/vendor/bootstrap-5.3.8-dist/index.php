<?php
if (isset($_GET['page'])) {
    $page = $_GET['page'];
} else {
    $page = 'index';
}
include ('../controllers/TodoController.php');

$todoController = new TodoController();
switch ($page) {
    case 'index':
        $todoController->index();
        break;
    case 'create':
        $todoController->create();
        break;
    case 'update':
        $todoController->update();
        break;
    case 'delete':
        $todoController->delete();
        break;
}
