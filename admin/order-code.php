<?php
include("../config/functions.php");
if (!isset($_SESSION['productItem'])) {
    $_SESSION['productItem'] = [];
}
if (!isset($_SESSION['productIds'])) {
    $_SESSION['productIds'] = [];
}

if (isset($_POST['addItem'])) {
    $productId = validate($_POST['product_id']);
    $productQty = validate($_POST['qty']);

    $productCheck = mysqli_query($conn, "SELECT * FROM products WHERE id='$productId' LIMIT 1 ");

    if ($productCheck) {
        if (mysqli_num_rows($productCheck) > 0) {
            $row = mysqli_fetch_assoc($productCheck);
            if ($row['qty'] < $productQty) {
                redirect('order-create.php', 'only ' . $row['qty'] . ' Quantity Available!');
            }
            $productData = [
                'product_id' => $row['id'],
                'product_name' => $row['name'],
                'product_price' => $row['price'],
                'product_qty' => $productQty,
                'product_image' => $row['image'],
            ];

            if (!in_array($row['id'], $_SESSION['productIds'])) {
                array_push($_SESSION['productIds'], $row['id']);
                array_push($_SESSION['productItem'], $productData);
            } else {
                foreach ($_SESSION['productItem'] as $key => $prodSessionItem) {
                    if ($prodSessionItem['product_id'] == $row['id']) {
                        $newQuantity = $prodSessionItem['product_qty'] + $productQty;

                        $productData = [
                            'product_id' => $row['id'],
                            'product_name' => $row['name'],
                            'product_price' => $row['price'],
                            'product_qty' => $newQuantity,
                            'product_image' => $row['image'],
                        ];
                        $_SESSION['productItem'][$key] = $productData;
                    }
                }
            }
            redirect('order-create.php', 'Items Added Successfully!');
        } else {
            redirect('order-create.php', 'No such product found');
        }
    } else {
        redirect('order-create.php', 'Something Went Wrong');
    }
}

if(isset($_POST['productIncDec'])){
    $productId =validate($_POST['product_id']);
    $quantity =validate($_POST['quantity']);
    $flag=false;
    foreach($_SESSION['productItem'] as $key => $item){
        if($item['product_id']==$productId)
        {
            $flag=true;
            $_SESSION['productItem'][$key]['product_qty']=$quantity;
        }
    }
    if($flag){
        jsonResponse(200,'success','Quantity Updated');
    }else{
        jsonResponse(500,'error','Something Went Wrong');
    }
}

//Proceed to place
if(isset($_POST['proceedToPlace'])){
    $phone=validate($_POST['cphone']);
    $payment_mode=validate($_POST['payment_mode']);

    //Checking customer exists or not
    $checkCustomer=mysqli_query($conn,"SELECT * FROM customers WHERE phone=$phone LIMIT 1");
    if($checkCustomer)
    {
        if(mysqli_num_rows($checkCustomer)>0){
            $_SESSION['invoice_no']="INV_".rand(111111,999999);
            $_SESSION['cphone']= $phone;
            $_SESSION['payment_mode']=$payment_mode;
            jsonResponse("200","success","Customer  Found");
        }
        else{
            $_SESSION['cphone']=$phone;
            jsonResponse("404","warning","Customer Not Found");
        }

    }else{
        jsonResponse("500","error","Something Went Wrong!");
    }

}
?>
