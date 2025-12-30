<?php
session_start();

// Access control: Only owners and admins can access this page
if (!isset($_SESSION['user_id']) || ($_SESSION['user_role'] !== 'owner' && $_SESSION['user_role'] !== 'admin')) {
  header('Location: login.php?redirect_to=' . urlencode($_SERVER['REQUEST_URI']));
  exit();
}

// Include database connection
require_once __DIR__ . '/../../config/db_connect.php';

$user_id = $_SESSION['user_id'];
$user_role = $_SESSION['user_role'];

// Fetch applications for properties owned by this user
$conn = get_db_connection();

// SQL to fetch applications along with property details
// If admin, show all applications. If owner, show only applications for their properties.
if ($user_role === 'admin') {
  $sql = "SELECT ra.*, p.title as property_title, p.location as property_location, p.price as property_price
            FROM rental_applications ra
            JOIN properties p ON ra.property_id = p.id
            ORDER BY ra.created_at DESC";
  $stmt = mysqli_prepare($conn, $sql);
} else {
  $sql = "SELECT ra.*, p.title as property_title, p.location as property_location, p.price as property_price
            FROM rental_applications ra
            JOIN properties p ON ra.property_id = p.id
            WHERE p.owner_id = ?
            ORDER BY ra.created_at DESC";
  $stmt = mysqli_prepare($conn, $sql);
  mysqli_stmt_bind_param($stmt, "i", $user_id);
}

mysqli_stmt_execute($stmt);
$result = mysqli_stmt_get_result($stmt);

$applications = [];
while ($row = mysqli_fetch_assoc($result)) {
  $applications[] = $row;
}

close_db_connection($conn);
?>
<!DOCTYPE html>
<html lang="en">

<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>Manage Applications - PRMS</title>
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
            <a class="nav-link" href="property_list.php">Inventory</a>
          </li>
          <li class="nav-item">
            <a class="nav-link active" href="manage_applications.php">Applications</a>
          </li>
          <li class="nav-item dropdown ms-lg-3">
            <a class="nav-link dropdown-toggle d-flex align-items-center gap-2" href="#" id="navbarDropdown"
              role="button" data-bs-toggle="dropdown">
              <i class="bi bi-person-circle fs-5"></i> <?php echo htmlspecialchars($_SESSION['user_name']); ?>
            </a>
            <ul class="dropdown-menu dropdown-menu-end glass-panel border-0 shadow-sm mt-2">
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
      <h1 class="display-4 fw-bold mb-3">Tenant Applications</h1>
      <p class="lead opacity-75">Review and manage rental applications for your properties.</p>
    </div>
    <div class="bg-blur"></div>
  </header>

  <div class="container mb-5 pb-5">
    <?php if (empty($applications)): ?>
      <div class="glass-panel p-5 rounded-4 text-center">
        <i class="bi bi-file-earmark-person display-1 text-muted opacity-25 mb-4 d-block"></i>
        <h3 class="fw-bold">No applications yet.</h3>
        <p class="text-secondary mb-4">You haven't received any rental applications for your properties.</p>
        <a href="property_list.php" class="btn btn-primary px-4 py-2 rounded-pill">Manage Inventory</a>
      </div>
    <?php else: ?>
      <div class="row g-4">
        <?php foreach ($applications as $app): ?>
          <div class="col-12" id="app-row-<?php echo $app['id']; ?>">
            <div class="card glass-panel border-0 rounded-4 overflow-hidden shadow-sm hover-up">
              <div class="row g-0">
                <div class="col-md-8 p-4 border-end border-white border-opacity-10">
                  <div class="d-flex justify-content-between align-items-start mb-3">
                    <div>
                      <h5 class="fw-bold mb-1"><?php echo htmlspecialchars($app['property_title']); ?></h5>
                      <p class="text-muted small mb-0">
                        <i class="bi bi-geo-alt-fill text-primary"></i>
                        <?php echo htmlspecialchars($app['property_location']); ?>
                      </p>
                    </div>
                    <div class="text-end">
                      <?php
                      $status_class = 'bg-warning text-dark';
                      if ($app['status'] === 'Approved')
                        $status_class = 'bg-success';
                      if ($app['status'] === 'Rejected')
                        $status_class = 'bg-danger';
                      ?>
                      <span class="badge <?php echo $status_class; ?> px-3 py-2 rounded-pill status-badge">
                        <?php echo $app['status']; ?>
                      </span>
                    </div>
                  </div>

                  <div class="row g-3">
                    <div class="col-sm-4">
                      <div class="small text-muted text-uppercase fw-bold ls-1 mb-1">Monthly Rent</div>
                      <div class="fw-bold text-primary">$<?php echo number_format($app['property_price'], 0); ?></div>
                    </div>
                    <div class="col-sm-4">
                      <div class="small text-muted text-uppercase fw-bold ls-1 mb-1">Submitted On</div>
                      <div class="fw-bold"><?php echo date('M d, Y', strtotime($app['created_at'])); ?></div>
                    </div>
                    <div class="col-sm-4">
                      <div class="small text-muted text-uppercase fw-bold ls-1 mb-1">Application Ref</div>
                      <div class="fw-bold">#APP-<?php echo str_pad($app['id'], 4, '0', STR_PAD_LEFT); ?></div>
                    </div>
                  </div>

                  <?php if ($app['message']): ?>
                    <div class="mt-4 p-3 bg-light bg-opacity-10 rounded-3 border border-white border-opacity-10">
                      <div class="small text-muted text-uppercase fw-bold ls-1 mb-2">Applicant's Message</div>
                      <p class="small text-secondary mb-0"><?php echo htmlspecialchars($app['message']); ?></p>
                    </div>
                  <?php endif; ?>
                </div>
                <div class="col-md-4 bg-light bg-opacity-5 d-flex flex-column justify-content-between p-4">
                  <div>
                    <h6 class="fw-bold mb-3">Applicant Details</h6>
                    <div class="small mb-2">
                      <div class="text-muted">Name</div>
                      <div><?php echo htmlspecialchars($app['applicant_name']); ?></div>
                    </div>
                    <div class="small mb-2">
                      <div class="text-muted">Email</div>
                      <div class="text-break"><?php echo htmlspecialchars($app['applicant_email']); ?></div>
                    </div>
                    <div class="small">
                      <div class="text-muted">Phone</div>
                      <div><?php echo htmlspecialchars($app['applicant_phone']); ?></div>
                    </div>
                  </div>

                  <div class="mt-4 pt-4 border-top border-white border-opacity-10">
                    <?php if ($app['status'] === 'Pending'): ?>
                      <div class="d-flex gap-2 action-buttons">
                        <button onclick="updateStatus(<?php echo $app['id']; ?>, 'approve')"
                          class="btn btn-success flex-grow-1 rounded-pill fw-bold">
                          <i class="bi bi-check-lg"></i> Approve
                        </button>
                        <button onclick="updateStatus(<?php echo $app['id']; ?>, 'reject')"
                          class="btn btn-danger flex-grow-1 rounded-pill fw-bold">
                          <i class="bi bi-x-lg"></i> Reject
                        </button>
                      </div>
                    <?php else: ?>
                      <div class="text-center">
                        <button onclick="resetStatus(<?php echo $app['id']; ?>)"
                          class="btn btn-outline-secondary btn-sm rounded-pill px-4">
                          Change Decision
                        </button>
                      </div>
                    <?php endif; ?>
                  </div>
                </div>
              </div>
            </div>
          </div>
        <?php endforeach; ?>
      </div>
    <?php endif; ?>
  </div>

  <!-- Toast Notification -->
  <div class="toast-container position-fixed bottom-0 end-0 p-3">
    <div id="statusToast" class="toast align-items-center text-white border-0" role="alert" aria-live="assertive"
      aria-atomic="true">
      <div class="d-flex">
        <div class="toast-body" id="toastMessage"></div>
        <button type="button" class="btn-close btn-close-white me-2 m-auto" data-bs-dismiss="toast"
          aria-label="Close"></button>
      </div>
    </div>
  </div>

  <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
  <script>
    async function updateStatus(id, action) {
      const row = document.getElementById(`app-row-${id}`);
      const buttons = row.querySelector('.action-buttons');
      const originalButtons = buttons ? buttons.innerHTML : '';

      if (buttons) {
        buttons.innerHTML = '<div class="text-center w-100"><span class="spinner-border spinner-border-sm"></span></div>';
      }

      try {
        const response = await fetch(`../controllers/application_controller.php?id=${id}&action=${action}`);
        const result = await response.json();

        const toast = new bootstrap.Toast(document.getElementById('statusToast'));
        const toastEl = document.getElementById('statusToast');
        const toastMsg = document.getElementById('toastMessage');

        if (result.success) {
          toastEl.className = 'toast align-items-center text-white border-0 bg-success';
          toastMsg.innerText = result.message;

          // Update UI without reload
          const badge = row.querySelector('.status-badge');
          badge.innerText = action === 'approve' ? 'Approved' : 'Rejected';
          badge.className = `badge ${action === 'approve' ? 'bg-success' : 'bg-danger'} px-3 py-2 rounded-pill status-badge`;

          const actionArea = row.querySelector('.col-md-4 .mt-4');
          actionArea.innerHTML = `
                        <div class="text-center">
                            <button onclick="resetStatus(${id})" 
                                    class="btn btn-outline-secondary btn-sm rounded-pill px-4">
                                Change Decision
                            </button>
                        </div>
                    `;
        } else {
          toastEl.className = 'toast align-items-center text-white border-0 bg-danger';
          toastMsg.innerText = result.message;
          if (buttons) buttons.innerHTML = originalButtons;
        }
        toast.show();
      } catch (error) {
        console.error('Error:', error);
        if (buttons) buttons.innerHTML = originalButtons;
      }
    }

    async function resetStatus(id) {
      const row = document.getElementById(`app-row-${id}`);
      const actionArea = row.querySelector('.col-md-4 .mt-4');
      const originalContent = actionArea.innerHTML;

      actionArea.innerHTML = '<div class="text-center w-100"><span class="spinner-border spinner-border-sm"></span></div>';

      try {
        const response = await fetch(`../controllers/application_controller.php?id=${id}&action=reset`);
        const result = await response.json();

        if (result.success) {
          // Update UI without reload
          const badge = row.querySelector('.status-badge');
          badge.innerText = 'Pending';
          badge.className = 'badge bg-warning text-dark px-3 py-2 rounded-pill status-badge';

          actionArea.innerHTML = `
            <div class="d-flex gap-2 action-buttons">
              <button onclick="updateStatus(${id}, 'approve')"
                class="btn btn-success flex-grow-1 rounded-pill fw-bold">
                <i class="bi bi-check-lg"></i> Approve
              </button>
              <button onclick="updateStatus(${id}, 'reject')"
                class="btn btn-danger flex-grow-1 rounded-pill fw-bold">
                <i class="bi bi-x-lg"></i> Reject
              </button>
            </div>
          `;

          const toast = new bootstrap.Toast(document.getElementById('statusToast'));
          const toastEl = document.getElementById('statusToast');
          const toastMsg = document.getElementById('toastMessage');
          toastEl.className = 'toast align-items-center text-white border-0 bg-secondary';
          toastMsg.innerText = result.message;
          toast.show();
        } else {
          alert(result.message);
          actionArea.innerHTML = originalContent;
        }
      } catch (error) {
        console.error('Error:', error);
        actionArea.innerHTML = originalContent;
      }
    }
  </script>
</body>

</html>