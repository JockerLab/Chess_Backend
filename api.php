<?php
require_once 'Game/Game.php';
require_once 'Controllers/SessionController.php';
require_once 'Controllers/ApiController.php';
require_once 'Controllers/GameController.php';

header("Content-Type: application/json");
session_start();
SessionController::putDatabaseIfAbsent();
if ($e = SessionController::connectDatabase()) {
    ApiController::sendResponse('ERROR', $e->getMessage());
}
if (!isset($_GET)) {
    ApiController::sendResponse('ERROR', 'You should use GET request.');
}
if (!ApiController::checkArguments()) {
    ApiController::sendResponse('ERROR',
        'You should use GET request with one of these parameters: move, state or reset.'
    );
}
$currentGame = SessionController::getSessionGame();
if ($currentGame == null) {
    ApiController::sendResponse('ERROR', 'Error occurred during getting game.');
}
if (isset($_GET['move'])) {
    $coordinates = explode(':', $_GET['move']);
    if (count($coordinates) != 2) {
        ApiController::sendResponse('ERROR', 'Use move=\'from\':\'to\'.');
    }
    GameController::makeMove($currentGame, $coordinates);
}
if (isset($_GET['state'])) {
    ApiController::sendResponse('OK',
        'State successfully received.',
        $currentGame->getPlayerNumber(),
        $currentGame->getState()
    );
}
if (isset($_GET['reset'])) {
    if(!$currentGame->reset()) {
        ApiController::sendResponse('ERROR', 'Error occurred during reset game.');
    }
    SessionController::changeEndState(false);
    ApiController::sendResponse('OK', 'New game was created.');
}