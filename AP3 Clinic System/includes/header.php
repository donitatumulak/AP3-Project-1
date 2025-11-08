<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8" />
  <meta name="viewport" content="width=device-width, initial-scale=1" />
  <title>Cura Clinic | Because Every Life Deserves Care</title>

  <!-- Define base path at the top -->
  <?php 
  $base_path = '/AP3 Clinic System';
  ?>

  <!-- Bootstrap CSS -->
  <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
  <!-- Bootstrap Icons -->
  <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.css" rel="stylesheet">
  <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">

  <!-- Google Fonts -->
  <link rel="preconnect" href="https://fonts.googleapis.com">
  <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
  <link href="https://fonts.googleapis.com/css2?family=Fira+Sans:wght@400;500;600;700&family=Montserrat:wght@300;400;500;600&display=swap" rel="stylesheet">

  <!-- Custom CSS - FIXED PATH -->
  <link rel="stylesheet" href="<?php echo $base_path; ?>/public/css/stylesheet.css">
  
  <!-- Sidebar CSS - For ALL pages that use sidebar -->
  <?php 
  $sidebar_pages = [
    'dashboards/patient_dashboard', 
    'dashboards/doctor_dashboard', 
    'dashboards/staff_dashboard', 
    'dashboards/superadmin_dashboard', 
    'pages/user_management', 
    'pages/medical_records_management',
    'pages/services_management', 
    'pages/payment_management', 
    'pages/appointment_management',
    'pages/my_account',
    'pages/update_account',
    'pages/my_medical_records',
    'pages/my_appointments',
    'pages/my_doc_appointments',
    'pages/my_schedule',
    'pages/my_doc_medical_records',
    'pages/change_pass'
  ];
  if (isset($page) && in_array($page, $sidebar_pages)): 
  ?>
  <link rel="stylesheet" href="<?php echo $base_path; ?>/public/css/sidebar.css">
  <?php endif; ?>
  
  <!-- Auth Page CSS -->
  <?php if (isset($page) && $page === 'auth'): ?>
  <link rel="stylesheet" href="<?php echo $base_path; ?>/public/css/authpage_style.css">
  <?php endif; ?>
  
  <!-- Dashboard CSS - Only for actual dashboard pages -->
  <?php 
  $dashboard_only_pages = [
    'dashboards/patient_dashboard', 
    'dashboards/doctor_dashboard', 
    'dashboards/staff_dashboard', 
    'dashboards/superadmin_dashboard'
  ];
  if (isset($page) && in_array($page, $dashboard_only_pages)): 
  ?>
  <link rel="stylesheet" href="<?php echo $base_path; ?>/public/css/dashboard_style.css">
  <?php endif; ?>

  <!-- User Management CSS - Only for user management -->
  <?php 
  $management_pages = [
    'pages/user_management', 
    'pages/medical_records_management', 
    'pages/services_management',
    'pages/appointment_management', 
    'pages/payment_management',
    'pages/my_account',
    'pages/update_account',
    'pages/my_medical_records',
    'pages/my_appointments',
    'pages/my_doc_appointments',
    'pages/my_schedule',
    'pages/my_doc_medical_records',
    'pages/change_pass'
  ];
  if (isset($page) && in_array($page, $management_pages)): 
  ?>
  <link rel="stylesheet" href="<?php echo $base_path; ?>/public/css/management_styles.css">
  <?php endif; ?>
  
  <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
</head>
<body>