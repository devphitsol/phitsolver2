# Product Management Feature

## Overview
The Product Management feature allows administrators to manage a product catalog with full CRUD (Create, Read, Update, Delete) operations. This feature is positioned below Company Management in the admin sidebar.

## Features

### 1. Product Listing
- **Statistics Dashboard**: Shows total products, active products, inactive products, and total pages
- **Search Functionality**: Search products by name, description, or category
- **Filtering**: Filter by status (active/inactive)
- **Pagination**: Navigate through large product lists
- **Responsive Table**: Displays product information with thumbnails

### 2. Product Creation
- **Form Validation**: Required fields validation
- **Real-time Preview**: Live preview of product information
- **Image URL Support**: Add product images via URL
- **Category Management**: Auto-complete from existing categories
- **Price and Stock Management**: Numeric inputs with validation

### 3. Product Editing
- **Pre-populated Forms**: All existing data is loaded
- **Live Preview Updates**: Real-time preview changes
- **Status Toggle**: Activate/deactivate products
- **Image Preview**: Display current product image

### 4. Product Actions
- **Edit**: Modify product details
- **Toggle Status**: Activate/deactivate products
- **Delete**: Remove products with confirmation
- **Bulk Operations**: Future enhancement

## Database Schema

### Products Collection
```javascript
{
  _id: ObjectId,
  name: String (required),
  description: String,
  category: String (required),
  price: Number,
  sku: String,
  stock_quantity: Number,
  status: String (active/inactive),
  image_url: String,
  created_at: Date,
  updated_at: Date
}
```

## File Structure

```
admin/
├── views/
│   └── products/
│       ├── content.php          # Main product listing
│       ├── create-content.php   # Product creation form
│       └── edit-content.php     # Product edit form
├── assets/
│   ├── css/
│   │   └── products.css         # Product-specific styles
│   └── docs/
│       └── PRODUCT_MANAGEMENT_FEATURE.md
app/
├── Controllers/
│   └── ProductController.php    # Product business logic
└── Models/
    └── Product.php             # Database operations
```

## API Endpoints

### GET Requests
- `index.php?action=products` - List all products
- `index.php?action=products&method=create` - Show creation form
- `index.php?action=products&method=edit&id={id}` - Show edit form
- `index.php?action=products&method=search&q={query}` - Search products

### POST Requests
- `index.php?action=products&method=store` - Create new product
- `index.php?action=products&method=update&id={id}` - Update product

### Action Requests
- `index.php?action=products&method=delete&id={id}` - Delete product
- `index.php?action=products&method=toggle-status&id={id}` - Toggle status

## Validation Rules

### Required Fields
- Product Name
- Category

### Validation Rules
- Price: Must be non-negative
- Stock Quantity: Must be non-negative
- Image URL: Must be valid URL format (optional)

## UI Components

### Statistics Cards
- Total Products (blue)
- Active Products (green)
- Inactive Products (yellow)
- Total Pages (info)

### Product Table
- Product image/placeholder
- Name and description
- Category badge
- Price (formatted)
- Stock quantity (color-coded)
- SKU (monospace)
- Status badge
- Creation date
- Action buttons

### Forms
- Responsive layout
- Real-time preview
- Validation feedback
- Image preview
- Category autocomplete

## Security Features

### Input Validation
- Server-side validation for all inputs
- XSS protection with htmlspecialchars()
- SQL injection protection via MongoDB driver

### Access Control
- Admin authentication required
- Session-based security
- CSRF protection (via session tokens)

## Future Enhancements

### Planned Features
1. **Bulk Operations**: Select multiple products for batch actions
2. **Image Upload**: Direct file upload instead of URL only
3. **Product Categories**: Hierarchical category management
4. **Product Variants**: Size, color, etc. variations
5. **Inventory Tracking**: Low stock alerts, reorder points
6. **Product Import/Export**: CSV/Excel file support
7. **Advanced Search**: Filters by price range, stock level, etc.
8. **Product Analytics**: View counts, popularity metrics

### Technical Improvements
1. **Caching**: Redis/Memcached for better performance
2. **API Endpoints**: RESTful API for external integrations
3. **Webhooks**: Notifications for product changes
4. **Audit Log**: Track all product modifications
5. **Soft Deletes**: Archive instead of permanent deletion

## Usage Instructions

### Adding a Product
1. Navigate to Product Management
2. Click "Add New Product"
3. Fill in required fields (name, category)
4. Add optional details (price, stock, description, image)
5. Preview the product in real-time
6. Click "Create Product"

### Editing a Product
1. Find the product in the listing
2. Click the edit icon (pencil)
3. Modify the desired fields
4. Preview changes in real-time
5. Click "Update Product"

### Managing Product Status
1. Find the product in the listing
2. Click the status toggle button (play/pause icon)
3. Confirm the action
4. Product status will be updated

### Deleting a Product
1. Find the product in the listing
2. Click the delete icon (trash)
3. Confirm the deletion
4. Product will be permanently removed

## Troubleshooting

### Common Issues
1. **Product not saving**: Check required fields and validation
2. **Image not displaying**: Verify image URL is accessible
3. **Search not working**: Ensure search terms match product data
4. **Pagination issues**: Check database connection and data integrity

### Performance Tips
1. Use appropriate indexes on MongoDB collections
2. Implement pagination for large datasets
3. Optimize image loading with lazy loading
4. Cache frequently accessed data

## Dependencies

### Required Software
- PHP 8.0+
- MongoDB 4.0+
- Bootstrap 5.3.0
- Font Awesome 6.4.0

### PHP Extensions
- MongoDB PHP Driver
- JSON extension
- cURL extension (for image validation)

## Configuration

### Environment Variables
```env
MONGODB_URI=mongodb://localhost:27017
MONGODB_DATABASE=your_database_name
```

### Database Setup
```javascript
// Create products collection
use your_database_name
db.createCollection("products")

// Create indexes for better performance
db.products.createIndex({ "name": 1 })
db.products.createIndex({ "category": 1 })
db.products.createIndex({ "status": 1 })
db.products.createIndex({ "created_at": -1 })
``` 