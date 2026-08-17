<?php
declare(strict_types=1);

require_once __DIR__.'/../includes/lacms_notification_helpers.php';
requireLacmsPermission('lacms.notifications.view');

$pdo=db();$pageTitle='Meeting Notifications';$activeMenu='meeting_notifications';
$extraCss=[appUrl('assets/css/lacms-operational.css')];

$status=clean($_GET['status']??'');$source=clean($_GET['source_type']??'');
$where=[];$params=[];
if($status!==''){$where[]='n.status=:status';$params[':status']=$status;}
if($source!==''){$where[]='n.source_type=:source';$params[':source']=$source;}
$sqlWhere=$where?'WHERE '.implode(' AND ',$where):'';

$stmt=$pdo->prepare(
 "SELECT n.*,
         (SELECT COUNT(*) FROM lacms_notification_recipients r WHERE r.notification_id=n.id) recipient_count,
         (SELECT COUNT(*) FROM lacms_notification_recipients r WHERE r.notification_id=n.id AND r.delivery_status='Sent') sent_count,
         (SELECT COUNT(*) FROM lacms_notification_recipients r WHERE r.notification_id=n.id AND r.delivery_status='Pending') pending_count,
         u.full_name created_name
  FROM lacms_notifications n
  LEFT JOIN users u ON u.id=n.created_by
  {$sqlWhere}
  ORDER BY COALESCE(n.scheduled_at,n.created_at) DESC,n.id DESC"
);
$stmt->execute($params);$rows=$stmt->fetchAll();

$stats=$pdo->query(
 "SELECT COUNT(*) total,
         SUM(status='Ready') ready_count,
         SUM(status='Scheduled') scheduled_count,
         SUM(status='Partially Sent') partial_count,
         SUM(status='Sent') sent_count,
         SUM(status='Cancelled') cancelled_count
  FROM lacms_notifications"
)->fetch()?:[];

$meetings=$pdo->query(
 "SELECT id,meeting_reference,title,start_datetime,status
  FROM lacms_meetings
  WHERE status NOT IN ('Cancelled')
  ORDER BY start_datetime DESC LIMIT 250"
)->fetchAll();

include __DIR__.'/../layouts/header.php';
?>
<div class="lacms-app-wrapper"><?php include __DIR__.'/../layouts/sidebar.php'; ?><main class="lacms-main-content">

<div class="lo-head"><div><div class="lo-eyebrow"><i class="bi bi-envelope-paper"></i> Step 6 · Notification Queue</div><h1>Meeting & Legislative Notifications</h1><p>Centralized queue for meeting confirmations, schedule changes, cancellations, deadline reminders and escalations. Internal users are delivered through the shared notification system; external email recipients remain traceable until SMTP is configured.</p></div><?php if(lacmsOperationalManager()): ?><button class="btn btn-primary" id="btnManual"><i class="bi bi-envelope-plus"></i> New Meeting Notice</button><?php endif; ?></div>

<div class="row g-3 mb-3"><?php foreach([
 ['Notifications',$stats['total']??0,'bi-envelope-paper'],['Ready',$stats['ready_count']??0,'bi-send'],
 ['Scheduled',$stats['scheduled_count']??0,'bi-clock'],['Partially Sent',$stats['partial_count']??0,'bi-exclamation-circle'],
 ['Sent',$stats['sent_count']??0,'bi-check2-circle'],['Cancelled',$stats['cancelled_count']??0,'bi-x-circle']
] as [$l,$v,$i]): ?><div class="col-6 col-xl-2"><div class="lo-stat"><i class="bi <?= e($i) ?>"></i><div><strong><?= (int)$v ?></strong><small><?= e($l) ?></small></div></div></div><?php endforeach; ?></div>

<div class="card lo-card mb-3"><div class="card-body"><form class="row g-2 align-items-end"><div class="col-md-4"><label class="form-label small">Status</label><select class="form-select form-select-sm" name="status"><option value="">All</option><?php foreach(['Ready','Scheduled','Partially Sent','Sent','Cancelled','Failed'] as $x): ?><option <?= $status===$x?'selected':'' ?>><?= e($x) ?></option><?php endforeach; ?></select></div><div class="col-md-4"><label class="form-label small">Source</label><select class="form-select form-select-sm" name="source_type"><option value="">All</option><option <?= $source==='Meeting'?'selected':'' ?>>Meeting</option><option <?= $source==='Deadline'?'selected':'' ?>>Deadline</option></select></div><div class="col-md-4"><button class="btn btn-outline-primary btn-sm w-100">Apply Filters</button></div></form></div></div>

<div class="card lo-card"><div class="card-header d-flex justify-content-between"><span>Notification Delivery Workspace</span><span>External SMTP delivery is intentionally not simulated.</span></div><div class="table-responsive"><table class="table table-hover lo-table mb-0"><thead><tr><th>Notification</th><th>Source</th><th>Schedule</th><th>Recipients</th><th>AI</th><th>Status</th><th>Created By</th><th class="text-end">Actions</th></tr></thead><tbody>
<?php if(!$rows): ?><tr><td colspan="8" class="text-center text-muted py-5">No notifications found.</td></tr><?php endif; ?>
<?php foreach($rows as $n): ?><tr><td><span class="lo-code"><?= e($n['notification_reference']) ?></span><div><strong><?= e($n['subject']) ?></strong></div><div class="small text-muted"><?= e(mb_strimwidth($n['message'],0,100,'…')) ?></div></td><td><?= e($n['source_type'].' #'.$n['source_id']) ?><div class="small text-muted"><?= e($n['notification_type']) ?></div></td><td><?= $n['scheduled_at']?formatDateTime($n['scheduled_at']):'Immediate' ?></td><td><?= (int)$n['sent_count'] ?>/<?= (int)$n['recipient_count'] ?> sent<div class="small text-muted"><?= (int)$n['pending_count'] ?> pending</div></td><td><?= $n['generated_by_ai']?'AI-assisted':'No' ?></td><td><span class="lo-status <?= $n['status']==='Sent'?'good':(in_array($n['status'],['Cancelled','Failed'],true)?'bad':($n['status']==='Partially Sent'?'warn':'')) ?>"><?= e($n['status']) ?></span></td><td><?= e($n['created_name']?:'System') ?></td><td class="text-end"><?php if(lacmsOperationalManager()&&!in_array($n['status'],['Sent','Cancelled'],true)): ?><div class="btn-group btn-group-sm"><button class="btn btn-outline-primary notif-action" data-id="<?= (int)$n['id'] ?>" data-mode="process">Process</button><button class="btn btn-outline-danger notif-action" data-id="<?= (int)$n['id'] ?>" data-mode="cancel">Cancel</button></div><?php endif; ?></td></tr><?php endforeach; ?>
</tbody></table></div></div>

</main></div>

<?php if(lacmsOperationalManager()): ?>
<div class="modal fade" id="manualModal" tabindex="-1"><div class="modal-dialog modal-lg"><div class="modal-content"><form id="manualForm"><?= csrfField() ?><input type="hidden" name="source_type" value="Meeting"><div class="modal-header bg-dark text-white"><h5 class="modal-title">New Meeting Notification</h5><button class="btn-close btn-close-white" type="button" data-bs-dismiss="modal"></button></div><div class="modal-body"><div class="row g-3">
<div class="col-12"><label class="form-label">Meeting *</label><select class="form-select" name="source_id" required><option value="">Select meeting</option><?php foreach($meetings as $m): ?><option value="<?= (int)$m['id'] ?>"><?= e($m['meeting_reference'].' · '.$m['title'].' · '.formatDateTime($m['start_datetime'])) ?></option><?php endforeach; ?></select></div>
<div class="col-md-6"><label class="form-label">Notification Type</label><select class="form-select" name="notification_type"><option>Meeting Reminder</option><option>Schedule Update</option><option>Agenda Update</option><option>Coordination Notice</option><option>Participant Update</option></select></div><div class="col-md-6"><label class="form-label">Optional Send Schedule</label><input type="datetime-local" class="form-control" name="scheduled_at"></div>
<div class="col-12"><label class="form-label">Subject *</label><input class="form-control" name="subject" required></div><div class="col-12"><label class="form-label">Message *</label><textarea class="form-control" name="message" rows="6" required></textarea></div>
</div></div><div class="modal-footer"><button class="btn btn-outline-secondary" type="button" data-bs-dismiss="modal">Cancel</button><button class="btn btn-primary">Queue Notification</button></div></form></div></div></div>

<script>
document.addEventListener('DOMContentLoaded',function(){
 const form=document.getElementById('manualForm'),modal=new bootstrap.Modal(document.getElementById('manualModal'));
 document.getElementById('btnManual').onclick=()=>{form.reset();modal.show();};
 form.onsubmit=async e=>{e.preventDefault();const r=await fetch('ajax_manual_notification.php',{method:'POST',headers:{'X-Requested-With':'XMLHttpRequest'},body:new FormData(form)}).then(x=>x.json());if(r.success)location.reload();else Swal.fire('Notification Error',r.message,'error');};
 document.querySelectorAll('.notif-action').forEach(b=>b.onclick=async function(){const c=await Swal.fire({title:(this.dataset.mode==='cancel'?'Cancel':'Process')+' notification?',showCancelButton:true});if(!c.isConfirmed)return;const fd=new FormData();fd.append('csrf_token','<?= e(csrfToken()) ?>');fd.append('notification_id',this.dataset.id);fd.append('mode',this.dataset.mode);const r=await fetch('ajax_notification.php',{method:'POST',headers:{'X-Requested-With':'XMLHttpRequest'},body:fd}).then(x=>x.json());if(r.success){await Swal.fire('Done',r.message,'success');location.reload();}else Swal.fire('Notification Error',r.message,'error');});
});
</script>
<?php endif; ?>

<?php include __DIR__.'/../layouts/footer.php'; ?>
