<?php
require "./../.env";

header("Access-Control-Allow-Origin: *");

header("Content-Type: application/json; charset=UTF-8");

header("Access-Control-Allow-Methods: OPTIONS,GET,POST,PUT,DELETE");

header("Access-Control-Max-Age: 3600");

header("Access-Control-Allow-Headers: Content-Type, Access-Control-Allow-Headers, Authorization, X-Requested-With");

function serverConnect()
{
  $servername = "localhost:3306";
  $username = "root";
  $password = getenv("PASSWORD");

  try {
    $conn = new PDO("mysql:host=$servername;dbname=pipper", $username, $password);
    // set the PDO error mode to exception

    $conn->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
    return $conn;
  } catch (PDOException $e) {
    echo "Connection failed: " . $e->getMessage();
  }
}

function validateUsername(array $input)
{
  if ($input['username'] == "") {
    return false;
  } elseif ($input['username'] === null) {
    return false;
  } else {
    return true;
  }
}

function validatePip(array $input)
{
  if ($input['pip'] == "") {
    return false;
  } elseif ($input['pip'] === null) {
    return false;
  } else {
    return true;
  }
}

$requestType = $_SERVER["REQUEST_METHOD"];


if ($requestType == "GET") {
  $conn = serverConnect();
  $statement = $conn->query("select * from user_pips");
  $result = $statement->fetchAll();

  echo json_encode($result);
} elseif ($requestType == "POST") {
  $conn = serverConnect();
  $input = (array) json_decode(file_get_contents("php://input"), TRUE);

  $usernameIsValid = validateUsername($input);
  if ($usernameIsValid == false) {
    echo "Udfyld felt!";
  } else {
    echo $input['username'];
  }

  $pipIsValid = validatePip($input);
  if ($pipIsValid == false) {
    echo "Pip et pip!";
  } else {
    echo $input['pip'];
  }

  $statement = "INSERT INTO pipper.user_pips (username, pip) VALUES (:username, :pip)";


  try {
    $statement = $conn->prepare($statement);
    $statement->execute(array("username" => $input['username'], "pip" => $input['pip']));
  } catch (PDOException $e) {
    echo "besked kan ikke sendes: " . $e->getMessage();
  }
} elseif ($requestType == "PUT") {
  $uri = parse_url($_SERVER['REQUEST_URI'], PHP_URL_PATH);
  $uri = explode('/', $uri);
  $iduser_pips = (string) $uri[1];
  $input = (array) json_decode(file_get_contents("php://input"), TRUE);

  $update =
    " UPDATE pipper.user_pips
    SET 
        username = :username,
        pip = :pip
    WHERE iduser_pips = :iduser_pips;";

  try {
    $update = serverConnect()->prepare($update);
    $update->execute(array(
      'iduser_pips' => (int) $iduser_pips,
      'username' => $input['username'],
      'pip' => $input['pip'],
    ));
  } catch (PDOException $e) {
    echo "besked kan ikke sendes: " . $e->getMessage();
  }
} elseif ($requestType == "DELETE") {
  $uri = parse_url($_SERVER['REQUEST_URI'], PHP_URL_PATH);
  $uri = explode('/', $uri);
  $iduser_pips = (string) $uri[1];

  $delete =
    " DELETE FROM pipper.user_pips
      WHERE iduser_pips = :iduser_pips;";

  try {
    $delete = serverConnect()->prepare($delete);
    $delete->execute(array(
      'iduser_pips' => (int) $iduser_pips,
    ));
  } catch (PDOException $e) {
    echo "besked kan ikke sendes: " . $e->getMessage();
  }
}
