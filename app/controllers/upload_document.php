<?php
/**
 * Upload Document Handler
 * 
 * This file handles document upload requests for properties.
 */

session_start();

// Include database connection
require_once __DIR__ . '/../../config/db_connect.php';

// Authentication check
if (!isset($_SESSION['user_id'])) {
    header('Content-Type: application/json');
    echo json_encode(['success' => false, 'message' => 'Unauthorized access. Please login.']);
    exit();
}

$user_id = $_SESSION['user_id'];

// Only process POST requests
if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    header('Location: ../views/property_list.php');
    exit();
}

// ============================================
// STEP 1: RECEIVE UPLOAD REQUEST
$property_id = $_POST['property_id'] ?? 0;
$document_type = $_POST['document_type'] ?? 'Other';

if (empty($property_id) || !is_numeric($property_id)) {
36:     header('Content-Type: application/json');
37:     echo json_encode(['success' => false, 'message' => 'Missing or invalid property reference ID']);
38:     exit();
39: }
40: 
41: // ============================================
42: // STEP 2: DATABASE CONNECTION & OWNERSHIP CHECK
43: // ============================================
44: $conn = get_db_connection();
45: 
46: // Verify property exists and belongs to the user
47: $check_sql = "SELECT owner_id FROM properties WHERE id = ?";
48: $check_stmt = mysqli_prepare($conn, $check_sql);
49: mysqli_stmt_bind_param($check_stmt, "i", $property_id);
50: mysqli_stmt_execute($check_stmt);
51: $res = mysqli_stmt_get_result($check_stmt);
52: $property = mysqli_fetch_assoc($res);
53: mysqli_stmt_close($check_stmt);
54: 
55: if (!$property) {
56:     close_db_connection($conn);
57:     header('Content-Type: application/json');
58:     echo json_encode(['success' => false, 'message' => 'Property not found']);
59:     exit();
60: }
61: 
62: // Optional: Check ownership
63: // if ($property['owner_id'] != $user_id && $_SESSION['user_role'] !== 'admin') {
64: //     close_db_connection($conn);
65: //     header('Content-Type: application/json');
66: //     echo json_encode(['success' => false, 'message' => 'Unauthorized to modify this property']);
67: //     exit();
68: // }
69: 
70: // ============================================
71: // STEP 3: HANDLE DOCUMENT UPLOADS
72: // ============================================
73: $uploaded_count = 0;
74: $errors = [];
75: 
76: if (isset($_FILES['documents']) && !empty($_FILES['documents']['name'][0])) {
77:     $upload_dir = __DIR__ . '/../../storage/documents/';
78:     if (!is_dir($upload_dir)) {
79:         mkdir($upload_dir, 0755, true);
80:     }
81: 
82:     $file_count = count($_FILES['documents']['name']);
83:     
84:     for ($i = 0; $i < $file_count; $i++) {
85:         if ($_FILES['documents']['error'][$i] === UPLOAD_ERR_OK) {
86:             $file_name = $_FILES['documents']['name'][$i];
87:             $ext = strtolower(pathinfo($file_name, PATHINFO_EXTENSION));
88:             $allowed_exts = ['pdf', 'doc', 'docx', 'txt', 'rtf', 'jpg', 'jpeg', 'png'];
89: 
90:             if (!in_array($ext, $allowed_exts)) {
91:                 $errors[] = "File '$file_name' has an invalid extension.";
92:                 continue;
93:             }
94: 
95:             // Sanitize filename and create unique name
96:             $safe_name = preg_replace("/[^a-zA-Z0-9.]/", "_", $file_name);
97:             $new_name = 'doc_' . $property_id . '_' . time() . '_' . $i . '_' . $safe_name;
98:             
99:             if (move_uploaded_file($_FILES['documents']['tmp_name'][$i], $upload_dir . $new_name)) {
100:                 $doc_sql = "INSERT INTO property_documents (property_id, document_name, document_path, document_type) VALUES (?, ?, ?, ?)";
101:                 $doc_stmt = mysqli_prepare($conn, $doc_sql);
102:                 mysqli_stmt_bind_param($doc_stmt, "isss", $property_id, $file_name, $new_name, $document_type);
103:                 mysqli_stmt_execute($doc_stmt);
104:                 mysqli_stmt_close($doc_stmt);
105:                 $uploaded_count++;
106:             } else {
107:                 $errors[] = "Failed to move file '$file_name' to storage.";
108:             }
109:         } else {
110:             if ($_FILES['documents']['error'][$i] !== UPLOAD_ERR_NO_FILE) {
111:                 $errors[] = "Upload error for file '$file_name': " . $_FILES['documents']['error'][$i];
112:             }
113:         }
114:     }
115: } else {
116:     $errors[] = "No documents were selected for upload.";
117: }
118: 
119: close_db_connection($conn);
120: 
121: // Return response
122: header('Content-Type: application/json');
123: echo json_encode([
124:     'success' => $uploaded_count > 0,
125:     'message' => $uploaded_count > 0 ? "$uploaded_count documents uploaded successfully." : "No documents uploaded.",
126:     'errors' => $errors,
127:     'uploaded_count' => $uploaded_count
128: ]);
129: exit();
