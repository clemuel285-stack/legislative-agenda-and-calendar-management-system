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
<meta name="viewport" content="width=device-width, initial-scale=1">
<title>Sign In | LACMS</title>
<link rel="stylesheet" href="<?= e(vendorAsset('bootstrap/bootstrap.min.css','https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css')) ?>">
<link rel="stylesheet" href="<?= e(vendorAsset('bootstrap-icons/bootstrap-icons.css','https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.min.css')) ?>">
<link rel="stylesheet" href="<?= e(appUrl('assets/css/lacms-login-v2.css')) ?>">
</head>
<body>
<main class="lacms-login-shell">
    <section class="lacms-login-brand-panel">
        <div class="lacms-login-brand-content">
            <span class="lacms-login-brand-icon"><i class="bi bi-calendar3"></i></span>
            <div class="lacms-login-eyebrow">Local Government Unit of Manila</div>
            <h1>Legislative Agenda and Calendar Management System</h1>
            <p>Centralize legislative agendas, calendars, meetings, deadlines, communication, monitoring, reminders, and reporting in one coordinated platform.</p>

            <div class="lacms-login-features">
                <span><i class="bi bi-list-check"></i> Legislative Agenda Management</span>
                <span><i class="bi bi-calendar-week"></i> Calendar & Meeting Coordination</span>
                <span><i class="bi bi-stars"></i> AI-Assisted Email Reminders</span>
                <span><i class="bi bi-alarm"></i> Deadline Tracking</span>
            </div>
        </div>
    </section>

    <section class="lacms-login-form-panel">
        <div class="lacms-login-card">
            <div class="lacms-login-card-heading">
                <span>Shared Legislative Account</span>
                <h2>Sign in to LACMS</h2>
                <p>Access is limited to authorized users.</p>
            </div>

            <?php if ($expired): ?><div class="alert alert-warning">Your session expired after eight hours of inactivity.</div><?php endif; ?>
            <?php if ($flash): ?><div class="alert alert-<?= e($flash['type']) ?>"><?= e($flash['message']) ?></div><?php endif; ?>

            <form method="post" action="<?= e(appUrl('auth/process_login.php')) ?>">
                <input type="hidden" name="csrf_token" value="<?= e(csrfToken()) ?>">

                <div class="mb-3">
                    <label class="form-label" for="email">Email Address</label>
                    <div class="input-group">
                        <span class="input-group-text"><i class="bi bi-envelope"></i></span>
                        <input type="email" id="email" name="email" class="form-control" required autofocus autocomplete="username">
                    </div>
                </div>

                <div class="mb-3">
                    <label class="form-label" for="password">Password</label>
                    <div class="input-group">
                        <span class="input-group-text"><i class="bi bi-key"></i></span>
                        <input type="password" id="password" name="password" class="form-control" required autocomplete="current-password">
                        <button type="button" class="btn btn-outline-secondary" id="togglePassword"><i class="bi bi-eye"></i></button>
                    </div>
                </div>

                <button type="submit" class="btn btn-primary w-100"><i class="bi bi-box-arrow-in-right"></i> Sign In</button>
            </form>

            <div class="lacms-login-note">
                <i class="bi bi-shield-check"></i>
                <span>Secure authentication, role-based access, shared session, and centralized database access support the legislative platform.</span>
            </div>
        </div>
    </section>
</main>

<script>
document.getElementById('togglePassword')?.addEventListener('click',function(){
    const input=document.getElementById('password');
    const show=input.type==='password';
    input.type=show?'text':'password';
    this.innerHTML=show?'<i class="bi bi-eye-slash"></i>':'<i class="bi bi-eye"></i>';
});
</script>
</body>
</html>
