<?php
$db = pg_connect("host=localhost dbname=my_db user=admin password=nimda");
$query = "SELECT p.surname, p.first_name, i.label, a.quantity 
          FROM allocation a 
          JOIN person p ON a.person_id = p.id 
          JOIN item i ON a.item_id = i.id 
          ORDER BY p.surname, i.label";
$result = pg_query($db, $query);
echo "<h2>Allocations</h2><table border='1'><tr><th>Surname</th><th>First Name</th><th>Item</th><th>Qty</th></tr>";
while ($row = pg_fetch_assoc($result)) {
    echo "<tr><td>{$row['surname']}</td><td>{$row['first_name']}</td><td>{$row['label']}</td><td>{$row['quantity']}</td></tr>";
}
echo "</table><br><a href='index.html'>Back</a>";
?>
