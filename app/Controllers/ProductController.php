<?php
namespace App\Controllers;

use Database;

class ProductController {
    public function index() {
        $pageTitle = 'Sản Phẩm';
        $pageDescription = 'Khám phá bộ sưu tập trà Cascara từ vỏ cà phê Arabica Việt Nam';

        $sort = $_GET['sort'] ?? 'featured';
        $category = (int)($_GET['category'] ?? 0);

        $orderBy = match($sort) {
            'price_asc'  => 'p.price ASC',
            'price_desc' => 'p.price DESC',
            'newest'     => 'p.created_at DESC',
            'rating'     => 'avg_rating DESC',
            default      => 'p.featured DESC, p.id ASC',
        };

        $params = [];
        $categoryWhere = '';
        if ($category) {
            $categoryWhere = 'AND p.category_id = ?';
            $params[] = $category;
        }

        $products = Database::fetchAll(
            "SELECT p.*,
                    (SELECT image_url FROM product_images WHERE product_id = p.id AND is_primary = 1 LIMIT 1) as primary_image,
                    COALESCE(AVG(r.rating), 0) as avg_rating,
                    COUNT(DISTINCT r.id) as review_count
             FROM products p
             LEFT JOIN reviews r ON r.product_id = p.id AND r.status = 'approved'
             WHERE p.status = 'active' $categoryWhere
             GROUP BY p.id
             ORDER BY $orderBy",
            $params
        );

        foreach ($products as &$p) {
            $p['images'] = Database::fetchAll(
                "SELECT * FROM product_images WHERE product_id = ? ORDER BY sort_order ASC",
                [$p['id']]
            );
        }
        unset($p);

        $categories = Database::fetchAll("SELECT * FROM categories");

        return view('products', [
            'pageTitle' => $pageTitle,
            'pageDescription' => $pageDescription,
            'products' => $products,
            'categories' => $categories,
            'sort' => $sort,
            'category' => $category
        ]);
    }

    public function show($slug) {
        $product = Database::fetchOne(
            "SELECT p.*, c.name as category_name,
                    COALESCE(AVG(r.rating), 0) as avg_rating,
                    COUNT(DISTINCT r.id) as review_count
             FROM products p
             LEFT JOIN categories c ON c.id = p.category_id
             LEFT JOIN reviews r ON r.product_id = p.id AND r.status = 'approved'
             WHERE p.slug = ? AND p.status = 'active'
             GROUP BY p.id",
            [$slug]
        );

        if (!$product) {
            http_response_code(404);
            return view('404');
        }

        $images = Database::fetchAll(
            "SELECT * FROM product_images WHERE product_id = ? ORDER BY sort_order ASC",
            [$product['id']]
        );

        $reviews = Database::fetchAll(
            "SELECT r.*, u.name as user_name, u.avatar as user_avatar
             FROM reviews r
             JOIN users u ON u.id = r.user_id
             WHERE r.product_id = ? AND r.status = 'approved'
             ORDER BY r.created_at DESC",
            [$product['id']]
        );

        $related = Database::fetchAll(
            "SELECT p.*, (SELECT image_url FROM product_images WHERE product_id = p.id AND is_primary = 1 LIMIT 1) as primary_image
             FROM products p
             WHERE p.id != ? AND p.status = 'active'
             LIMIT 3",
            [$product['id']]
        );

        $pageTitle = $product['name'];
        $pageDescription = $product['short_description'];

        return view('product-detail', [
            'pageTitle' => $pageTitle,
            'pageDescription' => $pageDescription,
            'product' => $product,
            'images' => $images,
            'reviews' => $reviews,
            'related' => $related
        ]);
    }

    public function postReview($slug) {
        $product = Database::fetchOne("SELECT id FROM products WHERE slug = ?", [$slug]);
        if (!$product) {
            http_response_code(404);
            return view('404');
        }

        \Auth::requireLogin();
        
        if (\Auth::verifyCsrf()) {
            $rating = (int) ($_POST['rating'] ?? 5);
            $comment = trim($_POST['comment'] ?? '');
            $rating = max(1, min(5, $rating));
            
            if ($comment) {
                Database::insert('reviews', [
                    'product_id' => $product['id'],
                    'user_id'    => \Auth::id(),
                    'rating'     => $rating,
                    'comment'    => $comment,
                    'status'     => 'approved',
                ]);
                \Auth::setFlash('success', 'Đánh giá của bạn đã được gửi!');
            }
        }
        redirect('/product/' . $slug);
    }
}
