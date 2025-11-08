<?php

require_once '../settings/db_class.php';

/**
 * Category class for managing categories in the e-commerce platform
 */
class Category extends db_connection
{
    private $cat_id;
    private $cat_name;
    private $cat_type;

    public function __construct($cat_id = null)
    {
        parent::db_connect();
        if ($cat_id) {
            $this->cat_id = $cat_id;
            $this->loadCategory();
        }
    }

    private function loadCategory($cat_id = null)
    {
        if ($cat_id) {
            $this->cat_id = $cat_id;
        }
        if (!$this->cat_id) {
            return false;
        }
        
        $stmt = $this->db->prepare("SELECT * FROM categories WHERE cat_id = ?");
        $stmt->bind_param("i", $this->cat_id);
        $stmt->execute();
        $result = $stmt->get_result()->fetch_assoc();
        
        if ($result) {
            $this->cat_name = $result['cat_name'];
            $this->cat_type = $result['cat_type'];
        }
    }

    /**
     * Add a new category (alias for createCategory)
     * @param array $args - array containing cat_name and cat_type
     * @return int|false
     */
    public function add($args)
    {
        if (!isset($args['cat_name']) || !isset($args['cat_type'])) {
            return false;
        }
        
        return $this->createCategory($args['cat_name'], $args['cat_type']);
    }

    /**
     * Create a new category
     * @param string $cat_name
     * @param string $cat_type
     * @return int|false
     */
    public function createCategory($cat_name, $cat_type)
    {
        // Check if category name already exists
        if ($this->categoryExists($cat_name)) {
            return false;
        }

        $stmt = $this->db->prepare("INSERT INTO categories (cat_name, cat_type) VALUES (?, ?)");
        $stmt->bind_param("ss", $cat_name, $cat_type);
        
        if ($stmt->execute()) {
            return $this->db->insert_id;
        }
        return false;
    }

    /**
     * Edit/Update a category
     * @param int $cat_id
     * @param string $cat_name
     * @param string $cat_type
     * @return boolean
     */
    public function edit($cat_id, $cat_name, $cat_type)
    {
        return $this->updateCategory($cat_id, $cat_name, $cat_type);
    }

    /**
     * Get all categories
     * @return array|false
     */
    public function getAllCategories()
    {
        $stmt = $this->db->prepare("SELECT * FROM categories ORDER BY cat_type, cat_name");
        $stmt->execute();
        return $stmt->get_result()->fetch_all(MYSQLI_ASSOC);
    }

    /**
     * Get categories (alias for getAllCategories)
     * @return array|false
     */
    public function get()
    {
        return $this->getAllCategories();
    }

    /**
     * Get a specific category by ID
     * @param int $cat_id
     * @return array|false
     */
    public function getCategoryById($cat_id)
    {
        $stmt = $this->db->prepare("SELECT * FROM categories WHERE cat_id = ?");
        $stmt->bind_param("i", $cat_id);
        $stmt->execute();
        return $stmt->get_result()->fetch_assoc();
    }

    /**
     * Update a category
     * @param int $cat_id
     * @param string $cat_name
     * @param string $cat_type
     * @return boolean
     */
    public function updateCategory($cat_id, $cat_name, $cat_type)
    {
        // Check if category name already exists (excluding current category)
        if ($this->categoryExists($cat_name, $cat_id)) {
            return false;
        }

        $stmt = $this->db->prepare("UPDATE categories SET cat_name = ?, cat_type = ? WHERE cat_id = ?");
        $stmt->bind_param("ssi", $cat_name, $cat_type, $cat_id);
        return $stmt->execute();
    }

    /**
     * Delete a category
     * @param int $cat_id
     * @return boolean
     */
    public function deleteCategory($cat_id)
    {
        $stmt = $this->db->prepare("DELETE FROM categories WHERE cat_id = ?");
        $stmt->bind_param("i", $cat_id);
        return $stmt->execute();
    }

    /**
     * Check if category name already exists
     * @param string $cat_name
     * @param int $exclude_id (optional - to exclude current category when updating)
     * @return boolean
     */
    private function categoryExists($cat_name, $exclude_id = null)
    {
        if ($exclude_id) {
            $stmt = $this->db->prepare("SELECT COUNT(*) as count FROM categories WHERE cat_name = ? AND cat_id != ?");
            $stmt->bind_param("si", $cat_name, $exclude_id);
        } else {
            $stmt = $this->db->prepare("SELECT COUNT(*) as count FROM categories WHERE cat_name = ?");
            $stmt->bind_param("s", $cat_name);
        }
        
        $stmt->execute();
        $result = $stmt->get_result()->fetch_assoc();
        return $result['count'] > 0;
    }

    /**
     * Get all category types
     * @return array
     */
    public function getCategoryTypes()
    {
        return [
            'cuisine' => 'Cuisine Regions',
            'restaurant_type' => 'Restaurant Type',
            'dietary' => 'Special Dietary'
        ];
    }

    /**
     * Get categories grouped by type
     * @return array
     */
    public function getCategoriesGroupedByType()
    {
        $categories = $this->getAllCategories();
        $grouped = [];
        
        foreach ($categories as $category) {
            $grouped[$category['cat_type']][] = $category;
        }
        
        return $grouped;
    }
}
