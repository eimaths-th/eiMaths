# Branch user creation: scoped Teacher role

`user-create` permits creating an account. The selected role is checked separately: a branch administrator cannot grant permissions they do not have. A shared Teacher role containing Quiz permissions can therefore be rejected even when the administrator can create users. Disabling the Quiz module does not make those raw grants safe to assign; the module could be enabled later.

## Deployment

Deploy this change as a complete backend release, including `TeacherRole`, the command, controller/support changes, Blade templates, translations and routes. No schema migration is required for this fix. Clear Laravel caches after deployment:

```sh
php artisan optimize:clear
```

Preview the branch Teacher role using the actual SB administrator's user ID or email on that server. For example, if the SB administrator is user 84:

```sh
php artisan branch:teacher-role SB --admin=84
php artisan branch:teacher-role SB --admin=84 --apply
```

The first command is read-only. Check the displayed centre, administrator and included/omitted permissions before applying. The second creates `Teacher (SB)` scoped to SB. It does not change the shared Teacher role, BranchAdmin permissions, existing user assignments, or module settings. The command refuses an empty permission set, inactive/wrong-branch authority, and a conflicting existing role; it does not overwrite a role customized later.

Re-running with identical role configuration is safe. Do not use the broader `branch:roles --apply` command as a substitute: that command changes shared role templates.

## Verification on the test server

1. Sign in as the SB administrator, reopen Add User, select SB and confirm `Teacher (SB)` is available. Roles beyond the administrator's assignment authority must be absent.
2. Create a test teacher with a unique test email and the intended SB department. Verify successful creation, centre/department and role in the Users table.
3. Confirm the new teacher is available in learning history/Fast History teacher selectors and the dashboard teacher list/count.
4. Reopen an existing user for Edit. Its current single role and department should remain selected; saving a name/department change must not reassign the role.
5. Confirm another centre's role and central administrator roles cannot be submitted by SB, even by modifying the request. Opening a form must not create roles or change permissions.

## Implementation notes

- Add fetches assignable roles for the selected centre on every open/change. Save waits for both department and role lookups. A new role must be explicitly selected.
- Edit retains an existing single role for profile-only edits, while sensitive changes to another account and role changes still require assignment authority.
- Teacher identity includes the original global Teacher and canonical scoped roles created by the command. It does not infer role identity from a display name or grant additional permissions.
- A scoped role remains tied to its centre. Staff transfers retain the existing restriction against carrying a source-centre role to another centre; use an appropriate destination role through an authorized workflow.

Automated verification uses SQLite `:memory:` for PHP feature tests and a JavaScript transport harness plus a real jQuery/Select2 browser fixture. It does not create users on the test server.

## Verified on 2026-10-15

- 121 PHP tests passed (1,298 assertions): UserAssignableRoleTest 13, BranchTeacherRoleTest 17, UserDepartmentUpdateTest + BranchWorkflowsTest 46, and FastHistoryTest + HistoricalReferenceIsolationTest + AffiliateEmployeeEligibilityTest + AffiliateEmployeeRedirectTest 45.
- 15 JavaScript handler tests and 10 real jQuery/Select2 browser cases passed.
- On the local development database, SB (branch ID 6, administrator ID 84) now has `branch-6-teacher` / `Teacher (SB)` with `ebook-activity-list`, `history-list`, `parent-list`, and `student-list`. Four Quiz grants from shared Teacher were excluded. Read-back verified that the SB administrator can assign the scoped role, cannot select the excessive shared Teacher, and no existing user was reassigned.
- The local Add User form can select the new role after loading SB. The live test server has not been deployed or provisioned by this change; run the commands above there after deployment using its actual SB administrator.
