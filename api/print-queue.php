<?php
/**
 * Print Queue API
 * Manage print job queue for remote printing
 */

require_once __DIR__ . '/../functions.php';

header('Content-Type: application/json');
header('Access-Control-Allow-Origin: *');
header('Access-Control-Allow-Methods: GET, POST');
header('Access-Control-Allow-Headers: Content-Type');

if ($_SERVER['REQUEST_METHOD'] === 'OPTIONS') {
    exit(0);
}

$pdo = getDB();
$method = $_SERVER['REQUEST_METHOD'];
$action = $_GET['action'] ?? '';

try {
    switch ($action) {
        case 'add':
            // Add new print job to queue (called from phone)
            requireLogin();
            $user = getCurrentUser();

            $input = json_decode(file_get_contents('php://input'), true);

            if (empty($input['job_data'])) {
                jsonResponse(['success' => false, 'message' => 'Job data required'], 400);
            }

            $stmt = $pdo->prepare("
                INSERT INTO print_queue (job_type, job_data, created_by)
                VALUES (?, ?, ?)
            ");
            $stmt->execute([
                $input['job_type'] ?? 'surat_izin',
                json_encode($input['job_data']),
                $user['id']
            ]);

            $jobId = $pdo->lastInsertId();

            jsonResponse([
                'success' => true,
                'job_id' => $jobId,
                'message' => 'Print job added to queue'
            ]);
            break;

        case 'pending':
            // Get pending print jobs (called by print server)
            $stmt = $pdo->prepare("
                SELECT id, job_type, job_data, created_at
                FROM print_queue
                WHERE status = 'pending'
                ORDER BY created_at ASC
                LIMIT 10
            ");
            $stmt->execute();
            $jobs = $stmt->fetchAll();

            // Decode job_data JSON
            foreach ($jobs as &$job) {
                $job['job_data'] = json_decode($job['job_data'], true);
            }

            jsonResponse(['success' => true, 'jobs' => $jobs]);
            break;

        case 'complete':
            // Mark job as completed
            $jobId = $_GET['id'] ?? 0;

            $stmt = $pdo->prepare("
                UPDATE print_queue 
                SET status = 'completed', processed_at = NOW()
                WHERE id = ?
            ");
            $stmt->execute([$jobId]);

            jsonResponse(['success' => true, 'message' => 'Job marked as completed']);
            break;

        case 'fail':
            // Mark job as failed
            $jobId = $_GET['id'] ?? 0;
            $error = $_GET['error'] ?? 'Unknown error';

            $stmt = $pdo->prepare("
                UPDATE print_queue 
                SET status = 'failed', processed_at = NOW(), error_message = ?
                WHERE id = ?
            ");
            $stmt->execute([$error, $jobId]);

            jsonResponse(['success' => true, 'message' => 'Job marked as failed']);
            break;

        case 'processing':
            // Mark job as processing
            $jobId = $_GET['id'] ?? 0;

            $stmt = $pdo->prepare("
                UPDATE print_queue 
                SET status = 'processing'
                WHERE id = ? AND status = 'pending'
            ");
            $stmt->execute([$jobId]);

            jsonResponse(['success' => true]);
            break;

        case 'stats':
            // Get queue statistics
            $stmt = $pdo->query("
                SELECT 
                    COUNT(CASE WHEN status = 'pending' THEN 1 END) as pending,
                    COUNT(CASE WHEN status = 'processing' THEN 1 END) as processing,
                    COUNT(CASE WHEN status = 'completed' AND DATE(processed_at) = CURDATE() THEN 1 END) as completed_today,
                    COUNT(CASE WHEN status = 'failed' AND DATE(processed_at) = CURDATE() THEN 1 END) as failed_today
                FROM print_queue
            ");
            $stats = $stmt->fetch();

            jsonResponse(['success' => true, 'stats' => $stats]);
            break;

        default:
            jsonResponse(['success' => false, 'message' => 'Invalid action'], 400);
    }

} catch (Exception $e) {
    error_log("Print Queue API Error: " . $e->getMessage());
    jsonResponse(['success' => false, 'message' => 'Server error'], 500);
}
