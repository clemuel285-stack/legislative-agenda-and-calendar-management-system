<?php
declare(strict_types=1);
require_once __DIR__ . '/includes/auth.php';

if (isAuthenticated()) {
    redirect(appUrl('dashboard.php'));
}

$flash = getFlash();
$expired = isset($_GET['expired']);
?>
<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<title>Sign In | LACMS</title>
<link rel="icon" type="image/png" href="<?= e(appUrl('assets/images/manila.png?v=' . time())) ?>">
<link rel="shortcut icon" href="<?= e(appUrl('favicon.ico?v=' . time())) ?>">
<link rel="apple-touch-icon" href="<?= e(appUrl('assets/images/manila.png?v=' . time())) ?>">
<link rel="stylesheet" href="<?= e(vendorAsset('bootstrap/bootstrap.min.css','https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css')) ?>">
<link rel="stylesheet" href="<?= e(vendorAsset('bootstrap-icons/bootstrap-icons.css','https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.min.css')) ?>">
<style>
    :root {
        --primary-blue: #0f2137;
        --primary-blue-dark: #071426;
        --primary-blue-light: #1a3a5c;
        --primary-yellow: #b8860b;
        --primary-yellow-light: #d97706;
        --primary-white: #FFFFFF;
        --primary-gray: #F3F4F6;
    }
    * { margin: 0; padding: 0; box-sizing: border-box; }
    html, body { height: 100%; overflow: hidden; font-family: Inter, system-ui, sans-serif; }
    .login-wrapper { display: flex; height: 100vh; width: 100%; background: var(--primary-white); }
    .brand-side { flex: 1.1; background: linear-gradient(135deg, #071426 0%, #0f2137 55%, #1a3a5c 100%); display: flex; flex-direction: column; justify-content: center; align-items: center; padding: 3rem; position: relative; overflow: hidden; min-height: 100vh; }
    .brand-side::before { content: ''; position: absolute; width: 500px; height: 500px; background: radial-gradient(circle, rgba(184, 134, 11, 0.18) 0%, transparent 70%); top: -150px; right: -150px; border-radius: 50%; }
    .brand-side::after { content: ''; position: absolute; width: 400px; height: 400px; background: radial-gradient(circle, rgba(184, 134, 11, 0.12) 0%, transparent 70%); bottom: -100px; left: -100px; border-radius: 50%; }
    .circle-decoration { position: absolute; border-radius: 50%; border: 2px solid rgba(184, 134, 11, 0.15); pointer-events: none; }
    .circle-1 { width: 300px; height: 300px; top: 10%; right: 5%; opacity: 0.4; }
    .circle-2 { width: 200px; height: 200px; bottom: 15%; left: 10%; opacity: 0.4; }
    .circle-3 { width: 150px; height: 150px; top: 50%; left: 50%; transform: translate(-50%, -50%); opacity: 0.3; }
    .brand-content { position: relative; z-index: 2; text-align: center; max-width: 520px; color: var(--primary-white); }
    .brand-logo { width: 220px; height: 220px; display: inline-flex; align-items: center; justify-content: center; margin-bottom: 1.5rem; }
    .brand-logo img { width: 220px !important; height: 220px !important; max-width: 220px !important; max-height: 220px !important; object-fit: contain; filter: drop-shadow(0 12px 30px rgba(0, 0, 0, 0.55)); }
    .brand-title { font-size: 2.5rem; font-weight: 850; margin-bottom: 0.5rem; letter-spacing: -1px; color: var(--primary-white); text-shadow: 0 2px 20px rgba(0, 0, 0, 0.2); }
    .brand-title .highlight { color: var(--primary-yellow); }
    .brand-description { font-size: 1.05rem; opacity: 0.92; line-height: 1.6; margin-bottom: 2rem; color: rgba(255, 255, 255, 0.9); }
    .brand-features { display: grid; grid-template-columns: 1fr 1fr; gap: 1rem; text-align: left; margin-top: 1.5rem; }
    .feature-item { background: rgba(255, 255, 255, 0.08); backdrop-filter: blur(10px); padding: 1rem; border-radius: 12px; border: 1px solid rgba(255, 255, 255, 0.12); transition: all 0.3s ease; display: flex; align-items: center; gap: 0.75rem; }
    .feature-item:hover { background: rgba(255, 255, 255, 0.18); transform: translateY(-2px); }
    .feature-item i { color: var(--primary-yellow); font-size: 1.25rem; }
    .feature-item span { font-size: 0.85rem; font-weight: 600; color: var(--primary-white); }
    .login-side { flex: 1; display: flex; align-items: center; justify-content: center; padding: 2rem; background: var(--primary-white); position: relative; min-height: 100vh; }
    .login-side::before { content: ''; position: absolute; top: 0; left: 0; width: 6px; height: 100%; background: linear-gradient(180deg, var(--primary-yellow), var(--primary-yellow-light)); box-shadow: 0 0 30px rgba(184, 134, 11, 0.4); }
    .login-container { width: 100%; max-width: 440px; padding: 0.5rem; position: relative; z-index: 1; animation: slideInRight 0.6s ease-out; }
    @keyframes slideInRight { from { opacity: 0; transform: translateX(30px); } to { opacity: 1; transform: translateX(0); } }
    .login-header { margin-bottom: 2rem; }
    .login-greeting { font-size: 1.85rem; font-weight: 850; color: #071426; margin-bottom: 0.35rem; display: flex; align-items: center; gap: 0.5rem; }
    .login-greeting i { color: var(--primary-yellow); font-size: 1.6rem; }
    .login-subtitle { color: #6B7280; font-size: 0.95rem; }
    .alert-custom { border: none; border-radius: 12px; padding: 0.75rem 1rem; margin-bottom: 1rem; display: flex; align-items: center; gap: 0.75rem; font-weight: 500; font-size: 0.9rem; border-left: 4px solid; }
    .alert-custom.alert-warning { background: #FFFBEB; color: #92400E; border-left-color: var(--primary-yellow); }
    .alert-custom.alert-danger { background: #FEF2F2; color: #991B1B; border-left-color: #EF4444; }
    .alert-custom.alert-success { background: #F0FDF4; color: #065F46; border-left-color: #10B981; }
    .form-group { margin-bottom: 1.25rem; }
    .form-label { font-weight: 650; color: #071426; font-size: 0.85rem; margin-bottom: 0.5rem; display: flex; align-items: center; gap: 0.5rem; }
    .input-group-modern { position: relative; }
    .input-group-modern .input-icon { position: absolute; left: 1rem; top: 50%; transform: translateY(-50%); color: #9CA3AF; z-index: 10; font-size: 1rem; transition: color 0.3s ease; pointer-events: none; }
    .input-group-modern .form-control { padding: 0.75rem 1rem 0.75rem 3rem; border-radius: 12px; border: 2px solid #E5E7EB; background: #FAFAFA; height: 3.25rem; font-size: 0.95rem; transition: all 0.3s ease; color: #1F2937; }
    .input-group-modern .form-control:focus { border-color: #071426; background: #FFFFFF; box-shadow: 0 0 0 4px rgba(7, 20, 38, 0.12); outline: none; }
    .input-group-modern .form-control:focus ~ .input-icon { color: var(--primary-yellow); }
    .btn-login { background: linear-gradient(135deg, #071426 0%, #1a3a5c 100%); border: none; border-radius: 12px; padding: 0.85rem; font-weight: 700; font-size: 1rem; color: white; height: 3.25rem; display: flex; align-items: center; justify-content: center; gap: 0.75rem; width: 100%; cursor: pointer; transition: all 0.3s ease; position: relative; overflow: hidden; box-shadow: 0 4px 15px rgba(7, 20, 38, 0.35); }
    .btn-login:hover { background: linear-gradient(135deg, #0f2137 0%, #b8860b 100%); transform: translateY(-2px); box-shadow: 0 8px 30px rgba(184, 134, 11, 0.4); color: white; }
    .login-footer { text-align: center; margin-top: 1.5rem; padding-top: 1.5rem; border-top: 1px solid #F3F4F6; color: #9CA3AF; font-size: 0.8rem; }
    .divider { display: flex; align-items: center; margin: 1.5rem 0; gap: 1rem; color: #9CA3AF; font-size: 0.8rem; font-weight: 500; }
    .divider::before, .divider::after { content: ''; flex: 1; height: 1px; background: #E5E7EB; }
    @media (max-width: 992px) { .login-wrapper { flex-direction: column; height: auto; } .brand-side { min-height: 45vh; padding: 2rem; } .brand-logo img { width: 100px !important; height: 100px !important; } .login-side { min-height: 55vh; padding: 2rem 1.5rem; } .login-side::before { top: 0; left: 0; width: 100%; height: 6px; } }
</style>
</head>
<body>
<div class="login-wrapper">
    <div class="brand-side">
        <div class="circle-decoration circle-1"></div>
        <div class="circle-decoration circle-2"></div>
        <div class="circle-decoration circle-3"></div>
        
        <div class="brand-content">
            <div class="brand-logo">
                <img src="<?= e(appUrl('assets/images/manila.png')) ?>" alt="LACMS Logo">
            </div>
            
            <h1 class="brand-title">
                LACMS
            </h1>
            
            <p class="brand-description">
                Legislative Agenda and Calendar Management System
            </p>

            <div class="brand-features">
                <div class="feature-item">
                    <i class="bi bi-list-check"></i>
                    <span>Agenda Management</span>
                </div>
                <div class="feature-item">
                    <i class="bi bi-calendar-week"></i>
                    <span>Meeting Scheduling</span>
                </div>
                <div class="feature-item">
                    <i class="bi bi-stars"></i>
                    <span>AI Email Reminders</span>
                </div>
                <div class="feature-item">
                    <i class="bi bi-alarm"></i>
                    <span>Deadline Tracking</span>
                </div>
            </div>
        </div>
    </div>

    <div class="login-side">
        <div class="login-container">
            <div class="login-header">
                <h2 class="login-greeting">
                    <i class="bi bi-box-arrow-in-right"></i>
                    Welcome Back
                </h2>
                <p class="login-subtitle">Sign in to access your LACMS account</p>
            </div>

            <?php if ($expired): ?>
                <div class="alert-custom alert-warning">
                    <i class="bi bi-clock-history"></i>
                    <span>Your session expired after eight hours of inactivity. Please log in again.</span>
                </div>
            <?php endif; ?>

            <?php if ($flash): ?>
                <div class="alert-custom alert-<?= e($flash['type']) ?>">
                    <i class="bi <?= $flash['type'] === 'success' ? 'bi-check-circle' : ($flash['type'] === 'warning' ? 'bi-exclamation-triangle' : 'bi-x-circle') ?>"></i>
                    <span><?= e($flash['message']) ?></span>
                </div>
            <?php endif; ?>

            <form method="post" action="<?= e(appUrl('auth/process_login.php')) ?>">
                <input type="hidden" name="csrf_token" value="<?= e(csrfToken()) ?>">

                <div class="form-group">
                    <label class="form-label" for="email">
                        <i class="bi bi-envelope"></i>
                        Email Address
                    </label>
                    <div class="input-group-modern">
                        <input type="email" id="email" name="email" class="form-control" required autofocus autocomplete="username" placeholder="name@example.com">
                        <i class="bi bi-envelope input-icon"></i>
                    </div>
                </div>

                <div class="form-group">
                    <label class="form-label" for="password">
                        <i class="bi bi-lock"></i>
                        Password
                    </label>
                    <div class="input-group-modern">
                        <input type="password" id="password" name="password" class="form-control" required autocomplete="current-password" placeholder="Enter your password">
                        <i class="bi bi-lock input-icon"></i>
                    </div>
                </div>

                <button type="submit" class="btn-login mt-4">
                    <i class="bi bi-box-arrow-in-right"></i>
                    Sign In
                </button>
            </form>

            <div class="divider">
                <span>Secure Shared Access</span>
            </div>

            <div class="login-footer">
                <i class="bi bi-shield-lock"></i>
                &nbsp;Local Government Unit of Manila &bull; LACMS &copy; <?= date('Y') ?>
            </div>
        </div>
    </div>
</div>
</body>
</html>

