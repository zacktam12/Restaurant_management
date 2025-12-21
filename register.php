<?php
/**
 * Registration Page
 * User registration interface
 */

session_start();

// Redirect if already logged in
if (isset($_SESSION['logged_in']) && $_SESSION['logged_in']) {
    if ($_SESSION['user']['role'] == 'admin' || $_SESSION['user']['role'] == 'manager') {
        header('Location: admin/index.php');
    } else {
        header('Location: tourist/index.php');
    }
    exit();
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Register - Restaurant Management System</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.7.2/font/bootstrap-icons.css" rel="stylesheet">
    <style>
        body {
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            min-height: 100vh;
            display: flex;
            align-items: center;
        }
        .register-card {
            border-radius: 15px;
            box-shadow: 0 15px 35px rgba(0, 0, 0, 0.1);
        }
    </style>
</head>
<body>
    <div class="container">
        <div class="row justify-content-center">
            <div class="col-md-6 col-lg-5">
                <div class="card register-card">
                    <div class="card-body p-5">
                        <div class="text-center mb-4">
                            <i class="bi bi-person-plus fs-1 text-primary"></i>
                            <h2 class="mt-3">Create Account</h2>
                            <p class="text-muted">Sign up for a new account</p>
                        </div>

                        <form id="registerForm">
                            <div class="mb-3">
                                <label for="name" class="form-label">Full Name</label>
                                <input type="text" class="form-control" id="name" name="name" required>
                            </div>
                            <div class="mb-3">
                                <label for="email" class="form-label">Email</label>
                                <input type="email" class="form-control" id="email" name="email" required>
                            </div>
                            <div class="mb-3">
                                <label for="password" class="form-label">Password</label>
                                <input type="password" class="form-control" id="password" name="password" required>
                            </div>
                            <div class="mb-3">
                                <label for="role" class="form-label">Role</label>
                                <select class="form-select" id="role" name="role" required>
                                    <option value="">Select your role</option>
                                    <option value="customer">Customer</option>
                                    <option value="tourist">Tourist</option>
                                    <option value="tour_guide">Tour Guide</option>
                                </select>
                            </div>
                            <div class="mb-3" id="professionalDetailsField" style="display: none;">
                                <label for="professional_details" class="form-label">Professional Details</label>
                                <textarea class="form-control" id="professional_details" name="professional_details" rows="3" placeholder="Enter your professional background and experience"></textarea>
                            </div>
                            <div class="mb-3">
                                <label for="phone" class="form-label">Phone Number</label>
                                <input type="text" class="form-control" id="phone" name="phone">
                            </div>
                            <div class="d-grid">
                                <button type="submit" class="btn btn-primary">Register</button>
                            </div>
                        </form>

                        <div class="text-center mt-4">
                            <p class="mb-0">Already have an account? <a href="login.php">Login</a></p>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/js/bootstrap.bundle.min.js"></script>
    <script>
        // Show/hide professional details field based on role selection
        document.getElementById('role').addEventListener('change', function() {
            const professionalDetailsField = document.getElementById('professionalDetailsField');
            if (this.value === 'tour_guide') {
                professionalDetailsField.style.display = 'block';
            } else {
                professionalDetailsField.style.display = 'none';
            }
        });

        document.getElementById('registerForm').addEventListener('submit', function(e) {
            e.preventDefault();
            
            const formData = new FormData(this);
            
            fetch('api/auth.php?action=register', {
                method: 'POST',
                body: JSON.stringify({
                    name: formData.get('name'),
                    email: formData.get('email'),
                    password: formData.get('password'),
                    role: formData.get('role'),
                    phone: formData.get('phone'),
                    professional_details: formData.get('professional_details')
                })
            })
            .then(response => response.json())
            .then(data => {
                if (data.success) {
                    alert('Registration successful! You can now login.');
                    window.location.href = 'login.php';
                } else {
                    alert('Registration failed: ' + data.message);
                }
            })
            .catch(error => {
                alert('Error during registration: ' + error.message);
            });
        });
    </script>
</body>
</html>