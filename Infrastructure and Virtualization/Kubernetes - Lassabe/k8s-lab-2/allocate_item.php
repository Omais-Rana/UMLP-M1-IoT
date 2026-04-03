<?php
$db = pg_connect("host=localhost dbname=my_db user=admin password=nimda");
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $p_id = $_POST['person_id'];
    $i_id = $_POST['item_id'];
    $qty = $_POST['quantity'];
    pg_query($db, "INSERT INTO allocation (person_id, item_id, quantity) VALUES ($p_id, $i_id, $qty)");
    echo "Allocation successful! <a href='index.html'>Back</a>";
    exit;
}
$people = pg_query($db, "SELECT id, first_name, surname FROM person");
$items = pg_query($db, "SELECT id, label FROM item");
?>
<form method="POST">
    Person: <select name="person_id">
        <?php while($row = pg_fetch_assoc($people)) { echo "<option value='{$row['id']}'>{$row['surname']}, {$row['first_name']}</option>"; } ?>
    </select><br>
    Item: <select name="item_id">
        <?php while($row = pg_fetch_assoc($items)) { echo "<option value='{$row['id']}'>{$row['label']}</option>"; } ?>
    </select><br>
    Quantity: <input type="number" name="quantity" required><br>
    <button type="submit">Allocate</button>
</form>
