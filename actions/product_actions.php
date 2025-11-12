<?php
/**
 * Product Actions - Handle all customer-facing product operations
 * This file routes different actions for product display, search, and filtering
 */

// Error reporting
error_reporting(E_ALL);
ini_set('display_errors', 0);
ini_set('log_errors', 1);

// Start output buffering
ob_start();

// Set JSON header
header('Content-Type: application/json; charset=utf-8');

try {
    // Require core files
    require_once '../settings/core.php';
    require_once '../controllers/product_controller.php';
    require_once '../controllers/category_controller.php';
    require_once '../controllers/brand_controller.php';

    // Get action parameter
    $action = $_GET['action'] ?? '';

    // Get pagination parameters
    $page = isset($_GET['page']) ? max(1, (int)$_GET['page']) : 1;
    $limit = isset($_GET['limit']) ? max(1, min(50, (int)$_GET['limit'])) : 10;
    $offset = ($page - 1) * $limit;

    switch ($action) {
        case 'view_all':
            // Get all products with pagination
            $result = view_all_products_ctr($limit, $offset);

            if ($result['success']) {
                $total_pages = ceil($result['total'] / $limit);
                ob_clean();
                echo json_encode([
                    'success' => true,
                    'products' => $result['products'],
                    'pagination' => [
                        'current_page' => $page,
                        'total_pages' => $total_pages,
                        'total_items' => $result['total'],
                        'items_per_page' => $limit
                    ]
                ]);
            } else {
                ob_clean();
                echo json_encode($result);
            }
            break;

        case 'search':
            // Search products
            $query = trim($_GET['query'] ?? '');

            if (empty($query)) {
                ob_clean();
                echo json_encode([
                    'success' => false,
                    'message' => 'Search query is required'
                ]);
                break;
            }

            $result = search_products_paginated_ctr($query, $limit, $offset);

            if ($result['success']) {
                $total_pages = ceil($result['total'] / $limit);
                ob_clean();
                echo json_encode([
                    'success' => true,
                    'products' => $result['products'],
                    'query' => $query,
                    'pagination' => [
                        'current_page' => $page,
                        'total_pages' => $total_pages,
                        'total_items' => $result['total'],
                        'items_per_page' => $limit
                    ]
                ]);
            } else {
                ob_clean();
                echo json_encode($result);
            }
            break;

        case 'filter_by_category':
            // Filter products by category
            $cat_id = (int)($_GET['cat_id'] ?? 0);

            if ($cat_id <= 0) {
                ob_clean();
                echo json_encode([
                    'success' => false,
                    'message' => 'Invalid category ID'
                ]);
                break;
            }

            $result = filter_products_by_category_ctr($cat_id, $limit, $offset);

            if ($result['success']) {
                $total_pages = ceil($result['total'] / $limit);
                ob_clean();
                echo json_encode([
                    'success' => true,
                    'products' => $result['products'],
                    'category_id' => $cat_id,
                    'pagination' => [
                        'current_page' => $page,
                        'total_pages' => $total_pages,
                        'total_items' => $result['total'],
                        'items_per_page' => $limit
                    ]
                ]);
            } else {
                ob_clean();
                echo json_encode($result);
            }
            break;

        case 'filter_by_brand':
            // Filter products by brand
            $brand_id = (int)($_GET['brand_id'] ?? 0);

            if ($brand_id <= 0) {
                ob_clean();
                echo json_encode([
                    'success' => false,
                    'message' => 'Invalid brand ID'
                ]);
                break;
            }

            $result = filter_products_by_brand_ctr($brand_id, $limit, $offset);

            if ($result['success']) {
                $total_pages = ceil($result['total'] / $limit);
                ob_clean();
                echo json_encode([
                    'success' => true,
                    'products' => $result['products'],
                    'brand_id' => $brand_id,
                    'pagination' => [
                        'current_page' => $page,
                        'total_pages' => $total_pages,
                        'total_items' => $result['total'],
                        'items_per_page' => $limit
                    ]
                ]);
            } else {
                ob_clean();
                echo json_encode($result);
            }
            break;

        case 'view_single':
            // View single product
            $product_id = (int)($_GET['id'] ?? 0);

            if ($product_id <= 0) {
                ob_clean();
                echo json_encode([
                    'success' => false,
                    'message' => 'Invalid product ID'
                ]);
                break;
            }

            $result = view_single_product_ctr($product_id);
            ob_clean();
            echo json_encode($result);
            break;

        case 'get_categories':
            // Get all categories for filters
            $result = get_categories_ctr();
            ob_clean();
            echo json_encode($result);
            break;

        case 'get_brands':
            // Get all brands for filters
            $result = get_all_brands_ctr();
            ob_clean();
            echo json_encode($result);
            break;

        default:
            ob_clean();
            echo json_encode([
                'success' => false,
                'message' => 'Invalid action specified'
            ]);
            break;
    }

} catch (Exception $e) {
    ob_clean();
    error_log('Product actions error: ' . $e->getMessage() . ' in ' . $e->getFile() . ' on line ' . $e->getLine());
    echo json_encode([
        'success' => false,
        'message' => 'An error occurred while processing your request.',
        'error' => $e->getMessage()
    ]);
} catch (Error $e) {
    ob_clean();
    error_log('Product actions fatal error: ' . $e->getMessage() . ' in ' . $e->getFile() . ' on line ' . $e->getLine());
    echo json_encode([
        'success' => false,
        'message' => 'A fatal error occurred.',
        'error' => $e->getMessage()
    ]);
}

ob_end_flush();
exit();
?>
