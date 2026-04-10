<?php

namespace Core;

class Notifications {

    private static $notificationsArray = array(
        'invalidForm' => '<b>Error:</b> Invalid form data! Please fill required fields',
        'logincredentialsError' => '<b>Error:</b> Sorry, but the login credentials provided are not valid. Please double-check your email and password and try again.',
        'required' => 'Error: This field is required!',
        'fillRequired' => 'Error: Please fill data in empty textbox or select valid option',
        'optionMismatch' => 'Error: Question option data not found!',
        'email' => 'Error: Please enter valid email',
        'mobile' => 'Error: Please enter valid mobile',
        'emailNotFound' => 'Error: The email entered do not match.',
        'empCodeNotFound' => 'Error: The employee code entered do not match.',
        'emailBlocked' => '<b>Error</b> Your email has been blocked. Please reach out to the administrator for further assistance.',
        'empCodeBlocked' => '<b>Error</b> Your employee code has been blocked. Please reach out to the administrator for further assistance.',
        'password' => 'Error: The passwords entered do not match.',
        'old_password' => 'Error: Old password entered do not match.',
        'passwordPolicyNotFound' => 'Error: Currently no password policy added.',
        'confirmPasswordNotMatch' => 'Error: Password and confirm password entries not match.',
        'passwordPolicyNotMatched' => 'Error: Please ensure your password match to our policy requirements.',
        'passwordChangedSuccess' => '<b>Success:</b> Password changes successfully! Please login to your account.',

        //employee master
        'name' => 'Error: Please enter valid name.',
        'employeeSelect' => 'Error: Please select valid employee.',
        'gender' => 'Error: Please select valid gender.',
        'employeeCode' => 'Error: Employee code already exists! Please enter another employee code.',
        'emailDuplicate' => 'Error: Email already exists! Please enter different email address.',
        'mobileDuplicate' => 'Error: Mobile Number already exists! Please enter different mobile number.',
        'profileUpdatedSuccess' => '<b>Success:</b> Profile updated successfully! Please check your details.',
        'emp_code' => 'Error: Please enter valid employee code.',
        'user_type' => 'Error: Please select valid user type.',
        'designation' => 'Error: Please enter valid designation.',
        'employeeAddedSuccess' => '<b>Success:</b> Employee details added successfully!',
        'employeeUpdatedSuccess' => '<b>Success:</b> Employee details updated successfully!',
        'employeeDeletedSuccess' => '<b>Success:</b> Employee details deleted successfully!',
        'setPassword' => '<b>Success:</b> Password reset successfully!.',
        
        //year master
        'year' => 'Error: Please enter valid year.',
        'yearAddedSuccess' => '<b>Success:</b> Year added successfully!',
        'yearUpdatedSuccess' => '<b>Success:</b> Year updated successfully!',
        'yearDeletedSuccess' => '<b>Success:</b> Year deleted successfully!',
        'yearExist' => 'Error: Year already exists! Please select different year.',
        'yearNotExist' => 'Error: Year not exists! Please select valid year.',
        'yearDataNotFound' => 'Error: Year data not found',

        //audit section master
        'auditSectionAddedSuccess' => '<b>Success:</b> Audit section added successfully!',
        'auditSectionUpdatedSuccess' => '<b>Success:</b> Audit section updated successfully!',
        'auditSection' => 'Error: Audit section already exists! Please enter another section name.',
        'auditFrequencySavedSuccess' => '<b>Success:</b> Audit frequency saved successfully!',
        'auditSectionDeletedSuccess' => '<b>Success:</b> Audit section deleted successfully!',
        'auditSectionSelect' => 'Error: Please select valid audit section.',
        'auditSectionDupliacateSelect' => 'Error: As each department can be added one at a time.',

        //audit unit master
        'auditUnitCodeSelect' => 'Error: Please enter valid audit unit code.',
        'headSubheadMatched' => 'Error: Please select a sub head that differs from the head.',
        'auditUnit' => 'Error: Audit unit code already exists! Please enter another unit code.',
        'auditUnitNotExists' => 'Error: Audit unit not exists! Please select valid audit unit',
        'auditUnitAddedSuccess' => '<b>Success:</b> Audit unit added successfully!',
        'auditUnitUpdatedSuccess' => '<b>Success:</b> Audit unit updated successfully!',
        'auditUnitDeletedSuccess' => '<b>Success:</b> Audit unit deleted successfully!',
        'auditAuthoritySavedSuccess' => '<b>Success:</b> Employee audit authority saved successfully!',
        'auditAuthorityNotFound' => '<b>Error:</b> No assigned branch or Head Office department at the moment. Kindly contact the admin department for assistance.',
        'auditAuthorityError' => 'Error: Please select valid audit units.',
        'employeeDataError' => 'Error: Please select only 5 Employee.',
        'frequency' => 'Error: Please select frequency',
        'audit_id' => 'Error: Please select valid audit unit',
        'noAuditUnitsError' => 'Error: No data found! Please add branch / head of departments',

        //Scheme Master
        'schemeType' => 'Error: Please select valid scheme type.',
        'schemeCode' => 'Error: Please enter valid scheme code.',
        'schemeError' => 'Error: Please select valid scheme.',
        'schemeCodeExists' => 'Error: Scheme code already exists! Please enter another scheme code.',
        'schemeAddedSuccess' => '<b>Success:</b> Scheme added successfully!',
        'schemeUpdatedSuccess' => '<b>Success:</b> Scheme updated successfully!',
        'schemeDeletedSuccess' => '<b>Success:</b> Scheme deleted successfully!',
        'linked_table_id' => 'Error: Please select scheme type',
        'schemeMappingSavedSuccess' => '<b>Success:</b> Scheme Mapping saved successfully!',
        'schemeMappingDuplicate' => 'Error: Scheme Code already exists for this category! Please enter other scheme code.',

        //broader area
        'broaderAreaSelect' => 'Error: Please select valid area of audit',
        'broaderNumber' => 'Error: Please enter valid number.',
        'broaderAreaUpdatedSuccess' => '<b>Success:</b> Broader Area updated successfully!',
        'broaderAreaDeletedSuccess' => '<b>Success:</b> Broader Area deleted successfully!',
        'broaderAreaAddedSuccess' => '<b>Success:</b> Broader Area added successfully!',
        'broaderArea' => 'Error: Broader Area already exists! Please enter another broader area.',
        'broaderAreaNotFound' => 'Error: Broader area data not found',

        //menu master
        'menuAddedSuccess' => '<b>Success:</b> Menu added successfully!',
        'menuUpdatedSuccess' => '<b>Success:</b> Menu updated successfully!',
        'menuDeletedSuccess' => '<b>Success:</b> Menu deleted successfully!',
        'menuDuplicate' => 'Error: Menu already exists for this section! Please enter another menu.',

        //category master
        'menuSelect' => 'Error: Please select valid menu.',
        'categoryDuplicate' => 'Error: Category already exists for this menu! Please enter other category.',
        'executiveSummaryDuplicate' => 'Error: Cannot add Category to this Menu! Please select different menu.',
        'categoryAddedSuccess' => '<b>Success:</b> Category added successfully!',
        'categoryUpdatedSuccess' => '<b>Success:</b> Category updated successfully!',
        'categoryDeletedSuccess' => '<b>Success:</b> Category deleted successfully!',
        'questionMappingSavedSuccess' => '<b>Success:</b> Question Set Mapping saved successfully!',
        'categoryNoDataError' => 'Error: Category data not found.',

        //question set master
        'questionsetAddedSuccess' => '<b>Success:</b> Set added successfully!',
        'questionsetUpdatedSuccess' => '<b>Success:</b> Set updated successfully!',
        'questionsetDeletedSuccess' => '<b>Success:</b> Set deleted successfully!',

        //question header master
        'questionheaderAddedSuccess' => '<b>Success:</b> Header added successfully!',
        'questionheaderUpdatedSuccess' => '<b>Success:</b> Header updated successfully!',
        'questionheaderDeletedSuccess' => '<b>Success:</b> Header deleted successfully!',
        'headerDuplicate' => 'Error: Header already exists! Please enter other header name.',
        'headerNotExists' => 'Header not exists! Please check the answers.',

        //risk category master
        'riskCategoryError' => 'Error: Please select valid risk category',
        'riskcategoryAddedSuccess' => '<b>Success:</b> Risk Category added successfully!',
        'riskcategoryUpdatedSuccess' => '<b>Success:</b> Risk Category updated successfully!',
        'riskcategoryDeletedSuccess' => '<b>Success:</b> Risk Category deleted successfully!',
        'riskcategoryDuplicate' => 'Error: Risk Category already exists! Please try different risk category name.',
        'riskCategoryNoData' => 'Error: Risk category data not found',

        //risk weight master
        'riskweightAddedSuccess' => '<b>Success:</b> Risk Weight added successfully!',
        'riskweightUpdatedSuccess' => '<b>Success:</b> Risk Weight updated successfully!',
        'riskweightDeletedSuccess' => '<b>Success:</b> Risk Weight deleted successfully!',
        'riskCategoryWeightNoData' => 'Error: Risk category weight data not found',

        //risk control master
        'riskControlError' => 'Error: Please select valid control risk',
        'riskcontrolAddedSuccess' => '<b>Success:</b> Control Risk Type added successfully!',
        'riskcontrolUpdatedSuccess' => '<b>Success:</b> Control Risk Type updated successfully!',
        'riskcontrolDeletedSuccess' => '<b>Success:</b> Control Risk Type deleted successfully!',
        'riskcontrolDuplicate' => 'Error: Control Risk Type already exists! Please try different control risk type.',

        //risk key aspect
        'riskKeyAspectError' => 'Error: Please select valid key aspect',
        'riskkeyaspectAddedSuccess' => '<b>Success:</b> Risk Key Aspect added successfully!',
        'riskkeyaspectUpdatedSuccess' => '<b>Success:</b> Risk Key Aspect updated successfully!',
        'riskkeyaspectDeletedSuccess' => '<b>Success:</b> Risk Key Aspect deleted successfully!',
        'riskcontrolaspectkeyDuplicate' => 'Error: Control Risk Key Aspect already exists for this Control Risk Type! Please try different control risk key aspect.',

        //risk matrix
        'riskMatrixAddedSuccess' => '<b>Success:</b> Risk Matrix added successfully!',
        'riskMatrixUpdatedSuccess' => '<b>Success:</b> Risk Matrix updated successfully!',
        'risk_parameter' => 'Error: Please enter risk parameter',
        'business_risk_app' => 'Error: Please check business risk app',
        'businessRiskScore' => 'Error: Please enter valid business risk score',
        'control_risk_app' => 'Error: Please check control risk app',
        'controlRiskScore' => 'Error: Please enter valid control risk score',
        'residual_risk_app' => 'Error: Please check residual risk app',
        'businessRiskValidSelect' => 'Error: Please select valid business risk',
        'controlRiskValidSelect' => 'Error: Please select valid control risk',        
        'riskTypeValidSelect' => 'Error: Please select valid risk type risk',        
        'riskMatrixNoData' => 'Error: Risk matrix data not found',


        //residual_risk
        'residualRiskError' => 'Error: Please select valid residual risk',

        //residual_risk
        'applicableError' => 'Error: Please select valid applicable to.',

        //question master
        'noQuestionFoundError' => 'Error: Questions data not found',
        'headerSelect' => 'Error: Please select valid header',
        'questionError' => 'Error: Please enter valid question',
        'questionTypeError' => 'Error: Please select valid question type',
        'optionError' => 'Error: Please select valid anwer option',
        'questionDupliacte' => 'Error: Question already exists! Please add different question.',
        'questionNotExists' => 'Question not exists! Please check the answers.',
        'questionAddedSuccess' => '<b>Success:</b> Question added successfully!',
        'questionUpdatedSuccess' => '<b>Success:</b> Question updated successfully!',
        'questionDeletedSuccess' => '<b>Success:</b> Question deleted successfully!',
        'questionRiskMappingError' => 'Error: Question option has no risk type!',
        'quesAnsAddedSuccess' => '<b>Success:</b> Question Answer added successfully!',
        'quesAnsRemovedSuccess' => '<b>Success:</b> Question Answer removed successfully!',
        'questionYesNoOptionError' => '<b>Warning:</b> Question option has already yes & no answers!',
        'oneQuesOptionNeededError' => '<b>Warning:</b> At least one option is needed in the question',
        'quesOptionNotExistsError' => '<b>Warning:</b> Question answer not exists!',
        'duplicateQuesRiskTypeError' => 'Error: Risk type already exist! Please enter new risk type',
        'quesOptionLimitError' => 'Error: Only ' . ENV_CONFIG['question_parameter_limit'] . ' question answers to add! Please remove some answers and then add.',

        //risk composite
        'riskcompositeAddedSuccess' => '<b>Success:</b> Composite Risk added successfully!',
        'riskcompositeUpdatedSuccess' => '<b>Success:</b> Composite Risk updated successfully!',
        'riskcompositeDeletedSuccess' => '<b>Success:</b> Composite Risk deleted successfully!',
        'riskcompositeDuplicate' => 'Error: Composite Risk already exists! Please try different Composite Risk.',
        'riskSelect' => 'Error: Please select valid risk.',
        'compositeNameDuplicate' => 'Error: Composite Risk already exists! Please enter different composite risk.',
        'compositeControlRiskDuplicate' => 'Error: Control Risk already exists! Please select different control risk.',

        //annexure master
        'annexureName' => 'Error: Annexure name already exists! Please enter other annexure name.',
        'risk_category_id' => 'Error: Annexure name already exists! Please enter other annexure name.',
        'annexureAddedSuccess' => '<b>Success:</b> Annexure added successfully!',
        'annexureUpdatedSuccess' => '<b>Success:</b> Annexure updated successfully!',
        'annexureDeletedSuccess' => '<b>Success:</b> Annexure deleted successfully!',
        'annexureNotFound' => 'Error: Annexure details not found.',
        'annexureColumnsNotFound' => 'Error: Annexure columns details not found.',
        'annexureColumnsAnsMisMatched' => 'Error: Annexure columns & Answers data mismatched.',
        'columnOptions' => 'Error: Please enter valid column options.',

        //audit assesment
        'pendingAudit' => 'Error: Pending audits found! Please complete pending audits.',
        'auditAssesmentStartSuccess' => '<b>Success:</b> Audit assesment started successfully!',
        'noAssesmentFound' => 'Error: No audit assessment data found!',
        'noAssesmentAuthority' => 'Currently! You do not have the authority to audit this unit.',
        'noReviewerAuthority' => 'Currently! You do not have the authority to review this audit.',
        'noComplianceAuthority' => 'Currently! You do not have the authority to compliance this audit.',
        'noDumpSampled' => 'No accounts are currently sampled for audit. Please proceed to sample accounts.',
        'dumpSamplingSuccess' => '<b>Success:</b> Sampling dump successfully!',
        'dumpSamplingRemoveSuccess' => '<b>Success:</b> Sampling dump removed successfully!',
        'samplingAccNotFound' => 'Selected dump account not found! Please select valid dump account',
        'blankAnswers' => 'Error: Please select / enter valid answers',
        'questionOptionNotFound' => 'Error: Please select valid option / answer.',
        'answerSaveSuccess' => '<b>Success:</b> Answer saved successfully!',
        'annexAnswerSaveSuccess' => '<b>Success:</b> Annexure answer saved successfully!',
        'noPendingAuditObservations' => 'Error: No pending observations found!',
        'annexAnsNotFound' => 'Error: Please add annexure answers',
        'annexAnsDeleteSuccess' => '<b>Success:</b> Annexure answer deleted successfully!',
        'annexAnsFailedSaveSuccess' => '<b>Success:</b> Annexure answer failed to save!',
        'singleAnnexAnsNeeded' => 'Error: Single annexure answer needed! Please select Not Applicable for all annexure answers remove.',
        'dumpHasAnsError' => 'Selected dump account has already been provided with answers and cannot be removed',
        'observationAction' => 'Error: Please select valid observation action',
        'observationActionSubmitted' => '<b>Success:</b> Observation action submitted successfully!',
        'auditDueExpired' => 'Audit Period is expired! Please contact admin department for further process.',
        'complianceDueExpired' => 'Compliance Period is expired! Please contact admin department for further process.',
        'auditDueExpiredShort' => 'Audit Period is expired',
        'complianceDueExpiredShort' => 'Compliance Period is expired',
        'auditBlockedShort' => 'Audit Blocked',
        'complianceBlockedShort' => 'Compliance Blocked',
        'auditCompletedShort' => 'Audit Completed',
        'complianceCompletedShort' => 'Compliance Completed',
        'increaseLimitSelectError' => 'Error: Please select valid increase limit',
        'increaseDueDateSelectError' => 'Error: Please select valid increase due date',
        'increaseLimitSuccess' => '<b>Success:</b> Limit increased successfully!',
        'increaseDueDateSuccess' => '<b>Success:</b> Due date increased successfully!',
       
        // compliance
        'validComment' => 'Error: Please enter valid comment',
        'validCompliance' => 'Error: Please enter valid compliance',
        'validReviewComment' => 'Error: Please enter valid comment',
        'auditComplianceSuccess' => '<b>Success:</b> Compliance saved successfully!',
        'auditCommentSuccess' => '<b>Success:</b> Audit comment saved successfully!',
        'reviewCommentSuccess' => '<b>Success:</b> Reviewer comment saved successfully!',
        'allCompliaceWarning' => '<b>Warning:</b> Please complete all compliance points!',
        'reviewActionSavedSuccess' => '<b>Success:</b> Review action saved successfully!',
        'reviewFailedSaveeSuccess' => '<b>Success:</b> Failed to save review action!',
        'noCompliancePointsFound' => '<b>Note:</b> No compliance found in this audit assesment',

        // reviewer action
        'dumpMarkAsComplete' => 'Mark as completed for selected Account has completed',
        'assesmentCompletedAuditSuccess' => '<b>Success:</b> Audit assesment completed successfully!',
        'assesmentBackAuditSuccess' => '<b>Success:</b> Audit assesment send to audit department successfully!',
        'assesmentSendComplianceSuccess' => '<b>Success:</b> Audit assesment send to compliance successfully!',
        'assesmentBackComplianceSuccess' => '<b>Success:</b> Audit assesment send back to compliance successfully!',
        'assesmentSendReviewerSuccess' => '<b>Success:</b> Audit assesment send to reviewer successfully!',

        // multi level control
        'multiLevelControlNoData' => 'Periodwise set data not found! Please add periodwise set data.',

        // annexure columns
        'columnDuplicate' => 'Error: Column name already exists! Please enter other column name.',
        'annexColumnAddedSuccess' => '<b>Success:</b> Annexure Column added successfully!',
        'annexColumnUpdatedSuccess' => '<b>Success:</b> Annexure Column updated successfully!',
        'annexColumnDeletedSuccess' => '<b>Success:</b> Annexure Column deleted successfully!',

        // target master
        'target' => 'Error: Please enter valid target.',
        'yearDuplicate' => 'Error: Year already exists for this audit unit! Please select different year.',
        'auditUnitSelect' => 'Error: Please select valid audit unit.',
        'targetAddedSuccess' => '<b>Success:</b> Target details added successfully!',
        'targetUpdatedSuccess' => '<b>Success:</b> Target details updated successfully!',
        'targetDeletedSuccess' => '<b>Success:</b> Target details deleted successfully!',

        //last march position (exe summary)
        'glType' => 'Error: Please select GL Type!',
        'glTypeDuplicate' => 'Error: Please select other GL Type, this GL Type already exist!',
        'marchPositionAddedSuccess' => '<b>Success:</b> March Position added successfully!',
        'marchPositionUpdatedSuccess' => '<b>Success:</b> March Position updated successfully!',
        'marchPositionDeletedSuccess' => '<b>Success:</b> March Position deleted successfully!',
        'marchPosition' => 'Error: Please enter valid march position.',
        'newAccount' => ' Error: Please enter valid accounts count',

        //single account dump
        'accountNo' => 'Error: Please enter valid Account Number.',
        'accountHolderName' => 'Error: Please enter Account Holder Name.',
        'customerId' => 'Error: Please enter valid customer Id.',
        'intrestRate' => 'Error: Please enter valid Rate of Interest.',
        'amount' => 'Error: Please enter valid Amount.',
        'status' => 'Error: Please enter valid Status.',
        'accountDuplicate' => 'Error: Account Number already exist ! Please enter different Account Number.',
        'accountAddedSuccess' => '<b>Success:</b> Account added successfully!',
        'accountUpdatedSuccess' => '<b>Success:</b> Account updated successfully!',
        'accountDeletedSuccess' => '<b>Success:</b> Account deleted successfully!',
        'accountOpenDateBetween' => 'Error: Account Open Date is not between Upload period from and Upload period to ! Please check.',
        'renewalDateBetween' => 'Error: Renewal Date is not between Upload period from and Upload period to ! Please check.',
        'accountInAssesment' => 'Notice: Assesment done for this particular Account!',

        //password policy
        'passwordPolicySuccess' => '<b>Success:</b> Password Policy changes successfully!.',
        'min_length' => 'Error: Please enter number lower than 56.',
        'num_cnt' => 'Error: Please enter valid number.',
        'uppercase_cnt' => 'Error: Please enter valid number.',
        'lowercase_cnt' => 'Error: Please enter valid number.',
        'symbol_cnt' => 'Error: Please enter valid number.',
        'minLength' => 'Error: Password length should below 56.',
        'totalLength' => 'Error: Total of other numbers is greater than the Minimum password length.',

        //Executive Summary Basic Details
        'basicDetailsAddedSuccess' => '<b>Success:</b> Basic Details added successfully!',
        'basicDetailsUpdatedSuccess' => '<b>Success:</b> Basic Details updated successfully!',
        'basicDetailsDeletedSuccess' => '<b>Success:</b> Basic Details deleted successfully!',
        'reportSubmittedDate' => 'Error: Please enter valid report submited date!.',
        'staffCount' => 'Error: Please enter valid staff count [without decimal point].',
        'challansError' => 'Error: Please enter valid challans [without decimal point].',

        //Executive Summary Branch Position
        'branchPositionValue' => 'Error: Please enter valid amount [XX.XX].',
        'branchPositionAddedSuccess' => '<b>Success:</b> Branch Position added successfully!',
        'branchPositionUpdatedSuccess' => '<b>Success:</b> Branch Position updated successfully!',
        'branchPositionDeletedSuccess' => '<b>Success:</b> Branch Position deleted successfully!',

        //Executive Summary Fresh Account
        'freshAccountValue' => 'Error: Please enter valid No. of Accounts !',
        'freshAccountRequired' => 'Error: This field is required !',
        'freshAccountAddedSuccess' => '<b>Success:</b> Fresh Accounts added successfully!',
        'freshAccountUpdatedSuccess' => '<b>Success:</b> Fresh Accounts updated successfully!',
        'freshAccountDeletedSuccess' => '<b>Success:</b> Fresh Accounts deleted successfully!',

        // period wise data
        'periodWiseDataExists' => 'Error: Period wise data already exists in selected financial year!',
        'yearlyDataNotExists' => 'Error: Period wise yearly data not exists! Please add yearly data first',
        'periodWiseDataAddedSuccess' => '<b>Success:</b> Period wise data added successfully!',
        'periodWiseDataUpdatedSuccess' => '<b>Success:</b> Period wise data updated successfully!',
        'periodWiseDataDeletedSuccess' => '<b>Success:</b> Period wise data deleted successfully!',
        'schemeCheckError' => 'Error: Please select valid schemes.',
        'menuCheckError' => 'Error: Please select valid menus.',
        'menuDataError' => 'Error: Menu data not found! Please add / update menu data',
        'categoryCheckError' => 'Error: Please select valid categories.',
        'categoryDataError' => 'Error: Category data not found! Please add / update category data',
        'headerDataError' => 'Error: Question header data not found! Please add / update question header data',
        'questionCheckError' => 'Error: Please select valid questions.',

        // branch rating
        'rangeFrom' => 'Error: Please enter valid number.',
        'branchRatingAddedSuccess' => '<b>Success:</b> Branch Rating added successfully!',
        'branchRatingUpdatedSuccess' => '<b>Success:</b> Branch Rating updated successfully!',
        'branchRatingDeletedSuccess' => '<b>Success:</b> Branch Rating deleted successfully!',
        'branchRatingDataNotFound' => 'Error: Branch Rating data not found!',
        'riskType' => 'Error: Risk Type already exists! Please select other Risk Type.',
        'rangeValidate' => 'Error: Value should equals to previous Range To value !',
        'yearUnitDuplicate' => 'Error: Audit Unit already exists for this Year! Please select different Audit Unit.',

        // question master
        'menuEmpty' => 'Error: Menu data not found!',
        'categoryEmpty' => 'Error: Category data not found!',
        'questionSetEmpty' => 'Error: Question set data not found!',
        'ansDataNotFound' => 'Error: Answer data not found!',

        // carry forward model
        'cfDataNotFound' => 'Error: Carry forward data not found!',

        //misc error
        'noDataFound' => 'Currently! No data found.',
        'errorNumber' => 'Error: Please enter valid number.',
        'filterError' => 'Error: Please select valid filter',
        'somethingWrong' => 'Something went wrong! Please try after sometime.',
        'errorSaving' => 'Error saving data! Please try after sometime.',
        'errorFinding' => 'Error finding data! Please try after sometime.',
        'errorDeleting' => 'Error Deleting data! Please try after sometime.',
        'dateError' => 'Error: Please select valid date with format [YYYY-MM-DD].',
        'yearMonthError' => 'Error: Please enter valid month year with format [YYYY-MM].',
        'dateGratorError' => 'Error: Upload date to should be later than the upload date from!',
        'endDateGratorError' => 'Error: End date should be later than the start date!',
        'endMonthGratorError' => 'Error: End month should be later than the start month!',
        'notFYMonthError' => 'Error: Start & End month not in Financial Year!',
        'auditStartDisableAction' => 'Error: Currently, the audit has started, and other actions are disabled.',
        'dateGratorTodayError' => 'Error: Date must be greater than today\'s date!',
        'invalidRequestError' => 'Error: Invalid request',

        'auditUnitNoDataFound' => 'Error: Audit Unit - Branch! Data not found!',
        'HOAuditUnitNoDataFound' => 'Error: Audit Unit - HO Department! Data not found!',
        'validTrend' => 'Error: Please select valid trend on',
        'overlapTrendDates' => 'Error: Period 2 dates conflict with Period 1 dates due to overlap.',

        'csvUploadError' => 'Error: Please upload valid CSV file.',
        'csvNoData' => 'Error: Currently no dump data found!',
        'errAccount' => 'Error: Account has error! Please resolve error and re-upload CSV file.',
        'dataUploadedSuccess' => '<b>Success:</b> Data uploaded successfully!',

        'assesmentNotFound' => 'Error: Assesment data not found!',
        'assesmentNotFound2' => 'Error: Assesment data not found! Please select valid assesment',
        'endAssesmentSuccess' => '<b>Success:</b> Audit end assesment successfully done!',
        'endAssesmentAlreadyDoneError' => 'Error: Audit end assesment already done! You can\'t end assesment again.',

        // carry forward
        'carryForwardDataNotFound' => 'Carry forward points data not found',
        
        // remark
        'auditRemarkType' => 'Error: Please select valid remark type',
        'auditRemarkAddedSuccess' => '<b>Success:</b> Remark added successfully!',
        'auditRemarkUpdatedSuccess' => '<b>Success:</b> Remark updated successfully!',
        'auditRemarkDeletedSuccess' => '<b>Success:</b> Remark deleted successfully!',
        'auditRemarkViewedError' => 'Error: Audit remark already viewed! Can\'t remove.',

        // Compliance Pro 16.09.2024 Notifications ----------------------------------

        // authority master
        'authorityType' => 'Error: Please select valid authority',
        'authorityDuplicate' => 'Error: Authority already exist ! Please enter different authority.',
        'authorityAddedSuccess' => '<b>Success:</b> Authority added successfully!',
        'authorityUpdatedSuccess' => '<b>Success:</b> Authority updated successfully!',

        // circular set master
        'refNoError' => 'Error: Please enter valid reference no.',
        'circularTypeError' => 'Error: Please select circular type',
        'selectCircularError' => 'Error: Please select valid circular',
        'priorityIdError' => 'Error: Please select valid priority',
        'circularSetAddedSuccess' => '<b>Success:</b> Circular set added successfully!',
        'circularSetUpdatedSuccess' => '<b>Success:</b> Circular set updated successfully!',
        'circularSetDeletedSuccess' => '<b>Success:</b> Circular set deleted successfully!',
        'circularNotFound' => 'Error: Circular not found!',
        'reportingDateEarlierError' => 'Error: Reporting date must be grater than the circular date.',
        'circularNotExists' => 'Circular not exists! Please check the circular.',

        // circular task master
        'circularTaskAddedSuccess' => '<b>Success:</b> Circular task added successfully!',
        'circularTaskUpdatedSuccess' => '<b>Success:</b> Circular task updated successfully!',
        'circularTaskDeletedSuccess' => '<b>Success:</b> Circular task deleted successfully!',
        'circularTaskNotFound' => 'Error: Circular task not found!',

        // circular task set master
        'circularTaskSetSelectError' => 'Error: Please select valid circular set',
        'circularTaskSetAddedSuccess' => '<b>Success:</b> Circular task set added successfully!',
        'circularTaskSetUpdatedSuccess' => '<b>Success:</b> Circular task set updated successfully!',
        'circularTaskSetDeletedSuccess' => '<b>Success:</b> Circular task set deleted successfully!',
        'circularTaskSetAssignSuccess' => '<b>Success:</b> Circular task set assigned successfully!',
        'circularTaskSetNotFound' => 'Error: Circular task set not found!',

        // circular assign
        'circularFrequency' => 'Error: Please select valid frequency',
        'circularAssignSuccess' => '<b>Success:</b> Circular assigned successfully!',

        // compliance comments
        'complianceSendCCOSuccess' => '<b>Success:</b> Compliance send to CCO for review successfully!',
        'complianceCompletedAuditSuccess' => '<b>Success:</b> Compliance completed successfully!',

        // reviewer action
        'dumpMarkAsComplete' => 'Mark as completed for selected Account has completed',
        'circularCompletedComplianceSuccess' => '<b>Success:</b> Circular compliance completed successfully!',
        'circularBackComplianceSuccess' => '<b>Success:</b> Circular compliance send back to compliance successfully!',
        'circularSendReviewerSuccess' => '<b>Success:</b> Circular compliance send to reviewer successfully!',

        'comDocsLimitError' => 'Error: Document limit exceeded. Please increase the limit or contact the administrator.',
        'dueDateEarlierError' => 'Error: Due date must be earlier than the reporting date.',
        'comCircularDocRemovedSuccess' => '<b>Success:</b> Document removed successfully!',
        
        // compliance submit
        'comCompletedAssesError' => 'Error: Please check the completed compliance before submitting the report.',
        'comSubmitReport' => '<b>Success:</b> Submit report successfully!',
        'comSubmitUpdateReport' => '<b>Success:</b> Submited report updated successfully!',
        'comSubmitNotFound' => 'Error: Submit report not found!',

        // Compliance Pro 16.09.2024 Notifications ----------------------------------

        'checkRemoved' => '<b>Success:</b> Check removed successfully!',
        'checkChecked' => '<b>Success:</b> Checked successfully!',

        'statusInactive' => '<b>Success:</b> Status changed to Inactive!',
        'statusActive' => '<b>Success:</b> Status changed to Active!',
        'statusChange' => '<b>Success:</b> Status changed successfully!',

        'checkboxCheckError' => 'Error: Please check the above checkbox.',
    );

    public static function getNoti($key ) {
        return (self::$notificationsArray[ $key ]) ?? null;
    }

    public static function getInputNoti(Request $request, $key, $type = 'x' ) {

        $cErr = $request -> input( $key );

        if(!empty($cErr) && array_key_exists($cErr, self::$notificationsArray ))
            return self::cError(self::$notificationsArray[ $cErr ], $type);

        return self::cError($cErr);
    }

    public static function getSessionAlertNoti($key = null, $type = 'x' ) {

        $val = null;

        if($key == null)
        {
            foreach(['success', 'warning', 'danger'] as $c_alerts) {

                $val = Session::flash( $c_alerts );
                
                if( /*Session::has( $c_alerts ) && */ !empty($val) )
                {
                    $key = $c_alerts;
                    break;
                }
            }

            return self::getCustomAlertNoti($val, $key);
        }

        return null;

    }

    public static function getCustomAlertNoti($key, $alert = 'warning', $class = NULL, $removeMB = false)
    {
        if(!empty($key) && array_key_exists($key, self::$notificationsArray ))
            return self::cError(self::$notificationsArray[ $key ], $alert, $class, $removeMB);

        //for custom notifcation
        if(!empty($key) && !array_key_exists($key, self::$notificationsArray ))
            return self::cError($key, $alert, $class, $removeMB);
    }

    //function for create notification
    public static function cError($str, $type = 'x', $class = NULL, $removeMB = false) {

        switch($type) {
            //success
            case 'success' 	:	{
                                    $class = !empty($class) ? $class : 'icn-check-green';
                                    return '<div class="alert alert-success '. ((!$removeMB) ? ' mb-3 ' : '' ) . $class .' alert-grid icn-grid icn-bf rounded-0" role="alert">' . $str . '</div>';
                                    break;
                                }

            //warning
            case 'warning' 	:	{
                                    $class = !empty($class) ? $class : 'icn-question-yellow';
                                    return '<div class="alert alert-warning '. ((!$removeMB) ? ' mb-3 ' : '' ) . $class .' alert-grid icn-grid icn-bf rounded-0" role="alert" data-mdb-color="warning">' . $str . '</div>';
                                    break;
                                }
            
            //danger
            case 'danger' 	:	{
                                    $class = !empty($class) ? $class : 'icn-error-red';
                                    return '<div class="alert alert-danger '. ((!$removeMB) ? ' mb-3 ' : '' ) . $class .' alert-grid icn-grid icn-bf rounded-0" role="alert" data-mdb-color="danger">' . $str . '</div>';
                                    break;
                                }
            
            //default small or 'x'
            default 	    :	{
                                    $class = !empty($class) ? $class : 'text-danger';
                                    return '<span class="d-block font-sm '. $class .' mt-1">' . $str . '</span>';
                                    break;				
                                }
        }
    }
}


?>