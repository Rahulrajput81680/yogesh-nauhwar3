<?php


if (!defined('ADMIN_INIT')) {
  die('Direct access not permitted');
}

// ── Role → Permission map ────────────────────────────────────────────────────
$role_permissions = [

  // Superadmin: wildcard bypasses every explicit check
  'superadmin' => ['*'],

  // Admin: full content rights + full user management
  'admin' => [
    'blog_view',
    'blog_create',
    'blog_edit',
    'blog_delete',
    'blog_restore',
    'gallery_view',
    'gallery_create',
    'gallery_edit',
    'gallery_delete',
    'hero_view',
    'hero_edit',
    'hero_create',
    'hero_delete',
    'tours_view',
    'tours_create',
    'tours_edit',
    'tours_delete',
    'tours_restore',
    'contact_view',
    'contact_delete',
    'events_view',
    'events_create',
    'events_edit',
    'events_delete',
    'our_work_view',
    'our_work_create',
    'our_work_edit',
    'our_work_delete',
    'media_view',
    'media_create',
    'media_edit',
    'media_delete',
    'forms_view',
    'forms_create',
    'forms_edit',
    'forms_delete',
    'users_view',
    'users_create',
    'users_edit',
    'users_delete',
    'profile_view',
    'profile_edit',
  ],

  // Editor: create & edit, no permanent deletes, no user management
  'editor' => [
    'blog_view',
    'blog_create',
    'blog_edit',
    'gallery_view',
    'gallery_create',
    'gallery_edit',
    'hero_view',
    'tours_view',
    'tours_create',
    'tours_edit',
    'contact_view',
    'events_view',
    'events_create',
    'events_edit',
    'our_work_view',
    'our_work_create',
    'our_work_edit',
    'media_view',
    'media_create',
    'media_edit',
    'forms_view',
    'forms_create',
    'profile_view',
    'profile_edit',
  ],

  // Teacher: read most areas + can submit blog/forms content
  'teacher' => [
    'blog_view',
    'blog_create',
    'blog_edit',
    'gallery_view',
    'tours_view',
    'forms_view',
    'forms_create',
    'contact_view',
    'events_view',
    'our_work_view',
    'media_view',
    'profile_view',
    'profile_edit',
  ],
];

// ── Public helpers ───────────────────────────────────────────────────────────

/**
 * Check whether the logged-in user holds a given permission.
 *
 * Returns true unconditionally when the 'roles' module is disabled,
 * keeping all simple/legacy sites fully functional.
 *
 * @param  string $permission  e.g. 'blog_delete', 'users_view'
 * @return bool
 */
function has_permission(string $permission): bool
{
  global $role_permissions;

  // Roles module off → everyone is treated as superadmin
  if (!is_module_enabled('roles')) {
    return true;
  }

  $role = $_SESSION['admin_role'] ?? 'editor';

  // Superadmin always passes
  if ($role === 'superadmin') {
    return true;
  }

  $granted = $role_permissions[$role] ?? [];

  // Explicit wildcard entry
  if (in_array('*', $granted, true)) {
    return true;
  }

  return in_array($permission, $granted, true);
}

/**
 * Abort execution with a redirect if the user lacks the given permission.
 * Must be called after session / login checks.
 *
 * @param string $permission
 */
function require_permission(string $permission): void
{
  if (!has_permission($permission)) {
    set_flash('error', 'You do not have permission to perform this action.');
    redirect(ADMIN_URL . '/dashboard.php');
    exit;
  }
}

/**
 * Return a human-readable label for a role slug.
 */
function role_label(string $role): string
{
  $labels = [
    'superadmin' => 'Super Admin',
    'admin' => 'Admin',
    'editor' => 'Editor',
    'teacher' => 'Teacher',
  ];
  return $labels[$role] ?? ucfirst($role);
}

/**
 * Return a Bootstrap badge HTML snippet coloured by role.
 */
function role_badge(string $role): string
{
  $classes = [
    'superadmin' => 'danger',
    'admin' => 'primary',
    'editor' => 'info',
    'teacher' => 'success',
  ];
  $class = $classes[$role] ?? 'secondary';
  return '<span class="badge bg-' . $class . ' role-badge badge-role-' . escape($role) . '">'
    . escape(role_label($role))
    . '</span>';
}

/**
 * Allowed roles list (used in forms).
 */
function get_roles(): array
{
  return [
    'superadmin' => 'Super Admin',
    'admin' => 'Admin',
    'editor' => 'Editor',
    'teacher' => 'Teacher',
  ];
}
