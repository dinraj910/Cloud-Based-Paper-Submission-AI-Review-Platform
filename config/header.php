<!DOCTYPE html>

<html lang="en">
<head>
        <meta charset="UTF-8">
        <meta name="viewport" content="width=device-width, initial-scale=1.0">
        <title>Research Portal - Share & Discover Academic Research</title>
        <?php include __DIR__ . '/../assets/css/tailwind.php'; ?>
</head>
<body class="bg-gradient-to-br from-gray-50 via-blue-50/30 to-purple-50/30 min-h-screen">

<?php if (session_status() === PHP_SESSION_NONE) session_start(); ?>

<!-- Header with glassmorphism -->
<header class="glass border-b border-white/20 mb-12 sticky top-0 z-50">
    <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-4">
        <div class="flex items-center justify-between">
            <a href="/index.php" class="flex items-center space-x-3 group">
                <div class="w-11 h-11 bg-gradient-to-br from-indigo-500 to-purple-600 rounded-xl flex items-center justify-center shadow-lg group-hover:scale-105 transition-transform">
                    <span class="text-white font-bold text-xl">R</span>
                </div>
                <span class="font-bold text-2xl tracking-tight gradient-text">Research Portal</span>
            </a>
            <nav class="flex space-x-8 items-center">
                <a href="/index.php" class="text-gray-700 hover:text-indigo-600 font-medium transition-colors relative group">
                    Home
                    <span class="absolute -bottom-1 left-0 w-0 h-0.5 bg-gradient-to-r from-indigo-500 to-purple-600 transition-all group-hover:w-full"></span>
                </a>
                <a href="/submissions/upload.php" class="text-gray-700 hover:text-indigo-600 font-medium transition-colors relative group">
                    Upload
                    <span class="absolute -bottom-1 left-0 w-0 h-0.5 bg-gradient-to-r from-indigo-500 to-purple-600 transition-all group-hover:w-full"></span>
                </a>
                <a href="/index.php#submissions" class="text-gray-700 hover:text-indigo-600 font-medium transition-colors relative group">
                    Submissions
                    <span class="absolute -bottom-1 left-0 w-0 h-0.5 bg-gradient-to-r from-indigo-500 to-purple-600 transition-all group-hover:w-full"></span>
                </a>
                <?php if (empty($_SESSION['user_id'])): ?>
                    <a href="/auth/login.php" class="ml-4 px-4 py-2 text-indigo-600 hover:text-indigo-700 font-semibold transition-colors">Login</a>
                    <a href="/auth/register.php" class="px-5 py-2 bg-gradient-to-r from-indigo-600 to-purple-600 text-white rounded-lg font-semibold shadow-md hover:shadow-lg hover:scale-105 transition-all">Sign Up</a>
                <?php else: ?>
                    <?php $avatarLetter = strtoupper(substr($_SESSION['user_name'], 0, 1)); ?>
                    <div class="flex items-center space-x-3 ml-4">
                        <span class="inline-flex items-center justify-center w-10 h-10 bg-gradient-to-br from-indigo-500 to-purple-600 text-white rounded-full font-semibold shadow-md" title="<?php echo htmlspecialchars($_SESSION['user_name']); ?>"><?php echo $avatarLetter; ?></span>
                        <span class="text-gray-700 font-medium"><?php echo htmlspecialchars($_SESSION['user_name']); ?></span>
                        <a href="/auth/logout.php" class="ml-2 text-gray-500 hover:text-red-500 transition-colors">Logout</a>
                    </div>
                <?php endif; ?>
            </nav>
        </div>
    </div>
</header>

<main class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">

