<?php

function getContent($filename){
    $s = file_get_contents($filename);
    return json_decode($s,true);
}

if(isset($_GET['id'])){
    $comments = getContent('comments.json');
    $coms = array();

    $cid = intval($_GET['id']);
    $counter = 1;

    foreach($comments as $id => $comment){
        if($id !== $cid){
            $c = array();

            $c['author'] = $comment['author'];
            $c['text'] = $comment['text'];
            $c['teamid'] = $comment['teamid'];
            $c['date'] = $comment['date'];

            $coms[$counter] = $c;
            $counter++;
        }
    }

    file_put_contents('comments.json', json_encode($coms, JSON_PRETTY_PRINT));

    header("Location: teamdetails.php?id={$_GET['teamid']}");
    exit();
}

header('Location: index.php');
exit();

?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta http-equiv="X-UA-Compatible" content="IE=edge">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Document</title>
</head>
<body>
    
</body>
</html>