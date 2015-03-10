## Sample Code

$transaction = array(

    "customer_email" => "juandelacruz@mail.com",
    "customer_name" => "Juan Dela Cruz",
    "customer_phone" => "09197291823",
    "customer_address" => "Cebu City",
    "amount" => 100000, // 1,000.00
    "expiry" => '2015-03-26 23:59:59',
    "payload" => "Payload",
    "product_description" => "Description"
);

$mlepay = new MLePay();

$mlepay->create_transaction($transaction);
