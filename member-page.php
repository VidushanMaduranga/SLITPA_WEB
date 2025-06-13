<?php
require_once 'config/config.php';

// Get paid member count
$stmt = $pdo->query("SELECT COUNT(*) as count FROM members WHERE payment_status = 'paid'");
$paid_member_count = $stmt->fetch()['count'] ?? 0;

// Get last registered member date
$stmt = $pdo->query("SELECT created_at FROM members ORDER BY created_at DESC LIMIT 1");
$last_registered_date = $stmt->fetch()['created_at'] ?? null;
$last_registered_date_fmt = $last_registered_date ? date('d-F-Y', strtotime($last_registered_date)) : '-';

// Fetch paid partner count
$stmt = $pdo->query("SELECT COUNT(*) as count FROM partners WHERE status = 'paid'");
$paid_partner_count = $stmt->fetch()['count'] ?? 0;
?>

<html lang="en">

<head>
    <meta charset="UTF-8">

    <title>Membership</title>
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <!-- Include Bootstrap CSS for responsive design -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <!-- Include custom styles -->
    <link rel="stylesheet" href="assets/css/member-page.css">
</head>

<body>
    <!-- Header Section with Navigation -->
    <?php include 'includes/header.php'; ?>
    <!-- Hero Section: Main banner with welcome message -->
    <section class="hero-section">
        <div class="hero">
            <!-- Hero Background Image -->
            <div class="hero-image-wrap">
                <img src="assets/images/hero.png" alt="SLITPA Hero Image" class="hero-image">
            </div>
            <!-- Hero Content Overlay -->
            <div class="hero-text-wrap">
                <h1 class="hero-title">Membership</h1>
                <p style="color: #fff; font-size: 1.2rem;">
                    <span class="committee-year-selector fw-bold" id="becomeMemberTab"
                        style="cursor:pointer;"><strong>Become a member</strong></span> &nbsp;
                    <span class="committee-year-selector" id="memberBenefitsTab" style="cursor:pointer;">Member
                        Benefits</span>
                </p>
            </div>
        </div>
    </section>

    <!-- Member Registration Section -->
    <div class="container">
        <div id="becomeMemberContent">
            <div class="become_a_member">
                <div class="registration">
                    <a href="./member-registration.php" class="registration-button">residence registration</a>
                    <p class="registration-text">This membership category allows IT Professionals are in the IT industry
                        in UAE</p>
                </div>

                <div class="registration">
                    <a href="./non_resident_registration.php" class="registration-button">non residence registration</a>
                    <p class="registration-text">This membership category allows IT Professionals who are seeking IT
                        Positions in the UAE</p>
                </div>

                <div class="registration">
                    <a href="./partners/register.php" class="registration-button" style="background:#28a745;">partner
                        registration</a>
                    <p class="registration-text">Register your company or organization as a SLITPA partner.</p>
                </div>

                    
                        <div class="member-wrap">
                            <div class="member">
                                <div class="row g-0">
                                    <div class="col-7">
                                        <div class="member-left-wrap">
                                            <div class="member-left">
                                                <div class="member-icon">
                                                    <img src="./assets/images/member.png" alt="" class="member-logo">
                                                </div>
                                                <div class="meber-text-wrap">
                                                    <h5 class="member-title">MEMBERS</h5>
                                                    <p class="date">
                                                        <?= htmlspecialchars($last_registered_date_fmt) ?>
                                                        <!-- Last registered member date -->
                                                    </p>
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                    <div class="col-5">
                                        <div class="member-count-wrap">
                                            <h1 class="member-count">
                                                <?= htmlspecialchars($paid_member_count) ?>
                                                <!-- Paid member count -->
                                            </h1>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                        <!-- <div class="member-wrap">
                            <div class="member">
                                <div class="row g-0">
                                    <div class="col-7">
                                        <div class="member-left-wrap">
                                            <div class="member-left">
                                                <div class="member-icon">
                                                    <img src="./assets/images/member.png" alt="" class="member-logo">
                                                </div>
                                                <div class="meber-text-wrap">
                                                    <h5 class="member-title">PARTNERS</h5>
                                                    <p class="date">&nbsp;</p>
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                    <div class="col-5">
                                        <div class="member-count-wrap">
                                            <h1 class="member-count">
                                                <?= htmlspecialchars($paid_partner_count) ?>
                                            </h1>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div> -->




            </div>
        </div>



        <!-- Member Benefits Section -->
        <div id="memberBenefitsContent" style="display:none;">
            <div class="member-benefits">
                <h2>MEMBER WELFARE</h2>

                <!-- Section 1 -->
                <div class="section">
                    <div class="section-title"><span class="section-number">1.</span> Membership Cards Issued</div>
                    <div class="item"><i class="fas fa-id-card icon"></i> Personalized ID cards granting access to all
                        member privileges.</div>
                </div>

                <!-- Section 2 -->
                <div class="section">
                    <div class="section-title"><span class="section-number">2.</span> Exclusive Discounts at Partner
                        Outlets</div>
                    <div class="item"><i class="fas fa-tags icon"></i> Enjoy savings at restaurants, gyms, tech stores,
                        and service providers through SLITPA partnerships.</div>
                </div>

                <!-- Section 3 -->
                <div class="section">
                    <div class="section-title"><span class="section-number">3.</span> Professional Qualification
                        Discounts</div>
                    <ul>
                        <li class="item"><i class="fas fa-graduation-cap icon"></i> Advance your career through
                            partnerships with:</li>
                        <li class="item"><i class="fas fa-circle-dot"></i> ISACA Sri Lanka</li>
                        <li class="item"><i class="fas fa-circle-dot"></i> Discounted Training Programs</li>
                        <li class="item"><i class="fas fa-circle-dot"></i> Access to Exclusive Events & Networking</li>
                        <li class="item"><i class="fas fa-circle-dot"></i> Upskill with Globally Recognized
                            Certifications</li>
                        <li class="item"><i class="fas fa-circle-dot"></i> Stay ahead in cybersecurity, risk, and audit!
                        </li>
                        <li class="item"><i class="fas fa-circle-dot"></i> CSSL – Computer Society of Sri Lanka</li>
                        <li class="item"><i class="fas fa-circle-dot"></i> Membership for Sri Lankan IT Pros in UAE</li>
                        <li class="item"><i class="fas fa-circle-dot"></i> Access to Premium IT Resources</li>
                        <li class="item"><i class="fas fa-circle-dot"></i> Join Events & Professional Development
                            Programs</li>
                        <li class="item"><i class="fas fa-circle-dot"></i> Grow your network. Expand your knowledge.
                        </li>
                        <li class="item"><i class="fas fa-circle-dot"></i> IIBA Colombo Chapter</li>
                        <li class="item"><i class="fas fa-circle-dot"></i> Discounts on IIBA Certifications</li>
                        <li class="item"><i class="fas fa-circle-dot"></i> Advance Your Business Analysis Skills</li>
                        <li class="item"><i class="fas fa-circle-dot"></i> Strengthen Your Professional Profile</li>
                        <li class="item"><i class="fas fa-circle-dot"></i> Perfect for aspiring and experienced business
                            analysts!</li>
                    </ul>
                </div>

                <!-- Section 4 -->
                <div class="section">
                    <div class="section-title"><span class="section-number">4.</span> TechieChat: Monthly Meetups</div>
                    <div class="item"><i class="fas fa-comments icon"></i> A casual space for exchanging knowledge,
                        sharing ideas, and building professional networking opportunities.</div>
                </div>

                <!-- Section 5 -->
                <div class="section">
                    <div class="section-title"><span class="section-number">5.</span> Family Gathering Events</div>
                    <div class="item"><i class="fas fa-people-group icon"></i> Events for the whole family to build
                        community and enjoy quality time.</div>
                </div>

                <!-- Section 6 -->
                <div class="section">
                    <div class="section-title"><span class="section-number">6.</span> Members-Only WhatsApp & Discord
                        Groups</div>
                    <div class="item"><i class="fab fa-whatsapp icon"></i> Stay connected through real-time channels for
                        job alerts, study groups, and domain-specific networking.</div>
                </div>

                <!-- Section 7 -->
                <div class="section">
                    <div class="section-title"><span class="section-number">7.</span> Workshops & Webinars (NEW)</div>
                    <div class="item"><i class="fas fa-laptop icon"></i> Regular sessions on career growth,
                        entrepreneurship, emerging technologies, and UAE-specific professional advice.</div>
                </div>

                <!-- Section 8 -->
                <div class="section">
                    <div class="section-title"><span class="section-number">8.</span> Recognition & Awards (NEW)</div>
                    <div class="item"><i class="fas fa-award icon"></i> Annual recognition for high-performing members,
                        community contributors, and tech professionals.</div>
                </div>
                <div class="section"></div>
            </div>

            </ul>
        </div>
    </div>



    <!-- Footer Section -->
    <?php include 'includes/footer.php'; ?>

    <!-- Include Bootstrap JS for interactive components -->
    <script src=" https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js">
    < script src = "https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js" >
    </script>
</body>
</script>

<script>
const becomeMemberTab = document.getElementById('becomeMemberTab');
const memberBenefitsTab = document.getElementById('memberBenefitsTab');
const becomeMemberContent = document.getElementById('becomeMemberContent');
const memberBenefitsContent = document.getElementById('memberBenefitsContent');

becomeMemberTab.addEventListener('click', function() {
    becomeMemberTab.classList.add('fw-bold');
    becomeMemberTab.innerHTML = '<strong>Become a member</strong>';
    memberBenefitsTab.classList.remove('fw-bold');
    memberBenefitsTab.innerHTML = 'Member Benefits';
    becomeMemberContent.style.display = '';
    memberBenefitsContent.style.display = 'none';
});

memberBenefitsTab.addEventListener('click', function() {
    memberBenefitsTab.classList.add('fw-bold');
    memberBenefitsTab.innerHTML = '<strong>Member Benefits</strong>';
    becomeMemberTab.classList.remove('fw-bold');
    becomeMemberTab.innerHTML = 'Become a member';
    becomeMemberContent.style.display = 'none';
    memberBenefitsContent.style.display = '';
});
</script>
</body>

</html>