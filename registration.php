<?php

$keyMatrix = array_fill(0, 3, array_fill(0, 3, 0));
$messageVector = array_fill(0, 3, array(0));
$cipherMatrix = array_fill(0, 3, array(0));

function getKeyMatrix($key) {
    global $keyMatrix;
    $k = 0;
    for ($i = 0; $i < 3; $i++) {
        for ($j = 0; $j < 3; $j++) {
            $keyMatrix[$i][$j] = ord($key[$k]) % 65;
            $k++;
        }
    }
}


function encrypt($messageVector) {
    global $keyMatrix, $cipherMatrix;
    for ($i = 0; $i < 3; $i++) {
        $cipherMatrix[$i][0] = 0;
        for ($j = 0; $j < 3; $j++) {
            $cipherMatrix[$i][0] += ($keyMatrix[$i][$j] * $messageVector[$j][0]);
        }
        $cipherMatrix[$i][0] = $cipherMatrix[$i][0] % 26;
    }
}


function hillCipher($message, $key) {
    global $messageVector, $cipherMatrix;
    
    
    getKeyMatrix($key);

   
    for ($i = 0; $i < 3; $i++) {
        $messageVector[$i][0] = ord($message[$i]) % 65;
    }

    
    encrypt($messageVector);

    
    $cipherText = '';
    for ($i = 0; $i < 3; $i++) {
        $cipherText .= chr($cipherMatrix[$i][0] + 65);
    }

    return $cipherText;
   
}




require 'database/config.php';
if (isset($_SESSION['user']))
    header('location: welcomepage.php');
if(isset($_POST["submit"])){
    $email = $_POST["email"];
    $message = $_POST["password"];
    $key = "GYBNQKURP";
    $cipherText = HillCipher($message, $key);
    $duplicate = mysqli_query($conn, "SELECT * FROM user WHERE email = '$email'");
    if(mysqli_num_rows($duplicate) > 0){
        $error_message = 'Email has already taken';
    }
    else{
        if($message == $message){
            $query = "INSERT INTO user VALUES ('','$email','$cipherText')";
            mysqli_query($conn,$query);
            $error_message = 'Registration successful';
        }
        
    }
}


?>


<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link rel="stylesheet" href="style.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/4.7.0/css/font-awesome.css">
    <title>Registration</title>
</head>
<body>
    <?php if (!empty($error_message)) { ?>
        <div id="yesMessage">
            <strong></strong> </p>
            <?= $error_message ?>
            </p>
        </div>
    <?php } ?>
    <section>
        <div class="form-box">
            <div class="form-value">
                <form action="registration.php" method="POST">
                    <h2>Create a account</h2>
                    <div class="inputbox">
                        <input type="text"  name="email" required>
                        <label for="">Email</label>
                    </div>
                    <div class="inputbox">
                        <input type="password"  name="password" id="password" required>
                        <label for="">Password</label>
                    </div>


                    <div class="forget">
                    <label for=""><a href="login.php">Login</a></label>
                    

                    </div>
                    <button type="submit" name="submit">Register</button>
                   
                    
                    
                </form>
            </div>
        </div>
    </section>
</body>
</html>