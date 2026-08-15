'use strict';
document.addEventListener('DOMContentLoaded',function(){
    const sidebar=document.getElementById('lacmsSidebar');
    const toggle=document.getElementById('lacmsSidebarToggle');
    const backdrop=document.getElementById('lacmsSidebarBackdrop');

    function closeSidebar(){
        sidebar?.classList.remove('open');
        backdrop?.classList.remove('show');
    }

    toggle?.addEventListener('click',function(){
        sidebar?.classList.toggle('open');
        backdrop?.classList.toggle('show');
    });

    backdrop?.addEventListener('click',closeSidebar);

    window.addEventListener('resize',function(){
        if(window.innerWidth>1050) closeSidebar();
    });

    document.querySelectorAll('[data-ui-preview]').forEach(function(button){
        button.addEventListener('click',function(){
            const title=button.querySelector('strong')?.textContent?.trim()||button.textContent.trim()||'LACMS action';
            if(typeof Swal!=='undefined'){
                Swal.fire({
                    icon:'info',
                    title:title,
                    html:'<div class="text-start small"><p>This navigation and page are ready for the current client-demo phase.</p><p class="text-muted mb-0">Database CRUD, automated reminders, email delivery, schedule conflict checking, and live reports will be connected later.</p></div>',
                    confirmButtonText:'Okay'
                });
            }
        });
    });
});
