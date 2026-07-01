<?php 
include 'include/conn.php';
include 'include/header.php'; // ከቀሩት ገጾች መዋቅር ጋር እንዲገጥም ተስተካክሏል
include 'include/nav.php';
?>

<style>
    body { 
        background-color: #f8fafc; 
        font-family: 'Inter', sans-serif; 
    }
    
    /* 💡 የግራ ሳይድባር ክፍተትን ሙሉ በሙሉ የሚያጠፋ መያዣ */
    .settings-wrapper-custom {
        margin: 0 !important;
        padding: 0 !important;
    }

    .settings-card { 
        border: none; 
        border-radius: 16px; 
        box-shadow: 0 10px 25px -5px rgba(0, 0, 0, 0.05); 
        background: white;
    }

    /* የሴቲንግ ታቦች ማሻሻያ */
    .nav-pills .nav-link { 
        color: #64748b; 
        font-weight: 500; 
        padding: 12px 20px;
        border-radius: 10px;
        transition: all 0.2s ease;
        margin-bottom: 5px;
    }
    
    .nav-pills .nav-link:hover {
        background-color: #f1f5f9;
        color: #1e293b;
    }

    .nav-pills .nav-link.active { 
        background-color: #0d6efd !important; 
        color: white !important; 
    }

    .form-control[readonly] {
        background-color: #f8fafc;
        border-color: #e2e8f0;
        color: #334155;
    }
</style>

<div class="settings-wrapper-custom">
    
    <div class="mb-4">
        <h2 class="fw-bold text-dark mb-1">Dashboard Overview</h2>
        <h3 class="fw-bold text-dark mb-1" style="font-size: 1.75rem;">System Settings</h3>
        <p class="text-muted mb-0">Configure your website's global parameters and security.</p>
    </div>

    <div class="row">
        <div class="col-lg-3 mb-4">
            <div class="card settings-card p-3">
                <div class="nav flex-column nav-pills" id="settings-tabs" role="tablist">
                    <button class="nav-link active text-start" data-bs-toggle="pill" data-bs-target="#general" type="button">
                        <i class="fas fa-globe me-2"></i> General
                    </button>
                    <button class="nav-link text-start" data-bs-toggle="pill" data-bs-target="#appearance" type="button">
                        <i class="fas fa-paint-brush me-2"></i> Appearance
                    </button>
                    <button class="nav-link text-start" data-bs-toggle="pill" data-bs-target="#notifications" type="button">
                        <i class="fas fa-bell me-2"></i> Notifications
                    </button>
                </div>
            </div>
        </div>

        <div class="col-lg-9">
            <div class="tab-content">
                
                <div class="tab-pane fade show active" id="general">
                    <div class="card settings-card p-4">
                        <h5 class="fw-bold mb-4 text-dark"><i class="fas fa-sliders-h text-primary me-2"></i>General Configuration</h5>
                        <form>
                            <div class="mb-3 row align-items-center">
                                <label class="col-sm-3 col-form-label fw-semibold text-secondary">Site Name</label>
                                <div class="col-sm-9">
                                    <input type="text" class="form-control py-2" value="St.Raguel Ethiopia" readonly>
                                </div>
                            </div>
                            <div class="mb-4 row align-items-center">
                                <label class="col-sm-3 col-form-label fw-semibold text-secondary">Admin Email</label>
                                <div class="col-sm-9">
                                    <input type="email" class="form-control py-2" value="dhsraguelabssedu21@gmail.com" readonly>
                                </div>
                            </div>
                            <hr class="my-4" style="border-color: #e2e8f0;">
                            <div class="d-flex justify-content-start">
                                <button type="button" class="btn btn-primary px-4 py-2 fw-bold rounded-pill shadow-sm" style="background-color: #0d6efd; border-color: #0d6efd;">Save Changes</button>
                            </div>
                        </form>
                    </div>
                </div>

                <div class="tab-pane fade" id="appearance">
                    <div class="card settings-card p-5 text-center">
                        <div class="rounded-circle bg-light d-flex align-items-center justify-content-center mx-auto mb-3" style="width: 80px; height: 80px;">
                            <i class="fas fa-palette fa-2x text-muted"></i>
                        </div>
                        <h5 class="fw-bold text-dark">Theme Settings</h5>
                        <p class="text-muted mx-auto mb-0" style="max-width: 400px;">Dark mode and custom branding configurations are coming soon in the v2.0 update.</p>
                    </div>
                </div>

                <div class="tab-pane fade" id="notifications">
                    <div class="card settings-card p-5 text-center">
                        <div class="rounded-circle bg-light d-flex align-items-center justify-content-center mx-auto mb-3" style="width: 80px; height: 80px;">
                            <i class="fas fa-bell-slash fa-2x text-muted"></i>
                        </div>
                        <h5 class="fw-bold text-dark">Alert System</h5>
                        <p class="text-muted mx-auto mb-0" style="max-width: 400px;">Real-time email and push notification preferences are under development.</p>
                    </div>
                </div>

            </div>
        </div>
    </div>
</div>

</main> <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>