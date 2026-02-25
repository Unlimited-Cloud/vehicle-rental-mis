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
        'title' => 'Vehicle Assignments',
        'icon' => 'fas fa-truck',
        'route' => 'admin.vehicle_assignments.index',
        'permission' => '',
    ],


];
