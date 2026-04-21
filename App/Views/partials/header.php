<?php

// function for generate menu and submenu
function generate_menu_submenu($menuSubmenuArray, $me = null)
{
  $cMenuIdKey = null;
          
  if(is_object($me) && isset($me -> menuKey))
    $cMenuIdKey = $me -> menuKey;

  foreach($menuSubmenuArray as $cMenu)
  {
    if(!array_key_exists('subMenu', $cMenu))
      echo '<li'. (($cMenuIdKey == $cMenu['id']) ? ' class="active"' : '') .'><a href="'. $cMenu['url'] .'">'. $cMenu['title'] .'</a></li>' . "\n";
    else
    {
      $isActive = false;

      $subMenu = '<ul class="">' . "\n";
        foreach($cMenu['subMenu'] as $cSubmenu)
        {
          //every time checked
          if(!$isActive && ($cMenuIdKey == $cSubmenu['id']))
            $isActive = true;

          $subMenu .= '<li'. (($cMenuIdKey == $cSubmenu['id']) ? ' class="active"' : '') .'><a href="'. $cSubmenu['url'] .'">'. $cSubmenu['title'] .'</a></li>' . "\n";
        }
      $subMenu .= '</ul>' . "\n";

      echo '<li class="sub-menu'. (($isActive) ? ' active' : '') .'">
        <a href="javascript:void(0)">'. $cMenu['title'] .'</a>';
        echo $subMenu;
      echo '</li>' . "\n";
    }
  }
}
if( in_array( $data['userDetails']['emp_type'], [10] ))
  $dashboardArray = [ 'id' => 'dashboard', 'title' => 'Dashboard', 'url' => $data['siteUrls']::getUrl('dashboard') . '/select-audit-unit'];
//support user
elseif ($data['userDetails']['emp_type'] == 10) {
    $dashboardArray = [
        'id' => 'supportDashboard',
        'title' => 'Dashboard',
        'url' => $data['siteUrls']::getUrl('  Dashboard')
    ];
}

else
  $dashboardArray = [ 'id' => 'dashboard', 'title' => 'Dashboard', 'url' => $data['siteUrls']::getUrl('dashboard') ];
if( in_array( $data['userDetails']['emp_type'], [2,4,16] ))
  $dashboardArray = [ 'id' => 'dashboard', 'title' => 'Dashboard', 'url' => $data['siteUrls']::getUrl('dashboard') . '/select-audit-unit'];


else
  $dashboardArray = [ 'id' => 'dashboard', 'title' => 'Dashboard', 'url' => $data['siteUrls']::getUrl('dashboard') ];

$defaultArray = [
  'id' => null, 
  'title' => 'Settings', 
  'subMenu' => [
    [ 'id' => 'updateProfile', 'title' => 'Profile Settings', 'url' => $data['siteUrls']::getUrl('updateProfile') ],
    [ 'id' => 'logout', 'title' => 'Logout', 'url' => $data['siteUrls']::getUrl('logout') ],
  ],
];

$menuArray = [];

// generate array for menu
if(isset($data['userDetails']['emp_type'])):

  if( in_array($data['userDetails']['emp_type'], [1]) )
  {
    // FOR ADMIN
    $menuArray = [
      /*[ 'id' => 'yearMaster', 'title' => 'Year Master', 'url' => $data['siteUrls']::getUrl('yearMaster') ],*/
      [
        'id' => 'employeeMaster', 
        'title' => 'Employee Master', 
        'subMenu' => [
          [ 'id' => 'employeeMaster', 'title' => 'Manage Employees', 'url' => $data['siteUrls']::getUrl('employeeMaster') ],
          [ 'id' => 'setPassword', 'title' => 'Set Password', 'url' => $data['siteUrls']::getUrl('employeeMaster') . "/password"],
          [ 'id' => 'passwordPolicyAdmin', 'title' => 'Password Policy', 'url' => $data['siteUrls']::getUrl('passwordPolicyAdmin') . "/update"],
        ]
      ],

      [ 'id' => 'auditSectionMaster', 'title' => 'Audit Section Master', 'url' => $data['siteUrls']::getUrl('auditSectionMaster') ],
      
      [
        'id' => 'auditUnitMaster', 
        'title' => 'Manage Audit Units', 
        'subMenu' => [
          [ 'id' => 'auditUnitMaster', 'title' => 'Manage Branches / HO Departments', 'url' => $data['siteUrls']::getUrl('auditUnitMaster') ],
          [ 'id' => 'auditUnitMasterFrequency', 'title' => 'Audit Frequency', 'url' => $data['siteUrls']::getUrl('auditUnitMaster') . "/frequency" ],
          [ 'id' => 'lastMarchPosition', 'title' => 'Last March Position', 'url' => $data['siteUrls']::getUrl('auditUnitMaster') . "/last-march-position" ],
        ]
      ],

      [
        'id' => null, 
        'title' => 'Risk Masters', 
        'subMenu' => [
          [ 'id' => 'riskMatrix', 'title' => 'Risk Matrix Parameters', 'url' => $data['siteUrls']::getUrl('riskMatrix') ],
          [ 'id' => 'riskCategoryMaster', 'title' => 'Risk Category Master', 'url' => $data['siteUrls']::getUrl('riskCategoryMaster') ],
          [ 'id' => 'riskComposite', 'title' => 'Composite Risk', 'url' => $data['siteUrls']::getUrl('riskComposite') ],
          [ 'id' => 'riskControlMaster', 'title' => 'Risk Control Master', 'url' => $data['siteUrls']::getUrl('riskControlMaster') ],
          [ 'id' => 'branchRatingMaster', 'title' => 'Branch Rating Master', 'url' => $data['siteUrls']::getUrl('branchRatingMaster') ],
        ]
      ],

      [ 'id' => 'broaderAreaMaster', 'title' => 'Broader Area of Audit', 'url' => $data['siteUrls']::getUrl('broaderAreaMaster') ],

      [
        'id' =>'schemeMaster', 
        'title' => 'Scheme Master',
        'url' => $data['siteUrls']::getUrl('schemeMaster'),
      ],

      [ 'id' => 'annexureMaster', 'title' => 'Annexure Master', 'url' => $data['siteUrls']::getUrl('annexureMaster')],
      [ 'id' => 'menuMaster', 'title' => 'Menu Master', 'url' => $data['siteUrls']::getUrl('menuMaster') ],
      [ 'id' => 'categoryMaster', 'title' => 'Category Master', 'url' => $data['siteUrls']::getUrl('categoryMaster') ],

      [
        'id' => null, 
        'title' => 'Question Master', 
        'subMenu' => [
          [ 'id' => 'questionSetMaster', 'title' => 'Question Set Master', 'url' => $data['siteUrls']::getUrl('questionSetMaster') ],
          [ 'id' => 'questionDownload', 'title' => 'Question Master Download', 'url' => $data['siteUrls']::getUrl('questionDownload') ],
          [ 'id' => 'questionDownloadBroaderArea', 'title' => 'Question Master Download (Broader Area Wise)', 'url' => $data['siteUrls']::getUrl('questionDownloadBroaderArea') ],
        ]
      ],

      [ 'id' => 'multiLevelControlMaster', 'title' => 'Periodwise Questions', 'url' => $data['siteUrls']::getUrl('multiLevelControlMaster') ],
      [
        'id' => null, 
        'title' => 'Manage Assesments', 
        'subMenu' => [
          [ 'id' => 'manageAssesment', 'title' => 'Manage Assesments', 'url' => $data['siteUrls']::getUrl('manageAssesment') ],
          [ 'id' => 'dateChange', 'title' => 'Date Change', 'url' => $data['siteUrls']::getUrl('dateChange') ],

        ]
      ],
      [
        'id' => null, 
        'title' => 'Manage Accounts Data', 
        'subMenu' => [
          [ 'id' => 'manageAccountsAdvances', 'title' => 'Manage Accounts (Advances)', 'url' => $data['siteUrls']::getUrl('manageAccountsAdvances') ],
          [ 'id' => 'bulkUploadAdvance', 'title' => 'Bulk Upoload Advances', 'url' => $data['siteUrls']::getUrl('bulkUploadAdvance') ],
          [ 'id' => 'manageAccountsDeposits', 'title' => 'Manage Accounts (Deposits)', 'url' => $data['siteUrls']::getUrl('manageAccountsDeposits') ],
          [ 'id' => 'bulkUploadDeposit', 'title' => 'Bulk Upoload Deposits', 'url' => $data['siteUrls']::getUrl('bulkUploadDeposit') ],
        ]
      ],
    ];
  }
  else if( in_array($data['userDetails']['emp_type'], [9]) )
  {
    // FOR ADMIN LITE
    $menuArray = [
      /*[ 'id' => 'yearMaster', 'title' => 'Year Master', 'url' => $data['siteUrls']::getUrl('yearMaster') ],*/
      [
        'id' => 'employeeMaster', 
        'title' => 'Employee Master', 
        'subMenu' => [
          [ 'id' => 'employeeMaster', 'title' => 'Manage Employees', 'url' => $data['siteUrls']::getUrl('employeeMaster') ],
          [ 'id' => 'setPassword', 'title' => 'Set Password', 'url' => $data['siteUrls']::getUrl('employeeMaster') . "/password"]
        ]
      ],
      
      [
        'id' => 'auditUnitMaster', 
        'title' => 'Manage Audit Units', 
        'subMenu' => [
          [ 'id' => 'auditUnitMaster', 'title' => 'Manage Branches / HO Departments', 'url' => $data['siteUrls']::getUrl('auditUnitMaster') ],
          [ 'id' => 'auditUnitMasterFrequency', 'title' => 'Audit Frequency', 'url' => $data['siteUrls']::getUrl('auditUnitMaster') . "/frequency" ],
        ]
      ],

      [
        'id' => null, 
        'title' => 'Risk Masters', 
        'subMenu' => [
          [ 'id' => 'branchRatingMaster', 'title' => 'Branch Rating Master', 'url' => $data['siteUrls']::getUrl('branchRatingMaster') ],
        ]
      ],

      [ 'id' => 'broaderAreaMaster', 'title' => 'Broader Area of Audit', 'url' => $data['siteUrls']::getUrl('broaderAreaMaster') ],

      [
        'id' => null, 
        'title' => 'Question Master', 
        'subMenu' => [
          [ 'id' => 'questionSetMaster', 'title' => 'Question Set Master', 'url' => $data['siteUrls']::getUrl('questionSetMaster') ],
          [ 'id' => 'questionDownload', 'title' => 'Question Master Download', 'url' => $data['siteUrls']::getUrl('questionDownload') ],
          [ 'id' => 'questionDownloadBroaderArea', 'title' => 'Question Master Download (Broader Area Wise)', 'url' => $data['siteUrls']::getUrl('questionDownloadBroaderArea') ],
        ]
      ],

      [ 'id' => 'multiLevelControlMaster', 'title' => 'Periodwise Questions', 'url' => $data['siteUrls']::getUrl('multiLevelControlMaster') ],

      [ 'id' => 'manageAssesment', 'title' => 'Manage Assesments', 'url' => $data['siteUrls']::getUrl('manageAssesment') ],

      [
        'id' => null, 
        'title' => 'Manage Accounts Data', 
        'subMenu' => [
          [ 'id' => 'manageAccountsAdvances', 'title' => 'Manage Accounts (Advances)', 'url' => $data['siteUrls']::getUrl('manageAccountsAdvances') ],
          [ 'id' => 'bulkUploadAdvance', 'title' => 'Bulk Upoload Advances', 'url' => $data['siteUrls']::getUrl('bulkUploadAdvance') ],
          [ 'id' => 'manageAccountsDeposits', 'title' => 'Manage Accounts (Deposits)', 'url' => $data['siteUrls']::getUrl('manageAccountsDeposits') ],
          [ 'id' => 'bulkUploadDeposit', 'title' => 'Bulk Upoload Deposits', 'url' => $data['siteUrls']::getUrl('bulkUploadDeposit') ],
        ]
      ]

    ];
  }
  else if( in_array($data['userDetails']['emp_type'], [6,7]) )
  {
    // FOR COMPLIANCE PRO 16.09.2024
    if($data['userDetails']['emp_type'] == 6)
      $menuArray[] = [ 
        'id' => null, 
        'title' => 'Setups', 
        'subMenu' => [
          [ 'id' => 'complianceCircularAuthority', 'title' => 'Authority', 'url' => $data['siteUrls']::getUrl('complianceCircularAuthority') ],
          [ 'id' => 'complianceCircularSetMaster', 'title' => 'Circular Master', 'url' => $data['siteUrls']::getUrl('complianceCircularSetMaster') ],
          [ 'id' => 'complianceCircularHeaderMaster', 'title' => 'Task Header Master', 'url' => $data['siteUrls']::getUrl('complianceCircularHeaderMaster') ],
          [ 'id' => 'complianceCircularTaskMaster', 'title' => 'Task Master', 'url' => $data['siteUrls']::getUrl('complianceCircularTaskMaster') ],
          [ 'id' => 'complianceCircularBulkUploadTasks', 'title' => 'Bulk Upload Tasks', 'url' => $data['siteUrls']::getUrl('complianceCircularTaskMaster') . '/bulk-upload-circular-task' ],
          [ 'id' => 'complianceCircularTaskSet', 'title' => 'Task Set Master', 'url' => $data['siteUrls']::getUrl('complianceCircularTaskSet') ],
        ]         
      ];

    // add other options
    $menuArray[] = [ 'id' => 'complianceCircularAssesData', 'title' => 'Circulars', 'url' => $data['siteUrls']::getUrl('complianceCircularAssesData') ];
    $menuArray[] = [ 'id' => 'complianceProReports', 'title' => 'Reports', 'url' => $data['siteUrls']::getUrl('complianceProReports') ];
  }
  else if( in_array($data['userDetails']['emp_type'], [2]) )
  {
    // FOR AUDITOR
    $menuArray = [];

    if(isset($data['menu_data']) && sizeof($data['menu_data']) > 0) 
    {
      $cMenuIndex = 0;
      foreach($data['menu_data'] as $cMenuId => $cMenuDetails)
      {
        $menuArray[ $cMenuIndex ] = [
          'id' => 'menu_' . $cMenuId, 
          'title' => string_operations($cMenuDetails -> name, 'upper'), 
        ];
        
        $menuArray[ $cMenuIndex ]['url'] = $data['siteUrls']::getUrl('executiveSummary');
        
        if(!isset($cMenuDetails -> categories))
        {
          if($cMenuId == 1) // for executive summary
            $menuArray[ $cMenuIndex ]['url'] = $data['siteUrls']::getUrl('executiveSummary') . '/audit';
          else
            $menuArray[ $cMenuIndex ]['url'] = $data['siteUrls']::getUrl('auditMenu') . $cMenuDetails -> decrypt_menu_id;
        }
        
        if( isset($cMenuDetails -> categories) && sizeof($cMenuDetails -> categories) > 0 )
        {
          $menuArray[ $cMenuIndex ]['subMenu'] = [];

          foreach($cMenuDetails -> categories as $cCatId => $cCatDetails)
          {
            $menuArray[ $cMenuIndex ]['subMenu'][] = [ 
              'id' => 'cat_' . $cCatId, 
              'title' => string_operations($cCatDetails -> name, 'upper'), 
              'url' => $data['siteUrls']::getUrl('auditCategory') . $cCatDetails -> decrypt_cat_id
            ];
          }
        }
        $cMenuIndex++;
        
      }
      
    }
    
  }
  elseif( in_array($data['userDetails']['emp_type'], [3,4,16]) && 
          array_key_exists('data', $data) && 
          is_array($data['data']) && 
          array_key_exists('assesmentData', $data['data']) && 
          is_object($data['data']['assesmentData']) )
  {
    $menuArray = [];

    if(!empty($data['data']['assesmentData'] -> menu_ids) && ENV_CONFIG['executive_summary_review'])
    {
      $tmpMenuIds = explode(',', $data['data']['assesmentData'] -> menu_ids);

      if(is_array($tmpMenuIds) && in_array('1', $tmpMenuIds))
        $menuArray[] = [ 
          'id' => 'executiveSummary', 
          'title' => 'Executive Summary', 
          'url' => $data['siteUrls']::getUrl('executiveSummary') . '/' . (($data['userDetails']['emp_type'] == '3') ? 'compliance' : (($data['data']['assesmentData'] -> audit_status_id == 5 ) ? 'review-compliance' : 'review-audit')) ];
    }

    if($data['userDetails']['emp_type'] == '3')
    {
      // for compliance
      if($data['data']['assesmentData'] -> audit_status_id == 6)
        $menuArray[] = [ 
          'id' => 'auditReCompliance', 
          'title' => 'Audit Re-Compliance', 
          'url' => $data['siteUrls']::getUrl('compliance') . '/re-compliance'
        ];
      else
        $menuArray[] = [ 
          'id' => 'auditCompliance', 
          'title' => 'Audit Compliance', 
          'url' => $data['siteUrls']::getUrl('compliance')
        ];
    }
    else
    {
      $reviewUrl = 'audit';
      // reviewer
      if( $data['data']['assesmentData'] -> audit_status_id == 5 || $data['data']['assesmentData'] -> audit_status_id == 15 )
      {
        $menuArray[] = [ 
          'id' => 'auditCompliance', 
          'title' => 'Audit Compliance', 
          'url' => $data['siteUrls']::getUrl('reviewer') . '/review-compliance' 
        ];

        $reviewUrl = 'compliance';
      }
      else
      {
        $menuArray[] = [ 
          'id' => 'auditReview', 
          'title' => 'Audit Review', 
          'url' => $data['siteUrls']::getUrl('reviewer') . '/review-audit' 
        ];
      }

      // seperate submit review
      $menuArray[] = [ 
        'id' => 'submit'. ucfirst($reviewUrl) .'Review', 
        'title' => 'Submit '. ucfirst($reviewUrl) .' Review', 
        'url' => $data['siteUrls']::getUrl('reviewer') . '/submit-' . $reviewUrl . '-review' 
      ];
    }
  }
  //support User - dashboard only
  if(in_array($data['userDetails']['emp_type'], [10]))
  {
    // for support menu
    $menuArray[] = [ 
      'id' => 'dateChange', 
      'title' => 'Date Change', 
      'url' => $data['siteUrls']::getUrl('dateChange') 
    ];
  }
//   //super admin - dashboard only
//   elseif(in_array($data['userDetails']['emp_type'], [11,3,2,4,1,9]))
// {
//     // for super admin menu - Calendar
//     $menuArray[] = [ 
//         'id' => 'superAdminDashboard', 
//         'title' => 'Calendar',  // Changed from "Calender" to "Calendar"
//         'url' => $data['siteUrls']::getUrl('superAdminDashboard') 
//     ];
//      $menuArray[] = [
//         'id' => 'reports', 
//         'title' => 'Reports', 
//         'url' => $data['siteUrls']::getUrl('reports')
//       ];
// }
endif;

?>

<span id="menu_ham"></span>  

<!--header start here-->
<div id="header">

  <a class="sidebar_logo" href="#">
    <img src="<?= ASSETS_IMG; ?>auditpro-logo.png" alt="AuditPro Logo" />
  </a>

  <div id="bank_name_container">
    <h4>Bank: <?= BANK_NAME ?></h4>
  </div>  

  <div class="container-fluid">
    
    <div id="header-right-container">

      <?php if(check_audit_remark_active_popup($data)): ?>
      <div id="assesment_remark" data-bs-toggle="modal" data-bs-target="#audit_remark_container" style="background-image: url(<?= ASSETS_IMG; ?>notification.svg)"></div>
      <?php endif; ?>

      <div id="profile_info">
        <span class="profile_img" style="background-image:url(<?= $data['userDetails']['emp_profile']; ?>)"></span>
        <h4 class="emp_name"><?= (ucwords($data['userDetails']['emp_gender'] . '. ') ?? '') . ucwords($data['userDetails']['emp_name']); ?></h4>
        <p class="emp_designation">Designation: <?= $data['userDetails']['emp_design']; ?></p>
      </div>

      <ul id="profile_info_container" class="shadow-3">
      <?php if($data['userDetails']['emp_type'] == 2 || $data['userDetails']['emp_type'] == 4 || $data['userDetails']['emp_type'] == 16): ?>
        <li><a href="<?= $data['siteUrls']::getUrl('dashboard'). '/select-audit-unit';?>">Dashboard</a></li>
      <?php else:?>
        <li><a href="<?= $data['siteUrls']::getUrl('dashboard') ?>">Dashboard</a></li>
      <?php endif;?>
        <li><a href="<?= $data['siteUrls']::getUrl('updateProfile') ?>">Profile Settings</a></li>
        <?php if( $data['me'] -> id != 'auditEndAssessment' && $data['userDetails']['emp_type'] == 2 && isset($_SESSION['audit_id']) && (isset($data['db_assesment']) || isset($data['data']['db_assesment_data']))): ?> 
        <li><a href="<?= $data['siteUrls']::getUrl('audit') . ( check_re_assesment_status( isset($data['db_assesment']) ? $data['db_assesment'] : $data['data']['db_assesment_data'] ) ? '/end-reassesment' : '/end-assesment' ) ?>">End Audit Assesment</a></li> 
        <?php endif; ?>
        <li><a href="<?= $data['siteUrls']::getUrl('logout') ?>">Logout</a></li>
      </ul>
    </div>

    <div class="w-100 clearfix"></div>
  </div>
</div>
<div class="w-100 clearfix"></div>

<!-- sidebar starts here -->
<div id="sidebar" class="active">

  <a class="sidebar_logo" href="#">
    <img src="<?= ASSETS_IMG; ?>auditpro-logo.png" alt="AuditPro Logo" />
  </a>
  
  <ul id="menu">

    <?php 

    //push array
    if(sizeof($menuArray) > 0)
    {
      if(isset($data['userDetails']['emp_type']) && in_array($data['userDetails']['emp_type'], [1,6,7]))

        if( in_array($data['userDetails']['emp_type'], [6,7]) ) $dashboardArray['id'] = 'complianceProDashboard';

        $tempMenuArray[] = $dashboardArray;

      foreach($menuArray as $cMenu)
        $tempMenuArray[] = $cMenu;

      if(isset($data['userDetails']['emp_type']) && in_array($data['userDetails']['emp_type'], [1]))
        $tempMenuArray[] = $defaultArray;

      //reassign
      $menuArray = $tempMenuArray;
      unset($tempMenuArray); //unset var
    }
    elseif(in_array($data['userDetails']['emp_type'], [2,3,4,16]))
    {
      $dashboardArray['title'] = 'Internal Audit';

      if(in_array($data['userDetails']['emp_type'], [3,4,16]))
        $dashboardArray['title'] .= ' Compliance';
      
      $menuArray = array($dashboardArray);

      // Compliance Pro 19.09.2024 Kunal Update
      if(in_array($data['userDetails']['emp_type'], [3]))
        $menuArray[] = [
          'id' => 'complianceProDashboard', 
          'title' => 'Other Compliances', 
          'url' => $data['siteUrls']::getUrl('complianceProDashboard')
        ];
        if($data['userDetails']['emp_type'] == 2) { // Admin user type
  }

      $menuArray[] = [
        'id' => 'reports', 
        'title' => 'Reports', 
        'url' => $data['siteUrls']::getUrl('reports')
      ];

      $menuArray[] = $defaultArray;
    }   
     
    else
      $menuArray = array($dashboardArray, $defaultArray);
    
    generate_menu_submenu($menuArray, $data['me']); 

    ?>
    
  </ul>
</div>
<!-- sidebar ends here -->

<!-- content starts here -->
<div id="content" class="active">