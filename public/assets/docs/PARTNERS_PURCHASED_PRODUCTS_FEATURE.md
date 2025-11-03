# Partners Purchased Products Feature

## Overview
The Partners Purchased Products feature allows partners to view all products they have purchased through the system. This provides transparency and easy access to purchase history, order details, and product information.

## Features

### 1. **Purchased Products Dashboard**
- **Location**: Partners Portal > Purchased Products
- **Purpose**: Centralized view of all purchased products
- **Access**: Available in sidebar navigation across all partner pages

### 2. **Summary Statistics**
- **Total Purchases**: Count of all purchase orders
- **Delivered**: Count of completed deliveries
- **In Progress**: Count of pending, ordered, paid, or shipped items
- **Unique Products**: Count of different product types purchased

### 3. **Product Grouping**
- Products are grouped by product type for better organization
- Each product shows all related purchase orders
- Summary statistics per product (total orders, quantity, amount spent)

### 4. **Detailed Purchase Information**
For each purchase order, partners can view:
- **Order Number/Reference**: Purchase order identification
- **Quantity**: Number of units purchased
- **Total Price**: Total cost with currency formatting
- **Order Date**: When the order was placed
- **Purchase Status**: Current status (pending, ordered, paid, shipped, delivered, cancelled, returned)
- **Payment Method**: How payment was made
- **Serial Numbers**: Multiple serial numbers/asset tags (if applicable)
- **Notes**: Additional purchase information

### 5. **Visual Status Indicators**
- Color-coded status badges for easy identification
- Different colors for different purchase statuses
- Serial number badges with barcode icons

### 6. **Company Profile Integration**
- **Quick Action Button**: "View Purchased Products" button in Company Profile
- **Direct Access**: Easy navigation from company information
- **Consistent Navigation**: Available in all partner portal pages

## Technical Implementation

### Database Schema
```php
// Purchases Collection
{
    "_id": ObjectId,
    "company_id": ObjectId,        // Partner company ID
    "product_id": ObjectId,        // Product reference
    "product_name": String,        // Product name for display
    "quantity": Number,
    "unit_price": Number,
    "total_price": Number,
    "order_date": UTCDateTime,
    "purchase_order_date": UTCDateTime,
    "order_number": String,
    "reference_no": String,
    "payment_method": String,
    "purchase_status": String,
    "delivery_date": UTCDateTime,
    "invoice_file": String,        // File path
    "warranty_period": String,
    "serial_numbers": Array,       // Multiple serial numbers
    "asset_tags": Array,           // Multiple asset tags
    "notes": String,
    "created_at": UTCDateTime,
    "updated_at": UTCDateTime
}
```

### File Structure
```
public/
├── purchased-products.php          # Main purchased products page
├── company-profile.php             # Updated with quick action button
├── partners-dashboard.php          # Updated sidebar navigation
├── contact-support.php             # Updated sidebar navigation
├── profile.php                     # Updated sidebar navigation
└── assets/
    └── docs/
        └── PARTNERS_PURCHASED_PRODUCTS_FEATURE.md  # This documentation

app/
├── Models/
│   └── Purchase.php               # Added getByCompanyId() method
└── Controllers/
    └── UserController.php         # Used for user data retrieval
```

### Key Methods

#### Purchase Model
```php
public function getByCompanyId($companyId)
{
    // Retrieves all purchases for a specific company
    // Sorted by creation date (newest first)
    // Returns formatted purchase data
}
```

#### Data Processing
```php
// Group purchases by product for better display
$productPurchases = [];
foreach ($purchases as $purchase) {
    $productId = $purchase['product_id'];
    if (!isset($productPurchases[$productId])) {
        $productPurchases[$productId] = [
            'product_name' => $purchase['product_name'],
            'purchases' => []
        ];
    }
    $productPurchases[$productId]['purchases'][] = $purchase;
}
```

## User Interface

### Navigation
- **Sidebar Menu**: "Purchased Products" link in all partner pages
- **Company Profile**: Quick action button for direct access
- **Breadcrumb**: Clear navigation path

### Design Elements
- **Gradient Cards**: Modern design with purple gradient background
- **Status Badges**: Color-coded purchase status indicators
- **Serial Number Badges**: Styled badges for serial numbers/asset tags
- **Summary Cards**: Statistics overview at the top
- **Responsive Layout**: Works on desktop and mobile devices

### Status Color Scheme
- **Pending**: Yellow (#fff3cd)
- **Ordered**: Light Blue (#d1ecf1)
- **Paid**: Light Green (#d4edda)
- **Shipped**: Blue (#cce5ff)
- **Delivered**: Green (#d1e7dd)
- **Cancelled/Returned**: Red (#f8d7da)

## Security Features

### Access Control
- **Session Validation**: Only logged-in partners can access
- **Company Isolation**: Partners can only see their own purchases
- **Data Sanitization**: All output is properly escaped

### Data Protection
- **User Authentication**: Redirects to login if not authenticated
- **Company ID Filtering**: Ensures data isolation between companies
- **Error Handling**: Graceful error handling with logging

## Usage Instructions

### For Partners
1. **Access**: Navigate to Partners Portal and click "Purchased Products" in the sidebar
2. **View Summary**: Check the statistics cards at the top
3. **Browse Products**: Scroll through grouped product purchases
4. **View Details**: Click on individual purchase items for detailed information
5. **Quick Access**: Use the "View Purchased Products" button in Company Profile

### For Administrators
1. **Purchase Creation**: Create purchases through Admin > Company Management > Update Product
2. **Status Updates**: Update purchase statuses as orders progress
3. **Data Management**: Monitor purchase data through the admin interface

## Benefits

### For Partners
- **Transparency**: Complete view of purchase history
- **Organization**: Products grouped logically
- **Tracking**: Easy status monitoring
- **Documentation**: Access to order details and serial numbers

### For Administrators
- **Customer Service**: Partners can self-serve purchase information
- **Reduced Support**: Less need for manual purchase inquiries
- **Data Visibility**: Clear view of partner purchase patterns

## Future Enhancements

### Potential Features
1. **Export Functionality**: PDF/Excel export of purchase history
2. **Filtering Options**: Filter by date range, status, product type
3. **Search Functionality**: Search by order number, product name
4. **Email Notifications**: Status change notifications
5. **Warranty Tracking**: Warranty period monitoring
6. **Invoice Download**: Direct download of invoice files

### Technical Improvements
1. **Pagination**: For large purchase histories
2. **Caching**: Improve performance for frequent access
3. **Real-time Updates**: Live status updates
4. **Mobile App**: Native mobile application
5. **API Integration**: REST API for external systems

## Configuration

### Required Settings
- **MongoDB Connection**: Ensure database connectivity
- **File Upload Path**: Configure invoice file storage
- **Session Management**: Proper session handling
- **Error Logging**: Configure error logging for debugging

### Optional Settings
- **Status Colors**: Customize status badge colors
- **Page Limits**: Set pagination limits
- **Cache Settings**: Configure caching options
- **Export Formats**: Configure export options

## Troubleshooting

### Common Issues
1. **Empty Purchase List**: Check if purchases exist for the company
2. **Missing Product Names**: Verify product data integrity
3. **Date Display Issues**: Check MongoDB date format handling
4. **Permission Errors**: Verify user authentication and company access

### Debug Information
- **Error Logging**: Check server logs for detailed error messages
- **Database Queries**: Verify MongoDB query results
- **Session Data**: Confirm user session and company ID
- **File Permissions**: Check file upload directory permissions

## Support

For technical support or feature requests:
- **Contact**: Use the Contact Support feature in Partners Portal
- **Documentation**: Refer to this documentation file
- **Admin Support**: Contact system administrators for urgent issues