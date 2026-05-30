/**
 * assets/js/app.js
 * U-SIIRS — Vanilla JavaScript interactions.
 * All page-specific inline scripts have been moved here.
 * No frameworks, no dependencies.
 */

document.addEventListener('DOMContentLoaded', function () {

  // ── USER DROPDOWN ───────────────────────────────────────────────────────────
  var navUser = document.getElementById('navUser');
  var navDrop = document.getElementById('navDrop');
  if (navUser && navDrop) {
    navUser.addEventListener('click', function (e) {
      e.stopPropagation();
      navDrop.classList.toggle('open');
    });
    document.addEventListener('click', function () {
      navDrop.classList.remove('open');
    });
  }

  // ── NOTIFICATION PANEL ──────────────────────────────────────────────────────
  var bell       = document.getElementById('bell');
  var notifPanel = document.getElementById('notifPanel');
  if (bell && notifPanel) {
    bell.addEventListener('click', function (e) {
      e.stopPropagation();
      notifPanel.classList.toggle('open');
    });
    document.addEventListener('click', function (e) {
      if (!notifPanel.contains(e.target)) {
        notifPanel.classList.remove('open');
      }
    });
  }

  // ── DATA-CONFIRM: delete/destructive action confirmations ───────────────────
  // Usage: <button data-confirm="Are you sure?"> — no inline onclick needed.
  document.querySelectorAll('[data-confirm]').forEach(function (btn) {
    btn.addEventListener('click', function (e) {
      var msg = btn.dataset.confirm || 'Are you sure?';
      if (!window.confirm(msg)) {
        e.preventDefault();
      }
    });
  });

  // ── TABLE SEARCH (client-side, for small data sets) ─────────────────────────
  var searchInput = document.getElementById('tableSearch');
  if (searchInput) {
    searchInput.addEventListener('input', function () {
      var q = searchInput.value.toLowerCase();
      document.querySelectorAll('table tbody tr').forEach(function (tr) {
        tr.style.display = tr.textContent.toLowerCase().includes(q) ? '' : 'none';
      });
    });
  }

  // ── STATUS FILTER CHIPS ─────────────────────────────────────────────────────
  document.querySelectorAll('.filter-chips').forEach(function (group) {
    group.querySelectorAll('.chip').forEach(function (chip) {
      chip.addEventListener('click', function () {
        var status = chip.dataset.status;
        var url    = new URL(window.location.href);
        url.searchParams.delete('page'); // reset to page 1 on filter change
        if (status && status !== 'all') {
          url.searchParams.set('status', status);
        } else {
          url.searchParams.delete('status');
        }
        window.location.href = url.toString();
      });
    });
  });

  // ── FAQ ACCORDION ───────────────────────────────────────────────────────────
  document.querySelectorAll('.faq-item').forEach(function (item) {
    var q = item.querySelector('.faq-q');
    if (q) {
      q.addEventListener('click', function () {
        item.classList.toggle('open');
      });
    }
  });

  // ── TOAST AUTO-DISMISS (3.5 s) ──────────────────────────────────────────────
  document.querySelectorAll('.toast').forEach(function (toast) {
    setTimeout(function () {
      toast.style.opacity    = '0';
      toast.style.transition = 'opacity 0.4s';
      setTimeout(function () { toast.remove(); }, 400);
    }, 3500);
  });

  // ── MOBILE HAMBURGER (user nav) ─────────────────────────────────────────────
  var hamburger = document.getElementById('hamburger');
  var navLinks  = document.querySelector('.nav-links');
  if (hamburger && navLinks) {
    hamburger.addEventListener('click', function () {
      hamburger.classList.toggle('open');
      navLinks.classList.toggle('mobile-open');
    });
    navLinks.querySelectorAll('a').forEach(function (link) {
      link.addEventListener('click', function () {
        hamburger.classList.remove('open');
        navLinks.classList.remove('mobile-open');
      });
    });
  }

  // ── SUBMIT REPORT: photo preview ────────────────────────────────────────────
  // Moved from inline script in submit-report.php
  var photoInput = document.getElementById('photoInput');
  if (photoInput) {
    photoInput.addEventListener('change', function () {
      previewPhotos(photoInput);
    });
  }

  // ── SUBMIT REPORT: description char counter ─────────────────────────────────
  var descField = document.getElementById('descField');
  var descCount = document.getElementById('descCount');
  if (descField && descCount) {
    function updateDescCount() {
      var len = descField.value.length;
      descCount.textContent = len + ' / 2000';
      descCount.className = 'char-counter' +
        (len > 1800 ? ' at-limit' : len > 1500 ? ' near-limit' : '');
      validateField(descField, 10, 2000, 'descError');
    }
    descField.addEventListener('input', updateDescCount);
    descField.addEventListener('blur',  updateDescCount);
    if (descField.value) updateDescCount(); // re-run on validation-error page reload
  }

  // ── SUBMIT REPORT: title live validation ────────────────────────────────────
  var titleField = document.getElementById('titleField');
  if (titleField) {
    titleField.addEventListener('blur', function () {
      validateField(titleField, 3, 150, 'titleError');
    });
  }

  // ── SUBMIT REPORT: form submit validation + loading state ───────────────────
  var reportForm = document.getElementById('reportForm');
  if (reportForm) {
    reportForm.addEventListener('submit', function (e) {
      var ok = true;
      if (titleField) ok = validateField(titleField, 3, 150, 'titleError') && ok;
      if (descField)  ok = validateField(descField, 10, 2000, 'descError') && ok;

      if (!ok) {
        e.preventDefault();
        var first = reportForm.querySelector('.is-invalid');
        if (first) first.scrollIntoView({ behavior: 'smooth', block: 'center' });
        return;
      }

      var submitBtn = document.getElementById('submitBtn');
      if (submitBtn) {
        submitBtn.classList.add('loading');
        submitBtn.disabled = true;
      }
    });
  }

  // ── CONTACT FORM: submit loading state ──────────────────────────────────────
  var contactForm = document.getElementById('contactForm');
  if (contactForm) {
    contactForm.addEventListener('submit', function () {
      var btn = document.getElementById('contactSubmitBtn');
      if (btn) {
        btn.classList.add('loading');
        btn.disabled = true;
      }
    });
  }

  // ── PERIOD FILTER (admin dashboard) — auto-submit on select change ──────────
  // Already handled via onchange="this.form.submit()" in admin/dashboard.php
  // No additional JS needed.

}); // end DOMContentLoaded

// ─────────────────────────────────────────────────────────────────────────────
// GLOBAL UTILITIES (called from inline event attributes and other scripts)
// ─────────────────────────────────────────────────────────────────────────────

/**
 * Open/close a modal overlay by ID.
 */
function openModal(id) {
  var el = document.getElementById(id);
  if (el) el.classList.add('open');
}
function closeModal(id) {
  var el = document.getElementById(id);
  if (el) el.classList.remove('open');
}

/**
 * Toggle password input visibility.
 * Called from pw-toggle buttons: onclick="togglePw('inputId', this)"
 */
function togglePw(inputId, btn) {
  var input = document.getElementById(inputId);
  if (!input) return;
  var eyeOpen = '<svg width="18" height="18" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path d="M1 12s4-8 11-8 11 8 11 8-4 8-11 8-11-8-11-8z"/><circle cx="12" cy="12" r="3"/></svg>';
  var eyeShut = '<svg width="18" height="18" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path d="M17.94 17.94A10.07 10.07 0 0112 20c-7 0-11-8-11-8a18.45 18.45 0 015.06-5.94M9.9 4.24A9.12 9.12 0 0112 4c7 0 11 8 11 8a18.5 18.5 0 01-2.16 3.19m-6.72-1.07a3 3 0 11-4.24-4.24"/><line x1="1" y1="1" x2="23" y2="23"/></svg>';
  if (input.type === 'password') {
    input.type = 'text';
    btn.innerHTML = eyeShut;
  } else {
    input.type = 'password';
    btn.innerHTML = eyeOpen;
  }
}

/**
 * Validate a field's length and apply visual feedback classes.
 */
function validateField(input, min, max, errorId) {
  var val = input.value.trim();
  var err = document.getElementById(errorId);
  if (val.length < min || val.length > max) {
    input.classList.remove('is-valid');
    input.classList.add('is-invalid');
    if (err) err.style.display = 'block';
    return false;
  }
  input.classList.remove('is-invalid');
  input.classList.add('is-valid');
  if (err) err.style.display = 'none';
  return true;
}

/**
 * Preview selected photos in the upload area (submit-report.php).
 */
function previewPhotos(input) {
  var grid  = document.getElementById('photoPreviewGrid');
  var label = document.getElementById('uploadLabel');
  if (!grid || !label) return;

  grid.innerHTML = '';
  var files = Array.from(input.files).slice(0, 5);

  if (files.length) {
    label.textContent = files.length + ' photo(s) selected';
    files.forEach(function (file) {
      var img = document.createElement('img');
      img.className = 'preview-thumb';
      var reader = new FileReader();
      reader.onload = function (e) { img.src = e.target.result; };
      reader.readAsDataURL(file);
      grid.appendChild(img);
    });
  } else {
    label.textContent = 'Click to upload photos';
  }
}

// ── LIGHTBOX ─────────────────────────────────────────────────────────────────

/**
 * Open the lightbox with the given image URL.
 * Called from: onclick="openLightbox(this.src)"
 */
function openLightbox(src) {
  var img = document.getElementById('lightboxImg');
  var box = document.getElementById('lightbox');
  if (img && box) {
    img.src = src;
    box.style.display = 'flex';
  }
}

// Close lightbox on Escape key
document.addEventListener('keydown', function (e) {
  if (e.key === 'Escape') {
    var box = document.getElementById('lightbox');
    if (box) box.style.display = 'none';
  }
});

/**
 * Highlight the active thumbnail in a photo gallery.
 * Called from thumbnail onclick in admin/report-view.php.
 */
function setActive(el) {
  document.querySelectorAll('.thumb-mini').forEach(function (t) {
    t.classList.remove('thumb-active');
  });
  el.classList.add('thumb-active');
}
