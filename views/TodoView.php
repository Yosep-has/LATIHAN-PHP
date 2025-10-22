<!DOCTYPE html>
<html>
<head>
    <title>PHP - Aplikasi Todolist</title>
    <link href="/assets/vendor/bootstrap-5.3.8-dist/css/bootstrap.min.css" rel="stylesheet" />
</head>
<body>
<div class="container-fluid p-5">
    <div class="card">
        <div class="card-body">
            <div class="d-flex justify-content-between align-items-center">
                <h1>Todo List</h1>
                <button class="btn btn-primary" data-bs-toggle="modal" data-bs-target="#addTodo">Tambah Data</button>
            </div>
            <hr />
            <table class="table table-striped">
                <thead>
                    <tr>
                        <th scope="col">#</th>
                        <th scope="col">Aktivitas</th>
                        <th scope="col">Status</th>
                        <th scope="col">Tanggal Dibuat</th>
                        <th scope="col">Tindakan</th>
                    </tr>
                </thead>
                <tbody>
                <?php if (!empty($todos)): ?>
                    <?php foreach ($todos as $i => $todo): ?>
                    <tr>
                        <td><?= $i + 1 ?></td>
                        <td><?= htmlspecialchars($todo['activity']) ?></td>
                        <td>
                            <?php if ($todo['status']): ?>
                                <span class="badge bg-success">Selesai</span>
                            <?php else: ?>
                                <span class="badge bg-danger">Belum Selesai</span>
                            <?php endif; ?>
                        </td>
                        <td><?= date('d F Y - H:i', strtotime($todo['created_at'])) ?></td>
                        <td>
                            <button class="btn btn-sm btn-warning"
                                onclick="showModalEditTodo(<?= $todo['id'] ?>, '<?= htmlspecialchars(addslashes($todo['activity'])) ?>', <?= $todo['status'] ?>)">
                                Ubah
                            </button>
                            <button class="btn btn-sm btn-danger"
                                onclick="showModalDeleteTodo(<?= $todo['id'] ?>, '<?= htmlspecialchars(addslashes($todo['activity'])) ?>')">
                                Hapus
                            </button>
                        </td>
                    </tr>
                    <?php endforeach; ?>
                <?php else: ?>
                    <tr>
                        <td colspan="5" class="text-center text-muted">Belum ada data tersedia!</td>
                    </tr>
                <?php endif; ?>
                </tbody>
            </table>
        </div>
    </div>
</div>

<!-- MODAL ADD TODO -->
<div class="modal fade" id="addTodo" tabindex="-1" aria-labelledby="addTodoLabel" aria-hidden="true">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="addTodoLabel">Tambah Data Todo</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <form action="?page=create" method="POST">
                <div class="modal-body">
                    <div class="mb-3">
                        <label for="inputActivity" class="form-label">Aktivitas</label>
                        <input type="text" name="activity" class="form-control" id="inputActivity"
                            placeholder="Contoh: Belajar membuat aplikasi website sederhana" required>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Batal</button>
                    <button type="submit" class="btn btn-primary">Save</button>
                </div>
            </form>
        </div>
    </div>
</div>

<!-- MODAL EDIT TODO -->
<div class="modal fade" id="editTodo" tabindex="-1" aria-labelledby="editTodoLabel" aria-hidden="true">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="editTodoLabel">Ubah Data Todo</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <form action="?page=update" method="POST">
                <input name="id" type="hidden" id="inputEditTodoId">
                <div class="modal-body">
                    <div class="mb-3">
                        <label for="inputEditActivity" class="form-label">Aktivitas</label>
                        <input type="text" name="activity" class="form-control" id="inputEditActivity"
                            placeholder="Contoh: Belajar membuat aplikasi website sederhana" required>
                    </div>
                    <div class="mb-3">
                        <label for="selectEditStatus" class="form-label">Status</label>
                        <select class="form-select" name="status" id="selectEditStatus">
                            <option value="0">Belum Selesai</option>
                            <option value="1">Selesai</option>
                        </select>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Batal</button>
                    <button type="submit" class="btn btn-primary">Save</button>
                </div>
            </form>
        </div>
    </div>
</div>

<!-- MODAL DELETE TODO -->
<div class="modal fade" id="deleteTodo" tabindex="-1" aria-labelledby="deleteTodoLabel" aria-hidden="true">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="deleteTodoLabel">Hapus Data Todo</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body">
                <div class="mb-3">
                    Kamu akan menghapus todo <strong class="text-danger" id="deleteTodoActivity"></strong>.
                    Apakah kamu yakin?
                </div>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Batal</button>
                <a id="btnDeleteTodo" class="btn btn-danger">Ya, Tetap Hapus</a>
            </div>
        </div>
    </div>
</div>

<script src="/assets/vendor/bootstrap-5.3.8-dist/js/bootstrap.min.js"></script>
<script>
function showModalEditTodo(todoId, activity, status) {
    document.getElementById("inputEditTodoId").value = todoId;
    document.getElementById("inputEditActivity").value = activity;
    document.getElementById("selectEditStatus").value = status;
    var myModal = new bootstrap.Modal(document.getElementById("editTodo"));
    myModal.show();
}
function showModalDeleteTodo(todoId, activity) {
    document.getElementById("deleteTodoActivity").innerText = activity;
    document.getElementById("btnDeleteTodo").setAttribute("href", `?page=delete&id=${todoId}`);
    var myModal = new bootstrap.Modal(document.getElementById("deleteTodo"));
    myModal.show();
}
</script>
</body>
</html>

