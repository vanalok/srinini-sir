<?php
/* ============================================================
   Srinivasulu IFS — Admin CMS configuration
   ============================================================ */

// --- Admin login ---------------------------------------------------------
// Username + bcrypt password hash. To change the password, run:
//   php -r 'echo password_hash("YOUR-NEW-PASSWORD", PASSWORD_DEFAULT), "\n";'
// and paste the result below. Default password: vanalok@2026
const ADMIN_USER = 'admin';
const ADMIN_PASS_HASH = '$2y$10$b7iWU3qX/1OZstocejWc9.KldRZLVItn6am.8UvIZqia3FDqy8F9e';

// --- Paths ---------------------------------------------------------------
// Project root = one level above /admin. All content JSON lives here.
define('SITE_ROOT', dirname(__DIR__));
define('IMAGES_DIR', SITE_ROOT . '/images');
define('PDF_DIR',    SITE_ROOT . '/assets/pdf');
define('AUDIO_DIR',  SITE_ROOT . '/assets/audio');

// --- Departments (mirror of chrome.js SR_DEPTS_CV, + video-only cats) -----
// slug => display name. Used for dropdowns across the CMS.
const DEPARTMENTS = [
  'ecology-environment'         => 'Ecology & Environment',
  'karnataka-forest-department' => 'Karnataka Forest Department',
  'ayush'                       => 'AYUSH',
  'kfcsc'                       => 'KFCSC',
  'kspcb'                       => 'KSPCB',
  'shimoga'                     => 'Shimoga Division',
  'adcl'                        => 'ADCL',
  'kali-tiger-reserve'          => 'Kali Tiger Reserve',
  'chitradurga'                 => 'Chitradurga Division',
  'academics'                   => 'Academics',
  'kalaburagi'                  => 'Kalaburagi Division',
  'nagarhole-national-park'     => 'Nagarhole National Park',
  'talks'                       => 'Talks & Keynotes (videos only)',
];

// --- Session cookie hardening -------------------------------------------
session_set_cookie_params([
  'httponly' => true,
  'samesite' => 'Lax',
]);
