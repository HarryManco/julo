<?php
error_reporting(E_ALL);
ini_set('display_errors', 1);
require_once 'db_connect.php';
session_start();

// Fetch categories
$categoryQuery = "SELECT * FROM categories";
$categoriesResult = $conn->query($categoryQuery);

// Fetch available menu items with category names
$menuQuery = "
    SELECT mi.menuitemid AS ID, mi.itemname AS Name, mi.price, mi.isavailable AS Availability, 
           mi.imageurl, c.categoryname 
    FROM menuitems mi
    LEFT JOIN categories c ON mi.categoryid = c.categoryid
    WHERE mi.isavailable = 1"; // Only fetch available items

$menuResult = $conn->query($menuQuery);

// Check for errors in the query
if (!$menuResult) {
    echo "Error: " . $conn->error;
    exit;
}

$isLoggedIn = true; // Assume user is logged in for this example
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta http-equiv="X-UA-Compatible" content="IE=edge">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link href="https://cdn.lineicons.com/4.0/lineicons.css" rel="stylesheet" />
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet"
          integrity="sha384-T3c6CoIi6uLrA9TneNEoa7RxnatzjcDSCmG1MXxSR1GAsXEV/Dwwykc2MPK8M2HN" crossorigin="anonymous">
    <script src="//cdnjs.cloudflare.com/ajax/libs/jquery/3.2.1/jquery.min.js"></script>
    <link href="https://cdnjs.cloudflare.com/ajax/libs/select2/4.0.6-rc.0/css/select2.min.css" rel="stylesheet">
    <script src="https://cdnjs.cloudflare.com/ajax/libs/select2/4.0.6-rc.0/js/select2.min.js"></script>
    <link rel="stylesheet" href="css/style_lp.css">
    <script src="https://www.paypal.com/sdk/js?client-id=AZeoASur185mEoI_Ds1k9OET1cgdacu9_7yIlGqrSEFAgABJeYnBkRb5PIjJkIrl7gc0pIDr7qmwM09j&currency=USD"></script>

    <title>Cafe Menu</title>
</head>

<body>
    <?php include 'header.php'; ?>
    <div class="container mt-5">
        <h2 class="text-center">Cafe Menu</h2>
        <div class="d-flex justify-content-center align-items-center flex-wrap mt-2">
            <?php if ($menuResult->num_rows > 0): ?>
                <?php while ($row = $menuResult->fetch_assoc()): ?>
                    <div class="card p-3 m-2" style="width: 300px;">
                        <div class="d-flex justify-content-between align-items-center">
                            <div>
                                <h4 class="text-uppercase"><?php echo htmlspecialchars($row['Name']); ?></h4>
                                <p class="mb-1">PHP <?php echo number_format($row['price'], 2); ?></p>
                                <p class="mb-1 <?php echo $row['Availability'] ? 'text-success' : 'text-danger'; ?>">
                                    <?php echo $row['Availability'] ? 'In Stock' : 'Out of Stock'; ?>
                                </p>
                            </div>
                            <img src="<?php echo htmlspecialchars($row['imageurl']) ?: 'default-image.jpg'; ?>" 
                                 alt="<?php echo htmlspecialchars($row['Name']); ?>" 
                                 class="img-fluid" 
                                 style="max-width: 100px; height: auto;">
                        </div>
                        <form action="add_to_cart.php" method="POST">
                            <input type="hidden" name="item_id" value="<?php echo $row['ID']; ?>">
                            <input type="hidden" name="item_name" value="<?php echo htmlspecialchars($row['Name']); ?>">
                            <input type="hidden" name="item_price" value="<?php echo $row['price']; ?>">
                            <input type="hidden" name="item_quantity" value="1">
                            <button type="submit" class="btn btn-primary add-to-cart" 
                                    <?php echo $row['Availability'] ? '' : 'disabled'; ?>>
                                Add to cart
                            </button>
                        </form>
                    </div>
                <?php endwhile; ?>
            <?php else: ?>
                <p class="text-center">No menu items available at this time.</p>
            <?php endif; ?>
        </div>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/js/bootstrap.bundle.min.js"
        integrity="sha384-C6RzsynM9kWDrMNeT87bh95OGNyZPhcTNXj1NW7RuBCsyN/o0jlpcV8Qyq46cDfL"
        crossorigin="anonymous"></script>
</body>
</html>
