<?php
// Redirect to Flocash payment page
$redirect_url = "https://sandbox.flocash.com/ecom/ecommerce.do";
$order_id = $_POST['order_id'] ?? ''; // Assuming order_id is sent in the POST request
$merchant_email = $_POST['merchant_email'] ?? ''; // Assuming merchant_email is sent in the POST request
$currency_code = $_POST['currency_code'] ?? ''; // Assuming currency_code is sent in the POST request
$item_name = $_POST['item_name'] ?? ''; // Assuming item_name is sent in the POST request
$item_price = $_POST['item_price'] ?? ''; // Assuming item_price is sent in the POST request
$quantity = $_POST['quantity'] ?? ''; // Assuming quantity is sent in the POST request
$amount = $_POST['amount'] ?? ''; // Assuming amount is sent in the POST request
 
$redirect_url .= '?merchant=' . urlencode($merchant_email);
$redirect_url .= '&order_id=' . urlencode($order_id);
$redirect_url .= '&custom=custom'; // Example value for custom data
$redirect_url .= '&currency_code=' . urlencode($currency_code);
$redirect_url .= '&item_name=' . urlencode($item_name);
$redirect_url .= '&item_price=' . urlencode($item_price);
$redirect_url .= '&quantity=' . urlencode($quantity);
$redirect_url .= '&amount=' . urlencode($amount);

// Perform the redirect
header('Location: ' . $redirect_url);
exit;
?>
