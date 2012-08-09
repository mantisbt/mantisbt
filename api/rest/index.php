<?php
/**
 * First prototype look of mantisbt 1.2 REST/JSON API
 *
 * Solution used Slim PHP microframework.
 *
 * @author Marcin Bielak <marcin.bieli@gmail.com>
 */

set_include_path( '../../library' );
set_include_path( '../soap' );

require_once('mc_core.php');
require_once('mc_api.php');
require_once('mc_account_api.php');
require_once('mc_issue_api.php');

require dirname(__FILE__) . '/Slim/Slim/Slim.php';


$app = new Slim();

$app->get('/mc_version', 'rest_mc_version');
$app->post('/mc_issue_add', 'rest_mc_issue_add');


function rest_mc_version() {
    $result = array(
        'data' => mc_version()
    );

    header("Content-Type: application/json");

    echo json_encode($result);
}

function rest_mc_issue_add() {
    $username = trim($_POST['username']);
    $password = trim($_POST['password']);
    $issue    = getIssueToAdd(trim($_POST['summary']), trim($_POST['description']), (int) trim($_POST['project']), trim($_POST['category']));

    $result = array(
        'data' => mc_issue_add($username, $password, $issue)
    );

    header("Content-Type: application/json");

    echo json_encode($result);
}

function getIssueToAdd($summary, $description, $projectId, $category) {
    return array(
        'summary' => $summary,
        'description' => $description,
        'project' => array( 'id' => $projectId ),
        'category' => $category
    );
}

$app->run();
