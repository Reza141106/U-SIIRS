// U-SIIRS app.js — vanilla JS interactions
document.addEventListener('DOMContentLoaded', () => {
  // user dropdown
  const ua = document.getElementById('navUser');
  const dm = document.getElementById('navDrop');
  if (ua && dm) {
    ua.addEventListener('click', (e) => { e.stopPropagation(); dm.classList.toggle('open'); });
    document.addEventListener('click', () => dm.classList.remove('open'));
  }
  // notif panel
  const bell = document.getElementById('bell');
  const np = document.getElementById('notifPanel');
  if (bell && np) {
    bell.addEventListener('click', (e) => { e.stopPropagation(); np.classList.toggle('open'); });
    document.addEventListener('click', (e) => { if(!np.contains(e.target)) np.classList.remove('open'); });
  }
  // image preview
  const fi = document.getElementById('photoInput');
  const fp = document.getElementById('photoPreview');
  if (fi && fp) {
    fi.addEventListener('change', () => {
      const f = fi.files[0]; if (!f) return;
      if (f.size > 5*1024*1024) { alert('Max file size 5MB'); fi.value=''; return; }
      if (!['image/jpeg','image/png'].includes(f.type)) { alert('JPG/PNG only'); fi.value=''; return; }
      const r = new FileReader();
      r.onload = e => { fp.src = e.target.result; fp.style.display='block'; };
      r.readAsDataURL(f);
    });
  }
  // delete confirm modal
  document.querySelectorAll('[data-confirm]').forEach(btn => {
    btn.addEventListener('click', (e) => {
      if (!confirm(btn.dataset.confirm || 'Are you sure?')) e.preventDefault();
    });
  });
  // table search filter
  const si = document.getElementById('tableSearch');
  if (si) {
    si.addEventListener('input', () => {
      const q = si.value.toLowerCase();
      document.querySelectorAll('table tbody tr').forEach(tr => {
        tr.style.display = tr.textContent.toLowerCase().includes(q) ? '' : 'none';
      });
    });
  }
  // filter chips
  document.querySelectorAll('.filter-chips').forEach(group => {
    group.querySelectorAll('.chip').forEach(chip => {
      chip.addEventListener('click', () => {
        const status = chip.dataset.status;
        const url = new URL(window.location.href);
        if (status && status !== 'all') url.searchParams.set('status', status);
        else url.searchParams.delete('status');
        window.location.href = url.toString();
      });
    });
  });
  // FAQ
  document.querySelectorAll('.faq-item').forEach(it => {
    it.querySelector('.faq-q')?.addEventListener('click', () => it.classList.toggle('open'));
  });
  // toast auto-hide
  document.querySelectorAll('.toast').forEach(t => {
    setTimeout(() => t.remove(), 3500);
  });
});

function openModal(id){ document.getElementById(id)?.classList.add('open'); }
function closeModal(id){ document.getElementById(id)?.classList.remove('open'); }
