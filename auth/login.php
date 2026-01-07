<?php
session_start();

$email = $password = $error = '';
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
		$email = trim($_POST['email'] ?? '');
		$password = $_POST['password'] ?? '';
		if (!$email || !$password) {
				$error = 'Both fields are required.';
		} else {
				include '../config/db.php';
				$stmt = mysqli_prepare($conn, "SELECT id, name, password FROM users WHERE email = ?");
				mysqli_stmt_bind_param($stmt, 's', $email);
				mysqli_stmt_execute($stmt);
				mysqli_stmt_store_result($stmt);
				if (mysqli_stmt_num_rows($stmt) === 1) {
						mysqli_stmt_bind_result($stmt, $id, $name, $hash);
						mysqli_stmt_fetch($stmt);
						if (password_verify($password, $hash)) {
								$_SESSION['user_id'] = $id;
								$_SESSION['user_name'] = $name;
								mysqli_stmt_close($stmt);
								mysqli_close($conn);
								header('Location: /index.php');
								exit;
						} else {
								$error = 'Incorrect password.';
						}
				} else {
						$error = 'No account found with that email.';
				}
				mysqli_stmt_close($stmt);
				mysqli_close($conn);
		}
}

include '../config/header.php';
?>
<div class="flex items-center justify-center min-h-[70vh] py-12">
	<div class="glass border border-white/20 rounded-2xl shadow-2xl p-8 w-full max-w-md">
		<div class="text-center mb-8">
			<div class="w-16 h-16 bg-gradient-to-br from-indigo-500 to-purple-600 rounded-2xl flex items-center justify-center mx-auto mb-4 shadow-lg">
				<svg class="w-8 h-8 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
					<path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z"></path>
				</svg>
			</div>
			<h2 class="text-3xl font-bold gradient-text mb-2">Welcome Back</h2>
			<p class="text-gray-600">Sign in to continue to Research Portal</p>
		</div>
		<?php if ($error): ?>
			<div class="bg-red-50 border border-red-200 text-red-700 p-4 rounded-xl mb-6 text-center text-sm">
				<svg class="w-5 h-5 inline mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
					<path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4m0 4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"></path>
				</svg>
				<?php echo htmlspecialchars($error); ?>
			</div>
		<?php endif; ?>
		<form method="post" class="space-y-5">
			<div>
				<label class="block mb-2 font-semibold text-gray-700 text-sm">Email Address</label>
				<input type="email" name="email" class="w-full border border-gray-200 rounded-xl px-4 py-3 focus:outline-none focus:ring-2 focus:ring-indigo-500 focus:border-transparent transition-all bg-white/50" placeholder="you@example.com" required value="<?php echo htmlspecialchars($email); ?>">
			</div>
			<div>
				<label class="block mb-2 font-semibold text-gray-700 text-sm">Password</label>
				<input type="password" name="password" class="w-full border border-gray-200 rounded-xl px-4 py-3 focus:outline-none focus:ring-2 focus:ring-indigo-500 focus:border-transparent transition-all bg-white/50" placeholder="••••••••" required>
			</div>
			<button type="submit" class="w-full bg-gradient-to-r from-indigo-600 to-purple-600 hover:from-indigo-700 hover:to-purple-700 text-white px-6 py-3.5 rounded-xl font-semibold shadow-lg hover:shadow-xl hover:scale-105 transition-all mt-6">
				Sign In
			</button>
		</form>
		<div class="mt-6 text-center text-sm text-gray-600">
			Don't have an account? <a href="register.php" class="text-indigo-600 hover:text-indigo-700 font-semibold">Create one</a>
		</div>
	</div>
</div>
<?php include '../config/footer.php'; ?>
