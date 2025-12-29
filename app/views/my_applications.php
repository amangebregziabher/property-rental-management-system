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

// Fetch user's applications from database
$conn = get_db_connection();

// SQL to fetch applications along with property details
// CRITICAL: We filter by user_id to ensure tenants only see THEIR OWN applications
$sql = "SELECT ra.*, p.title as property_title, p.location as property_location, p.price as property_price
        FROM rental_applications ra
        JOIN properties p ON ra.property_id = p.id
        WHERE ra.user_id = ?
        ORDER BY ra.created_at DESC";

$stmt = mysqli_prepare($conn, $sql);
mysqli_stmt_bind_param($stmt, "i", $user_id);
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
    <?php if (empty($applications)): ?>
      <div class="glass-panel p-5 rounded-4 text-center">
        <i class="bi bi-file-earmark-text display-1 text-muted opacity-25 mb-4 d-block"></i>
        <h3 class="fw-bold">No applications found.</h3>
        <p class="text-secondary mb-4">You haven't submitted any rental applications yet.</p>
        <a href="tenant_view.php" class="btn btn-primary px-4 py-2 rounded-pill">Browse Properties</a>
      </div>
    <?php else: ?>
      <div class="row g-4">
        <?php foreach ($applications as $app): ?>
          <div class="col-12">
            <div class="card glass-panel border-0 rounded-4 overflow-hidden shadow-sm hover-up">
              <div class="row g-0">
                <div class="col-md-9 p-4">
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
                      <span class="badge <?php echo $status_class; ?> px-3 py-2 rounded-pill">
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
                      <div class="small text-muted text-uppercase fw-bold ls-1 mb-2">My Message</div>
                      <p class="small text-secondary mb-0"><?php echo htmlspecialchars($app['message']); ?></p>
                    </div>
                  <?php endif; ?>
                </div>
                <div
                  class="col-md-3 bg-light bg-opacity-5 d-flex flex-column justify-content-center p-4 border-start border-white border-opacity-10">
                  <h6 class="fw-bold mb-3">Applicant Info</h6>
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
                  <div class="mt-4">
                    <a href="property_details.php?id=<?php echo $app['property_id']; ?>"
                      class="btn btn-outline-primary btn-sm w-100 rounded-pill">View Property</a>
                  </div>
                </div>
              </div>
            </div>
          </div>
        <?php endforeach; ?>
      </div>
    <?php endif; ?>
  </div>

  <!-- Footer -->
  <footer class="footer py-5 glass-panel border-0 border-top mt-5">
    <div class="container text-center">
      <p class="text-muted mb-0">© 2024 PRMS - Your trusted partner in finding the perfect home.</p>
    </div>
  </footer>

  <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
</body>

</html>