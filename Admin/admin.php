<?php 
include 'include/header.php'; // Renders clean structure frame base layouts panels
include 'include/conn.php';         // Establishes relational database connections lines

// Pull Metrics counts statistics summary data lines securely
$total_events = mysqli_fetch_assoc(mysqli_query($conn, "SELECT COUNT(*) as count FROM events"))['count'];
$pub_events = mysqli_fetch_assoc(mysqli_query($conn, "SELECT COUNT(*) as count FROM events WHERE status='published'"))['count'];
$total_news = mysqli_fetch_assoc(mysqli_query($conn, "SELECT COUNT(*) as count FROM news"))['count'];
$pub_news = mysqli_fetch_assoc(mysqli_query($conn, "SELECT COUNT(*) as count FROM news WHERE status='published'"))['count'];
?>

<style>
    /* Spacing alignments across Desktop Viewport grids models */
    #main-content { 
        margin-left: var(--sidebar-width); 
        padding: 40px 30px; 
        margin-top: 70px; /* Aligns data sheets safely below top header bar lines limits */
        transition: all 0.3s ease-in-out;
    }
    
    .stat-card { border: none; border-radius: 14px; transition: all 0.25s ease; box-shadow: 0 4px 6px -1px rgba(0,0,0,0.02); }
    .stat-card:hover { transform: translateY(-4px); box-shadow: 0 10px 15px -3px rgba(0,0,0,0.05); }
    .preview-img { width: 44px; height: 44px; object-fit: cover; border-radius: 8px; border: 1px solid #e2e8f0; }
    
    /* System context operational status indicators badge mappings styling rules */
    .badge-published, .badge-Published { background-color: #d1e7dd !important; color: #0f5132 !important; font-weight: 600; }
    .badge-draft, .badge-Draft { background-color: #fff3cd !important; color: #856404 !important; font-weight: 600; }
    .badge-rejected, .badge-Rejected { background-color: #f8d7da !important; color: #842029 !important; font-weight: 600; }

    /* Space overrides matching mobile system display constraints layout parameters */
    @media (max-width: 992px) {
        #main-content { 
            margin-left: 0 !important; 
            padding: 25px 16px;
            margin-top: 70px;
        }
    }
</style>

<main id="main-content">
    <div class="container-fluid p-0">
        
        <div class="d-flex flex-column mb-4">
            <h2 class="fw-bold tracking-tight mb-1">Dashboard Overview</h2>
            <p class="text-muted small mb-0">Manage system events updates, notifications logs records configuration channels.</p>
        </div>

        <div class="row g-3 mb-5">
            <div class="col-12 col-sm-6 col-lg-3">
                <div class="card stat-card bg-primary text-white p-3 h-100">
                    <div class="d-flex justify-content-between align-items-center">
                        <div><h6 class="mb-1 text-white-50 small fw-medium">Total Events</h6><h2 class="fw-bold m-0"><?php echo $total_events; ?></h2></div>
                        <div class="bg-white bg-opacity-10 p-3 rounded-circle"><i class="fas fa-calendar-alt fa-lg"></i></div>
                    </div>
                </div>
            </div>
            <div class="col-12 col-sm-6 col-lg-3">
                <div class="card stat-card bg-success text-white p-3 h-100">
                    <div class="d-flex justify-content-between align-items-center">
                        <div><h6 class="mb-1 text-white-50 small fw-medium">Published Events</h6><h2 class="fw-bold m-0"><?php echo $pub_events; ?></h2></div>
                        <div class="bg-white bg-opacity-10 p-3 rounded-circle"><i class="fas fa-check-circle fa-lg"></i></div>
                    </div>
                </div>
            </div>
            <div class="col-12 col-sm-6 col-lg-3">
                <div class="card stat-card bg-info text-white p-3 h-100">
                    <div class="d-flex justify-content-between align-items-center">
                        <div><h6 class="mb-1 text-white-50 small fw-medium">Total News</h6><h2 class="fw-bold m-0"><?php echo $total_news; ?></h2></div>
                        <div class="bg-white bg-opacity-10 p-3 rounded-circle"><i class="fas fa-newspaper fa-lg"></i></div>
                    </div>
                </div>
            </div>
            <div class="col-12 col-sm-6 col-lg-3">
                <div class="card stat-card bg-warning text-dark p-3 h-100">
                    <div class="d-flex justify-content-between align-items-center">
                        <div><h6 class="mb-1 text-dark-50 small fw-medium">Published News</h6><h2 class="fw-bold m-0"><?php echo $pub_news; ?></h2></div>
                        <div class="bg-dark bg-opacity-10 p-3 rounded-circle"><i class="fas fa-bullhorn fa-lg"></i></div>
                    </div>
                </div>
            </div>
        </div>

        <div class="card border-0 shadow-sm rounded-4">
            <div class="card-header bg-white py-3 border-bottom border-light">
                <h5 class="mb-0 fw-bold text-secondary">Your Hosted Events</h5>
            </div>
            <div class="card-body p-0">
                <div class="table-responsive">
                    <table class="table table-hover align-middle mb-0">
                        <thead class="table-light text-secondary small text-uppercase">
                            <tr>
                                <th class="ps-4 py-3">Preview</th>
                                <th class="py-3">Event Title & Location</th>
                                <th class="py-3">Category</th>
                                <th class="py-3">Date & Time</th>
                                <th class="py-3">Status</th>
                                <th class="text-end pe-4 py-3">Action</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php
                            $user_events = mysqli_query($conn, "SELECT * FROM events ORDER BY created_at DESC");
                            while($row = mysqli_fetch_assoc($user_events)): ?>
                            <tr>
                                <td class="ps-4">
                                    <img src="../uploads/<?php echo $row['image']; ?>" class="preview-img" alt="" onerror="this.src='../assets/image/logo.png'">
                                </td>
                                <td>
                                    <div class="fw-bold text-dark"><?php echo htmlspecialchars($row['title']); ?></div>
                                    <small class="text-muted"><i class="fas fa-map-marker-alt me-1 text-secondary-subtle"></i> <?php echo htmlspecialchars($row['Location']); ?></small>
                                </td>
                                <td><span class="badge bg-light text-secondary border px-2.5 py-1.5"><?php echo htmlspecialchars($row['category']); ?></span></td>
                                <td>
                                    <div class="small fw-semibold"><?php echo $row['event_date']; ?></div>
                                    <small class="text-muted"><?php echo $row['Time']; ?></small>
                                </td>
                                <td><span class="badge badge-<?php echo strtolower($row['status']); ?> px-2.5 py-1.5 rounded-pill"><?php echo ucfirst($row['status']); ?></span></td>
                                <td class="text-end pe-4">
                                    <a href="edit_events.php?id=<?php echo $row['id']; ?>" class="btn btn-sm btn-light border rounded-3 p-2 text-secondary" aria-label="Modify Row Entry Item Log"><i class="fas fa-edit"></i></a>
                                </td>
                            </tr>
                            <?php endwhile; ?>
                        </tbody>
                    </table>
                </div>
            </div>
        </div>

    </div>
</main>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>

<script>
  document.addEventListener("DOMContentLoaded", function () {
    const sidebarToggleBtn = document.getElementById("sidebarToggleBtn");
    const sidebar = document.getElementById("sidebar");

    if (sidebarToggleBtn && sidebar) {
      // Toggle sidebar panel active state shifts visibility variables values
      sidebarToggleBtn.addEventListener("click", function (e) {
        e.stopPropagation(); 
        sidebar.classList.toggle("active");
      });

      // Close the side panel automatically if a user targets layout spaces outside its container boundaries on mobile viewports
      document.addEventListener("click", function (e) {
        if (window.innerWidth <= 992) {
          if (!sidebar.contains(e.target) && !sidebarToggleBtn.contains(e.target)) {
            sidebar.classList.remove("active");
          }
        }
      });
    }
  });
</script>
</body>
</html>