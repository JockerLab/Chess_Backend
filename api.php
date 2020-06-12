<?php
require_once 'Game.php';
require_once 'Database/Database.php';
require_once 'Exceptions/DatabaseException.php';
require_once 'Exceptions/IncorrectMoveException.php';

session_start();

$status = 'OK';
$message = 'You should use GET request with one of these parameters: move, state or reset.';
$currentGame = null;
if (!isset($_SESSION['database'])) {
    $_SESSION['database'] = new Database();
}

try {
    $_SESSION['database']->connect();
} catch (DatabaseException $e) {
    $status = 'ERROR';
    $message = $e->getMessage();
}
//header("Content-Type: application/json");
if (!isset($_GET['move']) and !isset($_GET['state']) and !isset($_GET['reset'])) {
    $status = 'ERROR';
}

if (isset($_SESSION['game'])) {
    $currentGame = new Game($_SESSION['database'], $_SESSION['game']);
} else {
    $currentGame = new Game($_SESSION['database'], null);
    $_SESSION['game'] = $currentGame->getGameNumber();
}

$positions = null;
if ($status != 'ERROR' and isset($_GET['move'])) {
    if (isset($_GET['state']) or isset($_GET['reset'])) {
        $status = 'ERROR';
    } else {
        $coordinates = explode(':', $_GET['move']);
        if (count($coordinates) != 2) {
            $status = 'ERROR';
            $message = 'Use move=\'from\':\'to\'';
        } else {
            try {
                if (!isset($_SESSION['end']) or $_SESSION['end'] == false) {
                    $currentGame->move($coordinates[0], $coordinates[1]);
                    $message = 'Piece was successfully moved.';
                    if ($currentGame->checkEndGame()) {
                        $_SESSION['end'] = true;
                        $message = 'The game is over.';
                    }
                } else {
                    $message = 'The game is already over.';
                }
            } catch (IncorrectMoveException | DatabaseException $e) {
                $message = $e->getMessage();
                $status = 'ERROR';
            }
        }
    }
}
if ($status != 'ERROR' and isset($_GET['state'])) {
    if (isset($_GET['move']) or isset($_GET['reset'])) {
        $status = 'ERROR';
    } else {
        $message = 'State already received.';
    }
}
if ($status != 'ERROR' and isset($_GET['reset'])) {
    if (isset($_GET['state']) or isset($_GET['move'])) {
        $status = 'ERROR';
    } else {
        $currentGame->reset();
        $_SESSION['end'] = false;
        $message = 'New game was created.';
    }
}

$response = [
    'status' => $status,
    'message' => $message
];

if ($status != 'ERROR' and isset($_GET['state'])) {
    $response['playerNumber'] = $currentGame->getPlayerNumber();
    $response['positions'] = $currentGame->getPositions();
}

echo json_encode($response);

$_SESSION['database']->disconnect();