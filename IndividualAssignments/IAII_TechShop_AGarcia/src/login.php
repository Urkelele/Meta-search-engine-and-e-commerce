<?php
$mySqli = DB::get();

$message = "";
$toastClass = "";

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $email = $_POST['email'];
    $password = $_POST['password'];

        $stmt = $mySqli->prepare("
            SELECT user_id, name, password, IFNULL(is_verified,0) as is_verified, IFNULL(is_admin,0) as is_admin 
            FROM users WHERE email = ? LIMIT 1"
            );
        $stmt->bind_param("s", $email);
        $stmt->execute();
        $stmt->store_result();

        if ($stmt->num_rows > 0) {
            $stmt->bind_result($db_id, $db_name, $db_password, $db_is_verified, $db_is_admin);
            $stmt->fetch();

            // Password_verify because the password is hashed
            if (password_verify($password, $db_password))
            {
                // Checks if the user is verified in the DB
                if(!$db_is_verified)
                {
                    $message = "Please verify your email first";
                    $toastClass = "bg-warning";
                }
                else
                {
                    // Successful login
                    if (session_status() === PHP_SESSION_NONE) session_start();
                    session_regenerate_id(true);

                    $_SESSION['user'] = [
                        'id' => (int)$db_id,
                        'email' => $email,
                        'name' => $db_name,
                        'is_admin' => (int)$db_is_admin,
                        'is_verified' => (int)$db_is_verified
                    ];

                    header("Location: index.php?page=home");
                    exit();
                }

            } else {
                $message = "Incorrect password";
                $toastClass = "bg-danger";
            }
        } else {
            $message = "Email not found";
            $toastClass = "bg-warning";
    }

    $stmt->close();
    $mySqli->close();
}
?>

<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" 
          content="width=device-width, initial-scale=1.0">
    <link href= "https://cdn.jsdelivr.net/npm/bootstrap@5.0.2/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href= "https://cdnjs.cloudflare.com/ajax/libs/font-awesome/4.7.0/css/font-awesome.css">
    <link rel="shortcut icon" href= "https://cdn-icons-png.flaticon.com/512/295/295128.png">
    <script src= "https://cdn.jsdelivr.net/npm/bootstrap@5.0.2/dist/js/bootstrap.bundle.min.js"></script>
    <link rel="stylesheet" href="../css/login.css">
    <title>Login Page</title>
</head>

<body class="bg-light">
    <div class="container p-5 d-flex flex-column align-items-center">
        <?php if ($message): ?>
            <div class="toast align-items-center text-white 
            <?php echo $toastClass; ?> border-0" role="alert" aria-live="assertive" aria-atomic="true">
                <div class="d-flex">
                    <div class="toast-body">
                        <?php echo $message; ?>
                    </div>
                    <button type="button" class="btn-close btn-close-white me-2 m-auto" data-bs-dismiss="toast" aria-label="Close"></button>
                </div>
            </div>
        <?php endif; ?>
        <form action="" method="post" class="form-control mt-5 p-4" style="height:auto; width:380px; box-shadow: rgba(60, 64, 67, 0.3) 
            0px 1px 2px 0px, rgba(60, 64, 67, 0.15) 0px 2px 6px 2px;">
            <div class="row">
                <i class="fa fa-user-circle-o fa-3x mt-1 mb-2"style="text-align: center; color: green;"></i>
                <h5 class="text-center p-4" style="font-weight: 700;">Login Into Your Account</h5>
            </div>
            <div class="col-mb-3">
                <label for="email"><i class="fa fa-envelope"></i> Email</label>
                <input type="text" name="email" id="email" class="form-control" required>
            </div>
            <div class="mb-3 mt-3">
                <label for="password">
                    <i class="fa fa-lock"></i> Password</label>
                <div class="input-group">
                    <input type="password" name="password" id="password" class="form-control" required>
                    <span class="input-group-text" id="togglePassword" style="cursor: pointer;">
                        <i class="fa fa-eye"></i>
                    </span>
                </div>
            </div>
            <div class="col mb-3 mt-3">
                <button type="submit" 
                  class="btn btn-success bg-success" style="font-weight: 600;">Login</button>
            </div>
            <div class="col mb-2 mt-4">
                <p class="text-center"  style="font-weight: 600; color: navy;">
                    <a href="?page=register" style="text-decoration: none;">Create Account</a> OR
                    <a href="?page=forgot_password" style="text-decoration: none;">Forgot Password</a></p>
            </div>
        </form>
    </div>
    <script>
        var toastElList = [].slice.call(document.querySelectorAll('.toast'))
        var toastList = toastElList.map(function (toastEl) {
            return new bootstrap.Toast(toastEl, { delay: 3000 });
        });
        toastList.forEach(toast => toast.show());
    </script>

     <script>
    const togglePassword = document.querySelector("#togglePassword");
    const password = document.querySelector("#password");

    togglePassword.addEventListener("click", function () {
        const type = password.getAttribute("type") === "password" ? "text" : "password";
        password.setAttribute("type", type);

        this.querySelector("i").classList.toggle("fa-eye");
        this.querySelector("i").classList.toggle("fa-eye-slash");
    });
    </script>
</body>
</html>

<?php