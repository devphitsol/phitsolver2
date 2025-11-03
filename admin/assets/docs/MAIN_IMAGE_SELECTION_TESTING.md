# Main Image Selection Feature - Testing Checklist

## 🧪 **Testing Overview**
This document provides a comprehensive testing checklist for the Main Image Selection Feature to ensure all functionality works correctly across admin and frontend interfaces.

## 📋 **Pre-Testing Setup**

### 1. Environment Verification
- [ ] Upload directory exists: `admin/public/uploads/products/`
- [ ] Directory permissions are correct (755)
- [ ] Web server can write to upload directory
- [ ] MongoDB connection is working
- [ ] Admin panel is accessible

### 2. Test Data Preparation
- [ ] Create test product with multiple images
- [ ] Ensure at least 3-5 different image files are available
- [ ] Test images should be different formats (JPG, PNG, WebP)
- [ ] Test images should be different sizes (small, medium, large)

## 🔧 **Admin Panel Testing**

### 3. Product Creation Testing
- [ ] Navigate to Admin → Product Management → Add New Product
- [ ] Fill in basic product information (name, description, category, price)
- [ ] Upload multiple images (3-5 images)
- [ ] Verify images appear in "Current Product Images" section
- [ ] Verify "Add Another Image" button works correctly
- [ ] Verify image count limit (maximum 5 images)
- [ ] Submit form and verify product is created successfully

### 4. Product Edit - Main Image Selection Testing
- [ ] Navigate to Admin → Product Management
- [ ] Click "Edit" on a product with multiple images
- [ ] Verify existing images are displayed in "Current Product Images" section
- [ ] **Test Star Button Selection:**
  - [ ] Click star button (⭐) on any image
  - [ ] Verify image gets green border and shadow
  - [ ] Verify star button shows selected state
  - [ ] Verify main preview image updates immediately
  - [ ] Verify `main_image` hidden input is updated
- [ ] **Test Direct Image Click Selection:**
  - [ ] Click directly on any image thumbnail
  - [ ] Verify same visual feedback as star button
  - [ ] Verify preview updates immediately
- [ ] **Test Multiple Selections:**
  - [ ] Select different images multiple times
  - [ ] Verify only one image can be selected at a time
  - [ ] Verify previous selection is cleared
- [ ] **Test Form Submission:**
  - [ ] Select a main image
  - [ ] Submit the form
  - [ ] Verify no errors occur
  - [ ] Verify main image selection is saved

### 5. Product Edit - Image Management Testing
- [ ] **Add New Images:**
  - [ ] Upload additional images to existing product
  - [ ] Verify new images appear in "Current Product Images"
  - [ ] Verify main image selection persists
  - [ ] Verify image count limit is enforced
- [ ] **Remove Images:**
  - [ ] Remove non-main images
  - [ ] Verify main image selection remains if main image is not removed
  - [ ] Remove main image
  - [ ] Verify main image selection is cleared
  - [ ] Verify preview updates accordingly

### 6. Edge Cases Testing
- [ ] **No Images:**
  - [ ] Create product without images
  - [ ] Verify no errors occur
  - [ ] Verify placeholder is shown in preview
- [ ] **Single Image:**
  - [ ] Create product with only one image
  - [ ] Verify it can be selected as main image
  - [ ] Verify selection works correctly
- [ ] **Large Images:**
  - [ ] Upload images larger than 5MB
  - [ ] Verify they are rejected appropriately
- [ ] **Invalid File Types:**
  - [ ] Try to upload non-image files
  - [ ] Verify they are rejected appropriately

## 🌐 **Frontend Testing**

### 7. Product Catalogue Testing
- [ ] Navigate to Partners Portal → Product Catalogue
- [ ] **Test Products with Main Image:**
  - [ ] Verify products with `main_image` field display the selected main image
  - [ ] Verify image loads correctly
  - [ ] Verify image path resolution works
- [ ] **Test Products without Main Image:**
  - [ ] Verify products without `main_image` fall back to first image in array
  - [ ] Verify fallback to `image_url` if no images array
  - [ ] Verify fallback to `image` field if no other options
- [ ] **Test Image Loading:**
  - [ ] Verify all images load without errors
  - [ ] Verify placeholder is shown for missing images
  - [ ] Check browser console for any errors

### 8. Cross-Browser Testing
- [ ] Test in Chrome (latest version)
- [ ] Test in Firefox (latest version)
- [ ] Test in Safari (latest version)
- [ ] Test in Edge (latest version)
- [ ] Verify consistent behavior across browsers

### 9. Responsive Testing
- [ ] Test on desktop (1920x1080)
- [ ] Test on tablet (768x1024)
- [ ] Test on mobile (375x667)
- [ ] Verify image display and selection work on all screen sizes

## 🗄️ **Database Testing**

### 10. Data Integrity Testing
- [ ] **Verify Main Image Storage:**
  - [ ] Check MongoDB for `main_image` field
  - [ ] Verify field contains correct image path
  - [ ] Verify field is updated when selection changes
- [ ] **Verify Backward Compatibility:**
  - [ ] Check existing products still work
  - [ ] Verify `image_url` field is preserved
  - [ ] Verify `images` array is preserved
- [ ] **Verify Data Consistency:**
  - [ ] Check that main image path exists in images array
  - [ ] Verify no orphaned main image references

### 11. Performance Testing
- [ ] **Load Testing:**
  - [ ] Test with 100+ products
  - [ ] Verify page load times are acceptable
  - [ ] Check memory usage
- [ ] **Image Loading:**
  - [ ] Verify images load efficiently
  - [ ] Check for any memory leaks
  - [ ] Verify no excessive server requests

## 🐛 **Error Handling Testing**

### 12. Error Scenarios
- [ ] **Network Errors:**
  - [ ] Disconnect internet during image upload
  - [ ] Verify appropriate error message
  - [ ] Verify form data is not lost
- [ ] **Server Errors:**
  - [ ] Simulate server errors during save
  - [ ] Verify error handling works
  - [ ] Verify user gets appropriate feedback
- [ ] **File System Errors:**
  - [ ] Test with full disk space
  - [ ] Test with incorrect permissions
  - [ ] Verify graceful error handling

### 13. JavaScript Error Testing
- [ ] **Console Errors:**
  - [ ] Check browser console for JavaScript errors
  - [ ] Verify no undefined variable errors
  - [ ] Verify no function call errors
- [ ] **Event Handling:**
  - [ ] Test rapid clicking on selection buttons
  - [ ] Verify no race conditions
  - [ ] Verify proper event handling

## 📊 **User Experience Testing**

### 14. Usability Testing
- [ ] **Intuitive Interface:**
  - [ ] Verify star button is clearly visible
  - [ ] Verify selection feedback is obvious
  - [ ] Verify preview updates are immediate
- [ ] **Accessibility:**
  - [ ] Test with keyboard navigation
  - [ ] Verify screen reader compatibility
  - [ ] Check color contrast ratios
- [ ] **Performance:**
  - [ ] Verify smooth animations
  - [ ] Verify no lag during selection
  - [ ] Verify responsive interface

### 15. Workflow Testing
- [ ] **Complete Workflow:**
  - [ ] Create product with multiple images
  - [ ] Select main image
  - [ ] Save product
  - [ ] View in frontend catalogue
  - [ ] Verify main image displays correctly
- [ ] **Edit Workflow:**
  - [ ] Edit existing product
  - [ ] Change main image selection
  - [ ] Save changes
  - [ ] Verify changes persist
  - [ ] Verify frontend updates

## ✅ **Success Criteria**

### All tests must pass for feature to be considered complete:
- [ ] Main image selection works in admin panel
- [ ] Preview updates immediately when selection changes
- [ ] Selection is saved to database correctly
- [ ] Frontend displays selected main image
- [ ] Fallback system works for products without main image
- [ ] No JavaScript errors in console
- [ ] No PHP errors in logs
- [ ] Responsive design works on all devices
- [ ] Performance is acceptable
- [ ] Error handling works gracefully

## 📝 **Test Results Log**

### Test Date: _______________
### Tester: _______________
### Environment: _______________

### Passed Tests: _______________
### Failed Tests: _______________
### Issues Found: _______________
### Recommendations: _______________

## 🔄 **Regression Testing**

After any future changes to the product management system, run these critical tests:
- [ ] Main image selection still works
- [ ] Preview updates correctly
- [ ] Frontend displays main image
- [ ] No new JavaScript errors
- [ ] No new PHP errors
- [ ] Performance is maintained 