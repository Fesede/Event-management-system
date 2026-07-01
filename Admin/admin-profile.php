
    <style>
        :root { --sidebar-width: 250px; }
        
        
        #main-content { margin-left: var(--sidebar-width); padding: 40px; }

        .profile-cover {
            height: 160px;
            background: linear-gradient(to right, #1e3c72, #2a5298);
            border-radius: 15px 15px 0 0;
        }
        .profile-avatar-container {
            margin-top: -60px;
            padding-left: 30px;
        }
        .profile-avatar {
            width: 120px;
            height: 120px;
            border-radius: 50%;
            border: 5px solid #fff;
            object-fit: cover;
            box-shadow: 0 5px 15px rgba(0,0,0,0.1);
        }
        .card-custom {
            border: none;
            border-radius: 15px;
            box-shadow: 0 0.125rem 0.25rem rgba(0, 0, 0, 0.075);
        }
    </style>

<?php 
include 'include/header.php';
include 'include/A_nav.php';
?>

    
    <!-- Main Content Area -->
    <main id="main-content">
        <div class="container-fluid">
            
            <!-- Profile Header Card -->
            <div class="card card-custom mb-4 overflow-hidden">
                <div class="profile-cover"></div>
                <div class="profile-avatar-container d-flex align-items-end mb-4">
                    <img src="https://i.pravatar.cc/150?u=admin" alt="Admin" class="profile-avatar">
                    <div class="ms-3 mb-2">
                        <h3 class="fw-bold mb-0">Abebe Kebede</h3>
                        <span class="badge bg-primary">Super Admin</span>
                    </div>
                </div>
            </div>

            <div class="row">
                <!-- Personal Info Column -->
                <div class="col-lg-8">
                    <div class="card card-custom p-4 mb-4">
                        <h5 class="fw-bold mb-4">Edit Personal Information</h5>
                        <form>
                            <div class="row g-3">
                                <div class="col-md-6">
                                    <label class="form-label small fw-bold">Full Name</label>
                                    <input type="text" class="form-control" value="Abebe Kebede">
                                </div>
                                <div class="col-md-6">
                                    <label class="form-label small fw-bold">Email Address</label>
                                    <input type="email" class="form-control" value="abebe.k@eventhub.et">
                                </div>
                                <div class="col-md-6">
                                    <label class="form-label small fw-bold">Phone Number</label>
                                    <input type="text" class="form-control" value="+251 911 22 33 44">
                                </div>
                                <div class="col-md-6">
                                    <label class="form-label small fw-bold">Location</label>
                                    <input type="text" class="form-control" value="Addis Ababa, Ethiopia">
                                </div>
                                <div class="col-12">
                                    <label class="form-label small fw-bold">Short Bio</label>
                                    <textarea class="form-control" rows="3">Lead administrator for EventHub Ethiopia. Passionate about community building and historical preservation.</textarea>
                                </div>
                            </div>
                            <div class="mt-4">
                                <button type="submit" class="btn btn-primary px-4">Update Profile</button>
                            </div>
                        </form>
                    </div>

                    <div class="card card-custom p-4">
                        <h5 class="fw-bold mb-4 text-danger">Change Password</h5>
                        <form>
                            <div class="row g-3">
                                <div class="col-md-4">
                                    <label class="form-label small fw-bold">Current Password</label>
                                    <input type="password" class="form-control">
                                </div>
                                <div class="col-md-4">
                                    <label class="form-label small fw-bold">New Password</label>
                                    <input type="password" class="form-control">
                                </div>
                                <div class="col-md-4">
                                    <label class="form-label small fw-bold">Confirm New Password</label>
                                    <input type="password" class="form-control">
                                </div>
                            </div>
                            <button type="submit" class="btn btn-outline-danger mt-4">Change Password</button>
                        </form>
                    </div>
                </div>

                <!-- Stats/Activity Column -->
                <div class="col-lg-4">
                    <div class="card card-custom p-4 mb-4 text-center">
                        <h6 class="text-muted fw-bold small text-uppercase">Account Stats</h6>
                        <div class="row mt-3">
                            <div class="col-6 border-end">
                                <h4 class="fw-bold mb-0">45</h4>
                                <small class="text-muted">Posts</small>
                            </div>
                            <div class="col-6">
                                <h4 class="fw-bold mb-0">12</h4>
                                <small class="text-muted">Events</small>
                            </div>
                        </div>
                    </div>

                    <div class="card card-custom p-4">
                        <h6 class="fw-bold mb-3">Recent Activity</h6>
                        <div class="list-group list-group-flush small">
                            <div class="list-group-item px-0 border-0">
                                <i class="fas fa-edit text-primary me-2"></i> Updated "Leadership Workshop"
                                <div class="text-muted" style="font-size: 0.75rem;">1 hour ago</div>
                            </div>
                            <div class="list-group-item px-0 border-0">
                                <i class="fas fa-user-plus text-success me-2"></i> Invited new Editor
                                <div class="text-muted" style="font-size: 0.75rem;">Yesterday</div>
                            </div>
                            <div class="list-group-item px-0 border-0">
                                <i class="fas fa-lock text-warning me-2"></i> Changed Security Settings
                                <div class="text-muted" style="font-size: 0.75rem;">May 04, 2026</div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </main>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>