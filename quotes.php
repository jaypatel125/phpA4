<?php
// I Jay Patel, 000881881 certify that this material is my original work. No other person's work has been used without suitable acknowledgment and I have not made my work available to anyone else.
/**
* @author: Jay Patel
* @version: 202335.00
* @package COMP 10260 Assignment 4
**/

// Set up database connection
$servername = "localhost";
$username = "sa000881881";
$password = "Sa_20031225";
$dbname = "sa000881881";

try {
    // Create a PDO database connection
    $connection = new PDO("mysql:host=$servername;dbname=$dbname", $username, $password);
    $connection->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
} catch (PDOException $e) {
    // Handle connection error
    die("Connection failed: " . $e->getMessage());
}

/**
 * Generate a Bootstrap card HTML string.
 *
 * @param string $author Author's name.
 * @param string $quote  Quote text.
 * @param string $headerColorClass CSS class for card header color.
 * @param string $bodyColorClass   CSS class for card body color.
 *
 * @return string HTML representation of the Bootstrap card.
 */
function generateCard($author, $quote, $headerColorClass, $bodyColorClass) {
    return '<div class="card mb-3 a4card w-100">
                <div class="card-header ' . $headerColorClass . '">' . $author . '</div>
                <div class="card-body d-flex align-items-center ' . $bodyColorClass . '">
                    <p class="card-text w-100">' . $quote . '</p>
                </div>
            </div>';
}

// AJAX request handling
if ($_SERVER['REQUEST_METHOD'] === 'GET' && isset($_GET['page'])) {
    // Ensure the 'page' parameter is a positive integer
    $page = max(1, intval($_GET['page']));
    $limit = 20;

    // Calculate the offset based on the page and limit
    $offset = ($page - 1) * $limit;

    try {
        // Query to fetch quotes from the database
        $query = "SELECT quotes.quote_text, authors.author_name
                  FROM quotes
                  JOIN authors ON quotes.author_id = authors.author_id
                  LIMIT :per_page
                  OFFSET :offset";

        $stmt = $connection->prepare($query);
        $stmt->bindParam(':per_page', $limit, PDO::PARAM_INT);
        $stmt->bindParam(':offset', $offset, PDO::PARAM_INT);
        $stmt->execute();

        // Fetch results and generate HTML cards
        $cards = [];
        $headerColors = ['bg-primary', 'bg-success', 'bg-info', 'bg-warning', 'bg-danger'];
        $bodyColors = ['bg-danger', 'bg-primary', 'bg-success', 'bg-info', 'bg-warning'];

        $colorIndex = 0;
        while ($row = $stmt->fetch(PDO::FETCH_ASSOC)) {
            // Cycle through colors for header and body of each card
            $headerColorClass = $headerColors[$colorIndex % count($headerColors)];
            $bodyColorClass = $bodyColors[$colorIndex % count($bodyColors)];

            $cards[] = generateCard($row['author_name'], $row['quote_text'], $headerColorClass, $bodyColorClass);
            $colorIndex++;
        }

        // Encode the HTML cards array as JSON and return
        echo json_encode($cards);
    } catch (PDOException $e) {
        // Handle database query error
        echo json_encode(['error' => 'Database error']);
    }
} else {
    // Handle invalid requests
    echo json_encode(['error' => 'Invalid request']);
}

// Close the database connection
$connection = null;

?>