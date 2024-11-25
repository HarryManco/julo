<?php
require 'db_connect.php';
session_start();

$sql = "SELECT * FROM menu_items";
$result = mysqli_query($conn, $sql);
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Welcome to Cafe</title>
    <link rel="stylesheet" href="css/cafe.css">   
</head>
<body>
<?php include 'header.php'; ?>
<div class="background">
    <section class="julo-logo">
        <img class="julo-cafe-1" loading="lazy" alt="" src="images/julo cafe.png"/>
    </section>

    <div class="cafe-list">
        <!-- My Cart Button inside the container on the left -->
        <div class="cart-btn-container">
            <form method="post" action="cart1.php">
                <button type="submit" class="cart-btn">My Cart</button>
            </form>
        </div>

        <div id="menu-container">
            <?php
                while ($row = mysqli_fetch_assoc($result)) {
                    echo "<div class='menu-item'>";
                    echo "<div class='item1'>";
                    echo "<img src='images/" . $row['image'] . "' alt='" . $row['name'] . "' style='width:160px;height:200px;'>";
                    echo "</div>";
                    echo "<div class='item2'>";
                    echo "<h4>" . $row['name'] . "</h4>";
                    echo "<p>P" . $row['price'] . "</p>";
                    echo "</div>";
                    echo "<div class='item3'>";
                    echo "<form method='post' action='add_to_cart1.php'>";
                    echo "<input type='hidden' name='menu_item_id' value='" . $row['id'] . "'>";
                    echo "<button type='submit'>Add to Cart</button>";
                    echo "</form>";
                    echo "</div>";
                    echo "</div>";
                }
            ?>
        </div>
    </div>
</div>
</body>
</html>
