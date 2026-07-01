/**
 * DEPARTMENT ↔ WIX CATEGORIES MAPPING
 * Maps department slugs to actual Wix category strings
 * Used by API to filter posts dynamically
 */

const DEPT_CATEGORY_MAP = {
  // PUBLICATIONS - Department → Wix Publication Categories
  publications: {
    'kspcb': ['KSPCB_publications'],
    'kali-tiger-reserve': ['Kali Tiger Reserve_publications'],
    'nagarhole-national-park': ['Nagarhole_publications'],
    'karnataka-forest-department': ['Karnataka Forest Department_publica', 'Wildlife & Forest'],
    'shimoga': ['Shimoga_Publications'],
    'chitradurga': ['Chitradurga_publications'],
    'kalaburagi': ['Gulbarga_publications'],
    'academics': ['Academics_publications'],
    'adcl': ['ADCL_publications'],
    'ayush': [],
    'kfcsc': [],
    'general': ['Publications']
  },

  // ACCOMPLISHMENTS - Department
  accomplishments: {
    'kspcb': ['KSPCB', 'KSPCB photos'],
    'kali-tiger-reserve': ['Kali Tiger Reserve'],
    'nagarhole-national-park': [],
    'karnataka-forest-department': ['KFD - Wildlife'],
    'shimoga': ['Forest Department - Shimogga'],
    'chitradurga': ['Forest Department - Chitradurga'],
    'kalaburagi': [],
    'academics': [],
    'adcl': ['Dr B R Ambedkar Development Corp.'],
    'ayush': ['Ayush', 'Ayush Accomplishments'],
    'kfcsc': [],
    'general': ['Accomplishments']
  }
};

module.exports = DEPT_CATEGORY_MAP;