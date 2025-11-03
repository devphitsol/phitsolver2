# Main Image Selection Feature

## Overview

The Main Image Selection Feature enhances the Product Management system by allowing users to select a "main image" from existing product images in the Edit Product page. This selected image is automatically displayed in the main Product Preview Image section and is stored in the database for consistent display across the application.

## Key Features

### 1. Main Image Selection from Existing Images
- **Click Selection**: Users can click directly on any existing image thumbnail to set it as the main image
- **Star Button**: A star button appears on hover over each image for explicit main image selection
- **Visual Feedback**: Selected main image is highlighted with a green border and star indicator
- **Real-time Preview**: Selected image immediately appears in the main Product Preview Image section

### 2. Database Integration
- **New Field**: `main_image` field added to the products collection
- **Backward Compatibility**: Maintains existing `image_url` field for legacy support
- **Priority System**: Main image takes precedence over image_url and first existing image

### 3. User Interface Enhancements
- **Interactive Thumbnails**: Existing images are clickable with hover effects
- **Selection Indicators**: Visual feedback for selected main image
- **Status Updates**: Real-time status messages for image selection actions
- **Responsive Design**: Works seamlessly across different screen sizes

## Technical Implementation

### Database Schema

```javascript
// Products Collection
{
  _id: ObjectId,
  name: String,
  description: String,
  category: String,
  price: Number,
  sku: String,
  stock_quantity: Number,
  status: String,
  images: Array,           // Array of image paths
  image_url: String,       // Legacy field for backward compatibility
  main_image: String,      // NEW: Selected main image path
  company_id: ObjectId,
  created_at: Date,
  updated_at: Date
}
```

### File Structure

```
admin/
├── views/products/
│   ├── edit-content.php          # Main implementation
│   └── create-content.php        # No changes needed
├── assets/
│   ├── css/
│   │   └── products.css          # Enhanced with main image styles
│   └── docs/
│       └── MAIN_IMAGE_SELECTION_FEATURE.md  # This documentation
app/
├── Controllers/
│   └── ProductController.php     # Updated to handle main_image field
└── Models/
    └── Product.php               # Updated database operations
```

### JavaScript Functions

#### `selectMainImage(element, imageSrc, imagePath)`
- **Purpose**: Handles main image selection from existing images
- **Parameters**:
  - `element`: The clicked element (image or star button)
  - `imageSrc`: The resolved image source URL for preview
  - `imagePath`: The original image path for database storage
- **Actions**:
  - Updates hidden input field with selected image path
  - Updates preview image in main section
  - Provides visual feedback on existing images
  - Updates status messages

#### `updateMainImagePreview(imageSrc)`
- **Purpose**: Updates the main Product Preview Image section
- **Parameters**:
  - `imageSrc`: The image source URL to display
- **Actions**:
  - Replaces preview image content
  - Handles loading states and error conditions
  - Updates status indicators

#### `updateMainImageSelection(selectedImagePath)`
- **Purpose**: Provides visual feedback for main image selection
- **Parameters**:
  - `selectedImagePath`: The path of the selected main image
- **Actions**:
  - Removes previous selection indicators
  - Adds selection styling to chosen image
  - Updates star button states

### CSS Classes

#### Main Image Selection Styles
```css
.existing-image-box.main-image-selected {
    border: 3px solid #28a745;
    box-shadow: 0 0 15px rgba(40, 167, 69, 0.3);
}

.select-main-image-btn {
    position: absolute;
    top: 8px;
    right: 8px;
    background: rgba(255, 255, 255, 0.9);
    border: 2px solid #007bff;
    color: #007bff;
    border-radius: 50%;
    width: 32px;
    height: 32px;
    opacity: 0;
    transition: all 0.3s ease;
}

.select-main-image-btn.selected {
    background: #28a745;
    border-color: #28a745;
    color: white;
    opacity: 1;
}

.main-image-indicator {
    position: absolute;
    top: 8px;
    left: 8px;
    background: #28a745;
    color: white;
    border-radius: 50%;
    width: 24px;
    height: 24px;
    display: none;
}
```

## Usage Instructions

### For Product Editors

1. **Navigate to Edit Product Page**
   - Go to Product Management → Edit Product
   - Existing product images will be displayed as thumbnails

2. **Select Main Image**
   - **Method 1**: Click directly on any existing image thumbnail
   - **Method 2**: Hover over an image and click the star button
   - Selected image will be highlighted with green border and star indicator

3. **Verify Selection**
   - Selected image automatically appears in the main Product Preview Image section
   - Status message confirms "Main image selected"
   - Hidden input field stores the selection for form submission

4. **Save Changes**
   - Click "Update Product" to save the main image selection
   - Main image will be stored in the database and used for product display

### For Developers

#### Adding Main Image Support to New Pages

1. **Include Hidden Input Field**
```html
<input type="hidden" id="main_image" name="main_image" value="">
```

2. **Add Selection Event Handlers**
```javascript
// For image elements
onclick="selectMainImage(this, 'imageSrc', 'imagePath')"

// For star buttons
onclick="selectMainImage(this, 'imageSrc', 'imagePath')"
```

3. **Include CSS Classes**
```css
.existing-image-box.main-image-selected
.select-main-image-btn
.main-image-indicator
```

#### Database Operations

```php
// In ProductController
$data['main_image'] = $_POST['main_image'] ?? '';

// In Product Model
'main_image' => $data['main_image'] ?? ''
```

## Priority System

The system uses a priority-based approach for determining which image to display:

1. **Main Image** (Highest Priority)
   - User-selected main image from existing images
   - Stored in `main_image` field

2. **Image URL** (Medium Priority)
   - Legacy field for external image URLs
   - Stored in `image_url` field

3. **First Existing Image** (Lowest Priority)
   - First image from the `images` array
   - Fallback when no main image or image URL is set

## Error Handling

### Image Loading Failures
- Failed images show error placeholder with warning icon
- Console logging for debugging image path issues
- Graceful fallback to next priority image

### Selection Validation
- Ensures selected image exists in existing images array
- Validates image path before updating preview
- Provides user feedback for invalid selections

### Database Consistency
- Maintains data integrity during updates
- Preserves existing images when updating main image
- Handles missing or corrupted image references

## Performance Considerations

### Image Optimization
- Thumbnails are generated for existing images
- Lazy loading for image previews
- Efficient path resolution using helper functions

### Memory Management
- Minimal JavaScript memory footprint
- Efficient DOM manipulation
- Cleanup of event listeners

### Database Efficiency
- Indexed queries for main image lookups
- Efficient updates without full document replacement
- Optimized image path storage

## Future Enhancements

### Planned Features
1. **Bulk Main Image Selection**: Select main images for multiple products
2. **Image Cropping**: Crop selected main images to specific dimensions
3. **Auto-Selection**: Automatically select first image as main image
4. **Image Analytics**: Track which images are most commonly selected as main

### Technical Improvements
1. **Image Compression**: Automatic compression of selected main images
2. **CDN Integration**: Support for CDN-hosted main images
3. **Version Control**: Track changes to main image selections
4. **API Endpoints**: RESTful API for main image management

## Troubleshooting

### Common Issues

#### Main Image Not Displaying
- Check if `main_image` field is properly set in database
- Verify image path resolution in `getImagePath()` function
- Ensure JavaScript functions are properly loaded

#### Selection Not Working
- Verify onclick handlers are properly attached
- Check for JavaScript console errors
- Ensure CSS classes are properly applied

#### Database Update Failures
- Verify `main_image` field is included in update operations
- Check MongoDB connection and permissions
- Validate data types and field names

### Debug Information

#### Console Logging
```javascript
console.log('Selecting main image:', imageSrc, imagePath);
console.log('Main image selected and preview updated');
```

#### Error Logging
```php
error_log("Using main_image: " . $imageUrl);
error_log("Product data for preview: " . json_encode($data));
```

## Related Documentation

- [Enhanced Product Image Management](../ENHANCED_PRODUCT_IMAGE_MANAGEMENT.md)
- [Multiple Product Images Feature](../MULTIPLE_PRODUCT_IMAGES_FEATURE.md)
- [Product Management System](../PRODUCT_MANAGEMENT_SYSTEM.md)

## Version History

- **v1.0.0** (Current): Initial implementation of main image selection
  - Basic selection functionality
  - Visual feedback system
  - Database integration
  - Priority-based display system

---

*This feature enhances the user experience by providing intuitive control over product image presentation while maintaining system performance and data integrity.* 