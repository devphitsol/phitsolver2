/* ===================================
   Google Analytics Event Tracking
   ====================================== */

// Newsletter subscription tracking
function trackNewsletterSubscription(email) {
    if (typeof gtag !== 'undefined') {
        gtag('event', 'newsletter_subscription', {
            'event_category': 'engagement',
            'event_label': 'newsletter_signup',
            'value': 1
        });
    }
    
    if (typeof dataLayer !== 'undefined') {
        dataLayer.push({
            'event': 'newsletter_subscription',
            'event_category': 'engagement',
            'event_label': 'newsletter_signup',
            'email_domain': email.split('@')[1] || 'unknown'
        });
    }
}

// Contact form submission tracking
function trackContactFormSubmission(formType) {
    if (typeof gtag !== 'undefined') {
        gtag('event', 'contact_form_submission', {
            'event_category': 'lead_generation',
            'event_label': formType,
            'value': 1
        });
    }
    
    if (typeof dataLayer !== 'undefined') {
        dataLayer.push({
            'event': 'contact_form_submission',
            'event_category': 'lead_generation',
            'event_label': formType,
            'form_type': formType
        });
    }
}

// Partners Portal login tracking
function trackPartnersPortalLogin() {
    if (typeof gtag !== 'undefined') {
        gtag('event', 'partners_portal_login', {
            'event_category': 'authentication',
            'event_label': 'portal_access',
            'value': 1
        });
    }
    
    if (typeof dataLayer !== 'undefined') {
        dataLayer.push({
            'event': 'partners_portal_login',
            'event_category': 'authentication',
            'event_label': 'portal_access'
        });
    }
}

// Button click tracking
function trackButtonClick(buttonName, location) {
    if (typeof gtag !== 'undefined') {
        gtag('event', 'button_click', {
            'event_category': 'engagement',
            'event_label': buttonName,
            'custom_parameter_1': location,
            'value': 1
        });
    }
    
    if (typeof dataLayer !== 'undefined') {
        dataLayer.push({
            'event': 'button_click',
            'event_category': 'engagement',
            'event_label': buttonName,
            'button_location': location
        });
    }
}

// Page view tracking with custom parameters
function trackPageView(pageName, section) {
    if (typeof gtag !== 'undefined') {
        gtag('config', 'G-6DFTSQLP05', {
            'custom_map': {
                'custom_parameter_1': 'page_section'
            },
            'page_section': section
        });
        
        gtag('event', 'page_view', {
            'event_category': 'navigation',
            'event_label': pageName,
            'page_section': section
        });
    }
}

// Service inquiry tracking
function trackServiceInquiry(serviceType) {
    if (typeof gtag !== 'undefined') {
        gtag('event', 'service_inquiry', {
            'event_category': 'lead_generation',
            'event_label': serviceType,
            'value': 1
        });
    }
    
    if (typeof dataLayer !== 'undefined') {
        dataLayer.push({
            'event': 'service_inquiry',
            'event_category': 'lead_generation',
            'event_label': serviceType,
            'service_type': serviceType
        });
    }
}

// Make functions globally available
window.trackNewsletterSubscription = trackNewsletterSubscription;
window.trackContactFormSubmission = trackContactFormSubmission;
window.trackPartnersPortalLogin = trackPartnersPortalLogin;
window.trackButtonClick = trackButtonClick;
window.trackPageView = trackPageView;
window.trackServiceInquiry = trackServiceInquiry;
