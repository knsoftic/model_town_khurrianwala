<?php

/*
|--------------------------------------------------------------------------
| CMS configuration
|--------------------------------------------------------------------------
|
| "settings" defines every admin-editable website setting: the admin
| Settings / Appearance screens render their forms from this list, the
| SettingsRequest builds validation rules from it, and SettingsService
| falls back to the defaults here until a value is saved.
|
| Field keys: type (text|textarea|email|url|tel|number|color|boolean|select|image|code|password),
| label, default, help, options (select), rules (extra validation), super (Super Admin only).
|
*/

$fonts = [
    'Inter' => 'Inter:wght@400;500;600;700',
    'Poppins' => 'Poppins:wght@400;500;600;700',
    'Montserrat' => 'Montserrat:wght@400;500;600;700',
    'Manrope' => 'Manrope:wght@400;500;600;700',
    'DM Sans' => 'DM+Sans:wght@400;500;600;700',
    'Open Sans' => 'Open+Sans:wght@400;500;600;700',
    'Lato' => 'Lato:wght@400;700',
    'Roboto' => 'Roboto:wght@400;500;700',
    'Nunito Sans' => 'Nunito+Sans:wght@400;600;700',
    'Raleway' => 'Raleway:wght@400;500;600;700',
    'Playfair Display' => 'Playfair+Display:wght@500;600;700',
    'Cormorant Garamond' => 'Cormorant+Garamond:wght@500;600;700',
    'Lora' => 'Lora:wght@500;600;700',
    'Merriweather' => 'Merriweather:wght@400;700',
    'Libre Baskerville' => 'Libre+Baskerville:wght@400;700',
    'Marcellus' => 'Marcellus',
];

$fontOptions = array_combine(array_keys($fonts), array_keys($fonts));

return [

    'admin_path' => trim(env('ADMIN_PATH', 'admin'), '/'),

    // Behind Cloudflare or another reverse proxy set TRUSTED_PROXIES="*" (or a comma list of IPs)
    // so visitor IPs, HTTPS detection and the form rate limits use the real client address.
    'trusted_proxies' => env('TRUSTED_PROXIES'),

    // Print the front-end stylesheet inside each page instead of linking it, which removes a
    // render-blocking request (faster first paint on mobile). Turn off to serve it as a file.
    'inline_css' => (bool) env('CMS_INLINE_CSS', true),

    // Custom header/footer scripts and custom JavaScript are only printed when this is true.
    // Turn it off on deployments where staff must not be able to inject scripts.
    'allow_custom_scripts' => (bool) env('CMS_ALLOW_CUSTOM_SCRIPTS', true),

    'uploads' => [
        'image_mimes' => 'jpg,jpeg,png,webp',
        'image_max_kb' => (int) env('CMS_IMAGE_MAX_KB', 5120),
        'video_mimes' => 'mp4,webm',
        'video_max_kb' => (int) env('CMS_VIDEO_MAX_KB', 51200),
        'max_dimension' => 2400,
    ],

    'fonts' => $fonts,

    'settings' => [

        'general' => [
            'label' => 'General',
            'icon' => 'globe',
            'description' => 'Website name, logos and the demo-content notice.',
            'include' => ['logo', 'favicon'],
            'fields' => [
                'site_name' => ['type' => 'text', 'label' => 'Website / Company name', 'default' => 'Model Town Khurrianwala'],
                'site_tagline' => ['type' => 'text', 'label' => 'Website tagline', 'default' => 'Premium Residential & Commercial Plots on Easy Installments'],
                'logo_subtitle' => ['type' => 'text', 'label' => 'Text-logo subtitle', 'default' => 'Plots on Easy Installments', 'help' => 'Shown under the website name when no logo image is uploaded.'],
                'currency_symbol' => ['type' => 'text', 'label' => 'Currency label', 'default' => 'PKR'],
                'price_display' => ['type' => 'select', 'label' => 'Price format on cards', 'default' => 'full', 'options' => ['full' => 'Full (PKR 3,000,000)', 'short' => 'Short (PKR 30 Lakh)']],
                'demo_notice_enabled' => ['type' => 'boolean', 'label' => 'Show demo-content notice bar', 'default' => '1', 'help' => 'Turn off once real prices and content are entered.'],
                'demo_notice_text' => ['type' => 'text', 'label' => 'Demo notice text', 'default' => 'Demo website — all prices, figures and testimonials shown are sample content for preview only.'],
            ],
        ],

        'contact' => [
            'label' => 'Contact Information',
            'icon' => 'phone',
            'description' => 'Every phone number, email and address on the website comes from here.',
            'fields' => [
                'phone' => ['type' => 'tel', 'label' => 'Phone number', 'default' => '+92 300 0000000'],
                'phone_secondary' => ['type' => 'tel', 'label' => 'Secondary phone', 'default' => ''],
                'whatsapp_number' => ['type' => 'tel', 'label' => 'WhatsApp number', 'default' => '+92 300 0000000', 'help' => 'International format, e.g. +92 300 1234567.'],
                'email' => ['type' => 'email', 'label' => 'Email', 'default' => 'info@example.com'],
                'email_secondary' => ['type' => 'email', 'label' => 'Secondary email', 'default' => 'sales@example.com'],
                'office_address' => ['type' => 'textarea', 'label' => 'Office address', 'default' => 'Project Office Address, Khurrianwala, Faisalabad, Punjab'],
                'office_hours' => ['type' => 'text', 'label' => 'Office hours', 'default' => 'Monday – Saturday: 9:00 AM – 6:00 PM'],
                'google_maps_url' => ['type' => 'url', 'label' => 'Google Maps location link', 'default' => 'https://www.google.com/maps/search/?api=1&query=Khurrianwala+Faisalabad'],
                'google_maps_embed' => ['type' => 'textarea', 'label' => 'Google Maps embed', 'default' => 'https://www.google.com/maps?q=Khurrianwala,+Faisalabad&z=13&output=embed', 'help' => 'Paste the Google Maps "Embed a map" iframe code or its URL. Only google.com map URLs are accepted.'],
            ],
        ],

        'header' => [
            'label' => 'Header',
            'icon' => 'panel-top',
            'description' => 'Header buttons, top bar and behaviour. Menu links are managed under CMS → Menus.',
            'include' => ['header_logo', 'color_header_bg', 'color_header_text'],
            'fields' => [
                'header_cta_primary_text' => ['type' => 'text', 'label' => 'Primary button text', 'default' => 'Book Your Plot'],
                'header_cta_primary_url' => ['type' => 'text', 'label' => 'Primary button URL', 'default' => '/book-plot'],
                'header_cta_secondary_text' => ['type' => 'text', 'label' => 'Secondary button text', 'default' => 'Contact Us'],
                'header_cta_secondary_url' => ['type' => 'text', 'label' => 'Secondary button URL', 'default' => '/contact'],
                'header_sticky' => ['type' => 'boolean', 'label' => 'Sticky header', 'default' => '1'],
                'header_transparent_home' => ['type' => 'boolean', 'label' => 'Transparent header over the homepage hero', 'default' => '1'],
                'topbar_enabled' => ['type' => 'boolean', 'label' => 'Show top bar (phone, email, social links)', 'default' => '1'],
                'topbar_text' => ['type' => 'text', 'label' => 'Top bar announcement', 'default' => 'Bookings open — limited plots available on easy installments'],
            ],
        ],

        'footer' => [
            'label' => 'Footer',
            'icon' => 'panel-bottom',
            'description' => 'Footer text, columns and copyright. Footer links are managed under CMS → Menus.',
            'include' => ['footer_logo', 'color_footer_bg', 'color_footer_text'],
            'fields' => [
                'footer_description' => ['type' => 'textarea', 'label' => 'Footer description', 'default' => 'A planned community offering residential and commercial plots with transparent pricing, flexible installment plans and steady on-ground development.'],
                'footer_show_about' => ['type' => 'boolean', 'label' => 'Show about column (logo, description, social)', 'default' => '1'],
                'footer_show_quick_links' => ['type' => 'boolean', 'label' => 'Show quick links column', 'default' => '1'],
                'footer_quick_links_title' => ['type' => 'text', 'label' => 'Quick links heading', 'default' => 'Quick Links'],
                'footer_show_useful_links' => ['type' => 'boolean', 'label' => 'Show useful links column', 'default' => '1'],
                'footer_useful_links_title' => ['type' => 'text', 'label' => 'Useful links heading', 'default' => 'Plots & Plans'],
                'footer_show_contact' => ['type' => 'boolean', 'label' => 'Show contact column', 'default' => '1'],
                'footer_contact_title' => ['type' => 'text', 'label' => 'Contact heading', 'default' => 'Contact Us'],
                'footer_show_newsletter' => ['type' => 'boolean', 'label' => 'Show newsletter box', 'default' => '1'],
                'footer_newsletter_title' => ['type' => 'text', 'label' => 'Newsletter heading', 'default' => 'Stay Updated'],
                'footer_newsletter_text' => ['type' => 'text', 'label' => 'Newsletter text', 'default' => 'Get new launches, payment plan updates and development news in your inbox.'],
                'footer_show_map' => ['type' => 'boolean', 'label' => 'Show "Get directions" link', 'default' => '1'],
                'footer_copyright' => ['type' => 'text', 'label' => 'Copyright text', 'default' => '© {year} {site_name}. All rights reserved.', 'help' => 'Use {year} and {site_name} as placeholders.'],
                'footer_disclaimer' => ['type' => 'text', 'label' => 'Bottom disclaimer', 'default' => 'Prices, payment plans and availability are subject to change. Please confirm with our sales office before booking.'],
            ],
        ],

        'appearance' => [
            'label' => 'Appearance',
            'icon' => 'palette',
            'description' => 'Logos, brand colours, typography and corner radius used across the whole website.',
            'fields' => [
                'logo' => ['type' => 'image', 'label' => 'Website logo', 'help' => 'Used in the admin panel, emails and as a fallback everywhere.'],
                'header_logo' => ['type' => 'image', 'label' => 'Header logo', 'help' => 'Shown in the site header. Leave empty to use the website logo or the website name.'],
                'footer_logo' => ['type' => 'image', 'label' => 'Footer logo'],
                'favicon' => ['type' => 'image', 'label' => 'Favicon', 'help' => 'Square PNG, 64×64 or larger.', 'mimes' => 'png,ico'],
                'color_primary' => ['type' => 'color', 'label' => 'Primary colour', 'default' => '#0B1F3A'],
                'color_secondary' => ['type' => 'color', 'label' => 'Secondary colour', 'default' => '#FFFFFF'],
                'color_accent' => ['type' => 'color', 'label' => 'Accent colour', 'default' => '#C9A54C'],
                'color_background' => ['type' => 'color', 'label' => 'Page background', 'default' => '#F7F6F2'],
                'color_text' => ['type' => 'color', 'label' => 'Text colour', 'default' => '#1E293B'],
                'color_button' => ['type' => 'color', 'label' => 'Button colour', 'default' => '#C9A54C'],
                'color_button_hover' => ['type' => 'color', 'label' => 'Button hover colour', 'default' => '#B08D36'],
                'color_button_text' => ['type' => 'color', 'label' => 'Button text colour', 'default' => '#0B1F3A'],
                'color_header_bg' => ['type' => 'color', 'label' => 'Header background', 'default' => '#0B1F3A'],
                'color_header_text' => ['type' => 'color', 'label' => 'Header text', 'default' => '#FFFFFF'],
                'color_footer_bg' => ['type' => 'color', 'label' => 'Footer background', 'default' => '#081628'],
                'color_footer_text' => ['type' => 'color', 'label' => 'Footer text', 'default' => '#B8C2D3'],
                'border_radius' => ['type' => 'number', 'label' => 'Corner radius (px)', 'default' => '14', 'rules' => ['integer', 'min:0', 'max:40']],
                'font_heading' => ['type' => 'select', 'label' => 'Heading font', 'default' => 'Playfair Display', 'options' => $fontOptions],
                'font_body' => ['type' => 'select', 'label' => 'Body font', 'default' => 'Inter', 'options' => $fontOptions],
                'custom_css' => ['type' => 'code', 'label' => 'Custom CSS', 'default' => '', 'help' => 'Added after the theme styles on every public page.'],
                'custom_js' => ['type' => 'code', 'label' => 'Custom JavaScript', 'default' => '', 'super' => true, 'help' => 'Super Admin only. Printed at the end of every public page when custom scripts are allowed on this server.'],
            ],
        ],

        'seo' => [
            'label' => 'SEO & Analytics',
            'icon' => 'search',
            'description' => 'Default meta tags, social sharing image, analytics and verification.',
            'fields' => [
                'seo_title' => ['type' => 'text', 'label' => 'Default website title', 'default' => 'Model Town Khurrianwala — Plots on Easy Installments'],
                'seo_title_separator' => ['type' => 'text', 'label' => 'Title separator', 'default' => ' | '],
                'seo_description' => ['type' => 'textarea', 'label' => 'Default meta description', 'default' => 'Book 3 Marla, 5 Marla, 10 Marla and 1 Kanal plots on flexible installment plans. View prices, payment schedules, location and development progress.', 'rules' => ['max:500']],
                'seo_keywords' => ['type' => 'text', 'label' => 'Default meta keywords', 'default' => 'plots for sale, installment plots, 5 marla plot, 10 marla plot, 1 kanal plot, Khurrianwala, Faisalabad housing society'],
                'og_image' => ['type' => 'image', 'label' => 'Default Open Graph image', 'help' => '1200×630 recommended. Used when a page has no image of its own.'],
                'google_analytics_id' => ['type' => 'text', 'label' => 'Google Analytics measurement ID', 'default' => '', 'help' => 'e.g. G-XXXXXXXXXX', 'rules' => ['regex:/^(G|UA|GT|AW)-[A-Z0-9-]+$/i']],
                'google_site_verification' => ['type' => 'text', 'label' => 'Google Search Console verification code', 'default' => '', 'help' => 'Only the content value of the meta tag.', 'rules' => ['regex:/^[A-Za-z0-9_\-]+$/']],
                'header_scripts' => ['type' => 'code', 'label' => 'Custom header scripts', 'default' => '', 'super' => true, 'help' => 'Super Admin only. Printed inside <head>.'],
                'footer_scripts' => ['type' => 'code', 'label' => 'Custom footer scripts', 'default' => '', 'super' => true, 'help' => 'Super Admin only. Printed before </body>.'],
            ],
        ],

        'whatsapp' => [
            'label' => 'WhatsApp',
            'icon' => 'message-circle',
            'description' => 'Floating WhatsApp button and pre-filled messages.',
            'include' => ['whatsapp_number'],
            'fields' => [
                'whatsapp_button_enabled' => ['type' => 'boolean', 'label' => 'Show floating WhatsApp button', 'default' => '1'],
                'whatsapp_button_position' => ['type' => 'select', 'label' => 'Button position', 'default' => 'right', 'options' => ['right' => 'Bottom right', 'left' => 'Bottom left']],
                'whatsapp_button_color' => ['type' => 'color', 'label' => 'Button colour', 'default' => '#25D366'],
                'whatsapp_button_label' => ['type' => 'text', 'label' => 'Button label', 'default' => 'Chat on WhatsApp'],
                'whatsapp_default_message' => ['type' => 'textarea', 'label' => 'Default message', 'default' => 'Hello, I am interested in your plot installment plans. Please provide me with more details.'],
                'whatsapp_plot_message' => ['type' => 'textarea', 'label' => 'Plot card message', 'default' => 'Hello, I am interested in the {plot} plot. Please share the price and installment plan details.', 'help' => '{plot} is replaced with the plot size.'],
                'whatsapp_on_plot_cards' => ['type' => 'boolean', 'label' => 'Show WhatsApp button on plot cards', 'default' => '1'],
            ],
        ],

        'forms' => [
            'label' => 'Forms & Booking',
            'icon' => 'clipboard-list',
            'description' => 'Inquiry and booking form options and messages.',
            'fields' => [
                'lead_form_title' => ['type' => 'text', 'label' => 'Inquiry form heading', 'default' => 'Get Prices & Payment Plan'],
                'lead_form_text' => ['type' => 'text', 'label' => 'Inquiry form intro', 'default' => 'Share your details and our sales team will send you the latest prices and installment schedule.'],
                'lead_success_message' => ['type' => 'text', 'label' => 'Inquiry success message', 'default' => 'Thank you! Your inquiry has been received. Our sales team will contact you shortly.'],
                'callback_success_message' => ['type' => 'text', 'label' => 'Callback success message', 'default' => 'Thank you! We will call you back at your preferred time.'],
                'contact_success_message' => ['type' => 'text', 'label' => 'Contact form success message', 'default' => 'Thank you for reaching out. We will reply as soon as possible.'],
                'budget_options' => ['type' => 'textarea', 'label' => 'Budget options', 'default' => "Up to PKR 25 Lakh\nPKR 25 – 50 Lakh\nPKR 50 Lakh – 1 Crore\nAbove PKR 1 Crore", 'help' => 'One option per line.'],
                'booking_show_cnic' => ['type' => 'boolean', 'label' => 'Ask for CNIC on booking form', 'default' => '1'],
                'booking_require_cnic' => ['type' => 'boolean', 'label' => 'CNIC is required', 'default' => '0'],
                'booking_success_message' => ['type' => 'text', 'label' => 'Booking request success message', 'default' => 'Your booking request has been received. Our sales team will contact you to verify your details and explain the next steps.'],
                'booking_disclaimer' => ['type' => 'textarea', 'label' => 'Booking form notice', 'default' => 'Submitting this form sends a booking request only. It does not reserve a plot or take any payment. Our team will contact you to confirm availability and complete the official booking process.'],
            ],
        ],

        'notifications' => [
            'label' => 'Email & Notifications',
            'icon' => 'bell',
            'description' => 'Admin panel alerts are always on. Email alerts for new leads and bookings are optional.',
            'fields' => [
                'notify_by_email' => ['type' => 'boolean', 'label' => 'Email me about new leads and booking requests', 'default' => '0'],
                'notification_email' => ['type' => 'email', 'label' => 'Notification email address', 'default' => ''],
                'mail_from_name' => ['type' => 'text', 'label' => 'Sender name', 'default' => 'Model Town Khurrianwala'],
                'mail_from_address' => ['type' => 'email', 'label' => 'Sender email address', 'default' => ''],
                'smtp_enabled' => ['type' => 'boolean', 'label' => 'Send using custom SMTP server', 'default' => '0', 'help' => 'When off, the server mail settings from .env are used.'],
                'smtp_host' => ['type' => 'text', 'label' => 'SMTP host', 'default' => ''],
                'smtp_port' => ['type' => 'number', 'label' => 'SMTP port', 'default' => '587', 'rules' => ['integer', 'min:1', 'max:65535']],
                'smtp_username' => ['type' => 'text', 'label' => 'SMTP username', 'default' => ''],
                'smtp_password' => ['type' => 'password', 'label' => 'SMTP password', 'default' => '', 'help' => 'Stored encrypted. Leave blank to keep the current password.'],
                'smtp_encryption' => ['type' => 'select', 'label' => 'Encryption', 'default' => 'tls', 'options' => ['tls' => 'TLS', 'ssl' => 'SSL', 'none' => 'None']],
            ],
        ],

    ],

];
