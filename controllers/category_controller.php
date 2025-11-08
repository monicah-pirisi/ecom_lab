<?php

require_once '../classes/category_class.php';

/**
 * Category Controller - handles business logic for category operations
 */

/**
 * Add a new category (using kwargs array)
 * @param array $kwargs - array containing cat_name and cat_type
 * @return array
 */
function add_category_ctr($kwargs)
{
    $category = new Category();
    $result = $category->add($kwargs);
    
    if ($result) {
        return [
            'success' => true,
            'message' => 'Category added successfully',
            'category_id' => $result
        ];
    } else {
        return [
            'success' => false,
            'message' => 'Category addition failed. Category name may already exist or invalid data provided.'
        ];
    }
}

/**
 * Create a new category
 * @param string $cat_name
 * @param string $cat_type
 * @return array
 */
function create_category_ctr($cat_name, $cat_type)
{
    $category = new Category();
    $result = $category->createCategory($cat_name, $cat_type);
    
    if ($result) {
        return [
            'success' => true,
            'message' => 'Category created successfully',
            'category_id' => $result
        ];
    } else {
        return [
            'success' => false,
            'message' => 'Category creation failed. Category name may already exist.'
        ];
    }
}

/**
 * Get all categories
 * @return array
 */
function get_categories_ctr()
{
    $category = new Category();
    $categories = $category->getAllCategories();
    
    if ($categories !== false) {
        return [
            'success' => true,
            'categories' => $categories
        ];
    } else {
        return [
            'success' => false,
            'message' => 'Failed to retrieve categories'
        ];
    }
}

/**
 * Get a specific category by ID
 * @param int $cat_id
 * @return array
 */
function get_category_by_id_ctr($cat_id)
{
    $category = new Category();
    $result = $category->getCategoryById($cat_id);
    
    if ($result) {
        return [
            'success' => true,
            'category' => $result
        ];
    } else {
        return [
            'success' => false,
            'message' => 'Category not found'
        ];
    }
}

/**
 * Update a category
 * @param int $cat_id
 * @param string $cat_name
 * @param string $cat_type
 * @return array
 */
function update_category_ctr($cat_id, $cat_name, $cat_type)
{
    $category = new Category();
    $result = $category->updateCategory($cat_id, $cat_name, $cat_type);
    
    if ($result) {
        return [
            'success' => true,
            'message' => 'Category updated successfully'
        ];
    } else {
        return [
            'success' => false,
            'message' => 'Category update failed. Category name may already exist.'
        ];
    }
}

/**
 * Delete a category
 * @param int $cat_id
 * @return array
 */
function delete_category_ctr($cat_id)
{
    $category = new Category();
    $result = $category->deleteCategory($cat_id);
    
    if ($result) {
        return [
            'success' => true,
            'message' => 'Category deleted successfully'
        ];
    } else {
        return [
            'success' => false,
            'message' => 'Category deletion failed'
        ];
    }
}

/**
 * Get categories grouped by type
 * @return array
 */
function get_categories_grouped_ctr()
{
    $category = new Category();
    $grouped_categories = $category->getCategoriesGroupedByType();
    $category_types = $category->getCategoryTypes();
    
    return [
        'success' => true,
        'grouped_categories' => $grouped_categories,
        'category_types' => $category_types
    ];
}

/**
 * Validate category data
 * @param array $data
 * @return array
 */
function validate_category_data_ctr($data)
{
    $errors = [];
    
    if (empty($data['cat_name'])) {
        $errors[] = 'Category name is required';
    } elseif (strlen($data['cat_name']) < 2) {
        $errors[] = 'Category name must be at least 2 characters long';
    } elseif (strlen($data['cat_name']) > 100) {
        $errors[] = 'Category name must not exceed 100 characters';
    }
    
    if (empty($data['cat_type'])) {
        $errors[] = 'Category type is required';
    } else {
        $valid_types = ['cuisine', 'restaurant_type', 'dietary'];
        if (!in_array($data['cat_type'], $valid_types)) {
            $errors[] = 'Invalid category type';
        }
    }
    
    return [
        'valid' => empty($errors),
        'errors' => $errors
    ];
}
