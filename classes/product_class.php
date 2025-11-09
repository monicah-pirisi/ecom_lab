<?php

require_once '../settings/db_class.php';

/**
 * Product class for managing products in the e-commerce platform
 */
class Product extends db_connection
{
    private $product_id;
    private $product_cat;
    private $product_brand;
    private $product_title;
    private $product_price;
    private $product_desc;
    private $product_image;
    private $product_keywords;
    private $user_id;

    public function __construct($product_id = null)
    {
        parent::db_connect();
        if ($product_id) {
            $this->product_id = $product_id;
            $this->loadProduct();
        }
    }

    private function loadProduct()
    {
        if (!$this->product_id) {
            return false;
        }

        $stmt = $this->db->prepare("SELECT * FROM products WHERE product_id = ?");
        $stmt->bind_param("i", $this->product_id);
        $stmt->execute();
        $result = $stmt->get_result()->fetch_assoc();

        if ($result) {
            $this->product_cat = $result['product_cat'];
            $this->product_brand = $result['product_brand'];
            $this->product_title = $result['product_title'];
            $this->product_price = $result['product_price'];
            $this->product_desc = $result['product_desc'];
            $this->product_image = $result['product_image'];
            $this->product_keywords = $result['product_keywords'];
            $this->user_id = $result['user_id'];
        }
    }

    /**
     * Create a new product
     * @param array $data - Product data
     * @return int|false - Product ID or false on failure
     */
    public function createProduct($data)
    {
        // Validate required fields
        if (!isset($data['product_cat']) || !isset($data['product_brand']) ||
            !isset($data['product_title']) || !isset($data['product_price']) ||
            !isset($data['user_id'])) {
            return false;
        }

        $stmt = $this->db->prepare("INSERT INTO products
            (product_cat, product_brand, product_title, product_price, product_desc,
             product_image, product_keywords, user_id)
            VALUES (?, ?, ?, ?, ?, ?, ?, ?)");

        $stmt->bind_param("iisdsssi",
            $data['product_cat'],
            $data['product_brand'],
            $data['product_title'],
            $data['product_price'],
            $data['product_desc'] ?? '',
            $data['product_image'] ?? '',
            $data['product_keywords'] ?? '',
            $data['user_id']
        );

        if ($stmt->execute()) {
            return $this->db->insert_id;
        }
        return false;
    }

    /**
     * Get all products
     * @return array|false
     */
    public function getAllProducts()
    {
        $stmt = $this->db->prepare("SELECT p.*, c.cat_name, b.brand_name, u.user_name
                                    FROM products p
                                    LEFT JOIN categories c ON p.product_cat = c.cat_id
                                    LEFT JOIN brands b ON p.product_brand = b.brand_id
                                    LEFT JOIN customer u ON p.user_id = u.customer_id
                                    ORDER BY c.cat_name, b.brand_name, p.product_title");
        $stmt->execute();
        return $stmt->get_result()->fetch_all(MYSQLI_ASSOC);
    }

    /**
     * Get products by user ID
     * @param int $user_id
     * @return array|false
     */
    public function getProductsByUserId($user_id)
    {
        $stmt = $this->db->prepare("SELECT p.*, c.cat_name, b.brand_name, u.user_name
                                    FROM products p
                                    LEFT JOIN categories c ON p.product_cat = c.cat_id
                                    LEFT JOIN brands b ON p.product_brand = b.brand_id
                                    LEFT JOIN customer u ON p.user_id = u.customer_id
                                    WHERE p.user_id = ?
                                    ORDER BY c.cat_name, b.brand_name, p.product_title");
        $stmt->bind_param("i", $user_id);
        $stmt->execute();
        return $stmt->get_result()->fetch_all(MYSQLI_ASSOC);
    }

    /**
     * Get a specific product by ID
     * @param int $product_id
     * @return array|false
     */
    public function getProductById($product_id)
    {
        $stmt = $this->db->prepare("SELECT p.*, c.cat_name, b.brand_name, u.user_name
                                    FROM products p
                                    LEFT JOIN categories c ON p.product_cat = c.cat_id
                                    LEFT JOIN brands b ON p.product_brand = b.brand_id
                                    LEFT JOIN customer u ON p.user_id = u.customer_id
                                    WHERE p.product_id = ?");
        $stmt->bind_param("i", $product_id);
        $stmt->execute();
        return $stmt->get_result()->fetch_assoc();
    }

    /**
     * Get products by category
     * @param int $cat_id
     * @return array|false
     */
    public function getProductsByCategory($cat_id)
    {
        $stmt = $this->db->prepare("SELECT p.*, c.cat_name, b.brand_name, u.user_name
                                    FROM products p
                                    LEFT JOIN categories c ON p.product_cat = c.cat_id
                                    LEFT JOIN brands b ON p.product_brand = b.brand_id
                                    LEFT JOIN customer u ON p.user_id = u.customer_id
                                    WHERE p.product_cat = ?
                                    ORDER BY b.brand_name, p.product_title");
        $stmt->bind_param("i", $cat_id);
        $stmt->execute();
        return $stmt->get_result()->fetch_all(MYSQLI_ASSOC);
    }

    /**
     * Get products by brand
     * @param int $brand_id
     * @return array|false
     */
    public function getProductsByBrand($brand_id)
    {
        $stmt = $this->db->prepare("SELECT p.*, c.cat_name, b.brand_name, u.user_name
                                    FROM products p
                                    LEFT JOIN categories c ON p.product_cat = c.cat_id
                                    LEFT JOIN brands b ON p.product_brand = b.brand_id
                                    LEFT JOIN customer u ON p.user_id = u.customer_id
                                    WHERE p.product_brand = ?
                                    ORDER BY c.cat_name, p.product_title");
        $stmt->bind_param("i", $brand_id);
        $stmt->execute();
        return $stmt->get_result()->fetch_all(MYSQLI_ASSOC);
    }

    /**
     * Update a product
     * @param int $product_id
     * @param array $data - Product data
     * @return boolean
     */
    public function updateProduct($product_id, $data)
    {
        // Build update query dynamically based on provided fields
        $fields = [];
        $values = [];
        $types = "";

        if (isset($data['product_cat'])) {
            $fields[] = "product_cat = ?";
            $values[] = $data['product_cat'];
            $types .= "i";
        }
        if (isset($data['product_brand'])) {
            $fields[] = "product_brand = ?";
            $values[] = $data['product_brand'];
            $types .= "i";
        }
        if (isset($data['product_title'])) {
            $fields[] = "product_title = ?";
            $values[] = $data['product_title'];
            $types .= "s";
        }
        if (isset($data['product_price'])) {
            $fields[] = "product_price = ?";
            $values[] = $data['product_price'];
            $types .= "d";
        }
        if (isset($data['product_desc'])) {
            $fields[] = "product_desc = ?";
            $values[] = $data['product_desc'];
            $types .= "s";
        }
        if (isset($data['product_image'])) {
            $fields[] = "product_image = ?";
            $values[] = $data['product_image'];
            $types .= "s";
        }
        if (isset($data['product_keywords'])) {
            $fields[] = "product_keywords = ?";
            $values[] = $data['product_keywords'];
            $types .= "s";
        }

        if (empty($fields)) {
            return false;
        }

        $values[] = $product_id;
        $types .= "i";

        $sql = "UPDATE products SET " . implode(", ", $fields) . " WHERE product_id = ?";
        $stmt = $this->db->prepare($sql);
        $stmt->bind_param($types, ...$values);

        return $stmt->execute();
    }

    /**
     * Delete a product
     * @param int $product_id
     * @return boolean
     */
    public function deleteProduct($product_id)
    {
        // Get product image before deleting to remove file
        $product = $this->getProductById($product_id);

        $stmt = $this->db->prepare("DELETE FROM products WHERE product_id = ?");
        $stmt->bind_param("i", $product_id);
        $result = $stmt->execute();

        // Delete image file if exists
        if ($result && $product && !empty($product['product_image'])) {
            $image_path = "../" . $product['product_image'];
            if (file_exists($image_path)) {
                @unlink($image_path);
            }
        }

        return $result;
    }

    /**
     * Search products by keyword
     * @param string $keyword
     * @return array|false
     */
    public function searchProducts($keyword)
    {
        $search_term = "%{$keyword}%";
        $stmt = $this->db->prepare("SELECT p.*, c.cat_name, b.brand_name, u.user_name
                                    FROM products p
                                    LEFT JOIN categories c ON p.product_cat = c.cat_id
                                    LEFT JOIN brands b ON p.product_brand = b.brand_id
                                    LEFT JOIN customer u ON p.user_id = u.customer_id
                                    WHERE p.product_title LIKE ?
                                       OR p.product_desc LIKE ?
                                       OR p.product_keywords LIKE ?
                                    ORDER BY p.product_title");
        $stmt->bind_param("sss", $search_term, $search_term, $search_term);
        $stmt->execute();
        return $stmt->get_result()->fetch_all(MYSQLI_ASSOC);
    }

    /**
     * Check if user owns product
     * @param int $product_id
     * @param int $user_id
     * @return boolean
     */
    public function userOwnsProduct($product_id, $user_id)
    {
        $stmt = $this->db->prepare("SELECT COUNT(*) as count FROM products WHERE product_id = ? AND user_id = ?");
        $stmt->bind_param("ii", $product_id, $user_id);
        $stmt->execute();
        $result = $stmt->get_result()->fetch_assoc();
        return $result['count'] > 0;
    }
}
