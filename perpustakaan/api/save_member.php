<?php
require_once __DIR__ . '/../includes/config.php';

header('Content-Type: application/json');

// Cek login dan role admin
if (!is_logged_in() || $_SESSION['role'] !== 'admin') {
    echo json_encode(['success' => false, 'message' => 'Unauthorized']);
    exit;
}

// Get data dari form
$memberId = isset($_POST['id']) ? intval($_POST['id']) : 0;
$firstName = trim($_POST['firstName'] ?? '');
$lastName = trim($_POST['lastName'] ?? '');
$email = trim($_POST['email'] ?? '');
$phone = trim($_POST['phone'] ?? '');
$address = trim($_POST['address'] ?? '');
$birthDate = $_POST['birthDate'] ?? null;
$gender = $_POST['gender'] ?? null;
$memberType = $_POST['memberType'] ?? 'student';
$memberRole = $_POST['memberRole'] ?? 'library_member';
$institution = trim($_POST['institution'] ?? '');
$duration = isset($_POST['duration']) ? intval($_POST['duration']) : 12;

// Username & Password dari form
$username = trim($_POST['username'] ?? '');
$password = isset($_POST['password']) ? trim($_POST['password']) : '';

try {
    // Validasi
    if (empty($firstName) || empty($lastName) || empty($email) || empty($phone) || empty($username)) {
        throw new Exception('Data wajib tidak lengkap');
    }
    
    // Validasi email
    if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
        throw new Exception('Format email tidak valid');
    }
    
    // Validasi username
    if (!preg_match('/^[a-zA-Z0-9_]+$/', $username)) {
        throw new Exception('Username hanya boleh huruf, angka, dan underscore');
    }
    
    // Validasi password untuk member baru
    if ($memberId == 0 && empty($password)) {
        throw new Exception('Password wajib diisi untuk anggota baru');
    }
    
    if (!empty($password) && strlen($password) < 6) {
        throw new Exception('Password minimal 6 karakter');
    }
    
    // Handle upload photo
    $photoFileName = null;
    $uploadDir = __DIR__ . '/../uploads/members/';
    
    if (!is_dir($uploadDir)) {
        if (!mkdir($uploadDir, 0777, true)) {
            throw new Exception('Gagal membuat folder upload');
        }
    }
    
    // Cek upload foto
    if (isset($_FILES['photo']) && $_FILES['photo']['error'] === UPLOAD_ERR_OK) {
        $fileSize = $_FILES['photo']['size'];
        $maxSize = 2 * 1024 * 1024; // 2MB
        
        if ($fileSize > $maxSize) {
            throw new Exception('Ukuran foto terlalu besar. Maksimal 2MB');
        }
        
        if ($fileSize == 0) {
            throw new Exception('File foto kosong atau corrupt');
        }
        
        $fileExtension = strtolower(pathinfo($_FILES['photo']['name'], PATHINFO_EXTENSION));
        $allowedExtensions = ['jpg', 'jpeg', 'png', 'gif'];
        
        if (!in_array($fileExtension, $allowedExtensions)) {
            throw new Exception('Format foto tidak didukung. Gunakan: jpg, jpeg, png, gif');
        }
        
        $fileName = 'member_' . time() . '_' . uniqid() . '.' . $fileExtension;
        $targetPath = $uploadDir . $fileName;
        
        if (move_uploaded_file($_FILES['photo']['tmp_name'], $targetPath)) {
            $photoFileName = $fileName;
        } else {
            throw new Exception('Gagal upload foto');
        }
    }
    
    // Get default permissions based on role
    $defaultPermissions = [
        'library_member' => '{"can_borrow_books":true,"can_add_bibliography":false,"can_view_catalog":true,"can_request_books":true,"can_view_reports":false}',
        'intern' => '{"can_borrow_books":false,"can_add_bibliography":true,"can_view_catalog":true,"can_request_books":false,"can_view_reports":false}',
        'staff' => '{"can_borrow_books":true,"can_add_bibliography":true,"can_view_catalog":true,"can_request_books":true,"can_view_reports":true}'
    ];
    
    $permissions = $defaultPermissions[$memberRole] ?? $defaultPermissions['library_member'];
    
    $conn->begin_transaction();
    
    if ($memberId > 0) {
        // ========================================
        // UPDATE EXISTING MEMBER
        // ========================================
        
        $checkSql = "SELECT member_id FROM members WHERE email = ? AND member_id != ?";
        $checkStmt = $conn->prepare($checkSql);
        $checkStmt->bind_param("si", $email, $memberId);
        $checkStmt->execute();
        if ($checkStmt->get_result()->num_rows > 0) {
            throw new Exception('Email sudah digunakan oleh anggota lain');
        }
        
        $sql = "UPDATE members SET 
                first_name = ?,
                last_name = ?,
                email = ?,
                phone = ?,
                address = ?,
                birth_date = ?,
                gender = ?,
                member_type = ?,
                member_role = ?,
                permissions = ?,
                institution = ?";
        
        $params = [$firstName, $lastName, $email, $phone, $address, 
                   $birthDate, $gender, $memberType, $memberRole, 
                   $permissions, $institution];
        $types = "sssssssssss";
        
        if ($photoFileName) {
            // Hapus foto lama
            $oldPhotoSql = "SELECT photo FROM members WHERE member_id = ?";
            $oldStmt = $conn->prepare($oldPhotoSql);
            $oldStmt->bind_param("i", $memberId);
            $oldStmt->execute();
            $oldResult = $oldStmt->get_result();
            
            if ($oldResult->num_rows > 0) {
                $oldPhoto = $oldResult->fetch_assoc()['photo'];
                if ($oldPhoto) {
                    $oldPath = $uploadDir . $oldPhoto;
                    if (file_exists($oldPath)) {
                        @unlink($oldPath);
                    }
                }
            }
            
            $sql .= ", photo = ?";
            $params[] = $photoFileName;
            $types .= "s";
            
            $oldStmt->close();
        }
        
        $sql .= " WHERE member_id = ?";
        $params[] = $memberId;
        $types .= "i";
        
        $stmt = $conn->prepare($sql);
        $stmt->bind_param($types, ...$params);
        
        if ($stmt->execute()) {
            // Update users table
            $updateUserSql = "UPDATE users SET 
                             fullname = CONCAT(?, ' ', ?),
                             email = ?,
                             username = ?";
            
            $updateParams = [$firstName, $lastName, $email, $username];
            $updateTypes = "ssss";
            
            // Update password jika diisi
            if (!empty($password)) {
                $passwordHash = md5($password);
                $updateUserSql .= ", password = ?";
                $updateParams[] = $passwordHash;
                $updateTypes .= "s";
            }
            
            $updateUserSql .= " WHERE member_id = ?";
            $updateParams[] = $memberId;
            $updateTypes .= "i";
            
            $updateUserStmt = $conn->prepare($updateUserSql);
            $updateUserStmt->bind_param($updateTypes, ...$updateParams);
            $updateUserStmt->execute();
            
            $conn->commit();
            echo json_encode([
                'success' => true,
                'message' => 'Data anggota berhasil diupdate',
                'member_id' => $memberId,
                'username' => $username,
                'photo' => $photoFileName
            ]);
        } else {
            throw new Exception('Gagal update data anggota');
        }
        
    } else {
        // ========================================
        // INSERT NEW MEMBER
        // ========================================
        
        // Cek email duplicate
        $checkSql = "SELECT member_id FROM members WHERE email = ?";
        $checkStmt = $conn->prepare($checkSql);
        $checkStmt->bind_param("s", $email);
        $checkStmt->execute();
        if ($checkStmt->get_result()->num_rows > 0) {
            throw new Exception('Email sudah terdaftar');
        }
        
        // ========================================
        // GENERATE MEMBER CODE
        // ========================================
        $codeSql = "SELECT member_code FROM members 
                    WHERE member_code LIKE 'MBR%' 
                    ORDER BY CAST(SUBSTRING(member_code, 4) AS UNSIGNED) DESC 
                    LIMIT 1";
        $codeResult = $conn->query($codeSql);
        
        if ($codeResult->num_rows > 0) {
            $lastCode = $codeResult->fetch_assoc()['member_code'];
            $lastNumber = intval(substr($lastCode, 3));
            $newNumber = $lastNumber + 1;
        } else {
            $newNumber = 1;
        }
        
        $memberCode = 'MBR' . str_pad($newNumber, 3, '0', STR_PAD_LEFT);
        
        // Double check duplicate
        $checkCodeSql = "SELECT member_id FROM members WHERE member_code = ?";
        $checkCodeStmt = $conn->prepare($checkCodeSql);
        $checkCodeStmt->bind_param("s", $memberCode);
        $checkCodeStmt->execute();
        
        while ($checkCodeStmt->get_result()->num_rows > 0) {
            $newNumber++;
            $memberCode = 'MBR' . str_pad($newNumber, 3, '0', STR_PAD_LEFT);
            $checkCodeStmt->bind_param("s", $memberCode);
            $checkCodeStmt->execute();
        }
        
        // ========================================
        // CEK USERNAME DUPLICATE DI TABEL USERS
        // ========================================
        $checkUserSql = "SELECT id FROM users WHERE username = ?";
        $checkUserStmt = $conn->prepare($checkUserSql);
        $checkUserStmt->bind_param("s", $username);
        $checkUserStmt->execute();
        
        if ($checkUserStmt->get_result()->num_rows > 0) {
            throw new Exception('Username sudah digunakan. Silakan gunakan username lain.');
        }
        $checkUserStmt->close();
        
        // Hash password
        $passwordHash = md5($password);
        
        // Hitung tanggal
        $joinDate = date('Y-m-d');
        $expireDate = date('Y-m-d', strtotime("+$duration months"));
        
        // ========================================
        // INSERT MEMBER (TANPA username & password)
        // ========================================
        $sql = "INSERT INTO members 
                (member_code, first_name, last_name,
                 email, phone, address, birth_date, gender, 
                 member_type, member_role, permissions, institution,
                 photo, status, join_date, expired_date) 
                VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, 'active', ?, ?)";
        
        $stmt = $conn->prepare($sql);
        $stmt->bind_param("sssssssssssssss",
            $memberCode, 
            $firstName, $lastName,
            $email, $phone, $address, $birthDate, $gender,
            $memberType, $memberRole, $permissions, $institution,
            $photoFileName, $joinDate, $expireDate
        );
        
        if ($stmt->execute()) {
            $newMemberId = $conn->insert_id;
            
            // ========================================
            // BUAT USER ACCOUNT DI TABEL USERS
            // ========================================
            $userSql = "INSERT INTO users (fullname, email, username, password, role, member_id) 
                        VALUES (?, ?, ?, ?, 'member', ?)";
            $userStmt = $conn->prepare($userSql);
            $fullname = $firstName . ' ' . $lastName;
            $userStmt->bind_param("ssssi", $fullname, $email, $username, $passwordHash, $newMemberId);
            
            if ($userStmt->execute()) {
                $conn->commit();
                
                echo json_encode([
                    'success' => true,
                    'message' => 'Anggota baru berhasil ditambahkan',
                    'member_id' => $newMemberId,
                    'member_code' => $memberCode,
                    'username' => $username,
                    'photo' => $photoFileName
                ]);
            } else {
                throw new Exception('Gagal membuat user account: ' . $userStmt->error);
            }
            
            $userStmt->close();
        } else {
            throw new Exception('Gagal menyimpan data anggota: ' . $stmt->error);
        }
        
        $stmt->close();
    }
    
} catch (Exception $e) {
    $conn->rollback();
    
    // Hapus file upload jika ada error
    if (isset($photoFileName) && $photoFileName && isset($uploadDir)) {
        $errorPath = $uploadDir . $photoFileName;
        if (file_exists($errorPath)) {
            @unlink($errorPath);
        }
    }
    
    echo json_encode([
        'success' => false,
        'message' => $e->getMessage()
    ]);
}

$conn->close();
?>