<?php
include '../config/header.php';

$name = $email = $password = $error = '';
$success = false;
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
		$name = trim($_POST['name'] ?? '');
		$email = trim($_POST['email'] ?? '');
		$password = $_POST['password'] ?? '';
		if (!$name || !$email || !$password) {
				$error = 'All fields are required.';
		} elseif (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
				$error = 'Invalid email address.';
		} else {
				include '../config/db.php';
				$stmt = mysqli_prepare($conn, "SELECT id FROM users WHERE email = ?");
				mysqli_stmt_bind_param($stmt, 's', $email);
				mysqli_stmt_execute($stmt);
				mysqli_stmt_store_result($stmt);
				if (mysqli_stmt_num_rows($stmt) > 0) {
						$error = 'Email already registered.';
				} else {
						$hash = password_hash($password, PASSWORD_DEFAULT);
						$stmt = mysqli_prepare($conn, "INSERT INTO users (name, email, password, created_at) VALUES (?, ?, ?, NOW())");
						mysqli_stmt_bind_param($stmt, 'sss', $name, $email, $hash);
						if (mysqli_stmt_execute($stmt)) {
								$success = true;
						} else {
								$error = 'Registration failed. Please try again.';
						}
				}
				mysqli_stmt_close($stmt);
				mysqli_close($conn);
		}
}
?>
<div class="flex items-center justify-center min-h-[60vh]">
	<div class="bg-white rounded-xl shadow-lg p-8 w-full max-w-md">
		<h2 class="text-2xl font-bold text-gray-800 mb-6 text-center">Create an Account</h2>
		<?php if ($success): ?>
			<div class="bg-green-100 text-green-700 p-3 rounded mb-4 text-center">Registration successful! <a href="login.php" class="text-blue-600 underline">Login here</a>.</div>
		<?php endif; ?>
		<?php if ($error): ?>
			<div class="bg-red-100 text-red-700 p-3 rounded mb-4 text-center"><?php echo htmlspecialchars($error); ?></div>
		<?php endif; ?>
		<form method="post" class="space-y-5">
			<div>
				<label class="block mb-1 font-medium text-gray-700">Name</label>
				<input type="text" name="name" class="w-full border border-gray-300 rounded px-3 py-2 focus:outline-none focus:ring-2 focus:ring-blue-500" required value="<?php echo htmlspecialchars($name); ?>">
			</div>
			<div>
				<label class="block mb-1 font-medium text-gray-700">Email</label>
				<input type="email" name="email" class="w-full border border-gray-300 rounded px-3 py-2 focus:outline-none focus:ring-2 focus:ring-blue-500" required value="<?php echo htmlspecialchars($email); ?>">
			</div>
			<div>
				<label class="block mb-1 font-medium text-gray-700">Password</label>
				<input type="password" name="password" class="w-full border border-gray-300 rounded px-3 py-2 focus:outline-none focus:ring-2 focus:ring-blue-500" required>
			</div>
			<div class="text-center">
				<button class="bg-blue-600 hover:bg-blue-700 text-white px-6 py-2 rounded-lg font-semibold shadow transition">Register</button>
			</div>
		</form>
		<div class="mt-4 text-center text-sm text-gray-600">
			Already have an account? <a href="login.php" class="text-blue-600 underline">Login</a>
		</div>
	</div>
</div>
<?php include '../config/footer.php'; ?>
