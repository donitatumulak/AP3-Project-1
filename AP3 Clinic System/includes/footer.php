<!-- Footer -->
<footer>
    <p>© 2025 Cura Clinic | Because Every Life Deserves Care.</p>
    <p><i class="bi bi-globe"></i> Web Development | <i class="bi bi-person-circle"></i> Group 6 - BSIS III-A </p>
</footer>

<!-- Bootstrap JS -->
<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>

<?php 
$base_path = '/AP3 Clinic System';

$dashboard_pages = [
    'dashboards/patient_dashboard', 
    'dashboards/doctor_dashboard',
    'dashboards/staff_dashboard', 
    'dashboards/superadmin_dashboard'
];

if (isset($page) && in_array($page, $dashboard_pages)): 
?>
<script src="<?php echo $base_path; ?>/public/js/dashboard_script.js"></script>
<?php endif; ?>

</body>
</html>