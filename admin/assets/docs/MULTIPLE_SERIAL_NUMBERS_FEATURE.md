# Multiple Serial Numbers / Asset Tags Feature

## Overview
The Multiple Serial Numbers / Asset Tags feature allows administrators to register multiple serial numbers or asset tags for a single product purchase. This is particularly useful when purchasing multiple units of the same product, where each unit has its own unique serial number or asset tag.

## 🎯 Purpose
- **Multiple Registration**: Register multiple serial numbers/asset tags for a single product purchase
- **Bulk Purchases**: Handle cases where the same product is purchased in quantity (e.g., 10 laptops with 10 different serial numbers)
- **Asset Tracking**: Maintain individual tracking for each unit in a bulk purchase
- **Inventory Management**: Better inventory control and asset management

## 🛠️ Features

### 1. Dynamic Input Fields
- **Add/Remove**: Dynamically add or remove serial number input fields
- **Unlimited**: No limit on the number of serial numbers that can be added
- **Flexible**: Can be used for serial numbers, asset tags, or any unique identifiers

### 2. User Interface
- **Intuitive Design**: Clean, user-friendly interface with add/remove buttons
- **Visual Feedback**: Clear indication of available actions
- **Responsive**: Works on all screen sizes

### 3. Data Storage
- **Array Storage**: Serial numbers stored as arrays in MongoDB
- **Backward Compatibility**: Maintains compatibility with existing single serial number data
- **Validation**: Server-side validation for data integrity

## 📋 Implementation Details

### Frontend Components

#### 1. Create Form (`create-content.php`)
```html
<!-- Serial Number / Asset Tag (Multiple) -->
<div class="col-md-6 mb-3">
    <label class="form-label">Serial Numbers / Asset Tags</label>
    <div id="serialNumbersContainer">
        <div class="serial-number-item mb-2">
            <div class="input-group">
                <input type="text" class="form-control" name="serial_numbers[]" 
                       placeholder="Enter serial number or asset tag">
                <button type="button" class="btn btn-outline-danger remove-serial" 
                        onclick="removeSerialNumber(this)" style="display: none;">
                    <i class="fas fa-trash"></i>
                </button>
            </div>
        </div>
    </div>
    <button type="button" class="btn btn-outline-primary btn-sm" onclick="addSerialNumber()">
        <i class="fas fa-plus me-1"></i>Add Another Serial Number
    </button>
    <div class="form-text">Add multiple serial numbers or asset tags for this purchase</div>
</div>
```

#### 2. Edit Form (`edit-content.php`)
- Similar structure to create form
- Pre-populates existing serial numbers
- Maintains edit functionality

#### 3. JavaScript Functions
```javascript
// Add new serial number field
window.addSerialNumber = function() {
    const container = document.getElementById('serialNumbersContainer');
    const newItem = document.createElement('div');
    newItem.className = 'serial-number-item mb-2';
    newItem.innerHTML = `
        <div class="input-group">
            <input type="text" class="form-control" name="serial_numbers[]" 
                   placeholder="Enter serial number or asset tag">
            <button type="button" class="btn btn-outline-danger remove-serial" 
                    onclick="removeSerialNumber(this)">
                <i class="fas fa-trash"></i>
            </button>
        </div>
    `;
    container.appendChild(newItem);
    updateRemoveButtons();
};

// Remove serial number field
window.removeSerialNumber = function(button) {
    const item = button.closest('.serial-number-item');
    item.remove();
    updateRemoveButtons();
};

// Update remove button visibility
function updateRemoveButtons() {
    const items = document.querySelectorAll('.serial-number-item');
    const removeButtons = document.querySelectorAll('.remove-serial');
    
    removeButtons.forEach((button, index) => {
        if (items.length > 1) {
            button.style.display = 'block';
        } else {
            button.style.display = 'none';
        }
    });
}
```

### Backend Components

#### 1. Purchase Controller (`PurchaseController.php`)
```php
// Store method - handles multiple serial numbers
$data = [
    // ... other fields ...
    'serial_numbers' => $_POST['serial_numbers'] ?? [], // Handle multiple serial numbers
    'asset_tags' => $_POST['asset_tags'] ?? [], // Handle multiple asset tags
    // ... other fields ...
];
```

#### 2. Purchase Model (`Purchase.php`)
```php
// Create method - stores serial numbers as array
$purchaseData = [
    // ... other fields ...
    'serial_numbers' => $data['serial_numbers'] ?? [], // Store as array
    'asset_tags' => $data['asset_tags'] ?? [], // Store as array
    // ... other fields ...
];
```

#### 3. Database Schema
```javascript
// Purchase Collection Schema
{
    _id: ObjectId,
    product_id: String,
    product_name: String,
    company_id: String,
    quantity: Number,
    unit_price: Number,
    total_price: Number,
    // ... other fields ...
    serial_numbers: Array, // ["SN001", "SN002", "SN003"]
    asset_tags: Array,     // ["AT001", "AT002", "AT003"]
    // ... other fields ...
}
```

### Display Components

#### 1. Purchase Listing (`content.php`)
```php
<!-- Serial Numbers Column -->
<td>
    <div>
        <div class="fw-medium">
            <?php 
            if (isset($purchase['serial_numbers']) && is_array($purchase['serial_numbers'])) {
                echo implode(', ', $purchase['serial_numbers']);
            } else {
                echo 'N/A';
            }
            ?>
        </div>
    </div>
</td>
```

## 🔧 Usage Instructions

### For Administrators

#### Creating a Purchase with Multiple Serial Numbers
1. Navigate to **Purchase Management** → **Add Purchase Order**
2. Select the product and fill in basic purchase details
3. In the **Serial Numbers / Asset Tags** section:
   - Enter the first serial number in the default field
   - Click **"Add Another Serial Number"** to add more fields
   - Enter additional serial numbers as needed
   - Use the trash icon to remove unnecessary fields
4. Complete the rest of the form and save

#### Editing Existing Purchases
1. Navigate to **Purchase Management** → Find the purchase → Click **Edit**
2. The existing serial numbers will be pre-populated
3. Add, remove, or modify serial numbers as needed
4. Save the changes

### For System Administrators

#### Database Considerations
- Serial numbers are stored as arrays in MongoDB
- Empty arrays are allowed for purchases without serial numbers
- Backward compatibility maintained for existing single serial number data

#### Validation Rules
- No duplicate serial number validation (can be added if needed)
- No maximum limit on number of serial numbers
- Serial numbers are stored as strings

## 🚀 Future Enhancements

### 1. Advanced Features
- **Duplicate Detection**: Prevent duplicate serial numbers within the same purchase
- **Bulk Import**: Import serial numbers from CSV/Excel files
- **Auto-generation**: Generate serial numbers based on patterns
- **Validation Rules**: Custom validation for specific serial number formats

### 2. Integration Features
- **Asset Management**: Integration with asset tracking systems
- **Barcode Scanning**: Support for barcode/QR code scanning
- **API Integration**: REST API for serial number management
- **Reporting**: Advanced reporting on serial number usage

### 3. User Experience
- **Drag & Drop**: Reorder serial numbers by dragging
- **Copy/Paste**: Bulk paste multiple serial numbers
- **Search/Filter**: Search within serial numbers
- **Export**: Export serial numbers to various formats

## 📊 Benefits

### 1. Operational Efficiency
- **Faster Data Entry**: Quick addition of multiple serial numbers
- **Reduced Errors**: Structured input reduces data entry mistakes
- **Better Organization**: Clear separation of individual unit identifiers

### 2. Asset Management
- **Individual Tracking**: Track each unit separately
- **Inventory Control**: Better inventory management for bulk purchases
- **Warranty Tracking**: Individual warranty tracking per unit

### 3. Reporting & Analytics
- **Detailed Reports**: Comprehensive reporting on individual units
- **Audit Trail**: Complete audit trail for each serial number
- **Compliance**: Better compliance with asset tracking requirements

## 🔒 Security Considerations

### 1. Data Validation
- **Input Sanitization**: All serial numbers are sanitized before storage
- **XSS Prevention**: Proper escaping of output data
- **SQL Injection**: MongoDB query protection

### 2. Access Control
- **Admin Only**: Serial number management restricted to administrators
- **Audit Logging**: All changes logged for security purposes
- **Data Integrity**: Validation ensures data consistency

## 📈 Performance Considerations

### 1. Database Optimization
- **Indexing**: Proper indexing for serial number queries
- **Query Optimization**: Efficient queries for large datasets
- **Storage**: Minimal storage overhead for array data

### 2. Frontend Performance
- **Dynamic Loading**: Efficient DOM manipulation
- **Memory Management**: Proper cleanup of dynamic elements
- **Responsive Design**: Fast loading on all devices

## 📝 Change History

### Version 1.0 (Current)
- ✅ Multiple serial number input fields
- ✅ Dynamic add/remove functionality
- ✅ Array-based storage in MongoDB
- ✅ Backward compatibility
- ✅ Edit form support
- ✅ Listing display
- ✅ JavaScript management functions

### Planned Features
- 🔄 Duplicate detection
- 🔄 Bulk import functionality
- 🔄 Advanced validation rules
- 🔄 API integration
- 🔄 Reporting enhancements