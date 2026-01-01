<?php
include 'admin-header.php';

$cssFile = '../../INCLUDES/general-CSS.css';

// Handle form submission
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
  $logoSize = $_POST['logo_size'] ?? 'md';
  $fontFamily = $_POST['font_family'] ?? 'Roboto';

  $cssContent = file_get_contents($cssFile);

  $logoSizes = [
    'sm' => '28px',
    'md' => '40px',
    'lg' => '56px'
  ];

  $newLogoSize = $logoSizes[$logoSize];
  $cssContent = preg_replace(
    '/\.awegreen-logo\s*\{[^}]*width:\s*\d+px;[^}]*\}/',
    ".awegreen-logo {\n  width: $newLogoSize;\n}",
    $cssContent
  );

  // Update font family
  $fontImports = [
    'Roboto' => "@import url('https://fonts.googleapis.com/css2?family=Roboto:wght@300;400;500;700;900&display=swap');",
    'Open Sans' => "@import url('https://fonts.googleapis.com/css2?family=Open+Sans:wght@300;400;500;600;700;800&display=swap');",
    'Montserrat' => "@import url('https://fonts.googleapis.com/css2?family=Montserrat:wght@300;400;500;600;700;800;900&display=swap');",
    'Poppins' => "@import url('https://fonts.googleapis.com/css2?family=Poppins:wght@300;400;500;600;700;800;900&display=swap');",
    'Lato' => "@import url('https://fonts.googleapis.com/css2?family=Lato:wght@300;400;700;900&display=swap');",
    'Inter' => "@import url('https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700;800;900&display=swap');"
  ];

  // Replace font import
  $cssContent = preg_replace(
    '/@import url\([^)]+\);/',
    $fontImports[$fontFamily],
    $cssContent,
    1
  );

  // Replace font-family in body (preserving !important)
  $cssContent = preg_replace(
    '/body\s*\{[^}]*font-family:[^;]+;[^}]*\}/',
    "body {\n  font-family: '$fontFamily', sans-serif !important;\n}",
    $cssContent
  );

  if (file_put_contents($cssFile, $cssContent)) {
    $successMessage = true;
  } else {
    $errorMessage = true;
  }
}

$cssContent = file_get_contents($cssFile);

$currentLogoSize = 'md';
if (preg_match('/\.awegreen-logo\s*\{[^}]*width:\s*(\d+)px;/', $cssContent, $matches)) {
  $width = $matches[1];
  if ($width == 28) $currentLogoSize = 'sm';
  elseif ($width == 40) $currentLogoSize = 'md';
  elseif ($width == 56) $currentLogoSize = 'lg';
}

// Extract current font family
$currentFont = 'Roboto';
if (preg_match('/font-family:\s*[\'"]([^\'",]+)[\'"]/', $cssContent, $matches)) {
  $currentFont = $matches[1];
}
?>

<!DOCTYPE html>
<html lang="en">

<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>Admin Settings - System Configuration</title>
  <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
  <link rel="stylesheet" href="general-CSS.css">
  <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.0/font/bootstrap-icons.css">
</head>

<body class="bg-light">


  <main id="main" class="container-xxl text-dark px-4 py-5 min-vh-100">
    <div class="admin-top-inventory d-flex justify-content-between align-items-center">
      <div>
        <h1 class="fs-36 mobile-fs-32">System Settings</h1>
        <p class="admin-top-desc">Configure your system's appearance and branding</p>
      </div>
    </div>

    <!-- Settings Card -->
    <div class="card shadow-sm border-0">
      <div class="card-body p-4">
        <form method="POST" action="">
          <!-- Logo Size Setting -->
          <div class="mb-5">
            <label class="form-label fw-500 fs-18 mb-3">
              <i class="bi bi-image me-2 green-text"></i>Logo Size
            </label>
            <p class="light-text fs-14 mb-3">Choose the size of the logo throughout the system</p>

            <div class="btn-group w-100" role="group">
              <input type="radio" class="btn-check" name="logo_size" id="size_sm" value="sm" <?= $currentLogoSize === 'sm' ? 'checked' : '' ?>>
              <label class="btn btn-outline-success" for="size_sm">
                <div class="py-2">
                  <i class="bi bi-circle fs-12 d-block mb-2"></i>
                  <span class="d-block fw-500">Small</span>
                  <span class="d-block fs-12 light-text">28px</span>
                </div>
              </label>

              <input type="radio" class="btn-check" name="logo_size" id="size_md" value="md" <?= $currentLogoSize === 'md' ? 'checked' : '' ?>>
              <label class="btn btn-outline-success" for="size_md">
                <div class="py-2">
                  <i class="bi bi-circle fs-18 d-block mb-2"></i>
                  <span class="d-block fw-500">Medium</span>
                  <span class="d-block fs-12 light-text">40px (Default)</span>
                </div>
              </label>

              <input type="radio" class="btn-check" name="logo_size" id="size_lg" value="lg" <?= $currentLogoSize === 'lg' ? 'checked' : '' ?>>
              <label class="btn btn-outline-success" for="size_lg">
                <div class="py-2">
                  <i class="bi bi-circle fs-24 d-block mb-2"></i>
                  <span class="d-block fw-500">Large</span>
                  <span class="d-block fs-12 light-text">56px</span>
                </div>
              </label>
            </div>
          </div>

          <div class="divider mb-5"></div>

          <!-- Font Family Setting -->
          <div class="mb-4">
            <label class="form-label fw-500 fs-18 mb-3">
              <i class="bi bi-fonts me-2 green-text"></i>Font Family
            </label>
            <p class="light-text fs-14 mb-3">Select the primary font for your system</p>

            <select class="form-select form-select-lg" name="font_family">
              <option value="Roboto" <?= $currentFont === 'Roboto' ? 'selected' : '' ?> style="font-family: 'Roboto', sans-serif;">Roboto (Default)</option>
              <option value="Open Sans" <?= $currentFont === 'Open Sans' ? 'selected' : '' ?> style="font-family: 'Open Sans', sans-serif;">Open Sans</option>
              <option value="Montserrat" <?= $currentFont === 'Montserrat' ? 'selected' : '' ?> style="font-family: 'Montserrat', sans-serif;">Montserrat</option>
              <option value="Poppins" <?= $currentFont === 'Poppins' ? 'selected' : '' ?> style="font-family: 'Poppins', sans-serif;">Poppins</option>
              <option value="Lato" <?= $currentFont === 'Lato' ? 'selected' : '' ?> style="font-family: 'Lato', sans-serif;">Lato</option>
              <option value="Inter" <?= $currentFont === 'Inter' ? 'selected' : '' ?> style="font-family: 'Inter', sans-serif;">Inter</option>
            </select>

            <div class="mt-3 p-3 light-dark-bg rounded">
              <p class="mb-2 fs-14 fw-500">Preview:</p>
              <p class="mb-1" id="fontPreview">The quick brown fox jumps over the lazy dog</p>
              <p class="mb-0 fs-12 light-text" id="fontPreviewSmall">ABCDEFGHIJKLMNOPQRSTUVWXYZ 0123456789</p>
            </div>
          </div>

          <div class="divider mb-4"></div>

          <!-- Action Buttons -->
          <div class="d-flex gap-3 justify-content-end">
            <button type="submit" class="btn btn-green px-4">
              <i class="fa-solid fa-floppy-disk me-2"></i>Save Changes
            </button>
          </div>
        </form>
      </div>
    </div>



  </main>



  <script src="https://code.jquery.com/jquery-3.7.1.min.js"></script>
  <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
  <script>
    // Show alert if save was successful
    <?php if (isset($successMessage)): ?>
      alert('Settings updated successfully! The changes will be applied across the system.');
      window.location.href = window.location.pathname;
    <?php endif; ?>

    <?php if (isset($errorMessage)): ?>
      alert('Failed to update settings. Please check file permissions.');
    <?php endif; ?>

    // Live font preview
    const fontSelect = document.querySelector('select[name="font_family"]');
    const fontPreview = document.getElementById('fontPreview');
    const fontPreviewSmall = document.getElementById('fontPreviewSmall');

    fontSelect.addEventListener('change', function() {
      const selectedFont = this.value;
      fontPreview.style.fontFamily = `'${selectedFont}', sans-serif`;
      fontPreviewSmall.style.fontFamily = `'${selectedFont}', sans-serif`;
    });

    // Set initial preview font
    const currentFont = fontSelect.value;
    fontPreview.style.fontFamily = `'${currentFont}', sans-serif`;
    fontPreviewSmall.style.fontFamily = `'${currentFont}', sans-serif`;
  </script>
</body>

</html>