<?php

return [

    [
        'title' => 'Dashboard',
        'icon' => 'fas fa-tachometer-alt fa-spin',
        'route' => 'dashboard',
        'permission' => '',
    ],

    [
        'title' => 'Users',
        'icon' => 'fas fa-users',
        'route' => 'admin.users.index',
        'permission' => '',
    ],

    [
        'title' => 'Customers',
        'icon' => 'fas fa-user-tie',
        'route' => 'admin.customers.index',
        'permission' => '',
    ],

    [
        'title' => 'Vehicles',
        'icon' => 'fas fa-car',
        'route' => 'admin.vehicles.index',
        'permission' => '',
    ],
    // [
    //     'title' => 'Vehicle Details',
    //     'icon' => 'fas fa-info-circle',
    //     'route' => 'admin.vehicle_details.index',
    //     'permission' => '',
    // ],

    [
        'title' => 'Crew Profiles',
        'icon' => 'fas fa-user-tie',
        'route' => 'admin.crew_profiles.index',
        'permission' => '',
    ],



    [
        'title' => 'Vehicle Bookings',
        'icon' => 'fas fa-calendar-alt',
        'route' => 'admin.vehicle_bookings.index',
        'permission' => '',
    ],

    [
        'title' => 'GPS',
        'icon' => 'fas fa-map-marker-alt',
        'route' => 'admin.gpsdashboard',
        'permission' => '',
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
                'permission' => '',
            ],
            [
                'title' => 'Transactions',
                'icon' => 'fas fa-exchange-alt',
                'route' => 'admin.petrol_pump_transactions.index',
                'permission' => '',
            ],
        ],
    ],



];
