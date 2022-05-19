<?php

function getContent($filename){
    $s = file_get_contents($filename);
    return json_decode($s,true);
}

$users = getContent('users.json');
$usernameError = '';
$passwordError = '';
$success = false;

if(count($_POST) > 0){
    if(isset($_POST['username']) && isset($_POST['password'])){
        $usernameError = validateUsername($_POST['username']);
        $passwordError = validatePassword($_POST['password']);
        if($usernameError === '' && $passwordError === '' && usernameAndPasswordMatching($_POST['username'],$_POST['password'])){
            session_start();
            $_SESSION['username'] = $_POST['username'];
            $_SESSION['id'] = getUserId($_POST['username']);
            $success = true;
        }
    }
}


function getUserId($username){
    global $users;
    foreach($users as $id => $user){
        if($user['username'] === $username){
            return $user['id'];
        }
    }
    return -1;
}

function usernameAndPasswordMatching($username, $password){
    global $users;
    foreach($users as $id => $user){
        if($user['username'] === $username && $user['password'] === $password){
            return true;
        }
    }
    return false;
}

function validateUsername($username){
    if($username === ''){
        return 'A felhasználónév mező kitöltése kötelező!';
    }else return '';
}

function validatePassword($password){
    if($password === ''){
        return 'A jelszó mező kitöltése kötelező!';
    }else return '';
}

?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta http-equiv="X-UA-Compatible" content="IE=edge">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Bejelentkezés</title>
</head>
<body>
    <h1>Bejelentkezés</h1>
    <form method="post" novalidate>
        Felhasználóév: <input type="text" name="username" value="<?php if(isset($_POST['username']) && !$success) { echo $_POST['username']; } ?>"> <?php echo $usernameError; ?> <br>
        Jelszó: <input type="password" name="password" value="<?php if(isset($_POST['password']) && !$success) { echo $_POST['password']; } ?>"> <?php echo $passwordError; ?> <br>
        <input type="submit" value="Bejelentkezés">
    </form>
    <hr>
    <?php if($success) : ?>
        <p style="color: green">Sikeres bejelentkezés!</p>
        <?php header('Location: index.php'); ?>
    <?php elseif(count($_POST) > 0) : ?>
        <p style="color: red">Sikertelen bejelentkezés! A felhasználónév és a jelszó nem egyezik!</p>
    <?php endif ?>
    <a href="index.php">Vissza a főoldalra</a> <br>
    Még nincs fiókod? <a href="registration.php">Regisztráció</a>
</body>
</html>