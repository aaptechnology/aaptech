<?php
include("connection.php");

// Function to generate a unique invoice number 
function generateInvoiceNumber() {
    return 'INV-' . date('Ymd') . '-' . uniqid();
}

$query = "SELECT DISTINCT CATEGORY FROM pricelist";
$result = mysqli_query($conn, $query);

// Initial fetch of types and items (assuming initial category 'a' and type 'typeSelect')
$initialCategory = 'a'; 
$initialType = 'typeSelect'; 

$queryTypes = "SELECT DISTINCT TYPE FROM pricelist WHERE CATEGORY = '$initialCategory'";
$resultTypes = mysqli_query($conn, $queryTypes);

$queryItems = "SELECT ITEM FROM pricelist WHERE CATEGORY = '$initialCategory' AND TYPE = '$initialType'";
$resultItems = mysqli_query($conn, $queryItems);

// Initialize variables for invoice number and date
$invoiceNumber = generateInvoiceNumber();
$invoiceDate = date('Y-m-d'); // Assuming today's date

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    // Check if all form fields are set and not empty
    if (
        isset($_POST['category']) && !empty($_POST['category']) &&
        isset($_POST['type']) && !empty($_POST['type']) &&
        isset($_POST['item']) && !empty($_POST['item']) &&
        isset($_POST['quantity']) && !empty($_POST['quantity'])
    ) {
        $categories = $_POST['category'];
        $types = $_POST['type'];
        $items = $_POST['item'];
        $quantities = $_POST['quantity'];
        $invoiceNumber = $_POST['invoiceNumber'];
        $cusname = $_POST['cusname'];  
        $tel = $_POST['tel'];
        $invno = $_POST['invno'];

        // Insert each row into the sales table
        foreach ($categories as $index => $category) {
            // Ensure index exists for types and items
            $type = isset($types[$index]) ? $types[$index] : '';
            $item = isset($items[$index]) ? $items[$index] : '';
            $quantity = isset($quantities[$index]) ? $quantities[$index] : '';

            // Perform SQL insertion (sanitize inputs appropriately)
            $queryInsert = "INSERT INTO sales (category, type, item, quantity) VALUES ('$category', '$type', '$item', '$quantity')";
            mysqli_query($conn, $queryInsert);
        }

        echo "Sales data has been inserted successfully!";
    } else {
        echo "Please fill in all fields correctly.";
    }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Sales Board</title>
    <link rel="stylesheet" href="sales.css">
</head>
<body>
    <h1><a href="dashboard.php">Sales Board</a></h1>
    <table class="invoice-header" style="border: none;">
        <tr>
            
        <input name="invoiceNumber" type="hidden" value="<?php echo htmlspecialchars($invoiceNumber); ?>">
            
            <td>Customer Name</td>
            <td><input name="cusname" type="text" placeholder="name here"></td>
             <td>Customer Number</td>
             <td><input name = "tel" type="text" placeholder="tel here"></td>
             <td>Invoice No</td>
            <td>   <input name = "invno" type="text" placeholder="enter invoice no. here">   </td>
        </tr>
        <tr>
           
             <td>Date</td>
             <td><input type="text" name="dates" value="<?php echo htmlspecialchars($invoiceDate); ?>" disabled></td>
        
            
            </tr>
        

</table>
    <form method="post">
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
                        <select name="category[]" id="categorySelect">
                            <?php while ($row = mysqli_fetch_array($result)) : ?>
                                <option value="<?php echo $row['CATEGORY']; ?>"><?php echo $row['CATEGORY']; ?></option>
                            <?php endwhile; ?>
                        </select>
                    </td>
                    <td>
                        <select name="type[]" id="typeSelect">
                            <?php mysqli_data_seek($resultTypes, 0); // Reset result set pointer ?>
                            <?php while ($rowTypes = mysqli_fetch_array($resultTypes)) : ?>
                                <option value="<?php echo $rowTypes['TYPE']; ?>"><?php echo $rowTypes['TYPE']; ?></option>
                            <?php endwhile; ?>
                        </select>
                    </td>
                    <td>
                        <select name="item[]" id="itemSelect">
                            <?php mysqli_data_seek($resultItems, 0); // Reset result set pointer ?>
                            <?php while ($rowItems = mysqli_fetch_array($resultItems)) : ?>
                                <option value="<?php echo $rowItems['ITEM']; ?>"><?php echo $rowItems['ITEM']; ?></option>
                            <?php endwhile; ?>
                        </select>
                    </td>
                    <td class="quantity-input">
                        <input type="number" name="quantity[]" value="1" min="1">
                    </td>
                    <td class="quantity-input">
                        <button type="button" class="btn-add" onclick="addRow()">+++</button>
                    </td>
                </tr>
            </tbody>
        </table>
        <div style="text-align: center;">
            <button type="submit">Submit</button>
        </div>
    </form>

    <script>
        // Function to add a new row
        function addRow() {
            var table = document.getElementById('salesTable').getElementsByTagName('tbody')[0];
            var row = table.insertRow(-1);

            // Insert cells into the row
            var cellCategory = row.insertCell(0);
            var cellType = row.insertCell(1);
            var cellItem = row.insertCell(2);
            var cellQuantity = row.insertCell(3);
            var cellAction = row.insertCell(4);

            // Get selected values from the existing select elements
            var categorySelect = document.getElementById('categorySelect');
            var typeSelect = document.getElementById('typeSelect');
            var itemSelect = document.getElementById('itemSelect');
            var quantityInput = document.querySelector('input[name="quantity[]"]').value;

            // Clone the options for type and item select elements
            var newCategorySelect = categorySelect.cloneNode(true);
            var newTypeSelect = typeSelect.cloneNode(true);
            var newItemSelect = itemSelect.cloneNode(true);

            // Set default values to the first option in the cloned selects
            newCategorySelect.selectedIndex = 0;
            newTypeSelect.selectedIndex = 0;
            newItemSelect.selectedIndex = 0;

            // Append the cloned selects to the respective cells
            cellCategory.appendChild(newCategorySelect);
            cellType.appendChild(newTypeSelect);
            cellItem.appendChild(newItemSelect);
            cellQuantity.innerHTML = '<input type="number" name="quantity[]" value="' + quantityInput + '" min="1">';

            // Add buttons to the action cell
            var btnRemove = document.createElement('button');
            btnRemove.textContent = '- - - -';
            btnRemove.className = 'btn-remove';
            btnRemove.onclick = function () {
                removeRow(this);
            };

            cellAction.appendChild(btnRemove);

            // Update event listeners for newly added selects
            newCategorySelect.addEventListener('change', function () {
                var category = this.value;
                updateTypes(category, newTypeSelect);
            });

            newTypeSelect.addEventListener('change', function () {
                var category = newCategorySelect.value;
                var type = this.value;
                updateItems(category, type, newItemSelect);
            });
        }

        // Function to remove a row
        function removeRow(button) {
            var row = button.parentNode.parentNode;
            row.parentNode.removeChild(row);
        }

        // Function to update types based on selected category
        function updateTypes(category, typeSelect) {
            var xhr = new XMLHttpRequest();
            xhr.onreadystatechange = function () {
                if (xhr.readyState === XMLHttpRequest.DONE) {
                    if (xhr.status === 200) {
                        var data = JSON.parse(xhr.responseText);
                        populateSelect(typeSelect, data.types);
                        // After updating types, also update items for the current selected type
                        var currentType = typeSelect.value;
                        updateItems(category, currentType, typeSelect.nextElementSibling);
                    } else {
                        console.error('Request failed: ' + xhr.status);
                    }
                }
            };
            xhr.open('GET', 'get_types_and_items.php?category=' + encodeURIComponent(category), true);
            xhr.send();
        }

        // Function to update items based on selected category and type
        function updateItems(category, type, itemSelect) {
            var xhr = new XMLHttpRequest();
            xhr.onreadystatechange = function () {
                if (xhr.readyState === XMLHttpRequest.DONE) {
                    if (xhr.status === 200) {
                        var data = JSON.parse(xhr.responseText);
                        populateSelect(itemSelect, data.items);
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
            options.forEach(function (option) {
                var optionElem = document.createElement('option');
                optionElem.textContent = option;
                optionElem.value = option;
                select.appendChild(optionElem);
            });
        }

        // Initial setup: Add event listeners to the initial selects
        document.getElementById('categorySelect').addEventListener('change', function () {
            var category = this.value;
            updateTypes(category, document.getElementById('typeSelect'));
        });

        document.getElementById('typeSelect').addEventListener('change', function () {
            var category = document.getElementById('categorySelect').value;
            var type = this.value;
            updateItems(category, type, document.getElementById('itemSelect'));
        });

        // Initial population of types and items based on initial category and type
        updateTypes('a', document.getElementById('typeSelect')); 

    </script>

</body>
</html>

<?php
mysqli_close($conn);
?>
