<?php 
include "../includes/header.php"; 
include "../includes/database.php"; 
?>

<h2>Welcome to the TTRPG Shop</h2>

<p>Browse categories:</p>

<ul>
<?php
// Get categories from DB
$result = $conn->query("SELECT * FROM categories");

// Loop through categories
while ($cat = $result->fetch_assoc()):
?>
    <li>
        <!-- Link for each category with ?category=ID -->
        <a href="search.php?category=<?= $cat['id'] ?>">
            <?= htmlspecialchars($cat['name']) ?>
        </a>
    </li>
<?php endwhile; ?>
</ul>

<?php 
include "../includes/footer.php"; 
?>
