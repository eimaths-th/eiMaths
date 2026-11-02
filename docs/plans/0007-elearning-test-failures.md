# E-learning Full PHP Verification Failures

Final demo working-tree source; three disjoint partitions of all 191 configured PHP test files.

Results: 1,106 tests; 36,127 assertions; 66 failures; 1 error; 3 skipped. Full-suite verification did not pass.

These are observed failures, not a claim that they predate this change. No full baseline comparison was performed. Partitioned execution shares repository filesystem paths, so failure attribution remains unverified. No failing Contact tests were observed.

## Failures and Errors

| Kind | Test | First diagnostic |
| --- | --- | --- |
| failure | Tests\Feature\Affiliate\AffiliateAnalyticsPageTest::test_page_renders_default_evidence_controls_and_accessible_table | Expected response status code [200] but received 403. |
| failure | Tests\Feature\Affiliate\AffiliateAnalyticsPageTest::AffiliateAnalyticsUsesTheAdminLteContentShell | Expected response status code [200] but received 403. |
| failure | Tests\Feature\Affiliate\AffiliateCourseApplicationPermissionTest::BranchStaffNeedTheNewListPermissionToOpenCourseApplications | Failed asserting that '<!doctype html>\n |
| failure | Tests\Feature\Affiliate\AffiliateErrorTranslationTest::test_affiliate_payout_errors_have_user_facing_translations_in_thai | Failed asserting that two strings are identical. |
| failure | Tests\Feature\Affiliate\AffiliateIdentityVerificationTest::RegistrationStoresConsentEvidenceAndEmailVerificationStartsIdentityStep | Expected response status code [201, 301, 302, 303, 307, 308] but received 403. |
| failure | Tests\Feature\Affiliate\AffiliateIdentityVerificationTest::AffiliateCanSubmitEncryptedIdentityAndPrivateDocument | Expected response status code [201, 301, 302, 303, 307, 308] but received 403. |
| failure | Tests\Feature\Affiliate\AffiliateIdentityVerificationTest::InvalidNationalIdAndWrongDocumentTypeAreRejected | Session is missing expected key [errors]. |
| failure | Tests\Feature\Affiliate\AffiliateIdentityVerificationTest::AdminCanReviewRejectResubmissionAndApproveWhileEmployeeApprovalStillWorks | Expected response status code [200] but received 403. |
| failure | Tests\Feature\Affiliate\AffiliatePendingApprovalAlertTest::AuthorizedAffiliateListUserCanReadCurrentPendingApprovalSummary | Expected response status code [200] but received 403. |
| failure | Tests\Feature\Affiliate\AffiliatePendingApprovalAlertTest::AuthorizedAffiliateListUserSeesPendingApprovalAlertHooksInTheBackendLayout | Expected response status code [200] but received 403. |
| failure | Tests\Feature\Affiliate\AffiliateSecurePayoutRevealTest::test_admin_endpoint_reveals_to_authorized_user_without_email_verification | Expected response status code [200] but received 403. |
| failure | Tests\Feature\Affiliate\AffiliateSecurePayoutRevealTest::test_admin_list_html_and_json_keep_payout_details_masked_by_default | Expected response status code [200] but received 403. |
| failure | Tests\Feature\Affiliate\AffiliateSecurePayoutRevealTest::test_admin_page_only_renders_reveal_control_for_approved_rows_with_permission | Expected response status code [200] but received 403. |
| error | Tests\Feature\EbookApiTest::test_real_pdf_is_converted_into_private_page_images | Symfony\Component\Process\Exception\ProcessFailedException: The command "pdfinfo C:\Users\user\github\eimaths\eimaths_backend\storage\framework/testing/ebook-f7399d7f-5f8e-400e-975a-2b2ec195600b\imports/G11-demo.pdf" failed. |
| failure | Tests\Feature\Navigation\MenuOrganizationTest::OperationalPagesAreGroupedByTheirWorkArea | Failed asserting that false is not false. |
| failure | Tests\Unit\Affiliate\AffiliateTranslationStructureTest::EnglishAndThaiAffiliateTranslationLeavesAndPlaceholdersMatch | Failed asserting that two arrays are identical. |
| failure | Tests\Feature\Affiliate\AffiliateEmployeeAdministrationTest::NewEmployeeProfileAppearsInExistingApprovalList | Failed asserting that '<!doctype html>\n |
| failure | Tests\Feature\Affiliate\AffiliateLocaleTest::AffiliateDefaultsToThaiWhenPreferenceIsMissing | Expected response status code [200] but received 403. |
| failure | Tests\Feature\Affiliate\AffiliateLocaleTest::AuthenticatedEnglishPreferenceWins | Expected response status code [200] but received 403. |
| failure | Tests\Feature\Affiliate\AffiliateLocaleTest::UnsupportedPreferenceFallsBackToThai | Expected response status code [200] but received 403. |
| failure | Tests\Feature\Affiliate\AffiliatePortalAccessTest::ActiveUnverifiedEmployeeCanOpenDashboard | Expected response status code [200] but received 403. |
| failure | Tests\Feature\Affiliate\AffiliatePortalAccessTest::ActiveVerifiedExternalCanOpenDashboard | Expected response status code [200] but received 403. |
| failure | Tests\Feature\Affiliate\AffiliatePortalAccessTest::ActiveUnverifiedExternalIsRedirectedToWaiting | Expected response status code [201, 301, 302, 303, 307, 308] but received 403. |
| failure | Tests\Feature\Affiliate\AffiliatePortalAccessTest::NonActiveEmployeeIsRedirectedToEmployeeStatusPage | Expected response status code [201, 301, 302, 303, 307, 308] but received 403. |
| failure | Tests\Feature\Affiliate\AffiliatePortalAccessTest::IneligibleUserReceivesPlainForbiddenResponse | Failed asserting that '<!DOCTYPE html>\n |
| failure | Tests\Feature\Affiliate\AffiliatePortalAccessTest::IneligibleJsonRequestReceivesLocalizedForbiddenResponse | Failed asserting that null is identical to 'AFFILIATE_OWNERSHIP_DENIED'. |
| failure | Tests\Feature\Affiliate\AffiliatePortalAccessTest::UnsafeRequestIsForbiddenInsteadOfRedirecting | Failed asserting that '<!DOCTYPE html>\n |
| failure | Tests\Feature\Affiliate\AffiliatePortalAccessTest::AdministratorRoutesRemainUnchanged | Expected response status code [200] but received 403. |
| failure | Tests\Feature\Affiliate\AffiliatePortalAccessTest::EmployeePortalRequestsReachExistingDomainAndFeatureBoundaries | Expected response status code [200] but received 403. |
| failure | Tests\Feature\Affiliate\AffiliateRegistrationTest::PendingEmailAffiliateSeesGuestResendActionOnWaitingPage | Expected response status code [200] but received 403. |
| failure | Tests\Feature\Affiliate\AffiliateRegistrationTest::ActiveAffiliateIsRedirectedFromWaitingPageToDashboard | Expected response status code [201, 301, 302, 303, 307, 308] but received 403. |
| failure | Tests\Feature\Affiliate\AffiliateRegistrationTest::PendingAffiliateCanViewWaitingPage | Expected response status code [200] but received 403. |
| failure | Tests\Feature\Affiliate\AffiliateWithdrawalUiTest::test_active_affiliate_dashboard_exposes_summary_links_without_mutation_controls | Expected response status code [200] but received 403. |
| failure | Tests\Feature\Affiliate\AffiliateWithdrawalUiTest::test_affiliate_html_payout_and_withdrawal_forms_redirect_and_update_state | Expected response status code [201, 301, 302, 303, 307, 308] but received 403. |
| failure | Tests\Feature\Affiliate\AffiliateWithdrawalUiTest::test_dashboard_does_not_render_withdrawal_cancel_forms | Expected response status code [200] but received 403. |
| failure | Tests\Feature\Affiliate\AffiliateWithdrawalUiTest::test_html_validation_returns_to_the_originating_finance_page | Expected response status code [201, 301, 302, 303, 307, 308] but received 403. |
| failure | Tests\Feature\Affiliate\AffiliateWithdrawalUiTest::test_admin_withdrawal_page_renders_and_html_transitions_preserve_json_api | Expected response status code [200] but received 403. |
| failure | Tests\Feature\Affiliate\AffiliateWithdrawalUiTest::test_json_payout_and_withdrawal_endpoints_remain_json_contracts | Expected response status code [200] but received 403. |
| failure | Tests\Feature\Localization\UiLocalizedPagesTest::StudentsManagementPagesRenderInBothLocales with data set #12 | Expected response status code [200] but received 403. |
| failure | Tests\Feature\Localization\UiLocalizedPagesTest::StudentsManagementPagesRenderInBothLocales with data set #13 | Expected response status code [200] but received 403. |
| failure | Tests\Feature\Localization\UiLocalizedPagesTest::QuizManagementPagesRenderInBothLocales with data set #0 | Expected response status code [200] but received 500. |
| failure | Tests\Feature\Localization\UiLocalizedPagesTest::QuizManagementPagesRenderInBothLocales with data set #1 | Expected response status code [200] but received 500. |
| failure | Tests\Feature\Localization\UiLocalizedPagesTest::QuizManagementPagesRenderInBothLocales with data set #8 | Expected response status code [200] but received 500. |
| failure | Tests\Feature\Localization\UiLocalizedPagesTest::QuizManagementPagesRenderInBothLocales with data set #9 | Expected response status code [200] but received 500. |
| failure | Tests\Feature\Localization\UiLocalizedPagesTest::SecondaryStudentPagesExposeLocalizedLabels with data set #3 | Expected response status code [200] but received 403. |
| failure | Tests\Feature\Navigation\PendingWorkMenuBadgeTest::AffiliateManagementOwnsAffiliateAccountsCourseApplicationsWithdrawalsAndPendingBadges | Failed asserting that '<!doctype html>\n |
| failure | Tests\Feature\Navigation\PendingWorkMenuBadgeTest::AffiliateBadgeIsNoLongerAttachedToUserAccess | Failed asserting that '<!doctype html>\n |
| failure | Tests\Feature\Navigation\PendingWorkMenuBadgeTest::StockAndLogisticsParentShowsCentreBookShipmentBadge | Failed asserting that '<li class="nav-item ">\n |
| failure | Tests\Feature\Affiliate\AffiliateAnalyticsApiTest::test_active_owner_receives_stable_zero_contract | Expected response status code [200] but received 403. |
| failure | Tests\Feature\Affiliate\AffiliateAnalyticsApiTest::test_invalid_ranges_return_localized_validation_errors | Expected response status code [422] but received 403. |
| failure | Tests\Feature\Affiliate\AffiliateAnalyticsApiTest::test_disabled_analytics_feature_is_not_exposed | Expected response status code [503] but received 403. |
| failure | Tests\Feature\Affiliate\AffiliateDashboardAnalyticsTest::test_dashboard_shows_the_analytics_summary_only_when_enabled | Expected response status code [200] but received 403. |
| failure | Tests\Feature\Affiliate\AffiliateFinanceNavigationTest::test_active_affiliate_can_render_dashboard_payout_and_withdrawal_pages | Expected response status code [200] but received 403. |
| failure | Tests\Feature\Affiliate\AffiliateFinanceNavigationTest::test_withdrawal_page_renders_zero_wallet_balances_when_projection_is_missing | Expected response status code [200] but received 403. |
| failure | Tests\Feature\Affiliate\AffiliateFinanceNavigationTest::test_inactive_affiliate_cannot_render_finance_pages | Expected response status code [201, 301, 302, 303, 307, 308] but received 403. |
| failure | Tests\Feature\Affiliate\AffiliateFinanceNavigationTest::test_finance_pages_only_expose_the_authenticated_affiliate_profile | Expected response status code [200] but received 403. |
| failure | Tests\Feature\Affiliate\AffiliateFinanceNavigationTest::test_active_affiliate_navigation_contains_separate_finance_entries | Expected response status code [200] but received 403. |
| failure | Tests\Feature\Affiliate\AffiliateFinanceNavigationTest::ActiveUnverifiedEmployeeNavigationMatchesPortalAccess | Expected response status code [200] but received 403. |
| failure | Tests\Feature\Affiliate\AffiliateGuideTest::active_affiliate_can_read_the_guide_and_open_the_source_document | Expected response status code [200] but received 403. |
| failure | Tests\Feature\Affiliate\AffiliateGuideTest::active_affiliate_can_browse_every_curriculum_pdf | Expected response status code [200] but received 403. |
| failure | Tests\Feature\Affiliate\AffiliateGuideTest::unknown_curriculum_level_returns_not_found | Expected response status code [404] but received 403. |
| failure | Tests\Feature\Affiliate\AffiliateGuideTest::inactive_affiliate_is_redirected_away_from_the_guide_and_document | Expected response status code [201, 301, 302, 303, 307, 308] but received 403. |
| failure | Tests\Feature\Affiliate\AffiliateGuideTest::affiliate_finance_navigation_links_to_the_guide | Failed asserting that '<!DOCTYPE html>\n |
| failure | Tests\Feature\Affiliate\AffiliateGuideTest::affiliate_finance_sidebar_uses_the_english_guide_label | Failed asserting that '<li class="nav-item ">\n |
| failure | Tests\Feature\Affiliate\AffiliateRolePermissionTest::affiliate_user_without_cms_permissions_does_not_see_cms_menu_entries | Failed asserting that '<!doctype html>\n |
| failure | Tests\Feature\ExampleTest::test_the_application_returns_a_successful_response | Expected response status code [200] but received 302. |
| failure | Tests\Unit\CreditNote\CreditNoteEnumTest::ReasonEnumContainsExactlyTheApprovedTenStoredValues | Failed asserting that two arrays are identical. |

## Skipped

- Tests\Feature\Affiliate\AffiliateEmployeeEnrollmentConcurrencyTest::ConcurrentFirstEnrollmentsCreateOneProfileAndOneAuditWithoutChangingStaffIdentity
- Tests\Feature\CreditNote\CreditNoteConcurrencyTest::mysql_concurrency_scenarios_are_explicitly_skipped_without_opt_in
- Tests\Feature\Affiliate\AffiliateClickConcurrencyTest::ConcurrentFirstClicksProduceTwoEventsOneUniqueWindowAndAnAtomicDailyTotal

## Raw Evidence

JUnit XML and complete text logs: `%TEMP%/eimaths-elearning-part-0.{xml,log}`, `%TEMP%/eimaths-elearning-part-1.{xml,log}`, `%TEMP%/eimaths-elearning-part-2.{xml,log}`. Exact file manifests are stored beside them.
