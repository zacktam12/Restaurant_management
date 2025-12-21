<?php
/**
 * API Keys Management
 * Admin interface for managing API keys for service consumers
 */

session_start();

// Check if user is logged in and is admin
if (!isset($_SESSION['logged_in']) || !$_SESSION['logged_in'] || 
    !isset($_SESSION['user']) || $_SESSION['user']['role'] != 'admin') {
    header('Location: ../login.php');
    exit();
}

require_once '../backend/config.php';
require_once '../backend/ApiKey.php';

$apiKeyManager = new ApiKey();

// Handle delete confirmation
if (isset($_GET['confirm_delete'])) {
    $apiKeyId = $_GET['confirm_delete'];
    
    // We'll handle the actual deletion through a GET request
}

// Handle form submissions
if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    if (isset($_POST['action'])) {
        switch ($_POST['action']) {
            case 'generate':
                $result = $apiKeyManager->generateApiKey(
                    $_POST['service_name'],
                    $_POST['consumer_group'],
                    $_POST['permissions']
                );
                
                if ($result['success']) {
                    $message = "API key generated successfully: " . $result['api_key'];
                    $messageType = "success";
                } else {
                    $message = "Failed to generate API key: " . $result['message'];
                    $messageType = "danger";
                }
                break;
                
            case 'toggle_status':
                $result = $apiKeyManager->updateApiKeyStatus($_POST['id'], $_POST['is_active']);
                if ($result['success']) {
                    $message = "API key status updated successfully";
                    $messageType = "success";
                } else {
                    $message = "Failed to update API key status: " . $result['message'];
                    $messageType = "danger";
                }
                break;
                
            case 'delete':
                $result = $apiKeyManager->deleteApiKey($_POST['id']);
                if ($result['success']) {
                    $message = "API key deleted successfully";
                    $messageType = "success";
                } else {
                    $message = "Failed to delete API key: " . $result['message'];
                    $messageType = "danger";
                }
                break;
        }
    }
} else if (isset($_GET['confirm_delete'])) {
    // Handle delete confirmation through GET parameters
    $apiKeyId = $_GET['confirm_delete'];
    
    // Show confirmation message
    $apiKey = $apiKeyManager->getApiKeyById($apiKeyId);
    if ($apiKey) {
        $message = 'Are you sure you want to delete API key "' . htmlspecialchars($apiKey['service_name']) . '"? This action cannot be undone.';
        $messageType = 'warning';
        $showDeleteConfirmation = true;
    }
} else if (isset($_GET['delete_confirmed']) && isset($_GET['id'])) {
    // Handle confirmed deletion
    $result = $apiKeyManager->deleteApiKey($_GET['id']);
    if ($result['success']) {
        $message = "API key deleted successfully";
        $messageType = "success";
    } else {
        $message = "Failed to delete API key: " . $result['message'];
        $messageType = "danger";
    }
}

// Get all API keys
$apiKeys = $apiKeyManager->getAllApiKeys();
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>API Keys Management - Restaurant Management System</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.7.2/font/bootstrap-icons.css" rel="stylesheet">
</head>
<body>
    <!-- Navigation -->
    <nav class="navbar navbar-expand-lg navbar-dark bg-dark">
        <div class="container">
            <a class="navbar-brand" href="index.php">
                <i class="bi bi-restaurant"></i> Restaurant Manager
            </a>
            <div class="navbar-nav ms-auto">
                <span class="navbar-text me-3">
                    Welcome, <?php echo htmlspecialchars($_SESSION['user']['name']); ?> 
                    <span class="badge bg-secondary"><?php echo ucfirst($_SESSION['user']['role']); ?></span>
                </span>
                <a class="btn btn-outline-light" href="../logout.php">Logout</a>
            </div>
        </div>
    </nav>

    <div class="container-fluid">
        <div class="row">
            <!-- Sidebar -->
            <nav class="col-md-3 col-lg-2 d-md-block bg-light sidebar collapse">
                <div class="position-sticky pt-3">
                    <ul class="nav flex-column">
                        <li class="nav-item">
                            <a class="nav-link" href="index.php">
                                <i class="bi bi-speedometer2"></i> Dashboard
                            </a>
                        </li>
                        <li class="nav-item">
                            <a class="nav-link" href="users.php">
                                <i class="bi bi-people"></i> User Management
                            </a>
                        </li>
                        <li class="nav-item">
                            <a class="nav-link" href="restaurants.php">
                                <i class="bi bi-shop"></i> Restaurants
                            </a>
                        </li>
                        <li class="nav-item">
                            <a class="nav-link" href="reservations.php">
                                <i class="bi bi-calendar-check"></i> Reservations
                            </a>
                        </li>
                        <li class="nav-item">
                            <a class="nav-link" href="services.php">
                                <i class="bi bi-gear"></i> External Services
                            </a>
                        </li>
                        <li class="nav-item">
                            <a class="nav-link active" href="api_keys.php">
                                <i class="bi bi-key"></i> API Keys
                            </a>
                        </li>
                        <li class="nav-item">
                            <a class="nav-link" href="reports.php">
                                <i class="bi bi-graph-up"></i> Reports
                            </a>
                        </li>
                    </ul>
                </div>
            </nav>

            <!-- Main Content -->
            <main class="col-md-9 ms-sm-auto col-lg-10 px-md-4">
                <div class="d-flex justify-content-between flex-wrap flex-md-nowrap align-items-center pt-3 pb-2 mb-3 border-bottom">
                    <h1 class="h2">API Keys Management</h1>
                </div>

                <!-- Message Display -->
                <?php if (isset($message)): ?>
                <div class="alert alert-<?php echo $messageType; ?> alert-dismissible fade show" role="alert">
                    <?php echo htmlspecialchars($message); ?>
                    <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
                </div>
                <?php endif; ?>
                
                <?php if (isset($showDeleteConfirmation) && $showDeleteConfirmation): ?>
                <div class="alert alert-warning" role="alert">
                    <h4 class="alert-heading">Confirm Deletion</h4>
                    <p><?php echo htmlspecialchars($message); ?></p>
                    <hr>
                    <div class="d-flex">
                        <a href="api_keys.php" class="btn btn-secondary me-2">Cancel</a>
                        <form method="GET" class="d-inline">
                            <input type="hidden" name="delete_confirmed" value="1">
                            <input type="hidden" name="id" value="<?php echo $_GET['confirm_delete']; ?>">
                            <button type="submit" class="btn btn-danger">Yes, Delete API Key</button>
                        </form>
                    </div>
                </div>
                <?php endif; ?>

                <!-- Generate API Key Form -->
                <div class="card mb-4">
                    <div class="card-header">
                        <h5 class="mb-0">Generate New API Key</h5>
                    </div>
                    <div class="card-body">
                        <form method="POST">
                            <input type="hidden" name="action" value="generate">
                            <div class="row g-3">
                                <div class="col-md-4">
                                    <label for="service_name" class="form-label">Service Name</label>
                                    <input type="text" class="form-control" id="service_name" name="service_name" required>
                                </div>
                                <div class="col-md-4">
                                    <label for="consumer_group" class="form-label">Consumer Group</label>
                                    <input type="text" class="form-control" id="consumer_group" name="consumer_group" required>
                                </div>
                                <div class="col-md-4">
                                    <label for="permissions" class="form-label">Permissions</label>
                                    <select class="form-select" id="permissions" name="permissions">
                                        <option value="read">Read Only</option>
                                        <option value="write">Write Only</option>
                                        <option value="read_write">Read & Write</option>
                                    </select>
                                </div>
                            </div>
                            <div class="mt-3">
                                <button type="submit" class="btn btn-primary">Generate API Key</button>
                            </div>
                        </form>
                    </div>
                </div>

                <!-- API Keys Table -->
                <div class="card">
                    <div class="card-header d-flex justify-content-between align-items-center">
                        <h5 class="mb-0">Active API Keys</h5>
                        <span class="badge bg-primary"><?php echo count($apiKeys); ?> Total Keys</span>
                    </div>
                    <div class="card-body">
                        <?php if (empty($apiKeys)): ?>
                        <div class="text-center py-5">
                            <i class="bi bi-key fs-1 text-muted"></i>
                            <h3 class="mt-3">No API keys found</h3>
                            <p class="text-muted">Generate your first API key using the form above.</p>
                        </div>
                        <?php else: ?>
                        <div class="table-responsive">
                            <table class="table table-striped table-hover">
                                <thead>
                                    <tr>
                                        <th>Service Name</th>
                                        <th>Consumer Group</th>
                                        <th>Permissions</th>
                                        <th>Status</th>
                                        <th>Created</th>
                                        <th>Last Used</th>
                                        <th>Usage Count</th>
                                        <th>Actions</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <?php foreach ($apiKeys as $key): ?>
                                    <tr>
                                        <td><?php echo htmlspecialchars($key['service_name']); ?></td>
                                        <td><?php echo htmlspecialchars($key['consumer_group']); ?></td>
                                        <td>
                                            <span class="badge bg-<?php 
                                                echo $key['permissions'] == 'read' ? 'info' : 
                                                     ($key['permissions'] == 'write' ? 'warning' : 'primary'); ?>">
                                                <?php echo ucfirst(str_replace('_', ' & ', $key['permissions'])); ?>
                                            </span>
                                        </td>
                                        <td>
                                            <span class="badge bg-<?php echo $key['is_active'] ? 'success' : 'danger'; ?>">
                                                <?php echo $key['is_active'] ? 'Active' : 'Inactive'; ?>
                                            </span>
                                        </td>
                                        <td><?php echo date('M j, Y', strtotime($key['created_at'])); ?></td>
                                        <td><?php echo $key['last_used'] ? date('M j, Y H:i', strtotime($key['last_used'])) : 'Never'; ?></td>
                                        <td><?php echo $key['usage_count']; ?></td>
                                        <td>
                                            <div class="btn-group btn-group-sm" role="group">
                                                <form method="POST" class="d-inline">
                                                    <input type="hidden" name="action" value="toggle_status">
                                                    <input type="hidden" name="id" value="<?php echo $key['id']; ?>">
                                                    <input type="hidden" name="is_active" value="<?php echo $key['is_active'] ? 0 : 1; ?>">
                                                    <button type="submit" class="btn btn-<?php echo $key['is_active'] ? 'warning' : 'success'; ?> btn-sm" 
                                                            title="<?php echo $key['is_active'] ? 'Deactivate' : 'Activate'; ?>">
                                                        <i class="bi bi-<?php echo $key['is_active'] ? 'pause' : 'play'; ?>"></i>
                                                    </button>
                                                </form>
                                                <form method="GET" class="d-inline">
                                                    <input type="hidden" name="confirm_delete" value="<?php echo $key['id']; ?>">
                                                    <button type="submit" class="btn btn-danger btn-sm" title="Delete">
                                                        <i class="bi bi-trash"></i>
                                                    </button>
                                                </form>
                                            </div>
                                        </td>
                                    </tr>
                                    <?php endforeach; ?>
                                </tbody>
                            </table>
                        </div>
                        <?php endif; ?>
                    </div>
                </div>

                <!-- API Key Usage Instructions -->
                <div class="card mt-4">
                    <div class="card-header">
                        <h5 class="mb-0">API Key Usage Instructions</h5>
                    </div>
                    <div class="card-body">
                        <h6>Authentication</h6>
                        <p>To authenticate API requests, include your API key in the Authorization header:</p>
                        <pre class="bg-light p-3">Authorization: Bearer YOUR_API_KEY_HERE</pre>
                        
                        <h6>Permissions</h6>
                        <ul>
                            <li><strong>Read Only</strong>: GET requests only</li>
                            <li><strong>Write Only</strong>: POST, PUT, DELETE requests only</li>
                            <li><strong>Read & Write</strong>: All request types allowed</li>
                        </ul>
                        
                        <h6>Rate Limiting</h6>
                        <p>API requests are limited to 1000 requests per hour per API key.</p>
                    </div>
                </div>
            </main>
        </div>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>

<?php
$apiKeyManager->close();
?>