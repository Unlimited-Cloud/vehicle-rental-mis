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
                'title' => 'Vehicle Moments',
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
        'permission' => '',
    ],





];
