<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Register - SLITPA</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="assets/css/style.css">
</head>
<body>
    <?php include 'includes/header.php'; ?>

    <div class="container py-5 mt-5">
        <div class="row justify-content-center">
            <div class="col-md-8">
                <ul class="nav nav-tabs" id="registerTab" role="tablist">
                    <li class="nav-item" role="presentation">
                        <button class="nav-link active" id="member-tab" data-bs-toggle="tab" data-bs-target="#member" type="button" role="tab">Member Registration</button>
                    </li>
                    <li class="nav-item" role="presentation">
                        <button class="nav-link" id="partner-tab" data-bs-toggle="tab" data-bs-target="#partner" type="button" role="tab">Partner Registration</button>
                    </li>
                </ul>

                <div class="tab-content p-4 bg-white shadow-sm" id="registerTabContent">
                    <!-- Member Registration Form -->
                    <div class="tab-pane fade show active" id="member" role="tabpanel">
                        <h3 class="mb-4">Member Registration</h3>
                        <form action="process_registration.php" method="POST" enctype="multipart/form-data">
                            <input type="hidden" name="registration_type" value="member">
                            
                            <div class="mb-3">
                                <label for="member_name" class="form-label">Full Name</label>
                                <input type="text" class="form-control" id="member_name" name="member_name" required>
                            </div>

                            <div class="mb-3">
                                <label for="member_email" class="form-label">Email</label>
                                <input type="email" class="form-control" id="member_email" name="member_email" required>
                            </div>

                            <div class="mb-3">
                                <label for="member_country" class="form-label">Country</label>
                                <input type="text" class="form-control" id="member_country" name="member_country" required>
                            </div>

                            <div class="mb-3">
                                <label for="member_position" class="form-label">Position</label>
                                <input type="text" class="form-control" id="member_position" name="member_position" required>
                            </div>

                            <div class="mb-3">
                                <label for="member_passport" class="form-label">Passport Number</label>
                                <input type="text" class="form-control" id="member_passport" name="member_passport" required>
                            </div>

                            <div class="mb-3">
                                <label for="member_phone" class="form-label">Phone Number</label>
                                <input type="tel" class="form-control" id="member_phone" name="member_phone" required>
                            </div>

                            <div class="mb-3">
                                <label for="member_password" class="form-label">Password</label>
                                <input type="password" class="form-control" id="member_password" name="member_password" required>
                            </div>

                            <div class="mb-3">
                                <label for="member_confirm_password" class="form-label">Confirm Password</label>
                                <input type="password" class="form-control" id="member_confirm_password" name="member_confirm_password" required>
                            </div>

                            <button type="submit" class="btn btn-primary">Register as Member</button>
                        </form>
                    </div>

                    <!-- Partner Registration Form -->
                    <div class="tab-pane fade" id="partner" role="tabpanel">
                        <h3 class="mb-4">Partner Registration</h3>
                        <form action="process_registration.php" method="POST" enctype="multipart/form-data">
                            <input type="hidden" name="registration_type" value="partner">
                            
                            <div class="mb-3">
                                <label for="company_name" class="form-label">Company Name</label>
                                <input type="text" class="form-control" id="company_name" name="company_name" required>
                            </div>

                            <div class="mb-3">
                                <label for="partner_email" class="form-label">Email</label>
                                <input type="email" class="form-control" id="partner_email" name="partner_email" required>
                            </div>

                            <div class="mb-3">
                                <label for="contact_person" class="form-label">Contact Person</label>
                                <input type="text" class="form-control" id="contact_person" name="contact_person" required>
                            </div>

                            <div class="mb-3">
                                <label for="partner_phone" class="form-label">Phone Number</label>
                                <input type="tel" class="form-control" id="partner_phone" name="partner_phone" required>
                            </div>

                            <div class="mb-3">
                                <label for="partner_type" class="form-label">Partner Type</label>
                                <select class="form-select" id="partner_type" name="partner_type" required>
                                    <option value="">Select Partner Type</option>
                                    <option value="platinum">Platinum</option>
                                    <option value="gold">Gold</option>
                                    <option value="silver">Silver</option>
                                </select>
                            </div>

                            <div class="mb-3">
                                <label for="partner_description" class="form-label">Description</label>
                                <textarea class="form-control" id="partner_description" name="partner_description" rows="3" required></textarea>
                            </div>

                            <div class="mb-3">
                                <label for="partner_password" class="form-label">Password</label>
                                <input type="password" class="form-control" id="partner_password" name="partner_password" required>
                            </div>

                            <div class="mb-3">
                                <label for="partner_confirm_password" class="form-label">Confirm Password</label>
                                <input type="password" class="form-control" id="partner_confirm_password" name="partner_confirm_password" required>
                            </div>

                            <button type="submit" class="btn btn-primary">Register as Partner</button>
                        </form>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <?php include 'includes/footer.php'; ?>
    
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html> 