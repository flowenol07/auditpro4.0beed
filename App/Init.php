<?php

ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);

// Start session
if (session_status() === PHP_SESSION_NONE) {
	session_start();
}

//set default time zone
date_default_timezone_set('Asia/Kolkata');

//set bank name
defined( 'BANK_NAME' ) ?: define( 'BANK_NAME', 'Nasikroad Deolali Vyapari Sahakari Bank Ltd.' );

// Root for the app directory
defined( 'DS' ) ?: define( 'DS', DIRECTORY_SEPARATOR );
defined( 'APP_ROOT' ) ?: define('APP_ROOT', dirname(__DIR__) . DS . 'App');
defined( 'APP_CORE' ) ?: define('APP_CORE', APP_ROOT . DS . 'Core');
defined( 'APP_VIEWS' ) ?: define('APP_VIEWS', APP_ROOT . DS . 'Views');
defined( 'IMAGES_ROOT' ) ?: define('IMAGES_ROOT', dirname(__DIR__) . DS . 'Public' . DS . 'images' . DS);
defined( 'PROFILE_IMG_ROOT' ) ?: define('PROFILE_IMG_ROOT', IMAGES_ROOT . 'profile-pic' . DS);

defined( 'CONTROLLER' ) ?: define('CONTROLLER', APP_ROOT . DS . 'Controllers');
defined( 'CONTROLLER_NAMESPACE' ) ?: define('CONTROLLER_NAMESPACE', 'Controllers');

defined( 'REPORTS_VIEW' ) ?: define('REPORTS_VIEW', APP_VIEWS . DS . 'reports');

// Root for the public directory
defined( 'PUBLIC_ROOT' ) ?: define('PUBLIC_ROOT', dirname(__DIR__) . DS . 'Public');

// Public URL
defined( 'HOST' ) ?: define('HOST', 'http://192.168.1.33/');
defined( 'URL' ) ?: define('URL', HOST . 'auditpro4.0beed/');
defined( 'PUBLIC_JS' ) ?: define('PUBLIC_JS', URL . 'js/');
defined( 'IMAGES' ) ?: define('IMAGES', URL . 'images/');
defined( 'PROFILE_IMG' ) ?: define('PROFILE_IMG', URL . 'images/profile-pic/');
defined( 'ASSETS_IMG' ) ?: define('ASSETS_IMG', URL . 'resources/img/');
defined( 'DATA_TABLE_AJX' ) ?: define('DATA_TABLE_AJX', 'data-table-ajx');

// Multi-audit configuration with branches and default
if (!defined('IS_MULTI_AUDIT')) {
    define('IS_MULTI_AUDIT', [
        'isValid' => 0, // 1 = multi-DB enabled, 0 = single DB
        'branches' => [
            'ndvs'  => ['db' => 'ndvs_25-26', 'label' => 'NDVS Bank'],
            'sutex' => ['db' => 'sutex', 'label' => 'Sutex Bank'],
            'ajara' => ['db' => 'ajara_25_26', 'label' => 'Ajara Bank'],
			'veershaiv_24_25' => ['db' => 'veershaiv_24_25', 'label' => 'Veershaive Bank 24-25'],
			'veershaiv_25_26' => ['db' => 'veershaiv_25_26', 'label' => 'Veershaive Bank 25-26'],
			'deccan' => ['db' => 'deccan', 'label' => 'Deccan Bank'],
        ],
        'default' => $_SESSION['audit-type'] ?? 'deccan',
    ]);
}

// Determine selected branch
$selectedBranch = (IS_MULTI_AUDIT['isValid'] ?? 0) ? IS_MULTI_AUDIT['default'] : 'deccan';

// Define default DB name
if (!defined('DB_NAME')) {
    define(
        'DB_NAME',
        IS_MULTI_AUDIT['branches'][$selectedBranch]['db'] ?? 'deccan'
    );
}

// Database credentials (same for all branches)
defined('DB_HOST') ?: define('DB_HOST', 'localhost:3306');
defined('DB_USER') ?: define('DB_USER', 'root');
defined('DB_PASS') ?: define('DB_PASS', '');

// File maximum size, in mb
defined( 'FILE_MAX_SIZE' ) ?: define('FILE_MAX_SIZE', 4);


// Allowed extensions
defined( 'FILE_EXT' ) ?: define('FILE_EXT', array( 
	'img' => ['jpg', 'jpeg', 'png'],
	'pdf' => ['pdf'],
));

// Allowed extensions
defined( 'ENCRYPT_EXT' ) ?: define('ENCRYPT_EXT', array( 
	'encryptionKey' => 'e8df9f9c5781fda6606ac2062bb74d962bf5f70aa11dfdbc1d9f73d5a6176541',
	'cipherMethod' => 'AES-128-CBC',
	'encrypt' => 1,
));

// Env Config
defined( 'ENV_CONFIG' ) ?: define('ENV_CONFIG', array( 
	'executive_summary_review' => true,
	'question_parameter_limit' => 5,
	'advances_id' => 8
));

// Admin Panel Action Disable
defined( 'ADMIN_ACT_DISABLE' ) ?: define('ADMIN_ACT_DISABLE', 0);

// Root for the upload directory
// define('UPLOAD_ROOT', PUBLIC_ROOT.'/uploads');

// Cookie expiry time in seconds
// define("COOKIE_EXPIRY", 7 * 86400);

// Autoload classes
// spl_autoload_register(function ($class) {
//     require_once 'Core/' . $class . '.php';
// });

//support date formates
$dateSupportArray = array(
	1 => 'Y-m-d',
	2 => 'Y-m-d H:i:s',
);

$userTypesArray = array(
	9 => 'Admin',
	2 => 'Auditor',
	3 => 'Employee',
	4 => 'Reviewer',
	5 => 'Top Level Managemet',
	6 => 'CCO - Chief Compliance Officer',
	7 => 'Assistant to Chief Compliance Officer',
	11 => 'Super Admin',
	16 => 'RO Officer',
);

defined( 'AUDIT_DUE_ARRAY' ) ?: define('AUDIT_DUE_ARRAY', [
	1 => 15, //for audit
	2 => 15, //for audit review
	3 => 15, //for compliance
	4 => 15, //for compliance review
]);

//status array
defined( 'STATUS_ARRAY' ) ?: define('STATUS_ARRAY', [
	1 => "Active", 
	2 => "Inactive",
]);

//audit type array
defined( 'AUDIT_TYPE_ARRAY' ) ?: define('AUDIT_TYPE_ARRAY', [
	1 => "RBI Audit", 
	2 => "Concurrent Audit",
]);

defined( 'AUDIT_STATUS_ARRAY' ) ?: define('AUDIT_STATUS_ARRAY', [

	'review_reject_limit' => [ 'audit' => 5, 'compliance' => 5 ],

	'review_timeline_status' => [ 
		1 => 'Accept All Observations',
		2 => 'Reject All Observations',
	],

	'audit_review_action' => [ 2 => 'ACCEPTED', 3 => 'RE ASSESMENT NEEDED' ],

	'compliance_review_action' =>  [ 
		2 => 'ACCEPTED', 
		3 => 'RE COMPLIANCE NEEDED', 
		// 4 => 'ON HOLD', 
		// 5 => 'CARRY FORWARD' 
	],
]);

defined( 'CARRY_FORWARD_ARRAY' ) ?: define('CARRY_FORWARD_ARRAY', [
	'id' => 'CF',
	'title' => 'CARRY FORWARD POINTS'
]);

defined( 'ASSESMENT_TIMELINE_ARRAY' ) ?: define('ASSESMENT_TIMELINE_ARRAY', [

	// for audit
	1 => ['status_id' => 1, 'title' => 'AUDIT (PENDING / ACTIVE)'],
	2 => ['status_id' => 2, 'title' => 'REVIEW (PENDING / ACTIVE)'],
	3 => ['status_id' => 3, 'title' => 'RE AUDIT (PENDING / ACTIVE)'],

	// for compliance
	4 => ['status_id' => 4, 'title' => 'COMPLIANCE (PENDING / ACTIVE)'],
	15 => ['status_id' => 15, 'title' => 'RO OFFICER REVIEW (PENDING / ACTIVE)'],
	5 => ['status_id' => 5, 'title' => 'REVIEW (PENDING / ACTIVE)'],
	6 => ['status_id' => 6, 'title' => 'RE COMPLIANCE (PENDING / ACTIVE)'],

	// for reviewer extra
	7 => ['status_id' => 7, 'title' => 'ASSESMENT COMPLETED'],
	8 => ['status_id' => 8, 'title' => 'REVIEWER TO AUDIT (All OBSERVATIONS)'],
	9 => ['status_id' => 9, 'title' => 'REVIEWER TO COMPLIANCE (All OBSERVATIONS)'],
	10 => ['status_id' => 10, 'title' => 'ADMIN INCREASE ACCEPT / REJECT LIMIT IN AUDIT'],
	11 => ['status_id' => 11, 'title' => 'ADMIN INCREASE ACCEPT / REJECT LIMIT IN COMPLIANCE'],
	12 => ['status_id' => 12, 'title' => 'ADMIN INCREASE DUE DATE IN AUDIT'],
	13 => ['status_id' => 13, 'title' => 'ADMIN INCREASE DUE DATE IN COMPLIANCE'],
	14 => ['status_id' => 14, 'title' => 'REVIEWER TO AUDIT (ENTIRE ASSESMENT BACK TO AUDIT)'],
]);

defined( 'RISK_PARAMETERS_ARRAY' ) ?: define('RISK_PARAMETERS_ARRAY', [
	1 => ['id' => 1, 'title' => 'HIGH RISK'],
	2 => ['id' => 2, 'title' => 'MEIDUM RISK'],
	3 => ['id' => 3, 'title' => 'LOW RISK'],
	4 => ['id' => 4, 'title' => 'NO RISK'],
]);

$applicableToArray  = array(
	1 => ['id' => 1, 'title' => 'GENERAL'],
	2 => ['id' => 2, 'title' => 'INDIVIDUAL'],
	3 => ['id' => 3, 'title' => 'NON-INDIVIDUAL'],
	4 => ['id' => 4, 'title' => 'INDIVIDUAL / NON-INDIVIDUAL'],
);

$questionInputMethodArray  = array(
	1 => ['id' => 1, 'title' => 'MULTIPLE - OPTION SELECT'],
	2 => ['id' => 2, 'title' => 'YES / NO TYPE - OPTION SELECT'],
	3 => ['id' => 3, 'title' => 'GENERAL QUESTION - ONLY TEXTAREA'],
	4 => ['id' => 4, 'title' => 'ANNEXURE'],
	5 => ['id' => 5, 'title' => 'SUBSET'],
);

$questionTypeArray  = array(
	1 => 'QUALITATIVE',
	2 => 'QUANTITATIVE',
);

$schemeTypesArray = array(
	2 => 'ADVANCES',
	1 => 'DEPOSITS',
);

$setTypesArray = array(
	1 => 'MAINSET',
	2 => 'SUBSET',
);

$auditFrequencyArray = array(
	1 => '1 Month Frequency',
	3 => '3 Months Frequency',
	6 => '6 Months Frequency',
	12 => '12 Months Frequency',
);

$userGenderArray = array(
	'mr'	=> 'mr-profile.jpg',
	'ms'	=> 'mrs-profile.jpg',
	'mrs'	=> 'mrs-profile.jpg',
);

defined( 'BRANCH_FINANCIAL_POSITION' ) ?: define('BRANCH_FINANCIAL_POSITION', [

	'deposits' => array(
		'1' => 'CASA Deposit',
		'3' => 'Term Deposit',
	),

	'advances' => array(
		'4' => '(Advances) Clean Loan', 
		'5' => '(Advances) Vehicle Loan', 
		'6' => '(Advances) Gold Loan', 
		'7' => '(Advances) Other Term Loan', 
		'8' => '(Advances) Cash Credit Loan', 
		'9' => '(Advances) Decreed Loan',
	),
	
	'npa' => array(
		'10'  => '(NPA) Clean Loan',
		'11' => '(NPA) Vehicle Loan',
		'12' => '(NPA) Gold Loan',
		'13' => '(NPA) Other Term Loan',
		'14' => '(NPA) Cash Credit Loan',
		'15' => '(NPA) Decreed Loan',
	)
]);

defined( 'BRANCH_FRESH_ACCOUNTS' ) ?: define('BRANCH_FRESH_ACCOUNTS', [

	'deposits' => array(
		'1'  => 'CASA Deposit', 
		'2'  => 'Term Deposit (NEW)',
		'3'  => 'Term Deposits (Through Auto-Renewals)',
	),

	'advances' => array(
		'4'  => 'Clean Loan',
		'5'  => 'Vehicle Loan',
		'6'  => 'Gold Loan',
		'7'  => 'Loan Against Fixed Deposits',
		'8'  => 'Other Term Loan', 
		'9'  => 'Cash Credit Loans (New)', 
		'10' => 'Cash Credit Loans (Renewals)',
	),
	
	'npa' => array(
		'11' => 'Clean Loan',
		'12' => 'Vehicle Loan',
		'13' => 'Gold Loan',
		'14' => 'Other Term Loan',
		'15' => 'Cash Credit Loan',
		'16' => 'Decreed Accounts',
	)
]);

$columnTypeArray = array(
	'1' => 'TextBox',
	'2' => 'TextArea',
	'3' => 'Dropdown',
);

$remarkTypesArray = array(
	1 => 'Remark for Auditor',
	2 => 'Remark for Reviewer',
	3 => 'Remark for Compliance',
	4 => 'Remark for Reviewer & Compliance',
	5 => 'Remark for Auditor & Compliance'
);

defined( 'AS_PER_ANNEXURE' ) ?: define('AS_PER_ANNEXURE', 'AS PER ANNEXURE');

// Allowed extensions
defined( 'ERROR_VARS' ) ?: define('ERROR_VARS', array( 
	'notFound' => 'Not Found',
	'notFoundSpan' => '<span class="text-secondary font-sm">Not Found</span>',
	'notAvailable' => 'Not Available',
	'notAvailableSpan' => '<span class="text-secondary font-sm">Not Available</span>',
	'notApplicable' => 'NOT APPLICABLE',
));

defined( 'FILE_UPLOADS_TYPES' ) ?: define('FILE_UPLOADS_TYPES', array(
	'csv' => [ 1 => 'text/csv' ],
	'csv_size' => '5', // 5MB
	'image' => [ 1 => 'image/jpeg', 2 => 'image/jpg', 3 => 'image/png' ],
	'image_size' => '5', // 5MB
	'pdf' => [ 1 => 'application/pdf' ],
	'pdf_size' => '5', // 5MB
));

// Evidence Upload
defined( 'EVIDENCE_UPLOAD' ) ?: define('EVIDENCE_UPLOAD', array( 
	'file_types' => [1 => 'image/jpeg', 2 => 'image/jpg', 3 => 'image/png', 4 => 'application/pdf'],
	'size' => '5', // 5MB
	'multi' => false,
	'assets' => URL . 'evidence/',
	'controller' => APP_ROOT . '/Evidence/evidence-controller.php',
	'control_url' => URL . 'auditpro-evidences/',
	'upload_dir' => '/var/www/ndvs.2526/auditpro-evidences-docs/',
	'upload_url' => HOST . '/auditpro-evidences-docs/',
	'upload_folder_create' => 'evi_audit_',
	'checkbox_text' => 'Evidence to be uploaded',
	'database' => array(
		'db_host' => 'localhost:3306',
		'db_user' => 'root',
		'db_pass' => '',  
		'db_name' => 'evidence_db'
	)
));

// COMPLIANCE PRO 13.09.2024
defined( 'COMPLIANCE_PRO_ARRAY' ) ?: define('COMPLIANCE_PRO_ARRAY', array(

	'file_types' => [ 1 => 'image/jpeg', 2 => 'image/jpg', 3 => 'image/png', 4 => 'application/pdf' ],
	'size' => '5', // 5MB
	'multi' => true,
	//'controller' => APP_ROOT . '/Controllers/CompliancePro/',

	'compliance_categories' => array(
		'1' => 'Regulatory & Statutory Compliance',
		'2' => 'Supervisory Compliance',
		'3' => 'Compliances to Advisories',
		'4' => 'Compliances to Custom Requirements',
		'5' => 'Compliances to Policy Guidelines, SOPs',
		'6' => 'General Compliances'
	),
 
	'review_compliance_status' => array(
		1 => 'PASSED',
		2 => 'RE COMPLIANCE',
		// 3 => 'PARTIALLY PASSED',
		// 4 => 'IN REVIEW',
	),

	'review_timeline_status' => [ 
		1 => 'PASSED ALL OBSERVATIONS',
		2 => 'FAILED ALL OBSERVATIONS',
		3 => 'PARTIALLY PASSED ALL OBSERVATIONS',
	],

	'timeline_compliance_status' => array(
		// for compliance
		1 => ['status_id' => 1, 'title' => 'COMPLIANCE (PENDING / ACTIVE)'],
		2 => ['status_id' => 2, 'title' => 'REVIEW (PENDING / ACTIVE)'],
		3 => ['status_id' => 3, 'title' => 'RE COMPLIANCE (PENDING / ACTIVE)'],

		// for reviewer extra
		4 => ['status_id' => 4, 'title' => 'COMPLIANCE COMPLETED'],
		// 8 => ['status_id' => 8, 'title' => 'REVIEWER TO AUDIT (All OBSERVATIONS)'],
		// 9 => ['status_id' => 9, 'title' => 'REVIEWER TO COMPLIANCE (All OBSERVATIONS)'],
		// 10 => ['status_id' => 10, 'title' => 'ADMIN INCREASE ACCEPT / REJECT LIMIT IN AUDIT'],
		// 11 => ['status_id' => 11, 'title' => 'ADMIN INCREASE ACCEPT / REJECT LIMIT IN COMPLIANCE'],
		// 12 => ['status_id' => 12, 'title' => 'ADMIN INCREASE DUE DATE IN AUDIT'],
		// 13 => ['status_id' => 13, 'title' => 'ADMIN INCREASE DUE DATE IN COMPLIANCE'],
		// 14 => ['status_id' => 14, 'title' => 'REVIEWER TO AUDIT (ENTIRE ASSESMENT BACK TO AUDIT)'],
	),

	'compliance_priority' => array(
		'1' => 'CRITICAL',
		'2' => 'HIGH',
		'3' => 'MEDIUM',
		'4' => 'LOW',
	),

	'compliance_frequency' => array(
		'1' => [ 'title' => 'FORTNIGHT - Every 15 Days', 'freq' => 15 ],
		'2' => [ 'title' => 'MONTHLY - Every Month', 'freq' => 1 ],
		'3' => [ 'title' => 'QUARTERLY - Every Three Months', 'freq' => 3 ],
		'4' => [ 'title' => 'SEMIANNUALLY - Every Six Months', 'freq' => 6 ],
		'5' => [ 'title' => 'YEARLY - Every Year', 'freq' => 12],
		'6' => [ 'title' => '1 Time Use', 'freq' => 16 ], // due to 1 means single month
	),

	'compliance_due_array' => [
		1 => 15, //for compliance
		2 => 15, //for compliance review
	],

'compliance_docs_array' => [
		'file_types' => [ 
			1 => 'image/jpeg', 2 => 'image/jpg', 3 => 'image/png', 4 => 'application/pdf', 
			5 => 'text/csv', 6 => 'application/vnd.ms-excel', 7 => 'application/vnd.openxmlformats-officedocument.spreadsheetml.sheet'
		],
		'size' => '5', // 5MB
		'multi_limit' => 2,
		'assets' => URL . 'compliance-pro-assets/',
		'controller' => APP_ROOT . '/Evidence/evidence-controller.php',
		'control_url' => URL . 'compliance-pro-docs-upload/',
		'upload_dir' => '/var/www/ndvs-2526/compliance-circular-docs/',
		'upload_url' => HOST . '/compliance-circular-docs/',
		'com_circular_dir_name' => 'doc_circular_',
		'com_asses_dir_name' => 'doc_com_',
	]
));


// Register Autoloader
function laoderFunc( $class_name ){
	// echo '<br />' . $class_name . '<br />';
	$parts = explode( '\\', $class_name );
	
	array_walk( $parts, function( $v ){
		return ucfirst( $v );
	} );
	
	$path = APP_ROOT . DS . join( DS, $parts ) . '.php';

	if( file_exists( $path ) )
		require( $path );
};

spl_autoload_register( 'laoderFunc' );

// require helper functions
require_once APP_CORE . DS . 'HelperFunctions.php';

// require compliance helper functions
require_once APP_CORE . DS . 'HelperFunctionsCompliancePro.php';

// connect to db
Core\DBConnection::openConnection();

?>