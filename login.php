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


function determinantOfMatrix($matrix) {
    return $matrix[0][0] * $matrix[1][1] - $matrix[0][1] * $matrix[1][0];
}


function moduloInverse($a, $m) {
    $m0 = $m;
    $x0 = 0;
    $x1 = 1;

    if ($m == 1)
        return 0;

    while ($a > 1) {
        // q is quotient
        $q = intval($a / $m);

        $t = $m;

        // m is remainder now, process same as Euclid's algo
        $m = $a % $m;
        $a = $t;

        $t = $x0;
        $x0 = $x1 - $q * $x0;
        $x1 = $t;
    }

    // Make x1 positive
    if ($x1 < 0)
        $x1 += $m0;

    return $x1;
}

function adjoint($matrix) {
    return array(
        array($matrix[1][1], -$matrix[0][1], -$matrix[1][0]),
        array(-$matrix[1][2], $matrix[0][2], $matrix[1][0]),
        array($matrix[1][2], -$matrix[0][2], $matrix[0][0])
    );
}


function inverse($matrix) {
    $determinant = determinantOfMatrix($matrix);
    if ($determinant == 0) {
        return false; 
    }

    $modInv = moduloInverse($determinant, 26);
    if ($modInv === false) {
        return false; 
    }

    $adj = adjoint($matrix);
    $inverse = array();
    foreach ($adj as $row) {
        $inverseRow = array();
        foreach ($row as $element) {
            $inverseRow[] = ($element * $modInv) % 26;
        }
        $inverse[] = $inverseRow;
    }
    return $inverse;
}


function decrypt($cipherMatrix) {
    global $keyMatrix;
    $inverseKeyMatrix = inverse($keyMatrix);
    if ($inverseKeyMatrix === false) {
        return "Error: Key matrix is not invertible!";
    }

    $messageVector = array_fill(0, 3, array(0));
    for ($i = 0; $i < 3; $i++) {
        $messageVector[$i][0] = 0;
        for ($j = 0; $j < 3; $j++) {
            $messageVector[$i][0] += ($inverseKeyMatrix[$i][$j] * $cipherMatrix[$j][0]);
        }
        $messageVector[$i][0] = $messageVector[$i][0] % 26;
    }

    $decryptedText = '';
    for ($i = 0; $i < 3; $i++) {
        $decryptedText .= chr($messageVector[$i][0] + 65);
    }

    return $decryptedText;
}


function hillCipher($message, $key) {
    global $messageVector, $cipherMatrix, $keyMatrix;

    
    getKeyMatrix($key);

   
    for ($i = 0; $i < 3; $i++) {
        $messageVector[$i][0] = ord($message[$i]) % 65;
    }

    
    for ($i = 0; $i < 3; $i++) {
        $cipherMatrix[$i][0] = 0;
        for ($j = 0; $j < 3; $j++) {
            $cipherMatrix[$i][0] += ($keyMatrix[$i][$j] * $messageVector[$j][0]);
        }
        $cipherMatrix[$i][0] = $cipherMatrix[$i][0] % 26;
    }


    $cipherText = '';
    for ($i = 0; $i < 3; $i++) {
        $cipherText .= chr($cipherMatrix[$i][0] + 65);
    }

    return $cipherText;
}


session_start();

if (isset($_SESSION['user'])) {
    header('location: welcomepage.php');
    exit; 
}

$error_message = '';

if ($_POST) {
    include('database/connection.php');

    $email = $_POST['email'];
    $message = $_POST['password'];
    $key = "GYBNQKURP"; 
    $cipherText = HillCipher($message, $key);

    
    $query = 'SELECT * FROM user WHERE user.email=:email AND user.password=:password';
    $stmt = $conn->prepare($query);
    $stmt->bindParam(':email', $email);
    $stmt->bindParam(':password', $cipherText);
    $stmt->execute();

    
    if ($stmt->rowCount() > 0) {
        $user = $stmt->setFetchMode(PDO::FETCH_ASSOC); 
        $_SESSION['user'] = $user; 
        header('location: welcomepage.php'); 
        exit; 
    } else {
        $error_message = 'Please make sure that email and password are correct.';
    }
}


?>





<!DOCTYPE html>
<html>

<head>
    <meta charset="UTF-8">
    <meta http-equiv="X-UA-Compatible" content="IE=edge">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link rel="stylesheet" href="style.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/4.7.0/css/font-awesome.css">
    <title>Login System</title>
</head>

<body id="form-box">
    <?php if (!empty($error_message)) { ?>
        <div id="errorMessage">
            <strong>Error:</strong> </p>
            <?= $error_message ?>
            </p>
        </div>
    <?php } ?>
    <section>
        <div class="form-box">
            <div class="form-value">
                <form action="login.php" method="POST">
                    <h2>Login Form</h2>
                    <div class="inputbox">
                        <ion-icon name="mail-outline"></ion-icon>
                        <input type="text"  name="email" required>
                        <label for="">Email</label>
                    </div>
                    <div class="inputbox">
                        <ion-icon name="lock-closed-outline" id="lc"></ion-icon>
                        <input type="password"  name="password" id="password" required>
                        <label for="">Password</label>
                    </div>


                    <div class="forget">
                    <label for=""><a href="registration.php">Create an account</a></label>
                    

                    </div>
                    <button>Log in</button>
                   
                    
                    
                </form>
            </div>
        </div>
    </section>

    <script>
        let lc = document.getElementById("lc");
        let password = document.getElementById("password");

        lc.onclick = function(){
            if(password.type == "password"){
                password.type = "text";
                

            }else{
                password.type = "password";
                
            }
        }
    </script>
    <script type="module" src="https://unpkg.com/ionicons@5.5.2/dist/ionicons/ionicons.esm.js"></script>
    <script nomodule src="https://unpkg.com/ionicons@5.5.2/dist/ionicons/ionicons.js"></script>
</body>

</html>