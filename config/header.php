<!DOCTYPE html>

<html>
<head>
        <title>Research Portal</title>
        <?php include __DIR__ . '/../assets/css/tailwind.php'; ?>
</head>
<body class="bg-gray-100 min-h-screen">



<?php if (session_status() === PHP_SESSION_NONE) session_start(); ?>

<header class="bg-white shadow mb-8">
    <div class="max-w-6xl mx-auto px-4 py-4 flex items-center justify-between">
        <div class="flex items-center space-x-3">
            <span class="inline-flex items-center justify-center w-10 h-10 bg-blue-600 text-white rounded-full font-bold text-2xl">RP</span>
            <span class="font-extrabold text-2xl tracking-tight text-gray-800">Research Portal</span>
        </div>
        <nav class="flex space-x-6 items-center">
            <a href="/research-portal/index.php" class="text-gray-700 hover:text-blue-600 font-medium transition">Home</a>
            <a href="/research-portal/submissions/upload.php" class="text-gray-700 hover:text-blue-600 font-medium transition">Upload</a>
            <a href="/research-portal/index.php#submissions" class="text-gray-700 hover:text-blue-600 font-medium transition">Submissions</a>
            <?php if (empty($_SESSION['user_id'])): ?>
                <a href="/research-portal/auth/login.php" class="ml-4 text-blue-600 hover:underline font-semibold">Login</a>
                <a href="/research-portal/auth/register.php" class="ml-2 text-blue-600 hover:underline font-semibold">Register</a>
            <?php else: ?>
                <?php $avatarLetter = strtoupper(substr($_SESSION['user_name'], 0, 1)); ?>
                <span class="ml-4 inline-flex items-center justify-center w-9 h-9 bg-blue-500 text-white rounded-full font-bold text-lg" title="<?php echo htmlspecialchars($_SESSION['user_name']); ?>"><?php echo $avatarLetter; ?></span>
                <span class="ml-2 text-gray-700 font-medium"><?php echo htmlspecialchars($_SESSION['user_name']); ?></span>
                <a href="/research-portal/auth/logout.php" class="ml-4 text-red-500 hover:underline font-semibold">Logout</a>
            <?php endif; ?>
        </nav>
    </div>
</header>

<main class="max-w-6xl mx-auto px-4">
