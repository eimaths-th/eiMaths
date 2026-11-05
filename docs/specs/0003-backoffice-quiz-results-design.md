# Back-office Quiz Results and Role Permissions

## Approval and Purpose

The user requested that Test Preview (`/tests`) become a back-office feature accessible through Roles. The user approved the proposed design and authorized writing this specification. The user explicitly prohibited new environment variables. This specification requires review and approval before implementation planning; it does not authorize implementation or live permission changes.

The purpose is to separate staff review of multiple people's attempts from learners reviewing their own attempts. Intended users are authorized back-office staff and learners using My Results. Work continues on `demo` and preserves the uncommitted theme-alignment work from plan 0014.

## Decisions and Scope

- User decisions: back-office ownership of staff result pages, permissions assigned through Roles, existing branch boundaries, preservation of learner self-review, and no new `.env` settings or feature flags.
- Approved agent proposals: two permissions, `quiz-results-list` and `quiz-results-view`; back-office layout with the existing result-card presentation; guarded compatibility for old URLs; initial role mapping based on existing permissions rather than role names.
- Agent implementation choices recorded here: canonical staff routes under `/backoffice/quiz-results`, explicit staff and personal-result entry points, and reusable result presentation/data preparation without shared authorization shortcuts.
- Alternatives considered: hiding the current menu alone does not protect direct URLs; hard-coded role names prevent existing custom/branch roles from controlling access; reusing `quiz-manage` for every action cannot separate result access from quiz administration. Use the existing permission system with distinct capabilities instead.
- Non-goals: new role types, a new access-control system, quiz editing, score changes, new statistics/export features, changes to Leaderboard visibility, broad translation cleanup, or unrelated security repairs.
- Anti-goals: a permission migration that grants access by role name; a legacy URL bypass; users viewing a different branch through a forged id/filter; student access becoming dependent on a staff role; changes to persisted attempts or scores.

## Verified Existing Integration

- `routes/web2.php` registers `/tests/{quiz_slug?}` with `eLeanController::tests` inside `auth` and `affiliate.redirect` middleware. Personal results use `ResultController::show` under the user-or-student flow.
- `resources/views/layouts/menu.blade.php` currently places Test Preview inside `quiz-manage`. The learner quiz navigation also contains a tests link; every navigation surface must reflect the new staff capability.
- `BranchRouteAccess` currently requires `quiz-list` for web-user access through `ResultController`. `ResultController` checks student ownership using both `type_user` and `user_id` and controls student solution-video visibility through `ContactQuiz` assignment.
- `BranchServiceProvider` installs model scopes using `BranchContext`. Attempts are scoped through the correct participant type, and answers through their parent attempt. `BranchAccess::canCrossBranches` owns the existing cross-branch decision.
- `config/branch.php` maps `quiz-` permissions to the quiz module. The Roles permission presenter already groups that prefix under teaching/quizzes.
- Back-office quiz views use `layouts.app`. Test Preview currently uses `layouts.quiz_layout` with `test_preview_page`; answer review uses `result_page`.
- Migration `2026_09_08_000002_register_branch_action_permissions.php` provides the precedent for deriving new capabilities from existing role permissions without granting them to all administrators by name.

These are static repository observations, not proof of the current live database's role assignments or an executed access-control audit.

## Authorization Contract

All staff endpoints require an authenticated, enabled web user with a valid active branch, existing module eligibility, the named new permission, and the existing branch-scoped record access. Role membership conveys permissions through the existing system; role names alone do not grant access.

| Operation | Required capability | Data boundary |
| --- | --- | --- |
| Open staff result list | `quiz-results-list` | Existing branch scope |
| Open staff answer detail, including videos | `quiz-results-view` | Existing branch scope for the attempt and answers |
| Follow a detail link from the list | Both, because the list and detail are separate operations | Same scope at both requests |
| Learner self-review | Existing learner authentication and ownership | Matching `type_user` and `user_id` |

The two staff capabilities remain independently assignable. A list-only user sees scores and metadata but no actionable answer-detail link. A view-only user may access a permitted in-scope detail URL but cannot browse the list. Neither capability expands branch access. Preserve existing owner/central-account semantics rather than introducing an additional superuser bypass.

Student-only authentication never grants staff access. In an ambiguous session with both student and web authentication, personal-result handling keeps the existing student-first ownership rule; the staff endpoint must not use the student identity as a web identity or silently fall back between guards. Staff routes require the web guard and deny the ambiguous dual-guard case until the student session is exited through the existing UI.

For personal web-user attempts, retain existing web-flow eligibility (`quiz-list`) and require ownership by both participant type and id. Reviewing someone else's attempt is a staff operation requiring `quiz-results-view`; do not retain the old broad web-authentication shortcut. Personal student solution-video assignment rules remain unchanged. Personal web solution-video behavior remains unchanged for an authorized own attempt.

Unauthenticated requests use the applicable existing sign-in behavior. Authenticated actors missing a required permission receive 403. Missing or out-of-scope staff records return 404 through scoped lookup; do not disclose whether a record exists in another branch. Existing student ownership denial remains 403.

## Initial Permission Registration

Use an idempotent migration with guard `web`:

1. Register `quiz-results-list` and `quiz-results-view` if absent.
2. For existing web Roles with `quiz-manage`, add `quiz-results-list`.
3. Add `quiz-results-view` only to those Roles that have both `quiz-manage` and `quiz-list`.
4. Preserve all unrelated permissions, role identities and assignments. Do not revoke existing manually assigned new permissions. Do not grant to roles based on Admin/Teacher names.
5. Do not convert user-level direct legacy permissions into new grants automatically; this approved initialization is role-based. Administrators can assign the new permissions through existing authorized controls.
6. Clear the existing permission cache after registration. Re-running must not duplicate permissions or remove later assignments.

Use the project's non-destructive rollback precedent: leave permission identities and assignments intact on rollback rather than deleting grants that may have been assigned independently. Rolling back application authorization code would restore its former access behavior; migration rollback must not be represented as revoking access. Review this explicitly in the implementation plan and deployment notes.

No `.env`, `.env.example`, environment variable, feature flag, or runtime configuration switch is added. Permission registration and the existing Roles UI control rollout. Do not apply migrations to a shared/live database as part of merely writing or testing the implementation; use the project's isolated test database for validation.

## Routes and Compatibility

Canonical routes proposed by the agent:

- `GET /backoffice/quiz-results/{quiz_slug?}`: named `backoffice.quiz-results.index`.
- `GET /backoffice/quiz-results/attempts/{test}`: named `backoffice.quiz-results.show`; declare the specific route before the optional-slug route.

Preserve named route `tests` and `/tests/{quiz_slug?}` as an authenticated, permission-checked redirect to the canonical list. Preserve selected quiz and supported pagination parameters, including `per_page` and `page`. Validate page size using the current allowed values 10, 20, 50 and 100, default 20. Do not resolve a redirect target from an arbitrary request URL.

Preserve `results.show` and `/results/{test}` for My Results and existing links. A learner's own result remains in the learner layout. An eligible web staff request to another person's result must pass staff detail authorization and scoped lookup before redirecting to the canonical staff detail. An unauthorized direct request is denied; it cannot inherit a permission merely by arriving through a compatibility route.

Keep the existing filter POST and CSRF contract. Staff filters route to the canonical list using an explicit approved route target; do not broaden the shared filter handler's redirect capability. Keep Leaderboard filter behavior unchanged.

## Responsibilities and Data Flow

- A dedicated back-office quiz-results controller owns staff list and detail entry points, permission checks and view selection. Remove staff listing responsibility from the learner controller, retaining only compatibility delegation where required.
- A focused result authorization boundary expresses staff detail capability versus personal ownership. Integrate with `BranchRouteAccess` so its legacy `quiz-list` rule does not accidentally block a staff user who has the newly assigned detail capability. Do not remove unrelated branch checks.
- Query through the existing scoped Eloquent models. Resolve attempts by `type_user`: student attempts through `student`/Contact and user attempts through `user`/User. The current list's unconditional `whereHas('user')` must not be copied as the student authorization condition. Matching numeric ids in different participant tables never establishes ownership or branch eligibility.
- Eager-load the participant relation needed for each type plus quiz metadata. Preserve descending attempt order, score computation, question count semantics, duration/date formatting, empty-state fallbacks and pagination.
- Share answer preparation and presentation only where doing so avoids duplicated score/answer logic. Keep the staff and learner authorization decisions explicit before loading answer details. Retain the existing question/answer cards, image handling and video policy after authorization.
- Include both student and web-user fixtures in the list tests. Cross-branch access must be tested at route-level with middleware enabled, not inferred from a view-rendering test.

## Presentation and Navigation

Use `layouts.app` for staff list and detail, with its sidebar and breadcrumbs. Preserve the existing navy/orange/white result-card treatment inside the back-office content area; scope styles so they do not reset the entire admin body or affect the sidebar. Extract reusable partials where necessary for learner review to retain its existing layout.

```text
Back-office sidebar: Quizzes > Test results
Breadcrumb: Quizzes / Test results
Navy title + quiz filter + page-size control
White attempt cards + score + permitted View answers action
Pagination

Staff detail: Quizzes / Test results / Attempt
Existing answer-review cards within the back-office shell

Learner: My Results > Own attempt (existing learner shell)
```

Show the back-office result menu when `quiz-results-list` is allowed, independently of `quiz-manage`. Hide staff results from the learner navigation. Give permission labels readable English/Thai names through the existing permission presentation/localization mechanism. Existing callers of named routes must continue to resolve.

Maintain keyboard focus, semantic headings and labels, readable contrast, and usable mobile layouts. Preserve the in-progress Home/Leaderboard/My Results theme work; do not reset it while moving staff views.

## Verification and Acceptance

Required integration tests cover:

- Guest, student-only, disabled web user, inactive/missing branch, disabled quiz module, and ambiguous dual-guard sessions. Module-disabled expectations must preserve the existing central-account exemption in `BranchAccess::permissionAllowed`; do not introduce a new universal module denial.
- No permissions, list-only, view-only, both permissions, and authorized users with different role names.
- Same-branch and different-branch attempts for both participant types, plus existing authorized cross-branch access.
- Colliding User/Contact ids, forged attempt ids, deleted/missing participants, missing quiz metadata in list presentation, and empty lists.
- A learner's own result, someone else's result, and unchanged student video assignment behavior. Retain the current valid-quiz requirement for answer review rather than inventing a new deleted-quiz reconstruction feature.
- Direct canonical URLs, old `/tests` and `/results` URLs, selected filters, valid/invalid page sizes and pagination URLs.
- Permission migration mapping, non-qualifying roles, existing new permissions, repeated execution and preservation of unrelated grants.
- Regression tests for Test Preview's card data, learner result rendering and portal-theme tests from plan 0014.

Visual verification must inspect staff list/detail within the actual back-office assets and the unchanged learner pages on desktop and mobile. Verify sidebar links, filter submission, pagination, unauthorized-action visibility and direct-URL denial. Unit/DOM tests alone do not establish visual fidelity.

Known verification constraints from the previous task: browser access was blocked by inability to establish the current Windows browser URL; the full suite also encountered failures outside the theme work. Recheck access and baseline during planning/execution and report any unresolved limitations rather than claiming full verification.

## Handoff

This specification records the approved design and agent-selected integration details. No implementation or live permission change has been made for this feature. An independent readiness review precedes requesting approval of this written specification and the move to implementation planning. The completed implementation plan will require its own approval under the requested brainstorming workflow.

Independent readiness review completed on 2026-11-05 against the relevant routes, controllers, branch scopes, migration precedent and views. No material unresolved planning questions were found. The reviewer noted the existing central-account module exemption; the acceptance wording above now explicitly preserves it. This was a static specification review only: no runtime authorization checks, live role inspection or migration execution was performed.
