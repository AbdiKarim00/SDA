<?php
require_once '../includes/db.php';

$db = Database::getInstance();

// Handle actions
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $action = $_POST['action'] ?? '';
    $request_id = $_POST['request_id'] ?? '';
    $notes = $_POST['notes'] ?? '';
    
    try {
        switch ($action) {
            case 'approve':
                // Update request status
                $db->query("
                    UPDATE access_requests
                    SET status = 'approved',
                        processed_at = CURRENT_TIMESTAMP,
                        processed_by = :admin_id,
                        notes = :notes
                    WHERE id = :request_id
                ", [
                    'admin_id' => 1, // Mock admin ID
                    'notes' => $notes,
                    'request_id' => $request_id
                ]);
                
                // Get request details
                $request = $db->query("
                    SELECT * FROM access_requests WHERE id = :id
                ", ['id' => $request_id])->fetch();
                
                // Create user account
                $db->query("
                    INSERT INTO users (
                        username, email, personal_no, role, department, is_active, created_at
                    ) VALUES (
                        :username, :email, :personal_no, 'user', :department, true, CURRENT_TIMESTAMP
                    )
                ", [
                    'username' => $request['name'],
                    'email' => $request['email'],
                    'personal_no' => $request['personal_no'],
                    'department' => $request['department']
                ]);
                
                // Notify user
                $db->query("
                    INSERT INTO notifications (
                        user_id, title, message, type
                    ) SELECT 
                        user_id,
                        'Access Request Approved',
                        'Your access request has been approved. You can now log in with your email address.',
                        'access_request'
                    FROM users 
                    WHERE email = :email
                ", ['email' => $request['email']]);
                break;
                
            case 'reject':
                $db->query("
                    UPDATE access_requests
                    SET status = 'rejected',
                        processed_at = CURRENT_TIMESTAMP,
                        processed_by = :admin_id,
                        notes = :notes
                    WHERE id = :request_id
                ", [
                    'admin_id' => 1, // Mock admin ID
                    'notes' => $notes,
                    'request_id' => $request_id
                ]);
                
                // Get request details
                $request = $db->query("
                    SELECT * FROM access_requests WHERE id = :id
                ", ['id' => $request_id])->fetch();
                
                // Notify user
                $db->query("
                    INSERT INTO notifications (
                        user_id, title, message, type
                    ) SELECT 
                        user_id,
                        'Access Request Rejected',
                        'Your access request has been rejected. Reason: ' || :notes,
                        'access_request'
                    FROM users 
                    WHERE email = :email
                ", [
                    'notes' => $notes,
                    'email' => $request['email']
                ]);
                break;
        }
    } catch (Exception $e) {
        error_log("Access request processing error: " . $e->getMessage());
        $error = 'An error occurred while processing the request.';
    }
}

// Get access requests
$requests = $db->query("
    SELECT ar.*, u.username as processed_by_name
    FROM access_requests ar
    LEFT JOIN users u ON ar.processed_by = u.user_id
    ORDER BY 
        CASE WHEN ar.status = 'pending' THEN 0 ELSE 1 END,
        ar.created_at DESC
")->fetchAll();
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Access Requests - SDATIMS</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.7.2/font/bootstrap-icons.css" rel="stylesheet">
    <style>
        .page-header {
            background: linear-gradient(135deg, #1a237e, #0d47a1);
            color: white;
            padding: 20px 0;
            margin-bottom: 30px;
        }
        .btn-approve {
            background: #28a745;
            color: white;
        }
        .btn-reject {
            background: #dc3545;
            color: white;
        }
        .status-badge {
            font-size: 0.8rem;
            padding: 0.35em 0.65em;
        }
        .status-pending {
            background-color: #ffc107;
            color: #000;
        }
        .status-approved {
            background-color: #28a745;
            color: white;
        }
        .status-rejected {
            background-color: #dc3545;
            color: white;
        }
    </style>
</head>
<body>
    <?php include '../includes/admin_nav.php'; ?>
    
    <div class="page-header">
        <div class="container">
            <h1>Access Requests</h1>
        </div>
    </div>
    
    <div class="container">
        <?php if (isset($error)): ?>
            <div class="alert alert-danger" role="alert">
                <i class="bi bi-exclamation-triangle-fill me-2"></i>
                <?php echo htmlspecialchars($error); ?>
            </div>
        <?php endif; ?>
        
        <div class="card">
            <div class="card-body">
                <div class="table-responsive">
                    <table class="table">
                        <thead>
                            <tr>
                                <th>Personal No.</th>
                                <th>Name</th>
                                <th>Email</th>
                                <th>Department</th>
                                <th>Reason</th>
                                <th>Status</th>
                                <th>Requested</th>
                                <th>Processed</th>
                                <th>Actions</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php if (empty($requests)): ?>
                                <tr>
                                    <td colspan="9" class="text-center">No access requests found</td>
                                </tr>
                            <?php else: ?>
                                <?php foreach ($requests as $request): ?>
                                    <tr>
                                        <td><?php echo htmlspecialchars($request['personal_no']); ?></td>
                                        <td><?php echo htmlspecialchars($request['name']); ?></td>
                                        <td><?php echo htmlspecialchars($request['email']); ?></td>
                                        <td><?php echo htmlspecialchars($request['department']); ?></td>
                                        <td><?php echo htmlspecialchars($request['reason']); ?></td>
                                        <td>
                                            <span class="badge status-badge status-<?php echo $request['status']; ?>">
                                                <?php echo ucfirst($request['status']); ?>
                                            </span>
                                        </td>
                                        <td><?php echo date('Y-m-d H:i', strtotime($request['created_at'])); ?></td>
                                        <td>
                                            <?php if ($request['processed_at']): ?>
                                                <?php echo date('Y-m-d H:i', strtotime($request['processed_at'])); ?>
                                                by <?php echo htmlspecialchars($request['processed_by_name']); ?>
                                            <?php else: ?>
                                                -
                                            <?php endif; ?>
                                        </td>
                                        <td>
                                            <?php if ($request['status'] === 'pending'): ?>
                                                <button type="button" class="btn btn-sm btn-approve" 
                                                        data-bs-toggle="modal" 
                                                        data-bs-target="#approveModal<?php echo $request['id']; ?>">
                                                    <i class="bi bi-check-lg"></i> Approve
                                                </button>
                                                <button type="button" class="btn btn-sm btn-reject"
                                                        data-bs-toggle="modal" 
                                                        data-bs-target="#rejectModal<?php echo $request['id']; ?>">
                                                    <i class="bi bi-x-lg"></i> Reject
                                                </button>
                                                
                                                <!-- Approve Modal -->
                                                <div class="modal fade" id="approveModal<?php echo $request['id']; ?>" tabindex="-1">
                                                    <div class="modal-dialog">
                                                        <div class="modal-content">
                                                            <div class="modal-header">
                                                                <h5 class="modal-title">Approve Access Request</h5>
                                                                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                                                            </div>
                                                            <form method="POST" action="">
                                                                <div class="modal-body">
                                                                    <input type="hidden" name="request_id" value="<?php echo $request['id']; ?>">
                                                                    <input type="hidden" name="action" value="approve">
                                                                    <div class="mb-3">
                                                                        <label for="notes<?php echo $request['id']; ?>" class="form-label">Notes (optional)</label>
                                                                        <textarea class="form-control" id="notes<?php echo $request['id']; ?>" name="notes" rows="3"></textarea>
                                                                    </div>
                                                                </div>
                                                                <div class="modal-footer">
                                                                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                                                                    <button type="submit" class="btn btn-approve">Approve Request</button>
                                                                </div>
                                                            </form>
                                                        </div>
                                                    </div>
                                                </div>
                                                
                                                <!-- Reject Modal -->
                                                <div class="modal fade" id="rejectModal<?php echo $request['id']; ?>" tabindex="-1">
                                                    <div class="modal-dialog">
                                                        <div class="modal-content">
                                                            <div class="modal-header">
                                                                <h5 class="modal-title">Reject Access Request</h5>
                                                                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                                                            </div>
                                                            <form method="POST" action="">
                                                                <div class="modal-body">
                                                                    <input type="hidden" name="request_id" value="<?php echo $request['id']; ?>">
                                                                    <input type="hidden" name="action" value="reject">
                                                                    <div class="mb-3">
                                                                        <label for="reject_notes<?php echo $request['id']; ?>" class="form-label">Reason for Rejection</label>
                                                                        <textarea class="form-control" id="reject_notes<?php echo $request['id']; ?>" name="notes" rows="3" required></textarea>
                                                                    </div>
                                                                </div>
                                                                <div class="modal-footer">
                                                                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                                                                    <button type="submit" class="btn btn-reject">Reject Request</button>
                                                                </div>
                                                            </form>
                                                        </div>
                                                    </div>
                                                </div>
                                            <?php endif; ?>
                                        </td>
                                    </tr>
                                <?php endforeach; ?>
                            <?php endif; ?>
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>
    
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html> 