<?php
// populate_all_users.php
require_once 'config/Database.php';
require_once 'classes/User.php';

class UserPopulationService {
    private $db;
    private $user;
    private $defaultPassword = 'Welcome123!';
    
    public function __construct() {
        $database = new Database();
        $this->db = $database->connect();
        $this->user = new User($this->db);
    }
    
    public function generateUsername($firstName, $lastName, $existingUsernames = []) {
        // Clean names and create base username
        $cleanFirst = preg_replace('/[^a-zA-Z0-9]/', '', $firstName);
        $cleanLast = preg_replace('/[^a-zA-Z0-9]/', '', $lastName);
        
        $baseUsername = strtolower($cleanFirst . '.' . $cleanLast);
        $username = $baseUsername;
        $counter = 1;
        
        // Handle duplicates
        while (in_array($username, $existingUsernames)) {
            $username = $baseUsername . $counter;
            $counter++;
        }
        
        return $username;
    }
    
    public function getExistingUsernames() {
        $allUsers = $this->user->getAllUsers();
        return $allUsers['status'] === 'success' ? array_column($allUsers['data'], 'user_name') : [];
    }
    
    public function populateAllUsers() {
        $results = [
            'total_processed' => 0,
            'success' => 0,
            'errors' => 0,
            'details' => []
        ];
        
        // Get existing usernames to avoid duplicates
        $existingUsernames = $this->getExistingUsernames();
        
        echo "<h2>Starting User Population...</h2>";
        
        // Process each role (NO SUPER ADMIN)
        $results = $this->processPatients($results, $existingUsernames);
        $results = $this->processDoctors($results, $existingUsernames);
        $results = $this->processStaff($results, $existingUsernames);
        
        return $results;
    }
    
    private function processPatients(&$results, &$existingUsernames) {
        echo "<h3>Processing Patients...</h3>";
        
        try {
            $stmt = $this->db->query("SELECT pat_id, pat_first_name, pat_last_name FROM patient");
            $patients = $stmt->fetchAll(PDO::FETCH_ASSOC);
            
            echo "Found " . count($patients) . " patients to process<br>";
            
            foreach ($patients as $patient) {
                $results['total_processed']++;
                
                $username = $this->generateUsername($patient['pat_first_name'], $patient['pat_last_name'], $existingUsernames);
                $existingUsernames[] = $username;
                
                $result = $this->user->createUser(
                    $username,
                    $this->defaultPassword,
                    $patient['pat_id'],  // pat_id
                    null,                // doc_id
                    null                 // staff_id
                );
                
                if ($result['status'] === 'success') {
                    $results['success']++;
                    $results['details'][] = "✅ PATIENT: {$username} (ID: {$patient['pat_id']}) - Created successfully";
                    echo "✅ PATIENT: {$username} - Created<br>";
                } else {
                    $results['errors']++;
                    $results['details'][] = "❌ PATIENT: {$username} (ID: {$patient['pat_id']}) - {$result['message']}";
                    echo "❌ PATIENT: {$username} - {$result['message']}<br>";
                }
                
                // Flush output to see progress in real-time
                flush();
                ob_flush();
            }
        } catch (Exception $e) {
            $results['errors']++;
            $results['details'][] = "❌ PATIENT PROCESSING ERROR: " . $e->getMessage();
            echo "❌ PATIENT PROCESSING ERROR: " . $e->getMessage() . "<br>";
        }
        
        return $results;
    }
    
    private function processDoctors(&$results, &$existingUsernames) {
        echo "<h3>Processing Doctors...</h3>";
        
        try {
            $stmt = $this->db->query("SELECT doc_id, doc_first_name, doc_last_name FROM doctor");
            $doctors = $stmt->fetchAll(PDO::FETCH_ASSOC);
            
            echo "Found " . count($doctors) . " doctors to process<br>";
            
            foreach ($doctors as $doctor) {
                $results['total_processed']++;
                
                $username = $this->generateUsername($doctor['doc_first_name'], $doctor['doc_last_name'], $existingUsernames);
                $existingUsernames[] = $username;
                
                $result = $this->user->createUser(
                    $username,
                    $this->defaultPassword,
                    null,                // pat_id
                    $doctor['doc_id'],   // doc_id
                    null                 // staff_id
                );
                
                if ($result['status'] === 'success') {
                    $results['success']++;
                    $results['details'][] = "✅ DOCTOR: {$username} (ID: {$doctor['doc_id']}) - Created successfully";
                    echo "✅ DOCTOR: {$username} - Created<br>";
                } else {
                    $results['errors']++;
                    $results['details'][] = "❌ DOCTOR: {$username} (ID: {$doctor['doc_id']}) - {$result['message']}";
                    echo "❌ DOCTOR: {$username} - {$result['message']}<br>";
                }
                
                // Flush output to see progress in real-time
                flush();
                ob_flush();
            }
        } catch (Exception $e) {
            $results['errors']++;
            $results['details'][] = "❌ DOCTOR PROCESSING ERROR: " . $e->getMessage();
            echo "❌ DOCTOR PROCESSING ERROR: " . $e->getMessage() . "<br>";
        }
        
        return $results;
    }
    
    private function processStaff(&$results, &$existingUsernames) {
        echo "<h3>Processing Staff...</h3>";
        
        try {
            $stmt = $this->db->query("SELECT staff_id, staff_first_name, staff_last_name FROM staff");
            $staff = $stmt->fetchAll(PDO::FETCH_ASSOC);
            
            echo "Found " . count($staff) . " staff to process<br>";
            
            foreach ($staff as $staffMember) {
                $results['total_processed']++;
                
                $username = $this->generateUsername($staffMember['staff_first_name'], $staffMember['staff_last_name'], $existingUsernames);
                $existingUsernames[] = $username;
                
                $result = $this->user->createUser(
                    $username,
                    $this->defaultPassword,
                    null,                        // pat_id
                    null,                        // doc_id
                    $staffMember['staff_id']     // staff_id
                );
                
                if ($result['status'] === 'success') {
                    $results['success']++;
                    $results['details'][] = "✅ STAFF: {$username} (ID: {$staffMember['staff_id']}) - Created successfully";
                    echo "✅ STAFF: {$username} - Created<br>";
                } else {
                    $results['errors']++;
                    $results['details'][] = "❌ STAFF: {$username} (ID: {$staffMember['staff_id']}) - {$result['message']}";
                    echo "❌ STAFF: {$username} - {$result['message']}<br>";
                }
                
                // Flush output to see progress in real-time
                flush();
                ob_flush();
            }
        } catch (Exception $e) {
            $results['errors']++;
            $results['details'][] = "❌ STAFF PROCESSING ERROR: " . $e->getMessage();
            echo "❌ STAFF PROCESSING ERROR: " . $e->getMessage() . "<br>";
        }
        
        return $results;
    }
}

// Run the population
echo "<html><body style='font-family: Arial, sans-serif; margin: 20px;'>";
echo "<h1>User Account Population (Patients, Doctors, Staff Only)</h1>";

try {
    $populator = new UserPopulationService();
    $results = $populator->populateAllUsers();
    
    // Display final results
    echo "<hr>";
    echo "<h2>📊 Population Complete</h2>";
    echo "<p><strong>Total Processed:</strong> {$results['total_processed']}</p>";
    echo "<p style='color: green;'><strong>✅ Successfully Created:</strong> {$results['success']}</p>";
    echo "<p style='color: red;'><strong>❌ Errors:</strong> {$results['errors']}</p>";
    
    if (!empty($results['details'])) {
        echo "<h3>Details:</h3>";
        echo "<div style='max-height: 400px; overflow-y: auto; border: 1px solid #ccc; padding: 10px;'>";
        foreach ($results['details'] as $detail) {
            echo "<p style='margin: 2px 0;'>{$detail}</p>";
        }
        echo "</div>";
    }
    
    // Login instructions
    echo "<h3>🔑 Login Instructions:</h3>";
    echo "<p>All users can login with:</p>";
    echo "<ul>";
    echo "<li><strong>Username:</strong> firstname.lastname (e.g., john.doe)</li>";
    echo "<li><strong>Password:</strong> Welcome123!</li>";
    echo "</ul>";
    echo "<p><em>Users should change their password on first login for security.</em></p>";
    
} catch (Exception $e) {
    echo "<h2 style='color: red;'>❌ SCRIPT ERROR</h2>";
    echo "<p>{$e->getMessage()}</p>";
}

echo "</body></html>";
?>