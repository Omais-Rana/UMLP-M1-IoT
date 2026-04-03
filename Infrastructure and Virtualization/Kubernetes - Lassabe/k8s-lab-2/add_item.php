<?php
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $db = pg_connect("host=localhost dbname=my_db user=admin password=nimda");
    $label = $_POST['label'];
    pg_query($db, "INSERT INTO item (label) VALUES ('$label')");
    echo "Item added! <a href='index.html'>Back</a>";
    exit;
}
?>
<form method="POST">
    Item Label: <input type="text" name="label" required><br>
    <button type="submit">Add Item</button>
</form>
