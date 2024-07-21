<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Sales Board</title>
    <style>
        body {
            font-family: Arial, sans-serif;
            background-color: #f0f0f0;
            padding: 20px;
        }

        table {
            width: 100%;
            border-collapse: collapse;
            margin-bottom: 20px;
        }

        table, th, td {
            border: 1px solid #ccc;
        }

        th, td {
            padding: 10px;
            text-align: left;
        }

        th {
            background-color: #f2f2f2;
        }

        select, input[type="number"] {
            width: 100%;
            padding: 8px;
            border: 1px solid #ccc;
            border-radius: 4px;
            box-sizing: border-box;
            font-size: 14px;
        }

        select:focus, input[type="number"]:focus {
            outline: none;
            border-color: dodgerblue;
        }

        .btn-add, .btn-remove {
            padding: 8px 12px;
            background-color: dodgerblue;
            color: white;
            border: none;
            cursor: pointer;
            border-radius: 4px;
            font-size: 14px;
        }

        .btn-remove {
            background-color: #dc3545;
            margin-left: 5px;
        }
    </style>
</head>
<body>
    <h1>Sales Board</h1>

    <table id="salesTable">
        <thead>
            <tr>
                <th>CATEGORY</th>
                <th>TYPE</th>
                <th>ITEM</th>
                <th>QUANTITY</th>
                <th>ACTION</th>
            </tr>
        </thead>
        <tbody>
            <tr>
                <td>
                    <select name="category[]" class="categorySelect">
                        <!-- Options populated dynamically using PHP -->
                        <?php
                        // Include your database connection script
                        include("connection.php");

                        // Fetch distinct categories from your pricelist table
                        $query = "SELECT DISTINCT CATEGORY FROM pricelist";
                        $result = mysqli_query($conn, $query);
                        while ($row = mysqli_fetch_array($result)) {
                            echo '<option value="' . $row['CATEGORY'] . '">' . $row['CATEGORY'] . '</option>';
                        }
                        ?>
                    </select>
                </td>
                <td>
                    <select name="type[]" class="typeSelect">
                        <!-- Options populated dynamically using JavaScript -->
                    </select>
                </td>
                <td>
                    <select name="item[]" class="itemSelect">
                        <!-- Options populated dynamically using JavaScript -->
                    </select>
                </td>
                <td>
                    <input type="number" name="quantity[]" value="1" min="1">
                </td>
                <td>
                    <button type="button" class="btn-add" onclick="addRow()">Add</button>
                    <button type="button" class="btn-remove" onclick="removeRow(this)">Remove</button>
                </td>
            </tr>
        </tbody>
    </table>

    <script>
        // Function to clone and append a new row to the table
        function addRow() {
            var table = document.getElementById('salesTable').getElementsByTagName('tbody')[0];
            var newRow = table.rows[0].cloneNode(true);
            table.appendChild(newRow);
            resetNewRow(newRow);
        }

        // Function to reset values in the cloned row
        function resetNewRow(row) {
            var selects = row.querySelectorAll('select');
            for (var i = 0; i < selects.length; i++) {
                selects[i].selectedIndex = 0; // Reset selects to default option
            }
            var input = row.querySelector('input[type="number"]');
            input.value = 1; // Reset quantity input to 1
        }

        // Function to remove the current row
        function removeRow(button) {
            var row = button.parentNode.parentNode;
            row.parentNode.removeChild(row);
        }

        // JavaScript to handle dynamic loading of types and items based on selected category and type
        document.addEventListener('DOMContentLoaded', function() {
            // Event listener for dynamically added selects (delegated to table)
            document.getElementById('salesTable').addEventListener('change', function(event) {
                var target = event.target;
                if (target.classList.contains('categorySelect')) {
                    updateTypes(target);
                } else if (target.classList.contains('typeSelect')) {
                    updateItems(target);
                }
            });

            // Initial population of types and items for the first row
            var initialCategorySelect = document.querySelector('.categorySelect');
            updateTypes(initialCategorySelect);
        });

        // Function to update types based on selected category
        function updateTypes(select) {
            var category = select.value;
            var xhr = new XMLHttpRequest();
            xhr.onreadystatechange = function() {
                if (xhr.readyState === XMLHttpRequest.DONE) {
                    if (xhr.status === 200) {
                        var data = JSON.parse(xhr.responseText);
                        populateSelect(select.nextElementSibling, data.types);
                    } else {
                        console.error('Request failed: ' + xhr.status);
                    }
                }
            };
            xhr.open('GET', 'get_types_and_items.php?category=' + encodeURIComponent(category), true);
            xhr.send();
        }

        // Function to update items based on selected type
        function updateItems(select) {
            var category = select.parentNode.previousElementSibling.querySelector('.categorySelect').value;
            var type = select.value;
            var xhr = new XMLHttpRequest();
            xhr.onreadystatechange = function() {
                if (xhr.readyState === XMLHttpRequest.DONE) {
                    if (xhr.status === 200) {
                        var data = JSON.parse(xhr.responseText);
                        populateSelect(select.parentNode.nextElementSibling.querySelector('.itemSelect'), data.items);
                    } else {
                        console.error('Request failed: ' + xhr.status);
                    }
                }
            };
            xhr.open('GET', 'get_types_and_items.php?category=' + encodeURIComponent(category) + '&type=' + encodeURIComponent(type), true);
            xhr.send();
        }

        // Function to populate a <select> element with options
        function populateSelect(select, options) {
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
