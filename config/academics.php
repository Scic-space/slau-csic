<?php

$faculties = [
    'business' => [
        'name' => 'Faculty of Business Administration & Management',
        'programs' => [
            'Master of Business Administration and Management (MBA)',
            'Master of Business Administration and Management with Information Technology (MBA-IT)',
            'Master of Business Administration and Management in Finance & Accounting (MBA-FA)',
            'Master of Science in Entrepreneurship & Innovation',
            'Bachelor of Business Administration (BBA)',
            'Bachelor of Procurement and Supply Chain Management (BPSM)',
            'Bachelor of Human Resource Management (BHRM)',
            'Bachelor of Entrepreneurship & Small Business Management',
            'Bachelor of Science in Accounting & Finance',
            'Bachelor of Banking & Microfinance',
            'Bachelor of Economics & Statistics',
            'Bachelor of Tourism & Hospitality Management',
            'Diploma in Business Administration (DBA)',
            'Diploma in Procurement and Logistics Management',
            'Diploma in Microfinance / Accounting',
            'National Certificate in Business Administration (NCBA)',
        ],
    ],
    'education' => [
        'name' => 'Faculty of Education',
        'programs' => [
            'Master of Education Administration and Management (MAEM / M.Ed)',
            'Postgraduate Diploma in Education (PGDE)',
            'Bachelor of Arts with Education (BAED)',
            'Bachelor of Education (Primary Education)',
            'Bachelor of Education (Secondary Education)',
            'Bachelor of Science with Education (BSc. Ed)',
            'Bachelor of Early Childhood Education',
            'Diploma in Secondary Education (Arts / Sciences)',
            'Diploma in Primary Education (DPE)',
            'Diploma in Early Childhood Education',
            'Early Childhood Education / English Language Proficiency Certificates',
        ],
    ],
    'science' => [
        'name' => 'Faculty of Science & Technology',
        'programs' => [
            'Bachelor of Science in Cyber Security',
            'Bachelor of Information Technology (BIT)',
            'Bachelor of Science in Computer Science (BSCS)',
            'Bachelor of Science in Information Systems (BSIS)',
            'Bachelor of Science in Telecommunication Engineering',
            'Bachelor of Science in Computer Engineering',
            'Bachelor of Records and Information Management (BRIM)',
            'Bachelor of Public Health (BPH)',
            'Diploma in Information Technology (DIT)',
            'Diploma in Computer Science & Engineering',
            'Diploma in Records and Information Management',
            'Diploma in Finance and Business Computing',
            'National Certificate in Information Technology (NCIT)',
        ],
    ],
    'arts' => [
        'name' => 'Faculty of Arts & Social Sciences',
        'programs' => [
            'Bachelor of Mass Communication and Journalism',
            'Bachelor of Social Work and Social Administration (BSWSA)',
            'Bachelor of Public Administration and Management (BPAM)',
            'Bachelor of Diplomacy and International Relations (BDIR)',
            'Bachelor of Development Studies (BDS)',
            'Bachelor of Local Governance and Human Rights',
            'Bachelor of Guidance and Counseling',
            'Diploma in Public Administration and Management',
            'Diploma in Social Work and Social Administration',
            'Diploma in Mass Communication & Journalism',
            'Diploma in Guidance and Counseling',
            'Diploma in Development Studies',
        ],
    ],
];

$facultyNames = array_column($faculties, 'name');

$programNames = array_values(array_unique(array_merge(...array_column($faculties, 'programs'))));

return [
    'faculties' => $faculties,
    'facultyNames' => $facultyNames,
    'programNames' => $programNames,
];
