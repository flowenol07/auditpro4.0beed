<?php

namespace Core;

class SiteUrls {

    public static $site_urls = array(

        'auth' => [
            'id' => 'auth',
            'pageTitle' => 'Login to Your Account',
            'pageHeading' => 'Login to Your Account',
            'controller' => 'Auth',
            'controllerDir' => null,
            'url' => 'auth',
            'viewDir' => null,
            'breadcrumb' => null
        ],

        'logout' => [
            'id' => 'auth',
            'pageTitle' => 'Logout Your Account',
            'pageHeading' => 'Logout Your Account',
            'controller' => 'Auth',
            'controllerDir' => null,
            'url' => 'auth/logout',
            'viewDir' => null,
            'breadcrumb' => null
        ],

        'passwordPolicy' => [
            'id' => 'passwordPolicy',
            'pageTitle' => 'Password Policy Changed',
            'pageHeading' => 'Password Policy Changed!',
            'controller' => 'PasswordPolicy',
            'controllerDir' => null,
            'url' => 'password-policy',
            'viewDir' => 'password-policy/',
            'breadcrumb' => null,
            // 'accessControl' => array('1')
        ],  
        
        'passwordPolicyAdmin' => [
            'id' => 'passwordPolicyAdmin',
            'pageTitle' => 'Password Policy',
            'pageHeading' => 'Password Policy',
            'controller' => 'PasswordPolicy',
            'controllerDir' => null,
            'url' => 'password-policy',
            'viewDir' => 'admin/password-policy/',
            'breadcrumb' => array('dashboard'),
            'accessControl' => array('1')
        ], 

        'dashboard' => [
            'id' => 'dashboard',
            'pageTitle' => 'Dashboard',
            'pageHeading' => 'Dashboard - Welcome Back!',
            'controller' => 'Dashboard',
            'controllerDir' => null,
            'url' => 'dashboard',
            'viewDir' => 'dashboard/',
            'breadcrumb' => null,
            'extraMethods' => [ 
                'select-audit-unit' => 'selectAuditUnit',             
            ],
        ],

        'updateProfile' => [
            'id' => 'updateProfile',
            'pageTitle' => 'Update Profile',
            'pageHeading' => 'Update Profile',
            'controller' => 'Employee',
            'controllerDir' => 'Admin',
            'method' => 'updateProfile',
            'url' => 'update-profile',
            'viewDir' => 'admin/employee-master/',
            'breadcrumb' => array('dashboard')
        ],

        'auditAssessment' => [
            'id' => 'auditAssessment',
            'pageTitle' => 'Audit Assessment',
            'pageHeading' => 'Audit Assessment',
            'controller' => 'AuditAssessment',
            'controllerDir' => 'Audit',
            'url' => 'audit-assessment',
            'viewDir' => 'audit/assesment/',
            'breadcrumb' => array('dashboard'),
            'accessControl' => array('2', '3', '4','16')
        ],

        'audit' => [
            'id' => 'audit',
            'pageTitle' => 'Audit',
            'pageHeading' => 'Audit',
            'controller' => 'AuditContoller',
            'controllerDir' => 'Audit',
            'url' => 'audit',
            'viewDir' => 'audit/assesment/',
            'breadcrumb' => array('dashboard'),
            'extraMethods' => [ 
                // 're-audit' => 'reAudit', 
                'end-assesment' => 'endAssesment', 
                'end-reassesment' => 'endReAssesment', 
                'end-assesment-submit' => 'endAssesmentSubmit',
                'remove-annex' => 'removeAnnex',
                'remove-dump-sampling' => 'removeDumpSampling',
                'download-sample-annex' => 'downloadSampleAnnex',
                'upload-question-annex-csv' => 'uploadQuestionAnnexCSV',
                'cf-comment-save' => 'cfCommentSave',
            ],
            'accessControl' => array('2')
        ],

        'auditEndAssessment' => [
            'id' => 'auditEndAssessment',
            'pageTitle' => 'Audit End Assesment',
            'pageHeading' => 'Audit End Assesment',
            'controller' => 'AuditContoller',
            'controllerDir' => 'Audit',
            'url' => 'audit',
            'viewDir' => 'audit/',
            'breadcrumb' => array('dashboard'),
            'accessControl' => array('2')
        ],

        'auditMenu' => [
            'id' => 'auditMenu',
            'pageTitle' => 'Audit',
            'pageHeading' => 'Audit',
            'controller' => 'AuditContoller',
            'controllerDir' => 'Audit',
            'url' => 'audit/',
            'viewDir' => 'audit/',
            'breadcrumb' => array('dashboard'),
            'accessControl' => array('2')
        ],

        'auditCategory' => [
            'id' => 'auditCategory',
            'pageTitle' => 'Audit',
            'pageHeading' => 'Audit',
            'controller' => 'AuditContoller',
            'controllerDir' => 'Audit',
            'url' => 'audit/category/',
            'viewDir' => 'audit/',
            'breadcrumb' => array('dashboard'),
            'accessControl' => array('2')
        ],

        'compliance' => [
            'id' => 'compliance',
            'pageTitle' => 'Compliance',
            'pageHeading' => 'Compliance',
            'controller' => 'ComplianceContoller',
            'controllerDir' => 'Compliance',
            'url' => 'compliance',
            'viewDir' => 'compliance/',
            'breadcrumb' => array('dashboard'),
            'extraMethods' => [ 're-compliance' => 'reCompliance', 'save-compliance' => 'saveCompliance' ],
            'accessControl' => array('3')
        ],

        'reviewer' => [
            'id' => 'reviewer',
            'pageTitle' => 'Reviewer',
            'pageHeading' => 'Reviewer',
            'controller' => 'ReviewerController',
            'controllerDir' => 'Reviewer',
            'url' => 'reviewer',
            'viewDir' => 'reviewer/',
            'breadcrumb' => array('dashboard'),
            'extraMethods' => [ 
                'review-audit' => 'reviewAudit', 
                'review-compliance' => 'reviewCompliance', 
                'save-status' => 'saveStatus', 
                'save-comment' => 'saveComment', 
                'submit-audit-review' => 'submitAuditReview',
                'submit-compliance-review' => 'submitComplianceReview',
            ],
            'accessControl' => array('4','16')
        ],

        /*'yearMaster' => [
            'id' => 'yearMaster',
            'pageTitle' => 'Year Master',
            'pageHeading' => 'Year Master',
            'controller' => 'YearMaster',
            'controllerDir' => 'Admin',
            'url' => 'year-master',
            'viewDir' => 'admin/year-master/',
            'breadcrumb' => array('dashboard'),
            'accessControl' => array('1')
        ],*/

        'executiveSummary' => [
            'id' => 'executiveSummary',
            'pageTitle' => 'Executive Summary',
            'pageHeading' => 'Executive Summary',
            'controller' => 'ExecutiveSummary',
            'controllerDir' => null,
            'url' => 'executive-summary',
            'viewDir' => 'executive-summary/',
            'breadcrumb' => array('dashboard'),
            'extraMethods' => [ 'review-audit' => 'reviewAudit', 'review-compliance' => 'reviewCompliance', 'compliance' => 'compliance' ],
            'accessControl' => array('2','3','4','16')
        ],
        
        'employeeMaster' => [
            'id' => 'employeeMaster',
            'pageTitle' => 'Employee Master',
            'pageHeading' => 'Employee Master',
            'controller' => 'Employee',
            'controllerDir' => 'Admin',
            'url' => 'employee-master',
            'viewDir' => 'admin/employee-master/',
            'breadcrumb' => array('dashboard'),
            'accessControl' => array(1,9),
        ],

        'auditSectionMaster' => [
            'id' => 'auditSectionMaster',
            'pageTitle' => 'Audit Section Master',
            'pageHeading' => 'Audit Section Master',
            'controller' => 'AuditSectionMaster',
            'controllerDir' => 'Admin',
            'url' => 'audit-section-master',
            'viewDir' => 'admin/audit-section-master/',
            'breadcrumb' => array('dashboard'),
            'accessControl' => array('1')
        ],

        'auditUnitMaster' => [
            'id' => 'auditUnitMaster',
            'pageTitle' => 'Audit Unit Master',
            'pageHeading' => 'Audit Unit Master',
            'controller' => 'AuditUnitMaster',
            'controllerDir' => 'Admin',
            'url' => 'audit-unit-master',
            'viewDir' => 'admin/audit-unit-master/',
            'breadcrumb' => array('dashboard'),
            'extraMethods' => [ 
                DATA_TABLE_AJX . '-frequency' => 'dataTableAjaxFrequency',      
                DATA_TABLE_AJX . '-last-march-position' => 'dataTableAjaxLastMarchPosition',  // Add this line
                'last-march-position' => 'lastMarchPosition',
                'last-march-position/upload-csv' => 'uploadCsv',  // Add this line for CSV upload
                'download-sample-csv' => 'downloadSampleCsv',

            ],
            'accessControl' => array(1,9)
        ],

        'broaderAreaMaster' => [
            'id' => 'broaderAreaMaster',
            'pageTitle' => 'Broader Area Master',
            'pageHeading' => 'Broader Area Master',
            'controller' => 'BroaderAreaMaster',
            'controllerDir' => 'Admin',
            'url' => 'broader-area-master',
            'viewDir' => 'admin/broader-area-master/',
            'breadcrumb' => array('dashboard'),
            'accessControl' => array(1,9)
        ],

        'schemeMaster' => [
            'id' => 'schemeMaster',
            'pageTitle' => 'Scheme Master',
            'pageHeading' => 'Scheme Master',
            'controller' => 'SchemeMaster',
            'controllerDir' => 'Admin',
            'url' => 'scheme-master',
            'viewDir' => 'admin/scheme-master/',
            'breadcrumb' => array('dashboard'),
            'accessControl' => array('1')
        ],

        'menuMaster' => [
            'id' => 'menuMaster',
            'pageTitle' => 'Menu Master',
            'pageHeading' => 'Menu Master',
            'controller' => 'MenuMaster',
            'controllerDir' => 'Admin',
            'url' => 'menu-master',
            'viewDir' => 'admin/menu-master/',
            'breadcrumb' => array('dashboard'),
            'accessControl' => array('1')
        ],

        'categoryMaster' => [
            'id' => 'categoryMaster',
            'pageTitle' => 'Category Master',
            'pageHeading' => 'Category Master',
            'controller' => 'CategoryMaster',
            'controllerDir' => 'Admin',
            'url' => 'category-master',
            'viewDir' => 'admin/category-master/',
            'breadcrumb' => array('dashboard'),
            'accessControl' => array('1')
        ],

        'questionMaster' => [
            'id' => 'questionMaster',
            'pageTitle' => 'Question Master',
            'pageHeading' => 'Question Master',
            'controller' => 'QuestionMaster',
            'controllerDir' => 'Admin',
            'url' => 'question-master',
            'viewDir' => 'admin/question-master/',
            'breadcrumb' => array('dashboard'),
            'extraMethods' => [ 'risk-mapping' => 'riskMapping' ],
            'accessControl' => array(1,9)
        ],

        'questionSetMaster' => [
            'id' => 'questionSetMaster',
            'pageTitle' => 'Question Set Master',
            'pageHeading' => 'Question Set Master',
            'controller' => 'QuestionSetMaster',
            'controllerDir' => 'Admin',
            'url' => 'question-set-master',
            'viewDir' => 'admin/question-set-master/',
            'breadcrumb' => array('dashboard'),
            'accessControl' => array(1,9)
        ],

        'questionHeaderMaster' => [
            'id' => 'questionHeaderMaster',
            'pageTitle' => 'Question Header Master',
            'pageHeading' => 'Question Header Master',
            'controller' => 'QuestionHeaderMaster',
            'controllerDir' => 'Admin',
            'url' => 'question-header-master',
            'viewDir' => 'admin/question-header-master/',
            'breadcrumb' => array('dashboard'),
            'accessControl' => array(1,9)
        ],

        'manageAccountsDeposits' => [
            'id' => 'manageAccountsDeposits',
            'pageTitle' => 'Manage Accounts Deposits',
            'pageHeading' => 'Manage Accounts Deposits',
            'controller' => 'DumpUpload',
            'controllerDir' => 'Admin',
            'url' => 'manage-accounts-deposits',
            'viewDir' => 'admin/dump-upload/',
            'breadcrumb' => array('dashboard'),
            'accessControl' => array(1,9),
            'extraMethods' => [ 
                    'manage-accounts-deposits' => 'manageAccountsDeposits',
                    'delete-last-upload-deposit' => 'deleteLastUploadDeposit'
                ],
        ],

        'manageAccountsAdvances' => [
            'id' => 'manageAccountsAdvances',
            'pageTitle' => 'Manage Accounts Advances',
            'pageHeading' => 'Manage Accounts Advances',
            'controller' => 'DumpUpload',
            'controllerDir' => 'Admin',
            'url' => 'manage-accounts-advances',
            'viewDir' => 'admin/dump-upload/',
            'breadcrumb' => array('dashboard'),
            'accessControl' => array(1,9),
            'extraMethods' => [ 
                    'manage-accounts-advances' => 'manageAccountsAdvances',
                    'delete-last-upload-advance' => 'deleteLastUploadAdvance'
                ],
        ],

        'bulkUploadAdvance' => [
            'id' => 'bulkUploadAdvance',
            'pageTitle' => 'Bulk Upload Advance',
            'pageHeading' => 'Bulk Upload Advance',
            'controller' => 'DumpUpload',
            'controllerDir' => 'Admin',
            'url' => 'bulk-upload-advance',
            'viewDir' => 'admin/dump-upload/',
            'breadcrumb' => array('dashboard'),
            'accessControl' => array(1,9),
            'extraMethods' => [ 'bulk-upload-advance' => 'dumpUploadAdvance' ],
        ],

        'bulkUploadDeposit' => [
            'id' => 'bulkUploadDeposit',
            'pageTitle' => 'Bulk Upload Deposit',
            'pageHeading' => 'Bulk Upload Deposit',
            'controller' => 'DumpUpload',
            'controllerDir' => 'Admin',
            'url' => 'bulk-upload-deposit',
            'viewDir' => 'admin/dump-upload/',
            'breadcrumb' => array('dashboard'),
            'accessControl' => array(1,9),
            'extraMethods' => [ 'bulk-upload-deposit' => 'dumpUploadDeposit' ],
        ],

        'riskCategoryMaster' => [
            'id' => 'riskCategoryMaster',
            'pageTitle' => 'Risk Category Master',
            'pageHeading' => 'Risk Category Master',
            'controller' => 'RiskCategoryMaster',
            'controllerDir' => 'Admin',
            'url' => 'risk-category-master',
            'viewDir' => 'admin/risk-category-master/',
            'breadcrumb' => array('dashboard'),
            'accessControl' => array('1')
        ],

        'riskCategoryWeight' => [
            'id' => 'riskCategoryWeight',
            'pageTitle' => 'Risk Category Weight',
            'pageHeading' => 'Risk Category Weight',
            'controller' => 'RiskCategoryWeight',
            'controllerDir' => 'Admin',
            'url' => 'risk-category-weight',
            'viewDir' => 'admin/risk-category-weight/',
            'breadcrumb' => array('dashboard'),
            'accessControl' => array('1')
        ],

        'riskControlMaster' => [
            'id' => 'riskControlMaster',
            'pageTitle' => 'Risk Control Master',
            'pageHeading' => 'Risk Control Master',
            'controller' => 'RiskControlMaster',
            'controllerDir' => 'Admin',
            'url' => 'risk-control-master',
            'viewDir' => 'admin/risk-control-master/',
            'breadcrumb' => array('dashboard'),
            'accessControl' => array('1')
        ],

        'riskControlKeyAspect' => [
            'id' => 'riskControlKeyAspect',
            'pageTitle' => 'Risk Control Key Aspect',
            'pageHeading' => 'Risk Control Key Aspect',
            'controller' => 'RiskControlKeyAspect',
            'controllerDir' => 'Admin',
            'url' => 'risk-control-key-aspect',
            'viewDir' => 'admin/risk-control-key-aspect/',
            'breadcrumb' => array('dashboard'),
            'accessControl' => array('1'),
            'extraMethods' => [ 'ajax-risk-control-key-aspect' => 'ajaxKeyAspectRatio' ],
        ],

        /*'ajaxRiskControlKeyAspectFind' => [
            'id' => 'ajaxRiskControlKeyAspectFind',
            'controller' => 'RiskControlKeyAspect',
            'controllerDir' => 'Admin',
            'method' => 'ajaxKeyAspectRatio',
            'url' => 'ajax-risk-control-key-aspect',
        ],*/

        'riskComposite' => [
            'id' => 'riskComposite',
            'pageTitle' => 'Composite Risk',
            'pageHeading' => 'Composite Risk',
            'controller' => 'RiskComposite',
            'controllerDir' => 'Admin',
            'url' => 'risk-composite',
            'viewDir' => 'admin/risk-composite/',
            'breadcrumb' => array('dashboard'),
            'accessControl' => array('1')
        ],

        'riskMatrix' => [
            'id' => 'riskMatrix',
            'pageTitle' => 'Risk Matrix',
            'pageHeading' => 'Risk Matrix',
            'controller' => 'RiskMatrix',
            'controllerDir' => 'Admin',
            'url' => 'risk-matrix',
            'viewDir' => 'admin/risk-matrix/',
            'breadcrumb' => array('dashboard'),
            'extraMethods' => [ 'view-risk-matrix' => 'viewRiskMatrix' ],
            'accessControl' => array('1')
        ],

        'annexureMaster' => [
            'id' => 'annexureMaster',
            'pageTitle' => 'Annexure Master',
            'pageHeading' => 'Annexure Master',
            'controller' => 'AnnexureMaster',
            'controllerDir' => 'Admin',
            'url' => 'annexure-master',
            'viewDir' => 'admin/annexure-master/',
            'breadcrumb' => array('dashboard'),
            'accessControl' => array('1')
        ],

        'annexureColumns' => [
            'id' => 'annexureColumns',
            'pageTitle' => 'Annexure Columns',
            'pageHeading' => 'Annexure Columns',
            'controller' => 'AnnexureColumn',
            'controllerDir' => 'Admin',
            'url' => 'annexure-column',
            'viewDir' => 'admin/annexure-column/',
            'breadcrumb' => array('dashboard'),
            'accessControl' => array('1')
        ],

        'targetMaster' => [
            'id' => 'targetMaster',
            'pageTitle' => 'Target Master',
            'pageHeading' => 'Target Master',
            'controller' => 'TargetMaster',
            'controllerDir' => 'Admin',
            'url' => 'target-master',
            'viewDir' => 'admin/target-master/',
            'breadcrumb' => array('dashboard'),
            'accessControl' => array('1')
        ],

        'manageAssesment' => [
            'id' => 'manageAssesment',
            'pageTitle' => 'Manage Assesment',
            'pageHeading' => 'Manage Assesment',
            'controller' => 'ManageAssesmentController',
            'controllerDir' => 'Admin',
            'url' => 'manage-assesment',
            'viewDir' => 'admin/manage-assesment/',
            'breadcrumb' => array('dashboard'),
            'accessControl' => array(1,9),
            'extraMethods' => [ 
                'assesment-view' => 'assesmentView',
            ]
        ],

        'multiLevelControlMaster' => [
            'id' => 'multiLevelControlMaster',
            'pageTitle' => 'Manage Periodwise Questions',
            'pageHeading' => 'Manage Periodwise Questions',
            'controller' => 'MultiLevelControlMaster',
            'controllerDir' => 'Admin',
            'url' => 'periodwise-questions',
            'viewDir' => 'admin/periodwise-questions/',
            'breadcrumb' => array('dashboard'),
            'accessControl' => array(1,9),
            'extraMethods' => [ 
                'period-view' => 'periodView',
                'update-menu' => 'updateMenu',
                'update-category' => 'updateCategory',
                'update-questions' => 'updateQuestions',
                'update-scheme-advances' => 'updateSchemeAdvances',
                'update-scheme-deposits' => 'updateSchemeDeposits',
                'update-all-units' => 'updateToAllAuditUnits',
                'update-assessment' => 'updateAssessment', 
            ]
        ],

        'branchRatingMaster' => [
            'id' => 'branchRatingMaster',
            'pageTitle' => 'Branch Rating Master',
            'pageHeading' => 'Branch Rating Master',
            'controller' => 'BranchRatingMaster',
            'controllerDir' => 'Admin',
            'url' => 'branch-rating-master',
            'viewDir' => 'admin/branch-rating-master/',
            'breadcrumb' => array('dashboard'),
            'extraMethods' => [ 
                DATA_TABLE_AJX . '-year' => 'dataTableAjaxYear',
                'index-single-details' => 'indexSingleDetails',                
            ],
            'accessControl' => array(1,9)
        ],

        'exeSummaryAdmin' => [
            'id' => 'exeSummaryAdmin',
            'pageTitle' => 'Last March Position',
            'pageHeading' => 'Last March Position',
            'controller' => 'ExeSummary',
            'controllerDir' => 'Admin',
            'url' => 'exe-summary',
            'viewDir' => 'admin/exe-summary/',
            'breadcrumb' => array('dashboard'),
            'accessControl' => array('1')
        ],


        // for remark 11-06-2024
        'assesmentRemarkMaster' => [
            'id' => 'assesmentRemarkMaster',
            'pageTitle' => 'Assesment Remark Master',
            'pageHeading' => 'Assesment Remark Master',
            'controller' => 'AssesmentRemarkMaster',
            'controllerDir' => 'Audit',
            'url' => 'assesment-remark-master',
            'viewDir' => 'audit/assesment-remark-master/',
            'breadcrumb' => null,
            'accessControl' => array(2,3,4,16),
            'extraMethods' => [ 
                'get-audit-remarks' => 'getAuditRemarkData',
            ]
        ],

        'reports' => [
            'id' => 'reports',
            'pageTitle' => 'Reports',
            'pageHeading' => 'Reports',
            'controller' => 'Reports',
            'controllerDir' => 'Reports',
            'url' => 'reports',
            'viewDir' => 'reports/',
            'breadcrumb' => array('dashboard'),
            'accessControl' => array('2','4','3','10','1','11','16'),
            'extraMethods' => [ 
                'report-audit-units-ajx' => 'reportAuditUnitsAjax',
                'financial-year-report' => 'financialYearReport',
                'financial-year-wise-risk-matrix-report' => 'financialYearWiseRiskMatrixReport',
                'user-type-report' => 'userTypeReport',
                'employee-master-report' => 'employeeMasterReport', 
                'audit-section-report' => 'auditSectionReport',
                'audit-unit-master-report' => 'auditUnitMasterReport',
                'broader-area-audit-unit-report' => 'broaderAreaAuditUnitReport',
                'scheme-master-report' => 'schemeMasterReport',
                'menu-master-report' => 'menuMasterReport',
                'audit-unit-wise-financial-report' => 'auditUnitWiseFinancialReport',
                'audit-unit-wise-last-march-position-report' => 'auditUnitWiseLastMarchPositionReport',
                'audit-unit-wise-accounts-target-report' => 'auditUnitWiseAccountsTargetReport',
                'audit-frequency-and-last-assesment-done-report' => 'auditFrequencyAndLastAssesmentDoneReport',
                'audit-duration-report' => 'auditDurationReport',
                'risk-type-report' => 'riskTypeReport',
                'control-risk-key-report' => 'controlRiskKeyAspectReport',
                'category-master-report' => 'categoryMasterReport',                
                'cbs-deposit-report' => 'cbsDepositReport',
                'cbs-advances-report' => 'cbsAdvancesReport',
                'vouching-error-category-report' => 'vouchingErrorCategoryReport',
                'annexure-report' => 'annexureReport',
                'header-details-report' => 'headerDetailsReport',
                'question-set-wise-mapping-report' => 'questionSetWiseMappingReport',
                'audit-status-report' => 'auditStatusReport',
                'executive-summary-audit-report' => 'executiveSummaryAuditReport',
                'executive-summary-compliance-report' =>'executiveSummaryComplianceReport',
                'assesment-timeline-report' => 'assesmentTimelineReport',
                'accountwise-scoring-report' => 'accountwiseScoringReport',
                'assement-not-started-yet-report' => 'assementNotStartedYetReport',
                'pending-compliance-detail-report' => 'pendingComplianceDetailReport',
                'audit-status-expired-report' => 'auditStatusExpiredReport',
                'questionwsie-broader-areawise-report' => 'questionwiseBroaderAreaReport',

                // KUNAL REPORTS ---------------->
                'audit-complete-report' => 'auditCompleteReport',
                'audit-observations-report' => 'auditObservationsReport',
                'compliance-report' => 'complianceReport',
                'compliance-summary-report' => 'complianceSummaryReport',
                'broader-areawise-scoring-report' => 'broaderAreaWiseScoringReport',
                'audit-observation-count-report' => 'auditObservationCountReport',
                'risk-wise-audit-units-report' => 'riskWiseAuditUnitsReport',
                'risk-weightage-report' => 'riskWeightageReport',
                'audit-committee-board-report-1' => 'auditCommitteeBoardReport1',
                'reaudit-report' => 'reAuditReport',
                'internal-assesment-report' => 'internalAssesmentReport',
                'performance-risk-weightage-report' => 'performanceRiskWeightageReport',
                'performance-risk-weightage-report-category-wise' => 'performanceRiskWeightageReportCategoryWise',
                'rbia-performance-risk-weightage-report-all-units' => 'RBIAPerformanceRiskWeightageReportAllUnits',

                // Sahil Additional Reports ------------------>
                'category-wise-risk-weightage' => 'categoryWiseRiskWeightageReports',
                'border-aria-wise-risk-weightage' => 'borderAriaWiseRiskWeightageReport',
                'risk-category-summary' => 'riskCategorySummaryReport',
                'questionwise-consolidate-summary' => 'questionWiseConsolidateSummary',
                'get-category-related-header-data' => 'getCategoryRelatedHeaderData',
                'type-wise-risk-weightage' => 'typewiseRiskWeightageReport',
                'risk-trend-summary' => 'riskTrendSummaryReport',
                'risk-tred-summary-analysis'=> 'riskTredSummaryAnalysis',

                //prathamesh reports
                'risk-type-wise-risk-weightage-report' => 'riskTypeWiseRiskWeightageReport',
            ],
        ],

        'financialYearReport' => [
            'id' => 'financialYearReport',
            'pageTitle' => 'Financial Year Setup Report',
            'pageHeading' => 'Financial Year Setup Report',
            'controller' => 'Reports',
            'controllerDir' => 'Reports',
            'url' => 'reports/financial-year-report',
            'viewDir' => 'reports/financial-year-report/',
            'breadcrumb' => array('dashboard', 'reports'),
            'accessControl' => array('2','4','16'),
        ],

        'financialYearWiseRiskMatrixReport' => [
            'id' => 'financialYearWiseRiskMatrixReport',
            'pageTitle' => 'Financial Year Wise Risk Matrix Report',
            'pageHeading' => 'Financial Year Wise Risk Matrix Report',
            'controller' => 'Reports',
            'controllerDir' => 'Reports',
            'url' => 'reports/financial-year-wise-risk-matrix-report',
            'viewDir' => 'reports/financial-year-wise-risk-matrix-report/',
            'breadcrumb' => array('dashboard', 'reports'),
            'accessControl' => array('2','4','16'),
        ],

        'userTypeReport' => [
            'id' => 'userTypeReport',
            'pageTitle' => 'User Type Report',
            'pageHeading' => 'User Type Report',
            'controller' => 'Reports',
            'controllerDir' => 'Reports',
            'url' => 'reports/user-type-report',
            'viewDir' => 'reports/user-type-report/',
            'breadcrumb' => array('dashboard', 'reports'),
            'accessControl' => array('2','4','16'),
        ],

        'employeeMasterReport' => [
            'id' => 'employeeMasterReport',
            'pageTitle' => 'Employee Master Report',
            'pageHeading' => 'Employee Master Report',
            'controller' => 'Reports',
            'controllerDir' => 'Reports',
            'url' => 'reports/employee-master-report',
            'viewDir' => 'reports/employee-master-report/',
            'breadcrumb' => array('dashboard', 'reports'),
            'accessControl' => array('2','4','16'),
        ],

        'auditSectionReport' => [
            'id' => 'auditSectionReport',
            'pageTitle' => 'Audit Section Report',
            'pageHeading' => 'Audit Section Report',
            'controller' => 'Reports',
            'controllerDir' => 'Reports',
            'url' => 'reports/audit-section-report',
            'viewDir' => 'reports/audit-section-report/',
            'breadcrumb' => array('dashboard', 'reports'),
            'accessControl' => array('2','4','16'),
        ],

        'auditUnitMasterReport' => [
            'id' => 'auditUnitMasterReport',
            'pageTitle' => 'Audit Unit Master Report',
            'pageHeading' => 'Audit Unit Master Report',
            'controller' => 'Reports',
            'controllerDir' => 'Reports',
            'url' => 'reports/audit-unit-master-report',
            'viewDir' => 'reports/audit-unit-master-report/',
            'breadcrumb' => array('dashboard', 'reports'),
            'accessControl' => array('2','4','16'),
        ],

        'broaderAreaAuditUnitReport' => [
            'id' => 'broaderAreaAuditUnitReport',
            'pageTitle' => 'Broader Area of Audit Non-compliance Master Report',
            'pageHeading' => 'Broader Area of Audit Non-compliance Master Report',
            'controller' => 'Reports',
            'controllerDir' => 'Reports',
            'url' => 'reports/broader-area-audit-unit-report',
            'viewDir' => 'reports/broader-area-audit-unit-report/',
            'breadcrumb' => array('dashboard', 'reports'),
            'accessControl' => array('2','4','16'),
        ],

        'schemeMasterReport' => [
            'id' => 'schemeMasterReport',
            'pageTitle' => 'Scheme Master Report',
            'pageHeading' => 'Scheme Master Report',
            'controller' => 'Reports',
            'controllerDir' => 'Reports',
            'url' => 'reports/scheme-master-report',
            'viewDir' => 'reports/scheme-master-report/',
            'breadcrumb' => array('dashboard', 'reports'),
            'accessControl' => array('2','4','16'),
        ],

        'menuMasterReport' => [
            'id' => 'menuMasterReport',
            'pageTitle' => 'Menu Master Report',
            'pageHeading' => 'Menu Master Report',
            'controller' => 'Reports',
            'controllerDir' => 'Reports',
            'url' => 'reports/menu-master-report',
            'viewDir' => 'reports/menu-master-report/',
            'breadcrumb' => array('dashboard', 'reports'),
            'accessControl' => array('2','4','16'),
        ],

        'auditUnitWiseFinancialReport' => [
            'id' => 'auditUnitWiseFinancialReport',
            'pageTitle' => 'Audit Unit Wise Financial Report',
            'pageHeading' => 'Audit Unit Wise Financial Report',
            'controller' => 'Reports',
            'controllerDir' => 'Reports',
            'url' => 'reports/audit-unit-wise-financial-report',
            'viewDir' => 'reports/audit-unit-wise-financial-report/',
            'breadcrumb' => array('dashboard', 'reports'),
            'accessControl' => array('2','4','16'),
        ],

        'auditUnitWiseLastMarchPositionReport' => [
            'id' => 'auditUnitWiseLastMarchPositionReport',
            'pageTitle' => 'Audit Unit Wise Last March Position Report',
            'pageHeading' => 'Audit Unit Wise Last March Position Report',
            'controller' => 'Reports',
            'controllerDir' => 'Reports',
            'url' => 'reports/audit-unit-wise-last-march-position-report',
            'viewDir' => 'reports/audit-unit-wise-last-march-position-report/',
            'breadcrumb' => array('dashboard', 'reports'),
            'accessControl' => array('2','4','16'),
        ],

        'auditUnitWiseAccountsTargetReport' => [
            'id' => 'auditUnitWiseAccountsTargetReport',
            'pageTitle' => 'Audit Unit Wise Accounts Target Report',
            'pageHeading' => 'Audit Unit Wise Accounts Target Report',
            'controller' => 'Reports',
            'controllerDir' => 'Reports',
            'url' => 'reports/audit-unit-wise-accounts-target-report',
            'viewDir' => 'reports/audit-unit-wise-accounts-target-report/',
            'breadcrumb' => array('dashboard', 'reports'),
            'accessControl' => array('2','4','16'),
        ],

        'auditFrequencyAndLastAssesmentDoneReport' => [
            'id' => 'auditFrequencyAndLastAssesmentDoneReport',
            'pageTitle' => 'Audit Frequency and Last Assesment Done Report',
            'pageHeading' => 'Audit Frequency and Last Assesment Done Report',
            'controller' => 'Reports',
            'controllerDir' => 'Reports',
            'url' => 'reports/audit-frequency-and-last-assesment-done-report',
            'viewDir' => 'reports/audit-frequency-and-last-assesment-done-report/',
            'breadcrumb' => array('dashboard', 'reports'),
            'accessControl' => array('2','4','16'),
        ],

        'auditDurationReport' => [
            'id' => 'auditDurationReport',
            'pageTitle' => 'Audit Duration Report',
            'pageHeading' => 'Audit Duration Report',
            'controller' => 'Reports',
            'controllerDir' => 'Reports',
            'url' => 'reports/audit-duration-report',
            'viewDir' => 'reports/audit-duration-report/',
            'breadcrumb' => array('dashboard', 'reports'),
            'accessControl' => array('2','4','16'),
        ],

        'riskTypeReport' => [
            'id' => 'riskTypeReport',
            'pageTitle' => 'Risk Type Report',
            'pageHeading' => 'Risk Type Report',
            'controller' => 'Reports',
            'controllerDir' => 'Reports',
            'url' => 'reports/risk-type-report',
            'viewDir' => 'reports/risk-type-report/',
            'breadcrumb' => array('dashboard', 'reports'),
            'accessControl' => array('2','4','16'),
        ],

        'controlRiskKeyAspectReport' => [
            'id' => 'controlRiskKeyAspectReport',
            'pageTitle' => 'Control Risk - Key Aspects Report',
            'pageHeading' => 'Control Risk - Key Aspects Report',
            'controller' => 'Reports',
            'controllerDir' => 'Reports',
            'url' => 'reports/control-risk-key-report',
            'viewDir' => 'reports/control-risk-key-report/',
            'breadcrumb' => array('dashboard', 'reports'),
            'accessControl' => array('2','4','16'),
        ],

        'categoryMasterReport' => [
            'id' => 'categoryMasterReport',
            'pageTitle' => 'Category Master Report',
            'pageHeading' => 'Category Master Report',
            'controller' => 'Reports',
            'controllerDir' => 'Reports',
            'url' => 'reports/category-master-report',
            'viewDir' => 'reports/category-master-report/',
            'breadcrumb' => array('dashboard', 'reports'),
            'accessControl' => array('2','4','16'),
        ],

        'cbsDepositReport' => [
            'id' => 'cbsDepositReport',
            'pageTitle' => 'Deposit - CBS Data Upload Status Report',
            'pageHeading' => 'Deposit - CBS Data Upload Status Report',
            'controller' => 'Reports',
            'controllerDir' => 'Reports',
            'url' => 'reports/cbs-deposit-report',
            'viewDir' => 'reports/cbs-deposit-report/',
            'breadcrumb' => array('dashboard', 'reports'),
            'accessControl' => array('2','4','16'),
        ],

        'cbsAdvancesReport' => [
            'id' => 'cbsAdvancesReport',
            'pageTitle' => 'Advances - CBS Data Upload Status Report',
            'pageHeading' => 'Advances - CBS Data Upload Status Report',
            'controller' => 'Reports',
            'controllerDir' => 'Reports',
            'url' => 'reports/cbs-advances-report',
            'viewDir' => 'reports/cbs-advances-report/',
            'breadcrumb' => array('dashboard', 'reports'),
            'accessControl' => array('2','4','16'),
        ],

        'vouchingErrorCategoryReport' => [
            'id' => 'vouchingErrorCategoryReport',
            'pageTitle' => 'Vouching Error Category Report',
            'pageHeading' => 'Vouching Error Category Report',
            'controller' => 'Reports',
            'controllerDir' => 'Reports',
            'url' => 'reports/vouching-error-category-report',
            'viewDir' => 'reports/vouching-error-category-report/',
            'breadcrumb' => array('dashboard', 'reports'),
            'accessControl' => array('2','4','16'),
        ],

        'annexureReport' => [
            'id' => 'annexureReport',
            'pageTitle' => 'Annexure Report',
            'pageHeading' => 'Annexure Report',
            'controller' => 'Reports',
            'controllerDir' => 'Reports',
            'url' => 'reports/annexure-report',
            'viewDir' => 'reports/annexure-report/',
            'breadcrumb' => array('dashboard', 'reports'),
            'accessControl' => array('2','4','16'),
        ],

        'headerDetailsReport' => [
            'id' => 'headerDetailsReport',
            'pageTitle' => 'Header Details Report',
            'pageHeading' => 'Header Details Report',
            'controller' => 'Reports',
            'controllerDir' => 'Reports',
            'url' => 'reports/header-details-report',
            'viewDir' => 'reports/header-details-report/',
            'breadcrumb' => array('dashboard', 'reports'),
            'accessControl' => array('2','4','16'),
        ],

        'questionSetWiseMappingReport' => [
            'id' => 'questionSetWiseMappingReport',
            'pageTitle' => 'Question Set Wise Mapping Report',
            'pageHeading' => 'Question Set Wise Mapping Report',
            'controller' => 'Reports',
            'controllerDir' => 'Reports',
            'url' => 'reports/question-set-wise-mapping-report',
            'viewDir' => 'reports/question-set-wise-mapping-report/',
            'breadcrumb' => array('dashboard', 'reports'),
            'accessControl' => array('2','4','16'),
        ],

        'auditStatusReport' => [
            'id' => 'auditStatusReport',
            'pageTitle' => 'Audit Status Report',
            'pageHeading' => 'Audit Status Report',
            'controller' => 'Reports',
            'controllerDir' => 'Reports',
            'url' => 'reports/audit-status-report',
            'viewDir' => 'reports/audit-status-report/',
            'breadcrumb' => array('dashboard', 'reports'),
            'accessControl' => array('2','4','3','16'),
        ],

        'executiveSummaryAuditReport' => [
            'id' => 'executiveSummaryAuditReport',
            'pageTitle' => 'Executive Summary Audit Report',
            'pageHeading' => 'Executive Summary Audit Report',
            'controller' => 'Reports',
            'controllerDir' => 'Reports',
            'url' => 'reports/executive-summary-audit-report',
            'viewDir' => 'reports/executive-summary-audit-report/',
            'breadcrumb' => array('dashboard', 'reports'),
            'accessControl' => array('2','4','3','16'),
        ],
        
        'executiveSummaryComplianceReport' => [
            'id' => 'executiveSummaryComplianceReport',
            'pageTitle' => 'Executive Summary Compliance Report',
            'pageHeading' => 'Executive Summary Compliance Report',
            'controller' => 'Reports',
            'controllerDir' => 'Reports',
            'url' => 'reports/executive-summary-compliance-report',
            'viewDir' => 'reports/executive-summary-audit-report/',
            'breadcrumb' => array('dashboard', 'reports'),
            'accessControl' => array('2','4','3','16'),
        ],

        'assesmentTimelineReport' => [
            'id' => 'assesmentTimelineReport',
            'pageTitle' => 'Assesment Timeline Report',
            'pageHeading' => 'Assesment Timeline Report',
            'controller' => 'Reports',
            'controllerDir' => 'Reports',
            'url' => 'reports/assesment-timeline-report',
            'viewDir' => 'reports/assesment-timeline-report/',
            'breadcrumb' => array('dashboard', 'reports'),
            'accessControl' => array('2','4','16'),
        ],

        'accountwiseScoringReport' => [
            'id' => 'accountwiseScoringReport',
            'pageTitle' => 'Account-Wise Scoring Report',
            'pageHeading' => 'Account-Wise Scoring Report',
            'controller' => 'Reports',
            'controllerDir' => 'Reports',
            'url' => 'reports/accountwise-scoring-report',
            'viewDir' => 'reports/accountwise-scoring-report/',
            'breadcrumb' => array('dashboard', 'reports'),
            'accessControl' => array('2','4','16'),
        ],

        'assementNotStartedYetReport' => [
            'id' => 'assementNotStartedYetReport',
            'pageTitle' => 'Assement Not Started Yet Report',
            'pageHeading' => 'Assement Not Started Yet Report',
            'controller' => 'Reports',
            'controllerDir' => 'Reports',
            'url' => 'reports/assement-not-started-yet-report',
            'viewDir' => 'reports/assement-not-started-yet-report/',
            'breadcrumb' => array('dashboard', 'reports'),
            'accessControl' => array('2','4','16'),
        ],
        
        'pendingComplianceDetailReport' => [
            'id' => 'pendingComplianceDetailReport',
            'pageTitle' => 'Pending Compliance Detailed Report',
            'pageHeading' => 'Pending Compliance Detailed Report',
            'controller' => 'Reports',
            'controllerDir' => 'Reports',
            'url' => 'reports/pending-compliance-detail-report',
            'viewDir' => 'reports/pending-compliance-detail-report/',
            'breadcrumb' => array('dashboard', 'reports'),
            'accessControl' => array('2','4','16'),
        ],

        'auditStatusExpiredReport' => [
            'id' => 'auditStatusExpiredReport',
            'pageTitle' => 'Audit Status Expired Report',
            'pageHeading' => 'Audit Status Expired Report',
            'controller' => 'Reports',
            'controllerDir' => 'Reports',
            'url' => 'reports/audit-status-expired-report',
            'viewDir' => 'reports/audit-status-expired-report/',
            'breadcrumb' => array('dashboard', 'reports'),
            'accessControl' => array('2','4','16'),
        ],

        'questionwiseBroaderAreaReport' => [
            'id' => 'questionwiseBroaderAreaReport',
            'pageTitle' => 'Question Wise BroaderArea Report',
            'pageHeading' => 'Question Wise BroaderArea Report',
            'controller' => 'Reports',
            'controllerDir' => 'Reports',
            'url' => 'reports/questionwsie-broader-areawise-report',
            'viewDir' => 'reports/questionwsie-broader-areawise-report/',
            'breadcrumb' => array('dashboard', 'reports'),
            'accessControl' => array('2','4','16'),
        ],

        // KUNAL REPORTS ---------------->

        'auditCompleteReport' => [
            'id' => 'auditCompleteReport',
            'pageTitle' => 'Audit Complete Report',
            'pageHeading' => 'Audit Complete Report',
            'controller' => 'Reports',
            'controllerDir' => 'Reports',
            'url' => 'reports/audit-complete-report',
            'viewDir' => 'reports/audit-complete-report/',
            'breadcrumb' => array('dashboard', 'reports'),
            'accessControl' => array('2','4','3','16'),
        ],

        'auditObservationsReport' => [
            'id' => 'auditObservationsReport',
            'pageTitle' => 'Audit Observations Report',
            'pageHeading' => 'Audit Observations Report',
            'controller' => 'Reports',
            'controllerDir' => 'Reports',
            'url' => 'reports/audit-observations-report',
            'viewDir' => 'reports/audit-observations-report/',
            'breadcrumb' => array('dashboard', 'reports'),
            'accessControl' => array('2','4','16'),
        ],

        'complianceReport' => [
            'id' => 'complianceReport',
            'pageTitle' => 'Compliance Report',
            'pageHeading' => 'Compliance Report',
            'controller' => 'Reports',
            'controllerDir' => 'Reports',
            'url' => 'reports/compliance-report',
            'viewDir' => 'reports/compliance-report/',
            'breadcrumb' => array('dashboard', 'reports'),
            'accessControl' => array('2','4','3','16'),
        ],

        'complianceSummaryReport' => [
            'id' => 'complianceSummaryReport',
            'pageTitle' => 'Compliance Summary Report',
            'pageHeading' => 'Compliance Summary Report',
            'controller' => 'Reports',
            'controllerDir' => 'Reports',
            'url' => 'reports/compliance-summary-report',
            'viewDir' => 'reports/compliance-summary-report/',
            'breadcrumb' => array('dashboard', 'reports'),
            'accessControl' => array('2','4','3','16'),
        ],

        'broaderAreaWiseScoringReport' => [
            'id' => 'broaderAreaWiseScoringReport',
            'pageTitle' => 'Broader Areawise Scoring Report',
            'pageHeading' => 'Broader Areawise Scoring Report',
            'controller' => 'Reports',
            'controllerDir' => 'Reports',
            'url' => 'reports/broader-areawise-scoring-report',
            'viewDir' => 'reports/broader-areawise-scoring-report/',
            'breadcrumb' => array('dashboard', 'reports'),
            'accessControl' => array('2','4','16'),
        ],

        'riskWiseAuditUnitsReport' => [
            'id' => 'riskWiseAuditUnitsReport',
            'pageTitle' => 'Risk Wise Audit Units Report',
            'pageHeading' => 'Risk Wise Audit Units Report',
            'controller' => 'Reports',
            'controllerDir' => 'Reports',
            'url' => 'reports/risk-wise-audit-units-report',
            'viewDir' => 'reports/broader-areawise-risk-wise-audit-units-report/',
            'breadcrumb' => array('dashboard', 'reports'),
            'accessControl' => array('2','4','16'),
        ],

        'riskWeightageReport' => [
            'id' => 'riskWeightageReport',
            'pageTitle' => 'Risk Weightage Report',
            'pageHeading' => 'Risk Weightage Report',
            'controller' => 'Reports',
            'controllerDir' => 'Reports',
            'url' => 'reports/risk-weightage-report',
            'viewDir' => 'reports/broader-areawise-risk-weightage-report/',
            'breadcrumb' => array('dashboard', 'reports'),
            'accessControl' => array('2','4','16'),
        ],

        'auditObservationCountReport' => [
            'id' => 'auditObservationCountReport',
            'pageTitle' => 'Audit Observation Count Report',
            'pageHeading' => 'Audit Observation Count Report',
            'controller' => 'Reports',
            'controllerDir' => 'Reports',
            'url' => 'reports/audit-observation-count-report',
            'viewDir' => 'reports/audit-observation-count-report/',
            'breadcrumb' => array('dashboard', 'reports'),
            'accessControl' => array('2','4','16'),
        ],

        'auditCommitteeBoardReport1' => [
            'id' => 'auditCommitteeBoardReport1',
            'pageTitle' => 'Audit Committee Board Report - 1',
            'pageHeading' => 'Audit Committee Board Report - 1',
            'controller' => 'Reports',
            'controllerDir' => 'Reports',
            'url' => 'reports/audit-committee-board-report-1',
            'viewDir' => 'reports/audit-committee-board-report-1/',
            'breadcrumb' => array('dashboard', 'reports'),
            'accessControl' => array('2','4','16'),
        ],

        'reAuditReport' => [
            'id' => 'reAuditReport',
            'pageTitle' => 'Re Audit Report',
            'pageHeading' => 'Re Audit Report',
            'controller' => 'Reports',
            'controllerDir' => 'Reports',
            'url' => 'reports/reaudit-report',
            'viewDir' => 'reports/reaudit-report/',
            'breadcrumb' => array('dashboard', 'reports'),
            'accessControl' => array('2','4','16'),
        ],

        'internalAssesmentReport' => [
            'id' => 'internalAssesmentReport',
            'pageTitle' => 'Internal Audit & Compliance Report',
            'pageHeading' => 'Internal Audit & Compliance Report',
            'controller' => 'Reports',
            'controllerDir' => 'Reports',
            'url' => 'reports/internal-assesment-report',
            'viewDir' => 'reports/internal-assesment-report/',
            'breadcrumb' => array('dashboard', 'reports'),
            'accessControl' => array('2','4','16'),
        ],

        // performance report 08.11.2024
        'performanceRiskWeightageReport' => [
            'id' => 'performanceRiskWeightageReport',
            'pageTitle' => 'Performance Risk Weightage Report',
            'pageHeading' => 'Performance Risk Weightage Report',
            'controller' => 'Reports',
            'controllerDir' => 'Reports',
            'url' => 'reports/performance-risk-weightage-report',
            'viewDir' => 'reports/performance-risk-weightage-report/',
            'breadcrumb' => array('dashboard', 'reports'),
            'accessControl' => array('2','4','16'),
        ],

        'performanceRiskWeightageReportCategoryWise' => [
            'id' => 'performanceRiskWeightageReportCategoryWise',
            'pageTitle' => 'Performance Risk Weightage Report (Category Wise)',
            'pageHeading' => 'Performance Risk Weightage Report (Category Wise)',
            'controller' => 'Reports',
            'controllerDir' => 'Reports',
            'url' => 'reports/performance-risk-weightage-report-category-wise',
            'viewDir' => 'reports/performance-risk-weightage-report-category-wise/',
            'breadcrumb' => array('dashboard', 'reports'),
            'accessControl' => array('2','4','16'),
        ],

        'RBIAPerformanceRiskWeightageReportAllUnits' => [
            'id' => 'RBIAPerformanceRiskWeightageReportAllUnits',
            'pageTitle' => 'RBIA - Performance Risk Weightage Report (All Units)',
            'pageHeading' => 'RBIA - Performance Risk Weightage Report (All Units)',
            'controller' => 'Reports',
            'controllerDir' => 'Reports',
            'url' => 'reports/rbia-performance-risk-weightage-report-all-units',
            'viewDir' => 'reports/rbia-performance-risk-weightage-report-all-units/',
            'breadcrumb' => array('dashboard', 'reports'),
            'accessControl' => array('2','4','16'),
        ],

        'questionDownload' => [
            'id' => 'questionDownload',
            'pageTitle' => 'Question Master Download',
            'pageHeading' => 'Question Master Download',
            'controller' => 'QuestionDownload',
            'controllerDir' => 'Admin',
            'url' => 'question-download',
            'viewDir' => 'admin/question-download/',
            'breadcrumb' => array('dashboard'),
            'accessControl' => array(1,9)
        ],

        'questionDownloadBroaderArea' => [
            'id' => 'questionDownloadBroaderArea',
            'pageTitle' => 'Question Master Download',
            'pageHeading' => 'Question Master Download (Broader Area Wise)',
            'controller' => 'QuestionDownload',
            'controllerDir' => 'Admin',
            'url' => 'question-download-broaderarea',
            'viewDir' => 'admin/question-download-broaderarea/',
            'breadcrumb' => array('dashboard'),
            'accessControl' => array(1,9)
        ],

        'print' => [
            'id' => 'print',
            'pageTitle' => 'Print',
            'pageHeading' => 'Print',
            'controller' => 'PrintPreview',
            'controllerDir' => null,
            'url' => 'print',
            'viewDir' => 'print',
            'breadcrumb' => array('dashboard', 'print'),
            'accessControl' => array('1','2','3','4','5','6','7','16'),
        ],

        // COMPLIANCE PRO 16.09.2024
        'complianceCircularAuthority' => [
            'id' => 'complianceCircularAuthority',
            'pageTitle' => 'Compliance Circular Authority',
            'pageHeading' => 'Compliance Circular Authority',
            'controller' => 'ComplianceCircularAuthority',
            'controllerDir' => 'CompliancePro',
            'url' => 'compliance-circular-authority',
            'viewDir' => 'compliance-pro/circular-authority/',
            'breadcrumb' => array('dashboard'),
            'accessControl' => array('1','6','7')
        ],

        'complianceProAI' => [
            'id' => 'complianceProAI',
            'pageTitle' => 'Compliance With AI',
            'pageHeading' => '',
            'controller' => 'ComplianceProAI',
            'controllerDir' => 'CompliancePro',
            'url' => 'compliance-pro-ai',
            'viewDir' => 'compliance-pro/compliance-pro-ai/',
            //'extraMethods' => ['com-authority' => 'complianceAuthority'],
            'accessControl' => array('3', '6', '7'),
        ],


        'complianceCircularSetMaster' => [
            'id' => 'complianceCircularSetMaster',
            'pageTitle' => 'Compliance Circular Master',
            'pageHeading' => 'Compliance Circular Master',
            'controller' => 'ComplianceCircularSetMaster',
            'controllerDir' => 'CompliancePro',
            'url' => 'compliance-circular-set-master',
            'viewDir' => 'compliance-pro/circular-set-master/',
            'breadcrumb' => array('dashboard'),
            'accessControl' => array('1','6','7'),
            'extraMethods' => [ 
                'view-circular' => 'viewCircular',
                'applicable-status' => 'applicableStatus',
            ]
        ],

        'complianceCircularHeaderMaster' => [
            'id' => 'complianceCircularHeaderMaster',
            'pageTitle' => 'Task Header Master',
            'pageHeading' => 'Task Header Master',
            'controller' => 'ComplianceCircularHeaderMaster',
            'controllerDir' => 'CompliancePro',
            'url' => 'compliance-circular-header-master',
            'viewDir' => 'compliance-pro/header-master/',
            'breadcrumb' => array('dashboard'),
            'accessControl' => array('1','6','7')
        ],


        'complianceCircularTaskMaster' => [
            'id' => 'complianceCircularTaskMaster',
            'pageTitle' => 'Circular Task Master',
            'pageHeading' => 'Circular Task Master',
            'controller' => 'ComplianceCircularTaskMaster',
            'controllerDir' => 'CompliancePro',
            'url' => 'compliance-circular-task-master',
            'viewDir' => 'compliance-pro/task-master/',
            'breadcrumb' => array('dashboard'),
            'accessControl' => array('1','6','7'),
            'extraMethods' => [ 
                'header-ajx' => 'findHeaderAjax',
                'bulk-upload-circular-task' => 'bulkUploadCircularTask'
            ]
        ],

        'complianceCircularTaskSet' => [
            'id' => 'complianceCircularTaskSet',
            'pageTitle' => 'Circular Task Set',
            'pageHeading' => 'Circular Task Set',
            'controller' => 'ComplianceCircularTaskSet',
            'controllerDir' => 'CompliancePro',
            'url' => 'compliance-circular-task-set',
            'viewDir' => 'compliance-pro/task-set/',
            'breadcrumb' => array('dashboard'),
            'accessControl' => array(1,6,7),
            'extraMethods' => [ 
                'view-circular' => 'viewCircular',
            ]
        ],

        'complianceCircularAssesData' => [
            'id' => 'complianceCircularAssesData',
            'pageTitle' => 'Circular List',
            'pageHeading' => 'Circular List',
            'controller' => 'ComplianceCircularAssesData',
            'controllerDir' => 'CompliancePro',
            'url' => 'compliance-circular-asses-data',
            'viewDir' => 'compliance-pro/compliance-circular-asses-data/',
            'breadcrumb' => array('dashboard'),
            'accessControl' => array('1','6','7'),
            'extraMethods' => [ 
                'view-circular' => 'viewCircular',
                // 'assign' => 'assign',
                // 'submit-report' => 'submitReport'
            ]
        ],

        'complianceProDashboard' => [
            'id' => 'complianceProDashboard',
            'pageTitle' => 'Compliance Dashboard',
            'pageHeading' => 'Compliance Dashboard',
            'controller' => 'Dashboard',
            'controllerDir' => 'CompliancePro',
            'url' => 'compliance-dashboard',
            'viewDir' => 'compliance-pro/dashboard/',
            'breadcrumb' => array('dashboard'),
            'extraMethods' => [ 'com-authority' => 'complianceAuthority' ],
            'accessControl' => array('3','6','7'),
        ],

        'complianceAssessment' => [
            'id' => 'complianceAssessment',
            'pageTitle' => 'Compliance',
            'pageHeading' => 'Compliance',
            'controller' => 'ComplianceAssessment',
            'controllerDir' => 'CompliancePro',
            'url' => 'compliance-assesment',
            'viewDir' => 'compliance-pro/assesment/',
            'breadcrumb' => array('dashboard'),
            'extraMethods' => [ 'com-action' => 'assesment' ],
            'accessControl' => array('3', '6')
        ],

        'compliancePro' => [
            'id' => 'compliancePro',
            'pageTitle' => 'Compliance',
            'pageHeading' => 'Compliance',
            'controller' => 'ComplianceProController',
            'controllerDir' => 'CompliancePro',
            'url' => 'compliance-pro',
            'viewDir' => 'compliance-pro/compliance/',
            'breadcrumb' => array('dashboard'),
            'extraMethods' => [ 'compliance' => 'compliance', 're-compliance' => 'reCompliance', 'save-compliance' => 'saveCompliance' ],
            'accessControl' => array('3')
        ],

        'complianceAssessmentReviewer' => [
            'id' => 'complianceAssessmentReviewer',
            'pageTitle' => 'Compliance Review',
            'pageHeading' => 'Compliance Review',
            'controller' => 'ComplianceAssessmentReviewer',
            'controllerDir' => 'CompliancePro',
            'url' => 'compliance-assesment-review',
            'viewDir' => 'compliance-pro/reviewer/',
            'breadcrumb' => array('dashboard'),
            'extraMethods' => [ 
                'review-compliance' => 'reviewCompliance', 
                'save-status' => 'saveStatus', 
                'save-comment' => 'saveComment',
                'submit-compliance-review' => 'submitComplianceReview',
            ],
            'accessControl' => array('6','16')
        ],

        'complianceProDocsUpload' => [
            'id' => 'complianceProDocsUpload',
            'pageTitle' => 'Compliance Pro Docs Upload',
            'pageHeading' => 'Compliance Pro Docs Upload',
            'controller' => 'ComplianceProDocsUpload',
            'controllerDir' => 'CompliancePro',
            'url' => 'compliance-pro-docs-upload',
            'viewDir' => null,
            'breadcrumb' => null,
            'extraMethods' => [ 'upload' => 'uploadDocs' ],
            'accessControl' => array('3','6','7')
        ],

        'complianceProReports' => [
            'id' => 'complianceProReports',
            'pageTitle' => 'Other Compliance Reports',
            'pageHeading' => 'Other Compliance Reports',
            'controller' => 'ComplianceProReports',
            'controllerDir' => 'CompliancePro',
            'url' => 'compliance-pro-reports',
            'viewDir' => 'compliance-pro/reports/',
            'breadcrumb' => array('complianceProDashboard'),
            'accessControl' => array('6'),
            'extraMethods' => [ 
                'authority-report' => 'authorityReport',
                'circulars-report' => 'circularsReport',
                'status-report' => 'statusReport',
                'compliance-summary-report' => 'complianceSummaryReport',
                'non-compliance-escalation-report' => 'nonComplianceEscalationReport',
            ],
        ],

        'complianceAuthorityReport' => [
            'id' => 'complianceAuthorityReport',
            'pageTitle' => 'Compliance Authority Report',
            'pageHeading' => 'Compliance Authority Report',
            'controller' => 'ComplianceProReports',
            'controllerDir' => 'CompliancePro',
            'url' => 'compliance-pro-reports/authority-report',
            'viewDir' => 'compliance-pro/reports/authority-report/',
            'breadcrumb' => array('complianceProDashboard', 'complianceProReports'),
            'accessControl' => array('6'),
        ],

        'complianceCircularsReport' => [
            'id' => 'complianceCircularsReport',
            'pageTitle' => 'Compliance Circulars Report',
            'pageHeading' => 'Compliance Circulars Report',
            'controller' => 'ComplianceProReports',
            'controllerDir' => 'CompliancePro',
            'url' => 'compliance-pro-reports/circulars-report',
            'viewDir' => 'compliance-pro/reports/circulars-report/',
            'breadcrumb' => array('complianceProDashboard', 'complianceProReports'),
            'accessControl' => array('6'),
        ],

        'complianceStatusReport' => [
            'id' => 'complianceStatusReport',
            'pageTitle' => 'Compliance Status Report',
            'pageHeading' => 'Compliance Status Report',
            'controller' => 'ComplianceProReports',
            'controllerDir' => 'CompliancePro',
            'url' => 'compliance-pro-reports/status-report',
            'viewDir' => 'compliance-pro/reports/status-report/',
            'breadcrumb' => array('complianceProDashboard', 'complianceProReports'),
            'accessControl' => array('6'),
        ],

        'complianceProSummaryReport' => [
            'id' => 'complianceProSummaryReport',
            'pageTitle' => 'Compliance Summary Report',
            'pageHeading' => 'Compliance Summary Report',
            'controller' => 'ComplianceProReports',
            'controllerDir' => 'CompliancePro',
            'url' => 'compliance-pro-reports/compliance-summary-report',
            'viewDir' => 'compliance-pro/reports/compliance-summary-report/',
            'breadcrumb' => array('complianceProDashboard', 'complianceProReports'),
            'accessControl' => array('6'),
        ],

        'nonComplianceEscalationReport' => [
            'id' => 'nonComplianceEscalationReport',
            'pageTitle' => 'Non-compliance Escalation Report',
            'pageHeading' => 'Non-compliance Escalation Report',
            'controller' => 'ComplianceProReports',
            'controllerDir' => 'CompliancePro',
            'url' => 'compliance-pro-reports/non-compliance-escalation-report',
            'viewDir' => 'compliance-pro/reports/non-compliance-escalation-report/',
            'breadcrumb' => array('complianceProDashboard', 'complianceProReports'),
            'accessControl' => array('6'),
        ],

        'complianceCircularAi' => [
            'id' => 'complianceCircularAi',
            'pageTitle' => 'Compliance with AI',
            'pageHeading' => 'Compliance with AI',
            'controller' => 'complianceCircularAi',
            'controllerDir' => 'CompliancePro',
            'url' => 'compliance-circular-ai',
            'viewDir' => 'compliance-pro/compliance-circular-ai/',
            'breadcrumb' => array('dashboard'),
            'accessControl' => array('1','6','7')
        ],

        'categoryWiseRiskWeightageReports' => [
            'id' => 'categoryWiseRiskWeightageReports',
            'pageTitle' => 'Categorywise Risk Weightage Reports',
            'pageHeading' => 'Categorywise Risk Weightage Reports',
            'controller' => 'Reports',
            'controllerDir' => 'Reports',
            'url' => 'reports/category-wise-risk-weightage',
            'viewDir' => 'reports/category-wise-risk-weightage/',
            'breadcrumb' => array('dashboard', 'reports'),
            'accessControl' => array('2','4','16'),
        ],

        'borderAriaWiseRiskWeightageReport' => [
            'id' => 'borderAriaWiseRiskWeightageReport',
            'pageTitle' => 'Broader Areawise Risk Weightage Report',
            'pageHeading' => 'Broader Areawise Risk Weightage Report',
            'controller' => 'Reports',
            'controllerDir' => 'Reports',
            'url' => 'reports/border-aria-wise-risk-weightage',
            'viewDir' => 'reports/border-aria-wise-risk-weightage/',
            'breadcrumb' => array('dashboard', 'reports'),
            'accessControl' => array('2','4','16'),
        ],

        'riskCategorySummaryReport' => [
            'id' => 'riskCategorySummaryReport',
            'pageTitle' => 'Risk Category Summary Report',
            'pageHeading' => 'Risk Category Summary Report',
            'controller' => 'Reports',
            'controllerDir' => 'Reports',
            'url' => 'reports/risk-category-summary',
            'viewDir' => 'reports/risk-category-summary/',
            'breadcrumb' => array('dashboard', 'reports'),
            'accessControl' => array('2','4','16'),
        ],

        'questionWiseConsolidateSummary' => [
            'id' => 'questionWiseConsolidateSummary',
            'pageTitle' => 'Questionwise Consolidate Summary',
            'pageHeading' => 'Questionwise Consolidate Summary',
            'controller' => 'Reports',
            'controllerDir' => 'Reports',
            'url' => 'reports/questionwise-consolidate-summary',
            'viewDir' => 'reports/questionwise-consolidate-summary/',
            'breadcrumb' => array('dashboard', 'reports'),
            'accessControl' => array('2','4','16'),
        ],

        'typewiseRiskWeightageReport' => [
            'id' => 'typewiseRiskWeightageReport',
            'pageTitle' => 'Typewise Risk Weightage Report',
            'pageHeading' => 'Typewise Risk Weightage Report',
            'controller' => 'Reports',
            'controllerDir' => 'Reports',
            'url' => 'reports/type-wise-risk-weightage',
            'viewDir' => 'reports/type-wise-risk-weightage/',
            'breadcrumb' => array('dashboard', 'reports'),
            'accessControl' => array('2','4','16'),
        ],

        'riskTrendSummaryReport' => [
            'id' => 'riskTrendSummaryReport',
            'pageTitle' => 'Risk Trend Summary Report',
            'pageHeading' => 'Risk Trend Summary Report',
            'controller' => 'Reports',
            'controllerDir' => 'Reports',
            'url' => 'reports/risk-trend-summary',
            'viewDir' => 'reports/risk-trend-summary/',
            'breadcrumb' => array('dashboard', 'reports'),
            'accessControl' => array('2','4','16'),
        ],

        'riskTredSummaryAnalysis' => [
            'id' => 'riskTredSummaryAnalysis',
            'pageTitle' => 'Risk Trend Summary Analysis',
            'pageHeading' => 'Risk Trend Summary Analysis',
            'controller' => 'Reports',
            'controllerDir' => 'Reports',
            'url' => 'reports/risk-tred-summary-analysis',
            'viewDir' => 'reports/risk-tred-summary-analysis/',
            'breadcrumb' => array('dashboard', 'reports'),
            'accessControl' => array('2','4','16'),
        ],
        // SUPPORT MODULE 16.09.2024
        'dateChange' => [
            'id' => 'dateChange',
            'pageTitle' => 'Date Change',
            'pageHeading' => 'Date Change',
            'controller' => 'DateChange',
            'controllerDir' => 'Support',
            'url' => 'date-change',
            'viewDir' => 'support/date-change/',
            'breadcrumb' => array('supportDashboard'),
                'accessControl' => array('1','10')
        ],
        'supportDashboard' => [
            'id' => 'supportDashboard',
            'pageTitle' => 'Support Dashboard',
            'pageHeading' => 'Support Dashboard',
            'controller' => 'SupportDashboard',
            'controllerDir' => 'Support',
            'url' => 'support-dashboard',
            'viewDir' => 'support/dashboard/',
            'breadcrumb' => array('supportDashboard'),
            'accessControl' => array('1','10')
        ],
        'superAdminDashboard' => [
            'id' => 'superAdminDashboard',
            'pageTitle' => 'Super Admin Dashboard',
            'pageHeading' => 'Super Admin Dashboard',
            'controller' => 'SuperAdminDashboard',
            'controllerDir' => 'SuperAdmin',
            'url' => 'super-admin-dashboard',
            'viewDir' => 'superadmin/dashboard/',
            'breadcrumb' => array('superAdminDashboard'),
            'accessControl' => array('11','2','3','4','1','9','16')
        ],
        'riskTypeWiseRiskWeightageReport' => [
            'id' => 'riskTypeWiseRiskWeightageReport',
            'pageTitle' => 'Risk Type Wise Risk Weightage Report',
            'pageHeading' => 'Risk Type Wise Risk Weightage Report',
            'controller' => 'Reports',
            'controllerDir' => 'Reports',
            'url' => 'reports/risk-type-wise-risk-weightage-report',
            'viewDir' => 'reports/risk-type-wise-risk-weightage-report/',
            'breadcrumb' => array('dashboard', 'reports'),
            'accessControl' => array('2','4','16'),
        ],
        

    );

    public static function has( $key )
	{
		return isset( self::$site_urls[ $key ] );
	}

    public static function get( $key )
	{
		return self::has( $key ) ? json_decode(json_encode(self::$site_urls[ $key ]), FALSE) : null;
	}

    public static function getUrl( $key )
	{
		return self::has( $key ) ? (URL . self::$site_urls[ $key ]['url']) : URL;
	}

    public static function setUrl( $shortUrl )
	{
		return (URL . $shortUrl);
	}

    public static function getCurrentUrl()
	{
        $query = explode( 'url=', $_SERVER['QUERY_STRING']);

        if(!is_array($query) || (is_array($query) && !(sizeof($query) > 1)))
            return NULL;   

        return (URL . $query[1]);
	}

    public static function findMathchingController( $controller )
	{
        $me = null;

        foreach ( self::$site_urls as $cSiteId => $cSiteDetails )
        {
            if( $cSiteDetails['controller'] == ucwords($controller) || 
                $cSiteDetails['url'] == $controller )
            {
                $me = self::get($cSiteId);
                break;
            }
        }

		return $me;
	}
}


?>