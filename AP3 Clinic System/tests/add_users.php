<?php
// test_all_user_types.php
require_once 'config/Database.php';
require_once 'classes/User.php';

try {
    $database = new Database();     // Create Database object
    $db = $database->connect();     // CALL connect() and get PDO connection
    $user = new User($db);          // Pass the PDO connection to User class
    // Test SuperAdmin
    echo "<h3>Testing SuperAdmin Creation:</h3>";
    $adminResult = $user->createSuperAdmin('donita.tumulak', 'Admin123!');
    print_r($adminResult);

    // Test SuperAdmin
    echo "<h3>Testing SuperAdmin Creation:</h3>";
    $adminResult = $user->createSuperAdmin('fiona.menao', 'Admin123!');
    print_r($adminResult);

    // Test SuperAdmin
    echo "<h3>Testing SuperAdmin Creation:</h3>";
    $adminResult = $user->createSuperAdmin('therese.rosalijos', 'Admin123!');
    print_r($adminResult);
    
    // Test Patient
    echo "<h3>Testing Patient Creation:</h3>";
    $patientResult = $user->createUser('andrea.cruz', 'Welcome123!', 1000, null, null);
    print_r($patientResult);
    
    // Test Doctor  
    echo "<h3>Testing Doctor Creation:</h3>";
    $doctorResult = $user->createUser('maria.santos', 'Welcome123!', null, 1000, null);
    print_r($doctorResult);
    
    // Test Staff
    echo "<h3>Testing Staff Creation:</h3>";
    $staffResult = $user->createUser('althea.navarro', 'Welcome123!', null, null, 1000);
    print_r($staffResult);
    
} catch (Exception $e) {
    echo "❌ SCRIPT ERROR: " . $e->getMessage();
}
?>