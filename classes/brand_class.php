<?php

require_once __DIR__ . '/../settings/db_class.php';

/**
 * Brand class for managing brands in the e-commerce platform
 */
class Brand extends db_connection
{
    private $brand_id;
    private $brand_name;
    private $brand_cat;
    private $user_id;

    public function __construct($brand_id = null)
    {
        parent::db_connect();
        if ($brand_id) {
            $this->brand_id = $brand_id;
            $this->loadBrand();
        }
    }

    private function loadBrand($brand_id = null)
    {
        if ($brand_id) {
            $this->brand_id = $brand_id;
        }
        if (!$this->brand_id) {
            return false;
        }

        $stmt = $this->db->prepare("SELECT * FROM brands WHERE brand_id = ?");
        $stmt->bind_param("i", $this->brand_id);
        $stmt->execute();
        $result = $stmt->get_result()->fetch_assoc();

        if ($result) {
            $this->brand_name = $result['brand_name'];
            $this->brand_cat = $result['brand_cat'];
            $this->user_id = $result['user_id'];
        }
    }

    /**
     * Add a new brand (alias for createBrand)
     * @param array $args - array containing brand_name, brand_cat, and user_id
     * @return int|false
     */
    public function add($args)
    {
        if (!isset($args['brand_name']) || !isset($args['brand_cat']) || !isset($args['user_id'])) {
            return false;
        }

        return $this->createBrand($args['brand_name'], $args['brand_cat'], $args['user_id']);
    }

    /**
     * Create a new brand
     * @param string $brand_name
     * @param int $brand_cat
     * @param int $user_id
     * @return int|false
     */
    public function createBrand($brand_name, $brand_cat, $user_id)
    {
        // Check if brand name + category combination already exists
        if ($this->brandExists($brand_name, $brand_cat)) {
            return false;
        }

        $stmt = $this->db->prepare("INSERT INTO brands (brand_name, brand_cat, user_id) VALUES (?, ?, ?)");
        $stmt->bind_param("sii", $brand_name, $brand_cat, $user_id);

        if ($stmt->execute()) {
            return $this->db->insert_id;
        }
        return false;
    }

    /**
     * Get brands (alias for getBrandsByUserId)
     * @param int $user_id
     * @return array|false
     */
    public function get($user_id)
    {
        return $this->getBrandsByUserId($user_id);
    }

    /**
     * Get all brands
     * @return array|false
     */
    public function getAllBrands()
    {
        $stmt = $this->db->prepare("SELECT b.*, c.cat_name, c.cat_type
                                    FROM brands b
                                    LEFT JOIN categories c ON b.brand_cat = c.cat_id
                                    ORDER BY c.cat_name, b.brand_name");
        $stmt->execute();
        return $stmt->get_result()->fetch_all(MYSQLI_ASSOC);
    }

    /**
     * Get brands by user ID
     * @param int $user_id
     * @return array|false
     */
    public function getBrandsByUserId($user_id)
    {
        $stmt = $this->db->prepare("SELECT b.*, c.cat_name, c.cat_type
                                    FROM brands b
                                    LEFT JOIN categories c ON b.brand_cat = c.cat_id
                                    WHERE b.user_id = ?
                                    ORDER BY c.cat_name, b.brand_name");
        $stmt->bind_param("i", $user_id);
        $stmt->execute();
        return $stmt->get_result()->fetch_all(MYSQLI_ASSOC);
    }

    /**
     * Get brands grouped by category for a specific user
     * @param int $user_id
     * @return array
     */
    public function getBrandsGroupedByCategory($user_id)
    {
        $brands = $this->getBrandsByUserId($user_id);
        $grouped = [];

        foreach ($brands as $brand) {
            $category_key = $brand['cat_name'];
            if (!isset($grouped[$category_key])) {
                $grouped[$category_key] = [
                    'cat_id' => $brand['brand_cat'],
                    'cat_name' => $brand['cat_name'],
                    'cat_type' => $brand['cat_type'],
                    'brands' => []
                ];
            }
            $grouped[$category_key]['brands'][] = $brand;
        }

        return $grouped;
    }

    /**
     * Get a specific brand by ID
     * @param int $brand_id
     * @return array|false
     */
    public function getBrandById($brand_id)
    {
        $stmt = $this->db->prepare("SELECT b.*, c.cat_name, c.cat_type
                                    FROM brands b
                                    LEFT JOIN categories c ON b.brand_cat = c.cat_id
                                    WHERE b.brand_id = ?");
        $stmt->bind_param("i", $brand_id);
        $stmt->execute();
        return $stmt->get_result()->fetch_assoc();
    }

    /**
     * Edit/Update a brand (alias for updateBrand)
     * @param int $brand_id
     * @param string $brand_name
     * @param int $brand_cat
     * @return boolean
     */
    public function edit($brand_id, $brand_name, $brand_cat)
    {
        return $this->updateBrand($brand_id, $brand_name, $brand_cat);
    }

    /**
     * Update a brand
     * @param int $brand_id
     * @param string $brand_name
     * @param int $brand_cat
     * @return boolean
     */
    public function updateBrand($brand_id, $brand_name, $brand_cat)
    {
        // Check if brand name + category combination already exists (excluding current brand)
        if ($this->brandExists($brand_name, $brand_cat, $brand_id)) {
            return false;
        }

        $stmt = $this->db->prepare("UPDATE brands SET brand_name = ?, brand_cat = ? WHERE brand_id = ?");
        $stmt->bind_param("sii", $brand_name, $brand_cat, $brand_id);
        return $stmt->execute();
    }

    /**
     * Delete a brand
     * @param int $brand_id
     * @return boolean
     */
    public function deleteBrand($brand_id)
    {
        $stmt = $this->db->prepare("DELETE FROM brands WHERE brand_id = ?");
        $stmt->bind_param("i", $brand_id);
        return $stmt->execute();
    }

    /**
     * Check if brand name + category combination already exists
     * @param string $brand_name
     * @param int $brand_cat
     * @param int $exclude_id (optional - to exclude current brand when updating)
     * @return boolean
     */
    private function brandExists($brand_name, $brand_cat, $exclude_id = null)
    {
        if ($exclude_id) {
            $stmt = $this->db->prepare("SELECT COUNT(*) as count FROM brands WHERE brand_name = ? AND brand_cat = ? AND brand_id != ?");
            $stmt->bind_param("sii", $brand_name, $brand_cat, $exclude_id);
        } else {
            $stmt = $this->db->prepare("SELECT COUNT(*) as count FROM brands WHERE brand_name = ? AND brand_cat = ?");
            $stmt->bind_param("si", $brand_name, $brand_cat);
        }

        $stmt->execute();
        $result = $stmt->get_result()->fetch_assoc();
        return $result['count'] > 0;
    }
}
