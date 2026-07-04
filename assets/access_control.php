<?php

declare(strict_types=1);

function normalize_role(string $role): string
{
    $role = trim($role);
    $lower = strtolower($role);

    return match ($lower) {
        'user' => 'Parent/Guardian',
        'parent', 'guardian', 'parent/guardian' => 'Parent/Guardian',
        'guest', 'visitor', 'guest/visitor' => 'Guest/Visitor',
        'secretary', 'clerk', 'secretary/clerk' => 'Secretary/Clerk',
        'teacher' => 'Teacher',
        'sunday school coordinator', 'coordinator' => 'Sunday School Coordinator',
        'super administrator', 'super admin', 'administrator' => 'Super Administrator',
        default => $role,
    };
}

function is_public_page(string $page): bool
{
    $publicPages = [
        'index.php',
        'login.php',
        'signup.php',
        'announcements.php',
        'calendar.php',
    ];

    return in_array($page, $publicPages, true);
}

function get_page_role_map(): array
{
    return [
        'dashboard.php' => [
            'Super Administrator',
            'Sunday School Coordinator',
            'Teacher',
            'Secretary/Clerk',
            'Parent/Guardian',
        ],
        'teachers.php' => [
            'Super Administrator',
            'Sunday School Coordinator',
        ],
        'classes.php' => [
            'Super Administrator',
            'Sunday School Coordinator',
        ],
        'children.php' => [
            'Super Administrator',
            'Sunday School Coordinator',
            'Teacher',
            'Secretary/Clerk',
        ],
        'attendance.php' => [
            'Super Administrator',
            'Sunday School Coordinator',
            'Teacher',
        ],
        'bible-stories.php' => [
            'Super Administrator',
            'Sunday School Coordinator',
            'Teacher',
        ],
        'memory-verses.php' => [
            'Super Administrator',
            'Sunday School Coordinator',
            'Teacher',
        ],
        'songs.php' => [
            'Super Administrator',
            'Sunday School Coordinator',
            'Teacher',
        ],
        'awards.php' => [
            'Super Administrator',
            'Sunday School Coordinator',
            'Teacher',
        ],
        'children_handler.php' => [
            'Super Administrator',
            'Sunday School Coordinator',
            'Teacher',
            'Secretary/Clerk',
        ],
        'teachers_handler.php' => [
            'Super Administrator',
            'Sunday School Coordinator',
        ],
        'classes_handler.php' => [
            'Super Administrator',
            'Sunday School Coordinator',
        ],
        'attendance_handler.php' => [
            'Super Administrator',
            'Sunday School Coordinator',
            'Teacher',
        ],
        'bible-stories_handler.php' => [
            'Super Administrator',
            'Sunday School Coordinator',
            'Teacher',
        ],
        'memory-verses_handler.php' => [
            'Super Administrator',
            'Sunday School Coordinator',
            'Teacher',
        ],
        'songs_handler.php' => [
            'Super Administrator',
            'Sunday School Coordinator',
            'Teacher',
        ],
        'awards_handler.php' => [
            'Super Administrator',
            'Sunday School Coordinator',
            'Teacher',
        ],
    ];
}

function get_management_role_map(): array
{
    return [
        'children' => [
            'Super Administrator',
            'Sunday School Coordinator',
            'Teacher',
            'Secretary/Clerk',
        ],
        'teachers' => [
            'Super Administrator',
            'Sunday School Coordinator',
            'Teacher',
        ],
        'classes' => [
            'Super Administrator',
            'Sunday School Coordinator',
            'Teacher',
        ],
        'attendance' => [
            'Super Administrator',
            'Sunday School Coordinator',
            'Teacher',
        ],
        'announcements' => [
            'Super Administrator',
            'Sunday School Coordinator',
            'Teacher',
        ],
        'calendar' => [
            'Super Administrator',
            'Sunday School Coordinator',
            'Teacher',
        ],
        'bible-stories' => [
            'Super Administrator',
            'Sunday School Coordinator',
            'Teacher',
        ],
        'memory-verses' => [
            'Super Administrator',
            'Sunday School Coordinator',
            'Teacher',
        ],
        'songs' => [
            'Super Administrator',
            'Sunday School Coordinator',
            'Teacher',
        ],
        'awards' => [
            'Super Administrator',
            'Sunday School Coordinator',
            'Teacher',
        ],
        'certificates' => [
            'Super Administrator',
            'Sunday School Coordinator',
            'Teacher',
        ],
    ];
}

function can_manage_module(string $module, string $role): bool
{
    $role = normalize_role($role);
    $map = get_management_role_map();

    if (!isset($map[$module])) {
        return false;
    }

    return in_array($role, $map[$module], true);
}

function can_access_page(string $page, string $role): bool
{
    if (is_public_page($page)) {
        return true;
    }

    $role = normalize_role($role);
    $map = get_page_role_map();

    if (!isset($map[$page])) {
        return true;
    }

    return in_array($role, $map[$page], true);
}
