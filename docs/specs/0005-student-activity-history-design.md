# Student Activity History Design

## Summary

Add a back-office page that shows one student's login history and quiz attempts. Login history is new data. Quiz attempts already exist in the `tests` table and are read from there.

The work applies to `eimaths_backend`.

## Brief

- **Intended outcome:** Staff can open a student and see when the student logged in or logged out and which quizzes the student took.
- **Why it matters:** The user wants this information available to view. No further use was stated.
- **Intended users:** Central staff and branch staff who hold the new permission.
- **Constraints:** Student login and logout must not depend on history being written. Branch data isolation must hold. Personal data must stay limited to the fields in this specification.
- **Success criteria:** Automated tests cover every behavior in the Testing section, and an authorized staff member can read the page for a student in their scope.

## Goals

- Record each successful student login and each student-initiated logout.
- Show login history and quiz attempts on one page for each student.
- Control page access with a dedicated permission.
- Delete login history automatically after 365 days.

## Non-Goals

- Alerts, reports, exports, or a combined page across students.
- Recording failed logins, IP addresses, devices, or session durations.
- Recording guest activity. Guests use `guest_quizzers` and `guest_tests`, which are separate from students.
- Backfilling login history from before release.
- Changing how quiz attempts are stored, kept, or deleted.

## Anti-Goals

- A student being unable to log in or log out because history could not be written.
- Staff reading a student outside their authorized branch scope.
- A second copy of quiz attempt data that can disagree with `tests`.
- Keeping login history indefinitely.

## Decisions

### User Decisions

- Branch staff see only students in their own branch, and central staff with cross-branch access see all branches.
- Login history is kept for one year and then deleted automatically.
- The feature is for viewing only.

### Agent Decisions

- Add one new table, `student_login_events`, and read quiz attempts from `tests`.
- Exclude IP addresses and devices because viewing does not require them.
- Exclude failed logins because a mistyped code may not identify a student.
- Name the permission `student-activity-list`. Its `student-` prefix places it in the existing `students` module and permission group.
- Grant the permission to the `Admin` and `BranchAdmin` roles during migration. `Teacher` is not granted it.
- Delete a student's login history when the student is deleted.

### Approaches Considered

- **Chosen: a new login table plus reads from `tests`.** It adds only the missing data and keeps quiz attempts in one place.
- **Rejected: `branch_audit_logs`.** Its authentication flow is limited to staff accounts on the `web` guard, and the Activity Log page would mix student records with staff audit records.
- **Rejected: a combined activity table for logins and quiz attempts.** It would duplicate `tests`.

## Data Model

Create `student_login_events`:

| Column | Definition |
| --- | --- |
| `id` | Primary key |
| `contact_id` | Foreign key to `contacts.id`, matching that column's type, with cascade on delete |
| `event_type` | String holding `LOGIN` or `LOGOUT` |
| `occurred_at` | Timestamp, set by the application at the moment of the event |

Add indexes on (`contact_id`, `occurred_at`) and on `occurred_at`. The table has no `created_at` or `updated_at`.

Rollback drops the table.

## Recording

Add `App\Domain\Elearning\StudentLoginEventRecorder` with one public method for each event type.

- `ContactController::studentsCheckLogin` records `LOGIN` after `Auth::guard('student')->login()` succeeds. This is the only student login path in `app`.
- `ContactController::studentsLogout` records `LOGOUT` inside the existing authenticated-student branch.
- No event is recorded for a failed login, an expired session, or the session replacement handled by `EnsureCurrentStudentSession`.

The recorder catches any write failure, logs `STUDENT_LOGIN_EVENT_FAILED` with `contact_id` and `event_type` only, and returns normally.

## Page and Access

- **Route:** `GET /contacts/{contact}/activity`, named `contacts.activity.index`, inside the authenticated staff route group.
- **Controller:** `App\Http\Controllers\StudentActivityController::Index`, guarded by `permission:student-activity-list`.
- **Branch scope:** Resolve the student through the existing `Contact` branch scope. A student outside the actor's scope returns the same response the application gives for other out-of-scope contacts.
- **Model scope:** Add `App\Models\StudentLoginEvent` to `config/branch.php` with `Contact` as its parent through `contact_id`.
- **Entry point:** Add a "Student activity" button to the student list action column only when the actor holds the permission.
- **Login section:** Newest first, 20 rows per page.
- **Quiz section:** Shown only when the actor also holds `quiz-results-list`. It lists attempts where `type_user` is `student` and `user_id` is the student's ID, newest first, 20 rows per page, with each section keeping its own page parameter. Values use the same presentation as the existing quiz results page.
- **Answer link:** Shown only when the actor holds `quiz-results-view`, and it opens `backoffice.quiz-results.show`.

A student who moves to another branch keeps their history, so the new branch can read earlier records.

Text sketch:

```text
Student activity: Somchai (ST-0123)
[ Logins ]
06/11 09:48  Logout
06/11 09:12  Login
[ Quizzes ]
06/11 09:15  P3 Term 1  8/10  12 min  [View answers]
```

## Permission

- Register `student-activity-list` on the `web` guard in a migration and give it to the `Admin` and `BranchAdmin` roles when they exist. Rollback removes the permission.
- Add it to the `BranchAdmin` baseline in `config/branch-role-templates.php`.
- Add its label to `config/permission-ui.php` and to the `user-access` language files for `en` and `th`.
- Add the page text to the `students` language files for `en` and `th`.

## Retention

- Add `config/student-activity.php` with `login_retention_days` set to `365`.
- Add a `student-activity:prune-login-events` command that deletes rows whose `occurred_at` is older than the retention period, in batches.
- Schedule it daily at 02:45 Asia/Bangkok with `withoutOverlapping()` in `app/Console/Kernel.php`.
- The command logs the deleted row count and logs a failure with an error code and no personal data.

## Failure Handling

| Failure | Behavior |
| --- | --- |
| Login event write fails | The student logs in normally and a warning is logged |
| Logout event write fails | The student logs out normally and a warning is logged |
| Student deleted | The database deletes that student's login events |
| Prune command fails | Rows remain until the next run and the failure is logged |
| Student has no history | Each section shows an empty state |

## Security and Privacy

- The table stores only the student identifier, event type, and time.
- Logs from this feature contain identifiers, not names or contact details.
- Page text is escaped by the existing Blade output encoding.
- The published privacy policy at the configured `PRIVACY_NOTICE_URL` has not been reviewed for this feature. The privacy owner must confirm that it covers login history and the one-year period before release.

## Testing

Feature tests use the project's SQLite in-memory configuration.

- A successful login records one `LOGIN` event.
- A failed login records nothing.
- A logout records one `LOGOUT` event for that student.
- Login and logout still succeed when the event write fails.
- An actor with the permission reads a student in their branch.
- An actor without the permission receives 403.
- A branch actor cannot read a student from another branch.
- A cross-branch central actor reads a student from another branch.
- The student list shows the button only with the permission.
- The quiz section requires `quiz-results-list`, and the answer link requires `quiz-results-view`.
- The quiz section excludes attempts by a staff user whose ID equals the student's ID.
- The prune command deletes rows older than 365 days and keeps newer rows, including the exact boundary.
- Deleting a student deletes their login events.
- The migration grants the permission to `Admin` and `BranchAdmin` and not to `Teacher`.

## Operations

- Login history begins at release. Quiz attempts are available from existing data.
- Logout rows exist only when a student logs out explicitly, so the page does not represent session length.
- Automatic deletion depends on the Laravel scheduler running in production. This has not been verified and must be confirmed before release.
