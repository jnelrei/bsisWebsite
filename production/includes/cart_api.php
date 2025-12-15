<?php
/**
 * Cart API for user dashboard/cart pages.
 * Provides CRUD operations for cart items stored in database tables:
 * carts (cart_id, user_id, status) and cart_items (id, cart_id, item_id, quantity, price, subtotal).
 */
session_start();
header('Content-Type: application/json');

require_once __DIR__ . '/db.php';

if (!isset($_SESSION['user_id'])) {
    http_response_code(401);
    echo json_encode(['success' => false, 'message' => 'Not authenticated']);
    exit;
}

$userId = (int) $_SESSION['user_id'];
$action = $_GET['action'] ?? $_POST['action'] ?? '';

/**
 * Return JSON response and stop execution.
 */
function respond(array $data, int $statusCode = 200): void
{
    http_response_code($statusCode);
    echo json_encode($data);
    exit;
}

/**
 * Ensure the user has an active cart and return its ID.
 */
function getActiveCartId(PDO $pdo, int $userId): int
{
    $stmt = $pdo->prepare("SELECT cart_id FROM carts WHERE user_id = ? AND status = 'active' LIMIT 1");
    $stmt->execute([$userId]);
    $cart = $stmt->fetch(PDO::FETCH_ASSOC);

    if ($cart) {
        return (int) $cart['cart_id'];
    }

    // Create a new active cart for the user
    $insert = $pdo->prepare("INSERT INTO carts (user_id, status) VALUES (?, 'active')");
    $insert->execute([$userId]);
    return (int) $pdo->lastInsertId();
}

/**
 * Fetch active cart items for the user.
 */
function handleGetCartItems(PDO $pdo, int $userId): void
{
    $cartId = getActiveCartId($pdo, $userId);

    $stmt = $pdo->prepare("
        SELECT 
            ci.id AS cart_item_id,
            ci.item_id,
            ci.quantity,
            ci.price,
            ci.subtotal,
            i.item_name,
            i.picture,
            i.quantity AS stock_quantity
        FROM cart_items ci
        JOIN carts c ON ci.cart_id = c.cart_id
        JOIN invtry i ON ci.item_id = i.item_id
        WHERE c.user_id = ? AND c.status = 'active' AND c.cart_id = ?
        ORDER BY ci.id DESC
    ");
    $stmt->execute([$userId, $cartId]);
    $items = $stmt->fetchAll(PDO::FETCH_ASSOC);

    $totalItems = 0;
    foreach ($items as $row) {
        $totalItems += (int) $row['quantity'];
    }

    respond([
        'success' => true,
        'items' => $items,
        'total_items' => $totalItems
    ]);
}

/**
 * Fetch total cart item count.
 */
function handleGetCartCount(PDO $pdo, int $userId): void
{
    $stmt = $pdo->prepare("
        SELECT COALESCE(SUM(ci.quantity), 0) AS total_items
        FROM cart_items ci
        JOIN carts c ON ci.cart_id = c.cart_id
        WHERE c.user_id = ? AND c.status = 'active'
    ");
    $stmt->execute([$userId]);
    $count = (int) $stmt->fetchColumn();

    respond([
        'success' => true,
        'total_items' => $count
    ]);
}

/**
 * Add an item to the cart (or update quantity if it already exists).
 */
function handleAddToCart(PDO $pdo, int $userId): void
{
    $itemId = isset($_POST['item_id']) ? (int) $_POST['item_id'] : 0;
    $quantity = isset($_POST['quantity']) ? (int) $_POST['quantity'] : 0;

    if ($itemId <= 0 || $quantity <= 0) {
        respond(['success' => false, 'message' => 'Invalid item or quantity'], 400);
    }

    // Get item details and stock
    $itemStmt = $pdo->prepare("SELECT item_id, item_name, total_cost, quantity AS stock_quantity, picture FROM invtry WHERE item_id = ? LIMIT 1");
    $itemStmt->execute([$itemId]);
    $item = $itemStmt->fetch(PDO::FETCH_ASSOC);

    if (!$item) {
        respond(['success' => false, 'message' => 'Item not found'], 404);
    }

    $cartId = getActiveCartId($pdo, $userId);

    // Check if item already exists in cart
    $existingStmt = $pdo->prepare("SELECT id, quantity, price FROM cart_items WHERE cart_id = ? AND item_id = ? LIMIT 1");
    $existingStmt->execute([$cartId, $itemId]);
    $existing = $existingStmt->fetch(PDO::FETCH_ASSOC);

    $price = (float) $item['total_cost'];
    $newQuantity = $quantity;

    if ($existing) {
        $newQuantity += (int) $existing['quantity'];
    }

    if ($newQuantity > (int) $item['stock_quantity']) {
        respond(['success' => false, 'message' => 'Requested quantity exceeds available stock'], 400);
    }

    $subtotal = $price * $newQuantity;

    if ($existing) {
        $update = $pdo->prepare("UPDATE cart_items SET quantity = ?, price = ?, subtotal = ? WHERE id = ?");
        $update->execute([$newQuantity, $price, $subtotal, $existing['id']]);
    } else {
        $insert = $pdo->prepare("INSERT INTO cart_items (cart_id, item_id, quantity, price, subtotal) VALUES (?, ?, ?, ?, ?)");
        $insert->execute([$cartId, $itemId, $newQuantity, $price, $subtotal]);
    }

    respond(['success' => true, 'message' => 'Item added to cart']);
}

/**
 * Update cart item quantity.
 */
function handleUpdateQuantity(PDO $pdo, int $userId): void
{
    $cartItemId = isset($_POST['cart_item_id']) ? (int) $_POST['cart_item_id'] : 0;
    $quantity = isset($_POST['quantity']) ? (int) $_POST['quantity'] : 0;

    if ($cartItemId <= 0 || $quantity <= 0) {
        respond(['success' => false, 'message' => 'Invalid cart item or quantity'], 400);
    }

    $stmt = $pdo->prepare("
        SELECT ci.id, ci.cart_id, ci.item_id, ci.price, i.quantity AS stock_quantity
        FROM cart_items ci
        JOIN carts c ON ci.cart_id = c.cart_id
        JOIN invtry i ON ci.item_id = i.item_id
        WHERE ci.id = ? AND c.user_id = ? AND c.status = 'active'
        LIMIT 1
    ");
    $stmt->execute([$cartItemId, $userId]);
    $cartItem = $stmt->fetch(PDO::FETCH_ASSOC);

    if (!$cartItem) {
        respond(['success' => false, 'message' => 'Cart item not found'], 404);
    }

    if ($quantity > (int) $cartItem['stock_quantity']) {
        respond(['success' => false, 'message' => 'Requested quantity exceeds available stock'], 400);
    }

    $subtotal = ((float) $cartItem['price']) * $quantity;
    $update = $pdo->prepare("UPDATE cart_items SET quantity = ?, subtotal = ? WHERE id = ?");
    $update->execute([$quantity, $subtotal, $cartItemId]);

    respond(['success' => true, 'message' => 'Quantity updated']);
}

/**
 * Remove a cart item.
 */
function handleRemoveFromCart(PDO $pdo, int $userId): void
{
    $cartItemId = isset($_POST['cart_item_id']) ? (int) $_POST['cart_item_id'] : 0;
    if ($cartItemId <= 0) {
        respond(['success' => false, 'message' => 'Invalid cart item'], 400);
    }

    $stmt = $pdo->prepare("
        DELETE ci FROM cart_items ci
        JOIN carts c ON ci.cart_id = c.cart_id
        WHERE ci.id = ? AND c.user_id = ? AND c.status = 'active'
    ");
    $stmt->execute([$cartItemId, $userId]);

    if ($stmt->rowCount() === 0) {
        respond(['success' => false, 'message' => 'Cart item not found'], 404);
    }

    respond(['success' => true, 'message' => 'Item removed']);
}

try {
    switch ($action) {
        case 'get_cart_items':
            handleGetCartItems($pdo, $userId);
            break;
        case 'get_cart_count':
            handleGetCartCount($pdo, $userId);
            break;
        case 'add_to_cart':
            handleAddToCart($pdo, $userId);
            break;
        case 'update_quantity':
            handleUpdateQuantity($pdo, $userId);
            break;
        case 'remove_from_cart':
            handleRemoveFromCart($pdo, $userId);
            break;
        default:
            respond(['success' => false, 'message' => 'Invalid action'], 400);
    }
} catch (PDOException $e) {
    respond(['success' => false, 'message' => 'Database error: ' . $e->getMessage()], 500);
}



