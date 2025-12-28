

<?php
session_start();
include '../config/header.php';
if (empty($_SESSION['user_id'])) {
    header('Location: /research-portal/auth/login.php');
    exit;
}

$title = $description = $error = $success = '';
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $title = trim($_POST['title'] ?? '');
    $description = trim($_POST['description'] ?? '');
    if (!$title || !isset($_FILES['paper']) || $_FILES['paper']['error'] !== UPLOAD_ERR_OK) {
        $error = 'Title and file are required.';
    } else {
        $file = $_FILES['paper'];
        $fileType = strtolower(pathinfo($file['name'], PATHINFO_EXTENSION));
        $allowed = ['pdf', 'doc', 'docx', 'txt'];
        if (!in_array($fileType, $allowed)) {
            $error = 'Only PDF, DOC, DOCX, and TXT files are allowed.';
        } else {
            // Store file locally as a placeholder for S3
            $uploadDir = __DIR__ . '/uploads/';
            if (!is_dir($uploadDir)) mkdir($uploadDir, 0777, true);
            $fileName = uniqid('paper_', true) . '.' . $fileType;
            $filePath = $uploadDir . $fileName;
            if (move_uploaded_file($file['tmp_name'], $filePath)) {
                $s3Url = '/research-portal/submissions/uploads/' . $fileName; // Placeholder for S3 URL
                include '../config/db.php';
                $stmt = mysqli_prepare($conn, "INSERT INTO submissions (user_id, title, description, s3_file_url, file_type, created_at) VALUES (?, ?, ?, ?, ?, NOW())");
                mysqli_stmt_bind_param($stmt, 'issss', $_SESSION['user_id'], $title, $description, $s3Url, $fileType);
                if (mysqli_stmt_execute($stmt)) {
                    $success = 'Paper uploaded successfully!';
                    $title = $description = '';
                } else {
                    $error = 'Database error. Please try again.';
                }
                mysqli_stmt_close($stmt);
                mysqli_close($conn);
            } else {
                $error = 'File upload failed.';
            }
        }
    }
}
?>


<div class="flex items-center justify-center min-h-[60vh]">
    <div class="bg-white rounded-xl shadow-lg p-8 w-full max-w-lg">
        <h2 class="text-2xl font-bold text-gray-800 mb-6 text-center">Upload Research Paper</h2>
        <?php if ($success): ?>
            <div class="bg-green-100 text-green-700 p-3 rounded mb-4 text-center"><?php echo $success; ?></div>
        <?php endif; ?>
        <?php if ($error): ?>
            <div class="bg-red-100 text-red-700 p-3 rounded mb-4 text-center"><?php echo htmlspecialchars($error); ?></div>
        <?php endif; ?>
        <form method="post" enctype="multipart/form-data" class="space-y-5">
            <div>
                <label class="block mb-1 font-medium text-gray-700">Title</label>
                <input type="text" name="title" class="w-full border border-gray-300 rounded px-3 py-2 focus:outline-none focus:ring-2 focus:ring-blue-500" required value="<?php echo htmlspecialchars($title); ?>">
            </div>
            <div>
                <label class="block mb-1 font-medium text-gray-700">Description</label>
                <textarea name="description" class="w-full border border-gray-300 rounded px-3 py-2 focus:outline-none focus:ring-2 focus:ring-blue-500" rows="3"><?php echo htmlspecialchars($description); ?></textarea>
            </div>
            <div>
                <label class="block mb-1 font-medium text-gray-700">Select File</label>
                <input type="file" name="paper" class="w-full border border-gray-300 rounded px-3 py-2 bg-gray-50" required>
            </div>
            <div class="text-center">
                <button class="bg-blue-600 hover:bg-blue-700 text-white px-6 py-2 rounded-lg font-semibold shadow transition">Upload</button>
            </div>
        </form>
    </div>
</div>

<?php include '../config/footer.php'; ?>
