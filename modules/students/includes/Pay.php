<?php
 
 
$MD = $_POST['MD'] ?? '';
$PaReq = $_POST['PaReq'] ?? '';
$TermUrl = $_POST['TermUrl'] ?? '';
$postURL = $_POST['URL'] ?? '';
$studentId = intval($_POST['StudentId'])?? '';
 
if (!empty($MD) && !empty($PaReq) && !empty($TermUrl) && !empty($postURL)) {
 
    $postData = array(
        'MD' => $MD,
        'PaReq' => $PaReq,
        'TermUrl' => $TermUrl
    );
 
    $ch = curl_init($postURL);
 
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
    curl_setopt($ch, CURLOPT_CUSTOMREQUEST, "PUT");  
    curl_setopt($ch, CURLOPT_POSTFIELDS, http_build_query($postData));
 
    $response = curl_exec($ch);
 
    curl_close($ch);

    $responseData = array(
        'success' => true,
        'message' => 'Data posted to ' . $postURL . ' successfully',
        'response' => $response
    ); 
    header('Content-Type: application/json');
    $connection= new mysqli("localhost","root","root","opensis");
     
    if ($connection -> connect_errno) {
        echo "Failed to connect to MySQL: " . $mysqli -> connect_error;
        exit();
      }
    $sql = "UPDATE student_fees SET status = 'Paid' WHERE student_id = 16";
    
    $result = $connection->query($sql);
    
    if ($result === false) { 
        echo json_encode("Database query failed: " . mysqli_error($connection));
    } else { 
        echo json_encode( $responseData);
    }
    $connection -> close();
} else {
 
    $responseData = array(
        'success' => false,
        'message' => 'Error: Missing parameters',
    );
 
    header('Content-Type: application/json');
    echo json_encode($responseData);
}
