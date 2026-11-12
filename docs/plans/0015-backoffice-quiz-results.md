# Back-office Quiz Results Implementation Plan

> **REQUIRED EXECUTION SKILL:** After this plan is approved, use `git-worktrees` to inspect the authorized in-place workspace, then `executing-plans` with native execution and one independent final reviewer. The user has already selected work on `demo`; do not create a worktree or switch to `main`.

- **Goal:** Staff list and review quiz results within the back office only when their Roles grant the appropriate capabilities, while learners retain their own-result experience.
- **Architecture:** One coupled result-access feature: register permissions, centralize staff/personal authorization, expose dedicated back-office endpoints, retain guarded legacy URLs, and reuse result-card presentation in separate shells.
- **Tech Stack:** Laravel 10, Blade, existing Spatie permissions, existing branch scopes, PHPUnit 10 with SQLite in-memory integration tests.
- **Source:** Approved `docs/specs/0003-backoffice-quiz-results-design.md`. The user approved the written specification and move to planning with `ok` after its presentation.

## Global Constraints

- No additions or changes to `.env`, `.env.example`, environment variables, feature flags or runtime switches.
- Preserve existing branch/cross-branch decisions, module eligibility and owner/central semantics. Do not grant permissions based on role names.
- Preserve stored attempts, scores, question/answer semantics, student solution-video assignment checks and unrelated learner/Leaderboard behavior.
- Preserve all current plan-0014 changes. At planning time, the working tree includes edited Home/Leaderboard/My Results/quiz-layout views plus portal CSS/tests and plan documents. Do not reset, stash, overwrite or move them.
- No commit, push, deployment, live/shared database migration, dependency installation or unrelated repairs. Migration tests must assert `testing`, SQLite and `:memory:` before database refresh.
- All new functions/test methods use PascalCase, locals use snake_case, CSS classes use kebab-case; framework method conventions take precedence. Documentation is English, user communication Thai.
- Follow `test-driven-development` for production behavior and its refactor contract for unchanged presentation extraction. Observe a real RED before each new authorization behavior, then GREEN. Do not mock permissions, branch scopes or model binding in access tests.

## Verified Seams and Planning Rulings

- Repository: `C:/Users/user/github/eimaths/eimaths_backend`, branch `demo`, HEAD `2bf2226c93d8cd9fffaae995aa87f4872b25db8c` plus the preserved working-tree theme changes.
- `routes/web2.php` owns `tests`, `submit.leaderboard`, `results.show` and the student/user route group. Keep the current student-session middleware on personal routes; canonical staff routes belong to the web-authenticated group.
- `EnforceBranchContext` precedes route binding and calls `BranchRouteAccess::authorize`. This is the existing place for staff pre-binding capability checks; retain it and the scoped binding behavior.
- `BranchContext::scope` already scopes Test through Contact or User according to `type_user`; `eLeanController::tests` additionally uses `whereHas('user')`, which must be replaced with type-aware existence conditions for the new list.
- `ResultController::show` currently checks only student ownership locally; `BranchRouteAccess` applies `quiz-list` to web users for this controller. The new controller is authorized by the new permissions. The legacy result controller must perform explicit own-versus-staff authorization after scoped binding rather than retaining its blanket `quiz-list` precheck.
- `layouts.app` exposes `@yield('style')`, `@stack('styles')`, `@yield('content')` and `@stack('scripts')`. Existing result styles are scoped below `.result-page`; preview styles below `.test-preview-page`. Put these classes on staff content wrappers, never on the admin body.
- Existing answer-page scripts include history/unload interception. Leave that behavior on the learner page only; staff answer review needs MathJax but must retain normal back/breadcrumb navigation.
- `PermissionPresenter::label` first checks `user-access.permission_labels.<name>`. Add localized labels there; existing `quiz-` grouping and module mapping need no new configuration.
- Historical migrations declare `tests.user_id` as a users foreign key even though runtime also uses Contact ids. Do not change this schema in this feature. For integration fixtures, explicitly create distinct User and Contact rows with colliding numeric ids and different branches to prove type-aware isolation; do not disable integrity checks to conceal a production assumption. Live database constraint state is not verified.
- Verified command at planning time: `php vendor/bin/phpunit --do-not-cache-result tests/Feature/Elearning` passes 30 tests / 215 assertions. PHP reports an existing duplicate-exif startup warning. Earlier full-suite attempt failed outside this feature; repeat and report actual results at execution, never claim a clean baseline for the full suite.

## Review Focus

1. List-only and view-only roles must remain independently useful; missing list permission cannot block an otherwise permitted canonical detail request.
2. User/Contact id collisions and cross-branch ids must not select or authorize the wrong participant.
3. Old URLs and the shared filter POST must not bypass checks or change Leaderboard behavior.
4. Student own-result access and video visibility must survive the staff migration, including student-first personal handling and denial of dual-guard staff sessions.
5. Admin CSS, sidebar and normal browser navigation must survive extraction of learner-styled result cards; existing portal-theme changes must remain intact.

## Test Fixture Contract

Create `tests/Support/QuizResultFixtures.php` as a test-only trait shared by the new integration test classes. Use `RefreshDatabase` in those classes and a `beforeRefreshingDatabase` method asserting testing/SQLite/:memory:, following `BranchTeacherRoleTest`. Prevent stray HTTP, fake mail/notifications, and use the existing real models.

- `CreateResultActor(array $permission_names, int $branch_id): User`: active User assigned a custom web Role carrying exactly the specified permissions. Use explicit branch fixtures: HQ id 1, branch A id 2, branch B id 3, all active, quiz module enabled for A/B unless the test changes it.
- `CreateResultAttempt(string $participant_type, int $participant_id, int $attempt_id): Test`: fixed valid quiz `addition`, title `Addition practice`, P1/term 1/section 2, one question and one correct answer, result 1, duration 62 seconds, fixed timestamp. Create correct participant records before invoking this helper. Use real quiz/question/answer relations and test-owned ids.
- `LoginResultStudent(Contact $student): void`: establish a real current session using the existing student session service and the `student` guard; reset the default guard to `web` when simulating simultaneous guards, as the existing student-session tests do. Do not disable session middleware.
- Add a second quiz `subtraction` for filter exclusion, zero-score and zero-question variants, and missing-participant/quiz list fixtures with null relation ids where schema permits. No production factory/helper API is introduced.

## Task 1: Register the two capabilities and preserve role authority

**Files:**
- Create: `database/migrations/2026_11_05_000001_register_quiz_result_permissions.php`
- Create: `tests/Feature/Elearning/QuizResultPermissionMigrationTest.php`
- Create: `tests/Support/QuizResultFixtures.php`
- Modify: `lang/en/user-access.php` and `lang/th/user-access.php` at `permission_labels`

- **Consumes:** Existing Spatie web Roles/Permissions, permission cache and translation presenter.
- **Produces:** `quiz-results-list` and `quiz-results-view` with the exact approved initial mapping.
- **Checkpoint:** Start only after approval of this plan. All migration execution is restricted to the isolated test database; no deployment action is included.

**Production behavior change — use `test-driven-development`:**

1. Write `MapsOnlyEquivalentRoleCapabilities`, `PreservesExistingAssignmentsWhenRepeated`, `DoesNotConvertDirectUserPermissions`, `RollbackPreservesPermissionIdentities`, and `ShowsLocalizedPermissionLabels`.
2. Fixtures: roles carrying neither legacy permission, only `quiz-manage`, only `quiz-list`, both, and an unrelated permission plus a pre-existing new grant. Assert respectively: no new grants; list only; no new grants; list plus view; retained unrelated/manual grants. Include a role named Admin with neither old permission and a custom role with both. Invoke migration `up()` twice and assert exactly one row per new permission. Test `down()` preserves all identities and grants. Test presenter labels in both locales.
3. If the migration file is needed to execute the test, create only its empty `up()`/`down()` scaffold first, with no registration or grants. Run `php vendor/bin/phpunit --do-not-cache-result tests/Feature/Elearning/QuizResultPermissionMigrationTest.php`. Confirm failure for missing migration behavior, not fixture/database setup. Register permissions with `findOrCreate(..., 'web')`, add only approved equivalent grants from existing role assignments, and clear `PermissionRegistrar` cache. Keep `down()` non-destructive with an explanatory comment.
4. Add English labels “View quiz results” and “View quiz answers”, and Thai labels “ดูผลสอบ” and “ดูคำตอบแบบทดสอบ”. Rerun the same command; expect exit 0 and all mapping/idempotency/localization assertions to pass.

## Task 2: Enforce staff/personal boundaries and guarded compatibility routes

**Files:**
- Create: `app/Domain/Elearning/QuizResultAccess.php`
- Create: `app/Domain/Elearning/QuizResultReview.php`
- Create: `app/Http/Controllers/Backoffice/QuizResultsController.php`
- Modify: `app/Http/Controllers/ResultController.php` at `show`
- Modify: `app/Http/Controllers/eLeanController.php` at `tests` and `submitLeaderboard`
- Modify: `app/Support/Branch/BranchRouteAccess.php` at the ResultController check and controller dispatch
- Modify: `routes/web2.php` at staff tests and shared filter routes
- Create: `tests/Feature/Elearning/QuizResultAccessTest.php`
- Create: `tests/Feature/Elearning/QuizResultCompatibilityTest.php`

- **Consumes:** Task 1 permissions, scoped Test binding, existing student ownership/video assignments and list view contract.
- **Produces:** Canonical staff endpoints and legacy redirects, with authorization before answer data is prepared.
- **Checkpoint:** No broad removal of branch middleware or global scopes. Stop if completing this contract would require changing branch ownership or persisted schema.

**Production behavior change — use `test-driven-development`:**

1. Write route-level tests using literal canonical URLs before route registration. Cover guests; student-only sessions; no/list/view/both permissions; disabled accounts; inactive/missing branch; disabled quiz module for branch users; existing central-module exemption; both guards present; and cross-branch access with/without the existing central `all-centre` capability. Expected missing-capability status is 403, missing/out-of-scope staff record 404, guest HTML request redirects to existing login. Student-only staff requests follow web-authentication denial and never render data.
2. Write student own-result and other-result tests with valid current session, id collisions and assignment-dependent video visibility. Write web own-result tests requiring `quiz-list` and matching `type_user=user` plus user id. Write non-owned legacy requests that redirect only with `quiz-results-view`; view-only staff must not need `quiz-list` for the canonical endpoint.
3. Run `php vendor/bin/phpunit --do-not-cache-result tests/Feature/Elearning/QuizResultAccessTest.php`; confirm failures at the expected response/access boundary. Introduce `QuizResultAccess::AuthorizeStaff(string $permission_name): User` to require web identity, deny a simultaneous student identity, and check the existing effective permission API. Keep account/branch validity and scoped record lookup in the existing middleware/model pipeline.
4. Add `QuizResultAccess::AuthorizePersonalOrStaff(Test $test): bool`, returning true only when an authorized non-owned web request should redirect to staff detail. Check the student guard first and preserve its ownership denial. For a web-owned attempt require `quiz-list`; for another person's attempt call staff authorization with `quiz-results-view`. This method authorizes only; it never loads answers or bypasses scopes.
5. In `BranchRouteAccess::authorize`, dispatch `QuizResultsController` methods to the exact list/view capability through `QuizResultAccess` before binding. Remove only the blanket ResultController `quiz-list` requirement; keep final own-versus-staff checks in `ResultController::show`. Preserve every other branch rule. Staff controller entry points also call the same authorization helper before work so direct invocation cannot skip that boundary.
6. Add `QuizResultsController::Index(Request $request, string $quiz_slug = '0'): View`, `Show(Test $test): View`, `RedirectLegacy(Request $request, string $quiz_slug = '0'): RedirectResponse`, and `Filter(Request $request): RedirectResponse`. Register GET detail `/backoffice/quiz-results/attempts/{test}` before GET list `/backoffice/quiz-results/{quiz_slug?}`. Use the approved `backoffice.quiz-results.show/index` names and existing web auth/affiliate middleware group. Keep `tests` name/URI pointing to `RedirectLegacy`.
7. Move the list query out of `eLeanController::tests`. Use grouped type-aware conditions: student+`whereHas('student')` OR user+`whereHas('user')`, with existing branch scopes on both. Eager-load user, student and quiz metadata; preserve `withCount('questions')`, latest-first ordering and current score/date/duration expressions. Unknown quiz slugs retain the current all-results behavior inside the authorized branch scope. Page sizes remain [10,20,50,100], default 20; invalid values use 20. Legacy redirect forwards only normalized page/page-size and the route slug to the fixed canonical route.
8. Extract unchanged answer loading into `QuizResultReview::Build(Test $test, bool $can_view_solution_videos): array`, returning the existing `test`, `questions_count`, `results`, `header`, `canViewSolutionVideos` view contract. Both controllers call it only after authorization. Preserve quiz question-count and `Answer::where('test_id', ...)->with('question.options')` behavior. Staff authorized detail allows videos; learner assignment rules remain in the personal entry point.
9. Preserve POST `submit.leaderboard` and CSRF. In the existing handler, recognize only the fixed staff targets `tests` and `backoffice.quiz-results.index` and delegate those to the authorized `Filter` method. That method validates `quiz_slug` as a required string and redirects to the canonical route, resetting pagination as the existing filter does. Leave the nonstaff Leaderboard behavior unchanged; do not accept a client-provided URL or widen accepted targets.
10. Compatibility tests assert exact redirect routes, quiz slug, `page=2`, `per_page=10`, invalid sizes reverting to 20, missing filter input validation, forbidden staff POST with no permission and unchanged Leaderboard filter destination. Run both new access/compatibility tests; expect exit 0 and no answers or participant data in denied responses.

During this task the canonical endpoints may initially render the existing views to isolate authorization verification. Task 3 changes their shells. Never mark the feature complete in that intermediate state.

## Task 3: Render the existing cards inside the back-office shell

**Files:**
- Create: `resources/views/quiz-results/index.blade.php`
- Create: `resources/views/quiz-results/show.blade.php`
- Create: `resources/views/quiz-results/partials/list.blade.php`
- Create: `resources/views/quiz-results/styles.blade.php`
- Create: `resources/views/home/result/content.blade.php`
- Modify: `resources/views/home/tests.blade.php` at its content section
- Modify: `resources/views/home/result.blade.php` at its content section
- Modify: `app/Http/Controllers/Backoffice/QuizResultsController.php` at view selection
- Modify: `tests/Feature/Elearning/TestPreviewPageTest.php`
- Create: `tests/Feature/Elearning/BackofficeQuizResultPageTest.php`

- **Consumes:** Authorized controller view data, existing preview/result styles, layout style/script slots and `home.result.answer`.
- **Produces:** Staff list/detail under `layouts.app`, preserving learner result cards under their current shell.
- **Checkpoint:** Task 2 access checks pass. Keep existing learner scripts on their current page; do not copy history interception into the staff page.

**Production behavior change with behavior-preserving extraction — use `test-driven-development`:**

1. Run `php vendor/bin/phpunit --do-not-cache-result tests/Feature/Elearning` for the unchanged-card baseline. Extract only list markup to `quiz-results.partials.list` and answer-content markup to `home.result.content`; existing view wrappers include them. Existing rendering tests must remain green before changing staff presentation. Preserve the legacy view variable names as contracts.
2. Add page tests asserting staff sidebar/breadcrumb presence, one content h1, actual permission-controlled answer links, selected filters/page size, later-page links, empty states, long escaped Thai/English text, zero scores, missing list metadata and both participant types. Add a list-only case without answer links and a view-only detail case without a link back to a forbidden list. Observe RED for the missing admin shell/permission-aware links.
3. Add staff wrappers extending `layouts.app`. Use `@section('style')` for existing preview/result styles plus `quiz-results.styles`. Scope their content under `.backoffice-quiz-results.test-preview-page` or `.backoffice-quiz-results.result-page` with the appropriate inner main class. Never add these page classes to the admin body. Reset only conflicting Bootstrap card/heading/button styles inside this wrapper. Preserve the pinned navy/orange/white palette and responsive card geometry from the existing styles.
4. The list partial accepts explicit `can_view_answers` and `answer_route_name`. Staff uses the new permission and canonical detail route; the retained legacy render wrapper supplies its old rendering contract solely for existing view tests, and is no longer a route response. Never use that presentation default as server authorization. Update TestPreviewPageTest to make the presentation inputs explicit and retain its current data/label/escaping assertions.
5. Staff detail includes the shared answer content and existing answer partial. Include the existing MathJax loader only, without learner unload/popstate handlers. Back navigation uses canonical list only when list permission is present; breadcrumb text remains noninteractive otherwise. Personal result wrapper keeps its current scripts and video inputs.
6. Switch staff controller to the new staff wrappers. Run `php vendor/bin/phpunit --do-not-cache-result tests/Feature/Elearning`; expect all access, compatibility, existing preview/result and portal-theme tests to pass. DOM tests prove shell content and values, not final CSS appearance.

## Task 4: Align menu visibility with the actual capabilities

**Files:**
- Modify: `resources/views/layouts/menu.blade.php` at `$show_quiz_menu`, `$quiz_menu_active` and Test Preview entry
- Modify: `resources/views/layouts/nav-bar.blade.php` at the learner tests link
- Modify: `resources/views/layouts/navigation.blade.php` at both tests links
- Modify: `tests/Feature/Elearning/BackofficeQuizResultPageTest.php`

- **Consumes:** Canonical route names and existing `$menu_can` permission checks.
- **Produces:** Back-office Test results menu available with list permission independently of quiz-management rights; no staff result link in learner navigation.
- **Checkpoint:** Do not change visibility of unrelated quiz-management or account actions.

**Production behavior change — use `test-driven-development`:**

1. Add tests for a custom list-only role, a role with only `quiz-manage`, a view-only role, and a student session. Assert exact menu hrefs and active state on list/detail where the menu is allowed. Observe RED before editing.
2. Set `$show_quiz_menu` to existing quiz-manage OR quiz-results-list capability, move only the staff-result entry outside the quiz-manage block and guard it with quiz-results-list. Include `backoffice.quiz-results.*` in active route matching. Use existing localized result heading text rather than inventing a new unlocalized menu label.
3. Remove the tests entries from the learner navigation surfaces. Do not replace them with a link that students can see but cannot access. Leave My Results and existing account actions untouched.
4. Run the focused back-office page test and `QuizPortalThemeTest.php`; expect correct per-capability navigation and unchanged learner portal behavior.

## Task 5: Verify access boundaries, visual integration and rollout evidence

**Files:**
- Create: `docs/plans/0015-backoffice-quiz-results-execution-record.md`

- **Consumes:** Completed tasks 1–4 and the current runtime/browser access.
- **Produces:** Durable verification evidence, review dispositions and migration/rollback notes.
- **Checkpoint:** No live migration or deployment. Missing browser access or failing full-suite checks remain explicit unresolved verification, not a success claim.

**Nonbehavioral verification artifact:**

1. Run `php vendor/bin/phpunit --do-not-cache-result tests/Feature/Elearning`, `php vendor/bin/phpunit --do-not-cache-result tests/Feature/Branch/BranchEndpointAccessTest.php`, and `php vendor/bin/phpunit --do-not-cache-result tests/Feature/Contact/StudentElearningSessionTest.php`. Record each exit code, failure/skip/warning and count. The existing route-middleware coverage test must still confirm that only learner routes carry student-session middleware.
2. Run `php vendor/bin/phpunit --do-not-cache-result` for the full suite and record failures without fixing unrelated modules. Run `git diff --check` and inspect all changed/untracked files against the preserved starting snapshot. Verify no environment/config switch was added and no prior theme work was lost.
3. With a supported browser and an authorized session, inspect staff list/detail at desktop 1440px, mobile 390px/320px and 200% zoom. Check sidebar, breadcrumbs, card wrapping, score fields, control focus/target sizes, filter POST, page-size changes, pagination and normal back navigation. Inspect My Results and own-result/video behavior. Do not modify live role assignments to manufacture a test session; capability matrices are exercised in the isolated integration tests.
4. If browser tools still cannot establish the URL or no appropriate session exists, preserve the tool error and mark runtime/visual checks not verified. Do not bypass access controls or imply DOM tests replace browser acceptance.
5. Obtain one fresh independent final review using `reviewing-code`, the approved specification, this plan and the Review Focus section. Record every finding and declined judgment; fix in-scope issues and rerun affected checks. Request scope approval before unrelated repairs.
6. Record rollout order: apply the permission migration through a separately authorized deployment process before enabling the new application routes for users; validate mapped roles and cache refresh. Rollback leaves permission records intact; reverting code may restore previous authorization behavior. No rollout command is executed as part of this plan.

## Handoff and Approval

This plan is self-contained for the approved specification and changes one coupled result-access flow. No implementation of this feature has started. Native execution with an independent final review remains the selected method from the conversation; the reviewer is authorized by that selection. Ask the user to approve this completed plan before implementation. Do not request the same branch or execution-method permission again.
