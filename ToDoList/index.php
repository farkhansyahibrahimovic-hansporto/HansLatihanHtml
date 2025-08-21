<?php
session_start();

// Inisialisasi array tugas jika belum ada
if (!isset($_SESSION['tasks'])) {
    $_SESSION['tasks'] = [];
}

// Fungsi untuk menambah tugas
if (isset($_POST['add_task']) && !empty($_POST['task'])) {
    $newTask = [
        'id' => uniqid(),
        'task' => htmlspecialchars($_POST['task']),
        'completed' => false,
        'created_at' => date('Y-m-d H:i:s')
    ];
    $_SESSION['tasks'][] = $newTask;
    header('Location: ' . $_SERVER['PHP_SELF']);
    exit;
}

// Fungsi untuk menandai tugas selesai
if (isset($_GET['toggle']) && !empty($_GET['toggle'])) {
    foreach ($_SESSION['tasks'] as &$task) {
        if ($task['id'] == $_GET['toggle']) {
            $task['completed'] = !$task['completed'];
            break;
        }
    }
    header('Location: ' . $_SERVER['PHP_SELF']);
    exit;
}

// Fungsi untuk menghapus tugas
if (isset($_GET['delete']) && !empty($_GET['delete'])) {
    $_SESSION['tasks'] = array_filter($_SESSION['tasks'], function($task) {
        return $task['id'] != $_GET['delete'];
    });
    header('Location: ' . $_SERVER['PHP_SELF']);
    exit;
}

// Fungsi untuk menghapus semua tugas
if (isset($_POST['clear_all'])) {
    $_SESSION['tasks'] = [];
    header('Location: ' . $_SERVER['PHP_SELF']);
    exit;
}

// Hitung statistik
$totalTasks = count($_SESSION['tasks']);
$completedTasks = count(array_filter($_SESSION['tasks'], function($task) {
    return $task['completed'];
}));
$pendingTasks = $totalTasks - $completedTasks;
?>

<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>To-Do List Sederhana</title>
    <style>
        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
        }

        body {
            font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            min-height: 100vh;
            padding: 20px;
        }

        .container {
            max-width: 800px;
            margin: 0 auto;
            background: white;
            border-radius: 15px;
            box-shadow: 0 20px 40px rgba(0,0,0,0.1);
            overflow: hidden;
        }

        .header {
            background: linear-gradient(135deg, #ff6b6b, #ee5a24);
            color: white;
            padding: 30px;
            text-align: center;
        }

        .header h1 {
            font-size: 2.5em;
            margin-bottom: 10px;
        }

        .stats {
            display: flex;
            justify-content: space-around;
            background: #f8f9fa;
            padding: 20px;
            border-bottom: 1px solid #dee2e6;
        }

        .stat-item {
            text-align: center;
        }

        .stat-number {
            font-size: 2em;
            font-weight: bold;
            color: #495057;
        }

        .stat-label {
            color: #6c757d;
            font-size: 0.9em;
        }

        .add-task-form {
            padding: 30px;
            background: #fff;
        }

        .form-group {
            display: flex;
            gap: 10px;
            margin-bottom: 20px;
        }

        .task-input {
            flex: 1;
            padding: 15px;
            border: 2px solid #e9ecef;
            border-radius: 8px;
            font-size: 16px;
            transition: border-color 0.3s;
        }

        .task-input:focus {
            outline: none;
            border-color: #667eea;
        }

        .btn {
            padding: 15px 25px;
            border: none;
            border-radius: 8px;
            cursor: pointer;
            font-size: 16px;
            font-weight: 600;
            transition: all 0.3s;
            text-decoration: none;
            display: inline-block;
        }

        .btn-primary {
            background: linear-gradient(135deg, #667eea, #764ba2);
            color: white;
        }

        .btn-primary:hover {
            transform: translateY(-2px);
            box-shadow: 0 5px 15px rgba(102, 126, 234, 0.4);
        }

        .btn-danger {
            background: linear-gradient(135deg, #ff6b6b, #ee5a24);
            color: white;
        }

        .btn-danger:hover {
            transform: translateY(-2px);
            box-shadow: 0 5px 15px rgba(255, 107, 107, 0.4);
        }

        .btn-small {
            padding: 8px 12px;
            font-size: 12px;
        }

        .tasks-container {
            padding: 0 30px 30px;
        }

        .task-item {
            display: flex;
            align-items: center;
            gap: 15px;
            padding: 20px;
            margin-bottom: 10px;
            background: #f8f9fa;
            border-radius: 10px;
            transition: all 0.3s;
        }

        .task-item:hover {
            transform: translateX(5px);
            box-shadow: 0 5px 15px rgba(0,0,0,0.1);
        }

        .task-item.completed {
            background: #d4edda;
            opacity: 0.7;
        }

        .task-item.completed .task-text {
            text-decoration: line-through;
            color: #6c757d;
        }

        .task-text {
            flex: 1;
            font-size: 16px;
            color: #495057;
        }

        .task-date {
            font-size: 12px;
            color: #6c757d;
        }

        .task-actions {
            display: flex;
            gap: 10px;
        }

        .empty-state {
            text-align: center;
            padding: 50px;
            color: #6c757d;
        }

        .empty-state h3 {
            margin-bottom: 10px;
        }

        .clear-all {
            text-align: center;
            margin-top: 20px;
        }

        @media (max-width: 600px) {
            .form-group {
                flex-direction: column;
            }
            
            .stats {
                flex-direction: column;
                gap: 20px;
            }
            
            .task-item {
                flex-direction: column;
                align-items: flex-start;
            }
            
            .task-actions {
                align-self: flex-end;
            }
        }
    </style>
</head>
<body>
    <div class="container">
        <div class="header">
            <h1>📝 To-Do List</h1>
            <p>Kelola tugas harian Anda dengan mudah</p>
        </div>

        <div class="stats">
            <div class="stat-item">
                <div class="stat-number"><?php echo $totalTasks; ?></div>
                <div class="stat-label">Total Tugas</div>
            </div>
            <div class="stat-item">
                <div class="stat-number"><?php echo $pendingTasks; ?></div>
                <div class="stat-label">Belum Selesai</div>
            </div>
            <div class="stat-item">
                <div class="stat-number"><?php echo $completedTasks; ?></div>
                <div class="stat-label">Selesai</div>
            </div>
        </div>

        <div class="add-task-form">
            <form method="POST" action="">
                <div class="form-group">
                    <input type="text" name="task" class="task-input" placeholder="Tambahkan tugas baru..." required>
                    <button type="submit" name="add_task" class="btn btn-primary">Tambah</button>
                </div>
            </form>
        </div>

        <div class="tasks-container">
            <?php if (empty($_SESSION['tasks'])): ?>
                <div class="empty-state">
                    <h3>🎉 Tidak ada tugas!</h3>
                    <p>Tambahkan tugas pertama Anda di atas</p>
                </div>
            <?php else: ?>
                <?php foreach ($_SESSION['tasks'] as $task): ?>
                    <div class="task-item <?php echo $task['completed'] ? 'completed' : ''; ?>">
                        <div class="task-text"><?php echo $task['task']; ?></div>
                        <div class="task-date"><?php echo date('d/m/Y H:i', strtotime($task['created_at'])); ?></div>
                        <div class="task-actions">
                            <a href="?toggle=<?php echo $task['id']; ?>" class="btn btn-primary btn-small">
                                <?php echo $task['completed'] ? '↶ Batal' : '✓ Selesai'; ?>
                            </a>
                            <a href="?delete=<?php echo $task['id']; ?>" class="btn btn-danger btn-small" onclick="return confirm('Yakin ingin menghapus tugas ini?')">
                                🗑️ Hapus
                            </a>
                        </div>
                    </div>
                <?php endforeach; ?>
                
                <?php if ($totalTasks > 0): ?>
                    <div class="clear-all">
                        <form method="POST" action="" style="display: inline;">
                            <button type="submit" name="clear_all" class="btn btn-danger" onclick="return confirm('Yakin ingin menghapus semua tugas?')">
                                🗑️ Hapus Semua Tugas
                            </button>
                        </form>
                    </div>
                <?php endif; ?>
            <?php endif; ?>
        </div>
    </div>

    <script>
        // Auto focus pada input ketika halaman dimuat
        document.querySelector('.task-input').focus();
        
        // Animasi smooth untuk elemen yang baru ditambahkan
        document.addEventListener('DOMContentLoaded', function() {
            const taskItems = document.querySelectorAll('.task-item');
            taskItems.forEach((item, index) => {
                item.style.animationDelay = (index * 100) + 'ms';
                item.style.animation = 'slideIn 0.5s ease forwards';
            });
        });
        
        // CSS Animation
        const style = document.createElement('style');
        style.textContent = `
            @keyframes slideIn {
                from {
                    opacity: 0;
                    transform: translateY(20px);
                }
                to {
                    opacity: 1;
                    transform: translateY(0);
                }
            }
        `;
        document.head.appendChild(style);
    </script>
</body>
</html>