<?php
/**
 * Created by PhpStorm.
 * User: punam
 * Date: 9/15/16
 * Time: 12:55 PM
 */
namespace SelfService;

use Zend\Router\Http\Segment;
use Application\Controller\ControllerFactory;

return [
    'router' => [
        'routes' => [
            'myattendance' => [
                'type' => Segment::class,
                'options' => [
                    'route' => '/selfservice/myattendance[/:action[/:id]]',
                    'constants' => [
                        'action' => '[a-zA-Z][a-zA-Z0-9_-]*',
                        'id' => '[0-9]+',
                    ],
                    'defaults' => [
                        'controller' => Controller\MyAttendance::class,
                        'action' => 'index',
                    ]
                ],
            ],
            'holiday' => [
                'type' => Segment::class,
                'options' => [
                    'route' => '/selfservice/holiday[/:action[/:id]]',
                    'constants' => [
                        'action' => '[a-zA-Z][a-zA-Z0-9_-]*',
                        'id' => '[0-9]+',
                    ],
                    'defaults' => [
                        'controller' => Controller\Holiday::class,
                        'action' => 'index',
                    ]
                ],
            ],
            'leave' => [
                'type' => Segment::class,
                'options' => [
                    'route' => '/selfservice/leave[/:action[/:id]]',
                    'constants' => [
                        'action' => '[a-zA-Z][a-zA-Z0-9_-]*',
                        'id' => '[0-9]+',
                    ],
                    'defaults' => [
                        'controller' => Controller\Leave::class,
                        'action' => 'index',
                    ]
                ],
            ],
            'leaverequest' => [
                'type' => Segment::class,
                'options' => [
                    'route' => '/selfservice/leaverequest[/:action[/:id]]',
                    'constants' => [
                        'action' => '[a-zA-Z][a-zA-Z0-9_-]*',
                        'id' => '[0-9]+',
                    ],
                    'defaults' => [
                        'controller' => Controller\LeaveRequest::class,
                        'action' => 'index',
                    ]
                ],
            ],
            'attendancerequest' => [
                'type' => Segment::class,
                'options' => [
                    'route' => '/selfservice/attendancerequest[/:action[/:id]]',
                    'constants' => [
                        'action' => '[a-zA-Z][a-zA-Z0-9_-]*',
                        'id' => '[0-9]+',
                    ],
                    'defaults' => [
                        'controller' => Controller\AttendanceRequest::class,
                        'action' => 'index',
                    ]
                ],
            ],
            'service' => [
                'type' => Segment::class,
                'options' => [
                    'route' => '/selfservice/service[/:action[/:id]]',
                    'constants' => [
                        'action' => '[a-zA-Z][a-zA-Z0-9_-]*',
                        'id' => '[0-9]+',
                    ],
                    'defaults' => [
                        'controller' => Controller\Service::class,
                        'action' => 'index',
                    ]
                ],
            ],
            'profile' => [
                'type' => Segment::class,
                'options' => [
                    'route' => '/selfservice/profile[/:action[/:tab]]',
                    'constants' => [
                        'action' => '[a-zA-Z][a-zA-Z0-9_-]*',
                        'id' => '[0-9]+',
                    ],
                    'defaults' => [
                        'controller' => Controller\Profile::class,
                        'action' => 'index',
                    ]
                ],
            ],

        ],
    ],
    'navigation' => [
        'myattendance' => [
            [
                'label' => 'Attendance',
                'route' => 'myattendance',
            ],
            [
                'label' => 'Attendance',
                'route' => 'myattendance',
                'pages' => [
                    [
                        'label' => 'List',
                        'route' => 'myattendance',
                        'action' => 'index',
                    ],
                    [
                        'label' => 'Entry',
                        'route' => 'myattendance',
                        'action' => 'add',
                    ],
                    [
                        'label' => 'Edit',
                        'route' => 'myattendance',
                        'action' => 'edit',
                    ],
                ],
            ],
        ],
        'holiday' => [
            [
                'label' => 'Holiday',
                'route' => 'holiday',
            ],
            [
                'label' => 'Holiday',
                'route' => 'holiday',
                'pages' => [
                    [
                        'label' => 'List',
                        'route' => 'holiday',
                        'action' => 'index',
                    ],
                    [
                        'label' => 'Add',
                        'route' => 'holiday',
                        'action' => 'add',
                    ],
                    [
                        'label' => 'Edit',
                        'route' => 'holiday',
                        'action' => 'edit',
                    ],
                ],
            ],
        ],
        'leave' => [
            [
                'label' => 'Leave',
                'route' => 'leave',
            ],
            [
                'label' => 'Leave',
                'route' => 'leave',
                'pages' => [
                    [
                        'label' => 'List',
                        'route' => 'leave',
                        'action' => 'index',
                    ],
                    [
                        'label' => 'Add',
                        'route' => 'leave',
                        'action' => 'add',
                    ],
                    [
                        'label' => 'Edit',
                        'route' => 'leave',
                        'action' => 'edit',
                    ],
                ],
            ],
        ],
        'leaverequest' => [
            [
                'label' => 'Leave Request',
                'route' => 'leaverequest',
            ],
            [
                'label' => 'Leave Request',
                'route' => 'leaverequest',
                'pages' => [
                    [
                        'label' => 'List',
                        'route' => 'leaverequest',
                        'action' => 'index',
                    ],
                    [
                        'label' => 'Add',
                        'route' => 'leaverequest',
                        'action' => 'add',
                    ],
                    [
                        'label' => 'Edit',
                        'route' => 'leaverequest',
                        'action' => 'edit',
                    ],
                    [
                        'label' => 'Detail',
                        'route' => 'leaverequest',
                        'action' => 'view',
                    ],
                ],
            ],
        ],
        'attendancerequest' => [
            [
                'label' => 'Attendance Request',
                'route' => 'attendancerequest',
            ],
            [
                'label' => 'Attendance Request',
                'route' => 'attendancerequest',
                'pages' => [
                    [
                        'label' => 'List',
                        'route' => 'attendancerequest',
                        'action' => 'index',
                    ],
                    [
                        'label' => 'Add',
                        'route' => 'attendancerequest',
                        'action' => 'add',
                    ],
                    [
                        'label' => 'Edit',
                        'route' => 'attendancerequest',
                        'action' => 'edit',
                    ],
                    [
                        'label' => 'Detail',
                        'route' => 'attendancerequest',
                        'action' => 'view',
                    ],
                ],
            ],
        ],
        'service' => [
            [
                'label' => 'Service',
                'route' => 'service',
            ],
            [
                'label' => 'Service',
                'route' => 'service',
                'pages' => [
                    [
                        'label' => 'History',
                        'route' => 'service',
                        'action' => 'index',
                    ],
                    [
                        'label' => 'Detail',
                        'route' => 'service',
                        'action' => 'view',
                    ],
                    [
                        'label' => 'Edit',
                        'route' => 'service',
                        'action' => 'edit',
                    ],
                ],
            ],
        ],
        'profile' => [
            [
                'label' => 'Profile',
                'route' => 'profile',
            ],
            [
                'label' => 'Profile',
                'route' => 'profile',
                'pages' => [
                    [
                        'label' => 'Detail',
                        'route' => 'profile',
                        'action' => 'index',
                    ],
                    [
                        'label' => 'Add',
                        'route' => 'profile',
                        'action' => 'add',
                    ],
                    [
                        'label' => 'Edit',
                        'route' => 'profile',
                        'action' => 'edit',
                    ],
                ],
            ],
        ],
    ],

    'controllers' => [
        'factories' => [
            Controller\MyAttendance::class => ControllerFactory::class,
            Controller\Holiday::class => ControllerFactory::class,
            Controller\Leave::class => ControllerFactory::class,
            Controller\LeaveRequest::class => ControllerFactory::class,
            Controller\AttendanceRequest::class => ControllerFactory::class,
            Controller\Profile::class => ControllerFactory::class,
            Controller\Service::class => ControllerFactory::class
        ],
    ],
    'view_manager' => [
        'template_path_stack' => [
            __DIR__ . '/../view',
        ]
    ]
];