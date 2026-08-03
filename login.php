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
    <title>Sign In | <?= e(APP_SHORT_NAME) ?></title>

    <link rel="stylesheet" href="<?= e(vendorAsset(
        'bootstrap/bootstrap.min.css',
        'https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css'
    )) ?>">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.min.css">
    <link rel="stylesheet" href="<?= e(appUrl('assets/css/login.css')) ?>">
</head>
<body>
<main class="login-shell">
    <section class="login-brand-panel">
        <div class="brand-mark"><i class="bi bi-calendar3"></i></div>

        <div class="brand-copy">
            <span>Legislative System #3</span>
            <h1>Legislative Agenda and Calendar Management System</h1>
            <p>
                Coordinate priorities, calendars, meetings, deadlines,
                and executive-legislative activities.
            </p>
        </div>

        <div class="brand-features">
            <div><i class="bi bi-list-stars"></i><span>Legislative Priority Setting</span></div>
            <div><i class="bi bi-calendar-week"></i><span>Calendar Scheduling</span></div>
            <div><i class="bi bi-people"></i><span>Meeting Coordination</span></div>
            <div><i class="bi bi-alarm"></i><span>Deadline Tracking</span></div>
        </div>
    </section>

    <section class="login-form-panel">
        <div class="login-card">
            <div class="login-card-header">
                <div class="login-icon"><i class="bi bi-shield-lock"></i></div>
                <div>
                    <span>Shared Legislative Account</span>
                    <h2>Sign in to LACMS</h2>
                </div>
            </div>

            <?php if ($expired): ?>
                <div class="alert alert-warning">
                    Your session expired after eight hours of inactivity.
                </div>
            <?php endif; ?>

            <?php if ($flash): ?>
                <div class="alert alert-<?= e($flash['type']) ?>">
                    <?= e($flash['message']) ?>
                </div>
            <?php endif; ?>

            <form method="post" action="<?= e(appUrl('auth/process_login.php')) ?>">
                <input type="hidden" name="csrf_token" value="<?= e(csrfToken()) ?>">

                <div class="mb-3">
                    <label class="form-label" for="email">Email Address</label>
                    <div class="input-group">
                        <span class="input-group-text"><i class="bi bi-envelope"></i></span>
                        <input
                            type="email"
                            id="email"
                            name="email"
                            class="form-control"
                            placeholder="admin@legislative.local"
                            required
                            autofocus
                            autocomplete="username"
                        >
                    </div>
                </div>

                <div class="mb-3">
                    <label class="form-label" for="password">Password</label>
                    <div class="input-group">
                        <span class="input-group-text"><i class="bi bi-key"></i></span>
                        <input
                            type="password"
                            id="password"
                            name="password"
                            class="form-control"
                            placeholder="Enter your password"
                            required
                            autocomplete="current-password"
                        >
                        <button type="button" class="btn btn-outline-secondary" id="togglePassword">
                            <i class="bi bi-eye"></i>
                        </button>
                    </div>
                </div>

                <button type="submit" class="btn btn-primary w-100">
                    <i class="bi bi-box-arrow-in-right"></i>
                    Sign In
                </button>
            </form>

            <div class="login-note">
                <i class="bi bi-info-circle"></i>
                <span>
                    This website uses the same login and session as the
                    other legislative systems.
                </span>
            </div>
        </div>
    </section>
</main>

<script>
document.getElementById('togglePassword')?.addEventListener('click', function () {
    const input = document.getElementById('password');
    const show = input.type === 'password';
    input.type = show ? 'text' : 'password';
    this.innerHTML = show
        ? '<i class="bi bi-eye-slash"></i>'
        : '<i class="bi bi-eye"></i>';
});
</script>
</body>
</html>
