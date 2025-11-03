# Multiple Product Images Feature

## Overview
This feature allows administrators to upload and manage up to 5 images per product in the Product Management system.

## Features

### 1. Multiple Image Upload
- **Maximum Images**: Up to 5 images per product
- **File Types**: JPG, PNG, GIF, WebP
- **File Size**: Maximum 5MB per image
- **Upload Location**: `public/uploads/products/`

### 2. Image Management
- **Add Images**: Upload new images during product creation or editing
- **Remove Images**: Remove existing images with confirmation dialog
- **Preview**: Real-time preview of uploaded images
- **Backward Compatibility**: Maintains support for single `image_url` field

### 3. User Interface
- **Drag & Drop**: Visual upload interface with drag-and-drop styling
- **Image Preview**: Thumbnail preview of uploaded images
- **Remove Buttons**: Individual remove buttons for each image
- **Dynamic Addition**: "Add Another Image" button that appears/disappears based on image count

## Technical Implementation

### Database Changes
- Added `images` array field to products collection
- Maintains `image_url` field for backward compatibility
- Images stored as file paths in the array

### File Structure
```
public/uploads/products/
├── [unique_id]_[timestamp].jpg
├── [unique_id]_[timestamp].png
└── ...
```

### Backend Changes

#### Product Model (`app/Models/Product.php`)
- Updated `create()` method to handle `images` array
- Updated `update()` method to handle `images` array
- Maintains backward compatibility with `image_url`

#### Product Controller (`app/Controllers/ProductController.php`)
- Added `handleImageUploads()` method for file processing
- File validation (type, size, count)
- Unique filename generation
- Directory creation if not exists

### Frontend Changes

#### Create Form (`admin/views/products/create-content.php`)
- Multiple file upload interface
- Dynamic image addition (up to 5)
- Real-time preview
- Remove functionality

#### Edit Form (`admin/views/products/edit-content.php`)
- Display existing images
- Remove existing images
- Add new images
- Maintain total image count limit

#### Product List (`admin/views/products/content.php`)
- Display first image from `images` array
- Fallback to `image_url` for backward compatibility

## Usage

### Creating a New Product
1. Navigate to Admin → Product Management → Add New Product
2. Fill in basic product information
3. Upload images using the image upload section
4. Click "Add Another Image" to upload more images (up to 5 total)
5. Submit the form

### Editing an Existing Product
1. Navigate to Admin → Product Management
2. Click "Edit" on any product
3. View existing images in the "Current Product Images" section
4. Remove images by clicking the "X" button
5. Add new images using the upload section
6. Submit changes

### Image Removal
- **New Images**: Click the "X" button on the preview
- **Existing Images**: Click the "X" button and confirm removal

## Validation Rules

### File Validation
- **Types**: Only JPG, PNG, GIF, WebP allowed
- **Size**: Maximum 5MB per image
- **Count**: Maximum 5 images per product

### Error Handling
- Invalid file types are silently skipped
- Files exceeding size limit are silently skipped
- Directory creation errors are handled gracefully

## Security Considerations

### File Upload Security
- File type validation
- File size limits
- Unique filename generation
- Secure file storage location

### Access Control
- Admin-only access to upload functionality
- Proper file permissions on upload directory

## Backward Compatibility

### Existing Products
- Products with only `image_url` continue to work
- `image_url` field is preserved during updates
- First image from `images` array takes precedence in display

### Database Migration
- No migration required
- New `images` field is optional
- Existing data remains intact

## Future Enhancements

### Potential Improvements
- Image resizing/compression
- Image gallery view in product list
- Bulk image upload
- Image reordering functionality
- Image alt text support
- CDN integration for image storage

## Troubleshooting

### Common Issues
1. **Upload Directory Not Found**: Ensure `public/uploads/products/` directory exists
2. **File Upload Fails**: Check file permissions on upload directory
3. **Images Not Displaying**: Verify file paths and web server configuration
4. **Maximum Images Reached**: Remove existing images before adding new ones

### File Permissions
```bash
chmod 755 public/uploads/products/
chown www-data:www-data public/uploads/products/
```

## API Endpoints

### Product Creation
- **Method**: POST
- **Endpoint**: `index.php?action=products&method=store`
- **Files**: `product_images[]` (multiple files)

### Product Update
- **Method**: POST
- **Endpoint**: `index.php?action=products&method=update&id={id}`
- **Files**: `product_images[]` (multiple files)
- **Data**: `existing_images[]` (array of existing image paths)