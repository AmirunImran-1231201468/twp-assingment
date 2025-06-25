<?php
// Database connection
$servername = "localhost";
$username = "root"; // Replace with your MySQL username
$password = " "; // Replace with your MySQL password
$dbname = "Milkshake";

// Create connection
$conn = new mysqli($servername, $username, $password, $dbname);

// Check connection
if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}

// Process form data when form is submitted
if ($_SERVER["REQUEST_METHOD"] == "POST") {
    // Validate and sanitize input
    $firstName = htmlspecialchars(trim($_POST['firstName']));
    $lastName = htmlspecialchars(trim($_POST['lastName']));
    $username = htmlspecialchars(trim($_POST['username']));
    $email = filter_var(trim($_POST['email']), FILTER_SANITIZE_EMAIL);
    $password = $_POST['password'];
    $confirmPassword = $_POST['confirmPassword'];
    $telephone = htmlspecialchars(trim($_POST['telephone']));

    // Validate inputs
    $errors = [];
    
    if (empty($firstName)) $errors[] = "First name is required.";
    if (empty($lastName)) $errors[] = "Last name is required.";
    if (empty($username)) $errors[] = "Username is required.";
    if (empty($email)) $errors[] = "Email is required.";
    if (!filter_var($email, FILTER_VALIDATE_EMAIL)) $errors[] = "Invalid email format.";
    if (empty($password)) $errors[] = "Password is required.";
    if (strlen($password) < 8) $errors[] = "Password must be at least 8 characters.";
    if ($password !== $confirmPassword) $errors[] = "Passwords do not match.";
    if (empty($telephone)) $errors[] = "Phone number is required.";
    if (!preg_match('/^[0-9]{10,15}$/', $telephone)) $errors[] = "Invalid phone number format.";

    // Check if username or email already exists
    if (empty($errors)) {
        $stmt = $conn->prepare("SELECT id FROM customers WHERE username = ? OR email = ?");
        $stmt->bind_param("ss", $username, $email);
        $stmt->execute();
        $stmt->store_result();
        
        if ($stmt->num_rows > 0) {
            $errors[] = "Username or email already exists.";
        }
        $stmt->close();
    }

    // If no errors, insert into database
    if (empty($errors)) {
        $hashedPassword = password_hash($password, PASSWORD_DEFAULT);
        
        $stmt = $conn->prepare("INSERT INTO customers (first_name, last_name, username, email, password, phone_number, student_id) VALUES (?, ?, ?, ?, ?, ?, ?)");
        $stmt->bind_param("sssssss", $firstName, $lastName, $username, $email, $hashedPassword, $telephone, $studentId);
        
        if ($stmt->execute()) {
            $success = "Registration successful! You can now log in.";
            // Clear form fields
            $firstName = $lastName = $username = $email = $telephone = $studentId = '';
        } else {
            $errors[] = "Error: " . $stmt->error;
        }
        $stmt->close();
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8" />
  <title>Sign Up - Milkshake Haven</title>
  <style>
    body {
      font-family: Arial;
      background: #fff0f5;
      padding: 20px;
    }
    .form-box {
      max-width: 400px;
      margin: 50px auto;
      background: #fff;
      padding: 30px;
      border-radius: 8px;
      box-shadow: 0 4px 12px rgba(128, 0, 128, 0.2);
    }
    h2 {
      text-align: center;
      color: #800080;
      margin-bottom: 20px;
    }
    .form-group {
      margin-bottom: 15px;
    }
    label {
      display: block;
      margin-bottom: 5px;
      color: #555;
      font-size: 14px;
    }
    input {
      width: 100%;
      padding: 10px;
      border: 1px solid #ddd;
      border-radius: 5px;
      box-sizing: border-box;
    }
    input:focus {
      outline: none;
      border-color: #800080;
      box-shadow: 0 0 5px rgba(128, 0, 128, 0.2);
    }
    button {
      width: 100%;
      background-color: #800080;
      color: white;
      border: none;
      padding: 12px;
      cursor: pointer;
      border-radius: 5px;
      font-size: 16px;
      margin-top: 10px;
      transition: background-color 0.3s;
    }
    button:hover {
      background-color: #6a006a;
    }
    .link {
      text-align: center;
      margin-top: 20px;
      color: #555;
    }
    .link a {
      color: #800080;
      text-decoration: none;
    }
    .link a:hover {
      text-decoration: underline;
    }
    .name-fields {
      display: flex;
      gap: 10px;
    }
    .name-fields .form-group {
      flex: 1;
    }
    .password-match {
      color: green;
      font-size: 12px;
      margin-top: 5px;
      display: none;
    }
    .password-mismatch {
      color: red;
      font-size: 12px;
      margin-top: 5px;
      display: none;
    }
    .error {
      color: red;
      font-size: 14px;
      margin-bottom: 15px;
    }
    .success {
      color: green;
      font-size: 14px;
      margin-bottom: 15px;
    }
  </style>
</head>
<body>
  <div class="form-box">
    <h2>Create Your Account</h2>
    
    <?php if (!empty($errors)): ?>
      <div class="error">
        <?php foreach ($errors as $error): ?>
          <p><?php echo $error; ?></p>
        <?php endforeach; ?>
      </div>
    <?php endif; ?>
    
    <?php if (isset($success)): ?>
      <div class="success"><?php echo $success; ?></div>
    <?php endif; ?>
    
    <form method="post" action="<?php echo htmlspecialchars($_SERVER["PHP_SELF"]); ?>">
      <div class="name-fields">
        <div class="form-group">
          <label for="firstName">First Name</label>
          <input type="text" id="firstName" name="firstName" placeholder="First name" required 
                 value="<?php echo isset($firstName) ? $firstName : ''; ?>" />
        </div>
        
        <div class="form-group">
          <label for="lastName">Last Name</label>
          <input type="text" id="lastName" name="lastName" placeholder="Last name" required 
                 value="<?php echo isset($lastName) ? $lastName : ''; ?>" />
        </div>
      </div>
      
      <div class="form-group">
        <label for="username">Username</label>
        <input type="text" id="username" name="username" placeholder="Choose a username" required 
               value="<?php echo isset($username) ? $username : ''; ?>" />
      </div>
      
      <div class="form-group">
        <label for="email">Email</label>
        <input type="email" id="email" name="email" placeholder="Your email address" required 
               value="<?php echo isset($email) ? $email : ''; ?>" />
      </div>

      <div class="form-group">
        <label for="telephone">Phone Number</label>
        <input type="tel" id="telephone" name="telephone" placeholder="Your phone number" required 
               value="<?php echo isset($telephone) ? $telephone : ''; ?>" />
      </div>
      
      <div class="form-group">
        <label for="studentId">Student ID (optional)</label>
        <input type="text" id="studentId" name="studentId" placeholder="Your student ID" 
               value="<?php echo isset($studentId) ? $studentId : ''; ?>" />
      </div>
      
      <div class="form-group">
        <label for="password">Password</label>
        <input type="password" id="password" name="password" placeholder="Create a password" required />
      </div>
      
      <div class="form-group">
        <label for="confirmPassword">Confirm Password</label>
        <input type="password" id="confirmPassword" name="confirmPassword" placeholder="Re-enter your password" required />
        <div id="passwordMatch" class="password-match">Passwords match!</div>
        <div id="passwordMismatch" class="password-mismatch">Passwords do not match!</div>
      </div>
      
      <button type="submit">Sign Up</button>
    </form>
    
    <div class="link">
      Already have an account? <a href="login.php">Login here</a>
    </div>
  </div>

  <script>
    // Add real-time password confirmation check
    document.getElementById('confirmPassword').addEventListener('input', checkPasswordMatch);
    document.getElementById('password').addEventListener('input', checkPasswordMatch);

    function checkPasswordMatch() {
      const password = document.getElementById('password').value;
      const confirmPassword = document.getElementById('confirmPassword').value;
      const matchMessage = document.getElementById('passwordMatch');
      const mismatchMessage = document.getElementById('passwordMismatch');

      if (password && confirmPassword) {
        if (password === confirmPassword) {
          matchMessage.style.display = 'block';
          mismatchMessage.style.display = 'none';
        } else {
          matchMessage.style.display = 'none';
          mismatchMessage.style.display = 'block';
        }
      } else {
        matchMessage.style.display = 'none';
        mismatchMessage.style.display = 'none';
      }
    }
  </script>
</body>
</html>
<?php
$conn->close();
?>