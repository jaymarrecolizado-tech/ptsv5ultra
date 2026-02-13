<?php
/**
 * Project Type Configuration
 * Defines fields, validation rules, and report formats for each project type
 */

return [
    'project_types' => [
        'EgovPH' => [
            'description' => 'eGovernment Philippines platform - Agency onboarding and marketing activities',
            'icon' => 'government',
            'color' => '#6366F1',
            'is_activity_based' => true,
            'fields' => [
                'activity_id' => ['label' => 'Activity ID', 'type' => 'text', 'required' => true, 'pattern' => '/^EGV-[0-9]{4}$/'],
                'project_name' => ['label' => 'Project Name', 'type' => 'text', 'required' => true, 'default' => 'EgovPH'],
                'activity_title' => ['label' => 'Activity Title', 'type' => 'text', 'required' => true],
                'activity_type' => ['label' => 'Activity Type', 'type' => 'select', 'required' => true, 'options' => ['Marketing', 'Orientation', 'Onboarding', 'Training', 'Technical Assistance']],
                'activity_date' => ['label' => 'Date', 'type' => 'date', 'required' => true],
                'participants' => ['label' => 'Participants', 'type' => 'number', 'required' => false, 'min' => 0],
                'downloads' => ['label' => 'Downloads', 'type' => 'number', 'required' => false, 'min' => 0],
                'province' => ['label' => 'Province', 'type' => 'select', 'required' => true, 'options' => ['Batanes', 'Cagayan', 'Isabela', 'Nueva Vizcaya', 'Quirino']],
                'municipality' => ['label' => 'Municipality', 'type' => 'text', 'required' => true],
                'district' => ['label' => 'District', 'type' => 'text', 'required' => false],
                'status' => ['label' => 'Status', 'type' => 'select', 'required' => true, 'options' => ['Done', 'Pending']],
                'facilitator' => ['label' => 'Facilitator', 'type' => 'text', 'required' => false],
                'latitude' => ['label' => 'Latitude', 'type' => 'number', 'required' => false],
                'longitude' => ['label' => 'Longitude', 'type' => 'number', 'required' => false],
            ],
            'report_metrics' => [
                'total_activities' => 'Total Activities',
                'total_participants' => 'Total Participants',
                'total_downloads' => 'Total Downloads',
                'activities_by_type' => 'Activities by Type'
            ],
            'charts' => ['status_pie', 'activity_type_bar', 'participants_line', 'downloads_bar']
        ],
        
        'ELGU' => [
            'description' => 'eLocal Government Unit - LGU digitalization and EBOSS compliance tracking',
            'icon' => 'city',
            'color' => '#10B981',
            'fields' => [
                'site_code' => ['label' => 'Site Code', 'type' => 'text', 'required' => true, 'pattern' => '/^ELG-[0-9]{4}$/'],
                'project_name' => ['label' => 'Project Name', 'type' => 'text', 'required' => true, 'default' => 'ELGU'],
                'lgu_name' => ['label' => 'LGU Name', 'type' => 'text', 'required' => true],
                'lgu_type' => ['label' => 'LGU Type', 'type' => 'select', 'required' => true, 'options' => ['Municipal Hall', 'City Hall', 'Barangay Hall']],
                'services_digitalized' => ['label' => 'Services Digitalized', 'type' => 'multiselect', 'required' => false, 
                    'options' => ['Business Permit', 'Tax Payment', 'Civil Registry', 'Real Property', 'EBOSS', 'Online Services']],
                'eboss_compliance' => ['label' => 'EBOSS Compliance', 'type' => 'select', 'required' => false, 'options' => ['Yes', 'No', 'In Progress']],
                'date_digitalized' => ['label' => 'Date Digitalized', 'type' => 'date', 'required' => false],
                'province' => ['label' => 'Province', 'type' => 'select', 'required' => true, 'options' => ['Batanes', 'Cagayan', 'Isabela', 'Nueva Vizcaya', 'Quirino']],
                'municipality' => ['label' => 'Municipality', 'type' => 'text', 'required' => true],
                'district' => ['label' => 'District', 'type' => 'text', 'required' => false],
                'barangay' => ['label' => 'Barangay', 'type' => 'text', 'required' => false],
                'latitude' => ['label' => 'Latitude', 'type' => 'number', 'required' => true],
                'longitude' => ['label' => 'Longitude', 'type' => 'number', 'required' => true],
                'status' => ['label' => 'Status', 'type' => 'select', 'required' => true, 'options' => ['Done', 'Pending']],
            ],
            'report_metrics' => [
                'total_lgus' => 'Total LGUs Onboarded',
                'eboss_compliant' => 'EBOSS Compliant',
                'digitalization_rate' => 'Digitalization Rate',
                'services_offered' => 'Services Digitalized'
            ],
            'charts' => ['status_pie', 'lgu_type_bar', 'eboss_pie', 'timeline_line']
        ],
        
        'Free-WIFI for All' => [
            'description' => 'Public WiFi hotspots in government facilities with daily metrics tracking',
            'icon' => 'wifi',
            'color' => '#3B82F6',
            'has_daily_metrics' => true,
            'fields' => [
                'site_code' => ['label' => 'AP Site Code', 'type' => 'text', 'required' => true, 'pattern' => '/^(WIFI|SINAG)-[A-Z0-9-]+$/'],
                'project_name' => ['label' => 'Project Name', 'type' => 'text', 'required' => true, 'default' => 'Free-WIFI for All'],
                'site_name' => ['label' => 'AP Site Name', 'type' => 'text', 'required' => true],
                'location_name' => ['label' => 'Location Name', 'type' => 'text', 'required' => true],
                'site_type' => ['label' => 'Site Type', 'type' => 'select', 'required' => true, 
                    'options' => ['HSP', 'LGU-HALL', 'LGU-OTHERS', 'HEI-SUC', 'ES', 'HS', 'SUC', 'RHU', 'PNP', 'BFP', 'NGA', 'OTHERS']],
                'cms_provider' => ['label' => 'CMS Provider', 'type' => 'text', 'required' => false],
                'link_provider' => ['label' => 'Link Provider', 'type' => 'text', 'required' => false],
                'technology' => ['label' => 'Last Mile Technology', 'type' => 'select', 'required' => false, 
                    'options' => ['Fiber', 'LTE', 'LEO', 'VSAT', 'DSL', 'Others']],
                'bandwidth' => ['label' => 'BW Download (CIR)', 'type' => 'number', 'required' => false],
                'barangay' => ['label' => 'Barangay', 'type' => 'text', 'required' => false],
                'municipality' => ['label' => 'Locality', 'type' => 'text', 'required' => true],
                'province' => ['label' => 'Province', 'type' => 'select', 'required' => true, 'options' => ['Batanes', 'Cagayan', 'Isabela', 'Nueva Vizcaya', 'Quirino']],
                'district' => ['label' => 'District', 'type' => 'text', 'required' => false],
                'latitude' => ['label' => 'Latitude', 'type' => 'number', 'required' => true],
                'longitude' => ['label' => 'Longitude', 'type' => 'number', 'required' => true],
                'activation_date' => ['label' => 'Date of Activation', 'type' => 'date', 'required' => false],
                'status' => ['label' => 'Status', 'type' => 'select', 'required' => true, 'options' => ['Done', 'Pending', 'REBATES', 'Active', 'Inactive']],
                'notes' => ['label' => 'Notes', 'type' => 'textarea', 'required' => false]
            ],
            'report_metrics' => [
                'total_sites' => 'Total WiFi Sites',
                'avg_bandwidth' => 'Average Bandwidth',
                'provider_distribution' => 'Provider Distribution',
                'sites_by_type' => 'Sites by Type'
            ],
            'charts' => ['status_pie', 'provider_bar', 'bandwidth_histogram', 'site_type_bar']
        ],
        
        'Tech4ED' => [
            'description' => 'Technology for Education digital learning centers',
            'icon' => 'computer',
            'color' => '#10B981',
            'fields' => [
                'site_code' => ['label' => 'Site Code', 'type' => 'text', 'required' => true, 'pattern' => '/^T4E-[0-9]{4}$/'],
                'project_name' => ['label' => 'Project Name', 'type' => 'text', 'required' => true, 'default' => 'Tech4ED Program'],
                'site_name' => ['label' => 'Center Name', 'type' => 'text', 'required' => true],
                'computer_count' => ['label' => 'Number of Computers', 'type' => 'number', 'required' => true, 'min' => 1, 'max' => 50],
                'printer_count' => ['label' => 'Number of Printers', 'type' => 'number', 'required' => false, 'min' => 0, 'max' => 5],
                'trained_personnel' => ['label' => 'Trained Personnel', 'type' => 'number', 'required' => true, 'min' => 1],
                'courses_offered' => ['label' => 'Courses Offered', 'type' => 'multiselect', 'required' => false, 
                    'options' => ['Computer Basics', 'Microsoft Office', 'Internet & Email', 'Online Safety', 'Digital Marketing', 'Coding Basics']],
                'barangay' => ['label' => 'Barangay', 'type' => 'text', 'required' => true],
                'municipality' => ['label' => 'Municipality', 'type' => 'text', 'required' => true],
                'province' => ['label' => 'Province', 'type' => 'select', 'required' => true, 'options' => ['Batanes', 'Cagayan', 'Isabela', 'Nueva Vizcaya', 'Quirino']],
                'district' => ['label' => 'District', 'type' => 'text', 'required' => true],
                'latitude' => ['label' => 'Latitude', 'type' => 'number', 'required' => true],
                'longitude' => ['label' => 'Longitude', 'type' => 'number', 'required' => true],
                'activation_date' => ['label' => 'Date Established', 'type' => 'date', 'required' => true],
                'status' => ['label' => 'Status', 'type' => 'select', 'required' => true, 'options' => ['Done', 'Pending']],
                'beneficiaries_count' => ['label' => 'Number of Beneficiaries', 'type' => 'number', 'required' => false],
                'notes' => ['label' => 'Notes', 'type' => 'textarea', 'required' => false]
            ],
            'report_metrics' => [
                'total_centers' => 'Total Tech4ED Centers',
                'total_computers' => 'Total Computers Deployed',
                'total_beneficiaries' => 'Total Beneficiaries',
                'avg_computers_per_center' => 'Avg Computers per Center'
            ],
            'charts' => ['status_pie', 'computers_bar', 'beneficiaries_line', 'courses_pie']
        ],
        
        'National Broadband Program' => [
            'description' => 'Fiber optic and broadband infrastructure',
            'icon' => 'broadcast',
            'color' => '#8B5CF6',
            'fields' => [
                'site_code' => ['label' => 'Site Code', 'type' => 'text', 'required' => true, 'pattern' => '/^NBP-[0-9]{4}$/'],
                'project_name' => ['label' => 'Project Name', 'type' => 'text', 'required' => true, 'default' => 'National Broadband Program'],
                'site_name' => ['label' => 'Infrastructure Name', 'type' => 'text', 'required' => true],
                'infrastructure_type' => ['label' => 'Infrastructure Type', 'type' => 'select', 'required' => true,
                    'options' => ['Fiber Node', 'Tower Site', 'Point of Presence', 'Cable Landing', 'Distribution Point']],
                'fiber_length_km' => ['label' => 'Fiber Length (km)', 'type' => 'number', 'required' => false, 'min' => 0],
                'coverage_area' => ['label' => 'Coverage Area', 'type' => 'text', 'required' => false],
                'connected_institutions' => ['label' => 'Connected Institutions', 'type' => 'number', 'required' => false],
                'barangay' => ['label' => 'Barangay', 'type' => 'text', 'required' => true],
                'municipality' => ['label' => 'Municipality', 'type' => 'text', 'required' => true],
                'province' => ['label' => 'Province', 'type' => 'select', 'required' => true, 'options' => ['Batanes', 'Cagayan', 'Isabela', 'Nueva Vizcaya', 'Quirino']],
                'district' => ['label' => 'District', 'type' => 'text', 'required' => true],
                'latitude' => ['label' => 'Latitude', 'type' => 'number', 'required' => true],
                'longitude' => ['label' => 'Longitude', 'type' => 'number', 'required' => true],
                'activation_date' => ['label' => 'Date Operational', 'type' => 'date', 'required' => true],
                'status' => ['label' => 'Status', 'type' => 'select', 'required' => true, 'options' => ['Done', 'Pending']],
                'notes' => ['label' => 'Notes', 'type' => 'textarea', 'required' => false]
            ],
            'report_metrics' => [
                'total_infrastructure' => 'Total Infrastructure Sites',
                'total_fiber_km' => 'Total Fiber Length (km)',
                'avg_institutions_connected' => 'Avg Institutions Connected',
                'infrastructure_by_type' => 'Infrastructure by Type'
            ],
            'charts' => ['status_pie', 'infrastructure_type_bar', 'fiber_length_bar', 'timeline_line']
        ],
        
        'Digital Cities Program' => [
            'description' => 'Smart city infrastructure and digital services',
            'icon' => 'city',
            'color' => '#F59E0B',
            'fields' => [
                'site_code' => ['label' => 'Site Code', 'type' => 'text', 'required' => true, 'pattern' => '/^DCP-[0-9]{4}$/'],
                'project_name' => ['label' => 'Project Name', 'type' => 'text', 'required' => true, 'default' => 'Digital Cities Program'],
                'site_name' => ['label' => 'Facility Name', 'type' => 'text', 'required' => true],
                'facility_type' => ['label' => 'Facility Type', 'type' => 'select', 'required' => true,
                    'options' => ['Command Center', 'Free WiFi Zone', 'Payment Hub', 'Information Kiosk', 'E-Government Center', 'CCTV Network']],
                'cctv_cameras' => ['label' => 'Number of CCTV Cameras', 'type' => 'number', 'required' => false, 'min' => 0],
                'sensors_deployed' => ['label' => 'IoT Sensors Deployed', 'type' => 'number', 'required' => false, 'min' => 0],
                'digital_services' => ['label' => 'Digital Services Offered', 'type' => 'multiselect', 'required' => false,
                    'options' => ['Online Permits', 'Digital Payments', 'Public WiFi', 'CCTV Monitoring', 'Emergency Response', 'Traffic Management']],
                'barangay' => ['label' => 'Barangay', 'type' => 'text', 'required' => true],
                'municipality' => ['label' => 'Municipality', 'type' => 'text', 'required' => true],
                'province' => ['label' => 'Province', 'type' => 'select', 'required' => true, 'options' => ['Batanes', 'Cagayan', 'Isabela', 'Nueva Vizcaya', 'Quirino']],
                'district' => ['label' => 'District', 'type' => 'text', 'required' => true],
                'latitude' => ['label' => 'Latitude', 'type' => 'number', 'required' => true],
                'longitude' => ['label' => 'Longitude', 'type' => 'number', 'required' => true],
                'activation_date' => ['label' => 'Launch Date', 'type' => 'date', 'required' => true],
                'status' => ['label' => 'Status', 'type' => 'select', 'required' => true, 'options' => ['Done', 'Pending']],
                'notes' => ['label' => 'Notes', 'type' => 'textarea', 'required' => false]
            ],
            'report_metrics' => [
                'total_facilities' => 'Total Smart Facilities',
                'total_cctv' => 'Total CCTV Cameras',
                'total_sensors' => 'Total IoT Sensors',
                'facility_by_type' => 'Facilities by Type'
            ],
            'charts' => ['status_pie', 'facility_type_bar', 'cctv_sensors_bar', 'services_pie']
        ],
        
        'PIPOL' => [
            'description' => 'Philippine Public Library System digitization',
            'icon' => 'library',
            'color' => '#EC4899',
            'fields' => [
                'site_code' => ['label' => 'Site Code', 'type' => 'text', 'required' => true, 'pattern' => '/^PIP-[0-9]{4}$/'],
                'project_name' => ['label' => 'Project Name', 'type' => 'text', 'required' => true, 'default' => 'PIPOL Program'],
                'site_name' => ['label' => 'Library Name', 'type' => 'text', 'required' => true],
                'library_type' => ['label' => 'Library Type', 'type' => 'select', 'required' => true,
                    'options' => ['Public Library', 'School Library', 'Barangay Reading Center', 'Mobile Library', 'Digital Library']],
                'workstations' => ['label' => 'Computer Workstations', 'type' => 'number', 'required' => true, 'min' => 1],
                'ebooks_available' => ['label' => 'E-Books Available', 'type' => 'number', 'required' => false],
                'physical_books' => ['label' => 'Physical Books Count', 'type' => 'number', 'required' => false],
                'barangay' => ['label' => 'Barangay', 'type' => 'text', 'required' => true],
                'municipality' => ['label' => 'Municipality', 'type' => 'text', 'required' => true],
                'province' => ['label' => 'Province', 'type' => 'select', 'required' => true, 'options' => ['Batanes', 'Cagayan', 'Isabela', 'Nueva Vizcaya', 'Quirino']],
                'district' => ['label' => 'District', 'type' => 'text', 'required' => true],
                'latitude' => ['label' => 'Latitude', 'type' => 'number', 'required' => true],
                'longitude' => ['label' => 'Longitude', 'type' => 'number', 'required' => true],
                'activation_date' => ['label' => 'Launch Date', 'type' => 'date', 'required' => true],
                'status' => ['label' => 'Status', 'type' => 'select', 'required' => true, 'options' => ['Done', 'Pending']],
                'patrons_served' => ['label' => 'Monthly Patrons', 'type' => 'number', 'required' => false],
                'notes' => ['label' => 'Notes', 'type' => 'textarea', 'required' => false]
            ],
            'report_metrics' => [
                'total_libraries' => 'Total Libraries',
                'total_workstations' => 'Total Workstations',
                'total_ebooks' => 'Total E-Books',
                'libraries_by_type' => 'Libraries by Type'
            ],
            'charts' => ['status_pie', 'library_type_bar', 'resources_bar', 'patrons_line']
        ],
        
        'Rural Impact Sourcing' => [
            'description' => 'BPO and online freelancing hubs in rural areas',
            'icon' => 'briefcase',
            'color' => '#14B8A6',
            'fields' => [
                'site_code' => ['label' => 'Site Code', 'type' => 'text', 'required' => true, 'pattern' => '/^RIS-[0-9]{4}$/'],
                'project_name' => ['label' => 'Project Name', 'type' => 'text', 'required' => true, 'default' => 'Rural Impact Sourcing'],
                'site_name' => ['label' => 'Hub Name', 'type' => 'text', 'required' => true],
                'hub_type' => ['label' => 'Hub Type', 'type' => 'select', 'required' => true,
                    'options' => ['BPO Training Center', 'Freelancers Hub', 'Digital Workers Co-op', 'Remote Work Station', 'Community Work Hub']],
                'workstations' => ['label' => 'Available Workstations', 'type' => 'number', 'required' => true, 'min' => 1],
                'graduates_count' => ['label' => 'Total Graduates/Trainees', 'type' => 'number', 'required' => false],
                'active_workers' => ['label' => 'Active Digital Workers', 'type' => 'number', 'required' => false],
                'training_programs' => ['label' => 'Training Programs', 'type' => 'multiselect', 'required' => false,
                    'options' => ['Data Entry', 'Virtual Assistance', 'Content Writing', 'Graphic Design', 'Web Development', 'Customer Service']],
                'barangay' => ['label' => 'Barangay', 'type' => 'text', 'required' => true],
                'municipality' => ['label' => 'Municipality', 'type' => 'text', 'required' => true],
                'province' => ['label' => 'Province', 'type' => 'select', 'required' => true, 'options' => ['Batanes', 'Cagayan', 'Isabela', 'Nueva Vizcaya', 'Quirino']],
                'district' => ['label' => 'District', 'type' => 'text', 'required' => true],
                'latitude' => ['label' => 'Latitude', 'type' => 'number', 'required' => true],
                'longitude' => ['label' => 'Longitude', 'type' => 'number', 'required' => true],
                'activation_date' => ['label' => 'Date Established', 'type' => 'date', 'required' => true],
                'status' => ['label' => 'Status', 'type' => 'select', 'required' => true, 'options' => ['Done', 'Pending']],
                'notes' => ['label' => 'Notes', 'type' => 'textarea', 'required' => false]
            ],
            'report_metrics' => [
                'total_hubs' => 'Total Rural Hubs',
                'total_workstations' => 'Total Workstations',
                'total_graduates' => 'Total Graduates',
                'active_workers' => 'Active Workers'
            ],
            'charts' => ['status_pie', 'hub_type_bar', 'training_programs_pie', 'workers_line']
        ],
        
        'e-Government Services' => [
            'description' => 'Online government services and digital transactions',
            'icon' => 'government',
            'color' => '#6366F1',
            'fields' => [
                'site_code' => ['label' => 'Site Code', 'type' => 'text', 'required' => true, 'pattern' => '/^EGS-[0-9]{4}$/'],
                'project_name' => ['label' => 'Project Name', 'type' => 'text', 'required' => true, 'default' => 'e-Government Services'],
                'site_name' => ['label' => 'Service Center Name', 'type' => 'text', 'required' => true],
                'service_type' => ['label' => 'Service Type', 'type' => 'select', 'required' => true,
                    'options' => ['Business Permits', 'Civil Registry', 'Tax Payment', 'Health Records', 'Public Assistance', 'Document Processing']],
                'services_offered' => ['label' => 'Services Offered', 'type' => 'multiselect', 'required' => false,
                    'options' => ['Online Application', 'Digital Payment', 'Appointment Booking', 'Document Tracking', 'SMS Notifications', 'Email Alerts']],
                'daily_transactions' => ['label' => 'Avg Daily Transactions', 'type' => 'number', 'required' => false],
                'barangay' => ['label' => 'Barangay', 'type' => 'text', 'required' => true],
                'municipality' => ['label' => 'Municipality', 'type' => 'text', 'required' => true],
                'province' => ['label' => 'Province', 'type' => 'select', 'required' => true, 'options' => ['Batanes', 'Cagayan', 'Isabela', 'Nueva Vizcaya', 'Quirino']],
                'district' => ['label' => 'District', 'type' => 'text', 'required' => true],
                'latitude' => ['label' => 'Latitude', 'type' => 'number', 'required' => true],
                'longitude' => ['label' => 'Longitude', 'type' => 'number', 'required' => true],
                'activation_date' => ['label' => 'Launch Date', 'type' => 'date', 'required' => true],
                'status' => ['label' => 'Status', 'type' => 'select', 'required' => true, 'options' => ['Done', 'Pending']],
                'notes' => ['label' => 'Notes', 'type' => 'textarea', 'required' => false]
            ],
            'report_metrics' => [
                'total_centers' => 'Total e-Gov Centers',
                'total_services' => 'Total Services Offered',
                'avg_daily_transactions' => 'Avg Daily Transactions',
                'services_by_type' => 'Services by Type'
            ],
            'charts' => ['status_pie', 'service_type_bar', 'services_pie', 'transactions_line']
        ],
        
        'ICT Literacy Programs' => [
            'description' => 'Digital skills training and computer literacy',
            'icon' => 'education',
            'color' => '#F97316',
            'fields' => [
                'site_code' => ['label' => 'Site Code', 'type' => 'text', 'required' => true, 'pattern' => '/^ICT-[0-9]{4}$/'],
                'project_name' => ['label' => 'Project Name', 'type' => 'text', 'required' => true, 'default' => 'ICT Literacy Program'],
                'site_name' => ['label' => 'Training Center', 'type' => 'text', 'required' => true],
                'program_type' => ['label' => 'Program Type', 'type' => 'select', 'required' => true,
                    'options' => ['Basic Computer Training', 'Senior Digital Literacy', 'Youth Coding Camp', 'Women in Tech', 'Teachers Training']],
                'training_hours' => ['label' => 'Training Hours', 'type' => 'number', 'required' => true, 'min' => 1],
                'participants_trained' => ['label' => 'Participants Trained', 'type' => 'number', 'required' => false],
                'certification_offered' => ['label' => 'Certification Offered', 'type' => 'checkbox', 'required' => false],
                'barangay' => ['label' => 'Barangay', 'type' => 'text', 'required' => true],
                'municipality' => ['label' => 'Municipality', 'type' => 'text', 'required' => true],
                'province' => ['label' => 'Province', 'type' => 'select', 'required' => true, 'options' => ['Batanes', 'Cagayan', 'Isabela', 'Nueva Vizcaya', 'Quirino']],
                'district' => ['label' => 'District', 'type' => 'text', 'required' => true],
                'latitude' => ['label' => 'Latitude', 'type' => 'number', 'required' => true],
                'longitude' => ['label' => 'Longitude', 'type' => 'number', 'required' => true],
                'activation_date' => ['label' => 'Start Date', 'type' => 'date', 'required' => true],
                'status' => ['label' => 'Status', 'type' => 'select', 'required' => true, 'options' => ['Done', 'Pending']],
                'notes' => ['label' => 'Notes', 'type' => 'textarea', 'required' => false]
            ],
            'report_metrics' => [
                'total_programs' => 'Total Training Programs',
                'total_participants' => 'Total Participants',
                'total_training_hours' => 'Total Training Hours',
                'certified_count' => 'Certified Participants'
            ],
            'charts' => ['status_pie', 'program_type_bar', 'participants_bar', 'hours_line']
        ],
        
        'Cybersecurity Awareness' => [
            'description' => 'Online safety and security education',
            'icon' => 'shield',
            'color' => '#DC2626',
            'fields' => [
                'site_code' => ['label' => 'Site Code', 'type' => 'text', 'required' => true, 'pattern' => '/^CSA-[0-9]{4}$/'],
                'project_name' => ['label' => 'Project Name', 'type' => 'text', 'required' => true, 'default' => 'Cybersecurity Awareness'],
                'site_name' => ['label' => 'Center/Location', 'type' => 'text', 'required' => true],
                'center_type' => ['label' => 'Center Type', 'type' => 'select', 'required' => true,
                    'options' => ['Seminar Hall', 'Workshop Venue', 'Prevention Center', 'Privacy Hub', 'Youth Cyber Club']],
                'topics_covered' => ['label' => 'Topics Covered', 'type' => 'multiselect', 'required' => false,
                    'options' => ['Password Security', 'Phishing Awareness', 'Social Media Safety', 'Online Scams', 'Data Privacy', 'Safe Browsing']],
                'attendees_educated' => ['label' => 'Attendees Educated', 'type' => 'number', 'required' => false],
                'sessions_conducted' => ['label' => 'Sessions Conducted', 'type' => 'number', 'required' => false],
                'barangay' => ['label' => 'Barangay', 'type' => 'text', 'required' => true],
                'municipality' => ['label' => 'Municipality', 'type' => 'text', 'required' => true],
                'province' => ['label' => 'Province', 'type' => 'select', 'required' => true, 'options' => ['Batanes', 'Cagayan', 'Isabela', 'Nueva Vizcaya', 'Quirino']],
                'district' => ['label' => 'District', 'type' => 'text', 'required' => true],
                'latitude' => ['label' => 'Latitude', 'type' => 'number', 'required' => true],
                'longitude' => ['label' => 'Longitude', 'type' => 'number', 'required' => true],
                'activation_date' => ['label' => 'Launch Date', 'type' => 'date', 'required' => true],
                'status' => ['label' => 'Status', 'type' => 'select', 'required' => true, 'options' => ['Done', 'Pending']],
                'notes' => ['label' => 'Notes', 'type' => 'textarea', 'required' => false]
            ],
            'report_metrics' => [
                'total_centers' => 'Total Awareness Centers',
                'total_attendees' => 'Total Attendees Educated',
                'total_sessions' => 'Total Sessions',
                'avg_attendees_per_session' => 'Avg Attendees per Session'
            ],
            'charts' => ['status_pie', 'center_type_bar', 'topics_pie', 'attendees_line']
        ],
        
        'Free Internet in Public Places' => [
            'description' => 'Free internet access in public areas',
            'icon' => 'globe',
            'color' => '#06B6D4',
            'fields' => [
                'site_code' => ['label' => 'Site Code', 'type' => 'text', 'required' => true, 'pattern' => '/^FIP-[0-9]{4}$/'],
                'project_name' => ['label' => 'Project Name', 'type' => 'text', 'required' => true, 'default' => 'Free Internet in Public Places'],
                'site_name' => ['label' => 'Location Name', 'type' => 'text', 'required' => true],
                'location_type' => ['label' => 'Location Type', 'type' => 'select', 'required' => true,
                    'options' => ['Municipal Plaza', 'Public Market', 'Hospital', 'Transport Terminal', 'Public School', 'Park', 'Barangay Hall']],
                'coverage_radius_m' => ['label' => 'Coverage Radius (meters)', 'type' => 'number', 'required' => false, 'min' => 10],
                'expected_users' => ['label' => 'Expected Daily Users', 'type' => 'number', 'required' => false],
                'barangay' => ['label' => 'Barangay', 'type' => 'text', 'required' => true],
                'municipality' => ['label' => 'Municipality', 'type' => 'text', 'required' => true],
                'province' => ['label' => 'Province', 'type' => 'select', 'required' => true, 'options' => ['Batanes', 'Cagayan', 'Isabela', 'Nueva Vizcaya', 'Quirino']],
                'district' => ['label' => 'District', 'type' => 'text', 'required' => true],
                'latitude' => ['label' => 'Latitude', 'type' => 'number', 'required' => true],
                'longitude' => ['label' => 'Longitude', 'type' => 'number', 'required' => true],
                'activation_date' => ['label' => 'Launch Date', 'type' => 'date', 'required' => true],
                'status' => ['label' => 'Status', 'type' => 'select', 'required' => true, 'options' => ['Done', 'Pending']],
                'notes' => ['label' => 'Notes', 'type' => 'textarea', 'required' => false]
            ],
            'report_metrics' => [
                'total_locations' => 'Total Public Locations',
                'avg_coverage_radius' => 'Avg Coverage Radius',
                'total_expected_users' => 'Total Expected Users',
                'locations_by_type' => 'Locations by Type'
            ],
            'charts' => ['status_pie', 'location_type_bar', 'coverage_bar', 'users_line']
        ]
    ],
    
    // Common fields that appear in all project types
    'common_fields' => [
        'site_code', 'project_name', 'site_name', 'barangay', 'municipality', 
        'province', 'district', 'latitude', 'longitude', 'activation_date', 'status', 'notes'
    ],
    
    // Import validation settings
    'import_settings' => [
        'max_file_size' => 10 * 1024 * 1024, // 10MB
        'allowed_extensions' => ['csv', 'xlsx'],
        'batch_size' => 100, // Process 100 rows at a time
        'max_errors' => 50 // Stop after 50 errors
    ]
];
