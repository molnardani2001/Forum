<?php

session_start();

function getContent($filename){
    $s = file_get_contents($filename);
    return json_decode($s,true);
}

$teams = getContent('teams.json');
$matches = getContent('matches.json');
$comments = getContent('comments.json');
$users = getContent('users.json');

function getTeam($id){
    global $teams;
    return $teams[$id];
}

$commentError = validateComment();

function validateComment(){
    if(isset($_POST['text'])){
        if($_POST['text'] === ''){
            return 'A hozzászólás mező nem maradhat üresen!';
        }else return '';
    }
    return '';
}

if(count($_GET) > 0 && isset($_GET['id'])){
    $team = getTeam($_GET['id']);
}


if(isset($team['name'])){
    $teamname = $team['name'];
}else{
    $teamname = "Error 404";
}

function getTeamName($id){
    global $teams;
    return $teams[$id]['name'];
}

function getMatches(&$played, &$unplayed){
    global $matches;
    global $team;
    foreach($matches as $matchid => $match){
        if($match['home']['id'] === $team['id'] || $match['away']['id'] === $team['id']){
            if(is_numeric($match['home']['score'])){
                $played[] = $match;
            }else{
                $unplayed[] = $match;
            }
        }
    }
}

$played = array();
$unplayed = array();

getMatches($played,$unplayed);

function getColor($match){
    global $team;
    if($match['home']['score'] === $match['away']['score']){
        return "orange";
    }else if(($match['home']['id'] === $team['id'] && $match['home']['score'] > $match['away']['score']) || 
             ($match['away']['id'] === $team['id'] && $match['home']['score'] < $match['away']['score'])){
                return "green";
    }else return "red";
}

if(isset($_POST['text']) && $commentError === '' && !containsComment($_POST['text'])){
    $sid = strval(count($comments) + 1);
    $newComment = array();
    $newComment['author'] = $_SESSION['id'];
    $newComment['text'] = $_POST['text'];
    $newComment['teamid'] = $team['id'];
    $newComment['date'] = date("Y-m-d");

    $comments[$sid] = $newComment;

    file_put_contents('comments.json', json_encode($comments, JSON_PRETTY_PRINT));
}

$filteredComments = array();

foreach($comments as $commentid => $comment){
    if($comment['teamid'] === $team['id']){
        $comment['id'] = intval($commentid);
        $filteredComments[] = $comment;
    }
}


function getUserName($comment){
    global $users;
    foreach($users as $userid => $user){
        if($user['id'] === $comment['author']){
            return $user['username'];
        }
    }
    return "username";
}



function containsComment($text){
    global $comments;
    foreach($comments as $id => $comment){
        if($text === $comment['text'] && $comment['author'] === $_SESSION['id']){
            return true;
        }
    }
    return false;
}

?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta http-equiv="X-UA-Compatible" content="IE=edge">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link rel="stylesheet" href="css/teamdetails.css" >
    <title><?= $teamname ?></title>
</head>
<body>
<a id="return" href="index.php">Vissza a főoldalra</a>
<h1><?= $teamname ?></h1>

<?php if(count($_GET) > 0 && isset($_GET['id']) && intval($_GET['id']) < 11 && intval($_GET['id']) > 0) : ?>
    <div id="user">
        <?php if(isset($_SESSION['username'])) : ?>
            <a href="logout.php">Kijelentkezés (<?= $_SESSION['username'] ?>)</a>
        <?php else : ?>
            <p>Bejelentkezéshez lépj vissza a főoldalra!</p>
        <?php endif ?>
    </div>

    <div id="playedGames">
        <hr>
        <h3>Lejátszott meccsek</h3>
        <ul>
            <?php foreach($played as $match) : ?>
                <li class="match" style="color: <?= getColor($match) ?>;">
                    <span> <?= getTeamName($match['home']['id']) ?> </span>
                     - 
                    <span> <?= getTeamName($match['away']['id']) ?> </span>
                     | 
                    <span> <?= $match['home']['score'] ?> </span>
                     - 
                    <span> <?= $match['away']['score'] ?> </span>
                     | 
                    <span> (<?= $match['date'] ?>) </span>
                    <?php if(isset($_SESSION['username']) && $_SESSION['username'] === 'admin') : ?>
                     | <a style="color:blue;" href="modify.php?matchid=<?= $match['id'] ?>&teamid=<?= $_GET['id'] ?>"> Szerkesztés </a>
                    <?php endif ?>
                </li>
            <?php endforeach ?>
        </ul>
    </div>

    <div id="unplayedGames">
        <hr>
        <h3>Beütemezett meccsek</h3>
        <?php if(count($unplayed) > 0) : ?>
            <ul>
                <?php foreach($unplayed as $match) : ?>
                    <li class="match">
                        <?= getTeamName($match['home']['id']) ?> - <?= getTeamName($match['away']['id']) ?> | (<?= $match['date'] ?>)
                        <?php if(isset($_SESSION['username']) && $_SESSION['username'] === 'admin') : ?>
                         | <a style="color:blue;" href="modify.php?matchid=<?= $match['id'] ?>&teamid=<?= $_GET['id'] ?>"> Szerkesztés </a>
                        <?php endif ?>
                    </li>
                <?php endforeach; ?>
            </ul>
        <?php else : ?>
            <p>Nincs beütemezett meccs</p>
        <?php endif ?>
    </div>

    <div id="comments">
        <hr>
        <h3>Hozzászólások</h3>
        <?php foreach($filteredComments as $comment) : ?>
            <div id="comment">
                <img src="img/default.jpg" alt="default profile picture image">
                <p id="username"> <?= getUserName($comment) ?> | <?= $comment['date'] ?> </p>
                <p id="text"> <?= $comment['text'] ?> </p>
                <?php if(isset($_SESSION['username']) && $_SESSION['username'] === 'admin') : ?>
                    <a href="delete.php?id=<?= $comment['id']?>&teamid=<?= $team['id'] ?>">Törlés</a>
                <?php endif ?>
            </div>
        <?php endforeach ?>
        
        <form method="post" novalidate>
            <textarea name="text" cols="80" rows="5"></textarea> <br> <?= $commentError ?> <br>
            <input type="submit" value="Hozzászólás" title="<?php if(!isset($_SESSION['username'])) { echo 'Hozzászólás írásához bejelentkezésre van szükség!'; } ?>" <?php if(!isset($_SESSION['username'])) { echo 'disabled'; } ?> > 
        </form>

    </div>
<?php endif ?>

</body>
</html>