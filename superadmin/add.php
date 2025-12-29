<?php
session_start();
require_once __DIR__ . '/../functions/db/database.php';

$errors = [];
$success = '';
$username = '';
$email = '';
$full_name = '';
$status = 'active';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $username   = trim($_POST['username'] ?? '');
    $email      = trim($_POST['email'] ?? '');
    $password   = $_POST['password'] ?? '';
    $full_name  = trim($_POST['full_name'] ?? '');
    $status     = strtolower(trim($_POST['status'] ?? 'active'));

    if ($username === '') { $errors[] = 'Username is required'; }
    if ($email === '' || !filter_var($email, FILTER_VALIDATE_EMAIL)) { $errors[] = 'Valid email is required'; }
    if ($password === '') { $errors[] = 'Password is required'; }
    if ($full_name === '') { $errors[] = 'Full name is required'; }
    if (!in_array($status, ['active','inactive'], true)) { $errors[] = 'Status must be active or inactive'; }

    if (empty($errors)) {
        try {
            $pdo = getPDO();

            $stmt = $pdo->prepare('SELECT 1 FROM superadmins WHERE username = :username LIMIT 1');
            $stmt->execute([':username' => $username]);
            if ($stmt->fetch()) { $errors[] = 'Username already exists'; }

            $stmt = $pdo->prepare('SELECT 1 FROM superadmins WHERE email = :email LIMIT 1');
            $stmt->execute([':email' => $email]);
            if ($stmt->fetch()) { $errors[] = 'Email already exists'; }

            if (empty($errors)) {
                $hashed = password_hash($password, PASSWORD_DEFAULT);
                $stmt = $pdo->prepare('INSERT INTO superadmins (username, email, password, full_name, status, last_login) VALUES (:username, :email, :password, :full_name, :status, NULL)');
                $stmt->execute([
                    ':username'  => $username,
                    ':email'     => $email,
                    ':password'  => $hashed,
                    ':full_name' => $full_name,
                    ':status'    => $status,
                ]);
                $success = 'Superadmin added successfully.';
                $username = '';
                $email = '';
                $full_name = '';
                $status = 'active';
            }
        } catch (Throwable $e) {
            $errors[] = 'An error occurred while saving.';
        }
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="utf-8">
  <meta name="viewport" content="width=device-width, initial-scale=1">
  <title>Add Superadmin</title>
  <style>
    body { font-family: -apple-system, BlinkMacSystemFont, 'Segoe UI', Roboto, Arial, sans-serif; background:#f7f9fb; margin:0; }
    .container { max-width: 720px; margin: 40px auto; background:#fff; border-radius:12px; box-shadow:0 10px 25px rgba(0,0,0,0.08); padding:24px; }
    h1 { margin:0 0 16px; font-size:22px; color:#2f4050; }
    .form-group { margin-bottom:14px; }
    label { display:block; margin-bottom:6px; font-weight:600; color:#2f4050; }
    input, select { width:100%; padding:10px 12px; border:1px solid #e1e6ed; border-radius:8px; font-size:14px; outline:none; }
    input:focus, select:focus { border-color:#1ABB9C; box-shadow:0 0 0 3px rgba(26,187,156,0.15); }
    .btn { display:inline-block; padding:10px 14px; border:none; border-radius:8px; background:#1ABB9C; color:#fff; font-weight:600; cursor:pointer; }
    .btn:hover { background:#13866a; }
    .alert { padding:12px 14px; border-radius:8px; margin-bottom:14px; font-size:14px; }
    .alert-danger { background:#fff5f5; color:#c0392b; border:1px solid #f3c4c4; }
    .alert-success { background:#f0fff6; color:#1e7e34; border:1px solid #bfe7d7; }
    .card { border:1px solid #eef2f7; border-radius:12px; padding:16px; }
  </style>
  </head>
<body>
  <div class="container">
    <h1>Add Superadmin</h1>
    <?php if (!empty($errors)): ?>
      <div class="alert alert-danger">
        <?php foreach ($errors as $err): ?>
          <div><?php echo htmlspecialchars($err); ?></div>
        <?php endforeach; ?>
      </div>
    <?php endif; ?>
    <?php if ($success !== ''): ?>
      <div class="alert alert-success"><?php echo htmlspecialchars($success); ?></div>
    <?php endif; ?>
    <div class="card">
      <form method="post" action="add.php">
        <div class="form-group">
          <label for="username">Username</label>
          <input type="text" id="username" name="username" value="<?php echo htmlspecialchars($username); ?>" required>
        </div>
        <div class="form-group">
          <label for="email">Email</label>
          <input type="email" id="email" name="email" value="<?php echo htmlspecialchars($email); ?>" required>
        </div>
        <div class="form-group">
          <label for="password">Password</label>
          <input type="password" id="password" name="password" required>
        </div>
        <div class="form-group">
          <label for="full_name">Full Name</label>
          <input type="text" id="full_name" name="full_name" value="<?php echo htmlspecialchars($full_name); ?>" required>
        </div>
        <div class="form-group">
          <label for="status">Status</label>
          <select id="status" name="status">
            <option value="active" <?php echo $status==='active'?'selected':''; ?>>Active</option>
            <option value="inactive" <?php echo $status==='inactive'?'selected':''; ?>>Inactive</option>
          </select>
        </div>
        <button type="submit" class="btn">Add Superadmin</button>
      </form>
    </div>
  </div>
</body>
</html>