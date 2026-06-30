<?php
/**
 * submit-report.php
 * Report submission form with map picker, multi-photo upload.
 * v2: Added Leaflet.js map pin picker + admin notification on submit.
 */
require_once __DIR__.'/includes/auth-check.php';

$err = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    csrf_check();

    $title    = trim($_POST['title']       ?? '');
    $cat      = trim($_POST['category']    ?? '');
    $desc     = trim($_POST['description'] ?? '');
    $loc      = trim($_POST['location']    ?? '');
    $priority = trim($_POST['priority']    ?? 'Medium');

    
    $lat = isset($_POST['lat']) && $_POST['lat'] !== '' ? (float)$_POST['lat'] : null;
    $lng = isset($_POST['lng']) && $_POST['lng'] !== '' ? (float)$_POST['lng'] : null;
    if ($lat !== null && ($lat < -90  || $lat > 90))  $lat = null;
    if ($lng !== null && ($lng < -180 || $lng > 180)) $lng = null;

    
    if (strlen($title) < 3 || strlen($title) > 150) {
        $err = 'Title must be between 3 and 150 characters.';
    } elseif (!in_array($cat, REPORT_CATEGORIES, true)) {
        $err = 'Please select a valid category.';
    } elseif (strlen($desc) < 10 || strlen($desc) > 2000) {
        $err = 'Description must be between 10 and 2000 characters.';
    } elseif (strlen($loc) < 2 || strlen($loc) > 200) {
        $err = 'Location must be between 2 and 200 characters.';
    } elseif (!in_array($priority, REPORT_PRIORITIES, true)) {
        $err = 'Invalid priority value.';
    }

  
    $validatedFiles = [];
    if (!$err && !empty($_FILES['photos']['name'][0])) {
        foreach ($_FILES['photos']['name'] as $i => $fname) {
            if ($_FILES['photos']['error'][$i] === UPLOAD_ERR_NO_FILE) continue;
            if ($_FILES['photos']['error'][$i] !== UPLOAD_ERR_OK) {
                $err = 'One or more uploads failed. Please try again.'; break;
            }
            if ($_FILES['photos']['size'][$i] > 5 * 1024 * 1024) {
                $err = 'Each photo must be under 5 MB.'; break;
            }
            $info = getimagesize($_FILES['photos']['tmp_name'][$i]);
            if (!$info || !in_array($info['mime'], ['image/jpeg', 'image/png'], true)) {
                $err = 'Only JPG and PNG images are accepted.'; break;
            }
            $validatedFiles[] = [
                'tmp' => $_FILES['photos']['tmp_name'][$i],
                'ext' => ($info['mime'] === 'image/png') ? 'png' : 'jpg',
            ];
        }
    }

    if (!$err) {
        $uploadDir = __DIR__ . '/assets/uploads/';
        if (!is_dir($uploadDir)) mkdir($uploadDir, 0755, true);

        $savedNames = [];
        foreach ($validatedFiles as $vf) {
            $name = 'rep_' . time() . '_' . bin2hex(random_bytes(6)) . '.' . $vf['ext'];
            if (!move_uploaded_file($vf['tmp'], $uploadDir . $name)) {
                $err = 'Could not save upload. Please try again.'; break;
            }
            $savedNames[] = $name;
        }
    }

    if (!$err) {
        $coverPhoto = $savedNames[0] ?? null;

        $ins = $pdo->prepare(
            'INSERT INTO reports (user_id, title, category, description, location, latitude, longitude, photo, status, priority)
             VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?)'
        );
        $ins->execute([
            $_SESSION['user_id'], $title, $cat, $desc,
            $loc, $lat, $lng, $coverPhoto, 'Pending', $priority,
        ]);
        $rid = (int)$pdo->lastInsertId();

       
        $attStmt = $pdo->prepare('INSERT INTO report_attachments (report_id, filename) VALUES (?, ?)');
        foreach ($savedNames as $idx => $name) {
            if ($idx === 0) continue;
            $attStmt->execute([$rid, $name]);
        }

        
        $pdo->prepare(
            'INSERT INTO status_updates (report_id, status, remarks, changed_by_admin_id) VALUES (?, ?, ?, NULL)'
        )->execute([$rid, 'Pending', 'Report submitted by user.']);

      
        $submitterName = $_SESSION['user_name'] ?? 'A user';
        $mapNote = ($lat !== null) ? ' (📍 location pinned on map)' : '';
        $notifMsg = "New report #$rid: \"$title\" submitted by $submitterName — $cat · $priority$mapNote";
        $pdo->prepare(
            'INSERT INTO admin_notifications (report_id, message) VALUES (?, ?)'
        )->execute([$rid, $notifMsg]);

        flash('success', 'Your report has been submitted successfully.');
        redirect('report-details.php?id=' . $rid);
    }
}

$EXTRA_HEAD = '
  <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/leaflet/1.9.4/leaflet.min.css">
  <style>
    .map-picker-wrap { position: relative; margin-top: .5rem; }
    .map-picker      { height: 320px; border-radius: 10px; border: 1px solid var(--border); z-index: 0; }
    .map-locate-btn  { position: absolute; top: .6rem; right: .6rem; z-index: 999;
                       background: #fff; border: 1px solid var(--border); padding: .35rem .75rem;
                       border-radius: 6px; cursor: pointer; font-size: .8rem; font-weight: 500; }
    .map-locate-btn:hover { background: var(--navy); color: #fff; }
    .map-coord-hint  { font-size: .8rem; color: var(--text2); margin-top: .35rem; }
  </style>';

$PAGE_TITLE = 'Submit Report';
include __DIR__.'/includes/header.php';
include __DIR__.'/includes/navbar.php';
?>
<div class="page-header">
  <h1>Submit a Report</h1>
  <p>Describe the issue and attach up to 5 photos if possible.</p>
</div>

<div class="container submit-container">
  <?php if ($err): ?>
    <div class="alert alert-danger"><?= e($err) ?></div>
  <?php endif; ?>

  <div class="card">
    <form method="post" enctype="multipart/form-data" id="reportForm" novalidate>
      <input type="hidden" name="csrf" value="<?= e(csrf_token()) ?>">

      <!-- Title -->
      <div class="form-group">
        <label class="form-label" for="titleField">Title</label>
        <input id="titleField" class="form-control" name="title" required maxlength="150"
               placeholder="Short, clear title (e.g. Broken light in FTMK Lab 2)"
               value="<?= e($_POST['title'] ?? '') ?>">
        <div class="field-error" id="titleError">Title must be at least 3 characters.</div>
      </div>

      <!-- Category & Priority -->
      <div class="form-two-col">
        <div class="form-group">
          <label class="form-label" for="catField">Category</label>
          <select id="catField" class="form-control" name="category" required>
            <option value="">— Select category —</option>
            <?php foreach (REPORT_CATEGORIES as $c): ?>
              <option <?= (($_POST['category'] ?? '') === $c) ? 'selected' : '' ?>><?= e($c) ?></option>
            <?php endforeach; ?>
          </select>
        </div>
        <div class="form-group">
          <label class="form-label" for="priorityField">Priority</label>
          <select id="priorityField" class="form-control" name="priority">
            <?php foreach (REPORT_PRIORITIES as $p): ?>
              <option <?= (($_POST['priority'] ?? 'Medium') === $p) ? 'selected' : '' ?>><?= e($p) ?></option>
            <?php endforeach; ?>
          </select>
        </div>
      </div>

      <!-- Location text -->
      <div class="form-group">
        <label class="form-label" for="locField">Location</label>
        <input id="locField" class="form-control" name="location" required maxlength="200"
               placeholder="e.g. FTMK Block A, Level 2, Room 214"
               value="<?= e($_POST['location'] ?? '') ?>">
      </div>

      <!-- Map picker -->
      <div class="form-group">
        <label class="form-label">
          Pin on Map
          <span class="form-hint-inline">(optional — click the map or use GPS to mark the exact spot)</span>
        </label>
        <div class="map-picker-wrap">
          <div id="mapPicker" class="map-picker"></div>
          <button type="button" class="map-locate-btn" id="locateBtn">📍 Use My Location</button>
        </div>
        <input type="hidden" name="lat" id="latInput" value="<?= e($_POST['lat'] ?? '') ?>">
        <input type="hidden" name="lng" id="lngInput" value="<?= e($_POST['lng'] ?? '') ?>">
        <div id="mapCoordHint" class="map-coord-hint" style="display:none;">
          ✅ Location pinned. You can drag the marker to adjust.
        </div>
      </div>

      <!-- Description -->
      <div class="form-group">
        <label class="form-label" for="descField">
          Description
          <span class="char-counter" id="descCount">0 / 2000</span>
        </label>
        <textarea id="descField" class="form-control" name="description" required
                  maxlength="2000" rows="5"
                  placeholder="Describe the issue clearly. Include what you observed, when it started, and any safety concerns."><?= e($_POST['description'] ?? '') ?></textarea>
        <div class="field-error" id="descError">Description must be at least 10 characters.</div>
      </div>

      <!-- Photo upload -->
      <div class="form-group">
        <label class="form-label">Photos <span class="form-hint-inline">(JPG/PNG, max 5 MB each, up to 5)</span></label>
        <input id="photoInput" type="file" name="photos[]" accept="image/jpeg,image/png"
               multiple class="visually-hidden" onchange="previewPhotos(this)">
        <div class="upload-area" onclick="document.getElementById('photoInput').click()" role="button" tabindex="0">
          <div class="upload-icon">📷</div>
          <div id="uploadLabel">Click to upload photos</div>
          <div id="photoPreviewGrid" class="photo-preview-grid"></div>
        </div>
      </div>

      <!-- Actions -->
      <div class="flex-between">
        <a class="btn btn-outline" href="<?= BASE_URL ?>/my-report.php">Cancel</a>
        <button class="btn btn-primary" id="submitBtn" type="submit">Submit Report</button>
      </div>
    </form>
  </div>
</div>

<script src="https://cdnjs.cloudflare.com/ajax/libs/leaflet/1.9.4/leaflet.min.js"></script>
<script>
(function () {
  // UTeM campus default centre
  var UTEM = [2.3065, 102.3188];
  var map    = L.map('mapPicker').setView(UTEM, 16);
  var marker = null;
  var latEl  = document.getElementById('latInput');
  var lngEl  = document.getElementById('lngInput');
  var hint   = document.getElementById('mapCoordHint');

  L.tileLayer('https://{s}.tile.openstreetmap.org/{z}/{x}/{y}.png', {
    attribution: '© <a href="https://www.openstreetmap.org/copyright">OpenStreetMap</a>',
    maxZoom: 19
  }).addTo(map);

  function placeMarker(lat, lng) {
    latEl.value = lat.toFixed(8);
    lngEl.value = lng.toFixed(8);
    hint.style.display = 'block';
    var latlng = L.latLng(lat, lng);
    if (marker) {
      marker.setLatLng(latlng);
    } else {
      marker = L.marker(latlng, { draggable: true }).addTo(map);
      marker.on('dragend', function () {
        var p = marker.getLatLng();
        latEl.value = p.lat.toFixed(8);
        lngEl.value = p.lng.toFixed(8);
      });
    }
  }

  // Restore pin on validation error
  var sv = '<?= e($_POST['lat'] ?? '') ?>';
  var sl = '<?= e($_POST['lng'] ?? '') ?>';
  if (sv && sl) {
    placeMarker(parseFloat(sv), parseFloat(sl));
    map.setView([parseFloat(sv), parseFloat(sl)], 18);
  }

  map.on('click', function (e) { placeMarker(e.latlng.lat, e.latlng.lng); });

  document.getElementById('locateBtn').addEventListener('click', function () {
    if (!navigator.geolocation) { alert('Geolocation not supported by your browser.'); return; }
    navigator.geolocation.getCurrentPosition(function (pos) {
      var lat = pos.coords.latitude, lng = pos.coords.longitude;
      map.setView([lat, lng], 18);
      placeMarker(lat, lng);
    }, function () {
      alert('Could not get your location. Please pin manually on the map.');
    });
  });
}());
</script>

<?php include __DIR__.'/includes/footer.php'; ?>
