<?php
class TodoView {
    public function render($todos, $currentStatus, $currentSearch) {
        session_start();
        $error = $_SESSION['error'] ?? null;
        unset($_SESSION['error']);
?>
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>ToDo List Modern</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.min.css" rel="stylesheet">
    <style>
        body { background-color: #f0f2f5; }
        .container { max-width: 800px; }
        .todo-item { cursor: grab; display: flex; align-items: center; }
        .todo-item:active { cursor: grabbing; }
        .todo-title { flex-grow: 1; margin-left: 1rem; }
        .todo-item.finished .todo-title { text-decoration: line-through; color: #6c757d; }
        .drag-ghost { opacity: 0.5; background: #c8ebfb; }
        .filter-nav .btn { border-radius: 99px; }
    </style>
</head>
<body>
    <div class="container mt-5">
        <div class="card shadow-sm">
            <div class="card-body">
                <h1 class="card-title text-center mb-4">📝 ToDo List Saya</h1>
                
                <?php if ($error): ?>
                    <div class="alert alert-danger" role="alert"><?= htmlspecialchars($error) ?></div>
                <?php endif; ?>

                <form action="/add" method="POST" class="mb-4">
                    <div class="mb-3">
                        <input type="text" name="activity" class="form-control" placeholder="Apa aktivitas baru Anda?" required>
                    </div>
                    <div class="mb-3">
                        <textarea name="description" class="form-control" placeholder="Tambahkan deskripsi (opsional)"></textarea>
                    </div>
                    <button type="submit" class="btn btn-primary w-100">Tambah</button>
                </form>

                <div class="d-flex flex-column flex-md-row justify-content-between align-items-center mb-3 gap-2">
                    <div class="btn-group filter-nav" role="group">
                        <a href="/?status=semua&q=<?= urlencode($currentSearch) ?>" class="btn <?= $currentStatus == 'semua' ? 'btn-primary' : 'btn-outline-secondary' ?>">Semua</a>
                        <a href="/?status=belum_selesai&q=<?= urlencode($currentSearch) ?>" class="btn <?= $currentStatus == 'belum_selesai' ? 'btn-primary' : 'btn-outline-secondary' ?>">Belum Selesai</a>
                        <a href="/?status=selesai&q=<?= urlencode($currentSearch) ?>" class="btn <?= $currentStatus == 'selesai' ? 'btn-primary' : 'btn-outline-secondary' ?>">Selesai</a>
                    </div>
                    <form action="/" method="GET" class="d-flex">
                        <input type="hidden" name="status" value="<?= htmlspecialchars($currentStatus) ?>">
                        <input class="form-control me-2" type="search" name="q" placeholder="Cari..." value="<?= htmlspecialchars($currentSearch) ?>">
                        <button class="btn btn-outline-success" type="submit">Cari</button>
                    </form>
                </div>

                <ul class="list-group" id="todo-list">
                    <?php if (empty($todos)): ?>
                        <li class="list-group-item text-center text-muted">Tidak ada data.</li>
                    <?php else: ?>
                        <?php foreach ($todos as $todo): ?>
                            <li class="list-group-item todo-item <?= $todo['status'] == 1 ? 'finished' : '' ?>" data-id="<?= $todo['id'] ?>">
                                <form action="/toggle" method="POST" class="d-inline">
                                    <input type="hidden" name="id" value="<?= $todo['id'] ?>">
                                    <input class="form-check-input" type="checkbox" name="status" <?= $todo['status'] == 1 ? 'checked' : '' ?> onchange="this.form.submit()">
                                </form>
                                <span class="todo-title"><?= htmlspecialchars($todo['activity']) ?></span>
                                <div class="ms-auto">
                                    <button class="btn btn-sm btn-outline-info btn-detail" data-id="<?= $todo['id'] ?>" data-bs-toggle="modal" data-bs-target="#detailModal">
                                        <i class="bi bi-eye"></i> Detail
                                    </button>
                                    <form action="/delete" method="POST" class="d-inline" onsubmit="return confirm('Yakin ingin menghapus?');">
                                        <input type="hidden" name="id" value="<?= $todo['id'] ?>">
                                        <button type="submit" class="btn btn-sm btn-outline-danger"><i class="bi bi-trash"></i></button>
                                    </form>
                                </div>
                            </li>
                        <?php endforeach; ?>
                    <?php endif; ?>
                </ul>
            </div>
        </div>
    </div>

    <div class="modal fade" id="detailModal" tabindex="-1">
        <div class="modal-dialog">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title" id="detailModalTitle">Detail Aktivitas</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                </div>
                <div class="modal-body">
                    <h6>Deskripsi:</h6>
                    <p id="detailDescription">Tidak ada deskripsi.</p>
                    <hr>
                    <small class="text-muted">Dibuat: <span id="detailCreatedAt"></span></small><br>
                    <small class="text-muted">Terakhir diubah: <span id="detailUpdatedAt"></span></small>
                </div>
            </div>
        </div>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/sortablejs@latest/Sortable.min.js"></script>
    <script>
    document.addEventListener('DOMContentLoaded', function () {
        const detailModal = document.getElementById('detailModal');
        detailModal.addEventListener('show.bs.modal', async (event) => {
            const todoId = event.relatedTarget.getAttribute('data-id');
            const response = await fetch(`/detail?id=${todoId}`);
            if (!response.ok) return;
            const todo = await response.json();

            const formatDate = (dateString) => {
                if (!dateString) return 'N/A';
                const options = { year: 'numeric', month: 'long', day: 'numeric', hour: '2-digit', minute: '2-digit' };
                return new Date(dateString).toLocaleDateString('id-ID', options);
            };

            document.getElementById('detailModalTitle').textContent = todo.activity;
            document.getElementById('detailDescription').textContent = todo.description || 'Tidak ada deskripsi.';
            document.getElementById('detailCreatedAt').textContent = formatDate(todo.created_at);
            document.getElementById('detailUpdatedAt').textContent = formatDate(todo.updated_at);
        });

        const todoListEl = document.getElementById('todo-list');
        new Sortable(todoListEl, {
            animation: 150,
            ghostClass: 'drag-ghost',
            onEnd: (evt) => {
                const order = Array.from(todoListEl.children).map(item => item.getAttribute('data-id'));
                fetch('/reorder', {
                    method: 'POST',
                    headers: { 'Content-Type': 'application/json' },
                    body: JSON.stringify({ order: order }),
                }).catch(error => console.error('Error:', error));
            }
        });
    });
    </script>
</body>
</html>
<?php
    }
}
?>