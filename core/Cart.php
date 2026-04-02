<?php
// ========================================
// CART CLASS - Session & DB Cart Management
// ========================================
class Cart {
    
    private static function getIdentifier(): array {
        if (Auth::isLoggedIn()) {
            return ['user_id' => Auth::id(), 'session_id' => null];
        }
        if (empty($_SESSION['cart_session_id'])) {
            $_SESSION['cart_session_id'] = session_id();
        }
        return ['user_id' => null, 'session_id' => $_SESSION['cart_session_id']];
    }

    private static function getWhere(): array {
        $id = self::getIdentifier();
        if ($id['user_id']) {
            return ['where' => 'user_id = ?', 'params' => [$id['user_id']]];
        }
        return ['where' => 'session_id = ? AND user_id IS NULL', 'params' => [$id['session_id']]];
    }

    public static function add(int $productId, int $qty = 1): bool {
        $product = Database::fetchOne("SELECT id, stock FROM products WHERE id = ? AND status = 'active'", [$productId]);
        if (!$product || $product['stock'] < $qty) return false;

        $id = self::getIdentifier();
        $w = self::getWhere();

        $existing = Database::fetchOne(
            "SELECT id, quantity FROM cart WHERE product_id = ? AND {$w['where']}",
            array_merge([$productId], $w['params'])
        );

        if ($existing) {
            $newQty = min($existing['quantity'] + $qty, $product['stock']);
            Database::update('cart', ['quantity' => $newQty], 
                "id = ?", [$existing['id']]);
        } else {
            Database::insert('cart', array_merge([
                'product_id' => $productId,
                'quantity'   => min($qty, $product['stock']),
            ], $id));
        }
        return true;
    }

    public static function update(int $cartId, int $qty): bool {
        $w = self::getWhere();
        $item = Database::fetchOne(
            "SELECT c.id, p.stock FROM cart c JOIN products p ON p.id = c.product_id 
             WHERE c.id = ? AND {$w['where']}",
            array_merge([$cartId], $w['params'])
        );
        if (!$item) return false;

        if ($qty <= 0) {
            return self::remove($cartId);
        }
        $qty = min($qty, $item['stock']);
        Database::update('cart', ['quantity' => $qty], 'id = ?', [$cartId]);
        return true;
    }

    public static function remove(int $cartId): bool {
        $w = self::getWhere();
        Database::query(
            "DELETE FROM cart WHERE id = ? AND {$w['where']}",
            array_merge([$cartId], $w['params'])
        );
        return true;
    }

    public static function clear(): void {
        $w = self::getWhere();
        Database::query("DELETE FROM cart WHERE {$w['where']}", $w['params']);
    }

    public static function getItems(): array {
        $w = self::getWhere();
        return Database::fetchAll(
            "SELECT c.id, c.quantity, p.id as product_id, p.name, p.slug, p.price, p.stock,
                    (SELECT image_url FROM product_images WHERE product_id = p.id AND is_primary = 1 LIMIT 1) as image,
                    (p.price * c.quantity) as subtotal
             FROM cart c
             JOIN products p ON p.id = c.product_id AND p.status = 'active'
             WHERE {$w['where']}
             ORDER BY c.added_at ASC",
            $w['params']
        );
    }

    public static function count(): int {
        $w = self::getWhere();
        $row = Database::fetchOne(
            "SELECT COALESCE(SUM(c.quantity), 0) as total FROM cart c
             JOIN products p ON p.id = c.product_id AND p.status = 'active'
             WHERE {$w['where']}",
            $w['params']
        );
        return (int) ($row['total'] ?? 0);
    }

    public static function subtotal(): float {
        $items = self::getItems();
        return array_sum(array_column($items, 'subtotal'));
    }

    public static function total(): array {
        $subtotal = self::subtotal();
        $shipping = ($subtotal >= FREE_SHIPPING_THRESHOLD || $subtotal == 0) ? 0 : SHIPPING_FEE;
        return [
            'subtotal' => $subtotal,
            'shipping' => $shipping,
            'total'    => $subtotal + $shipping,
        ];
    }

    // Merge guest cart into user cart after login
    public static function mergeGuestCart(): void {
        if (!Auth::isLoggedIn() || empty($_SESSION['cart_session_id'])) return;
        $sessionId = $_SESSION['cart_session_id'];
        $userId = Auth::id();

        $guestItems = Database::fetchAll(
            "SELECT * FROM cart WHERE session_id = ? AND user_id IS NULL",
            [$sessionId]
        );

        foreach ($guestItems as $item) {
            $existing = Database::fetchOne(
                "SELECT id, quantity FROM cart WHERE user_id = ? AND product_id = ?",
                [$userId, $item['product_id']]
            );
            if ($existing) {
                Database::update('cart', 
                    ['quantity' => $existing['quantity'] + $item['quantity']],
                    'id = ?', [$existing['id']]
                );
                Database::query("DELETE FROM cart WHERE id = ?", [$item['id']]);
            } else {
                Database::update('cart',
                    ['user_id' => $userId, 'session_id' => null],
                    'id = ?', [$item['id']]
                );
            }
        }
        unset($_SESSION['cart_session_id']);
    }
}
