<?php

function getContent($filename){
    $s = file_get_contents($filename);
    return json_decode($s,true);
}

$users = getContent('users.json');
$emailError = '';
$usernameError = '';
$passwordError = '';
$successfull = false;


if(count($_POST) > 0){
    if(isset($_POST['email']) && isset($_POST['username']) && isset($_POST['password']) && isset($_POST['passwordAgain'])){
        $emailError = validateEmail($_POST['email']);
        $usernameError = validateUsername($_POST['username']);
        $passwordError = validatePassword($_POST['password'], $_POST['passwordAgain']);
        if($emailError === '' && $usernameError === '' && $passwordError === ''){
            $id = count($users) + 1;
            $sid = strval($id);
            $newUser = array();
            $newUser['id'] = $id;
            $newUser['username'] = $_POST['username'];
            $newUser['email'] = $_POST['email'];
            $newUser['password'] = $_POST['password'];

            $users[$sid] = $newUser;

            file_put_contents('users.json',json_encode($users, JSON_PRETTY_PRINT));
            $successfull = true;
        }
    }
}

function validateEmail($email){
    if($email === ''){
        return 'Az email mező kitöltése kötelező!';
    }else if(!filter_var($email, FILTER_VALIDATE_EMAIL)){
        return 'Az email nem megfelelő formátumú!';
    }else if(isTakenEmail($email)){
        return 'Az email cím már foglalt!';
    }else return '';
}

function isTakenEmail($email){
    global $users;
    foreach($users as $id => $user){
        if($user['email'] === $email){
            return true;
        }
    }
    return false;
}

function validateUsername($username){
    if($username === ''){
        return 'A felhasználónév mező kitöltése kötelező!';
    }else if(isTakenUsername($username)){
        return 'A felhasználónév már foglalt!';
    }else return '';
}

function isTakenUsername($username){
    global $users;
    foreach($users as $id => $user){
        if($user['username'] === $username){
            return true;
        }
    }
    return false;
}

function validatePassword($password, $passwordAgain){
    if($password === '' || $passwordAgain === ''){
        return 'A jelszó mező kitöltése kötelező!';
    }else if($password !== $passwordAgain){
        return 'A két jelszó nem egyezik meg!';
    }else return '';
}

?>


<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta http-equiv="X-UA-Compatible" content="IE=edge">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Regisztráció</title>
</head>
<body>
    <h1>Regisztráció</h1>
    <form method="post" novalidate>
        E-mail cím: <input type="text" name="email" value="<?php if(isset($_POST['email']) && !$successfull) { echo $_POST['email']; } ?>"> <?php echo $emailError; ?> <br>
        Felhasználónév: <input type="text" name="username" value="<?php if(isset($_POST['username']) && !$successfull) { echo $_POST['username']; } ?>"> <?php echo $usernameError; ?> <br>
        Jelszó: <input type="password" name="password" value="<?php if(isset($_POST['password']) && !$successfull) { echo $_POST['password']; } ?>"> <?php echo $passwordError; ?> <br>
        Jelszó mégegyszer: <input type="password" name="passwordAgain" value="<?php if(isset($_POST['passwordAgain']) && !$successfull) { echo $_POST['passwordAgain']; } ?>"> <br>
        <input type="submit" value="Regisztráció"> <br>
    </form>
    <?php if(count($_POST) > 0 && $emailError === '' && $usernameError === '' && $passwordError === '') : ?>
        <p style="color: green;">Sikeres regisztráció!</p>
        <?php header('Location: index.php'); ?>
    <?php elseif(count($_POST) > 0) : ?>
        <p style="color: red">Sikertelen regisztráció!</p>
    <?php endif ?>
    <hr>
    <a href="index.php">Vissza a főoldalra</a> <br> 
    Már van fiókod? <a href="login.php">Bejelentkezés</a>
</body>
</html>