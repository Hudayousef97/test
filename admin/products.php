<?php

include '../components/connect.php';

session_start();

$admin_id = $_SESSION['admin_id'];

if(!isset($admin_id)){
   header('location:admin_login.php');
};

if(isset($_POST['add_product'])){

   $name = $_POST['name'];
   $name = filter_var($name, FILTER_SANITIZE_SPECIAL_CHARS);
   $price = $_POST['price'];
   $price = filter_var($price, FILTER_SANITIZE_SPECIAL_CHARS);
   $details = $_POST['details'];
   $details = filter_var($details, FILTER_SANITIZE_SPECIAL_CHARS);

   $image_01 = $_FILES['image_01']['name'];
   $image_01 = filter_var($image_01, FILTER_SANITIZE_SPECIAL_CHARS);
   $image_size_01 = $_FILES['image_01']['size'];
   $image_tmp_name_01 = $_FILES['image_01']['tmp_name'];
   $image_folder_01 = '../uploaded_img/'.$image_01;

   $image_02 = $_FILES['image_02']['name'];
   $image_02 = filter_var($image_02, FILTER_SANITIZE_SPECIAL_CHARS);
   $image_size_02 = $_FILES['image_02']['size'];
   $image_tmp_name_02 = $_FILES['image_02']['tmp_name'];
   $image_folder_02 = '../uploaded_img/'.$image_02;

   $image_03 = $_FILES['image_03']['name'];
   $image_03 = filter_var($image_03, FILTER_SANITIZE_SPECIAL_CHARS);
   $image_size_03 = $_FILES['image_03']['size'];
   $image_tmp_name_03 = $_FILES['image_03']['tmp_name'];
   $image_folder_03 = '../uploaded_img/'.$image_03;

   $select_products = $conn->prepare("SELECT * FROM `products` WHERE name = ?");
   $select_products->execute([$name]);

   if($select_products->rowCount() > 0){
      $message[] = 'product name already exist!';
   }else{

      $insert_products = $conn->prepare("INSERT INTO `products`(name, details, price, image_01, image_02, image_03) VALUES(?,?,?,?,?,?)");
      $insert_products->execute([$name, $details, $price, $image_01, $image_02, $image_03]);

      if($insert_products){
         if($image_size_01 > 2000000 OR $image_size_02 > 2000000 OR $image_size_03 > 2000000){
            $message[] = 'image size is too large!';
         }else{
            move_uploaded_file($image_tmp_name_01, $image_folder_01);
            move_uploaded_file($image_tmp_name_02, $image_folder_02);
            move_uploaded_file($image_tmp_name_03, $image_folder_03);
            $message[] = 'new product added!';
         }

      }

   }  

};

if(isset($_GET['delete'])){

   $delete_id = $_GET['delete'];
   $delete_product_image = $conn->prepare("SELECT * FROM `products` WHERE id = ?");
   $delete_product_image->execute([$delete_id]);
   $fetch_delete_image = $delete_product_image->fetch(PDO::FETCH_ASSOC);
   unlink('../uploaded_img/'.$fetch_delete_image['image_01']);
   unlink('../uploaded_img/'.$fetch_delete_image['image_02']);
   unlink('../uploaded_img/'.$fetch_delete_image['image_03']);
   $delete_product = $conn->prepare("DELETE FROM `products` WHERE id = ?");
   $delete_product->execute([$delete_id]);
   $delete_cart = $conn->prepare("DELETE FROM `cart` WHERE pid = ?");
   $delete_cart->execute([$delete_id]);
   $delete_wishlist = $conn->prepare("DELETE FROM `wishlist` WHERE pid = ?");
   $delete_wishlist->execute([$delete_id]);
   header('location:products.php');
}


?>

<!DOCTYPE html>
<html lang="en">
<head>
   <meta charset="UTF-8">
   <meta http-equiv="X-UA-Compatible" content="IE=edge">
   <meta name="viewport" content="width=device-width, initial-scale=1.0">
   <title>products</title>

   <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.1.1/css/all.min.css">

   <link rel="stylesheet" href="../css/admin_style.css">

</head>
<body>

<?php include '../components/admin_header.php'; ?>

<section class="add-products">

   <h1 class="heading">add product</h1>

   <form action="" method="post" enctype="multipart/form-data">
      <div class="flex">
         <div class="inputBox">
            <span>product name (required)</span>
            <input type="text" class="box" required maxlength="100" placeholder="enter product name" name="name">
         </div>
         <div class="inputBox">
            <span>product price (required)</span>
            <input type="number" min="0" class="box" required max="9999999999" placeholder="enter product price" onkeypress="if(this.value.length == 10) return false;" name="price">
         </div>
        <div class="inputBox">
            <span>image 01 (required)</span>
            <input type="file" name="image_01" accept="image/jpg, image/jpeg, image/png, image/webp" class="box" required>
        </div>
        <div class="inputBox">
            <span>image 02 (required)</span>
            <input type="file" name="image_02" accept="image/jpg, image/jpeg, image/png, image/webp" class="box" required>
        </div>
        <div class="inputBox">
            <span>image 03 (required)</span>
            <input type="file" name="image_03" accept="image/jpg, image/jpeg, image/png, image/webp" class="box" required>
        </div>
         <div class="inputBox">
            <span>product details (required)</span>
            <textarea name="details" placeholder="enter product details" class="box" required maxlength="500" cols="30" rows="10"></textarea>
         </div>
      </div>
      
      <input type="submit" value="add product" class="btn" name="add_product">
   </form>

</section>



<section class="show-products">
   <h1 class="heading">Products Added</h1>

   <div class="table-container">
      <table style="width: 90%; border-collapse: collapse; text-align: center;">
         <thead>
            <tr>
               <th style="padding: 8px; border: 1px solid #ccc;">Image</th>
               <th style="padding: 8px; border: 1px solid #ccc;">Name</th>
               <th style="padding: 8px; border: 1px solid #ccc;">Price</th>
               <th style="padding: 8px; border: 1px solid #ccc;">Details</th>
               <th style="padding: 8px; border: 1px solid #ccc;">Actions</th>
            </tr>
         </thead>
         <tbody>
            <?php
               $select_products = $conn->prepare("SELECT * FROM `products`");
               $select_products->execute();

               if ($select_products->rowCount() > 0) {
                  while ($fetch_products = $select_products->fetch(PDO::FETCH_ASSOC)) {
                     echo "<tr>";
                     echo "<td style='border: 1px solid #ccc;'><img src='../uploaded_img/{$fetch_products['image_01']}' alt='Product Image' style='max-width: 100px; max-height: 100px;'></td>";
                     echo "<td style='border: 1px solid #ccc; padding: 8px;'>{$fetch_products['name']}</td>";
                     echo "<td style='border: 1px solid #ccc; padding: 8px;'>$<span>{$fetch_products['price']}</span>/-</td>";
                     echo "<td style='border: 1px solid #ccc; padding: 8px;'>{$fetch_products['details']}</td>";
                     echo "<td style='border: 1px solid #ccc; padding: 8px;'>
                              <a href='update_product.php?update={$fetch_products['id']}' class='option-btn'>Update</a>
                              <a href='products.php?delete={$fetch_products['id']}' class='delete-btn' onclick='return confirm(\"Delete this product?\");'>Delete</a>
                           </td>";
                     echo "</tr>";
                  }
               } else {
                  echo '<tr><td colspan="5" style="border: 1px solid #ccc; padding: 8px;">No products added yet!</td></tr>';
               }
            ?>
         </tbody>
      </table>
   </div>
</section>









<script src="../js/admin_script.js"></script>
   
</body>
</html>