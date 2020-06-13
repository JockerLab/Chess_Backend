<?php

require_once 'SessionController.php';

class ApiController
{
    /**
     * Check arguments
     *
     * @return bool true, if count of arguments equals 1
     */
    public static function checkArguments() {
        $requests = ['move' => 1, 'state' => 2, 'reset' => 3];
        $arguments = 0;

        if (isset($_GET)) {
            foreach ($_GET as $key => $value) {
                if (isset($requests[$key])) {
                    $arguments++;
                }
            }
        }

        return $arguments == 1;
    }

    /**
     * Send response and print
     *
     * @param $status mixed OK if all done successfully, ERROR - else
     * @param $message string message or error
     * @param int $playerNumber number of player
     * @param string $positions state of current positions
     */
    public static function sendResponse($status, $message, $playerNumber = 0, $positions = '') {
        if ($positions == '' and $playerNumber == 0) {
            $response = [
                'status' => $status,
                'message' => $message
            ];
        } else{
            $response = [
                'status' => $status,
                'message' => $message,
                'playerNumber' => $playerNumber,
                'positions' => $positions
            ];
        }
        echo json_encode($response);
        SessionController::disconnectDatabase();
        die();
    }
}