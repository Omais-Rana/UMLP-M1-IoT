<?php
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $db = pg_connect("host=localhost dbname=my_db user=admin password=nimda");
    $fname = $_POST['first_name'];
    $sname = $_POST['surname'];
    $age = $_POST['age'];
    pg_query($db, "INSERT INTO person (first_name, surname, age) VALUES ('$fname', '$sname', $age)");
    echo "Person added! <a href='index.html'>Back</a>";
    exit;
}
?>
<form method="POST">
    First Name: <input type="text" name="first_name" required><br>
    Surname: <input type="text" name="surname" required><br>
    Age: <input type="number" name="age" required><br>
    <button type="submit">Add Person</button>
</form>
