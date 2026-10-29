<?php
require_once 'includes/functions.php';
$page_title = 'About Us';
?>
<?php include 'includes/header.php'; ?>

<div class="breadcrumb-section">
    <div class="container">
        <ol class="breadcrumb">
            <li class="breadcrumb-item"><a href="<?php echo SITE_URL; ?>">Home</a></li>
            <li class="breadcrumb-item active">About Us</li>
        </ol>
    </div>
</div>

<!-- Hero -->
<section style="background:linear-gradient(135deg,#0A0E1A,#0D1B40);padding:80px 0;color:white;text-align:center;">
    <div class="container">
        <div class="section-tag" style="display:inline-block;margin-bottom:16px;">Our Story</div>
        <h1 style="font-size:clamp(2rem,5vw,3rem);font-weight:800;margin-bottom:16px;">
            Powering Your <span style="background:linear-gradient(90deg,#3385FF,#00D4FF);-webkit-background-clip:text;-webkit-text-fill-color:transparent;background-clip:text;">Digital Life</span>
        </h1>
        <p style="color:rgba(255,255,255,0.65);max-width:600px;margin:0 auto;font-size:1.05rem;line-height:1.7;">
            Since 2018, ElectroHub has been Sri Lanka's premier destination for authentic electronics, delivering cutting-edge technology with outstanding customer service.
        </p>
    </div>
</section>

<!-- Stats -->
<section style="background:white;padding:48px 0;border-bottom:1px solid #E5E7EB;">
    <div class="container">
        <div class="row g-4 text-center">
            <?php
            $stats = [
                ['6+','Years in Business'],
                ['500+','Products'],
                ['50,000+','Happy Customers'],
                ['4.9★','Average Rating'],
            ];
            foreach ($stats as $s):
            ?>
            <div class="col-6 col-md-3">
                <div style="font-family:'Syne',sans-serif;font-size:2.4rem;font-weight:800;color:#0066FF;"><?php echo $s[0]; ?></div>
                <div style="font-size:0.88rem;color:#6B7280;font-weight:500;"><?php echo $s[1]; ?></div>
            </div>
            <?php endforeach; ?>
        </div>
    </div>
</section>

<!-- Mission & Vision -->
<section class="section-padding" style="background:#F8FAFC;">
    <div class="container">
        <div class="row g-5 align-items-center">
            <div class="col-lg-6">
                <div class="section-tag">Who We Are</div>
                <h2 class="section-title mb-4">Your Trusted Electronics Partner</h2>
                <p style="color:#6B7280;line-height:1.8;margin-bottom:20px;">
                    ElectroHub was founded with a simple mission: make premium electronics accessible to everyone in Sri Lanka and beyond. We partner directly with leading manufacturers to bring you the latest smartphones, laptops, audio gear, and accessories at competitive prices.
                </p>
                <p style="color:#6B7280;line-height:1.8;margin-bottom:28px;">
                    Every product on our platform is 100% authentic and covered by our 2-year warranty. Our team of tech experts personally tests and curates each product, ensuring you get only the best.
                </p>
                <div class="row g-3">
                    <?php
                    $values = [
                        ['fas fa-certificate','100% Authentic','All products are genuine, sourced directly from manufacturers.'],
                        ['fas fa-headset','Expert Support','Our tech team is available 7 days a week to help you.'],
                        ['fas fa-truck','Fast Delivery','Island-wide delivery within 2-5 business days.'],
                        ['fas fa-undo','Easy Returns','30-day hassle-free return policy on all orders.'],
                    ];
                    foreach ($values as $v):
                    ?>
                    <div class="col-sm-6">
                        <div style="display:flex;gap:12px;align-items:flex-start;">
                            <div style="width:40px;height:40px;background:#F0F7FF;border-radius:10px;display:flex;align-items:center;justify-content:center;flex-shrink:0;">
                                <i class="<?php echo $v[0]; ?>" style="color:#0066FF;"></i>
                            </div>
                            <div>
                                <div style="font-weight:700;font-size:0.9rem;margin-bottom:3px;"><?php echo $v[1]; ?></div>
                                <div style="font-size:0.8rem;color:#6B7280;line-height:1.5;"><?php echo $v[2]; ?></div>
                            </div>
                        </div>
                    </div>
                    <?php endforeach; ?>
                </div>
            </div>
            <div class="col-lg-6">
                <div style="background:linear-gradient(135deg,#0066FF,#00D4FF);border-radius:24px;padding:48px;color:white;text-align:center;">
                    <i class="fas fa-bolt" style="font-size:4rem;margin-bottom:20px;opacity:0.9;"></i>
                    <h3 style="font-size:1.6rem;font-weight:800;margin-bottom:12px;">Our Mission</h3>
                    <p style="opacity:0.85;line-height:1.7;font-size:1rem;">
                        "To empower every customer with access to the world's best technology, delivered with integrity, expertise, and exceptional service."
                    </p>
                </div>
            </div>
        </div>
    </div>
</section>

<!-- Team -->
<section class="section-padding" style="background:white;">
    <div class="container">
        <div class="section-header">
            <div class="section-tag">Our People</div>
            <h2 class="section-title">Meet the Team</h2>
        </div>
        <div class="row g-4 justify-content-center">
            <?php
            $team = [
                ['SHAFNI AHAMED','Founder & CEO','fas fa-crown','#EFF6FF','#0066FF'],
                ['SAHEEF AHAMED','Head of Products','fas fa-cube','#F0FDF4','#10B981'],
                ['RASAN ','Lead Developer','fas fa-code','#FFF7ED','#F59E0B'],
                ['AKEEL','Customer Success','fas fa-heart','#FDF2F8','#EC4899'],
            ];
            foreach ($team as $t):
            ?>
            <div class="col-6 col-md-4 col-lg-3">
                <div style="text-align:center;background:#F8FAFC;border-radius:20px;padding:28px 20px;border:1px solid #E5E7EB;">
                    <div style="width:72px;height:72px;background:<?php echo $t[3]; ?>;border-radius:50%;display:flex;align-items:center;justify-content:center;margin:0 auto 14px;font-size:1.6rem;color:<?php echo $t[4]; ?>;">
                        <i class="<?php echo $t[2]; ?>"></i>
                    </div>
                    <div style="font-weight:800;font-size:0.95rem;margin-bottom:4px;"><?php echo $t[0]; ?></div>
                    <div style="font-size:0.8rem;color:#6B7280;"><?php echo $t[1]; ?></div>
                </div>
            </div>
            <?php endforeach; ?>
        </div>
    </div>
</section>

<?php include 'includes/footer.php'; ?>
