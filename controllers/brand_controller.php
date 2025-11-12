<?php

require_once __DIR__ . '/../classes/brand_class.php';

/**
 * Brand Controller - handles business logic for brand operations
 */

/**
 * Add a new brand (using kwargs array)
 * @param array $kwargs - array containing brand_name, brand_cat, and user_id
 * @return array
 */
function add_brand_ctr($kwargs)
{
    $brand = new Brand();
    $result = $brand->add($kwargs);

    if ($result) {
        return [
            'success' => true,
            'message' => 'Brand added successfully',
            'brand_id' => $result
        ];
    } else {
        return [
            'success' => false,
            'message' => 'Brand addition failed. This brand name and category combination may already exist or invalid data provided.'
        ];
    }
}

/**
 * Create a new brand
 * @param string $brand_name
 * @param int $brand_cat
 * @param int $user_id
 * @return array
 */
function create_brand_ctr($brand_name, $brand_cat, $user_id)
{
    $brand = new Brand();
    $result = $brand->createBrand($brand_name, $brand_cat, $user_id);

    if ($result) {
        return [
            'success' => true,
            'message' => 'Brand created successfully',
            'brand_id' => $result
        ];
    } else {
        return [
            'success' => false,
            'message' => 'Brand creation failed. This brand name and category combination may already exist.'
        ];
    }
}

/**
 * Get all brands
 * @return array
 */
function get_all_brands_ctr()
{
    $brand = new Brand();
    $brands = $brand->getAllBrands();

    if ($brands !== false) {
        return [
            'success' => true,
            'brands' => $brands
        ];
    } else {
        return [
            'success' => false,
            'message' => 'Failed to retrieve brands'
        ];
    }
}

/**
 * Get brands by user ID
 * @param int $user_id
 * @return array
 */
function get_brands_by_user_ctr($user_id)
{
    $brand = new Brand();
    $brands = $brand->getBrandsByUserId($user_id);

    if ($brands !== false) {
        return [
            'success' => true,
            'brands' => $brands
        ];
    } else {
        return [
            'success' => false,
            'message' => 'Failed to retrieve brands'
        ];
    }
}

/**
 * Get brands grouped by category for a specific user
 * @param int $user_id
 * @return array
 */
function get_brands_grouped_by_category_ctr($user_id)
{
    $brand = new Brand();
    $grouped_brands = $brand->getBrandsGroupedByCategory($user_id);

    return [
        'success' => true,
        'grouped_brands' => $grouped_brands
    ];
}

/**
 * Get a specific brand by ID
 * @param int $brand_id
 * @return array
 */
function get_brand_by_id_ctr($brand_id)
{
    $brand = new Brand();
    $result = $brand->getBrandById($brand_id);

    if ($result) {
        return [
            'success' => true,
            'brand' => $result
        ];
    } else {
        return [
            'success' => false,
            'message' => 'Brand not found'
        ];
    }
}

/**
 * Update a brand
 * @param int $brand_id
 * @param string $brand_name
 * @param int $brand_cat
 * @return array
 */
function update_brand_ctr($brand_id, $brand_name, $brand_cat)
{
    $brand = new Brand();
    $result = $brand->updateBrand($brand_id, $brand_name, $brand_cat);

    if ($result) {
        return [
            'success' => true,
            'message' => 'Brand updated successfully'
        ];
    } else {
        return [
            'success' => false,
            'message' => 'Brand update failed. This brand name and category combination may already exist.'
        ];
    }
}

/**
 * Delete a brand
 * @param int $brand_id
 * @return array
 */
function delete_brand_ctr($brand_id)
{
    $brand = new Brand();
    $result = $brand->deleteBrand($brand_id);

    if ($result) {
        return [
            'success' => true,
            'message' => 'Brand deleted successfully'
        ];
    } else {
        return [
            'success' => false,
            'message' => 'Brand deletion failed'
        ];
    }
}

/**
 * Validate brand data
 * @param array $data
 * @return array
 */
function validate_brand_data_ctr($data)
{
    $errors = [];

    if (empty($data['brand_name'])) {
        $errors[] = 'Brand name is required';
    } elseif (strlen($data['brand_name']) < 2) {
        $errors[] = 'Brand name must be at least 2 characters long';
    } elseif (strlen($data['brand_name']) > 100) {
        $errors[] = 'Brand name must not exceed 100 characters';
    }

    if (empty($data['brand_cat'])) {
        $errors[] = 'Category is required';
    } elseif (!is_numeric($data['brand_cat'])) {
        $errors[] = 'Invalid category';
    }

    return [
        'valid' => empty($errors),
        'errors' => $errors
    ];
}
