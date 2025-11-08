<?php include 'includes/header.php'; ?>
<?php include 'includes/navbar_guest.php'; ?>
<main>
  <!-- Hero Section with Slideshow -->
  <section id="hero">
    <div id="heroCarousel" class="carousel slide carousel-fade" data-bs-ride="carousel" data-bs-interval="4000">
      <div class="carousel-inner">
        <div class="carousel-item active" style="background-image: url('assets/images/cura_clinic1.jpg');">
          <div class="overlay">
            <div>
              <h1>Because Every Life Deserves Care.</h1>
              <p>Providing compassionate healthcare for you and your family.</p>
              <a href="login.php" class="btn btn-hero">Book an Appointment</a>
            </div>
          </div>
        </div>

        <div class="carousel-item" style="background-image: url('assets/images/cura_clinic2.jpg');">
          <div class="overlay">
            <div>
              <h1>Your Health, Our Mission.</h1>
              <p>Dedicated doctors and staff, ready to serve you every day.</p>
              <a href="#services" class="btn btn-hero">View Services</a>
            </div>
          </div>
        </div>

        <div class="carousel-item" style="background-image: url('assets/images/cura_clinic3.jpg');">
          <div class="overlay">
            <div>
              <h1>Trusted Care, Modern Solutions.</h1>
              <p>Experience healthcare guided by expertise and technology.</p>
              <a href="#about" class="btn btn-hero">Learn More</a>
            </div>
          </div>
        </div>

        <div class="carousel-item" style="background-image: url('assets/images/cura_clinic4.jpg');">
          <div class="overlay">
            <div>
              <h1>Where Compassion Meets Precision.</h1>
              <p>Your wellness journey begins with Cura Clinic.</p>
              <a href="#contact" class="btn btn-hero">Contact Us</a>
            </div>
          </div>
        </div>
      </div>
    </div>
  </section>

  <!-- About Section -->
  <section id="about">
    <div class="container text-center">
      <h2 class="mb-4" style="color: var(--main-teal);"> <i class="bi bi-heart-pulse-fill"></i> About Cura Clinic</h2>
      <p class="lead mx-auto" style="max-width: 850px;">
      <strong>Cura Clinic</strong> is a trusted community healthcare provider dedicated to delivering compassionate and comprehensive medical care for every individual. 
      Our goal is to create a welcoming environment where patients feel safe, heard, and cared for. 
      With a team of highly skilled doctors, nurses, and support staff, we offer a wide range of medical services — from preventive health checkups to specialized consultations — 
      ensuring that every patient receives the attention they deserve.
    </p>

    <p class="lead mx-auto mt-4" style="max-width: 850px;">
      At Cura Clinic, we believe that healthcare should be both accessible and personal. 
      We combine modern medical technology with a patient-centered approach to ensure the best outcomes possible. 
      Whether you’re visiting for a routine consultation or a complex medical concern, 
      Cura Clinic is here to help you achieve better health and peace of mind — because every life truly deserves care.
    </p>
    </div>
  </section>

  <!-- Services Section -->
  <section id="services">
    <div class="container text-center">
      <h2 class="mb-5" style="color: var(--main-teal);">Our Services</h2>
     <div class="row g-4">
        <div class="col-md-3">
          <div class="card p-4 h-100">
            <h5 class="mb-3"><i class="bi bi-clipboard2-check-fill"></i> General Consultation</h5>
              <p>Comprehensive check-ups and personalized medical advice for all ages.</p>
          </div>
        </div>

        <div class="col-md-3">
          <div class="card p-4 h-100">
            <h5 class="mb-3"> <i class="bi bi-clipboard2-pulse-fill"></i> Pediatrics</h5>
            <p>Gentle, reliable care for infants, children, and adolescents.</p>
          </div>
        </div>

        <div class="col-md-3">
          <div class="card p-4 h-100">
            <h5 class="mb-3"> <i class="bi bi-lungs-fill"></i> Laboratory & Diagnostics</h5>
           <p>Quick, accurate test results to guide better treatment decisions.</p>
          </div>
        </div>

        <div class="col-md-3">
          <div class="card p-4 h-100">
            <h5 class="mb-3"> <i class="bi bi-hospital-fill"></i> Minor Surgery</h5>
             <p>Safe outpatient procedures performed with care and precision.</p>
          </div>
        </div>

        <div class="col-md-3">
          <div class="card p-4 h-100">
            <h5 class="mb-3"> <i class="bi bi-gender-female"></i> </i> Women's Health</h5>
            <p>Comprehensive gynecological care and family health support.</p>
          </div>
        </div>

        <div class="col-md-3">
          <div class="card p-4 h-100">
            <h5 class="mb-3"> <i class="bi bi-prescription"></i> Dental Care</h5>
            <p>Preventive and restorative treatments for brighter, healthier smiles.</p>
          </div>
        </div>

        <div class="col-md-3">
          <div class="card p-4 h-100">
            <h5 class="mb-3"> <i class="bi bi-file-earmark-medical-fill"></i> Health Screening Packages</h5>
            <p>Routine wellness exams to help detect conditions early.</p>
          </div>
        </div>

        <div class="col-md-3">
          <div class="card p-4 h-100">
            <h5 class="mb-3"> <i class="bi bi-virus"></i> Vaccination Services</h5>
             <p>Protective immunization programs for all age groups.</p>
          </div>
        </div>
      </div>
    </div>
  </section>

  <!-- Contact Section -->
  <section id="contact">
    <div class="container text-center">
      <h2 class="mb-4" style="color: var(--main-teal);">Contact Us</h2>
      <p><i class="bi bi-geo-fill"></i> M.J. Cuenco Ave., Corner R. Palma St., Cebu City, Philippines 6000</p>
      <p><i class="bi bi-telephone-fill"></i> (032) 123 4567 | <i class="bi bi-phone-fill"></i> 09123456781 </p>
     <p><i class="bi bi-envelope-at-fill"></i>
      <a href="https://mail.google.com/mail/?view=cm&to=info@curaclinic.com" target="_blank" style="text-decoration: none; color: inherit;">
        info@curaclinic.com
      </a></p>
    </div>
  </section>
</main>
<?php include 'includes/footer.php'; ?>