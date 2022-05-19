<?php 

session_start();

function getContent($filename){
    $s = file_get_contents($filename);
    return json_decode($s,true);
}

$teams = getContent('teams.json');
$matches = getContent('matches.json');

function lastFiveMatches(){
    global $matches;
    $i = count($matches) + 1;
    $lastFive = array();
    while($i > 0 && count($lastFive) < 5){
        if(is_numeric($matches[$i]['home']['score'])){
            $lastFive[] = $matches[$i];
        }
        $i--;
    }
    return $lastFive;
}

$lastFive = lastFiveMatches();

function getTeamName($id){
    global $teams;
    return $teams[$id]['name'];
}


?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta http-equiv="X-UA-Compatible" content="IE=edge">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link rel="stylesheet" href="css/index.css">
    <title>Főoldal</title>
</head>
<body>
    <h1>Eötvös Loránd Stadion</h1>

    <div id="registration">
        <?php if(isset($_SESSION) && count($_SESSION) > 0 && isset($_SESSION['username'])) : ?>
            <a href="logout.php">Kijelentkezés (<?= $_SESSION['username'] ?>)</a>
        <?php else : ?>
            <a href="registration.php">Regisztráció</a>
            <a href="login.php">Bejelentkezés</a>
        <?php endif ?>
    </div>

    <div id="description">
        <h2>
            Leaírás
        </h2>

        <p>
            Az Eötvös Loránd Stadion adatbázisában szereplő információk találhatók ezen az oldalon! A csapatinformációk megtekintésére mindenki jogosult,
            ám hozzászólásokat csak már létező vagy újonnan létrehozott fiókba bejelentkezve lehet írni. A jobb oldalon a legutolsó 5 lejátszott meccs szerepel.
        </p>
    </div>


    <div id="teams">
    <hr>
        <h3>Csapatok</h3>
        <ul>
            <?php foreach($teams as $id => $team) : ?>
                <li class="team">
                    <?= $team['name'] ?> | 
                    <a href="teamdetails.php?id=<?= $id ?>">Részletek</a>
                </li>
            <?php endforeach ?>
        </ul>
    </div>
    
    

    <div id="last_five_matches">
    <hr>
        <h3>
            Az utolsó 5 lejátszott meccs <br>
            (ha van)
        </h3>
        
        <ul>
            <?php foreach($lastFive as $match) : ?>
                <li class="match">
                    <?= getTeamName($match['home']['id']) ?> - <?= getTeamName($match['away']['id']) ?> | <?= $match['home']['score'] ?> - <?= $match['away']['score'] ?> | (<?= $match['date'] ?>)
                </li>
            <?php endforeach ?>
        </ul>

    </div>

</body>
</html>