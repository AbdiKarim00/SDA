<?php
require_once '../includes/db.php';

$db = Database::getInstance();

// Handle actions
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $action = $_POST['action'] ?? '';
    $reset_id = $_POST['reset_id'] ?? '';
    
    try {
        switch ($action) {
            case 'approve':
                // Generate temporary password
                $temp_password = bin2hex(random_bytes(4));
                $hashed_password = password_hash($temp_password, PASSWORD_DEFAULT);
                
                // Update user's password
                $db->query("
                    UPDATE users u
                    JOIN password_resets pr ON u.id = pr.user_id
                    SET u.password_hash = :password,
                        u.is_temporary_password = TRUE
                    WHERE pr.id = :reset_id
                ", [
                    'password' => $hashed_password,
                    'reset_id' => $reset_id
                ]);
                
                // Update reset request status
                $db->query("
                    UPDATE password_resets
                    SET status = 'approved',
                        processed_at = CURRENT_TIMESTAMP
                    WHERE id = :reset_id
                ", ['reset_id' => $reset_id]);
                
                // Notify user
                $db->query("
                    INSERT INTO notifications (
                        user_id, title, message, type
                    ) SELECT 
                        pr.user_id,
                        'Password Reset Approved',
                        CONCAT('Your password has been reset. Your temporary password is: ', :temp_password, '. You will be required to change it upon next login.'),
                        'password_reset'
                    FROM password_resets pr
                    WHERE pr.id = :reset_id
                ", [
                    'temp_password' => $temp_password,
                    'reset_id' => $reset_id
                ]);
                break;
                
            case 'reject':
                $db->query("
                    UPDATE password_resets
                    SET status = 'rejected',
                        processed_at = CURRENT_TIMESTAMP
                    WHERE id = :reset_id
                ", ['reset_id' => $reset_id]);
                
                // Notify user
                $db->query("
                    INSERT INTO notifications (
                        user_id, title, message, type
                    ) SELECT 
                        pr.user_id,
                        'Password Reset Rejected',
                        'Your password reset request has been rejected. Please contact your administrator.',
                        'password_reset'
                    FROM password_resets pr
                    WHERE pr.id = :reset_id
                ", ['reset_id' => $reset_id]);
                break;
        }
    } catch (Exception $e) {
        $error = 'An error occurred while processing the request.';
    }
}

// Get pending reset requests
$reset_requests = $db->query("
    SELECT pr.*, u.username, u.personal_no
    FROM password_resets pr
    JOIN users u ON pr.user_id = u.user_id
    WHERE pr.status = 'pending'
    ORDER BY pr.created_at DESC
")->fetchAll();
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Password Reset Requests - SDATIMS</title>
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
    </style>
</head>
<body>
    <?php include '../includes/admin_nav.php'; ?>
    
    <div class="page-header">
        <div class="container">
            <h1>Password Reset Requests</h1>
        </div>
    </div>
    
    <div class="container">
        <?php if (isset($error)): ?>
            <div class="alert alert-danger" role="alert">
                <?php echo htmlspecialchars($error); ?>
            </div>
        <?php endif; ?>
        
        <div class="card">
            <div class="card-body">
                <div class="table-responsive">
                    <table class="table">
                        <thead>
                            <tr>
                                <th>User</th>
                                <th>Personal Number</th>
                                <th>Role</th>
                                <th>Requested At</th>
                                <th>Actions</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php if (empty($reset_requests)): ?>
                                <tr>
                                    <td colspan="5" class="text-center">No pending password reset requests</td>
                                </tr>
                            <?php else: ?>
                                <?php foreach ($reset_requests as $request): ?>
                                    <tr>
                                        <td><?php echo htmlspecialchars($request['username']); ?></td>
                                        <td><?php echo htmlspecialchars($request['personal_no']); ?></td>
                                        <td><?php echo htmlspecialchars($request['role']); ?></td>
                                        <td><?php echo date('Y-m-d H:i', strtotime($request['created_at'])); ?></td>
                                        <td>
                                            <form method="POST" action="" class="d-inline">
                                                <input type="hidden" name="reset_id" value="<?php echo $request['id']; ?>">
                                                <button type="submit" name="action" value="approve" class="btn btn-sm btn-approve">
                                                    <i class="bi bi-check-lg"></i> Approve
                                                </button>
                                                <button type="submit" name="action" value="reject" class="btn btn-sm btn-reject">
                                                    <i class="bi bi-x-lg"></i> Reject
                                                </button>
                                            </form>
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