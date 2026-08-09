<?php
/**
 * Master content registry for Unnat Technology Services.
 *
 * Every user-visible string, link and image on the public website is listed here
 * exactly once. The key names describe the place on the website they belong to:
 *
 *     <page>.<section>.<element>
 *     home.hero.headline_prefix   -> Home page, hero section, first part of the headline
 *     footer.bottom.copyright     -> Footer, bottom bar, copyright line
 *
 * These values are the seed data for the `cms_content` table and are also used as
 * the fallback whenever the database is unreachable, so the public site keeps
 * rendering its current copy even if MySQL is down.
 *
 * Field types understood by the admin content editor:
 *   text | textarea | html | url | email | tel | image | number
 */
declare(strict_types=1);

return [

    /* ---------------------------------------------------------------------
     * GLOBAL — brand, contact details and social profiles used site-wide
     * ------------------------------------------------------------------ */
    ['key' => 'global.brand.name', 'page' => 'Global', 'section' => 'Brand', 'label' => 'Company name', 'type' => 'text', 'value' => 'Unnat Technology Services'],
    ['key' => 'global.brand.short_name', 'page' => 'Global', 'section' => 'Brand', 'label' => 'Short name (PWA)', 'type' => 'text', 'value' => 'UTS'],
    ['key' => 'global.brand.logo', 'page' => 'Global', 'section' => 'Brand', 'label' => 'Logo image', 'type' => 'image', 'value' => 'assets/images/uts-logo-removebg-removebg-preview-512x512.webp'],
    ['key' => 'global.brand.logo_apple_touch', 'page' => 'Global', 'section' => 'Brand', 'label' => 'Apple touch icon', 'type' => 'image', 'value' => 'assets/images/uts-logo-removebg-removebg-preview.png'],
    ['key' => 'global.brand.tagline', 'page' => 'Global', 'section' => 'Brand', 'label' => 'Tagline', 'type' => 'text', 'value' => 'Smart, scalable and future-ready digital solutions.'],
    ['key' => 'global.brand.theme_color', 'page' => 'Global', 'section' => 'Brand', 'label' => 'Browser theme colour', 'type' => 'text', 'value' => '#fffaf0'],
    ['key' => 'global.brand.language', 'page' => 'Global', 'section' => 'Brand', 'label' => 'HTML language code', 'type' => 'text', 'value' => 'en'],

    ['key' => 'global.contact.phone_display', 'page' => 'Global', 'section' => 'Contact details', 'label' => 'Phone number (displayed)', 'type' => 'text', 'value' => '+91 96908 05228'],
    ['key' => 'global.contact.phone_link', 'page' => 'Global', 'section' => 'Contact details', 'label' => 'Phone number (tel: link)', 'type' => 'tel', 'value' => '+919690805228'],
    ['key' => 'global.contact.email', 'page' => 'Global', 'section' => 'Contact details', 'label' => 'Email address', 'type' => 'email', 'value' => 'unnattechnologyservices@gmail.com'],
    ['key' => 'global.contact.whatsapp_number', 'page' => 'Global', 'section' => 'Contact details', 'label' => 'WhatsApp number (digits only)', 'type' => 'text', 'value' => '919690805228'],
    ['key' => 'global.contact.whatsapp_message', 'page' => 'Global', 'section' => 'Contact details', 'label' => 'WhatsApp pre-filled message', 'type' => 'text', 'value' => 'Hello Unnat Technology Services'],
    ['key' => 'global.contact.whatsapp_aria', 'page' => 'Global', 'section' => 'Contact details', 'label' => 'WhatsApp button screen-reader label', 'type' => 'text', 'value' => 'Chat with Unnat Technology Services on WhatsApp'],
    ['key' => 'global.contact.street', 'page' => 'Global', 'section' => 'Contact details', 'label' => 'Street address', 'type' => 'text', 'value' => 'Buddhi Vihar, Delhi Road'],
    ['key' => 'global.contact.city', 'page' => 'Global', 'section' => 'Contact details', 'label' => 'City', 'type' => 'text', 'value' => 'Moradabad'],
    ['key' => 'global.contact.region', 'page' => 'Global', 'section' => 'Contact details', 'label' => 'State / region', 'type' => 'text', 'value' => 'Uttar Pradesh'],
    ['key' => 'global.contact.postal_code', 'page' => 'Global', 'section' => 'Contact details', 'label' => 'Postal code', 'type' => 'text', 'value' => '244001'],
    ['key' => 'global.contact.country', 'page' => 'Global', 'section' => 'Contact details', 'label' => 'Country code', 'type' => 'text', 'value' => 'IN'],
    ['key' => 'global.contact.location_short', 'page' => 'Global', 'section' => 'Contact details', 'label' => 'Short location label', 'type' => 'text', 'value' => 'Moradabad, UP'],
    ['key' => 'global.contact.location_full', 'page' => 'Global', 'section' => 'Contact details', 'label' => 'Full location label', 'type' => 'text', 'value' => 'Moradabad, Uttar Pradesh, India'],
    ['key' => 'global.contact.map_url', 'page' => 'Global', 'section' => 'Contact details', 'label' => 'Google Maps link', 'type' => 'url', 'value' => 'https://maps.app.goo.gl/nB6VPkdqmEcwCJhh6'],

    ['key' => 'global.social.linkedin_url', 'page' => 'Global', 'section' => 'Social profiles', 'label' => 'LinkedIn URL', 'type' => 'url', 'value' => 'https://www.linkedin.com/company/unnat-technology-services'],
    ['key' => 'global.social.linkedin_label', 'page' => 'Global', 'section' => 'Social profiles', 'label' => 'LinkedIn label', 'type' => 'text', 'value' => 'LinkedIn'],
    ['key' => 'global.social.instagram_url', 'page' => 'Global', 'section' => 'Social profiles', 'label' => 'Instagram URL', 'type' => 'url', 'value' => 'https://www.instagram.com/servicesunnat/'],
    ['key' => 'global.social.instagram_label', 'page' => 'Global', 'section' => 'Social profiles', 'label' => 'Instagram label', 'type' => 'text', 'value' => 'Instagram'],
    ['key' => 'global.social.facebook_url', 'page' => 'Global', 'section' => 'Social profiles', 'label' => 'Facebook URL', 'type' => 'url', 'value' => 'https://www.facebook.com/Servicesunnat/'],
    ['key' => 'global.social.facebook_label', 'page' => 'Global', 'section' => 'Social profiles', 'label' => 'Facebook label', 'type' => 'text', 'value' => 'Facebook'],

    /* ---------------------------------------------------------------------
     * HEADER
     * ------------------------------------------------------------------ */
    ['key' => 'header.skip_link.label', 'page' => 'Header', 'section' => 'Accessibility', 'label' => 'Skip-to-content link text', 'type' => 'text', 'value' => 'Skip to main content'],
    ['key' => 'header.brand.aria_label', 'page' => 'Header', 'section' => 'Brand', 'label' => 'Logo link screen-reader label', 'type' => 'text', 'value' => 'Unnat Technology Services home'],
    ['key' => 'header.brand.text', 'page' => 'Header', 'section' => 'Brand', 'label' => 'Brand text beside the logo', 'type' => 'text', 'value' => 'Unnat Technology Services'],
    ['key' => 'header.nav.aria_label', 'page' => 'Header', 'section' => 'Navigation', 'label' => 'Navigation screen-reader label', 'type' => 'text', 'value' => 'Primary navigation'],
    ['key' => 'header.nav.toggle_label', 'page' => 'Header', 'section' => 'Navigation', 'label' => 'Mobile menu button label', 'type' => 'text', 'value' => 'Open navigation'],
    ['key' => 'header.nav.header_aria', 'page' => 'Header', 'section' => 'Navigation', 'label' => 'Header landmark label', 'type' => 'text', 'value' => 'Main header'],

    /* ---------------------------------------------------------------------
     * HOME PAGE — hero
     * ------------------------------------------------------------------ */
    ['key' => 'home.hero.badge', 'page' => 'Home page', 'section' => 'Hero', 'label' => 'Badge above the headline', 'type' => 'text', 'value' => 'Technology built around your next move'],
    ['key' => 'home.hero.headline_prefix', 'page' => 'Home page', 'section' => 'Hero', 'label' => 'Headline — first part', 'type' => 'text', 'value' => 'Building smart, scalable &'],
    ['key' => 'home.hero.headline_highlight', 'page' => 'Home page', 'section' => 'Hero', 'label' => 'Headline — highlighted words', 'type' => 'text', 'value' => 'future-ready'],
    ['key' => 'home.hero.headline_suffix', 'page' => 'Home page', 'section' => 'Hero', 'label' => 'Headline — last part', 'type' => 'text', 'value' => 'digital solutions'],
    ['key' => 'home.hero.headline_aria', 'page' => 'Home page', 'section' => 'Hero', 'label' => 'Headline screen-reader text', 'type' => 'text', 'value' => 'Building smart, scalable and future-ready digital solutions'],
    ['key' => 'home.hero.copy', 'page' => 'Home page', 'section' => 'Hero', 'label' => 'Intro paragraph', 'type' => 'textarea', 'value' => 'Unnat Technology Services turns ideas into dependable web platforms, business software and automation systems—designed to create clarity, efficiency and lasting growth.'],
    ['key' => 'home.hero.primary_cta_label', 'page' => 'Home page', 'section' => 'Hero', 'label' => 'Primary button text', 'type' => 'text', 'value' => 'Explore services'],
    ['key' => 'home.hero.primary_cta_url', 'page' => 'Home page', 'section' => 'Hero', 'label' => 'Primary button link', 'type' => 'url', 'value' => '#services'],
    ['key' => 'home.hero.secondary_cta_label', 'page' => 'Home page', 'section' => 'Hero', 'label' => 'Secondary button text', 'type' => 'text', 'value' => 'Start a project'],
    ['key' => 'home.hero.secondary_cta_url', 'page' => 'Home page', 'section' => 'Hero', 'label' => 'Secondary button link', 'type' => 'url', 'value' => '#contact'],
    ['key' => 'home.hero.proof_1', 'page' => 'Home page', 'section' => 'Hero', 'label' => 'Proof point 1', 'type' => 'text', 'value' => 'Business-first thinking'],
    ['key' => 'home.hero.proof_2', 'page' => 'Home page', 'section' => 'Hero', 'label' => 'Proof point 2', 'type' => 'text', 'value' => 'Scalable engineering'],
    ['key' => 'home.hero.proof_3', 'page' => 'Home page', 'section' => 'Hero', 'label' => 'Proof point 3', 'type' => 'text', 'value' => 'Clear delivery'],
    ['key' => 'home.hero.chip_1_symbol', 'page' => 'Home page', 'section' => 'Hero', 'label' => 'Floating chip 1 — symbol', 'type' => 'text', 'value' => 'AI'],
    ['key' => 'home.hero.chip_1_text', 'page' => 'Home page', 'section' => 'Hero', 'label' => 'Floating chip 1 — text', 'type' => 'text', 'value' => 'Intelligent automation'],
    ['key' => 'home.hero.chip_2_symbol', 'page' => 'Home page', 'section' => 'Hero', 'label' => 'Floating chip 2 — symbol', 'type' => 'text', 'value' => '01'],
    ['key' => 'home.hero.chip_2_text', 'page' => 'Home page', 'section' => 'Hero', 'label' => 'Floating chip 2 — text', 'type' => 'text', 'value' => 'Reliable platforms'],
    ['key' => 'home.hero.chip_3_symbol', 'page' => 'Home page', 'section' => 'Hero', 'label' => 'Floating chip 3 — symbol', 'type' => 'text', 'value' => '↗'],
    ['key' => 'home.hero.chip_3_text', 'page' => 'Home page', 'section' => 'Hero', 'label' => 'Floating chip 3 — text', 'type' => 'text', 'value' => 'Built to scale'],
    ['key' => 'home.hero.scroll_cue_label', 'page' => 'Home page', 'section' => 'Hero', 'label' => 'Scroll cue text', 'type' => 'text', 'value' => 'Scroll to explore'],
    ['key' => 'home.hero.scroll_cue_url', 'page' => 'Home page', 'section' => 'Hero', 'label' => 'Scroll cue link', 'type' => 'url', 'value' => '#introduction'],

    /* HOME PAGE — trust strip */
    ['key' => 'home.trust_strip.aria', 'page' => 'Home page', 'section' => 'Trust strip', 'label' => 'Strip screen-reader label', 'type' => 'text', 'value' => 'Industries served'],
    ['key' => 'home.trust_strip.title', 'page' => 'Home page', 'section' => 'Trust strip', 'label' => 'Strip heading', 'type' => 'text', 'value' => 'Digital capability across real-world sectors'],
    ['key' => 'home.trust_strip.item_1', 'page' => 'Home page', 'section' => 'Trust strip', 'label' => 'Sector 1', 'type' => 'text', 'value' => 'Education'],
    ['key' => 'home.trust_strip.item_2', 'page' => 'Home page', 'section' => 'Trust strip', 'label' => 'Sector 2', 'type' => 'text', 'value' => 'Public sector'],
    ['key' => 'home.trust_strip.item_3', 'page' => 'Home page', 'section' => 'Trust strip', 'label' => 'Sector 3', 'type' => 'text', 'value' => 'Retail'],
    ['key' => 'home.trust_strip.item_4', 'page' => 'Home page', 'section' => 'Trust strip', 'label' => 'Sector 4', 'type' => 'text', 'value' => 'Healthcare'],
    ['key' => 'home.trust_strip.item_5', 'page' => 'Home page', 'section' => 'Trust strip', 'label' => 'Sector 5', 'type' => 'text', 'value' => 'Professional services'],

    /* HOME PAGE — introduction */
    ['key' => 'home.intro.eyebrow', 'page' => 'Home page', 'section' => 'Introduction', 'label' => 'Eyebrow', 'type' => 'text', 'value' => 'Who we are'],
    ['key' => 'home.intro.title', 'page' => 'Home page', 'section' => 'Introduction', 'label' => 'Heading', 'type' => 'text', 'value' => 'Technology that moves business forward.'],
    ['key' => 'home.intro.copy', 'page' => 'Home page', 'section' => 'Introduction', 'label' => 'Paragraph', 'type' => 'textarea', 'value' => 'We are an India-based technology partner helping organizations simplify complexity and build digital systems that work beautifully today—and remain useful tomorrow.'],
    ['key' => 'home.intro.cta_label', 'page' => 'Home page', 'section' => 'Introduction', 'label' => 'Button text', 'type' => 'text', 'value' => 'How we work'],
    ['key' => 'home.intro.cta_url', 'page' => 'Home page', 'section' => 'Introduction', 'label' => 'Button link', 'type' => 'url', 'value' => '#process'],
    ['key' => 'home.intro.card_1_index', 'page' => 'Home page', 'section' => 'Introduction', 'label' => 'Value card 1 — number', 'type' => 'text', 'value' => '01'],
    ['key' => 'home.intro.card_1_title', 'page' => 'Home page', 'section' => 'Introduction', 'label' => 'Value card 1 — title', 'type' => 'text', 'value' => 'Strategy before screens'],
    ['key' => 'home.intro.card_1_copy', 'page' => 'Home page', 'section' => 'Introduction', 'label' => 'Value card 1 — copy', 'type' => 'textarea', 'value' => 'We define the user need, business outcome and technical path before writing production code.'],
    ['key' => 'home.intro.card_2_index', 'page' => 'Home page', 'section' => 'Introduction', 'label' => 'Value card 2 — number', 'type' => 'text', 'value' => '02'],
    ['key' => 'home.intro.card_2_title', 'page' => 'Home page', 'section' => 'Introduction', 'label' => 'Value card 2 — title', 'type' => 'text', 'value' => 'Built for real operations'],
    ['key' => 'home.intro.card_2_copy', 'page' => 'Home page', 'section' => 'Introduction', 'label' => 'Value card 2 — copy', 'type' => 'textarea', 'value' => 'Every solution is shaped around the teams, workflows and constraints that make your business unique.'],
    ['key' => 'home.intro.card_3_index', 'page' => 'Home page', 'section' => 'Introduction', 'label' => 'Value card 3 — number', 'type' => 'text', 'value' => '03'],
    ['key' => 'home.intro.card_3_title', 'page' => 'Home page', 'section' => 'Introduction', 'label' => 'Value card 3 — title', 'type' => 'text', 'value' => 'Ready for what comes next'],
    ['key' => 'home.intro.card_3_copy', 'page' => 'Home page', 'section' => 'Introduction', 'label' => 'Value card 3 — copy', 'type' => 'textarea', 'value' => 'Clean architecture and thoughtful foundations make products easier to evolve, secure and scale.'],

    /* HOME PAGE — services */
    ['key' => 'home.services.eyebrow', 'page' => 'Home page', 'section' => 'Services', 'label' => 'Eyebrow', 'type' => 'text', 'value' => 'What we build'],
    ['key' => 'home.services.title', 'page' => 'Home page', 'section' => 'Services', 'label' => 'Heading', 'type' => 'text', 'value' => 'End-to-end digital services, one accountable partner.'],
    ['key' => 'home.services.copy', 'page' => 'Home page', 'section' => 'Services', 'label' => 'Paragraph', 'type' => 'textarea', 'value' => 'From a focused first release to a connected business platform, we design and engineer the right level of technology for your goals.'],
    ['key' => 'home.services.card_1_icon', 'page' => 'Home page', 'section' => 'Services', 'label' => 'Service 1 — icon', 'type' => 'text', 'value' => '⌘'],
    ['key' => 'home.services.card_1_title', 'page' => 'Home page', 'section' => 'Services', 'label' => 'Service 1 — title', 'type' => 'text', 'value' => 'Web platforms & portals'],
    ['key' => 'home.services.card_1_copy', 'page' => 'Home page', 'section' => 'Services', 'label' => 'Service 1 — copy', 'type' => 'textarea', 'value' => 'Fast, accessible websites and secure portals built around meaningful customer and team journeys.'],
    ['key' => 'home.services.card_1_link_label', 'page' => 'Home page', 'section' => 'Services', 'label' => 'Service 1 — link text', 'type' => 'text', 'value' => 'Plan a platform'],
    ['key' => 'home.services.card_1_link_url', 'page' => 'Home page', 'section' => 'Services', 'label' => 'Service 1 — link URL', 'type' => 'url', 'value' => '#contact'],
    ['key' => 'home.services.card_2_icon', 'page' => 'Home page', 'section' => 'Services', 'label' => 'Service 2 — icon', 'type' => 'text', 'value' => '◆'],
    ['key' => 'home.services.card_2_title', 'page' => 'Home page', 'section' => 'Services', 'label' => 'Service 2 — title', 'type' => 'text', 'value' => 'Business software'],
    ['key' => 'home.services.card_2_copy', 'page' => 'Home page', 'section' => 'Services', 'label' => 'Service 2 — copy', 'type' => 'textarea', 'value' => 'Purpose-built tools that replace fragmented workflows.'],
    ['key' => 'home.services.card_2_link_label', 'page' => 'Home page', 'section' => 'Services', 'label' => 'Service 2 — link text', 'type' => 'text', 'value' => 'Discuss your workflow'],
    ['key' => 'home.services.card_2_link_url', 'page' => 'Home page', 'section' => 'Services', 'label' => 'Service 2 — link URL', 'type' => 'url', 'value' => '#contact'],
    ['key' => 'home.services.card_3_icon', 'page' => 'Home page', 'section' => 'Services', 'label' => 'Service 3 — icon', 'type' => 'text', 'value' => '∞'],
    ['key' => 'home.services.card_3_title', 'page' => 'Home page', 'section' => 'Services', 'label' => 'Service 3 — title', 'type' => 'text', 'value' => 'Automation'],
    ['key' => 'home.services.card_3_copy', 'page' => 'Home page', 'section' => 'Services', 'label' => 'Service 3 — copy', 'type' => 'textarea', 'value' => 'Connected processes that reduce repetitive work and delays.'],
    ['key' => 'home.services.card_3_link_label', 'page' => 'Home page', 'section' => 'Services', 'label' => 'Service 3 — link text', 'type' => 'text', 'value' => 'Find efficiencies'],
    ['key' => 'home.services.card_3_link_url', 'page' => 'Home page', 'section' => 'Services', 'label' => 'Service 3 — link URL', 'type' => 'url', 'value' => '#contact'],
    ['key' => 'home.services.card_4_icon', 'page' => 'Home page', 'section' => 'Services', 'label' => 'Service 4 — icon', 'type' => 'text', 'value' => 'AI'],
    ['key' => 'home.services.card_4_title', 'page' => 'Home page', 'section' => 'Services', 'label' => 'Service 4 — title', 'type' => 'text', 'value' => 'AI solutions'],
    ['key' => 'home.services.card_4_copy', 'page' => 'Home page', 'section' => 'Services', 'label' => 'Service 4 — copy', 'type' => 'textarea', 'value' => 'Practical intelligence embedded where it creates value.'],
    ['key' => 'home.services.card_4_link_label', 'page' => 'Home page', 'section' => 'Services', 'label' => 'Service 4 — link text', 'type' => 'text', 'value' => 'Explore AI use cases'],
    ['key' => 'home.services.card_4_link_url', 'page' => 'Home page', 'section' => 'Services', 'label' => 'Service 4 — link URL', 'type' => 'url', 'value' => '#contact'],
    ['key' => 'home.services.card_5_icon', 'page' => 'Home page', 'section' => 'Services', 'label' => 'Service 5 — icon', 'type' => 'text', 'value' => '↗'],
    ['key' => 'home.services.card_5_title', 'page' => 'Home page', 'section' => 'Services', 'label' => 'Service 5 — title', 'type' => 'text', 'value' => 'Digital product engineering'],
    ['key' => 'home.services.card_5_copy', 'page' => 'Home page', 'section' => 'Services', 'label' => 'Service 5 — copy', 'type' => 'textarea', 'value' => 'Research, UX, architecture and product development brought together to move from idea to reliable release.'],
    ['key' => 'home.services.card_5_link_label', 'page' => 'Home page', 'section' => 'Services', 'label' => 'Service 5 — link text', 'type' => 'text', 'value' => 'Shape your product'],
    ['key' => 'home.services.card_5_link_url', 'page' => 'Home page', 'section' => 'Services', 'label' => 'Service 5 — link URL', 'type' => 'url', 'value' => '#contact'],

    /* HOME PAGE — expertise */
    ['key' => 'home.expertise.eyebrow', 'page' => 'Home page', 'section' => 'Expertise', 'label' => 'Eyebrow', 'type' => 'text', 'value' => 'Technology expertise'],
    ['key' => 'home.expertise.title', 'page' => 'Home page', 'section' => 'Expertise', 'label' => 'Heading', 'type' => 'text', 'value' => 'Modern foundations. Sensible choices.'],
    ['key' => 'home.expertise.copy', 'page' => 'Home page', 'section' => 'Expertise', 'label' => 'Paragraph', 'type' => 'textarea', 'value' => 'We choose technology for maintainability, performance and business fit—not for novelty.'],
    ['key' => 'home.expertise.card_1_tag', 'page' => 'Home page', 'section' => 'Expertise', 'label' => 'Expertise 1 — tag', 'type' => 'text', 'value' => 'FRONTEND'],
    ['key' => 'home.expertise.card_1_title', 'page' => 'Home page', 'section' => 'Expertise', 'label' => 'Expertise 1 — title', 'type' => 'text', 'value' => 'Responsive experiences'],
    ['key' => 'home.expertise.card_1_copy', 'page' => 'Home page', 'section' => 'Expertise', 'label' => 'Expertise 1 — copy', 'type' => 'textarea', 'value' => 'Modern interfaces designed for speed, clarity and accessibility.'],
    ['key' => 'home.expertise.card_2_tag', 'page' => 'Home page', 'section' => 'Expertise', 'label' => 'Expertise 2 — tag', 'type' => 'text', 'value' => 'BACKEND'],
    ['key' => 'home.expertise.card_2_title', 'page' => 'Home page', 'section' => 'Expertise', 'label' => 'Expertise 2 — title', 'type' => 'text', 'value' => 'Robust applications'],
    ['key' => 'home.expertise.card_2_copy', 'page' => 'Home page', 'section' => 'Expertise', 'label' => 'Expertise 2 — copy', 'type' => 'textarea', 'value' => 'Secure data flows, APIs and services built for dependable operations.'],
    ['key' => 'home.expertise.card_3_tag', 'page' => 'Home page', 'section' => 'Expertise', 'label' => 'Expertise 3 — tag', 'type' => 'text', 'value' => 'CLOUD'],
    ['key' => 'home.expertise.card_3_title', 'page' => 'Home page', 'section' => 'Expertise', 'label' => 'Expertise 3 — title', 'type' => 'text', 'value' => 'Scalable delivery'],
    ['key' => 'home.expertise.card_3_copy', 'page' => 'Home page', 'section' => 'Expertise', 'label' => 'Expertise 3 — copy', 'type' => 'textarea', 'value' => 'Deployment foundations that support reliability and sustainable growth.'],
    ['key' => 'home.expertise.card_4_tag', 'page' => 'Home page', 'section' => 'Expertise', 'label' => 'Expertise 4 — tag', 'type' => 'text', 'value' => 'INTELLIGENCE'],
    ['key' => 'home.expertise.card_4_title', 'page' => 'Home page', 'section' => 'Expertise', 'label' => 'Expertise 4 — title', 'type' => 'text', 'value' => 'Useful automation'],
    ['key' => 'home.expertise.card_4_copy', 'page' => 'Home page', 'section' => 'Expertise', 'label' => 'Expertise 4 — copy', 'type' => 'textarea', 'value' => 'AI and workflow automation applied to clear, measurable opportunities.'],

    /* HOME PAGE — why unnat */
    ['key' => 'home.why.eyebrow', 'page' => 'Home page', 'section' => 'Why Unnat', 'label' => 'Eyebrow', 'type' => 'text', 'value' => 'Why Unnat'],
    ['key' => 'home.why.title', 'page' => 'Home page', 'section' => 'Why Unnat', 'label' => 'Heading', 'type' => 'text', 'value' => 'A partnership designed for confidence.'],
    ['key' => 'home.why.copy', 'page' => 'Home page', 'section' => 'Why Unnat', 'label' => 'Paragraph', 'type' => 'textarea', 'value' => 'Clear decisions, visible progress and technology you can continue to own.'],
    ['key' => 'home.why.card_1_title', 'page' => 'Home page', 'section' => 'Why Unnat', 'label' => 'Reason 1 — title', 'type' => 'text', 'value' => 'Business alignment'],
    ['key' => 'home.why.card_1_copy', 'page' => 'Home page', 'section' => 'Why Unnat', 'label' => 'Reason 1 — copy', 'type' => 'textarea', 'value' => 'Every feature traces back to a user problem or business outcome.'],
    ['key' => 'home.why.card_2_title', 'page' => 'Home page', 'section' => 'Why Unnat', 'label' => 'Reason 2 — title', 'type' => 'text', 'value' => 'Transparent delivery'],
    ['key' => 'home.why.card_2_copy', 'page' => 'Home page', 'section' => 'Why Unnat', 'label' => 'Reason 2 — copy', 'type' => 'textarea', 'value' => 'Priorities, progress and trade-offs stay visible throughout the work.'],
    ['key' => 'home.why.card_3_title', 'page' => 'Home page', 'section' => 'Why Unnat', 'label' => 'Reason 3 — title', 'type' => 'text', 'value' => 'Quality engineering'],
    ['key' => 'home.why.card_3_copy', 'page' => 'Home page', 'section' => 'Why Unnat', 'label' => 'Reason 3 — copy', 'type' => 'textarea', 'value' => 'Performance, accessibility, security and maintainability are built in.'],
    ['key' => 'home.why.card_4_title', 'page' => 'Home page', 'section' => 'Why Unnat', 'label' => 'Reason 4 — title', 'type' => 'text', 'value' => 'Practical innovation'],
    ['key' => 'home.why.card_4_copy', 'page' => 'Home page', 'section' => 'Why Unnat', 'label' => 'Reason 4 — copy', 'type' => 'textarea', 'value' => 'New technology is used when it improves the result—not for decoration.'],
    ['key' => 'home.why.card_5_title', 'page' => 'Home page', 'section' => 'Why Unnat', 'label' => 'Reason 5 — title', 'type' => 'text', 'value' => 'Flexible engagement'],
    ['key' => 'home.why.card_5_copy', 'page' => 'Home page', 'section' => 'Why Unnat', 'label' => 'Reason 5 — copy', 'type' => 'textarea', 'value' => 'Focused delivery that adapts to the stage and scope of your initiative.'],
    ['key' => 'home.why.card_6_title', 'page' => 'Home page', 'section' => 'Why Unnat', 'label' => 'Reason 6 — title', 'type' => 'text', 'value' => 'Long-term thinking'],
    ['key' => 'home.why.card_6_copy', 'page' => 'Home page', 'section' => 'Why Unnat', 'label' => 'Reason 6 — copy', 'type' => 'textarea', 'value' => 'Solutions are structured to support iteration, integration and growth.'],

    /* HOME PAGE — process */
    ['key' => 'home.process.eyebrow', 'page' => 'Home page', 'section' => 'Process', 'label' => 'Eyebrow', 'type' => 'text', 'value' => 'How we work'],
    ['key' => 'home.process.title', 'page' => 'Home page', 'section' => 'Process', 'label' => 'Heading', 'type' => 'text', 'value' => 'From ambiguity to a confident launch.'],
    ['key' => 'home.process.copy', 'page' => 'Home page', 'section' => 'Process', 'label' => 'Paragraph', 'type' => 'textarea', 'value' => 'A lean, collaborative process keeps decisions grounded and momentum visible.'],
    ['key' => 'home.process.step_1_number', 'page' => 'Home page', 'section' => 'Process', 'label' => 'Step 1 — number', 'type' => 'text', 'value' => '01'],
    ['key' => 'home.process.step_1_title', 'page' => 'Home page', 'section' => 'Process', 'label' => 'Step 1 — title', 'type' => 'text', 'value' => 'Discover'],
    ['key' => 'home.process.step_1_copy', 'page' => 'Home page', 'section' => 'Process', 'label' => 'Step 1 — copy', 'type' => 'textarea', 'value' => 'Understand goals, users, constraints and the strongest opportunity.'],
    ['key' => 'home.process.step_2_number', 'page' => 'Home page', 'section' => 'Process', 'label' => 'Step 2 — number', 'type' => 'text', 'value' => '02'],
    ['key' => 'home.process.step_2_title', 'page' => 'Home page', 'section' => 'Process', 'label' => 'Step 2 — title', 'type' => 'text', 'value' => 'Define'],
    ['key' => 'home.process.step_2_copy', 'page' => 'Home page', 'section' => 'Process', 'label' => 'Step 2 — copy', 'type' => 'textarea', 'value' => 'Shape scope, architecture, success measures and a practical roadmap.'],
    ['key' => 'home.process.step_3_number', 'page' => 'Home page', 'section' => 'Process', 'label' => 'Step 3 — number', 'type' => 'text', 'value' => '03'],
    ['key' => 'home.process.step_3_title', 'page' => 'Home page', 'section' => 'Process', 'label' => 'Step 3 — title', 'type' => 'text', 'value' => 'Design'],
    ['key' => 'home.process.step_3_copy', 'page' => 'Home page', 'section' => 'Process', 'label' => 'Step 3 — copy', 'type' => 'textarea', 'value' => 'Turn complex requirements into clear, intuitive product experiences.'],
    ['key' => 'home.process.step_4_number', 'page' => 'Home page', 'section' => 'Process', 'label' => 'Step 4 — number', 'type' => 'text', 'value' => '04'],
    ['key' => 'home.process.step_4_title', 'page' => 'Home page', 'section' => 'Process', 'label' => 'Step 4 — title', 'type' => 'text', 'value' => 'Build'],
    ['key' => 'home.process.step_4_copy', 'page' => 'Home page', 'section' => 'Process', 'label' => 'Step 4 — copy', 'type' => 'textarea', 'value' => 'Develop in focused increments with continuous review and quality checks.'],
    ['key' => 'home.process.step_5_number', 'page' => 'Home page', 'section' => 'Process', 'label' => 'Step 5 — number', 'type' => 'text', 'value' => '05'],
    ['key' => 'home.process.step_5_title', 'page' => 'Home page', 'section' => 'Process', 'label' => 'Step 5 — title', 'type' => 'text', 'value' => 'Launch & evolve'],
    ['key' => 'home.process.step_5_copy', 'page' => 'Home page', 'section' => 'Process', 'label' => 'Step 5 — copy', 'type' => 'textarea', 'value' => 'Release confidently, learn from real use and improve with purpose.'],

    /* HOME PAGE — selected work */
    ['key' => 'home.work.eyebrow', 'page' => 'Home page', 'section' => 'Selected work', 'label' => 'Eyebrow', 'type' => 'text', 'value' => 'Selected capabilities'],
    ['key' => 'home.work.title', 'page' => 'Home page', 'section' => 'Selected work', 'label' => 'Heading', 'type' => 'text', 'value' => 'Solutions grounded in real operations.'],
    ['key' => 'home.work.copy', 'page' => 'Home page', 'section' => 'Selected work', 'label' => 'Paragraph', 'type' => 'textarea', 'value' => 'Representative areas where our product and engineering approach creates value.'],
    ['key' => 'home.work.card_1_tag', 'page' => 'Home page', 'section' => 'Selected work', 'label' => 'Case 1 — tag', 'type' => 'text', 'value' => 'Operations platform'],
    ['key' => 'home.work.card_1_title', 'page' => 'Home page', 'section' => 'Selected work', 'label' => 'Case 1 — title', 'type' => 'text', 'value' => 'Connected municipal workflows'],
    ['key' => 'home.work.card_1_copy', 'page' => 'Home page', 'section' => 'Selected work', 'label' => 'Case 1 — copy', 'type' => 'textarea', 'value' => 'A unified digital direction for public-service coordination, information access and operational visibility.'],
    ['key' => 'home.work.card_2_tag', 'page' => 'Home page', 'section' => 'Selected work', 'label' => 'Case 2 — tag', 'type' => 'text', 'value' => 'Learning technology'],
    ['key' => 'home.work.card_2_title', 'page' => 'Home page', 'section' => 'Selected work', 'label' => 'Case 2 — title', 'type' => 'text', 'value' => 'Digital learning experiences'],
    ['key' => 'home.work.card_2_copy', 'page' => 'Home page', 'section' => 'Selected work', 'label' => 'Case 2 — copy', 'type' => 'textarea', 'value' => 'Structured learning journeys and responsive course delivery for growing skill ecosystems.'],
    ['key' => 'home.work.card_3_tag', 'page' => 'Home page', 'section' => 'Selected work', 'label' => 'Case 3 — tag', 'type' => 'text', 'value' => 'Business systems'],
    ['key' => 'home.work.card_3_title', 'page' => 'Home page', 'section' => 'Selected work', 'label' => 'Case 3 — title', 'type' => 'text', 'value' => 'Client & asset management'],
    ['key' => 'home.work.card_3_copy', 'page' => 'Home page', 'section' => 'Selected work', 'label' => 'Case 3 — copy', 'type' => 'textarea', 'value' => 'Purpose-built tools that bring projects, clients, assets and progress into one clear workflow.'],
    ['key' => 'home.work.cta_label', 'page' => 'Home page', 'section' => 'Selected work', 'label' => 'Button text', 'type' => 'text', 'value' => 'Explore products'],
    ['key' => 'home.work.cta_url', 'page' => 'Home page', 'section' => 'Selected work', 'label' => 'Button link', 'type' => 'url', 'value' => 'products.php'],

    /* HOME PAGE — industries */
    ['key' => 'home.industries.eyebrow', 'page' => 'Home page', 'section' => 'Industries', 'label' => 'Eyebrow', 'type' => 'text', 'value' => 'Industries'],
    ['key' => 'home.industries.title', 'page' => 'Home page', 'section' => 'Industries', 'label' => 'Heading', 'type' => 'text', 'value' => 'Domain-aware, user-centered.'],
    ['key' => 'home.industries.copy', 'page' => 'Home page', 'section' => 'Industries', 'label' => 'Paragraph', 'type' => 'textarea', 'value' => 'We learn the context behind each challenge and shape technology around how the sector actually works.'],
    ['key' => 'home.industries.card_1_symbol', 'page' => 'Home page', 'section' => 'Industries', 'label' => 'Industry 1 — symbol', 'type' => 'text', 'value' => '＋'],
    ['key' => 'home.industries.card_1_title', 'page' => 'Home page', 'section' => 'Industries', 'label' => 'Industry 1 — title', 'type' => 'text', 'value' => 'Healthcare'],
    ['key' => 'home.industries.card_1_copy', 'page' => 'Home page', 'section' => 'Industries', 'label' => 'Industry 1 — copy', 'type' => 'textarea', 'value' => 'Clearer workflows and thoughtful digital access for care-focused teams.'],
    ['key' => 'home.industries.card_2_symbol', 'page' => 'Home page', 'section' => 'Industries', 'label' => 'Industry 2 — symbol', 'type' => 'text', 'value' => '□'],
    ['key' => 'home.industries.card_2_title', 'page' => 'Home page', 'section' => 'Industries', 'label' => 'Industry 2 — title', 'type' => 'text', 'value' => 'Retail & commerce'],
    ['key' => 'home.industries.card_2_copy', 'page' => 'Home page', 'section' => 'Industries', 'label' => 'Industry 2 — copy', 'type' => 'textarea', 'value' => 'Connected experiences across discovery, operations and customer service.'],
    ['key' => 'home.industries.card_3_symbol', 'page' => 'Home page', 'section' => 'Industries', 'label' => 'Industry 3 — symbol', 'type' => 'text', 'value' => '◫'],
    ['key' => 'home.industries.card_3_title', 'page' => 'Home page', 'section' => 'Industries', 'label' => 'Industry 3 — title', 'type' => 'text', 'value' => 'Education'],
    ['key' => 'home.industries.card_3_copy', 'page' => 'Home page', 'section' => 'Industries', 'label' => 'Industry 3 — copy', 'type' => 'textarea', 'value' => 'Accessible learning platforms and tools that support skill development.'],
    ['key' => 'home.industries.card_4_symbol', 'page' => 'Home page', 'section' => 'Industries', 'label' => 'Industry 4 — symbol', 'type' => 'text', 'value' => '◎'],
    ['key' => 'home.industries.card_4_title', 'page' => 'Home page', 'section' => 'Industries', 'label' => 'Industry 4 — title', 'type' => 'text', 'value' => 'Government'],
    ['key' => 'home.industries.card_4_copy', 'page' => 'Home page', 'section' => 'Industries', 'label' => 'Industry 4 — copy', 'type' => 'textarea', 'value' => 'Reliable public-facing services and more visible internal processes.'],
    ['key' => 'home.industries.card_5_symbol', 'page' => 'Home page', 'section' => 'Industries', 'label' => 'Industry 5 — symbol', 'type' => 'text', 'value' => '↗'],
    ['key' => 'home.industries.card_5_title', 'page' => 'Home page', 'section' => 'Industries', 'label' => 'Industry 5 — title', 'type' => 'text', 'value' => 'Professional services'],
    ['key' => 'home.industries.card_5_copy', 'page' => 'Home page', 'section' => 'Industries', 'label' => 'Industry 5 — copy', 'type' => 'textarea', 'value' => 'Systems that improve delivery, client coordination and insight.'],
    ['key' => 'home.industries.card_6_symbol', 'page' => 'Home page', 'section' => 'Industries', 'label' => 'Industry 6 — symbol', 'type' => 'text', 'value' => '◇'],
    ['key' => 'home.industries.card_6_title', 'page' => 'Home page', 'section' => 'Industries', 'label' => 'Industry 6 — title', 'type' => 'text', 'value' => 'Emerging ventures'],
    ['key' => 'home.industries.card_6_copy', 'page' => 'Home page', 'section' => 'Industries', 'label' => 'Industry 6 — copy', 'type' => 'textarea', 'value' => 'Focused product foundations for ideas moving toward the market.'],

    /* HOME PAGE — stats */
    ['key' => 'home.stats.aria', 'page' => 'Home page', 'section' => 'Statistics', 'label' => 'Section screen-reader label', 'type' => 'text', 'value' => 'Delivery at a glance'],
    ['key' => 'home.stats.stat_1_number', 'page' => 'Home page', 'section' => 'Statistics', 'label' => 'Stat 1 — number', 'type' => 'number', 'value' => '5'],
    ['key' => 'home.stats.stat_1_suffix', 'page' => 'Home page', 'section' => 'Statistics', 'label' => 'Stat 1 — suffix', 'type' => 'text', 'value' => '+'],
    ['key' => 'home.stats.stat_1_label', 'page' => 'Home page', 'section' => 'Statistics', 'label' => 'Stat 1 — label', 'type' => 'text', 'value' => 'Core service capabilities'],
    ['key' => 'home.stats.stat_2_number', 'page' => 'Home page', 'section' => 'Statistics', 'label' => 'Stat 2 — number', 'type' => 'number', 'value' => '6'],
    ['key' => 'home.stats.stat_2_suffix', 'page' => 'Home page', 'section' => 'Statistics', 'label' => 'Stat 2 — suffix', 'type' => 'text', 'value' => '+'],
    ['key' => 'home.stats.stat_2_label', 'page' => 'Home page', 'section' => 'Statistics', 'label' => 'Stat 2 — label', 'type' => 'text', 'value' => 'Industry contexts'],
    ['key' => 'home.stats.stat_3_number', 'page' => 'Home page', 'section' => 'Statistics', 'label' => 'Stat 3 — number', 'type' => 'number', 'value' => '5'],
    ['key' => 'home.stats.stat_3_suffix', 'page' => 'Home page', 'section' => 'Statistics', 'label' => 'Stat 3 — suffix', 'type' => 'text', 'value' => ''],
    ['key' => 'home.stats.stat_3_label', 'page' => 'Home page', 'section' => 'Statistics', 'label' => 'Stat 3 — label', 'type' => 'text', 'value' => 'Delivery stages'],
    ['key' => 'home.stats.stat_4_number', 'page' => 'Home page', 'section' => 'Statistics', 'label' => 'Stat 4 — number', 'type' => 'number', 'value' => '1'],
    ['key' => 'home.stats.stat_4_suffix', 'page' => 'Home page', 'section' => 'Statistics', 'label' => 'Stat 4 — suffix', 'type' => 'text', 'value' => ''],
    ['key' => 'home.stats.stat_4_label', 'page' => 'Home page', 'section' => 'Statistics', 'label' => 'Stat 4 — label', 'type' => 'text', 'value' => 'Accountable partner'],

    /* HOME PAGE — trust */
    ['key' => 'home.trust.eyebrow', 'page' => 'Home page', 'section' => 'Built for trust', 'label' => 'Eyebrow', 'type' => 'text', 'value' => 'Built for trust'],
    ['key' => 'home.trust.title', 'page' => 'Home page', 'section' => 'Built for trust', 'label' => 'Heading', 'type' => 'text', 'value' => 'What every client should expect.'],
    ['key' => 'home.trust.copy', 'page' => 'Home page', 'section' => 'Built for trust', 'label' => 'Paragraph', 'type' => 'textarea', 'value' => 'We do not publish invented testimonials. We earn trust through the way the work is run.'],
    ['key' => 'home.trust.card_1_title', 'page' => 'Home page', 'section' => 'Built for trust', 'label' => 'Promise 1 — title', 'type' => 'text', 'value' => 'Clarity'],
    ['key' => 'home.trust.card_1_copy', 'page' => 'Home page', 'section' => 'Built for trust', 'label' => 'Promise 1 — copy', 'type' => 'textarea', 'value' => 'Plain-language decisions, practical priorities and no unnecessary complexity.'],
    ['key' => 'home.trust.card_2_title', 'page' => 'Home page', 'section' => 'Built for trust', 'label' => 'Promise 2 — title', 'type' => 'text', 'value' => 'Ownership'],
    ['key' => 'home.trust.card_2_copy', 'page' => 'Home page', 'section' => 'Built for trust', 'label' => 'Promise 2 — copy', 'type' => 'textarea', 'value' => 'A team that treats product quality and business outcomes as shared responsibilities.'],
    ['key' => 'home.trust.card_3_title', 'page' => 'Home page', 'section' => 'Built for trust', 'label' => 'Promise 3 — title', 'type' => 'text', 'value' => 'Continuity'],
    ['key' => 'home.trust.card_3_copy', 'page' => 'Home page', 'section' => 'Built for trust', 'label' => 'Promise 3 — copy', 'type' => 'textarea', 'value' => 'Maintainable foundations, useful documentation and support beyond release.'],

    /* HOME PAGE — closing call to action */
    ['key' => 'home.cta.title', 'page' => 'Home page', 'section' => 'Closing call to action', 'label' => 'Heading', 'type' => 'text', 'value' => 'Have a technology challenge worth solving?'],
    ['key' => 'home.cta.copy', 'page' => 'Home page', 'section' => 'Closing call to action', 'label' => 'Paragraph', 'type' => 'textarea', 'value' => 'Bring us the goal, the bottleneck or the early idea. We’ll help turn it into a practical next step.'],
    ['key' => 'home.cta.button_label', 'page' => 'Home page', 'section' => 'Closing call to action', 'label' => 'Button text', 'type' => 'text', 'value' => 'Start the conversation'],
    ['key' => 'home.cta.button_url', 'page' => 'Home page', 'section' => 'Closing call to action', 'label' => 'Button link', 'type' => 'url', 'value' => '#contact'],

    /* HOME PAGE — contact block */
    ['key' => 'home.contact.eyebrow', 'page' => 'Home page', 'section' => 'Contact block', 'label' => 'Eyebrow', 'type' => 'text', 'value' => 'Start a project'],
    ['key' => 'home.contact.title', 'page' => 'Home page', 'section' => 'Contact block', 'label' => 'Heading', 'type' => 'text', 'value' => 'Let’s build what’s next.'],
    ['key' => 'home.contact.copy', 'page' => 'Home page', 'section' => 'Contact block', 'label' => 'Paragraph', 'type' => 'textarea', 'value' => 'Tell us what you want to improve or create. We’ll review your inquiry and respond with the most useful next step.'],

    /* ---------------------------------------------------------------------
     * SHARED — contact detail cards and the inquiry form
     * ------------------------------------------------------------------ */
    ['key' => 'shared.contact_cards.call_icon', 'page' => 'Shared blocks', 'section' => 'Contact cards', 'label' => 'Call card — icon', 'type' => 'text', 'value' => '☎'],
    ['key' => 'shared.contact_cards.call_title', 'page' => 'Shared blocks', 'section' => 'Contact cards', 'label' => 'Call card — title', 'type' => 'text', 'value' => 'Call us'],
    ['key' => 'shared.contact_cards.email_icon', 'page' => 'Shared blocks', 'section' => 'Contact cards', 'label' => 'Email card — icon', 'type' => 'text', 'value' => '@'],
    ['key' => 'shared.contact_cards.email_title', 'page' => 'Shared blocks', 'section' => 'Contact cards', 'label' => 'Email card — title', 'type' => 'text', 'value' => 'Email'],
    ['key' => 'shared.contact_cards.visit_icon', 'page' => 'Shared blocks', 'section' => 'Contact cards', 'label' => 'Visit card — icon', 'type' => 'text', 'value' => '⌖'],
    ['key' => 'shared.contact_cards.visit_title', 'page' => 'Shared blocks', 'section' => 'Contact cards', 'label' => 'Visit card — title', 'type' => 'text', 'value' => 'Visit'],

    ['key' => 'shared.inquiry_form.name_label', 'page' => 'Shared blocks', 'section' => 'Inquiry form', 'label' => 'Name field label', 'type' => 'text', 'value' => 'Name *'],
    ['key' => 'shared.inquiry_form.phone_label', 'page' => 'Shared blocks', 'section' => 'Inquiry form', 'label' => 'Phone field label', 'type' => 'text', 'value' => 'Phone *'],
    ['key' => 'shared.inquiry_form.email_label', 'page' => 'Shared blocks', 'section' => 'Inquiry form', 'label' => 'Email field label', 'type' => 'text', 'value' => 'Email'],
    ['key' => 'shared.inquiry_form.question_label', 'page' => 'Shared blocks', 'section' => 'Inquiry form', 'label' => 'Question field label', 'type' => 'text', 'value' => 'What would you like to build or improve? *'],
    ['key' => 'shared.inquiry_form.details_label', 'page' => 'Shared blocks', 'section' => 'Inquiry form', 'label' => 'Details field label', 'type' => 'text', 'value' => 'Helpful context'],
    ['key' => 'shared.inquiry_form.details_placeholder', 'page' => 'Shared blocks', 'section' => 'Inquiry form', 'label' => 'Details field placeholder', 'type' => 'text', 'value' => 'Goals, users, timeline or anything else that will help us understand the opportunity.'],
    ['key' => 'shared.inquiry_form.consent_note', 'page' => 'Shared blocks', 'section' => 'Inquiry form', 'label' => 'Consent note below the form', 'type' => 'textarea', 'value' => 'By submitting, you agree that Unnat Technology Services may contact you about this inquiry.'],
    ['key' => 'shared.inquiry_form.submit_label', 'page' => 'Shared blocks', 'section' => 'Inquiry form', 'label' => 'Submit button text', 'type' => 'text', 'value' => 'Send inquiry'],

    /* ---------------------------------------------------------------------
     * CONTACT PAGE
     * ------------------------------------------------------------------ */
    ['key' => 'contact.hero.eyebrow', 'page' => 'Contact page', 'section' => 'Hero', 'label' => 'Eyebrow', 'type' => 'text', 'value' => 'Start a project'],
    ['key' => 'contact.hero.title_prefix', 'page' => 'Contact page', 'section' => 'Hero', 'label' => 'Heading — first part', 'type' => 'text', 'value' => 'Bring us the challenge.'],
    ['key' => 'contact.hero.title_highlight', 'page' => 'Contact page', 'section' => 'Hero', 'label' => 'Heading — highlighted part', 'type' => 'text', 'value' => 'We’ll shape the next step.'],
    ['key' => 'contact.hero.copy', 'page' => 'Contact page', 'section' => 'Hero', 'label' => 'Paragraph', 'type' => 'textarea', 'value' => 'Whether you have a detailed brief or an early idea, tell us what needs to change. We’ll respond with practical questions and a clear way forward.'],
    ['key' => 'contact.form.eyebrow', 'page' => 'Contact page', 'section' => 'Form block', 'label' => 'Eyebrow', 'type' => 'text', 'value' => 'Contact UTS'],
    ['key' => 'contact.form.title', 'page' => 'Contact page', 'section' => 'Form block', 'label' => 'Heading', 'type' => 'text', 'value' => 'Let’s create something useful.'],
    ['key' => 'contact.form.copy', 'page' => 'Contact page', 'section' => 'Form block', 'label' => 'Paragraph', 'type' => 'textarea', 'value' => 'Share the business goal, workflow problem or product idea. You do not need to have every detail figured out.'],
    ['key' => 'contact.next.eyebrow', 'page' => 'Contact page', 'section' => 'What happens next', 'label' => 'Eyebrow', 'type' => 'text', 'value' => 'What happens next'],
    ['key' => 'contact.next.title', 'page' => 'Contact page', 'section' => 'What happens next', 'label' => 'Heading', 'type' => 'text', 'value' => 'A focused, low-friction start.'],
    ['key' => 'contact.next.card_1_title', 'page' => 'Contact page', 'section' => 'What happens next', 'label' => 'Step 1 — title', 'type' => 'text', 'value' => 'We review the context'],
    ['key' => 'contact.next.card_1_copy', 'page' => 'Contact page', 'section' => 'What happens next', 'label' => 'Step 1 — copy', 'type' => 'textarea', 'value' => 'Our team looks at your goal, users and the capability you may need.'],
    ['key' => 'contact.next.card_2_title', 'page' => 'Contact page', 'section' => 'What happens next', 'label' => 'Step 2 — title', 'type' => 'text', 'value' => 'We clarify the opportunity'],
    ['key' => 'contact.next.card_2_copy', 'page' => 'Contact page', 'section' => 'What happens next', 'label' => 'Step 2 — copy', 'type' => 'textarea', 'value' => 'We ask the questions that reduce ambiguity and reveal the right scope.'],
    ['key' => 'contact.next.card_3_title', 'page' => 'Contact page', 'section' => 'What happens next', 'label' => 'Step 3 — title', 'type' => 'text', 'value' => 'We recommend a path'],
    ['key' => 'contact.next.card_3_copy', 'page' => 'Contact page', 'section' => 'What happens next', 'label' => 'Step 3 — copy', 'type' => 'textarea', 'value' => 'You receive a practical next step—not a one-size-fits-all sales pitch.'],

    /* ---------------------------------------------------------------------
     * PRODUCTS PAGE
     * ------------------------------------------------------------------ */
    ['key' => 'products.hero.eyebrow', 'page' => 'Products page', 'section' => 'Hero', 'label' => 'Eyebrow', 'type' => 'text', 'value' => 'Our products'],
    ['key' => 'products.hero.title_prefix', 'page' => 'Products page', 'section' => 'Hero', 'label' => 'Heading — first part', 'type' => 'text', 'value' => 'Digital tools with a'],
    ['key' => 'products.hero.title_highlight', 'page' => 'Products page', 'section' => 'Hero', 'label' => 'Heading — highlighted part', 'type' => 'text', 'value' => 'clear reason to exist.'],
    ['key' => 'products.hero.copy', 'page' => 'Products page', 'section' => 'Hero', 'label' => 'Paragraph', 'type' => 'textarea', 'value' => 'Explore products and platforms shaped around practical business, learning and community needs.'],
    ['key' => 'products.list.eyebrow', 'page' => 'Products page', 'section' => 'Product list', 'label' => 'Eyebrow', 'type' => 'text', 'value' => 'Product portfolio'],
    ['key' => 'products.list.title', 'page' => 'Products page', 'section' => 'Product list', 'label' => 'Heading', 'type' => 'text', 'value' => 'Ideas made useful.'],
    ['key' => 'products.list.copy', 'page' => 'Products page', 'section' => 'Product list', 'label' => 'Paragraph', 'type' => 'textarea', 'value' => 'Each product reflects our focus on simple experiences, dependable technology and meaningful outcomes.'],
    ['key' => 'products.card.tag', 'page' => 'Products page', 'section' => 'Product list', 'label' => 'Card tag on every product', 'type' => 'text', 'value' => 'Digital product'],
    ['key' => 'products.card.link_label', 'page' => 'Products page', 'section' => 'Product list', 'label' => 'Card link text', 'type' => 'text', 'value' => 'Visit product'],
    ['key' => 'products.empty.title', 'page' => 'Products page', 'section' => 'Empty states', 'label' => 'No products — title', 'type' => 'text', 'value' => 'New products are being prepared.'],
    ['key' => 'products.empty.copy', 'page' => 'Products page', 'section' => 'Empty states', 'label' => 'No products — copy', 'type' => 'textarea', 'value' => 'We are refining the portfolio. Contact us to discuss a solution for your organization.'],
    ['key' => 'products.empty.button_label', 'page' => 'Products page', 'section' => 'Empty states', 'label' => 'No products — button text', 'type' => 'text', 'value' => 'Start a conversation'],
    ['key' => 'products.unavailable.title', 'page' => 'Products page', 'section' => 'Empty states', 'label' => 'Load failure — title', 'type' => 'text', 'value' => 'Products are temporarily unavailable.'],
    ['key' => 'products.unavailable.copy', 'page' => 'Products page', 'section' => 'Empty states', 'label' => 'Load failure — copy', 'type' => 'textarea', 'value' => 'Our portfolio could not be loaded right now. Please try again shortly or contact our team.'],
    ['key' => 'products.unavailable.button_label', 'page' => 'Products page', 'section' => 'Empty states', 'label' => 'Load failure — button text', 'type' => 'text', 'value' => 'Contact us'],
    ['key' => 'products.cta.title', 'page' => 'Products page', 'section' => 'Closing call to action', 'label' => 'Heading', 'type' => 'text', 'value' => 'Need a product built around your workflow?'],
    ['key' => 'products.cta.copy', 'page' => 'Products page', 'section' => 'Closing call to action', 'label' => 'Paragraph', 'type' => 'textarea', 'value' => 'We can help define, design and engineer a solution that fits your organization.'],
    ['key' => 'products.cta.button_label', 'page' => 'Products page', 'section' => 'Closing call to action', 'label' => 'Button text', 'type' => 'text', 'value' => 'Discuss your idea'],
    ['key' => 'products.cta.button_url', 'page' => 'Products page', 'section' => 'Closing call to action', 'label' => 'Button link', 'type' => 'url', 'value' => 'contact.html'],

    /* ---------------------------------------------------------------------
     * BLOG
     * ------------------------------------------------------------------ */
    ['key' => 'blog.hero.eyebrow', 'page' => 'Blog', 'section' => 'Hero', 'label' => 'Eyebrow', 'type' => 'text', 'value' => 'Insights'],
    ['key' => 'blog.hero.title_prefix', 'page' => 'Blog', 'section' => 'Hero', 'label' => 'Heading — first part', 'type' => 'text', 'value' => 'Ideas, notes and'],
    ['key' => 'blog.hero.title_highlight', 'page' => 'Blog', 'section' => 'Hero', 'label' => 'Heading — highlighted part', 'type' => 'text', 'value' => 'practical technology thinking.'],
    ['key' => 'blog.hero.copy', 'page' => 'Blog', 'section' => 'Hero', 'label' => 'Paragraph', 'type' => 'textarea', 'value' => 'Perspectives on building web platforms, business software and automation that actually get used.'],
    ['key' => 'blog.list.eyebrow', 'page' => 'Blog', 'section' => 'Article list', 'label' => 'Eyebrow', 'type' => 'text', 'value' => 'Latest articles'],
    ['key' => 'blog.list.title', 'page' => 'Blog', 'section' => 'Article list', 'label' => 'Heading', 'type' => 'text', 'value' => 'From the Unnat team.'],
    ['key' => 'blog.list.read_more_label', 'page' => 'Blog', 'section' => 'Article list', 'label' => 'Read-more button text', 'type' => 'text', 'value' => 'Read full article'],
    ['key' => 'blog.list.placeholder_image', 'page' => 'Blog', 'section' => 'Article list', 'label' => 'Image used when an article has no cover', 'type' => 'image', 'value' => 'assets/images/uts-logo-removebg-removebg-preview-512x512.webp'],
    ['key' => 'blog.list.reading_time_suffix', 'page' => 'Blog', 'section' => 'Article list', 'label' => 'Reading time suffix', 'type' => 'text', 'value' => 'min read'],
    ['key' => 'blog.list.empty_title', 'page' => 'Blog', 'section' => 'Article list', 'label' => 'No posts — title', 'type' => 'text', 'value' => 'The first articles are on the way.'],
    ['key' => 'blog.list.empty_copy', 'page' => 'Blog', 'section' => 'Article list', 'label' => 'No posts — copy', 'type' => 'textarea', 'value' => 'We are preparing useful, no-fluff writing about technology decisions. Check back shortly.'],
    ['key' => 'blog.post.back_label', 'page' => 'Blog', 'section' => 'Article page', 'label' => 'Back link text', 'type' => 'text', 'value' => 'All articles'],
    ['key' => 'blog.post.published_label', 'page' => 'Blog', 'section' => 'Article page', 'label' => 'Published date prefix', 'type' => 'text', 'value' => 'Published'],
    ['key' => 'blog.post.author_label', 'page' => 'Blog', 'section' => 'Article page', 'label' => 'Author prefix', 'type' => 'text', 'value' => 'By'],
    ['key' => 'blog.post.share_label', 'page' => 'Blog', 'section' => 'Article page', 'label' => 'Share heading', 'type' => 'text', 'value' => 'Share this article'],
    ['key' => 'blog.post.cta_title', 'page' => 'Blog', 'section' => 'Article page', 'label' => 'Closing CTA — heading', 'type' => 'text', 'value' => 'Want to talk through your own project?'],
    ['key' => 'blog.post.cta_copy', 'page' => 'Blog', 'section' => 'Article page', 'label' => 'Closing CTA — copy', 'type' => 'textarea', 'value' => 'We are happy to look at the goal, the bottleneck or the early idea with you.'],
    ['key' => 'blog.post.cta_button_label', 'page' => 'Blog', 'section' => 'Article page', 'label' => 'Closing CTA — button text', 'type' => 'text', 'value' => 'Start a conversation'],
    ['key' => 'blog.post.cta_button_url', 'page' => 'Blog', 'section' => 'Article page', 'label' => 'Closing CTA — button link', 'type' => 'url', 'value' => 'contact.html'],

    /* ---------------------------------------------------------------------
     * FOOTER
     * ------------------------------------------------------------------ */
    ['key' => 'footer.brand.copy', 'page' => 'Footer', 'section' => 'Brand column', 'label' => 'Description under the logo', 'type' => 'textarea', 'value' => 'Smart digital solutions built with clarity, care and long-term thinking.'],
    ['key' => 'footer.column_1.title', 'page' => 'Footer', 'section' => 'Link columns', 'label' => 'Column 1 heading', 'type' => 'text', 'value' => 'Explore'],
    ['key' => 'footer.column_2.title', 'page' => 'Footer', 'section' => 'Link columns', 'label' => 'Column 2 heading', 'type' => 'text', 'value' => 'Platforms'],
    ['key' => 'footer.column_3.title', 'page' => 'Footer', 'section' => 'Link columns', 'label' => 'Column 3 heading', 'type' => 'text', 'value' => 'Contact'],
    ['key' => 'footer.bottom.copyright', 'page' => 'Footer', 'section' => 'Bottom bar', 'label' => 'Copyright line (use {year} for the year)', 'type' => 'text', 'value' => '© {year} Unnat Technology Services. All rights reserved.'],
    ['key' => 'footer.bottom.note', 'page' => 'Footer', 'section' => 'Bottom bar', 'label' => 'Right-hand note', 'type' => 'text', 'value' => 'Designed for performance, accessibility and progress.'],

    /* ---------------------------------------------------------------------
     * SYSTEM PAGES
     * ------------------------------------------------------------------ */
    ['key' => 'system.notfound.eyebrow', 'page' => 'System pages', 'section' => 'Page not found (404)', 'label' => 'Eyebrow', 'type' => 'text', 'value' => 'Error 404'],
    ['key' => 'system.notfound.title', 'page' => 'System pages', 'section' => 'Page not found (404)', 'label' => 'Heading', 'type' => 'text', 'value' => 'This page could not be found.'],
    ['key' => 'system.notfound.copy', 'page' => 'System pages', 'section' => 'Page not found (404)', 'label' => 'Paragraph', 'type' => 'textarea', 'value' => 'The address may have changed or the page may have been removed. Try the homepage or contact our team.'],
    ['key' => 'system.notfound.button_label', 'page' => 'System pages', 'section' => 'Page not found (404)', 'label' => 'Button text', 'type' => 'text', 'value' => 'Back to homepage'],
    ['key' => 'system.notfound.button_url', 'page' => 'System pages', 'section' => 'Page not found (404)', 'label' => 'Button link', 'type' => 'url', 'value' => '/'],
    ['key' => 'system.form_success.message', 'page' => 'System pages', 'section' => 'Form responses', 'label' => 'Inquiry sent message', 'type' => 'text', 'value' => 'Thank you. Your inquiry has been received and our team will respond shortly.'],
    ['key' => 'system.form_error.message', 'page' => 'System pages', 'section' => 'Form responses', 'label' => 'Inquiry failed message', 'type' => 'text', 'value' => 'Your inquiry could not be sent. Please check the details and try again.'],
];
