<?php

namespace App\Http\Controllers\Panel;

use App\User;
use Carbon\Carbon;
use App\Models\Role;
use App\Models\Webinar;
use App\Models\Category;
use App\Models\UserMeta;
use App\CourseVisibility;
use App\Models\UserBreak;
use App\Models\AuditTrail;
use App\Models\Newsletter;
use App\Models\UserZoomApi;
use App\Models\UserDocument;
use Illuminate\Http\Request;
use App\Helpers\WebinarHelper;
use App\Models\UserOccupation;
use App\Exports\StudentsExport;
use App\Models\UserInformation;
use App\Models\OrganizationData;
use App\Models\OrganizationSite;
use App\Models\StudentDeclaration;
use App\Http\Controllers\Controller;
use App\Models\OrganizationContract;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\File;
use Illuminate\Support\Facades\Hash;
use Maatwebsite\Excel\Facades\Excel;
use Illuminate\Support\Facades\Storage;
use App\Traits\AjaxOrganizationUsersTrait;
use App\Models\Translation\WebinarTranslation;


class UserController extends Controller
{
    use AjaxOrganizationUsersTrait;

    public function deleteDocument( $docId ){
        $documentId =  $docId;
        $userDocument = UserDocument::where("id", $documentId)->first();
        if($userDocument){

            if ($userDocument->delete()) {
                File::delete('store/' . $userDocument->user_id . '/user_documents/'.$userDocument->document);
                return back();
            }
        }
        abort(404);
    }

    function exportStudents(Request $request) {
        $users = $this->manageUsers($request, "students", false, true);

        $usersExport = new StudentsExport($users);

        return Excel::download($usersExport, 'students.xlsx');
    }

    public function setting($step = 1)
    {
        $user = auth()->user();
        $categories = Category::where('parent_id', null)
            ->with('subCategories')
            ->get();
        $userMetas = $user->userMetas;

        $occupations = $user->occupations->pluck('category_id')->toArray();
        $documents = $user->getUserDocuments;
        $userInfo = $user->userInfo;
        //por revisar
        //  if($user->role_name == 'teacher'){
        //     $user->role_name = "organization";
        //  }

        $studentDeclarationCompleted = StudentDeclaration::where('user_id', $user->id)->exists();
        $usiSupportingDoc = UserDocument::where(['user_id'=>$user->id,
            'description'=>'Supporting document for 100 Points Identification',
            'student_visibility' => true])->exists();
        //is user being edited an Organization
        if ($user->role_name === Role::$organization) {
            $organizationData = OrganizationData::where('organ_id', $user->id)->first();

            $organizationContracts = OrganizationContract::where('organ_id', $user->id)->pluck('contract')->toArray();
            if(count($organizationContracts) && in_array(OrganizationContract::$other, $organizationContracts)) {
                $organizationOtherContract = OrganizationContract::where(['organ_id' => $user->id,
                    'contract' => OrganizationContract::$other])->first()->other_contract;
            }
        }


        //Enrolment check for Students
        $enrollmentMessage = null;
        if ($step > 3 && $user->isUser()) {
            $missingStep = $user->getMissingUserEnrolmentStep();
            if ($missingStep > 0 && $step > $missingStep) {
                $step = $missingStep;
                $enrollmentMessage = trans('panel.please_complete_enrolment_information_first');
            } else {
                if ($step === 10 && $studentDeclarationCompleted) {
                    $step = 9; //if declaration page is being accessed and it is already filled, don't show again.
                } elseif ($step === 12 ) { //identity & financial
                    $step = 9; //identity & financial has been hidden on client's request
                }
            }
        }

        //Don't show steps beyond step 2 for any roles besides Student
        //See: https://trello.com/c/c47wjNIG/158-remove-the-student-fill-in-detail-page-from-the-consultant-when-creating-new-student
        if (!$user->isUser() && $step > 2) {
            $step = 2;
        }

        $userLanguages = getGeneralSettings('user_languages');
        if (!empty($userLanguages) and is_array($userLanguages)) {
            $userLanguages = getLanguages($userLanguages);
        } else {
            $userLanguages = [];
        }

        $data = [
            'pageTitle' => trans('panel.settings'),
            'user' => $user,
            'userInfo' => $userInfo,
            'categories' => $categories,
            'educations' => $userMetas->where('name', 'education'),
            'experiences' => $userMetas->where('name', 'experience'),
            'occupations' => $occupations,
            'userLanguages' => $userLanguages,
            'currentStep' => $step,
            'enrollmentMessage' => $enrollmentMessage,
            'usiSupportingDoc' => $usiSupportingDoc,
            'studentDeclarationCompleted' => $studentDeclarationCompleted,
            'usiDocuments' => $documents,
        ];
        if ($user->isOrganization()) {
            $data['organizationData'] = $organizationData;
            $data['organizationContracts'] = $organizationContracts;
            $data['organizationOtherContract'] = isset($organizationOtherContract) ? $organizationOtherContract : null;
        }
        // dd($data);
        return view(getTemplate() . '.panel.setting.index', $data);
    }

    public function update(Request $request)
    {
        $data = $request->all();
        $organization = null;
        if (!empty($data['organization_id']) and !empty($data['user_id'])) {
            $organization = auth()->user();
            $organizationId = ($organization->isOrganization()) ? $organization->id : $organization->organ_id;
            $user = User::where('id', $data['user_id'])
            ->where('organ_id', $organizationId)
            ->first();
        } else {
            $user = auth()->user();
        }
        $step = $data['step'] ?? 1;
        $nextStep = (!empty($data['next_step']) and $data['next_step'] == '1') ?? false;

        $rules = [
            'iban' => 'required_with:account_type',
            'account_id' => 'required_with:account_type',
            'identity_scan' => 'required_with:account_type',
            'bio' => 'nullable|string|min:3|max:48',
        ];
        $validationMessages = [];

        if ($step == 1) {
            $rules = array_merge($rules, [
                'full_name' => 'required|string',
                'email' => 'required|email|unique:users,email,' . $user->id,
            ]);
            if($user->isUser()){
                $this->validate($request, [
                    'mobile' => 'required|numeric|unique:users,mobile,' . $user->id,
                ]);
            }
        }

        if ($step == 3) {
            $rules = array_merge($rules, [
                'title' => 'required',
                'first_name' => 'required|string',
                'sur_name' => 'required|string',
                'dob' => 'required|date_format:d/m/Y',
                'gender' => 'required',
                'address' => 'required',
                'suburb' => 'required',
                'state' => 'required',
                'post_code' => 'required',
                'emergency_contact' => 'required',
                'contact_number' => 'required',
            ]);
            $validationMessages['dob.required'] = "The " . trans('public.dob')." field is required";
            $validationMessages['dob.date_format'] = "The " . trans('public.dob')." is required in the 'DD/MM/YYYY' format";
        }
        if ($step == 4) {
            $rules = array_merge($rules, [
                'cultural_identity' => 'required',
                'birth_country' => 'required|string',
                'birth_city' => 'required|string',
                'citizenship' => 'required',
            ]);
        }
        if ($step == 5) {
            if($request->has("does_speak_other_language")){
                $rules = array_merge($rules, [
                    'other_language' => 'required_with:does_speak_other_language',
                ]);
            }
        }
        if ($step == 6) {
            $rules = array_merge($rules, [
                'employment_type' => 'required',
            ]);
        }
        if ($step == 7) {
            $rules = array_merge($rules, [
                'school_level' => 'required',
                'school_completed_year' => 'required_if:school_level,1,2,3,4,5',
                'enrolled_studies' => 'required_with:is_enrolled',
            ]);
        }
        if ($step == 8) {
            $rules = array_merge($rules, [
                'study_reason' => 'required',
            ]);
        }
        if ($step == 9) {
            $rules = array_merge($rules, [
                'can_gaps_search_usi' => 'required_without:rto_permission',
                'rto_permission' => 'required_without:can_gaps_search_usi',
            ]);

            //check if the user has document don't need to check for validation rules
            $userInfo = UserInformation::where('user_id', $user->id)->first();
            $fileExists = UserDocument::where(['user_id'=> $user->id,
            'description'=>'Supporting document for 100 Points Identification',
            'student_visibility' => true])->exists();

            if(!$fileExists) {
                $rules = array_merge($rules, [
                    'usi_doc' => 'required_with:rto_permission',
                    'usi_doc.*' => 'file',
                ]);
            }
        }
        if ($step == 10) {
            $rules = array_merge($rules, [
                'student_name' => 'required',
            ]);
            $checkName = strcmp( strtoupper($user->full_name) , strtoupper($request->student_name));
            if($checkName !== 0){
                session()->flash("no-match", "Please enter same name");
                return back();
            }
        }

        $this->validate($request, $rules, $validationMessages);
        if (!empty($user)) {

            if (!empty($data['password'])) {
                $this->validate($request, [
                    'password' => 'required|confirmed|min:6',
                ]);

                $user->update([
                    'password' => User::generatePassword($data['password'])
                ]);
            }

            $updateData = [];
            $updateInfoData = [];

            if ($step == 1) {
                $joinNewsletter = (!empty($data['join_newsletter']) and $data['join_newsletter'] == 'on');
                if (is_array($data['organization_site'])) {
                    $organizationSitesString = (isset($data['organization_site']) && count($data['organization_site']) > 0) ? implode(',',$data['organization_site']) : null;
                } else {
                    $organizationSitesString = $data['organization_site'];
                }

                $updateData = [
                    'email' => $data['email'],
                    'full_name' => $data['full_name'],
                    'mobile' => $data['mobile'],
                    'language' => $data['language'],
                    'newsletter' => $joinNewsletter,
                    'public_message' => (!empty($data['public_messages']) and $data['public_messages'] == 'on'),
                    'organization_site' => isset($data['organization_site'])? $data['organization_site'] : null,
                    'course' => isset($data['course'])?$data['course']:null,
                ];

                if (isset($data['bio']) && !empty(trim($data['bio']))) {
                    $updateData['bio'] = !empty($data['bio']) ? $data['bio'] : null;
                }

                if ($user->isOrganization()) {
                    //remove existing contract records
                    OrganizationContract::where('organ_id', $user->id)->delete();
                    if (isset($data['contract'])) {
                        $insertData = [];
                        foreach($data['contract'] as $contract) {
                            $insertData[] = [
                                'organ_id' => $user->id,
                                'contract' => $contract,
                                'other_contract' => ($contract === OrganizationContract::$other && isset($data['other_contract']) && strlen(trim($data['other_contract'])) > 0) ? trim($data['other_contract']) : null,
                            ];
                        }
                        OrganizationContract::insert($insertData);
                    }

                    OrganizationData::where('organ_id', $user->id)->delete();
                    $organizationData = [];
                    if (isset($data['po_num_required'])) {
                        $organizationData += [
                            'po_num_required' => $data['po_num_required'],
                        ];
                    }
                    if (isset($data['po_sequence']) && strlen(trim($data['po_sequence'])) > 0) {
                        $organizationData += [
                            'po_sequence' => $data['po_sequence'],
                        ];
                    }
                    if (isset($organizationData) && count($organizationData)) {
                        $organizationData += [
                            'organ_id' => $user->id,
                        ];
                        OrganizationData::create($organizationData);
                    }

                }

                if(isset($organization) && ($organization->isTeacher() || $organization->isOrganization() || $organization->isOrganizationManager())) {
                    $updateData['organ_id'] = $data['organization'];
                    $organization_site = $request->get('organization_site');
                    $organizationSitesString = (isset($organization_site) && count($organization_site) > 0) ? implode(',',$organization_site) : null;
                    if(isset($organizationSitesString)) {
                        $updateData['organization_site'] = $organizationSitesString;
                    }
                    $manager_id = $request->get('manager_id');
                    if (isset($manager_id)) {
                        $updateData['manager_id'] = $manager_id;
                    }
                    $updateData['schedule'] = isset($data['schedule']) ? $data['schedule'] : null;
                }

                $this->handleNewsletter($data['email'], $user->id, $joinNewsletter);
            } elseif ($step == 2) {
                $updateData = [
                    'cover_img' => $data['cover_img'],
                ];

                if (!empty($data['profile_image'])) {
                    $profileImage = $this->createImage($user, $data['profile_image']);
                    $updateData['avatar'] = $profileImage;
                }
            } elseif ($step == 3) {
                $updateInfoData = [
                    'user_id' => $user->id,
                    'title' => $data['title'] ?? null,
                    'first_name' => $data['first_name'] ?? null,
                    'middle_name' => $data['middle_name'] ?? null,
                    'sur_name' => $data['sur_name'] ?? null,
                    'dob' => (!empty($data['dob']) ? Carbon::createFromFormat('d/m/Y', $data['dob'])->format('Y-m-d') : null) ?? null,
                    'gender' => ($data['gender']) ?? 0,
                    'suburb' => $data['suburb'] ?? null,
                    'state' => $data['state'] ?? null,
                    'post_code' => $data['post_code'] ?? null,
                    'emergency_contact' => $data['emergency_contact'] ?? null,
                    'contact_number' => $data['contact_number'] ?? null,
                    'send_result_to_employer' => ($data['send_result_to_employer']) ?? 0,
                ];
                if (isset($data['address']) && strlen(trim($data['address'])) > 0) {
                    $updateData = [
                        'address' => $data['address'] ?? null,
                    ];
                }
            } elseif ($step == 4) {
                $updateInfoData = [
                    'user_id' => $user->id,
                    'cultural_identity' => $data['cultural_identity'] ?? null,
                    'birth_country' => $data['birth_country'] ?? null,
                    'birth_city' => $data['birth_city'] ?? null,
                    'citizenship' => ($data['citizenship']) ?? 0,
                ];
            } elseif ($step == 5) {
                $updateInfoData = [
                    'user_id' => $user->id,
                    'does_speak_other_language' => ($data['does_speak_other_language']) ?? 0,
                    'other_language' => ($data['other_language']) ?? " ",
                    'require_assistance' => ($data['require_assistance']) ?? 0,
                    'is_disable' => ($data['is_disable']) ?? 0,
                    'disability' => ($data['disability']) ?? 0,
                ];
            } elseif ($step == 6) {
                $updateInfoData = [
                    'user_id' => $user->id,
                    'employment_type' => ($data['employment_type']) ?? 0,
                ];
            } elseif ($step == 7) {
                $updateInfoData = [
                    'user_id' => $user->id,
                    'attending_secondary_school' => ($data['attending_secondary_school']) ?? 0,
                    'school_level' => ($data['school_level']) ?? 0,
                    'school_completed_year' => $data['school_completed_year'],
                    'completed_qualification_in_australia' => ($data['completed_qualification_in_australia']) ?? 0,
                    'is_enrolled' => ($data['is_enrolled']) ?? 0,
                    'enrolled_studies' => $data['enrolled_studies'],
                    'certificate1' => ($data['certificate1']) ?? 0,
                    'certificate1_qualification' => $data['certificate1_qualification'] ?? null,
                    'certificate1_year_completed' => ((isset($data['certificate1_qualification'])) && strlen(trim($data['certificate1_qualification']))) ? $data['certificate1_year_completed'] : null,
                    'certificate2' => ($data['certificate2']) ?? 0,
                    'certificate2_qualification' => $data['certificate2_qualification'],
                    'certificate2_year_completed' => ((isset($data['certificate2_qualification'])) && strlen(trim($data['certificate2_qualification']))) ? $data['certificate2_year_completed'] : null,
                    'certificate3' => ($data['certificate3']) ?? 0,
                    'certificate3_qualification' => $data['certificate3_qualification'],
                    'certificate3_year_completed' => ((isset($data['certificate3_qualification'])) && strlen(trim($data['certificate3_qualification']))) ? $data['certificate3_year_completed'] : null,
                    'certificate4' => ($data['certificate4']) ?? 0,
                    'certificate4_qualification' => $data['certificate4_qualification'],
                    'certificate4_year_completed' => ((isset($data['certificate4_qualification'])) && strlen(trim($data['certificate4_qualification']))) ? $data['certificate4_year_completed'] : null,
                    'diploma' => ($data['diploma']) ?? 0,
                    'diploma_qualification' => $data['diploma_qualification'],
                    'diploma_year_completed' => ((isset($data['diploma_qualification'])) && strlen(trim($data['diploma_qualification']))) ? $data['diploma_year_completed'] : null,
                    'adiploma' => ($data['adiploma']) ?? 0,
                    'adiploma_qualification' => $data['adiploma_qualification'],
                    'adiploma_year_completed' => ((isset($data['adiploma_qualification'])) && strlen(trim($data['adiploma_qualification']))) ? $data['adiploma_year_completed'] : null,
                    'bachelor' => ($data['bachelor']) ?? 0,
                    'bachelor_qualification' => $data['bachelor_qualification'],
                    'bachelor_year_completed' => ((isset($data['bachelor_qualification'])) && strlen(trim($data['bachelor_qualification']))) ? $data['bachelor_year_completed'] : null,
                    'miscellaneous' => ($data['miscellaneous']) ?? 0,
                    'miscellaneous_qualification' => $data['miscellaneous_qualification'],
                    'miscellaneous_year_completed' => ((isset($data['miscellaneous_qualification'])) && strlen(trim($data['miscellaneous_qualification']))) ? $data['miscellaneous_year_completed'] : null,

                ];
            } elseif ($step == 8) {
                $updateInfoData = [
                    'user_id' => $user->id,
                    'study_reason' => ($data['study_reason']) ?? 0
                ];
            } elseif ($step == 9) {
                $updateInfoData = [
                    'user_id' => $user->id,
                    'usi_number' => $data['usi_number'] ?? null,
                    'can_gaps_search_usi' => ($data['can_gaps_search_usi']) ?? 0,
                    'rto_permission' => ($data['rto_permission']) ?? 0
                ];
                if (isset($data['rto_permission']) && !empty($data['rto_permission'])) {
                    /**
                     * if docType is not set, it means docType is default and default is "Enrolment"
                     * "Enrolment" = 100 Points Supporting Document
                     * So, if docType is "Enrolment" then "student_visibility" is "true" otherwise "false"
                     */
                    // dd(is_uploaded_file($data['usi_doc'][0]));
                    $studentVisibility = (isset($data["docType"]) && $data["docType"] != "Enrolment") ? false : true;
                    if (isset($data['usi_doc'][0]) && is_uploaded_file($data['usi_doc'][0])) {

                        $filenames = uploadFile($request, 'usi_doc', 'store/' . $user->id . '/user_documents');
                        foreach($filenames as $filename){
                            $documentData = [
                                'user_id' => $user->id,
                                'title' => isset($data["docTitle"]) ? $data["docTitle"] : '100 Points Supporting Document',
                                'type' => isset($data["docType"]) ? $data["docType"] : 'Enrolment',
                                'document_side' => isset($data["docSide"]) ? $data["docSide"] : 'front',
                                'description' => 'Supporting document for 100 Points Identification',
                                'document' => $filename,
                                'student_visibility' => $studentVisibility,
                                'uploaded_by' => auth()->user()->id,
                                'created_at' => Carbon::now(),
                                'updated_at' => Carbon::now()
                            ];
                           UserDocument::insert($documentData);
                        }
                    }
                }
            } elseif ($step == 10) {
                if (isset($data['student_name']) && strlen(trim($data['student_name'])) > 1) {
                    $userDeclarationData = [
                        'user_id' => $user->id,
                        'student_name' => trim($data['student_name']) ?? null,
                    ];

                    if (StudentDeclaration::create($userDeclarationData)) {
                        //Notify Admins that user's enrolment process has completed
                        $notifyOptions = [
                            '[student.name]' => $user->full_name,
                            '[student.id]' => $user->id
                        ];
                        $adminUserIds = User::where(['role_name'=> Role::$admin, 'status' => User::$active])->pluck('id')->toArray();
                        $studentOrganizaton = User::where(['id' => $user->id, 'organ_id'=> $user->organ_id])->pluck('organ_id')->toArray();
                        $userIds = array_merge($adminUserIds, $studentOrganizaton);
                        sendNotification('student_enrolment_completed', $notifyOptions, $userIds);

                        //Audit Trail entry - enrolment completed
                        $audit = new AuditTrail();
                        $audit->user_id = $user->id;
                        $audit->organ_id = $user->organ_id;
                        $audit->role_name = $user->role_name;
                        $audit->audit_type = AuditTrail::auditType['enrolment_completed'];
                        $audit->added_by = $user->id;
                        $audit->description = "Student Completed Enrolment";
                        $ip = null;
                        $ip = getClientIp();
                        $audit->ip = ip2long($ip);
                        $audit->save();

                        $toastData = [
                            'title' => trans('public.request_success'),
                            'msg' => trans('panel.user_thankyou_enrolment'),
                            'status' => 'success'
                        ];

                        return redirect('/')->with(['toast' => $toastData]);
                    }
                }

            } elseif ($step == 11) {
                UserOccupation::where('user_id', $user->id)->delete();
                if (!empty($data['occupations'])) {

                    foreach ($data['occupations'] as $category_id) {
                        UserOccupation::create([
                            'user_id' => $user->id,
                            'category_id' => $category_id
                        ]);
                    }
                }
            } elseif ($step == 12) {
                $updateData = [
                    'account_type' => $data['account_type'] ?? '',
                    'iban' => $data['iban'] ?? '',
                    'account_id' => $data['account_id'] ?? '',
                    'identity_scan' => $data['identity_scan'] ?? '',
                    'certificate' => $data['certificate'] ?? '',
                    'address' => $data['address'] ?? '',
                ];
            } elseif ($step == 13) {
                if (!$user->isUser() and !empty($data['zoom_jwt_token'])) {
                    UserZoomApi::updateOrCreate(
                        [
                            'user_id' => $user->id,
                        ],
                        [
                            'jwt_token' => $data['zoom_jwt_token'],
                            'created_at' => time()
                        ]
                    );
                }
            }

            if (!empty($updateData)) {
                $user->update($updateData);
                if ($step == 1) {
                    //organizaiton sites pivot insertion
                    if (isset($data['organization_site']) && is_array($data['organization_site']) && count($data['organization_site'])) {
                        $organizationSites = $data['organization_site'];
                        if (!empty($organization)) {
                            $organizationSites = array_fill_keys($organizationSites, array(
                                'organ_id' => ($organization->isOrganization()) ? $organization->id : $organization->organ_id,
                            ));
                            $user->organizationSites()->sync($organizationSites );
                        } elseif (!empty($user->organ_id) && $user->isUser()) {
                            $organizationSites = array_fill_keys($organizationSites, array(
                                'organ_id' => $user->organ_id,
                            ));
                            $user->organizationSites()->sync($organizationSites );
                        }
                        // in case site wasn't already applied, syncing here will cause issues
                        // $user->organizationSites()->sync($organizationSites );
                    } else {
                        $organizationSites = [$data['organization_site']];
                        $organizationSites = array_fill_keys($organizationSites, array(
                            'organ_id' => $user->organ_id,
                        ));
                        $user->organizationSites()->sync($organizationSites);

                    }
                }
            }

            if(!empty($updateInfoData)){
                $userInfo = UserInformation::where('user_id', $user->id)->first();
                if($userInfo){
                    UserInformation::where('id', $userInfo->id)->update($updateInfoData);
                }
                else{
                    UserInformation::insert($updateInfoData);
                }
            }

            $url = '/panel/setting';
            if (!empty($organization)) {
                $user_type = 'students'; //default fallback - student
                if ($user->isTeacher()) {
                    $user_type = 'instructors';
                } elseif ($user->isOrganizationManager()) {
                    $user_type = 'managers';
                } elseif ($user->isOrganizationSubManager()) {
                    $user_type = 'sub_managers';
                } elseif ($user->isOrganizationStaff()) {
                    $user_type = 'consultants';
                }

                $url = '/panel/manage/' . $user_type . '/' . $user->id . '/edit';
            }

            if ($step <= 13) {
                if ($nextStep) {
                    $step = $step + 1;
                }
                $studentDeclaration = StudentDeclaration::where('user_id', $user->id)->exists();
                if (in_array($step, [10, 11]) && $studentDeclaration) {
                    $step = 9; //declaration should be shown only once
                }

                $url .= '/step/' . (($step <= 13) ? $step : 13);
            }

            $toastData = [
                'title' => trans('public.request_success'),
                'msg' => trans('panel.user_setting_success'),
                'status' => 'success'
            ];

            return redirect($url)->with(['toast' => $toastData]);
        }
        abort(404);
    }

    private function handleNewsletter($email, $user_id, $joinNewsletter)
    {
        $check = Newsletter::where('email', $email)->first();

        if ($joinNewsletter) {
            if (empty($check)) {
                Newsletter::create([
                    'user_id' => $user_id,
                    'email' => $email,
                    'created_at' => time()
                ]);
            } else {
                $check->update([
                    'user_id' => $user_id,
                ]);
            }
        } elseif (!empty($check)) {
            $check->delete();
        }
    }

    public function createImage($user, $img)
    {
        $folderPath = "/" . $user->id . '/avatar/';

        $image_parts = explode(";base64,", $img);
        $image_type_aux = explode("image/", $image_parts[0]);
        $image_type = $image_type_aux[1];
        $image_base64 = base64_decode($image_parts[1]);
        $file = uniqid() . '.' . $image_type;

        Storage::disk('public')->put($folderPath . $file, $image_base64);

        return Storage::disk('public')->url($folderPath . $file);
    }

    public function storeMetas(Request $request)
    {
        $data = $request->all();

        if (!empty($data['name']) and !empty($data['value'])) {

            if (!empty($data['user_id'])) {
                $organization = auth()->user();
                $user = User::where('id', $data['user_id'])
                    ->where('organ_id', $organization->id)
                    ->first();
            } else {
                $user = auth()->user();
            }

            UserMeta::create([
                'user_id' => $user->id,
                'name' => $data['name'],
                'value' => $data['value'],
            ]);

            return response()->json([
                'code' => 200
            ], 200);
        }

        return response()->json([], 422);
    }

    public function updateMeta(Request $request, $meta_id)
    {
        $data = $request->all();
        $user = auth()->user();

        if (!empty($data['user_id'])) {
            $checkUser = User::find($data['user_id']);

            if ((!empty($checkUser) and ($data['user_id'] == $user->id) or $checkUser->organ_id == $user->id)) {
                $meta = UserMeta::where('id', $meta_id)
                    ->where('user_id', $data['user_id'])
                    ->where('name', $data['name'])
                    ->first();

                if (!empty($meta)) {
                    $meta->update([
                        'value' => $data['value']
                    ]);

                    return response()->json([
                        'code' => 200
                    ], 200);
                }

                return response()->json([
                    'code' => 403
                ], 200);
            }
        }

        return response()->json([], 422);
    }

    public function deleteMeta(Request $request, $meta_id)
    {
        $data = $request->all();
        $user = auth()->user();

        if (!empty($data['user_id'])) {
            $checkUser = User::find($data['user_id']);

            if (!empty($checkUser) and ($data['user_id'] == $user->id or $checkUser->organ_id == $user->id)) {
                $meta = UserMeta::where('id', $meta_id)
                    ->where('user_id', $data['user_id'])
                    ->first();

                $meta->delete();

                return response()->json([
                    'code' => 200
                ], 200);
            }
        }

        return response()->json([], 422);
    }

    public function manageUsersBehindSchedule(Request $request)
    {
        $user_type = 'students';
        $data = $this->manageUsers($request, $user_type, true);
        //filter out only students behind progress
        $users = $data['users']->get()->filter(function($user) {
                    return $user->isBehindProgress();
                });

        $data['users'] = $users;
        $data['pageTitle'] = trans('panel.students_behind_schedule');
        $data['noPagination'] = true;

        return view(getTemplate() . '.panel.manage.students', $data);

    }

    public function manageUsers(Request $request, $user_type, $returnManageDataOnly = false, $exportUsers=false)
    {
        $valid_type = ['instructors', 'students', 'managers', 'sub_managers', 'consultants'];
        $authUser = auth()->user();

        // Access to teacher no longer included as according to new information, teacher is not part of any organizations
        // This is a major structural modification! -_-'
        if (($authUser->isAdmin() || $authUser->isOrganization() || $authUser->isOrganizationManager() || $authUser->isOrganizationSubManager() || $authUser->isOrganizationStaff()  || $authUser->isTeacher())
            and in_array($user_type, $valid_type)
        ) {
            if ($user_type == 'instructors') {
                $query = $authUser->getOrganizationTeachers();
            } elseif ( $user_type == 'managers' && ($authUser->isAdmin() || $authUser->isOrganization())) { //handling Higher manager management
                if($authUser->isAdmin()) {
                    $query = User::where('role_name', Role::$organization_manager)->with('organization');
                    if(!empty($request->organization_id)){
                        $query->where("organ_id", $request->organization_id);
                    }
                } else {
                    $query = $authUser->getOrganizationManagers();
                }
            } elseif ( $user_type == 'sub_managers'
                && ($authUser->isAdmin() || $authUser->isOrganization() || $authUser->isOrganizationManager())
            ) { //handling manager management
                if($authUser->isAdmin()) {
                    $query = User::where('role_name', Role::$organization_sub_manager)->with('organization');
                    if(!empty($request->organization_id)){
                        $query->where("organ_id", $request->organization_id);
                    }
                } else {
                    $query = $authUser->getOrganizationSubManagers();
                }
            } elseif ( $user_type == 'consultants'
                && ($authUser->isAdmin() || $authUser->isOrganization() || $authUser->isOrganizationManager() || $authUser->isOrganizationSubManager() )
            ) { //handling manager management
                if($authUser->isAdmin()) {
                    $query = User::where('role_name', Role::$organization_staff)->with('organization');
                    if(!empty($request->organization_id)){
                        $query->where("organ_id", $request->organization_id);
                    }
                } else {
                    $query = $authUser->getOrganizationStaff();
                }
            } else {
                if ($authUser->isTeacher()) {
                    $query = $authUser->getTeacherStudents($authUser->organ_id);
                } else { //logged in user is organization or admin
                    if ($user_type === 'students') {
                        if ($authUser->isOrganization()) {
                            $query = $authUser->getOrganizationStudents();
                        } else {
                            $query = $authUser->getOrganizationSiteStudents();
                            // this is coverd in getOrganizationSiteStudents()
                            // if ($authUser->isOrganizationStaff())
                            // {
                            //     $query = $query->where('manager_id', $authUser->id);
                            // }
                        }
                    } else {
                        $query = $authUser->getOrganizationManagers();
                    }
                }
            }


            $from = $request->get('from', null);
            $to = $request->get('to', null);
            $name = $request->get('name', null);
            $email = $request->get('email', null);
            $type = request()->get('type', null);

            if (!empty($from) and !empty($to)) {
                $from = strtotime($from);
                $to = strtotime($to);

                $query->whereBetween('created_at', [$from, $to]);
            } else {
                if (!empty($from)) {
                    $from = strtotime($from);

                    $query->where('created_at', '>=', $from);
                }

                if (!empty($to)) {
                    $to = strtotime($to);

                    $query->where('created_at', '<', $to);
                }
            }

            if(!empty($request->organization_id && $authUser->isOrganizationPersonnel())){
                $query->where("users.organ_id", $request->organization_id);
            }

            if (!empty($name)) {
                $query->where('full_name', 'like', "%$name%");
            }

            if (!empty($email)) {
                $query->where('email', $email);
            }

            if (!empty($type)) {
                if (in_array($type, ['active', 'inactive'])) {
                    $query->where('status', $type);
                } elseif ($type == 'verified') {
                    $query->where('verified', true);
                }
            }

            if($exportUsers) {
                return $query->get();
            }

            if (!$returnManageDataOnly) {
                $users = $query->orderBy('created_at', 'desc')
                        ->paginate(10);
            } else {
                $users = $query->orderBy('created_at', 'desc');
            }

            $inActiveCount = deepclone($users)->where("status","inactive")->count();
            $activeCount = deepclone($users)->where("status","active")->count();
            $verifiedCount = deepclone($users)->where('verified', true)->count();

            $organizations = User::where("role_name", "organization")->get();
            $data = [
                'organizations' => $organizations,
                'pageTitle' => trans('public.' . $user_type),
                'user_type' => $user_type,
                'organization' => $authUser,
                'users' => $users,
                'activeCount' => $activeCount,
                'inActiveCount' => $inActiveCount,
                'verifiedCount' => $verifiedCount,
            ];

            if ($returnManageDataOnly) {
                return $data;
            }
            if($authUser->isAdmin()) {
                return view('admin.manage.' . $user_type . '.list', $data);
            }

            if(session('NewUserId')){
                $userId = session('NewUserId');
                $user = User::with(["manager","organizationSites"])->findOrfail($userId);
                $data = array_merge($data,["NewUser"=>$user]);
            }

            return view(getTemplate() . '.panel.manage.' . $user_type, $data);
        }

        abort(404);
    }

    public function createUser($user_type)
    {
        $valid_type = ['students', 'managers', 'sub_managers', 'consultants'];
        $organization = auth()->user();
        if (
            ($organization->isOrganizationPersonnel() || $organization->isTeacher())
            && in_array($user_type, $valid_type)
        ) {
            $categories = Category::where('parent_id', null)
                ->with('subCategories')
                ->get();

            $userLanguages = getGeneralSettings('user_languages');
            if (!empty($userLanguages) and is_array($userLanguages)) {
                $userLanguages = getLanguages($userLanguages);
            }

            $query = User::where('role_name', Role::$organization)->where('status', 'active')->get();
            // $webinar = Webinar::whereIn('type', ['course', 'text_lesson'])->where('status', 'active')->get();
            $organId = $organization->isOrganizationPersonnel() ? (
                $organization->isOrganization() ? $organization->id : $organization->organ_id
                ) : null;

            $webinarHelper = new WebinarHelper();
            $visibleWebinars = $webinarHelper->getWebinarsVisibleToOrganization();

            $managers = null;
            if ($organization->isOrganization()) {
                $organization_sites = OrganizationSite::where('organ_id', $organization->id)
                    ->with('users')->get();
            } elseif ($organization->isOrganizationPersonnel()) {
                $organization_sites = $organization->organizationSites;
                if ($organization->isOrganizationStaff()) {
                    //Consultant will be the manager itself
                    $managers = $organization;
                    foreach($organization_sites as $consultant_organization_site) {
                        $consultantSiteId = $consultant_organization_site->id;
                        break; //just a failsafe - consultant will only be assigned to a single site
                    }
                }
            } else {
                $organization_sites = OrganizationSite::where('organ_id', $organization->organ_id)->get();
            }

            //authUser isn't a consultant
            if (is_null($managers)) {
                //get managers against first site id
                $site = null;
                foreach($organization_sites as $organization_site) {
                    $site = $organization_site->id;
                    if (isset($site) && (int)$site > 0) {
                        $managers = User::select('users.*')->where(['role_name' => Role::$organization_staff,
                            'status' => User::$active])
                            ->join('organization_site_user', 'organization_site_user.user_id', 'users.id')
                            ->where('organization_site_user.site_id', $site)
                            //->pluck('users.id', 'full_name');
                            ->get();
                    }
                    break;
                }
            }

            $data = [
                'pageTitle' => trans('public.new') . ' ' . trans('quiz.' . $user_type),
                'new_user' => true,
                'user_type' => $user_type,
                'user' => $organization,
                'organization_sites' => $organization_sites,
                'managers' => $managers,
                'categories' => $categories,
                'organization_id' => ($organization->role_name === Role::$organization) ? $organization->id : $organization->organ_id,
                'userLanguages' => $userLanguages,
                'currentStep' => 1,
                'organization' => $query,
                'webinar' => $visibleWebinars,
                'password' => User::newRandomPassword()
            ];

            if (isset($consultantSiteId)) {
                $data['consultantSiteId'] = $consultantSiteId;
            }

            return view(getTemplate() . '.panel.setting.index', $data);
        }

        abort(404);
    }

    public function storeUser(Request $request, $user_type)
    {
        try {
            //code...
            if($request->has("course")) {
                $webinar = WebinarTranslation::where("webinar_id", $request->course)->first();
                $webinarName = $webinar->title . " (Course ID: " . $webinar->id . ").";
            }else{
                $webinarName = "courses";
            }

            $valid_type = ['instructors', 'students', 'managers', 'sub_managers', 'consultants'];
            $organization = auth()->user();

            if (
                    ($organization->isOrganizationPersonnel()
                    || $organization->isTeacher()
                    )
                    and in_array($user_type, $valid_type)
            ) {
                $this->validate($request, [
                    'email' => 'required|string|email|max:255|unique:users',
                    'full_name' => 'required|string',
                    'password' => 'required|confirmed|min:6',
                ]);

                if ($user_type !== "students") {
                    $this->validate($request, [
                        'mobile' => 'sometimes|numeric',
                    ]);
                }

                if (in_array($user_type, ['managers', 'sub_managers', 'consultants', 'students'])) {
                    $this->validate($request, [
                        'organization_site' => 'required',
                    ]);
                    if($user_type == "students"){
                        $this->validate($request, [
                            'mobile' => 'required|numeric',
                        ]);
                    }
                }

                $data = $request->all();
                if ( !isset($data['schedule'])) {
                    $data['schedule'] = null;
                }
                //default role_name and role_id to teacher
                if ($user_type == 'instructors') {
                    $role_name = Role::$teacher;
                    $role_id = Role::getTeacherRoleId();
                } elseif ($user_type == 'managers') {
                    $role_name = Role::$organization_manager;
                    $role_id = Role::getOrganizationManagerRoleId();
                } elseif ($user_type == 'sub_managers') {
                    $role_name = Role::$organization_sub_manager;
                    $role_id = Role::getOrganizationSubManagerRoleId();
                } elseif ($user_type == 'consultants') {
                    $role_name = Role::$organization_staff;
                    $role_id = Role::getOrganizationStaffRoleId();
                } elseif ($user_type == 'students') {
                    $this->validate($request, [
                        'manager_id' => 'required|integer|min:1',
                    ]);
                    $role_name = Role::$user;
                    $role_id = Role::getUserRoleId();
                }

                if ($organization->isOrganization()) {
                    $organ_id = $organization->id;
                } else { //action is being performed by one of these: ['managers', 'sub_managers', 'consultants']
                    $organ_id = $organization->organ_id;
                }

                $manager_id = $request->get('manager_id');

                $referralSettings = getReferralSettings();
                $usersAffiliateStatus = (!empty($referralSettings) and !empty($referralSettings['users_affiliate_status']));
                $organizationSitesString = (isset($data['organization_site']) && is_array($data['organization_site']) ) ? implode(',',$data['organization_site']) : $data['organization_site'];
                $creationData = [
                    'role_name' => $role_name,
                    'role_id' => $role_id,
                    'email' => $data['email'],
                    'organ_id' => $organ_id,
                    'manager_id' => $manager_id,
                    'password' => Hash::make($data['password']),
                    'full_name' => $data['full_name'],
                    'mobile' => $data['mobile'],
                    'language' => $data['language'],
                    'affiliate' => $usersAffiliateStatus,
                    'newsletter' => (!empty($data['join_newsletter']) and $data['join_newsletter'] == 'on'),
                    'public_message' => (!empty($data['public_messages']) and $data['public_messages'] == 'on'),
                    'created_at' => time(),
                    'organization_site' => $organizationSitesString,
                ];

                if (isset($data['bio'])) {
                    $creationData['bio'] = $data['bio'];
                }

                if ($user_type === 'students') {
                    $data['course'] = isset($data['course'])?$data['course']:null;
                    $data['schedule'] = isset($data['schedule'])?$data['schedule']:null;
                }

                $user = User::create($creationData);
                if (isset($user->id) && (int)$user->id > 1) {
                    //organizaiton sites pivot insertion
                    if (isset($data['organization_site']) ) {
                        if (is_array($data['organization_site']) && count($data['organization_site'])) {
                            $organizationSites = $data['organization_site'];
                        } else {
                            $organizationSites = [$data["organization_site"]];
                        }
                        $organizationSites = array_fill_keys($organizationSites, array(
                            'organ_id' => ($organization->isOrganization()) ? $organization->id : $organization->organ_id,
                        ));
                          $user->organizationSites()->attach($organizationSites );
                    }
                }

                // if (!in_array($user_type, ['instructors', 'managers'])) {
                if ($user_type === "students") {
                    $notifyOptions = [
                        '[organization.name]' => $organization->full_name,
                        '[student.name]' => $data['full_name'] . " (Student ID: " . $user->id . ").",
                        '[c.title]' =>  $webinarName,
                    ];
                    $adminUserIds = User::where(['role_name'=> Role::$admin, 'status' => User::$active])->pluck('id')->toArray();
                    sendNotification('new_student_created', $notifyOptions, $adminUserIds);

                    //send notification to student to complete enrolment
                    $notifyOptions = [
                        '[student.name]' => $data['full_name'],
                        '[link]' => route('web.login'),
                    ];
                    if (sendNotification('complete_student_signup_request', $notifyOptions, $user->id)) {
                        //audit signup completion notification sent
                        $audit = new AuditTrail();
                        $audit->user_id = $user->id;
                        $audit->organ_id = $user->organ_id ?? null;
                        $audit->role_name = $user->role_name ?? null;
                        $audit->audit_type = AuditTrail::auditType['enrolment_completion_notification'];
                        $audit->added_by = $organization->id;
                        $audit->description = "Enrolment completion notification sent to student upon account creation (account created by {$organization->full_name})";
                        $ip = null;
                        $ip = getClientIp();
                        $audit->ip = ip2long($ip);
                        $audit->save();
                    }
                }
                if($user_type == "students"){

                    return redirect("/panel/manage/{$user_type}")->with("NewUserId",$user->id);
                }else{

                    return redirect("/panel/manage/{$user_type}/{$user->id}/edit");
                }

            }

            abort(404);
        } catch(\Illuminate\Database\QueryException $th ) {
            $failureMessage = "Failed to create user!";
            $message  = $failureMessage. '
' . $th->getMessage();
            \Log::error($message);
            $sqlPos = strpos($message, "(SQL: ");
            $message = substr($message, 0, $sqlPos);
            return redirect()->back()->with('enrollmentMessage', "Database error encountered while creating record: " . $message)->withInput();
        } catch (\Throwable $th) {
            //throw $th;
            $failureMessage = "Failed to create user!";
            $message = $failureMessage. ':' . $th->getMessage();
            \Log::error($message);
            return redirect()->back()->with('enrollmentMessage', $message)->withInput();
        }
    }

    public function editUser($user_type, $user_id, $step = 1)
    {
        $valid_type = ['instructors', 'students', 'managers', 'sub_managers', 'consultants'];
        $organization = auth()->user();

        if (
            (
                $organization->isOrganizationPersonnel()
            )
            and in_array($user_type, $valid_type)
        ) {
            $user = User::select('users.*')->where('users.id', $user_id);

            if($organization->isOrganization()) {
                $user = $user->where('organ_id', $organization->id)->with('organizationSites');
            } else {
                //sites of the respective organization member has been set here
                $userOrganizationSite = $organization->organizationSitesArray();
                $user = $user->where(['users.organ_id'=> $organization->organ_id])
                        ->join('organization_site_user', 'organization_site_user.user_id', 'users.id')
                        ->whereIn('organization_site_user.site_id', $userOrganizationSite);
            }
            $user = $user->first();

            if (!empty($user)) {
                $categories = Category::where('parent_id', null)
                    ->with('subCategories')
                    ->get();
                $userMetas = $user->userMetas;

                $occupations = $user->occupations->pluck('category_id')->toArray();

                $userLanguages = getGeneralSettings('user_languages');
                if (!empty($userLanguages) and is_array($userLanguages)) {
                    $userLanguages = getLanguages($userLanguages);
                }

                $query = User::where('role_name', Role::$organization)->where('status', 'active')->get();
                $webinar = Webinar::where('type', 'course')->where('status', 'active')->get();

                $managers = null;
                if ($organization->isOrganization()) {
                    $organization_sites = OrganizationSite::where('organ_id', $organization->id)
                                            ->with('users')->get();
                    $userOrganizationSite = $user->organization_site;
                    if(!empty($userOrganizationSite)) {
                        $managers = User::where(['organization_site' => $userOrganizationSite, 'status' => User::$active])
                                    ->where('role_name', Role::$organization_staff)->get();
                                    // ->whereIn('role_name', [Role::$organization_manager, Role::$organization_sub_manager])->get();
                    }
                } else {
                    //$userOrganizationSite should have already been set previously
                    $organization_sites = OrganizationSite::where('organization_sites.organ_id', $organization->organ_id)
                    ->join('organization_site_user', 'organization_site_user.site_id', 'organization_sites.id')
                    ->whereIn('organization_site_user.site_id',  $userOrganizationSite)
                    ->groupBy('organization_site_user.site_id')->get();
                }

                $userInfo = $user->userInfo;

                $data = [
                    'organization_id' => $organization->id,
                    'user' => $user,
                    'userInfo' => $userInfo,
                    'user_type' => $user_type,
                    'categories' => $categories,
                    'educations' => $userMetas->where('name', 'education'),
                    'experiences' => $userMetas->where('name', 'experience'),
                    'pageTitle' => trans('panel.settings'),
                    'occupations' => $occupations,
                    'userLanguages' => $userLanguages,
                    'currentStep' => $step,
                    'organization' => $query,
                    'organization_sites' => $organization_sites,
                    'managers' => $managers,
                    'webinar' => $webinar
                ];

                return view(getTemplate() . '.panel.setting.index', $data);
            }
        }

        abort(404);
    }

    public function deleteUser($user_type, $user_id)
    {
        $valid_type = ['instructors', 'students', 'managers'];
        $user = $organization = auth()->user();
        if(!$user->isAdmin()){
            abort(403);
        }


        if ($organization->canDeleteStudent() && $organization->isOrganization() and in_array($user_type, $valid_type)) {
            $user = User::where('id', $user_id)
                ->where('organ_id', $organization->id)
                ->first();

            if (!empty($user)) {
                $user->delete();

                return response()->json([
                    'code' => 200
                ]);
            }
        }

        return response()->json([], 422);
    }

    public function search(Request $request)
    {
        $term = $request->get('term');
        $option = $request->get('option', null);
        $user = auth()->user();

        if (!empty($term)) {
            $query = User::select('id', 'full_name')
                ->where(function ($query) use ($term) {
                    $query->where('full_name', 'like', '%' . $term . '%');
                    $query->orWhere('email', 'like', '%' . $term . '%');
                    $query->orWhere('mobile', 'like', '%' . $term . '%');
                })
                ->where('id', '<>', $user->id)
                ->whereNotIn('role_name', ['admin']);

            if (!empty($option) and $option == 'just_teachers') {
                $query->where('role_name', 'teacher');
            }

            $users = $query->get();

            return response()->json($users, 200);
        }

        return response('', 422);
    }

    public function contactInfo(Request $request)
    {
        $this->validate($request, [
            'user_id' => 'required'
        ]);

        $user = User::find($request->get('user_id'));

        if (!empty($user)) {
            return response()->json([
                'code' => 200,
                'avatar' => $user->getAvatar(),
                'name' => $user->full_name,
                'email' => !empty($user->email) ? $user->email : '-',
                'phone' => !empty($user->mobile) ? $user->mobile : '-'
            ], 200);
        }

        return response()->json([], 422);
    }

    public function offlineToggle(Request $request)
    {
        $user = auth()->user();

        $message = $request->get('message');
        $toggle = $request->get('toggle');
        $toggle = (!empty($toggle) and $toggle == 'true');

        $user->offline = $toggle;
        $user->offline_message = $message;

        $user->save();

        return response()->json([
            'code' => 200
        ], 200);
    }

    public function enrolStudents($id = 0)
    {
        if ($id == 0) {
            abort(404);
        }

        $user = User::where('role_name', Role::$user)->where('id', $id)->first();

        $webinarHelper = new WebinarHelper();
        $webinars =  $webinarHelper->getWebinarsVisibleToOrganization();
        $purchasedCourseIds = $user->getPurchasedCoursesIds();

        $data = [
            'pageTitle' => trans('public.students'),
            'user' => $user,
            'webinars' => $webinars,
            "purchasedCourseIds" => $purchasedCourseIds
        ];

        return view(getTemplate() . '.panel.manage.enrolStudents', $data);
    }

    public function createBreakRequest($user_id)
    {
        $authUser = auth()->user();
        if ($authUser->isOrganizationPersonnel()) {
            $user = User::select('users.*')->where('users.id', $user_id)
                    ->with('occupiedBreaks');

            if($authUser->isOrganization()) {
                $user = $user->where('organ_id', $authUser->id)->with('organizationSites');
            } else {
                //sites of the respective organization member has been set here
                $userOrganizationSite = $authUser->organizationSitesArray();
                $user = $user->where(['users.organ_id'=> $authUser->organ_id])
                        ->join('organization_site_user', 'organization_site_user.user_id', 'users.id')
                        ->whereIn('organization_site_user.site_id', $userOrganizationSite);
            }

            $user = $user->first();
            if (isset($user) && $user->id > 0) {
                $breaks = $user->occupiedBreaks;
            }
            //$students = $authUser->getOrganizationSiteStudents()->get();

            $data = [
                'organization_id' => ($authUser->isOrganization()) ? $authUser->id : $authUser->organ_id,
                'user' => $user,
                'breaks' => $breaks,
                'pageTitle' => trans('panel.add_break_request'),
            ];

            return view(getTemplate() . '.panel.break.create', $data);
        }
    }

    public function saveBreakRequest(Request $request)
    {
        $authUser = auth()->user();
        if ($authUser->isOrganizationPersonnel()) {
            $user_id = $request->get('user_id');
            if(!empty($user_id)) {
                $user = User::select('users.*')->where('users.id', $user_id)
                        ->with('occupiedBreaks');

                if($authUser->isOrganization()) {
                    $user = $user->where('organ_id', $authUser->id)->with('organizationSites');
                } else {
                    //sites of the respective organization member has been set here
                    $userOrganizationSite = $authUser->organizationSitesArray();
                    $user = $user->where(['users.organ_id'=> $authUser->organ_id])
                            ->join('organization_site_user', 'organization_site_user.user_id', 'users.id')
                            ->whereIn('organization_site_user.site_id', $userOrganizationSite);
                }

                $user = $user->first();
                if (isset($user) && $user->id > 0) {
                    //fetch existing occupied breaks
                    $occupiedBreaks = $user->occupiedBreaks;
                    //check if submitted date range clashes with existing occupied break ranges
                    $clashingDates = false;
                    $fromDate = Carbon::createFromFormat('Y-m-d', $request->get('from'));
                    $toDate = Carbon::createFromFormat('Y-m-d', $request->get('to'));
                    foreach($occupiedBreaks as $break) {
                        $breakFrom = Carbon::createFromFormat('Y-m-d', $break->from);
                        $breakTo = Carbon::createFromFormat('Y-m-d', $break->to);
                        if(
                            (
                                ($fromDate >= $breakFrom && $fromDate <= $breakTo)
                                || ($toDate >= $breakFrom && $toDate <= $breakTo)
                            ) || (
                                ($breakFrom >= $fromDate && $breakFrom <= $toDate)
                                || ($breakTo >= $fromDate && $breakTo <= $toDate)
                            )
                        ) {
                            $clashingDates = true;
                            break;
                        }
                    }

                    if ($clashingDates) {
                        return redirect()->back()->withInput($request->input())->with('error', trans('panel.clashing_dates_please_reselect'));
                    } else {
                        //Commit record to database
                        $fromDate = Carbon::createFromFormat('Y-m-d', $request->get('from'));
                        $toDate = Carbon::createFromFormat('Y-m-d', $request->get('to'));
                        $userBreak = new UserBreak();
                        $userBreak->from = $fromDate->format('Y-m-d');
                        $userBreak->to = $toDate->format('Y-m-d');
                        $userBreak->user_id = $user->id;
                        $userBreak->requested_by = $authUser->id;
                        $userBreak->status = strtolower(UserBreak::$status['pending']);
                        $userBreak->type = (in_array($request->get('type'), UserBreak::$breakTypes)) ? strtolower($request->get('type')) : strtolower(UserBreak::$breakTypes['other']);

                        if ($userBreak->save()) {
                            //audit break request status
                            $auditedUser = User::find($userBreak->user_id);
                            $audit = new AuditTrail();
                            $audit->user_id = $auditedUser->id;
                            $audit->organ_id = $auditedUser->organ_id;
                            $audit->role_name = $auditedUser->role_name;
                            $audit->audit_type = AuditTrail::auditType['user_break_requested'];
                            $audit->added_by = $authUser->id;
                            $audit->description = "Break Request Created (by {$authUser->full_name})";
                            $ip = null;
                            $ip = getClientIp();
                            $audit->ip = ip2long($ip);
                            $audit->save();
                            return redirect()->route('panel.manage.list.users', ['user_type' => 'students'])
                                    ->with('success', trans('panel.break_request_added_success'));
                        }
                    }
                }
            }
        }
        return redirect()->route('panel.manage.list.users', ['user_type' => 'students'])->with('error', trans('panel.break_request_added_failure'));
    }

    /**
    * Responsibility: get the all courses purchased by the student and return itt o the view.
    * view: web/default/course/courseProgress/all_courses_progresss
    * @param $user_type (string)
    * @param $user_id (Integer)
    * @return coursePurchased (object of Sale model) , student (object of User model)
    */

    public function getEnrolledCourses($user_type, $user_id) {
        // role_id = 3 for organization itself and role_id = 1 is being used for the user
        if ($user_type == 'students') {
            // dd(auth::user());
            // getting the user with organization_id and user_id
            $student = User::where([
                'organ_id'=> (auth::user()->role_id == 3) ? auth::user()->id : auth::user()->organ_id,
                'id'=> $user_id,
                'role_id' =>  '1'
            ])->first();
            if ($student) {

                $coursePurchased = $student->getPurchasedCourses();
                return view('web.default.course.courseProgress.all_courses_progress',compact('coursePurchased', 'student'));
            } else {
                abort(404);
            }
        } else {
            abort(404);
        }
    }

}
