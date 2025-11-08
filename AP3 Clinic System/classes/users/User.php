<?php
class User {
    private $conn;
    private $table = "user";
    
    // Common response format
    private function response($status, $data = [], $message = "") {
        return [
            "status" => $status,
            "data" => $data,
            "message" => $message
        ];
    }
    
    // Determine user type based on ID presence
        private function getUserType($pat_id, $doc_id, $staff_id, $user_is_superadmin = 0) {
        // ULTRA FORCEFUL - check every possible way it could be superadmin
        if ($user_is_superadmin == 1 || 
            $user_is_superadmin === true || 
            $user_is_superadmin === '1' ||
            (int)$user_is_superadmin === 1 ||
            (!empty($user_is_superadmin) && $user_is_superadmin != '0')) {
            return 'superadmin';
        }
        
        // Then check other types
        if (!empty($pat_id)) return 'patient';
        if (!empty($doc_id)) return 'doctor';
        if (!empty($staff_id)) return 'staff';
        
        return 'inactive';
    }
        
    // Validation method - UPDATED for correct logic
    private function validateUserData($user_name, $user_password, $pat_id = null, $doc_id = null, $staff_id = null) {
        $errors = [];
        
        if (empty(trim($user_name))) {
            $errors[] = "Username is required";
        } elseif (strlen(trim($user_name)) < 3) {
            $errors[] = "Username must be at least 3 characters long";
        }
        
        if (empty(trim($user_password))) {
            $errors[] = "Password is required";
        } elseif (strlen(trim($user_password)) < 6) {
            $errors[] = "Password must be at least 6 characters long";
        }
        
        // Count how many IDs are provided
        $idCount = 0;
        if (!empty($pat_id)) $idCount++;
        if (!empty($doc_id)) $idCount++;
        if (!empty($staff_id)) $idCount++;
        
        // Must have exactly ONE ID set (or none for superadmin)
        if ($idCount > 1) {
            $errors[] = "Only one ID (pat_id, doc_id, or staff_id) can be set";
        }
        
        // If no IDs, it's a superadmin (handled separately)
        if ($idCount === 0) {
            // This is acceptable for superadmin
            return $errors;
        }
        
        // Validate ID is numeric if provided
        if (!empty($pat_id) && !is_numeric($pat_id)) {
            $errors[] = "Valid patient ID is required";
        }
        
        if (!empty($doc_id) && !is_numeric($doc_id)) {
            $errors[] = "Valid doctor ID is required";
        }
        
        if (!empty($staff_id) && !is_numeric($staff_id)) {
            $errors[] = "Valid staff ID is required";
        }
        
        return $errors;
    }

    public function __construct($db) {
        $this->conn = $db;
    }

    // Module 1: Create user - SIMPLIFIED (no user_type parameter) *
    public function createUser($user_name, $user_password, $pat_id = null, $doc_id = null, $staff_id = null, $is_superadmin = false) {
        try {
            // Validate input
            $validationErrors = $this->validateUserData($user_name, $user_password, $pat_id, $doc_id, $staff_id);
            if (!empty($validationErrors)) {
                return $this->response("error", [], implode(", ", $validationErrors));
            }

            // Check if username already exists
            $checkQuery = "SELECT user_id FROM {$this->table} WHERE user_name = :user_name";
            $checkStmt = $this->conn->prepare($checkQuery);
            $checkStmt->bindParam(':user_name', $user_name);
            $checkStmt->execute();
            
            if ($checkStmt->rowCount() > 0) {
                return $this->response("error", [], "Username already exists.");
            }

            // Hash password
            $hashed_password = password_hash($user_password, PASSWORD_DEFAULT);
            
            $query = "INSERT INTO {$this->table} 
                        (user_name, user_password, user_is_superadmin, pat_id, doc_id, staff_id)
                      VALUES 
                        (:user_name, :user_password, :is_superadmin, :pat_id, :doc_id, :staff_id)";
            $stmt = $this->conn->prepare($query);
            $stmt->bindParam(':user_name', $user_name);
            $stmt->bindParam(':user_password', $hashed_password);
            $stmt->bindParam(':is_superadmin', $is_superadmin, PDO::PARAM_BOOL);
            $stmt->bindParam(':pat_id', $pat_id, PDO::PARAM_INT);
            $stmt->bindParam(':doc_id', $doc_id, PDO::PARAM_INT);
            $stmt->bindParam(':staff_id', $staff_id, PDO::PARAM_INT);

            if ($stmt->execute()) {
                return $this->response("success", 
                    ["user_id" => $this->conn->lastInsertId()], 
                    "User created successfully."
                );
            } else {
                return $this->response("info", [], "Failed to create user.");
            }
        } catch (PDOException $e) {
            return $this->response("error", [], "Database error: " . $e->getMessage());
        }
    }

    // Module 2: View user by ID - UPDATED column names *
public function getUserById($user_id) {
    try {
        if (empty($user_id) || !is_numeric($user_id)) {
            return $this->response("error", [], "Valid user ID is required.");
        }

        $query = "SELECT 
                    u.user_id,
                    u.user_name,
                    u.user_is_superadmin,  
                    u.user_last_login,
                    u.user_created_at,
                    u.pat_id,
                    u.doc_id,
                    u.staff_id
                  FROM {$this->table} u
                  WHERE u.user_id = :user_id";
        $stmt = $this->conn->prepare($query);
        $stmt->bindParam(':user_id', $user_id, PDO::PARAM_INT);
        $stmt->execute();

        $result = $stmt->fetch(PDO::FETCH_ASSOC);
        
        if ($result) {
            // FIX: Pass user_is_superadmin to getUserType
            $result['user_type'] = $this->getUserType(
                $result['pat_id'], 
                $result['doc_id'], 
                $result['staff_id'],
                $result['user_is_superadmin']  // ← THIS IS THE FIX
            );
        }

        return $this->response(
            "success", 
            $result ?: [],
            $result ? "User found." : "No user found with that ID."
        );
    } catch (PDOException $e) {
        return $this->response("error", [], $e->getMessage());
    }
}

    // Module 3: View all users - UPDATED column names *
public function getAllUsers() {
    try {
        $query = "SELECT 
                    u.user_id,
                    u.user_name,
                    u.user_is_superadmin, 
                    u.user_last_login,
                    u.user_created_at,
                    u.pat_id,
                    u.doc_id,
                    u.staff_id
                  FROM {$this->table} u
                  ORDER BY u.user_created_at DESC";
        $stmt = $this->conn->prepare($query);
        $stmt->execute();
        $result = $stmt->fetchAll(PDO::FETCH_ASSOC);
        
        // FIX: Pass user_is_superadmin to getUserType for each user
        foreach ($result as &$user) {
            $user['user_type'] = $this->getUserType(
                $user['pat_id'], 
                $user['doc_id'], 
                $user['staff_id'],
                $user['user_is_superadmin']  // ← THIS IS THE FIX
            );
        }

        return $this->response(
            "success", 
            $result ?: [],
            $result ? "All users retrieved successfully." : "No users found."
        );
    } catch (PDOException $e) {
        return $this->response("error", [], $e->getMessage());
    }
}

    // Module 4: View all doctors' users - UPDATED column names *
    public function getDoctorUsers() {
        try {
            $query = "SELECT 
                        u.user_id,
                        u.user_name,
                        u.user_is_superadmin,
                        u.user_last_login,
                        u.user_created_at,
                        u.doc_id
                      FROM {$this->table} u
                      WHERE u.doc_id IS NOT NULL
                      ORDER BY u.user_created_at DESC";
            $stmt = $this->conn->prepare($query);
            $stmt->execute();
            $result = $stmt->fetchAll(PDO::FETCH_ASSOC);

            return $this->response(
                "success", 
                $result ?: [],
                $result ? "Doctor users retrieved successfully." : "No doctor users found."
            );
        } catch (PDOException $e) {
            return $this->response("error", [], $e->getMessage());
        }
    }

    // Module 5: View all patients' users - UPDATED column names *
    public function getPatientUsers() {
        try {
            $query = "SELECT 
                        u.user_id,
                        u.user_name,
                        u.user_is_superadmin,
                        u.user_last_login,
                        u.user_created_at,
                        u.pat_id
                      FROM {$this->table} u
                      WHERE u.pat_id IS NOT NULL
                      ORDER BY u.user_created_at DESC";
            $stmt = $this->conn->prepare($query);
            $stmt->execute();
            $result = $stmt->fetchAll(PDO::FETCH_ASSOC);

            return $this->response(
                "success", 
                $result ?: [],
                $result ? "Patient users retrieved successfully." : "No patient users found."
            );
        } catch (PDOException $e) {
            return $this->response("error", [], $e->getMessage());
        }
    }

    // Module 6: View all staff users - UPDATED column names *
    public function getStaffUsers() {
        try {
            $query = "SELECT 
                        u.user_id,
                        u.user_name,
                        u.user_is_superadmin,
                        u.user_last_login,
                        u.user_created_at,
                        u.staff_id
                      FROM {$this->table} u
                      WHERE u.staff_id IS NOT NULL
                      ORDER BY u.user_created_at DESC";
            $stmt = $this->conn->prepare($query);
            $stmt->execute();
            $result = $stmt->fetchAll(PDO::FETCH_ASSOC);

            return $this->response(
                "success", 
                $result ?: [],
                $result ? "Staff users retrieved successfully." : "No staff users found."
            );
        } catch (PDOException $e) {
            return $this->response("error", [], $e->getMessage());
        }
    }

    // NEW: Get user with full profile information (supplementary)
    public function getUserWithProfile($user_id) {
        try {
            if (empty($user_id) || !is_numeric($user_id)) {
                return $this->response("error", [], "Valid user ID is required.");
            }

            // First get the basic user info
            $userResult = $this->getUserById($user_id);
            if ($userResult['status'] !== 'success' || empty($userResult['data'])) {
                return $userResult;
            }

            $user = $userResult['data'];
            $userType = $user['user_type'];
            
            // Get profile data based on user type
            switch ($userType) {
                case 'patient':
                    $query = "SELECT pat_first_name, pat_last_name, pat_contact_num 
                              FROM patient WHERE pat_id = :id";
                    break;
                case 'doctor':
                    $query = "SELECT doc_first_name, doc_last_name, doc_specialization 
                              FROM doctor WHERE doc_id = :id";
                    break;
                case 'staff':
                    $query = "SELECT staff_first_name, staff_last_name
                              FROM staff WHERE staff_id = :id";
                    break;
                default: // superadmin
                    $user['profile'] = ['full_name' => 'Super Administrator'];
                    return $this->response("success", $user, "User with profile found.");
            }
            
            $stmt = $this->conn->prepare($query);
            $idField = $userType . '_id';
            $stmt->bindParam(':id', $user[$idField], PDO::PARAM_INT);
            $stmt->execute();
            $profile = $stmt->fetch(PDO::FETCH_ASSOC);
            
            if ($profile) {
                $user['profile'] = $profile;
            }

            return $this->response("success", $user, "User with profile found.");
        } catch (PDOException $e) {
            return $this->response("error", [], $e->getMessage());
        }
    }

    // Login method - UPDATED column names (supplementary)
    public function login($user_name, $user_password) {
        try {
            if (empty($user_name) || empty($user_password)) {
                return $this->response("error", [], "Username and password are required.");
            }

            $query = "SELECT 
                        user_id, user_name, user_password, user_is_superadmin,
                        pat_id, doc_id, staff_id
                      FROM {$this->table} 
                      WHERE user_name = :user_name";
            $stmt = $this->conn->prepare($query);
            $stmt->bindParam(':user_name', $user_name);
            $stmt->execute();

            $user = $stmt->fetch(PDO::FETCH_ASSOC);

            if ($user && password_verify($user_password, $user['user_password'])) {
                // Update last login
                $updateQuery = "UPDATE {$this->table} SET user_last_login = NOW() WHERE user_id = :user_id";
                $updateStmt = $this->conn->prepare($updateQuery);
                $updateStmt->bindParam(':user_id', $user['user_id'], PDO::PARAM_INT);
                $updateStmt->execute();

                // Remove password from response and add user_type
                unset($user['user_password']);
                $user['user_type'] = $this->getUserType(
                    $user['pat_id'], 
                    $user['doc_id'], 
                    $user['staff_id'],
                    $user['user_is_superadmin']  // ← ADD THIS PARAMETER!
                );

                return $this->response("success", $user, "Login successful.");
            } else {
                return $this->response("error", [], "Invalid username or password.");
            }
        } catch (PDOException $e) {
            return $this->response("error", [], $e->getMessage());
        }
    }
    
    // NEW: Create superadmin account (supplementary/not used)
    public function createSuperAdmin($user_name, $user_password) {
        return $this->createUser($user_name, $user_password, null, null, null, true);
    }
}
?>