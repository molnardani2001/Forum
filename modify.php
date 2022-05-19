<?php

session_start();

function getContent($filename){
    $s = file_get_contents($filename);
    return json_decode($s,true);
}

$matches = getContent('matches.json');
$teams = getContent('teams.json');


if(isset($_GET['matchid']) && count($_SESSION) >= 2 && $_SESSION['username'] === 'admin' && intval($_GET['matchid']) > 0 && intval($_GET['matchid']) <= count($matches)){
    $success = false;
    $match = getMatch();

    if(isset($_POST['home']) && isset($_POST['away']) && isset($_POST['date'])){
        $homeScoreError = validateScore($_POST['home']);
        $awayScoreError = validateScore($_POST['away']);
        $dateError = validateDate($_POST['date']);

        if(isset($_POST['delete']) && $_POST['delete'] === 'on'){
            $match['home']['score'] = "-";
            $match['away']['score'] = "-";
            if($_POST['date'] !== ''){
                $match['date'] = $_POST['date'];
            }
            $matches[$_GET['matchid']] = $match;
            $success = true;

            file_put_contents('matches.json', json_encode($matches, JSON_PRETTY_PRINT));
        }else if($homeScoreError === '' && $awayScoreError === '' && $dateError === ''){
            $match['home']['score'] = intval($_POST['home']);
            $match['away']['score'] = intval($_POST['away']);
            $match['date'] = $_POST['date'];

            $matches[$_GET['matchid']] = $match;
            $success = true;

            file_put_contents('matches.json', json_encode($matches, JSON_PRETTY_PRINT));
        }
    }
}

function getMatch(){
    global $matches;
    foreach($matches as $id => $match){
        if(strval($id) === $_GET['matchid']){
            return $matches[$id];
        }
    }
    return NULL;
}

function getTeamName($id){
    global $teams;
    return $teams[$id]['name'];
}

function validateScore($score){
    if($score === ''){
        return 'A mező nem maradhat üresen! Ha nem kívánod módosítani akkor az eredeti eredményt írd be újra!';
    }else return '';
}

function validateDate($date){
    if($date === ''){
        return 'A mező nem maradhat üresen! Ha nem kívánod módosítani akkor az eredeti dátumot írd be újra!';
    }else return '';
}

?>


<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta http-equiv="X-UA-Compatible" content="IE=edge">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Módosítás</title>
</head>
<body>
    <?php if(isset($_GET['matchid']) && count($_SESSION) >= 2 && $_SESSION['username'] === 'admin' && intval($_GET['matchid']) > 0 && intval($_GET['matchid']) <= count($matches)) : ?>
        <h2>Meccs:</h2>

        Hazai csapat: <?= getTeamName($match['home']['id']) ?> <br>
        Vendég csapat: <?= getTeamName($match['away']['id']) ?> <br>
        Hazai gólok száma: <?= $match['home']['score'] ?> <br>
        Vendég gólok száma: <?= $match['away']['score'] ?> <br>
        Dátum: <?= $match['date'] ?> <br>

        <h2>Módosítás</h2>
        <form method="post" novalidate>
            Hazai csapat góljainak módosítása: <input type="number" name="home" min="0" value="<?php if(isset($_POST['home'])) { echo $_POST['home']; } ?>">  <?= $homeScoreError ?> <br>
            Vendég csapat góljainak módosítása: <input type="number" name="away" min="0" value="<?php if(isset($_POST['away'])) { echo $_POST['away']; } ?>"> <?= $awayScoreError ?> <br>
            Dátum módosítása (év-hónap-nap): <input type="text" name="date" value="<?php if(isset($_POST['date'])) { echo $_POST['date']; } ?>"> <?= $dateError ?> <br>
            Eredmény törlése: <input type="checkbox" name="delete" <?php if(isset($_POST['delete']) && $_POST['delete'] === 'on') { echo 'checked'; } ?>> <br>
            <input type="submit" value="Módosítás">
        </form>

        <?php if(count($_POST) > 0 && $success) : ?>
            <p style="color:green;">Sikeres módosítás!</p>
            <?php
                header('Location: teamdetails.php?id=' . $_GET['teamid']);
            ?>
        <?php elseif(count($_POST) > 0) : ?>
            <p style="color:red;">Sikertelen módosítás!</p>
        <?php endif ?>

    <?php else : ?>
        <p>Hozzáférés megtagadva.</p>
    <?php endif ?>
    
</body>
</html>