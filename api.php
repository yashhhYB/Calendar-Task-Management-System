<?php
// Database Connection
class Database
{
    private static $pdo = null;
    private static $isAvailable = null;

    public static function connect()
    {
        if (self::$pdo === null) {
            try {
                if (!in_array('sqlite', PDO::getAvailableDrivers())) {
                    throw new Exception("SQLite driver not available");
                }

                $dbPath = __DIR__ . '/database.sqlite';
                self::$pdo = new PDO("sqlite:" . $dbPath);
                self::$pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
                self::$pdo->setAttribute(PDO::ATTR_DEFAULT_FETCH_MODE, PDO::FETCH_ASSOC);
                self::initialize($dbPath);
                self::$isAvailable = true;
            } catch (Exception $e) {
                self::$isAvailable = false;
                return null;
            }
        }
        return self::$pdo;
    }

    public static function isAvailable()
    {
        if (self::$isAvailable === null) {
            self::connect();
        }
        return self::$isAvailable;
    }

    private static function initialize($dbPath)
    {
        $stmt = self::$pdo->query("SELECT name FROM sqlite_master WHERE type='table' AND name='tasks'");
        if (!$stmt->fetch()) {
            // Simple schema creation if file doesn't exist
            $schema = "CREATE TABLE IF NOT EXISTS tasks (
                id INTEGER PRIMARY KEY AUTOINCREMENT,
                title TEXT NOT NULL,
                description TEXT,
                due_date DATE NOT NULL,
                priority TEXT DEFAULT 'medium',
                category TEXT DEFAULT 'general',
                status TEXT DEFAULT 'pending',
                created_at DATETIME DEFAULT CURRENT_TIMESTAMP
            );";
            self::$pdo->exec($schema);
        }
    }
}

// Task Model
class Task
{
    private $pdo;
    private $jsonFile;
    private $useJson;

    public function __construct()
    {
        $this->pdo = Database::connect();
        $this->useJson = !Database::isAvailable();
        $this->jsonFile = __DIR__ . '/tasks.json';

        if ($this->useJson && !file_exists($this->jsonFile)) {
            file_put_contents($this->jsonFile, json_encode([]));
        }
    }

    private function getJsonTasks()
    {
        return json_decode(file_get_contents($this->jsonFile), true) ?: [];
    }

    private function saveJsonTasks($tasks)
    {
        file_put_contents($this->jsonFile, json_encode($tasks, JSON_PRETTY_PRINT));
    }

    public function getAll($filters = [])
    {
        if ($this->useJson) {
            $tasks = $this->getJsonTasks();
            return array_filter($tasks, function ($task) use ($filters) {
                if (!empty($filters['start_date']) && !empty($filters['end_date'])) {
                    if ($task['due_date'] < $filters['start_date'] || $task['due_date'] > $filters['end_date'])
                        return false;
                }
                if (!empty($filters['priority']) && $task['priority'] !== $filters['priority'])
                    return false;
                if (!empty($filters['category']) && $task['category'] !== $filters['category'])
                    return false;
                if (!empty($filters['status']) && $task['status'] !== $filters['status'])
                    return false;
                if (!empty($filters['search'])) {
                    if (stripos($task['title'], $filters['search']) === false && stripos($task['description'], $filters['search']) === false)
                        return false;
                }
                return true;
            });
        }

        $sql = "SELECT * FROM tasks WHERE 1=1";
        $params = [];

        if (!empty($filters['start_date']) && !empty($filters['end_date'])) {
            $sql .= " AND due_date BETWEEN :start_date AND :end_date";
            $params[':start_date'] = $filters['start_date'];
            $params[':end_date'] = $filters['end_date'];
        }

        if (!empty($filters['priority'])) {
            $sql .= " AND priority = :priority";
            $params[':priority'] = $filters['priority'];
        }

        if (!empty($filters['category'])) {
            $sql .= " AND category = :category";
            $params[':category'] = $filters['category'];
        }

        if (!empty($filters['status'])) {
            $sql .= " AND status = :status";
            $params[':status'] = $filters['status'];
        }

        if (!empty($filters['search'])) {
            $sql .= " AND (title LIKE :search OR description LIKE :search)";
            $params[':search'] = '%' . $filters['search'] . '%';
        }

        $sql .= " ORDER BY due_date ASC, priority DESC";

        $stmt = $this->pdo->prepare($sql);
        $stmt->execute($params);
        return $stmt->fetchAll();
    }

    public function save($data)
    {
        if ($this->useJson) {
            $tasks = $this->getJsonTasks();
            if (empty($data['id'])) {
                $data['id'] = uniqid();
                $data['created_at'] = date('Y-m-d H:i:s');
                $data['status'] = $data['status'] ?? 'pending';
                $tasks[] = $data;
            } else {
                foreach ($tasks as &$task) {
                    if ($task['id'] == $data['id']) {
                        $task = array_merge($task, $data);
                        break;
                    }
                }
            }
            $this->saveJsonTasks($tasks);
            return true;
        }

        if (empty($data['id'])) {
            $sql = "INSERT INTO tasks (title, description, due_date, priority, category, status) 
                    VALUES (:title, :description, :due_date, :priority, :category, :status)";
        } else {
            $sql = "UPDATE tasks SET 
                    title = :title, 
                    description = :description, 
                    due_date = :due_date, 
                    priority = :priority, 
                    category = :category, 
                    status = :status 
                    WHERE id = :id";
        }

        $stmt = $this->pdo->prepare($sql);

        $params = [
            ':title' => $data['title'],
            ':description' => $data['description'] ?? '',
            ':due_date' => $data['due_date'],
            ':priority' => $data['priority'] ?? 'medium',
            ':category' => $data['category'] ?? 'general',
            ':status' => $data['status'] ?? 'pending'
        ];

        if (!empty($data['id'])) {
            $params[':id'] = $data['id'];
        }

        return $stmt->execute($params);
    }

    public function delete($id)
    {
        if ($this->useJson) {
            $tasks = $this->getJsonTasks();
            $tasks = array_filter($tasks, function ($task) use ($id) {
                return $task['id'] != $id;
            });
            $this->saveJsonTasks(array_values($tasks));
            return true;
        }

        $stmt = $this->pdo->prepare("DELETE FROM tasks WHERE id = :id");
        return $stmt->execute([':id' => $id]);
    }

    public function toggleStatus($id)
    {
        if ($this->useJson) {
            $tasks = $this->getJsonTasks();
            foreach ($tasks as &$task) {
                if ($task['id'] == $id) {
                    $task['status'] = ($task['status'] === 'pending') ? 'completed' : 'pending';
                    break;
                }
            }
            $this->saveJsonTasks($tasks);
            return true;
        }

        $stmt = $this->pdo->prepare("SELECT status FROM tasks WHERE id = :id");
        $stmt->execute([':id' => $id]);
        $task = $stmt->fetch();
        
        if ($task) {
            $newStatus = ($task['status'] === 'pending') ? 'completed' : 'pending';
            $stmt = $this->pdo->prepare("UPDATE tasks SET status = :status WHERE id = :id");
            return $stmt->execute([':status' => $newStatus, ':id' => $id]);
        }
        return false;
    }
}

// API Handler
$action = $_GET['action'] ?? '';
$taskModel = new Task();

if ($action !== 'export_csv') {
    header('Content-Type: application/json');
}

try {
    switch ($action) {
        case 'get_tasks':
            echo json_encode(['success' => true, 'data' => $taskModel->getAll($_GET)]);
            break;

        case 'save_task':
            $data = json_decode(file_get_contents('php://input'), true);
            if ($taskModel->save($data)) {
                echo json_encode(['success' => true]);
            } else {
                throw new Exception("Failed to save task");
            }
            break;

        case 'delete_task':
            $data = json_decode(file_get_contents('php://input'), true);
            if ($taskModel->delete($data['id'])) {
                echo json_encode(['success' => true]);
            } else {
                throw new Exception("Failed to delete task");
            }
            break;

        case 'toggle_status':
            $data = json_decode(file_get_contents('php://input'), true);
            if ($taskModel->toggleStatus($data['id'])) {
                echo json_encode(['success' => true]);
            } else {
                throw new Exception("Failed to update status");
            }
            break;

        case 'export_csv':
            $tasks = $taskModel->getAll();
            header('Content-Type: text/csv');
            header('Content-Disposition: attachment; filename="tasks_export_' . date('Y-m-d') . '.csv"');
            
            $output = fopen('php://output', 'w');
            fputcsv($output, ['ID', 'Title', 'Description', 'Due Date', 'Priority', 'Category', 'Status', 'Created At']);
            
            foreach ($tasks as $task) {
                fputcsv($output, [
                    $task['id'],
                    $task['title'],
                    $task['description'],
                    $task['due_date'],
                    $task['priority'],
                    $task['category'],
                    $task['status'],
                    $task['created_at'] ?? ''
                ]);
            }
            fclose($output);
            break;

        case 'import_csv':
            if (!isset($_FILES['csv_file']) || $_FILES['csv_file']['error'] !== UPLOAD_ERR_OK) {
                throw new Exception('No valid file uploaded');
            }
            
            $handle = fopen($_FILES['csv_file']['tmp_name'], "r");
            if ($handle === FALSE) throw new Exception('Cannot open file');
            
            fgetcsv($handle); // Skip header
            $count = 0;
            
            while (($row = fgetcsv($handle)) !== FALSE) {
                if (count($row) < 7) continue;
                
                $data = [
                    'title' => $row[1],
                    'description' => $row[2],
                    'due_date' => $row[3],
                    'priority' => $row[4],
                    'category' => $row[5],
                    'status' => $row[6]
                ];
                
                if ($taskModel->save($data)) $count++;
            }
            
            fclose($handle);
            echo json_encode(['success' => true, 'message' => "Imported $count tasks"]);
            break;

        default:
            echo json_encode(['success' => false, 'error' => 'Invalid action']);
    }
} catch (Exception $e) {
    if ($action !== 'export_csv') {
        http_response_code(400);
        echo json_encode(['success' => false, 'error' => $e->getMessage()]);
    } else {
        echo "Error: " . $e->getMessage();
    }
}
