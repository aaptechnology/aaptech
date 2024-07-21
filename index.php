<?php
// Assuming connection.php contains your database connection code
include("connection.php");

// Fetch distinct categories from your pricelist table
$query = "SELECT DISTINCT CATEGORY FROM pricelist";
$result = mysqli_query($conn, $query);

// Fetch types based on selected category (for initial load, assuming 'a' category)
$queryTypes = "SELECT TYPE FROM pricelist WHERE CATEGORY = 'a'";
$resultTypes = mysqli_query($conn, $queryTypes);
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Sales Board</title>
</head>
<body>
    <table>
        <tr>
            <th>CLASS</th>
            <th>TYPE</th>
            <th>ITEM</th>
            <th>QUANTITY</th>
        </tr>
        <tr>
            <td id="a">
                <select name="category" id="categorySelect">
                    <?php while ($row = mysqli_fetch_array($result)) : ?>
                        <option value="<?php echo $row['CATEGORY']; ?>"><?php echo $row['CATEGORY']; ?></option>
                    <?php endwhile; ?>
                </select>
            </td>
            <td>
                <select name="type" id="typeSelect">
                    <?php while ($rowTypes = mysqli_fetch_array($resultTypes)) : ?>
                        <option value="<?php echo $rowTypes['TYPE']; ?>"><?php echo $rowTypes['TYPE']; ?></option>
                    <?php endwhile; ?>
                </select>
            </td>
            <td>
                <!-- Placeholder for ITEM selection (to be populated via AJAX) -->
                <select name="item" id="itemSelect">
                    <!-- Options will be populated dynamically -->
                </select>
            </td>
        </tr>
    </table>

    <script>
        // JavaScript to handle dynamic loading of types and items based on selected category
        document.getElementById('categorySelect').addEventListener('change', function() {
            var category = this.value;
            var xhr = new XMLHttpRequest();
            xhr.onreadystatechange = function() {
                if (xhr.readyState === XMLHttpRequest.DONE) {
                    if (xhr.status === 200) {
                        var data = JSON.parse(xhr.responseText);
                        populateSelect('typeSelect', data.types);
                        populateSelect('itemSelect', data.items);
                    } else {
                        console.error('Request failed: ' + xhr.status);
                    }
                }
            };
            xhr.open('GET', 'get_types_and_items.php?category=' + encodeURIComponent(category), true);
            xhr.send();
        });

        // Function to populate a <select> element with options
        function populateSelect(selectId, options) {
            var select = document.getElementById(selectId);
            select.innerHTML = '';
            options.forEach(function(option) {
                var optionElem = document.createElement('option');
                optionElem.textContent = option;
                optionElem.value = option;
                select.appendChild(optionElem);
            });
        }
    </script>
</body>
</html>

<?php
// Close your database connection
mysqli_close($conn);
?>
