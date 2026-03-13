<?php

return [

    [
        'title' => 'Dashboard',
        'icon' => 'fas fa-tachometer-alt fa-spin',
        'route' => 'dashboard',
        'permission' => 'index_dashboard',
    ],

    [
        'title' => 'Users',
        'icon' => 'fas fa-users',
        'route' => 'admin.users.index',
        'permission' => 'index_users',
    ],

    [
        'title' => 'Roles',
        'icon' => 'fas fa-users',
        'route' => 'admin.user_roles.index',
        'permission' => 'index_roles',
    ],

    [
        'title' => 'Customers',
        'icon' => 'fas fa-user-tie',
        'route' => 'admin.customers.index',
        'permission' => 'index_customers',
    ],


    [
        'title' => 'Vehicles',
        'icon' => 'fas fa-car',
        'route' => 'admin.vehicles.index',
        'permission' => '',
        'children' => [
            [
                'title' => 'Vehicles',
                'icon' => 'fas fa-car',
                'route' => 'admin.vehicles.index',
                'permission' => 'index_vehicles',
            ],
            [
                'title' => 'Vehicle Bookings',
                'icon' => 'fas fa-calendar-alt',
                'route' => 'admin.vehicle_bookings.index',
                'permission' => 'index_vehicle_bookings',
            ],
            [
                'title' => 'Vehicle Movements',
                'icon' => 'fas fa-camera',
                'route' => 'admin.vehicle_moments.index',
                'permission' => 'index_vehicles_vehicle_movement',
            ],
        ],
    ],


    [
        'title' => 'Crew Profiles',
        'icon' => 'fas fa-user-tie',
        'route' => 'admin.crew_profiles.index',
        'permission' => 'index_crew_profiles',
    ],

    [
        'title' => 'GPS',
        'icon' => 'fas fa-map-marker-alt',
        'route' => 'admin.gpsdashboard',
        'permission' => 'index_gps',
    ],

    [
        'title' => 'Petrol Pumps',
        'icon' => 'fas fa-gas-pump',
        'permission' => null, // parent menu; show if any child visible
        'children' => [
            [
                'title' => 'Petrol Pumps',
                'icon' => 'fas fa-gas-pump',
                'route' => 'admin.petrol_pumps.index',
                'permission' => 'index_petrol_pumps_petrol_pumps',
            ],
            [
                'title' => 'Transactions',
                'icon' => 'fas fa-exchange-alt',
                'route' => 'admin.petrol_pump_transactions.index',
                'permission' => 'index_petrol_pumps_transactions',
            ],
        ],
    ],

    [
        'title' => 'Questionnaires',
        'icon' => 'fas fa-question-circle',
        'route' => 'admin.questionnaires.index',
        'permission' => 'index_questionnaires',
    ],

    [
        'title' => 'Fuel Purchased',
        'icon' => 'fas fa-gas-pump',
        'route' => 'admin.fuel_purchased.index',
        'permission' => 'index_fuel_purchased',
    ],

    [
        'title' => 'Bills',
        'icon' => 'fas fa-file-invoice-dollar', // parent icon for Bills
        'permission' => null,
        'children' => [
            [
                'title' => 'Proforma Invoice',
                'icon' => 'fas fa-file-invoice', // child icon for Proforma Invoice
                'route' => 'admin.proforma.index',
                'permission' => 'index_bills_proforma_invoice',
            ],

            [
                'title' => 'Receipts',
                'icon' => 'fas fa-file-invoice', // child icon for Proforma Invoice
                'route' => 'admin.receipt.index',
                'permission' => 'index_bills_receipt',
            ],
        ],
    ],

    [
        'title' => 'Emails',
        'icon' => 'fas fa-envelope', // parent icon for Emails
        'permission' => null,
        'children' => [
            [
                'title' => 'Email Template Activities',
                'icon' => 'fas fa-tasks',
                'route' => 'admin.emailtemplate_activities.index',
                'permission' => 'index_emails_emailtemplate_activities',
            ],
            [
                'title' => 'Email Template',
                'icon' => 'fas fa-envelope-open-text',
                'route' => 'admin.email-templates.index',
                'permission' => 'index_emails_emailtemplate',
            ],
            [
                'title' => 'Email Logs',
                'icon' => 'fas fa-history',
                'route' => 'admin.email-logs.index',
                'permission' => 'index_emails_emaillogs',
            ],
        ],
    ],






];
