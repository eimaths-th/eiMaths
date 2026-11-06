# Student Activity History Execution Record

## Identity

- **Plan:** `docs/plans/0017-student-activity-history.md`
- **Source:** `docs/specs/0005-student-activity-history-design.md`
- **Repository and workspace:** `eimaths_backend`, main working tree
- **Branch and start revision:** `main` at `49060fe9`, with uncommitted Guest Reports changes preserved

## Authority

- **Plan approval and branch:** The user replied "ok ทำใน main" after the plan was presented.
- **Execution method:** Native implementation. The user did not select A or B, so no independent reviewer was used.

## Baseline

The full suite at `49060fe9` had 64 failing tests. One was the Guest Report test replaced by the approved Guest Reports change. The other 63 also fail with the Guest Reports change applied.

## Progress

| Task | State | Evidence |
| --- | --- | --- |
| 1. Store login events | Completed | Missing table RED, then GREEN |
| 2. Record login and logout | Completed | RED, then 38 login and session tests passed |
| 3. Show login history with access control | Completed | Missing route RED, then GREEN |
| 4. Show quiz attempts | Completed | Missing section RED after the fixture correction, then GREEN |
| 5. Add the student list button | Completed | Missing link RED, then GREEN |
| 6. Delete old login events | Completed | Missing command and schedule RED, then GREEN |
| 7. Verify the whole change | Completed | Full suite on the final working tree: 1,328 tests, 62 failures, 1 error, 3 skipped. All 63 are in the baseline, and none is a new test. |

Three temporary mutations were each detected and restored: the `type_user` filter, the prune boundary, and the permission middleware.

## Decisions

- **Recorder write path:** The plan writes with `DB::table` instead of the Eloquent model, because `BranchContext::validateWrite` could refuse a write while a staff actor from another branch is active. The stored data matches the specification.
- **Other-branch staff test:** The planned test assumed the login would succeed. `Contact::find` is scoped by the staff actor, so the student is rejected as invalid before an event exists. The test was replaced by a same-branch test and a test that records this existing behavior. The specification does not require recording this case. If this ruling is wrong, the cost is a missing feature, not lost data, because no login happened. The rejection itself is existing behavior outside this plan.
- **Page test traits:** `StudentActivityPageTest` uses `insteadof` for `beforeRefreshingDatabase`, matching `QuizResultCompatibilityTest`.
- **Quiz fixtures:** `tests.user_id` has a foreign key to `users`, so student fixtures create a user with the same ID, matching `QuizResultAccessTest`.
- **Table headers:** Added during self-review with RED and GREEN because the tables had no column labels. This stays within the page scope.

## Review

- **Type:** Self-review, because no independent reviewer was authorized.
- **Findings:** One Low accessibility finding, fixed as recorded above.
- **Declined to judge:** The privacy policy and the production scheduler, which the specification assigns to release checks.
- **Rounds:** One.

## Not Verified

- The page in a real browser.
- The migrations on MySQL or production data.
- The privacy policy review and the production scheduler.
