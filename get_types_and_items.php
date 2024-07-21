<?php
// Include your database connection script
include("connection.php");

// Initialize response array
$response = array();

// Check if category parameter is set
if (isset($_GET['category'])) {
    $category = mysqli_real_escape_string($conn, $_GET['category']);

    // Query to get types based on the selected category
    $queryTypes = "SELECT DISTINCT TYPE FROM pricelist WHERE CATEGORY = '$category'";
    $resultTypes = mysqli_query($conn, $queryTypes);

    // Array to hold types
    $types = array();

    // Fetching types into the array
    while ($rowTypes = mysqli_fetch_assoc($resultTypes)) {
        $types[] = $rowTypes['TYPE'];
    }

    // Add types to the response array
    $response['types'] = $types;

    // Check if type parameter is also set
    if (isset($_GET['type'])) {
        $type = mysqli_real_escape_string($conn, $_GET['type']);

        // Query to get items based on the selected category and type
        $queryItems = "SELECT ITEM FROM pricelist WHERE CATEGORY = '$category' AND TYPE = '$type'";
        $resultItems = mysqli_query($conn, $queryItems);

        // Array to hold items
        $items = array();

        // Fetching items into the array
        while ($rowItems = mysqli_fetch_assoc($resultItems)) {
            $items[] = $rowItems['ITEM'];
        }

        // Add items to the response array
        $response['items'] = $items;
    } else {
        // If type parameter is not set or empty, return an empty items array
        $response['items'] = array();
    }
} else {
    // If category parameter is not set or empty, return an empty types array
    $response['types'] = array();
}

// Return types and items as JSON
echo json_encode($response);

// Close database connection
mysqli_close($conn);
?>
