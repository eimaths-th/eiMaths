# User role delegation

Account management and operational permissions are separate. A user with `user-create` can create an account with an existing assignable role in their permitted centre even when they do not hold all of that role's ordinary operational permissions. `user-edit` follows the same rule when changing an existing user's role. The role's configured permission set is assigned intact.

For example, a role named Teacher can include teaching, Quiz or finance permissions as configured by the role administrator. Role names do not impose a fixed job description. Adding a permission to a shared role affects every account assigned to that role.

## Boundaries retained

- Add/edit endpoints still require their respective account-management permission.
- The actor must be allowed to manage the destination centre and target user; branch-scoped roles must belong to that centre.
- Admin and SystemAdmin remain restricted to SystemAdmin assignment in HQ.
- Owner-only permissions remain restricted to SystemAdmin. Delegating central permissions still requires central authority and the corresponding central grants.
- The recipient's enabled modules and normal per-operation permission checks still apply. A role's ordinary permissions are not removed simply because a module is currently disabled.
- Changing a role's permission definition is separate from assigning an existing role. This change does not relax Role Management authorization.
- Dropdown and save operations use the same assignment rules. Current role/department preservation and asynchronous form-loading guards remain in place.

## Deploy

Deploy the backend changes, then clear cached application configuration/routes/views:

```sh
php artisan optimize:clear
```

No database migration or branch-specific Teacher provisioning is needed. Reopen Add User, select the centre, and choose the existing Teacher role (or another configured role suitable for the account).

**The previous `branch:teacher-role ... --apply` instructions are superseded.** That legacy command now exits with a clear retirement message and never creates or trims a role. Existing `Teacher (SB)` or other scoped roles remain compatible and are not automatically deleted, expanded, or assigned to a different user. Use the existing Teacher role for new accounts when that is the permission set you intend; review any previously created limited roles separately before changing their users' access.

## Verify

1. With a branch administrator that has `user-create` but lacks the Teacher role's Quiz/finance permissions, reopen Add User. The ordinary Teacher role should now be available.
2. Create a test account with that role and confirm it receives the entire configured permission set; the creator's permissions and shared role definition must remain unchanged.
3. For the existing Pending Receipt workflow, configure the relevant operational permissions (including `receipt-list` and `order-edit`) and enable the finance module. The label `receipt-create` alone does not cover that workflow.
4. With `user-edit`, change a same-branch user's ordinary role or department and confirm the update succeeds. Without `user-create`/`user-edit`, the matching operation must remain forbidden.
5. Verify central/owner roles and foreign-branch users/roles remain blocked, including forged requests. A central administrator lacking `all-centre` must not be able to delegate that central grant through a custom role.
6. A role may retain Quiz grants while the Quiz module is disabled; the recipient must still be unable to use Quiz until the module is enabled.

Automated PHP feature tests run on SQLite `:memory:` with fake external services, not on the test server database. The local SB administrator can be checked against the existing Teacher role without creating a real account.

## Verification result (2026-10-15)

- 56 tests / 482 assertions passed: UserAssignableRoleTest + UserDepartmentUpdateTest (41/359), retired-command and teacher-identity tests (9/70), and six staff-transfer boundary regressions (6/53).
- The operational-role case creates a user through POST /users and checks the complete receipt-related permission bundle is effective when the recipient's modules are enabled. It does not execute a financial transaction.
- A read-only check against the local SB administrator confirms the existing Teacher is assignable despite four Quiz permissions not held by the creator. Teacher retains all eight configured grants. No local users or role definitions were changed by this policy revision.
- Test-server deployment has not been performed. After deployment and cache clearing, reopen Add User and select the existing Teacher role.
