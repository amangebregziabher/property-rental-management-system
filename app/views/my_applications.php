<?php
session_start();

// Access control: Only logged in users (tenants) can access this page
if (!isset($_SESSION['user_id'])) {
  header('Location: login.php?redirect_to=' . urlencode($_SERVER['REQUEST_URI']));
  exit();
}

// Include database connection
require_once __DIR__ . '/../../config/db_connect.php';

$user_id = $_SESSION['user_id'];
$user_name = $_SESSION['user_name'];
$user_role = $_SESSION['user_role'];

// Basic info already extracted from session
?>
<!DOCTYPE html>
<html lang="en">

<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>My Applications - PRMS</title>
  <!-- Bootstrap CSS -->
  <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
  <!-- Custom CSS -->
  <link rel="stylesheet" href="../../public/assets/css/style.css?v=<?php echo time(); ?>">
  <!-- Bootstrap Icons -->
  <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.1/font/bootstrap-icons.css">
  <!-- Google Fonts -->
  <link href="https://fonts.googleapis.com/css2?family=Outfit:wght@300;400;600;700&display=swap" rel="stylesheet">
</head>

<body class="tenant-portal">
  <!-- Navigation -->
  <nav class="navbar navbar-expand-lg navbar-dark glass-nav sticky-top">
    <div class="container">
      <a class="navbar-brand text-gradient fs-3 fw-bold" href="../../public/index.php">PRMS</a>
      <button class="navbar-toggler" type="button" data-bs-toggle="collapse" data-bs-target="#navbarNav">
        <span class="navbar-toggler-icon"></span>
      </button>
      <div class="collapse navbar-collapse" id="navbarNav">
        <ul class="navbar-nav ms-auto align-items-center">
          <li class="nav-item">
            <a class="nav-link" href="tenant_view.php">Find Home</a>
          </li>
          <li class="nav-item">
            <a class="nav-link active" href="my_applications.php">My Applications</a>
          </li>
          <li class="nav-item dropdown ms-lg-3">
            <a class="nav-link dropdown-toggle d-flex align-items-center gap-2" href="#" id="navbarDropdown"
              role="button" data-bs-toggle="dropdown">
              <i class="bi bi-person-circle fs-5"></i> <?php echo htmlspecialchars($user_name); ?>
            </a>
            <ul class="dropdown-menu dropdown-menu-end glass-panel border-0 shadow-sm mt-2">
              <?php if ($user_role === 'owner' || $user_role === 'admin'): ?>
                <li><a class="dropdown-item" href="property_list.php">Owner Dashboard</a></li>
                <li><a class="dropdown-item" href="manage_applications.php">Manage Applications</a></li>
                <li>
                  <hr class="dropdown-divider">
                </li>
              <?php endif; ?>
              <li><a class="dropdown-item text-danger"
                  href="../controllers/auth_controller.php?action=logout">Logout</a></li>
            </ul>
          </li>
        </ul>
      </div>
    </div>
  </nav>

  <!-- Header -->
  <header class="page-header py-5 bg-gradient-primary text-white position-relative overflow-hidden mb-5">
    <div class="container position-relative z-1">
      <h1 class="display-4 fw-bold mb-3">My Applications</h1>
      <p class="lead opacity-75">Track the status of your rental applications in real-time.</p>
    </div>
    <div class="bg-blur"></div>
  </header>

  <div class="container mb-5 pb-5">
    <div id="applications-loading" class="text-center p-5">
      <div class="spinner-border text-primary" role="status">
        <span class="visually-hidden">Loading...</span>
      </div>
      <p class="mt-3 text-muted">Loading your applications...</p>
    </div>

    <div id="no-applications" class="glass-panel p-5 rounded-4 text-center d-none">
      <i class="bi bi-file-earmark-text display-1 text-muted opacity-25 mb-4 d-block"></i>
      <h3 class="fw-bold">No applications found.</h3>
      <p class="text-secondary mb-4">You haven't submitted any rental applications yet.</p>
      <a href="tenant_view.php" class="btn btn-primary px-4 py-2 rounded-pill">Browse Properties</a>
    </div>

    <div id="applications-list" class="row g-4">
      <!-- Dynamic applications will be injected here -->
    </div>
  </div>

  <script>
    document.addEventListener('DOMContentLoaded', function () {
      const loadingEl = document.getElementById('applications-loading');
      const noAppsEl = document.getElementById('no-applications');
      const listEl = document.getElementById('applications-list');

      // Fetch applications from the backend API
      fetch('../../api/get_tenant_applications.php')
        .then(response => response.json())
        .then(data => {
          loadingEl.classList.add('d-none');

          if (!data.success) {
            console.error('API Error:', data.message);
            listEl.innerHTML = `<div class="col-12 text-center text-danger p-5 glass-panel rounded-4">
              <i class="bi bi-exclamation-triangle display-4 mb-3"></i>
              <p>${data.message || 'Failed to load applications.'}</p>
            </div>`;
            return;
          }

          if (!data.data || data.data.length === 0) {
            noAppsEl.classList.remove('d-none');
          } else {
            renderApplications(data.data);
          }
        })
        .catch(error => {
          console.error('Fetch error:', error);
          loadingEl.innerHTML = `
            <div class="text-danger p-5">
              <i class="bi bi-wifi-off display-4 mb-3"></i>
              <p>Unable to connect to the server. Please check your connection and try again.</p>
              <button class="btn btn-outline-primary btn-sm rounded-pill" onclick="window.location.reload()">Retry</button>
            </div>
          `;
        });

      function renderApplications(apps) {
        listEl.innerHTML = apps.map(app => {
          const statusClass = getStatusClass(app.application_status);
          const date = new Date(app.application_date).toLocaleDateString('en-US', {
            month: 'short',
            day: '2-digit',
            year: 'numeric'
          });

          // Format price (Handle possible null or undefined property details)
          const priceVal = app.property_price ? parseFloat(app.property_price) : 0;
          const price = new Intl.NumberFormat('en-US', {
            style: 'currency',
            currency: 'USD',
            maximumFractionDigits: 0
          }).format(priceVal);

          const appId = app.application_id.toString().padStart(4, '0');

          let messageHtml = '';
          if (app.application_message) {
            messageHtml = `
              <div class="mt-4 p-3 bg-light bg-opacity-10 rounded-3 border border-white border-opacity-10">
                <div class="small text-muted text-uppercase fw-bold ls-1 mb-2">My Message</div>
                <p class="small text-secondary mb-0">${escapeHtml(app.application_message)}</p>
              </div>
            `;
          }

          return `
            <div class="col-12">
              <div class="card glass-panel border-0 rounded-4 overflow-hidden shadow-sm hover-up">
                <div class="row g-0">
                  <div class="col-md-9 p-4">
                    <div class="d-flex justify-content-between align-items-start mb-3">
                      <div>
                        <h5 class="fw-bold mb-1">${escapeHtml(app.property_title || 'Unknown Property')}</h5>
                        <p class="text-muted small mb-0">
                          <i class="bi bi-geo-alt-fill text-primary"></i>
                          ${escapeHtml(app.property_location || 'N/A')}
                        </p>
                      </div>
                      <div class="text-end">
                        <span class="badge ${statusClass} px-3 py-2 rounded-pill text-uppercase" style="font-size: 0.7rem;">
                          ${app.application_status}
                        </span>
                      </div>
                    </div>

                    <div class="row g-3">
                      <div class="col-sm-4">
                        <div class="small text-muted text-uppercase fw-bold ls-1 mb-1" style="font-size: 0.65rem;">Monthly Rent</div>
                        <div class="fw-bold text-primary">${price}</div>
                      </div>
                      <div class="col-sm-4">
                        <div class="small text-muted text-uppercase fw-bold ls-1 mb-1" style="font-size: 0.65rem;">Submitted On</div>
                        <div class="fw-bold fs-6">${date}</div>
                      </div>
                      <div class="col-sm-4">
                        <div class="small text-muted text-uppercase fw-bold ls-1 mb-1" style="font-size: 0.65rem;">Application Ref</div>
                        <div class="fw-bold fs-6">#APP-${appId}</div>
                      </div>
                    </div>

                    ${messageHtml}
                  </div>
                  <div class="col-md-3 bg-light bg-opacity-5 d-flex flex-column justify-content-center p-4 border-start border-white border-opacity-10">
                    <h6 class="fw-bold mb-3 small text-uppercase ls-1">Applicant Info</h6>
                    <div class="small mb-2">
                      <div class="text-muted" style="font-size: 0.75rem;">Name</div>
                      <div class="fw-semibold">${escapeHtml(app.applicant_name || 'N/A')}</div>
                    </div>
                    <div class="small mb-2">
                      <div class="text-muted" style="font-size: 0.75rem;">Email</div>
                      <div class="text-break">${escapeHtml(app.applicant_email || 'N/A')}</div>
                    </div>
                    <div class="small">
                      <div class="text-muted" style="font-size: 0.75rem;">Phone</div>
                      <div>${escapeHtml(app.applicant_phone || 'N/A')}</div>
                    </div>
                    <div class="mt-4">
                      <a href="property_details.php?id=${app.property_id || ''}" 
                         class="btn btn-outline-primary btn-sm w-100 rounded-pill ${!app.property_id ? 'disabled' : ''}">
                        View Property
                      </a>
                    </div>
                  </div>
                </div>
              </div>
            </div>
          `;
        }).join('');
      }

      function getStatusClass(status) {
        switch (status) {
          case 'Approved': return 'bg-success';
          case 'Rejected': return 'bg-danger';
          case 'Pending': return 'bg-warning text-dark';
          default: return 'bg-secondary';
        }
      }

      function escapeHtml(text) {
        if (!text) return '';
        const div = document.createElement('div');
        div.textContent = text;
        return div.innerHTML;
      }
    });
  </script>

  <!-- Footer -->
  <footer class="footer py-5 glass-panel border-0 border-top mt-5">
    <div class="container text-center">
      <p class="text-muted mb-0">© 2024 PRMS - Your trusted partner in finding the perfect home.</p>
    </div>
  </footer>

  <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
</body>

</html>