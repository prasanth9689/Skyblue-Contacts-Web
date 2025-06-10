<?php
$con = mysqli_connect("localhost", "root", "prasanth", "skyblue_contacts");

ini_set('display_errors', 1);
error_reporting(E_ALL);

$json = file_get_contents("php://input");
$data = json_decode($json, true);

if (json_last_error() === JSON_ERROR_NONE) {
    $access = htmlspecialchars($data["acc"]);
}

function signUp($googleId, $email, $displayName, $dateTime) {
    global $con;
    $stmt = $con->prepare("INSERT INTO users (googleId, email, displayName, joinedDate) VALUES (?,?,?,?)");
    $stmt->bind_param("ssss", $googleId, $email, $displayName, $dateTime);
    if ($stmt->execute()) {
      //  echo "User signed up successfully.";
      getUserDetails($googleId);
    } else {
        echo "Signup failed: " . $stmt->error;
    }
}

function getUserDetails($googleId) {
    global $con;
    global $data;
    $Sql = "SELECT id  FROM users WHERE googleId = '$googleId' ";
    $result = mysqli_query($con, $Sql);
    $data2 = array();

    while($row = mysqli_fetch_assoc($result))
      {
        array_push($data2, array("userId"=>$row["id"]));
      }
         $formatted = [
             "status" => $data["status"] ?? "true",
             "message" => $data["message"] ?? "Login success",
             "request" => $data,
             "response" => $data2
         ];

         echo json_encode($formatted, JSON_PRETTY_PRINT);
}

switch ($access) {
    case "save_contacts";

    /* 
     Qustion: To avoid saving duplicate firstName values, you can modify your code to check for existing names 
     before inserting a new contact.

     Ans 1: Already implemented

     Ans 2: 
           this approach can be inefficient if you're dealing with a large number of contacts.
           A better solution would be to create a unique index on the firstName and userId columns 
           in your database table, and then use INSERT IGNORE to avoid duplicate entries:

           Code: 

             foreach ($data['contacts'] as $contact) {
             $firstName = htmlspecialchars($contact['firstName']);
             $phone = htmlspecialchars($contact['phoneNumber']);
             $userId = htmlspecialchars($data['userId']);

             $stmt = $con->prepare("INSERT IGNORE INTO contacts (firstName, phone, userId) VALUES (?, ?, ?)");
             $stmt->bind_param("sss", $firstName, $phone, $userId);
             $stmt->execute();
             }

             Con:
                This way, the database will handle duplicate checks efficiently, and you'll avoid unnecessary queries.

    */

    foreach ($data['contacts'] as $contact) {
        $firstName = htmlspecialchars($contact['firstName']);
        $phone = htmlspecialchars($contact['phoneNumber']);
        $userId = htmlspecialchars($data['userId']);


        // Check if firstName already exists for the user
        $stmt = $con->prepare("SELECT * FROM contacts WHERE firstName = ? AND userId = ?");
        $stmt->bind_param("ss", $firstName, $userId);
        $stmt->execute();
        $result = $stmt->get_result();


        if ($result->num_rows == 0) {
        // Insert new contact if firstName doesn't exist
        $insertStmt = $con->prepare("INSERT INTO contacts (firstName, phone, userId) VALUES (?, ?, ?)");
        $insertStmt->bind_param("sss", $firstName, $phone, $userId);
        $insertStmt->execute();
        }
    }
    $formatted = [
        "status" => "true",
        "message" => "Contacts saved success",
        "request" => $data
    ];

    echo json_encode($formatted, JSON_PRETTY_PRINT);
    break;

    case "get_contacts":
        $userId = htmlspecialchars($data["userId"]);

        $Sql = "SELECT firstName, phone FROM contacts WHERE userId = '$userId'";
        $result=mysqli_query($con,$Sql);
        $contacts = array();
        while ($row = mysqli_fetch_assoc($result)) {
            array_push($contacts, array(
                "firstName" => $row["firstName"],
                "phoneNumber" => $row["phone"]
            ));
        }

           if (empty($contacts)) {
        $formatted = [
            "status" => "false",
            "message" => "No contacts found.",
            "request" => $data,
            "response" => []
        ];
    } else {
        $formatted = [
            "status" => "true",
            "message" => "Contacts found!",
            "request" => $data,
            "response" => $contacts
        ];
    }
   
            echo json_encode($formatted, JSON_PRETTY_PRINT);
        break;

    case "login":
        $googleId = htmlspecialchars($data["googleId"]);
        $displayName = htmlspecialchars($data["displayName"]);
        $dateTime = htmlspecialchars($data["dateTime"]);
        $email = htmlspecialchars($data["email"]);

        $sql = "SELECT googleId FROM users WHERE googleId = ?";
        $stmt = $con->prepare($sql);
        $stmt->bind_param("s", $googleId); // "s" means the database expects a string
        $stmt->execute();
        $result = $stmt->get_result();
           // Check if any rows were returned
           if ($result->num_rows > 0) {
            getUserDetails($googleId);
            return;
        }
        signUp($googleId, $email, $displayName, $dateTime);
        // $formatted = [
        //     "status" => $data["status"] ?? "true",
        //     "message" => $data["message"] ?? "Correct access key",
        //     "request" => $data,
        //     "response" => "hii"
        // ];

        // echo json_encode($formatted, JSON_PRETTY_PRINT);
        break;

    default:

    header("Content-Type:Application/json");

            $formatted = [
                "status" => $data["status"] ?? "false",
                "message" => $data["message"] ?? "Wrong access key",
                "data" => $data
            ];

            echo json_encode($formatted, JSON_PRETTY_PRINT);

        // array_push($data, [
        //     "status" => "0",
        //     "message" => "Wrong access key"
        // ]);
        // header("Content-Type:Application/json");
        // print json_encode($data);
        break;
}



?>