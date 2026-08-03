<?php
/**
 * LACMS shared page footer.
 *
 * Expected optional variable:
 * $extraJs = [APP_URL . '/assets/js/example.js'];
 */

declare(strict_types=1);

$extraJs = $extraJs ?? [];

$bootstrapJs = vendorAsset(
    'bootstrap/bootstrap.bundle.min.js',
    'https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js'
);
?>

        <footer class="content-footer">
            <span>
                &copy; <?= date('Y') ?>
                <?= e(APP_NAME) ?>
            </span>

            <span>
                Shared Legislative Management Platform
            </span>
        </footer>
    </main>
</div>

<script src="<?= e($bootstrapJs) ?>"></script>

<script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>

<script src="https://cdn.jsdelivr.net/npm/chart.js@4.4.7/dist/chart.umd.min.js"></script>

<script src="<?= e(appUrl('assets/js/layout.js')) ?>"></script>

<?php foreach ($extraJs as $js): ?>
    <script src="<?= e((string)$js) ?>"></script>
<?php endforeach; ?>

</body>
</html>
